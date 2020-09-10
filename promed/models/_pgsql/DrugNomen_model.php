<?php
defined("BASEPATH") or die ("No direct script access allowed");
require_once("DrugNomen_model_get.php");
require_once("DrugNomen_model_save.php");
/**
 * Class DrugNomen_model
 * @property CI_DB_driver $db
 * @property RlsDrug_model $RlsDrug_model
 */
class DrugNomen_model extends swPgModel
{
	public $dateTimeForm104 = "DD.MM.YYYY";

	/**
	 *  Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}
	#region save

	/**
	 *  Сохранение латинского наименования МНН
	 * @param $data
	 * @return bool|CI_DB_result
	 */
	function saveActmatters_LatName($data)
	{
		return DrugNomen_model_save::saveActmatters_LatName($this, $data);
	}

	/**
	 * Сохранение латинского наименования комплексного МНН
	 * @param $data
	 * @return bool|CI_DB_result
	 */
	function saveDrugComplexMnn_LatName($data)
	{
		return DrugNomen_model_save::saveDrugComplexMnn_LatName($this, $data);
	}

	/**
	 * Сохранение латинского наименования ЛП
	 * @param $data
	 * @return bool|CI_DB_result
	 */
	function saveDrugTorg_NameLatin($data)
	{
		return DrugNomen_model_save::saveDrugTorg_NameLatin($this, $data);
	}

	/**
	 * Сохранение латинского наименования торгового названия
	 * @param $data
	 * @return array|bool
	 */
	function saveTradenames_LatName($data)
	{
		return DrugNomen_model_save::saveTradenames_LatName($this, $data);
	}

	/**
	 * Сохранение латинского наименования формы выпуска
	 * @param $data
	 * @return array|bool
	 */
	function saveClsdrugforms_LatName($data)
	{
		return DrugNomen_model_save::saveClsdrugforms_LatName($this, $data);
	}

	/**
	 * Сохранение латинского наименования дозировки
	 * @param $data
	 * @return array|bool
	 */
	function saveUnit_LatName($data)
	{
		return DrugNomen_model_save::saveUnit_LatName($this, $data);
	}

	/**
	 * Сохранение кода комплексного МНН
	 * @param $data
	 * @return array
	 */
	function saveDrugComplexMnnCode($data)
	{
		return DrugNomen_model_save::saveDrugComplexMnnCode($this, $data);
	}

	/**
	 * Сохранение кода торгового наименования
	 * @param $data
	 * @return array
	 */
	function saveDrugTorgCode($data)
	{
		return DrugNomen_model_save::saveDrugTorgCode($this, $data);
	}

	/**
	 * Сохранение кода группировочного торгового наименования
	 * @param $data
	 * @return array
	 */
	function saveDrugPrepFasCode($data)
	{
		return DrugNomen_model_save::saveDrugPrepFasCode($this, $data);
	}

	/**
	 * Сохранение кода МНН
	 * @param $data
	 * @return array
	 */
	function saveDrugMnnCode($data)
	{
		return DrugNomen_model_save::saveDrugMnnCode($this, $data);
	}

	/**
	 * Сохранение норматива
	 * @param $data
	 * @return array|bool
	 */
	function saveDrugNomen($data)
	{
		return DrugNomen_model_save::saveDrugNomen($this, $data);
	}

	/**
	 * Сохранение кода организации
	 * @param $data
	 * @return array|bool
	 */
	function saveDrugNomenOrgLink($data)
	{
		return DrugNomen_model_save::saveDrugNomenOrgLink($this, $data);
	}

	/**
	 * Сохранение записи справочника Количество товара в упаковке
	 * @param $data
	 * @return array
	 */
	function saveDrugPrepEdUcCount($data)
	{
		return DrugNomen_model_save::saveDrugPrepEdUcCount($this, $data);
	}

