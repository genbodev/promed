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
 * @copyright    Copyright (c) 2012 Swan Ltd.
 * @author       Khorev Sergey (sergey.khorev@yandex.ru)
 * @version      30.05.2012
 */
class LpuOrgServed_model extends CI_Model{

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Получение конкретной обслуживаемой организации
	 */
	function getCurrentLpuOrgServed($data){
		$queryparams = array();
		$queryparams['LpuOrgServed_id'] = $data['LpuOrgServed_id'];
		$query = "
					SELECT LOS.Org_id,
					CONVERT(varchar(10),LOS.LpuOrgServed_begDate,104) as LpuOrgServed_begDate,
		 			CONVERT(varchar(10),LOS.LpuOrgServed_endDate,104) as LpuOrgServed_endDate,
					LpuOrgServiceType_id
					FROM v_LpuOrgServed LOS with(nolock)
					WHERE LOS.LpuOrgServed_id = :LpuOrgServed_id
				";
		$result = $this->db->query($query,$queryparams);
                          
		if(!is_object($result))
		{
			return false;
		}
		return $result->result('array');
	}

	/**
	 * Получение списка обслуживаемых организаций в ЛПУ
	 */
	function getLpuOrgServed($data){
		$queryparams = array();
		$queryparams['Lpu_id'] = $data['Lpu_id'];
		$filter = "";
		if (!empty($data['isClose']) && $data['isClose'] == 1) {
			$filter .= " and (LOS.LpuOrgServed_endDate is null or LOS.LpuOrgServed_endDate > dbo.tzGetDate())";
		} elseif (!empty($data['isClose']) && $data['isClose'] == 2) {
			$filter .= " and LOS.LpuOrgServed_endDate <= dbo.tzGetDate()";
		}

		$query = "
			SELECT LOS.LpuOrgServed_id,
		 	LOS.Org_id,
		 	O.Org_Name,
		 	O.Org_Nick,
			CONVERT(varchar(10),LOS.LpuOrgServed_begDate,104) as LpuOrgServed_begDate,
		 	CONVERT(varchar(10),LOS.LpuOrgServed_endDate,104) as LpuOrgServed_endDate,
			LOST.LpuOrgServiceType_Name
  			FROM v_LpuOrgServed LOS with(nolock)
  			INNER JOIN Org O with(nolock) ON O.Org_id = LOS.Org_id
			left join LpuOrgServiceType LOST with (nolock) on LOST.LpuOrgServiceType_id = LOS.LpuOrgServiceType_id
  			WHERE LOS.Lpu_id = :Lpu_id {$filter}
		";
                   
		$result = $this->db->query($query,$queryparams);
		//var_dump($result->result('array'));
		//return false;
		if(!is_object($result))
		{
			return false;
		}
		return $result->result('array');
	}

	/**
	 * Сохранение
	 */
	function saveLpuOrgServed($data){
		$storedproc = '';
		
		$queryparams = array(
			'Lpu_id' => $data['Lpu_id'],
			'LpuOrgServed_id' => $data['LpuOrgServed_id'],
			'Org_id' => $data['Org_id'],
			'LpuOrgServed_begDate' => $data['LpuOrgServed_begDate'],
			'LpuOrgServed_endDate' => $data['LpuOrgServed_endDate'],
			'LpuOrgServiceType_id' => $data['LpuOrgServiceType_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		if (!isset($data['LpuOrgServed_id']))
		{
			$storedproc = 'ins';
		}
		else
		{
			$storedproc = 'upd';
		}
		$query = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :LpuOrgServed_id;
			exec p_LpuOrgServed_{$storedproc}
			@LpuOrgServed_id = @Res output,
			@Lpu_id = :Lpu_id,
			@Org_id = :Org_id,
			@LpuOrgServed_begDate = :LpuOrgServed_begDate,
			@LpuOrgServed_endDate = :LpuOrgServed_endDate,
			@LpuOrgServiceType_id = :LpuOrgServiceType_id,
			@pmUser_id = :pmUser_id,
			@Error_Code = @ErrCode output,
			@Error_Message = @ErrMsg output

			select @Res as LpuOrgServed_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";
		//echo getDebugSQL($query,$queryparams);
		//return false;
		$result = $this->db->query($query,$queryparams);
		return $result->result('array');;
	}

	/**
	 * Удаление
	 */
	function deleteLpuOrgServed($data)
	{
		$queryparams = array(
			'LpuOrgServed_id' => $data['LpuOrgServed_id']
		);
		$query = "
			declare
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			exec p_LpuOrgServed_del
			@LpuOrgServed_id :LpuOrgServed_id
			@Error_Code = @ErrCode output,
			@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query,$queryparams);
		return $result;
	}
}