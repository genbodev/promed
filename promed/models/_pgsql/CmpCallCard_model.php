<?php
require_once("CmpCallCard_model_check.php");
require_once("CmpCallCard_model_common.php");
require_once("CmpCallCard_model_set.php");
require_once("CmpCallCard_model_delete.php");
require_once("CmpCallCard_model_print.php");
require_once("CmpCallCard_model_save.php");
require_once("CmpCallCard_model_load.php");
require_once("CmpCallCard_model_loadCmp.php");
require_once("CmpCallCard_model_loadSmp.php");
require_once("CmpCallCard_model_get.php");
/**
 * Class CmpCallCard_model
 *
 * @property CI_DB_driver $db
 * @property CI_DB_driver $dbmodel
 * @property Person_model $Person_model
 * @property EmergencyTeam_model4E $ETModel
 * @property Options_model $opmodel
 * @property HomeVisit_model $HomeVisit_model
 * @property User_model $User_$model
 * @property DocumentUc_model $du_model
 * @property CmpCallCard_model4E $CmpCallCard_model4E
 * @property CostPrint_model $CostPrint_model
 * @property Common_model $Common_model
 * @property MedicalCareBudgType_model $MedicalCareBudgType_model
 * @property DocumentUc_model DocumentUc_model
 * @property GeoserviceTransport_model $GeoserviceTransport_model
 * @property Wialon_model $Wialon_model
 */
class CmpCallCard_model extends swPgModel
{
	public $dateTimeForm104 = "DD.MM.YYYY";
	public $dateTimeForm108 = "HH24:MI:SS";
	public $dateTimeForm113 = "DD mon YYYY HH24:MI:SS:MS";
	public $dateTimeForm126 = "YYYY-MM-DDT HH24:MI:SS:MS";
	public $dateTimeForm120 = "YYYY-MM-DD HH24:MI:SS";

	public $numericForm18_2 = "FM999999999999999999.00";

	public $schema = "dbo";  //региональная схема
	public $comboSchema = "dbo";  //Казахстанский мод

