<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * NoticeDiagGroup_model - модель для работы c диагнозами для оповещений по диспансеризации
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2019 Swan Ltd.
 * @version			2019
 */

class NoticeDiagGroup_model extends swModel {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Получение данных для редактирования группы диагнозов
	 */
	function loadNoticeDiagGroupForm($data) {

		$params = array('NoticeDiagGroup_id' => $data['NoticeDiagGroup_id']);

		$query = "
			select top 1
				ndg.NoticeDiagGroup_id,
				ndg.NoticeDiagGroup_Name
			from v_NoticeDiagGroup ndg with(nolock)
			where ndg.NoticeDiagGroup_id = :NoticeDiagGroup_id
		";

		$result = $this->getFirstRowFromQuery($query, $params);

		if (empty($result)) {
			return array(array('Error_Msg' => 'Ошибка получения наименования группы диагнозов'));
		}

		$response = $result;

		$query = "
			select
				ndgl.NoticeDiagGroupLink_id,
				ndgl.NoticeDiagGroupLink_FromDiag_id,
				ndgl.NoticeDiagGroupLink_ToDiag_id,
				1 as RecordStatus_Code
			from v_NoticeDiagGroupLink ndgl with(nolock)
			where ndgl.NoticeDiagGroup_id = :NoticeDiagGroup_id
		";

		$diags = $this->queryResult($query, $params);

		if (empty($diags)) {
			return array(array('Error_Msg' => 'Ошибка получения диагнозов группы'));
		}

		$response['NoticeDiagGroupData'] = $diags;
		return array($response);
	}

	/**
	 * Получение списка групп диагнозов
	 */
	function loadNoticeDiagGroupGrid($data) {
		$params = array();

		$query  = "
			select
				ndg.NoticeDiagGroup_id,
				ndg.NoticeDiagGroup_Name
			from v_NoticeDiagGroup ndg (nolock)
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$response = $result->result('array');

		$query = "
			select
				ndgl.NoticeDiagGroup_id,
				fD.Diag_Code as Diag_fCode,
				tD.Diag_Code as Diag_tCode
			from
				v_NoticeDiagGroupLink ndgl with(nolock)
				left join v_Diag fD with(nolock) on fD.Diag_id = ndgl.NoticeDiagGroupLink_FromDiag_id
				left join v_Diag tD with(nolock) on tD.Diag_id = ndgl.NoticeDiagGroupLink_ToDiag_id
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result('array');
		$code_arr = array();

		foreach($resp as $item) {
			$key = $item['NoticeDiagGroup_id'];
			if (empty($item['Diag_tCode'])) {
				$code_arr[$key][] = $item['Diag_fCode'];
			} else {
				$code_arr[$key][] = $item['Diag_fCode'].' - '.$item['Diag_tCode'];
			}
		}

		foreach($response as &$item) {
			$key = $item['NoticeDiagGroup_id'];
			$item['NoticeDiagGroup_Codes'] = isset($code_arr[$key]) ? implode(', ', $code_arr[$key]) : '';
		}

		return array('data' => $response);
	}

