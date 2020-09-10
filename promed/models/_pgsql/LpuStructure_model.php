<?php
require_once("LpuStructure_model_check.php");
require_once("LpuStructure_model_copy.php");
require_once("LpuStructure_model_delete.php");
require_once("LpuStructure_model_get.php");
require_once("LpuStructure_model_getCommon.php");
require_once("LpuStructure_model_inputRules.php");
require_once("LpuStructure_model_load.php");
require_once("LpuStructure_model_save.php");
require_once("LpuStructure_model_saveCommon.php");
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      All
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Markoff Andrew
 * @version      31.08.2009
 *
 * @property CI_DB_driver $db
 * @property Registry_model $Reg_model
 * @property Attribute_model $Attribute_model
 * @property Utils_model $umodel
 * @property MedServiceLink_model $msl_model
 * @property MedService_model $MedService_model
 */
class LpuStructure_model extends SwPgModel
{
	protected $_scheme = "dbo";

	public $dateTimeForm104 = "DD.MM.YYYY";
	public $dateTimeFormTime = "HH24:MI:SS";
	public $dateTimeForm120 = "YYYY-MM-DD";

	/**
	 * Правила для входящих параметров
	 */
	public $inputRules = [];

	function __construct()
	{
		parent::__construct();
		$this->inputRules = LpuStructure_model_inputRules::getInputRules();
	}

	#region delete
	/**
	 * Привязка службы ФД к службе центра удаленной консультации
	 *
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function deleteLinkFDServiceToRCCService($data)
	{
		return LpuStructure_model_delete::deleteLinkFDServiceToRCCService($this, $data);
	}

	/**
	 * Сохранение связи МО с бюро МСЭ
	 *
	 * @param $data
	 * @return array|false
	 */
	function deleteLpuMseLink($data)
	{
		return LpuStructure_model_delete::deleteLpuMseLink($this, $data);
	}

	/**
	 * Удаление в отделении МО коек по профилю
	 *
	 * @param $data
	 * @return array|false
	 *
	 * @throws Exception
	 */
	public function deleteLpuSectionBedState($data)
	{
		return LpuStructure_model_delete::deleteLpuSectionBedState($this, $data);
	}

	/**
	 * Удаление дополнительного профиля отделения
	 *
	 * @param $data
	 * @return array|bool
	 */
	function deleteLpuSectionLpuSectionProfile($data)
	{
		return LpuStructure_model_delete::deleteLpuSectionLpuSectionProfile($this, $data);
	}

	/**
	 * Удаление вида оказания  МП
	 *
	 * @param $data
	 * @return array|bool
	 */
	function deleteLpuSectionMedicalCareKind($data)
	{
		return LpuStructure_model_delete::deleteLpuSectionMedicalCareKind($this, $data);
	}

	/**
	 * Удаление информации об обслуживании отделения
	 *
	 * @param $data
	 * @return array|bool
	 */
	function deleteLpuSectionService($data)
	{
		return LpuStructure_model_delete::deleteLpuSectionService($this, $data);
	}

	/**
	 * Удаление объекта комфортности
	 *
	 * @param $data
	 * @return array|bool
	 *
	 * @throws Exception
	 */
	function deleteSectionWardComfortLink($data)
	{
		return LpuStructure_model_delete::deleteSectionWardComfortLink($this, $data);
	}

	/**
	 * Удаление палаты
	 *
	 * @param $data
	 * @return array|bool
	 *
	 * @throws Exception
	 */
	function deleteLpuSectionWard($data)
	{
		return LpuStructure_model_delete::deleteLpuSectionWard($this, $data);
	}

	/**
	 * Удаление врача с участка
	 *
	 * @param $data
	 * @return array|bool
	 */
	function deleteMedStaffRegion($data)
	{
		return LpuStructure_model_delete::deleteMedStaffRegion($this, $data);
	}

	/**
	 * Удаление операции над койкой
	 *
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function deleteSectionBedStateOper($data)
	{
		return LpuStructure_model_delete::deleteSectionBedStateOper($this, $data);
	}
	#endregion delete
	#region check
	/**
	 * Сохраняет операцию с лицензией МО
	 *
	 * @param $data
	 * @return array|bool
	 *
	 * @throws Exception
	 */
	function checkAttachOnDates($data)
	{
		return LpuStructure_model_check::checkAttachOnDates($this, $data);
	}