	public $cmpCardFields = [
		"CmpCallCard_Numv" => "CmpCallCard_Numv",
		"CmpCallCard_Ngod" => "CmpCallCard_Ngod",
		"CmpCallCard_Prty" => "CmpCallCard_Prty",
		"CmpCallCard_Sect" => "CmpCallCard_Sect",
		"CmpArea_id" => "CmpArea_id",
		"CmpCallCard_City" => "CmpCallCard_City",
		"CmpCallCard_Ulic" => "CmpCallCard_Ulic",
		"CmpCallCard_Dom" => "CmpCallCard_Dom",
		"CmpCallCard_Kvar" => "CmpCallCard_Kvar",
		"CmpCallCard_Podz" => "CmpCallCard_Podz",
		"CmpCallCard_Etaj" => "CmpCallCard_Etaj",
		"CmpCallCard_Kodp" => "CmpCallCard_Kodp",
		"CmpCallCard_Telf" => "CmpCallCard_Telf",
		"CmpPlace_id" => "CmpPlace_id",
		"CmpCallCard_Comm" => "CmpCallCard_Comm",
		"CmpReason_id" => "CmpReason_id",
		"Person_id" => "Person_id",
		"Person_SurName" => "Person_SurName",
		"Person_FirName" => "Person_FirName",
		"Person_SecName" => "Person_SecName",
		"Person_Age" => "Person_Age",
		"Person_BirthDay" => "Person_BirthDay",
		"Person_PolisSer" => "Person_PolisSer",
		"Person_PolisNum" => "Person_PolisNum",
		"Sex_id" => "Sex_id",
		"CmpCallCard_Ktov" => "CmpCallCard_Ktov",
		"CmpCallType_id" => "CmpCallType_id",
		"CmpProfile_cid" => "CmpProfile_cid",
		"CmpCallCard_Smpt" => "CmpCallCard_Smpt",
		"CmpCallCard_Stan" => "CmpCallCard_Stan",
		"CmpCallCard_prmDT" => "CmpCallCard_prmDT",
		"CmpCallCard_Line" => "CmpCallCard_Line",
		"CmpResult_id" => "CmpResult_id",
		"CmpArea_gid" => "CmpArea_gid",
		"CmpLpu_id" => "CmpLpu_id",
		"CmpDiag_oid" => "CmpDiag_oid",
		"CmpDiag_aid" => "CmpDiag_aid",
		"CmpTrauma_id" => "CmpTrauma_id",
		"CmpCallCard_IsAlco" => "CmpCallCard_IsAlco",
		"Diag_uid" => "Diag_uid",
		"CmpCallCard_Numb" => "CmpCallCard_Numb",
		"CmpCallCard_Smpb" => "CmpCallCard_Smpb",
		"CmpCallCard_Stbr" => "CmpCallCard_Stbr",
		"CmpCallCard_Stbb" => "CmpCallCard_Stbb",
		"CmpProfile_bid" => "CmpProfile_bid",
		"CmpCallCard_Ncar" => "CmpCallCard_Ncar",
		"CmpCallCard_RCod" => "CmpCallCard_RCod",
		"CmpCallCard_TabN" => "CmpCallCard_TabN",
		"CmpCallCard_Dokt" => "CmpCallCard_Dokt",
		"CmpCallCard_Tab2" => "CmpCallCard_Tab2",
		"CmpCallCard_Tab3" => "CmpCallCard_Tab3",
		"CmpCallCard_Tab4" => "CmpCallCard_Tab4",
		"Diag_sid" => "Diag_sid",
		"CmpTalon_id" => "CmpTalon_id",
		"CmpCallCard_Expo" => "CmpCallCard_Expo",
		"CmpCallCard_Smpp" => "CmpCallCard_Smpp",
		"CmpCallCard_Vr51" => "CmpCallCard_Vr51",
		"CmpCallCard_D201" => "CmpCallCard_D201",
		"CmpCallCard_Dsp1" => "CmpCallCard_Dsp1",
		"CmpCallCard_Dsp2" => "CmpCallCard_Dsp2",
		"CmpCallCard_Dspp" => "CmpCallCard_Dspp",
		"CmpCallCard_Dsp3" => "CmpCallCard_Dsp3",
		"CmpCallCard_Kakp" => "CmpCallCard_Kakp",
		"CmpCallCard_Tper" => "CmpCallCard_Tper",
		"CmpCallCard_Vyez" => "CmpCallCard_Vyez",
		"CmpCallCard_Przd" => "CmpCallCard_Przd",
		"CmpCallCard_Tgsp" => "CmpCallCard_Tgsp",
		"CmpCallCard_Tsta" => "CmpCallCard_Tsta",
		"CmpCallCard_Tisp" => "CmpCallCard_Tisp",
		"CmpCallCard_Tvzv" => "CmpCallCard_Tvzv",
		"CmpCallCard_Kilo" => "CmpCallCard_Kilo",
		"CmpCallCard_Dlit" => "CmpCallCard_Dlit",
		"CmpCallCard_Prdl" => "CmpCallCard_Prdl",
		"CmpArea_pid" => "CmpArea_pid",
		"CmpCallCard_PCity" => "CmpCallCard_PCity",
		"CmpCallCard_PUlic" => "CmpCallCard_PUlic",
		"CmpCallCard_PDom" => "CmpCallCard_PDom",
		"CmpCallCard_PKvar" => "CmpCallCard_PKvar",
		"CmpLpu_aid" => "CmpLpu_aid",
		"CmpCallCard_IsPoli" => "CmpCallCard_IsPoli",
		"cmpCallCard_Medc" => "cmpCallCard_Medc",
		"CmpCallCard_Izv1" => "CmpCallCard_Izv1",
		"CmpCallCard_Tiz1" => "CmpCallCard_Tiz1",
		"CmpCallCard_Inf1" => "CmpCallCard_Inf1",
		"CmpCallCard_Inf2" => "CmpCallCard_Inf2",
		"CmpCallCard_Inf3" => "CmpCallCard_Inf3",
		"CmpCallCard_Inf4" => "CmpCallCard_Inf4",
		"CmpCallCard_Inf5" => "CmpCallCard_Inf5",
		"CmpCallCard_Inf6" => "CmpCallCard_Inf6",
		"pmUser_insID" => "pmUser_insID",
		"pmUser_updID" => "pmUser_updID",
		"CmpCallCard_insDT" => "CmpCallCard_insDT",
		"CmpCallCard_updDT" => "CmpCallCard_updDT",
		"KLRgn_id" => "KLRgn_id",
		"KLSubRgn_id" => "KLSubRgn_id",
		"KLCity_id" => "KLCity_id",
		"KLTown_id" => "KLTown_id",
		"KLStreet_id" => "KLStreet_id",
		"Lpu_ppdid" => "Lpu_ppdid",
		"CmpCallCard_IsEmergency" => "CmpCallCard_IsEmergency",
		"CmpCallCard_IsOpen" => "CmpCallCard_IsOpen",
		"CmpCallCardStatusType_id" => "CmpCallCardStatusType_id",
		"CmpCallCardStatus_Comment" => "CmpCallCardStatus_Comment",
		"CmpCallCard_IsReceivedInPPD" => "CmpCallCard_IsReceivedInPPD",
		"CmpPPDResult_id" => "CmpPPDResult_id",
		"EmergencyTeam_id" => "EmergencyTeam_id",
		"CmpCallCard_IsInReg" => "CmpCallCard_IsInReg",
		"Lpu_id" => "Lpu_id",
		"CmpCallCard_IsMedPersonalIdent" => "CmpCallCard_IsMedPersonalIdent",
		"MedPersonal_id" => "MedPersonal_id",
		"ResultDeseaseType_id" => "ResultDeseaseType_id",
		"CmpCallCard_firstVersion" => "CmpCallCard_firstVersion",
		"UnformalizedAddressDirectory_id" => "UnformalizedAddressDirectory_id",
		"CmpCallCard_IsPaid" => "CmpCallCard_IsPaid",
		"CmpCallCard_Korp" => "CmpCallCard_Korp",
		"CmpCallCard_Room" => "CmpCallCard_Room",
		"CmpCallCard_DiffTime" => "CmpCallCard_DiffTime",
		"UslugaComplex_id" => "UslugaComplex_id",
		"LpuBuilding_id" => "LpuBuilding_id",
		"CmpCallerType_id" => "CmpCallerType_id",
		"CmpCallPlaceType_id" => "CmpCallPlaceType_id",
		"CmpCallCard_rid" => "CmpCallCard_rid",
		"CmpCallCard_Urgency" => "CmpCallCard_Urgency",
		"CmpCallCard_BoostTime" => "CmpCallCard_BoostTime",
		"CmpSecondReason_id" => "CmpSecondReason_id",
		"CmpDiseaseAndAccidentType_id" => "CmpDiseaseAndAccidentType_id",
		"CmpCallReasonType_id" => "CmpCallReasonType_id",
		"CmpReasonNew_id" => "CmpReasonNew_id",
		"CmpCallCard_EmergencyTeamDiscardReason" => "CmpCallCard_EmergencyTeamDiscardReason",
		"CmpCallCard_IndexRep" => "CmpCallCard_IndexRep",
		"CmpCallCard_IndexRepInReg" => "CmpCallCard_IndexRepInReg",
		"CmpCallCard_IsArchive" => "CmpCallCard_IsArchive",
		"MedStaffFact_id" => "MedStaffFact_id",
		"RankinScale_id" => "RankinScale_id",
		"RankinScale_sid" => "RankinScale_sid",
		"LeaveType_id" => "LeaveType_id",
		"CmpCallCard_isShortEditVersion" => "CmpCallCard_isShortEditVersion",
		"LpuSection_id" => "LpuSection_id",
		"CmpCallCard_Recomendations" => "CmpCallCard_Recomendations",
		"CmpCallCard_Condition" => "CmpCallCard_Condition",
		"Lpu_cid" => "Lpu_cid",
		"CmpCallCard_Tend" => "CmpCallCard_Tend",
		"CmpCallCard_CallLtd" => "CmpCallCard_CallLtd",
		"CmpCallCard_CallLng" => "CmpCallCard_CallLng",
		"CmpCallCard_IsNMP" => "CmpCallCard_IsNMP",
		"CmpRejectionReason_id" => "CmpRejectionReason_id",
		"CmpCallCard_HospitalizedTime" => "CmpCallCard_HospitalizedTime",
		"CmpCallCard_saveDT" => "CmpCallCard_saveDT",
		"CmpCallCard_PlanDT" => "CmpCallCard_PlanDT",
		"CmpCallCard_FactDT" => "CmpCallCard_FactDT",
		"CmpCallCardInputType_id" => "CmpCallCardInputType_id",
		"CmpCallCard_IsExtra" => "CmpCallCard_IsExtra",
		"CmpCallCardStatus_id" => "CmpCallCardStatus_id",
		"CmpCallCard_GUID" => "CmpCallCard_GUID",
		"CmpCallCard_rGUID" => "CmpCallCard_rGUID",
		"CmpCallCard_firstVersionGUID" => "CmpCallCard_firstVersionGUID",
		"CmpCallCardStatus_GUID" => "CmpCallCardStatus_GUID",
		"EmergencyTeam_GUID" => "EmergencyTeam_GUID",
		"CmpCallCard_storDT" => "CmpCallCard_storDT",
		"CmpCallCard_defCom" => "CmpCallCard_defCom",
		"MedService_id" => "MedService_id",
		"CmpCallCard_PolisEdNum" => "CmpCallCard_PolisEdNum",
		"CmpCallCard_IsDeterior" => "CmpCallCard_IsDeterior",
		"Diag_sopid" => "Diag_sopid",
		"CmpLeaveType_id" => "CmpLeaveType_id",
		"CmpLeaveTask_id" => "CmpLeaveTask_id",
		"CmpMedicalCareKind_id" => "CmpMedicalCareKind_id",
		"CmpTransportType_id" => "CmpTransportType_id",
		"CmpResultDeseaseType_id" => "CmpResultDeseaseType_id",
		"CmpCallCardResult_id" => "CmpCallCardResult_id",
		"Person_IsUnknown" => "Person_IsUnknown",
		"CmpCallCard_IsPassSSMP" => "CmpCallCard_IsPassSSMP",
		"Lpu_smpid" => "Lpu_smpid",
		"Lpu_hid" => "Lpu_hid",
		"UnformalizedAddressDirectory_wid" => "UnformalizedAddressDirectory_wid",
		"PayType_id" => "PayType_id",
		"CmpCallCard_UlicSecond" => "CmpCallCard_UlicSecond",
		"CmpCallCard_sid" => "CmpCallCard_sid",
		"CmpCallCard_IsActiveCall" => "CmpCallCard_IsActiveCall",
		"CmpCallCard_isControlCall" => "CmpCallCard_isControlCall",
		"CmpCallCard_isTimeExceeded" => "CmpCallCard_isTimeExceeded",
		"CmpCallCard_NumvPr" => "CmpCallCard_NumvPr",
		"CmpCallCard_NgodPr" => "CmpCallCard_NgodPr",
		"CmpCallSignType_id" => "CmpCallSignType_id",
		"Lpu_CodeSMO" => "Lpu_CodeSMO",
		"Registry_sid" => "Registry_sid",
		"Diag_gid" => "Diag_gid",
		"MedicalCareBudgType_id" => "MedicalCareBudgType_id",
		"CmpCommonState_id" => "CmpCommonState_id",
		"CmpCallKind_id" => "CmpCallKind_id",
		"CmpCallCard_isViewCancelCall" => "CmpCallCard_isViewCancelCall"
	];
	function __construct()
	{
		parent::__construct();
		//установка региональной схемы
		$config = get_config();
		if ($this->regionNick == "kz") {
			$this->schema = $config["regions"][getRegionNumber()]["schema"];
		}
		// Казахстан использует схему 101 для таблицы CmpCloseCardCombo а все остальные - дбо
		// на тесте у Казахстана установлена дефолтная схема 101 (и можно подумать что этот код не нужен), НО - на рабочем дефолтная DBO
		// а сейчас пока костыль:
		if ($this->regionNick == "kz") {
			$this->comboSchema = $config["regions"][getRegionNumber()]["schema"];
		}
	}

