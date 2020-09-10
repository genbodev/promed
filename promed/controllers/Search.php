<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Search - контроллер для работы с формами поиска
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      17.11.2009
 * @property Search_model dbmodel
**/
class Search extends swController {
	/**
	*  Описание правил для входящих параметров
	*  @var array
	*/
	public $inputRules = array(
		// Карты вызова
		'CmpCallCard' => array(
			// Пациент (карта)
			array(
				'field' => 'CmpCloseCard_id',
				'label' => 'Идентификатор карты закрытия',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpArea_gid',
				'label' => 'В каком районе госпитализирован',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpCallCard_Expo',
				'label' => 'Экспертная оценка',
				'rules' => 'replace_percent',
				'type' => 'int'
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
				'field' => 'Diag_uCode_From',
				'label' => 'Уточненный код диагноза по МКБ-10 (с)',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_uCode_To',
				'label' => 'Уточненный код диагноза по МКБ-10 (по)',
				'rules' => '',
				'type' => 'string'
			),

			// Вызов
			array(
				'field' => 'CmpArea_id',
				'label' => 'Код района',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpCallCard_City',
				'label' => 'Населенный пункт',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Dom',
				'label' => 'Дом',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Ktov',
				'label' => 'Кто вызывает',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'field' => 'ResultDeseaseType_id',
				'label' => 'Исход',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpResult_Code_From',
				'label' => 'Результат карты в интервале (включительно) от',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpResult_Code_To',
				'label' => 'Результат карты в интервале (включительно) до',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Kvar',
				'label' => 'Квартира',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Line',
				'label' => 'Пульт приема',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Ngod',
				'label' => 'Номер с начала года',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Numv',
				'label' => 'Номер вызова',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_NgodPr',
				'label' => 'Номер с начала года',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_NumvPr',
				'label' => 'Номер вызова',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CmpNumber_From',
				'label' => 'Номер вызова от',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpNumber_To',
				'label' => 'Номер вызова до',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EmergencyTeamNum',
				'label' => 'Номер бригады',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EmergencyTeamSpec_id',
				'label' => 'Профиль бригады',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_prmDate_Range',
				'label' => 'Дата приема (диапазон)',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'Подстанция СМП',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Prty',
				'label' => 'Приоритет',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Sect',
				'label' => 'Сектор',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Stan',
				'label' => 'Номер П/С',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_InRegistry',
				'label' => 'Не вошедшие в реестр',
				'rules' => '',
				'type'  => 'int'
			),
			array(
				'field' => 'CmpCallCard_Ulic',
				'label' => 'Улица',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallType_id',
				'label' => 'Тип вызова',
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
				'field' => 'Lpu_oid',
				'label' => 'Куда доставлен',
				'rules' => '',
				'type' => 'id'
			),
			// Управление вызовом
			array(
				'field' => 'CmpCallCard_D201',
				'label' => 'Старший диспетчер смены',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Dlit',
				'label' => 'Длительность приема вызова в сек.',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Dsp1',
				'label' => 'Принял',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Dsp2',
				'label' => 'Назначил',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Dsp3',
				'label' => 'Закрыл',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Dspp',
				'label' => 'Передал',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Prdl',
				'label' => 'Номер строки из списка предложений',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Smpp',
				'label' => 'Код ССМП приема вызова',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Vr51',
				'label' => 'Старший врач смены',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			// Бригада СМП
			array(
				'field' => 'CmpCallCard_Dokt',
				'label' => 'Фамилия старшего в бригаде',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Kakp',
				'label' => 'Как получен',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Kilo',
				'label' => 'Километраж',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'CmpCallCard_Ncar',
				'label' => 'Номер машины',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Numb',
				'label' => 'Номер бригады',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CLLpuBuilding_id',
				'label' => 'Подстанция бригады СМП',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Smpb',
				'label' => 'Код станции СМП бригады',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Stbb',
				'label' => 'Номер П/С базирования бригады',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Stbr',
				'label' => 'Номер П/С бригады по управлению',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Tab2',
				'label' => '1-й помощник',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Tab3',
				'label' => '2-й помощник',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Tab4',
				'label' => 'Водитель',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Tabn',
				'label' => 'Номер старшего в бригаде',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_isPaid',
				'label' => 'Случай оплачен',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'CmpProfile_bid',
				'label' => 'Профиль бригады',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
				'rules' => '',
				'type' => 'id'
			),
			/*
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
				'type' => 'date'
			), 
			array(
				'default' => null,
				'field' => 'TPRM',
				'label' => 'Принят',
				'rules' => 'trim',
				'type' => 'time'
			), 
			array(
				'default' => null,
				'field' => 'TPER',
				'label' => 'Передан',
				'rules' => 'trim',
				'type' => 'time'
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
			array(
				'field' => 'SECT',
				'label' => 'исп',
				'rules' => 'trim',
				'type' => 'int'
			), 
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
				'type' => 'int'
			),
			array(
				'field' => 'TAB2',
				'label' => 'П1',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'TAB3',
				'label' => 'П2',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'TAB4',
				'label' => 'В',
				'rules' => 'trim',
				'type' => 'int'
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
				'type' => 'int'
			),
			array(
				'field' => 'D201',
				'label' => 'Ст. дисп',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'DSP1',
				'label' => 'Принял',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'DSP2',
				'label' => 'Назначил',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'DSPP',
				'label' => 'Передал',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'DSP3',
				'label' => 'Закрыл',
				'rules' => 'trim',
				'type' => 'int'
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
				'type' => 'time'
			), 
			array(
				'default' => null,
				'field' => 'VYEZ',
				'label' => 'Выезд',
				'rules' => 'trim',
				'type' => 'time'
			), 
			array(
				'default' => null,
				'field' => 'PRZD',
				'label' => 'Прибыл',
				'rules' => 'trim',
				'type' => 'time'
			), 
			array(
				'default' => null,
				'field' => 'TGSP',
				'label' => 'Госпит',
				'rules' => 'trim',
				'type' => 'time'
			), 
			array(
				'default' => null,
				'field' => 'TSTA',
				'label' => 'В_стац',
				'rules' => 'trim',
				'type' => 'time'
			), 
			array(
				'default' => null,
				'field' => 'TISP',
				'label' => 'Исполн.',
				'rules' => 'trim',
				'type' => 'time'
			), 
			array(
				'default' => null,
				'field' => 'TVZV',
				'label' => 'Возвр',
				'rules' => 'trim',
				'type' => 'time'
			), 
			array(
				'field' => 'KILO',
				'label' => 'Километр',
				'rules' => 'trim',
				'type' => 'int'
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
				'type' => 'time'
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
			)
			*/
		),
		
		'CmpCloseCard' => array(
			// Пациент (карта)
			array(
				'field' => 'CmpCloseCard_id',
				'label' => 'Идентификатор карты закрытия',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpArea_gid',
				'label' => 'В каком районе госпитализирован',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpCallCard_Expo',
				'label' => 'Экспертная оценка',
				'rules' => 'replace_percent',
				'type' => 'int'
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
				'field' => 'Diag_uCode_From',
				'label' => 'Уточненный код диагноза по МКБ-10 (с)',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_uCode_To',
				'label' => 'Уточненный код диагноза по МКБ-10 (по)',
				'rules' => '',
				'type' => 'string'
			),			
			// Вызов
			array(
				'field' => 'CmpArea_id',
				'label' => 'Код района',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpCallCard_City',
				'label' => 'Населенный пункт',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Dom',
				'label' => 'Дом',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Ktov',
				'label' => 'Кто вызывает',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'field' => 'ResultDeseaseType_id',
				'label' => 'Исход',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpResult_Code_From',
				'label' => 'Результат карты в интервале (включительно) от',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpResult_Code_To',
				'label' => 'Результат карты в интервале (включительно) до',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCardInputType_id',
				'label' => 'Источник карты вызова',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Kvar',
				'label' => 'Квартира',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_Line',
				'label' => 'Пульт приема',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Ngod',
				'label' => 'Номер с начала года',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Numv',
				'label' => 'Номер вызова',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpNumber_From',
				'label' => 'Номер вызова от',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpNumber_To',
				'label' => 'Номер вызова до',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EmergencyTeamNum',
				'label' => 'Номер бригады',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EmergencyTeamSpec_id',
				'label' => 'Профиль бригады',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_prmDate_Range',
				'label' => 'Дата приема (диапазон)',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'CmpCallCard_Prty',
				'label' => 'Приоритет',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Sect',
				'label' => 'Сектор',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Stan',
				'label' => 'Номер П/С',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_InRegistry',
				'label' => 'Не вошедшие в реестр',
				'rules' => '',
				'type'  => 'int'
			),
			array(
				'field' => 'CmpCallCard_Ulic',
				'label' => 'Улица',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallType_id',
				'label' => 'Тип вызова',
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
				'field' => 'ResultUfa_id',
				'label' => 'Результат',
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
				'field' => 'Lpu_oid',
				'label' => 'Куда доставлен',
				'rules' => '',
				'type' => 'id'
			),			
			array(
				'field' => 'CmpCallCard_Kilo',
				'label' => 'Километраж',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'CmpCallCard_Ncar',
				'label' => 'Номер машины',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Numb',
				'label' => 'Номер бригады',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Smpb',
				'label' => 'Код станции СМП бригады',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'Подстанция СМП',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CLLpuBuilding_id',
				'label' => 'Подстанция бригады СМП',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Stbb',
				'label' => 'Номер П/С базирования бригады',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Stbr',
				'label' => 'Номер П/С бригады по управлению',
				'rules' => '',
				'type' => 'int'
			),			
			array(
				'field' => 'CmpCallCard_isPaid',
				'label' => 'Случай оплачен',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'CmpProfile_bid',
				'label' => 'Профиль бригады',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Вид оплаты ("Посещение")',
				'rules' => '',
				'type' => 'id'
			),
		),
		'EvnPL' => array(
			// Диагноз и услуги
			array(
				'field' => 'Diag_IsNotSet',
				'label' => 'Диагноз не установлен ("Диагноз и услуги")',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'DeseaseType_id',
				'label' => 'Характер заболевания ("Диагноз и услуги")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_Code_From',
				'label' => 'Код диагноза с ("Диагноз и услуги")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_Code_To',
				'label' => 'Код диагноза по ("Диагноз и услуги")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'LpuSectionDiag_id',
				'label' => 'Отделение ("Диагноз и услуги")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonalDiag_id',
				'label' => 'Код врача ("Диагноз и услуги")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaCategory_id',
				'label' => 'Категория услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_Code_From',
				'label' => 'Выполненная услуга с',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaComplex_Code_To',
				'label' => 'Выполненная услуга по',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaComplex_uid',
				'label' => 'Код посещения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_Code',
				'label' => 'Код посещения (шаблон)',
				'rules' => '',
				'type' => 'string'
			),

			// Посещение
			array(
				'field' => 'EvnPL_IsUnlaw',
				'label' => 'Противоправная ("Посещение")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPL_IsUnport',
				'label' => 'Нетранспортабельность ("Посещение")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPL_NumCard',
				'label' => '№ талона',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnPL_setDate_Range',
				'label' => 'Дата начала случая (диапазон)',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'EvnPL_disDate_Range',
				'label' => 'Дата окончания случая (диапазон)',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'VizitClass_id',
				'label' => 'Вид посещения',
				'rules' => '',
				'type'  => 'id'
			),
			array(
				'field' => 'LpuBuildingViz_id',
				'label' => 'Подразделение ("Посещение")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionViz_id',
				'label' => 'Отделение ("Посещение")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonalViz_id',
				'label' => 'Врач ("Посещение")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFactViz_id',
				'label' => 'Рабочее место врача ("Посещение")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonalViz_sid',
				'label' => 'Средний м/перс. ("Посещение")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Вид оплаты ("Посещение")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrehospTrauma_id',
				'label' => 'Травма ("Посещение")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnVizitPLStom_IsPrimaryVizit',
				'label' => 'Первично в текущем году',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLStom_KSG',
				'label' => 'Наличие КСГ',
				'rules' => '',
				'type'  => 'int'
			),

			array(
				'field' => 'EvnPLStom_KSG_Num',
				'label' => 'Номер КСГ',
				'rules' => '',
				'type'  => 'int'
			),

			array(
				'field' => 'ServiceType_id',
				'label' => 'Место обслуживания ("Посещение")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnVizitPL_isPaid',
				'label' => 'Случай оплачен',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'HealthKind_id',
				'label' => 'Группа здоровья',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnVizitPLStom_isPaid',
				'label' => 'Случай оплачен',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Vizit_Date_Range',
				'label' => 'Диапазон дат посещения ("Посещение")',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'VizitType_id',
				'label' => 'Цель посещения ("Посещение")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPL_InRegistry',
				'label' => 'ТАП, включение в реестр',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnPLStom_InRegistry',
				'label' => 'ТАП Стоматология, включение в реестр',
				'rules' => '',
				'type' => 'int'
			),

			// Результаты
			array(
				'field' => 'DirectClass_id',
				'label' => 'Направление ("Результаты")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DirectType_id',
				'label' => 'Куда направлен ("Регультаты")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPL_IsFinish',
				'label' => 'Случай закончен ("Результаты")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_begDate_Range',
				'label' => 'Диапазон дат открытия листа нетрудоспособности ("Результаты")',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'EvnStick_endDate_Range',
				'label' => 'Диапазон дат закрытия листа нетрудоспособности ("Результаты")',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'Lpu_oid',
				'label' => 'ЛПУ ("Результаты")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_oid',
				'label' => 'Отделение ("Результаты")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ResultClass_id',
				'label' => 'Результат лечения ("Результаты")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StickCause_id',
				'label' => 'Причина выдачи ("Результаты")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StickType_id',
				'label' => 'Тип листа ("Результаты")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'InterruptLeaveType_id',
				'label' => 'Случай прерван',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Service1EvnStatus_id',
				'label' => 'Статус фед.сервиса ИЭМК',
				'rules' => '',
				'type' => 'id'
			)
		),

		'EvnPS' => array(
			// Госпитализация
			array(
				'field' => 'EvnDirection_Num',
				'label' => '№ направления ("Госпитализация")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirection_setDate_Range',
				'label' => 'Диапазон дат направления ("Госпитализация")',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'EvnPS_IsUnlaw',
				'label' => 'Противоправная ("Госпитализация")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsUnport',
				'label' => 'Нетранспортабельность ("Госпитализация")',
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
				'field' => 'EvnPS_IsWithoutDirection',
				'label' => 'Без электронного направления ("Госпитализация")',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'EvnSection_insideNumCard',
				'label' => 'insideNum',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnPS_NumCard',
				'label' => '№ карты ("Госпитализация")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Lpu_did',
				'label' => 'Направившее ЛПУ ("Госпитализация")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_IsFondHolder',
				'label' => 'Фондодержатель ("Госпитализация")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_did',
				'label' => 'Направившее отделение ("Госпитализация")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuUnitType_did',
				'label' => 'Тип стационара ("Госпитализация")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Org_did',
				'label' => 'Направившая организация ("Госпитализация")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OrgMilitary_did',
				'label' => 'Направивший военкомат ("Госпитализация")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Вид оплаты ("Госпитализация")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrehospArrive_id',
				'label' => 'Способ доставки ("Госпитализация")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrehospDirect_id',
				'label' => 'Кем направлен ("Госпитализация")',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PrehospToxic_id',
				'label' => 'Вид опьянения ("Госпитализация")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrehospTrauma_id',
				'label' => 'Травма ("Госпитализация")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrehospType_id',
				'label' => 'Тип госпитализации ("Госпитализация")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_disDate_Range',
				'label' => 'Диапазон дат выписки ("Госпитализация")',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'EvnPS_HospCount_Max',
				'label' => 'Максимальное количество госпитализаций ("Госпитализация")',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnPS_HospCount_Min',
				'label' => 'Минимальный количество госпитализаций ("Госпитализация")',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnPS_setDate_Range',
				'label' => 'Диапазон дат поступления ("Госпитализация")',
				'rules' => 'trim',
				'type' => 'daterange'
			),
            array(
                'field' => 'Date_Type',
                'label' => 'Тип поиска по дате',
                'rules' => '',
                'type'  => 'int'
            ),
			array(
				'field' => 'EvnPS_TimeDesease_Max',
				'label' => 'Максимальное время от начала заболевания ("Госпитализация")',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnPS_TimeDesease_Min',
				'label' => 'Минимальное время от начала заболевания ("Госпитализация")',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnPS_InRegistry',
				'label' => 'КВС, включенние в реестр',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnReanimatPeriod_setDate',
				'label' => 'Дата начала реанимационного периода',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'EvnReanimatPeriod_disDate',
				'label' => 'Дата окончания реанимационного периода',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			// Лечение
			array(
				'field' => 'Diag_Code_From',
				'label' => 'Код диагноза с ("Лечение")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_Code_To',
				'label' => 'Код диагноза по ("Лечение")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'DiagSetClass_id',
				'label' => 'Вид диагноза ("Лечение")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DiagSetType_id',
				'label' => 'Тип диагноза ("Лечение")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_disDate_Range',
				'label' => 'Диапазон дат выписки ("Лечение")',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'EvnSection_setDate_Range',
				'label' => 'Диапазон дат поступления ("Лечение")',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'EvnSection_isPaid',
				'label' => 'Случай оплачен',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnUsluga_setDate_Range',
				'label' => 'Диапазон дат выполнения услуги ("Лечение")',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'LpuBuilding_cid',
				'label' => 'Подразделение ("Лечение")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_cid',
				'label' => 'Отделение ("Лечение")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_cid',
				'label' => 'Лечащий врач ("Лечение")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaCategory_id',
				'label' => 'Категория услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_Code_From',
				'label' => 'Выполненная услуга с',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaComplex_Code_To',
				'label' => 'Выполненная услуга по',
				'rules' => '',
				'type' => 'string'
			),
			// Результат лечения
			array(
				'field' => 'CureResult_id',
				'label' => 'Итог лечения',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnLeave_IsNotSet',
				'label' => 'Исход не указан ("Результат лечения")',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnLeaveBase_UKL',
				'label' => 'ERK ("Результат лечения")',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'EvnLeave_IsAmbul',
				'label' => 'Направлен на амбулаторное долечивание',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_begDate_Range',
				'label' => 'Диапазон дат открытия листа нетрудоспособности ("Результат лечения")',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'EvnStick_endDate_Range',
				'label' => 'Диапазон дат закрытия листа нетрудоспособности ("Результат лечения")',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'LeaveCause_id',
				'label' => 'Причина выписки / перевода ("Результат лечения")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LeaveType_id',
				'label' => 'Исход госпитализации ("Результат лечения")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Org_oid',
				'label' => 'ЛПУ ("Результат лечения")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_oid',
				'label' => 'Отделение ("Результат лечения")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuUnitType_oid',
				'label' => 'Тип стационара ("Результат лечения")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ResultDesease_id',
				'label' => 'Исход заболевания ("Результат лечения")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StickCause_id',
				'label' => 'Причина выдачи ("Результат лечения")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StickType_id',
				'label' => 'Тип листа ("Результат лечения")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Service1EvnStatus_id',
				'label' => 'Статус фед.сервиса ИЭМК',
				'rules' => '',
				'type' => 'id'
			)
		),

		'EvnDtpWound' => array(
			// Извещения о раненых в ДТП
			array(
				'field' => 'EvnDtpWound_setDate_Range',
				'label' => 'Диапазон дат заполнения извещения',
				'rules' => 'trim',
				'type' => 'daterange'
			)		
		),

		'EvnDtpDeath' => array(
			// Извещения о скончавшихся в ДТП
			array(
				'field' => 'EvnDtpDeath_setDate_Range',
				'label' => 'Диапазон дат заполнения извещения',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'EvnDtpDeath_DeathDate_Range',
				'label' => 'Диапазон дат смерти',
				'rules' => 'trim',
				'type' => 'daterange'
			)			
		),

		'EvnUslugaPar' => array(
			// Услуга
			array(
				'field' => 'EvnDirection_Num',
				'label' => 'Номер направления ("Услуга")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirection_setDate',
				'label' => 'Дата направления ("Услуга")',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnUslugaPar_setDate_Range',
				'label' => 'Диапазон дат оказания услуги ("Услуга")',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'LpuSection_uid',
				'label' => 'Отделение ("Услуги")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_did',
				'label' => 'Направившее отделение ("Услуги")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_did',
				'label' => 'Направивший врач ("Услуги")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_uid',
				'label' => 'Врач, оказавший услугу ("Услуги")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Вид оплаты ("Услуга")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrehospDirect_id',
				'label' => 'Кем направлен ("Услуга")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaCategory_id',
				'label' => 'Категория Услуг ("Услуга")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Услуга ("Услуга")',
				'rules' => '',
				'type' => 'id'
			),
            array(
                'field' => 'Part_of_the_study',
                'label' => 'В составе исследования',
                'rules' => '',
                'type' => 'checkbox'
            ),

		),

		'searchData' => array(
			// Тип формы поиска
			array(
				'field' => 'SearchFormType',
				'label' => 'Тип формы поиска',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'getCountOnly',
				'label' => 'Посчитать только каунт',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Org_did',
				'label' => 'Организация',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ProsthesType_id',
				'label' => 'Тип протезирования',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_iid',
				'label' => 'Врач постановки на учёт',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_IsAdultEscort',
				'label' => 'Сопровождается взрослым',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'LpuSection_hid',
				'label' => 'Госпитализирован в',
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
				'field' => 'Ksg_id',
				'label' => 'Код КСГ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Kpg_id',
				'label' => 'КПГ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'hasObrTalonMse',
				'label' => 'Наличие обратного талона МСЭ',
				'rules' => '',
				'type' => 'string'
			),
			
			// Тип поиска человека
			array(
				'default' => 1,
				'field' => 'PersonPeriodicType_id',
				'label' => 'Тип поиска человека',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Услуга',
				'rules' => '',
				'type' => 'id'
			),
			// Пациент
			array(
					'field' => 'EvnPLWOW_setDate_Range',
					'label' => 'Диапазон дат обследований',
					'rules' => 'trim',
					'type' => 'daterange'
			),
			array(
				'default' => 0,
				'field' => 'OMSSprTerr_id',
				'label' => 'Территория страхования ("Пациент")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'OrgSmo_id',
				'label' => 'СМО, выдавшая полис ("Пациент")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'soc_card_id',
				'label' => 'Идентификатор социальной карты',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Birthday',
				'label' => 'Дата рождения ("Пациент")',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'Person_BirthdayYear',
				'label' => 'Год рождения ("Пациент")',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'Person_Birthday_Range',
				'label' => 'Диапазон дат рождения ("Пациент")',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'Person_Code',
				'label' => 'Единый номер полиса ("Пациент")',
				'rules' => 'trim',
				'type' => 'string'
			),
            array(
                'field' => 'Person_Phone',
                'label' => 'Телефон',
                'rules' => '',
                'type' => 'string'
            ),
			array(
				'field' => 'Person_Firname',
				'label' => 'Имя ("Пациент")',
				'rules' => 'ban_percent|trim',
				'type' => 'russtring'
			),
			array(
				'field' => 'Person_Secname',
				'label' => 'Отчество ("Пациент")',
				'rules' => 'ban_percent|trim',
				'type' => 'russtring'
			),
			array(
				'field' => 'Person_Surname',
				'label' => 'Фамилия ("Пациент")',
				'rules' => 'ban_percent|trim',
				'type' => 'russtring'
			),
			array(
				'field' => 'PersonAge',
				'label' => 'Возраст человека ("Пациент")',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonAge_Max',
				'label' => 'Максимальный возраст человека ("Пациент")',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonAge_Min',
				'label' => 'Минимальный возраст человека ("Пациент")',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonBirthdayYear',
				'label' => 'Год рождения человека ("Пациент")',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonBirthdayYear_Max',
				'label' => 'Максимальный год рождения человека ("Пациент")',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonBirthdayYear_Min',
				'label' => 'Минимальный год рождения человека ("Пациент")',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonBirthdayMonth',
				'label' => 'Месяц рождения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonCard_Code',
				'label' => 'Номер карты ("Пациент")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Polis_Num',
				'label' => 'Номер полиса ("Пациент")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Polis_Ser',
				'label' => 'Серия полиса ("Пациент")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'PolisType_id',
				'label' => 'Тип полиса ("Пациент")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_NoPolis',
				'label' => 'Фильтр поиска пациентов без полиса',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'Person_NoOrgSMO',
				'label' => 'Фильтр поиска пациентов без СМО',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'HasPolis_Code',
				'label' => 'Наличие полиса',
				'rules' => '',
				'type' 	=> 'id'
			),
			array('field' => 'dontShowUnknowns', 'label' => '', 'rules' => '', 'type' => 'int'),
			array(
				'field' => 'IsBDZ',
				'label' => 'БДЗ',
				'rules' => '',
				'type' 	=> 'id'
			),
			array(
				'field' => 'TFOMSIdent',
				'label' => 'Идентификатор с ТФОМС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PolisClosed',
				'label' => 'Данные о закрытии полиса',
				'rules' => '',
				'type'  => 'id'
			),
			array(
				'field' => 'PolisClosed_Date_Range',
				'label' => 'Диапазон дат закрытия полиса',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			// Пациент (доп.)
			array(
				'field' => 'Document_Num',
				'label' => 'Номер документа ("Пациент (доп.)")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Document_Ser',
				'label' => 'Серия документа ("Пациент (доп.)")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'DocumentType_id',
				'label' => 'Тип документа ("Пациент (доп.)")',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'Org_id',
				'label' => 'Место работы ("Пациент (доп.)")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'OrgDep_id',
				'label' => 'Организация, выдавшая документ ("Пациент (доп.)")',
				'rules' => '',
				'type' => 'id'
			),
            array(
                'field' => 'Person_citizen',
                'label' => 'Гражданство',
                'rules' => '',
                'type' => 'int'
            ),
			array(
				'field' => 'Person_IsBDZ',
				'label' => 'БДЗ ("Пациент (доп.)")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_isIdentified',
				'label' => 'Идентифицирован ("Пациент (доп.)")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'SnilsExistence',
				'label' => 'Наличие СНИЛС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_Snils',
				'label' => 'СНИЛС ("Пациент (доп.)")',
				'rules' => 'trim',
				'type' => 'snils'
			),
			array(
				'field' => 'Person_Inn',
				'label' => 'ИНН пациента',
				'rules' => 'trim|is_numeric',
				'type' => 'string'
			),
			array(
				'field' => 'Person_IsDisp',
				'label' => 'Диспансерный учет ("Пациент (доп.)")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'Post_id',
				'label' => 'Должность ("Пациент (доп.)")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'Sex_id',
				'label' => 'Пол ("Пациент (доп.)")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'SocStatus_id',
				'label' => 'Социальный статус ("Пациент (доп.)")',
				'rules' => '',
				'type' => 'id'
			),

			// Прикрепление
			array(
				'default' => 0,
				'field' => 'AttachLpu_id',
				'label' => 'ЛПУ прикрепления ("Прикрепление")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuRegion_id',
				'label' => 'Участок ("Прикрепление")',
				'rules' => '',
				'type' => 'int'
			),
            array(
                'field' => 'LpuRegion_Fapid',
                'label' => 'Участок ФАП ("Прикрепление")',
                'rules' => '',
                'type' => 'int'
            ),
			array(
				'field' => 'LpuAttachType_id',
				'label' => 'Тип прикрепления ("Прикрепление")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'LpuRegionType_id',
				'label' => 'Тип участка ("Прикрепление")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'MedPersonal_id',
				'label' => 'Врач участка ("Прикрепление")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonCard_begDate',
				'label' => 'Дата прикрепления ("Прикрепление")',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'PersonCard_begDate_Range',
				'label' => 'Диапазон дат прикрепления ("Прикрепление")',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'PersonCard_endDate',
				'label' => 'Дата открепления ("Прикрепление")',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'PersonCard_endDate_Range',
				'label' => 'Диапазон дат открепления ("Прикрепление")',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'PersonCard_IsAttachCondit',
				'label' => 'Условное прикрепление ("Прикрепление")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonCardAttach',
				'label' => 'Заявление',
				'rules' => '',
				'type'  => 'id'
			),
			array(
				'field' => 'PersonCard_IsDms',
				'label' => 'ДМС прикрепление ("Прикрепление")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonCardStateType_id',
				'label' => 'Актуальность прикрепления ("Прикрепление")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsWaif',
				'label' => 'Беспризорный (КВС)',
				'rules' => '',
				'type' => 'id'
			),

			// Адрес
			array(
				'field' => 'Address_House',
				'label' => 'Номер дома ("Адрес")',
				'rules' => 'trim',
				'type' => 'russtring'
			),
			array(
				'field' => 'Address_Corpus',
				'label' => 'Корпус ("Адрес")',
				'rules' => 'trim',
				'type' => 'russtring'
			),
			array(
				'field' => 'Address_Street',
				'label' => 'Улица ("Адрес")',
				'rules' => 'trim',
				'type' => 'russtring'
			),
			array(
				'default' => 0,
				'field' => 'AddressStateType_id',
				'label' => 'Тип адреса ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'KLAreaType_id',
				'label' => 'Тип населенного пункта ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'KLCity_id',
				'label' => 'Город ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
            array(
                'default' => 0,
                'field' => 'PDKLCountry_id',
                'label' => 'Страна ("Гражданство")',
                'rules' => '',
                'type' => 'id'
            ),
            array(
				'default' => 0,
				'field' => 'KLCountry_id',
				'label' => 'Страна ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'KLRgn_id',
				'label' => 'Регион ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'KLStreet_id',
				'label' => 'Улица ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'KLSubRgn_id',
				'label' => 'Район ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'KLTown_id',
				'label' => 'Населенный пункт ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_NoAddress',
				'label' => 'Без адреса ("Адрес")',
				'rules' => '',
				'type' => 'string'
			),

			// Льгота
			array(
				'field' => 'Privilege_begDate',
				'label' => 'Дата начала действия льготы ("Льгота")',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'Privilege_begDate_Range',
				'label' => 'Диапазон дат начала действия льготы ("Льгота")',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'Privilege_endDate',
				'label' => 'Дата окончания действия льготы ("Льгота")',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'Privilege_endDate_Range',
				'label' => 'Диапазон дат окончания действия льготы ("Льгота")',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'defaul' => 1,
				'field' => 'PrivilegeStateType_id',
				'label' => 'Актуальность льготы ("Льгота")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'defaul' => 0,
				'field' => 'WithDrugComplexMnn',
				'label' => 'Только с комплексным МНН',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Lpu_prid',
				'label' => 'ЛПУ добавления льготы ("Льгота")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrivilegeType_id',
				'label' => 'Категория льготы ("Льгота")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'SubCategoryPrivType_id',
				'label' => 'Подгатегория ("Льгота")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Refuse_id',
				'label' => 'Отказ от льготы ("Льгота")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RefuseNextYear_id',
				'label' => 'Отказ на следующий год ("Льгота")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegisterSelector_id',
				'label' => 'Регистр льготников ("Льгота")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonPrivilege_deleted',
				'label' => 'Льгота удалена ("Льгота")',
				'rules' => '',
				'type' => 'id'
			),

			// Пользователь
			array(
				'field' => 'InsDate',
				'label' => 'Дата добавления ("Пользователь")',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'InsDate_Range',
				'label' => 'Диапазон дат добавления ("Пользователь")',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'default' => 0,
				'field' => 'pmUser_insID',
				'label' => 'Пользователь, добавивший запись ("Пользователь")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'pmUser_updID',
				'label' => 'Пользователь, обновивший запись ("Пользователь")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UpdDate',
				'label' => 'Дата обновления ("Пользователь")',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'UpdDate_Range',
				'label' => 'Диапазон дат обновления ("Пользователь")',
				'rules' => 'trim',
				'type' => 'daterange'
			),

			// Рецепт
			array(
				'default' => 0,
				'field' => 'Drug_id',
				'label' => 'Торговое наименование ("Рецепт")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'DrugMnn_id',
				'label' => 'МНН ("Рецепт")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ER_Diag_Code_From',
				'label' => 'Код диагноза с ("Рецепт")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'ER_Diag_Code_To',
				'label' => 'Код диагноза по ("Рецепт")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'ER_MedPersonal_id',
				'label' => 'Врач ("Рецепт")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ER_PrivilegeType_id',
				'label' => 'Категория льготы ("Рецепт")',
				'rules' => '',
				'type' => 'id'
			),
            array(
                'field' => 'ReceptForm_id',
                'label' => 'Форма рецепта',
                'rules' => '',
                'type'  => 'id'
            ),
			array(
				'field' => 'EvnRecept_IsExtemp',
				'label' => 'Экстемпоральный ("Рецепт (доп.)")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnRecept_Is7Noz',
				'label' => '7 нозологий ("Рецепт")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'EvnRecept_IsKEK',
				'label' => 'Выписка через ВК ("Рецепт")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnRecept_VKProtocolNum',
				'label' => 'Номер протокола ВК',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnRecept_VKProtocolDT',
				'label' => 'Дата протокола ВК',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'default' => 0,
				'field' => 'EvnRecept_IsNotOstat',
				'label' => 'Выписка без наличия медикамента на остатках ("Рецепт")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnRecept_IsSigned',
				'label' => 'Подписан ("Рецепт")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'WhsDocumentCostItemType_id',
				'label'	=> 'Статья расхода',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field' => 'EvnRecept_Num',
				'label' => 'Номер рецепта ("Рецепт")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnRecept_Ser',
				'label' => 'Серия рецепта ("Рецепт")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnRecept_MarkDeleted',
				'label' => 'Помеченные к удалению',
				'rules' => '',
				'type' => 'boolean'
			),
			array(
				'field' => 'EvnReceptSearchDateType',
				'label' => 'Поиск по дате',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnRecept_setDate',
				'label' => 'Дата выписки ("Рецепт")',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnRecept_setDate_Range',
				'label' => 'Диапазон дат выписки ("Рецепт")',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'OrgFarmacy_id',
				'label' => 'Аптека ("Рецепт")',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'OrgFarmacyIndex_OrgFarmacy_id',
				'label' => 'Аптека ЛПУ выписки ("Рецепт")',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 0,
				'field' => 'ReceptDiscount_id',
				'label' => 'Скидка ("Рецепт")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'ReceptFinance_id',
				'label' => 'Тип финансирования ("Рецепт")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'ReceptType_id',
				'label' => 'Тип рецепта ("Рецепт")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'ReceptValid_id',
				'label' => 'Срок действия ("Рецепт")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'inValidRecept',
				'label' => 'С истекшим сроком действия',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'DistributionPoint',
				'label' => 'АРМ провизора',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ReceptDelayType_id',
				'label' => 'Статус рецепта',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Drug_Name',
				'label' => 'Медикамент',
				'rules' => 'trim',
				'type' => 'string'
			),

			// Диспансерный учет
			array(
				'default' => null,
				'field' => 'ViewAll_id',
				'label' => 'Отображать карты ДУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DispLpu_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'isDispAttachAddress',
				'label' => 'Поставлен на диспучет по вирусному гепатиту по месту прикрепления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'isDispAttachOnko',
				'label' => 'Состоит на диспучете в ОД',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'DispLpuSection_id',
				'label' => 'Отделние',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'HTMLpu_id',
				'label' => 'МО оказания ВМП',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'DispLpuSectionProfile_id',
				'label' => 'Профиль отделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'DispMedPersonal_id',
				'label' => 'Поставивший Врач',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'HistMedPersonal_id',
				'label' => 'Ответственный Врач',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field'	=> 'checkMPHistory',
				'label'	=> 'Учитывать историю ответственных врачей',
				'rules'	=> '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'PersonDisp_begDate',
				'label' => 'Дата постановки на дисп. учет',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'PersonDisp_begDate_Range',
				'label' => 'Диапазон дат постановки на дисп. учет',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'PersonDisp_endDate',
				'label' => 'Дата снятия с дисп. учета',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'PersonDisp_endDate_Range',
				'label' => 'Диапазон дат снятия с дисп. учета',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'PersonDisp_NextDate',
				'label' => 'Дата следующей явки',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'PersonDisp_NextDate_Range',
				'label' => 'Диапазон следующей явки',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'PersonDisp_LastDate',
				'label' => 'Дата следующей явки',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'PersonDisp_LastDate_Range',
				'label' => 'Диапазон следующей явки',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'default' => 'off',
				'field' => 'PersonDisp_IsAutoClose',
				'label' => 'Закрыта автоматически',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => null,
				'field' => 'DispOutType_id',
				'label' => 'Причина исключения',
				'rules' => '',
				'type' => 'id'
			),
			// Категория
			array(
				'default' => null,
				'field' => 'PrivilegeTypeWow_id',
				'label' => 'Категория',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'PersonDisp_IsDop',
				'label' => 'По результатам доп. дисп.',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'DiagDetectType',
				'label' => 'По результатам профосмотров',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'Sickness_id',
				'label' => 'По результатам доп. дисп.',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'Disp_Diag_id',
				'label' => 'Диагноз',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Disp_Diag_Code_From',
				'label' => 'Код диагноза с ("Дисп. учет")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Disp_Diag_Code_To',
				'label' => 'Код диагноза по ("Дисп. учет")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => null,
				'field' => 'Disp_Diag_pid',
				'label' => 'Диагноз',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Disp_PredDiag_Code_From',
				'label' => 'Код диагноза с ("Дисп. учет")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Disp_PredDiag_Code_To',
				'label' => 'Код диагноза по ("Дисп. учет")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => null,
				'field' => 'Disp_Diag_nid',
				'label' => 'Диагноз',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Disp_NewDiag_Code_From',
				'label' => 'Код диагноза с ("Дисп. учет")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Disp_NewDiag_Code_To',
				'label' => 'Код диагноза по ("Дисп. учет")',
				'rules' => 'trim',
				'type' => 'string'
			),
			// дата начала ввода в форму потокового ввода регистра по доп диспансеризациии
			array(
				'field' => 'dop_disp_reg_beg_date',
				'label' => 'Дата начала ввода',
				'rules' => 'trim',
				'type' => 'date'
			),
			// дата начала ввода в форму потокового ввода регистра по доп диспансеризациии
			array(
				'field' => 'dop_disp_reg_beg_time',
				'label' => 'Время начала ввода',
				'rules' => 'trim',
				'type' => 'time_with_seconds'
			),
			array(
				'field' => 'PersonDopDisp_Year',
				'label' => 'Год регистра по доп. дисп.',
				'rules' => 'trim',
				'type' => 'int'
			),
			// просмотр деталей по картотеке из журнала
			array(
				'field' => 'PCSD_mode',
				'label' => 'Режим показа деталей',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'PCSD_LpuAttachType_id',
				'label' => 'Идентификатор участка',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PCSD_LpuRegion_id',
				'label' => 'Идентификатор участка',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => 1,
				'field' => 'PCSD_LpuMotion_id',
				'label' => 'Движение',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'PCSD_FromLpu_id',
				'label' => 'Движение',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'PCSD_ToLpu_id',
				'label' => 'Движение',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PCSD_StartDate',
				'label' => 'Дата начала периода',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'PCSD_EndDate',
				'label' => 'Дата окончания периода',
				'rules' => 'trim',
				'type' => 'date'
			),
			// талон по ДД
			array(
				'field' => 'EvnPLDispDop_setDate',
				'label' => 'Дата',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPLDispDop_setDate_Range',
				'label' => 'Диапазон дат',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'EvnPLDispDop_disDate',
				'label' => 'Дата',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPLDispDop_disDate_Range',
				'label' => 'Диапазон дат',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'EvnPLDispDop_VizitCount',
				'label' => '',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'EvnPLDispDop_VizitCount_From',
				'label' => '',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'EvnPLDispDop_VizitCount_To',
				'label' => '',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'EvnPLDispDop_IsFinish',
				'label' => 'Случай закончен',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispDop_HealthKind_id',
				'label' => 'Группа здоровья',
				'rules' => 'trim',
				'type' => 'id'
			),
			// потоковый ввод талона по ДД
			array(
				'field' => 'EvnPLDispDopStream_begDate',
				'label' => 'Дата',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPLDispDopStream_begTime',
				'label' => 'Время',
				'rules' => 'trim',
				'type' => 'time_with_seconds'
			),
			// профосмотр
			array(
				'field' => 'EvnPLDispProf_setDate',
				'label' => 'Дата',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPLDispProf_setDate_Range',
				'label' => 'Диапазон дат',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'EvnPLDispProf_disDate',
				'label' => 'Дата',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPLDispProf_disDate_Range',
				'label' => 'Диапазон дат',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'EvnPLDispProf_IsFinish',
				'label' => 'Случай закончен',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispProf_IsRefusal',
				'label' => 'Отказ от профосмотра',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispProf_HealthKind_id',
				'label' => 'Группа здоровья',
				'rules' => 'trim',
				'type' => 'id'
			),
			// скрининговое исследование
			array(
				'field' => 'EvnPLDispScreen_setDate',
				'label' => 'Дата',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPLDispScreen_setDate_Range',
				'label' => 'Диапазон дат',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'EvnPLDispScreen_disDate',
				'label' => 'Дата',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPLDispScreen_disDate_Range',
				'label' => 'Диапазон дат',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			// скрининговое исследование детей
			array(
				'field' => 'EvnPLDispScreenChild_setDate',
				'label' => 'Дата',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPLDispScreenChild_setDate_Range',
				'label' => 'Диапазон дат',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'EvnPLDispScreenChild_disDate',
				'label' => 'Дата',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPLDispScreenChild_disDate_Range',
				'label' => 'Диапазон дат',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			// талон по ДД13
			array(
				'field' => 'EvnPLDispDop13_setDate',
				'label' => 'Дата',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPLDispDop13_setDate_Range',
				'label' => 'Диапазон дат',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'EvnPLDispDop13_disDate',
				'label' => 'Дата',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPLDispDop13_disDate_Range',
				'label' => 'Диапазон дат',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'EvnPLDispDop13_IsFinish',
				'label' => '1 этап закончен',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispDop13_IsRefusal',
				'label' => 'Отказ от диспансерзации',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispDop13_IsTwoStage',
				'label' => 'Направлен на 2 этап',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispDop13_HealthKind_id',
				'label' => 'Группа здоровья',
				'rules' => 'trim',
				'type' => 'id'
			),
			// 9.посещение
			 array(
				'field' => 'PL_ElDirection',
				'label' => 'Без Эл. Направления',
				'rules' => 'trim',
				'type' => 'string'
			),
			 array(
				'field' => 'PL_PrehospDirect_id',
				'label' => 'Кем направлен',
				'rules' => 'trim',
				'type' => 'id'
			),
			 array(
				'field' => 'PL_NumDirection',
				'label' => 'Номер направления',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PL_DirectionDate',
				'label' => 'Дата направления',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'PL_LpuSection_id',
				'label' => 'Отделение',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PL_Org_id',
				'label' => 'Организация',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PL_Diag_id',
				'label' => 'Диагноз напр. Учреждения',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'TreatmentClass_id',
				'label' => 'Вид обращения',
				'rules' => '',
				'type' => 'id'
			),
			// талон по ДД13 2 этап
			array(
				'field' => 'EvnPLDispDop13Second_setDate',
				'label' => 'Дата',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPLDispDop13Second_setDate_Range',
				'label' => 'Диапазон дат',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'EvnPLDispDop13Second_disDate',
				'label' => 'Дата',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPLDispDop13Second_disDate_Range',
				'label' => 'Диапазон дат',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'EvnPLDispDop13Second_IsFinish',
				'label' => '2 этап закончен',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispDop13Second_HealthKind_id',
				'label' => 'Группа здоровья',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispDop13_isPaid',
				'label' => 'Случай оплачен',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispDop13Second_isPaid',
				'label' => 'Случай оплачен',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispProf_isPaid',
				'label' => 'Случай оплачен',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispDop13_isMobile',
				'label' => 'Случай обслужен мобильной бригадой',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispDop13Second_isMobile',
				'label' => 'Случай обслужен мобильной бригадой',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispProf_isMobile',
				'label' => 'Случай обслужен мобильной бригадой',
				'rules' => 'trim',
				'type' => 'id'
			),
			// потоковый ввод талона по ДД
			array(
				'field' => 'EvnPLDispTeen14Stream_begDate',
				'label' => 'Дата',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPLDispTeen14Stream_begTime',
				'label' => 'Время',
				'rules' => 'trim',
				'type' => 'time_with_seconds'
			),
			// талон по ДД 14
			array(
				'field' => 'EvnPLDispTeen14_setDate',
				'label' => 'Дата',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPLDispTeen14_setDate_Range',
				'label' => 'Диапазон дат',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'EvnPLDispTeen14_disDate',
				'label' => 'Дата',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPLDispTeen14_disDate_Range',
				'label' => 'Диапазон дат',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'EvnPLDispTeen14_VizitCount',
				'label' => '',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'EvnPLDispTeen14_VizitCount_From',
				'label' => '',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'EvnPLDispTeen14_VizitCount_To',
				'label' => '',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'EvnPLDispTeen14_IsFinish',
				'label' => 'Случай закончен',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispTeen14_HealthKind_id',
				'label' => 'Группа здоровья',
				'rules' => 'trim',
				'type' => 'id'
			),
			// осмотры
			array(
				'field' => 'Disp_MedStaffFact_id',
				'label' => 'Врач',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Disp_LpuSection_id',
				'label' => 'Отделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Disp_LpuBuilding_id',
				'label' => 'Подразделение',
				'rules' => '',
				'type' => 'id'
			),
			// потоковый ввод талона по ДД 14
			array(
				'field' => 'EvnPLDispTeen14Stream_begDate',
				'label' => 'Дата',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPLDispTeen14Stream_begTime',
				'label' => 'Время',
				'rules' => 'trim',
				'type' => 'time_with_seconds'
			),
			// талон по диспасеризации детей-сирот
			array(
				'field' => 'EvnPLDispOrp_setDate',
				'label' => 'Дата',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'PersonDispOrp_Year',
				'label' => 'Год',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'CategoryChildType',
				'label' => 'Тип детей-сирот',
				'rules' => 'trim',
				'default' => 'orp',
				'type' => 'string'
			),
			array(
				'field' => 'EducationInstitutionType_id',
				'label' => 'Тип образовательного учреждения',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'AgeGroupDisp_id',
				'label' => 'Возрастная группа',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'OrgExist',
				'label' => 'Признак обучающегося',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispOrp_setDate_Range',
				'label' => 'Диапазон дат',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'EvnPLDispOrp_disDate',
				'label' => 'Дата',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPLDispOrp_disDate_Range',
				'label' => 'Диапазон дат',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'EvnPLDispOrp_VizitCount',
				'label' => '',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'EvnPLDispOrp_VizitCount_From',
				'label' => '',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'EvnPLDispOrp_VizitCount_To',
				'label' => '',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'EvnPLDispOrp_IsFinish',
				'label' => 'Случай закончен',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispOrp_IsRefusal',
				'label' => 'Отказ от прохождения',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispOrp_IsTwoStage',
				'label' => 'Направлен на 2 этап',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispOrp_HealthKind_id',
				'label' => 'Группа здоровья',
				'rules' => 'trim',
				'type' => 'id'
			),
            array(
                'field' => 'EvnPLDispOrp_ChildStatusType_id',
                'label' => 'Статус ребенка',
                'rules' => 'trim',
                'type'  => 'int'
            ),
			array(
				'field' => 'EvnPLDispOrp_isPaid',
				'label' => 'Случай оплачен',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispOrp_isMobile',
				'label' => 'Случай обслужен мобильной бригадой',
				'rules' => 'trim',
				'type' => 'id'
			),
			// Карта осмотра несовершеннолетнего
			array(
				'field' => 'DispClass_id',
				'label' => 'Тип карты ДД',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispTeenInspection_setDate',
				'label' => 'Дата',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPLDispTeenInspection_setDate_Range',
				'label' => 'Диапазон дат',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'EvnPLDispTeenInspection_disDate',
				'label' => 'Дата',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPLDispTeenInspection_disDate_Range',
				'label' => 'Диапазон дат',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'EvnPLDispTeenInspection_IsFinish',
				'label' => 'Случай закончен',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispTeenInspection_IsRefusal',
				'label' => 'Отказ пот прохождения',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispScreen_IsEndStage',
				'label' => 'Случай закончен',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispScreenChild_IsEndStage',
				'label' => 'Случай закончен',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispTeenInspection_isMobile',
				'label' => 'Случай обслужен мобильной бригадой',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispTeenInspection_IsTwoStage',
				'label' => 'Направлен на 2 этап',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispTeenInspection_HealthKind_id',
				'label' => 'Группа здоровья',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispTeenInspection_isPaid',
				'label' => 'Случай оплачен',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'HealthGroupType_oid',
				'label' => 'Медицинская группа для занятий физ.культурой до проведения обследования',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'HealthGroupType_id',
				'label' => 'Медицинская группа для занятий физ.культурой',
				'rules' => 'trim',
				'type' => 'id'
			),
			// потоковый ввод талона по диспасеризации детей сирот
			array(
				'field' => 'EvnPLDispOrpStream_begDate',
				'label' => 'Дата',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPLDispOrpStream_begTime',
				'label' => 'Время',
				'rules' => 'trim',
				'type' => 'time_with_seconds'
			),
			// регистр
			array(
				'field' => 'PersonRegisterEndo_hospDate_Range',
				'label' => 'Дата госпитализации в стационар',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'PersonRegister_setDate_Range',
				'label' => 'Дата включения в регистр',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'PersonRegister_disDate_Range',
				'label' => 'Дата исключения из регистра',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'PersonRegister_evnSection_Range',
				'label' => 'Дата госпитализации',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'PersonRegisterType_id',
				'label' => 'Тип записи регистра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonRegister_Code',
				'label' => 'Номер записи регистра',
				'rules' => 'trim',
				'type' => 'string'
			),
			// регистр по профзаболеваниям
			array(
				'field' => 'MorbusProfDiag_id',
				'label' => 'Заболевание',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OrgWork_id',
				'label' => 'Место работы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_IsDead',
				'label' => 'Смерть',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'Person_DeRegister',
				'label' => 'Выбытие с территории субъекта РФ',
				'rules' => '',
				'type' => 'checkbox'
			),
			// регистр по онкологии
			array(
				'field' => 'PersonRegisterRecordType_id',
				'label' => 'Записи регистра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MorbusOnkoBase_NumCard',
				'label' => 'Регистрационный номер',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'PersonRegister_onkoDeathDate_Range',
				'label' => 'Дата смерти',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'PersonRegister_onkoDiagDeath',
				'label' => 'Причина смерти',
				'rules' => 'trim',
				'type' => 'string'
			),
			// регистр по наркологии
			array(
				'field' => 'RegLpu_id',
				'label' => 'ЛПУ, включившее в регистр',
				'rules' => '',
				'type' => 'id'
			),
			// регистр по нефрологии
			array(
				'field' => 'isNotVizitMonth',
				'label' => 'Неявка пациента в течение месяца',
				'rules' => '',
				'type' => 'id'
			),
			// регистр по гепатиту - диагноз
			array(
				'field' => 'MorbusHepatitisDiag_setDT_Range',
				'label' => 'Дата установки диагноза',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'HepatitisDiagType_id',
				'label' => 'Диагноз',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'HepatitisDiagActiveType_id',
				'label' => 'Активность',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'HepatitisFibrosisType_id',
				'label' => 'Фиброз',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'HepatitisEpidemicMedHistoryType_id',
				'label' => 'Эпиданамнез',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MorbusHepatitis_EpidNum',
				'label' => 'Эпидномер',
				'rules' => 'trim',
				'type' => 'int'
			),
			// регистр по гепатиту - Лаб. подтверждения
			array(
				'field' => 'MorbusHepatitisLabConfirm_setDT_Range',
				'label' => 'Дата исследования',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'HepatitisLabConfirmType_id',
				'label' => 'Тип лабораторного подтверждения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MorbusHepatitisLabConfirm_Result',
				'label' => 'Результат исследования',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			// регистр по гепатиту - Инстр. подтверждения	
			array(
				'field' => 'MorbusHepatitisFuncConfirm_setDT_Range',
				'label' => 'Дата исследования',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'HepatitisFuncConfirmType_id',
				'label' => 'Тип инструментального подтверждения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MorbusHepatitisFuncConfirm_Result',
				'label' => 'Результат исследования',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),	
			// регистр по гепатиту - Лечение
			array(
				'field' => 'MorbusHepatitisCure_begDT',
				'label' => 'Дата лечения с',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'MorbusHepatitisCure_endDT',
				'label' => 'Дата лечения по',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'MorbusHepatitisCure_Drug',
				'label' => 'Препарат',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'field' => 'HepatitisResultClass_id',
				'label' => 'Результат',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'HepatitisSideEffectType_id',
				'label' => 'Побочный эффект',
				'rules' => '',
				'type' => 'id'
			),
			// регистр по гепатиту - Очередь
			array(
				'field' => 'HepatitisQueueType_id',
				'label' => 'Тип очереди',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MorbusHepatitisQueue_Num',
				'label' => 'Номер в очереди',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'MorbusHepatitisQueue_IsCure',
				'label' => 'Лечение проведено',
				'rules' => '',
				'type' => 'id'
			),
			// Извещения
			array(
				'field' => 'EvnNotifyTub_IsFirstDiag',
				'label' => 'Установлен впервые в жизни',
				'rules' => 'trim',
				'type' => 'id'				
			),
			array(
				'field' => 'EvnNotifyBase_setDT_Range',
				'label' => 'Дата заполнения извещения',
				'rules' => 'trim',
				'type' => 'daterange'				
			),
			array(
				'field' => 'isNotifyProcessed',
				'label' => 'Извещение обработано',
				'rules' => 'trim',
				'type' => 'id'
			),	
			array(
				'field' => 'Diag_Code_From',
				'label' => 'Код диагноза с',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_Code_To',
				'label' => 'Код диагноза по',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_Code_Group',
				'label' => 'Группа диагнозов',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'TubDiagSop_id',
				'label' => 'Сопутствующий диагноз',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PersonCategoryType_id',
				'label' => 'Категория населения',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'TubSurveyGroupType_id',
				'label' => 'Выявлен из наблюдаемых в тубучреждениях групп',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'OnkoDiag_Code_From',
				'label' => 'Код диагноза с',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'OnkoDiag_Code_To',
				'label' => 'Код диагноза по',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Lpu_oid',
				'label' => 'МО, в которой пациенту впервые установлен диагноз орфанного заболевания',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_sid',
				'label' => 'ЛПУ, куда направлено извещение',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_did',
				'label' => 'ЛПУ создания извещения',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'isNeglected',
				'label' => 'Составлен протокол (запущенное)',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'isGeneralForm',
				'label' => 'Генерализованные формы',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'isOnlyTheir',
				'label' => 'Признак, что можно показывать документы, созданные только текущим пользователем',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'TumorStage_id',
				'label' => 'Стадия опухолевого процесса',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'TumorCircumIdentType_id',
				'label' => 'Обстоятельства выявления опухоли',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'MorbusOnko_setDiagDT_Range',
				'label' => 'Дата установления диагноза (онко)',
				'rules' => 'trim',
				'type' => 'daterange'				
			),
			array(
				'field' => 'MorbusGEBT_setDiagDT_Range',
				'label' => 'Дата установления диагноза',
				'rules' => 'trim',
				'type' => 'daterange'				
			),
			array(
				'field' => 'MorbusOnko_IsMainTumor',
				'label' => 'Основная опухоль',
				'rules' => 'trim',
				'type' => 'id'
			),	
			array(
				'field' => 'Diag_mid',
				'label' => 'Гистология опухоли',
				'rules' => 'trim',
				'type' => 'id'
			),	
			array(
				'field' => 'TumorStage_id',
				'label' => 'Стадия опухолевого процесса',
				'rules' => 'trim',
				'type' => 'id'
			),	
			array(
				'field' => 'MorbusOnkoSpecTreat_begDate_Range',
				'label' => 'Дата начала лечения',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'MorbusOnkoSpecTreat_endDate_Range',
				'label' => 'Дата окончания лечения',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'TumorPrimaryTreatType_id',
				'label' => 'Проведенное лечение первичной опухоли',
				'rules' => 'trim',
				'type' => 'id'
			),	
			array(
				'field' => 'TumorRadicalTreatIncomplType_id',
				'label' => 'Причины незавершенности радикального лечения',
				'rules' => 'trim',
				'type' => 'id'
			),	
			array(
				'field' => 'OnkoTumorStatusType_id',
				'label' => 'Состояние опухолевого процесса',
				'rules' => 'trim',
				'type' => 'id'
			),	
			array(
				'field' => 'OnkoPersonStateType_id',
				'label' => 'Общее состояние пациента',
				'rules' => 'trim',
				'type' => 'id'
			),	
			array(
				'field' => 'OnkoStatusYearEndType_id',
				'label' => 'Клиническая группа',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnNotifyType_SysNick',
				'label' => 'Тип извещения/направления',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'PersonRegisterType_SysNick',
				'label' => 'Тип регистра',
				'rules' => 'trim',
				'type' => 'string'
			),	
			array(
				'field' => 'PersonRegisterOutCause_id',
				'label' => 'Причина исключения из регистра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'NotifyType_id',
				'label' => 'Тип извещения/направления',
				'rules' => 'trim',
				'type' => 'string'
			),	
			array(
				'field' => 'HIVNotifyType_id',
				'label' => 'Тип извещения ВИЧ',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Диагноз по МКБ-10',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'MorbusHIV_NumImmun',
				'label' => 'Номер иммуноблота',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnNotifyStatus_id',
				'label' => 'Статус извещения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'IsIncluded',
				'label' => 'Включен в регистр',
				'rules' => '',
				'type' => 'id'
			),
			// Параметры страничного вывода EvnNotifyType_id
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 0,
				'field' => 'archiveStart',
				'label' => 'Номер стартовой архивной записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 50,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 'off',
				'field' => 'onlySQL',
				'label' => 'Вывести SQL-запрос',
				'rules' => 'ban_percent', // TO-DO: добавить в правила обработки права пользователя, под которыми можно использовать этот параметр (?)
				'type' => 'string'
			),
			array(
				'field' => 'HTMedicalCareClass_id',
				'label' => 'Метод высокотехнологичной медицинской помощи',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'HTMedicalCareType_id',
				'label' => 'Вид высокотехнологичной медицинской помощи',
				'rules' => '',
				'type' => 'id'
			),
			// регистр ИБС
			array(
				'field' => 'IBSType_id',
				'label' => 'Тип ИБС',
				'rules' => 'trim',
				'type' => 'id'
			),
            array(
                'field' => 'MorbusIBS_IsKGIndication',
                'label' => 'Показано проведение КГ',
                'rules' => 'trim',
                'type' => 'id'
            ),
            array(
                'field' => 'MorbusIBS_IsKGFinished',
                'label' => 'Проведена КГ',
                'rules' => 'trim',
                'type' => 'id'
            ),
			// регистр по гериатрии
			array('field' => 'AgeNotHindrance_id', 'label' => 'Градация пациента по скринингу', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusGeriatrics_IsKGO', 'label' => 'Заполнена Карта комплексной гериатрической оценки (КГО)', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusGeriatrics_IsWheelChair', 'label' => 'Колясочник', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusGeriatrics_IsFallDown', 'label' => 'Падения', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusGeriatrics_IsWeightDecrease', 'label' => 'Снижение веса', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusGeriatrics_IsCapacityDecrease', 'label' => 'Снижение функциональной активности', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusGeriatrics_IsCognitiveDefect', 'label' => 'Когнитивные нарушения', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusGeriatrics_IsMelancholia', 'label' => 'Депрессия', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusGeriatrics_IsEnuresis', 'label' => 'Недержание мочи', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusGeriatrics_IsPolyPragmasy', 'label' => 'Полипрагмазия', 'rules' => '', 'type' => 'id'),
			// регистр по паллиативной помощи
			array(
				'field' => 'MorbusPalliat_IsIVL',
				'label' => 'Нуждается в ИВЛ',
				'rules' => 'trim',
				'type' => 'id'
			),
            array(
                'field' => 'AnesthesiaType_id',
                'label' => 'Нуждается в обезболивании',
                'rules' => '',
                'type' => 'int'
            ),
            array(
                'field' => 'MorbusPalliat_IsZond',
                'label' => 'Находится на зондовом питании',
                'rules' => 'trim',
                'type' => 'id'
            ),
            array(
                'field' => 'ViolationsDegreeType_id',
                'label' => 'Степень выраженности стойких нарушений организма',
                'rules' => 'trim',
                'type' => 'id'
            ),
            array(
                'field' => 'Lpu_sid',
                'label' => 'МО оказания паллиативной помощи (стац)',
                'rules' => 'trim',
                'type' => 'id'
            ),
            array(
                'field' => 'Lpu_aid',
                'label' => 'МО оказания паллиативной помощи (амб)',
                'rules' => 'trim',
                'type' => 'id'
            ),
			//ACSRegistry
            array(
                'field' => 'DiagACS_id',
                'label' => 'Диагноз по ОКС',
                'rules' => 'trim',
                'type' => 'id'
            ),
            array(
                'field' => 'Lpu_iid',
                'label' => 'МО добавления',
                'rules' => 'trim',
                'type' => 'id'
            ),
            array(
                'field' => 'MorbusACS_IsST',
                'label' => 'Подъем сегмента ST',
                'rules' => 'trim',
                'type' => 'int'
            ),
            array(
                'field' => 'MorbusACS_IsCoronary',
                'label' => 'Коронароангиография',
                'rules' => 'trim',
                'type' => 'int'
            ),
            array(
                'field' => 'MorbusACS_IsTransderm',
                'label' => 'Чрезкожное коронарное вмешательство',
                'rules' => 'trim',
                'type' => 'int'
            ),
            array(
                'field' => 'PartMatchSearch',
                'label' => 'Поиск по частичному совпадению',
                'rules' => '',
                'type' => 'checkbox'
            ),
            // Регистр ИПРА
             array(
                'field' => 'PersonRegister_number_IPRA',
                'label' => 'Номер ИПРА',
                'rules' => '',
                'type' => 'string'
            ),   
             array(
                'field' => 'PersonRegister_confirm_IPRA',
                'label' => 'подтверждение ИПРА',
                'rules' => '',
                'type' => 'int'
            ),   
            array(
                'field' => 'PersonRegister_buro_MCE',
                'label' => 'код бюро, проводившее МСЕ',
                'rules' => '',
                'type' => 'int'
            ),     
            array(
                'field' => 'IPRARegistry_DirectionLPU_id',
                'label' => 'МО направившая на МСЭ',
                'rules' => '',
                'type' => 'int'
            ) ,
            array(
                'field' => 'LPU_id',
                'label' => 'МО сопровождения',
                'rules' => '',
                'type' => 'int'
            ),
			array(
				'field' => 'IPRARegistry_EndDate_Range',
				'label' => 'Дата окончания срока ИПРА',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'IPRARegistry_issueDate_Range',
				'label' => 'Дата выдачи ИПРА',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'IPRARegistryData_MedRehab_yn',
				'label' => 'Медицинская реабилитация',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'IPRARegistryData_ReconstructSurg_yn',
				'label' => 'Реконструктивная хирургия',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'IPRARegistryData_Orthotics_yn',
				'label' => 'Протезирование и ортезирование',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'IPRARegistryEdit',
				'label' => 'Группа Регистр ИПРА, редактирование',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'IsMeasuresComplete',
				'label' => 'Мероприятия пыполнены',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'pmUser_confirmID',
				'label' => 'Пользователь подтвердивший',
				'rules' => '',
				'type'  => 'int'
			),
			array(
				'field' => 'PersonRegister_FilterBy',
				'label' => 'Фильтр по:',
				'rules' => '',
				'type'  => 'string'
			),
			//Скрининг населения 60+
			array(
				'field' => 'YesNo_id',
				'label' => 'Дисп. учет',
				'rules' => '',
				'type'  => 'int'
			),
			array(
				'field' => 'ProfileData',
				'label' => 'Профиль наблюдения',
				'rules' => '',
				'type'  => 'int' 
			),
			array(
				'field' => 'OnkoCtrComment_id',
				'label' => 'Онкоконтроль',
				'rules' => '',
				'type'  => 'int'
			), 
			array(
				'field' => 'DisabilityData_id',
				'label' => 'Инвалидность',
				'rules' => '',
				'type'  => 'int'
			),
			array(
				'field' => 'Cholesterol_id',
				'label' => 'Холестерин',
				'rules' => '',
				'type'  => 'int'
			),
			array(
				'field' => 'Sugar_id',
				'label' => 'глюкоза',
				'rules' => '',
				'type'  => 'int'
			),
			array(
				'field' => 'IMT_id',
				'label' => 'глюкоза',
				'rules' => '',
				'type'  => 'int'
			),
			array(
				'field' => 'RiskType_id',
				'label' => 'Оценочный риск',
				'rules' => '',
				'type'  => 'int'
			),
                    //ЭКО
            array(
                'field' => 'EcoRegistryData_dateRange',
                'label' => 'Дата включения в регистр',
                'rules' => 'trim',
                'type' => 'daterange'
            ),
            array(
                    'field' => 'EcoRegistryData_vidOplod',
                    'label' => 'вид оплодотворения',
                    'rules' => '',
                    'type' => 'int'
            ),
            array(
                    'field' => 'EcoRegistryData_countMoveEmbroin',
                    'label' => 'Количество перенесенных эмбрионов',
                    'rules' => '',
                    'type' => 'int'
            ),
            array(
                    'field' => 'EcoRegistryData_ds1_from',
                    'label' => 'основной диагнох',
                    'rules' => '',
                    'type' => 'string'
            ),
            array(
                    'field' => 'EcoRegistryData_ds1_to',
                    'label' => 'основной диагнох',
                    'rules' => '',
                    'type' => 'string'
            ),
            array(
                    'field' => 'EcoPregnancyType_id',
                    'label' => 'Вид беременности',
                    'rules' => '',
                    'type' => 'int'
            ),
            array(
                    'field' => 'PayType_id',
                    'label' => 'Вид оплаты',
                    'rules' => '',
                    'type' => 'int'
            ),
            array(
                    'field' => 'EcoRegistryData_genDiag',
                    'label' => 'ПГД',
                    'rules' => '',
                    'type' => 'int'
            ),
            array(
                    'field' => 'EcoRegistryData_resEco',
                    'label' => 'результат эко',
                    'rules' => '',
                    'type' => 'int'
            ),
            array(
                    'field' => 'EcoRegistryData_lpu_id',
                    'label' => 'лпу',
                    'rules' => '',
                    'type' => 'int'
            ),
            array(
                    'field' => 'EcoRegistryData_noRes',
                    'label' => 'результат не указан',
                    'rules' => '',
                    'type' => 'int'
            ),
            array(
                'field' => 'isRegion',
                'label' => 'региональный пользователь',
                'rules' => '',
                'type' => 'int'
            ),
            //БСК + REAB
             array(
                   'field' => 'Reabquest_yn', 
	 	   'label' => 'Фильтр Анкеты', 
	 	   'rules' => '', 
	 	   'type' => 'checkbox' 
            ), 
            array(
                   'field' => 'ReabScale_yn', 
	 	   'label' => 'Фильтр Шкалы', 
	 	   'rules' => '', 
	 	   'type' => 'checkbox' 
            ), 
             array(
                    'field' => 'StageType_id',
                    'label' => 'Этап реабилитации',
                    'rules' => '',
                    'type' => 'id'
            ), 
			array(
                    'field' => 'ZnoViewLpu_id',
                    'label' => 'Для просмотра своих пациентов',
                    'rules' => '',
                    'type' => 'int'
            ), 
			array(
                    'field' => 'BiopsyRefZNO_id',
                    'label' => 'Направление на биопсию',
                    'rules' => '',
                    'type' => 'int'
            ), 
			array(
                    'field' => 'DeadlineZNO_id',
                    'label' => 'Нарушение сроков',
                    'rules' => '',
                    'type' => 'int'
            ), 
			array(
                    'field' => 'AdminVIPPersonLpu_id',
                    'label' => 'Для просмотра своих пациентов',
                    'rules' => '',
                    'type' => 'int'
            ), 
			array(
                    'field' => 'ObservType_id',
                    'label' => 'Тип наблюдения',
                    'rules' => '',
                    'type' => 'int'
            ), 
            array(
                    'field' => 'DirectType_id',
                    'label' => 'Профиль наблюдения',
                    'rules' => '',
                    'type' => 'id'
            ), 
            array(
                    'field' => 'MorbusType_id',
                    'label' => 'Предмет наблюдения',
                    'rules' => '',
                    'type' => 'id'
            ), 
            array(
                    'field' => 'quest_id',
                    'label' => 'Есть заполненные анкеты',
                    'rules' => '',
                    'type' => 'int'
            ), 
            array(
                    'field' => 'pmUser_docupdID',
                    'label' => 'Пользователь',
                    'rules' => '',
                    'type' => 'int'
            ),                     
                array(
                        'field' => 'EvnDie_IsAnatom',
                        'label' => 'Паталагоанатомическая экспертиза',
                        'rules' => '',
                        'type' => 'int'
                ),
			array(
				'field'	=> 'EvnPLDisp_UslugaComplex',
				'label'	=> 'EvnPLDisp_UslugaComplex',
				'rules'	=> '',
				'type'	=> 'int'
			),
			// нефрология
			array(
				'field'	=> 'NephroCRIType_id',
				'label'	=> 'Стадия ХБП',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field' => 'DialysisCenter_id',
				'label'	=> 'Диализный центр прикрепления',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field' => 'PersonCountAtDate',
				'label' => 'Пациенты, находящиеся в регистре на выбранную дату',
				'rules' => '',
				'type'  => 'date'
			),
			array(
				'field' => 'MorbusNephro_DialDate_Range',
				'label' => 'Дата начала диализа',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'MorbusNephro_DialEndDate_Range',
				'label' => 'Дата окончания диализа',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field'	=> 'NephroPersonStatus_id',
				'label'	=> 'Статус пациента',
				'rules'	=> '',
				'type'	=> 'int'
			),
			// мигранты
			array(
				'field'	=> 'ResultDispMigrant_id',
				'label'	=> 'Результат',
				'rules'	=> '',
				'type'	=> 'id'
			),
			array(
				'field'	=> 'EvnPLDispMigran_SertHIVNumber',
				'label'	=> 'Сертификат ВИЧ - номер',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'EvnPLDispMigran_SertHIVDate',
				'label'	=> 'Сертификат ВИЧ - дата',
				'rules'	=> '',
				'type'	=> 'date'
			),
			array(
				'field'	=> 'EvnPLDispMigran_SertHIVDateRange',
				'label'	=> 'Сертификат ВИЧ - дата',
				'rules'	=> '',
				'type'	=> 'daterange'
			),
			array(
				'field'	=> 'EvnPLDispMigran_SertInfectNumber',
				'label'	=> 'Заключение об инфекционных заболеваниях - номер',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'EvnPLDispMigran_SertInfectDate',
				'label'	=> 'Заключение об инфекционных заболеваниях - дата',
				'rules'	=> '',
				'type'	=> 'date'
			),
			array(
				'field'	=> 'EvnPLDispMigran_SertInfectDateRange',
				'label'	=> 'Заключение об инфекционных заболеваниях - дата',
				'rules'	=> '',
				'type'	=> 'daterange'
			),
			array(
				'field'	=> 'EvnPLDispMigran_SertNarcoNumber',
				'label'	=> 'Заключение о наркомании - номер',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'EvnPLDispMigran_SertNarcoDate',
				'label'	=> 'Заключение о наркомании - дата',
				'rules'	=> '',
				'type'	=> 'date'
			),
			array(
				'field'	=> 'EvnPLDispMigran_SertNarcoDateRange',
				'label'	=> 'Заключение о наркомании - дата',
				'rules'	=> '',
				'type'	=> 'daterange'
			),
			// водители
			array(
				'field'	=> 'ResultDispDriver_id',
				'label'	=> 'Результат',
				'rules'	=> '',
				'type'	=> 'id'
			),
			// Поиск для плана диспансеризации
			array(
				'field'	=> 'DispCheckPeriod_begDate',
				'label'	=> 'Период',
				'rules'	=> '',
				'type'	=> 'date'
			),
			array(
				'field'	=> 'PeriodCap_id',
				'label'	=> 'Тип периода',
				'rules'	=> '',
				'type'	=> 'id'
			),
			array(
				'field'	=> 'Person_isDopDispPassed',
				'label'	=> 'Учесть открытые/закрытые карты в плановом году',
				'rules'	=> '',
				'type'	=> 'checkbox'
			),
			array(
				'field'	=> 'Person_isOftenApplying',
				'label'	=> 'Часто обращающиеся за МП',
				'rules'	=> '',
				'type'	=> 'checkbox'
			),
			array(
				'field'	=> 'Person_isNotApplyingLastYear',
				'label'	=> 'Не обращавшиеся за МП в прошлом году',
				'rules'	=> '',
				'type'	=> 'checkbox'
			),
			array(
				'field'	=> 'Person_isNotApplyingLastYear',
				'label'	=> 'Не обращавшиеся за МП в прошлом году',
				'rules'	=> '',
				'type'	=> 'checkbox'
			),
			array(
				'field'	=> 'Person_isNotDispProf',
				'label'	=> 'Не проходили ПОВН в прошлом году',
				'rules'	=> '',
				'type'	=> 'checkbox'
			),
			array(
				'field'	=> 'Person_isYearlyDispDop',
				'label'	=> 'Подлежащие ежегодному прохождению ДВН',
				'rules'	=> '',
				'type'	=> 'checkbox'
			),
			array(
				'field'	=> 'Person_isNotDispDop',
				'label'	=> 'Не проходили ДВН в прошлом году',
				'rules'	=> '',
				'type'	=> 'checkbox'
			),
			array(
				'field'	=> 'Person_isNotDispDopOnTime',
				'label'	=> 'Не проходившие в установленные сроки',
				'rules'	=> '',
				'type'	=> 'checkbox'
			),
			array(
				'field' => 'EvnPLDisp_setDate_Range',
				'label' => 'Диапазон дат',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'EvnPLDisp_disDate_Range',
				'label' => 'Диапазон дат',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'PersonVisit_Date_Range',
				'label' => 'Диапазон дат',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'MonthsWithoutNefroVisit',
				'label' => 'Месяцы без визита к нефрологу',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'Diab_Diag_Code_From',
				'label' => 'Код диагноза диабета с',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Diab_Diag_Code_To',
				'label' => 'Код диагноза диабета по',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'IsExtra',
				'label' => 'Вид вызова',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'Lpu_ppdid',
				'label' => 'МО передачи (НМП)',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'acceptPPD',
				'label' => 'Подтверждение принятия МО НМП',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_begTime',
				'label' => 'Время с',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'CmpCallCard_endTime',
				'label' => 'Время по',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'PersonSocial_id',
				'label' => 'Социальное положение',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'CmpNumberGod_From',
				'label' => 'Номер вызова за год с',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'CmpNumberGod_To',
				'label' => 'Номер вызова за год по',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'Lpu_hid',
				'label' => 'МО госпитализации',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'isActive',
				'label' => 'Подлежит активному посещению врачом поликлиники',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'CardAddress_Office',
				'label' => 'Квартира ("Адрес вызова")',
				'rules' => 'trim',
				'type' => 'russtring'
			),
			array(
				'field' => 'CardAddress_House',
				'label' => 'Номер дома ("Адрес вызова")',
				'rules' => 'trim',
				'type' => 'russtring'
			),
			array(
				'field' => 'CardAddress_Corpus',
				'label' => 'Корпус ("Адрес вызова")',
				'rules' => 'trim',
				'type' => 'russtring'
			),
			array(
				'field' => 'CardAddress_Street',
				'label' => 'Улица ("Адрес вызова")',
				'rules' => 'trim',
				'type' => 'russtring'
			),
			array(
				'default' => 0,
				'field' => 'CardKLCity_id',
				'label' => 'Город ("Адрес вызова")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'CardKLCountry_id',
				'label' => 'Страна ("Адрес вызова")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'CardKLRgn_id',
				'label' => 'Регион ("Адрес вызова")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'CardKLStreet_id',
				'label' => 'Улица ("Адрес вызова")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'CardKLSubRgn_id',
				'label' => 'Район ("Адрес вызова")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'CardKLTown_id',
				'label' => 'Населенный пункт ("Адрес вызова")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ETMedStaffFact_id',
				'label' => 'Старший бригады',
				'rules' => '',
				'type' => 'id'
			),
			//Регистр РЖД
			array(
				'field' => 'RzhdRegistry_id',
				'label' => 'Идентификатор анкеты',
				'rules'	=> '',
				'type'	=> 'int',
			),
			array(
				'field' => 'RzhdRegistry_PensionBegDate_Range',
				'label' => 'Дата начала пенсии',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'RzhdWorkerCategory_id',
				'label' => 'Категория населения',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'RzhdWorkerGroup_id',
				'label' => 'Группа рабочего',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'RzhdWorkerSubgroup_id',
				'label' => 'Подгруппа рабочего',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'RzhdOrg_id',
				'label' => 'Организация ржд',
				'rules'	=> '',
				'type'	=> 'int',
			),
			array(
				'field'	=> 'UslugaExecutionType_id',
				'label'	=> 'Результат выполнения услуги',
				'rules'	=> '',
				'type'	=> 'int',
			),
			//Регистр
			array(
				'field' => 'RegisterType_id',
				'label' => 'Тип записи регистра',
				'rules' => 'trim',
				'type'  => 'int'
			),
			array(
				'field' => 'Register_setDate_Range',
				'label' => 'Дата включения в регистр',
				'rules' => 'trim',
				'type'  => 'daterange'
			),
			array(
				'field' => 'Register_disDate_Range',
				'label' => 'Дата исключения из регистра',
				'rules' => 'trim',
				'type'  => 'daterange'
			),
			array(
				'field' => 'RegisterDisCause_id',
				'label' => 'Причина исключения из регистра',
				'rules' => '',
				'type'  => 'int'
			),
			//Регистр ВМП
			array(
				'field' => 'LpuSectionProfile_id',
				'label' => 'Профиль',
				'rules' => 'trim',
				'type'  => 'int'
			),
			array(
				'field' => 'HTMedicalCareClass_id',
				'label' => 'Вид ВМП',
				'rules' => 'trim',
				'type'  => 'int'
			),
			array(
				'field' => 'HTMRegister_stage',
				'label' => 'Этап ВМП',
				'rules' => 'trim',
				'type'  => 'int'
			),
			array(
				'field' => 'Diag_id1',
				'label' => 'Диагноз',
				'rules' => 'trim',
				'type'  => 'int'
			),
			array(
				'field' => 'HTMRegister_ApplicationDate_Range',
				'label' => 'Дата обращения пациента в МО',
				'rules' => 'trim',
				'type'  => 'daterange'
			),
			array(
				'field' => 'HTMRegister_DisDate_Range',
				'label' => 'Дата обращения пациента в МО',
				'rules' => 'trim',
				'type'  => 'daterange'
			),
			array(
				'field' => 'HTMRegister_Stage',
				'label' => 'Этап ВМП',
				'rules' => 'trim',
				'type'  => 'int'
			),
			array(
				'field' => 'HTMQueueType_id',
				'label' => 'Очередь',
				'rules' => 'trim',
				'type'  => 'int'
			),
			array(
				'field' => 'isSetPlannedHospDate',
				'label' => 'Планируемая дата госпитализации заполнено',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'HTMRegister_IsSigned',
				'label' => 'Подписание',
				'rules' => 'trim',
				'type'  => 'int'
			),
			array(
				'field' => 'HTMRegister_OperDate_Range',
				'label' => 'Дата проведения оперативного вмешательства',
				'rules' => 'trim',
				'type'  => 'daterange'
			),
			array(
				'field' => 'HTMResult_id',
				'label' => 'Результат оказания ВМП',
				'rules' => 'trim',
				'type'  => 'int'
			),
			//реанимация   //BOB - 25.10.2017
			array(
				'field' => 'HardOnly',
				'label' => 'Фильтр Только тяжёлые',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'ReanimatRegister_IsPeriodNow',
				'label' => 'Фильтр В реанимации сечас',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'RRW_BeginDate',
				'label' => 'Дата начала период нахождения в реанимации',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'RRW_EndDate',
				'label' => 'Дата окончания период нахождения в реанимации',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field'	=> 'EvnScaleType',
				'label'	=> 'Тип Шкалы исследования состояния',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field' => 'EvnScaleFrom',
				'label' => 'результат исследования по шкале - от',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'EvnScaleTo',
				'label' => 'результат исследования по шкале - до',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field'	=> 'ReanimatActionType',
				'label'	=> 'Вид реанимационного мероприятия',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'RA_DrugNames',
				'label'	=> 'Использованный медикамент',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'ReanimatLpu',
				'label'	=> 'МО госпитализации',
				'rules'	=> '',
				'type'	=> 'int'
			),
			//BOB - 25.10.2017
			array ('field' => 'Hospitalization_id', 'label' => 'Передано в БГ', 'rules' => '', 'type' => 'int'),
			array(
				'field'	=> 'toERSB',
				'label'	=> 'Передано в ЭРСБ',
				'rules'	=> '',
				'type'	=> 'id'
			),
			// используется в поиске ТАП и пар.услуг
			array(
				'field'	=> 'toAis25',
				'label'	=> 'Случай передан в АИС-Пол-ка (25-5у)',
				'rules'	=> '',
				'type'	=> 'id'
			),
			array(
				'field'	=> 'toAis259',
				'label'	=> 'Случай передан в АИС-Пол-ка (25-9у)',
				'rules'	=> '',
				'type'	=> 'id'
			),
                        //используется в сигнальной информации
                        array(
				'field' => 'SignalInfo',
				'label' => 'Сигнальная информация',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field'	=> 'ONMKRegistry_Status',
				'label'	=> 'Статус записи ОНМК',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'ONMKRegistry_ISTLT',
				'label'	=> 'Статус ТЛТ',
				'rules'	=> '',
				'type'	=> 'int'
			),			
			array(
				'field'	=> 'ONMKRegistry_TypeMO',
				'label'	=> 'Тип МО госпитализации',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'ONMKRegistry_Evn_DTDesease',
				'label'	=> 'Дата начала заболевания',
				'rules'	=> 'trim',
				'type'	=> 'daterange'
			),
			array(
				'field'	=> 'ONMKRegistry_ResultDesease',
				'label'	=> 'Исход заболевания',
				'rules'	=> '',
				'type'	=> 'int'
			),						
			// ЭРС
			array(
				'field' => 'EvnERSBirthCertificate_Number',
				'label' => 'Номер ЭРС',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'EvnERSBirthCertificate_CreateDate_Range',
				'label' => 'Дата формирования ЭРС',
				'rules' => '',
				'type' => 'daterange'
			),
			array(
				'field' => 'ERSStatus_id',
				'label' => 'Статус ЭРС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ERSRequestType_id',
				'label' => 'Тип запроса',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ERSRequestStatus_id',
				'label' => 'Статус запроса',
				'rules' => '',
				'type' => 'id'
			),
			// Регистр спортсменов
			array(
				'field' => 'SportType_id',
				'label' => 'Идентификатор вида спорта',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'SportStage_id',
				'label' => 'Идентификатор этапа подготовки',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'SportCategory_id',
				'label' => 'Идентификатор спротивного разряда',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'SportOrg_id',
				'label' => 'Идентификатор спортивной организации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UMOResult_id',
				'label' => 'Идентификатор заключения врача',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_pid',
				'label' => 'Идентификатор врача',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'SportTrainer_id',
				'label' => 'Идентификатор тренера',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'IsTeamMember_id',
				'label' => 'Признак спортсмена сборника',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'InvalidGroupType_id',
				'label' => 'Идентификатор группы инвалидности',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'SportParaGroup_id',
				'label' => 'Идентификатор паралимпической группы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'SportRegisterUMO_UMODate',
				'label' => 'Дата проведения УМО',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'SportRegisterUMO_AdmissionDtBeg',
				'label' => 'Дата начала допуска',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'SportRegisterUMO_AdmissionDtEnd',
				'label' => 'Дата окончания допуска',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'SportRegisterType_id',
				'label' => 'Тип поиска',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LPU_sid',
				'label' => 'РСЦ/ПСО/МО госп-ии',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EUPSWLpu_id',
				'label' => 'МО',
				'rules' => '',
				'type' => 'id'
			)
		)
	);

	protected $inputData = array();

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		// Получаем сессионные переменные
		$this->inputData = array_merge($this->inputData, getSessionParams());

		// Третий параметр в getInputParams зависит от вызываемого метода
		// При печати списка найденных записей (метод printSearchResults) конвертация из UTF-8 не требуется
		$fromUtf = ($this->router->method != 'printSearchResults');
		
		$err = getInputParams($this->inputData, $this->inputRules['searchData'], $fromUtf);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		/**
		 *	@url https://redmine.swan.perm.ru/issues/3936
		 *
		 *	Поиск учетных документов
		 *	При заполнении следующих полей в форме поиска производить поиск по основной базе (всегда только если по текущему состоянию):
		 *	Фамилия <или>
		 *	Номер амбулаторной карты <или>
		 *	Номер учетного документа (№ ТАП, № КВС, Серия+номер рецепта)
		 *	Серия, номер полиса <или>
		 *	Единый номер застрахованного <или>
		 *	СНИЛС <или>
		 *	Серия, номер документа, удостоверяющего личность.
		 */

		if (empty($this->inputData['SearchFormType'])) {
			$this->ReturnError('Ошибка. Не указан тип поиска.');
			return false;
		}

		switch ( $this->inputData['SearchFormType'] ) {
			case 'EvnPL':
			case 'EvnPS':
			case 'CmpCallCard':
			case 'CmpCloseCard':
			case 'EvnDtpWound':
			case 'EvnUslugaPar':
				$err = getInputParams($this->inputData, $this->inputRules[$this->inputData['SearchFormType']], false);

				if ( strlen($err) > 0 ) {
					echo json_return_errors($err);
					return false;
				}
			break;
			case 'EvnVizitPL':
			case 'EvnPLStom':
			case 'EvnVizitPLStom':
				$err = getInputParams($this->inputData, $this->inputRules['EvnPL'], false);

				if ( strlen($err) > 0 ) {
					echo json_return_errors($err);
					return false;
				}
			break;
			case 'EvnSection':
				$err = getInputParams($this->inputData, $this->inputRules['EvnPS'], false);

				if ( strlen($err) > 0 ) {
					echo json_return_errors($err);
					return false;
				}
			break;
			case '': case null: // Если тип формы поиска не задан, то возвращаем пустой набор данных (раньше было сообщение об обязательности поля)
				$val = array();
				$val['data']= array();
				$val['totalCount'] = 0;
				$this->ReturnData($val);
				return false;
			break;
		}
		$bdSearchRegistry = false;
		switch(true){
			case !empty($this->inputData['EvnVizitPL_isPaid']):
			case !empty($this->inputData['EvnPLDispOrp_isPaid']):
			case !empty($this->inputData['EvnPLDispProf_isPaid']):
			case !empty($this->inputData['EvnPLDispScreen_isPaid']):
			case !empty($this->inputData['EvnPLDispScreenChild_isPaid']):
			case !empty($this->inputData['EvnPLDispDop13Second_isPaid']):
			case !empty($this->inputData['EvnPLDispDop13_isPaid']):
			case !empty($this->inputData['EvnVizitPLStom_isPaid']):
			case !empty($this->inputData['CmpCallCard_isPaid']):
			case !empty($this->inputData['EvnSection_isPaid']):
			case !empty($this->inputData['EvnPLDispTeenInspection_isPaid']):
				$bdSearchRegistry = true;
				break;
		}

        //определяем какую базу использовать для поиска
		$archive_database_enable = $this->config->item('archive_database_enable');
		$search_database_only = $this->config->item('search_database_only');
        $database_type = null;

		// если по архивным - используем архивную БД
		if (!empty($archive_database_enable) && !empty($_REQUEST['useArchive'])) {
			$database_type = 'archive';
		} else if($bdSearchRegistry && (!defined('ENABLE_REGISTRY_DB_SEARCH') || ENABLE_REGISTRY_DB_SEARCH === true)){
			$database_type = 'registry';
		} else {
			if ( 
				(
					// https://redmine.swan.perm.ru/issues/28999
					in_array($this->inputData['SearchFormType'], array('HepatitisRegistry', 'OnkoRegistry', 'GeriatricsRegistry', 'NarkoRegistry', 'TubRegistry', 'DiabetesRegistry', 'GibtRegistry', 'EvnERSBirthCertificate',
						'OrphanRegistry', 'CrazyRegistry', 'VenerRegistry', 'HIVRegistry','ACSRegistry','FmbaRegistry', 'EvnInfectNotify', 'EvnNotifyHepatitis', 'EvnOnkoNotify',
						'EvnNotifyOrphan', 'EvnNotifyCrazy', 'EvnNotifyTub', 'EvnNotifyNarko', 'EvnNotifyVener','EvnNotifyNephro', 'EvnNotifyProf', 'PalliatNotify', 'IBSRegistry', 'ProfRegistry', 'PalliatRegistry', 'NephroRegistry', 'EndoRegistry',
						'EvnNotifyRegister', 'PersonRegisterBase','AdminVIPPerson','ZNOSuspectRegistry','PersonRegister60Plus','ReabRegistry','BskRegistry','IPRARegistry', 'EvnUslugaPar', 'PersonDopDispPlan', 'EvnPLDispMigrant','ONMKRegistry', 'SportRegistry',
						'RzhdRegistry','ReanimatRegistry'
						)
					)
					|| (
						// Только для поиска ТАП, посещений, КВС и рецептов, и параклинических услуг (#6439) и ДД и ДД ДС (#6768)
						in_array($this->inputData['SearchFormType'], array('PersonCallCenter', 'EvnPL', 'EvnVizitPL', 'EvnPLStom', 'EvnVizitPLStom', 'EvnPS', 'EvnSection',
							'EvnRecept', 'EvnReceptGeneral', 'EvnPLDispDop', 'EvnPLDispDop13', 'EvnPLDispDop13Sec', 'EvnPLDispProf', 'EvnPLDispScreen', 'EvnPLDispScreenChild', 'EvnPLDispOrp', 'EvnPLDispOrpOld', 'EvnPLDispOrpSec',
							'EvnPLDispTeenInspectionPeriod', 'EvnPLDispTeenInspectionProf', 'EvnPLDispTeenInspectionPred', 'PersonDopDisp', 'PersonDispOrp',
							'PersonDispOrpPeriod', 'PersonDispOrpProf', 'PersonDispOrpPred', 'PersonDispOrpOld', 'PersonPrivilege', 'PersonCardStateDetail', 'PersonDisp')
						)
						// Только если поиск по активной периодике
						&& $this->inputData['PersonPeriodicType_id'] == 1
						&& (!empty($this->inputData['Person_Surname']) || !empty($this->inputData['PersonCard_Code']) ||
							!empty($this->inputData['EvnPS_NumCard']) || !empty($this->inputData['EvnPL_NumCard']) ||
							(!empty($this->inputData['EvnRecept_Ser']) && !empty($this->inputData['EvnRecept_Num'])) ||
							(!empty($this->inputData['Polis_Ser']) && !empty($this->inputData['Polis_Num'])) ||
							!empty($this->inputData['Person_Code']) || !empty($this->inputData['Person_Snils']) ||
							(!empty($this->inputData['Document_Ser']) && !empty($this->inputData['Document_Num'])) ||
							(!empty($this->inputData['LpuSection_cid']) && !empty($this->inputData['EvnSection_disDate_Range'])) // журнал выбывших (refs #26313)
						)
					)
					// если это форма поточного ввода в регистр по ДД
					|| ($this->inputData['SearchFormType'] == 'PersonDopDisp' && isset($this->inputData['dop_disp_reg_beg_date']))
					|| ( //Или АРМ регистратора поликлиники
						$this->inputData['SearchFormType'] == 'WorkPlacePolkaReg' && (
							!empty($this->inputData['Person_Surname']) || !empty($this->inputData['Person_Firname']) || !empty($this->inputData['Person_Secname']) ||
							isset($this->inputData['Person_Birthday']) || !empty($this->inputData['PersonCard_Code']) || !empty($this->inputData['Polis_Num'])
						)
					)
					|| (  //или ЭКО регистра (#145370)
							$this->inputData['SearchFormType'] == 'ECORegistry'
							&& ((!empty($this->inputData['Person_Surname']) || !empty($this->inputData['Person_Firname']) || !empty($this->inputData['Person_Secname'])))
						)
					// https://redmine.swan.perm.ru/issues/17135
					// Добавил CmpCloseCard
					// @task https://redmine.swan.perm.ru/issues/107112
					|| (in_array($this->inputData['SearchFormType'], array('CmpCallCard','CmpCloseCard')) && !empty($this->inputData['CmpCallCard_InRegistry']))
				)
			) {
				if ($search_database_only) {
					// Всегда используем базу поиска 
                    $database_type = 'search';
				} else {
					// Используем базу по умолчанию
                    $database_type = 'default';
				}
			} else { // Используем специально обученную базу для поиска
                $database_type = 'search';
			}
		}

        // при поиске рецептов в АРМ провизора, всегда используем базу по умолчанию
        // https://redmine.swan.perm.ru/issues/110630
        if ($this->inputData['SearchFormType'] == 'EvnRecept' && !empty($this->inputData['DistributionPoint'])) {
            $database_type = 'default';
        }

        // подключаем базу
        if(!empty($database_type)) {
            if ($database_type != 'default') {
                $this->load->database($database_type, false);
            } else {
                $this->load->database(); // используем базу по умолчанию
            }
        }
		
		//Выставляем таймауты для выполнения запросов, пока вручную
		$this->db->query_timeout = 600;

		$this->load->model('Search_model', 'dbmodel');
	}

	/**
	 * Зачем?
	 */
	function Index() {
		return false;
	}

	/**
	 * в Dbf
	 */
	function InDbf($file_name, $response, $def)
	{
		$cnt = $response['totalCount'];
		$h = dbase_create($file_name, $def);
		$i = 0;
		while ( $i < $cnt )
		{
			$row = $response['data']->row_array($i);

			if ( array_key_exists('archiveRecord', $row) ) {
				unset($row['archiveRecord']);
			}

			// определяем которые даты и конвертируем их
			foreach ( $def as $descr )
			{
				if ( $descr[1] == "D" ) {
					if ( !empty($row[$descr[0]]) ) {
						$row[$descr[0]] = date("Ymd",strtotime($row[$descr[0]]));
					}
					else {
						$row[$descr[0]] = '';
					}
				}
			}
			array_walk($row, 'ConvertFromUtf8ToCp866');
			//var_dump($def);die;
			@dbase_add_record( $h, array_values($row) );
			$i++;			
		}
		dbase_close ($h);
	}
	
	/**
	*  Экспорт в дбф
	*  На выходе: JSON-строка
	*  Используется: форма поиска ТАП (SearchFormType = EvnPL)
	*/
	function exportSearchResultsToDbf() {
		if (empty($this->dbmodel)) { return false; } // не прошли конструктор
		
		$val  = array();

		switch ( $this->inputData['SearchFormType'] ) {
			case 'EvnVizitPL':
			case 'EvnPL':
				if ($this->inputData['session']['region']['nick'] != 'kareliya')
				{
					set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится

					log_message('debug', 'начало выгрузки '.$this->inputData['SearchFormType']);

					// Подготовительные операции 
					$out_dir = 'pl'.time();
					if ( !file_exists(EXPORTPATH_PL) ) {
						mkdir( EXPORTPATH_PL );
					}
					mkdir( EXPORTPATH_PL.$out_dir );

					$file_EvnPL_sign = 'EvnPL';
					$file_EvnPL_name = EXPORTPATH_PL.$out_dir."/".$file_EvnPL_sign.".dbf";

					$file_EvnVizitPL_sign = 'EvnVizitPL';
					$file_EvnVizitPL_name = EXPORTPATH_PL.$out_dir."/".$file_EvnVizitPL_sign.".dbf";

					$file_EvnUsluga_sign = 'EvnUsluga';
					$file_EvnUsluga_name = EXPORTPATH_PL.$out_dir."/".$file_EvnUsluga_sign.".dbf";

					$file_EvnAgg_sign = 'EvnAgg';
					$file_EvnAgg_name = EXPORTPATH_PL.$out_dir."/".$file_EvnAgg_sign.".dbf";

					$file_zip_sign = 'pl';
					$file_zip_name = EXPORTPATH_PL.$out_dir."/".$file_zip_sign.".zip";
					
					log_message('debug', 'создали папку для выгрузки: '.$out_dir);
					
					$evn_pl_def = array(
						array("EPL_ID",		"N",	18, 0),
						array("DIR_CODE",	"N",	2, 0),
						array("PDO_CODE",	"C",	10),
						array("SETDATE",	"D",	8),
						array("SETTIME",	"C",	5),
						array("DISDATE",	"D",	8),
						array("DISTIME",	"C",	5),
						array("NUMCARD",	"C",	10),
						array("VPERV",		"N",	1, 0),
						array("KATEGOR",	"N",	1, 0),
						array("OGRN",		"C",	15),
						array("SURNAME",	"C",	20),
						array("FIRNAME",	"C",	20),
						array("SECNAME",	"C",	20),
						array("BIRTHDAY",	"D",	8),
						array("POL_COD",	"N",	1, 0),
						array("SOC_COD",	"C",	3),
						array("KOD_TER",	"C",	17),
						array("SNILS",		"C",	14),
						array("FINISH_ID",	"N",	1, 0),
						array("RSC_COD",	"N",	2, 0),
						array("DZ_COD",		"C",	5, 0),
						array("DZ_NAM",		"C",	200),
						array("DST_COD",	"N",	10, 0),
						array("UKL",		"N",	3, 2),
						array("CODE_NAP",	"N",	1, 0),
						array("KUDA_NAP",	"C",	10),
						array("INVALID",	"N",	1, 0),
						array("REG_PERM",	"N",	1, 0),
						array("BDZ",	"C",	3)
					);

					$evn_vizit_pl_def = array(
						array( "EPL_ID", "N",18, 0 ),
						array( "PCT_ID", "N",18, 0 ),
                        array( "EVZ_ID", "N",18, 0 ),
						array( "NUMCARD", "C",10 , 0 ),
						array( "SETDATE", "D",8 , 0 ),
						array( "SETTIME", "C",5 , 0 ),
						array( "PERVVTOR", "N", 1, 0),
						array( "LS_COD", "N",9, 0 ),
						array( "LS_NAM", "C",50 , 0 ),
						array( "MP_COD", "C",20 , 0 ),
						array( "MP_FIO", "C",100 , 0 ),
						array( "PAY_COD", "N",10 , 0 ),
						array( "VZT_COD", "N",10 , 0 ),
						array( "SRT_COD", "N",10 , 0 ),
						array( "PRG_COD", "N",2 , 0 ),
						array( "DZ_COD", "C",5 , 0 ),
						array( "DZ_NAM", "C",200 , 0 ),
						array( "DST_COD", "N",10 , 0 )
					);

					$evn_usluga_def = array(
						array("EPL_ID",		"N",	18, 0), // EvnPL_id
						array("EVZ_ID",		"N",	18, 0), // EvnVizitPL_id
						array("EUS_ID",		"N",	18, 0), // EvnUsluga_id
						array("EU_CLASS",	"C",	20), // EvnClass_SysNick
						array("SETDATE",	"D",	8), // EvnUsluga_setDate
						array("SETTIME",	"C",	5), // EvnUsluga_setTime
						array("USL_CODE",	"C",	20), // Usluga_Code
						array("KOLVO",		"N",	3, 3), // EvnUsluga_Kolvo
						array("UP_CODE",	"N",	1,	0), // UslugaPlace_Code
						array("MP_CODE",	"C",	5), // MedPersonal_Code
						array("PT_CODE",	"N",	1, 0) // PayType_Code
					);

					$evn_agg_def = array(
						array("EUS_ID",		"N",	18, 0), // EvnUsluga_id
						array("PCT_ID",     "N",	18, 0),
                        array("EAGG_ID",	"N",	18, 0), // EvnAgg_id
						array("SETDATE",	"D",	8), // EvnAgg_setDate
						array("SETTIME",	"C",	5), // EvnAgg_setTime
						array("AW_CODE",	"N",	1,	0), // AggWhen_Code
						array("AT_CODE",	"N",	1,	0) // AggType_Code
					);
					log_message('debug', 'предварительно подготовили массивы ');

					$this->inputData['SearchFormType'] = 'EvnPL';
					// Талоны 
					$response = $this->dbmodel->searchData($this->inputData, false, false, true);
				
					log_message('debug', 'выполнили SQL '.$this->inputData['SearchFormType']);
					
					if ( $response === false ) {
	                    $this->ReturnError('Ошибка: обрыв коннекта. Повторите попытку.');
						return false;
					}
					else if ( is_array($response) && isset($response['Error_Msg']) && strlen($response['Error_Msg']) > 0 ) {
	                    $this->ReturnError($response['Error_Msg']);
	                    return false;
					}
					else if ( $response['totalCount'] == 0 ) {
	                    $this->ReturnError('В БД нет данных, удовлетворяющих условиям поиска.');
						return false;
					}
					else if ( $response['totalCount'] > 50000 ) {
	                    $this->ReturnError('Выгрузка невозможна из-за большого количества записей.<br />Используйте фильтры для ограничения количества выгружаемых записей');
	                    return false;
					}
					$this->InDbf($file_EvnPL_name, $response, $evn_pl_def);
					log_message('debug', 'сформировали DBF '.$file_EvnPL_name);
					
					// Посещения 
					$this->inputData['SearchFormType'] = 'EvnVizitPL';
					$response = $this->dbmodel->searchData($this->inputData, false, false, true);
					log_message('debug', 'выполнили SQL '.$this->inputData['SearchFormType']);
					
					if ( $response === false ) {
	                    $this->ReturnError('Ошибка: обрыв коннекта. Повторите попытку.');
						return false;
					}
					else if ( is_array($response) && isset($response['Error_Msg']) && strlen($response['Error_Msg']) > 0 ) {
	                    $this->ReturnError($response['Error_Msg']);
	                    return false;
					}
					
					$this->InDbf($file_EvnVizitPL_name, $response, $evn_vizit_pl_def);
					log_message('debug', 'сформировали DBF '.$file_EvnVizitPL_name);
					
					$this->inputData['SearchFormType'] = 'EvnUsluga';
					$response = $this->dbmodel->searchData($this->inputData, false, false, true);
					log_message('debug', 'выполнили SQL '.$this->inputData['SearchFormType']);
					if ( $response === false ) {
	                    $this->ReturnError('Ошибка: обрыв коннекта. Повторите попытку.');
	                    return false;
					}
					else if ( is_array($response) && isset($response['Error_Msg']) && strlen($response['Error_Msg']) > 0 ) {
	                    $this->ReturnError($response['Error_Msg']);
	                    return false;
					}
					
					$this->InDbf($file_EvnUsluga_name, $response, $evn_usluga_def);
					log_message('debug', 'сформировали DBF '.$file_EvnUsluga_name);
					
					$this->inputData['SearchFormType'] = 'EvnAgg';
					$response = $this->dbmodel->searchData($this->inputData, false, false, true);
					log_message('debug', 'выполнили SQL '.$this->inputData['SearchFormType']);
					if ( $response === false ) {
	                    $this->ReturnError('Ошибка: обрыв коннекта. Повторите попытку.');
	                    return false;
					}
					else if ( is_array($response) && isset($response['Error_Msg']) && strlen($response['Error_Msg']) > 0 ) {
	                    $this->ReturnError($response['Error_Msg']);
	                    return false;
					}
					$this->InDbf($file_EvnAgg_name, $response, $evn_agg_def);
					log_message('debug', 'сформировали DBF '.$file_EvnAgg_name);
					unset($response);
					
					log_message('debug', 'формируем ZIP');
					$zip = new ZipArchive();
					$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
					$zip->AddFile( $file_EvnPL_name, "EvnPL.dbf" );
					$zip->AddFile( $file_EvnVizitPL_name, "EvnVizitPL.dbf" );
					$zip->AddFile( $file_EvnUsluga_name, "EvnUsluga.dbf" );
					$zip->AddFile( $file_EvnAgg_name, "EvnAgg.dbf" );
					$zip->AddFile( 'documents/EvnPL structure_other.xlsx',"EvnPL structure.xlsx");
					$zip->close();
					unlink($file_EvnPL_name);
					unlink($file_EvnVizitPL_name);
					unlink($file_EvnUsluga_name);
					unlink($file_EvnAgg_name);
					log_message('debug', 'сформировали ZIP '.$file_zip_name);
					
					$val = array("success" => true, "url" => "/" . $file_zip_name );
				}
				else
				{
					set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится

					log_message('debug', 'начало выгрузки '.$this->inputData['SearchFormType']);

					// Подготовительные операции 
					$out_dir = 'pl'.time();
					if ( !file_exists(EXPORTPATH_PL) ) {
						mkdir( EXPORTPATH_PL );
					}
					mkdir( EXPORTPATH_PL.$out_dir );
					$file_zip_sign = 'pl';
					$file_zip_name = EXPORTPATH_PL.$out_dir."/".$file_zip_sign.".zip";
					
					log_message('debug', 'создали папку для выгрузки: '.$out_dir);

					$object_array = array(
						'EPLPerson'		=> array('file_sign' => 'PACIENT'),
						'EvnPL'			=> array('file_sign' => 'EvnPL'),
						'EvnVizitPL'	=> array('file_sign' => 'EvnVizitPL'),
						'EvnUsluga'		=> array('file_sign' => 'EvnUsluga'),
						'EvnAgg'		=> array('file_sign' => 'EvnAgg')
					);

					$table_list = array();

					$this->inputData['and_eplperson'] = false;
					if ($_POST['table_list'] && $_POST['table_list'] != '') {
						$table_list = preg_split('/[,]/', $_POST['table_list']);
						if (in_array('ADD_PERSON', $table_list))
							$this->inputData['and_eplperson'] = true;
					}
					
					$this->inputData['epl_date_type'] = 1;
					if ($_POST['date_type'] && $_POST['date_type'] != '') {
						$this->inputData['epl_date_type'] = $_POST['date_type'];
					}

					foreach ($object_array as $object_name => $object) {
						$object_array[$object_name]['file_name'] = EXPORTPATH_PL.$out_dir."/".$object['file_sign'].".dbf";
						$exp = in_array($object['file_sign'], $table_list);					
						$object_array[$object_name]['export'] = $exp;
					}

					$fields_array = array(
						'EPLPerson' => array(
							array("PCT_ID", "N", 15, 8),
							array("P_ID", "N", 15, 8),
							array("SURNAME", "C", 50),
							array("FIRNAME", "C", 50),
							array("SECNAME", "C", 50),
							array("BIRTHDAY", "D", 8),
							array("SNILS", "C", 11),
							array("INV_N", "N", 1, 0),
							array("INV_DZ", "C", 5),
							array("INV_DATA", "D", 8),
							array("SEX", "C", 15),
							array("SOC", "C", 30),
							array("P_TERK", "C", 10),
							array("P_TER", "C", 30),
							array("P_NAME", "C", 10),
							array("P_SER", "C", 10),
							array("P_NUM", "C", 30),
							array("P_NUMED", "C", 30),
							array("P_DATA", "D", 8),
							array("SMOK", "C", 10),
							array("SMO", "C", 100),
							array("AR_TP", "C", 20),
							array("AR_IDX", "C", 10),
							array("AR_LND", "C", 50),
							array("AR_RGN", "C", 50),
							array("AR_RN", "C", 50),
							array("AR_CTY", "C", 50),
							array("AR_NP", "C", 50),
							array("AR_STR", "C", 50),
							array("AR_DOM", "C", 5),
							array("AR_K", "C", 5),
							array("AR_KV", "C", 5),
							array("AP_TP", "C", 20),
							array("AP_IDX", "C", 10),
							array("AP_LND", "C", 50),
							array("AP_RGN", "C", 50),
							array("AP_RN", "C", 50),
							array("AP_CTY", "C", 50),
							array("AP_NP", "C", 50),
							array("AP_STR", "C", 50),
							array("AP_DOM", "C", 5),
							array("AP_K", "C", 5),
							array("AP_KV", "C", 5),
							array("D_TIP", "C", 60),
							array("D_SER", "C", 10),
							array("D_NOM", "C", 30),
							array("D_OUT", "C", 100),
							array("D_DATA", "D", 8)
						),
						'EvnPL' => array(
							array("EPL_ID",		"N",	18, 0),
							array("PCT_ID",		"N",	18, 0),
							array("DIR_CODE",	"N",	2, 0),
							array("PDO_CODE",	"C",	10),
							array("SETDATE",	"D",	8),
							array("SETTIME",	"C",	5),
							array("DISDATE",	"D",	8),
							array("DISTIME",	"C",	5),
							array("NUMCARD",	"C",	10),
							array("VPERV",		"N",	1, 0),
							array("KATEGOR",	"N",	1, 0),
							array("OGRN",		"C",	15),
							array("FINISH_ID",	"N",	1, 0),
							array("RSC_COD",	"N",	2, 0),
							array("DZ_COD",		"C",	5, 0),
							array("DZ_NAM",		"C",	200),
							array("DST_COD",	"N",	10, 0),
							array("UKL",		"N",	3, 2),
							array("CODE_NAP",	"N",	1, 0),
							array("KUDA_NAP",	"C",	10),
							array("INVALID",	"N",	1, 0),
							array("REG_PERM",	"N",	1, 0),
							array("BDZ",	"C",	3)
						),
						'EvnVizitPL' => array(
							array( "EPL_ID", "N",18, 0 ),
							array("PCT_ID",		"N",	18, 0),
							array( "EVZ_ID", "N",18, 0 ),
							array( "NUMCARD", "C",10 , 0 ),
							array( "SETDATE", "D",8 , 0 ),
							array( "SETTIME", "C",5 , 0 ),
							array( "PERVVTOR", "N", 1, 0),
							array( "LS_COD", "N",9, 0 ),
							array( "LS_NAM", "C",50 , 0 ),
							array( "MP_COD", "C",20 , 0 ),
							array( "MP_FIO", "C",100 , 0 ),
							array( "PAY_COD", "N",10 , 0 ),
							array( "VZT_COD", "N",10 , 0 ),
							array( "SRT_COD", "N",10 , 0 ),
							array( "PRG_COD", "N",2 , 0 ),
							array( "DZ_COD", "C",5 , 0 ),
							array( "DZ_NAM", "C",200 , 0 ),
							array( "DST_COD", "N",10 , 0 )
						),
						'EvnUsluga' => array(
							array("EPL_ID",		"N",	18, 0), // EvnPL_id
							array("PCT_ID",		"N",	18, 0),
							array("EVZ_ID",		"N",	18, 0), // EvnVizitPL_id
							array("EUS_ID",		"N",	18, 0), // EvnUsluga_id
							array("EU_CLASS",	"C",	20), // EvnClass_SysNick
							array("SETDATE",	"D",	8), // EvnUsluga_setDate
							array("SETTIME",	"C",	5), // EvnUsluga_setTime
							array("USL_CODE",	"C",	20), // Usluga_Code
							array("KOLVO",		"N",	3, 3), // EvnUsluga_Kolvo
							array("UP_CODE",	"N",	1,	0), // UslugaPlace_Code
							array("MP_CODE",	"C",	5), // MedPersonal_Code
							array("PT_CODE",	"N",	1, 0) // PayType_Code
						),
						'EvnAgg' => array(
							array("EUS_ID",		"N",	18, 0), // EvnUsluga_id
							array("PCT_ID",		"N",	18, 0),
							array("EAGG_ID",	"N",	18, 0), // EvnAgg_id
							array("SETDATE",	"D",	8), // EvnAgg_setDate
							array("SETTIME",	"C",	5), // EvnAgg_setTime
							array("AW_CODE",	"N",	1,	0), // AggWhen_Code
							array("AT_CODE",	"N",	1,	0) // AggType_Code
						)
					);
					log_message('debug', 'предварительно подготовили массивы ');

					$this->inputData['SearchFormType'] = 'EvnPL';

					foreach ($object_array as $object_name => $object) if ($object['export'] || $object_name == 'EvnPL') { //при любом наборе таблиц обязательно выгружаем EvnPL, для оценки объема выгружаемых данных
						$this->inputData['SearchFormType'] = $object_name;					
						$f_array = $fields_array[$object_name];
						if ($this->inputData['and_eplperson']) {
							$tmp_array = $fields_array['EPLPerson'];
							for ($i = 0; $i < count($f_array); $i++) if($f_array[$i][0] != 'PCT_ID' && $f_array[$i][0] != 'P_ID' ) $tmp_array[] = $f_array[$i];
							$f_array = $tmp_array;
						}
						$response = $this->dbmodel->searchData($this->inputData, false, false, true);
						log_message('debug', 'выполнили SQL '.$this->inputData['SearchFormType']);					
						if ( $response === false ) {
	                        $this->ReturnError('Ошибка: обрыв коннекта. Повторите попытку.');
							return false;
						}
						else if ( is_array($response) && isset($response['Error_Msg']) && strlen($response['Error_Msg']) > 0 ) {
	                        $this->ReturnError($response['Error_Msg']);
							return false;
						}
						else if ($object_name == 'EvnPL' && $response['totalCount'] == 0) {//только для ТАПов
	                        $this->ReturnError('В БД нет данных, удовлетворяющих условиям поиска.');
							return false;
						}
						else if ($object_name == 'EvnPL' && $response['totalCount'] > 50000) {//только для квс
	                        $this->ReturnError('Выгрузка невозможна из-за большого количества записей.<br />Используйте фильтры для ограничения количества выгружаемых записей');
							return false;
						}					
						//var_dump($f_array);die;
						$this->InDbf($object['file_name'], $response, $f_array);
						log_message('debug', 'сформировали DBF '.$object['file_name']);
					}

					log_message('debug', 'формируем ZIP');
					$zip = new ZipArchive();
					$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
					
					foreach ($object_array as $object_name => $object) if ($object['export'])
						$zip->AddFile($object['file_name'], $object['file_sign'].".dbf");
					
					$zip->close();

					foreach ($object_array as $object_name => $object) if ($object['export'])
						unlink($object['file_name']);

					log_message('debug', 'сформировали ZIP '.$file_zip_name);
					
					$val = array("success" => true, "url" => "/" . $file_zip_name );
				}
			break;

			case 'EvnVizitPLStom':
			case 'EvnPLStom':
				set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится

				log_message('debug', 'начало выгрузки '.$this->inputData['SearchFormType']);

				// Подготовительные операции 
				$out_dir = 'plstom'.time();
				if ( !file_exists(EXPORTPATH_PL) ) {
					mkdir( EXPORTPATH_PL );
				}
				mkdir( EXPORTPATH_PL.$out_dir );
				
				$file_zip_sign = 'plstom';
				$file_zip_name = EXPORTPATH_PL.$out_dir."/".$file_zip_sign.".zip";
				
				log_message('debug', 'создали папку для выгрузки: '.$out_dir);

				$object_array = array(
					'EPLStomPerson'		=> array('file_sign' => 'PACIENT'),
					'EvnPLStom'			=> array('file_sign' => 'EvnPL'),
					'EvnVizitPLStom'	=> array('file_sign' => 'EvnVizitPL'),
					'EvnUslugaStom'		=> array('file_sign' => 'EvnUsluga'),
					'EvnAggStom'		=> array('file_sign' => 'EvnAgg')
				);

				$table_list = array();

				$this->inputData['and_eplstomperson'] = false;
				if ($_POST['table_list'] && $_POST['table_list'] != '') {
					$table_list = preg_split('/[,]/', $_POST['table_list']);
					if (in_array('ADD_PERSON', $table_list))
						$this->inputData['and_eplstomperson'] = true;
				}
				
				$this->inputData['eplstom_date_type'] = 1;
				if ($_POST['date_type'] && $_POST['date_type'] != '') {
					$this->inputData['eplstom_date_type'] = $_POST['date_type'];
				}

				foreach ($object_array as $object_name => $object) {
					$object_array[$object_name]['file_name'] = EXPORTPATH_PL.$out_dir."/".$object['file_sign'].".dbf";
					$exp = in_array($object['file_sign'], $table_list);					
					$object_array[$object_name]['export'] = $exp;
				}

				$fields_array = array(
					'EPLStomPerson' => array(
						array("PCT_ID", "N", 15, 8),
						array("P_ID", "N", 15, 8),
						array("SURNAME", "C", 50),
						array("FIRNAME", "C", 50),
						array("SECNAME", "C", 50),
						array("BIRTHDAY", "D", 8),
						array("SNILS", "C", 11),
						array("INV_N", "N", 1, 0),
						array("INV_DZ", "C", 5),
						array("INV_DATA", "D", 8),
						array("SEX", "C", 15),
						array("SOC", "C", 30),
						array("P_TERK", "C", 10),
						array("P_TER", "C", 30),
						array("P_NAME", "C", 10),
						array("P_SER", "C", 10),
						array("P_NUM", "C", 30),
						array("P_NUMED", "C", 30),
						array("P_DATA", "D", 8),
						array("SMOK", "C", 10),
						array("SMO", "C", 100),
						array("AR_TP", "C", 20),
						array("AR_IDX", "C", 10),
						array("AR_LND", "C", 50),
						array("AR_RGN", "C", 50),
						array("AR_RN", "C", 50),
						array("AR_CTY", "C", 50),
						array("AR_NP", "C", 50),
						array("AR_STR", "C", 50),
						array("AR_DOM", "C", 5),
						array("AR_K", "C", 5),
						array("AR_KV", "C", 5),
						array("AP_TP", "C", 20),
						array("AP_IDX", "C", 10),
						array("AP_LND", "C", 50),
						array("AP_RGN", "C", 50),
						array("AP_RN", "C", 50),
						array("AP_CTY", "C", 50),
						array("AP_NP", "C", 50),
						array("AP_STR", "C", 50),
						array("AP_DOM", "C", 5),
						array("AP_K", "C", 5),
						array("AP_KV", "C", 5),
						array("D_TIP", "C", 60),
						array("D_SER", "C", 10),
						array("D_NOM", "C", 30),
						array("D_OUT", "C", 100),
						array("D_DATA", "D", 8)
					),
					'EvnPLStom' => array(
						array("EPL_ID",		"N",	18, 0),
						array("PCT_ID",		"N",	18, 0),
						array("DIR_CODE",	"N",	2, 0),
						array("PDO_CODE",	"C",	10),
						array("SETDATE",	"D",	8),
						array("SETTIME",	"C",	5),
						array("DISDATE",	"D",	8),
						array("DISTIME",	"C",	5),
						array("NUMCARD",	"C",	10),
						array("VPERV",		"N",	1, 0),
						array("KATEGOR",	"N",	1, 0),
						array("OGRN",		"C",	15),
						//array("POL_COD",	"N",	1, 0),
						//array("SOC_COD",	"C",	3),
						//array("KOD_TER",	"C",	17),
						array("FINISH_ID",	"N",	1, 0),
						array("RSC_COD",	"N",	2, 0),
						array("DZ_COD",		"C",	5, 0),
						array("DZ_NAM",		"C",	200),
						array("DST_COD",	"N",	10, 0),
						array("UKL",		"N",	3, 2),
						array("CODE_NAP",	"N",	1, 0),
						array("KUDA_NAP",	"C",	10),
						array("INVALID",	"N",	1, 0),
						array("REG_PERM",	"N",	1, 0),
						array("BDZ",	"C",	3)
					),
					'EvnVizitPLStom' => array(
						array( "EPL_ID", "N",18, 0 ),
						array("PCT_ID",		"N",	18, 0),
						array( "EVZ_ID", "N",18, 0 ),
						array( "NUMCARD", "C",10 , 0 ),
						array( "SETDATE", "D",8 , 0 ),
						array( "SETTIME", "C",5 , 0 ),
						array( "PERVVTOR", "N", 1, 0),
						array( "LS_COD", "N",9, 0 ),
						array( "LS_NAM", "C",50 , 0 ),
						array( "MP_COD", "C",20 , 0 ),
						array( "MP_FIO", "C",100 , 0 ),
						array( "PAY_COD", "N",10 , 0 ),
						array( "VZT_COD", "N",10 , 0 ),
						array( "SRT_COD", "N",10 , 0 ),
						array( "PRG_COD", "N",2 , 0 ),
						array( "DZ_COD", "C",5 , 0 ),
						array( "DZ_NAM", "C",200 , 0 ),
						array( "DST_COD", "N",10 , 0 )
					),
					'EvnUslugaStom' => array(
						array("EPL_ID",		"N",	18, 0), // EvnPL_id
						array("PCT_ID",		"N",	18, 0),
						array("EVZ_ID",		"N",	18, 0), // EvnVizitPL_id
						array("EUS_ID",		"N",	18, 0), // EvnUsluga_id
						array("EU_CLASS",	"C",	20), // EvnClass_SysNick
						array("SETDATE",	"D",	8), // EvnUsluga_setDate
						array("SETTIME",	"C",	5), // EvnUsluga_setTime
						array("USL_CODE",	"C",	20), // Usluga_Code
						array("KOLVO",		"N",	3, 3), // EvnUsluga_Kolvo
						array("UP_CODE",	"N",	1,	0), // UslugaPlace_Code
						array("MP_CODE",	"C",	5), // MedPersonal_Code
						array("PT_CODE",	"N",	1, 0) // PayType_Code
					),
					'EvnAggStom' => array(
						array("EUS_ID",		"N",	18, 0), // EvnUsluga_id
						array("PCT_ID",		"N",	18, 0),
						array("EAGG_ID",	"N",	18, 0), // EvnAgg_id
						array("SETDATE",	"D",	8), // EvnAgg_setDate
						array("SETTIME",	"C",	5), // EvnAgg_setTime
						array("AW_CODE",	"N",	1,	0), // AggWhen_Code
						array("AT_CODE",	"N",	1,	0) // AggType_Code
					)
				);
				log_message('debug', 'предварительно подготовили массивы ');

				$this->inputData['SearchFormType'] = 'EvnPLStom';

				foreach ($object_array as $object_name => $object) if ($object['export'] || $object_name == 'EvnPLStom') { //при любом наборе таблиц обязательно выгружаем EvnPL, для оценки объема выгружаемых данных
					$this->inputData['SearchFormType'] = $object_name;					
					$f_array = $fields_array[$object_name];
					if ($this->inputData['and_eplstomperson']) {
						$tmp_array = $fields_array['EPLStomPerson'];
						for ($i = 0; $i < count($f_array); $i++) if($f_array[$i][0] != 'PCT_ID' && $f_array[$i][0] != 'P_ID' ) $tmp_array[] = $f_array[$i];
						$f_array = $tmp_array;
					}
					$response = $this->dbmodel->searchData($this->inputData, false, false, true);
					log_message('debug', 'выполнили SQL '.$this->inputData['SearchFormType']);					
					if ( $response === false ) {
                        $this->ReturnError('Ошибка: обрыв коннекта. Повторите попытку.');
						return false;
					}
					else if ( is_array($response) && isset($response['Error_Msg']) && strlen($response['Error_Msg']) > 0 ) {
                        $this->ReturnError($response['Error_Msg']);
						return false;
					}
					else if ($object_name == 'EvnPLStom' && $response['totalCount'] == 0) {//только для ТАПов
                        $this->ReturnError('В БД нет данных, удовлетворяющих условиям поиска.');
						return false;
					}
					else if ($object_name == 'EvnPLStom' && $response['totalCount'] > 50000) {//только для квс
                        $this->ReturnError('Выгрузка невозможна из-за большого количества записей.<br />Используйте фильтры для ограничения количества выгружаемых записей');
						return false;
					}					
					//var_dump($f_array);die;
					$this->InDbf($object['file_name'], $response, $f_array);
					log_message('debug', 'сформировали DBF '.$object['file_name']);
				}

				log_message('debug', 'формируем ZIP');
				$zip = new ZipArchive();
				$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
				
				foreach ($object_array as $object_name => $object) if ($object['export'])
					$zip->AddFile($object['file_name'], $object['file_sign'].".dbf");
				
				$zip->close();

				foreach ($object_array as $object_name => $object) if ($object['export'])
					unlink($object['file_name']);

				log_message('debug', 'сформировали ZIP '.$file_zip_name);
				
				$val = array("success" => true, "url" => "/" . $file_zip_name );

			break;

			case 'EvnSection':
			case 'EvnPS':
				$this->inputData['SearchFormType'] = 'EvnPS';

				set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
				
				log_message('debug', 'начало выгрузки '.$this->inputData['SearchFormType']);

				// Подготовительные операции 
				$out_dir = 'ps'.time();
				if ( !file_exists(EXPORTPATH_PS) ) {
					mkdir( EXPORTPATH_PS );
				}
				mkdir( EXPORTPATH_PS.$out_dir );

				$file_zip_sign = 'KBC';
				$file_zip_name = EXPORTPATH_PS.$out_dir."/".$file_zip_sign.".zip";
				
				log_message('debug', 'создали папку для выгрузки: '.$out_dir);
				
				$object_array = array(
					'KvsEvnPS' => array('file_sign' => 'GOSPITAL'),
					'KvsPerson' => array('file_sign' => 'PACIENT'),
					'KvsPersonCard' => array('file_sign' => 'REGISTR'),
					'KvsEvnDiag' => array('file_sign' => 'DIAGNOZ'),					
					'KvsEvnSection' => array('file_sign' => 'HISTORY'),					
					'KvsNarrowBed' => array('file_sign' => 'UZKOKOEK'),					
					'KvsEvnUsluga' => array('file_sign' => 'USLUGA'),					
					'KvsEvnUslugaOB' => array('file_sign' => 'USLUGAOB'),					
					'KvsEvnUslugaAn' => array('file_sign' => 'USLUGAAN'),					
					'KvsEvnUslugaOsl' => array('file_sign' => 'USLUGAOSL'),					
					//'KvsEvnUslugaCombined' => array('file_sign' => 'USLUGA'),
					'KvsEvnDrug' => array('file_sign' => 'MEDIKAMENT'),					
					'KvsEvnLeave' => array('file_sign' => 'ISCHOD'),
					'KvsEvnStick' => array('file_sign' => 'LWN')					
					/*'EvnPS'		 => array('file_sign' => 'EPS'),					
					'EvnSection' => array('file_sign' => 'ESEC'),
					'EvnDiag'	 => array('file_sign' => 'EPSDZ'),
					'EvnLeave'	 => array('file_sign' => 'ELV'),
					'EvnStick'	 => array('file_sign' => 'EST')*/
				);
				
				$table_list = array();
				$this->inputData['and_kvsperson'] = false;
				if ($_POST['table_list'] && $_POST['table_list'] != '') {
					$table_list = preg_split('/[,]/', $_POST['table_list']);
					if (in_array('ADD_PERSON', $table_list))
						$this->inputData['and_kvsperson'] = true;
				}
				
				$this->inputData['kvs_date_type'] = 1;
				if ($_POST['date_type'] && $_POST['date_type'] != '') {
					$this->inputData['kvs_date_type'] = $_POST['date_type'];
				}
				
				//подключаем к общему списку таблиц, также таблицы относящиеся к услугам
				if (in_array('USLUGA', $table_list)) {
					//if (!$this->inputData['and_kvsperson'])
						$table_list = array_merge($table_list, array('USLUGA', 'USLUGAOB', 'USLUGAAN', 'USLUGAOSL'));
				}
				
				foreach ($object_array as $object_name => $object) {
					$object_array[$object_name]['file_name'] = EXPORTPATH_PS.$out_dir."/".$object['file_sign'].".dbf";
					$exp = in_array($object['file_sign'], $table_list);
					
					/*if ($object['file_sign'] == 'USLUGA') {
						if ($this->inputData['and_kvsperson']) {
							$exp = ($object_name == 'KvsEvnUslugaCombined');
						} else {
							$exp = ($object_name == 'KvsEvnUsluga');
						}
					}*/
					
					$object_array[$object_name]['export'] = $exp;
				}
				
				$fields_array = array(
					'KvsPerson' => array(
						array("PCT_ID", "N", 15, 8),
						array("P_ID", "N", 15, 8),
						array("SURNAME", "C", 50),
						array("FIRNAME", "C", 50),
						array("SECNAME", "C", 50),
						array("BIRTHDAY", "D", 8),
						array("SNILS", "C", 11),
						array("INV_N", "N", 1, 0),
						array("INV_DZ", "C", 5),
						array("INV_DATA", "D", 8),
						array("SEX", "C", 15),
						array("SOC", "C", 30),
						array("P_TERK", "C", 10),
						array("P_TER", "C", 30),
						array("P_NAME", "C", 10),
						array("P_SER", "C", 10),
						array("P_NUM", "C", 30),
						array("P_NUMED", "C", 30),
						array("P_DATA", "D", 8),
						array("SMOK", "C", 10),
						array("SMO", "C", 100),
						array("AR_TP", "C", 20),
						array("AR_IDX", "C", 10),
						array("AR_LND", "C", 50),
						array("AR_RGN", "C", 50),
						array("AR_RN", "C", 50),
						array("AR_CTY", "C", 50),
						array("AR_NP", "C", 50),
						array("AR_STR", "C", 50),
						array("AR_DOM", "C", 5),
						array("AR_K", "C", 5),
						array("AR_KV", "C", 5),
						array("AP_TP", "C", 20),
						array("AP_IDX", "C", 10),
						array("AP_LND", "C", 50),
						array("AP_RGN", "C", 50),
						array("AP_RN", "C", 50),
						array("AP_CTY", "C", 50),
						array("AP_NP", "C", 50),
						array("AP_STR", "C", 50),
						array("AP_DOM", "C", 5),
						array("AP_K", "C", 5),
						array("AP_KV", "C", 5),
						array("D_TIP", "C", 60),
						array("D_SER", "C", 10),
						array("D_NOM", "C", 30),
						array("D_OUT", "C", 100),
						array("D_DATA", "D", 8)
					),
					'KvsPersonCard' => array(
						array("REG_ID", "N", 15, 8),
						array("PCT_ID", "N", 15, 8),
						array("P_ID", "N", 15, 8),
						array("PR_AK", "C", 10),
						array("PR_TP", "C", 12),
						array("PR_DATA", "D", 8),
						array("LPUK", "C", 10),
						array("LPU", "C", 150),
						array("TPLOT", "C", 30),
						array("LOT", "C", 30)
					),
					'KvsEvnDiag' => array(
						array("DZ_ID", "N", 15, 8),
						array("GSP_ID", "N", 15, 8),
						array("PCT_ID", "N", 15, 8),
						array("P_ID", "N", 15, 8),
						array("KTO", "N", 1, 0),
						array("LPUK", "C", 10),
						array("LPU", "C", 150),
						array("OTDK", "C", 10),
						array("OTD", "C", 100),
						array("DZ_DATA", "D", 8),
						array("DZ_W", "C", 25),
						array("DZ_T", "C", 25),
						array("DZ_DZ", "C", 5)
					),
					'KvsEvnPS' => array(
						array("GSP_ID", "N", 15, 8),
						array("PCT_ID", "N", 15, 8),
						array("P_ID", "N", 15, 8),
						array("KARTPR", "N", 1, 0),
						array("KART", "C", 10),
						array("WOPL", "C", 10),
						array("DATAPOST", "D", 8),
						array("TIMEPOST", "C", 10),
						array("AGEPOST", "N", 3, 0),
						array("KN_KT", "C", 25),
						array("KN_OTDLK", "C", 10),
						array("KN_OTDL", "C", 100),
						array("KN_ORGK", "C", 10),
						array("KN_ORG", "C", 100),
						array("KN_FD", "N", 1, 0),
						array("KN_N", "C", 10),
						array("KN_DATA", "D", 8),
						array("KD_KT", "C", 20),
						array("KD_KOD", "C", 10),
						array("KD_NN", "C", 10),
						array("DZGOSP", "C", 5),
						array("DEF_NG", "N", 1, 0),
						array("DEF_NOO", "N", 1, 0),
						array("DEF_NTL", "N", 1, 0),
						array("DEF_ND", "N", 1, 0),						
						array("ALKO", "N", 1, 0),
						array("PR_GP", "C", 25),
						array("PR_N", "N", 2, 0),
						array("PR_W", "C", 10),
						array("TR_T", "C", 30),
						array("TR_P", "N", 1, 0),
						array("TR_N", "N", 1, 0),
						array("PRO_NK", "C", 10),
						array("PRO_N", "C", 100),
						array("PRO_DOCK", "C", 10),
						array("PRO_DOC", "C", 35),
						array("PRO_DZ", "C", 5)
					),
					'KvsEvnSection' => array(
						array("HSTRY_ID", "N", 15, 8),
						array("GSP_ID", "N", 15, 8),
						array("PCT_ID", "N", 15, 8),
						array("P_ID", "N", 15, 8),
						array("DATAP", "D", 8),
						array("TIMEP", "C", 10),
						array("DATAW", "D", 8),
						array("TIMEW", "C", 10),
						array("OTDLK", "C", 10),
						array("OTDL", "C", 100),
						array("WO", "C", 25),
						array("WT", "C", 25),
						array("DOCK", "C", 10),
						array("DOC", "C", 35),
						array("DZ", "C", 5),
						array("MES", "C", 20),
						array("NORM", "N", 3, 0),
						array("KDN", "N", 5, 0)
					),
					'KvsNarrowBed' => array(
						array("UK_ID", "N", 15, 8),
						array("PCT_ID", "N", 15, 8),
						array("P_ID", "N", 15, 8),
						array("GSP_ID", "N", 15, 8),
						array("HSTRY_ID", "N", 15, 8),
						array("DATAP", "D", 8),
						array("DATAW", "D", 8),
						array("OTDLK", "C", 10),
						array("OTDL", "C", 100)
					),
					'KvsEvnUsluga' => array(
						array("U_ID", "N", 15, 8),
						array("PCT_ID", "N", 15, 8),
						array("P_ID", "N", 15, 8),
						array("GSP_ID", "N", 15, 8),
						array("U_TIP", "C", 35, 0),
						array("U_DATA", "D", 8),
						array("U_TIME", "C", 10),
						array("U_MESTO", "C", 100),
						array("U_OTELK", "C", 10),
						array("U_OTEL", "C", 100),
						array("U_LPUK", "C", 10),
						array("U_LPU", "C", 100),
						array("U_ORGK", "C", 10),
						array("U_ORG", "C", 100),
						array("U_DOCK", "C", 10),
						array("U_DOC", "C", 35),
						array("U_USLKOD", "N", 10, 0),
						array("U_USL", "C", 100),
						array("U_WO", "C", 25),
						array("U_TIPOP", "N", 2, 0),
						array("U_KATSLOJ", "N", 2, 0),
						array("U_PREND", "N", 2, 0),
						array("U_PRLAS", "N", 2, 0),
						array("U_PRKRI", "N", 2, 0),
						array("U_KOL", "N", 5, 0)
					),
					'KvsEvnUslugaOB' => array(
						array("U_ID", "N", 15, 8),
						array("PCT_ID", "N", 15, 8),
						array("P_ID", "N", 15, 8),
						array("GSP_ID", "N", 15, 8),
						array("U_WID", "C", 35),
						array("U_DOCK", "C", 10),
						array("U_DOC", "C", 35)
					),
					'KvsEvnUslugaAn' => array(
						array("U_ID", "N", 15, 8),
						array("PCT_ID", "N", 15, 8),
						array("P_ID", "N", 15, 8),
						array("GSP_ID", "N", 15, 8),
						array("U_ANEST", "C", 35)
					),
					'KvsEvnUslugaOsl' => array(
						array("U_ID", "N", 15, 8),
						array("PCT_ID", "N", 15, 8),
						array("P_ID", "N", 15, 8),
						array("GSP_ID", "N", 15, 8),
						array("U_DATA", "D", 8),
						array("U_TIME", "C", 10),
						array("U_WID", "C", 100),
						array("U_KONT", "C", 100)
					),
					/*'KvsEvnUslugaCombined' => array(
						array("U_ID", "N", 15, 8),
						array("GSP_ID", "N", 15, 8),
						array("U_TIP", "C", 35, 0),
						array("U_DATA", "D", 8),
						array("U_TIME", "C", 10),
						array("U_MESTO", "C", 100),
						array("U_OTELK", "C", 10),
						array("U_OTEL", "C", 100),
						array("U_LPUK", "C", 10),
						array("U_LPU", "C", 100),
						array("U_ORGK", "C", 10),
						array("U_ORG", "C", 100),
						array("U_DOCK", "C", 10),
						array("U_DOC", "C", 35),
						array("U_USLKOD", "N", 10, 0),
						array("U_USL", "C", 100),
						array("U_WO", "C", 25),
						array("U_TIPOP", "N", 2, 0),
						array("U_KATSLOJ", "N", 2, 0),
						array("U_PREND", "N", 2, 0),
						array("U_PRLAS", "N", 2, 0),
						array("U_PRKRI", "N", 2, 0),
						array("U_KOL", "N", 5, 0),
						array("UOB_WID", "C", 35),
						array("UOB_DOCK", "C", 10),
						array("UOB_DOC", "C", 35),
						array("U_ANEST", "C", 35),
						array("UOS_DATA", "D", 8),
						array("UOS_TIME", "C", 10),
						array("UOS_WID", "C", 100),
						array("UOS_KONT", "C", 100)
					),*/
					'KvsEvnDrug' => array(
						array("MED_ID", "N", 15, 8),
						array("PCT_ID", "N", 15, 8),
						array("P_ID", "N", 15, 8),
						array("GSP_ID", "N", 15, 8),
						array("M_DATA", "D", 8),
						array("M_OTDLK", "C", 10),
						array("M_OTDL", "C", 100),
						array("M_MOL", "C", 100),
						array("MEDK", "C", 10),
						array("MED", "C", 100),
						array("M_PART", "C", 100),
						array("M_KOL", "N", 10, 0),
						array("M_EU", "C", 10),
						array("M_EU_OCT", "N", 10, 0),
						array("M_EU_KOL", "N", 10, 0),
						array("M_ED", "C", 10),
						array("M_ED_OCT", "N", 10, 0),
						array("M_ED_KOL", "N", 10, 0),
						array("M_CENA", "N", 10, 0),
						array("M_SUM", "N", 10, 0),
					),
					'KvsEvnLeave' => array(
						array("ISCH_ID", "N", 15, 8),
						array("PCT_ID", "N", 15, 8),
						array("P_ID", "N", 15, 8),
						array("GSP_ID", "N", 15, 8),
						array("IG_W", "C", 30),
						array("IS_DATA", "D", 8),
						array("IS_TIME", "C", 10),
						array("IS_URUW", "C", 5),
						array("IS_BOL", "C", 50),
						array("IS_PR", "C", 20),
						array("IS_NAPR", "N", 1, 0),
						array("IS_LPUK", "C", 10),
						array("IS_LPU", "C", 100),
						array("IS_TS", "C", 50),
						array("IS_STACK", "C", 10),
						array("IS_STAC", "C", 100),
						array("IS_DOCK", "C", 10),
						array("IS_DOC", "C", 100),
						array("IS_DZ", "C", 5)
					),
					'KvsEvnStick' => array(
						array("LWN_ID", "N", 15, 8),
						array("PCT_ID", "N", 15, 8),
						array("P_ID", "N", 15, 8),
						array("GSP_ID", "N", 15, 8),
						array("PORYAD", "C", 20),
						array("LWNOLD", "C", 20),
						array("LWN_S", "C", 20),
						array("LWN_N", "C", 20),
						array("LWN_D", "D", 8),
						array("LWN_PR", "C", 50),
						array("ROD_FIO", "C", 50),
						array("ROD_W", "N", 2, 0),
						array("ROD_POL", "C", 15),
						array("SKL_DN", "D", 8),
						array("SKL_DK", "D", 8),
						array("SKL_NOM", "C", 20),
						array("SKL_LPU", "C", 100),
						array("LWN_R", "C", 20),
						array("LWN_ISCH", "C", 50),
						array("LWN_SP", "C", 20),
						array("LWN_NP", "C", 20),
						array("LWN_DR", "D", 8),
						array("LWN_DOCK", "C", 10),
						array("LWN_DOC", "C", 100),
						array("LWN_LPUK", "C", 10),
						array("LWN_LPU", "C", 100),
						array("LWN_DZ1", "C", 5),
						array("LWN_DZ2", "C", 5)
					)
				);				
				log_message('debug', 'предварительно подготовили массивы ');

				foreach ($object_array as $object_name => $object) if ($object['export'] || $object_name == 'KvsEvnPS') { //при любом наборе таблиц обязательно выгружаем kvsEvnPS, для оценки объема выгружаемых данных
					$this->inputData['SearchFormType'] = $object_name;					
					$f_array = $fields_array[$object_name];
					if ($this->inputData['and_kvsperson']) {
						$tmp_array = $fields_array['KvsPerson'];
						for ($i = 0; $i < count($f_array); $i++) if($f_array[$i][0] != 'PCT_ID' && $f_array[$i][0] != 'P_ID' ) $tmp_array[] = $f_array[$i];
						$f_array = $tmp_array;
					}
					$response = $this->dbmodel->searchData($this->inputData, false, false, true);
					log_message('debug', 'выполнили SQL '.$this->inputData['SearchFormType']);					
					if ( $response === false ) {
                        $this->ReturnError('Ошибка: обрыв коннекта. Повторите попытку.');
						return false;
					}
					else if ( is_array($response) && isset($response['Error_Msg']) && strlen($response['Error_Msg']) > 0 ) {
                        $this->ReturnError($response['Error_Msg']);
						return false;
					}
					else if ($object_name == 'KvsEvnPS' && $response['totalCount'] == 0) {//только для квс
                        $this->ReturnError('В БД нет данных, удовлетворяющих условиям поиска.');
						return false;
					}
					else if ($object_name == 'KvsEvnPS' && $response['totalCount'] > 50000) {//только для квс
                        $this->ReturnError('Выгрузка невозможна из-за большого количества записей.<br />Используйте фильтры для ограничения количества выгружаемых записей');
						return false;
					}					
					$this->InDbf($object['file_name'], $response, $f_array);
					log_message('debug', 'сформировали DBF '.$object['file_name']);
				}
				
				log_message('debug', 'формируем ZIP');
				$zip = new ZipArchive();
				$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
				
				foreach ($object_array as $object_name => $object) if ($object['export'])
					$zip->AddFile($object['file_name'], $object['file_sign'].".dbf");
				
				$zip->close();

				foreach ($object_array as $object_name => $object) if ($object['export'])
					unlink($object['file_name']);

				log_message('debug', 'сформировали ZIP '.$file_zip_name);
				
				$val = array("success" => true, "url" => "/" . $file_zip_name );
			break;
		}

		$this->ReturnData($val);

		return true;
	}

	/**
	*  Поиск
	*  На выходе: JSON-строка
	*  Используется: форма поиска льгот (SearchFormType = PersonPrivilege)
	*                форма поиска по картотеке (SearchFormType = PersonCard) [ пока экспериментальная поддержка ]
	*                форма поиска рецептов (SearchFormType = EvnRecept) или (SearchFormType = EvnReceptGeneral)
	*                форма поиска ТАП (SearchFormType = EvnPL или EvnVizitPL) 
	*                форма поиска талона по стоматологии (SearchFormType = EvnPLStom или EvnVizitPLStom)
	*                фильтр АРМ оператора call-центра (SearchFormType = PersonCallCenter)
	*/
	function searchData() {
		if (empty($this->dbmodel)) { return false; } // не прошли конструктор
		
		$val  = array();
		
		$searchOnReserveBase = false;

		$data = $this->inputData;

		/*
		//законсервировано, потом пригодится
		// в формах поиска сделан выбор - при поиске без фио и номера карты - поиск переключается на БД указанную в коннекторе "search" 
		if(
			!empty($data['SearchFormType']) && 
			($data['SearchFormType'] == 'CmpCloseCard' || $data['SearchFormType'] == 'CmpCallCard' ) &&
			!empty($data['Person_Secname']) &&
			!empty($data['Person_Surname']) &&
			!empty($data['Person_Firname']) &&
			(!empty($this->config->item('hasReservedDB')) && $this->config->item('hasReservedDB') == true)
			//!empty($this->inputData['CmpCallCard_id'])
		){
			$searchOnReserveBase = true;
		}
		
		
		if($searchOnReserveBase){
			unset($this->db);
			$this->load->database('search');
		}
		*/
		//сейчас мы на резервной базе
		//var_dump($data);die;
		if (!empty($data['getCountOnly'])) {
			$response = $this->dbmodel->searchData($data, true, false);
			$this->ProcessModelSave($response, true)->ReturnData();
		} else {
			$response = $this->dbmodel->searchData($data, false, false);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
		}
		/*
		if($searchOnReserveBase){
			//сейчас мы на по умалочанию базе
			unset($this->db);
			$this->load->database();
		}
		*/
		return true;
	}


	/**
	*  Печать результатов поиска
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: формы поиска
	*/
	function printSearchResults() {
		if (empty($this->dbmodel)) { return false; } // не прошли конструктор
		
		$this->load->library('parser');

		$view = '';

		$response = $this->dbmodel->searchData($this->inputData, false, true);

		if ( !is_array($response) ) {
			echo 'Ошибка при выполнении запроса к базе данных';
			return false;
		}
		else if ( (isset($response['Error_Msg'])) && (strlen($response['Error_Msg']) > 0) ) {
			echo $response['Error_Msg'];
			return false;
		}

		switch ( $this->inputData['SearchFormType'] ) {
			case 'EvnPL':
				$view = 'evn_pl_search_results';
				for ( $i = 0; $i < count($response['data']); $i++ ) {
					$response['data'][$i]['Record_Num'] = $i + 1;
				}
			break;

			case 'EvnVizitPL':
				if ( $this->inputData['session']['region']['nick'] == 'ufa' ) {
					$view = 'evn_vizit_pl_search_results_ufa';
				} else {
					$view = 'evn_vizit_pl_search_results';
				}
				for ( $i = 0; $i < count($response['data']); $i++ ) {
					$response['data'][$i]['Record_Num'] = $i + 1;
				}
			break;

			case 'EvnVizitPLStom':
				$view = 'evn_vizit_pl_stom_search_results';
				for ( $i = 0; $i < count($response['data']); $i++ ) {
					$response['data'][$i]['Record_Num'] = $i + 1;
				}
			break;
			
			case 'EvnPLDispDop':
				$view = 'evn_pl_dd_search_results';
				for ( $i = 0; $i < count($response['data']); $i++ ) {
					$response['data'][$i]['Record_Num'] = $i + 1;
				}
			break;

			case 'EvnPLDispDop13':
				$view = 'evn_pl_dd13_search_results';
				for ( $i = 0; $i < count($response['data']); $i++ ) {
					$response['data'][$i]['Record_Num'] = $i + 1;
				}
			break;

			case 'EvnPLDispDop13Sec':
				$view = 'evn_pl_dd13_search_results';
				for ( $i = 0; $i < count($response['data']); $i++ ) {
					$response['data'][$i]['Record_Num'] = $i + 1;
				}
			break;

			case 'EvnPLDispProf':
				$view = 'evn_pl_dp_search_results';
				for ( $i = 0; $i < count($response['data']); $i++ ) {
					$response['data'][$i]['Record_Num'] = $i + 1;
				}
			break;
			
			case 'EvnPLDispTeen14':
				$view = 'evn_pl_dt14_search_results';
				for ( $i = 0; $i < count($response['data']); $i++ ) {
					$response['data'][$i]['Record_Num'] = $i + 1;
				}
			break;

			case 'EvnPLStom':
				$view = 'evn_pl_stom_search_results';
				for ( $i = 0; $i < count($response['data']); $i++ ) {
					$response['data'][$i]['Record_Num'] = $i + 1;
				}
			break;

			case 'EvnPLWOW':
				$view = 'evn_pl_wow_search_results';
				for ( $i = 0; $i < count($response['data']); $i++ ) {
					$response['data'][$i]['Record_Num'] = $i + 1;
				}
			break;

			case 'EvnDtpWound':
				$view = 'evn_dtp_wound_search_results';
				for ( $i = 0; $i < count($response['data']); $i++ ) {
					$response['data'][$i]['Record_Num'] = $i + 1;
				}
			break;

			case 'EvnDtpDeath':
				$view = 'evn_dtp_death_search_results';
				for ( $i = 0; $i < count($response['data']); $i++ ) {
					$response['data'][$i]['Record_Num'] = $i + 1;
				}
			break;

			case 'EvnUslugaPar':
				$view = 'evn_usluga_par_search_results';
				for ( $i = 0; $i < count($response['data']); $i++ ) {
					$response['data'][$i]['Record_Num'] = $i + 1;
				}
			break;

			case 'PersonPrivilegeWOW':
				$view = 'pp_wow_search_results';
				for ( $i = 0; $i < count($response['data']); $i++ ) {
					$response['data'][$i]['Record_Num'] = $i + 1;
				}
			break;

			case 'EvnPS':
				$view = 'evn_ps_search_results';
				for ( $i = 0; $i < count($response['data']); $i++ ) {
					$response['data'][$i]['Record_Num'] = $i + 1;
					$response['data'][$i]['Person_IsBDZ'] = ($response['data'][$i]['Person_IsBDZ'] == 'true' ? 'Да' : 'Нет');
				
				}
			break;

			case 'EvnSection':
				$view = 'evn_section_search_results';
				for ( $i = 0; $i < count($response['data']); $i++ ) {
					$response['data'][$i]['Record_Num'] = $i + 1;
				}
			break;

			case 'EvnRecept':
				$view = 'evn_recept_search_results';
				for ( $i = 0; $i < count($response['data']); $i++ ) {
					$response['data'][$i]['Record_Num'] = $i + 1;
				}
			break;

			case 'EvnReceptGeneral':
				$view = 'evn_recept_search_results';
				for ( $i = 0; $i < count($response['data']); $i++ ) {
					$response['data'][$i]['Record_Num'] = $i + 1;
				}
			break;

			case 'WorkPlacePolkaReg':
			case 'PersonCard':
			case 'PersonCallCenter':
				$view = 'person_card_search_results';
				for ( $i = 0; $i < count($response['data']); $i++ ) {
					$response['data'][$i]['Record_Num'] = $i + 1;
					$response['data'][$i]['PersonCard_IsAttachCondit'] = ($response['data'][$i]['PersonCard_IsAttachCondit'] == 'true' ? 'Да' : 'Нет');
					$response['data'][$i]['PersonCardAttach'] = ($response['data'][$i]['PersonCardAttach'] == 'true' ? 'Да' : 'Нет');
				}
			break;
				
			case 'PersonCardStateDetail':
				$view = 'person_card_state_detail_search_results';
				for ( $i = 0; $i < count($response['data']); $i++ ) {
					$response['data'][$i]['Record_Num'] = $i + 1;
					$response['data'][$i]['PersonCard_IsAttachCondit'] = ($response['data'][$i]['PersonCard_IsAttachCondit'] == 'true' ? 'Да' : 'Нет');
				}
			break;

			case 'PersonPrivilege':
				$view = 'privilege_search_results';
				for ( $i = 0; $i < count($response['data']); $i++ ) {
					$response['data'][$i]['Record_Num'] = $i + 1;
					$response['data'][$i]['Person_IsRefuse'] = ($response['data'][$i]['Person_IsRefuse'] == 'true' ? 'Да' : 'Нет');
				}
			break;
			
			case 'PersonDisp':
				$view = 'person_disp_search_results';
				for ( $i = 0; $i < count($response['data']); $i++ ) {
					$response['data'][$i]['Record_Num'] = $i + 1;
					$response['data'][$i]['Is7Noz'] = ($response['data'][$i]['Is7Noz'] == 'true' ? 'Да' : 'Нет');					
				}
			break;

			case 'PersonDopDisp':
				$view = 'person_dop_disp_search_results';
				for ( $i = 0; $i < count($response['data']); $i++ ) {
					$response['data'][$i]['Record_Num'] = $i + 1;
					$response['data'][$i]['ExistsDDPL'] = ($response['data'][$i]['ExistsDDPL'] == 'true' ? 'Да' : 'Нет');					
				}
			break;
			
			case 'VacJournal':
				$view = 'vac_journal_search_results';
				for ( $i = 0; $i < count($response['data']); $i++ ) {
					$response['data'][$i]['Record_Num'] = $i + 1;
					$response['data'][$i]['PersonCard_IsAttachCondit'] = ($response['data'][$i]['PersonCard_IsAttachCondit'] == 'true' ? 'Да' : 'Нет');
				}
			break;
		}

		$xml = $this->parser->parse($view, array('search_results' => $response['data']));
		
		return true;
	}


	/**
	*  Подсчет количества найденных записей
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма поиска КВС
	*                форма поиска льгот
	*                форма поиска талона по стоматологии
	*                форма поиска ТАП
	*                форма просмотра списка удостоверений
	*/
	function getRecordsCount() {
		if (empty($this->dbmodel)) { return false; } // не прошли конструктор
		
		$val  = array();

		$response = $this->dbmodel->searchData($this->inputData, true, false);

		if ( (!is_array($response)) || (!isset($response['totalCount'])) ) {
			if ( isset($response[0]['Error_Msg']) ) {
				$val = $response[0];
			}
			else {
				$val['success'] = false;
				$val['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных';
			}
		}
		else {
			$val['success'] = true;
			$val['Records_Count'] = $response['totalCount'];
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}
}