	/**
	 * Редактирование справочника кодов РЗН
	 * @param $data
	 * @return array|bool
	 */
	function saveDrugRMZ($data)
	{
		return DrugNomen_model_save::saveDrugRMZ($this, $data);
	}

	/**
	 * Редактирование связи справочника кодов РЗН со справочником ЛП
	 * @param $data
	 * @return array
	 */
	function saveDrugRMZLink($data)
	{
		return DrugNomen_model_save::saveDrugRMZLink($this, $data);
	}

	/**
	 * Редактирование справочника кодов ЛС ВЗН
	 * @param $data
	 * @return array|bool
	 */
	function saveDrugVznData($data)
	{
		return DrugNomen_model_save::saveDrugVznData($this, $data);
	}

	/**
	 * Сохранение записи справочника Количество товара в упаковке
	 * @param $data
	 * @return array
	 */
	function saveGoodsPackCount($data)
	{
		return DrugNomen_model_save::saveGoodsPackCount($this, $data);
	}

	/**
	 * Обновление связей между таблицами rls.Drug и rls.DrugRMZ
	 * @param $data
	 * @return bool
	 */
	function updateDrugRMZLink($data)
	{
		return DrugNomen_model_save::updateDrugRMZLink($this, $data);
	}

	/**
	 * Импорт данных справочника ЛП Росздравнадзора из csv файла.
	 * @param $data
	 * @return array
	 */
	function importDrugRMZFromCsv($data)
	{
		return DrugNomen_model_save::importDrugRMZFromCsv($this, $data);
	}

	/**
	 * Добавление кода группировочного торгового наименования по идентификатору медикамента
	 * Если кода для заданого медикамента еще нет, то добавляется новый
	 * Возвращает идентификатор кода
	 * @param $data
	 * @return mixed|null
	 */
	function addDrugPrepFasCodeByDrugId($data)
	{
		return DrugNomen_model_save::addDrugPrepFasCodeByDrugId($this, $data);
	}

	/**
	 * Добавление данных в номенклатурный справочник
	 * $object - наименование сущности
	 * $id - идентификатор сущности
	 * возвращает id записи из таблицы справочника
	 * @param $object
	 * @param $id
	 * @param $data
	 * @return bool|float|int|string|null
	 */
	function addNomenData($object, $id, $data)
	{
		return DrugNomen_model_save::addNomenData($this, $object, $id, $data);
	}

	/**
	 * Проверка уникалности полей при сохранении номенклатурной карточки
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function checkDrugNomen($data)
	{
		return DrugNomen_model_save::checkDrugNomen($this, $data);
	}

	/**
	 * Получение кода
	 * @param $data
	 * @return array|bool
	 */
	function generateCodeForObject($data)
	{
		return DrugNomen_model_save::generateCodeForObject($this, $data);
	}

	/**
	 * Удаление регионального кода МНН
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function deleteDrugMnnCode($data)
	{
		return DrugNomen_model_save::deleteDrugMnnCode($this, $data);
	}

	/**
	 * Удаление записи справочника Количество товара в упаковке ЛП
	 * @param $data
	 * @return array
	 */
	function deleteDrugPrepEdUcCount($data)
	{
		return DrugNomen_model_save::deleteDrugPrepEdUcCount($this, $data);
	}

	/**
	 * Удаление регионального кода Торгового наименования
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function deleteDrugTorgCode($data)
	{
		return DrugNomen_model_save::deleteDrugTorgCode($this, $data);
	}

	/**
	 * Удаление записи справочника Количество товара в упаковке
	 * @param $data
	 * @return array
	 */
	function deleteGoodsPackCount($data)
	{
		return DrugNomen_model_save::deleteGoodsPackCount($this, $data);
	}
	#endregion save
	#region get
	/**
	 * Получение медикамента по номенклатурному коду
	 * @param $data
	 * @return array|bool
	 */
	function getDrugByDrugNomenCode($data)
	{
		return DrugNomen_model_get::getDrugByDrugNomenCode($this, $data);
	}

