<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * VoteListVK_model - модель
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @access			public
 */

class VoteListVK_model extends swModel {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Загрузка списка
	 */
	function loadList($data) {
		
		return $this->queryResult("
			select 
				vek.VoteExpertVK_id,
				vek.MedServiceMedPersonal_id,
				MP.Person_Fio as MF_Person_FIO,
				EMSF.ExpertMedStaffType_Name,
				case when vek.ExpertMedStaffType_id = 1 then 2 else 1 end as EvnVKExpert_IsChairman,
				convert(varchar(10), cast(vek.VoteExpertVK_VoteDate as datetime), 104) as VoteExpertVK_VoteDate,
				vek.VoteExpertVK_isApproved as EvnVKExpert_isApproved,
				vek.VoteExpertVK_isInternalRequest,
				isApproved.YesNo_Name as EvnVKExpert_isApprovedName,
				vek.VoteExpertVK_Descr as EvnVKExpert_Descr,
				vek.ExpertMedStaffType_id,
				case
					when vek.VoteExpertVK_isApproved is not null or vek.VoteExpertVK_isInternalRequest is not null then 
						convert(varchar(10), cast(vek.VoteExpertVK_updDT as datetime), 104) 
					else ''
				end as VoteExpertVK_updDT,
				1 as RecordStatus_Code
			from v_VoteExpertVK vek (nolock)
				inner join v_VoteListVK vvk (nolock) on vvk.VoteListVK_id = vek.VoteListVK_id
				left join v_ExpertMedStaffType EMSF with(nolock) on EMSF.ExpertMedStaffType_id = vek.ExpertMedStaffType_id
				left join v_MedServiceMedPersonal MSMP with(nolock) on MSMP.MedServiceMedPersonal_id = vek.MedServiceMedPersonal_id
				outer apply (
					select top 1 Person_Fio
					from v_MedPersonal MP (nolock)
					where MP.MedPersonal_id = MSMP.MedPersonal_id
				) MP
				left join YesNo isApproved with(nolock) on isApproved.YesNo_id = vek.VoteExpertVK_isApproved
			where 
				vvk.EvnPrescrVK_id = :EvnPrescrVK_id
		", $data);
	}
	
	/**
	 * Сохранение 
	 */
	function save($data) {
		
		$data['VoteListVK_id'] = $this->getFirstResultFromQuery("
			select top 1 VoteListVK_id from v_VoteListVK (nolock) where EvnPrescrVK_id = :EvnPrescrVK_id
		", $data, true);

		$proc = empty($data['VoteListVK_id']) ? 'p_VoteListVK_ins' : 'p_VoteListVK_upd';
		$resp = $this->execCommonSP($proc, [
			'VoteListVK_id' => [
				'value' => $data['VoteListVK_id'],
				'out' => true,
				'type' => 'bigint',
			],
			'EvnPrescrVK_id' => $data['EvnPrescrVK_id'],
			'pmUser_id' => $data['pmUser_id']
		], 'array_assoc');
		
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}
		
		if (empty($data['VoteListVK_id'])) {
			$this->execCommonSP('p_Evn_setStatus', [
				'Evn_id' => $data['EvnPrescrVK_id'],
				'EvnStatus_SysNick' => 'AssignCommission',
				'EvnClass_id' => 73,
				'EvnStatusHistory_Cause' => null,
				'pmUser_id' => $data['pmUser_id']
			], 'array_assoc');
		}
		
		$data['VoteListVK_id'] = $resp['VoteListVK_id'];
		$this->saveVoteExpertVKData($data);
		
		return $resp;
	}
	
	/**
	 * Сохранение эксперта
	 */
	function saveVoteExpertVKData($data) {
		foreach($data['GridData'] as $VoteExpertVK) {
			$VoteExpertVK = (array)$VoteExpertVK;
			$VoteExpertVK['VoteListVK_id'] = $data['VoteListVK_id'];
			$VoteExpertVK['pmUser_id'] = $data['pmUser_id'];
			switch($VoteExpertVK['RecordStatus_Code']) {
				case 0:
				case 2:
					$resp = $this->saveVoteExpertVK($VoteExpertVK);
					break;
				case 3:
					$resp = $this->deleteVoteExpertVK($VoteExpertVK);
			}
		}
	}
	
	/**
	 * Сохранение эксперта
	 */
	function saveVoteExpertVK($data) {
		$proc = $data['VoteExpertVK_id'] < 0 ? 'p_VoteExpertVK_ins' : 'p_VoteExpertVK_upd';
		$this->execCommonSP($proc, [
			'VoteExpertVK_id' => $data['VoteExpertVK_id'] > 0 ? $data['VoteExpertVK_id'] : null,
			'VoteListVK_id' => $data['VoteListVK_id'],
			'MedServiceMedPersonal_id' => $data['MedServiceMedPersonal_id'],
			'ExpertMedStaffType_id' => $data['ExpertMedStaffType_id'],
			'VoteExpertVK_VoteDate' => !empty($data['VoteExpertVK_VoteDate']) ? date('Y-m-d', strtotime($data['VoteExpertVK_VoteDate'])) : null,
			'pmUser_id' => $data['pmUser_id']
		], 'array_assoc');
	}
	
	/**
	 * Удаление эксперта
	 */
	function deleteVoteExpertVK($data) {
		$this->execCommonSP('p_VoteExpertVK_del', [
			'VoteExpertVK_id' => $data['VoteExpertVK_id']
		], 'array_assoc');
	}
	
	/**
	 * Загрузка решения эксперта
	 */
	function getDecision($data) {
		
		return $this->queryResult("
			select 
				vek.VoteExpertVK_id,
				case when vek.VoteExpertVK_isInternalRequest = 2 then 'true' else 'false' end as VoteExpertVK_isInternalRequest,
				vek.VoteExpertVK_isApproved,
				vek.VoteExpertVK_Descr
			from v_VoteExpertVK vek (nolock) 
			where VoteExpertVK_id = :VoteExpertVK_id
		", $data);
	}
	
	/**
	 * Сохранение решения эксперта
	 */
	function saveDecision($data) {
		
		$result = $this->getFirstRowFromQuery("
			select 
				vek.VoteListVK_id,
				vek.MedServiceMedPersonal_id,
				vek.ExpertMedStaffType_id,
				convert(varchar(10), cast(vek.VoteExpertVK_VoteDate as datetime), 120) as VoteExpertVK_VoteDate,
				epvk.EvnPrescrVK_id,
				epvk.EvnStatus_id
			from v_VoteExpertVK vek (nolock) 
			inner join v_VoteListVK vlk (nolock) on vlk.VoteListVK_id = vek.VoteListVK_id
			inner join v_EvnPrescrVK epvk (nolock) on epvk.EvnPrescrVK_id = vlk.EvnPrescrVK_id
			where VoteExpertVK_id = :VoteExpertVK_id
		", $data);
		
		if ($result === false) {
			throw new Exception('Не удалось получить данные эксперта', 500);
		}
		
		$data = array_merge($result, $data);
		
		$resp = $this->execCommonSP('p_VoteExpertVK_upd', [
			'VoteExpertVK_id' => $data['VoteExpertVK_id'],
			'VoteListVK_id' => $data['VoteListVK_id'],
			'MedServiceMedPersonal_id' => $data['MedServiceMedPersonal_id'],
			'ExpertMedStaffType_id' => $data['ExpertMedStaffType_id'],
			'VoteExpertVK_VoteDate' => $data['VoteExpertVK_VoteDate'],
			'VoteExpertVK_isApproved' => $data['VoteExpertVK_isApproved'],
			'VoteExpertVK_isInternalRequest' => $data['VoteExpertVK_isInternalRequest'],
			'VoteExpertVK_Descr' => $data['VoteExpertVK_Descr'],
			'pmUser_id' => $data['pmUser_id']
		], 'array_assoc');
		
		$this->processVote($data);
		
		return $resp;
	}
	
	/**
	 * Обработка результатов голосования
	 */
	private function processVote($data) {
		
		// Если все члены комиссии вынесли свое решение по направлению на ВК (VoteExpertVK_isApproved данной комиссии имеют любое значение not null), 
		// то объекту «Комиссия по рассмотрению направления на ВК» устанавливается признак завершения голосования (VoteListVK_isFinished)
		$result = $this->getFirstRowFromQuery("
			select top 1 VoteExpertVK_id
			from v_VoteExpertVK (nolock) 
			where 
				VoteListVK_id = :VoteListVK_id and 
				VoteExpertVK_isApproved is null
		", $data);
		
		if ($result === false) {
			$this->db->query("
				update 
					VoteListVK with(rowlock) 
				set 
					VoteListVK_isFinished = 2,
					VoteListVK_updDT = dbo.tzGetDate(),
					pmUser_updID = :pmUser_id
				where 
					VoteListVK_id = :VoteListVK_id
			", [
				'VoteListVK_id' => $data['VoteListVK_id'],
				'pmUser_id' => $data['pmUser_id'],
			]);
			
			return;
		}
		
		// Если в решении эксперта установлен флаг «Запросить очную экспертизу» и текущий статус Направления на ВК «Назначен состав комиссии», 
		// То, статус направления на ВК изменяется на «Запрошена очная экспертиза».
		if ($data['EvnStatus_id'] == 49 && $data['VoteExpertVK_isInternalRequest'] == 2) {
			$this->execCommonSP('p_Evn_setStatus', [
				'Evn_id' => $data['EvnPrescrVK_id'],
				'EvnStatus_SysNick' => 'RequestExpertise',
				'EvnClass_id' => 73,
				'EvnStatusHistory_Cause' => null,
				'pmUser_id' => $data['pmUser_id']
			], 'array_assoc');
			
			return;
		}
		
		// Если текущий статус Направления на ВК «Запрошена очная экспертиза» и ни у одного решения членов комиссии не установлен признак запроса очной экспертизы (VoteExpertVK_isInternalRequest), 
		// То, статус направления на ВК изменяется на «Назначен состав комиссии». 
		if ($data['EvnStatus_id'] == 50) {
			$result = $this->getFirstRowFromQuery("
				select top 1 VoteExpertVK_id
				from v_VoteExpertVK (nolock) 
				where 
					VoteListVK_id = :VoteListVK_id and 
					VoteExpertVK_isInternalRequest = 2
			", $data);
			
			if ($result === false) {
				$this->execCommonSP('p_Evn_setStatus', [
					'Evn_id' => $data['EvnPrescrVK_id'],
					'EvnStatus_SysNick' => 'AssignCommission',
					'EvnClass_id' => 73,
					'EvnStatusHistory_Cause' => null,
					'pmUser_id' => $data['pmUser_id']
				], 'array_assoc');
			}
			
			return;
		}
	}
	
	/**
	 * Удаление 
	 */
	function delete($data) {

	}
	
	/**
	 * Уведомления членов врачебной комиссии, просрочивших согласование направлений на ВК
	 */
	function sendVoteListVKNotice() {
		
		$this->load->model('Messages_model');
		
		$result = $this->queryResult("
			declare @curDt date = dbo.tzGetDate(); 
			
			select 
				epvk.Lpu_id,
				msmp.MedPersonal_id,
				convert(varchar(10), cast(epvk.EvnPrescrVK_setDate as datetime), 104) as EvnPrescrVK_setDate,
				convert(varchar(10), cast(vek.VoteExpertVK_VoteDate as datetime), 104) as VoteExpertVK_VoteDate,
				(ps.Person_SurName + ' ' + ps.Person_FirName + ' ' + isnull(ps.Person_SecName, '')) as Person_FullName 
			from v_VoteExpertVK vek (nolock)
				inner join v_VoteListVK vlk (nolock) on vlk.VoteListVK_id = vek.VoteListVK_id
				inner join v_EvnPrescrVK epvk (nolock) on epvk.EvnPrescrVK_id = vlk.EvnPrescrVK_id
				inner join v_PersonState ps (nolock) on ps.Person_id = epvk.Person_id
				inner join v_MedServiceMedPersonal msmp (nolock) on msmp.MedServiceMedPersonal_id = vek.MedServiceMedPersonal_id
			where 
				vek.VoteExpertVK_isApproved is null and
				vek.VoteExpertVK_isInternalRequest is null and
				vek.VoteExpertVK_VoteDate < @curDt
		");
		
		foreach($result as $row) {
			$noticeData = array(
				'autotype' => 3,
				'Lpu_rid' => $row['Lpu_id'],
				'MedPersonal_rid' => $row['MedPersonal_id'],
				'pmUser_id' => 1,
				'type' => 1,
				'title' => 'Просрок рассмотрения Направлений на ВК',
				'text' => "Просрочена дата вынесения решения по Направлению на ВК от {$row['EvnPrescrVK_setDate']} пациента {$row['Person_FullName']}. Указанный срок рассмотрения: {$row['VoteExpertVK_VoteDate']}"
			);
			
			$this->Messages_model->autoMessage($noticeData);
		}
	}
}