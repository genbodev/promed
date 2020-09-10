<?php
require_once("EmergencyTeam_model4E_get.php");
require_once("EmergencyTeam_model4E_load.php");
require_once("EmergencyTeam_model4E_save.php");
require_once("EmergencyTeam_model4E_common.php");
/**
 * Class EmergencyTeam_Model4E
 *
 * @property Wialon_Model $wmodel
 * @property CmpCallCard_model4E $CmpCallCard_model4E
 * @property LpuStructure_model $LpuStructure
 * @property LpuPassport_model $LpuPassport
 * @property CmpCallCard_model4E $cccmodel
 * @property TNC_model $tncmodel
 * @property CmpCallCard_model $CmpCallCard_model
 * @property CmpCallCard_model4E $cardModel
 * @property CI_DB_driver $db
 */
class EmergencyTeam_model4E extends swPgModel
{
	public $dateTimeForm104 = "DD.MM.YYYY";
	public $dateTimeForm108 = "HH24:MI:SS";
	public $dateTimeForm113 = "DD mon YYYY HH24:MI:SS:MS";
	public $dateTimeForm126 = "YYYY-MM-DDT HH24:MI:SS:MS";
	public $dateTimeForm120 = "YYYY-MM-DD HH24:MI:SS";
	public $dateTimeFormUnixDate = "YYYY-MM-DD";

	public $numericForm18_2 = "FM999999999999999999.00";

	#region get
	/**
	 * Возвращает список бригад которым пора со смены (закончилось плановое время)
	 * @param $data
	 * @param $where
	 * @param $params
	 * @return array|bool
	 */
	public function getAutoFinishVigil($data, $where, $params)
	{
		return EmergencyTeam_model4E_get::getAutoFinishVigil($this, $data, $where, $params);
	}

	/**
	 * Возвращает список бригад которым пора на смену (началось плановое время)
	 * @param $data
	 * @param $where
	 * @param $params
	 * @return array|bool
	 */
	public function getAutoStartVigil($data, $where, $params)
	{
		return EmergencyTeam_model4E_get::getAutoStartVigil($this, $data, $where, $params);
	}

	/**
	 * Возвращает последний статус текущего вызова на бригаде
	 * @param $data
	 * @return array|CI_DB_result
	 */
	public function getCallOnEmergencyTeam($data)
	{
		return EmergencyTeam_model4E_get::getCallOnEmergencyTeam($this, $data);
	}

	/**
	 * Получение параметров для проигрывания трека виалон
	 * @param $data
	 * @return array
	 */
	public function getCmpCallCardTrackPlayParams($data)
	{
		return EmergencyTeam_model4E_get::getCmpCallCardTrackPlayParams($this, $data);
	}

	/**
	 * Получение времени назначения статуса карты вызова "принято"
	 * @param $param
	 * @return bool|mixed
	 */
	public function _getCmpCallCardStartTime($param)
	{
		return EmergencyTeam_model4E_get::_getCmpCallCardStartTime($this, $param);
	}

	/**
	 * Получение времени завершения вызова
	 * @param $param
	 * @return bool|mixed
	 */
	public function _getCmpCallCardEndTime($param)
	{
		return EmergencyTeam_model4E_get::_getCmpCallCardEndTime($this, $param);
	}

	/**
	 * Получает идентификатор автомобиля в Виалон по идентификатору бригады
	 * @param null $EmergencyTeam_id
	 * @return bool|mixed
	 */
	public function _getEmergencyTeamGeoserviveTransportId($EmergencyTeam_id = null)
	{
		return EmergencyTeam_model4E_get::_getEmergencyTeamGeoserviveTransportId($this, $EmergencyTeam_id);
	}

	/**
	 * получение идентификатор автомобиля в Виалон из защищенного метода  _getEmergencyTeamGeoserviveTransportId
	 * @param $param
	 * @return bool
	 */
	public function getEmergencyTeamGeoserviveTransportId($param)
	{
		return EmergencyTeam_model4E_get::getEmergencyTeamGeoserviveTransportId($this, $param);
	}

