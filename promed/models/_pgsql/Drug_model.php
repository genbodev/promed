<?php
defined("BASEPATH") or die ("No direct script access allowed");
require_once("Drug_model_save.php");
require_once("Drug_model_get.php");
/**
 * Utils - модель для работы с медикаментами, ну и до кучи с аптеками
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (megatherion@list.ru)
 * @version      15.07.2009
 *
 * @property CI_DB_driver $db
 * @property Dlo_EvnRecept_model ermodel
 */
class Drug_model extends swPgModel
{
	public $dateTimeForm104 = "DD.MM.YYYY";
	public $dateTimeForm108 = "HH24:MI:SS";
	public $dateTimeForm120 = "YYYY-MM-DD HH24:MI:SS";

	public $numericForm18_2 = "FM999999999999999999.00";

	function __construct()
	{
		parent::__construct();
	}

	#region get
	/**
	 * Получение списка открытых медикаментов
	 * @param $data
	 * @return mixed
	 */
	function getDrugGrid($data)
	{
		return Drug_model_get::getDrugGrid($this, $data);
	}

	/**
	 * Получение списка аптек с остатками по выбранному медикаменту
	 * @param $data
	 * @return array|bool
	 */
	function getDrugOstat($data)
	{
		return Drug_model_get::getDrugOstat($this, $data);
	}

	/**
	 * Еще одно получение списка аптек с остатками по выбранному медикаменту
	 * @param $data
	 * @return array|bool
	 */
	function getDrugOstatGrid($data)
	{
		return Drug_model_get::getDrugOstatGrid($this, $data);
	}

	/**
	 * Получение последней даты обновления остатков
	 * @return array|bool
	 */
	function getDrugOstatUpdateTime()
	{
		return Drug_model_get::getDrugOstatUpdateTime($this);
	}

	/**
	 * Получение последней даты обновления остатков
	 * @return array|bool
	 */
	function getDrugOstatRASUpdateTime()
	{
		return Drug_model_get::getDrugOstatRASUpdateTime($this);
	}

	/**
	 * Получение списка остатков по медикаментам по выбранной аптеке
	 * @param $data
	 * @return array|bool
	 */
	function getDrugOstatByFarmacyGrid($data)
	{
		return Drug_model_get::getDrugOstatByFarmacyGrid($this, $data);
	}

	/**
	 * Получение списка подразделений МО прикрепленных к аптеке
	 * @param $data
	 * @return array|false
	 */
	function getLpuBuildingLinkedByOrgFarmacy($data)
	{
		return Drug_model_get::getLpuBuildingLinkedByOrgFarmacy($this, $data);
	}

	/**
	 * Получение списка подразделений МО прикрепленных к аптеке склада
	 * @param $data
	 * @return array|false
	 */
	function getLpuBuildingStorageLinkedByOrgFarmacy($data)
	{
		return Drug_model_get::getLpuBuildingStorageLinkedByOrgFarmacy($this, $data);
	}

	/**
	 * Получение списка прикрепления МО/подразделений МО к аптеке
	 * @param $data
	 * @return array|bool
	 */
	public function GetMoByFarmacy($data)
	{
		return Drug_model_get::GetMoByFarmacy($this, $data);
	}

	/**
	 * Получение полного списка аптек
	 * @param $data
	 * @return array|bool
	 */
	function getOrgFarmacyGrid($data)
	{
		return Drug_model_get::getOrgFarmacyGrid($this, $data);
	}

	/**
	 * Получение полного списка аптек (для формы просмотра прикрепления к МО)
	 * @param $data
	 * @return array|bool
	 */
	function getOrgFarmacyGridByLpu($data)
	{
		return Drug_model_get::getOrgFarmacyGridByLpu($this, $data);
	}