	/**
	 * Сохранение наименования группы для ограничения доступа
	 */
	function save($data) {
		$params = array(
			'NoticeDiagGroup_id' => $data['NoticeDiagGroup_id'],
			'NoticeDiagGroup_Name' => $data['NoticeDiagGroup_Name'],
			'pmUser_id' => $data['pmUser_id']
		);

		$procedure = 'p_NoticeDiagGroup_ins';
		if (!empty($params['NoticeDiagGroup_id'])) {
			$procedure = 'p_NoticeDiagGroup_upd';
		}

		$query = "
			declare
				@NoticeDiagGroup_id bigint = :NoticeDiagGroup_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$procedure}
				@NoticeDiagGroup_id = @NoticeDiagGroup_id output,
				@NoticeDiagGroup_Name = :NoticeDiagGroup_Name,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @NoticeDiagGroup_id as NoticeDiagGroup_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка сохранения наименования группы диагнозов'));
		}
		return $result->result('array');
	}

	/**
	 * Сохранение группы диагнозов
	 */
	function saveNoticeDiagGroup($data) {
		$this->beginTransaction();

		$response = $this->save($data);
		if (!empty($response[0]['Error_Msg'])) {
			$this->rollbackTransaction();
			return $response;
		}

		$NoticeDiagGroupData = json_decode($data['NoticeDiagGroupData'], true);
		foreach($NoticeDiagGroupData as $NoticeDiagGroup) {
			$NoticeDiagGroup['NoticeDiagGroup_id'] = $response[0]['NoticeDiagGroup_id'];
			$NoticeDiagGroup['pmUser_id'] = $data['pmUser_id'];
			$NoticeDiagGroup['allowIntersection'] = $data['allowIntersection'];
			switch($NoticeDiagGroup['RecordStatus_Code']) {
				case 1:
					$resp = true;
					break;
				case 0:
				case 2:
					$resp = $this->saveNoticeDiagGroupLink($NoticeDiagGroup);
					break;
				case 3:
					$resp = $this->deleteNoticeDiagGroup($NoticeDiagGroup);
			}
			if (!empty($resp[0]['Error_Msg']) || !empty($resp[0]['Alert_Msg'])) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		$this->commitTransaction();
		return $response;
	}

	/**
	 * Сохранение диагноза/диапозона диагнозов в группе для ограничения доступа
	 */
	function saveNoticeDiagGroupLink($data) {

		if (empty($data['allowIntersection']) || !$data['allowIntersection']) {
			$check = $this->checkNoticeDiagGroupIntersection($data);
			if (!empty($check['Error_Msg'])) {
				return array($check);
			}
			if (!empty($check['NoticeDiagGroup_Name'])) {
				return array(array(
					'Alert_Msg' => "Указанный диагноз (группа диагнозов) уже имеется в группе диагнозов '{$check['NoticeDiagGroup_Name']}'.",
					'Alert_Code' => 1
				));
			}
		}

		$params = array(
			'NoticeDiagGroupLink_id' => $data['NoticeDiagGroupLink_id'],
			'NoticeDiagGroup_id' => $data['NoticeDiagGroup_id'],
			'NoticeDiagGroupLink_FromDiag_id' => $data['NoticeDiagGroupLink_FromDiag_id'],
			'NoticeDiagGroupLink_ToDiag_id' => $data['NoticeDiagGroupLink_ToDiag_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		if (!empty($params['NoticeDiagGroupLink_id']) && $params['NoticeDiagGroupLink_id'] > 0) {
			$procedure = 'p_NoticeDiagGroupLink_upd';
		} else {
			$params['NoticeDiagGroupLink_id'] = null;
			$procedure = 'p_NoticeDiagGroupLink_ins';
		}

		$query = "
			declare
				@NoticeDiagGroupLink_id bigint = :NoticeDiagGroupLink_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$procedure}
				@NoticeDiagGroupLink_id = @NoticeDiagGroupLink_id output,
				@NoticeDiagGroup_id = :NoticeDiagGroup_id,
				@NoticeDiagGroupLink_FromDiag_id = :NoticeDiagGroupLink_FromDiag_id,
				@NoticeDiagGroupLink_ToDiag_id = :NoticeDiagGroupLink_ToDiag_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @NoticeDiagGroupLink_id as NoticeDiagGroupLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка при сохранении диагнозов в группе'));
		}
		$response = $result->result('array');

		return $response;
	}

	/**
	 * Удаление диагноза/диапозона диагнозов из группы диагнозов
	 */
	function deleteNoticeDiagGroupLink($data) {
		$params = array('NoticeDiagGroupLink_id' => $data['NoticeDiagGroupLink_id']);

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_NoticeDiagGroupLink_del
				@NoticeDiagGroupLink_id = :NoticeDiagGroupLink_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";
		$response = $this->getFirstRowFromQuery($query, $params);
		if (!$response) {
			$response = array('Error_Msg' => 'Ошибка при удалении группы диагнозов');
		}

		return array($response);
	}

	/**
	 * Удаление группы диагнозов
	 */
	function deleteNoticeDiagGroup($data) {
		$params = array('NoticeDiagGroup_id' => $data['NoticeDiagGroup_id']);

		$this->beginTransaction();

		$query = "
			select ndgl.NoticeDiagGroupLink_id
			from v_NoticeDiagGroupLink ndgl (nolock)
			where ndgl.NoticeDiagGroup_id = :NoticeDiagGroup_id
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Ошибка при удалении диагнозов в группе'));
		}
		$diags = $result->result('array');
		foreach($diags as $item) {
			$resp = $this->deleteNoticeDiagGroupLink($item);
			if (!empty($resp[0]['Error_Msg'])) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		$response = $this->delete($data);
		if (!empty($response[0]['Error_Msg'])) {
			$this->rollbackTransaction();
		} else {
			$this->commitTransaction();
		}

		return $response;
	}

	/**
	 * Удаление группы диагнозов
	 */
	function delete($data) {
		$params = array('NoticeDiagGroup_id' => $data['NoticeDiagGroup_id']);

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_NoticeDiagGroup_del
				@NoticeDiagGroup_id = :NoticeDiagGroup_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка при удалении наименования группы диагнозов'));
		}
		return $result->result('array');
	}

	/**
	 * Проверка на пересечениями с другими группами диагнозов
	 */
	function checkNoticeDiagGroupIntersection($data) {
		$params = array(
			'NoticeDiagGroupLink_FromDiag_id' => $data['NoticeDiagGroupLink_FromDiag_id'],
			'NoticeDiagGroupLink_ToDiag_id' => $data['NoticeDiagGroupLink_ToDiag_id'],
			'NoticeDiagGroupLink_id' => $data['NoticeDiagGroupLink_id']
		);

		$query = "
			declare @fid bigint = :NoticeDiagGroupLink_FromDiag_id
			declare @tid bigint = :NoticeDiagGroupLink_ToDiag_id

			declare @fdiag varchar(5) = (select top 1 t.Diag_Code from v_Diag t with(nolock) where t.Diag_id = @fid)
			declare @tdiag varchar(5) = ''
			if @tid is not null set @tdiag = (select top 1 t.Diag_Code from v_Diag t with(nolock) where t.Diag_id = @tid)

			select top 1 ndg.NoticeDiagGroup_Name
			from v_NoticeDiagGroupLink ndgl with(nolock)
				left join v_Diag fD with(nolock) on fD.Diag_id = ndgl.NoticeDiagGroupLink_FromDiag_id
				left join v_Diag tD with(nolock) on tD.Diag_id = ndgl.NoticeDiagGroupLink_ToDiag_id
				left join v_NoticeDiagGroup ndg (nolock) on ndg.NoticeDiagGroup_id = ndgl.NoticeDiagGroup_id
			where
				1=(case
					when tD.Diag_id is null
						then case when fD.Diag_Code >= @fdiag and fD.Diag_Code <= @tdiag then 1 else 0 end
					when fD.Diag_Code > @fdiag and fD.Diag_Code > @tdiag or tD.Diag_Code < @fdiag and (tD.Diag_Code < @tdiag or @tdiag = '')
						then 0 else 1
				end)
				and ndgl.NoticeDiagGroupLink_id != :NoticeDiagGroupLink_id
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при поиске пересечений диагнозов');
		}
		$resp = $result->result('array');
		if (count($resp) == 0){
			$resp = array(array());
		}
		return $resp[0];
	}

	/**
	 * Получение диагнозов для по рассылкам
	 */
	function getNoticeDiagData($data) {

		$query = "
				select
					ndgl.NoticeDiagGroupLink_FromDiag_id as fDiag_id,
					ndgl.NoticeDiagGroupLink_ToDiag_id as tDiag_id,
					fDiag.Diag_Code as fDiag_Code,
					tDiag.Diag_Code as tDiag_Code
				from v_NoticeDiagGroupLink ndgl (nolock)
				left join v_Diag fDiag (nolock) on fDiag.Diag_id = ndgl.NoticeDiagGroupLink_FromDiag_id
				left join v_Diag tDiag (nolock) on tDiag.Diag_id = ndgl.NoticeDiagGroupLink_ToDiag_id
			";

		$diags = $this->queryResult($query, array());
		return $diags;
	}

	/**
	 * Задание отправляет уведомления тем кому необходимо записаться на плановый прием
	 */
	function upcomingDispNotifyTask($data) {

		$this->load->model("Options_model", "Options_model");
		$notification_enabled = $this->Options_model->getOptionsGlobals($data,'notify_on_upcoming_disp_visits');

		$diags = $this->getNoticeDiagData(array());

		if (!empty($diags) && $notification_enabled) {

			$params = array(); $filter = " "; $ranges_filter = array();
			$ranges = array(); $codes = array();

			foreach($diags as $diag) {
				if (!empty($diag['tDiag_id'])) {
					$ranges[] = array($diag['fDiag_Code'], $diag['tDiag_Code']);
				} else {
					$codes[] = $diag['fDiag_Code'];
				}
			}

			if (!empty($codes)) {
				$filter .= " isnull(diag.Diag_Code, '') in('".implode("','",$codes)."') ";
			}

			foreach ($ranges as $range) {
				$ranges_filter[] = " isnull(diag.Diag_Code, '') between '{$range[0]}' and '{$range[1]}' ";
			}

			if (!empty($ranges_filter)) $ranges_filter = implode(' or ', $ranges_filter);

			if (!empty($codes) && !empty($ranges_filter)) {
				$filter = " and (".$filter. " or ".$ranges_filter. ")";
			} else if (empty($ranges_filter)) {
				$filter = " and (".$filter. ")";
			} else if (empty($codes)) {
				$filter = " and (".$ranges_filter. ")";
			}

			$query = "
				select top 1000
					disp.Person_id,
					diag.Diag_id,
					diag.Diag_Code,
					pdh.MedPersonal_Fio,
					pdh.Doctor_FullName,
					pdh.ProfileSpec_Name_Rod,
					pdv.PersonDispVizit_NextDate
				from v_PersonState_All PS (nolock)
				inner join v_PersonDisp disp (nolock) on disp.Person_id = PS.Person_id  
				left join v_Diag diag (nolock) on diag.Diag_id = disp.Diag_id
				outer apply(
					select top 1
						pdh.PersonDispHist_id,
						pdh.MedPersonal_id,
						D.Person_Fio as MedPersonal_Fio,
						D.Doctor_FullName,
						lsp.ProfileSpec_Name_Rod
					from v_PersonDispHist pdh (nolock)
						outer apply (
							select top 1 
								D2.Person_Fio,
								rtrim(D2.Person_SurName)+' '+SUBSTRING(rtrim(D2.Person_FirName), 1, 1)+'.'+isnull(SUBSTRING(rtrim(D2.Person_SecName), 1, 1)+'.', '') as Doctor_FullName 
							from v_MedPersonal D2 (nolock) 
							where D2.MedPersonal_id = pdh.MedPersonal_id
						) D
						left join v_LpuSection ls (nolock) on pdh.LpuSection_id  = ls.LpuSection_id
						left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id  = ls.LpuSectionProfile_id
					where (1=1)
						and pdh.PersonDisp_id = disp.PersonDisp_id
						and PersonDispHist_endDate is null
					order by pdh.PersonDispHist_id desc
				) as pdh
				outer apply(	
					select top 1
						pdv.PersonDispVizit_id,
						pdv.PersonDispVizit_NextDate
					from v_PersonDispVizit pdv (nolock)
					where (1=1)
						and pdv.PersonDisp_id = disp.PersonDisp_id
						and pdv.PersonDispVizit_NextFactDate is null
					order by pdv.PersonDispVizit_id desc
				) as pdv
				where (1=1)
					{$filter}
					and cast(dateadd(day, 13, dbo.tzGetDate()) as date) = cast(pdv.PersonDispVizit_NextDate as date)
					and pdh.MedPersonal_Fio is not null
			";

			$visits = $this->queryResult($query, $params);
			foreach ($visits as $visit_data) {

				// отправляем оповещения пользователям портала
				$this->load->model("UserPortalNotifications_model");
				$this->UserPortalNotifications_model->send(
					array(
						'notify_object' => 'disp',
						'notify_action' => 'upcoming_visit',
						'Person_id' => $visit_data['Person_id'],
						'profile' => mb_strtolower($visit_data['ProfileSpec_Name_Rod']),
						'doctor_fio' => $visit_data['Doctor_FullName'],
						'doctor_fullfio' => $visit_data['MedPersonal_Fio']
					)
				);
			}
		}
	}
}