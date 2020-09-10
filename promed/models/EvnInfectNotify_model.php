<?php
/**
* EvnInfectNotify_model - модель, для работы с таблицей EvnInfectNotify
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

class EvnInfectNotify_model extends CI_Model
{

	/**
	 * Method description
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Method description
	 */
	function isIsset($data)
	{
		$query = "
			select 
				1
			from
				v_EvnInfectNotify with (nolock)
			where
				EvnInfectNotify_pid = ?
		";
		$res = $this->db->query($query, array($data['EvnInfectNotify_pid']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;		
	}

	/**
	 * Method description
	 */
	function load($data)
	{
		$query = "
			select 
				EvnInfectNotify_id,
				EvnInfectNotify_pid,
				EvnInfectNotify_IsLabDiag,
				convert(varchar(10), EvnInfectNotify_DiseaseDate, 104) as EvnInfectNotify_DiseaseDate,
				convert(varchar(10), EvnInfectNotify_FirstTreatDate, 104) as EvnInfectNotify_FirstTreatDate,
				convert(varchar(10), EvnInfectNotify_SetDiagDate, 104) as EvnInfectNotify_SetDiagDate,
				convert(varchar(10), EvnInfectNotify_NextVizitDate, 104) as EvnInfectNotify_NextVizitDate,
				Lpu_id,
				Server_id,
				PersonEvn_id,
				EvnInfectNotify_PoisonDescr,
				EvnInfectNotify_FirstMeasures,
				convert(varchar(10), EvnInfectNotify_FirstSESDT, 104) as EvnInfectNotify_FirstSESDT_Date,
				substring(convert(varchar, EvnInfectNotify_FirstSESDT,108),1,5) as EvnInfectNotify_FirstSESDT_Time,
				MedPersonal_id,
				EvnInfectNotify_ReceiverMessage
			from
				v_EvnInfectNotify with (nolock)
			where
				EvnInfectNotify_id = ?
		";
		$res = $this->db->query($query, array($data['EvnInfectNotify_id']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;		
	}

	/**
	 * Method description
	 */
	function save($data)
	{

		if ( !isset($data['EvnInfectNotify_id']) ) {
			$procedure_action = "ins";
			$out = "output";
		}
		else {
			$procedure_action = "upd";
			$out = "";
		}
		
		if (!empty($data['EvnInfectNotify_FirstSESDT_Date'])) {
			$data['EvnInfectNotify_FirstSESDT_Time'] = !empty($data['EvnInfectNotify_FirstSESDT_Time']) ? $data['EvnInfectNotify_FirstSESDT_Time'] : '00:00';
			$data['EvnInfectNotify_FirstSESDT'] = $data['EvnInfectNotify_FirstSESDT_Date'] . " ".$data['EvnInfectNotify_FirstSESDT_Time'].":00";
		} else {
			$data['EvnInfectNotify_FirstSESDT'] = null;
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :EvnInfectNotify_id;
			exec p_EvnInfectNotify_" . $procedure_action . "
				@EvnInfectNotify_id = @Res output,
				@EvnInfectNotify_pid = :EvnInfectNotify_pid,
				@EvnInfectNotify_IsLabDiag = :EvnInfectNotify_IsLabDiag,
				@EvnInfectNotify_DiseaseDate = :EvnInfectNotify_DiseaseDate,
				@EvnInfectNotify_FirstTreatDate = :EvnInfectNotify_FirstTreatDate,
				@EvnInfectNotify_SetDiagDate = :EvnInfectNotify_SetDiagDate,
				@EvnInfectNotify_NextVizitDate = :EvnInfectNotify_NextVizitDate,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnInfectNotify_PoisonDescr = :EvnInfectNotify_PoisonDescr,
				@EvnInfectNotify_FirstMeasures = :EvnInfectNotify_FirstMeasures,
				@EvnInfectNotify_FirstSESDT = :EvnInfectNotify_FirstSESDT,
				@MedPersonal_id = :MedPersonal_id,
				@EvnInfectNotify_ReceiverMessage = :EvnInfectNotify_ReceiverMessage,
				@EvnSection_id = :EvnSection_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as EvnInfectNotify_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";
		
		$queryParams = array(
			'EvnInfectNotify_id' => $data['EvnInfectNotify_id'],
			'EvnInfectNotify_pid' => $data['EvnInfectNotify_pid'],
			'EvnInfectNotify_IsLabDiag' => $data['EvnInfectNotify_IsLabDiag'],
			'EvnInfectNotify_DiseaseDate' => $data['EvnInfectNotify_DiseaseDate'],
			'EvnInfectNotify_FirstTreatDate' => $data['EvnInfectNotify_FirstTreatDate'],
			'EvnInfectNotify_SetDiagDate' => $data['EvnInfectNotify_SetDiagDate'],
			'EvnInfectNotify_NextVizitDate' => $data['EvnInfectNotify_NextVizitDate'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnInfectNotify_PoisonDescr' => $data['EvnInfectNotify_PoisonDescr'],
			'EvnInfectNotify_FirstMeasures' => $data['EvnInfectNotify_FirstMeasures'],
			'EvnInfectNotify_FirstSESDT' => $data['EvnInfectNotify_FirstSESDT'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'EvnInfectNotify_ReceiverMessage' => $data['EvnInfectNotify_ReceiverMessage'],
			'EvnSection_id' => (!empty($data['EvnSection_id'])) ? $data['EvnSection_id'] : null,
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
	
	/**
	 * Метод для API. Получение извещения об инфекционном заболевании (ВИЧ)
	 */
	function getEvnInfectNotifyAPI($data){
		if(empty($data['Person_id']) && empty($data['EvnInfectNotify_id'])) return false;
		$where = '';
		if(!empty($data['EvnInfectNotify_id'])){
			$where .= ' AND EIN.EvnInfectNotify_id = :EvnInfectNotify_id';
		}
		if(!empty($data['Person_id'])){
			$where .= ' AND Evn.Person_id = :Person_id';
		}
		$query = "
			SELECT 
				Evn.Person_id as Person_id,
				ENB.EvnNotifyBase_id,
				convert(varchar(10), EIN.EvnInfectNotify_DiseaseDate, 104) as EvnInfectNotify_DiseaseDate,
				EIN.EvnInfectNotify_FirstMeasures,
				convert(varchar(10), EIN.EvnInfectNotify_FirstSESDT, 104) as EvnInfectNotify_FirstSESDT_Date,
				convert(varchar(10), EIN.EvnInfectNotify_FirstTreatDate, 104) as EvnInfectNotify_FirstTreatDate,
				case
					when isnull(EIN.EvnInfectNotify_IsLabDiag, 2) = 2
					then 'нет'
					else 'да'
				end as EvnInfectNotify_IsLabDiag,
				convert(varchar(10), EIN.EvnInfectNotify_NextVizitDate, 104) as EvnInfectNotify_NextVizitDate,
				EVN.Lpu_id,
				EIN.EvnInfectNotify_PoisonDescr,
				EIN.EvnInfectNotify_ReceiverMessage,
				convert(varchar(10), EIN.EvnInfectNotify_SetDiagDate, 104) as EvnInfectNotify_SetDiagDate,
				EIN.EvnSection_id,
				ES.Diag_id,
				ES.MedPersonal_id
			from Evn with(nolock)
				inner join EvnNotifyBase ENB with(nolock) on Evn.Evn_id = ENB.Evn_id
				inner join EvnInfectNotify EIN with(nolock) on ENB.EvnNotifyBase_id = EIN.EvnNotifyBase_id
				left join v_EvnSection ES with(nolock) on ES.EvnSection_id = EIN.EvnSection_id
			where 1=1
				{$where}
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}	
}