	/**
	 * Получение единиц измерения
	 * @param $data
	 * @return array|bool
	 */
	function getGoodsUnitData($data)
	{
		return DrugNomen_model_get::getGoodsUnitData($this, $data);
	}

	/**
	 * Получение номенклатурного кода по действующему веществу
	 * @param $data
	 * @return array|bool
	 */
	function getDrugMnnCodeByActMattersId($data)
	{
		return DrugNomen_model_get::getDrugMnnCodeByActMattersId($this, $data);
	}

	/**
	 * Получение регионального кода по Drug_id
	 * @param $data
	 * @return array|bool
	 */
	function getDrugNomenCode($data)
	{
		return DrugNomen_model_get::getDrugNomenCode($this, $data);
	}

	/**
	 * Получение регионального кода для позиции номенклатурного справочника
	 * Получение данных, связанных с Drug_id
	 * @param $data
	 * @return array|bool
	 */
	function getDrugNomenData($data)
	{
		return DrugNomen_model_get::getDrugNomenData($this, $data);
	}

	/**
	 * Формирование фильтров для загрузки грида DrugNomen
	 * @param $data
	 * @return string
	 */
	function getDrugNomenGridFilter($data)
	{
		return DrugNomen_model_get::getDrugNomenGridFilter($this, $data);
	}

	/**
	 * Формирование join'ов для грида DrugNomen
	 * @param $data
	 * @return string
	 */
	function getDrugNomenGridJoin($data)
	{
		return DrugNomen_model_get::getDrugNomenGridJoin($data);
	}

	/**
	 * Получение данных для экспорта остатков и поставок по ОНЛС и ВЗН
	 * @param $data
	 * @return bool
	 */
	function getDrugRMZExportData($data)
	{
		return DrugNomen_model_get::getDrugRMZExportData($this, $data);
	}

	/**
	 * Получение данных для сопоставления данных номенклатурного справочника и справочника ЛР РЗН
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	function getDrugRMZLinkData($data)
	{
		return DrugNomen_model_get::getDrugRMZLinkData($this, $data);
	}

	/**
	 * Получение общей информации о справочнике ЛП Росздравнадзора
	 * @return array|bool
	 */
	function getDrugRMZInformation()
	{
		return DrugNomen_model_get::getDrugRMZInformation($this);
	}

	/**
	 * Читает дерево комплексных услуг
	 * @param $data
	 * @return array|bool
	 */
	function loadPrepClassTree($data)
	{
		return DrugNomen_model_get::loadPrepClassTree($this, $data);
	}

	/**
	 * Получение списка нормативов
	 * @param $data
	 * @return array|bool
	 */
	function loadDrugNomenGrid($data)
	{
		return DrugNomen_model_get::loadDrugNomenGrid($this, $data);
	}

	/**
	 * Получение комбо списка медикаментов (для карты закрытия вызова СМП)
	 * @param $data
	 * @return array|bool
	 */
	function loadDrugNomenCmpDrugUsageCombo($data)
	{
		return DrugNomen_model_get::loadDrugNomenCmpDrugUsageCombo($this, $data);
	}

	/**
	 * Загрузка списка дозировок (nsi)
	 * @param $data
	 * @return array|false
	 */
	function loadNsiDrugDoseCombo($data)
	{
		return DrugNomen_model_get::loadNsiDrugDoseCombo($this, $data);
	}

	/**
	 * Загрузка списка количеств доз в упаковках (nsi)
	 * @param $data
	 * @return array|false
	 */
	function loadNsiDrugKolDoseCombo($data)
	{
		return DrugNomen_model_get::loadNsiDrugKolDoseCombo($this, $data);
	}

	/**
	 * Загрузка формы редактирования
	 * @param $data
	 * @return array|bool
	 */
	function loadDrugNomenEditForm($data)
	{
		return DrugNomen_model_get::loadDrugNomenEditForm($this, $data);
	}