	/**
	 * Получает время передачи вызова на бригаду
	 * @param null $CmpCallCard_id
	 * @return bool|mixed
	 */
	public function _getCmpCallCardPassToEmergencyTeamTimestamp($CmpCallCard_id = null)
	{
		return EmergencyTeam_model4E_get::_getCmpCallCardPassToEmergencyTeamTimestamp($this, $CmpCallCard_id);
	}

	/**
	 * Получает время окончания вызова бригадой
	 * @param null $CmpCallCard_id
	 * @param null $EmergencyTeam_id
	 * @return int
	 */
	public function _getCmpCallCardEndTimestamp($CmpCallCard_id = null, $EmergencyTeam_id = null)
	{
		return EmergencyTeam_model4E_get::_getCmpCallCardEndTimestamp($this, $CmpCallCard_id, $EmergencyTeam_id);
	}

	/**
	 * Метод получения идентификатора укладки по идентификатору движения укладки
	 * @param $data
	 * @return array|false
	 */
	protected function _getDrugPackByDrugPackMove($data)
	{
		return EmergencyTeam_model4E_get::_getDrugPackByDrugPackMove($this, $data);
	}

	/**
	 * Метод получения идентификатора смены по идентификатору бригады
	 * @param $data
	 * @return array|false
	 */
	protected function _getEmergencyTeamDutyIdByEmergencyTeamId($data)
	{
		return EmergencyTeam_model4E_get::_getEmergencyTeamDutyIdByEmergencyTeamId($this, $data);
	}

	/**
	 * Возвращает количество кол-во врачей, бригад, вызовов СМП для арма ЦМК
	 * Для списка подчиненных подстанций СМП
	 * @param $data
	 * @return array|bool
	 */
	public function getCountsTeamsCallsAndDocsARMCenterDisaster($data)
	{
		return EmergencyTeam_model4E_get::getCountsTeamsCallsAndDocsARMCenterDisaster($this, $data);
	}

	/**
	 * Номер телефона по умолчанию для наряда
	 * @param $data
	 * @return array|bool
	 */
	public function getDefaultPhoneNumber($data)
	{
		return EmergencyTeam_model4E_get::getDefaultPhoneNumber($this, $data);
	}

	/**
	 * Возвращает массив ID МО выбранных в АРМ
	 * @return array|bool
	 */
	public function getSelectedLpuId()
	{
		return EmergencyTeam_model4E_get::getSelectedLpuId();
	}

	/**
	 * Информация о бригаде в АРМе ЦМК
	 * @param $data
	 * @return array
	 */
	function getEmergencyTeam($data)
	{
		return EmergencyTeam_model4E_get::getEmergencyTeam($this, $data);
	}

	/**
	 * Получение суммарное количество медикамента по всем операциям списания и зачисления
	 * @param $data
	 * @return array|false
	 */
	public function getDrugCountFromDrugPackMoveByDrugPackId($data)
	{
		return EmergencyTeam_model4E_get::getDrugCountFromDrugPackMoveByDrugPackId($this, $data);
	}

	/**
	 * Метод получения идентификатора движения медикамента для наряда СМП по идентификатору строки учетного документа списания
	 * @param $data
	 * @return array|false
	 */
	public function getEmergencyTeamDrugPackMoveIdByDocumentUcStr($data)
	{
		return EmergencyTeam_model4E_get::getEmergencyTeamDrugPackMoveIdByDocumentUcStr($this, $data);
	}

	/**
	 * Получение записи остатков по идентификатору бригады и медикамента
	 * @param $data
	 * @return array|false
	 */
	protected function getDrugPackByDrugAndEmergencyTeam($data)
	{
		return EmergencyTeam_model4E_get::getDrugPackByDrugAndEmergencyTeam($this, $data);
	}

	/**
	 * Список бригад для комбобокса
	 * @param $data
	 * @return array|bool
	 */
	public function getEmergencyTeamCombo($data)
	{
		return EmergencyTeam_model4E_get::getEmergencyTeamCombo($this, $data);
	}

