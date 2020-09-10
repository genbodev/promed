<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		PromedWeb
 * @access		public
 * @copyright	Copyright (c) 2013 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 * @version		09.2013
 */
require_once('EvnPrescrAbstract_model.php');
/**
 * Модель назначения "Наблюдение"
 *
 * Назначения с типом "Наблюдение" хранятся в таблицах EvnPrescr, EvnPrescrObserv, EvnPrescrObservPos
 * В EvnPrescr хранится само назначение,
 * в EvnPrescrObserv - календарь назначения и тип времени наблюдения, признак выполнения
 * в EvnPrescrObservPos - список параметров наблюдения
 *
 * @package		EvnPrescr
 * @author		Александр Пермяков
 *
 */
class EvnPrescrObserv_model extends EvnPrescrAbstract_model
{
	/**
	 * Конструктор
	 */
	public function __construct()
	{
		parent::__construct();
	}
	public $EvnPrescr_id =null;
	public $out = array();
	public $outForDel = array();
	public $inForDel = array();
	/**
	 * Определение идентификатора типа назначения
	 * @return int
	 */
	public function getPrescriptionTypeId() {
		return 10;
	}

	/**
	 *	Method description
	 */
	public function getFreeDay($data){
		$query = "
declare @vs bigint
declare @prDate bigint
declare @freeDate datetime
set @vs =0
set @freeDate=0
while @freeDate= 0
begin
	set @prDate = (select distinct COUNT(Obs.EvnPrescrObserv_setDate) as cnt from	v_EvnPrescr EP with (nolock)
				inner join v_EvnPrescrObserv Obs with (nolock) on Obs.EvnPrescrObserv_pid = EP.EvnPrescr_id
		where	EP.EvnPrescr_pid = :EvnPrescr_pid  and EP.PrescriptionType_id = 10 and EP.PrescriptionStatusType_id != 3
				and CAST(Obs.EvnPrescrObserv_setDT as date) = CAST(dbo.tzGetDate()+@vs as date))
	if @prDate=0 begin set @freeDate=CAST(dbo.tzGetDate()+@vs as date) end
	else set @vs=@vs+1
	
end
select convert(varchar(10), @freeDate, 104) as FreeDate
		";
		//echo getDebugSQL($query, $queryParams); exit();
		$queryParams['EvnPrescr_pid'] = $data['EvnPrescr_pid'];
		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) ) {
			throw new Exception('Ошибка при выполнении запроса к базе данных (Контроль пересечения дат)', 500);
		}
		$response = $result->result('array');
		if ( !is_array($response) || count($response) == 0 ) {
			throw new Exception('Ошибка при проверке возможности добавить назначение');
		}
		return $response;
		
		
	}
	
	/**
	 * Определение имени таблицы с данными назначения
	 * @return string
	 */
	public function getTableName() {
		return 'EvnPrescrObserv';
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $scenario
	 * @return array
	 */
	public function getInputRules($scenario) {
		$rules = array();
		switch ($scenario) {
			case 'doSave':
				$rules = array(
					array('field' => 'EvnPrescr_id','label' => 'Идентификатор назначения', 'rules' => '', 'type' =>  'id'),
					array('field' => 'EvnPrescr_pid','label' => 'Идентификатор родительского события', 'rules' => '', 'type' =>  'id'),
					array('field' => 'EvnPrescr_setDate','label' => 'Начать', 'rules' => '', 'type' =>  'date'),
					array('field' => 'EvnPrescr_dayNum','label' => 'Продолжать', 'rules' => '', 'type' =>  'int'),
					array('field' => 'observParamTypeList','label' => 'Список типов назначаемых параметров наблюдения', 'rules' => 'trim', 'type' =>  'string'),
					array('field' => 'observTimeTypeList','label' => 'Список времен суток, в которые должно проводиться наблюдение', 'rules' => 'trim', 'type' =>  'string'),
					array('field' => 'EvnPrescr_Descr','label' => 'Комментарий', 'rules' => 'trim', 'type' =>  'string'),
					array('field' => 'PersonEvn_id','label' => 'Идентификатор состояния человека', 'rules' => '', 'type' =>  'id'),
					array('field' => 'Server_id','label' => 'Идентификатор сервера', 'rules' => '', 'type' =>  'int'),
					//array('field' => 'action','label' => 'Действие', 'rules' => '', 'type' =>  'string'),
				);
				break;
			case 'getFreeDay':
				$rules = array(
					array('field' => 'EvnPrescr_pid','label' => 'Идентификатор родительского события', 'rules' => '', 'type' =>  'id')
				);
				break;
			case 'doLoad':
				$rules[] = array(
					'field' => 'EvnPrescr_id',
					'label' => 'Идентификатор назначения',
					'rules' => 'required',
					'type' =>  'id'
				);
				break;
		}
		return $rules;
	}
	
	/**
	 * Контроль пересечения дат
	 */
	protected function hasCrossingDatesTypes($data, $dateList) {
		$lastIndex = count($dateList)-1;
		$where = '';
		$queryParams = array(
			'EvnPrescr_pid' => $data['EvnPrescr_pid'],
			'PrescriptionType_id' => $this->getPrescriptionTypeId(),
			'beg_date' => $dateList[0],
			'end_date' => $dateList[$lastIndex]
		);
		
		if( !empty($data['observParamTypeList']) ){
			$observParamTypeList = json_decode(toUTF($data['observParamTypeList']));
			$where .= ' and eps.ObservParamType_id in ('.implode(",", $observParamTypeList).') ';
		}
		
		$query = "
			select	
				count(ep.EvnPrescr_id) as cnt
			from
				v_EvnPrescr EP with (nolock)
				inner join v_EvnPrescrObserv Obs with (nolock) on Obs.EvnPrescrObserv_pid = EP.EvnPrescr_id
				inner join v_EvnPrescrObservPos eps with (nolock) on eps.EvnPrescr_id = ep.EvnPrescr_id
			where
				EP.EvnPrescr_pid = :EvnPrescr_pid
				and EP.PrescriptionType_id = :PrescriptionType_id
				and EP.PrescriptionStatusType_id != 3
				and CAST(Obs.EvnPrescrObserv_setDT as date) between CAST(:beg_date as datetime) and CAST(:end_date as datetime)
 				{$where}
		";
		//echo getDebugSQL($query, $queryParams); exit();
		$result = $this->getFirstRowFromQuery($query, $queryParams);

		if ( !is_array($result) ) {
			throw new Exception('Ошибка при выполнении запроса к базе данных (Контроль пересечения дат)', 500);
		}
		
		if ( $result['cnt']>0 ) {
			return true;
		}else{
			return false;
		}
	}

	/**
	 * Контроль пересечения дат
	 */
	protected function _hasCrossingDates($data, $dateList) {
		$lastIndex = count($dateList)-1;
		$queryParams = array(
			'EvnPrescr_pid' => $data['EvnPrescr_pid'],
			'PrescriptionType_id' => $this->getPrescriptionTypeId(),
			'beg_date' => $dateList[0],
			'end_date' => $dateList[$lastIndex],
		);
		$add_where = '';
		/*if ( !empty($data['EvnPrescr_id']) ) {
			$add_where .= 'and EP.EvnPrescr_id != :EvnPrescr_id';
			$queryParams['EvnPrescr_id'] = $data['EvnPrescr_id'];
		}*/
		$query = "
			select
		case when EP.PrescriptionStatusType_id = 1 
		and not EXISTS
		(select EOD.EvnObservData_Value
			from
				v_EvnPrescrObservPos EPOP with (nolock)
				inner join v_EvnPrescrObserv EPO with (nolock) on EPO.EvnPrescrObserv_pid = EPOP.EvnPrescr_id
				left join v_EvnObserv EO with (nolock) on EO.EvnObserv_pid = EPO.EvnPrescrObserv_id	
				left join v_EvnObservData EOD with (nolock) on EOD.EvnObserv_id = EO.EvnObserv_id
				where EPO.EvnPrescrObserv_id = Obs.EvnPrescrObserv_id
				and EOD.EvnObservData_Value is not null) then 'edit' else 'view' end as accessType,
			ep.EvnPrescr_id,
			ep.EvnPrescr_IsExec,
			Obs.EvnPrescrObserv_id,
			cast(CAST(Obs.EvnPrescrObserv_setDate as date)as varchar)as EvnPrescrObserv_setDate,
			eps.ObservParamType_id,
			Obs.ObservTimeType_id,
			eps.EvnPrescrObservPos_id,
			case when CAST(Obs.EvnPrescrObserv_setDT as date) between CAST(:beg_date as date) and CAST(:end_date as date) then 'in' else 'out' end as interval
			from
				v_EvnPrescr EP with (nolock)
				inner join v_EvnPrescrObserv Obs with (nolock) on Obs.EvnPrescrObserv_pid = EP.EvnPrescr_id
				inner join v_EvnPrescrObservPos eps with (nolock) on eps.EvnPrescr_id = ep.EvnPrescr_id
		where
				EP.EvnPrescr_pid = :EvnPrescr_pid
				and EP.PrescriptionType_id = :PrescriptionType_id
				and EP.PrescriptionStatusType_id != 3
				and CAST(Obs.EvnPrescrObserv_setDT as date) between CAST(:beg_date as datetime)-60 and CAST(:end_date as datetime)+60
				{$add_where}
		";
		//echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			throw new Exception('Ошибка при выполнении запроса к базе данных (Контроль пересечения дат)', 500);
		}
		$response = $result->result('array');
		if ( !is_array($response)) {
			throw new Exception('Ошибка при проверке возможности добавить назначение');
		}
		$Observ = array();
		$cnt = 0;
		$arr = array();
		if(!empty($data['EvnPrescr_id'])){
			$this->EvnPrescr_id=$data['EvnPrescr_id'];
		}
		$split = array();
		foreach($response as $day){
			if(($day['EvnPrescr_IsExec']==2||($day['accessType']=='view'&&$data['EvnPrescr_id']!=$day['EvnPrescr_id']))){
				if($day['interval']=='in'){
					$this->rollbackTransaction();
					throw new Exception('Указанная продолжительность курса пересекается 
						с продолжительностью курса назначения указанного типа, 
						которое уже имеется в рамках выбранного случая посещения/движения',400);
				}
			}
			if($day['interval']=='out'){
				if(isset($split[$day['EvnPrescr_id']])){
					if(!in_array($day['ObservParamType_id'], $split[$day['EvnPrescr_id']]['ObservParamType_id'])){
						$split[$day['EvnPrescr_id']]['ObservParamType_id'][]=$day['ObservParamType_id'];
						$split[$day['EvnPrescr_id']]['EvnPrescr_id']=$day['EvnPrescr_id'];
						$split[$day['EvnPrescr_id']]['ObservTimeType_id'][] = $day['ObservTimeType_id'];
					}
				}else{
					$split[$day['EvnPrescr_id']]['ObservParamType_id'][]=$day['ObservParamType_id'];
					$split[$day['EvnPrescr_id']]['EvnPrescr_id']=$day['EvnPrescr_id'];
					$split[$day['EvnPrescr_id']]['ObservTimeType_id'][] = $day['ObservTimeType_id'];
				}
				$this->out[$day['EvnPrescr_id']]['prescr_id']=$day['EvnPrescr_id'];
				$this->out[$day['EvnPrescr_id']]['params'][$day['ObservParamType_id']] = $day['EvnPrescrObservPos_id'];
				if(isset($day['EvnPrescrObserv_id'])){
					array_push($this->outForDel,$day['EvnPrescrObserv_id']);
				}
			}else{ 
				if(!in_array($day['EvnPrescrObserv_id'], $Observ)){
					$Observ[]=$day['EvnPrescrObserv_id'];
					$arr[$day['EvnPrescrObserv_id']]['EvnPrescrObserv_id']=$day['EvnPrescrObserv_id'];
					$arr[$day['EvnPrescrObserv_id']]['EvnPrescrObserv_setDate']=$day['EvnPrescrObserv_setDate'];
					$arr[$day['EvnPrescrObserv_id']]['EvnPrescr_id'] = $day['EvnPrescr_id'];
					$arr[$day['EvnPrescrObserv_id']]['accessType'] = $day['accessType'];
				}
				$arr[$day['EvnPrescrObserv_id']]['ObservParamType_id'][] = $day['ObservParamType_id'];
				$arr[$day['EvnPrescrObserv_id']]['ObservTimeType_id'][] = $day['ObservTimeType_id'];
				$arr[$day['EvnPrescrObserv_id']]['params'][$day['ObservParamType_id']] = $day['EvnPrescrObservPos_id'];
				if(isset($day['EvnPrescrObserv_id'])){
					array_push($this->inForDel,$day['EvnPrescrObserv_id']);
				}
			}
			
		}
		foreach($split as $val){
			if($val['ObservParamType_id']==json_decode(toUTF($data['observParamTypeList']), true)
					&&in_array($val['ObservTimeType_id'][0],json_decode(toUTF($data['observTimeTypeList']), true))){
				if($this->EvnPrescr_id==null||$val['EvnPrescr_id']==$this->EvnPrescr_id){
					$this->EvnPrescr_id = $val['EvnPrescr_id'];	
				}else{
					$this->updatePrescrObserv($val['EvnPrescr_id'],$data['pmUser_id']);
				}
			}
		}
		//print_r($split);exit()in_array($row['ObservTimeType_id'][0],;
		return $arr;
		
		return false;
	}

	/**
	 *	Method description
	 */
	public function Clear($EvnPrescr_id,$pmUser,$all=false){
		// календарь полностью очищается
		$response = $this->clearEvnPrescrTable(array(
			'object'=>'EvnPrescrObserv',
			'fk_pid'=>'EvnPrescrObserv_pid',
			'pid'=>$EvnPrescr_id,
			'pmUser_id'=>$pmUser,
		));
		if (!$response) {
			throw new Exception('Не удалось очистить календарь!');
		}
		if ( !empty($response[0]['Error_Msg']) ) {
			throw new Exception($response[0]['Error_Msg']);
		}
		if($all){
			$query="
			declare
			@ErrCode int,
			@ErrMessage varchar(4000);

		exec p_EvnPrescrObserv_del
			@EvnPrescrObserv_id = :EvnPrescrObserv_id,
			@pmUser_id = :pmUser_id,
			@Error_Code = @ErrCode output,
			@Error_Message = @ErrMessage output";
			
			$queryParams=array();
			$queryParams['pmUser_id']=$pmUser;
			$queryParams['EvnPrescrObserv_id']=$EvnPrescr_id;
			//echo getDebugSQL($query, $queryParams); exit();
			$result = $this->db->query($query, $queryParams);

			if ( !is_object($result) ) {
				throw new Exception('Не удалось очистить календарь!');
			}
			
		}else{
			// назначеные параметры полностью очищается
			$response = $this->clearEvnPrescrTable(array(
				'object'=>'EvnPrescrObservPos',
				'fk_pid'=>'EvnPrescr_id',
				'pid'=>$EvnPrescr_id,
			));
			if (!$response) {
				throw new Exception('Не удалось очистить назначеные параметры!');
			}
			if ( !empty($response[0]['Error_Msg']) ) {
				throw new Exception($response[0]['Error_Msg']);
			}
		}
	}
	/**
	 *	Method description
	 */
	public function ClearDay($data){
		//Проверяем существуют ли выполненные назначения (при наблюдении утром и вечером их может быть 2)
		$query0 = 
		"
		select 
			epr.EvnPrescr_id,
			EO.ObservTimeType_id
		from v_EvnPrescr epr with (nolock)
		inner join v_EvnObserv EO with(nolock) on EO.EvnObserv_pid = epr.EvnPrescr_id
		where epr.EvnPrescr_pid in (select ep.EvnPrescr_pid from v_EvnPrescr ep with(nolock) where ep.EvnPrescr_id =:EvnPrescr_id)
		and epr.EvnPrescr_IsExec = 2
		";
		$params0 = array('EvnPrescr_id'=>$data['EvnPrescr_id']);
		
		$result0 = $this->db->query($query0, $params0);
		$response0 = $result0->result('array');
		$dblObservIsExec = false;
		$useNigthId = false;
		$where = "";
		//Если выполненны утреннее и вечернее наблюдение, то исключаем из списка на удаление общие параметры
		if(count($response0)==2){
			$dblObservIsExec = true;
			$where = " and eod.ObservParamType_id not in (5,6,7,8,9,10,11) ";
		} else {
			//Если выполнено только утреннее наблюдение и мы собираемся отменить выполнение, то берем айдишник вечернего для использования при удалении общих параметров (они сохраняются и выводятся всегда с вечерним)
			if($response0[0]['ObservTimeType_id'] == 1){
				$useNigthId = true;
				$query00 = 
				"
				select 
					epr.EvnPrescr_id
				from v_EvnPrescr epr with (nolock)
				inner join v_EvnObserv EO with(nolock) on EO.EvnObserv_pid = epr.EvnPrescr_id
				where epr.EvnPrescr_pid in (select ep.EvnPrescr_pid from v_EvnPrescr ep with(nolock) where ep.EvnPrescr_id =:EvnPrescr_id)
				and EO.ObservTimeType_id = 3
				";
				$params00 = array('EvnPrescr_id'=>$data['EvnPrescr_id']);
				
				$result00 = $this->db->query($query00, $params00);
				$response00 = $result00->result('array');
				$nigthId = $response00[0]['EvnPrescr_id'];
			}
		}

		$query = "select eod.EvnObservData_id,
						eod.ObservParamType_id
					from v_EvnObservData eod with(nolock)
					inner join v_EvnObserv EO with(nolock) on EO.EvnObserv_id = eod.EvnObserv_id
					where EO.EvnObserv_pid =:EvnPrescr_id {$where}";
		$params = array('EvnPrescr_id'=>$data['EvnPrescr_id']);
		
		$result = $this->db->query($query, $params);
		$err = true;
		$response = $result->result('array');
		
		foreach($response as $val){
			if($val['ObservParamType_id']>4 && $useNigthId === true){
				//Для общих параметров при отмене единственно выполненного (из двух - утро и вечер) утреннего наблюдения берем данные сохраненные с вечерним айдишником
				$query1 = "select eod.EvnObservData_id
					from v_EvnObservData eod with(nolock)
					inner join v_EvnObserv EO with(nolock) on EO.EvnObserv_id = eod.EvnObserv_id
					where EO.EvnObserv_pid =:nightId 
					and eod.ObservParamType_id = :ObservParamType_id";
				$params1 = array('EvnPrescr_id'=>$data['EvnPrescr_id'],'ObservParamType_id'=>$val['ObservParamType_id'],'nightId'=>$nigthId);
				
				$result1 = $this->db->query($query1, $params1);
				$response1 = $result1->result('array');
				$val['EvnObservData_id'] = $response1[0]['EvnObservData_id'];
			}
			$query = "declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :EvnObservData_id;

				exec p_EvnObservData_del
				@EvnObservData_id = @Res,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnObservData, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$result = $this->db->query($query,array('EvnObservData_id'=>$val['EvnObservData_id']));
			if (is_object($result)) {
				$response = $result->result('array');
				if (!empty($response[0]['Error_Msg'])) {
					$err=false;
				}
			} else {
				$err=false;
			}
		}
		return $err;
	}
	/**
	 *	Method description
	 */
	public function getCntObserv($EvnPrescr_id,$pmUser){
		$query="select
			COUNT(*) as cnt
			from
				v_EvnPrescr EP with (nolock)
				inner join v_EvnPrescrObserv Obs with (nolock) on Obs.EvnPrescrObserv_pid = EP.EvnPrescr_id
				--inner join v_EvnPrescrObservPos eps with (nolock) on eps.EvnPrescr_id = ep.EvnPrescr_id
			where
				EP.PrescriptionType_id = 10
				and EP.EvnPrescr_id = :EvnPrescr_id
		";
			$queryParams['EvnPrescr_id'] = $EvnPrescr_id;
				
			$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			throw new Exception('Ошибка при выполнении запроса к базе данных (Контроль пересечения дат)', 500);
		}
		$response = $result->result('array');
		if($response[0]['cnt']==0){
			$this->Clear($EvnPrescr_id, $pmUser,true);
		}
	}
	/**
	 *	Method description
	 */
	public function getStatus($EvnPrescr_id){
		$query="select 
			COUNT(*) as cnt
			from
				v_EvnPrescrObservPos EPOP with (nolock)
				inner join v_EvnPrescrObserv EPO with (nolock) on EPO.EvnPrescrObserv_pid = EPOP.EvnPrescr_id
				left join v_EvnObserv EO with (nolock) on EO.EvnObserv_pid = EPO.EvnPrescrObserv_id	
				left join v_EvnObservData EOD with (nolock) on EOD.EvnObserv_id = EO.EvnObserv_id
				where EPO.EvnPrescrObserv_pid = :EvnPrescr_id
				and  EOD.EvnObservData_Value is not null and EOD.EvnObservData_Value!=''
		";
		$queryParams['EvnPrescr_id'] = $EvnPrescr_id;
			
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			throw new Exception('Ошибка при выполнении запроса к базе данных ', 500);
		}
		$response = $result->result('array');
		if($response[0]['cnt']==0){
			return "edit";
		}
		return 'view';
	}
	/**
	 * Сохранение назначения
	 */
	public function doSave($data = array(), $isAllowTransaction = true) {
		// Стартуем транзакцию
		$this->beginTransaction();
		try {
			if (empty($data['EvnPrescr_pid'])) {
				throw new Exception('Не указан Идентификатор родительского события', 400);
			}
			if (empty($data['PersonEvn_id'])) {
				throw new Exception('Не указан Идентификатор состояния человека', 400);
			}
			if (!isset($data['Server_id'])) {
				throw new Exception('Не указан Идентификатор сервера', 400);
			}
			$observParamTypeList = array();
			$observTimeTypeList = array();
			if ( !empty($data['observParamTypeList']) ) {
				$observParamTypeList = json_decode(toUTF($data['observParamTypeList']), true);
			}
			if ( !empty($data['observTimeTypeList']) ) {
				$observTimeTypeList = json_decode(toUTF($data['observTimeTypeList']), true);
			}

			if ( !is_array($observParamTypeList) || count($observParamTypeList) == 0 ) {
				throw new Exception('Не выбран ни один параметр наблюдения', 400);
			}

			if ( !is_array($observTimeTypeList) || count($observTimeTypeList) == 0 ) {
				throw new Exception('Не выбрано время наблюдений', 400);
			}
			if($data['EvnPrescr_id']){
				$data['action'] = $this->getStatus($data['EvnPrescr_id']);
			}
			$dateList = $this->_createDateList($data);
			$action = (empty($data['EvnPrescr_id']) ? 'add' : 'edit');
			if($action == 'edit'&&$data['action']=='edit')
			{
				$this->Clear($data['EvnPrescr_id'], $data['pmUser_id']);
			}
			// контроль пересечения дат
			// ----------- в рамках #122171 ---------------- //
			/*
			$cross=$this->_hasCrossingDates($data, $dateList);
			if ($cross) {
				//throw new Exception('Указанная продолжительность курса пересекается 
				//с продолжительностью курса назначения указанного типа, 
				//которое уже имеется в рамках выбранного случая посещения/движения',400);
			}
			 */
			$cross = array();
			$crossDates=$this->hasCrossingDatesTypes($data, $dateList);
			if ($crossDates) {
				throw new Exception('Ошибка при добавлении нового наблюдения.
				В случае уже добавлено аналогичное наблюдение в том же диапазоне дат.
				Измените параметры или дату начала наблюдения',400);
			}
			
			if ($this->EvnPrescr_id!=NULL&&$data['EvnPrescr_id']==null) {
					$data['EvnPrescr_id']=$this->EvnPrescr_id;
					//echo $this->EvnPrescr_id;
					$data['action'] = $this->getStatus($data['EvnPrescr_id']);
			}
			/*
			foreach ($cross as $row) {
				if(in_array($row['ObservTimeType_id'][0],$observTimeTypeList)){
					//$observParamTypeList = array_unique(array_merge($row['ObservParamType_id'],$observParamTypeList));
				}
				if($data['EvnPrescr_id']!=$row['EvnPrescr_id']||($row['accessType']=='edit'&&$data['action']=='view')){
					$this->_destroy(array(
						'object'=>'EvnPrescrObserv',
						'id' => $row['EvnPrescrObserv_id'],
						'pmUser_id' => $data['pmUser_id'],
					));
					$this->getCntObserv($row['EvnPrescr_id'],$data['pmUser_id']);
				}
			}
			$forDel = $this->inForDel;
			foreach ($forDel as $row) {
				$this->_destroy(array(
					'object'=>'EvnPrescrObserv',
					'id' => $row,
					'pmUser_id' => $data['pmUser_id'],
				));
			}
			*/
			//--------------- end #122171 ------------------//
			
			$data['EvnPrescr_id'] = $this->_save($data);
			
			$idList = $this->_saveCalendar($data, $dateList, $observTimeTypeList,$cross);
			$idList2 = array();
			// Сохранение выбранных типов наблюдения
			//if($this->EvnPrescr_id==null)
			foreach ( $observParamTypeList as $ObservParamType_id ) {
				$EPO_id=null;
				foreach($cross as $row){
					if($row['EvnPrescr_id']==$data['EvnPrescr_id']&&$row['accessType']=='view'){
						foreach($row['params'] as $key=>$val){
							if($ObservParamType_id==$key){
								$EPO_id=$val;
								break;
							}
						}
					}
				}
				if($EPO_id==null){
					foreach($this->out as $row){
						if($row['prescr_id']==$data['EvnPrescr_id']){
							foreach($row['params'] as $key=>$val){
								if($ObservParamType_id==$key){
									$EPO_id=$val;
									break;
								}
							}
						}
					}
				}
				$response = $this->_savePos(array(
					'ObservParamType_id' => $ObservParamType_id,
					'EvnPrescr_id' => $data['EvnPrescr_id'],
					'EvnPrescrObservPos_id' =>$EPO_id,
					'pmUser_id' => $data['pmUser_id']
				));
				if ( !is_array($response) || count($response) == 0 ) {
					throw new Exception('Ошибка при сохранении выбранного типа наблюдения', 500);
				} else if ( !empty($response[0]['Error_Msg']) ) {
					throw new Exception($response[0]['Error_Msg'], 500);
				}
				$idList2[] = $response[0]['EvnPrescrObservPos_id'];
			}
			$this->EvnPrescr_id = $data['EvnPrescr_id'];
		} catch (Exception $e) {
			$this->rollbackTransaction();
			return array(array(
				'Error_Msg'=>$e->getMessage(),
				'Error_Code'=>$e->getCode(),
			));
		}
		$this->commitTransaction();
		$idList = $this->updatePrescrObserv($data['EvnPrescr_id'], $data['pmUser_id']);
		return array(array(
			'EvnPrescr_id'=>$data['EvnPrescr_id'],
			'EvnPrescrObserv_id_list'=>$idList,
			'EvnPrescrObservPos_id_list'=>$idList2,
			'Error_Msg'=>null,
			'Error_Code'=>null,
		));
	}
	/**
	 *	Method description
	 */
	function updatePrescrObserv($EvnPrescr_id,$pmUser_id){
		
		
		$query = "select
						EvnPrescrObserv_id,
						Lpu_id,
						Server_id,
						PersonEvn_id,
						PrescriptionType_id,
						PrescriptionStatusType_id,
						ObservTimeType_id,
						convert(varchar(10), EvnPrescrObserv_setDT, 120) as EvnPrescr_setDate
					from v_EvnPrescrObserv with (nolock) where EvnPrescrObserv_pid = :EvnPrescr_id
					order by EvnPrescrObserv_setDT
		";
		$queryParams = array('EvnPrescr_id' => $EvnPrescr_id);
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception('Ошибка при запросе данных календаря');
		}
		$idList=array();
		$index = 1;
		$response = $result->result('array');
		foreach($response as $val){
			$query = "declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :EvnPrescrObserv_id;

				exec p_EvnPrescrObserv_upd
				@EvnPrescrObserv_id = @Res output,
				@EvnPrescrObserv_pid = :EvnPrescr_id,
				@PrescriptionType_id = :PrescriptionType_id,
				@EvnPrescrObserv_IsCito = 1,
				@Lpu_id = :Lpu_id,
				@EvnPrescrObserv_Index = :EvnPrescrObserv_Index,
				@EvnPrescrObserv_Count = :EvnPrescrObserv_Count,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnPrescrObserv_setDT = :EvnPrescrObserv_setDT,
				@PrescriptionStatusType_id = :PrescriptionStatusType_id,
				@ObservTimeType_id = :ObservTimeType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnPrescrObserv_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$queryParams = array(
				'EvnPrescr_id' => $this->EvnPrescr_id,
				'EvnPrescrObserv_setDT'=>$val['EvnPrescr_setDate'],
				'EvnPrescrObserv_id'=>$val['EvnPrescrObserv_id'],
				'EvnPrescrObserv_Count'=>count($response),
				'Lpu_id'=>$val["Lpu_id"],
				'EvnPrescrObserv_Index'=>$index,
				'PrescriptionStatusType_id'=>1,
				'ObservTimeType_id'=>$val['ObservTimeType_id'],
				'PrescriptionType_id'=>$val['PrescriptionType_id'],
				'PersonEvn_id'=>$val['PersonEvn_id'],
				'Server_id'=>$val['Server_id'],
				'pmUser_id'=>$pmUser_id);
			//echo getDebugSQL($query, $queryParams);
			$result = $this->db->query($query, $queryParams);
			if (is_object($result)) {
				$response = $result->result('array');
				if (!empty($response[0]['Error_Msg'])) {
					throw new Exception($response[0]['Error_Msg'], 500);
				}
				$idList[] = $response[0]['EvnPrescrObserv_id'];
			} else {
				throw new Exception('Ошибка при запросе к БД при сохранении календаря', 500);
			}
			$index++;
		}
		if($this->EvnPrescr_id!=$EvnPrescr_id){

			$this->getCntObserv($EvnPrescr_id, $pmUser_id);
		}
		return $idList;
	}
	/**
	 * Сохранение календаря в EvnPrescrObserv
	 */
	protected function _saveCalendar($data, $dateList, $observTimeTypeList,$cross) {
		

		$queryParams = array(
			'EvnPrescrObserv_id'=>null,
			'EvnPrescr_id' => $data['EvnPrescr_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'PrescriptionType_id' => $this->getPrescriptionTypeId(),
			'PrescriptionStatusType_id' => 1,
			'pmUser_id' => $data['pmUser_id'],
		);
		$idList = array();
		foreach ( $dateList as $addDate ) {
			$action='ins';
			$queryParams['EvnPrescrObserv_id'] = null;
			
			$queryParams['EvnPrescrObserv_setDT'] = $addDate;
			foreach ( $observTimeTypeList as $ObservTimeType_id ) {
				$queryParams['ObservTimeType_id'] = $ObservTimeType_id;
				foreach($cross as $row){
					if($row['EvnPrescr_id']==$data['EvnPrescr_id']&&$row['EvnPrescrObserv_setDate']==$addDate&&$row['accessType']=='view'&&$ObservTimeType_id==$row['ObservTimeType_id'][0]){
						$queryParams['EvnPrescrObserv_id'] = $row['EvnPrescrObserv_id'];
						$action='upd';
						break;
					}
				}
				$query = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);

					set @Res = :EvnPrescrObserv_id;

					exec p_EvnPrescrObserv_".$action."
						@EvnPrescrObserv_id = @Res output,
						@EvnPrescrObserv_pid = :EvnPrescr_id,
						@PrescriptionType_id = :PrescriptionType_id,
						@EvnPrescrObserv_IsCito = 1,
						@Lpu_id = :Lpu_id,
						@Server_id = :Server_id,
						@PersonEvn_id = :PersonEvn_id,
						@EvnPrescrObserv_setDT = :EvnPrescrObserv_setDT,
						@PrescriptionStatusType_id = :PrescriptionStatusType_id,
						@ObservTimeType_id = :ObservTimeType_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					select @Res as EvnPrescrObserv_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				 //echo getDebugSQL($query, $queryParams);
				$result = $this->db->query($query, $queryParams);
				if ( is_object($result) ) {
					$response = $result->result('array');
					if ( !empty($response[0]['Error_Msg']) ) {
						throw new Exception($response[0]['Error_Msg'], 500);
					}
					$idList[] = $response[0]['EvnPrescrObserv_id'];
				}
				else {
					throw new Exception('Ошибка при запросе к БД при сохранении календаря', 500);
				}
			}
		}
		return $idList;
	}

	/**
	 * Метод сохранения параметра наблюдения
	 */
	private function _savePos($data) {
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :EvnPrescrObservPos_id;

			exec p_EvnPrescrObservPos_". (!empty($data['EvnPrescrObservPos_id']) && $data['EvnPrescrObservPos_id'] > 0 ? "upd" : "ins") ."
				@EvnPrescrObservPos_id = @Res output,
				@EvnPrescr_id = :EvnPrescr_id,
				@ObservParamType_id = :ObservParamType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnPrescrObservPos_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'EvnPrescrObservPos_id'=>$data['EvnPrescrObservPos_id'],
			'EvnPrescr_id' => $data['EvnPrescr_id'],
			'ObservParamType_id' => $data['ObservParamType_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение данных для формы редактирования
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array|boolean
	 */
	public function doLoad($data) {
		$query = "
	select 
		case when EP.PrescriptionStatusType_id = 1 
		and not EXISTS
		(select EOD.EvnObservData_Value
			from
				v_EvnPrescrObservPos EPOP with (nolock)
				inner join v_EvnPrescrObserv EPO with (nolock) on EPO.EvnPrescrObserv_pid = EPOP.EvnPrescr_id
				left join v_EvnObserv EO with (nolock) on EO.EvnObserv_pid = EPO.EvnPrescrObserv_id	
				left join v_EvnObservData EOD with (nolock) on EOD.EvnObserv_id = EO.EvnObserv_id
				where EPO.EvnPrescrObserv_pid = :EvnPrescr_id
				and EOD.EvnObservData_Value is not null) then 'edit' else 'view' end as accessType,
		EP.EvnPrescr_id,
		EP.EvnPrescr_pid,
		EP.EvnPrescr_IsCito,
		EP.EvnPrescr_Descr,
		EP.PersonEvn_id,
		EP.Server_id,
		convert(varchar(10), EPP.EvnPrescrObserv_setDT, 104) as EvnPrescrObserv_setDT,
		EPP.ObservTimeType_id,
		eps.ObservParamType_id
	from
		v_EvnPrescr ep with (nolock)
		inner join v_EvnPrescrObserv EPP with (nolock) on EPP.EvnPrescrObserv_pid = ep.EvnPrescr_id
		inner join v_EvnPrescrObservPos eps with (nolock) on eps.EvnPrescr_id = ep.EvnPrescr_id
	where 
		ep.EvnPrescr_id = :EvnPrescr_id
		order by EPP.EvnPrescrObserv_setDT
		";

		$queryParams = array(
			'EvnPrescr_id' => $data['EvnPrescr_id']
		);
		//echo getDebugSql($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$tmp_arr = $result->result('array');
			if(count($tmp_arr) > 0)
			{
				$response = array();
				$dateList = array();
				$TimeTypeList = array();
				$paramtype = array();
			}
			else
			{
				return $tmp_arr;
			}
			foreach($tmp_arr as $row) {
				if (!in_array($row['EvnPrescrObserv_setDT'], $dateList))
					$dateList[] = $row['EvnPrescrObserv_setDT'];
				if (!in_array($row['ObservTimeType_id'], $TimeTypeList))
					$TimeTypeList[] = $row['ObservTimeType_id'];
				if (!in_array($row['ObservParamType_id'], $paramtype))
					$paramtype[] = $row['ObservParamType_id'];
			}
			$response[0] = $tmp_arr[0];
			$response[0]['EvnPrescr_setDate'] = $dateList[0];
			$response[0]['EvnPrescr_dayNum'] = count($dateList);
			$response[0]['ObservTimeType_id'] = $TimeTypeList;
			$response[0]['ObservParamType_id'] = $paramtype;
			return $response;
		}
		else {
			return false;
		}
	}


	/**
	 * Получение данных для панели просмотра ЭМК и правой панели формы добавления назначений
	 */
	public function doLoadViewData($section, $evn_pid, $sessionParams) {
		$sysnick = swPrescription::getParentEvnClassSysNickBySectionName($section);
		$addJoin = '';
		if ($sysnick) {
			$accessType = "case when {$sysnick}.Lpu_id = :Lpu_id AND isnull({$sysnick}.{$sysnick}_IsSigned,1) = 1 then 'edit' else 'view' end as accessType";
			$addJoin = "left join v_{$sysnick} {$sysnick} with (nolock) on {$sysnick}.{$sysnick}_id = EP.EvnPrescr_pid";
		} else {
			$accessType = "'view' as accessType";
		}
		$query = "
			select
				{$accessType},
				EP.EvnPrescr_id
				,EP.EvnPrescr_pid
				,EP.EvnPrescr_rid
				,convert(varchar,Obs.EvnPrescrObserv_setDT,104) as EvnPrescr_setDate
				,null as EvnPrescr_setTime
				,isnull(Obs.EvnPrescrObserv_IsExec,1) as EvnPrescr_IsExec
				,Obs.PrescriptionStatusType_id
				,EP.PrescriptionType_id
				,EP.PrescriptionType_id as PrescriptionType_Code
				,isnull(EP.EvnPrescr_IsCito,1) as EvnPrescr_IsCito
				,Obs.EvnPrescrObserv_Descr as EvnPrescr_Descr,
				Obs.EvnPrescrObserv_id,
				OTT.ObservTimeType_id,
				OTT.ObservTimeType_Name,
				EPOP.EvnPrescrObservPos_id,
				OPT.ObservParamType_Name
			from v_EvnPrescr EP with (nolock)
				inner join v_EvnPrescrObserv Obs with (nolock) on Obs.EvnPrescrObserv_pid = EP.EvnPrescr_id
				left join v_ObservTimeType OTT with (nolock) on OTT.ObservTimeType_id = Obs.ObservTimeType_id
				left join v_EvnPrescrObservPos EPOP with (nolock) on EPOP.EvnPrescr_id = EP.EvnPrescr_id
				left join ObservParamType OPT with (nolock) on OPT.ObservParamType_id = EPOP.ObservParamType_id
				{$addJoin}
			where
				EP.EvnPrescr_pid  = :EvnPrescr_pid
				and EP.PrescriptionType_id = 10
				and Obs.PrescriptionStatusType_id != 3
			order by
				EP.EvnPrescr_id,
				Obs.EvnPrescrObserv_setDT
		";

		$queryParams = array(
			'EvnPrescr_pid' => $evn_pid,
			'Lpu_id' => $sessionParams['lpu_id'],
		);
		//echo getDebugSql($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$tmp_arr = $result->result('array');
			$response = array();
			$last_ep = null;
			$is_exe = null;
			$is_sign = null;
			$first_index = 0;
			$time_arr = array();
			$params_arr = array();
			foreach ($tmp_arr as $i => $row) {
				if ($last_ep != $row['EvnPrescr_id']) {
					//это первая итерация с другим назначением
					$first_index = $i;
					$last_ep = $row['EvnPrescr_id'];
					$is_exe = false;
					$is_sign = false;
					$time_arr = array();
					$params_arr = array();
				}
				if (!in_array($row['ObservTimeType_Name'],$time_arr))
					$time_arr[] = $row['ObservTimeType_Name'];
				if (empty($params_arr[$row['EvnPrescrObservPos_id']]))
					$params_arr[$row['EvnPrescrObservPos_id']] = $row['ObservParamType_Name'];
				if ($is_exe == true) $is_exe = ($row['EvnPrescr_IsExec'] == 2);
				if ($is_sign == false) $is_sign = ($row['PrescriptionStatusType_id'] == 2);
				if (empty($tmp_arr[$i+1]) || $last_ep != $tmp_arr[$i+1]['EvnPrescr_id']) {
					$row['EvnPrescr_IsExec'] = $is_exe ? 2 : 1;
					if ($is_sign) $row['PrescriptionStatusType_id'] = 2;
					$row['Params'] = implode(', ',$params_arr);
					$row['EvnPrescr_setTime'] = implode(', ',$time_arr);
					$row['EvnPrescr_setDate'] = $tmp_arr[$first_index]['EvnPrescr_setDate'].'&nbsp;—&nbsp;'.$row['EvnPrescr_setDate'];
					$row[$section . '_id'] = $row['EvnPrescr_id'].'-'.$row['EvnPrescrObserv_id'];
					$response[] = $row;
				}
			}
			return $response;
		} else {
			return false;
		}
	}
}
