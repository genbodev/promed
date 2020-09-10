<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Магические свойства
 * @property-read int $morbusTypeId
 * @property-read string $morbusTypeSysNick
 * @property-read string $groupRegistry
 */
class MorbusPregnancy_model extends swModel
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
				declare
				@Error_Code bigint,
				@Morbus_id bigint = :Morbus_id,
				@MorbusPregnancy_id bigint = :MorbusPregnancy_id,
				@Error_Message varchar(4000);
		exec p_MorbusPregnancy_upd
					@MorbusPregnancy_id = @MorbusPregnancy_id output, 
					@Morbus_id = @Morbus_id,
					@pmUser_id = :pmUser_id,
					@AbortType_id = :AbortType_id,
					@BirthResult_id = :BirthResult_id,
					@MorbusPregnancy_BloodLoss=:MorbusPregnancy_BloodLoss,
					@MorbusPregnancy_CountPreg=:MorbusPregnancy_CountPreg,
					@MorbusPregnancy_IsHIV=:MorbusPregnancy_IsHIV,
					@MorbusPregnancy_IsHIVtest=:MorbusPregnancy_IsHIVtest,
					@MorbusPregnancy_IsMedicalAbort=:MorbusPregnancy_IsMedicalAbort,
					@MorbusPregnancy_OutcomDT=:MorbusPregnancy_OutcomDT,
					@MorbusPregnancy_OutcomPeriod=:MorbusPregnancy_OutcomPeriod,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select @MorbusPregnancy_id as MorbusPregnancy_id, @Morbus_id as Morbus_id, 2 as IsCreate, @Error_Code as Error_Code, @Error_Message as Error_Msg;";
			/*$data['MorbusPregnancy_id'] = $data['MorbusPregnancy_id'];
			$data['Morbus_id'] = $data['Morbus_id'];
			
			$data['Person_id'] = $data['Person_id'];
			$data['Diag_id'] = $data['Diag_id'];*/
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
				Preg.MorbusPregnancy_id
			from
				v_MorbusPregnancy Preg with(nolock)
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
			
			/*$sql = "select EP.Evnvizitpl_id as Evn_id,
			EP.Person_id as Person_id,
			EP.Diag_id as Diag_id
			from v_Evnvizitpl EP with (nolock)
			where EP.Evnvizitpl_id = :MorbusPregnancy_pid";
			$result = $this->db->query($sql, array('MorbusPregnancy_pid'=>$data['MorbusPregnancy_pid']));
			if ( is_object($result) )
			{
			$result = $result->result('array');
				if(!count($result)>0){
					return false;
				}
			}
			else
			{
				return false;
			}
			$result[0]['pmUser_id'] = $data['pmUser_id'];
			$result = $this->createMorbusSpecific($result[0]);
			print_r($result);*/
			
		}

		$params['MorbusPregnancy_pid'] = $data['MorbusPregnancy_pid'];

		
		$sql="
			select top 1
			'edit' as accessType,
			preg.MorbusPregnancy_id,
			preg.Evn_pid as MorbusPregnancy_pid,
			preg.Morbus_id,
			preg.MorbusPregnancy_CountPreg,
			convert(varchar(10), preg.MorbusPregnancy_OutcomDT, 104) as MorbusPregnancy_OutcomD,
			convert(varchar(10), preg.MorbusPregnancy_OutcomDT, 108) as MorbusPregnancy_OutcomT,
			preg.BirthResult_id,
			BR.BirthResult_Name as BirthResult_id_Name,
			preg.MorbusPregnancy_OutcomPeriod,
			preg.AbortType_id,
			AT.AbortType_Name as AbortType_id_Name,
			preg.MorbusPregnancy_IsMedicalAbort,
			preg.MorbusPregnancy_BloodLoss,
			2 as MorbusPregnancy_TrueStream,
			'Да' as MorbusPregnancy_TrueStreamName,
			preg.MorbusPregnancy_IsHIVtest,
			preg.MorbusPregnancy_IsHIV,
			isMA.YesNo_Name as MorbusPregnancy_IsMedicalAbortName,
			isH.YesNo_Name as MorbusPregnancy_IsHIVName,
			isHt.YesNo_Name as MorbusPregnancy_IsHIVtestName
			 from v_MorbusPregnancy preg with(nolock)
			 inner join v_BirthResult BR with(nolock) on BR.BirthResult_id = preg.BirthResult_id
			 inner join v_AbortType AT with(nolock) on AT.AbortType_id=preg.AbortType_id
			 left join v_YesNo isMA with(nolock) on isMA.YesNo_id = preg.MorbusPregnancy_IsMedicalAbort
			 left join v_YesNo isH with(nolock) on isH.YesNo_id = preg.MorbusPregnancy_IsHIV
			 left join v_YesNo isHt with(nolock) on isHt.YesNo_id = preg.MorbusPregnancy_IsHIVtest
			 where preg.Evn_pid=:MorbusPregnancy_pid
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
		$query = '
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				
				@MorbusBase_id bigint = :MorbusBase_id,
				@MorbusType_id bigint = :MorbusType_id, --беременость
				@Person_id bigint = :Person_id,
				
				@Morbus_id bigint = :Morbus_id,
				@Diag_id bigint = :Diag_id,
				@Morbus_setDT datetime = :Morbus_setDT,
				
				@MorbusPregnancy_id bigint = :MorbusPregnancy_id,
				@pmUser_id bigint = :pmUser_id,
				@Evn_pid bigint = :Evn_id;

			if isnull(@Morbus_setDT, 0) = 0
			begin
				set @Morbus_setDT = GetDate();
			end
			
			--базовое заболевание данного типа должно быть одно на человека
			select top 1 @MorbusBase_id = MorbusBase_id from v_MorbusBase with (nolock) where Person_id = @Person_id and MorbusType_id = @MorbusType_id and MorbusBase_disDT is null

			if isnull(@MorbusBase_id, 0) = 0
			begin
				exec p_MorbusBase_ins
					@MorbusBase_id = @MorbusBase_id output,
					@Person_id = @Person_id,
					@MorbusBase_setDT = @Morbus_setDT,
					@MorbusType_id = @MorbusType_id,
					@Evn_pid = @Evn_pid,
					@pmUser_id = @pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
			end

			
			if isnull(@MorbusBase_id, 0) > 0 and isnull(@Morbus_id, 0) = 0
			begin
				exec p_Morbus_ins
					@Morbus_id = @Morbus_id output,
					@MorbusBase_id = @MorbusBase_id,
					@Morbus_setDT = @Morbus_setDT,
					@Evn_pid = @Evn_pid,
					@Diag_id = @Diag_id,
					@pmUser_id = @pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
			end

			if isnull(@Morbus_id, 0) > 0 and isnull(@MorbusPregnancy_id, 0) = 0
			begin
				exec p_MorbusPregnancy_ins
					@MorbusPregnancy_id = @MorbusPregnancy_id output, 
					@Morbus_id = @Morbus_id,
					@pmUser_id = @pmUser_id,
					@AbortType_id = :AbortType_id,
					@BirthResult_id = :BirthResult_id,
					@MorbusPregnancy_BloodLoss=:MorbusPregnancy_BloodLoss,
					@MorbusPregnancy_CountPreg=:MorbusPregnancy_CountPreg,
					@MorbusPregnancy_IsHIV=:MorbusPregnancy_IsHIV,
					@MorbusPregnancy_IsHIVtest=:MorbusPregnancy_IsHIVtest,
					@MorbusPregnancy_IsMedicalAbort=:MorbusPregnancy_IsMedicalAbort,
					@MorbusPregnancy_OutcomDT=:MorbusPregnancy_OutcomDT,
					@MorbusPregnancy_OutcomPeriod=:MorbusPregnancy_OutcomPeriod,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select @MorbusPregnancy_id as MorbusPregnancy_id, @Morbus_id as Morbus_id, 2 as IsCreate, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			end
			else
			begin
				select @MorbusPregnancy_id as MorbusPregnancy_id, @Morbus_id as Morbus_id, 1 as IsCreate, @Error_Code as Error_Code, @Error_Message as Error_Msg;					
			end
		';
		try {
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
			preg.Morbus_id,
			preg.MorbusPregnancy_CountPreg,
			convert(varchar(10), preg.MorbusPregnancy_OutcomDT, 104) as MorbusPregnancy_OutcomD,
			convert(varchar(10), preg.MorbusPregnancy_OutcomDT, 108) as MorbusPregnancy_OutcomT,
			preg.MorbusPregnancy_OutcomPeriod,
			preg.AbortType_id,
			preg.MorbusPregnancy_IsMedicalAbort,
			preg.MorbusPregnancy_BloodLoss,
			preg.MorbusPregnancy_IsHIVtest,
			preg.MorbusPregnancy_IsHIV
			 from v_MorbusPregnancy preg with(nolock)
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