	/**
	 * Получение полного списка аптек
	 * @param $data
	 * @return array|bool
	 */
	function getOrgFarmacyNetGrid($data)
	{
		return Drug_model_get::getOrgFarmacyNetGrid($this, $data);
	}
	#endregion get
	#region check
	/**
	 * Получение списка остатков по медикаменту на аптечном складе
	 * @param $data
	 * @return array|bool
	 */
	function checkDrugOstatOnSklad($data)
	{
		return Drug_model_save::checkDrugOstatOnSklad($this, $data);
	}

	/**
	 * Проверка дублирования аптек при включении
	 * Возможно, больше не потребуется, но пускай будет
	 * @param $data
	 * @return array|bool
	 */
	function checkOrgFarmacyDoubles($data)
	{
		return Drug_model_save::checkOrgFarmacyDoubles($this, $data);
	}
	#endregion check
	#region delete
	/**
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	function deleteDrugState($data)
	{
		return Drug_model_save::deleteDrugState($this, $data);
	}

	/**
	 * Удаление данных о прикреплении подразделений МО к аптеке
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function deleteLpuBuildingLinkData($data)
	{
		return Drug_model_save::deleteLpuBuildingLinkData($this, $data);
	}
	#endregion delete
	#region search
	/**
	 * @param $data
	 * @return array|bool
	 */
	function SearchDrugRlsList($data)
	{
		return Drug_model_save::SearchDrugRlsList($this, $data);
	}

	/**
	 * Поиск МНН по всему справочнику без учета даты
	 * Используется в фильтре в окне поиске рецепта
	 * @param $data
	 * @return array|bool
	 */
	function searchFullDrugMnnList($data)
	{
		return Drug_model_save::searchFullDrugMnnList($this, $data);
	}

	/**
	 * Поиск медикаментов по всему справочнику
	 * Используется в фильтре в окне поиске рецепта
	 */
	function searchFullDrugList($data)
	{
		return Drug_model_save::searchFullDrugList($this, $data);
	}
	#endregion search
	#region common
	/**
	 * Изменение приоритета аптеки
	 * @param $data
	 * @return array|bool
	 */
	function orgFarmacyReplace($data)
	{
		return Drug_model_save::orgFarmacyReplace($this, $data);
	}

	/**
	 * Включение и выключение аптек
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function vklOrgFarmacy($data)
	{
		return Drug_model_save::vklOrgFarmacy($this, $data);
	}
	#endregion common
	#region save
	/**
	 * Сохранение наименования МНН
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function saveDrugMnnLatinName($data)
	{
		return Drug_model_save::saveDrugMnnLatinName($this, $data);
	}

	/**
	 * Сохранение торг. наименования
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function saveDrugTorgLatinName($data)
	{
		return Drug_model_save::saveDrugTorgLatinName($this, $data);
	}

	/**
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function saveDrugState($data)
	{
		return Drug_model_save::saveDrugState($this, $data);
	}

	/**
	 * Сохранение данных о прикреплении подразделений МО к аптеке
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function saveLpuBuildingLinkDataFromJSON($data)
	{
		return Drug_model_save::saveLpuBuildingLinkDataFromJSON($this, $data);
	}

	/**
	 * Сохранение данных о прикреплении подразделений МО к складам аптеки
	 * @param $data
	 * @return array
	 */
	function saveLpuBuildingStorageLinkDataFromJSON($data)
	{
		return Drug_model_save::saveLpuBuildingStorageLinkDataFromJSON($this, $data);
	}

	/**
	 * Запись прикрепления подразделений МО к аптеке
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public function saveMoByFarmacy3($data)
	{
		return Drug_model_save::saveMoByFarmacy3($this, $data);
	}

	/**
	 * Сохранение записи о признании рецепта недействительным
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	public function saveReceptWrong($data)
	{
		return Drug_model_save::saveReceptWrong($this, $data);
	}

	/**
	 * Запись прикрепления подразделений МО к аптеке
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public function saveMoByFarmacy($data)
	{
		return Drug_model_save::saveMoByFarmacy($this, $data);
	}
	#endregion save
	#region load
	/**
	 * Получение комбобокса медикамента
	 * @param $data
	 * @return array|bool
	 */
	function loadDrugProtoMnnCombo($data)
	{
		return Drug_model_get::loadDrugProtoMnnCombo($this, $data);
	}

