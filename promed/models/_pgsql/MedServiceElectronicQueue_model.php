<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * MedServiceElectronicQueue_model - связь службы и очереди
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 *
 * @property CI_DB_driver $db
 * @property ElectronicQueue_model $ElectronicQueue_model
 */
class MedServiceElectronicQueue_model extends SwPgModel
{

	/**
	 * Удаление связи
	 * @param $data
	 * @return array|false
	 */
	function delete($data)
	{

		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_medserviceelectronicqueue_del(medserviceelectronicqueue_id := :MedServiceElectronicQueue_id);
		";
		return $this->queryResult($query, $data);
	}

	/**
	 * Возвращает список связей
	 * @param $data
	 * @return array|false
	 */
	function loadList($data)
	{
		$filters = [];
		if (!empty($data["MedService_id"])) {
			$filters[] = "msmp.MedService_id = :MedService_id";
		}
		if (!empty($data["LpuBuilding_id"])) {
			$filters[] = "eq.LpuBuilding_id = :LpuBuilding_id";
		}
		if (!empty($data["LpuSection_id"])) {
			$filters[] = "eq.LpuSection_id = :LpuSection_id";
		}
		$whereString = (count($filters) != 0)?"where ".implode(" and ", $filters):"";
		$query = "
			select
				mseq.MedServiceElectronicQueue_id as \"MedServiceElectronicQueue_id\",
				eq.MedService_id as \"MedService_id\",
				mseq.UslugaComplexMedService_id as \"UslugaComplexMedService_id\",
				mseq.MedStaffFact_id as \"MedStaffFact_id\",
				mseq.Resource_id as \"Resource_id\",
				eq.LpuBuilding_id as \"LpuBuilding_id\",
				eq.LpuSection_id as \"LpuSection_id\",
				coalesce(mp.Person_Fio,msf.Person_Fio) as \"MedPersonal_Name\",
				uc.UslugaComplex_Name as \"UslugaComplex_Name\",
				es.ElectronicService_Name as \"ElectronicService_Name\",
				es.ElectronicService_Num as \"ElectronicService_Num\"
			from
				v_MedServiceElectronicQueue mseq
				left join v_MedStaffFact msf on msf.MedStaffFact_id = mseq.MedStaffFact_id
				left join v_MedServiceMedPersonal msmp on msmp.MedServiceMedPersonal_id = mseq.MedServiceMedPersonal_id
				left join v_MedPersonal mp on mp.MedPersonal_id = msmp.MedPersonal_id
				left join v_UslugaComplexMedService ucms on ucms.UslugaComplexMedService_id = mseq.UslugaComplexMedService_id
				left join v_UslugaComplex uc on uc.UslugaComplex_id = ucms.UslugaComplex_id
				left join v_ElectronicService es on es.ElectronicService_id = mseq.ElectronicService_id
				left join v_ElectronicQueueInfo eq on eq.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
			{$whereString}
			order by es.ElectronicService_Num
		";
		return $this->queryResult($query, $data);
	}

	/**
	 * Возвращает связь
	 * @param $data
	 * @return array|false
	 */
	function load($data)
	{
		$query = "
			select
				mseq.MedServiceElectronicQueue_id as \"MedServiceElectronicQueue_id\",
				eq.MedService_id as \"MedService_id\",
				eq.LpuBuilding_id as \"LpuBuilding_id\",
				eq.LpuSection_id as \"LpuSection_id\",
				mseq.MedServiceMedPersonal_id as \"MedServiceMedPersonal_id\",
				mseq.MedStaffFact_id as \"MedStaffFact_id\",
				mseq.UslugaComplexMedService_id as \"UslugaComplexMedService_id\",
				mseq.ElectronicService_id as \"ElectronicService_id\",
				mseq.Resource_id as \"Resource_id\",
				es.ElectronicService_Num as \"ElectronicService_Num\"
			from
				v_MedServiceElectronicQueue mseq
				left join v_MedStaffFact msf on msf.MedStaffFact_id = mseq.MedStaffFact_id
				left join v_MedServiceMedPersonal msmp on msmp.MedServiceMedPersonal_id = mseq.MedServiceMedPersonal_id
				inner join v_ElectronicService es on es.ElectronicService_id = mseq.ElectronicService_id
				inner join v_ElectronicQueueInfo eq on eq.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
			where mseq.MedServiceElectronicQueue_id = :MedServiceElectronicQueue_id
		";
		return $this->queryResult($query, $data);
	}