	/**
	 * я не знаю что за функция, но от меня требуют ее описание
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function getEmergencyTeamProposalLogic($data)
	{
		return EmergencyTeam_model4E_get::getEmergencyTeamProposalLogic($this, $data);
	}

	/**
	 * я не знаю что за функция, но от меня требуют ее описание
	 * @param $data
	 * @return array|bool
	 */
	function getEmergencyTeamProposalLogicRuleSpecSequence($data)
	{
		return EmergencyTeam_model4E_get::getEmergencyTeamProposalLogicRuleSpecSequence($this, $data);
	}

	/**
	 * Возвращает идентификатор из справочника статусов бригад по его коду
	 * @param $id
	 * @return bool|mixed
	 */
	public function getEmergencyTeamStatusCodeById($id)
	{
		return EmergencyTeam_model4E_get::getEmergencyTeamStatusCodeById($this, $id);
	}

	/**
	 * Возвращает идентификатор из справочника статусов бригад по его коду
	 * @param $code
	 * @return bool|mixed
	 */
	public function getEmergencyTeamStatusIdByCode($code)
	{
		return EmergencyTeam_model4E_get::getEmergencyTeamStatusIdByCode($this, $code);
	}

	/**
	 * Получаем список EmergencyTeam_TemplateName
	 * @param $data
	 * @return array|bool
	 */
	public function getEmergencyTeamTemplatesNames($data)
	{
		return EmergencyTeam_model4E_get::getEmergencyTeamTemplatesNames($this, $data);
	}

	/**
	 * Получение подчин. подстанций для запроса
	 * @param $data
	 * @return array|string
	 * @throws Exception
	 */
	public function getNestedLpuBuildingsForRequests($data)
	{
		return EmergencyTeam_model4E_get::getNestedLpuBuildingsForRequests($this, $data);
	}
	#endregion get
	#region load
	/**
	 * Возвращает данные по оперативной обстановке бригад СМП для арма ЦМК
	 * Для списка подчиненных подстанций СМП
	 * @param $data
	 * @return array|bool
	 */
	public function loadCmpCallCardsARMCenterDisaster($data)
	{
		return EmergencyTeam_model4E_load::loadCmpCallCardsARMCenterDisaster($this, $data);
	}

	/**
	 * Возвращает данные для раздела "Наряды"
	 * @param $data
	 * @return array|bool
	 */
	public function loadOutfitsARMCenterDisaster($data)
	{
		return EmergencyTeam_model4E_load::loadOutfitsARMCenterDisaster($this, $data);
	}

	/**
	 * Возвращает массив ID тех подразделений СМП, которые были выбраны  пользователем при входе в АРМ
	 * @return bool|mixed
	 */
	public function loadIdSelectSmp()
	{
		return EmergencyTeam_model4E_load::loadIdSelectSmp();
	}

	/**
	 * Получение оперативной обстановки по диспетчерам СМП
	 * @param $data
	 * @return array|bool
	 */
	function loadDispatchOperEnv($data)
	{
		return EmergencyTeam_model4E_load::loadDispatchOperEnv($this, $data);
	}

	/**
	 * Возвращает укладку
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function loadEmergencyTeamDrugsPack($data)
	{
		return EmergencyTeam_model4E_load::loadEmergencyTeamDrugsPack($this, $data);
	}

	/**
	 * Возвращает данные указанной бригады
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function loadEmergencyTeam($data)
	{
		return EmergencyTeam_model4E_load::loadEmergencyTeam($this, $data);
	}

	/**
	 * Возвращает данные автомобилей, заведенных в паспорте мо
	 * @param $data
	 * @return array|bool|CI_DB_result
	 * @throws Exception
	 */
	public function loadEmergencyTeamList($data)
	{
		return EmergencyTeam_model4E_load::loadEmergencyTeamList($this, $data);
	}

	/**
	 * Возвращает данные всех бригад ЛПУ c сегодняшнего дня
	 * @param $data
	 * @return array|bool
	 */
	public function loadEmergencyTeamShiftList($data)
	{
		return EmergencyTeam_model4E_load::loadEmergencyTeamShiftList($this, $data);
	}

	/**
	 * Возращает список для справочника списка бригад СМП
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function loadEmergencyTeamCombo($data)
	{
		return EmergencyTeam_model4E_load::loadEmergencyTeamCombo($this, $data);
	}

	/**
	 * Возращает список для справочника списка бригад СМП
	 * @param $data
	 * @return array|false
	 */
	public function loadEmergencyTeamComboWithWialonID($data)
	{
		return EmergencyTeam_model4E_load::loadEmergencyTeamComboWithWialonID($this, $data);
	}