	/**
	 * Получение списка МНН с возможностью фильтрации по части наименования
	 * @param $data
	 * @return array|bool
	 */
	function loadDrugMnnGrid($data)
	{
		return Drug_model_get::loadDrugMnnGrid($this, $data);
	}

	/**
	 * Получение списка торговых наименований с возможностью фильтрации по части наименования
	 * @param $data
	 * @return array|bool
	 */
	function loadDrugTorgGrid($data)
	{
		return Drug_model_get::loadDrugTorgGrid($this, $data);
	}

	/**
	 *  Получение списка остатков по медикаменту в аптеках
	 * @param $data
	 * @param $options
	 * @return array|bool
	 */
	function loadFarmacyOstatList($data, $options)
	{
		return Drug_model_get::loadFarmacyOstatList($this, $data, $options);
	}

	/**
	 * Получение списка медикаментов в комбобокс в рецепте
	 * @param $data
	 * @param $options
	 * @return array|bool
	 */
	function loadDrugList($data, $options)
	{
		return Drug_model_get::loadDrugList($this, $data, $options);
	}

	/**
	 * Загрузка списка
	 * @param $data
	 * @return array|bool
	 */
	function loadSicknessDrugList($data)
	{
		return Drug_model_get::loadSicknessDrugList($this, $data);
	}

	/**
	 * Загрузка списка МНН
	 * @param $data
	 * @param $options
	 * @return array|bool
	 */
	function loadDrugMnnList($data, $options)
	{
		return Drug_model_get::loadDrugMnnList($this, $data, $options);
	}

	/**
	 * Загрузка списка комплексных мнн
	 * @param $data
	 * @param $options
	 * @return array|bool
	 */
	function loadDrugComplexMnnList($data, $options)
	{
		return Drug_model_get::loadDrugComplexMnnList($this, $data, $options);
	}

	/**
	 * Получение списка комплексных МНН (выборка из ЖНВЛП)
	 * @param $data
	 * @return array|bool
	 */
	function loadDrugComplexMnnJnvlpList($data)
	{
		return Drug_model_get::loadDrugComplexMnnJnvlpList($this, $data);
	}

	/**
	 * Загрузка списка
	 * @param $data
	 * @param $options
	 * @return array|bool
	 */
	function loadDrugRlsList($data, $options)
	{
		return Drug_model_get::loadDrugRlsList($this, $data, $options);
	}

	/**
	 * Получение списка остатков по медикаменту в аптеках
	 * @param $data
	 * @param $options
	 * @return array|bool
	 */
	function loadFarmacyRlsOstatList($data, $options)
	{
		return Drug_model_get::loadFarmacyRlsOstatList($this, $data, $options);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadDrugStateGrid($data)
	{
		return Drug_model_get::loadDrugStateGrid($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadDrugState($data)
	{
		return Drug_model_get::loadDrugState($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadDrugProtoCombo($data)
	{
		return Drug_model_get::loadDrugProtoCombo($this, $data);
	}

	/**
	 * Получение данных для формы признания рецепта неправильно выписанным
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function loadReceptWrongInfo($data)
	{
		return Drug_model_get::loadReceptWrongInfo($this, $data);
	}

	/**
	 * Получение списка аптек для комбобокса
	 * @param $data
	 * @return array|bool|false
	 */
	function loadOrgFarmacyCombo($data)
	{
		return Drug_model_get::loadOrgFarmacyCombo($this, $data);
	}

	/**
	 * Получение списка складов аптеки для комбобокса
	 * @param $data
	 * @return array|bool|false
	 */
	function loadOrgFarmacyStorageCombo($data)
	{
		return Drug_model_get::loadOrgFarmacyStorageCombo($this, $data);
	}
	#endregion load
}