<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Mes - методы для работы с МЭСами
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 - 2010 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @author       Markoff A.A. <markov@swan.perm.ru>
* @version      08.08.2011
* @property MesOld_model MesOld_model
* @property MesOld_model dbmodel
*/
class MesOld extends swController {
    
    /**
     * comment
     */
    function __construct() {
		parent::__construct();

		$this->inputRules = array(
			'loadKsgList' => array(
				array(
					'field' => 'query',
					'label' => 'Строка поиска',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Mes_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'groupByCode',
					'label' => 'Признак группировки',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'mesTypeList',
					'label' => 'Тип МЭСа - КСГ/КПГ',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'startYear',
					'label' => 'Фильтр по году начала действия',
					'rules' => '',
					'type' => 'int'
				)
			),
			'exportMesOldToDbf' => array(
				array(
					'field' => 'MesStatus_id',
					'label' => 'Статус',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MesProf_id',
					'label' => 'Специальность',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MesAgeGroup_id',
					'label' => 'Возрастная группа',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MesLevel_id',
					'label' => 'Уровень',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_id',
					'label' => 'Диагноз',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Mes_KoikoDni_From',
					'label' => 'Нормативный срок лечения c',
					'rules' => 'trim',
					'type' => 'float'
				),
				array(
					'field' => 'Mes_KoikoDni_To',
					'label' => 'Нормативный срок лечения по',
					'rules' => 'trim',
					'type' => 'float'
				),
				array(
					'field' => 'OmsLpuUnitType_id',
					'label' => 'Тип стационара',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Mes_begDT_Range',
					'label' => 'Дата начала',
					'rules' => 'trim',
					'type' => 'daterange'
				),
				array(
					'field' => 'Mes_endDT_Range',
					'label' => 'Дата окончания',
					'rules' => 'trim',
					'type' => 'daterange'
				)
			),
			'loadMesOldComboSearchList' =>array(				
				array(
					'field' => 'Diag_Name',
					'label' => 'Наименование',
					'rules' => 'trim',
					'type' => 'string'
				)
			),
			'loadKsgCombo' =>array(
				array(
					'field' => 'year',
					'label' => 'Год',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadKpgCombo' =>array(
				array(
					'field' => 'year',
					'label' => 'Год',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadMesOldCodeList' =>array(
				array(
					'field' => 'Mes_id',
					'label' => 'Идентификатор МЭС',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'query',
					'label' => 'Код МЭС',
					'rules' => 'trim',
					'type' => 'string'
				)
			),
			'loadMesOldSearchList' =>array(
				array(
					'field' => 'MesStatus_id',
					'label' => 'Статус',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MesProf_id',
					'label' => 'Специальность',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MesAgeGroup_id',
					'label' => 'Возрастная группа',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MesLevel_id',
					'label' => 'Уровень',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_Code_From',
					'label' => 'Диагноз с',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Diag_Code_To',
					'label' => 'Диагноз по',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Mes_KoikoDni_From',
					'label' => 'Нормативный срок лечения c',
					'rules' => 'trim',
					'type' => 'float'
				),
				array(
					'field' => 'Mes_KoikoDni_To',
					'label' => 'Нормативный срок лечения по',
					'rules' => 'trim',
					'type' => 'float'
				),
				array(
					'field' => 'OmsLpuUnitType_id',
					'label' => 'Тип стационара',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Mes_begDT_Range',
					'label' => 'Дата начала',
					'rules' => 'trim',
					'type' => 'daterange'
				),
				array(
					'field' => 'Mes_endDT_Range',
					'label' => 'Дата окончания',
					'rules' => 'trim',
					'type' => 'daterange'
				),
				array(
	                'field' => 'sort',
					'label' => 'Сортировка',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
	                'field' => 'dir',
					'label' => 'Направление сортировки',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'default' => 0,
	                'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'default' => 100,
	                'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'MedicalCareKind_id',
					'label' => 'Вид медицинской помощи',
					'rules' => '',
					'type' => 'int'
				)
			),
			'addMesOld' =>array(
				array(
					'field' => 'MesProf_id',
					'label' => 'Специальность',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MesAgeGroup_id',
					'label' => 'Возрастная группа',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MesLevel_id',
					'label' => 'Уровень',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_id',
					'label' => 'Диагноз',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'OmsLpuUnitType_id',
					'label' => 'Тип стационара',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Mes_KoikoDni',
					'label' => 'Нормативный срок лечения',
					'rules' => 'trim',
					'type' => 'float'
				),
				array(
					'field' => 'Mes_VizitNumber',
					'label' => 'Порядковый номер посещения',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'Mes_begDT',
					'label' => 'Дата начала',
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'field' => 'Mes_endDT',
					'label' => 'Дата окончания',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'Mes_DiagClinical',
					'label' => 'Текст',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Mes_DiagVolume',
					'label' => 'Текст',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Mes_Consulting',
					'label' => 'Текст',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Mes_CureVolume',
					'label' => 'Текст',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Mes_QualityMeasure',
					'label' => 'Текст',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Mes_ResultClass',
					'label' => 'Текст',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Mes_ComplRisk',
					'label' => 'Текст',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'action',
					'label' => 'Действие',
					'rules' => 'trim|required',
					'type' => 'string'
				)
			),
			'editMesOld' =>array(
				array(
					'field' => 'Mes_id',
					'label' => 'Идентификатор МЭСа',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Mes_Code',
					'label' => 'Код МЭС',
					'rules' => 'trim',
					'type' => 'string'
				),
			),
			'loadMesOld' =>array(
				array(
					'field' => 'Mes_id',
					'label' => 'Идентификатор МЭСа',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'save' => array(
				array(
					'field' => 'Mes_id',
					'label' => 'идентификатор МЭС',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'Mes_KoikoDni',
					'label' => 'количество койко-дней',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'Mes_VizitNumber',
					'label' => 'Порядковый номер посещения',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Mes_begDT',
					'label' => 'дата начала',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'Mes_endDT',
					'label' => 'дата окончания',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'MesAgeGroup_id',
					'label' => 'идентификатор возрастной группы МЭС',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MesLevel_id',
					'label' => 'идентификатор уровня МЭС',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MesProf_id',
					'label' => 'идентификатор профиля МЭС',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Diag_id',
					'label' => 'идентификатор диагноза',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'идентификатор справочника ЛПУ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Mes_HStac',
					'label' => 'признак вида стационара',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Region_id',
					'label' => 'Идентификатор региона справочника территорий',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSectionProfile_id',
					'label' => 'Профиль отделения в ЛПУ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MesOperType_id',
					'label' => 'Тип лечения',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MedicalCareKind_id',
					'label' => 'Вид медицинской помощи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PayMedType_id',
					'label' => 'Способ оплаты медицинской помощи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Mes_IsModern',
					'label' => 'Признак МЭСа по модернизации',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Mes_KoikoDniMin',
					'label' => 'Минимальное количество койко-дней',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'curARMType',
					'label' => 'ткущий АРМ пользователя',
					'rules' => 'trim',
					'type' => 'string'
				),
			),
		);
	}

	/**
	 * Получение списка КСГ
	 */
	function loadKsgList() {
		$this->load->database();
		$this->load->model("MesOld_model");

		$data = $this->ProcessInputData('loadKsgList', true);
		if ( $data === false ) { return false; }

		$response = $this->MesOld_model->loadKsgList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	* Сохранение МЭС
	*/
	function save() {
		if (!isset($_SESSION['groups_array'])){
			$_SESSION['groups_array'] = explode('|', $_SESSION['groups']);
		}
		
		$data = $this->ProcessInputData('save', true);
		if ( $data === false ) { return false; }
		
		$region = getRegionNick();
		$save_access = false;
		$curARMType = (!empty($data['curARMType'])) ? $data['curARMType'] : $_SESSION['CurArmType'];
		
		//Доступ для добавления / редактирования МЭС имеют пользователи с указанными группами:
		if($region=='vologda' || $region == 'buryatiya'){
			if( ($curARMType == 'superadmin' && havingGroup('OuzChief')) || ($curARMType == 'mstat' && havingGroup('EditingMES')) ) {
				$save_access = true;
			}
		}else if( havingGroup('OuzChief') || havingGroup('OuzUser') || havingGroup('OuzAdmin') ){
			//- Руководитель ОУЗ, Пользователь ОУЗ, Администратор ОУЗ
			$save_access = true;
		}
		
		if ($save_access) {
			if ($data){
				$this->load->database();
				$this->load->model("MesOld_model", "MesOld_model");
				$response = $this->MesOld_model->save($data);
				if(isset($_POST['UslugaArr'])){
					ConvertFromWin1251ToUTF8($_POST['UslugaArr']);
					$UslArr = json_decode($_POST['UslugaArr'], true);
					$this->load->model('MesUsluga_model', 'MesUsluga_model');
					foreach ($UslArr as $val) {
						if($val['Usluga_id_Name']!=''){
							$this->MesUsluga_model->setMesUsluga_id(null);

							if (isset($val['Usluga_id'])&&$val['Usluga_id']!='') {
								$this->MesUsluga_model->setUsluga_id($val['Usluga_id']);
							}else{
								$this->MesUsluga_model->setUsluga_id(null);
							}
							if (isset($val['UslugaComplex_id'])&&$val['UslugaComplex_id']!='') {
								$this->MesUsluga_model->setUslugaComplex_id($val['UslugaComplex_id']);
							}else{
								$this->MesUsluga_model->setUslugaComplex_id(null);
							}
							if (isset($response[0]['Mes_id'])) {
								$this->MesUsluga_model->setMes_id($response[0]['Mes_id']);
							}
							if (isset($val['MesUsluga_UslugaCount'])&&$val['MesUsluga_UslugaCount']!="") {
								$this->MesUsluga_model->setMesUsluga_UslugaCount($val['MesUsluga_UslugaCount']);
							}else{
								$this->MesUsluga_model->setMesUsluga_UslugaCount(0);
							}
							if($response[0]['Mes_id']>0)
							$resp = $this->MesUsluga_model->save();
						}
					}
				}
				$this->ProcessModelSave($response, true, 'Ошибка при сохранении МЭС')->ReturnData();
				return true;
			} else {
				return false;
			}
		} else {
			throw new Exception('Access denied');
		}
	}

	/**
	* Поиск МЭС по наименованию диагноза
	*/
	function loadMesOldComboSearchList() {
		$this->load->database();
		$this->load->model("MesOld_model", "dbmodel");

		$data = array();
		$val  = array();
		
		$data = array_merge($data, getSessionParams());
		$err = getInputParams($data, $this->inputRules['loadMesOldComboSearchList']);
		if (strlen($err) > 0) {
			echo json_return_errors($err);
			return false;
		}
		
		$response = $this->dbmodel->loadMesOldComboSearchList($data);		
		
		if (is_array($response['data']) && (count($response['data'])>0)) {
			foreach ($response['data'] as $row) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}
		$this->ReturnData($val);
		return true;
	}
	
	/**
	* Для комбобокса код МЭС
	*/
	function loadMesOldCodeList() {
		$this->load->database();
		$this->load->model("MesOld_model", "dbmodel");

		$data = array();
		$val  = array();
		
		$data = array_merge($data, getSessionParams());
		$err = getInputParams($data, $this->inputRules['loadMesOldCodeList']);
		if (strlen($err) > 0) {
			echo json_return_errors($err);
			return false;
		}
		
		$response = $this->dbmodel->searchFullMesOldCodeList($data);
		
		if (is_array($response['data']) && (count($response['data'])>0)) {
			foreach ($response['data'] as $row) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}
		$this->ReturnData($val);
		return true;
	}
	
	/**
	* Поиск по МЭСам
	*/
	function loadMesOldSearchList() {
		$this->load->database();
		$this->load->model("MesOld_model", "dbmodel");

		$data = $this->ProcessInputData('loadMesOldSearchList', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadMesOldSearchList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	* Загрузка данных формы МЭСов
	*/
	function loadMesOld() {
		$this->load->database();
		$this->load->model("MesOld_model", "dbmodel");

		$data = array();
		$val  = array();
		
		$data = array_merge($data, getSessionParams());
		$err = getInputParams($data, $this->inputRules['loadMesOld']);
		if (strlen($err) > 0) 
		{
			echo json_return_errors($err);
			return false;
		}
			
		$response = $this->dbmodel->loadMesOld($data);
		
		if ( is_array($response) && (count($response)>0) )
		{
			foreach ($response as $row)
			{
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}
		$this->ReturnData($val);
		return true;
	}

	/**
	* Загрузка кодов КСГ
	*/
	function loadKsgCombo() {
		$this->load->database();
		$this->load->model("MesOld_model", "dbmodel");

		$data = $this->ProcessInputData('loadKsgCombo', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadKsgCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	* Загрузка кодов КПГ
	*/
	function loadKpgCombo() {
		$this->load->database();
		$this->load->model("MesOld_model", "dbmodel");

		$data = $this->ProcessInputData('loadKpgCombo', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadKpgCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

}
