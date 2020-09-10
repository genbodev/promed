<?php
require_once("Polka_PersonDisp_model_get.php");
require_once("Polka_PersonDisp_model_load.php");
require_once("Polka_PersonDisp_model_save.php");
require_once("Polka_PersonDisp_model_common.php");
/**
 * Polka_PersonDisp_model - модель, для работы с таблицей PersonDisp
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
 * @version      30.06.2009
 *
 * @property CI_DB_driver $db
 * @property CI_DB_driver $dbmodel
  */

class Polka_PersonDisp_model extends swPgModel
{
	public $dateTimeForm104 = "DD.MM.YYYY";
	public $dateTimeFormUnixDate = "YYYY-MM-DD";

	function __construct()
	{
		parent::__construct();
	}

	#region get
	/**
	 * Возвращает список диспансерных карт по заданным фильтрам
	 * @param $data
	 * @return array|bool
	 */
	function getPersonDispHistoryList($data)
	{
		return Polka_PersonDisp_model_get::getPersonDispHistoryList($this, $data);
	}

	/**
	 * Возвращает список медикаментов по заданным фильтрам
	 * @param $PersonDisp_id
	 * @return array|bool
	 */
	function getPersonDispMedicamentList($PersonDisp_id)
	{
		return Polka_PersonDisp_model_get::getPersonDispMedicamentList($this, $PersonDisp_id);
	}

	/**
	 * Возвращает количество медикаментов по заданным фильтрам
	 * @param $PersonDisp_id
	 * @return array|bool
	 */
	function getPersonDispMedicamentCount($PersonDisp_id)
	{
		return Polka_PersonDisp_model_get::getPersonDispMedicamentCount($this, $PersonDisp_id);
	}

	/**
	 * @param $data
	 * @return array
	 */
	function getPersonDispNumber($data)
	{
		return Polka_PersonDisp_model_get::getPersonDispNumber($this, $data);
	}

	/**
	 * Возвращает список карт по заданным фильтрам из дерева
	 * @param $data
	 * @return array|bool
	 */
	function getPersonDispListByTree($data)
	{
		return Polka_PersonDisp_model_get::getPersonDispListByTree($this, $data);
	}

	/**
	 * @return string
	 */
	function getVizitTypeSysNick()
	{
		return Polka_PersonDisp_model_get::getVizitTypeSysNick();
	}

	/**
	 * Получение данных по дисп. учету человека для панели просмотра сигнальной информации ЭМК
	 * @param $data
	 * @return array|bool
	 */
	function getPersonDispViewData($data)
	{
		return Polka_PersonDisp_model_get::getPersonDispViewData($this, $data);
	}

