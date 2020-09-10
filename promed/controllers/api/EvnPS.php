<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnPS - контроллер API для работы с КВС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			28.11.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class EvnPS extends SwREST_Controller {
	protected $inputRules = array(
		'getEvnPS' => array(
			array('field' => 'EvnPS_id', 'label' => 'Идентификатор КВС', 'rules' => '', 'type' => 'id'),
			array('field' => 'Evn_id', 'label' => 'Идентификатор случая', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_NumCard', 'label' => 'Номер карты', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPS_setDT', 'label' => 'Дата поступления', 'rules' => '', 'type' => 'datetime'),
		),
		'getEvnSectionList' => array(
			array('field' => 'EvnPS_id', 'label' => 'Идентификатор КВС', 'rules' => 'required', 'type' => 'id'),
		),
		'getEvnDiagPSList' => array(
			array('field' => 'EvnSection_id', 'label' => 'Идентификатор КВС', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DiagSetClass_id', 'label' => 'Вид диагноза', 'rules' => '', 'type' => 'id'),
		),
		'getEvnSection' => array(
			array('field' => 'EvnSection_id', 'label' => 'Идентификатор движения', 'rules' => '', 'type' => 'id'),
			array('field' => 'Evn_id', 'label' => 'Идентификатор случая движения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_id', 'label' => 'Идентификатор КВС', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnSection_setDate', 'label' => 'Дата поступления', 'rules' => '', 'type' => 'date'),
			array('field' => 'Date_DT', 'label' => 'Дата', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnSection_IsPriem', 'label' => 'Признак приемного отделения', 'rules' => '', 'type' => 'api_flag'),
		),
		'getEvnDiagPS' => array(
			array('field' => 'EvnDiagPS_id', 'label' => 'Идентификатор сопутсвующего диагноза', 'rules' => 'required', 'type' => 'id'),
		),
		'createEvnPS' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnPS_IsCont', 'label' => 'Продолжение случая', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'EvnPS_NumCard', 'label' => 'Номер карты', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'PayType_id', 'label' => 'Тип оплаты', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnPS_setDT', 'label' => 'Дата и время поступления', 'rules' => 'required', 'type' => 'datetime'),
			array('field' => 'EvnPS_IsWithoutDirection', 'label' => 'С электронным направлением', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'EvnDirection_Num', 'label' => 'Номер направления', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnDirection_setDT', 'label' => 'Дата направления', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnPS_IsImperHosp', 'label' => 'Несвоевременность госпитализации', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'EvnPS_IsShortVolume', 'label' => 'Недостаточный объем оперативной помощи', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'EvnPS_IsWrongCure', 'label' => 'Неправильная тактика лечения', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'EvnPS_IsDiagMismatch', 'label' => 'Несовпадение диагноза', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'PrehospType_id', 'label' => 'Тип предварительной госпитализации', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PrehospDirect_id', 'label' => 'Кем направлен', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_did', 'label' => 'Направившее отделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_did', 'label' => 'Направившее МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'Org_did', 'label' => 'Направившая организация', 'rules' => '', 'type' => 'id'),
			array('field' => 'OrgMilitary_did', 'label' => 'Направивший военкомат', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrehospArrive_id', 'label' => 'Кем доставлен', 'rules' => '', 'type' => 'id'),
			array('field' => 'CmpCallCard_id', 'label' => 'Идентификатор карты вызова', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_CodeConv', 'label' => 'Кем доставлен (код)', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPS_NumConv', 'label' => 'Кем доставлен (номер наряда)', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPS_IsPLAmbulance', 'label' => 'Талон передан на ССМП', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'EvnPS_IsWaif', 'label' => 'Беспризорный', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'PrehospWaifArrive_id', 'label' => 'Кем доставлен, если беспризорный', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrehospWaifReason_id', 'label' => 'Причина помещения в ЛПУ, если беспризорный', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrehospToxic_id', 'label' => 'Состояние опьянения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_HospCount', 'label' => 'Количество госпитализаций', 'rules' => '', 'type' => 'int'),
			array('field' => 'Okei_id', 'label' => 'Единицы измерения для времени с начала заболевания', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnPS_TimeDesease', 'label' => 'Время с начала заболевания', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnPS_IsNeglectedCase', 'label' => 'Случай запущенный', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'PrehospTrauma_id', 'label' => 'Вид травмы', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_IsUnlaw', 'label' => 'Противоправная', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'EvnPS_IsUnport', 'label' => 'Нетранспортабельность', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'LpuSection_pid', 'label' => 'Приемное отделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedPersonal_pid', 'label' => 'Врач приемного отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_pid', 'label' => 'Основной диагноз приемного отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'DiagSetPhase_pid', 'label' => 'Фаза для диагнозов приемного', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_PhaseDescr_pid', 'label' => 'Описание фазы для диагноза приемного', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPS_IsPrehospAcceptRefuse', 'label' => 'Отказ в подтверждении госпитализации', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'EvnPS_OutcomeDT', 'label' => 'Дата исхода в приемном отделении', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuSection_eid', 'label' => 'Отделение для госпитализации', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionProfile_id', 'label' => 'Профиль отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaComplex_id', 'label' => 'Код посещения', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrehospWaifRefuseCause_id', 'label' => 'Отказ', 'rules' => '', 'type' => 'id'),
			array('field' => 'DeseaseType_id', 'label' => 'Характер', 'rules' => '', 'type' => 'id')
		),
		'updateEvnPS' => array(
			array('field' => 'EvnPS_id', 'label' => 'Идентификатор КВС', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_IsCont', 'label' => 'Продолжение случая', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'EvnPS_NumCard', 'label' => 'Номер карты', 'rules' => '', 'type' => 'string'),
			array('field' => 'PayType_id', 'label' => 'Тип оплаты', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_setDT', 'label' => 'Дата и время поступления', 'rules' => '', 'type' => 'datetime'),
			array('field' => 'EvnPS_IsWithoutDirection', 'label' => 'С электронным направлением', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'EvnDirection_Num', 'label' => 'Номер направления', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnDirection_setDT', 'label' => 'Дата направления', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnPS_IsImperHosp', 'label' => 'Несвоевременность госпитализации', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'EvnPS_IsShortVolume', 'label' => 'Недостаточный объем оперативной помощи', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'EvnPS_IsWrongCure', 'label' => 'Неправильная тактика лечения', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'EvnPS_IsDiagMismatch', 'label' => 'Несовпадение диагноза', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'PrehospType_id', 'label' => 'Тип предварительной госпитализации', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrehospDirect_id', 'label' => 'Кем направлен', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_did', 'label' => 'Направившее отделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_did', 'label' => 'Направившее МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'Org_did', 'label' => 'Направившая организация', 'rules' => '', 'type' => 'id'),
			array('field' => 'OrgMilitary_did', 'label' => 'Направивший военкомат', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrehospArrive_id', 'label' => 'Кем доставлен', 'rules' => '', 'type' => 'id'),
			array('field' => 'CmpCallCard_id', 'label' => 'Идентификатор карты вызова', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_CodeConv', 'label' => 'Кем доставлен (код)', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPS_NumConv', 'label' => 'Кем доставлен (номер наряда)', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPS_IsPLAmbulance', 'label' => 'Талон передан на ССМП', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'EvnPS_IsWaif', 'label' => 'Беспризорный', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'PrehospWaifArrive_id', 'label' => 'Кем доставлен, если беспризорный', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrehospWaifReason_id', 'label' => 'Причина помещения в ЛПУ, если беспризорный', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrehospToxic_id', 'label' => 'Состояние опьянения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_HospCount', 'label' => 'Количество госпитализаций', 'rules' => '', 'type' => 'int'),
			array('field' => 'Okei_id', 'label' => 'Единицы измерения для времени с начала заболевания', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_TimeDesease', 'label' => 'Время с начала заболевания', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnPS_IsNeglectedCase', 'label' => 'Случай запущенный', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'PrehospTrauma_id', 'label' => 'Вид травмы', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_IsUnlaw', 'label' => 'Противоправная', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'EvnPS_IsUnport', 'label' => 'Нетранспортабельность', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuSection_pid', 'label' => 'Приемное отделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedPersonal_pid', 'label' => 'Врач приемного отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_pid', 'label' => 'Основной диагноз приемного отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'DiagSetPhase_pid', 'label' => 'Фаза для диагнозов приемного', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_PhaseDescr_pid', 'label' => 'Описание фазы для диагноза приемного', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPS_IsPrehospAcceptRefuse', 'label' => 'Отказ в подтверждении госпитализации', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'EvnPS_OutcomeDT', 'label' => 'Дата исхода в приемном отделении', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuSection_eid', 'label' => 'Отделение для госпитализации', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionProfile_id', 'label' => 'Профиль отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaComplex_id', 'label' => 'Код посещения', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrehospWaifRefuseCause_id', 'label' => 'Отказ', 'rules' => '', 'type' => 'id'),
			array('field' => 'DeseaseType_id', 'label' => 'Характер', 'rules' => '', 'type' => 'id')
		),
		'createEvnSection' => array(
			array('field' => 'EvnPS_id', 'label' => 'Идентификатор КВС', 'rules' => 'required', 'type' => 'id'),
			/*array('field' => 'EvnSection_setDT', 'label' => 'Дата и время поступления', 'rules' => 'required', 'type' => 'datetime'),
			array('field' => 'EvnSection_disDT', 'label' => 'Дата и время выписки', 'rules' => '', 'type' => 'datetime'),*/
			array('field' => 'Evn_setDT', 'label' => 'Дата и время поступления', 'rules' => 'required', 'type' => 'datetime'),
			array('field' => 'Evn_disDT', 'label' => 'Дата и время выписки', 'rules' => '', 'type' => 'datetime'),
			array('field' => 'PayType_id', 'label' => 'Тип оплаты', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'TariffClass_id', 'label' => 'Вид тарифа', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSectionWard_id', 'label' => 'Палатная структура', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Врач', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Diag_id', 'label' => 'Основной диагноз', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DiagSetPhase_id', 'label' => 'Фаза основного диагноза', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnSection_PhaseDescr', 'label' => 'Описание фазы', 'rules' => '', 'type' => 'string'),
			array('field' => 'Mes_id', 'label' => 'МЭС', 'rules' => '', 'type' => 'id'),
			array('field' => 'Mes_sid', 'label' => 'КСГ', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionProfile_id', 'label' => 'Профиль', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LeaveType_id', 'label' => 'Исход госпитализации', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnLeave_UKL', 'label' => 'Уровень качества лечения', 'rules' => '', 'type' => 'int'),
			array('field' => 'ResultDesease_id', 'label' => 'Исход заболевания', 'rules' => '', 'type' => 'id'),
			array('field' => 'LeaveCause_id', 'label' => 'Причина выписки', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnLeave_IsAmbul', 'label' => 'Напрвлен на амбулаторное лечение', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'Org_oid', 'label' => 'Идентификатор МО для перевода', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedPersonal_did', 'label' => 'Врачь, установивший смерть', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDie_IsAnatom', 'label' => 'Необходимость экспертизы', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuUnitType_oid', 'label' => 'Тип стационара при переводе', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_oid', 'label' => 'Отделение для перевода', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionBedProfile_oid', 'label' => 'Профиль коек', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnSection_IsPriem', 'label' => 'Признак приемного отделения', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'DeseaseType_id', 'label' => 'Характер', 'rules' => '', 'type' => 'id')
		),
		'updateEvnSection' => array(
			array('field' => 'EvnSection_id', 'label' => 'Идентификатор движения', 'rules' => 'required', 'type' => 'id'),
			/*array('field' => 'EvnSection_setDT', 'label' => 'Дата и время поступления', 'rules' => '', 'type' => 'datetime'),
			array('field' => 'EvnSection_disDT', 'label' => 'Дата и время выписки', 'rules' => '', 'type' => 'datetime'),*/
			array('field' => 'Evn_setDT', 'label' => 'Дата и время поступления', 'rules' => '', 'type' => 'datetime'),
			array('field' => 'Evn_disDT', 'label' => 'Дата и время выписки', 'rules' => '', 'type' => 'datetime'),
			array('field' => 'PayType_id', 'label' => 'Тип оплаты', 'rules' => '', 'type' => 'id'),
			array('field' => 'TariffClass_id', 'label' => 'Вид тарифа', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionWard_id', 'label' => 'Палатная структура', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Врач', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_id', 'label' => 'Основной диагноз', 'rules' => '', 'type' => 'id'),
			array('field' => 'DiagSetPhase_id', 'label' => 'Фаза основного диагноза', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnSection_PhaseDescr', 'label' => 'Описание фазы', 'rules' => '', 'type' => 'string'),
			array('field' => 'Mes_id', 'label' => 'МЭС', 'rules' => '', 'type' => 'id'),
			array('field' => 'Mes_sid', 'label' => 'КСГ', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionProfile_id', 'label' => 'Профиль', 'rules' => '', 'type' => 'id'),
			array('field' => 'LeaveType_id', 'label' => 'Исход госпитализации', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnLeave_UKL', 'label' => 'Уровень качества лечения', 'rules' => '', 'type' => 'int'),
			array('field' => 'ResultDesease_id', 'label' => 'Исход заболевания', 'rules' => '', 'type' => 'id'),
			array('field' => 'LeaveCause_id', 'label' => 'Причина выписки', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnLeave_IsAmbul', 'label' => 'Напрвлен на амбулаторное лечение', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'Org_oid', 'label' => 'Идентификатор МО для перевода', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedPersonal_did', 'label' => 'Врачь, установивший смерть', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDie_IsAnatom', 'label' => 'Необходимость экспертизы', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuUnitType_oid', 'label' => 'Тип стационара при переводе', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_oid', 'label' => 'Отделение для перевода', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionBedProfile_oid', 'label' => 'Профиль коек', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnSection_IsPriem', 'label' => 'Признак приемного отделения', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'DeseaseType_id', 'label' => 'Характер', 'rules' => '', 'type' => 'id')
		),
		'createEvnDiagPS' => array(
			array('field' => 'EvnSection_id', 'label' => 'Идентификатор движения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDiagPS_setDate', 'label' => 'Дата установки', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'EvnDiagPS_setTime', 'label' => 'Время установки', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'DiagSetClass_id', 'label' => 'Вид диагноза', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DiagSetType_id', 'label' => 'Тип диагноза', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Diag_id', 'label' => 'Диагноз', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DiagSetPhase_id', 'label' => 'Фаза диагноза', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDiagPS_PhaseDescr', 'label' => 'Расшифровка', 'rules' => '', 'type' => 'string'),
		),
		'updateEvnDiagPS' => array(
			array('field' => 'EvnDiagPS_id', 'label' => 'Идентификатор сопутствующего диагноза', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDiagPS_setDate', 'label' => 'Дата установки', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnDiagPS_setTime', 'label' => 'Время установки', 'rules' => '', 'type' => 'string'),
			array('field' => 'DiagSetClass_id', 'label' => 'Вид диагноза', 'rules' => '', 'type' => 'id'),
			array('field' => 'DiagSetType_id', 'label' => 'Тип диагноза', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_id', 'label' => 'Диагноз', 'rules' => '', 'type' => 'id'),
			array('field' => 'DiagSetPhase_id', 'label' => 'Фаза диагноза', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDiagPS_PhaseDescr', 'label' => 'Расшифровка', 'rules' => '', 'type' => 'string'),
		),
		'getEvnPrescr' => array(
			array('field' => 'EvnPrescr_id', 'label' => 'идентификатор назначения', 'rules' => '', 'type' => 'id'),
			array('field' => 'Evn_pid', 'label' => 'идентификатор КВС/ТАП', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrescriptionType_id', 'label' => 'тип назначения', 'rules' => '', 'type' => 'int'),
		),
		'createEvnPrescr' => array(
			array('field' => 'Evn_pid', 'label' => 'идентификатор КВС/ТАП', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PrescriptionType_id', 'label' => 'тип назначения', 'rules' => 'required', 'type' => 'id'),			
			array('field' => 'PrescriptionRegimeType_id', 'label' => 'Тип режима', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrescriptionDietType_id', 'label' => 'Тип диеты', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescr_Descr', 'label' => 'комментарий к назначению', 'rules' => '', 'type' => 'string'),
			array('field' => 'Evn_Count', 'label' => 'Продолжать дней', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'Evn_setDT', 'label' => 'Начало выполнения', 'rules' => 'required', 'type' => 'date'),
		),
		'updateEvnPrescr' => array(
			array('field' => 'EvnPrescr_id', 'label' => 'идентификатор назначения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PrescriptionRegimeType_id', 'label' => 'Тип режима', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrescriptionDietType_id', 'label' => 'Тип диеты', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescr_Descr', 'label' => 'комментарий к назначению', 'rules' => '', 'type' => 'string'),
			array('field' => 'Evn_Count', 'label' => 'Продолжать дней', 'rules' => '', 'type' => 'int'),
			array('field' => 'Evn_setDT', 'label' => 'Начало выполнения', 'rules' => '', 'type' => 'date'),
		),
		'mGetEvnPSInfo' => array(
			array('field' => 'EvnPS_id', 'label' => 'Идентификатор КВС', 'rules' => 'required', 'type' => 'id')
		),
	);
	
	var $ignoreYesNoArr = array(
		'ignoreCheckEvnUslugaChange' => 1,
		'ignoreCheckEvnUslugaDates' => 1,
		'ignoreCheckKSGisEmpty' => 1,
		'ignoreCheckCardioFieldsEmpty' => 1,
		'ignoreDiagKSGCheck' => 1,
		'ignoreEvnDrug' => 1,
		'ignoreEvnPSDoublesCheck' => 1,
		'ignoreEvnUslugaKSGCheck' => 1,
		'ignoreEvnUslugaHirurgKSGCheck' => 1,
		'ignoreNotHirurgKSG' => 1,
		'ignoreMorbusOnkoDrugCheck' => 1
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnPS_model', 'dbmodel');
	}

	/**
	 * Получение данных КВС
	 */
	function index_get() {
		$data = $this->ProcessInputData('getEvnPS');
		
		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];

		$paramsExist = false;
		foreach ($data as $value) {
			if(!empty($value)){
				$paramsExist = true;
			}
		}
		if(!$paramsExist){
			$this->response(array(
				'error_msg' => 'Не передан ни один параметр',
				'error_code' => '6',
				'data' => ''
			));
		}

		$resp = $this->dbmodel->getEvnPSForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Добавление данных о КВС
	 */
	function index_post() {
		$data = $this->ProcessInputData('createEvnPS', null, true, true, true);
		
		$sp = getSessionParams();
		if ( $data['Lpu_id'] != $sp['Lpu_id'] ) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}

		$personInfo = $this->dbmodel->getFirstRowFromQuery("
			select top 1
				PS.PersonEvn_id,
				PS.Server_id
			from v_PersonState PS with(nolock)
			where PS.Person_id = :Person_id
		", $data);
		if (!is_array($personInfo)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$data = array_merge($data, $personInfo);

		if (!empty($data['MedPersonal_pid']) && !empty($data['LpuSection_pid'])) {
			$data['MedStaffFact_pid'] = $this->dbmodel->getFirstResultFromQuery("
				declare @dt datetime = :EvnPS_setDT
				select MedStaffFact_id 
				from v_MedStaffFact MSF with(nolock)
				where MSF.MedPersonal_id = :MedPersonal_pid 
				and MSF.WorkData_begDate <= @dt and isnull(MSF.WorkData_endDate, @dt) >= @dt
			", $data, true);
			if ($data['MedStaffFact_pid'] === false) {
				$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			}
		}
		
		$Diag_Code = null;
		if(!empty($data['Diag_pid'])){
			$Diag_Code = $this->dbmodel->getFirstResultFromQuery("select top 1 Diag_Code from v_Diag with (nolock) where Diag_id = :Diag_id", array(
				'Diag_id' => $data['Diag_pid']
			));
			$diagFlag = TRUE;
			if(substr($Diag_Code, 0, 3) >= 'Z00' && substr($Diag_Code, 0, 3) <= 'Z99') {
				$diagFlag = FALSE;
			}
			if(empty($data['DeseaseType_id']) && $diagFlag && !empty($data['EvnPS_OutcomeDT']) && $data['EvnPS_OutcomeDT']>='2018-11-01' && !empty($data['PrehospWaifRefuseCause_id'])){
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Не передан обязательный параметр DeseaseType_id'
				));
			}
		}

		$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		$data['ignoreEvnPSDoublesCheck'] = 1;

		$data['EvnPS_setDate'] = date_create($data['EvnPS_setDT'])->format('Y-m-d');
		$data['EvnPS_setTime'] = date_create($data['EvnPS_setDT'])->format('H:i');

		$data['EvnDirection_setDate'] = $data['EvnDirection_setDT'];
		$data = array_merge($data, $this->ignoreYesNoArr);
		$resp = $this->dbmodel->doSave($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp['Error_Msg']
			));
		}

		if ($data['EvnPS_IsPrehospAcceptRefuse'] == 2) {
			$this->dbmodel->setEvnPSPrehospAcceptRefuse(array(
				'EvnPS_id' => $resp['EvnPS_id'],
				'EvnPS_IsPrehospAcceptRefuse' => $data['EvnPS_IsPrehospAcceptRefuse'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		$response = array(
			'error_code' => 0,
			'data' => array(
				array('EvnPS_id' => $resp['EvnPS_id'])
			)
		);

		if (!empty($resp['EvnSectionPriem_id'])) {
			$response['data'][0]['EvnSection_id'] = $resp['EvnSectionPriem_id'];
		}

		$this->response($response);
	}

	/**
	 * Добавление данных о КВС
	 */
	function index_put() {
		$data = $this->ProcessInputData('updateEvnPS', null, true, true, true);

		foreach($data as $key => $value) {
			if (empty($value) && !array_key_exists($key, $this->_args)) {
				unset($data[$key]);
			}
		}
		if (!empty($data['Person_id'])) {
			$personInfo = $this->dbmodel->getFirstRowFromQuery("
				select top 1
					PS.PersonEvn_id,
					PS.Server_id
				from v_PersonState PS with(nolock)
				where PS.Person_id = :Person_id
			", $data);
			if (!is_array($personInfo)) {
				$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			}
			$data = array_merge($data, $personInfo);
		} else {
			$personInfo = $this->dbmodel->getFirstRowFromQuery("
				select top 1
					EPS.Person_id,
					EPS.PersonEvn_id,
					EPS.Server_id
				from v_EvnPS EPS with(nolock)
				where EPS.EvnPS_id = :EvnPS_id
			", $data);
			if (!is_array($personInfo)) {
				$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			}
			$data = array_merge($data, $personInfo);
		}

		$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		$data['ignoreEvnPSDoublesCheck'] = 1;

		if (!empty($data['EvnPS_setDT'])) {
			$data['EvnPS_setDate'] = date_create($data['EvnPS_setDT'])->format('Y-m-d');
			$data['EvnPS_setTime'] = date_create($data['EvnPS_setDT'])->format('H:i');
		}

		if (!empty($data['EvnDirection_setDT'])) {
			$data['EvnDirection_setDate'] = $data['EvnDirection_setDT'];
		}
		$data = array_merge($data, $this->ignoreYesNoArr);
		$resp = $this->dbmodel->doSave($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp['Error_Msg']
			));
		}

		if (!empty($data['EvnPS_IsPrehospAcceptRefuse'])) {
			$this->dbmodel->setEvnPSPrehospAcceptRefuse(array(
				'EvnPS_id' => $resp['EvnPS_id'],
				'EvnPS_IsPrehospAcceptRefuse' => $data['EvnPS_IsPrehospAcceptRefuse'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Получение списка движений в КВС
	 */
	function EvnSectionList_get() {
		$data = $this->ProcessInputData('getEvnSectionList');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->getEvnSectionListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
	
	/**
	 * Получить назначения  КВС,ТАП
	 */
	function EvnPrescr_get() {
		$data = $this->ProcessInputData('getEvnPrescr');
		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];

		$resp = $this->dbmodel->getEvnPrescrForAPI($data);
		
		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp['Error_Msg']
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
	
	/**
	 * Сохраненить назначение
	 */
	function EvnPrescr_post() {
		$data = $this->ProcessInputData('createEvnPrescr',null,true);		
		$this->load->model('EvnPrescrRegime_model', 'EvnPrescrRegime_model');
		
		$data['EvnPrescr_pid'] = $data['Evn_pid'];
		$resp = $this->dbmodel->saveEvnPrescrForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp['Error_Msg']
			));
		}
		
		if(empty($resp['EvnPrescr_id'])){
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$response = array(
			'error_code' => 0,
			'EvnPrescr_id' => $resp['EvnPrescr_id']
		);
		$this->response($response);
	}
	
	/**
	 * Редактировать назначение
	 */
	function EvnPrescr_put() {
		$data = $this->ProcessInputData('updateEvnPrescr',null,true);
		$resp = $this->dbmodel->updateEvnPrescrForAPI($data);
		
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp['Error_Msg']
			));
		}
		
		if(empty($resp['EvnPrescr_id'])){
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Получение данных движений в КВС
	 */
	function EvnSection_get() {
		$data = $this->ProcessInputData('getEvnSection');

		if(empty($data['Evn_id']) && empty($data['EvnPS_id'])){
			$this->response(array(
				'error_msg' => 'Должен быть передан параметр Evn_id или EvnPS_id',
				'error_code' => '6',
				'data' => ''
			));
		}

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];

		$resp = $this->dbmodel->getEvnSectionForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Добавление данных о движении
	 */
	function EvnSection_post() {
		$data = $this->ProcessInputData('createEvnSection', null, true, true, true);
		
		$data['EvnSection_setDate'] = $data['Evn_setDT'];
		if (!empty($data['Evn_disDT'])) $data['EvnSection_disDate'] = $data['Evn_disDT'];
		
		$Diag_Code = null;
		if(!empty($data['Diag_id'])){
			$Diag_Code = $this->dbmodel->getFirstResultFromQuery("select top 1 Diag_Code from v_Diag with (nolock) where Diag_id = :Diag_id", array(
				'Diag_id' => $data['Diag_id']
			));
			$diagFlag = TRUE;
			if(substr($Diag_Code, 0, 3) >= 'Z00' && substr($Diag_Code, 0, 3) <= 'Z99') {
				$diagFlag = FALSE;
			}
			if(empty($data['DeseaseType_id']) && $diagFlag && ( (!empty($data['Evn_setDT']) && $data['Evn_setDT']>='2018-11-01') || (!empty($data['Evn_disDT']) && $data['Evn_disDT']>='2018-11-01') ) ){
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Не передан обязательный параметр DeseaseType_id'
				));
			}
		}

		$this->load->model('EvnSection_model');

		$info = $this->EvnSection_model->getFirstRowFromQuery("
			select top 1
				EPS.Person_id,
				EPS.PersonEvn_id,
				EPS.Server_id
			from v_EvnPS EPS with(nolock)
			where EPS.EvnPS_id = :EvnPS_id
		", $data);
		if (!is_array($info)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$data = array_merge($data, $info);

		if (!empty($data['MedStaffFact_id'])) {
			// заполняем MedPersonal_id и LpuSection_id из MedStaffFact_id
			$resp = $this->dbmodel->getFirstRowFromQuery("
				select top 1
					MSF.MedStaffFact_id,
					MSF.LpuSection_id,
					MSF.MedPersonal_id,
					MSF.Lpu_id
				from
					v_MedStaffFact MSF with(nolock)
				where
					MSF.MedStaffFact_id = :MedStaffFact_id
			", $data, true);
			if (!is_array($resp)) {
				$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			}
			if (empty($resp['MedStaffFact_id'])) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Указано некорректное рабочее место врача'
				));
			}
			if ( $resp['Lpu_id'] != $data['Lpu_id'] ) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Данный метод доступен только для своей МО'
				));
			}
			if ( $resp['LpuSection_id'] != $data['LpuSection_id'] ) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Место работы врача должно быть в указанном отделении'
				));
			}
			$data['LpuSection_id'] = $resp['LpuSection_id'];
			$data['MedPersonal_id'] = $resp['MedPersonal_id'];
		}

		if (!empty($data['ResultDesease_id'])) {
			$info = $this->EvnSection_model->getFirstRowFromQuery("
				select top 1
					RD.ResultDesease_id,
					RD.ResultDesease_fedid as ResultDeseaseType_fedid
				from v_ResultDesease RD with(nolock)
				where RD.ResultDesease_id = :ResultDesease_id
			", $data);
			if (!is_array($info)) {
				$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			}
			$data = array_merge($data, $info);
		}

		if (!empty($data['LeaveType_id'])) {
			$info = $this->EvnSection_model->getFirstRowFromQuery("
				select top 1
					LT.LeaveType_id,
					LT.LeaveType_fedid
				from v_LeaveType LT with(nolock)
				where LT.LeaveType_id = :LeaveType_id
			", $data);
			if (!is_array($info)) {
				$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			}
			$data = array_merge($data, $info);
		}

		$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		$data['EvnSection_pid'] = $data['EvnPS_id'];

		$data['EvnSection_setDate'] = date_create($data['Evn_setDT'])->format('Y-m-d');
		$data['EvnSection_setTime'] = date_create($data['Evn_setDT'])->format('H:i');

		if (!empty($data['EvnSection_disDT'])) {
			$data['EvnSection_disDate'] = date_create($data['Evn_disDT'])->format('Y-m-d');
			$data['EvnSection_disTime'] = date_create($data['Evn_disDT'])->format('H:i');
		}

		$data = array_merge($data, $this->ignoreYesNoArr);
		$resp = $this->EvnSection_model->doSave($data);	//Сохранение движения
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp['Error_Msg']
			));
		}

		$response = array(
			'error_code' => 0,
			'data' => array(
				array('EvnSection_id' => $resp['EvnSection_id'])
			)
		);

		$this->response($response);
	}

	/**
	 * Редактирование данных о движении
	 */
	function EvnSection_put() {
		$data = $this->ProcessInputData('updateEvnSection', null, true, true, true);
		
		$Diag_Code = null;
		if(!empty($data['Diag_id'])){
			$Diag_Code = $this->dbmodel->getFirstResultFromQuery("select top 1 Diag_Code from v_Diag with (nolock) where Diag_id = :Diag_id", array(
				'Diag_id' => $data['Diag_pid']
			));
			$diagFlag = TRUE;
			if(substr($Diag_Code, 0, 3) >= 'Z00' && substr($Diag_Code, 0, 3) <= 'Z99') {
				$diagFlag = FALSE;
			}
			if(empty($data['DeseaseType_id']) && $diagFlag && ( (!empty($data['Evn_setDT']) && $data['Evn_setDT']>='2018-11-01') || (!empty($data['Evn_disDT']) && $data['Evn_disDT']>='2018-11-01') ) ){
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Не передан обязательный параметр DeseaseType_id'
				));
			}
		}
		
		if (!empty($data['Evn_setDT'])) $data['EvnSection_setDT'] = $data['Evn_setDT'];
		if (!empty($data['Evn_disDT'])) $data['EvnSection_disDT'] = $data['Evn_disDT'];

		$this->load->model('EvnSection_model');

		foreach($data as $key => $value) {
			if (empty($value) && !array_key_exists($key, $this->_args)) {
				unset($data[$key]);
			}
		}

		$info = $this->EvnSection_model->getFirstRowFromQuery("
			select top 1
				ES.Person_id,
				ES.PersonEvn_id,
				ES.Server_id,
				ES.Lpu_id
			from v_EvnSection ES with(nolock)
			where ES.EvnSection_id = :EvnSection_id
		", $data);
		if (!is_array($info)) {
			//$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Движение EvnSection_id = '.$data['EvnSection_id'].' не найдено'
			));
		}
		if ( $data['Lpu_id'] != $info['Lpu_id'] ) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		$data = array_merge($data, $info);

		if (!empty($data['MedStaffFact_id'])) {
			// заполняем MedPersonal_id и LpuSection_id из MedStaffFact_id
			$resp = $this->dbmodel->getFirstRowFromQuery("
				select top 1
					MSF.MedStaffFact_id,
					MSF.LpuSection_id,
					MSF.MedPersonal_id
				from
					v_MedStaffFact MSF with(nolock)
				where
					MSF.MedStaffFact_id = :MedStaffFact_id
			", $data, true);
			if (!is_array($resp)) {
				$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			}
			if (empty($resp['MedStaffFact_id'])) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Указано некорректное рабочее место врача'
				));
			}
			if ( ! empty($data['LpuSection_id']) && $resp['LpuSection_id'] != $data['LpuSection_id'] ) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Место работы врача должно быть в указанном отделении'
				));
			}
			$data['LpuSection_id'] = $resp['LpuSection_id'];
			$data['MedPersonal_id'] = $resp['MedPersonal_id'];
		}

		if (!empty($data['ResultDesease_id'])) {
			$info = $this->EvnSection_model->getFirstRowFromQuery("
				select top 1
					RD.ResultDesease_id,
					RD.ResultDesease_fedid as ResultDeseaseType_fedid
				from v_ResultDesease RD with(nolock)
				where RD.ResultDesease_id = :ResultDesease_id
			", $data);
			if (!is_array($info)) {
				$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			}
			$data = array_merge($data, $info);
		}

		if (!empty($data['LeaveType_id'])) {
			$info = $this->EvnSection_model->getFirstRowFromQuery("
				select top 1
					LT.LeaveType_id,
					LT.LeaveType_fedid
				from v_LeaveType LT with(nolock)
				where LT.LeaveType_id = :LeaveType_id
			", $data);
			if (!is_array($info)) {
				$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			}
			$data = array_merge($data, $info);
		}

		$data['scenario'] = swModel::SCENARIO_DO_SAVE;

		if (!empty($data['EvnSection_setDT'])) {
			$data['EvnSection_setDate'] = date_create($data['EvnSection_setDT'])->format('Y-m-d');
			$data['EvnSection_setTime'] = date_create($data['EvnSection_setDT'])->format('H:i');
		}

		if (!empty($data['EvnSection_disDT'])) {
			$data['EvnSection_disDate'] = date_create($data['EvnSection_disDT'])->format('Y-m-d');
			$data['EvnSection_disTime'] = date_create($data['EvnSection_disDT'])->format('H:i');
		}
		$data = array_merge($data, $this->ignoreYesNoArr);
		$resp = $this->EvnSection_model->doSave($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Получение списка сопутствующих диагнозов или осложнений в движении
	 */
	function EvnDiagPSByPar_get() {
		$data = $this->ProcessInputData('getEvnDiagPSList');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->getEvnDiagPSListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение данных сопутствующих диагнозов или осложнений в движении
	 */
	function EvnDiagPS_get() {
		$data = $this->ProcessInputData('getEvnDiagPS');
		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->getEvnDiagPSListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Добавление данных о сопутствующем диагнозе в движении
	 */
	function EvnDiagPS_post() {
		$data = $this->ProcessInputData('createEvnDiagPS', null, true, true, true);

		$this->load->model('EvnDiagPS_model');

		$info = $this->EvnDiagPS_model->getFirstRowFromQuery("
			select top 1
				ES.Person_id,
				ES.PersonEvn_id,
				ES.Server_id,
				ES.Lpu_id,
				ES.EvnSection_pid,
				isnull(ES.EvnSection_IsPriem, 1) as EvnSection_IsPriem
			from v_EvnSection ES with(nolock)
			where ES.EvnSection_id = :EvnSection_id
		", $data, true);
		if ($info === null) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Не найдено движение'
			));
		}
		if (!is_array($info)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if ( $data['Lpu_id'] != $info['Lpu_id'] ) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		$data = array_merge($data, $info);

		$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		if ($info['EvnSection_IsPriem'] == 2) {
			//Сопутствующий диагноз приемного отделения сохраняется на КВС
			$data['EvnDiagPS_pid'] = $info['EvnSection_pid'];
		} else {
			$data['EvnDiagPS_pid'] = $data['EvnSection_id'];
		}

		$resp = $this->EvnDiagPS_model->doSave($data, true);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp['Error_Msg']
			));
		}

		$response = array(
			'error_code' => 0,
			'data' => array(
				array('EvnDiagPS_id' => $resp['EvnDiagPS_id'])
			)
		);

		$this->response($response);
	}

	/**
	 * Редактирование данных о сопутствующем диагнозе в движении
	 */
	function EvnDiagPS_put() {
		$data = $this->ProcessInputData('updateEvnDiagPS', null, true, true, true);

		$this->load->model('EvnDiagPS_model');

		foreach($data as $key => $value) {
			if (empty($value) && !array_key_exists($key, $this->_args)) {
				unset($data[$key]);
			}
		}

		$info = $this->EvnDiagPS_model->getFirstRowFromQuery("
			select top 1
				EDPS.Person_id,
				EDPS.PersonEvn_id,
				EDPS.Server_id,
				EDPS.Lpu_id
			from v_EvnDiagPS EDPS with(nolock)
			where EDPS.EvnDiagPS_id = :EvnDiagPS_id
		", $data);
		if (!is_array($info)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if ( $data['Lpu_id'] != $info['Lpu_id'] ) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		$data = array_merge($data, $info);

		$data['scenario'] = swModel::SCENARIO_DO_SAVE;

		$resp = $this->EvnDiagPS_model->doSave($data, true);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0,
		));
	}

	/**
	 * Получение данных КВС для мобилы
	 */
	function mGetEvnPSInfo_get() {

		$data = $this->ProcessInputData('mGetEvnPSInfo');
		$this->response(array('error_code' => 500,'error_msg' => "Функционал находится в разработке"));
	}
}