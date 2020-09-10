<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Магические свойства
 * @property-read int $morbusTypeId
 * @property-read string $morbusTypeSysNick
 * @property-read string $groupRegistry
 */
class MorbusPregnancy_model extends swPgModel
{
	protected $_MorbusType_id = null;//2,23

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * @return string
	 */
	function getMorbusTypeSysNick()
	{
		return 'pregnancy';
	}

	/**
	 * @return int
	 * @throws Exception
	 */
	function getMorbusTypeId()
	{
		if (empty($this->_MorbusType_id)) {
			$this->load->library('swMorbus');
			$this->_MorbusType_id = swMorbus::getMorbusTypeIdBySysNick($this->getMorbusTypeSysNick());
			if (empty($this->_MorbusType_id)) {
				throw new Exception('Не удалось определить тип заболевания', 500);
			}
		}
		return $this->_MorbusType_id;
	}

	/**
	 * @return string
	 */
	function getGroupRegistry()
	{
		return null;
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'MorbusPregnancy';
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function saveMorbusPregnancy($data){
		if($data['MorbusPregnancy_id']==null){
			$result = $this->createMorbusSpecific($data);
			return $result;
		}else{
			$sql = "
				select
					MorbusPregnancy_id as \"MorbusPregnancy_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\",
					Morbus_id as \"Morbus_id\",
					2 as \"isCreate\"
				from p_MorbusPregnancy_upd(
					MorbusPregnancy_id := :MorbusPregnancy_id, 
					Morbus_id := :Morbus_id,
					pmUser_id := :pmUser_id,
					AbortType_id := :AbortType_id,
					BirthResult_id := :BirthResult_id,
					MorbusPregnancy_BloodLoss := :MorbusPregnancy_BloodLoss,
					MorbusPregnancy_CountPreg := :MorbusPregnancy_CountPreg,
					MorbusPregnancy_IsHIV := :MorbusPregnancy_IsHIV,
					MorbusPregnancy_IsHIVtest := :MorbusPregnancy_IsHIVtest,
					MorbusPregnancy_IsMedicalAbort := :MorbusPregnancy_IsMedicalAbort
					MorbusPregnancy_OutcomDT := :MorbusPregnancy_OutcomDT,
					MorbusPregnancy_OutcomPeriod := :MorbusPregnancy_OutcomPeriod
				)
			";
			$data['Morbus_setDT'] =$data['EvnVizitPL_setDate'];
			$data['AbortType_id'] = isset($data['AbortType_id'])?$data['AbortType_id']:null;
			$data['BirthResult_id'] = isset($data['BirthResult_id'])?$data['BirthResult_id']:3;
			$data['MorbusPregnancy_BloodLoss'] = isset($data['MorbusPregnancy_BloodLoss'])?$data['MorbusPregnancy_BloodLoss']:null;
			$data['MorbusPregnancy_CountPreg'] = isset($data['MorbusPregnancy_CountPreg'])?$data['MorbusPregnancy_CountPreg']:null;
			$data['MorbusPregnancy_IsHIV'] = isset($data['MorbusPregnancy_IsHIV'])?$data['MorbusPregnancy_IsHIV']:null;
			$data['MorbusPregnancy_IsHIVtest'] = isset($data['MorbusPregnancy_IsHIVtest'])?$data['MorbusPregnancy_IsHIVtest']:null;
			$data['MorbusPregnancy_IsMedicalAbort'] = isset($data['MorbusPregnancy_IsMedicalAbort'])?$data['MorbusPregnancy_IsMedicalAbort']:null;
			$data['MorbusPregnancy_OutcomDT'] = (isset($data['MorbusPregnancy_OutcomD'])&&isset($data['MorbusPregnancy_OutcomT']))?$data['MorbusPregnancy_OutcomD'].' '.$data['MorbusPregnancy_OutcomT']:null;
			$data['MorbusPregnancy_OutcomPeriod'] = isset($data['MorbusPregnancy_OutcomPeriod'])?$data['MorbusPregnancy_OutcomPeriod']:null;

			//$data['Lpu_id'] = isset($data['Lpu_id'])?$data['Lpu_id']:null;
			//echo getDebugSQL($query, $data); exit();
			$result = $this->db->query($sql, $data);
			if ( !is_object($result) )
			{
				throw new Exception('Ошибка БД');
			}
			return $result->result('array');
		}
	}

	/**
	 * Метод получения данных онкозаболевания
	 * При вызове из формы просмотра записи регистра параметр MorbusPregnancy_pid будет содержать Person_id, также будет передан PersonRegister_id
	 * При вызове из формы просмотра движения/посещения параметр MorbusPregnancy_pid будет содержать Evn_id просматриваемого движения/посещения
	 * @param $data
	 * @return mixed
	 */
	function getViewData($data)
	{
		if(empty($data['MorbusPregnancy_pid']))
		{
			return false;
		}
		$params = array();
		//Ищем Morbus_id по PersonRegister_id или по Evn_id
		$params['Evn_id'] = $data['MorbusPregnancy_pid'];
		$query = '
			select
				Preg.MorbusPregnancy_id as \"MorbusPregnancy_id\"
			from
				v_MorbusPregnancy Preg
			where
				Preg.Evn_pid = :Evn_id
		';
		//echo getDebugSQL($query, $params);
		$result = $this->db->query($query, $params);
		if ( is_object($result) )
		{
			$response = $result->result('array');
			$params = null;
			if(isset($response[0]))
			{
				$params = $response[0];
			}
		}
		else
		{
			return false;
		}

		if(empty($params['MorbusPregnancy_id']))
		{


		}

		$params['MorbusPregnancy_pid'] = $data['MorbusPregnancy_pid'];


		$sql="
			select
				'edit' as \"accessType\",
				preg.MorbusPregnancy_id as \"MorbusPregnancy_id\",
				preg.Evn_pid as \"MorbusPregnancy_pid\",
				preg.Morbus_id as \"Morbus_id\",
				preg.MorbusPregnancy_CountPreg as \"MorbusPregnancy_CountPreg\",
				to_char(preg.MorbusPregnancy_OutcomDT, 'dd.mm.yyyy') as \"MorbusPregnancy_OutcomD\",
				to_char(preg.MorbusPregnancy_OutcomDT, 'HH24:MI:SS') as \"MorbusPregnancy_OutcomT\",
				preg.BirthResult_id as \"BirthResult_id\",
				BR.BirthResult_Name as \"BirthResult_id_Name\",
				preg.MorbusPregnancy_OutcomPeriod as \"MorbusPregnancy_OutcomPeriod\",
				preg.AbortType_id as \"AbortType_id\",
				AT.AbortType_Name as \"AbortType_id_Name\",
				preg.MorbusPregnancy_IsMedicalAbort as \"MorbusPregnancy_IsMedicalAbort\",
				preg.MorbusPregnancy_BloodLoss as \"MorbusPregnancy_BloodLoss\",
				2 as \"MorbusPregnancy_TrueStream\",
				'Да' as \"MorbusPregnancy_TrueStreamName\",
				preg.MorbusPregnancy_IsHIVtest as \"MorbusPregnancy_IsHIVtest\",
				preg.MorbusPregnancy_IsHIV as \"MorbusPregnancy_IsHIV\",
				isMA.YesNo_Name as \"MorbusPregnancy_IsMedicalAbortName\",
				isH.YesNo_Name as \"MorbusPregnancy_IsHIVName\",
				isHt.YesNo_Name as \"MorbusPregnancy_IsHIVtestName\"
			from v_MorbusPregnancy preg
				inner join v_BirthResult BR on BR.BirthResult_id = preg.BirthResult_id
				inner join v_AbortType AT on AT.AbortType_id=preg.AbortType_id
				left join v_YesNo isMA on isMA.YesNo_id = preg.MorbusPregnancy_IsMedicalAbort
				left join v_YesNo isH on isH.YesNo_id = preg.MorbusPregnancy_IsHIV
				left join v_YesNo isHt on isHt.YesNo_id = preg.MorbusPregnancy_IsHIVtest
			where preg.Evn_pid=:MorbusPregnancy_pid
			limit 1
		";
		//echo getDebugSQL($sql, $params); exit;
		$result = $this->db->query($sql, $params);
		if ( is_object($result) )
		{
			$res = $result->result('array');
			if (count($res)>0) {
				return $res;
			} else {
				return array(array('MorbusPregnancy_id'=>0,
					'MorbusPregnancy_pid'=>$data['MorbusPregnancy_pid'],
					'Morbus_id'=>null,
					'MorbusPregnancy_CountPreg'=>null,
					'MorbusPregnancy_OutcomD'=>null,
					'MorbusPregnancy_OutcomT'=>null,
					'BirthResult_id'=>3,
					'BirthResult_id_Name'=>'Аборт',
					'MorbusPregnancy_TrueStream'=>1,
					'MorbusPregnancy_TrueStreamName'=>'Нет',
					'MorbusPregnancy_OutcomPeriod'=>null,
					'AbortType_id'=>null,
					'MorbusPregnancy_IsMedicalAbort'=>null,
					'MorbusPregnancy_BloodLoss'=>null,
					'MorbusPregnancy_IsHIVtest'=>null,
					'MorbusPregnancy_IsHIV'=>null
				));
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Создание заболевания у человека
	 * Обязательные параметры:
	 * 1) Evn_pid или пара Person_id и Diag_id
	 * 2) pmUser_id
	 * @return array
	 */
	private function createMorbusSpecific($data)
	{
		$data['MorbusPregnancy_id'] = null;
		$data['Morbus_id'] = isset($data['Morbus_id'])?$data['Morbus_id']:null;
		$data['MorbusBase_id'] = isset($data['MorbusBase_id'])?$data['MorbusBase_id']:null;
		$data['Evn_id'] = isset($data['Evn_id'])?$data['Evn_id']:null;
		$data['Person_id'] = isset($data['Person_id'])?$data['Person_id']:null;
		$data['Diag_id'] = isset($data['Diag_id'])?$data['Diag_id']:null;
		$data['Morbus_setDT'] = isset($data['EvnVizitPL_setDate'])?$data['EvnVizitPL_setDate']:null;
		$data['AbortType_id'] = isset($data['AbortType_id'])?$data['AbortType_id']:null;
		$data['BirthResult_id'] = isset($data['BirthResult_id'])?$data['BirthResult_id']:3;
		$data['MorbusPregnancy_BloodLoss'] = isset($data['MorbusPregnancy_BloodLoss'])?$data['MorbusPregnancy_BloodLoss']:null;
		$data['MorbusPregnancy_CountPreg'] = isset($data['MorbusPregnancy_CountPreg'])?$data['MorbusPregnancy_CountPreg']:1;
		$data['MorbusPregnancy_IsHIV'] = isset($data['MorbusPregnancy_IsHIV'])?$data['MorbusPregnancy_IsHIV']:null;
		$data['MorbusPregnancy_IsHIVtest'] = isset($data['MorbusPregnancy_IsHIVtest'])?$data['MorbusPregnancy_IsHIVtest']:null;
		$data['MorbusPregnancy_IsMedicalAbort'] = isset($data['MorbusPregnancy_IsMedicalAbort'])?$data['MorbusPregnancy_IsMedicalAbort']:null;
		$data['MorbusPregnancy_OutcomDT'] = (isset($data['MorbusPregnancy_OutcomD'])&&isset($data['MorbusPregnancy_OutcomT']))?$data['MorbusPregnancy_OutcomD'].' '.$data['MorbusPregnancy_OutcomT']:null;
		$data['MorbusPregnancy_OutcomPeriod'] = isset($data['MorbusPregnancy_OutcomPeriod'])?$data['MorbusPregnancy_OutcomPeriod']:null;
		$qr = $this->queryResult("
			select
				MorbusBase_id as \"MorbusBase_id\"
			from v_MorbusBase
			where
				Person_id = :Person_id
				and MorbusType_id :MorbusType_id
				and MorbusBase_disDT is null
			limit 1
			",$data);

		if (empty($qr[0]['MorbusBase_id'])) {
			$data['MorbusBase_id'] = $qr[0]['MorbusBase_id'];
			$qr = $this->queryResult("
				select
					MorbusBase_id as \"MorbusBase_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_MorbusBase_ins(
					MorbusBase_id := :MorbusBase_id
					MorbusBase_setDT := coalesce(:Morbus_setDT, dbo.tzgetdate()),
					MorbusType_id := :MorbusType_id,
					Evn_pid := :Evn_id,
					pmUser_id := :pmUser_id
				)
			", $data);
			if ($this->isSuccessful($qr) && !empty($qr[0]['MorbusBase_id'])) {
				$data['MorbusBase_id'] = $qr[0]['MorbusBase_id'];
			}
		}
		if (!empty($data['MorbusBase_id']) && $data['MorbusBase_id'] > 0 and empty($data['Morbus_id'])) {
			$qr = $this->queryResult("
				select
					Morbus_id as \"Morbus_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_MorbusBase_ins(
					Morbus_id := :Morbus_id,
					MorbusBase_id := :MorbusBase_id,
					Morbus_setDT := coalesce(:Morbus_setDT, dbo.tzgetdate()),
					Evn_pid := :Evn_id,
					Diag_id := :Diag_id,
					pmUser_id := :pmUser_id
				)
			", $data);
			if ($this->isSuccessful($qr) && !empty($qr[0]['Morbus_id'])) {
				$data['Morbus_id'] = $qr[0]['Morbus_id'];
			}
		}
		if (!empty($data['Morbus_id']) && $data['Morbus_id'] > 0 and empty($data['MorbusPregnancy_id'])) {
			$query = "
				select
					MorbusPregnancy_id as \"MorbusPregnancy_id\",
					Morbus_id as \"Morbus_id\",
					2 as \"IsCreate\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_MorbusPregnancy_ins(
					MorbusPregnancy_id := :MorbusPregnancy_id, 
					Morbus_id := :Morbus_id,
					pmUser_id := :pmUser_id,
					AbortType_id := :AbortType_id,
					BirthResult_id := :BirthResult_id,
					MorbusPregnancy_BloodLoss := :MorbusPregnancy_BloodLoss,
					MorbusPregnancy_CountPreg := :MorbusPregnancy_CountPreg,
					MorbusPregnancy_IsHIV := :MorbusPregnancy_IsHIV,
					MorbusPregnancy_IsHIVtest := :MorbusPregnancy_IsHIVtest,
					MorbusPregnancy_IsMedicalAbort := :MorbusPregnancy_IsMedicalAbort,
					MorbusPregnancy_OutcomDT := :MorbusPregnancy_OutcomDT,
					MorbusPregnancy_OutcomPeriod := :MorbusPregnancy_OutcomPeriod
				)";
		} else {
			$query = "
				select
					:MorbusPregnancy_id as \"MorbusPregnancy_id\",
					:Morbus_id as \"Morbus_id\",
					1 as \"IsCreate\"
			";
		}
		try {

			$data['MorbusType_id'] = $this->getMorbusTypeId();

			$result = $this->db->query($query, $data);
			if ( !is_object($result) )
			{
				throw new Exception('Ошибка БД');
			}
			return $result->result('array');
		} catch (Exception $e) {
			return array(array('Error_Msg' => 'Создание заболевания у человека. '. $e->getMessage()));
		}
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function load($data){
		$sql="
			select 
				preg.Morbus_id as \"Morbus_id\",
				preg.MorbusPregnancy_CountPreg as \"MorbusPregnancy_CountPreg\",
				to_char(preg.MorbusPregnancy_OutcomDT, 'dd.mm.yyyy') as \"MorbusPregnancy_OutcomD\",
				to_char(preg.MorbusPregnancy_OutcomDT, 'HH24:MI:SS') as \"MorbusPregnancy_OutcomT\",
				preg.MorbusPregnancy_OutcomPeriod as \"MorbusPregnancy_OutcomPeriod\",
				preg.AbortType_id as \"AbortType_id\",
				preg.MorbusPregnancy_IsMedicalAbort as \"MorbusPregnancy_IsMedicalAbort\",
				preg.MorbusPregnancy_BloodLoss as \"MorbusPregnancy_BloodLoss\",
				preg.MorbusPregnancy_IsHIVtest as \"MorbusPregnancy_IsHIVtest\",
				preg.MorbusPregnancy_IsHIV as \"MorbusPregnancy_IsHIV\"
			from v_MorbusPregnancy preg
			where preg.MorbusPregnancy_id=:MorbusPregnancy_id
		";
		$result = $this->db->query($sql, array('MorbusPregnancy_id'=>$data['MorbusPregnancy_id']));
		if ( !is_object($result) )
		{
			throw new Exception('Ошибка БД');
		}
		return $result->result('array');
	}
}