	/**
	 * @param string $value
	 */
	public function addLog($value)
	{
		$this->textlog->add($value);
	}
	#region check
	/**
	 * Проверка и устнановка статуса карте при ее сохранении
	 * @param $data
	 * @throws Exception
	 */
	function checkCallStatusOnSave($data)
	{
		CmpCallCard_model_check::checkCallStatusOnSave($this, $data);
	}

	/**
	 * функция либо возвращает ид персон, либо создает оный при его отсутствиипри
	 * при неизвестном пациенте сохраняем неизвестного и вставляет новый ид в талон
	 * @param $data
	 * @return bool|mixed
	 */
	function checkUnknownPerson($data)
	{
		return CmpCallCard_model_check::checkUnknownPerson($this, $data);
	}

	/**
	 * @param $data
	 * @return mixed
	 */
	public function checkCmpCallCardNumber($data)
	{
		return CmpCallCard_model_check::checkCmpCallCardNumber($this, $data);
	}

	/**
	 * функция проверяет изменения по карте и регистрирует событие Корректировка вызова
	 * @param $oldCard
	 * @param $newCard
	 * @return bool
	 * @throws Exception
	 */
	public function checkChangesCmpCallCard($oldCard, $newCard)
	{
		return CmpCallCard_model_check::checkChangesCmpCallCard($this, $oldCard, $newCard);
	}

	/**
	 * Проверка оплаты диагноза по ОМС
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function checkDiagFinance($data)
	{
		return CmpCallCard_model_check::checkDiagFinance($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function checkDuplicateCmpCallCard($data)
	{
		return CmpCallCard_model_check::checkDuplicateCmpCallCard($this, $data);
	}

	/**
	 * Поиск дублей по населенному пункту, улице, дому, квартире (за последние 24 часа)
	 * @param $data
	 * @return array|bool
	 */
	function checkDuplicateCmpCallCardByAddress($data)
	{
		return CmpCallCard_model_check::checkDuplicateCmpCallCardByAddress($this, $data);
	}

	/**
	 * Поиск дублей по id пользователя (за последние 24 часа)
	 * @param $data
	 * @return array|bool
	 */
	function checkDuplicateCmpCallCardByFIO($data)
	{
		return CmpCallCard_model_check::checkDuplicateCmpCallCardByFIO($this, $data);
	}

	/**
	 * Проверка стандарта мед помощии
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function checkEmergencyStandart($data)
	{
		return CmpCallCard_model_check::checkEmergencyStandart($this, $data);
	}

	/**
	 * Проверяет заблокирована или нет карта вызова
	 * @param $data
	 * @return array|bool
	 */
	function checkLockCmpCallCard($data)
	{
		return CmpCallCard_model_check::checkLockCmpCallCard($this, $data);
	}

	/**
	 * Проверка наличия CmpCallCard у пациента
	 * @param null $Person_id
	 * @return bool|float|int|string|null
	 */
	public function checkPersonCmpCallCard($Person_id = null)
	{
		return CmpCallCard_model_check::checkPersonCmpCallCard($this, $Person_id);
	}

	/**
	 * Проверка наличия CmpCloseCard у пациента
	 * @param null $Person_id
	 * @return bool|float|int|string|null
	 */
	public function checkPersonCmpCloseCard($Person_id = null)
	{
		return CmpCallCard_model_check::checkPersonCmpCloseCard($this, $Person_id);
	}

	/**
	 * Проверка вызова на связь с карточкой 112
	 * @param $data
	 * @return array|bool
	 */
	public function checkRelated112Call($data)
	{
		return CmpCallCard_model_check::checkRelated112Call($this, $data);
	}

	/**
	 * Проверка уникальности правила.
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function checkRuleUniqueness($data)
	{
		return CmpCallCard_model_check::checkRuleUniqueness($this, $data);
	}

	/**
	 * Отправка реагирования в зависимости от события вызова
	 * @param $data
	 * @return bool
	 */
	public function checkSendReactionToActiveMQ($data)
	{
		return CmpCallCard_model_check::checkSendReactionToActiveMQ($this, $data);
	}
	#endregion check
	#region common
	/**
	 * Удаление диагноза
	 * @param $action
	 * @param $params
	 * @return bool
	 */
	function actionCmpCloseCardDiag($action, $params)
	{
		return CmpCallCard_model_common::actionCmpCloseCardDiag($this, $action, $params);
	}

