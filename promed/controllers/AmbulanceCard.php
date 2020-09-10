<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * AmbulanceCard - контроллер для выполенния операций с картами вызова.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Ambulance
 * @access       public
 * @copyright    Copyright (c) 2010 Swan Ltd.
 * @author       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
 * @version      05.01.2010
 *
 * @property AmbulanceCard_model dbmodel
 */

class AmbulanceCard extends swController {
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('AmbulanceCard_model', 'dbmodel');

		$this->db2 = $this->load->database('registry', true);

		$this->inputRules = array(
			'getAmbulanceCard' => array( 
				array(
					'field' => 'AmbulanceCard_id',
					'label' => 'Идентификатор карты',
					'rules' => 'required|trim',
					'type' => 'id'
				)
			),
			'saveAmbulanceCard' => array(
				array('field' => 'NUMV', 'label' => 'CmpCallCard_Numv', 'rules' => 'trim|required', 'type' => 'int'),
				array('field' => 'NGOD', 'label' => 'CmpCallCard_Ngod', 'rules' => 'trim|required', 'type' => 'int'),
				array('field' => 'PRTY', 'label' => 'CmpCallCard_Prty', 'rules' => 'trim', 'type' => 'int'),
				array('field' => 'SECT', 'label' => 'CmpCallCard_Sect', 'rules' => 'trim', 'type' => 'int'),
				array('field' => 'CITY', 'label' => 'CmpCallCard_City', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'ULIC', 'label' => 'CmpCallCard_Ulic', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'DOM', 'label' => 'CmpCallCard_Dom', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'KVAR', 'label' => 'CmpCallCard_Kvar', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'PODZ', 'label' => 'CmpCallCard_Podz', 'rules' => 'trim', 'type' => 'int'),
				array('field' => 'ETAJ', 'label' => 'CmpCallCard_Etaj', 'rules' => 'trim', 'type' => 'int'),
				array('field' => 'KODP', 'label' => 'CmpCallCard_Kodp', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'TELF', 'label' => 'CmpCallCard_Telf', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'PLS', 'label' => 'cmpPlace_Str', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'PLC', 'label' => 'cmpPlace_Code', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'ARS', 'label' => 'cmpArea_Str', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'ARC', 'label' => 'cmpArea_Code', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'REAS', 'label' => 'cmpReason_Str', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'REAC', 'label' => 'cmpReason_Code', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'PROFS', 'label' => 'cmpProfile_cStr', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'PROFC', 'label' => 'cmpProfile_cCode', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'ARGS', 'label' => 'cmpArea_gStr', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'ARGC', 'label' => 'cmpArea_gCode', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'ARPS', 'label' => 'cmpArea_pStr', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'ARPC', 'label' => 'cmpArea_pCode', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'DIAGS', 'label' => 'cmpDiag_oStr', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'DIAGC', 'label' => 'cmpDiag_oCode', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'DIAGS1', 'label' => 'cmpDiag_aStr', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'DIAGC1', 'label' => 'cmpDiag_aCode', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'PRFBS', 'label' => 'cmpProfile_bStr', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'PRFBC', 'label' => 'cmpProfile_bCode', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'REZLS', 'label' => 'cmpResult_Str', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'REZLC', 'label' => 'cmpResult_Code', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'TRAVS', 'label' => 'cmpTrauma_Str', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'TRAVC', 'label' => 'cmpTrauma_Code', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'DGUC', 'label' => 'Diag_uCode', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'DGSC', 'label' => 'Diag_sCode', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'LPUC', 'label' => 'lpu_Code', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'LPUS', 'label' => 'lpu_Str', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'CLTPS', 'label' => 'cmpCallType_Str', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'CLTPC', 'label' => 'cmpCallType_Code', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'TALS', 'label' => 'cmpTalon_Str', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'TALC', 'label' => 'cmpTalon_Code', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'PBDD', 'label' => 'person_BirthDay', 'rules' => 'trim', 'type' => 'datetime'),
				array('field' => 'CCIZVS', 'label' => 'CmpCallCard_Izv1', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'CCTIZD', 'label' => 'CmpCallCard_Tiz1', 'rules' => 'trim', 'type' => 'datetime'),
				array('field' => 'CCPCS', 'label' => 'cmpCallCard_PCity', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'CCPUS', 'label' => 'cmpCallCard_PUlic', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'CCPDS', 'label' => 'cmpCallCard_PDom', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'CCPKS', 'label' => 'cmpCallCard_PKvar', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'PPSS', 'label' => 'person_PolisSer', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'PPNS', 'label' => 'person_PolisNum', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'CCMI', 'label' => 'cmpCallCard_Medc', 'rules' => 'trim', 'type' => 'int'),
				array('field' => 'VOZR', 'label' => 'Person_Age', 'rules' => 'trim', 'type' => 'int'),
				array('field' => 'COMM', 'label' => 'CmpCallCard_Comm', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'FAM', 'label' => 'Person_SurName', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'IMYA', 'label' => 'Person_FirName', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'OTCH', 'label' => 'Person_SecName', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'POL', 'label' => 'person_Sex', 'rules' => 'trim', 'type' => 'int'),
				array('field' => 'KTOV', 'label' => 'CmpCallCard_Ktov', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'SMPT', 'label' => 'CmpCallCard_Smpt', 'rules' => 'trim', 'type' => 'int'),
				array('field' => 'STAN', 'label' => 'CmpCallCard_Stan', 'rules' => 'trim', 'type' => 'int'),
				array('field' => 'DPRM', 'label' => 'CmpCallCard_prmDT', 'rules' => 'trim', 'type' => 'datetime'),
				array('field' => 'ALK', 'label' => 'CmpCallCard_IsAlco', 'rules' => 'trim', 'type' => 'int'),
				array('field' => 'NUMB', 'label' => 'CmpCallCard_Numb', 'rules' => 'trim', 'type' => 'int'),
				array('field' => 'SMPB', 'label' => 'CmpCallCard_Smpb', 'rules' => 'trim', 'type' => 'int'),
				array('field' => 'STBR', 'label' => 'CmpCallCard_Stbr', 'rules' => 'trim', 'type' => 'int'),
				array('field' => 'STBB', 'label' => 'CmpCallCard_Stbb', 'rules' => 'trim', 'type' => 'int'),
				array('field' => 'NCAR', 'label' => 'CmpCallCard_Ncar', 'rules' => 'trim', 'type' => 'int'),
				array('field' => 'RCOD', 'label' => 'CmpCallCard_RCod', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'Tabn', 'label' => 'CmpCallCard_TabN', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'TAB2', 'label' => 'CmpCallCard_Tab2', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'TAB3', 'label' => 'CmpCallCard_Tab3', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'TAB4', 'label' => 'CmpCallCard_Tab4', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'EXPO', 'label' => 'CmpCallCard_Expo', 'rules' => 'trim', 'type' => 'int'),
				array('field' => 'DOKT', 'label' => 'CmpCallCard_Dokt', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'SMPP', 'label' => 'CmpCallCard_Smpp', 'rules' => 'trim', 'type' => 'int'),
				array('field' => 'VR51', 'label' => 'CmpCallCard_Vr51', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'D201', 'label' => 'CmpCallCard_D201', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'DSP1', 'label' => 'CmpCallCard_Dsp1', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'DSP2', 'label' => 'CmpCallCard_Dsp2', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'DSPP', 'label' => 'CmpCallCard_Dspp', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'DSP3', 'label' => 'CmpCallCard_Dsp3', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'KAKP', 'label' => 'CmpCallCard_Kakp', 'rules' => 'trim', 'type' => 'int'),
				array('field' => 'TPER', 'label' => 'CmpCallCard_Tper', 'rules' => 'trim', 'type' => 'datetime'),
				array('field' => 'VYEZ', 'label' => 'CmpCallCard_Vyez', 'rules' => 'trim', 'type' => 'datetime'),
				array('field' => 'PRZD', 'label' => 'CmpCallCard_Przd', 'rules' => 'trim', 'type' => 'datetime'),
				array('field' => 'TGSP', 'label' => 'CmpCallCard_Tgsp', 'rules' => 'trim', 'type' => 'datetime'),
				array('field' => 'TSTA', 'label' => 'CmpCallCard_Tsta', 'rules' => 'trim', 'type' => 'datetime'),
				array('field' => 'TISP', 'label' => 'CmpCallCard_Tisp', 'rules' => 'trim', 'type' => 'datetime'),
				array('field' => 'TVZV', 'label' => 'CmpCallCard_Tvzv', 'rules' => 'trim', 'type' => 'datetime'),
				array('field' => 'KILO', 'label' => 'CmpCallCard_Kilo', 'rules' => 'trim', 'type' => 'float'),
				array('field' => 'DLIT', 'label' => 'CmpCallCard_Dlit', 'rules' => 'trim', 'type' => 'int'),
				array('field' => 'PRDL', 'label' => 'CmpCallCard_Prdl', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'POLI', 'label' => 'CmpCallCard_IsPoli', 'rules' => 'trim', 'type' => 'int'),
				array('field' => 'novalue1', 'label' => 'CmpCallCard_Line', 'rules' => 'trim', 'type' => 'int'),
				array('field' => 'MEDS', 'label' => 'ResultDeseaseType_Code', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'personID', 'label' => 'Person_id', 'rules' => 'trim', 'type' => 'int')
			),
			'saveAmbulanceCardFromPromed' => array(
				array(
					'field' => 'AmbulanceCard_id',
					'label' => 'Идентификатор карты',
					'rules' => 'required|trim',
					'type' => 'id'
				),
				array(
					'field' => 'ACE_Person_id',
					'label' => 'Идентификатор человека',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RJON',
					'label' => 'Район',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'ULIC',
					'label' => 'Улица',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'CITY',
					'label' => 'Пункт',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'DOM',
					'label' => 'Дом',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'KVAR',
					'label' => 'Кв',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'KODP',
					'label' => 'Код',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'TELF',
					'label' => 'Тлф',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'MEST',
					'label' => 'Где',
					'rules' => 'trim',
					'type' => 'int'
				),			
				array(
					'field' => 'PODZ',
					'label' => 'Под',
					'rules' => 'trim',
					'type' => 'string'
				),			
				array(
					'field' => 'ETAJ',
					'label' => 'Эт',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'COMM',
					'label' => '!',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'POVD',
					'label' => 'Повод',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'FAM',
					'label' => 'Фамилия',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'OTCH',
					'label' => 'Отчество',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'IMYA',
					'label' => 'Имя',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'KTOV',
					'label' => 'Вызвал',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'VOZR',
					'label' => 'Возр',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'POL',
					'label' => 'Пол',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'NUMV',
					'label' => 'Номер',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'NGOD',
					'label' => 'Скв. ном.',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'POVT',
					'label' => 'Скв. ном.',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'PRTY',
					'label' => 'Сроч.',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'PROF',
					'label' => 'Прф.',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'SECT',
					'label' => 'Сект.',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'SMPT',
					'label' => 'СМП.',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'STAN',
					'label' => 'П/c',
					'rules' => 'trim',
					'type' => 'int'
				), 
				array(
					'field' => 'DPRM',
					'label' => 'Дата',
					'rules' => 'trim',
					'type' => 'datetime'
				), 
				array(
					'default' => null,
					'field' => 'TPRM',
					'label' => 'Принят',
					'rules' => 'trim',
					'type' => 'datetime'
				), 
				array(
					'default' => null,
					'field' => 'TPER',
					'label' => 'Передан',
					'rules' => 'trim',
					'type' => 'datetime'
				), 
				array(
					'field' => 'WDAY',
					'label' => 'день',
					'rules' => 'trim',
					'type' => 'int'
				), 
				array(
					'field' => 'LINE',
					'label' => 'пульт',
					'rules' => 'trim',
					'type' => 'int'
				),
				/*array(
					'field' => 'SECT',
					'label' => 'исп',
					'rules' => 'trim',
					'type' => 'int'
				)*/
				array(
					'field' => 'REZL',
					'label' => 'Рез-т',
					'rules' => 'trim',
					'type' => 'int'
				), 
				array(
					'field' => 'TRAV',
					'label' => 'Вид.',
					'rules' => 'trim',
					'type' => 'int'
				), 
				array(
					'field' => 'RGSP',
					'label' => 'Район',
					'rules' => 'trim',
					'type' => 'int'
				), 
				array(
					'field' => 'KUDA',
					'label' => 'Куда',
					'rules' => 'trim',
					'type' => 'string'
				), 
				array(
					'field' => 'DS1',
					'label' => 'DS',
					'rules' => 'trim',
					'type' => 'int'
				), 
				array(
					'field' => 'DS2',
					'label' => 'DS',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'ALK',
					'label' => 'Алк',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'cmpCallCard_IsPoli',
					'label' => 'cmpCallCard_IsPoli',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'MKB',
					'label' => 'МКБ',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'NUMB',
					'label' => 'Бригада',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'SMPB',
					'label' => 'ССМП',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'STBR',
					'label' => 'п/с',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'STBB',
					'label' => '/',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'PRFB',
					'label' => 'прф.',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'NCAR',
					'label' => 'Машина',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'RCOD',
					'label' => 'Рация',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'TABN',
					'label' => 'СБ',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'TAB2',
					'label' => 'П1',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'TAB3',
					'label' => 'П2',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'TAB4',
					'label' => 'В',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'SMPP',
					'label' => 'ССМП',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'VR51',
					'label' => 'Ст. врач',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'D201',
					'label' => 'Ст. дисп',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'DSP1',
					'label' => 'Принял',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'DSP2',
					'label' => 'Назначил',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'DSPP',
					'label' => 'Передал',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'DSP3',
					'label' => 'Закрыл',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'KAKP',
					'label' => 'Получен',
					'rules' => 'trim',
					'type' => 'int'
				), 
				array(
					'default' => null,
					'field' => 'TPER',
					'label' => 'Передан',
					'rules' => 'trim',
					'type' => 'datetime'
				), 
				array(
					'default' => null,
					'field' => 'VYEZ',
					'label' => 'Выезд',
					'rules' => 'trim',
					'type' => 'datetime'
				), 
				array(
					'default' => null,
					'field' => 'PRZD',
					'label' => 'Прибыл',
					'rules' => 'trim',
					'type' => 'datetime'
				), 
				array(
					'default' => null,
					'field' => 'TGSP',
					'label' => 'Госпит',
					'rules' => 'trim',
					'type' => 'datetime'
				), 
				array(
					'default' => null,
					'field' => 'TSTA',
					'label' => 'В_стац',
					'rules' => 'trim',
					'type' => 'datetime'
				), 
				array(
					'default' => null,
					'field' => 'TISP',
					'label' => 'Исполн.',
					'rules' => 'trim',
					'type' => 'datetime'
				), 
				array(
					'default' => null,
					'field' => 'TVZV',
					'label' => 'Возвр',
					'rules' => 'trim',
					'type' => 'datetime'
				), 
				array(
					'field' => 'KILO',
					'label' => 'Километр',
					'rules' => 'trim',
					'type' => 'float'
				), 
				array(
					'field' => 'DLIT',
					'label' => 'Длит_03',
					'rules' => 'trim',
					'type' => 'int'
				), 
				array(
					'field' => 'PRDL',
					'label' => 'Предлож.',
					'rules' => 'trim',
					'type' => 'string'
				), 
				array(
					'field' => 'POLI',
					'label' => 'Актив',
					'rules' => 'trim',
					'type' => 'int'
				), 
				array(
					'field' => 'IZV1',
					'label' => 'с1',
					'rules' => 'trim',
					'type' => 'string'
				), 
				array(
					'default' => null,
					'field' => 'TIZ1',
					'label' => 'Время1',
					'rules' => 'trim',
					'type' => 'datetime'
				), 
				array(
					'field' => 'INF1',
					'label' => 'Причина задержки',
					'rules' => 'trim',
					'type' => 'string'
				), 
				array(
					'field' => 'INF2',
					'label' => 'Диагностика',
					'rules' => 'trim',
					'type' => 'string'
				), 
				array(
					'field' => 'INF3',
					'label' => 'Причина повтора',
					'rules' => 'trim',
					'type' => 'string'
				), 
				array(
					'field' => 'INF4',
					'label' => 'Тактика',
					'rules' => 'trim',
					'type' => 'string'
				), 
				array(
					'field' => 'INF5',
					'label' => 'Оформление карты',
					'rules' => 'trim',
					'type' => 'string'
				), 
				array(
					'field' => 'INF6',
					'label' => 'Оценка',
					'rules' => 'trim',
					'type' => 'string'
				), 
				array(
					'field' => 'MKOD',
					'label' => 'код',
					'rules' => 'trim',
					'type' => 'int'
				), 
				array(
					'field' => 'MNAM',
					'label' => 'Наимен-е',
					'rules' => 'trim',
					'type' => 'string'
				), 
				array(
					'field' => 'MEDI',
					'label' => 'Ед. изм',
					'rules' => 'trim',
					'type' => 'string'
				), 
				array(
					'field' => 'MKOL',
					'label' => 'Кол-во',
					'rules' => 'trim',
					'type' => 'string'
				), 
				array(
					'field' => 'DSHS',
					'label' => 'DS',
					'rules' => 'trim',
					'type' => 'int'
				), 
				array(
					'field' => 'FERR',
					'label' => 'Р',
					'rules' => 'trim',
					'type' => 'int'
				), 
				array(
					'field' => 'EXPO',
					'label' => 'Э',
					'rules' => 'trim',
					'type' => 'int'
				),
				array('field' => 'cmpArea_Code', 'label' => 'cmpArea_Code', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpArea_Str', 'label' => 'cmpArea_Str', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpArea_gCode', 'label' => 'cmpArea_gCode', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpArea_gStr', 'label' => 'cmpArea_gStr', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpArea_pCode', 'label' => 'cmpArea_pCode', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpArea_pStr', 'label' => 'cmpArea_pStr', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpPlace_Code', 'label' => 'cmpPlace_Code', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpPlace_Str', 'label' => 'cmpPlace_Str', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpReason_Code', 'label' => 'cmpReason_Code', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpReason_Str', 'label' => 'cmpReason_Str', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpDiag_oCode', 'label' => 'cmpDiag_oCode', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpDiag_oStr', 'label' => 'cmpDiag_oStr', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpDiag_aCode', 'label' => 'cmpDiag_aCode', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpDiag_aStr', 'label' => 'cmpDiag_aStr', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpProfile_cCode', 'label' => 'cmpProfile_cCode', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpProfile_cStr', 'label' => 'cmpProfile_cStr', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpProfile_bCode', 'label' => 'cmpProfile_bCode', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpProfile_bStr', 'label' => 'cmpProfile_bStr', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpResult_Code', 'label' => 'cmpResult_Code', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpResult_Str', 'label' => 'cmpResult_Str', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpTrauma_Code', 'label' => 'cmpTrauma_Code', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpTrauma_Str', 'label' => 'cmpTrauma_Str', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'Diag_uCode', 'label' => 'Diag_uCode', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'Diag_sCode', 'label' => 'Diag_sCode', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'lpu_Code', 'label' => 'lpu_Code', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'person_Sex', 'label' => 'person_Sex', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'person_SurName', 'label' => 'person_SurName', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'person_FirName', 'label' => 'person_FirName', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'person_SecName', 'label' => 'person_SecName', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'person_BirthDay', 'label' => 'person_BirthDay', 'rules' => 'trim', 'type' => 'datetime'),
				array('field' => 'person_PolisSer', 'label' => 'person_PolisSer', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'person_PolisNum', 'label' => 'person_PolisNum', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpCallType_Code', 'label' => 'cmpCallType_Code', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpCallType_Str', 'label' => 'cmpCallType_Str', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpTalon_Code', 'label' => 'cmpTalon_Code', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpTalon_Str', 'label' => 'cmpTalon_Str', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpPlace_id', 'label' => 'cmpPlace_id', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'cmpReason_id', 'label' => 'cmpReason_id', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'cmpDiag_oid', 'label' => 'cmpDiag_oid', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'cmpDiag_aid', 'label' => 'cmpDiag_aid', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'cmpProfile_cid', 'label' => 'cmpProfile_cid', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'cmpProfile_bid', 'label' => 'cmpProfile_bid', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'cmpResult_id', 'label' => 'cmpResult_id', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'cmpTrauma_id', 'label' => 'cmpTrauma_id', 'rules' => 'trim', 'type' => 'id'),
				//array('field' => 'lpu_id', 'label' => 'lpu_id', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'lpu_Code', 'label' => 'lpu_Code', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'lpu_Str', 'label' => 'lpu_Str', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpCallType_id', 'label' => 'cmpCallType_id', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'cmpTalon_id', 'label' => 'cmpTalon_id', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'cmpCallCard_Izv1', 'label' => 'cmpCallCard_Izv1', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpCallCard_Tiz1', 'label' => 'cmpCallCard_Tiz1', 'rules' => 'trim', 'type' => 'datetime'),				
				array('field' => 'cmpCallCard_PCity', 'label' => 'cmpCallCard_PCity', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpCallCard_PUlic', 'label' => 'cmpCallCard_PUlic', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpCallCard_PDom', 'label' => 'cmpCallCard_PDom', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpCallCard_PKvar', 'label' => 'cmpCallCard_PKvar', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'cmpCallCard_Medc', 'label' => 'cmpCallCard_Medc', 'rules' => 'trim', 'type' => 'int')
			),
			'getAmbulanceMedicamentList' => array(
				array (
					'field' => 'AmbulanceCard_id',
					'label' => 'Идентификатор карты',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'deleteAmbulanceDrug' => array(
				array (
					'field' => 'CmpCallDrug_id',
					'label' => 'Идентификатор медикамента',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'saveAmbulanceDrug' => array(
				array (
					'field' => 'CmpDrug_id',
					'label' => 'Медикамент',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array (
					'field' => 'CmpDrug_Kolvo',
					'label' => 'Количество',
					'rules' => 'trim|required',
					'type' => 'int'
				),
				array (
					'field' => 'AmbulanceCard_id',
					'label' => 'Идентификатор карты',
                    'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'importAmbulanceCardFromDbf' => array(
				array(
					'field' => 'AmbulanceCardDbf',
					'label' => 'Файл',
					'rules' => '',
					'type' => 'string'
				)
			)
		);
	}
	
	
	/**
	 * Удаление медикамента из карты вызова
	 */
	function deleteAmbulanceDrug()
	{
		$this->load->helper('Text');
		
		$data = $this->ProcessInputData('deleteAmbulanceDrug', true);
		if ($data === false) return false;
		
		$info = $this->dbmodel->deleteAmbulanceDrug($data);
	}
	
	
	/**
	*  Функция сохранения медикаментов.
	*  Входящие данные: $_POST с идетификатором.
	*  На выходе: JSON-строка
	*  Используется: форма редактирования карты вызова.
	*/
	function saveAmbulanceDrug()
	{
		$data = $this->ProcessInputData('saveAmbulanceDrug', true);
		if ($data === false) return false;
		
		$response = $this->dbmodel->saveAmbulanceDrug($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}
	
	/**
	*  Функция сохранения данных карты вызова.
	*  Входящие данные: $_POST с идетификатором.
	*  На выходе: JSON-строка
	*  Используется: форма редактирования карты вызова.
	*/
	function saveAmbulanceCard() {
	
		$logging = true;
		if ($logging) {
			$today = getdate();
			$data_str = $today['mday'].".".$today['mon'].".".$today['year']." ".$today['hours'].":".$today['minutes']." ";
			foreach($_POST as $key => $value) {
				$data_str .= "[".$key."] = '".$value."'; ";
			}
			$f_log = fopen(PROMED_LOGS . 'ambulance.card.save.3.log', 'a');
			fputs($f_log, $data_str);
			fputs($f_log, "\r\n");
			fclose($f_log);
		}


		//ввод тестовой информации
		$_POST = $_GET;
		$_POST['NUMV'] = '338';
		$_POST['NGOD'] = '146672';
		$_POST['PRTY'] = '4';
		$_POST['SECT'] = '35';
		$_POST['CITY'] = 'ПЕРМЬ';
		$_POST['ULIC'] = 'КОСМОНАВТА ЛЕОНОВА';
		$_POST['DOM'] = '47';
		$_POST['KVAR'] = '122';
		$_POST['PODZ'] = '0';
		$_POST['ETAJ'] = '4';
		$_POST['KODP'] = '122';
		$_POST['TELF'] = '89229560228';
		$_POST['PLS'] = '';
		$_POST['PLC'] = '1';
		$_POST['ARS'] = '';
		$_POST['ARC'] = '';
		$_POST['REAS'] = '09Ж';
		$_POST['REAC'] = '09Ж';
		$_POST['PROFS'] = 'Л ';
		$_POST['PROFC'] = 'Л';
		$_POST['ARGS'] = '';
		$_POST['ARGC'] = '';
		$_POST['ARPS'] = '';
		$_POST['ARPC'] = '';
		$_POST['DIAGS'] = 'I70';
		$_POST['DIAGC'] = 'I70';
		$_POST['DIAGS1'] = '';
		$_POST['DIAGC1'] = '';
		$_POST['PRFBS'] = 'Ф ';
		$_POST['PRFBC'] = 'Ф';
		$_POST['REZLS'] = '11 ';
		$_POST['REZLC'] = '11';
		$_POST['TRAVS'] = 'Х ';
		$_POST['TRAVC'] = 'Х';
		$_POST['DGUC'] = 'I70';
		$_POST['DGSC'] = '';
		$_POST['LPUC'] = '';
		$_POST['LPUS'] = 'ПЕРМЬ_+4 ГКБ';
		$_POST['CLTPS'] = '1 ';
		$_POST['CLTPC'] = '1';
		$_POST['TALS'] = '';
		$_POST['TALC'] = '';
		//$_POST['PBDD'] = 'Thu Jan 01 05:00:00 GMT+05:00 1970';
		$_POST['PBDD'] = '01.01.1970 00:00';
		$_POST['CCIZVS'] = '1231 КОКАРЕВА Л.А.';
		$_POST['CCTIZD'] = '18.03.2015 17:21';
		$_POST['CCPCS'] = 'КИРОВ';
		$_POST['CCPUS'] = 'МОЛОДЕЖНАЯ';
		$_POST['CCPDS'] = '17';
		$_POST['CCPKS'] = '18';
		$_POST['PPSS'] = '';
		$_POST['PPNS'] = '433600*3020521148';
		$_POST['CCMI'] = '0';
		$_POST['VOZR'] = '66';
		$_POST['COMM'] = 'О ДЛИТ ПРЕБ В ПР/О СВП';
		$_POST['FAM'] = 'ЭШТРЕКОВ';
		$_POST['IMYA'] = 'НИКОЛАЙ';
		$_POST['OTCH'] = 'НИКОЛАЕВИЧ';
		$_POST['POL'] = '1';
		$_POST['KTOV'] = 'ЖЕНА';
		$_POST['SMPT'] = '1';
		$_POST['STAN'] = '3';
		$_POST['DPRM'] = '18.03.2015 12:53';
		$_POST['ALK'] = '0';
		$_POST['NUMB'] = '302';
		$_POST['SMPB'] = '1';
		$_POST['STBR'] = '3';
		$_POST['STBB'] = '3';
		$_POST['NCAR'] = '654';
		$_POST['RCOD'] = '0070';
		$_POST['Tabn'] = '7092';
		$_POST['TAB2'] = '7923';
		$_POST['TAB3'] = '';
		$_POST['TAB4'] = '';
		$_POST['EXPO'] = '';
		$_POST['SMPP'] = '1';
		$_POST['VR51'] = '1123';
		$_POST['D201'] = '1126';
		$_POST['DSP1'] = '2063';
		$_POST['DSP2'] = '2202';
		$_POST['DSPP'] = '2037';
		$_POST['DSP3'] = '2037';
		$_POST['KAKP'] = '1';
		$_POST['DOKT'] = '0';
		$_POST['TPER'] = '18.03.2015 13:00';
		$_POST['VYEZ'] = '18.03.2015 13:03';
		$_POST['PRZD'] = '18.03.2015 13:13';
		$_POST['TGSP'] = '18.03.2015 13:35';
		$_POST['TSTA'] = '18.03.2015 14:10';
		$_POST['TISP'] = '18.03.2015 14:32';
		$_POST['TVZV'] = '01.01.1970 00:00';
		$_POST['KILO'] = '15';
		$_POST['DLIT'] = '71';
		$_POST['PRDL'] = '1#';
		$_POST['POLI'] = '0';
		$_POST['novalue1'] = '1';
		$_POST['MEDS'] = '9101:::1;7012:::2';


		//array_walk($_POST, 'ConvertFromWin1251ToUTF8');

		$data = $this->ProcessInputData('saveAmbulanceCard', true);
		if ($data === false) return false;

		$response = $this->dbmodel->saveAmbulanceCard($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}


	/**
	*  Функция получения данных карты вызова.
	*  Входящие данные: $_POST с идетификатором.
	*  На выходе: JSON-строка
	*  Используется: форма редактирования карты вызова.
	*/
	function getAmbulanceCard()
	{
		$data = $this->ProcessInputData('getAmbulanceCard', true);
		if ($data === false) return false;

		$response = $this->dbmodel->getAmbulanceCard($data);
		$this->ProcessModelList($response, true,true)->ReturnData();
	}
	
	/**
	 * Получение списка медикаментов для карты вызова
	 */
	function getAmbulanceMedicamentList()
	{
		$data = $this->ProcessInputData('getAmbulanceMedicamentList', true);
		if ($data === false) return false;
	
		$response = $this->dbmodel->getAmbulanceMedicamentList($data);
		$this->ProcessModelList($response, true,true)->ReturnData();
	}

	/**
	 * Импорт карт СМП из Dbf
	 */
	function importAmbulanceCardFromDbf()
	{
		set_time_limit(100000);
		/*ini_set("memory_limit", "1024M");
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");
		ini_set("post_max_size", "220");
		ini_set("default_socket_timeout", "999");
		ini_set("upload_max_filesize", "220M");*/

		$upload_path = './'.IMPORTPATH_ROOT.$_SESSION['lpu_id'].'/'.time().'/';
		$allowed_types = explode('|','zip|rar|dbf');

		$data = $this->ProcessInputData('importAmbulanceCardFromDbf', true);
		if ($data === false) return false;

		if (!isset($_FILES['AmbulanceCardDbf'])) {
			$this->ReturnError('Не выбран файл для импорта!', 100011);
			return false;
		}

		if (!is_uploaded_file($_FILES['AmbulanceCardDbf']['tmp_name']))
		{
			$error = (!isset($_FILES['AmbulanceCardDbf']['error'])) ? 4 : $_FILES['AmbulanceCardDbf']['error'];
			switch($error)
			{
				case 1:
					$message = 'Загружаемый файл превышает максимально допустимый размер, определённый в вашем файле конфигурации PHP.';
					break;
				case 2:
					$message = 'Загружаемый файл превышает максимально допустимый размер, заданный формой.';
					break;
				case 3:
					$message = 'Этот файл был загружен не полностью.';
					break;
				case 4:
					$message = 'Вы не выбрали файл для загрузки.';
					break;
				case 6:
					$message = 'Временная директория не найдена.';
					break;
				case 7:
					$message = 'Файл не может быть записан на диск.';
					break;
				case 8:
					$message = 'Неверный формат файла.';
					break;
				default :
					$message = 'При загрузке файла произошла ошибка.';
					break;
			}

			$this->ReturnError($message, 100012);
			return false;
		}


		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['AmbulanceCardDbf']['name']);
		$file_data['file_ext'] = end($x);
		if (!in_array(mb_strtolower($file_data['file_ext']), $allowed_types)) {
			$this->ReturnError('Данный тип файла не разрешен.', 100013);
			return false;
		}

		// Правильно ли указана директория для загрузки?
		if ( !@is_dir($upload_path) ) {
			$folders = explode('/', $upload_path);
			$upload_path = '';

			foreach ( $folders as $folder ) {
				if ( empty($folder) ) {
					continue;
				}

				$upload_path .= $folder . '/';

				if ( is_dir($upload_path) ) {
					continue;
				}

				@mkdir($upload_path);
			}
		}

		if (!@is_dir($upload_path)) {
			$this->ReturnError('Путь для загрузки файлов некорректен.', 100014);
			return false;
		}

		// Имеет ли директория для загрузки права на запись?
		if (!is_writable($upload_path)) {
			$this->ReturnError('Загрузка файла не возможна из-за прав пользователя.', 100015);
			return false;
		}

		if (!file_exists($upload_path.$_FILES["AmbulanceCardDbf"]["name"])){

			$zip = new ZipArchive;
			if ($zip->open($_FILES["AmbulanceCardDbf"]["tmp_name"]) === TRUE)
			{
				$zip->extractTo( $upload_path );
				$zip->close();
			} else {
				copy($_FILES["AmbulanceCardDbf"]["tmp_name"], $upload_path.$_FILES["AmbulanceCardDbf"]["name"]);
			}

		}

		$dbffile = $_FILES['AmbulanceCardDbf']['name'];
		$recall = 0;
		$recerr = 0;
		$params = array();

		$hd = dbase_open($upload_path.$dbffile, 0);
		$count_all = 0;
		$count_new = 0;

		if (!$hd) {
			//unlink($upload_path.$dbffile);
			$this->ReturnError('В файле с амбулаторными картами обнаружена ошибка!', 100016);
			return false;
		}

		$r = dbase_numrecords($hd);

		$this->dbmodel->beginTransaction();

		for ($i=1; $i <= $r; $i++)
		{
			$rech = dbase_get_record_with_names($hd, $i);

			$inputParams = array(
				'pmUser_id' => $data['session']['pmuser_id'],
				'session' => $data['session'],
				'Server_id' => $data['session']['server_id'],
			);

			array_walk($rech, 'ConvertFromWin866ToUtf8');

			foreach($rech as $key=>$val) {
				$val = trim($val);

				switch ($key) {
					case 'KOD1':
					case 'DATR':
						if ( !empty($val) ) {
							$val = DateTime::createFromFormat('d/m/Y H:i:s', $val . ' 00:00:00');
						}
						$inputParams[$key] = $val;
					break;
					case 'MEDS':
						$inputParams[$key] = $val;
					break;
					case 'TPRM':
					case 'TPER':
					case 'VYEZ':
					case 'PRZD':
					case 'TGSP':
					case 'TSTA':
					case 'TISP':
					case 'TVZV':
					case 'TEND':
						$inputParams[$key] = empty($val) || preg_match('/\d\d:\d\d/', $val) ? $val : '00:00';
					break;
					case 'PODZ':
					case 'DLIT':
					case 'ETAJ':
					case 'EXPO':
					case 'KAKP':
					case 'KILO':
					case 'LINE':
					case 'NCAR':
					case 'NGOD':
					case 'NUMB':
					case 'PRTY':
					case 'SECT':
					case 'SMPB':
					case 'SMPP':
					case 'SMPT':
					case 'STAN':
					case 'STBB':
					case 'STBR':
						$inputParams[$key] = preg_replace('/[^0-9]/', '', $val);
					break;
					case 'VOZR':
						$inputParams[$key] = preg_replace('/[^0-9]/', '', $val);
						if($inputParams[$key] > 120){
							$inputParams[$key] = NULL;
						}
					break;
					default:
						$inputParams[$key] = $val;
					break;
				}
			}

			// Преобразуем поля, содержащие время, в дата-время
			// https://redmine.swan.perm.ru/issues/49361
			if ( !empty($inputParams['DPRM']) ) {
				$DPRM = DateTime::createFromFormat('ymd H:i', $inputParams['DPRM'] . ' ' . $inputParams['TPRM']);

				if ( is_object($DPRM) ) {
					foreach ( $inputParams as $key => $val ) {
						switch ( $key ) {
							case 'TPER':
							case 'VYEZ':
							case 'PRZD':
							case 'TGSP':
							case 'TSTA':
							case 'TISP':
							case 'TVZV':
							case 'TEND':
								$parsed = DateTime::createFromFormat('ymd H:i', $inputParams['DPRM'] . ' ' . $inputParams[$key]);

								if ( is_object($parsed) ) {
									if ( $parsed < $DPRM ) {
										$parsed->add(new DateInterval('P1D'));
									}

									$inputParams[$key] = $parsed->format('Y-m-d H:i:s');
								}
								else {
									$inputParams[$key] = $inputParams['DPRM'] . ' ' . $inputParams[$key];
								}
							break;
						}
					}
				}
			}

			if ( !empty($inputParams['DPRM']) ) {
				$inputParams['DPRM'] .= ' ' . $inputParams['TPRM'];
			}

			if($_SESSION['region']['nick'] == 'perm'){

				// Костыль пока в АДИС не поменяли номера на нормальные
				if ($inputParams['SMPB'] == 9 and $inputParams['STBR']== 1 ) {
					$inputParams['SMPB'] = 1;
					$inputParams['STBR'] = 11;
				}
				elseif ($inputParams['SMPB'] == 6 and $inputParams['STBR'] == 1) {
					$inputParams['SMPB'] = 1;
					$inputParams['STBR'] = 31;
				}
				elseif ($inputParams['SMPB'] == 6 and $inputParams['STBR'] == 2 ) {
					$inputParams['SMPB'] = 1;
					$inputParams['STBR'] = 32;
				}
				elseif ($inputParams['SMPB'] == 43 and $inputParams['STBR'] == 1) {
					$inputParams['SMPB'] = 14;
					$inputParams['STBR'] = 2;
				}
				elseif ($inputParams['SMPB'] == 29 and $inputParams['STBR'] == 1) {
					$inputParams['SMPB'] = 14;
					$inputParams['STBR'] = 3;
				}
				elseif ($inputParams['SMPB'] == 29 and $inputParams['STBR'] == 2) {
					$inputParams['SMPB'] = 14;
					$inputParams['STBR'] = 3;
				}
				elseif ($inputParams['SMPB'] == 30 and $inputParams['STBR'] == 1) {
					$inputParams['SMPB'] = 12;
					$inputParams['STBR'] = 8;
				}
				elseif ($inputParams['SMPB'] == 25 and $inputParams['STBR'] == 1) {
					$inputParams['SMPB'] = 12;
					$inputParams['STBR'] = 9;
				}
				elseif ($inputParams['SMPB'] == 39 and $inputParams['STBR'] == 1) {
					$inputParams['SMPB'] = 12;
					$inputParams['STBR'] = 10;
				}
				elseif ($inputParams['SMPB'] == 38 and $inputParams['STBR'] == 1) {
					$inputParams['SMPB'] = 12;
					$inputParams['STBR'] = 11;
				}
				elseif ($inputParams['SMPB'] == 21 and $inputParams['STBR'] == 1) {
					$inputParams['SMPB'] = 15;
					$inputParams['STBR'] = 4;
				}
				elseif ($inputParams['SMPB'] == 21 and $inputParams['STBR'] == 2) {
					$inputParams['SMPB'] = 15;
					$inputParams['STBR'] = 4;
				}
			}

			$result = $this->dbmodel->saveAmbulanceCard($inputParams);

			$count_all++;
			if (!$result || !empty($result[0]['Error_Msg'])) {
				$this->dbmodel->rollbackTransaction();
				//print_r(array($rech, $inputParams));exit;
				$this->ReturnError('Запись №'.$count_all.'. '.'Ошибка: '.$result[0]['Error_Msg'], 100017);
				dbase_close ($hd);
				return false;
			} elseif (!array_key_exists('exists', $result[0]) || $result[0]['exists'] === false) {
				$count_new++;
			}
		}

		dbase_close ($hd);

		$this->dbmodel->commitTransaction();

		//unlink($upload_path.$dbffile);
		echo json_encode(array(
			'success' => true,
			'Message' => toUTF('Амбулаторные карты успешно загружены.'),
			'Count_New' => toUTF('Записей добавлено: '.$count_new.', Записей уже существует: '.($count_all-$count_new))
		));

		return true;
	}
}

?>