<?php
/**
 * PlanVolume_model - модель, для работы с плановыми объёмами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      21.11.2018
 */
class PlanVolume_model extends swModel {
	/**
	 * Удаление заявки на плановый объём
	 */
	function deletePlanVolumeRequest($data)
	{
		$queryParams = array(
			'PlanVolumeRequest_id' => $data['id'],
			'pmUser_id' => (!empty($data['pmUser_id']) ? $data['pmUser_id'] : null)
		);

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_PlanVolumeRequest_del
				@PlanVolumeRequest_id = :PlanVolumeRequest_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Получение номера заявки
	 */
	function getPlanVolumeRequestNumber($data)
	{
		$queryParams = array('Lpu_id' => $data['Lpu_id']);

		$query = "
			select
				ISNULL(MAX(PlanVolumeRequest_Num) + 1, 1) as PlanVolumeRequest_Num
			from
				v_PlanVolumeRequest (nolock)
			where
				Lpu_id = :Lpu_id
		";

		$resp = $this->queryResult($query, $queryParams);
		if (!empty($resp[0]['PlanVolumeRequest_Num'])) {
			return array('Error_Msg' => '', 'PlanVolumeRequest_Num' => $resp[0]['PlanVolumeRequest_Num']);
		}

		return false;
	}

	/**
	 * Сохранение заявки на плановый объём
	 */
	function savePlanVolumeRequest($data)
	{
		// проверяем настройку
		$approveMzInLpu = false;
		$resp_ds = $this->queryResult("
			select top 1
				ds.DataStorage_id
			from
				DataStorage ds (nolock)
			where
				ds.DataStorage_Name = 'registry_mz_approve_lpu'
				and ds.DataStorage_Value = '1'
				and ds.Lpu_id is null
		");
		if (!empty($resp_ds[0]['DataStorage_id'])) {
			$approveMzInLpu = true;
		}

		$params = array(
			'PlanVolumeRequest_id' => (!empty($data['PlanVolumeRequest_id']))?$data['PlanVolumeRequest_id']:null,
			'PlanVolumeRequest_Num' => $data['PlanVolumeRequest_Num'],
			'MedicalCareBudgType_id' => $data['MedicalCareBudgType_id'],
			'Lpu_id' => $data['Lpu_id'],
			'PayType_id' => $data['PayType_id'],
			'QuoteUnitType_id' => $data['QuoteUnitType_id'],
			'PlanVolumeRequest_Value' => $data['PlanVolumeRequest_Value'],
			'PlanVolumeRequest_begDT' => $data['PlanVolumeRequest_begDT'],
			'PlanVolumeRequest_endDT' => $data['PlanVolumeRequest_endDT'],
			'PlanVolumeRequest_Comment' => $data['PlanVolumeRequest_Comment'],
			'PlanVolumeRequestStatus_id' => $data['PlanVolumeRequestStatus_id'],
			'PlanVolumeRequestSourceType_id' => $data['PlanVolumeRequestSourceType_id'],
			'PlanVolume_id' => $data['PlanVolume_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$filter_check = "";
		if (!empty($params['PlanVolumeRequest_id'])) {
			$filter_check .= " and PlanVolumeRequest_id <> :PlanVolumeRequest_id";
		}
		if (!empty($params['Lpu_id'])) {
			$filter_check .= " and Lpu_id = :Lpu_id";
		} else {
			$filter_check .= " and Lpu_id IS NULL";
		}

		// проверка
		$resp_check = $this->queryResult("
			select top 1
				PlanVolumeRequest_id
			from
				v_PlanVolumeRequest (nolock)
			where
				MedicalCareBudgType_id = :MedicalCareBudgType_id
				and PayType_id = :PayType_id
				and QuoteUnitType_id = :QuoteUnitType_id
				and (
					(PlanVolumeRequest_begDT >= :PlanVolumeRequest_begDT and PlanVolumeRequest_begDT <= :PlanVolumeRequest_endDT)
					or (PlanVolumeRequest_endDT >= :PlanVolumeRequest_begDT and PlanVolumeRequest_endDT <= :PlanVolumeRequest_endDT)
				)
			    and PlanVolumeRequestStatus_id <> 4
				{$filter_check}
		", $params);

		if (!empty($resp_check[0]['PlanVolumeRequest_id'])) {
			return array('Error_Msg' => 'Сохранение невозможно: уже существует заявка с такими параметрами.');
		}

		if (!empty($data['PlanVolumeRequest_id'])) {
			$procedure = 'p_PlanVolumeRequest_upd';
		} else {
			$procedure = 'p_PlanVolumeRequest_ins';
		}

		$query = "
			declare
                @PlanVolumeRequest_id bigint,
				@Error_Code int,
				@Error_Msg varchar(4000);
            set @PlanVolumeRequest_id = :PlanVolumeRequest_id;
			exec {$procedure}
				@PlanVolumeRequest_id = @PlanVolumeRequest_id output,
				@PlanVolumeRequest_Num = :PlanVolumeRequest_Num,
				@MedicalCareBudgType_id = :MedicalCareBudgType_id,
				@Lpu_id = :Lpu_id,
				@PayType_id = :PayType_id,
				@QuoteUnitType_id = :QuoteUnitType_id,
				@PlanVolumeRequest_Value = :PlanVolumeRequest_Value,
				@PlanVolumeRequest_begDT = :PlanVolumeRequest_begDT,
				@PlanVolumeRequest_endDT = :PlanVolumeRequest_endDT,
				@PlanVolumeRequest_Comment = :PlanVolumeRequest_Comment,
				@PlanVolumeRequestStatus_id = :PlanVolumeRequestStatus_id,
				@PlanVolumeRequestSourceType_id = :PlanVolumeRequestSourceType_id,
				@PlanVolume_id = :PlanVolume_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;
			select @PlanVolumeRequest_id as PlanVolumeRequest_id, @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";

		$resp = $this->queryResult($query, $params);

		if (empty($data['PlanVolumeRequest_id']) && !empty($resp[0]['PlanVolumeRequest_id']) && $data['PlanVolumeRequestSourceType_id'] == 2 && !$approveMzInLpu) {
			// автоматически утверждаем новую заявку от МЗ, т.к. утверждение в МО не требуется
			$this->setPlanVolumeRequestStatus(array(
				'PlanVolumeRequest_id' => $resp[0]['PlanVolumeRequest_id'],
				'PlanVolumeRequestStatus_id' => 3,
				'pmUser_id' => $data['pmUser_id']
			));
		}

		return $resp;
	}

	/**
	 * Загрузка заявки на плановый объём на редактирование
	 */
	function loadPlanVolumeRequestEditWindow($data) {
		return $this->queryResult("
			select
				PlanVolumeRequest_id,
				PlanVolumeRequest_Num,
				MedicalCareBudgType_id,
				Lpu_id,
				PayType_id,
				QuoteUnitType_id,
				convert(varchar(10), PlanVolumeRequest_begDT, 104) as PlanVolumeRequest_begDT,
				convert(varchar(10), PlanVolumeRequest_endDT, 104) as PlanVolumeRequest_endDT,
				PlanVolumeRequest_Value,
				PlanVolumeRequest_Comment,
				PlanVolumeRequestStatus_id,
				PlanVolumeRequestSourceType_id,
				PlanVolume_id
			from
				v_PlanVolumeRequest (nolock)
			where
				PlanVolumeRequest_id = :PlanVolumeRequest_id
		", array(
			'PlanVolumeRequest_id' => $data['PlanVolumeRequest_id']
		));
	}

	/**
	 * Загрузка списка заявок на плановый объём
	 */
	function loadPlanVolumeRequestGrid($data)
	{
		$filter = "1=1";
		$params = array();

		if (!empty($data['Lpu_id'])) {
			$filter .= " and PVR.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		if (!empty($data['Lpu_ids'])) {
			$data['Lpu_ids'] = preg_replace('/[^0-9 ,]/', '', $data['Lpu_ids']);
			if (!empty($data['Lpu_ids'])) {
				$filter .= " and PVR.Lpu_id IN ({$data['Lpu_ids']})";
			}
		}

		if (!empty($data['isClose']) && $data['isClose'] == 1) {
			$filter .= " and (PVR.PlanVolumeRequest_endDT is null or PVR.PlanVolumeRequest_endDT > @curDate)";
		} elseif (!empty($data['isClose']) && $data['isClose'] == 2) {
			$filter .= " and PVR.PlanVolumeRequest_endDT <= @curDate";
		}

		if (!empty($data['MedicalCareBudgType_id'])) {
			$filter .= " and PVR.MedicalCareBudgType_id = :MedicalCareBudgType_id";
			$params['MedicalCareBudgType_id'] = $data['MedicalCareBudgType_id'];
		}

		if (!empty($data['PayType_id'])) {
			$filter .= " and PVR.PayType_id = :PayType_id";
			$params['PayType_id'] = $data['PayType_id'];
		}

		if (!empty($data['QuoteUnitType_id'])) {
			$filter .= " and PVR.QuoteUnitType_id = :QuoteUnitType_id";
			$params['QuoteUnitType_id'] = $data['QuoteUnitType_id'];
		}

		if (!empty($data['Year'])) {
			$filter .= " and YEAR(PVR.PlanVolumeRequest_begDT) = :Year";
			$params['Year'] = $data['Year'];
		}

		if (!empty($data['PlanVolumeRequestStatus_id'])) {
			$filter .= " and PVR.PlanVolumeRequestStatus_id = :PlanVolumeRequestStatus_id";
			$params['PlanVolumeRequestStatus_id'] = $data['PlanVolumeRequestStatus_id'];
		}

		if (!empty($data['PlanVolumeRequestSourceType_id'])) {
			$filter .= " and PVR.PlanVolumeRequestSourceType_id = :PlanVolumeRequestSourceType_id";
			$params['PlanVolumeRequestSourceType_id'] = $data['PlanVolumeRequestSourceType_id'];
		}

		$query = "
			-- variables
			declare @curDate date = dbo.tzGetDate();
			-- end variables
			
			SELECT
			-- select
				PVR.PlanVolumeRequest_id,
				PVR.PlanVolumeRequest_Num,
				MCBT.MedicalCareBudgType_Name,
				PT.PayType_Name,
				QUT.QuoteUnitType_Name,
				L.Lpu_Nick,
				PVR.PlanVolumeRequest_Value,
				convert(varchar(10), PVR.PlanVolumeRequest_begDT, 104) as PlanVolumeRequest_begDT,
				convert(varchar(10), PVR.PlanVolumeRequest_endDT, 104) as PlanVolumeRequest_endDT,
				PVR.PlanVolumeRequest_Comment,
				PV.PlanVolume_Num
			-- end select
			FROM
			-- from
				v_PlanVolumeRequest PVR (nolock)
				left join v_MedicalCareBudgType MCBT (nolock) on MCBT.MedicalCareBudgType_id = PVR.MedicalCareBudgType_id
				left join v_PayType PT (nolock) on PT.PayType_id = PVR.PayType_id
				left join v_QuoteUnitType QUT (nolock) on QUT.QuoteUnitType_id = PVR.QuoteUnitType_id
				left join v_Lpu L (nolock) on L.Lpu_id = PVR.Lpu_id
				left join v_PlanVolume PV (nolock) on PV.PlanVolume_id = PVR.PlanVolume_id
			-- end from
			WHERE
			-- where
				".$filter."
			-- end where
			ORDER BY
			-- order by
				PVR.PlanVolumeRequest_id
			-- end order by
		";

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}

	/**
	 * Загрузка списка плановых объёмов
	 */
	function loadPlanVolumeGrid($data)
	{
		$filter = "1=1";
		$params = array();

		if (!empty($data['Lpu_id'])) {
			$filter .= " and PVR.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		if (!empty($data['Lpu_ids'])) {
			$data['Lpu_ids'] = preg_replace('/[^0-9 ,]/', '', $data['Lpu_ids']);
			if (!empty($data['Lpu_ids'])) {
				$filter .= " and PVR.Lpu_id IN ({$data['Lpu_ids']})";
			}
		}

		if (!empty($data['isClose']) && $data['isClose'] == 1) {
			$filter .= " and (PVR.PlanVolumeRequest_endDT is null or PVR.PlanVolumeRequest_endDT > @curDate)";
		} elseif (!empty($data['isClose']) && $data['isClose'] == 2) {
			$filter .= " and PVR.PlanVolumeRequest_endDT <= @curDate";
		}

		if (!empty($data['MedicalCareBudgType_id'])) {
			$filter .= " and PVR.MedicalCareBudgType_id = :MedicalCareBudgType_id";
			$params['MedicalCareBudgType_id'] = $data['MedicalCareBudgType_id'];
		}

		if (!empty($data['PayType_id'])) {
			$filter .= " and PVR.PayType_id = :PayType_id";
			$params['PayType_id'] = $data['PayType_id'];
		}

		if (!empty($data['QuoteUnitType_id'])) {
			$filter .= " and PVR.QuoteUnitType_id = :QuoteUnitType_id";
			$params['QuoteUnitType_id'] = $data['QuoteUnitType_id'];
		}

		if (!empty($data['PlanVolume_Num'])) {
			$filter .= " and PV.PlanVolume_Num = :PlanVolume_Num";
			$params['PlanVolume_Num'] = $data['PlanVolume_Num'];
		}

		if (!empty($data['Year'])) {
			$filter .= " and YEAR(PVR.PlanVolumeRequest_begDT) = :Year";
			$params['Year'] = $data['Year'];
		}

		$query = "
			-- variables
			declare @curDate date = dbo.tzGetDate();
			-- end variables
			
			SELECT
			-- select
				PV.PlanVolume_id,
				PV.PlanVolume_Num,
				PV.PlanVolumeRequest_id,
				PVR.PlanVolumeRequestSourceType_id,
				PVR.PlanVolumeRequest_Num,
				MCBT.MedicalCareBudgType_Name,
				PT.PayType_Name,
				QUT.QuoteUnitType_Name,
				L.Lpu_Nick,
				PVR.PlanVolumeRequest_Value,
				convert(varchar(10), PVR.PlanVolumeRequest_begDT, 104) as PlanVolumeRequest_begDT,
				convert(varchar(10), PVR.PlanVolumeRequest_endDT, 104) as PlanVolumeRequest_endDT,
				PVR.PlanVolumeRequest_Comment,
				PVRN.PlanVolumeRequest_Num as NextPlanVolumeRequest_Num
			-- end select
			FROM
			-- from
				v_PlanVolume PV (nolock)
				inner join v_PlanVolumeRequest PVR (nolock) on PVR.PlanVolumeRequest_id = PV.PlanVolumeRequest_id
				left join v_MedicalCareBudgType MCBT (nolock) on MCBT.MedicalCareBudgType_id = PVR.MedicalCareBudgType_id
				left join v_PayType PT (nolock) on PT.PayType_id = PVR.PayType_id
				left join v_QuoteUnitType QUT (nolock) on QUT.QuoteUnitType_id = PVR.QuoteUnitType_id
				left join v_Lpu L (nolock) on L.Lpu_id = PVR.Lpu_id
				left join v_PlanVolumeRequest PVRN (nolock) on PVRN.PlanVolume_id = PV.PlanVolume_id
			-- end from
			WHERE
			-- where
				".$filter."
			-- end where
			ORDER BY
			-- order by
				PVR.PlanVolumeRequest_id
			-- end order by
		";

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}

	/**
	 * Сохранение планового объёма
	 */
	function savePlanVolume($data)
	{
		$params = array(
			'PlanVolume_id' => (!empty($data['PlanVolume_id']))?$data['PlanVolume_id']:null,
			'PlanVolume_Num' => $data['PlanVolume_Num'],
			'Lpu_id' => $data['Lpu_id'],
			'PlanVolumeRequest_id' => $data['PlanVolumeRequest_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$filter_check = "";
		if (!empty($params['PlanVolume_id'])) {
			$filter_check .= " and PlanVolume_id <> :PlanVolume_id";
		}

		// проверка
		$resp_check = $this->queryResult("
			select top 1
				PlanVolume_id
			from
				v_PlanVolume (nolock)
			where
				PlanVolume_Num = :PlanVolume_Num
				and PlanVolumeRequest_id = :PlanVolumeRequest_id
				{$filter_check}
		", $params);

		if (!empty($resp_check[0]['PlanVolume_id'])) {
			return array(array('Error_Msg' => 'Сохранение невозможно: уже существует плановый объём с такими параметрами.'));
		}

		if (!empty($data['PlanVolume_id'])) {
			$procedure = 'p_PlanVolume_upd';
		} else {
			$procedure = 'p_PlanVolume_ins';
		}

		if (!empty($params['Lpu_id'])) {
			$filterPlanVolume = 'PVR.Lpu_id = :Lpu_id';
		} else {
			$filterPlanVolume = 'PVR.Lpu_id IS NULL';
		}

		$query = "
			declare
                @PlanVolume_id bigint = :PlanVolume_id,
                @PlanVolume_Num int = :PlanVolume_Num,
				@Error_Code int,
				@Error_Msg varchar(4000);
            
            if (@PlanVolume_Num is null)
			begin
				set @PlanVolume_Num = (
					select
						ISNULL(MAX(PV.PlanVolume_Num) + 1, 1) as PlanVolume_Num
					from
						v_PlanVolume PV (nolock)
						inner join v_PlanVolumeRequest PVR (nolock) on PVR.PlanVolumeRequest_id = PV.PlanVolumeRequest_id
					where
						{$filterPlanVolume}
				);
			end
            
			exec {$procedure}
				@PlanVolume_id = @PlanVolume_id output,
				@PlanVolume_Num = @PlanVolume_Num,
				@PlanVolumeRequest_id = :PlanVolumeRequest_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;
			select @PlanVolume_id as PlanVolume_id, @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Установка статуса заявки
	 */
	function setPlanVolumeRequestStatus($data) {
		$updateFields = "";

		if ($data['PlanVolumeRequestStatus_id'] == 3) {
			// при утверждении необходимо создать плановый объём
			$resp = $this->queryResult("
				select
					PVR.PlanVolumeRequest_id,
					PVR.Lpu_id,
					PV.PlanVolume_id,
					PV.PlanVolume_Num
				from
					v_PlanVolumeRequest PVR (nolock)
					left join v_PlanVolume PV (nolock) on PV.PlanVolume_id = PVR.PlanVolume_id 
				where
					PVR.PlanVolumeRequest_id = :PlanVolumeRequest_id
			", array(
				'PlanVolumeRequest_id' => $data['PlanVolumeRequest_id']
			));

			if (!empty($resp[0]['PlanVolumeRequest_id'])) {
				$resp_save = $this->savePlanVolume(array(
					'PlanVolume_id' => $resp[0]['PlanVolume_id'],
					'PlanVolume_Num' => $resp[0]['PlanVolume_Num'],
					'Lpu_id' => $resp[0]['Lpu_id'],
					'PlanVolumeRequest_id' => $data['PlanVolumeRequest_id'],
					'pmUser_id' => $data['pmUser_id']
				));
				if (!empty($resp_save[0]['Error_Msg'])) {
					return $resp_save;
				}

				if (!empty($resp[0]['PlanVolume_id'])) {
					// для выбранной заявки очищается поле «Исходный плановый объём»;
					$updateFields .= " PlanVolume_id = null,";
				}
			} else {
				return array('Error_Msg' => 'Ошибка получения данных по заявке');
			}
		}

		$this->db->query("
			update
				PlanVolumeRequest with (rowlock)
			set
				PlanVolumeRequestStatus_id = :PlanVolumeRequestStatus_id,
				{$updateFields}
				pmUser_updID = :pmUser_id,
				PlanVolumeRequest_updDT = dbo.tzGetDate()
			where
				PlanVolumeRequest_id = :PlanVolumeRequest_id
		", array(
			'PlanVolumeRequest_id' => $data['PlanVolumeRequest_id'],
			'PlanVolumeRequestStatus_id' => $data['PlanVolumeRequestStatus_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		return array('Error_Msg' => '');
	}
}
