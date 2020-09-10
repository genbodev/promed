<?php
/**
* MorbusHepatitisDiag_model - модель, для работы с таблицей MorbusHepatitisDiag
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Alexander Chebukin 
* @version      07.2012
*/

class MorbusHepatitisDiag_model extends CI_Model {
	/**
	 * MorbusHepatitisDiag_model constructor.
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
				MorbusHepatitisDiag_id,
				MorbusHepatitis_id,
				Evn_id as EvnSection_id,
				convert(varchar,cast(MorbusHepatitisDiag_setDT as datetime),104) as MorbusHepatitisDiag_setDT,
				MedPersonal_id,
				HepatitisDiagType_id,
				convert(varchar,cast(MorbusHepatitisDiag_ConfirmDT as datetime),104) as MorbusHepatitisDiag_ConfirmDT,
				HepatitisDiagActiveType_id,
				HepatitisFibrosisType_id
			from
				v_MorbusHepatitisDiag with(nolock)
			where
				MorbusHepatitisDiag_id = ?
		";
		$res = $this->db->query($query, array($data['MorbusHepatitisDiag_id']));
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

		if ( !isset($data['MorbusHepatitisDiag_id']) ) {
			$procedure_action = "ins";
			$out = "output";
		}
		else {
			$procedure_action = "upd";
			$out = "";
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :MorbusHepatitisDiag_id;
			exec p_MorbusHepatitisDiag_" . $procedure_action . "
				@MorbusHepatitisDiag_id = @Res output,
				@MorbusHepatitis_id = :MorbusHepatitis_id,
				@MorbusHepatitisDiag_setDT = :MorbusHepatitisDiag_setDT,
				@MedPersonal_id = :MedPersonal_id,				
				@HepatitisDiagType_id = :HepatitisDiagType_id,
				@MorbusHepatitisDiag_ConfirmDT = :MorbusHepatitisDiag_ConfirmDT,
				@HepatitisDiagActiveType_id = :HepatitisDiagActiveType_id,
				@HepatitisFibrosisType_id = :HepatitisFibrosisType_id,	
				@Evn_id = :Evn_id,			
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as MorbusHepatitisDiag_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";
		
		$queryParams = array(
			'MorbusHepatitisDiag_id' => $data['MorbusHepatitisDiag_id'],
			'MorbusHepatitis_id' => $data['MorbusHepatitis_id'],
			'MorbusHepatitisDiag_setDT' => $data['MorbusHepatitisDiag_setDT'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'HepatitisDiagType_id' => $data['HepatitisDiagType_id'],			
			'MorbusHepatitisDiag_ConfirmDT' => $data['MorbusHepatitisDiag_ConfirmDT'],			
			'HepatitisDiagActiveType_id' => $data['HepatitisDiagActiveType_id'],
			'HepatitisFibrosisType_id' => $data['HepatitisFibrosisType_id'],
			'Evn_id' => $data['EvnSection_id'],
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