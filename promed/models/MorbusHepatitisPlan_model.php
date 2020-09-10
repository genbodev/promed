<?php
/**
* MorbusHepatitisPlan_model - модель, для работы с таблицей MorbusHepatitisPlan
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      06.2019
*/
class MorbusHepatitisPlan_model extends swModel {
	/**
	 * MorbusHepatitisPlan_model constructor.
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function load($data)
	{
		$query = "
			select 
				MorbusHepatitisPlan_id,
				MorbusHepatitis_id,
				MorbusHepatitisPlan_Year,
				MorbusHepatitisPlan_Month,
				MedicalCareType_id,
				Lpu_id,
				MorbusHepatitisPlan_Treatment
			from
				v_MorbusHepatitisPlan with(nolock)
			where
				MorbusHepatitisPlan_id = ?
		";
		$res = $this->db->query($query, array($data['MorbusHepatitisPlan_id']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;		
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function save($data)
	{
		if ($data['MorbusHepatitisPlan_Treatment'] == 1) {
			$queryParams = array('MorbusHepatitis_id' => $data['MorbusHepatitis_id']);
			$filter = "";
			if (!empty($data['MorbusHepatitisPlan_id'])) {
				$queryParams['MorbusHepatitisPlan_id'] = $data['MorbusHepatitisPlan_id'];
				$filter .= " and MorbusHepatitisPlan_id <> :MorbusHepatitisPlan_id";
			}

			// проверка, есть ли ещё записи с MorbusHepatitisPlan_Treatment = 1
			$resp = $this->queryResult("
				select top 1
					MorbusHepatitisPlan_id
				from
					v_MorbusHepatitisPlan (nolock)
				where
					ISNULL(MorbusHepatitisPlan_Treatment, 1) = 1
					and MorbusHepatitis_id = :MorbusHepatitis_id
					{$filter}
			", $queryParams);

			if (!empty($resp[0]['MorbusHepatitisPlan_id'])) {
				return array('Error_Msg' => 'В плане лечения от гепатита C может быть только одно не проведенное лечение');
			}
		}

		if ( !isset($data['MorbusHepatitisPlan_id']) ) {
			$procedure_action = "ins";
		}
		else {
			$procedure_action = "upd";
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :MorbusHepatitisPlan_id;
			exec p_MorbusHepatitisPlan_" . $procedure_action . "
				@MorbusHepatitisPlan_id = @Res output,
				@MorbusHepatitis_id = :MorbusHepatitis_id,
				@MorbusHepatitisPlan_Year = :MorbusHepatitisPlan_Year,
				@MorbusHepatitisPlan_Month = :MorbusHepatitisPlan_Month,
				@MedicalCareType_id = :MedicalCareType_id,
				@Lpu_id = :Lpu_id,
				@MorbusHepatitisPlan_Treatment = :MorbusHepatitisPlan_Treatment,				
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as MorbusHepatitisPlan_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";
		
		$queryParams = array(
			'MorbusHepatitisPlan_id' => $data['MorbusHepatitisPlan_id'],
			'MorbusHepatitis_id' => $data['MorbusHepatitis_id'],
			'MorbusHepatitisPlan_Year' => $data['MorbusHepatitisPlan_Year'],
			'MorbusHepatitisPlan_Month' => $data['MorbusHepatitisPlan_Month'],
			'MedicalCareType_id' => $data['MedicalCareType_id'],
			'Lpu_id' => $data['Lpu_id'],
			'MorbusHepatitisPlan_Treatment' => $data['MorbusHepatitisPlan_Treatment'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $response = $res->result('array');
		}
		else {
			return false;
		}
	}
}