<?php
/**
* EvnNotifyHIVDisp_model - модель для работы с таблицей EvnNotifyHIVDisp
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Alexander Permyakov 
* @version      07.2013
*/

/**
 * @property MorbusHIV_model $MorbusHIV
 */
class EvnNotifyHIVDisp_model extends swPgModel
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * @param array $data
	 * @return bool|array
	 */
	function load($data)
	{
		$query = "
			select
				ENO.EvnNotifyHIVDisp_id as \"EvnNotifyHIVDisp_id\",
				ENO.EvnNotifyHIVDisp_pid as \"EvnNotifyHIVDisp_pid\",
				ENO.Morbus_id as \"Morbus_id\",
				ENO.Server_id as \"Server_id\",
				ENO.PersonEvn_id as \"PersonEvn_id\",
				ENO.Person_id as \"Person_id\",
				to_char(ENO.EvnNotifyHIVDisp_setDT, 'dd.mm.yyyy') as \"EvnNotifyHIVDisp_setDT\",
				ENO.MedPersonal_id as \"MedPersonal_id\",
				RTRIM(coalesce(BABY.Person_SurName,'')) ||' '|| RTRIM(coalesce(BABY.Person_FirName,'')) ||' '|| RTRIM(coalesce(BABY.Person_SecName,'')) as \"baby_fio\",
				RTRIM(coalesce(MOTHER.Person_SurName,'')) ||' '|| RTRIM(coalesce(MOTHER.Person_FirName,'')) ||' '|| RTRIM(coalesce(MOTHER.Person_SecName,'')) as \"mother_fio\",
				ENO.Person_mid as \"Person_mid\",
				ENO.EvnNotifyHIVDisp_IsRefuse as \"EvnNotifyHIVDisp_IsRefuse\",
				ENO.HIVChildType_id as \"HIVChildType_id\",
				ENO.EvnNotifyHIVDisp_OtherChild as \"EvnNotifyHIVDisp_OtherChild\",
				ENO.EvnNotifyHIVDisp_Place as \"EvnNotifyHIVDisp_Place\",
				to_char(ENO.EvnNotifyHIVDisp_DiagDT, 'dd.mm.yyyy') as \"EvnNotifyHIVDisp_DiagDT\",
				ENO.EvnNotifyHIVDisp_Diag as \"EvnNotifyHIVDisp_Diag\",
				ENO.EvnNotifyHIVDisp_CountCD4 as \"EvnNotifyHIVDisp_CountCD4\",
				ENO.EvnNotifyHIVDisp_PartCD4 as \"EvnNotifyHIVDisp_PartCD4\",
				lab.MorbusHIVLab_id as \"MorbusHIVLab_id\",
				to_char(lab.MorbusHIVLab_BlotDT, 'dd.mm.yyyy') as \"MorbusHIVLab_BlotDT\",
				lab.MorbusHIVLab_TestSystem as \"MorbusHIVLab_TestSystem\",
				lab.MorbusHIVLab_BlotNum as \"MorbusHIVLab_BlotNum\",
				lab.MorbusHIVLab_BlotResult as \"MorbusHIVLab_BlotResult\",
				to_char(lab.MorbusHIVLab_IFADT, 'dd.mm.yyyy') as \"MorbusHIVLab_IFADT\",
				lab.MorbusHIVLab_IFAResult as \"MorbusHIVLab_IFAResult\",
				lab.Lpu_id as \"Lpuifa_id\",
				to_char(lab.MorbusHIVLab_PCRDT, 'dd.mm.yyyy') as \"MorbusHIVLab_PCRDT\",
				lab.MorbusHIVLab_PCRResult as \"MorbusHIVLab_PCRResult\"
			from
				v_EvnNotifyHIVDisp ENO
				left join v_PersonState BABY on ENO.Person_id = BABY.Person_id
				left join v_PersonState MOTHER on ENO.Person_mid = MOTHER.Person_id
				left join v_MorbusHIVLab lab on ENO.EvnNotifyHIVDisp_id = lab.EvnNotifyBase_id and lab.MorbusHIV_id is null
			where
				ENO.EvnNotifyHIVDisp_id = ?
		";
		$res = $this->db->query($query, array($data['EvnNotifyHIVDisp_id']));
		if ( is_object($res) )
		{
			return $res->result('array');
		}
		else
			return false;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function save($data)
	{
		$this->load->model('MorbusHIV_model','MorbusHIV');
		$this->load->library('swMorbus');
		try {
			if ( empty($data['EvnNotifyHIVDisp_id']) ) {
				$procedure_action = 'ins';
				//Проверяем наличие в системе заболевания ВИЧ у ребенка, если нет, то создаем, но в регистр не включаем
				$tmp = $this->MorbusHIV->checkByPersonRegister(array(
					'Person_id'=>$data['Person_id']
					,'pmUser_id'=>$data['pmUser_id']
				));
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
				if ( isset($tmp[0]['Error_Msg']) ) {
					throw new Exception($tmp[0]['Error_Msg']);
				}
				if (empty($tmp[0]['Morbus_id']) || empty($tmp[0]['MorbusHIV_id']))
				{
					throw new Exception('Ошибка при проверке наличия в системе заболевания ВИЧ у ребенка');
				}
				$data['Morbus_id'] = $tmp[0]['Morbus_id'];
				$data['MorbusHIV_id'] = $tmp[0]['MorbusHIV_id'];
			} else {
				throw new Exception('Редактирование извещения не предусмотрено!');
			}
			$this->load->library('swMorbus');
			$data['MorbusType_id'] = swMorbus::getMorbusTypeIdBySysNick('hiv');
			if (empty($data['MorbusType_id'])) {
				throw new Exception('Попытка получить идентификатор типа заболевания hiv провалилась', 500);
			}
			$queryEvnNotifyHIVDisp = '
				select
					EvnNotifyHIVDisp_id as "EvnNotifyHIVDisp_id",
					Error_Code as "Error_Code",
					Error_Message as "Error_Msg"
				from p_EvnNotifyHIVDisp_' . $procedure_action . '(
					EvnNotifyHIVDisp_id := :EvnNotifyHIVDisp_id,
					EvnNotifyHIVDisp_pid := :EvnNotifyHIVDisp_pid,
					Lpu_id := :Lpu_id,
					Server_id := :Server_id,
					PersonEvn_id := :PersonEvn_id,
					Morbus_id := :Morbus_id,
					MorbusType_id := :MorbusType_id,
					EvnNotifyHIVDisp_setDT := :EvnNotifyHIVDisp_setDT,
					MedPersonal_id := :MedPersonal_id,
					Person_mid := :Person_mid,
					EvnNotifyHIVDisp_IsRefuse := :EvnNotifyHIVDisp_IsRefuse,
					HIVChildType_id := :HIVChildType_id,
					EvnNotifyHIVDisp_OtherChild := :EvnNotifyHIVDisp_OtherChild,
					EvnNotifyHIVDisp_Place := :EvnNotifyHIVDisp_Place,
					EvnNotifyHIVDisp_DiagDT := :EvnNotifyHIVDisp_DiagDT,
					EvnNotifyHIVDisp_Diag := :EvnNotifyHIVDisp_Diag,
					EvnNotifyHIVDisp_CountCD4 := :EvnNotifyHIVDisp_CountCD4,
					EvnNotifyHIVDisp_PartCD4 := :EvnNotifyHIVDisp_PartCD4,
					pmUser_id := :pmUser_id
				)
			';
			// Стартуем транзакцию
			if ( !$this->beginTransaction() ) {
				throw new Exception('Ошибка при попытке запустить транзакцию');
			}
			//Сохраняем извещение
			$res = $this->db->query($queryEvnNotifyHIVDisp, $data);
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
			$data['EvnNotifyBase_id'] = $tmp[0]['EvnNotifyHIVDisp_id'];
			
			//Сохраняем MorbusHIVLab на извещении и сохраняем данные на заболевании ребенка (если они уже были, то они обновятся)
			$tmp = $this->MorbusHIV->saveMorbusHIVLabWithEvnNotifyBase_id($data, true);
			if ( isset($tmp[0]['Error_Msg']) ) {
				$this->rollbackTransaction();
				throw new Exception($tmp[0]['Error_Msg']);
			}
			$response[0]['MorbusHIVLab_id_EvnNotify'] = $tmp[0]['MorbusHIVLab_id'];
			if(isset($tmp[0]['MorbusHIVLab_id_copy'])) $response[0]['MorbusHIVLab_id_MorbusHIV'] = $tmp[0]['MorbusHIVLab_id_copy'];

			// Сохраняем на заболевании ребенка специфику
			$tmpdata = array(
				'pmUser_id'=>$data['pmUser_id'],
				'Person_id'=>$data['Person_id'],
				'Morbus_id'=>$data['Morbus_id'],
				'MorbusHIV_id'=>$data['MorbusHIV_id'],
				'MorbusHIV_DiagDT'=>$data['EvnNotifyHIVDisp_DiagDT'],
				'MorbusHIV_CountCD4'=>$data['EvnNotifyHIVDisp_CountCD4'],
				'MorbusHIV_PartCD4'=>$data['EvnNotifyHIVDisp_PartCD4'],
				'MorbusHIV_NumImmun'=>$data['MorbusHIV_NumImmun'],
				'Mode'=>'evnnotifyhivdisp_form',
			);
			$this->MorbusHIV->isAllowTransaction = false;
			$tmp = $this->MorbusHIV->saveMorbusSpecific($tmpdata);
			if ( isset($tmp[0]['Error_Msg']) ) {
				$this->rollbackTransaction();
				throw new Exception($tmp[0]['Error_Msg']);
			}
			/*if(!empty($data['EvnNotifyHIVDisp_CountCD4']) && !empty($data['EvnNotifyHIVDisp_PartCD4']))
			{
			}*/
			
			if(!empty($data['MorbusHIVChem_data']))
			{
				//Сохраняем Противоретровирусная терапия ребенку на извещении и сохраняем данные на заболевание ребенка
				$response[0]['MorbusHIVChem_id_EvnNotifylist'] = array();
				$response[0]['MorbusHIVChem_id_MorbusHIVlist'] = array();
				ConvertFromWin1251ToUTF8($data['MorbusHIVChem_data']);
				$griddata = @json_decode($data['MorbusHIVChem_data'],true);
				$jsonerror = json_last_error();
				if(!empty($jsonerror) || !is_array($griddata))
				{
					$this->rollbackTransaction();
					throw new Exception('Неправильный формат списка «Противоретровирусная терапия»!');
				}
				foreach($griddata as $item) {
					if(!is_array($item))
					{
						$this->rollbackTransaction();
						throw new Exception('Неправильный формат записи «Противоретровирусная терапия»!');
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
					$tmp = $this->MorbusHIV->saveMorbusHIVChem($tmpdata);
					if ( isset($tmp[0]['Error_Msg']) ) {
						$this->rollbackTransaction();
						throw new Exception($tmp[0]['Error_Msg']);
					}
					$response[0]['MorbusHIVChem_id_EvnNotifylist'][] = $tmp[0]['MorbusHIVChem_id'];
					// Сохраняем на заболевание ребенка
					$tmpdata['MorbusHIV_id'] = $data['MorbusHIV_id'];
					$tmpdata['EvnNotifyBase_id'] = null;
					$tmp = $this->MorbusHIV->saveMorbusHIVChem($tmpdata);
					if ( isset($tmp[0]['Error_Msg']) ) {
						$this->rollbackTransaction();
						throw new Exception($tmp[0]['Error_Msg']);
					}
					$response[0]['MorbusHIVChem_id_MorbusHIVlist'][] = $tmp[0]['MorbusHIVChem_id'];
				}
			}
			
			
			if(!empty($data['MorbusHIVSecDiag_data']))
			{
				//Сохраняем Вторичные заболевания и оппортунистические инфекции ребенку на извещении и сохраняем данные на заболевание ребенка
				$response[0]['MorbusHIVSecDiag_id_EvnNotifylist'] = array();
				$response[0]['MorbusHIVSecDiag_id_MorbusHIVlist'] = array();
				ConvertFromWin1251ToUTF8($data['MorbusHIVSecDiag_data']);
				$griddata = @json_decode($data['MorbusHIVSecDiag_data'],true);
				$jsonerror = json_last_error();
				if(!empty($jsonerror) || !is_array($griddata))
				{
					$this->rollbackTransaction();
					throw new Exception('Неправильный формат списка «Вторичные заболевания и оппортунистические инфекции»!');
				}
				foreach($griddata as $item) {
					if(!is_array($item))
					{
						$this->rollbackTransaction();
						throw new Exception('Неправильный формат записи «Вторичные заболевания и оппортунистические инфекции»!');
					}
					if(empty($item['MorbusHIVSecDiag_setDT']) || !DateTime::createFromFormat('Y-m-d', trim($item['MorbusHIVSecDiag_setDT'])))
					{
						$item['MorbusHIVSecDiag_setDT'] = null;
					}
					// Сохраняем на извещении
					$tmpdata = array(
						'pmUser_id'=>$data['pmUser_id'],
						'EvnNotifyBase_id'=>$data['EvnNotifyBase_id'],
						'MorbusHIVSecDiag_id'=>empty($item['MorbusHIVSecDiag_id'])? null : ((int) $item['MorbusHIVSecDiag_id']),
						'Diag_id'=>empty($item['Diag_id'])? null : ((int) $item['Diag_id']),
						'MorbusHIVSecDiag_setDT'=>empty($item['MorbusHIVSecDiag_setDT'])? null : $item['MorbusHIVSecDiag_setDT'],
					);
					$tmp = $this->MorbusHIV->saveMorbusHIVSecDiag($tmpdata);
					if ( isset($tmp[0]['Error_Msg']) ) {
						$this->rollbackTransaction();
						throw new Exception($tmp[0]['Error_Msg']);
					}
					$response[0]['MorbusHIVSecDiag_id_EvnNotifylist'][] = $tmp[0]['MorbusHIVSecDiag_id'];
					// Сохраняем на заболевание ребенка
					$tmpdata['MorbusHIV_id'] = $data['MorbusHIV_id'];
					$tmpdata['EvnNotifyBase_id'] = null;
					$tmp = $this->MorbusHIV->saveMorbusHIVSecDiag($tmpdata);
					if ( isset($tmp[0]['Error_Msg']) ) {
						$this->rollbackTransaction();
						throw new Exception($tmp[0]['Error_Msg']);
					}
					$response[0]['MorbusHIVSecDiag_id_MorbusHIVlist'][] = $tmp[0]['MorbusHIVSecDiag_id'];
				}
			}

			if(!empty($data['MorbusHIVVac_data']))
			{
				//Сохраняем Вакцинация ребенку на извещении и сохраняем данные на заболевание ребенка
				$response[0]['MorbusHIVVac_id_EvnNotifylist'] = array();
				$response[0]['MorbusHIVVac_id_MorbusHIVlist'] = array();
				ConvertFromWin1251ToUTF8($data['MorbusHIVVac_data']);
				$griddata = @json_decode($data['MorbusHIVVac_data'],true);
				$jsonerror = json_last_error();
				if(!empty($jsonerror) || !is_array($griddata))
				{
					$this->rollbackTransaction();
					throw new Exception('Неправильный формат списка «Вакцинация»!');
				}
				foreach($griddata as $item) {
					if(!is_array($item))
					{
						$this->rollbackTransaction();
						throw new Exception('Неправильный формат записи «Вакцинация»!');
					}
					if(empty($item['MorbusHIVVac_setDT']) || !DateTime::createFromFormat('Y-m-d', trim($item['MorbusHIVVac_setDT'])))
					{
						$item['MorbusHIVVac_setDT'] = null;
					}
					// Сохраняем на извещении
					$tmpdata = array(
						'pmUser_id'=>$data['pmUser_id'],
						'EvnNotifyBase_id'=>$data['EvnNotifyBase_id'],
						'MorbusHIVVac_id'=>empty($item['MorbusHIVVac_id'])? null : ((int) $item['MorbusHIVVac_id']),
						'Drug_id'=>empty($item['Drug_id'])? null : ((int) $item['Drug_id']),
						'MorbusHIVVac_setDT'=>empty($item['MorbusHIVVac_setDT'])? null : $item['MorbusHIVVac_setDT'],
					);
					$tmp = $this->MorbusHIV->saveMorbusHIVVac($tmpdata);
					if ( isset($tmp[0]['Error_Msg']) ) {
						$this->rollbackTransaction();
						throw new Exception($tmp[0]['Error_Msg']);
					}
					$response[0]['MorbusHIVVac_id_EvnNotifylist'][] = $tmp[0]['MorbusHIVVac_id'];
					// Сохраняем на заболевание ребенка
					$tmpdata['MorbusHIV_id'] = $data['MorbusHIV_id'];
					$tmpdata['EvnNotifyBase_id'] = null;
					$tmp = $this->MorbusHIV->saveMorbusHIVVac($tmpdata);
					if ( isset($tmp[0]['Error_Msg']) ) {
						$this->rollbackTransaction();
						throw new Exception($tmp[0]['Error_Msg']);
					}
					$response[0]['MorbusHIVVac_id_MorbusHIVlist'][] = $tmp[0]['MorbusHIVVac_id'];
				}
			}

			$this->commitTransaction();
			return $response;
		} catch (Exception $e) {
			return array(array('EvnNotifyHIVDisp_id'=>$data['EvnNotifyHIVDisp_id'],'Error_Msg' => 'Cохранениe извещения. <br />'. $e->getMessage()));	
		}
	}

	/**
	 * @param array $data
	 * @return bool|array
	 */
	function del($data)
	{
		$query = '
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\",
				:EvnNotifyHIVDisp_id as \"EvnNotifyHIVDisp_id\"
			from p_EvnNotifyHIVDisp_del(
				EvnNotifyHIVDisp_id := :EvnNotifyHIVDisp_id,
				pmUser_id := :pmUser_id
			)
		';
		
		$queryParams = array(
			'EvnNotifyHIVDisp_id' => $data['EvnNotifyHIVDisp_id'],
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
