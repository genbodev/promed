<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * @property EvnUslugaOnkoBeam_model EvnUslugaOnkoBeam
 * @property EvnUslugaOnkoChem_model EvnUslugaOnkoChem
 * @property EvnUslugaOnkoGormun_model EvnUslugaOnkoGormun
 * @property EvnUslugaOnkoSurg_model EvnUslugaOnkoSurg
 * @property EvnUslugaOnkoNonSpec_model EvnUslugaOnkoNonSpec
 * @property EvnPrescr_model $EvnPrescr_model
 * @property Usluga_model $Usluga_model
 * @property PersonToothCard_model $PersonToothCard_model
 * @property EvnSection_model $EvnSection_model
 */
class EvnUsluga_model extends swModel {
	private $_personNotice;

	public $inputRules = array(
		'loadEvnUslugaList' => array(
			array('field' => 'Evn_pid', 'label' => 'Идентификатор случая-родителя (движение в КВС, посещения в ТАП)', 'rules' => 'required', 'type' => 'id')
		),
		'loadEvnUsluga' => array(
			array(
				'field' => 'EvnUsluga_id', 
				'label' => 'Идентификатор оказания услуги', 
				'rules' => 'trim', 
				'type' => 'id'
			),
			array(
				'field' => 'Evn_pid', 
				'label' => 'Идентификатор случая-родителя (движение в КВС, посещения в ТАП)', 
				'rules' => 'trim', 
				'type' => 'id'
			),
			array(
				'field' => 'Evn_setDT', 
				'label' => 'Дата и время начала выполнения услуги', 
				'rules' => 'trim', 
				'type' => 'date'
			),
			array(
				'field' => 'UslugaComplex_id', 
				'label' => 'Услуга', 
				'rules' => 'trim', 
				'type' => 'id'
			)
		),
		'createEvnUslugaOper' => array(
			array(
				'field' => 'Evn_pid',
				'label' => 'Идентификатор случая-родителя (движение в КВС, посещения в ТАП)',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnClass_id',
				'label' => 'Класс события',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_setDT',
				'label' => 'Дата и время начала выполнения услуги',
				'rules' => 'trim|required',
				'type' => 'datetime'
			),
			array(
				'field' => 'Evn_disDT',
				'label' => 'Дата и время окончания выполнения услуги',
				'rules' => 'trim|required',
				'type' => 'datetime'
			),
			array(
				'field' => 'UslugaPlace_id',
				'label' => 'Место выполнения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Org_id',
				'label' => 'Другая организация',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => 'trim',
				'type' => 'id',
				'checklpu' => true
			),
			array(
				'field' => 'LpuSectionProfile_id',
				'label' => 'Профиль',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'MedSpecOms_id',
				'label' => 'Специальность',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Рабочее место врача',
				'rules' => 'trim',
				'type' => 'id',
				'checklpu' => true
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
				'rules' => 'trim|required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPrescr_id',
				'label' => 'Идентификатор назначения',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'DiagSetClass_id',
				'label' => 'Вид диагноза',
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
				'field' => 'UslugaComplex_id',
				'label' => 'Услуга',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnUsluga_Price',
				'label' => 'Цена услуги',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'OperDiff_id',
				'label' => 'Категория сложности',
				'rules' => 'required',
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
				'field' => 'EvnUslugaOper_IsEndoskop',
				'label' => 'Признак использования эндоскопической аппаратуры',
				'rules' => 'required',
				'type' => 'api_flag'
			),
			array(
				'field' => 'EvnUslugaOper_IsKriogen',
				'label' => 'Признак использования криогенной аппаратуры',
				'rules' => 'required',
				'type' => 'api_flag'
			),
			array(
				'field' => 'EvnUslugaOper_IsLazer',
				'label' => 'Признак использования лазерной аппаратуры',
				'rules' => 'required',
				'type' => 'api_flag'
			),
			array(
				'field' => 'EvnUslugaOper_IsRadGraf',
				'label' => 'Признак использования рентгенологической аппаратуры',
				'rules' => '',
				'type' => 'api_flag'
			),
			array(
				'field' => 'EvnUslugaOper_IsVMT',
				'label' => 'Применение ВМТ',
				'rules' => 'required',
				'type' => 'api_flag'
			),
			array(
				'field' => 'EvnUslugaOper_IsMicrSurg',
				'label' => 'Микрохирургическая',
				'rules' => 'required',
				'type' => 'api_flag'
			),
			array(
				'field' => 'EvnUslugaOper_IsOpenHeart',
				'label' => 'На открытом сердце',
				'rules' => 'required',
				'type' => 'api_flag'
			),
			array(
				'field' => 'EvnUslugaOper_IsArtCirc',
				'label' => 'С искусственным кровообращением',
				'rules' => 'required',
				'type' => 'api_flag'
			),
			array(
				'field' => 'EvnUsluga_Kolvo',
				'label' => 'Количество',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'updateEvnUslugaOper' => array(
			array(
				'field' => 'EvnUsluga_id',
				'label' => 'Идентификатор оказания услуги',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_pid',
				'label' => 'Идентификатор случая-родителя (движение в КВС, посещения в ТАП)',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnClass_id',
				'label' => 'Класс события',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_setDT',
				'label' => 'Дата и время начала выполнения услуги',
				'rules' => 'trim',
				'type' => 'datetime'
			),
			array(
				'field' => 'Evn_disDT',
				'label' => 'Дата и время окончания выполнения услуги',
				'rules' => 'trim',
				'type' => 'datetime'
			),
			array(
				'field' => 'UslugaPlace_id',
				'label' => 'Место выполнения',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Org_id',
				'label' => 'Другая организация',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => 'trim',
				'type' => 'id',
				'checklpu' => true
			),
			array(
				'field' => 'LpuSectionProfile_id',
				'label' => 'Профиль',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'MedSpecOms_id',
				'label' => 'Специальность',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Рабочее место врача',
				'rules' => 'trim',
				'type' => 'id',
				'checklpu' => true
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPrescr_id',
				'label' => 'Идентификатор назначения',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'DiagSetClass_id',
				'label' => 'Вид диагноза',
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
				'field' => 'UslugaComplex_id',
				'label' => 'Услуга',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnUsluga_Price',
				'label' => 'Цена услуги',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'OperDiff_id',
				'label' => 'Категория сложности',
				'rules' => 'trim',
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
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnUslugaOper_IsEndoskop',
				'label' => 'Признак использования эндоскопической аппаратуры',
				'rules' => 'trim',
				'type' => 'api_flag'
			),
			array(
				'field' => 'EvnUslugaOper_IsKriogen',
				'label' => 'Признак использования криогенной аппаратуры',
				'rules' => 'trim',
				'type' => 'api_flag'
			),
			array(
				'field' => 'EvnUslugaOper_IsLazer',
				'label' => 'Признак использования лазерной аппаратуры',
				'rules' => 'trim',
				'type' => 'api_flag'
			),
			array(
				'field' => 'EvnUslugaOper_IsRadGraf',
				'label' => 'Признак использования рентгенологической аппаратуры',
				'rules' => 'trim',
				'type' => 'api_flag'
			),
			array(
				'field' => 'EvnUslugaOper_IsVMT',
				'label' => 'Применение ВМТ',
				'rules' => '',
				'type' => 'api_flag'
			),
			array(
				'field' => 'EvnUslugaOper_IsMicrSurg',
				'label' => 'Микрохирургическая',
				'rules' => '',
				'type' => 'api_flag'
			),
			array(
				'field' => 'EvnUslugaOper_IsOpenHeart',
				'label' => 'На открытом сердце',
				'rules' => '',
				'type' => 'api_flag'
			),
			array(
				'field' => 'EvnUslugaOper_IsArtCirc',
				'label' => 'С искусственным кровообращением',
				'rules' => '',
				'type' => 'api_flag'
			),
			array(
				'field' => 'EvnUsluga_Kolvo',
				'label' => 'Количество',
				'rules' => 'trim',
				'type' => 'int'
			)
		),
		'createEvnUsluga' => array(
			array(
				'field' => 'Evn_pid',
				'label' => 'Идентификатор случая-родителя (движение в КВС, посещения в ТАП)',
				'rules' => 'required',
				'type' => 'id',
				'checklpu' => true
			),
			array(
				'field' => 'EvnClass_id',
				'label' => 'Класс события',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_setDT',
				'label' => 'Дата и время начала выполнения услуги',
				'rules' => 'trim|required',
				'type' => 'datetime'
			),
			array(
				'field' => 'Evn_disDT',
				'label' => 'Дата и время окончания выполнения услуги',
				'rules' => 'trim|required',
				'type' => 'datetime'
			),
			array(
				'field' => 'UslugaPlace_id',
				'label' => 'Место выполнения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Org_id',
				'label' => 'Другая организация',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionProfile_id',
				'label' => 'Профиль',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'MedSpecOms_id',
				'label' => 'Специальность',
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
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
				'rules' => 'trim|required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPrescr_id',
				'label' => 'Идентификатор назначения',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'DiagSetClass_id',
				'label' => 'Вид диагноза',
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
				'field' => 'UslugaComplex_id',
				'label' => 'Услуга',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnUsluga_Price',
				'label' => 'Цена услуги',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnUsluga_Kolvo',
				'label' => 'Количество',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'EvnUsluga_Summa',
				'label' => 'Сумма',
				'rules' => '',
				'type' => 'int'
			)
		),
		'updateEvnUsluga' => array(
			array(
				'field' => 'EvnUsluga_id',
				'label' => 'Идентификатор оказания услуги',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_pid',
				'label' => 'Идентификатор случая-родителя (движение в КВС, посещения в ТАП)',
				'rules' => 'trim',
				'type' => 'id',
				'checklpu' => true
			),
			array(
				'field' => 'EvnClass_id',
				'label' => 'Класс события',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_setDT',
				'label' => 'Дата и время начала выполнения услуги',
				'rules' => 'trim',
				'type' => 'datetime'
			),
			array(
				'field' => 'Evn_disDT',
				'label' => 'Дата и время окончания выполнения услуги',
				'rules' => 'trim',
				'type' => 'datetime'
			),
			array(
				'field' => 'UslugaPlace_id',
				'label' => 'Место выполнения',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Org_id',
				'label' => 'Другая организация',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionProfile_id',
				'label' => 'Профиль',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'MedSpecOms_id',
				'label' => 'Специальность',
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
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPrescr_id',
				'label' => 'Идентификатор назначения',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'DiagSetClass_id',
				'label' => 'Вид диагноза',
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
				'field' => 'UslugaComplex_id',
				'label' => 'Услуга',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnUsluga_Price',
				'label' => 'Цена услуги',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnUsluga_Kolvo',
				'label' => 'Количество',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'EvnUsluga_Summa',
				'label' => 'Сумма',
				'rules' => '',
				'type' => 'int'
			)
		),
		'mloadEvnUslugaPanel' => array(
			array('field' => 'EvnUsluga_pid', 'label' => 'Идентификатор случая', 'rules' => 'required', 'type' => 'id')
		),
		'msaveEvnUslugaCommon' => array(
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
				'rules' => 'trim',
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
				'field' => 'MesOperType_id',
				'label' => 'Вид лечения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
				'default' => 1,
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
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => '',
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
				'default' => 1,
				'rules' => '',
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
			array('field' => 'EvnUslugaOnkoBeam_disDT'  ,'label' => 'Дата окончания','rules' => '','type' => 'date'),
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
			array('field' => 'EvnUslugaOnkoChem_disDT'  ,'label' => 'Дата окончания','rules' => '','type' => 'date'),
			array('field' => 'OnkoUslugaChemKindType_id'         ,'label' => 'Вид проведенного химиотерапевтического лечения','rules' => '','type' => 'id'),
			array('field' => 'OnkoUslugaChemFocusType_id'        ,'label' => 'Преимущественная направленность химиотерапии','rules' => '','type' => 'id'),
			array('field' => 'OnkoDrug_id'                       ,'label' => 'Кодированная номенклатура препаратов для лекарственного лечения злокачественных новообразований','rules' => '','type' => 'id'),
			array('field' => 'EvnUslugaOnkoChem_Dose'            ,'label' => 'Доза','rules' => '','type' => 'int'),
			//onkogormun
			array('field' => 'EvnUslugaOnkoGormun_disDT'    ,'label' => 'Дата окончания'                                                                                   ,'rules' => '','type' => 'date'),
			array('field' => 'EvnUslugaOnkoGormun_IsDrug'    ,'label' => 'Лекарственная'                                                                                   ,'rules' => '','type' => 'id'),
			array('field' => 'OnkoGormunType_id'    ,'label' => 'Вид проведенной гормоноиммунотерапии'                                                                                        ,'rules' => '','type' => 'id'),
			array('field' => 'OnkoUslugaGormunFocusType_id'  ,'label' => 'Преимущественная направленность гормоноиммунотерапии'                                            ,'rules' => '','type' => 'id'),
			array('field' => 'OnkoDrug_id'                   ,'label' => 'Кодированная номенклатура препаратов для лекарственного лечения злокачественных новообразований' ,'rules' => '','type' => 'id'),
			array('field' => 'EvnUslugaOnkoGormun_Dose'      ,'label' => 'Доза'                                                                                            ,'rules' => '','type' => 'string'),
			array('field' => 'ignoreParentEvnDateCheck', 'default' => 1, 'label' => 'Признак игнорирования проверки периода выполенения услуги', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnUslugaOnkoGormun_CountFractionRT' ,'label' => 'Кол-во фракций проведения лучевой терапии                                   ','rules' => '','type' => 'int'),
		),
		'mDeleteEvnUsluga' => array(
			array(
				'field' => 'ignorePaidCheck',
				'label' => 'Игнорировать признак оплаты случая',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnUsluga_id',
				'label' => 'Идентификатор услуги',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'mloadEvnUslugaEditForm' => array(// для api/EvnUsluga/mloadEvnUslugaEditForm
			array(
				'field' => 'class',
				'label' => 'Класс услуги',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'evnusluga_id',
				'label' => 'Идентификатор услуги',
				'rules' => 'required',
				'type' => 'id'
			)
		),
	);
	/**
	 * construct
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Проверка изменились ли обязательные услуги по КСГ
	 */
	function checkChangeEvnUslugaIsNeed($data) {
		// Если хотя бы одна заведенная услуга по старому КСГ с атрибутом «обязательная» не предусмотрена в новом КСГ
		$query = "
			select top 1
				eu.EvnUslugaStom_id
			from
				v_EvnUslugaStom eu (nolock)
				cross apply (
					select top 1
						MesUsluga_id
					from
						v_MesUsluga with (nolock)
					where
						Mes_id = :oldMes_id and UslugaComplex_id = EU.UslugaComplex_id and MesUsluga_IsNeedUsluga = 2
				) MU
				outer apply (
					select top 1
						MesUsluga_IsNeedUsluga
					from
						v_MesUsluga with (nolock)
					where
						Mes_id = :newMes_id and UslugaComplex_id = EU.UslugaComplex_id
				) MUNEW
			where
				eu.EvnDiagPLStom_id = :EvnDiagPLStom_id
				and ISNULL(MUNEW.MesUsluga_IsNeedUsluga, 1) = 1
		";

		$resp = $this->queryResult($query, array(
			'EvnDiagPLStom_id' => $data['mid'],
			'oldMes_id' => $data['oldMes_id'],
			'newMes_id' => $data['newMes_id']
		));

		if (!empty($resp[0]['EvnUslugaStom_id'])) {
			return array('Error_Msg' => '', 'needUslugaChange' => true);
		}

		return array('Error_Msg' => '', 'needUslugaChange' => false);
	}

	/**
	 * Функция связывает заказанную услугу с соответствующим направлением
	 * Временное решение. В идеале заказ и направление должны сохраняться одновременно.
	 */
	function saveEvnDirectionInEvnUsluga($data) {
		$query = "
			update EvnUsluga with(rowlock) 
			set EvnDirection_id = :EvnDirection_id
			where EvnUsluga_id = :EvnUsluga_id
		";
		$result = $this->db->query($query, $data);
		//echo getDebugSql($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение данных об обычной услуге
	 */
	function getEvnUslugaCommonViewData($data) {
		$accessType = 'EUC.Lpu_id = :Lpu_id';
		$join_msf = '';
		$params =  array(
			'EvnUslugaCommon_id' => $data['EvnUslugaCommon_id'],
			'Lpu_id' => $data['Lpu_id']
		);
		if (isset($data['session']['CurMedStaffFact_id']))
		{
			$accessType .= ' and (
				((EUC.MedPersonal_id is null or EUC.MedPersonal_id = MSF.MedPersonal_id) and EUC.LpuSection_uid = MSF.LpuSection_id)
				or exists (
					select top 1 WG.WorkGraph_id
					from v_WorkGraph WG (nolock)
					where (
						CAST(WG.WorkGraph_begDT as date) <= CAST(dbo.tzGetDate() as date)
						and CAST(WG.WorkGraph_endDT as date) >= CAST(dbo.tzGetDate() as date)
					)
					and WG.MedStaffFact_id = :user_MedStaffFact_id
				)
			)';
			$join_msf = 'left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = :user_MedStaffFact_id';
			$params['user_MedStaffFact_id'] = $data['session']['CurMedStaffFact_id'];
		}

		$selectPersonData = "PS.Person_SurName+' '+PS.Person_FirName+' '+isnull(PS.Person_SecName,'') as Person_Fio,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_Birthday,";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = PS.Person_id";
			$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null 
					then ps.Person_SurName+' '+ps.Person_FirName+' '+isnull(ps.Person_SecName,'') 
					else peh.PersonEncrypHIV_Encryp 
				end as Person_Fio,
				null as Person_Birthday,";
		}

		$query = "
			SELECT top 1
				case when {$accessType} then 'edit' else 'view' end as accessType,
				EUC.EvnUslugaCommon_id,
				EUC.EvnUslugaCommon_pid,
				ED.EvnDirection_id,
				EUC.Person_id,
				EUC.PersonEvn_id,
				EUC.Server_id,
				EUC.Usluga_id,
				EUC.UslugaComplex_id,
				EUC.EvnUslugaCommon_isCito,
				EUC.EvnUslugaCommon_Kolvo,
				EUC.PayType_id,
				--EUC.PrehospDirect_id,
				--EUC.TimetablePar_id,
				EUC.Lpu_id,
				EUC.LpuSection_uid,
				EUC.MedPersonal_id as MedStaffFact_uid,
				DLpuSection.Lpu_id as Lpu_did,
				EUC.LpuSection_uid,
				EUC.Org_uid,
				--EUC.MedPersonal_did as MedStaffFact_did,
				EUC.MedPersonal_sid as MedStaffFact_sid,
				{$selectPersonData}
				D.Diag_id,
				coalesce(D4.Diag_Code,D3.Diag_Code,D2.Diag_Code,D.Diag_Code) as Diag_Code,
				coalesce(D4.Diag_Name,D3.Diag_Name,D2.Diag_Name,D.Diag_Name) as Diag_Name,
				UC.UslugaComplex_Name as Usluga_Name,
				EUC.EvnUslugaCommon_id as Usluga_Number,
				ULpu.Lpu_Nick,
				ULpu.Lpu_Name,
				ULpu.UAddress_Address as Lpu_Address,
				ULpuSection.LpuSection_Code,
				ULpuSection.LpuSection_Name,
				convert(varchar(10), EUC.EvnUslugaCommon_setDT, 104) as EvnUslugaCommon_setDate,
				ISNULL(EUC.EvnUslugaCommon_setTime, '') as EvnUslugaCommon_setTime,
				MP.Person_SurName + ' ' + LEFT(MP.Person_FirName, 1)  + '. ' + ISNULL(LEFT(MP.Person_SecName, 1) + '.', '') as MedPersonal_Fin,
				DLpuSection.LpuSection_Code as DirectSubject_Code,-- кем направлен
                DLpuSection.LpuSection_Name as DirectSubject_Name,-- кем направлен
                DOrg.Org_Code as OrgDirectSubject_Code,
                ISNULL(DOrg.Org_Nick,NaprLpu.Lpu_Nick) as OrgDirectSubject_Name, -- кем направлен
				ED.EvnDirection_Num as EvnDirection_Num, -- номер направления
				convert(varchar(10), ED.EvnDirection_setDate, 104) as EvnDirection_setDate,
				case
				    when EVPLMedPersonal.Person_SurName is not null then EVPLMedPersonal.Person_SurName + ' ' + LEFT(EVPLMedPersonal.Person_FirName, 1)  + '. ' + ISNULL(LEFT(EVPLMedPersonal.Person_SecName, 1) + '.', '')
				    when DMedPersonal.Person_SurName is not null then DMedPersonal.Person_SurName + ' ' + LEFT(DMedPersonal.Person_FirName, 1)  + '. ' + ISNULL(LEFT(DMedPersonal.Person_SecName, 1) + '.', '')
				    when SecMedPersonal.Person_SurName is not null then SecMedPersonal.Person_SurName + ' ' + LEFT(SecMedPersonal.Person_FirName, 1)  + '. ' + ISNULL(LEFT(SecMedPersonal.Person_SecName, 1) + '.', '')
				    else CMedPersonal.Person_SurName + ' ' + LEFT(CMedPersonal.Person_FirName, 1)  + '. ' + ISNULL(LEFT(CMedPersonal.Person_SecName, 1) + '.', '')
				end as MedPersonalDirect_Fin,
				case when EvnLabRequest.EvnLabRequest_id is null then 0 else 1 end as isLab,
				--EUC.Study_uid
				doc.EvnXml_id
			FROM v_EvnUslugaCommon EUC with (nolock)
				left join v_Person_all PS with (nolock) on EUC.Person_id = PS.Person_id AND EUC.PersonEvn_id = PS.PersonEvn_id AND EUC.Server_id = PS.Server_id
				outer apply (
					select top 1 EvnDirection_id
					from v_EvnPrescrDirection with (nolock)
					where EvnPrescr_id = EUC.EvnPrescr_id
				) EPD
				left join v_EvnDirection_all ED with (nolock) on ISNULL(EUC.EvnDirection_id, EPD.EvnDirection_id) = ED.EvnDirection_id and ED.DirFailType_id is null
				left join v_EvnLabRequest EvnLabRequest with (nolock) on EvnLabRequest.EvnDirection_id = ED.EvnDirection_id
				outer apply (select top 1 * from v_EvnLabSample with (nolock) where EvnLabRequest_id = EvnLabRequest.EvnLabRequest_id) as EvnLabSample
				left join v_MedService MS with (nolock) on EvnLabRequest.MedService_id = MS.MedService_id
				left join v_Lpu ULpu with (nolock) on isnull(MS.Lpu_id,EUC.Lpu_id) = ULpu.Lpu_id
				left join v_LpuSection ULpuSection with (nolock) on coalesce(MS.LpuSection_id,EUC.LpuSection_uid,ED.LpuSection_did) = ULpuSection.LpuSection_id
				left join v_Lpu NaprLpu with (nolock) on isnull(ED.Lpu_sid, ULpuSection.Lpu_id) = NaprLpu.Lpu_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = isNull(EvnLabSample.MedPersonal_aid,EUC.MedPersonal_id) AND MP.Lpu_id = isnull(MS.Lpu_id,EUC.Lpu_id)
				left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EUC.UslugaComplex_id
				left join v_Diag D with (nolock) on ED.Diag_id = D.Diag_id

				left join v_EvnSection ES with (nolock) on EUC.EvnUslugaCommon_pid = ES.EvnSection_id
				left join v_Diag D3 with (nolock) on D3.Diag_id = ES.Diag_id
                left join v_MedPersonal SecMedPersonal with (nolock) on SecMedPersonal.MedPersonal_id = ES.MedPersonal_id

				left join v_EvnVizitPL EVPL with (nolock) on EUC.EvnUslugaCommon_pid = EVPL.EvnVizitPL_id
				left join v_Diag D4 with (nolock) on D4.Diag_id = EVPL.Diag_id
                left join v_MedPersonal EVPLMedPersonal with (nolock) on EVPLMedPersonal.MedPersonal_id = EVPL.MedPersonal_id

				left join v_LpuSection DLpuSection with (nolock) on COALESCE(ES.LpuSection_id,EVPL.LpuSection_id,EUC.LpuSection_uid,ED.LpuSection_id) = DLpuSection.LpuSection_id
				left join v_Org DOrg with (nolock) on isnull(EUC.Org_uid,ED.Lpu_id) = DOrg.Org_id
				left join v_MedPersonal DMedPersonal with (nolock) on isnull(EUC.MedPersonal_sid,ED.MedPersonal_id) = DMedPersonal.MedPersonal_id AND isnull(DLpuSection.Lpu_id,ED.Lpu_id) = DMedPersonal.Lpu_id
				left join v_Evn E with (nolock) on E.Evn_id = EUC.EvnPrescr_id
				left join v_pmUserCache pmUC with (nolock) on E.pmUser_updID = pmUC.PMUser_id
				left join v_MedPersonal CMedPersonal with (nolock) on CMedPersonal.MedPersonal_id = pmUC.MedPersonal_id
				left join V_Morbus Mor with (nolock) on EUC.Morbus_id = Mor.Morbus_id
				left join v_Diag D2 with (nolock) on D2.Diag_id = Mor.Diag_id
				
				outer apply (
					select top 1 EvnXml_id
					from v_EvnXml with (nolock)
					where Evn_id = EUC.EvnUslugaCommon_id
		            order by EvnXml_insDT desc -- костыль, должен быть только один протокол
				) doc

				{$join_msf}
				{$joinPersonEncrypHIV}
			WHERE
				EUC.EvnUslugaCommon_id = :EvnUslugaCommon_id
		";

		//echo getDebugSql($query, $params); exit;
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение списка для раздела "услуги" в стационаре
	 * @param $data
	 * @return bool
	 */
	function getEvnUslugaStacViewData($data) {
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);
		if (isset($data['EvnUslugaStac_pid']))
		{
			$filter = 'and (EU.EvnUsluga_pid = :EvnUsluga_pid)';
			$queryParams['EvnUsluga_pid'] = $data['EvnUslugaStac_pid'];
		}
		else
		{
			$filter = 'and EU.EvnUsluga_id = :EvnUsluga_id';
			$queryParams['EvnUsluga_id'] = $data['EvnUslugaStac_id'];
		}

		$accessAdd = "";
		if ( !($this->getRegionNick() == 'perm' || ($this->getRegionNick() == 'ufa' && isSuperadmin())) ) {
			$accessAdd = " and ISNULL(ES.EvnSection_IsPaid, 1) = 1 ";
		}
		
		$filterAccessRights = getAccessRightsTestFilter('UC.UslugaComplex_id');		
		$filterAccessRightsDenied = getAccessRightsTestFilter('UCMPp.UslugaComplex_id', false, true);
		$orf = isset($data['session']['medpersonal_id']) ? " or ED.MedPersonal_id = {$data['session']['medpersonal_id']}" : '';
		$filter .= !empty($filterAccessRights) ? " and (($filterAccessRights and UCp.UslugaComplex_id is null) $orf)" : '';

		$this->load->library('swEvnXml');
		$narcosis_protocol_type=swEvnXml::EVN_USLUGA_NARCOSIS_PROTOCOL_TYPE_ID;
		$query = "
			select
				case when EU.Lpu_id = :Lpu_id
					{$accessAdd} " .
					((/*$data['session']['isMedStatUser'] == false && */isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0)
						? "and (EU.MedPersonal_id = " . $data['session']['medpersonal_id']." or exists(
							select top 1 WG.WorkGraph_id
							from v_WorkGraph WG (nolock)
							inner join v_MedStaffFact WG_MSF (nolock) on WG_MSF.MedStaffFact_id = WG.MedStaffFact_id
							where WG_MSF.MedPersonal_id = {$data['session']['medpersonal_id']}
							and (
								CAST(WG.WorkGraph_begDT as date) <= CAST(dbo.tzGetDate() as date)
								and CAST(WG.WorkGraph_endDT as date) >= CAST(dbo.tzGetDate() as date)
							)
						))" : "")
					. "
					and EU.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper')
					then 'edit' else 'view' end as accessType,
				EU.EvnUsluga_id,
				EU.EvnUsluga_pid,
				EU.EvnUsluga_rid,
				EU.EvnClass_id,
				EU.EvnClass_Name,
				RTRIM(EU.EvnClass_SysNick) as EvnClass_SysNick,
				convert(varchar(10), EU.EvnUsluga_setDate, 104) as EvnUsluga_setDate,
				ISNULL(EU.EvnUsluga_setTime, '') as EvnUsluga_setTime,
				ISNULL(Usluga.Usluga_Code, UC.UslugaComplex_Code) as Usluga_Code,
				coalesce(ucms.UslugaComplex_Name, Usluga.Usluga_Name, UC.UslugaComplex_Name) as Usluga_Name,
				ROUND(cast(EU.EvnUsluga_Kolvo as float), 2) as EvnUsluga_Kolvo,
				EU.UslugaComplex_id,
				docOper.EvnXml_id,
				docNarcosis.EvnXml_id as EvnXmlNarcosis_id
			from v_EvnUsluga EU with (nolock)
				left join v_Usluga Usluga with (nolock) on Usluga.Usluga_id = EU.Usluga_id
				left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
				left join v_EvnSection ES with (nolock) on ES.EvnSection_id = EU.EvnUsluga_pid
				outer apply (
					select top 1 
						EvnXml_id
					from 
						v_EvnXml with (nolock)
					where 
						Evn_id = EU.EvnUsluga_id
					order by 
						EvnXml_insDT desc -- костыль, должен быть только один протокол
				) docOper
				outer apply (
					select top 1 
						EvnXml_id
					from 
						v_EvnXml with (nolock)
					where 
						Evn_id = EU.EvnUsluga_id
						and XmlType_id={$narcosis_protocol_type}
					order by 
						EvnXml_insDT desc -- костыль, для вывовода протокола анестезии
				) docNarcosis
				left join v_EvnLabRequest ELR with (nolock) on ELR.EvnDirection_id = EU.EvnDirection_id
				left join v_UslugaComplexMedService UCMS (nolock) on UCMS.MedService_id = ELR.MedService_id and UCMS.UslugaComplex_id = UC.UslugaComplex_id and UCMS.UslugaComplexMedService_pid is null
				left join v_EvnDirection_all ED with (nolock) on ED.EvnDirection_id = EU.EvnDirection_id
				outer apply (
					select top 1
						UCMPp.UslugaComplex_id
					from
						v_UslugaComplexMedService UCMPp (nolock)
					inner join v_EvnLabRequestUslugaComplex ELRUC on UCMPp.UslugaComplex_id = ELRUC.UslugaComplex_id and ELRUC.EvnLabRequest_id = ELR.EvnLabRequest_id
					inner join v_EvnLabSample ELS (nolock) on ELS.EvnLabSample_id = ELRUC.EvnLabSample_id and ELS.LabSampleStatus_id IN(4,6)
					where
						UCMS.UslugaComplexMedService_id = UCMPp.UslugaComplexMedService_pid
						".((!empty($filterAccessRightsDenied))?"and ".$filterAccessRightsDenied:'')."
				) as UCp
			where (1 = 1)
				{$filter}
				and (EU.Lpu_id = :Lpu_id or " . (isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0 ? 1 : 0) . " = 1)
				and (
					EU.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper')--, 'EvnUslugaStom'
					OR (EU.EvnClass_SysNick like 'EvnUslugaPar' and EU.EvnUsluga_setDate is not null)
				)
			order by EvnUsluga_setDate	
		";

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение списка для раздела "услуги" в полке
	 * @param $data
	 * @return bool
	 */
	function getEvnUslugaViewData($data) {
		$response = array();

		if ($this->usePostgreLis) {
			$this->load->swapi('lis');
			$response = $this->lis->GET('EvnUsluga/ViewData', $data, 'list');
			if (!$this->isSuccessful($response)) {
				return false;
			}
		}

		$resp = $this->_getEvnUslugaViewData($data, $response);
		if (!is_array($resp)) {
			return false;
		}

		return array_merge($response, $resp);
	}

	/**
	 * Получение списка для раздела "услуги" в полке
	 * @param $data
	 * @return bool
	 */
	function _getEvnUslugaViewData($data, $excepts = array()) {
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);
		$from_clause = '';
		if (isset($data['EvnUsluga_pid']))
		{
			$where_clause = 'and (:EvnUsluga_pid in (EU.EvnUsluga_pid, EU.EvnUsluga_rid))';
			$queryParams['EvnUsluga_pid'] = $data['EvnUsluga_pid'];
		}
		else
		{
			$where_clause = 'and EU.EvnUsluga_id = :EvnUsluga_id';
			$queryParams['EvnUsluga_id'] = $data['EvnUsluga_id'];
		}

		$this->load->model('EvnVizitPL_model');
		if ( $this->EvnVizitPL_model->isUseVizitCode ) {
			$where_clause .= "
				and ISNULL(EU.EvnUsluga_IsVizitCode, 1) = 1
			";
		}

		$accessAdd = "";
		if ( !($this->getRegionNick() == 'perm' || ($this->getRegionNick() == 'ufa' && isSuperadmin())) ) {
			$accessAdd = "
				and ISNULL(EV.EvnVizit_IsPaid, 1) = 1
				and ISNULL(ES.EvnSection_IsPaid, 1) = 1
			";
		}
		
		$filterAccessRights = getAccessRightsTestFilter('UC.UslugaComplex_id');		
		$filterAccessRightsDenied = getAccessRightsTestFilter('UCMPp.UslugaComplex_id', false, true);
		$orf = isset($data['session']['medpersonal_id']) ? " or ED.MedPersonal_id = {$data['session']['medpersonal_id']}" : '';
		$where_clause .= !empty($filterAccessRights) ? " and (($filterAccessRights and UCp.UslugaComplex_id is null) $orf)" : '';

		$except_ids = array();
		foreach($excepts as $except) {
			if (!empty($except['EvnUsluga_id'])) {
				$except_ids[] = $except['EvnUsluga_id'];
			}
		}
		if (count($except_ids) > 0) {
			$except_ids = implode(",", $except_ids);
			$where_clause .= " and EU.EvnUsluga_id not in ({$except_ids})";
		}

		$this->load->library('swEvnXml');
		$narcosis_protocol_type=swEvnXml::EVN_USLUGA_NARCOSIS_PROTOCOL_TYPE_ID;
		$oper_protocol_type=swEvnXml::EVN_USLUGA_OPER_PROTOCOL_TYPE_ID;
		$query = "
			select
				case when EU.Lpu_id = :Lpu_id
					{$accessAdd} " .
			((/*$data['session']['isMedStatUser'] == false && */isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0)
					? "and (EU.MedPersonal_id = " . $data['session']['medpersonal_id']." or exists(
							select top 1 WG.WorkGraph_id
							from v_WorkGraph WG (nolock)
							inner join v_MedStaffFact WG_MSF (nolock) on WG_MSF.MedStaffFact_id = WG.MedStaffFact_id
							where WG_MSF.MedPersonal_id = {$data['session']['medpersonal_id']}
							and (
								CAST(WG.WorkGraph_begDT as date) <= CAST(dbo.tzGetDate() as date)
								and CAST(WG.WorkGraph_endDT as date) >= CAST(dbo.tzGetDate() as date)
							)
						))" : "")
					. "
					and EU.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper')
					then 'edit' else 'view' end as accessType,
				EU.EvnUsluga_id
				,EU.EvnUsluga_pid
				,EU.EvnUsluga_rid
				,EU.EvnClass_id
				,EU.EvnClass_Name
				,RTRIM(EU.EvnClass_SysNick) as EvnClass_SysNick
				,convert(varchar(10), EU.EvnUsluga_setDate, 104) as EvnUsluga_setDate
				,ISNULL(EU.EvnUsluga_setTime, '') as EvnUsluga_setTime
				,UC.UslugaComplex_id
				,ISNULL(Usluga.Usluga_Code, UC.UslugaComplex_Code) as Usluga_Code
				,coalesce(ucms.UslugaComplex_Name, UC.UslugaComplex_Name, Usluga.Usluga_Name) as Usluga_Name
				,ROUND(cast(EU.EvnUsluga_Kolvo as float), 2) as EvnUsluga_Kolvo
				,doc.EvnXml_id
				,docNarcosis.EvnXml_id as EvnXmlNarcosis_id
            from
				v_EvnUsluga EU with (nolock)
				left join v_Usluga Usluga with (nolock) on Usluga.Usluga_id = EU.Usluga_id
				left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
				left join EvnVizit EV with (nolock) on EV.EvnVizit_id = EU.EvnUsluga_pid
				left join EvnSection ES with (nolock) on ES.EvnSection_id = EU.EvnUsluga_pid
				{$from_clause}
				left join v_EvnLabRequest ELR with (nolock) on ELR.EvnDirection_id = EU.EvnDirection_id
				left join v_UslugaComplexMedService UCMS (nolock) on UCMS.MedService_id = ELR.MedService_id and UCMS.UslugaComplex_id = UC.UslugaComplex_id and UCMS.UslugaComplexMedService_pid is null
				left join v_EvnDirection_all ED with (nolock) on ED.EvnDirection_id = EU.EvnDirection_id
				outer apply (
					select top 1 
						EvnXml_id
					from 
						v_EvnXml with (nolock)
					where 
						Evn_id = EU.EvnUsluga_id
						and XmlType_id={$oper_protocol_type}
					order by 
						EvnXml_insDT desc -- костыль, должен быть только один протокол
				) doc				
				outer apply (
					select top 1 
						EvnXml_id
					from 
						v_EvnXml with (nolock)
					where 
						Evn_id = EU.EvnUsluga_id
						and XmlType_id={$narcosis_protocol_type}
					order by 
						EvnXml_insDT desc -- костыль, для вывовода протокола анестезии
				) docNarcosis
				outer apply (
					select top 1
						UCMPp.UslugaComplex_id
					from
						v_UslugaComplexMedService UCMPp (nolock)
					inner join v_EvnLabRequestUslugaComplex ELRUC on UCMPp.UslugaComplex_id = ELRUC.UslugaComplex_id and ELRUC.EvnLabRequest_id = ELR.EvnLabRequest_id
					inner join v_EvnLabSample ELS (nolock) on ELS.EvnLabSample_id = ELRUC.EvnLabSample_id and ELS.LabSampleStatus_id IN(4,6)
					where
						UCMS.UslugaComplexMedService_id = UCMPp.UslugaComplexMedService_pid
						".((!empty($filterAccessRightsDenied))?"and ".$filterAccessRightsDenied:'')."
				) as UCp
            where
				(1 = 1)
				{$where_clause}
				and (EU.Lpu_id = :Lpu_id or " . (isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0 ? 1 : 0) . " = 1)
				and (
					EU.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaStom' /*, 'EvnUslugaOnkoChem', 'EvnUslugaOnkoBeam', 'EvnUslugaOnkoGormun', 'EvnUslugaOnkoSurg'*/ ) OR (EU.EvnClass_SysNick like 'EvnUslugaPar' and EU.EvnUsluga_setDate is not null)
				)
		";
		
		//echo getDebugSql($query, $queryParams);
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}
		$response = $result->result('array');

		$attribute_type_list = array();
		if (count($response) > 0) {
			$UslugaComplex_ids = array(0);
			foreach($response as $item) {
				if (!empty($item['UslugaComplex_id']) && !in_array($item['UslugaComplex_id'], $UslugaComplex_ids)) {
					$UslugaComplex_ids[] = $item['UslugaComplex_id'];
				}
			}
			$UslugaComplex_ids_str = implode(',', $UslugaComplex_ids);
			$query = "
				select
					UCA.UslugaComplex_id,
					UCAT.UslugaComplexAttributeType_SysNick
				from v_UslugaComplexAttribute UCA with(nolock)
				inner join v_UslugaComplexAttributeType UCAT with(nolock) on UCAT.UslugaComplexAttributeType_id = UCA.UslugaComplexAttributeType_id
				where UCA.UslugaComplex_id in ($UslugaComplex_ids_str)
			";
			$resp = $this->queryResult($query);
			if (!is_array($resp)) {
				return false;
			}
			foreach($resp as $item) {
				$key = $item['UslugaComplex_id'];
				$attribute_type_list[$key][] = $item['UslugaComplexAttributeType_SysNick'];
			}
		}

		foreach($response as &$item) {
			$key = $item['UslugaComplex_id'];
			$list = isset($attribute_type_list[$key])?$attribute_type_list[$key]:array();
			$item['UslugaComplexAttrbuteTypeList'] = json_encode($list);
		}

		return $response;
	}

	/**
	 * @param array $data
	 * @param string $usluga_class
	 * @param string $uc_list
	 * @return int
	 */
	function checkEvnUslugaDoubles($data, $usluga_class, $uc_list = null) {
		$uc_filter = 'and ISNULL(UslugaComplex_id, 0) = ISNULL(:UslugaComplex_id, 0)';
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'Person_id' => $data['Person_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'LpuSection_uid' => $data['LpuSection_uid'],
			'Lpu_uid' => (!empty($data['Lpu_uid']) ? $data['Lpu_uid']: NULL),
			'Usluga_id' => (!empty($data['Usluga_id']) ? $data['Usluga_id'] : NULL),
			'UslugaComplex_id' => (!empty($data['UslugaComplex_id']) ? $data['UslugaComplex_id'] : NULL),
			'PayType_id' => $data['PayType_id'],
		);
		if (!empty($uc_list)) {
			unset($queryParams['UslugaComplex_id']);
			$queryParams['Usluga_id'] = NULL;
			$uc_filter = "and ISNULL(UslugaComplex_id, 0) in ({$uc_list})";
		}
		switch ( $usluga_class ) {
			case 'common':
				// если движение оплачено, то данная проверка не нужна refs #74335
				// завернул в условие, ибо https://redmine.swan.perm.ru/issues/76864
				if ( !empty($data['EvnUslugaCommon_pid']) ) {
					$resp = $this->queryResult("
						select
							ES.EvnSection_id
						from
							v_EvnSection es (nolock)
						where
							es.EvnSection_id = :EvnUslugaCommon_pid
							and es.EvnSection_IsPaid = 2
					", array(
						'EvnUslugaCommon_pid' => $data['EvnUslugaCommon_pid']
					));

					if (!empty($resp[0]['EvnSection_id'])) {
						return 0;
					}
				}

				if(is_object($data['EvnUslugaCommon_setDate'])){
					$data['EvnUslugaCommon_setDate'] = $data['EvnUslugaCommon_setDate']->format('Y-m-d H:i:s');
				} else if (empty($data['fromAPI'])) { // для АПИ дата идёт уже со временем.
					$data['EvnUslugaCommon_setDate'] .= ' ' . (isset($data['EvnUslugaCommon_setTime']) ? $data['EvnUslugaCommon_setTime'] : '00:00') . ':00';
				}

				$query = "
					select count(EvnUslugaCommon_id) as Usluga_Count
					from v_EvnUslugaCommon with (nolock)
					where Lpu_id = :Lpu_id
						and Person_id = :Person_id
						and ((MedPersonal_id = :MedPersonal_id and LpuSection_uid = :LpuSection_uid) or (Lpu_uid = :Lpu_uid))
						and ISNULL(Usluga_id, 0) = ISNULL(:Usluga_id, 0)
						{$uc_filter}
						and PayType_id = :PayType_id
						and EvnUslugaCommon_setDT = cast(:EvnUslugaCommon_setDate as datetime)
						and EvnUslugaCommon_id <> ISNULL(:EvnUslugaCommon_id, 0)
				";
				$queryParams['EvnUslugaCommon_id'] =  $data['EvnUslugaCommon_id'];
				$queryParams['EvnUslugaCommon_setDate'] =  $data['EvnUslugaCommon_setDate'];
			break;

			case 'oper':
				// если движение оплачено, то данная проверка не нужна refs #74335
				$resp = $this->queryResult("
					select
						ES.EvnSection_id
					from
						v_EvnSection es (nolock)
					where
						es.EvnSection_id = :EvnUslugaOper_pid
						and es.EvnSection_IsPaid = 2
				", array(
					'EvnUslugaOper_pid' => $data['EvnUslugaOper_pid']
				));

				if (!empty($resp[0]['EvnSection_id'])) {
					return 0;
				}

				$data['EvnUslugaOper_setDate'] .= ' ' . (isset($data['EvnUslugaOper_setTime']) ? $data['EvnUslugaOper_setTime'] : '00:00') . ':00';

				$query = "
					select count(EvnUslugaOper_id) as Usluga_Count
					from v_EvnUslugaOper with (nolock)
					where Lpu_id = :Lpu_id
						and Person_id = :Person_id
						and ((MedPersonal_id = :MedPersonal_id and LpuSection_uid = :LpuSection_uid) or (Lpu_uid = :Lpu_uid))
						and ISNULL(Usluga_id, 0) = ISNULL(:Usluga_id, 0)
						{$uc_filter}
						and PayType_id = :PayType_id
						and EvnUslugaOper_setDT = cast(:EvnUslugaOper_setDate as datetime)
						and EvnUslugaOper_id <> ISNULL(:EvnUslugaOper_id, 0)
				";
				$queryParams['EvnUslugaOper_id'] =  $data['EvnUslugaOper_id'];
				$queryParams['EvnUslugaOper_setDate'] =  $data['EvnUslugaOper_setDate'];
			break;

			case 'stom':
				$edpfilter = "";
				if (!empty($data['EvnDiagPLStom_id'])) {
					$queryParams['EvnDiagPLStom_id'] = $data['EvnDiagPLStom_id'];
					$edpfilter = " and EvnDiagPLStom_id = :EvnDiagPLStom_id";
				}
				$query = "
					select count(EvnUslugaStom_id) as Usluga_Count
					from v_EvnUslugaStom with (nolock)
					where Lpu_id = :Lpu_id
						and Person_id = :Person_id
						and MedPersonal_id = :MedPersonal_id
						and LpuSection_uid = :LpuSection_uid
						and ISNULL(Usluga_id, 0) = ISNULL(:Usluga_id, 0)
						{$uc_filter}
						and PayType_id = :PayType_id
						and EvnUslugaStom_setDate = cast(:EvnUslugaStom_setDate as datetime)
						and EvnUslugaStom_id <> ISNULL(:EvnUslugaStom_id, 0)
						and EvnUslugaStom_pid = ISNULL(:EvnUslugaStom_pid, 0)
						{$edpfilter}
				";
				$queryParams['EvnUslugaStom_id'] =  $data['EvnUslugaStom_id'];
				$queryParams['EvnUslugaStom_setDate'] =  $data['EvnUslugaStom_setDate'];
				$queryParams['EvnUslugaStom_pid'] = (!empty($data['EvnUslugaStom_pid']) ? $data['EvnUslugaStom_pid'] : NULL);
			break;

			default:
				return -1;
			break;
		}

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');

			if ( is_array($response) && count($response) > 0 ) {
				if ( isset($response[0]['Usluga_Count']) ) {
					return $response[0]['Usluga_Count'];
				}
				else {
					return -1;
				}
			}
			else {
				return -1;
			}
		}
		else {
			return -1;
		}
	}
	
	/**
	 * Проверка возможности удаления услуги в движении для Екатеринбурга
	 */
	function checkEkbSignRao($data) {
		$query = "
			select top 1
				es.EvnSection_id,
				eus1.EvnUslugaSignRao_id,
				mes1.MesUslugaSignRao_id,
				eus2.EvnUsluga_id
			from
				v_EvnSection es (nolock)
				outer apply(
					select top 1
						eu.EvnUsluga_id as EvnUslugaSignRao_id
					from
						v_EvnUsluga eu with (nolock)
						inner join r66.v_UslugaComplexPartitionLink ucpl (nolock) on ucpl.UslugaComplex_id = eu.UslugaComplex_id
					where
						eu.EvnUsluga_pid = es.EvnSection_id
						and ucpl.UslugaComplexPartitionLink_Signrao = 1
						and eu.EvnUsluga_id <> :id
				) eus1
				outer apply(
					select top 1
						mu.MesUsluga_id as MesUslugaSignRao_id
					from
						v_MesUsluga mu (nolock)
						inner join r66.v_UslugaComplexPartitionLink ucpl (nolock) on ucpl.UslugaComplex_id = mu.UslugaComplex_id
					where
						mu.Mes_id = es.Mes_sid
						and ucpl.UslugaComplexPartitionLink_Signrao = 1
				) mes1
				outer apply(
					select top 1
						eu.EvnUsluga_id
					from
						v_EvnUsluga eu with (nolock)
						inner join r66.v_UslugaComplexPartitionLink ucpl (nolock) on ucpl.UslugaComplex_id = eu.UslugaComplex_id
						inner join r66.v_UslugaComplexPartition ucp (nolock) on ucp.UslugaComplexPartition_id = ucpl.UslugaComplexPartition_id
					where
						eu.EvnUsluga_pid = es.EvnSection_id
						and ucp.UslugaComplexPartition_Code = '105'
						and eu.EvnUsluga_id <> :id
				) eus2
			where
				es.EvnSection_id = :EvnUsluga_pid
		";
		
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0]['EvnSection_id'])) {
				if ((!empty($resp[0]['EvnUslugaSignRao_id']) || !empty($resp[0]['MesUslugaSignRao_id'])) && empty($resp[0]['EvnUsluga_id'])) {
					return false;
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Проверка возможности удаления услуги в движении для Екатеринбурга
	 */
	function checkEkbKsgUslugaComplex($data) {
		$query = "
			select top 1
				mouc.MesOldUslugaComplex_id,
				INES.EvnUsluga_id
			from
				v_EvnSection es (nolock)
				inner join v_MesOldUslugaComplex mouc (nolock) on mouc.Mes_id = es.Mes_sid
				outer apply (
					select top 1
						euc.EvnUsluga_id
					from
						v_EvnUsluga euc (nolock)
					where
						UslugaComplex_id in (
							select
								UslugaComplex_id
							from
								v_MesOldUslugaComplex (nolock)
							where
								Mes_id = mouc.Mes_id
						)
						and euc.EvnUsluga_pid = es.EvnSection_id
					order by
						case when euc.EvnUsluga_id <> :id then 1 else 2 end ASC
				) INES
			where
				es.EvnSection_id = :EvnUsluga_pid
				and ISNULL(es.EvnSection_IsPriem, 1) = 1
		";
		
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0]['MesOldUslugaComplex_id']) && !empty($resp[0]['EvnUsluga_id']) && $resp[0]['EvnUsluga_id'] == $data['id']) {
				return false;
			}
		}
		
		return true;
	}

	/**
	 * Проверки и прочая логика перед удалением услуги
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	private function _beforeDeleteEvnUsluga($data) {
		$evnData = $this->getFirstRowFromQuery("SELECT
			e.EvnClass_SysNick,
			eu.EvnUsluga_pid,
			eu.EvnPrescr_id,
			eu.EvnDirection_id,
			STUFF(
				(SELECT
					','+cast(tm.PersonToothCard_id as varchar)
				FROM
					v_PersonToothCard tm WITH (nolock)
				WHERE
					tm.EvnUsluga_id = eu.EvnUsluga_id
				FOR XML PATH ('')
				), 1, 1, ''
			) as PersonToothCard_id_List,
			coalesce(EPL.EvnPL_IsPaid, EV.EvnVizit_IsPaid, ES.EvnSection_IsPaid, 1) as EvnUsluga_IsPaid,
			coalesce(EPL.EvnPL_IndexRep, EV.EvnVizit_IndexRep, ES.EvnSection_IndexRep, 0) as EvnUsluga_IndexRep,
			coalesce(EPL.EvnPL_IndexRepInReg, EV.EvnVizit_IndexRepInReg, ES.EvnSection_IndexRepInReg, 1) as EvnUsluga_IndexRepInReg
			FROM v_EvnUsluga_all eu with (nolock)
			left join v_evn e with (nolock) on e.Evn_id = eu.EvnUsluga_pid
			left join v_EvnVizit EV with (nolock) on EV.EvnVizit_id = e.Evn_id
			left join v_EvnPL EPL with (nolock) on EPL.EvnPL_id = e.Evn_pid
            left join v_EvnSection ES with (nolock) on ES.EvnSection_id = e.Evn_id
			WHERE eu.EvnUsluga_id = :id",
			array('id' => $data['id'])
		);
		if (false === $evnData) {
			throw new Exception('Не удалось получить данные случая оказания услуги!');
		}

		if (
			!($this->getRegionNick() == 'perm' && (!empty($data['ignorePaidCheck']) || $evnData['EvnUsluga_IndexRep'] >= $evnData['EvnUsluga_IndexRepInReg']))
			&& !($this->getRegionNick() == 'ufa' && isSuperadmin())
			&& !empty($evnData['EvnUsluga_IsPaid']) && $evnData['EvnUsluga_IsPaid'] == 2
		) {
			throw new Exception('Нельзя удалить услугу, т.к. случай оплачен');
		}

		$data['ParentEvnClass_SysNick'] = $evnData['EvnClass_SysNick'];
		$data['EvnUsluga_pid'] = $evnData['EvnUsluga_pid'];
		$data['EvnPrescr_id'] = $evnData['EvnPrescr_id'];
		$data['EvnDirection_id'] = $evnData['EvnDirection_id'];
		$data['PersonToothCard_id_List'] = $evnData['PersonToothCard_id_List'];

		if ($this->getRegionNick() == 'ekb') {
			if (!empty($data['EvnUsluga_pid'])) {
				$result = $this->checkEkbSignRao($data);
				if (!$result) {
					throw new Exception('В движении не может быть услуг с признаком SIGNRAO без услуг категории "Пребывание в РАО стационара"');
				}
				$result = $this->checkEkbKsgUslugaComplex($data);
				if (!$result) {
					throw new Exception('В движении должны быть услуги соответсвующие услуге для хирургической КСГ');
				}
			}
		}

		if (getRegionNick() == 'ufa' && $data['class'] == 'EvnUslugaStom') {
			// проверяем, что ТАП не закрыт
			$resp = $this->queryResult("
				select
					eps.EvnPLStom_id
				from
					v_EvnUslugaStom eus (nolock)
					inner join v_EvnPLStom eps (nolock) on eps.EvnPLStom_id = eus.EvnUslugaStom_rid
				where
					eus.EvnUslugaStom_id = :EvnUslugaStom_id
					and eps.EvnPLStom_IsFinish = 2
					and not exists (
						select top 1
							eus2.EvnUslugaStom_id
						from
							v_EvnUslugaStom eus2 (nolock)
						where
							eus2.EvnUslugaStom_id <> eus.EvnUslugaStom_id
							and eus2.EvnUslugaStom_rid = eps.EvnPLStom_id
					)
			", array(
				'EvnUslugaStom_id' => $data['id']
			));

			if (!empty($resp[0]['EvnPLStom_id'])) {
				throw new Exception('Стоматологический случай лечения должен содержать хотя бы одну услугу');
			}
		}

		if (getRegionNick() == 'astra' && $data['ParentEvnClass_SysNick'] == 'EvnSection') {
			$LpuSectionProfile_Code = $this->getFirstResultFromQuery("
				select lsp.LpuSectionProfile_Code
				from v_EvnSection es (nolock)
				inner join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = es.LpuSectionProfile_id
				where es.EvnSection_id = :Evn_id",
				array('Evn_id' => $data['EvnUsluga_pid'])
			);
			if ($LpuSectionProfile_Code != 5) {
				$usluga_cnt = $this->getFirstResultFromQuery("
					select COUNT(*) [cnt] from v_EvnUsluga (nolock) where EvnUsluga_pid = :Evn_id and EvnUsluga_id != :id
				", array(
					'Evn_id' => $data['EvnUsluga_pid'],
					'id' => $data['id']
				));
				if ($usluga_cnt == 0) {
					throw new Exception('В движение должна быть добавлена хотя бы одна услуга');
				}
			}
		}

		// если протокол подписан, то запрещаем удалять услугу
		$resp = $this->queryResult("
			select top 1
				ex.EvnXml_id
			from
				v_EvnXml ex (nolock)
			where
				ex.Evn_id = :EvnUsluga_id
				and ex.EvnXml_IsSigned = 2
		", array(
			'EvnUsluga_id' => $data['id']
		));

		if (!empty($resp[0]['EvnXml_id'])) {
			throw new Exception('Протокол услуги зарегистрирован в региональном РЭМД, удаление услуги невозможно');
		}

		return $data;
	}

	/**
	 * Логика после успешного удаления услуги
	 * @param $data
	 * @throws Exception
	 */
	private function _afterDeleteEvnUsluga($data) {
		if (!empty($data['EvnPrescr_id'])) {
			$this->load->model('EvnPrescr_model', 'EvnPrescr_model');
			$tmp = $this->EvnPrescr_model->rollbackEvnPrescrExecution(array(
				'EvnPrescr_id' => $data['EvnPrescr_id'],
				'pmUser_id' => $data['pmUser_id'],
			));
			if (!empty($tmp[0]['Error_Msg'])) {
				//нужно откатить транзакцию
				throw new Exception($tmp[0]['Error_Msg']);
			}
		}
		if (!empty($data['PersonToothCard_id_List'])) {
			/*
			 * нужно удалить состояния в ЗК, установленное этой услугой,
			 * в случае ошибки нужно откатить транзакцию
			 */
			$tmp = explode(',', $data['PersonToothCard_id_List']);
			$this->load->model('PersonToothCard_model', 'PersonToothCard_model');
			$data['IsRemove'] = 2;
			foreach($tmp as $id) {
				$data['PersonToothCard_id'] = $id;
				$this->PersonToothCard_model->deleteState($data);
			}
		}
		if (!empty($data['EvnDirection_id'])) {
			$query = "
				select top 1
					es.EvnStatus_SysNick,
					ed.EvnDirection_id,
					TTMS.TimetableMedService_begTime
				from
					v_EvnDirection_all ed (nolock)
					left join v_TimetableMedService_lite TTMS (nolock) on ED.EvnDirection_id = TTMS.EvnDirection_id
					left join v_EvnStatus es (nolock) on es.EvnStatus_id = ed.EvnStatus_id
				where
					ed.EvnDirection_id = :EvnDirection_id
			";
			$result = $this->db->query($query, array(
				'EvnDirection_id' => $data['EvnDirection_id']
			));
			if (is_object($result)) {
				$resp = $result->result('array');
				$this->load->model('Evn_model', 'Evn_model');
				if (!empty($resp[0]['TimetableMedService_begTime'])) {
					$EDEvnStatus_SysNick = 'DirZap';
				} else {
					$EDEvnStatus_SysNick = 'Queued';
				}
				$this->Evn_model->updateEvnStatus(array(
					'Evn_id' => $data['EvnDirection_id'],
					'EvnStatus_SysNick' => $EDEvnStatus_SysNick,
					'EvnClass_SysNick' => 'EvnDirection',
					'pmUser_id' => $data['pmUser_id']
				));
			}
		}
	}

	/**
	 * Удаление в транзакции
	 * @param $data
	 * @return array
	 */
	function deleteEvnUsluga($data) {
		$response = array(array(
			'Error_Msg' => null,
			'Error_Code' => null,
		));
		$startedTrans = false;
		try {
			$startedTrans = $this->beginTransaction();
			$data = $this->_beforeDeleteEvnUsluga($data);
			$query = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);
				exec p_" . $data['class'] . "_del
					@" . $data['class'] . "_id = :id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$result = $this->db->query($query, array(
				'id' => $data['id'],
				'pmUser_id' => $data['pmUser_id']
			));
			if ( !is_object($result) ) {
				throw new Exception('Ошибка при выполнении запроса к базе данных (удаление услуги)');
			}
			$response = $result->result('array');
			if (!empty($response[0]['Error_Msg'])) {
				throw new Exception($response[0]['Error_Msg']);
			}
			$this->_afterDeleteEvnUsluga($data);
		} catch (Exception $e) {
			if ($startedTrans) {
				$this->rollbackTransaction();
			}
			$response[0]['Error_Msg'] = $e->getMessage();
			$response[0]['Error_Code'] = $e->getCode();
			return $response;
		}
		if ($startedTrans) {
			$this->commitTransaction();
		}
		try {
			// @todo переместить в _afterDeleteEvnUsluga, если нужно, чтобы отменялась транзакция
			if (!empty($data['EvnUsluga_pid'])
				&& !empty($data['ParentEvnClass_SysNick'])
				&& 'EvnSection' == $data['ParentEvnClass_SysNick']
			) {
				$this->load->model('EvnSection_model');
				// пересчитать КСГ/КПГ/Коэф в движении
				$this->EvnSection_model->recalcKSGKPGKOEF($data['EvnUsluga_pid'], $data['session'], array(
					'byEvnUslugaChange' => true
				));
			}
		} catch (Exception $e) {
			//$this->_setAlertMsg("<div>При перерасчете КСГ/КПГ произошла ошибка</div><div>{$e->getMessage()}</div>");
		}
		return $response;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getUslugaComplexListDetails($data) {
		$query = "
			select
				UCL.UslugaComplexList_id as UslugaComplexList_id,
				U.Usluga_id as Usluga_id,
				UC.UslugaClass_Code as UslugaClass_Code
			from
				UslugaComplexList UCL with (nolock)
				inner join Usluga U with (nolock) on U.Usluga_id = UCL.Usluga_id
				inner join UslugaComplex UCom with (nolock) on UCom.UslugaComplex_id = UCL.UslugaComplex_id
				inner join UslugaClass UC with (nolock) on UC.UslugaClass_id = UCL.UslugaClass_id
			where
				UCom.Lpu_id = :Lpu_id
				and UCom.UslugaComplex_id = :UslugaComplex_id
		";

		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadEvnUslugaEditForm($data) {
		$query = '';
		$queryParams = array(
			'id' => $data['id'],
			'Lpu_id' => $data['Lpu_id']
		);

		switch ($data['class']) {
			case 'EvnUslugaOnkoSurg':
				$this->load->model('EvnUslugaOnkoSurg_model', 'EvnUslugaOnkoSurg');
				$this->EvnUslugaOnkoSurg->setId($data['id']);
				return $this->EvnUslugaOnkoSurg->load();
				break;
			case 'EvnUslugaOnkoBeam':
				$this->load->model('EvnUslugaOnkoBeam_model', 'EvnUslugaOnkoBeam');
				$this->EvnUslugaOnkoBeam->setId($data['id']);
				return $this->EvnUslugaOnkoBeam->load();
				break;
            case 'EvnUslugaOnkoChem':
	            $this->load->model('EvnUslugaOnkoChem_model', 'EvnUslugaOnkoChem');
	            $this->EvnUslugaOnkoChem->setId($data['id']);
	            return $this->EvnUslugaOnkoChem->load();
                break;
            case 'EvnUslugaOnkoGormun':
	            $this->load->model('EvnUslugaOnkoGormun_model', 'EvnUslugaOnkoGormun');
	            $this->EvnUslugaOnkoGormun->setId($data['id']);
	            return $this->EvnUslugaOnkoGormun->load();
                break;
            case 'EvnUslugaOnkoNonSpec':
	            $this->load->model('EvnUslugaOnkoNonSpec_model', 'EvnUslugaOnkoNonSpec');
	            $this->EvnUslugaOnkoNonSpec->setId($data['id']);
	            return $this->EvnUslugaOnkoNonSpec->load();
                break;
            default:
				$this->load->helper('MedStaffFactLink');
				$med_personal_list = getMedPersonalListWithLinks();
				//$data['class'] = 'EvnUslugaOper';
                switch ( $data['class'] ) {
        			case 'EvnUslugaCommon':
						$accessAdd = "1=1";
						if ( !($this->getRegionNick() == 'perm' || ($this->getRegionNick() == 'ufa' && isSuperadmin())) ) {
							$accessAdd .= " and ISNULL(EV.EvnVizit_IsPaid, 1) = 1 and ISNULL(ES.EvnSection_IsPaid, 1) = 1 ";
						}
						$accessAdd .= '
							and case
								when EUC.Lpu_id = :Lpu_id then 1
								' . (count($data['session']['linkedLpuIdList']) > 1 ? 'when EUC.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and ISNULL(EUC.EvnUslugaCommon_IsTransit, 1) = 2 then 1' : '') . '
								else 0
							end = 1
						';
						$accessType = "case when ({$accessAdd} " . ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 ? "and (ISNULL(EV.MedPersonal_id, ES.MedPersonal_id) in (".implode(',',$med_personal_list).")
							or exists(
										select top 1 WG.WorkGraph_id
											from v_WorkGraph WG (nolock)
											inner join v_MedStaffFact WG_MSF (nolock) on WG_MSF.MedStaffFact_id = WG.MedStaffFact_id
											where (
												CAST(WG.WorkGraph_begDT as date) <= CAST(dbo.tzGetDate() as date)
												and CAST(WG.WorkGraph_endDT as date) >= CAST(dbo.tzGetDate() as date)
											)
											and WG_MSF.MedPersonal_id in (".implode(',',$med_personal_list).")
									)
							or (ES.MedPersonal_id is null and EV.MedPersonal_id is null)
							or EUC.MedPersonal_id in (".implode(',',$med_personal_list)."))
							" : "") . "
							)
							then 'edit' else 'view' end as accessType";
        				$query = "
        					SELECT TOP 1
        						 {$accessType}
        						,EUC.EvnUslugaCommon_id
        						,EUC.EvnUslugaCommon_pid
        						,convert(varchar(10), EPS.EvnPS_setDate, 104) + ' / ' + LS.LpuSection_Name + ' / ' + MP.Person_Fio as EvnUslugaCommon_pid_Name
        						,EUC.EvnUslugaCommon_pid as EvnUslugaCommon_rid
        						,EUC.Person_id
        						,EUC.PersonEvn_id
        						,EUC.Server_id
        						,convert(varchar(10), EUC.EvnUslugaCommon_setDate, 104) as EvnUslugaCommon_setDate
        						,ISNULL(EUC.EvnUslugaCommon_setTime, '') as EvnUslugaCommon_setTime
        						,convert(varchar(10), EUC.EvnUslugaCommon_disDate, 104) as EvnUslugaCommon_disDate
        						,ISNULL(EUC.EvnUslugaCommon_disTime, '') as EvnUslugaCommon_disTime
        						,EUC.UslugaPlace_id
        						,EUC.Lpu_uid
        						,EUC.Org_uid
        						,org.Org_Name
        						,EUC.LpuSection_uid
        						,EUC.MedPersonal_id
        						,EUC.MedStaffFact_id
        						,EUC.Usluga_id
        						,EUC.UslugaComplex_id
        						,EUC.PayType_id
        						,ROUND(ISNULL(EUC.EvnUslugaCommon_Kolvo, 0), 2) as EvnUslugaCommon_Kolvo
								,ROUND(ISNULL(EUC.EvnUslugaCommon_Price, 0), 2) as UslugaComplexTariff_UED
								,ROUND(ISNULL(EUC.EvnUslugaCommon_Summa, 0), 2) as EvnUslugaCommon_Summa
        						,EUC.EvnUslugaCommon_CoeffTariff
        						,EUC.EvnUslugaCommon_IsModern
        						,EUC.EvnUslugaCommon_IsMinusUsluga
        						,EUC.MesOperType_id
        						,EUC.UslugaComplexTariff_id
        						,EUC.DiagSetClass_id
        						,EUC.Diag_id
        						,EUC.EvnPrescr_id
        						,EUC.EvnPrescrTimetable_id
        						,EUC.MedSpecOms_id
        						,EUC.LpuSectionProfile_id
        						,EUC.EvnDirection_id
        						,EUC.UslugaExecutionType_id
        						,EUC.UslugaExecutionReason_id
        						,UC.UslugaCategory_id
								,convert(varchar(10), EUC.EvnUslugaCommon_setDT, 120) as EvnUslugaCommon_setDT
								,convert(varchar(10), EUC.EvnUslugaCommon_disDT, 120) as EvnUslugaCommon_disDT
        					FROM
        						v_EvnUslugaCommon EUC with (nolock)
        						left join EvnVizit EV with (nolock) on EV.EvnVizit_id = EUC.EvnUslugaCommon_pid
        						left join EvnSection ES with (nolock) on ES.EvnSection_id = EUC.EvnUslugaCommon_pid
								left join v_UslugaComplexTariff UCT with (nolock) on UCT.UslugaComplexTariff_id = EUC.UslugaComplexTariff_id
								left join v_EvnPS EPS with (nolock) on EPS.EvnPS_id = EUC.EvnUslugaCommon_pid
								left join v_LpuSection LS with (nolock) on LS.LpuSection_id = EPS.LpuSection_pid
								left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EPS.MedPersonal_pid
								left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EUC.UslugaComplex_id
								left join v_Org org (nolock) on org.Org_id = EUC.Org_uid
        					WHERE
        						EUC.EvnUslugaCommon_id = :id
        				";
        			break;

        			case 'EvnUslugaOper':
						$accessAdd = "1=1";
						if ( !($this->getRegionNick() == 'perm' || ($this->getRegionNick() == 'ufa' && isSuperadmin())) ) {
							$accessAdd .= " and ISNULL(EV.EvnVizit_IsPaid, 1) = 1 and ISNULL(ES.EvnSection_IsPaid, 1) = 1 ";
						}

						$accessAdd .= '
							and case
								when EUO.Lpu_id = :Lpu_id then 1
								' . (count($data['session']['linkedLpuIdList']) > 1 ? 'when EUO.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and ISNULL(EUO.EvnUslugaOper_IsTransit, 1) = 2 then 1' : '') . '
								else 0
							end = 1
						';

						$filterMS = "";
						if (!empty($data['session']['CurMedService_id'])) { // если открываем из службы по направлению
							$filterMS = " or ed.MedService_id = :CurMedService_id";
							$queryParams['CurMedService_id'] = $data['session']['CurMedService_id'];
						}
						//$accessType = "case when {$accessAdd} " . ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 ? "and (EUO.MedPersonal_id in (".implode(',',$med_personal_list).") {$filterMS})" : "") . " then 'edit' else 'view' end as accessType,";
						$accessType = "case when ({$accessAdd} " . ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 ? "and (EUO.MedPersonal_id in (".implode(',',$med_personal_list).") {$filterMS}
							or exists(
										select top 1 WG.WorkGraph_id
											from v_WorkGraph WG (nolock)
											inner join v_MedStaffFact WG_MSF (nolock) on WG_MSF.MedStaffFact_id = WG.MedStaffFact_id
											where (
												CAST(WG.WorkGraph_begDT as date) <= CAST(dbo.tzGetDate() as date)
												and CAST(WG.WorkGraph_endDT as date) >= CAST(dbo.tzGetDate() as date)
											)
											and WG_MSF.MedPersonal_id in (".implode(',',$med_personal_list).")
									)
							)" : "") . ")
							then 'edit' else 'view' end as accessType";
        				$query = "
        					SELECT TOP 1
        						{$accessType},
        						EUO.EvnDirection_id,
        						EUO.EvnUslugaOper_id,
								EUO.EvnUslugaOper_IsVMT,
								EUO.EvnUslugaOper_IsMicrSurg,
								EUO.EvnUslugaOper_IsOpenHeart,
								EUO.EvnUslugaOper_IsArtCirc,
        						EUO.EvnUslugaOper_pid,
        						convert(varchar(10), EPS.EvnPS_setDate, 104) + ' / ' + LS.LpuSection_Name + ' / ' + MP.Person_Fio as EvnUslugaCommon_pid_Name,
        						EUO.EvnUslugaOper_pid as EvnUslugaOper_rid,
        						EUO.Person_id,
        						EUO.PersonEvn_id,
        						EUO.Server_id,
        						convert(varchar(10), EUO.EvnUslugaOper_setDate, 104) as EvnUslugaOper_setDate,
        						ISNULL(EUO.EvnUslugaOper_setTime, '') as EvnUslugaOper_setTime,
        						convert(varchar(10), EUO.EvnUslugaOper_disDate, 104) as EvnUslugaOper_disDate,
        						ISNULL(EUO.EvnUslugaOper_disTime, '') as EvnUslugaOper_disTime,
        						EUO.UslugaPlace_id,
        						EUO.Lpu_uid,
        						EUO.Org_uid,
        						EUO.LpuSection_uid,
        						EUO.MedPersonal_id,
        						isnull(EUO.MedStaffFact_id, MSF.MedStaffFact_id) as MedStaffFact_id,
        						EUO.Morbus_id,
        						EUO.Usluga_id,
        						UC.UslugaCategory_id,
        						EUO.UslugaComplex_id,
        						EUO.PayType_id,
        						EUO.OperType_id,
        						EUO.OperDiff_id,
        						EUO.TreatmentConditionsType_id,
        						isnull(EUO.EvnUslugaOper_IsEndoskop, 1) as EvnUslugaOper_IsEndoskop,
        						isnull(EUO.EvnUslugaOper_IsLazer, 1) as EvnUslugaOper_IsLazer,
        						isnull(EUO.EvnUslugaOper_IsKriogen, 1) as EvnUslugaOper_IsKriogen,
        						isnull(EUO.EvnUslugaOper_IsRadGraf, 1) as EvnUslugaOper_IsRadGraf,
        						ROUND(ISNULL(EUO.EvnUslugaOper_Kolvo, 0), 2) as EvnUslugaOper_Kolvo,
        						EUO.EvnUslugaOper_CoeffTariff,
        						EUO.EvnUslugaOper_IsModern,
        						EUO.MesOperType_id,
								EUO.UslugaComplexTariff_id,
								EUO.UslugaExecutionType_id,
        						EUO.UslugaExecutionReason_id,
								EUO.DiagSetClass_id,
								EUO.Diag_id
        						,EUO.EvnPrescr_id
        						,EUO.EvnPrescrTimetable_id
        						,EUO.MedSpecOms_id
        						,EUO.LpuSectionProfile_id
								,convert(varchar(10), EUO.EvnUslugaOper_BallonBegDT, 120) as EvnUslugaOper_BallonBegDate
								,convert(varchar(5), EUO.EvnUslugaOper_BallonBegDT, 108) as EvnUslugaOper_BallonBegTime
								,convert(varchar(10), EUO.EvnUslugaOper_CKVEndDT, 120) as EvnUslugaOper_CKVEndDate
								,convert(varchar(5), EUO.EvnUslugaOper_CKVEndDT, 108) as EvnUslugaOper_CKVEndTime
								,EUO.EvnUslugaOper_IsOperationDeath
							  	--,case when EUO.EvnUslugaOper_IsOperationDeath = 2 then 1 else 0 end as EvnUslugaOper_IsOperationDeath
								,e.EvnClass_SysNick as parentEvnClass_SysNick
								,convert(varchar(10), EUO.EvnUslugaOper_setDT, 120) as EvnUslugaOper_setDT
								,convert(varchar(10), EUO.EvnUslugaOper_disDT, 120) as EvnUslugaOper_disDT
        					FROM
        						v_EvnUslugaOper EUO with (nolock)
        						left join v_Evn e with (nolock) on e.Evn_id = EUO.EvnUslugaOper_pid
        						left join EvnVizit EV with (nolock) on EV.EvnVizit_id = EUO.EvnUslugaOper_pid
        						left join EvnSection ES with (nolock) on ES.EvnSection_id = EUO.EvnUslugaOper_pid
								left join v_EvnPS EPS with (nolock) on EPS.EvnPS_id = EUO.EvnUslugaOper_pid
								left join v_LpuSection LS with (nolock) on LS.LpuSection_id = EPS.LpuSection_pid
								left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EPS.MedPersonal_pid
								left join v_EvnDirection_all ed with (nolock) on ed.EvnDirection_id = EUO.EvnDirection_id
								left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EUO.UslugaComplex_id
								outer apply (
									select top 1 MedStaffFact_id
									from v_MedStaffFact with(nolock)
									where MedPersonal_id = EUO.MedPersonal_id and LpuSection_id = EUO.LpuSection_uid
								) MSF
        					WHERE EUO.EvnUslugaOper_id = :id
        				";
        			break;

        			case 'EvnUslugaStom':
						$accessAdd = "1=1";
						if ( !($this->getRegionNick() == 'perm' || ($this->getRegionNick() == 'ufa' && isSuperadmin())) ) {
							$accessAdd .= " and ISNULL(EV.EvnVizit_IsPaid, 1) = 1 and ISNULL(ES.EvnSection_IsPaid, 1) = 1 ";
						}

						$accessAdd .= '
							and case
								when EUS.Lpu_id = :Lpu_id then 1
								' . (count($data['session']['linkedLpuIdList']) > 1 ? 'when EUS.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and ISNULL(EUS.EvnUslugaStom_IsTransit, 1) = 2 then 1' : '') . '
								else 0
							end = 1
						';

        				$query = "
        					SELECT TOP 1
        						case when {$accessAdd} " . ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 ? "and EUS.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . " then 'edit' else 'view' end as accessType,
        						EUS.EvnUslugaStom_id,
        						EUS.EvnUslugaStom_rid,
        						EUS.EvnUslugaStom_pid,
        						EUS.EvnDiagPLStom_id,
        						ISNULL(EUS.EvnUslugaStom_IsAllMorbus, 1) as EvnUslugaStom_IsAllMorbus,
        						ISNULL(EUS.EvnUslugaStom_IsMes, 1) as EvnUslugaStom_IsMes,
        						convert(varchar(10), EPS.EvnPS_setDate, 104) + ' / ' + LS.LpuSection_Name + ' / ' + MP.Person_Fio as EvnUslugaCommon_pid_Name,
        						EUS.Person_id,
        						EUS.PersonEvn_id,
        						EUS.Server_id,
        						convert(varchar(10), EUS.EvnUslugaStom_setDate, 104) as EvnUslugaStom_setDate,
        						ISNULL(EUS.EvnUslugaStom_setTime, '') as EvnUslugaStom_setTime,
        						convert(varchar(10), EUS.EvnUslugaStom_disDate, 104) as EvnUslugaStom_disDate,
        						ISNULL(EUS.EvnUslugaStom_disTime, '') as EvnUslugaStom_disTime,
        						EUS.LpuSection_uid,
        						EUS.LpuSectionProfile_id,
        						EUS.MedStaffFact_id,
        						EUS.MedPersonal_id,
        						EUS.MedPersonal_sid,
        						EUS.Usluga_id,
        						EUS.UslugaComplex_id,
        						EUS.LpuDispContract_id,
        						UC.UslugaCategory_id,
        						EUS.PayType_id,
								ROUND(ISNULL(EUS.EvnUslugaStom_UED, 0), 2) as EvnUslugaStom_UED,
								ROUND(ISNULL(EUS.EvnUslugaStom_UEM, 0), 2) as EvnUslugaStom_UEM,
        						ROUND(ISNULL(EUS.EvnUslugaStom_Kolvo, 0), 2) as EvnUslugaStom_Kolvo,
        						ROUND(ISNULL(EUS.EvnUslugaStom_Summa, 0), 2) as EvnUslugaStom_Summa,
								EUS.UslugaComplexTariff_id
        						,EUS.EvnPrescr_id
        						,EUS.EvnPrescrTimetable_id
        						,EUS.BlackCariesClass_id
        					FROM
        						v_EvnUslugaStom EUS with (nolock)
        						left join EvnVizit EV with (nolock) on EV.EvnVizit_id = EUS.EvnUslugaStom_pid
        						left join EvnSection ES with (nolock) on ES.EvnSection_id = EUS.EvnUslugaStom_pid
								left join v_UslugaComplexTariff UCT with (nolock) on UCT.UslugaComplexTariff_id = EUS.UslugaComplexTariff_id
								left join v_EvnPS EPS with (nolock) on EPS.EvnPS_id = EUS.EvnUslugaStom_pid
								left join v_LpuSection LS with (nolock) on LS.LpuSection_id = EPS.LpuSection_pid
								left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EPS.MedPersonal_pid
								left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EUS.UslugaComplex_id
        					WHERE EUS.EvnUslugaStom_id = :id
        				";
        			break;

        			default:
        				return false;
        			break;
        		}
				//echo getDebugSQL($query, $queryParams); exit;
        		$result = $this->db->query($query, $queryParams);

        		if ( is_object($result) ) {
					$resp = $result->result('array');
					if (!empty($resp[0]) && $data['class'] == 'EvnUslugaOper') {
						$resp[0]['parentEvnComboData'] = array();
						if (!empty($resp[0]['EvnUslugaOper_pid'])) {
							// грузим для услуги родительские события
							$from = "
								v_EvnSection es (nolock)
								inner join v_EvnSection es2 (nolock) on es2.EvnSection_pid = es.EvnSection_pid
							";
							$where = "es.EvnSection_id = :EvnUslugaOper_pid";
							if (!empty($resp[0]['parentEvnClass_SysNick']) && $resp[0]['parentEvnClass_SysNick'] == 'EvnPS') {
								$from = "
									v_EvnPS eps (nolock)
									inner join v_EvnSection es2 (nolock) on es2.EvnSection_pid = eps.EvnPS_id
								";
								$where = "eps.EvnPS_id = :EvnUslugaOper_pid";
							}
							$resp[0]['parentEvnComboData'] = $this->queryResult("
								select
									es2.EvnSection_id as Evn_id,
									IsNull(convert(varchar,cast(es2.EvnSection_setDT as datetime),104),'') +' / ' + isnull(LpuSection.LpuSection_Name,'') + ' / ' + isnull(msf.Person_FIO,'') as Evn_Name,
									convert(varchar(10), es2.EvnSection_setDT, 104) as Evn_setDate,
									convert(varchar(10), es2.EvnSection_disDT, 104) as Evn_disDate,
									es2.EvnSection_IsPriem as IsPriem,
									es2.Diag_id
								from
									{$from}
									left join v_LpuSection LpuSection with (nolock) on LpuSection.LpuSection_id = es2.LpuSection_id
									left join v_MedStaffFact Msf with (nolock) on Msf.MedStaffFact_id = es2.MedStaffFact_id
								where
									{$where}
							", array(
								'EvnUslugaOper_pid' => $resp[0]['EvnUslugaOper_pid']
							));

							$this->load->model('EvnPS_model');
							if (!in_array(getRegionNick(), $this->EvnPS_model->getListRegionNickWithEvnSectionPriem())) {
								// приёмное движение - КВС
								$from = "
									v_EvnSection es (nolock)
									inner join v_EvnPS eps (nolock) on eps.EvnPS_id = es.EvnSection_pid
								";
								$where = "es.EvnSection_id = :EvnUslugaOper_pid";
								if (!empty($resp[0]['parentEvnClass_SysNick']) && $resp[0]['parentEvnClass_SysNick'] == 'EvnPS') {
									$from = "
										v_EvnPS eps (nolock)
									";
									$where = "eps.EvnPS_id = :EvnUslugaOper_pid";
								}

								$resp_eps = $this->queryResult("
									select
										eps.EvnPS_id as Evn_id,
										IsNull(convert(varchar,cast(eps.EvnPS_setDT as datetime),104),'') +' / ' + isnull(LpuSection.LpuSection_Name,'') + ' / ' + isnull(msf.Person_FIO,'') as Evn_Name,
										convert(varchar(10), eps.EvnPS_setDT, 104) as Evn_setDate,
										convert(varchar(10), eps.EvnPS_OutcomeDT, 104) as Evn_disDate,
										2 as IsPriem,
										eps.Diag_pid as Diag_id
									from
										{$from}
										left join v_LpuSection LpuSection with (nolock) on LpuSection.LpuSection_id = eps.LpuSection_pid
										left join v_MedStaffFact Msf with (nolock) on Msf.MedStaffFact_id = eps.MedStaffFact_pid
									where
										{$where}
								", array(
									'EvnUslugaOper_pid' => $resp[0]['EvnUslugaOper_pid']
								));

								if (!empty($resp_eps[0])) {
									$resp[0]['parentEvnComboData'][] = $resp_eps[0];
								}
							}
						}
					}

					if (!empty($resp[0]) && $this->regionNick === 'kz') {
					    if ($data['class'] === 'EvnUslugaStom') {
					        $Evn_id = $resp[0]['EvnUslugaStom_id'];
                        } else if ($data['class'] === 'EvnUslugaCommon') {
                            $Evn_id = $resp[0]['EvnUslugaCommon_id'];
                        } else if ($data['class'] === 'EvnUslugaOper') {
                            $Evn_id = $resp[0]['EvnUslugaOper_id'];
                        }

					    if (isset($Evn_id)) {
                            $this->load->model('UslugaMedType_model');
                            $UslugaMedType_id = $this->UslugaMedType_model->getUslugaMedTypeIdByEvnId($Evn_id);
                            if ($UslugaMedType_id) {
                                $resp[0]['UslugaMedType_id'] = $UslugaMedType_id;
                            }
                        }
                    }

					return $resp;
        		}
        		else {
        			return false;
        		}
			break;
        }
	}
	
	/**
	 * @param $data
	 * @return bool
	 */
	function loadEvnUslugaParSimpleEditForm($data) {
		$queryParams = array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
			'Lpu_id' => $data['Lpu_id']
		);

		$accessAdd = "";
		if ( !($this->getRegionNick() == 'perm' || ($this->getRegionNick() == 'ufa' && isSuperadmin())) ) {
			// случай, к которому она привязана не оплачен
			$accessAdd = " and ISNULL(EV.EvnVizit_IsPaid, 1) = 1 and ISNULL(ES.EvnSection_IsPaid, 1) = 1 ";
		}

		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();
		// текущий врач работает в отделении,  которое указано в движении/посещении, в котором сделано назначение, на основе которого выполнена данная услуга или пользователь статистик.
		$accessAdd .= ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 ? "and (ISNULL(EV2.MedPersonal_id, ES2.MedPersonal_id) in (".implode(',',$med_personal_list).") or (EV.MedPersonal_id is null and ES.MedPersonal_id is null ))" : "");

		$query = "
			SELECT TOP 1
				case when EUP.Lpu_id = :Lpu_id {$accessAdd} then 'edit' else 'view' end as accessType,
				EUP.EvnUslugaPar_id,
				EUP.EvnUslugaPar_pid,
				EUP.EvnUslugaPar_pid as EvnUslugaPar_rid,
				EUP.Person_id,
				EUP.PersonEvn_id,
				EUP.Server_id,
				EUP.EvnDirection_id,
				convert(varchar(10), EUP.EvnUslugaPar_setDate, 104) as EvnUslugaPar_setDate,
				ISNULL(EUP.EvnUslugaPar_setTime, '') as EvnUslugaPar_setTime,
				EUP.UslugaPlace_id,
				EUP.Lpu_uid,
				EUP.Org_uid,
				EUP.LpuSection_uid,
				EUP.MedStaffFact_id,
				EUP.Morbus_id,
				EUP.Usluga_id,
				EUP.UslugaComplex_id,
				EUP.PayType_id,
				ROUND(ISNULL(EUP.EvnUslugaPar_Kolvo, 0), 2) as EvnUslugaPar_Kolvo,
				EUP.EvnUslugaPar_CoeffTariff,
				EUP.EvnUslugaPar_IsModern,
				EUP.MesOperType_id,
				EUP.UslugaComplexTariff_id
				,EUP.EvnPrescr_id
				,EUP.EvnPrescrTimetable_id
				,e.EvnClass_SysNick as parentClass
			FROM
				v_EvnUslugaPar EUP with (nolock)
				inner join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = eup.EvnDirection_id
				left join v_Evn e (nolock) on e.Evn_id = ed.EvnDirection_pid
				left join EvnVizit EV with (nolock) on EV.EvnVizit_id = EUP.EvnUslugaPar_pid
				left join EvnSection ES with (nolock) on ES.EvnSection_id = EUP.EvnUslugaPar_pid
				left join EvnVizit EV2 with (nolock) on EV2.EvnVizit_id = ed.EvnDirection_pid
				left join EvnSection ES2 with (nolock) on ES2.EvnSection_id = ed.EvnDirection_pid
			WHERE (1 = 1)
				and EUP.EvnUslugaPar_id = :EvnUslugaPar_id
		";

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (!empty($resp[0])) {
				$resp[0]['parentEvnComboData'] = array();
				if (!empty($resp[0]['EvnDirection_id'])) {
					// грузим для услуги родительские события
					$resp[0]['parentEvnComboData'] = $this->queryResult("
						select
							epl2.EvnVizitPL_id as Evn_id,
							IsNull(convert(varchar,cast(epl2.EvnVizitPL_setDT as datetime),104),'') +' / ' + isnull(LpuSection.LpuSection_Name,'') + ' / ' + isnull(msf.Person_FIO,'') as Evn_Name,
							convert(varchar(10), epl2.EvnVizitPL_setDT, 104) as Evn_setDate,
							convert(varchar(10), epl2.EvnVizitPL_disDT, 104) as Evn_disDate
						from
							v_EvnDirection_all ed (nolock)
							inner join v_EvnVizitPL epl (nolock) on epl.EvnVizitPL_id = ed.EvnDirection_pid
							inner join v_EvnVizitPL epl2 (nolock) on epl2.EvnVizitPL_pid = epl.EvnVizitPL_pid
							left join v_LpuSection LpuSection with (nolock) on LpuSection.LpuSection_id = epl2.LpuSection_id
							left join v_MedStaffFact Msf with (nolock) on Msf.MedStaffFact_id = epl2.MedStaffFact_id
						where
							ed.EvnDirection_id = :EvnDirection_id
							
						union all
						
						select
							es2.EvnSection_id as Evn_id,
							IsNull(convert(varchar,cast(es2.EvnSection_setDT as datetime),104),'') +' / ' + isnull(LpuSection.LpuSection_Name,'') + ' / ' + isnull(msf.Person_FIO,'') as Evn_Name,
							convert(varchar(10), es2.EvnSection_setDT, 104) as Evn_setDate,
							convert(varchar(10), es2.EvnSection_disDT, 104) as Evn_disDate
						from
							v_EvnDirection_all ed (nolock)
							inner join v_EvnSection es (nolock) on es.EvnSection_id = ed.EvnDirection_pid
							inner join v_EvnSection es2 (nolock) on es2.EvnSection_pid = es.EvnSection_pid
							inner join v_LpuSection LpuSection with (nolock) on LpuSection.LpuSection_id = es2.LpuSection_id -- движения только с отделением
							left join v_MedStaffFact Msf with (nolock) on Msf.MedStaffFact_id = es2.MedStaffFact_id
						where
							ed.EvnDirection_id = :EvnDirection_id
							
						union all
						
						select
							es2.EvnSection_id as Evn_id,
							IsNull(convert(varchar,cast(es2.EvnSection_setDT as datetime),104),'') +' / ' + isnull(LpuSection.LpuSection_Name,'') + ' / ' + isnull(msf.Person_FIO,'') as Evn_Name,
							convert(varchar(10), es2.EvnSection_setDT, 104) as Evn_setDate,
							convert(varchar(10), es2.EvnSection_disDT, 104) as Evn_disDate
						from
							v_EvnDirection_all ed (nolock)
							inner join v_EvnSection es2 (nolock) on es2.EvnSection_pid = ed.EvnDirection_pid
							left join v_LpuSection LpuSection with (nolock) on LpuSection.LpuSection_id = es2.LpuSection_id
							left join v_MedStaffFact Msf with (nolock) on Msf.MedStaffFact_id = es2.MedStaffFact_id
						where
							ed.EvnDirection_id = :EvnDirection_id
					", array(
						'EvnDirection_id' => $resp[0]['EvnDirection_id']
					));
				}
			}
			return $resp;
		}
		else {
			return false;
		}
	}

	/**
	 * Получение дополнителных данных для формы редактирования
	 */
	function loadEvnUslugaParSimpleEditFormAdditData($data) {
		$params = array(
			'EvnUslugaPar_pid' => $data['EvnUslugaPar_pid'],
			'EvnDirection_pid' => !empty($data['EvnDirection_pid'])?$data['EvnDirection_pid']:null,
			'Lpu_oid' => $data['Lpu_oid'],
			'Lpu_id' => $data['Lpu_id'],
		);

		$accessAdd = "";
		if ( !($this->getRegionNick() == 'perm' || ($this->getRegionNick() == 'ufa' && isSuperadmin())) ) {
			// случай, к которому она привязана не оплачен
			$accessAdd = " and coalesce(EV.EvnVizit_IsPaid, 1) = 1 and ISNULL(ES.EvnSection_IsPaid, 1) = 1 ";
		}

		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();
		// текущий врач работает в отделении,  которое указано в движении/посещении, в котором сделано назначение, на основе которого выполнена данная услуга или пользователь статистик.
		$accessAdd .= ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 ? "and (coalesce(EV2.MedPersonal_id, ES2.MedPersonal_id) in (".implode(',',$med_personal_list).") or (EV.MedPersonal_id is null and ES.MedPersonal_id is null ))" : "");

		$query = "
			select top 1
				case when EUP.Lpu_id = :Lpu_id {$accessAdd} then 'edit' else 'view' end as accessType,
				EUP.EvnDirection_pid,
				e.EvnClass_SysNick as parentClass
			from
				(select 
					:EvnUslugaPar_pid as EvnUslugaPar_pid,
					:EvnDirection_pid as EvnDirection_pid,
					:Lpu_oid as Lpu_id
				) EUP
				left join v_Evn e with(nolock) on e.Evn_id = EUP.EvnDirection_pid
				left join EvnVizit EV with(nolock) on EV.EvnVizit_id = EUP.EvnUslugaPar_pid
				left join EvnSection ES with(nolock) on ES.EvnSection_id = EUP.EvnUslugaPar_pid
				left join EvnVizit EV2 with(nolock) on EV2.EvnVizit_id = EUP.EvnDirection_pid
				left join EvnSection ES2 with(nolock) on ES2.EvnSection_id = EUP.EvnDirection_pid
		";

		//echo getDebugSQL($query, $params);exit;
		$resp = $this->queryResult($query, $params);
		if ($resp === false) {
			return false;
		}

		$resp[0]['parentEvnComboData'] = array();
		if (!empty($resp[0]['EvnDirection_pid'])) {
			// грузим для услуги родительские события
			$resp[0]['parentEvnComboData'] = $this->queryResult("
				select
					epl2.EvnVizitPL_id as Evn_id,
					IsNull(convert(varchar,cast(epl2.EvnVizitPL_setDT as datetime),104),'') +' / ' + isnull(LpuSection.LpuSection_Name,'') + ' / ' + isnull(msf.Person_FIO,'') as Evn_Name,
					convert(varchar(10), epl2.EvnVizitPL_setDT, 104) as Evn_setDate,
					convert(varchar(10), epl2.EvnVizitPL_disDT, 104) as Evn_disDate
				from
					(select :EvnDirection_pid as EvnDirection_pid) ed
					inner join v_EvnVizitPL epl (nolock) on epl.EvnVizitPL_id = ed.EvnDirection_pid
					inner join v_EvnVizitPL epl2 (nolock) on epl2.EvnVizitPL_pid = epl.EvnVizitPL_pid
					left join v_LpuSection LpuSection with (nolock) on LpuSection.LpuSection_id = epl2.LpuSection_id
					left join v_MedStaffFact Msf with (nolock) on Msf.MedStaffFact_id = epl2.MedStaffFact_id

				union all

				select
					es2.EvnSection_id as Evn_id,
					IsNull(convert(varchar,cast(es2.EvnSection_setDT as datetime),104),'') +' / ' + isnull(LpuSection.LpuSection_Name,'') + ' / ' + isnull(msf.Person_FIO,'') as Evn_Name,
					convert(varchar(10), es2.EvnSection_setDT, 104) as Evn_setDate,
					convert(varchar(10), es2.EvnSection_disDT, 104) as Evn_disDate
				from
					(select :EvnDirection_pid as EvnDirection_pid) ed
					inner join v_EvnSection es (nolock) on es.EvnSection_id = ed.EvnDirection_pid
					inner join v_EvnSection es2 (nolock) on es2.EvnSection_pid = es.EvnSection_pid
					inner join v_LpuSection LpuSection with (nolock) on LpuSection.LpuSection_id = es2.LpuSection_id -- движения только с отделением
					left join v_MedStaffFact Msf with (nolock) on Msf.MedStaffFact_id = es2.MedStaffFact_id

				union all

				select
					es2.EvnSection_id as Evn_id,
					IsNull(convert(varchar,cast(es2.EvnSection_setDT as datetime),104),'') +' / ' + isnull(LpuSection.LpuSection_Name,'') + ' / ' + isnull(msf.Person_FIO,'') as Evn_Name,
					convert(varchar(10), es2.EvnSection_setDT, 104) as Evn_setDate,
					convert(varchar(10), es2.EvnSection_disDT, 104) as Evn_disDate
				from
					(select :EvnDirection_pid as EvnDirection_pid) ed
					inner join v_EvnSection es2 (nolock) on es2.EvnSection_pid = ed.EvnDirection_pid
					left join v_LpuSection LpuSection with (nolock) on LpuSection.LpuSection_id = es2.LpuSection_id
					left join v_MedStaffFact Msf with (nolock) on Msf.MedStaffFact_id = es2.MedStaffFact_id
			", array(
				'EvnDirection_pid' => $resp[0]['EvnDirection_pid']
			));
		}

		return $resp;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadEvnUslugaGrid($data) {
		$response = array();

		if ($this->usePostgreLis && empty($data['byMorbus']) && (empty($data['class']) || in_array($data['class'], array('EvnUsluga','EvnUslugaCommon','EvnUslugaPar')))) {
			if (!empty($data['pid']) && isset($data['parent']) && in_array($data['parent'], array('EvnPL','EvnPLStom','EvnPS'))) {
				$data['pid_list'] = $this->queryList("
					select Evn_id from v_Evn with(nolock) where Evn_pid = :pid
				", $data);
			}

			$this->load->swapi('lis');
			$response = $this->lis->GET('EvnUsluga/Grid', $data, 'list');
			if (!$this->isSuccessful($response)) {
				return false;
			}
		}

		$resp = $this->_loadEvnUslugaGrid($data, $response);
		if (!is_array($resp)) {
			return false;
		}

		return array_merge($response, $resp);
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function _loadEvnUslugaGrid($data, $excepts = array()) {
		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();
		$with_clause = "";
		$accessAdd = "1=1";
		$union = "";
		if ( !($this->getRegionNick() == 'perm' || ($this->getRegionNick() == 'ufa' && isSuperadmin())) ) {
			$accessAdd .= " and ISNULL(EV.EvnVizit_IsPaid, 1) = 1 and ISNULL(ES.EvnSection_IsPaid, 1) = 1 ";
		}

		$allowVizitCode = (!empty($data['allowVizitCode']) && $data['allowVizitCode'] == 1);

		$accessAdd .= '
			and case
				when EU.Lpu_id = :Lpu_id then 1
				' . (array_key_exists('linkedLpuIdList', $data['session']) && count($data['session']['linkedLpuIdList']) > 1 ? 'when EU.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and ISNULL(EU.' . (in_array($data['class'], array('EvnUslugaStom')) ? $data['class'] : 'EvnUsluga') . '_IsTransit, 1) = 2 then 1' : '') . '
				when :isMedStatUser = 1 then 1
				else 0
			end = 1
		';

        //$accessType = "case when {$accessAdd} " . ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 ? "and (ISNULL(EV.MedPersonal_id, ES.MedPersonal_id) in (".implode(',',$med_personal_list).")or (ES.MedPersonal_id is null and EV.MedPersonal_id is null))" : "") . " then 'edit' else 'view' end as accessType,";
		$accessType = "case when {$accessAdd} " . ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 ? "and (ISNULL(EV.MedPersonal_id, ES.MedPersonal_id) in (".implode(',',$med_personal_list).")
		or exists(
					select top 1 WG.WorkGraph_id
						from v_WorkGraph WG (nolock)
						inner join v_MedStaffFact WG_MSF (nolock) on WG_MSF.MedStaffFact_id = WG.MedStaffFact_id
						where (
							CAST(WG.WorkGraph_begDT as date) <= CAST(dbo.tzGetDate() as date)
							and CAST(WG.WorkGraph_endDT as date) >= CAST(dbo.tzGetDate() as date)
						)
						and WG_MSF.MedPersonal_id in (".implode(',',$med_personal_list).")
			 	)
		or (ES.MedPersonal_id is null and EV.MedPersonal_id is null))" : "") . " then 'edit' else 'view' end as accessType,";

		if (isset($data['accessType']) && $data['accessType'] == 'view') {
			$accessType  = " 'view' as accessType,";
		}
		
        $addSelectclause = '';
        $select_clause = "
             EU.EvnUsluga_id
            ,EU.EvnUsluga_pid
            ,RTRIM(EU.EvnClass_SysNick) as EvnClass_SysNick
            ,convert(varchar(10), EU.EvnUsluga_setDate, 104) as EvnUsluga_setDate
            ,ISNULL(EU.EvnUsluga_setTime, '') as EvnUsluga_setTime
			,ISNULL(Usluga.Usluga_Code, UC.UslugaComplex_Code) as Usluga_Code
			,ISNULL(Usluga.Usluga_Name, UC.UslugaComplex_Name) as Usluga_Name
            ,ROUND(cast(EU.EvnUsluga_Kolvo as float), 2) as EvnUsluga_Kolvo
			,ROUND(cast(EU.EvnUsluga_Price as float), 2) as EvnUsluga_Price
			,ROUND(cast(EU.EvnUsluga_Summa as float), 2) as EvnUsluga_Summa
            ,PT.PayType_id
            ,ISNULL(PT.PayType_SysNick, '') as PayType_SysNick
        ";
        $from_clause = "
            v_EvnUsluga EU with (nolock)
            left join v_Evn EvnParent with(nolock) on EvnParent.Evn_id = EU.EvnUsluga_pid
            left join v_EvnPrescr EP with(nolock) on EP.EvnPrescr_id = EU.EvnPrescr_id
			left join v_Usluga Usluga with (nolock) on Usluga.Usluga_id = EU.Usluga_id
			left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
            left join v_EvnVizit EV with (nolock) on EV.EvnVizit_id = EU.EvnUsluga_pid
            left join v_EvnSection ES with (nolock) on ES.EvnSection_id = EU.EvnUsluga_pid
			left join v_PayType PT with (nolock) on PT.PayType_id = EU.PayType_id
        ";
		
		if (!empty($data['isMorbusOnko']) && $data['isMorbusOnko'] == 2) {
			$select_clause .= "
			,ISNULL(onkoucat.UslugaComplexAttributeType_Name, 'Неспецифическое лечение') as UslugaComplexAttributeType_Name
			";
			$from_clause .= "
			outer apply (
				select top 1 UslugaComplexAttributeType_SysNick, UslugaComplexAttributeType_Name
				from v_UslugaComplexAttribute UCA with (nolock)
				inner join v_UslugaComplexAttributeType UCAT with (nolock) on UCAT.UslugaComplexAttributeType_id = UCA.UslugaComplexAttributeType_id
				where UCA.UslugaComplex_id = EU.UslugaComplex_id and UslugaComplexAttributeType_SysNick in ('XimLech','LuchLech','GormImunTerLech','XirurgLech')
			) onkoucat
			";
		}

		if ( !empty($data['parent']) && $data['parent'] == 'EvnPLStom' ) {
			$from_clause .= "
				left join v_EvnUslugaStom EUS with (nolock) on EUS.EvnUslugaStom_id = EU.EvnUsluga_id
				left join v_EvnDiagPLStom EDPLS with (nolock) on EDPLS.EvnDiagPLStom_id = EUS.EvnDiagPLStom_id
				outer apply (
					select top 1 MesUsluga_IsNeedUsluga
					from v_MesUsluga with (nolock)
					where Mes_id = EDPLS.Mes_id
						and UslugaComplex_id = EU.UslugaComplex_id
				) MU
			";
			$select_clause .= "
				,case when EU.EvnUsluga_IsVizitCode = 2 then 'X' else '' end as EvnUsluga_IsVizitCode
				,case when EUS.EvnUslugaStom_IsAllMorbus = 2 then 'X' else '' end as EvnUsluga_IsAllMorbus
				,case when EUS.EvnUslugaStom_IsMes = 2 then 'X' else '' end as EvnUsluga_IsMes
				,case when MU.MesUsluga_IsNeedUsluga = 2 then 'X' else '' end as EvnUsluga_IsRequired
			";
		}

		// Параклинические услуги должны отображаться только выполненные
		// https://redmine.swan.perm.ru/issues/55296
		//$EvnClassFilter = "and EU.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaStom', /*'EvnUslugaOnkoChem', 'EvnUslugaOnkoBeam', 'EvnUslugaOnkoGormun',*/ 'EvnUslugaOnkoSurg', 'EvnUslugaPar')";
		//if (!empty($data['parent']) && $data['parent'] == 'EvnPS') {
		$EvnClassFilter = "and (EU.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaStom' /*, 'EvnUslugaOnkoChem', 'EvnUslugaOnkoBeam', 'EvnUslugaOnkoGormun', 'EvnUslugaOnkoSurg'*/ ) OR (EU.EvnClass_SysNick like 'EvnUslugaPar' and EU.EvnUsluga_setDate is not null))"; // все услуги отображаемые в ЕМК.
		//}
		
		$p = array(
			'pid' => $data['pid'],
			'Lpu_id' => $data['Lpu_id'],
			'isMedStatUser' => !empty($data['session']["isMedStatUser"]) ? $data['session']["isMedStatUser"] : 0
		);
		if (isset($data['byMorbus']) && $data['byMorbus']) {
            if (isset($data['Morbus_id']) && (!empty($data['Morbus_id']))) {
				$evnFilter = "and EU.Morbus_id = :Morbus_id";
				$p['Morbus_id'] = $data['Morbus_id'];
			} else {
				$evnFilter = "and EU.Morbus_id = (SELECT morbus_id FROM dbo.v_Evn with (nolock) WHERE evn_id = :pid AND Morbus_id IS NOT null)";
			}
            if (isset($data['EvnEdit_id']) && (!empty($data['EvnEdit_id']))) {
				$p['EvnEdit_id'] = $data['EvnEdit_id'];
				$evnFilter .= " and (not exists(select * from v_Evn with(nolock) where Evn_id = :EvnEdit_id) or EU.EvnUsluga_pid = :EvnEdit_id)";
			}
        } else {
            $evnFilter = "and (:pid in (EU.EvnUsluga_pid, EU.EvnUsluga_rid, EP.EvnPrescr_pid, EP.EvnPrescr_rid))";

			$pidName = 'EU.EvnUsluga_pid';
			if(isset($data['parent']) && in_array($data['parent'], array('EvnPL','EvnPLStom','EvnPS')))
				$pidName = 'EU.EvnUsluga_rid';
			$with_clause = "
			with UslugaPid (
				EvnUsluga_id
			) as (
				select EvnUsluga_id from v_EvnUsluga EU with (nolock)
				where  :pid = {$pidName}
				{$EvnClassFilter}
				" . ($allowVizitCode === false ? "and ISNULL(EU.EvnUsluga_IsVizitCode, 1) = 1" : "") . "
			)";
			if(!empty($data['rid']) && !empty($data['parent']) && $data['parent'] == 'EvnPLStom') {
				$with_clause = "
				with UslugaPid (
					EvnUsluga_id
				) as (
					select EvnUsluga_id from v_EvnUsluga EU with (nolock)
					where  (:pid = EU.EvnUsluga_pid or :rid = EU.EvnUsluga_rid)
					{$EvnClassFilter}
					" . ($allowVizitCode === false ? "and ISNULL(EU.EvnUsluga_IsVizitCode, 1) = 1" : "") . "
				)";
				$p['rid'] = $data['rid'];
			}
        }
		

		if (strlen($with_clause)==0) {
			$where_clause = "
				(1 = 1)
				$evnFilter
				{$EvnClassFilter}
			";
		} else {
			$where_clause = "
				 exists(Select top 1 1 from UslugaPid with(nolock) where UslugaPid.EvnUsluga_id = EU.EvnUsluga_id)
			";
		}
		$this->load->model('EvnVizitPL_model');
		if ( $this->EvnVizitPL_model->isUseVizitCode && $allowVizitCode === false ) {
			$where_clause .= "
				and ISNULL(EU.EvnUsluga_IsVizitCode, 1) = 1
			";
		}

		//Только основные услуги
		$where_clause .= " and (EvnParent.EvnClass_SysNick <> 'EvnUslugaPar' or EvnParent.EvnClass_SysNick is null)";

		if ( !empty($data['parent']) && $data['parent'] == 'EvnPLStom' && $data['class'] == 'EvnUsluga' ) {
			$select_clause .= "
				,ROUND(ISNULL(EU.EvnUsluga_Price, 0), 2) as EvnUsluga_Price
				,ROUND(ISNULL(EU.EvnUsluga_Summa, 0), 2) as EvnUsluga_Summa
			";
			$from_clause .= " left join v_UslugaComplexTariff UCT with (nolock) on UCT.UslugaComplexTariff_id = EU.UslugaComplexTariff_id";
		}
		
		
		/*if (!empty($data['isMorbusOnko']) && $data['isMorbusOnko'] == 2) {
			$where_clause .= " and onkoucat.UslugaComplexAttributeType_SysNick is not null ";
		}*/
		
		$MesField = "EDPLS.Mes_id";
		if (!empty($data['Mes_id'])) {
			$MesField = ":Mes_id";
			$p['Mes_id'] = $data['Mes_id'];
		}

        switch ( $data['class'] ) {
			case 'EvnUslugaStom':
				$with_clause = "";
                $select_clause = "
						EU.EvnUslugaStom_id as EvnUsluga_id,
						EU.EvnUslugaStom_pid as EvnUsluga_pid,
						'EvnUslugaStom' as EvnClass_SysNick,
						convert(varchar(10), EU.EvnUslugaStom_setDate, 104) as EvnUsluga_setDate,
						ISNULL(Usluga.Usluga_Code, UC.UslugaComplex_Code) as Usluga_Code,
						ISNULL(Usluga.Usluga_Name, UC.UslugaComplex_Name) as Usluga_Name,
						ROUND(cast(EU.EvnUslugaStom_Kolvo as float), 2) as EvnUsluga_Kolvo,
						ROUND(cast(EU.EvnUslugaStom_Price as float), 2) as EvnUsluga_Price,
						ROUND(cast(EU.EvnUslugaStom_Summa as float), 2) as EvnUsluga_Summa,
						PT.PayType_id,
						ISNULL(PT.PayType_SysNick, '') as PayType_SysNick,
						ISNULL(parodontogram.Parodontogram_id, 0) as EvnUslugaStom_hasParodontogram,
						case when 'A02.07.009' = ISNULL(UC2011.UslugaComplex_Code,'') then 1 else 0 end as EvnUslugaStom_isParodontogram,
						case
							when Diag.Diag_Code is not null then Diag.Diag_Code + ' ' + ISNULL(cast(Tooth.Tooth_Code as varchar(2)), '')
							else ''
						end as EvnDiagPLStom_Title,
						Diag.Diag_Code,
						Tooth.Tooth_Code,
						case when EU.EvnUslugaStom_IsAllMorbus = 2 then 'X' else '' end as EvnUsluga_IsAllMorbus,
						case when EU.EvnUslugaStom_IsMes = 2 then 'X' else '' end as EvnUsluga_IsMes,
						case when MU.MesUsluga_IsNeedUsluga = 2 then 'X' else '' end as EvnUsluga_IsRequired,
						EU.EvnDiagPLStom_id,
						EU.UslugaComplex_id,
						EU.EvnClass_id,
						doc.EvnXml_id,
						isoper.UslugaComplexAttributeType_SysNick as isoper
                ";
                $from_clause = "
						v_EvnUslugaStom EU with (nolock)
						inner join v_EvnVizitPLStom EVPLS with (nolock) on EVPLS.EvnVizitPLStom_id = EU.EvnUslugaStom_pid
						left join v_EvnDiagPLStom EDPLS with (nolock) on EDPLS.EvnDiagPLStom_id = EU.EvnDiagPLStom_id
						outer apply (
							select top 1 MesUsluga_IsNeedUsluga
							from v_MesUsluga with (nolock)
							where Mes_id = {$MesField}
								and UslugaComplex_id = EU.UslugaComplex_id
						) MU
						left join v_Diag Diag with (nolock) on Diag.Diag_id = EDPLS.Diag_id
						left join v_Tooth Tooth with (nolock) on Tooth.Tooth_id = EDPLS.Tooth_id
						left join Usluga with (nolock) on Usluga.Usluga_id = EU.Usluga_id
						left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
						left join EvnVizit EV with (nolock) on EV.EvnVizit_id = EU.EvnUslugaStom_pid
						left join EvnSection ES with (nolock) on ES.EvnSection_id = EU.EvnUslugaStom_pid
						left join v_PayType PT with (nolock) on PT.PayType_id = EU.PayType_id
						left join v_UslugaComplex UC2011 with (nolock) on UC2011.UslugaComplex_id = UC.UslugaComplex_2011id
						outer apply (
							select top 1 p.Parodontogram_id
							from v_Parodontogram p with (nolock)
							where p.EvnUslugaStom_id = EU.EvnUslugaStom_id
						) parodontogram
						outer apply (
							select top 1 EvnXml_id
							from v_EvnXml with (nolock)
							where Evn_id = EU.EvnUslugaStom_id
							order by EvnXml_insDT desc -- костыль, должен быть только один протокол
						) doc
						outer apply (
							select top 1 UslugaComplexAttributeType_SysNick
							from v_UslugaComplexAttribute UCA with (nolock)
							inner join v_UslugaComplexAttributeType UCAT with (nolock) on UCAT.UslugaComplexAttributeType_id = UCA.UslugaComplexAttributeType_id
							where UCA.UslugaComplex_id = EU.UslugaComplex_id and UCAT.UslugaComplexAttributeType_SysNick = 'operstomatusl'
						) isoper
				";
				$where_clause = "
					(1 = 1)
				";
				
				if ( $this->EvnVizitPL_model->isUseVizitCode && $allowVizitCode === false ) {
					$where_clause .= "
						and ISNULL(EU.EvnUslugaStom_IsVizitCode, 1) = 1
					";
				}
				// Добавляем union для второго условия (на форме заболевания видим услуги, добавленные по этому заболеванию + услуги "для всех заболеваний"), 
				// т.е. для отображения услуг "для всех заболеваний"
				$where_clause_union = $where_clause;
				if ( !empty($data['isEvnDiagPLStom']) ) {
					$where_clause .= "and EU.EvnDiagPLStom_id = :mid ";
					$where_clause_union .= "and EU.EvnUslugaStom_rid = :rid and ISNULL(EU.EvnUslugaStom_IsAllMorbus, 1) = 2 ";
					$p['mid'] = $data['mid'];
					$p['rid'] = $data['rid'];
					$union = "
						union
						$with_clause
						select
						  $accessType
						  $select_clause
						  $addSelectclause
						from
						  $from_clause
						where
						  $where_clause_union
					";
				}
				else {
					$where_clause .= "and EVPLS.EvnVizitPLStom_id = :pid";
				}
				
				break;
			// виды специального лечения специфики по онкологии
            case 'EvnUslugaOnkoChem': // химиотерапевтическое лечение
			case 'EvnUslugaOnkoBeam': // лучевое лечение
			case 'EvnUslugaOnkoGormun': // гормоноиммунотерапевтическое лечение
	        case 'EvnUslugaOnkoSurg': // хирургическое лечение
	        case 'EvnUslugaOnkoNonSpec': // неспецифическое лечение
				$with_clause = "";
				$from_clause = "
					v_EvnUsluga_all EU with (nolock)
					left join v_Usluga Usluga with (nolock) on Usluga.Usluga_id = EU.Usluga_id
					left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
					left join EvnVizit EV with (nolock) on EV.EvnVizit_id = EU.EvnUsluga_pid
					left join EvnSection ES with (nolock) on ES.EvnSection_id = EU.EvnUsluga_pid
					left join EvnUslugaOnkoChem OnkoChem with (nolock) on EU.EvnClass_SysNick = 'EvnUslugaOnkoChem' and OnkoChem.EvnUslugaOnkoChem_id = EU.EvnUsluga_id
					left join v_OnkoUslugaChemKindType ChemKindType with (nolock) on EU.EvnClass_SysNick = 'EvnUslugaOnkoChem' and ChemKindType.OnkoUslugaChemKindType_id = OnkoChem.OnkoUslugaChemKindType_id
					left join v_OnkoUslugaChemFocusType ChemFocusType with (nolock) on EU.EvnClass_SysNick = 'EvnUslugaOnkoChem' and ChemFocusType.OnkoUslugaChemFocusType_id = OnkoChem.OnkoUslugaChemFocusType_id
					left join EvnUslugaOnkoGormun OnkoGormun with (nolock) on EU.EvnClass_SysNick = 'EvnUslugaOnkoGormun' and OnkoGormun.EvnUslugaOnkoGormun_id = EU.EvnUsluga_id
					left join v_OnkoUslugaGormunFocusType GormunFocusType with (nolock) on EU.EvnClass_SysNick = 'EvnUslugaOnkoGormun' and GormunFocusType.OnkoUslugaGormunFocusType_id = OnkoGormun.OnkoUslugaGormunFocusType_id
					left join EvnUslugaOnkoBeam OnkoBeam with (nolock) on EU.EvnClass_SysNick = 'EvnUslugaOnkoBeam' and OnkoBeam.EvnUslugaOnkoBeam_id = EU.EvnUsluga_id
					left join v_OnkoUslugaBeamKindType OnkoBeamKindType with (nolock) on EU.EvnClass_SysNick = 'EvnUslugaOnkoBeam' and OnkoBeamKindType.OnkoUslugaBeamKindType_id = OnkoBeam.OnkoUslugaBeamKindType_id
					left join v_OnkoUslugaBeamRadioModifType OnkoBeamRadioModifType with (nolock) on EU.EvnClass_SysNick = 'EvnUslugaOnkoBeam' and OnkoBeamRadioModifType.OnkoUslugaBeamRadioModifType_id = OnkoBeam.OnkoUslugaBeamRadioModifType_id
					left join v_OnkoUslugaBeamMethodType OnkoBeamMethodType with (nolock) on EU.EvnClass_SysNick = 'EvnUslugaOnkoBeam' and OnkoBeamMethodType.OnkoUslugaBeamMethodType_id = OnkoBeam.OnkoUslugaBeamMethodType_id
					left join v_OnkoUslugaBeamIrradiationType OnkoBeamIrradiationType with (nolock) on EU.EvnClass_SysNick = 'EvnUslugaOnkoBeam' and OnkoBeamIrradiationType.OnkoUslugaBeamIrradiationType_id = OnkoBeam.OnkoUslugaBeamIrradiationType_id
					left join v_OnkoUslugaBeamFocusType BeamFocusType with (nolock) on EU.EvnClass_SysNick = 'EvnUslugaOnkoBeam' and BeamFocusType.OnkoUslugaBeamFocusType_id = OnkoBeam.OnkoUslugaBeamFocusType_id
					left join v_Lpu Lpu with (nolock) on EU.Lpu_uid = Lpu.Lpu_id
					left join EvnUslugaOnkoSurg OnkoSurg with (nolock) on EU.EvnClass_SysNick = 'EvnUslugaOnkoSurg' and OnkoSurg.EvnUslugaOnkoSurg_id = EU.EvnUsluga_id
					left join v_OperType OperType with (nolock) on EU.EvnClass_SysNick = 'EvnUslugaOnkoSurg' and OnkoSurg.OperType_id = OperType.OperType_id
					left join v_MedPersonal MP with (nolock) on EU.EvnClass_SysNick = 'EvnUslugaOnkoSurg' and EU.MedPersonal_id = MP.MedPersonal_id and EU.Lpu_uid = MP.Lpu_id
				";
				$select_clause = "
				 EU.EvnUsluga_id
				,RTRIM(EU.EvnClass_SysNick) as EvnClass_SysNick
				,convert(varchar(10), EU.EvnUsluga_setDate, 104) as EvnUsluga_setDate
				,ISNULL(EU.EvnUsluga_setTime, '') as EvnUsluga_setTime
				,ISNULL(Usluga.Usluga_Code, UC.UslugaComplex_Code) as Usluga_Code
				,ISNULL(Usluga.Usluga_Name, UC.UslugaComplex_Name) as Usluga_Name
				,convert(varchar(10), EU.EvnUsluga_disDate, 104) as EvnUsluga_disDate,
				EU.Person_id,
				EU.EvnUsluga_pid,
				CASE EU.UslugaPlace_id
					WHEN 1 THEN 	--1	Отделение ЛПУ
						(SELECT TOP 1 s.LpuSection_Name FROM dbo.v_LpuSection s with (nolock) WHERE s.LpuSection_id = EU.LpuSection_uid)
					WHEN 2 THEN 	--2	Другое ЛПУ
						(SELECT TOP 1 l.Lpu_Nick FROM v_lpu l with (nolock) WHERE l.Lpu_id = eu.Lpu_uid)
					WHEN 3 THEN		--3	Другая организация
						(SELECT TOP 1 o.Org_Nick FROM v_org o with (nolock) WHERE o.Org_id = eu.Org_uid)
				END AS place_name,
				Lpu.Lpu_Nick as Lpu_Name,
				MP.Person_Fio as MedPersonal_Name,
				OperType.OperType_Name,
				ChemKindType.OnkoUslugaChemKindType_Name,
				OnkoGormun.EvnUslugaOnkoGormun_IsBeam,
				OnkoGormun.EvnUslugaOnkoGormun_IsSurg,
				OnkoGormun.EvnUslugaOnkoGormun_IsDrug,
				OnkoGormun.EvnUslugaOnkoGormun_IsOther,
				coalesce(GormunFocusType.OnkoUslugaGormunFocusType_Name,ChemFocusType.OnkoUslugaChemFocusType_Name,BeamFocusType.OnkoUslugaBeamFocusType_Name) as FocusType_Name,
				OnkoBeamIrradiationType.OnkoUslugaBeamIrradiationType_Name,
				OnkoBeamKindType.OnkoUslugaBeamKindType_Name,
				OnkoBeamMethodType.OnkoUslugaBeamMethodType_Name,
				OnkoBeamRadioModifType.OnkoUslugaBeamRadioModifType_Name";
				if(!empty($data['EvnEdit_id']))
				{
					$p['EvnEdit_id'] = $data['EvnEdit_id'];
					$accessType = 'case when (EU.Lpu_id ' . getLpuIdFilter($data) . ' or EU.Lpu_uid ' . getLpuIdFilter($data) . ') and M.Morbus_disDT is null and (';
					//условие при редактировании из регистра
					$accessType .= '(EU.EvnUsluga_pid is null and EU.Person_id = :EvnEdit_id)';
					$accessType .= ' or ';
					//при редактировании из учетного документа #55518
					$accessType .= "(EU.EvnUsluga_pid = :EvnEdit_id)";
					$accessType .= ") then 'edit' else 'view' end as accessType,";
					$addSelectclause .= '
					,EU.Morbus_id
					,:EvnEdit_id as MorbusOnko_pid';
					$from_clause .= '
					left join v_Morbus M with (nolock) on M.Morbus_id = EU.Morbus_id
					left join v_Evn EvnEdit with (nolock) on EvnEdit.Evn_id = :EvnEdit_id and EU.Person_id != :EvnEdit_id';
				} else {
					$accessType = 'case when (EU.Lpu_id ' . getLpuIdFilter($data) . ' or EU.Lpu_uid ' . getLpuIdFilter($data) . ') and M.Morbus_disDT is null and (';
					$accessType .= 'EU.EvnUsluga_pid is null';
					$accessType .= ") then 'edit' else 'view' end as accessType,";
					$addSelectclause .= '
					,EU.Morbus_id
					,EU.Person_id as MorbusOnko_pid';
					$from_clause .= 'left join v_Morbus M with (nolock) on M.Morbus_id = EU.Morbus_id';
				}
				if (isset($data['accessType']) && $data['accessType'] == 'view') {
					$accessType  = " 'view' as accessType,";
				}
                $where_clause = "
					    (1 = 1)
						$evnFilter
						and EU.EvnClass_SysNick in ('{$data['class']}')
				";
				break;
		}

		$except_ids = array();
		foreach($excepts as $except) {
			if (!empty($except['EvnUsluga_id'])) {
				$except_ids[] = $except['EvnUsluga_id'];
			}
		}
		if (count($except_ids) > 0) {
			$except_ids = implode(",", $except_ids);
			$where_clause .= " and EU.EvnUsluga_id not in ({$except_ids})";
		}

		$query = "
			$with_clause
			select
			  $accessType
			  $select_clause
			  $addSelectclause
			from
			  $from_clause
			where
			  $where_clause
			
			$union
		";
		if (false && $data['class'] == 'EvnUslugaOnkoSurg') {
			echo getDebugSQL($query, $p); exit;
		}
		//echo getDebugSQL($query, $p);exit();
		$result = $this->db->query($query, $p);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadUslugaComplexCombo($data) {
		$query = "
			select
				UslugaComplex_id,
				UslugaComplex_Code,
				RTRIM(UslugaComplex_Name) as UslugaComplex_Name
			from
				UslugaComplex with (nolock)
			where
				Lpu_id = :Lpu_id
				and (
					(UslugaComplex_begDT is null OR UslugaComplex_begDT <= dbo.tzGetDate()) AND
					(UslugaComplex_endDT is null OR UslugaComplex_endDT >= dbo.tzGetDate())
				)
			order by
				UslugaComplex_Code
		";

		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadUslugaComplexGrid($data) {
		$query = "
			select
				-- select
				UslugaComplex_id,
				UslugaComplex_Code,
				RTRIM(UslugaComplex_Name) as UslugaComplex_Name
				-- end select
			from
				-- from
				UslugaComplex with (nolock)
				-- end from
			where
				-- where
				Lpu_id = :Lpu_id
				-- end where
			order by
				-- order by
				UslugaComplex_Code
				-- end order by
		";

		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);

		$response = array();

		if ( $data['start'] >= 0 && $data['limit'] >= 0 ) {
			$limit_query = getLimitSQLPH($query, $data['start'], $data['limit']);
			$result = $this->db->query($limit_query, $queryParams);
		}
		else {
			$result = $this->db->query($query, $queryParams);
		}

		if ( is_object($result) ) {
			$res = $result->result('array');

			if ( is_array($res) ) {
				if ( $data['start'] == 0 && count($res) < 100 ) {
					$response['data'] = $res;
					$response['totalCount'] = count($res);
				}
				else {
					$response['data'] = $res;

					$get_count_query = getCountSQLPH($query);
					$get_count_result = $this->db->query($get_count_query, $queryParams);

					if ( is_object($get_count_result) ) {
						$response['totalCount'] = $get_count_result->result('array');
						$response['totalCount'] = $response['totalCount'][0]['cnt'];
					}
					else {
						return false;
					}
				}
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}

		return $response;
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadUslugaComplexListGrid($data) {
		$query = "
			select
				-- select
				UCL.UslugaComplexList_id,
				UCom.UslugaComplex_id,
				U.Usluga_id,
				UC.UslugaClass_id,
				U.Usluga_Code,
				RTRIM(U.Usluga_Name) as Usluga_Name,
				RTRIM(UC.UslugaClass_Name) as UslugaClass_Name
				-- end select
			from
				-- from
				UslugaComplexList UCL with (nolock)
				inner join Usluga U with (nolock) on U.Usluga_id = UCL.Usluga_id
				inner join UslugaComplex UCom with (nolock) on UCom.UslugaComplex_id = UCL.UslugaComplex_id
				inner join UslugaClass UC with (nolock) on UC.UslugaClass_id = UCL.UslugaClass_id
				-- end from
			where
				-- where
				UCom.Lpu_id = :Lpu_id
				and UCom.UslugaComplex_id = :UslugaComplex_id
				-- end where
			order by
				-- order by
				U.Usluga_Name
				-- end order by
		";

		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id']
		);

		$response = array();

		if ( $data['start'] >= 0 && $data['limit'] >= 0 ) {
			$limit_query = getLimitSQLPH($query, $data['start'], $data['limit']);
			$result = $this->db->query($limit_query, $queryParams);
		}
		else {
			$result = $this->db->query($query, $queryParams);
		}

		if ( is_object($result) ) {
			$res = $result->result('array');

			if ( is_array($res) ) {
				if ( $data['start'] == 0 && count($res) < 100 ) {
					$response['data'] = $res;
					$response['totalCount'] = count($res);
				}
				else {
					$response['data'] = $res;

					$get_count_query = getCountSQLPH($query);
					$get_count_result = $this->db->query($get_count_query, $queryParams);

					if ( is_object($get_count_result) ) {
						$response['totalCount'] = $get_count_result->result('array');
						$response['totalCount'] = $response['totalCount'][0]['cnt'];
					}
					else {
						return false;
					}
				}
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}

		return $response;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadUslugaComplexList($data) {
		if ( isset($data['Usluga_id']) ) {
			$query = "
				select
					1 as UslugaComplex_id,
					Usluga_id,
					Usluga_Code,
					RTRIM(Usluga_Name) as Usluga_Name,
					0 as UslugaComplex_UET
				from
					v_Usluga with (nolock)
				where
					Usluga_id = :Usluga_id
			";

			$queryParams = array(
				'Usluga_id' => $data['Usluga_id']
			);
		}
		else {
			$queryParams = array(
				'Lpu_id' => $data['Lpu_id'],
				'LpuSection_id' => $data['LpuSection_id']
			);
			$filter = '';
			/*
			* выводим  услуги с учетом того, закрыта уже услуга или нет.
			* для этого должен быть передан параметр Usluga_date - дата оказания услуги
			*/
			if ( isset($data['Usluga_date']) )
			{
				$filter .= " AND (UC.UslugaComplex_endDT is null OR cast(UC.UslugaComplex_endDT as date) >= cast(:Usluga_date as date))
							 AND (UC.UslugaComplex_begDT is null OR cast(UC.UslugaComplex_begDT as date) <= cast(:Usluga_date as date))";
				$queryParams['Usluga_date'] = $data['Usluga_date'];
			}
			$query = "
				select
					UC.UslugaComplex_id,
					U.Usluga_id,
					U.Usluga_Code,
					RTRIM(U.Usluga_Name) as Usluga_Name,
					ROUND(UC.UslugaComplex_UET, 2) as UslugaComplex_UET
				from v_UslugaComplex UC with (nolock)
					inner join Usluga U with (nolock) on U.Usluga_id = UC.Usluga_id
					inner join LpuSection LS with (nolock) on LS.LpuSection_id = UC.LpuSection_id
					inner join LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
					inner join LpuBuilding LB with (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
				where (1 = 1)
					and LS.LpuSection_id = :LpuSection_id
					and LB.Lpu_id = :Lpu_id
					{$filter}
				order by
					U.Usluga_Code
			";
		}

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function saveEvnUslugaCommon($data) {
		$resp_save = $this->_saveEvnUslugaPackage($data, 'EvnUslugaCommon');

		if (!empty($resp_save[0]['EvnUslugaCommon_id'])) {
			if (!empty($data['AttributeSignValueData'])) {
				$this->load->model('Attribute_model');
				$AttributeSignValueData = json_decode($data['AttributeSignValueData'], true);

				if (is_array($AttributeSignValueData)) {
					foreach ($AttributeSignValueData as $AttributeSignValue) {
						$AttributeSignValue['pmUser_id'] = $data['pmUser_id'];
						$AttributeSignValue['AttributeSign_TableName'] = 'EvnUslugaCommon';
						$AttributeSignValue['AttributeSignValue_TablePKey'] = $resp_save[0]['EvnUslugaCommon_id'];
						$AttributeSignValue['AttributeSignValue_begDate'] = !empty($AttributeSignValue['AttributeSignValue_begDate']) ? ConvertDateFormat($AttributeSignValue['AttributeSignValue_begDate']) : null;
						$AttributeSignValue['AttributeSignValue_endDate'] = !empty($AttributeSignValue['AttributeSignValue_endDate']) ? ConvertDateFormat($AttributeSignValue['AttributeSignValue_endDate']) : null;

						$this->Attribute_model->isAllowTransaction = false;
						switch ($AttributeSignValue['RecordStatus_Code']) {
							case 0:
							case 2:
								$queryResponse = $this->Attribute_model->saveAttributeSignValue($AttributeSignValue);
								break;

							case 3:
								$queryResponse = $this->Attribute_model->deleteAttributeSignValue($AttributeSignValue);
								break;
						}
						$this->Attribute_model->isAllowTransaction = true;

						if (isset($queryResponse) && !is_array($queryResponse)) {
							$this->rollbackTransaction();
							return array(array('Error_Msg' => 'Ошибка при ' . ($AttributeSignValue['RecordStatus_Code'] == 3 ? 'удалении' : 'сохранении') . ' обслуживаемого отделения'));
						} else if (!empty($queryResponse[0]['Error_Msg'])) {
							$this->rollbackTransaction();
							return $queryResponse;
						}
					}
				}

			}

			if(!empty($data['UslugaComplex_id'])){
				//смотрим есть ли у него ссылка на метку
				$sql = "
					SELECT 
						PL.PersonLabel_id
					FROM v_PersonLabel PL (nolock)
						left join v_LabelDiag LD (nolock) on PL.Label_id = LD.Label_id
						left join v_EvnUslugaCommon EUC (nolock) on LD.UslugaComplex_id = EUC.UslugaComplex_id and PL.Person_id = EUC.Person_id
					WHERE 1=1
						AND PL.PersonLabel_disDate is null
						AND EUC.EvnUslugaCommon_id = :EvnUslugaCommon_id
				";

				$labelDiag = $this->getFirstRowFromQuery($sql, array('EvnUslugaCommon_id' => $resp_save[0]['EvnUslugaCommon_id']));
				if(!empty($labelDiag['PersonLabel_id']) && !empty($data['EvnUslugaCommon_setDate'])){
					//найденная соответствующая открытая метка человека снимается
					$sql = "
						UPDATE PersonLabel SET PersonLabel_disDate = :EvnUslugaCommon_setDate
						WHERE PersonLabel_id = :PersonLabel_id AND PersonLabel_disDate is null
					";
					$res=$this->db->query($sql, array('EvnUslugaCommon_setDate' => $data['EvnUslugaCommon_setDate'], 'PersonLabel_id' => $labelDiag['PersonLabel_id']));
				}
			}
		}

		return $resp_save;
    }

	/**
	 * @param $data
	 * @return array|bool
	 */
	private function _saveEvnUslugaCommon($data) {
		if ( !isset($data['EvnUslugaCommon_id']) || $data['EvnUslugaCommon_id'] <= 0 ) {
			$procedure = 'p_EvnUslugaCommon_ins';
			$data['EvnUslugaCommon_id'] = null;
		}
		else {
			$procedure = 'p_EvnUslugaCommon_upd';
		}

		if ( !isset($data['EvnUslugaCommon_pid']) ) {
			$data['EvnUslugaCommon_pid'] = $data['EvnUslugaCommon_rid'];
		}

		if ( isset($data['EvnUslugaCommon_setTime']) ) {
			$data['EvnUslugaCommon_setDate'] .= ' ' . $data['EvnUslugaCommon_setTime'] . ':00:000';
		}

		if ( !empty($data['EvnUslugaCommon_disDate']) && !empty($data['EvnUslugaCommon_disTime']) ) {
			$data['EvnUslugaCommon_disDate'] .= ' ' . $data['EvnUslugaCommon_disTime'] . ':00:000';
		}
		if ( empty($data['EvnUslugaCommon_disDate']) ) {
			$data['EvnUslugaCommon_disDate'] = $data['EvnUslugaCommon_setDate'];
		}

		if (!empty($data['MedStaffFact_id'])) {
			// проверяем что рабочее место врача соответсвует врачу, т.к. как то умудряются сохранять некорректные данные
			$filterLpuSection = "and LpuSection_id = :LpuSection_id";
			if (empty($data['LpuSection_uid']) || getRegionNick() == "penza") {
				$filterLpuSection = ""; // на Пензе могут вводить врачей не работающих в отделении. refs #124791 + консультационная услуга может оказываться в другой МО, поэтому фильтр по отделению не нужен #138438
			}

			$MedStaffFact_id = $this->getFirstResultFromQuery("
				select top 1
					MedStaffFact_id
				from
					v_MedStaffFact (nolock)
				where
					MedStaffFact_id = :MedStaffFact_id
					{$filterLpuSection}
					and MedPersonal_id = :MedPersonal_id
			", array(
				'MedStaffFact_id' => $data['MedStaffFact_id'],
				'LpuSection_id' => $data['LpuSection_uid'],
				'MedPersonal_id' => $data['MedPersonal_id']
			));

			if (empty($MedStaffFact_id)) {
				throw new Exception('Место работы врача не соответствует врачу');
			}
		}
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@morbus_id bigint;
			set @Res = :EvnUslugaCommon_id;
			IF (:Morbus_id IS NOT NULL ) BEGIN
           		SET @morbus_id = :Morbus_id;
			   END ELSE BEGIN
            	SET @morbus_id = (SELECT morbus_id FROM v_evn with (nolock) WHERE evn_id = :EvnUslugaCommon_pid);
			END;
			exec " . $procedure . "
				@EvnUslugaCommon_id = @Res output,
				@EvnUslugaCommon_pid = :EvnUslugaCommon_pid,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnUslugaCommon_setDT = :EvnUslugaCommon_setDT,
				@EvnUslugaCommon_disDT = :EvnUslugaCommon_disDT,
				@PayType_id = :PayType_id,
				@Usluga_id = :Usluga_id,
				@UslugaComplex_id = :UslugaComplex_id,
				@MedSpecOms_id = :MedSpecOms_id,
				@LpuSectionProfile_id = :LpuSectionProfile_id,
				@MedPersonal_id = :MedPersonal_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@UslugaPlace_id = :UslugaPlace_id,
				@Lpu_uid = :Lpu_uid,
				@LpuSection_uid = :LpuSection_uid,
				@Org_uid = :Org_uid,
				@EvnUslugaCommon_Kolvo = :EvnUslugaCommon_Kolvo,
				@Morbus_id = @morbus_id,
				@EvnUslugaCommon_CoeffTariff = :EvnUslugaCommon_CoeffTariff,
				@EvnUslugaCommon_IsModern = :EvnUslugaCommon_IsModern,
				@MesOperType_id = :MesOperType_id,
				@UslugaComplexTariff_id = :UslugaComplexTariff_id,
				@DiagSetClass_id = :DiagSetClass_id,
				@Diag_id = :Diag_id,
				@EvnUslugaCommon_Price = :EvnUslugaCommon_Price,
				@EvnUslugaCommon_Summa = :EvnUslugaCommon_Summa,
				@EvnPrescr_id = :EvnPrescr_id,
				@EvnUslugaCommon_IsVizitCode = :EvnUslugaCommon_IsVizitCode,
				@EvnUslugaCommon_IsMinusUsluga = :EvnUslugaCommon_IsMinusUsluga,
				@EvnDirection_id = :EvnDirection_id,
				@UslugaExecutionType_id = :UslugaExecutionType_id,
				@UslugaExecutionReason_id = :UslugaExecutionReason_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnUslugaCommon_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'EvnUslugaCommon_id' => $data['EvnUslugaCommon_id'],
			'EvnUslugaCommon_pid' => $data['EvnUslugaCommon_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => (!empty($data['PersonEvn_id']) ? $data['PersonEvn_id'] : NULL),
			'EvnUslugaCommon_setDT' => $data['EvnUslugaCommon_setDate'],
			'EvnUslugaCommon_disDT' => $data['EvnUslugaCommon_disDate'],
			'PayType_id' => $data['PayType_id'],
			'Usluga_id' => (!empty($data['Usluga_id']) ? $data['Usluga_id'] : NULL),
			'UslugaComplex_id' => (!empty($data['UslugaComplex_id']) ? $data['UslugaComplex_id'] : NULL),
			'MedSpecOms_id' => (!empty($data['MedSpecOms_id']) ? $data['MedSpecOms_id'] : NULL),
			'LpuSectionProfile_id' => (!empty($data['LpuSectionProfile_id']) ? $data['LpuSectionProfile_id'] : NULL),
			'MedPersonal_id' => (!empty($data['MedPersonal_id']) ? $data['MedPersonal_id'] : NULL),
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'UslugaPlace_id' => $data['UslugaPlace_id'],
			'Lpu_uid' => (!empty($data['Lpu_uid'])?$data['Lpu_uid']:null),
			'LpuSection_uid' => $data['LpuSection_uid'],
			'Org_uid' => $data['Org_uid'],
			'EvnUslugaCommon_Kolvo' => $data['EvnUslugaCommon_Kolvo'],
			'EvnPrescr_id' => (!empty($data['EvnPrescr_id']) ? $data['EvnPrescr_id'] : NULL),
			'Morbus_id' => (!empty($data['Morbus_id']) ? $data['Morbus_id'] : NULL),
			'EvnUslugaCommon_IsVizitCode' => (!empty($data['EvnUslugaCommon_IsVizitCode']) ? $data['EvnUslugaCommon_IsVizitCode'] : NULL),
			'EvnUslugaCommon_IsMinusUsluga' => (!empty($data['EvnUslugaCommon_IsMinusUsluga']) ? $data['EvnUslugaCommon_IsMinusUsluga'] : NULL),
			'EvnUslugaCommon_CoeffTariff' => (!empty($data['EvnUslugaCommon_CoeffTariff']) ? $data['EvnUslugaCommon_CoeffTariff'] : NULL),
			'EvnUslugaCommon_IsModern' => (!empty($data['EvnUslugaCommon_IsModern']) ? $data['EvnUslugaCommon_IsModern'] : NULL),
			'MesOperType_id' => (!empty($data['MesOperType_id']) ? $data['MesOperType_id'] : NULL),
			'UslugaComplexTariff_id' => (!empty($data['UslugaComplexTariff_id']) ? $data['UslugaComplexTariff_id'] : NULL),
			'DiagSetClass_id' => (!empty($data['DiagSetClass_id']) ? $data['DiagSetClass_id'] : NULL),
			'Diag_id' => (!empty($data['Diag_id']) ? $data['Diag_id'] : NULL),
			'EvnUslugaCommon_Price' => (!empty($data['EvnUslugaCommon_Price']) ? $data['EvnUslugaCommon_Price'] : NULL),
			'EvnUslugaCommon_Summa' => (!empty($data['EvnUslugaCommon_Price']) ? number_format(($data['EvnUslugaCommon_Kolvo'] * $data['EvnUslugaCommon_Price']), 2, '.', '') : NULL),
			'EvnDirection_id' => (!empty($data['EvnDirection_id']) ? $data['EvnDirection_id'] : NULL),
			'UslugaExecutionType_id' => (!empty($data['UslugaExecutionType_id']) ? $data['UslugaExecutionType_id'] : NULL),
			'UslugaExecutionReason_id' => (!empty($data['UslugaExecutionReason_id']) ? $data['UslugaExecutionReason_id'] : NULL),
			'pmUser_id' => $data['pmUser_id']
		);

		$PersonNotice = $this->getPersonNotice($data);
		//Начинаем отслеживать статусы события EvnUslugaCommon
		$PersonNotice->setEvnClassSysNick('EvnUslugaCommon');
		$PersonNotice->setEvnId($data['EvnUslugaCommon_id']);
		$PersonNotice->doStatusSnapshotFirst();

		$oldEvnPrescrId = null;
		if ($data['EvnUslugaCommon_id'] > 0) {
			$oldEvnPrescrId = $this->getFirstResultFromQuery('
				select EvnPrescr_id from v_EvnUsluga with (nolock)
				where EvnUsluga_id = :EvnUslugaCommon_id
			', array(
				'EvnUslugaCommon_id' => $data['EvnUslugaCommon_id'],
			));
			if (false === $oldEvnPrescrId) {
				//нужно откатить транзакцию
				throw new Exception('Не удалось прочитать общую услугу');
			}
		}

		//throw new Exception(getDebugSQL($query, $queryParams));
		//echo '<pre>',print_r(getDebugSQL($query, $queryParams)),'</pre>'; die();
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			//нужно откатить транзакцию
			throw new Exception('Не удалось сохранить общую услугу');
		}
		$response = $result->result('array');
		if (!empty($response[0]['Error_Msg'])) {
			//нужно откатить транзакцию
			throw new Exception($response[0]['Error_Msg']);
		}

		if ($oldEvnPrescrId != $queryParams['EvnPrescr_id']) {
			$this->load->model('EvnPrescr_model', 'EvnPrescr_model');
			if ($oldEvnPrescrId > 0) {
				// Отмена выполнения назначения
				$tmp = $this->EvnPrescr_model->rollbackEvnPrescrExecution(array(
					'EvnPrescr_id' => $oldEvnPrescrId,
					'pmUser_id' => $data['pmUser_id'],
				));
				if (!empty($tmp[0]['Error_Msg'])) {
					//нужно откатить транзакцию
					throw new Exception($tmp[0]['Error_Msg']);
				}
			}
			if ($queryParams['EvnPrescr_id'] > 0) {
				// помечаем назначение как выполненное
				$tmp = $this->EvnPrescr_model->execEvnPrescr(array(
					'EvnPrescr_id' => $queryParams['EvnPrescr_id'],
					'pmUser_id' => $data['pmUser_id'],
				));
				if (!empty($tmp[0]['Error_Msg'])) {
					//нужно откатить транзакцию
					throw new Exception($tmp[0]['Error_Msg']);
				}

				if (empty($data['EvnDirection_id'])) {
					// надо получить направление по назначениею, чтобы проставить статус обслужено.
					$resp_ed = $this->queryResult("
						select
							epd.EvnDirection_id
						from
							v_EvnPrescrDirection epd (nolock)
						where
							epd.EvnPrescr_id = :EvnPrescr_id
					", array(
						'EvnPrescr_id' => $queryParams['EvnPrescr_id']
					));

					if (!empty($resp_ed[0]['EvnDirection_id'])) {
						$data['EvnDirection_id'] = $resp_ed[0]['EvnDirection_id'];
					}
				}
			}
		}

		if(!empty($data['EvnDirection_id'])) {
			$this->load->model('Evn_model', 'Evn_model');
			$this->Evn_model->updateEvnStatus(array(
				'Evn_id' => $data['EvnDirection_id'],
				'EvnStatus_SysNick' => 'Serviced',
				'EvnClass_SysNick' => 'EvnDirection',
				'pmUser_id' => $data['pmUser_id']
			));
		}

		$PersonNotice->setEvnId($response[0]['EvnUslugaCommon_id']);
		$PersonNotice->doStatusSnapshotSecond();
		$PersonNotice->processStatusChange();

		return $response;
	}

	/**
	 * @param $data
	 * @return PersonNoticeEvn
	 */
	function getPersonNotice($data) {
		if (empty($this->_personNotice)) {
			$this->load->helper('PersonNotice');
			//Инициализация хелпера рассылки сообщений о смене статуса
			$this->_personNotice = new PersonNoticeEvn($data['Person_id']);
			$this->_personNotice->loadPersonInfo($data['PersonEvn_id'], $data['Server_id']);
		}
		return $this->_personNotice;
	}

	/**
	 * @param $level
	 * @return string
	 */
	function getUslugaComplexTemplate($level) {
		switch ($level)
		{
			case 0: 
				// генерация шаблона для услуги первого уровня 
				$template = "uslugacomplex/uslugacomplex_xml";
				break;
			case 1: 
				// генерация шаблона для услуги первого уровня 
				$template = "uslugacomplex/uslugacomplex";
				break;
			default: 
				// генерация шаблона для вложенных услуг
				$template = "uslugacomplex/uslugacomplex_item";
				break;
		}
		return $template;
	}
	
	
	/**
	 *  Генерация шаблона комплексной услуги при заказе
	 */
	function genEvnUslugaComplexTemplate($data) {
		$level = (isset($data['level']))?$data['level']:1;
		$response = false;
		$where = 'uc.UslugaComplex_pid = :UslugaComplex_id';
		switch ($level)
		{
			case 1: 
				$where = 'uc.UslugaComplex_id = :UslugaComplex_id';
				break;
		}
		if ($data['UslugaComplex_id']>0) {
			// код, по которому связываются данные с анализатора с конкретной услугой = UslugaComplex_ACode
			$query = "
				Select 
					case when IsNull(uc.UslugaComplex_isGenXml,0) = 0 then uc.XmlTemplate_id else null end as XmlTemplate_id, 
					uc.UslugaComplex_id,
					RTrim(uc.UslugaComplex_Name) as UslugaComplex_Name,
					RTrim(uc.UslugaComplex_ACode) as UslugaComplex_ACode,
					'' as UslugaComplex_Value,
					RTrim(RefValuesUnit.RefValuesUnit_Name) as UslugaComplex_Measure,
					RefValues_LowerLimit as UslugaComplex_ValueLow,
					RefValues_UpperLimit as UslugaComplex_ValueUpp,
					XmlTemplate.XmlTemplate_Data as XmlTemplate_Data, 
					:EvnUsluga_id as EvnUsluga_id, 
					uc.Usluga_id as Usluga_id,
					uc.RefValues_id,
					IsNull(uc.UslugaComplex_isGenXml,0) as UslugaComplex_isGenXml,
					case when Leaf.leaf_count>0 then 0 else 1 end as leaf
				from v_UslugaComplex uc with (nolock)
				left join v_RefValues RefValues with (nolock) on RefValues.RefValues_id = uc.RefValues_id 
				--left join v_Usluga Usluga with (nolock) on Usluga.Usluga_id = uc.Usluga_id 
				left join v_RefValuesUnit RefValuesUnit with (nolock) on RefValuesUnit.RefValuesUnit_id = RefValues.RefValuesUnit_id 
				left join XmlTemplate with (nolock) on XmlTemplate.XmlTemplate_id = uc.XmlTemplate_id
				outer apply (
					Select count(*) as leaf_count from v_UslugaComplex with (nolock) where UslugaComplex_pid = uc.UslugaComplex_id
				) as Leaf
				where {$where}
			";
			$params = array('UslugaComplex_id' => $data['UslugaComplex_id'], 'EvnUsluga_id' => $data['EvnUsluga_id']);
			$result = $this->db->query($query, $params);
			
			if (is_object($result)) {
				$response = $result->result('array');
				if ( is_array($response) && count($response) > 0 ) {
					// весело выводим содержимое услуг 
				}
			}
		}
		return $response;
	}

	/**
	 *  Сохранение комплексной услуги при заказе
	 */
	function saveEvnUslugaComplexOrder($data) {
		$trans_good = true;
		$trans_result = array();
		//$this->db->trans_begin();
		$data['object'] = (isset($data['object']))?$data['object']:'EvnUslugaPar';
		$time_table = (isset($data['time_table']))?$data['time_table']:'TimetablePar';
		$time_table_id = (isset($data[$time_table .'_id']))?$data[$time_table .'_id']:NULL;

		if ($trans_good === true) {
			// если услуга уже заказана, новую создавать не надо (как минимум в арм лаборанта заказ услуги создаётся при направлении сразу)
			if (!empty($data['UslugaComplex_id']) && $data['object'] == 'EvnUslugaPar' && !empty($data['EvnDirection_id'])) {
				$resp = $this->queryResult("
					select top 1
						EvnUsluga_id as EvnUslugaPar_id
					from
						v_EvnUsluga (nolock)
					where
						UslugaComplex_id = :UslugaComplex_id
						and EvnDirection_id = :EvnDirection_id
				", array(
					'UslugaComplex_id' => $data['UslugaComplex_id'],
					'EvnDirection_id' => $data['EvnDirection_id']
				));

				if (!empty($resp[0]['EvnUslugaPar_id'])) {
					return array(array('Error_Msg' => '', 'Error_Code' => '', 'EvnUsluga_id' => $resp[0]['EvnUslugaPar_id']));
				}
			}

			if (!isset($data['EvnUsluga_id']) || $data['EvnUsluga_id'] <= 0) {
				$procedure = 'p_'.$data['object'].'_ins';
			} else {
				$procedure = 'p_'.$data['object'].'_upd';
			}

			/*вот надо уточнить у Круглова (надо ли у комплексной услуги указывать тип оплаты, или указывать ее на каждую вложенную услугу, или если фомс - то ОМС?)
			Круглов Евгений ( 30.05.2011 15:28:09) :
			это надо обсуждать. Но, по моему мнению:
			1. в справочнике комплексных услуг должна сохраниться возможность отдельного указания источников финансирования для каждой услуги
			2. если источник не указан в справочнике, то автоматически ставить ОМС
			*/

			$queryParams = array(
				'EvnUsluga_id' => ( !isset($data['EvnUsluga_id']) || $data['EvnUsluga_id'] <= 0 ? NULL : $data['EvnUsluga_id']),
				'EvnUsluga_pid' => $data['EvnUsluga_pid'],
				'Lpu_id' => $data['Lpu_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'Usluga_isCito' => $data['Usluga_isCito'],
				'EvnUsluga_Result' => $data['EvnUsluga_Result'],
				'Diag_id' => $data['Diag_id'] ?? null,
				'time_table_id' => $time_table_id,
				'EvnUsluga_setDT' => null, 									// $data['EvnUslugaComplex_setDate'] . " " . $data['EvnUslugaComplex_setTime'], // время выполения услуги
				'Usluga_id' => null, //1000003, //$data['Usluga_id'],  				// вот это вообще не надо (пока поставил 'Диагностические услуги')
				'LpuSection_uid' => $data['LpuSection_uid'],  				// Отделение при заказе берется из расписания (в какое отделение направляем)
				'MedPersonal_id' => null, //$data['MedPersonal_id'],		// Врач заполняется при занесении данных услуги (при заказе мы не знаем какой именно врач окажет услугу)
				'UslugaPlace_id' => 1, //$data['UslugaPlace_id'], 			// тут тоже что-то автоматически ставиться должно
				'UslugaComplex_id' => $data['UslugaComplex_id'], 			// комлпексная услуга
				'EvnDirection_id' => $data['EvnDirection_id'], 			    // направление, если есть
				'EvnPrescr_id' => $data['EvnPrescr_id'], 			    // назначение, если есть
				'pmUser_id' => $data['pmUser_id']
			);

			$add_param = '';
			// при заказе комплексной паракл. услуги отделением ЛПУ нужно сохранить информацию о направлении
			if($data['object'] == 'EvnUslugaPar' && !empty($data['PrehospDirect_id']))
			{
				$queryParams['PrehospDirect_id'] = $data['PrehospDirect_id'];
				$queryParams['Org_did'] = (empty($data['Org_did']))?NULL:$data['Org_did'];
				$queryParams['Lpu_did'] = (empty($data['Lpu_did']))?NULL:$data['Lpu_did'];
				$queryParams['LpuSection_did'] = (empty($data['LpuSection_did']))?NULL:$data['LpuSection_did'];
				$queryParams['MedPersonal_did'] = (empty($data['MedPersonal_did']))?NULL:$data['MedPersonal_did'];
				$add_param = '@PrehospDirect_id = :PrehospDirect_id,
					@Org_did = :Org_did,
					@Lpu_did = :Lpu_did,
					@LpuSection_did = :LpuSection_did,
					@MedPersonal_did = :MedPersonal_did,';
			}

			if (empty($queryParams['EvnUsluga_pid']) && !empty($queryParams['EvnPrescr_id'])) {
				$query = "
					select top 1
						e_child.Evn_id
					from
						v_EvnPrescr ep (nolock)
						inner join v_Evn e (nolock) on e.Evn_id = EvnPrescr_pid -- посещние/движение
						inner join v_Evn e_child (nolock) on e_child.Evn_pid = e.Evn_pid -- посещения/движения той же КВС/ТАП
					where
						e_child.EvnClass_SysNick IN ('EvnSection', 'EvnVizitPL', 'EvnVizitPLStom')
						and EvnPrescr_id = :EvnPrescr_id
						/*and e_child.Evn_setDT <= :EvnUsluga_setDT and (e_child.Evn_disDT >= :EvnUsluga_setDT OR e_child.Evn_disDT IS NULL)*/ -- актуальное
				";

				$result = $this->getFirstResultFromQuery($query, $queryParams);
				$queryParams['EvnUsluga_pid'] = !$result ? null : $result;
			}

			$queryParams['PayType_id'] = null;
			if (!empty($data['PayType_id'])) {
				$queryParams['PayType_id'] = $data['PayType_id'];
			}

			$queryParams['PayType_SysNickOMS'] = getPayTypeSysNickOMS();
			if ($this->getRegionNick() == 'kz' && !empty($queryParams['EvnUsluga_pid'])) {
				$resp = $this->getPayTypeByEvnUslugaPid(array('EvnUsluga_pid' => $queryParams['EvnUsluga_pid']));
				if (!empty($resp['Error_Msg'])) {
					$trans_good = false;
					$trans_result = array($resp);
					return $trans_result;
				}
				$queryParams['PayType_id'] = $resp['PayType_id'];
			}

			$query = "
				declare
					@pt bigint,
					@Res bigint,
					@EvnPrescr_id bigint,
					@EvnUsluga_pid bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @pt = case when :PayType_id is null then (
					select top 1 PayType_id from v_PayType with (nolock) where PayType_SysNick = :PayType_SysNickOMS
				) else :PayType_id end;
				set @EvnPrescr_id = :EvnPrescr_id;
				set @EvnUsluga_pid = :EvnUsluga_pid
				set @Res = :EvnUsluga_id;
				exec " . $procedure . "
					@".$data['object']."_id = @Res output,
					@".$data['object']."_pid = @EvnUsluga_pid,
					@Lpu_id = :Lpu_id,
					@Server_id = :Server_id,
					@PersonEvn_id = :PersonEvn_id,
					@".$data['object']."_setDT = :EvnUsluga_setDT,
					@PayType_id = @pt,
					@Usluga_id = :Usluga_id,
					@".$data['object']."_Kolvo = 1,
					@".$data['object']."_isCito = :Usluga_isCito,
					@".$time_table."_id = :time_table_id,
					{$add_param}
					@UslugaPlace_id = :UslugaPlace_id,
					@LpuSection_uid = :LpuSection_uid,
					@MedPersonal_id = :MedPersonal_id,
					@UslugaComplex_id = :UslugaComplex_id,
					@EvnDirection_id = :EvnDirection_id,
					@Diag_id = :Diag_id,
					@".$data['object']."_Result = :EvnUsluga_Result,
					@EvnPrescr_id = @EvnPrescr_id,
					@EvnPrescrTimetable_id = NULL,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as EvnUsluga_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			/*
			echo getDebugSql($query, $queryParams);
			exit;
			*/
			$result = $this->db->query($query, $queryParams);

			if ( is_object($result) ) {
				$response = $result->result('array');
				if ( !is_array($response) || count($response) == 0 ) {
					$trans_good = false;
					$trans_result = array(0 => array('Error_Msg' => 'Ошибка при сохранении заказа комплексной услуги!'));
				}
				else
				{
					$trans_result = $response;
				}
			}
			else {
				$trans_good = false;
				$trans_result = array(0 => array('Error_Msg' => 'Ошибка при сохранении заказа комплексной услуги'));
			}
		}

		/*if ( $trans_good === true ) {
			$this->db->trans_commit();
		}
		else {
			$this->db->trans_rollback();
		}*/
		return $trans_result;
	}

	/**
	 * Определение типа оплаты услуги по событию-предку
	 */
	function getPayTypeByEvnUslugaPid($data) {
		if (empty($data['EvnUsluga_pid'])) {
			return array('Error_Msg' => 'Не указан идентификатор родительского события', 'Error_Code' => 500);
		}
		$params = array('EvnUsluga_pid' => $data['EvnUsluga_pid']);

		$query = "
			select
				EvnClass_SysNick
			from v_Evn with (nolock)
			where
				Evn_id = :EvnUsluga_pid";
		$EvnClass_SysNick = $this->getFirstResultFromQuery($query, $params);
		if (!in_array($EvnClass_SysNick, array('EvnVizitPL','EvnVizitPLStom','EvnPS','EvnSection'))) {
			return array('Error_Msg' => 'Невозможно определить тип финансирования', 'Error_Code' => 500);
		}

		$query = "
			select top 1
				PT.PayType_id,
				PT.PayType_Code,
				PT.PayType_SysNick
			from v_{$EvnClass_SysNick} Evn with (nolock)
				left join v_PayType PT with (nolock) on PT.PayType_id = Evn.PayType_id
			where
				Evn.{$EvnClass_SysNick}_id = :EvnUsluga_pid
		";
		$resp = $this->getFirstRowFromQuery($query, $params);
		if (empty($resp) || !is_array($resp)) {
			return array('Error_Msg' => 'Ошибка при опредении типа финансирования', 'Error_Code' => 500);
		}
		return $resp;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function getEvnUslugaParams($data) {
		$params = array('_id','_pid','_setDate','_setTime','_disDate','_disTime');
		$objects = array('EvnUsluga','EvnUslugaCommon','EvnUslugaStom','EvnUslugaOper','EvnUslugaPar','EvnUslugaDispDop','EvnUslugaDispOrp');

		$response = array();
		$object = '';

		foreach($objects as $tmp_object) {
			if (array_key_exists($tmp_object.'_id', $data)) {
				$object = $tmp_object;
				break;
			}
		}
		foreach($params as $param) {
			if (array_key_exists($object.$param, $data)) {
				$response['EvnUsluga'.$param] = $data[$object.$param];
			}
		}
		return $response;
	}

	/**
	 * Получение данных о параклинической услуге
	 * Используется для создания листа согласования (ApprovalList_model)
	 * @param $data
	 * @return array|false
	 */
	function getEvnUslugaParInfo($data) {
		return $this->queryResult("
			select top 1
				EUP.EvnUslugaPar_id,
				convert(varchar(10), EUP.EvnUslugaPar_setDate, 104) as EvnUslugaPar_setDate,
				RTRIM(ISNULL(ps.Person_Surname, '')) + ' ' + SUBSTRING(ISNULL(ps.Person_Firname, ''),1,1) + ' ' + SUBSTRING(ISNULL(ps.Person_Secname, ''),1,1) as Person_Fin,
				uc.UslugaComplex_Name,
				els.MedService_id,
				ms.LpuSection_id,
				eup.Lpu_id,
				eup.Person_id,
				eup.LpuSection_uid,
				eup.Lpu_oid,
				eup.MedStaffFact_id
			from
				v_EvnUslugaPar EUP (nolock)
				inner join v_PersonState ps (nolock) on ps.Person_id = EUP.Person_id
				inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eup.UslugaComplex_id
				left join v_UslugaTest ut (nolock) on ut.UslugaTest_pid = eup.EvnUslugaPar_id
				left join v_EvnLabSample els (nolock) on els.EvnLabSample_id = isnull(ut.EvnLabSample_id, eup.EvnLabsample_id)
				left join v_MedService ms (nolock) on ms.MedService_id = els.MedService_id
			where
				EUP.EvnUslugaPar_id = :EvnUslugaPar_id
		", array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id']
		));
	}

    /**
     * Проверка даты и времени услуги - не должно быть раньше начала периода лечения и больше даты выписки (если такая есть)
	 */
	function CheckEvnUslugaDate($data)
    {
		$data = array_merge($data, $this->getEvnUslugaParams($data));
		$params = array(
			'EvnUsluga_setDT' => $data['EvnUsluga_setDate'].' '.(empty($data['EvnUsluga_setTime'])?'00:00':$data['EvnUsluga_setTime']),
			'EvnUsluga_disDT' => null
		);
		if (!empty($data['EvnUsluga_disDate'])) {
			$params['EvnUsluga_disDT'] = $data['EvnUsluga_disDate'].' '.(empty($data['EvnUsluga_disTime'])?'00:00':$data['EvnUsluga_disTime']) ;
		}

		switch(true){
			case !empty($data['EvnUslugaCommon_pid']):
				$data['EvnUsluga_pid'] = !empty($data['EvnUslugaCommon_pid'])?$data['EvnUslugaCommon_pid']:null;
				break;
			case !empty($data['EvnUslugaOper_pid']):
				$data['EvnUsluga_pid'] = !empty($data['EvnUslugaOper_pid'])?$data['EvnUslugaOper_pid']:null;
				break;
			case !empty($data['EvnUslugaDispDop_id']):
				$data['EvnUsluga_pid'] = !empty($data['EvnUslugaDispDop_id'])?$data['EvnUslugaDispDop_id']:null;
				break;
			default:
				$data['EvnUsluga_pid'] = null;
				break;
		}

		$EvnUsluga_setDT = DateTime::createFromFormat('Y-m-d H:i', $params['EvnUsluga_setDT']);
		$EvnUsluga_disDT = DateTime::createFromFormat('Y-m-d H:i', $params['EvnUsluga_disDT']);

		if (!$EvnUsluga_setDT || (!empty($params['EvnUsluga_disDT']) && !$EvnUsluga_disDT)) {
			return $this->createError('', 'Неправильный формат периода выполнения услуги');
		}

		if (!empty($params['EvnUsluga_disDT']) && $EvnUsluga_disDT < $EvnUsluga_setDT) {
			return $this->createError('', 'Дата окончания выполнения услуги не может быть меньше даты начала выполнения услуги');
		}

		if (!empty($data['EvnUsluga_pid'])) {
			$UslugaComplex_FullName = '';
			if (!empty($data['UslugaComplex_id'])) {
				$UslugaComplex_FullName = $this->getFirstResultFromQuery("
					select top 1
						('«'+UslugaComplex_Code+'. '+UslugaComplex_Name+'»') as UslugaComplex_FullName
					from v_UslugaComplex with(nolock)
					where UslugaComplex_id = :UslugaComplex_id
				", array('UslugaComplex_id' => $data['UslugaComplex_id']));
				if ($UslugaComplex_FullName === false) {
					$this->createError('', 'Ошибка получения наименования услуги');
				}
			}

			$query = "
				select top 1
					convert(varchar(10), EvnR.Evn_setDT, 120) + ' ' + convert(varchar(5), EvnR.Evn_setDT, 108) as Evn_setDT
					,convert(varchar(10), EvnR.Evn_disDT, 120) + ' ' + convert(varchar(5), EvnR.Evn_disDT, 108) as Evn_disDT
					,EvnR.EvnClass_SysNick
					,EvnR.Evn_rid
					,EvnR.Evn_id
				from  v_Evn Evn with (nolock)
				inner join v_Evn EvnR with (nolock) on EvnR.Evn_id = Evn.Evn_rid
				where Evn.Evn_id = :Evn_id
			";

			$evn = $this->getFirstRowFromQuery($query, array('Evn_id' => $data['EvnUsluga_pid']));
			if ($evn === false) {
				return $this->createError('', 'Ошибка при получении родительского события услуги');
			}

			if (in_array($evn['EvnClass_SysNick'], array('EvnPL','EvnPLStom'))) {

				switch ($evn['EvnClass_SysNick']){
					case 'EvnPL':
						$prefix = 'EvnPL';
						break;
					case 'EvnPLStom':
						$prefix = 'EvnPLStom';
						break;
				}

				$resp = $this->getFirstRowFromQuery("
					select top 1
						convert(varchar(10), Evn.{$prefix}_disDT, 120) + ' ' + convert(varchar(5), Evn.{$prefix}_disDT, 108) as Evn_disDT,
						Evn.{$prefix}_isFinish as Evn_isFinish
					from v_{$prefix} Evn with(nolock)
					where Evn.{$prefix}_id = :Evn_id
				", array('Evn_id' => $evn['Evn_id']));
				if ($evn === false) {
					return $this->createError('', 'Ошибка при получении родительского события услуги');
				}

				$evn['Evn_disDT'] = $resp['Evn_disDT'];
				$evn['Evn_isFinish'] = $resp['Evn_isFinish'];
			}

			$Evn_setDT = DateTime::createFromFormat('Y-m-d H:i', $evn['Evn_setDT']);
			$Evn_disDT = DateTime::createFromFormat('Y-m-d H:i', $evn['Evn_disDT']);

			if (!$Evn_setDT || (!empty($evn['Evn_disDT']) && !$Evn_disDT)) {
				return $this->createError('', 'Неправильный формат периода выполнения услуги');
			}

			if (empty($data['ignoreParentEvnDateCheck']) || !$data['ignoreParentEvnDateCheck']) {

				//Если ТАП закрыт то услуга должа польностью попадать в случай, иначе начало услуги должно быть после начала ТАП
				$alert_msg = "Период выполнения услуги {$UslugaComplex_FullName} превышает период случая лечения.";
				if ($EvnUsluga_setDT < $Evn_setDT ) {
					$alert_msg = "Дата начала услуги {$UslugaComplex_FullName} раньше даты начала случая лечения.";
					$this->_setAlertMsg($alert_msg);
					return $this->createError(109, 'YesNo');
				}
				if (!empty($params['EvnUsluga_disDT']) && ($EvnUsluga_disDT < $Evn_setDT || (!empty($evn['Evn_isFinish']) && $evn['Evn_isFinish'] === '2' && !empty($evn['Evn_disDT']) && $EvnUsluga_disDT > $Evn_disDT))) {
					$this->_setAlertMsg($alert_msg);
					return $this->createError(109, 'YesNo');
				}
			}
		}

    	return array(array('success' => true, 'Error_Msg' => ''));
    }

    /**
     * Проверка местр работы врачей операционной бригады - места работы должны быть открыты на дату начала операции
	 */
	function CheckUslugaOperBrig($data)
    {
		$data = array_merge($data, $this->getEvnUslugaParams($data));
		$params = array(
			'EvnUsluga_setDT' => $data['EvnUsluga_setDate'].' '.(empty($data['EvnUsluga_setTime'])?'00:00':$data['EvnUsluga_setTime']),
			'EvnUslugaOper_id' => $data['EvnUslugaOper_id']
		);

		$query = "
			select
				EvnUslugaOperBrig_id,
				MSF.Person_Fio + ' - ' + post.name as MedStaffFact_Name
			from
				v_EvnUslugaOperBrig EUOB with (nolock)
				left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = EUOB.MedStaffFact_id
				left join persis.v_post post with (nolock) on MSF.post_id = post.id
			where
				EvnUslugaOper_id = :EvnUslugaOper_id
				and WorkData_endDate < :EvnUsluga_setDT
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			$response = $result->result('array');
			if ( count($response) > 0) {
				$WorkPlaceList = array();
				foreach ($response as $key => $value) {
					array_push($WorkPlaceList, $value['MedStaffFact_Name']);
				}

				return $this->createError('', 'В операционной бригаде указаны закрытые места работы врачей на дату выполнения операции: '.implode(', ',$WorkPlaceList));
			}
		}

    	return array(array('success' => true, 'Error_Msg' => ''));
    }

	/**
	 * @param $data
	 * @return array|bool
	 */
	function saveEvnUslugaOper($data) {
		$this->isAllowTransaction = true;
		try {
			if ( !$this->beginTransaction() ) {
				$this->isAllowTransaction = false;
				throw new Exception('Не удалось запустить транзакцию', 500);
			}
			if ( empty($data['EvnUslugaOper_id']) || $data['EvnUslugaOper_id'] <= 0 ) {
				$procedure = 'p_EvnUslugaOper_ins';
				$data['EvnUslugaOper_id'] = null;
			}
			else {
				$procedure = 'p_EvnUslugaOper_upd';
			}

			if ( isset($data['EvnUslugaOper_setTime']) ) {
				$data['EvnUslugaOper_setDate'] .= ' ' . $data['EvnUslugaOper_setTime'] . ':00:000';
			}

			if ( isset($data['EvnUslugaOper_BallonBegTime']) ) {
				$data['EvnUslugaOper_BallonBegDate'] .= ' ' . $data['EvnUslugaOper_BallonBegTime'] . ':00:000';
			}

			if ( isset($data['EvnUslugaOper_CKVEndTime']) ) {
				$data['EvnUslugaOper_CKVEndDate'] .= ' ' . $data['EvnUslugaOper_CKVEndTime'] . ':00:000';
			}

			if (empty($data['EvnUslugaOper_pid']) && !empty($data['EvnDirection_id'])) {
				// если сохраняем из оперблока (по направлению), значит определим родителя - актуальное движение на дату выполнению услуги.
				$resp = $this->queryResult("
					select top 1
						es.EvnSection_id,
						es.Diag_id, 
						case when EvnSection_IsPriem = 2 then 1 else 0 end as EvnSection_IsPriem
					from
						v_EvnSection es (nolock)
					where
						es.Lpu_id = :Lpu_id
						and es.Person_id = :Person_id
						and es.EvnSection_setDT <= :EvnUslugaOper_setDate and (es.EvnSection_disDT >= :EvnUslugaOper_setDate OR es.EvnSection_disDT IS NULL)
					order by EvnSection_IsPriem asc
				", array(
					'Person_id' => $data['Person_id'],
					'Lpu_id' => $data['Lpu_id'],
					'EvnUslugaOper_setDate' => $data['EvnUslugaOper_setDate']
				));

				if (!empty($resp[0]['EvnSection_id'])) {
					$data['EvnUslugaOper_pid'] = $resp[0]['EvnSection_id'];
				} else {
					$data['EvnUslugaOper_pid'] = null; // если нет актуального движения, то не к чему привязывать
					// throw new Exception('Не удалось определить родительское событие');
				}
			}

			if ( !isset($data['EvnUslugaOper_pid']) ) {
				$data['EvnUslugaOper_pid'] = $data['EvnUslugaOper_rid'];
			}

			if (!empty($data['EvnUslugaOper_pid'])) {
				$query = "
					select
						coalesce(EPL.EvnPL_IsPaid, EV.EvnVizit_IsPaid, ES.EvnSection_IsPaid, 1) as EvnUsluga_IsPaid,
						coalesce(EPL.EvnPL_IndexRep, EV.EvnVizit_IndexRep, ES.EvnSection_IndexRep, 0) as EvnUsluga_IndexRep,
						coalesce(EPL.EvnPL_IndexRepInReg, EV.EvnVizit_IndexRepInReg, ES.EvnSection_IndexRepInReg, 1) as EvnUsluga_IndexRepInReg
					from
						v_Evn e (nolock)
						left join v_EvnVizit EV with (nolock) on EV.EvnVizit_id = e.Evn_id
						left join v_EvnPL EPL with (nolock) on EPL.EvnPL_id = e.Evn_pid
						left join v_EvnSection ES with (nolock) on ES.EvnSection_id = e.Evn_id
					where
						e.Evn_id = :EvnUslugaOper_pid
				";

				$result = $this->db->query($query, $data);
				if (is_object($result)) {
					$resp = $result->result('array');
					if (
						!($this->getRegionNick() == 'perm' && (!empty($data['ignorePaidCheck']) || $resp[0]['EvnUsluga_IndexRep'] >= $resp[0]['EvnUsluga_IndexRepInReg']))
						&& !($this->getRegionNick() == 'ufa' && isSuperadmin())
						&& !empty($resp[0]['EvnUsluga_IsPaid']) && $resp[0]['EvnUsluga_IsPaid'] == 2
					) {
						throw new Exception('Нельзя добавить услугу, т.к. случай оплачен');
					}
				}
			}

			if ( !empty($data['EvnUslugaOper_disDate']) && !empty($data['EvnUslugaOper_disTime']) ) {
				$data['EvnUslugaOper_disDate'] .= ' ' . $data['EvnUslugaOper_disTime'] . ':00:000';
			}
			if ( empty($data['EvnUslugaOper_disDate']) ) {
				$data['EvnUslugaOper_disDate'] = $data['EvnUslugaOper_setDate'];
			}

			if(empty($data['TreatmentConditionsType_id']))
			{
				$data['LpuUnitType_SysNick'] = null;

				if(isset($data['EvnUslugaOper_pid']))
				{
					$q = '
						select lu.LpuUnitType_SysNick from v_EvnSection es WITH (NOLOCK)
						inner join v_LpuSection ls WITH (NOLOCK) on ls.LpuSection_id = es.LpuSection_id
						inner join v_LpuUnit lu WITH (NOLOCK) on lu.LpuUnit_id = ls.LpuUnit_id
						where es.EvnSection_id = :EvnUslugaOper_pid
					';
					$r = $this->db->query($q, $data);
					if ( is_object($r) ) {
						$tmp = $r->result('array');
						if(count($tmp) > 0)
						{
							$data['LpuUnitType_SysNick'] = $tmp[0]['LpuUnitType_SysNick'] ;
						}
					}
				}

				switch ( true )
				{
					case (in_array($data['LpuUnitType_SysNick'],array('polka','hstac','pstac'))):
						$data['TreatmentConditionsType_id'] = 1; //Амбулаторно
					break;
					case ($data['LpuUnitType_SysNick'] == 'stac'):
						$data['TreatmentConditionsType_id'] = 2; //Стационарно (Дневной стационар при стационаре	dstac сюда же?)
					break;
					default:
						$data['TreatmentConditionsType_id'] = 3;
					break;
				}
			}
			
			if ( !empty($data['IsCardioCheck']) && $data['IsCardioCheck'] == 1 ) {			
				$this->load->model('Options_model');
				
				$euo_ballonbeg_control = $this->Options_model->getOptionsGlobals($data, 'euo_ballonbeg_control');
				if ($euo_ballonbeg_control == 2) {
					if ( empty($data['EvnUslugaOper_BallonBegDate']) && empty($data['ignoreBallonBegCheck']) ) {
						$this->_setAlertMsg('Не заполнены Дата и время начала раздувания баллона. Сохранить?');
						$this->_saveResponse['data'] = array();
						throw new Exception('YesNo', 110);
					}
				} elseif ($euo_ballonbeg_control == 3) {
					if ( empty($data['EvnUslugaOper_BallonBegDate']) ) {
						throw new Exception('Не заполнены Дата и время начала раздувания баллона');
					}
				}
				
				$euo_ckvend_control = $this->Options_model->getOptionsGlobals($data, 'euo_ckvend_control');
				if ($euo_ckvend_control == 2) {
					if ( empty($data['EvnUslugaOper_CKVEndDate']) && empty($data['ignoreCKVEndCheck']) ) {
						$this->_setAlertMsg('Не заполнены Дата и время окончания ЧКВ. Сохранить?');
						$this->_saveResponse['data'] = array();
						throw new Exception('YesNo', 111);
					}
				} elseif ($euo_ckvend_control == 3) {
					if ( empty($data['EvnUslugaOper_CKVEndDate']) ) {
						throw new Exception('Не заполнены Дата и время окончания ЧКВ');
					}
				}			
			}

			/*$parentPayType = $this->getPayTypeByEvnUslugaPid(array('EvnUsluga_pid' => $data['EvnUslugaOper_pid']));
			if (!empty($parentPayType['Error_Msg'])) {
				throw new Exception($parentPayType['Error_Msg']);
			}*/

			// https://redmine.swan.perm.ru/issues/90534#note-10
			/*if ($this->regionNick == 'perm' && $parentPayType['PayType_SysNick'] == 'oms') {
				$tariff_count = $this->getUslugaComplexTariffCount(array(
					'Person_id' => $data['Person_id'],
					'Date' => $data['EvnUslugaOper_setDate'],
					'PayType_id' => $parentPayType['PayType_id'],
					'UslugaComplex_id' => $data['UslugaComplex_id']
				));
				if ($tariff_count === false) {
					throw new Exception("Ошибка при проверке наличия тарифов");
				}
				if ($tariff_count == 0) {
					$this->addWarningMsg('Выполнение услуги: На данную услугу нет тарифа!');
				}
			}*/

			if (!empty($data['EvnDirection_id']) && empty($data['EvnUslugaOper_pid'])) {
				// если услуга по направлению (из опреблока), то надо привязать её к движению
				$data['EvnUslugaOper_pid'] = $this->getFirstResultFromQuery("
					select top 1
						EvnDirection_pid
					from
						v_EvnDirection_all (nolock)
					where
						EvnDirection_id = :EvnDirection_id
				", array(
					'EvnDirection_id' => $data['EvnDirection_id']
				), true);
			}

			$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000),
					@morbus_id bigint;
				set @Res = :EvnUslugaOper_id;
				SET @morbus_id = (SELECT morbus_id FROM v_evn with (nolock) WHERE evn_id = :EvnUslugaOper_pid);
				exec " . $procedure . "
					@EvnUslugaOper_id = @Res output,
					@EvnUslugaOper_pid = :EvnUslugaOper_pid,
					@EvnDirection_id = :EvnDirection_id,
					@Lpu_id = :Lpu_id,
					@Server_id = :Server_id,
					@PersonEvn_id = :PersonEvn_id,
					@EvnUslugaOper_setDT = :EvnUslugaOper_setDT,
					@EvnUslugaOper_disDT = :EvnUslugaOper_disDT,
					@PayType_id = :PayType_id,
					@EvnUslugaOper_IsVMT = :EvnUslugaOper_IsVMT,
					@EvnUslugaOper_IsMicrSurg = :EvnUslugaOper_IsMicrSurg,
					@EvnUslugaOper_IsOpenHeart = :EvnUslugaOper_IsOpenHeart,
					@EvnUslugaOper_IsArtCirc = :EvnUslugaOper_IsArtCirc,
					@Usluga_id = :Usluga_id,
					@UslugaComplex_id = :UslugaComplex_id,
					@MedPersonal_id = :MedPersonal_id,
					@MedStaffFact_id = :MedStaffFact_id,
					@Morbus_id = :Morbus_id,
					@UslugaPlace_id = :UslugaPlace_id,
					@Lpu_uid = :Lpu_uid,
					@LpuSection_uid = :LpuSection_uid,
					@Org_uid = :Org_uid,
					@EvnUslugaOper_Kolvo = :EvnUslugaOper_Kolvo,
					@EvnUslugaOper_IsEndoskop = :EvnUslugaOper_IsEndoskop,
					@EvnUslugaOper_IsLazer = :EvnUslugaOper_IsLazer,
					@EvnUslugaOper_IsKriogen = :EvnUslugaOper_IsKriogen,
					@EvnUslugaOper_IsRadGraf = :EvnUslugaOper_IsRadGraf,
					@OperType_id = :OperType_id,
					@OperDiff_id = :OperDiff_id,
					@TreatmentConditionsType_id = :TreatmentConditionsType_id,
					@EvnUslugaOper_CoeffTariff = :EvnUslugaOper_CoeffTariff,
					@EvnUslugaOper_IsModern = :EvnUslugaOper_IsModern,
					@MesOperType_id = :MesOperType_id,
					@UslugaComplexTariff_id = :UslugaComplexTariff_id,
					@DiagSetClass_id = :DiagSetClass_id,
					@Diag_id = :Diag_id,
					@EvnPrescrTimetable_id = null,
					@EvnPrescr_id = :EvnPrescr_id,
					@MedSpecOms_id = :MedSpecOms_id,
					@LpuSectionProfile_id = :LpuSectionProfile_id,
					@EvnUslugaOper_BallonBegDT = :EvnUslugaOper_BallonBegDT,
					@EvnUslugaOper_CKVEndDT = :EvnUslugaOper_CKVEndDT,
					@EvnUslugaOper_IsOperationDeath = :EvnUslugaOper_IsOperationDeath,
					@EvnUslugaOper_Price = :EvnUslugaOper_Price,
					@UslugaExecutionType_id = :UslugaExecutionType_id,
					@UslugaExecutionReason_id = :UslugaExecutionReason_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as EvnUslugaOper_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$queryParams = array(
				'EvnUslugaOper_id' => $data['EvnUslugaOper_id'],
				'EvnUslugaOper_pid' => $data['EvnUslugaOper_pid'],
				'EvnDirection_id' => (!empty($data['EvnDirection_id']) ? $data['EvnDirection_id'] : NULL),
				'EvnUslugaOper_IsVMT' => $data['EvnUslugaOper_IsVMT'],//(empty($data['EvnUslugaOper_IsVMT']) ? 2 : $data['EvnUslugaOper_IsVMT']),
				'EvnUslugaOper_IsMicrSurg' => $data['EvnUslugaOper_IsMicrSurg'],
				'EvnUslugaOper_IsOpenHeart' => $data['EvnUslugaOper_IsOpenHeart'],
				'EvnUslugaOper_IsArtCirc' => $data['EvnUslugaOper_IsArtCirc'],
				'Lpu_id' => $data['Lpu_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => (!empty($data['PersonEvn_id']) ? $data['PersonEvn_id'] : NULL),
				'EvnUslugaOper_setDT' => $data['EvnUslugaOper_setDate'],
				'EvnUslugaOper_disDT' => $data['EvnUslugaOper_disDate'],
				'PayType_id' => $data['PayType_id'],
				'Usluga_id' => (!empty($data['Usluga_id']) ? $data['Usluga_id'] : NULL),
				'UslugaComplex_id' => (!empty($data['UslugaComplex_id']) ? $data['UslugaComplex_id'] : NULL),
				'MedPersonal_id' => (!empty($data['MedPersonal_id']) ? $data['MedPersonal_id'] : NULL),
				'MedStaffFact_id' => $data['MedStaffFact_id'],
				'UslugaPlace_id' => $data['UslugaPlace_id'],
				'Lpu_uid' => (!empty($data['Lpu_uid']) ? $data['Lpu_uid'] : NULL),
				'LpuSection_uid' => $data['LpuSection_uid'],
				'Org_uid' => $data['Org_uid'],
				'EvnUslugaOper_Kolvo' => $data['EvnUslugaOper_Kolvo'],
				'EvnUslugaOper_IsEndoskop' => $data['EvnUslugaOper_IsEndoskop'],
				'EvnUslugaOper_IsLazer' => $data['EvnUslugaOper_IsLazer'],
				'EvnUslugaOper_IsKriogen' => $data['EvnUslugaOper_IsKriogen'],
				'EvnUslugaOper_IsRadGraf' => $data['EvnUslugaOper_IsRadGraf'],
				'OperType_id' => $data['OperType_id'],
				'OperDiff_id' => $data['OperDiff_id'],
				'TreatmentConditionsType_id' => $data['TreatmentConditionsType_id'],
				'EvnUslugaOper_CoeffTariff' => (!empty($data['EvnUslugaOper_CoeffTariff']) ? $data['EvnUslugaOper_CoeffTariff'] : NULL),
				'EvnUslugaOper_IsModern' => (!empty($data['EvnUslugaOper_IsModern']) ? $data['EvnUslugaOper_IsModern'] : NULL),
				'MesOperType_id' => (!empty($data['MesOperType_id']) ? $data['MesOperType_id'] : NULL),
				'UslugaComplexTariff_id' => (!empty($data['UslugaComplexTariff_id']) ? $data['UslugaComplexTariff_id'] : NULL),
				'DiagSetClass_id' => (!empty($data['DiagSetClass_id']) ? $data['DiagSetClass_id'] : NULL),
				'Diag_id' => (!empty($data['Diag_id']) ? $data['Diag_id'] : NULL),
				'EvnPrescr_id' => (!empty($data['EvnPrescr_id']) ? $data['EvnPrescr_id'] : NULL),
				'MedSpecOms_id' => (!empty($data['MedSpecOms_id']) ? $data['MedSpecOms_id'] : NULL),
				'LpuSectionProfile_id' => (!empty($data['LpuSectionProfile_id']) ? $data['LpuSectionProfile_id'] : NULL),
				'EvnUslugaOper_BallonBegDT' => (!empty($data['EvnUslugaOper_BallonBegDate']) ? $data['EvnUslugaOper_BallonBegDate'] : NULL),
				'EvnUslugaOper_CKVEndDT' => (!empty($data['EvnUslugaOper_CKVEndDate']) ? $data['EvnUslugaOper_CKVEndDate'] : NULL),
				'EvnUslugaOper_IsOperationDeath' => (!empty($data['EvnUslugaOper_IsOperationDeath']) ? $data['EvnUslugaOper_IsOperationDeath'] : NULL),
				'EvnUslugaOper_Price' => (!empty($data['EvnUslugaOper_Price']) ? $data['EvnUslugaOper_Price'] : NULL),
				'UslugaExecutionType_id' => (!empty($data['UslugaExecutionType_id']) ? $data['UslugaExecutionType_id'] : NULL),
				'UslugaExecutionReason_id' => (!empty($data['UslugaExecutionReason_id']) ? $data['UslugaExecutionReason_id'] : NULL),
				'pmUser_id' => $data['pmUser_id']
			);

			// предварительная проверка действительно ли есть заполнена услуга, которую нужно сохранить // todo: потом должна остаться только UslugaComplex_id
			if (empty($queryParams['Usluga_id']) && empty($queryParams['UslugaComplex_id'])) {
				throw new Exception('Ошибка при сохранении услуг: Услуга не может быть пустой.');
			}

	        if (isset($data['Morbus_id']) && ($data['Morbus_id'])) {
	            $queryParams['Morbus_id'] = $data['Morbus_id'];
	        } else {
	            $queryParams['Morbus_id'] = null;
	        }

			$this->load->helper('PersonNotice');
			//Инициализация хелпера рассылки сообщений о смене статуса
			$PersonNotice = new PersonNoticeEvn($data['Person_id']);
			$PersonNotice->loadPersonInfo($data['PersonEvn_id'], $data['Server_id']);

			//Начинаем отслеживать статусы события EvnUslugaOper
			$PersonNotice->setEvnClassSysNick('EvnUslugaOper');
			$PersonNotice->setEvnId($data['EvnUslugaOper_id']);
			$PersonNotice->doStatusSnapshotFirst();

			// если услуга по направлениюе, то
			if (!empty($data['EvnDirection_id'])) {
				// направление должно стать обслуженным
				$this->load->model('Evn_model');
				$this->Evn_model->updateEvnStatus(array(
					'Evn_id' => $data['EvnDirection_id'],
					'EvnStatus_SysNick' => 'Serviced',
					'EvnClass_SysNick' => 'EvnDirection',
					'pmUser_id' => $data['pmUser_id']
				));
			}

			$oldEvnPrescrId = null;
			$oldEvnPrescrIsExec = null;
			if ($data['EvnUslugaOper_id'] > 0) {
				$oldEvnPrescrData = $this->queryResult('
					select
						euo.EvnPrescr_id,
						ep.EvnPrescr_IsExec
					from
						v_EvnUslugaOper euo with (nolock)
						left join v_EvnPrescr ep (nolock) on ep.EvnPrescr_id = euo.EvnPrescr_id
					where
						euo.EvnUslugaOper_id = :EvnUslugaOper_id
				', array(
					'EvnUslugaOper_id' => $data['EvnUslugaOper_id'],
				));
				if (!empty($oldEvnPrescrData[0]['EvnPrescr_id'])) {
					$oldEvnPrescrId = $oldEvnPrescrData[0]['EvnPrescr_id'];
					$oldEvnPrescrIsExec = $oldEvnPrescrData[0]['EvnPrescr_IsExec'];
				}
			}
			//echo getDebugSQL($query, $queryParams);exit;
			$result = $this->db->query($query, $queryParams);
			if ( !is_object($result) ) {
				throw new Exception('Не удалось выполнить сохранение оперативной услуги');
			}
			$response = $result->result('array');
			if ( !empty($response[0]['Error_Msg']) ) {
				throw new Exception($response[0]['Error_Msg']);
			}

			if ( !empty($data['UslugaMedType_id']) ) {
				$this->saveUslugaMedTypeLink($response[0]['EvnUslugaOper_id'], $data['UslugaMedType_id']);
			}

			$this->load->model('EvnPrescr_model', 'EvnPrescr_model');
			if ($oldEvnPrescrId != $data['EvnPrescr_id']) {
				if ($oldEvnPrescrId > 0) {
					// Отмена выполнения назначения
					$tmp = $this->EvnPrescr_model->rollbackEvnPrescrExecution(array(
						'EvnPrescr_id' => $oldEvnPrescrId,
						'pmUser_id' => $data['pmUser_id'],
					));
					if (!empty($tmp[0]['Error_Msg'])) {
						//нужно откатить транзакцию
						throw new Exception($tmp[0]['Error_Msg']);
					}
				}
				if ($data['EvnPrescr_id'] > 0) {
					// помечаем назначение как выполненное
					$tmp = $this->EvnPrescr_model->execEvnPrescr(array(
						'EvnPrescr_id' => $data['EvnPrescr_id'],
						'pmUser_id' => $data['pmUser_id'],
					));
					if (!empty($tmp[0]['Error_Msg'])) {
						//нужно откатить транзакцию
						throw new Exception($tmp[0]['Error_Msg']);
					}
				}
			} else if (!empty($oldEvnPrescrId) && $oldEvnPrescrIsExec != 2) {
				// помечаем назначение как выполненное
				$tmp = $this->EvnPrescr_model->execEvnPrescr(array(
					'EvnPrescr_id' => $oldEvnPrescrId,
					'pmUser_id' => $data['pmUser_id'],
				));
				if (!empty($tmp[0]['Error_Msg'])) {
					//нужно откатить транзакцию
					throw new Exception($tmp[0]['Error_Msg']);
				}
			}

			if ( !$this->commitTransaction() ) {
				$this->isAllowTransaction = false;
				throw new Exception('Не удалось зафиксировать транзакцию', 500);
			}
		} catch (Exception $e) {
			$this->rollbackTransaction();
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
			$this->_saveResponse['Error_Code'] = $e->getCode();
			return $this->_saveResponse;
		}
		try {
			if ( !empty($data['EvnUslugaOper_pid'])
				&& !empty($data['ParentEvnClass_SysNick'])
				&& 'EvnSection' == $data['ParentEvnClass_SysNick']
			) {
				// пересчитать КСГ/КПГ/Коэф в движении
				$this->load->model('EvnSection_model');
				$this->EvnSection_model->recalcKSGKPGKOEF($data['EvnUslugaOper_pid'], $data['session'], array(
					'byEvnUslugaChange' => true
				));
			}
		} catch (Exception $e) {
			//$this->_setAlertMsg("<div>При перерасчете КСГ/КПГ произошла ошибка</div><div>{$e->getMessage()}</div>");
		}

		$PersonNotice->setEvnId($response[0]['EvnUslugaOper_id']);
		$PersonNotice->doStatusSnapshotSecond();
		$PersonNotice->processStatusChange();

		if (isset($this->_saveResponse['Warning_Msg'])) {
			$response[0]['Warning_Msg'] = $this->_saveResponse['Warning_Msg'];
		}
		if (isset($this->_saveResponse['Info_Msg'])) {
			$response[0]['Info_Msg'] = $this->_saveResponse['Info_Msg'];
		}

		return $response;
	}

	/**
	 * Проверки и другая логика перед сохранением общей услуги или пакета услуг
	 */
	private function _beforeSaveEvnUslugaCommonPackage($data) {
        $uslugaComplexIdList = array();
        $uslugaComplexRows = array();

        if (!empty($data['UslugaSelectedList']) && empty($data['EvnUslugaCommon_id'])) {
            $tmp = json_decode($data['UslugaSelectedList'], true);
            if (empty($tmp)) {
                throw new Exception('Нет выбранных услуг');
            }
            $UslugaComplex_list = array();
            foreach ($tmp as $row) {
                if (empty($row['UslugaComplex_id'])
                || !array_key_exists('UslugaComplexTariff_id', $row)
                || !array_key_exists('UslugaComplexTariff_UED', $row)
                || !array_key_exists('UslugaComplexTariff_UEM', $row)
                || !array_key_exists('UslugaComplexTariff_Tariff', $row)
                || empty($row['EvnUsluga_Kolvo'])
                ) {
                    throw new Exception('Неправильный формат списка отмеченных услуг');
                }
                if (empty($row['UslugaComplexTariff_id'])) {
                    $row['UslugaComplexTariff_id'] = NULL;
                }
                if (empty($row['UslugaComplexTariff_Tariff'])) {
                    $row['UslugaComplexTariff_Tariff'] = NULL;
                }
                $row['EvnUslugaCommon_Price'] = $row['UslugaComplexTariff_Tariff'];
                $row['EvnUslugaCommon_Kolvo'] = $row['EvnUsluga_Kolvo'];
                unset($row['EvnUsluga_Kolvo']);
                unset($row['UslugaComplexTariff_UED']);
                unset($row['UslugaComplexTariff_UEM']);
                unset($row['UslugaComplexTariff_Tariff']);
                $uslugaComplexRows[] = $row;
                $uslugaComplexIdList[] = $row['UslugaComplex_id'];
            }
        } else if (isset($data['UslugaComplex_id'])) {
			if (empty($data['EvnUslugaCommon_Kolvo'])) {
				throw new Exception('Поле Количество обязательно для заполнения.');
			}
			if (empty($data['PayType_id'])) {
				throw new Exception('Поле Вид оплаты обязательно для заполнения.');
			}
			if (empty($data['UslugaPlace_id'])) {
				throw new Exception('Поле Место оказания обязательно для заполнения.');
			}
			if ($this->regionNick == 'perm'
				&& 1 == $data['PayType_id']
				&& 1 != $data['UslugaPlace_id']
				&& (empty($data['EvnUslugaCommon_IsVizitCode']) || 2 != $data['EvnUslugaCommon_IsVizitCode'])
			) {
				if (empty($data['MedSpecOms_id'])) {
					throw new Exception('Поле Специальность обязательно для заполнения.');
				}
				if (empty($data['LpuSectionProfile_id'])) {
					throw new Exception('Поле Профиль обязательно для заполнения.');
				}
			}
            $row = array(
                'UslugaComplex_id' => $data['UslugaComplex_id'],
                'UslugaComplexTariff_id' => (empty($data['UslugaComplexTariff_id']) ? NULL : $data['UslugaComplexTariff_id']),
                'EvnUslugaCommon_Price' => (empty($data['EvnUslugaCommon_Price']) ? NULL : $data['EvnUslugaCommon_Price']),
                'EvnUslugaCommon_Kolvo' => $data['EvnUslugaCommon_Kolvo'],
            );
            $uslugaComplexRows[] = $row;
            $uslugaComplexIdList[] = $row['UslugaComplex_id'];
        } else {
            throw new Exception('Нет выбранных услуг');
        }
        $in_UslugaComplex_list = implode(',', $uslugaComplexIdList);

		// Если место выполнения - отделение ЛПУ и врач не указан, возвращаем ошибку о необходимости проверить поле
		if ( $data['UslugaPlace_id'] == 1 && empty($data['MedPersonal_id']) && empty($data['fromAPI']) && empty($data['fromOnko'])) {
			throw new Exception('Проверьте правильность заполнения поля "Врач, выполнивший услугу"');
		}
		$isUfa = ($this->getRegionNick() == 'ufa');
		$isEkb = ($this->getRegionNick() == 'ekb');
		$isPerm = ($this->getRegionNick() == 'perm');
		$isPskov = ($this->getRegionNick() == 'pskov');

		if ($isPerm && !empty($in_UslugaComplex_list) && !empty($data['EvnUslugaCommon_pid']) && (empty($data['EvnUslugaCommon_IsVizitCode']) || $data['EvnUslugaCommon_IsVizitCode'] != 2)) {
			// проверям что услуга не занесена как код посещения
			$EvnVizitPL_id = $this->getFirstResultFromQuery("
				select top 1
					EvnUsluga_id
				from
					v_EvnUsluga (nolock)
				where
					EvnUsluga_pid = :EvnUslugaCommon_pid
					and EvnUsluga_IsVizitCode = 2
					and UslugaComplex_id in ({$in_UslugaComplex_list})
			", $data);
			if (!empty($EvnVizitPL_id)) {
				throw new Exception('Услуга сохранена как код посещения, сохранение невозможно');
			}
		}

		if ($isPerm && !empty($in_UslugaComplex_list) && !empty($data['EvnUslugaCommon_pid']) && $data['ParentEvnClass_SysNick'] == 'EvnVizitPL') {
			$resp = $this->getFirstRowFromQuery("
				select top 1
					ST.ServiceType_SysNick,
					ST.ServiceType_Name,
					PT.PayType_id,
					PT.PayType_SysNick,
					convert(varchar(10), EVPL.EvnVizitPL_setDate, 120) as EvnVizitPL_setDate
				from
					v_EvnVizitPL EVPL with(nolock)
					left join v_ServiceType ST with(nolock) on ST.ServiceType_id = EVPL.ServiceType_id
					left join v_PayType PT with(nolock) on PT.PayType_id = EVPL.PayType_id
				where
					EvnVizitPL_id = :EvnUslugaCommon_pid
			", array('EvnUslugaCommon_pid' => $data['EvnUslugaCommon_pid']));
			if (!$resp) {
				throw new Exception('Ошибка при запросе посещения');
			}

			/*$query = "
				select top 1 UslugaComplex_Code
				from v_UslugaComplex with(nolock)
				where UslugaComplex_id in ({$in_UslugaComplex_list})
			";
			$result = $this->db->query($query);

			if (!is_object($result)) {
				throw new Exception('Ошибка при запросе кодов услуг');
			}
			$ignoreUslugaComplexTariffCountCheck = empty($data['ignoreUslugaComplexTariffCountCheck']) ? 0 : 1;

			$usluga_resp = $result->result('array');

			if ( !is_array($usluga_resp) || count($usluga_resp) == 0 ) {
				throw new Exception('Ошибка при запросе кодов услуг');
			}

			foreach($usluga_resp as $item) {
				if (
					$item['UslugaComplex_Code'] == 'B04.069.333'
					&& in_array($resp['ServiceType_SysNick'], array('home','neotl'))
					&& strtotime($resp['EvnVizitPL_setDate']) >= strtotime('01.01.2015')
				) {
					throw new Exception("Невозможно сохранить изменения. Услуга «B04.069.333» не может быть указана для выбранного места оказания «{$resp['ServiceType_SysNick']}»");
				}
			}*/
			foreach($uslugaComplexIdList as $UslugaComplex_id) {
				if ($resp['PayType_SysNick'] == 'oms') {
					$tariff_count = $this->getUslugaComplexTariffCount(array(
						'Person_id' => $data['Person_id'],
						'Date' => $data['EvnUslugaCommon_setDate'],
						'PayType_id' => $resp['PayType_id'],
						'UslugaComplex_id' => $UslugaComplex_id
					));
					if ($tariff_count === false) {
						throw new Exception("Ошибка при проверке наличия тарифов");
					}
					if ($tariff_count == 0) {
						$this->addWarningMsg('Выполнение услуги: На данную услугу нет тарифа!');
					}
				}
			}
		}

		if ($isPerm && !empty($in_UslugaComplex_list) && !empty($data['EvnUslugaCommon_pid']) && $data['ParentEvnClass_SysNick'] == 'EvnSection') {
			$UslugaComplex_Codes = array('A11.20.017.001', 'A11.20.017.002', 'A11.20.017.003', 'A11.20.030.001', 'A11.20.017'); // может быть только одна из этих услуг

			$resp_uc = $this->queryResult("
				select distinct top 2
					uc.UslugaComplex_id,
					uc.UslugaComplex_Code,
					uc.UslugaComplex_Name
				from
					v_UslugaComplex uc (nolock)
				where
					uc.UslugaComplex_Code in ('".implode("','", $UslugaComplex_Codes)."')
					and uc.UslugaComplex_id in ({$in_UslugaComplex_list})
			");
			if (count($resp_uc) > 1) {
				// сохраняем две услуги?
				throw new Exception("Добавление услуги {$resp_uc[0]['UslugaComplex_Code']} {$resp_uc[0]['UslugaComplex_Name']} недоступно. В случае лечения была выполнена другая услуга базовой программы ЭКО");
			} else if (count($resp_uc) > 0) {
				// если в движении есть ещё услуга ЭКО, то запрещаем добавлять ещё одну.
				$resp_eu = $this->queryResult("
					select top 1
						euc.EvnUslugaCommon_id
					from
						v_EvnUslugaCommon euc (nolock)
						inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = euc.UslugaComplex_id
					where
						euc.EvnUslugaCommon_pid = :EvnUslugaCommon_pid
						and uc.UslugaComplex_Code in ('".implode("','", $UslugaComplex_Codes)."')
						and (euc.EvnUslugaCommon_id <> :EvnUslugaCommon_id or :EvnUslugaCommon_id is null)
				", $data);
				if (!empty($resp_eu[0]['EvnUslugaCommon_id'])) {
					throw new Exception("Добавление услуги {$resp_uc[0]['UslugaComplex_Code']} {$resp_uc[0]['UslugaComplex_Name']} недоступно. В случае лечения была выполнена другая услуга базовой программы ЭКО");
				}
			}
		}

		if (in_array($this->regionNick, ['adygeya', 'khak', 'pskov']) && !empty($in_UslugaComplex_list) && !empty($data['EvnUslugaCommon_pid']) && $data['ParentEvnClass_SysNick'] == 'EvnSection') {
			$UslugaComplex_Codes = array('A11.20.017'); // может быть только одна из этих услуг

			$resp_uc = $this->queryResult("
				select distinct top 2
					uc.UslugaComplex_id,
					uc.UslugaComplex_Code,
					uc.UslugaComplex_Name
				from
					v_UslugaComplex uc (nolock)
				where
					uc.UslugaComplex_Code in ('".implode("','", $UslugaComplex_Codes)."')
					and uc.UslugaComplex_id in ({$in_UslugaComplex_list})
			");
			if (count($resp_uc) > 1) {
				// сохраняем две услуги?
				throw new Exception("КВС уже содержит услугу ЭКО, добавление новой услуги недоступно");
			} else if (count($resp_uc) > 0) {
				// если в движении есть ещё услуга ЭКО, то запрещаем добавлять ещё одну.
				$resp_eu = $this->queryResult("
					select top 1
						euc.EvnUslugaCommon_id
					from
						v_EvnUslugaCommon euc (nolock)
						inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = euc.UslugaComplex_id
					where
						euc.EvnUslugaCommon_pid = :EvnUslugaCommon_pid
						and uc.UslugaComplex_Code in ('".implode("','", $UslugaComplex_Codes)."')
						and (euc.EvnUslugaCommon_id <> :EvnUslugaCommon_id or :EvnUslugaCommon_id is null)
				", $data);
				if (!empty($resp_eu[0]['EvnUslugaCommon_id'])) {
					throw new Exception("КВС уже содержит услугу ЭКО, добавление новой услуги недоступно");
				}
			}
		}

		if ($isPerm && !empty($in_UslugaComplex_list) && !empty($data['EvnUslugaCommon_pid']) && $data['ParentEvnClass_SysNick'] == 'EvnSection') {
			$resp = $this->getFirstRowFromQuery("
				select top 1
					PT.PayType_id,
					PT.PayType_SysNick,
					convert(varchar(10), ES.EvnSection_setDate, 120) as EvnSection_setDate
				from
					v_EvnSection ES with(nolock)
					left join v_PayType PT with(nolock) on PT.PayType_id = ES.PayType_id
				where
					EvnSection_id = :EvnUslugaCommon_pid
			", array('EvnUslugaCommon_pid' => $data['EvnUslugaCommon_pid']));
			if (!$resp) {
				throw new Exception('Ошибка при запросе движения');
			}

			foreach($uslugaComplexIdList as $UslugaComplex_id) {
				if ($resp['PayType_SysNick'] == 'oms') {
					$tariff_count = $this->getUslugaComplexTariffCount(array(
						'Person_id' => $data['Person_id'],
						'Date' => $data['EvnUslugaCommon_setDate'],
						'PayType_id' => $resp['PayType_id'],
						'UslugaComplex_id' => $UslugaComplex_id
					));
					if ($tariff_count === false) {
						throw new Exception("Ошибка при проверке наличия тарифов");
					}
					if ($tariff_count == 0) {
						$this->addWarningMsg('Выполнение услуги: На данную услугу нет тарифа!');
					}
				}
			}
		}

		if ($isEkb && !empty($in_UslugaComplex_list) && !empty($data['EvnUslugaCommon_pid']) && (empty($data['EvnUslugaCommon_IsVizitCode']) || $data['EvnUslugaCommon_IsVizitCode'] != 2)) {
			// проверям что услуга не занесена как код посещения
			$EvnVizitPL_id = $this->getFirstResultFromQuery("
				select top 1
					EvnVizitPL_id
				from
					v_EvnVizitPL (nolock)
				where
					EvnVizitPL_id = :EvnUslugaCommon_pid
					and UslugaComplex_id  in ({$in_UslugaComplex_list})
			", $data);
			if (!empty($EvnVizitPL_id)) {
				throw new Exception('Услуга сохранена как код посещения, сохранение невозможно');
			}
			
			// Если в посещении добавили две или больше одинаковых услуги на одну и тужу дату, то выводить ошибку. Исключение услуги из группы 301 (refs #38517)
			$EvnUslugaCommon_id = $this->getFirstResultFromQuery("
				select top 1
					euc.EvnUslugaCommon_id
				from
					v_EvnVizitPL (nolock) epl
					inner join v_EvnUslugaCommon euc (nolock) on euc.EvnUslugaCommon_pid = epl.EvnVizitPL_id
					inner join r66.v_UslugaComplexPartitionLink ucpl (nolock) on ucpl.UslugaComplex_id = euc.UslugaComplex_id
					inner join r66.v_UslugaComplexPartition ucp (nolock) on ucp.UslugaComplexPartition_id = ucpl.UslugaComplexPartition_id
				where
					epl.EvnVizitPL_id = :EvnUslugaCommon_pid
					and euc.UslugaComplex_id  in ({$in_UslugaComplex_list})
					and euc.EvnUslugaCommon_setDate = :EvnUslugaCommon_setDate
					and ucp.UslugaComplexPartition_Code <> 301
					and (euc.EvnUslugaCommon_id <> :EvnUslugaCommon_id or :EvnUslugaCommon_id is null)
			", $data);
			if (!empty($EvnUslugaCommon_id)) {
				throw new Exception('Данная услуга уже добавлена, сохранение дубля невозможно');
			}
			
			// Если любая услуга отмечена SIGNRAO и нет в списке услуг движения услуг с UslugaComplexPartition_Name="Пребывание в РАО стационара", то выдавать ошибку, сохранение запретить
			$isSIGNRAO = false;
			
			$query = "
				select top 1
					UslugaComplex_id
				from
					r66.v_UslugaComplexPartitionLink (nolock)
				where
					UslugaComplex_id  in ({$in_UslugaComplex_list})
					and UslugaComplexPartitionLink_Signrao = 1
				
				union
				
				select top 1
					euc.UslugaComplex_id
				from
					v_EvnSection es with (nolock)
					inner join v_EvnUslugaCommon euc (nolock) on euc.EvnUslugaCommon_pid = es.EvnSection_id
					inner join r66.v_UslugaComplexPartitionLink ucpl (nolock) on ucpl.UslugaComplex_id = euc.UslugaComplex_id
				where
					es.EvnSection_id = :EvnUslugaCommon_pid
					and ucpl.UslugaComplexPartitionLink_Signrao = 1
					and (euc.EvnUslugaCommon_id <> :EvnUslugaCommon_id or :EvnUslugaCommon_id is null)
					
				union
				
				select top 1
					mu.UslugaComplex_id
				from
					v_EvnSection es with (nolock)
					inner join v_MesUsluga mu (nolock) on mu.Mes_id = es.Mes_sid
					inner join r66.v_UslugaComplexPartitionLink ucpl (nolock) on ucpl.UslugaComplex_id = mu.UslugaComplex_id
				where
					es.EvnSection_id = :EvnUslugaCommon_pid
					and ucpl.UslugaComplexPartitionLink_Signrao = 1
			";
			$result = $this->db->query($query, $data);;
			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['UslugaComplex_id'])) {
					$isSIGNRAO = true;
				}
			}
			
			// если добавляем услугу SIGNRAO то проверяем наличие услуг "Пребывание в РАО стационара"
			if ($isSIGNRAO) {
				$EvnUslugaCommon_id = $this->getFirstResultFromQuery("
					select top 1
						euc.EvnUslugaCommon_id
					from
						v_EvnSection es with (nolock)
						inner join v_EvnUslugaCommon euc (nolock) on euc.EvnUslugaCommon_pid = es.EvnSection_id
						inner join r66.v_UslugaComplexPartitionLink ucpl (nolock) on ucpl.UslugaComplex_id = euc.UslugaComplex_id
						inner join r66.v_UslugaComplexPartition ucp (nolock) on ucp.UslugaComplexPartition_id = ucpl.UslugaComplexPartition_id
					where
						es.EvnSection_id = :EvnUslugaCommon_pid
						and ucp.UslugaComplexPartition_Code = 105
						and (euc.EvnUslugaCommon_id <> :EvnUslugaCommon_id or :EvnUslugaCommon_id is null)
				", $data);
				if (empty($EvnUslugaCommon_id)) {
					throw new Exception('В движении не может быть услуг с признаком SIGNRAO без услуг категории "Пребывание в РАО стационара"');
				}
			}
		}

		/*$checkDate = $this->CheckEvnUslugaDate($data);
		if ( !$this->isSuccessful($checkDate) ) {
			throw new Exception($checkDate[0]['Error_Msg'], (int)$checkDate[0]['Error_Code']);
		}*/

		if (empty($data['EvnUslugaCommon_IsVizitCode']) || 2 != $data['EvnUslugaCommon_IsVizitCode']) {
			// Проверка на дубли
			$response = $this->checkEvnUslugaDoubles($data, 'common', $in_UslugaComplex_list);
			if ( $response == -1 ) {
				throw new Exception('Ошибка при выполнении проверки услуг на дубли');
			}
			if ( $response > 0 ) {
				throw new Exception('Сохранение отменено, т.к. данная услуга уже заведена в талоне/КВС.
				Если было выполнено несколько услуг, то измените количество в ранее заведенной услуге');
			}
		}

		if (!empty($data['EvnUslugaCommon_pid'])) {
			$query = "
				select
					coalesce(EPL.EvnPL_IsPaid, EV.EvnVizit_IsPaid, ES.EvnSection_IsPaid, 1) as EvnUsluga_IsPaid,
					coalesce(EPL.EvnPL_IndexRep, EV.EvnVizit_IndexRep, ES.EvnSection_IndexRep, 0) as EvnUsluga_IndexRep,
					coalesce(EPL.EvnPL_IndexRepInReg, EV.EvnVizit_IndexRepInReg, ES.EvnSection_IndexRepInReg, 1) as EvnUsluga_IndexRepInReg
				from
					v_Evn e (nolock)
					left join v_EvnVizit EV with (nolock) on EV.EvnVizit_id = e.Evn_id
					left join v_EvnPL EPL with (nolock) on EPL.EvnPL_id = e.Evn_pid
					left join v_EvnSection ES with (nolock) on ES.EvnSection_id = e.Evn_id
				where
					e.Evn_id = :EvnUslugaCommon_pid
			";

			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				if (
					!($this->getRegionNick() == 'perm' && (!empty($data['ignorePaidCheck']) || $resp[0]['EvnUsluga_IndexRep'] >= $resp[0]['EvnUsluga_IndexRepInReg']))
					&& !($this->getRegionNick() == 'ufa' && isSuperadmin())
					&& !empty($resp[0]['EvnUsluga_IsPaid']) && $resp[0]['EvnUsluga_IsPaid'] == 2
				) {
					throw new Exception('Нельзя добавить услугу, т.к. случай оплачен');
				}
			}
		}

		return $uslugaComplexRows;
	}

	/**
	 * Получение количества тарифов на услуге
	 */
	public function getUslugaComplexTariffCount($data) {
		$params = array(
			'Person_id' => $data['Person_id'],
			'Date' => $data['Date'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'PayType_id' => $data['PayType_id']
		);

		$query = "
			declare
				@Person_Age int,
				@Person_AgeDays int;
			select top 1
				@Person_Age = dbo.Age2(PS.Person_BirthDay, :Date),
				@Person_AgeDays = datediff(day, PS.Person_BirthDay, :Date)
			from v_PersonState PS with(nolock)
			where PS.Person_id = :Person_id;

			select top 1
				count(UCT.UslugaComplexTariff_id) as Count
			from v_UslugaComplexTariff UCT with(nolock)
			where
				UCT.UslugaComplex_id = :UslugaComplex_id
				and UCT.PayType_id = :PayType_id
				and (
					(@Person_Age >= 18 and UCT.MesAgeGroup_id = 1)
					or (@Person_Age < 18 and UCT.MesAgeGroup_id = 2)
					or (@Person_AgeDays > 28 and UCT.MesAgeGroup_id = 3)
					or (@Person_AgeDays <= 28 and UCT.MesAgeGroup_id = 4)
					or (@Person_Age < 18 and UCT.MesAgeGroup_id = 5)
					or (@Person_Age >= 18 and UCT.MesAgeGroup_id = 6)
					or (@Person_Age < 8 and UCT.MesAgeGroup_id = 7)
					or (@Person_Age >= 8 and UCT.MesAgeGroup_id = 8)
					or (@Person_AgeDays <= 90 and UCT.MesAgeGroup_id = 9)
					or (UCT.MesAgeGroup_id is NULL)
				)
				and UCT.UslugaComplexTariff_begDate <= :Date
				and (UCT.UslugaComplexTariff_endDate > :Date or UCT.UslugaComplexTariff_endDate is null)
		";

		return $this->getFirstResultFromQuery($query, $params);
	}

	/**
	 * Проверки и другая логика перед сохранением стоматологической услуги или пакета услуг
	 */
	private function _beforeSaveEvnUslugaStomPackage($data) {
        $uslugaComplexIdList = array();
        $uslugaComplexRows = array();
        $summaUet = 0;
		$omsPayTypeId = $this->getFirstResultFromQuery("
			select top 1 PayType_id
			from v_PayType with (nolock)
			where PayType_SysNick = 'oms'
		");

		// надо проверить, что КСГ удовлетворяет новым датам
		if (false && empty($data['ignoreKSGCheck']) && !empty($data['EvnDiagPLStom_id'])) { // проверка неактуальна refs #125508
			$query = "
				select
					edps.Mes_id,
					EU.EvnUslugaStom_setDate,
					EU.EvnUslugaStom_disDate,
					m.Mes_begDT,
					m.Mes_endDT
				from
					v_EvnDiagPLStom edps (nolock)
					inner join v_MesOld m (nolock) on m.Mes_id = edps.Mes_id
					outer apply(
						select
							MIN(EvnUslugaStom_setDate) as EvnUslugaStom_setDate,
							MAX(ISNULL(EvnUslugaStom_disDate, EvnUslugaStom_setDate)) as EvnUslugaStom_disDate
						from
							v_EvnUslugaStom (nolock)
						where
							EvnDiagPLStom_id = edps.EvnDiagPLStom_id
					) EU
				where
					edps.EvnDiagPLStom_id = :EvnDiagPLStom_id
			";

			$resp_edps = $this->queryResult($query, array(
				'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id']
			));

			if (!empty($resp_edps[0]['Mes_id'])) {
				$EvnUslugaStom_setDate = strtotime($data['EvnUslugaStom_setDate']);
				if (!empty($resp_edps[0]['EvnUslugaStom_setDate']) && $resp_edps[0]['EvnUslugaStom_setDate']->getTimestamp() < $EvnUslugaStom_setDate) {
					$EvnUslugaStom_setDate = $resp_edps[0]['EvnUslugaStom_setDate']->getTimestamp();
				}

				$EvnUslugaStom_disDate = strtotime(!empty($data['EvnUslugaStom_disDate'])?$data['EvnUslugaStom_disDate']:$data['EvnUslugaStom_setDate']);
				if (!empty($resp_edps[0]['EvnUslugaStom_disDate']) && $resp_edps[0]['EvnUslugaStom_disDate']->getTimestamp() > $EvnUslugaStom_disDate) {
					$EvnUslugaStom_disDate = $resp_edps[0]['EvnUslugaStom_disDate']->getTimestamp();
				}

				// получили даты, проверяем дату КСГ
				if (
					(!empty($resp_edps[0]['Mes_begDT']) && $resp_edps[0]['Mes_begDT']->getTimestamp() > $EvnUslugaStom_setDate)
					|| (!empty($resp_edps[0]['Mes_endDT']) && $resp_edps[0]['Mes_endDT']->getTimestamp() < $EvnUslugaStom_disDate)
				) {
					// если КСГ не актуальна в периоде заболевания, то выдаём ошибочку
					$this->_saveResponse['ignoreParam'] = 'ignoreKSGCheck';
					$this->_setAlertMsg('На указанную дату выполнения услуги выбранная в заболевании КСГ закрыта. При сохранении услуги информация по сохраненным услугам по КСГ будет потеряна. Сохранить?');
					throw new Exception('', 110);
				}
			}
		}

		if (empty($data['MedPersonal_id'])) {
			throw new Exception('Поле Врач обязательно для заполнения.');
		}
        if (!empty($data['UslugaSelectedList']) && empty($data['EvnUslugaStom_id'])) {
            $tmp = json_decode($data['UslugaSelectedList'], true);
            if (empty($tmp)) {
                throw new Exception('Нет выбранных услуг');
            }
            foreach ($tmp as $row) {
                if (empty($row['UslugaComplex_id'])
                || !array_key_exists('UslugaComplexTariff_id', $row)
                || (empty($row['UslugaComplexTariff_UED']) && empty($data['EvnDiagPLStom_id']))
                || !array_key_exists('UslugaComplexTariff_UEM', $row)
                || !array_key_exists('UslugaComplexTariff_Tariff', $row)
                || empty($row['EvnUsluga_Kolvo'])
                ) {
                    throw new Exception('Неправильный формат списка отмеченных услуг');
                }
                $row['EvnUslugaStom_UED'] = null;
                $row['EvnUslugaStom_UEM'] = null;
                $row['EvnUslugaStom_Price'] = 0;
                $row['EvnUslugaStom_Kolvo'] = $row['EvnUsluga_Kolvo'];
                unset($row['EvnUsluga_Kolvo']);
                if ($data['MedPersonal_id']>0) {
                    $row['EvnUslugaStom_UED'] = $row['UslugaComplexTariff_UED'];
                }
                if ($data['MedPersonal_sid']>0) {
                    $row['EvnUslugaStom_UEM'] = $row['UslugaComplexTariff_UEM'];
                }
                if ( $row['EvnUslugaStom_UED'] > 0 ) {
                    $row['EvnUslugaStom_Price'] += $row['EvnUslugaStom_UED'];
                }
                if ( $row['EvnUslugaStom_UEM'] > 0 ) {
                    $row['EvnUslugaStom_Price'] += $row['EvnUslugaStom_UEM'];
                }
                unset($row['UslugaComplexTariff_UED']);
                unset($row['UslugaComplexTariff_UEM']);
                unset($row['UslugaComplexTariff_Tariff']);
                $uslugaComplexRows[] = $row;
                $uslugaComplexIdList[] = $row['UslugaComplex_id'];
				if (getRegionNick() == 'ufa' && $data['PayType_id'] == $omsPayTypeId) {
					$summaUet += ($row['EvnUslugaStom_Kolvo'] * $row['EvnUslugaStom_Price']);
				}
            }
        } else if (isset($data['UslugaComplex_id'])) {
            if (empty($data['EvnUslugaStom_Price']) && empty($data['EvnDiagPLStom_id']) && $this->getRegionNick() != 'buryatiya' && (
                empty($data['EvnUslugaStom_IsVizitCode']) || 2 != $data['EvnUslugaStom_IsVizitCode']
            )) {
                throw new Exception('Поле Цена (УЕТ) обязательно для заполнения.');
            }
            if (empty($data['EvnUslugaStom_Kolvo'])) {
                throw new Exception('Поле Количество обязательно для заполнения.');
            }
			if (empty($data['PayType_id'])) {
				throw new Exception('Поле Вид оплаты обязательно для заполнения.');
			}
			if (empty($data['UslugaPlace_id'])) {
				throw new Exception('Поле Место оказания обязательно для заполнения.');
			}
            $row = array(
                'UslugaComplex_id' => $data['UslugaComplex_id'],
                'UslugaComplexTariff_id' => (empty($data['UslugaComplexTariff_id']) ? NULL : $data['UslugaComplexTariff_id']),
                'EvnUslugaStom_UED' => (empty($data['EvnUslugaStom_UED']) ? NULL : $data['EvnUslugaStom_UED']),
                'EvnUslugaStom_UEM' => (empty($data['EvnUslugaStom_UEM']) ? NULL : $data['EvnUslugaStom_UEM']),
                'EvnUslugaStom_Price' => $data['EvnUslugaStom_Price'],
                'EvnUslugaStom_Kolvo' => $data['EvnUslugaStom_Kolvo'],
            );
            $uslugaComplexRows[] = $row;
            $uslugaComplexIdList[] = $row['UslugaComplex_id'];

			if (getRegionNick() == 'ufa' && $data['PayType_id'] == $omsPayTypeId) {
				$summaUet = $data['EvnUslugaStom_Kolvo'] * $data['EvnUslugaStom_Price'];
			}
        } else {
            throw new Exception('Нет выбранных услуг');
        }
		$in_UslugaComplex_list = implode(',', $uslugaComplexIdList);

		/*$checkDate = $this->CheckEvnUslugaDate($data);
		if ( !$this->isSuccessful($checkDate) ) {
			throw new Exception($checkDate[0]['Error_Msg'], (int)$checkDate[0]['Error_Code']);
		}*/

		// Проверка на дубли
		$response = $this->checkEvnUslugaDoubles($data, 'stom', $in_UslugaComplex_list);
		if ( $response == -1 ) {
			throw new Exception('Ошибка при выполнении проверки услуг на дубли');
		}
		if ( $response > 0 ) {
			throw new Exception('Сохранение отменено, т.к. данная услуга уже заведена в талоне/КВС.
			Если было выполнено несколько услуг, то измените количество в ранее заведенной услуге');
		}

		$isEkb = ($this->getRegionNick() == 'ekb');
		if ($isEkb && !empty($data['EvnUslugaStom_pid']) && (empty($data['EvnUslugaStom_IsVizitCode']) || $data['EvnUslugaStom_IsVizitCode'] != 2)) {
			// проверям что услуга не занесена как код посещения
			$EvnVizitPLStom_id = $this->getFirstResultFromQuery("
					select top 1 EvnVizitPLStom_id
					from v_EvnVizitPLStom with (nolock)
					where EvnVizitPLStom_id = :EvnUslugaStom_pid
						and UslugaComplex_id in ({$in_UslugaComplex_list})
				", $data);
			if (!empty($EvnVizitPLStom_id)) {
				throw new Exception('Услуга сохранена как код посещения, сохранение невозможно');
			}
		}
		/*
		 * Проверка суммы УЕТ (ФАКТ ПО ОМС) всех посещений случая
		 * https://redmine.swan.perm.ru/issues/36149
		 * чтобы одним запросом проверить сохраняемые услуги
		 * при добавлении оказания услуг из пакета
		 */
		if ($this->getRegionNick() == 'ufa') {
			if (false === $omsPayTypeId) {
				throw new Exception('Не удалось получить тип оплаты по ОМС');
			}
			if ($data['PayType_id'] == $omsPayTypeId) {
				$max = 20;

				// Проверяем сумму УЕТ (ФАКТ ПО ОМС) всех посещений случая
				$summaOms = $this->getFirstResultFromQuery("
					select sum(isnull(eus.EvnUslugaStom_Summa, 0)) as EvnUslugaStom_SummaOms
					from v_EvnVizitPLStom evpls with (nolock)
						inner join v_EvnUslugaStom eus with (nolock) on eus.EvnUslugaStom_rid = evpls.EvnVizitPLStom_pid
					where evpls.EvnVizitPLStom_id = :EvnUslugaStom_pid
						and eus.PayType_id = :omsPayTypeId
						and eus.EvnUslugaStom_id != ISNULL(:EvnUslugaStom_id, 0)
				", array(
					'EvnUslugaStom_id' => $data['EvnUslugaStom_id'],
					'EvnUslugaStom_pid' => $data['EvnUslugaStom_pid'],
					'omsPayTypeId' => $omsPayTypeId
				));
				if ( false === $summaOms ) {
					throw new Exception('Ошибка при получении суммы УЕТ по ОМС');
				}
				if ( ($summaOms + $summaUet) > $max ) {
					throw new Exception('Сумма УЕТ в услугах по ОМС в случае лечения не должна превышать '.$max.' УЕТ');
				}
			}
		}
		if ($this->getRegionNick() == 'perm' && !empty($uslugaComplexRows) && !empty($data['EvnUslugaStom_pid'])) {

			//Если дата начала ТАП 31.10.2015 и раньше, то дата конца / начала любой услуги должны быть 31.10.2015 и раньше
			if ( strtotime($data['EvnUslugaStom_setDate']) > strtotime('2015-10-31') || strtotime($data['EvnUslugaStom_disDate']) > strtotime('2015-10-31')) {
				$EPLS = $this->getFirstRowFromQuery("
					select
						EPLS.EvnPLStom_id
					from
						v_EvnPLStom EPLS (nolock)
						left join v_EvnVizitPLStom EVPLS with (nolock) on EVPLS.EvnVizitPLStom_rid = EPLS.EvnPLStom_id
					where
						EVPLS.EvnVizitPLStom_id = :EvnUslugaStom_pid
						and EPLS.EvnPLStom_setDate <= '2015-10-31'
				", array('EvnUslugaStom_pid' => $data['EvnUslugaStom_pid']));

				if (!empty($EPLS)) {
					throw new Exception('Механизмы хранения данных и оплаты по стомат. случаям изменены ТФОМС с 01-11-2015, создание переходных случаев невозможно.');
				}
			}

			if ( $this->getRegionNick() != 'perm' ) {
				$resp = $this->getFirstRowFromQuery("
					select top 1
						PT.PayType_id,
						PT.PayType_SysNick,
						convert(varchar(10), EVPL.EvnVizitPLStom_setDate, 120) as EvnVizitPLStom_setDate
					from
						v_EvnVizitPLStom EVPL with(nolock)
						left join v_PayType PT with(nolock) on PT.PayType_id = EVPL.PayType_id
					where
						EvnVizitPLStom_id = :EvnUslugaStom_pid
				", array('EvnUslugaStom_pid' => $data['EvnUslugaStom_pid']));
				if (!$resp) {
					throw new Exception('Ошибка при запросе посещения');
				}

				foreach($uslugaComplexIdList as $id) {
					if ($resp['PayType_SysNick'] == 'oms') {
						$tariff_count = $this->getUslugaComplexTariffCount(array(
							'Person_id' => $data['Person_id'],
							'Date' => $data['EvnUslugaStom_setDate'],
							'PayType_id' => $resp['PayType_id'],
							'UslugaComplex_id' => $id
						));
						if ($tariff_count === false) {
							throw new Exception("Ошибка при проверке наличия тарифов");
						}
						if ($tariff_count == 0) {
							$this->addWarningMsg('Стоматологическая услуга: На данную услугу нет тарифа!');
						}
					}
				}
			}
		}
		return $uslugaComplexRows;
	}

	/**
	 * Получает список тарифов для каждой комплексной услуги
	 */
	private function _loadUslugaComplexTariff($queryParams) {
		if ( empty($queryParams['LpuSection_id']) ) {
			throw new Exception('Не указано отделение');
		}
		if ( empty($queryParams['PayType_id']) ) {
			throw new Exception('Не указан вид оплаты');
		}
		if ( empty($queryParams['Person_id']) ) {
			throw new Exception('Не указан идентификатор пациента');
		}
		if ( empty($queryParams['UslugaComplexTariff_Date']) ) {
			throw new Exception('Не указана дата оказания услуги');
		}
		if ( empty($queryParams['UslugaComplex_id']) ) {
			throw new Exception('Не указан UslugaComplex_id');
		}
		if ( empty($queryParams['in_UslugaComplex_list']) ) {
			throw new Exception('Не указан in_UslugaComplex_list');
		}
		$this->load->model('Usluga_model', 'Usluga_model');
		$uslugaComplexTariffList = $this->Usluga_model->loadUslugaComplexTariffList($queryParams);
		if ( !is_array($uslugaComplexTariffList) ) {
			throw new Exception('Ошибка при запросе списка тарифов');
		}
		$uc_TariffList = array();
		foreach ($uslugaComplexTariffList as $row) {
			if (empty($uc_TariffList[$row['UslugaComplex_id']])) {
				$uc_TariffList[$row['UslugaComplex_id']] = array();
			}
			$uc_TariffList[$row['UslugaComplex_id']][] = $row;
		}
		unset($uslugaComplexTariffList);
		return $uc_TariffList;
	}

	/**
	 * Сохранение услуги или пакета услуг
	 *
	 * При сохранении пакета услуг создается столько EvnUsluga, сколько отмечено в составе.
	 */
	private function _saveEvnUslugaPackage($data, $sysNick) {
		try {
			$response = array();
			$this->beginTransaction();
			switch ($sysNick) {
				case 'EvnUslugaStom':
					// https://redmine.swan-it.ru/issues/149468
					if ( empty($data['EvnDiagPLStom_id']) && strtotime($data['EvnUslugaStom_setDate']) >= getEvnPLStomNewBegDate() ) {
						// для парадонтограмммы нет заболевания
						$parondontogramAttribute = $this->getFirstResultFromQuery("
							select top 1
								uca.UslugaComplexAttribute_id
							from
								v_UslugaComplexAttribute uca with (nolock)
								inner join v_UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
							where
								uca.UslugaComplex_id = :UslugaComplex_id
								and ucat.UslugaComplexAttributeType_SysNick = 'parondontogram'
						", array(
							'UslugaComplex_id' => $data['UslugaComplex_id']
						), true);

						if (empty($parondontogramAttribute)) {
							throw new Exception('Поле "Заболевание" обязательно для заполнения');
						}
					}

					// @task https://redmine.swan.perm.ru/issues/136169
					if ( $this->regionNick == 'penza' && strtotime($data['EvnUslugaStom_setDate']) >= strtotime('2018-08-01') ) {
						$uslugaTypeValue = $this->getFirstResultFromQuery("
							select top 1 uca.UslugaComplexAttribute_Value
							from v_UslugaComplexAttribute uca with (nolock)
								inner join v_UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
							where uca.UslugaComplex_id = :UslugaComplex_id
								and ucat.UslugaComplexAttributeType_SysNick = 'uslugatype'
								and uca.UslugaComplexAttribute_Value in ('01', '02', '03')
						", array(
							'UslugaComplex_id' => $data['UslugaComplex_id']
						), true);

						if ( $uslugaTypeValue == '03' ) {
							$ServiceType_SysNick = $this->getFirstResultFromQuery("
								select top 1 st.ServiceType_SysNick
								from v_EvnVizitPLStom evpls with (nolock)
									inner join v_ServiceType st on st.ServiceType_id = evpls.ServiceType_id
								where evpls.EvnVizitPLStom_id = :EvnVizitPLStom_id
							", array(
								'EvnVizitPLStom_id' => $data['EvnUslugaStom_pid']
							), true);

							if ( !empty($ServiceType_SysNick) && $ServiceType_SysNick != 'neotl' && $ServiceType_SysNick != 'polnmp' ) {
								$uslugaTypeValue = '01';
							}
						}

						if ( $uslugaTypeValue == '01' ) {
							$EvnPLStom_NumCard = $this->getFirstResultFromQuery("
								declare @EvnUslugaStom_setDate datetime = :EvnUslugaStom_setDate;

								declare
									@Diag_pid bigint,
									@EvnDiagPLStom_id bigint = :EvnDiagPLStom_id,
									@EvnDiagPLStom_rid bigint,
									@Month int,
									@Year int;

								select top 1
									@Diag_pid = d.Diag_pid,
									@EvnDiagPLStom_rid = edpls.EvnDiagPLStom_rid
								from v_EvnDiagPLStom edpls with (nolock)
									inner join v_Diag d with (nolock) on d.Diag_id = edpls.Diag_id
								where edpls.EvnDiagPLStom_id = @EvnDiagPLStom_id;

								set @Month = MONTH(@EvnUslugaStom_setDate);
								set @Year = YEAR(@EvnUslugaStom_setDate);

								with epls as (
									select
										EvnPLStom_id,
										EvnPLStom_NumCard
									from v_EvnPLStom with (nolock)
									where Person_id = :Person_id
										and Lpu_id = :Lpu_id
										and EvnPLStom_IsFinish = 2
										and EvnPLStom_id != @EvnDiagPLStom_rid
										and YEAR(EvnPLStom_disDT) = @Year
										and MONTH(EvnPLStom_disDT) = @Month
								)

								select top 1
									epls.EvnPLStom_NumCard
								from v_EvnUslugaStom eus with (nolock)
									inner join v_EvnDiagPLStom edpls on edpls.EvnDiagPLStom_id = eus.EvnDiagPLStom_id
									inner join v_Diag d with (nolock) on d.Diag_id = edpls.Diag_id
									inner join v_EvnVizitPLStom evpls on evpls.EvnVizitPLStom_id = eus.EvnUslugaStom_pid
									inner join epls on epls.EvnPLStom_id = eus.EvnUslugaStom_rid
									inner join v_UslugaComplexAttribute uca on uca.UslugaComplex_id = eus.UslugaComplex_id
									inner join v_UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
								where d.Diag_pid = @Diag_pid
									and edpls.EvnDiagPLStom_IsClosed = 2
									and ucat.UslugaComplexAttributeType_SysNick = 'uslugatype'
									and uca.UslugaComplexAttribute_Value = '01'

								union all

								select top 1
									epls.EvnPLStom_NumCard
								from v_EvnUslugaStom eus with (nolock)
									inner join v_EvnDiagPLStom edpls on edpls.EvnDiagPLStom_id = eus.EvnDiagPLStom_id
									inner join v_Diag d with (nolock) on d.Diag_id = edpls.Diag_id
									inner join v_EvnVizitPLStom evpls on evpls.EvnVizitPLStom_id = eus.EvnUslugaStom_pid
									inner join epls on epls.EvnPLStom_id = eus.EvnUslugaStom_rid
									inner join v_ServiceType st on st.ServiceType_id = evpls.ServiceType_id
									inner join v_UslugaComplexAttribute uca on uca.UslugaComplex_id = eus.UslugaComplex_id
									inner join v_UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
								where d.Diag_pid = @Diag_pid
									and edpls.EvnDiagPLStom_IsClosed = 2
									and st.ServiceType_SysNick != 'neotl'
									and st.ServiceType_SysNick != 'polnmp'
									and ucat.UslugaComplexAttributeType_SysNick = 'uslugatype'
									and uca.UslugaComplexAttribute_Value = '03'
							", array(
								'Person_id' => $data['Person_id'],
								'Lpu_id' => $data['Lpu_id'],
								'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id'],
								'EvnUslugaStom_setDate' => $data['EvnUslugaStom_setDate'],
							));

							if ( $EvnPLStom_NumCard !== false && !empty($EvnPLStom_NumCard) ) {
								throw new Exception('В текущем месяце у данного пациента уже есть закрытое заболевание с такой же группой диагнозов и услугами с лечебной целью: ТАП № ' . $EvnPLStom_NumCard . '. Услуги с лечебной целью необходимо добавить в ранее созданный талон»');
							}
						}
					}

					$this->load->model('PersonToothCard_model', 'PersonToothCard_model');
					$newStatesData = array(
						'EvnUsluga_pid' => $data['EvnUslugaStom_pid'],
						'EvnDiagPLStom_id' => !empty($data['EvnDiagPLStom_id']) ? $data['EvnDiagPLStom_id'] : null,
						'Person_id' => $data['Person_id'],
						'Lpu_id' => $data['Lpu_id'],
						'EvnUsluga_setDT' => $data['EvnUslugaStom_setDate'],
						'pmUser_id' => $data['pmUser_id'],
						'UslugaData' => array(),
						//'session' => $data['session'],
					);
					$UslugaComplex_rows = $this->_beforeSaveEvnUslugaStomPackage($data);
					foreach ($UslugaComplex_rows as $row) {
						$data['UslugaComplex_id'] = $row['UslugaComplex_id'];
						$data['UslugaComplexTariff_id'] = $row['UslugaComplexTariff_id'];
						$data['EvnUslugaStom_UED'] = $row['EvnUslugaStom_UED'];
						$data['EvnUslugaStom_UEM'] = $row['EvnUslugaStom_UEM'];
						$data['EvnUslugaStom_Kolvo'] = $row['EvnUslugaStom_Kolvo'];
						$data['EvnUslugaStom_Price'] = $row['EvnUslugaStom_Price'];
						$response = $this->_saveEvnUslugaStom($data);
						$newStatesData['UslugaData'][] = array(
							'EvnUsluga_id' => $response[0]['EvnUslugaStom_id'],
							'UslugaComplex_id' => $data['UslugaComplex_id'],
						);

						if ( !empty($data['UslugaMedType_id']) ) {
							$this->saveUslugaMedTypeLink($response[0]['EvnUslugaStom_id'], $data['UslugaMedType_id']);
						}

					}
					if (empty($data['EvnUslugaStom_id'])) {
						// устанавливаем состояния только при добавлении
						$this->PersonToothCard_model->applyEvnUslugaChanges($newStatesData);
					}
					break;
				case 'EvnUslugaCommon':
					if ( !empty($data['EvnUslugaCommon_pid']) ) {
						$data['ParentEvnClass_SysNick'] = $this->getFirstResultFromQuery('
						SELECT EvnClass_SysNick FROM v_Evn with (nolock)
						WHERE Evn_id = :id', array('id' => $data['EvnUslugaCommon_pid']));
						if (empty($data['ParentEvnClass_SysNick'])) {
							throw new Exception('Сохранение отменено, т.к. не удалось определить вид учетного документа');
						}
					}
					$UslugaComplex_rows = $this->_beforeSaveEvnUslugaCommonPackage($data);
					foreach ($UslugaComplex_rows as $row) {
						$data['UslugaComplex_id'] = $row['UslugaComplex_id'];
						$data['UslugaComplexTariff_id'] = $row['UslugaComplexTariff_id'];
						$data['EvnUslugaCommon_Kolvo'] = $row['EvnUslugaCommon_Kolvo'];
						$data['EvnUslugaCommon_Price'] = $row['EvnUslugaCommon_Price'];
						$response = $this->_saveEvnUslugaCommon($data);

						if ( !empty($data['UslugaMedType_id']) ) {
							$this->saveUslugaMedTypeLink($response[0]['EvnUslugaCommon_id'], $data['UslugaMedType_id']);
						}
					}
					break;
				default:
					throw new Exception('Неправильный класс события для сохранения пакета услуг');
					break;
			}
			$this->commitTransaction();
			try {
				if (!empty($data['EvnUslugaCommon_pid'])
					&& !empty($data['ParentEvnClass_SysNick'])
					&& 'EvnSection' == $data['ParentEvnClass_SysNick']
				) {
					$this->load->model('EvnSection_model');
					// пересчитать КСГ/КПГ/Коэф в движении
					$this->EvnSection_model->recalcKSGKPGKOEF($data['EvnUslugaCommon_pid'], $data['session'], array(
						'byEvnUslugaChange' => true
					));
				}
			} catch (Exception $e) {
				//$this->_setAlertMsg("<div>При перерасчете КСГ/КПГ произошла ошибка</div><div>{$e->getMessage()}</div>");
			}
			if (isset($this->_saveResponse['Warning_Msg'])) {
				$response[0]['Warning_Msg'] = $this->_saveResponse['Warning_Msg'];
			}
			if (isset($this->_saveResponse['Info_Msg'])) {
				$response[0]['Info_Msg'] = $this->_saveResponse['Info_Msg'];
			}
			return $response;
		} catch (Exception $e) {
			$this->rollbackTransaction();
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
			$this->_saveResponse['Error_Code'] = $e->getCode();
			return $this->_saveResponse;
		}
	}

	/**
	 * Сохранение стоматологической услуги
	 */
	function saveEvnUslugaStom($data) {
		return $this->_saveEvnUslugaPackage($data, 'EvnUslugaStom');
	}

	/**
	 * Сохранение стоматологической услуги
	 */
	private function _saveEvnUslugaStom($data) {
		if ( isset($data['EvnUslugaStom_setTime']) ) {
			$data['EvnUslugaStom_setDate'] .= ' ' . $data['EvnUslugaStom_setTime'] . ':00:000';
		}

		if ( !empty($data['EvnUslugaStom_disDate']) && !empty($data['EvnUslugaStom_disTime']) ) {
			$data['EvnUslugaStom_disDate'] .= ' ' . $data['EvnUslugaStom_disTime'] . ':00:000';
		}
		if ( empty($data['EvnUslugaStom_disDate']) ) {
			$data['EvnUslugaStom_disDate'] = $data['EvnUslugaStom_setDate'];
		}

		if ( empty($data['EvnUslugaStom_id']) || $data['EvnUslugaStom_id'] <= 0 ) {
			$action = 'ins';
		} else {
			$action = 'upd';
		}
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :EvnUslugaStom_id;

			exec p_EvnUslugaStom_" . $action . "
				@EvnUslugaStom_id = @Res output,
				@EvnUslugaStom_pid = :EvnUslugaStom_pid,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnUslugaStom_setDT = :EvnUslugaStom_setDT,
				@EvnUslugaStom_disDT = :EvnUslugaStom_disDT,
				@PayType_id = :PayType_id,
				@Usluga_id = :Usluga_id,
				@UslugaComplex_id = :UslugaComplex_id,
				@LpuDispContract_id = :LpuDispContract_id,
				@MedPersonal_id = :MedPersonal_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@MedPersonal_sid = :MedPersonal_sid,
				@UslugaPlace_id = :UslugaPlace_id,
				@LpuSection_uid = :LpuSection_uid,
				@LpuSectionProfile_id = :LpuSectionProfile_id,
				@EvnUslugaStom_Kolvo = :EvnUslugaStom_Kolvo,
				@EvnUslugaStom_UED = :EvnUslugaStom_UED,
				@EvnUslugaStom_UEM = :EvnUslugaStom_UEM,
				@EvnUslugaStom_Price = :EvnUslugaStom_Price,
				@EvnUslugaStom_Summa = :EvnUslugaStom_Summa,
				@UslugaComplexTariff_id = :UslugaComplexTariff_id,
				@EvnUslugaStom_SumUL = :EvnUslugaStom_SumUL,
				@EvnUslugaStom_SumUR = :EvnUslugaStom_SumUR,
				@EvnUslugaStom_SumDL = :EvnUslugaStom_SumDL,
				@EvnUslugaStom_SumDR = :EvnUslugaStom_SumDR,
				@EvnPrescrTimetable_id = null,
				@EvnPrescr_id = null,
				@EvnUslugaStom_IsVizitCode = :EvnUslugaStom_IsVizitCode,
				@EvnDiagPLStom_id = :EvnDiagPLStom_id,
				@EvnUslugaStom_IsMes = :EvnUslugaStom_IsMes,
				@EvnUslugaStom_IsAllMorbus = :EvnUslugaStom_IsAllMorbus,
				@BlackCariesClass_id = :BlackCariesClass_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnUslugaStom_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$queryParams = array(
			'EvnUslugaStom_id' => $data['EvnUslugaStom_id'],
			'EvnUslugaStom_pid' => $data['EvnUslugaStom_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnUslugaStom_setDT' => $data['EvnUslugaStom_setDate'],
			'EvnUslugaStom_disDT' => $data['EvnUslugaStom_disDate'],
			'PayType_id' => $data['PayType_id'],
			'Usluga_id' => (!empty($data['Usluga_id']) ? $data['Usluga_id'] : NULL),
			'UslugaComplex_id' => (!empty($data['UslugaComplex_id']) ? $data['UslugaComplex_id'] : NULL),
			'LpuDispContract_id' => (!empty($data['LpuDispContract_id']) ? $data['LpuDispContract_id'] : NULL),
			'MedPersonal_id' => $data['MedPersonal_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'MedPersonal_sid' => (!empty($data['MedPersonal_sid']) ? $data['MedPersonal_sid'] : NULL),
			'UslugaPlace_id' => $data['UslugaPlace_id'],
			'LpuSection_uid' => $data['LpuSection_uid'],
			'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
			'EvnUslugaStom_Kolvo' => $data['EvnUslugaStom_Kolvo'],
			'EvnUslugaStom_Price' => $data['EvnUslugaStom_Price'],
			'EvnUslugaStom_UED' => (!empty($data['EvnUslugaStom_UED']) ? $data['EvnUslugaStom_UED'] : NULL),
			'EvnUslugaStom_UEM' => (!empty($data['EvnUslugaStom_UEM']) ? $data['EvnUslugaStom_UEM'] : NULL),
			'EvnUslugaStom_Summa' => number_format(($data['EvnUslugaStom_Kolvo'] * $data['EvnUslugaStom_Price']), 2, '.', ''),
			'UslugaComplexTariff_id' => (!empty($data['UslugaComplexTariff_id']) ? $data['UslugaComplexTariff_id'] : NULL),
			'EvnUslugaStom_SumUL' => (!empty($data['EvnUslugaStom_SumUL']) ? $data['EvnUslugaStom_SumUL'] : NULL),
			'EvnUslugaStom_SumUR' => (!empty($data['EvnUslugaStom_SumUR']) ? $data['EvnUslugaStom_SumUR'] : NULL),
			'EvnUslugaStom_SumDL' => (!empty($data['EvnUslugaStom_SumDL']) ? $data['EvnUslugaStom_SumDL'] : NULL),
			'EvnUslugaStom_SumDR' => (!empty($data['EvnUslugaStom_SumDR']) ? $data['EvnUslugaStom_SumDR'] : NULL),
			'EvnUslugaStom_IsVizitCode' => (!empty($data['EvnUslugaStom_IsVizitCode']) ? $data['EvnUslugaStom_IsVizitCode'] : NULL),
			'EvnDiagPLStom_id' => (!empty($data['EvnDiagPLStom_id']) ? $data['EvnDiagPLStom_id'] : NULL),
			'EvnUslugaStom_IsMes' => (!empty($data['EvnUslugaStom_IsMes']) ? $data['EvnUslugaStom_IsMes'] : NULL),
			'EvnUslugaStom_IsAllMorbus' => (!empty($data['EvnUslugaStom_IsAllMorbus']) ? $data['EvnUslugaStom_IsAllMorbus'] : NULL),
			'BlackCariesClass_id' => (!empty($data['BlackCariesClass_id']) ? $data['BlackCariesClass_id'] : NULL),
			'pmUser_id' => $data['pmUser_id']
		);
		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) ) {
			throw new Exception('Ошибка при сохранении стоматологической услуги');
		}
		return $result->result('array');
	}

	/**
	 * @param $EvnUslugaStom_id
	 * @param $JawPartType_id
	 * @param $Parodontogram_NumTooth
	 * @param $ToothStateType_id
	 */
	function getParodontogramId($EvnUslugaStom_id, $JawPartType_id, $Parodontogram_NumTooth, $ToothStateType_id) {
		$query = "
			select top 1 Parodontogram_id
			from v_Parodontogram with (nolock)
			where EvnUslugaStom_id = :EvnUslugaStom_id
				and JawPartType_id = :JawPartType_id
				and Parodontogram_NumTooth = :Parodontogram_NumTooth
				and ToothStateType_id = :ToothStateType_id
		";
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function saveUslugaComplex($data) {
		$procedure = '';

		if ( !isset($data['UslugaComplex_id']) || $data['UslugaComplex_id'] <= 0 ) {
			$procedure = 'p_UslugaComplex_ins';
		}
		else {
			$procedure = 'p_UslugaComplex_upd';
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :UslugaComplex_id;

			exec " . $procedure . "
				@UslugaComplex_id = @Res output,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@UslugaComplex_ACode = null,
				@UslugaComplex_Code = :UslugaComplex_Code,
				@UslugaComplex_Name = :UslugaComplex_Name,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as UslugaComplex_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'UslugaComplex_Code' => $data['UslugaComplex_Code'],
			'UslugaComplex_Name' => $data['UslugaComplex_Name'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function saveUslugaComplexList($data) {
		$procedure = '';

		if ( !isset($data['UslugaComplexList_id']) || $data['UslugaComplexList_id'] <= 0 ) {
			$procedure = 'p_UslugaComplexList_ins';
		}
		else {
			$procedure = 'p_UslugaComplexList_upd';
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :UslugaComplexList_id;

			exec " . $procedure . "
				@UslugaComplexList_id = @Res output,
				@UslugaComplex_id = :UslugaComplex_id,
				@Server_id = :Server_id,
				@Usluga_id = :Usluga_id,
				@UslugaClass_id = :UslugaClass_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as UslugaComplexList_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'UslugaComplexList_id' => $data['UslugaComplexList_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'Server_id' => $data['Server_id'],
			'Usluga_id' => $data['Usluga_id'],
			'UslugaClass_id' => $data['UslugaClass_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnUslugaOperViewData($data) {
		$filter='';
		$params = array('Person_id' => $data['Person_id']);
		//unset($data['session']);print_r($data);exit();
		if(isset($data['object'])&&$data['object']=='EvnUslugaOper')
		{
			$filter.=" and EUO.EvnUslugaOper_id = :EvnUslugaOper_id";
			$params['EvnUslugaOper_id']=$data['EvnUslugaOper_id'];
		}
		if (!empty($data['Person_id'])){
			//если запрос с идентификатором
			$filter.=" and EUO.Person_id = :Person_id";
		}
		$query = "
			select
				EUO.Person_id,
				EUO.EvnUslugaOper_id,
				0 as Children_Count,
				EUO.EvnUslugaOper_id as SurgicalList_id,
				convert(varchar(10), EUO.EvnUslugaOper_setDT, 104) as EvnUslugaOper_setDate,
				RTRIM(ISNULL(Lpu.Lpu_Nick, '')) as Lpu_Nick,
				isnull(Usluga.Usluga_Code,UC.UslugaComplex_Code) as Usluga_Code,
				isnull(Usluga.Usluga_Name,UC.UslugaComplex_Name) as Usluga_Name
			from v_EvnUslugaOper EUO with (nolock)
				left join v_Usluga Usluga with (nolock) on Usluga.Usluga_id = EUO.Usluga_id
				left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EUO.UslugaComplex_id
				inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = EUO.Lpu_id
			where
				(1=1)
				{$filter}
			order by
				EUO.EvnUslugaOper_setDT
		";
				//echo getDebugSQL($query,$params);
		$result = $this->db->query($query,$params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return array
	 */
	function loadParodontogram($data) {
		$queryParams = array(
			 'EvnUslugaStom_id' => $data['EvnUslugaStom_id']
			,'Person_id' => $data['Person_id']
		);
		$parodontogram = array();
		$response = array();

		if ( !empty($data['EvnUslugaStom_id']) ) {
			$query = "
				select
					 JawPartType_id
					,Parodontogram_NumTooth
					,ToothStateType_id
				from v_Parodontogram with (nolock)
				where EvnUslugaStom_id = :EvnUslugaStom_id
			";
		}
		else {
			// Добавить учет категории и кода услуги 
			$query = "
				select
					 JawPartType_id
					,Parodontogram_NumTooth
					,ToothStateType_id
				from v_Parodontogram with (nolock)
				where EvnUslugaStom_id = (
					select top 1 t1.EvnUslugaStom_id
					from v_EvnUslugaStom t1 with (nolock)
						inner join v_UslugaComplex t2 with (nolock) on t2.UslugaComplex_id = t1.UslugaComplex_id
						inner join v_UslugaCategory t3 with (nolock) on t3.UslugaCategory_id = t2.UslugaCategory_id
					where t1.Person_id = :Person_id
						and (
							(t3.UslugaCategory_SysNick = 'tfoms' and t2.UslugaComplex_Code = '02180212')
							or (t3.UslugaCategory_SysNick in ('gost2004', 'gost2011') and replace(t2.UslugaComplex_Code, '.', '') = 'A0207009')
						)
					order by t1.EvnUslugaStom_setDT desc
				)
			";
		}

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');
		}

		if ( is_array($response) && count($response) > 0 ) {
			foreach ( $response as $rec ) {
				$parodontogram['ToothStateType_' . $rec['JawPartType_id'] . '_' . $rec['Parodontogram_NumTooth']] = $rec['ToothStateType_id'];
			}
		}
		else {
			for ( $i = 1; $i <= 4; $i++ ) {
				for ( $j = 1; $j <= 8; $j++ ) {
					$parodontogram['ToothStateType_' . $i . '_' . $j] = 1;
				}
			}
		}

		return array($parodontogram);
	}

	/**
	 *  Получение списка реактивов и их количества, для конкретной службы на конкретную дату
	 */
	function getReagentCountByDate($data) {
		$query = "
			select
				UslugaComplex_id as \"UslugaComplex_id\",
				UslugaComplex_Name as \"UslugaComplex_Name\",
				PayType_id as \"PayType_id\",
				PayType_Name as \"PayType_Name\",
				count(Evn_id) as \"Kolvo\"
			from
				(
					select
						Evn_id,
						uc.UslugaComplex_id,
						uc.UslugaComplex_Name,
						pt.PayType_id,
						pt.PayType_Name,
						case
							-- 2. В работе: заявки, у которых не заполнен результат в разделе Пробы. Т.е. у заявки есть пробы, у которых дата выполнения не проставлена
							when ( ( studied_count <> 0 ) or ( setted_count <> 0 ) ) and ( studied_count < setted_count )
							then (
								select
									cast (min(EvnLabSample_setDT) as date)
								from
									v_EvnLabSample (nolock)
								where
									EvnLabRequest_id = elr_EvnLabRequest_id
									and EvnLabSample_StudyDT is null
									and EvnLabSample_setDT is not null
							)
							-- 3. Выполненные: заявки, у которых заполнен результат в разделе Пробы. Т.е. у заявки нет проб, у которых дата выполнения пустая
							when ( studied_count > 0 ) and ( studied_count >= setted_count )
							then (
								select
									cast (max(EvnLabSample_StudyDT) as date)
								from
									EvnLabSample (nolock)
								where
									EvnLabRequest_id = elr_EvnLabRequest_id
									and EvnLabSample_StudyDT is not null
							)
							else null
						end as start_date,
						case
							-- 2. В работе: заявки, у которых не заполнен результат в разделе Пробы. Т.е. у заявки есть пробы, у которых дата выполнения не проставлена
							when ( ( studied_count <> 0 ) or ( setted_count <> 0 ) ) and ( studied_count < setted_count )
							then (
								select
									cast(max(EvnLabSample_setDT) as date)
								from
									v_EvnLabSample (nolock)
								where
									EvnLabRequest_id = elr_EvnLabRequest_id
									and EvnLabSample_StudyDT is null
									and EvnLabSample_setDT is not null
							)
							-- 3. Выполненные: заявки, у которых заполнен результат в разделе Пробы.
							when ( studied_count > 0 ) and ( studied_count >= setted_count )
							then (
								select
									cast (max(EvnLabSample_StudyDT) as date)
								from
									EvnLabSample (nolock)
								where
									EvnLabRequest_id = elr_EvnLabRequest_id
										and EvnLabSample_StudyDT is not null
							)
							else null
						end as end_date
					from
						(
						select
							coalesce(lr.EvnLabRequest_id, eu.EvnUslugaPar_id) as Evn_id,
							lr.EvnLabRequest_id as elr_EvnLabRequest_id,
							lr.UslugaExecutionType_id,
							coalesce(lr.UslugaComplex_id,eu.UslugaComplex_id) as UslugaComplex_id,
							coalesce(lr.PayType_id,eu.PayType_id) as PayType_id,
							(
								select
									count(*)
								from
									v_EvnLabSample (nolock)
								where
									EvnLabRequest_id = lr.EvnLabRequest_id
										and EvnLabSample_StudyDT is not null
							) as studied_count,
							(
								select
									count(*)
								from
									v_EvnLabSample (nolock)
								where
									EvnLabRequest_id = lr.EvnLabRequest_id
										and EvnLabSample_SetDT is not null
							) as setted_count,
							(
								select
									count(app_els.EvnLabSample_id)
								from
									v_EvnLabSample app_els (nolock)
									inner join v_UslugaTest ut (nolock) on ut.EvnLabSample_id = app_els.EvnLabSample_id
								where
									app_els.EvnLabRequest_id = lr.EvnLabRequest_id
									and coalesce(ut.UslugaTest_ResultApproved, 1) = 2
							) as approved_count
						from
							v_EvnDirection_all d (nolock)
							left join v_EvnLabRequest lr (nolock) on lr.EvnDirection_id = d.EvnDirection_id
							left join v_EvnUslugaPar eu (nolock) on eu.EvnDirection_id = d.EvnDirection_id
						where
							d.MedService_id = :MedService_id
					) a
					left join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = a.UslugaComplex_id
					left join v_PayType pt with (nolock) on pt.PayType_id = a.PayType_id
					where
						(( studied_count <> 0 ) or ( setted_count <> 0 )) and
						( approved_count <> 0 ) and
						(coalesce(UslugaExecutionType_id,3) in (1,2)) and
						(:PayType_id is null or a.PayType_id = :PayType_id)
				) b
			where
				(start_date <= :Date or start_date is null) and
				(end_date >= :Date or end_date is null)
			group by
				b.UslugaComplex_id, b.UslugaComplex_Name, b.PayType_id, b.PayType_Name;
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Автоучет реактивов - Получение списка реактивов и их количества на конкретную дату
	 * с группировкой по анализаторам
	 */
	function getReagentAutoRateCountOnAnalyser($data) {
		$query = "
			with stat as (
				SELECT
					ts.TestStat_testCode,
					SUM(ts.TestStat_testCount) as testCountSum,
					ts.TestStat_analyzerCode,
					ts.ReagentNormRate_id
				FROM
					lis.TestStat ts with(nolock)
				WHERE 
					ts.TestStat_testDate >= cast(:begDate as date)
					AND ts.TestStat_testDate < dateadd(day, 1, cast(:endDate as date))
				GROUP BY ts.TestStat_analyzerCode, ts.TestStat_testCode, ts.ReagentNormRate_id
			)
			
			SELECT 
				stat.TestStat_analyzerCode as \"analyzerCode\",
				(stat.TestStat_analyzerCode + ' \"' + coalesce(a.Analyzer_Name, 'Не найден') + '\"') as \"analyzerFullName\",
				stat.TestStat_testCode + ' ' +
					CASE coalesce(vuc.UslugaComplex_Nick, '')   
						WHEN ''
							THEN vuc.UslugaComplex_Name
							ELSE vuc.UslugaComplex_Nick
				END as \"test\",
				stat.testCountSum as \"testCountSum\",
				dn.DrugNomen_Name as \"DrugNomen_Name\",
				u.unit_Name as \"unit_Name\",
				rnr.ReagentNormRate_RateValue * stat.testCountSum as \"reagentRateSum\",
				rnr.ReagentNormRate_RateValue as \"ReagentNormRate_RateValue\"
			FROM
				stat
				LEFT JOIN lis.v_Analyzer a with(nolock) ON a.Analyzer_Code = stat.TestStat_analyzerCode
				LEFT JOIN v_UslugaComplex vuc with(nolock) ON vuc.UslugaComplex_Code = stat.TestStat_testCode
					AND vuc.UslugaCategory_id = 4
				LEFT JOIN lis.v_ReagentNormRate rnr with(nolock) ON rnr.ReagentNormRate_id = stat.ReagentNormRate_id
				LEFT JOIN lis.v_unit u with(nolock) ON u.unit_id = rnr.unit_id
				LEFT JOIN rls.v_DrugNomen dn with(nolock) ON dn.DrugNomen_id = rnr.DrugNomen_id
			WHERE 
			  a.MedService_id = :MedService_id
		";

		return $this->queryResult($query, $data);
	}

	/**
	 *	Получение списка профилей
	 */
	function loadLpuSectionProfileList($data)
	{
		$filter = "";
		$params = array();

		if (empty($data['onDate'])) {
			$data['onDate'] = date('Y-m-d');
		}
		$params['onDate'] = $data['onDate'];
		
		// *** NGS: SELECT PROFILE SECTION OF A LPU FOR STOMATOLOGIES - START ***
		// if isStom parameter is defined and equal to 2 that's true
		if(!empty($data['isStom']) && $data['isStom'] == 2) {
			$params['LpuSection_id'] = $data['LpuSection_id'];
			
			if ( !empty($data['onDate']) ) {
				$filterDT = "
					AND LSLSP.LpuSectionLpuSectionProfile_begDate <= :onDate
					AND (LSLSP.LpuSectionLpuSectionProfile_endDate >= :onDate
					OR LSLSP.LpuSectionLpuSectionProfile_endDate IS NULL)
				";
			}
			
			$query = "
				SELECT
				  LSP.LpuSectionProfile_id
				 ,LSP.LpuSectionProfile_Code
				 ,LSP.LpuSectionProfile_Name
				 ,RTRIM(CAST(LSP.LpuSectionProfile_Code AS VARCHAR)) + ' ' + LSP.LpuSectionProfile_Name AS LpuSectionProfile_FullName
				FROM v_LpuSectionLpuSectionProfile LSLSP (NOLOCK)
				INNER JOIN v_LpuSectionProfile LSP (NOLOCK)
				  ON LSP.LpuSectionProfile_id = LSLSP.LpuSectionProfile_id
				WHERE LSLSP.LpuSection_id = :LpuSection_id
				{$filterDT}
				UNION
				
				SELECT
				  LSP.LpuSectionProfile_id
				 ,LSP.LpuSectionProfile_Code
				 ,LSP.LpuSectionProfile_Name
				 ,RTRIM(CAST(LSP.LpuSectionProfile_Code AS VARCHAR)) + ' ' + LSP.LpuSectionProfile_Name AS LpuSectionProfile_FullName
				FROM v_LpuSection LS (NOLOCK)
				INNER JOIN v_LpuSectionProfile LSP (NOLOCK)
				  ON LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
				WHERE LS.LpuSection_id = :LpuSection_id
				ORDER BY LpuSectionProfile_Code
			";
		}
		// *** NGS: SELECT PROFILE SECTION OF A LPU FOR STOMATOLOGIES - END ***
		else {
			if (!empty($data['MedSpecOms_id'])) {
				if (false && getRegionNick() == 'penza' && !empty($data['isStom'])) { // refs #124034 контроль временно убран
					$LpuSectionProfileIds = $this->queryList("
					select LpuSectionProfile_id from v_LpuSectionProfileMedSpecOms with(nolock) 
					where MedSpecOms_id = :MedSpecOms_id
					and (LpuSectionProfileMedSpecOms_begDate <= :onDate or LpuSectionProfileMedSpecOms_begDate is null)
					and (LpuSectionProfileMedSpecOms_endDate >= :onDate or LpuSectionProfileMedSpecOms_endDate is null)
				", $data);
					if (!is_array($LpuSectionProfileIds)) {
						return false;
					}
					if (count($LpuSectionProfileIds) > 0) {
						$LpuSectionProfileIds_str = implode(",", $LpuSectionProfileIds);
						$filter .= "
						and LSP.LpuSectionProfile_id in ({$LpuSectionProfileIds_str})
					";
					}
				} else {
					$params['MedSpecOms_id'] = $data['MedSpecOms_id'];
					$filter .= "and exists(
					select top 1 mso.MedSpec_id from dbo.v_MedSpecOms mso (nolock)
					inner join LSPMS with(nolock) on LSPMS.LpuSectionProfile_id = LSP.LpuSectionProfile_fedid
						and LSPMS.MedSpec_id = mso.MedSpec_id
					where mso.MedSpecOms_id = :MedSpecOms_id
				)";
				}
			} elseif (empty($data['LpuSection_id'])) {
				// если указано отделение, значит мы в своей МО, значит фильтр не нужен
				// исключаем профили, для которых нет специальностей
				$filter .= "and exists(
				select top 1 MedSpec_id from LSPMS with(nolock)
				where LSPMS.LpuSectionProfile_id = LSP.LpuSectionProfile_fedid
			)";
			}
			
			if (!empty($data['LpuSection_id'])) {
				$params['LpuSection_id'] = $data['LpuSection_id'];
				$filter .= "
				and exists(
					select top 1 LS.LpuSection_id from v_LpuSection LS (nolock)
					where LS.LpuSection_id = :LpuSection_id and LS.LpuSectionProfile_id = LSP.LpuSectionProfile_id
					
					union
					
					select LSLSP.LpuSection_id
					from v_LpuSectionLpuSectionProfile LSLSP (nolock)
					where LSLSP.LpuSection_id = :LpuSection_id and LSLSP.LpuSectionProfile_id = LSP.LpuSectionProfile_id
				) 
			";
			}
			
			$query = "
			with LSPMS as (
				SELECT LpuSectionProfile_id, MedSpec_id
				FROM fed.LpuSectionProfileMedSpec (nolock)
				where (LpuSectionProfileMedSpec_begDT <= :onDate or LpuSectionProfileMedSpec_begDT is null)
					and (LpuSectionProfileMedSpec_endDT >= :onDate or LpuSectionProfileMedSpec_endDT is null)
			)

			select
				LSP.LpuSectionProfile_id,
				LSP.LpuSectionProfile_fedid,
				LSP.LpuSectionProfile_Code,
				LSP.LpuSectionProfile_Name,
				RTRIM(cast(LSP.LpuSectionProfile_Code as varchar)) + ' ' + LSP.LpuSectionProfile_Name as LpuSectionProfile_FullName
			from
				dbo.v_LpuSectionProfile LSP (nolock)
			where 1=1
				and (LSP.LpuSectionProfile_begDT <= :onDate or LSP.LpuSectionProfile_begDT is null)
				and (LSP.LpuSectionProfile_endDT >= :onDate or LSP.LpuSectionProfile_endDT is null)
				{$filter}
		";
		}

		
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 *	Получение списка специальностей
	 */
	function loadMedSpecOmsList($data)
	{
		$filter = "";
		$params = array();

		if (empty($data['onDate'])) {
			$data['onDate'] = date('Y-m-d');
		}
		$params['onDate'] = $data['onDate'];

		if (!empty($data['LpuSectionProfile_id'])) {
			$params['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
			$filter .= "and exists(
					select top 1 LSP.LpuSectionProfile_fedid from dbo.v_LpuSectionProfile LSP (nolock)
					inner join LSPMS with(nolock) on LSPMS.LpuSectionProfile_id = LSP.LpuSectionProfile_fedid
						and LSPMS.MedSpec_id = MSO.MedSpec_id
					where LSP.LpuSectionProfile_id = :LpuSectionProfile_id
				)";
		} else {
			// исключаем специальности, для которых нет профилей
			$filter .= "and exists(
				select top 1 LpuSectionProfile_id from LSPMS with(nolock)
				where LSPMS.MedSpec_id = MSO.MedSpec_id
			)";
		}

		$query = "
			with LSPMS as (
				SELECT LpuSectionProfile_id, MedSpec_id
				FROM fed.LpuSectionProfileMedSpec (nolock)
				where (LpuSectionProfileMedSpec_begDT <= :onDate or LpuSectionProfileMedSpec_begDT is null)
					and (LpuSectionProfileMedSpec_endDT >= :onDate or LpuSectionProfileMedSpec_endDT is null)
			)

			select
				MSO.MedSpecOms_id,
				MSO.MedSpec_id as MedSpec_fedid,
				MSO.MedSpecOms_Code,
				MSO.MedSpecOms_Name,
				RTRIM(cast(MSO.MedSpecOms_Code as varchar)) + ' ' + MSO.MedSpecOms_Name as MedSpecOms_FullName
			from
				dbo.v_MedSpecOms MSO (nolock)
			where 1=1
				{$filter}
		";
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}
	
	/**
	 * возмьем события_услуги по идешнику назначения
	 */
	function getEvnUslugaByEvnPrescrId($data, $postgre = false) {

		if ($postgre) {
			$query = "
					select
						EvnUsluga_id
					from v_EvnUsluga
					where
						EvnPrescr_id = :EvnPrescr_id
					limit 1	
				";
		} else {
			$query = "
					select top 1
						EvnUsluga_id
					from v_EvnUsluga (nolock)
					where
						EvnPrescr_id = :EvnPrescr_id
				";
		}
		return $resp = $this->queryResult($query, array(
			'EvnPrescr_id' => $data['EvnPrescr_id'],
		));
	}

	/**
	 * получаем названия услуг уже включенных в направление
	 */
	function getAllUslugaNameInIncludableDirection($direction_id) {
		$result = $this->queryResult("
				SELECT 
					UC.UslugaComplex_Name,
					ELR.EvnLabRequest_id
				FROM v_EvnPrescrDirection EPD with (nolock)
				inner join v_EvnPrescr EP with (nolock) on EP.EvnPrescr_id = EPD.EvnPrescr_id
				inner join v_EvnLabRequest ELR with (nolock) on ELR.EvnDirection_id = EPD.EvnDirection_id
				inner join EvnPrescrLabDiag EPLD with (nolock) on EPLD.EvnPrescrLabDiag_id = EP.EvnPrescr_id
				left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EPLD.UslugaComplex_id
				where  EP.PrescriptionType_id = 11 and EPD.EvnDirection_id = :EvnDirection_id
			", array('EvnDirection_id' => $direction_id));
		if (empty($result) && !is_array($result) && count($result) == 0) {
			return $this->createError('','Ошибка при получении названия услуги');
		}

		return $result;
	}

	/**
	 * возмьем данные по существующим зубам для события_услуги
	 */
	function getToothNumEvnUsluga($data) {
		return $this->queryResult("
				select
					ToothNumEvnUsluga_id,
					ToothNumEvnUsluga_ToothNum
				from v_ToothNumEvnUsluga (nolock)
				where
					EvnUsluga_id = :EvnUsluga_id
				order by ToothNumEvnUsluga_ToothNum asc
			", array(
			'EvnUsluga_id' => $data['EvnUsluga_id'],
		));
	}

	/**
	 * удалим позицию по существующему зубу для события_услуги
	 */
	function delToothNumEvnUsluga($ToothNumEvnUsluga_id) {

		$data['ToothNumEvnUsluga_id'] = $ToothNumEvnUsluga_id;
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_ToothNumEvnUsluga_del
				@ToothNumEvnUsluga_id = :ToothNumEvnUsluga_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$db_query = $this->db->query($query, $data);
		$toothNumEvnUslugaDel = $db_query->result('array');

		return $toothNumEvnUslugaDel;
	}

	/**
	 * вставим позицию по номеру зуба для события_услуги
	 */
	function insToothNumEvnUsluga($data) {

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_ToothNumEvnUsluga_ins
				@ToothNumEvnUsluga_id = @Res output,
				@EvnUsluga_id = :EvnUsluga_id,
				@ToothNumEvnUsluga_ToothNum = :ToothNumEvnUsluga_ToothNum,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as ToothNumEvnUsluga_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$db_query = $this->db->query($query, $data);
		$result = $db_query->result('array');

		return $result;
	}

	/**
	 * Сохранение полезной нагрузки формы "параметры исследования" StudyTarget
	 * или при редактировании зубов в окне назначения на исследование
	 */
	function saveStudyTargetPayloadData($data) {

		$result = true;
		if (!isset($data['StudyTargetPayloadData'])) $data['StudyTargetPayloadData'] = array();

		$payloadData = $data['StudyTargetPayloadData'];

		// если указан ID назначения на исследование, возможно это значит что мы обновляем его
		// для этого найдем событие_услуги по этому указанному направлению
		if (!empty($data['EvnPrescrFuncDiag_id']) && empty($data['EvnUsluga_id'])) {

			$resp = $this->getEvnUslugaByEvnPrescrId(array(
				'EvnPrescr_id' => $data['EvnPrescrFuncDiag_id']
			));

			if (!empty($resp[0]) && !empty($resp[0]['EvnUsluga_id'])) { $data['EvnUsluga_id'] = $resp[0]['EvnUsluga_id'];}
		}

		// пытаемся сохранить, если есть EvnUsluga_id
		if ($data['EvnUsluga_id'] > 0) {

			$existed_tooths = array();
			$new_tooths = array();

			// возмьем данные по существующим зубам для события_услуги
			$resp = $this->getToothNumEvnUsluga($data);

			if (!empty($resp[0]) && empty($response[0]['Error_Msg'])) {
				$existed_tooths = array_column($resp, 'ToothNumEvnUsluga_ToothNum', 'ToothNumEvnUsluga_id');
			}

			if (!empty($payloadData['toothData'])) {

				$new_tooths = $payloadData['toothData'];
				foreach ($new_tooths as $toothNumber) {

					if (!in_array($toothNumber, $existed_tooths)) {

						$toothUslugaAddResult = $this->insToothNumEvnUsluga(array(
							'EvnUsluga_id' => $data['EvnUsluga_id'],
							'pmUser_id' => $data['pmUser_id'],
							'ToothNumEvnUsluga_ToothNum' => $toothNumber
						));
					}
				}

				sort($new_tooths, SORT_NUMERIC);
			}

			// если номера зубов изменились, удалим связь тех что нам больше не нужны
			$tooths_to_delete = array_diff($existed_tooths, $new_tooths);

			if (!empty($tooths_to_delete)) {
				foreach($tooths_to_delete as $key => $deleted_tooth) {
					$this->delToothNumEvnUsluga($key);
				}
			}
		}

		return $result;
	}

	/**
	 * Сохранение заказа на услугу при записи
	 */
	function saveUslugaOrder($data) {

		// сохраняем заказ, если есть необходимость
		if ( !empty($data['order']) ) {
			$orderparams = json_decode(toUTF($data['order']), true);
			if ( count($orderparams) > 0 ) {
				$orderparams['EvnDirection_id'] = (isset($data['EvnDirection_id'])) ? $data['EvnDirection_id'] : null; // сохраняем направление в заказе
				$orderparams['EvnPrescr_id'] = (isset($data['EvnPrescr_id'])) ? $data['EvnPrescr_id'] : null; // сохраняем назначение в заказе
				// если человека в заказе нет, то берем из основных данных
				if ( (!isset($orderparams['Person_id'])) || empty($orderparams['Person_id']) ) {
					$orderparams['Person_id'] = $data['Person_id'];
					$orderparams['PersonEvn_id'] = $data['PersonEvn_id'];
					$orderparams['Server_id'] = $data['Server_id'];
				}

				$orderparams['Server_id'] .= ''; //приводим к строке, чтобы проверить в ProcessInputData
				
				$orderdata = getSessionParams();
				$err = getInputParams($orderdata, getsaveEvnUslugaComplexOrderRule(), true, $orderparams);

				if ( empty($err) ) {
					$orderdata['EvnDirection_id'] = (isset($data['EvnDirection_id'])) ? $data['EvnDirection_id'] : null;
					if (empty($orderdata['PayType_id']))
						$orderdata['PayType_id'] = !empty($data['PayType_id'])?$data['PayType_id']:null;
					$orderdata['EvnPrescr_id'] = !empty($data['EvnPrescr_id']) ? $data['EvnPrescr_id'] : null;
					$orderdata['Diag_id'] = $data['Diag_id'] ?? null;
					$orderdata['EvnUsluga_Result'] = $orderdata['checked'];
					$orderdata['checked'] = json_decode($orderdata['checked']);
					$orderdata['session'] = !empty($data['session']) ? $data['session'] : null;

					if (empty($orderdata['EvnDirection_id']) && !empty($orderdata['EvnPrescr_id'])) {
						$orderdata['EvnDirection_id'] = $this->getFirstResultFromQuery("
							select top 1
								ed.EvnDirection_id
							from
								v_EvnPrescrDirection epd (nolock)
								inner join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = epd.EvnDirection_id
							where
								epd.EvnPrescr_id = :EvnPrescr_id
								and ISNULL(ed.EvnStatus_id, 16) not in (12, 13) -- не отменено/отклонено
						", $orderdata, true);

						if ($orderdata['EvnDirection_id'] === false) {
							throw new Exception("Произошла ошибка при получении направления из переданного назначения");
						}
					}

					$response = $this->saveEvnUslugaComplexOrder($orderdata);
					if ( is_array($response) && (count($response) > 0) ) {

						if ( empty($response[0]['Error_Msg']) ) {

							$data['EvnUsluga_id'] = $response[0]['EvnUsluga_id'];
							$saveStudyTargetPayloadResp = $this->saveStudyTargetPayloadData(array(
								'pmUser_id' => $data['pmUser_id'],
								'EvnUsluga_id' => $data['EvnUsluga_id'],
								'StudyTargetPayloadData' => (!empty($orderparams['StudyTargetPayloadData']) ? $orderparams['StudyTargetPayloadData'] : null)
							));

						} else {

							throw new Exception("Произошла ошибка при сохранении заказа услуги: ".$response[0]['Error_Msg']);
						}
					} else {
						throw new Exception("Произошла ошибка при сохранении заказа услуги");
					}
				} else {
					throw new Exception($err);
				}
			}
		}
		return $data;
	}

	/**
	 *	Получение списка выполненных услуг. Метод для API
	 */
	function loadEvnUslugaList($data)
	{
		$query = "
			select
				EvnUsluga_id
				,EvnUsluga_id as Evn_id
				,EvnUsluga_pid as Evn_pid
				,EvnClass_id
				,convert(varchar(19),EvnUsluga_setDT,120) as Evn_setDT
				,convert(varchar(19),EvnUsluga_disDT,120) as Evn_disDT
				,UslugaComplex_id
			from
				v_EvnUsluga (nolock)
			where
				EvnUsluga_pid = :Evn_pid
				and Lpu_id = :Lpu_id
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	Получение информации по оперативной услуге. Метод для API
	 */
	function loadEvnUslugaOper($data)
	{
		$where = "eu.Lpu_id = :Lpu_id";

		if(!empty($data['EvnUsluga_id'])){
			$where .= " and eu.EvnUslugaOper_id = :EvnUsluga_id and eu.EvnClass_id = 43";
		}
		if(!empty($data['Evn_pid'])){
			$where .= " and eu.EvnUslugaOper_pid = :Evn_pid";
		}
		if(!empty($data['Evn_setDT'])){
			$where .= " and convert(varchar(19),eu.EvnUslugaOper_setDT,120) = :Evn_setDT";
		}
		if(!empty($data['UslugaComplex_id'])){
			$where .= " and eu.UslugaComplex_id = :UslugaComplex_id";
		}

		$query = "
			select
				eu.EvnUslugaOper_id as EvnUsluga_id
				,eu.EvnUslugaOper_id
				,eu.EvnUslugaOper_id as Evn_id
				,eu.EvnUslugaOper_pid as Evn_pid
				,eu.EvnClass_id
				,convert(varchar(19),eu.EvnUslugaOper_setDT,120) as Evn_setDT
				,convert(varchar(19),eu.EvnUslugaOper_disDT,120) as Evn_disDT
				,eu.UslugaPlace_id
				,eu.LpuSection_uid as LpuSection_id
				,eu.Lpu_id
				,eu.Org_uid as Org_id
				,eu.LpuSectionProfile_id
				,eu.MedSpecOms_id
				,eu.MedStaffFact_id
				,eu.PayType_id
				,eu.EvnPrescr_id
				,eu.DiagSetClass_id
				,eu.Diag_id
				,u.UslugaCategory_id
				,eu.UslugaComplex_id
				,eu.EvnUslugaOper_Price as EvnUsluga_Price
				,eu.OperType_id
				,eu.OperDiff_id
				,eu.TreatmentConditionsType_id
				,case when eu.EvnUslugaOper_IsVMT > 1 then 1 else 0 end as EvnUslugaOper_IsVMT
				,case when eu.EvnUslugaOper_IsMicrSurg > 1 then 1 else 0 end as EvnUslugaOper_IsMicrSurg
				,case when eu.EvnUslugaOper_IsOpenHeart > 1 then 1 else 0 end as EvnUslugaOper_IsOpenHeart
				,case when eu.EvnUslugaOper_IsArtCirc > 1 then 1 else 0 end as EvnUslugaOper_IsArtCirc
				,case when eu.EvnUslugaOper_IsEndoskop > 1 then 1 else 0 end as EvnUslugaOper_IsEndoskop
				,case when eu.EvnUslugaOper_IsLazer > 1 then 1 else 0 end as EvnUslugaOper_IsLazer
				,case when eu.EvnUslugaOper_IsKriogen > 1 then 1 else 0 end as EvnUslugaOper_IsKriogen
				,case when eu.EvnUslugaOper_IsRadGraf > 1 then 1 else 0 end as EvnUslugaOper_IsRadGraf
				,eu.EvnUslugaOper_Kolvo as EvnUsluga_Kolvo
			from
				v_EvnUslugaOper eu (nolock)
				left join v_Usluga u with (nolock) on eu.Usluga_id = u.Usluga_id
			where {$where}
		";
		//echo getDebugSQL($query, $data);exit;
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	Получение информации по общей услуге. Метод для API
	 */
	function loadEvnUsluga($data)
	{
		$where = "eu.Lpu_id = :Lpu_id";

		if(!empty($data['EvnUsluga_id'])){
			$where .= " and eu.EvnUsluga_id = :EvnUsluga_id and eu.EvnClass_id = 22";
		}
		if(!empty($data['Evn_pid'])){
			$where .= " and eu.EvnUsluga_pid = :Evn_pid";
		}
		if(!empty($data['Evn_setDT'])){
			$where .= " and eu.EvnUsluga_setDT = :Evn_setDT";
		}
		if(!empty($data['UslugaComplex_id'])){
			$where .= " and eu.UslugaComplex_id = :UslugaComplex_id";
		}

		$query = "
			select
				eu.EvnUsluga_id
				,eu.EvnUsluga_id as Evn_id
				,eu.EvnUsluga_pid as Evn_pid
				,eu.EvnClass_id
				,convert(varchar(19),eu.EvnUsluga_setDT,120) as Evn_setDT
				,convert(varchar(19),eu.EvnUsluga_disDT,120) as Evn_disDT
				,eu.UslugaPlace_id
				,eu.LpuSection_uid as LpuSection_id
				,eu.Lpu_id
				,eu.Org_uid as Org_id
				,eu.LpuSectionProfile_id
				,eu.MedSpecOms_id
				,eu.MedStaffFact_id
				,eu.PayType_id
				,eu.EvnPrescr_id
				,eu.DiagSetClass_id
				,eu.Diag_id
				,u.UslugaCategory_id
				,eu.UslugaComplex_id
				,eu.EvnUsluga_Price
				,eu.EvnUsluga_Kolvo
				,eu.EvnUsluga_Summa
			from
				v_EvnUsluga eu (nolock)
				left join v_Usluga u with (nolock) on eu.Usluga_id = u.Usluga_id
			where {$where}
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	Получение информации о событии. Метод для API
	 */
	function loadEvnUslugaEvnData($data)
	{
		if(empty($data['Evn_pid'])){
			return false;
		}
		$query = "
			select
				e.PersonEvn_id,
				e.Server_id,
				e.Person_id,
				e.Lpu_id
			from
				v_Evn e (nolock)
			where 
				e.Evn_id = :Evn_pid
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Получение списка направлений для панели направлений в ЭМК
	 */
	function loadEvnUslugaPanel($data)
	{
		$response = array();

		if ($this->usePostgreLis) {
			$this->load->swapi('lis');
			$response = $this->lis->GET('EvnUsluga/Panel', $data, 'list');
			if (!$this->isSuccessful($response)) {
				return false;
			}
			$response = $this->addEvnXML($response);
		}

		$resp = $this->_loadEvnUslugaPanel($data, $response);
		if (!is_array($resp)) {
			return false;
		}

		return array_merge($response, $resp);
	}

	/**
	 *  Получение списка направлений для панели направлений в ЭМК
	 */
	function _loadEvnUslugaPanel($data, $excepts)
	{
		$filter = "";

		$except_ids = array();
		foreach($excepts as $except) {
			if (!empty($except['EvnUsluga_id'])) {
				$except_ids[] = $except['EvnUsluga_id'];
			}
		}
		if (count($except_ids) > 0) {
			$except_ids = implode(",", $except_ids);
			$filter .= " and eu.EvnUsluga_id not in ({$except_ids})";
		}

		return $this->queryResult("
			select
				eu.EvnUsluga_id,
				ec.EvnClass_SysNick,
				uc.UslugaComplex_Name,
				convert(varchar(10), eu.EvnUsluga_setDT, 104) as EvnUsluga_setDate,
				eu.EvnUsluga_Count,
				eu.EvnUsluga_Kolvo,
				eus.EvnDiagPLStom_id,
				ex.EvnXml_id
			from
				v_EvnUsluga eu (nolock)
				left join EvnUslugaStom eus (nolock) on eus.EvnUsluga_id = eu.EvnUsluga_id
				left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
				left join v_EvnClass ec (nolock) on ec.EvnClass_id = eu.EvnClass_id
				left join v_EvnXml ex on ex.Evn_id = eu.EvnUsluga_id
			where
				eu.EvnUsluga_pid = :EvnUsluga_pid
				and ISNULL(EU.EvnUsluga_IsVizitCode, 1) = 1
				and eu.EvnUsluga_setDT is not null
				{$filter}
		", $data);
	}

	/**
	 *  Получение списка направлений для панели направлений в ЭМК
	 */
	function getMsfDataForApi($data)
	{
		$res = array();
		$resp = $this->queryResult("
			select
				msf.LpuSection_id as LpuSection_uid,
				msf.LpuSectionProfile_id
			from
				v_MedStaffFact msf (nolock)
			where
				msf.MedStaffFact_id = :MedStaffFact_id
		", $data);

		if (!empty($resp) && !empty($resp[0])) $res = $resp[0];
		return $res;
}

	/**
	 *  Получение списка направлений для панели направлений в ЭМК
	 */
	function getUslugaComplexDataForApi($data)
	{
		$res = array('Error_Msg' => 'Услуга не найдена');

		$resp = $this->queryResult("
			select
				uc.UslugaCategory_id,
				ucf.UslugaComplexTariff_id
			from
				v_UslugaComplex uc (nolock)
				left join v_UslugaComplexTariff ucf (nolock) on ucf.UslugaComplex_id = uc.UslugaComplex_id
			where
				uc.UslugaComplex_id = :UslugaComplex_id
		", $data);

		if (!empty($resp) && !empty($resp[0])) $res = $resp[0];
		return $res;
	}

	/**
	 *  копирование услуг
	 */
	function copyEvnUsluga($data)
	{
		$modelList = array(
			'EvnUslugaOnkoBeam' => 'Лучевое лечение',
			'EvnUslugaOnkoChem' =>  'Химиотерапевтическое лечение',
			'EvnUslugaOnkoGormun' => 'Гормоноиммунотерапевтическое лечение',
			'EvnUslugaOnkoSurg' => 'Хирургическое лечение',
			'EvnUslugaOnkoNonSpec' => 'Неспецифическое лечение',
		);
		
		if (!is_array($data['ids']) || !count($data['ids'])) {
			return array(array('success' => true, 'Error_Msg' => ''));
		}
		
		$doubles = '';
		
		foreach($data['ids'] as $id) {
			$usluga_params = $this->getFirstRowFromQuery("
				select 
					EU.EvnClass_SysNick
					,onkoucat.UslugaComplexAttributeType_SysNick
					,EU.Person_id
					,EU.UslugaComplex_id
					,EU.EvnUsluga_pid
					,convert(varchar(20), EU.EvnUsluga_setDT, 120) as EvnUsluga_setDT
					,convert(varchar(20), EU.EvnUsluga_disDT, 120) as EvnUsluga_disDT
				from v_EvnUsluga EU (nolock) 
					outer apply (
						select top 1 UslugaComplexAttributeType_SysNick
						from v_UslugaComplexAttribute UCA with (nolock)
							inner join v_UslugaComplexAttributeType UCAT with (nolock) on UCAT.UslugaComplexAttributeType_id = UCA.UslugaComplexAttributeType_id
						where UCA.UslugaComplex_id = EU.UslugaComplex_id
							and UslugaComplexAttributeType_SysNick in ('XimLech','LuchLech','GormImunTerLech','XirurgLech')
					) onkoucat
				where EU.EvnUsluga_id = ?
			", array($id));
			
			$uc_check = $this->getFirstRowFromQuery("
				select top 1 EvnUsluga_id, EvnClass_SysNick
				from v_EvnUsluga (nolock)
				where 
					EvnClass_SysNick in ('EvnUslugaOnkoBeam', 'EvnUslugaOnkoChem', 'EvnUslugaOnkoGormun', 'EvnUslugaOnkoSurg', 'EvnUslugaOnkoNonSpec') and
					UslugaComplex_id = :UslugaComplex_id and 
					Person_id = :Person_id and
					EvnUsluga_pid = :EvnUsluga_pid and
					cast(EvnUsluga_setDT as date) = cast(:EvnUsluga_setDT as date) and
					cast(isnull(EvnUsluga_disDT,EvnUsluga_setDT) as date) = cast(:EvnUsluga_disDT as date)
			", array(
				'EvnUsluga_setDT' => $usluga_params['EvnUsluga_setDT'],
				'EvnUsluga_disDT' => !empty($usluga_params['EvnUsluga_disDT']) ? $usluga_params['EvnUsluga_disDT'] : $usluga_params['EvnUsluga_setDT'],
				'UslugaComplex_id' => $usluga_params['UslugaComplex_id'],
				'Person_id' => $usluga_params['Person_id'],
				'EvnUsluga_pid' => $usluga_params['EvnUsluga_pid']
			));
			
			if ($uc_check != false) {
				$doubles = $uc_check['EvnClass_SysNick'];
				continue; // уже есть такая услуга
			}
			
			$usluga_data = $this->loadEvnUslugaEditForm(array(
				'id' => $id,
				'Lpu_id' => $data['Lpu_id'],
				'session' => $data['session'],
				'class' => $usluga_params['EvnClass_SysNick']
			));
			
			
			$usluga_data = $usluga_data[0];

			if ($usluga_data['UslugaPlace_id'] == 1) {
				$usluga_data['Lpu_uid'] = $data['Lpu_id'];
			}
			// Для опер. услуг с идентификаторами какая-то ерунда - в Lpu_uid созраняется Org_id
			// Костыль:
			else if ($usluga_params['EvnClass_SysNick'] == 'EvnUslugaOper' && $usluga_data['UslugaPlace_id'] == 2) {
				$Lpu_uid = $this->getFirstResultFromQuery("select top 1 Lpu_id from v_Lpu_all with (nolock) where Org_id = :Org_id", array('Org_id' => $usluga_data['Lpu_uid']));

				if ( $Lpu_uid !== false ) {
					$usluga_data['Lpu_uid'] = $Lpu_uid;
				}
			}

			switch($usluga_params['UslugaComplexAttributeType_SysNick']) {
				
				case 'XimLech':
					$this->load->model('EvnUslugaOnkoChem_model', 'EvnUslugaOnkoChem');
					$p = array(
						'isAutoDouble' => true,
						'EvnUslugaOnkoChem_pid' => isset($usluga_data['EvnUslugaOper_pid']) ? $usluga_data['EvnUslugaOper_pid'] : $usluga_data['EvnUslugaCommon_pid'],
						'Lpu_id' => $data['Lpu_id'],
						'Server_id' => $usluga_data['Server_id'],
						'PersonEvn_id' => $usluga_data['PersonEvn_id'],
						'EvnUslugaOnkoChem_setDate' => date('Y-m-d', strtotime(isset($usluga_data['EvnUslugaOper_setDate']) ? $usluga_data['EvnUslugaOper_setDate'] : $usluga_data['EvnUslugaCommon_setDate'])),
						'EvnUslugaOnkoChem_setTime' => isset($usluga_data['EvnUslugaOper_setTime']) ? $usluga_data['EvnUslugaOper_setTime'] : $usluga_data['EvnUslugaCommon_setTime'],
						'EvnUslugaOnkoChem_disDate' => !empty($usluga_data['EvnUslugaOper_disDate']) || !empty($usluga_data['EvnUslugaCommon_disDate']) ? date('Y-m-d', strtotime(isset($usluga_data['EvnUslugaOper_disDate']) ? $usluga_data['EvnUslugaOper_disDate'] : $usluga_data['EvnUslugaCommon_disDate'])) : null,
						'EvnUslugaOnkoChem_disTime' => isset($usluga_data['EvnUslugaOper_disTime']) ? $usluga_data['EvnUslugaOper_disTime'] : $usluga_data['EvnUslugaCommon_disTime'],
						'Morbus_id' => $data['Morbus_id'],
						'PayType_id' => $usluga_data['PayType_id'],
						'PayType_SysNickOMS' => getPayTypeSysNickOMS(),
						'UslugaPlace_id' => $usluga_data['UslugaPlace_id'],
						'Lpu_uid' => $usluga_data['Lpu_uid'],
						'EvnUslugaOnkoChem_id' => null,
						'OnkoUslugaChemKindType_id' => null,
						'OnkoUslugaChemFocusType_id' => null,
						'OnkoUslugaChemStageType_id' => null,
						'EvnUslugaOnkoChem_Scheme' => null,
						'AggType_id' => null,
						'OnkoTreatType_id' => null,
						'TreatmentConditionsType_id' => null,
						'DrugTherapyLineType_id' => null,
						'DrugTherapyLoopType_id' => null,
						'UslugaComplex_id' => $usluga_data['UslugaComplex_id'],
						'pmUser_id' => $data['pmUser_id']
					);
					$response = $this->EvnUslugaOnkoChem->save($p);
					break;
					
				case 'LuchLech':
					$this->load->model('EvnUslugaOnkoBeam_model', 'EvnUslugaOnkoBeam');
					$p = array(
						'isAutoDouble' => true,
						'EvnUslugaOnkoBeam_pid' => isset($usluga_data['EvnUslugaOper_pid']) ? $usluga_data['EvnUslugaOper_pid'] : $usluga_data['EvnUslugaCommon_pid'],
						'Lpu_id' => $data['Lpu_id'],
						'Server_id' => $usluga_data['Server_id'],
						'PersonEvn_id' => $usluga_data['PersonEvn_id'],
						'EvnUslugaOnkoBeam_setDate' => date('Y-m-d', strtotime(isset($usluga_data['EvnUslugaOper_setDate']) ? $usluga_data['EvnUslugaOper_setDate'] : $usluga_data['EvnUslugaCommon_setDate'])),
						'EvnUslugaOnkoBeam_setTime' => isset($usluga_data['EvnUslugaOper_setTime']) ? $usluga_data['EvnUslugaOper_setTime'] : $usluga_data['EvnUslugaCommon_setTime'],
						'EvnUslugaOnkoBeam_disDate' => !empty($usluga_data['EvnUslugaOper_disDate']) || !empty($usluga_data['EvnUslugaCommon_disDate']) ? date('Y-m-d', strtotime(isset($usluga_data['EvnUslugaOper_disDate']) ? $usluga_data['EvnUslugaOper_disDate'] : $usluga_data['EvnUslugaCommon_disDate'])) : null,
						'EvnUslugaOnkoBeam_disTime' => isset($usluga_data['EvnUslugaOper_disTime']) ? $usluga_data['EvnUslugaOper_disTime'] : $usluga_data['EvnUslugaCommon_disTime'],
						'Morbus_id' => $data['Morbus_id'],
						'PayType_id' => $usluga_data['PayType_id'],
						'PayType_SysNickOMS' => getPayTypeSysNickOMS(),
						'UslugaPlace_id' => $usluga_data['UslugaPlace_id'],
						'Lpu_uid' => $usluga_data['Lpu_uid'],
						'EvnUslugaOnkoBeam_id' => null,
						'EvnUslugaOnkoBeam_CountFractionRT' => 1,
						'EvnUslugaOnkoBeam_TotalDoseTumor' => null,
						'EvnUslugaOnkoBeam_TotalDoseLymph' => null,
						'EvnUslugaOnkoBeam_TotalDoseRegZone' => null,
						'OnkoUslugaBeamUnitType_did' => null,
						'OnkoPlanType_id' => null,
						'OnkoUslugaBeamIrradiationType_id' => null,
						'OnkoUslugaBeamKindType_id' => null,
						'OnkoUslugaBeamMethodType_id' => null,
						'OnkoUslugaBeamRadioModifType_id' => null,
						'OnkoUslugaBeamUnitType_id' => null,
						'OnkoUslugaBeamFocusType_id' => null,
						'AggType_id' => null,
						'OnkoTreatType_id' => null,
						'OnkoRadiotherapy_id' => null,
						'TreatmentConditionsType_id' => null,
						'UslugaComplex_id' => $usluga_data['UslugaComplex_id'],
						'pmUser_id' => $data['pmUser_id']
					);
					$response = $this->EvnUslugaOnkoBeam->save($p);
					break;
					
				case 'GormImunTerLech':
					$this->load->model('EvnUslugaOnkoGormun_model', 'EvnUslugaOnkoGormun');
					$p = array(
						'isAutoDouble' => true,
						'EvnUslugaOnkoGormun_pid' => isset($usluga_data['EvnUslugaOper_pid']) ? $usluga_data['EvnUslugaOper_pid'] : $usluga_data['EvnUslugaCommon_pid'],
						'Lpu_id' => $data['Lpu_id'],
						'Server_id' => $usluga_data['Server_id'],
						'PersonEvn_id' => $usluga_data['PersonEvn_id'],
						'EvnUslugaOnkoGormun_setDate' => date('Y-m-d', strtotime(isset($usluga_data['EvnUslugaOper_setDate']) ? $usluga_data['EvnUslugaOper_setDate'] : $usluga_data['EvnUslugaCommon_setDate'])),
						'EvnUslugaOnkoGormun_setTime' => isset($usluga_data['EvnUslugaOper_setTime']) ? $usluga_data['EvnUslugaOper_setTime'] : $usluga_data['EvnUslugaCommon_setTime'],
						'EvnUslugaOnkoGormun_disDate' => !empty($usluga_data['EvnUslugaOper_disDate']) || !empty($usluga_data['EvnUslugaCommon_disDate']) ? date('Y-m-d', strtotime(isset($usluga_data['EvnUslugaOper_disDate']) ? $usluga_data['EvnUslugaOper_disDate'] : $usluga_data['EvnUslugaCommon_disDate'])) : null,
						'EvnUslugaOnkoGormun_disTime' => isset($usluga_data['EvnUslugaOper_disTime']) ? $usluga_data['EvnUslugaOper_disTime'] : $usluga_data['EvnUslugaCommon_disTime'],
						'Morbus_id' => $data['Morbus_id'],
						'PayType_id' => $usluga_data['PayType_id'],
						'PayType_SysNickOMS' => getPayTypeSysNickOMS(),
						'UslugaPlace_id' => $usluga_data['UslugaPlace_id'],
						'Lpu_uid' => $usluga_data['Lpu_uid'],
						'EvnUslugaOnkoGormun_id' => null,
						'EvnUslugaOnkoGormun_IsBeam' => null,
						'EvnUslugaOnkoGormun_IsSurg' => null,
						'EvnUslugaOnkoGormun_IsDrug' => null,
						'EvnUslugaOnkoGormun_IsOther' => null,
						'OnkoUslugaGormunFocusType_id' => null,
						'AggType_id' => null,
						'OnkoRadiotherapy_id' => null,
						'OnkoTreatType_id' => null,
						'TreatmentConditionsType_id' => null,
						'DrugTherapyLineType_id' => null,
						'DrugTherapyLoopType_id' => null,
						'UslugaComplex_id' => $usluga_data['UslugaComplex_id'],
						'EvnUslugaOnkoGormun_CountFractionRT' => 1,
						'EvnUslugaOnkoGormun_TotalDoseTumor' => null,
						'EvnUslugaOnkoGormun_TotalDoseRegZone' => null,
						'pmUser_id' => $data['pmUser_id']
					);
					$response = $this->EvnUslugaOnkoGormun->save($p);
					break;
					
				case 'XirurgLech':
					$this->load->model('EvnUslugaOnkoSurg_model', 'EvnUslugaOnkoSurg');
					$p = array(
						'isAutoDouble' => true,
						'EvnUslugaOnkoSurg_pid' => isset($usluga_data['EvnUslugaOper_pid']) ? $usluga_data['EvnUslugaOper_pid'] : $usluga_data['EvnUslugaCommon_pid'],
						'Lpu_id' => $data['Lpu_id'],
						'Server_id' => $usluga_data['Server_id'],
						'PersonEvn_id' => $usluga_data['PersonEvn_id'],
						'EvnUslugaOnkoSurg_setDate' => date('Y-m-d', strtotime(isset($usluga_data['EvnUslugaOper_setDate']) ? $usluga_data['EvnUslugaOper_setDate'] : $usluga_data['EvnUslugaCommon_setDate'])),
						'EvnUslugaOnkoSurg_setTime' => isset($usluga_data['EvnUslugaOper_setTime']) ? $usluga_data['EvnUslugaOper_setTime'] : $usluga_data['EvnUslugaCommon_setTime'],
						'EvnUslugaOnkoSurg_disDate' => !empty($usluga_data['EvnUslugaOper_disDate']) || !empty($usluga_data['EvnUslugaCommon_disDate']) ? date('Y-m-d', strtotime(isset($usluga_data['EvnUslugaOper_disDate']) ? $usluga_data['EvnUslugaOper_disDate'] : $usluga_data['EvnUslugaCommon_disDate'])) : null,
						'EvnUslugaOnkoSurg_disTime' => isset($usluga_data['EvnUslugaOper_disTime']) ? $usluga_data['EvnUslugaOper_disTime'] : $usluga_data['EvnUslugaCommon_disTime'],
						'Morbus_id' => $data['Morbus_id'],
						'PayType_id' => $usluga_data['PayType_id'],
						'PayType_SysNickOMS' => getPayTypeSysNickOMS(),
						'UslugaPlace_id' => $usluga_data['UslugaPlace_id'],
						'Lpu_uid' => $usluga_data['Lpu_uid'],
						'EvnUslugaOnkoSurg_id' => null,
						'MedPersonal_id' => $usluga_data['MedPersonal_id'],
						'AggType_sid' => null,
						'OnkoSurgTreatType_id' => null,
						'OnkoSurgicalType_id' => null,
						'UslugaComplex_id' => $usluga_data['UslugaComplex_id'],
						'OperType_id' => isset($usluga_data['OperType_id']) ? $usluga_data['OperType_id'] : null,
						'AggType_id' => null,
						'TreatmentConditionsType_id' => null,
						'pmUser_id' => $data['pmUser_id']
					);
					$response = $this->EvnUslugaOnkoSurg->save($p);
					break;

				default:
					$this->load->model('EvnUslugaOnkoNonSpec_model', 'EvnUslugaOnkoNonSpec');
					$p = array(
						'isAutoDouble' => true,
						'EvnUslugaOnkoNonSpec_pid' => isset($usluga_data['EvnUslugaOper_pid']) ? $usluga_data['EvnUslugaOper_pid'] : $usluga_data['EvnUslugaCommon_pid'],
						'Lpu_id' => $data['Lpu_id'],
						'Server_id' => $usluga_data['Server_id'],
						'PersonEvn_id' => $usluga_data['PersonEvn_id'],
						'EvnUslugaOnkoNonSpec_setDT' => isset($usluga_data['EvnUslugaOper_setDT']) ? $usluga_data['EvnUslugaOper_setDT'] : $usluga_data['EvnUslugaCommon_setDT'],
						'EvnUslugaOnkoNonSpec_disDT' => null,
						'Morbus_id' => $data['Morbus_id'],
						'PayType_id' => $usluga_data['PayType_id'],
						'PayType_SysNickOMS' => getPayTypeSysNickOMS(),
						'UslugaPlace_id' => $usluga_data['UslugaPlace_id'],
						'Lpu_uid' => $usluga_data['Lpu_uid'],
						'EvnUslugaOnkoNonSpec_id' => null,
						'MedPersonal_id' => $usluga_data['MedPersonal_id'],
						'UslugaComplex_id' => $usluga_data['UslugaComplex_id'],
						'pmUser_id' => $data['pmUser_id']
					);
					$response = $this->EvnUslugaOnkoNonSpec->save($p);
					break;
			}
		}

		if (!empty($doubles)) {
			return array(array('success' => false, 'Error_Msg' => 'Найдено пересечение периодов выполнения услуг. Необходимо проверить дату начала выполнения и дату окончания выполнения услуги в разделе «Услуги» и в разделе «'.$modelList[$doubles].'» специфики по онкологии текущего посещения / движения.'));
		} else {
			return array(array('success' => true, 'Error_Msg' => ''));
		}
			
			
		
	}
	

	/**
	 *  дублирование онкоуслуг
	 */
	function saveEvnUslugaOnko($data)
	{
		if(empty($data['EvnUsluga_pid']) || empty($data['UslugaComplex_id'])) {
			return false;
		}
		
		$uc_check = $this->getFirstResultFromQuery("
			select top 1 EvnUslugaCommon_id 
			from v_EvnUslugaCommon (nolock)
			where 
				UslugaComplex_id = :UslugaComplex_id and 
				Person_id = :Person_id and
				EvnUslugaCommon_setDate <= :EvnUsluga_setDT and
				isnull(EvnUslugaCommon_disDate, EvnUslugaCommon_setDate) >= :EvnUsluga_setDT
		", array(
			'EvnUsluga_setDT' => $data['EvnUsluga_setDT'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'Person_id' => $data['Person_id'],
		));
		
		if ($uc_check != false) return array(array('EvnUslugaCommon_id' => -1));
		
		$evndata = $this->getFirstRowFromQuery("
			select Lpu_id, MedStaffFact_id, LpuSection_id, MedPersonal_id, LpuSectionProfile_id from v_evnvizitpl (nolock) where evnvizitpl_id = :evn_id
			union all
			select Lpu_id, MedStaffFact_id, LpuSection_id, MedPersonal_id, LpuSectionProfile_id from v_evnsection (nolock) where evnsection_id = :evn_id
		", array('evn_id' => $data['EvnUsluga_pid']));
		
		if (isset($evndata['Lpu_id']) && $evndata['Lpu_id'] != $data['Lpu_uid']) return false;
		
		$data['EvnUslugaCommon_id'] = null;
		$data['MedPersonal_id'] = isset($evndata['MedPersonal_id']) ? $evndata['MedPersonal_id'] : null;
		$data['LpuSection_uid'] = isset($evndata['LpuSection_id']) ? $evndata['LpuSection_id'] : null;
		$data['MedStaffFact_id'] = isset($evndata['MedStaffFact_id']) ? $evndata['MedStaffFact_id'] : null;
		$data['LpuSectionProfile_id'] = isset($evndata['LpuSectionProfile_id']) ? $evndata['LpuSectionProfile_id'] : null;
		$data['Lpu_uid'] = null;
		$data['Org_uid'] = null;
		$data['PayType_id'] = $this->getFirstResultFromQuery("
			select top 1 PayType_id
			from v_PayType with (nolock)
			where PayType_SysNick = 'oms'
		");
		$data['EvnUslugaCommon_pid'] = $data['EvnUsluga_pid'];
		$data['EvnUslugaCommon_rid'] = $data['EvnUsluga_pid'];
		$data['EvnUslugaCommon_setDate'] = date('Y-m-d', strtotime($data['EvnUsluga_setDT']));
		$data['EvnUslugaCommon_setTime'] = date('H:i', strtotime($data['EvnUsluga_setDT']));
		$data['EvnUslugaCommon_disDate'] = !empty($data['EvnUsluga_disDT']) ? date('Y-m-d', strtotime($data['EvnUsluga_disDT'])) : $data['EvnUslugaCommon_setDate'];
		$data['EvnUslugaCommon_disTime'] = !empty($data['EvnUsluga_disDT']) ? date('H:i', strtotime($data['EvnUsluga_disDT'])) : $data['EvnUslugaCommon_setTime'];
		$data['fromOnko'] = true;
		
		return $this->saveEvnUslugaCommon($data);
	}
	

	/**
	 *  дублирование онкоуслуг
	 */
	function saveEvnUslugaOnkoOper($data)
	{
		if(empty($data['EvnUsluga_pid']) || empty($data['UslugaComplex_id'])) {
			return false;
		}
		
		$uc_check = $this->getFirstResultFromQuery("
			select top 1 EvnUslugaOper_id 
			from v_EvnUslugaOper (nolock)
			where 
				UslugaComplex_id = :UslugaComplex_id and 
				Person_id = :Person_id and
				EvnUslugaOper_setDate <= :EvnUsluga_setDT and
				isnull(EvnUslugaOper_disDate, EvnUslugaOper_setDate) >= :EvnUsluga_setDT
		", array(
			'EvnUsluga_setDT' => $data['EvnUsluga_setDT'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'Person_id' => $data['Person_id'],
		));
		
		if ($uc_check != false) return array(array('EvnUslugaOper_id' => -1));
		
		$evndata = $this->getFirstRowFromQuery("
			select Lpu_id, MedStaffFact_id, LpuSection_id, MedPersonal_id, LpuSectionProfile_id from v_evnvizitpl (nolock) where evnvizitpl_id = :evn_id
			union all
			select Lpu_id, MedStaffFact_id, LpuSection_id, MedPersonal_id, LpuSectionProfile_id from v_evnsection (nolock) where evnsection_id = :evn_id
		", array('evn_id' => $data['EvnUsluga_pid']));
		
		if (isset($evndata['Lpu_id']) && $evndata['Lpu_id'] != $data['Lpu_uid']) return false;
		
		$data['EvnUslugaOper_id'] = null;
		if (empty($data['MedPersonal_id']) ) {
			$data['MedPersonal_id'] = isset($evndata['MedPersonal_id']) ? $evndata['MedPersonal_id'] : null;
		}
		$data['LpuSection_uid'] = isset($evndata['LpuSection_id']) ? $evndata['LpuSection_id'] : null;
		$data['MedStaffFact_id'] = isset($evndata['MedStaffFact_id']) ? $evndata['MedStaffFact_id'] : null;
		$data['LpuSectionProfile_id'] = isset($evndata['LpuSectionProfile_id']) ? $evndata['LpuSectionProfile_id'] : null;
		$data['EvnUslugaOper_IsVMT'] = null;
		$data['EvnUslugaOper_IsMicrSurg'] = null;
		$data['EvnUslugaOper_IsOpenHeart'] = null;
		$data['EvnUslugaOper_IsArtCirc'] = null;
		$data['EvnUslugaOper_Kolvo'] = 1;
		$data['EvnUslugaOper_IsEndoskop'] = 1;
		$data['EvnUslugaOper_IsLazer'] = 1;
		$data['EvnUslugaOper_IsKriogen'] = 1;
		$data['EvnUslugaOper_IsRadGraf'] = 1;
		$data['OperType_id'] = $data['OperType_id'];
		$data['OperDiff_id'] = 5;
		$data['EvnPrescr_id'] = null;
		$data['Lpu_uid'] = null;
		$data['Org_uid'] = null;
		$data['PayType_id'] = $this->getFirstResultFromQuery("
			select top 1 PayType_id
			from v_PayType with (nolock)
			where PayType_SysNick = 'oms'
		");
		$data['EvnUslugaOper_pid'] = $data['EvnUsluga_pid'];
		$data['EvnUslugaOper_rid'] = $data['EvnUsluga_pid'];
		$data['EvnUslugaOper_setDate'] = date('Y-m-d', strtotime($data['EvnUsluga_setDT']));
		$data['EvnUslugaOper_setTime'] = date('H:i', strtotime($data['EvnUsluga_setDT']));
		$data['EvnUslugaOper_disDate'] = !empty($data['EvnUsluga_disDT']) ? date('Y-m-d', strtotime($data['EvnUsluga_disDT'])) : $data['EvnUslugaOper_setDate'];
		$data['EvnUslugaOper_disTime'] = !empty($data['EvnUsluga_disDT']) ? date('H:i', strtotime($data['EvnUsluga_disDT'])) : $data['EvnUslugaOper_setTime'];
		$data['fromOnko'] = true;
		
		return $this->saveEvnUslugaOper($data);
	}

	/**
	 * Справочник "Объем выполнения услуг"
	 */
	function getUslugaExecutionTypeList($data){
		return $this->queryResult("
			select
				*
			from
				v_UslugaExecutionType (nolock)
		", $data);
	}

	/**
	 * Получение данных для создания протокола результатов услуги
	 */
	function getDataForResults($data) {
		$query = "
			with x as (
				select
					dbo.tzgetdate() as curdate
			)
			
			select
				(case
					when (coalesce(
						(
							SELECT
								Lpu.Lpu_IsSecret
							FROM
								dbo.v_Lpu Lpu with(nolock)
							WHERE
								Lpu_id = RootTable.Lpu_id), 1) = 2)
								AND	(coalesce(Patient.Person_IsEncrypHIV, 1) = 2
						)
						then Patient_PersonEncrypHIV.PersonEncrypHIV_Encryp
					else coalesce(Patient.Person_SurName, '-') + ' ' +
				(case
					when Patient.Person_FirName is not null and len(Patient.Person_FirName) > 0
						then substring(Patient.Person_FirName, 1, 1) + '.'
					else '' end) +
				(case
					when Patient.Person_SecName IS not null and len(Patient.Person_SecName) > 0
						then substring(Patient.Person_SecName, 1, 1) + '.'
					else '' end)
				end)                                                           as \"MarkerData_12\",
				datepart(year, Age((select curdate from x), Patient.Person_BirthDay)) as \"MarkerData_20\",
				PatientPersonCard.PersonCard_Code                              as \"MarkerData_52\",
				convert(varchar(10), (select curdate from x), 104)             as \"MarkerData_70\",
				EvnLabRequest.EvnLabRequest_Ward                               as \"MarkerData_134\",
				convert(varchar(10), EvnLabSample.EvnLabSample_setDT, 104)     as \"MarkerData_135\",
				EvnLabRequest.EvnLabRequest_UslugaName                         as \"MarkerData_136\",
				null                                                           as \"MarkerData_137\",
				EvnLabSample.MedPersonal_aid                                   as \"MedPersonal_aid\",
				EvnLabSample.Lpu_aid                                           as \"Lpu_aid\",
				:Evn_id                                                        as \"Evn_id\",
				EvnLabSample_LpuSectionA.LpuSection_Name                       as \"MarkerData_138\",
				EvnLabSample_LpuA.Lpu_Name                                     as \"MarkerData_139\"
			from
				v_EvnUslugaPar as RootTable with(nolock)
				left join v_PersonState as Patient with(nolock) on Patient.Person_id = RootTable.Person_id
				left join v_PersonEncrypHIV as Patient_PersonEncrypHIV with(nolock) on Patient_PersonEncrypHIV.Person_id = Patient.Person_id
				left join v_PersonCard as PatientPersonCard with(nolock) on PatientPersonCard.Person_id = RootTable.Person_id and PatientPersonCard.LpuAttachType_id = 1
				left join v_EvnDirection_all as EvnUslugaPar_EvnDirection with(nolock) on EvnUslugaPar_EvnDirection.EvnDirection_id = RootTable.EvnDirection_id
				left join v_EvnLabRequest as EvnLabRequest with(nolock) on EvnLabRequest.EvnDirection_id = EvnUslugaPar_EvnDirection.EvnDirection_id
				outer apply(
					select top 1
						*
					from
						v_EvnLabSample
					where
						EvnLabRequest_id = EvnLabRequest.EvnLabRequest_id
				) EvnLabSample
				left join v_LpuSection as EvnLabSample_LpuSectionA with(nolock) on EvnLabSample_LpuSectionA.LpuSection_id = EvnLabSample.LpuSection_aid
				left join v_Lpu as EvnLabSample_LpuA with(nolock) on EvnLabSample_LpuA.Lpu_id = EvnLabSample.Lpu_aid
			where
				RootTable.EvnUslugaPar_id = :Evn_id
		";

		$res = $this->queryResult($query, $data);

		//названия услуг хранятся в виде json-строки
		if (isset($res[0]['MarkerData_136'])) {
			$x = json_decode($res[0]['MarkerData_136'], true);
			$names = [];
			foreach ($x as $usluga) {
				$names[] = $usluga['UslugaComplex_Name'];
			}
			$res[0]['MarkerData_136'] = implode(', ', $names);
		}

		return $res[0];
	}

	/**
	 * Поиск услуг по Prescr_id
	 */
	function getUslugaByPrescr($data) {
		$query = "
			select top 1
				EvnPrescr_id as \"EvnPrescr_id\",
				EvnUsluga_id as \"EvnUsluga_id\",
				convert(varchar(10), EvnUsluga_setDate, 120) as \"EvnUsluga_setDate\"
			from 
				v_EvnUsluga with(nolock)
			where
				EvnPrescr_id = :EvnPrescr_id
		";

		return $this->queryResult($query, $data);
	}

	/**
	 * Поиск данных evn
	 */
	function getEvnParams($data) {
		$query = "
			select top 1
				Evn_id as \"Evn_id\",
				Evn_pid as \"Evn_pid\",
				Evn_rid as \"Evn_rid\",
				EvnClass_id as \"EvnClass_id\",
				EvnClass_SysNick as \"EvnClass_SysNick\"
			from
				v_Evn with(nolock)
			where
				Evn_id = :Evn_id
		";

		return $this->getFirstRowFromQuery($query, $data);
	}

    /**
     * Сохранение схем лекарственной терапии
     */
    protected function saveUslugaMedTypeLink($Evn_id, $UslugaMedType_id)
    {
        if (getRegionNick() === 'kz') {
            $this->load->model('UslugaMedType_model');

            $result = $this->UslugaMedType_model->saveUslugaMedTypeLink([
                'UslugaMedType_id' => $UslugaMedType_id,
                'Evn_id' => $Evn_id,
                'pmUser_id' => $this->promedUserId
            ]);

            if (!$this->isSuccessful($result)) {
                throw new Exception($result[0]['Error_Msg'], $result[0]['Error_Code']);
            }
        }
    }
    
    function addEvnXML($data)
	{
		foreach ($data as $key => $usl) {
			$xml = $this->getFirstResultFromQuery("
				select top 1
					EvnXml_id
				from v_EvnXml with (nolock)
				where Evn_id = :EvnUsluga_id
				order by EvnXml_insDT desc
			", $usl);
			
			if (!empty($xml)) {
				$data[$key]['EvnXml_id'] = $xml;
			}
		}
		
		return $data;
	}
}
