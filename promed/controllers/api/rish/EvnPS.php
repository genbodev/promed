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

/**
 * @property EvnPS_model dbmodel
 * @OA\Tag(
 *     name="EvnPS",
 *     description="Карта выбывшего из стационара"
 * )
 */
class EvnPS extends SwREST_Controller {
	protected $inputRules = array(
		'getEvnPS' => array(
			array('field' => 'EvnPS_id', 'label' => 'Идентификатор КВС', 'rules' => '', 'type' => 'id'),
			array('field' => 'Evn_id', 'label' => 'Идентификатор случая', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_NumCard', 'label' => 'Номер карты', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPS_setDT', 'label' => 'Дата поступления', 'rules' => '', 'type' => 'string'),
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
			array('field' => 'LpuSectionBedProfile_id', 'label' => 'Профиль коек', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionBedProfileLink_fedid', 'label' => 'Профиль коек', 'rules' => '', 'type' => 'id'),
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
		'mLoadWorkPlacePriem' => array(
			array('field' => 'date', 'label' => 'Дата приема', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'EvnDirection_isConfirmed', 'label' => 'Подтверждение госпитализации', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSection_id', 'label' => 'Приемное отделение', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Person_SurName', 'label' => 'Фамилия', 'rules' => '', 'type' => 'string'),
			array('field' => 'Person_FirName', 'label' => 'Имя', 'rules' => '', 'type' => 'string'),
			array('field' => 'Person_SecName', 'label' => 'Отчество', 'rules' => '', 'type' => 'string'),
			array('field' => 'Person_BirthDay', 'label' => 'Дата рождения', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnQueueShow_id', 'label' => 'Очередь', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnDirectionShow_id', 'label' => 'План госпитализаций', 'rules' => '', 'type' => 'int'),
			array('field' => 'PrehospStatus_id', 'label' => 'Статус', 'rules' => '', 'type' => 'id'),
		),
		'mGetEvnPSNumber' => array(
			array('field' => 'year', 'label' => 'Год', 'rules' => '', 'type' => 'int')
		),
		'mSaveEvnPSWithLeavePriem' => array(
			array('field' => 'EvnPS_id', 'label' => 'Идентификатор карты выбывшего из стационара', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Diag_id', 'label' => 'Основной диагноз приемного отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonEvn_id', 'label' => 'Идентификатор состояния пациента', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение ("Госпитализирован в")', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_IsTransfCall', 'label' => 'Передан активный вызов', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrehospWaifRefuseCause_id', 'label' => 'Отказ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedicalCareFormType_id', 'label' => 'Форма помощи', 'rules' => '', 'type' => 'id'),
			array('field' => 'LeaveType_prmid', 'label' => 'Исход пребывания в приемном отделении', 'rules' => '', 'type' => 'id'),
			array('field' => 'ResultClass_id', 'label' => 'Исход', 'rules' => '', 'type' => 'id'),
			array('field' => 'ResultDeseaseType_id', 'label' => 'Результат обращения', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionProfile_id', 'label' => 'Профиль', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaComplex_id', 'label' => 'Код посещения', 'rules' => '', 'type' => 'id'),
			array('field' => 'DeseaseType_id', 'label' => 'Характер', 'rules' => '', 'type' => 'id'),
			array('field' => 'LeaveType_fedid', 'label' => 'Фед. результат', 'rules' => '', 'type' => 'id'),
			array('field' => 'ResultDeseaseType_fedid', 'label' => 'Фед. исход', 'rules' => '', 'type' => 'id')
		),
		'mSaveEvnPSWithPrehospWaifRefuseCause' => array(
			array('field' => 'EvnPS_id', 'label' => 'Идентификатор КВС', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PrehospWaifRefuseCause_id', 'label' => 'Причина отказа в госпитализации', 'rules' => '', 'type' => 'id', 'default' => null),
			array('field' => 'LeaveType_prmid', 'label' => 'Исход пребывания в приемном отделении', 'rules' => '', 'type' => 'id'),
			array('field' => 'ResultClass_id', 'label' => 'Исход', 'rules' => '', 'type' => 'id'),
			array('field' => 'ResultDeseaseType_id', 'label' => 'Результат обращения', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionProfile_id', 'label' => 'Профиль', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaComplex_id', 'label' => 'Код посещения', 'rules' => '', 'type' => 'id'),
			array('field' => 'LeaveType_fedid', 'label' => 'Фед. результат', 'rules' => '', 'type' => 'id'),
			array('field' => 'ResultDeseaseType_fedid', 'label' => 'Фед. исход', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_IsTransfCall', 'label' => 'Передан активный вызов', 'rules' => '', 'type' => 'id', 'default' => null)
		),
		'mBindRFID' => array(
			array('field' => 'EvnPS_id', 'label' => 'Идентификатор КВС', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'RFID_id', 'label' => 'Идентификатор метки RFID', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'ignoreOtherRelationsForRFID', 'label' => 'Игнорировать существующие связи RFID-метки с другими КВС', 'rules' => '', 'type' => 'boolean'),
			array('field' => 'ignoreOtherRelationsForEvnPS', 'label' => 'Игнорировать наличие RFID-метки у КВС', 'rules' => '', 'type' => 'boolean')
		),
		'mGetEvnPSByRFID' => array(
			array('field' => 'RFID_id', 'label' => 'Идентификатор метки RFID', 'rules' => 'required', 'type' => 'string')
		),
		'mUnbindRFID' => array(
			array('field' => 'EvnPS_id', 'label' => 'Идентификатор КВС', 'rules' => 'required', 'type' => 'id')
		),
		'mSaveEvnPS' => array(
			array('field' => 'from', 'label' => 'Откуда доставлен', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'childPS', 'label' => 'Признак наличия дочернего направления', 'rules' => '', 'type' => 'checkbox'),
			array('field' => 'PrehospStatus_id', 'label' => 'Статус', 'rules' => '', 'type' => 'id'),
			array('field' => 'TimetableStac_id', 'label' => 'Идентификатор бирки для стационара', 'type' => 'id'),
			array('field' => 'LpuSection_eid', 'label' => 'Отделение ("Госпитализирован в")', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_aid', 'label' => 'Основной диагноз (паталого-анатомический)', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_did', 'label' => 'Основной диагноз направившего учреждения', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_eid', 'label' => 'Внешняя причина', 'rules' => '', 'type' => 'id'),
			array('field' => 'DiagSetPhase_did', 'label' => 'Состояние пациента при направлении', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_PhaseDescr_did', 'label' => 'Описание фазы для диагнозов направившего (Diag_did)', 'rules' => '', 'type' => 'string'),
			array('field' => 'Diag_pid', 'label' => 'Основной диагноз приемного отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'DiagSetPhase_pid', 'label' => 'Состояние пациента при поступлении', 'rules' => '', 'type' => 'id'),
			array('field' => 'DiagSetPhase_aid', 'label' => 'Состояние пациента при выписке', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_PhaseDescr_pid', 'label' => 'Описание фазы для диагноза приемного', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnQueue_id', 'label' => 'Идентификатор очереди', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор электронного направления', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDirection_Num', 'label' => 'Номер направления', 'rules' => 'max_length[16]', 'type' => 'string'),
			array('field' => 'EvnDirection_setDate', 'label' => 'Дата направления', 'rules' => 'trim', 'type' => 'date'),
			array('field' => 'EvnPS_CodeConv', 'label' => 'Код', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPS_disDate', 'label' => 'Дата закрытия КВС', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnPS_disTime', 'label' => 'Время закрытия КВС', 'rules' => '', 'type' => 'time'),
			array('field' => 'EvnPS_HospCount', 'label' => 'Количество госпитализаций', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnPS_id', 'label' => 'Идентификатор карты выбывшего из стационара', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_IsCont', 'label' => 'Переведен', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnPS_IsDiagMismatch', 'label' => 'Несовпадение диагноза', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_IsImperHosp', 'label' => 'Несвоевременность госпитализации', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_IsShortVolume', 'label' => 'Недостаточный объем клинико-диагностического обследования', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_IsNeglectedCase', 'label' => 'Случай запущен', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'EvnPS_IsPLAmbulance', 'label' => 'Талон передан на ССМП', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_IsPrehospAcceptRefuse', 'label' => 'Отказ в подтверждении госпитализации', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_IsTransfCall', 'label' => 'Передан активный вызов', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_IsUnlaw', 'label' => 'Противоправная', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_IsUnport', 'label' => 'Нетранспортабельность', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_IsWaif', 'label' => 'Беспризорный', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_IsWithoutDirection', 'label' => 'Без электронного направления', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_IsWrongCure', 'label' => 'Неправильная тактика лечения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_NumCard', 'label' => 'Номер карты', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'EvnPS_NumConv', 'label' => 'Номер наряда', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPS_PrehospAcceptRefuseDT', 'label' => 'Дата отказа в подтверждении госпитализации', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnPS_PrehospWaifRefuseDT', 'label' => 'Дата отказа приёма', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnPS_setDate', 'label' => 'Дата поступления', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'EvnPS_setTime', 'label' => 'Время поступления', 'rules' => '', 'type' => 'time'),
			array('field' => 'EvnPS_OutcomeDate', 'label' => 'Дата исхода из приемного отделения', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnPS_OutcomeTime', 'label' => 'Время исхода из приемного отделения', 'rules' => '', 'type' => 'time'),
			array('field' => 'EvnPS_TimeDesease', 'label' => 'Время с начала заболевания', 'rules' => '', 'type' => 'int'),
			array('field' => 'Okei_id', 'label' => 'Единица измерени времени (с начала заболевания)', 'rules' => '', 'type' => 'id'),
			array('field' => 'LeaveType_prmid', 'label' => 'Исход пребывания в приемном отделении', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_did', 'label' => 'ЛПУ ("Госпитализация")', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_did', 'label' => 'Отделение ("Госпитализация")', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_pid', 'label' => 'Приемное отделение ("Приемное")', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedPersonal_pid', 'label' => 'Врач приемного отделения ("Приемное")', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedStaffFact_pid', 'label' => 'Рабочее место врача приемного отделения ("Приемное")', 'rules' => '', 'type' => 'id'),
			array('field' => 'Org_did', 'label' => 'Организация ("Госпитализация")', 'rules' => '', 'type' => 'id'),
			array('field' => 'OrgMilitary_did', 'label' => 'Военкомат ("Госпитализация")', 'rules' => '', 'type' => 'id'),
			array('field' => 'PayType_id', 'label' => 'Вид оплаты', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PersonEvn_id', 'label' => 'Идентификатор состояния пациента', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrehospArrive_id', 'label' => 'Кем доставлен', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrehospDirect_id', 'label' => 'Кем направлен', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrehospToxic_id', 'label' => 'Состояние опьянения', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionTransType_id', 'label' => 'Вид транспортировки', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrehospTrauma_id', 'label' => 'Травма', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrehospType_id', 'label' => 'Тип госпитализации', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PrehospWaifRefuseCause_id', 'label' => 'Отказ', 'rules' => '', 'type' => 'id'),
			array('field' => 'ResultClass_id', 'label' => 'Исход', 'rules' => '', 'type' => 'id'),
			array('field' => 'ResultDeseaseType_id', 'label' => 'Результат обращения', 'rules' => '', 'type' => 'id'),
			array('field' => 'LeaveType_fedid', 'label' => 'Фед. результат', 'rules' => '', 'type' => 'id'),
			array('field' => 'ResultDeseaseType_fedid', 'label' => 'Фед. исход', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaComplex_id', 'label' => 'Идентификатор услуги', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionProfile_id', 'label' => 'Профиль отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrehospWaifArrive_id', 'label' => 'Кем доставлен, если беспризорный', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrehospWaifReason_id', 'label' => 'Причина помещения в ЛПУ, если беспризорный', 'rules' => '', 'type' => 'id'),
			array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'addEvnSection', 'label' => 'Флаг добавления движения', 'rules' => '', 'type'	=> 'string'),
			array('field' => 'MedPersonal_id', 'label' => 'Идентификатор врача','rules' => '', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор места работы врача','rules' => '', 'type' => 'id'),
			array('field' => 'EntranceModeType_id', 'label' => 'Вид транспортировки', 'rules' => '', 'type' => 'id'),
			array('field' => 'CmpCallCard_id', 'label' => 'Идентфикатор талона вызова', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_HTMBegDate', 'label' => 'Дата выдачи талона на ВМП', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnPS_HTMHospDate', 'label' => 'Дата планируемой госпитализации (ВМП)', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnPS_HTMTicketNum', 'label' => 'Номер талона на ВМП', 'rules' => '', 'type' => 'string'),
			array('field' => 'DeseaseType_id', 'label' => 'Характер', 'rules' => '', 'type' => 'id'),
			array('field' => 'TumorStage_id', 'label' => 'Стадия выявленного ЗНО', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_IsZNO', 'label' => 'Подозрение на ЗНО', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_spid', 'label' => 'Подозрение на диагноз', 'rules' => '', 'type' => 'id'),
			array('field' => 'vizit_direction_control_check', 'label' => 'Проверка пересечения КВС с ТАП', 'rules' => '', 'type' => 'int'),
			array('field' => 'ignoreEvnPSDoublesCheck', 'label' => 'Проверка пересечения КВС', 'rules' => '', 'type' => 'int'),
			array('field' => 'ignoreEvnPSTimeDeseaseCheck', 'label' => 'Проверять заполнения поля «Время с начала заболевания»', 'rules' => '', 'type' => 'int')
		),
		'mLoadEvnPSEditForm' => [['field' => 'EvnPS_id', 'label' => 'Идентификатор карты выбывшего из стационара', 'rules' => 'required', 'type' => 'id']],
	);
	
	var $ignoreYesNoArr = array(
		'ignoreCheckEvnUslugaChange' => 1,
		'ignoreCheckEvnUslugaDates' => 1,
		'ignoreCheckKSGisEmpty' => 1,
		'ignoreCheckCardioFieldsEmpty' => 1,
		'ignoreDiagKSGCheck' => 1,
		'ignoreEvnDrug' => 1,
		'ignoreEvnPSTimeDeseaseCheck' => 1,
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
		$this->load->model('PacketPrescr_model', 'PacketPrescr_model');
		$this->load->model('EvnDiag_model', 'EvnDiag_model');
		$this->load->model('Person_model', 'PersonModel');
		$this->load->model('EvnSection_model', 'EvnSectionModel');

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

		if (!empty($data['Evn_disDT'])) {
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
	@OA\get(
	path="/api/EvnPS/mGetEvnPSInfo",
	tags={"EvnPS"},
	summary="Получение данных по КВС",

	@OA\Parameter(
	name="EvnPS_id",
	in="query",
	description="Идентификатор КВС",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,

	@OA\Response(
	response="200",
	description="JSON response",
	@OA\JsonContent(
	type="object",

	@OA\Property(
	property="error_code",
	description="Код ошибки",
	type="string",

	)
	,
	@OA\Property(
	property="data",
	description="Результат",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="accessType",
	description="Тип доступа",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPS_id",
	description="Карта выбывшего из стационара, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnPS_IsSigned",
	description="Документ подписан",
	type="boolean",

	)
	,
	@OA\Property(
	property="Person_id",
	description="Справочник идентификаторов человека, Идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="PersonEvn_id",
	description="События по человеку, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="Server_id",
	description="Идентификатор сервера",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnPS_NumCard",
	description="Карта выбывшего из стационара, номер карты",
	type="string",

	)
	,
	@OA\Property(
	property="Diag_id",
	description="Справочник диагнозов, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="Diag_Code",
	description="Справочник диагнозов, код",
	type="string",

	)
	,
	@OA\Property(
	property="Diag_Name",
	description="Справочник диагнозов, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="LeaveType_Code",
	description="тип выписки , код",
	type="string",

	)
	,
	@OA\Property(
	property="LeaveType_Name",
	description="тип выписки , наименование",
	type="string",

	)
	,
	@OA\Property(
	property="PrehospType_id",
	description="Тип предварительной госпитализации, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="PrehospType_Name",
	description="Тип предварительной госпитализации, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="PrehospArrive_id",
	description="Кем доставлен при предварительной госпитализации, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="PrehospArrive_Name",
	description="Кем доставлен при предварительной госпитализации, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPS_IsDiagMismatch",
	description="Карта выбывшего из стационара, Несовпадение диагноза",
	type="boolean",

	)
	,
	@OA\Property(
	property="EvnPS_IsWrongCure",
	description="Карта выбывшего из стационара, Неправильная тактика лечения",
	type="boolean",

	)
	,
	@OA\Property(
	property="EvnPS_IsShortVolume",
	description="Карта выбывшего из стационара, Недостаточный обьем оперативной помощи",
	type="boolean",

	)
	,
	@OA\Property(
	property="EvnPS_IsImperHosp",
	description="Карта выбывшего из стационара, Несвоевременность госпитализации",
	type="boolean",

	)
	,
	@OA\Property(
	property="EvnDirection_Num",
	description="Выписка направлений, номер направления",
	type="string",

	)
	,
	@OA\Property(
	property="EvnDirection_setDate",
	description="Дата направления",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPS_setDate",
	description="Дата прибытия",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPS_setTime",
	description="Время прибытия",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPS_disDate",
	description="Дата выписки",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPS_disTime",
	description="Время выписки",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSection",
	description="Движения по КВС",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="EvnSection_id",
	description="Движение в отделении, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnSection_setDate",
	description="Дата движения",
	type="string",

	),

	@OA\Property(
	property="LpuSection_Name",
	description="Отделение, наименование отделения",
	type="string",

	)

	)

	)

	)

	)

	)
	)

	)

	 */
	function mGetEvnPSInfo_get() {

		$data = $this->ProcessInputData('mGetEvnPSInfo', false, true);
		$this->load->model('EvnPS_model');

		$resp = $this->EvnPS_model->mGetEvnPSInfo($data);
		$this->response(array('error_code' => 0,'data' => $resp));
	}

	/**
	 @OA\get(
	path="/api/EvnPS/mLoadWorkPlacePriem",
	tags={"EvnPS"},
	summary="Получение списка записей для АРМа приемного отделения стационара",

	@OA\Parameter(
	name="date",
	in="query",
	description="Дата приема",
	required=true,
	@OA\Schema(type="string", format="date")
	)
	,
	@OA\Parameter(
	name="EvnDirection_isConfirmed",
	in="query",
	description="Подтверждение госпитализации",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LpuSection_id",
	in="query",
	description="Приемное отделение",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Person_SurName",
	in="query",
	description="Фамилия",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="Person_FirName",
	in="query",
	description="Имя",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="Person_SecName",
	in="query",
	description="Отчество",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="Person_BirthDay",
	in="query",
	description="Дата рождения",
	required=false,
	@OA\Schema(type="string", format="date")
	)
	,
	@OA\Parameter(
	name="EvnQueueShow_id",
	in="query",
	description="Очередь
	 * 0-не показывать
	 * 1-показывать
	 * 2-показывать, включая архив",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnDirectionShow_id",
	in="query",
	description="План госпитализаций
	 * 0-на текущий день
	 * 1-все направления",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PrehospStatus_id",
	in="query",
	description="Статус госпитализации
	 * 4-госпитализирован
	 * 5-отказ",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,

	@OA\Response(
	response="200",
	description="JSON response",
	@OA\JsonContent(
	type="object",

	@OA\Property(
	property="error_code",
	description="Код ошибки",
	type="string",

	)
	,
	@OA\Property(
	property="data",
	description="Данные",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="group_id",
	description="Идентификатор группы",
	type="integer",

	)
	,
	@OA\Property(
	property="group_title",
	description="Название группы",
	type="string",

	)
	,
	@OA\Property(
	property="group_data",
	description="Дополнительные данные",
	type="string",

	)
	,
	@OA\Property(
	property="patients",
	description="Информация по пациенту",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="insertDT",
	description="Дата создания записи",
	type="string",

	)
	,
	@OA\Property(
	property="groupField",
	description="Идентификатор статуса записи АРМ приемного отделения",
	type="string",

	)
	,
	@OA\Property(
	property="EvnDirection_id",
	description="Выписка направлений, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="PersonLpuInfo_IsAgree",
	description="Обработка перс. данных, Согласие на обработку перс данных",
	type="boolean",

	)
	,
	@OA\Property(
	property="EvnDirection_IsConfirmed",
	description="Выписка направлений, Подтверждение",
	type="boolean",

	)
	,
	@OA\Property(
	property="EvnDirection_Num",
	description="Выписка направлений, номер направления",
	type="string",

	)
	,
	@OA\Property(
	property="EvnDirection_setDate",
	description="Дата направления",
	type="string",

	)
	,
	@OA\Property(
	property="Diag_did",
	description="Основной диагноз направившего учреждения",
	type="string",

	)
	,
	@OA\Property(
	property="LpuSection_did",
	description="Направившее отделение",
	type="string",

	)
	,
	@OA\Property(
	property="Lpu_did",
	description="Направившая МО",
	type="string",

	)
	,
	@OA\Property(
	property="Org_did",
	description="Направившая организация",
	type="string",

	)
	,
	@OA\Property(
	property="DirType_id",
	description="Справочник назначений направления, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="TimetableStac_setDate",
	description="Дата поступления в стационар",
	type="string",

	)
	,
	@OA\Property(
	property="SMMP_exists",
	description="Информация о бригаде скорой помощи",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPS_CodeConv",
	description="Карта выбывшего из стационара, Кем доставлен (код)",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPS_NumConv",
	description="Карта выбывшего из стационара, Кем доставлен (номер наряда)",
	type="string",

	)
	,
	@OA\Property(
	property="TimetableStac_insDT",
	description="Дата создания записи",
	type="string",

	)
	,
	@OA\Property(
	property="EvnQueue_setDate",
	description="Дата постановки в очередь",
	type="string",

	)
	,
	@OA\Property(
	property="EvnQueue_id",
	description="Постановка в очередь, Идентификатор постановки в очередь",
	type="integer",

	)
	,
	@OA\Property(
	property="IsHospitalized",
	description="Отделение, указанное в исходе пребывания в приемном отделении",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPS_id",
	description="Карта выбывшего из стационара, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnPS_setDT",
	description="Дата создания КВС",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPS_NumCard",
	description="Карта выбывшего из стационара, номер карты",
	type="integer",

	)
	,
	@OA\Property(
	property="TimetableStac_id",
	description="Идентификатор бирки для стационара",
	type="integer",

	)
	,
	@OA\Property(
	property="LpuSectionProfile_Name",
	description="профиль отделения в ЛПУ, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="LpuSectionProfile_did",
	description="Идентификатор отделения, куда направили",
	type="integer",

	)
	,
	@OA\Property(
	property="PrehospStatus_id",
	description="Статусы записей АРМа приемного отделения, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="PrehospStatus_Name",
	description="Статусы записей АРМа приемного отделения, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="Diag_id",
	description="Справочник диагнозов, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="Diag_Code",
	description="Код диагноза",
	type="integer",

	)
	,
	@OA\Property(
	property="Diag_Name",
	description="Название диагноза",
	type="string",

	)
	,
	@OA\Property(
	property="pmUser_Name",
	description="Имя оператора",
	type="string",

	)
	,
	@OA\Property(
	property="IsRefusal",
	description="Признак наличия отказа",
	type="string",

	)
	,
	@OA\Property(
	property="PrehospWaifRefuseCause_id",
	description="Причина отказа от госпитализации, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="MedStaffFact_pid",
	description="Специльность врача",
	type="string",

	)
	,
	@OA\Property(
	property="IsCall",
	description="Признак передачи активного вызова",
	type="string",

	)
	,
	@OA\Property(
	property="IsSmmp",
	description="Признак передачи талона на ССМП",
	type="string",

	)
	,
	@OA\Property(
	property="PrehospArrive_id",
	description="Кем доставлен при предварительной госпитализации, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="PrehospArrive_SysNick",
	description="Кем доставлен при предварительной госпитализации, системное наименование",
	type="string",

	)
	,
	@OA\Property(
	property="PrehospType_id",
	description="Тип предварительной госпитализации, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="PrehospType_SysNick",
	description="Тип предварительной госпитализации, системное наименование",
	type="string",

	)
	,
	@OA\Property(
	property="Person_BirthDay",
	description="Дата рождения пациента",
	type="string",

	)
	,
	@OA\Property(
	property="Person_age",
	description="Возраст пациента",
	type="string",

	)
	,
	@OA\Property(
	property="Person_Fio",
	description="ФИО пациента",
	type="string",

	)
	,
	@OA\Property(
	property="PersonEncrypHIV_Encryp",
	description="Справочник шифрования ВИЧ-инфицированных, Шифр",
	type="string",

	)
	,
	@OA\Property(
	property="Person_id",
	description="Справочник идентификаторов человека, Идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="Person_IsUnknown",
	description="Справочник идентификаторов человека, Неизвестный человек",
	type="boolean",

	)
	,
	@OA\Property(
	property="PersonEvn_id",
	description="События по человеку, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="Server_id",
	description="Идентификатор сервера",
	type="integer",

	)
	,
	@OA\Property(
	property="LpuSection_Name",
	description="Справочник ЛПУ: отделения, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="EvnPL_id",
	description="Лечение в поликлинике, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="CmpCallCard_id",
	description="Справочник СМП: карты вызова, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnPS_RFID",
	description="RFID-метка",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnPS_OutcomeDT",
	description="Карта выбывшего из стационара, Дата исхода в приемном отделении",
	type="string",

	)

	)

	)

	)

	)

	)
	)

	)

	)
	 */
	function mLoadWorkPlacePriem_get() {
		$data = $this->ProcessInputData('mLoadWorkPlacePriem', false, true);
		$result = $this->dbmodel->mLoadWorkPlacePriem($data);
		$response = array('error_code' => 0,'data' => $result);
		$this->response($response);
	}

	/**
	 *  @OA\get(
	path="/api/EvnPS/mGetEvnPSNumber",
	tags={"EvnPS"},
	summary="Получение номера карты выбывшего из стационара",

	@OA\Parameter(
	name="year",
	in="query",
	description="Год",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,

	@OA\Response(
	response="200",
	description="JSON response",
	@OA\JsonContent(
	type="object",

	@OA\Property(
	property="EvnPS_NumCard",
	description="Карта выбывшего из стационара, номер карты",
	type="string",

	)

	)
	)

	)
	 */
	function mGetEvnPSNumber_get($returnAction = 'echo') {
		$data = $this->ProcessInputData('mGetEvnPSNumber', null, true);
		try {
			$this->load->model("Options_model", "opmodel");

			$options = $this->opmodel->getDataStorageOptions($data);
			$val = array();

			if (empty($data['Lpu_id'])) {
				throw new Exception('Не указан идентификатор ЛПУ', 400);
			}

			$response = $this->dbmodel->getEvnPSNumber($data);

			if (empty($options['stac']['evnps_numcard_prefix'])) {
				$options['stac']['evnps_numcard_prefix'] = "";
			}
			if (empty($options['stac']['evnps_numcard_postfix'])) {
				$options['stac']['evnps_numcard_postfix'] = "";
			}

			if (is_array($response) && count($response) > 0) {
				$response = $options['stac']['evnps_numcard_prefix'] . $response[0]['EvnPS_NumCard'] . $options['stac']['evnps_numcard_postfix'];
			}

		} catch (Exception $e) {
			$response = array('error_code' => 777 ,'error_msg' => toUtf($e->getMessage()));
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	@OA\post(
	path="/api/EvnPS/mSaveEvnPSWithLeavePriem",
	tags={"EvnPS"},
	summary="Cохранение исхода пребывания в приемном отделении",

	@OA\Parameter(
	name="EvnPS_id",
	in="query",
	description="Идентификатор карты выбывшего из стационара",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Diag_id",
	in="query",
	description="Основной диагноз приемного отделения",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PersonEvn_id",
	in="query",
	description="Идентификатор состояния пациента",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Server_id",
	in="query",
	description="Идентификатор сервера",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Person_id",
	in="query",
	description="Идентификатор пациента",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LpuSection_id",
	in="query",
	description="Идентификатор отделения",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnPS_IsTransfCall",
	in="query",
	description="Передан активный вызов",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PrehospWaifRefuseCause_id",
	in="query",
	description="Отказ",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="MedicalCareFormType_id",
	in="query",
	description="Форма помощи",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LeaveType_prmid",
	in="query",
	description="Исход пребывания в приемном отделении",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ResultClass_id",
	in="query",
	description="Исход",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ResultDeseaseType_id",
	in="query",
	description="Результат обращения",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LpuSectionProfile_id",
	in="query",
	description="Профиль",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="UslugaComplex_id",
	in="query",
	description="Код посещения",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="DeseaseType_id",
	in="query",
	description="Характер",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LeaveType_fedid",
	in="query",
	description="Фед. результат",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ResultDeseaseType_fedid",
	in="query",
	description="Фед. исход",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,

	@OA\Response(
	response="200",
	description="JSON response",
	@OA\JsonContent(
	type="object",

	@OA\Property(
	property="error_code",
	description="Код ошибки",
	type="string",

	)
	,
	@OA\Property(
	property="EvnSectionPriem_id",
	description="Идентификатор приёмного отделения",
	type="integer",

	)
	,
	@OA\Property(
	property="success",
	description="Результат выполнения",
	type="boolean",

	)

	)
	)

	)

	 */

	function mSaveEvnPSWithLeavePriem_post() {
		$data = $this->ProcessInputData('mSaveEvnPSWithLeavePriem', false, true);
		try {
			$response = $this->dbmodel->saveEvnPSWithLeavePriem($data);
			if (!empty($response['Error_Msg'])) {
				throw new Exception($response['Error_Msg'], 400);
			}
			$response = array('error_code' => 0 ,'EvnSectionPriem_id' => $response['EvnSectionPriem_id'],	'success' => true);
		} catch (Exception $e) {
			$response = array('error_code' => 777 ,'error_msg' => toUtf($e->getMessage()));
		}
		$this->response($response);

	}

	/**
	 *
	@OA\post(
	path="/api/EvnPS/mSaveEvnPSWithPrehospWaifRefuseCause",
	tags={"EvnPS"},
	summary="Сохранение причины отказа в госпитализации",

	@OA\Parameter(
	name="EvnPS_id",
	in="query",
	description="Идентификатор КВС",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PrehospWaifRefuseCause_id",
	in="query",
	description="Причина отказа в госпитализации",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LeaveType_prmid",
	in="query",
	description="Исход пребывания в приемном отделении",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ResultClass_id",
	in="query",
	description="Исход",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ResultDeseaseType_id",
	in="query",
	description="Результат обращения",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LpuSectionProfile_id",
	in="query",
	description="Профиль",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="UslugaComplex_id",
	in="query",
	description="Код посещения",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LeaveType_fedid",
	in="query",
	description="Фед. результат",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ResultDeseaseType_fedid",
	in="query",
	description="Фед. исход",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnPS_IsTransfCall",
	in="query",
	description="Передан активный вызов",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,

	@OA\Response(
	response="200",
	description="JSON response",
	@OA\JsonContent(
	type="object",

	@OA\Property(
	property="error_code",
	description="Код ошибки",
	type="integer",

	)
	,
	@OA\Property(
	property="success",
	description="Результат выполнения",
	type="boolean",

	@OA\Items(
	type="object",

	@OA\Property(
	property="Error_Msg",
	description="Сообщение об ошибке",
	type="string",

	)
	,
	@OA\Property(
	property="Error_Code",
	description="Код ошибки",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnSectionPriem_id",
	description="Идентификатор приемного отделения",
	type="integer",

	)

	)

	)

	)
	)

	)
	 */
	function mSaveEvnPSWithPrehospWaifRefuseCause_post() {
		$data = $this->ProcessInputData('mSaveEvnPSWithPrehospWaifRefuseCause', false, true);
		try {
			$response = $this->dbmodel->saveEvnPSWithPrehospWaifRefuseCause($data);
			if (!empty($response['Error_Msg'])) {
				throw new Exception($response['Error_Msg'], 400);
			}
			$response = array('error_code' => 0 ,'success' => true);
		} catch (Exception $e) {
			$response = array('error_code' => 777 ,'error_msg' => toUtf($e->getMessage()));
		}
		$this->response($response);
	}

	/**
	@OA\post(
	path="/api/EvnPS/mBindRFID",
	tags={"EvnPS"},
	summary="Связь КВС и метки RFID",

	@OA\Parameter(
	name="EvnPS_id",
	in="query",
	description="Идентификатор КВС",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="RFID_id",
	in="query",
	description="Идентификатор метки RFID",
	required=true,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="ignoreOtherRelationsForRFID",
	in="query",
	description="Игнорировать существующие связи RFID-метки с другими КВС",
	required=false,
	@OA\Schema(type="boolean")
	)
	,
	@OA\Parameter(
	name="ignoreOtherRelationsForEvnPS",
	in="query",
	description="Игнорировать наличие RFID-метки у КВС",
	required=false,
	@OA\Schema(type="boolean")
	)
	,

	@OA\Response(
	response="200",
	description="JSON response",
	@OA\JsonContent(
	type="object",

	@OA\Property(
	property="error_code",
	description="Код ошибки",
	type="string",

	)
	,
	@OA\Property(
	property="error_msg",
	description="Сообщение об ошибке",
	type="string",

	),
	@OA\Property(
	property="warning_bypass_flag",
	description="Флаг пропуска предупреждения",
	type="string"

	)

	)
	)

	)

	 */
	function mBindRFID_post() {

		$data = $this->ProcessInputData('mBindRFID', false, true);

		if (!empty($data['ignoreOtherRelationsForRFID']) && $data['ignoreOtherRelationsForRFID'] == 'false') {
			$data['ignoreOtherRelationsForRFID'] = false;
		}

		if (!empty($data['ignoreOtherRelationsForEvnPS']) && $data['ignoreOtherRelationsForEvnPS'] == 'false') {
			$data['ignoreOtherRelationsForEvnPS'] = false;
		}

		try {
			$this->dbmodel->mBindRFID($data);
			$response = array('error_code' => 0 ,'success' => true);
		} catch (Exception $e) {

			$code = $e->getCode();
			$code = !empty($code) ? $code : 777;
			$response = array('error_code' => $code,'error_msg' => toUtf($e->getMessage()));

			if ($code === 1) {
				$response['warning_bypass_flag'] = 'ignoreOtherRelationsForRFID';
			}

			if ($code === 2) {
				$response['warning_bypass_flag'] = 'ignoreOtherRelationsForEvnPS';
			}
		}

		$this->response($response);
	}

	/**
	@OA\get(
	path="/api/EvnPS/mGetEvnPSByRFID",
	tags={"EvnPS"},
	summary="Описание метода",

	@OA\Parameter(
	name="RFID_id",
	in="query",
	description="Идентификатор метки RFID",
	required=true,
	@OA\Schema(type="string")
	)
	,

	@OA\Response(
	response="200",
	description="JSON response",
	@OA\JsonContent(
	type="object",

	@OA\Property(
	property="error_code",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="data",
	description="Описание",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="EvnPS_id",
	description="Карта выбывшего из стационара, идентификатор",
	type="integer",

	)

	)

	)

	)
	)

	)

	 */
	function mGetEvnPSByRFID_get() {

		$data = $this->ProcessInputData('mGetEvnPSByRFID', false, true);

		$result = $this->dbmodel->mGetEvnPSByRFID($data);
		$response = array('error_code' => 0 ,'data' => $result);

		$this->response($response);
	}

	/**
	@OA\post(
	path="/api/EvnPS/mUnbindRFID",
	tags={"EvnPS"},
	summary="Отвязать метку от КВС",

	@OA\Parameter(
	name="EvnPS_id",
	in="query",
	description="Идентификатор КВС",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,

	@OA\Response(
	response="200",
	description="JSON response",
	@OA\JsonContent(
	type="object",

	@OA\Property(
	property="error_code",
	description="Код ошибки",
	type="string",

	)
	,
	@OA\Property(
	property="error_msg",
	description="Сообщение об ошибке",
	type="string",

	)

	)
	)

	)
	 */
	function mUnbindRFID_post() {

		$data = $this->ProcessInputData('mUnbindRFID', false, true);

		try {
			$this->dbmodel->mUnbindRFID($data);
			$response = array('error_code' => 0 ,'success' => true);
		} catch (Exception $e) {

			$code = $e->getCode();
			$code = !empty($code) ? $code : 777;
			$response = array('error_code' => $code,'error_msg' => toUtf($e->getMessage()));
		}

		$this->response($response);
	}

	/**
	 *
	@OA\post(
	path="/api/EvnPs/mSaveEvnPS",
	tags={"EvnPS"},
	summary="Сохранение карты выбывшего из стационара",

	@OA\Parameter(
	name="from",
	in="query",
	description="Откуда доставили",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="childPS",
	in="query",
	description="Признак наличия дочернего направления",
	required=false,
	@OA\Schema(type="boolean")
	)
	,
	@OA\Parameter(
	name="PrehospStatus_id",
	in="query",
	description="Статус записи",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnPS_Policeman",
	in="query",
	description="ФИО и должность сотрудника МВД России",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="EvnPS_NotificationTime",
	in="query",
	description="Время направления извещения",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="EvnPS_NotificationDate",
	in="query",
	description="Дата направления извещения",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="TimetableStac_id",
	in="query",
	description="Идентификатор бирки для стационара",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="LpuSection_eid",
	in="query",
	description="Отделение ('Госпитализирован в')",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="LpuSection_id",
	in="query",
	description="Отделение",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Diag_aid",
	in="query",
	description="Основной диагноз (паталого-анатомический)",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Diag_did",
	in="query",
	description="Основной диагноз направившего учреждения",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Diag_eid",
	in="query",
	description="Внешняя причина",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="DiagSetPhase_did",
	in="query",
	description="Состояние пациента при направлении",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnPS_PhaseDescr_did",
	in="query",
	description="Описание фазы для диагнозов направившего (Diag_did)",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="Diag_pid",
	in="query",
	description="Основной диагноз приемного отделения",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="DiagSetPhase_pid",
	in="query",
	description="Состояние пациента при поступлении",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="DiagSetPhase_aid",
	in="query",
	description="Состояние пациента при выписке",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnPS_PhaseDescr_pid",
	in="query",
	description="Описание фазы для диагноза приемного",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="EvnQueue_id",
	in="query",
	description="Идентификатор очереди",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnDirection_id",
	in="query",
	description="Идентификатор электронного направления",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnDirection_Num",
	in="query",
	description="Номер направления",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="EvnDirection_setDate",
	in="query",
	description="Дата направления",
	required=false,
	@OA\Schema(type="string", format="date")
	)
	,
	@OA\Parameter(
	name="EvnPS_CodeConv",
	in="query",
	description="Код КВС",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="EvnPS_disDate",
	in="query",
	description="Дата закрытия КВС",
	required=false,
	@OA\Schema(type="string", format="date")
	)
	,
	@OA\Parameter(
	name="EvnPS_disTime",
	in="query",
	description="Время закрытия КВС",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="EvnPS_HospCount",
	in="query",
	description="Количество госпитализаций",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnPS_id",
	in="query",
	description="Идентификатор карты выбывшего из стационара",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnPS_IsCont",
	in="query",
	description="Переведен",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnPS_IsDiagMismatch",
	in="query",
	description="Несовпадение диагноза",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnPS_IsImperHosp",
	in="query",
	description="Несвоевременность госпитализации",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnPS_IsShortVolume",
	in="query",
	description="Недостаточный объем клинико-диагностического обследования",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnPS_IsNeglectedCase",
	in="query",
	description="Случай запущен",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnPS_IsPLAmbulance",
	in="query",
	description="Талон передан на ССМП",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnPS_IsPrehospAcceptRefuse",
	in="query",
	description="Отказ в подтверждении госпитализации",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnPS_IsTransfCall",
	in="query",
	description="Передан активный вызов",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnPS_IsUnlaw",
	in="query",
	description="Противоправная",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnPS_IsUnport",
	in="query",
	description="Нетранспортабельность",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnPS_IsWaif",
	in="query",
	description="Беспризорный",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnPS_IsWithoutDirection",
	in="query",
	description="Без электронного направления",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnPS_IsWrongCure",
	in="query",
	description="Неправильная тактика лечения",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnPS_NumCard",
	in="query",
	description="Номер карты",
	required=true,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="EvnPS_NumConv",
	in="query",
	description="Номер наряда",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="EvnPS_PrehospAcceptRefuseDT",
	in="query",
	description="Дата отказа в подтверждении госпитализации",
	required=false,
	@OA\Schema(type="string", format="date")
	)
	,
	@OA\Parameter(
	name="EvnPS_PrehospWaifRefuseDT",
	in="query",
	description="Дата отказа приёма",
	required=false,
	@OA\Schema(type="string", format="date")
	)
	,
	@OA\Parameter(
	name="EvnPS_setDate",
	in="query",
	description="Дата поступления",
	required=true,
	@OA\Schema(type="string", format="date")
	)
	,
	@OA\Parameter(
	name="EvnPS_setTime",
	in="query",
	description="Время поступления",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="EvnPS_OutcomeDate",
	in="query",
	description="Дата исхода из приемного отделения",
	required=false,
	@OA\Schema(type="string", format="date")
	)
	,
	@OA\Parameter(
	name="EvnPS_OutcomeTime",
	in="query",
	description="Время исхода из приемного отделения",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="EvnPS_TimeDesease",
	in="query",
	description="Время с начала заболевания",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Okei_id",
	in="query",
	description="Единица измерени времени (с начала заболевания)",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LeaveType_prmid",
	in="query",
	description="Исход пребывания в приемном отделении",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Lpu_did",
	in="query",
	description="ЛПУ ('Госпитализация')",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LpuSection_did",
	in="query",
	description="Отделение ('Госпитализация')",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LpuSection_pid",
	in="query",
	description="Приемное отделение ('Приемное')",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="MedPersonal_pid",
	in="query",
	description="Врач приемного отделения ('Приемное')",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="MedStaffFact_pid",
	in="query",
	description="Рабочее место врача приемного отделения ('Приемное')",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Org_did",
	in="query",
	description="Организация ('Госпитализация')",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="OrgMilitary_did",
	in="query",
	description="Военкомат ('Госпитализация')",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PayType_id",
	in="query",
	description="Вид оплаты",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PersonEvn_id",
	in="query",
	description="Идентификатор состояния пациента",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PrehospArrive_id",
	in="query",
	description="Кем доставлен",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PrehospDirect_id",
	in="query",
	description="Кем направлен",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PrehospToxic_id",
	in="query",
	description="Состояние опьянения",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LpuSectionTransType_id",
	in="query",
	description="Вид транспортировки",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PrehospTrauma_id",
	in="query",
	description="Травма",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PrehospType_id",
	in="query",
	description="Тип госпитализации",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PrehospWaifRefuseCause_id",
	in="query",
	description="Отказ",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ResultClass_id",
	in="query",
	description="Исход",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ResultDeseaseType_id",
	in="query",
	description="Результат обращения",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LeaveType_fedid",
	in="query",
	description="Фед. результат",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ResultDeseaseType_fedid",
	in="query",
	description="Фед. исход",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="UslugaComplex_id",
	in="query",
	description="Идентификатор услуги",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LpuSectionProfile_id",
	in="query",
	description="Профиль отделения",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PrehospWaifArrive_id",
	in="query",
	description="Кем доставлен, если беспризорный",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PrehospWaifReason_id",
	in="query",
	description="Причина помещения в ЛПУ, если беспризорный",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Server_id",
	in="query",
	description="Идентификатор сервера",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Person_id",
	in="query",
	description="Идентификатор пациента",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="addEvnSection",
	in="query",
	description="Флаг добавления движения",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="MedPersonal_id",
	in="query",
	description="Идентификатор врача",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="MedStaffFact_id",
	in="query",
	description="Идентификатор места работы врача",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="EntranceModeType_id",
	in="query",
	description="Вид транспортировки",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="CmpCallCard_id",
	in="query",
	description="Идентфикатор талона вызова",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnPS_HTMBegDate",
	in="query",
	description="Дата выдачи талона на ВМП",
	required=false,
	@OA\Schema(type="string", format="date")
	)
	,
	@OA\Parameter(
	name="EvnPS_HTMHospDate",
	in="query",
	description="Дата планируемой госпитализации (ВМП)",
	required=false,
	@OA\Schema(type="string", format="date")
	)
	,
	@OA\Parameter(
	name="EvnPS_HTMTicketNum",
	in="query",
	description="Номер талона на ВМП",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="DeseaseType_id",
	in="query",
	description="Характер",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="TumorStage_id",
	in="query",
	description="Стадия выявленного ЗНО",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnPS_IsZNO",
	in="query",
	description="Подозрение на ЗНО",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Diag_spid",
	in="query",
	description="Подозрение на диагноз",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="vizit_direction_control_check",
	in="query",
	description="Проверка пересечения КВС с ТАП",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ignoreEvnPSDoublesCheck",
	in="query",
	description="Проверка пересечения КВС",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ignoreEvnPSTimeDeseaseCheck",
	in="query",
	description="Проверять заполнения поля «Время с начала заболевания»",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,

	@OA\Response(
	response="200",
	description="JSON response",
	@OA\JsonContent(
	type="object",


	@OA\Property(
	property="error_code",
	description="Код ошибки",
	type="string",

	)
	,
	@OA\Property(
	property="success",
	description="Результат выполнения",
	type="string",

	)

	)

	)

	)
	)

	)
	 */
	function mSaveEvnPS_post()
	{
		$this->inputRules['saveEvnPS'] = $this->dbmodel->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('mSaveEvnPS', false, true);
		$data['Server_id'] = $this->PacketPrescr_model->getServerId(array('Person_id'=>$data['Person_id']));

		if (!empty($data['session']['CurMedStaffFact_id'])) {
			$data['MedStaffFact_pid'] = $data['session']['CurMedStaffFact_id'];
		}

		if (!empty($data['session']['CurLpuSection_id'])) {
			$data['LpuSection_pid'] = $data['session']['CurLpuSection_id'];
		}

		$arr = array('Person_id'=> $data['Person_id'], 'Server_id'=>$data['Server_id']);

		$PersonEvn_id = $data['PersonEvn_id'] = $this->PersonModel->getPersonEvn($arr);
		if (!empty($data['save_data'])) {
			$saveData = json_decode($data['save_data'],true);
		}
		try {
			if (empty($data['isAutoCreate'])) {
				$data['scenario'] = swModel::SCENARIO_DO_SAVE;
			} else {
				$data['scenario'] = swModel::SCENARIO_AUTO_CREATE;
			}
			$saveEvnPs = $this->dbmodel->doSave($data);
			if (!empty($saveEvnPs['Error_Msg'])) {
				$error = !empty($saveEvnPs['Alert_Msg']) ? $saveEvnPs['Alert_Msg'] : $saveEvnPs['Error_Msg'];
				throw new Exception($error, $saveEvnPs['Error_Code']);
			}
			if (!empty($saveData)) {
				foreach ($saveData as $item) {
					$DiagArr = array('Person_id'=>$data['Person_id'], 'Diag_id'=>$item['Diag_id']);
					$diagData = $this->EvnDiag_model->getDiagData($DiagArr);

					$item['DiagSetType_id'] = $diagData['DiagSetType_id'];
					$item['PersonEvn_id'] = $PersonEvn_id;
					$item['Person_id'] = $data['Person_id'];
					$item['EvnDiagPS_pid'] = $diagData['EvnDiagPS_pid'];
					$item['EvnDiagPS_id'] = 0;
					$saveDiag = $this->EvnDiag_model->mSaveEvnDiagPS(array_merge($item, $data));
				}
			}
			$response = array('error_code' => 0 ,'success' => true);
		} catch (Exception $e) {
			$code = $e->getCode();
			$code = !empty($code) ? $code : 777;
			$response = array('error_code' => $code,'error_msg' => toUtf($e->getMessage()));
			if ($code == 113) {
				$response['warning_bypass_flag'] = 'ignoreEvnPSDoublesCheck';
			}
		}
		$this->response($response);
	}

	/**
	@OA\get(
		path="/api/rish/EvnPS/mLoadEvnPSEditForm",
		tags={"EvnPS"},
		summary="Загрузка данных формы редактирования движения приемного отделения",

		@OA\Parameter(
			name="EvnPS_id",
			in="query",
			description="Идентификатор карты выбывшего из стационара",
			required=true,
			@OA\Schema(type="integer", format="int64")
		),

		@OA\Response(
			response="200",
			description="JSON response",
			@OA\JsonContent(
				type="object",

				@OA\Property(
					property="error_code",
					description="Описание",
					type="string",
				),
				@OA\Property(
					property="data",
					description="Описание",
					type="array",

					@OA\Items(
						type="object",

						@OA\Property(
							property="accessType",
							description="Описание",
							type="string",
						),
						@OA\Property(
							property="EvnPS_id",
							description="Карта выбывшего из стационара, идентификатор",
							type="integer",
						),
						@OA\Property(
							property="EvnPS_IsSigned",
							description="Описание",
							type="boolean",
						),
						@OA\Property(
							property="Lpu_id",
							description="справочник ЛПУ, ЛПУ",
							type="integer",
						),
						@OA\Property(
							property="EvnPS_IsTransit",
							description="Описание",
							type="boolean",
						),
						@OA\Property(
							property="EvnPS_IsCont",
							description="Карта выбывшего из стационара, Продолжение случая",
							type="boolean",
						),
						@OA\Property(
							property="DiagSetPhase_did",
							description="Описание",
							type="string",
						),
						@OA\Property(
							property="EvnPS_PhaseDescr_did",
							description="Карта выбывшего из стационара, Описание фазы для диагнозов направившего (Diag_did)",
							type="string",
						),
						@OA\Property(
							property="Diag_pid",
							description="Справочник диагнозов, идентификатор диагноза родителя",
							type="string",
						),
						@OA\Property(
							property="Diag_eid",
							description="Описание",
							type="string",
						),
						@OA\Property(
							property="DiagSetPhase_pid",
							description="Описание",
							type="string",
						),
						@OA\Property(
							property="DiagSetPhase_aid",
							description="Описание",
							type="string",
						),
						@OA\Property(
							property="EvnPS_PhaseDescr_pid",
							description="Карта выбывшего из стационара, Описание фазы для диагнозов приемного (Diag_pid)",
							type="string",
						),
						@OA\Property(
							property="EvnPS_NumCard",
							description="Карта выбывшего из стационара, номер карты",
							type="string",
						),
						@OA\Property(
							property="LeaveType_id",
							description="тип выписки , идентификатор",
							type="integer",
						),
						@OA\Property(
							property="PayType_id",
							description="Тип оплаты, идентификатор",
							type="integer",
						),
						@OA\Property(
							property="EvnPS_setDate",
							description="Описание",
							type="string",
						),
						@OA\Property(
							property="EvnPS_setTime",
							description="Описание",
							type="string",
						),
						@OA\Property(
							property="EvnPS_OutcomeDate",
							description="Описание",
							type="string",
						),
						@OA\Property(
							property="EvnPS_OutcomeTime",
							description="Описание",
							type="string",
						),
						@OA\Property(
							property="PrehospDirect_id",
							description="Кем направлен в предварительной госпитализации, идентификатор",
							type="integer",
						),
						@OA\Property(
							property="EvnDirection_Num",
							description="Выписка направлений, номер направления",
							type="string",
						),
						@OA\Property(
							property="EvnDirection_setDate",
							description="Описание",
							type="string",
						),
						@OA\Property(
							property="Org_did",
							description="Описание",
							type="string",
						),
						@OA\Property(
							property="MedStaffFact_did",
							description="Описание",
							type="string",
						),
						@OA\Property(
							property="MedPersonal_did",
							description="Описание",
							type="string",
						),
						@OA\Property(
							property="MedStaffFact_TFOMSCode",
							description="Описание",
							type="string",
						),
						@OA\Property(
							property="LpuSection_did",
							description="Описание",
							type="string",
						),
						@OA\Property(
							property="Lpu_did",
							description="Описание",
							type="string",
						),
						@OA\Property(
							property="Diag_did",
							description="Описание",
							type="string",
						),
						@OA\Property(
							property="EvnDirection_id",
							description="Выписка направлений, идентификатор",
							type="integer",
						),
						@OA\Property(
							property="EvnDirectionHTM_id",
							description="Направление на ВМП, идентификатор",
							type="integer",
						),
						@OA\Property(
							property="DirType_id",
							description="Справочник назначений направления, идентификатор",
							type="integer",
						),
						@OA\Property(
							property="EvnDirection_IsAuto",
							description="Выписка направлений, автоматически созданное направление",
							type="boolean",
						),
						@OA\Property(
							property="EvnDirection_IsReceive",
							description="Выписка направлений, признак создания направления принимающей стороной",
							type="boolean",
						),
						@OA\Property(
							property="LpuSection_pid",
							description="Справочник ЛПУ: отделения, идентификатор родительского отделения",
							type="string",
						),
						@OA\Property(
							property="MedStaffFact_pid",
							description="Описание",
							type="string",
						),
						@OA\Property(
							property="PrehospArrive_id",
							description="Кем доставлен при предварительной госпитализации, идентификатор",
							type="integer",
						),
						@OA\Property(
							property="CmpCallCard_id",
							description="Справочник СМП: карты вызова, идентификатор",
							type="integer",
						),
						@OA\Property(
							property="EvnPS_CodeConv",
							description="Карта выбывшего из стационара, Кем доставлен (код)",
							type="string",
						),
						@OA\Property(
							property="EvnPS_NumConv",
							description="Карта выбывшего из стационара, Кем доставлен (номер наряда)",
							type="string",
						),
						@OA\Property(
							property="PrehospToxic_id",
							description="Вид отравления, идентификатор",
							type="integer",
						),
						@OA\Property(
							property="LpuSectionTransType_id",
							description="Справочник Вид транспортировки в отделение, идентификатор",
							type="integer",
						),
						@OA\Property(
							property="PrehospType_id",
							description="Тип предварительной госпитализации, идентификатор",
							type="integer",
						),
						@OA\Property(
							property="EvnPS_HospCount",
							description="Карта выбывшего из стационара, количество госпитализаций",
							type="string",
						),
						@OA\Property(
							property="EvnPS_TimeDesease",
							description="Карта выбывшего из стационара, Время с начала заболевания",
							type="string",
						),
						@OA\Property(
							property="Okei_id",
							description="Общероссийский классификатор единиц измерения (ОКЕИ), идентификатор",
							type="integer",
						),
						@OA\Property(
							property="PrehospTrauma_id",
							description="Травма при предварительной госпитализации, идентификатор",
							type="integer",
						),
						@OA\Property(
							property="EvnPS_IsUnlaw",
							description="Карта выбывшего из стационара, противоправная",
							type="boolean",
						),
						@OA\Property(
							property="EvnPS_IsUnport",
							description="Карта выбывшего из стационара, нетранспортабельность",
							type="boolean",
						),
						@OA\Property(
							property="EvnPS_NotificationDate",
							description="Описание",
							type="string",
						),
						@OA\Property(
							property="EvnPS_NotificationTime",
							description="Описание",
							type="string",
						),
						@OA\Property(
							property="MedStaffFact_id",
							description="Кэш мест работы, идентификатор места работы",
							type="integer",
						),
						@OA\Property(
							property="EvnPS_Policeman",
							description="Карта выбывшего из стационара, ФИО и должность сотрудника МВД России",
							type="string",
						),
						@OA\Property(
							property="EvnPS_IsImperHosp",
							description="Карта выбывшего из стационара, Несвоевременность госпитализации",
							type="boolean",
						),
						@OA\Property(
							property="EvnPS_IsNeglectedCase",
							description="Карта выбывшего из стационара, Случай запущен",
							type="boolean",
						),
						@OA\Property(
							property="EvnPS_IsShortVolume",
							description="Карта выбывшего из стационара, Недостаточный обьем оперативной помощи",
							type="boolean",
						),
						@OA\Property(
							property="EvnPS_IsWrongCure",
							description="Карта выбывшего из стационара, Неправильная тактика лечения",
							type="boolean",
						),
						@OA\Property(
							property="EvnPS_IsDiagMismatch",
							description="Карта выбывшего из стационара, Несовпадение диагноза",
							type="boolean",
						),
						@OA\Property(
							property="EvnPS_IsWaif",
							description="Карта выбывшего из стационара, признак Беспризорный (Да/Нет)",
							type="boolean",
						),
						@OA\Property(
							property="EvnPS_IsPLAmbulance",
							description="Карта выбывшего из стационара, признак «Талон передан на ССМП» (Да/Нет)",
							type="boolean",
						),
						@OA\Property(
							property="PrehospWaifArrive_id",
							description="Кем доставлен( Беспризорный), идентификатор",
							type="integer",
						),
						@OA\Property(
							property="PrehospWaifReason_id",
							description="Причина помещения в ЛПУ(Беспризорный), идентификатор",
							type="integer",
						),
						@OA\Property(
							property="LpuSection_id",
							description="Справочник ЛПУ: отделения, идентификатор",
							type="integer",
						),
						@OA\Property(
							property="PrehospWaifRefuseCause_id",
							description="Причина отказа от госпитализации, идентификатор",
							type="integer",
						),
						@OA\Property(
							property="MedicalCareFormType_id",
							description="Описание",
							type="integer",
						),
						@OA\Property(
							property="ResultClass_id",
							description="Полка: результат лечения, идентификатор",
							type="integer",
						),
						@OA\Property(
							property="ResultDeseaseType_id",
							description="Исход заболевания, идентификатор",
							type="integer",
						),
						@OA\Property(
							property="EvnPS_IsTransfCall",
							description="Карта выбывшего из стационара, признак 'Передан активный вызов' (да/нет)",
							type="boolean",
						),
						@OA\Property(
							property="Person_id",
							description="Справочник идентификаторов человека, Идентификатор",
							type="integer",
						),
						@OA\Property(
							property="PersonEvn_id",
							description="События по человеку, идентификатор",
							type="integer",
						),
						@OA\Property(
							property="Server_id",
							description="Описание",
							type="integer",
						),
						@OA\Property(
							property="EvnPS_IsWithoutDirection",
							description="Карта выбывшего из стационара, Без направления",
							type="boolean",
						),
						@OA\Property(
							property="EvnQueue_id",
							description="Постановка в очередь, Идентификатор постановки в очередь",
							type="integer",
						),
						@OA\Property(
							property="EvnPS_IsPrehospAcceptRefuse",
							description="Карта выбывшего из стационара, Отказ в подтверждении госпитализации",
							type="boolean",
						),
						@OA\Property(
							property="EvnPS_PrehospAcceptRefuseDT",
							description="Карта выбывшего из стационара, Дата отказа в потверждении госпитализации",
							type="string",
						),
						@OA\Property(
							property="EvnPS_PrehospWaifRefuseDT",
							description="Карта выбывшего из стационара, Дата отказа от госпитализации (Беспризорный)",
							type="string",
						),
						@OA\Property(
							property="LpuSection_eid",
							description="Описание",
							type="string",
						),
						@OA\Property(
							property="PrehospStatus_id",
							description="Статусы записей АРМа приемного отделения, идентификатор",
							type="integer",
						),
						@OA\Property(
							property="EvnPS_HTMBegDate",
							description="Карта выбывшего из стационара, Дата выдачи талона на ВМП",
							type="string",
						),
						@OA\Property(
							property="EvnPS_HTMHospDate",
							description="Карта выбывшего из стационара, Дата планируемой госпитализации (ВМП)",
							type="string",
						),
						@OA\Property(
							property="EvnPS_HTMTicketNum",
							description="Карта выбывшего из стационара, Номер талона на ВМП",
							type="string",
						),
						@OA\Property(
							property="UslugaComplex_id",
							description="Комплексные услуги, идентификатор",
							type="integer",
						),
						@OA\Property(
							property="LpuSectionProfile_id",
							description="профиль отделения в ЛПУ, идентификатор",
							type="integer",
						),
						@OA\Property(
							property="EntranceModeType_id",
							description="Способ передвижения, Идентификатор способа передвижения",
							type="integer",
						),
						@OA\Property(
							property="DeseaseType_id",
							description="Справочник заболеваний: характер заболевания, идентификатор",
							type="integer",
						),
						@OA\Property(
							property="TumorStage_id",
							description="Стадия опухолевого процесса, идентификатор",
							type="integer",
						),
						@OA\Property(
							property="EvnPS_IsZNO",
							description="Карта выбывшего из стационара, подозрение на ЗНО",
							type="boolean",
						),
						@OA\Property(
							property="Diag_spid",
							description="Описание",
							type="string",
						),
						@OA\Property(
							property="ChildLpuSection_id",
							description="Описание",
							type="integer",
						),
						@OA\Property(
							property="EvnCostPrint_setDT",
							description="Справочник даты печати справки либо даты отказа от справки на случае, дата выдачи справки/отказа",
							type="string",
						),
						@OA\Property(
							property="EvnCostPrint_IsNoPrint",
							description="Справочник даты печати справки либо даты отказа от справки на случае, отказ от печати справки",
							type="boolean",
						),
						@OA\Property(
							property="EvnCostPrint_Number",
							description="Справочник даты печати справки либо даты отказа от справки на случае, Номер справки",
							type="string",
						),
						@OA\Property(
							property="LeaveType_prmid",
							description="Описание",
							type="string",
						),
						@OA\Property(
							property="LeaveType_fedid",
							description="тип выписки , Классификатор результатов обращения за медицинской помощью",
							type="string",
						),
						@OA\Property(
							property="ResultDeseaseType_fedid",
							description="Исход заболевания, классификатор исходов заболеваний (V012)",
							type="string",
						),
						@OA\Property(
							property="EvnSection_IsPaid",
							description="Движение в отделении, Случай оплачен",
							type="boolean",
						),
						@OA\Property(
							property="EvnPS_IndexRep",
							description="Карта выбывшего из стационара, Признак повторной подачи",
							type="string",
						),
						@OA\Property(
							property="EvnPS_IndexRepInReg",
							description="Карта выбывшего из стационара, Признак вхождения в реестр повторной подачи",
							type="string",
						),
						@OA\Property(
							property="childPS",
							description="Описание",
							type="string",
						),
						@OA\Property(
							property="EvnPS_isMseDirected",
							description="Карта выбывшего из стационара, Направлен на МЭС",
							type="string",
						),
						@OA\Property(
							property="pid_DiagName",
							description="Описание",
							type="string",
						),
						@OA\Property(
							property="eid_DiagName",
							description="Описание",
							type="string",
						),
						@OA\Property(
							property="did_DiagName",
							description="Описание",
							type="string",
						),
						@OA\Property(
							property="spid_DiagName",
							description="Описание",
							type="string",
						),
						@OA\Property(
							property="EvnDiagPSHosp",
							description="Сопутствующие диагнозы направившего заведения",
							type="array",

							@OA\Items(
								type="object",

								@OA\Property(
									property="EvnDiagPS_id",
									description="Установка диагноза в стационаре, идентификатор",
									type="integer",
								),
								@OA\Property(
									property="EvnDiagPS_pid",
									description="Описание",
									type="integer",
								),
								@OA\Property(
									property="Person_id",
									description="Справочник идентификаторов человека, Идентификатор",
									type="integer",
								),
								@OA\Property(
									property="PersonEvn_id",
									description="События по человеку, идентификатор",
									type="integer",
								),
								@OA\Property(
									property="Server_id",
									description="Описание",
									type="integer",
								),
								@OA\Property(
									property="Diag_id",
									description="Справочник диагнозов, идентификатор",
									type="integer",
								),
								@OA\Property(
									property="DiagSetPhase_id",
									description="Степень тяжести состояния пациента (OID 1.2.643.5.1.13.13.11.1006 ), идентификатор",
									type="integer",
								),
								@OA\Property(
									property="EvnDiagPS_PhaseDescr",
									description="Установка диагноза в стационаре, описание фазы",
									type="string",
								),
								@OA\Property(
									property="DiagSetClass_id",
									description="Справочник диагнозов: класс диагноза, идентификатор",
									type="integer",
								),
								@OA\Property(
									property="DiagSetType_id",
									description="Справочник диагнозов: вид диагноза, идентификатор",
									type="integer",
								),
								@OA\Property(
									property="EvnDiagPS_setDate",
									description="Описание",
									type="string",
								),
								@OA\Property(
									property="EvnDiagPS_setTime",
									description="Описание",
									type="string",
								),
								@OA\Property(
									property="DiagSetClass_Name",
									description="Справочник диагнозов: класс диагноза, наименование",
									type="string",
								),
								@OA\Property(
									property="Diag_Code",
									description="Справочник диагнозов, код",
									type="string",
								),
								@OA\Property(
									property="Diag_Name",
									description="Справочник диагнозов, наименование",
									type="string",
								),
								@OA\Property(
									property="RecordStatus_Code",
									description="Описание",
									type="string",
								)
							)
						),
						@OA\Property(
							property="EvnDiagPSRecep",
							description="Сопутствующие диагнозы приемного отделения",
							type="array",

							@OA\Items(
								type="object",

								@OA\Property(
									property="EvnDiagPS_id",
									description="Установка диагноза в стационаре, идентификатор",
									type="integer",
								),
								@OA\Property(
									property="EvnDiagPS_pid",
									description="Описание",
									type="integer",
								),
								@OA\Property(
									property="Person_id",
									description="Справочник идентификаторов человека, Идентификатор",
									type="integer",
								),
								@OA\Property(
									property="PersonEvn_id",
									description="События по человеку, идентификатор",
									type="integer",
								),
								@OA\Property(
									property="Server_id",
									description="Описание",
									type="integer",
								),
								@OA\Property(
									property="Diag_id",
									description="Справочник диагнозов, идентификатор",
									type="integer",
								),
								@OA\Property(
									property="DiagSetPhase_id",
									description="Степень тяжести состояния пациента (OID 1.2.643.5.1.13.13.11.1006 ), идентификатор",
									type="integer",
								),
								@OA\Property(
									property="EvnDiagPS_PhaseDescr",
									description="Установка диагноза в стационаре, описание фазы",
									type="string",
								),
								@OA\Property(
									property="DiagSetClass_id",
									description="Справочник диагнозов: класс диагноза, идентификатор",
									type="integer",
								),
								@OA\Property(
									property="DiagSetType_id",
									description="Справочник диагнозов: вид диагноза, идентификатор",
									type="integer",
								),
								@OA\Property(
									property="EvnDiagPS_setDate",
									description="Описание",
									type="string",
								),
								@OA\Property(
									property="EvnDiagPS_setTime",
									description="Описание",
									type="string",
								),
								@OA\Property(
									property="DiagSetClass_Name",
									description="Справочник диагнозов: класс диагноза, наименование",
									type="string",
								),
								@OA\Property(
									property="Diag_Code",
									description="Справочник диагнозов, код",
									type="string",
								),
								@OA\Property(
									property="Diag_Name",
									description="Справочник диагнозов, наименование",
									type="string",
								),
								@OA\Property(
									property="RecordStatus_Code",
									description="Описание",
									type="string",
								)
							)
						),
						@OA\Property(
							property="EvnDiagPSDie",
							description="Описание",
							type="array",

							@OA\Items(
								type="object",
							)
						),
						@OA\Property(
							property="EvnDiagPSSect",
							description="Описание",
							type="array",

							@OA\Items(
								type="object",
							)
						),
						@OA\Property(
							property="EvnSectionGrid",
							description="Движения",
							type="array",

							@OA\Items(
								type="object",

								@OA\Property(
									property="accessType",
									description="Описание",
									type="string",
								),
								@OA\Property(
									property="EvnSection_id",
									description="Движение в отделении, идентификатор",
									type="integer",
								),
								@OA\Property(
									property="EvnSection_IsSigned",
									description="Описание",
									type="boolean",
								),
								@OA\Property(
									property="EvnSection_pid",
									description="Описание",
									type="integer",
								),
								@OA\Property(
									property="Person_id",
									description="Справочник идентификаторов человека, Идентификатор",
									type="integer",
								),
								@OA\Property(
									property="PersonEvn_id",
									description="События по человеку, идентификатор",
									type="integer",
								),
								@OA\Property(
									property="Server_id",
									description="Описание",
									type="integer",
								),
								@OA\Property(
									property="Diag_id",
									description="Справочник диагнозов, идентификатор",
									type="integer",
								),
								@OA\Property(
									property="MedStaffFact_id",
									description="Кэш мест работы, идентификатор места работы",
									type="integer",
								),
								@OA\Property(
									property="LpuSection_id",
									description="Справочник ЛПУ: отделения, идентификатор",
									type="integer",
								),
								@OA\Property(
									property="LpuSectionWard_id",
									description="Палатная структура ЛПУ, идентификатор",
									type="integer",
								),
								@OA\Property(
									property="MedPersonal_id",
									description="Кэш врачей, идентификатор медицинского работника",
									type="integer",
								),
								@OA\Property(
									property="PayType_id",
									description="Тип оплаты, идентификатор",
									type="integer",
								),
								@OA\Property(
									property="TariffClass_id",
									description="Класс тарифа, идентификатор",
									type="integer",
								),
								@OA\Property(
									property="Mes_id",
									description="справочник МЭС, идентификатор",
									type="integer",
								),
								@OA\Property(
									property="EvnSection_disDate",
									description="Описание",
									type="string",
								),
								@OA\Property(
									property="EvnSection_setDate",
									description="Описание",
									type="string",
								),
								@OA\Property(
									property="EvnSection_disTime",
									description="Описание",
									type="string",
								),
								@OA\Property(
									property="EvnSection_setTime",
									description="Описание",
									type="string",
								),
								@OA\Property(
									property="LpuSection_Name",
									description="Справочник ЛПУ: отделения, наименование",
									type="string",
								),
								@OA\Property(
									property="LpuSectionWard_Name",
									description="Палатная структура ЛПУ, наименование (номер)",
									type="string",
								),
								@OA\Property(
									property="LpuSectionProfile_Name",
									description="профиль отделения в ЛПУ, наименование",
									type="string",
								),
								@OA\Property(
									property="LpuSectionBedProfile_Name",
									description="профиль коек, наименование",
									type="string",
								),
								@OA\Property(
									property="LpuSectionProfile_id",
									description="профиль отделения в ЛПУ, идентификатор",
									type="integer",
								),
								@OA\Property(
									property="MedPersonal_Fio",
									description="Описание",
									type="string",
								),
								@OA\Property(
									property="PayType_Name",
									description="Тип оплаты, наименование",
									type="string",
								),
								@OA\Property(
									property="Diag_Code",
									description="Справочник диагнозов, код",
									type="string",
								),
								@OA\Property(
									property="Diag_Name",
									description="Справочник диагнозов, наименование",
									type="string",
								),
								@OA\Property(
									property="EvnSection_KoikoDni",
									description="Описание",
									type="string",
								),
								@OA\Property(
									property="EvnSection_KoikoDniNorm",
									description="Описание",
									type="string",
								),
								@OA\Property(
									property="LeaveType_id",
									description="тип выписки , идентификатор",
									type="integer",
								),
								@OA\Property(
									property="CureResult_Code",
									description="Итог лечения, код",
									type="string",
								),
								@OA\Property(
									property="LeaveType_Code",
									description="тип выписки , код",
									type="string",
								),
								@OA\Property(
									property="LeaveType_SysNick",
									description="тип выписки , системное наименование",
									type="string",
								),
								@OA\Property(
									property="LeaveType_Name",
									description="тип выписки , наименование",
									type="string",
								),
								@OA\Property(
									property="LpuUnitType_id",
									description="тип подразделения ЛПУ, идентификатор",
									type="integer",
								),
								@OA\Property(
									property="LpuUnitType_SysNick",
									description="тип подразделения ЛПУ, системное наименование",
									type="string",
								),
								@OA\Property(
									property="DeseaseBegTimeType_id",
									description="Время с начала заболевания , идентификатор",
									type="integer",
								),
								@OA\Property(
									property="EvnSection_IsPaid",
									description="Движение в отделении, Случай оплачен",
									type="boolean",
								),
								@OA\Property(
									property="isLast",
									description="Описание",
									type="string",
								),
								@OA\Property(
									property="EvnSection_IndexNum",
									description="Движение в отделении, Порядковый номер в рамках группировки по диагнозу",
									type="string",
								),
								@OA\Property(
									property="EvnSection_KOEF",
									description="Описание",
									type="string",
								),
								@OA\Property(
									property="Mes_rid",
									description="Описание",
									type="string",
								),
								@OA\Property(
									property="Mes_Code",
									description="справочник МЭС, код",
									type="string",
								),
								@OA\Property(
									property="MesType_id",
									description="Тип МЭС, идентификатор",
									type="integer",
								),
								@OA\Property(
									property="EvnSection_KPG",
									description="Описание",
									type="string",
								),
								@OA\Property(
									property="EvnSection_IsMultiKSG",
									description="Движение в отделении, Более одной КСГ",
									type="boolean",
								),
								@OA\Property(
									property="EvnSection_KSG",
									description="Описание",
									type="string",
								)
							)
						)
					)
				)
			)
		)
	)
	 */
	function mLoadEvnPSEditForm_get() {
		$data = $this->ProcessInputData('mLoadEvnPSEditForm', false, true);
		$result = $this->dbmodel->mLoadEvnPSEditForm($data);

		// #180334 добить параметры: EvnDiagPSHosp, EvnDiagPSRecep
		if(!empty($result) && is_array($result)){
			// EvnDiagPSHosp - сопутствующие диагнозы направившего заведения
			$result['EvnDiagPSHosp'] = $this->EvnDiag_model->loadEvnDiagPSGrid(array(
				'class' => 'EvnDiagPSHosp',
				'EvnDiagPS_pid' => $result['EvnPS_id'],
				'Lpu_id' => $data['Lpu_id']
			));

			// EvnDiagPSRecep - сопутствующие диагнозы приемного отделения
			$result['EvnDiagPSRecep'] = $this->EvnDiag_model->loadEvnDiagPSGrid(array(
				'class' => 'EvnDiagPSRecep',
				'EvnDiagPS_pid' => $result['EvnPS_id'],
				'Lpu_id' => $data['Lpu_id']
			));

			// EvnSectionGrid - движения
			$result['EvnSectionGrid'] = $this->EvnSectionModel->loadEvnSectionGrid(array(
				'EvnSection_pid' => $data['EvnPS_id'],
				'Lpu_id' => $data['Lpu_id'],
				'session' => $data['session']
			));
		}

		$response = array('error_code' => 0,'data' => $result);
		$this->response($response);
	}
}