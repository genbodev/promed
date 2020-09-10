<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnUdost - контроллер для управления услугами
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Polka
* @access		public
* @copyright	Copyright (c) 2010-2011 Swan Ltd.
* @author		Stas Bykov aka Savage (savage@swan.perm.ru)
* @version		27.01.2010
*/

/**
 * @property EvnUsluga_model dbmodel
 * @property Evn_model evn
 */
class EvnUsluga extends swController {
	/**
	 * Поддерживаемые классы услуги:
	 * - EvnUslugaCommon
	 * - EvnUslugaOper
	 * - EvnUslugaStom
	 */
	private $evnUslugaClasses = array(
		'EvnUsluga',
		'EvnUslugaCommon',
		'EvnUslugaOper',
		'EvnUslugaStom',
		'EvnUslugaPregnancySpec'//! только удаление
        ,
        'EvnUslugaOnkoBeam',
        'EvnUslugaOnkoChem',
        'EvnUslugaOnkoGormun',
        'EvnUslugaOnkoNonSpec',
        'EvnUslugaOnkoSurg',
	);

	private $moduleMethods = [
		'getReagentCountByDate',
		'getReagentAutoRateCountOnAnalyser'
	];

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->inputRules = array(
			'loadEvnUslugaPanel' => array(
				array(
					'field' => 'EvnUsluga_pid',
					'label' => 'Идентификатор родительского события',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadParodontogram' => array(
				array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnUslugaStom_id', 'label' => 'Идентификатор стоматологической услуги', 'rules' => '', 'type' => 'id')
			),
			'loadMedSpecOmsList' => array(
				array('field' => 'LpuSectionProfile_id','label' => 'Профиль','rules' => 'trim','type' => 'id'),
				array('field' => 'onDate','label' => 'На дату','rules' => 'trim','type' => 'date'),
			),
			'loadLpuSectionProfileList' => array(
				array('field' => 'MedSpecOms_id','label' => 'Специальность','rules' => 'trim','type' => 'id'),
				array('field' => 'LpuSection_id','label' => 'Отделение','rules' => 'trim','type' => 'id'),
				array('field' => 'onDate','label' => 'На дату','rules' => 'trim','type' => 'date'),
				array('field' => 'isStom','label' => 'Стоматология','rules' => 'trim','type' => 'swcheckbox'),
			),
			'deleteEvnUsluga' => array(
				array(
					'field' => 'ignorePaidCheck',
					'label' => 'Игнорировать признак оплаты случая',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'class',
					'label' => 'Класс услуги',
					'rules' => 'trim|required',
					'type' => 'string'
				),
				array(
					'field' => 'id',
					'label' => 'Идентификатор услуги',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadEvnUslugaParSimpleEditForm' => array(
				array(
					'field' => 'EvnUslugaPar_id',
					'label' => 'Идентификатор услуги',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadEvnUslugaEditForm' => array(
				array(
					'field' => 'class',
					'label' => 'Класс услуги',
					'rules' => 'trim|required',
					'type' => 'string'
				),
				array(
					'field' => 'id',
					'label' => 'Идентификатор услуги',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadEvnUslugaGrid' => array(
				array(
					'default' => 'EvnUsluga',
					'field' => 'class',
					'label' => 'Класс услуги',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'parent',
					'label' => 'Родительский класс',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'pid',
					'label' => 'Идентификатор родительского события',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'rid',
					'label' => 'Идентификатор корневого родительского события',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'mid',
					'label' => 'Идентификатор заболевания',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'isEvnDiagPLStom',
					'label' => 'Признак загрузки для заболевания',
					'rules' => '',
					'type' => 'int'
				),
                array(
                    'field' => 'EvnClass_SysNick',
                    'label' => 'Фильтр по классам услуг',
                    'rules' => '',
                    'type' => 'string'
                ),
				array(
					'field' => 'allowVizitCode',
					'label' => 'Флаг разрешения загрузки услуг (кодов) посещений',
					'rules' => '',
					'type' => 'int'
				),
                array(
                    'field' => 'byMorbus',
                    'label' => 'Признак фильтрации услуг по заболеванию',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Morbus_id',
                    'label' => 'Идентификатор заболевания',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Mes_id',
                    'label' => 'Идентификатор КСГ',
                    'rules' => '',
                    'type' => 'id'
				),
                array(
                    'field' => 'isMorbusOnko',
                    'label' => 'Признак услуги по онкологии',
                    'rules' => '',
                    'type' => 'id'
				)
			),
			'loadUslugaComplexGrid' => array(
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
			'loadUslugaComplexListGrid' => array(
				// Параметры страничного вывода
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество записей',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Номер стартовой записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Идентификатор комплексной услуги',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadUslugaComplexList' => array(
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Usluga_id',
					'label' => 'Идентификатор услуги',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Usluga_date',
					'label' => 'Дата оказания услуги (в формате xx.мм.гггг)',
					'rules' => 'trim',
					'type' => 'date'
				)
			),
			// Возможно, уже нигде не используется после замены на loadUslugaComplexList
			'loadUslugaSectionList' => array(
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Usluga_id',
					'label' => 'Идентификатор услуги',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Usluga_date',
					'label' => 'Дата оказания услуги (в формате xx.мм.гггг)',
					'rules' => 'trim',
					'type' => 'date'
				)
			),
			'saveEvnUslugaCommon' => array(
				array(
					'field' => 'AttributeSignValueData',
					'label' => 'Список значний атрибутов',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'ignorePaidCheck',
					'label' => 'Игнорировать признак оплаты случая',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'checkUslugaDate',
					'label' => 'Проверка что дата услуги больше даты посещения/КВС',
					'rules' => '',
					'type' => 'int'
				),
				array('field' => 'MedSpecOms_id','label' => 'Специальность','rules' => 'trim','type' => 'id'),
				array('field' => 'LpuSectionProfile_id','label' => 'Профиль','rules' => 'trim','type' => 'id'),
				array(
					'field' => 'EvnPrescr_id',
					'label' => 'Идентификатор назначения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPrescrTimetable_id',
					'label' => 'Идентификатор позиции графика назначения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaCommon_id',
					'label' => 'Идентификатор общей услуги',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaCommon_CoeffTariff',
					'label' => 'Коэффициент изменения тарифа',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'EvnUslugaCommon_IsModern',
					'label' => 'Признак услуги по модернизации',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnUslugaCommon_Kolvo',
					'label' => 'Количество',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnUslugaCommon_pid',
					'label' => 'Идентификатор посещения/движения',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaCommon_Price',
					'label' => 'УЕТ',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'EvnUslugaCommon_rid',
					'label' => 'Идентификатор родительского события',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaCommon_setDate',
					'label' => 'Дата начала оказания услуги',
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaCommon_setTime',
					'label' => 'Время начала оказания услуги',
					'rules' => 'trim',
					'type' => 'time'
				),
				array(
					'field' => 'EvnUslugaCommon_disDate',
					'label' => 'Дата окончания оказания услуги',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaCommon_disTime',
					'label' => 'Время окончания оказания услуги',
					'rules' => 'trim',
					'type' => 'time'
				),
				array(
					'field' => 'EvnUslugaCommon_IsMinusUsluga',
					'label' => 'Вычесть стоимость услуги',
					'rules' => '',
					'type' => 'swcheckbox'
				),
				array(
					'field' => 'Lpu_uid',
					'label' => 'ЛПУ',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Org_uid',
					'label' => 'Другая организация',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_uid',
					'label' => 'Отделение',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Врач',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Рабочее место врача',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MesOperType_id',
					'label' => 'Вид лечения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PayType_id',
					'label' => 'Вид оплаты',
					'rules' => 'trim|required',
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
					'field' => 'Server_id',
					'label' => 'Идентификатор сервера',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'Usluga_id',
					'label' => 'Услуга',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Услуга',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaSelectedList',
					'label' => 'Услуги',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'UslugaComplexTariff_id',
					'label' => 'Тариф',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DiagSetClass_id',
					'label' => 'Вид диагноза',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_id',
					'label' => 'Диагноз',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaPlace_id',
					'label' => 'Место оказывания услуги',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnDirection_id',
					'label' => 'Направление',
					'rules' => '',
					'type' => 'id'
				),
				// для сохранения анамнеза
				array(
					'field' => 'AnamnezData',
					'label' => 'Данные специфики',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'XmlTemplate_id',
					'label' => 'Идентификатор шаблона',
					'rules' => 'trim',
					'type' => 'id'
				),
                //onkobeam
                array('field' => 'EvnUslugaOnkoBeam_disDT'  ,'label' => 'Дата/время окончания','rules' => '','type' => 'date'),
                array('field' => 'EvnUslugaOnkoBeam_disDate'  ,'label' => 'Дата окончания','rules' => '','type' => 'date'),
                array('field' => 'EvnUslugaOnkoBeam_disTime'  ,'label' => 'Время окончания','rules' => '','type' => 'time'),
                array('field' => 'OnkoUslugaBeamIrradiationType_id'  ,'label' => 'Способ облучения при проведении лучевой терапии                             ','rules' => '','type' => 'id'),
                array('field' => 'OnkoUslugaBeamKindType_id'         ,'label' => 'Вид лучевой терапии                                                         ','rules' => '','type' => 'id'),
                array('field' => 'OnkoUslugaBeamMethodType_id'       ,'label' => 'Метод лучевой терапии                                                       ','rules' => '','type' => 'id'),
                array('field' => 'OnkoUslugaBeamRadioModifType_id'   ,'label' => 'Радиомодификаторы                                                           ','rules' => '','type' => 'id'),
                array('field' => 'OnkoUslugaBeamFocusType_id'        ,'label' => 'Преимущественная направленность лучевой терапии                             ','rules' => '','type' => 'id'),
                array('field' => 'EvnUslugaOnkoBeam_CountFractionRT' ,'label' => 'Кол-во фракций проведения лучевой терапии                                   ','rules' => '','type' => 'int'),
                array('field' => 'EvnUslugaOnkoBeam_TotalDoseTumor'  ,'label' => 'Суммарная доза облучения опухоли                                            ','rules' => '','type' => 'string'),
                array('field' => 'OnkoUslugaBeamUnitType_id'         ,'label' => 'Единица измерения cуммарной дозы облучения опухоли                          ','rules' => '','type' => 'id'),
                array('field' => 'EvnUslugaOnkoBeam_TotalDoseRegZone','label' => 'Суммарная доза облучения зон регионарного метастазирования                  ','rules' => '','type' => 'string'),
                array('field' => 'OnkoUslugaBeamUnitType_did'        ,'label' => 'Единица измерения cуммарной дозы облучения зон регионарного метастазирования','rules' => '','type' => 'id'),
                //onkochem
                array('field' => 'EvnUslugaOnkoChem_disDT'  ,'label' => 'Дата/время окончания','rules' => '','type' => 'date'),
                array('field' => 'EvnUslugaOnkoChem_disDate'  ,'label' => 'Дата окончания','rules' => '','type' => 'date'),
                array('field' => 'EvnUslugaOnkoChem_disTime'  ,'label' => 'Время окончания','rules' => '','type' => 'time'),
                array('field' => 'OnkoUslugaChemKindType_id'         ,'label' => 'Вид проведенного химиотерапевтического лечения','rules' => '','type' => 'id'),
                array('field' => 'OnkoUslugaChemFocusType_id'        ,'label' => 'Преимущественная направленность химиотерапии','rules' => '','type' => 'id'),
                array('field' => 'OnkoDrug_id'                       ,'label' => 'Кодированная номенклатура препаратов для лекарственного лечения злокачественных новообразований','rules' => '','type' => 'id'),
                array('field' => 'EvnUslugaOnkoChem_Dose'            ,'label' => 'Доза','rules' => '','type' => 'int'),
                //onkogormun
                array('field' => 'EvnUslugaOnkoGormun_disDT'    ,'label' => 'Дата/время окончания'                                                                             ,'rules' => '','type' => 'date'),
                array('field' => 'EvnUslugaOnkoGormun_disDate'  ,'label' => 'Дата окончания'                                                                                   ,'rules' => '','type' => 'date'),
                array('field' => 'EvnUslugaOnkoGormun_disTime'  ,'label' => 'Время окончания'                                                                                  ,'rules' => '','type' => 'time'),
                array('field' => 'EvnUslugaOnkoGormun_IsDrug'   ,'label' => 'Лекарственная'                                                                                    ,'rules' => '','type' => 'id'),
                array('field' => 'OnkoGormunType_id'    ,'label' => 'Вид проведенной гормоноиммунотерапии'                                                                                   ,'rules' => '','type' => 'id'),
                //array('field' => 'EvnUslugaOnkoGormun_IsDrug'    ,'label' => 'Лекарственная'                                                                                   ,'rules' => '','type' => 'id'),
                //array('field' => 'EvnUslugaOnkoGormun_IsSurgical','label' => 'Хирургическая'                                                                                   ,'rules' => '','type' => 'id'),
                //array('field' => 'EvnUslugaOnkoGormun_IsBeam'    ,'label' => 'Лучевая'                                                                                         ,'rules' => '','type' => 'id'),
                array('field' => 'OnkoUslugaGormunFocusType_id'  ,'label' => 'Преимущественная направленность гормоноиммунотерапии'                                            ,'rules' => '','type' => 'id'),
                array('field' => 'OnkoDrug_id'                   ,'label' => 'Кодированная номенклатура препаратов для лекарственного лечения злокачественных новообразований' ,'rules' => '','type' => 'id'),
                array('field' => 'EvnUslugaOnkoGormun_Dose'      ,'label' => 'Доза'                                                                                            ,'rules' => '','type' => 'string'),
                array('field' => 'ignoreParentEvnDateCheck', 'label' => 'Признак игнорирования проверки периода выполенения услуги', 'rules' => '', 'type' => 'int'),
                array('field' => 'EvnUslugaOnkoGormun_CountFractionRT' ,'label' => 'Кол-во фракций проведения лучевой терапии                                   ','rules' => '','type' => 'int'),
				array(
					'field' => 'UslugaExecutionType_id',
					'label' => 'Объём выполнения услуги',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaExecutionReason_id',
					'label' => 'Причина частичного выполнения (невыполнения)',
					'rules' => 'trim',
					'type' => 'id'
				),
				[
					'field' => 'UslugaMedType_id',
					'label' => 'Вид услуги',
					'rules' => '',
					'type' => 'id'
				],
			),
			'saveEvnUslugaComplexOrder' => array(
				array(
					'field' => 'object',
					'label' => 'Объект',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'EvnUsluga_id',
					'label' => 'Идентификатор комплексной услуги',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUsluga_pid',
					'label' => 'Идентификатор родителя',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PrehospDirect_id',
					'label' => 'Идентификатор типа направления',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_did',
					'label' => 'Идентификатор ЛПУ заказавшего услугу',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_did',
					'label' => 'Идентификатор отделения ЛПУ заказавшего услугу',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_did',
					'label' => 'Идентификатор врача заказавшего услугу',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Org_did',
					'label' => 'Идентификатор организации заказавшей услугу',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaComplex_setDate',
					'label' => 'Дата оказания комплексной услуги',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaComplex_setTime',
					'label' => 'Время оказания комплексной услуги',
					'rules' => '',
					'type' => 'time'
				),
				array(
					'field' => 'Lpu_uid',
					'label' => 'ЛПУ, которому назначается оказание услуги',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Org_uid',
					'label' => 'Другая организация',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_uid',
					'label' => 'Отделение, которому назначается оказание услуги',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Врач',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Usluga_isCito',
					'label' => 'Cito',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'time_table',
					'label' => 'Тип расписания (параклиника, поликлиника, стационар)',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'TimetablePar_id',
					'label' => 'Идентификатор записи расписания параклиники',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableMedService_id',
					'label' => 'Идентификатор записи расписания службы/услуги',
					'rules' => 'trim',
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
					'field' => 'Server_id',
					'label' => 'Идентификатор сервера',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Услуга',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'checked',
					'label' => 'пометки',
					'rules' => '',
					'type' => 'string'
				)
			),
			'saveEvnUslugaOper' => array(
				array(
					'field' => 'ignorePaidCheck',
					'label' => 'Игнорировать признак оплаты случая',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnUslugaOper_id',
					'label' => 'Идентификатор выполнения операции',
					'rules' => '',
					'type' => 'id'
				),
				array('field' => 'MedSpecOms_id','label' => 'Специальность','rules' => 'trim','type' => 'id'),
				array('field' => 'LpuSectionProfile_id','label' => 'Профиль','rules' => 'trim','type' => 'id'),
				array(
					'field' => 'EvnUslugaOper_IsEndoskop',
					'label' => 'Признак использования эндоскопической аппаратуры',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOper_IsKriogen',
					'label' => 'Признак использования криогенной аппаратуры',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOper_IsLazer',
					'label' => 'Признак использования лазерной аппаратуры',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOper_IsRadGraf',
					'label' => 'Признак использования рентгенологической аппаратуры',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOper_IsModern',
					'label' => 'Признак услуги по модернизации',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOper_CoeffTariff',
					'label' => 'Коэффициент изменения тарифа',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'EvnUslugaOper_IsVMT',
					'label' => 'Применение ВМТ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnUslugaOper_IsMicrSurg',
					'label' => 'Микрохирургическая',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnUslugaOper_IsOpenHeart',
					'label' => 'На открытом сердце',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnUslugaOper_IsArtCirc',
					'label' => 'С искусственным кровообращением',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnUslugaOper_Kolvo',
					'label' => 'Количество',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'EvnUslugaOper_pid',
					'label' => 'Идентификатор отделения',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'EvnDirection_id',
					'label' => 'Идентификатор отделения',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOper_rid',
					'label' => 'Идентификатор родительского события',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOper_setDate',
					'label' => 'Дата начала оказания услуги',
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaOper_setTime',
					'label' => 'Время начала оказания услуги',
					'rules' => 'trim',
					'type' => 'time'
				),
				array(
					'field' => 'EvnUslugaOper_disDate',
					'label' => 'Дата окончания оказания услуги',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaOper_disTime',
					'label' => 'Время окончания оказания услуги',
					'rules' => 'trim',
					'type' => 'time'
				),
				array(
					'field' => 'Lpu_uid',
					'label' => 'ЛПУ',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Org_uid',
					'label' => 'Другая организация',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_uid',
					'label' => 'Отделение',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Врач',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Рабочее место врача',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MesOperType_id',
					'label' => 'Вид лечения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Morbus_id',
					'label' => 'Заболевание',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPrescr_id',
					'label' => 'Идентификатор назначения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OperDiff_id',
					'label' => 'Категория сложности',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TreatmentConditionsType_id',
					'label' => 'Условие проведения лечения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OperType_id',
					'label' => 'Тип операции',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PayType_id',
					'label' => 'Вид оплаты',
					'rules' => 'trim|required',
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
					'field' => 'Server_id',
					'label' => 'Идентификатор сервера',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'Usluga_id',
					'label' => 'Услуга',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Услуга',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplexTariff_id',
					'label' => 'Тариф',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DiagSetClass_id',
					'label' => 'Вид диагноза',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_id',
					'label' => 'Диагноз',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaPlace_id',
					'label' => 'Место оказывания услуги',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ignoreParentEvnDateCheck',
					'label' => 'Признак игнорирования проверки периода выполенения услуги',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnUslugaOper_BallonBegDate',
					'label' => 'Дата и время начала раздувания баллона',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaOper_BallonBegTime',
					'label' => 'Дата и время начала раздувания баллона',
					'rules' => 'trim',
					'type' => 'time'
				),
				array(
					'field' => 'EvnUslugaOper_CKVEndDate',
					'label' => 'Дата и время окончания ЧКВ',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaOper_CKVEndTime',
					'label' => 'Дата и время окончания ЧКВ',
					'rules' => 'trim',
					'type' => 'time'
				),
				array(
					'field' => 'EvnUslugaOper_IsOperationDeath',
					'label' => 'Смерть наступила на операционном столе',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ignoreBallonBegCheck',
					'label' => 'Признак игнорирования проверки заполения Даты и времени начала раздувания баллона',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'ignoreCKVEndCheck',
					'label' => 'Признак игнорирования проверки заполения Даты и времени окончания ЧКВ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'IsCardioCheck',
					'label' => 'Признак необходимости проверки полей кардио-блока',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaExecutionType_id',
					'label' => 'Объём выполнения услуги',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaExecutionReason_id',
					'label' => 'Причина частичного выполнения (невыполнения)',
					'rules' => 'trim',
					'type' => 'id'
				), [
					'field' => 'UslugaMedType_id',
					'label' => 'Вид услуги',
					'rules' => '',
					'type' => 'id'
                ],
			),
			'saveEvnUslugaParSimple' => array(
				array(
					'field' => 'EvnUslugaPar_id',
					'label' => 'Идентификатор выполнения параклинической услуги',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaPar_pid',
					'label' => 'Идентификатор родительского события',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaPar_setDate',
					'label' => 'Дата оказания услуги',
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaPar_setTime',
					'label' => 'Время оказания услуги',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'ignoreParentEvnDateCheck',
					'label' => 'Признак игнорирования проверки периода выполенения услуги',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'ignoreKSGChangeCheck',
					'label' => 'Признак игнорирования проверки изменения КСГ',
					'rules' => '',
					'type' => 'int'
				)
			),
			'saveEvnUslugaStom' => array(
				array(
					'field' => 'BlackCariesClass_id',
					'label' => 'Класс по Блэку',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaStom_id',
					'label' => 'Идентификатор стоматологической услуги',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaStom_Kolvo',
					'label' => 'Количество',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'EvnUslugaStom_UED',
					'label' => 'УЕТ врача',
					'rules' => 'trim',
					'type' => 'float'
				),
				array(
					'field' => 'EvnUslugaStom_UEM',
					'label' => 'УЕТ среднего м/п',
					'rules' => 'trim',
					'type' => 'float'
				),
				array(
					'field' => 'EvnUslugaStom_pid',
					'label' => 'Идентификатор родительского события',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnDiagPLStom_id',
					'label' => 'Идентификатор стоматологического заболевания',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaStom_IsAllMorbus',
					'label' => 'Признак "Для всех заболеваний"',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaStom_IsMes',
					'label' => 'Признак "Услуга по МЭС/КСГ"',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaStom_Price',
					'label' => 'Цена (УЕТ)',
					'rules' => 'trim',
					'type' => 'float'
				),
				array(
					'field' => 'EvnUslugaStom_setDate',
					'label' => 'Дата начала оказания услуги',
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaStom_setTime',
					'label' => 'Время начала оказания услуги',
					'rules' => 'trim',
					'type' => 'time'
				),
				array(
					'field' => 'EvnUslugaStom_disDate',
					'label' => 'Дата окончания оказания услуги',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaStom_disTime',
					'label' => 'Время окончания оказания услуги',
					'rules' => 'trim',
					'type' => 'time'
				),
				array(
					'field' => 'LpuSection_uid',
					'label' => 'Отделение',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionProfile_id',
					'label' => 'Профиль отделения',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Врач',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Рабочее место врача',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_sid',
					'label' => 'Средний мел. персонал',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'parodontogramData',
					'label' => 'Информация по пародонтограмме',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'PayType_id',
					'label' => 'Вид оплаты',
					'rules' => 'trim|required',
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
					'field' => 'Server_id',
					'label' => 'Идентификатор сервера',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Услуга',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuDispContract_id',
					'label' => 'По договору',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaSelectedList',
					'label' => 'Услуги',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'UslugaComplexTariff_id',
					'label' => 'Тариф',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaPlace_id',
					'label' => 'Место оказывания услуги',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ignoreParentEvnDateCheck',
					'label' => 'Признак игнорирования проверки периода выполенения услуги',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'ignoreKSGCheck',
					'label' => 'Признак игнорирования проверки КСГ',
					'rules' => '',
					'type' => 'int'
				),
				[
					'field' => 'UslugaMedType_id',
					'label' => 'Вид услуги',
					'rules' => '',
					'type' => 'int'
				],
			),
			'saveUslugaComplexList' => array(
				array(
					'field' => 'UslugaComplexList_id',
					'label' => 'Идентификатор записи из набора услуг',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Идентификатор комплексной услуги',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Usluga_id',
					'label' => 'Идентификатор услуги',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaClass_id',
					'label' => 'Идентификатор класса услуги',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getReagentCountByDate' => array(
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Date',
					'label' => 'Дата',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'PayType_id',
					'label' => 'Вид оплаты',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getReagentAutoRateCountOnAnalyser' => array(
				array(
					'default' => 0,
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'begDate', 
					'label' => '',
					'rules' => '', 
					'type' => 'date'
				),
				array(
					'field' => 'endDate', 
					'label' => '',
					'rules' => '', 
					'type' => 'date'
				)
			),
			'checkChangeEvnUslugaIsNeed' => array(
				array(
					'field' => 'mid',
					'label' => 'Идентификатор заболевания',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'oldMes_id',
					'label' => 'Старый КСГ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'newMes_id',
					'label' => 'Новый КСГ',
					'rules' => '',
					'type' => 'id'
				)
			),
			'copyEvnUsluga' => array(
				array(
					'field' => 'ids',
					'label' => 'Идентификаторы услуг',
					'rules' => 'required',
					'type' => 'json_array'
				),
				array(
					'field' => 'Morbus_id',
					'label' => 'Идентификатор заболевания',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'getUslugaExecutionTypeList' => array(),
		);

		$this->init();
	}

	/**
	 * Дополнительная инициализация
	 */
	public function init(){
		$method = $this->router->fetch_method();

		if ($this->usePostgreLis && in_array($method, $this->moduleMethods)) {
			$this->load->swapi('lis');
		} else {
			$this->load->database();
			$this->load->model("EvnUsluga_model", "dbmodel");
		}
	}

	/**
	 * Проверка изменились ли обязательные услуги по КСГ
	 */
	function checkChangeEvnUslugaIsNeed() {
		$data = $this->ProcessInputData('checkChangeEvnUslugaIsNeed', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkChangeEvnUslugaIsNeed($data);
		$this->ProcessModelSave($response, true, 'При проверке изменения КСГ возникли ошибки')->ReturnData();

		return true;
	}

	/**
	*  Удаление услуги
	*  Входящие данные: $_POST['EvnUsluga_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования талона амбулаторного пациента
	*                форма редактирования посещения пациентом поликлиники
	*/
	function deleteEvnUsluga() {
		$data = $this->ProcessInputData('deleteEvnUsluga', true);
		if ($data === false) { return false; }
		if ( !in_array($data['class'], $this->evnUslugaClasses) )  {
			$this->ReturnError('Неверный класс услуги');
			return false;
		}
		$response = $this->dbmodel->deleteEvnUsluga($data);
		$this->ProcessModelSave($response, true, 'При удалении услуги возникли ошибки')
			->ReturnData();
		return true;
	}


	/**
	*  Получение данных для формы редактирования услуги в зависимости от класса услуги
	*  Входящие данные: $_POST['class'], $_POST['id']
	*  На выходе: JSON-строка
	*  Используется: формы редактирования услуги
	*/
	function loadEvnUslugaEditForm() {
		$data = $this->ProcessInputData('loadEvnUslugaEditForm', true);
		if ($data === false) { return false; }
		if ( !in_array($data['class'], $this->evnUslugaClasses) )  {
			echo json_return_errors('Неверный класс услуги');
			return false;
		}

		$response = $this->dbmodel->loadEvnUslugaEditForm($data);

		$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
		return true;
	}
	
	/**
	*  Получение данных для простой формы редактирования параклинической услуги
	*  На выходе: JSON-строка
	*  Используется: формы редактирования услуги
	*/
	function loadEvnUslugaParSimpleEditForm() {
		$data = $this->ProcessInputData('loadEvnUslugaParSimpleEditForm', true);
		if ($data === false) { return false; }

		$response = array();

		if ($this->usePostgreLis) {
			$this->load->swapi('lis');
			$response = $this->lis->GET('EvnUsluga/ParSimpleEditForm', $data, 'list');
		}
		if (empty($response)) {
			$response = $this->dbmodel->loadEvnUslugaParSimpleEditForm($data);
		}

		$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
		return true;
	}


	/**
	*  Получение списка выполненных услуг
	*  Входящие данные: $_POST['class'], $_POST['pid']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования талона амбулаторного пациента
	*                форма редактирования посещения пациентом поликлиники
	*/
	function loadEvnUslugaGrid() {
		$data = $this->ProcessInputData('loadEvnUslugaGrid', true);
		if ($data === false) { return false; }

		if ( !in_array($data['class'], $this->evnUslugaClasses) )  {
			echo json_return_errors('Неверный класс услуги');
			return false;
		}

		$response = $this->dbmodel->loadEvnUslugaGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}


	/**
	*  Получение списка комплексных услуг по ЛПУ
	*  Входящие данные: нет
	*  На выходе: JSON-строка
	*  Используется: форма редактирования комплексной услуги
	*/
	function loadUslugaComplexCombo() {
		$data = getSessionParams();

		$response = $this->dbmodel->loadUslugaComplexCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}


	/**
	*  Получение справочника комплексных услуг
	*  Входящие данные: нет
	*  На выходе: JSON-строка
	*  Используется: форма просмотра справочника комплексных услуг
	*/
	function loadUslugaComplexGrid() {
		$val  = array();

		$data = $this->ProcessInputData('loadUslugaComplexGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadUslugaComplexGrid($data);

		if ( is_array($response) ) {
			if ( (isset($response['Error_Msg'])) && (strlen($response['Error_Msg']) > 0) ) {
				$val = $response;
				array_walk($val, 'ConvertFromWin1251ToUTF8');
			}
			else if ( isset($response['data']) ) {
				$val['data'] = array();

				foreach ( $response['data'] as $row ) {
					array_walk($row, 'ConvertFromWin1251ToUTF8');
					$val['data'][] = $row;
				}

				$val['totalCount'] = $response['totalCount'];
			}
		}

		$this->ReturnData($val);

		return false;
	}


	/**
	*  Получение списка услуг для комплексной услуги
	*  Входящие данные: $_POST['UslugaComplex_id']
	*  На выходе: JSON-строка
	*  Используется: форма просмотра справочника комплексных услуг
	*/
	function loadUslugaComplexListGrid() {
		$val  = array();

		$data = $this->ProcessInputData('loadUslugaComplexListGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadUslugaComplexListGrid($data);

		if ( is_array($response) ) {
			if ( (isset($response['Error_Msg'])) && (strlen($response['Error_Msg']) > 0) ) {
				$val = $response;
				array_walk($val, 'ConvertFromWin1251ToUTF8');
			}
			else if ( isset($response['data']) ) {
				$val['data'] = array();

				foreach ( $response['data'] as $row ) {
					array_walk($row, 'ConvertFromWin1251ToUTF8');
					$val['data'][] = $row;
				}

				$val['totalCount'] = $response['totalCount'];
			}
		}

		$this->ReturnData($val);

		return false;
	}


	/**
	*  Получение списка услуг по отделению для формы быстрого ввода стомат. услуг
	*  Входящие данные: $_POST['LpuSection_id']
	 *                  $_POST['Usluga_date'] дата оказания услуги в формате дд.мм.гггг
	*  На выходе: JSON-строка
	*  Используется: форма быстрого ввода услуг
	*/
	function loadUslugaComplexList() {
		$data = $this->ProcessInputData('loadUslugaComplexList', true, true);

		if ( is_array($data) && count($data) > 0 ) {
			if ( empty($data['LpuSection_id']) && empty($data['Usluga_id']) ) {
				echo json_return_errors('Не заданы обязательные параметры');
				return false;
			}

			$response = $this->dbmodel->loadUslugaComplexList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();

			return true;
		}
		else {
			return false;
		}
	}


	/**
	*  Получение списка услуг по отделению для формы быстрого ввода стомат. услуг
	*  Входящие данные: $_POST['LpuSection_id']
	 *                  $_POST['Usluga_date'] дата оказания услуги в формате дд.мм.гггг
	*  На выходе: JSON-строка
	*  Используется: форма быстрого ввода услуг
	*/
	function loadUslugaSectionList() {
		$val  = array();

		$data = $this->ProcessInputData('loadUslugaSectionList', true);
		if ($data === false) { return false; }

		if ( !isset($data['LpuSection_id']) && !isset($data['Usluga_id']) ) {
			echo json_return_errors('Не заданы обязательные параметры');
			return false;
		}

		$response = $this->dbmodel->loadUslugaSectionList($data);

		if ( is_array($response) && count($response) > 0 ) {
			foreach ( $response as $row ) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$row['EvnUslugaStom_Price'] = floatval($row['EvnUslugaStom_Price']);
				$val[] = $row;
			}
		}

		$this->ReturnData($val);

		return false;
	}


	/**
	*  Сохранение общей услуги
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма редактирования общей услуги
     * @return bool
     */
	function saveEvnUslugaCommon() {
		$data = $this->ProcessInputData('saveEvnUslugaCommon', true);
		if ($data === false) { return false; }

		$checkDate = $this->dbmodel->CheckEvnUslugaDate($data);

		if ( !empty($checkDate[0]['Error_Msg']) ) {
			$val = array('success' => false, 'Error_Msg' => $checkDate[0]['Error_Msg'], 'Error_Code' => $checkDate[0]['Error_Code']);
			$val['Alert_Msg'] = $this->dbmodel->getAlertMsg();
			$this->ReturnData($val);
			return false;
		}

		$response = $this->dbmodel->saveEvnUslugaCommon($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}


	/**
	*  Сохранение комплексной услуги при заказе (новый вариант, использующий сохранение в шаблоны )
	*  Алгоритм: 
	*    1. сохраняем комплексную услугу
	*    2. получаем id сохраненной услуги
	*    3. сохраняем сгенерированный шаблон по выбранным услугам
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма заказа комплексной услуги
	*/
	function saveEvnUslugaComplexOrder() {
		/*
		$this->load->database();
		$this->load->model("EvnUsluga_model", 'dbmodel');
		*/
		$this->load->helper("Xml");
		$this->load->library('parser');
		
		/*
		$_POST['Person_id'] = 8680000005; // ИВАНОВ	ИВАН	ИВАНОВИЧ
		$_POST['PersonEvn_id'] = 106974902;
		$_POST['Server_id'] = '0';
		*/
		$data = $this->ProcessInputData('saveEvnUslugaComplexOrder', true);
		
		if ($data) {
			
			$data['EvnUsluga_Result'] = $data['checked'];
			$data['checked'] = json_decode($data['checked']);
			
			// Определяем что будем делать - создавать новую запись или сохранять старую 
			$data['action'] = (!isset($data['EvnUsluga_id']) || $data['EvnUsluga_id'] <= 0)?'add':'edit';
			
			$response = $this->dbmodel->saveEvnUslugaComplexOrder($data);
			// получаем id 
			$this->ProcessModelSave($response, true);
			if (isset($this->OutData['EvnUsluga_id']) && ($this->OutData['EvnUsluga_id']>0))
			{
				//Если задана бирка, то осуществляем запись на нее
				if (isset($data['TimetableMedService_id'])) {
					$this->load->model('TimetableMedService_model', 'ttmsmodel');
					$applyresponse = $this->ttmsmodel->Apply(
						array(
							'Person_id' => $data['Person_id'],
							'pmUser_id' => $data['pmUser_id'],
							'TimetableMedService_id' => $data['TimetableMedService_id'],
							'Evn_id' => $data['EvnUsluga_pid']
						)
					);
					if ($applyresponse !== false) {	$this->OutData['TimetableApplied'] = true; } // запись на бирку выполнена
				}
				$this->ReturnData();
				return true;
				
			} else {
				$val = array('success' => false, 'Error_Msg' => 'Произошла ошибка при сохранении шаблона заказа услуг!');
				array_walk($val, 'ConvertFromWin1251ToUTF8');
				$this->ReturnData($val);
				return false;
			}
			
		} else {
			return false;
		}
	}

	/**
	*  Сохранение выполнения параклинической услуги
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма редактирования выполнения параклинической услуги
	*/
	function saveEvnUslugaParSimple() {
		$val  = array();

		$data = $this->ProcessInputData('saveEvnUslugaParSimple', true);
		if ($data === false) { return false; }

		$checkDate = $this->dbmodel->CheckEvnUslugaDate($data);
		if ( !empty($checkDate[0]['Error_Msg']) ) {
			$val = array('success' => false, 'Error_Msg' => $checkDate[0]['Error_Msg'], 'Error_Code' => $checkDate[0]['Error_Code']);
			$val['Alert_Msg'] = $this->dbmodel->getAlertMsg();
			$this->ReturnData($val);
			return false;
		}

		$data['EvnUslugaPar_IsManual'] = 2;

		$response = array();

		if ($this->usePostgreLis) {
			$this->load->swapi('lis');
			$response = $this->lis->PUT('EvnUsluga/Par', $data, 'list');
		}
		if (empty($response) || (isset($response[0]) && $response[0]['Error_Code'] == 3)) {
			$this->load->model('EvnUslugaPar_model');
			$response = $this->EvnUslugaPar_model->editEvnUslugaPar($data);
		}

		$this->ProcessModelSave($response, true, 'Ошибка при сохранении услуги по выполнению параклинической услуги')->ReturnData();
		return true;
	}
	
	/**
	*  Сохранение выполнения операции
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма редактирования выполнения операции
	*/
	function saveEvnUslugaOper() {
		$data = $this->ProcessInputData('saveEvnUslugaOper', true);
		if ($data === false) { return false; }
		try {
			if ( empty($data['Usluga_id']) && empty($data['UslugaComplex_id']) ) {
				throw new Exception('Поле "Услуга" обязательно для заполнения');
			}

			if (empty($data['PayType_id'])) {
				throw new Exception('Поле Вид оплаты обязательно для заполнения.');
			}
			if (empty($data['UslugaPlace_id'])) {
				throw new Exception('Поле Место оказания обязательно для заполнения.');
			}

			if ($this->dbmodel->regionNick == 'perm'
				&& 1 == $data['PayType_id']
				&& 1 != $data['UslugaPlace_id']
			) {
				if (empty($data['MedSpecOms_id'])) {
					throw new Exception('Поле Специальность обязательно для заполнения.');
				}
				if (empty($data['LpuSectionProfile_id'])) {
					throw new Exception('Поле Профиль обязательно для заполнения.');
				}
			}

			$checkDate = $this->dbmodel->CheckEvnUslugaDate($data);
			if ( !$this->dbmodel->isSuccessful($checkDate) ) {
				throw new Exception($checkDate[0]['Error_Msg'], (int)$checkDate[0]['Error_Code']);
			}

			//Проверка что место работы врачей операционной бригады не закрыто на дату начала операции
			$checkUslugaOperBrig = $this->dbmodel->CheckUslugaOperBrig($data);
			if ( !$this->dbmodel->isSuccessful($checkUslugaOperBrig) ) {
				throw new Exception($checkUslugaOperBrig[0]['Error_Msg'], (int)$checkUslugaOperBrig[0]['Error_Code']);
			}

			// Проверка на дубли
			$response = $this->dbmodel->checkEvnUslugaDoubles($data, 'oper');
			if ( $response == -1 ) {
				throw new Exception('Ошибка при выполнении проверки услуг на дубли');
			} else if ( $response > 0 ) {
				throw new Exception('Сохранение отменено, т.к. данная услуга уже сохранена в БД для выбранного пациента. Если было выполнено несколько услуг, то измените количество в ранее заведенной услуге');
			}

			if ( !empty($data['EvnUslugaOper_pid']) ) {
				$this->load->model('Evn_model', 'evn');
				$data['ParentEvnClass_SysNick'] = $this->evn->getEvnClassSysNick($data['EvnUslugaOper_pid']);
				if (empty($data['ParentEvnClass_SysNick'])) {
					throw new Exception('Сохранение отменено, т.к. не удалось определить вид учетного документа');
				}
			}
			$response = $this->dbmodel->saveEvnUslugaOper($data);
		} catch (Exception $e) {
			$val = array('success' => false, 'Error_Msg' => $e->getMessage(), 'Error_Code' => $e->getCode());
			$val['Alert_Msg'] = $this->dbmodel->getAlertMsg();
			array_walk($val, 'ConvertFromWin1251ToUTF8');
			$this->ReturnData($val);
			return false;
		}
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении услуги по выполнению операции')
			->ReturnData();
		return true;
	}


	/**
	 *  Сохранение стоматологической услуги
	 *  Входящие данные: ...
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования стоматологической услуги
	 */
	function saveEvnUslugaStom() {
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('saveEvnUslugaStom', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->saveEvnUslugaStom($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении стоматологической услуги')
			->ReturnData();
		return true;
	}


	/**
	*  Сохранение записи для комплексной услуги
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма редактирования услуги (справочник комплексных услуг)
	*/
	function saveUslugaComplexList() {
		$data = $this->ProcessInputData('saveUslugaComplexList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveUslugaComplexList($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении услуги')->ReturnData();

		return true;
	}

	/**
	 *	Получение списка специальностей
	 */
	function loadMedSpecOmsList()
	{
		$data = $this->ProcessInputData('loadMedSpecOmsList', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadMedSpecOmsList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *	Получение списка профилей
	 */
	function loadLpuSectionProfileList()
	{
		$data = $this->ProcessInputData('loadLpuSectionProfileList', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadLpuSectionProfileList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	*  Получение данных пародонтограммы
	*  Входящие данные: EvnUslugaStom_id
	*  На выходе: JSON-строка
	*  Используется: форма редактирования стоматологической услуги
	*/
	function loadParodontogram() {
		$data = $this->ProcessInputData('loadParodontogram', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadParodontogram($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}


	/**
	 *  Получение списка реактивов и их количества, для конкретной службы на конкретную дату
	 */
	function getReagentCountByDate() {
		$data = $this->ProcessInputData('getReagentCountByDate', true);
		if ($data === false) { return false; }

		if ($this->usePostgreLis) {
			$response = $this->lis->GET('EvnUsluga/ReagentCountByDate', $data);
			$this->ProcessRestResponse($response, 'list')->ReturnData();
		} else {
			$response = $this->dbmodel->getReagentCountByDate($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}

		return true;
	}

	/**
	 * Автоучет реактивов - Получение списка реактивов и их количества на конкретную дату
	 * с группировкой по анализаторам
	 */
	function getReagentAutoRateCountOnAnalyser () {
		$data = $this->ProcessInputData('getReagentAutoRateCountOnAnalyser', true);
		if ($data === false) { return false; }

		if ($this->usePostgreLis) {
			$response = $this->lis->GET('EvnUsluga/ReagentAutoRateCountOnAnalyser', $data);
			$this->ProcessRestResponse($response, 'list')->ReturnData();
		} else {
			$response = $this->dbmodel->getReagentAutoRateCountOnAnalyser($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields('d.m.Y H:i')->ReturnData();
		}

		return true;
	}

	/**
	 *  Получение списка услуг для панели направлений в ЭМК
	 */
	function loadEvnUslugaPanel() {
		$data = $this->ProcessInputData('loadEvnUslugaPanel', true, true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnUslugaPanel($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 *  копирование услуг
	 */
	function copyEvnUsluga() {
		$data = $this->ProcessInputData('copyEvnUsluga', true, true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->copyEvnUsluga($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении услуги')->ReturnData();

		return true;
	}

	/**
	 * Справочник "Объем выполнения услуг"
	 */
	function getUslugaExecutionTypeList(){
		$data = $this->ProcessInputData('getUslugaExecutionTypeList');
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getUslugaExecutionTypeList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

}
