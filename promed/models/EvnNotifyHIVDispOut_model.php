<?php
/**
* EvnNotifyHIVDispOut_model - модель для работы с таблицей EvnNotifyHIVDispOut
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

class EvnNotifyHIVDispOut_model extends swModel
{
	/**
	 * Method description
	 */
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	* Проверка наличия ребенка в регистре с открытым заболеванием
	*/
	function checkEvnNotifyHIVDispOut($data)
	{
		$queryParams = array(
			'Person_id' => $data['Person_id'],
		);
		$this->load->library('swMorbus');
		$queryParams['MorbusType_id'] = swMorbus::getMorbusTypeIdBySysNick('hiv');
		if (empty($queryParams['MorbusType_id'])) {
			throw new Exception('Попытка получить идентификатор типа заболевания hiv провалилась', 500);
		}
		$query = "
			select top 1
				M.Morbus_id
				,PR.PersonRegister_id
			from
				v_Morbus M with (nolock)
				inner join v_MorbusBase MB with (nolock) on MB.MorbusBase_id = M.MorbusBase_id
				left join v_PersonRegister PR with (nolock) on M.Morbus_id = PR.Morbus_id and PR.PersonRegister_disDate is null
			where
				MB.Person_id = :Person_id
				and MB.MorbusType_id = :MorbusType_id
				and M.Morbus_disDT is null 
		";
		$res = $this->db->query($query, $queryParams);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return array(array('Error_Msg' => 'Проверка наличия ребенка в регистре с открытым заболеванием. Ошибка БД.'));
		}
	}

	/**
	 * Method description
	 */
	function load($data)
	{
		$query = '
			select
				ENO.EvnNotifyHIVDispOut_id,
				ENO.EvnNotifyHIVDispOut_pid,
				ENO.Morbus_id,
				ENO.Server_id,
				ENO.PersonEvn_id,
				ENO.Person_id,
				convert(varchar,ENO.EvnNotifyHIVDispOut_setDT,104) as EvnNotifyHIVDispOut_setDT,
				ENO.MedPersonal_id,
				
				RTRIM(isnull(BABY.Person_SurName,\'\')) +\' \'+ RTRIM(isnull(BABY.Person_FirName,\'\')) +\' \'+ RTRIM(isnull(BABY.Person_SecName,\'\')) as baby_fio,
				RTRIM(isnull(MOTHER.Person_SurName,\'\')) +\' \'+ RTRIM(isnull(MOTHER.Person_FirName,\'\')) +\' \'+ RTRIM(isnull(MOTHER.Person_SecName,\'\')) as mother_fio,
				ENO.Person_mid,
				ENO.EvnNotifyHIVDispOut_IsRefuse,
				ENO.HIVChildType_id,
				ENO.EvnNotifyHIVDispOut_OtherChild,
				ENO.Lpu_rid,
				convert(varchar,ENO.EvnNotifyHIVDispOut_endDT,104) as EvnNotifyHIVDispOut_endDT,
				ENO.HIVDispOutCauseType_id,
				ENO.Diag_id,
				
				lab.MorbusHIVLab_id,
				convert(varchar,lab.MorbusHIVLab_BlotDT,104) as MorbusHIVLab_BlotDT,
				lab.MorbusHIVLab_TestSystem,
				lab.MorbusHIVLab_BlotNum,
				lab.MorbusHIVLab_BlotResult,
				convert(varchar,lab.MorbusHIVLab_IFADT,104) as MorbusHIVLab_IFADT,
				lab.Lpu_id as Lpuifa_id,
				lab.MorbusHIVLab_IFAResult,
				convert(varchar,lab.MorbusHIVLab_PCRDT,104) as MorbusHIVLab_PCRDT,
				lab.MorbusHIVLab_PCRResult
			from
				v_EvnNotifyHIVDispOut ENO with (nolock)
				--left join v_Diag Diag with (nolock) on ENO.Diag_id = Diag.Diag_id
				left join v_PersonState BABY with (nolock) on ENO.Person_id = BABY.Person_id
				left join v_PersonState MOTHER with (nolock) on ENO.Person_mid = MOTHER.Person_id
				left join v_MorbusHIVLab lab with (nolock) on ENO.EvnNotifyHIVDispOut_id = lab.EvnNotifyBase_id and lab.MorbusHIV_id is null
			where
				ENO.EvnNotifyHIVDispOut_id = ?
		';
		$res = $this->db->query($query, array($data['EvnNotifyHIVDispOut_id']));
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
		$this->load->model('MorbusHIV_model','MorbusHIV_model');
		$this->load->library('swMorbus');
		try {
			if ( empty($data['EvnNotifyHIVDispOut_id']) ) {
				$procedure_action = 'ins';
				$out = 'output';
				//Проверяем наличие в системе заболевания ВИЧ у ребенка, если нет, то создаем, но в регистр не включаем
				/*try {
					$tmp = swMorbus::checkByPersonRegister($this->MorbusType_SysNick, array(
						'isDouble' => (isset($this->Mode) && $this->Mode == 'new'),
						'Diag_id' => $this->Diag_id,
						'Person_id' => $this->Person_id,
						'Morbus_setDT' => $this->PersonRegister_setDate,
						'session' => $this->sessionParams,
					), 'onBeforeSavePersonRegister');
				} catch (Exception $e) {
					return array(array('Error_Msg' => $e->getMessage()));
				}*/
				$tmp = $this->MorbusHIV_model->checkByPersonRegister(array(
					'Person_id'=>$data['Person_id']
					,'pmUser_id'=>$data['pmUser_id']
				));
				if ( isset($tmp[0]['Error_Msg']) ) {
					throw new Exception($tmp[0]['Error_Msg']);
				}
				if (empty($tmp[0]['Morbus_id']) || empty($tmp[0]['MorbusHIV_id']))
				{
					throw new Exception('Ошибка при проверке наличия в системе заболевания ВИЧ у ребенка');
				}
				$data['Morbus_id'] = $tmp[0]['Morbus_id'];
				$data['MorbusHIV_id'] = $tmp[0]['MorbusHIV_id'];
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
			if (empty($data['Lpu_rid'])) {
				$tmp = $this->MorbusHIV_model->defineBirthSvidLpu($data);
				if ( isset($tmp[0]['Error_Msg']) ) {
					throw new Exception($tmp[0]['Error_Msg']);
				}
				if(count($tmp) > 0) {
					$data['Lpu_rid'] = $tmp[0]['Lpu_id'];
				}
			}
			$queryEvnNotifyHIVDispOut = '
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :EvnNotifyHIVDispOut_id;
				exec p_EvnNotifyHIVDispOut_' . $procedure_action . '
					@EvnNotifyHIVDispOut_id = @Res output,
					@EvnNotifyHIVDispOut_pid = :EvnNotifyHIVDispOut_pid,
					@Lpu_id = :Lpu_id,
					@Server_id = :Server_id,
					@PersonEvn_id = :PersonEvn_id,
					@Morbus_id = :Morbus_id,
					@MorbusType_id = :MorbusType_id,
					@EvnNotifyHIVDispOut_setDT = :EvnNotifyHIVDispOut_setDT,
					@MedPersonal_id = :MedPersonal_id,

					@Person_mid = :Person_mid,
					@EvnNotifyHIVDispOut_IsRefuse = :EvnNotifyHIVDispOut_IsRefuse,
					@HIVChildType_id = :HIVChildType_id,
					@EvnNotifyHIVDispOut_OtherChild = :EvnNotifyHIVDispOut_OtherChild,
					@Lpu_rid = :Lpu_rid,
					@EvnNotifyHIVDispOut_endDT = :EvnNotifyHIVDispOut_endDT,
					@HIVDispOutCauseType_id = :HIVDispOutCauseType_id,
					@Diag_id = :Diag_id,

					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as EvnNotifyHIVDispOut_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			';

			// Стартуем транзакцию
			if ( !$this->beginTransaction() ) {
				throw new Exception('Ошибка при попытке запустить транзакцию');
			}
			
			//Сохраняем извещение
			$res = $this->db->query($queryEvnNotifyHIVDispOut, $data);
			if ( !is_object($res) ) {
				$this->rollbackTransaction();
				throw new Exception('Ошибка БД!');
			}
			$tmp = $res->result('array');
			if ( isset($tmp[0]['Error_Msg']) ) {
				$this->rollbackTransaction();
				throw new Exception($tmp[0]['Error_Msg']);
			}
			$response = $tmp;
			$data['EvnNotifyBase_id'] = $tmp[0]['EvnNotifyHIVDispOut_id'];

			//Сохраняем MorbusHIVLab на извещении и сохраняем данные на заболевании ребенка (если они уже были, то они обновятся)
			$tmp = $this->MorbusHIV_model->saveMorbusHIVLabWithEvnNotifyBase_id($data, true);
			if ( isset($tmp[0]['Error_Msg']) ) {
				$this->rollbackTransaction();
				throw new Exception($tmp[0]['Error_Msg']);
			}
			$response[0]['MorbusHIVLab_id_EvnNotify'] = $tmp[0]['MorbusHIVLab_id'];
			if(isset($tmp[0]['MorbusHIVLab_id_copy'])) $response[0]['MorbusHIVLab_id_MorbusHIV'] = $tmp[0]['MorbusHIVLab_id_copy'];
			
			if(!empty($data['EvnNotifyHIVDispOut_endDT']) && isset($data['HIVDispOutCauseType_id']))
			{
				//Проверка наличия ребенка в регистре с открытым заболеванием
				$tmp = $this->checkEvnNotifyHIVDispOut($data);
				if(count($tmp) > 0)
				{
					if ( isset($tmp[0]['Error_Msg']) ) {
						$this->rollbackTransaction();
						throw new Exception($tmp[0]['Error_Msg']);
					}
					if ( isset($tmp[0]['PersonRegister_id']) ) {
						// Нужно исключить из регистра
						$PersonRegisterOutCause_id = 3;
						switch($data['HIVDispOutCauseType_id']) {
							case 3:// умер
								$PersonRegisterOutCause_id = 1; // Смерть
								break;
							case 2:// выбыл
								$PersonRegisterOutCause_id = 2; // Выехал
								break;
							case 1:// отсутствие клинических симптомов и отрицательные результаты лабораторной диагностики
								$PersonRegisterOutCause_id = 3; // Выздоровление
								break;
						}
						$this->load->model('PersonRegister_model', 'PersonRegister_model');
						$tmp = $this->PersonRegister_model->out(array(
							'PersonRegister_id' => $tmp[0]['PersonRegister_id']
							,'MedPersonal_did' => $data['MedPersonal_id']
							,'Lpu_did' => $data['Lpu_id']
							,'PersonRegisterOutCause_id' => $PersonRegisterOutCause_id
							,'PersonRegister_disDate' => $data['EvnNotifyHIVDispOut_endDT']
							,'pmUser_id' => $data['pmUser_id']
						));
					} else {
						//Нужно закрыть заболевание
						$this->load->model('Morbus_model', 'Morbus_model');
						$tmp = $this->Morbus_model->closeMorbus(array(
							'Morbus_id' => $tmp[0]['Morbus_id']
							,'Morbus_disDT' => $data['EvnNotifyHIVDispOut_endDT']
							,'pmUser_id' => $data['pmUser_id']
						));
					}
					if ( isset($tmp[0]['Error_Msg']) ) {
						$this->rollbackTransaction();
						throw new Exception($tmp[0]['Error_Msg']);
					}
				}
			}
			
			$this->commitTransaction();
			return $response;
		} catch (Exception $e) {
			return array(array('EvnNotifyHIVDispOut_id'=>$data['EvnNotifyHIVDispOut_id'],'Error_Msg' => 'Cохранениe извещения. <br />'. $e->getMessage()));	
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
			set @Res = :EvnNotifyHIVDispOut_id;
			exec p_EvnNotifyHIVDispOut_del
				@EvnNotifyHIVDispOut_id = @Res,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as EvnNotifyHIVDispOut_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		';
		
		$queryParams = array(
			'EvnNotifyHIVDispOut_id' => $data['EvnNotifyHIVDispOut_id'],
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