	/**
	 * Определяет сущность по входящим параметрам
	 * @param $data
	 * @return string
	 */
	function defineTimetableEntity($data)
	{
		$enitity = "";
		if (!empty($data["MedStaffFact_id"])) {
			$enitity = "TimetableGraf";
		}
		if (!empty($data["UslugaComplexMedService_id"]) || !empty($data["MedService_id"])) {
			$enitity = "TimetableMedService";
		}
		if (!empty($data["Resource_id"])) {
			$enitity = "TimetableResource";
		}
		return $enitity;
	}

	/**
	 * Сохраняет связь
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function save($data)
	{
		if (!empty($data["MedStaffFact_id"])) {
			// доступность привязки врача к определенному пункту обслуживания
			if (true !== ($response = $this->checkMedStaffFactLinkWithLpuSection($data))) {
				throw new Exception("Выбранный врач не может быть связан с данным пунктом обслуживания, так как ЭО данного пункта обслуживания связана с другим отделением");
			}
		}
		if (empty($data["ignoreDoublesByMedPersonal"]) && true !== ($response = $this->checkElectronicServiceDoubles($data))) {
			// Проверка дубликатов пунктов
			return $this->createAlert("MSEQ001", "YesNo", "Пункт обслуживания уже добавлен для другого сотрудника. Обслуживание одного пункта несколькими сотрудниками не предусмотрено. Продолжить сохранение?");
		}
		$procedure = empty($data["MedServiceElectronicQueue_id"]) ? "p_MedServiceElectronicQueue_ins" : "p_MedServiceElectronicQueue_upd";
		if (!(!empty($data["MedService_id"]) && !empty($data["MedServiceType_SysNick"]) && $data["MedServiceType_SysNick"] == "pzm")) {
			$data["MedService_id"] = null;
		}
		$selectString = "
		    medserviceelectronicqueue_id as \"MedServiceElectronicQueue_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    medserviceelectronicqueue_id := :MedServiceElectronicQueue_id,
			    medservicemedpersonal_id := :MedServiceMedPersonal_id,
			    uslugacomplexmedservice_id := :UslugaComplexMedService_id,
			    electronicservice_id := :ElectronicService_id,
			    medstafffact_id := :MedStaffFact_id,
			    electronicqueueinfo_id := null,
			    medservice_id := :MedService_id,
			    resource_id := :Resource_id,
			    pmuser_id := :pmUser_id
			);
		";
		$resp = $this->queryResult($query, $data);
		// Генерим коды брони, если они не сгенерированы для тех кто записался раньше
		if (!empty($resp[0]) && !empty($resp[0]["MedServiceElectronicQueue_id"])) {
			$this->load->model("ElectronicQueue_model");
			$this->ElectronicQueue_model->generateTalonCodeForExistedRecords($data);
		}
		return $resp;
	}

	/**
	 * Проверка дубликатов сотрудников на службе
	 * @param $data
	 * @return bool
	 */
	function checkMedPersonalDoubles($data)
	{
		$filter = (!empty($data["MedServiceElectronicQueue_id"])) ? " and MedServiceElectronicQueue_id != :MedServiceElectronicQueue_id" : "";
		$query = "
			select count(*) as \"dubs\"
			from v_MedServiceElectronicQueue
			where MedServiceMedPersonal_id = :MedServiceMedPersonal_id {$filter}
		";
		$result = $this->getFirstResultFromQuery($query, $data);
		return ($result > 0) ? false : true;
	}

