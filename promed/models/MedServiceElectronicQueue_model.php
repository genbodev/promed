<?php defined('BASEPATH') or die ('No direct script access allowed');
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
 */

class MedServiceElectronicQueue_model extends swModel {

	/**
	 * Удаление связи
	 */
	function delete($data) {

		$query = "
			declare
				@Error_Code int,
				@Error_Msg varchar(4000);

			exec p_MedServiceElectronicQueue_del
				@MedServiceElectronicQueue_id = :MedServiceElectronicQueue_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;

			select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";

		return $this->queryResult($query, $data);
	}

	/**
	 * Возвращает список связей
	 */
	function loadList($data) {

		$filter = '';

		if (!empty($data['MedService_id'])) {
			$filter = 'msmp.MedService_id = :MedService_id';
		}

		if (!empty($data['LpuBuilding_id'])) {
			$filter = 'eq.LpuBuilding_id = :LpuBuilding_id';
		}

		if (!empty($data['LpuSection_id'])) {
			$filter = 'eq.LpuSection_id = :LpuSection_id';
		}

		$query = "
			select
				mseq.MedServiceElectronicQueue_id
				,eq.MedService_id
				,mseq.UslugaComplexMedService_id
				,mseq.MedStaffFact_id
				,mseq.Resource_id
				,eq.LpuBuilding_id
				,eq.LpuSection_id
				,ISNULL(mp.Person_Fio,msf.Person_Fio) as MedPersonal_Name
				,uc.UslugaComplex_Name
				,es.ElectronicService_Name
				,es.ElectronicService_Num
			from
				v_MedServiceElectronicQueue mseq with(nolock)
				left join v_MedStaffFact msf with(nolock) on msf.MedStaffFact_id = mseq.MedStaffFact_id
				left join v_MedServiceMedPersonal msmp with(nolock) on msmp.MedServiceMedPersonal_id = mseq.MedServiceMedPersonal_id
				left join v_MedPersonal mp with(nolock) on mp.MedPersonal_id = msmp.MedPersonal_id
				left join v_UslugaComplexMedService ucms with(nolock) on ucms.UslugaComplexMedService_id = mseq.UslugaComplexMedService_id
				left join v_UslugaComplex uc with(nolock) on uc.UslugaComplex_id = ucms.UslugaComplex_id
				left join v_ElectronicService es with(nolock) on es.ElectronicService_id = mseq.ElectronicService_id
				left join v_ElectronicQueueInfo eq with(nolock) on eq.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
			where {$filter}
			order by es.ElectronicService_Num
		";

		return $this->queryResult($query, $data);
	}

	/**
	 * Возвращает связь
	 */
	function load($data) {

		$query = "
			select
				mseq.MedServiceElectronicQueue_id
				,eq.MedService_id
				,eq.LpuBuilding_id
				,eq.LpuSection_id
				,mseq.MedServiceMedPersonal_id
				,mseq.MedStaffFact_id
				,mseq.UslugaComplexMedService_id
				,mseq.ElectronicService_id
				,mseq.Resource_id
				,es.ElectronicService_Num
			from
				v_MedServiceElectronicQueue mseq with(nolock)
				left join v_MedStaffFact msf with(nolock) on msf.MedStaffFact_id = mseq.MedStaffFact_id
				left join v_MedServiceMedPersonal msmp with(nolock) on msmp.MedServiceMedPersonal_id = mseq.MedServiceMedPersonal_id
				inner join v_ElectronicService es with(nolock) on es.ElectronicService_id = mseq.ElectronicService_id
				inner join v_ElectronicQueueInfo eq with(nolock) on eq.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
			where
				mseq.MedServiceElectronicQueue_id = :MedServiceElectronicQueue_id
		";

		return $this->queryResult($query, $data);
	}

	/**
	 * Определяет сущность по входящим параметрам
	 */
	function defineTimetableEntity($data) {

		$enitity = "";

		if (!empty($data['MedStaffFact_id'])) $enitity = "TimetableGraf";
		if (!empty($data['UslugaComplexMedService_id']) || !empty($data['MedService_id']))  $enitity = "TimetableMedService";
		if (!empty($data['Resource_id']))  $enitity = "TimetableResource";

		return $enitity;
	}

	/**
	 * Сохраняет связь
	 */
	function save($data) {

		/*if (!empty($data['MedServiceMedPersonal_id'])) {

			// Проверка дубликатов сотрудников на службе
			if (true !== ($response = $this->checkMedPersonalDoubles($data))) {
				return $this->createError('', 'Сотрудник на службе уже добавлен для другого пункта обслуживания. Обслуживание одним сотрудником нескольких пунктов обслуживания не предусмотрено');
			}
		}*/

		if (!empty($data['MedStaffFact_id'])) {

			// Проверка дубликатов врачей
			/*if (true !== ($response = $this->checkMedStaffFactDoubles($data))) {
				return $this->createError('', 'Выбранный врач уже добавлен для другого пункта обслуживания. Обслуживание одним врачом нескольких пунктов обслуживания не предусмотрено');
			}*/

			// доступность привязки врача к определенному пункту обслуживания
			if (true !== ($response = $this->checkMedStaffFactLinkWithLpuSection($data))) {
				return $this->createError('', 'Выбранный врач не может быть связан с данным пунктом обслуживания, так как ЭО данного пункта обслуживания связана с другим отделением');
			}
		}

		// Проверка дубликатов пунктов
		if (empty($data['ignoreDoublesByMedPersonal']) && true !== ($response = $this->checkElectronicServiceDoubles($data))) {
			return $this->createAlert('MSEQ001', 'YesNo', 'Пункт обслуживания уже добавлен для другого сотрудника. Обслуживание одного пункта несколькими сотрудниками не предусмотрено. Продолжить сохранение?');
		}

		$procedure = empty($data['MedServiceElectronicQueue_id']) ? 'p_MedServiceElectronicQueue_ins' : 'p_MedServiceElectronicQueue_upd';

		if (!empty($data['MedService_id'])
			&& !empty($data['MedServiceType_SysNick'])
			&& $data['MedServiceType_SysNick'] == 'pzm') {
			// не затираем службу
		} else { $data['MedService_id'] = NULL; }

		$query = "
			declare
				@MedServiceElectronicQueue_id bigint = :MedServiceElectronicQueue_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$procedure}
				@MedServiceElectronicQueue_id = @MedServiceElectronicQueue_id output,
				@MedServiceMedPersonal_id = :MedServiceMedPersonal_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@Resource_id = :Resource_id,
				@UslugaComplexMedService_id = :UslugaComplexMedService_id,
				@ElectronicService_id = :ElectronicService_id,
				@pmUser_id = :pmUser_id,
				@MedService_id = :MedService_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @MedServiceElectronicQueue_id as MedServiceElectronicQueue_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$resp = $this->queryResult($query, $data);

