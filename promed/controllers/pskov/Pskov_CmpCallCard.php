<?php defined('BASEPATH') or die('No direct script access allowed');
/**
 * CmpCallCard - контроллер для СМП. Версия для Пскова
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 */

require_once(APPPATH.'controllers/CmpCallCard.php');

class Pskov_CmpCallCard extends CmpCallCard {

	/**
	 *
	 * Конструктор
	 *  
	 */
	function __construct() {
		parent::__construct();		
		/*
        $this->inputRules['setEmergencyTeam'] = array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'EmergencyTeam_id', 'label' => 'Назначенная бригада', 'rules' => 'required', 'type' => 'int' ),
			array('field' => 'Person_id', 'label' => 'ID пациента', 'rules' => '', 'type' => 'int' ),
			array('field' => 'Person_FIO', 'label' => 'ФИО пациента', 'rules' => '', 'type' => 'string' ),
			array('field' => 'Person_Firname', 'label' => 'Имя пациента', 'rules' => '', 'type' => 'string' ),
			array('field' => 'Person_Secname', 'label' => 'Отчество пациента', 'rules' => '', 'type' => 'string' ),
			array('field' => 'Person_Surname', 'label' => 'Фамилия пациента', 'rules' => '', 'type' => 'string' ),
			array('field' => 'Person_Birthday', 'label' => 'Дата рождения пациента', 'rules' => '', 'type' => 'string' ),
			array('field' => 'CmpCallCard_prmDate', 'label' => 'Дата принятия вызова', 'rules' => 'required', 'type' => 'string' ),
			array('field' => 'CmpReason_Name', 'label' => 'Повод вызова', 'rules' => '', 'type' => 'string' ),
			array('field' => 'Adress_Name', 'label' => 'Адрес вызова', 'rules' => 'required', 'type' => 'string' ),
			array('field' => 'CmpCallType_Name', 'label' => 'Тип вызова', 'rules' => '', 'type' => 'string' )
		);

		$this->inputRules['saveCmpCallCard'] = array(
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
				'field' => 'CmpCallCard_Room',
				'label' => 'Комната (место вызова)',
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
				'field' => 'CmpCallerType_id',
				'label' => 'Кто вызывает',
				'rules' => '',
				'type' => 'int'
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
				'field' => 'CmpCallCard_prmDate',
				'label' => 'Дата приема',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'CmpCallCard_prmTime',
				'label' => 'Время приема',
				'rules' => '',
				'type' => 'time'
			),
			array(
				'field' => 'CmpCallCard_Prty',
				'label' => 'Приоритет',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Przd',
				'label' => 'Время прибытия на адрес',
				'rules' => '',
				'type' => 'time'
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
				'field' => 'CmpCallCard_Tgsp',
				'label' => 'Время отзвона о госпитализации',
				'rules' => '',
				'type' => 'time'
			),
			array(
				'field' => 'CmpCallCard_Tisp',
				'label' => 'Время исполнения (освобождения)',
				'rules' => '',
				'type' => 'time'
			),
			array(
				'field' => 'CmpCallCard_Tiz1',
				'label' => 'Время передачи извещения',
				'rules' => '',
				'type' => 'time'
			),
			array(
				'field' => 'CmpCallCard_Tper',
				'label' => 'Время передачи',
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
				'field' => 'CmpCallCard_Vyez',
				'label' => 'Время выезда',
				'rules' => '',
				'type' => 'time'
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
				'field' => 'CmpSecondReason_id',
				'label' => 'Доп. повод',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpReasonNew_id',
				'label' => 'Повод расширенный',
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
				'field' => 'Person_Firname',
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
				'field' => 'Person_PolisSer',
				'label' => 'Серия полиса',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Secname',
				'label' => 'Отчество',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Surname',
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
				'field' => 'UslugaComplex_id',
				'label' => 'Идентификатор основной услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CmpCallPlaceType_id',
				'label' => 'Идентификатор типа места вызова',
				'rules' => '',
				'type' => 'id'
			)
		
		);*/
	}
}