	/**
	 * Получение списка смен указанной бригады для графика нарядов
	 * @param $data
	 * @return array|bool
	 */
	public function loadEmergencyTeamDutyTimeGrid($data)
	{
		return EmergencyTeam_model4E_load::loadEmergencyTeamDutyTimeGrid($this, $data);
	}

	/**
	 * Получение списка смен бригад СМП указанной ЛПУ для графика нарядов
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function loadEmergencyTeamDutyTimeListGrid($data)
	{
		return EmergencyTeam_model4E_load::loadEmergencyTeamDutyTimeListGrid($this, $data);
	}

	/**
	 * Получение оперативной обстановки по бригадам СМП
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function loadEmergencyTeamOperEnv($data)
	{
		return EmergencyTeam_model4E_load::loadEmergencyTeamOperEnv($this, $data);
	}

	/**
	 * Возвращает данные по оперативной обстановке бригад СМП
	 * С возможность фильтрации по подстанции
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function loadEmergencyTeamOperEnvForSmpUnit($data)
	{
		return EmergencyTeam_model4E_load::loadEmergencyTeamOperEnvForSmpUnit($this, $data);
	}

	/**
	 * Возвращает данные по оперативной обстановке бригад СМП
	 * Для списка подчиненных подстанций СМП
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function loadEmergencyTeamOperEnvForSmpUnitsNested($data)
	{
		return EmergencyTeam_model4E_load::loadEmergencyTeamOperEnvForSmpUnitsNested($this, $data);
	}

	/**
	 * Список бригад для АРМ Интерактивной карты СМП
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function loadEmergencyTeamOperEnvForInteractiveMap($data)
	{
		return EmergencyTeam_model4E_load::loadEmergencyTeamOperEnvForInteractiveMap($this, $data);
	}

	/**
	 * Возвращает данные по оперативной обстановке бригад СМП для арма ЦМК
	 * Для списка подчиненных подстанций СМП
	 * @param $data
	 * @return array|bool
	 */
	public function loadEmergencyTeamsARMCenterDisaster($data)
	{
		return EmergencyTeam_model4E_load::loadEmergencyTeamsARMCenterDisaster($this, $data);
	}

	/**
	 * Загрузка возможных статусов бригады
	 * @param $data
	 * @return array|bool
	 */
	public function loadEmergencyTeamStatuses($data)
	{
		return EmergencyTeam_model4E_load::loadEmergencyTeamStatuses($this, $data);
	}

	/**
	 * Загрузка истории статусов бригады
	 * @param $data
	 * @return array|bool
	 */
	public function loadEmergencyTeamStatusesHistory($data)
	{
		return EmergencyTeam_model4E_load::loadEmergencyTeamStatusesHistory($this, $data);
	}

	/**
	 * Возвращает данные шаблонов бригад
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function loadEmergencyTeamTemplateList($data)
	{
		return EmergencyTeam_model4E_load::loadEmergencyTeamTemplateList($this, $data);
	}

	/**
	 * Получение списка бригад с незавершёнными сменами
	 * @param $data
	 * @return array|bool|false
	 */
	public function loadUnfinishedEmergencyTeamList($data)
	{
		return EmergencyTeam_model4E_load::loadUnfinishedEmergencyTeamList($this, $data);
	}

	/**
	 * Получение дежурств наряда бригады СМП
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function loadEmergencyTeamVigils($data)
	{
		return EmergencyTeam_model4E_load::loadEmergencyTeamVigils($this, $data);
	}

	/**
	 * Получение информации о дежурстве наряда бригады СМП
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function loadSingleEmergencyTeamVigil($data)
	{
		return EmergencyTeam_model4E_load::loadSingleEmergencyTeamVigil($this, $data);
	}
	#endregion load
	#region save
	/**
	 * Сохранение бригады СМП
	 * @param $data
	 * @param bool $update_duty_time
	 * @return array|bool|false
	 * @throws Exception
	 */
	public function saveEmergencyTeam($data, $update_duty_time = true)
	{
		return EmergencyTeam_model4E_save::saveEmergencyTeam($this, $data, $update_duty_time);
	}