		// Генерим коды брони, если они не сгенерированы для тех кто записался раньше
		if (!empty($resp[0]) && !empty($resp[0]['MedServiceElectronicQueue_id'])) {

			$this->load->model('ElectronicQueue_model');
			$this->ElectronicQueue_model->generateTalonCodeForExistedRecords($data);
		}

		return $resp;
	}

	/**
	 * Проверка дубликатов сотрудников на службе
	 */
	function checkMedPersonalDoubles($data) {
		
		$query = "
			select count(*) [dubs]
			from v_MedServiceElectronicQueue with(nolock)
			where MedServiceMedPersonal_id = :MedServiceMedPersonal_id
		";
		
		if (!empty($data['MedServiceElectronicQueue_id'])) {
			$query .= " and MedServiceElectronicQueue_id != :MedServiceElectronicQueue_id";
		}
		
		$res = $this->getFirstResultFromQuery($query, $data);
		
		if ($res > 0) {
			return false;
		}
		
		return true;
	}

	/**
	 * Проверка дубликатов врачей
	 */
	function checkMedStaffFactDoubles($data) {

		$query = "
			select count(*) [dubs]
			from v_MedServiceElectronicQueue with(nolock)
			where MedStaffFact_id = :MedStaffFact_id
		";

		if (!empty($data['MedServiceElectronicQueue_id'])) {
			$query .= " and MedServiceElectronicQueue_id != :MedServiceElectronicQueue_id";
		}

		$res = $this->getFirstResultFromQuery($query, $data);

		if ($res > 0) {
			return false;
		}

		return true;
	}

	/**
	 * Получение пункта осблуживания и услуги для списка рабочих мест
	 */
	function getInfoForMSFList($data) {
		return $this->queryResult("
			select
				mseq.ElectronicService_id,
				mseq.UslugaComplexMedService_id,
				ucms.UslugaComplex_id,
				ISNULL(cast(es.ElectronicService_Code as varchar) + ' ', '') + es.ElectronicService_Name as ElectronicService_Name,
				uc.UslugaComplex_Name
			from
				v_MedServiceElectronicQueue mseq (nolock)
				inner join v_MedServiceMedPersonal msmp (nolock) on msmp.MedServiceMedPersonal_id = mseq.MedServiceMedPersonal_id
				left join v_UslugaComplexMedService ucms (nolock) on ucms.UslugaComplexMedService_id = mseq.UslugaComplexMedService_id
				left join v_ElectronicService es (nolock) on es.ElectronicService_id = mseq.ElectronicService_id
				left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = ucms.UslugaComplex_id
			where
				msmp.MedPersonal_id = :MedPersonal_id
				and msmp.MedService_id = :MedService_id
		", array(
			'MedPersonal_id' => $data['MedPersonal_id'],
			'MedService_id' => $data['MedService_id']
		));
	}

	/**
	 * Проверка дубликатов пунктов
	 */
	function checkElectronicServiceDoubles($data) {
		
		$query = "
			select count(*) [dubs]
			from v_MedServiceElectronicQueue with(nolock)
			where ElectronicService_id = :ElectronicService_id
		";
		
		if (!empty($data['MedServiceElectronicQueue_id'])) {
			$query .= " and MedServiceElectronicQueue_id != :MedServiceElectronicQueue_id";
		}
		
		$res = $this->getFirstResultFromQuery($query, $data);
		
		if ($res > 0) {
			return false;
		}
		
		return true;
	}

	/**
	 * Проверка пунктов обслуживания (отделения ЛПУ) на доступность
	 * привязки врача к определенному пункту обслуживания
	 */
	function checkMedStaffFactLinkWithLpuSection($data) {

		$query = "
			select top 1
				eqi.ElectronicQueueInfo_id,
				eqi.LpuSection_id
			from v_ElectronicService es with(nolock)
			inner join v_ElectronicQueueInfo eqi (nolock) on eqi.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
			where
				es.ElectronicService_id = :ElectronicService_id
		";

		$response = $this->queryResult($query, $data);

		// если в отделении пусто, то пропускаем
		if (empty($response[0]['LpuSection_id'])) {

			return true;

			// проверяем дальше
		} else {

			$data['LpuSection_id'] = $response[0]['LpuSection_id'];

			$query = "
				select top 1
					MedStaffFact_id
				from v_MedStaffFact msf with(nolock)
				where
					MedStaffFact_id = :MedStaffFact_id
					and LpuSection_id = :LpuSection_id
			";

			$response = $this->queryResult($query, $data);

			// если выбранный врач связан с этим отделением
			if (!empty($response[0])) {
				return true; // проверка пройдена
			} else {
				return false;
			}
		}
	}
}