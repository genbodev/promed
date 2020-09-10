<?php

class EvnAgg_model extends CI_Model {

	public $inputRules = array(
		'loadEvnAggList' => array(
			array(
				'field' => 'Evn_id', 
				'label' => 'Идентификатор случая услуги-родителя', 
				'rules' => 'required', 
				'type' => 'id'
			)
		),
		'loadEvnAgg' => array(
			array(
				'field' => 'Evn_id', 
				'label' => 'Идентификатор случая услуги-родителя', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'EvnAgg_id', 
				'label' => 'Идентификатор осложнения', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'AggType_id', 
				'label' => 'Тип осложнения', 
				'rules' => '', 
				'type' => 'id'
			)
		),
		'createEvnAgg' => array(
			array(
				'field' => 'Evn_id', 
				'label' => 'Идентификатор случая услуги-родителя', 
				'rules' => 'required', 
				'type' => 'id',
				'checklpu' => true
			),
			array(
				'field' => 'AggWhen_id', 
				'label' => 'Период, в котором произошло осложнение', 
				'rules' => 'required', 
				'type' => 'id'
			),
			array(
				'field' => 'AggType_id', 
				'label' => 'Тип осложнения', 
				'rules' => 'required', 
				'type' => 'id'
			)
		),
		'updateEvnAgg' => array(
			array(
				'field' => 'EvnAgg_id', 
				'label' => 'Идентификатор осложнения', 
				'rules' => 'required', 
				'type' => 'id'
			),
			array(
				'field' => 'Evn_id', 
				'label' => 'Идентификатор случая услуги-родителя', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'AggWhen_id', 
				'label' => 'Период, в котором произошло осложнение', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'AggType_id', 
				'label' => 'Тип осложнения', 
				'rules' => '', 
				'type' => 'id'
			)
		)
	);

	/**
	 * d
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function deleteEvnAgg($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnAgg_del
				@EvnAgg_id = :EvnAgg_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'EvnAgg_id' => $data['EvnAgg_id'],
			'pmUser_id' => $data['pmUser_id']
				));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление осложнения)'));
		}
	}

	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function loadEvnAggEditForm($data) {
		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$accessType = '
			case
				when EA.Lpu_id = :Lpu_id then 1
				' . (count($data['session']['linkedLpuIdList']) > 1 ? 'when EA.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and ISNULL(EA.EvnAgg_IsTransit, 1) = 2 then 1' : '') . '
				else 0
			end = 1
		';

		$query = "
			select top 1
				case when {$accessType} " . ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 ? "and EU.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . " then 'edit' else 'view' end as accessType,
				EA.EvnAgg_id,
				EA.EvnAgg_pid,
				EA.Person_id,
				EA.PersonEvn_id,
				EA.Server_id,
				EA.AggType_id,
				EA.AggWhen_id,
				convert(varchar(10), EA.EvnAgg_setDate, 104) as EvnAgg_setDate,
				EA.EvnAgg_setTime
			from v_EvnAgg EA with (nolock)
				inner join v_EvnUsluga EU with (nolock) on EU.EvnUsluga_id = EA.EvnAgg_pid
			where
				EA.EvnAgg_id = :EvnAgg_id
				and (EA.Lpu_id = :Lpu_id or " . (!empty($data['session']['medpersonal_id']) ? 1 : 0) . " = 1)
		";

		$result = $this->db->query($query, array(
			'EvnAgg_id' => $data['EvnAgg_id'],
			'Lpu_id' => $data['Lpu_id']
				));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function loadEvnAggGrid($data) {
		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$accessType = '
			case
				when EA.Lpu_id = :Lpu_id then 1
				' . (count($data['session']['linkedLpuIdList']) > 1 ? 'when EA.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and ISNULL(EA.EvnAgg_IsTransit, 1) = 2 then 1' : '') . '
				else 0
			end = 1
		';

		$query = "
			select
				case when {$accessType} " . ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 ? "and EU.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . " then 'edit' else 'view' end as accessType,
				EA.EvnAgg_id,
				EA.EvnAgg_pid,
				EA.Person_id,
				EA.PersonEvn_id,
				EA.Server_id,
				AT.AggType_id,
				AW.AggWhen_id,
				convert(varchar(10), EA.EvnAgg_setDate, 104) as EvnAgg_setDate,
				EA.EvnAgg_setTime,
				RTRIM(ISNULL(AT.AggType_Name, '')) as AggType_Name,
				RTRIM(ISNULL(AW.AggWhen_Name, '')) as AggWhen_Name
			from v_EvnAgg EA with (nolock)
				inner join v_EvnUsluga EU with (nolock) on EU.EvnUsluga_id = EA.EvnAgg_pid
				left join AggType AT with (nolock) on AT.AggType_id = EA.AggType_id
				left join AggWhen AW with (nolock) on AW.AggWhen_id = EA.AggWhen_id
			where EA.EvnAgg_pid = :EvnAgg_pid
				and EA.Lpu_id = :Lpu_id
		";
		$result = $this->db->query($query, array(
			'EvnAgg_pid' => $data['EvnAgg_pid'],
			'Lpu_id' => $data['Lpu_id']
		));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function saveEvnAgg($data) {
		$procedure = '';

		if ((!isset($data['EvnAgg_id'])) || ($data['EvnAgg_id'] <= 0)) {
			$procedure = 'p_EvnAgg_ins';
		} else {
			$procedure = 'p_EvnAgg_upd';
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnAgg_id;
			exec " . $procedure . "
				@EvnAgg_id = @Res output,
				@EvnAgg_pid = :EvnAgg_pid,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnAgg_setDT = :EvnAgg_setDT,
				@AggType_id = :AggType_id,
				@AggWhen_id = :AggWhen_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnAgg_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'EvnAgg_id' => $data['EvnAgg_id'],
			'EvnAgg_pid' => $data['EvnAgg_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnAgg_setDT' => $data['EvnAgg_setDate'] . " " . $data['EvnAgg_setTime'],
			'AggType_id' => $data['AggType_id'],
			'AggWhen_id' => $data['AggWhen_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		//echo getDebugSQL($query, $queryParams);exit();
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Получение информации по осложнению услуги
	 */
	function loadEvnAgg($data) {
		$where = "Lpu_id = :Lpu_id";
		if(!empty($data['Evn_id'])){
			$where .= " and  EvnAgg_pid = :Evn_id";
		}
		if(!empty($data['EvnAgg_id'])){
			$where .= " and  EvnAgg_id = :EvnAgg_id";
		}
		if(!empty($data['AggType_id'])){
			$where .= " and  AggType_id = :AggType_id";
		}
		$query = "
			select
				EvnAgg_id,
				EvnAgg_pid as Evn_id,
				AggType_id,
				AggWhen_id
			from v_EvnAgg with (nolock)
			where 
				{$where}
		";
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function loadEvnAggList($data) {
		$params = array(
			'EvnAgg_pid' => $data['EvnAgg_pid']
		);
		$where = "";
		if(!empty($data['AggWhen_id'])){
			$params['AggWhen_id'] = $data['AggWhen_id'];
			$where = " and  EA.AggWhen_id = :AggWhen_id";
		}
		$query = "
			select
				EA.EvnAgg_id,
				EA.AggType_id
			from v_EvnAgg EA with (nolock)
			where EA.EvnAgg_pid = :EvnAgg_pid
			{$where}
		";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
}