	/**
	 * Сохранения связи наряда и транспорта геосервиса
	 * @param $data
	 * @return array
	 */
	protected function _saveEmergencyTeamGeoserviceTransportRel($data)
	{
		return EmergencyTeam_model4E_save::_saveEmergencyTeamGeoserviceTransportRel($this, $data);
	}

	/**
	 * Сохраняет бригады
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public function saveEmergencyTeams($data)
	{
		return EmergencyTeam_model4E_save::saveEmergencyTeams($this, $data);
	}

	/**
	 * Актуализация остатков медикамента в укладке СМП
	 * @param $data
	 * @return array|false
	 */
	public function saveEmergencyTeamDrugPack($data)
	{
		return EmergencyTeam_model4E_save::saveEmergencyTeamDrugPack($this, $data);
	}

	/**
	 * Метод сохранения прихода медикамента на укладку наряда СМП
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	public function saveEmergencyTeamDrugPackMove($data)
	{
		return EmergencyTeam_model4E_save::saveEmergencyTeamDrugPackMove($this, $data);
	}

	/**
	 * Сохраняет время смен бригад
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function saveEmergencyTeamDutyTime($data)
	{
		return EmergencyTeam_model4E_save::saveEmergencyTeamDutyTime($this, $data);
	}

	/**
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function saveEmergencyTeamProposalLogicRule($data)
	{
		return EmergencyTeam_model4E_save::saveEmergencyTeamProposalLogicRule($this, $data);
	}

	/**
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function saveEmergencyTeamProposalLogicRuleSequence($data)
	{
		return EmergencyTeam_model4E_save::saveEmergencyTeamProposalLogicRuleSequence($this, $data);
	}

	/**
	 * Сохранение дежурства бригады
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function saveEmergencyTeamVigil($data)
	{
		return EmergencyTeam_model4E_save::saveEmergencyTeamVigil($this, $data);
	}

	/**
	 * Сохраняет заданную дату и время начала и окончания смены
	 * @param $data
	 * @return bool|mixed
	 * @throws Exception
	 */
	public function editEmergencyTeamDutyTime($data)
	{
		return EmergencyTeam_model4E_save::editEmergencyTeamDutyTime($this, $data);
	}

	/**
	 * Редактирование времени дежурства бригады
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function editEmergencyTeamVigilTimes($data)
	{
		return EmergencyTeam_model4E_save::editEmergencyTeamVigilTimes($this, $data);
	}

	/**
	 * Отмечает закрытие смен бригад СМП
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function setEmergencyTeamsCloseList($data)
	{
		return EmergencyTeam_model4E_save::setEmergencyTeamsCloseList($this, $data);
	}

	/**
	 * Изменяет статус бригады СМП
	 * @param $data
	 * @return array|bool
	 */
	public function setEmergencyTeamStatus($data)
	{
		return EmergencyTeam_model4E_save::setEmergencyTeamStatus($this, $data);
	}

	/**
	 * Установка временных параметров карты в зависимости от статуса бригады вызова
	 * @param $data
	 * @return bool
	 */
	public function setTimesCardFromEmergencyTeam($data)
	{
		return EmergencyTeam_model4E_save::setTimesCardFromEmergencyTeam($this, $data);
	}

