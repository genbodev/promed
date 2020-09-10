<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnVizit - контроллер для работы с посещениями поликлиники
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			Polka
* @access			public
* @copyright		Copyright (c) 2009 Swan Ltd.
* @author	Stas Bykov aka Savage (savage1981@gmail.com)
* @version			26.01.2012

 * @property EvnVizit_model $dbmodel
 * @property Template_model $Template_model
 * @property EvnVizitPL_model $EvnVizitPL_model
 * @property EvnVizitPLStom_model $EvnVizitPLStom_model
 * @property EvnPL_model $EvnPL_model
 * @property EvnPLStom_model $EvnPLStom_model
 * @property EvnPS_model $EvnPS_model
 */
class EvnVizit extends swController {
	public $inputRules = array(
		'loadDiagCombo' => array(
			array(
				'field' => 'EvnPLStom_id',
				'label' => 'Идентификатор ТАП',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnVizitPLStom_id',
				'label' => 'Идентификатор посещения',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadToothCard' => array(
			array('field' => 'EvnVizitPLStom_id', 'label' => 'Идентификатор стомат. посещения', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id')
		),
		'loadEvnVizitCombo' => array(
			array('field' => 'rid', 'label' => 'Идентификатор родительского события', 'rules' => 'required', 'type' => 'id')
		),
		'loadEvnVizitPLListForLLO' => [
			[ 'field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'begDate', 'label' => 'Дата начала', 'rules' => '', 'type' => 'date' ],
			[ 'field' => 'endDate', 'label' => 'Дата окончания', 'rules' => '', 'type' => 'date' ],
			[ 'field' => 'Person_SurName', 'label' => 'Фамилия', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'Person_FirName', 'label' => 'Имя', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'Person_SecName', 'label' => 'Отчество', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'Person_BirthDay', 'label' => 'Отчество', 'rules' => '', 'type' => 'date' ],
			[ 'field' => 'Person_Snils', 'label' => 'СНИЛС', 'rules' => '', 'type' => 'snils' ],
			[ 'field' => 'pmUser_insID', 'label' => 'Пользователь', 'rules' => '', 'type' => 'id' ],
		],
		'loadEvnVizitPLEditForm' => array(
			array(
				'field' => 'EvnVizitPL_id',
				'label' => 'Идентификатор посещения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'FormType',
				'label' => 'Тип посещения',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadEvnVizitPLGrid' => array(
			array(
				'field' => 'EvnVizitPL_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveBitePersonType' => array(
			array(
				'field' => 'BitePersonData_id',
				'label' => 'Идентификатор родительского события',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnVizitPLStom_id',
				'label' => 'Идентификатор посещения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'BitePersonData_setDate',
				'label' => 'Дата посещения',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'BitePersonData_disDate',
				'label' => 'Дата посещения',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'BitePersonType_id',
				'label' => 'Идентификатор типа прикуса',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadEvnVizitPLGridAll' => array(
			array('default' => 100, 'field' => 'limit', 'label' => 'Лимит записей', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'start', 'label' => 'Начальная запись', 'rules' => '', 'type' => 'int'),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор медицинской организации',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Идентификатор место работы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => '',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Person_Surname',
				'label' => 'Фамилия пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Firname',
				'label' => 'Имя пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Secname',
				'label' => 'Отчество пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Birthday',
				'label' => 'Дата рождения пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'begDate',
				'label' => 'Дата начала',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'endDate',
				'label' => 'Дата окончания',
				'rules' => '',
				'type' => 'date'
			),


		),
		'loadEvnVizitPLStomEditForm' => array(
			array(
				'field' => 'EvnVizitPLStom_id',
				'label' => 'Идентификатор посещения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field'	=>'fromMZ',
				'label'	=> 'Из АРМ МЗ',
				'rules'	=> '',
				'type'	=> 'string',
				'default' => '1'
			)
		),
		'loadEvnVizitPLStomGrid' => array(
			array(
				'field' => 'EvnVizitPLStom_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadLastEvnVizitPLData' => array(
			array(
				'field' => 'EvnVizitPL_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadFirstEvnVizitPLData' => array(
			array(
				'field' => 'EvnVizitPL_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadLastEvnPLStomData' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Пациент',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadMesList' => array(
			array('field' => 'Diag_id', 'label' => 'Диагноз', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnVizit_id', 'label' => 'Идентификатор посещения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnVizit_setDate', 'label' => 'Дата посещения', 'rules' => 'trim', 'type' => 'date'),
			array('field' => 'EvnVizitPLStom_id', 'label' => 'Посещение заболевания', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Врач', 'rules' => '', 'type' => 'id'),
			array('field' => 'Mes_id', 'label' => 'Идентификатор МЭС', 'rules' => '', 'type' => 'id'),
			array('field' => 'mode', 'label' => 'Режим загрузки', 'rules' => '', 'type' => 'string'),
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id')
		),
		'loadMesEkbList' => array(
			array('field' => 'Mes_id', 'label' => 'МЭС', 'rules' => '', 'type' => 'id'),
			array('field' => 'MesType_id', 'label' => 'Тип МЭС', 'rules' => '', 'type' => 'id'),
			array('field' => 'Mes_Codes', 'label' => 'Список кодов', 'rules' => '', 'type' => 'string'),
			array('field' => 'Mes_Date', 'label' => 'Дата', 'rules' => '', 'type' => 'date'),
			array('field' => 'query', 'label' => 'Строка поиска', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'UslugaComplex_id', 'label' => 'Идентификатор комплексной услуги', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnLabRequest_id', 'label' => 'Идентификатор лаб. заявки', 'rules' => '', 'type' => 'id'),
		),
		'getEvnVizitPLDoubles' => array(
			array(
				'field' => 'EvnVizitPL_id',
				'label' => 'Идентификатор события',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'saveEvnVizitPLDoubles' => array(
			array(
				'field' => 'EvnVizitPLDoublesData',
				'label' => 'EvnVizitPLDoublesData',
				'rules' => 'required',
				'type' => 'json_array',
				'assoc' => true
			),
		),
		'printEvnVizitPL' => array(
			array(
				'field' => 'EvnVizitPL_id',
				'label' => 'Идентификатор события',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'printHtml',
				'label' => 'Печать HTML',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'is_tcpdf',
				'label' => 'Вариант печати в PDF',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'useWkhtmltopdf',
				'label' => 'Вариант печати с помощью wkhtmltopdf',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadLastEvnVizitPLStomData' => array(
			array(
				'field' => 'EvnVizitPLStom_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'copyEvnVizitPL' => array(
			array(
				'field' => 'EvnPL_id',
				'label' => 'Идентификатор талона',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnVizitPL_id',
				'label' => 'Идентификатор посещения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Пациент',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'setEvnVizitParameter' => array(
			array('field' => 'id','label' => 'Идентификатор объекта','rules' => 'required','type' => 'id')
			,array('field' => 'param_name','label' => 'Системное имя параметра','rules' => 'required','type' => 'string')
			,array('field' => 'method_name','label' => 'Системное имя метода','rules' => '','type' => 'string')
			,array('field' => 'param_value','label' => 'Значение параметра','rules' => '','type' => 'string')
			,array('field' => 'options','label' => 'Дополнительные опции','rules' => '','type' => 'string')
		),
		'getLastEvnVizitPL' => array(
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type'  => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type'  => 'id')
		),
		'saveEvnVizitPL' => array(

		),
		'loadReceptionTableGrid' => array(
			[ 'field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int' ],
			array(
				'field' => 'Lpu_id',
				'label' => '',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'LpuBuilding_id',
				'label' => '',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'врач',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'begDate',
				'label' => 'Дата начала периода',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'endDate',
				'label' => 'Дата окончания периода',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SecName',
				'label' => 'Отчество',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_BirthDay',
				'label' => 'Дата рождения',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'CardAtTheReception',
				'label' => 'Карта на приёме?',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'RequestFromTheDoctor',
				'label' => 'Запрос от врача приёма',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'field_numberCard',
				'label' => '№ амб. карты',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'attachmentLpuBuilding_id',
				'label' => 'уровень расположения службы (на МО или на подразделении)',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonAmbulatCard_id',
				'label' => 'Идентификатор АК',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'пациент',
				'rules' => 'trim',
				'type' => 'id'
			)
		),
		'checkMesOldUslugaComplexFields' => array(
			array(
				'field' => 'EvnVizitPL_id',
				'label' => 'Идентификатор посещения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuUnitType_id',
				'label' => 'Идентификатор типа группы отделений',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Идентификатор диагноза',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_setDate',
				'label' => 'Дата посещения',
				'rules' => '',
				'type' => 'date'
			)
		),
		'getEvnVizitPLDrugTherapyScheme' => array(
			array(
				'field' => 'EvnVizitPL_id',
				'label' => 'Идентификатор посещения',
				'rules' => '',
				'type' => 'id'
			)
		),
		'createEvnVizitPLForLLO' => array(
			array(
				'field' => 'session-id',
				'label' => 'Идентификатор сессии',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'mc',
				'label' => 'Код организации',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'mcod',
				'label' => 'Код группы отделений',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'DiagCode',
				'label' => 'Код МКБ',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Snl',
				'label' => 'Номер СНИЛС',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Sm',
				'label' => 'Фамилия пациента',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Nm',
				'label' => 'Имя пациента',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Pm',
				'label' => 'Отчество пациента',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'BD',
				'label' => 'Дата рождения пациента',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'Sx',
				'label' => 'Пол пациента',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'PolisForm',
				'label' => 'Форма полиса',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PolisType',
				'label' => 'Тип полиса',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'SI',
				'label' => 'Серия полиса',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'NI',
				'label' => 'Номер полиса',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Terr',
				'label' => 'Территория страхования',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'PolisOrg',
				'label' => 'Организация, выдавшая полис',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'PolisDt',
				'label' => 'Дата выдачи полиса',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'Ar',
				'label' => 'Адрес регистрации пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'AL',
				'label' => 'Адрес проживания пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'AreaReg',
				'label' => 'Район адреса регистрации',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'AreaLive',
				'label' => 'Район адреса проживания',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'DocTypeId',
				'label' => 'Тип документа, удостоверяющего личность',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'DocTypeSeries',
				'label' => 'Серия документа',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'DocTypeNumber',
				'label' => 'Номер документа',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'DocTypeWhom',
				'label' => 'Наименование органа выдавшего документ',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'DocTypeDate',
				'label' => 'Дата выдачи документа. Формат DD.MM.YYYY',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'SmV',
				'label' => 'Фамилия врача',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'NmV',
				'label' => 'Имя врача',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'PmV',
				'label' => 'Отчество врача',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'BDV',
				'label' => 'Дата рождения врача',
				'rules' => 'required',
				'type' => 'date'
			)
		),
		'loadListOfOpenVisitsToThePatientClinic' => array(
			array('field' => 'Person_id', 'label' => 'идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Место работы', 'rules' => 'required', 'type' => 'id'),
		),
		'loadDataEvnVizitPL' => array(
			array('field' => 'EvnVizitPL_id', 'label' => 'идентификатор случая посещения', 'rules' => 'required', 'type' => 'id'),
		),
		'getLastEvnVisitPLToday' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			)
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnVizit_model', 'dbmodel');
	}

	/**
	*  Запись одного параметра события
	*  Используется: ЭМК
     *
     * @return bool
     */
	function setEvnVizitParameter() {
		$data = $this->ProcessInputData('setEvnVizitParameter', true);
		if ($data === false) { return false; }

		if(getRegionNick() == 'vologda' && 'LpuSectionProfile_id' == $data['param_name']){
			$this->load->model('EvnPL_model', 'eplmodel');
			$arrVizit = $this->eplmodel->checkEvnVizitsPL(['EvnVizitPL_id' => $data['id'],'LpuSectionProfile_id' => $data['param_value'],'closeAPL'=>1]);

			if(isset($arrVizit[0])) {
				$LpuSectionProfile_Code = $this->eplmodel->getProfileCode($data['LpuSectionProfile_id']);
				$options = $this->eplmodel->getGlobalOptions();
				foreach ($arrVizit as $id => $row) {
					if (
						!in_array($LpuSectionProfile_Code, $options['globals']['exceptionprofiles'])
						&& ($LpuSectionProfile_Code != $arrVizit[$id]['LpuSectionProfile_Code'])
						&& $arrVizit[$id]['MedStaffFact_id'] != $data['session']['CurARM']['MedStaffFact_id']
					) {
						throw new Exception('Профиль отделения текущего посещения должен соответствовать профилю отделения других посещений в этом ТАП.');
						return false;
					}
				}
			}
		}
		//166824
		if ( getRegionNick() == 'penza' && $data['param_name'] == 'EvnPLBase_IsFinish' && $data['param_value'] == 2 ){
			if ( !$this->dbmodel->checkPenzaVizitTypeId( [ 'Evn_pid' => $data['id'] ] )[0]['VizitType_id'] ){
				throw new Exception('Поле "Цель посещения" обязательно для заполнения' , 400);
			}
		}

		try {
			if (!empty($data['options'])) {
				$options = json_decode($data['options'], true);
				$data = array_merge($data, $options);
			}
			
			// определяем какую модель использовать
			$this->load->model('Evn_model', 'Evn_model');
			$evn_class_id = $this->Evn_model->getEvnClass([
				'id' => $data['id']
			]);
			$model = null;
			switch ($evn_class_id) {
				case 30:
					$this->load->model('EvnPS_model');
					$model = $this->EvnPS_model;
					break;
				case 11:
					$this->load->model('EvnVizitPL_model');
					$model = $this->EvnVizitPL_model;
					break;
				case 13:
					$this->load->model('EvnVizitPLStom_model');
					$model = $this->EvnVizitPLStom_model;
					break;
				case 3:
					$this->load->model('EvnPL_model');
					$model = $this->EvnPL_model;
					break;
				case 6:
					$this->load->model('EvnPLStom_model');
					$model = $this->EvnPLStom_model;
					break;
				case 101:
					$this->load->model('EvnPLDispDop13_model');
					$model = $this->EvnPLDispDop13_model;
					break;
				case 103:
					$this->load->model('EvnPLDispProf_model');
					$model = $this->EvnPLDispProf_model;
					break;
				case 9:
					$this->load->model('EvnPLDispOrp_model');
					$model = $this->EvnPLDispOrp13_model;
					break;
				case 104:
					$this->load->model('EvnPLDispTeenInspection_model');
					$model = $this->EvnPLDispTeenInspection_model;
					break;
				case 189:
					$this->load->model('EvnPLDispMigrant_model');
					$model = $this->EvnPLDispMigrant_model;
					break;
				case 190:
					$this->load->model('EvnPLDispDriver_model');
					$model = $this->EvnPLDispDriver_model;
					break;
			}

			if (empty($model)) { throw new Exception('Неправильный объект', 400); }

			$method = 'update';

			// если указан $data['method_name'], записываем его метод, иначе смотрим на $data['param_name']
			if (!empty($data['method_name'])) $method .= $data['method_name'];
			else {

			// проверяем наличие метода у модели
			// имя метода должно быть в верблюжьем стиле!
			// и начинаться с update
			$parts = explode('_', $data['param_name']);
				foreach($parts as $word) { $method .= ucfirst($word); }
			};

			if (!method_exists($model, $method)) { throw new Exception('Указанное поле нельзя изменить', 400); }

			// устанавливаем сценарий и параметры
			$model->setScenario(EvnAbstract_model::SCENARIO_SET_ATTRIBUTE);
			$model->setParams($data);

			// вызываем метод у модели
			$response = call_user_func(array($model, $method), $data['id'], $data['param_value'], $data['param_name']);

			if( !empty($response['EvnVizitPL_id']) && $response['EvnVizitPL_id'] > 0 && $data['param_name'] == 'PersonDisp_id' ){
				$evpl_resp = $this->EvnVizitPL_model->getEvnVizitPLSetDate([
					'EvnVizitPL_id' => $response['EvnVizitPL_id']
				]);

				if (is_object($evpl_resp)) {
					$evpl_resp = $evpl_resp->result('array');

					if (!empty($evpl_resp[0]['EvnVizitPL_setDate'])) {
						$this->load->model('Polka_PersonDisp_model', 'PersonDisp_model');
						$params = array();
						$params['PersonDispVizit_NextFactDate'] = $evpl_resp[0]['EvnVizitPL_setDate'];
						$params['EvnVizitPL_id'] = $response['EvnVizitPL_id'];
						$params['PersonDisp_id'] = $data['param_value'];
						$params['pmUser_id'] = $data['pmUser_id'];
						$this->PersonDisp_model->savePersonDispEvnVizitPL($params);
					}
				}
			}

			if(getRegionNick() == 'vologda' && in_array($evn_class_id, array(3, 6, 11, 13)) && !empty($data['param_name']))
			{
				$pid = null;
				if(in_array($evn_class_id, array(11, 13)) && in_array($data['param_name'], array('VizitType_id', 'TreatmentClass_id'))){
					$evnVizitPLtable = '';
					if(in_array($evn_class_id, array(13))) $evnVizitPLtable = 'EvnVizitPLStom';
					if(in_array($evn_class_id, array(11))) $evnVizitPLtable = 'EvnVizitPL';
					if($evnVizitPLtable && !empty($response[$evnVizitPLtable.'_id'])){
						$this->load->model('Evn_model', 'Evn_model');
						$evpl_resp = $this->Evn_model->getPid([
							'id' => $response[$evnVizitPLtable . '_id'],
							'evnVizitPLtable' => $evnVizitPLtable
						]);
						if (is_object($evpl_resp)) {
							$evpl_resp = $evpl_resp->result('array');
							$pid = (!empty($evpl_resp[0]['pid'])) ? $evpl_resp[0]['pid'] : null;
						}
					}
				}elseif (in_array($evn_class_id, array(3, 6)) && $data['param_name'] == 'EvnPLBase_IsFinish' && $data['param_value'] == 2) {
					if($evn_class_id == 6 && !empty($response['EvnPLStom_id'])) $pid = $response['EvnPLStom_id'];
					if($evn_class_id == 3 && !empty($response['EvnPL_id'])) $pid = $response['EvnPL_id'];
				}
				
				if($pid){
					if(in_array($evn_class_id, array(13, 6))){
						$this->load->model('EvnVizitPLStom_model');
						$this->EvnVizitPLStom_model->updateEvnVizitNumGroup($pid);
					}
					if(in_array($evn_class_id, array(11, 3))){
						$this->load->model('EvnVizitPL_model');
						$this->EvnVizitPL_model->updateEvnVizitNumGroup($pid);
					}
				}				
			}
			
			$updateCVI = in_array($data['param_name'], ['EvnPLBase_IsFinish', 'Diag_id', 'ResultClass_id']);
			if(!empty($response['EvnVizitPL_id']) && $updateCVI && empty($response['Error_Msg'])) {
				$params = $data;
				$params['source'] = 'EvnVizitPL';
				$params['EvnVizitPL_id'] = $response['EvnVizitPL_id'];
				$this->load->model('CVIRegistry_model', 'CVIRegistry_model');
				$this->CVIRegistry_model->saveCVIEvent($params);
			}

			if(!empty($response['EvnPL_id']) && $updateCVI && empty($response['Error_Msg'])) {
				$params = $data;
				$params['source'] = 'EvnPL';
				$params['EvnPL_id'] = $response['EvnPL_id'];
				$this->load->model('CVIRegistry_model', 'CVIRegistry_model');
				$this->CVIRegistry_model->saveCVIEvent($params);
			}

			$this->ProcessModelSave($response, true, 'При записи параметра посещения пациентом поликлиники возникли ошибки');
			$this->ReturnData();

			return true;

		} catch (Exception $e) {
			$this->ReturnData(array(
				'success' => false,
				'Error_Msg' => toUtf($e->getMessage())
			));
			return false;
		}
	}

	/**
	 *  Печать протокола осмотра посещения стоматологии
	 *  Входящие данные: $_POST['EvnVizitPLStom_id']
	 *  На выходе: HTML-строка в кодировке Win1251
	 *  Используется: панель специфики
	 *
	 * @throws Exception
	 * @return bool
	 */
	function printEvnVizitPLStom() {
		$this->inputRules['printEvnVizitPL'][0]['field'] = 'EvnVizitPLStom_id';
		$data = $this->ProcessInputData('printEvnVizitPL', true);
		if ($data === false)
		{
			return false;
		}

		/*$ConvertToUTF8 = false;
		$response = $this->dbmodel->getEvnVizitPLStomViewData($data);
		$this->ProcessModelList($response, $ConvertToUTF8, true);*/
		$this->OutData = array(array());
		$data['Evn_id'] = $data['EvnVizitPLStom_id'];
		return $this->printEvnVizitProtocol($data);
	}

	/**
	 *  Получение дублей
	 */
	function getEvnVizitPLDoubles() {
		$data = $this->ProcessInputData('getEvnVizitPLDoubles', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getEvnVizitPLDoubles($data);
		$this->ProcessModelSave($response,true,'Ошибка получения дублей')->ReturnData();

		return true;
	}

	/**
	 *  Сохранение прикуса
	 */
	function saveBitePersonType() {
		$data = $this->ProcessInputData('saveBitePersonType', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveBitePersonType($data);
		$this->ProcessModelSave($response,true,'Ошибка получения дублей')->ReturnData();

		return true;
	}

	/**
	 *  Сохранение дублей
	 */
	function saveEvnVizitPLDoubles() {
		$data = $this->ProcessInputData('saveEvnVizitPLDoubles', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveEvnVizitPLDoubles($data);
		$this->ProcessModelSave($response,true,'Ошибка сохранения дублей')->ReturnData();

		return true;
	}

	/**
	 *  Печать протокола осмотра поликлинического посещения
	 *  Входящие данные: $_POST['EvnVizitPL_id']
	 *  На выходе: HTML-строка в кодировке Win1251
	 *  Используется: ЭМК
	 *
	 * @throws Exception
	 * @return bool
	 */
	function printEvnVizitPL() {
		$data = $this->ProcessInputData('printEvnVizitPL', true);
		if ($data === false)
		{
			return false;
		}

		$ConvertToUTF8 = false;
		$this->load->library('swFilterResponse');
		$response = $this->dbmodel->getEvnVizitPLViewData($data);
		$this->ProcessModelList($response, $ConvertToUTF8, true);
		$data['Evn_id'] = $data['EvnVizitPL_id'];
		return $this->printEvnVizitProtocol($data);
	}

	/**
	 *  Печать протокола осмотра посещения
	 *  На выходе: HTML-строка в кодировке Win1251
	 *
	 * @throws Exception
	 * @return bool
	 */
	private function printEvnVizitProtocol($data) {

		$this->load->library('swFilterResponse'); 
		$this->load->helper("Xml");
		$this->load->helper("PDF");
		$this->load->helper('Options');
		$this->load->library('parser');
		$this->load->model('Template_model', 'Template_model');

		// Получаем настройки
		$options = getOptions();
		
		$xml_data = $this->Template_model->getXmlTemplateAndXmlData(array('Evn_id'=>$data['Evn_id']));
		if ($xml_data === false)
		{
			echo '<div>Ошибка получения Xml-данных.</div>';
			return false;
		}
        $docTplSettings = $this->Template_model->getXmlTemplateSettingsArrFromJson($xml_data['XmlTemplate_Settings']);
		$this->OutData[0]['XmlTemplate_Caption'] = '';
		if (isset($xml_data['XmlTemplate_Caption']))
		{
			$this->OutData[0]['XmlTemplate_Caption'] = $xml_data['XmlTemplate_Caption'];
		}
		//exit($xml_data['XmlTemplate_HtmlTemplate']);
		$html_from_xml = processingXmlToHtml($xml_data['EvnXml_Data'],$xml_data['XmlTemplate_Data']);
		$doc = '';
		if ($html_from_xml === false)
		{
			/**
			 * Обработка
			 */
			function processingNodeValue(&$doc)
			{
				// https://redmine.swan.perm.ru/issues/12618
				// В некоторых местах замена переноса строки на <br /> не требуется, т.к. в самом шаблоне уже указан <br />
				// Поэтому поголовная замена \n на <br /> заменена регуляркой

				// $doc = str_replace("\n", "<br />\n", $doc);
				$doc = preg_replace("/(\<br\>|\<br\/\>|\<br \/\>)?(\n|\n\r|\r\n)+/", "<br />", $doc);
			}
			
			if (strpos($xml_data['EvnXml_Data'], '<UserTemplateData>'))
			{
				//есть UserTemplateData в EvnXml_Data. Используется устаревший шаблон без разметки областей ввода данных и областей только для печати
				$xml_data_arr = transformEvnXmlDataToArr(toUTF($xml_data['EvnXml_Data']));
				array_walk($xml_data_arr,'ConvertFromUTF8ToWin1251');
				array_walk($xml_data_arr,'processingNodeValue');
				$html_from_xml = $xml_data_arr['UserTemplateData'];
			}
			else if ($xml_data['XmlTemplate_HtmlTemplate'])
			{
				// нет тегов data в XmlTemplate_Data и нет узла UserTemplateData в EvnXml_Data
				$xml_data_arr = transformEvnXmlDataToArr(toUTF($xml_data['EvnXml_Data']));
				array_walk($xml_data_arr,'ConvertFromUTF8ToWin1251');
				array_walk($xml_data_arr,'processingNodeValue');
				//это нужно для печати объектов с типом Параметр и список значений 
				$this->load->library('swMarker');
				$xml_data['XmlTemplate_HtmlTemplate'] = swMarker::createParameterValueFields($xml_data['XmlTemplate_HtmlTemplate'],$xml_data['EvnXml_id'],$xml_data_arr);
				$html_from_xml = $this->parser->parse_string($xml_data['XmlTemplate_HtmlTemplate'], $xml_data_arr, true);
			}
		}
		if($html_from_xml)
		{
			$doc = $this->parser->parse_string($html_from_xml, $this->OutData[0], true);
			//это нужно для печати шаблонных маркеров
			$this->load->library('swMarker'); 
			$doc = swMarker::processingTextWithMarkers($doc, $data['Evn_id'], array('isPrint'=>true));
		}
		else
		{
			$doc = '<div>Ошибка получения HTML из Xml-документа. Возможно, что в шаблоне отсутствует разметка областей ввода данных или Xml-шаблон имеет неправильный формат.</div>';
		}
		
		// Для всех регионов, кроме Перми печать в ПДФ
		if ($data['session']['region']['nick'] != 'perm') {
			if ($data['printHtml']==2) { // принудительно в PDF 
				echo '<html><head><title>Печатная форма</title><link href="/css/emk.css" rel="stylesheet" type="text/css" /></head><body id="rightEmkPanelPrint">'.$doc.'</body></html>';
				return true;
			} 
		} else {
			if (isset($data['printHtml'])) {
				echo '<html><head><title>Печатная форма</title><link href="/css/emk.css" rel="stylesheet" type="text/css" /></head><body id="rightEmkPanelPrint">'.$doc.'</body></html>';
				return true;
			}
		}
		
        $paper_format  = 'A4';
        $paper_orient  = 'landscape';
        $font_size     = '10';
        $margin_top    = 10;
        $margin_right  = 10;
        $margin_bottom = 10;
        $margin_left   = 10;
        if (isset($docTplSettings['PaperFormat_id']) && (2 == $docTplSettings['PaperFormat_id'])) {
            $paper_format = 'A5';
        }
        if (isset($docTplSettings['PaperOrient_id']) && (2 == $docTplSettings['PaperOrient_id'])) {
            $paper_orient = 'portrait';
        }
        if (isset($docTplSettings['FontSize_id']) && (in_array($docTplSettings['FontSize_id'], array('6', '8', '10', '12', '14')))) {
            $font_size = $docTplSettings['FontSize_id'];
        }
        if (isset($docTplSettings['margin_top']) && ($docTplSettings['margin_top'])) {
            $margin_top = $docTplSettings['margin_top'];
        }
        if (isset($docTplSettings['margin_right']) && ($docTplSettings['margin_right'])) {
            $margin_right = $docTplSettings['margin_right'];
        }
        if (isset($docTplSettings['margin_bottom']) && ($docTplSettings['margin_bottom'])) {
            $margin_bottom = $docTplSettings['margin_bottom'];
        }
        if (isset($docTplSettings['margin_left']) && ($docTplSettings['margin_left'])) {
            $margin_left = $docTplSettings['margin_left'];
        }
		
		$styles = file_get_contents('css/emk.css');
		$html = "<style type='text/css'>{$styles}</style>".$doc;
		
		$plugin = 'mpdf';
		
		if (!empty($data['useWkhtmltopdf'])) {
			$plugin = 'wkpdf';
		}
		
		// При задании формата использовать настройки
		if ((!isset($data['is_tcpdf'])) || ($data['is_tcpdf']!=1)) {
			print_pdf( $plugin, $paper_orient, $paper_format, $font_size, $margin_left, $margin_right, $margin_top, $margin_bottom, $html, 'osmotr.pdf', 'I');
		} else {
			throw new Exception('Вход в ветку запрещен');
		}
		return true;
	}

	/**
	*  Получение данных для формы редактирования посещения
	*  Входящие данные: $_POST['EvnVizitPL_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования посещения
	*/
	function loadEvnVizitPLEditForm() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadEvnVizitPLEditForm', true);
		if ($data === false)
		{
			return false;
		}

		$response = $this->dbmodel->loadEvnVizitPLEditForm($data);

		if (getRegionNick() == 'vologda' && !empty($response[0]['EvnVizitPL_id'])) {
			$this->load->model('Registry' . (getRegionNick() == 'ufa' ? 'Ufa' : '') . '_model', 'Reg_model');
			$dbConnection = getRegistryChecksDBConnection();
			if ($dbConnection != 'default') {
				$this->regDB = $this->load->database($dbConnection, true);
				$this->Reg_model->db = $this->regDB;
			}
			$registryData = $this->Reg_model->checkEvnAccessInRegistry(array(
				'EvnVizitPL_id' => $response[0]['EvnVizitPL_id'],
				'EvnVizitPL_NumGroup' => $response[0]['EvnVizitPL_NumGroup'],
				'Lpu_id' => $data['Lpu_id'],
				'session' => $data['session']
			), 'edit');

			if (is_array($registryData)) {
				if (!empty($registryData['Error_Msg'])) {
					$response[0]['accessType'] = 'view';
					$response[0]['AlertReg_Msg'] = $registryData['Error_Msg'];
				} elseif (!empty($registryData['Alert_Msg'])) {
					$response[0]['AlertReg_Msg'] = $registryData['Alert_Msg'];
				}
			}
		}

        $this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}


	/**
	*  Получение списка поликлинических посещений
	*  Входящие данные: $_POST['EvnVizitPL_pid']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования талона амбулаторного пациента
	*/
	function loadEvnVizitPLGrid() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadEvnVizitPLGrid', true);
		if ($data === false)
		{
			return false;
		}

		$response = $this->dbmodel->loadEvnVizitPLGrid($data);
        $this->ProcessModelList($response,true,true)->ReturnData();

		return false;
	}


	/**
	 * Вывод всех посещений пациентом поликлиники для формы "Картохранилище"
	 * @return bool
	 */
	function loadEvnVizitPLGridAll() {

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadEvnVizitPLGridAll', true);
		if ($data === false)
		{
			return false;
		}

		$response = $this->dbmodel->loadEvnVizitPLGridAll($data);

		$this->ProcessModelMultiList($response,true,'При запросе возникла ошибка.')->ReturnData();

		return true;
	}


	/**
	*  Получение данных для формы редактирования посещения стоматологии
	*  Входящие данные: $_POST['EvnVizitPLStom_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования посещения стоматологии
	*/
	function loadEvnVizitPLStomEditForm() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadEvnVizitPLStomEditForm', true);
		if ($data === false)
		{
			return false;
		}

		$response = $this->dbmodel->loadEvnVizitPLStomEditForm($data);

		if (getRegionNick() == 'vologda' && !empty($response[0]['EvnVizitPLStom_id'])) {
			$this->load->model('Registry' . (getRegionNick() == 'ufa' ? 'Ufa' : '') . '_model', 'Reg_model');
			$dbConnection = getRegistryChecksDBConnection();
			if ($dbConnection != 'default') {
				$this->regDB = $this->load->database($dbConnection, true);
				$this->Reg_model->db = $this->regDB;
			}
			$registryData = $this->Reg_model->checkEvnAccessInRegistry(array(
				'EvnVizitPLStom_id' => $response[0]['EvnVizitPLStom_id'],
				'EvnVizitPLStom_NumGroup' => $response[0]['EvnVizitPLStom_NumGroup'],
				'Lpu_id' => $data['Lpu_id'],
				'session' => $data['session']
			), 'edit');

			if (is_array($registryData)) {
				if (!empty($registryData['Error_Msg'])) {
					$response[0]['accessType'] = 'view';
					$response[0]['AlertReg_Msg'] = $registryData['Error_Msg'];
				} elseif (!empty($registryData['Alert_Msg'])) {
					$response[0]['AlertReg_Msg'] = $registryData['Alert_Msg'];
				}
			}
		}

        if (getRegionNick() === 'kz') {
            $this->load->model('UslugaMedType_model');

            $UslugaMedType_id = $this->UslugaMedType_model->getUslugaMedTypeIdByEvnId($response[0]['EvnVizitPLStom_id']);
            if ($UslugaMedType_id) {
                $response[0]['UslugaMedType_id'] = $UslugaMedType_id;
            }
        }

        $this->ProcessModelList($response,true,true)->ReturnData();

		return true;
	}


	/**
	*  Получение списка посещений стоматологии
	*  Входящие данные: $_POST['EvnVizitPLStom_pid']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования стоматологического талона амбулаторного пациента
	*/
	function loadEvnVizitPLStomGrid() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadEvnVizitPLStomGrid', true);
		if ($data === false)
		{
			return false;
		}

		$response = $this->dbmodel->loadEvnVizitPLStomGrid($data);
        $this->ProcessModelList($response,true,true)->ReturnData();

		return false;
	}


	/**
	 *  Получение идешника последнего стоматологического посещения
	 *  Входящие данные: $_POST['Person_id']
	 *  На выходе: JSON-строка
	 */
	function loadLastEvnPLStomData() {
		$data = $this->ProcessInputData('loadLastEvnPLStomData', false);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadLastEvnPLStomData($data);
		if (is_array($response)) {
            $response['success'] = true;
			$this->OutData = $response;
		} else {
			$this->OutData = array(
				'success' => true,
				'EvnVizitPLStom_id' => null,
				'EvnPLStom_id' => null
			);
		}
		$this->ReturnData();
		return true;
	}

	/**
	*  Получение данных первого посещения 
	*  Входящие данные: $_POST['EvnVizitPL_pid']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования посещения
	*/
	function loadFirstEvnVizitPLData() {
		$data = $this->ProcessInputData('loadFirstEvnVizitPLData', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadFirstEvnVizitPLData($data);
		$this->ProcessModelList($response,true,true)->ReturnData();

		return true;
	}


	/**
	*  Получение данных последнего посещения 
	*  Входящие данные: $_POST['EvnVizitPL_pid']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования посещения
	*/
	function loadLastEvnVizitPLData() {
		$data = $this->ProcessInputData('loadLastEvnVizitPLData', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLastEvnVizitPLData($data);
		$this->ProcessModelList($response,true,true)->ReturnData();

		return true;
	}


	/**
	*  Получение данных последнего посещения стоматологии
	*  Входящие данные: $_POST['EvnVizitPLStom_pid']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования посещения стоматологии
	*/
	function loadLastEvnVizitPLStomData() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadLastEvnVizitPLStomData', true);
		if ($data === false)
		{
			return false;
		}

		$response = $this->dbmodel->loadLastEvnVizitPLStomData($data);
		$this->ProcessModelList($response,true,true)->ReturnData();

		return false;
	}


	/**
	 *	Получение списка МЭС
	 *	Входящие данные: ...
	 *	На выходе: JSON-строка
	 *	Используется: форма редактирования случая движения пациента в стационаре
	 *	@return bool
	 */
	function loadMesList() {
		$data = $this->ProcessInputData('loadMesList', true);
		if ( $data === false ) { return false; }

		if ( (empty($data['Diag_id']) || empty($data['EvnVizit_setDate']) || empty($data['LpuSection_id']) || empty($data['MedStaffFact_id']) || empty($data['Person_id'])) && empty($data['Mes_id']) ) {
			$this->ReturnError('Неверные параметры');
			return false;
		}

		$response = $this->dbmodel->loadMesOldList($data);
        $this->ProcessModelList($response,true,true)->ReturnData();

		return false;
	}

	/**
	 *	Получение списка МЭС
	 *	Входящие данные: ...
	 *	На выходе: JSON-строка
	 *	Используется: форма редактирования посещения в стоматологию (Екб)
	 *	@return bool
	 */
	function loadMesEkbList() {
		$data = $this->ProcessInputData('loadMesEkbList', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadMesOldEkbList($data);
        $this->ProcessModelList($response,true,true)->ReturnData();

		return false;
	}


	/**
	 *	Получение данных зубной карты
	 *	Входящие данные: EvnVizitPLStom_id, Person_id
	 *	На выходе: JSON-строка
	 *	Используется: форма редактирования стоматологической услуги
	 */
	function loadToothCard() {
		$data = $this->ProcessInputData('loadToothCard', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadToothCard($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}

	/**
	 *	Получение списка посещений для комбо
	 *	Входящие данные: $_POST['rid']
	 *	На выходе: JSON-строка
	 *	Используется: форма редактирования стомат. услуги
	 */
	function loadEvnVizitCombo() {
		$data = $this->ProcessInputData('loadEvnVizitCombo', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnVizitCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *	Получение списка диагнозов для комбо
	 *	Входящие данные: $_POST['rid']
	 *	На выходе: JSON-строка
	 *	Используется: форма редактирования стомат. посещения
	 */
	function loadDiagCombo() {
		$data = $this->ProcessInputData('loadDiagCombo', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadDiagCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение последнего посещения в ТАПе
	 */
	function getLastEvnVizitPL() {
		$data = $this->ProcessInputData('getLastEvnVizitPL',true);
		if ($data === false)return false;
		$this->load->model('EvnVizitPL_model');
		$response = $this->EvnVizitPL_model->getLastEvnVizitPL($data);
		$this->ProcessModelList($response, true,true)->ReturnData();
		return true;
	}
	
	/**
	*  Получение списка для таблицы записей на приём
	*  Используется: форма "Рабочее место сотрудника картохранилища"
	*/
	function loadReceptionTableGrid() {
		$data = array();
		$this->load->helper('Reg_helper');
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadReceptionTableGrid', true);
		if ($data === false)
		{
			return false;
		}

		$response = $this->dbmodel->loadReceptionTableGrid($data);
        $this->ProcessModelMultiList($response,true,true)->ReturnData();

		return false;
	}

	/**
	 * Проверка наличия связок для отображения полей
	 */
	function checkMesOldUslugaComplexFields() {
		$data = $this->ProcessInputData('checkMesOldUslugaComplexFields', true);
		if ($data === false) { return false; }
		$this->load->model('EvnVizitPL_model');
		$response = $this->EvnVizitPL_model->checkMesOldUslugaComplexFields($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * @return bool
	 */
	public function loadEvnVizitPLListForLLO() {
		$data = $this->ProcessInputData('loadEvnVizitPLListForLLO', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnVizitPLListForLLO($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Идентификация пациента и создание посещения
	 */
	public function createEvnVizitPLForLLO() {
		$data = $this->ProcessInputData('createEvnVizitPLForLLO', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->createEvnVizitPLForLLO($data);
		$this->ProcessModelSave($response, true, 'Ошибка при создании посещения')->ReturnData();
	}
	
	/**
	 *	Получение списка открытых посещений поликлиники пациента для комбо
	 */
	function loadListOfOpenVisitsToThePatientClinic() {
		$data = $this->ProcessInputData('loadListOfOpenVisitsToThePatientClinic', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadListOfOpenVisitsToThePatientClinic($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 *	Получение списка открытых посещений поликлиники пациента для комбо
	 */
	function loadDataEvnVizitPL() {
		$data = $this->ProcessInputData('loadDataEvnVizitPL', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadDataEvnVizitPL($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получить ID последнего посещения пациента на текущий день
	 */
	public function getLastEvnVisitPLToday() {
		$data = $this->ProcessInputData('getLastEvnVisitPLToday', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getLastEvnVisitPLToday($data);

		if (is_array($response)) {
			$response['success'] = true;
			$this->OutData = $response;
		} else {
			$this->OutData = array(
				'success' => true,
				'EvnVizitPL_id' => null,
				'MedPersonal_id' => null
			);
		}
		$this->ReturnData();
		return true;
	}
}
