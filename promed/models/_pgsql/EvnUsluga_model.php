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
class EvnUsluga_model extends swPgModel {
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
			select
				eu.EvnUslugaStom_id as \"EvnUslugaStom_id\"
			from
				v_EvnUslugaStom eu
				INNER JOIN LATERAL (
					select
						MesUsluga_id
					from
						v_MesUsluga
					where
						Mes_id = :oldMes_id and UslugaComplex_id = EU.UslugaComplex_id and MesUsluga_IsNeedUsluga = 2
                    limit 1
				) MU ON true
				LEFT JOIN LATERAL (
					select
						MesUsluga_IsNeedUsluga
					from
						v_MesUsluga
					where
						Mes_id = :newMes_id and UslugaComplex_id = EU.UslugaComplex_id
                    limit 1
				) MUNEW ON true
			where
				eu.EvnDiagPLStom_id = :EvnDiagPLStom_id
				and COALESCE(MUNEW.MesUsluga_IsNeedUsluga, 1) = 1
            limit 1
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
			update EvnUsluga
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
					select WG.WorkGraph_id
					from v_WorkGraph WG
					where (
						CAST(WG.WorkGraph_begDT as date) <= CAST(dbo.tzGetDate() as date)
						and CAST(WG.WorkGraph_endDT as date) >= CAST(dbo.tzGetDate() as date)
					)
					and WG.MedStaffFact_id = :user_MedStaffFact_id
				)
			)';
			$join_msf = 'left join v_MedStaffFact MSF on MSF.MedStaffFact_id = :user_MedStaffFact_id';
			$params['user_MedStaffFact_id'] = $data['session']['CurMedStaffFact_id'];
		}

		$selectPersonData = "PS.Person_SurName||' '||PS.Person_FirName||' '||coalesce(PS.Person_SecName,'') as \"Person_Fio\",
				to_char (PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_Birthday\",";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh on peh.Person_id = PS.Person_id";
			$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null
					then ps.Person_SurName||' '||ps.Person_FirName||' '||coalesce(ps.Person_SecName,'')
					else peh.PersonEncrypHIV_Encryp
				end as \"Person_Fio\",
				null as \"Person_Birthday\",";
		}

		$query = "
			SELECT
				case when {$accessType} then 'edit' else 'view' end as \"accessType\",
				EUC.EvnUslugaCommon_id as \"EvnUslugaCommon_id\",
				EUC.EvnUslugaCommon_pid as \"EvnUslugaCommon_pid\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				EUC.Person_id as \"Person_id\",
				EUC.PersonEvn_id as \"PersonEvn_id\",
				EUC.Server_id as \"Server_id\",
				EUC.Usluga_id as \"Usluga_id\",
				EUC.UslugaComplex_id as \"UslugaComplex_id\",
				EUC.EvnUslugaCommon_isCito as \"EvnUslugaCommon_isCito\",
				EUC.EvnUslugaCommon_Kolvo as \"EvnUslugaCommon_Kolvo\",
				EUC.PayType_id as \"PayType_id\",
				--EUC.PrehospDirect_id,
				--EUC.TimetablePar_id,
				EUC.Lpu_id as \"Lpu_id\",
				EUC.LpuSection_uid as \"LpuSection_uid\",
				EUC.MedPersonal_id as \"MedStaffFact_uid\",
				DLpuSection.Lpu_id as \"Lpu_did\",
				EUC.LpuSection_uid as \"LpuSection_uid\",
				EUC.Org_uid as \"Org_uid\",
				--EUC.MedPersonal_did as MedStaffFact_did,
				EUC.MedPersonal_sid as \"MedStaffFact_sid\",
				{$selectPersonData}
				D.Diag_id as \"Diag_id\",
				coalesce(D4.Diag_Code,D3.Diag_Code,D2.Diag_Code,D.Diag_Code) as \"Diag_Code\",
				coalesce(D4.Diag_Name,D3.Diag_Name,D2.Diag_Name,D.Diag_Name) as \"Diag_Name\",
				UC.UslugaComplex_Name as \"Usluga_Name\",
				EUC.EvnUslugaCommon_id as \"Usluga_Number\",
				ULpu.Lpu_Nick as \"Lpu_Nick\",
				ULpu.Lpu_Name as \"Lpu_Name\",
				ULpu.UAddress_Address as \"Lpu_Address\",
				ULpuSection.LpuSection_Code as \"LpuSection_Code\",
				ULpuSection.LpuSection_Name as \"LpuSection_Name\",
				to_char (EUC.EvnUslugaCommon_setDT, 'dd.mm.yyyy') as \"EvnUslugaCommon_setDate\",
                COALESCE(to_char(EUC.EvnUslugaCommon_setTime, 'HH24:mi'), '') as \"EvnUslugaCommon_setTime\",
				MP.Person_SurName || ' ' || LEFT(MP.Person_FirName, 1)  || '. ' || COALESCE(LEFT(MP.Person_SecName, 1) || '.', '') as \"MedPersonal_Fin\",
				DLpuSection.LpuSection_Code as \"DirectSubject_Code\",-- кем направлен
                DLpuSection.LpuSection_Name as \"DirectSubject_Name\",-- кем направлен
                DOrg.Org_Code as \"OrgDirectSubject_Code\",
                COALESCE(DOrg.Org_Nick,NaprLpu.Lpu_Nick) as \"OrgDirectSubject_Name\", -- кем направлен
				ED.EvnDirection_Num as \"EvnDirection_Num\", -- номер направления
				to_char (ED.EvnDirection_setDate, 'dd.mm.yyyy') as \"EvnDirection_setDate\",
				case
				    when EVPLMedPersonal.Person_SurName is not null then EVPLMedPersonal.Person_SurName || ' ' || LEFT(EVPLMedPersonal.Person_FirName, 1)  || '. ' || COALESCE(LEFT(EVPLMedPersonal.Person_SecName, 1) || '.', '')
				    when DMedPersonal.Person_SurName is not null then DMedPersonal.Person_SurName || ' ' || LEFT(DMedPersonal.Person_FirName, 1)  || '. ' || COALESCE(LEFT(DMedPersonal.Person_SecName, 1) || '.', '')
				    when SecMedPersonal.Person_SurName is not null then SecMedPersonal.Person_SurName || ' ' || LEFT(SecMedPersonal.Person_FirName, 1)  || '. ' || COALESCE(LEFT(SecMedPersonal.Person_SecName, 1) || '.', '')
				    else CMedPersonal.Person_SurName || ' ' || LEFT(CMedPersonal.Person_FirName, 1)  || '. ' || COALESCE(LEFT(CMedPersonal.Person_SecName, 1) || '.', '')
				end as \"MedPersonalDirect_Fin\",
				case when EvnLabRequest.EvnLabRequest_id is null then 0 else 1 end as \"isLab\",
				--EUC.Study_uid
				doc.EvnXml_id as \"EvnXml_id\"
			FROM v_EvnUslugaCommon EUC
				left join v_Person_all PS  on EUC.Person_id = PS.Person_id AND EUC.PersonEvn_id = PS.PersonEvn_id AND EUC.Server_id = PS.Server_id
				LEFT JOIN LATERAL (
					select EvnDirection_id
					from v_EvnPrescrDirection
					where EvnPrescr_id = EUC.EvnPrescr_id
                    limit 1
				) EPD ON true
				left join v_EvnDirection_all ED  on COALESCE(EUC.EvnDirection_id, EPD.EvnDirection_id) = ED.EvnDirection_id and ED.DirFailType_id is null
				left join v_EvnLabRequest EvnLabRequest  on EvnLabRequest.EvnDirection_id = ED.EvnDirection_id
				LEFT JOIN LATERAL (select * from v_EvnLabSample  where EvnLabRequest_id = EvnLabRequest.EvnLabRequest_id limit 1) as EvnLabSample on true
				left join v_MedService MS  on EvnLabRequest.MedService_id = MS.MedService_id
				left join v_Lpu ULpu  on COALESCE(MS.Lpu_id,EUC.Lpu_id) = ULpu.Lpu_id
				left join v_LpuSection ULpuSection  on coalesce(MS.LpuSection_id,EUC.LpuSection_uid,ED.LpuSection_did) = ULpuSection.LpuSection_id
				left join v_Lpu NaprLpu  on COALESCE(ED.Lpu_sid, ULpuSection.Lpu_id) = NaprLpu.Lpu_id
				left join v_MedPersonal MP  on MP.MedPersonal_id = COALESCE(EvnLabSample.MedPersonal_aid,EUC.MedPersonal_id) AND MP.Lpu_id = coalesce(MS.Lpu_id,EUC.Lpu_id)
				left join v_UslugaComplex UC  on UC.UslugaComplex_id = EUC.UslugaComplex_id
				left join v_Diag D  on ED.Diag_id = D.Diag_id

				left join v_EvnSection ES  on EUC.EvnUslugaCommon_pid = ES.EvnSection_id
				left join v_Diag D3  on D3.Diag_id = ES.Diag_id
                left join v_MedPersonal SecMedPersonal  on SecMedPersonal.MedPersonal_id = ES.MedPersonal_id

				left join v_EvnVizitPL EVPL  on EUC.EvnUslugaCommon_pid = EVPL.EvnVizitPL_id
				left join v_Diag D4  on D4.Diag_id = EVPL.Diag_id
                left join v_MedPersonal EVPLMedPersonal  on EVPLMedPersonal.MedPersonal_id = EVPL.MedPersonal_id

				left join v_LpuSection DLpuSection  on COALESCE(ES.LpuSection_id,EVPL.LpuSection_id,EUC.LpuSection_uid,ED.LpuSection_id) = DLpuSection.LpuSection_id
				left join v_Org DOrg  on COALESCE(EUC.Org_uid,ED.Lpu_id) = DOrg.Org_id
				left join v_MedPersonal DMedPersonal  on COALESCE(EUC.MedPersonal_sid,ED.MedPersonal_id) = DMedPersonal.MedPersonal_id AND coalesce(DLpuSection.Lpu_id,ED.Lpu_id) = DMedPersonal.Lpu_id
				left join v_Evn E  on E.Evn_id = EUC.EvnPrescr_id
				left join v_pmUserCache pmUC  on E.pmUser_updID = pmUC.PMUser_id
				left join v_MedPersonal CMedPersonal  on CMedPersonal.MedPersonal_id = pmUC.MedPersonal_id
				left join V_Morbus Mor  on EUC.Morbus_id = Mor.Morbus_id
				left join v_Diag D2  on D2.Diag_id = Mor.Diag_id

				LEFT JOIN LATERAL (
					select EvnXml_id
					from v_EvnXml
					where Evn_id = EUC.EvnUslugaCommon_id
		            order by EvnXml_insDT desc -- костыль, должен быть только один протокол
                    limit 1
				) doc ON true
				{$join_msf}
				{$joinPersonEncrypHIV}
			WHERE
				EUC.EvnUslugaCommon_id = :EvnUslugaCommon_id
            limit 1
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
			$accessAdd = " and COALESCE(ES.EvnSection_IsPaid, 1) = 1 ";
		}

		$filterAccessRights = getAccessRightsTestFilter('UC.UslugaComplex_id');
		$filterAccessRightsDenied = getAccessRightsTestFilter('UCMPp.UslugaComplex_id', false, true);
		$orf = isset($data['session']['medpersonal_id']) ? " or ED.MedPersonal_id = {$data['session']['medpersonal_id']}" : '';
		$filter .= !empty($filterAccessRights) ? " and (($filterAccessRights and UCp.UslugaComplex_id is null) $orf)" : '';

		$this->load->library('swEvnXml');
		$narcosis_protocol_type=swEvnXml::EVN_USLUGA_NARCOSIS_PROTOCOL_TYPE_ID;
		$oper_protocol_type=swEvnXml::EVN_USLUGA_PROTOCOL_TYPE_ID;
		$query = "
		select
				case when EU.Lpu_id = :Lpu_id
					{$accessAdd} " .
					((/*$data['session']['isMedStatUser'] == false && */isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0)
						? "and (EU.MedPersonal_id = " . $data['session']['medpersonal_id']."
        or exists(
            select WG.WorkGraph_id
							from v_WorkGraph WG
							inner join v_MedStaffFact WG_MSF on WG_MSF.MedStaffFact_id = WG.MedStaffFact_id
							where WG_MSF.MedPersonal_id = {$data['session']['medpersonal_id']}
							and (
        CAST(WG.WorkGraph_begDT as date) <= CAST(dbo.tzGetDate() as date)
								and CAST(WG.WorkGraph_endDT as date) >= CAST(dbo.tzGetDate() as date)
							)
						))" : "")
					. "
        and EU.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper')
					then 'edit' else 'view' end as \"accessType\",
				EU.EvnUsluga_id as \"EvnUsluga_id\",
				EU.EvnUsluga_pid as \"EvnUsluga_pid\",
				EU.EvnUsluga_rid as \"EvnUsluga_rid\",
				EU.EvnClass_id as \"EvnClass_id\",
				EU.EvnClass_Name as \"EvnClass_Name\",
				RTRIM(EU.EvnClass_SysNick) as \"EvnClass_SysNick\",
				to_char (EU.EvnUsluga_setDate, 'dd.mm.yyyy') as \"EvnUsluga_setDate\",
				COALESCE(to_char(EU.EvnUsluga_setTime,'HH24:mi'), '') as \"EvnUsluga_setTime\",
				COALESCE(Usluga.Usluga_Code, UC.UslugaComplex_Code) as \"Usluga_Code\",
				coalesce(ucms.UslugaComplex_Name, Usluga.Usluga_Name, UC.UslugaComplex_Name) as \"Usluga_Name\",
				ROUND(cast(EU.EvnUsluga_Kolvo as numeric), 2) as \"EvnUsluga_Kolvo\",
				EU.UslugaComplex_id as \"UslugaComplex_id\",
				docOper.EvnXml_id as \"EvnXml_id\",
				docNarcosis.EvnXml_id as \"EvnXmlNarcosis_id\"
			from v_EvnUsluga EU
				left join v_Usluga Usluga  on Usluga.Usluga_id = EU.Usluga_id
				left join v_UslugaComplex UC  on UC.UslugaComplex_id = EU.UslugaComplex_id
				left join v_EvnSection ES  on ES.EvnSection_id = EU.EvnUsluga_pid
				left join lateral (
					select
						EvnXml_id
					from
						v_EvnXml
					where
						Evn_id = EU.EvnUsluga_id
						and XmlType_id={$oper_protocol_type}
					order by
						EvnXml_insDT desc -- костыль, должен быть только один протокол
					limit 1
				) docOper on true
				left join lateral (
					select
						EvnXml_id
					from
						v_EvnXml
					where
						Evn_id = EU.EvnUsluga_id
						and XmlType_id={$narcosis_protocol_type}
					order by
						EvnXml_insDT desc -- костыль, для вывовода протокола анестезии
					limit 1
				) docNarcosis on true
				left join v_EvnLabRequest ELR  on ELR.EvnDirection_id = EU.EvnDirection_id
				left join v_UslugaComplexMedService UCMS on UCMS.MedService_id = ELR.MedService_id and UCMS.UslugaComplex_id = UC.UslugaComplex_id and UCMS.UslugaComplexMedService_pid is null
				left join v_EvnDirection_all ED  on ED.EvnDirection_id = EU.EvnDirection_id
				LEFT JOIN LATERAL (
            select
						UCMPp.UslugaComplex_id
					from
						v_UslugaComplexMedService UCMPp
					inner join v_EvnLabRequestUslugaComplex ELRUC on UCMPp.UslugaComplex_id = ELRUC.UslugaComplex_id and ELRUC.EvnLabRequest_id = ELR.EvnLabRequest_id
					inner join v_EvnLabSample ELS on ELS.EvnLabSample_id = ELRUC.EvnLabSample_id and ELS.LabSampleStatus_id IN(4,6)
					where
						UCMS.UslugaComplexMedService_id = UCMPp.UslugaComplexMedService_pid
						".((!empty($filterAccessRightsDenied))?"and ".$filterAccessRightsDenied:'')."
                    limit 1
				) as UCp ON true
			where (1 = 1)
            {$filter}
            and (EU.Lpu_id = :Lpu_id or " . (isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0 ? 1 : 0) . " = 1)
				and (
            EU.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper')--, 'EvnUslugaStom'
        OR (EU.EvnClass_SysNick ilike 'EvnUslugaPar' and EU.EvnUsluga_setDate is not null)
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
				and COALESCE(EU.EvnUsluga_IsVizitCode, 1) = 1
			";
		}

		$accessAdd = "";
		if ( !($this->getRegionNick() == 'perm' || ($this->getRegionNick() == 'ufa' && isSuperadmin())) ) {
			$accessAdd = "
				and COALESCE(EV.EvnVizit_IsPaid, 1) = 1
				and COALESCE(ES.EvnSection_IsPaid, 1) = 1
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
							select WG.WorkGraph_id
							from v_WorkGraph WG
							inner join v_MedStaffFact WG_MSF on WG_MSF.MedStaffFact_id = WG.MedStaffFact_id
							where WG_MSF.MedPersonal_id = {$data['session']['medpersonal_id']}
							and (
								CAST(WG.WorkGraph_begDT as date) <= CAST(dbo.tzGetDate() as date)
								and CAST(WG.WorkGraph_endDT as date) >= CAST(dbo.tzGetDate() as date)
							)
						))" : "")
					. "
					and EU.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper')
					then 'edit' else 'view' end as \"accessType\",
				EU.EvnUsluga_id as \"EvnUsluga_id\"
				,EU.EvnUsluga_pid as \"EvnUsluga_pid\"
				,EU.EvnUsluga_rid as \"EvnUsluga_rid\"
				,EU.EvnClass_id as \"EvnClass_id\"
				,EU.EvnClass_Name as \"EvnClass_Name\"
				,RTRIM(EU.EvnClass_SysNick) as \"EvnClass_SysNick\"
				,to_char (EU.EvnUsluga_setDate, 'dd.mm.yyyy') as \"EvnUsluga_setDate\"
				,COALESCE(to_char(EU.EvnUsluga_setTime,'HH24:mi'), '') as \"EvnUsluga_setTime\"
				,UC.UslugaComplex_id as \"UslugaComplex_id\"
				,COALESCE(Usluga.Usluga_Code, UC.UslugaComplex_Code) as \"Usluga_Code\"
				,coalesce(ucms.UslugaComplex_Name, UC.UslugaComplex_Name, Usluga.Usluga_Name) as \"Usluga_Name\"
				,ROUND(cast(EU.EvnUsluga_Kolvo as numeric), 2) as \"EvnUsluga_Kolvo\"
				,doc.EvnXml_id as \"EvnXml_id\"
				,docNarcosis.EvnXml_id as \"EvnXmlNarcosis_id\"
			from v_EvnUsluga EU
				left join v_Usluga Usluga  on Usluga.Usluga_id = EU.Usluga_id
				left join v_UslugaComplex UC  on UC.UslugaComplex_id = EU.UslugaComplex_id
				left join v_EvnVizit EV  on EV.EvnVizit_id = EU.EvnUsluga_pid
				left join v_EvnSection ES  on ES.EvnSection_id = EU.EvnUsluga_pid
				{$from_clause}
				left join v_EvnLabRequest ELR  on ELR.EvnDirection_id = EU.EvnDirection_id
				left join v_UslugaComplexMedService UCMS on UCMS.MedService_id = ELR.MedService_id and UCMS.UslugaComplex_id = UC.UslugaComplex_id and UCMS.UslugaComplexMedService_pid is null
				left join v_EvnDirection_all ED  on ED.EvnDirection_id = EU.EvnDirection_id
				left join lateral (
					select
						EvnXml_id
					from
						v_EvnXml
					where
						Evn_id = EU.EvnUsluga_id
						and XmlType_id={$oper_protocol_type}
					order by
						EvnXml_insDT desc -- костыль, должен быть только один протокол
					limit 1
				) doc on true
				left join lateral (
					select
						EvnXml_id
					from
						v_EvnXml
					where
						Evn_id = EU.EvnUsluga_id
						and XmlType_id={$narcosis_protocol_type}
					order by
						EvnXml_insDT desc -- костыль, для вывовода протокола анестезии
					limit 1
				) docNarcosis on true
				left join lateral (
					select
						UCMPp.UslugaComplex_id
					from
						v_UslugaComplexMedService UCMPp
					inner join v_EvnLabRequestUslugaComplex ELRUC on UCMPp.UslugaComplex_id = ELRUC.UslugaComplex_id and ELRUC.EvnLabRequest_id = ELR.EvnLabRequest_id
					inner join v_EvnLabSample ELS on ELS.EvnLabSample_id = ELRUC.EvnLabSample_id and ELS.LabSampleStatus_id IN(4,6)
					where
						UCMS.UslugaComplexMedService_id = UCMPp.UslugaComplexMedService_pid
						".((!empty($filterAccessRightsDenied))?"and ".$filterAccessRightsDenied:'')."
					limit 1
				) as UCp on true
            where
				(1 = 1)
				{$where_clause}
				and (EU.Lpu_id = :Lpu_id or " . (isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0 ? 1 : 0) . " = 1)
				and (
					EU.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaStom' /*, 'EvnUslugaOnkoChem', 'EvnUslugaOnkoBeam', 'EvnUslugaOnkoGormun', 'EvnUslugaOnkoSurg'*/ ) OR (EU.EvnClass_SysNick ilike 'EvnUslugaPar' and EU.EvnUsluga_setDate is not null)
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
				UCA.UslugaComplex_id as \"UslugaComplex_id\",
				UCAT.UslugaComplexAttributeType_SysNick as \"UslugaComplexAttributeType_SysNick\"
			from v_UslugaComplexAttribute UCA
			inner join v_UslugaComplexAttributeType UCAT on UCAT.UslugaComplexAttributeType_id = UCA.UslugaComplexAttributeType_id
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
		$uc_filter = 'and COALESCE(UslugaComplex_id, 0) = COALESCE(cast(:UslugaComplex_id as bigint), 0)';
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
			$uc_filter = "and COALESCE(UslugaComplex_id, 0) in ({$uc_list})";
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
							v_EvnSection es
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
					select count(EvnUslugaCommon_id) as \"Usluga_Count\"
					from v_EvnUslugaCommon
					where Lpu_id = :Lpu_id
						and Person_id = :Person_id
						and ((MedPersonal_id = :MedPersonal_id and LpuSection_uid = :LpuSection_uid) or (Lpu_uid = :Lpu_uid))
						and COALESCE(Usluga_id, 0) = COALESCE(cast(:Usluga_id as bigint), 0)
						{$uc_filter}
						and PayType_id = :PayType_id
						and EvnUslugaCommon_setDT = cast(:EvnUslugaCommon_setDate as timestamp)
						and EvnUslugaCommon_id <> COALESCE(cast(:EvnUslugaCommon_id as bigint), 0)
				";
				$queryParams['EvnUslugaCommon_id'] =  $data['EvnUslugaCommon_id'];
				$queryParams['EvnUslugaCommon_setDate'] =  $data['EvnUslugaCommon_setDate'];
			break;

			case 'oper':
				// если движение оплачено, то данная проверка не нужна refs #74335
				$resp = $this->queryResult("
					select
						ES.EvnSection_id as \"EvnSection_id\"
					from
						v_EvnSection es
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
					select count(EvnUslugaOper_id) as \"Usluga_Count\"
					from v_EvnUslugaOper
					where Lpu_id = :Lpu_id
						and Person_id = :Person_id
						and ((MedPersonal_id = :MedPersonal_id and LpuSection_uid = :LpuSection_uid) or (Lpu_uid = :Lpu_uid))
						and COALESCE(Usluga_id, 0) = COALESCE(cast(:Usluga_id as bigint), 0)
						{$uc_filter}
						and PayType_id = :PayType_id
						and EvnUslugaOper_setDT = cast(:EvnUslugaOper_setDate as timestamp)
						and EvnUslugaOper_id <> COALESCE(cast(:EvnUslugaOper_id as bigint), 0)
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
					select count(EvnUslugaStom_id) as \"Usluga_Count\"
					from v_EvnUslugaStom
					where Lpu_id = :Lpu_id
						and Person_id = :Person_id
						and MedPersonal_id = :MedPersonal_id
						and LpuSection_uid = :LpuSection_uid
						and COALESCE(Usluga_id, 0) = COALESCE(cast(:Usluga_id as bigint), 0)
						{$uc_filter}
						and PayType_id = :PayType_id
						and EvnUslugaStom_setDate = cast(:EvnUslugaStom_setDate as timestamp)
						and EvnUslugaStom_id <> COALESCE(CAST(:EvnUslugaStom_id AS bigint), 0)
						and EvnUslugaStom_pid = COALESCE(CAST(:EvnUslugaStom_pid AS bigint), 0)
						{$edpfilter}
				";
				$queryParams['EvnUslugaStom_id'] =  $data['EvnUslugaStom_id'];
				$queryParams['EvnUslugaStom_setDate'] =  $data['EvnUslugaStom_setDate'];
				$queryParams['EvnUslugaStom_pid'] = (!empty($data['EvnUslugaStom_pid']) ? intval($data['EvnUslugaStom_pid']) : NULL);
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
			select
				es.EvnSection_id as \"EvnSection_id\",
				eus1.EvnUslugaSignRao_id as \"EvnUslugaSignRao_id\",
				mes1.MesUslugaSignRao_id as \"MesUslugaSignRao_id\",
				eus2.EvnUsluga_id as \"EvnUsluga_id\"
			from
				v_EvnSection es
				LEFT JOIN LATERAL (
					select
						eu.EvnUsluga_id as EvnUslugaSignRao_id
					from
						v_EvnUsluga eu
						inner join r66.v_UslugaComplexPartitionLink ucpl on ucpl.UslugaComplex_id = eu.UslugaComplex_id
					where
						eu.EvnUsluga_pid = es.EvnSection_id
						and ucpl.UslugaComplexPartitionLink_Signrao = 1
						and eu.EvnUsluga_id <> :id
                    limit 1
				) eus1 ON true
				LEFT JOIN LATERAL (
					select
						mu.MesUsluga_id as MesUslugaSignRao_id
					from
						v_MesUsluga mu
						inner join r66.v_UslugaComplexPartitionLink ucpl on ucpl.UslugaComplex_id = mu.UslugaComplex_id
					where
						mu.Mes_id = es.Mes_sid
						and ucpl.UslugaComplexPartitionLink_Signrao = 1
                    limit 1
				) mes1 ON true
				LEFT JOIN LATERAL (
					select
						eu.EvnUsluga_id
					from
						v_EvnUsluga eu
						inner join r66.v_UslugaComplexPartitionLink ucpl on ucpl.UslugaComplex_id = eu.UslugaComplex_id
						inner join r66.v_UslugaComplexPartition ucp on ucp.UslugaComplexPartition_id = ucpl.UslugaComplexPartition_id
					where
						eu.EvnUsluga_pid = es.EvnSection_id
						and ucp.UslugaComplexPartition_Code = '105'
						and eu.EvnUsluga_id <> :id
                    limit 1
				) eus2 ON true
			where
				es.EvnSection_id = :EvnUsluga_pid
			limit 1
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
			select
				mouc.MesOldUslugaComplex_id as \"MesOldUslugaComplex_id\",
				INES.EvnUsluga_id as \"EvnUsluga_id\"
			from
				v_EvnSection es
				inner join v_MesOldUslugaComplex mouc on mouc.Mes_id = es.Mes_sid
				LEFT JOIN LATERAL (
					select
						euc.EvnUsluga_id
					from
						v_EvnUsluga euc
					where
						UslugaComplex_id in (
							select
								UslugaComplex_id
							from
								v_MesOldUslugaComplex
							where
								Mes_id = mouc.Mes_id
						)
						and euc.EvnUsluga_pid = es.EvnSection_id
					order by
						case when euc.EvnUsluga_id <> :id then 1 else 2 end ASC
                    limit 1
				) INES ON true
			where
				es.EvnSection_id = :EvnUsluga_pid
				and COALESCE(es.EvnSection_IsPriem, 1) = 1
            limit 1
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
			e.EvnClass_SysNick as \"EvnClass_SysNick\",
			eu.EvnUsluga_pid as \"EvnUsluga_pid\",
			eu.EvnPrescr_id as \"EvnPrescr_id\",
			eu.EvnDirection_id as \"EvnDirection_id\",
			(SELECT
					string_agg(cast(tm.PersonToothCard_id as varchar), ',')
				FROM
					v_PersonToothCard tm
				WHERE
					tm.EvnUsluga_id = eu.EvnUsluga_id
			) as \"PersonToothCard_id_List\",
			coalesce(EPL.EvnPL_IsPaid, EV.EvnVizit_IsPaid, ES.EvnSection_IsPaid, 1) as \"EvnUsluga_IsPaid\",
			coalesce(EPL.EvnPL_IndexRep, EV.EvnVizit_IndexRep, ES.EvnSection_IndexRep, 0) as \"EvnUsluga_IndexRep\",
			coalesce(EPL.EvnPL_IndexRepInReg, EV.EvnVizit_IndexRepInReg, ES.EvnSection_IndexRepInReg, 1) as \"EvnUsluga_IndexRepInReg\"
			FROM v_EvnUsluga_all eu
			left join v_evn e on e.Evn_id = eu.EvnUsluga_pid
			left join v_EvnVizit EV on EV.EvnVizit_id = e.Evn_id
			left join v_EvnPL EPL on EPL.EvnPL_id = e.Evn_pid
            left join v_EvnSection ES on ES.EvnSection_id = e.Evn_id
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
					eps.EvnPLStom_id as \"EvnPLStom_id\"
				from
					v_EvnUslugaStom eus
					inner join v_EvnPLStom eps on eps.EvnPLStom_id = eus.EvnUslugaStom_rid
				where
					eus.EvnUslugaStom_id = :EvnUslugaStom_id
					and eps.EvnPLStom_IsFinish = 2
					and not exists (
						select
							eus2.EvnUslugaStom_id
						from
							v_EvnUslugaStom eus2
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
				select lsp.LpuSectionProfile_Code as \"LpuSectionProfile_Code\"
				from v_EvnSection es
				inner join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = es.LpuSectionProfile_id
				where es.EvnSection_id = :Evn_id",
				array('Evn_id' => $data['EvnUsluga_pid'])
			);
			if ($LpuSectionProfile_Code != 5) {
				$usluga_cnt = $this->getFirstResultFromQuery("
					select COUNT(*) AS \"cnt\" from v_EvnUsluga where EvnUsluga_pid = :Evn_id and EvnUsluga_id != :id
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
			select
				ex.EvnXml_id as \"EvnXml_id\"
			from
				v_EvnXml ex
			where
				ex.Evn_id = :EvnUsluga_id
				and ex.EvnXml_IsSigned = 2
		limit 1
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
				select
					es.EvnStatus_SysNick as \"EvnStatus_SysNick\",
					ed.EvnDirection_id as \"EvnDirection_id\",
					TTMS.TimetableMedService_begTime as \"TimetableMedService_begTime\"
				from
					v_EvnDirection_all ed
					left join v_TimetableMedService_lite TTMS on ED.EvnDirection_id = TTMS.EvnDirection_id
					left join v_EvnStatus es on es.EvnStatus_id = ed.EvnStatus_id
				where
					ed.EvnDirection_id = :EvnDirection_id
                limit 1
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
    				SELECT
                        Error_Code as \"Error_Code\",
                        Error_Message as \"Error_Msg\"
                    FROM dbo.p_" . $data['class'] . "_del(
                                " . $data['class'] . "_id := :id,
					pmUser_id := :pmUser_id);
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
				UCL.UslugaComplexList_id as \"UslugaComplexList_id\",
				U.Usluga_id as \"Usluga_id\",
				UC.UslugaClass_Code as \"UslugaClass_Code\"
			from
				UslugaComplexList UCL
				inner join Usluga U  on U.Usluga_id = UCL.Usluga_id
				inner join UslugaComplex UCom  on UCom.UslugaComplex_id = UCL.UslugaComplex_id
				inner join UslugaClass UC  on UC.UslugaClass_id = UCL.UslugaClass_id
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
							$accessAdd .= " and COALESCE(EV.EvnVizit_IsPaid, 1) = 1 and COALESCE(ES.EvnSection_IsPaid, 1) = 1 ";
						}
						$accessAdd .= '
							and case
								when EUC.Lpu_id = :Lpu_id then 1
								' . (count($data['session']['linkedLpuIdList']) > 1 ? 'when EUC.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and COALESCE(EUC.EvnUslugaCommon_IsTransit, 1) = 2 then 1' : '') . '
								else 0
							end = 1
						';
						$accessType = "case when ({$accessAdd} " . ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 ? "and (COALESCE(EV.MedPersonal_id, ES.MedPersonal_id) in (".implode(',',$med_personal_list).")
							or exists(
										select  WG.WorkGraph_id
											from v_WorkGraph WG
											inner join v_MedStaffFact WG_MSF on WG_MSF.MedStaffFact_id = WG.MedStaffFact_id
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
							then 'edit' else 'view' end as \"accessType\"";
        				$query = "
        					SELECT
        						 {$accessType}
        						,EUC.EvnUslugaCommon_id as \"EvnUslugaCommon_id\"
        						,EUC.EvnUslugaCommon_pid as \"EvnUslugaCommon_pid\"
        						,to_char (EPS.EvnPS_setDate, 'dd.mm.yyyy') || ' / ' || LS.LpuSection_Name || ' / ' || MP.Person_Fio as \"EvnUslugaCommon_pid_Name\"
        						,EUC.EvnUslugaCommon_pid as \"EvnUslugaCommon_rid\"
        						,EUC.Person_id as \"Person_id\"
        						,EUC.PersonEvn_id as \"PersonEvn_id\"
        						,EUC.Server_id as \"Server_id\"
        						,to_char (EUC.EvnUslugaCommon_setDate, 'dd.mm.yyyy') as \"EvnUslugaCommon_setDate\"
        						,COALESCE(to_char(EUC.EvnUslugaCommon_setTime, 'HH24:mi'), '') as \"EvnUslugaCommon_setTime\"
        						,to_char (EUC.EvnUslugaCommon_disDate, 'dd.mm.yyyy') as \"EvnUslugaCommon_disDate\"
        						,COALESCE(to_char(EUC.EvnUslugaCommon_disTime, 'HH24:mi'), '') as \"EvnUslugaCommon_disTime\"
        						,EUC.UslugaPlace_id as \"UslugaPlace_id\"
        						,EUC.Lpu_uid as \"Lpu_uid\"
        						,EUC.Org_uid as \"Org_uid\"
        						,org.Org_Name as \"Org_Name\"
        						,EUC.LpuSection_uid as \"LpuSection_uid\"
        						,EUC.MedPersonal_id as \"MedPersonal_id\"
        						,EUC.MedStaffFact_id as \"MedStaffFact_id\"
        						,EUC.Usluga_id as \"Usluga_id\"
        						,EUC.UslugaComplex_id as \"UslugaComplex_id\"
        						,EUC.PayType_id as \"PayType_id\"
        						,ROUND(COALESCE(cast(EUC.EvnUslugaCommon_Kolvo as numeric), 0), 2) as \"EvnUslugaCommon_Kolvo\"
								,ROUND(COALESCE(cast(EUC.EvnUslugaCommon_Price as numeric), 0), 2) as \"UslugaComplexTariff_UED\"
								,ROUND(COALESCE(cast(EUC.EvnUslugaCommon_Summa as numeric), 0), 2) as \"EvnUslugaCommon_Summa\"
        						,EUC.EvnUslugaCommon_CoeffTariff as \"EvnUslugaCommon_CoeffTariff\"
        						,EUC.EvnUslugaCommon_IsModern as \"EvnUslugaCommon_IsModern\"
        						,EUC.EvnUslugaCommon_IsMinusUsluga as \"EvnUslugaCommon_IsMinusUsluga\"
        						,EUC.MesOperType_id as \"MesOperType_id\"
        						,EUC.UslugaComplexTariff_id as \"UslugaComplexTariff_id\"
        						,EUC.DiagSetClass_id as \"DiagSetClass_id\"
        						,EUC.Diag_id as \"Diag_id\"
        						,EUC.EvnPrescr_id as \"EvnPrescr_id\"
        						,EUC.EvnPrescrTimetable_id as \"EvnPrescrTimetable_id\"
        						,EUC.MedSpecOms_id as \"MedSpecOms_id\"
        						,EUC.LpuSectionProfile_id as \"LpuSectionProfile_id\"
        						,EUC.EvnDirection_id as \"EvnDirection_id\"
        						,EUC.UslugaExecutionType_id as \"UslugaExecutionType_id\"
        						,EUC.UslugaExecutionReason_id as \"UslugaExecutionReason_id\"
        						,UC.UslugaCategory_id as \"UslugaCategory_id\"
								,to_char (EUC.EvnUslugaCommon_setDT, 'YYYY-MM-DD HH24:MI:SS') as \"EvnUslugaCommon_setDT\"
								,to_char (EUC.EvnUslugaCommon_disDT, 'YYYY-MM-DD HH24:MI:SS') as \"EvnUslugaCommon_disDT\"
        					FROM
        						v_EvnUslugaCommon EUC
        						left join v_EvnVizit EV  on EV.EvnVizit_id = EUC.EvnUslugaCommon_pid
        						left join v_EvnSection ES  on ES.EvnSection_id = EUC.EvnUslugaCommon_pid
								left join v_UslugaComplexTariff UCT  on UCT.UslugaComplexTariff_id = EUC.UslugaComplexTariff_id
								left join v_EvnPS EPS  on EPS.EvnPS_id = EUC.EvnUslugaCommon_pid
								left join v_LpuSection LS  on LS.LpuSection_id = EPS.LpuSection_pid
								left join v_MedPersonal MP  on MP.MedPersonal_id = EPS.MedPersonal_pid
								left join v_UslugaComplex UC  on UC.UslugaComplex_id = EUC.UslugaComplex_id
								left join v_Org org on org.Org_id = EUC.Org_uid
        					WHERE
        						EUC.EvnUslugaCommon_id = :id
			                LIMIT 1
        				";
        			break;

        			case 'EvnUslugaOper':
						$accessAdd = "1=1";
						if ( !($this->getRegionNick() == 'perm' || ($this->getRegionNick() == 'ufa' && isSuperadmin())) ) {
							$accessAdd .= " and COALESCE(EV.EvnVizit_IsPaid, 1) = 1 and COALESCE(ES.EvnSection_IsPaid, 1) = 1 ";
						}

						$accessAdd .= '
							and case
								when EUO.Lpu_id = :Lpu_id then 1
								' . (count($data['session']['linkedLpuIdList']) > 1 ? 'when EUO.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and COALESCE(EUO.EvnUslugaOper_IsTransit, 1) = 2 then 1' : '') . '
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
										select WG.WorkGraph_id
											from v_WorkGraph WG
											inner join v_MedStaffFact WG_MSF on WG_MSF.MedStaffFact_id = WG.MedStaffFact_id
											where (
												CAST(WG.WorkGraph_begDT as date) <= CAST(dbo.tzGetDate() as date)
												and CAST(WG.WorkGraph_endDT as date) >= CAST(dbo.tzGetDate() as date)
											)
											and WG_MSF.MedPersonal_id in (".implode(',',$med_personal_list).")
									)
							)" : "") . ")
							then 'edit' else 'view' end as \"accessType\"";
        				$query = "
        					SELECT
        						{$accessType},
        						EUO.EvnDirection_id as \"EvnDirection_id\",
        						EUO.EvnUslugaOper_id as \"EvnUslugaOper_id\",
								EUO.EvnUslugaOper_IsVMT as \"EvnUslugaOper_IsVMT\",
								EUO.EvnUslugaOper_IsMicrSurg as \"EvnUslugaOper_IsMicrSurg\",
								EUO.EvnUslugaOper_IsOpenHeart as \"EvnUslugaOper_IsOpenHeart\",
								EUO.EvnUslugaOper_IsArtCirc as \"EvnUslugaOper_IsArtCirc\",
        						EUO.EvnUslugaOper_pid as \"EvnUslugaOper_pid\",
        						to_char (EPS.EvnPS_setDate, 'dd.mm.yyyy') || ' / ' || LS.LpuSection_Name || ' / ' || MP.Person_Fio as \"EvnUslugaCommon_pid_Name\",
        						EUO.EvnUslugaOper_pid as \"EvnUslugaOper_rid\",
        						EUO.Person_id as \"Person_id\",
        						EUO.PersonEvn_id as \"PersonEvn_id\",
        						EUO.Server_id as \"Server_id\",
        						to_char (EUO.EvnUslugaOper_setDate, 'dd.mm.yyyy') as \"EvnUslugaOper_setDate\",
        						COALESCE(to_char(EUO.EvnUslugaOper_setTime, 'HH24:mi'), '') as \"EvnUslugaOper_setTime\",
        						to_char (EUO.EvnUslugaOper_disDate, 'dd.mm.yyyy') as \"EvnUslugaOper_disDate\",
        						COALESCE(to_char(EUO.EvnUslugaOper_disTime, 'HH24:mi'), '') as \"EvnUslugaOper_disTime\",
        						EUO.UslugaPlace_id as \"UslugaPlace_id\",
        						EUO.Lpu_uid as \"Lpu_uid\",
        						EUO.Org_uid as \"Org_uid\",
        						EUO.LpuSection_uid as \"LpuSection_uid\",
        						EUO.MedPersonal_id as \"MedPersonal_id\",
        						coalesce(EUO.MedStaffFact_id, MSF.MedStaffFact_id) as \"MedStaffFact_id\",
        						EUO.Morbus_id as \"Morbus_id\",
        						EUO.Usluga_id as \"Usluga_id\",
        						UC.UslugaCategory_id as \"UslugaCategory_id\",
        						EUO.UslugaComplex_id as \"UslugaComplex_id\",
        						EUO.PayType_id as \"PayType_id\",
        						EUO.OperType_id as \"OperType_id\",
        						EUO.OperDiff_id as \"OperDiff_id\",
        						EUO.TreatmentConditionsType_id as \"TreatmentConditionsType_id\",
        						COALESCE(EUO.EvnUslugaOper_IsEndoskop, 1) as \"EvnUslugaOper_IsEndoskop\",
        						COALESCE(EUO.EvnUslugaOper_IsLazer, 1) as \"EvnUslugaOper_IsLazer\",
        						COALESCE(EUO.EvnUslugaOper_IsKriogen, 1) as \"EvnUslugaOper_IsKriogen\",
        						COALESCE(EUO.EvnUslugaOper_IsRadGraf, 1) as \"EvnUslugaOper_IsRadGraf\",
        						ROUND(COALESCE(cast(EUO.EvnUslugaOper_Kolvo as numeric), 0), 2) as \"EvnUslugaOper_Kolvo\",
        						EUO.EvnUslugaOper_CoeffTariff as \"EvnUslugaOper_CoeffTariff\",
        						EUO.EvnUslugaOper_IsModern as \"EvnUslugaOper_IsModern\",
        						EUO.MesOperType_id as \"MesOperType_id\",
								EUO.UslugaComplexTariff_id as \"UslugaComplexTariff_id\",
								EUO.UslugaExecutionType_id as \"UslugaExecutionType_id\",
        						EUO.UslugaExecutionReason_id as \"UslugaExecutionReason_id\",
								EUO.DiagSetClass_id as \"DiagSetClass_id\",
								EUO.Diag_id as \"Diag_id\"
        						,EUO.EvnPrescr_id as \"EvnPrescr_id\"
        						,EUO.EvnPrescrTimetable_id as \"EvnPrescrTimetable_id\"
        						,EUO.MedSpecOms_id as \"MedSpecOms_id\"
        						,EUO.LpuSectionProfile_id as \"LpuSectionProfile_id\"
								,to_char (EUO.EvnUslugaOper_BallonBegDT, 'YYYY-MM-DD HH24:MI:SS') as \"EvnUslugaOper_BallonBegDate\"
								,to_char (EUO.EvnUslugaOper_BallonBegDT, 'HH24:MI:SS') as \"EvnUslugaOper_BallonBegTime\"
								,to_char (EUO.EvnUslugaOper_CKVEndDT, 'YYYY-MM-DD HH24:MI:SS') as \"EvnUslugaOper_CKVEndDate\"
								,to_char (EUO.EvnUslugaOper_CKVEndDT, 'HH24:MI:SS') as \"EvnUslugaOper_CKVEndTime\"
								,EUO.EvnUslugaOper_IsOperationDeath as \"EvnUslugaOper_IsOperationDeath\"
							  	--,case when EUO.EvnUslugaOper_IsOperationDeath = 2 then 1 else 0 end as EvnUslugaOper_IsOperationDeath
								,e.EvnClass_SysNick as \"parentEvnClass_SysNick\"
								,to_char (EUO.EvnUslugaOper_setDT, 'YYYY-MM-DD HH24:MI:SS') as \"EvnUslugaOper_setDT\"
								,to_char (EUO.EvnUslugaOper_disDT, 'YYYY-MM-DD HH24:MI:SS') as \"EvnUslugaOper_disDT\"
        					FROM
        						v_EvnUslugaOper EUO
        						left join v_Evn e  on e.Evn_id = EUO.EvnUslugaOper_pid
        						left join v_EvnVizit EV  on EV.EvnVizit_id = EUO.EvnUslugaOper_pid
        						left join v_EvnSection ES  on ES.EvnSection_id = EUO.EvnUslugaOper_pid
								left join v_EvnPS EPS  on EPS.EvnPS_id = EUO.EvnUslugaOper_pid
								left join v_LpuSection LS  on LS.LpuSection_id = EPS.LpuSection_pid
								left join v_MedPersonal MP  on MP.MedPersonal_id = EPS.MedPersonal_pid
								left join v_EvnDirection_all ed  on ed.EvnDirection_id = EUO.EvnDirection_id
								left join v_UslugaComplex UC  on UC.UslugaComplex_id = EUO.UslugaComplex_id
								LEFT JOIN LATERAL (
									select MedStaffFact_id
									from v_MedStaffFact
									where MedPersonal_id = EUO.MedPersonal_id and LpuSection_id = EUO.LpuSection_uid
                                    limit 1
								) MSF ON true
        					WHERE EUO.EvnUslugaOper_id = :id
                            LIMIT 1
        				";
        			break;

        			case 'EvnUslugaStom':
						$accessAdd = "1=1";
						if ( !($this->getRegionNick() == 'perm' || ($this->getRegionNick() == 'ufa' && isSuperadmin())) ) {
							$accessAdd .= " and COALESCE(EV.EvnVizit_IsPaid, 1) = 1 and COALESCE(ES.EvnSection_IsPaid, 1) = 1 ";
						}

						$accessAdd .= '
							and case
								when EUS.Lpu_id = :Lpu_id then 1
								' . (count($data['session']['linkedLpuIdList']) > 1 ? 'when EUS.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and COALESCE(EUS.EvnUslugaStom_IsTransit, 1) = 2 then 1' : '') . '
								else 0
							end = 1
						';

        				$query = "
        					SELECT
        						case when {$accessAdd} " . ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 ? "and EUS.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . " then 'edit' else 'view' end as \"accessType\",
        						EUS.EvnUslugaStom_id as \"EvnUslugaStom_id\",
        						EUS.EvnUslugaStom_rid as \"EvnUslugaStom_rid\",
        						EUS.EvnUslugaStom_pid as \"EvnUslugaStom_pid\",
        						EUS.EvnDiagPLStom_id as \"EvnDiagPLStom_id\",
        						COALESCE(EUS.EvnUslugaStom_IsAllMorbus, 1) as \"EvnUslugaStom_IsAllMorbus\",
        						COALESCE(EUS.EvnUslugaStom_IsMes, 1) as \"EvnUslugaStom_IsMes\",
        						to_char (EPS.EvnPS_setDate, 'dd.mm.yyyy') || ' / ' || LS.LpuSection_Name || ' / ' || MP.Person_Fio as \"EvnUslugaCommon_pid_Name\",
        						EUS.Person_id as \"Person_id\",
        						EUS.PersonEvn_id as \"PersonEvn_id\",
        						EUS.Server_id as \"Server_id\",
        						to_char (EUS.EvnUslugaStom_setDate, 'dd.mm.yyyy') as \"EvnUslugaStom_setDate\",
        						COALESCE(to_char(EUS.EvnUslugaStom_setTime, 'HH24:mi'), '') as \"EvnUslugaStom_setTime\",
        						to_char (EUS.EvnUslugaStom_disDate, 'dd.mm.yyyy') as \"EvnUslugaStom_disDate\",
        						COALESCE(to_char(EUS.EvnUslugaStom_disTime, 'HH24:mi'), '') as \"EvnUslugaStom_disTime\",
        						EUS.LpuSection_uid as \"LpuSection_uid\",
        						EUS.LpuSectionProfile_id as \"LpuSectionProfile_id\",
        						EUS.MedStaffFact_id as \"MedStaffFact_id\",
        						EUS.MedPersonal_id as \"MedPersonal_id\",
        						EUS.MedPersonal_sid as \"MedPersonal_sid\",
        						EUS.Usluga_id as \"Usluga_id\",
        						EUS.UslugaComplex_id as \"UslugaComplex_id\",
        						EUS.LpuDispContract_id as \"LpuDispContract_id\",
        						UC.UslugaCategory_id as \"UslugaCategory_id\",
        						EUS.PayType_id as \"PayType_id\",
								ROUND(COALESCE(EUS.EvnUslugaStom_UED, 0), 2) as \"EvnUslugaStom_UED\",
								ROUND(COALESCE(EUS.EvnUslugaStom_UEM, 0), 2) as \"EvnUslugaStom_UEM\",
        						ROUND(COALESCE(cast(EUS.EvnUslugaStom_Kolvo as numeric), 0), 2) as \"EvnUslugaStom_Kolvo\",
        						ROUND(COALESCE(EUS.EvnUslugaStom_Summa, 0), 2) as \"EvnUslugaStom_Summa\",
								EUS.UslugaComplexTariff_id as \"UslugaComplexTariff_id\"
        						,EUS.EvnPrescr_id as \"EvnPrescr_id\"
        						,EUS.EvnPrescrTimetable_id as \"EvnPrescrTimetable_id\"
        						,EUS.BlackCariesClass_id as \"BlackCariesClass_id\"
        					FROM
        						v_EvnUslugaStom EUS
        						left join v_EvnVizit EV  on EV.EvnVizit_id = EUS.EvnUslugaStom_pid
        						left join v_EvnSection ES  on ES.EvnSection_id = EUS.EvnUslugaStom_pid
								left join v_UslugaComplexTariff UCT  on UCT.UslugaComplexTariff_id = EUS.UslugaComplexTariff_id
								left join v_EvnPS EPS  on EPS.EvnPS_id = EUS.EvnUslugaStom_pid
								left join v_LpuSection LS  on LS.LpuSection_id = EPS.LpuSection_pid
								left join v_MedPersonal MP  on MP.MedPersonal_id = EPS.MedPersonal_pid
								left join v_UslugaComplex UC  on UC.UslugaComplex_id = EUS.UslugaComplex_id
        					WHERE EUS.EvnUslugaStom_id = :id
                            LIMIT 1
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
								v_EvnSection es
								inner join v_EvnSection es2 on es2.EvnSection_pid = es.EvnSection_pid
							";
							$where = "es.EvnSection_id = :EvnUslugaOper_pid";
							if (!empty($resp[0]['parentEvnClass_SysNick']) && $resp[0]['parentEvnClass_SysNick'] == 'EvnPS') {
								$from = "
									v_EvnPS eps
									inner join v_EvnSection es2 on es2.EvnSection_pid = eps.EvnPS_id
								";
								$where = "eps.EvnPS_id = :EvnUslugaOper_pid";
							}
							$resp[0]['parentEvnComboData'] = $this->queryResult("
								select
									es2.EvnSection_id as \"Evn_id\",
									COALESCE(to_char(cast(es2.EvnSection_setDT as timestamp),'dd.mm.yyyy'),'') ||' / ' || coalesce(LpuSection.LpuSection_Name,'') || ' / ' || coalesce(msf.Person_FIO,'') as \"Evn_Name\",
									to_char (es2.EvnSection_setDT, 'dd.mm.yyyy') as \"Evn_setDate\",
									to_char (es2.EvnSection_disDT, 'dd.mm.yyyy') as \"Evn_disDate\",
									es2.EvnSection_IsPriem as \"IsPriem\",
									es2.Diag_id as \"Diag_id\"
								from
									{$from}
									left join v_LpuSection LpuSection on LpuSection.LpuSection_id = es2.LpuSection_id
									left join v_MedStaffFact Msf on Msf.MedStaffFact_id = es2.MedStaffFact_id
								where
									{$where}
							", array(
								'EvnUslugaOper_pid' => $resp[0]['EvnUslugaOper_pid']
							));

							$this->load->model('EvnPS_model');
							if (!in_array(getRegionNick(), $this->EvnPS_model->getListRegionNickWithEvnSectionPriem())) {
								// приёмное движение - КВС
								$from = "
									v_EvnSection es
									inner join v_EvnPS eps on eps.EvnPS_id = es.EvnSection_pid
								";
								$where = "es.EvnSection_id = :EvnUslugaOper_pid";
								if (!empty($resp[0]['parentEvnClass_SysNick']) && $resp[0]['parentEvnClass_SysNick'] == 'EvnPS') {
									$from = "
										v_EvnPS eps
									";
									$where = "eps.EvnPS_id = :EvnUslugaOper_pid";
								}

								$resp_eps = $this->queryResult("
									select
										eps.EvnPS_id as \"Evn_id\",
										COALESCE(to_char(cast(eps.EvnPS_setDT as timestamp),'dd.mm.yyyy'),'') ||' / ' || coalesce(LpuSection.LpuSection_Name,'') || ' / ' || coalesce(msf.Person_FIO,'') as \"Evn_Name\",
										to_char (eps.EvnPS_setDT, 'dd.mm.yyyy') as \"Evn_setDate\",
										to_char (eps.EvnPS_OutcomeDT, 'dd.mm.yyyy') as \"Evn_disDate\",
										2 as \"IsPriem\",
										eps.Diag_pid as \"Diag_id\"
									from
										{$from}
										left join v_LpuSection LpuSection on LpuSection.LpuSection_id = eps.LpuSection_pid
										left join v_MedStaffFact Msf on Msf.MedStaffFact_id = eps.MedStaffFact_pid
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
			$accessAdd = " and COALESCE(EV.EvnVizit_IsPaid, 1) = 1 and COALESCE(ES.EvnSection_IsPaid, 1) = 1 ";
		}

		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();
		// текущий врач работает в отделении,  которое указано в движении/посещении, в котором сделано назначение, на основе которого выполнена данная услуга или пользователь статистик.
		$accessAdd .= ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 ? "and (COALESCE(EV2.MedPersonal_id, ES2.MedPersonal_id) in (".implode(',',$med_personal_list).") or (EV.MedPersonal_id is null and ES.MedPersonal_id is null ))" : "");

		$query = "
			SELECT
                case when EUP.Lpu_id = :Lpu_id {$accessAdd} then 'edit' else 'view' end as \"accessType\",
                EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\",
                EUP.EvnUslugaPar_pid as \"EvnUslugaPar_pid\",
                EUP.EvnUslugaPar_pid as \"EvnUslugaPar_rid\",
                EUP.Person_id as \"Person_id\",
                EUP.PersonEvn_id as \"PersonEvn_id\",
                EUP.Server_id as \"Server_id\",
                EUP.EvnDirection_id as \"EvnDirection_id\",
                to_char (EUP.EvnUslugaPar_setDate, 'dd.mm.yyyy') as \"EvnUslugaPar_setDate\",
                COALESCE(to_char(EUP.EvnUslugaPar_setTime, 'HH24:mi'), '') as \"EvnUslugaPar_setTime\",
                EUP.UslugaPlace_id as \"UslugaPlace_id\",
                EUP.Lpu_uid as \"Lpu_uid\",
                EUP.Org_uid as \"Org_uid\",
                EUP.LpuSection_uid as \"LpuSection_uid\",
                EUP.MedStaffFact_id as \"MedStaffFact_id\",
                EUP.Morbus_id as \"Morbus_id\",
                EUP.Usluga_id as \"Usluga_id\",
                EUP.UslugaComplex_id as \"UslugaComplex_id\",
                EUP.PayType_id as \"PayType_id\",
                ROUND(COALESCE(cast(EUP.EvnUslugaPar_Kolvo as numeric), 0), 2) as \"EvnUslugaPar_Kolvo\",
                EUP.EvnUslugaPar_CoeffTariff as \"EvnUslugaPar_CoeffTariff\",
                EUP.EvnUslugaPar_IsModern as \"EvnUslugaPar_IsModern\",
                EUP.MesOperType_id as \"MesOperType_id\",
                EUP.UslugaComplexTariff_id as \"UslugaComplexTariff_id\"
                ,EUP.EvnPrescr_id as \"EvnPrescr_id\"
                ,EUP.EvnPrescrTimetable_id as \"EvnPrescrTimetable_id\"
                ,e.EvnClass_SysNick as \"parentClass\"
            FROM
                v_EvnUslugaPar EUP
                inner join v_EvnDirection_all ed on ed.EvnDirection_id = eup.EvnDirection_id
                left join v_Evn e on e.Evn_id = ed.EvnDirection_pid
                left join v_EvnVizit EV  on EV.EvnVizit_id = EUP.EvnUslugaPar_pid
                left join v_EvnSection ES  on ES.EvnSection_id = EUP.EvnUslugaPar_pid
                left join v_EvnVizit EV2  on EV2.EvnVizit_id = ed.EvnDirection_pid
                left join v_EvnSection ES2  on ES2.EvnSection_id = ed.EvnDirection_pid
            WHERE (1 = 1)
                and EUP.EvnUslugaPar_id = :EvnUslugaPar_id
            LIMIT 1
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
							epl2.EvnVizitPL_id as \"Evn_id\",
							COALESCE(to_char(cast(epl2.EvnVizitPL_setDT as timestamp),'dd.mm.yyyy'),'') ||' / ' || coalesce(LpuSection.LpuSection_Name,'') || ' / ' || coalesce(msf.Person_FIO,'') as \"Evn_Name\",
							to_char (epl2.EvnVizitPL_setDT, 'dd.mm.yyyy') as \"Evn_setDate\",
							to_char (epl2.EvnVizitPL_disDT, 'dd.mm.yyyy') as \"Evn_disDate\"
						from
							v_EvnDirection_all ed
							inner join v_EvnVizitPL epl on epl.EvnVizitPL_id = ed.EvnDirection_pid
							inner join v_EvnVizitPL epl2 on epl2.EvnVizitPL_pid = epl.EvnVizitPL_pid
							left join v_LpuSection LpuSection on LpuSection.LpuSection_id = epl2.LpuSection_id
							left join v_MedStaffFact Msf on Msf.MedStaffFact_id = epl2.MedStaffFact_id
						where
							ed.EvnDirection_id = null :EvnDirection_id

						union all

						select
							es2.EvnSection_id as \"Evn_id\",
							COALESCE(to_char(cast(es2.EvnSection_setDT as timestamp),'dd.mm.yyyy'),'') ||' / ' || coalesce(LpuSection.LpuSection_Name,'') || ' / ' || coalesce(msf.Person_FIO,'') as \"Evn_Name\",
							to_char (es2.EvnSection_setDT, 'dd.mm.yyyy') as \"Evn_setDate\",
							to_char (es2.EvnSection_disDT, 'dd.mm.yyyy') as \"Evn_disDate\"
						from
							v_EvnDirection_all ed
							inner join v_EvnSection es on es.EvnSection_id = ed.EvnDirection_pid
							inner join v_EvnSection es2 on es2.EvnSection_pid = es.EvnSection_pid
							inner join v_LpuSection LpuSection on LpuSection.LpuSection_id = es2.LpuSection_id -- движения только с отделением
							left join v_MedStaffFact Msf on Msf.MedStaffFact_id = es2.MedStaffFact_id
						where
							ed.EvnDirection_id = :EvnDirection_id

						union all

						select
							es2.EvnSection_id as \"Evn_id\",
							COALESCE(to_char(cast(es2.EvnSection_setDT as timestamp(3)),'dd.mm.yyyy'),'') ||' / ' || coalesce(LpuSection.LpuSection_Name,'') || ' / ' || coalesce(msf.Person_FIO,'') as \"Evn_Name\",
							to_char (es2.EvnSection_setDT, 'dd.mm.yyyy') as \"Evn_setDate\",
							to_char (es2.EvnSection_disDT, 'dd.mm.yyyy') as \"Evn_disDate\"
						from
							v_EvnDirection_all ed
							inner join v_EvnSection es2 on es2.EvnSection_pid = ed.EvnDirection_pid
							left join v_LpuSection LpuSection on LpuSection.LpuSection_id = es2.LpuSection_id
							left join v_MedStaffFact Msf on Msf.MedStaffFact_id = es2.MedStaffFact_id
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
			$accessAdd = " and coalesce(EV.EvnVizit_IsPaid, 1) = 1 and coalesce(ES.EvnSection_IsPaid, 1) = 1 ";
		}

		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();
		// текущий врач работает в отделении,  которое указано в движении/посещении, в котором сделано назначение, на основе которого выполнена данная услуга или пользователь статистик.
		$accessAdd .= ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 ? "and (coalesce(EV2.MedPersonal_id, ES2.MedPersonal_id) in (".implode(',',$med_personal_list).") or (EV.MedPersonal_id is null and ES.MedPersonal_id is null ))" : "");

		$query = "
			select
				case when EUP.Lpu_id = :Lpu_id {$accessAdd} then 'edit' else 'view' end as \"accessType\",
				EUP.EvnDirection_pid as \"EvnDirection_pid\",
				e.EvnClass_SysNick as \"parentClass\"
			from
				(select
					:EvnUslugaPar_pid as EvnUslugaPar_pid,
					:EvnDirection_pid as EvnDirection_pid,
					:Lpu_oid as Lpu_id
				) EUP
				left join v_Evn e on e.Evn_id = EUP.EvnDirection_pid
				left join EvnVizit EV on EV.EvnVizit_id = EUP.EvnUslugaPar_pid
				left join EvnSection ES on ES.EvnSection_id = EUP.EvnUslugaPar_pid
				left join EvnVizit EV2 on EV2.EvnVizit_id = EUP.EvnDirection_pid
				left join EvnSection ES2 on ES2.EvnSection_id = EUP.EvnDirection_pid
			limit 1
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
					epl2.EvnVizitPL_id as \"Evn_id\",
					coalesce(to_char(cast(epl2.EvnVizitPL_setDT as timestamp),'DD.MM.YYYY'),'') ||' / ' || coalesce(LpuSection.LpuSection_Name,'') || ' / ' || coalesce(msf.Person_FIO,'') as \"Evn_Name\",
					to_char(epl2.EvnVizitPL_setDT, 'DD.MM.YYYY') as \"Evn_setDate\",
					to_char(epl2.EvnVizitPL_disDT, 'DD.MM.YYYY') as \"Evn_disDate\"
				from
					(select :EvnDirection_pid as EvnDirection_pid) ed
					inner join v_EvnVizitPL epl on epl.EvnVizitPL_id = ed.EvnDirection_pid
					inner join v_EvnVizitPL epl2 on epl2.EvnVizitPL_pid = epl.EvnVizitPL_pid
					left join v_LpuSection LpuSection with on LpuSection.LpuSection_id = epl2.LpuSection_id
					left join v_MedStaffFact Msf with on Msf.MedStaffFact_id = epl2.MedStaffFact_id

				union all

				select
					es2.EvnSection_id as \"Evn_id\",
					coalesce(to_char(cast(es2.EvnSection_setDT as timestamp),'DD.MM.YYYY'),'') ||' / ' || coalesce(LpuSection.LpuSection_Name,'') || ' / ' || coalesce(msf.Person_FIO,'') as \"Evn_Name\",
					to_char(es2.EvnSection_setDT, 'DD.MM.YYYY') as \"Evn_setDate\",
					to_char(es2.EvnSection_disDT, 'DD.MM.YYYY') as \"Evn_disDate\"
				from
					(select :EvnDirection_pid as EvnDirection_pid) ed
					inner join v_EvnSection es on es.EvnSection_id = ed.EvnDirection_pid
					inner join v_EvnSection es2 on es2.EvnSection_pid = es.EvnSection_pid
					inner join v_LpuSection LpuSection with on LpuSection.LpuSection_id = es2.LpuSection_id -- движения только с отделением
					left join v_MedStaffFact Msf with on Msf.MedStaffFact_id = es2.MedStaffFact_id

				union all

				select
					es2.EvnSection_id as \"Evn_id\",
					coalesce(to_char(cast(es2.EvnSection_setDT as timestamp),'DD.MM.YYYY'),'') ||' / ' || coalesce(LpuSection.LpuSection_Name,'') || ' / ' || coalesce(msf.Person_FIO,'') as \"Evn_Name\",
					to_char(es2.EvnSection_setDT, 'DD.MM.YYYY') as \"Evn_setDate\",
					to_char(es2.EvnSection_disDT, 'DD.MM.YYYY') as \"Evn_disDate\"
				from
					(select :EvnDirection_pid as EvnDirection_pid) ed
					inner join v_EvnSection es2 on es2.EvnSection_pid = ed.EvnDirection_pid
					left join v_LpuSection LpuSection with on LpuSection.LpuSection_id = es2.LpuSection_id
					left join v_MedStaffFact Msf with on Msf.MedStaffFact_id = es2.MedStaffFact_id
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
					select Evn_id as \"Evn_id\" from v_Evn where Evn_pid = :pid
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
		$where_clause = "(1=1)";

		if ( !($this->getRegionNick() == 'perm' || ($this->getRegionNick() == 'ufa' && isSuperadmin())) ) {
			$accessAdd .= " and COALESCE(EV.EvnVizit_IsPaid, 1) = 1 and COALESCE(ES.EvnSection_IsPaid, 1) = 1 ";
		}

		$allowVizitCode = (!empty($data['allowVizitCode']) && $data['allowVizitCode'] == 1);

		$accessAdd .= '
			and case
				when EU.Lpu_id = :Lpu_id then 1
				' . (array_key_exists('linkedLpuIdList', $data['session']) && count($data['session']['linkedLpuIdList']) > 1 ? 'when EU.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and COALESCE(EU.' . (in_array($data['class'], array('EvnUslugaStom')) ? $data['class'] : 'EvnUsluga') . '_IsTransit, 1) = 2 then 1' : '') . '
				when cast(:isMedStatUser as boolean) = TRUE then 1
				else 0
			end = 1
		';

        //$accessType = "case when {$accessAdd} " . ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 ? "and (coalesce(EV.MedPersonal_id, ES.MedPersonal_id) in (".implode(',',$med_personal_list).")or (ES.MedPersonal_id is null and EV.MedPersonal_id is null))" : "") . " then 'edit' else 'view' end as accessType,";
		$accessType = "case when {$accessAdd} " . ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 ? "and (COALESCE(EV.MedPersonal_id, ES.MedPersonal_id) in (".implode(',',$med_personal_list).")
		or exists(
					select WG.WorkGraph_id
						from v_WorkGraph WG
						inner join v_MedStaffFact WG_MSF on WG_MSF.MedStaffFact_id = WG.MedStaffFact_id
						where (
							CAST(WG.WorkGraph_begDT as date) <= CAST(dbo.tzGetDate() as date)
							and CAST(WG.WorkGraph_endDT as date) >= CAST(dbo.tzGetDate() as date)
						)
						and WG_MSF.MedPersonal_id in (".implode(',',$med_personal_list).")
			 	)
		or (ES.MedPersonal_id is null and EV.MedPersonal_id is null))" : "") . " then 'edit' else 'view' end as \"accessType\",";

		if (isset($data['accessType']) && $data['accessType'] == 'view') {
			$accessType  = " 'view' as \"accessType\",";
		}
		
        $addSelectclause = '';
        $select_clause = "
             EU.EvnUsluga_id as \"EvnUsluga_id\"
            ,EU.EvnUsluga_pid as \"EvnUsluga_pid\"
            ,RTRIM(EU.EvnClass_SysNick) as \"EvnClass_SysNick\"
            ,to_char (EU.EvnUsluga_setDate, 'dd.mm.yyyy') as \"EvnUsluga_setDate\"
            ,COALESCE(to_char(EU.EvnUsluga_setTime, 'HH24:mi'),'') as \"EvnUsluga_setTime\"
			,COALESCE(Usluga.Usluga_Code, UC.UslugaComplex_Code) as \"Usluga_Code\"
			,COALESCE(Usluga.Usluga_Name, UC.UslugaComplex_Name) as \"Usluga_Name\"
            ,ROUND(cast(EU.EvnUsluga_Kolvo as numeric), 2) as \"EvnUsluga_Kolvo\"
			,ROUND(cast(EU.EvnUsluga_Price as numeric), 2) as \"EvnUsluga_Price\"
			,ROUND(cast(EU.EvnUsluga_Summa as numeric), 2) as \"EvnUsluga_Summa\"
            ,PT.PayType_id as \"PayType_id\"
            ,COALESCE(PT.PayType_SysNick, '') as \"PayType_SysNick\"
        ";
        $from_clause = "
            v_EvnUsluga EU
            left join v_Evn EvnParent on EvnParent.Evn_id = EU.EvnUsluga_pid
            left join v_EvnPrescr EP on EP.EvnPrescr_id = EU.EvnPrescr_id
			left join v_Usluga Usluga on Usluga.Usluga_id = EU.Usluga_id
			left join v_UslugaComplex UC on UC.UslugaComplex_id = EU.UslugaComplex_id
            left join v_EvnVizit EV on EV.EvnVizit_id = EU.EvnUsluga_pid
            left join v_EvnSection ES on ES.EvnSection_id = EU.EvnUsluga_pid
			left join v_PayType PT on PT.PayType_id = EU.PayType_id
        ";

		if (!empty($data['isMorbusOnko']) && $data['isMorbusOnko'] == 2) {
			$select_clause .= "
			,COALESCE(onkoucat.UslugaComplexAttributeType_Name, 'Неспецифическое лечение') as \"UslugaComplexAttributeType_Name\"
			";
			$from_clause .= "
			LEFT JOIN LATERAL (
				select UslugaComplexAttributeType_SysNick, UslugaComplexAttributeType_Name
				from v_UslugaComplexAttribute UCA
				inner join v_UslugaComplexAttributeType UCAT on UCAT.UslugaComplexAttributeType_id = UCA.UslugaComplexAttributeType_id
				where UCA.UslugaComplex_id = EU.UslugaComplex_id and UslugaComplexAttributeType_SysNick in ('XimLech','LuchLech','GormImunTerLech','XirurgLech')
				limit 1
			) onkoucat ON true
			";
		}

		if ( !empty($data['parent']) && $data['parent'] == 'EvnPLStom' ) {
			$from_clause .= "
				left join v_EvnUslugaStom EUS on EUS.EvnUslugaStom_id = EU.EvnUsluga_id
				left join v_EvnDiagPLStom EDPLS on EDPLS.EvnDiagPLStom_id = EUS.EvnDiagPLStom_id
				LEFT JOIN LATERAL (
					select MesUsluga_IsNeedUsluga
					from v_MesUsluga
					where Mes_id = EDPLS.Mes_id
						and UslugaComplex_id = EU.UslugaComplex_id
			        limit 1
				) MU ON true

			";
			$select_clause .= "
				,case when EU.EvnUsluga_IsVizitCode = 2 then 'X' else '' end as \"EvnUsluga_IsVizitCode\"
				,case when EUS.EvnUslugaStom_IsAllMorbus = 2 then 'X' else '' end as \"EvnUsluga_IsAllMorbus\"
				,case when EUS.EvnUslugaStom_IsMes = 2 then 'X' else '' end as \"EvnUsluga_IsMes\"
				,case when MU.MesUsluga_IsNeedUsluga = 2 then 'X' else '' end as \"EvnUsluga_IsRequired\"
			";
		}

		// Параклинические услуги должны отображаться только выполненные
		// https://redmine.swan.perm.ru/issues/55296
		//$EvnClassFilter = "and EU.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaStom', /*'EvnUslugaOnkoChem', 'EvnUslugaOnkoBeam', 'EvnUslugaOnkoGormun',*/ 'EvnUslugaOnkoSurg', 'EvnUslugaPar')";
		//if (!empty($data['parent']) && $data['parent'] == 'EvnPS') {
		$EvnClassFilter = "and (EU.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaStom' /*, 'EvnUslugaOnkoChem', 'EvnUslugaOnkoBeam', 'EvnUslugaOnkoGormun', 'EvnUslugaOnkoSurg'*/ ) OR (EU.EvnClass_SysNick ilike 'EvnUslugaPar' and EU.EvnUsluga_setDate is not null))"; // все услуги отображаемые в ЕМК.
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
				$evnFilter = "and EU.Morbus_id = (SELECT morbus_id FROM dbo.v_Evn WHERE evn_id = :pid AND Morbus_id IS NOT null)";
			}
            if (isset($data['EvnEdit_id']) && (!empty($data['EvnEdit_id']))) {
				$p['EvnEdit_id'] = $data['EvnEdit_id'];
				$evnFilter .= " and (not exists(select * from v_Evn where Evn_id = :EvnEdit_id) or EU.EvnUsluga_pid = :EvnEdit_id)";
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
				select EvnUsluga_id from v_EvnUsluga EU
				where  :pid = {$pidName}
				{$EvnClassFilter}
				" . ($allowVizitCode === false ? "and COALESCE(EU.EvnUsluga_IsVizitCode, 1) = 1" : "") . "
			)";
			if(!empty($data['rid']) && !empty($data['parent']) && $data['parent'] == 'EvnPLStom') {
				$with_clause = "
				with UslugaPid (
					EvnUsluga_id
				) as (
					select EvnUsluga_id from v_EvnUsluga EU
					where  (:pid = EU.EvnUsluga_pid or :rid = EU.EvnUsluga_rid)
					{$EvnClassFilter}
					" . ($allowVizitCode === false ? "and COALESCE(EU.EvnUsluga_IsVizitCode, 1) = 1" : "") . "
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
			//$where_clause = "
			//	 exists(Select 1 from UslugaPid where UslugaPid.EvnUsluga_id = EU.EvnUsluga_id)
			//";
			//оптимизация запроса
			$from_clause = str_replace("v_EvnUsluga EU", "v_EvnUsluga EU
			inner join UslugaPid on UslugaPid.EvnUsluga_id = EU.Evnusluga_id", $from_clause);
		}
		$this->load->model('EvnVizitPL_model');
		if ( $this->EvnVizitPL_model->isUseVizitCode && $allowVizitCode === false ) {
			$where_clause .= "
				and COALESCE(EU.EvnUsluga_IsVizitCode, 1) = 1
			";
		}

		//Только основные услуги
		$where_clause .= " and (EvnParent.EvnClass_SysNick <> 'EvnUslugaPar' or EvnParent.EvnClass_SysNick is null)";

		if ( !empty($data['parent']) && $data['parent'] == 'EvnPLStom' && $data['class'] == 'EvnUsluga' ) {
			$select_clause .= "
				,ROUND(COALESCE(EU.EvnUsluga_Price, 0), 2) as \"EvnUsluga_Price\"
				,ROUND(COALESCE(EU.EvnUsluga_Summa, 0), 2) as \"EvnUsluga_Summa\"
			";
			$from_clause .= " left join v_UslugaComplexTariff UCT on UCT.UslugaComplexTariff_id = EU.UslugaComplexTariff_id";
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
						EU.EvnUslugaStom_id as \"EvnUsluga_id\",
						EU.EvnUslugaStom_pid as \"EvnUsluga_pid\",
						'EvnUslugaStom' as \"EvnClass_SysNick\",
						to_char (EU.EvnUslugaStom_setDate, 'dd.mm.yyyy') as \"EvnUsluga_setDate\",
						COALESCE(Usluga.Usluga_Code, UC.UslugaComplex_Code) as \"Usluga_Code\",
						COALESCE(Usluga.Usluga_Name, UC.UslugaComplex_Name) as \"Usluga_Name\",
						ROUND(cast(EU.EvnUslugaStom_Kolvo as numeric), 2) as \"EvnUsluga_Kolvo\",
						ROUND(cast(EU.EvnUslugaStom_Price as numeric), 2) as \"EvnUsluga_Price\",
						ROUND(cast(EU.EvnUslugaStom_Summa as numeric), 2) as \"EvnUsluga_Summa\",
						PT.PayType_id as \"PayType_id\",
						COALESCE(PT.PayType_SysNick, '') as \"PayType_SysNick\",
						COALESCE(parodontogram.Parodontogram_id, 0) as \"EvnUslugaStom_hasParodontogram\",
						case when 'A02.07.009' = COALESCE(UC2011.UslugaComplex_Code,'') then 1 else 0 end as \"EvnUslugaStom_isParodontogram\",
						case
							when Diag.Diag_Code is not null then Diag.Diag_Code || ' ' || COALESCE(cast(Tooth.Tooth_Code as varchar(2)), '')
							else ''
						end as \"EvnDiagPLStom_Title\",
						Diag.Diag_Code as \"Diag_Code\",
						Tooth.Tooth_Code as \"Tooth_Code\",
						case when EU.EvnUslugaStom_IsAllMorbus = 2 then 'X' else '' end as \"EvnUsluga_IsAllMorbus\",
						case when EU.EvnUslugaStom_IsMes = 2 then 'X' else '' end as \"EvnUsluga_IsMes\",
						case when MU.MesUsluga_IsNeedUsluga = 2 then 'X' else '' end as \"EvnUsluga_IsRequired\",
						EU.EvnDiagPLStom_id as \"EvnDiagPLStom_id\",
						EU.UslugaComplex_id as \"UslugaComplex_id\",
						EU.EvnClass_id as \"EvnClass_id\",
						doc.EvnXml_id as \"EvnXml_id\",
						isoper.UslugaComplexAttributeType_SysNick as \"isoper\"
                ";
                $from_clause = "
						v_EvnUslugaStom EU
						inner join v_EvnVizitPLStom EVPLS on EVPLS.EvnVizitPLStom_id = EU.EvnUslugaStom_pid
						left join v_EvnDiagPLStom EDPLS on EDPLS.EvnDiagPLStom_id = EU.EvnDiagPLStom_id
						LEFT JOIN LATERAL (
							select MesUsluga_IsNeedUsluga
							from v_MesUsluga
							where Mes_id = {$MesField}
								and UslugaComplex_id = EU.UslugaComplex_id
                            limit 1
						) MU ON true
						left join v_Diag Diag on Diag.Diag_id = EDPLS.Diag_id
						left join v_Tooth Tooth on Tooth.Tooth_id = EDPLS.Tooth_id
						left join Usluga on Usluga.Usluga_id = EU.Usluga_id
						left join v_UslugaComplex UC on UC.UslugaComplex_id = EU.UslugaComplex_id
						left join EvnVizit EV on EV.Evn_id = EU.EvnUslugaStom_pid
						left join EvnSection ES on ES.Evn_id = EU.EvnUslugaStom_pid
						left join v_PayType PT on PT.PayType_id = EU.PayType_id
						left join v_UslugaComplex UC2011  on UC2011.UslugaComplex_id = UC.UslugaComplex_2011id
						LEFT JOIN LATERAL (
							select p.Parodontogram_id
							from v_Parodontogram p
							where p.EvnUslugaStom_id = EU.EvnUslugaStom_id
                            limit 1
						) parodontogram ON true
						LEFT JOIN LATERAL (
							select EvnXml_id
							from v_EvnXml
							where Evn_id = EU.EvnUslugaStom_id
							order by EvnXml_insDT desc -- костыль, должен быть только один протокол
                            limit 1
						) doc ON true
						LEFT JOIN LATERAL (
							select UslugaComplexAttributeType_SysNick
							from v_UslugaComplexAttribute UCA
							inner join v_UslugaComplexAttributeType UCAT  on UCAT.UslugaComplexAttributeType_id = UCA.UslugaComplexAttributeType_id
							where UCA.UslugaComplex_id = EU.UslugaComplex_id and UCAT.UslugaComplexAttributeType_SysNick = 'operstomatusl'
                            limit 1
						) isoper ON true
				";
				$where_clause = "
					(1 = 1)
				";

				if ( $this->EvnVizitPL_model->isUseVizitCode && $allowVizitCode === false ) {
					$where_clause .= "
						and COALESCE(EU.EvnUslugaStom_IsVizitCode, 1) = 1
					";
				}
				// Добавляем union для второго условия (на форме заболевания видим услуги, добавленные по этому заболеванию + услуги "для всех заболеваний"),
				// т.е. для отображения услуг "для всех заболеваний"
				$where_clause_union = $where_clause;
				if ( !empty($data['isEvnDiagPLStom']) ) {
					$where_clause .= "and EU.EvnDiagPLStom_id = :mid ";
					$where_clause_union .= "and EU.EvnUslugaStom_rid = :rid and COALESCE(EU.EvnUslugaStom_IsAllMorbus, 1) = 2 ";
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
					v_EvnUsluga_all EU
					left join v_Usluga Usluga  on Usluga.Usluga_id = EU.Usluga_id
					left join v_UslugaComplex UC  on UC.UslugaComplex_id = EU.UslugaComplex_id
					left join EvnVizit EV  on EV.Evn_id = EU.EvnUsluga_pid
					left join EvnSection ES  on ES.Evn_id = EU.EvnUsluga_pid
					left join EvnUslugaOnkoChem OnkoChem  on EU.EvnClass_SysNick = 'EvnUslugaOnkoChem' and OnkoChem.Evn_id = EU.EvnUsluga_id
					left join v_OnkoUslugaChemKindType ChemKindType  on EU.EvnClass_SysNick = 'EvnUslugaOnkoChem' and ChemKindType.OnkoUslugaChemKindType_id = OnkoChem.OnkoUslugaChemKindType_id
					left join v_OnkoUslugaChemFocusType ChemFocusType  on EU.EvnClass_SysNick = 'EvnUslugaOnkoChem' and ChemFocusType.OnkoUslugaChemFocusType_id = OnkoChem.OnkoUslugaChemFocusType_id
					left join EvnUslugaOnkoGormun OnkoGormun  on EU.EvnClass_SysNick = 'EvnUslugaOnkoGormun' and OnkoGormun.Evn_id = EU.EvnUsluga_id
					left join v_OnkoUslugaGormunFocusType GormunFocusType  on EU.EvnClass_SysNick = 'EvnUslugaOnkoGormun' and GormunFocusType.OnkoUslugaGormunFocusType_id = OnkoGormun.OnkoUslugaGormunFocusType_id
					left join EvnUslugaOnkoBeam OnkoBeam  on EU.EvnClass_SysNick = 'EvnUslugaOnkoBeam' and OnkoBeam.Evn_id = EU.EvnUsluga_id
					left join v_OnkoUslugaBeamKindType OnkoBeamKindType  on EU.EvnClass_SysNick = 'EvnUslugaOnkoBeam' and OnkoBeamKindType.OnkoUslugaBeamKindType_id = OnkoBeam.OnkoUslugaBeamKindType_id
					left join v_OnkoUslugaBeamRadioModifType OnkoBeamRadioModifType  on EU.EvnClass_SysNick = 'EvnUslugaOnkoBeam' and OnkoBeamRadioModifType.OnkoUslugaBeamRadioModifType_id = OnkoBeam.OnkoUslugaBeamRadioModifType_id
					left join v_OnkoUslugaBeamMethodType OnkoBeamMethodType  on EU.EvnClass_SysNick = 'EvnUslugaOnkoBeam' and OnkoBeamMethodType.OnkoUslugaBeamMethodType_id = OnkoBeam.OnkoUslugaBeamMethodType_id
					left join v_OnkoUslugaBeamIrradiationType OnkoBeamIrradiationType  on EU.EvnClass_SysNick = 'EvnUslugaOnkoBeam' and OnkoBeamIrradiationType.OnkoUslugaBeamIrradiationType_id = OnkoBeam.OnkoUslugaBeamIrradiationType_id
					left join v_OnkoUslugaBeamFocusType BeamFocusType  on EU.EvnClass_SysNick = 'EvnUslugaOnkoBeam' and BeamFocusType.OnkoUslugaBeamFocusType_id = OnkoBeam.OnkoUslugaBeamFocusType_id
					left join v_Lpu Lpu  on EU.Lpu_uid = Lpu.Lpu_id
					left join EvnUslugaOnkoSurg OnkoSurg  on EU.EvnClass_SysNick = 'EvnUslugaOnkoSurg' and OnkoSurg.Evn_id = EU.EvnUsluga_id
					left join v_OperType OperType  on EU.EvnClass_SysNick = 'EvnUslugaOnkoSurg' and OnkoSurg.OperType_id = OperType.OperType_id
					left join v_MedPersonal MP  on EU.EvnClass_SysNick = 'EvnUslugaOnkoSurg' and EU.MedPersonal_id = MP.MedPersonal_id and EU.Lpu_uid = MP.Lpu_id
				";
				$select_clause = "
				 EU.EvnUsluga_id as \"EvnUsluga_id\"
				,RTRIM(EU.EvnClass_SysNick) as \"EvnClass_SysNick\"
				,to_char (EU.EvnUsluga_setDate, 'dd.mm.yyyy') as \"EvnUsluga_setDate\"
				,COALESCE(to_char(EU.EvnUsluga_setTime, 'HH24:mi'),'') as \"EvnUsluga_setTime\"
				,COALESCE(Usluga.Usluga_Code, UC.UslugaComplex_Code) as \"Usluga_Code\"
				,COALESCE(Usluga.Usluga_Name, UC.UslugaComplex_Name) as \"Usluga_Name\"
				,to_char (EU.EvnUsluga_disDate, 'dd.mm.yyyy') as \"EvnUsluga_disDate\",
				EU.Person_id as \"Person_id\",
				EU.EvnUsluga_pid as \"EvnUsluga_pid\",
				CASE EU.UslugaPlace_id
					WHEN 1 THEN 	--1	Отделение ЛПУ
						(SELECT s.LpuSection_Name FROM v_LpuSection s WHERE s.LpuSection_id = EU.LpuSection_uid limit 1)
					WHEN 2 THEN 	--2	Другое ЛПУ
						(SELECT l.Lpu_Nick FROM v_lpu l WHERE l.Lpu_id = eu.Lpu_uid limit 1)
					WHEN 3 THEN		--3	Другая организация
						(SELECT o.Org_Nick FROM v_org o WHERE o.Org_id = eu.Org_uid limit 1)
				END AS \"place_name\",
				Lpu.Lpu_Nick as \"Lpu_Name\",
				MP.Person_Fio as \"MedPersonal_Name\",
				OperType.OperType_Name as \"OperType_Name\",
				ChemKindType.OnkoUslugaChemKindType_Name as \"OnkoUslugaChemKindType_Name\",
				OnkoGormun.EvnUslugaOnkoGormun_IsBeam as \"EvnUslugaOnkoGormun_IsBeam\",
				OnkoGormun.EvnUslugaOnkoGormun_IsSurg as \"EvnUslugaOnkoGormun_IsSurg\",
				OnkoGormun.EvnUslugaOnkoGormun_IsDrug as \"EvnUslugaOnkoGormun_IsDrug\",
				OnkoGormun.EvnUslugaOnkoGormun_IsOther as \"EvnUslugaOnkoGormun_IsOther\",
				coalesce(GormunFocusType.OnkoUslugaGormunFocusType_Name,ChemFocusType.OnkoUslugaChemFocusType_Name,BeamFocusType.OnkoUslugaBeamFocusType_Name) as \"FocusType_Name\",
				OnkoBeamIrradiationType.OnkoUslugaBeamIrradiationType_Name as \"OnkoUslugaBeamIrradiationType_Name\",
				OnkoBeamKindType.OnkoUslugaBeamKindType_Name as \"OnkoUslugaBeamKindType_Name\",
				OnkoBeamMethodType.OnkoUslugaBeamMethodType_Name as \"OnkoUslugaBeamMethodType_Name\",
				OnkoBeamRadioModifType.OnkoUslugaBeamRadioModifType_Name as \"OnkoUslugaBeamRadioModifType_Name\"";
				if(!empty($data['EvnEdit_id']))
				{
					$p['EvnEdit_id'] = $data['EvnEdit_id'];
					$accessType = 'case when (EU.Lpu_id ' . getLpuIdFilter($data) . ' or EU.Lpu_uid ' . getLpuIdFilter($data) . ') and M.Morbus_disDT is null and (';
					//условие при редактировании из регистра
					$accessType .= '(EU.EvnUsluga_pid is null and EU.Person_id = :EvnEdit_id)';
					$accessType .= ' or ';
					//при редактировании из учетного документа #55518
					$accessType .= "(EU.EvnUsluga_pid = :EvnEdit_id)";
					$accessType .= ") then 'edit' else 'view' end as \"accessType\",";
					$addSelectclause .= '
					,EU.Morbus_id as "Morbus_id"
					,:EvnEdit_id as "MorbusOnko_pid"';
					$from_clause .= '
					left join v_Morbus M on M.Morbus_id = EU.Morbus_id
					left join v_Evn EvnEdit on EvnEdit.Evn_id = :EvnEdit_id and EU.Person_id != :EvnEdit_id';
				} else {
					$accessType = 'case when (EU.Lpu_id ' . getLpuIdFilter($data) . ' or EU.Lpu_uid ' . getLpuIdFilter($data) . ') and M.Morbus_disDT is null and (';
					$accessType .= 'EU.EvnUsluga_pid is null';
					$accessType .= ") then 'edit' else 'view' end as \"accessType\",";
					$addSelectclause .= '
					,EU.Morbus_id as "Morbus_id"
					,EU.Person_id as "MorbusOnko_pid"';
					$from_clause .= 'left join v_Morbus M on M.Morbus_id = EU.Morbus_id';
				}
				if (isset($data['accessType']) && $data['accessType'] == 'view') {
					$accessType  = " 'view' as \"accessType\",";
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
				UslugaComplex_id as \"UslugaComplex_id\",
				UslugaComplex_Code as \"UslugaComplex_Code\",
				RTRIM(UslugaComplex_Name) as \"UslugaComplex_Name\"
			from
				UslugaComplex
			where
				Lpu_id = null :Lpu_id
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
				UslugaComplex_id as \"UslugaComplex_id\",
				UslugaComplex_Code as \"UslugaComplex_Code\",
				RTRIM(UslugaComplex_Name) as \"UslugaComplex_Name\"
				-- end select
			from
				-- from
				UslugaComplex
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
				UCL.UslugaComplexList_id as \"UslugaComplexList_id\",
				UCom.UslugaComplex_id as \"UslugaComplex_id\",
				U.Usluga_id as \"Usluga_id\",
				UC.UslugaClass_id as \"UslugaClass_id\",
				U.Usluga_Code as \"Usluga_Code\",
				RTRIM(U.Usluga_Name) as \"Usluga_Name\",
				RTRIM(UC.UslugaClass_Name) as \"UslugaClass_Name\"
				-- end select
			from
				-- from
				UslugaComplexList UCL
				inner join Usluga U on U.Usluga_id = UCL.Usluga_id
				inner join UslugaComplex UCom on UCom.UslugaComplex_id = UCL.UslugaComplex_id
				inner join UslugaClass UC on UC.UslugaClass_id = UCL.UslugaClass_id
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
					1 as \"UslugaComplex_id\",
					Usluga_id as \"Usluga_id\",
					Usluga_Code as \"Usluga_Code\",
					RTRIM(Usluga_Name) as \"Usluga_Name\",
					0 as \"UslugaComplex_UET\"
				from
					v_Usluga
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
					UC.UslugaComplex_id as \"UslugaComplex_id\",
					U.Usluga_id as \"Usluga_id\",
					U.Usluga_Code as \"Usluga_Code\",
					RTRIM(U.Usluga_Name) as \"Usluga_Name\",
					ROUND(UC.UslugaComplex_UET, 2) as \"UslugaComplex_UET\"
				from v_UslugaComplex UC
					inner join Usluga U  on U.Usluga_id = UC.Usluga_id
					inner join LpuSection LS  on LS.LpuSection_id = UC.LpuSection_id
					inner join LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id
					inner join LpuBuilding LB  on LB.LpuBuilding_id = LU.LpuBuilding_id
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
						PL.PersonLabel_id as \"PersonLabel_id\"
					FROM v_PersonLabel PL
						left join v_LabelDiag LD on PL.Label_id = LD.Label_id
						left join v_EvnUslugaCommon EUC on LD.UslugaComplex_id = EUC.UslugaComplex_id and PL.Person_id = EUC.Person_id
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
			$data['EvnUslugaCommon_setDate'] .= ' ' . $data['EvnUslugaCommon_setTime'];
		}

		if ( !empty($data['EvnUslugaCommon_disDate']) && !empty($data['EvnUslugaCommon_disTime']) ) {
			$data['EvnUslugaCommon_disDate'] .= ' ' . $data['EvnUslugaCommon_disTime'];
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
				select
					MedStaffFact_id as \"MedStaffFact_id\"
				from
					v_MedStaffFact
				where
					MedStaffFact_id = :MedStaffFact_id
					{$filterLpuSection}
					and MedPersonal_id = :MedPersonal_id
			    limit 1
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
			 WITH cte AS (
                SELECT
                    cast(:EvnUslugaCommon_id as bigint) AS res,
                    CASE WHEN :Morbus_id IS NOT NULL THEN :Morbus_id ELSE (SELECT morbus_id FROM v_evn WHERE evn_id = :EvnUslugaCommon_pid) END AS morbus_id

            )
			select
            	EvnUslugaCommon_id as \"EvnUslugaCommon_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
            from dbo." . $procedure . "(
            	EvnUslugaCommon_id := (select res from cte),
				EvnUslugaCommon_pid := :EvnUslugaCommon_pid,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnUslugaCommon_setDT := :EvnUslugaCommon_setDT,
				EvnUslugaCommon_disDT := :EvnUslugaCommon_disDT,
				PayType_id := :PayType_id,
				Usluga_id := :Usluga_id,
				UslugaComplex_id := :UslugaComplex_id,
				MedSpecOms_id := :MedSpecOms_id,
				LpuSectionProfile_id := :LpuSectionProfile_id,
				MedPersonal_id := :MedPersonal_id,
				MedStaffFact_id := :MedStaffFact_id,
				UslugaPlace_id := :UslugaPlace_id,
				Lpu_uid := :Lpu_uid,
				LpuSection_uid := :LpuSection_uid,
				Org_uid := :Org_uid,
				EvnUslugaCommon_Kolvo := :EvnUslugaCommon_Kolvo,
				Morbus_id := (select morbus_id from cte),
				EvnUslugaCommon_CoeffTariff := :EvnUslugaCommon_CoeffTariff,
				EvnUslugaCommon_IsModern := :EvnUslugaCommon_IsModern,
				MesOperType_id := :MesOperType_id,
				UslugaComplexTariff_id := :UslugaComplexTariff_id,
				DiagSetClass_id := :DiagSetClass_id,
				Diag_id := :Diag_id,
				EvnUslugaCommon_Price := :EvnUslugaCommon_Price,
				EvnUslugaCommon_Summa := :EvnUslugaCommon_Summa,
				EvnPrescr_id := :EvnPrescr_id,
				EvnUslugaCommon_IsVizitCode := :EvnUslugaCommon_IsVizitCode,
				EvnUslugaCommon_IsMinusUsluga := :EvnUslugaCommon_IsMinusUsluga,
				EvnDirection_id := :EvnDirection_id,
				UslugaExecutionType_id := :UslugaExecutionType_id,
				UslugaExecutionReason_id := :UslugaExecutionReason_id,
				pmUser_id := :pmUser_id);
		";

		$queryParams = array(
			'EvnUslugaCommon_id' => $data['EvnUslugaCommon_id'] ? $data['EvnUslugaCommon_id'] : null,
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
				select EvnPrescr_id  as "EvnPrescr_id" from v_EvnUsluga
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
							epd.EvnDirection_id as \"EvnDirection_id\"
						from
							v_EvnPrescrDirection epd
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
					case when COALESCE(uc.UslugaComplex_isGenXml,0) = 0 then uc.XmlTemplate_id else null end as \"XmlTemplate_id\",
					uc.UslugaComplex_id as \"UslugaComplex_id\",
					RTrim(uc.UslugaComplex_Name) as \"UslugaComplex_Name\",
					RTrim(uc.UslugaComplex_ACode) as \"UslugaComplex_ACode\",
					'' as \"UslugaComplex_Value\",
					RTrim(RefValuesUnit.RefValuesUnit_Name) as \"UslugaComplex_Measure\",
					RefValues_LowerLimit as \"UslugaComplex_ValueLow\",
					RefValues_UpperLimit as \"UslugaComplex_ValueUpp\",
					XmlTemplate.XmlTemplate_Data as \"XmlTemplate_Data\",
					:EvnUsluga_id as \"EvnUsluga_id\",
					uc.Usluga_id as \"Usluga_id\",
					uc.RefValues_id as \"RefValues_id\",
					COALESCE(uc.UslugaComplex_isGenXml,0) as \"UslugaComplex_isGenXml\",
					case when Leaf.leaf_count>0 then 0 else 1 end as \"leaf\"
				from v_UslugaComplex uc
				left join v_RefValues RefValues  on RefValues.RefValues_id = uc.RefValues_id
				--left join v_Usluga Usluga  on Usluga.Usluga_id = uc.Usluga_id
				left join v_RefValuesUnit RefValuesUnit  on RefValuesUnit.RefValuesUnit_id = RefValues.RefValuesUnit_id
				left join XmlTemplate  on XmlTemplate.XmlTemplate_id = uc.XmlTemplate_id
				LEFT JOIN LATERAL (
					Select count(*) as leaf_count from v_UslugaComplex  where UslugaComplex_pid = uc.UslugaComplex_id
				) as Leaf ON true
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
					select
						EvnUsluga_id as \"EvnUslugaPar_id\"
					from
						v_EvnUsluga
					where
						UslugaComplex_id = :UslugaComplex_id
						and EvnDirection_id = :EvnDirection_id
				    limit 1
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
				$add_param = 'PrehospDirect_id := CAST(:PrehospDirect_id AS bigint),
					Org_did := CAST(:Org_did AS bigint),
					Lpu_did := CAST(:Lpu_did AS bigint),
					LpuSection_did := CAST(:LpuSection_did AS bigint),
					MedPersonal_did := CAST(:MedPersonal_did AS bigint),';
			}

			if (empty($queryParams['EvnUsluga_pid']) && !empty($queryParams['EvnPrescr_id'])) {
				$query = "
					select
						e_child.Evn_id as \"Evn_id\"
					from
						v_EvnPrescr ep
						inner join v_Evn e on e.Evn_id = EvnPrescr_pid -- посещние/движение
						inner join v_Evn e_child on e_child.Evn_pid = e.Evn_pid -- посещения/движения той же КВС/ТАП
					where
						e_child.EvnClass_SysNick IN ('EvnSection', 'EvnVizitPL', 'EvnVizitPLStom')
						and EvnPrescr_id = :EvnPrescr_id
						/*and e_child.Evn_setDT <= :EvnUsluga_setDT and (e_child.Evn_disDT >= :EvnUsluga_setDT OR e_child.Evn_disDT IS NULL)*/ -- актуальное
					limit 1
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
				WITH cte AS (
                        SELECT
                        	case when :PayType_id is null then (
                                select PayType_id from v_PayType where PayType_SysNick = :PayType_SysNickOMS limit 1
                            ) else :PayType_id end AS pt,
                            CAST(:EvnPrescr_id AS bigint) AS EvnPrescr_id,
                            CAST(:EvnUsluga_pid AS bigint) AS EvnUsluga_pid,
                            CAST(:EvnUsluga_id AS bigint) AS res
                 )

				SELECT
                	evnuslugapar_id as \"EvnUsluga_id\",
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg \"
                FROM dbo." . $procedure . "(
					".$data['object']."_pid := (SELECT EvnUsluga_pid FROM cte),
					Lpu_id := CAST(:Lpu_id AS bigint),
					Server_id := CAST(:Server_id AS bigint),
					PersonEvn_id := CAST(:PersonEvn_id AS bigint),
					".$data['object']."_setDT := CAST(:EvnUsluga_setDT AS timestamp),
					PayType_id := (SELECT pt FROM cte),
					Usluga_id := CAST(:Usluga_id AS bigint),
					".$data['object']."_Kolvo := 1,
					".$data['object']."_isCito := CAST(:Usluga_isCito AS bigint),
					".$time_table."_id := CAST(:time_table_id AS bigint),
					{$add_param}
					UslugaPlace_id := CAST(:UslugaPlace_id AS bigint),
					LpuSection_uid := CAST(:LpuSection_uid AS bigint),
					MedPersonal_id := CAST(:MedPersonal_id AS bigint),
					UslugaComplex_id := CAST(:UslugaComplex_id AS bigint),
					EvnDirection_id := CAST(:EvnDirection_id AS bigint),
					Diag_id := :Diag_id,
					".$data['object']."_Result := CAST(:EvnUsluga_Result AS varchar),
					EvnPrescr_id := (SELECT EvnPrescr_id FROM cte),
					EvnPrescrTimetable_id := CAST(NULL AS bigint),
					pmUser_id := CAST(:pmUser_id AS bigint)
					);
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
				EvnClass_SysNick as \"EvnClass_SysNick\"
			from v_Evn
			where
				Evn_id = :EvnUsluga_pid";
		$EvnClass_SysNick = $this->getFirstResultFromQuery($query, $params);
		if (!in_array($EvnClass_SysNick, array('EvnVizitPL','EvnVizitPLStom','EvnPS','EvnSection'))) {
			return array('Error_Msg' => 'Невозможно определить тип финансирования', 'Error_Code' => 500);
		}

		$query = "
			select
				PT.PayType_id as \"PayType_id\",
				PT.PayType_Code as \"PayType_Code\",
				PT.PayType_SysNick as \"PayType_SysNick\"
			from v_{$EvnClass_SysNick} Evn
				left join v_PayType PT on PT.PayType_id = Evn.PayType_id
			where
				Evn.{$EvnClass_SysNick}_id = :EvnUsluga_pid
			limit 1
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
			select
				EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				to_char(EUP.EvnUslugaPar_setDate, 'DD.MM.YYYY') as \"EvnUslugaPar_setDate\",
				RTRIM(coalesce(ps.Person_Surname, '')) || ' ' || SUBSTRING(coalesce(ps.Person_Firname, ''),1,1) || ' ' || SUBSTRING(coalesce(ps.Person_Secname, ''),1,1) as \"Person_Fin\",
				uc.UslugaComplex_Name as \"UslugaComplex_Name\",
				els.MedService_id as \"MedService_id\",
				ms.LpuSection_id as \"LpuSection_id\",
				eup.Lpu_id as \"Lpu_id\",
				eup.Person_id as \"Person_id\",
				eup.LpuSection_uid as \"LpuSection_uid\",
				eup.Lpu_oid as \"Lpu_oid\",
				eup.MedStaffFact_id as \"MedStaffFact_id\"
			from
				v_EvnUslugaPar EUP
				inner join v_PersonState ps on ps.Person_id = EUP.Person_id
				inner join v_UslugaComplex uc on uc.UslugaComplex_id = eup.UslugaComplex_id
				left join v_UslugaTest ut on ut.UslugaTest_pid = eup.EvnUslugaPar_id
				left join v_EvnLabSample els on els.EvnLabSample_id = coalesce(ut.EvnLabSample_id, eup.EvnLabsample_id)
				left join v_MedService ms on ms.MedService_id = els.MedService_id
			where
				EUP.EvnUslugaPar_id = :EvnUslugaPar_id
			limit 1
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

		$EvnUsluga_setDT = ConvertDateFormat($params['EvnUsluga_setDT'], 'Y-m-d H:i');
		$EvnUsluga_disDT = ConvertDateFormat($params['EvnUsluga_disDT'], 'Y-m-d H:i');

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
					select
						('«'||UslugaComplex_Code||'. '||UslugaComplex_Name||'»') as \"UslugaComplex_FullName\"
					from v_UslugaComplex
					where UslugaComplex_id = :UslugaComplex_id
					limit 1
				", array('UslugaComplex_id' => $data['UslugaComplex_id']));
				if ($UslugaComplex_FullName === false) {
					$this->createError('', 'Ошибка получения наименования услуги');
				}
			}

			$query = "
				select
					to_char (EvnR.Evn_setDT, 'YYYY-MM-DD HH24:MI') as \"Evn_setDT\"
					,to_char (EvnR.Evn_disDT, 'YYYY-MM-DD HH24:MI') as \"Evn_disDT\"
					,EvnR.EvnClass_SysNick as \"EvnClass_SysNick\"
					,EvnR.Evn_rid as \"Evn_rid\"
					,EvnR.Evn_id as \"Evn_id\"
				from  v_Evn Evn
				inner join v_Evn EvnR on EvnR.Evn_id = Evn.Evn_rid
				where Evn.Evn_id = :Evn_id
				limit 1
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
					select
						to_char (Evn.{$prefix}_disDT, 'YYYY-MM-DD HH24:MI') as \"Evn_disDT\",
						Evn.{$prefix}_isFinish as \"Evn_isFinish\"
					from v_{$prefix} Evn
					where Evn.{$prefix}_id = :Evn_id
                    limit 1
				", array('Evn_id' => $evn['Evn_id']));
				if ($evn === false) {
					return $this->createError('', 'Ошибка при получении родительского события услуги');
				}

				$evn['Evn_disDT'] = $resp['Evn_disDT'];
				$evn['Evn_isFinish'] = $resp['Evn_isFinish'];
			}

			$Evn_setDT = ConvertDateFormat($evn['Evn_setDT'], 'Y-m-d H:i');
			$Evn_disDT = ConvertDateFormat($evn['Evn_disDT'], 'Y-m-d H:i');

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
				EvnUslugaOperBrig_id as \"EvnUslugaOperBrig_id\",
				MSF.Person_Fio || ' - ' || post.name as \"MedStaffFact_Name\"
			from
				v_EvnUslugaOperBrig EUOB
				left join v_MedStaffFact MSF  on MSF.MedStaffFact_id = EUOB.MedStaffFact_id
				left join persis.v_post post  on MSF.post_id = post.id
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
				$data['EvnUslugaOper_setDate'] .= ' ' . $data['EvnUslugaOper_setTime'];
			}

			if ( isset($data['EvnUslugaOper_BallonBegTime']) ) {
				$data['EvnUslugaOper_BallonBegDate'] .= ' ' . $data['EvnUslugaOper_BallonBegTime'];
			}

			if ( isset($data['EvnUslugaOper_CKVEndTime']) ) {
				$data['EvnUslugaOper_CKVEndDate'] .= ' ' . $data['EvnUslugaOper_CKVEndTime'];
			}

			if (empty($data['EvnUslugaOper_pid']) && !empty($data['EvnDirection_id'])) {
				// если сохраняем из оперблока (по направлению), значит определим родителя - актуальное движение на дату выполнению услуги.
				$resp = $this->queryResult("
					select
						es.EvnSection_id as \"EvnSection_id\",
						es.Diag_id as \"Diag_id\",
						case when EvnSection_IsPriem = 2 then 1 else 0 end as \"EvnSection_IsPriem\"
					from
						v_EvnSection es
					where
						es.Lpu_id = :Lpu_id
						and es.Person_id = :Person_id
						and es.EvnSection_setDT <= :EvnUslugaOper_setDate and (es.EvnSection_disDT >= :EvnUslugaOper_setDate OR es.EvnSection_disDT IS NULL)
					order by EvnSection_IsPriem asc
					limit 1
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
						coalesce(EPL.EvnPL_IsPaid, EV.EvnVizit_IsPaid, ES.EvnSection_IsPaid, 1) as \"EvnUsluga_IsPaid\",
						coalesce(EPL.EvnPL_IndexRep, EV.EvnVizit_IndexRep, ES.EvnSection_IndexRep, 0) as \"EvnUsluga_IndexRep\",
						coalesce(EPL.EvnPL_IndexRepInReg, EV.EvnVizit_IndexRepInReg, ES.EvnSection_IndexRepInReg, 1) as \"EvnUsluga_IndexRepInReg\"
					from
						v_Evn e
						left join v_EvnVizit EV  on EV.EvnVizit_id = e.Evn_id
						left join v_EvnPL EPL  on EPL.EvnPL_id = e.Evn_pid
						left join v_EvnSection ES  on ES.EvnSection_id = e.Evn_id
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
				$data['EvnUslugaOper_disDate'] .= ' ' . $data['EvnUslugaOper_disTime'];
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
						select lu.LpuUnitType_SysNick  as "LpuUnitType_SysNick"
						from v_EvnSection es
						inner join v_LpuSection ls on ls.LpuSection_id = es.LpuSection_id
						inner join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
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
					select
						EvnDirection_pid as \"EvnDirection_pid\"
					from
						v_EvnDirection_all
					where
						EvnDirection_id = :EvnDirection_id
				", array(
					'EvnDirection_id' => $data['EvnDirection_id']
				), true);
			}

			$query = "
				SELECT
	                EvnUslugaOper_id as \"EvnUslugaOper_id\",
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\"
                FROM dbo." . $procedure . "(
                	EvnUslugaOper_id := :EvnUslugaOper_id,
					EvnUslugaOper_pid := :EvnUslugaOper_pid,
					EvnDirection_id := :EvnDirection_id,
					Lpu_id := :Lpu_id,
					Server_id := :Server_id,
					PersonEvn_id := :PersonEvn_id,
					EvnUslugaOper_setDT := :EvnUslugaOper_setDT,
					EvnUslugaOper_disDT := :EvnUslugaOper_disDT,
					PayType_id := :PayType_id,
					EvnUslugaOper_IsVMT := :EvnUslugaOper_IsVMT,
					EvnUslugaOper_IsMicrSurg := :EvnUslugaOper_IsMicrSurg,
					EvnUslugaOper_IsOpenHeart := :EvnUslugaOper_IsOpenHeart,
					EvnUslugaOper_IsArtCirc := :EvnUslugaOper_IsArtCirc,
					Usluga_id := :Usluga_id,
					UslugaComplex_id := :UslugaComplex_id,
					MedPersonal_id := :MedPersonal_id,
					MedStaffFact_id := :MedStaffFact_id,
					Morbus_id := :Morbus_id,
					UslugaPlace_id := :UslugaPlace_id,
					Lpu_uid := :Lpu_uid,
					LpuSection_uid := :LpuSection_uid,
					Org_uid := :Org_uid,
					EvnUslugaOper_Kolvo := :EvnUslugaOper_Kolvo,
					EvnUslugaOper_IsEndoskop := :EvnUslugaOper_IsEndoskop,
					EvnUslugaOper_IsLazer := :EvnUslugaOper_IsLazer,
					EvnUslugaOper_IsKriogen := :EvnUslugaOper_IsKriogen,
					EvnUslugaOper_IsRadGraf := :EvnUslugaOper_IsRadGraf,
					OperType_id := :OperType_id,
					OperDiff_id := :OperDiff_id,
					TreatmentConditionsType_id := :TreatmentConditionsType_id,
					EvnUslugaOper_CoeffTariff := :EvnUslugaOper_CoeffTariff,
					EvnUslugaOper_IsModern := :EvnUslugaOper_IsModern,
					MesOperType_id := :MesOperType_id,
					UslugaComplexTariff_id := :UslugaComplexTariff_id,
					DiagSetClass_id := :DiagSetClass_id,
					Diag_id := :Diag_id,
					EvnPrescrTimetable_id := null,
					EvnPrescr_id := :EvnPrescr_id,
					MedSpecOms_id := :MedSpecOms_id,
					LpuSectionProfile_id := :LpuSectionProfile_id,
					EvnUslugaOper_BallonBegDT := :EvnUslugaOper_BallonBegDT,
					EvnUslugaOper_CKVEndDT := :EvnUslugaOper_CKVEndDT,
					EvnUslugaOper_IsOperationDeath := :EvnUslugaOper_IsOperationDeath,
					EvnUslugaOper_Price := :EvnUslugaOper_Price,
					UslugaExecutionType_id := :UslugaExecutionType_id,
					UslugaExecutionReason_id := :UslugaExecutionReason_id,
					pmUser_id := :pmUser_id);
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
						euo.EvnPrescr_id as "EvnPrescr_id",
						ep.EvnPrescr_IsExec as "EvnPrescr_IsExec"
					from
						v_EvnUslugaOper euo
						left join v_EvnPrescr ep on ep.EvnPrescr_id = euo.EvnPrescr_id
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
				select
					EvnUsluga_id as \"EvnUsluga_id\"
				from
					v_EvnUsluga
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
				select
					ST.ServiceType_SysNick as \"ServiceType_SysNick\",
					ST.ServiceType_Name as \"ServiceType_Name\",
					PT.PayType_id as \"PayType_id\",
					PT.PayType_SysNick as \"PayType_SysNick\",
					to_char (EVPL.EvnVizitPL_setDate, 'YYYY-MM-DD HH24:MI:SS') as \"EvnVizitPL_setDate\"
				from
					v_EvnVizitPL EVPL
					left join v_ServiceType ST  on ST.ServiceType_id = EVPL.ServiceType_id
					left join v_PayType PT  on PT.PayType_id = EVPL.PayType_id
				where
					EvnVizitPL_id = :EvnUslugaCommon_pid
				limit 1
			", array('EvnUslugaCommon_pid' => $data['EvnUslugaCommon_pid']));
			if (!$resp) {
				throw new Exception('Ошибка при запросе посещения');
			}

			/*$query = "
				select top 1 UslugaComplex_Code
				from v_UslugaComplex
				where UslugaComplex_id in ({$in_UslugaComplex_list})
			";
			$result = $this->db->query($query);

			if (!is_object($result)) {
				throw new Exception('Ошибка при запросе кодов услуг');
			}

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
				select distinct
					uc.UslugaComplex_id as \"UslugaComplex_id\",
					uc.UslugaComplex_Code as \"UslugaComplex_Code\",
					uc.UslugaComplex_Name as \"UslugaComplex_Name\"
				from
					v_UslugaComplex uc
				where
					uc.UslugaComplex_Code in ('".implode("','", $UslugaComplex_Codes)."')
					and uc.UslugaComplex_id in ({$in_UslugaComplex_list})
                limit 2
			");
			if (count($resp_uc) > 1) {
				// сохраняем две услуги?
				throw new Exception("Добавление услуги {$resp_uc[0]['UslugaComplex_Code']} {$resp_uc[0]['UslugaComplex_Name']} недоступно. В случае лечения была выполнена другая услуга базовой программы ЭКО");
			} else if (count($resp_uc) > 0) {
				// если в движении есть ещё услуга ЭКО, то запрещаем добавлять ещё одну.
				$resp_eu = $this->queryResult("
					select
						euc.EvnUslugaCommon_id as \"EvnUslugaCommon_id\"
					from
						v_EvnUslugaCommon euc
						inner join v_UslugaComplex uc on uc.UslugaComplex_id = euc.UslugaComplex_id
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
				select distinct
					uc.UslugaComplex_id as \"UslugaComplex_id\",
					uc.UslugaComplex_Code as \"UslugaComplex_Code\",
					uc.UslugaComplex_Name as \"UslugaComplex_Name\"
				from
					v_UslugaComplex uc
				where
					uc.UslugaComplex_Code in ('".implode("','", $UslugaComplex_Codes)."')
					and uc.UslugaComplex_id in ({$in_UslugaComplex_list})
                limit 2
			");
			if (count($resp_uc) > 1) {
				// сохраняем две услуги?
				throw new Exception("КВС уже содержит услугу ЭКО, добавление новой услуги недоступно");
			} else if (count($resp_uc) > 0) {
				// если в движении есть ещё услуга ЭКО, то запрещаем добавлять ещё одну.
				$resp_eu = $this->queryResult("
					select
						euc.EvnUslugaCommon_id as \"EvnUslugaCommon_id\"
					from
						v_EvnUslugaCommon euc
						inner join v_UslugaComplex uc on uc.UslugaComplex_id = euc.UslugaComplex_id
					where
						euc.EvnUslugaCommon_pid = :EvnUslugaCommon_pid
						and uc.UslugaComplex_Code in ('".implode("','", $UslugaComplex_Codes)."')
						and (euc.EvnUslugaCommon_id <> :EvnUslugaCommon_id or :EvnUslugaCommon_id is null)
                    limit 1
				", $data);
				if (!empty($resp_eu[0]['EvnUslugaCommon_id'])) {
					throw new Exception("КВС уже содержит услугу ЭКО, добавление новой услуги недоступно");
				}
			}
		}

		if ($isPerm && !empty($in_UslugaComplex_list) && !empty($data['EvnUslugaCommon_pid']) && $data['ParentEvnClass_SysNick'] == 'EvnSection') {
			$resp = $this->getFirstRowFromQuery("
				select
					PT.PayType_id as \"PayType_id\",
					PT.PayType_SysNick as \"PayType_SysNick\",
					to_char (ES.EvnSection_setDate, 'YYYY-MM-DD HH24:MI:SS') as \"EvnSection_setDate\"
				from
					v_EvnSection ES
					left join v_PayType PT on PT.PayType_id = ES.PayType_id
				where
					EvnSection_id = :EvnUslugaCommon_pid
				limit 1
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
				select
					EvnVizitPL_id as \"EvnVizitPL_id\"
				from
					v_EvnVizitPL
				where
					EvnVizitPL_id = :EvnUslugaCommon_pid
					and UslugaComplex_id  in ({$in_UslugaComplex_list})
				limit 1
			", $data);
			if (!empty($EvnVizitPL_id)) {
				throw new Exception('Услуга сохранена как код посещения, сохранение невозможно');
			}

			// Если в посещении добавили две или больше одинаковых услуги на одну и тужу дату, то выводить ошибку. Исключение услуги из группы 301 (refs #38517)
			$EvnUslugaCommon_id = $this->getFirstResultFromQuery("
				select
					euc.EvnUslugaCommon_id as \"EvnUslugaCommon_id\"
				from
					v_EvnVizitPL  epl
					inner join v_EvnUslugaCommon euc  on euc.EvnUslugaCommon_pid = epl.EvnVizitPL_id
					inner join r66.v_UslugaComplexPartitionLink ucpl  on ucpl.UslugaComplex_id = euc.UslugaComplex_id
					inner join r66.v_UslugaComplexPartition ucp  on ucp.UslugaComplexPartition_id = ucpl.UslugaComplexPartition_id
				where
					epl.EvnVizitPL_id = :EvnUslugaCommon_pid
					and euc.UslugaComplex_id  in ({$in_UslugaComplex_list})
					and euc.EvnUslugaCommon_setDate = :EvnUslugaCommon_setDate
					and ucp.UslugaComplexPartition_Code <> '301'
					and (euc.EvnUslugaCommon_id <> :EvnUslugaCommon_id or :EvnUslugaCommon_id is null)
				limit 1
			", $data);
			if (!empty($EvnUslugaCommon_id)) {
				throw new Exception('Данная услуга уже добавлена, сохранение дубля невозможно');
			}

			// Если любая услуга отмечена SIGNRAO и нет в списке услуг движения услуг с UslugaComplexPartition_Name="Пребывание в РАО стационара", то выдавать ошибку, сохранение запретить
			$isSIGNRAO = false;

			$query = "
				(select
					UslugaComplex_id as \"UslugaComplex_id\"
				from
					r66.v_UslugaComplexPartitionLink
				where
					UslugaComplex_id  in ({$in_UslugaComplex_list})
					and UslugaComplexPartitionLink_Signrao = 1
				limit 1)

				union

				(select
					euc.UslugaComplex_id as \"UslugaComplex_id\"
				from
					v_EvnSection es
					inner join v_EvnUslugaCommon euc  on euc.EvnUslugaCommon_pid = es.EvnSection_id
					inner join r66.v_UslugaComplexPartitionLink ucpl  on ucpl.UslugaComplex_id = euc.UslugaComplex_id
				where
					es.EvnSection_id = :EvnUslugaCommon_pid
					and ucpl.UslugaComplexPartitionLink_Signrao = 1
					and (euc.EvnUslugaCommon_id <> :EvnUslugaCommon_id or :EvnUslugaCommon_id is null)
				limit 1)

				union

				(select
					mu.UslugaComplex_id as \"UslugaComplex_id\"
				from
					v_EvnSection es
					inner join v_MesUsluga mu  on mu.Mes_id = es.Mes_sid
					inner join r66.v_UslugaComplexPartitionLink ucpl  on ucpl.UslugaComplex_id = mu.UslugaComplex_id
				where
					es.EvnSection_id = :EvnUslugaCommon_pid
					and ucpl.UslugaComplexPartitionLink_Signrao = 1
				limit 1)
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
					select
						euc.EvnUslugaCommon_id as \"EvnUslugaCommon_id\"
					from
						v_EvnSection es
						inner join v_EvnUslugaCommon euc  on euc.EvnUslugaCommon_pid = es.EvnSection_id
						inner join r66.v_UslugaComplexPartitionLink ucpl  on ucpl.UslugaComplex_id = euc.UslugaComplex_id
						inner join r66.v_UslugaComplexPartition ucp  on ucp.UslugaComplexPartition_id = ucpl.UslugaComplexPartition_id
					where
						es.EvnSection_id = :EvnUslugaCommon_pid
						and ucp.UslugaComplexPartition_Code = '105'
						and (euc.EvnUslugaCommon_id <> :EvnUslugaCommon_id or :EvnUslugaCommon_id is null)
					limit 1
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
					coalesce(EPL.EvnPL_IsPaid, EV.EvnVizit_IsPaid, ES.EvnSection_IsPaid, 1) as \"EvnUsluga_IsPaid\",
					coalesce(EPL.EvnPL_IndexRep, EV.EvnVizit_IndexRep, ES.EvnSection_IndexRep, 0) as \"EvnUsluga_IndexRep\",
					coalesce(EPL.EvnPL_IndexRepInReg, EV.EvnVizit_IndexRepInReg, ES.EvnSection_IndexRepInReg, 1) as \"EvnUsluga_IndexRepInReg\"
				from
					v_Evn e
					left join v_EvnVizit EV on EV.EvnVizit_id = e.Evn_id
					left join v_EvnPL EPL on EPL.EvnPL_id = e.Evn_pid
					left join v_EvnSection ES on ES.EvnSection_id = e.Evn_id
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
		    WITH cte AS (
                SELECT
                    dbo.Age2(PS.Person_BirthDay, :Date) AS Person_Age,
                    datediff('day', PS.Person_BirthDay, :Date::timestamp) AS Person_AgeDays
                from v_PersonState PS
                where PS.Person_id = :Person_id
                limit 1
            )
			select
				count(UCT.UslugaComplexTariff_id) as \"Count\"
			from v_UslugaComplexTariff UCT
			where
				UCT.UslugaComplex_id = :UslugaComplex_id
				and UCT.PayType_id = :PayType_id
				and (
					((SELECT Person_Age FROM cte) >= 18 and UCT.MesAgeGroup_id = 1)
					or ((SELECT Person_Age FROM cte) < 18 and UCT.MesAgeGroup_id = 2)
					or ((SELECT Person_AgeDays FROM cte) > 28 and UCT.MesAgeGroup_id = 3)
					or ((SELECT Person_AgeDays FROM cte) <= 28 and UCT.MesAgeGroup_id = 4)
					or ((SELECT Person_Age FROM cte) < 18 and UCT.MesAgeGroup_id = 5)
					or ((SELECT Person_Age FROM cte) >= 18 and UCT.MesAgeGroup_id = 6)
					or ((SELECT Person_Age FROM cte) < 8 and UCT.MesAgeGroup_id = 7)
					or ((SELECT Person_Age FROM cte) >= 8 and UCT.MesAgeGroup_id = 8)
					or ((SELECT Person_AgeDays FROM cte) <= 90 and UCT.MesAgeGroup_id = 9)
					or (UCT.MesAgeGroup_id is NULL)
				)
				and UCT.UslugaComplexTariff_begDate <= :Date
				and (UCT.UslugaComplexTariff_endDate > :Date or UCT.UslugaComplexTariff_endDate is null)
			limit 1
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
			select PayType_id as \"PayType_id\"
			from v_PayType
			where PayType_SysNick = 'oms'
			limit 1
		");

		// надо проверить, что КСГ удовлетворяет новым датам
		if (false && empty($data['ignoreKSGCheck']) && !empty($data['EvnDiagPLStom_id'])) { // проверка неактуальна refs #125508
			$query = "
				select
					edps.Mes_id as \"Mes_id\",
					EU.EvnUslugaStom_setDate as \"EvnUslugaStom_setDate\",
					EU.EvnUslugaStom_disDate as \"EvnUslugaStom_disDate\",
					m.Mes_begDT as \"Mes_begDT\",
					m.Mes_endDT as \"Mes_endDT\"
				from
					v_EvnDiagPLStom edps
					inner join v_MesOld m on m.Mes_id = edps.Mes_id
					LEFT JOIN LATERAL(
						select
							MIN(EvnUslugaStom_setDate) as EvnUslugaStom_setDate,
							MAX(COALESCE(EvnUslugaStom_disDate, EvnUslugaStom_setDate)) as EvnUslugaStom_disDate
						from
							v_EvnUslugaStom
						where
							EvnDiagPLStom_id = edps.EvnDiagPLStom_id
					) EU ON true
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
					select EvnVizitPLStom_id as \"EvnVizitPLStom_id\"
					from v_EvnVizitPLStom
					where EvnVizitPLStom_id = :EvnUslugaStom_pid
						and UslugaComplex_id in ({$in_UslugaComplex_list})
				    limit 1
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
					select sum(coalesce(eus.EvnUslugaStom_Summa, 0)) as \"EvnUslugaStom_SummaOms\"
					from v_EvnVizitPLStom evpls
						inner join v_EvnUslugaStom eus on eus.EvnUslugaStom_rid = evpls.EvnVizitPLStom_pid
					where evpls.EvnVizitPLStom_id = :EvnUslugaStom_pid
						and eus.PayType_id = :omsPayTypeId
						and eus.EvnUslugaStom_id != COALESCE(cast(:EvnUslugaStom_id as bigint), 0)
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
						EPLS.EvnPLStom_id as \"EvnPLStom_id\"
					from
						v_EvnPLStom EPLS
						left join v_EvnVizitPLStom EVPLS on EVPLS.EvnVizitPLStom_rid = EPLS.EvnPLStom_id
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
					select
						PT.PayType_id as \"PayType_id\",
						PT.PayType_SysNick as \"PayType_SysNick\",
						to_char (EVPL.EvnVizitPLStom_setDate, 'YYYY-MM-DD HH24:MI:SS') as \"EvnVizitPLStom_setDate\"
					from
						v_EvnVizitPLStom EVPL
						left join v_PayType PT on PT.PayType_id = EVPL.PayType_id
					where
						EvnVizitPLStom_id = :EvnUslugaStom_pid
					limit 1
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
							select
								uca.UslugaComplexAttribute_id as \"UslugaComplexAttribute_id\"
							from
								v_UslugaComplexAttribute uca
								inner join v_UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
							where
								uca.UslugaComplex_id = :UslugaComplex_id
								and ucat.UslugaComplexAttributeType_SysNick = 'parondontogram'
							limit 1
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
							select uca.UslugaComplexAttribute_Value as \"UslugaComplexAttribute_Value\"
							from v_UslugaComplexAttribute uca
								inner join v_UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
							where uca.UslugaComplex_id = :UslugaComplex_id
								and ucat.UslugaComplexAttributeType_SysNick = 'uslugatype'
								and uca.UslugaComplexAttribute_Value in ('01', '02', '03')
							limit 1
						", array(
							'UslugaComplex_id' => $data['UslugaComplex_id']
						), true);

						if ( $uslugaTypeValue == '03' ) {
							$ServiceType_SysNick = $this->getFirstResultFromQuery("
								select st.ServiceType_SysNick as \"ServiceType_SysNick\"
								from v_EvnVizitPLStom evpls
									inner join v_ServiceType st on st.ServiceType_id = evpls.ServiceType_id
								where evpls.EvnVizitPLStom_id = :EvnVizitPLStom_id
								limit 1
							", array(
								'EvnVizitPLStom_id' => $data['EvnUslugaStom_pid']
							), true);

							if ( !empty($ServiceType_SysNick) && $ServiceType_SysNick != 'neotl' && $ServiceType_SysNick != 'polnmp' ) {
								$uslugaTypeValue = '01';
							}
						}

						if ( $uslugaTypeValue == '01' ) {
							$EvnPLStom_NumCard = $this->getFirstResultFromQuery("
								WITH cte1 AS (
                                    SELECT
                                        cast(:EvnUslugaStom_setDate as timestamp) AS EvnUslugaStom_setDate,
                                        cast(:EvnDiagPLStom_id as bigint) AS EvnDiagPLStom_id
                                ),
                                cte2 AS (
                                        select
                                                        d.Diag_pid AS Diag_pid,
                                                        edpls.EvnDiagPLStom_rid AS EvnDiagPLStom_rid
                                                    from v_EvnDiagPLStom edpls
                                                        inner join v_Diag d on d.Diag_id = edpls.Diag_id
                                                    where edpls.EvnDiagPLStom_id = :EvnDiagPLStom_id
                                                    limit 1
                                ),
                                cte3 AS (
										SELECT
                                        	date_part('month', CAST(:EvnUslugaStom_setDate as date)) AS \"Month\",
                                        	date_part('year', CAST(:EvnUslugaStom_setDate as date)) AS \"Year\"
                                ),
								epls as (
									select
										EvnPLStom_id,
										EvnPLStom_NumCard
									from v_EvnPLStom
									where Person_id = :Person_id
										and Lpu_id = :Lpu_id
										and EvnPLStom_IsFinish = 2
										and EvnPLStom_id != (SELECT EvnDiagPLStom_rid FROM cte1)
										and date_part('year', EvnPLStom_disDT) = (SELECT \"Year\" FROM cte3)
										and date_part('month', EvnPLStom_disDT) = (SELECT \"Month\" FROM cte3)
								)

								select
									EvnPLStom_NumCard as \"EvnPLStom_NumCard\"
								from (
									(select
										epls.EvnPLStom_NumCard
									from v_EvnUslugaStom eus
										inner join v_EvnDiagPLStom edpls on edpls.EvnDiagPLStom_id = eus.EvnDiagPLStom_id
										inner join v_Diag d  on d.Diag_id = edpls.Diag_id
										inner join v_EvnVizitPLStom evpls on evpls.EvnVizitPLStom_id = eus.EvnUslugaStom_pid
										inner join epls on epls.EvnPLStom_id = eus.EvnUslugaStom_rid
										inner join v_UslugaComplexAttribute uca on uca.UslugaComplex_id = eus.UslugaComplex_id
										inner join v_UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
									where d.Diag_pid = (SELECT Diag_pid FROM cte2)
										and edpls.EvnDiagPLStom_IsClosed = 2
										and ucat.UslugaComplexAttributeType_SysNick = 'uslugatype'
										and uca.UslugaComplexAttribute_Value = '01'
									limit 1)
	
									union all
									(select
										epls.EvnPLStom_NumCard
									from v_EvnUslugaStom eus
										inner join v_EvnDiagPLStom edpls on edpls.EvnDiagPLStom_id = eus.EvnDiagPLStom_id
										inner join v_Diag d  on d.Diag_id = edpls.Diag_id
										inner join v_EvnVizitPLStom evpls on evpls.EvnVizitPLStom_id = eus.EvnUslugaStom_pid
										inner join epls on epls.EvnPLStom_id = eus.EvnUslugaStom_rid
										inner join v_ServiceType st on st.ServiceType_id = evpls.ServiceType_id
										inner join v_UslugaComplexAttribute uca on uca.UslugaComplex_id = eus.UslugaComplex_id
										inner join v_UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
									where d.Diag_pid = (SELECT Diag_pid FROM cte2)
										and edpls.EvnDiagPLStom_IsClosed = 2
										and st.ServiceType_SysNick != 'neotl'
										and st.ServiceType_SysNick != 'polnmp'
										and ucat.UslugaComplexAttributeType_SysNick = 'uslugatype'
										and uca.UslugaComplexAttribute_Value = '03'
									limit 1)
								) t
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
						SELECT EvnClass_SysNick as "EvnClass_SysNick"
						FROM v_Evn
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
			$data['EvnUslugaStom_setDate'] .= ' ' . $data['EvnUslugaStom_setTime'];
		}

		if ( !empty($data['EvnUslugaStom_disDate']) && !empty($data['EvnUslugaStom_disTime']) ) {
			$data['EvnUslugaStom_disDate'] .= ' ' . $data['EvnUslugaStom_disTime'];
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
			select
            	EvnUslugaStom_id as \"EvnUslugaStom_id\", -- WAS LIKE : EvnUslugaStom_pid as \"EvnUslugaStom_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			FROM dbo.p_EvnUslugaStom_" . $action . "(
				EvnUslugaStom_id := :EvnUslugaStom_id,
				EvnUslugaStom_pid := :EvnUslugaStom_pid,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnUslugaStom_setDT := :EvnUslugaStom_setDT,
				EvnUslugaStom_disDT := :EvnUslugaStom_disDT,
				PayType_id := :PayType_id,
				Usluga_id := :Usluga_id,
				UslugaComplex_id := :UslugaComplex_id,
				LpuDispContract_id := :LpuDispContract_id,
				MedPersonal_id := :MedPersonal_id,
				MedStaffFact_id := :MedStaffFact_id,
				MedPersonal_sid := :MedPersonal_sid,
				UslugaPlace_id := :UslugaPlace_id,
				LpuSection_uid := :LpuSection_uid,
				LpuSectionProfile_id := :LpuSectionProfile_id,
				EvnUslugaStom_Kolvo := :EvnUslugaStom_Kolvo,
				EvnUslugaStom_UED := :EvnUslugaStom_UED,
				EvnUslugaStom_UEM := :EvnUslugaStom_UEM,
				EvnUslugaStom_Price := :EvnUslugaStom_Price,
				EvnUslugaStom_Summa := :EvnUslugaStom_Summa,
				UslugaComplexTariff_id := :UslugaComplexTariff_id,
				EvnUslugaStom_SumUL := :EvnUslugaStom_SumUL,
				EvnUslugaStom_SumUR := :EvnUslugaStom_SumUR,
				EvnUslugaStom_SumDL := :EvnUslugaStom_SumDL,
				EvnUslugaStom_SumDR := :EvnUslugaStom_SumDR,
				EvnPrescrTimetable_id := null,
				EvnPrescr_id := null,
				EvnUslugaStom_IsVizitCode := :EvnUslugaStom_IsVizitCode,
				EvnDiagPLStom_id := :EvnDiagPLStom_id,
				EvnUslugaStom_IsMes := :EvnUslugaStom_IsMes,
				EvnUslugaStom_IsAllMorbus := :EvnUslugaStom_IsAllMorbus,
				BlackCariesClass_id := :BlackCariesClass_id,
				pmUser_id := :pmUser_id);
		";
		$queryParams = array(
			'EvnUslugaStom_id' => (!( empty($data['EvnUslugaStom_id']) || $data['EvnUslugaStom_id'] <= 0 ) ? $data['EvnUslugaStom_id'] : NULL),
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
			select Parodontogram_id as \"Parodontogram_id\"
			from v_Parodontogram
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
			WITH cte AS (
            	SELECT :UslugaComplex_id AS res
            )
            select
            	UslugaComplexList_id as \"UslugaComplex_id\",
            	Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			FROM dbo." . $procedure . "(
			    UslugaComplexList_id := (SELECT res FROM cte),
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				UslugaComplex_ACode := null,
				UslugaComplex_Code := :UslugaComplex_Code,
				UslugaComplex_Name := :UslugaComplex_Name,
				pmUser_id := :pmUser_id);
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
            select
            	UslugaComplex_id as \"UslugaComplexList_id\",
            	Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			FROM dbo." . $procedure . "(
				UslugaComplex_id := :UslugaComplex_id,
				Server_id := :Server_id,
				Usluga_id := :Usluga_id,
				UslugaClass_id := :UslugaClass_id,
				pmUser_id := :pmUser_id);
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
		$query = "
			select
				EUO.Person_id as \"Person_id\",
				EUO.EvnUslugaOper_id as \"EvnUslugaOper_id\",
				0 as \"Children_Count\",
				EUO.EvnUslugaOper_id as \"SurgicalList_id\",
				to_char (EUO.EvnUslugaOper_setDT, 'dd.mm.yyyy') as \"EvnUslugaOper_setDate\",
				RTRIM(COALESCE(Lpu.Lpu_Nick, '')) as \"Lpu_Nick\",
				coalesce(Usluga.Usluga_Code,UC.UslugaComplex_Code) as \"Usluga_Code\",
				coalesce(Usluga.Usluga_Name,UC.UslugaComplex_Name) as \"Usluga_Name\"
			from v_EvnUslugaOper EUO
				left join v_Usluga Usluga  on Usluga.Usluga_id = EUO.Usluga_id
				left join v_UslugaComplex UC  on UC.UslugaComplex_id = EUO.UslugaComplex_id
				inner join v_Lpu Lpu  on Lpu.Lpu_id = EUO.Lpu_id
			where
				EUO.Person_id = :Person_id
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
					 JawPartType_id as \"JawPartType_id\"
					,Parodontogram_NumTooth as \"Parodontogram_NumTooth\"
					,ToothStateType_id as \"ToothStateType_id\"
				from v_Parodontogram
				where EvnUslugaStom_id = :EvnUslugaStom_id
			";
		}
		else {
			// Добавить учет категории и кода услуги
			$query = "
				select
					 JawPartType_id as \"JawPartType_id\"
					,Parodontogram_NumTooth as \"Parodontogram_NumTooth\"
					,ToothStateType_id as \"ToothStateType_id\"
				from v_Parodontogram
				where EvnUslugaStom_id = (
					select t1.EvnUslugaStom_id
					from v_EvnUslugaStom t1
						inner join v_UslugaComplex t2 on t2.UslugaComplex_id = t1.UslugaComplex_id
						inner join v_UslugaCategory t3 on t3.UslugaCategory_id = t2.UslugaCategory_id
					where t1.Person_id = :Person_id
						and (
							(t3.UslugaCategory_SysNick = 'tfoms' and t2.UslugaComplex_Code = '02180212')
							or (t3.UslugaCategory_SysNick in ('gost2004', 'gost2011') and replace(t2.UslugaComplex_Code, '.', '') = 'A0207009')
                    limit 1
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
									v_EvnLabSample
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
									EvnLabSample
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
									v_EvnLabSample
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
									EvnLabSample
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
									v_EvnLabSample
								where
									EvnLabRequest_id = lr.EvnLabRequest_id
										and EvnLabSample_StudyDT is not null
							) as studied_count,
							(
								select
									count(*)
								from
									v_EvnLabSample
								where
									EvnLabRequest_id = lr.EvnLabRequest_id
										and EvnLabSample_SetDT is not null
							) as setted_count,
							(
								select
									count(app_els.EvnLabSample_id)
								from
									v_EvnLabSample app_els
									inner join v_UslugaTest ut on ut.EvnLabSample_id = app_els.EvnLabSample_id
								where
									app_els.EvnLabRequest_id = lr.EvnLabRequest_id
									and coalesce(ut.UslugaTest_ResultApproved, 1) = 2
							) as approved_count
						from
							v_EvnDirection_all d
							left join v_EvnLabRequest lr on lr.EvnDirection_id = d.EvnDirection_id
							left join v_EvnUslugaPar eu on eu.EvnDirection_id = d.EvnDirection_id
						where
							d.MedService_id = :MedService_id
					) a
					left join v_UslugaComplex uc on uc.UslugaComplex_id = a.UslugaComplex_id
					left join v_PayType pt on pt.PayType_id = a.PayType_id
					where
						(( studied_count <> 0 ) or ( setted_count <> 0 )) and
						( approved_count <> 0 ) and
						(coalesce(UslugaExecutionType_id,3) in (1,2))
                        and (:PayType_id is null or a.PayType_id = :PayType_id)
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
					lis.TestStat ts
				WHERE
					ts.TestStat_testDate >= cast(:begDate as date)
					AND ts.TestStat_testDate < interval '1 day' + cast(:endDate as date)
				GROUP BY ts.TestStat_analyzerCode, ts.TestStat_testCode, ts.ReagentNormRate_id
			)

			SELECT
				stat.TestStat_analyzerCode as \"analyzerCode\",
				(stat.TestStat_analyzerCode || ' \"' || coalesce(a.Analyzer_Name, 'Не найден') || '\"') as \"analyzerFullName\",
				stat.TestStat_testCode || ' ' ||
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
				LEFT JOIN lis.v_Analyzer a ON a.Analyzer_Code = stat.TestStat_analyzerCode
				LEFT JOIN v_UslugaComplex vuc ON vuc.UslugaComplex_Code = stat.TestStat_testCode
					AND vuc.UslugaCategory_id = 4
				LEFT JOIN lis.v_ReagentNormRate rnr ON rnr.ReagentNormRate_id = stat.ReagentNormRate_id
				LEFT JOIN lis.v_unit u ON u.unit_id = rnr.unit_id
				LEFT JOIN rls.v_DrugNomen dn ON dn.DrugNomen_id = rnr.DrugNomen_id
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
		if (!empty($data['isStom']) && $data['isStom'] == 2) {
			$params['LpuSection_id'] = $data['LpuSection_id'];

			if (!empty($data['onDate'])) {
				$filterDT = "
				  AND LSLSP.LpuSectionLpuSectionProfile_begDate <= :onDate
				  AND (LSLSP.LpuSectionLpuSectionProfile_endDate >= :onDate
				    OR LSLSP.LpuSectionLpuSectionProfile_endDate IS NULL)
				";
			}

			$query = "
				SELECT LSP.LpuSectionProfile_id   AS \"LpuSectionProfile_id\"
				     , LSP.LpuSectionProfile_Code AS \"LpuSectionProfile_Code\"
				     , LSP.LpuSectionProfile_Name AS \"LpuSectionProfile_Name\"
				     , RTRIM(CAST(LSP.LpuSectionProfile_Code AS VARCHAR(1))) || ' ' ||
				       LSP.LpuSectionProfile_Name AS \"LpuSectionProfile_FullName\"
				FROM v_LpuSectionLpuSectionProfile LSLSP
				         INNER JOIN v_LpuSectionProfile LSP
				                    ON LSP.LpuSectionProfile_id = LSLSP.LpuSectionProfile_id
				WHERE LSLSP.LpuSection_id = :LpuSection_id
				
				{$filterDT}
				
				UNION
				
				SELECT LSP.LpuSectionProfile_id   AS \"LpuSectionProfile_id\"
				     , LSP.LpuSectionProfile_Code AS \"LpuSectionProfile_Code\"
				     , LSP.LpuSectionProfile_Name AS \"LpuSectionProfile_Name\"
				     , RTRIM(CAST(LSP.LpuSectionProfile_Code AS VARCHAR(1))) || ' ' ||
				       LSP.LpuSectionProfile_Name AS \"LpuSectionProfile_FullName\"
				FROM v_LpuSection LS
				         INNER JOIN v_LpuSectionProfile LSP
				                    ON LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
				WHERE LS.LpuSection_id = :LpuSection_id
			";
		}
		// *** NGS: SELECT PROFILE SECTION OF A LPU FOR STOMATOLOGIES - END ***
		else {
			if (!empty($data['MedSpecOms_id'])) {
				if (false && getRegionNick() == 'penza' && !empty($data['isStom'])) { // refs #124034 контроль временно убран
					$LpuSectionProfileIds = $this->queryList("
					select LpuSectionProfile_id  as \"LpuSectionProfile_id\" from v_LpuSectionProfileMedSpecOms
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
					select
					    mso.MedSpec_id
					from dbo.v_MedSpecOms mso
					    inner join LSPMS 
					    on LSPMS.LpuSectionProfile_id = LSP.LpuSectionProfile_fedid and LSPMS.MedSpec_id = mso.MedSpec_id
					where mso.MedSpecOms_id = :MedSpecOms_id
					limit 1
				)";
				}
			} elseif (empty($data['LpuSection_id'])) {
				// если указано отделение, значит мы в своей МО, значит фильтр не нужен
				// исключаем профили, для которых нет специальностей
				$filter .= "and exists(
				select MedSpec_id
				from LSPMS
				where LSPMS.LpuSectionProfile_id = LSP.LpuSectionProfile_fedid
				limit 1
			)";
			}

			if (!empty($data['LpuSection_id'])) {
				$params['LpuSection_id'] = $data['LpuSection_id'];
				$filter .= "
				and exists(
					(select LS.LpuSection_id from v_LpuSection LS
					where LS.LpuSection_id = :LpuSection_id and LS.LpuSectionProfile_id = LSP.LpuSectionProfile_id
					limit 1)

					union

					(select LSLSP.LpuSection_id
					from v_LpuSectionLpuSectionProfile LSLSP
					where LSLSP.LpuSection_id = :LpuSection_id and LSLSP.LpuSectionProfile_id = LSP.LpuSectionProfile_id)
				)
			";
			}

			$query = "
            with LSPMS as (
				SELECT LpuSectionProfile_id, MedSpec_id
				FROM fed.LpuSectionProfileMedSpec
				where (LpuSectionProfileMedSpec_begDT <= :onDate or LpuSectionProfileMedSpec_begDT is null)
					and (LpuSectionProfileMedSpec_endDT >= :onDate or LpuSectionProfileMedSpec_endDT is null)
			)
			SELECT
				LSP.LpuSectionProfile_id AS \"LpuSectionProfile_id\",
				LSP.LpuSectionProfile_fedid AS \"LpuSectionProfile_fedid\",
				LSP.LpuSectionProfile_Code AS \"LpuSectionProfile_Code\",
				LSP.LpuSectionProfile_Name AS \"LpuSectionProfile_Name\",
				RTRIM(cast(LSP.LpuSectionProfile_Code as varchar(6))) || ' ' || LSP.LpuSectionProfile_Name as \"LpuSectionProfile_FullName\"
			FROM
				v_LpuSectionProfile LSP
			WHERE 1=1
				AND (LSP.LpuSectionProfile_begDT <= :onDate OR LSP.LpuSectionProfile_begDT is null)
				AND (LSP.LpuSectionProfile_endDT >= :onDate OR LSP.LpuSectionProfile_endDT is null)
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
					select LSP.LpuSectionProfile_fedid from dbo.v_LpuSectionProfile LSP
					inner join LSPMS
					on LSPMS.LpuSectionProfile_id = LSP.LpuSectionProfile_fedid and LSPMS.MedSpec_id = MSO.MedSpec_id
					where LSP.LpuSectionProfile_id = :LpuSectionProfile_id
				)";
		} else {
			// исключаем специальности, для которых нет профилей
			$filter .= "and exists(
				select LpuSectionProfile_id from LSPMS
				where LSPMS.MedSpec_id = MSO.MedSpec_id
			)";
		}

		$query = "
			with LSPMS as (
				SELECT LpuSectionProfile_id, MedSpec_id
				FROM fed.LpuSectionProfileMedSpec
				where (LpuSectionProfileMedSpec_begDT <= :onDate or LpuSectionProfileMedSpec_begDT is null)
					and (LpuSectionProfileMedSpec_endDT >= :onDate or LpuSectionProfileMedSpec_endDT is null)
			)
			select
				MSO.MedSpecOms_id as \"MedSpecOms_id\",
				MSO.MedSpec_id as \"MedSpec_fedid\",
				MSO.MedSpecOms_Code as \"MedSpecOms_Code\",
				MSO.MedSpecOms_Name as \"MedSpecOms_Name\",
				RTRIM(cast(MSO.MedSpecOms_Code as varchar)) || ' ' || MSO.MedSpecOms_Name as \"MedSpecOms_FullName\"
			from
				v_MedSpecOms MSO
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

	    $query = "
					select
						EvnUsluga_id as \"EvnUsluga_id\"
					from v_EvnUsluga
					where
						EvnPrescr_id = :EvnPrescr_id
					limit 1
				";
		return $resp = $this->queryResult($query, array(
			'EvnPrescr_id' => $data['EvnPrescr_id'],
		));
	}

	/**
	 * возмьем данные по существующим зубам для события_услуги
	 */
	function getToothNumEvnUsluga($data) {
		return $this->queryResult("
				select
					ToothNumEvnUsluga_id as \"ToothNumEvnUsluga_id\",
					ToothNumEvnUsluga_ToothNum as \"ToothNumEvnUsluga_ToothNum\"
				from v_ToothNumEvnUsluga
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
			select
            	Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			FROM dbo.p_ToothNumEvnUsluga_del(ToothNumEvnUsluga_id := :ToothNumEvnUsluga_id);
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
			select
            	ToothNumEvnUsluga_id as \"ToothNumEvnUsluga_id\",
            	Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			FROM dbo.p_ToothNumEvnUsluga_ins(
				EvnUsluga_id := :EvnUsluga_id,
				ToothNumEvnUsluga_ToothNum := :ToothNumEvnUsluga_ToothNum,
				pmUser_id := :pmUser_id);
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

			if (!empty($resp[0]) && empty($resp[0]['Error_Msg'])) {
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
							select
								ed.EvnDirection_id as \"EvnDirection_id\"
							from
								v_EvnPrescrDirection epd
								inner join v_EvnDirection_all ed on ed.EvnDirection_id = epd.EvnDirection_id
							where
								epd.EvnPrescr_id = :EvnPrescr_id and
                                COALESCE(ed.EvnStatus_id, 16) not in (12, 13) -- не отменено/отклонено
							limit 1
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
				EvnUsluga_id as \"EvnUsluga_id\"
				,EvnUsluga_id as \"Evn_id\"
				,EvnUsluga_pid as \"Evn_pid\"
				,EvnClass_id as \"EvnClass_id\"
				,to_char(EvnUsluga_setDT,'YYYY-MM-DD HH24:MI:SS') as \"Evn_setDT\"
				,to_char(EvnUsluga_disDT,'YYYY-MM-DD HH24:MI:SS') as \"Evn_disDT\"
				,UslugaComplex_id as \"UslugaComplex_id\"
			from
				v_EvnUsluga
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
			$where .= " and to_char(eu.EvnUslugaOper_setDT,'YYYY-MM-DD HH24:MI:SS') = :Evn_setDT";
		}
		if(!empty($data['UslugaComplex_id'])){
			$where .= " and eu.UslugaComplex_id = :UslugaComplex_id";
		}

		$query = "
			select
				eu.EvnUslugaOper_id as \"EvnUsluga_id\"
				,eu.EvnUslugaOper_id as \"EvnUslugaOper_id\"
				,eu.EvnUslugaOper_id as \"Evn_id\"
				,eu.EvnUslugaOper_pid as \"Evn_pid\"
				,eu.EvnClass_id as \"EvnClass_id\"
				,to_char(eu.EvnUslugaOper_setDT,'YYYY-MM-DD HH24:MI:SS') as \"Evn_setDT\"
				,to_char(eu.EvnUslugaOper_disDT,'YYYY-MM-DD HH24:MI:SS') as \"Evn_disDT\"
				,eu.UslugaPlace_id as \"UslugaPlace_id\"
				,eu.LpuSection_uid as \"LpuSection_id\"
				,eu.Lpu_id as \"Lpu_id\"
				,eu.Org_uid as \"Org_id\"
				,eu.LpuSectionProfile_id as \"LpuSectionProfile_id\"
				,eu.MedSpecOms_id as \"MedSpecOms_id\"
				,eu.MedStaffFact_id as \"MedStaffFact_id\"
				,eu.PayType_id as \"PayType_id\"
				,eu.EvnPrescr_id as \"EvnPrescr_id\"
				,eu.DiagSetClass_id as \"DiagSetClass_id\"
				,eu.Diag_id as \"Diag_id\"
				,u.UslugaCategory_id as \"UslugaCategory_id\"
				,eu.UslugaComplex_id as \"UslugaComplex_id\"
				,eu.EvnUslugaOper_Price as \"EvnUsluga_Price\"
				,eu.OperType_id as \"OperType_id\"
				,eu.OperDiff_id as \"OperDiff_id\"
				,eu.TreatmentConditionsType_id as \"TreatmentConditionsType_id\"
				,case when eu.EvnUslugaOper_IsVMT > 1 then 1 else 0 end as \"EvnUslugaOper_IsVMT\"
				,case when eu.EvnUslugaOper_IsMicrSurg > 1 then 1 else 0 end as \"EvnUslugaOper_IsMicrSurg\"
				,case when eu.EvnUslugaOper_IsOpenHeart > 1 then 1 else 0 end as \"EvnUslugaOper_IsOpenHeart\"
				,case when eu.EvnUslugaOper_IsArtCirc > 1 then 1 else 0 end as \"EvnUslugaOper_IsArtCirc\"
				,case when eu.EvnUslugaOper_IsEndoskop > 1 then 1 else 0 end as \"EvnUslugaOper_IsEndoskop\"
				,case when eu.EvnUslugaOper_IsLazer > 1 then 1 else 0 end as \"EvnUslugaOper_IsLazer\"
				,case when eu.EvnUslugaOper_IsKriogen > 1 then 1 else 0 end as \"EvnUslugaOper_IsKriogen\"
				,case when eu.EvnUslugaOper_IsRadGraf > 1 then 1 else 0 end as \"EvnUslugaOper_IsRadGraf\"
				,eu.EvnUslugaOper_Kolvo as \"EvnUsluga_Kolvo\"
			from
				v_EvnUslugaOper eu
				left join v_Usluga u on eu.Usluga_id = u.Usluga_id
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
				eu.EvnUsluga_id as \"EvnUsluga_id\"
				,eu.EvnUsluga_id as \"Evn_id\"
				,eu.EvnUsluga_pid as \"Evn_pid\"
				,eu.EvnClass_id as \"EvnClass_id\"
				,to_char(eu.EvnUsluga_setDT,'YYYY-MM-DD HH24:MI:SS') as \"Evn_setDT\"
				,to_char(eu.EvnUsluga_disDT,'YYYY-MM-DD HH24:MI:SS') as \"Evn_disDT\"
				,eu.UslugaPlace_id as \"UslugaPlace_id\"
				,eu.LpuSection_uid as \"LpuSection_id\"
				,eu.Lpu_id as \"Lpu_id\"
				,eu.Org_uid as \"Org_id\"
				,eu.LpuSectionProfile_id as \"LpuSectionProfile_id\"
				,eu.MedSpecOms_id as \"MedSpecOms_id\"
				,eu.MedStaffFact_id as \"MedStaffFact_id\"
				,eu.PayType_id as \"PayType_id\"
				,eu.EvnPrescr_id as \"EvnPrescr_id\"
				,eu.DiagSetClass_id as \"DiagSetClass_id\"
				,eu.Diag_id as \"Diag_id\"
				,u.UslugaCategory_id as \"UslugaCategory_id\"
				,eu.UslugaComplex_id as \"UslugaComplex_id\"
				,eu.EvnUsluga_Price as \"EvnUsluga_Price\"
				,eu.EvnUsluga_Kolvo as \"EvnUsluga_Kolvo\"
				,eu.EvnUsluga_Summa as \"EvnUsluga_Summa\"
			from
				v_EvnUsluga eu
				left join v_Usluga u on eu.Usluga_id = u.Usluga_id
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
				e.PersonEvn_id as \"PersonEvn_id\",
				e.Server_id as \"Server_id\",
				e.Person_id as \"Person_id\",
				e.Lpu_id as \"Lpu_id\"
			from
				v_Evn e
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
				eu.EvnUsluga_id as \"EvnUsluga_id\",
				ec.EvnClass_SysNick as \"EvnClass_SysNick\",
				uc.UslugaComplex_Name as \"UslugaComplex_Name\",
				to_char (eu.EvnUsluga_setDT, 'dd.mm.yyyy') as \"EvnUsluga_setDate\",
				eu.EvnUsluga_Count as \"EvnUsluga_Count\",
				eu.EvnUsluga_Kolvo as \"EvnUsluga_Kolvo\",
				eus.EvnDiagPLStom_id as \"EvnDiagPLStom_id\",
				ex.EvnXml_id as \"EvnXml_id\"
			from
				v_EvnUsluga eu
				left join v_EvnUslugaStom eus on eus.EvnUslugaStom_id = eu.EvnUsluga_id
				left join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
				left join v_EvnClass ec on ec.EvnClass_id = eu.EvnClass_id
				left join v_EvnXml ex on ex.Evn_id = eu.EvnUsluga_id
			where
				eu.EvnUsluga_pid = :EvnUsluga_pid and
                COALESCE(EU.EvnUsluga_IsVizitCode, 1) = 1
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
				msf.LpuSection_id as \"LpuSection_uid\",
				msf.LpuSectionProfile_id as \"LpuSectionProfile_id\"
			from
				v_MedStaffFact msf
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
				uc.UslugaCategory_id as \"UslugaCategory_id\",
				ucf.UslugaComplexTariff_id as \"UslugaComplexTariff_id\"
			from
				v_UslugaComplex uc
				left join v_UslugaComplexTariff ucf on ucf.UslugaComplex_id = uc.UslugaComplex_id
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
					EU.EvnClass_SysNick as \"EvnClass_SysNick\"
					,onkoucat.UslugaComplexAttributeType_SysNick as \"UslugaComplexAttributeType_SysNick\"
					,EU.Person_id as \"Person_id\"
					,EU.UslugaComplex_id as \"UslugaComplex_id\"
					,EU.EvnUsluga_pid as \"EvnUsluga_pid\"
					,to_char (EU.EvnUsluga_setDT, 'YYYY-MM-DD HH24:MI:SS') as \"EvnUsluga_setDT\"
					,to_char (EU.EvnUsluga_disDT, 'YYYY-MM-DD HH24:MI:SS') as \"EvnUsluga_disDT\"
				from v_EvnUsluga EU
					LEFT JOIN LATERAL (
						select UslugaComplexAttributeType_SysNick
						from v_UslugaComplexAttribute UCA
							inner join v_UslugaComplexAttributeType UCAT on UCAT.UslugaComplexAttributeType_id = UCA.UslugaComplexAttributeType_id
						where UCA.UslugaComplex_id = EU.UslugaComplex_id
							and UslugaComplexAttributeType_SysNick in ('XimLech','LuchLech','GormImunTerLech','XirurgLech')
                        limit 1
					) onkoucat ON true
				where EU.EvnUsluga_id = ?
			", array($id));

			$uc_check = $this->getFirstRowFromQuery("
				select EvnUsluga_id as \"EvnUsluga_id\", EvnClass_SysNick as \"EvnClass_SysNick\"
				from v_EvnUsluga
				where
					EvnClass_SysNick in ('EvnUslugaOnkoBeam', 'EvnUslugaOnkoChem', 'EvnUslugaOnkoGormun', 'EvnUslugaOnkoSurg', 'EvnUslugaOnkoNonSpec') and
					UslugaComplex_id = :UslugaComplex_id and
					Person_id = :Person_id and
					EvnUsluga_pid = :EvnUsluga_pid and
					cast(EvnUsluga_setDT as date) = cast(:EvnUsluga_setDT as date) and
					cast(coalesce(EvnUsluga_disDT,EvnUsluga_setDT) as date) = cast(:EvnUsluga_disDT as date)
                limit 1
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
				$Lpu_uid = $this->getFirstResultFromQuery("select Lpu_id  as \"Lpu_id\" from v_Lpu_all where Org_id = :Org_id", array('Org_id' => $usluga_data['Lpu_uid']));

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
			select EvnUslugaCommon_id  as \"EvnUslugaCommon_id\"
			from v_EvnUslugaCommon
			where
				UslugaComplex_id = :UslugaComplex_id and
				Person_id = :Person_id and
				EvnUslugaCommon_setDate <= :EvnUsluga_setDT and
				coalesce(EvnUslugaCommon_disDate, EvnUslugaCommon_setDate) >= :EvnUsluga_setDT
		    limit 1
		", array(
			'EvnUsluga_setDT' => $data['EvnUsluga_setDT'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'Person_id' => $data['Person_id'],
		));

		if ($uc_check != false) return array(array('EvnUslugaCommon_id' => -1));

		$evndata = $this->getFirstRowFromQuery("
			select Lpu_id as \"Lpu_id\",
                   MedStaffFact_id as \"MedStaffFact_id\",
                   LpuSection_id as \"LpuSection_id\",
                   MedPersonal_id as \"MedPersonal_id\",
                   LpuSectionProfile_id as \"LpuSectionProfile_id\"
            from v_evnvizitpl
            where evnvizitpl_id =:evn_id

            union all

            select Lpu_id as \"Lpu_id\",
                   MedStaffFact_id as \"MedStaffFact_id\",
                   LpuSection_id as \"LpuSection_id\",
                   MedPersonal_id as \"MedPersonal_id\",
                   LpuSectionProfile_id as \"LpuSectionProfile_id\"
            from v_evnsection
            where evnsection_id =:evn_id
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
			select PayType_id as \"PayType_id\"
			from v_PayType
			where PayType_SysNick = 'oms'
			limit 1
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
			select EvnUslugaOper_id  as \"EvnUslugaOper_id\"
			from v_EvnUslugaOper
			where
				UslugaComplex_id = :UslugaComplex_id and
				Person_id = :Person_id and
				EvnUslugaOper_setDate <= :EvnUsluga_setDT and
				coalesce(EvnUslugaOper_disDate, EvnUslugaOper_setDate) >= :EvnUsluga_setDT
		", array(
			'EvnUsluga_setDT' => $data['EvnUsluga_setDT'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'Person_id' => $data['Person_id'],
		));

		if ($uc_check != false) return array(array('EvnUslugaOper_id' => -1));

		$evndata = $this->getFirstRowFromQuery("
			select Lpu_id as \"Lpu_id\",
                   MedStaffFact_id as \"MedStaffFact_id\",
                   LpuSection_id as \"LpuSection_id\",
                   MedPersonal_id as \"MedPersonal_id\",
                   LpuSectionProfile_id as \"LpuSectionProfile_id\"
            from v_evnvizitpl
            where evnvizitpl_id =:evn_id
            union all
            select Lpu_id as \"Lpu_id\",
                   MedStaffFact_id as \"MedStaffFact_id\",
                   LpuSection_id as \"LpuSection_id\",
                   MedPersonal_id as \"MedPersonal_id\",
                   LpuSectionProfile_id as \"LpuSectionProfile_id\"
            from v_evnsection
            where evnsection_id =:evn_id
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
			select PayType_id as \"PayType_id\"
			from v_PayType
			where PayType_SysNick = 'oms'
			limit 1
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
                UslugaExecutionType_id as \"UslugaExecutionType_id\",
                UslugaExecutionType_Code as \"UslugaExecutionType_Code\",
                UslugaExecutionType_Name as \"UslugaExecutionType_Name\",
                pmUser_insID as \"pmUser_insID\",
                pmUser_updID as \"pmUser_updID\",
                UslugaExecutionType_insDT as \"UslugaExecutionType_insDT\",
                UslugaExecutionType_updDT as \"UslugaExecutionType_updDT\"
			from
				v_UslugaExecutionType
		", $data);
	}

	/**
	 * получаем названия услуг уже включенных в направление
	 */
	function getAllUslugaNameInIncludableDirection($direction_id) {
		$result = $this->queryResult("
				SELECT 
					UC.UslugaComplex_Name as \"UslugaComplex_Name\",
					ELR.EvnLabRequest_id as \"EvnLabRequest_id\"
				FROM v_EvnPrescrDirection EPD
				inner join v_EvnPrescr EP on EP.EvnPrescr_id = EPD.EvnPrescr_id
				inner join v_EvnLabRequest ELR on ELR.EvnDirection_id = EPD.EvnDirection_id
				inner join EvnPrescrLabDiag EPLD on EPLD.EvnPrescrLabDiag_id = EP.EvnPrescr_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EPLD.UslugaComplex_id
				where  EP.PrescriptionType_id = 11 and EPD.EvnDirection_id = :EvnDirection_id
			", array('EvnDirection_id' => $direction_id));
		if (empty($result) && !is_array($result) && count($result) == 0) {
			return $this->createError('','Ошибка при получении названия услуги');
		}

		return $result;
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
								v_Lpu Lpu
							WHERE
								Lpu_id = RootTable.Lpu_id), 1) = 2)
								AND	(coalesce(Patient.Person_IsEncrypHIV, 1) = 2
						)
						then Patient_PersonEncrypHIV.PersonEncrypHIV_Encryp
					else coalesce(Patient.Person_SurName, '-') || ' ' ||
				(case
					when Patient.Person_FirName is not null and length(Patient.Person_FirName) > 0
						then substring(Patient.Person_FirName, 1, 1) || '.'
					else '' end) ||
				(case
					when Patient.Person_SecName IS not null and length(Patient.Person_SecName) > 0
						then substring(Patient.Person_SecName, 1, 1) || '.'
					else '' end)
				end)                                                           as \"MarkerData_12\",
				date_part('year', (select curdate from x) - Patient.Person_BirthDay) as \"MarkerData_20\",
				PatientPersonCard.PersonCard_Code                              as \"MarkerData_52\",
				to_char ((select curdate from x), 'dd.mm.yyyy')             as \"MarkerData_70\",
				EvnLabRequest.EvnLabRequest_Ward                               as \"MarkerData_134\",
				to_char (EvnLabSample.EvnLabSample_setDT, 'dd.mm.yyyy')     as \"MarkerData_135\",
				EvnLabRequest.EvnLabRequest_UslugaName                         as \"MarkerData_136\",
				null                                                           as \"MarkerData_137\",
				EvnLabSample.MedPersonal_aid                                   as \"MedPersonal_aid\",
				EvnLabSample.Lpu_aid                                           as \"Lpu_aid\",
				:Evn_id                                                        as \"Evn_id\",
				EvnLabSample_LpuSectionA.LpuSection_Name                       as \"MarkerData_138\",
				EvnLabSample_LpuA.Lpu_Name                                     as \"MarkerData_139\"
			from
				v_EvnUslugaPar as RootTable
				left join v_PersonState as Patient  on Patient.Person_id = RootTable.Person_id
				left join v_PersonEncrypHIV as Patient_PersonEncrypHIV  on Patient_PersonEncrypHIV.Person_id = Patient.Person_id
				left join v_PersonCard as PatientPersonCard  on PatientPersonCard.Person_id = RootTable.Person_id and PatientPersonCard.LpuAttachType_id = 1
				left join v_EvnDirection_all as EvnUslugaPar_EvnDirection  on EvnUslugaPar_EvnDirection.EvnDirection_id = RootTable.EvnDirection_id
				left join v_EvnLabRequest as EvnLabRequest  on EvnLabRequest.EvnDirection_id = EvnUslugaPar_EvnDirection.EvnDirection_id
				LEFT JOIN LATERAL (
					select
						*
					from
						v_EvnLabSample
					where
						EvnLabRequest_id = EvnLabRequest.EvnLabRequest_id
                    limit 1
				) EvnLabSample ON true
				left join v_LpuSection as EvnLabSample_LpuSectionA  on EvnLabSample_LpuSectionA.LpuSection_id = EvnLabSample.LpuSection_aid
				left join v_Lpu as EvnLabSample_LpuA  on EvnLabSample_LpuA.Lpu_id = EvnLabSample.Lpu_aid
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
			select
				EvnPrescr_id as \"EvnPrescr_id\",
				EvnUsluga_id as \"EvnUsluga_id\",
				to_char (EvnUsluga_setDate, 'YYYY-MM-DD HH24:MI:SS') as \"EvnUsluga_setDate\"
			from
				v_EvnUsluga
			where
				EvnPrescr_id = :EvnPrescr_id
		    limit 1
		";

		return $this->queryResult($query, $data);
	}

	/**
	 * Поиск данных evn
	 */
	function getEvnParams($data) {
		$query = "
			select
				Evn_id as \"Evn_id\",
				Evn_pid as \"Evn_pid\",
				Evn_rid as \"Evn_rid\",
				EvnClass_id as \"EvnClass_id\",
				EvnClass_SysNick as \"EvnClass_SysNick\"
			from
				v_Evn
			where
				Evn_id = :Evn_id
			limit 1
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
				select
					EvnXml_id as \"EvnXml_id\"
				from v_EvnXml
				where Evn_id = :EvnUsluga_id
				order by EvnXml_insDT desc
				limit 1
			", $usl);

			if (!empty($xml)) {
				$data[$key]['EvnXml_id'] = $xml;
			}
		}

		return $data;
	}
}
