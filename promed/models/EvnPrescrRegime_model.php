<?php

defined('BASEPATH') or die('No direct script access allowed');
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
 * Модель назначения "Режим"
 *
 * Назначения с типом "Режим" хранятся в таблицах EvnPrescr, EvnPrescrRegime
 * В EvnPrescr хранится само назначение, а в EvnPrescrRegime - календарь назначения и тип режима, признак выполнения
 *
 * @package		EvnPrescr
 * @author		Александр Пермяков
 */
class EvnPrescrRegime_model extends EvnPrescrAbstract_model {

	/**
	 * Конструктор
	 */
	public function __construct() {
		parent::__construct();
	}

	public $EvnPrescr_id = null;

	/**
	 * Определение идентификатора типа назначения
	 * @return int
	 */
	public function getPrescriptionTypeId() {
		return 1;
	}

	/**
	 * Определение имени таблицы с данными назначения
	 * @return string
	 */
	public function getTableName() {
		return 'EvnPrescrRegime';
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
					array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => '', 'type' => 'id'),
					array('field' => 'EvnPrescr_pid', 'label' => 'Идентификатор родительского события', 'rules' => '', 'type' => 'id'),
					array('field' => 'EvnPrescr_setDate', 'label' => 'Начать', 'rules' => '', 'type' => 'date'),
					array('field' => 'EvnPrescr_dayNum', 'label' => 'Продолжать', 'rules' => '', 'type' => 'int'),
					array('field' => 'PrescriptionRegimeType_id', 'label' => 'Тип режима', 'rules' => '', 'type' => 'id'),
					array('field' => 'EvnPrescr_Descr', 'label' => 'Комментарий', 'rules' => 'trim', 'type' => 'string'),
					array('field' => 'PersonEvn_id', 'label' => 'Идентификатор состояния человека', 'rules' => '', 'type' => 'id'),
					array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => '', 'type' => 'int'),
					array('field' => 'accessType', 'label' => 'Режим', 'rules' => '', 'type' => 'string'),
				);
				break;
			case 'doLoad':
				$rules[] = array(
					'field' => 'EvnPrescr_id',
					'label' => 'Идентификатор назначения',
					'rules' => 'required',
					'type' => 'id'
				);
				break;
		}
		return $rules;
	}

	/**
	 *
	 * @param type $EvnPrescr_id
	 * @param type $pmUser_id
	 * @return type 
	 */
	function updatePrescrRegime($EvnPrescr_id, $pmUser_id,$filter=null) {

		if($filter==null){
			$filter="";
		}
		$query = "select
						EvnPrescrRegime_id,
						Lpu_id,
						Server_id,
						PersonEvn_id,
						PrescriptionType_id,
						PrescriptionRegimeType_id,
						PrescriptionStatusType_id,
						convert(varchar(10), EvnPrescrRegime_setDT, 120) as EvnPrescr_setDate
					from v_EvnPrescrRegime with (nolock) where EvnPrescrRegime_pid = :EvnPrescr_id
					".$filter."
					order by EvnPrescrRegime_setDT
				";
		$queryParams = array('EvnPrescr_id' => $EvnPrescr_id);
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception('Ошибка при запросе данных календаря');
		}
		//if($EvnPrescr_id==$this->EvnPrescr_id)
		//echo getDebugSQL($query, $queryParams); exit();
		$idList = array();
		$index = 1;
		$response = $result->result('array');
		//echo count($response)." - ";
		foreach ($response as $val) {
			$query = "declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :EvnPrescrRegime_id;

			exec p_EvnPrescrRegime_upd
				@EvnPrescrRegime_id = @Res output,
				@EvnPrescrRegime_pid = :EvnPrescr_id,
				@Lpu_id = :Lpu_id,
				@PrescriptionType_id = :PrescriptionType_id,
				@PersonEvn_id = :PersonEvn_id,
				@PrescriptionRegimeType_id = :PrescriptionRegimeType_id,
				@Server_id = :Server_id,
				
				@EvnPrescrRegime_setDT=:EvnPrescrRegime_setDT,
				@PrescriptionStatusType_id = :PrescriptionStatusType_id,
				@EvnPrescrRegime_IsCito = 1,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnPrescrRegime_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$queryParams = array(
				'EvnPrescr_id' => $this->EvnPrescr_id,
				'EvnPrescrRegime_setDT' => $val['EvnPrescr_setDate'],
				'EvnPrescrRegime_id' => $val['EvnPrescrRegime_id'],
				'EvnPrescrRegime_Count' => count($response),
				'Lpu_id' => $val["Lpu_id"],
				'EvnPrescrRegime_Index' => $index,
				'PrescriptionStatusType_id' => $val['PrescriptionStatusType_id'],
				'PrescriptionRegimeType_id' => $val['PrescriptionRegimeType_id'],
				'PrescriptionType_id' => $val['PrescriptionType_id'],
				'PersonEvn_id' => $val['PersonEvn_id'],
				'Server_id' => $val['Server_id'],
				'pmUser_id' => $pmUser_id);
			//echo getDebugSQL($query, $queryParams);exit();
			$result = $this->db->query($query, $queryParams);
			if (is_object($result)) {
				$response = $result->result('array');
				if (!empty($response[0]['Error_Msg'])) {
					throw new Exception($response[0]['Error_Msg'], 500);
				}
				$idList[] = $response[0]['EvnPrescrRegime_id'];
			} else {
				throw new Exception('Ошибка при запросе к БД при сохранении календаря', 500);
			}
			$index++;
		}
		if ($this->EvnPrescr_id != $EvnPrescr_id) {

			$this->getCntRegime($EvnPrescr_id, $pmUser_id);
		}
		return $idList;
	}

	/**
	 * Контроль пересечения дат
	 */
	protected function _hasCrossingDates($data, $dateList) {
		$lastIndex = count($dateList) - 1;
		$queryParams = array(
			'EvnPrescr_pid' => $data['EvnPrescr_pid'],
			'PrescriptionType_id' => $this->getPrescriptionTypeId(),
			'beg_date' => $dateList[0],
			'end_date' => $dateList[$lastIndex],
		);
		$add_where = '';
		/* if (!empty($data['EvnPrescr_id'])) {
		  $add_where .= 'and EP.EvnPrescr_id != :EvnPrescr_id';
		  $queryParams['EvnPrescr_id'] = $data['EvnPrescr_id'];
		  } */
		$query = "
			select
				EP.EvnPrescr_id,
				Regime.EvnPrescrRegime_id,
				PrescriptionRegimeType_id,
				Regime.EvnPrescrRegime_setDT,
				EP.PrescriptionStatusType_id,
				case when CAST(Regime.EvnPrescrRegime_setDT as date) between CAST(:beg_date as date) and CAST(:end_date as date) then 'in' else 'out' end as interval
			from
				v_EvnPrescr EP with (nolock)
				inner join v_EvnPrescrRegime Regime with (nolock) on Regime.EvnPrescrRegime_pid = EP.EvnPrescr_id
			where
				EP.EvnPrescr_pid = :EvnPrescr_pid
				and EP.PrescriptionType_id = :PrescriptionType_id
				and EP.PrescriptionStatusType_id != 3
				and CAST(Regime.EvnPrescrRegime_setDT as date) between CAST(:beg_date as datetime)-1 and CAST(:end_date as datetime)+1
				{$add_where}
		";
		//echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);

		if (!is_object($result)) {
			throw new Exception('Ошибка при выполнении запроса к базе данных (Контроль пересечения дат)', 500);
		}
		$response = $result->result('array');

		$Regime = array();
		$cnt = 0;
		$arr = array();
		if (!empty($data['EvnPrescr_id'])) {
			$this->EvnPrescr_id = $data['EvnPrescr_id'];
		}
		foreach ($response as $day) {
			/* if ($day['PrescriptionRegimeType_id'] != $data['PrescriptionRegimeType_id']) {
			  $this->rollbackTransaction();
			  throw new Exception('Указанная продолжительность курса пересекается
			  с продолжительностью курса назначения указанного типа,
			  которое уже имеется в рамках выбранного случая посещения/движения', 400);
			  } */
			//if(empty($data['EvnPrescr_id'])){
			if ($day['interval'] == 'out'){
				if($data['PrescriptionRegimeType_id'] == $day['PrescriptionRegimeType_id']){
					if ($this->EvnPrescr_id == null || $day['EvnPrescr_id'] == $this->EvnPrescr_id){
						$this->EvnPrescr_id = $day['EvnPrescr_id'];
					} else {
						$this->updatePrescrRegime($day['EvnPrescr_id'], $data['pmUser_id']);
						
					}
				}else{
					$cnt++;
					if($cnt==2){
						$this->cutPrescr($day['EvnPrescr_id'],$day['EvnPrescrRegime_setDT'],$data['pmUser_id']);
						if (!empty($data['EvnPrescr_id'])) {
							$this->EvnPrescr_id = $data['EvnPrescr_id'];
						}else{
							$this->EvnPrescr_id=null;
						}
					}
				}
			}
			if (!in_array($day['EvnPrescrRegime_id'], $Regime) && $day['interval'] == 'in') {
				$Regime[] = $day['EvnPrescrRegime_id'];
				$arr[$day['EvnPrescrRegime_id']]['EvnPrescrRegime_id'] = $day['EvnPrescrRegime_id'];
				$arr[$day['EvnPrescrRegime_id']]['EvnPrescrRegime_setDT'] = $day['EvnPrescrRegime_setDT'];
				$arr[$day['EvnPrescrRegime_id']]['EvnPrescr_id'] = $day['EvnPrescr_id'];
			}
		}
		return $arr;
	}
	/**
	 *
	 * @param type $EvnPrescr_id
	 * @param type $date
	 * @param type $pmUser_id 
	 */
	function cutPrescr($EvnPrescr_id,$date,$pmUser_id){
		$data = array();
		$query = "select
						EvnPrescr_pid,
						Lpu_id,
						Server_id,
						PersonEvn_id,
						PrescriptionType_id,
						EvnPrescr_Descr,
						PrescriptionStatusType_id,
						EvnPrescr_IsCito
					from v_EvnPrescr with (nolock) where EvnPrescr_id = :EvnPrescr_id
				";
				
				$queryParams = array('EvnPrescr_id' => $EvnPrescr_id);
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception('Ошибка при запросе данных календаря');
		}
		$idList = array();
		$response = $result->result('array');
		$data=array(
			'EvnPrescr_pid' => $response[0]['EvnPrescr_pid'],
			'Lpu_id' => $response[0]['Lpu_id'],
			'Server_id' => $response[0]['Server_id'],
			'PersonEvn_id' => $response[0]['PersonEvn_id'],
			'PrescriptionType_id' => $response[0]['PrescriptionType_id'],
			'PrescriptionStatusType_id' => $response[0]['PrescriptionStatusType_id'],
			'EvnPrescr_Descr' => $response[0]['EvnPrescr_Descr'],
			'EvnPrescr_IsCito' => $response[0]['EvnPrescr_IsCito'],
			'pmUser_id' => $pmUser_id);
		$this->EvnPrescr_id =$this->_save($data);
		
		$filter = " and CAST(EvnPrescrRegime_setDT as date)>=CAST('".$date->format('Y-m-d')."' as date)";
		$this->updatePrescrRegime($EvnPrescr_id, $pmUser_id,$filter);
	}
	
	/**
	 * Сохранение календаря в EvnPrescrRegime
	 */
	protected function _saveCalendar($data, $dateList) {
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = null;

			exec p_EvnPrescrRegime_ins
				@EvnPrescrRegime_id = @Res output,
				@EvnPrescrRegime_pid = :EvnPrescr_id,
				@PrescriptionType_id = :PrescriptionType_id,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnPrescrRegime_setDT = :EvnPrescrRegime_setDT,
				@PrescriptionRegimeType_id = :PrescriptionRegimeType_id,
				@PrescriptionStatusType_id = :PrescriptionStatusType_id,
				@EvnPrescrRegime_IsCito = 1,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnPrescrRegime_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'EvnPrescr_id' => $data['EvnPrescr_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'PrescriptionType_id' => $this->getPrescriptionTypeId(),
			'PrescriptionRegimeType_id' => $data['PrescriptionRegimeType_id'],
			'PrescriptionStatusType_id' => 1,
			'pmUser_id' => $data['pmUser_id'],
		);
		$idList = array();
		foreach ($dateList as $addDate) {
			$queryParams['EvnPrescrRegime_setDT'] = $addDate;
			// echo getDebugSQL($query, $queryParams); exit();
			$result = $this->db->query($query, $queryParams);
			if (is_object($result)) {
				$response = $result->result('array');
				if (!empty($response[0]['Error_Msg'])) {
					throw new Exception($response[0]['Error_Msg'], 500);
				}
				$idList[] = $response[0]['EvnPrescrRegime_id'];
			} else {
				throw new Exception('Ошибка при запросе к БД при сохранении календаря', 500);
			}
		}
		return $idList;
	}

	/**
	 *
	 * @param type $EvnPrescr_id
	 * @param type $pmUser
	 * @param type $all 
	 */
	public function Clear($EvnPrescr_id, $pmUser, $dateList, $all=false) {


		$query = "
					select
						EvnPrescrRegime_id,
						PrescriptionRegimeType_id,
						convert(varchar(10), EvnPrescrRegime_setDT, 120) as EvnPrescr_setDate
					from v_EvnPrescrRegime with (nolock) where EvnPrescrRegime_pid = :EvnPrescr_id
					order by EvnPrescrRegime_setDT
				";
		$queryParams = array('EvnPrescr_id' => $EvnPrescr_id);
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception('Ошибка при запросе данных календаря');
		}
		$response = $result->result('array');
		if ($all) {
			$query = "
					declare
					@ErrCode int,
					@ErrMessage varchar(4000);

				exec p_EvnPrescrRegime_del
					@EvnPrescrRegime_id = :EvnPrescrRegime_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output";

			$queryParams = array();
			$queryParams['pmUser_id'] = $pmUser;
			$queryParams['EvnPrescrRegime_id'] = $EvnPrescr_id;
			//echo getDebugSQL($query, $queryParams); exit();
			$result = $this->db->query($query, $queryParams);

			if (!is_object($result)) {
				throw new Exception('Не удалось очистить календарь!');
			}
		} else {
			// Иначе заменяются новым только те дни назначения, которые попадают во введенный временной отрезок.
			foreach ($response as $row) {
				if (!in_array($row['EvnPrescr_setDate'], $dateList)) {
					$this->_destroy(array(
						'object' => 'EvnPrescrRegime',
						'id' => $row['EvnPrescrRegime_id'],
						'pmUser_id' => $pmUser,
					));
				}
			}
		}
	}

	/**
	 *
	 * @param type $EvnPrescr_id
	 * @param type $pmUser 
	 */
	public function getCntRegime($EvnPrescr_id, $pmUser) {
		$query = "select
			COUNT(*) as cnt
			from
				v_EvnPrescr EP with (nolock)
				inner join v_EvnPrescrRegime Regime with (nolock) on Regime.EvnPrescrRegime_pid = EP.EvnPrescr_id
		where
				EP.PrescriptionType_id = 1
				and EP.EvnPrescr_id = :EvnPrescr_id
		";
		$queryParams['EvnPrescr_id'] = $EvnPrescr_id;

		$result = $this->db->query($query, $queryParams);

		if (!is_object($result)) {
			throw new Exception('Ошибка при выполнении запроса к базе данных (Контроль пересечения дат)', 500);
		}
		$response = $result->result('array');
		if ($response[0]['cnt'] == 0) {
			$this->Clear($EvnPrescr_id, $pmUser, 0, true);
		}
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
			if (empty($data['PrescriptionRegimeType_id'])) {
				throw new Exception('Не указан Тип режима', 400);
			}
			if (empty($data['PersonEvn_id'])) {
				throw new Exception('Не указан Идентификатор состояния человека', 400);
			}
			if (!isset($data['Server_id'])) {
				throw new Exception('Не указан Идентификатор сервера', 400);
			}
			$dateList = $this->_createDateList($data);
			$action = (empty($data['EvnPrescr_id']) ? 'add' : 'edit');
			if ($action == 'edit' && (isset($data['accessType']) && $data['accessType'] == 'edit')) {

				$this->Clear($data['EvnPrescr_id'], $data['pmUser_id'], $dateList);
			}
			$cross = $this->_hasCrossingDates($data, $dateList);
			if ($this->EvnPrescr_id != NULL && empty($data['EvnPrescr_id'])) {
				$data['EvnPrescr_id'] = $this->EvnPrescr_id;
			}
			if($action == 'add' && !empty($cross)){
				throw new Exception('Ошибка при добавлении нового режима.
				В случае уже добавлен аналогичный режим в том же диапазоне дат.
				Измените параметры или дату начала режима',400);
			}
			// контроль пересечения дат
			if(!empty($data['EvnPrescr_id'])){
				foreach ($cross as $row) {
					$this->_destroy(array(
						'object' => 'EvnPrescrRegime',
						'id' => $row['EvnPrescrRegime_id'],
						'pmUser_id' => $data['pmUser_id'],
					));
					if ($row['EvnPrescr_id'] != $data['EvnPrescr_id']) {
						$this->getCntRegime($row['EvnPrescr_id'], $data['pmUser_id']);
					}
				}
			}

			$data['EvnPrescr_id'] = $this->_save($data);
			$this->EvnPrescr_id = $data['EvnPrescr_id'];
			$idList = $this->_saveCalendar($data, $dateList);
			//$this->updatePrescrRegime($data['EvnPrescr_id'], $data['pmUser_id']);
		} catch (Exception $e) {
			$this->rollbackTransaction();
			return array(array(
					'Error_Msg' => $e->getMessage(),
					'Error_Code' => $e->getCode(),
					));
		}
		$this->commitTransaction();

		$idList = $this->updatePrescrRegime($data['EvnPrescr_id'], $data['pmUser_id']);
		//$this->updatePrescrRegime($data['EvnPrescr_id'], $data['pmUser_id']);
		return array(array(
				'EvnPrescr_id' => $data['EvnPrescr_id'],
				'EvnPrescrRegime_id_list' => $idList,
				'Error_Msg' => null,
				'Error_Code' => null,
				));
	}

	/**
	 * Получение данных для формы редактирования
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array|boolean
	 */
	public function doLoad($data) {
		$query = "
			select
				case when isnull(EP.PrescriptionStatusType_id, 1) = 1 then 'edit' else 'view' end as accessType,
				EP.EvnPrescr_id,
				EP.EvnPrescr_pid,
				EPP.PrescriptionRegimeType_id,
				convert(varchar(10), EPP.EvnPrescrRegime_setDT, 104) as EvnPrescr_setDate,
				EP.EvnPrescr_Descr,
				EP.PersonEvn_id,
				EP.Server_id
			from 
				v_EvnPrescr EP with (nolock)
				inner join v_EvnPrescrRegime EPP with (nolock) on EPP.EvnPrescrRegime_pid = EP.EvnPrescr_id
			where
				EP.EvnPrescr_id = :EvnPrescr_id
			order by EPP.EvnPrescrRegime_setDT
		";

		$queryParams = array(
			'EvnPrescr_id' => $data['EvnPrescr_id']
		);
		//echo getDebugSql($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$tmp_arr = $result->result('array');
			if (count($tmp_arr) > 0) {
				$response = array();
				$dateList = array();
			} else {
				return $tmp_arr;
			}
			foreach ($tmp_arr as $row) {
				$dateList[] = $row['EvnPrescr_setDate'];
			}
			$response[0] = $tmp_arr[0];
			$response[0]['EvnPrescr_dayNum'] = count($dateList);
			return $response;
		} else {
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
				,convert(varchar,Regime.EvnPrescrRegime_setDT,104) as EvnPrescr_setDate
				,Regime.EvnPrescrRegime_IsExec as EvnPrescr_IsExec
				,Regime.PrescriptionStatusType_id
				,EP.PrescriptionType_id
				,EP.PrescriptionType_id as PrescriptionType_Code
				,isnull(EP.EvnPrescr_IsCito,1) as EvnPrescr_IsCito
				,Regime.EvnPrescrRegime_Descr as EvnPrescr_Descr,
				EP.EvnPrescr_Descr as EvnPrescr_MainDescr,
				Regime.EvnPrescrRegime_id,
				Regime.EvnPrescrRegime_Count as EvnPrescr_dayNum,
				ISNULL(PRT.PrescriptionRegimeType_id, 0) as PrescriptionRegimeType_id,--тип режима
				ISNULL(PRT.PrescriptionRegimeType_Code, 0) as PrescriptionRegimeType_Code,
				ISNULL(PRT.PrescriptionRegimeType_Name, '') as PrescriptionRegimeType_Name
			from v_EvnPrescr EP with (nolock)
				inner join v_EvnPrescrRegime Regime with (nolock) on Regime.EvnPrescrRegime_pid = EP.EvnPrescr_id
				left join PrescriptionRegimeType PRT with (nolock) on PRT.PrescriptionRegimeType_id = Regime.PrescriptionRegimeType_id
				{$addJoin}
			where
				EP.EvnPrescr_pid  = :EvnPrescr_pid
				and EP.PrescriptionType_id = 1
				and Regime.PrescriptionStatusType_id != 3
			order by
				EP.EvnPrescr_id,
				Regime.EvnPrescrRegime_setDT
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
			foreach ($tmp_arr as $i => $row) {
				if ($last_ep != $row['EvnPrescr_id']) {
					//это первая итерация с другим назначением
					$first_index = $i;
					$last_ep = $row['EvnPrescr_id'];
					$is_exe = false;
					$is_sign = false;
				}
				if ($is_exe == false) $is_exe = ($row['EvnPrescr_IsExec'] == 2);
				if ($is_sign == false) $is_sign = ($row['PrescriptionStatusType_id'] == 2);
				if (empty($tmp_arr[$i+1]) || $last_ep != $tmp_arr[$i+1]['EvnPrescr_id']) {
					if ($is_exe) $row['EvnPrescr_IsExec'] = 2;
					if ($is_sign) $row['PrescriptionStatusType_id'] = 2;

					if (!empty($section) && $section === 'api') {
						$row['EvnPrescr_setDate'] = $tmp_arr[$first_index]['EvnPrescr_setDate'];
					} else {
						$row['EvnPrescr_setDate'] = $tmp_arr[$first_index]['EvnPrescr_setDate'].'&nbsp;—&nbsp;'.$row['EvnPrescr_setDate'];
					}

					$row[$section . '_id'] = $row['EvnPrescr_id'].'-'.$row['EvnPrescrRegime_id'];

					if ($section === "api" && empty($row['EvnPrescr_Descr']) && !empty($row['EvnPrescr_MainDescr'])) {
						$row['EvnPrescr_Descr'] = $row['EvnPrescr_MainDescr'];
						unset($row['EvnPrescr_MainDescr']);
					}

					$response[] = $row;
				}
			}
			return $response;
		} else {
			return false;
		}
	}
}
