<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * CmpCallCardImport_model - модель
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 *
 */
class SoapClientExt extends SoapClient
{
	public $customXml = null;
	public $lastRequest = null;

	/**
	 * Устаналивает кастомную XML для отправки в сервис
	 */
	public function setCustomXml($customXml) {
		$this->customXml = $customXml;
		return $this;
	}

	/**
	 * Получение последнего запроса
	 */
	public function __getLastRequest() {
		return $this->lastRequest;
	}

	/**
	 * Выполнение SOAP запроса
	 */
	public function __doRequest($request, $location, $action, $version, $one_way = 0) {
		if (!empty($this->customXml)) {
			$request = $this->customXml;
		}

		$this->lastRequest = $request;

		return parent::__doRequest($request, $location, $action, $version, $one_way);
	}
}

class CmpCallCardImport_model extends swModel {
	protected $soapClient = null;
	protected $soapUri = null;
	protected $soapLocation = null;
	protected $ServiceListLog_id = null;

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		ini_set("default_socket_timeout", 600);

		$this->load->library('textlog', array('file'=>'CmpCallCardImport_'.date('Y-m-d').'.log'));
		$this->load->model('CmpCallCard_model', 'cccmodel');
	}

	/**
	 * Создание исключений по ошибкам
	 */
	function exceptionErrorHandler($errno, $errstr, $errfile, $errline) {
		switch ($errno) {
			case E_NOTICE:
			case E_USER_NOTICE:
				$errors = "Notice";
				break;
			case E_WARNING:
			case E_USER_WARNING:
				$errors = "Warning";
				break;
			case E_ERROR:
			case E_USER_ERROR:
				$errors = "Fatal Error";
				break;
			default:
				$errors = "Unknown Error";
				break;
		}

		$msg = sprintf("%s:  %s in %s on line %d", $errors, $errstr, $errfile, $errline);
		throw new ErrorException($msg, 0, $errno, $errfile, $errline);
	}

	/**
	 * Получние данных из справочника
	 */
	function getSpr($table, $code) {
		if(empty($code)) return null;
		return $this->getFirstResultFromQuery("
			select top 1 {$table}_id
			from v_$table (nolock)
			where {$table}_Code = :code
		", array(
			'code' => $code
		));
	}

	/**
	 * Получние данных из справочника
	 */
	function getSprByName($table, $name) {
		if(empty($name)) return null;
		return $this->getFirstResultFromQuery("
			select top 1 {$table}_id
			from v_$table (nolock)
			where {$table}_Name = :name
		", array(
			'name' => $name
		));
	}

	/**
	 * Получение идентификатора результата выезда из справочника стыковки результатов
	 */
	function getCmpResult($id){
		if(empty($id)) return null;

		return $this->getFirstResultFromQuery("
			select top 1 CRL.CmpCloseCardCombo_id
			from v_CmpResultLink CRL (nolock)
			where CRL.CmpResultLink_CmpResultGIT = :id
		", array(
			'id' => $id
		));
	}

	/**
	 * Получение идентификатора результата окозания медицинской помощи
	 */
	function getComboResult($id){
		if(empty($id)) return null;

		$ComboResultLink = array(
			1 => 106,
			2 => 107,
			3 => 108
		);

		return isset($ComboResultLink[$id]) ? $ComboResultLink[$id] : null;

	}

	/**
	 * Получение идентификатора результата окозания медицинской помощи
	 */
	function getComboAgeType($id){
		if(empty($id)) return null;

		$ComboAgeTypeLink = array(
			1 => 219,
			2 => 220,
			4 => 221
		);

		return isset($ComboAgeTypeLink[$id]) ? $ComboAgeTypeLink[$id] : null;

	}

	/**
	 * Получние идентификатора социального положения из справочника стыковки социальных положений
	 */
	function getSocStatus($id) {
		if(empty($id)) return null;
		return $this->getFirstResultFromQuery("
			select top 1 CmpCloseCardCombo_id
			from v_CmpSocStatusLink (nolock)
			where CmpSocStatusLink_SocStatusGIT = :id
		", array(
			'id' => $id
		));
	}

	/**
	 * getKLRgn
	 */
	function getKLRgn($KLAdr_Code) {
		return $this->getFirstResultFromQuery("
			select top 1 lvl1.KLArea_id
			from dbo.KLArea lvl4 (nolock)
			inner join dbo.KLArea lvl2 (nolock) on lvl2.KLArea_id = lvl4.KLArea_pid and lvl2.KLAreaLevel_id = 2
			inner join dbo.KLArea lvl1 (nolock) on lvl1.KLArea_id = lvl2.KLArea_pid and lvl1.KLAreaLevel_id = 1
			where lvl4.KLAdr_Code = :KLAdr_Code and lvl4.KLAreaLevel_id in (3,4)
		", array(
			'KLAdr_Code' => $KLAdr_Code
		));
	}

	/**
	 * getKLCity
	 */
	function getKLCity($KLAdr_Code) {
		return $this->getFirstResultFromQuery("
			select top 1 KLArea_id
			from dbo.KLArea (nolock)
			where KLAdr_Code = :KLAdr_Code and KLAreaLevel_id = 3
		", array(
			'KLAdr_Code' => $KLAdr_Code
		));
	}

	/**
	 * getKLTown
	 */
	function getKLTown($KLAdr_Code) {
		return $this->getFirstResultFromQuery("
			select top 1 KLArea_id
			from dbo.KLArea (nolock)
			where KLAdr_Code = :KLAdr_Code and KLAreaLevel_id = 4
		", array(
			'KLAdr_Code' => $KLAdr_Code
		));
	}

	/**
	 * getKLStreet
	 */
	function getKLStreet($KLAdr_Code) {
		return $this->getFirstResultFromQuery("
			select top 1 KLStreet_id
			from dbo.KLStreet (nolock)
			where KLAdr_Code = :KLAdr_Code
		", array(
			'KLAdr_Code' => $KLAdr_Code
		));
	}

	/**
	 * Сохранение карты вызова
	 */
	function saveCmpCallCard($cccdata) {
		
		$params = array(
			'CmpCallCard_id' => $cccdata['CmpCallCard_id'],
			'CmpCallCard_Numv' => preg_replace("/[^0-9]/", '', $cccdata['Number_day']),
			'CmpCallCard_Ngod' => preg_replace("/[^0-9]/", '', $cccdata['Number']),
			'CmpCallCard_NumvPr' => preg_replace("/[^a-z]/i", '', $cccdata['Number_day']),
			'CmpCallCard_NgodPr' => preg_replace('/[^a-z]/i','', $cccdata['Number']),
			'setDay_num' => preg_replace("/[^0-9]/", '', $cccdata['Number_day']), // установка номера без проверки
			'setYear_num' => preg_replace("/[^0-9]/", '', $cccdata['Number']), // установка номера без проверки
			'KLRgn_id' => isset($cccdata['Dict_City']) ? $this->getKLRgn($cccdata['Dict_City']->KLADRCode) : null,
			'KLSubRgn_id' => null,
			'KLCity_id' => isset($cccdata['Dict_City']) ? $this->getKLCity($cccdata['Dict_City']->KLADRCode) : null,
			'KLTown_id' => isset($cccdata['Dict_City']) ? $this->getKLTown($cccdata['Dict_City']->KLADRCode) : null,
			'KLStreet_id' => isset($cccdata['Dict_Street']) ? $this->getKLStreet($cccdata['Dict_Street']->KLADRCode) : null,
			'CmpCallCard_Dom' => isset($cccdata['Dom']) ? $cccdata['Dom'] : null,
			'CmpCallCard_Korp' => isset($cccdata['Korpus']) ? $cccdata['Korpus'] : null,
			'CmpCallCard_Kvar' => isset($cccdata['Kvartira']) ? $cccdata['Kvartira'] : null,
			'CmpCallCard_Podz' => null,
			'CmpCallCard_Etaj' => null,
			'CmpCallCard_Kodp' => null,
			'CmpCallCard_Telf' => isset($cccdata['Phone']) ? $cccdata['Phone'] : null,
			'CmpReason_id' => isset($cccdata['Dict_Povod']) ? $this->getSpr('CmpReason', $cccdata['Dict_Povod']->ID) : null,
			'Person_id' => $cccdata['Person_id'],
			'Person_SurName' => isset($cccdata['Pacient_Fam']) ? $cccdata['Pacient_Fam'] : null,
			'Person_FirName' => isset($cccdata['Pacient_Name']) ? $cccdata['Pacient_Name'] : null,
			'Person_SecName' => isset($cccdata['Pacient_Patronomyc']) ? $cccdata['Pacient_Patronomyc'] : null,
			'Person_Age' => isset($cccdata['Age']) ? $cccdata['Age'] : null,
			'Person_BirthDay' => isset($cccdata['Birthday']) ? $cccdata['Birthday'] : null,
			'Sex_id' => isset($cccdata['Dict_Gender']->ID) ? $cccdata['Dict_Gender']->ID : null,
			'CmpCallCard_Ktov' => isset($cccdata['Caller_FIO']) ? $cccdata['Caller_FIO'] : null,
			'CmpCallType_id' => isset($cccdata['Dict_Vid']) ? $this->getSprByName('CmpCallType', $cccdata['Dict_Vid']->Name) : null,
			'CmpCallCard_IsExtra' => isset($cccdata['Dict_Priznak']) ? ($cccdata['Dict_Priznak']->ID == 5 ? 1 : 2) : null,
			'CmpCallCard_prmDate' => date('Y-m-d', strtotime($cccdata['Received'])),
			'CmpCallCard_prmTime' => date('H:i', strtotime($cccdata['Received'])),
			'CmpCallCard_Dlit' => null,
			'Lpu_id' => $cccdata['Lpu']['Lpu_id'],
			'LpuBuilding_id' => $cccdata['Lpu']['LpuBuilding_id'],
			'CmpCallPlaceType_id' => null,
			'CmpCallCard_Urgency' => isset($cccdata['Priority']) ? $cccdata['Priority'] : null,
			'CmpCallCard_IsOpen' => 2,
			 // --- мимикрируем ---
			'action' => empty($cccdata['CmpCallCard_id']) ? 'add' : 'edit',
			'ARMType' => null,
			'session' => array('groups' => ''),
			'Server_id' => 1,
			'pmUser_id' => 1,
		);
				
		if (!empty($_REQUEST['getDebug'])) {
			echo "Обработанные данные для CmpCallCard:<br>";
			echo "<textarea cols=150 rows=20>" . print_r($params, true) . "</textarea><br><br>";
		}
		
		$result = $this->cccmodel->saveCmpCallCard($params);
		
		if (!empty($_REQUEST['getDebug'])) {
			echo "Результат сохранения CmpCallCard:<br>";
			echo print_r($result, true) . "<br><br>";
		}
		
		return $result;
	}

	/**
	 * Сохранение карты закрытия
	 */
	function saveCmpCloseCard($cccdata) {
		
		$CmpCloseCard_id = $this->getFirstResultFromQuery("
			select top 1 CmpCloseCard_id from v_CmpCloseCard (nolock) where CmpCallCard_id = :CmpCallCard_id
		", array(
			'CmpCallCard_id' => $cccdata['CmpCallCard_id']
		));
		
		$params = array(
			'CmpCloseCard_id' => $CmpCloseCard_id,
			'CmpCallCard_id' => $cccdata['CmpCallCard_id'],
			'CmpCallerType_id' => null,
			'Ktov' => isset($cccdata['Caller_FIO']) ? $cccdata['Caller_FIO'] : null,
			'CallPovod_id' => isset($cccdata['Dict_Povod']) ? $this->getSpr('CmpReason', $cccdata['Dict_Povod']->ID) : null,
			'CmpCallType_id' => isset($cccdata['Dict_Vid']) ? $this->getSprByName('CmpCallType', $cccdata['Dict_Vid']->Name) : null,
			'CmpCloseCard_IsExtra' => isset($cccdata['Dict_Priznak']) ? ($cccdata['Dict_Priznak']->ID == 5 ? 1 : 2) : null,
			'Day_num' => preg_replace("/[^0-9]/", '', $cccdata['Number_day']),
			'Year_num' => preg_replace("/[^0-9]/", '', $cccdata['Number']),
			'CmpCloseCard_DayNumPr' => preg_replace("/[^a-z]/i", '', $cccdata['Number_day']),
			'CmpCloseCard_YearNumPr' => preg_replace("/[^a-z]/i", '', $cccdata['Number']),
			'setDay_num' => preg_replace("/[^0-9]/", '', $cccdata['Number_day']), // установка номера без проверки
			'setYear_num' => preg_replace("/[^0-9]/", '', $cccdata['Number']), // установка номера без проверки
			'Feldsher_id' => null,
			'FeldsherAccept' => null,
			'FeldsherTrans' => null,
			'MedStaffFact_id' => $cccdata['BrigadaHead']['MedStaffFact_id'],
			'StationNum' => null,
			'Lpu_id' => $cccdata['Lpu']['Lpu_id'],
			'LpuBuilding_id' => $cccdata['Lpu']['LpuBuilding_id'],
			'LpuSection_id' => null,
			'EmergencyTeam_id' => null,
			'EmergencyTeamNum' => null,
			'CmpCloseCard_IsProfile' => null, //Brigada_Type
			'AcceptTime' => $this->convertDT($cccdata['Received']),
			'CmpCloseCard_PassTime' => isset($cccdata['Peredacha_Visova_Brigade']) ? $cccdata['Peredacha_Visova_Brigade'] : null,
			'TransTime' => $this->convertDT(isset($cccdata['Viezd_Na_Vizov']) ? $cccdata['Viezd_Na_Vizov'] : null),
			'GoTime' => $this->convertDT(isset($cccdata['Viezd_Na_Vizov']) ? $cccdata['Viezd_Na_Vizov'] : null),
			'ArriveTime' => $this->convertDT(isset($cccdata['Pribitye_Na_Mesto_Visova']) ? $cccdata['Pribitye_Na_Mesto_Visova'] : null),
			'TransportTime' => $this->convertDT(isset($cccdata['Start_Transfer_Pacient']) ? $cccdata['Start_Transfer_Pacient'] : null),
			'ToHospitalTime' => $this->convertDT(isset($cccdata['Pribitye_V_LPU']) ? $cccdata['Pribitye_V_LPU'] : null),
			'EndTime' => $this->convertDT(isset($cccdata['Okonchanie_Visova']) ? $cccdata['Okonchanie_Visova'] : null),
			'BackTime' => $this->convertDT(isset($cccdata['Vosvrachenie_Na_Sub_SMP']) ? $cccdata['Vosvrachenie_Na_Sub_SMP'] : null),
			'SummTime' => $this->convertDT(isset($cccdata['Obchee_Vremya']) ? $cccdata['Obchee_Vremya'] : null),
			'Area_id' => null,
			'City_id' => isset($cccdata['Dict_City']) ? $this->getKLCity($cccdata['Dict_City']->KLADRCode) : null,
			'Town_id' => isset($cccdata['Dict_City']) ? $this->getKLTown($cccdata['Dict_City']->KLADRCode) : null,
			'Street_id' => isset($cccdata['Dict_Street']) ? $this->getKLStreet($cccdata['Dict_Street']->KLADRCode) : null,
			'CmpCloseCard_Street' => isset($cccdata['Dict_Street']) ? $cccdata['Dict_Street']->Name : null,
			'House' => isset($cccdata['Dom']) ? $cccdata['Dom'] : null,
			'Korpus' => isset($cccdata['Korpus']) ? $cccdata['Korpus'] : null,
			'Entrance' =>  null,
			'CodeEntrance' => null,
			'Level' => null,
			'Office' => isset($cccdata['Kvartira']) ? $cccdata['Kvartira'] : null,
			'Room' => null,
			'Person_id' => $cccdata['Person_id'],
			'Fam' => isset($cccdata['Pacient_Fam']) ? $cccdata['Pacient_Fam'] : null,
			'Name' => isset($cccdata['Pacient_Name']) ? $cccdata['Pacient_Name'] : null,
			'Middle' => isset($cccdata['Pacient_Patronomyc']) ? $cccdata['Pacient_Patronomyc'] : null,
			'Age' => isset($cccdata['Age']) ? $cccdata['Age'] : null,
			'Sex_id' => isset($cccdata['Dict_Gender']->ID) ? $cccdata['Dict_Gender']->ID : null,
			'Person_PolisSer' => !empty($cccdata['serial_strah']) ? $cccdata['serial_strah'] : null,
			'Person_PolisNum' => !empty($cccdata['serial_strah']) ? isset($cccdata['number_strah']) ? $cccdata['number_strah'] : null : null,
			'CmpCloseCard_PolisEdNum' => empty($cccdata['serial_strah']) ? isset($cccdata['number_strah']) ? $cccdata['number_strah'] : null : null,
			'Work' => null,
			'DocumentNum' => (isset($cccdata['serial_doc']) ? $cccdata['serial_doc'] : null) . " " . (isset($cccdata['number_doc']) ? $cccdata['number_doc'] : null),
			'Phone' => isset($cccdata['Phone']) ? $cccdata['Phone'] : null,
			'Complaints' => isset($cccdata['Zhalobyi']) ? $cccdata['Zhalobyi'] : null,
			'Anamnez' => isset($cccdata['Anamnez']) ? $cccdata['Anamnez'] : null,
			'isAlco' => isset($cccdata['Bolnoi_piyan']) ? $cccdata['Bolnoi_piyan'] : null,
			'isMenen' => (isset($cccdata['Soznanie_Meningealnyie_znaki']) && $cccdata['Soznanie_Meningealnyie_znaki'] >=1 && $cccdata['Soznanie_Meningealnyie_znaki']) <= 6 ? 2 : 1,
			'isAnis' => null,
			'isNist' => isset($cccdata['Zrachki_Nistagm']) ? $cccdata['Zrachki_Nistagm'] : null,
			'isLight' => isset($cccdata['Zrachki_Reaktsiya_na_svet']) ? $cccdata['Zrachki_Reaktsiya_na_svet'] : null,
			'isAcro' => isset($cccdata['Kozhnyie_pokrovyi_Agrotsianoz']) ? $cccdata['Kozhnyie_pokrovyi_Agrotsianoz'] : null,
			'isMramor' => isset($cccdata['Kozhnyie_pokrovyi_Mramornost']) ? $cccdata['Kozhnyie_pokrovyi_Mramornost'] : null,
			'isHale' => null,
			'isPerit' => null,
			'Urine' => isset($cccdata['Mocheispuskanie']) ? $cccdata['Mocheispuskanie'] : null,
			'Shit' => isset($cccdata['Zhivot_Harakter_stula']) ? $cccdata['Zhivot_Harakter_stula'] : null,
			'OtherSympt' => isset($cccdata['Drugie_simptomyi']) ? $cccdata['Drugie_simptomyi'] : null,
			'WorkAD' => isset($cccdata['Privyichnoe_AD']) ? $cccdata['Privyichnoe_AD'] : null,
			'AD' => null,
			'Pulse' => isset($cccdata['Puls_Do']) ? $cccdata['Puls_Do'] : null,
			'Chss' => isset($cccdata['ChSS_Do']) ? intval($cccdata['ChSS_Do']) : null,
			'Chd' => isset($cccdata['ChD_Do']) ? $cccdata['ChD_Do'] : null,
			'Temperature' => isset($cccdata['T_Do']) ? $cccdata['T_Do'] : null,
			'Pulsks' => isset($cccdata['Pulsoksimetriya_Do']) ? $cccdata['Pulsoksimetriya_Do'] : null,
			'Gluck' => isset($cccdata['Glyukometriya_Do']) ? $cccdata['Glyukometriya_Do'] : null,
			'LocalStatus' => isset($cccdata['STATUS_LOCALIS']) ? $cccdata['STATUS_LOCALIS'] : null,
			'Ekg1Time' => null,
			'Ekg1' => isset($cccdata['EKG_do_okazaniya_pomoschi']) ? $cccdata['EKG_do_okazaniya_pomoschi'] : null,
			'Ekg2Time' => null,
			'Ekg2' => isset($cccdata['EKG_posle_okazaniya_pomoschi']) ? $cccdata['EKG_posle_okazaniya_pomoschi'] : null,
			'Diag_id' => $this->getSpr('Diag', isset($cccdata['Dict_Kod_MKB']->Kod_MKB) ? $cccdata['Dict_Kod_MKB']->Kod_MKB : null),
			'Diag_sid' => null,
			'Diag_uid' => null,
			'EfAD' => isset($cccdata['Privyichnoe_AD_D']) ? $cccdata['Privyichnoe_AD_D'] : null,
			'EfChss' => isset($cccdata['ChSS_Posle']) ? $cccdata['ChSS_Posle'] : null,
			'EfPulse' => isset($cccdata['Puls_Posle']) ? $cccdata['Puls_Posle'] : null,
			'EfTemperature' => isset($cccdata['T_Posle']) ? $cccdata['T_Posle'] : null,
			'EfChd' => isset($cccdata['ChD_Posle']) ? $cccdata['ChD_Posle'] : null,
			'EfPulsks' => isset($cccdata['Pulsoksimetriya_Posle']) ? $cccdata['Pulsoksimetriya_Posle'] : null,
			'EfGluck' => isset($cccdata['Glyukometriya_Posle']) ? $cccdata['Glyukometriya_Posle'] : null,
			'Kilo' => null,
			'CmpCloseCard_UserKilo' => isset($cccdata['Kilometrazh_vyiezda']) ? $cccdata['Kilometrazh_vyiezda'] : null,
			'HelpPlace' => isset($cccdata['Okazannaya_pomosch_na_meste']) ? $cccdata['Okazannaya_pomosch_na_meste'] : null,
			'HelpAuto' => isset($cccdata['Okazannaya_pomosch_v_avtomobile']) ? $cccdata['Okazannaya_pomosch_v_avtomobile'] : null,
			'DescText' => isset($cccdata['Primechaniya_k_statusu']) ? $cccdata['Primechaniya_k_statusu'] : null,
			'PayType_Code' => 1,
			'isSogl' => isset($cccdata['Soglasie_na_meditsinskoe_vmeshatelstvo']) ? $cccdata['Soglasie_na_meditsinskoe_vmeshatelstvo'] : null,
			'isOtkazMed' => isset($cccdata['Otkaz_ot_meditsinskogo_vmeshatelstva']) ? $cccdata['Otkaz_ot_meditsinskogo_vmeshatelstva'] : null,
			'isOtkazHosp' => isset($cccdata['Otkaz_ot_gospitalizatsii']) ? $cccdata['Otkaz_ot_gospitalizatsii'] : null,
			'isOtkazSign' => null,
			'OtkazSignWhy' => null,
			'CmpCloseCard_Topic' => null,
			'CmpCloseCard_Glaz' => null,
			'CmpCloseCard_GlazAfter' => null,
			'CmpCloseCard_Sat' => null,
			'CmpCloseCard_AfterSat' => null,
			'CmpCloseCard_IsIntestinal' => null,
			'CmpCloseCard_IsVomit' => null,
			'CmpCloseCard_IsDiuresis' => null,
			'CmpCloseCard_IsDefecation' => null,
			'CmpCloseCard_IsTrauma' => null,
			'CmpCloseCard_IsHeartNoise' => null,
			'CmpCloseCard_BegTreatDT' => null,
			'CmpCloseCard_EndTreatDT' => null,
			'CmpCloseCard_Rhythm' => null,
			'CmpCloseCard_AfterRhythm' => null,
			'CmpCloseCard_HelpDT' => null,
			'CmpCloseCard_TranspEndDT' => isset($cccdata['Pribitye_V_LPU']) ? $cccdata['Pribitye_V_LPU'] : null,
			'ComboCheck_PersonSocial_id' => $this->getSocStatus(isset($cccdata['Dict_Mesto_Raboty']->ID) ? $cccdata['Dict_Mesto_Raboty']->ID : null),
			'ComboCheck_ResultUfa_id' => $this->getCmpResult(isset($cccdata['Dict_Result_viezd']->ID) ? $cccdata['Dict_Result_viezd']->ID : null),
			'CmpCloseCard_AddInfo' => isset($cccdata['Bolnoy_drugoe']) ? $cccdata['Bolnoy_drugoe'] : null,
			'CmpCloseCard_ClinicalEff' => null,
			'ComboCheck_Result_id' => $this->getComboResult(isset($cccdata['Dict_Effekt_ot_provedennoy_terapii']->ID) ? $cccdata['Dict_Effekt_ot_provedennoy_terapii']->ID : null),
			'ComboCheck_AgeType_id' => $this->getComboAgeType(isset($cccdata['Dict_Priznak_Age']->ID) ? $cccdata['Dict_Priznak_Age']->ID : null),
			  // --- мимикрируем ---
			'action' => empty($CmpCloseCard_id) ? 'add' : 'edit',
			'ARMType' => null,
			'session' => array('groups' => ''),
			'Server_id' => 1,
			'pmUser_id' => 1
		);
				
		if (!empty($_REQUEST['getDebug'])) {
			echo "Обработанные данные для CmpCloseCard:<br>";
			echo "<textarea cols=150 rows=20>" . print_r($params, true) . "</textarea><br><br>";
		}
		
		// ошибки в лог
		if(empty($params['CallPovod_id']) && !empty($cccdata['Dict_Povod']->ID)) {
			$this->ServiceList_model->saveServiceListDetailLog(array(
				'ServiceListLog_id' => $this->ServiceListLog_id,
				'ServiceListLogType_id' => 2,
				'ServiceListDetailLog_Message' => "Повод с указанным кодом не найден в справочнике (Povod={$cccdata['Dict_Povod']->ID})",
				'pmUser_id' => 1
			));
		}
		
		if(empty($params['CmpCallType_id']) && !empty($cccdata['Dict_Vid']->Name)) {
			$this->ServiceList_model->saveServiceListDetailLog(array(
				'ServiceListLog_id' => $this->ServiceListLog_id,
				'ServiceListLogType_id' => 2,
				'ServiceListDetailLog_Message' => "Тип вызова с указанным наименованием не найден в справочнике (Vid={$cccdata['Dict_Vid']->Name})",
				'pmUser_id' => 1
			));
		}
		
		if(empty($params['Diag_id']) && !empty($cccdata['Dict_Kod_MKB']->Kod_MKB)) {
			$this->ServiceList_model->saveServiceListDetailLog(array(
				'ServiceListLog_id' => $this->ServiceListLog_id,
				'ServiceListLogType_id' => 2,
				'ServiceListDetailLog_Message' => "Диагноз с указанным кодом не найден в справочнике (Kod_MKB={$cccdata['Dict_Kod_MKB']->Kod_MKB})",
				'pmUser_id' => 1
			));
		}
		
		if(empty($params['SocStatus_id']) && !empty($cccdata['Dict_Mesto_Raboty']->Name)) {
			$this->ServiceList_model->saveServiceListDetailLog(array(
				'ServiceListLog_id' => $this->ServiceListLog_id,
				'ServiceListLogType_id' => 2,
				'ServiceListDetailLog_Message' => "Социальное положение с указанным наименованием не найдено в справочнике (Mesto_Raboty.Name={$cccdata['Dict_Mesto_Raboty']->Name})",
				'pmUser_id' => 1
			));
		}
		
		if(empty($params['CmpResult_id']) && !empty($cccdata['Dict_Result_viezd']->ID)) {
			$this->ServiceList_model->saveServiceListDetailLog(array(
				'ServiceListLog_id' => $this->ServiceListLog_id,
				'ServiceListLogType_id' => 2,
				'ServiceListDetailLog_Message' => "Результат выезда с указанным ID не найден в справочнике (Result_viezd.ID={$cccdata['Dict_Result_viezd']->ID})",
				'pmUser_id' => 1
			));
		}
		
		$result = $this->cccmodel->saveCmpCloseCard110($params);
		
		if (!empty($_REQUEST['getDebug'])) {
			echo "Результат сохранения CmpCloseCard:<br>";
			echo print_r($result, true) . "<br><br>";
		}
		
		return $result;
	}

	/**
	 * Идентификация Пациента
	 */
	function findPerson($CallCard) {
		
		if(!isset($CallCard->Pacient_Fam)) return false;
		if($CallCard->Pacient_Fam == 'Неизвестный') return false;
		
		$secname_sql = 'Person_SecName = :Person_SecName';
		if(empty($CallCard->Pacient_Patronomyc) || in_array(trim($CallCard->Pacient_Patronomyc), array('Нет', '- - -'))) {
			$secname_sql = "(Person_SecName is null or Person_SecName in ('Нет', '- - -', ''))";
		}
		
		// Вариант 1
		$result = $this->queryResult("
			select
				Person_id
			from v_PersonState (nolock) 
			where 
				Person_SurName = :Person_SurName and
				Person_FirName = :Person_FirName and
				{$secname_sql} and
				Sex_id = :Sex_id and
				Person_Birthday = :Person_Birthday and
				Polis_Num = :Polis_Num
		", array(
			'Person_SurName' => $CallCard->Pacient_Fam,
			'Person_FirName' => isset($CallCard->Pacient_Name) ? $CallCard->Pacient_Name : null,
			'Person_SecName' => isset($CallCard->Pacient_Patronomyc) ? $CallCard->Pacient_Patronomyc : null,
			'Sex_id' => isset($CallCard->Dict_Gender->ID) ? $CallCard->Dict_Gender->ID : null,
			'Person_Birthday' => isset($CallCard->Birthday) ? substr($CallCard->Birthday, 0, 23) : null,
			'Polis_Num' => isset($CallCard->number_strah) ? $CallCard->number_strah : null
		));
		
		if(count($result) > 1) return false;
		if(count($result) == 1) return $result[0]['Person_id'];
		
		// Вариант 2
		$result = $this->queryResult("
			select
				Person_id
			from v_PersonState (nolock) 
			where 
				Person_SurName = :Person_SurName and
				Person_FirName = :Person_FirName and
				{$secname_sql} and
				Sex_id = :Sex_id and
				year(Person_Birthday) = year(:Person_Birthday) and
				Polis_Num = :Polis_Num
		", array(
			'Person_SurName' => $CallCard->Pacient_Fam,
			'Person_FirName' => isset($CallCard->Pacient_Name) ? $CallCard->Pacient_Name : null,
			'Person_SecName' => isset($CallCard->Pacient_Patronomyc) ? $CallCard->Pacient_Patronomyc : null,
			'Sex_id' => isset($CallCard->Dict_Gender->ID) ? $CallCard->Dict_Gender->ID : null,
			'Person_Birthday' => isset($CallCard->Birthday) ? substr($CallCard->Birthday, 0, 23) : null,
			'Polis_Num' => isset($CallCard->number_strah) ? $CallCard->number_strah : null
		));
		
		if(count($result) > 1) return false;
		if(count($result) == 1) return $result[0]['Person_id'];
		
		// Вариант 3
		$result = $this->queryResult("
			select
				Person_id
			from v_PersonState (nolock) 
			where 
				Person_SurName = :Person_SurName and
				Person_FirName = :Person_FirName and
				{$secname_sql} and
				Sex_id = :Sex_id and
				Person_Birthday = :Person_Birthday
		", array(
			'Person_SurName' => $CallCard->Pacient_Fam,
			'Person_FirName' => isset($CallCard->Pacient_Name) ? $CallCard->Pacient_Name : null,
			'Person_SecName' => isset($CallCard->Pacient_Patronomyc) ? $CallCard->Pacient_Patronomyc : null,
			'Sex_id' => isset($CallCard->Dict_Gender->ID) ? $CallCard->Dict_Gender->ID : null,
			'Person_Birthday' => isset($CallCard->Birthday) ? substr($CallCard->Birthday, 0, 23) : null
		));
		
		if(count($result) > 1) return false;
		if(count($result) == 1) return $result[0]['Person_id'];	
		
		// Вариант 4
		$result = $this->queryResult("
			select
				Person_id
			from v_PersonState (nolock) 
			where 
				Person_SurName = :Person_SurName and
				Person_FirName = :Person_FirName and
				{$secname_sql} and
				Sex_id = :Sex_id and
				year(Person_Birthday) = year(:Person_Birthday)
		", array(
			'Person_SurName' => $CallCard->Pacient_Fam,
			'Person_FirName' => isset($CallCard->Pacient_Name) ? $CallCard->Pacient_Name : null,
			'Person_SecName' => isset($CallCard->Pacient_Patronomyc) ? $CallCard->Pacient_Patronomyc : null,
			'Sex_id' => isset($CallCard->Dict_Gender->ID) ? $CallCard->Dict_Gender->ID : null,
			'Person_Birthday' => isset($CallCard->Birthday) ? substr($CallCard->Birthday, 0, 23) : null
		));
		
		if(count($result) == 1) return $result[0]['Person_id'];		
		
		return false;
	}

	/**
	 * Идентификация Старшего бригады
	 */
	function findBrigadaHead($BrigadaHead, $Lpu_id) {

		$where = 'Person_Fio = :Person_Fio';
		if(!empty($Lpu_id)){
			$where .= ' and Lpu_id = :Lpu_id';
		}

		//Замена пробелов
		$BrigadaHead = preg_replace("/\s+/u", ' ', $BrigadaHead);

		return $this->getFirstRowFromQuery("
			declare @date datetime = cast(dbo.tzGetDate() as date);
			select top 1
				MedStaffFact_id,
				MedPersonal_id
			from v_MedStaffFact (nolock) 
			where {$where}
			and cast(WorkData_begDate as date) <= @date and (cast(WorkData_endDate as date) >= @date or WorkData_endDate is null)
		", array(
			'Person_Fio' => $BrigadaHead,
			'Lpu_id' => $Lpu_id
		));
	}

	/**
	 * Приводит дату-время в формат dd.mm.yyyy hh:mm
	 * потому что saveCmpCloseCard110 принимает только такие
	 */
	function convertDT($date) {
		if(empty($date)) return $date;
		return date('d.m.Y H:i', strtotime($date));
	}

	/**
	 * Идентификация МО/Подразделения
	 */
	function findLpuBuilding($LpuBuilding_Code, $Lpu_id = false) {

		$where = 'LpuBuilding_Code = :LpuBuilding_Code';
		if(!empty($Lpu_id)){
			$where .= ' and Lpu_id = :Lpu_id';
		}

		return $this->getFirstRowFromQuery("
			select top 1
				Lpu_id,
				LpuBuilding_id
			from v_LpuBuilding (nolock) 
			where {$where}

		", array(
			'LpuBuilding_Code' => $LpuBuilding_Code,
			'Lpu_id' => $Lpu_id
		));
	}

	/**
	 * Поиск карт
	 */
	function findCmpCallCard($CallCard, $lpu = false) {
		
		if(!$lpu) {
			$lpu = $this->findLpuBuilding($CallCard->Dict_Sub_SMP->ID);
		}
		
		if(!$lpu) return false;
		
		return $this->getFirstResultFromQuery("
			select top 1
				CmpCallCard_id
			from v_CmpCallCard (nolock) 
			where 
				CmpCallCard_Numv = :CmpCallCard_Numv and 
				CmpCallCard_Ngod = :CmpCallCard_Ngod and 
				CmpCallCard_prmDT = :CmpCallCard_prmDT and 
				Lpu_id = :Lpu_id
		", array(
			'CmpCallCard_prmDT' => date('Y-m-d H:i:00', strtotime($CallCard->Received)), // у нас секунды не сохраняются
			'Lpu_id' => $lpu['Lpu_id'],
			'CmpCallCard_Numv' => $CallCard->Number_day,
			'CmpCallCard_Ngod' => $CallCard->Number
		));
	}

	/**
	 * Загрузка карт
	 */
	function syncAll($data) {
		$this->load->model('ServiceList_model');
		$ServiceList_id = $this->ServiceList_model->getServiceListId('ImportDateSMP');
		$begDT = date('Y-m-d H:i:s');
		$resp = $this->ServiceList_model->saveServiceListLog(array(
			'ServiceListLog_id' => null,
			'ServiceList_id' => $ServiceList_id,
			'ServiceListLog_begDT' => $begDT,
			'ServiceListResult_id' => 2,
			'pmUser_id' => 1
		));
		if (!$this->isSuccessful($resp)) {
			throw new Exception($resp[0]['Error_Msg'], $resp[0]['Error_Code']);
		}
		$this->ServiceListLog_id = $resp[0]['ServiceListLog_id'];

		set_error_handler(array($this, 'exceptionErrorHandler'));
		try {
			
			$this->soapLocation = CMP_SERVICE;
			$this->soapUri = defined('CMP_SERVICE_URI') ? CMP_SERVICE_URI : CMP_SERVICE;

			// Опции soap-клиента
			$options = array(
				'soap_version' => SOAP_1_1,
				'exceptions' => true,
				'trace' => 1,
				'encoding' => 'utf-8',
				'connection_timeout' => 15,
				'location' => $this->soapLocation,
				'uri' => $this->soapUri
			);
			
			$this->soapClient = new SoapClientExt(null, $options);

			$date1 = date('Y-m-d', time() - 3600*24);
			$date2 = date('Y-m-d');  // по идее должна быть тоже прошедшая дата, но сервис считает время 0:00, поэтому текущая

			$params = array();
			$params[] = new SoapVar($date1, XSD_STRING, null, null, 'startDate' );
			$params[] = new SoapVar($date2, XSD_STRING, null, null, 'endDate' );
			$response = $this->soapClient->getCallCardsRequest(new SoapVar($params, SOAP_ENC_OBJECT, null, null, 'dateParams'));

			if (!empty($_REQUEST['getDebug'])) {
				echo "URL: {$this->soapLocation}<br>";
				echo "Запрос:<br>";
				echo "<textarea cols=150 rows=10>" . $this->soapClient->__getLastRequest() . "</textarea><br><br>";
			}

			if (empty($response) || !is_array($response) || !isset($response['CallCard'])) {
				$this->ServiceList_model->saveServiceListDetailLog(array(
					'ServiceListLog_id' => $this->ServiceListLog_id,
					'ServiceListLogType_id' => 2,
					'ServiceListDetailLog_Message' => "Пустой или некорректный ответ от сервиса",
					'pmUser_id' => 1
				));
				return false;
			}
			
			if (!empty($_REQUEST['getDebug'])) {
				echo "Количество записей в ответе от сервиса: " . count($response['CallCard']) . "<br><br>\n";
			}
			
			$this->ServiceList_model->saveServiceListDetailLog(array(
				'ServiceListLog_id' => $this->ServiceListLog_id,
				'ServiceListLogType_id' => 1,
				'ServiceListDetailLog_Message' => "Количество записей в ответе от сервиса: " . count($response['CallCard']),
				'pmUser_id' => 1
			));
			
			if ($data['limit'] > 0) {
				$response['CallCard'] = array_slice($response['CallCard'], -$data['limit'], $data['limit']); 
			}

			$this->load->model("Options_model", "opmodel");
			$globalOpts = $this->opmodel->getOptionsGlobals($data);
			$g_options = $globalOpts['globals'];

			foreach($response['CallCard'] as $CallCard) {
				// погнали
				$cccdata = (array)$CallCard;

				$default_lpu = $g_options["smp_default_lpu_import_git"];

				if (!empty($_REQUEST['getDebug'])) {
					echo "Распознанные данные:<br>";
					echo "<textarea cols=150 rows=20>" . print_r($cccdata, true) . "</textarea><br><br>";
				}

				$cccdata['Lpu'] = $this->findLpuBuilding($CallCard->Dict_Sub_SMP->ID, $default_lpu);

				if( isset($CallCard->Birthday) && substr($CallCard->Birthday, 0, 4) > 1755) {
					$cccdata['Birthday'] = substr($cccdata['Birthday'], 0, 23);
				}else {
					$cccdata['Birthday'] = null;
					$CallCard->Birthday = null;
				}

				if (!$cccdata['Lpu']) {
					$this->ServiceList_model->saveServiceListDetailLog(array(
						'ServiceListLog_id' => $this->ServiceListLog_id,
						'ServiceListLogType_id' => 2,
						'ServiceListDetailLog_Message' => "Не идентифицировано подразделение СМП (Sub_SMP={$CallCard->Dict_Sub_SMP->ID})",
						'pmUser_id' => 1
					));
				}else{
					$default_lpu = $cccdata['Lpu']['Lpu_id'];
				}
				
				$cccdata['CmpCallCard_id'] = $this->findCmpCallCard($CallCard, $cccdata['Lpu']);
				
				$cccdata['Person_id'] = $this->findPerson($CallCard);
				if (!$cccdata['Person_id']) {
					$this->ServiceList_model->saveServiceListDetailLog(array(
						'ServiceListLog_id' => $this->ServiceListLog_id,
						'ServiceListLogType_id' => 2,
						'ServiceListDetailLog_Message' => "Не идентифицирован пациент (".(isset($CallCard->Pacient_Fam) ? $CallCard->Pacient_Fam  : '')." "
							.(isset($CallCard->Pacient_Name) ? $CallCard->Pacient_Name  : '')." "
							.(isset($CallCard->Pacient_Patronomyc) ? $CallCard->Pacient_Patronomyc  : '')." "
							.(isset($CallCard->Birthday) ? substr($CallCard->Birthday, 0, 23)  : '').")",
						'pmUser_id' => 1
					));
				}
				
				if(isset($CallCard->Dict_BrigadaHead) && isset($CallCard->Dict_BrigadaHead->Name)) {
					$cccdata['BrigadaHead'] = $this->findBrigadaHead($CallCard->Dict_BrigadaHead->Name, $default_lpu);
					if (!$cccdata['BrigadaHead']) {
						$this->ServiceList_model->saveServiceListDetailLog(array(
							'ServiceListLog_id' => $this->ServiceListLog_id,
							'ServiceListLogType_id' => 2,
							'ServiceListDetailLog_Message' => "Не идентифицирован старший бригады (BrigadaHead={$CallCard->Dict_BrigadaHead->Name})",
							'pmUser_id' => 1
						));
					}
				} else {
					$cccdata['BrigadaHead'] = null;
				}

				// Сохранение услуги только для Пскова
				$usluga = false;
				if(getRegionNick() == 'pskov' && isset($cccdata['KT_Otmetki']->dictval)){

					if(is_array($cccdata['KT_Otmetki']->dictval)){
						foreach($cccdata['KT_Otmetki']->dictval as $val){
							if(in_array($val->ID, array(74,75))){
								$usluga = $this->cccmodel->getUslugaFields(array(
									'UslugaComplex_Code' => '028001', // услуга «Тромболизис»
									'acceptTime' => $this->convertDT($cccdata['Received']),
									'Lpu_id' => $cccdata['Lpu']['Lpu_id']
								));
								break;
							}
						}
					}else{
						if(in_array($cccdata['KT_Otmetki']->dictval->ID, array(74,75))){
							$usluga = $this->cccmodel->getUslugaFields(array(
								'UslugaComplex_Code' => '028001', // услуга «Тромболизис»
								'acceptTime' => $this->convertDT($cccdata['Received']),
								'Lpu_id' => $cccdata['Lpu']['Lpu_id']
							));
						}
					}

				}

				// Собственно, сохранение
				$result = $this->saveCmpCallCard($cccdata);
				if (count($result) && !empty($result[0]['CmpCallCard_id'])) {
					$cccdata['CmpCallCard_id'] = $result[0]['CmpCallCard_id'];
					$this->saveCmpCloseCard($cccdata);

					if(is_array($usluga) && isset($usluga[0])){
						$usluga_array = array();
						$usluga_array[] = array(
							'UslugaComplex_id' => $usluga[0]['UslugaComplex_id'],
							'UslugaCategory_id' => $usluga[0]['UslugaCategory_id'],
							'CmpCallCardUsluga_Kolvo' => 1,
							'CmpCallCardUsluga_setDate' => date('d.m.Y', strtotime($cccdata['Received'])),
							'CmpCallCardUsluga_setTime' => date('H:i', strtotime($cccdata['Received'])),
							'MedStaffFact_id' => $cccdata['BrigadaHead']['MedStaffFact_id'],
							'PayType_Code' => 1
						);

						$resp = $this->cccmodel->saveCmpCallCardUslugaList(array(
							'CmpCallCard_id' => $result[0]['CmpCallCard_id'],
							'pmUser_id' => $data['pmUser_id'],
							'usluga_array' => $usluga_array
						));
						if ( !$this->isSuccessful( $resp ) ) {
							$this->ServiceList_model->saveServiceListDetailLog(array(
								'ServiceListLog_id' => $this->ServiceListLog_id,
								'ServiceListLogType_id' => 2,
								'ServiceListDetailLog_Message' => "Не удалось сохранить услугу Тромболизис для карты №{$cccdata['Number_day']}: {$resp[0]['Error_Msg']}",
								'pmUser_id' => 1
							));
						}
					}
				} elseif (count($result) && !empty($result[0]['Error_Msg'])) {
					$this->ServiceList_model->saveServiceListDetailLog(array(
						'ServiceListLog_id' => $this->ServiceListLog_id,
						'ServiceListLogType_id' => 2,
						'ServiceListDetailLog_Message' => "Сохранение карты №{$cccdata['Number_day']}: {$result[0]['Error_Msg']}",
						'pmUser_id' => 1
					));
				}
			}
			
			$endDT = date('Y-m-d H:i:s');
			$resp = $this->ServiceList_model->saveServiceListLog(array(
				'ServiceListLog_id' => $this->ServiceListLog_id,
				'ServiceList_id' => $ServiceList_id,
				'ServiceListLog_begDT' => $begDT,
				'ServiceListLog_endDT' => $endDT,
				'ServiceListResult_id' => 1,
				'pmUser_id' => 1
			));
		} catch(Exception $e) {
			echo $e->getMessage();
			// Сохраняем ошибку, выходим
			$this->ServiceList_model->saveServiceListDetailLog(array(
				'ServiceListLog_id' => $this->ServiceListLog_id,
				'ServiceListLogType_id' => 2,
				'ServiceListDetailLog_Message' => $e->getMessage(),
				'pmUser_id' => 1
			));

			$endDT = date('Y-m-d H:i:s');
			$this->ServiceList_model->saveServiceListLog(array(
				'ServiceListLog_id' => $this->ServiceListLog_id,
				'ServiceList_id' => $ServiceList_id,
				'ServiceListLog_begDT' => $begDT,
				'ServiceListLog_endDT' => $endDT,
				'ServiceListResult_id' => 3,
				'pmUser_id' => 1
			));
		}
		restore_exception_handler();
	}
}