	/**
	 * Список карт д-учёта, подходящих для связи с записью регистра
	 */
	function loadMorbusOnkoSelectList($data) {
		
		$data['Diag_pid'] = $this->getFirstResultFromQuery("
			select Diag_pid 
			from v_PersonRegister PR
			inner join Diag D on D.Diag_id = PR.Diag_id
			where PR.PersonRegister_id = :PersonRegister_id
		", $data);
		
		if (!$data['Diag_pid']) return [];
		
		$query = "
			select
				PD.PersonDisp_id as \"PersonDisp_id\"
				,to_char(PD.PersonDisp_begDate, '{$this->dateTimeForm104}') as \"PersonDisp_begDate\"
				,coalesce(MP.Person_Fin, '') as \"MedPersonal_Fio\"
				,coalesce(MPH.Person_Fin, '') as \"MedPersonalH_Fio\"
				,L.Lpu_Nick as \"Lpu_Nick\"
			from
				v_PersonDisp PD
				inner join Diag D on D.Diag_id = PD.Diag_id
				inner join v_Lpu L on L.Lpu_id = PD.Lpu_id
				left join lateral (
					select Person_Fin
					from v_MedPersonal
					where MedPersonal_id = PD.MedPersonal_id
					limit 1
				) MP on true
				left join lateral (
					select mpp.Person_Fin
					from v_PersonDispHist pdh
					left join v_MedPersonal mpp on mpp.MedPersonal_id = pdh.MedPersonal_id
					where PersonDisp_id = PD.PersonDisp_id and (
						(pdh.PersonDispHist_begDate <= dbo.tzGetDate() and PDH.PersonDispHist_endDate is null)
							or
						(PDH.PersonDispHist_begDate <= dbo.tzGetDate() and PDH.PersonDispHist_endDate >= dbo.tzGetDate()) 
					)
					limit 1
				) MPH on true
			where
				PD.Person_id = :Person_id
				and D.Diag_pid = :Diag_pid
				and PD.PersonDisp_endDate is null
				and not exists (
					select PersonDisp_id 
					from PersonRegisterDispLink PRDL 
					where 
						PRDL.PersonDisp_id = PD.PersonDisp_id and
						PRDL.PersonRegister_id = :PersonRegister_id
					limit 1
				)
			order by
				PD.PersonDisp_begDate desc
		";
		
		return $this->queryResult($query, $data);
	}

	/**
	 * Cвязь с записью регистра
	 */
	function savePersonRegisterDispLink($data) {
		
		return $this->execCommonSP('p_PersonRegisterDispLink_ins', [
			'PersonRegisterDispLink_id' => ['value' => null, 'out' => true, 'type' => 'bigint'],
			'PersonRegister_id' => $data['PersonRegister_id'],
			'PersonDisp_id' => $data['PersonDisp_id'],
			'pmUser_id' => $data['pmUser_id']
		]);
	}

	/**
	 * Получение данных по дисп. учету человека для панели просмотра сигнальной информации ЭМК
	 * @param $data
	 * @return array|bool
	 */
	function getMorbusOnkoPersonDispViewData($data)
	{
		return Polka_PersonDisp_model_get::getMorbusOnkoPersonDispViewData($this, $data);
	}

	/**
	 * Получение данных по дисп. учету человека при открытии ЭМК #12461
	 * @param $data
	 * @return array|bool
	 */
	function getPersonDispSignalViewData($data)
	{
		return Polka_PersonDisp_model_get::getPersonDispSignalViewData($this, $data);
	}

	/**
	 * Возвращает список диспансерных карт по заданным фильтрам
	 * @param $data
	 * @return array|bool
	 */
	function getPersonDispHistoryListForPrint($data)
	{
		return Polka_PersonDisp_model_get::getPersonDispHistoryListForPrint($this, $data);
	}

	/**
	 * Возвращает список адресов пациента
	 * @param $data
	 * @return array|bool
	 */
	function getPersonDispHistoryListAdresses($data)
	{
		return Polka_PersonDisp_model_get::getPersonDispHistoryListAdresses($this, $data);
	}

	/**
	 * ДМ: Количество меток
	 * @param $data
	 * @return array
	 */
	function getPersonLabelCounts($data)
	{
		return Polka_PersonDisp_model_get::getPersonLabelCounts($this, $data);
	}

	/**
	 * ДМ: Получение информации о пациенте и его карте наблюдения
	 * Используется: Дистанционный мониторинг (RemoteMonitoringWindow.js)
	 * @param $data
	 * @return array|bool
	 */
	function getPersonChartInfo($data)
	{
		return Polka_PersonDisp_model_get::getPersonChartInfo($this, $data);
	}

	/**
	 * ДМ: Получить данные пациента с портала и мобильного приложения
	 * Используется: приглашение в Дистанционный мониторинг
	 * @param $data
	 * @return array
	 */
	function getPersonDataFromPortal($data)
	{
		return Polka_PersonDisp_model_get::getPersonDataFromPortal($this, $data);
	}

	/**
	 * ДМ: Количество замеров после даты
	 * Используется: перед исключением из программы мониторинга
	 * @param $data
	 * @return bool|float|int|string
	 */
	function getMeasuresNumberAfterDate($data)
	{
		return Polka_PersonDisp_model_get::getMeasuresNumberAfterDate($this, $data);
	}

	/**
	 * Получить контакты кабинета здоровья в подразделении
	 * @param $data
	 * @return bool|mixed
	 */
	function getLpuBuildingHealth($data)
	{
		return Polka_PersonDisp_model_get::getLpuBuildingHealth($this, $data);
	}

	/**
	 * Получить правильно отформатированный номер телефона
	 * @param $number
	 * @return string
	 */
	function getPhoneNumber($number)
	{
		return Polka_PersonDisp_model_get::getPhoneNumber($number);
	}

	/**
	 * Получить информацию по мониторингу температуры пациента
	 * @param $data
	 * @return array|bool
	 */
	function getMonitorTemperatureInfo($data)
	{
		return Polka_PersonDisp_model_get::getMonitorTemperatureInfo($this, $data);
	}

	/**
	 * Получить список открытых карт наблюдения по пациенту
	 * Используется: вкладка "мониторинг" в ЭМК
	 * @param $data
	 * @return array|bool
	 */
	function getLabelObserveCharts($data)
	{
		return Polka_PersonDisp_model_get::getLabelObserveCharts($this, $data);
	}

	/**
	 * наличие диспансерной карты с причиной снятия Смерть
	 * @param $data
	 * @return bool|int
	 */
	function getAvailabilityDispensaryCardCauseDeath($data)
	{
		return Polka_PersonDisp_model_get::getAvailabilityDispensaryCardCauseDeath($this, $data);
	}
	#endregion get
	#region load
	/**
	 * Возвращает список диагнозов по заданным фильтрам
	 * @param $Diag_pid
	 * @return array|bool
	 */
	function loadDiagList($Diag_pid)
	{
		return Polka_PersonDisp_model_load::loadDiagList($this, $Diag_pid);
	}

	/**
	 * Получение данных для редактирования
	 * @param $data
	 * @return array|bool
	 */
	function loadPersonDispEditForm($data)
	{
		return Polka_PersonDisp_model_load::loadPersonDispEditForm($this, $data);
	}

	/**
	 * Получение категорий регистра заболеваний
	 * @return array|bool
	 */
	function loadSicknessList()
	{
		return Polka_PersonDisp_model_load::loadSicknessList($this);
	}

	/**
	 * Получение данных для грида
	 * @param $data
	 * @return array|bool
	 */
	function loadPersonDispGrid($data)
	{
		return Polka_PersonDisp_model_load::loadPersonDispGrid($this, $data);
	}

	/**
	 * Получение истории изменений диагонозов карты ДУ
	 * @param $data
	 * @return array|bool
	 */
	function loadDiagDispCardHistory($data)
	{
		return Polka_PersonDisp_model_load::loadDiagDispCardHistory($this, $data);
	}

	/**
	 * Загрузыка данных для формы "Диагноз в карте ДУ"
	 * @param $data
	 * @return array|bool
	 */
	function loadDiagDispCardEditForm($data)
	{
		return Polka_PersonDisp_model_load::loadDiagDispCardEditForm($this, $data);
	}

	/**
	 * Загрузка списка Контроля посещений
	 * @param $data
	 * @return array|bool
	 */
	function loadPersonDispVizitList($data)
	{
		return Polka_PersonDisp_model_load::loadPersonDispVizitList($this, $data);
	}

	/**
	 * Загрузка Контроля посещений
	 * @param $data
	 * @return array|bool
	 */
	function loadPersonDispVizit($data)
	{
		return Polka_PersonDisp_model_load::loadPersonDispVizit($this, $data);
	}

	/**
	 * Загрузка списка Сопутствующих диагнозов
	 * @param $data
	 * @return array|bool
	 */
	function loadPersonDispSopDiaglist($data)
	{
		return Polka_PersonDisp_model_load::loadPersonDispSopDiaglist($this, $data);
	}

	/**
	 * Загрузка Сопутствующих диагнозов
	 * @param $data
	 * @return array|bool
	 */
	function loadPersonDispSopDiag($data)
	{
		return Polka_PersonDisp_model_load::loadPersonDispSopDiag($this, $data);
	}

	/**
	 * Загрузка списка врачей, ответственных за наблюдение
	 * @param $data
	 * @return array|bool
	 */
	function loadPersonDispHistlist($data)
	{
		return Polka_PersonDisp_model_load::loadPersonDispHistlist($this, $data);
	}

	/**
	 * Загрузка Отвественного врача
	 * @param $data
	 * @return array|bool
	 */
	function loadPersonDispHist($data)
	{
		return Polka_PersonDisp_model_load::loadPersonDispHist($this, $data);
	}

	/**
	 * Загрузка списка Целевых показателей
	 * @param $data
	 * @return array|bool|CI_DB_result|mixed
	 */
	function loadPersonDispTargetRateList($data)
	{
		return Polka_PersonDisp_model_load::loadPersonDispTargetRateList($this, $data);
	}

	/**
	 * Загрузка Целевых показателей
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	function loadPersonDispTargetRate($data)
	{
		return Polka_PersonDisp_model_load::loadPersonDispTargetRate($this, $data);
	}

	/**
	 * Загрузка списка Фактических показателей
	 * @param $data
	 * @return array|bool
	 */
	function loadPersonDispFactRateList($data)
	{
		return Polka_PersonDisp_model_load::loadPersonDispFactRateList($this, $data);
	}

	/**
	 * Получение списка дис. карт пациента
	 * @param $data
	 * @return array|false
	 */
	function loadPersonDispList($data)
	{
		return Polka_PersonDisp_model_load::loadPersonDispList($this, $data);
	}

	/**
	 * Получение списка дисп.учета пациента для ЭМК
	 * @param $data
	 * @return array|false
	 */
	function loadPersonDispPanel($data)
	{
		return Polka_PersonDisp_model_load::loadPersonDispPanel($this, $data);
	}

	/**
	 * ДМ: Получить список пациентов для дистанционного мониторинга
	 * Используется: Дистанционный мониторинг (RemoteMonitoringWindow.js)
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	function loadPersonLabelList($data)
	{
		return Polka_PersonDisp_model_load::loadPersonLabelList($this, $data);
	}

	/**
	 * ДМ: Получить измерения для карты наблюдения
	 * Используется: таблица замеров карты наблюдения
	 * @param $data
	 * @return array|bool
	 */
	function loadLabelObserveChartMeasure($data)
	{
		return Polka_PersonDisp_model_load::loadLabelObserveChartMeasure($this, $data);
	}

	/**
	 * ДМ: История включений в программу дистанционного мониторинга
	 * Используется: форма "История включения в программу" (InviteHistoryWindow)
	 * @param $data
	 * @return array|bool
	 */
	function loadLabelInviteHistory($data)
	{
		return Polka_PersonDisp_model_load::loadLabelInviteHistory($this, $data);
	}

	/**
	 * ДМ: Список сообщений в карте наблюдения
	 * @param $data
	 * @return array
	 */
	function loadLabelMessages($data)
	{
		return Polka_PersonDisp_model_load::loadLabelMessages($this, $data);
	}
	#endregion load
	#region save
	/**
	 * Сохраняет медикамент
	 * @param $data
	 * @return array|bool
	 */
	function savePersonDispMedicament($data)
	{
		return Polka_PersonDisp_model_save::savePersonDispMedicament($this, $data);
	}

	/**
	 * Сохранение
	 * @param $data
	 * @return array|bool|CI_DB_result|mixed
	 * @throws Exception
	 */
	function savePersonDisp($data)
	{
		return Polka_PersonDisp_model_save::savePersonDisp($this, $data);
	}

	/**
	 * Добавление/изменение строки из истории изменений диагонозов в карте ДУ
	 * @param $data
	 * @return array|bool
	 */
	function saveDiagDispCard($data)
	{
		return Polka_PersonDisp_model_save::saveDiagDispCard($this, $data);
	}

	/**
	 * Сохранение Контроля посещений
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function savePersonDispVizit($data)
	{
		return Polka_PersonDisp_model_save::savePersonDispVizit($this, $data);
	}

	/**
	 * Сохранение Контроля посещений
	 * @param $data
	 * @return array|bool
	 */
	function savePersonDispEvnVizitPL($data)
	{
		return Polka_PersonDisp_model_save::savePersonDispEvnVizitPL($this, $data);
	}

	/**
	 * Сохранение Сопутствующих диагнозов
	 * @param $data
	 * @return array|bool
	 */
	function savePersonDispSopDiag($data)
	{
		return Polka_PersonDisp_model_save::savePersonDispSopDiag($this, $data);
	}

	/**
	 * Сохранение Ответственного врача
	 * @param $data
	 * @return array|bool
	 */
	function savePersonDispHist($data)
	{
		return Polka_PersonDisp_model_save::savePersonDispHist($this, $data);
	}

	/**
	 * Сохранение Целевых показателей
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function savePersonDispTargetRate($data)
	{
		return Polka_PersonDisp_model_save::savePersonDispTargetRate($this, $data);
	}

	/**
	 * Сохранение Фактических показателей
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	function savePersonDispFactRate($data)
	{
		return Polka_PersonDisp_model_save::savePersonDispFactRate($this, $data);
	}

	/**
	 * ДМ: Сохранить способ обратной связи в Карте наблюдения
	 * Используется: Дистанционный мониторинг
	 * @param $data
	 * @return array
	 */
	function savePersonChartFeedback($data)
	{
		return Polka_PersonDisp_model_save::savePersonChartFeedback($this, $data);
	}

	/**
	 * ДМ: Сохранить целевые показатели для карты наблюдения
	 * @param $data
	 * @return array
	 */
	function saveLabelObserveChartRate($data)
	{
		return Polka_PersonDisp_model_save::saveLabelObserveChartRate($this, $data);
	}

	/**
	 * ДМ: Сохранение/добавление измерения по целевому показателю
	 * Используется: saveLabelObserveChartMeasure
	 * @param $measure
	 * @param $data
	 * @return array|bool
	 */
	function saveLabelObserveChartRateMeasure($measure, $data)
	{
		return Polka_PersonDisp_model_save::saveLabelObserveChartRateMeasure($this, $measure, $data);
	}

	/**
	 * ДМ: Сохранить/добавить измерения в карте наблюдения (строка в LabelObserveChartInfo и массив замеров к ней)
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	function saveLabelObserveChartMeasure($data)
	{
		return Polka_PersonDisp_model_save::saveLabelObserveChartMeasure($this, $data);
	}

	/**
	 * ДМ: Сохранить поля в карте наблюдения
	 * @param $data
	 * @return array|bool
	 */
	function savePersonChartInfo($data)
	{
		return Polka_PersonDisp_model_save::savePersonChartInfo($this, $data);
	}
	#endregion save
	#region common



	/**
	 * ДМ
	 * @param $data
	 * @return bool
	 */
	function updateLabelInviteStatus($data)
	{
		return Polka_PersonDisp_model_common::updateLabelInviteStatus($this, $data);
	}
	
	
	/**
	 * ДМ: Приглашение в программу дист.мониторинга
	 * @param $data
	 * @return bool
	 */
	function InviteInMonitoring($data)
	{
		return Polka_PersonDisp_model_common::InviteInMonitoring($this, $data);
	}

	/**
	 * ДМ: Отправить пациентам напоминание
	 * @param $data
	 * @return bool
	 */
	function RemindToMonitoring($data)
	{
		return Polka_PersonDisp_model_common::RemindToMonitoring($this, $data);
	}

	/**
	 * ДМ: Исключить из программы Дистанционный мониторинг
	 * @param $data
	 * @return bool
	 */
	function removePersonFromMonitoring($data)
	{
		return Polka_PersonDisp_model_common::removePersonFromMonitoring($this, $data);
	}

	/**
	 * ДМ: Отправить пациенту сообщение
	 * @param $data
	 * @return array|bool
	 */
	function sendLabelMessage($data)
	{
		return Polka_PersonDisp_model_common::sendLabelMessage($this, $data);
	}

	/**
	 * Заполнение меток у пациентов
	 * @param $userId
	 * @return bool
	 */
	function setLabels($userId)
	{
		return Polka_PersonDisp_model_common::setLabels($this, $userId);
	}

	/**
	 * Проверка существования диспансерской карты
	 * @param $data
	 * @return array|bool
	 */
	function checkPersonDispExists($data)
	{
		return Polka_PersonDisp_model_common::checkPersonDispExists($this, $data);
	}

	/**
	 * Удаление медикамента
	 * @param $data
	 */
	function deletePersonDispMedicament($data)
	{
		Polka_PersonDisp_model_common::deletePersonDispMedicament($this, $data);
	}

	/**
	 * Удаление
	 * @param $data
	 * @return array|bool|CI_DB_result|false|mixed|void
	 */
	public function deletePersonDisp($data)
	{
		return Polka_PersonDisp_model_common::deletePersonDisp($this, $data);
	}

	/**
	 * Удаление строки из истории изменений диагонозов в карте ДУ
	 * @param $data
	 * @return array|bool
	 */
	function deleteDiagDispCard($data)
	{
		return Polka_PersonDisp_model_common::deleteDiagDispCard($this, $data);
	}

	/**
	 * Проверяет, повторяется ли дата в поле "Назначено явиться" в контрольной карте, форма Контроль посещений
	 * @param $PersonDisp_id - ID контрольной карты диспансерного наблюдения.
	 * @param $PersonDispVizit_NextDate - Дата из поля "Назначено явиться".
	 * @param $PersonDispVizit_id - ID контроля посещений.
	 * @return bool
	 */
	function checkVisitDoubleNextdate($PersonDisp_id, $PersonDispVizit_NextDate, $PersonDispVizit_id)
	{
		return Polka_PersonDisp_model_common::checkVisitDoubleNextdate($this, $PersonDisp_id, $PersonDispVizit_NextDate, $PersonDispVizit_id);
	}

	/**
	 * Удаление Контроля посещений
	 * @param $data
	 * @return array|bool
	 */
	function delPersonDispVizit($data)
	{
		return Polka_PersonDisp_model_common::delPersonDispVizit($this, $data);
	}

	/**
	 * Удаление Сопутствующих диагнозов
	 * @param $data
	 * @return array|bool
	 */
	function delPersonDispSopDiag($data)
	{
		return Polka_PersonDisp_model_common::delPersonDispSopDiag($this, $data);
	}

	/**
	 * Проверка на пересечение периодов действия отвественных врачей
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	function checkPersonDispHistDates($data)
	{
		return Polka_PersonDisp_model_common::checkPersonDispHistDates($this, $data);
	}

	/**
	 * Удаление Ответственного врача
	 * @param $data
	 * @return array|bool
	 */
	function deletePersonDispHist($data)
	{
		return Polka_PersonDisp_model_common::deletePersonDispHist($this, $data);
	}

	/**
	 * Удаление Фактических показателей
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	function deletePersonDispFactRate($data)
	{
		return Polka_PersonDisp_model_common::deletePersonDispFactRate($this, $data);
	}

	/**
	 * Выгрузка списка карт диспансерного наблюдения
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function exportPersonDispCard($data)
	{
		return Polka_PersonDisp_model_common::exportPersonDispCard($this, $data);
	}

	/**
	 * ДМ: Удалить измерение в карте наблюдения
	 * @param $data
	 * @return array|bool
	 */
	function deleteLabelObserveChartMeasure($data)
	{
		return Polka_PersonDisp_model_common::deleteLabelObserveChartMeasure($this, $data);
	}

	/**
	 * ДМ: Проверка количества открытых карт наблюдения по метке
	 * @param $data
	 * @return bool|float|int|string
	 */
	function checkOpenedLabelObserveChart($data)
	{
		return Polka_PersonDisp_model_common::checkOpenedLabelObserveChart($this, $data);
	}

	/**
	 * ДМ: проверить наличие карт по пациенту
	 * @param $data
	 * @return bool|float|int|string
	 */
	function checkOpenedLabelObserveChartByPerson($data)
	{
		return Polka_PersonDisp_model_common::checkOpenedLabelObserveChartByPerson($this, $data);
	}

	/**
	 * ДМ: Создать карту наблюдения
	 * @param $data
	 * @return bool|float|int|string
	 */
	function createLabelObserveChart($data)
	{
		return Polka_PersonDisp_model_common::createLabelObserveChart($this, $data);
	}

	/**
	 * ДМ: Создать целевые показатели для карты наблюдения
	 * Используется: function createLabelObserveChart
	 * @param $params
	 * @return bool
	 */
	function addLabelObserveChartRate($params)
	{
		return Polka_PersonDisp_model_common::addLabelObserveChartRate($this, $params);
	}

	/**
	 * ДМ: Изменение статуса приглашения в дист.мониторинг
	 * @param $data
	 * @return array|bool
	 */
	function ChangeLabelInviteStatus($data)
	{
		return Polka_PersonDisp_model_common::ChangeLabelInviteStatus($this, $data);
	}

	/**
	 * Создать для пациента метку
	 * @param $data
	 * @return bool|mixed
	 */
	function createPersonLabel($data)
	{
		return Polka_PersonDisp_model_common::createPersonLabel($this, $data);
	}

	/**
	 * Проверка существования показателей у пользователя
	 * @param $data
	 * @return bool|mixed
	 */
	function checkPersonLabelObserveChartRates($data)
	{
		return Polka_PersonDisp_model_common::checkPersonLabelObserveChartRates($this, $data);
	}
	#endregion common

    /**
     *  Замена ответственного врача
     */
    function setResponsibleReplacementOptionsDoctor($data){
        if(empty($data['MedStaffFact_id']) || empty($data['personDispList'])) return false;
        $resultErr = array();
        $resultSuccessfully = array();
        $personDispARR = explode(',', $data['personDispList']);
        $curentDT = new DateTime();
        $dt = new DateTime();
        $curentDT = $curentDT->format('Y-m-d');
        $yesterdayDT = $dt->modify('-1 day')->format('Y-m-d');

        foreach ($personDispARR as $value) {
            $PersonDisp_id = $value;
            $curentDoctor = $this->getCurrentResponsibleDoctor(array('PersonDisp_id' => $PersonDisp_id));
            if($curentDoctor && !empty($curentDoctor[0]['PersonDispHist_id']) && empty($curentDoctor[0]['PersonDispHist_endDate'])){
                if($curentDoctor[0]['MedPersonal_id'] != $data['MedPersonal_id'] && $curentDoctor[0]['PersonDispHist_begDate'] < $curentDT){
                    $flag = true;
                    $paramsClose = array(
                        'PersonDispHist_id' => $curentDoctor[0]['PersonDispHist_id'],
                        'PersonDisp_id' => $curentDoctor[0]['PersonDisp_id'],
                        'MedPersonal_id' => $curentDoctor[0]['MedPersonal_id'],
                        'LpuSection_id' => $curentDoctor[0]['LpuSection_id'],
                        'PersonDispHist_begDate' => $curentDoctor[0]['PersonDispHist_begDate'],
                        'PersonDispHist_endDate' => $yesterdayDT,
                        'pmUser_id' => $data['pmUser_id']
                    );
                    //закрываем ответственного врача
                    $resClose = $this->savePersonDispHist($paramsClose);
                    if($resClose && (!empty($resClose['Error_Msg']) || empty($resClose[0]['PersonDispHist_id']))){
                        $err = '';
                        if((!empty($resClose['Error_Msg']))) $err .= $resClose['Error_Msg'].'. ';
                        if(!empty($resClose[0]['Error_Msg'])) $err .= $resClose[0]['Error_Msg'];
                        $resultErr[$value] = $err;
                        $flag = false;
                    }
                    if($flag){
                        $paramsAdd = array(
                            'PersonDispHist_id' => null,
                            'PersonDisp_id' => $curentDoctor[0]['PersonDisp_id'],
                            'MedPersonal_id' => $data['MedPersonal_id'],
                            'LpuSection_id' => $data['LpuSection_id'],
                            'PersonDispHist_begDate' => $curentDT,
                            'PersonDispHist_endDate' => null,
                            'pmUser_id' => $data['pmUser_id']
                        );
                        // Создается новая запись «ответственный врач»
                        $resAdd = $this->savePersonDispHist($paramsAdd);
                        if(!empty($resAdd[0]['PersonDispHist_id'])){
                            $resultSuccessfully[$value] = $resAdd[0]['PersonDispHist_id'];
                        }else{
                            $resultErr[$value] = (!empty($resClose[0]['Error_Msg'])) ? $resClose[0]['Error_Msg'] : 'Ошибка при добавлении нового ответственного врача';
                        }
                    }
                }else{
                    $resultErr[$value] = 'Не соответствует условию';
                }
            }
        }

        return array('resultErr' => $resultErr, 'resultSuccessfully' => $resultSuccessfully);
    }

    /**
     * Получить текущего отвественного врача
     */
    function getCurrentResponsibleDoctor($data) {
        if(empty($data['PersonDisp_id'])) return false;
        $sql = "
			select
				MP.MedPersonal_id as \"MedPersonal_id\",
				PDSD.PersonDisp_id as \"PersonDisp_id\",
				PDSD.PersonDispHist_id as \"PersonDispHist_id\",
				PDSD.MedPersonal_id as \"MedPersonal_id\",
				PDSD.LpuSection_id as \"LpuSection_id\",				
				to_char(PersonDispHist_begDate, 'YYYY-MM-DD') as \"PersonDispHist_begDate\",
				to_char(PersonDispHist_endDate, 'YYYY-MM-DD') as \"PersonDispHist_endDate\"
			from v_PersonDispHist PDSD
				left join v_MedPersonal MP on MP.MedPersonal_id = PDSD.MedPersonal_id
			where PDSD.PersonDisp_id = :PersonDisp_id
			order by PDSD.PersonDispHist_begDate desc
			limit 1	
		";
        $res = $this->db->query($sql, $data);
        if ( is_object($res) ) {
            return $res->result('array');
        } else {
            return false;
        }
    }


	/**
	 * Получить показатели, связанные с пользователем
	 */

	function getPersonLabelObserveChartRates($data) {

		$sql = "
			select
				locr.LabelObserveChartRate_id as \"LabelObserveChartRate_id\",
				locr.LabelObserveChartRate_IsShowValue as \"LabelObserveChartRate_IsShowValue\",
				locr.LabelObserveChartSource_id as \"LabelObserveChartSource_id\",
				rt.RateType_SysNick as \"RateType_SysNick\",
				rt.RateType_Name as \"RateType_Name\",
				rt.RateType_id as \"RateType_id\"
			from v_LabelObserveChartRate locr
			left join v_ratetype rt on rt.RateType_id = locr.RateType_id
			where (1=1)
				and locr.Person_id = :Person_id
		";
		
		$res = $this->db->query($sql, array('Person_id' => $data['Person_id']));
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}
}