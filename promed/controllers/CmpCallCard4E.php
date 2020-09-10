<?php	defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Class CmpCallCard4E
 * @property CmpCallCard_Model4E dbmodel
 */
class CmpCallCard4E extends swController {

	public $inputRules = array(
	    'portalProxyTest' => [],
		'getIsOverCallLpuBuildingData' => array(),
		'loadCmpCallCardEventType' => array(),
		'loadActiveCallRules' => array(
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'Идентификатор подразделения СМП',
				'rules' => '',
				'type' => 'id'
			)
		),
		'checkNeedActiveCall' => array(
			array()
		),
		'loadSMPDispatchDirectWorkPlace' => array(
			array(
				'field' => 'begDate',
				'label' => 'Дата с',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'hours',
				'label' => 'За последние часы',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'dispatchCallPmUser_id',
				'label' => 'Ид. диспетчера вызовов',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Numv',
				'label' => 'Номер вызова',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Ngod',
				'label' => 'Номер вызова за год',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'CmpLpu_id',
				'label' => 'ЛПУ куда доставлен',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'endDate',
				'label' => 'Дата по',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'Search_BirthDay',
				'label' => 'Дата рождения',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Search_FirName',
				'label' => 'Имя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Search_SecName',
				'label' => 'Отчество',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Search_SurName',
				'label' => 'Фамилия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpGroup_id',
				'label' => 'Статус',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'appendExceptClosed',
				'label' => 'Флаг вывода карт находящихся в обслуживании у бригад СМП',
				'rules' => '',
				'type' => 'boolean'
			),
		),
		'setCmpCallCardToControl' => array(
			array(
				'field' => 'CmpCallCard_id',
				'label' => 'Идентификатор карты вызова СМП',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'CmpCallCard_isControlCall',
				'label' => 'Флаг установки статуса на контроле',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'setDefferedCallToTransmitted' => array(
			array('field' => 'CmpCallCard_id','label' => 'Идентификатор карты вызова','rules' => 'required','type' => 'id'),
			array('field' => 'CmpCallCardStatusType_currentId', 'label' => 'Ид статуса карты для проверки', 'type' => 'int' ),
		),
		'copyCmpCallCard' => array(
			array('field' => 'CmpCallCard_id','label' => 'Идентификатор карты вызова','rules' => 'required','type' => 'id')
		),
		'saveCmpCallCard' => array(
			array(
				'field' => 'CmpArea_gid',
				'label' => 'В каком районе госпитализирован',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpArea_id',
				'label' => 'Код района (место вызова)',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpCallCard_rid',
				'label' => 'Код первичного вызова(карты)',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpArea_pid',
				'label' => 'Код района проживания',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpCallCard_City',
				'label' => 'Населенный пункт (место вызова)',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Street_id',
				'label' => 'Улица',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_UlicSecond',
				'label' => 'Вторая улица',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Area_id',
				'label' => 'Город',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Comm',
				'label' => 'Дополнительная информация',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_D201',
				'label' => 'Старший диспетчер смены',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Dlit',
				'label' => 'Длительность приема вызова в сек.',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Dokt',
				'label' => 'Фамилия старшего в бригаде',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Врач',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpCallCard_IsMedPersonalIdent',
				'label' => 'Признак идентификации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpCallCard_Dom',
				'label' => 'Дом (место вызова)',
				'rules' => '',
				'type' => 'string'
			),
            array(
                'field' => 'CmpCallCard_Korp',
                'label' => 'Корпус (место вызова)',
                'rules' => '',
                'type' => 'string'
            ),
			array(
				'field' => 'CmpCallCard_Dsp1',
				'label' => 'Принял',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Dsp2',
				'label' => 'Назначил',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Dsp3',
				'label' => 'Закрыл',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Dspp',
				'label' => 'Передал',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Etaj',
				'label' => 'Этаж (место вызова)',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Expo',
				'label' => 'Экспертная оценка',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_id',
				'label' => 'Идентификатор карты вызова СМП',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpCallCard_insID',
				'label' => 'Идентификатор карты вызова СМП для вставки',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpCallCard_IsAlco',
				'label' => 'Алкогольное (наркотическое) опьянение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpCallCard_IsPoli',
				'label' => 'Актив в поликлинику',
				'rules' => '',
				'type' => 'swcheckbox'
			),
			array(
				'field' => 'CmpCallCard_Izv1',
				'label' => 'Извещение',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Kakp',
				'label' => 'Как получен',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Kilo',
				'label' => 'Километраж, затраченный на вызов',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'CmpCallCard_Kodp',
				'label' => 'Код замка в подъезде (домофон) (место вызова)',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Ktov',
				'label' => 'Кто вызывает',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Kvar',
				'label' => 'Квартира (место вызова)',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Line',
				'label' => 'Пульт приема',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Ncar',
				'label' => 'Номер машины',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Ngod',
				'label' => 'Номер с начала года',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Numb',
				'label' => 'Номер бригады',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Numv',
				'label' => 'Номер вызова',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_PCity',
				'label' => 'Населенный пункт (адрес проживания)',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_PDom',
				'label' => 'Дом (адрес проживания)',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_PKvar',
				'label' => 'Квартира (адрес проживания)',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Podz',
				'label' => 'Подъезд (место вызова)',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Prdl',
				'label' => 'Номер строки из списка предложений',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_prmDT',
				'label' => 'Дата и время приема',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Prty',
				'label' => 'Приоритет',
				'rules' => '',
				'type' => 'int'
			),array(
				'field' => 'CmpCallCard_IsNMP',
				'label' => 'НМП/СМП',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_PUlic',
				'label' => 'Улица (адрес проживания)',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_RCod',
				'label' => 'Код рации',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Sect',
				'label' => 'Сектор',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Smpb',
				'label' => 'Код станции СМП бригады',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Smpp',
				'label' => 'Код ССМП приема вызова',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Smpt',
				'label' => 'Код территориальной станции СМП',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Stan',
				'label' => 'Номер П/С',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Stbb',
				'label' => 'Номер П/С базирования бригады',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Stbr',
				'label' => 'Номер П/С бригады по управлению',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Tab2',
				'label' => 'Номер 1-го помощника',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Tab3',
				'label' => 'Номер 2-го помощника',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Tab4',
				'label' => 'Водитель',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_TabN',
				'label' => 'Номер старшего в бригаде',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Telf',
				'label' => 'Телефон',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Tiz1',
				'label' => 'Время передачи извещения',
				'rules' => '',
				'type' => 'time'
			),
			array(
				'field' => 'CmpCallCard_Tsta',
				'label' => 'Время прибытия в стационар',
				'rules' => '',
				'type' => 'time'
			),
			array(
				'field' => 'CmpCallCard_Tvzv',
				'label' => 'Время возвращения на станцию',
				'rules' => '',
				'type' => 'time'
			),
			array(
				'field' => 'CmpCallCard_Ulic',
				'label' => 'Улица',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Vr51',
				'label' => 'Старший врач смены',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallType_id',
				'label' => 'Тип вызова',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpDiag_aid',
				'label' => 'Диагноз (осложнение)',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpDiag_oid',
				'label' => 'Диагноз (основной)',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpLpu_id',
				'label' => 'Куда доставлен',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpPlace_id',
				'label' => 'Местонахождение больного',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpProfile_bid',
				'label' => 'Профиль бригады',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpProfile_cid',
				'label' => 'Профиль вызова',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpReason_id',
				'label' => 'Повод',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpResult_id',
				'label' => 'Результат',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ResultDeseaseType_id',
				'label' => 'Исход',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpTalon_id',
				'label' => 'Признак расхождения диагнозов или причина отказа стационара',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpTrauma_id',
				'label' => 'Вид заболевания',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_sid',
				'label' => 'Диагноз стационара',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_uid',
				'label' => 'Уточненный код диагноза по МКБ-10',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ создания вызова',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_ppdid',
				'label' => 'ЛПУ передачи',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuTransmit_id',
				'label' => 'ЛПУ передачи',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_oid',
				'label' => 'Куда доставлен',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_IsUnknown',
				'label' => 'Неизвестный',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_Age',
				'label' => 'Возраст',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Person_Birthday',
				'label' => 'Дата рождения',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Polis_Ser',
				'label' => 'Серия полиса',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Polis_Num',
				'label' => 'Номер полиса',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_PolisNum',
				'label' => 'Номер полиса',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_PolisSer',
				'label' => 'Серия полиса',
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
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Sex_id',
				'label' => 'Пол',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 2,
				'field' => 'CmpCallCard_IsOpen',
				'label' => 'Открытая карта',
				'rules' => '',
				'type' => 'int'
			),
			array(

				'field' => 'KLRgn_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'KLSubRgn_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'KLCity_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'KLTown_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'KLStreet_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ARMType',
				'label' => '',
				'rules' => '',
				'type' => 'string'
			),
			array(
				//'default' => 1,
				'field' => 'Person_isOftenCaller',
				'label' => 'Индикатор нахождения в регистре часто обращающихся',
				'rules' => '',
				'type' => 'int'
			)
			,
			array(
				'field' => 'CmpCallCard_Inf1',
				'label' => '',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Inf2',
				'label' => '',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Inf3',
				'label' => '',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Inf4',
				'label' => '',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Inf5',
				'label' => '',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Inf6',
				'label' => '',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'UnformalizedAddressDirectory_id',
				'label' => '',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_DiffTime',
				'label' => '',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'Идентификатор подразделения СМП',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор службы НМП',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpCallerType_id',
				'label' => 'Идентификатор типа вызывающего',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpCallPlaceType_id',
				'label' => 'Идентификатор типа места вызова',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpCallCard_CallLtd',
				'label' => 'Широта',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_CallLng',
				'label' => 'Долгота',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Reason_isNMP',
				'label' => '',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Urgency',
				'label' => 'Срочность',
				'rules' => '',
				'type' => 'int'
			),
			/*array(
				'field' => 'CmpCallCard_IsReceivedInPPD',
				'label' => '',
				'rules' => '',
				'type' => 'int'
			),*/
			array(
				'field' => 'typeSave',
				'label' => 'Статус карты дублирования',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpRejectionReason_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpCallType_Code',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpCallCardStatus_Comment',
				'label' => '',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_IsDeterior',
				'label' => 'Признак ухудшения состояния',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'copyParamsToOthersCards',
				'label' => 'Список параметров для копирования в другие карты',
				'rules' => '',
				'type' => 'string'
			),
			array('field' => 'CmpCallCard_Tper','label' => 'Время передачи','rules' => '',	'type' => 'string'),
			array('field' => 'CmpCallCard_Vyez','label' => 'Время выезда','rules' => '',	'type' => 'string'),
			array('field' => 'CmpCallCard_Przd','label' => 'Время прибытия на место вызова','rules' => '',	'type' => 'string'),
			array('field' => 'CmpCallCard_Tgsp','label' => 'Время начало транспортировки больного','rules' => '',	'type' => 'string'),
			array('field' => 'CmpCallCard_Tsta','label' => 'Время приезда на станцию','rules' => '',	'type' => 'string'),
			array('field' => 'CmpCallCard_prmDT','label' => 'Время приема','rules' => '',	'type' => 'string'),
			array('field' => 'CmpCallCard_Tvzv','label' => 'Время возвращения на станцию','rules' => '',	'type' => 'string'),
			array('field' => 'CmpCallCard_Tisp','label' => 'Время окончания вызова','rules' => '',	'type' => 'string'),
			array('field' => 'CmpCallCard_HospitalizedTime', 'label' => 'Время госпитализации', 'type' => 'string' ),
			array('field' => 'EmergencyTeam_id', 'label' => 'Ид бригады', 'type' => 'int' ),
			array('field' => 'CmpCallCardStatusType_id', 'label' => 'Ид статуса карты', 'type' => 'int' ),
			array('field' => 'CmpCallCardStatusType_currentId', 'label' => 'Ид статуса карты для проверки', 'type' => 'int' ),
			array('field' => 'CmpCallCard_IsExtra', 'label' => 'Вид вызова', 'type' => 'int' ),
			array('field' => 'CmpCallCard_storDT', 'label' => 'Дата и время отложенного вызова', 'type' => 'string' ),
			array('field' => 'CmpCallCard_defCom', 'label' => 'Комментарий отложенного вызова', 'type' => 'string'),
			array('field' => 'Lpu_smpid', 'label' => 'МО передачи СМП', 'type' => 'id'),
			array('field' => 'CmpCallCard_IsPassSSMP', 'label' => 'Вызов передан в другую ССМП по телефону (рации)', 'type' => 'swcheckbox'),
			array('field' => 'Lpu_hid', 'label' => 'МО госпитализации', 'type' => 'id'),
			array('field' => 'UnformalizedAddressDirectory_wid', 'label' => 'Объект МО госпитализации', 'type' => 'id'),
			array('field' => 'withoutChangeStatus', 'label' => 'Сохранить без смены статуса', 'type' => 'boolean'),
			array('field' => 'CmpCallCard_sid', 'label' => 'Ид связанного вызова (Первого в группе множественных)', 'type' => 'int'),
			array('field' => 'CmpCallCard_IsActiveCall', 'label' => '', 'rules' => '', 'type' => 'int'),
			array('field' => 'CmpCallCard_updDT','label' => 'Время последнего обновления записи вызова','rules' => '',	'type' => 'string'),
			array('field' => 'AmbulanceDecigionTree_id','label' => 'Ид выбранной ноды в дереве решений','rules' => '',	'type' => 'int'),
			array('field' => 'CmpCallCard_IsQuarantine', 'label' => 'Карантин', 'rules' => '', 'type' => 'swcheckbox'),
			array('field' => 'PlaceArrival_id', 'label' => 'Прибытие', 'rules' => '', 'type' => 'int'),
			array('field' => 'KLCountry_id', 'label' => 'Страна', 'rules' => '', 'type' => 'int'),
			array('field' => 'OMSSprTerr_id', 'label' => 'Регион', 'rules' => '', 'type' => 'int'),
			array('field' => 'ApplicationCVI_arrivalDate', 'label' => 'Дата прибытия', 'rules' => '', 'type' => 'date'),
			array('field' => 'ApplicationCVI_flightNumber', 'label' => 'Рейс', 'rules' => '', 'type' => 'string'),
			array('field' => 'ApplicationCVI_isContact', 'label' => 'Контакт с человеком с подтвержденным диагнозом КВИ', 'rules' => '', 'type' => 'int'),
			array('field' => 'ApplicationCVI_isHighTemperature', 'label' => 'Высокая температура', 'rules' => '', 'type' => 'int'),
			array('field' => 'Cough_id', 'label' => 'Кашель', 'rules' => '', 'type' => 'int'),
			array('field' => 'Dyspnea_id', 'label' => 'Одышка', 'rules' => '', 'type' => 'int'),
			array('field' => 'ApplicationCVI_Other', 'label' => 'Другое', 'rules' => '', 'type' => 'string'),
			array('field' => 'isSavedCVI', 'label' => 'Анкета КВИ', 'rules' => '', 'type' => 'int')
		),
		'getDiags' => array(
			array('field' => 'where','label' => 'Условия','rules' => '','type' => 'string'),
			array('field' => 'top','label' => 'Первая загрузка','rules' => '','type' => 'string'),
			array('field' => 'query','label' => 'Первая загрузка','rules' => '','type' => 'string')
		),
		'saveCmpCallCardClose' => array(
			array('field' => 'CmpCallCard_id','label' => 'Идентификатор карты вызова СМП','rules' => '','type' => 'id'),
			array('field' => 'CmpCallCard_rid','label' => 'Код первичного вызова(карты)','rules' => '','type' => 'int'),
			array('field' => 'CmpCallCard_Kodp','label' => 'Код замка в подъезде (домофон) (место вызова)','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Comm','label' => 'Дополнительная информация','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_IsAlco','label' => 'Алкогольное (наркотическое) опьянение','rules' => '','type' => 'id'),
			array('field' => 'CmpCallCard_Kilo','label' => 'Километраж, затраченный на вызов','rules' => '','type' => 'float'),
			array('field' => 'CmpCallCard_Ktov','label' => 'Кто вызывает','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Telf','label' => 'Телефон','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Przd','label' => 'Время прибытия на адрес','rules' => '','type' => 'time'),
			array('field' => 'CmpCallCard_Vyez','label' => 'Время выезда','rules' => '','type' => 'time'),
			array('field' => 'CmpCallCard_Tgsp','label' => 'Время отзвона о госпитализации','rules' => '','type' => 'time'),
			array('field' => 'CmpCallCard_Tisp','label' => 'Время исполнения (освобождения)','rules' => '','type' => 'time'),
			array('field' => 'CmpCallCard_Tsta','label' => 'Время прибытия в стационар','rules' => '','type' => 'time'),
			array('field' => 'CmpCallCard_Tvzv','label' => 'Время возвращения на станцию','rules' => '','type' => 'time'),
			array('field' => 'CmpCallCard_Ulic','label' => 'Улица','rules' => '','type' => 'string'),
			array('field' => 'CmpCallType_id','label' => 'Тип вызова','rules' => '','type' => 'id'),
			array('field' => 'CmpDiag_aid','label' => 'Диагноз (осложнение)','rules' => '','type' => 'id'),
			array('field' => 'CmpDiag_oid','label' => 'Диагноз (основной)','rules' => '','type' => 'id'),
			array('field' => 'CmpPlace_id','label' => 'Местонахождение больного','rules' => '','type' => 'id'),
			array('field' => 'CmpReason_id','label' => 'Повод','rules' => 'required','type' => 'id'),
			array('field' => 'CmpResult_id','label' => 'Результат','rules' => '','type' => 'id'),
			array('field' => 'ResultDeseaseType_id','label' => 'Исход','rules' => '','type' => 'id'),
			array('field' => 'CmpTrauma_id','label' => 'Вид заболевания','rules' => '','type' => 'id'),
			array('field' => 'LpuTransmit_id','label' => 'ЛПУ передачи','rules' => '','type' => 'id'),
			array('field' => 'Person_Age','label' => 'Возраст','rules' => '','type' => 'int'),
			array('field' => 'Person_Birthday','label' => 'Дата рождения','rules' => '','type' => 'date'),
			array('field' => 'Person_Firname','label' => 'Имя','rules' => '','type' => 'string'),
			array('field' => 'Person_id','label' => 'Идентификатор человека','rules' => '','type' => 'id'),
			array('field' => 'Polis_Ser','label' => 'Серия полиса','rules' => '','type' => 'string'),
			array('field' => 'Polis_Num','label' => 'Номер полиса','rules' => '','type' => 'string'),
			array('field' => 'Person_PolisSer','label' => 'Серия полиса','rules' => '','type' => 'string'),
			array('field' => 'Person_Secname','label' => 'Отчество','rules' => '','type' => 'string'),
			array('field' => 'Person_Surname','label' => 'Фамилия','rules' => '','type' => 'string'),
			array('field' => 'Sex_id','label' => 'Пол','rules' => '','type' => 'id'),
			array('default' => 2,'field' => 'CmpCallCard_IsOpen','label' => 'Открытая карта','rules' => '','type' => 'int'),
			array('field' => 'CmpCallCard_DiffTime','label' => '','rules' => '','type' => 'string'),
			array('field' => 'CmpCallerType_id','label' => 'Идентификатор типа вызывающего','rules' => '','type' => 'id'),
			array('field' => 'CmpCallPlaceType_id','label' => 'Идентификатор типа места вызова','rules' => 'required','type' => 'id'),
			array('field' => 'CmpDiseaseAndAccidentType_id','label' => 'Идентификатор типа травмы и заболевания ','rules' => '','type' => 'id'),
			array('field' => 'CmpCallReasonType_id','label' => 'Идентификатор типа результата','rules' => 'required','type' => 'id'),
			array('field' => 'EmergencyTeamDrugPackMoveList', 'label' => 'JSON-массив списанных медикаментов', 'rules' => '', 'type' => 'json_array', 'assoc' => true ),
		),
		'setStatusCmpCallCard' => array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'CmpCallCard_rid', 'label' => 'Ид. родит. карты вызова', 'rules' => '', 'type' => 'id' ),
			array('field' => 'CmpCallCardStatusType_id', 'label' => 'Устанавливаемый статус', 'rules' => '', 'type' => 'int' ),
			array('field' => 'CmpCallCardStatusType_Code', 'label' => 'Устанавливаемый код статуса', 'rules' => '', 'type' => 'int' ),
			array('field' => 'CmpCallType_id', 'label' => 'Тип вызова', 'rules' => '', 'type' => 'int' ),
			array('field' => 'CmpCallType_Code', 'label' => 'Код типа вызова', 'rules' => '', 'type' => 'int' ),
			array('field' => 'CmpCallCardStatus_Comment', 'label' => 'Комментарий к статусу', 'rules' => '', 'type' => 'string' ),
			array('field' => 'CmpMoveFromNmpReason_id', 'label' => 'Ид. причины передачи из НМП в СМП', 'rules' => '', 'type' => 'id' ),
			array('field' => 'CmpReturnToSmpReason_id', 'label' => 'Ид. причины возврата в СМП из НМП', 'rules' => '', 'type' => 'id' ),
			array('field' => 'CmpCallCard_IsOpen', 'label' => 'Признак открытия карты', 'rules' => '', 'type' => 'int' ),
			array('field' => 'armtype', 'label' => 'Тип АРМ', 'rules' => '', 'type' => 'string' ),
			array('field' => 'CmpReason_id', 'label' => 'Причина отказа', 'rules' => '', 'type' => 'int' ),
			array('field' => 'CmpRejectionReason_id', 'label' => 'Причина отказа', 'rules' => '', 'type' => 'int' ),
			array('field' => 'CmpRejectionReason_Name', 'label' => 'Причина отказа', 'rules' => '', 'type' => 'string' ),
			array('field' => 'typeSetStatusCCC', 'label' => 'Тип отмены вызова', 'rules' => '', 'type' => 'string' )
		),
		'setStatusCmpCallCard112' => array(
			array('field' => 'CmpCallCard112_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'CmpCallCard112StatusType_id', 'label' => 'Устанавливаемый статус', 'rules' => '', 'type' => 'int' )
		),		
		'setStatusCmpCallCardList112' => array(
			array('field' => 'CmpCallCard112_ids', 'label' => 'Ид. карт вызова', 'rules' => 'required', 'type' => 'string' ),
			array('field' => 'allCards', 'label' => 'Флаг поиска всех карт112', 'rules' => '', 'type' => 'boolean' ),
			array('field' => 'CmpCallCard112StatusType_id', 'label' => 'Устанавливаемый статус', 'rules' => '', 'type' => 'int' )
		),
		'setStatusCmpCallCardByHD' => array(
			array( 'field' => 'CmpCallCard_id', 'label' => 'Ид талона', 'rules' => 'required', 'type' => 'id' ),
			array( 'field' => 'CmpCallCard_rid', 'label' => 'Ид первичного талона', 'rules' => '', 'type' => 'id' ),
			array('field' => 'CmpCallCard_Comm','label' => 'Дополнительная информация','rules' => '','type' => 'string'),
			array( 'field' => 'callType', 'label' => 'Тип второго вызова', 'rules' => 'required', 'type' => 'string' ),
			array( 'field' => 'action', 'label' => 'Действие', 'rules' => 'required', 'type' => 'string' ),
			array( 'field' => 'CmpCallCard_IsDeterior', 'label' => 'признак Ухудшение состояния', 'rules' => '', 'type' => 'int' ),
		),
		'getCallAudio' => array(
			array(
				'field' => 'CmpCallRecord_id',
				'label' => 'Ид талона',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getCallAudioList' => array(
			array(
				'field' => 'dateStart',
				'label' => 'Дата начала поиска',
				'rules' => '',
				'type' => 'date',
			),
			array(
				'field' => 'dateFinish',
				'label' => 'Дата окончания поиска',
				'rules' => '',
				'type' => 'date',
			),
			array(
				'field' => 'audioIds',
				'label' => 'Дата окончания поиска',
				'rules' => '',
				'type' => 'string',
			)
		),
		'saveCallAudio' => array(
			array(
				'field' => 'callAudio',
				'label' => 'Аудиозапись звонка вызова СМП',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'Идентификатор подразделения СМП',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpCallCard_id',
				'label' => 'Ссылка на запись',
				'rules' => '',
				'type' => 'id'
			),
		),
		'removeCallAudio' => array(
			array(
				'field' => 'CmpCallRecord_id',
				'label' => 'Ид талона',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getExportCallAudios' => array(
			array(
				'field' => 'audioIds',
				'label' => 'Ид талона',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		'removeCallAudioBytimer' => array(),
		'setLpuHospitalized' => array(
			array(
				'field' => 'Lpu_hid',
				'label' => 'Ид мо госпитализации',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Ид отделения',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'viewAllMO',
				'label' => 'Показать все МО',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'CmpCallCard_id',
				'label' => 'Ид талона',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'Code',
				'label' => 'Код подразделения',
				'rules' => 'required',
				'type'  => 'string'
			),
			array(
				'field' => 'EmergencyTeam_id',
				'label' => 'Номер бригады',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Numg',
				'label' => 'Номер вызова за год',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Person_id',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnDirection_IsAuto',
				'label' => 'Автоматическое направление',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Ид диагноза',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'cmpcommonstate_id',
				'label' => 'Ид состояния',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'formType',
				'label' => 'Тип формы, зависящий от статуса бригады',
				'rules' => '',
				'type' => 'string'
			)
		),
		'saveScales' => array(
				array(
					'field' => 'FaceAsymetry_id',
					'label' => 'Асиметрия лица',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'HandHold_id',
					'label' => 'Удержание рук',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'SqueezingBrush_id',
					'label' => 'Сжимание в кисти',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'ScaleLams_Value',
					'label' => 'Баллы (Шкала LAMS)',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PainResponse_id',
					'label' => 'Реакция на боль',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'ExternalRespirationType_id',
					'label' => 'Харакер внешнего дыхания',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'SystolicBloodPressure_id',
					'label' => 'Систолическое АД',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'InternalBleedingSigns_id',
					'label' => 'Признаки внутреннего кровотечения',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LimbsSeparation_id',
					'label' => 'Отрыв конечности',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PrehospTraumaScale_Value',
					'label' => 'Баллы (оценка тяжести)',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'ResultECG',
					'label' => 'Результат ЭКГ',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'ECGDT',
					'label' => 'Дата проведения ЭКГ',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'PainDT',
					'label' => 'Дата начала заболевания',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'TLTDT',
					'label' => 'Дата проведения ТЛТ',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'FailTLT',
					'label' => 'Причина отказа от ТЛТ',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Ид пациента',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'CmpCallCard_id',
					'label' => 'Ид вызова',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'diagType',
					'label' => 'Тип диагноза',
					'rules' => '',
					'type' => 'string'
				)
		),
		'loadSMPCmpCallCardsList' => array(
			array(
				'field' => 'begDate',
				'label' => 'Дата с',
				'rules' => 'trim|required',
				'type' => 'datetime'
			),
			array(
				'field' => 'hours',
				'label' => 'За последние часы',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'dispatchCallPmUser_id',
				'label' => 'Ид. диспетчера вызовов',
				'rules' => 'trim',
				'type' => 'string'
			),
            array(
				'field' => 'cmpCallCardList',
				'label' => 'Массив карт',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EmergencyTeam_id',
				'label' => 'Ид. бригады',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Numv',
				'label' => 'Номер вызова',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Ngod',
				'label' => 'Номер вызова за год',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'CmpLpu_id',
				'label' => 'ЛПУ куда доставлен',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'endDate',
				'label' => 'Дата по',
				'rules' => 'trim',
				'type' => 'datetime'
			),
			array(
				'field' => 'Search_BirthDay',
				'label' => 'Дата рождения',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'Search_FirName',
				'label' => 'Имя',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Search_SecName',
				'label' => 'Отчество',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Search_SurName',
				'label' => 'Фамилия',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'KLCity_id',
				'label' => 'Город',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Town_id',
				'label' => 'Населенный пункт',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'KLStreet_id',
				'label' => 'Улица',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'UnformalizedAddressDirectory_id',
				'label' => 'Объект',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'showByDp',
				'label' => 'Признак открытия из дп',
				'rules' => 'trim',
				'type' => 'string'
			)
		),
		'loadCmpCallCardEditForm' => array(
			array(
				'field' => 'CmpCallCard_id',
				'label' => 'Идентификатор карты вызова',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'CmpCloseCard_id',
				'label' => 'Идентификатор карты вызова',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadSmpFarmacyRegister' => array(
		),
		'loadSmpFarmacyRegisterHistory' => array(
			array('field' => 'CmpFarmacyBalance_id','label' => 'Идентификатор медикамента в регистре','rules' => 'required','type' => 'id'),
			array('default' => 100,'field' => 'limit','label' => 'Количество','rules' => 'trim','type' => 'int'),
			array('default' => 0,'field' => 'start','label' => 'Старт','rules' => 'trim','type' => 'int')
		),
		'saveSmpFarmacyDrug' => array(
			array('field' => 'CmpFarmacyBalanceAddHistory_RashCount','label' => 'Количество (ед. уч.)','rules' => 'required','type' => 'float'),
			array('field' => 'CmpFarmacyBalanceAddHistory_RashEdCount','label' => 'Количество (ед. доз.)','rules' => 'required','type' => 'float'),
			array('field' => 'Drug_id','label' => 'Идентификатор медикамента','rules' => 'required','type' => 'id'),
			array('field' => 'CmpFarmacyBalanceAddHistory_AddDate','label' => 'Дата поставки','rules' => 'required','type' => 'date'),
		),
		'removeSmpFarmacyDrug' => array(
			array('field' => 'CmpFarmacyBalance_id','label' => 'Идентификатор медикамента в регистре','rules' => 'required','type' => 'id'),
			array('field' => 'CmpFarmacyBalanceRemoveHistory_PackCount','label' => 'Списываемое количество (ед. уч.)','rules' => 'required','type' => 'float'),
			array('field' => 'CmpFarmacyBalanceRemoveHistory_DoseCount','label' => 'Списываемое количество (ед. доз.)','rules' => 'required','type' => 'float'),
			array('field' => 'CmpCallCard_id','label' => 'Идентификатор талона вызова','rules' => '','type' => 'id'),
			array('field' => 'EmergencyTeam_id','label' => 'Идентификатор бригады','rules' => '','type' => 'id'),
		),
		'loadUnformalizedAddressDirectory'=>array(
			array('field' => 'UnformalizedAddressType_id','label' => 'Тип объекта','rules' => 'trim','type' => 'int'),
			array('default' => 100,'field' => 'limit','label' => 'Количество','rules' => 'trim','type' => 'int'),
			array('default' => 0,'field' => 'start','label' => 'Старт','rules' => 'trim','type' => 'int'),
			array('field' => 'UnformalizedAddressDirectory_Name','label' => 'Название объекта','rules' => 'trim','type' => 'string'),
			array('field' => 'UnformalizedAddressDirectoryType_Name','label' => 'Тип объекта 1','rules' => 'trim','type' => 'string'),
			array('field' => 'Lpu_aid','label' => 'МО','rules' => 'trim','type' => 'string'),
			array('field' => 'LpuBuilding_Name','label' => 'СМП подразделение','rules' => 'trim','type' => 'string'),
			array('field' => 'UnformalizedAddressDirectory_lat','label' => 'Широта','rules' => 'trim','type' => 'string'),
			array('field' => 'UnformalizedAddressDirectory_lng','label' => 'Долгота','rules' => 'trim','type' => 'string'),
			array('field' => 'UnformalizedAddressDirectory_Address','label' => 'Адресс','rules' => 'trim','type' => 'string')			
		),
		'loadUnformalizedAddressType'=>array(),
		'getCmpCallCardListForDoubleChoose'=>array(
			array('field' => 'doubleCmpCallCard_id', 'label' => 'Ид. дубл. карты вызова', 'rules' => 'required', 'type' => 'id' )
		),
		'saveCmpCallCardTimes'=>array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'CmpCallCard_Tper','label' => 'Время передачи','rules' => '',	'type' => 'string'),
			array('field' => 'CmpCallCard_Vyez','label' => 'Время выезда','rules' => '',	'type' => 'string'),
			array('field' => 'CmpCallCard_Przd','label' => 'Время прибытия на место вызова','rules' => '',	'type' => 'string'),
			array('field' => 'CmpCallCard_Tgsp','label' => 'Время начало транспортировки больного','rules' => '',	'type' => 'string'),
			array('field' => 'CmpCallCard_Tsta','label' => 'Время приезда на станцию','rules' => '',	'type' => 'string'),
			array('field' => 'CmpCallCard_prmDT','label' => 'Время приема','rules' => '',	'type' => 'string'),
			array('field' => 'CmpCallCard_Tvzv','label' => 'Время возвращения на станцию','rules' => '',	'type' => 'string'),
			array('field' => 'CmpCallCard_Tisp','label' => 'Время окончания вызова','rules' => '',	'type' => 'string'),
			array('field' => 'CmpCallCard_HospitalizedTime', 'label' => 'Время госпитализации', 'type' => 'string' ),
			array('field' => 'CmpCallCard_IsPoli', 'label' => 'Актив в поликлинику', 'type' => 'int' ),
			array('field' => 'Lpu_id', 'label' => 'МО', 'type' => 'int' )
		),
		'saveShortCmpCallCard'=>array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'CmpCallCard_rid','label' => 'Код первичного вызова(карты)','rules' => '','type' => 'int'),
			array('field' => 'CmpCallCard_Telf','label' => 'Телефон','rules' => '',	'type' => 'string'),
			array('field' => 'CmpCallCard_Tper','label' => 'Время передачи','rules' => '',	'type' => 'string'),
			array('field' => 'CmpCallCard_Vyez','label' => 'Время выезда','rules' => '',	'type' => 'string'),
			array('field' => 'CmpCallCard_Przd','label' => 'Время прибытия на место вызова','rules' => '',	'type' => 'string'),
			array('field' => 'CmpCallCard_Tgsp','label' => 'Время начало транспортировки больного','rules' => '',	'type' => 'string'),
			array('field' => 'CmpCallCard_Tsta','label' => 'Время приезда на станцию','rules' => '',	'type' => 'string'),
			array('field' => 'CmpCallCard_prmDT','label' => 'Время приема','rules' => '',	'type' => 'string'),
			array('field' => 'CmpCallCard_Tvzv','label' => 'Время возвращения на станцию','rules' => '',	'type' => 'string'),
			array('field' => 'CmpCallCard_Tisp','label' => 'Время окончания вызова','rules' => '',	'type' => 'string'),
			array('field' => 'CmpCallCard_HospitalizedTime', 'label' => 'Время госпитализации', 'type' => 'string' ),
			array('field' => 'CmpCallCard_IsPoli', 'label' => 'Актив в поликлинику', 'type' => 'swcheckbox' ),
			array('field' => 'Lpu_hid', 'label' => 'Мо госпитализации', 'type' => 'id' ),

			array('field' => 'CmpCallCard_Dom','label' => 'Дом (место вызова)','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Kvar','label' => 'Квартира (место вызова)','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Comm','label' => 'Дополнительная информация','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Podz','label' => 'Подъезд (место вызова)','rules' => '','type' => 'int'),
			array('field' => 'CmpCallCard_Etaj','label' => 'Этаж (место вызова)','rules' => '','type' => 'int'),
			array('field' => 'CmpCallCard_Kodp','label' => 'Код замка в подъезде (домофон) (место вызова)','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Comm','label' => 'Дополнительная информация','rules' => '','type' => 'string'),
			array('field' => 'CmpCallPlaceType_id','label' => 'Тип места','rules' => '','type' => 'id'),
			array('field' => 'KLRgn_id', 'label' => '','rules' => '','type' => 'id'),
			array('field' => 'KLSubRgn_id', 'label' => '','rules' => '','type' => 'id'),
			array('field' => 'KLCity_id', 'label' => '','rules' => '','type' => 'id'),
			array('field' => 'KLTown_id', 'label' => '','rules' => '','type' => 'id'),
			array('field' => 'KLStreet_id', 'label' => '','rules' => '','type' => 'id'),
			array('field' => 'CmpCallCard_UlicSecond', 'label' => '','rules' => '','type' => 'id'),
			array('field' => 'UnformalizedAddressDirectory_id', 'label' => 'Идентификатор элемента справочника', 'rules' => '','type' => 'id'),
			array('field' => 'CmpCallCard_IsExtra', 'label' => 'Вид вызова', 'rules' => '','type' => 'int'),
			array('field' => 'CmpReason_id', 'label' => 'Повод', 'rules' => '','type' => 'id'),
			array('field' => 'LpuBuilding_id', 'label' => 'Подстанция СМП', 'rules' => '','type' => 'id'),
			array('field' => 'MedService_id', 'label' => 'Подстанция НМП', 'rules' => '','type' => 'id'),
			array('field' => 'CallType', 'label' => 'Признак типа вызова', 'rules' => '','type' => 'string'),
			array('field' => 'CmpCallType_id', 'label' => 'Тип вызова', 'rules' => '','type' => 'id'),
			array('field' => 'CmpCallCardStatusType_id', 'label' => 'Тип вызова', 'rules' => '','type' => 'id'),
			array('field' => 'CmpCallCard_IsPassSSMP', 'label' => 'Вызов передан ', 'type' => 'swcheckbox' ),
			array('field' => 'Lpu_smpid', 'label' => 'Мо передачи (СМП)', 'type' => 'id' ),
			array('field' => 'Lpu_ppdid', 'label' => 'Мо передачи (НМП)', 'type' => 'id' ),

		),
		'getCountCateredCmpCallCards'=>array(array('field' => 'EmergencyTeam_id', 'label' => 'Ид бригады', 'rules' => 'required', 'type' => 'int' ),),
		'getUnformalizedAddressStreetKladrParams' => array(
			array('field' => 'regionName', 'label' => 'Территория', 'rules' => '', 'type' => 'string' ),
			array('field' => 'cityName', 'label' => 'Город/Район', 'rules' => '', 'type' => 'string' ),
			array('field' => 'streetName', 'label' => 'Улица', 'rules' => '', 'type' => 'string' ),
			array('field' => 'streetNumber', 'label' => 'Номер дома', 'rules' => '', 'type' => 'string' )
		),
		'saveUnformalizedAddress' =>array(
			array('field' => 'addresses', 'label' => 'Неформализованный адрес', 'rules' => '', 'type' => 'string',)
		),
		'deleteUnformalizedAddress' =>array(
			array('field' => 'UnformalizedAddressDirectory_id','label' => 'Идентификатор элемента справочника', 'rules' => 'required','type' => 'id')
		),
		'setEmergencyTeamWithoutSending'=>array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'EmergencyTeam_id', 'label' => 'Назначенная бригада', 'rules' => 'required', 'type' => 'int' ),
		),
		'printControlTicket'=>array(
			array('field' => 'teamId', 'label' => 'Ид бригады', 'rules' => 'required', 'type' => 'int' ),
			array('field' => 'callId', 'label' => 'Ид карты вызова', 'rules' => 'required', 'type' => 'int' )
		),

