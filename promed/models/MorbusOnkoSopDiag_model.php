<?php defined('BASEPATH') or die ('No direct script access allowed');

class MorbusOnkoSopDiag_model extends swModel {

	public $inputRules = array(
		'saveMorbusOnkoSopDiag' => array(
			array('field' => 'Diag_id', 'label' => 'идентификатор сопутствующего заболевания', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MorbusOnkoBase_id', 'label' => 'Идентификатор специфики', 'rules' => 'required', 'type' => 'id')
		),
		'deleteMorbusOnkoSopDiag' => array(
			array('field' => 'MorbusOnkoBaseDiagLink_id', 'label' => 'идентификатор сопутствующего заболевания', 'rules' => 'required', 'type' => 'id')
		),
		'loadMorbusOnkoSopDiagList' => array(
			array('field' => 'MorbusOnkoBase_id', 'label' => 'Идентификатор специфики', 'rules' => 'required', 'type' => 'id')
		),
		'loadMorbusOnkoSopDiag' => array(
			array('field' => 'MorbusOnkoBaseDiagLink_id', 'label' => 'идентификатор сопутствующего заболевания', 'rules' => 'required', 'type' => 'id')
		)
	);
	
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

    /**
	 * Получение списка сопутствующих заболеваний
	 */
	function loadMorbusOnkoSopDiagList($data) {
		$filters = "1=1";
		$params = array();

		if(!empty($data['MorbusOnkoBase_id'])){
			$params['MorbusOnkoBase_id'] = $data['MorbusOnkoBase_id'];
			$filters .= " and mobdl.MorbusOnkoBase_id = :MorbusOnkoBase_id";
		} else {
			return false;
		}
		$query = "
			select 
				mobdl.MorbusOnkoBaseDiagLink_id,
				rtrim(d.Diag_Code +' '+ d.Diag_Name) as SopDiag_Name
			from
				dbo.v_MorbusOnkoBaseDiagLink mobdl with(nolock)
				left join v_Diag d with (nolock) on d.Diag_id = mobdl.Diag_id
			where
				{$filters}
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение сопутствующего заболевания
	 */
	function loadMorbusOnkoSopDiag($data) {
		$filters = "1=1";
		$params = array();

		if(!empty($data['MorbusOnkoBaseDiagLink_id'])){
			$params['MorbusOnkoBaseDiagLink_id'] = $data['MorbusOnkoBaseDiagLink_id'];
			$filters .= " and mobdl.MorbusOnkoBaseDiagLink_id = :MorbusOnkoBaseDiagLink_id";
		} else {
			return false;
		}
		$query = "
			select 
				mobdl.MorbusOnkoBaseDiagLink_id,
				mobdl.MorbusOnkoBase_id,
				mobdl.Diag_id
			from
				dbo.v_MorbusOnkoBaseDiagLink mobdl with(nolock)
			where
				{$filters}
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * @param array $data
	 * @return array Стандартный ответ модели
	 */
	function getViewData($data)
	{
		$query = "
			SELECT
				case
					when 1=1 then 'edit'
					else 'view'
				end as accessType
				,mobdl.MorbusOnkoBaseDiagLink_id
				,MO.MorbusOnko_id
				,rtrim(d.Diag_Code +' '+ d.Diag_Name) as SopDiag_Name
				,:Evn_id as MorbusOnko_pid
				,Morbus.Morbus_id
			FROM
				dbo.v_Morbus Morbus WITH (NOLOCK)
				INNER JOIN dbo.v_MorbusOnko MO WITH (NOLOCK) on Morbus.Morbus_id = MO.Morbus_id
				INNER JOIN dbo.v_MorbusOnkoBase mob WITH (NOLOCK) on Morbus.MorbusBase_id = mob.MorbusBase_id
				INNER JOIN dbo.v_MorbusOnkoBaseDiagLink mobdl WITH (NOLOCK) on mob.MorbusOnkoBase_id = mobdl.MorbusOnkoBase_id
				left join v_Diag d with (nolock) on d.Diag_id = mobdl.Diag_id
			where
				Morbus.Morbus_id = :Morbus_id
		";
		$params = array(
			'Morbus_id' => $data['Morbus_id'],
			'Evn_id' => $data['Evn_id'],
		);
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение сопутствующего заболевания
	 */
	function saveMorbusOnkoSopDiag($data) {
		if(!empty($data['MorbusOnkoBaseDiagLink_id'])){
			$proc = 'dbo.p_MorbusOnkoBaseDiagLink_upd';
		} else {
			$proc = 'dbo.p_MorbusOnkoBaseDiagLink_ins';
		}
		$params = array(
			'MorbusOnkoBaseDiagLink_id' => (!empty($data['MorbusOnkoBaseDiagLink_id'])?$data['MorbusOnkoBaseDiagLink_id']:null),
			'Diag_id' => (!empty($data['Diag_id'])?$data['Diag_id']:null),
			'MorbusOnkoBase_id' => (!empty($data['MorbusOnkoBase_id'])?$data['MorbusOnkoBase_id']:null),
			'pmUser_id' => (!empty($data['pmUser_id'])?$data['pmUser_id']:1)
		);
		return $this->execCommonSP($proc,$params);
	}

	/**
	 * Удаление сопутствующего заболевания
	 */
	function deleteMorbusOnkoSopDiag($data) {
		if(!empty($data['MorbusOnkoBaseDiagLink_id'])){
			$params = array(
				'MorbusOnkoBaseDiagLink_id' => (!empty($data['MorbusOnkoBaseDiagLink_id'])?$data['MorbusOnkoBaseDiagLink_id']:null)
			);
			return $this->execCommonSP('dbo.p_MorbusOnkoBaseDiagLink_del',$params);
		} else {
			return array('Error_Msg'=>'Не указан идентификатор Сопутствующего заболевания');
		}
	}
}