	/**
	 * Проверка дубликатов врачей
	 * @param $data
	 * @return bool
	 */
	function checkMedStaffFactDoubles($data)
	{
		$filter = (!empty($data["MedServiceElectronicQueue_id"])) ? " and MedServiceElectronicQueue_id != :MedServiceElectronicQueue_id" : "";
		$query = "
			select count(*) as \"dubs\"
			from v_MedServiceElectronicQueue
			where MedStaffFact_id = :MedStaffFact_id {$filter}
		";
		$result = $this->getFirstResultFromQuery($query, $data);
		return ($result > 0) ? false : true;
	}

	/**
	 * Получение пункта осблуживания и услуги для списка рабочих мест
	 * @param $data
	 * @return array|false
	 */
	function getInfoForMSFList($data)
	{
		$query = "
			select
				mseq.ElectronicService_id as \"ElectronicService_id\",
				mseq.UslugaComplexMedService_id as \"UslugaComplexMedService_id\",
				ucms.UslugaComplex_id as \"UslugaComplex_id\",
				coalesce(es.ElectronicService_Code::varchar||' ', '')||es.ElectronicService_Name as \"ElectronicService_Name\",
				uc.UslugaComplex_Name as \"UslugaComplex_Name\"
			from
				v_MedServiceElectronicQueue mseq
				inner join v_MedServiceMedPersonal msmp on msmp.MedServiceMedPersonal_id = mseq.MedServiceMedPersonal_id
				left join v_UslugaComplexMedService ucms on ucms.UslugaComplexMedService_id = mseq.UslugaComplexMedService_id
				left join v_ElectronicService es on es.ElectronicService_id = mseq.ElectronicService_id
				left join v_UslugaComplex uc on uc.UslugaComplex_id = ucms.UslugaComplex_id
			where msmp.MedPersonal_id = :MedPersonal_id
			  and msmp.MedService_id = :MedService_id
		";
		$queryParams = [
			"MedPersonal_id" => $data["MedPersonal_id"],
			"MedService_id" => $data["MedService_id"]
		];
		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Проверка дубликатов пунктов
	 * @param $data
	 * @return bool
	 */
	function checkElectronicServiceDoubles($data)
	{
		$filter = (!empty($data["MedServiceElectronicQueue_id"])) ? " and MedServiceElectronicQueue_id != :MedServiceElectronicQueue_id" : "";
		$query = "
			select count(*) as \"dubs\"
			from v_MedServiceElectronicQueue
			where ElectronicService_id = :ElectronicService_id {$filter}
		";
		$result = $this->getFirstResultFromQuery($query, $data);
		return ($result > 0) ? false : true;
	}

	/**
	 * Проверка пунктов обслуживания (отделения ЛПУ) на доступность привязки врача к определенному пункту обслуживания
	 * @param $data
	 * @return bool
	 */
	function checkMedStaffFactLinkWithLpuSection($data)
	{
		$query = "
			select
				eqi.ElectronicQueueInfo_id as \"ElectronicQueueInfo_id\",
				eqi.LpuSection_id as \"LpuSection_id\"
			from
				v_ElectronicService es
				inner join v_ElectronicQueueInfo eqi on eqi.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
			where es.ElectronicService_id = :ElectronicService_id
			limit 1
		";
		$response = $this->queryResult($query, $data);
		// если в отделении пусто, то пропускаем
		if (empty($response[0]["LpuSection_id"])) {
			return true;
		}
		$data["LpuSection_id"] = $response[0]["LpuSection_id"];
		$query = "
			select medstafffact_id as \"MedStaffFact_id\"
			from v_MedStaffFact msf
			where MedStaffFact_id = :MedStaffFact_id
			  and LpuSection_id = :LpuSection_id
			limit 1
		";
		$response = $this->queryResult($query, $data);
		return (!empty($response[0])) ? true : false;
	}
}