		'getCallsPriorityFromReason' => array(
			array('field' => 'CmpCardsArray', 'label' => 'карты вызовов', 'rules' => 'required', 'type' => 'string' ),
			array('field' => 'EmergencyTeamSpec_id', 'label' => 'профиль бригады', 'rules' => 'required', 'type' => 'int' )
		),

		'checkDuplicateCmpCallCard'=> array(
			array('field' => 'CmpCallCard_prmTime','label' => 'Время приема','rules' => '','type' => 'time_with_seconds'),
			array('default' => 2,'field' => 'CmpCallCard_IsOpen','label' => 'Открытая карта','rules' => '','type' => 'int'),
			// *******************************************
			array('field' => 'Person_id','label' => '','rules' => '','type' => 'id'),
			array('field' => 'PersonEvn_id','label' => '','rules' => '','type' => 'id'),
			array('field' => 'Person_Surname','label' => 'Фамилия','rules' => '','type' => 'string'),
			array('field' => 'Person_Firname','label' => 'Имя','rules' => '','type' => 'string'),
			array('field' => 'Person_Secname','label' => 'Отчество','rules' => '','type' => 'string'),
			array('field' => 'Person_FIO','label' => 'ФИО','rules' => '','type' => 'string'),
			array('field' => 'Person_Birthday','label' => 'Дата рождения','rules' => '','type' => 'date'),
			array('field' => 'CmpReason_id','label' => 'Повод','rules' => '','type' => 'id'),
			array('field' => 'CmpReason_Name','label' => 'Повод','rules' => '','type' => 'string'),
			array('field' => 'CmpCallType_Name','label' => 'Тип вызова','rules' => '','type' => 'string'),
			array('field' => 'KLRgn_id', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLSubRgn_id','label' => '','rules' => '','type' => 'id'),
			array('field' => 'KLCity_id','label' => '','rules' => '','type' => 'id'),
			array('field' => 'KLCity_Name','label' => '','rules' => '','type' => 'string'),
			array('field' => 'KLTown_id','label' => '','rules' => '','type' => 'id'),
			array('field' => 'KLTown_Name','label' => '','rules' => '','type' => 'string'),
			array('field' => 'KLStreet_id','label' => '','rules' => '','type' => 'id'),
			array('field' => 'CmpCallCard_UlicSecond','label' => '','rules' => '','type' => 'id'),
			array('field' => 'KLStreet_FullName','label' => 'Полное имя','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Dom','label' => 'Дом (место вызова)','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Kvar','label' => 'Квартира (место вызова)','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Comm','label' => 'Дополнительная информация','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Podz','label' => 'Подъезд (место вызова)','rules' => '','type' => 'int'),
			array('field' => 'CmpCallCard_Etaj','label' => 'Этаж (место вызова)','rules' => '','type' => 'int'),
			array('field' => 'CmpCallCard_Kodp','label' => 'Код замка в подъезде (домофон) (место вызова)','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Telf','label' => 'Телефон','rules' => '','type' => 'string'),
			array('field' => 'Person_Age','label' => 'Возраст','rules' => '','type' => 'int'),
			array('field' => 'Sex_id','label' => 'Пол','rules' => '','type' => 'id'),
			array('field' => 'CmpCallerType_id','label' => 'Идентификатор типа вызывающего','rules' => '','type' => 'id'),
			array('field' => 'CmpCallCard_Ktov','label' => 'Кто вызывает','rules' => '','type' => 'string'),
			array('field' => 'UnformalizedAddressDirectory_id','label' => '','rules' => '','type' => 'string'),
			array('field' => 'UnformalizedAddressType_id','label' => '','rules' => '','type' => 'string'),
			array('field' => 'UnformalizedAddressDirectory_Dom','label' => '','rules' => '','type' => 'string'),
			array('field' => 'UnformalizedAddressDirectory_Name','label' => '','rules' => '','type' => 'string'),
			// *************************************************************
			array('field' => 'CmpCallCard_id','label' => 'Идентификатор карты вызова СМП','rules' => '','type' => 'id'),
			array('field' => 'CallCard_id','label' => 'Идентификатор карты вызова СМП','rules' => '','type' => 'id'),
			array('field' => 'EmergencyTeam_id','label' => '','rules' => '','type' => 'id'),
			array('field' => 'CmpCallCard_Tper','label' => 'Время передачи','rules' => '','type' => 'time'),
			array('field' => 'CmpCallCard_Ngod','label' => 'Номер с начала года','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Numv','label' => 'Номер вызова','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_prmDate','label' => 'Дата приема','rules' => '','type' => 'date'),
			array('field' => 'Adress_Name','label' => 'Полный адрес (место вызова)','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Korp','label' => 'Корпус (место вызова)','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCardStatus_insDT','label' => '','rules' => '','type' => 'date'),
			array('field' => 'CmpCallPlaceType_id','label' => 'Идентификатор места вызова','rules' => '','type' => 'id'),
		),
		'loadSmpUnits' => array(
			array('field' => 'loadSelectSmp','label' => '','rules' => '','type' => 'boolean',)
		),
		'getLpuBuildingOptions' => array(),
		'copyCmpCallCardToLpu' => array(
			array('field' => 'CmpCallCard_id', 'label' => 'Идентификатор карты вызова', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'Lpu_did', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'LpuBuilding_did', 'label' => 'Идентификатор подстанции', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'LpuDid_Nick', 'label' => 'Наименование МО', 'rules' => '', 'type' => 'string')
		),
		'getOperDepartament' => array(),
		'getOperDepartamentOptions' => array(),
		'loadSmpUnitsNested' => array(
			array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подстанции', 'rules' => '', 'type' => 'int'),
			array(
				'field' => 'showOwnedLpuBuiding',
				'label' => 'Флаг отоборажения только своей подстанции',
				'rules' => '',
				'type' => 'boolean'
			),
			array(
				'field' => 'loadSelectSmp',
				'label' => '',
				'rules' => '',
				'type' => 'boolean',
			),
		),
		'loadSmpUnitsFromOptions' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'int'),
			array('field' => 'loadSelectSmp','label' => '','rules' => '','type' => 'boolean',)
		),
		'loadLpuWithNestedSmpUnits' => array(
			array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подстанции', 'rules' => '', 'type' => 'int'),
			array('field' => 'MedServiceType_id', 'label' => 'Идентификатор медсервиса', 'rules' => '', 'type' => 'int'),
			array('field' => 'Object', 'label' => 'Идентификатор объекта', 'rules' => '', 'type' => 'string'),
		),
		'loadLpuWithNestedLpuBuildings' => array(),
		'loadNestedLpuBuildings' => array(),
		'loadCmpCommonStateCombo' => array(),
		'getEmergencyTeamPriorityFromReason' => array(
			array('field' => 'CmpReason_id', 'label' => 'Ид причины', 'rules' => 'required', 'type' => 'int' ),
			array('field' => 'Person_Age', 'label' => 'Возраст', 'rules' => '', 'type' => 'int' ),
			//array('field' => 'Sex_id', 'label' => 'Пол', 'rules' => '', 'type' => 'int' ),
			array('field' => 'CmpCallPlaceType_id', 'label' => 'Ид причины', 'rules' => '', 'type' => 'int' )
		),
		'setCmpCallCardBoostTime' => array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид карты', 'rules' => 'required', 'type' => 'int' )
		),
		'setCmpCallCardSecondReason' => array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид карты', 'rules' => 'required', 'type' => 'int' ),
			array('field' => 'CmpCallCard_secondReason', 'label' => 'Ид повторного вызова', 'rules' => 'required', 'type' => 'int' )
		),
		'getCmpCallCardSmpInfo' => array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' )
		),
		'getCallUrgencyAndProfile' =>array(
			array('field' => 'CmpReason_id', 'label' => 'Ид причины', 'rules' => 'required', 'type' => 'int' ),
			array('field' => 'Person_Age', 'label' => 'Возраст', 'rules' => 'required', 'type' => 'int' ),
			array('field' => 'CmpCallPlaceType_id', 'label' => 'Ид причины', 'rules' => '', 'type' => 'int' ),
			array('field' => 'FlagArmWithoutPlaceType', 'label' => 'Флаг АРМ-а без типа места вызова', 'rules' => '', 'type' => 'boolean' )
		),
		'loadSMPDispatchStationWorkPlace' => array(
			array('field' => 'begDate','label' => 'Дата с','rules' => 'trim', 'type' => 'date', 'default' => '01.06.2013'),
			array('field' => 'hours','label' => 'За последние часы','rules' => 'trim','type' => 'string'),
			array('field' => 'endDate','label' => 'Дата по','rules' => 'trim','type' => 'date'),
			array('field' => 'CmpGroup_id','label' => 'Статус','rules' => '','type' => 'string'),
			array('field' => 'CmpGroupTable_id','label' => 'Статус (табличный вид)','rules' => '','type' => 'string'),
			array('field' => 'mode','label' => 'Вид','rules' => '','type' => 'string'),
			array('field' => 'callRecords','label' => 'json-массив загруженных вызовов','rules' => '','type' => 'string'),
		),
		'loadSMPHeadDoctorWorkPlace' => array(),
		'loadCallCardFarmacyRegisterHistory'=>array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' )
		),
		'loadCmpCallCardStatusHistory'=>array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' )
		),
		'loadCmpCallCardEventHistory'=>array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' )
		),
		'removeCmpCallCardFarmacyDrugHistory'=>array(
			array('field' => 'CmpFarmacyBalanceRemoveHistory_id', 'label' => 'Ид. истории списания', 'rules' => 'required', 'type' => 'id' )
		),
		'loadCmpCloseCardShort'=>array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' )
		),
		'printCmpCallCardCloseTicket'=>array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' )
		),
		'getLpuBuildingByAddress'=>array(
			array('field' => 'UnformalizedAddressDirectory_id','label' => '','rules' => '','type' => 'string'),
			array('field' => 'KLStreet_id','label' => '','rules' => '','type' => 'id'),
			array('field' => 'CmpCallCard_Dom','label' => 'Дом (место вызова)','rules' => 'required','type' => 'string'),
		),
		'getLpuBuildingBySessionData'=>array(),
		'getLpuBuildingByCurMedServiceId'=>array(
			array('field' => 'CurMedService_id', 'label' => 'CurMedService_id', 'rules' => 'required', 'type' => 'int'),
		),
		'setCmpCallCardTransType'=>array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'CmpCallCard_Kakp', 'label' => 'Тип передачи талона бригаде', 'rules' => 'required', 'type' => 'int' )
		),
		'acceptCmpCallCardByDispatchStation'=>array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' ),
		),
		'declineCmpCallCardByDispatchStation'=>array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' ),
		),
		'loadEmergencyTeamDrugPackByCmpCallCardId'=>array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' ),
		),
		'loadEmergencyTeamDrugPackMoveList'=>array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' ),
		),
		'cancelCmpCallCardFromEmergencyTeam'=>array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' ),
		),
		'checkLastDayClosedCallsByAddress' => array(
			array('field' => 'KLStreet_id', 'label' => 'Улица', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'CmpCallCard_Dom', 'label' => 'Дом', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'CmpCallCard_Korp', 'label' => 'Корпус', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'CmpCallCard_Kvar', 'label' => 'Квартира', 'rules' => 'trim', 'type' => 'string'),
		),
		'checkLastDayClosedCallsByPersonId' => array(
			array('field' => 'Person_id', 'label' => 'Пациент', 'rules' => 'trim', 'type' => 'id'),
		),
		'getRejectionReason' => array(
			array('field' => 'CmpRejectionReason_id', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'CmpRejectionReason_code','label' => '','rules' => '','type' => 'string'),
			array('field' => 'CmpRejectionReason_name','label' => '','rules' => '','type' => 'string'),
		),
		'loadBrigadesHistory' => array(
			array('field' => 'EmergencyTeam_id', 'label' => 'ид бригады', 'rules' => 'required', 'type' => 'int' ),
		),
		'getNmpMedService' => array(
			array('field' => 'KLStreet_id', 'label' => 'Улица', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'KLArea_id', 'label' => 'Нас.пункт', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'CmpCallCard_Dom', 'label' => 'Дом', 'rules' => 'required', 'type' => 'string' ),
			array('field' => 'CmpCallCard_Korp', 'label' => 'Корпус', 'rules' => '', 'type' => 'string' ),
			array('field' => 'CmpCallCard_prmDate', 'label' => 'Дата вызова', 'rules' => 'required', 'type' => 'date' ),
			array('field' => 'CmpCallCard_prmTime', 'label' => 'Время вызова', 'rules' => 'required', 'type' => 'string' ),
			array('field' => 'Person_Age', 'label' => 'Возраст', 'rules' => 'required', 'type' => 'int')
		),
		'setDefferedCmpCallCardParams' => array(
			array('field' => 'CmpCallCard_id', 'label' => 'Идентификатор вызова', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'CmpCallCard_storDate', 'label' => 'Дата', 'rules' => 'required', 'type' => 'string' ),
			array('field' => 'CmpCallCard_storTime', 'label' => 'Время', 'rules' => 'required', 'type' => 'string' ),
			array('field' => 'CmpCallCard_defCom', 'label' => 'Комментарий', 'rules' => '', 'type' => 'string' )
		),
		'checkLastDayClosedCallsByAddressAndPersonId' => array(
			array('field' => 'dStreetsCombo', 'label' => 'Улица', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'KLStreet_id', 'label' => 'Улица', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'CmpCallCard_UlicSecond', 'label' => 'Вторая улица', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'UnformalizedAddressDirectory_id', 'label' => 'Место', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'CmpCallCard_Dom', 'label' => 'Дом', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'CmpCallCard_Korp', 'label' => 'Корпус', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'CmpCallCard_Kvar', 'label' => 'Квартира', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Person_Surname', 'label' => 'Фамилия', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Person_Firname', 'label' => 'Имя', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Person_Secname', 'label' => 'Отчество', 'rules' => 'trim', 'type' => 'string')
		),
		'getSettingsChallengesRequiringTheDecisionOfSeniorDoctor' => array(
			array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подстанции', 'rules' => '', 'type' => 'int')
		),
		'loadDispatcherCallsList' => array(
			array('field' => 'filter','label' => 'Массив фильтров','rules' => '','type' => 'string'),
			array('field' => 'searchType','label' => 'Форма поиска','rules' => '', 'type' => 'int'),
			array('field' => 'begDate','label' => 'Дата с','rules' => 'trim', 'type' => 'date'),
			array('field' => 'endDate','label' => 'Дата по','rules' => 'trim','type' => 'date'),
			array('field' => 'begTime','label' => 'Время с','rules' => 'trim','type' => 'string'),
			array('field' => 'endTime','label' => 'Время по','rules' => 'trim','type' => 'string'),
			array('default' => 10000, 'field' => 'limit','label' => 'Количество','rules' => 'trim','type' => 'int'),
			array('default' => 0, 'field' => 'start','label' => 'Старт','rules' => 'trim','type' => 'int'),
			array('field' => 'MedPersonal_id','label' => 'Диспетчер вызовов','rules' => 'trim','type' => 'id'),
			array('field' => 'LpuBuilding_id','label' => 'Подразделение СМП','rules' => 'trim','type' => 'id'),
			array('field' => 'Person_id','label' => 'Идентификатор пациента','rules' => 'trim','type' => 'id'),
			array('field' => 'Person_FIO','label' => 'ФИО','rules' => 'trim','type' => 'string'),
			array('field' => 'Sex_id','label' => 'Пол','rules' => 'trim','type' => 'id'),
			array('field' => 'Person_Birthday_From','label' => 'Дата рождения с','rules' => 'trim','type' => 'date'),
			array('field' => 'Person_Birthday_To','label' => 'Дата рождения по','rules' => 'trim','type' => 'date'),
			array('field' => 'Person_Age_From','label' => 'Возраст с','rules' => 'trim','type' => 'int'),
			array('field' => 'Person_Age_To','label' => 'Возраст по','rules' => 'trim','type' => 'int'),
			array('field' => 'Person_Fam','label' => 'Фамилия','rules' => 'trim','type' => 'string'),
			array('field' => 'Person_Name','label' => 'Имя','rules' => 'trim','type' => 'string'),
			array('field' => 'Person_Middle','label' => 'Отчество','rules' => 'trim','type' => 'string'),

			array('field' => 'CmpCallType_id','label' => 'Тип вызова','rules' => 'trim','type' => 'id'),
			array('field' => 'CmpCallCard_IsExtra','label' => 'Вид вызова','rules' => 'trim','type' => 'int'),
			array('field' => 'CmpReason_id','label' => 'Повод','rules' => 'trim','type' => 'id'),
			array('field' => 'Diag_id_from','label' => 'Диагноз с','rules' => 'trim','type' => 'id'),
			array('field' => 'Diag_id_to','label' => 'Диагноз по','rules' => 'trim','type' => 'id'),
			array('field' => 'CmpResult_id','label' => 'Результат выезда','rules' => 'trim','type' => 'id'),
			array('field' => 'hasHdMark','label' => 'Имеет экспертную оценку','rules' => '','type' => 'id'),

			array('field' => 'KLTown_id','label' => '','rules' => 'trim','type' => 'int'),
			array('field' => 'KLSubRgn_id','label' => '','rules' => 'trim','type' => 'int'),
			array('field' => 'KLCity_id','label' => '','rules' => 'trim','type' => 'int'),
			array('field' => 'KLRgn_id','label' => '','rules' => 'trim','type' => 'int'),
			array('field' => 'KLAreaLevel_id','label' => '','rules' => 'trim','type' => 'int'),
			array('field' => 'UAD_id','label' => '','rules' => 'trim','type' => 'int'),
			array('field' => 'EmergencyTeam_Num','label' => '','rules' => 'trim','type' => 'int'),
			array('field' => 'KLStreet_id','label' => '','rules' => 'trim','type' => 'int'),
			array('field' => 'UnformalizedAddressDirectory_id','label' => '','rules' => 'trim','type' => 'int'),
			array('field' => 'CmpCallCard_Dom','label' => '','rules' => 'trim','type' => 'string'),
			array('field' => 'CmpCallCard_Korp','label' => '','rules' => 'trim','type' => 'string'),
			array('field' => 'CmpCallCard_Kvar','label' => '','rules' => 'trim','type' => 'string')
		),
		'loadCmpCallCard112List' => array(
			array('field' => 'begDate','label' => 'Дата с','rules' => 'trim', 'type' => 'date'),
			array('field' => 'endDate','label' => 'Дата по','rules' => 'trim','type' => 'date'),
			array('default' => 10000, 'field' => 'limit','label' => 'Количество','rules' => 'trim','type' => 'int'),
			array('default' => 0, 'field' => 'start','label' => 'Старт','rules' => 'trim','type' => 'int'),

			array('field' => 'Ier_AcceptOperatorStr','label' => 'Номер оператора 112','rules' => 'trim','type' => 'string'),
			array('field' => 'CmpCallCard112StatusType_id','label' => 'Статус карточки','rules' => 'trim','type' => 'id')
		),
		'findCmpCallCard112' => array(
			array('field' => 'Ier_AcceptOperatorStr','label' => 'Номер оператора 112','rules' => 'trim','type' => 'string')
		),
		'loadCmpCallCard112EditForm' => array(
			array('field' => 'CmpCallCard_id', 'label' => 'Идентификатор карты вызова', 'type' => 'int', 'rules' => 'required')
		),
		'loadRegionSmpUnits' => array(

		),
		'loadCmpCallCardStatusTypes' => array(

		),
		'loadAktivJournalList' => array(
			array('field' => 'begDate', 'label' => 'Дата с', 'type' => 'date', 'rules' => 'required'),
			array('field' => 'endDate', 'label' => 'Дата по', 'type' => 'date', 'rules' => 'required')
		),
		'loadSMPInteractiveMapWorkPlace' => array(
			array('field' => 'begDate','label' => 'Дата с','rules' => 'trim', 'type' => 'date', 'default' => '01.06.2013'),
			array('field' => 'endDate','label' => 'Дата по','rules' => 'trim','type' => 'date'),
			array('field' => 'CmpCallType_id','label' => 'Тип вызова','rules' => '','type' => 'int'),
			array('field' => 'CmpCallCardStatusType_id','label' => 'Статус вызова','rules' => '','type' => 'int'),
		),
		'sendCmpCallCardToLpuBuilding' => array(
			array('field' => 'CmpCallCard_id', 'label' => '', 'type' => 'int', 'rules' => 'required'),
			array('field' => 'LpuBuilding_id', 'label' => '', 'type' => 'int', 'rules' => 'required')
		),
		'loadCallsUnderControlList' => array(
			array('field' => 'filter','label' => 'Массив фильтров','rules' => '','type' => 'string'),
			array('field' => 'searchType','label' => 'Форма поиска','rules' => '', 'type' => 'int'),
			array('field' => 'begDate','label' => 'Дата с','rules' => 'trim', 'type' => 'date'),
			array('field' => 'endDate','label' => 'Дата по','rules' => 'trim','type' => 'date'),
			array('field' => 'begTime','label' => 'Время с','rules' => 'trim','type' => 'string'),
			array('field' => 'endTime','label' => 'Время по','rules' => 'trim','type' => 'string'),
			array('default' => 10000, 'field' => 'limit','label' => 'Количество','rules' => 'trim','type' => 'int'),
			array('default' => 0, 'field' => 'start','label' => 'Старт','rules' => 'trim','type' => 'int'),
			array('field' => 'MedPersonal_id','label' => 'Диспетчер вызовов','rules' => 'trim','type' => 'id'),
			array('field' => 'LpuBuilding_id','label' => 'Подразделение СМП','rules' => 'trim','type' => 'id'),
			array('field' => 'Person_id','label' => 'Идентификатор пациента','rules' => 'trim','type' => 'id'),
			array('field' => 'Person_FIO','label' => 'ФИО','rules' => 'trim','type' => 'string'),
			array('field' => 'Sex_id','label' => 'Пол','rules' => 'trim','type' => 'id'),
			array('field' => 'Person_Birthday_From','label' => 'Дата рождения с','rules' => 'trim','type' => 'date'),
			array('field' => 'Person_Birthday_To','label' => 'Дата рождения по','rules' => 'trim','type' => 'date'),
			array('field' => 'Person_Age_From','label' => 'Возраст с','rules' => 'trim','type' => 'int'),
			array('field' => 'Person_Age_To','label' => 'Возраст по','rules' => 'trim','type' => 'int'),
			array('field' => 'CmpCallType_id','label' => 'Тип вызова','rules' => 'trim','type' => 'id'),
			array('field' => 'CmpCallCard_IsExtra','label' => 'Вид вызова','rules' => 'trim','type' => 'int'),
			array('field' => 'CmpReason_id','label' => 'Повод','rules' => 'trim','type' => 'id'),
			array('field' => 'Diag_id_from','label' => 'Диагноз с','rules' => 'trim','type' => 'id'),
			array('field' => 'Diag_id_to','label' => 'Диагноз по','rules' => 'trim','type' => 'id'),
			array('field' => 'CmpResult_id','label' => 'Результат выезда','rules' => 'trim','type' => 'id'),

			array('field' => 'KLTown_id','label' => '','rules' => 'trim','type' => 'int'),
			array('field' => 'KLSubRgn_id','label' => '','rules' => 'trim','type' => 'int'),
			array('field' => 'KLCity_id','label' => '','rules' => 'trim','type' => 'int'),
			array('field' => 'KLRgn_id','label' => '','rules' => 'trim','type' => 'int'),
			array('field' => 'KLAreaLevel_id','label' => '','rules' => 'trim','type' => 'int'),
			array('field' => 'UAD_id','label' => '','rules' => 'trim','type' => 'int'),
			array('field' => 'EmergencyTeam_Num','label' => '','rules' => 'trim','type' => 'int'),
			array('field' => 'KLStreet_id','label' => '','rules' => 'trim','type' => 'int'),
			array('field' => 'UnformalizedAddressDirectory_id','label' => '','rules' => 'trim','type' => 'int'),
			array('field' => 'CmpCallCard_Dom','label' => '','rules' => 'trim','type' => 'string'),
			array('field' => 'CmpCallCard_Korp','label' => '','rules' => 'trim','type' => 'string'),
			array('field' => 'CmpCallCard_Kvar','label' => '','rules' => 'trim','type' => 'string')
		),
		'saveActiveCallRules' => array(
			array('field' => 'ActiveCallRule_id', 'label' => 'Идентификатор правила', 'type' => 'id', 'rules' => 'trim'),
			array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделения', 'type' => 'id', 'rules' => 'required'),
			array('field' => 'ActiveCallRule_From', 'label' => 'Возраст с', 'type' => 'int', 'rules' => 'trim'),
			array('field' => 'ActiveCallRule_To', 'label' => 'Возраст по', 'type' => 'int', 'rules' => 'trim'),
			array('field' => 'ActiveCallRule_UrgencyFrom', 'label' => 'Срочночть с', 'type' => 'int', 'rules' => 'trim'),
			array('field' => 'ActiveCallRule_UrgencyTo', 'label' => 'Срочность по', 'type' => 'int', 'rules' => 'trim'),
			array('field' => 'ActiveCallRule_WaitTime', 'label' => 'Время ожидания, мин', 'type' => 'int', 'rules' => 'trim')
		),
		'getActiveCallRuleEdit' => array(
			array('field' => 'ActiveCallRule_id', 'label' => 'Идентификатор правила', 'type' => 'id', 'rules' => 'required'),
			array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделения', 'type' => 'id', 'rules' => 'required'),
		),
		'loadSelectNmpReasonWindow' => array(
			array('field' => 'CmpCallCard_id', 'label' => 'Первичный вызов', 'type' => 'int', 'rules' => 'required')
		),
		'setResultCmpCallCards' => array(
			array('field' => 'calls', 'label' => 'json строка вызовов', 'type' => 'string', 'rules' => 'required')
		),
		'cancelEmergencyTeamFromCalls'=>array(
			array('field' => 'calls', 'label' => 'json строка вызовов', 'type' => 'string', 'rules' => 'required')
		),
		'updateSmpUnitHistoryData'=>array(
			array('field' => 'lpuBuildings', 'label' => 'json строка подстанций', 'type' => 'string', 'rules' => '', 'default' => '[]'),
			array('field' => 'closeAll', 'label' => 'Флаг закрытия всей истории управления подстанциями текущего диспетчера', 'type' => 'boolean', 'default' => false)
		),
		'getDispControlLpuBuilding'=>array(
			array('field' => 'lpuBuildings', 'label' => 'json строка подстанций', 'type' => 'string', 'rules' => '')
		),
		'checkLpuBuildingWithoutSmpUnitHistory'=>array()
	);

	/**
	 * @desc Загрузка списка карт СМП для АРМ диспетчера направлений
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('CmpCallCard_model4E', 'dbmodel');
	}


    /**
     * @desc Метод тестирования прокси портала
     */
	function portalProxyTest() {
        $this->load->helper('NodeJS');
        $postSendResult = NodePostRequest(['123' => '567'], ['host' => NODEJS_PORTAL_PROXY_HOSTNAME, 'port' => NODEJS_PORTAL_PROXY_HTTPPORT]);
        var_dump($postSendResult);
    }

	/**
	 * @desc Загрузка списка карт СМП для АРМ диспетчера направлений
	 */
	function loadSMPDispatchDirectWorkPlace() {
		$data = $this->ProcessInputData('loadSMPDispatchDirectWorkPlace', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadSMPDispatchDirectWorkPlace($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * @desc Загрузка списка карт СМП для АРМ диспетчера подстанции
	 */
	public function loadSMPDispatchStationWorkPlace() {
		$data = $this->ProcessInputData('loadSMPDispatchStationWorkPlace', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadSMPDispatchStationWorkPlace($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * @desc Загрузка списка карт СМП для АРМ диспетчера подстанции
	 */
	public function loadSMPHeadDoctorWorkPlace() {
		$data = $this->ProcessInputData('loadSMPHeadDoctorWorkPlace', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->loadSMPHeadDoctorWorkPlace($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	*  Сохранение карты вызова СМП
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма редактирования карты вызова СМП
	*/
	public function saveCmpCallCard() {
		$data = $this->ProcessInputData( 'saveCmpCallCard', true );
		
		//Проверка для арма старшего врача
		if ($data['ARMType'] == 'smpheaddoctor' && !$this->checkSaveCmpCallCard($data))
			return;
		
		$dolog = (defined('DOLOGSAVECARD') && DOLOGSAVECARD === true) ? true : false;
		if($dolog)$this->load->library('textlog', array('file'=>'saveCmpCallCardNumbers_'.date('Y-m-d').'.log'));

        if ( $data === false ) {
			 return false;
		}

		$CurArmType = $data['session']['CurArmType'];
		//поводы для ППД 12Я; 12Э; 12У; 12Р; 12К; 12Г; 13Л; 11Я; 11Л; 04Д; 04Г; 13М; 09Я; 15
		//$reasons = array( 541, 542, 595, 606, 609, 613, 616, 618, 619, 620, 621, 629, 630, 644, 632, 689 ); // Возможные поводы
		$callTypeCodesWithoutLpuBuilding = array(6, 15, 16);
		// CmpCallType_Code = 6 Консультативный, 15 Справка, 16 Абонент отключился
		$str = '';

		if (empty($data[ 'LpuBuilding_id' ]) && empty($data['MedService_id']) && empty($data[ 'Lpu_ppdid' ]) && empty($data[ 'Lpu_smpid' ])
			&& !(in_array($data['CmpCallType_Code'],$callTypeCodesWithoutLpuBuilding))
		) {
			$this->ReturnData( array( 'success' => false, 'Error_Msg' => 'Должно быть заполнено либо "Подразделение СМП", либо "Служба НМП", либо "Поликлиника"' ) );
			return true;
		}
		//array_unshift($callTypeCodesWithoutLpuBuilding, 14, 17);
		// CmpCallType_Code = 14 - дубль, 17 - отмена вызова
		/*
		if (!empty($data[ 'CmpReason_id' ]) &&
			!((in_array($data['CmpCallType_Code'],$callTypeCodesWithoutLpuBuilding)) && $_SESSION['region']['nick'] == 'ufa')) {
			$this->ReturnData( array( 'success' => false, 'Error_Msg' => 'Невозможно сохранить. Поле Повод обязательно к заполнению.' ) );
			return true;
		}
		*/
		if ( ($data[ 'CmpCallCard_rid' ] == null || $data[ 'CmpCallCard_rid' ] == '') &&
			(($data['CmpCallType_Code'] == 17 || $data['CmpCallType_Code'] == 14) && $_SESSION['region']['nick'] == 'ufa')) {
			$this->ReturnData( array( 'success' => false, 'Error_Msg' => 'Невозможно сохранить. Выберите первичный вызов' ) );
			return true;
		}

		if($data['CmpCallCard_IsExtra'] != 2 && empty($data[ 'LpuBuilding_id' ]) && !(in_array($data['CmpCallType_Code'],$callTypeCodesWithoutLpuBuilding))) {
			$LpuBuildingByCurMedServiceId = '';
			$LpuBuildingByCurMedServiceId = $this->getLpuBuildingBySessionDataInto();
			$data[ 'LpuBuilding_id' ] = $LpuBuildingByCurMedServiceId[0]['LpuBuilding_id'];
			if($data[ 'LpuBuilding_id' ] == '')
				return $this->createError(null, 'Не определена подстанция');

		}

		if(getRegionNick() == 'krym' && empty($data['CmpCallerType_id']) && empty($data['CmpCallCard_Ktov'])){
			$this->ReturnData( array( 'success' => false, 'Error_Msg' => 'Поле "Кто вызывает" обязательно для заполнения' ) );
			return true;
		}

		$forPPDflag = false;

		 // Проверяем откуда пришел вызов.
		if ( $data[ 'ARMType' ] == 'slneotl' && !empty( $data[ 'session' ][ 'lpu_id' ] ) ) {
			//$data['Lpu_id'] = $data['session']['lpu_id'];
			$data[ 'CmpCallCard_IsReceivedInPPD' ] = 2;
			$forPPDflag = true;
		}

		// Не нужно самоопределяться в ЛПУ

		$data[ 'CmpLpu_id' ] = $data[ 'LpuTransmit_id' ];

		//if( in_array($CurArmType, array('dispcallnmp', 'dispdirnmp', 'dispnmp')) ){
		//if($data[ 'Lpu_smpid' ] || $data['Lpu_ppdid']){
			//$data[ 'Lpu_id' ] = !empty($data[ 'Lpu_smpid' ]) ? $data[ 'Lpu_smpid' ] : $data[ 'Lpu_ppdid' ];
		//}
		//else{
		$data[ 'Lpu_id' ] = $data[ 'session' ][ 'lpu_id' ];
		//}
		//}
		//else{
			//$data[ 'Lpu_id' ] = $data[ 'session' ][ 'lpu_id' ];
		//}
		if($dolog)$this->textlog->add('saveCmpCallCard default');
		$response = $this->dbmodel->saveCmpCallCard( $data );

		$IsSMPServer = $this->config->item('IsSMPServer');
		$IsLocalSMP = $this->config->item('IsLocalSMP');

		if(/*getRegionNick() == 'ufa' &&*/ !empty( $data['isSavedCVI']) ) {
			$this->load->model('ApplicationCVI_model', 'ApplicationCVI_model');
			$this->load->model('RepositoryObserv_model', 'RepositoryObserv_model');
			$params = [
				'Person_id' => $response['Person_id'],
				'CmpCallCard_id' => !empty($response[0]["CmpCallCard_id"]) ? $response[0]["CmpCallCard_id"] : null,
				'PlaceArrival_id' => $data['PlaceArrival_id'],
				'KLCountry_id' => $data['KLCountry_id'],
				'OMSSprTerr_id' => $data['OMSSprTerr_id'],
				'ApplicationCVI_arrivalDate' => $data['ApplicationCVI_arrivalDate'],
				'ApplicationCVI_flightNumber' => $data['ApplicationCVI_flightNumber'],
				'ApplicationCVI_isContact' => $data['ApplicationCVI_isContact'],
				'ApplicationCVI_isHighTemperature' => $data['ApplicationCVI_isHighTemperature'],
				'Cough_id' => $data['Cough_id'],
				'Dyspnea_id' => $data['Dyspnea_id'],
				'ApplicationCVI_Other' => $data['ApplicationCVI_Other']
			];
			$res = $this->ApplicationCVI_model->doSave($params, false);
			if(!empty($res['CVIQuestion_id'])) {
                //PROMEDWEB-4491 сохранение в RepositoryObserv
			    $CVIParams = [
			    	'PersonQuarantine_id' => NULL,
					'RepositoryObesrv_contactDate' => NULL,
					'KLRgn_id' => $data['OMSSprTerr_id'],
					'TransportMeans_id' => NULL,
					'RepositoryObserv_IsAntivirus' => 1,
					'RepositoryObserv_IsEKMO' => 1,
					'RepositoryObserv_TransportDesc' => NULL,
					'RepositoryObserv_TransportPlace' => NULL,
					'RepositoryObserv_TransportRoute' => NULL,
					'RepositoryObserv_IsResuscit' => 1,
					'RepositoryObserv_GLU' => NULL,
					'RepositoryObserv_Cho' => NULL,
					'CovidType_id' => NULL,
					'DiagConfirmType_id' => NULL,
					'StateDynamic_id' => NULL,
                    'Person_id' => $response['Person_id'],
                    'CmpCallCard_id' => !empty($response[0]["CmpCallCard_id"]) ? $response[0]["CmpCallCard_id"] : null,
                    'PlaceArrival_id' => $data['PlaceArrival_id'],
                    'KLCountry_id' => $data['KLCountry_id'],
                    'Region_id' => $data['OMSSprTerr_id'],
                    'RepositoryObserv_arrivalDate' => $data['ApplicationCVI_arrivalDate'],
                    'RepositoryObserv_FlightNumber' => $data['ApplicationCVI_flightNumber'],
                    'RepositoryObserv_IsCVIContact' => $data['ApplicationCVI_isContact'],
                    'RepositoryObserv_IsHighTemperature' => $data['ApplicationCVI_isHighTemperature'],
                    'Cough_id' => $data['Cough_id'],
                    'Dyspnea_id' => $data['Dyspnea_id'],
                    'CVIQuestion_id' => $res['CVIQuestion_id'],
                    'Lpu_id' => $data['Lpu_id'],
                    'pmUser_id' => $data['pmUser_id'],
                    'MedPersonal_id' => $data['session']['medpersonal_id'],
                    'MedStaffFact_id' => $data['session']['MedStaffFact'][0],
                    'ApplicationCVI_Other' => $data['ApplicationCVI_Other'],
                    'Evn_id' => NULL,
                    'RepositoryObserv_BreathPeep' => NULL,
                    'RepositoryObserv_PH' => NULL,
                    'RepositoryObserv_IsSputum' => NULL,
                    'MedPersonal_Email' => NULL,
                    'HomeVisit_id' => NULL,
                    'LpuWardType_id' => NULL,
                    'MedPersonal_Phone' => NULL,
                    'DiagSetPhase_id' => NULL,
                    'GenConditFetus_id' => NULL,
                    'IVLRegim_id' => NULL,
                    'RepositoryObserv_BloodOxygen' => NULL,
                    'RepositoryObserv_BreathFrequency' => NULL,
                    'EvnRepositoryObserv_BreathPeep_id' => NULL,
                    'RepositoryObserv_BreathPressure' => NULL,
                    'RepositoryObserv_BreathRate' => NULL,
                    'RepositoryObserv_BreathVolume' => NULL,
                    'RepositoryObserv_CVIQuestionNotReason' => NULL,
                    'RepositoryObserv_Diastolic' => NULL,
                    'RepositoryObserv_FiO2' => NULL,
                    'RepositoryObserv_Height' => NULL,
                    'RepositoryObserv_Hemoglobin' => NULL,
                    'RepositoryObserv_IsCVIQuestion' => NULL,
                    'RepositoryObserv_IsHighTemperature' => NULL,
                    'RepositoryObserv_IsMyoplegia' => NULL,
                    'RepositoryObserv_IsPronPosition' => NULL,
                    'RepositoryObserv_IsRunnyNose' => NULL,
                    'RepositoryObserv_IsSedation' => NULL,
                    'RepositoryObserv_IsSoreThroat' => NULL,
                    'RepositoryObserv_Leukocytes' => NULL,
                    'RepositoryObserv_Lymphocytes' => NULL,
                    'RepositoryObserv_NumberTMK' => NULL,
                    'RepositoryObserv_Other' => NULL,
                    'RepositoryObserv_PaO2' => NULL,
                    'RepositoryObserv_PaO2FiO2' => NULL,
                    'RepositoryObserv_IVL' => NULL,
                    'RepositoryObserv_Person_BloodOxygen' => NULL,
                    'RepositoryObserv_Oxygen' => NULL,
                    'RepositoryObserv_Platelets' => NULL,
                    'RepositoryObserv_PregnancyPeriod' => NULL,
                    'RepositoryObserv_Pulse' => NULL,
                    'RepositoryObserv_RegimVenting' => NULL,
                    'RepositoryObserv_SOE' => NULL,
                    'RepositoryObserv_SpO2' => NULL,
                    'RepositoryObserv_SRB' => NULL,
                    'RepositoryObserv_Systolic' => NULL,
                    'RepositoryObserv_TemperatureFrom' => NULL,
                    'RepositoryObserv_TemperatureTo' => NULL,
                    'RepositoryObserv_Weight' => NULL,
                    'RepositoryObserv_setDate' => NULL,
                    'RepositoryObserv_id' => NULL,
                    'RepositoryObserv_setTime' => NULL
                ];
                $resRepObs = $this->RepositoryObserv_model->save($CVIParams);
            }


			if( !$this->isSuccessful($res) ) {
				throw new Exception($res['Error_Msg']);
			}
		}

		if (($IsLocalSMP === true || $IsSMPServer === true) && !empty($response[0]["CmpCallCard_id"])) {
			if (
				defined('STOMPMQ_MESSAGE_ENABLE')
				&& defined('STOMPMQ_MESSAGE_ENABLE')
				&& STOMPMQ_MESSAGE_ENABLE === TRUE
				&& $_SESSION['region']['nick'] != 'ufa'
			){
				// отправляем карту СМП в основную БД через очередь ActiveMQ
				$this->load->model('Replicator_model');
				$this->Replicator_model->sendRecordToActiveMQ(array(
					'table' => 'CmpCallCard',
					'type' => (empty($data['CmpCallCard_id'])) ? 'insert' : 'update',
					'keyParam' => 'CmpCallCard_id',
					'keyValue' => $response[0]["CmpCallCard_id"]
				));
			}else{

				$smpCard112 =  $this->dbmodel->loadCmpCallCard112EditForm(array('CmpCallCard_id' => $response[0]["CmpCallCard_id"]));
				//в ручном режиме
				unset($this->db);
				$this->load->database('main');
				//сейчас мы на дефолтной базе

				$cccConfig = array(
					'CmpCallCard_GUID' => $response[0]["CmpCallCard_GUID"],
					'CmpCallCard_id' => $response[0]["CmpCallCard_id"],
					'CmpCallCard_Numv' => $response["CmpCallCard_Numv"],
					'CmpCallCard_Ngod' => $response["CmpCallCard_Ngod"],
					'CmpCallCard_prmDT' => $response['CmpCallCard_prmDT']
				);
				if(!empty($response["Person_id"])) {$data['Person_id'] = $response["Person_id"];}
				if(!empty($response["CmpCallCardStatus_id"])) {$data['CmpCallCardStatus_id'] = $response["CmpCallCardStatus_id"];}
				if(!empty($response["CmpCallCardEvent_id"])) {$data['CmpCallCardEvent_id'] = $response["CmpCallCardEvent_id"];}
				if (!empty($response['CmpCallCardStatusType_id'])) {$data['CmpCallCardStatusType_id'] = $response['CmpCallCardStatusType_id'];}

				if($dolog)$this->textlog->add('saveCmpCallCard main id=' . $cccConfig['CmpCallCard_id'] . '/' . $cccConfig['CmpCallCard_Numv'] . '/' . $cccConfig['CmpCallCard_Ngod']  );
				$res = $this->dbmodel->saveCmpCallCard( $data, $cccConfig );

				if(is_array($smpCard112) && count($smpCard112) > 0){
					//Проверим наличие карточки 112 на основной БД
					$mainCard112 =  $this->dbmodel->loadCmpCallCard112EditForm(array('CmpCallCard_id' => $response[0]["CmpCallCard_id"]));

					if(!is_array($mainCard112) || count($mainCard112) == 0){
						//Сохраним карточку если ее не на основной бд
						$smpCard112[0]['pmUser_id'] = $data['pmUser_id'];
						$this->dbmodel->saveCmpCallCard112($smpCard112[0]);
					}
				}

				unset($this->db);
				$this->load->database();
			}
		}

		//$is_pg = $this->db->dbdriver == 'postgre' ? true : false;

		//if ( !$is_pg ) {
		if ( isset( $data[ 'Person_id' ] ) && isset( $data[ 'Person_isOftenCaller' ] ) && $data[ 'Person_isOftenCaller' ] == 1 ) {
			$this->load->model( 'OftenCallers_model', 'oc_model' );
			$this->oc_model->checkOftenCallers( $data );
		}
		//}
		/*
		// Если вызов сохранен удачно, передадим его на подстанции
		if ( $is_pg && $this->GetOutData('success') === true ) {
			$proxy =& load_class( 'swProxyQueries', 'libraries', null );
			$proxy->init( $this->config->config['proxy_queries']['settings'] );
			$proxy->setCookies( $proxy->restoreCookies() );
			$result = $proxy->forward( '/?c=CmpCallCard4E&m=saveCmpCallCard', false );
			$proxy->rememberCookies( $result['cookies'] );

			$headers = explode( "\n", $result['headers'] );
			foreach( $headers as $header ) if ( strpos( $header, ":" ) !== false ) {
				list( $h, $v ) = explode( ':', $header );
				if ( $h == 'Content-Encoding' ) {
					if ( trim( $v ) == 'gzip' ) {
						$result['body'] = $proxy->gunzip( $result['body'] );
					}
				}
			}
		}
		*/

		if(!empty($data['copyParamsToOthersCards']) && !empty($response[0]) && !empty($response[0]["CmpCallCard_id"]) ){
			$paramsToCopy = json_decode($data['copyParamsToOthersCards'], true);

			$paramsToCopy["donorCard"] = $response[0]["CmpCallCard_id"];
			$paramsToCopy["pmUser_id"] = $data["pmUser_id"];
			$paramsToCopy["Lpu_id"] = $data["Lpu_id"];
			$this->dbmodel->copyParamsCmpCallCard( $paramsToCopy );
		};

		$this->load->model('CmpCallCard_model', 'cccmodel');

		$addHomeVisitResult = null;

		//@todo определить настройки флаг «Возможность вызова врача на дом»

		$CurrentLpuHomevizitAllowed = $this->cccmodel->loadLpuHomeVisit(array('Lpu_id' => $data[ 'Lpu_ppdid' ]));

		if(is_array($CurrentLpuHomevizitAllowed) && !empty($CurrentLpuHomevizitAllowed[0]['Lpu_id'])){
			$CurrentLpuHomevizitAllowed = true;
		}else{
			$CurrentLpuHomevizitAllowed = false;
		}
        if(!empty($data['Lpu_hid'])) {
            $this->dbmodel->setLpuHospitalized(array(
                "CmpCallCard_id" => $data['CmpCallCard_id'],
                "Lpu_hid" => $data['Lpu_hid'],
                "pmUser_id" => $data["pmUser_id"],
                "Diag_id" => !empty($data['Diag_id']) ? $data['Diag_id'] : null
            ));
        }
		if(
			empty($data['CmpCallCard_id'])
			&& $data['CmpCallCard_IsExtra'] == 3
			&& !empty($response[0]["CmpCallCard_id"])
			&& empty($data['CmpCallCard_storDate']) //отложенные сохраняем потом)
			&& $CurrentLpuHomevizitAllowed
		)
		{
			$data['CmpCallCard_id'] = $response[0]["CmpCallCard_id"];
			//if (!empty($data['MedService_id'])) {
				//$lpuBuilding = $this->dbmodel->getLpuBuildingByMedServiceId(array('MedService_id' => $data['MedService_id']));

				//if (!empty($lpuBuilding[0]['LpuBuilding_id'])) {
					//$this->load->model('LpuStructure_model', 'LpuStructure');
					//$LpuBuildingData = $this->LpuStructure->getSmpUnitData(array("LpuBuilding_id" => $lpuBuilding[0]['LpuBuilding_id']));
				//}
				//if (!empty($LpuBuildingData[0]['SmpUnitParam_IsAutoHome']) && ($LpuBuildingData[0]['SmpUnitParam_IsAutoHome'] == "true")
				//) {
			//		$addHomeVisitResult = $this->cccmodel->addHomeVisitFromSMP($data);
				//}
			//} else {

				$addHomeVisitResult = $this->cccmodel->addHomeVisitFromSMP($data);
			//}


			//если добавили вызов на дом и все хорошо по времени
			if(is_array($addHomeVisitResult) && $addHomeVisitResult[0]['success']){

				//Передадим на основную бд
				if(!empty($addHomeVisitResult[0]['HomeVisit_id']) && $IsSMPServer === true &&
					(defined('STOMPMQ_MESSAGE_ENABLE') && STOMPMQ_MESSAGE_ENABLE === TRUE)){

					$this->load->model('Replicator_model');
					$this->Replicator_model->sendRecordToActiveMQ(array(
						'table' => 'HomeVisit',
						'type' => 'insert',
						'keyParam' => 'HomeVisit_id',
						'keyValue' => $addHomeVisitResult[0]['HomeVisit_id']
					));

				}

				if (!in_array($CurArmType, array('dispnmp','dispcallnmp', 'dispdirnmp', 'nmpgranddoc'))) {
					//Вызов в статус "Обслужено"
					$this->dbmodel->setStatusCmpCallCard(array(
						"CmpCallCard_id" => $data['CmpCallCard_id'],
						"CmpCallCardStatusType_id" => 4,
						"CmpCallCardStatus_Comment" => '',
						"pmUser_id" => $data["pmUser_id"]
					));
				}

				$this->cccmodel->setResult(array(
					"CmpPPDResult_id" => 0,
					"CmpPPDResult_Code" => 23, //Вызов передан уч. врачу
					"CmpCallCard_id" => $data['CmpCallCard_id'],
					"pmUser_id" => $data["pmUser_id"]
				));
			}
			else{
				//if( in_array($CurArmType, array('dispcallnmp')
				//2.13.	Функция определения службы НМП обслуживания вызова
				$data['KLArea_id'] = $data['KLCity_id'];
				$serviceNmp = $this->dbmodel->getNmpMedService($data);

				if(!empty($serviceNmp[0]) && !empty($serviceNmp[0]["MedService_id"]) && !empty($serviceNmp[0]["Lpu_id"])){
					//переписываем MedService_id и Lpu_id в карте
					$this->dbmodel->changeCmpCallCardCommonParams(
						array(
							'MedService_id' => $serviceNmp[0]["MedService_id"],
							'Lpu_ppdid' => $serviceNmp[0]["Lpu_id"],
							'CmpCallCard_id' => $data['CmpCallCard_id']
						)
					);

					$response[0]['saveWarningMsg'] = 'Вызовов врача на дом не доступен, поэтому был передан в МО НМП: "'
						. $serviceNmp[0]["Lpu_Nick"] .'", служба: "' . $serviceNmp[0]["MedService_Name"] . '"';
				}
				else{
					//Вызов в статус "Решение старшего врача"
					$this->dbmodel->setStatusCmpCallCard(array(
						"CmpCallCard_id" => $response[0]['CmpCallCard_id'],
						"CmpCallCardStatusType_id" => 18,
						"CmpCallCardStatus_Comment" => '',
						"pmUser_id" => $data["pmUser_id"]
					));

					$operDpt = $this->dbmodel->getOperDepartament($data);

					if ( isset( $operDpt["Lpu_id"] ) ){
						$this->dbmodel->changeCmpCallCardCommonParams(
							array(
								'MedService_id' => null,
								'Lpu_ppdid' => $operDpt["Lpu_id"],
								'CmpCallCard_id' => $data['CmpCallCard_id']
							)
						);
					}


					$response[0]['saveWarningMsg'] = 'Вызов врача на дом не доступен, и был передан на решение старшего врача';
				}
			}
		}


		return $this->ProcessModelSave($response, true)->ReturnData();
	 }

	 /**
	 *	Установка статуса карты вызова
	 */
	function setStatusCmpCallCard() {
		$data = $this->ProcessInputData('setStatusCmpCallCard', true);
		if ( $data === false ) { return false; }

		if(empty($data['CmpCallCard_id'])){
			return false;
		}

		$IsSMPServer = $this->config->item('IsSMPServer');
		$IsLocalSMP = $this->config->item('IsLocalSMP');

		if( $data['CmpCallCardStatusType_id'] != null || $data['CmpCallCardStatusType_Code'] != null ) { // если нужно проставить статус
			$response = $this->dbmodel->setStatusCmpCallCard($data);

			if (($IsLocalSMP === true || $IsSMPServer === true) && !empty($response[0]["CmpCallCardStatus_id"])) {

				//в ручном режиме
				unset($this->db);
				$this->load->database('main');
				//сейчас мы на дефолтной базе

				if (!empty($response[0]["CmpCallCardStatus_id"])) {
					$data['CmpCallCardStatus_id'] = $response[0]["CmpCallCardStatus_id"];
				}
				if (!empty($response[0]["CmpCallCardEvent_id"])) {
					$data['CmpCallCardEvent_id'] = $response[0]["CmpCallCardEvent_id"];
				}

				$response = $this->dbmodel->setStatusCmpCallCard($data);

				unset($this->db);
				$this->load->database();

			}

		} else if( !empty($data['CmpCallCard_IsOpen']) ) { // если нужно открыть/закрыть
			$response = $this->dbmodel->setIsOpenCmpCallCard($data);
		} else {
			return false;
		}

		//если вызов дубль - то вьюха(v_CmpCallCard) его не возвращает и определять в группу не надо
		//так что просто воротаемся назад
		if(
			$data['CmpCallCardStatusType_id'] == 16 || $data['CmpCallCardStatusType_Code'] == 9 ||
			 $data['CmpCallCardStatusType_id'] == 5 || $data['CmpCallCardStatusType_Code'] == 5
			){
			$this->ReturnData(array('success'=> true, 'CmpCallCard'=> $data['CmpCallCard_id']));
			return;
		}

		if ( !empty($response[0]['Error_Msg']) ) {
			$this->ReturnError($response[0]['Error_Msg']);
			return false;
		}

		// Узнаем группу
		$groupData = $this->dbmodel->defineAccessoryGroupCmpCallCard($data);

		$this->ReturnData($groupData);

		return true;
	}

	/**
	 * default desc
	 */
	function copyCmpCallCard() {
		$data = $this->ProcessInputData('copyCmpCallCard', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->copyCmpCallCard($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();

		return true;
	}

	
	/**
	 *	Установка статуса карты вызова 112
	 */
	function setStatusCmpCallCard112() {
		$data = $this->ProcessInputData('setStatusCmpCallCard112', true);
		if ( $data === false ) { return false; }
		
		
		$response = $this->dbmodel->setStatusCmpCallCard112($data);
		
		if ( !empty($response[0]['Error_Msg']) ) {
			$this->ReturnError($response[0]['Error_Msg']);
			return;
		}
		
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 * @return bool
	 */
	function setStatusCmpCallCardList112(){
		$data = $this->ProcessInputData('setStatusCmpCallCardList112', true);

		if(isset($data['allCards'])){
			$arr = $this->dbmodel->find112CardsProcessing($data);
			$data['CmpCallCard112StatusType_id'] = 1; // Все статусы "В обработке" меняем на "Новая"
		}
		else{
			$arr = json_decode($data['CmpCallCard112_ids']);
		}
		foreach ($arr as $key => $value) {
			$response = $this->dbmodel->setStatusCmpCallCard112(array(
					'CmpCallCard112_id'=>$value,
					'CmpCallCard112StatusType_id'=>$data['CmpCallCard112StatusType_id'],
					'pmUser_id'=>$data['pmUser_id']
				)
			);
		};
		
		return true;
	}
	
	
	/**
	 * Загрузка списком карт вызовов
	 */
	function loadSMPCmpCallCardsList() {
		$data = $this->ProcessInputData('loadSMPCmpCallCardsList', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadSMPCmpCallCardsList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	*  Получение данных для формы редактирования карты вызова
	*  Входящие данные: $_POST['CmpCallCard_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования карты вызова
	*/
	function loadCmpCallCardEditForm() {
		$data = array();
		$val  = array();
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadCmpCallCardEditForm', true);

		if (!$data) {
			return false;
		}

		//var_dump($data); exit;
		$response = $this->dbmodel->loadCmpCallCardEditForm($data);

		if ( is_array($response) && count($response) > 0 ) {
			$val = $response;
			array_walk($val[0], 'ConvertFromWin1251ToUTF8');
		}

		$this->ReturnData($val);

		return true;
	}

	/**
	 * default desc
	 */
	function loadSmpFarmacyRegister() {
		$data = $this->ProcessInputData('loadSmpFarmacyRegister', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadSmpFarmacyRegister($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * default desc
	 */
	function loadSmpFarmacyRegisterHistory() {

		$data = $this->ProcessInputData('loadSmpFarmacyRegisterHistory', false);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->loadSmpFarmacyRegisterHistory($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * default desc
	 */
	function saveSmpFarmacyDrug() {
		$data = $this->ProcessInputData('saveSmpFarmacyDrug', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->saveSmpFarmacyDrug($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * default desc
	 */
	function removeSmpFarmacyDrug() {
		$data = $this->ProcessInputData('removeSmpFarmacyDrug', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->removeSmpFarmacyDrug($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * default desc
	 */
	function printControlTicket() {
		$data = $this->ProcessInputData( 'printControlTicket', true );
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->printControlTicket( $data );
		if ( !is_array( $response ) || !sizeof( $response ) ) {
			echo 'Не удалось получить данные контрольного листа.';
			return false;
		}

		$this->load->library('parser');



        switch ($_SESSION['region']['nick']){
            case 'astra':
                $res = $this->parser->parse( 'print_controlticket_astra', $response[0] );
                break;
            default: $res = $this->parser->parse( 'print_controlticket', $response[0] );
        }

		return $res;
	}

	/**
	 * default desc
	 */
	function loadUnformalizedAddressDirectory() {
		$data = $this->ProcessInputData('loadUnformalizedAddressDirectory', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadUnformalizedAddressDirectory($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * default desc
	 */
	function loadUnformalizedAddressType() {
		$data = $this->ProcessInputData('loadUnformalizedAddressType', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadUnformalizedAddressType($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 *
	 * @param type $data
	 */
	public function getUnformalizedAddressStreetKladrParams() {
		$data = $this->ProcessInputData('getUnformalizedAddressStreetKladrParams',true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->getUnformalizedAddressStreetKladrParams( $data );
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение списка неформализованных адресов
	 */
	public function saveUnformalizedAddress(){
		$data = $this->ProcessInputData('saveUnformalizedAddress', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->saveUnformalizedAddress($data);
		return $this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 * default desc
	 */
	function deleteUnformalizedAddress() {
		$data = $this->ProcessInputData('deleteUnformalizedAddress', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->deleteUnformalizedAddress($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();

		if( !$response || strlen($response[0]['Error_Msg'])>0 ){
			return false;
		}else{
			return true;
		}
	}

	/**
	 * Назначение бригады на вызов
	 * Производится без дополнительного запроса в мобильном АРМе бригады
	 */
	public function setEmergencyTeamWithoutSending() {
		$data = $this->ProcessInputData('setEmergencyTeamWithoutSending', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->setEmergencyTeam($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 * Проверка на дубли
	 */
	function checkDuplicateCmpCallCard(){
		$data = $this->ProcessInputData('checkDuplicateCmpCallCard', true);
		if ( $data === false ) { return false; }
		$this->load->model('CmpCallCard_model', 'cccmodel');

		$matches = array();
		preg_match('/^\d{1,2}:\d\d/', $data['CmpCallCard_prmTime'], $matches);
		$data['CmpCallCard_prmTime'] = $matches[0];

		$response = $this->cccmodel->checkDuplicateCmpCallCard($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка подстанций СМП
	 */
	public function loadSmpUnits(){
		$data = $this->ProcessInputData('loadSmpUnits', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadSmpUnits($data);
		return $this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение списка подчиненных подстанций СМП
	 */
	public function getLpuBuildingOptions(){
		$data = $this->ProcessInputData('getLpuBuildingOptions', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->getLpuBuildingOptions($data);
		return $this->ProcessModelList($response, true)->ReturnData();
	}
	/**
	 * Получение информации о контролируемых подстанциях
	 */
	public function getControlLpuBuildingsInfo(){
		$response = $this->dbmodel->getControlLpuBuildingsInfo();
		return $this->ProcessModelList($response, true)->ReturnData();
	}

	/**
	 * Копирование карты вызова в другое МО (подстанцию)
	 */
	public function copyCmpCallCardToLpu() {
		$data = $this->ProcessInputData('copyCmpCallCardToLpu', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->copyCmpCallCardToLpu($data);
		return $this->ProcessModelList($response, true)->ReturnData();
	}

	/**
	 * Получение списка подчиненных подстанций СМП
	 */
	public function loadSmpUnitsNested(){
		$data = $this->ProcessInputData('loadSmpUnitsNested', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadSmpUnitsNested($data);
		return $this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * @return bool
	 */
	public function loadSmpUnitsNestedALL(){
		$data = $this->ProcessInputData('loadSmpUnitsNested', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadSmpUnitsNestedALL($data);
		return $this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение списка лпу подчиненных подстанций СМП
	 */
	public function loadLpuWithNestedSmpUnits(){
		$data = $this->ProcessInputData('loadLpuWithNestedSmpUnits', true);
		if ($data === false) {
			return false;
		}
		$this->load->model("User_model", "User_model");
		$groups = $this->User_model->getGroupsDB();

		$user = pmAuthUser::find($_SESSION['login']);
		if (!$user)
			die();

		$recordCallsAuditGroup = $user->havingGroup('recordCallsAudit');

		//если состоит в группе Аудит то все подчиненные подстанции - (точно подчиненные? лпу фильтрует только по MedServiceType_id, если указан в параметре)
		//иначе только те, которые выбраны в опциях
		if($recordCallsAuditGroup){
			$this->load->model("MedService_model4E", "MedService_model4E");
			//для получения всех лпу
			$data['Lpu_id']=null;
			$response = $this->MedService_model4E->getLpusWithMedService($data);
		}
		else{
			$response = $this->dbmodel->loadLpuWithNestedSmpUnits($data);
		}

		return $this->ProcessModelList($response, true, true)->ReturnData();
	}


	/**
	 * Получение списка мо подстанций подчиненных опер отделу
	 */
	public function loadLpuWithNestedLpuBuildings(){
		$data = $this->ProcessInputData('loadLpuWithNestedLpuBuildings', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadLpuWithNestedLpuBuildings( $data );
		$this->ProcessModelSave( $response, false, false )->ReturnData();
	}

	/**
	 * Получение списка подчиненных подстанций СМП из опций
	 */
	public function loadSmpUnitsFromOptions(){
		$data = $this->ProcessInputData('loadSmpUnitsFromOptions', true);

		if ($data === false) {
			return false;
		}
		$this->load->model("User_model", "User_model");
		$groups = $this->User_model->getGroupsDB();

		$user = pmAuthUser::find($_SESSION['login']);
		if (!$user)
			die();

		$recordCallsAuditGroup = $user->havingGroup('recordCallsAudit');

		//если состоит в группе Аудит то все подчиненные подстанции
		//иначе только те, которые выбраны в опциях
		if($recordCallsAuditGroup){
			$data['Lpu_id'] = !empty($data['Lpu_id']) ? $data['Lpu_id']: $data['session']['lpu_id'];
			//$response = $this->dbmodel->loadSmpUnitsNested($data);
			$response = $this->dbmodel->loadSmpUnits($data);
		}
		else{
			$response = $this->dbmodel->loadSmpUnitsFromOptions($data);
		}
		return $this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение списка ранжированных по правилу профилей бригад для вызова
	 * @return boolean
	 */
	public function getEmergencyTeamPriorityFromReason() {
		$data = $this->ProcessInputData('getEmergencyTeamPriorityFromReason',true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->getEmergencyTeamPriorityFromReason( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}

	/**
	 * Получение списка ранжированных по правилу профилей бригад для вызова
	 * @return boolean
	 */
	public function getCallsPriorityFromReason() {
		$data = $this->ProcessInputData('getCallsPriorityFromReason',true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->getCallsPriorityFromReason( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}

	/**
	 * Установка времени ускорения вызова
	 */
	public function setCmpCallCardBoostTime() {
		$data = $this->ProcessInputData('setCmpCallCardBoostTime', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->setCmpCallCardBoostTime($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 * Установка времени ускорения вызова
	 */
	public function setCmpCallCardSecondReason() {
		$data = $this->ProcessInputData('setCmpCallCardSecondReason', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->setCmpCallCardSecondReason($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 * Возвращает информацию по талону вызова для всех АРМов.
	 * Входные данные: CmpCallCard_id
	 * Выходные данные: JSON-строка
	 */
	function getCmpCallCardSmpInfo(){
		$data = $this->ProcessInputData('getCmpCallCardSmpInfo', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->getCmpCallCardSmpInfo($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();
		//TODO: Сделать одним запросом. После учесть что при изменении запроса к одному из АРМов, необходимо будет изменить этот запрос
		//Поскольку HeadBrig пока не нужен, в этом запросе его не учитываем


		return true;
	}

	/**
	 * Получение срочности и профиля вызова
	 */
	public function getCallUrgencyAndProfile(){
		$data = $this->ProcessInputData('getCallUrgencyAndProfile',true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->getCallUrgencyAndProfile( $data );
		$this->ProcessModelSave( $response, true, true )->ReturnData();
	}

	/**
	 * Получение списанных на вызов лекарств
	 */
	public function loadCallCardFarmacyRegisterHistory(){
		$data = $this->ProcessInputData('loadCallCardFarmacyRegisterHistory',true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->loadCallCardFarmacyRegisterHistory( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}

	/**
	 * Получение истории статусов карты вызова
	 */
	public function loadCmpCallCardStatusHistory(){
		$data = $this->ProcessInputData('loadCmpCallCardStatusHistory',true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->loadCmpCallCardStatusHistory( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}


	/**
	 * Получение истории событий карты вызова
	 */
	public function loadCmpCallCardEventHistory(){
		$data = $this->ProcessInputData('loadCmpCallCardEventHistory',true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->loadCmpCallCardEventHistory( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}


	/**
	 * Получение срочности и профиля вызова
	 */
	public function removeCmpCallCardFarmacyDrugHistory(){
		$data = $this->ProcessInputData('removeCmpCallCardFarmacyDrugHistory',true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->removeCmpCallCardFarmacyDrugHistory( $data );
		$this->ProcessModelSave( $response, true, true )->ReturnData();
	}

	/**
	 * Метод получения количества закрытых вызовов за смену указанной бригады
	 */
	public function getCountCateredCmpCallCards(){
		$data = $this->ProcessInputData('getCountCateredCmpCallCards',true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->getCountCateredCmpCallCards( $data );
		//var_dump($response);
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}

	/**
	 * Получение данных для короткой карты закрытия вызова в АРМ ДП
	 */
	public function loadCmpCloseCardShort(){
		$data = $this->ProcessInputData('loadCmpCloseCardShort',true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->loadCmpCloseCardShort( $data );
		$this->ProcessModelSave( $response, true, true )->ReturnData();
	}

	/**
	 * Получение списка диагнозов
	 */
	public function getDiags() {
		$data = $this->ProcessInputData('getDiags',true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->getDiags($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение session
	 */
	public function getSess() {
		$s = $_SESSION;
		$s['setting'] = '';
		$s['allgroups'] = '';
		var_dump($s);
	}

	/**
	*  Закрытие карты вызова СМП
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма редактирования карты вызова СМП
	*/
	public function saveCmpCallCardClose() {
		$data = $this->ProcessInputData('saveCmpCallCardClose', true);

		if ( $data === false ) { return false; }

		$data['CmpLpu_id'] = $data['LpuTransmit_id'];
		$data['Lpu_id'] = $data['session']['lpu_id'];
		$response = $this->dbmodel->saveCmpCallCardClose($data);
		/*
		$status_data['CmpCallCardStatusType_id'] = '6';
		$status_data['CmpCallCardStatus_Comment'] = '';
		$status_data['pmUser_id'] = $data['pmUser_id'];
		$status_data['CmpCallCard_id'] = $response[0]['CmpCallCard_id'];
		$response = $this->dbmodel->setStatusCmpCallCard($status_data);
		*/

		$this->ProcessModelSave($response, true, 'Ошибка при сохранении карты вызова СМП')->ReturnData();

		return true;
	}

	/**
	 * Печать контрольного талона закрытого вызова
	 * default desc
	 */
	public function printCmpCallCardCloseTicket() {
		$data = $this->ProcessInputData( 'printCmpCallCardCloseTicket', true );
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->printCmpCallCardCloseTicket( $data );
		if ( !is_array( $response ) || !sizeof( $response ) ) {
			echo 'Не удалось получить данные контрольного листа.';
			return false;
		}
		$drugs = $this->dbmodel->loadEmergencyTeamDrugPackMoveList(array('CmpCallCard_id'=>$data['CmpCallCard_id']));
		$this->load->library('parser');

		$response[0]['Drugs'] = $drugs;

		return $this->parser->parse( 'print_cmpcallcard_closeticket', $response[0] );
	}

	/**
	 * Определение подстанции по адресу
	 */

	public function getLpuBuildingByAddress() {

		$data = $this->ProcessInputData('getLpuBuildingByAddress', true);

		if ( $data === false ) { return false; }

		if (empty($data['UnformalizedAddressDirectory_id'])&&empty($data['KLStreet_id'])) {
			$response = array(array('success'=>'false','Errom_Msg'=>'Не передан идентификатор улицы или объекта'));
		}

		$response = $this->dbmodel->getLpuBuildingByAddress($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение типа передачи талона вызова бригаде
	 * @return boolean
	 */
	public function setCmpCallCardTransType() {
		$data = $this->ProcessInputData('setCmpCallCardTransType', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->setCmpCallCardTransType($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении типа передачи талона вызова бригаде')->ReturnData();
		return true;
	}

	/**
	 * Сохранение временных полей в карту вызова
	 * @return boolean
	 */
	public function saveCmpCallCardTimes() {
		$data = $this->ProcessInputData('saveCmpCallCardTimes', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->saveCmpCallCardTimes($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении карты')->ReturnData();
		return true;
	}

	/**
	 * Сохранение адреса, комментария и временных полей в карту вызова
	 */
	public function saveShortCmpCallCard() {
		$data = $this->ProcessInputData('saveShortCmpCallCard', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->saveShortCmpCallCard($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении карты')->ReturnData();
		return true;
	}

	/**
	 * Определение подстанции по сессии
	 */
	public function getLpuBuildingBySessionData() {
		$data = $this->ProcessInputData('getLpuBuildingBySessionData', true);
		$response = $this->dbmodel->getLpuBuildingBySessionData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

    /**
	 * Определение подстанции по сессии
	 */
	public function getOperDepartamentOptions() {
		$data = $this->ProcessInputData('getOperDepartamentOptions', true);
		$response = $this->dbmodel->getOperDepartamentOptions($data);

        $this->ProcessModelSave( $response, true, true )->ReturnData();
		return $response;
	}
	/**
	 * Определение и сохранение подстанции по сессии
	 */
	public function getLpuBuildingBySessionDataInto() {
		$data = $this->ProcessInputData('getLpuBuildingBySessionData', true);
		$response = $this->dbmodel->getLpuBuildingBySessionData($data);
		return $response;
	}
	/**
	 * Проверка на дубли по адресу
	 */
	function checkDuplicateCmpCallCardByAddress(){
		$data = $this->ProcessInputData('checkDuplicateCmpCallCard', true);
		if ( $data === false ) { return false; }
		$this->load->model('CmpCallCard_model', 'cccmodel');

		$matches = array();
		preg_match('/^\d{1,2}:\d\d/', $data['CmpCallCard_prmTime'], $matches);
		$data['CmpCallCard_prmTime'] = $matches[0];

		$response = $this->cccmodel->checkDuplicateCmpCallCardByAddress($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}
	/**
	 * Проверка на дубли по ФИО
	 */
	function checkDuplicateCmpCallCardByFIO(){
		$data = $this->ProcessInputData('checkDuplicateCmpCallCard', true);
		if ( $data === false ) { return false; }
		$this->load->model('CmpCallCard_model', 'cccmodel');

		$matches = array();
		preg_match('/^\d{1,2}:\d\d/', $data['CmpCallCard_prmTime'], $matches);
		$data['CmpCallCard_prmTime'] = $matches[0];

		$response = $this->cccmodel->checkDuplicateCmpCallCardByFIO($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}
	/**
	 * Определение подстанции по переданному CurMedService_id
	 */
	public function getLpuBuildingByCurMedServiceId() {
		$data = $this->ProcessInputData('getLpuBuildingByCurMedServiceId', true);
		if (!$data) {
			return false;
		}
		$response = $this->dbmodel->getLpuBuildingByMedServiceId(array('MedService_id' => $data['CurMedService_id']));
		return $this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Определение подстанции по переданному CurMedService_id
	 */
	public function getCmpCallCardListForDoubleChoose() {
		$data = $this->ProcessInputData('getCmpCallCardListForDoubleChoose', true);
		$response = $this->dbmodel->getCmpCallCardListForDoubleChoose($data);
		return $this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Принятие талона вызова диспетчером подстанции
	 * @return boolean
	 */
	public function acceptCmpCallCardByDispatchStation() {
		$data = $this->ProcessInputData('acceptCmpCallCardByDispatchStation', true);
		if ( $data === false ) { return false; }
		$responseSetStatus = $this->dbmodel->setStatusCmpCallCard(array(
			'CmpCallCard_id'=>$data['CmpCallCard_id'],
			'CmpCallCardStatusType_id'=>2,
			'pmUser_id'=>$data['pmUser_id']
		));
		if (!$responseSetStatus||!is_array($responseSetStatus)||!isset($responseSetStatus[0])||(strlen($responseSetStatus[0]['Error_Msg'])>0)) {
			return $responseSetStatus;
		}

		$responseSendDispatchEmergencyTeam = $this->dbmodel->sendNodeCallCard($data);

		$this->ProcessModelSave($responseSendDispatchEmergencyTeam, true)->ReturnData();
		return true;
	}

	/**
	 * Отклонение талона вызова диспетчером подстанции
	 * @return boolean
	 */
	public function declineCmpCallCardByDispatchStation() {
		$data = $this->ProcessInputData('declineCmpCallCardByDispatchStation', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->setStatusCmpCallCard(array(
			'CmpCallCard_id'=>$data['CmpCallCard_id'],
			'CmpCallCardStatusType_id'=>8,
			'pmUser_id'=>$data['pmUser_id']
		));
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	/**
	 * Метод получения списка медикаментов с количеством из укладки наряда по идентификатору талона вызова
	 * @return boolean
	 */
	public function loadEmergencyTeamDrugPackByCmpCallCardId() {
		$data = $this->ProcessInputData('loadEmergencyTeamDrugPackByCmpCallCardId', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->loadEmergencyTeamDrugPackByCmpCallCardId($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Метод получения списка списанных с укладки на талон вызова медикаментов
	 * @return boolean
	 */
	public function loadEmergencyTeamDrugPackMoveList() {
		$data = $this->ProcessInputData('loadEmergencyTeamDrugPackMoveList', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->loadEmergencyTeamDrugPackMoveList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	/**
	 * Метод снятия бригады с вызова.
	 * @return boolean
	 */
	public function cancelCmpCallCardFromEmergencyTeam() {
		$data = $this->ProcessInputData('cancelCmpCallCardFromEmergencyTeam', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->cancelCmpCallCardFromEmergencyTeam($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Проверка наличия закрытых вызовов за последние сутки по указанному адресу
	 */
	public function checkLastDayClosedCallsByAddress(){
		$data = $this->ProcessInputData('checkLastDayClosedCallsByAddress');
		if (!$data) {
			return false;
		}

		$result = $this->dbmodel->checkLastDayClosedCallsByAddress($data);
		return $this->ProcessModelList($result, true, true)->ReturnData();
	}

	/**
	 * Проверка наличия закрытых вызовов за последние сутки по указанному пациенту
	 */
	public function checkLastDayClosedCallsByPersonId(){
		$data = $this->ProcessInputData('checkLastDayClosedCallsByPersonId');
		if (!$data) {
			return false;
		}

		$result = $this->dbmodel->checkLastDayClosedCallsByPersonId($data['Person_id']);
		return $this->ProcessModelList($result, true, true)->ReturnData();
	}

	/**
	 * Проверка наличия закрытых вызовов за последние сутки по адресу и по указанному пациенту
	 */
	public function checkLastDayClosedCallsByAddressAndPersonId(){
		$data = $this->ProcessInputData('checkLastDayClosedCallsByAddressAndPersonId');
		if (!$data) {
			return false;
		}

		$result = $this->dbmodel->checkLastDayClosedCallsByAddressAndPersonId($data);
		return $this->ProcessModelList($result, true, true)->ReturnData();
	}

	/**
	 * Загрузка типов поводов отказа
	 */
	public function getRejectionReason(){
		$response = $this->dbmodel->getRejectionReason();
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Определение статуса карты старшим врачом
	 */
	public function setStatusCmpCallCardByHD(){
		$data = $this->ProcessInputData('setStatusCmpCallCardByHD');

		$response = $this->dbmodel->setStatusCmpCallCardByHD($data);
		
		if ($this->config->item('IsSMPServer') || $this->config->item('IsLocalSMP')) {
			unset($this->db);
			$this->load->database('main');

			$this->dbmodel->setStatusCmpCallCardByHD($data);
			
			unset($this->db);
			$this->load->database();
		}
		
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение аудио звонка вызова СМП
	 */
	public function saveCallAudio(){
		$data = $this->ProcessInputData('saveCallAudio');

		$response = $this->dbmodel->saveCallAudio($data);

		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	* Получение ссылки на аудио
	*/
	public function getCallAudio(){
		$data = $this->ProcessInputData('getCallAudio');

		$response = $this->dbmodel->getCallAudio($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	* Получение списка ссылок на аудио
	*/
	public function getCallAudioList(){
		$data = $this->ProcessInputData('getCallAudioList');

		$response = $this->dbmodel->getCallAudioList($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	* Экспорт аудио
	*/
	public function getExportCallAudios(){
		$data = $this->ProcessInputData('getExportCallAudios');

		$response = $this->dbmodel->getExportCallAudios($data);

		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	* Удаление аудио
	*/
	public function removeCallAudio(){
		$data = $this->ProcessInputData('removeCallAudio');

		$response = $this->dbmodel->removeCallAudio($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	* Удаление аудио по просрокам
	*/
	public function removeCallAudioBytimer(){
		$response = $this->dbmodel->removeCallAudioBytimer();
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * история статусов бригады
	 */
	public function loadBrigadesHistory(){
		$data = $this->ProcessInputData('loadBrigadesHistory');
		if ( $data === false && empty($data['EmergencyTeam_id'])) {
			return false;
		}

		$response = $this->dbmodel->loadBrigadesHistory($data);
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}

	/**
	 * Получение службы НМП для вызова по адресу с учетом даты/времени вызова
	 */
	public function getNmpMedService() {
		$data = $this->ProcessInputData('getNmpMedService');
		if ( $data === false ) return false;

		$response = $this->dbmodel->getNmpMedService($data);
		$this->ProcessModelSave( $response )->ReturnData();
	}

	/**
	 * Обновление полей cmpcallcard у отложенного вызова
	 */
	public function setDefferedCmpCallCardParams(){
		$data = $this->ProcessInputData('setDefferedCmpCallCardParams');
		if ( $data === false ) return false;

		$response = $this->dbmodel->setDefferedCmpCallCardParams($data);
		$this->ProcessModelSave( $response )->ReturnData();
	}
	/**
	 *Получение из структуры МО настроек требующие решения старшего врача
	 */
	public function getSettingsChallengesRequiringTheDecisionOfSeniorDoctor() {
		$data = $this->ProcessInputData('getSettingsChallengesRequiringTheDecisionOfSeniorDoctor');
		if (!$data) {
			return false;
		}
		$response = $this->dbmodel->getSettingsChallengesRequiringTheDecisionOfSeniorDoctor(array('LpuBuilding_id' => $data['LpuBuilding_id']));
		$this->ReturnData($response);
	}

	/**
	 * Получение вызовов принятых диспетчером
	 */
	public function loadDispatcherCallsList(){
		$data = $this->ProcessInputData('loadDispatcherCallsList');

		if (!$data) {return false;}
		if (!isset($data['begDate']) || !isset($data['endDate'])) {return false;}
		$searchOnReserveBase = false;
		$hasReservedDB = $this->config->item('hasReservedDB');

		if(
			($_SESSION['region']['nick'] == 'perm') &&
			//!empty($data['Person_FIO']) &&			
			($hasReservedDB)
		){
			$searchOnReserveBase = true;
		}

		if($searchOnReserveBase){
			unset($this->db);
			$this->load->database('search');
		}


		$response = $this->dbmodel->loadDispatcherCallsList($data);

		if($searchOnReserveBase){
			//сейчас мы на по умалочанию базе
			unset($this->db);
			$this->load->database();
		}

		$this->ReturnData($response);

	}

	/**
	 * Получение обслуженных вызовов принятых диспетчером
	 */
	public function loadDispatcherCallsServedList(){
		$data = $this->ProcessInputData('loadDispatcherCallsList');
		if ($data === false) { return false; }

		$data['CmpCallCardStatusType_id'] = 4;
		$data['useLdapLpuBuildings'] = true;
		$response = $this->dbmodel->loadDispatcherCallsList($data);
		$this->ReturnData($response);

	}

	/**
	 * Получение вызовов принятых диспетчером
	 */
	public function loadCmpCallCard112List(){
		$data = $this->ProcessInputData('loadCmpCallCard112List');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadCmpCallCard112List($data);
		$this->ReturnData($response);
	}

	/**
	 * Поиск карты вызова 112
	 */
	public function findCmpCallCard112(){
		$data = $this->ProcessInputData('findCmpCallCard112');
		if ($data === false) { return false; }

		$response = $this->dbmodel->findCmpCallCard112($data);
		$this->ProcessModelSave($response, true, 'Ошибка поиска карты 112')->ReturnData();
	}

	/**
	 * Получение данных карты 112
	 */
	public function loadCmpCallCard112EditForm(){
		$data = $this->ProcessInputData('loadCmpCallCard112EditForm');
		if (!$data) {
			return false;
		}
		$response = $this->dbmodel->loadCmpCallCard112EditForm($data);
		$this->ReturnData($response);
	}

	/**
	 * Получение всех подстанций СМП региона
	 */
	public function loadRegionSmpUnits() {
		$data = $this->ProcessInputData('loadRegionSmpUnits',true);
		if ( $data === false ) {
			return false;
		}
		$this->load->model('CmpCallCard_model', 'cccmodel');

		$response = $this->cccmodel->loadRegionSmpUnits($data);
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}

	/**
	 * Печать журнала вызова
	 */
	public function printCmpCallsList(){
		$data = $this->ProcessInputData('loadDispatcherCallsList',true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->loadDispatcherCallsList( $data );
		if ( !is_array( $response ) || !sizeof( $response ) ) {
			echo 'Не удалось получить данные для печати';
			return false;
		}

		$this->load->library('parser');

		return $this->parser->parse( 'print_cmpcallslist', $response );
	}

	/**
	 * Загрузка статусов карты
	 */
	public function loadCmpCallCardStatusTypes(){
		$data = $this->ProcessInputData('loadCmpCallCardStatusTypes',true);
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->loadCmpCallCardStatusTypes($data);
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}
	
	/**
	 * Загрузка статусов карты
	 */
	public function setLpuHospitalized(){
		$data = $this->ProcessInputData('setLpuHospitalized',true);
		if ( $data === false ) {
			return false;
		}


		$response = $this->dbmodel->setLpuHospitalized($data);
		if(isset($response[0]['success']) && $response[0]['success']){

			if($data['formType'] != 'traum' && (!isset($data['Person_id']) || !($data['Person_id']) || !(intval($data['Person_id'],10) > 0))) {
				$this->ProcessModelSave( array(),false,'Человек не идентифицирован. Автоматическое резервирование койки невозможно' )->ReturnData();
			}
			else{
				if($data['Code'] != 'MO' || $data['formType'] == 'traum') {

					$IsSMPServer = $this->config->item('IsSMPServer');
					if ($IsSMPServer === true) // если веб СМП
					{
						//сейчас мы на бд смп
						unset($this->db);

						try{
							// переключение базы в модели работает только так!
							// так просто не работает: $this->load->database('main');
							$this->db = $this->load->database('main', true);
						} catch (Exception $e) {
							//return $this->createError($e->getCode(), 'db_unable_to_connect');
						}

						$this->dbmodel->setLpuHospitalized($data);
							if($data['formType'] != 'traum'){
								$response = $this->dbmodel->createEvnDirection($data);
							}

						$this->ProcessModelSave( $response )->ReturnData();


						unset($this->db);
						$this->db = $this->load->database('default', true);
						$this->db->throw_exception = false;
						//сейчас мы на бд смп
					}
					else{
						// если не веб СМП, просто создаем
						$response = $this->dbmodel->createEvnDirection($data, false);
						$this->ProcessModelSave( $response )->ReturnData();
					}
				}
				else
					$this->ProcessModelSave( $response )->ReturnData();
			}
		}
		else
			$this->ProcessModelSave( $response )->ReturnData();

	}

	/**
	 * Сохранение дополнительных данных при госпитализации
	 */
	public function saveScales() {
		$this->load->model('BSK_Register_User_model', 'bskmodel');
		$bskrules = $this->bskmodel->getInputRules('saveInOKS');
		$this->inputRules['saveScales'] = array_merge($this->inputRules['saveScales'], $bskrules);

		$data = $this->ProcessInputData('saveScales',true);

		if($data === false) return false;

		$response = [ 'Error_Msg' => '' ];

		if( $data['diagType'] == 'OKS' ) {
			$data['MorbusType_id'] = 19;
			$data['AbsoluteList'] = '{}';
			$data['RelativeList'] = '{}';
			$data['Registry_method'] = 'ins';
			$result = $this->bskmodel->saveInOks($data);
			if(!empty($result[0]) && !empty($result['Error_Msg']))
				$response['Error_Msg'] .= " Ошибка при сохранении в регистр БСК.";
		}

		if( isset($data['ScaleLams_Value']) ) {
			$result = $this->dbmodel->saveScaleLams($data);
			if(!empty($result['Error_Msg']))
				$response['Error_Msg'] .= " Ошибка при сохранении шкалы LAMS.";
		}

		if( isset($data['PrehospTraumaScale_Value']) ) {
			$result = $this->dbmodel->savePrehospTraumaScale($data);
			if(!empty($result['Error_Msg']))
				$response['Error_Msg'] .= ' Ошибка при сохранении шкалы оценки тяжести.';
		}
		$this->ReturnData($response);
	}

	/**
	 * @desc проверка атрибута "отображать вызовы с превышением срока обслуживания
	 * в отдельной группе АРМ СВ" оперативного отдела данного подразделения
	 */
	public function getIsOverCallLpuBuildingData() {
		$data = $this->ProcessInputData('getIsOverCallLpuBuildingData', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getIsOverCallLpuBuildingData($data,true,null);
		$this->ReturnData($response);
	}

	/**
	 * Получение списка активов в поликлиннику
	 */
	public function loadAktivJournalList(){
		$data = $this->ProcessInputData('loadAktivJournalList', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadAktivJournalList($data);

		$this->ReturnData($response);
	}
	/**
	 * 	Возвращает  список ключевых типов событий
	 */
	function loadCmpCallCardEventType() {
		$data = $this->ProcessInputData( 'loadCmpCallCardEventType', true );

		if ( $data ) {
			$response = $this->dbmodel->loadCmpCallCardEventType( $data );
			$this->ProcessModelList( $response, true, true )->ReturnData();
		}
	}

	/**
	 * Загрузка вызовов для интерактивной карты
	 */
	public function loadSMPInteractiveMapWorkPlace() {
		$data = $this->ProcessInputData('loadSMPInteractiveMapWorkPlace');
		if (!$data) return;
		$response = $this->dbmodel->loadSMPInteractiveMapWorkPlace($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * 	Возвращает  список ключевых типов событий
	 */
	function sendCmpCallCardToLpuBuilding() {
		$data = $this->ProcessInputData( 'sendCmpCallCardToLpuBuilding', true );

		if ( $data ) {
			$response = $this->dbmodel->sendCmpCallCardToLpuBuilding($data);
			$this->ProcessModelSave( $response, true, true )->ReturnData();
		}
	}

	/**
	 * Получение (активных) вызовов под контролем старшего врача
	 */
	public function loadCallsUnderControlList(){
		$data = $this->ProcessInputData('loadCallsUnderControlList');

		if (!$data) {return false;}
		$searchOnReserveBase = false;
		$hasReservedDB = $this->config->item('hasReservedDB');

		if(
			($_SESSION['region']['nick'] == 'perm') &&
			//!empty($data['Person_FIO']) &&
			($hasReservedDB)
		){
			$searchOnReserveBase = true;
		}

		if($searchOnReserveBase){
			unset($this->db);
			$this->load->database('search');
		}


		$response = $this->dbmodel->loadCallsUnderControlList($data);

		if($searchOnReserveBase){
			//сейчас мы на БД по умолчанию
			unset($this->db);
			$this->load->database();
		}

		$this->ReturnData($response);

	}
	/**
	 * 	Возвращает список правил контроля вызовов с превышением времени назначения на бригаду
	 */
	function loadActiveCallRules() {
		$data = $this->ProcessInputData( 'loadActiveCallRules', true );

		if ( $data ) {
			$response = $this->dbmodel->loadActiveCallRules( $data );
			$this->ProcessModelList( $response, true, true )->ReturnData();
		}
	}
	/**
	 * 	Сохраняет  правило контроля вызовов с превышением времени назначения на бригаду
	 */
	function saveActiveCallRules() {
		$data = $this->ProcessInputData( 'saveActiveCallRules', true );
		if (!$data) {
			return false;
		}
		$result = $this->dbmodel->saveActiveCallRules($data);
		return $this->ProcessModelSave($result, true, 'Не удалось сохранить информацию о территории, обслуживаемой подразделением.')->ReturnData();
	}
	/**
	 * 	Сохраняет флаг контроля карты вызова
	 */
	function setCmpCallCardToControl() {
		$data = $this->ProcessInputData( 'setCmpCallCardToControl', true );
		if (!$data) {
			return false;
		}
		$result = $this->dbmodel->setCmpCallCardToControl($data);
		return $this->ProcessModelSave($result, true, 'Не удалось сохранить информацию о территории, обслуживаемой подразделением.')->ReturnData();
	}
	/**
	 * 	Загрузка данных для редактирования правила контроля вызовов с превышением времени назначения на бригаду
	 */
	function getActiveCallRuleEdit() {
		$data = $this->ProcessInputData( 'getActiveCallRuleEdit', true );
		if (!$data) {
			return false;
		}
		$result = $this->dbmodel->getActiveCallRuleEdit($data['ActiveCallRule_id'], $data['LpuBuilding_id']);

		return $this->ProcessModelList($result)->ReturnData();
	}
	/**
	 * 	Возвращает список правил контроля вызовов с превышением времени назначения на бригаду
	 */
	function checkNeedActiveCall() {
		$data = $this->ProcessInputData( 'checkNeedActiveCall', true );

		if ( $data ) {
			$response = $this->dbmodel->checkNeedActiveCall( $data );
			$this->ProcessModelList( $response, true, true )->ReturnData();
		}
	}

	/**
	 * Загрузка вызовов для формы "Результат обслуживания НМП"
	 */
	function loadSelectNmpReasonWindow(){
		$data = $this->ProcessInputData( 'loadSelectNmpReasonWindow', true );

		if ( $data ) {
			$response = $this->dbmodel->loadSelectNmpReasonWindow( $data );
			$this->ProcessModelList( $response )->ReturnData();
		}
	}

	/**
	 * Сохранение результата НМП для нескольких вызовов и смена статуса в зависимости от результата
	 */
	function setResultCmpCallCards(){
		$data = $this->ProcessInputData( 'setResultCmpCallCards', true );

		if ($data) {
			$response = $this->dbmodel->setResultCmpCallCards($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}


	}

	/**
	 * Смена типа вызова у попутных
	 * Отклонение бригады у попутных
	 * Связь попутных при отмене первичного
	 */
	public function cancelEmergencyTeamFromCalls() {
		$data = $this->ProcessInputData( 'cancelEmergencyTeamFromCalls', true );
		if ( $data === false ) {
			return false;
		}

		$data['ARMType_id'] = $_SESSION['CurARM']['ARMType_id'];

		if ($data) {
			$response = $this->dbmodel->cancelEmergencyTeamFromCalls($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}

		return true;
	}

	/**
	 * Вывод вызова из отложенных
	 */
	public function setDefferedCallToTransmitted() {
		$data = $this->ProcessInputData( 'setDefferedCallToTransmitted', true );

		if ( $data === false || !$this->checkSaveCmpCallCard($data)) {
			return false;
		}

		if ($data) {
			$response = $this->dbmodel->setDefferedCallToTransmitted($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}

		return true;
	}

	/**
	 * Обновление данных об управлении подстанциями
	 */
	public function updateSmpUnitHistoryData() {
		$data = $this->ProcessInputData( 'updateSmpUnitHistoryData', true );
		if ( $data === false ) {
			return false;
		}

		if(!empty($data['lpuBuildings'])){
			$data['lpuBuildings'] = json_decode($data['lpuBuildings']);
		}

		if ($data) {
			$response = $this->dbmodel->updateSmpUnitHistoryData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}

		return true;
	}

	/**
	 * Список диспетчеров, управляющих подстанциями
	 * Или подстанций, по диспетчеру
	 */
	function getDispControlLpuBuilding(){

		$data = $this->ProcessInputData( 'getDispControlLpuBuilding', true );
        if ( $data === false ) {
            return false;
        }


        //Пока так, потом может потребоваться поиск по medpersonal
		if(!empty($data['lpuBuildings'])){
			$data['lpuBuildings'] = json_decode($data['lpuBuildings']);
			$response = $this->dbmodel->getDispControlLpuBuilding($data['lpuBuildings'], 'LpuBuilding_id');
		}

		if (isset($response)) {
			$this->ProcessModelList($response, true, true)->ReturnData();
		}

	}

	/**
	 * Проверка наличия подстанций без управления диспетчером
	 */
	function checkLpuBuildingWithoutSmpUnitHistory(){

		$data = $this->ProcessInputData( 'checkLpuBuildingWithoutSmpUnitHistory', true );
		if ( $data === false ) {
			return false;
		}

		if ($data) {
			$this->load->model('MedService_model4E', 'MedService_model4E');
			$lbs = $this->MedService_model4E->loadMedPersonalLpuBuildings($data);

			if(count($lbs) > 0){
				//$response = $this->dbmodel->getDispControlLpuBuilding(array_keys($lbs), 'LpuBuilding_id', false);

                $user = pmAuthUser::find($_SESSION['login']);
                $settings = @unserialize($user->settings);
                if ( isset($settings['lpuBuildingsWorkAccess']) && is_array($settings['lpuBuildingsWorkAccess']) ) {
                    foreach ($settings['lpuBuildingsWorkAccess'] as $mp) {
                        unset($lbs[$mp]);
                    }
                } else if(isset($settings['lpuBuildingsWorkAccess']) && !is_array($settings['lpuBuildingsWorkAccess'])) {
                    unset($lbs[$settings['lpuBuildingsWorkAccess']]);
                }
			}

			$this->ProcessModelList($lbs, true, true)->ReturnData();
		}

	}
	
	/**
	*  Проверка возможности сохранение карты вызова СМП
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма редактирования карты вызова СМП
	*/
	public function checkSaveCmpCallCard($data) {

        if ( $data === false ) {
			 return false;
		}
		
		$response = $this->dbmodel->checkSaveCmpCallCard( $data );
		
		if (is_array($response) && count($response) > 0){
			if ($response[0]['CmpCallCardStatusType_id'] != $data['CmpCallCardStatusType_currentId']){
				$this->ReturnData( array( 'success' => false, 'Error_Msg' => 'Изменение вызова невозможно. Вызов обработан.' ) );
				return false;				
			}            
		}
		
		return true;
	}	

    /**
     * Список диспетчеров, управляющих подстанциями
     * Или подстанций, по диспетчеру
     */
    function loadCmpCommonStateCombo(){

        $response = $this->dbmodel->loadCmpCommonStateCombo();
        if (isset($response)) {
            $this->ProcessModelList($response, true, true)->ReturnData();
}

    }

}
