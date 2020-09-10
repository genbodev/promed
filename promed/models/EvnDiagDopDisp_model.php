<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnDiagDopDisp_model - модель для работы с записями в 'Ранее известные имеющиеся заболевания' / 'Впервые выявленные заболевания'
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      02.07.2013
*/

class EvnDiagDopDisp_model extends CI_Model
{
	/**
	 *	Конструктор
	 */
    function __construct()
    {
        parent::__construct();
    }

	/**
	 *	Загрузка грида
	 */	
	function loadEvnDiagDopDispGrid($data)
	{
		$filter = " and EDDD.EvnDiagDopDisp_pid = :EvnPLDisp_id and EDDD.DeseaseDispType_id = :DeseaseDispType_id";
		
		if (!empty($data['EvnDiagDopDisp_id'])) {
			$filter = " and EDDD.EvnDiagDopDisp_id = :EvnDiagDopDisp_id";
		}
		
		$query = "
			select 
				EDDD.EvnDiagDopDisp_pid as EvnPLDisp_id,
				EDDD.EvnDiagDopDisp_pid,
				convert(varchar(10), EDDD.EvnDiagDopDisp_setDate, 104) as EvnDiagDopDisp_setDate,
				EDDD.EvnDiagDopDisp_id,
				EDDD.PersonEvn_id,
				EDDD.Server_id,
				EDDD.DiagSetClass_id,
				EDDD.EvnDiagDopDisp_IsSystemDataAdd,
				DSC.DiagSetClass_Name,
				EDDD.Diag_id,
				D.Diag_Name,
				EDDD.Lpu_id
			from
				v_EvnDiagDopDisp EDDD (nolock)
				left join v_DiagSetClass DSC (nolock) on DSC.DiagSetClass_id = EDDD.DiagSetClass_id
				left join v_Diag D (nolock) on D.Diag_id = EDDD.Diag_id
			where
				(1=1) {$filter}
			order by
				EDDD.EvnDiagDopDisp_id
		";
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Загрузка грида
	 */
	function loadEvnDiagDopDispSoputGrid($data)
	{
		$query = "
			select
				EDDD.EvnDiagDopDisp_id,
				EDDD.Diag_id,
				D.Diag_Code,
				D.Diag_Name,
				EDDD.DeseaseDispType_id,
				DDT.DeseaseDispType_Name,
				1 as Record_Status
			from
				v_EvnDiagDopDisp EDDD (nolock)
				left join v_DeseaseDispType DDT (nolock) on DDT.DeseaseDispType_id = EDDD.DeseaseDispType_id
				left join v_Diag D (nolock) on D.Diag_id = EDDD.Diag_id
			where
				EDDD.EvnDiagDopDisp_pid = :EvnDiagDopDisp_pid
				and EDDD.DiagSetClass_id = 3
			order by
				EDDD.EvnDiagDopDisp_id
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}
	
	/**
	 *	Загрузка грида
	 */	
	function loadEvnDiagDopDispAndRecomendationGrid($data)
	{
		$filter = " and EDDD.EvnDiagDopDisp_pid = :EvnPLDisp_id";
		
		if (!empty($data['EvnDiagDopDisp_id'])) {
			$filter = " and EDDD.EvnDiagDopDisp_id = :EvnDiagDopDisp_id";
		}
		
		$query = "
			select
				EDDD.EvnDiagDopDisp_id,
				EDDD.PersonEvn_id,
				EDDD.Server_id,
				EDDD.EvnDiagDopDisp_pid,
				EDDD.HTMRecomType_id,
				EDDD.EvnDiagDopDisp_IsVMP,
				D.Diag_id,
				D.Diag_Code,
				D.Diag_Code + '. ' + D.Diag_Name as Diag_Name,
				EDDD.DispSurveilType_id,
				EDDD.DeseaseDispType_id,
				DST.DispSurveilType_Name,
				DDT.YesNo_Name as DeseaseDispType_Name,
				ISNULL(MC1.ConditMedCareType_nid,1) as ConditMedCareType1_nid,
				MC1.PlaceMedCareType_nid as PlaceMedCareType1_nid,
				MC1.ConditMedCareType_id as ConditMedCareType1_id,
				MC1.PlaceMedCareType_id as PlaceMedCareType1_id,
				MC1.LackMedCareType_id as LackMedCareType1_id,
				ISNULL(MC2.ConditMedCareType_nid,1) as ConditMedCareType2_nid,
				MC2.PlaceMedCareType_nid as PlaceMedCareType2_nid,
				MC2.ConditMedCareType_id as ConditMedCareType2_id,
				MC2.PlaceMedCareType_id as PlaceMedCareType2_id,
				MC2.LackMedCareType_id as LackMedCareType2_id,
				ISNULL(MC3.ConditMedCareType_nid,1) as ConditMedCareType3_nid,
				MC3.PlaceMedCareType_nid as PlaceMedCareType3_nid,
				MC3.ConditMedCareType_id as ConditMedCareType3_id,
				MC3.PlaceMedCareType_id as PlaceMedCareType3_id,
				MC3.LackMedCareType_id as LackMedCareType3_id
			from v_EvnDiagDopDisp EDDD (nolock)
				left join v_Diag D (nolock) on D.Diag_id = EDDD.Diag_id
				left join v_DispSurveilType DST (nolock) on DST.DispSurveilType_id = EDDD.DispSurveilType_id
				left join v_YesNo DDT (nolock) on DDT.YesNo_id = EDDD.DeseaseDispType_id
				outer apply(
					select top 1 * from v_MedCare MC (nolock) where MC.MedCareType_id = 1 and MC.EvnDiagDopDisp_id = EDDD.EvnDiagDopDisp_id
				) MC1
				-- лечение
				outer apply(
					select top 1 * from v_MedCare MC (nolock) where MC.MedCareType_id = 2 and MC.EvnDiagDopDisp_id = EDDD.EvnDiagDopDisp_id
				) MC2
				-- медицинская реабилитация / санаторно-курортное лечение
				outer apply(
					select top 1 * from v_MedCare MC (nolock) where MC.MedCareType_id = 3 and MC.EvnDiagDopDisp_id = EDDD.EvnDiagDopDisp_id
				) MC3
			where
				(1=1) {$filter}
			order by
				EDDD.EvnDiagDopDisp_id
		";
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Проверка существования
	 */	
	function checkEvnDiagDopDispExists($data) {
		$query = "
			select top 1
				EvnDiagDopDisp_id
			from
				v_EvnDiagDopDisp (nolock)
			where
				EvnDiagDopDisp_pid = :EvnDiagDopDisp_pid
				and Diag_id = :Diag_id
				and DeseaseDispType_id = :DeseaseDispType_id
				and EvnDiagDopDisp_id <> ISNULL(:EvnDiagDopDisp_id,0)
		";
		
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return false;
			}
		}
		
		return true;
	}

