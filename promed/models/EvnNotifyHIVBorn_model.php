<?php
/**
* EvnNotifyHIVBorn_model - модель для работы с таблицей EvnNotifyHIVBorn
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

class EvnNotifyHIVBorn_model extends swModel
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
	function load($data)
	{
		$query = '
			select
				ENO.EvnNotifyHIVBorn_id,
				ENO.EvnNotifyHIVBorn_pid,
				ENO.Morbus_id,
				ENO.Server_id,
				ENO.PersonEvn_id,
				ENO.Person_id,
				convert(varchar,ENO.EvnNotifyHIVBorn_setDT,104) as EvnNotifyHIVBorn_setDT,
				ENO.MedPersonal_id,
				convert(varchar,ENO.EvnNotifyHIVBorn_HIVDT,104) as EvnNotifyHIVBorn_HIVDT,
				convert(varchar,ENO.EvnNotifyHIVBorn_FirstPregDT,104) as EvnNotifyHIVBorn_FirstPregDT,
				RTRIM(isnull(BABY.Person_SurName,\'\')) +\' \'+ RTRIM(isnull(BABY.Person_FirName,\'\')) +\' \'+ RTRIM(isnull(BABY.Person_SecName,\'\')) as baby_fio,
				RTRIM(isnull(MOTHER.Person_SurName,\'\')) +\' \'+ RTRIM(isnull(MOTHER.Person_FirName,\'\')) +\' \'+ RTRIM(isnull(MOTHER.Person_SecName,\'\')) as mother_fio,
				ENO.Person_mid,
				ENO.EvnNotifyHIVBorn_ChildMass,
				ENO.EvnNotifyHIVBorn_ChildHeight,
				ENO.EvnNotifyHIVBorn_IsRefuse,
				ENO.Lpu_rid,
				ENO.EvnNotifyHIVBorn_IsBreastFeed,
				ENO.EvnNotifyHIVBorn_Diag,
				ENO.Lpu_fid,
				ENO.HIVRegPregnancyType_id,
				ENO.HIVPregPathTransType_id,
				ENO.EvnNotifyHIVBorn_IsCes,
				ENO.EvnNotifyHIVBorn_Srok				
			from
				v_EvnNotifyHIVBorn ENO with (nolock)
				left join v_PersonState BABY with (nolock) on ENO.Person_id = BABY.Person_id
				left join v_PersonState MOTHER with (nolock) on ENO.Person_mid = MOTHER.Person_id
			where
				ENO.EvnNotifyHIVBorn_id = ?
		';
		$res = $this->db->query($query, array($data['EvnNotifyHIVBorn_id']));
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
		try {
			if ( empty($data['EvnNotifyHIVBorn_id']) )
			{
				$procedure_action = 'ins';
				$out = 'output';
				//Проверяем наличие в системе заболевания ВИЧ у ребенка, если нет, то создаем, но в регистр не включаем
				/*$tmp = $this->MorbusHIV_model->checkByPersonRegister(array(
					'Person_id'=>$data['Person_id']
					,'pmUser_id'=>$data['pmUser_id']
				));*/
				try {
					$tmp = swMorbus::checkByPersonRegister($this->MorbusType_SysNick, array(
						'isDouble' => (isset($this->Mode) && $this->Mode == 'new'),
						'Diag_id' => $this->Diag_id,
						'Person_id' => $this->Person_id,
						'Morbus_setDT' => $this->PersonRegister_setDate,
						'session' => $this->sessionParams,
					), 'onBeforeSavePersonRegister');
				} catch (Exception $e) {
					return array(array('Error_Msg' => $e->getMessage()));
				}
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
			else
			{
				throw new Exception('Редактирование извещения не предусмотрено!');
				$procedure_action = 'upd';
				$out = '';
			}
			$this->load->library('swMorbus');
			$data['MorbusType_id'] = swMorbus::getMorbusTypeIdBySysNick('hiv');
			if (empty($data['MorbusType_id'])) {
				throw new Exception('Попытка получить идентификатор типа заболевания hiv провалилась', 500);
			}
			if(empty($data['Lpu_rid']))
			{
				$tmp = $this->MorbusHIV_model->defineBirthSvidLpu($data);
				if ( isset($tmp[0]['Error_Msg']) ) {
					throw new Exception($tmp[0]['Error_Msg']);
				}
				if(count($tmp) > 0) {
					$data['Lpu_rid'] = $tmp[0]['Lpu_id'];
				}
			}
			$queryEvnNotifyHIVBorn = '
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :EvnNotifyHIVBorn_id;
				exec p_EvnNotifyHIVBorn_' . $procedure_action . '
					@EvnNotifyHIVBorn_id = @Res output,
					@EvnNotifyHIVBorn_pid = :EvnNotifyHIVBorn_pid,
					@Lpu_id = :Lpu_id,
					@Server_id = :Server_id,
					@PersonEvn_id = :PersonEvn_id,
					@Morbus_id = :Morbus_id,
					@MorbusType_id = :MorbusType_id,
					@EvnNotifyHIVBorn_setDT = :EvnNotifyHIVBorn_setDT,
					@MedPersonal_id = :MedPersonal_id,
					
					@Person_mid = :Person_mid,
					@EvnNotifyHIVBorn_ChildMass = :EvnNotifyHIVBorn_ChildMass,
					@EvnNotifyHIVBorn_ChildHeight = :EvnNotifyHIVBorn_ChildHeight,
					@EvnNotifyHIVBorn_IsRefuse = :EvnNotifyHIVBorn_IsRefuse,
					@Lpu_rid = :Lpu_rid,
					@EvnNotifyHIVBorn_IsBreastFeed = :EvnNotifyHIVBorn_IsBreastFeed,
					@EvnNotifyHIVBorn_Diag = :EvnNotifyHIVBorn_Diag,
					@EvnNotifyHIVBorn_FirstPregDT = :EvnNotifyHIVBorn_FirstPregDT,
					@Lpu_fid = :Lpu_fid,
					@HIVRegPregnancyType_id = :HIVRegPregnancyType_id,
					@EvnNotifyHIVBorn_HIVDT = :EvnNotifyHIVBorn_HIVDT,
					@HIVPregPathTransType_id = :HIVPregPathTransType_id,
					@EvnNotifyHIVBorn_IsCes = :EvnNotifyHIVBorn_IsCes,
					@EvnNotifyHIVBorn_Srok = :EvnNotifyHIVBorn_Srok,

					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as EvnNotifyHIVBorn_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			';
			// Стартуем транзакцию
			if ( !$this->beginTransaction() ) {
				throw new Exception('Ошибка при попытке запустить транзакцию');
			}
			//Сохраняем извещение
			$res = $this->db->query($queryEvnNotifyHIVBorn, $data);
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
			$data['EvnNotifyBase_id'] = $tmp[0]['EvnNotifyHIVBorn_id'];
			
			if(!empty($data['MorbusHIVChem_data']))
			{
				//Сохраняем Проведение химиопрофилактики ВИЧ-инфекции ребенку на извещении и сохраняем данные на заболевание ребенка
				$response[0]['MorbusHIVChem_id_EvnNotifylist'] = array();
				$response[0]['MorbusHIVChem_id_MorbusHIVlist'] = array();
				ConvertFromWin1251ToUTF8($data['MorbusHIVChem_data']);
				$griddata = @json_decode($data['MorbusHIVChem_data'],true);
				$jsonerror = json_last_error();
				if(!empty($jsonerror) || !is_array($griddata))
				{
					$this->rollbackTransaction();
					throw new Exception('Неправильный формат списка «Проведение химиопрофилактики ВИЧ-инфекции ребенку»!');
				}
				foreach($griddata as $item) {
					if(!is_array($item))
					{
						$this->rollbackTransaction();
						throw new Exception('Неправильный формат записи «Проведение химиопрофилактики ВИЧ-инфекции ребенку»!');
					}
					if(empty($item['MorbusHIVChem_begDT']) || !DateTime::createFromFormat('Y-m-d', trim($item['MorbusHIVChem_begDT'])))
					{
						$item['MorbusHIVChem_begDT'] = null;
					}
					if(empty($item['MorbusHIVChem_endDT']) || !DateTime::createFromFormat('Y-m-d', trim($item['MorbusHIVChem_endDT'])))
					{
						$item['MorbusHIVChem_endDT'] = null;
					}
					ConvertFromUTF8ToWin1251($item['MorbusHIVChem_Dose']);
					// Сохраняем на извещении
					$tmpdata = array(
						'pmUser_id'=>$data['pmUser_id'],
						'EvnNotifyBase_id'=>$data['EvnNotifyBase_id'],
						'MorbusHIVChem_id'=>empty($item['MorbusHIVChem_id'])? null : ((int) $item['MorbusHIVChem_id']),
						'Drug_id'=>empty($item['Drug_id'])? null : ((int) $item['Drug_id']),
						'MorbusHIVChem_Dose'=>empty($item['MorbusHIVChem_Dose'])? null : strip_tags($item['MorbusHIVChem_Dose']),
						'MorbusHIVChem_begDT'=>empty($item['MorbusHIVChem_begDT'])? null : $item['MorbusHIVChem_begDT'],
						'MorbusHIVChem_endDT'=>empty($item['MorbusHIVChem_endDT'])? null : $item['MorbusHIVChem_endDT'],
					);
					$tmp = $this->MorbusHIV_model->saveMorbusHIVChem($tmpdata);
					if ( isset($tmp[0]['Error_Msg']) ) {
						$this->rollbackTransaction();
						throw new Exception($tmp[0]['Error_Msg']);
					}
					$response[0]['MorbusHIVChem_id_EvnNotifylist'][] = $tmp[0]['MorbusHIVChem_id'];
					// Сохраняем на заболевание ребенка
					$tmpdata['MorbusHIV_id'] = $data['MorbusHIV_id'];
					$tmpdata['EvnNotifyBase_id'] = null;
					$tmp = $this->MorbusHIV_model->saveMorbusHIVChem($tmpdata);
					if ( isset($tmp[0]['Error_Msg']) ) {
						$this->rollbackTransaction();
						throw new Exception($tmp[0]['Error_Msg']);
					}
					$response[0]['MorbusHIVChem_id_MorbusHIVlist'][] = $tmp[0]['MorbusHIVChem_id'];
				}
			}
			
			if(!empty($data['MorbusHIVChemPreg_data']))
			{
				//Сохраняем Проведение перинатальной профилактики ВИЧ на извещении и сохраняем данные на заболевание ребенка
				$response[0]['MorbusHIVChemPreg_id_EvnNotifylist'] = array();
				$response[0]['MorbusHIVChemPreg_id_MorbusHIVlist'] = array();
				ConvertFromWin1251ToUTF8($data['MorbusHIVChemPreg_data']);
				$griddata = @json_decode($data['MorbusHIVChemPreg_data'],true);
				$jsonerror = json_last_error();
				if(!empty($jsonerror) || !is_array($griddata))
				{
					$this->rollbackTransaction();
					throw new Exception('Неправильный формат списка «Проведение перинатальной профилактики ВИЧ»!');
				}
				foreach($griddata as $item) {
					if(!is_array($item))
					{
						$this->rollbackTransaction();
						throw new Exception('Неправильный формат записи «Проведение перинатальной профилактики ВИЧ»!');
					}
					ConvertFromUTF8ToWin1251($item['MorbusHIVChemPreg_Dose']);
					$tmpdata = array(
						'pmUser_id'=>$data['pmUser_id'],
						'EvnNotifyBase_id'=>$data['EvnNotifyBase_id'],
						'MorbusHIVChemPreg_id'=>empty($item['MorbusHIVChemPreg_id'])? null : ((int) $item['MorbusHIVChemPreg_id']),
						'Drug_id'=>empty($item['Drug_id'])? null : ((int) $item['Drug_id']),
						'MorbusHIVChemPreg_Dose'=>empty($item['MorbusHIVChemPreg_Dose'])? null : strip_tags($item['MorbusHIVChemPreg_Dose']),
						'HIVPregnancyTermType_id'=>empty($item['HIVPregnancyTermType_id'])? null : ((int) $item['HIVPregnancyTermType_id']),
					);
					$tmp = $this->MorbusHIV_model->saveMorbusHIVChemPreg($tmpdata);
					if ( isset($tmp[0]['Error_Msg']) ) {
						$this->rollbackTransaction();
						throw new Exception($tmp[0]['Error_Msg']);
					}
					$response[0]['MorbusHIVChemPreg_id_EvnNotifylist'][] = $tmp[0]['MorbusHIVChemPreg_id'];
					// Сохраняем на заболевание ребенка
					$tmpdata['MorbusHIV_id'] = $data['MorbusHIV_id'];
					$tmpdata['EvnNotifyBase_id'] = null;
					$tmp = $this->MorbusHIV_model->saveMorbusHIVChemPreg($tmpdata);
					if ( isset($tmp[0]['Error_Msg']) ) {
						$this->rollbackTransaction();
						throw new Exception($tmp[0]['Error_Msg']);
					}
					$response[0]['MorbusHIVChemPreg_id_MorbusHIVlist'][] = $tmp[0]['MorbusHIVChemPreg_id'];
				}
			}

			$this->commitTransaction();
			return $response;
		} catch (Exception $e) {
			return array(array('EvnNotifyHIVBorn_id'=>$data['EvnNotifyHIVBorn_id'],'Error_Msg' => 'Cохранениe извещения. <br />'. $e->getMessage()));	
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
			set @Res = :EvnNotifyHIVBorn_id;
			exec p_EvnNotifyHIVBorn_del
				@EvnNotifyHIVBorn_id = @Res,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as EvnNotifyHIVBorn_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		';
		
		$queryParams = array(
			'EvnNotifyHIVBorn_id' => $data['EvnNotifyHIVBorn_id'],
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