	/**
	 * Загрузка данных справочника ЛП ВЗН
	 * @param $data
	 * @return array|bool
	 */
	function loadDrugVznData($data)
	{
		return DrugNomen_model_get::loadDrugVznData($this, $data);
	}

	/**
	 * Получение списка ОКПД
	 * @param $data
	 * @return array|bool
	 */
	function loadOkpdList($data)
	{
		return DrugNomen_model_get::loadOkpdList($this, $data);
	}

	/**
	 * Загрузка списка кодов РЗН для формы просмотра справочника
	 * @param $data
	 * @return array|bool
	 */
	function loadDrugRMZList($data)
	{
		return DrugNomen_model_get::loadDrugRMZList($this, $data);
	}

	/**
	 * Загрузка списка кодов РЗН для формы поиска
	 * @param $data
	 * @return array|bool
	 */
	function loadDrugRMZListByQuery($data)
	{
		return DrugNomen_model_get::loadDrugRMZListByQuery($this, $data);
	}

	/**
	 * Загрузка регионального кода МНН
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	function loadDrugMnnCode($data)
	{
		return DrugNomen_model_get::loadDrugMnnCode($this, $data);
	}

	/**
	 * Загрузка списка региональных кодов МНН
	 * @param $filter
	 * @return array|bool
	 */
	function loadDrugMnnCodeList($filter)
	{
		return DrugNomen_model_get::loadDrugMnnCodeList($this, $filter);
	}

	/**
	 * Загрузка регионального кода Торгового наименования
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	function loadDrugTorgCode($data)
	{
		return DrugNomen_model_get::loadDrugTorgCode($this, $data);
	}

	/**
	 * Загрузка списка региональных кодов Торговых наименований
	 * @param $filter
	 * @return array|bool
	 */
	function loadDrugTorgCodeList($filter)
	{
		return DrugNomen_model_get::loadDrugTorgCodeList($this, $filter);
	}

	/**
	 * Загрузка списка кодов из таблицы dbo.DrugMnn по имени
	 * @param $filter
	 * @return array|bool
	 */
	function loadDboDrugMnnCodeListByName($filter)
	{
		return DrugNomen_model_get::loadDboDrugMnnCodeListByName($this, $filter);
	}

	/**
	 * Загрузка списка кодов из таблицы dbo.DrugTorg по имени
	 * @param $filter
	 * @return array|bool
	 */
	function loadDboDrugTorgCodeListByName($filter)
	{
		return DrugNomen_model_get::loadDboDrugTorgCodeListByName($this, $filter);
	}

	/**
	 * Загрузка списка форм выпуска ЛС ВЗН
	 * @param $filter
	 * @return array|bool
	 */
	function loadDrugFormMnnVZNCombo($filter)
	{
		return DrugNomen_model_get::loadDrugFormMnnVZNCombo($this, $filter);
	}

	/**
	 * Получение списка номенклатур медикаментов
	 * @param $data
	 * @return array|false
	 */
	function loadDrugNomenList($data)
	{
		return DrugNomen_model_get::loadDrugNomenList($this, $data);
	}

	/**
	 * Получение списка количества товара в упаковке
	 * @param $data
	 * @return array|false
	 */
	function loadGoodsPackCountList($data)
	{
		return DrugNomen_model_get::loadGoodsPackCountList($this, $data);
	}

	/**
	 * Получение списка количества товара в упаковке для грида в номенклатурной карточке
	 * @param $data
	 * @return array|false
	 */
	function loadGoodsPackCountListGrid($data)
	{
		return DrugNomen_model_get::loadGoodsPackCountListGrid($this, $data);
	}

	/**
	 * Получение списка количества товара в упаковке для грида в номенклатурной карточке
	 * @param $data
	 * @return array|false
	 */
	function loadDrugPrepEdUcCountListGrid($data)
	{
		return DrugNomen_model_get::loadDrugPrepEdUcCountListGrid($this, $data);
	}
	#endregion get
}