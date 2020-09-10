<?php	defined('BASEPATH') or die ('No direct script access allowed');
class CmpCallCard extends swController {
	public $inputRules = array(
		'sendCallToSmp' => array(
			array('field' => 'CmpCallCard_id', 'label' => 'ИД талона', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'Lpu_id', 'label' => 'ИД МО', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuBuilding_id', 'label' => 'ИД подстанции', 'rules' => '', 'type' => 'string'),
		),
		'getAdressByCardId' => array(
			array('field' => 'CmpCallCard_id', 'label' => 'ИД талона', 'rules' => '', 'type' => 'string')
		),
		'printReportCmp' => array (
			array('field' => 'daydate1','label' => 'Дата','rules' => 'trim','type' => 'date'),
			array('field' => 'daydate2','label' => 'Дата','rules' => 'trim','type' => 'date')	
		),
		'importSMPCardsTest' => array(
			array('field' => 'Lpu_Name','label' => 'Название ЛПУ','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_insDT1','label' => 'Дата с','rules' => 'trim','type' => 'date'),
			array('field' => 'CmpCallCard_insDT2','label' => 'Дата по','rules' => 'trim','type' => 'date')
		),
		'deleteCmpCallCard' => array(	
			array('field' => 'CmpCallCard_id','label' => 'Идентификатор карты вызова','rules' => 'required','type' => 'id')
		),
		'setPPDWaitingTime' => array(
			array('field' => 'PPD_WaitingTime','label' => 'Время ожидания принятия НМП','rules' => 'required','type' => 'int'),
			array('field' => 'Password','label' => 'Пароль учётной записи','rules' => 'required','type' => 'string')
		),
		'loadCmpStreamEditForm' => array(
			array('field' => 'CmpCallCard_id','label' => 'Идентификатор карты вызова','rules' => 'required','type' => 'id')			
		),
		'loadCmpCallCardEditForm' => array(
			array('field' => 'CmpCallCard_id','label' => 'Идентификатор карты вызова','rules' => 'required','type' => 'id'),
			array('field' => 'CmpCallCardEventType_Code','label' => 'Идентификатор карты вызова','rules' => '','type' => 'int')
		),		
		'loadCmpCloseCardViewForm' => array(
			array('field' => 'CmpCallCard_id','label' => 'Идентификатор карты вызова','rules' => '','type' => 'id'),
			array('field' => 'CmpCloseCard_id','label' => 'Идентификатор карты вызова','rules' => '','type' => 'id'),
			array('field' => 'delDocsView','label' => 'Просмотр удаленных документов','rules' => '','type' => 'id'),
		),
		'loadCmpCallCardJournalGrid' => array(
			array('field' => 'CmpCallCard_IsPoli','label' => 'Актив','rules' => 'trim','type' => 'id'),
			array('field' => 'CmpCallCard_prmDT_From','label' => 'Дата с','rules' => 'trim','type' => 'date'),
			array('field' => 'CmpCallCard_prmDT_To','label' => 'Дата по','rules' => 'trim','type' => 'date'),
			array('field' => 'CmpLpu_id','label' => 'ЛПУ куда доставлен','rules' => 'trim','type' => 'id'),
			array('field' => 'LpuAttachType_id','label' => 'Тип прикрепления','rules' => 'trim','type' => 'id'),
			array('field' => 'LpuRegion_id','label' => 'Участок','rules' => 'trim','type' => 'id'),
			array('field' => 'Lpu_aid','label' => 'Лпу прикрепления','rules' => 'trim','type' => 'string'),
			array('field' => 'MedPersonal_id','label' => 'Врач на участке','rules' => 'trim','type' => 'id'),
			array('field' => 'limit','default' => 100,'label' => 'Количество','rules' => 'trim','type' => 'int'),
			array('field' => 'start','default' => 0,'label' => 'Старт','rules' => 'trim','type' => 'int')
		),
		'loadSMPWorkPlace' => array(
			array('field' => 'begDate','label' => 'Дата с','rules' => 'trim|required','type' => 'date'),
			array('field' => 'CmpCallCard_Numv','label' => 'Номер вызова','rules' => 'trim','type' => 'string'),
			array('field' => 'CmpCallCard_Ngod','label' => 'Номер вызова за год','rules' => 'trim','type' => 'string'),
			array('field' => 'CmpLpu_id','label' => 'ЛПУ куда доставлен','rules' => 'trim','type' => 'id'),
			array('field' => 'endDate','label' => 'Дата по','rules' => 'trim','type' => 'date'),
			array('field' => 'Search_BirthDay','label' => 'Дата рождения','rules' => '','type' => 'date'),
			array('field' => 'Search_FirName','label' => 'Имя','rules' => '','type' => 'string'),
			array('field' => 'Search_SecName','label' => 'Отчество','rules' => '','type' => 'string'),
			array('field' => 'Search_SurName','label' => 'Фамилия','rules' => '','type' => 'string')
		),
		'loadSMPDispatchDirectWorkPlace' => array(
			array('field' => 'begDate','label' => 'Дата с','rules' => 'trim|required','type' => 'date'),
			array('field' => 'hours','label' => 'За последние часы','rules' => 'trim','type' => 'string'),
			array('field' => 'dispatchCallPmUser_id','label' => 'Ид. диспетчера вызовов','rules' => 'trim','type' => 'string'),
			array('field' => 'EmergencyTeam_id','label' => 'Ид. бригады','rules' => 'trim','type' => 'id'),
			array('field' => 'CmpCallCard_Numv','label' => 'Номер вызова','rules' => 'trim','type' => 'string'),
			array('field' => 'CmpCallCard_Ngod','label' => 'Номер вызова за год','rules' => 'trim','type' => 'string'),
			array('field' => 'CmpLpu_id','label' => 'ЛПУ куда доставлен','rules' => 'trim','type' => 'id'),
			array('field' => 'endDate','label' => 'Дата по','rules' => 'trim','type' => 'date'),
			array('field' => 'Search_BirthDay','label' => 'Дата рождения','rules' => '','type' => 'date'),
			array('field' => 'Search_FirName','label' => 'Имя','rules' => '','type' => 'string'),
			array('field' => 'Search_SecName','label' => 'Отчество','rules' => '','type' => 'string'),
			array('field' => 'Search_SurName','label' => 'Фамилия','rules' => '','type' => 'string')
		),
		'loadSmpStacDiffDiagJournal' => array(
			array('field' => 'CmpCallCard_Numv','label' => 'Номер вызова за день','rules' => '','type' => 'int'),
			array('field' => 'begDate','label' => 'Дата с','rules' => 'trim|required','type' => 'date'),
			array('field' => 'endDate','label' => 'Дата по','rules' => 'trim','type' => 'date'),
			array('field' => 'diffDiagView','label' => 'Отображать только с разными диагнозами','rules' => 'trim','type' => 'string'),
			array('field' => 'Lpu_id','label' => 'МО','rules' => '','type' => 'id')
		),
		
		'loadSMPDispatchCallWorkPlace' => array(
			array('field' => 'begDate','label' => 'Дата с','rules' => 'trim|required','type' => 'date'),
			array('field' => 'hours','label' => 'За последние часы','rules' => 'trim','type' => 'string'),
			array('field' => 'CmpCallCard_Numv','label' => 'Номер вызова','rules' => 'trim','type' => 'string'),
			array('field' => 'CmpCallCard_Ngod','label' => 'Номер вызова за год','rules' => 'trim','type' => 'string'),
			array('field' => 'CmpLpu_id','label' => 'ЛПУ куда доставлен','rules' => 'trim','type' => 'id'),
			array('field' => 'endDate','label' => 'Дата по','rules' => 'trim','type' => 'date'),
			array('field' => 'Search_BirthDay','label' => 'Дата рождения','rules' => '','type' => 'date'),
			array('field' => 'Search_FirName','label' => 'Имя','rules' => '','type' => 'string'),
			array('field' => 'Search_SecName','label' => 'Отчество','rules' => '','type' => 'string'),
			array('field' => 'Search_SurName','label' => 'Фамилия','rules' => '','type' => 'string')
		),
		'loadSMPAdminWorkPlace' => array(
			array('field' => 'begDate','label' => 'Дата с','rules' => 'trim|required','type' => 'date'),
			array('field' => 'hours','label' => 'За последние часы','rules' => 'trim','type' => 'string'),
			array('field' => 'dispatchCallPmUser_id','label' => 'Ид. диспетчера вызовов','rules' => 'trim','type' => 'string'),
			array('field' => 'EmergencyTeam_id','label' => 'Ид. бригады','rules' => 'trim','type' => 'id'),
			array('field' => 'CmpCallCard_Numv','label' => 'Номер вызова','rules' => 'trim','type' => 'string'),
			array('field' => 'CmpCallCard_Ngod','label' => 'Номер вызова за год','rules' => 'trim','type' => 'string'),
			array('field' => 'displayDeletedCards','label' => 'Отображать удаленные карты','rules' => 'trim','type' => 'string'),
			array('field' => 'CmpLpu_id','label' => 'ЛПУ куда доставлен','rules' => 'trim','type' => 'id'),
			array('field' => 'endDate','label' => 'Дата по','rules' => 'trim','type' => 'date'),
			array('field' => 'Search_BirthDay','label' => 'Дата рождения','rules' => '','type' => 'date'),
			array('field' => 'Search_FirName','label' => 'Имя','rules' => '','type' => 'string'),
			array('field' => 'Search_SecName','label' => 'Отчество','rules' => '','type' => 'string'),
			array('field' => 'Search_SurName','label' => 'Фамилия','rules' => '','type' => 'string'),
			array('field' => 'LpuBuilding_id','label' => 'Подстанция','rules' => '','type' => 'id'),
			array('field' => 'CheckCard','label' => 'Карта проверена','rules' => '','type' => 'id'),
		),
		'loadSMPHeadDutyWorkPlace' => array(
			array('field' => 'begDate','label' => 'Дата с','rules' => 'trim|required','type' => 'date'),
			array('field' => 'hours','label' => 'За последние часы','rules' => 'trim','type' => 'string'),
			array('field' => 'dispatchCallPmUser_id','label' => 'Ид. диспетчера вызовов','rules' => 'trim','type' => 'string'),
			array('field' => 'EmergencyTeam_id','label' => 'Ид. бригады','rules' => 'trim','type' => 'id'),
			array('field' => 'CmpCallCard_Numv','label' => 'Номер вызова','rules' => 'trim','type' => 'string'),
			array('field' => 'CmpCallCard_Ngod','label' => 'Номер вызова за год','rules' => 'trim','type' => 'string'),
			array('field' => 'CmpLpu_id','label' => 'ЛПУ куда доставлен','rules' => 'trim','type' => 'id'),
			array('field' => 'endDate','label' => 'Дата по','rules' => 'trim','type' => 'date'),
			array('field' => 'Search_BirthDay','label' => 'Дата рождения','rules' => '','type' => 'date'),
			array('field' => 'Search_FirName','label' => 'Имя','rules' => '','type' => 'string'),
			array('field' => 'Search_SecName','label' => 'Отчество','rules' => '','type' => 'string'),
			array('field' => 'Search_SurName','label' => 'Фамилия','rules' => '','type' => 'string')
		),
		'loadSMPHeadBrigWorkPlace' => array(
			array('field' => 'begDate','label' => 'Дата с','rules' => 'trim|required','type' => 'date'),
			array('field' => 'endDate','label' => 'Дата по','rules' => 'trim','type' => 'date'),
			array('field' => 'MedPersonal_id','label' => 'Врач на участке','rules' => 'trim','type' => 'id'),
			array('field' => 'CmpCallCard_Numv','label' => 'Номер вызова','rules' => 'trim','type' => 'string'),
			array('field' => 'CmpCallCard_Ngod','label' => 'Номер вызова за год','rules' => 'trim','type' => 'string'),
			array('field' => 'CmpLpu_id','label' => 'ЛПУ куда доставлен','rules' => 'trim','type' => 'id'),
			array('field' => 'Search_BirthDay','label' => 'Дата рождения','rules' => '','type' => 'date'),
			array('field' => 'Search_FirName','label' => 'Имя','rules' => '','type' => 'string'),
			array('field' => 'Search_SecName','label' => 'Отчество','rules' => '','type' => 'string'),
			array('field' => 'Search_SurName','label' => 'Фамилия','rules' => '','type' => 'string')
		),	
		'checkEmergencyStandart' => array(
			array('field' => 'Diag_id','label' => 'Ид диагноза','rules' => 'required','type' => 'id'),
			array('field' => 'Person_id','label' => 'Ид пациента','rules' => 'required','type' => 'id')
		),
		'loadPPDWorkPlace' => array(
			array('field' => 'begDate','label' => 'Дата с','rules' => 'trim|required','type' => 'date'),
			array('field' => 'CmpCallCard_Numv','label' => 'Номер вызова','rules' => 'trim','type' => 'string'),
			array('field' => 'CmpCallCard_Ngod','label' => 'Номер вызова за год','rules' => 'trim','type' => 'string'),
			array('field' => 'CmpLpu_id','label' => 'ЛПУ куда доставлен','rules' => 'trim','type' => 'id'),
			array('field' => 'MedService_id','label' => 'Служба НМП','rules' => 'trim','type' => 'id'),
			array('field' => 'endDate','label' => 'Дата по','rules' => 'trim','type' => 'date'),
			array('field' => 'Search_BirthDay','label' => 'Дата рождения','rules' => '','type' => 'date'),
			array('field' => 'Search_FirName','label' => 'Имя','rules' => '','type' => 'string'),
			array('field' => 'Search_SecName','label' => 'Отчество','rules' => '','type' => 'string'),
			array('field' => 'Search_SurName','label' => 'Фамилия','rules' => '','type' => 'string')
		),
		'loadCmpIllegalActList' => array(),
		'loadCmpIllegalActForm' => array(
			array('field' => 'CmpIllegalAct_id','label' => 'Ид случая','rules' => 'required','type' => 'id')
		),
		'deleteCmpIllegalAct' => array(
			array('field' => 'CmpIllegalAct_id','label' => 'Идентификатор ckexfz','rules' => 'required','type' => 'id')
		),
		'findCmpIllegalAct' => array(
			array('field' => 'CmpIllegalAct_id','label' => 'Ид случая','rules' => '','type' => 'id'),
			array('field' => 'Lpu_id','label' => 'Ид МО','rules' => '','type' => 'id'),
			array('field' => 'CmpIllegalAct_prmDT', 'label' => 'Дата регистрации случая', 'rules' => '', 'type' => 'string'),
			array('field' => 'Person_id','label' => 'Ид пациента','rules' => '','type' => 'id'),
			array('field' => 'Address_Zip','label' => 'Индекс','rules' => '','type' => 'string'),
			array('field' => 'KLCountry_id','label' => 'Ид страны','rules' => '','type' => 'id'),
			array('field' => 'KLRgn_id','label' => 'Ид региона','rules' => '','type' => 'id'),
			array('field' => 'KLSubRGN_id','label' => 'Ид района','rules' => '','type' => 'id'),
			array('field' => 'KLCity_id','label' => 'Ид города','rules' => '','type' => 'id'),
			array('field' => 'PersonSprTerrDop_id','label' => 'Ид района города','rules' => '','type' => 'id'),
			array('field' => 'KLTown_id','label' => 'Ид нас. пункта','rules' => '','type' => 'id'),
			array('field' => 'KLTown_id','label' => 'Ид нас. пункта','rules' => '','type' => 'id'),
			array('field' => 'KLStreet_id','label' => 'Ид улицы','rules' => '','type' => 'id'),
			array('field' => 'Address_House','label' => 'Номер дома','rules' => '','type' => 'string'),
			array('field' => 'Address_Corpus','label' => 'Номер корпуса','rules' => '','type' => 'string'),
			array('field' => 'Address_Flat','label' => 'Номер квартиры','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_id','label' => 'Ид талона вызова','rules' => '','type' => 'id'),
			array('field' => 'CmpIllegalAct_Comment','label' => 'Комментарий','rules' => '','type' => 'string')
		),
		'saveCmpIllegalActForm' => array(
			array('field' => 'CmpIllegalAct_id','label' => 'Ид случая','rules' => '','type' => 'id'),
			array('field' => 'Lpu_id','label' => 'Ид МО','rules' => '','type' => 'id'),
			array('field' => 'CmpIllegalAct_prmDT', 'label' => 'Дата регистрации случая', 'rules' => '', 'type' => 'string'),
			array('field' => 'Person_id','label' => 'Ид пациента','rules' => '','type' => 'id'),
			array('field' => 'Address_Zip','label' => 'Индекс','rules' => '','type' => 'string'),
			array('field' => 'KLCountry_id','label' => 'Ид страны','rules' => '','type' => 'id'),
			array('field' => 'KLRgn_id','label' => 'Ид региона','rules' => '','type' => 'id'),
			array('field' => 'KLSubRGN_id','label' => 'Ид района','rules' => '','type' => 'id'),
			array('field' => 'KLCity_id','label' => 'Ид города','rules' => '','type' => 'id'),
			array('field' => 'PersonSprTerrDop_id','label' => 'Ид района города','rules' => '','type' => 'id'),
			array('field' => 'KLTown_id','label' => 'Ид нас. пункта','rules' => '','type' => 'id'),
			array('field' => 'KLTown_id','label' => 'Ид нас. пункта','rules' => '','type' => 'id'),
			array('field' => 'KLStreet_id','label' => 'Ид улицы','rules' => '','type' => 'id'),
			array('field' => 'Address_House','label' => 'Номер дома','rules' => '','type' => 'string'),
			array('field' => 'Address_Corpus','label' => 'Номер корпуса','rules' => '','type' => 'string'),
			array('field' => 'Address_Flat','label' => 'Номер квартиры','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_id','label' => 'Ид талона вызова','rules' => '','type' => 'id'),
			array('field' => 'CmpIllegalAct_Comment','label' => 'Комментарий','rules' => '','type' => 'string')
		),
		'loadLpuOperEnv' => array(
			array('field' => 'Lpu_ppdid','label' => 'Выбранное ЛПУ','rules' => 'int','type' => 'id'),
		),
		'saveCmpCallCloseCard' => array(
			array('field' => 'CmpCallCard_id','label' => 'Идентификатор карты вызова СМП','rules' => '','type' => 'id'),
			array('field' => 'Town_id','label' => 'Населенный пункт','rules' => '','type' => 'id')
		),
		'saveCmpCallCard' => array(
			array('field' => 'CmpArea_gid','label' => 'В каком районе госпитализирован','rules' => '','type' => 'id'),
			array('field' => 'CmpArea_id','label' => 'Код района (место вызова)','rules' => '','type' => 'id'),
			array('field' => 'CmpArea_pid','label' => 'Код района проживания','rules' => '','type' => 'id'),
			array('field' => 'CmpCallCard_City','label' => 'Населенный пункт (место вызова)','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Comm','label' => 'Дополнительная информация','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_D201','label' => 'Старший диспетчер смены','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Dlit','label' => 'Длительность приема вызова в сек.','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Dokt','label' => 'Фамилия старшего в бригаде','rules' => '','type' => 'string'),
			array('field' => 'MedPersonal_id','label' => 'Врач','rules' => '','type' => 'id'),
			array('field' => 'MedStaffFact_id','label' => 'Рабочее место врача','rules' => '','type' => 'id'),
			array('field' => 'CmpCallCard_IsMedPersonalIdent','label' => 'Признак идентификации','rules' => '','type' => 'id'),
			array('field' => 'CmpCallCard_Dom','label' => 'Дом (место вызова)','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Korp','label' => 'Корпус (место вызова)','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Room','label' => 'Комната (место вызова)','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Dsp1','label' => 'Принял','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Dsp2','label' => 'Назначил','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Dsp3','label' => 'Закрыл','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Dspp','label' => 'Передал','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Etaj','label' => 'Этаж (место вызова)','rules' => '','type' => 'int'),
			array('field' => 'CmpCallCard_Expo','label' => 'Экспертная оценка','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_id','label' => 'Идентификатор карты вызова СМП','rules' => '','type' => 'id'),
			array('field' => 'CmpCallCard_insID','label' => 'Идентификатор карты вызова СМП для вставки','rules' => '','type' => 'id'),
			array('field' => 'CmpCallCard_IsAlco','label' => 'Алкогольное (наркотическое) опьянение','rules' => '','type' => 'id'),
			array('field' => 'CmpCallCard_IsPoli','label' => 'Актив в поликлинику','rules' => '','type' => 'string'),
			array('field' => 'MedService_id', 'label' => 'Идентификатор службы НМП','rules' => '','type' => 'id'),
			array('field' => 'CmpCallCard_Izv1','label' => 'Извещение','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Kakp','label' => 'Как получен','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Kilo','label' => 'Километраж, затраченный на вызов','rules' => '','type' => 'float'),
			array('field' => 'CmpCallCard_Kodp','label' => 'Код замка в подъезде (домофон) (место вызова)','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Ktov','label' => 'Кто вызывает','rules' => '','type' => 'string'),
			array('field' => 'CmpCallerType_id','label' => 'Кто вызывает','rules' => '','type' => 'int'),
			array('field' => 'CmpCallCard_Kvar','label' => 'Квартира (место вызова)','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Line','label' => 'Пульт приема','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Ncar','label' => 'Номер машины','rules' => '','type' => 'int'),
			array('field' => 'CmpCallCard_Ngod','label' => 'Номер с начала года','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Numb','label' => 'Номер бригады','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Numv','label' => 'Номер вызова','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_NumvPr','label' => 'Признак вызова за день','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_NgodPr','label' => 'Признак вызова за год','rules' => '','type' => 'string'),
			array('field' => 'setDay_num','label' => 'Признак вызова за день','rules' => '','type' => 'string'),
			array('field' => 'setYear_num','label' => 'Признак вызова за год','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_PCity','label' => 'Населенный пункт (адрес проживания)','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_PDom','label' => 'Дом (адрес проживания)','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_PKvar','label' => 'Квартира (адрес проживания)','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Podz','label' => 'Подъезд (место вызова)','rules' => '','type' => 'int'),
			array('field' => 'CmpCallCard_Prdl','label' => 'Номер строки из списка предложений','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_prmDate','label' => 'Дата приема','rules' => '','type' => 'date'),
			array('field' => 'CmpCallCard_prmTime','label' => 'Время приема','rules' => '','type' => 'time'),
			array('field' => 'CmpCallCard_Prty','label' => 'Приоритет','rules' => '','type' => 'int'),
			array('field' => 'CmpCallCard_Przd','label' => 'Время прибытия на адрес','rules' => '','type' => 'datetime'),
			array('field' => 'CmpCallCard_PUlic','label' => 'Улица (адрес проживания)','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_RCod','label' => 'Код рации','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Sect','label' => 'Сектор','rules' => '','type' => 'int'),
			array('field' => 'CmpCallCard_Smpb','label' => 'Код станции СМП бригады','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Smpp','label' => 'Код ССМП приема вызова','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Smpt','label' => 'Код территориальной станции СМП','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Stan','label' => 'Номер П/С','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Stbb','label' => 'Номер П/С базирования бригады','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Stbr','label' => 'Номер П/С бригады по управлению','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Tab2','label' => 'Номер 1-го помощника','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Tab3','label' => 'Номер 2-го помощника','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Tab4','label' => 'Водитель','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_TabN','label' => 'Номер старшего в бригаде','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Telf','label' => 'Телефон','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Tgsp','label' => 'Время отзвона о госпитализации','rules' => '','type' => 'datetime'),
			array('field' => 'CmpCallCard_Tisp','label' => 'Время исполнения (освобождения)','rules' => '','type' => 'datetime'),
			array('field' => 'CmpCallCard_Tiz1','label' => 'Время передачи извещения','rules' => '','type' => 'datetime'),
			array('field' => 'CmpCallCard_Tper','label' => 'Время передачи','rules' => '','type' => 'datetime'),
			array('field' => 'CmpCallCard_Tsta','label' => 'Время прибытия в стационар','rules' => '','type' => 'datetime'),
			array('field' => 'CmpCallCard_Tvzv','label' => 'Время возвращения на станцию','rules' => '','type' => 'datetime'),
			array('field' => 'CmpCallCard_Ulic','label' => 'Улица','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Vr51','label' => 'Старший врач смены','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Vyez','label' => 'Время выезда','rules' => '','type' => 'datetime'),
			array('field' => 'CmpCallType_id','label' => 'Тип вызова','rules' => '','type' => 'id'),
			array('field' => 'CmpDiag_aid','label' => 'Диагноз (осложнение)','rules' => '','type' => 'id'),
			array('field' => 'CmpDiag_oid','label' => 'Диагноз (основной)','rules' => '','type' => 'id'),
			array('field' => 'CmpLpu_id','label' => 'Куда доставлен','rules' => '','type' => 'id'),
			array('field' => 'CmpPlace_id','label' => 'Местонахождение больного','rules' => '','type' => 'id'),
			array('field' => 'CmpProfile_bid','label' => 'Профиль бригады','rules' => '','type' => 'id'),
			array('field' => 'CmpProfile_cid','label' => 'Профиль вызова','rules' => '','type' => 'id'),
			array('field' => 'CmpReason_id','label' => 'Повод','rules' => '','type' => 'id'),
			array('field' => 'CmpReasonNew_id','label' => 'Повод расширенный','rules' => '','type' => 'id'),
			array('field' => 'CmpResult_id','label' => 'Результат','rules' => '','type' => 'id'),
			array('field' => 'ResultDeseaseType_id','label' => 'Исход','rules' => '','type' => 'id'),
			array('field' => 'LeaveType_id' , 'label'=>'LeaveType_id','rules'=>'','type'=>'id'),
			array('field' => 'CmpTalon_id','label' => 'Признак расхождения диагнозов или причина отказа стационара','rules' => '','type' => 'id'),
			array('field' => 'CmpTrauma_id','label' => 'Вид заболевания','rules' => '','type' => 'id'),
			array('field' => 'Diag_sid','label' => 'Диагноз стационара','rules' => '','type' => 'id'),
			array('field' => 'Diag_sopid','label' => 'Сопутствующий диагноз','rules' => '','type' => 'id'),
			array('field' => 'Diag_uid','label' => 'Уточненный код диагноза по МКБ-10','rules' => '','type' => 'id'),
			array('field' => 'Lpu_id','label' => 'ЛПУ создания вызова','rules' => '','type' => 'id'),
			array('field' => 'Lpu_hid','label' => 'МО госпитализации вызова','rules' => '','type' => 'id'),
			array('field' => 'LpuBuilding_id','label' => 'Подстанция','rules' => '','type' => 'id'),
			array('field' => 'CmpCallCard_IsNMP','label' => 'СМП/НМП','rules' => '','type' => 'id'),
			array('field' => 'CmpCallCard_IsExtra','label' => 'Вид вызова','rules' => '','type' => 'id'),
			array('field' => 'Lpu_ppdid','label' => 'ЛПУ передачи','rules' => '','type' => 'id'),
			array('field' => 'LpuTransmit_id','label' => 'ЛПУ передачи','rules' => '','type' => 'id'),
			array('field' => 'Lpu_oid','label' => 'Куда доставлен','rules' => '','type' => 'id'),
			array('field' => 'Person_Age','label' => 'Возраст','rules' => '','type' => 'int'),
			array('field' => 'Person_BirthDay','label' => 'Дата рождения','rules' => '','type' => 'date'),
			array('field' => 'Person_FirName','label' => 'Имя','rules' => '','type' => 'string'),
			array('field' => 'Person_id','label' => 'Идентификатор человека','rules' => '','type' => 'id'),
			array('field' => 'Polis_Ser','label' => 'Серия полиса','rules' => '','type' => 'string'),
			array('field' => 'Polis_Num','label' => 'Номер полиса','rules' => '','type' => 'string'),
			array('field' => 'Polis_EdNum','label' => 'Единый Номер','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_PolisEdNum','label' => 'Единый Номер','rules' => '','type' => 'string'),
			array('field' => 'Person_PolisSer','label' => 'Серия полиса','rules' => '','type' => 'string'),
			array('field' => 'Person_SecName','label' => 'Отчество','rules' => '','type' => 'string'),
			array('field' => 'Person_SurName','label' => 'Фамилия','rules' => '','type' => 'string'),
			array('field' => 'Sex_id','label' => 'Пол','rules' => '','type' => 'id'),
			array('field' => 'Person_IsUnknown','label' => 'Неизвестный','rules' => '','type' => 'id'),
			array('field' => 'CmpCallCard_IsOpen','label' => 'Открытая карта','rules' => '','type' => 'int','default' => 2),
			array('field' => 'KLRgn_id','label' => '','rules' => '','type' => 'id'),
			array('field' => 'KLSubRgn_id','label' => '','rules' => '','type' => 'id'),
			array('field' => 'KLCity_id','label' => '','rules' => '','type' => 'id'),
			array('field' => 'KLTown_id','label' => '','rules' => '','type' => 'id'),
			array('field' => 'KLStreet_id','label' => '','rules' => '','type' => 'id'),
			array('field' => 'ARMType','label' => '','rules' => '','type' => 'string'),
			array('field' => 'Person_isOftenCaller','label' => 'Индикатор нахождения в регистре часто обращающихся','rules' => '','type' => 'int'/*,'default' => 2*/),
			array('field' => 'CmpCallCard_Inf1','label' => '','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Inf2','label' => '','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Inf3','label' => '','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Inf4','label' => '','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Inf5','label' => '','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Inf6','label' => '','rules' => '','type' => 'string'),
			array('field' => 'UnformalizedAddressDirectory_id','label' => '','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_UlicSecond','label' => '','rules' => '','type' => 'int'),
			array('field' => 'UslugaComplex_id','label' => 'Идентификатор основной услуги','rules' => '','type' => 'id'),
			array('field' => 'CmpCallCardCostPrint_setDT','label' => 'Дата выдачи справки/отказа','rules' => '','type' => 'date'),
			array('field' => 'CmpCallCardCostPrint_IsNoPrint','label' => 'Отказ','rules' => '','type' => 'id'),
			array('field' => 'CmpCallCard_IndexRep','label' => 'Признак повторной подачи','rules' => '','type' => 'int'),
			array('field' => 'CmpCallCard_IndexRepInReg','label' => 'Признак повторной подачи в реестре','rules' => '','type' => 'int'),
			array('field' => 'RankinScale_id','label' => 'Значение по шкале Рэнкина','rules' => '','type' => 'id'),
			array('field' => 'usluga_array', 'label' => 'JSON-массив услуг', 'rules' => '', 'type' => 'json_array', 'assoc' => true ),
			array('field' => 'CmpCallPlaceType_id',	'label' => 'Идентификатор типа места вызова','rules' => '',	'type' => 'id'),
			array('field' => 'CmpCallCardInputType_id',	'label' => 'Идентификатор типа карты АДИС','rules' => '',	'type' => 'id'),
			array('field' => 'EmergencyTeam_id', 'label' => '','rules' => '','type' => 'id'),
			array('field' => 'Lpu_smpid', 'label' => 'МО передачи СМП','rules' => '','type' => 'id'),
			array('field' => 'CmpLeaveType_id', 'label' => 'Вид выезда','rules' => '','type' => 'id'),
			array('field' => 'CmpLeaveTask_id', 'label' => 'Задание','rules' => '','type' => 'id'),
			array('field' => 'CmpMedicalCareKind_id', 'label' => 'Вид мед. помощи','rules' => '','type' => 'id'),
			array('field' => 'CmpTransportType_id', 'label' => 'Вид транспорта','rules' => '','type' => 'id'),
			array('field' => 'CmpResultDeseaseType_id', 'label' => 'Исход','rules' => '','type' => 'id'),
			array('field' => 'CmpCallCardResult_id', 'label' => 'Исход','rules' => '','type' => 'id'),
			array('field' => 'CmpCallCard_IsPassSSMP', 'label' => 'Вызов передан в другую ССМП по телефону (рации)','rules' => '','type' => 'string'),
			array('field' => 'PayType_id', 'label' => 'Идентификатор вида оплаты', 'rules' => '', 'type' => 'id' ),
			array('field' => 'CmpCallCard_isControlCall', 'label' => 'Ид контрольного вызова', 'rules' => '', 'type' => 'id' ),
			array('field' => 'CmpRejectionReason_id', 'label' => 'Ид причины отказа', 'rules' => '', 'type' => 'id' ),
			array('field' => 'CmpCallCardRejection_Comm', 'label' => 'Комментарий к отказу', 'rules' => '', 'type' => 'string'),
			/** Анкета по КВИ (refs #198982)*/
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
		'printCmpCloseCard110' => array(
			array(
				'field' => 'CmpCallCard_id',
				'label' => 'Идентификатор карты',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'printCmpCallCardHeader' => array(
			array(
				'field' => 'CmpCallCard_id',
				'label' => 'Идентификатор карты',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'page',
				'label' => 'Номер страницы',
				'rules' => '',
				'type' => 'int'
			)
		),
		'printCmpCallCard' => array(
			array('field' => 'CmpCallCard_id','label' => 'Идентификатор карты','rules' => 'required','type' => 'id')
		),
		'loadCmpStation' => array(
			array('field' => 'Lpu_id','label' => 'Станция','rules' => '','type' => 'id')
		),	
		'getUslugaFields' => array(
			array('field' => 'acceptTime','label' => 'Время принятия вызова','rules' => '','type' => 'string'),
            array('field' => 'CmpCallCard_id','label' => 'Идентификатор карты','rules' => '','type' => 'int'),
            array('field' => 'UslugaComplex_Code','label' => 'Код услуги','rules' => '','type' => 'string'),
            array('field' => 'PayType_Code','label' => 'Код типа оплаты','rules' => '','type' => 'int')
		),
		'checkDuplicateCmpCallCard'=> array(
			array('field' => 'CmpCallCard_Comm','label' => 'Дополнительная информация','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Dom','label' => 'Дом (место вызова)','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Etaj','label' => 'Этаж (место вызова)','rules' => '','type' => 'int'),
			array('field' => 'CmpCallCard_id','label' => 'Идентификатор карты вызова СМП','rules' => '','type' => 'id'),
			array('field' => 'CmpCallCard_Kvar','label' => 'Квартира (место вызова)','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Podz','label' => 'Подъезд (место вызова)','rules' => '','type' => 'int'),
			array('field' => 'CmpCallCard_prmDate','label' => 'Дата приема','rules' => '','type' => 'date'),
			array('field' => 'CmpCallCard_prmTime','label' => 'Время приема','rules' => '','type' => 'time'),
			array('field' => 'CmpReason_id','label' => 'Повод','rules' => '','type' => 'id'),
			array('field' => 'Person_Age','label' => 'Возраст','rules' => '','type' => 'int'),
			array('field' => 'Person_id','label' => 'Ид пациента','rules' => '','type' => 'int'),
			array('field' => 'Person_BirthDay','label' => 'Дата рождения','rules' => '','type' => 'date'),
			array('field' => 'Person_FirName','label' => 'Имя','rules' => '','type' => 'string'),
			array('field' => 'Person_PolisSer','label' => 'Серия полиса','rules' => '','type' => 'string'),
			array('field' => 'Person_PolisNum','label' => 'Серия полиса','rules' => '','type' => 'string'),
			array('field' => 'Person_SecName','label' => 'Отчество','rules' => '','type' => 'string'),
			array('field' => 'Person_SurName','label' => 'Фамилия','rules' => '','type' => 'string'),
			array('field' => 'Sex_id','label' => 'Пол','rules' => '','type' => 'id'),
			array('field' => 'CmpCallCard_IsOpen','label' => 'Открытая карта','rules' => '','type' => 'int', 'default' => 2),
			array('field' => 'KLSubRgn_id','label' => '','rules' => '','type' => 'id'),
			array('field' => 'KLCity_id','label' => '','rules' => '','type' => 'id'),
			array('field' => 'KLTown_id','label' => '','rules' => '','type' => 'id'),
			array('field' => 'KLStreet_id','label' => '','rules' => '','type' => 'id'),
			array('field' => 'ComboLoad','label' => '','rules' => '','type' => 'id'),
			array('field' => 'CmpCallCard_Numv','label' => 'Номер вызова','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_Ngod','label' => 'Номер вызова за год','rules' => 'trim','type' => 'string')
		),
		'loadIllegalActCmpCards'=> array(
			array('field' => 'CmpCallCard_Dom','label' => 'Дом (место вызова)','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_id','label' => 'Идентификатор карты вызова СМП','rules' => '','type' => 'id'),
			array('field' => 'CmpCallCard_Kvar','label' => 'Квартира (место вызова)','rules' => '','type' => 'string'),
			array('field' => 'CmpCallCard_prmDate','label' => 'Дата приема','rules' => '','type' => 'date'),
			array('field' => 'Person_id','label' => 'Ид пациента','rules' => '','type' => 'int'),
			array('field' => 'KLSubRgn_id','label' => '','rules' => '','type' => 'id'),
			array('field' => 'KLCity_id','label' => '','rules' => '','type' => 'id'),
			array('field' => 'KLTown_id','label' => '','rules' => '','type' => 'id'),
			array('field' => 'KLStreet_id','label' => '','rules' => '','type' => 'id')
		),
		'setStatusCmpCallCard' => array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'CmpCallCardStatusType_id', 'label' => 'Устанавливаемый статус', 'rules' => '', 'type' => 'int' ),
			array('field' => 'CmpCallCardStatus_Comment', 'label' => 'Комментарий к статусу', 'rules' => '', 'type' => 'string' ),
			array('field' => 'CmpMoveFromNmpReason_id', 'label' => 'Ид. причины передачи из НМП в СМП', 'rules' => '', 'type' => 'id' ),
			array('field' => 'CmpReturnToSmpReason_id', 'label' => 'Ид. причины возврата в СМП из НМП', 'rules' => '', 'type' => 'id' ),
			array('field' => 'CmpCallCard_IsOpen', 'label' => 'Признак открытия карты', 'rules' => '', 'type' => 'int' ),
			array('field' => 'MedService_id', 'label' => 'Ид службы НМП', 'rules' => '', 'type' => 'int' ),
			array('field' => 'armtype', 'label' => 'Тип АРМ', 'rules' => '', 'type' => 'string' ),
			array('field' => 'CmpReason_id', 'label' => 'Причина отказа', 'rules' => '', 'type' => 'int' ),
			array('field' => 'CmpCallCard_isNMP', 'label' => 'Передача в НМП', 'rules' => '', 'type' => 'int' )
		),
		'setResult' => array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'CmpPPDResult_id', 'label' => 'Результат', 'rules' => '', 'type' => 'int' ),
			array('field' => 'CmpPPDResult_Code', 'label' => 'Код результата', 'rules' => '', 'type' => 'int' )
		),
		'setLpuTransmit' => array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'Lpu_ppdid', 'label' => 'ЛПУ передачи', 'rules' => 'required', 'type' => 'int' ) // может передаваться 0
		),
		'setEmergencyTeam' => array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'EmergencyTeam_id', 'label' => 'Назначенная бригада', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'Person_id', 'label' => 'ID пациента', 'rules' => '', 'type' => 'int' ),
			array('field' => 'Person_FIO', 'label' => 'ФИО пациента', 'rules' => '', 'type' => 'string' ),
			array('field' => 'Person_Firname', 'label' => 'Имя пациента', 'rules' => '', 'type' => 'string' ),
			array('field' => 'Person_Secname', 'label' => 'Отчество пациента', 'rules' => '', 'type' => 'string' ),
			array('field' => 'Person_Surname', 'label' => 'Фамилия пациента', 'rules' => '', 'type' => 'string' ),
			array('field' => 'Person_Birthday', 'label' => 'Дата рождения пациента', 'rules' => '', 'type' => 'string' ),
			array('field' => 'CmpCallCard_prmDate', 'label' => 'Дата принятия вызова', 'rules' => 'required', 'type' => 'string' ),
			array('field' => 'CmpReason_Name', 'label' => 'Повод вызова', 'rules' => '', 'type' => 'string' ),
			array('field' => 'Adress_Name', 'label' => 'Адрес вызова', 'rules' => '', 'type' => 'string' ),
			array('field' => 'CmpCallType_Name', 'label' => 'Тип вызова', 'rules' => '', 'type' => 'string' )
		),
		'setEmergencyTeamWithoutSending'=>array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'EmergencyTeam_id', 'label' => 'Назначенная бригада', 'rules' => 'required', 'type' => 'id' ),
		),
		'setPerson' => array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'Person_id', 'label' => 'Человек', 'rules' => 'required', 'type' => 'id' )
		),
		'identifiPerson' => array(
			array('field' => 'Person_Surname', 'label' => 'Фамилия', 'rules' => '', 'type' => 'string' ),
			array('field' => 'Person_Firname', 'label' => 'Имя', 'rules' => '', 'type' => 'string' ),
			array('field' => 'Person_Secname', 'label' => 'Отчество', 'rules' => '', 'type' => 'string' ),
			array('field' => 'Person_Birthday', 'label' => 'Д/р', 'rules' => '', 'type' => 'date' ),
			array('field' => 'Person_Age', 'label' => 'Возраст', 'rules' => '', 'type' => 'int' ),
			array('field' => 'Polis_Ser', 'label' => 'Серия полиса', 'rules' => '', 'type' => 'string' ),
			array('field' => 'Polis_Num', 'label' => 'Номер полиса', 'rules' => '', 'type' => 'int' ),
			array('field' => 'Sex_id', 'label' => 'Пол', 'rules' => '', 'type' => 'id' )
		),
		'unrefuseCmpCallCard' => array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' )
		),
		'getCombox' => array(
			array('field' => 'combo_id', 'label' => 'Имя поля', 'rules' => 'required', 'type' => 'string' )
		),
		'getComboValuesList' => array(
			array('field' => 'ComboSys', 'label' => 'ComboSys комбика', 'rules' => '', 'type' => 'string' ),
			array('field' => 'CmpCloseCardCombo_Code', 'label' => 'Код комбика', 'rules' => '', 'type' => 'string' )
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
			array('field' => 'Drug_id','label' => 'Идентификатор медикамента','rules' => 'required','type' => 'id'),
			array('field' => 'CmpFarmacyBalance_PackRest','label' => 'Остаток после списания (ед. уч.)','rules' => 'required','type' => 'float'),
			array('field' => 'CmpFarmacyBalance_DoseRest','label' => 'Остаток после списания (ед. доз.)','rules' => 'required','type' => 'float'),
			array('field' => 'CmpFarmacyBalanceRemoveHistory_PackCount','label' => 'Списываемое количество (ед. уч.)','rules' => 'required','type' => 'float'),
			array('field' => 'CmpFarmacyBalanceRemoveHistory_DoseCount','label' => 'Списываемое количество (ед. доз.)','rules' => 'required','type' => 'float'),
			array('field' => 'EmergencyTeam_id','label' => 'Идентификатор бригады','rules' => 'required','type' => 'id'),
		),
		'loadCmpCloseCardComboboxesViewForm' => array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' )
		),
		'loadCmpCloseCardEquipmentViewForm' => array(
			array( 'field' => 'CmpCloseCard_id', 'label' => 'Идентификатор карты закрытия вызова', 'rules' => 'required', 'type' => 'id' )
		),
		'saveUnformalizedAddress' =>array(
			array('field' => 'KLRgn_id','label' => '','rules' => '','type' => 'id'),
			array('field' => 'KLSubRgn_id','label' => '','rules' => '','type' => 'id'),
			array('field' => 'KLCity_id','label' => '','rules' => '','type' => 'id'),
			array('field' => 'KLTown_id','label' => '','rules' => '','type' => 'id'),
			array('field' => 'KLStreet_id','label' => '','rules' => '','type' => 'id'),
			array('field' => 'UnformalizedAddressDirectory_id','label' => 'Идентификатор элемента справочника', 'rules' => '','type' => 'id'),
			array('field' => 'UnformalizedAddressDirectory_Dom','label' => 'Дом (место вызова)','rules' => '','type' => 'string'),
			array('field' => 'UnformalizedAddressDirectory_Name','label' => 'Название','rules' => 'trim|required','type' => 'string'),
			array('field' => 'UnformalizedAddressDirectory_lng','label' => 'Долгота','rules' => 'required','type' => 'string'),
			array('field' => 'UnformalizedAddressDirectory_lat','label' => 'Широта','rules' => 'required','type' => 'string'),
		),
		'loadUnformalizedAddressDirectory'=>array(
			array('default' => 100,'field' => 'limit','label' => 'Количество','rules' => 'trim','type' => 'int'),
			array('default' => 0,'field' => 'start','label' => 'Старт','rules' => 'trim','type' => 'int')
		),
		'loadStreetsAndUnformalizedAddressDirectoryCombo' => array(
			array('field' => 'StreetAndUnformalizedAddressDirectory_id','label' => '','rules' => '','type' => 'string'),
			array('field' => 'town_id','label' => '','rules' => '','type' => 'id')
		),
		'deleteUnformalizedAddress' =>array(
			array('field' => 'UnformalizedAddressDirectory_id','label' => 'Идентификатор элемента справочника', 'rules' => 'required','type' => 'id')
		),
		'getPmUserInfo' => array(),
		'getLpuWithOperSmp' => array(),
		'loadLpuHomeVisit' => array(
			array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => '', 'type' => 'int')
		),
		'addHomeVisitFromSMP' => array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'ComboValue_693', 'label' => 'Ид. поликлиники', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'Person_id', 'label' => 'Ид. пациента', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'ComboValue_710', 'label' => 'Квартира', 'rules' => '', 'type' => 'string' ),
			array('field' => 'ComboValue_708', 'label' => 'Дом', 'rules' => '', 'type' => 'string' ),
			array('field' => 'HomeVisit_Phone', 'label' => 'Телефон', 'rules' => '', 'type' => 'string' ),
			array('field' => 'ComboValue_694', 'label' => 'Дата вызова на дом d.m.Y H:i', 'rules' => '', 'type' => 'string' ),
			array('field' => 'ComboValue_705', 'label' => 'KLCity_id', 'rules' => '', 'type' => 'id' ),
			array('field' => 'ComboValue_703', 'label' => 'KLRgn_id', 'rules' => '', 'type' => 'id' ),
			array('field' => 'ComboValue_707', 'label' => 'KLStreet_id', 'rules' => '', 'type' => 'id' ),
			array('field' => 'ComboValue_711', 'label' => 'Address_Address', 'rules' => '', 'type' => 'string' )
		),

		'lockCmpCallCard' => array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' )
		),
		'unlockCmpCallCard' => array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' )
		),
		'checkLockCmpCallCard' => array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' )
		),
		'getCmpCallCardSmpInfo' => array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' )
		),
		'getCmpCallCardNumber' => array(
			array('field' => 'CmpCallCard_prmDT', 'label' => 'Дата вызова', 'rules' => '', 'type' => 'string'),
			array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => '', 'type' => 'int')
		),
		'getLpuAddressTerritory' => array(),
		'getAddressForOsmGeocode' =>array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' )
		),
		'getAddressForNavitel' =>array(
			array('field' => 'CmpCallCard_id', 'label' => 'Ид. карты вызова', 'rules' => 'required', 'type' => 'id' )
		),
		'getYandexGeocode' =>array(
			array('field' => 'Address_Name', 'label' => 'Адрес геокодирования', 'rules' => 'required', 'type' => 'string' )
		),
		'getDispatchCallUsers' =>array(),
		
		'getCmpCallCardAddress' => array(
			array(
				'field' => 'CmpCallCard_id',
				'label' => 'Идентификатор карты вызова СМП',
				'rules' => '',
				'type' => 'id'
			)
		),
		'getUnformalizedAddressStreetKladrParams' => array(
			array('field' => 'administrative_area_level_1', 'label' => 'Территория', 'rules' => 'required', 'type' => 'string' ),
			array('field' => 'administrative_area_level_2', 'label' => 'Город/Район', 'rules' => 'required', 'type' => 'string' ),
			array('field' => 'route', 'label' => 'Улица', 'rules' => 'required', 'type' => 'string' ),
			array('field' => 'street_number', 'label' => 'Номер дома', 'rules' => 'required', 'type' => 'string' )
		),
		'saveDecigionTree' => array(
			array('field' => 'data', 'label' => 'Значения', 'rules' => 'required', 'type' => 'string' ),
		),
		'getDecigionTree' => array(
			array('field' => 'concreteTree', 'label' => 'Конкретное дерево', 'rules' => '', 'type' => 'boolean'),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'AmbulanceDecigionTreeRoot_id', 'label' => 'Идентификатор кореня дерева решений', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id')
		),
		'copyDecigionTree'=> array(
			array('field' => 'Region_id', 'label' => 'Регион', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
			array('field' => 'AmbulanceDecigionTreeRoot_id', 'label' => 'ИД деревеа решений', 'rules' => 'required', 'type' => 'id'),
		),
		'createDecigionTree'=> array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id')
		),
		'getStructuresTree' => array(
			array('field' => 'adminRegion', 'label' => 'Админ регоина', 'rules' => '', 'type' => 'boolean')
		),
		'getStructuresIssetTree' => array(
			array('field' => 'level', 'label' => 'Уровень дерева', 'rules' => '', 'type' => 'string'),
		),
		'saveDecigionTreeNode' => array(
			array('field' => 'AmbulanceDecigionTree_id', 'label' => 'Идентификатор ноды дерева решений', 'rules' => '', 'type' => 'id'),
			array('field' => 'AmbulanceDecigionTree_nodeid', 'label' => 'Идентификатор еще ноды дерева решений', 'rules' => '', 'type' => 'int'),
			array('field' => 'AmbulanceDecigionTree_nodepid', 'label' => 'Идентификатор еще родителя ноды дерева решений', 'rules' => '', 'type' => 'int'),
			array('field' => 'AmbulanceDecigionTree_Type', 'label' => 'Идентификатор типа ноды дерева решений', 'rules' => '', 'type' => 'int'),
			array('field' => 'AmbulanceDecigionTree_Text', 'label' => 'Описание еще родителя ноды дерева решений', 'rules' => '', 'type' => 'string'),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подстанции', 'rules' => '', 'type' => 'id'),
			array('field' => 'AmbulanceDecigionTreeRoot_id', 'label' => 'Идентификатор корня дерева решений', 'rules' => '', 'type' => 'id'),
			array('field' => 'CmpReason_id', 'label' => 'Повод ноды дерева решений', 'rules' => '', 'type' => 'int')
		),
		'deleteDecigionTreeNode' => array(
			array('field' => 'AmbulanceDecigionTree_id', 'label' => 'Идентификатор ноды дерева решений', 'rules' => '', 'type' => 'id')
		),
		'getCmpRangeReasonList' => array(),
		'saveCmpRangeReasonList' => array(
			array(
				'field' => 'SmpCallRange',
				'label' => 'Вызовы ранжированные',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadSmpUnits' => array(
			array('field'=>'showOperDpt','label'=>'Отображать опер. отдел','rules'=>'','type'=>'int'),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор ЛПУ', 'rules' => '', 'type' => 'id'),
			array('field' => 'SmpUnitType_Code', 'label' => 'тип подстанции', 'rules' => '', 'type' => 'string'),
			array('field' => 'form', 'label' => 'форма', 'rules' => '', 'type' => 'string')
		),
		'loadLpuCmpUnits' => array(),
		'initiateProposalLogicForLpu'=>array(
			array('field' => 'Lpu_id','label' => 'ид лпу','rules' => '','type' => 'int')
		),
		'getCmpCallPlaces'=>array(
			array('field'=>'CmpUrgencyAndProfileStandart_id','label'=>'Ид. правила','rules'=>'','type'=>'id')
		),
		'getCmpUrgencyAndProfileStandart'=>array(
			array('default' => 100,'field' => 'limit','label' => 'Количество','rules' => 'trim','type' => 'int'),
			array('default' => 0,'field' => 'start','label' => 'Старт','rules' => 'trim','type' => 'int'),
			array('field' => 'Lpu_id','label' => 'ид лпу','rules' => '','type' => 'int'),
			array('field' => 'CmpCallCardAcceptor_id','label' => 'ид типа приема вызова','rules' => '','type' => 'int'),
		),
		'deleteCmpUrgencyAndProfileStandartRule'=>array(
			array('field' => 'CmpUrgencyAndProfileStandart_id', 'label' => 'Ид. правила', 'rules' => 'required', 'type' => 'id' )
		),
		'getCmpUrgencyAndProfileStandartPlaces'=>array(
			array('field' => 'CmpUrgencyAndProfileStandart_id', 'label' => 'Ид. правила', 'rules' => 'required', 'type' => 'id' )
		),
		'getCmpUrgencyAndProfileStandartSpecPriority'=>array(
			array('field' => 'CmpUrgencyAndProfileStandart_id', 'label' => 'Ид. правила', 'rules' => '', 'type' => 'id' )
		),
		'saveCmpUrgencyAndProfileStandartRule'=>array(
			array('field' => 'Lpu_id', 'label' => 'ИД МО', 'rules' => '', 'type' => 'string'),
			array('field' => 'CmpUrgencyAndProfileStandart_id', 'label' => 'Ид. правила', 'rules' => '', 'type' => 'id' ),
			array('field' => 'CmpReason_id', 'label' => 'Причина отказа', 'rules' => 'required', 'type' => 'int' ),
			array('field' => 'CmpUrgencyAndProfileStandart_Urgency', 'label' => 'Базовая срочность', 'rules' => 'required', 'type' => 'int' ),
			array('field' => 'CmpUrgencyAndProfileStandart_UntilAgeOf', 'label' => 'Возраст', 'rules' => '', 'type' => 'int' ),
			array('field' => 'CmpCallPlaceType_jsonArray', 'label' => 'Список мест', 'rules' => 'required', 'type' => 'string' ),
			array('field' => 'CmpUrgencyAndProfileStandartRefSpecPriority_jsonArray', 'label' => 'Профили и приоритеты', 'rules' => 'required', 'type' => 'string' ),
			array('field' => 'CmpCallCardAcceptor_id', 'label' => 'Тип приёма вызова', 'rules' => '', 'type' => 'int' ),
			array('field' => 'CmpUrgencyAndProfileStandart_HeadDoctorObserv', 'label' => 'Требуется наблюдение старшим врачем', 'rules' => '', 'type' => 'int', 'default' => 1 ),
			array('field' => 'CmpUrgencyAndProfileStandart_MultiVictims', 'label' => 'Несколько пострадавших', 'rules' => '', 'type' => 'int', 'default' => 1 ),
		),
		'saveCmpCallCardUsluga' => array(
			array('field' => 'CmpCallCardUsluga_id', 'label' => 'Идентификатор услуги в карте вызова СМП', 'rules' => '', 'type' => 'id' ),
			array('field' => 'CmpCallCard_id', 'label' => 'Идентификатор карты вызова СМП', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'CmpCallCardUsluga_setDate', 'label' => 'Дата выполнения', 'rules' => 'required', 'type' => 'date' ),
			array('field' => 'CmpCallCardUsluga_setTime', 'label' => 'Время выполнения', 'rules' => 'required', 'type' => 'time' ),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор врача', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'PayType_id', 'label' => 'Идентификатор вида оплаты', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'UslugaCategory_id', 'label' => 'Идентификатор категории услугии', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'UslugaComplex_id', 'label' => 'Идентификатор услуги', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'UslugaComplexTariff_id', 'label' => 'Идентификатор тарифа', 'rules' => '', 'type' => 'id' ),
			array('field' => 'CmpCallCardUsluga_Cost', 'label' => 'Цена', 'rules' => '', 'type' => 'float' ),
			array('field' => 'CmpCallCardUsluga_Kolvo', 'label' => 'Количество', 'rules' => '', 'type' => 'int' )
		),
		'loadCmpCallCardUslugaGrid' => array(
			array('field' => 'CmpCallCard_id', 'label' => 'Идентификатор карты вызова СМП', 'rules' => 'required', 'type' => 'id' )
		),
		'getCmpCallDiagnosesFields' => array(
			array('field' => 'CmpCallCard_id', 'label' => 'Идентификатор карты вызова СМП', 'rules' => 'required', 'type' => 'id' )
		),
		'loadCmpCallCardUslugaForm' => array(
			array('field' => 'CmpCallCardUsluga_id', 'label' => 'Идентификатор услуги в карте вызова СМП', 'rules' => 'required', 'type' => 'id' )
		),
		'loadCmpEquipmentCombo' => array (),
		'loadCmpCallCardAcceptorList' => array(),
		'loadCmpCallCardDrugList' => array(
            array('field' => 'CmpCallCard_id', 'label' => 'Идентификатор карты вызова СМП', 'rules' => 'required', 'type' => 'id' )
        ),
		'loadCmpCallCardEvnDrugList' => array(
			array('field' => 'CmpCallCard_id', 'label' => 'Идентификатор карты вызова СМП', 'rules' => 'required', 'type' => 'id' )
		),
		'loadCmpCallCardSimpleDrugList' => array(
			array('field' => 'CmpCallCard_id', 'label' => 'Идентификатор карты вызова СМП', 'rules' => 'required', 'type' => 'id' )
		),
		'getSidOoidDiags' => array(
			array('field' => 'CmpCloseCard_id', 'label' => 'Идентификатор карты вызова СМП', 'rules' => 'required', 'type' => 'id' )
		),
        'loadMedStaffFactCombo' => array(
            array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
            array('field' => 'Lpu_id', 'label' => 'Идентификатор ЛПУ', 'rules' => '', 'type' => 'id'),
            array('field' => 'EmergencyTeam_id', 'label' => 'Идентификатор бригады', 'rules' => '', 'type' => 'id'),
            array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
        ),
        'loadLpuBuildingCombo' => array(
            array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
            array('field' => 'Lpu_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
            array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
        ),
        'loadStorageCombo' => array(
            array('field' => 'Storage_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
            array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
            array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
        ),
        'loadMolCombo' => array(
            array('field' => 'Mol_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
            array('field' => 'Storage_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
            array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
        ),
        'loadStorageZoneCombo' => array(
            array('field' => 'StorageZone_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
            array('field' => 'Storage_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
            array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
        ),
        'loadDrugPrepFasCombo' => array(
            array('field' => 'DrugPrepFas_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
            array('field' => 'Storage_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
            array('field' => 'StorageZone_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
            array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
        ),
        'loadDrugCombo' => array(
            array('field' => 'Drug_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
            array('field' => 'Storage_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
            array('field' => 'StorageZone_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
            array('field' => 'DrugPrepFas_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
            array('field' => 'DrugShipment_setDT_max', 'label' => 'Максимальная дата партии', 'rules' => '', 'type' => 'date'),
            array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
        ),
        'loadDocumentUcStrOidCombo' => array(
            array('field' => 'DocumentUcStr_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
            array('field' => 'Drug_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
            array('field' => 'Storage_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
            array('field' => 'StorageZone_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
            array('field' => 'DrugShipment_setDT_max', 'label' => 'Максимальная дата партии', 'rules' => '', 'type' => 'date'),
            array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
        ),
        'loadGoodsUnitCombo' => array(
            array('field' => 'GoodsUnit_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
            array('field' => 'Drug_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
            array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
        ),
		'loadCmpCallCardList' => array(
			array('field' => 'CmpCallCard_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
			array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string'),
			array('field' => 'date', 'label' => 'Дата поиска', 'rules' => '', 'type' => 'date'),
		),
        'getCmpCallCardDrugDefaultValues' => array(
            array('field' => 'EmergencyTeam_id', 'label' => 'Идентификатор бригады', 'rules' => 'required', 'type' => 'id')
        ),
		'setCmpCallCardEvent' => array(
			array('field' => 'CmpCallCard_id', 'label' => 'Идентификатор карты', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'CmpCallCardEventType_Code', 'label' => 'Код статуса', 'rules' => '', 'type' => 'id'),
			array('field' => 'CmpCallCardEventType_id', 'label' => 'Идентификатор статуса', 'rules' => '', 'type' => 'id'),
			array('field' => 'CmpCallCardEvent_Comment', 'label' => 'Комментарий', 'rules' => '', 'type' => 'string')
		),
		'checkDiagFinance' => array(
			array('field' => 'Diag_id', 'label' => 'Идентификатор диагноза', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PayType_id', 'label' => 'Тип оплаты', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id'),
			array('field' => 'Age', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'int'),
			array('field' => 'Sex_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id'),
		),
		'printCmpCall' => array(
            array('field' => 'CmpCallCard_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id')
		),
		'loadRegionSmpUnits' => array(),
		'autoCreateCmpPerson' => array(),
		'existenceNumbersDayYear' => array(
			array('field' => 'Day_num', 'label' => 'искомый номер за день', 'rules' => '', 'type' => 'int'),
			array('field' => 'Year_num', 'label' => 'искомый номер за год', 'rules' => '', 'type' => 'int'),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор ЛПУ', 'rules' => '', 'type' => 'id'),
			array('field' => 'AcceptTime', 'label' => 'дата', 'rules' => '', 'type' => 'string'),
			array('field' => 'CmpCallCard_id', 'label' => 'Идентификатор вызова', 'rules' => '', 'type' => 'int')
		),
		'getExpertResponseFields' => array(),
		'getCmpCloseCardExpertResponses' => array(
			array('field' => 'CmpCloseCard_id', 'label' => 'Идентификатор карты', 'rules' => 'required', 'type' => 'id')
		),
		'getFedLeaveTypeList' => array(),
		'getTheDistanceInATimeInterval' => array(
			array('field' => 'EmergencyTeam_id', 'label' => 'Идентификатор бригады', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'GoTime', 'label' => 'Время выезда на вызов', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EndTime', 'label' => 'Время окончания вызова', 'rules' => 'required', 'type' => 'string')
		),
		'getDatesToNumbersDayYear' => array(
			array('field' => 'CmpCallCard_prmDT', 'label' => 'Время приема вызова', 'rules' => '', 'type' => 'string')
		),
		'saveCmpCloseCardExpertResponseList' => array(
			array('field' => 'CmpCloseCard_id', 'label' => 'Идентификатор карты','type' => 'id','rules' => 'required'),
			array('field' => 'ExpertResponseJSON', 'label' => 'json массив экспертных оценок','rules' => 'required', 'type' => 'string')
		),
		'getPatientDiffList' => array(
			array('field' => 'lpu_id', 'label' => 'МО', 'rules' => '', 'type' => 'string'),
			array('field' => 'begDate','label' => 'Дата с','rules' => 'trim','type' => 'date'),
			array('field' => 'endDate','label' => 'Дата по','rules' => 'trim','type' => 'date'),
		),
		'getSmoQueryCallCards' => array(
			array('field' => 'OrgSmo_id','label' => 'СМО ИД','rules' => 'required','type' => 'int')
		),
		'setSmoQueryCallCards' => array(
				array('field' => 'OrgSmo_id', 'label'=> 'СМО ИД', 'rules' => 'required', 'type' => 'int'  ),
				array('field' => 'Lpu_id', 'label'=> 'МО ИД', 'rules' => 'required', 'type' => 'int'  ),
				array('field' => 'CardNumber', 'label'=> 'Номер карты', 'rules' => '', 'type' => 'string'  ),
				array('field' => 'pmUser_id', 'label' => 'ид пользователя','rules' => '', 'type' => 'int'),
				array('field' => 'insDT', 'label' => 'Время импорта', 'rules' => '', 'type' => 'string')
		),
		'delSmoQueryCallCards' => array(
				array('field' => 'OrgSmo_id', 'label' => 'СМО ИД', 'rules' => 'required', 'type' => 'int')
		),
		'saveSmoQuery' => array(
				array('field' => 'jsondata', 'label' => 'jsondata', 'rules' => 'required', 'type' => 'string')
		),
		'getIsCallControllFlag' => array()
	);
	
	/**
	 * default desc
	 */
	
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('CmpCallCard_model', 'dbmodel');
	}
	
	/**
	* Получение списка найденных записей для журнала карт СМП
	*/
	
	function loadCmpCallCardJournalGrid() {
		$data = $this->ProcessInputData('loadCmpCallCardJournalGrid', true);
		
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadCmpCallCardJournalGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}


	/**
	*  Удаление карты вызова
	*  Входящие данные: $_POST['CmpCallCard_id']
	*  На выходе: JSON-строка
	*  Используется: форма поиска карт вызова
	*/
	function deleteCmpCallCard() {
		$data = $this->ProcessInputData('deleteCmpCallCard', true);
		if ( $data === false ) { return false; }

        $this->dbmodel->beginTransaction() ;

		$response = $this->dbmodel->deleteCmpCallCard($data);
        if (!empty($response[0]['Error_Msg'])) {
            $this->dbmodel->rollbackTransaction() ;
            $this->ReturnError($response[0]['Error_Msg']);
            return false;
        }

        $this->dbmodel->commitTransaction() ;

		$this->ProcessModelSave($response, true, true)->ReturnData();

		return true;
	}

	/**
	*  Для теста импорта карт СМП
	*/
	function importSMPCardsTest() {
		$this->db = null;
		$this->load->database('registry');
				
		$data = $this->ProcessInputData('importSMPCardsTest', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->importSMPCardsTest($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();

		return true;
	}
	/**
	 * default desc
	 */
	function deleteUnformalizedAddress() {
		$data = $this->ProcessInputData('deleteUnformalizedAddress', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->deleteUnformalizedAddress($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * default desc
	 */
	function deleteCmpIllegalAct() {
		$data = $this->ProcessInputData('deleteCmpIllegalAct', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->deleteCmpIllegalAct($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();

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
			$this->load->model('ApplicationCVI_model', 'ApplicationCVI_model');
			$params = [
				'CmpCallCard_id' => isset($response[0]['CmpCallCard_id']) ? $response[0]['CmpCallCard_id'] : null
			];
			$anketaCVIdata = $this->ApplicationCVI_model->doLoadData($params);

			if (!empty($anketaCVIdata) && isset($anketaCVIdata[0])) {
				$response[0] = array_merge($response[0], $anketaCVIdata[0]);
			}
			$val = $response;
			array_walk($val[0], 'ConvertFromWin1251ToUTF8');
		}
		
		$this->ReturnData($val);
		
		return true;
	}

	/**
	*  Получение данных для формы редактирования карты закрытия вызова
	*  Входящие данные: $_POST['CmpCallCard_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования карты вызова
	*/
	function loadCmpCloseCardEditForm() {
		$data = array();
		$val  = array();
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadCmpCallCardEditForm', true);
		
		if (!$data) {
			return false;
		}
		
		$response = $this->dbmodel->loadCmpCloseCardEditForm($data);
		

		if ( is_array($response) && count($response) > 0 ) {
			$val = $response;
			array_walk($val[0], 'ConvertFromWin1251ToUTF8');
		}
		$this->ReturnData($val);

		return true;
	}
	
	/**
	 * Возвращает данные карты закрытия вызова 110у
	 */
	public function loadCmpCloseCardViewForm() {
		$data = $this->ProcessInputData( 'loadCmpCloseCardViewForm', true );
		if ( $data === false ) {
			return false;
		}

		if ( empty($data['CmpCallCard_id']) && empty($data['CmpCloseCard_id']) ) {
			$this->ReturnError('Не указан идентификатор карты');
			return false;
		}
		
		if($data['delDocsView'] && $data['delDocsView'] == 1)
			$response = $this->dbmodel->loadCmpCloseCardViewFormForDelDocs($data);
		else
			$response = $this->dbmodel->loadCmpCloseCardViewForm($data);

		$this->ProcessModelList( $response )->ReturnData();
	}
	
	
	/**
	 * default desc
	 */
	function UnformalizedAddressDirectory() {
		$data = $this->ProcessInputData('UnformalizedAddressDirectory', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->UnformalizedAddressDirectory($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
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
	*  Функция используется для доп.аутентификации пользователя при socket-соединении NodeJS для армов СМП
	*  Входящие данные: session
	*  На выходе: JSON-строка
	*/
	function getPmUserInfo() {
		$data = $this->ProcessInputData('getPmUserInfo', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->getPmUserInfo($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;	
	}
	/**
	*  Блокирование карты вызова для редактирования. Начало транзакции редактирования
	*  Входящие данные: $_POST['CmpCallCard_id']
	*  На выходе: JSON-строка
	*/
	function lockCmpCallCard() {
		$data = $this->ProcessInputData('lockCmpCallCard', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->lockCmpCallCard($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();
		return true;		
	}
	/**
	*  Разблокирование карты вызова для редактирования. Конец транзакции редактирования
	*  Входящие данные: $_POST['CmpCallCard_id']
	*  На выходе: JSON-строка
	*/
	function unlockCmpCallCard() {
		$data = $this->ProcessInputData('unlockCmpCallCard', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->unlockCmpCallCard($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();
		return true;
	}
	/**
	*  Функция проверки наличия блокировки талона вызова. Снимает блокировку 
	*  после определенного временного интервала
	*  Входящие данные: $_POST['CmpCallCard_id']
	*  На выходе: JSON-строка
	*/
	function checkLockCmpCallCard() {
		$data = $this->ProcessInputData('checkLockCmpCallCard', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->checkLockCmpCallCard($data);

		$this->ProcessModelSave($response, true, true)->ReturnData();
		return true;
	}
	/**
	 * default desc
	 */
	function clearCmpCallCardList() {
	
		$response = $this->dbmodel->clearCmpCallCardList();
		$this->ProcessModelSave($response, true, true)->ReturnData();
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
	function saveUnformalizedAddress() {
		$data = $this->ProcessInputData('saveUnformalizedAddress', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->saveUnformalizedAddress($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	
	/**
	 * Загрузка списка карт СМП для АРМ врача/оператора СМП
	 */
	function loadSMPWorkPlace() {
		$data = $this->ProcessInputData('loadSMPWorkPlace', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadSMPWorkPlace($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}


	/**
	 * Загрузка списка карт СМП для АРМ врача/оператора СМП
	 */
	function loadSMPDispatchCallWorkPlace() {		
		$data = $this->ProcessInputData('loadSMPDispatchCallWorkPlace', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadSMPDispatchCallWorkPlace($data);
		//var_dump($response); exit;
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
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
	 * Загрузка списка карт СМП для АРМ врача/оператора СМП
	 */
	function loadSMPAdminWorkPlace() {
		$data = $this->ProcessInputData('loadSMPAdminWorkPlace', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadSMPAdminWorkPlace($data);
		
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
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
	 * default desc
	 */
	function loadSmpStacDiffDiagJournal() {
		$data = $this->ProcessInputData('loadSmpStacDiffDiagJournal', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadSmpStacDiffDiagJournal($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * @desc Загрузка списка карт СМП для АРМ старшего смены
	 */
	function loadSMPHeadDutyWorkPlace() {
		$data = $this->ProcessInputData('loadSMPHeadDutyWorkPlace', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadSMPHeadDutyWorkPlace($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * Загрузка списка карт СМП для АРМ врача/оператора ППД
	 */
	function loadPPDWorkPlace() {
		$data = $this->ProcessInputData('loadPPDWorkPlace', true);
		if ( $data === false ) { return false; }
		//var_dump($data);exit;
		$response = $this->dbmodel->loadPPDWorkPlace($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
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
	function loadStreetsAndUnformalizedAddressDirectoryCombo() {
		$data = $this->ProcessInputData('loadStreetsAndUnformalizedAddressDirectoryCombo', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadStreetsAndUnformalizedAddressDirectoryCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	/**
	 * default desc
	 */
	function loadSMPHeadBrigWorkPlace() {
		$data = $this->ProcessInputData('loadSMPHeadBrigWorkPlace', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadSMPHeadBrigWorkPlace($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}
	/**
	 * @desc Возвращает данные по оперативной обстановке выбранного ЛПУ со службой ППД
	 */
	function loadLpuOperEnv(){
		$data = $this->ProcessInputData('loadLpuOperEnv', false);
		if ( $data ) {
			$response = $this->dbmodel->loadLpuOperEnv( $data );
			$this->ProcessModelList( $response, true, true )->ReturnData();
		}
	}

	/**
	 * @desc Возвращает список случаев противоправных действий в отношении персонала СМП всего региона
	 */
	function loadCmpIllegalActList(){
		$data = $this->ProcessInputData('loadCmpIllegalActList', false);

		$response = $this->dbmodel->loadCmpIllegalActList( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}

	/**
	 * @desc Возвращает случаей противоправных действий в отношении персонала СМП всего региона
	 */
	function loadCmpIllegalActForm(){
		$data = $this->ProcessInputData('loadCmpIllegalActForm', false);

		$response = $this->dbmodel->loadCmpIllegalActForm( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}

	/**
	 * default desc
	 */
	function saveCmpIllegalActForm() {
		$data = $this->ProcessInputData('saveCmpIllegalActForm', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->saveCmpIllegalActForm($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * default desc
	 */
	function findCmpIllegalAct() {
		$data = $this->ProcessInputData('findCmpIllegalAct', false);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->findCmpIllegalAct($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	
	/**
	*  Сохранение карты вызова СМП
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма редактирования карты вызова СМП
	*/
	function saveCmpCallCard() {

		$data = $this->ProcessInputData('saveCmpCallCard', true);

		if ( $data === false ) { return false; }
		
		//поводы для ППД 12Я; 12Э; 12У; 12Р; 12К; 12Г; 13Л; 11Я; 11Л; 04Д; 04Г; 13М; 09Я; 15
		$reasons = array(541, 542, 595, 606, 609, 613, 616, 618, 619, 620, 621, 629, 630, 644, 632, 689); // Возможные поводы
		
		$forPPDflag = false;

		// Проверяем откуда пришел вызов. 
		// Из неотложки
		if (
				$data['ARMType'] == 'slneotl'  && 
				!empty($data['session']['lpu_id'])
		) 
		{
				//$data['Lpu_id'] = $data['session']['lpu_id'];
				$data['CmpCallCard_IsReceivedInPPD'] = 2;
		}
		
		//Не нужно самоопределяться в ЛПУ
		$data['Lpu_ppdid'] = $data['LpuTransmit_id'];

		if ( empty($data['Lpu_id']) ) {
			$data['Lpu_id'] = $data['session']['lpu_id'];
		}

		$data['Person_PolisNum'] = !empty($data['Polis_Num'])?$data['Polis_Num']:null;
		$data['Person_PolisSer'] = !empty($data['Polis_Ser'])?$data['Polis_Ser']:null;

        $data['CmpCallCard_PolisEdNum'] = null;
        if(!empty($data['CmpCallCard_PolisEdNum'])){
            $data['CmpCallCard_PolisEdNum'] = $data['CmpCallCard_PolisEdNum'];
        }
        if(!empty($data['Polis_EdNum'])){
            $data['CmpCallCard_PolisEdNum'] = $data['Polis_EdNum'];
        }
		//$data['CmpCallCard_PolisEdNum'] = !empty($data['CmpCallCard_PolisEdNum'])?$data['CmpCallCard_PolisEdNum']:$data['Polis_EdNum'];

		$data['CmpCallCard_IsPoli'] = ( !empty($data['CmpCallCard_IsPoli']) && ($data['CmpCallCard_IsPoli'] == "on" || $data['CmpCallCard_IsPoli'] === 'true') )? 2: 1;
		$data['CmpCallCard_IsPassSSMP'] = ( !empty($data['CmpCallCard_IsPassSSMP']) && ($data['CmpCallCard_IsPassSSMP'] == "on" || $data['CmpCallCard_IsPassSSMP'] === 'true') )? 2: 1;

		if(
			(!empty($data['CmpCallCard_IsExtra']) && ($data['CmpCallCard_IsExtra'] == 2)) ||
			(!empty($data['CmpCallCard_IsPoli']) && ($data['CmpCallCard_IsPoli'] == 2))			
			){
			$data['CmpCallCard_IsNMP'] = 2;
		}
		else{
			$data['CmpCallCard_IsNMP'] = 1;
		}
		//$data['CmpCallCard_IsNMP'] = ( !empty($data['CmpCallCard_IsExtra']) )? $data['CmpCallCard_IsExtra']: 1;
		$data['CmpCallCardStatusType_id'] = ( !empty($data['CmpCallCardStatusType_id']) )? $data['CmpCallCard_IsExtra']: 1;
		
		//надо перенести в модель или что-то подумать над этим - не сохраняет на дефолтную, если включено
		//$this->dbmodel->db->trans_begin();
		
		//$response = $this->dbmodel->saveCmpCallCard($data);
		if(!empty($data["CmpCallCard_id"])){
			$data['action'] = 'edit';
		}
		else{
			$data['action'] = 'add';
		}
		/*
		 * это было
		$response = $this->dbmodel->saveCmpCallCard( $data );

		$IsSMPServer = $this->config->item('IsSMPServer');
		$IsLocalSMP = $this->config->item('IsLocalSMP');
		if (($IsLocalSMP === true || $IsSMPServer === true) && !empty($response[0]["CmpCallCard_id"])) {
			// отправляем карту СМП в основную БД через очередь ActiveMQ
			$this->load->model('Replicator_model');
			$this->Replicator_model->sendRecordToActiveMQ(array(
				'table' => 'CmpCallCard',
				'type' => (empty($data['CmpCallCard_id'])) ? 'insert' : 'update',
				'keyParam' => 'CmpCallCard_id',
				'keyValue' => $response[0]["CmpCallCard_id"]
			));
		}
		*/
		/*
		 * а это перенес с релиза
		 * */
		$IsSMPServer = $this->config->item('IsSMPServer');

		$IsMainServer = $this->config->item('IsMainServer');

		//если с смп на рабочий
		if (
			($data['CmpCallCard_IsNMP'] == 2) &&
			($IsSMPServer === true)// если веб СМП
		) {
			//сейчас мы на бд смп
			$response = $this->dbmodel->saveCmpCallCard($data);

			if(isset($response[0]) && isset($response[0]["CmpCallCard_id"])){
				// #137883 закомментировал отправку в очередь, так как на рабочем очередь включена, и сохранение не отрабатывает
				//проверяем, активно ли сохранение на ActiveMQ, да - сохраняем туда, нет - вручную
				/*if (defined('STOMPMQ_MESSAGE_ENABLE') && defined('STOMPMQ_MESSAGE_ENABLE') && STOMPMQ_MESSAGE_ENABLE === TRUE) {

					$mqCardData = $data;
					$mqCardData["CmpCallCard_id"] = $response[0]["CmpCallCard_id"];
					$mqCardData["CmpCallCard_GUID"] = $response[0]["CmpCallCard_GUID"];

					$this->dbmodel->sendCmpCallCardToActiveMQ($mqCardData);

				}
				else{*/

				unset($this->db);

				//сейчас мы на дефолтной базе
				$this->load->database('main');

				$cccConfig = array(
					'CmpCallCard_GUID' => $response[0]["CmpCallCard_GUID"],
					'CmpCallCard_id' => $response[0]["CmpCallCard_id"],
					'CmpCallCard_Numv' => (isset($response["CmpCallCard_Numv"]) ? $response["CmpCallCard_Numv"] : null),
					'CmpCallCard_Ngod' => (isset($response["CmpCallCard_Ngod"]) ? $response["CmpCallCard_Ngod"] : null),
					'CmpCallCard_prmDT' => (isset($response["CmpCallCard_prmDT"]) ? $response["CmpCallCard_prmDT"] : null)
				);

				if (!empty($response[0]["Person_id"])) {
					$data['Person_id'] = $response[0]["Person_id"];
				}
				if(!empty($response["Person_id"])) {$data['Person_id'] = $response["Person_id"];}
				$data['CmpCallCard_Numv'] = $cccConfig["CmpCallCard_Numv"];
				$data['CmpCallCard_Ngod'] = $cccConfig["CmpCallCard_Ngod"];
				$res = $this->dbmodel->saveCmpCallCard( $data, $cccConfig);

				unset($this->db);

				//сейчас мы на бд смп
				$this->load->database();
				//}
			}
		}
		else{
			//если рабочий главный сервер, где присутствуют смп армы
			if($IsMainServer === true){
				//проверяем подключение к СМП
				unset($this->db);

				try{
					$this->load->database('smp');
				} catch (Exception $e) {
					$this->load->database();
					$errMsg = "Нет связи с сервером: создание нового вызова недоступно";
					$this->ReturnError($errMsg);
					return false;
				}

				//сохраняем на СМП
				$response = $this->dbmodel->saveCmpCallCard( $data );

				//возвращаемся на рабочую
				unset($this->db);
				$this->load->database();

				if(isset($response[0]) && isset($response[0]["CmpCallCard_id"])){
					//сохраняем на рабочую
					$cccConfig = array(
						'CmpCallCard_GUID' => $response[0]["CmpCallCard_GUID"],
						'CmpCallCard_id' => $response[0]["CmpCallCard_id"],
						'CmpCallCard_Numv' => (isset($response["CmpCallCard_Numv"]) ? $response["CmpCallCard_Numv"] : null),
						'CmpCallCard_Ngod' => (isset($response["CmpCallCard_Ngod"]) ? $response["CmpCallCard_Ngod"] : null),
						'CmpCallCard_prmDT' => (isset($response["CmpCallCard_prmDT"]) ? $response["CmpCallCard_prmDT"] : null)
					);

					if (!empty($response[0]["Person_id"])) {
						$data['Person_id'] = $response[0]["Person_id"];
					}
					$data['CmpCallCard_Numv'] = $cccConfig["CmpCallCard_Numv"];
					$data['CmpCallCard_Ngod'] = $cccConfig["CmpCallCard_Ngod"];
					$res = $this->dbmodel->saveCmpCallCard( $data, $cccConfig );
				}
				/*else{
					$errMsg = "Нет связи с сервером: создание нового вызова недоступно";
					$this->ReturnError($errMsg);
					return false;
				}*/
			}
			else{
				// если нет СМП на рабочем, просто сохраняем
				$response = $this->dbmodel->saveCmpCallCard( $data );
			}
		}

		if(/*getRegionNick() == 'ufa' && */!empty( $data['isSavedCVI']) ) {
			$this->load->model('ApplicationCVI_model', 'ApplicationCVI_model');
			$params = [
				'Person_id' => $data['Person_id'],
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
                    'Person_id' => $response['Person_id'],
	                'PersonQuarantine_id' => NULL,
	                'KLRgn_id' => $data['KLRgn_id'],
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
	                'RepositoryObesrv_contactDate' => NULL,
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
	            $this->load->model('RepositoryObserv_model', 'RepositoryObserv_model');
                $resRepObs = $this->RepositoryObserv_model->save($CVIParams);
            }
			if( !$this->isSuccessful($res) ) {
				throw new Exception($res['Error_Msg']);
			}
		}

		if (!$this->dbmodel->isSuccessful($response)) {
			//$this->dbmodel->db->trans_rollback();
			$this->ProcessModelSave($response, true)->ReturnData();
			return false;
		}

		if (isset($data['Person_id']) && isset($data['Person_isOftenCaller']) && $data['Person_isOftenCaller']==1) {
			$this->load->model('OftenCallers_model', 'oc_model');
			$this->oc_model->checkOftenCallers($data);
		}
		
		//Если переданы услуги, сохраняем услуги
		
		if ( isset( $data[ 'usluga_array' ] ) && is_array( $data[ 'usluga_array' ] ) ) {

			$save_usluga_response = $this->dbmodel->saveCmpCallCardUslugaList(array(
				'CmpCallCard_id'=>$response[ 0 ][ 'CmpCallCard_id' ],
				'pmUser_id'=>$data['pmUser_id'],
				'usluga_array'=>$data[ 'usluga_array' ]
			));

			if ( !$this->dbmodel->isSuccessful( $save_usluga_response ) ) {
				//$this->dbmodel->db->trans_rollback() ;
				$this->ProcessModelSave( $save_usluga_response , true )->ReturnData() ;
				return false ;
			}
			
		}

		$response[0]['Warning_Msg'] = $this->dbmodel->getWarningMsg();
		$response[0]['Info_Msg'] = $this->dbmodel->getInfoMsg();

		//$this->dbmodel->db->trans_commit();
		
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении карты вызова СМП')->ReturnData();

		return true;
	}	
	
	/**
	 * Закрытие карты вызова
	 */
	
	function saveCmpCallCloseCard() {
		$data = $this->ProcessInputData('saveCmpCallCloseCard', true);
		if ( $data === false ) { return false; }		
						
		//var_dump($data['EPREF_DrugGrid']); exit;
		$response = $this->dbmodel->saveCmpCallCloseCard($data);		
		
		//Создаем статус
		/*
		$data1['CmpCallCardStatusType_id'] = '1';
		$data1['CmpCallCardStatus_Comment'] = '';
		$data1['pmUser_id'] = $data['pmUser_id'];
		$data1['CmpCallCard_id'] = $response[0]['CmpCallCard_id'];		
		$response = $this->dbmodel->setStatusCmpCallCard($data1);
		*/

		$this->ProcessModelSave($response, true, 'Ошибка при закрытии карты вызова')->ReturnData();
		return true;
	}

	/**
	 * Печать шапки
	 */
	public function printCmpCallCardHeader() {
		return false;
	}

	/**
	 * Печать карты закрытия вызова 110у
	 */	
	public function printCmpCloseCard110() {
		$data = $this->ProcessInputData( 'printCmpCloseCard110', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->printCmpCloseCard110( $data );

		if ( !is_array( $response ) || !sizeof( $response ) || !sizeof( $response[ 0 ] ) ) {
			echo 'Для карты вызова не заполнена 110у';
			return true;
		}
		
		$response = $response[0];

		$response['druglist'] = $this->dbmodel->loadCmpCallCardDrugList($data);

		if(!empty($response['druglist'])) {
		    if($response['druglist'][0]['DrugComplexMnn_RusName'] == NULL) {
                $response['druglist'] = $this->dbmodel->loadCmpCallCardSimpleDrugList($data);
                for($i=0; $i < sizeof($response['druglist']); $i++) {
                    $response['druglist'][$i]['DrugComplexMnn_RusName'] = $response['druglist'][$i]['DrugNomen_Name'];
                }
            }
        }

		$response['uslugalist'] = $this->dbmodel->loadCmpCallCardUslugaGrid($data);
		$pd = array();
		foreach( $response as $k => $resp ){
			$pd[ $k ] = isset( $resp ) ? $resp : '&nbsp;';
		}

		if (
			$pd['AcceptDate'] != '&nbsp;' 
			&& $pd['AcceptDate'] != '' 
			&& $pd['AcceptDate'] != '01.01.1900') $pd['CallCardDate'] = $pd['AcceptDate'];
		
		$parse_data = $pd+array(
			'C_PersonRegistry_id' => $this->getComboRel($response['CmpCloseCard_id'], 'PersonRegistry_id')
			,'C_AgeType' => $this->getComboRel($response['CmpCloseCard_id'], 'AgeType_id')
			,'C_CallTeamPlace_id' => $this->getComboRel($response['CmpCloseCard_id'], 'CallTeamPlace_id')
			,'C_Delay_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Delay_id')
			,'C_TeamComplect_id' => $this->getComboRel($response['CmpCloseCard_id'], 'TeamComplect_id')
			,'C_CallPlace_id' => $this->getComboRel($response['CmpCloseCard_id'], 'CallPlace_id')
			,'C_AccidentReason_id' => $this->getComboRel($response['CmpCloseCard_id'], 'AccidentReason_id')
			,'C_PersonSocial_id' => $this->getComboRel($response['CmpCloseCard_id'], 'PersonSocial_id')
			,'C_Trauma_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Trauma_id')
			,'Condition_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Condition_id')
			,'Behavior_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Behavior_id')
			,'Cons_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Cons_id')
			,'Pupil_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Pupil_id')
			,'Kozha_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Kozha_id')
			,'Hypostas_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Hypostas_id')
			,'Crop_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Crop_id')
			,'Hale_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Hale_id')
			,'Rattle_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Rattle_id')
			,'Shortwind_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Shortwind_id')
			,'Heart_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Heart_id')
			,'Noise_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Noise_id')
			,'Pulse_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Pulse_id')
			,'Lang_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Lang_id')
			,'Gaste_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Gaste_id')
			,'Liver_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Liver_id')

			,'Complicat_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Complicat_id')
			,'ComplicatEf_id' => $this->getComboRel($response['CmpCloseCard_id'], 'ComplicatEf_id')
			,'Result_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Result_id')
			,'Patient_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Patient_id')
			,'TransToAuto_id' => $this->getComboRel($response['CmpCloseCard_id'], 'TransToAuto_id')
			//,'DeportClose_id' => $this->getComboRel($response['CmpCloseCard_id'], 'DeportClose_id')
			//,'DeportFail_id' => $this->getComboRel($response['CmpCloseCard_id'], 'DeportFail_id')
		);

		if (getRegionNick() == 'perm') {
			$parse_data['ResultUfa_id'] = $this->dbmodel->getResultCmpForPrint($response['CmpCloseCard_id']);
		} else {
			$parse_data['ResultUfa_id'] = $this->getComboRel($response['CmpCloseCard_id'], 'ResultUfa_id');
		}

		if ( isset( $data['session']['region']['nick'] ) && $data['session']['region']['nick'] == 'krym' ) {

			$parse_data['LongDirect_id'] = $this->getComboRel($response['CmpCloseCard_id'], 'LongDirect_id');
			$parse_data['Allergic_id'] = $this->getComboRel($response['CmpCloseCard_id'], 'Allergic_id');
			$parse_data['VisitEpid_id'] = $this->getComboRel($response['CmpCloseCard_id'], 'VisitEpid_id');
			$parse_data['Infec_id'] = $this->getComboRel($response['CmpCloseCard_id'], 'Infec_id');
			$parse_data['Injections_id'] = $this->getComboRel($response['CmpCloseCard_id'], 'Injections_id');
			$parse_data['SmellOfAlc_id'] = $this->getComboRel($response['CmpCloseCard_id'], 'SmellOfAlc_id');
			$parse_data['Reflexes_id'] = $this->getComboRel($response['CmpCloseCard_id'], 'Reflexes_id');
			$parse_data['Auscultation_id'] = $this->getComboRel($response['CmpCloseCard_id'], 'Auscultation_id');
			$parse_data['Muscular_id'] = $this->getComboRel($response['CmpCloseCard_id'], 'Muscular_id');
			$parse_data['Percussion_id'] = $this->getComboRel($response['CmpCloseCard_id'], 'Percussion_id');
			$parse_data['BordersHeart_id'] = $this->getComboRel($response['CmpCloseCard_id'], 'BordersHeart_id');
			$parse_data['Heartbeat_id'] = $this->getComboRel($response['CmpCloseCard_id'], 'Heartbeat_id');
			$parse_data['Fauces_id'] = $this->getComboRel($response['CmpCloseCard_id'], 'Fauces_id');
			$parse_data['Diuresis_id'] = $this->getComboRel($response['CmpCloseCard_id'], 'Diuresis_id');
			$parse_data['Defik_id'] = $this->getComboRel($response['CmpCloseCard_id'], 'Defik_id');
			$parse_data['Injury_id'] = $this->getComboRel($response['CmpCloseCard_id'], 'Injury_id');
			$parse_data['Agreement_id'] = $this->getComboRel($response['CmpCloseCard_id'], 'Agreement_id');
			$parse_data['Renouncement_id'] = $this->getComboRel($response['CmpCloseCard_id'], 'Renouncement_id');
			$parse_data['OsmPed_id'] = $this->getComboRel($response['CmpCloseCard_id'], 'OsmPed_id');
		}
		if ( isset( $data['session']['region']['nick'] ) && $data['session']['region']['nick'] == 'buryatiya' ) {
			$parse_data[ 'region_nick' ] = $data['session']['region']['nick'];
			$parse_data[ 'Speech_id' ] = $this->getComboRel( $response[ 'CmpCloseCard_id' ], 'Speech_id' );
			$parse_data[ 'CranialNerve_id' ] = $this->getComboRel( $response[ 'CmpCloseCard_id' ], 'CranialNerve_id' );
			$parse_data[ 'LightReaction_id' ] = $this->getComboRel( $response[ 'CmpCloseCard_id' ], 'LightReaction_id' );
			$parse_data[ 'Sensitivity_id' ] = $this->getComboRel( $response[ 'CmpCloseCard_id' ], 'Sensitivity_id' );
			$parse_data[ 'ReflexesCC_id' ] = $this->getComboRel( $response[ 'CmpCloseCard_id' ], 'ReflexesCC_id' );
			$parse_data[ 'TendonReflexes_id' ] = $this->getComboRel( $response[ 'CmpCloseCard_id' ], 'TendonReflexes_id' );
			$parse_data[ 'MuscleST_id' ] = $this->getComboRel( $response[ 'CmpCloseCard_id' ], 'MuscleST_id' );
			$parse_data[ 'Coordination_id' ] = $this->getComboRel( $response[ 'CmpCloseCard_id' ], 'Coordination_id' );
			$parse_data[ 'Romberg_id' ] = $this->getComboRel( $response[ 'CmpCloseCard_id' ], 'Romberg_id' );
			$parse_data[ 'PathologicalReflexes_id' ] = $this->getComboRel( $response[ 'CmpCloseCard_id' ], 'PathologicalReflexes_id' );
			$parse_data[ 'MeningealSigns_id' ] = $this->getComboRel( $response[ 'CmpCloseCard_id' ], 'MeningealSigns_id' );
			$parse_data[ 'FingerNoseTest_id' ] = $this->getComboRel( $response[ 'CmpCloseCard_id' ], 'FingerNoseTest_id' );
			$parse_data[ 'FocalSymptoms_id' ] = $this->getComboRel( $response[ 'CmpCloseCard_id' ], 'FocalSymptoms_id' );
			$parse_data[ 'Convulsions_id' ] = $this->getComboRel( $response[ 'CmpCloseCard_id' ], 'Convulsions_id' );
			$parse_data[ 'Face_id' ] = $this->getComboRel( $response[ 'CmpCloseCard_id' ], 'Face_id' );
			$parse_data[ 'PupilsN_id' ] = $this->getComboRel( $response[ 'CmpCloseCard_id' ], 'PupilsN_id' );
			$parse_data[ 'Anisocoria_id' ] = $this->getComboRel( $response[ 'CmpCloseCard_id' ], 'Anisocoria_id' );
			$parse_data[ 'Nystagmus_id' ] = $this->getComboRel( $response[ 'CmpCloseCard_id' ], 'Nystagmus_id' );
		}

		$equipment = $this->dbmodel->loadCmpCloseCardEquipmentPrintForm( array( 'CmpCloseCard_id' => $response[ 'CmpCloseCard_id' ] ) );
		if ( !empty( $equipment ) ) {
			$parse_data[ 'equipment' ] = $equipment;
		}

		$this->load->library( 'parser' );

		$this->parser->parse( 'print_form110u', $parse_data );
	}
	
	/**
	 * @desc описание
	 * Печать талона вызова по подобию 110у
	 */
	function printCmpCallCard() {		
		$this->load->library('parser');
	
		$data = $this->ProcessInputData('printCmpCallCard', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->printCmpCallCard($data);
	
		$pd = array();
		foreach ($response as $k => $resp) {						
			$pd[$k] = isset($resp) ? $resp : '&nbsp;';			
		}
		
		$h = $this->parser->parse('print_callcard', $pd[0], true);
		
		$this->load->helper('Options');
		$this->load->library('swEvnXml');
		
		$options = getOptions();

		if (is_array($options['print'])
			&& isset($options['print']['evnxml_print_type'])
			&& 2 == $options['print']['evnxml_print_type']
		) {
			echo $h;
		}
		else{
			swEvnXml::doPrintPdf('Печать карты вызова СМП',$options,$h);
		}
	}
	
	/**
	 * default desc
	 */
	function printReportCmp() {
		$this->load->library('parser');
		
		$data = $this->ProcessInputData('printReportCmp', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->printReportCmp($data);
		$diagDay = $this->dbmodel->reportDayDiag($data);
		$brigDay = $this->dbmodel->reportBrig($data);
		
		
		if (is_array($diagDay) && count($diagDay) > 0) {
			$diagtxt ='';
			foreach ($diagDay as $diagd) {
				$diagtxt .= (($diagd['CmpDiag_Name'] != '')?$diagd['CmpDiag_Name']:'неизвестно').': '.$diagd['cnt'].'<br/>';	
			}
		} else {
			$diagtxt = ' данные отсутствуют. ';
		}
		
		
		
		if (is_array($brigDay) && count($brigDay) > 0) {
			//$brigtxt = "<table class='tbl'><tr><td>Дата</td><td>Время</td><td>Номер бригады</td><td>Профиль</td><td>Действие</td></tr>";
			$brigtxt = "<table class='tbl'><tr><td>Номер бригады</td><td>Профиль</td></tr>";
			foreach ($brigDay as $br) {
				$brigtxt .= '<tr><td>'.$br['Num'].'</td><td>'.$br['Spec'].'</td></tr>';	
				//$brigtxt .= '<tr><td>'.$br['Date'].'</td><td>'.$br['Time'].'</td><td>'.$br['Num'].'</td><td>'.$br['Spec'].'</td><td>'.$br['StatusName'].'</td></tr>';	
			}
			$brigtxt .= "</table>";
		} else {
			$brigtxt = ' данные отсутствуют. ';
		}
		
		
		//var_dump($response); exit;
		$parse_data = array(
			'DayDate' => 'c '.$data['daydate1'].' по '.$data['daydate2'],
			'SpisokBrigad' => $brigtxt,
			'AllCalls' => $response['allcall'],
			'Zabolevaniya' => $diagtxt,
			'toNMP' => $response['transmit_nmp'],
			'Reject' => $response['reject']					
		);
		
		$this->parser->parse('print_formreportcmp', $parse_data);		
		
		return true;		
	}
	
	/**
	 * default desc
	 */
	public function saveCmpCloseCard110() {
		$data = $_POST;

		//сохранение 110 в тестовом режиме реализовано через json
		if(isset($data['CardParamsJSON'])){
			ConvertFromWin1251ToUTF8($data);
			$cmp_data = (array) json_decode($data['CardParamsJSON']);

			if ( is_array($cmp_data) ) {
				array_walk_recursive($cmp_data, 'ConvertFromUTF8ToWin1251');
			}
			else {
				$cmp_data = array();
			}
		}
		else {
			$cmp_data = array();
		}

		$data = array_merge(  getSessionParams(), $data, $cmp_data);
		if ( $data === false ) { return false; }

		//Обернём метод в транзакцию
		//$this->dbmodel->beginTransaction() ;

		$response = $this->dbmodel->saveCmpCloseCard110($data);

		if ( isset( $data['session']['region']['nick'] ) && $data['session']['region']['nick'] == 'perm' ) {
			$this->dbmodel->updateCmpCallCardByClose( $data ) ;
		}else{
			$this->dbmodel->updateCmpCallCardByClose(
				array(
					'CmpCallCard_id' => $data['CmpCallCard_id'],
					'Diag_id' => $data['Diag_id'],
					'Lpu_hid' => (!empty($data['ComboValue_241']) ? $data['ComboValue_241'] : null),
					'CmpCallCard_isControlCall' => (!empty($data['CmpCallCard_isControlCall']) ? $data['CmpCallCard_isControlCall'] : 1),
					'CmpCallCard_IndexRep' => (!empty($data['CmpCallCard_IndexRep']) ? $data['CmpCallCard_IndexRep'] : null)
				)
			);
		}
		if ( !$this->dbmodel->isSuccessful( $response ) ) {
			//$this->dbmodel->rollbackTransaction() ;
			$errMsg = 'Ошибка при обновлении данных';
			if(count($response) > 0 && !empty($response[0]['Error_Msg'])){
				$errMsg = $response[0]['Error_Msg'];
			}
            $this->ReturnError($errMsg);
            return false;
		}

        if (empty($data['CmpCallCard_id'])) {
            $data['CmpCallCard_id'] = $response[0]['CmpCallCard_id'];
        }

		//Если переданы услуги, сохраняем услуги
		if ( isset( $data[ 'usluga_array' ] ) ) {

			$uslugaArr = (array) json_decode($data['usluga_array']);
			array_walk_recursive($uslugaArr, 'ConvertFromUTF8ToWin1251');
			if(isset($uslugaArr)){
				$save_usluga_list_result = $this->dbmodel->saveCmpCallCardUslugaList( array(
					'CmpCallCard_id' => $data[ 'CmpCallCard_id' ] ,
					'usluga_array' => $uslugaArr,
					'pmUser_id' => $data[ 'pmUser_id' ]
				) ) ;
				if ( !$this->dbmodel->isSuccessful( $save_usluga_list_result ) ) {
					//$this->dbmodel->rollbackTransaction() ;
					if (!empty($save_usluga_list_result[0]['Error_Msg'])) {
						$this->ReturnError($save_usluga_list_result[0]['Error_Msg']);
						return false;
					}
				}
			}
		}

		if( isset($data[ 'ExpertResponseJSON' ])){

			$arrExpertResponse = json_decode($data['ExpertResponseJSON'], true);
			if(count($arrExpertResponse) > 0){
				$this->dbmodel->saveCmpCloseCardExpertResponseList(
					array(
						'CmpCloseCard_id' => $response[0]["CmpCloseCard_id"],
						'ExpertResponseList' => $arrExpertResponse,
						'pmUser_id' => $data[ 'pmUser_id' ]
					)
				);
			}
		}

		//Если переданы данные об использовании медикаментов
		if (isset($data['CmpCallCardDrugJSON'])) {

			$dparams = array(
				'CmpCallCard_id' => $data['CmpCallCard_id'],
				'LpuBuilding_id' => $data['LpuBuilding_id'],
				'json_str' => $data['CmpCallCardDrugJSON'],
				'pmUser_id' => $data['pmUser_id'],
				'Server_id' => $data['Server_id']
			);

			if (!empty($data['LpuBuilding_IsWithoutBalance']) && $data['LpuBuilding_IsWithoutBalance'] == 'true') {
				$save_drug_result = $this->dbmodel->saveCmpCallCardSimpleDrugFromJSON($dparams);
			} else {
				$save_drug_result = $this->dbmodel->saveCmpCallCardDrugFromJSON($dparams);
			}

			if (!$this->dbmodel->isSuccessful($save_drug_result)) {
				//$this->dbmodel->rollbackTransaction() ;
				if (!empty($save_drug_result[0]['Error_Msg'])) {
					$this->ReturnError($save_drug_result[0]['Error_Msg']);
					return false;
				}
			}
		}


		// Если передано сохранить вызов в Поликлинику
        if(!empty($data['saveActive']) && $data['saveActive']){
            $data['CmpCallCard_id'] = ($data['CmpCallCard_id']) ? $data['CmpCallCard_id'] : $response[0]['CmpCallCard_id'];

            $saveActive = $this->dbmodel->addHomeVisitFromSMP($data);
            if (!empty($saveActive[0]['Error_Msg'])) {
                $this->ReturnError($saveActive[0]['Error_Msg']);
                return false;
            }


        }

		// Изменим статус бригады
		if(!empty($data['AutoBrigadeStatusChange']) && $data['AutoBrigadeStatusChange']){

			// 95560: Статус бригады должен смениться на "Конец обслуживания" и далее сразу на "Свободна".
			if ( $this->dbmodel->setEmergencyTeamStatus( $data, 'Конец обслуживания' ) ){
				$freeStatus = ( isset($_SESSION['region']['nick']) && in_array($_SESSION['region']['nick'], array('ufa', 'krym')) ) ? 'Свободна на базе' : 'Свободна';

				$this->dbmodel->setEmergencyTeamStatus( $data, $freeStatus );
			}
		}
		//$this->dbmodel->commitTransaction() ;

		//закладка для сохранения в activeMQ
		$IsSMPServer = $this->config->item('IsSMPServer');

		if (
			$IsSMPServer === true  &&// если веб СМП
			(defined('STOMPMQ_MESSAGE_ENABLE') && defined('STOMPMQ_MESSAGE_ENABLE') && STOMPMQ_MESSAGE_ENABLE === TRUE) && //проверяем, активно ли сохранение на ActiveMQ, да - сохраняем туда
			(
				!empty($data['ComboCheck_Patient_id'] ) && ($data['ComboCheck_Patient_id'] == 111) || // больной Подлежит активному посещению врачом поликлиники...(остальные)
				!empty($data['ComboCheck_ResultOther_id'] ) && ($data['ComboCheck_ResultOther_id'] == 645) // Активное посещение врачом поликлиники/ССМП (Крым)
			)
		){
			$mqCardData = $data;
			$mqCardData["CmpCloseCard_id"] = $response[0]["CmpCloseCard_id"];
			$mqCardData["CmpCloseCard_GUID"] = $response[0]["CmpCloseCard_GUID"];

			$this->dbmodel->sendCmpCloseCardToActiveMQ($data);
		}

		$dataForDiags = $data;
		$dataForDiags["CmpCloseCard_id"] = $response[0]["CmpCloseCard_id"];
		//Сохранение сопутствующих диагнозов и осложений основного
		$this->dbmodel->saveCmpCallCardDiagArr($dataForDiags);
		// Если передано сохранить вызов в Поликлинику и мы в СМП в обычной БД тоже это надо вставить


		if(isset($saveActive) && !empty($saveActive['Error_Msg'])){
			$response[0]['Active_Error_Msg'] = $saveActive['Error_Msg'];
		}

		$this->ProcessModelSave($response, true, 'Ошибка при закрытии карты вызова')->ReturnData();
		return true;
	}

	/**
	 * Поточный ввод талонов вызова
	 */
	public function saveCmpStreamCard() {
		
		$data = $_POST;
		
		$cmp_data = array();
		
		//сохранение 110 в тестовом режиме реализовано через json
		if(isset($data['CardParamsJSON'])){
			ConvertFromWin1251ToUTF8($data);
			$cmp_data = (array) json_decode($data['CardParamsJSON']);
			array_walk_recursive($cmp_data, 'ConvertFromUTF8ToWin1251');
		}
		
		$data = array_merge(  getSessionParams(), $data, $cmp_data);
		if ( $data === false ) { return false; }
		
		$data[ 'MedStaffFact_uid' ] = !empty( $data[ 'MedStaffFact_id' ] ) ? $data[ 'MedStaffFact_id' ] : null;
		
		$this->dbmodel->beginTransaction() ;

		$response = $this->dbmodel->saveCmpStreamCard( $data ) ;

		if ( !$this->dbmodel->isSuccessful( $response ) ) {
			$this->dbmodel->rollbackTransaction();
			$this->ProcessModelSave( $response, true )->ReturnData();
			return false;
		}

		$data['CmpCloseCard_id'] = ($data['CmpCloseCard_id']) ? $data['CmpCloseCard_id'] : $response[0]['CmpCloseCard_id'];
		//Если переданы услуги, сохраняем услуги

		if ( isset( $data[ 'usluga_array' ] ) ) {
			
			$uslugaArr = (array) json_decode($data['usluga_array']);
			array_walk_recursive($uslugaArr, 'ConvertFromUTF8ToWin1251');

			if( isset($uslugaArr[0]) && isset($response[0][ 'CmpCallCard_id' ]) ){
				$save_usluga_list_result = $this->dbmodel->saveCmpCallCardUslugaList( array(
					'CmpCallCard_id' => $response[0][ 'CmpCallCard_id' ] ,
					'usluga_array' => $uslugaArr,
					'pmUser_id' => $data[ 'pmUser_id' ]
				) ) ;

				if ( !$this->dbmodel->isSuccessful( $save_usluga_list_result ) ) {
					$this->dbmodel->rollbackTransaction() ;
					if (!empty($save_usluga_list_result[0]['Error_Msg'])) {
						$this->ReturnError($save_usluga_list_result[0]['Error_Msg']);
						return false;
					}
				}
				
			}
		}
		
		// Если передано сохранить вызов в Поликлинику
		if(!empty($data['saveActive']) && $data['saveActive']){
			$data['CmpCallCard_id'] = ($data['CmpCallCard_id']) ? $data['CmpCallCard_id'] : $response[0]['CmpCallCard_id'];

			$saveActive = $this->dbmodel->addHomeVisitFromSMP($data);
			if (!empty($saveActive[0]['Error_Msg'])) {
				$this->ReturnError($saveActive[0]['Error_Msg']);
				return false;
			}

		}
		
		$this->dbmodel->commitTransaction() ;
		
		//закладка для сохранения в activeMQ
		$IsSMPServer = $this->config->item('IsSMPServer');
		if (
			$IsSMPServer === true  &&// если веб СМП
			(defined('STOMPMQ_MESSAGE_ENABLE') && defined('STOMPMQ_MESSAGE_ENABLE') && STOMPMQ_MESSAGE_ENABLE === TRUE) && //проверяем, активно ли сохранение на ActiveMQ, да - сохраняем туда
			(
				!empty($data['ComboCheck_Patient_id'] ) && ($data['ComboCheck_Patient_id'] == 111) || // больной Подлежит активному посещению врачом поликлиники...(остальные)
				!empty($data['ComboCheck_ResultOther_id'] ) && ($data['ComboCheck_ResultOther_id'] == 645) // Активное посещение врачом поликлиники/ССМП (Крым)
			)
		){
			$mqCardData = $data;
			$mqCardData["CmpCloseCard_id"] = $response[0]["CmpCloseCard_id"];
			$mqCardData["CmpCallCard_id"] = $response[0]["CmpCallCard_id"];
			$mqCardData["CmpCloseCard_GUID"] = $response[0]["CmpCloseCard_GUID"];
			$mqCardData["CmpCallCard_GUID"] = $response[0]["CmpCallCard_GUID"];

			$this->dbmodel->sendCmpCallCardToActiveMQ($mqCardData);
			$this->dbmodel->sendCmpCloseCardToActiveMQ($mqCardData);
		}
		
		// Если передано сохранить вызов в Поликлинику и мы в СМП в обычной БД тоже это надо вставить
		if(!empty($saveActive["HomeVisit_id"]) && $IsSMPServer === true){

			$data['CmpCallCard_id'] = ($data['CmpCallCard_id']) ? $data['CmpCallCard_id'] : $response[0]['CmpCallCard_id'];
            /*
			$this->load->model('Replicator_model');
			$this->Replicator_model->sendRecordToActiveMQ(array(
				'table' => 'HomeVisit',
				'type' => 'insert',
				'keyParam' => 'HomeVisit_id',
				'keyValue' => $saveActive["HomeVisit_id"]
			));
            */
			//$data['CmpCallCard_id'] = ($data['CmpCallCard_id']) ? $data['CmpCallCard_id'] : $response[0]['CmpCallCard_id'];
			//unset($this->db);
			//сейчас мы на дефолтной базе
			//$this->load->database('main');

			/*
			$response = $this->dbmodel->saveCmpStreamCard(
				$data,
				array(
					'CmpCallCard_GUID' => (empty($data['CmpCallCard_GUID']))?$response[0]['CmpCallCard_GUID']:$data['CmpCallCard_GUID'],
					'CmpCallCard_id' => (empty($data['CmpCallCard_id']))?$response[0]['CmpCallCard_id']:$data['CmpCallCard_id']
				)
			);
			*/
			/*
			if (!empty($mqCardData["CmpCallCard_id"]))
			{
				$data['CmpCallCard_id'] = $response[0]['CmpCallCard_id'];
			}

			$saveActive = $this->dbmodel->addHomeVisitFromSMP($data);

			unset($this->db);
			//сейчас мы на бд смп
			$this->load->database();
			
			if (!empty($saveActive[0]['Error_Msg'])) {
				$this->ReturnError($saveActive[0]['Error_Msg']);
				return false;
			}
			*/
		}

		//Сохранение сопутствующих диагнозов и осложений основного
		if (getRegionNick() != 'kz' && $data['session']['region']['nick'] != 'kz') {
			$this->dbmodel->saveCmpCallCardDiagArr($data);
		}
		//Если переданы данные об использовании медикаментов
		if (isset($data['CmpCallCardDrugJSON'])) {

			$dparams = array(
				'CmpCallCard_id' => (empty($data['CmpCallCard_id']))?$response[0]['CmpCallCard_id']:$data['CmpCallCard_id'],
				'LpuBuilding_id' => $data['LpuBuilding_id'],
				'json_str' => $data['CmpCallCardDrugJSON'],
				'pmUser_id' => $data['pmUser_id'],
				'Server_id' => $data['Server_id']
			);

			if (!empty($data['LpuBuilding_IsWithoutBalance']) && $data['LpuBuilding_IsWithoutBalance'] == 'true') {
				$save_drug_result = $this->dbmodel->saveCmpCallCardSimpleDrugFromJSON($dparams);
			} else {
				$save_drug_result = $this->dbmodel->saveCmpCallCardDrugFromJSON($dparams);
			}

			if (!$this->dbmodel->isSuccessful($save_drug_result)) {
				//$this->dbmodel->rollbackTransaction() ;
				if (!empty($save_drug_result[0]['Error_Msg'])) {
					$this->ReturnError($save_drug_result[0]['Error_Msg']);
					return false;
				}
			}
		}
		$this->ProcessModelSave( $response, true, 'Ошибка при закрытии карты вызова' )->ReturnData();

		return true;
	}
	
	/**
	 * Проверка на дубли
	 */
	function checkDuplicateCmpCallCard(){
		$data = $this->ProcessInputData('checkDuplicateCmpCallCard', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->checkDuplicateCmpCallCard($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Загрука комбобокса случаев противоправных действий
	 */
	function loadIllegalActCmpCards(){
		$data = $this->ProcessInputData('loadIllegalActCmpCards', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->loadIllegalActCmpCards($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	/**
	 * Загрузка списка станций
	 */
	function loadCmpStation(){
		$data = $this->ProcessInputData('loadCmpStation', false);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->loadCmpStation($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	*  Получение номера карты вызова СМП
	*  Входящие данные: нет
	*  На выходе: JSON-строка
	*  Используется: форма редактирования карты вызова СМП
	*/
	function getCmpCallCardNumber() {
		//Для получения Lpu_id
		$data = $this->ProcessInputData('getCmpCallCardNumber', true);
		if ( $data === false ) { return false; }

		$IsMainServer = $this->config->item('IsMainServer');

		if($IsMainServer === true) {
			//проверяем подключение к СМП
			unset($this->db);

			try{
				$this->load->database('smp');
			} catch (Exception $e) {
				$this->load->database();
				$errMsg = "Нет связи с сервером: создание нового вызова недоступно";
				$this->ReturnError($errMsg);
				return false;
			}

			$response = $this->dbmodel->getCmpCallCardNumber($data);
			//возвращаемся на рабочую
			unset($this->db);
			$this->load->database();

		}else{
			$response = $this->dbmodel->getCmpCallCardNumber($data);
		}

		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	/**
	*  Получение ID справочников местоположения ЛПУ
	*  Входящие данные: нет
	*  На выходе: JSON-строка
	*  Используется: форма редактирования карты вызова СМП
	*/
	function getLpuAddressTerritory() {
		//Для получения Lpu_id
		$data = $this->ProcessInputData('getLpuAddressTerritory', true);
		if ( $data === false ) { return false; }		
		$response = $this->dbmodel->getLpuAddressTerritory($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	/**
	*  Получение валидного адреса для геолокации OSM карт
	*  Входящие данные: CmpCallCard_id
	*  На выходе: JSON-строка
	*  Используется: АРМ ДН v.2
	*/
	function getAddressForOsmGeocode() {
		//Для получения Lpu_id
		$data = $this->ProcessInputData('getAddressForOsmGeocode', true);
		if ( $data === false ) { return false; }		
		$response = $this->dbmodel->getAddressForOsmGeocode($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	/**
	*  Получение валидного адреса для геолокации Navitel карт
	*  Входящие данные: CmpCallCard_id
	*  На выходе: JSON-строка
	*  Используется: АРМ ДН v.2
	*/	
	function getAddressForNavitel() {
		$data = $this->ProcessInputData('getAddressForNavitel', true);
		if ( $data === false ) { return false; }		
		$response = $this->dbmodel->getAddressForNavitel($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	/**
	*  Геокодирование с использованием GET-запроса к yandexmap v1
	*  Входящие данные: Address_Name
	*  На выходе: JSON-строка
	*  Используется: АРМ ДН v.2
	*/	
	function getYandexGeocode() {
		$data = $this->ProcessInputData('getYandexGeocode', true);
		$xml = file_get_contents("http://geocode-maps.yandex.ru/1.x/?format=json&geocode={$data['Address_Name']}&results=1");
		$response = array(0=>$xml);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	/**
	*	Возвращает результаты
	*/
	function getResults() {
		$response = $this->dbmodel->getResults();
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	/**
	 * default desc
	 */
	function getRejectPPDReasons() {
		$response = $this->dbmodel->getRejectPPDReasons();
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	/**
	 * default desc
	 */
	function getMoveFromNmpReasons() {
		$response = $this->dbmodel->getMoveFromNmpReasons();
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	/**
	 * default desc
	 */
	function getReturnToSmpReasons() {
		$response = $this->dbmodel->getReturnToSmpReasons();
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	/**
	 * default desc
	 */
	function getCombox() {
		$data = $this->ProcessInputData('getCombox', true);		
		$response = $this->dbmodel->getCombox($data);			
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	/**
	 * Список значений для комбика по ComboSys или CmpCloseCardCombo_Code
	 */
	function getComboValuesList() {
		$data = $this->ProcessInputData('getComboValuesList', true);
		$response = $this->dbmodel->getComboValuesList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * получение элементов для раздела услуги 110
	 */
	function getUslugaFields() {
		$data = $this->ProcessInputData('getUslugaFields', true);
		$response = $this->dbmodel->getUslugaFields($data);			
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Возвращает данные для всех комбобоксов талона вызова
	 */
	public function getComboxAll() {
		$response = $this->dbmodel->getComboxAll();
		$this->ProcessModelSave( $response )->ReturnData();
	}

	/**
	 * default desc
	 */
	function getComboRel($CmpCloseCard, $SysName) {		
		$response = $this->dbmodel->getComboRel($CmpCloseCard, $SysName);
		return $response;		
	}

	/**
	 * default desc
	 */
	function getCombo($data, $object)  {
		$response = $this->dbmodel->getCombo($data, $object);
		return $response;
	}
	
	/**
	 *	Установка статуса карты вызова
	 */
	function setStatusCmpCallCard() {
		$data = $this->ProcessInputData('setStatusCmpCallCard', true);
		if ( $data === false ) { return false; }

		if( $data['CmpCallCardStatusType_id'] != null ) { // если нужно проставить статус
			$response = $this->dbmodel->setStatusCmpCallCard($data);
		} else if( !empty($data['CmpCallCard_IsOpen']) ) { // если нужно открыть/закрыть
			$response = $this->dbmodel->setIsOpenCmpCallCard($data);
		} else {
			return false;
		}
		//для оператора нмп
		//проверяем, активно ли сохранение на ActiveMQ, да - сохраняем туда,
		//$IsSMPServer = $this->config->item('IsSMPServer');

		if (getRegionNick() == 'ufa' && (!empty($data['armtype']) && $data['armtype'] == 'slneotl')) {
			try{
				$dbSMP = $this->load->database('smp', true);
				$response = $this->dbmodel->setStatusCmpCallCard($data, $dbSMP);
			} catch (Exception $e) {
				$this->load->database();
			}
		} else if (
			//($IsSMPServer === true) &&
			(!empty($data['armtype']) && $data['armtype'] == 'slneotl') &&
			( defined('STOMPMQ_MESSAGE_ENABLE') && defined('STOMPMQ_MESSAGE_ENABLE') && STOMPMQ_MESSAGE_ENABLE === TRUE )
		) {
			$mqCardData = $data;

			$this->dbmodel->sendStatusCmpCallCardToActiveMQ($mqCardData);

		}
		
		//print_r($response); exit();
		if ( !empty($response[0]['Error_Msg']) ) {
			$this->ReturnError($response[0]['Error_Msg']);
			return false;
		}
		
		// Узнаем группу
		$groupData = $this->dbmodel->defineAccessoryGroupCmpCallCard($data);
		
		$this->ReturnData($groupData);
	}		

	/**
	 * default desc
	 */
	function setResult() {		
		$data = $this->ProcessInputData('setResult', true);		
		if ( $data === false ) { return false; }		
		$response = $this->dbmodel->setResult($data);
		$this->ProcessModelSave($response, true)->ReturnData();
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
	 * Назначение бригады на вызов с запросом в мобильный АРМ ответа от бригады
	 */
	public function setEmergencyTeam() {
		$data = $this->ProcessInputData('setEmergencyTeam', true);
		if ( $data === false ) { return false; }
	
		$this->load->helper('NodeJS');
		$params = array('action'=>'set',
			'EmergencyTeamId'=>$data['EmergencyTeam_id'],
			'CmpCallCardId'=>$data['CmpCallCard_id'],
			'PersonId'=>($data['Person_id'])?$data['Person_id']:'',
			'PersonFIO'=>($data['Person_FIO'])?$data['Person_FIO']:$data['Person_Surname'].' '.$data['Person_Firname'].' '.$data['Person_Secname'],
			'PersonFir'=>($data['Person_Firname'])?$data['Person_Firname']:'',
			'PersonSec'=>($data['Person_Secname'])?$data['Person_Secname']:'',
			'PersonSur'=>($data['Person_Surname'])?$data['Person_Surname']:'',
			'PersonBirthday'=>($data['Person_Birthday'])?$data['Person_Birthday']:'',
			'CmpCallCardPrmDate'=>$data['CmpCallCard_prmDate'],
			'CmpReasonName'=>(isset($data['CmpReasonName']))?$data['CmpReasonName']:'',
			'CmpCallTypeName'=>($data['CmpCallType_Name'])?$data['CmpCallType_Name']:'',
			'AdressName'=>$data['Adress_Name']
			);		
		$AdditionalCallCardInfo = $this->dbmodel->getAdditionalCallCardInfo($data);
		$params['CallerInfo'] = (isset($AdditionalCallCardInfo[0]['CallerInfo']))?$AdditionalCallCardInfo[0]['CallerInfo']:'';
		$params['Age'] = (isset($AdditionalCallCardInfo[0]['Age']))?$AdditionalCallCardInfo[0]['Age']:'';
		$params['AgeTypeValue'] = (isset($AdditionalCallCardInfo[0]['AgeTypeValue']))?$AdditionalCallCardInfo[0]['AgeTypeValue']:'';
		$params['SexId'] = (isset($AdditionalCallCardInfo[0]['SexId']))?$AdditionalCallCardInfo[0]['SexId']:'';
		array_walk($params, 'ConvertFromWin1251ToUTF8');
		
		$postSendResult = NodePostRequest($params);
		if ($postSendResult[0]['success']==true) {
			$responseData = json_decode($postSendResult[0]['data'],true);
			if ($responseData["success"]===true) {
				$response = $this->dbmodel->setEmergencyTeam($data);
				$this->ProcessModelSave($response, true)->ReturnData();
			} else {
				$this->ProcessModelSave(array(0=>array('success'=>false,'Err_Msg'=>'В момент передачи вызова статус мобильной бригады - offline')), true)->ReturnData();
			}
		} else {
			$this->ProcessModelSave($postSendResult, true)->ReturnData();
		}
	}
	
	/**
	*	Установка ЛПУ передачи
	*/
	function setLpuTransmit() {
		$data = $this->ProcessInputData('setLpuTransmit', true);
		if ( $data === false ) { return false; }
	
		$response = $this->dbmodel->setLpuTransmit($data);
		
		if ( defined('STOMPMQ_MESSAGE_ENABLE') && defined('STOMPMQ_MESSAGE_ENABLE') && STOMPMQ_MESSAGE_ENABLE === TRUE )
		{
			$mqCardData = $data;	

			$this->dbmodel->sendLpuTransmitToActiveMQ($mqCardData);			
		}
		
		$this->ProcessModelSave($response, true)->ReturnData();
	}
	
	/**
	 * default desc
	 */
	function setPerson() {
		$data = $this->ProcessInputData('setPerson', true);
		if ( $data === false ) { return false; }
	
		$response = $this->dbmodel->setPerson($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}
	/**
	 * default desc
	 */
	function identifiPerson() {
		$data = $this->ProcessInputData('identifiPerson', true);
		if ( $data === false ) { return false; }
	
		$response = $this->dbmodel->identifiPerson($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	* Снятие статус "отказ" карты вызова
	*/
	function unrefuseCmpCallCard() {
		$data = $this->ProcessInputData('unrefuseCmpCallCard', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->unrefuseCmpCallCard($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	
	/**
	 * Установка времени ожидания принятия вызова, созданного в операторе СМП, и переданного в ППД
	 */
	
	function setPPDWaitingTime() {
		$data = $this->ProcessInputData('setPPDWaitingTime', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->setPPDWaitingTime($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		
	}
	/**
	 * default desc
	 */
	function loadCmpCloseCardComboboxesViewForm() {
		$data = $this->ProcessInputData('loadCmpCloseCardComboboxesViewForm', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadCmpCloseCardComboboxesViewForm($data);
		$this->ProcessModelList($response, true)->ReturnData();
	}

	/**
	 * Возвращает использованное оборудование для указанной карты закрытия вызова
	 */
	public function loadCmpCloseCardEquipmentViewForm() {
		$data = $this->ProcessInputData( 'loadCmpCloseCardEquipmentViewForm', false );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->loadCmpCloseCardEquipmentViewForm( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}

	/**
	 * default desc
	 */
	function getDispatchCallUsers() {
		$data = $this->ProcessInputData('getDispatchCallUsers', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->getDispatchCallUsers($data);
		$this->ProcessModelList($response, true,true)->ReturnData();
	}
	
	/**
	 * Возвращает адрес из талона вызова, в т.ч. неформализованные
	 * 
	 * @return output
	 */
	public function getCmpCallCardAddress(){
		$data = $this->ProcessInputData('getCmpCallCardAddress',true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->getCmpCallCardAddress( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}
	
	/**
	 * Возвращает идентификаторы КЛАДР, в случае, если найдет
	 * 
	 * @return array
	 */
	
	public function getUnformalizedAddressStreetKladrParams() {
		$data = $this->ProcessInputData('getUnformalizedAddressStreetKladrParams',true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->getUnformalizedAddressStreetKladrParams( $data );
		$this->ProcessModelSave( $response, true)->ReturnData();
	}
			
	/**
	 * Возвращает список ранжированных причин вызовов для тек. лпу
	 * 
	 * @return array
	 */	
	public function getCmpRangeReasonList() {
		$data = $this->ProcessInputData('getCmpRangeReasonList',true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->getCmpRangeReasonList( $data );
		$this->ProcessModelList( $response, true, true)->ReturnData();
	}
	
	/**
	 * Сохраняет список ранжированных причин вызовов для тек. лпу
	 * 
	 * @return array
	 */	
	public function saveCmpRangeReasonList() {
		$data = $this->ProcessInputData('saveCmpRangeReasonList',true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->saveCmpRangeReasonList( $data );
		$this->ProcessModelSave( $response, true, true)->ReturnData();
	}
	
	/**
	 * Сохранение дерева решений
	 * @return array
	 */
	public function saveDecigionTree() {
		$data = $this->ProcessInputData('saveDecigionTree',true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->saveDecigionTree( $data );
		$this->ProcessModelSave( $response, true)->ReturnData();
	}

	/**
	 * Сохранение дерева решений
	 */
	public function createDecigionTree() {
		$data = $this->ProcessInputData('createDecigionTree',true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->createDecigionTree($data);
		$this->ProcessModelSave( $response, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение ноды дерева решений
	 * @return array
	 */
	public function saveDecigionTreeNode() {
		$data = $this->ProcessInputData('saveDecigionTreeNode',true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->saveDecigionTreeNode( $data );
		$this->ProcessModelSave( $response, true)->ReturnData();
	}

	/**
	 * Сохранение ноды дерева решений
	 * @return array
	 */
	public function deleteDecigionTreeNode() {
		$data = $this->ProcessInputData('deleteDecigionTreeNode',true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->deleteDecigionTreeNode( $data );
		$this->ProcessModelSave( $response, true)->ReturnData();
	}

	/**
	 * Получение дерева решений
	 * @return array
	 */
	public function getDecigionTree() {
		$data = $this->ProcessInputData('getDecigionTree',true);

		if(isset($data['concreteTree']) && $data['concreteTree'] == true){
			$response = $this->dbmodel->getConcreteDecigionTree( $data );
		}else{
			$response = $this->dbmodel->getDecigionTree( $data );
		}

		if(sizeof($response) == 0) return false;

		if(sizeof($response) == 1) $response[0]['leaf']= true;

		foreach ($response as $key => $value) {
			//Если не корневой элемент
			if ($value['AmbulanceDecigionTree_nodeid'] != $value['AmbulanceDecigionTree_nodepid']){
				$value['leaf'] = true;
				$this->addChildToDecigionTreeItem($response,$value);
				unset($response["$key"]);
			}
				
		}
		
		
		$this->ProcessModelList( $response, true)->ReturnData();
		return true;
	}

	/**
	 * Получение структуры дерева решений
	 */
	public function getStructuresTree() {
		$data = $this->ProcessInputData('getStructuresTree',true);

		$Region = $this->dbmodel->getDecigionTreeRegion( $data );
		$LpuBuilding = $this->dbmodel->getDecigionTreeLpuBuilding( $data );
		$Lpu = $this->dbmodel->getDecigionTreeLpu( $data );

		foreach ($Lpu as $keyLpu => $valueLpu) {
			foreach ($LpuBuilding as $key => $value) {

				if($valueLpu['Lpu_id'] == $value['Lpu_id']){
					$value['leaf'] = true;

					$Lpu[$keyLpu]['children'] = array();
					$Lpu[$keyLpu]['children'][] = $value;
				}
			}
		}

		$Region['children']  = $Lpu;
		$Region['expanded']  = true;

		$response = array(
			array(
				'text' => 'Базовое дерево СМП',
				'AmbulanceDecigionTreeRoot_id' => 1,
				'issetTree' => 'true',
				'children' => $Region,
				'expanded' => true
			)
		);

		$this->ProcessModelList( $response, true)->ReturnData();
		return true;
	}

	/**
	 * Получение структуры для которых существует дерево решений
	 */
	public function getStructuresIssetTree() {
		$data = $this->ProcessInputData('getStructuresIssetTree',true);

		$response = $this->dbmodel->getStructuresIssetTree( $data );
		$this->ProcessModelList( $response, true,true)->ReturnData();
		return true;
	}

	/**
	 * Получение структуры дерева решений
	 */
	public function copyDecigionTree() {
		$data = $this->ProcessInputData('copyDecigionTree',true);

		$response = $this->dbmodel->copyDecigionTree( $data );
		$this->ProcessModelSave( $response, true)->ReturnData();
		return true;
	}
	/**
	 * рекурсивная функция добавления поиска родителя по nodeid и добавления потомка
	 * @param type $TreeArray
	 * @param type $item
	 * @return boolean
	 */
			
	private function addChildToDecigionTreeItem(&$TreeArray,$item) {
		$found = false;
		foreach ($TreeArray as $key => $value) {
			if ($value['AmbulanceDecigionTree_nodeid'] == $item['AmbulanceDecigionTree_nodepid']) {
				
				$found = true;
				
				if (!isset($TreeArray["$key"]['children'])) {
					$TreeArray["$key"]['leaf'] = false;
					$TreeArray["$key"]['expanded'] = true;
					$TreeArray["$key"]['children'] = array();
				}
				$TreeArray["$key"]['children'][] = $item;
			}
		}
		if ($found) {
			return true;
		}
		else {
			foreach ($TreeArray as $key => $value) {
				if (isset($TreeArray["$key"]['children'])) {
					$found = $this->addChildToDecigionTreeItem($TreeArray["$key"]['children'], $item);
				}
				if ($found) {
					return true;
				}
			}
		}
		
	}
	
	/**
	 * Получение списка подстанций СМП
	 * @return boolean
	 */
	public function loadSmpUnits() {
		$data = $this->ProcessInputData('loadSmpUnits',true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->loadSmpUnits( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();		
	}
	
	
	/**
	 * Получение списка отделений СМП
	 * @return boolean
	 */
	public function loadLpuCmpUnits() {
		$data = $this->ProcessInputData('loadLpuCmpUnits',true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->loadLpuCmpUnits( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();		
	}
	
	
	/**
	 * Получение списка типов места вызова СМП
	 * @return boolean
	 */
	public function getCmpCallPlaces() {
		$data = $this->ProcessInputData('getCmpCallPlaces',true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->getCmpCallPlaces( $data );
		$this->ProcessModelMultiList( $response, true, true )->ReturnData();		
	}

	/**
	 * Получение справочника нормативов назначения профилей бригад и срочности вызова
	 */
	public function getCmpUrgencyAndProfileStandart() {
		$data = $this->ProcessInputData('getCmpUrgencyAndProfileStandart', false);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->getCmpUrgencyAndProfileStandart($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * Инициализация дефолтной логиги предложения бригад на вызов и назначения срочности вызова
	 */
	public function initiateProposalLogicForLpu() {
		$data = $this->ProcessInputData('initiateProposalLogicForLpu',true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->initiateProposalLogicForLpu($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	/**
	 * Удаление правила логики предложения бригады на вызов
	 * @return boolean
	 */
	public function deleteCmpUrgencyAndProfileStandartRule() {
		$data = $this->ProcessInputData('deleteCmpUrgencyAndProfileStandartRule',true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->deleteCmpUrgencyAndProfileStandartRule($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}
	
	/**
	 * Получене списка мест, привязанных к правилу
	 * @return boolean
	 */
	public function getCmpUrgencyAndProfileStandartPlaces() {
		$data = $this->ProcessInputData('getCmpUrgencyAndProfileStandartPlaces',true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->getCmpUrgencyAndProfileStandartPlaces($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * Получене списка мест, привязанных к правилу
	 * @return boolean
	 */
	public function getCmpUrgencyAndProfileStandartSpecPriority() {
		$data = $this->ProcessInputData('getCmpUrgencyAndProfileStandartSpecPriority',true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->getCmpUrgencyAndProfileStandartSpecPriority($data);
		$response = array(
			'data' => $response,
			'totalCount' => sizeof($response)
		);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение правила предложения бригады на вызов, и срочность вызова в соответствии с указанными местами вызова
	 */
	public function saveCmpUrgencyAndProfileStandartRule() {
		$data = $this->ProcessInputData('saveCmpUrgencyAndProfileStandartRule',true);

		if ( $data === false ) { return false; }

		//Получаем массив идентификаторов мест вызова из JSON-массива
		$CmpCallPlaceType = json_decode($data['CmpCallPlaceType_jsonArray'], true);
		if (($CmpCallPlaceType === NULL)||(!is_array($CmpCallPlaceType))) {
			return array(array('Error_Msg'=>'Список мест вызова не является JSON-массивом'));
		}
		$data['CmpCallPlaceType_Array'] = $CmpCallPlaceType;
		
		//Получаем массив идентификаторов профилей бригад с установленными приоритетами из JSON-массива
		$CmpUrgencyAndProfileStandartRefSpecPriority = json_decode($data['CmpUrgencyAndProfileStandartRefSpecPriority_jsonArray'], true);
		if (($CmpUrgencyAndProfileStandartRefSpecPriority === NULL)||(!is_array($CmpUrgencyAndProfileStandartRefSpecPriority))) {
			return array(array('Error_Msg'=>'Список профилей бригад не является JSON-массивом'));
		}
		$data['CmpUrgencyAndProfileStandartRefSpecPriority_Array'] = $CmpUrgencyAndProfileStandartRefSpecPriority;
		
		$response = $this->dbmodel->saveCmpUrgencyAndProfileStandartRule($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}
	/**
	 * Возвращает список типов приемов вызова: СМП, НМП и тп :)
	 */
	public function getLpuWithOperSmp(){
		$data = $this->ProcessInputData( 'getLpuWithOperSmp', true );
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->getLpuWithOperSmp($data);
		//var_dump($response); exit;
		return $this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Сохранение услуги в карте вызова СМП
	 */
	function saveCmpCallCardUsluga() {
		$data = $this->ProcessInputData('saveCmpCallCardUsluga',true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->saveCmpCallCardUsluga($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка услуг в карте вызова СМП
	 */
	function loadCmpCallCardUslugaGrid() {
		$data = $this->ProcessInputData('loadCmpCallCardUslugaGrid',true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadCmpCallCardUslugaGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение данных для формы редактирования услуги в карте вызова СМП
	 */
	function loadCmpCallCardUslugaForm() {
		$data = $this->ProcessInputData('loadCmpCallCardUslugaForm',true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadCmpCallCardUslugaForm($data);
		$this->ProcessModelList($response, true)->ReturnData();

		return true;
	}

	/**
	 * Читает список контактных лиц организации
	 */
	public function loadCmpEquipmentCombo() {
		$data = $this->ProcessInputData( 'loadCmpEquipmentCombo', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->loadCmpEquipmentCombo( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();
		
		return true;
	}
	
	/**
	 * Возвращает список типов приемов вызова: СМП, НМП и тп :)
	 */
	public function loadCmpCallCardAcceptorList(){
		$data = $this->ProcessInputData( 'loadCmpCallCardAcceptorList', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->loadCmpCallCardAcceptorList();
		return $this->ProcessModelList($response)->ReturnData();
	}	
	
	/**
	 * Возвращает список типов приемов вызова: СМП, НМП и тп :)
	 */
	public function loadLpuHomeVisit(){
		$data = $this->ProcessInputData( 'loadLpuHomeVisit', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->loadLpuHomeVisit($data);
		//var_dump($response); exit;
		return $this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Сохранение актива СМП (создание вызова на дом из АРМ-а администратора СМП)
	 */
	function addHomeVisitFromSMP(){
		$data = $this->ProcessInputData( 'addHomeVisitFromSMP', true );
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->addHomeVisitFromSMP($data);
		return $this->ProcessModelList($response, true, true)->ReturnData();
	}

    /**
     * Получение информации о использовании медикаментов CМП
     */
    function loadCmpCallCardDrugList() {
        $data = $this->ProcessInputData('loadCmpCallCardDrugList',true);
        if ( $data === false ) { return false; }

        $response = $this->dbmodel->loadCmpCallCardDrugList($data);
        $this->ProcessModelList($response, true, true)->ReturnData();

        return true;
    }

	/**
	 * Получение информации о использовании медикаментов CМП (простой учет)
	 */
	function loadCmpCallCardEvnDrugList() {
		$data = $this->ProcessInputData('loadCmpCallCardEvnDrugList',true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadCmpCallCardEvnDrugList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
     * Получение стандарта Мед помощи
     */
    function checkEmergencyStandart() {
        $data = $this->ProcessInputData('checkEmergencyStandart',true);
        if ( $data === false ) { return false; }

        $response = $this->dbmodel->checkEmergencyStandart($data);
        $this->ProcessModelList($response, true, true)->ReturnData();

        return true;
    }

    /**
     * Загрузка списка для комбобокса
     */
    function loadMedStaffFactCombo() {
        $data = $this->ProcessInputData('loadMedStaffFactCombo', true);
        if ( $data === false ) { return false; }

        $response = $this->dbmodel->loadMedStaffFactCombo($data);
        $this->ProcessModelList($response, true, true)->ReturnData();

        return true;
    }

    /**
     * Загрузка списка для комбобокса
     */
    function loadLpuBuildingCombo() {
        $data = $this->ProcessInputData('loadLpuBuildingCombo', true);
        if ( $data === false ) { return false; }

        $response = $this->dbmodel->loadLpuBuildingCombo($data);
        $this->ProcessModelList($response, true, true)->ReturnData();

        return true;
    }

    /**
     * Загрузка списка для комбобокса
     */
    function loadStorageCombo() {
        $data = $this->ProcessInputData('loadStorageCombo', true);
        if ( $data === false ) { return false; }

        $response = $this->dbmodel->loadStorageCombo($data);
        $this->ProcessModelList($response, true, true)->ReturnData();

        return true;
    }

    /**
     * Загрузка списка для комбобокса
     */
    function loadMolCombo() {
        $data = $this->ProcessInputData('loadMolCombo', true);
        if ( $data === false ) { return false; }

        $response = $this->dbmodel->loadMolCombo($data);
        $this->ProcessModelList($response, true, true)->ReturnData();

        return true;
    }


    /**
     * Загрузка списка для комбобокса
     */
    function loadStorageZoneCombo() {
        $data = $this->ProcessInputData('loadStorageZoneCombo', true);
        if ( $data === false ) { return false; }

        $response = $this->dbmodel->loadStorageZoneCombo($data);
        $this->ProcessModelList($response, true, true)->ReturnData();

        return true;
    }

    /**
     * Загрузка списка для комбобокса
     */
    function loadDrugPrepFasCombo() {
        $data = $this->ProcessInputData('loadDrugPrepFasCombo', true);
        if ( $data === false ) { return false; }

        $response = $this->dbmodel->loadDrugPrepFasCombo($data);
        $this->ProcessModelList($response, true, true)->ReturnData();

        return true;
    }

    /**
     * Загрузка списка для комбобокса
     */
    function loadDrugCombo() {
        $data = $this->ProcessInputData('loadDrugCombo', true);
        if ( $data === false ) { return false; }

        $response = $this->dbmodel->loadDrugCombo($data);
        $this->ProcessModelList($response, true, true)->ReturnData();

        return true;
    }

    /**
     * Загрузка списка для комбобокса
     */
    function loadDocumentUcStrOidCombo() {
        $data = $this->ProcessInputData('loadDocumentUcStrOidCombo', true);
        if ( $data === false ) { return false; }

        $response = $this->dbmodel->loadDocumentUcStrOidCombo($data);
        $this->ProcessModelList($response, true, true)->ReturnData();

        return true;
    }

    /**
     * Загрузка списка для комбобокса
     */
    function loadGoodsUnitCombo() {
        $data = $this->ProcessInputData('loadGoodsUnitCombo', true);
        if ( $data === false ) { return false; }

        $response = $this->dbmodel->loadGoodsUnitCombo($data);
        $this->ProcessModelList($response, true, true)->ReturnData();

        return true;
    }

    /**
     * Получение значений по умолчанию для формы использования медикаментов
     */
    function getCmpCallCardDrugDefaultValues() {
        $data = $this->ProcessInputData('getCmpCallCardDrugDefaultValues', true);
        if ( $data === false ) { return false; }

        $response = $this->dbmodel->getCmpCallCardDrugDefaultValues($data);
        $this->ProcessModelSave($response, true, true)->ReturnData();

        return true;
    }

	/**
	 * Запись события карты в журнал.
	 */
	function setCmpCallCardEvent(){
		$data = $this->ProcessInputData('setCmpCallCardEvent', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->setCmpCallCardEvent($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();

		return true;
	}

    /**
     * Входные параметры CmpCallCard_id
	 * Печатает справку о вызове СМП
     */
    function printCmpCall()
    {
    	$data = $this->ProcessInputData('printCmpCall', true);
    	if ( $data === false ) { return false; }		

    	$response = $this->dbmodel->printCmpCall($data);

 		$defaultAdress = '&nbsp;______________________________________________________________________';
 		$defaultFIO = '______________________________________________________________________';
 		$defaultNum  = '_____________________';
 		$defaultDate = '____________________';
 		$defaultTime = '&nbsp;____________________';
 		$defaultBirthDay = '_______________________';
 		$defaultYo = '_______&nbsp______________________';
 		$defaultDiag = '__________________________________________________________________';
 		$defaultHiMed =   "____________________&nbsp&nbsp&nbsp&nbsp___________________";
 		$defaultMedStat = "____________________&nbsp&nbsp&nbsp&nbsp___________________";

 		$sign = '_______________';

 		$Num = empty($response[0]['CmpCallCard_Numv']) ? $defaultNum.'&nbsp' : $response[0]['CmpCallCard_Numv'];
    	$Date = empty($response[0]['CallDate']) ? $defaultDate : $response[0]['CallDate'];
    	$Time = empty($response[0]['CallTime']) ? $defaultTime : $response[0]['CallTime'];
    	$Adress = empty($response[0]['adress']) ? $defaultAdress : $response[0]['adress'];
    	$FIO = empty($response[0]['person_fio']) ? $defaultFIO : $response[0]['person_fio'];
    	$BirthDay = empty($response[0]['Person_BirthDay']) ? $defaultBirthDay : $response[0]['Person_BirthDay'];
    	$Yo = empty($response[0]['yo']) ? $defaultYo : $response[0]['yo'];
    	$Diag = empty($response[0]['Diag_Name']) ? $defaultDiag : $response[0]['Diag_Name'];
    	$HiMed = empty($response[0]['HiMed']) ? $defaultHiMed : $response[0]['HiMed'];        	
    	$MedStat = empty($response[0]['MedStat']) ? $defaultMedStat : $response[0]['MedStat'];

    	$html = "<h4>Справка о вызове скорой медицинской помощи № ______</h4>";
    	$html .= "<div class='printCmpCall'>";
		$html .= "<p>Вызов  № ".$Num." &nbspдата: ".$Date." &nbspвремя: ".$Time."</p>";
		$html .= "<p>Адрес вызова: ".$Adress."</p>";
		$html .= "<p>ФИО пациента: ".$FIO."</p>";
		$html .= "<p>Дата рождения пациента: ".$BirthDay."&nbsp&nbspВозраст: ".$Yo."</p>";
		$html .= "<p>Установлен диагноз: ".$Diag."</p>";
		$html .= "<p style='text-align:center;'>Выдано: по месту работы/в следственные органы/в поликлинику/по месту требования <br>(нужное подчеркнуть)</p>";
 		$html .= "<div class='signature'><div class='medvrach'>Старший врач: </div><div class='medvrach_fio'>".$HiMed."</div><div class='sign_'>".$sign."</div></div>";
 		$html .= "<div class='signature'><div class='sign'>подпись</div></div>";
 		$html .= "<div class='signature'><div class='medvrach'>Медицинский статистик: </div><div class='medvrach_fio'>".$MedStat."</div><div class='sign_'>".$sign."</div></div>";
 		$html .= "<div class='signature'><div class='sign'>подпись</div></div>";
 		$html .= "<p>Дата выдачи справки: ".date('d.m.Y', time())."</p>";
 		$html .= "</div>";
 		$html .= "<style>
 		p {font-size:14px;text-align:left;} h4 {font-size:16px; text-align:center; margin-bottom:50px;} .printCmpCall{margin-left:50px;}
 		.signature {width:710px;}
 		.medvrach {width:25%; float:left;}
 		.medvrach_fio {text-align:left; width:50%; float:left;}
 		.sign_ {width:25%; float:right;}
		.sign {text-align:right; padding-right:12.5%;}
 		</style>";

    	echo $html;
    	return true;
    }

	/**
	 * Получение всех подстанций СМП региона
	 */
	public function loadRegionSmpUnits() {
		$data = $this->ProcessInputData('loadRegionSmpUnits',true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->loadRegionSmpUnits( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}

	/**
	 * Автоматическое создание записей о пациентах СМП, которые не были созданы из-за отсутсвия связи с основным сервером.
	 * Должен запускаться по заданию на сервере СМП.
	 */
	public function autoCreateCmpPerson() {
		$data = $this->ProcessInputData('autoCreateCmpPerson',true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->autoCreateCmpPerson($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
	
	/**
	 * уникальность введенного номера вызова за день и за год
	 */
	public function existenceNumbersDayYear() {
		$data = $this->ProcessInputData('existenceNumbersDayYear', true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->existenceNumbersDayYear($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}
	
	/**
	 * получение диагнозов карты
	 */
	public function getCmpCallDiagnosesFields() {
		$data = $this->ProcessInputData('getCmpCallDiagnosesFields', true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->getCmpCallDiagnosesFields($data);
		//$this->ProcessModelSave($response, true)->ReturnData();
		$this->ProcessModelList( $response )->ReturnData();
	}

	/**
	 * Возвращает поля экспертной оценки для карты закрытия вызова 110у
	 */
	public function getExpertResponseFields(){

		$response = $this->dbmodel->getExpertResponseFields();
		$this->ProcessModelSave( $response )->ReturnData();
	}

	/**
	 * Возвращает оценки карты 110у
	 */
	public function getCmpCloseCardExpertResponses(){
		$data = $this->ProcessInputData('getCmpCloseCardExpertResponses', true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->getCmpCloseCardExpertResponses($data);
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}

	/**
	 * Возвращает список федеральных результатов для карты 110у
	 */
	public function getFedLeaveTypeList(){
		$response = $this->dbmodel->getFedLeaveTypeList();
		$this->ProcessModelList( $response )->ReturnData();
	}

	/**
	 * Получение из Wialon пройденного расстояния бригадой за промежуток времени
	 */
	public function getTheDistanceInATimeInterval(){
		$data = $this->ProcessInputData('getTheDistanceInATimeInterval', true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->getTheDistanceInATimeInterval($data);

		$this->ProcessModelSave( $response, true, true )->ReturnData();
	}

	/**
	 * Возвращает параметры начала и окончания дня/года из настроек
	 * startDateTime - начало дня
	 * endDateTime - конец дня
	 * firstDayCurrentYearDateTime - начало года
	 * firstDayNextYearDateTime - конец года
	 */
	public function getDatesToNumbersDayYear(){
		$data = $this->ProcessInputData('getDatesToNumbersDayYear', true);
		if ( $data === false ) {
			return false;
		}
		$response['data'] = $this->dbmodel->getDatesToNumbersDayYear($data);

		$this->ProcessModelSave( $response, true, true )->ReturnData();
	}

	/**
	 * Сохранение экспертных оценок
	 * принимает json массив ExpertResponseJSON и CmpCloseCard_id
	 */
	public function saveCmpCloseCardExpertResponseList(){
		$data = $this->ProcessInputData('saveCmpCloseCardExpertResponseList', true);
		if ( $data === false ) {
			return false;
		}
		$data['ExpertResponseList'] = json_decode($data['ExpertResponseJSON'],true);
		$response = $this->dbmodel->saveCmpCloseCardExpertResponseList($data);

		$this->ProcessModelSave( $response, true, true )->ReturnData();
	}
	
	/**
	 * список пациентов для журнала расхождения
	 */
	public function getPatientDiffList(){
		$data = $this->ProcessInputData('getPatientDiffList', true);
		if ( $data === false ) {
			return false;
		}

		if (isset($data['lpu_id'])) {
			$data['Lpu_id'] = $data['lpu_id'];
		} else {
			$data['Lpu_id'] = $data['session']['lpu_id'];
		}

		unset($this->db);
		$this->load->database('default');
		$response = $this->dbmodel->getPatientDiffList($data);
		unset($this->db);
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}
	
	/**
	 * Проверка оплаты диагноза по ОМС
	 */
	function checkDiagFinance(){
		$data = $this->ProcessInputData('checkDiagFinance', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->checkDiagFinance($data);
		if(is_array($response) && isset($response[0])){
			$this->ReturnData($response[0]);
		}else{
			//$this->ReturnData(array('success'=>false, 'Err_Msg'=>'Данных в справочнике о оплате диагоза по СМП не найдено'));
			return false;
		}

	}

	/**
	 * для тестов отправки запросов в ActiveMQ
	 */
	function testAM() {
		if (isSuperAdmin()) {
			$this->dbmodel->testAM();
		}
	}

	/**
	 * удаление медикаментов из карты при замене подстанции
	 */
	function deleteCmpCallCardEvnDrug()
	{
		$data = $_POST;
		$data = array_merge(  getSessionParams(), $data);
		if ( $data === false ) { return false; }
		if ( !isset($data['CmpCallCard_id']) || empty($data['CmpCallCard_id']) ) { return false; }
		//Если переданы данные об использовании медикаментов
		if (isset($data['CmpCallCardDrugJSON'])) {

			$dparams = array(
				'CmpCallCard_id' => $data['CmpCallCard_id'],
				'LpuBuilding_id' => $data['LpuBuilding_id'],
				'json_str' => $data['CmpCallCardDrugJSON'],
				'pmUser_id' => $data['pmUser_id'],
				'Server_id' => $data['Server_id']
			);
			if (!empty($data['LpuBuilding_IsWithoutBalance']) && $data['LpuBuilding_IsWithoutBalance'] == 'true') {
				$response = $this->dbmodel->saveCmpCallCardSimpleDrugFromJSON($dparams);
			} else {
				$response = $this->dbmodel->saveCmpCallCardDrugFromJSON($dparams);
			}
			$response['success'] = true;
			if (!$this->dbmodel->isSuccessful($response)) {
				//$this->dbmodel->rollbackTransaction() ;
				if (!empty($save_drug_result[0]['Error_Msg'])) {
					$this->ReturnError($save_drug_result[0]['Error_Msg']);
					return false;
				}
			}
		}
		$this->ProcessModelSave($response, true, 'Ошибка при закрытии карты вызова')->ReturnData();
		return true;
	}
	/**
	 * Получение информации о использовании медикаментов CМП (простой учет)
	 */
	function loadCmpCallCardSimpleDrugList() {
		$data = $this->ProcessInputData('loadCmpCallCardSimpleDrugList',true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadCmpCallCardSimpleDrugList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	/**
	 * Получение информации о диагнозах
	 */
	function getSidOoidDiags() {
		$data = $this->ProcessInputData('getSidOoidDiags',true);
		if ( getRegionNick() == 'kz' || $data['session']['region']['nick'] == 'kz' || $data === false ) { return false; }
		$response = $this->dbmodel->getSidOoidDiags($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	/**
	 * Получение списка номеров карт(110/у), которых запросил СМО
	 */
	function getSmoQueryCallCards() 
	{
		$data = $this->ProcessInputData('getSmoQueryCallCards',true);
		if ( $data === false ) 
		{
			 return false; 
		}
		$response = $this->dbmodel->getSmoQueryCallCards($data);
		$this->ReturnData($response);
	}
	/**
	* Запись в БД номеров карт 110/у, которых запросил СМО
	*/
	function setSmoQueryCallCards()
	{
		$data = $this->ProcessInputData('setSmoQueryCallCards',true);
		if ( $data === false ) 
		{
			return false; 
		}
		$response = $this->dbmodel->setSmoQueryCallCards($data);
		return $response;
	}
	/**
	* Удаляем прошлый запрос карт от СМО
	*/
	function delSmoQueryCallCards()
	{
		$data = $this->ProcessInputData('delSmoQueryCallCards',true);
		$response = $this->dbmodel->delSmoQueryCallCards($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	/**
	* Сохранение запроса СМО
	*/
	function saveSmoQuery()
	{
		$errors = array();
		$data = $this->ProcessInputData('saveSmoQuery',true);
		if ( $data === false ) 
		{
			return false;
		}
		$dataArray = json_decode($data['jsondata'], 1);
		$params = array();
		$params['Lpu_id'] = $dataArray['Lpu_id'];
		$params['OrgSmo_id'] = $dataArray['OrgSmo_id'];
		$params['pmUser_id'] = $dataArray['pmUser_id'];
		$params['insDT'] = $dataArray['insDT'];
		foreach ($dataArray['cardNumbers'] as $CardNumber)
		{
			$params['CardNumber'] = trim($CardNumber);
			$_POST = $params;
			$response = $this->setSmoQueryCallCards();
			if (!empty($response[0]['Error_Message']) && $response[0]['Error_Code'] != 309)
			{
				$errors[$params['CardNumber']] = $response[0]['Error_Message']; 
			}
		}
		if (!empty($errors)){
			$this->ReturnData(array(
					'success' => false,
					'Error_Code' => -1,
					'Error_Msg' => toUTF('Произошла ошибка при работе с БД.'),
					'ErrorText' => $response[0]['Error_Message'],
					'FullTextError'=>json_encode($errors)
			));
		} 
		else
		{
			$this->ReturnData(array(
					array(
							'success' => true,
							'Error_Code' => 0
					)
			));
		}
	}

	/**
	 * Получаем флаг опер отдела "Включить функцию «Контроль вызовов»"
	 */
	function getIsCallControllFlag(){

		$data = $this->ProcessInputData('getIsCallControllFlag',true);
		$response = $this->dbmodel->getIsCallControllFlag($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
	}

	/*
	 * получаем адрес по ИД талона вызова
	 */
	function getAdressByCardId() {
		$data = $this->ProcessInputData('getAdressByCardId',true);
		$response = $this->dbmodel->getAdressByCardId($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
	}

	/*
	 * проставляем талону МО передачи СМП и подстанцию
	 */
	function sendCallToSmp() {
		$data = $this->ProcessInputData('sendCallToSmp',true);
		$response = $this->dbmodel->sendCallToSmp($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
	}
}

