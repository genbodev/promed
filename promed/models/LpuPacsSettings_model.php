<?php	defined('BASEPATH') or die ('No direct script access allowed');

/**
 * LpuOrgServed_model - модель для работы с обслуживаемыми организациями
 *http://redmine.swan.perm.ru/projects/promedweb-dlo/repository/revisions/10303
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Tokarev Sergey 
 * @version      22.02.2012
 */
class LpuPacsSettings_model extends CI_Model{

	/**
	 * LpuOrgServed_model
	 */
	function LpuOrgServed_model()
	{
		parent::__construct();
	}

	/**
	 * Получение настроек ПАКС
	 */
	function getCurrentPacsSettings($data){
		//var_dump($data['LpuSection_id']);
        // var_dump ($data['LpuSection_id']); die();
		$queryparams = array();
		$queryparams['LpuSection_id'] = $data['LpuSection_id'];
		$query = "
					SELECT * 
					FROM v_LpuPacs LP with(nolock)
					WHERE LP.LpuSection_id = :LpuSection_id
				";
		$result = $this->db->query($query,$queryparams);
                          
		if(!is_object($result))
		{
			return false;
		}
		return $result->result('array');
	}

	/**
	 * @param $data
	 * @return bool|CI_DB_sqlsrv_result|void
	 */
	function saveLpuPacsData($data){	
		//	echo ($data['LpuSection_id']);
		$queryparams = array(
			'LpuPacs_aetitle' => $data['LpuPacs_aetitle'],  
			'LpuPacs_desc' => $data['LpuPacs_desc'],  
			'LpuPacs_port' => $data['LpuPacs_port'],  
			'LpuPacs_ip' => $data['LpuPacs_ip'],  
			'LpuSection_id' => $data['LpuSection_id'],
			'LpuPacs_wadoPort' => $data['LpuPacs_wadoPort']
		);
		if (!isset($data['LpuPacs_id']))
		{
			$storedproc = 'ins';
			$queryparams['LpuPacs_id'] = 0;			
		}
		else
		{
			$storedproc = 'upd';
			$queryparams['LpuPacs_id'] = $data['LpuPacs_id'];
		}
		$query = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :LpuPacs_id;
			exec p_LpuPacs_{$storedproc}
			@LpuPacs_id = @Res output,
            @LpuSection_id = :LpuSection_id,
			@LpuPacs_aetitle = :LpuPacs_aetitle,
			@LpuPacs_desc = :LpuPacs_desc,
			@LpuPacs_port = :LpuPacs_port,
			@LpuPacs_wadoPort = :LpuPacs_wadoPort,
			@LpuPacs_ip = :LpuPacs_ip,			
			@pmUser_id = :pmUser_id,
			@Error_Code = @ErrCode output,
			@Error_Message = @ErrMsg output

			select @Res as LpuPacs_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";
		//echo getDebugSQL($query,$queryparams);
		//return false;
		$queryparams['pmUser_id'] = $data['pmUser_id'];
		//$queryparams['LpuPacs_id'] = 0;
		$result = $this->db->query($query,$queryparams);
		return $result;
	}

	/**
	 * @param $data
	 * @return bool|CI_DB_sqlsrv_result|void
	 */
	function deleteLpuPacsData($data)
	{
		$queryparams = array(
			'LpuPacs_id' => $data['LpuPacs_id']
		);		
		$query = "
			declare
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			exec p_LpuPacs_del
			@LpuPacs_id = :LpuPacs_id,
			@Error_Code = @ErrCode output,
			@Error_Message = @ErrMsg output
			
			select @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";
		$result = $this->db->query($query,$queryparams);
		return $result;
	}
}