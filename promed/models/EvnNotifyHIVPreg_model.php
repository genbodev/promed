<?php
/**
* EvnNotifyHIVPreg_model - модель для работы с таблицей EvnNotifyHIVPreg
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Alexander Permyakov 
* @version      12.2012
*/

class EvnNotifyHIVPreg_model extends swModel
{
	/**
	 * Method description
	 */
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	* Проверка наличия извещения
	* Проверка выполняется из Common_model->signedDocument
	*/
	function checkEvnNotifyHIVPreg($data)
	{
		$tableName = 'EvnNotifyHIVPreg';
		$this->load->library('swMorbus');
		return swMorbus::getStaticMorbusCommon()->checkExistsExtended('hiv', $data['Person_id'], null,"
				,EN.{$tableName}_id
				,PR.EvnNotifyBase_id
				,PR.PersonRegister_id
				,PR.PersonRegisterOutCause_id" ,"
				left join v_{$tableName} EN with (nolock) on EN.Morbus_id = Morbus.Morbus_id
				left join v_PersonRegister PR with (nolock) on PR.Morbus_id = Morbus.Morbus_id",
			true
		);
	}

	/**
	 * Method description
	 */
	function load($data)
	{
		$query = '
			select
				ENO.EvnNotifyHIVPreg_id,
				ENO.EvnNotifyHIVPreg_pid,
				ENO.Morbus_id,
				ENO.Server_id,
				ENO.PersonEvn_id,
				ENO.Person_id,
				ENO.MedPersonal_id,
				convert(varchar,ENO.EvnNotifyHIVPreg_setDT,104) as EvnNotifyHIVPreg_setDT,
				convert(varchar,ENO.EvnNotifyHIVPreg_DiagDT,104) as EvnNotifyHIVPreg_DiagDT,
				convert(varchar,ENO.EvnNotifyHIVPreg_endDT,104) as EvnNotifyHIVPreg_endDT,
				ENO.HIVPregPathTransType_id,
				ENO.HIVPregPeriodType_id,
				ENO.HIVPregInfectStudyType_id,
				ENO.HIVPregInfectStudyType_did,
				ENO.HIVPregResultType_id,
				ENO.EvnNotifyHIVPreg_IsPreterm,
				ENO.HIVPregWayBirthType_id,
				ENO.EvnNotifyHIVPreg_OtherWayBirth,
				ENO.EvnNotifyHIVPreg_DuratBirth,
				ENO.EvnNotifyHIVPreg_DuratWaterless,
				ENO.HIVPregChemProphType_id,
				ENO.EvnNotifyHIVPreg_SrokChem,
				ENO.EvnNotifyHIVPreg_IsChemProphBirth,
				ENO.HIVPregAbortPeriodType_id,
				ENO.AbortType_id,
				ENO.EvnNotifyHIVPreg_Srok
			from
				v_EvnNotifyHIVPreg ENO with (nolock)
			where
				ENO.EvnNotifyHIVPreg_id = ?
		';
		$res = $this->db->query($query, array($data['EvnNotifyHIVPreg_id']));
		if ( is_object($res) )
		{
			return $res->result('array');
		}
		else
			return false;
	}

	/**
	 * Method description
	 */
	function save($data)
	{
		try {
			if ( empty($data['EvnNotifyHIVPreg_id']) ) {
				$procedure_action = 'ins';
				$out = 'output';
				if (empty($data['Morbus_id']))
				{
					throw new Exception('Не указано заболевание');
				}
			}
			else {
				throw new Exception('Редактирование извещения не предусмотрено!');
				$procedure_action = 'upd';
				$out = '';
			}
			$this->load->library('swMorbus');
			$data['MorbusType_id'] = swMorbus::getMorbusTypeIdBySysNick('hiv');
			if (empty($data['MorbusType_id'])) {
				throw new Exception('Попытка получить идентификатор типа заболевания hiv провалилась', 500);
			}
			$queryEvnNotifyHIVPreg = '
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :EvnNotifyHIVPreg_id;
				exec p_EvnNotifyHIVPreg_' . $procedure_action . '
					@EvnNotifyHIVPreg_id = @Res output,
					@EvnNotifyHIVPreg_pid = :EvnNotifyHIVPreg_pid,
					@Lpu_id = :Lpu_id,
					@Server_id = :Server_id,
					@PersonEvn_id = :PersonEvn_id,
					@Morbus_id = :Morbus_id,
					@MorbusType_id = :MorbusType_id,
					@EvnNotifyHIVPreg_setDT = :EvnNotifyHIVPreg_setDT,
					@MedPersonal_id = :MedPersonal_id,
					
					@EvnNotifyHIVPreg_DiagDT = :EvnNotifyHIVPreg_DiagDT,
					@EvnNotifyHIVPreg_endDT = :EvnNotifyHIVPreg_endDT,
					@HIVPregPathTransType_id = :HIVPregPathTransType_id,
					@HIVPregPeriodType_id = :HIVPregPeriodType_id,
					@HIVPregInfectStudyType_id = :HIVPregInfectStudyType_id,
					@HIVPregInfectStudyType_did = :HIVPregInfectStudyType_did,
					@HIVPregResultType_id = :HIVPregResultType_id,
					@EvnNotifyHIVPreg_IsPreterm = :EvnNotifyHIVPreg_IsPreterm,
					@HIVPregWayBirthType_id = :HIVPregWayBirthType_id,
					@EvnNotifyHIVPreg_OtherWayBirth = :EvnNotifyHIVPreg_OtherWayBirth,
					@EvnNotifyHIVPreg_DuratBirth = :EvnNotifyHIVPreg_DuratBirth,
					@EvnNotifyHIVPreg_DuratWaterless = :EvnNotifyHIVPreg_DuratWaterless,
					@HIVPregChemProphType_id = :HIVPregChemProphType_id,
					@EvnNotifyHIVPreg_SrokChem = :EvnNotifyHIVPreg_SrokChem,
					@EvnNotifyHIVPreg_IsChemProphBirth = :EvnNotifyHIVPreg_IsChemProphBirth,
					@HIVPregAbortPeriodType_id = :HIVPregAbortPeriodType_id,
					@AbortType_id = :AbortType_id,
					@EvnNotifyHIVPreg_Srok = :EvnNotifyHIVPreg_Srok,

					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as EvnNotifyHIVPreg_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			';
			// Стартуем транзакцию
			if ( !$this->beginTransaction() ) {
				throw new Exception('Ошибка при попытке запустить транзакцию');
			}
			//Сохраняем извещение
			$res = $this->db->query($queryEvnNotifyHIVPreg, $data);
			if ( !is_object($res) ) {
				$this->rollbackTransaction();
				throw new Exception('Ошибка БД!');
			}
			$tmp = $res->result('array');
			if ( isset($tmp[0]['Error_Msg']) ) {
				$this->rollbackTransaction();
				throw new Exception($tmp[0]['Error_Msg']);
			}
			
			$this->commitTransaction();
			return $tmp;
		} catch (Exception $e) {
			return array(array('EvnNotifyHIVPreg_id'=>$data['EvnNotifyHIVPreg_id'],'Error_Msg' => 'Cохранениe извещения. <br />'. $e->getMessage()));	
		}
	}

	/**
	 * Method description
	 */
	function del($data)
	{
		$query = '
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :EvnNotifyHIVPreg_id;
			exec p_EvnNotifyHIVPreg_del
				@EvnNotifyHIVPreg_id = @Res,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as EvnNotifyHIVPreg_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		';
		
		$queryParams = array(
			'EvnNotifyHIVPreg_id' => $data['EvnNotifyHIVPreg_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}
}
