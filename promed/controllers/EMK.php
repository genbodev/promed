<?php
/**
* Контроллер - Электронная медицинская карта
* 
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      All
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Andrew Markoff, Alexander Permyakov 
* @version      11.03.2012
 *
 * @property EPH_model $EPH_model
 * @property PersonNewBorn_model $PersonNewBorn_model for ufa
*/
class EMK extends swController
{
	public $inputRules = array(
		'getPersonEmkData' => array(
			array(
				'field' => 'user_MedStaffFact_id',
				'label' => 'Идентификатор рабочего места пользователя', // c которым была открыта форма ЭМК
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'user_LpuUnitType_SysNick',
				'label' => 'Тип подразделения ЛПУ', // c которым была открыта форма ЭМК
				'rules' => '',
				'type' => 'string'
			),
			// данные для отображения структуры дерева
			array(
				'default' => 'common',
				'field' => 'ARMType',
				'label' => 'Тип рабочего места врача',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'level',
				'label' => 'Уровень события',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 0,
				'field' => 'type',
				'label' => 'Тип группировки',
				'rules' => '',
				'type' => 'int'
			),array(
				'field' => 'filter',
				'label' => 'Фильтр',
				'rules' => '',
				'type' => 'string'
			),
			// данные для получения дочерних узлов
			array(
				'default' => 'root',
				'field' => 'node',
				'label' => 'Идентификатор родительской ноды',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'object',
				'label' => 'Тип объекта',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'object_id',
				'label' => 'Идентификатор объекта',
				'rules' => '',
				'type' => 'id'
			),
			// фильтры
			array(
				'default' => '',
				'field' => 'EvnDate_Range',
				'label' => 'Период случаев',
				'rules' => '',
				'type' => 'daterange'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека просматриваемой ЭМК',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Идентификатор диагноза',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Идентификатор рабочего места врача',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Идентификатор отделения',
				'rules' => 'trim',
				'type' => 'id'
			),
			array( //https://redmine.swan.perm.ru/issues/104824
				'default'	=> '1',
				'field'		=> 'from_MZ',
				'label'		=> 'Запуск из АРМ МЗ',
				'rules'		=> '',
				'type'		=> 'int'
			)
		),
		'loadEmkDoc' => array (				
			array('field' => 'filterDoc','label' => 'Фильтр','rules' => 'trim|strtolower','type' => 'string', 'default' => 'evn'),
			array('field' => 'Person_id','label' => 'Идентификатор человека','rules' => '','type' => 'id'),
			array('field' => 'Evn_rid','label' => 'Идентификатор события','rules' => '','type' => 'id'),
			array('field' => 'EvnXml_id','label' => 'Идентификатор документа','rules' => '','type' => 'id')
		),
		// -------------------------- 2017 ------------------
		'getPersonHistory' => array (
			array('field' => 'Person_id','label' => 'Идентификатор человека','rules' => 'required','type' => 'id'),
			array('field' => 'Server_id','label' => 'Идентификатор сервера','rules' => 'required','type' => 'id'),
			array('field' => 'userMedStaffFact_id','label' => 'Идентификатор рабочего места пользователя', 'rules' => '','type' => 'id'),
			array('field' => 'userLpuUnitType_SysNick','label' => 'Тип подразделения', 'rules' => 'trim','type' => 'string'),
			array('field' => 'Lpu_id','label' => 'Идентификатор МО','rules' => '','type' => 'id','session_value' => 'lpu_id'),
			array('field' => 'useArchive','label' => 'Признак загрузки архивных данных','rules' => '','type' => 'int'),
			array('default' => 1, 'field' => 'type', 'label' => 'Тип группировки', 'rules' => '', 'type' => 'int')
		),
		'getAll' => array (
			array('field' => 'Person_id','label' => 'Идентификатор человека','rules' => 'required','type' => 'id'),
			array('field' => 'Server_id','label' => 'Идентификатор сервера','rules' => 'required','type' => 'id'),
			array('field' => 'userMedStaffFact_id','label' => 'Идентификатор рабочего места пользователя', 'rules' => '','type' => 'id'),
			array('field' => 'userLpuUnitType_SysNick','label' => 'Тип подразделения', 'rules' => 'trim','type' => 'string'),
			array('field' => 'Lpu_id','label' => 'Идентификатор МО','rules' => '','type' => 'id','session_value' => 'lpu_id'),
			array('field' => 'useArchive','label' => 'Признак загрузки архивных данных','rules' => '','type' => 'int'),
			array('field' => 'object','label' => 'Объект','rules' => '','type' => 'string'),
			array('field' => 'object_value','label' => 'Идентификатор объекта','rules' => '','type' => 'id')
		),
		'loadEvnPLForm' => array (
			array(
				'field' => 'EvnPL_id',
				'label' => 'Идентификатор ТАП',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnPLStomForm' => array (
			array(
				'field' => 'EvnPLStom_id',
				'label' => 'Идентификатор стомат. ТАП',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPersonForm' => array (
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnVizitPLForm' => array (
			array(
				'field' => 'EvnVizitPL_id',
				'label' => 'Идентификатор посещения',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnVizitPLStomForm' => array (
			array(
				'field' => 'EvnVizitPLStom_id',
				'label' => 'Идентификатор стомат. посещения',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveDrugTherapyScheme' => array (
			array(
				'field' => 'EvnVizitPL_id',
				'label' => 'Идентификатор посещения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DrugTherapyScheme_id',
				'label' => 'Схема лекарственной терапии',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteDrugTherapyScheme' => array (
			array(
				'field' => 'EvnVizitPLDrugTherapyLink_id',
				'label' => 'Схема лекарственной терапии',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getEvnVizitNodeList' => array (
			array(
				'field' => 'Object',
				'label' => '',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Object_id',
				'label' => 'Идентификатор ТАП',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'пациент',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getLastDirectionVKforVMP' => array(
			array(
				'field' => 'Person_id',
				'label' => 'пациент',
				'rules' => 'required',
				'type' => 'id'
			)
		)
	);

	/**
	 * Список классов событий пациента
	 */
	private $_personEvnClassList = array();

	/**
	 * Тип загрузки дерева в ЭМК (0 - по хронологии; 1 - по событиям)
	 */
	private $_personEMKTreeType = 0;

	/**
	 * Уровень вложенности дерева в ЭМК
	 */
	private $_personEMKTreeLevel = 0;

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		if (!defined('IS_DEBUG'))
			define('IS_DEBUG', $this->config->item('IS_DEBUG'));

	}

	/**
	 * Получение данных для дерева ЭМК
	 */
	function getPersonEmkData()
	{
		$this->getPersonEPHData();
	}
	
	/**
	 * Формирование отображаемого текста ноды
	 * @param $lvl
	 * @param $getdata
	 * @param string $ARMType
	 * @param string $region_nick
	 * @return mixed
	 */
	private function _formNameNode($lvl, $getdata, $ARMType = 'common', $region_nick = '') {
		$arr = array();

		if ( !is_array($getdata) || count($getdata) == 0 ) {
			return $getdata;
		}

		switch ( $lvl ) {
			case 'GroupByType':
				$grouptype =& $getdata;
				foreach ($grouptype as $i => $row)
				{
					// Группа событий
					//$name = '<b>'.$row['EvnClass_Name'].'</b> ';
					$grouptype[$i]['Name'] = ''.$row['text'].' ';
					if (isset($row['iconCls']))
					{
						$getdata[$i]['iconCls'] = $row['iconCls'];
					}
					// Дата для сортировки
					$grouptype[$i]['Date'] = $grouptype[$i]['id'];
				}
				return $grouptype;
				break;
			case 'EvnPS':
				//$template = '{date_beg} - {date_end} / {Lpu_Name} / Госпитализация № {EvnPS_NumCard} ';
				$template = '{date_beg} - {date_end} / {Diag_Code} / {Lpu_Name}';
				$template_hint = 'Лечение в стационаре / {Diag_Name} / {hosp_state}';
				foreach ($getdata as $i => $row)
				{
					$row['iconCls'] = 'emk-tree-hospital';
					if ($row['EvnPS_IsFinish']==2)
					{
						$row['iconCls'] .= '-unclosed';
					}
					if ($row['accessType']=='view')
					{
						$row['iconCls'] .= '-locked';
					}
					$row['iconCls'] .= '24';
					$getdata[$i]['iconCls'] = $row['iconCls'];
					/*
					if (!empty($row['Diag_Code']))
						$row['Diag'] = '<span style="color: darkblue;">'.$row['Diag_Code'].'.'.$row['Diag_Name'].'</span>';
					else 
						$row['Diag'] = '<span style="color: red;"><i>нет диагноза</i></span>';
					*/
					// Наименование 
					$getdata[$i]['Name'] = $this->_parseNameView($row, $template);
					//if(($_SESSION['pmuser_id'] == $row['pmUser_insID'])||($_SESSION['pmuser_id'] == $row['pmUser_updID'])||($row['IsThis_MedPersonal']==1))
					if($row['IsThis_MedPersonal']==1)
						$getdata[$i]['Name'] = '<b>'.$getdata[$i]['Name'].'</b>';
					if ($row['PrehospType_id'] == 1)
						$getdata[$i]['Name'] .= ' / <sub><img src="/img/icons/ambulance16.png" /></sub>';
					// Хинт 
					$getdata[$i]['title'] = $this->_parseNameView($row, $template_hint);
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['sortDate'];
				}
				return $getdata;
				break;
			case 'EvnSection':
				$template = '{date_beg} - {date_end} / {Diag_Code} / {LpuSection_Name}';
				$template_hint = 'Лечение в отделении / {Diag_Name} / {MedPersonal_Fio}';
				foreach ($getdata as $i => $row)
				{
					$getdata[$i]['iconCls'] = 'hospitalization16';
					// Наименование 
					$getdata[$i]['Name'] = $this->_parseNameView($row, $template);
					// Хинт 
					$getdata[$i]['title'] = $this->_parseNameView($row, $template_hint);
					// Дата для сортировки 
					$getdata[$i]['Date'] = $row['sortDate'];
					//if(($_SESSION['pmuser_id'] == $row['pmUser_insID'])||($_SESSION['pmuser_id'] == $row['pmUser_updID'])||($row['IsThis_MedPersonal']==1))
					if($row['IsThis_MedPersonal']==1)
						$getdata[$i]['Name'] = '<b>'.$getdata[$i]['Name'].'</b>';
				}
				return $getdata;
				break;
			case 'EvnDocument':
				$template = '{date} / {EvnDocument_Name}';
				foreach ($getdata as $i => $row)
				{
					// Наименование 
					$getdata[$i]['Name'] = $this->_parseNameView($row, $template);
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['date'];
				}
				return $getdata;
				break;
			case 'LabDiagnostic':
			case 'InstrDiagnostic':
			case 'RadioDiagnostic':
				$template = '{date} {Research_Name} № {Research_Num} {MedPersonal_Fin}';
				foreach ($getdata as $i => $row)
				{
					// Наименование 
					$getdata[$i]['Name'] = $this->_parseNameView($row, $template);
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['date'];
				}
				return $getdata;
				break;
			case 'ProtocolSurgery':
				$template = '{date} {time} ПРОТОКОЛ ОПЕРАЦИИ № {ProtocolSurgery_Num} {SurgeryType_Name}. DS: {Diag_Code} {Diag_Name} ЛПУ: {Lpu_Name}, {LpuSection_Name}';
				foreach ($getdata as $i => $row)
				{
					// Наименование 
					$getdata[$i]['Name'] = $this->_parseNameView($row, $template);
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['date'];
				}
				return $getdata;
				break;
			case 'Epicrisis':
				$template = '{date} {time} {EpicrisisType_Name} Врач: {MedPersonal_Fin}';
				foreach ($getdata as $i => $row)
				{
					// Наименование 
					$getdata[$i]['Name'] = $this->_parseNameView($row, $template);
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['date'];
				}
				return $getdata;
				break;
			case 'MedicalCheckup':
				$template = '{date} {time} {MedicalCheckupType_Name} DS: {Diag_Code} {Diag_Name} {MedicalCheckupProfil_Name} Врач: {MedPersonal_Fin}';
				foreach ($getdata as $i => $row)
				{
					// Наименование 
					$getdata[$i]['Name'] = $this->_parseNameView($row, $template);
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['date'];
				}
				return $getdata;
				break;
			case 'EvnStickAll': // также как SignalInformationAll
			case 'EvnPLDispAll': // также как SignalInformationAll
			case 'Research': // также как SignalInformationAll
			case 'MedHisResearchAll': // также как SignalInformationAll
			case 'SignalInformationAll':
				foreach ($getdata as $i => $row)
				{
					// текст ноды
					$getdata[$i]['Name'] = $row['Name'];
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['order'];
				}
				return $getdata;
				break;
			case 'EvnPL':
				if ($ARMType != 'headBrig') {
					$template = '<span style="color: darkblue;">{EvnPL_setdisDT}</span> / <span style="color: darkblue;">{Diag_Code}</span> / <span style="color: darkblue;">{Lpu_Nick}</span>';
				} else {
					$template = '{EvnPL_setdisDT} / {Diag_Code} / {Lpu_Nick}';
				}
				$template_hint = 'Лечение в поликлинике / {Diag_Name} / {ResultClass_Name} / {ChildrensCount}';
				foreach ($getdata as $i => $row)
				{
					$row['iconCls'] = 'emk-tree-polka';
					if ($row['EvnPL_IsFinish']==2)
					{
						$row['iconCls'] .= '-unclosed';
					}
					if ($row['accessType']=='view')
					{
						$row['iconCls'] .= '-locked';
					}
					$row['iconCls'] .= '24';
					$getdata[$i]['iconCls'] = $row['iconCls'];
					// Если даты посещений разные - отображать первую и последнюю даты
					$row['EvnPL_setdisDT'] = ($row['EvnPL_setDT'] == $row['EvnPL_disDT']) ? $row['EvnPL_setDT'] : $row['EvnPL_setDT'] . ' - ' . $row['EvnPL_disDT'];
					// Наименование 
					$getdata[$i]['Name'] = $this->_parseNameView($row, $template);
					// Хинт 
					$getdata[$i]['title'] = $this->_parseNameView($row, $template_hint);
					// Наименование узла 
					$getdata[$i]['node_name'] = $row['EvnClass_Name'];
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['sortDate'];
					//if(($_SESSION['pmuser_id'] == $row['pmUser_insID'])||($_SESSION['pmuser_id'] == $row['pmUser_updID'])||($row['IsThis_MedPersonal']==1))
					if($row['IsThis_MedPersonal']==1 || ($row['IsNapravlNaUdalKonsult'] == 2 && getRegionNick() == 'ufa'))
						$getdata[$i]['Name'] = '<b>'.$getdata[$i]['Name'].'</b>';
				}
				return $getdata;
				break;
			case 'EvnPLStom':
				if ($ARMType != 'headBrig') {
					$template = '<span style="color: darkblue;">{EvnPLStom_setdisDT}</span> / <span style="color: darkblue;">{Lpu_Nick}</span>';
				} else {
					$template = '{EvnPLStom_setdisDT} / {Lpu_Nick}';
				}
				foreach ($getdata as $i => $row)
				{
					$row['iconCls'] = 'emk-tree-stomat';
					if ($row['EvnPLStom_IsFinish']==2)
					{
						$row['iconCls'] .= '-unclosed';
					}
					if ($row['accessType']=='view')
					{
						$row['iconCls'] .= '-locked';
					}
					$row['iconCls'] .= '24';
					$getdata[$i]['iconCls'] = $row['iconCls'];
					// Если даты посещений разные - отображать первую и последнюю даты
					$row['EvnPLStom_setdisDT'] = ($row['EvnPLStom_setDT'] == $row['EvnPLStom_disDT']) ? $row['EvnPLStom_setDT'] : $row['EvnPLStom_setDT'] . ' - ' . $row['EvnPLStom_disDT'];
					// Наименование 
					$getdata[$i]['Name'] = $this->_parseNameView($row, $template);
					// Наименование узла 
					$getdata[$i]['node_name'] = $row['EvnClass_Name'];
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['sortDate'];
					if($row['IsThis_MedPersonal']==1)
						$getdata[$i]['Name'] = '<b>'.$getdata[$i]['Name'].'</b>';
				}
				return $getdata;
				break;
			case 'EvnPLDispDop':
				foreach ($getdata as $i => $row)
				{
					$name = '';
					// Событие 
					// Даты случая 
					if (!empty($row['EvnPLDispDop_setDT']))
						$name .= $row['EvnPLDispDop_setDT'].'-'.$row['EvnPLDispDop_disDT'].' / ';
					else 
						$name .= '<span style="color: red;"><i>нет дат</i></span> / ';
					//$name = '<b>'.$row['EvnClass_Name'].'</b> ';
					$name .= $row['EvnClass_Name'].' ';
					// Количество посещений
					$name = $name.'/ число посещений: <span style="color: darkblue;">'.$row['EvnVizitDispDop_Count'].'</span> ';
					// ЛПУ
					$name = $name.'/ ЛПУ: '.$row['Lpu_Nick'].' ';
					$getdata[$i]['Name'] = $name;
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['EvnPLDispDop_setDT'];
				}
				return $getdata;
				break;
			case 'EvnPLDispAdult':
			case 'EvnPLDispChild':
				foreach ($getdata as $i => $row)
				{
					$row['iconCls'] = 'emk-tree-polka';
					if ($row['DispClass_id'] == 19) {
						$row['iconCls'] = 'emk-tree-migrant';
					}
					if ($row['DispClass_id'] == 26) {
						$row['iconCls'] = 'emk-tree-driver';
					}
					if ($row['EvnPLDisp_IsFinish']==2)
					{
						$row['iconCls'] .= '-unclosed';
					}
					if ($row['accessType']=='view')
					{
						$row['iconCls'] .= '-locked';
					}
					$row['iconCls'] .= '24';
					$getdata[$i]['iconCls'] = $row['iconCls'];

					$name = '';
					// Событие
					// Даты случая
					if (!empty($row['EvnPLDisp_setDT']))
						$name .= $row['EvnPLDisp_setDT'];
					if (!empty($row['EvnPLDisp_disDT']) && !empty($row['EvnPLDisp_IsFinish']) && $row['EvnPLDisp_IsFinish'] == 2)
						$name .= ' - '.$row['EvnPLDisp_disDT'];
					if (isset($row['DispClass_Name']) && !empty($row['DispClass_Name'])) {
						$name .= ' / '.$row['DispClass_Name'].' ';							
					} else {
						$name .= ' / '.$row['EvnClass_Name'].' ';						
					}
					// Количество посещений
					// $name = $name.'/ число посещений: <span style="color: darkblue;">'.$row['EvnPLDisp_VizitCount'].'</span> ';
					// ЛПУ
					$name .= ' / '.$row['Lpu_Nick'].' ';
					$getdata[$i]['Name'] = $name;
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['EvnPLDisp_setDT'];
					if (!empty($row['DispClass_id'])) {
						switch ($row['DispClass_id']) {
							case 1: // 1 этап
							case 2: // 2 этап
								$getdata[$i]['object'] = 'EvnPLDispDop13';
								break;
							case 3: // 1 этап
							case 7:
							case 4: // 2 этап
							case 8:
								$getdata[$i]['object'] = 'EvnPLDispOrp';
								break;
							case 5:
								$getdata[$i]['object'] = 'EvnPLDispProf';
								break;
							case 6: // 1 этап
							case 9:
							case 10:
							case 11: // 2 этап
							case 12:
								$getdata[$i]['object'] = 'EvnPLDispTeenInspection';
								break;
							case 19:
								$getdata[$i]['object'] = 'EvnPLDispMigrant';
								break;
							case 26:
								$getdata[$i]['object'] = 'EvnPLDispDriver';
								break;
						}
					}
				}
				return $getdata;
				break;
			case 'EvnPLDispScreen':
				foreach ($getdata as $i => $row)
				{
					$name = '';
					// Событие
					// Даты случая
					if (!empty($row['EvnPLDispScreen_setDT']))
						$name .= $row['EvnPLDispScreen_setDT'];
					if (!empty($row['EvnPLDispScreen_disDT']) && !empty($row['EvnPLDispScreen_IsFinish']) && $row['EvnPLDispScreen_IsFinish'] == 2)
						$name .= ' - '.$row['EvnPLDispScreen_disDT'];
					if (isset($row['DispClass_Name']) && !empty($row['DispClass_Name'])) {
						$name .= ' / '.$row['DispClass_Name'].' ';
					} else {
						$name .= ' / '.$row['EvnClass_Name'].' ';
					}
					// Количество посещений
					// $name = $name.'/ число посещений: <span style="color: darkblue;">'.$row['EvnPLDispScreen_VizitCount'].'</span> ';
					// ЛПУ
					$name .= ' / '.$row['Lpu_Nick'].' ';
					$getdata[$i]['Name'] = $name;
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['EvnPLDispScreen_setDT'];
				}
				return $getdata;
				break;
			case 'EvnPLDispScreenChild':
				foreach ($getdata as $i => $row)
				{
					$name = '';
					// Событие
					// Даты случая
					if (!empty($row['EvnPLDispScreenChild_setDT']))
						$name .= $row['EvnPLDispScreenChild_setDT'];
					if (!empty($row['EvnPLDispScreenChild_disDT']) && !empty($row['EvnPLDispScreenChild_IsFinish']) && $row['EvnPLDispScreenChild_IsFinish'] == 2)
						$name .= ' - '.$row['EvnPLDispScreenChild_disDT'];
					if (isset($row['DispClass_Name']) && !empty($row['DispClass_Name'])) {
						$name .= ' / '.$row['DispClass_Name'].' ';
					} else {
						$name .= ' / '.$row['EvnClass_Name'].' ';
					}
					// Количество посещений
					// $name = $name.'/ число посещений: <span style="color: darkblue;">'.$row['EvnPLDispScreenChild_VizitCount'].'</span> ';
					// ЛПУ
					$name .= ' / '.$row['Lpu_Nick'].' ';
					$getdata[$i]['Name'] = $name;
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['EvnPLDispScreenChild_setDT'];
				}
				return $getdata;
				break;
			case 'EvnOnkoNotify':
				foreach ($getdata as $i => $row) {
					$name = '';
					if (!empty($row['EvnOnkoNotify_setDT']))
						$name .= $row['EvnOnkoNotify_setDT'];
					if (isset($row['EvnOnkoNotify_Status']) && !empty($row['EvnOnkoNotify_Status']))
						$name .= ' / '.$row['EvnOnkoNotify_Status'].' ';
					$name .= ' / Извещение о включении в регистр по онкологии ';
					$getdata[$i]['Name'] = $name;
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['EvnOnkoNotify_setDT'];
				}
				return $getdata;
				break;
			case 'EvnOnkoNotifyNeglected':
				foreach ($getdata as $i => $row) {
					$name = '';
					if (!empty($row['EvnOnkoNotifyNeglected_setDT']))
						$name .= $row['EvnOnkoNotifyNeglected_setDT'];
					if (isset($row['OnkoLateDiagCause_Name']) && !empty($row['OnkoLateDiagCause_Name']))
						$name .= ' / '.$row['OnkoLateDiagCause_Name'].' ';
					$name .= ' / Протокол о запущенной форме онкозаболевания ';
					$getdata[$i]['Name'] = $name;
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['EvnOnkoNotifyNeglected_setDT'];
				}
				return $getdata;
				break;
			case 'EvnVizitPL':
				if ( in_array($region_nick, array('pskov', 'ufa')) ) {
					$template = '<span style="color: darkblue;">{EvnVizitPL_setDT}</span> / <span style="color: darkblue;">{Diag_Code}</span> / <span style="color: darkblue;">код {UslugaComplex_Code}</span> / <span style="color: darkblue;">{LpuSection_Name}</span>';
					$template_hint = 'Посещение поликлиники / {Diag_Name} / {UslugaComplex_Code}. {UslugaComplex_Name} / {VizitType_Name} / {ServiceType_Name} / {MedPersonal_FIO}';
				} else {
					$template = '<span style="color: darkblue;">{EvnVizitPL_setDT}</span> / <span style="color: darkblue;">{Diag_Code}</span> / <span style="color: darkblue;">{LpuSection_Name}</span>';
					$template_hint = 'Посещение поликлиники / {Diag_Name} / {VizitType_Name} / {ServiceType_Name} / {MedPersonal_FIO}';
				}
				foreach ($getdata as $i => $row)
				{
					// Наименование 
					$getdata[$i]['Name'] = $this->_parseNameView($row, $template);
					// Хинт 
					$getdata[$i]['title'] = $this->_parseNameView($row, $template_hint);
					// Наименование узла 
					$getdata[$i]['node_name'] = $row['EvnClass_Name'];
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['sortDate'];
					$row['iconCls'] = 'emk-tree-polka-unclosed';
					if ($row['accessType']=='view')
					{
						$row['iconCls'] .= '-locked';
					}
					$row['iconCls'] .= '16';
					$getdata[$i]['iconCls'] = $row['iconCls'];
					//if(($_SESSION['pmuser_id'] == $row['pmUser_insID'])||($_SESSION['pmuser_id'] == $row['pmUser_updID'])||($row['IsThis_MedPersonal']==1))
					if($row['IsThis_MedPersonal']==1)
						$getdata[$i]['Name'] = '<b>'.$getdata[$i]['Name'].'</b>';
				}
				return $getdata;
				break;
			case 'EvnVizitPLStom':
				if ( in_array($region_nick, array('pskov', 'ufa')) ) {
					$template = '<span style="color: darkblue;">{EvnVizitPLStom_setDT} {EvnClass_Name}</span>'
						.' / <span style="color: darkblue;">код {UslugaComplex_Code}</span>'
						.' / <span style="color: darkblue;">{LpuUnit_Name} / {LpuSection_Name} / {MedPersonal_FIO}</span>'
						.' / <span style="color: darkblue;">{ServiceType_Name}</span>'
						.' / <span style="color: darkblue;">{VizitType_Name}</span>'
						.' / <span style="color: darkblue;">{PayType_Name}</span>'
						.' / <span style="color: darkblue;">УЕТ: {EvnVizitPLStom_Uet}</span>';
					$template_hint = '{EvnClass_Name} / {UslugaComplex_Code}. {UslugaComplex_Name} / {VizitType_Name} / {ServiceType_Name} / {MedPersonal_FIO}';
				} else {
					$template = '<span style="color: darkblue;">{EvnVizitPLStom_setDT} {EvnClass_Name}</span>'
						.' / <span style="color: darkblue;">{LpuUnit_Name} / {LpuSection_Name} / {MedPersonal_FIO}</span>'
						.' / <span style="color: darkblue;">{ServiceType_Name}</span>'
						.' / <span style="color: darkblue;">{VizitType_Name}</span>'
						.' / <span style="color: darkblue;">{PayType_Name}</span>'
						.' / <span style="color: darkblue;">УЕТ: {EvnVizitPLStom_Uet}</span>';
					$template_hint = '{EvnClass_Name} / {VizitType_Name} / {ServiceType_Name} / {MedPersonal_FIO}';
				}
				foreach ($getdata as $i => $row)
				{
					// Наименование
					$getdata[$i]['Name'] = $this->_parseNameView($row, $template);
					// Хинт
					$getdata[$i]['title'] = $this->_parseNameView($row, $template_hint);
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['sortDate'];
					if($row['IsThis_MedPersonal']==1)
						$getdata[$i]['Name'] = '<b>'.$getdata[$i]['Name'].'</b>';
				}
				return $getdata;
				break;
			case 'EvnDiagPLStom':
				foreach ($getdata as $i => $row)
				{
					$name = '';
					// Дата установки
					$name .= '<span style="color: darkblue;"> Дата установки: '.$row['EvnDiagPLStom_setDT'].'</span> / ';
					// Название
					$name .= $row['EvnClass_Name'].' ';
					
					// Диагноз
					$name = $name. '/ <span style="color: darkblue;"> '.$row['Diag_Code'].' '.$row['Diag_Name'].'</span> ';
					// Характер заболевания
					$name = $name. '/ <span style="color: darkblue;"> '.$row['DeseaseType_Name'].'</span> ';
					
					// Наименование 
					$getdata[$i]['Name'] = $name;
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['EvnDiagPLStom_setDT'];
				}
				return $getdata;
			break;
			case 'CmpCloseCard':
				foreach ($getdata as $i => $row)
				{
					$getdata[$i]['iconCls'] = 'ambulance16';
					$name = '';
					// Дата установки
					$name .= '<span style="color: darkblue;"> '.$row['AcceptTime'].'</span>';
					if (trim($row['Lpu_Nick']) != '') $name .= ' / <span style="font-size: 14px;">'.$row['Lpu_Nick'].'</span>';
					//$name = $name. '/ Номер за день: <span style="color: darkblue;"> '.$row['Day_num'].'</span> ';							
					if ($row['Year_num'] > 0) $name = $name. '/ <span style="color: darkblue; font-size: 12px;"> №'.$row['Year_num'].'</span>';
					if (!empty($row['CmpCloseCard_YearNumPr'])) $name = $name. '<span style="color: darkblue; font-size: 12px;">'.$row['CmpCloseCard_YearNumPr'].'</span> ';
					$getdata[$i]['Name'] = $name;
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['AcceptTime'];
				}
				return $getdata;
			break;
			case 'CmpCallCard':
				foreach ($getdata as $i => $row)
				{
					//@todo поправить иконку 
					$getdata[$i]['iconCls'] = 'ambulance16';
					$name = '';
					// Дата установки
					$name .= '<span style="color: darkblue;"> '.$row['AcceptTime'].'</span>';
					if (trim($row['Lpu_Nick']) != '') $name .= ' / <span style="font-size: 14px;">'.$row['Lpu_Nick'].'</span>';
					//$name = $name. '/ Номер за день: <span style="color: darkblue;"> '.$row['Day_num'].'</span> ';							
					if ($row['Year_num'] > 0) $name = $name. '/ <span style="color: darkblue; font-size: 12px;"> №'.$row['Year_num'].'</span>';
					if (!empty($row['CmpCallCard_NgodPr'])) $name = $name. '<span style="color: darkblue; font-size: 12px;">'.$row['CmpCallCard_NgodPr'].'</span> ';
					$getdata[$i]['Name'] = $name;
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['AcceptTime'];
				}						
				return $getdata;
			break;
			case 'EvnVizitDispDop':
				foreach ($getdata as $i => $row)
				{
					$name = '';
					// Название и дата
					$name = $name. '<span style="color: darkblue;"> '.$row['EvnVizitDispDop_setDT'].'</span> / ';
					//$name = '<b>'.$row['EvnClass_Name'].'</b> / '.$row['EvnVizitDispDop_setDT'].' ';
					$name .= $row['EvnClass_Name'].' ';
					
					// Диагноз
					$name = $name. '/ <span style="color: darkblue;"> '.$row['Diag_Code'].'.'.$row['Diag_Name'].'</span> ';
					// Подразделение - Отделение 
					$name = $name. '/ <span style="color: darkblue;"> '.$row['LpuUnit_Name'].' / '.$row['LpuSection_Name'].'</span> ';
					// Специальность врача - Врач 
					$name = $name. ' / '.$row['DopDispSpec_Name'].' '.$row['MedPersonal_FIO'].'</span> ';
					// Наименование 
					$getdata[$i]['Name'] = $name;
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['EvnVizitDispDop_setDT'];
				}
				return $getdata;
				break;
			case 'EvnRecept':
				//[Значок льготности] 04.02.2010 – Таурин-Акос капли глазные 5 мл фл. N 1
				//var_dump($getdata);die;
				foreach ($getdata as $i => $row)
				{
					$name = '';
					//$name = $row['EvnClass_Name'].' ';
					// Дата выписки рецепта
					$name = $name. '<span style="color: darkblue;">'.$row['EvnRecept_setDT'].'</span> ';
					// текст «Рецепт» только при линейной группировке
					if (0 == $this->_personEMKTreeType)
						$name .= '/ Рецепт ';
					// Серия и номер рецепта
					$name = $name. '/ <span style="color: darkblue;">';
					if (getRegionNick() != 'kz') {
						$name = $name. ' '.$row['EvnRecept_Ser'];
					}
					$name = ' '.$row['EvnRecept_Num'].'</span> ';
					// Медикамент 
					$name = $name. '- <span style="color: darkblue;"> '.$row['Drug_Name'].'</span> ';
					// Кол-во 
					$name = $name. 'D.t.d: <span style="color: darkblue;"> '.$row['EvnRecept_Kolvo'].'</span> ';
					// Наименование 
					$getdata[$i]['Name'] = $name;
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['EvnRecept_setDT'];
				}
				return $getdata;
				break;
			case 'EvnReceptGeneral':
				//[Значок льготности] 04.02.2010 – Таурин-Акос капли глазные 5 мл фл. N 1
				//var_dump($getdata);die;
				foreach ($getdata as $i => $row)
				{
					$name = '';
					//$name = $row['EvnClass_Name'].' ';
					// Дата выписки рецепта
					$name = $name. '<span style="color: darkblue;">'.$row['EvnReceptGeneral_setDT'].'</span> ';
					// текст «Рецепт» только при линейной группировке
					if (0 == $this->_personEMKTreeType)
						$name .= '/ Рецепт ';
					// Серия и номер рецепта
					$name = $name. '/ <span style="color: darkblue;"> '.$row['EvnReceptGeneral_Ser'].' '.$row['EvnReceptGeneral_Num'].'</span> ';
					// Медикамент 
					$name = $name. '- <span style="color: darkblue;"> '.$row['Drug_Name'].'</span> ';
					// Кол-во 
					//$name = $name. 'D.t.d: <span style="color: darkblue;"> '.$row['EvnReceptGeneral_Kolvo'].'</span> ';
					// Наименование 
					$getdata[$i]['Name'] = $name;
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['EvnReceptGeneral_setDT'];
				}
				return $getdata;
				break;
			case 'FreeDocument':
				$template = '<span style="color: darkblue;">{EvnXml_insDT}</span> / <span style="color: darkblue;">{EvnXml_Name}</span> / <span style="color: darkblue;">{pmUser_Name}</span>';
				$template_hint = 'Документ / {EvnXml_insDT} / {EvnXml_Name} / {pmUser_Name}';
				foreach ($getdata as $i => $row) {
					// Наименование 
					$getdata[$i]['Name'] = $this->_parseNameView($row, $template);
					// Хинт 
					$getdata[$i]['title'] = $this->_parseNameView($row, $template_hint);
					// Наименование узла 
					$getdata[$i]['node_name'] = 'Документ';
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['EvnXml_insDT'];							
					$getdata[$i]['iconCls'] = 'document16';
				}
				return $getdata;
				break;
			case 'MorbusOnkoVizitPLDop':
				foreach ($getdata as $i => $row) {
					// Наименование 
					$getdata[$i]['Name'] = '<span style="color: darkblue;">Талон дополнений больного ЗНО</span>';
					// Хинт 
					$getdata[$i]['title'] = '';
					// Наименование узла 
					$getdata[$i]['node_name'] = 'Талон дополнений больного ЗНО';
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['MorbusOnkoVizitPLDop_setDT'];							
					$getdata[$i]['iconCls'] = '';
				}
				return $getdata;
				break;
			case 'MorbusOnkoLeave':
				foreach ($getdata as $i => $row) {
					// Наименование
					$getdata[$i]['Name'] = '<span style="color: darkblue;">Выписка из медицинской карты стационарного больного ЗНО</span>';
					// Хинт
					$getdata[$i]['title'] = '';
					// Наименование узла
					$getdata[$i]['node_name'] = 'Выписка из медицинской карты стационарного больного ЗНО';
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['MorbusOnkoLeave_setDT'];
					$getdata[$i]['iconCls'] = '';
				}
				return $getdata;
				break;
			case 'EvnUsluga':
				$template = '<span style="color: darkblue;">{EvnUsluga_setDT}</span> / <span style="color: darkblue;">{Usluga_Code}. {Usluga_Name}</span>';
				$template_hint = 'Оказание услуги / {EvnUsluga_Kolvo}';
				foreach ($getdata as $i => $row)
				{
					// Наименование 
					$getdata[$i]['Name'] = $this->_parseNameView($row, $template);
					// Хинт 
					$getdata[$i]['title'] = $this->_parseNameView($row, $template_hint);
					// Значок iconCls
					$getdata[$i]['iconCls'] = 'pay-'. $row['PayType_SysNick'] .'16';
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['EvnUsluga_setDT'];
					//Для параклинических услуг будет свой тип
					if ($row['EvnClass_Name'] == 'Параклиническая услуга') {
						$getdata[$i]['object'] = 'EvnUslugaPar';
					}
					if ($row['EvnClass_Name'] == 'Стоматологическая услуга') {
						$getdata[$i]['object'] = 'EvnUslugaStom';
					}
				}
				return $getdata;
				break;
			case 'EvnUslugaStom':
				foreach ($getdata as $i => $row)
				{
					$name = '';
					// дата оказания услуги
					$name .= '<span style="color: darkblue;">'.$row['EvnUslugaStom_setDT'].'</span> / ';
					// Название события - тип услуги 
					//$name = '<b>'.$row['EvnClass_Name'].'</b> ';
					$name .= $row['EvnClass_Name'].' ';
					// код и наименование услуги 
					$name = $name. '/ <span style="color: darkblue;"> '.$row['Usluga_Code'].'.'.$row['Usluga_Name'].'</span> ';
					// Кол-во 
					$name = $name. '/ количество: <span style="color: darkblue;"> '.$row['EvnUslugaStom_Kolvo'].'</span> ';
					// Наименование 
					$getdata[$i]['Name'] = $name;
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['EvnUslugaStom_setDT'];
				}
				return $getdata;
				break;
			case 'EvnUslugaPar'://{Usluga_Code}.
				$template = '{EvnUslugaPar_setDate} / <span style="color: darkblue;">{Usluga_Name}</span> / {Lpu_Name} / {LpuSection_Name}';
				$template_hint = 'Параклиническая услуга / {MedPersonal_Fio}';
				foreach ($getdata as $i => $row)
				{
					$row['EvnUslugaPar_setDate'] = (strlen($row['EvnUslugaPar_setDT'])>0)?$row['EvnUslugaPar_setDT']:'<sub><img src="/img/icons/exclamation16.png" title="Неоказанная услуга" /></sub>';
					$getdata[$i]['iconCls'] = 'document-parka24';
					// Наименование 
					$getdata[$i]['Name'] = $this->_parseNameView($row, $template);
					// Хинт 
					$getdata[$i]['title'] = $this->_parseNameView($row, $template_hint);
					// Дата для сортировки 
					$getdata[$i]['Date'] = $row['sortDate'];
				}
				return $getdata;
				break;
			case 'EvnUslugaCommon'://{Usluga_Code}.
				$template = '{EvnUslugaCommon_setDate} / <span style="color: darkblue;">{Usluga_Name}</span> / {Lpu_Name} / {LpuSection_Name}';
				$template_hint = 'Общая услуга / {MedPersonal_Fio}';
				foreach ($getdata as $i => $row)
				{
					$row['EvnUslugaCommon_setDate'] = (strlen($row['EvnUslugaCommon_setDT'])>0)?$row['EvnUslugaCommon_setDT']:'<sub><img src="/img/icons/exclamation16.png" title="Неоказанная услуга" /></sub>';
					$getdata[$i]['iconCls'] = 'document-parka24';
					// Наименование
					$getdata[$i]['Name'] = $this->_parseNameView($row, $template);
					// Хинт
					$getdata[$i]['title'] = $this->_parseNameView($row, $template_hint);
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['sortDate'];
				}
				return $getdata;
				break;
			case 'EvnUslugaOper'://{Usluga_Code}.
				$template = '{EvnUslugaOper_setDate} / <span style="color: darkblue;">{Usluga_Name}</span> / {Lpu_Name} / {LpuSection_Name}';
				$template_hint = 'Оперативная услуга / {MedPersonal_Fio}';
				foreach ($getdata as $i => $row)
				{
					$row['EvnUslugaOper_setDate'] = (strlen($row['EvnUslugaOper_setDT'])>0)?$row['EvnUslugaOper_setDT']:'<sub><img src="/img/icons/exclamation16.png" title="Неоказанная услуга" /></sub>';
					$getdata[$i]['iconCls'] = 'document-parka24';
					// Наименование
					$getdata[$i]['Name'] = $this->_parseNameView($row, $template);
					// Хинт
					$getdata[$i]['title'] = $this->_parseNameView($row, $template_hint);
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['sortDate'];
				}
				return $getdata;
				break;
			case 'EvnUslugaTelemed'://{Usluga_Code}.
				$template = '{EvnUslugaTelemed_setDate} / Консультация <span style="color: darkblue;">{Usluga_Name}</span> / {Lpu_Name} / {LpuSection_Name}';
				$template_hint = 'Телемедицинская услуга / {MedPersonal_Fio}';
				foreach ($getdata as $i => $row)
				{
					$row['EvnUslugaTelemed_setDate'] = (strlen($row['EvnUslugaTelemed_setDT'])>0)?$row['EvnUslugaTelemed_setDT']:'<sub><img src="/img/icons/exclamation16.png" title="Неоказанная услуга" /></sub>';
					$getdata[$i]['iconCls'] = 'document-parka24';
					// Наименование
					$getdata[$i]['Name'] = $this->_parseNameView($row, $template);
					// Хинт
					$getdata[$i]['title'] = $this->_parseNameView($row, $template_hint);
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['EvnUslugaTelemed_setDT'];
				}
				return $getdata;
				break;
			case 'EvnUslugaDispDop':
				foreach ($getdata as $i => $row)
				{
					$name = '';
					// дата оказания услуги
					$name .= '<span style="color: darkblue;">'.$row['EvnUslugaDispDop_setDT'].'</span> / ';
					// Название события - тип услуги 
					//$name = '<b>'.$row['EvnClass_Name'].'</b> ';
					$name .= $row['EvnClass_Name'].' ';
					// код и наименование услуги 
					$name = $name. '/ <span style="color: darkblue;"> '.$row['Usluga_Code'].'.'.$row['Usluga_Name'].'</span> ';
					// Кол-во 
					$name = $name. '/ количество: <span style="color: darkblue;"> '.$row['EvnUslugaDispDop_Kolvo'].'</span> ';
					// Наименование 
					$getdata[$i]['Name'] = $name;
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['EvnUslugaDispDop_setDT'];
				}
				return $getdata;
				break;
			/*
			case 'PersonDocumentsGroup':
				foreach ($getdata as $i => $row)
				{
					// Название события
					//$name = '<b>'.$row['EvnClass_Name'].'</b> ';
					$name = $row['PersonDocumentsGroup_Name'];							
					// Наименование 
					$getdata[$i]['Name'] = $name;
					// Дата для сортировки
					$getdata[$i]['Date'] = '01.01.1970';
				}
				return $getdata;
				break;
			case 'PersonDocuments':
				foreach ($getdata as $i => $row)
				{
					// Название события
					//$name = '<b>'.$row['EvnClass_Name'].'</b> ';
					$name = $row['PersonDocuments_Name'];							
					// Наименование 
					$getdata[$i]['Name'] = $name;
					// Дата для сортировки
					$getdata[$i]['Date'] = '01.01.1970';
				}
				return $getdata;
				break;
			case 'DeathSvid':
				foreach ($getdata as $i => $row)
				{
					// Название события
					//$name = '<b>'.$row['EvnClass_Name'].'</b> ';
					$name = $row['EvnClass_Name'];
					$name .= ' '.$row['DeathSvid_Ser'].' '.$row['DeathSvid_Num'];
					$name .= ' '.$row['DeathSvid_DeathDate'];
					$name .= ' '.$row['DeathCause_Name'];
					$name .= ' '.$row['MedPersonal_FIO'];
					// Наименование 
					$getdata[$i]['Name'] = $name;
					// Дата для сортировки
					$getdata[$i]['Date'] = '01.01.1970';
				}
				return $getdata;
				break;
			case 'BirthSvid':
				foreach ($getdata as $i => $row)
				{
					// Название события
					//$name = '<b>'.$row['EvnClass_Name'].'</b> ';
					$name = $row['EvnClass_Name'];
					$name .= ' '.$row['BirthSvid_Ser'].' '.$row['BirthSvid_Num'];
					$name .= ' '.$row['BirthSvid_GiveDate'];
					$name .= ' '.$row['BirthPlace_Name'];
					$name .= ' '.$row['Lpu_Nick'];
					// Наименование 
					$getdata[$i]['Name'] = $name;
					// Дата для сортировки
					$getdata[$i]['Date'] = '01.01.1970';
				}
				return $getdata;
				break;
			*/
			case 'EvnStick':// также как для EvnStickDop
			case 'EvnStickDop':
				$template = 'ЛВН / {StickWorkType_Name} / {StickOrder_Name} / <span style="color: darkblue;">{EvnStick_Ser} {EvnStick_Num} {EvnStick_setDate}</span> / {EvnStick_ParentTypeName} {EvnStick_ParentNum} {EvnStick_ParentDate}';
				foreach ($getdata as $i => $row)
				{
					$name = $this->_parseNameView($row, $template);
					$name .= '/ '.$row['EvnStick_closedName'];
					if ($row['EvnStick_closed']) {
						 $name .= ': <span style="color: darkblue;">'.$row['EvnStick_disDate'].'</span>';
					}
					// Наименование
					$getdata[$i]['Name'] = $name;
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['sortdate'];
					$getdata[$i]['iconCls'] = 'document16';
				}
				return $getdata;
				break;
			case 'EvnStickStudent':
				$template = 'Справка учащегося <span style="color: darkblue;">{EvnStickStudent_Num}</span> <span style="color: darkblue;">{EvnStickStudent_setDate}</span>';
				foreach ($getdata as $i => $row)
				{
					// Наименование
					$getdata[$i]['Name'] = $this->_parseNameView($row, $template);
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['sortdate'];
				}
				return $getdata;
				break;
			case 'EvnCostPrint':
				$template = 'Справка о стоимости лечения <span style="color: darkblue;">{EvnCostPrint_setDate}</span>';
				foreach ($getdata as $i => $row)
				{
					// Наименование
					$getdata[$i]['Name'] = $this->_parseNameView($row, $template);
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['sortdate'];
				}
				return $getdata;
				break;
			case 'CmpCallCardCostPrint':
				$template = 'Справка о стоимости лечения <span style="color: darkblue;">{CmpCallCardCostPrint_setDate}</span>';
				foreach ($getdata as $i => $row)
				{
					// Наименование
					$getdata[$i]['Name'] = $this->_parseNameView($row, $template);
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['sortdate'];
				}
				return $getdata;
				break;
			/*
			case 'BegPersonPrivilege':
				//[Значок льготы: фед., рег., мест.] 05.02.2009 / Инвалиды 2-й группы [?Льгота действительна ? недействительна]
				foreach ($getdata as $i => $row)
				{
					$name = '';
					// дата открытия 
					$name = $name. '<span style="color: darkblue;">'.$row['PersonPrivilege_begDT'].'</span> ';
					// Убрать текст «Льгота: открытие/закрытие» и др., сохранить его только при линейной группировке
					if (0 == $this->_personEMKTreeType)
						$name .= '/ Льгота: открытие ';
					// Категория льготы
					$name = $name. '/ <span style="color: darkblue;">'.$row['PrivilegeType_Name'].'</span> ';
					// Льгота действительна ok16/недействительна delete16
					$cur_date = new DateTime(date('d.m.Y'));
					$beg_date = new DateTime($row['PersonPrivilege_begDT']);
					$end_date = (!empty($row['PersonPrivilege_endDT']))?new DateTime($row['PersonPrivilege_endDT']):NULL;
					$real_privelege = ( // Льгота действительна, если 
						$beg_date <= $cur_date
						AND
						( empty($end_date) OR $end_date > $cur_date)
						AND 
						( empty($row['PersonRefuse_IsRefuse']) OR $row['PersonRefuse_IsRefuse'] != 2)
					);
					$ico = '<img src="/img/icons/delete16.png" title="Льгота недействительна" />';
					if ($real_privelege) $ico = '<img src="/img/icons/ok16.png" title="Льгота действительна" />';
					$name .= '<sub>'.$ico.'</sub>';// [beg_date: '.$row['PersonPrivilege_begDT'].' end_date:'.$row['PersonPrivilege_endDT'].' IsRefuse:'.$row['PersonRefuse_IsRefuse'].']';
					// ЛПУ
					//$name = $name.'/ ЛПУ: '.$row['Lpu_Nick'].' ';
					// Значок iconCls
					$getdata[$i]['iconCls'] = $row['iconCls'];
					// Наименование 
					$getdata[$i]['Name'] = $name;
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['PersonPrivilege_begDT'];
				}
				return $getdata;
				break;
			case 'EndPersonPrivilege':
				//[Значок льготы: фед., рег., мест.] 05.02.2009 / Инвалиды 2-й группы [?Льгота действительна ? недействительна]
				foreach ($getdata as $i => $row)
				{
					$name = '';
					// дата закрытия 
					$name = $name. '<span style="color: darkblue;">'.$row['PersonPrivilege_endDT'].'</span> ';
					// Убрать текст «Льгота: открытие/закрытие» и др., сохранить его только при линейной группировке
					if (0 == $this->_personEMKTreeType)
						$name .= '/ Льгота: закрытие ';
					// Категория льготы
					$name = $name. '/ <span style="color: darkblue;">'.$row['PrivilegeType_Name'].'</span> ';
					// Льгота действительна ok16/недействительна delete16
					$cur_date = new DateTime(date('d.m.Y'));
					$beg_date = new DateTime($row['PersonPrivilege_begDT']);
					$end_date = (!empty($row['PersonPrivilege_endDT']))?new DateTime($row['PersonPrivilege_endDT']):NULL;
					$real_privelege = ( // Льгота действительна, если 
						$beg_date <= $cur_date
						AND
						( empty($end_date) OR $end_date > $cur_date)
						AND 
						( empty($row['PersonRefuse_IsRefuse']) OR $row['PersonRefuse_IsRefuse'] != 2)
					);
					$ico = '<img src="/img/icons/delete16.png" title="Льгота недействительна" />';
					if ($real_privelege) $ico = '<img src="/img/icons/ok16.png" title="Льгота действительна" />';
					$name .= '<sub>'.$ico.'</sub>';
					// ЛПУ
					//$name = $name.'/ ЛПУ: '.$row['Lpu_Nick'].' ';
					// Значок iconCls
					$getdata[$i]['iconCls'] = $row['iconCls'];
					// Наименование 
					$getdata[$i]['Name'] = $name;
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['PersonPrivilege_endDT'];
				}
				return $getdata;
				break;
			case 'BegPersonDisp':
				//[Значок дисп.] 05.02.2009 / Взятие на учет / C90.0 Множественная миелома / Пермь ГП2 – кабинет такой-то
				foreach ($getdata as $i => $row)
				{
					$name = '';
					// дата постановки 
					$name = $name. '<span style="color: darkblue;">'.$row['PersonDisp_begDT'].'</span> / ';
					// текст «Диспансеризация» только при линейной группировке
					if (0 == $this->_personEMKTreeType)
						$name .= 'Диспансеризация: ';
					// Название события
					$name .= 'Взятие на учет ';
					// Диагноз
					$name = $name. '/ <span style="color: darkblue;">'.$row['Diag_Code'].' '.$row['Diag_Name'].'</span> ';
					// ЛПУ - кабинет
					$name = $name. '/ <span style="color: darkblue;">'.$row['Lpu_Nick'].'</span>';// - кабинет 
					// ЛПУ - Подразделение - Отделение - МедПерсонал
					//$name = $name. '/ <span style="color: darkblue;"> '.$row['Lpu_Nick'].' / '.$row['LpuUnit_Name'].' / '.$row['LpuSection_Name'].' / '.$row['MedPersonal_FIO'].'</span> ';
					// Дата следующей явки
					//$name = $name. '/ <span style="color: darkblue;">'.$row['PersonDisp_NextDate'].'</span> ';
					// Наименование 
					$getdata[$i]['Name'] = $name;
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['PersonDisp_begDT'];
				}
				return $getdata;
				break;
			case 'EndPersonDisp':
				//[Значок дисп.] 05.02.2009 / Снятие с учета / C90.0 Множественная миелома / Пермь ГП2 – кабинет такой-то
				foreach ($getdata as $i => $row)
				{
					$name = '';
					// дата снятия 
					$name = $name. '<span style="color: darkblue;">'.$row['PersonDisp_endDT'].'</span> / ';
					// текст «Диспансеризация» только при линейной группировке
					if (0 == $this->_personEMKTreeType)
						$name .= 'Диспансеризация: ';
					// Название события
					$name .= 'Снятие с учета ';
					// Диагноз
					$name = $name. '/ <span style="color: darkblue;"> '.$row['Diag_Code'].' '.$row['Diag_Name'].'</span> ';
					// ЛПУ - кабинет 
					$name = $name. '/ <span style="color: darkblue;"> '.$row['Lpu_Nick'].'</span> ';// - кабинет
					// ЛПУ - Подразделение - Отделение - МедПерсонал
					//$name = $name. '/ <span style="color: darkblue;"> '.$row['Lpu_Nick'].' / '.$row['LpuUnit_Name'].' / '.$row['LpuSection_Name'].' / '.$row['MedPersonal_FIO'].'</span> ';
					// Причина снятия
					//$name = $name. '/ <span style="color: darkblue;">'.$row['DispOutType_Name'].'</span> ';
					// Наименование 
					$getdata[$i]['Name'] = $name;
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['PersonDisp_endDT'];
				}
				return $getdata;
				break;
			case 'BegPersonCard':
				foreach ($getdata as $i => $row)
				{
					$name = '';
					// дата прикрепления
					$name = $name. '<span style="color: darkblue;">'.$row['PersonCard_begDT'].'</span> / ';
					// Название события
					$name .= 'Прикрепление: ';
					// ЛПУ прикрепления
					$name = $name. '<span style="color: darkblue;">'.$row['Lpu_Nick'].'</span> ';
					// Наименование 
					$getdata[$i]['Name'] = $name;
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['PersonCard_begDT'];
				}
				return $getdata;
				break;
			case 'EndPersonCard':
				foreach ($getdata as $i => $row)
				{
					$name = '';
					// дата прикрепления
					$name = $name. '<span style="color: darkblue;">'.$row['PersonCard_endDT'].'</span> / ';
					// Название события
					$name .= 'Открепление: ';
					// ЛПУ прикрепления
					$name = $name. '<span style="color: darkblue;">'.$row['Lpu_Nick'].'</span> ';
					// Наименование 
					$getdata[$i]['Name'] = $name;
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['PersonCard_endDT'];
				}
				return $getdata;
				break;
			*/
			case 'EvnDirection':
				$template = 'Направлен <span style="color: darkblue;">{DirType_Name}</span> № <span style="color: darkblue;">{EvnDirection_Num}</span> / <span style="color: darkblue;">{Lpu_Nick}</span> / <span style="color: darkblue;">{LpuSectionProfile_Name}</span> / <span style="color: darkblue;">{RecDate}</span>';
				foreach ($getdata as $i => $row)
				{
					if (empty($row['RecDate'])) {
						$row['RecDate'] = '';
					}
					if (empty($row['RecDate']) && !empty($row['EvnStatus_Name'])) {
						$row['RecDate'] = $row['EvnStatus_Name'];
						if (!empty($row['EvnDirection_statusDate'])) {
							$template .= ' <span style="color: darkblue;">{EvnDirection_statusDate}</span>';
						}
					}
					/*if(!empty($row['EvnQueue_failDT']))
						$row['RecDate'] = 'ОТМЕНЕНО';*/
					$getdata[$i]['iconCls'] = 'pol-directions16';
					// Наименование 
					$getdata[$i]['Name'] = $this->_parseNameView($row, $template);
					// Хинт
					$template_hint = '';
					if (!empty($row['EvnStatus_Name'])) {
						$template_hint = 'Статус <span style="color: darkblue;">{EvnStatus_Name}</span>';
						if (!empty($row['EvnDirection_statusDate'])) {
							$template_hint .= ' <span style="color: darkblue;">{EvnDirection_statusDate}</span>';
						}
					}
					if (!empty($template_hint)) {
						$getdata[$i]['title'] = $this->_parseNameView($row, $template_hint);
					}
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['EvnDirection_setDT'];
				}
				return $getdata;
				break;
			case 'EvnVK':// также как EvnMse
			case 'EvnPrescrMse':// также как EvnMse 
			case 'EvnMse':
				$template = '<span style="color: darkblue;">{date_beg}</span> / <span style="color: darkblue;">{Diag_Code}</span> / <span style="color: darkblue;">{MedService_Name}</span> / <span style="color: darkblue;">{EvnClass_Name}</span>';
				foreach ($getdata as $i => $row)
				{
					//$getdata[$i]['iconCls'] = 'pol-directions16';
					// Наименование 
					$getdata[$i]['Name'] = $this->_parseNameView($row, $template);
					// Дата для сортировки
					$getdata[$i]['Date'] = $row['date_beg'];
				}
				return $getdata;
				break;

		}

		return $getdata;
	}

	/**
	 * Вспомогательная функция для вставки значений узла в представление узла
	 */
	private function _parseNameView($data, $view) {
		if ( empty($view) ) {
			$view = '{text}';
		}

		$search_replace = array();

		foreach ( $data as $k => $v ) {
			if ( strpos($view, $k) ) {
				$search_replace['{' . $k . '}'] = $v;
			}
		}

		return strtr($view,$search_replace);
	}

	/**
	 * @param $childrens
	 * @param $field
	 * @param $lvl
	 * @param string $dop
	 * @return array
	 */
	private function _getPersonEPHChild($childrens, $field, $dop = "") {
		$val = array();

		if ( !is_array($childrens) || count($childrens) == 0 ) {
			return $val;
		}

		foreach ( $childrens as $i => $rows ) {
			if ( !isset($rows['Date']) OR !isset($rows[$field['id']]) ) {
				continue;
			}
			if (isset($rows['ChildrensCount'])) {
				$field['leaf'] = ($rows['ChildrensCount'] == 0) ? true : false;
			}
			$code = (!isset($rows['object']))?$field['object']:$rows['object'];

			switch ( $field['object'] ) {
				case 'EvnStick':
				case 'EvnStickDop':
				case 'EvnStickStudent':
					$id = $code.'_'.$rows[$field['id']].'_'.$rows['Evn_pid'];
				break;
				default:
					$id = $code.'_'.$rows[$field['id']];
				break;
			}

			$obj = array(
				'archiveRecord' => empty($rows['archiveRecord'])?(!empty($_REQUEST['useArchive'])?1:0):$rows['archiveRecord'],
				'text' => toUTF(trim($rows[$field['name']])),
				'id' => $id,
				'date' => $rows['Date'],
				'object' => $code,
				'Lpu_id' => (isset($rows['Lpu_id'])) ? $rows['Lpu_id']: null,
				'object_id' => $field['id'],
				'object_value' => $rows[$field['id']],
				'leaf' => (isset($rows['leaf']))?$rows['leaf']:$field['leaf'],
				'iconCls' => (!isset($rows['iconCls']))?$field['iconCls']:$rows['iconCls'],
				'isMicroLab' => !isset($rows['isMicroLab']) ? 1 : $rows['isMicroLab'],
				'cls' => $field['cls']
				);
			$obj['node_name'] = (!empty($rows['node_name']))?toUTF($rows['node_name']):$obj['text'];

			// Если Person_id 
			if ( isset($field['person_id']) ) {
				$obj['object_id'] = 'person_id';
				$obj['object'] = $rows['object'];
				$obj['object_value'] = $field['person_id'];
			}

			if ( isset($rows['accessType']) ) {
				$obj['accessType'] = $rows['accessType'];
			}

			if ( isset($rows['EvnClass_SysNick']) ) {
				$obj['EvnClass_SysNick'] = $rows['EvnClass_SysNick'];
			}

			if ( isset($rows['Parent_EvnClass_SysNick']) ) {
				$obj['Parent_EvnClass_SysNick'] = $rows['Parent_EvnClass_SysNick'];
			}
			
			if ( isset($rows['title']) ) {
				$obj['title'] = $rows['title'];
			}

			switch ( $field['object'] ) {
				case 'EvnVizitPL':
					//case 'EvnVizitPLStom':
					$obj['accessType'] = $rows['accessType'];
					$obj['accessForDel'] = (isset($rows['accessForDel']) ? $rows['accessForDel'] : '');
					$obj['Diag_id'] = $rows['Diag_id'];
					$obj['LpuSection_id'] = $rows['LpuSection_id'];
					$obj['MedPersonal_id'] = $rows['MedPersonal_id'];
					$obj['VizitType_id'] = $rows['VizitType_id'];
					$obj['VizitType_SysNick'] = $rows['VizitType_SysNick'];
					$obj['Evn_setDate'] = $rows[$field['object'] . '_setDT'];
					$obj['Evn_setTime'] = $rows[$field['object'] . '_setTime'];
					$obj['Evn_Name'] = toUTF($rows['Evn_Name']);
					break;
				case 'EvnStick':
				case 'EvnStickDop':
				case 'EvnStickStudent':
					$obj['accessType'] = $rows['accessType'];
					$obj['Evn_pid'] = $rows['Evn_pid'];
					$obj['EvnStick_pid'] = $rows['EvnStick_pid'];
					$obj['evnStickType'] = $rows['evnStickType'];
					$obj['EvnStick_closed'] = $rows['EvnStick_closed'];
					break;
				case 'EvnPrescrMse':
					$obj['EvnPrescrMse_id'] = $rows['EvnPrescrMse_id'];
					$obj['EvnVK_id'] = $rows['EvnVK_id'];
					$obj['TimetableMedService_id'] = $rows['TimetableMedService_id'];
					$obj['MedPersonal_sid'] = $rows['MedPersonal_sid'];
					$obj['Lpu_gid'] = $rows['Lpu_gid'];
					$obj['MedService_id'] = $rows['MedService_id'];
					break;
				case 'EvnMse':
					$obj['EvnPrescrMse_id'] = $rows['EvnPrescrMse_id'];
					$obj['MedService_id'] = $rows['MedService_id'];
					break;
				case 'EvnVK':
					$obj['MedService_id'] = $rows['MedService_id'];
					break;
				case 'EvnPS':
					$obj['deleteAccess'] = $rows['deleteAccess'];
					$obj['isRankin'] = $rows['DiagFinance_IsRankin'];
					// Флаг хирургических вмешательств для мобильного АРМа СМП
					if(isset($rows['withSurgery'])) $obj['surgery'] = $rows['withSurgery'];
					break;
				case 'EvnPL':
					//case 'EvnPLStom':
					$obj['LpuUnitSet_id'] = $rows['LpuUnitSet_id'];
					break;
			}
			
			$val[] = $obj;
		}

		return $val;
	}

	/**
	 * Получение данных для дерева истории лечения (ЭПЗ)
	 */
	private function getPersonEPHData() {
		/**
		 * Сравнение двух дат
		 * Даты могут быть в формате:
		 * 2012-03-13
		 * 2012-03-13 19:55
		 * 2012-03-13 19:55:00.000
		 * 13.03.2012
		 * 13.03.2012 23:05
		 * и др.
		 */
		function cmp($a, $b) {
			$t = strtotime($a['date']);
			$d1 = empty($t)?(empty($a['date'])?0:intval($a['date'])):$t;
			$t = strtotime($b['date']);
			$d2 = empty($t)?(empty($b['date'])?0:intval($b['date'])):$t;
			if ($d1 == $d2) {
				return 0;
			}
			return ($d1 > $d2) ? -1 : 1;
		}

		$region_nick = getRegionNick();

		$data = $this->ProcessInputData('getPersonEmkData', true);
		if ( $data === false ) { return false; }

		$archive_database_enable = $this->config->item('archive_database_enable');
		// если по архивным - используем архивную БД
		if (!empty($archive_database_enable) && !empty($_REQUEST['useArchive'])) {
			$this->load->database('archive', false);
		} else {
			$this->load->database();
		}

		$this->load->library('swFilterResponse'); 
		$this->load->model('CmpCallCard_model', 'CmpCallCard_model');
		$this->load->model('Evn_model', 'Evn_model');
		$this->load->model('EPH_model', 'EPH_model');
		$this->load->model('EMK_model', 'EMK_model'); // получает фейковые данные
		if (getRegionNick() == 'ufa')
			$this->load->model('PersonNewBorn_model', 'PersonNewBorn_model');

		$val = array();
		$val_new = array();
		$arr_1 = array();
		$arr_2 = array();
		$arr_3 = array();
		
		$EvnList = array();

		switch ( $data['ARMType'] ) {
			case 'common':
			case 'phys':
			case 'stom':
				$EvnList = array_merge($EvnList, array('SignalInformationAll',
					'EvnPL', 'EvnPS', 'EvnPLStom', 'EvnVizitPL', 'EvnVizitPLStom', 'EvnSection',
					'EvnUslugaStom', 'EvnUslugaPar', 'EvnUslugaTelemed', 'EvnDirection',
					'EvnPLDispDop', 'EvnPLDispOrp', 'EvnRecept', 'EvnReceptGeneral', 'MorbusOnkoVizitPLDop', 'MorbusOnkoLeave',
					'FreeDocument', 'EvnVK', 'EvnPrescrMse', 'EvnMse',
					'MedHisEpicrisis', 'EvnSurgery', 'MedHisMedicalCheckup',
					'RadioDiagnostic', 'InstrDiagnostic', 'LabDiagnostic', 'MedHisDocument',
					'EvnPLDispAdult', 'EvnPLDispChild',
					/*'EvnPLDispDop13', 'EvnPLDispProf', 'EvnPLDispOrp', 'EvnPLDispTeenInspection',*/ 'EvnPLDispMigrant', 'EvnPLDispDriver',
					in_array(getRegionNick(), array('ekb', 'perm', 'karelia')) ? 'CmpCallCard' : 'CmpCloseCard')
				);

				if ( in_array(getRegionNick(), array('kz')) ) {
					array_push($EvnList, 'EvnPLDispScreen');
					array_push($EvnList, 'EvnPLDispScreenChild');
				}
			break;
			case 'headBrigMobile':
				$EvnList = array_merge($EvnList, array('SignalInformationAll', 'EvnPL', 'EvnPS', 'EvnVizitPL', 'CmpCloseCard'));
			break;
			case 'par':
				$EvnList = array_merge($EvnList, array('EvnUslugaPar'));
			break;
			case 'stac':
				$EvnList = array('SignalInformationAll', 'MedHisEpicrisis', 'EvnSurgery', 'MedHisMedicalCheckup', 'RadioDiagnostic', 'InstrDiagnostic', 'LabDiagnostic', 'MedHisDocument');
			break;
			case 'headnurse':
				$EvnList = array('SignalInformationAll', 'EvnPS');
			break;
			case 'vk':
				$EvnList = array_merge($EvnList, array('SignalInformationAll',
					'EvnPL', 'EvnPS', 'EvnPLStom', 'EvnVizitPL', 'EvnVizitPLStom', 'EvnSection',
					'EvnUslugaStom', 'EvnUslugaPar', 'EvnUslugaTelemed', 'EvnDirection',
					'EvnPLDispDop', 'EvnPLDispOrp', 'EvnRecept', 'MorbusOnkoVizitPLDop', 'MorbusOnkoLeave',
					'FreeDocument', 'EvnVK', 'EvnPrescrMse', 'EvnMse',
					'MedHisEpicrisis', 'EvnSurgery', 'MedHisMedicalCheckup',
					'RadioDiagnostic', 'InstrDiagnostic', 'LabDiagnostic',
					'EvnPLDispAdult', 'EvnPLDispChild',
					'MedHisDocument', /*'EvnPLDispDop13', 'EvnPLDispProf', 'EvnPLDispOrp', 'EvnPLDispTeenInspection',*/ 'CmpCloseCard')
				);
			break;
		}

		if ( $data['node'] == 'root' ) {
			$data['level'] = 0;
		}

		if ( !empty($data['type']) ) {
			$this->_personEMKTreeType = $data['type'];
		}
		
		if ( !empty($data['level']) ) {
			$this->_personEMKTreeLevel = $data['level'];
		}
		
		$data['node'] = str_replace(array('EvnPL', 'EvnVizit', 'LpuUnit', 'Lpu', 'Building'), '', $data['node']);
		
		if ( $this->_personEMKTreeType == 0 || $this->_personEMKTreeLevel != 1 ) {
			$data[$data['object'].'_id'] = $data['object_id'];
		}
		else  {
			$data['Person_id'] = $data['object_id'];
		}

		if ( $this->_personEMKTreeLevel == 0 && $data['object_id'] == 0 ) {
			// Первый запрос при инициализации - возвращаем пустоту
			$this->ReturnData($val);
			return true;
		}

		// в ЭМК в режиме полки фильтр АПЛ и госпитализаций по диагнозу
		//if ( !empty($data['Diag_id']) && 'common' == $data['ARMType'] ) {
		if ( !empty($data['Diag_id']) && in_array($data['ARMType'], array('common', 'phys')) ) {
			//выводим SignalInformationAll, EvnPL, EvnPS по хронологии, независимо от положения переключателя #3537
			$this->_personEMKTreeType = 0;
			$EvnList = array('SignalInformationAll', 'EvnPL', 'EvnPS');
		}

		// Получаем список классов событий пациента
		if ( $this->_personEMKTreeType == 0 ) {
			if ( $data['node'] == 'root' || substr($data['object'], 0, 3) == 'Evn' ) {
				$this->_personEvnClassList = $this->Evn_model->getPersonEvnClassList(array(
					'Person_id' => $data['Person_id'],
					'Evn_pid' => (substr($data['object'], 0, 3) == 'Evn' ? $data['object_id'] : null),
				));
			}

			if ( in_array('CmpCallCard', $EvnList) ) {
				$CmpCallCardFlag = $this->CmpCallCard_model->checkPersonCmpCallCard($data['Person_id']);

				if ( !empty($CmpCallCardFlag) ) {
					$this->_personEvnClassList[] = 'CmpCallCard';
				}
			}

			if ( in_array('CmpCloseCard', $EvnList) ) {
				$CmpCloseCardFlag = $this->CmpCallCard_model->checkPersonCmpCloseCard($data['Person_id']);

				if ( !empty($CmpCloseCardFlag) ) {
					$this->_personEvnClassList[] = 'CmpCloseCard';
				}
			}

			if ( is_array($this->_personEvnClassList) ) {
				foreach ( $EvnList as $key => $value ) {
					if (
						($value == 'EvnPLDispAdult' && !in_array('EvnPLDispDop13', $this->_personEvnClassList) && !in_array('EvnPLDispProf', $this->_personEvnClassList) && !in_array('EvnPLDispMigrant', $this->_personEvnClassList) && !in_array('EvnPLDispDriver', $this->_personEvnClassList))
						|| ($value == 'EvnPLDispChild' && !in_array('EvnPLDispOrp', $this->_personEvnClassList) && !in_array('EvnPLDispTeenInspection', $this->_personEvnClassList))
						|| ($value == 'CmpCallCard' && !in_array('CmpCallCard', $this->_personEvnClassList))
						|| ($value == 'CmpCloseCard' && !in_array('CmpCloseCard', $this->_personEvnClassList))
						|| (!in_array($value, array('EvnPLDispAdult', 'EvnPLDispChild')) && substr($value, 0, 3) == 'Evn' && !in_array($value, $this->_personEvnClassList))
						
					) {
						unset($EvnList[$key]);
					}
				}
			}
		}

		$json = "";

		// Первый вариант вывода - Группировка по типам
		if ( $this->_personEMKTreeType == 1 ) {
			switch ( $this->_personEMKTreeLevel ) {
				case 0:
				{
					$grouptype = array();
					//if ($data['ARMType'] == 'common') {
					if (in_array($data['ARMType'], array('common', 'phys', 'vk'))) {
						$grouptype = array(
							array('id'=>9999999999, 'object' =>'SignalInformationAll', 'text' => 'Сигнальная информация','iconCls' => 'folder-info16'),
							array('id'=>9999999998, 'object' =>'CmpCloseCardGroup', 'text' => 'Случаи вызова СМП'), 
							// array('id'=>81111, 'object' =>'MedicalCheckup', 'text' => 'Осмотры (консультации)'),
							array('id'=>71114, 'object' =>'EvnStickAll', 'text' => 'Нетрудоспособность'),
							array('id'=>61116, 'object' =>'EvnDirection', 'text' => 'Направления'),
							/*array('id'=>60120, 'object' =>'EvnDirection1', 'text' => 'Направления на госпитализацию плановую'),
							array('id'=>60119, 'object' =>'EvnDirection2', 'text' => 'Направления на обследование'),
							array('id'=>60118, 'object' =>'EvnDirection3', 'text' => 'Направления на консультацию'),
							array('id'=>60117, 'object' =>'EvnDirection4', 'text' => 'Направления на восстановительное лечение'),
							array('id'=>60116, 'object' =>'EvnDirection5', 'text' => 'Направления на госпитализацию экстренную'),
							array('id'=>60115, 'object' =>'EvnDirection6', 'text' => 'Направления на осмотр с целью госпитализации'),
							array('id'=>60114, 'object' =>'EvnDirection7', 'text' => 'Направления на патологогистологическое исследование'),
							array('id'=>60113, 'object' =>'EvnDirection9', 'text' => 'Направления на ВК или МСЭ'),
							array('id'=>60112, 'object' =>'EvnDirection10', 'text' => 'Направления на исследование'),
							array('id'=>60110, 'object' =>'EvnDirection11', 'text' => 'Направления в консультационный кабинет'),
							array('id'=>60103, 'object' =>'EvnDirection15', 'text' => 'Направления в процедурный кабинет'),*/
							array('id'=>51117, 'object' =>'Research', 'text' => 'Результаты исследований'),
							// array('id'=>41111, 'object' =>'AppointmentPL', 'text' => 'Лист назначений'),
							array('id'=>31117, 'object' =>'EvnReceptList', 'text' => 'Рецепты'),
							array('id'=>31118, 'object' =>'EvnReceptGeneralList', 'text' => 'Общие рецепты'),
							//array('id'=>31115, 'object' =>'EvnPrescrMseList', 'text' => 'Направления на МСЭ'),
							array('id'=>21111, 'object' =>'EvnSurgery', 'text' => 'Оперативное лечение'),
							// array('id'=>11119, 'object' =>'EpicrisisPL', 'text' => 'Эпикризы (поликлиника)'),
							//array('id'=>11118, 'object' =>'PersonDocuments', 'text' => 'Документы и справки'),
							array('id'=>11115, 'object' =>'EvnPLList', 'text' => 'Случаи амбулаторно-поликлинического лечения'), 
							array('id'=>11122, 'object' =>'EvnPLStomList', 'text' => 'Случаи стоматологического лечения'), 
							array('id'=>11114, 'object' =>'EvnPSList', 'text' => 'Случаи госпитализации'), 
							array('id'=>11113, 'object' =>'EvnPLDispAdultList', 'text' => 'Случаи диспансеризации/мед.осмотров взрослых'),
							array('id'=>11111, 'object' =>'EvnPLDispChildList', 'text' => 'Случаи диспансеризации/мед.осмотров несовершеннолетних')
							// array('id'=>11112, 'object' =>'EvnSanCur', 'text' => 'Санаторно-курортное лечение')
						);

						if ( $data['session']['region']['nick'] != 'ufa' ) {
							// array_push($grouptype, array('id'=>11110, 'object' =>'EvnUslugaAll', 'text' => 'Услуги'));
						}
					}
					if ($data['ARMType'] == 'stom') {
						$grouptype = array(
							array('id'=>9999999998, 'object' =>'CmpCloseCardGroup', 'text' => 'Случаи вызова СМП'), 
							array('id'=>11123, 'object' =>'EvnPLList', 'text' => 'Случаи амбулаторно-поликлинического лечения'), 
							array('id'=>11122, 'object' =>'EvnPLStomList', 'text' => 'Случаи стоматологического лечения'), 
							array('id'=>11121, 'object' =>'EvnPLDispDopList', 'text' => 'Случаи дополнительной диспансеризации'),
							array('id'=>11120, 'object' =>'EvnPLDispOrpList', 'text' => 'Случаи диспансеризации детей-сирот'), 
							array('id'=>11119, 'object' =>'EvnVizitPL', 'text' => 'Посещения'), 
							array('id'=>11118, 'object' =>'EvnReceptList', 'text' => 'Рецепты'),

							array('id'=>11117, 'object' =>'EvnDirection', 'text' => 'Направления'),
							/*array('id'=>10120, 'object' =>'EvnDirection1', 'text' => 'Направления на госпитализацию плановую'),
							array('id'=>10119, 'object' =>'EvnDirection2', 'text' => 'Направления на обследование'),
							array('id'=>10118, 'object' =>'EvnDirection3', 'text' => 'Направления на консультацию'),
							array('id'=>10117, 'object' =>'EvnDirection4', 'text' => 'Направления на восстановительное лечение'),
							array('id'=>10116, 'object' =>'EvnDirection5', 'text' => 'Направления на госпитализацию экстренную'),
							array('id'=>10115, 'object' =>'EvnDirection6', 'text' => 'Направления на осмотр с целью госпитализации'),
							array('id'=>10114, 'object' =>'EvnDirection7', 'text' => 'Направления на патологогистологическое исследование'),
							array('id'=>10113, 'object' =>'EvnDirection9', 'text' => 'Направления на ВК или МСЭ'),
							array('id'=>10112, 'object' =>'EvnDirection10', 'text' => 'Направления на исследование'),
							array('id'=>10110, 'object' =>'EvnDirection11', 'text' => 'Направления в консультационный кабинет'),
							array('id'=>10103, 'object' =>'EvnDirection15', 'text' => 'Направления в процедурный кабинет'),*/

							// array('id'=>10016, 'object' =>'EvnUsluga', 'text' => 'Услуги'),
							array('id'=>10015, 'object' =>'EvnUslugaStom', 'text' => 'Услуги по стоматологии'),
							//array('id'=>10014, 'object' =>'PersonDisp', 'text' => 'Диспансеризация'),
							//array('id'=>10013, 'object' =>'PersonPrivilege', 'text' => 'Льготы'),
							//array('id'=>10012, 'object' =>'PersonCard', 'text' => 'Прикрепление'),
							array('id'=>10011, 'object' =>'EvnUslugaPar', 'text' => 'Параклинические услуги'),
							array('id'=>10000, 'object' =>'EvnStickAll', 'text' => 'Нетрудоспособность')
						);
					}
					if ($data['ARMType'] == 'par') {
						$grouptype = array(
							array('id'=>9999999001, 'object' =>'EvnUslugaPar', 'text' => 'Параклинические услуги')
						);
					}
					if ($data['ARMType'] == 'stac') {
						// MedHisRecordReceptionist Запись врача приемного отделения
						$childrens = $this->EMK_model->GetMedHisRecordReceptionistNodeList($data);
						$childrens = $this->_formNameNode('SignalInformationAll', $childrens, $data['ARMType']);
						$field = Array('object' => "MedHisRecordReceptionist", 'id' => "MedHisRecordReceptionist_id", 'name' => 'Name', 'iconCls' => 'evn-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);

						$grouptype = array(
							//array('id'=>91111, 'object' =>'MedHisTitul', 'text' => 'Титульный лист', 'leaf' => true),
							array('id'=>9999999999, 'object' =>'SignalInformationAll', 'text' => 'Сигнальная информация', 'iconCls' =>'folder-info16'),
							array('id'=>9999999998, 'object' =>'CmpCloseCardGroup', 'text' => 'Случаи вызова СМП'), 
							//array('id'=>71111, 'object' =>'MedHisRecordReceptionist', 'text' => 'Запись врача приемного отделения', 'leaf' => true),
							// array('id'=>9999999044, 'object' =>'MedHisMedicalCheckup', 'text' => 'Осмотры (консультации)'),
							array('id'=>9999999037, 'object' =>'MedHisDiagnos', 'text' => 'Диагнозы'),
							array('id'=>9999999035, 'object' =>'MedHisDiaryEntrie', 'text' => 'Дневниковые записи'),
							array('id'=>9999999030, 'object' =>'EvnSurgery', 'text' => 'Инвазивные вмешательства'),
							array('id'=>9999999025, 'object' =>'MedHisAppointment', 'text' => 'Лечебные назначения'),
							array('id'=>9999999021, 'object' =>'MedHisDirection', 'text' => 'Направления'),
							array('id'=>9999999011, 'object' =>'MedHisEvnSection', 'text' => 'Движение по отделениям'),
							array('id'=>9999999009, 'object' =>'MedHisResearchAll', 'text' => 'Результаты исследований'),
							// array('id'=>9999999007, 'object' =>'MedHisEpicrisis', 'text' => 'Эпикризы'),
							array('id'=>9999999005, 'object' =>'MedHisNotices', 'text' => 'Извещения'),
							array('id'=>9999999003, 'object' =>'EvnStickAll', 'text' => 'Нетрудоспособность'),
							array('id'=>9999999001, 'object' =>'MedHisDocument', 'text' => 'Документы')
							);
					}
					if ($data['ARMType'] == 'headnurse') {
						$grouptype = array(
							array('id'=>9999999999, 'object' =>'SignalInformationAll', 'text' => 'Сигнальная информация','iconCls' => 'folder-info16'),
							array('id'=>9999999998, 'object' =>'CmpCloseCardGroup', 'text' => 'Случаи вызова СМП'), 
							array('id'=>11114, 'object' =>'EvnPSList', 'text' => 'Случаи госпитализации')
							);
					}
					//Группировка по типам
					$field = Array(
						'object' => "GroupByType",
						'person_id'=>$data['object_id'],
						'id' => "id", 
						'name' => 'Name', 
						'iconCls' => 
						'evn-16', 
						'leaf' => false, 
						'cls' => "folder"
						);
					$archive_database_enable = $this->config->item('archive_database_enable');
					// если по архивным - обрабатываем список групп, чтобы исключить лишние и чтобы не было одинаковых идешников
					if (!empty($archive_database_enable) && !empty($_REQUEST['useArchive'])) {
						foreach ($grouptype as $key => $onegroup) {
							if (!in_array($onegroup['object'], array('EvnPSList', 'EvnPLList', 'CmpCloseCardGroup', 'EvnMseList', 'EvnUslugaAll', 'EvnUslugaPar', 'EvnUsluga', 'EvnUslugaStom', 'EvnReceptList', 'EvnPLStom', 'EvnVizitPL', 'EvnStickAll')) && mb_strpos($onegroup['object'], 'EvnDirection') === false) {
								unset($grouptype[$key]);
							} else {
								$grouptype[$key]['id'] = $grouptype[$key]['id'].'_arch';
							}
						}
					}
					$childrens = $this->_formNameNode('GroupByType', $grouptype, $data['ARMType']);
					$arr_2 = $this->_getPersonEPHChild($childrens, $field);
					$arr_1 = array_merge($arr_1,$arr_2);
					break;
				}
				case 1: 
				{
					if ($data['object'] == 'MedHisDocument')
					{
						// EvnDocument Документы по случаю госпитализации
						$childrens = $this->EMK_model->GetEvnDocumentNodeList($data);
						$childrens = $this->_formNameNode('EvnDocument', $childrens, $data['ARMType']);
						$field = Array('object' => 'EvnDocument','id' => 'EvnDocument_id', 'name' => 'Name', 'iconCls' => 'evn-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if ($data['object'] == 'EvnPSList')
					{
						// EvnPS Случаи госпитализации
						$childrens = $this->EPH_model->GetEvnPSNodeList($data);
						$childrens = $this->_formNameNode('EvnPS', $childrens, $data['ARMType']);
						$field = Array('object' => 'EvnPS','id' => 'EvnPS_id', 'name' => 'Name', 'iconCls' => 'evn-16', 'leaf' => false, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if ($data['object'] == 'EvnSurgery')
					{
						// ProtocolSurgery  
						$childrens = $this->EMK_model->GetProtocolSurgeryNodeList($data);
						$childrens = $this->_formNameNode('ProtocolSurgery', $childrens, $data['ARMType']);
						$field = Array('object' => "ProtocolSurgery", 'id' => "ProtocolSurgery_id", 'name' => 'Name', 'iconCls' => 'evn-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if ($data['object'] == 'MedHisEpicrisis')
					{
						// Epicrisis эпикризы стационара
						$childrens = $this->EMK_model->GetEpicrisisNodeList($data);
						$childrens = $this->_formNameNode('Epicrisis', $childrens, $data['ARMType']);
						$field = Array('object' => "Epicrisis", 'id' => "Epicrisis_id", 'name' => 'Name', 'iconCls' => 'evn-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if ($data['object'] == 'EvnPLDispChildList' && in_array('EvnPLDispChildList', $EvnList))
					{
						// Диспансеризация
						$childrens = $this->EPH_model->GetEvnPLDispAdultNodeList($data);
						$childrens = $this->_formNameNode('EvnPLDisp', $childrens, $data['ARMType']);
						$field = Array('object' => "EvnPLDispAdult",'id' => "EvnPLDisp_id", 'name' => 'Name', 'iconCls' => 'folder', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if ($data['object'] == 'EvnPLDispAdultList')
					{
						// Диспансеризация
						$childrens = $this->EPH_model->GetEvnPLDispAdultNodeList($data);
						$childrens = $this->_formNameNode('EvnPLDispAdult', $childrens, $data['ARMType']);
						$field = Array('object' => "EvnPLDispAdult",'id' => "EvnPLDisp_id", 'name' => 'Name', 'iconCls' => 'folder', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if ($data['object'] == 'EvnPLDispChildList' && in_array('EvnPLDispChild', $EvnList))
					{
						// Диспансеризация
						$childrens = $this->EPH_model->GetEvnPLDispChildNodeList($data);
						$childrens = $this->_formNameNode('EvnPLDispChild', $childrens, $data['ARMType']);
						$field = Array('object' => "EvnPLDisp",'id' => "EvnPLDisp_id", 'name' => 'Name', 'iconCls' => 'folder', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if ($data['object'] == 'MedHisMedicalCheckup')
					{
						// MedicalCheckup Осмотры специалистов
						$childrens = $this->EMK_model->GetMedicalCheckupNodeList($data);
						$childrens = $this->_formNameNode('MedicalCheckup', $childrens, $data['ARMType']);
						$field = Array('object' => "MedicalCheckup",'id' => "MedicalCheckup_id",'name' => 'Name','iconCls' => 'folder', 'leaf' => true, 'cls' => "folder");
						/*
						$childrens = array(
							array('id'=>41, 'object' =>'EvnVisitCardiolog', 'Name' => 'Кардиолог', 'order' => 51), 
							array('id'=>42, 'object' =>'EvnVisitNeurolog', 'Name' => 'Невролог', 'order' => 50), 
							array('id'=>43, 'object' =>'EvnVisitTherapist', 'Name' => 'Терапевт', 'order' => 49), 
							array('id'=>44, 'object' =>'EvnVisitSurgeon', 'Name' => 'Хирург', 'order' => 48), 
							array('id'=>45, 'object' =>'EvnVisitOphthalmolog', 'Name' => 'Офтальмолог', 'order' => 47)
						);
						$childrens = $this->_formNameNode('MedicalCheckup', $childrens, $data['ARMType']);
						$field = Array('object' => "MedicalCheckup",'id' => "id",'name' => 'Name','iconCls' => 'folder', 'leaf' => false, 'cls' => "folder");
						*/
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if ($data['object'] == 'EvnStickAll')
					{
						// Все документы по трудопотерям
						$childrens = array(
							array('id'=>34, 'object' =>'EvnStickList', 'Name' => 'Листы нетрудоспособности', 'order' => 9),
							array('id'=>78, 'object' =>'EvnVKList', 'Name' => 'Протоколы ВК', 'order' => 6),
							array('id'=>99, 'object' =>'EvnMseList', 'Name' => 'Протоколы МСЭ', 'order' => 5)
						);
						if (!empty($archive_database_enable) && !empty($_REQUEST['useArchive'])) {
							foreach ($childrens as $key => $onegroup) {
								if (!in_array($onegroup['object'], array('EvnStickList'))) {
									unset($childrens[$key]);
								} else {
									$childrens[$key]['id'] = $childrens[$key]['id'].'_arch';
								}
							}
						}
						$childrens = $this->_formNameNode('EvnStickAll', $childrens, $data['ARMType']);
						$field = Array('object' => "EvnStickAll",'id' => 'id','name' => 'Name','iconCls' => 'folder', 'leaf' => false, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if ($data['object'] == 'Research') // все 
					{
						// Результаты исследований
						$arr_1 = $this->_mergeNodesData('EvnUslugaPar', $data, $arr_1);
					}
					if ($data['object'] == 'EvnUslugaPar' && in_array('EvnUslugaPar', $EvnList))
					{
						$arr_1 = $this->_mergeNodesData('EvnUslugaPar', $data, $arr_1);
					}
					if ($data['object'] == 'EvnPrescrMseList')
					{
						// EvnPrescrMse Направления на МСЭ
						$childrens = $this->EPH_model->GetEvnPrescrMseNodeList($data);
						$childrens = $this->_formNameNode('EvnPrescrMse', $childrens, $data['ARMType']);
						$field = Array('object' => 'EvnPrescrMse','id' => 'EvnPrescrMse_id', 'name' => 'Name', 'iconCls' => 'evn-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if ($data['object'] == 'EvnDirection')
					{
						//Электронные направления без фильтрации по типу
						$arr_1 = $this->_mergeNodesData('EvnDirection', $data, $arr_1);
					}
					if ($data['object'] == 'EvnDirection1') {
						//Направления на госпитализацию плановую
						$data['DirType_id'] = 1;
						$arr_1 = $this->_mergeNodesData('EvnDirection', $data, $arr_1);
					}
					if ($data['object'] == 'EvnDirection2') {
						//Направления на обследование
						$data['DirType_id'] = 2;
						$arr_1 = $this->_mergeNodesData('EvnDirection', $data, $arr_1);
					}
					if ($data['object'] == 'EvnDirection3') {
						//Направления на консультацию
						$data['DirType_id'] = 3;
						$arr_1 = $this->_mergeNodesData('EvnDirection', $data, $arr_1);
					}
					if ($data['object'] == 'EvnDirection4') {
						//Направления на восстановительное лечение
						$data['DirType_id'] = 4;
						$arr_1 = $this->_mergeNodesData('EvnDirection', $data, $arr_1);
					}
					if ($data['object'] == 'EvnDirection5') {
						//Направления на госпитализацию экстренную
						$data['DirType_id'] = 5;
						$arr_1 = $this->_mergeNodesData('EvnDirection', $data, $arr_1);
					}
					if ($data['object'] == 'EvnDirection6') {
						//Направления на осмотр с целью госпитализации
						$data['DirType_id'] = 6;
						$arr_1 = $this->_mergeNodesData('EvnDirection', $data, $arr_1);
					}
					if ($data['object'] == 'EvnDirection7') {
						//Направления на патологогистологическое исследование
						$data['DirType_id'] = 7;
						$arr_1 = $this->_mergeNodesData('EvnDirection', $data, $arr_1);
					}
					if ($data['object'] == 'EvnDirection9') {
						//Направления на ВК или МСЭ
						$data['DirType_id'] = 9;
						$arr_1 = $this->_mergeNodesData('EvnDirection', $data, $arr_1);
					}
					if ($data['object'] == 'EvnDirection10') {
						//Направления на исследование
						$data['DirType_id'] = 10;
						$arr_1 = $this->_mergeNodesData('EvnDirection', $data, $arr_1);
					}
					if ($data['object'] == 'EvnDirection11') {
						//Направления в консультационный кабинет
						$data['DirType_id'] = 11;
						$arr_1 = $this->_mergeNodesData('EvnDirection', $data, $arr_1);
					}
					if ($data['object'] == 'EvnDirection15') {
						//Направления в процедурный кабинет
						$data['DirType_id'] = 15;
						$arr_1 = $this->_mergeNodesData('EvnDirection', $data, $arr_1);
					}
					if ($data['object'] == 'MedHisResearchAll')
					{
						// Результаты исследований по случаю госпитализации
						$childrens = array(
							array('Name' => "Лабораторная диагностика",'object' =>'LabDiagnostic', "id" => 10, 'order' => 3),
							array('Name'=>"Инструментальная диагностика",'object' =>'InstrDiagnostic',"id" => 11,'order'=>2),
							array('Name' => "Лучевая диагностика",'object' =>'RadioDiagnostic', "id" => 12, 'order' => 1)
						);
						$childrens = $this->_formNameNode('MedHisResearchAll', $childrens, $data['ARMType']);
						$field = Array('object' => "MedHisResearchAll",'id' => "id",'name' => 'Name','iconCls' => 'folder', 'leaf' => false, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if ($data['object'] == 'SignalInformationAll')
					{
						/*
						// Anthropometry
						$childrens = $this->EMK_model->GetAnthropometryNodeList($data);
						$childrens = $this->_formNameNode('SignalInformationAll', $childrens, $data['ARMType']);
						$field = Array('object' => "Anthropometry", 'id' => "Anthropometry_id", 'name' => 'Name', 'iconCls' => 'evn-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
						// BloodData
						$childrens = $this->EMK_model->GetBloodDataNodeList($data);
						$childrens = $this->_formNameNode('SignalInformationAll', $childrens, $data['ARMType']);
						$field = Array('object' => "BloodData", 'id' => "BloodData_id", 'name' => 'Name', 'iconCls' => 'evn-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
						// AllergHistory
						$childrens = $this->EMK_model->GetAllergHistoryNodeList($data);
						$childrens = $this->_formNameNode('SignalInformationAll', $childrens, $data['ARMType']);
						$field = Array('object' => "AllergHistory", 'id' => "AllergHistory_id", 'name' => 'Name', 'iconCls' => 'evn-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
						// LifeHistory
						$childrens = $this->EMK_model->GetLifeHistoryNodeList($data);
						$childrens = $this->_formNameNode('SignalInformationAll', $childrens, $data['ARMType']);
						$field = Array('object' => "LifeHistory", 'id' => "LifeHistory_id", 'name' => 'Name', 'iconCls' => 'evn-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
						*/
						// Сигнальная информация папки со списками
						$childrens = array(
							array('Name' => 'Анамнез жизни', 'object' => 'PersonMedHistory', 'Person_id' =>$data['Person_id'], 'order' => 15, 'leaf' => true),
							array('Name' => 'Антропометрические данные', 'object' => 'Anthropometry', 'Person_id' =>$data['Person_id'], 'order' => 5, 'leaf' => true),
							array('Name' => "Способ вскармливания", 'object' => 'FeedingType','Person_id' =>$data['Person_id'], 'order' => 4, 'leaf' => true),
							array('Name' => "Группа крови и резус фактор", 'object' => 'BloodData', 'Person_id' =>$data['Person_id'], 'order' => 14, 'leaf' => true),
							array('Name' => "Аллергологический анамнез", 'object' => 'AllergHistory','Person_id' =>$data['Person_id'], 'order' => 13, 'leaf' => true),
							// array('Name' => "Анамнез жизни", 'object' => 'LifeHistory', 'Person_id' =>$data['Person_id'], 'order' => 11),
							// array('Name' => "Анамнез заболевания", 'object' => 'SicknessAnamnezData', 'Person_id' =>$data['Person_id'], 'order' => 10),
							array('Name' => "Список уточненных диагнозов", 'object' => 'DiagList', 'Person_id' =>$data['Person_id'], 'order' => 9, 'leaf' => true),
							array('Name' => 'Свидетельства', 'object' => 'PersonSvidInfo', 'Person_id' => $data['Person_id'], 'order' => 12, 'leaf' => true),
							array('Name' => "Диспансерный учет", 'object' => 'PersonDispInfo', 'Person_id' =>$data['Person_id'], 'order' => 11, 'leaf' => true),
							array('Name' => "Диспансеризация/мед. осмотры", 'object' => 'EvnPLDispInfo', 'Person_id' =>$data['Person_id'], 'order' => 10, 'leaf' => true),
							// array('Name' => "Перенесенные инфекционные заболевания", 'object' => 'InfectDiseases', 'Person_id' =>$data['Person_id'], 'order' => 7),
							array('Name' => "Экспертный анамнез и льготы", 'object' => 'ExpertHistory', 'Person_id' =>$data['Person_id'], 'order' => 12, 'leaf' => true),
							// array('Name' => "Вакцинопрофилактика", 'object' => 'Vaccine', 'Person_id' =>$data['Person_id'], 'order' => 5),
							// array('Name' => "Флюоротека", 'object' => 'Flyuoroteka', 'Person_id' =>$data['Person_id'], 'order' => 4),
							// array('Name' => "Лист лучевой нагрузки", 'object' => 'RadioDose', 'Person_id' =>$data['Person_id'], 'order' => 3),
							// array('Name' => "Список направлений на госпитализацию", 'object' => 'HospitDirectList', 'Person_id' =>$data['Person_id'], 'order' => 2),
							array('Name' => "Список оперативных вмешательств", 'object' => 'SurgicalList', 'Person_id' =>$data['Person_id'], 'order' => 7, 'leaf' => true),
							array('Name' => "Список отмененных направлений", 'object' => 'DirFailList', 'Person_id' =>$data['Person_id'], 'order' => 6, 'leaf' => true),
							//array('Name' => "Исполненные привики", 'object' => 'Inoculation', 'Person_id' =>$data['Person_id'], 'order' => 2, 'leaf' => true),
							array('Name' => "Список открытых ЛВН", 'object' => 'EvnStickOpenInfo', 'Person_id' => $data['Person_id'], 'order' => 4, 'leaf' => true),
							array('Name' => "Список опросов", 'object' => 'PersonOnkoProfileInfo', 'Person_id' => $data['Person_id'], 'order' => 4, 'leaf' => true),
						);
						if ( $data['session']['region']['nick'] == 'vologda' ){
							array_push($childrens,
								array('Name' => "Список ЛС, заявленных в рамках ЛЛО", 'object' => 'PersonDrugRequestInfo', 'Person_id' => $data['Person_id'], 'order' => 4, 'leaf' => true)
							);
						}

						// #182475
						// Для регионов 'ufa' и 'vologda' присутствует три пункта о прививках,
						// заголовок пункта MantuReaction зависит от региона:
						if ($region_nick == 'ufa' ||  $region_nick == 'vologda')
							array_push($childrens,
								array(
									'Name' => "Исполненные прививки",
									'object' => 'Inoculation',
									'Person_id' =>$data['Person_id'],
									'order' => 3,
									'leaf' => true),
								array(
									'Name' => "Планируемые прививки",
									'object' => 'InoculationPlan',
									'Person_id' =>$data['Person_id'],
									'order' => 2,
									'leaf' => true),
								array(
									'Name' => ($region_nick == 'ufa' ? "Реакция Манту" : "Манту/Диаскинтест"),
									'object' => 'MantuReaction',
									'Person_id' =>$data['Person_id'],
									'order' => 1,
									'leaf' => true));

						if ($region_nick != 'kz') {
							$childrens[] = [ 'Name'=>'Список контрольных карт по карантину', 'object' => 'PersonQuarantine', 'Person_id' => $data['Person_id'], 'order'=>1, 'leaf' => true ];
						}

						$childrens = $this->_formNameNode('SignalInformationAll', $childrens, $data['ARMType']);
						$field = Array('object' => 'SignalInformationAll','Person_id' =>$data['Person_id'],'id' => 'Person_id','name' => 'Name','iconCls' => 'info16', 'leaf' => false, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					/*
					if ($data['object'] == 'PersonDocuments' && in_array('PersonDocuments', $EvnList))
					{
						// PersonDocuments - Документы человека
						$childrens = array(
							array("PersonDocumentsGroup_Name" => "Cвидетельства", "PersonDocumentsGroup_id" => 1),
							array("PersonDocumentsGroup_Name" => "Справки", "PersonDocumentsGroup_id" => 2)
						);
						$childrens = $this->_formNameNode('PersonDocumentsGroup', $childrens, $data['ARMType']);
						$field = Array('object' => "PersonDocumentsGroup",'id' => "PersonDocumentsGroup_id", 'name' => "PersonDocumentsGroup_Name", 'iconCls' => 'folder', 'leaf' => false, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					*/
					if ($data['object'] == 'EvnPLList' && in_array('EvnPL', $EvnList))
					{
						// EvnPL - Случаи амбулаторно-поликлинического лечения 
						$childrens = $this->EPH_model->GetEvnPLNodeList($data);
						$childrens = $this->_formNameNode('EvnPL', $childrens, $data['ARMType']);
						$field = Array('object' => "EvnPL",'id' => "EvnPL_id", 'name' => 'Name', 'iconCls' => 'evnpl-16', 'leaf' => false, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if ($data['object'] == 'EvnPLStomList' && in_array('EvnPLStom', $EvnList))
					{
						// EvnPLStom - Случаи стоматологического лечения 
						$childrens = $this->EPH_model->GetEvnPLStomNodeList($data);
						$childrens = $this->_formNameNode('EvnPLStom', $childrens, $data['ARMType']);
						$field = Array('object' => "EvnPLStom",'id' => "EvnPLStom_id", 'name' => 'Name', 'iconCls' => 'evnpl-16', 'leaf' => false, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if ($data['object'] == 'CmpCloseCardGroup' && in_array('CmpCloseCard', $EvnList))
					{
						// CmpCloseCard - Случаи СМП
						$childrensCCC = $this->EPH_model->GetCmpCloseCard($data);
						$childrensCCC = $this->_formNameNode('CmpCloseCard', $childrensCCC, $data['ARMType']);							
						$fieldCCC = Array('object' => "CmpCloseCard",'id' => "CmpCloseCard_id",'CmpCallCard_id' => "CmpCallCard_id", 'name' => 'Name', 'iconCls' => 'evnpl-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrensCCC, $fieldCCC);
						
						if ($this->EPH_model->getRegionNick() == 'ufa'){
							$arr_1 = array_merge($arr_1,$arr_2);
						} else {
							// CmpCallCard - Случаи СМП
							$childrensCC = $this->EPH_model->GetCmpCallCard($data);
							$childrensCC = $this->_formNameNode('CmpCallCard', $childrensCC, $data['ARMType']);
							$fieldCC = Array('object' => "CmpCallCard",'id' => "CmpCallCard_id", 'name' => 'Name', 'iconCls' => 'evnpl-16', 'leaf' => false, 'cls' => "folder");
							$arr_3 = $this->_getPersonEPHChild($childrensCC, $fieldCC);
							
							$arr_1 = array_merge($arr_1,$arr_2, $arr_3);
						}
					}
					if ($data['object'] == 'EvnVizitPLStom' && in_array('EvnVizitPLStom', $EvnList))
					{
						// EvnVizitPLStom - 
						$childrens = $this->EPH_model->GetEvnVizitPLStomNodeList($data);
						$childrens = $this->_formNameNode('EvnVizitPLStom', $childrens, $data['ARMType'], $data['session']['region']['nick']);
						$field = Array('object' => "EvnVizitPLStom",'id' => "EvnVizitPLStom_id", 'name' => 'Name', 'iconCls' => 'evnpl-16', 'leaf' => false, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					
					if ($data['object'] == 'EvnVizitPL' && in_array('EvnVizitPL', $EvnList))
					{
						// EvnVizit - Посещения (все)
						// EvnVizitPL
						$childrens = $this->EPH_model->GetEvnVizitPLNodeList($data);
						$childrens = $this->_formNameNode('EvnVizitPL', $childrens, $data['ARMType'], $data['session']['region']['nick']);
						$field = Array('object' => "EvnVizitPL", 'id' => "EvnVizitPL_id", 'name' => 'Name', 'iconCls' => 'evnvizit-16', 'leaf' => false, 'cls' => "folder");
						$arr_1 = $this->_getPersonEPHChild($childrens, $field);
						// EvnVizitDispDop
						$childrens = $this->EPH_model->GetEvnVizitDispDopNodeList($data);
						$childrens = $this->_formNameNode('EvnVizitDispDop', $childrens, $data['ARMType']);
						$field = Array('object' => "EvnVizitDispDop", 'id' => "EvnVizitDispDop_id", 'name' => 'Name', 'iconCls' => 'evnvizit-16', 'leaf' => false, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
						
						$childrens = $this->EPH_model->GetEvnVizitPLStomNodeList($data);
						$childrens = $this->_formNameNode('EvnVizitPLStom', $childrens, $data['ARMType'], $data['session']['region']['nick']);
						$field = Array('object' => "EvnVizitPLStom", 'id' => "EvnVizitPLStom_id", 'name' => 'Name', 'iconCls' => 'evnvizit-16', 'leaf' => false, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);

					}
					if ($data['object'] == 'EvnReceptList' && in_array('EvnRecept', $EvnList))
					{
						// EvnRecept
						$childrens = $this->EPH_model->GetEvnReceptNodeList($data);
						$childrens = $this->_formNameNode('EvnRecept', $childrens, $data['ARMType']);
						$field = Array('object' => "EvnRecept", 'id' => "EvnRecept_id", 'name' => 'Name', 'iconCls' => 'lgot16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if ($data['object'] == 'EvnReceptGeneralList' && in_array('EvnReceptGeneral', $EvnList))
					{
						// EvnRecept
						$childrens = $this->EPH_model->GetEvnReceptGeneralNodeList($data);
						$childrens = $this->_formNameNode('EvnReceptGeneral', $childrens, $data['ARMType']);
						$field = Array('object' => "EvnReceptGeneral", 'id' => "EvnReceptGeneral_id", 'name' => 'Name', 'iconCls' => 'lgot16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if ($data['object'] == 'FreeDocument' && in_array('FreeDocument', $EvnList))
					{
						// FreeDocument
						$childrens = $this->EPH_model->GetEvnVizitPLFreeDocumentNodeList($data);
						$childrens = $this->_formNameNode('FreeDocument', $childrens, $data['ARMType']);
						$field = Array('object' => "FreeDocument", 'id' => "FreeDocument_id", 'name' => 'Name', 'iconCls' => 'lgot16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if ($data['object'] == 'EvnUsluga' && in_array('EvnUsluga', $EvnList))
					{
						// EvnUsluga
						$childrens = $this->EPH_model->GetEvnUslugaNodeList($data);
						$childrens = $this->_formNameNode('EvnUsluga', $childrens, $data['ARMType']);
						$field = Array('object' => "EvnUsluga", 'id' => "EvnUsluga_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
						$arr_1 = $this->_getPersonEPHChild($childrens, $field);
						// EvnUslugaDispDop
						$childrens = $this->EPH_model->GetEvnUslugaDispDopNodeList($data);
						$childrens = $this->_formNameNode('EvnUslugaDispDop', $childrens, $data['ARMType']);
						$field = Array('object' => "EvnUslugaDispDop", 'id' => "EvnUslugaDispDop_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					/*
					if ($data['object'] == 'PersonDisp' && in_array('PersonDisp', $EvnList))
					{
						// PersonDisp - Диспансеризация: постановка
						$childrens = $this->EPH_model->GetBegPersonDispNodeList($data);
						$childrens = $this->_formNameNode('BegPersonDisp', $childrens, $data['ARMType']);
						$field = Array('object' => "PersonDisp",'id' => "PersonDisp_id", 'name' => 'Name', 'iconCls' => 'disp16', 'leaf' => true, 'cls' => "folder");
						$arr_1 = $this->_getPersonEPHChild($childrens, $field, "Beg");
						// PersonDisp - Диспансеризация: снятие
						$childrens = $this->EPH_model->GetEndPersonDispNodeList($data);
						$childrens = $this->_formNameNode('EndPersonDisp', $childrens, $data['ARMType']);
						$field = Array('object' => "PersonDisp",'id' => "PersonDisp_id", 'name' => 'Name', 'iconCls' => 'disp16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field, "End");
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if ($data['object'] == 'PersonPrivilege' && in_array('PersonPrivilege', $EvnList))
					{
						// PersonPrivilege - – Льгота: открытие
						$childrens = $this->EPH_model->GetBegPersonPrivilegeNodeList($data);
						$childrens = $this->_formNameNode('BegPersonPrivilege', $childrens, $data['ARMType']);
						$field = Array('object' => "PersonPrivilege",'id' => "PersonPrivilege_id", 'name' => 'Name', 'iconCls' => 'evnpl-16', 'leaf' => true, 'cls' => "folder");
						$arr_1 = $this->_getPersonEPHChild($childrens, $field, "Beg");
						// PersonPrivilege – Льгота: закрытие 
						$childrens = $this->EPH_model->GetEndPersonPrivilegeNodeList($data);
						$childrens = $this->_formNameNode('EndPersonPrivilege', $childrens, $data['ARMType']);
						$field = Array('object' => "PersonPrivilege",'id' => "PersonPrivilege_id", 'name' => 'Name', 'iconCls' => 'evnpl-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field, "End");
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if ($data['object'] == 'PersonCard' && in_array('PersonCard', $EvnList))
					{
						// PersonCard - Прикрепление - (Прикрепление, дата прикрепления, ЛПУ) 
						$childrens = $this->EPH_model->GetPersonCardBegNodeList($data);
						$childrens = $this->_formNameNode('BegPersonCard', $childrens, $data['ARMType']);
						$field = Array('object' => "PersonCard",'id' => "PersonCard_id", 'name' => 'Name', 'iconCls' => 'evnpl-16', 'leaf' => true, 'cls' => "folder");
						$arr_1 = $this->_getPersonEPHChild($childrens, $field, "Beg");
						// PersonCard - Открепление - (Открепление, дата открепления, ЛПУ) 
						$childrens = $this->EPH_model->GetPersonCardEndNodeList($data);
						$childrens = $this->_formNameNode('EndPersonCard', $childrens, $data['ARMType']);
						$field = Array('object' => "PersonCard",'id' => "PersonCard_id", 'name' => 'Name', 'iconCls' => 'evnpl-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field, "End");
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					*/
					if ($data['object'] == 'EvnUslugaStom' && in_array('EvnUslugaStom', $EvnList))
					{
						// EvnUsluga - Стоматологическая услуга на человека 
						$childrens = $this->EPH_model->GetEvnUslugaStomNodeList($data);
						$childrens = $this->_formNameNode('EvnUslugaStom', $childrens, $data['ARMType']);
						$field = Array('object' => "EvnUslugaStom", 'id' => "EvnUslugaStom_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					break;
				}
				case 2:
					if ($data['object'] == 'EvnVKList')
					{
						// EvnVK Протоколы ВК
						$childrens = $this->EPH_model->GetEvnVKNodeList($data);
						$childrens = $this->_formNameNode('EvnVK', $childrens, $data['ARMType']);
						$field = Array('object' => 'EvnVK','id' => 'EvnVK_id', 'name' => 'Name', 'iconCls' => 'evn-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if ($data['object'] == 'EvnMseList')
					{
						// EvnMse Протоколы МСЭ
						$childrens = $this->EPH_model->GetEvnMseNodeList($data);
						$childrens = $this->_formNameNode('EvnMse', $childrens, $data['ARMType']);
						$field = Array('object' => 'EvnMse','id' => 'EvnMse_id', 'name' => 'Name', 'iconCls' => 'evn-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if ($data['object'] == 'EvnPLDispDopList' && in_array('EvnPLDispDop', $EvnList))
					{
						// EvnPLDispDop - Случай дополнительной диспансеризации
						$childrens = $this->EPH_model->GetEvnPLDispDopNodeList($data);
						$childrens = $this->_formNameNode('EvnPLDispDop', $childrens, $data['ARMType']);
						$field = Array('object' => "EvnPLDispDop",'id' => "EvnPLDispDop_id", 'name' => 'Name', 'iconCls' => 'evnpl-16', 'leaf' => false, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if ($data['object'] == 'EvnStickList')
					{
						// EvnStick
						$childrens = $this->EPH_model->GetEvnStickNodeList($data);
						$childrens = $this->_formNameNode('EvnStick', $childrens, $data['ARMType']);
						$field = Array('object' => "EvnStick", 'id' => "EvnStick_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1, $arr_2);
						// EvnStickDop
						$childrens = $this->EPH_model->GetEvnStickDopNodeList($data);
						$childrens = $this->_formNameNode('EvnStickDop', $childrens, $data['ARMType']);
						$field = Array('object' => "EvnStickDop", 'id' => "EvnStickDop_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1, $arr_2);
						// EvnStickStudent - справки студентов
						$childrens = $this->EPH_model->GetEvnStickStudentNodeList($data);
						$childrens = $this->_formNameNode('EvnStickStudent', $childrens, $data['ARMType']);
						$field = Array('object' => "EvnStickStudent", 'id' => "EvnStickStudent_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1, $arr_2);
					}
					if ($data['object'] == 'EvnPS')
					{
						/*
						// MedHisRecordReceptionist Запись врача приемного отделения
						$childrens = $this->EMK_model->GetMedHisRecordReceptionistNodeList($data);
						$childrens = $this->_formNameNode('SignalInformationAll', $childrens, $data['ARMType']);
						$field = Array('object' => "MedHisRecordReceptionist", 'id' => "MedHisRecordReceptionist_id", 'name' => 'Name', 'iconCls' => 'evn-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
						// Диагнозы
						$childrens = array(
							array('Name' => "Диагнозы",'object' =>'DiagList', "id" => 12, 'order' => 11121)
						);
						$childrens = $this->_formNameNode('SignalInformationAll', $childrens, $data['ARMType']);
						$field = Array('object' => "DiagList",'id' => "id",'name' => 'Name','iconCls' => 'folder', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
						*/
						// EvnSection движения по отделениям
						$childrens = $this->EPH_model->GetEvnSectionNodeList($data);
						$childrens = $this->_formNameNode('EvnSection', $childrens, $data['ARMType']);
						$field = Array('object' => "EvnSection",'id' => "EvnSection_id", 'name' => 'Name', 'iconCls' => 'evn-16', 'leaf' => false, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
						/*/ КВС
						$childrens = array(
							array('Name' => "Карта выбывшего из стационара",'object' =>'CardEvnPS', "id" => 12, 'order' => 1)
						);
						$childrens = $this->_formNameNode('SignalInformationAll', $childrens, $data['ARMType']);
						$field = Array('object' => "CardEvnPS",'id' => "id",'name' => 'Name','iconCls' => 'folder', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
						//*/
						// EvnUsluga
						$childrens = $this->EPH_model->GetEvnUslugaNodeList($data);
						$childrens = $this->_formNameNode('EvnUsluga', $childrens, $data['ARMType']);
						// To-Do [Значок оплаты: ОМС, ДМС и т.д.]
						$field = Array('object' => "EvnUsluga", 'id' => "EvnUsluga_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if ($data['object'] == 'LabDiagnostic')
					{
						// LabDiagnostic
						$childrens = $this->EMK_model->GetLabDiagnosticNodeList($data);
						$childrens = $this->_formNameNode('LabDiagnostic', $childrens, $data['ARMType']);
						$field = Array('object' => "Research", 'id' => "Research_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1, $arr_2);
					}
					if ($data['object'] == 'InstrDiagnostic')
					{
						// InstrDiagnostic
						$childrens = $this->EMK_model->GetInstrDiagnosticNodeList($data);
						$childrens = $this->_formNameNode('InstrDiagnostic', $childrens, $data['ARMType']);
						$field = Array('object' => "Research", 'id' => "Research_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1, $arr_2);
					}
					if ($data['object'] == 'RadioDiagnostic')
					{
						// RadioDiagnostic
						$childrens = $this->EMK_model->GetRadioDiagnosticNodeList($data);
						$childrens = $this->_formNameNode('RadioDiagnostic', $childrens, $data['ARMType']);
						$field = Array('object' => "Research", 'id' => "Research_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1, $arr_2);
					}
						
					//break;
					$this->_personEMKTreeType = 0;
					$this->_personEMKTreeLevel = 1;
					if (($data['object']=='EvnPL') || ($data['object']=='PersonDocumentsGroup'))
					{
						$this->_personEMKTreeLevel = 1;
					}
					else
					{
						$this->_personEMKTreeLevel = 2;
					}
					break;
				case 3:
					if ($data['object'] == 'EvnSection')
					{
						/*/DiagList Диагнозы
						$childrens = array(
							array('Name' => "Диагнозы",'object' =>'DiagList', "id" => 12, 'order' => 91121)
						);
						$childrens = $this->_formNameNode('SignalInformationAll', $childrens, $data['ARMType']);
						$field = Array('object' => "DiagList",'id' => "id",'name' => 'Name','iconCls' => 'folder', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
						// PrimaryMedView Запись врача при поступлении
						$childrens = $this->EMK_model->GetPrimaryMedViewNodeList($data);
						$childrens = $this->_formNameNode('SignalInformationAll', $childrens, $data['ARMType']);
						$field = Array('object' => "MedHisRecordReceptionist", 'id' => "MedHisRecordReceptionist_id", 'name' => 'Name', 'iconCls' => 'evn-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
						// Прочие подпапки движения 
						$grouptype = array(
							array('id'=>61131, 'object' =>'MedHisTherapyPlan', 'text' => 'План лечения'),
							array('id'=>61121, 'object' =>'MedHisMes', 'text' => 'Медико-экономические стандарты'),
							array('id'=>61111, 'object' =>'MedHisMedicalCheckup', 'text' => 'Осмотры (консультации)'),
							array('id'=>41111, 'object' =>'MedHisDiaryEntrie', 'text' => 'Дневниковые записи'),
							array('id'=>41109, 'object' =>'MedHisCardObservation', 'text' => 'Карта наблюдения'),
							
							array('id'=>31111, 'object' =>'EvnSurgery', 'text' => 'Оперативные вмешательства'),
							array('id'=>21111, 'object' =>'MedHisAppointment', 'text' => 'Лечебные назначения'),
							array('id'=>11119, 'object' =>'MedHisDirection', 'text' => 'Направления на исследования и консультации'),
							array('id'=>11117, 'object' =>'MedHisResearchAll', 'text' => 'Результаты исследований'),
							array('id'=>11116, 'object' =>'MedHisEpicrisis', 'text' => 'Эпикризы'),
							array('id'=>11115, 'object' =>'MedHisNotices', 'text' => 'Извещения'),
							array('id'=>11114, 'object' =>'EvnStickList', 'text' => 'Нетрудоспособность'),
							array('id'=>11109, 'object' =>'MedHisDocument', 'text' => 'Документы')
						);
						$field = Array(
							'object' => "GroupByType",
							'EvnSection_id'=>$data['object_id'],
							'id' => "id", 
							'name' => 'Name', 
							'iconCls' => 
							'evn-16', 
							'leaf' => false, 
							'cls' => "folder"
						);
						$childrens = $this->_formNameNode('GroupByType', $grouptype, $data['ARMType']);
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
						*/

					}
					/*
					if ($data['object'] in ('EvnPL', 'EvnPLDispDop'))
					{
						$this->_personEMKTreeLevel = 1;
					}
					else
					{
						$this->_personEMKTreeLevel = 2;
					}
					*/
					$this->_personEMKTreeType = 0;
					$this->_personEMKTreeLevel = 2;
					if ($data['object']=='EvnPLDispDop')
					{
						$this->_personEMKTreeLevel = 1;
					}
					break;
				case 4:
					if ($data['object'] == 'MedHisResearchAll')
					{
						// Результаты исследований
						$childrens = array(
							array('Name' => "Лабораторная диагностика",'object' =>'LabDiagnostic', "id" => 10, 'order' => 3),
							array('Name'=>"Инструментальная диагностика",'object' =>'InstrDiagnostic',"id" => 11,'order'=>2),
							array('Name' => "Лучевая диагностика",'object' =>'RadioDiagnostic', "id" => 12, 'order' => 1)
						);
						$childrens = $this->_formNameNode('MedHisResearchAll', $childrens, $data['ARMType']);
						$field = Array('object' => "MedHisResearchAll",'id' => "id",'name' => 'Name','iconCls' => 'folder', 'leaf' => false, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if ($data['object'] == 'MedHisMedicalCheckup')
					{
						// MedicalCheckup Осмотры специалистов 
						$childrens = $this->EMK_model->GetMedicalCheckupNodeList($data);
						$childrens = $this->_formNameNode('MedicalCheckup', $childrens, $data['ARMType']);
						$field = Array('object' => "MedicalCheckup",'id' => "MedicalCheckup_id",'name' => 'Name','iconCls' => 'folder', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if ($data['object'] == 'MedHisEpicrisis')
					{
						// Epicrisis 
						$childrens = $this->EMK_model->GetEpicrisisNodeList($data);
						$childrens = $this->_formNameNode('Epicrisis', $childrens, $data['ARMType']);
						$field = Array('object' => "Epicrisis", 'id' => "Epicrisis_id", 'name' => 'Name', 'iconCls' => 'evn-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if ($data['object'] == 'EvnSurgery')
					{
						// ProtocolSurgery  
						$childrens = $this->EMK_model->GetProtocolSurgeryNodeList($data);
						$childrens = $this->_formNameNode('ProtocolSurgery', $childrens, $data['ARMType']);
						$field = Array('object' => "ProtocolSurgery", 'id' => "ProtocolSurgery_id", 'name' => 'Name', 'iconCls' => 'evn-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if ($data['object'] == 'MedHisDocument')
					{
						// EvnDocument Документы по случаю госпитализации
						$childrens = $this->EMK_model->GetEvnDocumentNodeList($data);
						$childrens = $this->_formNameNode('EvnDocument', $childrens, $data['ARMType']);
						$field = Array('object' => "EvnDocument",'id' => "EvnDocument_id", 'name' => 'Name', 'iconCls' => 'evn-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					break;
				case 5:
					if ($data['object'] == 'LabDiagnostic')
					{
						// LabDiagnostic
						$childrens = $this->EMK_model->GetLabDiagnosticNodeList($data);
						$childrens = $this->_formNameNode('LabDiagnostic', $childrens, $data['ARMType']);
						$field = Array('object' => "Research", 'id' => "Research_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1, $arr_2);
					}
					if ($data['object'] == 'InstrDiagnostic')
					{
						// InstrDiagnostic
						$childrens = $this->EMK_model->GetInstrDiagnosticNodeList($data);
						$childrens = $this->_formNameNode('InstrDiagnostic', $childrens, $data['ARMType']);
						$field = Array('object' => "Research", 'id' => "Research_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1, $arr_2);
					}
					if ($data['object'] == 'RadioDiagnostic')
					{
						// RadioDiagnostic
						$childrens = $this->EMK_model->GetRadioDiagnosticNodeList($data);
						$childrens = $this->_formNameNode('RadioDiagnostic', $childrens, $data['ARMType']);
						$field = Array('object' => "Research", 'id' => "Research_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1, $arr_2);
					}
					break;
			}
			
		}
		
		// Второй вариант вывода - Группировка по дате
		if ( $this->_personEMKTreeType == 0 ) {
			switch ( $this->_personEMKTreeLevel ) {
				case 0:
					if (in_array('CmpCloseCard', $EvnList))
					{
						// CmpCloseCard - Случаи СМП
						$childrens = $this->EPH_model->GetCmpCloseCard($data);							
						$childrens = $this->_formNameNode('CmpCloseCard', $childrens, $data['ARMType']);							
						$field = Array('object' => "CmpCloseCard",'id' => "CmpCloseCard_id",'CmpCallCard_id' => "CmpCallCard_id", 'name' => 'Name', 'iconCls' => 'evnpl-16', 'leaf' => false, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);	
					}
					if (in_array('CmpCallCard', $EvnList)){
						$childrens = $this->EPH_model->GetCmpCallCard($data);
						$childrens = $this->_formNameNode('CmpCallCard', $childrens, $data['ARMType']);
						//var_dump($childrens);die;
						$field = Array('object' => "CmpCallCard",'id' => "CmpCallCard_id", 'name' => 'Name', 'iconCls' => 'evnpl-16', 'leaf' => false, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if (in_array('EvnVK', $EvnList))
					{
						// EvnVK Протоколы ВК
						$childrens = $this->EPH_model->GetEvnVKNodeList($data);
						$childrens = $this->_formNameNode('EvnVK', $childrens, $data['ARMType']);
						$field = Array('object' => 'EvnVK','id' => 'EvnVK_id', 'name' => 'Name', 'iconCls' => 'evn-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if (in_array('EvnPrescrMse', $EvnList))
					{
						// EvnPrescrMse Направления на МСЭ
						$childrens = $this->EPH_model->GetEvnPrescrMseNodeList($data);
						$childrens = $this->_formNameNode('EvnPrescrMse', $childrens, $data['ARMType']);
						$field = Array('object' => 'EvnPrescrMse','id' => 'EvnPrescrMse_id', 'name' => 'Name', 'iconCls' => 'evn-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if (in_array('EvnMse', $EvnList))
					{
						// EvnMse Протоколы МСЭ
						$childrens = $this->EPH_model->GetEvnMseNodeList($data);
						$childrens = $this->_formNameNode('EvnMse', $childrens, $data['ARMType']);
						$field = Array('object' => 'EvnMse','id' => 'EvnMse_id', 'name' => 'Name', 'iconCls' => 'evn-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if (in_array('SignalInformationAll', $EvnList))
					{
						if (empty($archive_database_enable) || empty($_REQUEST['useArchive'])) { // только для актуальных
							$childrens = array(array('id'=>'9999999999', 'object' =>'SignalInformationAll', 'text' => 'Сигнальная информация', 'iconCls' =>'folder-info24'));
							$childrens = $this->_formNameNode('GroupByType', $childrens, $data['ARMType']);
							$field = Array('object' => "GroupByType",'person_id'=>$data['object_id'],'id' => "id",'name' => 'Name', 'iconCls' => 'folder24', 'leaf' => false, 'cls' => "x-tree-node-24x24");
							$arr_2 = $this->_getPersonEPHChild($childrens, $field);
							$arr_1 = array_merge($arr_1,$arr_2);
						}
					}
					if (in_array('EvnPS', $EvnList))
					{
						// EvnPS Случаи госпитализации
						$childrens = $this->EPH_model->GetEvnPSNodeList($data);
						$childrens = $this->_formNameNode('EvnPS', $childrens, $data['ARMType']);
						$field = Array('object' => "EvnPS",'id' => "EvnPS_id", 'name' => 'Name', 'iconCls' => 'folder24', 'leaf' => false, 'cls' => "x-tree-node-24x24");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if (in_array('MedHisDocument', $EvnList))
					{
						// EvnDocument Документы по случаю госпитализации
						$childrens = $this->EMK_model->GetEvnDocumentNodeList($data);
						$childrens = $this->_formNameNode('EvnDocument', $childrens, $data['ARMType']);
						$field = Array('object' => "EvnDocument",'id' => "EvnDocument_id", 'name' => 'Name', 'iconCls' => 'document24', 'leaf' => true, 'cls' => "x-tree-node-24x24");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if (in_array('LabDiagnostic', $EvnList))
					{
						// LabDiagnostic
						$childrens = $this->EMK_model->GetLabDiagnosticNodeList($data);
						$childrens = $this->_formNameNode('LabDiagnostic', $childrens, $data['ARMType']);
						$field = Array('object' => "Research", 'id' => "Research_id", 'name' => 'Name', 'iconCls' => 'document24', 'leaf' => true, 'cls' => "x-tree-node-24x24");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1, $arr_2);
					}
					if (in_array('InstrDiagnostic', $EvnList))
					{
						// InstrDiagnostic
						$childrens = $this->EMK_model->GetInstrDiagnosticNodeList($data);
						$childrens = $this->_formNameNode('InstrDiagnostic', $childrens, $data['ARMType']);
						$field = Array('object' => "Research", 'id' => "Research_id", 'name' => 'Name', 'iconCls' => 'document24', 'leaf' => true, 'cls' => "x-tree-node-24x24");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1, $arr_2);
					}
					if (in_array('RadioDiagnostic', $EvnList))
					{
						// RadioDiagnostic
						$childrens = $this->EMK_model->GetRadioDiagnosticNodeList($data);
						$childrens = $this->_formNameNode('RadioDiagnostic', $childrens, $data['ARMType']);
						$field = Array('object' => "Research", 'id' => "Research_id", 'name' => 'Name', 'iconCls' => 'document24', 'leaf' => true, 'cls' => "x-tree-node-24x24");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1, $arr_2);
					}
					if (in_array('MedHisMedicalCheckup', $EvnList))
					{
						// MedicalCheckup Осмотры специалистов
						$childrens = $this->EMK_model->GetMedicalCheckupNodeList($data);
						$childrens = $this->_formNameNode('MedicalCheckup', $childrens, $data['ARMType']);
						$field = Array('object' => "MedicalCheckup",'id' => "MedicalCheckup_id",'name' => 'Name', 'iconCls' => 'document24', 'leaf' => true, 'cls' => "x-tree-node-24x24");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if (in_array('EvnSurgery', $EvnList))
					{
						// ProtocolSurgery ПРОТОКОЛ ОПЕРАЦИИ 
						$childrens = $this->EMK_model->GetProtocolSurgeryNodeList($data);
						$childrens = $this->_formNameNode('ProtocolSurgery', $childrens, $data['ARMType']);
						$field = Array('object' => "ProtocolSurgery", 'id' => "ProtocolSurgery_id", 'name' => 'Name', 'iconCls' => 'document24', 'leaf' => true, 'cls' => "x-tree-node-24x24");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if (in_array('MedHisEpicrisis', $EvnList))
					{
						// Epicrisis
						$childrens = $this->EMK_model->GetEpicrisisNodeList($data);
						$childrens = $this->_formNameNode('Epicrisis', $childrens, $data['ARMType']);
						$field = Array('object' => "Epicrisis", 'id' => "Epicrisis_id", 'name' => 'Name', 'iconCls' => 'document24', 'leaf' => true, 'cls' => "x-tree-node-24x24");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					// end stac
					if (in_array('EvnPL', $EvnList)) {
						// EvnPL - Случаи амбулаторно-поликлинического лечения 
						$childrens = $this->EPH_model->GetEvnPLNodeList($data);
						$childrens = $this->_formNameNode('EvnPL', $childrens, $data['ARMType']);
						$field = Array('object' => "EvnPL",'id' => "EvnPL_id", 'name' => 'Name', 'iconCls' => 'folder24', 'leaf' => false, 'cls' => "x-tree-node-24x24");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if (in_array('EvnPLStom', $EvnList))
					{
						// EvnPLStom - Случаи стоматологического лечения 
						$childrens = $this->EPH_model->GetEvnPLStomNodeList($data);
						$childrens = $this->_formNameNode('EvnPLStom', $childrens, $data['ARMType']);
						$field = Array('object' => "EvnPLStom",'id' => "EvnPLStom_id", 'name' => 'Name', 'iconCls' => 'folder24', 'leaf' => false, 'cls' => "x-tree-node-24x24");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if (in_array('EvnPLDispDop', $EvnList)) {
						// EvnPLDispDop - Случай дополнительной диспансеризации
						$childrens = $this->EPH_model->GetEvnPLDispDopNodeList($data);
						$childrens = $this->_formNameNode('EvnPLDispDop', $childrens, $data['ARMType']);
						$field = Array('object' => "EvnPLDispDop",'id' => "EvnPLDispDop_id", 'name' => 'Name', 'iconCls' => 'folder24', 'leaf' => false, 'cls' => "x-tree-node-24x24");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if (in_array('EvnPLDispAdult', $EvnList)) {
						// EvnPLDisp - Случай диспансеризации/мед.осмотра
						$childrens = $this->EPH_model->GetEvnPLDispAdultNodeList($data);
						$childrens = $this->_formNameNode('EvnPLDispAdult', $childrens, $data['ARMType']);
						$field = Array('object' => "EvnPLDispAdult",'id' => "EvnPLDisp_id", 'name' => 'Name', 'iconCls' => 'folder24', 'leaf' => true, 'cls' => "x-tree-node-24x24");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if (in_array('EvnPLDispChild', $EvnList)) {
						// EvnPLDisp - Случай диспансеризации/мед.осмотра
						$childrens = $this->EPH_model->GetEvnPLDispChildNodeList($data);
						$childrens = $this->_formNameNode('EvnPLDispChild', $childrens, $data['ARMType']);
						$field = Array('object' => "EvnPLDispChild",'id' => "EvnPLDisp_id", 'name' => 'Name', 'iconCls' => 'folder24', 'leaf' => true, 'cls' => "x-tree-node-24x24");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if (in_array('EvnPLDispScreen', $EvnList)) {
						// EvnPLDisp - Случай диспансеризации/мед.осмотра
						$childrens = $this->EPH_model->GetEvnPLDispScreenNodeList($data);
						$childrens = $this->_formNameNode('EvnPLDispScreen', $childrens, $data['ARMType']);
						$field = Array('object' => "EvnPLDispScreen",'id' => "EvnPLDispScreen_id", 'name' => 'Name', 'iconCls' => 'folder24', 'leaf' => true, 'cls' => "x-tree-node-24x24");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if (in_array('EvnPLDispScreenChild', $EvnList)) {
						// EvnPLDisp - Случай диспансеризации/мед.осмотра
						$childrens = $this->EPH_model->GetEvnPLDispScreenChildNodeList($data);
						$childrens = $this->_formNameNode('EvnPLDispScreenChild', $childrens, $data['ARMType']);
						$field = Array('object' => "EvnPLDispScreenChild",'id' => "EvnPLDispScreenChild_id", 'name' => 'Name', 'iconCls' => 'folder24', 'leaf' => true, 'cls' => "x-tree-node-24x24");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					
					if (in_array('EvnDirection', $EvnList)) {
						// Выписанные электронные направления 
						$arr_1 = $this->_mergeNodesData('EvnDirection', $data, $arr_1);
					}
					
					if (in_array('EvnRecept', $EvnList)) {
						// EvnRecept
						$childrens = $this->EPH_model->GetEvnReceptWithNoVizitNodeList($data);
						$childrens = $this->_formNameNode('EvnRecept', $childrens, $data['ARMType']);
						$field = Array('object' => "EvnRecept", 'id' => "EvnRecept_id", 'name' => 'Name', 'iconCls' => 'document24', 'leaf' => true, 'cls' => "x-tree-node-24x24");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if (in_array('EvnReceptGeneral', $EvnList)) {
						// EvnRecept
						$childrens = $this->EPH_model->GetEvnReceptGeneralWithNoVizitNodeList($data);
						$childrens = $this->_formNameNode('EvnReceptGeneral', $childrens, $data['ARMType']);
						$field = Array('object' => "EvnReceptGeneral", 'id' => "EvnReceptGeneral_id", 'name' => 'Name', 'iconCls' => 'document24', 'leaf' => true, 'cls' => "x-tree-node-24x24");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					/*
					if (in_array('BegPersonPrivilege', $EvnList)) {
						// PersonPrivilege - – Льгота: открытие
						$childrens = $this->EPH_model->GetBegPersonPrivilegeNodeList($data);
						$childrens = $this->_formNameNode('BegPersonPrivilege', $childrens, $data['ARMType']);
						// To-Do [Значок льготы: фед., рег., мест.]
						$field = Array('object' => "PersonPrivilege",'id' => "PersonPrivilege_id", 'name' => 'Name', 'iconCls' => 'priv-new16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field, "Beg");
						$arr_1 = array_merge($arr_1,$arr_2);
					}
						
					if (in_array('EndPersonPrivilege', $EvnList)) {
						// PersonPrivilege – Льгота: закрытие 
						$childrens = $this->EPH_model->GetEndPersonPrivilegeNodeList($data);
						$childrens = $this->_formNameNode('EndPersonPrivilege', $childrens, $data['ARMType']);
						// To-Do [Значок льготы: фед., рег., мест.]
						$field = Array('object' => "PersonPrivilege",'id' => "PersonPrivilege_id", 'name' => 'Name', 'iconCls' => 'pers-priv16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field, "End");
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if (in_array('BegPersonDisp', $EvnList)) {
						// PersonDisp - Диспансеризация: постановка
						$childrens = $this->EPH_model->GetBegPersonDispNodeList($data);
						$childrens = $this->_formNameNode('BegPersonDisp', $childrens, $data['ARMType']);
						$field = Array('object' => "PersonDisp",'id' => "PersonDisp_id", 'name' => 'Name', 'iconCls' => 'disp16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field, "Beg");
						$arr_1 = array_merge($arr_1,$arr_2);
					}
						
					if (in_array('EndPersonDisp', $EvnList)) {
						// PersonDisp - Диспансеризация: снятие
						$childrens = $this->EPH_model->GetEndPersonDispNodeList($data);
						$childrens = $this->_formNameNode('EndPersonDisp', $childrens, $data['ARMType']);
						$field = Array('object' => "PersonDisp",'id' => "PersonDisp_id", 'name' => 'Name', 'iconCls' => 'disp16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field, "End");
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if (in_array('BegPersonCard', $EvnList)) {
						// PersonCard - Прикрепление - (Прикрепление, дата прикрепления, ЛПУ) 
						$childrens = $this->EPH_model->GetPersonCardBegNodeList($data);
						$childrens = $this->_formNameNode('BegPersonCard', $childrens, $data['ARMType']);
						$field = Array('object' => "PersonCard",'id' => "PersonCard_id", 'name' => 'Name', 'iconCls' => 'evnpl-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field, "Beg");
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					
					if (in_array('EndPersonCard', $EvnList)) {
						// PersonCard - Открепление - (Открепление, дата открепления, ЛПУ) 
						$childrens = $this->EPH_model->GetPersonCardEndNodeList($data);
						$childrens = $this->_formNameNode('EndPersonCard', $childrens, $data['ARMType']);
						$field = Array('object' => "PersonCard",'id' => "PersonCard_id", 'name' => 'Name', 'iconCls' => 'evnpl-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field, "End");
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					*/
					if (in_array('EvnUslugaPar', $EvnList)) {
						$arr_1 = $this->_mergeNodesData('EvnUslugaPar', $data, $arr_1);
					}
					if (in_array('EvnUslugaTelemed', $EvnList)) {
						$arr_1 = $this->_mergeNodesData('EvnUslugaTelemed', $data, $arr_1);
					}
					/*
					if (in_array('PersonDocuments', $EvnList)) {
						// PersonDocuments - Документы человека
						$childrens = array(
							array("PersonDocuments_Name" => "Документы", "PersonDocuments_id" => 11109)
						);
						$childrens = $this->_formNameNode('PersonDocuments', $childrens, $data['ARMType']);
						$field = Array('object' => "PersonDocuments",'id' => "PersonDocuments_id", 'name' => "PersonDocuments_Name", 'iconCls' => 'folder', 'leaf' => false, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);						
					}
					*/
					break;
				case 1:
					if ($data['object'] == 'SignalInformationAll')
					{
						// Сигнальная информация папки со списками
						$childrens = array(
							array('Name' => 'Анамнез жизни', 'object' => 'PersonMedHistory', 'Person_id' =>$data['Person_id'], 'order' => 15, 'leaf' => true),
							array('Name' => 'Антропометрические данные', 'object' => 'Anthropometry', 'Person_id' =>$data['Person_id'], 'order' => 5, 'leaf' => true),
							array('Name' => "Способ вскармливания", 'object' => 'FeedingType','Person_id' =>$data['Person_id'], 'order' => 4, 'leaf' => true),
							array('Name' => "Группа крови и резус фактор", 'object' => 'BloodData', 'Person_id' =>$data['Person_id'], 'order' => 14, 'leaf' => true),
							array('Name' => "Аллергологический анамнез", 'object' => 'AllergHistory','Person_id' =>$data['Person_id'], 'order' => 13, 'leaf' => true),
							// array('Name' => "Анамнез жизни", 'object' => 'LifeHistory', 'Person_id' =>$data['Person_id'], 'order' => 11),
							// array('Name' => "Анамнез заболевания", 'object' => 'SicknessAnamnezData', 'Person_id' =>$data['Person_id'], 'order' => 10),
							array('Name' => "Список уточненных диагнозов", 'object' => 'DiagList', 'Person_id' =>$data['Person_id'], 'order' => 9, 'leaf' => true),
							array('Name' => 'Свидетельства', 'object' => 'PersonSvidInfo', 'Person_id' => $data['Person_id'], 'order' => 12, 'leaf' => true),
							array('Name' => "Диспансерный учет", 'object' => 'PersonDispInfo', 'Person_id' =>$data['Person_id'], 'order' => 11, 'leaf' => true),
							array('Name' => "Диспансеризация/мед. осмотры", 'object' => 'EvnPLDispInfo', 'Person_id' =>$data['Person_id'], 'order' => 10, 'leaf' => true),
							// array('Name' => "Перенесенные инфекционные заболевания", 'object' => 'InfectDiseases', 'Person_id' =>$data['Person_id'], 'order' => 7),
							array('Name' => "Экспертный анамнез и льготы", 'object' => 'ExpertHistory', 'Person_id' =>$data['Person_id'], 'order' => 12, 'leaf' => true),
							// array('Name' => "Вакцинопрофилактика", 'object' => 'Vaccine', 'Person_id' =>$data['Person_id'], 'order' => 5),
							// array('Name' => "Флюоротека", 'object' => 'Flyuoroteka', 'Person_id' =>$data['Person_id'], 'order' => 4),
							// array('Name' => "Лист лучевой нагрузки", 'object' => 'RadioDose', 'Person_id' =>$data['Person_id'], 'order' => 3),
							// array('Name' => "Список направлений на госпитализацию", 'object' => 'HospitDirectList', 'Person_id' =>$data['Person_id'], 'order' => 2),
							array('Name' => "Список оперативных вмешательств", 'object' => 'SurgicalList', 'Person_id' =>$data['Person_id'], 'order' => 7, 'leaf' => true),
							array('Name' => "Список отмененных направлений", 'object' => 'DirFailList', 'Person_id' =>$data['Person_id'], 'order' => 6, 'leaf' => true),
							//array('Name' => "Исполненные привики", 'object' => 'Inoculation', 'Person_id' =>$data['Person_id'], 'order' => 2, 'leaf' => true),
							array('Name' => "Список открытых ЛВН", 'object' => 'EvnStickOpenInfo', 'Person_id' => $data['Person_id'], 'order' => 4, 'leaf' => true),
						);
						// #179545 боковая панель содержит Список опросов, регион Базовый
						$childrens[] = array('Name' => "Список опросов", 'object' => 'PersonOnkoProfileInfo', 'Person_id' => $data['Person_id'], 'order' => 4, 'leaf' => true);

						if (getRegionNick() == 'vologda') {
							$childrens[] = array('Name' => "Список ЛС, заявленных в рамках ЛЛО", 'object' => 'PersonDrugRequestInfo', 'Person_id' => $data['Person_id'], 'order' => 4, 'leaf' => true);
						}

						// #182475
						// Для регионов 'ufa' и 'vologda' присутствует три пункта о прививках,
						// заголовок пункта MantuReaction зависит от региона:
						if ($region_nick == 'ufa' ||  $region_nick == 'vologda')
							array_push($childrens,
								array(
									'Name' => "Исполненные прививки",
									'object' => 'Inoculation',
									'Person_id' =>$data['Person_id'],
									'order' => 3,
									'leaf' => true),
								array(
									'Name' => "Планируемые прививки",
									'object' => 'InoculationPlan',
									'Person_id' =>$data['Person_id'],
									'order' => 2,
									'leaf' => true),
								array(
									'Name' => ($region_nick == 'ufa' ? "Реакция Манту" : "Манту/Диаскинтест"),
									'object' => 'MantuReaction',
									'Person_id' =>$data['Person_id'],
									'order' => 1,
									'leaf' => true));

						if ($region_nick != 'kz') {
							$childrens[] = [ 'Name'=>'Список контрольных карт по карантину', 'object' => 'PersonQuarantine', 'Person_id' => $data['Person_id'], 'order'=>1, 'leaf' => true ];
						}
						$childrens = $this->_formNameNode('SignalInformationAll', $childrens, $data['ARMType']);
						$field = Array('object' => 'SignalInformationAll','Person_id' =>$data['Person_id'],'id' => 'Person_id','name' => 'Name','iconCls' => 'info16', 'leaf' => false, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if ($data['object'] == 'EvnPS')
					{
						/*
						// MedHisRecordReceptionist Запись врача приемного отделения
						$childrens = $this->EMK_model->GetMedHisRecordReceptionistNodeList($data);
						$childrens = $this->_formNameNode('SignalInformationAll', $childrens, $data['ARMType']);
						$field = Array('object' => "MedHisRecordReceptionist", 'id' => "MedHisRecordReceptionist_id", 'name' => 'Name', 'iconCls' => 'evn-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
						// Диагнозы
						$childrens = array(
							array('Name' => "Диагнозы",'object' =>'DiagList', "id" => 12, 'order' => 11121)
						);
						$childrens = $this->_formNameNode('SignalInformationAll', $childrens, $data['ARMType']);
						$field = Array('object' => "DiagList",'id' => "id",'name' => 'Name','iconCls' => 'folder', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
						*/
						// EvnSection движения по отделениям
						if ( $this->_baseEvnClassExists('EvnSection') ) {
							$childrens = $this->EPH_model->GetEvnSectionNodeList($data);
							$childrens = $this->_formNameNode('EvnSection', $childrens, $data['ARMType']);
							$field = Array('object' => "EvnSection",'id' => "EvnSection_id", 'name' => 'Name', 'iconCls' => 'evn-16', 'leaf' => false, 'cls' => "folder");
							$arr_2 = $this->_getPersonEPHChild($childrens, $field);
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						/*/ КВС
						$childrens = array(
							array('Name' => "Карта выбывшего из стационара",'object' =>'CardEvnPS', "id" => 12, 'order' => 1)
						);
						$childrens = $this->_formNameNode('SignalInformationAll', $childrens, $data['ARMType']);
						$field = Array('object' => "CardEvnPS",'id' => "id",'name' => 'Name','iconCls' => 'folder', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
						//*/
						// EvnStick
						if ( $this->_baseEvnClassExists('EvnStick') ) {
							$childrens = $this->EPH_model->GetEvnStickNodeList($data);
							$childrens = $this->_formNameNode('EvnStick', $childrens, $data['ARMType']);
							$field = Array('object' => "EvnStick", 'id' => "EvnStick_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->_getPersonEPHChild($childrens, $field);
							$arr_1 = array_merge($arr_1,$arr_2);
							// EvnStickDop
							$childrens = $this->EPH_model->GetEvnStickDopNodeList($data);
							$childrens = $this->_formNameNode('EvnStickDop', $childrens, $data['ARMType']);
							$field = Array('object' => "EvnStickDop", 'id' => "EvnStickDop_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->_getPersonEPHChild($childrens, $field);
							$arr_1 = array_merge($arr_1,$arr_2);
							// EvnStickStudent - справки студентов
							$childrens = $this->EPH_model->GetEvnStickStudentNodeList($data);
							$childrens = $this->_formNameNode('EvnStickStudent', $childrens, $data['ARMType']);
							$field = Array('object' => "EvnStickStudent", 'id' => "EvnStickStudent_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->_getPersonEPHChild($childrens, $field);
							$arr_1 = array_merge($arr_1, $arr_2);
						}
						// EvnCostPrint - справки о стомиости лечения
						if ( $this->_baseEvnClassExists('EvnCostPrint') ) {
							$childrens = $this->EPH_model->GetEvnCostPrintNodeList($data);
							$childrens = $this->_formNameNode('EvnCostPrint', $childrens, $data['ARMType']);
							$field = Array('object' => "EvnCostPrint", 'id' => "EvnCostPrint_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->_getPersonEPHChild($childrens, $field);
							$arr_1 = array_merge($arr_1, $arr_2);
						}
						if (
							!in_array(getRegionNick(), array('kz'))
							&& $this->_baseEvnClassExists('EvnOnkoNotify')
						) {
							// EvnOnkoNotify - извещение онко
							$childrens = $this->EPH_model->GetEvnOnkoNotifyNodeList($data);
							$childrens = $this->_formNameNode('EvnOnkoNotify', $childrens, $data['ARMType']);
							$field = Array('object' => "EvnOnkoNotify",'id' => "EvnOnkoNotify_id", 'name' => 'Name', 'iconCls' => 'document16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->_getPersonEPHChild($childrens, $field);
							$arr_1 = array_merge($arr_1,$arr_2);
							// EvnOnkoNotifyNeglected - Протокол о запущенной форме онко
							$childrens = $this->EPH_model->GetEvnOnkoNotifyNeglectedNodeList($data);
							$childrens = $this->_formNameNode('EvnOnkoNotifyNeglected', $childrens, $data['ARMType']);
							$field = Array('object' => "EvnOnkoNotifyNeglected",'id' => "EvnOnkoNotifyNeglected_id", 'name' => 'Name', 'iconCls' => 'document16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->_getPersonEPHChild($childrens, $field);
							$arr_1 = array_merge($arr_1,$arr_2);
						}

						$arr_1 = $this->_mergeNodesData('EvnUslugaPar', $data, $arr_1);
						$arr_1 = $this->_mergeNodesData('EvnDirection', $data, $arr_1);
					}
					if ($data['object'] == 'EvnUslugaPar')
					{
						// EvnCostPrint - справки о стомиости лечения
						if ( $this->_baseEvnClassExists('EvnCostPrint') ) {
							$childrens = $this->EPH_model->GetEvnCostPrintNodeList($data);
							$childrens = $this->_formNameNode('EvnCostPrint', $childrens, $data['ARMType']);
							$field = Array('object' => "EvnCostPrint", 'id' => "EvnCostPrint_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->_getPersonEPHChild($childrens, $field);
							$arr_1 = array_merge($arr_1, $arr_2);
						}
					}
					if ($data['object'] == 'PersonDocuments')
					{
						// PersonDocuments - Документы человека
						$childrens = array(
							array("PersonDocumentsGroup_Name" => "Медсвидетельстава", "PersonDocumentsGroup_id" => 1),
							array("PersonDocumentsGroup_Name" => "Справки", "PersonDocumentsGroup_id" => 2)
						);
						$childrens = $this->_formNameNode('PersonDocumentsGroup', $childrens, $data['ARMType']);
						$field = Array('object' => "PersonDocumentsGroup",'id' => "PersonDocumentsGroup_id", 'name' => "PersonDocumentsGroup_Name", 'iconCls' => 'folder', 'leaf' => false, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					// Список медсвидетельств
					if ($data['object'] == 'PersonDocumentsGroup' && $data['object_id'] == 1)
					{
						// свидетельства о рождении
						$childrens = $this->EPH_model->GetBirthSvidNodeList($data);
						$childrens = $this->_formNameNode('BirthSvid', $childrens, $data['ARMType']);
						$field = Array('object' => "BirthSvid",'id' => "BirthSvid_id", 'name' => 'Name', 'iconCls' => 'evnpl-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
						
						// свидетельства о смерти
						$childrens = $this->EPH_model->GetDeathSvidNodeList($data);
						$childrens = $this->_formNameNode('DeathSvid', $childrens, $data['ARMType']);
						$field = Array('object' => "DeathSvid", 'id' => "DeathSvid_id", 'name' => 'Name', 'iconCls' => 'evnpl-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
						
						// свидетельства о смерти детей
						/*$childrens = $this->EPH_model->GetPntDeathSvidNodeList($data);
						$childrens = $this->_formNameNode('PntDeathSvid', $childrens);
						$field = Array('object' => "PntDeathSvid",'id' => "PntDeathSvid_id", 'name' => 'Name', 'iconCls' => 'evnpl-16', 'leaf' => false, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);*/
					}
					// Список справок
					if ($data['object'] == 'PersonDocumentsGroup' && $data['object_id'] == 2)
					{
						/*$childrens = $this->EPH_model->GetMedSvidNodeList($data);
						$childrens = $this->_formNameNode('MedSvid', $childrens);
						$field = Array('object' => "EvnPL",'id' => "EvnPL_id", 'name' => 'Name', 'iconCls' => 'evnpl-16', 'leaf' => false, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);*/
					}
					if ($data['object'] == 'EvnPL')
					{
						// EvnVizitPL
						if ( $this->_baseEvnClassExists('EvnVizitPL') ) {
							$childrens = $this->EPH_model->GetEvnVizitPLNodeList($data);
							$childrens = $this->_formNameNode('EvnVizitPL', $childrens, $data['ARMType'], $data['session']['region']['nick']);
							$field = Array('object' => "EvnVizitPL", 'id' => "EvnVizitPL_id", 'name' => 'Name', 'iconCls' => 'evnvizit-16', 'leaf' => false, 'cls' => "folder");
							$arr_1 = $this->_getPersonEPHChild($childrens, $field);
						}
						// EvnUsluga
						if ( $this->_baseEvnClassExists('EvnUsluga') ) {
							$childrens = $this->EPH_model->GetEvnUslugaNodeList($data);
							$childrens = $this->_formNameNode('EvnUsluga', $childrens, $data['ARMType']);
							// To-Do [Значок оплаты: ОМС, ДМС и т.д.] 
							$field = Array('object' => "EvnUsluga", 'id' => "EvnUsluga_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->_getPersonEPHChild($childrens, $field);
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						// EvnStick
						if ( $this->_baseEvnClassExists('EvnStick') ) {
							$childrens = $this->EPH_model->GetEvnStickNodeList($data);
							$childrens = $this->_formNameNode('EvnStick', $childrens, $data['ARMType']);
							$field = Array('object' => "EvnStick", 'id' => "EvnStick_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->_getPersonEPHChild($childrens, $field);
							$arr_1 = array_merge($arr_1,$arr_2);
							// EvnStickDop
							$childrens = $this->EPH_model->GetEvnStickDopNodeList($data);
							$childrens = $this->_formNameNode('EvnStickDop', $childrens, $data['ARMType']);
							$field = Array('object' => "EvnStickDop", 'id' => "EvnStickDop_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->_getPersonEPHChild($childrens, $field);
							$arr_1 = array_merge($arr_1,$arr_2);
							// EvnStickStudent - справки студентов
							$childrens = $this->EPH_model->GetEvnStickStudentNodeList($data);
							$childrens = $this->_formNameNode('EvnStickStudent', $childrens, $data['ARMType']);
							$field = Array('object' => "EvnStickStudent", 'id' => "EvnStickStudent_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->_getPersonEPHChild($childrens, $field);
							$arr_1 = array_merge($arr_1, $arr_2);
						}
						// EvnCostPrint - справки о стомиости лечения
						if ( $this->_baseEvnClassExists('EvnCostPrint') ) {
							$childrens = $this->EPH_model->GetEvnCostPrintNodeList($data);
							$childrens = $this->_formNameNode('EvnCostPrint', $childrens, $data['ARMType']);
							$field = Array('object' => "EvnCostPrint", 'id' => "EvnCostPrint_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->_getPersonEPHChild($childrens, $field);
							$arr_1 = array_merge($arr_1, $arr_2);
						}
						if (
							!in_array(getRegionNick(), array('kz'))
							&& $this->_baseEvnClassExists('EvnOnkoNotify')
						) {
							// EvnOnkoNotify - извещение онко
							$childrens = $this->EPH_model->GetEvnOnkoNotifyNodeList($data);
							$childrens = $this->_formNameNode('EvnOnkoNotify', $childrens, $data['ARMType']);
							$field = Array('object' => "EvnOnkoNotify",'id' => "EvnOnkoNotify_id", 'name' => 'Name', 'iconCls' => 'document16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->_getPersonEPHChild($childrens, $field);
							$arr_1 = array_merge($arr_1,$arr_2);
							// EvnOnkoNotifyNeglected - Протокол о запущенной форме онко
							$childrens = $this->EPH_model->GetEvnOnkoNotifyNeglectedNodeList($data);
							$childrens = $this->_formNameNode('EvnOnkoNotifyNeglected', $childrens, $data['ARMType']);
							$field = Array('object' => "EvnOnkoNotifyNeglected",'id' => "EvnOnkoNotifyNeglected_id", 'name' => 'Name', 'iconCls' => 'document16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->_getPersonEPHChild($childrens, $field);
							$arr_1 = array_merge($arr_1,$arr_2);
						}

						// $arr_1 = $this->_mergeNodesData('EvnUslugaPar', $data, $arr_1);
					}
					if ($data['object'] == 'EvnPLStom')
					{
						// EvnVizitPLStom
						if ( $this->_baseEvnClassExists('EvnVizitPLStom') ) {
							$childrens = $this->EPH_model->GetEvnVizitPLStomNodeList($data);
							$childrens = $this->_formNameNode('EvnVizitPLStom', $childrens, $data['ARMType'], $data['session']['region']['nick']);
							$field = Array('object' => "EvnVizitPLStom", 'id' => "EvnVizitPLStom_id", 'name' => 'Name', 'iconCls' => 'evnvizit-16', 'leaf' => false, 'cls' => "folder");
							$arr_1 = $this->_getPersonEPHChild($childrens, $field);
						}
						// EvnUsluga
						if ( $this->_baseEvnClassExists('EvnUsluga') ) {
							$childrens = $this->EPH_model->GetEvnUslugaStomNodeList($data);
							$childrens = $this->_formNameNode('EvnUslugaStom', $childrens, $data['ARMType']);
							$field = Array('object' => "EvnUslugaStom", 'id' => "EvnUslugaStom_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->_getPersonEPHChild($childrens, $field);
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						// EvnStick
						if ( $this->_baseEvnClassExists('EvnStick') ) {
							$childrens = $this->EPH_model->GetEvnStickNodeList($data);
							$childrens = $this->_formNameNode('EvnStick', $childrens, $data['ARMType']);
							$field = Array('object' => "EvnStick", 'id' => "EvnStick_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->_getPersonEPHChild($childrens, $field);
							$arr_1 = array_merge($arr_1,$arr_2);
							// EvnStickDop
							$childrens = $this->EPH_model->GetEvnStickDopNodeList($data);
							$childrens = $this->_formNameNode('EvnStickDop', $childrens, $data['ARMType']);
							$field = Array('object' => "EvnStickDop", 'id' => "EvnStickDop_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->_getPersonEPHChild($childrens, $field);
							$arr_1 = array_merge($arr_1,$arr_2);
							// EvnStickStudent - справки студентов
							$childrens = $this->EPH_model->GetEvnStickStudentNodeList($data);
							$childrens = $this->_formNameNode('EvnStickStudent', $childrens, $data['ARMType']);
							$field = Array('object' => "EvnStickStudent", 'id' => "EvnStickStudent_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->_getPersonEPHChild($childrens, $field);
							$arr_1 = array_merge($arr_1, $arr_2);
							// EvnCostPrint - справки о стомиости лечения
							$childrens = $this->EPH_model->GetEvnCostPrintNodeList($data);
							$childrens = $this->_formNameNode('EvnCostPrint', $childrens, $data['ARMType']);
							$field = Array('object' => "EvnCostPrint", 'id' => "EvnCostPrint_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->_getPersonEPHChild($childrens, $field);
							$arr_1 = array_merge($arr_1, $arr_2);
						}
					}
					if ($data['object'] == 'CmpCloseCard')
					{
						// CmpCallCardCostPrint - справки о стомиости лечения
						$childrens = $this->EPH_model->GetCmpCallCardCostPrintNodeList($data);
						$childrens = $this->_formNameNode('CmpCallCardCostPrint', $childrens, $data['ARMType']);
						$field = Array('object' => "CmpCallCardCostPrint", 'id' => "CmpCallCardCostPrint_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
						$arr_1 = $this->_getPersonEPHChild($childrens, $field);
					}
					if ($data['object'] == 'EvnPLDispDop')
					{
						// EvnVizitDispDop
						if ( $this->_baseEvnClassExists('EvnVizitDispDop') ) {
							$childrens = $this->EPH_model->GetEvnVizitDispDopNodeList($data);
							$childrens = $this->_formNameNode('EvnVizitDispDop', $childrens, $data['ARMType']);
							$field = Array('object' => "EvnVizitDispDop", 'id' => "EvnVizitDispDop_id", 'name' => 'Name', 'iconCls' => 'evnvizit-16', 'leaf' => false, 'cls' => "folder");
							$arr_1 = $this->_getPersonEPHChild($childrens, $field);
						}
						// EvnUslugaDispDop
						if ( $this->_baseEvnClassExists('EvnUslugaDispDop') ) {
							$childrens = $this->EPH_model->GetEvnUslugaDispDopNodeList($data);
							$childrens = $this->_formNameNode('EvnUslugaDispDop', $childrens, $data['ARMType']);
							$field = Array('object' => "EvnUslugaDispDop", 'id' => "EvnUslugaDispDop_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->_getPersonEPHChild($childrens, $field);
							$arr_1 = array_merge($arr_1,$arr_2);
						}
					}
					if ($data['object'] == 'EvnPLDispMigrant')
					{
						$arr_1 = $this->_mergeNodesData('EvnUslugaPar', $data, $arr_1);
						$arr_1 = $this->_mergeNodesData('EvnDirection', $data, $arr_1);
					}
					if ($data['object'] == 'EvnPLDispDriver')
					{
						$arr_1 = $this->_mergeNodesData('EvnUslugaPar', $data, $arr_1);
						$arr_1 = $this->_mergeNodesData('EvnDirection', $data, $arr_1);
					}
					break;
				case 2:
					// Список медсвидетельств
					if ($data['object'] == 'PersonDocumentsGroup' && $data['object_id'] == 1)
					{
						// свидетельства о рождении
						$childrens = $this->EPH_model->GetBirthSvidNodeList($data);
						$childrens = $this->_formNameNode('BirthSvid', $childrens, $data['ARMType']);
						$field = Array('object' => "BirthSvid",'id' => "BirthSvid_id", 'name' => 'Name', 'iconCls' => 'evnpl-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1, $arr_2);
						
						// свидетельства о смерти
						$childrens = $this->EPH_model->GetDeathSvidNodeList($data);
						$childrens = $this->_formNameNode('DeathSvid', $childrens, $data['ARMType']);
						$field = Array('object' => "DeathSvid", 'id' => "DeathSvid_id", 'name' => 'Name', 'iconCls' => 'evnpl-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1, $arr_2);
						
						// свидетельства о смерти детей
						/*$childrens = $this->EPH_model->GetPntDeathSvidNodeList($data);
						$childrens = $this->_formNameNode('PntDeathSvid', $childrens);
						$field = Array('object' => "PntDeathSvid",'id' => "PntDeathSvid_id", 'name' => 'Name', 'iconCls' => 'evnpl-16', 'leaf' => false, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);*/
					}
					// Список справок
					if ($data['object'] == 'PersonDocumentsGroup' && $data['object_id'] == 2)
					{
						/*$childrens = $this->EPH_model->GetMedSvidNodeList($data);
						$childrens = $this->_formNameNode('MedSvid', $childrens);
						$field = Array('object' => "EvnPL",'id' => "EvnPL_id", 'name' => 'Name', 'iconCls' => 'evnpl-16', 'leaf' => false, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);*/
					}
					if ($data['object'] == 'EvnVizitPL')
					{
						// EvnRecept
						if ( $this->_baseEvnClassExists('EvnRecept') ) {
							$childrens = $this->EPH_model->GetEvnReceptNodeList($data);
							$childrens = $this->_formNameNode('EvnRecept', $childrens, $data['ARMType']);
							$field = Array('object' => "EvnRecept", 'id' => "EvnRecept_id", 'name' => 'Name', 'iconCls' => 'lgot16', 'leaf' => true, 'cls' => "folder");
							$arr_1 = $this->_getPersonEPHChild($childrens, $field);

							//EvnReceptGeneral
							$childrens = $this->EPH_model->GetEvnReceptGeneralNodeList($data);
							$childrens = $this->_formNameNode('EvnReceptGeneral',$childrens,$data['ARMType']);
							$field = Array('object' => "EvnReceptGeneral", 'id' => "EvnReceptGeneral_id", 'name' => 'Name', 'iconCls' => 'lgot16', 'leaf' => true, 'cls' => "folder");
							//var_dump($childrens);die;
							$arr_2 = $this->_getPersonEPHChild($childrens, $field);
							$arr_1 = array_merge($arr_1,$arr_2);
						}

						if ( $data['session']['region']['nick'] != 'ufa' ) {
							// EvnUsluga
							if ( $this->_baseEvnClassExists('EvnUsluga') ) {
								$childrens = $this->EPH_model->GetEvnUslugaNodeList($data);
								$childrens = $this->_formNameNode('EvnUsluga', $childrens, $data['ARMType']);
								$field = Array('object' => "EvnUsluga", 'id' => "EvnUsluga_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
								$arr_2 = $this->_getPersonEPHChild($childrens, $field);
								$arr_1 = array_merge($arr_1,$arr_2);
							}
						}

						/*
						// PersonDisp - Диспансеризация: постановка
						$childrens = $this->EPH_model->GetBegPersonDispNodeList($data);
						$childrens = $this->_formNameNode('BegPersonDisp', $childrens, $data['ARMType']);
						$field = Array('object' => "PersonDisp",'id' => "PersonDisp_id", 'name' => 'Name', 'iconCls' => 'disp16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field, "Beg");
						$arr_1 = array_merge($arr_1,$arr_2);
						// PersonDisp - Диспансеризация: снятие
						$childrens = $this->EPH_model->GetEndPersonDispNodeList($data);
						$childrens = $this->_formNameNode('EndPersonDisp', $childrens, $data['ARMType']);
						$field = Array('object' => "PersonDisp",'id' => "PersonDisp_id", 'name' => 'Name', 'iconCls' => 'disp16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field, "End");
						$arr_1 = array_merge($arr_1,$arr_2);
						*/
						
						// FreeDocument
						$childrens = $this->EPH_model->GetEvnVizitPLFreeDocumentNodeList($data);
						$childrens = $this->_formNameNode('FreeDocument', $childrens, $data['ARMType']);
						$field = Array('object' => "FreeDocument", 'id' => "FreeDocument_id", 'name' => 'Name', 'iconCls' => 'direction-16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
						
						//MorbusOnkoVizitPLDop
						$childrens = $this->EPH_model->GetMorbusOnkoVizitPLDopNodeList($data);
						$childrens = $this->_formNameNode('MorbusOnkoVizitPLDop', $childrens, $data['ARMType']);
						$field = Array('object' => "MorbusOnkoVizitPLDop", 'id' => "MorbusOnkoVizitPLDop_id", 'name' => 'Name', 'iconCls' => '', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);

						$arr_1 = $this->_mergeNodesData('EvnUslugaPar', $data, $arr_1);
						$arr_1 = $this->_mergeNodesData('EvnDirection', $data, $arr_1);
						
					}
					if ($data['object'] == 'EvnVizitPLStom')
					{
						// EvnUslugaStom
						if ( $this->_baseEvnClassExists('EvnUsluga') ) {
							$childrens = $this->EPH_model->GetEvnUslugaStomNodeList($data);
							$childrens = $this->_formNameNode('EvnUslugaStom', $childrens, $data['ARMType']);
							$field = Array('object' => "EvnUslugaStom", 'id' => "EvnUslugaStom_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->_getPersonEPHChild($childrens, $field);
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						// EvnDiagPLStom
						if ( $this->_baseEvnClassExists('EvnDiagPLStom') ) {
							$childrens = $this->EPH_model->GetEvnDiagPLStomNodeList($data);
							$childrens = $this->_formNameNode('EvnDiagPLStom', $childrens, $data['ARMType']);
							$field = Array('object' => "EvnDiagPLStom", 'id' => "EvnDiagPLStom_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->_getPersonEPHChild($childrens, $field);
							$arr_1 = array_merge($arr_1,$arr_2);
						}

						//MorbusOnkoVizitPLDop
						$childrens = $this->EPH_model->GetMorbusOnkoVizitPLDopNodeList($data);
						$childrens = $this->_formNameNode('MorbusOnkoVizitPLDop', $childrens, $data['ARMType']);
						$field = Array('object' => "MorbusOnkoVizitPLDop", 'id' => "MorbusOnkoVizitPLDop_id", 'name' => 'Name', 'iconCls' => '', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);

						$arr_1 = $this->_mergeNodesData('EvnUslugaPar', $data, $arr_1);
						$arr_1 = $this->_mergeNodesData('EvnDirection', $data, $arr_1);
					}
					if ($data['object'] == 'EvnSection')
					{
						$arr_1 = $this->_mergeNodesData('EvnUslugaPar', $data, $arr_1);
						$arr_1 = $this->_mergeNodesData('EvnUslugaCommon', $data, $arr_1);
						$arr_1 = $this->_mergeNodesData('EvnDirection', $data, $arr_1);
						$arr_1 = $this->_mergeNodesData('EvnUslugaOper', $data, $arr_1);

						//MorbusOnkoLeave
						$childrens = $this->EPH_model->GetMorbusOnkoLeaveNodeList($data);
						$childrens = $this->_formNameNode('MorbusOnkoLeave', $childrens, $data['ARMType']);
						$field = Array('object' => "MorbusOnkoLeave", 'id' => "MorbusOnkoLeave_id", 'name' => 'Name', 'iconCls' => '', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field);
						$arr_1 = array_merge($arr_1,$arr_2);
					}
					if ($data['object'] == 'EvnVizitDispDop')
					{
						$arr_1 = $this->_mergeNodesData('EvnDirection', $data, $arr_1);
						// EvnUslugaDispDop
						if ( $this->_baseEvnClassExists('EvnUslugaDispDop') ) {
							$childrens = $this->EPH_model->GetEvnUslugaDispDopNodeList($data);
							$childrens = $this->_formNameNode('EvnUslugaDispDop', $childrens, $data['ARMType']);
							$field = Array('object' => "EvnUslugaDispDop", 'id' => "EvnUslugaDispDop_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_1 = $this->_getPersonEPHChild($childrens, $field);
						}
						/*
						// PersonDisp - Диспансеризация: постановка
						$childrens = $this->EPH_model->GetBegPersonDispNodeList($data);
						$childrens = $this->_formNameNode('BegPersonDisp', $childrens, $data['ARMType']);
						$field = Array('object' => "PersonDisp",'id' => "PersonDisp_id", 'name' => 'Name', 'iconCls' => 'disp16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field, "Beg");
						$arr_1 = array_merge($arr_1,$arr_2);
						// PersonDisp - Диспансеризация: снятие
						$childrens = $this->EPH_model->GetEndPersonDispNodeList($data);
						$childrens = $this->_formNameNode('EndPersonDisp', $childrens, $data['ARMType']);
						$field = Array('object' => "PersonDisp",'id' => "PersonDisp_id", 'name' => 'Name', 'iconCls' => 'disp16', 'leaf' => true, 'cls' => "folder");
						$arr_2 = $this->_getPersonEPHChild($childrens, $field, "End");
						$arr_1 = array_merge($arr_1,$arr_2);
						*/
					}
					if ($data['object'] == 'EvnUslugaPar')
					{
						// EvnCostPrint - справки о стомиости лечения
						if ( $this->_baseEvnClassExists('EvnCostPrint') ) {
							$childrens = $this->EPH_model->GetEvnCostPrintNodeList($data);
							$childrens = $this->_formNameNode('EvnCostPrint', $childrens, $data['ARMType']);
							$field = Array('object' => "EvnCostPrint", 'id' => "EvnCostPrint_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->_getPersonEPHChild($childrens, $field);
							$arr_1 = array_merge($arr_1, $arr_2);
						}
					}
					break;
				case 3:
					if ($data['object'] == 'EvnUslugaPar')
					{
						// EvnCostPrint - справки о стомиости лечения
						if ( $this->_baseEvnClassExists('EvnCostPrint') ) {
							$childrens = $this->EPH_model->GetEvnCostPrintNodeList($data);
							$childrens = $this->_formNameNode('EvnCostPrint', $childrens, $data['ARMType']);
							$field = Array('object' => "EvnCostPrint", 'id' => "EvnCostPrint_id", 'name' => 'Name', 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->_getPersonEPHChild($childrens, $field);
							$arr_1 = array_merge($arr_1, $arr_2);
						}
					}
					break;
				case 4:
					if (($data['level_two']=='LpuSection') || ($data['level_two']=='All'))
						{
						$childrens = $this->EPH_model->GetLpuSectionPidNodeList($data);
						$field = Array('object' => "LpuSection",'id' => "LpuSection_id", 'name' => "LpuSection_Name", 'iconCls' => 'lpu-section16', 'leaf' => true, 'cls' => "folder");
						}
					break;
				default:
					$childrens = $this->EPH_model->GetLpuNodeList($data);
					$field = Array('object' => "Lpu",'id' => "Lpu_id", 'name' => "Lpu_Name", 'iconCls' => 'lpu16', 'leaf' => false, 'cls' => "folder");
					break;
			}
		}
		//$val = $this->_getPersonEPHChild($childrens, $field);

		if ( count($arr_1) > 0 ) {
			$val = array_merge($arr_1,$val);
		}

		usort($val, "cmp");

		//PROMEDWEB-9278
		//Риск новорожденых только при первом открытии ЭМК
		if (getRegionNick() == 'ufa' && $data['level'] == 0) {
			$paramshr = Array('Person_id' => $data['Person_id']);
			$risktype = $this->PersonNewBorn_model->GetHighRisk($paramshr);
			$val[0]['newbornrisk'] = $risktype;
		}

		$json = $json . json_encode($val);
		//var_dump($json);die;
		echo $json;

		return true;
	}

	/**
	 * Возвращает данные дочерних узлов
	 */
	private function _mergeNodesData($object, $data, $arr_1) {
		$childrens = array();
		$field = array();

		if ( $this->_personEMKTreeType == 0 && !$this->_baseEvnClassExists($object) ) {
			return $arr_1;
		}

		switch ( true ) {
			case ('EvnUslugaPar' == $object):
				// Параклиническая услуга

				if ($this->usePostgreLis) {
					$childrens = $this->EPH_model->GetEvnUslugaParNodeListLis($data);
				}
				if (is_array($childrens)) {
					$childrens = array_merge($childrens, $this->EPH_model->GetEvnUslugaParNodeList($data, $childrens));
				}

				$childrens = $this->_formNameNode('EvnUslugaPar', $childrens, $data['ARMType']);
				$field = Array(
					'object' => 'EvnUslugaPar',
					'id' => 'EvnUslugaPar_id',
					'isMicroLab' => '1',
					'name' => 'Name',
					'iconCls' => 'evnusluga-16',//document16
					'leaf' => false,
					'cls' => 'folder'
				);
				if ($this->_personEMKTreeLevel == 0 && $this->_personEMKTreeType == 0) {
					$field['iconCls'] = 'document24';
					$field['cls'] = 'x-tree-node-24x24';
				}
				break;
			case ('EvnUslugaCommon' == $object):
				// Общая услуга
				$childrens = $this->EPH_model->GetEvnUslugaCommonNodeList($data);
				$childrens = $this->_formNameNode('EvnUslugaCommon', $childrens, $data['ARMType']);
				$field = Array(
					'object' => 'EvnUslugaCommon',
					'id' => 'EvnUslugaCommon_id',
					'name' => 'Name',
					'iconCls' => 'evnusluga-16',//document16
					'leaf' => false,
					'cls' => 'folder'
				);
				if ($this->_personEMKTreeLevel == 0 && $this->_personEMKTreeType == 0) {
					$field['iconCls'] = 'document24';
					$field['cls'] = 'x-tree-node-24x24';
				}
				break;
			case ('EvnUslugaTelemed' == $object):
				// Услуга телемед
				$childrens = $this->EPH_model->GetEvnUslugaTelemedNodeList($data);
				$childrens = $this->_formNameNode('EvnUslugaTelemed', $childrens, $data['ARMType']);
				$field = Array(
					'object' => 'EvnUslugaTelemed',
					'id' => 'EvnUslugaTelemed_id',
					'name' => 'Name',
					'iconCls' => 'evnusluga-16',//document16
					'leaf' => true,
					'cls' => 'folder'
				);
				if ($this->_personEMKTreeLevel == 0 && $this->_personEMKTreeType == 0) {
					$field['iconCls'] = 'document24';
					$field['cls'] = 'x-tree-node-24x24';
				}
				break;
			case ('EvnDirection' == $object):
				// Выписанные электронные направления
				if ($this->usePostgreLis) {
					$childrens = $this->EPH_model->GetEvnDirectionNodeListLis($data);
				}
				if (is_array($childrens)) {
					$childrens = array_merge($childrens, $this->EPH_model->GetEvnDirectionNodeList($data, $childrens));
				}
				$childrens = $this->_formNameNode('EvnDirection', $childrens, $data['ARMType']);
				$field = Array(
					'object' => 'EvnDirection',
					'id' => 'EvnDirection_id',
					'name' => 'Name',
					'iconCls' => 'direction-16',
					'leaf' => true,
					'cls' => 'folder'
				);
				if ($this->_personEMKTreeLevel == 0 && $this->_personEMKTreeType == 0) {
					$field['iconCls'] = 'document24';
					$field['cls'] = 'x-tree-node-24x24';
				}
				break;


			case ('EvnUslugaOper' == $object):
				// оперативная услуга
				$childrens = $this->EPH_model->GetEvnUslugaOperNodeList($data);
				$childrens = $this->_formNameNode('EvnUslugaOper', $childrens, $data['ARMType']);
				$field = Array(
					'object' => 'EvnUslugaOper',
					'id' => 'EvnUslugaOper_id',
					'name' => 'Name',
					'iconCls' => 'evnusluga-16',//document16
					'leaf' => false,
					'cls' => 'folder'
				);
				if ($this->_personEMKTreeLevel == 0 && $this->_personEMKTreeType == 0) {
					$field['iconCls'] = 'document24';
					$field['cls'] = 'x-tree-node-24x24';
				}
				break;
		}

		if ( !empty($childrens) && count($childrens) > 0 ) {
			$arr_2 = $this->_getPersonEPHChild($childrens, $field);
			return array_merge($arr_1,$arr_2);
		}

		return $arr_1;
	}
	
	/**
	 * история болезни в формате для новой ЭМК
	 */
	function getPersonHistory() {
		$data = $this->ProcessInputData('getPersonHistory', true);
		if ( $data === false ) {
			return false;
		}
		$this->load->library('swFilterResponse'); 
		$this->load->database();
		$this->load->model('EPH_model', 'EPH_model'); 
		$response = $this->EPH_model->getPersonHistory($data);	
		$this->ProcessModelMultiList( $response, true, true )->ReturnData();
		return true;
	}

	/**
	 * история болезни в формате для новой ЭМК + инфа о пациенте + ТАП
	 */
	function getAll() {
		$data = $this->ProcessInputData('getAll', true);
		if ( $data === false ) {
			return false;
		}
		$this->load->library('swFilterResponse');
		$this->load->database();
		$this->load->model('EPH_model');
		$this->load->model('Common_model');
		$response = array(
			'Error_Msg' => ''
		);
		$response['personHistory'] = $this->EPH_model->getPersonHistory($data);
		$data['mode'] = 'PersonInfoPanel';
		$response['personInfo'] = $this->Common_model->loadPersonData($data);

		if (!empty($data['object'])) {
			switch($data['object']) {
				case 'EvnPL':
					$data['EvnPL_id'] = $data['object_value'];
					$response['evnPLData'] = $this->EPH_model->loadEvnPLForm($data);
					if (!empty($response['evnPLData'][0]['EvnVizitPL'][0]['EvnVizitPL_id'])) {
						// грузим сразу ещё посещение
						$data['EvnVizitPL_id'] = $response['evnPLData'][0]['EvnVizitPL'][0]['EvnVizitPL_id'];
						$response['evnVizitPLData'] = $this->EPH_model->loadEvnVizitPLForm($data);
					}
					break;
			}
		}

		$this->ProcessModelSave( $response, true, true )->ReturnData();
		return true;
	}
	
	/**
	 * Загрузка формы ТАП в новой ЭМК
	 */
	function loadEvnPLForm() {
		$data = $this->ProcessInputData('loadEvnPLForm', true);
		if ( $data === false ) {
			return false;
		}

		$this->load->database();
		$this->load->model('EPH_model', 'EPH_model');
		$response = $this->EPH_model->loadEvnPLForm($data);
		$this->ProcessModelList( $response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Загрузка формы стомат. ТАП в новой ЭМК
	 */
	function loadEvnPLStomForm() {
		$data = $this->ProcessInputData('loadEvnPLStomForm', true);
		if ( $data === false ) {
			return false;
		}

		$this->load->database();
		$this->load->model('EPH_model', 'EPH_model');
		$response = $this->EPH_model->loadEvnPLStomForm($data);
		$this->ProcessModelList( $response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Загрузка формы информации о пациенте в новой ЭМК
	 */
	function loadPersonForm() {
		$data = $this->ProcessInputData('loadPersonForm', true);
		if ( $data === false ) {
			return false;
		}

		$this->load->database();
		$this->load->model('EPH_model', 'EPH_model');
		$response = $this->EPH_model->loadPersonForm($data);
		$this->ProcessModelList( $response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Загрузка формы посещения в новой ЭМК
	 */
	function loadEvnVizitPLForm() {
		$data = $this->ProcessInputData('loadEvnVizitPLForm', true);
		if ( $data === false ) {
			return false;
		}

		$this->load->database();
		$this->load->model('EPH_model', 'EPH_model');
		$response = $this->EPH_model->loadEvnVizitPLForm($data);
		$this->ProcessModelList( $response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Загрузка списка специфик
	 */
	function loadEvnVizitPLListMorbus() {
		$data = $this->ProcessInputData('loadEvnVizitPLForm', true);
		if ( $data === false ) {
			return false;
		}

		$this->load->database();
		$this->load->model('EPH_model', 'EPH_model');
		$response = $this->EPH_model->loadEvnVizitPLListMorbus($data);
		
		$this->ProcessModelList( $response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Загрузка формы стомат. посещения в новой ЭМК
	 */
	function loadEvnVizitPLStomForm() {
		$data = $this->ProcessInputData('loadEvnVizitPLStomForm', true);
		if ( $data === false ) {
			return false;
		}

		$this->load->database();
		$this->load->model('EPH_model', 'EPH_model');
		$response = $this->EPH_model->loadEvnVizitPLStomForm($data);
		$this->ProcessModelList( $response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Определяет наличие событий для пациента по наименованию базового класса
	 */
	private function _baseEvnClassExists($evnClass = 'Evn') {
		$result = false;

		foreach ( $this->_personEvnClassList as $value ) {
			if ( strtolower(substr($value, 0, strlen($evnClass))) == strtolower($evnClass) ) {
				$result = true;
				break;
			}
		}

		return $result;
	}

	/**
	 * Сохранение схемы лекарственной терапии
	 */
	function saveDrugTherapyScheme() {
		$data = $this->ProcessInputData('saveDrugTherapyScheme', true);
		if ( $data === false ) {
			return false;
		}

		$this->load->database();
		$this->load->model('EPH_model', 'EPH_model');
		$response = $this->EPH_model->saveDrugTherapyScheme($data);
		$this->ProcessModelSave( $response, true, true )->ReturnData();
	}

	/**
	 * Удаление схемы лекарственной терапии 
	 */
	function deleteDrugTherapyScheme() {
		$data = $this->ProcessInputData('deleteDrugTherapyScheme', true);
		if ( $data === false ) {
			return false;
		}

		$this->load->database();
		$this->load->model('EPH_model', 'EPH_model');
		$response = $this->EPH_model->deleteDrugTherapyScheme($data);
		$this->ProcessModelSave( $response, true, true )->ReturnData();
	}
	
	/**
	 * Получение посещений в рамках ТАП
	 */
	function getEvnVizitPLStomNodeList(){
		$data = $this->ProcessInputData('getEvnVizitNodeList', true);
		if ( $data === false || !in_array($data['Object'], array('EvnVizitPLStom', 'EvnVizitPL'))) {
			return false;
		}
		
		$this->load->database();
		$this->load->model('EPH_model', 'EPH_model');
		if($data['Object'] == 'EvnVizitPLStom'){
			$data['EvnPLStom_id'] = $data['Object_id'];
			$response = $this->EPH_model->GetEvnVizitPLStomNodeList($data);
		}else if($data['Object'] == 'EvnVizitPL'){
			$data['EvnPL_id'] = $data['Object_id'];
			$response = $this->EPH_model->GetEvnVizitPLNodeList($data);
		}
		
		$this->ProcessModelList( $response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * получить последний протокол BK для ВМП
	 */
	function getLastDirectionVKforVMP(){
		$data = $this->ProcessInputData('getLastDirectionVKforVMP', true);
		if ( $data === false ) {
			return false;
		}

		$this->load->database();
		$this->load->model('EMK_model', 'EMK_model');
		$response = $this->EMK_model->getLastDirectionVKforVMP($data);
		$this->ProcessModelSave( $response, true, true )->ReturnData();
	}
}