	/**
	 *	Проверка существования
	 */
	function loadEvnDiagDopDispEditForm($data) {
		$query = "
			select
				EvnDiagDopDisp_id,
				EvnDiagDopDisp_pid,
				PersonEvn_id,
				Server_id,
				DiagSetClass_id,
				Diag_id,
				DeseaseDispType_id
			from
				v_EvnDiagDopDisp (nolock)
			where
				EvnDiagDopDisp_id = :EvnDiagDopDisp_id
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}

		return true;
	}

	/**
	 *	Проверка наличия карты диспансеризации при определенной группе диагноза
	 */
	function checkDiagDisp($data) {
		if (empty($data['Person_id']) && !empty($data['PersonEvn_id'])) {
			$query_person = "
			select top 1
				Person_id
			from
				v_PersonEvn with (nolock)
			where
				PersonEvn_id = :PersonEvn_id
		";
			$response_person = $this->db->query($query_person, $data);
			if (is_object($response_person)) {
				$response_person = $response_person->result('array');
				$data['Person_id'] = $response_person[0]['Person_id'];
			}
		}
		if (empty($data['Person_id'])) {
			throw new Exception('Не заданы идентификатор пациента и идентификатор события');
		}
		
		// ищем прикрепление
		$query_attach = "
				select top 1
					PersonCard_id
				from
					v_PersonCard with (nolock)
				where
					Lpu_id = :Lpu_id and Person_id = :Person_id
			";
		
		$response_attach = $this->db->query($query_attach, $data);
		if (is_object($response_attach) && !empty($response_attach->result('array'))) {
			// если прикрепление есть, проверяем диагноз
			$query_diag = "
					select top 1
						DispSickDiag_id
					from
						v_DispSickDiag with (nolock)
					where
						Diag_id = :Diag_id
				";
			
			$response_diag = $this->db->query($query_diag, $data);
			
			if (is_object($response_diag) && !empty($response_diag->result('array'))) {
				// если диагноз входит в список, проверяем карту диспансерного наблюдения
				$query_disp_card = "
						declare
							@date date = dbo.tzGetDate();
						
						select top 1
							 PersonDisp_id
						from
							v_PersonDisp with (nolock)
						where
							Person_id = :Person_id
							and Lpu_id = :Lpu_id
							and COALESCE(:Date, @date) between PersonDisp_begDate and COALESCE(PersonDisp_endDate, @date)
							and Diag_id = :Diag_id
					";
				
				$response_disp_card = $this->db->query($query_disp_card, $data);
				
				if (is_object($response_disp_card) && empty($response_disp_card->result('array'))) {
					return array('success' => true, 'result' => false, 'Person_id' => $data['Person_id']);
				}
			}
		}

		return array('success' => true, 'result' => true, 'Person_id' => $data['Person_id']);
	}
	
	/**
	 *	Удаление
	 */	
	function delEvnDiagDopDisp($data) {
		$sql = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnDiagDopDisp_del
				@EvnDiagDopDisp_id = :EvnDiagDopDisp_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
            select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
   		$res = $this->db->query($sql, $data);
		if ( is_object($res) ) {
 	    	return $res->result('array');
		} else {
 	    	return false;
		}
	}
	
	/**
	 *	Сохранение
	 */	
	function saveEvnDiagDopDisp($data) {
		if (!empty($data['EvnDiagDopDisp_id']) && $data['EvnDiagDopDisp_id'] > 0) {
			$proc = "p_EvnDiagDopDisp_upd";
		} else {
			$proc = "p_EvnDiagDopDisp_ins";
		}

		if (!isset($data['EvnDiagDopDisp_IsSystemDataAdd'])) {
			$data['EvnDiagDopDisp_IsSystemDataAdd'] = 1;
		}
		
		$sql = "
			declare
				@EvnDiagDopDisp_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @EvnDiagDopDisp_id = :EvnDiagDopDisp_id;
			exec {$proc}
				@EvnDiagDopDisp_id = @EvnDiagDopDisp_id output,
				@EvnDiagDopDisp_setDT = :EvnDiagDopDisp_setDate,
				@EvnDiagDopDisp_pid = :EvnDiagDopDisp_pid,
				@Diag_id = :Diag_id,
				@DiagSetClass_id = :DiagSetClass_id,
				@DeseaseDispType_id = :DeseaseDispType_id,
				@EvnDiagDopDisp_IsSystemDataAdd = :EvnDiagDopDisp_IsSystemDataAdd,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
            select @EvnDiagDopDisp_id as EvnDiagDopDisp_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
   		$res = $this->db->query($sql, $data);
		if ( is_object($res) ) {
 	    	return $res->result('array');
		} else {
 	    	return false;
		}
	}
	
	/**
	 *	Получение идентификатора медицинской помощи
	 */
	function getMedCareForEvnDiagDopDisp($EvnDiagDopDisp_id, $MedCareType_id) {
		$query = "
			select top 1
				MedCare_id
			from
				v_MedCare (nolock)
			where
				EvnDiagDopDisp_id = :EvnDiagDopDisp_id
				and MedCareType_id = :MedCareType_id
		";
		
		$result = $this->db->query($query, array(
			'EvnDiagDopDisp_id' => $EvnDiagDopDisp_id,
			'MedCareType_id' => $MedCareType_id
		));
		
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return $resp[0]['MedCare_id'];
			}
		}
		
		return null;
	}
	
	/**
	 *	Сохранение оказания медицинской помощи
	 */
	function saveMedCare($data) {
		if (!empty($data['MedCare_id']) && $data['MedCare_id'] > 0) {
			$proc = 'p_MedCare_upd';
		} else {
			$proc = 'p_MedCare_ins';
			$data['MedCare_id'] = null;
		}
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :MedCare_id;
			
			exec {$proc}
				@MedCare_id = @Res output, 
				@EvnDiagDopDisp_id = :EvnDiagDopDisp_id,
				@LackMedCareType_id = :LackMedCareType_id,
				@ConditMedCareType_nid = :ConditMedCareType_nid,
				@PlaceMedCareType_nid = :PlaceMedCareType_nid,
				@ConditMedCareType_id = :ConditMedCareType_id,
				@PlaceMedCareType_id = :PlaceMedCareType_id,
				@MedCareType_id = :MedCareType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as MedCare_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		// echo getDebugSql($query, $data); die();
		$result = $this->db->query($query, $data);
		
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *	Сохранение
	 */	
	function saveEvnDiagDopDispAndRecomendation($data) {
		if (!empty($data['EvnDiagDopDisp_id']) && $data['EvnDiagDopDisp_id'] > 0) {
			$proc = "p_EvnDiagDopDisp_upd";
		} else {
			$proc = "p_EvnDiagDopDisp_ins";
		}
		
		$sql = "
			declare
				@EvnDiagDopDisp_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@curDate date = dbo.tzGetDate();
			set @EvnDiagDopDisp_id = :EvnDiagDopDisp_id;
			exec {$proc}
				@EvnDiagDopDisp_id = @EvnDiagDopDisp_id output,
				@EvnDiagDopDisp_setDT = @curDate,
				@EvnDiagDopDisp_pid = :EvnDiagDopDisp_pid,
				@Diag_id = :Diag_id,
				@DiagSetClass_id = 1,
				@DeseaseDispType_id = :DeseaseDispType_id,
				@DispSurveilType_id = :DispSurveilType_id,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@HTMRecomType_id = :HTMRecomType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
            select @EvnDiagDopDisp_id as EvnDiagDopDisp_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
   		$res = $this->db->query($sql, $data);
		if ( is_object($res) ) {
 	    	$resp = $res->result('array');
			if (!empty($resp[0]['EvnDiagDopDisp_id'])) {
				$MedCare_id = $this->getMedCareForEvnDiagDopDisp($resp[0]['EvnDiagDopDisp_id'], 1);
				$this->saveMedCare(array(
					'MedCare_id' => $MedCare_id,
					'EvnDiagDopDisp_id' => $resp[0]['EvnDiagDopDisp_id'],
					'ConditMedCareType_nid' => empty($data['ConditMedCareType1_nid'])?null:$data['ConditMedCareType1_nid'],
					'PlaceMedCareType_nid' => empty($data['PlaceMedCareType1_nid'])?null:$data['PlaceMedCareType1_nid'],
					'ConditMedCareType_id' => empty($data['ConditMedCareType1_id'])?null:$data['ConditMedCareType1_id'],
					'PlaceMedCareType_id' => empty($data['PlaceMedCareType1_id'])?null:$data['PlaceMedCareType1_id'],
					'LackMedCareType_id' => empty($data['LackMedCareType1_id'])?null:$data['LackMedCareType1_id'],
					'MedCareType_id' => 1,
					'pmUser_id' => $data['pmUser_id']
				));

				// получаем MedCare_id для MedCareType_id = 2
				$MedCare_id = $this->getMedCareForEvnDiagDopDisp($resp[0]['EvnDiagDopDisp_id'], 2);
				$this->saveMedCare(array(
					'MedCare_id' => $MedCare_id,
					'EvnDiagDopDisp_id' => $resp[0]['EvnDiagDopDisp_id'],
					'ConditMedCareType_nid' => empty($data['ConditMedCareType2_nid'])?null:$data['ConditMedCareType2_nid'],
					'PlaceMedCareType_nid' => empty($data['PlaceMedCareType2_nid'])?null:$data['PlaceMedCareType2_nid'],
					'ConditMedCareType_id' => empty($data['ConditMedCareType2_id'])?null:$data['ConditMedCareType2_id'],
					'PlaceMedCareType_id' => empty($data['PlaceMedCareType2_id'])?null:$data['PlaceMedCareType2_id'],
					'LackMedCareType_id' => empty($data['LackMedCareType2_id'])?null:$data['LackMedCareType2_id'],
					'MedCareType_id' => 2,
					'pmUser_id' => $data['pmUser_id']
				));

				// получаем MedCare_id для MedCareType_id = 3
				$MedCare_id = $this->getMedCareForEvnDiagDopDisp($resp[0]['EvnDiagDopDisp_id'], 3);
				$this->saveMedCare(array(
					'MedCare_id' => $MedCare_id,
					'EvnDiagDopDisp_id' => $resp[0]['EvnDiagDopDisp_id'],
					'ConditMedCareType_nid' => empty($data['ConditMedCareType3_nid'])?null:$data['ConditMedCareType3_nid'],
					'PlaceMedCareType_nid' => empty($data['PlaceMedCareType3_nid'])?null:$data['PlaceMedCareType3_nid'],
					'ConditMedCareType_id' => empty($data['ConditMedCareType3_id'])?null:$data['ConditMedCareType3_id'],
					'PlaceMedCareType_id' => empty($data['PlaceMedCareType3_id'])?null:$data['PlaceMedCareType3_id'],
					'LackMedCareType_id' => empty($data['LackMedCareType3_id'])?null:$data['LackMedCareType3_id'],
					'MedCareType_id' => 3,
					'pmUser_id' => $data['pmUser_id']
				));
			}
			
			return $resp;
		} else {
 	    	return false;
		}
	}
	
	/**
	 *	Добавление впервые выявленного
	 */	
	function addEvnDiagDopDispFirst($data, $diag_id) {
		// проверяем есть ли такой диагноз уже, если нет, то добавляем
		if (!empty($diag_id)) {
			$data['Diag_id'] = $diag_id;
			$query = "
				select top 1
					EvnDiagDopDisp_id
				from v_EvnDiagDopDisp (nolock)
				where EvnDiagDopDisp_pid = :EvnPLDisp_id and Diag_id = :Diag_id and DeseaseDispType_id = 2
			";
			
			$result = $this->db->query($query, $data);

			if (is_object($result))
			{
				$resp = $result->result('array');
				if (count($resp) > 0) {
					return false;
				}
			}

			$params = array(
				'EvnDiagDopDisp_id' => null,
				'EvnDiagDopDisp_setDate' => $data['EvnDiagDopDisp_setDate'],
				'EvnDiagDopDisp_pid' => $data['EvnPLDisp_id'],
				'Diag_id' => $data['Diag_id'],
				'DiagSetClass_id' => 1,
				'DeseaseDispType_id' => 2,
				'EvnDiagDopDisp_IsSystemDataAdd' => 2,
				'Lpu_id' => $data['Lpu_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'pmUser_id' => $data['pmUser_id']
			);
			$this->saveEvnDiagDopDisp($params);		
		}
	}
	
	/**
	 *	Добавление выявленного до
	 */	
	function addEvnDiagDopDispBefore($data, $diag_id) {
		// проверяем есть ли такой диагноз уже, если нет, то добавляем
		if (!empty($diag_id)) {
			$data['Diag_id'] = $diag_id;
			$query = "
				select top 1
					EvnDiagDopDisp_id
				from v_EvnDiagDopDisp (nolock)
				where EvnDiagDopDisp_pid = :EvnPLDisp_id and Diag_id = :Diag_id and DeseaseDispType_id = 1
			";
			
			$result = $this->db->query($query, $data);

			if (is_object($result))
			{
				$resp = $result->result('array');
				if (count($resp) > 0) {
					return false;
				}
			}

			$params = array(
				'EvnDiagDopDisp_id' => null,
				'EvnDiagDopDisp_setDate' => $data['EvnDiagDopDisp_setDate'],
				'EvnDiagDopDisp_pid' => $data['EvnPLDisp_id'],
				'Diag_id' => $data['Diag_id'],
				'DiagSetClass_id' => 1,
				'DeseaseDispType_id' => 1,
				'EvnDiagDopDisp_IsSystemDataAdd' => 2,
				'Lpu_id' => $data['Lpu_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'pmUser_id' => $data['pmUser_id']
			);
			$this->saveEvnDiagDopDisp($params);		
		}
	}
	
	/**
	 *	Удаление впервые выявленного
	 */	
	function delEvnDiagDopDispFirst($data, $diag_id) {
		// проверяем есть ли такой диагноз уже, если есть, то удаляем
		if (!empty($diag_id)) {
			$data['Diag_id'] = $diag_id;
			$query = "
				select top 1
					EvnDiagDopDisp_id
				from v_EvnDiagDopDisp (nolock)
				where EvnDiagDopDisp_pid = :EvnPLDisp_id and Diag_id = :Diag_id and DeseaseDispType_id = 2
			";
			
			$result = $this->db->query($query, $data);

			if (is_object($result))
			{
				$resp = $result->result('array');
				if (count($resp) > 0 && !empty($resp[0]['EvnDiagDopDisp_id'])) {
					$params = array(
						'EvnDiagDopDisp_id' => $resp[0]['EvnDiagDopDisp_id'],
						'pmUser_id' => $data['pmUser_id']
					);
					$this->delEvnDiagDopDisp($params);
				}
			}
		}
	}
	
	/**
	 *	Удаление выявленного до
	 */	
	function delEvnDiagDopDispBefore($data, $diag_id) {
		// проверяем есть ли такой диагноз уже, если есть, то удаляем
		if (!empty($diag_id)) {
			$data['Diag_id'] = $diag_id;
			$query = "
				select top 1
					EvnDiagDopDisp_id
				from v_EvnDiagDopDisp (nolock)
				where EvnDiagDopDisp_pid = :EvnPLDisp_id and Diag_id = :Diag_id and DeseaseDispType_id = 1
			";
			
			$result = $this->db->query($query, $data);

			if (is_object($result))
			{
				$resp = $result->result('array');
				if (count($resp) > 0 && !empty($resp[0]['EvnDiagDopDisp_id'])) {
					$params = array(
						'EvnDiagDopDisp_id' => $resp[0]['EvnDiagDopDisp_id'],
						'pmUser_id' => $data['pmUser_id']
					);
					$this->delEvnDiagDopDisp($params);
				}
			}
		}
	}

	/**
	 * Получение данных об установленных диагнозах во время диспанцеризации/мед.осмотра
	 * взрослого населения для панели просмотра ЭМК
	 */
	function getEvnDiagDopDispViewData($data) {
		if (empty($data['EvnDiagDopDisp_pid'])) {
			return array();
		}

		$filter = "";
		$queryParams = array(
			'EvnDiagDopDisp_pid' => $data['EvnDiagDopDisp_pid']
		);

		if (!empty($data['DeseaseDispType_id'])) {
			$filter .= " and EDDD.DeseaseDispType_id = :DeseaseDispType_id";
			$queryParams['DeseaseDispType_id'] = $data['DeseaseDispType_id'];
		}

		$query = "
			select
				EDDD.EvnDiagDopDisp_id,
				EDDD.EvnDiagDopDisp_pid,
				convert(varchar(10), EDDD.EvnDiagDopDisp_setDate, 104) as EvnDiagDopDisp_setDate,
				D.Diag_id,
				D.Diag_Code,
				D.Diag_Name,
				DSC.DiagSetClass_Name,
				DDT.DeseaseDispType_Name,
				DST.DispSurveilType_Name
			from
				v_EvnDiagDopDisp EDDD with(nolock)
				inner join v_Diag D with(nolock) on D.Diag_id = EDDD.Diag_id
				left join v_DeseaseDispType DDT with(nolock) on DDT.DeseaseDispType_id = EDDD.DeseaseDispType_id
				left join v_DispSurveilType DST (nolock) on DST.DispSurveilType_id = EDDD.DispSurveilType_id
				left join v_DiagSetClass DSC (nolock) on DSC.DiagSetClass_id = EDDD.DiagSetClass_id
			where
				EDDD.EvnDiagDopDisp_pid = :EvnDiagDopDisp_pid
				{$filter}
		";

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return swFilterResponse::filterNotViewDiag($result->result('array'), $data);
		}
		else {
			return false;
		}
	}
	
	/**
	 * Добавление диагноза в ДВН-Ext6
	 * Если в системе за последние 5 лет у пациента сохранен указанный диагноз, 
	 * то тип "Ранее известные имеющиеся заболевания"
	 * иначе "Впервые выявленные заболевания"
	 */	
	function addEvnDiagDopDispExt6($data, $diag_id) {
		if (!empty($diag_id) && !empty($data['EvnPLDisp_id'])) {
			$data['Diag_id'] = $diag_id;
			// проверяем есть ли такой диагноз уже, если нет, то добавляем
			$query = "
				select top 1
					EvnDiagDopDisp_id
				from v_EvnDiagDopDisp (nolock)
				where EvnDiagDopDisp_pid = :EvnPLDisp_id and Diag_id = :Diag_id and DeseaseDispType_id = 1
			";
			
			$result = $this->db->query($query, $data);

			if (is_object($result))
			{
				$resp = $result->result('array');
				if (count($resp) > 0) {
					return false;
				}
			}
			
			if(empty($data['Person_id'])) {
				$Person_id = $this->db->query(
					"select Person_id from v_EvnPLDisp where EvnPLDisp_id = :EvnPLDisp_id", 
					array('EvnPLDisp_id' => $data['EvnPLDisp_id'])
				);
				if(is_object($Person_id)) {
					$Person_id = $Person_id->result('array');
					if(!empty($Person_id[0]['Person_id']))
						$data['Person_id'] = $Person_id[0]['Person_id'];
				}
			}
			
			if(!empty($data['Person_id'])) {
				$query = "
					declare @today date = dbo.tzGetDate();

					with EvnDiag(
						Diag_id,
						Diag_setDate
					) as (
						-- диагнозы из движений
						select
							Diag_id,
							EvnSection_setDate
						from
							v_EvnSection with (nolock)
						where
							Person_id = :Person_id
							and Diag_id = :Diag_id
						union all
						
						-- диагнозы из посещений
						select
							Diag_id,
							EvnVizitPL_setDate
						from
							v_EvnVizitPL EVPL with (nolock)
						where
							Person_id = :Person_id
							and Diag_id = :Diag_id
							
						union all
						
						-- сопутствующие диагнозы
						select
							EDL.Diag_id,
							EDL.EvnDiagPLSop_setDate
						from
							v_EvnDiagPLSop EDL with (nolock)
							left join v_EvnVizit ev with (nolock) on EDL.EvnDiagPLSop_pid=ev.EvnVizit_id
						where
							EDL.Person_id = :Person_id
							and EDL.Diag_id = :Diag_id
							
						union all
						
						-- диагнозы из КВС
						select
							eds.Diag_id,
							EDS.EvnDiagPS_setDate
						from
							v_EvnDiagPS EDS with (nolock)
							left join v_EvnSection es with (nolock) on EDS.EvnDiagPS_pid=es.EvnSection_id
						where 
							EDS.Person_id = :Person_id
							and eds.Diag_id = :Diag_id
							
						union all
						
						select
							eds.Diag_id,
							EDS.EvnDiagSpec_didDT
						from
							v_EvnDiagSpec EDS with (nolock)
						where 
							Person_id = :Person_id
							and eds.Diag_id = :Diag_id
							
						union all
						
						select
							EVDD.Diag_id,
							EVDD.EvnVizitDispDop_setDate
						from
							v_EvnUslugaDispDop EVNU with(nolock)
							inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EVNU.EvnUslugaDispDop_pid
							inner join v_Diag diag with(nolock) on diag.Diag_id=EVDD.Diag_id
							left join v_DopDispInfoConsent DDIC with(nolock) on EVDD.DopDispInfoConsent_id=DDIC.DopDispInfoConsent_id
							left join v_SurveyTypeLink STL with(nolock) on STL.SurveyTypeLink_id=DDIC.SurveyTypeLink_id
						where
							EVNU.Person_id = :Person_id
							and EVDD.Diag_id = :Diag_id
							 and STL.SurveyType_id = 19
							 and EVDD.DopDispDiagType_id = 2
							 and diag.Diag_Code not like 'Z%'
						union all
						
						select
							EDDD.Diag_id,
							EDDD.EvnDiagDopDisp_setDate
						from
							v_EvnDiagDopDisp EDDD (nolock)
						where
							Person_id = :Person_id
							and EDDD.DeseaseDispType_id = '2'
							and EDDD.Diag_id = :Diag_id
					)
					select top 1
						ED.Diag_id
					from EvnDiag ED
					where DATEDIFF(YEAR, ED.Diag_setDate, @today)<=5
				";
				
				$result = $this->db->query($query, $data);

				if (is_object($result))
				{
					$resp = $result->result('array');
					$data['DeseaseDispType_id'] = (count($resp) > 0) ? 1 : 2;
					
					$params = array(
						'EvnDiagDopDisp_id' => null,
						'EvnDiagDopDisp_setDate' => $data['EvnDiagDopDisp_setDate'],
						'EvnDiagDopDisp_pid' => $data['EvnPLDisp_id'],
						'Diag_id' => $data['Diag_id'],
						'DiagSetClass_id' => 1,
						'DeseaseDispType_id' => $data['DeseaseDispType_id'],
						'EvnDiagDopDisp_IsSystemDataAdd' => 2,
						'Lpu_id' => $data['Lpu_id'],
						'Server_id' => $data['Server_id'],
						'PersonEvn_id' => $data['PersonEvn_id'],
						'pmUser_id' => $data['pmUser_id']
					);
					$this->saveEvnDiagDopDisp($params);
				}
			}
		}
	}
	
	/**
	 *	Удаление диагноза из ДВН-ext6
	 */	
	function delEvnDiagDopDispExt6($data, $diag_id) {
		// проверяем есть ли такой диагноз уже, если есть, то удаляем
		if (!empty($diag_id)) {
			$data['Diag_id'] = $diag_id;
			$query = "
				select top 1
					EvnDiagDopDisp_id
				from v_EvnDiagDopDisp (nolock)
				where EvnDiagDopDisp_pid = :EvnPLDisp_id and Diag_id = :Diag_id and DeseaseDispType_id = 1
			";
			
			$result = $this->db->query($query, $data);

			if (is_object($result))
			{
				$resp = $result->result('array');
				if (count($resp) > 0 && !empty($resp[0]['EvnDiagDopDisp_id'])) {
					$params = array(
						'EvnDiagDopDisp_id' => $resp[0]['EvnDiagDopDisp_id'],
						'pmUser_id' => $data['pmUser_id']
					);
					$this->delEvnDiagDopDisp($params);
				}
			}
		}
	}
}
?>