	/**
	 * Сохранение актива СМП (создание вызова на дом из АРМ-а администратора СМП)
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function addHomeVisitFromSMP($data)
	{
		return CmpCallCard_model_common::addHomeVisitFromSMP($this, $data);
	}

	/**
	 * Автоматическое создание записей о пациентах СМП, которые не были созданы из-за отсутсвия связи с основным сервером.
	 * Должен запускаться по заданию на сервере СМП.
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public function autoCreateCmpPerson($data)
	{
		return CmpCallCard_model_common::autoCreateCmpPerson($this, $data);
	}

	/**
	 * @return array|bool
	 */
	function clearCmpCallCardList()
	{
		return CmpCallCard_model_common::clearCmpCallCardList($this);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function defineAccessoryGroupCmpCallCard($data)
	{
		return CmpCallCard_model_common::defineAccessoryGroupCmpCallCard($this, $data);
	}

	/**
	 * проверка на существование номера вызовов за год и за день на определенную дату
	 * возвращает existenceNumbersYear, existenceNumbersDay: 1 - есть занчение, 0 - отсутствует
	 * nextNumberDay, nextNumberYear: следующие значения номера вызова
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function existenceNumbersDayYear($data)
	{
		return CmpCallCard_model_common::existenceNumbersDayYear($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function findCmpIllegalAct($data)
	{
		return CmpCallCard_model_common::findCmpIllegalAct($this, $data);
	}

	/**
	 * Вспомогательная функция преобразования формата даты
	 * Получает строку c датой в формате d.m.Y, возвращает строку с датой в формате Y-m-d
	 * @param $date
	 * @return string|null
	 */
	function formatDate($date)
	{
		return CmpCallCard_model_common::formatDate($date);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function identifiPerson($data)
	{
		return CmpCallCard_model_common::identifiPerson($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function importSMPCardsTest($data)
	{
		return CmpCallCard_model_common::importSMPCardsTest($this, $data);
	}

	/**
	 * Инициализация дефолтной логиги предложения бригад на вызов и назначения срочности вызова
	 * @param $data
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	public function initiateProposalLogicForLpu($data)
	{
		return CmpCallCard_model_common::initiateProposalLogicForLpu($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function lockCmpCallCard($data)
	{
		return CmpCallCard_model_common::lockCmpCallCard($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function unlockCmpCallCard($data)
	{
		return CmpCallCard_model_common::unlockCmpCallCard($this, $data);
	}

	/**
	 * Вспомогательная функция для преобразование полной даты записсаной в виде строки в дату человекоподобную
	 * @param $str
	 * @return string
	 */
	function peopleDate($str)
	{
		return CmpCallCard_model_common::peopleDate($str);
	}

	/**
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	function RefuseOnTimeout($data)
	{
		return CmpCallCard_model_common::RefuseOnTimeout($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function removeSmpFarmacyDrug($data)
	{
		return CmpCallCard_model_common::removeSmpFarmacyDrug($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function reportBrig($data)
	{
		return CmpCallCard_model_common::reportBrig($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function reportDayDiag($data)
	{
		return CmpCallCard_model_common::reportDayDiag($this, $data);
	}

	/**
	 * Автоматическое создание записей о пациентах СМП, которые не были созданы из-за отсутсвия связи с основным сервером.
	 * Должен запускаться по заданию на сервере СМП.
	 * @param $data
	 */
	function sendCmpCallCardToActiveMQ($data)
	{
		CmpCallCard_model_common::sendCmpCallCardToActiveMQ($data);
	}

	/**
	 * Автоматическое создание записей о пациентах СМП, которые не были созданы из-за отсутсвия связи с основным сервером.
	 * Должен запускаться по заданию на сервере СМП.
	 * @param $data
	 */
	function sendCmpCloseCardToActiveMQ($data)
	{
		CmpCallCard_model_common::sendCmpCloseCardToActiveMQ($data);
	}

	/**
	 * Автоматическое создание записей о пациентах СМП, которые не были созданы из-за отсутсвия связи с основным сервером.
	 * Должен запускаться по заданию на сервере СМП.
	 * @param $data
	 */
	function sendStatusCmpCallCardToActiveMQ($data)
	{
		CmpCallCard_model_common::sendStatusCmpCallCardToActiveMQ($data);
	}

	/**
	 * Автоматическое создание записей о пациентах СМП, которые не были созданы из-за отсутсвия связи с основным сервером.
	 * Должен запускаться по заданию на сервере СМП.
	 * @param $data
	 */
	function sendLpuTransmitToActiveMQ($data)
	{
		CmpCallCard_model_common::sendLpuTransmitToActiveMQ($data);
	}

	/**
	 * Отправка пуша при назначении бригады на вызов
	 * @param $data
	 */
	function sendPushOnSetMergencyTeam($data)
	{
		CmpCallCard_model_common::sendPushOnSetMergencyTeam($this, $data);
	}

	/**
	 * Передача сообщений в зависимости от параметра reactionType:
	 * @param $data
	 * @param $reactionType
	 * @return bool
	 */
	function sendReactionToActiveMQ($data, $reactionType)
	{
		return CmpCallCard_model_common::sendReactionToActiveMQ($this, $data, $reactionType);
	}

	/**
	 * для тестов отправки запросов в ActiveMQ
	 */
	function testAM()
	{
		CmpCallCard_model_common::testAM();
	}

	/**
	 * Снятие статус "отказ" карты вызова
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function unrefuseCmpCallCard($data)
	{
		return CmpCallCard_model_common::unrefuseCmpCallCard($this, $data);
	}

	/**
	 * Обновление параметров CmpCallCard при закрычии 110у
	 * @param $data
	 * @return bool
	 */
	function updateCmpCallCardByClose($data)
	{
		return CmpCallCard_model_common::updateCmpCallCardByClose($this, $data);
	}

	/**
	 * Проверка финансируемости диагноза по ОМС для СМП
	 * @param $data
	 * @return bool
	 */
	public function validDiagFinance($data)
	{
		return CmpCallCard_model_common::validDiagFinance($this, $data);
	}

	/**
	 * Создание структуры дерева
	 */
	public function createDecigionTree($data){
       return CmpCallCard_model_common::createDecigionTree($this, $data);
	}

	/**
     * Копирование структуры дерева
     */
	public function copyDecigionTree($data){
        return CmpCallCard_model_common::copyDecigionTree($this, $data);
	}	
	
	#endregion common
	#region set
	/**
	 * Смена пациента в карте вызова
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function setAnotherPersonForCmpCallCard($data)
	{
		return CmpCallCard_model_set::setAnotherPersonForCmpCallCard($this, $data);
	}

	/**
	 * Привязка забронированной экстренной койки к карте вызова
	 * @param $data
	 * @return array|bool
	 */
	function setCmpCloseCardTimetable($data)
	{
		return CmpCallCard_model_set::setCmpCloseCardTimetable($this, $data);
	}

	/**
	 * Запись события карты в журнал. Обработка данных. Выявление статуса
	 * @param $data
	 * @return bool|mixed
	 */
	public function setCmpCallCardEvent($data)
	{
		return CmpCallCard_model_set::setCmpCallCardEvent($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function setEmergencyTeam($data)
	{
		return CmpCallCard_model_set::setEmergencyTeam($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function setLpuTransmit($data)
	{
		return CmpCallCard_model_set::setLpuTransmit($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function setLpuId($data)
	{
		return CmpCallCard_model_set::setLpuId($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function setPerson($data)
	{
		return CmpCallCard_model_set::setPerson($this, $data);
	}

	/**
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function setPPDWaitingTime($data)
	{
		return CmpCallCard_model_set::setPPDWaitingTime($this, $data);
	}

	/**
	 * Изменение статуса бригады СМП
	 * @param $data
	 * @param string $status
	 * @return bool
	 */
	function setEmergencyTeamStatus($data, $status = "Свободна")
	{
		return CmpCallCard_model_set::setEmergencyTeamStatus($this, $data, $status);
	}

	/**
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function setIsOpenCmpCallCard($data)
	{
		return CmpCallCard_model_set::setIsOpenCmpCallCard($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function setResult($data)
	{
		return CmpCallCard_model_set::setResult($this, $data);
	}

	/**
	 * Добавление списка номеров карт(110/у), которых запросил СМО
	 * @param $data
	 * @return array|false
	 */
	function setSmoQueryCallCards($data)
	{
		return CmpCallCard_model_set::setSmoQueryCallCards($this, $data);
	}

	/**
	 * Установка статуса карты вызова
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function setStatusCmpCallCard($data)
	{
		return CmpCallCard_model_set::setStatusCmpCallCard($this, $data);
	}
	#endregion set
	#region delete
	/**
	 * Удаление экспертной оценки
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function delCmpCloseCardExpertResponse($data)
	{
		return CmpCallCard_model_delete::delCmpCloseCardExpertResponse($this, $data);
	}

	/**
	 * Удаление информации о использовании медикаментов CМП
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function deleteCmpCallCardDrug($data)
	{
		return CmpCallCard_model_delete::deleteCmpCallCardDrug($this, $data);
	}

	/**
	 * Удаление информации о использовании медикаментов (простой учет)
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function deleteCmpCallCardEvnDrug($data)
	{
		return CmpCallCard_model_delete::deleteCmpCallCardEvnDrug($this, $data);
	}

	/**
	 * Удаление карты вызова
	 * @param array $data
	 * @param bool $ignoreRegistryCheck
	 * @param bool $delCallCard
	 * @return array|bool
	 * @throws Exception
	 */
	function deleteCmpCallCard($data = [], $ignoreRegistryCheck = false, $delCallCard = true)
	{
		return CmpCallCard_model_delete::deleteCmpCallCard($this, $data, $ignoreRegistryCheck, $delCallCard);
	}

	/**
	 * Удаление записи о статусе карты
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function deleteCmpCallCardStatus($data)
	{
		return CmpCallCard_model_delete::deleteCmpCallCardStatus($this, $data);
	}

	/**
	 * Удаление услуги, прикреплённой к карте вызова
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	public function deleteCmpCallCardUsluga($data)
	{
		return CmpCallCard_model_delete::deleteCmpCallCardUsluga($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function deleteUnformalizedAddress($data)
	{
		return CmpCallCard_model_delete::deleteUnformalizedAddress($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function deleteCmpIllegalAct($data)
	{
		return CmpCallCard_model_delete::deleteCmpIllegalAct($this, $data);
	}

	/**
	 * Удаление ноды дерева решений
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function deleteDecigionTreeNode($data)
	{
		return CmpCallCard_model_delete::deleteDecigionTreeNode($this, $data);
	}

	/**
	 * Удаление правила логики предложения бригады на вызов
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function deleteCmpUrgencyAndProfileStandartRule($data)
	{
		return CmpCallCard_model_delete::deleteCmpUrgencyAndProfileStandartRule($this, $data);
	}

	/**
	 * Удаление произвольного обьекта.
	 * @param string $object_name
	 * @param array $data
	 * @return array|bool
	 * @throws Exception
	 */
	function deleteObject($object_name, $data)
	{
		return CmpCallCard_model_delete::deleteObject($this, $object_name, $data);
	}

	/**
	 * Удаление пустых докуиментов учета (только со статусом 'Новый')
	 * @param $doc_id
	 * @return array
	 * @throws Exception
	 */
	function deleteEmptyDocumentUc($doc_id)
	{
		return CmpCallCard_model_delete::deleteEmptyDocumentUc($this, $doc_id);
	}

	/**
	 * Удаляем прошлый запрос карт от СМО
	 * @param $data
	 * @return array|false
	 */
	function delSmoQueryCallCards($data)
	{
		return CmpCallCard_model_delete::delSmoQueryCallCards($this, $data);
	}
	#endregion delete
	#region print
	/**
	 * Печать справки СМП
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	function printCmpCall($data)
	{
		return CmpCallCard_model_print::printCmpCall($this, $data);
	}

	/**
	 * Возвращает данные для печати карты закрытия вызова 110у
	 * @param $data
	 * @return array|bool
	 */
	public function printCmpCloseCard110($data)
	{
		return CmpCallCard_model_print::printCmpCloseCard110($this, $data);
	}

	/**
	 * для ЭМК
	 * @param $data
	 * @return array|bool
	 */
	function printCmpCloseCardEMK($data)
	{
		return CmpCallCard_model_print::printCmpCloseCardEMK($this, $data);
	}

	/**
	 * для ЭМК карты вызова
	 * @param $data
	 * @return array|bool
	 */
	function printCmpCallCardEMK($data)
	{
		return CmpCallCard_model_print::printCmpCallCardEMK($this, $data);
	}

	/**
	 * печать шапки
	 * @param $data
	 * @return bool
	 */
	function printCmpCallCardHeader($data)
	{
		return CmpCallCard_model_print::printCmpCallCardHeader($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function printCmpCallCard($data)
	{
		return CmpCallCard_model_print::printCmpCallCard($this, $data);
	}

	/**
	 * Суточный рапорт
	 * @param $data
	 * @return array
	 */
	function printReportCmp($data)
	{
		return CmpCallCard_model_print::printReportCmp($this, $data);
	}
	#endregion print
	#region save
	/**
	 * @param $data
	 * @param null $cccConfig
	 * @return array|bool
	 * @throws Exception
	 */
	function saveCmpCallCard($data, $cccConfig = null)
	{
		return CmpCallCard_model_save::saveCmpCallCard($this, $data, $cccConfig);
	}

	/**
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function saveUnformalizedAddress($data)
	{
		return CmpCallCard_model_save::saveUnformalizedAddress($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function saveCmpIllegalActForm($data)
	{
		return CmpCallCard_model_save::saveCmpIllegalActForm($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function saveSmpFarmacyDrug($data)
	{
		return CmpCallCard_model_save::saveSmpFarmacyDrug($this, $data);
	}

	/**
	 * Поточный ввод талонов вызова
	 * @param $data
	 * @param null $cccConfig
	 * @return array|bool
	 * @throws Exception
	 */
	public function saveCmpStreamCard($data, $cccConfig = null)
	{
		return CmpCallCard_model_save::saveCmpStreamCard($this, $data, $cccConfig);
	}

	/**
	 * Сохранение формы 110у
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function saveCmpCloseCard110($data)
	{
		return CmpCallCard_model_save::saveCmpCloseCard110($this, $data);
	}

	/**
	 * сохранение person_id в CmpCallCard
	 * @param $data
	 * @return bool
	 */
	function savePersonToCmpCallCard($data)
	{
		return CmpCallCard_model_save::savePersonToCmpCallCard($this, $data);
	}

	/**
	 * Сохранение значений комбо (чеки, радио и инпуты) 110
	 * @param $data
	 * @param $action
	 * @param null $oldresult
	 * @param $resArray
	 * @param null $NewCmpCloseCard_id
	 * @param $UnicNums
	 * @param $relProcedure
	 * @return mixed
	 * @throws Exception
	 */
	function saveCmpCloseCardComboValues($data, $action, $oldresult, $resArray, $NewCmpCloseCard_id, $UnicNums, $relProcedure)
	{
		return CmpCallCard_model_save::saveCmpCloseCardComboValues($this, $data, $action, $oldresult, $resArray, $NewCmpCloseCard_id, $UnicNums, $relProcedure);
	}

	/**
	 * Функция для сохранения разных типов компонентов
	 * @param $Fields
	 * @param $UnicNums
	 * @param $relProcedure
	 * @param $queryRelParams
	 * @param null $relResult
	 * @throws Exception
	 */
	function saveOtherFields($Fields, $UnicNums, $relProcedure, $queryRelParams, $relResult = null)
	{
		CmpCallCard_model_save::saveOtherFields($this, $Fields, $UnicNums, $relProcedure, $queryRelParams, $relResult);
	}

	/**
	 * Сохранение списка использованного оборудования
	 * @param $data
	 * @return bool
	 */
	public function saveCmpCloseCardEquipmentRel($data)
	{
		return CmpCallCard_model_save::saveCmpCloseCardEquipmentRel($this, $data);
	}

	/**
	 * Сохранение связи документа списания медикаментов на пациента и талона закрытия вызова
	 * @param $data
	 * @return bool
	 */
	public function saveCmpCloseCardDocumentUcRel($data)
	{
		return CmpCallCard_model_save::saveCmpCloseCardDocumentUcRel($this, $data);
	}

	/**
	 * @deprecated
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function saveCmpCallCloseCard($data)
	{
		return CmpCallCard_model_save::saveCmpCallCloseCard($this, $data);
	}

	/**
	 * Запрос на запись события карты
	 * @param $qparams
	 * @return CI_DB_result
	 */
	function saveCmpCallCardEvent($qparams)
	{
		return CmpCallCard_model_save::saveCmpCallCardEvent($this, $qparams);
	}

	/**
	 * Сохранение пуш-уведомления в историю
	 * @param $data
	 * @return array|false
	 */
	function saveCmpCallCardMessage($data)
	{
		return CmpCallCard_model_save::saveCmpCallCardMessage($this, $data);
	}

	/**
	 * Сохранение дерева решений
	 * @param $data
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	public function saveDecigionTree($data)
	{
		return CmpCallCard_model_save::saveDecigionTree($this, $data);
	}

	/**
	 * Сохранение ноды дерева решений
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	public function saveDecigionTreeNode($data)
	{
		return CmpCallCard_model_save::saveDecigionTreeNode($this, $data);
	}

	/**
	 * Сохранение правила предложения бригады на вызов, и срочность вызова в соответствии с указанными местами вызова
	 * @param $data
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	public function saveCmpUrgencyAndProfileStandartRule($data)
	{
		return CmpCallCard_model_save::saveCmpUrgencyAndProfileStandartRule($this, $data);
	}

	/**
	 * Сохранение услуги в карте вызова СМП
	 * @param $data
	 * @return array|bool|false
	 * @throws Exception
	 */
	function saveCmpCallCardUsluga($data)
	{
		return CmpCallCard_model_save::saveCmpCallCardUsluga($this, $data);
	}

	/**
	 * Метод сохранения списка услуг для карты вызова
	 * @param $data
	 * @return array|bool|false
	 * @throws Exception
	 */
	public function saveCmpCallCardUslugaList($data)
	{
		return CmpCallCard_model_save::saveCmpCallCardUslugaList($this, $data);
	}

	/**
	 * Сохранение произвольного обьекта (без повреждения предыдущих данных).
	 * @param string $object_name
	 * @param array $data
	 * @return array|bool
	 * @throws Exception
	 */
	function saveObject($object_name, $data)
	{
		return CmpCallCard_model_save::saveObject($this, $object_name, $data);
	}

	/**
	 * Сохранение спецификации из JSON
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function saveCmpCallCardDrugFromJSON($data)
	{
		return CmpCallCard_model_save::saveCmpCallCardDrugFromJSON($this, $data);
	}

	/**
	 * Сохранение спецификации из JSON
	 * @param $record
	 * @param $data
	 * @return mixed
	 * @throws Exception
	 */
	function saveCmpCallCardEvnOneDrugFromJSON($record, $data)
	{
		return CmpCallCard_model_save::saveCmpCallCardEvnOneDrugFromJSON($this, $record, $data);
	}

	/**
	 * Сохранение спецификации из JSON
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function saveCmpCallCardEvnDrugFromJSON($data)
	{
		return CmpCallCard_model_save::saveCmpCallCardEvnDrugFromJSON($this, $data);
	}

	/**
	 * Сохранение экспертных оценок карты закрытия вызова 110у
	 * @param $data
	 * @return mixed
	 * @throws Exception
	 */
	public function saveCmpCloseCardExpertResponseList($data)
	{
		return CmpCallCard_model_save::saveCmpCloseCardExpertResponseList($this, $data);
	}

	/**
	 * Сохранение экспертной оценки
	 * @param $data
	 * @return mixed
	 */
	function saveCmpCloseCardExpertResponse($data)
	{
		return CmpCallCard_model_save::saveCmpCloseCardExpertResponse($this, $data);
	}

	/**
	 * Сохранение спецификации из JSON
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function saveCmpCallCardSimpleDrugFromJSON($data)
	{
		return CmpCallCard_model_save::saveCmpCallCardSimpleDrugFromJSON($this, $data);
	}

	/**
	 * Сохранение спецификации из JSON
	 * @param $record
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	function saveCmpCallCardSimpleOneDrugFromJSON($record, $data)
	{
		return CmpCallCard_model_save::saveCmpCallCardSimpleOneDrugFromJSON($this, $record, $data);
	}

	/**
	 * Сохранение множественных диагнозов
	 * @param $data
	 * @return array|bool
	 */
	function saveCmpCallCardDiagArr($data)
	{
		return CmpCallCard_model_save::saveCmpCallCardDiagArr($this, $data);
	}

	/**
	 * Расчет и сохранение типа мед помощи
	 * @param $data
	 * @return bool
	 */
	function saveMedicalCareBudgTypeToCmpCallCard($data)
	{
		return CmpCallCard_model_save::saveMedicalCareBudgTypeToCmpCallCard($this, $data);
	}
	#endregion save
	#region load
	/**
	 * Получение списка подстанций СМП
	 * @param $data
	 * @return array|bool
	 */
	public function loadCmpCallCardAcceptorList($data)
	{
		return CmpCallCard_model_loadCmp::loadCmpCallCardAcceptorList($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadCmpCallCardJournalGrid($data)
	{
		return CmpCallCard_model_loadCmp::loadCmpCallCardJournalGrid($this, $data);
	}

	/**
	 * Возвращает данные талона вызова для карты закрытия вызова
	 * Используется для первичного наполнения карты закрытия вызова
	 * @param $data
	 * @return array|bool
	 */
	public function loadCmpCloseCardEditForm($data)
	{
		return CmpCallCard_model_loadCmp::loadCmpCloseCardEditForm($this, $data);
	}

	/**
	 * Возвращает данные карты закрытия вызова
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function loadCmpCloseCardViewForm($data)
	{
		return CmpCallCard_model_loadCmp::loadCmpCloseCardViewForm($this, $data);
	}
	
	/**
	 * Возвращает данные карты закрытия вызова
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function loadCmpCloseCardViewFormForDelDocs($data)
	{
		return CmpCallCard_model_loadCmp::loadCmpCloseCardViewFormForDelDocs($this, $data);
	}
	
	/**
	 * Возвращает карту вызова на редактирование
	 * @param $data
	 * @return array|bool
	 */
	public function loadCmpCallCardEditForm($data)
	{
		return CmpCallCard_model_loadCmp::loadCmpCallCardEditForm($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadCmpStation($data)
	{
		return CmpCallCard_model_loadCmp::loadCmpStation($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadSMPWorkPlace($data)
	{
		return CmpCallCard_model_loadSmp::loadSMPWorkPlace($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadSMPDispatchCallWorkPlace($data)
	{
		return CmpCallCard_model_loadSmp::loadSMPDispatchCallWorkPlace($this, $data);
	}

	/**
	 * Возвращает список вызовов для грида администратора СМП
	 * @param $data
	 * @return array|bool
	 */
	public function loadSMPAdminWorkPlace($data)
	{
		return CmpCallCard_model_loadSmp::loadSMPAdminWorkPlace($this, $data);
	}

	/**
	 * Диспетчер направлений
	 * @param $data
	 * @return array|bool
	 */
	function loadSMPDispatchDirectWorkPlace($data)
	{
		return CmpCallCard_model_loadSmp::loadSMPDispatchDirectWorkPlace($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadSmpFarmacyRegisterHistory($data)
	{
		return CmpCallCard_model_loadSmp::loadSmpFarmacyRegisterHistory($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadSmpFarmacyRegister($data)
	{
		return CmpCallCard_model_loadSmp::loadSmpFarmacyRegister($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadUnformalizedAddressDirectory($data)
	{
		return CmpCallCard_model_load::loadUnformalizedAddressDirectory($this, $data);
	}

	/**
	 * Загрузка комбинированного справочника улиц и неформализованных адресов СМП
	 * @param $data
	 * @return array|bool
	 */
	function loadStreetsAndUnformalizedAddressDirectoryCombo($data)
	{
		return CmpCallCard_model_load::loadStreetsAndUnformalizedAddressDirectoryCombo($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadCmpIllegalActList($data)
	{
		return CmpCallCard_model_loadCmp::loadCmpIllegalActList($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadCmpIllegalActForm($data)
	{
		return CmpCallCard_model_loadCmp::loadCmpIllegalActForm($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadSmpStacDiffDiagJournal($data)
	{
		return CmpCallCard_model_loadSmp::loadSmpStacDiffDiagJournal($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadSMPHeadDutyWorkPlace($data)
	{
		return CmpCallCard_model_loadSmp::loadSMPHeadDutyWorkPlace($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadSMPHeadBrigWorkPlace($data)
	{
		return CmpCallCard_model_loadSmp::loadSMPHeadBrigWorkPlace($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadPPDWorkPlace($data)
	{
		return CmpCallCard_model_load::loadPPDWorkPlace($this, $data);
	}

	/**
	 * Получение оперативной обстановки по ЛПУ со службой ППД
	 * @param $data
	 * @return array|bool
	 */
	function loadLpuOperEnv($data)
	{
		return CmpCallCard_model_load::loadLpuOperEnv($this, $data);
	}

	/**
	 * Загрука комбобокса случаев противоправных действий
	 * @param $data
	 * @return array|bool
	 */
	public function loadIllegalActCmpCards($data)
	{
		return CmpCallCard_model_load::loadIllegalActCmpCards($this, $data);
	}

	/**
	 * Возвращает использованное оборудование для указанной карты закрытия вызова
	 * @param $data
	 * @return array|bool
	 */
	public function loadCmpCloseCardEquipmentViewForm($data)
	{
		return CmpCallCard_model_loadCmp::loadCmpCloseCardEquipmentViewForm($this, $data);
	}

	/**
	 * Возвращает использованное оборудование для указанной карты закрытия вызова
	 * @param $data
	 * @return array|bool
	 */
	public function loadCmpCloseCardEquipmentPrintForm($data)
	{
		return CmpCallCard_model_loadCmp::loadCmpCloseCardEquipmentPrintForm($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadCmpCloseCardComboboxesViewForm($data)
	{
		return CmpCallCard_model_loadCmp::loadCmpCloseCardComboboxesViewForm($this, $data);
	}

	/**
	 * Получение списка подстанций СМП
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function loadSmpUnits($data)
	{
		return CmpCallCard_model_loadSmp::loadSmpUnits($this, $data);
	}

	/**
	 * Получение списка отделений
	 * @param $data
	 * @return array|bool
	 */
	public function loadLpuCmpUnits($data)
	{
		return CmpCallCard_model_load::loadLpuCmpUnits($this, $data);
	}

	/**
	 * Получение списка услуг в карте вызова СМП
	 * @param $data
	 * @return array|false
	 */
	function loadCmpCallCardUslugaGrid($data)
	{
		return CmpCallCard_model_loadCmp::loadCmpCallCardUslugaGrid($this, $data);
	}

	/**
	 * Получение данных для формы редактирования услуги в карте выз
	 * @param $data
	 * @return array|false
	 */
	function loadCmpCallCardUslugaForm($data)
	{
		return CmpCallCard_model_loadCmp::loadCmpCallCardUslugaForm($this, $data);
	}

	/**
	 * Загрузка комбинированного справочника улиц и неформализованных адресов СМП
	 * @param $data
	 * @return array|bool
	 */
	public function loadCmpEquipmentCombo($data)
	{
		return CmpCallCard_model_loadCmp::loadCmpEquipmentCombo($this, $data);
	}

	/**
	 * Получение списка МО с обслуживанием на дому
	 * @param $data
	 * @return array|bool
	 */
	public function loadLpuHomeVisit($data)
	{
		return CmpCallCard_model_load::loadLpuHomeVisit($this, $data);
	}

	/**
	 * Получение информации о использовании медикаментов CМП
	 * @param $data
	 * @return array|false
	 */
	function loadCmpCallCardDrugList($data)
	{
		return CmpCallCard_model_loadCmp::loadCmpCallCardDrugList($this, $data);
	}

	/**
	 * Получение информации о использовании медикаментов CМП (простой учет)
	 * @param $data
	 * @return array|false
	 */
	function loadCmpCallCardEvnDrugList($data)
	{
		return CmpCallCard_model_loadCmp::loadCmpCallCardEvnDrugList($this, $data);
	}

	/**
	 * Загрузка списка для комбобокса
	 * @param $data
	 * @return array|bool
	 */
	function loadMedStaffFactCombo($data)
	{
		return CmpCallCard_model_load::loadMedStaffFactCombo($this, $data);
	}

	/**
	 * Загрузка списка для комбобокса
	 * @param $data
	 * @return array|bool
	 */
	function loadLpuBuildingCombo($data)
	{
		return CmpCallCard_model_load::loadLpuBuildingCombo($this, $data);
	}

	/**
	 * Загрузка списка для комбобокса
	 * @param $data
	 * @return array|bool
	 */
	function loadStorageCombo($data)
	{
		return CmpCallCard_model_load::loadStorageCombo($this, $data);
	}

	/**
	 * Загрузка списка для комбобокса
	 * @param $data
	 * @return array|bool
	 */
	function loadMolCombo($data)
	{
		return CmpCallCard_model_load::loadMolCombo($this, $data);
	}

	/**
	 * Загрузка списка для комбобокса
	 * @param $data
	 * @return array|bool
	 */
	function loadStorageZoneCombo($data)
	{
		return CmpCallCard_model_load::loadStorageZoneCombo($this, $data);
	}

	/**
	 * Загрузка списка для комбобокса
	 * @param $data
	 * @return array|bool
	 */
	function loadDrugPrepFasCombo($data)
	{
		return CmpCallCard_model_load::loadDrugPrepFasCombo($this, $data);
	}

	/**
	 * Загрузка списка для комбобокса
	 * @param $data
	 * @return array|bool
	 */
	function loadDrugCombo($data)
	{
		return CmpCallCard_model_load::loadDrugCombo($this, $data);
	}

	/**
	 * Загрузка списка для комбобокса
	 * @param $data
	 * @return array|bool
	 */
	function loadDocumentUcStrOidCombo($data)
	{
		return CmpCallCard_model_load::loadDocumentUcStrOidCombo($this, $data);
	}

	/**
	 * Загрузка списка для комбобокса
	 * @param $data
	 * @return array|bool
	 */
	function loadGoodsUnitCombo($data)
	{
		return CmpCallCard_model_load::loadGoodsUnitCombo($this, $data);
	}

	/**
	 * Загрузка списка талонов вызова СМП
	 * @param $data
	 * @return array|bool|false
	 */
	function loadCmpCallCardList($data)
	{
		return CmpCallCard_model_loadCmp::loadCmpCallCardList($this, $data);
	}

	/**
	 * Получение всех подстанций СМП региона
	 * @param $data
	 * @return array|bool
	 */
	public function loadRegionSmpUnits($data)
	{
		return CmpCallCard_model_load::loadRegionSmpUnits($this, $data);
	}

	/**
	 * Получение информации о использовании медикаментов CМП (простой учет)
	 * @param $data
	 * @return array|false
	 */
	function loadCmpCallCardSimpleDrugList($data)
	{
		return CmpCallCard_model_loadCmp::loadCmpCallCardSimpleDrugList($this, $data);
	}
	#endregion load
	#region get
	/**
	 * @param $data
	 * @return array|bool
	 */
	function getCmpCallCardSmpInfo($data)
	{
		return CmpCallCard_model_get::getCmpCallCardSmpInfo($this, $data);
	}

	/**
	 * Тестовый эксперимент по получению параметров для инсерта в sql запрос
	 * @param $inputProcedure
	 * @param $params
	 * @param null $exceptedFields
	 * @param bool $isPostgresql
	 * @return array
	 */
	public function getParamsForSQLQuery($inputProcedure, $params, $exceptedFields = null, $isPostgresql = false)
	{
		return CmpCallCard_model_get::getParamsForSQLQuery($this, $inputProcedure, $params, $exceptedFields, $isPostgresql);
	}

	/**
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function getCmpCallCardNumber($data)
	{
		return CmpCallCard_model_get::getCmpCallCardNumber($this, $data);
	}

	/**
	 * Возвращает параметры начала и окончания дня/года из настроек
	 * @param $data
	 * @return mixed
	 * @throws Exception
	 */
	function getDatesToNumbersDayYear($data)
	{
		return CmpCallCard_model_get::getDatesToNumbersDayYear($this, $data);
	}

	/**
	 * @return array|bool
	 */
	function getResults()
	{
		return CmpCallCard_model_get::getResults($this);
	}

	/**
	 * @return array|bool
	 */
	function getRejectPPDReasons()
	{
		return CmpCallCard_model_get::getRejectPPDReasons($this);
	}

	/**
	 * @return array|bool
	 */
	function getMoveFromNmpReasons()
	{
		return CmpCallCard_model_get::getMoveFromNmpReasons($this);
	}

	/**
	 * @return array|bool
	 */
	function getReturnToSmpReasons()
	{
		return CmpCallCard_model_get::getReturnToSmpReasons($this);
	}

	/**
	 * Загрузка справочника, формирование чекбоксов
	 */
	function getCombo(CmpCallCard_model $callObject,$data,$object)
	{
	    return CmpCallCard_model_get::getCombo($this,$data,$object);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getCombox($data)
	{
		return CmpCallCard_model_get::getCombox($this, $data);
	}

	/**
	 * @return array|bool
	 */
	public function getComboxAll()
	{
		return CmpCallCard_model_get::getComboxAll($this);
	}

	/**
	 * Список значений для комбика по ComboSys или CmpCloseCardCombo_Code
	 * @param $data
	 * @return array|bool
	 */
	public function getComboValuesList($data)
	{
		return CmpCallCard_model_get::getComboValuesList($this, $data);
	}

	/**
	 * Печать результата
	 * @param $CmpCloseCard
	 * @return string
	 */
	function getResultCmpForPrint($CmpCloseCard)
	{
		return CmpCallCard_model_get::getResultCmpForPrint($this, $CmpCloseCard);
	}

	/**
	 * @param $CmpCloseCard
	 * @param $SysName
	 * @return bool|string
	 */
	function getComboRel($CmpCloseCard, $SysName)
	{
		return CmpCallCard_model_get::getComboRel($this, $CmpCloseCard, $SysName);
	}

	/**
	 * @param $CmpCloseCard
	 * @param $SysName
	 * @return bool|string
	 */
	function getComboRelEMK($CmpCloseCard, $SysName)
	{
		return CmpCallCard_model_get::getComboRelEMK($this, $CmpCloseCard, $SysName);
	}

	/**
	 * Возвращает идентификатор из справочника статусов бригад по его коду
	 * @param $emergencyTeamStatus_id
	 * @return bool|mixed
	 */
	public function getCmpCallCardEventTypeIdByEmergencyTeamStatusId($emergencyTeamStatus_id)
	{
		return CmpCallCard_model_get::getCmpCallCardEventTypeIdByEmergencyTeamStatusId($this, $emergencyTeamStatus_id);
	}

	/**
	 * Запрос на выборку параметров карты для последующей обработки
	 * @param $data
	 * @return bool|mixed
	 */
	public function getCardParamsForEvent($data)
	{
		return CmpCallCard_model_get::getCardParamsForEvent($this, $data);
	}

	/**
	 * Возвращает дополнительную информацию по карте вызова
	 * @param $data
	 * @return array|bool
	 */
	public function getAdditionalCallCardInfo($data)
	{
		return CmpCallCard_model_get::getAdditionalCallCardInfo($this, $data);
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function getCmpCallCardNgod($data)
	{
		return CmpCallCard_model_get::getCmpCallCardNgod($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getLpuAddressTerritory($data)
	{
		return CmpCallCard_model_get::getLpuAddressTerritory($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getDispatchCallUsers($data)
	{
		return CmpCallCard_model_get::getDispatchCallUsers($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getAddressForNavitel($data)
	{
		return CmpCallCard_model_get::getAddressForNavitel($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getAddressForOsmGeocode($data)
	{
		return CmpCallCard_model_get::getAddressForOsmGeocode($this, $data);
	}

	/**
	 * Возвращает адрес из талона вызова, в т.ч. неформализованные
	 * @param $data
	 * @return array|bool
	 */
	public function getCmpCallCardAddress($data)
	{
		return CmpCallCard_model_get::getCmpCallCardAddress($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	public function getUnformalizedAddressStreetKladrParams($data)
	{
		return CmpCallCard_model_get::getUnformalizedAddressStreetKladrParams($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function getDecigionTree($data)
	{
		return CmpCallCard_model_get::getDecigionTree($this, $data);
	}

	/**
	 * Возвращает массив ID МО выбранных в АРМ
	 * @return array|bool
	 */
	public function getSelectedLpuId()
	{
		return CmpCallCard_model_get::getSelectedLpuId();
	}

	/**
	 * Получение списка подстанций СМП
	 * @param $data
	 * @return array|bool
	 */
	public function getCmpCallPlaces($data)
	{
		return CmpCallCard_model_get::getCmpCallPlaces($this, $data);
	}

	/**
	 * Получение справочника нормативов назначения профилей бригад и срочности вызова
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function getCmpUrgencyAndProfileStandart($data)
	{
		return CmpCallCard_model_get::getCmpUrgencyAndProfileStandart($this, $data);
	}

	/**
	 * Получене списка мест, привязанных к правилу
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function getCmpUrgencyAndProfileStandartPlaces($data)
	{
		return CmpCallCard_model_get::getCmpUrgencyAndProfileStandartPlaces($this, $data);
	}

	/**
	 * Получене списка мест, привязанных к правилу
	 * @param $data
	 * @return array|bool
	 */
	public function getCmpUrgencyAndProfileStandartSpecPriority($data)
	{
		return CmpCallCard_model_get::getCmpUrgencyAndProfileStandartSpecPriority($this, $data);
	}

	/**
	 * Получения ID комбобокса по его коду
	 * @param $data
	 * @return bool
	 */
	public function getComboIdByCode($data)
	{
		return CmpCallCard_model_get::getComboIdByCode($this, $data);
	}

	/**
	 * Получение ID комбобокса по ComboSys
	 * @param $data
	 * @return mixed|bool
	 */
	public function getComboIdByComboSys($data)
	{
		return CmpCallCard_model_get::getComboIdByComboSys($this, $data);
	}

	/**
	 * Получение списка подстанций СМП
	 * @param $data
	 * @return array|bool
	 */
	public function getCmpCallDiagnosesFields($data)
	{
		return CmpCallCard_model_get::getCmpCallDiagnosesFields($this, $data);
	}

	/**
	 * Функция используется для доп.аутентификации пользователя при socket-соединении NodeJS для армов СМП
	 * @param $data
	 * @return array
	 */
	public function getPmUserInfo($data)
	{
		return CmpCallCard_model_get::getPmUserInfo($this, $data);
	}

	/**
	 * Получение списка параметров хранимой процедуры
	 * @param string $sp
	 * @param string $schema
	 * @return array|bool
	 */
	function getStoredProcedureParamsList($sp, $schema)
	{
		return CmpCallCard_model_get::getStoredProcedureParamsList($this, $sp, $schema);
	}

	/**
	 * Получение идентификатора типа документа по коду
	 * @param $object_name
	 * @param $code
	 * @return bool|float|int|string|null
	 */
	function getObjectIdByCode($object_name, $code)
	{
		return CmpCallCard_model_get::getObjectIdByCode($this, $object_name, $code);
	}

	/**
	 * Получение следующего номера произвольного обьекта.
	 * @param $object_name
	 * @param $num_field
	 * @return bool|float|int|string
	 */
	function getObjectNextNum($object_name, $num_field)
	{
		return CmpCallCard_model_get::getObjectNextNum($this, $object_name, $num_field);
	}

	/**
	 * Поиск подходящего документа по заданнам параметрам. Если документ не найден - создается новый.
	 * @param $data
	 * @return bool|float|int|mixed|string|null
	 * @throws Exception
	 */
	function getDocSMPForCmpCallCardDrug($data)
	{
		return CmpCallCard_model_get::getDocSMPForCmpCallCardDrug($this, $data);
	}

	/**
	 * Получение значений по умолчанию для формы использования медикаментов
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	function getCmpCallCardDrugDefaultValues($data)
	{
		return CmpCallCard_model_get::getCmpCallCardDrugDefaultValues($this, $data);
	}

	/**
	 * Получение из Wialon пройденного расстояния бригадой за промежуток времени
	 * @param $data
	 * @return array|bool
	 */
	public function getTheDistanceInATimeInterval($data)
	{
		return CmpCallCard_model_get::getTheDistanceInATimeInterval($this, $data);
	}

	/**
	 * получение списка полей в разделе Услуги для формы 110
	 * @param $data
	 * @return array|bool
	 */
	public function getUslugaFields($data)
	{
		return CmpCallCard_model_get::getUslugaFields($this, $data);
	}

	/**
	 * Возвращает поля экспертной оценки для карты закрытия вызова 110у
	 * @return array|bool
	 */
	public function getExpertResponseFields()
	{
		return CmpCallCard_model_get::getExpertResponseFields($this);
	}

	/**
	 * Возвращает оценки карты 110у
	 * @param $data
	 * @return array|bool
	 */
	public function getCmpCloseCardExpertResponses($data)
	{
		return CmpCallCard_model_get::getCmpCloseCardExpertResponses($this, $data);
	}

	/**
	 * Возвращает список федеральных результатов для карты 110у
	 * @return array
	 */
	public function getFedLeaveTypeList()
	{
		return CmpCallCard_model_get::getFedLeaveTypeList($this);
	}

	/**
	 * возвращает признак источника карты CmpCallCard
	 * @param $cmpCallCardID
	 * @return bool|int
	 */
	public function getCallCardInputTypeCode($cmpCallCardID)
	{
		return CmpCallCard_model_get::getCallCardInputTypeCode($this, $cmpCallCardID);
	}

	/**
	 * список пациентов для журнала расхождения
	 * @param $data
	 * @return array|false
	 */
	public function getPatientDiffList($data)
	{
		return CmpCallCard_model_get::getPatientDiffList($this, $data);
	}

	/**
	 * Получение информации о диагнозах
	 * @param $data
	 * @return array|false
	 */
	function getSidOoidDiags($data)
	{
		return CmpCallCard_model_get::getSidOoidDiags($this, $data);
	}

	/**
	 * Получение списка номеров карт(110/у), которых запросил СМО
	 * @param $data
	 * @return array|bool
	 */
	function getSmoQueryCallCards($data)
	{
		return CmpCallCard_model_get::getSmoQueryCallCards($this, $data);
	}

	/**
	 * Получаем флаг опер отдела "Включить функцию «Контроль вызовов»"
	 * @param $data
	 * @return array|bool|false
	 */
	function getIsCallControllFlag($data)
	{
		return CmpCallCard_model_get::getIsCallControllFlag($this, $data);
	}

    /**
     * Получение списка МО с обслуживанием на дому
     * @param $data
     * @return boolean
     */
    public function getLpuWithOperSmp($data){
        return CmpCallCard_model_get::getLpuWithOperSmp($this,$data);
    }
    
      	/**
     * Полчение дерева решений приналижащего определнное структуре
     */
	public function getConcreteDecigionTree($data){
        return CmpCallCard_model_get::getConcreteDecigionTree($this, $data);
	}

	/**
     * Получение стркутуры деревьев МО
     */
	public function getDecigionTreeLpu($data){
        return CmpCallCard_model_get::getDecigionTreeLpu($this,$data);
	}

	/**
     * Получение стркутуры деревьев подстанции
     */
	public function getDecigionTreeRegion($data){
        return CmpCallCard_model_get::getDecigionTreeRegion($this,$data);
	}

	/**
     * Получение стркутуры деревьев подстанции
     */
	public function getDecigionTreeLpuBuilding($data){
        return CmpCallCard_model_get::getDecigionTreeLpuBuilding($this,$data);
	}

	/**
     * Получение структуры для которых существует дерево решений
     */
	public function getStructuresIssetTree($data){
        return CmpCallCard_model_get::getStructuresIssetTree($this,$data);
	}

	/**
	 * получаем МО опер отдела НМП, на котором текущая служба
	 */
	function getNMPLpu($data)
	{
		return CmpCallCard_model_get::getNMPLpu($this,$data);
	}
	#endregion get
}