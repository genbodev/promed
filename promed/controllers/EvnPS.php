<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnPS - контроллер для работы с картами выбывшего из стационара (КВС)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			Stac
* @access			public
* @copyright		Copyright (c) 2009 Swan Ltd.
* @author	Stas Bykov aka Savage (savage1981@gmail.com)
* @version			03.02.2012
 * @property EvnPS_model $dbmodel
 * @property EvnPS_model $newEvnPS
 * @property EvnSection_model $EvnSection
 * @property EvnSection_model $newEvnSection
 * @property EvnLeaveAbstract_model $elmodel
*/

class EvnPS extends swController {
	public $inputRules = array(
		'loadWorkPlaceSprst' => array(
			array('field' => 'EvnPS_NumCard','label' => 'КВС','rules' => 'trim','type' => 'string')
			,array('field' => 'LpuSection_id','label' => 'КВС','rules' => '','type' => 'id')
			,array('field' => 'beg_date','label' => 'КВС','rules' => 'trim','type' => 'date')
			,array('field' => 'end_date','label' => 'КВС','rules' => 'trim','type' => 'date')
			,array('field' => 'Person_Surname','label' => 'КВС','rules' => 'trim','type' => 'string')
			,array('field' => 'Person_Firname','label' => 'КВС','rules' => 'trim','type' => 'string')
			,array('field' => 'Person_Secname','label' => 'КВС','rules' => 'trim','type' => 'string')
			,array('field' => 'Person_Birthday','label' => 'КВС','rules' => 'trim','type' => 'date'),
			array('field' => 'PSNumCard','label' => 'КВС','rules' => 'trim','type' => 'string')
		),
		'getEvnPSInfoForEvnPL' => array(
			array('field' => 'EvnPS_id', 'label' => 'EvnPS_id', 'rules' => 'required', 'type' => 'id')
		),
		'getEvnPSNumber' => array(
			array('field' => 'year','label' => 'Год','rules' => '','type' => 'int')
		),
		'checkEvnPSBirth' => array(
			array('field' => 'EvnPS_id','label' => 'EvnPS_id','rules' => '','type' => 'id')
		),
		'checkEvnPSChild' => array(
			array('field' => 'Person_id','label' => 'EvnPS_id','rules' => '','type' => 'id')
		),
		'checkEvnPSSectionAndDateEqual' => array(
			array('field' => 'EvnPS_id','label' => 'EvnPS_id','rules' => 'required','type' => 'id'),
			array('field' => 'LpuSection_eid','label' => 'Отделение куда госпитализирован','rules' => 'required','type' => 'id'),
			array('field' => 'EvnPS_OutcomeDate','label' => 'Дата исхода','rules' => 'required','type' => 'date'),
			array('field' => 'EvnPS_OutcomeTime','label' => 'Время исхода','rules' => 'required','type' => 'string')
		),
		'checkEvnSectionSectionAndDateEqual' => array(
			array('field' => 'EvnSection_id','label' => 'EvnSection_id','rules' => 'required','type' => 'id'),
			array('field' => 'LpuSection_id','label' => 'Отделение куда госпитализирован','rules' => 'required','type' => 'id'),
			array('field' => 'EvnSection_setDate','label' => 'Дата исхода','rules' => 'required','type' => 'date'),
			array('field' => 'EvnSection_setTime','label' => 'Время исхода','rules' => 'required','type' => 'string')
		),
		'beforeOpenEmk' => array(
			array('field' => 'Person_id','label' => 'Человек','rules' => 'required','type' => 'id')
		),
		'setActiveCall' => array(
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор КВС',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getEvnPSPayTypeSysNick' => array(
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор КВС',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'printEvnPS' => array(
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор КВС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Parent_Code',
				'label' => 'Код вызвавшей кнопки печати',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'KVS_Type',
				'label' => 'Тип печати КВС',
				'rules' => '',
				'type'  => 'string'
			),
			array(
				'field' => 'EvnSection_id',
				'label' => 'ID движения',
				'rules' => '',
				'type'  => 'id'
			)
		),
		'printEvnPLRefuse' => array(
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор КВС',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'setTransmitAmbulance' => array(
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор КВС',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'checkSelfTreatment' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'CheckEvnPSDie' => array(
			array(
				'field' => 'EvnPS_disDate',
				'label' => 'Дата закрытия КВС',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'LeaveType_id',
				'label' => 'Исход госпитализации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор карты выбывшего из стационара',
				'rules' => '',
				'type' => 'id'
			)
		),		
		'saveEvnPSWithPrehospWaifRefuseCause' => array(
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор КВС',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PrehospWaifRefuseCause_id',
				'label' => 'Причина отказа в госпитализации',
				'rules' => '',
				'type' => 'id', 
				'default' => null
			),
			array(
				'field' => 'LeaveType_prmid',
				'label' => 'Исход пребывания в приемном отделении',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ResultClass_id',
				'label' => 'Исход',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ResultDeseaseType_id',
				'label' => 'Результат обращения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionProfile_id',
				'label' => 'Профиль',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Код посещения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LeaveType_fedid',
				'label' => 'Фед. результат',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ResultDeseaseType_fedid',
				'label' => 'Фед. исход',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsTransfCall',
				'label' => 'Передан активный вызов',
				'rules' => '',
				'type' => 'id',
				'default' => null
			)
		),
		'checkDirHospitalize' => array(
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Идентификатор направления',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'getLastFluorographyDate' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveEvnPSWithLeavePriem' => array(
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор карты выбывшего из стационара',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Основной диагноз приемного отделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор состояния пациента',
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
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение ("Госпитализирован в")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsTransfCall',
				'label' => 'Передан активный вызов',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrehospWaifRefuseCause_id',
				'label' => 'Отказ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedicalCareFormType_id',
				'label' => 'Форма помощи',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LeaveType_prmid',
				'label' => 'Исход пребывания в приемном отделении',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ResultClass_id',
				'label' => 'Исход',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ResultDeseaseType_id',
				'label' => 'Результат обращения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionProfile_id',
				'label' => 'Профиль',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Код посещения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DeseaseType_id',
				'label' => 'Характер',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LeaveType_fedid',
				'label' => 'Фед. результат',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ResultDeseaseType_fedid',
				'label' => 'Фед. исход',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadScalesByCmpCallCardId' => array(
			array(
				'field' => 'CmpCallCard_id',
				'label' => 'Номер карты вызова',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadWorkPlacePriem' => array(
			array(
				'field' => 'date',
				'label' => 'Дата приема',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnDirection_isConfirmed',
				'label' => 'Подтверждение госпитализации',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Приемное отделение',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SecName',
				'label' => 'Отчество',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_BirthDay',
				'label' => 'Дата рождения',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnQueueShow_id',
				'label' => 'Очередь',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnDirectionShow_id',
				'label' => 'План госпитализаций',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PrehospStatus_id',
				'label' => 'Статус',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'isAll',
				'label' => 'Направления по всей МО',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PSNumCard',
				'label' => 'Номер КВС',
				'rules' => '',
				'type' => 'int'
			),
		),
		'setEvnPSPrehospAcceptRefuse' => array(
			array(
				'field' => 'EvnPS_IsPrehospAcceptRefuse',
				'label' => 'Отказ в подтверждении госпитализации',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор карты выбывшего из стационара',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор движения',
				'rules' => '',
				'type' => 'id'
			)
		),
		'copyEvnPS' => array(
			array(
				'field' => 'date',
				'label' => 'Дата',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор карты выбывшего из стационара',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'vizit_direction_control_check',
				'label' => 'Контроль пересечения движения и посещения',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'vizit_kvs_control_check',
				'label' => 'Контроль пересечения посещения с КВС',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreParentEvnDateCheck',
				'label' => 'Признак игнорирования проверки периода выполенения услуги',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreEvnPSDoublesCheck',
				'label' => 'Проверять КВС на дубли',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreEvnPSTimeDeseaseCheck',
				'label' => 'Проверять заполнения поля «Время с начала заболевания»',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreUslugaComplexTariffCountCheck',
				'label' => 'Признак игнорирования проверки количества тарифов на услуге',
				'rules' => '',
				'type' => 'int'
			)
		),
		'signLeaveEvent' => array(
			array('field' => 'parentClass','label' => 'parentClass','rules' => 'required','type' => 'string'),
			array('field' => 'LeaveType_id','label' => 'Исход госпитализации','rules' => '','type' => 'id'),
			array('field' => 'LeaveEvent_id','label' => 'Идентификатор события исхода','rules' => 'required','type' => 'id'),
			array('field' => 'LeaveEvent_pid','label' => 'Идентификатор движения','rules' => 'required','type' => 'id'),
			array('field' => 'LeaveEvent_rid','label' => 'Идентификатор карты выбывшего из стационара','rules' => 'required','type' => 'id')
		),
		'loadEvnPSEditForm' => array(
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор карты выбывшего из стационара',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'delDocsView',
				'label' => 'Просмотр удаленных документов',
				'rules' => '',
				'type' => 'id'
			)
		),
		'RunReportsInjuryJournal' => array(
			array(
				'field' => 'kindJournal_id',
				'label' => 'Вид журнала',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'begDate',
				'label' => 'Дата начала',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'endDate',
				'label' => 'Дата окончания',
				'rules' => 'required',
				'type' => 'date'
			)
		),
		'loadEvnPSStreamList' => array(
			array(
				'field' => 'begDate',
				'label' => 'Дата начала',
				'rules' => '',
				'type' => 'date',
				'default' => null
			),
			array(
				'field' => 'endDate',
				'label' => 'Дата окончания',
				'rules' => '',
				'type' => 'date',
				'default' => null
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Лимит записей',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Начальная запись',
				'rules' => '',
				'type' => 'int'
			)
		),
		'loadEvnPSList' => array(
			// Возможно, нужно будет еще добавить даты
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор КВС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadLeaveInfoGrid' => array(
			array(
				'field' => 'EvnDie_id',
				'label' => 'Идентификатор случая смерти',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnLeave_id',
				'label' => 'Идентификатор случая выписки из стационара',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnOtherLpu_id',
				'label' => 'Идентификатор перевода в другое ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnOtherSection_id',
				'label' => 'Идентификатор перевода в другое отделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnOtherSectionBedProfile_id',
				'label' => 'Идентификатор перевода на другой профиль коек',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnOtherStac_id',
				'label' => 'Идентификатор перевода в стационар другого типа',
				'rules' => '',
				'type' => 'id'
			)
		),
		'saveEvnPS' => array(
            array(
                'field' => 'checkEvnPSPersonNewbornBirthSpecStacConnect',
                'label' => 'checkEvnPSPersonNewbornBirthSpecStacConnect',
                'rules' => '',
                'type' => 'string'
            ),
			array(
				'field' => 'from',
				'label' => 'from',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'childPS',
				'label' => 'childPS',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'PrehospStatus_id',
				'label' => 'Статус',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TimetableStac_id', // Для АРМ приемного
				'label' => 'Бирка',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_eid',
				'label' => 'Отделение ("Госпитализирован в")', // Для АРМ приемного
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionWard_id',
				'label' => 'Палата', // Для АРМ приемного
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionBedProfile_id',
				'label' => 'Профиль коек', // Для АРМ приемного
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionBedProfileLink_id',
				'label' => 'Профиль коек (фед)', // Для АРМ приемного
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_aid',
				'label' => 'Основной диагноз (паталого-анатомический)',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_did',
				'label' => 'Основной диагноз направившего учреждения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_eid',
				'label' => 'Внешняя причина',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TraumaCircumEvnPS_Name',
				'label' => 'Обстоятельства получения травмы',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'TraumaCircumEvnPS_setDTDate',
				'label' => 'Дата, время получения травмы',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'TraumaCircumEvnPS_setDTTime',
				'label' => 'Дата, время получения травмы',
				'rules' => '',
				'type' => 'time'
			),
			array(
				'field' => 'DiagSetPhase_did',
				'label' => 'Состояние пациента при направлении',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_PhaseDescr_did',
				'label' => 'Расшифровка',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_pid',
				'label' => 'Основной диагноз приемного отделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DiagSetPhase_pid',
				'label' => 'Состояние пациента при поступлении',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DiagSetPhase_aid',
				'label' => 'Состояние пациента при выписке',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_PhaseDescr_pid',
				'label' => 'Расшифровка',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnQueue_id',
				'label' => 'Идентификатор очереди',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Идентификатор электронного направления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_Num',
				'label' => 'Номер направления',
				'rules' => 'max_length[16]',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirection_setDate',
				'label' => 'Дата направления',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPS_CodeConv',
				'label' => 'Код',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnPS_disDate',
				'label' => 'Дата закрытия КВС',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPS_disTime',
				'label' => 'Время закрытия КВС',
				'rules' => '',
				'type' => 'time'
			),
			array(
				'field' => 'EvnPS_HospCount',
				'label' => 'Количество госпитализаций',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор карты выбывшего из стационара',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsCont',
				'label' => 'Переведен',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsDiagMismatch',
				'label' => 'Несовпадение диагноза',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsImperHosp',
				'label' => 'Несвоевременность госпитализации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsShortVolume',
				'label' => 'Недостаточный объем клинико-диагностического обследования',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsNeglectedCase',
				'label' => 'Случай запущен',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsPLAmbulance',
				'label' => 'Талон передан на ССМП',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsPrehospAcceptRefuse',
				'label' => 'Отказ в подтверждении госпитализации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsTransfCall',
				'label' => 'Передан активный вызов',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsUnlaw',
				'label' => 'Противоправная',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsUnport',
				'label' => 'Нетранспортабельность',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsWaif',
				'label' => 'Беспризорный',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsWithoutDirection',
				'label' => 'Без электронного направления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsWrongCure',
				'label' => 'Неправильная тактика лечения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_NumCard',
				'label' => 'Номер карты',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnPS_NumConv',
				'label' => 'Номер наряда',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnPS_PrehospAcceptRefuseDT',
				'label' => 'Дата отказа в подтверждении госпитализации',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPS_PrehospWaifRefuseDT',
				'label' => 'Дата отказа приёма',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPS_setDate',
				'label' => 'Дата поступления',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPS_setTime',
				'label' => 'Время поступления',
				'rules' => '',
				'type' => 'time'
			),
			array(
				'field' => 'EvnPS_OutcomeDate',
				'label' => 'Дата исхода из приемного отделения',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPS_OutcomeTime',
				'label' => 'Время исхода из приемного отделения',
				'rules' => '',
				'type' => 'time'
			),
			array(
				'field' => 'EvnPS_TimeDesease',
				'label' => 'Время с начала заболевания',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Okei_id',
				'label' => 'Единица измерени времени (с начала заболевания)',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LeaveType_prmid',
				'label' => 'Исход пребывания в приемном отделении',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_did',
				'label' => 'ЛПУ ("Госпитализация")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_did',
				'label' => 'Отделение ("Госпитализация")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_pid',
				'label' => 'Приемное отделение ("Приемное")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_pid',
				'label' => 'Врач приемного отделения ("Приемное")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_pid',
				'label' => 'Рабочее место врача приемного отделения ("Приемное")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Org_did',
				'label' => 'Организация ("Госпитализация")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OrgMilitary_did',
				'label' => 'Военкомат ("Госпитализация")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор состояния пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PrehospArrive_id',
				'label' => 'Кем доставлен',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrehospDirect_id',
				'label' => 'Кем направлен',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrehospToxic_id',
				'label' => 'Состояние опьянения',
				'rules' => '',
				'type' => 'id'
			),
            array(
                'field' => 'LpuSectionTransType_id',
                'label' => 'Вид транспортировки',
                'rules' => '',
                'type' => 'id'
            ),
            array(
				'field' => 'PrehospTrauma_id',
				'label' => 'Травма',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrehospType_id',
				'label' => 'Тип госпитализации',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PrehospWaifRefuseCause_id',
				'label' => 'Отказ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ResultClass_id',
				'label' => 'Исход',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ResultDeseaseType_id',
				'label' => 'Результат обращения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LeaveType_fedid',
				'label' => 'Фед. результат',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ResultDeseaseType_fedid',
				'label' => 'Фед. исход',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'UslugaComplex_id',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionProfile_id',
				'label' => 'LpuSectionProfile_id',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrehospWaifArrive_id',
				'label' => 'PrehospWaifArrive_id',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrehospWaifReason_id',
				'label' => 'PrehospWaifReason_id',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'addEvnSection',
				'label' => 'Флаг добавления движения',
				'rules' => '',
				'type'	=> 'string'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Идентификатор врача', // для добавления из ЭМК
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Идентификатор места работы врача', // для добавления из ЭМК
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EntranceModeType_id',
				'label' => 'Вид транспортировки',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpCallCard_id',
				'label' => 'Идентфикатор талона вызова',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_HTMBegDate',
				'label' => 'Дата выдачи талона на ВМП',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPS_HTMHospDate',
				'label' => 'Дата планируемой госпитализации (ВМП)',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPS_HTMTicketNum',
				'label' => 'Номер талона на ВМП',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'DeseaseType_id',
				'label' => 'Характер',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TumorStage_id',
				'label' => 'Стадия выявленного ЗНО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsZNO',
				'label' => 'Подозрение на ЗНО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_spid',
				'label' => 'Подозрение на диагноз',
				'rules' => '',
				'type' => 'id'
			),
            array(
                'field' => 'FamilyContact_msgDate',
                'label' => 'Дата сообщения родственнику',
                'rules' => '',
                'type' => 'date'
            ),
            array(
                'field' => 'FamilyContact_msgTime',
                'label' => 'Время сообщения родственнику',
                'rules' => '',
                'type' => 'time'
            ),
            array(
                'field' => 'FamilyContact_FIO',
                'label' => 'ФИО родственника',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'FamilyContact_Phone',
                'label' => 'Телефон родственника',
                'rules' => '',
                'type' => 'string'
            ),
			[
				'field' => 'FamilyContactPerson_id',
				'label' => 'Идентификатор представителя',
				'rules' => '',
				'type' => 'id'
			],
            array(
            	'field' => 'RepositoryObserv_FluorographyDate',
            	'label' => 'Дата флюорографии',
            	'rules' => '',
            	'type' => 'date'
            ),
			array(
				'field' => 'vizit_direction_control_check',
				'label' => 'Проверка пересечения КВС с ТАП',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreEvnPSDoublesCheck',
				'label' => 'Проверка пересечения КВС',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreEvnPSTimeDeseaseCheck',
				'label' => 'Проверять заполнения поля «Время с начала заболевания»',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Pediculos_id',
				'label' => 'идентифкатор',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PediculosDiag_id',
				'label' => 'педикулёз идентифкатор диагноза',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ScabiesDiag_id',
				'label' => 'чесотка идентифкатор диагноза',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Pediculos_SanitationDT',
				'label' => 'дата время санитарной обработки',
				'rules' => '',
				'type' => 'datetime'
			),
			array(
				'field' => 'Pediculos_isSanitation',
				'label' => 'Санитарная обработка',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'isPediculos',
				'label' => 'педикулёз',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'isScabies',
				'label' => 'чесотка',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'Pediculos_isPrint',
				'label' => 'признак печати уведомления',
				'rules' => '',
				'default' => 1,
				'type' => 'int'
			),
			array(
				'field' => 'checkMoreThanOneEvnPSToEvnDirection',
				'label' => 'Проверять привязку к одному направлению многиx КВС',
				'rules' => '',
				'type' => 'int'
			)
		),
		'loadHospitalizationsGrid' => array(
			array('default' => 100,'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
			array('default' => 0,'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int'),
			array('default' => '','field' => 'EvnPS_setDateTime_Start','label' => 'Дата поступления с','rules' => 'trim','type' => 'date'),
			array('default' => '','field' => 'EvnPS_setDateTime_End','label' => 'Дата поступления по','rules' => 'trim','type' => 'date'),
			array('default' => 0,'field' => 'isEvnDirection','label' => 'C направлением','rules' => '','type' => 'id'),
			array('default' => 0,'field' => 'PrehospType_id','label' => 'Тип госпитализации','rules' => '','type' => 'id'),
			array('default' => 0,'field' => 'PrehospArrive_id','label' => 'Кем доставлен','rules' => '','type' => 'id'),
			array('default' => 0,'field' => 'Org_oid','label' => '','rules' => 'ЛПУ, куда госпитализирован','type' => 'id'),
			array('default' => '','field' => 'EvnPS_disDateTime_Start','label' => 'Дата выписки с','rules' => 'trim','type' => 'date'),
			array('default' => '','field' => 'EvnPS_disDateTime_End','label' => 'Дата выписки по','rules' => 'trim','type' => 'date'),
			array('default' => 0,'field' => 'LeaveType_id','label' => 'Исход госпитализации','rules' => '','type' => 'id'),
			array('default' => 0,'field' => 'ResultDesease_id','label' => 'Результат госпитализации','rules' => '','type' => 'id'),
			array('default' => 0,'field' => 'Lpu_aid','label' => 'ЛПУ прикрепления','rules' => '','type' => 'id'),
			array('default' => 0,'field' => 'LpuRegion_id','label' => 'Участок','rules' => '','type' => 'id'),
			array('default' => 0,'field' => 'LeaveCause_id','label' => 'Прич. вып. / перевода','rules' => '','type' => 'id'),
			array('default' => null,'field' => 'MedPersonal_id','label' => 'Врач','rules' => '','type' => 'id'),
			array('default' => false, 'field' => 'NotLeave','label'=>'Только не выписанные','rules'=>'','type'=>'string'),
			array('default' => '','field' => 'Person_Surname','label' => 'Имя','rules' => 'trim','type' => 'string'),
			array('default' => '','field' => 'Person_Firname','label' => 'Фамилия','rules' => 'trim','type' => 'string'),
			array('default' => '','field' => 'Person_Secname','label' => 'Отчество','rules' => 'trim','type' => 'string'),
			array('default' => '','field' => 'Person_Birthday','label' => 'Дата Рождения','rules' => 'trim','type' => 'date'),
			array('default' => '','field' => 'Person_Birthday_Range_0','label' => 'Дата рождения - С','rules' => 'trim','type' => 'date'),
			array('default' => '','field' => 'Person_Birthday_Range_1','label' => 'Дата рождения - до','rules' => 'trim','type' => 'date'),
			array(
				'field' => 'EvnPS_IsNeglectedCase',
				'label' => 'Случай запущен',
				'rules' => 'trim',
				'type' => 'id'
			),
                        array('default' => '','field' => 'SignalInfo','label' => 'Сигнальная информация','rules' => '','type' => 'id')   
		),
		'checkReceptionTime' => array(
			array('field' => 'Person_id','label' => 'Идентификатор пациента','rules' => 'required','type' => 'id')
			//,array('field' => 'Server_id','label' => 'Идентификатор сервера','rules' => 'required','type' => 'id')
			//,array('field' => 'PersonEvn_id','label' => 'Идентификатор состояния пациента','rules' => 'required','type' => 'id')
		),
		'printPatientRefuse' => array(
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор карты выбывшего из стационара',
				'rules' => '',
				'type' => 'id'
			)
		),
		'printEvnPSPrehospWaifRefuseCause' => array(
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор карты выбывшего из стационара',
				'rules' => '',
				'type' => 'id'
			)
		),
		'exportToDbfBedFond' => array(
			array(
				'field' => 'begDate',
				'label' => 'Дата начала',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'endDate',
				'label' => 'Дата окончания',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'OnlyBedFond',
				'label' => 'Выгрузить только коечный фонд',
				'rules' => '',
				'type' => 'int'
			)
		),
		'exportHospDataForTfomsToXml' => array(
			array(
				'field' => 'Date',
				'label' => 'Дата',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'ARMType',
				'label' => 'Тип АРМа',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'ExportLpu_id',
				'label' => 'МО',
				'rules' => '',
				'type' => 'id'
			),
		),
		'importHospDataFromTfomsXml' => array(
			array(
				'field' => 'ImportFile',
				'label' => 'Файл',
				'rules' => '',
				'type' => 'string'
			)
		),
        'getMorbusCrazy' => array(
            array(
                'field' => 'EvnPS_id',
                'label' => 'Идентификатор КВС',
                'rules' => 'required',
                'type'  => 'int'
            )
        ),
        'getLastEvnPS' => array(
            array(
                'field' => 'LpuSection_id',
                'label' => 'Идентификатор отделения',
                'rules' => '',
                'type'  => 'id'
            ),
			array(
                'field' => 'Person_id',
                'label' => 'Идентификатор пациента',
                'rules' => '',
                'type'  => 'id'
            )
        ),
		'getInfoKVSfromERSB' => array(
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор',
				'rules' => 'required',
				'type'  => 'id'
			)
		),
		'getInfoEvnPSfromBg' => array (
			array ('field' => 'id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id')
		),
		'getEcgResult'=> array(
			array(
				'field' => 'EvnUsluga_id',
				'label' => 'ИД услуги',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'controlSavingForm_DepartmentSelectionLPU' => array (
			array ('field' => 'EvnPS_id', 'label' => 'Идентификатор КВС', 'rules' => 'required', 'type' => 'id')
		),
		'getMedicalCareBudgType' => array (
			array('field' => 'EvnPS_setDate', 'label' => 'Дата начала госпитализации', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'EvnPS_disDate', 'label' => 'Дата окончания госпитализации', 'rules' => '', 'type' => 'date'),
			array('field' => 'LeaveType_SysNick', 'label' => 'Исход госпитализации', 'rules' => '', 'type' => 'string'),
			array('field' => 'PayType_SysNick', 'label' => 'Вид оплаты', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'LpuUnitType_SysNick', 'label' => 'Тип группы отделения', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'HTMedicalCareClass_id', 'label' => 'Метод ВМП', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionProfile_id', 'label' => 'Идентификатор профиля отделения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Diag_id', 'label' => 'Идентификатор диагноза', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
		),
		'getBedList' => array (
			array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Пациент', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'GetBed_id', 'label' => 'Профиль койки', 'rules' => '', 'type' => 'id'),
		),
		'getInfoPanelAdditionalInformation' => array(
			array('field' => 'EvnPS_id', 'label' => 'Идентификатор КВС', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'fields', 'label' => 'JSON-массив искомых данных', 'rules' => '', 'type' => 'json_array'),
			array('field' => 'Person_id', 'label' => 'Пациент', 'rules' => 'required', 'type' => 'id'),
		),
		'getFirstProfileEvnSectionId' => [
			['field' => 'EvnPS_id', 'label' => 'Идентификатор КВС', 'rules' => 'required', 'type' => 'id']
		]
	);

    private $bedFondExportSettings = array();

	/**
	 * @param $dbf_name
	 * @param array $fields
	 * @return string
	 */
	private function getExportQuery($dbf_name, $fields = array()) {
		if ( is_array($fields) && count($fields) > 0 ) {
			$selectData = '[' . implode('],[', $fields) . ']';
		}
		else {
			$selectData = '*';
		}

        return 'SELECT ' . $selectData . ' FROM r63.Export'.$dbf_name.'(:Lpu_id, :begDate, :endDate)';
    }

	/**
	 * for export
 	 */
	private function declare_bedFondExportSettings(){
        $this->bedFondExportSettings = array(
            'perm' => array(
                'dbf_files' => array(
                    'bedfond' => array(
                        'fields_dbf' => array(
                            array("STARTDATE", "D", 8),
                            array("ENDDATE", "D", 8),
                            array("LPU", "N", 5, 0),
                            //array("SKIND", "N", 2, 0),
                            array("BEDPROF", "N", 3, 0),
                            array("FACT_BED", "N", 5, 0),
                            array("AVG_BED", "N", 8, 2),
                            array("ILL_BEG", "N", 6, 0),
                            array("ENTERRED", "N", 6, 0),
                            array("ENT_VILL", "N", 6, 0),
                            array("ENT_BABY", "N", 6, 0),
                            array("ENT_OLDMAN", "N", 6, 0),
                            array("ILL_IN", "N", 6, 0),
                            array("ILL_TO", "N", 6, 0),
                            array("DRAWN", "N", 6, 0),
                            array("ILL_OUT", "N", 6, 0),
                            array("ILL_OUT_D", "N", 6, 0),
                            array("DEAD", "N", 6, 0),
                            array("ILL_END", "N", 6, 0),
                            array("BEDDAYS", "N", 7, 0),
                            array("BEDDAYS13", "N", 7, 0),
                            array("BD_VILL", "N", 7, 0),
                            array("BD_MOTHER", "N", 7, 0)
                        ),
                        'query' => "
        declare @Lpu_id bigint = :Lpu_id
                    declare @begDate datetime = :begDate
                    declare @endDate datetime = :endDate

                    declare @StatTimeShift time = case when dbo.IsStat(@Lpu_id) = 1 then :time1 else :time2 end;

                    declare @StatBegDate datetime;
                    declare @StatEndDate datetime;

                    set @StatBegDate = @begDate + @StatTimeShift;
                    set @StatEndDate = dateadd(day, 1, @endDate + @StatTimeShift);

                    with
                    PlanKD as
                    (
                    select
                    LpuSection.Lpu_id,
                    LpuSectionBedProfile.LpuSectionBedProfile_id,
                    LpuSectionBedProfile.LpuSectionBedProfile_Code,
                    sum(LpuSectionBedPlan.LpuSectionBedPlan_Plan * LpuSectionBedState.LpuSectionBedState_Plan) as zakaz
                    from v_LpuSection LpuSection  with (nolock)
                    inner join LpuUnit with (nolock) on LpuUnit.LpuUnit_id = LpuSection.LpuUnit_id
                    inner join LpuSectionBedProfile  with (nolock)  on LpuSectionBedProfile.LpuSectionBedProfile_id = LpuSection.LpuSectionBedProfile_id
                    inner join CalendarMonth  with (nolock)  on CalendarMonth.CalendarMonth_begDate between @begDate and @endDate
                    outer apply
                    (
                    select top 1 LpuSectionBedPlan_Plan
                    from LpuSectionBedPlan  with (nolock)
                    where LpuSectionBedPlan.LpuSection_id = LpuSection.LpuSection_id
                        and LpuSectionBedPlan_begDate <= CalendarMonth_endDate
                    order by LpuSectionBedPlan_begDate desc
                    ) LpuSectionBedPlan
                    outer apply
                    (
                    select top 1 LpuSectionBedState_Plan
                    from LpuSectionBedState  with (nolock)
                    where LpuSectionBedState.LpuSection_id = LpuSection.LpuSection_id
                        and LpuSectionBedState_begDate <= CalendarMonth_endDate
                    order by LpuSectionBedState_begDate desc
                    ) LpuSectionBedState
                    where LpuSection.Lpu_id = @Lpu_id
                    and (LpuSection.LpuSection_disDate >= @begDate or LpuSection.LpuSection_disDate is Null)
                    group by LpuSection.Lpu_id,
                    LpuSectionBedProfile.LpuSectionBedProfile_id,
                    LpuSectionBedProfile.LpuSectionBedProfile_Code
                    ),

                    KoikiEnd as
                    (
                    select
                    LpuSectionBedProfile.LpuSectionBedProfile_id,
                    LpuSectionBedProfile.LpuSectionBedProfile_Code,
                    sum(LpuSectionBedState.LpuSectionBedState_Plan) as s1
                    from v_LpuSection LpuSection
                    inner join LpuUnit with (nolock) on LpuUnit.LpuUnit_id = LpuSection.LpuUnit_id
                    inner join LpuSectionBedProfile on LpuSectionBedProfile.LpuSectionBedProfile_id = LpuSection.LpuSectionBedProfile_id
                    outer apply
                    (
                    select top 1 LpuSectionBedState_Plan
                    from LpuSectionBedState  with (nolock)
                    where LpuSectionBedState.LpuSection_id = LpuSection.LpuSection_id
                    and LpuSectionBedState_begDate <= @endDate
                    order by LpuSectionBedState_begDate desc
                    ) LpuSectionBedState
                    where LpuSection.Lpu_id = @Lpu_id
                    and (LpuSection.LpuSection_disDate >= @begDate or LpuSection.LpuSection_disDate is Null)
                    group by LpuSectionBedProfile.LpuSectionBedProfile_id,
                    LpuSectionBedProfile.LpuSectionBedProfile_Code
                    ),

                    SredKoiki as
                    (
                    select
                    LpuSectionBedProfile.LpuSectionBedProfile_id,
                    LpuSectionBedProfile.LpuSectionBedProfile_Code,
                    cast(sum(cast(LpuSectionBedState.LpuSectionBedState_Plan as decimal(10, 2))) /
                        (DATEDIFF(month, @begDate, @endDate) + 1) as decimal(10, 2)) as f4sum
                    from v_LpuSection LpuSection  with (nolock)
                    inner join LpuUnit with (nolock) on LpuUnit.LpuUnit_id = LpuSection.LpuUnit_id
                    inner join LpuSectionBedProfile on LpuSectionBedProfile.LpuSectionBedProfile_id = LpuSection.LpuSectionBedProfile_id
                    inner join CalendarMonth on CalendarMonth.CalendarMonth_begDate between @begDate and @endDate
                    outer apply
                    (
                    select top 1 LpuSectionBedState_Plan
                    from LpuSectionBedState  with (nolock)
                    where LpuSectionBedState.LpuSection_id = LpuSection.LpuSection_id
                        and LpuSectionBedState_begDate <= CalendarMonth_endDate
                    order by LpuSectionBedState_begDate desc
                    ) LpuSectionBedState
                    where LpuSection.Lpu_id = @Lpu_id
                    and (LpuSection.LpuSection_disDate >= @begDate or LpuSection.LpuSection_disDate is Null)
                    group by
                    LpuSectionBedProfile.LpuSectionBedProfile_id,
                    LpuSectionBedProfile.LpuSectionBedProfile_Code
                    ),

                    tr as
                    (
                    select
                    t.LpuSectionBedProfile_id as LpuSectionBedProfile_id,
                    SUM(t.v4) as v19,
                    SUM(t.v18) as v20,
                    SUM(t.v19) as v21
                    from
                      (select Calendar.Calendar_Date,
                               LpuSectionBedProfile.LpuSectionBedProfile_id,
                        count (distinct case  when
                           EvnSection.EvnSection_setDT <= Calendar.Calendar_Date + @StatTimeShift
                                 and (EvnSection.EvnSection_disDT > Calendar.Calendar_Date + @StatTimeShift
                                 or EvnSection.EvnSection_disDT is null)
                                 then EvnPS_id end) as v4,
                        count (distinct case  when
                            EvnSection.EvnSection_setDT <= dateadd(day,1,Calendar.Calendar_Date + @StatTimeShift)
                                 and (EvnSection.EvnSection_disDT > dateadd(day,1,Calendar.Calendar_Date + @StatTimeShift)
                                 or EvnSection.EvnSection_disDT is null)
                                 then EvnSection_id end) as v18,
                        count (distinct case  when
                            EvnSection.EvnSection_setDT <= dateadd(day,1,Calendar.Calendar_Date + @StatTimeShift)
                                 and (EvnSection.EvnSection_disDT > dateadd(day,1,Calendar.Calendar_Date + @StatTimeShift)
                                 or EvnSection.EvnSection_disDT is null)
                                 and (PAddress.KlareaType_id=2)
                                 then EvnSection_id end) as v19
                           from v_LpuSection LpuSection with (nolock)
                           inner join LpuUnit with (nolock) on LpuSection.LpuUnit_id = LpuUnit.LpuUnit_id
                           inner join LpuSectionBedProfile with (nolock) on LpuSectionBedProfile.LpuSectionBedProfile_id = LpuSection.LpuSectionBedProfile_id
                           inner join v_EvnSection EvnSection with (nolock) on LpuSection.LpuSection_id = EvnSection.LpuSection_id
                           and EvnSection_setDT <= @StatEndDate
                           and (EvnSection_disDT > @StatBegDate
                           or EvnSection_disDT is null)
                           inner join EvnPS with (nolock) on EvnPS.EvnPS_id = EvnSection.EvnSection_pid
                           left outer join v_Person_reg_rpt Person with (nolock) on Person.PersonEvn_id=EvnSection.PersonEvn_id and EvnSection.Server_id=Person.Server_id
                           left outer join v_Address PAddress with (nolock) on PAddress.Address_id=Person.UAddress_id
                           left join Calendar  with (nolock) on Calendar.Calendar_Date between @begDate and @endDate
                           group by Calendar.Calendar_Date, LpuSectionBedProfile.LpuSectionBedProfile_id
                    ) as t
                    group by LpuSectionBedProfile_id
                    ),

                    EvnSecCount as
                    (
                    select
                    LpuSectionBedProfile.LpuSectionBedProfile_Code,
                    LpuSectionBedProfile.LpuSectionBedProfile_id,

                    count (distinct case  when
                    EvnSection.EvnSection_setDT <= @StatBegDate
                    and (EvnSection.EvnSection_disDT > @StatBegDate
                    or EvnSection.EvnSection_disDT is null)
                    then EvnPS_id end)  as v4, --состояло на начало периода

                    count (distinct case
                    when (EvnSection_Index=0)
                    and EvnPS_IsCont=1
                     and EvnSection.EvnSection_setDT > @StatBegDate
                     and EvnSection.EvnSection_setDT <= @StatEndDate
                    then EvnSection_id end) as v5, -- всего поступило

                    count (distinct case
                    when (EvnSection_Index=0)
                    and (PAddress.KlareaType_id=2)
                    and EvnPS_IsCont=1
                     and EvnSection.EvnSection_setDT > @StatBegDate
                     and EvnSection.EvnSection_setDT <= @StatEndDate
                    then EvnSection_id end) as v6, --из них сельские жители

                    count (distinct case  when (EvnPS.Person_Age between 0 and 18)  --18 включать
                     and EvnSection.EvnSection_setDT > @StatBegDate
                     and EvnSection.EvnSection_setDT <= @StatEndDate
                     and (EvnSection_Index=0)
                    and EvnPS_IsCont=1
                    then EvnSection_id end) as v7, --в том числе детей до 18 лет

                    count (distinct case
                    when  (EvnPS.Person_Age >=60)
                     and EvnSection.EvnSection_setDT > @StatBegDate
                     and EvnSection.EvnSection_setDT <= @StatEndDate
                     and (EvnSection_Index=0)
                    and EvnPS_IsCont=1
                    then EvnSection_id end) as v8, --в том лиц старше 60 лет

                    count (distinct case  when
                    (EvnSection_Index>=1 or (EvnSection_Index=0 and EvnPS_IsCont=2 and evnps.PrehospDirect_id=1))
                     and EvnSection.EvnSection_setDT > @StatBegDate
                     and EvnSection.EvnSection_setDT <= @StatEndDate
                    then EvnSection_id end) as v9, --переведено из других отделений

                    count (distinct case
                    when (EvnSection_Index<EvnSection_Count-1
                    or (EvnSection_Index=EvnSection_Count-1
                    and LeaveType.LeaveType_SysNick='section'))
                    and (EvnSection.EvnSection_disDT <= @StatEndDate)
                    and (EvnSection.EvnSection_disDT > @StatBegDate)
                    then EvnSection_id end) as v10, --переведено в другие отделения


                    count (distinct case
                    when ((LeaveType.LeaveType_Code is not null)
                    and evnSection_Index=(EvnSection_Count-1)
                    and (LeaveType.LeaveType_SysNick not in ('die','section')))
                    and (EvnSection.EvnSection_disDT > @StatBegDate)
                    and (EvnSection.EvnSection_disDT <= @StatEndDate)
                    then EvnSection_id end) as v11, --выписано всего

                    count (distinct case
                    when evnSection_Index=EvnSection_Count-1
                    and (EvnOtherStac_id is not null)
                    and LpuUnitOtherStac.LpuUnitType_SysNick='stac'
                    and (EvnSection.EvnSection_disDT > @StatBegDate)
                    and (EvnSection.EvnSection_disDT <= @StatEndDate)
                    then EvnSection_id end) as v12, -- в том числе выбыло в круглосуточные стационары

                    count(distinct case when
                    (EvnSection_Index=(EvnSection_Count-1)
                    and (LeaveType.LeaveType_SysNick='other'))
                    and (EvnSection.EvnSection_disDT > @StatBegDate)
                    and (EvnSection.EvnSection_disDT <= @StatEndDate)
                    then EvnSection_id end) as v13, --в том числе выбыло в дневные стационары

                    count (distinct case
                    when ((LeaveType.LeaveType_Code is not null)
                    and evnSection_Index=(EvnSection_Count-1)
                    and (LeaveType.LeaveType_SysNick='die'))
                    and (EvnSection.EvnSection_disDT >@StatBegDate)
                    and (EvnSection.EvnSection_disDT <= @StatEndDate)
                    then EvnSection_id end) as v14, --умерло

                    count (distinct case  when
                    EvnSection.EvnSection_setDT <= @StatEndDate
                    and (EvnSection.EvnSection_disDT >@StatEndDate
                    or EvnSection.EvnSection_disDT is null)
                    then EvnSection_id end) as v15 -- состоит на конец периода

                    from v_LpuSection LpuSection with (nolock)
                    inner join LpuUnit with (nolock) on LpuSection.LpuUnit_id = LpuUnit.LpuUnit_id
                    inner join LpuSectionBedProfile with (nolock) on LpuSectionBedProfile.LpuSectionBedProfile_id = LpuSection.LpuSectionBedProfile_id
                    inner join v_EvnSection EvnSection with (nolock) on LpuSection.LpuSection_id = EvnSection.LpuSection_id
                        and EvnSection_setDT <= @StatEndDate
                        and (EvnSection_disDT > @StatBegDate
                        or EvnSection_disDT is null)
                    inner join EvnPS with (nolock) on EvnPS.EvnPS_id = EvnSection.EvnSection_pid
                    left outer join v_Person_reg_rpt Person with (nolock) on Person.PersonEvn_id=EvnSection.PersonEvn_id and EvnSection.Server_id=Person.Server_id
                    left outer join v_Address PAddress with (nolock) on PAddress.Address_id=Person.UAddress_id
                    left outer join v_PrehospDirect as PrehospDirect with (nolock) on PrehospDirect.PrehospDirect_id = EvnPS.PrehospDirect_id
                    left outer join v_LpuSection as LpuSectionDirect with (nolock) on LpuSectionDirect.LpuSection_id = EvnPS.LpuSection_did
                    left outer join v_LpuUnit as LpuUnitDirect with (nolock) on LpuUnitDirect.LpuUnit_id = LpuSection.LpuUnit_id
                    left outer join LeaveType with (nolock) on LeaveType.LeaveType_id=EvnPS.LeaveType_id
                    left outer join v_EvnOtherStac EvnOtherStac with (nolock) on EvnOtherStac_rid=EvnPS_id
                    left outer join v_lpuUnitType LpuUnitOtherStac with (nolock) on LpuUnitOtherStac.LpuUnitType_id=EvnOtherStac.LpuUnitType_oid
                    where
                    LpuSection.Lpu_id = @Lpu_id
                    and (LpuSection.LpuSection_disDate >= @begDate or LpuSection.LpuSection_disDate is Null)
                    group by LpuSectionBedProfile.LpuSectionBedProfile_id,
                    LpuSectionBedProfile.LpuSectionBedProfile_Code
                    )

                    select
                    @BegDate as STARTDATE,
                    @EndDate as ENDDATE,
                    PlanKD.Lpu_id as LPU,
                    --LPuUnitType_Code as SKIND,
                    KoikiEnd.LpuSectionBedProfile_Code as BEDPROF,
                    cast(KoikiEnd.s1 as float) as FACT_BED, -- количество коек
                    cast(SredKoiki.f4sum as float) as AVG_BED, --средние койки
                    --cast(PlanKD.zakaz as float) as PlanKoiki,
                    v4 as ILL_BEG , --состояло на начало периода
                    v5 as ENTERRED , -- всего поступило
                    v6 as ENT_VILL, --из них сельские жители
                    v7 as ENT_BABY, --в том числе детей до 18 лет
                    v8 as ENT_OLDMAN, --в том лиц старше 60 лет
                    v9 as ILL_IN,  --переведено из других отделений
                    v10 as ILL_TO, --переведено в другие отделения
                    v11 as DRAWN, --выписано всего
                    v12 as ILL_OUT , -- в том числе выбыло в круглосуточные стационары
                    v13 as ILL_OUT_D, -- в том числе выбыло в дневные стационары
                    v14 as DEAD , -- умерло
                    v15 as ILL_END , -- состоит на конец периода
                    tr.v19 as BEDDAYS, --Количество койко-дней
                    tr.v20 as BEDDAYS13,
                    tr.v21 as BD_VILL, -- в том числе сельские жители
                    null as BD_MOTHER -- с матерями
                    from EvnSecCount with (nolock)
                    left join PlanKD with (nolock) on PlanKD.LpuSectionBedProfile_id=EvnSecCount.LpuSectionBedProfile_id
                    left join KoikiEnd with (nolock) on KoikiEnd.LpuSectionBedProfile_id=EvnSecCount.LpuSectionBedProfile_id
                    left join SredKoiki with (nolock) on SredKoiki.LpuSectionBedProfile_id=EvnSecCount.LpuSectionBedProfile_id
                    left join tr with (nolock) on tr.LpuSectionBedProfile_id = EvnSecCount.LpuSectionBedProfile_id
        "

                    ),
                ),
                'archiver' => 'zip',
            ),
        );
    }

	/**
	 * construct
 	 */
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('EvnPS_model', 'dbmodel');
        $this->declare_bedFondExportSettings();
        $this->load->helper('Text');
	}

	/**
	 * Проверка наличия поступления пациента в приемное отделение за последние 24 часа
	 */
	function checkReceptionTime() {
		$data = $this->ProcessInputData('checkReceptionTime', true);
		if($data) {
			$response = $this->dbmodel->checkReceptionTime($data);
			if ( !is_array($response) )
			{
				$this->ReturnData(array(
					'success' => false,
					'Error_Msg' => toUTF('Не удалось проверить наличия поступления пациента в приемное отделение за последние 24 часа!')
				));
				return false;
			}
			else if (count($response) > 0)
			{
				$this->ReturnData(array(
					'success' => false,
					'Alert_Msg' => toUTF('У пациента уже было обращение в приемное отделение '.$response[0]['Lpu_Nick'].' за последние 24 часа')
				));
				return false;
			}
			else
			{
				$this->ReturnData(array(
					'success' => true,
					'Error_Msg' => ''
				));
				return true;
			}
		}
		else {
			return false;
		}
	}

	/**
	 * Загрузка данных по шкалам по ид карте вызова
	 */
	public function loadScalesByCmpCallCardId()
	{
		$data = $this->ProcessInputData('loadScalesByCmpCallCardId', true);
		if ($data === false)
		{
			return false;
		}
		$response = $this->dbmodel->loadScalesByCmpCallCardId($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	*  Получение списка записей для АРМа приемного отделения стационара
	*  На выходе: JSON-строка
	*  Используется: форма АРМа приемного отделения стационара (swMPWorkPlacePriemWindow)
	*/
	function loadWorkPlacePriem() {
		$data = $this->ProcessInputData('loadWorkPlacePriem', true);
		if ($data === false)
		{
			return false;
		}
		$response = $this->dbmodel->loadWorkPlacePriem($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	*  Получение списка записей для справочного стола стационара
	*  На выходе: JSON-строка
	*  Используется: форма АРМ справочного стола стационара (swWorkPlaceStacHelpDeskWindow)
	*/
	function loadWorkPlaceSprst() {
		$data = $this->ProcessInputData('loadWorkPlaceSprst', true);
		if ($data === false)
		{
			return false;
		}
		/*
		if ( empty($data['EvnPS_NumCard']) && empty($data['LpuSection_id']) && empty($data['Person_Surname']) && empty($data['Person_Firname']) && empty($data['Person_Secname']) && empty($data['Person_Birthday']) ) {
			echo json_return_errors('Помимо диапазона дат должен быть применен ещё любой фильтр');
			return false;
		}*/
		$response = $this->dbmodel->loadWorkPlaceSprst($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	*  Установка значения признака "Отказ в подтверждении госпитализации"
	*  Входящие данные: $_POST['EvnPS_id'], $_POST['EvnPS_IsPrehospAcceptRefuse']
	*  На выходе: JSON-строка
	*  Используется: журнал госпитализаций
	*/
	function setEvnPSPrehospAcceptRefuse() {
		// Получаем входящие данные и сессионные переменные
		$data = $this->ProcessInputData('setEvnPSPrehospAcceptRefuse', true);

		if ( $data === false ) {
			return false;
		}
		

		// Добавить проверки на возможность установки или отмены признака "Отказ в подтверждении госпитализации"
		// Необходимые условия:
		// - В течение 5 дней с даты госпитализации
		// - ЛПУ направления <> ЛПУ госпитализации или направления нет
		// - Тип госпитализации "Планово"
		// - Функция доступна участковому терапевту или его завотделения, если направление выписано уч. терапевтом или направления нет.
		// - Узкому специалисту или его завотделения, если направление выписано узким специалистом.
		$response = $this->dbmodel->checkPrehospAcceptRefuseChangeAbility($data);

		if ( !is_array($response) || count($response) == 0 ) {
			echo json_return_errors('Ошибка при выполнении запроса к базе данных (проверка возможности изменения признака "Отказ в подтверждении госпитализации")');
			return false;
		}

		// Признак, что текущий врач является участковым врачом пациента
		$isMedPersonalLpuRegion = false;

		if ( !empty($data['session']['medpersonal_id']) && is_array($response['LpuRegionMedPersonalList']) && count($response['LpuRegionMedPersonalList'] > 0 )) {
			foreach ( $response['LpuRegionMedPersonalList'] as $lpuRegionArray ) {
				if ( $lpuRegionArray['MedPersonal_id'] == $data['session']['medpersonal_id'] ) {
					$isMedPersonalLpuRegion = true;
				}
			}
		}

		if ( empty($data['session']['medpersonal_id']) || ($isMedPersonalLpuRegion == false && $data['session']['medpersonal_id'] != $response['MedPersonal_did'] && $data['session']['medpersonal_id'] != $response['MedPersonal_zdid']) ) {
			echo json_return_errors('Изменение признака "Отказ в подтверждении госпитализации" Вам недоступно');
			return false;
		}

		if ( $data['EvnPS_IsPrehospAcceptRefuse'] == 2 ) {
			if ( $response['DaysDiff'] > 5 ) {
				echo json_return_errors('Изменение признака "Отказ в подтверждении госпитализации" возможно только в течение 5 дней с момента госпитализации');
				return false;
			}
			else if ( !empty($response['EvnDirection_id']) && $response['Lpu_id'] == $response['Lpu_did'] ) {
				echo json_return_errors('Изменение признака "Отказ в подтверждении госпитализации" возможно, если имеется направление и ЛПУ направления отличается от ЛПУ госпитализации');
				return false;
			}
			else if ( $response['PrehospType_id'] != 2 ) {
				echo json_return_errors('Изменение признака "Отказ в подтверждении госпитализации" возможно только при плановых госпитализациях');
				return false;
			}
		}

		// Стартуем транзакцию
		$this->dbmodel->beginTransaction();

		// Получение данных для отправки уведомлений об изменении признака "Отказ в подтверждении госпитализации"
		$response = $this->dbmodel->getMessageDataOnPrehospAcceptRefuse($data);

		// Если данные получены успешно, то формируем уведомление
		if ( is_array($response) && count($response) > 0 && !empty($response[0]['MedPersonal_id']) ) {
			// цепляем модель для работы с сообщениями
			$this->load->model('Messages_model', 'msgmodel');

			$messageData = array(
				 'autotype' => 1
				,'Lpu_rid' => $response[0]['Lpu_id']
				,'MedPersonal_rid' => $response[0]['MedPersonal_id']
				,'pmUser_id' => $data['pmUser_id']
				,'type' => 1
			);

			if ( $data['EvnPS_IsPrehospAcceptRefuse'] == 2 ) {
				$messageData['text'] = 'Госпитализация пациента ' . $response[0]['Person_Surname'] . ' ' . $response[0]['Person_Firname'] . ' ' . $response[0]['Person_Secname'] . ' не одобрена фондодержателем ' . $response[0]['Lpu_Name'] . '.';
				$messageData['title'] = 'Отказ в подтверждении госпитализации пациента';
			}
			else {
				$messageData['text'] = 'Отказ в подтверждении госпитализация пациента ' . $response[0]['Person_Surname'] . ' ' . $response[0]['Person_Firname'] . ' ' . $response[0]['Person_Secname'] . ' отменен фондодержателем ' . $response[0]['Lpu_Name'] . '.';
				$messageData['title'] = 'Отмена отказа в подтверждении госпитализации пациента';
			}

			$messageResponse = $this->msgmodel->autoMessage($messageData);

			if ( !empty($messageResponse['Error_Msg']) ) {
				echo json_return_errors($messageResponse['Error_Msg']);
				$this->dbmodel->rollbackTransaction();
				return false;
			}
		}

		// Установка признака
		$response = $this->dbmodel->setEvnPSPrehospAcceptRefuse($data);

		if ( !is_array($response) || count($response) == 0 ) {
			echo json_return_errors('Ошибка при ' . ($data['EvnPS_IsPrehospAcceptRefuse'] == 1 ? 'отмене' : 'установке') . ' признака "Отказ в подтверждении госпитализации"');
			$this->dbmodel->rollbackTransaction();
			return false;
		}

		$this->ProcessModelSave($response)->ReturnData();
		$this->dbmodel->commitTransaction();

		return true;
	}


	/**
	*  Создание копии карты выбывшего из стационара
	*  Входящие данные: $_POST['EvnPS_id']
	*  На выходе: JSON-строка
	*  Используется: форма поиска карты выбывшего из стационара
	*/
	function copyEvnPS() {
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('copyEvnPS', true);
		if ( $data === false ) { return false; }
		try {
			$this->dbmodel->isAllowTransaction = false;
			if (!in_array($data['Lpu_id'], array(150184, 13002495, 13002494)) ) {
				throw new Exception('Копирование КВС недоступно');
			}
			@ini_set('max_execution_time', 0);

			$alert_msg = '';
			$oldEvnPS = $this->dbmodel;
			$oldEvnPS->applyData(array(
				'session' => $data['session'],
				'EvnPS_id' => $data['EvnPS_id'],
			));
			if ( 3 == $oldEvnPS->leaveTypeCode ) {
				throw new Exception('Копирование отменено! Исход госпитализации в копируемой КВС "Смерть пациента"');
			}
			if ( $data['Lpu_id'] != $oldEvnPS->Lpu_id ) {
				throw new Exception('Ошибка при получении данных о копируемой КВС');
			}
			$evnPSData = array(
				'Person_id' => $oldEvnPS->Person_id,
				'PersonEvn_id' => $oldEvnPS->PersonEvn_id,
				'Server_id' => $oldEvnPS->Server_id,
				'EvnPS_IsCont' => $oldEvnPS->IsCont,
				'Diag_aid' => $oldEvnPS->Diag_aid,
				'Diag_pid' => $oldEvnPS->Diag_pid,
				'Diag_did' => $oldEvnPS->Diag_did,
				'EvnDirection_id' => $oldEvnPS->EvnDirection_id,
				'PrehospArrive_id' => $oldEvnPS->PrehospArrive_id,
				'PrehospDirect_id' => $oldEvnPS->PrehospDirect_id,
				'PrehospToxic_id' => $oldEvnPS->PrehospToxic_id,
                'LpuSectionTransType_id' => $oldEvnPS->LpuSectionTransType_id,
				'PayType_id' => $oldEvnPS->PayType_id,
				'PrehospTrauma_id' => $oldEvnPS->PrehospTrauma_id,
				'PrehospType_id' => $oldEvnPS->PrehospType_id,
				'Lpu_did' => $oldEvnPS->Lpu_did,
				'Org_did' => $oldEvnPS->Org_did,
				'LpuSection_did' => $oldEvnPS->LpuSection_did,
				'OrgMilitary_did' => $oldEvnPS->OrgMilitary_did,
				'LpuSection_pid' => $oldEvnPS->LpuSection_pid,
				'MedPersonal_pid' => $oldEvnPS->MedPersonal_pid,
				'EvnDirection_Num' => $oldEvnPS->EvnDirection_Num,
				'EvnPS_CodeConv' => $oldEvnPS->CodeConv,
				'EvnPS_NumConv' => $oldEvnPS->NumConv,
				'EvnPS_TimeDesease' => $oldEvnPS->TimeDesease,
				'Okei_id' => $oldEvnPS->Okei_id,
				'EvnPS_HospCount' => $oldEvnPS->HospCount,
				'EvnPS_IsUnlaw' => $oldEvnPS->IsUnlaw,
				'EvnPS_IsUnport' => $oldEvnPS->IsUnport,
				'EvnPS_IsImperHosp' => $oldEvnPS->IsImperHosp,
				'EvnPS_IsShortVolume' => $oldEvnPS->IsShortVolume,
				'EvnPS_IsWrongCure' => $oldEvnPS->IsWrongCure,
				'EvnPS_IsDiagMismatch' => $oldEvnPS->IsDiagMismatch,
				'EvnPS_IsPLAmbulance' => $oldEvnPS->IsPLAmbulance,
				'EvnPS_IsTransfCall' => $oldEvnPS->IsTransfCall,
				'EvnPS_IsWaif' => $oldEvnPS->IsWaif,
				//'LeaveType_prmid' => $oldEvnPS->LeaveType_prmid,
				'PrehospWaifRefuseCause_id' => $oldEvnPS->PrehospWaifRefuseCause_id,
				'ResultClass_id' => $oldEvnPS->ResultClass_id,
				'ResultDeseaseType_id' => $oldEvnPS->ResultDeseaseType_id,
				'PrehospWaifArrive_id' => $oldEvnPS->PrehospWaifArrive_id,
				'PrehospWaifReason_id' => $oldEvnPS->PrehospWaifReason_id,
				'Lpu_id' => $data['Lpu_id'],
				'EvnPS_NumCard' => $this->getEvnPSNumber('return'),
				'EvnPS_setDate' => (!empty($data['date']) ? $data['date'] : date('Y-m-d')),
				'EvnPS_setTime' => $oldEvnPS->setTime,
				'EvnPS_setDT' => (!empty($data['date']) ? $data['date'] : date('Y-m-d')),
				'EvnDirection_setDate' => null,
				'EvnPS_id' => $data['EvnPS_id'],
				'EvnPS_IsWithoutDirection' => $oldEvnPS->IsWithoutDirection,
				'ignoreUslugaComplexTariffCountCheck' => (!empty($data['ignoreUslugaComplexTariffCountCheck']) ? $data['ignoreUslugaComplexTariffCountCheck'] : 1),
				'vizit_direction_control_check' => (!empty($data['vizit_direction_control_check']) ? $data['vizit_direction_control_check'] : 1),
				'ignoreParentEvnDateCheck' => (!empty($data['datignoreParentEvnDateChecke']) ? $data['ignoreParentEvnDateCheck'] : 1),
				'ignoreEvnPSDoublesCheck' => $data['ignoreEvnPSDoublesCheck'],
				'ignoreEvnPSTimeDeseaseCheck' => $data['ignoreEvnPSTimeDeseaseCheck'],
				'session' => $data['session'],
				'scenario' => swModel::SCENARIO_AUTO_CREATE,
			);
			if (isset($oldEvnPS->EvnDirection_setDT)) {
				$evnPSData['EvnDirection_setDate'] = $oldEvnPS->EvnDirection_setDT->format('Y-m-d');
			}

			// Проверка КВС на дубли по номеру
			$response = $this->dbmodel->checkEvnPSDoublesByNum($evnPSData);

			if ( false === $response ) {
				throw new Exception('Ошибка при проверке КВС на дубли по номеру');
			}

			while ( is_array($response) && count($response) > 0 && !empty($response[0]['EvnPS_id']) ) {
				$evnPSData['EvnPS_NumCard'] = $this->getEvnPSNumber('return');

				// Проверка КВС на дубли по номеру
				$response = $this->dbmodel->checkEvnPSDoublesByNum($evnPSData);

				if ( false === $response ) {
					throw new Exception('Ошибка при проверке КВС на дубли по номеру');
				}
			}

			$evnPSData['EvnPS_id'] = null;

			$this->load->model('EvnPS_model', 'newEvnPS');
			$this->newEvnPS->applyData($evnPSData);

			if ($oldEvnPS->evnSectionFirstId) {
				$this->load->model('EvnSection_model', 'EvnSection');
				$oldEvnSection = $this->EvnSection;
				$oldEvnSection->applyData(array(
					'session' => $data['session'],
					'EvnSection_id' => $oldEvnPS->evnSectionFirstId,
				));

				$EvnSection_setDate = $this->newEvnPS->setDate;
				$EvnSection_disDate = $this->newEvnPS->setDate;
				if (strtotime($this->newEvnPS->setDate . ' ' . $oldEvnSection->setTime) > strtotime($this->newEvnPS->setDate . ' ' . $oldEvnSection->disTime)) {
					// если время выписки получилось раньше чем время поступления, пусть переходит на следующий день.
					$EvnSection_disDate = date('Y-m-d', strtotime($this->newEvnPS->setDate . ' ' . $oldEvnSection->setTime) + 24*60*60);
				}

				$evnSectionData = array(
					'Person_id' => $this->newEvnPS->Person_id,
					'PersonEvn_id' => $this->newEvnPS->PersonEvn_id,
					'Server_id' => $this->newEvnPS->Server_id,
					'LpuSection_id' => $oldEvnSection->LpuSection_id,
					'Diag_id' => $oldEvnSection->Diag_id,
					'DiagSetPhase_id' => $oldEvnSection->DiagSetPhase_id,
					'EvnSection_PhaseDescr' => $oldEvnSection->PhaseDescr,
					'Mes_id' => $oldEvnSection->Mes_id,
					'TariffClass_id' => $oldEvnSection->TariffClass_id,
					'MedPersonal_id' => $oldEvnSection->MedPersonal_id,
					'LpuSectionWard_id' => $oldEvnSection->LpuSectionWard_id,
					'PayType_id' => $oldEvnSection->PayType_id,
					'LeaveType_id' => $oldEvnSection->LeaveType_id,
					'CureResult_id' => $oldEvnSection->CureResult_id,
					'Lpu_id' => $data['Lpu_id'],
					'EvnPS_NumCard' => $this->getEvnPSNumber('return'),
					'EvnSection_setDate' => $EvnSection_setDate,
					'EvnSection_setTime' => $oldEvnSection->setTime,
					'EvnSection_disDate' => $EvnSection_disDate,
					'EvnSection_disTime' => $oldEvnSection->disTime,
					'EvnSection_id' => null,
					'LeaveType_fedid' => $oldEvnSection->LeaveType_fedid,
					'ResultDeseaseType_fedid' => $oldEvnSection->ResultDeseaseType_fedid,
					'session' => $data['session'],
					'scenario' => swModel::SCENARIO_AUTO_CREATE,
				);

				// выбираем данные исхода госпитализации
				switch ( $evnSectionData['LeaveType_id'] ) {
					case 1:
						$this->load->model('EvnLeave_model', 'elmodel');
						$evnLeaveData = $this->elmodel->doLoadCopyData(array(
							'EvnLeave_pid' => $oldEvnSection->id,
							'session' => $data['session'],
						));
						break;
					case 2:
						$this->load->model('EvnOtherLpu_model', 'elmodel');
						$evnLeaveData = $this->elmodel->doLoadCopyData(array(
							'EvnOtherLpu_pid' => $oldEvnSection->id,
							'session' => $data['session'],
						));
						break;
					case 4:
						$this->load->model('EvnOtherStac_model', 'elmodel');
						$evnLeaveData = $this->elmodel->doLoadCopyData(array(
							'EvnOtherStac_pid' => $oldEvnSection->id,
							'session' => $data['session'],
						));
						break;
					case 5:
						$this->load->model('EvnOtherSection_model', 'elmodel');
						$evnLeaveData = $this->elmodel->doLoadCopyData(array(
							'EvnOtherSection_pid' => $oldEvnSection->id,
							'session' => $data['session'],
						));
						break;
					/*
					case 6:
						$this->load->model('EvnOtherSectionBedProfile_model', 'elmodel');
						$evnLeaveData = $this->elmodel->doLoadCopyData(array(
							'EvnOtherSectionBedProfile_pid' => $oldEvnSection->id,
							'session' => $data['session'],
						));
					break;
					*/
					default:
						$evnLeaveData = array();
						break;
				}
				if (empty($evnLeaveData)) {
					$evnSectionData['LeaveType_id'] = null;
					$evnSectionData['EvnSection_disDate'] = null;
					$evnSectionData['EvnSection_disTime'] = null;
				} else {
					// имитируем передачу параметров из формы
					if (empty($evnLeaveData['EvnLeave_UKL'])) {
						$evnLeaveData['EvnLeave_UKL'] = 1;
					}
					$evnSectionData = array_merge($evnSectionData, $evnLeaveData);
				}
				$this->load->model('EvnSection_model', 'newEvnSection');
				$this->newEvnSection->applyData($evnSectionData);
			}

			// запускаем транзакцию
			$this->dbmodel->isAllowTransaction = true;
			if ( !$this->dbmodel->beginTransaction() ) {
				$this->dbmodel->isAllowTransaction = false;
				throw new Exception('Не удалось запустить транзакцию', 500);
			}

			// создаем новую КВС
			$savedata = $this->newEvnPS->doSave($evnPSData, false);
			if ( !empty($savedata['Error_Msg']) ) {
				$this->ProcessModelSave($savedata)->ReturnData();
				return true;
			}

			// создаем новое движение, копируем только первое движение в профильном отделении
			// также сохраняется исход, если он есть
			if (isset($this->newEvnSection)) {
				$this->newEvnSection->setParent($this->newEvnPS);
				$response = $this->newEvnSection->doSave(array(), false);
				if ( !empty($response['Error_Msg']) ) {
					throw new Exception($response['Error_Msg']);
				}
			}

			if ( !$this->dbmodel->commitTransaction() ) {
				$this->dbmodel->isAllowTransaction = false;
				throw new Exception('Не удалось зафиксировать транзакцию', 500);
			}

			if (isset($this->newEvnSection) && $this->newEvnSection->PayType_id != $this->newEvnPS->PayType_id) {
				$alert_msg .= '<div>Случай содержит движения в отделении с другим видом оплаты.</div><div>Пожалуйста, проверьте правильность вида оплаты в отделениях.</div>';
			}
			if ( !empty($alert_msg) ) {
				ConvertFromWin1251ToUTF8($alert_msg);
				$savedata['Alert_Msg'] = $alert_msg;
			}

			$this->ProcessModelSave($savedata)
				->ReturnData();
			return true;
		} catch (Exception $e) {
			$this->dbmodel->rollbackTransaction();
			$this->ReturnError($e->getMessage());
			return false;
		}
	}

	/**
	*  Подписание события исхода из отделения
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: ЭМК
	*/
	function signLeaveEvent() {
		$data = $this->ProcessInputData('signLeaveEvent', true);
		if ( $data === false ) {
			return false;
		}
		$response = array(array('Error_Msg'=>'Неправильно указан тип исхода!'));
		if($data['parentClass'] == 'EvnSection' && in_array($data['LeaveType_id'],array(1,2,3,4,5)))
		{
			//нельзя подписать, если есть не подписанные записи, связанные с текущим движением
			$this->load->model('EvnSection_model', 'EvnSection');
			$response = $this->EvnSection->checkSignEvnSection(array(
				'EvnSection_id' => $data['LeaveEvent_pid']
			));
			if(!is_array($response))
			{
				$response=array(array('Error_Msg'=>'Не удалось выполнить проверку перед подписанием исхода!'));
			}
			else if(count($response)>0)
			{
				$response=array(array('Error_Msg'=>'У вас есть не подписанные записи в истории болезни, подписание данных об исходе из отделения невозможно!'));
			}
			else
			{
				$this->load->model('Common_model', 'Common_model');
				// отменяет подпись под суперадмином, если подписано
				$response = $this->Common_model->signedDocument(array(
					'type'=> 'Evn',
					'id'=>$data['LeaveEvent_id'],
					'pmUser_id'=>$data['pmUser_id']
				));
			}
		}
		$this->ProcessModelSave($response, true,'Ошибка при удалении события исхода')->ReturnData();
		return true;
	}

	/**
	*  Получение номера карты выбывшего из стационара
	*  Входящие данные: нет
	*  На выходе: JSON-строка
	*  Используется: форма редактирования карты выбывшего из стационара
	*/
	function getEvnPSNumber($returnAction = 'echo') {
		$data = $this->ProcessInputData('getEvnPSNumber', true);

		if ( $data === false ) {
			return false;
		}

		$this->load->model("Options_model", "opmodel");

		$options = $this->opmodel->getDataStorageOptions($data);
		$val     = array();

		if ( empty($data['Lpu_id']) ) {
			$this->ReturnData(array('success' => false));
			return true;
		}

		$response = $this->dbmodel->getEvnPSNumber($data);

		if (empty($options['stac']['evnps_numcard_prefix'])) {
			$options['stac']['evnps_numcard_prefix'] = "";
		}
		if (empty($options['stac']['evnps_numcard_postfix'])) {
			$options['stac']['evnps_numcard_postfix'] = "";
		}
		
		if ( is_array($response) && count($response) > 0 ) {
			$val['EvnPS_NumCard'] = $options['stac']['evnps_numcard_prefix'] . $response[0]['EvnPS_NumCard'] . $options['stac']['evnps_numcard_postfix'];
		}

		switch ( $returnAction ) {
			case 'echo':
				$this->ReturnData($val);
				return true;
			break;

			case 'return':
				return $val['EvnPS_NumCard'];
			break;
		}
	}

	function ExportInjuryJournalXLS($result, $header) {
		require_once('vendor/autoload.php');
		$objPHPExcel = new PhpOffice\PhpSpreadsheet\Spreadsheet();
		$objPHPExcel->getProperties();
		$objPHPExcel->getActiveSheet()->setTitle($header[0]);

		foreach($header as $key => $headerrow){
			$key++;
			$objPHPExcel->getActiveSheet()->mergeCells('A'.$key.':M'.$key);
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$key, $headerrow)->getStyle('A'.$key)->getAlignment()->setHorizontal(PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
		}

		$columnHeadersFirstPart = [ 'Номер истории', 'ФИО Пациента', 'Полис', 'Дата рождения', 'Откуда доставлен', 'Кем доставлен'];
		if (getRegionNick() != 'kz') {
			$columnHeadersFirstPart[] = 'Дата, время получения травмы';
			$columnHeadersFirstPart[] = 'Обстоятельства';
		}
		$columnHeadersSecondPart = ['Дата поступления', 'Диагноз', 'Внешняя причина', 'Дата смерти', 'Место смерти'];

		$columnname = array_merge($columnHeadersFirstPart, $columnHeadersSecondPart);

		$r = 4;	$j = 0;
		for ($i='A'; $i<='M'; $i++){
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($i.$r, $columnname[$j]);
			$j++;
			switch($i){
				case 'H':
				case 'K':
				case 'J':
					$objPHPExcel->getActiveSheet()->getColumnDimension($i)->setWidth(60);
					break;
				default:
					$objPHPExcel->getActiveSheet()->getColumnDimension($i)->setAutoSize(true);
					break;
			}
		}

		foreach ($result as $item) {
			$c = 'A';
			$r++;
			foreach ($item as $val) {
				$objPHPExcel->getActiveSheet()->setCellValueExplicit($c . $r, $val,PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
				$c++;
			}
		}
		
		$objWriter = new PhpOffice\PhpSpreadsheet\Writer\Xls($objPHPExcel);
		$file = 'export/reports/' . $header[0] . '.xls';
		$objWriter->save($file);
		//$this->download($file);
		return $file;
	}

	function RunReportsInjuryJournal(){
		$data = $this->ProcessInputData('RunReportsInjuryJournal', true);
		if ( $data === false )
			return false;

		$session_data = getSessionParams();
		$response = $this->dbmodel->RunReportsInjuryJournal($data);
		$title = [ 'Журнал травм при ДТП', 'Журнал производственных травм', 'Журнал криминальных травм' ];
		$filename = $this->ExportInjuryJournalXLS( $response, [ $title[$data['kindJournal_id']-1], $session_data['session']['Org_Name'], $data['begDate'] . ' - ' . $data['endDate'] ] );

		$this->ReturnData(array('success' => true, 'Link' => $filename));
	}

	/**
	*  Получение данных для формы редактирования КВС
	*  Входящие данные: $_POST['EvnPS_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования КВС
	*/
	function loadEvnPSEditForm() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadEvnPSEditForm', true);

		if ( $data === false ) {
			return false;
		}

		if($data['delDocsView'] && $data['delDocsView'] == 1)
			$response = $this->dbmodel->loadEvnPSEditFormForDelDocs($data);
		else
			$response = $this->dbmodel->loadEvnPSEditForm($data);

		if ( is_array($response) && count($response) > 0 ) {
			// Времянка, но временное, как известно, долговечнее постоянного (c) Night
			if ($response[0]['Lpu_id'] != $data['Lpu_id'])
			{
				$response[0]['action'] = 'view';
			}
			$val = $response;
			array_walk($val[0], 'ConvertFromWin1251ToUTF8');
		}

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Получение списка КВС для формы выбора КВС
	*  Входящие данные: $_POST['Person_id'],
	*  На выходе: JSON-строка
	*  Используется: форма выбора КВС
	*/
	function loadEvnPSList() {
		$data = $this->ProcessInputData('loadEvnPSList', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnPSList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return false;
	}


	/**
	*  Получение данных КВС для использования в ТАП (при заведении ТАП из отказа в приёмном)
	*/
	function getEvnPSInfoForEvnPL() {
		$data = $this->ProcessInputData('getEvnPSInfoForEvnPL', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getEvnPSInfoForEvnPL($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения данных КВС')->ReturnData();

		return false;
	}

	/**
	 * Получение даты последней флюорографии
	 */
	function getLastFluorographyDate() {
		$data = $this->ProcessInputData('getLastFluorographyDate', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getLastFluorographyDate($data);
		$this->ProcessModelList($response, true, 'Ошибка получения даты последней флюорографии')->ReturnData();

		return false;
	}


	/**
	*  Получение списка КВС для потокового ввода
	*  Входящие данные: $_POST['begDate'],
	*                   $_POST['begTime']
	*  На выходе: JSON-строка
	*  Используется: форма потокового ввода КВС
	*/
	function loadEvnPSStreamList() {
		$data = $this->ProcessInputData('loadEvnPSStreamList', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnPSStreamList($data);
		$outdata=array();
		$outdata['data'] = $this->ProcessModelList($response, true, true)->GetOutData();
		$this->ReturnData($outdata);
		
		return false;
	}


	/**
	*  Получение информации о выписке
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма редактирования КВС
	*/
	function loadLeaveInfoGrid() {
		$data = array();
		$i    = 0;
		$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadLeaveInfoGrid', true);

		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->loadLeaveInfoGrid($data);

		if ( is_array($response) && count($response) > 0 ) {
			// Преобразовать результат выполнения запроса к виду LeaveParams_Name -> LeaveParams_Value
			array_walk($response[0], 'ConvertFromWin1251ToUTF8');

			foreach ( $response[0] as $key => $value ) {
				$i++;

				switch ( $key ) {
					case 'deathDate':
						$val[] = array('id' => $i, 'LeaveParams_SysNick' => 'setDate', 'LeaveParams_Name' => toUTF('Дата смерти'), 'LeaveParams_Value' => $value);
					break;

					case 'deathTime':
						$val[] = array('id' => $i, 'LeaveParams_SysNick' => 'setTime', 'LeaveParams_Name' => toUTF('Время смерти'), 'LeaveParams_Value' => $value);
					break;

					case 'Diag_Anatom_Name':
						$val[] = array('id' => $i, 'LeaveParams_SysNick' => 'DiagAnatom', 'LeaveParams_Name' => toUTF('Основной патологоанатомический диагноз'), 'LeaveParams_Value' => $value);
					break;

					case 'IsAmbul':
						$val[] = array('id' => $i, 'LeaveParams_SysNick' => 'IsAmbul', 'LeaveParams_Name' => toUTF('Амбулаторное долечивание'), 'LeaveParams_Value' => $value);
					break;

					case 'IsWait':
						$val[] = array('id' => $i, 'LeaveParams_SysNick' => 'IsWait', 'LeaveParams_Name' => toUTF('Умер в приемном покое'), 'LeaveParams_Value' => $value);
					break;

					case 'IsAnatom':
						$val[] = array('id' => $i, 'LeaveParams_SysNick' => 'IsAnatom', 'LeaveParams_Name' => toUTF('Необходимость экспертизы'), 'LeaveParams_Value' => $value);
					break;

					case 'LeaveCause_Name':
						$val[] = array('id' => $i, 'LeaveParams_SysNick' => 'LeaveCause', 'LeaveParams_Name' => toUTF('Причина выписки'), 'LeaveParams_Value' => $value);
					break;

					case 'Lpu_Name':
						$val[] = array('id' => $i, 'LeaveParams_SysNick' => 'Lpu', 'LeaveParams_Name' => toUTF('ЛПУ'), 'LeaveParams_Value' => $value);
					break;

					case 'LpuSection_Name':
						$val[] = array('id' => $i, 'LeaveParams_SysNick' => 'LpuSection', 'LeaveParams_Name' => toUTF('Отделение'), 'LeaveParams_Value' => $value);
					break;

					case 'LpuUnitType_Name':
						$val[] = array('id' => $i, 'LeaveParams_SysNick' => 'LpuUnitType', 'LeaveParams_Name' => toUTF('Тип стационара'), 'LeaveParams_Value' => $value);
					break;

					case 'MP_Anatom_Fio':
						$val[] = array('id' => $i, 'LeaveParams_SysNick' => 'MedPersAnatom', 'LeaveParams_Name' => toUTF('Врач, установивший смерть'), 'LeaveParams_Value' => $value);
					break;

					case 'OtherLeaveCause_Name':
						$val[] = array('id' => $i, 'LeaveParams_SysNick' => 'LeaveCause', 'LeaveParams_Name' => toUTF('Причина перевода'), 'LeaveParams_Value' => $value);
					break;

					case 'ResultDesease_Name':
						$val[] = array('id' => $i, 'LeaveParams_SysNick' => 'ResultDesease', 'LeaveParams_Name' => toUTF('Результат госпитализации'), 'LeaveParams_Value' => $value);
					break;

					case 'setDate':
						$val[] = array('id' => $i, 'LeaveParams_SysNick' => 'setDate', 'LeaveParams_Name' => toUTF('Дата выписки'), 'LeaveParams_Value' => $value);
					break;

					case 'setTime':
						$val[] = array('id' => $i, 'LeaveParams_SysNick' => 'setTime', 'LeaveParams_Name' => toUTF('Время выписки'), 'LeaveParams_Value' => $value);
					break;

					case 'setDateOther':
						$val[] = array('id' => $i, 'LeaveParams_SysNick' => 'setDate', 'LeaveParams_Name' => toUTF('Дата перевода'), 'LeaveParams_Value' => $value);
					break;

					case 'setTimeOther':
						$val[] = array('id' => $i, 'LeaveParams_SysNick' => 'setTime', 'LeaveParams_Name' => toUTF('Время перевода'), 'LeaveParams_Value' => $value);
					break;

					case 'UKL':
						$val[] = array('id' => $i, 'LeaveParams_SysNick' => 'UKL', 'LeaveParams_Name' => toUTF('Уровень качества лечения'), 'LeaveParams_Value' => number_format($value, 2, '.', ''));
					break;
				}
			}
		}

		$this->ReturnData($val);

		return false;
	}


	/**
	*  Печать карты выбывшего из стационара
	*  Входящие данные: $_GET['EvnPS_id']
	*  На выходе: форма для печати карты выбывшего из стационара
	*  Используется: форма редактирования карты выбывшего из стационара
	*                форма потокового ввода КВС
	*Значение параметра Parent_Code означают, что печать вызвана из следующих форм:
	*	1	Форма «Карта выбывшего из стационара» по кнопке Печать кнопок управления формой
	*	2	Форма «Карта выбывшего из стационара: Поиск» по кнопке Печать панели управления списком КВС
	*	3	Форма «Карта выбывшего из стационара: Поточный ввод» по кнопке Печать панели управления списком КВС
	*	4	ЭМК. Панель просмотра. Случай стационарного лечения. Кнопка Печать КВС.
	*	5	Форма «Карта выбывшего из стационара», в панели управления списком Движений в форме «Карта выбывшего из стационара»
	*	6	Форма «Электронная медицинская карта (ЭМК)». В панели просмотра данных о Движении пациента в приемном отделении
	*	7	Форма «Электронная медицинская карта (ЭМК)». В панели просмотра данных о Движении пациента в профильном отделении
	*
	*Значение параметра KVS_Type означает следующее:
	*	"AB" - список составлен по КВС
	*	"VG" - список составлен по движениям
	*	"V"  - выбрано первое по хронологии движение
	*	"G"  - выбрано НЕ первое по хронологии движение
	*/
	function printEvnPS() {
		$data = $this->ProcessInputData('printEvnPS', true);
		if ( $data === false ) { return false; }

		if ( empty($data['EvnPS_id']) && empty($data['EvnSection_id']) ) {
			echo 'Не указаны обязательные идентификаторы (КВС или движение)';
			return false;
		}

		$this->dbmodel->printEvnPS($data);
	}

	/**
	 * Печать ТАП отказа в госпитализации
	 */
	function printEvnPLRefuse() {
		$data = $this->ProcessInputData('printEvnPLRefuse', true);
		if ( $data === false ) { return false; }

		$this->load->library('parser');

		$template = 'evn_pl_template_list_a4_kareliya';

		$response = $this->dbmodel->getEvnPSForPrintEvnPLRefuse($data);

		$evn_section_data = array();
		$evn_usluga_oper_data = array();
		$evn_usluga_oper_med_data = array();

		$allowPriem = !empty($response[0]['PrehospWaifRefuseCause_id']);

		//$response_temp = $this->dbmodel->getEvnSectionData($data, $allowPriem);
		$EvnSection_IsAdultEscort = 1;

		$print_data = array(
			'OrgJob_Name' => returnValidHTMLString($response[0]['OrgJob_Name']),
			'OrgUnion_Name' => returnValidHTMLString($response[0]['OrgUnion_Name']),
			'Post_Name' => returnValidHTMLString($response[0]['Post_Name']),
			'DocumentType_Name' => returnValidHTMLString($response[0]['DocumentType_Name']),
			'Document_Ser' => returnValidHTMLString($response[0]['Document_Ser']),
			'Document_Num' => returnValidHTMLString($response[0]['Document_Num']),
			'Document_begDate' => returnValidHTMLString($response[0]['Document_begDate']),
			'UAddress_Name' => returnValidHTMLString($response[0]['UAddress_Name']),
			'KLAreaType_Code' => returnValidHTMLString($response[0]['KLAreaType_Code']),
			'KLAreaType_Name' => returnValidHTMLString($response[0]['KLAreaType_Name']),
			'SocStatus_Code' => returnValidHTMLString($response[0]['SocStatus_Code']),
			'SocStatus_Name' => returnValidHTMLString($response[0]['SocStatus_Name']),
			'PrivilegeType_Name' => '',
			'PersonPrivilege_begDate' => '',
			//'DeseaseTypeSop_Code' => returnValidHTMLString($response[0]['DeseaseTypeSop_Code']),
			//'DiagAgg_Code' => returnValidHTMLString($response[0]['DiagAgg_Code']),
			//'DiagSop_Code' => returnValidHTMLString($response[0]['DiagSop_Code']),
			'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name']),
			'LpuAddress' => returnValidHTMLString($response[0]['LpuAddress']),
			'Lpu_OGRN' => returnValidHTMLString($response[0]['Lpu_OGRN']),
			'Sex_Code' => returnValidHTMLString($response[0]['Sex_Code']),
			'Sex_Name' => returnValidHTMLString($response[0]['Sex_Name']),
			'Person_Birthday' => returnValidHTMLString($response[0]['Person_Birthday']),
			//'EvnPL_setDate' => returnValidHTMLString($response[0]['EvnPL_setDate']),
			//'EvnPLTemplateTitle' => 'Печать талона амбулаторного пациента',
			'EvnStick_Age' => '',
			/*'EvnStick_begDate' => returnValidHTMLString($response[0]['EvnStick_begDate']),
			'EvnStick_endDate' => returnValidHTMLString($response[0]['EvnStick_endDate']),
			'EvnStick_Open' => returnValidHTMLString($response[0]['EvnStick_Open']),*/
			'EvnStick_Sex' => '',
			//'FinalDiag_Code' => returnValidHTMLString($response[0]['FinalDiag_Code']),
			//'FinalDeseaseType_Code' => returnValidHTMLString($response[0]['FinalDeseaseType_Code']),
			'KlareaType_id' => returnValidHTMLString($response[0]['KlareaType_id']),
			'LpuRegion_Name' => returnValidHTMLString($response[0]['LpuRegion_Name']),
			'LpuSectionProfile_Code' => returnValidHTMLString($response[0]['LpuSectionProfile_Code']),
			'LpuSectionProfile_Name' => returnValidHTMLString($response[0]['LpuSectionProfile_Name']),
			'MedPersonal_Fio' => returnValidHTMLString($response[0]['MedPersonal_Fio']),
			'OrgSmo_Name' => returnValidHTMLString($response[0]['OrgSmo_Name']),
			'PayType_Code' => returnValidHTMLString($response[0]['PayType_Code']),
			'PAddress_Name' => returnValidHTMLString($response[0]['PAddress_Name']),
			'Person_Docum' => returnValidHTMLString($response[0]['DocumentType_Name']). ' '. returnValidHTMLString($response[0]['Document_Ser']) . ' ' . returnValidHTMLString($response[0]['Document_Num']),
			'Person_Fio' => mb_strtoupper(returnValidHTMLString($response[0]['Person_Fio'])),
			'Person_INN' => returnValidHTMLString($response[0]['Person_INN']),
			'Person_Snils' => returnValidHTMLString($response[0]['Person_Snils']),
			'PersonCard_Code' => returnValidHTMLString($response[0]['PersonCard_Code']),
			'Polis_begDate' => returnValidHTMLString($response[0]['Polis_begDate']),
			'Polis_endDate' => returnValidHTMLString($response[0]['Polis_endDate']),
			'Polis_Num' => returnValidHTMLString($response[0]['Polis_Num']),
			'Polis_Ser' => returnValidHTMLString($response[0]['Polis_Ser']),
			//'PrehospDiag_Code' => returnValidHTMLString($response[0]['PrehospDiag_Code']),
			//'PrehospDiag_regDate' => returnValidHTMLString($response[0]['PrehospDiag_regDate']),
			//'ServiceType_Code' => returnValidHTMLString($response[0]['ServiceType_Code']),
			'ResultClass_Code' => returnValidHTMLString($response[0]['ResultClass_Code']),
			'PrehospTrauma_Code' => returnValidHTMLString($response[0]['PrehospTrauma_Code']),
			//'MedicalCareKind_Code' => returnValidHTMLString($response[0]['MedicalCareKind_Code'])
		);
		//print_r($print_data);exit;
		$print_data['vizitDataPol_1'] = '&nbsp;';// $print_data['vizitDataPol_2'] = '&nbsp;'; $print_data['vizitDataPol_3'] = '&nbsp;';
		$print_data['DaysPol_Count_1'] = '&nbsp;'; $print_data['DaysPol_Count_2'] = '&nbsp;'; $print_data['DaysPol_Count_3'] = '&nbsp;';
		$print_data['Pol_1'] = '&nbsp;'; $print_data['Pol_2'] = '&nbsp;'; $print_data['Pol_3'] = '&nbsp;';
		$print_data['vizitDataHome_1'] = '&nbsp;'; $print_data['vizitDataHome_2'] = '&nbsp;';
		$print_data['DaysHome_Count_1'] = '&nbsp;'; $print_data['DaysHome_Count_2'] = '&nbsp;';
		$print_data['Home_1'] = '&nbsp;'; $print_data['Home_2'] = '&nbsp;';
		$print_data['vizitDataHomeAct_1'] = '&nbsp;'; $print_data['vizitDataHomeAct_2'] = '&nbsp;';
		$print_data['DaysHomeAct_Count_1'] = '&nbsp;'; $print_data['DaysHomeAct_Count_2'] = '&nbsp;';
		$print_data['HomeAct_1'] = '&nbsp;'; $print_data['HomeAct_2'] = '&nbsp;';
		$print_data['lrn1'] = '&nbsp;'; $print_data['lrn2'] = '&nbsp;'; $print_data['lrn3'] = '&nbsp;';
		//$print_data['mpc1'] = '&nbsp;'; $print_data['mpc2'] = '&nbsp;'; $print_data['mpc3'] = '&nbsp;';$print_data['mpc4'] = '&nbsp;'; $print_data['mpc5'] = '&nbsp;'; $print_data['mpc5'] = '&nbsp;'; $print_data['mpc5'] = '&nbsp;';
		for($i=1;$i<=7;$i++){
			$print_data['mpc'.$i] = '&nbsp;';
			$print_data['mpcl'.$i] = '&nbsp;';
		}
		$print_data['vtc1'] = '&nbsp;'; $print_data['vtc2'] = '&nbsp;';
		if(!empty($response[0]['LpuRegion_Name'])){
			for ( $i = 1; $i <= 3; $i++ ){
				if (isset($response[0]['LpuRegion_Name'][$i-1])){
					$print_data['lrn'.$i] = $response[0]['LpuRegion_Name'][$i-1];
				}
			}
		}
		if(!empty($response[0]['MedPersonal_Code'])){
			for ( $i = 1; $i <= 7; $i++ ){
				if (isset($response[0]['MedPersonal_Code'][$i-1])){
					$print_data['mpc'.$i] = $response[0]['MedPersonal_Code'][$i-1];
				}
			}
		}
		if(!empty($response[0]['MedPersonal_Code_Last'])){
			for( $i = 1; $i <= 7; $i++){
				if(isset($response[0]['MedPersonal_Code_Last'][$i-1])){
					$print_data['mpcl'.$i] = $response[0]['MedPersonal_Code_Last'][$i-1];
				}
			}
		}
		if(!empty($response[0]['VizitType_Code'])){
			for ( $i = 1; $i <= 5; $i++ ){
				if (isset($response[0]['VizitType_Code'][$i-1])){
					$print_data['vtc'.$i] = $response[0]['VizitType_Code'][$i-1];
				}
			}
		}

		$pol_vizit = $response[0]['EvnSection_setDate'] . ' - ' . $response[0]['MedPersonal_Code'].' &nbsp;&nbsp;';
		$pol_days = ($response[0]['Days_Count']==0)?1:$response[0]['Days_Count'];

		$print_data['vizitDataPol_1'] = $pol_vizit;
		$print_data['DaysPol_Count_1'] = ($pol_days==0)?'':$pol_days;
		$print_data['Pol_1'] = 1;

		for($i=1;$i<=4;$i++){
			for($j=1;$j<=14;$j++)
				$print_data['u'.$i.$j] = '&nbsp;';
			for($j=1;$j<=2;$j++)
				$print_data['uk'.$i.$j] = '&nbsp;';
		}
		$response_usluga = $this->dbmodel->getEvnUslugaData($data); //Коды выполненных услуг
		if(is_array($response_usluga)){
			for($i=1;$i<=4;$i++){
				if(isset($response_usluga[$i-1])){
					for($j=1;$j<=14;$j++) {
						if ( mb_strlen($response_usluga[$i-1]['UslugaComplex_Code']) >= $j ) {
							if(isset($response_usluga[$i-1]['UslugaComplex_Code'][$j-1]))
								$print_data['u'.$i.$j] = $response_usluga[$i-1]['UslugaComplex_Code'][$j-1];
							else
								$print_data['u'.$i.$j] = '';
						}
					}
					for($j=1;$j<=2;$j++) {
						if ( mb_strlen($response_usluga[$i-1]['EvnUsluga_Kolvo']) >= $j ) {
							$str = strval($response_usluga[$i-1]['EvnUsluga_Kolvo']);
							if(isset($str[$j-1])) {
								$print_data['uk'.$i.$j] = $str[$j-1];
							} else {
								$print_data['uk'.$i.$j] = '';
							}
						}
					}
				}
			}
		}

		for($i=1;$i<=5;$i++){
			$print_data['diagType'.$i] = '&nbsp;';
			$print_data['Diag_Code'.$i] = '&nbsp;';
			$print_data['DeseaseType_Code'.$i] = '&nbsp;';
			$print_data['IsDisp'.$i] = '&nbsp;';
			$print_data['Disp_Date'.$i] = '&nbsp;';
			$print_data['DOT_Zdorov'.$i] = '&nbsp;';
			$print_data['DOT_Other'.$i] = '&nbsp;';
		}
		$response_diag = $this->dbmodel->getEvnDiagData($data);
		if(is_array($response_diag)){
			for($i=1;$i<=5;$i++){
				if(isset($response_diag[$i-1])){
					$print_data['diagType'.$i] = $response_diag[$i-1]['diagType'];
					$print_data['Diag_Code'.$i] = $response_diag[$i-1]['Diag_Code'];
					$print_data['DeseaseType_Code'.$i] = $response_diag[$i-1]['DeseaseType_Code'];
					$print_data['IsDisp'.$i] =  $response_diag[$i-1]['IsDisp'];
					$print_data['Disp_Date'.$i] = $response_diag[$i-1]['Disp_Date'];
					$print_data['DOT_Zdorov'.$i] = $response_diag[$i-1]['DOT_Zdorov'];
					$print_data['DOT_Other'.$i] = $response_diag[$i-1]['DOT_Other'];
				}
			}
		}

		//Документ временной нетрудоспособности
		for($i=1;$i<=8;$i++){
			$print_data['sb'.$i] = '&nbsp;';
			$print_data['se'.$i] = '&nbsp;';
		}
		$print_data['StickCause_Type'] = '&nbsp;';
		$print_data['Person_Age'] = '&nbsp;';

		$print_data['MedicalCareKind_Code1'] = '&nbsp';$print_data['MedicalCareKind_Code2'] = '&nbsp';
		if(!empty($response[0]['MedicalCareKind_Code'])){
			for($i=1;$i<=strlen($response[0]['MedicalCareKind_Code']);$i++){
				$print_data['MedicalCareKind_Code'.$i] = $response[0]['MedicalCareKind_Code'][$i-1];
			}
		}
		$print_data['ResultClass_Code1'] = '&nbsp;';$print_data['ResultClass_Code2'] = '&nbsp;';$print_data['ResultClass_Code3'] = '&nbsp;';
		$print_data['ResultDeseaseType_Code1'] = '&nbsp;';$print_data['ResultDeseaseType_Code2'] = '&nbsp;';$print_data['ResultDeseaseType_Code3'] = '&nbsp;';
		if(isset($response[0]['ResultClass_Code'])){
			if(strlen($response[0]['ResultClass_Code'])==3){
				$print_data['ResultClass_Code1'] = $response[0]['ResultClass_Code'][0];
				$print_data['ResultClass_Code2'] = $response[0]['ResultClass_Code'][1];
				$print_data['ResultClass_Code3'] = $response[0]['ResultClass_Code'][2];
			}
		}
		if(isset($response[0]['ResultDeseaseType_Code'])){
			if(strlen($response[0]['ResultDeseaseType_Code'])==3){
				$print_data['ResultDeseaseType_Code1'] = $response[0]['ResultDeseaseType_Code'][0];
				$print_data['ResultDeseaseType_Code2'] = $response[0]['ResultDeseaseType_Code'][1];
				$print_data['ResultDeseaseType_Code3'] = $response[0]['ResultDeseaseType_Code'][2];
			}
		}
		if (!empty($response[0]['PrivilegeType_Name'])) {
			$print_data['PrivilegeType_Name'] = returnValidHTMLString($response[0]['PrivilegeType_Name']) . ', установлена ' . returnValidHTMLString($response[0]['PersonPrivilege_begDate']);
		}

		$print_data['Document'] = (strlen($print_data['Document_Num']))
			? "тип док-та <u>&nbsp;&nbsp;&nbsp;{$response[0]['DocumentType_Code']}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u> серия <u>{$print_data['Document_Ser']}</u> номер <u>{$print_data['Document_Num']}</u>"
			: 'тип док-та ______________ серия _______ номер ______';
		//print_r($print_data);exit;

		return $this->parser->parse($template, $print_data);
	}

	/**
	*  Получение данных для грида журнала госпитализаций
	*  Входящие данные: $_POST
	*  На выходе: JSON-строка
	*  Используется: форма журнала госпитализаций swJournalHospitWindow
	*/
	function loadHospitalizationsGrid() {
		$this->load->library('swFilterResponse');
		
		$data = $this->ProcessInputData('loadHospitalizationsGrid', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadHospitalizationsGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Проверки при попытке добавить исход госпитализации
	 */
	function checkBeforeLeave()
	{
		$scenario = empty($_POST['isFromForm']) ? 'checkBeforeLeave' : 'checkBeforeLeaveFromForm';
		$this->inputRules['checkBeforeLeave'] = $this->dbmodel->getInputRules($scenario);
		$data = $this->ProcessInputData('checkBeforeLeave', true, true);
		if ( $data === false ) { return false; }
		$this->dbmodel->setScenario($scenario);
		$response = $this->dbmodel->checkBeforeLeave($data);
		$this->ProcessModelSave($response, true, 'Ошибка при проверке')
			->ReturnData();
		return true;
	}

	/**
	*  Сохранение карты выбывшего из стационара
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма редактирования карты выбывшего из стационара
	*/
	function saveEvnPS()
	{
		$this->inputRules['saveEvnPS'] = $this->dbmodel->getInputRules(swModel::SCENARIO_DO_SAVE);
		$this->load->helper('Reg_helper');
        $data = $this->ProcessInputData('saveEvnPS', true, true);

		if ( $data === false ) { return false; }

        if (empty($data['isAutoCreate'])) {
			$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		} else {
			$data['scenario'] = swModel::SCENARIO_AUTO_CREATE;
		}
		$response = $this->dbmodel->doSave($data);
		if(in_array(getRegionNick(), array('vologda','msk', 'ufa')) && !empty($response['EvnPS_id'])){
			//Проверка на педикулёз и санобработку
			$data['Evn_id'] = $response['EvnPS_id'];

			if (!empty($data['isPediculos']) || !empty($data['isScabies'])) {
				$data['Pediculos_SanitationDT'] = date("Y-m-d H:i:s", DayMinuteToTime(TimeToDay(strtotime($data['Pediculos_Sanitation_setDate'])), StringToTime($data['Pediculos_Sanitation_setTime'])));
				$save_pediculos = $this->dbmodel->savePediculos($data);
			} else if(empty($data['isPediculos']) && empty($data['isScabies']) && !empty($data['Pediculos_id'])) {
				$save_pediculos = $this->dbmodel->deletePediculos($data);
			}
			
			if(isset($save_pediculos) && $save_pediculos['success'] && !empty($save_pediculos['Pediculos_id'])) $response['Pediculos_id'] = $save_pediculos['Pediculos_id'];
		}
		if(getRegionNick() != 'kz'){
			if(!empty($response['EvnPS_id'])){
				$data['EvnPS_id'] = $response['EvnPS_id'];
			}
			$this->dbmodel->saveTraumaCircumEvnPS($data);
		}

		if (!empty($response['EvnPS_id']) && empty($response['Error_Msg'])) {
			$params = $data;
			$params['source'] = 'EvnPS';
			$params['EvnPS_id'] = $response['EvnPS_id'];
			$this->load->model('CVIRegistry_model', 'CVIRegistry_model');
			$this->CVIRegistry_model->saveCVIEvent($params);
		}

		$this->ProcessModelSave($response, true, 'Ошибка при сохранении карты выбывшего из стационара')
			->ReturnData();
		return true;
	}

	
	/**
	 * Функция проверки совпадения (Для КВС, в которой уже добавлен ЛВН с исходом ЛВН "Смерть" требуется, чтобы дата исхода госпитализации равнялась дате смерти и исход госпитализации был равен Смерть.)
	 *  Входящие данные: ...
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования КВС
	 */
	function CheckEvnPSDie() {
		$bool = true;
		
		$data = $this->ProcessInputData('CheckEvnPSDie', true);
		if ($data === false) { return false; }
		
		$bool = $this->dbmodel->CheckEvnPSDie($data);
		$this->ReturnData(array('success' => $bool));
		return $bool;
	}
	
	/**
	 * Функция проверки наличия у человека направления или самостоятельного обращения на текущие статистические сутки
	 *  Входящие данные: ...
	 *  На выходе: JSON-строка
	 *  Используется: рабочее место врача приемного отделения
	 */
	function checkSelfTreatment() {
		$response = array();
		$data = $this->ProcessInputData('checkSelfTreatment', true);
		if($data) {
			$response = $this->dbmodel->checkSelfTreatment($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else {
			$this->ReturnData(array('success' => false));
			return false;
		}
	}

	/**
	 * Функция сохранения причины отказа в госпитализации
	 *  Входящие данные: ...
	 *  На выходе: JSON-строка
	 *  Используется: рабочее место врача приемного отделения
	 */
	function saveEvnPSWithPrehospWaifRefuseCause()
	{
		$data = $this->ProcessInputData('saveEvnPSWithPrehospWaifRefuseCause', true);
		if (false == $data) { return false; }
		$response = $this->dbmodel->saveEvnPSWithPrehospWaifRefuseCause($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Проверка возможности госпитализации по направлению
	 */
	function checkDirHospitalize()
	{
		$data = $this->ProcessInputData('checkDirHospitalize', true);
		if (false === $data) { return false; }
		$response = $this->dbmodel->checkDirHospitalize($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
		
	/**
	 * Функция сохранения исхода пребывания в приемном отделении
	 */
	function saveEvnPSWithLeavePriem()
	{
		$data = $this->ProcessInputData('saveEvnPSWithLeavePriem', true);
		if (false === $data) { return false; }
		$response = $this->dbmodel->saveEvnPSWithLeavePriem($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Функция печати справки об отказе от госпитализации
	 *  Входящие данные: ...
	 *  На выходе: JSON-строка
	 *  Используется: рабочее место врача приемного отделения
	 */
	function printEvnPSPrehospWaifRefuseCause() {
		$this->load->library('parser');
		$data = $this->ProcessInputData('printEvnPSPrehospWaifRefuseCause', true);
		if($data && !empty($data['EvnPS_id']) && is_numeric($data['EvnPS_id'])) {
			$response = $this->dbmodel->printEvnPSEditForm($data);
			$this->ProcessModelList($response, false, true);
			//print_r($this->OutData);
			$this->load->model('EvnDirection_model', 'dir_model');
			$ndata = $this->dir_model->getDirectionDataForNotice($data);
			if (($ndata['PrehospWaifRefuseCause_Name'] = 'Отказ больного') && ($data['session']['region']['nick'] == 'pskov' )) {
				$template = 'print_evnps_prehospwaifrefusecause_pskov';
			}
			else {
				$template = 'print_evnps_prehospwaifrefusecause';
			}
			return $this->parser->parse($template, $this->OutData[0], false);
		}
		else {
			echo "";
			return false;
		}
	}
	
	/**
	 * Функция печати справки об отказе пациента от госпитализации
	 *  Входящие данные: ...
	 *  На выходе: JSON-строка
	 *  Используется: рабочее место врача приемного отделения
	 */
	function printPatientRefuse() {
		$this->load->library('parser');
		$data = $this->ProcessInputData('printPatientRefuse', true);
		if($data && !empty($data['EvnPS_id']) && is_numeric($data['EvnPS_id'])) {
			$response = $this->dbmodel->printPatientRefuse($data);
			$this->ProcessModelList($response, false, true);
			//print_r($this->OutData);
			$template = 'print_evnps_refusal_patient';
			return $this->parser->parse($template, $this->OutData[0], false);
		}
		else {
			echo "";
			return false;
		}
	}
	
	/**
	 * Функция получения данных для проверок перед открытием ЭМК
	 *  Входящие данные: POST['Person_id']
	 *  На выходе: JSON-строка
	 *  Используется: рабочее место врача приемного отделения, профильного отделения стационара
	 */
	function beforeOpenEmk() {
		$data = $this->ProcessInputData('beforeOpenEmk', true);
		if($data) {
			$response = $this->dbmodel->beforeOpenEmk($data);
			$this->ProcessModelList($response, true)->ReturnData();
			return true;
		}
		else {
			return false;
		}		
	}
	
	/**
	 * Функция сохранения признака "Передан активный вызов"
	 *  Входящие данные: POST['EvnPS_id']
	 *  На выходе: JSON-строка
	 *  Используется: рабочее место врача приемного отделения
	 */
	function setActiveCall() {
		$response = array();
		$data = $this->ProcessInputData('setActiveCall', true);
		if($data) {
			$response = $this->dbmodel->setActiveCall($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		}
		else {
			$this->ReturnData(array('success' => false));
			return false;
		}
	}

	/**
	 * Получение вида оплаты КВС
	 */
	function getEvnPSPayTypeSysNick() {
		$data = $this->ProcessInputData('getEvnPSPayTypeSysNick', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getEvnPSPayTypeSysNick($data);
		$this->ProcessModelSave($response, true, 'Ошибка при определении вида оплаты КВС')->ReturnData();

		return true;
	}

	/**
	 * Функция сохранения признака "Талон передан на ССМП"
	 *  Входящие данные: POST['EvnPS_id']
	 *  На выходе: JSON-строка
	 *  Используется: рабочее место врача приемного отделения
	 */
	function setTransmitAmbulance() {
		$response = array();
		$data = $this->ProcessInputData('setTransmitAmbulance', true);
		if($data) {
			$response = $this->dbmodel->setTransmitAmbulance($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		}
		else {
			$this->ReturnData(array('success' => false));
			return false;
		}
	}

	/**
	 * @param $lvl
	 * @param $message
	 */
	private function log_mes2($lvl, $message) {
        $this->textlog->add($lvl . ' ' .$message);
    }

	/**
	 * Очистка лога
 	 */
	public function clear_log() {
        $this->load->library('textlog', array('file'=>'export2miac.log', 'rewrite'=>true));
        $this->textlog->add('Логи очищены');
        $now = new Datetime();
        echo $now->format('d.m.Y H:i:s').' Логи очищены';
    }


	/**
	 * Выгрузка данных по коечному фонду в DBF
	 */
	function exportToDbfBedFond()
	{
        $this->load->library('textlog', array('file'=>'export2miac.log'));
		/**
		 * @param $fullfilename
		 * @return bool
		 */
		function delfile($fullfilename) {
			$result = @unlink($fullfilename);
			clearstatcache(true, $fullfilename);
			$i = 0;
			while (is_file($fullfilename) && $i < 50) {
				usleep(500);
				@unlink($fullfilename);
				clearstatcache(true, $fullfilename);
				$i++;
			}
			return $result;
		}

		/**
		 * @param $base_name
		 * @return bool|string
		 * @throws Exception
		 */
		function create_directories($base_name){
			$result = true;
			$out_dir = "exp";
			$dir_name_path = $base_name . EXPORTPATH_HOSPITAL_BED_FOND;
			if (!is_dir($dir_name_path)) {
				if (!mkdir($dir_name_path)) {
					throw new Exception("Не удалось создать каталог ($dir_name_path)!");
				}
			}
			if ($result) {
				$dir_name_path = $base_name . EXPORTPATH_HOSPITAL_BED_FOND . $out_dir;
				if(!is_dir($dir_name_path)){
					if (!mkdir($dir_name_path)) {
						throw new Exception("Не удалось создать каталог ($dir_name_path)!");
					}
				}
			}
			if ($result) {
				$result = EXPORTPATH_HOSPITAL_BED_FOND . $out_dir . '/';
			}
			return $result;
		}

		/**
		 * @param $archivefilename
		 * @param $filename
		 * @param string $archiver
		 * @param bool $deletefile
		 * @return bool
		 * @throws Exception
		 */
		function packit($archivefilename, $filename, $archiver = 'zip', $deletefile = false){
			$result = true;
			switch ($archiver) {
				case 'zip':
					$zip = new ZipArchive();
					$result = $result && $zip->open($archivefilename, ZIPARCHIVE::CREATE);
					$result = $result && $zip->addFile($filename, basename($filename));
					$result = $result && $zip->close();
					break;
				case 'arj':
					//создаю каталог в системной временной папке
					$tmp_folder = tempnam(sys_get_temp_dir(),'pmd');//создаю временный файл
					if ($tmp_folder) {
						unlink($tmp_folder);//удаляю его и использую как имя для временного каталога
						if (mkdir($tmp_folder)) {
							$arch_base_name = pathinfo($archivefilename, PATHINFO_BASENAME);
							if (is_file($archivefilename)) {
								//архив уже существует, надо добавить переданный файл в этот архив
								//копирую имеющийся архив во временный каталог
								if (!copy($archivefilename,$tmp_folder.'/'.$arch_base_name)){
									$result = false;
                                    throw new Exception("error copying given arch [$archivefilename] to temporary folder: $tmp_folder");
								}
							}
							if ($result) {
								if ($result && is_file($filename)) {
									$file_base_name = pathinfo($filename, PATHINFO_BASENAME);
									//копирую во временную папку файл, который хотим добавить
									if (copy($filename,$tmp_folder.'/'.$file_base_name)){
										if (is_file(EXPORTPATH_HOSPITAL_BED_FOND.'ARJ32.exe')) {
											//запускаю архиватор
											$root = $_SERVER["DOCUMENT_ROOT"];
											if (!($root[strlen($root) - 1] == "/")) {
												$root = $root . "/";
											}
                                            $run_command = $root.EXPORTPATH_HOSPITAL_BED_FOND."ARJ32.exe a -e \"".$tmp_folder.'/'.$arch_base_name."\" \"".$tmp_folder.'/'.$file_base_name."\"";
                                            $output = shell_exec($run_command);
											//проверяю результат архивации
											if ((!$output) || FALSE === strpos($output,'1 file(s)')) {
												//$result = false;
                                                throw new Exception('archiver return an error: '.$run_command.PHP_EOL.$output);
											} else {
												//перемещаю результат архивации на старое место
												if (!@rename($tmp_folder.'/'.$arch_base_name, $archivefilename)) {
                                                    throw new Exception('Error moving from '. $tmp_folder.'/'.$arch_base_name . ' to '. $archivefilename);
                                                };
												clearstatcache(true, $tmp_folder.'/'.$arch_base_name);
											}
										} else {
											//$result = false;
                                            throw new Exception("archiver [".EXPORTPATH_HOSPITAL_BED_FOND."ARJ32.exe] not found");
										}
										del_temp($tmp_folder, $file_base_name);
									} else {
										del_temp($tmp_folder);
										//$result = false;
                                        throw new Exception("error copying given file [$filename] to temporary folder: $tmp_folder");
									}
								} else {
                                    throw new Exception("[$filename] is not a file or does not exists");
								}
							}
						} else {
							//$result = false;
                            throw new Exception('error creating temporary folder: '.$tmp_folder);
						}
					} else {
						//$result = false;
                        throw new Exception('error creating temporary file: '.$tmp_folder);
					}
					break;
			}
            if ($deletefile) {
                if (!@unlink($filename)) {
                    throw new Exception('Не удалось удалить исходный файл после архивации ' . $filename);
                }
            }
			return $result;
		}

		/**
		 * @param $tmp_folder
		 * @param string $file_base_name
		 * @throws Exception
		 */
		function del_temp($tmp_folder, $file_base_name = '') {
			if ('' !== $file_base_name) {
				if (!delfile($tmp_folder . '/' . $file_base_name)) {
                    throw new Exception( 'error deleting file from temporary dir: ' . $tmp_folder . '/' . $file_base_name);
				}
				clearstatcache(true, $tmp_folder . '/' . $file_base_name);
			}
			clearstatcache($tmp_folder);
			if (!@rmdir($tmp_folder)) {
                throw new Exception('error deleting temporary dir: ' . $tmp_folder);
			}
		}
        /**
         * Переводит целое число от 1 до 31 в систему исчисления по основанию 32: 1 - 1, 2 - 2, ..., 10 - A, ..., 31 - V.
         * Для чисел вне диапазона [1,31] вернет false
         * @param int $i
         * @return string
         */
        function int2base32($i){
            if ($i >= 1 && $i <= 31) {
                if ($i>9) {
                    $chr = $i + 55;
                } else {
                    $chr = $i+48;
                }
                $result = chr($chr);
            } else {
                $result = false;
            }
            return $result;
        }

        /**
         * Ищет запись в справочнике.
         * Справочник должен раполагаться где надо: EXPORTPATH_HOSPITAL_BED_FOND.'RDS/'.$dict.'.dbf'
         * $field_name - Имя столбца, в котором надо искать значение $field_value, если не указано - ищется запись в которой первое поле  совпадает с $field_value
         * Находка (все поля, не только искомое) сохраняется в глобальной переменной $miac_export_seek_found_record.
         *
         * @param $exported_data Данные, полученные при этой выгрузке
         * @param $field_value
         * @param $dict
         * @param null $field_name
         * @throws Exception
         * @return bool
         */
        function seek($exported_data,$field_value, $dict, $field_name = null) {
            global $miac_export_seek_found_record;
            $result = false;//не найдено - возвращаю false
            if (null === $field_name) {
                $field_name = 0;
            }
            if (array_key_exists($dict, $exported_data)) {
                foreach($exported_data[$dict] as $r) {
                    //Если поле, по которому надо найти запись не указано, предполагаю, что ключом является первое поле
                    if (0 === $field_name) {
                        $array_keys = array_keys($r);
                        $field_name = $array_keys[0];
                    }
                    if ($r[$field_name] == $field_value) {
                        //найдено - возвращаю true
                        $result = true;
                        //запоминаю запись в глобальные(sic!) переменные - нужно для eval'a
                        $array_keys = array_keys($r);
                        foreach ($array_keys as $key) {
                            if (isset($r[$key])) {
                                $miac_export_seek_found_record["$dict.$key"] = $r[$key];
                            }
                        }
                        break;
                    }
                }
            } else {
                $dbfFile = EXPORTPATH_HOSPITAL_BED_FOND.'RDS/'.$dict.'.dbf';
                //открываю dbf
                if(!is_file($dbfFile)) {
                    throw new Exception('Не удалось выполнить логический контроль (Не найден файл со справочником ' . $dbfFile. ' для выполнения логического контроля выгруженных данных)');
                }
                $db = @dbase_open($dbfFile,0);
                if ($db) {
                    $record_numbers = dbase_numrecords($db);
                    //пробегаю в поисках искомого значения
                    for ($i = 1; $i <= $record_numbers; $i++) {
                        //Если поле, по которому надо найти запись не указано, предполагаю, что ключом является первое поле
                        if (0 === $field_name) {
                            $r = dbase_get_record ( $db, $i);
                        } else {
                            $r = dbase_get_record_with_names ( $db, $i);
                        }
                        if ($r) {
                            if (trim($r[$field_name]) == trim($field_value)) {
                                //найдено - возвращаю true
                                $result = true;
                                //запоминаю запись в глобальные(sic!) переменные - нужно для eval'a
                                $re = dbase_get_record_with_names ( $db, $i);
                                foreach ($re as $fnam => $fval) {
                                    $miac_export_seek_found_record["$dict.$fnam"] = $fval;
                                }
                                break;
                            }
                        } else {
                            throw new Exception("Не удалось выполнить логический контроль (Ошибка считывания записи $i из справочника $dbfFile при выполнении логического контроля выгруженных данных)");
                        }
                    }
                    dbase_close ( $db );
                } else {
                    throw new Exception('Не удалось выполнить логический контроль (Не удалось открыть файл со справочником ' . $dbfFile. ' для выполнения логического контроля выгруженных данных)');
                }
            }
            return $result;
        }

		/**
		 * @param $key
		 * @param $dict_elems
		 * @param $field
		 * @param string $table_name
		 * @return object|string
		 * @throws Exception
		 */
		function getJoinedTableFieldValue($key, $dict_elems, $field, $table_name = ''){
            $result = '';//не найдено - возвращаю пусто
            //пробегаю в поисках искомого значения
            foreach ($dict_elems as $elem) {
                $array_keys = array_keys($elem);
                if ($elem[$array_keys[0]] == $key) {//предполагается, что ключом является первое поле
                    //найдено - возвращаю значение
                    if (array_key_exists($field, $elem)){//если такое поле вообще есть
                        $result = $elem[$field];
                        if (is_object($result)) {
                            if ('DateTime' == get_class($result)) {
                                $result = ($result->getTimestamp()*(1.157407407407407e-6));
                            }
                        }
                    } else {//либо падаю
                        throw new Exception("Не удалось выполнить логический контроль (Запрошено значение несуществующего поля $field из справочника $table_name)");
                    }
                    break;
                }
            }
            return $result;
        }

		/**
		 * @param $val
		 * @return bool
		 */
		function empty_wrapped($val) {
            return empty($val);
        }

		/**
		 * @param $unix_epoch_days
		 * @return int
		 */
		function year_wrapped($unix_epoch_days) {
            $int = (int)floor($unix_epoch_days*864000);
            $date = date_timestamp_set(new Datetime(), $int);
            return (int)($date->format('Y'));
        }

		/**
		 * @return int
		 */
		function date_wrapped(){
            $now = new Datetime();
            return ($now->getTimestamp()*(1.157407407407407e-6));
        }

		/**
		 * @param $val
		 * @return string
		 */
		function str_wrapped($val){
            return (string)$val;
        }

		/**
		 * @param $val
		 * @return float
		 */
		function val_wrapped($val){
            return floatval($val);
        }

		/**
		 * @param $str
		 * @return int
		 */
		function ctod_wrapped($str){
            $d = DateTime::createFromFormat('d.m.Y', $str);
            return ($d->getTimestamp()*(1.157407407407407e-6));
        }

		/**
		 * @param $severity
		 * @param $message
		 * @param $filepath
		 * @param $line
		 * @return bool
		 */
		function err_handler($severity, $message, $filepath, $line){
            if ('' !== $message) {
                _exception_handler($severity, $message, $filepath, $line);
            }
            return false;
        }

		/**
		 * @param $what
		 * @param $min
		 * @param $max
		 * @return bool
		 */
		function BETW($what, $min, $max){
            return ((floatval($what)>=$min) && (floatval($what)<=$max));
        }
        set_error_handler('err_handler');//вызов обработчика ошибок CI через обертку, которая возвращает false, чтобы работала ф-ция error_get_last()
		$data = $this->ProcessInputData('exportToDbfBedFond', true);
		if($data){
			try {
                $warnings = array();
                $filename = 'bedfond.zip';
                $today = new Datetime();
                $export_settings = $this->bedFondExportSettings[$_SESSION['region']['nick']];
                if (isset($export_settings['filename_format'])) {
                    $filename = $export_settings['filename_format'];
                    if (strpos($filename, 'LLLLL')!==false) {
                        $Org_Code = $this->dbmodel->getFirstResultFromQuery('SELECT Org_Code FROM v_Lpu l WHERE l.Lpu_id = :Lpu_id', array('Lpu_id' => $data['Lpu_id']));
                        $LLLLL = str_pad($Org_Code, 5, '0', STR_PAD_LEFT);
                        $filename = str_replace('LLLLL', $LLLLL, $filename);
                    }
                    if (strpos($filename, 'YM16D32')!==false) {
                        $Y = $today->format('y');
                        $Y = $Y[1];
                        $M16 = int2base32((int)$today->format('m'));
                        $D32 = int2base32((int)$today->format('d'));
                        $YM16D32 = $Y.$M16.$D32;
                        $filename = str_replace('YM16D32', $YM16D32, $filename);
                    }
                }
				$dbf = '.DBF';
				//если архиватор не указан использую по умолчанию zip
				if (!isset($export_settings['archiver'])){
                    $export_settings['archiver'] = 'zip';
				}
				$root = $_SERVER["DOCUMENT_ROOT"];
				if (!($root[strlen($root) - 1] == "/")) {
					$root = $root . "/";
				}
				//Директория для хранения архива
				$out_dir = create_directories($root);
				if (!$out_dir) {
					throw new Exception('Ошибка создания каталогов');
				}
				$arch_full_name = $root . $out_dir . $filename;
				if (is_file($arch_full_name)) {
					unlink($arch_full_name);
				}
				$invoice_file = isset($export_settings['invoice_file']);
				$invoice_filesize = 0;
				$invoice_filescount = 0;
				foreach($export_settings['dbf_files'] as $dbf_filename => $exp_settings){
					// Данные для экспорта
					$response = $this->dbmodel->exportToDbfBedFond($data, $exp_settings['query']);
					if (is_array($response)) {
						if (!count($response)){
							$warnings[] = mb_strtoupper($dbf_filename).': Нет данных за указанный период';
                        }
                        if (isset($export_settings['validation'])) {
                            //запоминаю выгруженные данные чтобы потом прогнать их через проверки
                            $export_settings['exported_data'][$dbf_filename] = $response;
                        }
					} else {
						throw new Exception("Ошибка получения данных для экспорта");
					}
					$dbf_full_name = $root . $out_dir . $dbf_filename . $dbf;
					// Создаем dbase
					$this->exportDbf($dbf_full_name, $exp_settings['fields_dbf'], $response);
					// Архивируем результат
					if (!packit($arch_full_name, $dbf_full_name, $export_settings['archiver'], false)) {
						throw new Exception('Ошибка архивации ' . $dbf_full_name . '->' . $arch_full_name);
					}
					$fpt_full_name = mb_substr($dbf_full_name, 0, -3).'DBT';
					if (is_file($fpt_full_name)) {
						if (!packit($arch_full_name, $fpt_full_name, $export_settings['archiver'], false)) {
							throw new Exception('Ошибка архивации ' . $fpt_full_name . '->' . $arch_full_name);
						}
					}
					if ($invoice_file) {
						$invoice_filesize = $invoice_filesize + filesize($dbf_full_name);
						$invoice_filescount++;
					}
					if (!@unlink($dbf_full_name)) {
						throw new Exception( 'Не удалось удалить исходный файл после архивации ' . $dbf_full_name);
					}
                }
                $counter = array(
                    'records_checked' => 0,
                    'records_error' => 0,
                    'checks' => 0,//проверок произведено
                    'errors' => 0,//ошибок найдено
                    'error_detail' => array(),//детализация ошибок по записям
                    'record_detail' => array(),//детализация записей по ошибкам
                );
                $max_error_lvl = 0;
                if (isset($export_settings['validation'])) {
                    if (count($export_settings['exported_data'])){
                        $exported_data = $export_settings['exported_data'];
                    } else {
                        $exported_data = array();
                    }
                    foreach($export_settings['exported_data'] as $table => $records){//для каждого файла
                        if (isset ($export_settings['validation'][$table])) {
                            $this->log_mes2('debug', 'Начинаю проверку '.$table);
                            foreach ($records as $record) {//для каждой строки данных
                                $record = array_change_key_case($record,CASE_UPPER);
                                $pkey = array_keys($record);
                                $pkey = $pkey[0];
                                $invalid_record = false;
                                $errors_of_this_record = array();
                                foreach($export_settings['validation'][$table] as $errnum => $error) {//для каждой логической проверки
                                    $this->log_mes2('debug', 'Строка '.$pkey.' = '.var_export($record[$pkey],true).': начинаю проверку критерия '.$errnum);
                                    @trigger_error('');//сбрасываю ошибки
                                    //заменяю логические выражения AND, OR и т.п. на их эквиваленты в PHP
                                    global $miac_export_seek_found_record;
                                    $miac_export_seek_found_record = array();// сбрасываю значения, найденные с помощью seek
                                    $eval = $error['EXPR'];
                                    $eval = str_replace('EMPTY(', 'empty_wrapped(', $eval);
                                    $eval = str_replace('LEN(', 'strlen(', $eval);
                                    $eval = str_replace('ALLTRIM(', 'trim(', $eval);
                                    $eval = str_replace('ALLT(', 'trim(', $eval);//не уверен что именно так, не нашел никакой информации об этой Foxpro-функции
                                    $eval = str_replace('YEAR(', 'year_wrapped(', $eval);
                                    $eval = str_replace('STR(', 'str_wrapped(', $eval);
                                    $eval = str_replace('SEEK(', 'SEEK($exported_data,', $eval);
                                    $eval = str_replace('SUBstr_wrapped(', 'substr(', $eval);
                                    $eval = str_replace('VAL(', 'val_wrapped(', $eval);
                                    $eval = str_replace('DATE(', 'date_wrapped(', $eval);
                                    $eval = str_replace('CtoD(', 'ctod_wrapped(', $eval);
                                    $eval = str_replace(' AND ', ' && ', $eval);
                                    $eval = str_replace(' OR ', ' || ', $eval);
                                    $eval = str_replace('=', '==', $eval);
                                    $eval = str_replace('#', '!=', $eval);
                                    $eval = str_replace('>==', '>=', $eval);
                                    $eval = str_replace('<==', '<=', $eval);
                                    $field_values_to_be_checked = array();
                                    //заменяю имена полей в выражении на названия переменных
                                    foreach(explode(',',$error['FIELDS']) as $field ) {
                                        list($table_name, $field_name) = explode('.',$field);
                                        $table_name=trim($table_name);
                                        $field_name=trim($field_name);
                                        if ($table_name == $table) {
                                            //поле из этой таблицы
                                            if (!array_key_exists($field_name, $record)) {
                                                $this->log_mes2('error', 'В результатах запроса на получение данных для выгрузки отсутствует поле '.$field_name.', описанное в критериях логического контроля');
                                                throw new Exception('Не удалось выполнить проверку результата выгрузки');
                                            } else {
                                                if (is_object($record[$field_name])){
                                                    switch (get_class($record[$field_name])) {
                                                        case 'DateTime':
                                                            $replace = '('.$record[$field_name]->getTimestamp()*(1.157407407407407e-6).')';
                                                            break;
                                                        default:
                                                            $replace = var_export($record[$field_name], true);
                                                    }
                                                } else {
                                                    $replace = '\''.$record[$field_name].'\'';
                                                }
                                            }
                                        } else {
                                            //поле из другой таблицы
                                            if (isset($export_settings['exported_data'][$table_name])) {
                                                //таблица их тех, что мы навыгружали
                                                $record_keys = array_keys($record);
                                                $tkey = $record[$record_keys[0]];//предполагаю что в первом столбце лежит ключ
                                                $replace = '\''.getJoinedTableFieldValue($tkey, $export_settings['exported_data'][$table_name], $field_name, $table_name).'\'';
                                            } else {
                                                //используется запись, найденная с помощью seek
                                                $replace = '"{$miac_export_seek_found_record[\''.$table_name.'.'.$field_name.'\']}"';
                                            }
                                        }
                                        $field_values_to_be_checked[$table_name.'.'.$field_name] = $replace;//на случай если запись окажется ошибочной, собираю значения всех полей, которые входят в проверочное выражение
                                        $eval = str_replace("$table_name.{$field_name}", $replace, $eval);
                                    }
                                    $is_check_fail = null;
                                    $eval_res = @eval('$is_check_fail = '.$eval.';');//тут я могу глушить ошибки потому что проверяю потом функцией error_get_last()
                                    $counter['checks']++;
                                    if ($eval_res !== false &&  $is_check_fail !== null) {
                                        //код выполнился
                                        if ($is_check_fail) {
                                            //запись ошибочна, записываем ее в протокол
                                            $invalid_record = true;
                                            $counter['errors']++;
                                            if (!isset($counter['error_detail'][$table][$errnum])) {
                                                $counter['error_detail'][$table][$errnum] = 0;
                                            }
                                            $counter['error_detail'][$table][$errnum]++;
                                            $errors_of_this_record[] = $errnum;
                                            if ($error['ERRLVL'] > $max_error_lvl) {
                                                $max_error_lvl = $error['ERRLVL'];
                                            }
                                        }
                                    } else {
                                        //что-то упало в коде
                                        $this->log_mes2('error', 'Ошибка выполнения проверочного выражения '.$eval);
                                        throw new Exception('Не удалось выполнить проверку результата выгрузки');
                                    }
                                    $e = error_get_last();
                                    if ('' !== $e['message']) {
                                        $this->log_mes2('ERROR', "При проверке критерия логического контроля $errnum произошли ошибка {$e['message']}. Таблица $table, запись: ".var_export($record, true)." Выражение для проверки: $eval");
                                        throw new Exception('Не удалось выполнить проверку результата выгрузки. Более подробная информация содержится в логах приложения');
                                        //@trigger_error('');//сбрасываю последнюю ошибку
                                    }
                                }
                                $counter['records_checked']++;
                                if ($invalid_record){
                                    $counter['records_error']++;
                                    $counter['record_detail'][$table][] = array(
                                        'record_data' => $record,
                                        'errors' => $errors_of_this_record
                                    );
                                }
                            }
                        }
                    }
                }
                if (isset($export_settings['invoice_file'])) {
                    $this->exportDbf($root.$out_dir.'INVOICE.DBF',$export_settings['invoice_file'], array(
                        0 => array(
                            'LPU'        => $data['Lpu_id'],//Код учреждения, сформировавшего пакет
                            'PACKTYPE'   => 'HSP',//Тип информационного пакета (согласно табл. 3.1)
                            'PACKET'     => $filename,//Полное имя файла информационного пакета
                            'OUTDATE'    => $today,//Дата формирования информационного пакета
                            'REMARK'     => 'Экспорт статистики выполнен из системы "Промед"',//Описание содержимого пакета
                            'BEGDATE'    => Datetime::createFromFormat('Y-m-d',$data['begDate']),//Начальная дата периода выгрузки информации
                            'ENDDATE'    => Datetime::createFromFormat('Y-m-d',$data['endDate']),//Конечная дата периода выгрузки информации
                            'FILECOUNT'  => $invoice_filescount,//Общее количество файлов в пакете без учета сопроводительного файла
                            'FILESIZE'   => $invoice_filesize,//Общий объем файлов в пакете без учета размера сопроводительного файла (в байтах)
                            'REPEATDATE' => new Datetime(),//Максимально допустимая дата повторной передачи исправленной информации
                        )
                    ));
                    $dbf_full_name = $root.$out_dir.'INVOICE.DBF';
                    if (!packit($arch_full_name, $dbf_full_name, $export_settings['archiver'], true)) {
                        throw new Exception('Ошибка архивации ' . $dbf_full_name . '->' . $arch_full_name);
                    }
                    //формирование протокола загрузки
                    $explog = '';
                    if (count($counter['error_detail'])) {
                        $explog = $explog.PHP_EOL.'  из них:'.PHP_EOL;
                        foreach($counter['error_detail'] as $table => $tableerr) {
                            $explog = "$explog      в таблице $table ({$export_settings['dbf_files'][$table]['table_desc']}):".PHP_EOL;
                            foreach($tableerr as $errnum => $errdata){
                                $explog = "$explog        ".($errdata).ru_word_case(' ошибка',' ошибки',' ошибок',($errdata))." $errnum {$export_settings['validation'][$table][$errnum]['ERRLVL']} {$export_settings['validation'][$table][$errnum]['DESCRIPT']}".PHP_EOL;
                            }
                        }
                    }
                    $rec_detail = '';
                    if (count($counter['record_detail'])) {
                        foreach($counter['record_detail'] as $table => $records) {
                            $rec_detail = "{$rec_detail}Записи с ошибками из таблицы $table ({$export_settings['dbf_files'][$table]['table_desc']}):".PHP_EOL;
                            foreach($records as $recnum => $rec){
                                $recnum = $recnum + 1;
                                $rec_text = array();
                                foreach($rec['record_data'] as $fname => $fvalue){
                                    if (is_object($fvalue) && ('DateTime' === get_class($fvalue))){
                                        $rec_text[] = $fname.': '.$fvalue->format('d.m.Y');
                                    } else {
                                        $rec_text[] = $fname.': '.$fvalue;
                                    }
                                }
                                $rec_text = implode (', ', $rec_text);
                                $rec_detail = "$rec_detail  $recnum. $rec_text".PHP_EOL;
                                foreach($rec['errors'] as $errnum){
                                    $rec_detail = "$rec_detail    $errnum {$export_settings['validation'][$table][$errnum]['ERRLVL']} {$export_settings['validation'][$table][$errnum]['DESCRIPT']}".PHP_EOL;
                                }
                            }
                        }
                    }
                }
                // формирую ссылку на архив и отдаю клиенту
				$link = $out_dir . $filename;
				$this->log_mes2('debug', "[" . date('Y-m-d H:i:s') . "] Возвращаем ссылку на файл {$link}");
                if (!empty($explog) && !empty($rec_detail)){
                    $explog = <<<L
Записей обработано: {$counter['records_checked']}
Из них с ошибками: {$counter['records_error']}
Проверок произведено: {$counter['checks']}
Ошибок найдено: {$counter['errors']} (максимальный уровень: $max_error_lvl) $explog
L;
                    $rec_detail = 'Протокол выгрузки пакета HSP. Дата и время выполнения выгрузки: '.$today->format('Y-m-d H:i:s').PHP_EOL.$rec_detail;
                    file_put_contents($link.'.txt',$rec_detail);
                    $result = array('success'=> false);
                    $result['warning'] = 'В процессе выгрузки обнаружены ошибки.<br />
                    <pre style="width:100%; height: 200px; overflow: scroll;">'.($explog).'</pre><br />
                    Более подробная информация содержится в <a target="_blank" title="Скачать протокол выгрузки" href="'.$link.'.txt">протоколе выгрузки</a>';
                    if ($max_error_lvl<3) {
                        $result['warning'] = $result['warning'].'<br />Скачать <a target="_blank" title="Скачать результат выгрузки" href="'.$link.'">результат выгрузки</a>';
                    }
                    array_walk_recursive($result,'ConvertFromWin1251ToUTF8');
                } else {
                    $result = array('success'=> true, 'Link' => $link);
                    if (count($warnings)) {
                        $result['warning'] = 'Выгрузка завершена с предупреждениями:'.PHP_EOL.'<br />'.implode(PHP_EOL.'<br />', $warnings);
                        array_walk_recursive($result,'ConvertFromWin1251ToUTF8');
                    }
                }
				$this->ReturnData($result);
			} catch (Exception $e) {
				$this->log_mes2('error', $e->getMessage());
                 $result = array(
                        'success' => false,
                        'warning' => 'В процессе выгрузки произошли ошибки. Пожалуйста, обратитесь к разработчикам ('.$e->getMessage().')'
                );
                array_walk_recursive($result,'ConvertFromWin1251ToUTF8');
				$this->ReturnData($result);
			}
		}
	}

	/**
	 * @param $dbf_full_name
	 * @param $fields_dbf
	 * @param $response
	 * @throws Exception
	 */
	public function exportDbf($dbf_full_name, $fields_dbf, $response)
	{
		$h = dbase_create($dbf_full_name, $fields_dbf);
		if (!$h) {
			throw new Exception('dbase_create() fails ' . $dbf_full_name);
		}
		$add_ok = true;
		$cnt = 0;
        @trigger_error('');//сброс ошибки
		foreach ($response as $record) {
            $record = array_change_key_case($record, CASE_UPPER);
			foreach ($fields_dbf as $column) {
				switch ($column[1]) {
					case 'D':
						if (!empty($record[$column[0]])) {
							if ($record[$column[0]] instanceOf DateTime) {
								$record[$column[0]] = $record[$column[0]]->format('Ymd');
							} else {
								throw new Exception('Неверная дата в записи (' . implode(', ', $record) . ')');
							}
						}
						break;
					case 'C':
						if (!empty($record[$column[0]])) {
							ConvertFromWin1251ToCp866($record[$column[0]]);
						}
						break;
                    case 'M':
                        if (!empty($record[$column[0]])) {//если поле не пустое - падаю, я не умею их выгружать
                            throw new Exception('Выгрузка полей типа Memo не поддерживается.');
                        }
                        break;
                    default:
                        if (array_key_exists($column[0], $record)) {
                            if (is_object($record[$column[0]])) {
                                throw new Exception('Попытка записать объект без предварительного преобразования в строку. DBF-файл '.$dbf_full_name.' (Данные записи: ' . var_export($record, true) . ')');
                            }
                        } else {
                            throw new Exception("В результатах выполнения запроса для выгрузки в файл $dbf_full_name отсутствует столбец {$column[0]}, описанный в структуре таблицы");
                        }
				}
			}
			$add_ok = $add_ok && @dbase_add_record($h, array_values($record));
			if (!$add_ok) {
                $err = error_get_last();
                if ('' !== $err['message']) {
                    $err = 'Текст ошибки: '.$err['message'].', ';
                } else {
                    $err = '';
                }
                throw new Exception('Ошибка добавления записи в DBF-файл '.$dbf_full_name.' ('.$err.'Данные записи: ' . var_export($record, true) . ')');
			} else {
				$cnt++;
			}
		}
		$this->log_mes2('debug', 'Записей добавлено в '.$dbf_full_name.': ' . $cnt);
		if (!dbase_close($h)) {
			throw new Exception('Не удалось сохранить изменения в ' . $dbf_full_name);
		}
        //Если есть Memo-поля,
        //  создаем FPT-пустышку -
        //    учиться писать их пока нет необходимости - все memo пока выгружаются пустыми.
        $needFptFile = false;
        foreach ($fields_dbf as $column) {
            if ($column[1] == 'M') {
                $needFptFile = true;
                break;
            }
        }
        if ($needFptFile) {
            $fpr = fopen(mb_substr($dbf_full_name,0,-3).'DBT', 'w+');
            fwrite($fpr,str_repeat(chr(0x00),3));
            fwrite($fpr,chr(0x08));
            fwrite($fpr,str_repeat(chr(0x00),3));
            fwrite($fpr,chr(0x40));
            fwrite($fpr,str_repeat(chr(0x00),504));
            fclose($fpr);
        }
	}

    /**
     * Получение Morbus_id по психиатрии/наркологии https://redmine.swan.perm.ru/issues/36513
     */
    function getMorbusCrazy(){
        $data = $this->ProcessInputData('getMorbusCrazy',true);

        if ($data === false)
            return false;

        $response = $this->dbmodel->getMorbusCrazy($data);

        $this->ProcessModelList($response, true,true)->ReturnData();
        return true;
    }
	/**
     * checkEvnPSBirth
     */
    function checkEvnPSBirth(){
        $data = $this->ProcessInputData('checkEvnPSBirth',true);

        if ($data === false)
            return false;

        $response = $this->dbmodel->checkEvnPSBirth($data);

        $this->ProcessModelList($response, true,true)->ReturnData();
        return true;
    }
	/**
     * checkEvnPSBirth
     */
    function checkEvnPSChild(){
        $data = $this->ProcessInputData('checkEvnPSChild',true);

        if ($data === false)
            return false;

        $response = $this->dbmodel->checkEvnPSChild($data);

        $this->ProcessModelList($response, true,true)->ReturnData();
        return true;
    }

	/**
     * checkEvnPSSectionAndDateEqual
     */
    function checkEvnPSSectionAndDateEqual(){
        $data = $this->ProcessInputData('checkEvnPSSectionAndDateEqual',true);
        if ($data === false)return false;

        $response = $this->dbmodel->checkEvnPSSectionAndDateEqual($data);
        $this->ProcessModelList($response, true,true)->ReturnData();
        return true;
    }

	/**
     * checkEvnSectionSectionAndDateEqual
     */
    function checkEvnSectionSectionAndDateEqual(){
        $data = $this->ProcessInputData('checkEvnSectionSectionAndDateEqual',true);
        if ($data === false)return false;

        $response = $this->dbmodel->checkEvnSectionSectionAndDateEqual($data);
        $this->ProcessModelList($response, true,true)->ReturnData();
        return true;
    }

	/**
	 * getLastEvnPS
	 */
	function getLastEvnPS() {
		$data = $this->ProcessInputData('getLastEvnPS',true);
		if ($data === false)return false;

		$response = $this->dbmodel->getLastEvnPS($data);
		$this->ProcessModelList($response, true,true)->ReturnData();
		return true;
	}

	/**
	 * Получение информации о КВС из ЭРСБ
	 * Используется: swInfoKVSfromERSB
	 */
	function getInfoKVSfromERSB() {
		$data = $this->ProcessInputData('getInfoKVSfromERSB',true);
		if ($data === false)return false;

		$response = $this->dbmodel->getInfoKVSfromERSB($data);
		//$response = array('Hosp_id'=>123, 'Hosp_date'=>'2018');
		$this->ReturnData(array('success' => true, 'data' => $response ));
		return true;
	}

	/**
	 * Метод возвращает информацию о КВС, переданном в БГ
	 */
	function getInfoEvnPSfromBg() {
		$data = $this->ProcessInputData('getInfoEvnPSfromBg',true);
		if ($data === false)return false;

		$response = $this->dbmodel->getInfoEvnPSfromBg($data);
		$this->ReturnData(array($response));
		return true;
	}
	
	/**
	 * Метод возвращает информацию о КВС для контроля выбора в форме "Выбор отделения ЛПУ"
	 */
	function controlSavingForm_DepartmentSelectionLPU() {
		$data = $this->ProcessInputData('controlSavingForm_DepartmentSelectionLPU',true);
		if ($data === false)return false;

		$response = $this->dbmodel->controlSavingForm_DepartmentSelectionLPU($data);
		$this->ReturnData(array($response));
		return true;
	}

	/**
	 * Получения типа медицинской помощи
	 */
	function getMedicalCareBudgType() {
		$data = $this->ProcessInputData('getMedicalCareBudgType', true);
		if ( $data === false )  return;
		$response = $this->dbmodel->getMedicalCareBudgType($data);
		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Список профилей коек
	 */
	function getBedList() {
		$data = $this->ProcessInputData('getBedList', true);
		if ( $data === false )  return;
		$response = $this->dbmodel->getBedList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * получение дополнительной информации в блок информации о пациенте
	 */
	function getInfoPanelAdditionalInformation(){
		$data = $this->ProcessInputData('getInfoPanelAdditionalInformation', true);
		if ( $data === false )  return;
		$response = $this->dbmodel->getInfoPanelAdditionalInformation($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * получение id первого профильного движения КВС
	 */
	function getFirstProfileEvnSectionId(){
		$data = $this->ProcessInputData('getFirstProfileEvnSectionId', true);
		if ( $data === false )  return;
		$response = $this->dbmodel->getFirstProfileEvnSectionId($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
}