	/**
	 * Проверка на уникальность врача на участке
	 *
	 * @param $data
	 * @return array|bool
	 */
	public function checkSaveMedStaffRegion($data)
	{
		return LpuStructure_model_check::checkSaveMedStaffRegion($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function checkLpuSectionFinans($data)
	{
		return LpuStructure_model_check::checkLpuSectionFinans($this, $data);
	}

	/**
	 * Проверка даты закрытия строк штатного расписания (для задачи http://redmine.swan.perm.ru/issues/17622)
	 *
	 * @param $data
	 * @return string
	 */
	public function checkStaff($data)
	{
		return LpuStructure_model_check::checkStaff($this, $data);
	}

	/**
	 * Проверка наличия дочерних объектов
	 *
	 * @param $ids
	 * @return string
	 */
	public function checkLpuSectionHasChildObjects($ids)
	{
		return LpuStructure_model_check::checkLpuSectionHasChildObjects($this, $ids);
	}

	/**
	 * Проверка существования ссылок на отделения в документах
	 *
	 * @param $ids
	 * @return string
	 */
	function checkLpuSectionLinksExists($ids)
	{
		return LpuStructure_model_check::checkLpuSectionLinksExists($this, $ids);
	}

	/**
	 * Проверяет возможность изменения (удаления) врачей на участке
	 *
	 * @param $data
	 * @param $Lpu_id
	 * @param $LpuRegion_id
	 * @param $LpuSection_id
	 * @return array|bool
	 *
	 * @throws Exception
	 */
	function checkMedStaffRegionDelAvailable($data, $Lpu_id, $LpuRegion_id, $LpuSection_id)
	{
		return LpuStructure_model_check::checkMedStaffRegionDelAvailable($this, $data, $Lpu_id, $LpuRegion_id, $LpuSection_id);
	}

	/**
	 * Проверка может ли выполнять отделение ВМП
	 *
	 * @param $data
	 * @return array
	 *
	 * @throws Exception
	 */
	function checkLpuSectionIsVMP($data)
	{
		return LpuStructure_model_check::checkLpuSectionIsVMP($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function checkLpuSectionLicence($data)
	{
		return LpuStructure_model_check::checkLpuSectionLicence($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function checkLpuSectionPlan($data)
	{
		return LpuStructure_model_check::checkLpuSectionPlan($this, $data);
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function checkUslugaSection($data)
	{
		return LpuStructure_model_check::checkUslugaSection($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function checkLpuSectionTariffMes($data)
	{
		return LpuStructure_model_check::checkLpuSectionTariffMes($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 *
	 * @throws Exception
	 */
	function checkLpuSectionShift($data)
	{
		return LpuStructure_model_check::checkLpuSectionShift($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function checkLpuSectionTariff($data)
	{
		return LpuStructure_model_check::checkLpuSectionTariff($this, $data);
	}

	/**
	 * Подсчет количества записей, помеченных как "основной врач"
	 *
	 * @param $data
	 * @param $Lpu_id
	 * @return array
	 */
	public function checkMainMedPersonal($data, $Lpu_id)
	{
		return LpuStructure_model_check::checkMainMedPersonal($this, $data, $Lpu_id);
	}

	/**
	 * Проверка на наличие незакрытых дочерних структур
	 * @param $method
	 * @param $data
	 * @return string
	 */
	public function _checkOpenChildStruct($method, $data)
	{
		return LpuStructure_model_check::_checkOpenChildStruct($this, $method, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
    public function _checkLpuUnitType($data)
	{
		return LpuStructure_model_check::_checkLpuUnitType($this, $data);
	}

	/**
	 * Проверка атрибутов отделения
	 * @param $data
	 * @return string
	 */
    public function _checkLpuSectionAttributeSignValue($data)
	{
		return LpuStructure_model_check::_checkLpuSectionAttributeSignValue($this, $data);
	}
	#endregion check
	#region load
	/**
	 * Получение операции над профилем койки
	 *
	 * @param $data
	 * @return array|bool
	 */
	function loadDBedOperation($data)
	{
		return LpuStructure_model_load::loadDBedOperation($this, $data);
	}

	/**
	 * Функция получения обслуживающих отделений для службы судебно-медицинской экспертизы трупов
	 *
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public function loadForenCorpServingMedServices($data)
	{
		return LpuStructure_model_load::loadForenCorpServingMedServices($this, $data);
	}

	/**
	 * Функция получения обслуживающих отделений для медико-криминалистической / судебно-гистологической службы
	 *
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public function loadForenHistServingMedServices($data)
	{
		return LpuStructure_model_load::loadForenHistServingMedServices($this, $data);
	}

	/**
	 * Получение списка средних длительностей лечения для отделения
	 *
	 * @param $data
	 * @return array|bool
	 */
	function loadSectionAverageDurationGrid($data)
	{
		return LpuStructure_model_load::loadSectionAverageDurationGrid($this, $data);
	}

	/**
	 * Получение типа здания
	 *
	 * @param $data
	 * @return array|bool
	 */
	function loadLpuBuildingType($data)
	{
		return LpuStructure_model_load::loadLpuBuildingType($this, $data);
	}

	/**
	 * Получение данных для редактирования связи МО с бюро МСЭ
	 *
	 * @param $data
	 * @return array|false
	 */
	function loadLpuMseLinkForm($data)
	{
		return LpuStructure_model_load::loadLpuMseLinkForm($this, $data);
	}

	/**
	 * Получение списка связей МО с бюро МСЭ
	 *
	 * @param $data
	 * @return array|bool
	 */
	function loadLpuMseLinkGrid($data)
	{
		return LpuStructure_model_load::loadLpuMseLinkGrid($this, $data);
	}

	/**
	 * Получение информации об участке для прикрепления
	 * @param $data
	 * @return array|bool
	 */
	function loadLpuRegionInfo($data)
	{
		return LpuStructure_model_load::loadLpuRegionInfo($this, $data);
	}

	/**
	 * Получение списка кодов отделений
	 * @param $data
	 * @return array|bool
	 */
	function loadLpuSectionCodeList($data)
	{
		return LpuStructure_model_load::loadLpuSectionCodeList($this, $data);
	}

	/**
	 * Получение списка дополнительных профилей отделения
	 * @param $data
	 * @return array|bool
	 */
	function loadLpuSectionLpuSectionProfileGrid($data)
	{
		return LpuStructure_model_load::loadLpuSectionLpuSectionProfileGrid($this, $data);
	}

	/**
	 * Получение списка профилей отделений
	 * @param $data
	 * @return array|bool
	 */
	function loadLpuSectionProfileList($data)
	{
		return LpuStructure_model_load::loadLpuSectionProfileList($this, $data);
	}

	/**
	 * Возвращает список обслуживаемых отделений
	 * @param $data
	 * @return array|bool
	 */
	function loadLpuSectionServiceGrid($data)
	{
		return LpuStructure_model_load::loadLpuSectionServiceGrid($this, $data);
	}

	/**
	 * Получение объекта комфортности
	 * @param $data
	 * @return array|bool
	 */
	function loadLpuSectionWardComfortLink($data)
	{
		return LpuStructure_model_load::loadLpuSectionWardComfortLink($this, $data);
	}
	#endregion load
	#region copy
	/**
	 * @param $data
	 * @param null $UslugaComplex_pid
	 * @param null $UslugaComplex_id
	 * @return bool|CI_DB_result|mixed
	 */
	function copyUslugaFromSection($data, $UslugaComplex_pid = NULL, $UslugaComplex_id = NULL)
	{
		return LpuStructure_model_copy::copyUslugaFromSection($this, $data, $UslugaComplex_pid, $UslugaComplex_id);
	}

	/**
	 * @param $data
	 * @return bool|CI_DB_result|mixed
	 */
	function copyUslugaSectionList($data)
	{
		return LpuStructure_model_copy::copyUslugaSectionList($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function ExportErmpStaff($data)
	{
		return LpuStructure_model_copy::ExportErmpStaff($this, $data);
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function hasMedStaffFactInAIDSCenter($data)
	{
		return LpuStructure_model_copy::hasMedStaffFactInAIDSCenter($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function updMaxEmergencyBed($data)
	{
		return LpuStructure_model_copy::updMaxEmergencyBed($this, $data);
	}

	/**
	 * Метод загрузки фотографии подразделения
	 * формирует два файла по пути вида вида:
	 * uploads/orgs/photos/[lpu_id]/LpuSection/[LpuSection_id].(jpg|png|gif)
	 * uploads/orgs/photos/[lpu_id]/LpuSection/thumbs/[LpuSection_id].(jpg|png|gif)
	 * @param $data
	 * @param $files
	 * @return array
	 * @throws Exception
	 */
	function uploadOrgPhoto($data, $files)
	{
		return LpuStructure_model_copy::uploadOrgPhoto($this, $data, $files);
	}

	/**
	 * Проверка, является ли подразделение СМП головным
	 * @param $data
	 * @return array|bool
	 */
	function _lpuBuildingIsHeadSmpUnit($data)
	{
		return LpuStructure_model_copy::_lpuBuildingIsHeadSmpUnit($this, $data);
	}
	#endregion copy
	#region get
	/**
	 * @param $data
	 * @param $parent_object
	 * @return array|bool
	 */
	function GetMedServiceNodeList($data, $parent_object)
	{
		return LpuStructure_model_get::GetMedServiceNodeList($this->db, $data, $parent_object);
	}

	/**
	 * Получение списка аппаратов
	 *
	 * @param $data
	 * @return array|bool
	 */
	function GetMedServiceAppNodeList($data)
	{
		return LpuStructure_model_get::GetMedServiceAppNodeList($this->db, $data);
	}

	/**
	 * Получение списка складов
	 *
	 * @param $data
	 * @param $parent_object
	 * @return array|bool
	 */
	function GetStorageNodeList($data, $parent_object)
	{
		return LpuStructure_model_get::GetStorageNodeList($this->db, $data, $parent_object);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function GetLpuNodeList($data)
	{
		return LpuStructure_model_get::GetLpuNodeList($this->db, $data);
	}

	/**
	 * Метод достает все филиалы для конкретного МО, к которым прикреплено хотя бы одно здание
	 *
	 * @param $data
	 * @return array|bool
	 */
	function GetLpuFilialNodeList($data)
	{
		return LpuStructure_model_get::GetLpuFilialNodeList($this->db, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function GetLpuBuildingNodeList($data)
	{
		return LpuStructure_model_get::GetLpuBuildingNodeList($this->db, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function GetLpuUnitNodeList($data)
	{
		return LpuStructure_model_get::GetLpuUnitNodeList($this->db, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function GetLpuUnitTypeNodeList($data)
	{
		return LpuStructure_model_get::GetLpuUnitTypeNodeList($this->db, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function GetLpuRegionTypeNodeList($data)
	{
		return LpuStructure_model_get::GetLpuRegionTypeNodeList($this->db, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function GetLpuSectionNodeList($data)
	{
		return LpuStructure_model_get::GetLpuSectionNodeList($this->db, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function GetLpuSectionPidNodeList($data)
	{
		return LpuStructure_model_get::GetLpuSectionPidNodeList($this->db, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function GetLpuRegionNodeList($data)
	{
		return LpuStructure_model_get::GetLpuRegionNodeList($this->db, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function GetLpuAllQuery($data)
	{
		return LpuStructure_model_get::GetLpuAllQuery($this->db, $data);
	}

	/**
	 * https://redmine.swan.perm.ru/issues/41129
	 *
	 * @param $data
	 * @return array|bool
	 */
	function getIsNoFRMP($data)
	{
		return LpuStructure_model_get::getIsNoFRMP($this->db, $data);
	}

	/**
	 * Получение списка коек коечного фонда отделения МО
	 *
	 * @param $data
	 * @return array|false
	 */
	public function getLpuSectionBedStateListBySectionForAPI($data)
	{
		return LpuStructure_model_get::getLpuSectionBedStateListBySectionForAPI($this, $data);
	}

	/**
	 * @param $data
	 * @param $town_id
	 * @param $street_id
	 * @param $lpuregion_id
	 * @param $lpuregionstreet_id
	 * @return array|bool
	 */
	function getStreetHouses($data, $town_id, $street_id, $lpuregion_id, $lpuregionstreet_id)
	{
		return LpuStructure_model_get::getStreetHouses($this, $data, $town_id, $street_id, $lpuregion_id, $lpuregionstreet_id);
	}

	/**
	 * Возвращает улицу по указанному идентификатору
	 *
	 * @param int $KLStreet_id ID улицы
	 * @return array
	 */
	public function getKLStreetById($KLStreet_id)
	{
		$sql = "select * from KLStreet where KLStreet_id = :KLStreet_id limit 1";
		return $this->db->query($sql, ["KLStreet_id" => $KLStreet_id])->row_array();
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getLpuUnitList($data)
	{
		return LpuStructure_model_get::getLpuUnitList($this, $data);
	}

	/**
	 * Получение списка групп отделений
	 *
	 * @param $data
	 * @return array|bool
	 */
	function getLpuUnitCombo($data)
	{
		return LpuStructure_model_get::getLpuUnitCombo($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	function getLpuUnitSetCombo($data)
	{
		return LpuStructure_model_get::getLpuUnitSetCombo($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getLpuBuildingList($data)
	{
		return LpuStructure_model_get::getLpuBuildingList($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getLpuRegionList($data)
	{
		return LpuStructure_model_get::getLpuRegionList($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getMedStaffRegion($data)
	{
		return LpuStructure_model_get::getMedStaffRegion($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getUslugaSectionTariff($data)
	{
		return LpuStructure_model_get::getUslugaSectionTariff($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function GetUslugaComplexTariff($data)
	{
		return LpuStructure_model_get::GetUslugaComplexTariff($this, $data);
	}

	/**
	 * Получение списка улиц или улицы для вывода в стуктуре МО на участок
	 *
	 * @param $data
	 * @return array|bool
	 */
	function getLpuRegionStreet($data)
	{
		return LpuStructure_model_get::getLpuRegionStreet($this, $data);
	}

	/**
	 * Получение списка улиц или улицы для вывода в стуктуре МО на службу
	 *
	 * @param $data
	 * @return array|bool
	 */
	function getMedServiceStreet($data)
	{
		return LpuStructure_model_get::getMedServiceStreet($this, $data);
	}

	/**
	 * Получение списка улиц или улицы для вывода в стуктуре МО на участок
	 *
	 * @param $data
	 * @return array|bool
	 */
	function getLpuBuildingStreet($data)
	{
		return LpuStructure_model_get::getLpuBuildingStreet($this, $data);
	}

	/**
	 * Получение тарифов на отделение
	 *
	 * @param $data
	 * @return array|bool
	 */
	function getLpuSectionTariff($data)
	{
		return LpuStructure_model_get::getLpuSectionTariff($this, $data);
	}

	/**
	 * Функция чтения справочника профилей, по которым заведены отделения в структуре МО
	 *
	 * @param $data
	 * @return array|bool
	 */
	function getLpuSectionProfile($data)
	{
		return LpuStructure_model_get::getLpuSectionProfile($this, $data);
	}

	/**
	 * Получение смен на отделение
	 *
	 * @param $data
	 * @return array|bool
	 */
	function getLpuSectionShift($data)
	{
		return LpuStructure_model_get::getLpuSectionShift($this, $data);
	}

	/**
	 * Получение коек на отделение
	 *
	 * @param $data
	 * @return array|bool
	 */
	function getLpuSectionBedState($data)
	{
		return LpuStructure_model_get::getLpuSectionBedState($this, $data);
	}

	/**
	 * Получение финансирования на отделение
	 *
	 * @param $data
	 * @return array|bool
	 */
	function getLpuSectionFinans($data)
	{
		return LpuStructure_model_get::getLpuSectionFinans($this, $data);
	}

	/**
	 * Получение лицензий на отделение
	 * @param $data
	 * @return array|bool
	 */
	function getLpuSectionLicence($data)
	{
		return LpuStructure_model_get::getLpuSectionLicence($this, $data);
	}

	/**
	 * Получение тарифов МЭС
	 * @param $data
	 * @return array|bool
	 */
	function getLpuSectionTariffMes($data)
	{
		return LpuStructure_model_get::getLpuSectionTariffMes($this, $data);
	}

	/**
	 * Получение информации о палнировании
	 * @param $data
	 * @return array|bool
	 */
	function getLpuSectionPlan($data)
	{
		return LpuStructure_model_get::getLpuSectionPlan($this, $data);
	}

	/**
	 * Получает сумму фактически выполненного муниципального заказа из реестров (поэтому запрос должен выполняться на реестровой базе).
	 * Возвращает массив вида array('LpuSectionQuote_Fact'=>'1')
	 * @param $data
	 * @return array|bool
	 */
	function getLpuSectionQuoteFact($data)
	{
		return LpuStructure_model_get::getLpuSectionQuoteFact($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getLpuSectionQuote($data)
	{
		return LpuStructure_model_get::getLpuSectionQuote($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function GetPersonDopDispPlan($data)
	{
		return LpuStructure_model_get::GetPersonDopDispPlan($this, $data);
	}

	/**
	 * Получение значения флага работы по ОМС для отделения или группы отделений
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function getLpuUnitIsOMS($data)
	{
		return LpuStructure_model_get::getLpuUnitIsOMS($this, $data);
	}

	/**
	 * получаем данные по плановым койкам отделения-родителя и его подотделений
	 * @param $data
	 * @return array|bool
	 */
	function getLpuSectionBedStatePlan($data)
	{
		return LpuStructure_model_get::getLpuSectionBedStatePlan($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function GetLpuUnitTypeList($data)
	{
		$sql = "select * from v_LpuUnitType order by LpuUnitType_Code";
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getLpuSectionGrid($data)
	{
		return LpuStructure_model_get::getLpuSectionGrid($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getLpuSectionList($data)
	{
		return LpuStructure_model_get::getLpuSectionList($this, $data);
	}

	/**
	 * Дополнительные поля для выборки списка отделений и данных для формы редактирования отделения
	 * @return string
	 */
	function getLpuSectionListAdditionalFields()
	{
		if ($this->getRegionNick() == "kz") {
			return " , lsfl.FPID ";
		}
		return "";
	}

	/**
	 * Дополнительные джойны для выборки списка отделений и данных для формы редактирования отделения
	 * @return string
	 */
	function getLpuSectionListAdditionalJoin()
	{
		if ($this->getRegionNick() == "kz") {
			return "
				LEFT JOIN LATERAL (
					select FPID
					from r101.LpuSectionFPIDLink 
					where LpuSection_id = LpuSection.LpuSection_id
					limit 1
				) as lsfl on true 
			";
		}
		return "";
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getLpuSectionPid($data)
	{
		return LpuStructure_model_get::getLpuSectionPid($this, $data);
	}

	/**
	 * Получение списка услуг или одного элемента на структуре МО
	 * @param $data
	 * @return array|bool
	 */
	function GetLpuUsluga($data)
	{
		return LpuStructure_model_get::GetLpuUsluga($this, $data);
	}

	/**
	 * Получение палат отделения
	 * @param $data
	 * @return array|bool
	 */
	function GetLpuSectionWard($data)
	{
		return LpuStructure_model_get::GetLpuSectionWard($this, $data);
	}

	/**
	 * Получение операции над профилем койки
	 * @param $data
	 * @return array|bool
	 */
	function getStaffOSMGridDetail($data)
	{
		return LpuStructure_model_get::getStaffOSMGridDetail($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getLpuSectionBedAllQuery($data)
	{
		return LpuStructure_model_get::getLpuSectionBedAllQuery($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getLpuSectionProfileforCombo($data)
	{
		return LpuStructure_model_get::getLpuSectionProfileforCombo($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getLpuSectionBedProfileforCombo($data)
	{
		return LpuStructure_model_get::getLpuSectionBedProfileforCombo($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getLpuSectionWardByIdData($data)
	{
		return LpuStructure_model_get::getLpuSectionWardByIdData($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getLpuSectionData($data)
	{
		return LpuStructure_model_get::getLpuSectionData($this, $data);
	}

	/**
	 * @param $query2export
	 * @return array|bool
	 */
	function getExp2DbfData($query2export)
	{
		return LpuStructure_model_get::getExp2DbfData($this, $query2export);
	}

	/**
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	function getAllLpuNotFRMP($data)
	{
		return LpuStructure_model_get::getAllLpuNotFRMP($this, $data);
	}

	/**
	 * Получение данных по отделению для регистратуры
	 * @param $data
	 * @return bool|mixed
	 */
	function getLpuSectionInfoForReg($data)
	{
		return LpuStructure_model_get::getLpuSectionInfoForReg($this, $data);
	}

	/**
	 * Получение примечания для отделения
	 * @param $data
	 * @return array|bool
	 */
	function getLpuSectionComment($data)
	{
		return LpuStructure_model_get::getLpuSectionComment($this, $data);
	}

	/**
	 * Получение списка доступных типов служб в зависимости от уровеня структурного элемента МО
	 * @param $data
	 * @return array|bool
	 */
	function getAllowedMedServiceTypes($data)
	{
		return LpuStructure_model_getCommon::getAllowedMedServiceTypes($this, $data);
	}

	/**
	 * Получение списка структурных элементов МО
	 * @param $data
	 * @return array|bool
	 */
	function getLpuStructureElementList($data)
	{
		return LpuStructure_model_getCommon::getLpuStructureElementList($this, $data);
	}

	/**
	 * Получение списка МО, обладающих службами ФД, не обслуживаемых ни одним консультационным центром
	 * @param $data
	 * @return array|bool
	 */
	function getLpuWithUnservedDiagMedService($data)
	{
		return LpuStructure_model_getCommon::getLpuWithUnservedDiagMedService($this, $data);
	}

	/**
	 * Получение списка служб ФД выбранного МО, не обслуживающихся ни одним консультационным центром
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function getUnservedDiagMedService($data)
	{
		return LpuStructure_model_getCommon::getUnservedDiagMedService($this, $data);
	}

	/**
	 * Получение списка служб ФД привязанных к службе ЦУК
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function getFDServicesConnectedToRCCService($data)
	{
		return LpuStructure_model_getCommon::getFDServicesConnectedToRCCService($this, $data);
	}

	/**
	 * Получение спсика МО по адресу
	 * @param $data
	 * @return array|bool
	 */
	function getLpuListByAddress($data)
	{
		return LpuStructure_model_getCommon::getLpuListByAddress($this, $data);
	}

	/**
	 * Получение списка типов подстанций СМП
	 * @param $data
	 * @return array|bool
	 */
	function getSmpUnitTypes($data)
	{
		return LpuStructure_model_getCommon::getSmpUnitTypes($this, $data);
	}

	/**
	 * Получаем список подстанций со всех МО с типом СМП
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function getLpuBuildingsForFilials($data)
	{
		return LpuStructure_model_getCommon::getLpuBuildingsForFilials($this, $data);
	}

	/**
	 * Получение информации о подстанции СМП
	 * @param $data
	 * @return array
	 */
	public function getSmpUnitData($data)
	{
		return LpuStructure_model_getCommon::getSmpUnitData($this, $data);
	}

	/**
	 * Получение информации о таймерах подстанции СМП
	 * @param $data
	 * @return array
	 */
	public function getLpuBuildingData($data)
	{
		return LpuStructure_model_getCommon::getLpuBuildingData($this, $data);
	}

	/**
	 * Получение адреса для структурного уровня лпу
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function getAddressByLpuStructure($data)
	{
		return LpuStructure_model_getCommon::getAddressByLpuStructure($this, $data);
	}

	/**
	 * Формирование строки грида обслуживаемых отделений
	 * @param $data
	 * @return array
	 */
	function getRowLpuSectionService($data)
	{
		return LpuStructure_model_getCommon::getRowLpuSectionService($this, $data);
	}

	/**
	 * Получение количества групп отделений по типу группы в МО
	 * @param $data
	 * @return array|false
	 */
	function getLpuUnitCountByType($data)
	{
		return LpuStructure_model_getCommon::getLpuUnitCountByType($this, $data);
	}

	/**
	 * Возвращает количество обслуживаемых отделений
	 * @param $data
	 * @return array|bool
	 */
	function getLpuSectionServiceCount($data)
	{
		return LpuStructure_model_getCommon::getLpuSectionServiceCount($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function getLpuWithMedServiceList($data)
	{
		return LpuStructure_model_getCommon::getLpuWithMedServiceList($this, $data);
	}

	/**
	 * @param $data
	 * @return bool|string
	 */
	function getOrgPhoto($data)
	{
		return LpuStructure_model_getCommon::getOrgPhoto($data);
	}

	/**
	 * Получение списка МО по региону. Метод для API
	 * @param $data
	 * @return array|false
	 */
	public function getLpuListByRegion($data)
	{
		return LpuStructure_model_getCommon::getLpuListByRegion($this, $data);
	}

	/**
	 * Получение списка МО по району. Метод для API
	 * @param $data
	 * @return array|false
	 */
	public function getLpuListBySubRgn($data)
	{
		return LpuStructure_model_getCommon::getLpuListBySubRgn($this, $data);
	}

	/**
	 * Получение общих данных по участку
	 * @param $data
	 * @return array|false
	 */
	function getLpuRegionByID($data)
	{
		return LpuStructure_model_getCommon::getLpuRegionByID($this, $data);
	}

	/**
	 * Получение общих данных по участку
	 * @param $data
	 * @return array|false
	 */
	function getLpuRegionByMO($data)
	{
		return LpuStructure_model_getCommon::getLpuRegionByMO($this, $data);
	}

	/**
	 * Получение информации о периоде работы врача по идентификатору
	 * @param $data
	 * @return array|false
	 */
	function getLpuRegionWorkerPlaceByID($data)
	{
		return LpuStructure_model_getCommon::getLpuRegionWorkerPlaceByID($this, $data);
	}

	/**
	 * Получение списка периодов работы врачей на участке
	 * @param $data
	 * @return array|false
	 */
	function getLpuRegionWorkerPlaceList($data)
	{
		return LpuStructure_model_getCommon::getLpuRegionWorkerPlaceList($this, $data);
	}

	/**
	 * Получение данных подразделения. Метод для API
	 * @param $data
	 * @return array|false
	 */
	function getLpuBuildingById($data)
	{
		return LpuStructure_model_getCommon::getLpuBuildingById($this, $data);
	}

	/**
	 * Получение списка подразделений. Метод для API
	 * @param $data
	 * @return array|false
	 */
	function getLpuBuildingListForAPI($data)
	{
		return LpuStructure_model_getCommon::getLpuBuildingListForAPI($this, $data);
	}

	/**
	 * Получение списка отделений. Метод для API
	 * @param $data
	 * @return array|false
	 */
	function getLpuSectionListForAPI($data)
	{
		return LpuStructure_model_getCommon::getLpuSectionListForAPI($this, $data);
	}

	/**
	 * Получение отделения. Метод для API
	 * @param $data
	 * @return array|false
	 */
	function getLpuSectionByIdForAPI($data)
	{
		return LpuStructure_model_getCommon::getLpuSectionByIdForAPI($this, $data);
	}

	/**
	 * Получение отделения. Метод для API
	 * @param $data
	 * @return array|false
	 */
	function getLpuSectionForAPI($data)
	{
		return LpuStructure_model_getCommon::getLpuSectionForAPI($this, $data);
	}

	/**
	 * Получение данных о группе отделений МО по идентификатору. Метод для API
	 * @param $data
	 * @return array|false
	 */
	public function getLpuUnitByIdForAPI($data)
	{
		return LpuStructure_model_getCommon::getLpuUnitByIdForAPI($this, $data);
	}

	/**
	 * Получение списка групп отделений МО по идентификатору подразделения. Метод для API
	 * @param $data
	 * @return array|false
	 */
	public function getLpuUnitListForAPI($data)
	{
		return LpuStructure_model_getCommon::getLpuUnitListForAPI($this, $data);
	}

	/**
	 * Получения списка дополнительных профилей отделения. Метод для API
	 * @param $data
	 * @return array|false
	 */
	function getLpuSectionLpuSectionProfileListForAPI($data)
	{
		return LpuStructure_model_getCommon::getLpuSectionLpuSectionProfileListForAPI($this, $data);
	}

	/**
	 * Получения списка участков. Метод для API
	 * @param $data
	 * @return array|false
	 */
	function getLpuRegionListForAPI($data)
	{
		return LpuStructure_model_getCommon::getLpuRegionListForAPI($this, $data);
	}

	/**
	 * Получения списка участков по МО. Метод для API
	 * @param $data
	 * @return array|false
	 */
	function getLpuRegionListByMOForAPI($data)
	{
		return LpuStructure_model_getCommon::getLpuRegionListByMOForAPI($this, $data);
	}

	/**
	 * Получения списка палат. Метод для API
	 * @param $data
	 * @return array|false
	 */
	function getLpuSectionWardListForAPI($data)
	{
		return LpuStructure_model_getCommon::getLpuSectionWardListForAPI($this, $data);
	}

	/**
	 * Получение палаты. Метод для API
	 * @param $data
	 * @return array|false
	 */
	function getLpuSectionWardByIdForAPI($data)
	{
		return LpuStructure_model_getCommon::getLpuSectionWardByIdForAPI($this, $data);
	}

	/**
	 * Получения списка объектов комфортности. Метод для API
	 * @param $data
	 * @return array|false
	 */
	function getLpuSectionWardComfortLinkListForAPI($data)
	{
		return LpuStructure_model_getCommon::getLpuSectionWardComfortLinkListForAPI($this, $data);
	}

	/**
	 * Получения объекта комфортности. Метод для API
	 * @param $data
	 * @return array|false
	 */
	function getLpuSectionWardComfortLinkForAPI($data)
	{
		return LpuStructure_model_getCommon::getLpuSectionWardComfortLinkForAPI($this, $data);
	}

	/**
	 * Получение идентификатора вида группы отделений по коду
	 * @param $LpuUnitType_Code
	 * @return bool|float|int|string
	 */
	function getLpuUnitTypeId($LpuUnitType_Code)
	{
		return LpuStructure_model_getCommon::getLpuUnitTypeId($this, $LpuUnitType_Code);
	}

	/**
	 * Получение идентификатора профиля отделения по коду
	 * @param $LpuSectionProfile_Code
	 * @return bool|float|int|string
	 */
	function getLpuSectionProfileId($LpuSectionProfile_Code)
	{
		return LpuStructure_model_getCommon::getLpuSectionProfileId($this, $LpuSectionProfile_Code);
	}

	/**
	 * Получение параметров службы НМП
	 * @param $data
	 * @return array|bool|false
	 * @throws Exception
	 */
	function getNmpParams($data)
	{
		return LpuStructure_model_getCommon::getNmpParams($this, $data);
	}

	/**
	 * Получения списка объектов комфортности. Метод для API
	 * @param $data
	 * @return array|false
	 */
	public function getMOById($data)
	{
		return LpuStructure_model_getCommon::getMOById($this, $data);
	}

	/**
	 * Получение списка профилей (ФРМО)
	 * @param $data
	 * @return array|bool
	 */
	function getLpuUnitProfile($data)
	{
		return LpuStructure_model_getCommon::getLpuUnitProfile($this, $data);
	}

	/**
	 * Получение списка профилей (ФРМО)
	 * @param $data
	 * @return array|bool
	 */
	function getFRMPSubdivisionType($data)
	{
		return LpuStructure_model_getCommon::getFRMPSubdivisionType($this, $data);
	}

	/**
	 * Получение МО обслуживания адреса (МО обслуживания активного вызова) по участку
	 * @param $data
	 * @return array|bool
	 */
	function getLpuAddress($data)
	{
		return LpuStructure_model_getCommon::getLpuAddress($this, $data);
	}

	/**
	 * Получить номер телефона из настроек группы отделений
	 * @param $data
	 * @return array|bool
	 */
	function getLpuPhoneMO($data)
	{
		return LpuStructure_model_getCommon::getLpuPhoneMO($this, $data);
	}

	/**
	 * Получение штатного расписания
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	public function getLpuStaffGridDetail($data)
	{
		return LpuStructure_model_getCommon::getLpuStaffGridDetail($this, $data);
	}

	/**
	 * Загрузка списка МО для формы "Выбор МО для управления"
	 * @return mixed
	 */
	public function getLpuListWithSmp()
	{
		return LpuStructure_model_getCommon::getLpuListWithSmp($this);
	}

	/**
	 * Загрузка списка Функциональных подразделений по СУР
	 * @param $data
	 * @return array|false
	 */
	public function getFpList($data)
	{
		return LpuStructure_model_getCommon::getFpList($this, $data);
	}

	/**
	 * Загрузка списка Функциональных подразделений по СУР
	 * @return array|false
	 */
	public function getLpuSectionBedProfileLinkFed()
	{
		return LpuStructure_model_getCommon::getLpuSectionBedProfileLinkFed($this);
	}

    /**
     * Загрузка атрибутов отделения
     */
    public function getLpuSectionAttributes($data) {
        return LpuStructure_model_getCommon::getLpuSectionAttributes($this, $data);
    }
	#endregion get
	#region save
	/**
	 * @param $data
	 * @return array|bool
	 *
	 * @throws Exception
	 */
	function saveLpuUnit($data)
	{
		return LpuStructure_model_save::saveLpuUnit($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function saveLpuBuilding($data)
	{
		return LpuStructure_model_save::saveLpuBuilding($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 *
	 * @throws Exception
	 */
	public function saveLpuSection($data)
	{
		return LpuStructure_model_save::saveLpuSection($this, $data);
	}

	/**
	 * Сохранение доп. параметров
	 * Заглушка для регионов, в которых нет необходимости отдельно сохранять дополнительные параметры отделения
	 *
	 * @param $data
	 * @return array
	 */
	function saveOtherLpuSectionParams($data)
	{
		return [["Error_Msg" => ""]];
	}

	/**
	 * @param $data
	 * @return array|bool
	 *
	 * @throws Exception
	 */
	function SaveLpuRegion($data)
	{
		return LpuStructure_model_save::SaveLpuRegion($this, $data);
	}

	/**
	 * Сохраняет  операцию с лицензией МО
	 *
	 * @param $data
	 * @return array|bool
	 */
	function saveMedStaffRegion($data)
	{
		return LpuStructure_model_save::saveMedStaffRegion($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 *
	 * @throws Exception
	 */
	function SaveUslugaSection($data)
	{
		return LpuStructure_model_save::SaveUslugaSection($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function SaveUslugaSectionTariff($data)
	{
		return LpuStructure_model_save::SaveUslugaSectionTariff($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function SaveUslugaComplexTariff($data)
	{
		return LpuStructure_model_save::SaveUslugaComplexTariff($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function SaveLpuSectionTariff($data)
	{
		return LpuStructure_model_save::SaveLpuSectionTariff($this, $data);
	}

	/**
	 * @param $data
	 * @return array
	 *
	 * @throws Exception
	 */
	function SaveLpuSectionShift($data)
	{
		return LpuStructure_model_save::SaveLpuSectionShift($this, $data);
	}

	/**
	 * Сохранение палаты в структуре МО
	 *
	 * @param $data
	 * @return array|mixed
	 *
	 * @throws Exception
	 */
	function SaveLpuSectionBedState($data)
	{
		return LpuStructure_model_save::SaveLpuSectionBedState($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function SaveLpuSectionFinans($data)
	{
		return LpuStructure_model_save::SaveLpuSectionFinans($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function SaveLpuSectionLicence($data)
	{
		return LpuStructure_model_save::SaveLpuSectionLicence($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function SaveLpuSectionTariffMes($data)
	{
		return LpuStructure_model_save::SaveLpuSectionTariffMes($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function SaveLpuSectionPlan($data)
	{
		return LpuStructure_model_save::SaveLpuSectionPlan($this, $data);
	}

	/**
	 * Метод сохраняет данные формы PersonDopDispPlan в структуре МО
	 *
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function SavePersonDopDispPlan($data)
	{
		return LpuStructure_model_save::SavePersonDopDispPlan($this, $data);
	}

	/**
	 * Метод сохраняет данные формы LpuSectionQuote в структуре МО
	 *
	 * @param $data
	 * @return array
	 *
	 * @throws Exception
	 */
	function SaveLpuSectionQuote($data)
	{
		return LpuStructure_model_save::SaveLpuSectionQuote($this, $data);
	}

	/**
	 * Сохранение территории обслуживамой подразделением
	 *
	 * @param $data
	 * @return array|bool
	 */
	public function SaveLpuBuildingStreet($data)
	{
		return LpuStructure_model_save::SaveLpuBuildingStreet($this, $data);
	}

	/**
	 * @param $data
	 * @return mixed
	 */
	function SaveMedServiceStreet($data)
	{
		return LpuStructure_model_save::SaveMedServiceStreet($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function SaveLpuRegionStreet($data)
	{
		return LpuStructure_model_save::SaveLpuRegionStreet($this, $data);
	}

	/**
	 * Сохранение палатной структуры
	 * @param $data
	 * @return array|mixed
	 * @throws Exception
	 */
	function SaveLpuSectionWard($data)
	{
		return LpuStructure_model_save::SaveLpuSectionWard($this, $data);
	}

	/**
	 * Сохранение объекта комфортности
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function saveLpuSectionWardComfortLink($data)
	{
		return LpuStructure_model_save::saveLpuSectionWardComfortLink($this, $data);
	}

	/**
	 * Сохранение операции над профилем койки
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function saveDBedOperation($data)
	{
		return LpuStructure_model_save::saveDBedOperation($this, $data);
	}

	/**
	 * Сохранение операции над профилем койки
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function saveStaffOSMGridDetail($data)
	{
		return LpuStructure_model_save::saveStaffOSMGridDetail($this, $data);
	}

	/**
	 * Сохранение комментария для отделения
	 * @param $data
	 * @return array
	 */
	function saveLpuSectionComment($data)
	{
		return LpuStructure_model_save::saveLpuSectionComment($this, $data);
	}

	/**
	 * Сохранение средней длительности лечения для отделения
	 * @param $data
	 * @return array|bool
	 */
	function saveSectionAverageDuration($data)
	{
		return LpuStructure_model_save::saveSectionAverageDuration($this, $data);
	}

	/**
	 * Функция сохранения обслуживающих отделений для службы судебно-медицинской экспертизы трупов
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public function saveForenCorpServingMedServices($data)
	{
		return LpuStructure_model_saveCommon::saveForenCorpServingMedServices($this, $data);
	}

	/**
	 * Функция сохранения обслуживающих отделений для медико-криминалистической / судебно-гистологической службы
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public function saveForenHistServingMedServices($data)
	{
		return LpuStructure_model_saveCommon::saveForenHistServingMedServices($this, $data);
	}

	/**
	 * Привязка службы ФД к службе центра удаленной консультации
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function saveLinkFDServiceToRCCService($data)
	{
		return LpuStructure_model_saveCommon::saveLinkFDServiceToRCCService($this, $data);
	}

	/**
	 * Сохранение доп параметров подстанции
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function saveLpuBuildingAdditionalParams($data)
	{
		return LpuStructure_model_saveCommon::saveLpuBuildingAdditionalParams($this, $data);
	}

	/**
	 * Сохранение связи МО с бюро МСЭ
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function saveLpuMseLink($data)
	{
		return LpuStructure_model_saveCommon::saveLpuMseLink($this, $data);
	}

	/**
	 * Получение списка мед. оборудования
	 */
	function loadLpuSectionMedProductTypeLinkGrid($data) {
		$query = "
			select
				 lslsp.LpuSectionMedProductTypeLink_id as \"LpuSectionMedProductTypeLink_id\"
				,mpt.MedProductType_id as \"MedProductType_id\"
				,mpt.MedProductType_Name as \"MedProductType_Name\"
				,lslsp.LpuSectionMedProductTypeLink_TotalAmount as \"LpuSectionMedProductTypeLink_TotalAmount\"
				,lslsp.LpuSectionMedProductTypeLink_IncludePatientKVI as \"LpuSectionMedProductTypeLink_IncludePatientKVI\"
				,lslsp.LpuSectionMedProductTypeLink_IncludeReanimation as \"LpuSectionMedProductTypeLink_IncludeReanimation\"
				,to_char(lslsp.LpuSectionMedProductTypeLink_begDT, 'DD.MM.YYYY') as \"LpuSectionMedProductTypeLink_begDT\"
				,to_char(lslsp.LpuSectionMedProductTypeLink_endDT, 'DD.MM.YYYY') as \"LpuSectionMedProductTypeLink_endDT\"
				,1 as \"RecordStatus_Code\"
			from dbo.v_LpuSectionMedProductTypeLink lslsp
				inner join passport.v_MedProductType mpt on mpt.MedProductType_id = lslsp.MedProductType_id
			where
				lslsp.LpuSection_id = :LpuSection_id
		";
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 * Сохранение дополнительного профиля отделения
	 * @param $data
	 * @return array|bool
	 */
	function saveLpuSectionLpuSectionProfile($data)
	{
		return LpuStructure_model_saveCommon::saveLpuSectionLpuSectionProfile($this, $data);
	}

	/**
	 * Сохранение мед. оборудования
	 */
	function saveLpuSectionMedProductTypeLink($data) {
		$query = "
			select
				LpuSectionMedProductTypeLink_id as \"LpuSectionMedProductTypeLink_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from dbo.p_LpuSectionMedProductTypeLink_" . (!empty($data['LpuSectionMedProductTypeLink_id']) && $data['LpuSectionMedProductTypeLink_id'] > 0 ? "upd" : "ins") . "(
				LpuSectionMedProductTypeLink_id := :LpuSectionMedProductTypeLink_id,
				LpuSection_id := :LpuSection_id,
				MedProductType_id := :MedProductType_id,
				LpuSectionMedProductTypeLink_TotalAmount := :LpuSectionMedProductTypeLink_TotalAmount,
				LpuSectionMedProductTypeLink_IncludePatientKVI := :LpuSectionMedProductTypeLink_IncludePatientKVI,
				LpuSectionMedProductTypeLink_IncludeReanimation := :LpuSectionMedProductTypeLink_IncludeReanimation,
				LpuSectionMedProductTypeLink_begDT := :LpuSectionMedProductTypeLink_begDT,
				LpuSectionMedProductTypeLink_endDT := :LpuSectionMedProductTypeLink_endDT,
				pmUser_id := :pmUser_id
			)
		";

		$queryParams = array(
			'LpuSectionMedProductTypeLink_id' => (!empty($data['LpuSectionMedProductTypeLink_id']) && $data['LpuSectionMedProductTypeLink_id'] > 0 ? $data['LpuSectionMedProductTypeLink_id'] : NULL),
			'LpuSection_id' => $data['LpuSection_id'],
			'MedProductType_id' => $data['MedProductType_id'],
			'LpuSectionMedProductTypeLink_TotalAmount' => $data['LpuSectionMedProductTypeLink_TotalAmount'],
			'LpuSectionMedProductTypeLink_IncludePatientKVI' => $data['LpuSectionMedProductTypeLink_IncludePatientKVI'],
			'LpuSectionMedProductTypeLink_IncludeReanimation' => $data['LpuSectionMedProductTypeLink_IncludeReanimation'],
			'LpuSectionMedProductTypeLink_begDT' => $data['LpuSectionMedProductTypeLink_begDT'],
			'LpuSectionMedProductTypeLink_endDT' => (!empty($data['LpuSectionMedProductTypeLink_endDT']) ? $data['LpuSectionMedProductTypeLink_endDT'] : NULL),
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
	 * Удаление мед. оборудования
	 */
	function deleteLpuSectionMedProductTypeLink($data) {
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from dbo.p_LpuSectionMedProductTypeLink_del(
				LpuSectionMedProductTypeLink_id := :LpuSectionMedProductTypeLink_id
			)
		";

		$queryParams = array(
			'LpuSectionMedProductTypeLink_id' => $data['LpuSectionMedProductTypeLink_id']
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
	 * Сохранение вида оказания  МП
	 * @param $data
	 * @return array|bool
	 */
	function saveLpuSectionMedicalCareKind($data)
	{
		return LpuStructure_model_saveCommon::saveLpuSectionMedicalCareKind($this, $data);
	}

	/**
	 * @param $data
	 * @return mixed
	 */
	public function saveSmpUnitTimes($data)
	{
		return LpuStructure_model_saveCommon::saveSmpUnitTimes($this, $data);
	}

	/**
	 * Сохранение параметров подстанции
	 * @param $data
	 * @return array|false
	 */
	public function saveSmpUnitParams($data)
	{
		return LpuStructure_model_saveCommon::saveSmpUnitParams($this, $data);
	}

	/**
	 * Сохранение информации об обслуживании отделения
	 * @param $data
	 * @return array|bool
	 */
	function saveLpuSectionService($data)
	{
		return LpuStructure_model_saveCommon::saveLpuSectionService($this, $data);
	}

	/**
	 * Сохранение штатного расписания
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function saveLpuStaffGridDetail($data)
	{
		return LpuStructure_model_saveCommon::saveLpuStaffGridDetail($this, $data);
	}

	/**
	 * Сохранение параметров службы НМП
	 * @param $data
	 * @return CI_DB_result
	 * @throws Exception
	 */
	function saveNmpParams($data)
	{
		return LpuStructure_model_saveCommon::saveNmpParams($this, $data);
	}
	
	/**
	 * получение основного и дополнительных профилей отделения
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function getLpuStructureProfileAll($data)
	{
		return LpuStructure_model_saveCommon::getLpuStructureProfileAll($this, $data);
	}
	#endregion save
}