	/**
	 * Отмечает выход на смену бригады СМП
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function setEmergencyTeamsWorkComingList($data)
	{
		return EmergencyTeam_model4E_save::setEmergencyTeamsWorkComingList($this, $data);
	}

	/**
	 * Отмечает выход на смену бригады СМП
	 * @param $data
	 * @return mixed
	 * @throws Exception
	 */
	public function setEmergencyTeamWorkComing($data)
	{
		return EmergencyTeam_model4E_save::setEmergencyTeamWorkComing($this, $data);
	}
	#endregion save
	#region common
	/**
	 * Удаляет бригаду СМП
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function deleteEmergencyTeam($data)
	{
		return EmergencyTeam_model4E_common::deleteEmergencyTeam($this, $data);
	}

	/**
	 * Удаление списка бригад
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function deleteEmergencyTeamList($data)
	{
		return EmergencyTeam_model4E_common::deleteEmergencyTeamList($this, $data);
	}

	/**
	 * Удаляет заданную дату и время начала и окончания смены
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function deleteEmergencyTeamDutyTime($data)
	{
		return EmergencyTeam_model4E_common::deleteEmergencyTeamDutyTime($this, $data);
	}

	/**
	 * Удаляет заданную дату и время начала и окончания у смен
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public function deleteEmergencyTeamDutyTimeList($data)
	{
		return EmergencyTeam_model4E_common::deleteEmergencyTeamDutyTimeList($this, $data);
	}

	/**
	 * Метод удаления записи о приходе
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	public function deleteEmergencyTeamPackMove($data)
	{
		return EmergencyTeam_model4E_common::deleteEmergencyTeamPackMove($this, $data);
	}

	/**
	 * Метод удаления движения медикамента на укладку наряда СМП
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	public function deleteEmergencyTeamPackMoveByDocumentUcStr($data)
	{
		return EmergencyTeam_model4E_common::deleteEmergencyTeamPackMoveByDocumentUcStr($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function deleteEmergencyTeamProposalLogicRule($data)
	{
		return EmergencyTeam_model4E_common::deleteEmergencyTeamProposalLogicRule($this, $data);
	}

	/**
	 * @param $data
	 * @throws Exception
	 */
	public function deleteEmergencyTeamProposalLogicRuleSequence($data)
	{
		EmergencyTeam_model4E_common::deleteEmergencyTeamProposalLogicRuleSequence($data);
	}

	/**
	 * Удаление дежурства бригады
	 * @param $data
	 * @return array|bool
	 */
	public function deleteEmergencyTeamVigil($data)
	{
		return EmergencyTeam_model4E_common::deleteEmergencyTeamVigil($this, $data);
	}

	/**
	 * Изменяет статус бригады СМП на предыдущий у вызова
	 * @param $data
	 * @return array|bool|false
	 */
	public function cancelEmergencyTeamFromCall($data)
	{
		return EmergencyTeam_model4E_common::cancelEmergencyTeamFromCall($this, $data);
	}

	/**
	 * Проверка превышения времени обеда бригады
	 * @param $data
	 * @return array|bool
	 */
	public function checkLunchTimeOut($data)
	{
		return EmergencyTeam_model4E_common::checkLunchTimeOut($this, $data);
	}

	/**
	 * Проверяем закрыта ли бригада
	 * @param $EmergencyTeam_id
	 * @return bool
	 * @throws Exception
	 */
	public function checkOpenEmergencyTeam($EmergencyTeam_id)
	{
		return EmergencyTeam_model4E_common::checkOpenEmergencyTeam($this, $EmergencyTeam_id);
	}

	/**
	 * Проверка на активное место работы сотрудника бригады
	 * @param $data
	 * @return bool
	 */
	public function checkActiveMedStaffFact($data)
	{
		return EmergencyTeam_model4E_common::checkActiveMedStaffFact($this, $data);
	}

	/**
	 * Метод получения полей и объектов БД для идентификаторов транспортных средств в сторонних сервисах
	 * @param array $data
	 * @return array
	 */
	public function _defineGeoserviceTransportRelQueryParams($data = [])
	{
		return EmergencyTeam_model4E_common::_defineGeoserviceTransportRelQueryParams();
	}

	/**
	 * Проверка на автомобиль - не просрочен ли он
	 * @param $data
	 * @return array|bool
	 */
	public function checkCarByDate($data)
	{
		return EmergencyTeam_model4E_common::checkCarByDate($this, $data);
	}

	/**
	 * Проверка на автомобиль - не задействован ли он на данное время
	 * @param $data
	 * @return array|bool
	 */
	public function checkCarByDutyDate($data)
	{
		return EmergencyTeam_model4E_common::checkCarByDutyDate($this, $data);
	}

	/**
	 * Проверка не состоит ли врач в другой смене.
	 * @param $data
	 * @return array|bool
	 */
	public function checkMedPersonalBusy($data)
	{
		return EmergencyTeam_model4E_common::checkMedPersonalBusy($this, $data);
	}
	#endregion common
}