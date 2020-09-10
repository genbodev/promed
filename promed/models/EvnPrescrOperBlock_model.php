<?php	defined('BASEPATH') or die ('No direct script access allowed');
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
 * Модель назначения "Операционный блок"
 *
 * Назначения с типом "Операционный блок" хранятся в таблицах EvnPrescrOperBlock, EvnPrescrOperUsluga
 * В назначении должна быть указана только одна услуга.
 * Если услуга имеет состав (UslugaComplexComposition), то могут выбраны все или лишь некоторые простые услуги из её состава.
 * Для каждой выбранной простой услуги из состава создается запись в EvnPrescrOperUsluga.
 *
 * @package		EvnPrescr
 * @author		Александр Пермяков
 * @property EvnDirectionAll_model $EvnDirectionAll_model
 */
class EvnPrescrOperBlock_model extends EvnPrescrAbstract_model
{
	/**
	 * Конструктор
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Определение идентификатора типа назначения
	 * @return int
	 */
	public function getPrescriptionTypeId() {
		return 7;
	}

	/**
	 * Определение имени таблицы с данными назначения
	 * @return string
	 */
	public function getTableName() {
		return 'EvnPrescrOperBlock';
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
					array('field' => 'parentEvnClass_SysNick','label' => 'Системное имя род.события','rules' => '', 'default' => 'EvnSection','type' => 'string'),
					array('field' => 'signature','label' => 'Признак для подписания','rules' => '','type' => 'int'),
					array('field' => 'EvnPrescrOperBlock_id','label' => 'Идентификатор назначения','rules' => '','type' => 'id'),
					array('field' => 'EvnPrescrOperBlock_pid','label' => 'Идентификатор род.события','rules' => 'required','type' => 'id'),
					array('field' => 'UslugaComplex_id','label' => 'Услуга','rules' => 'required','type' => 'id'),
					array('field' => 'EvnPrescrOperBlock_uslugaList','label' => 'Список услуг, выбранных из состава комплексной услуги','rules' => '','type' => 'string'),
					array('field' => 'EvnPrescrOperBlock_setDate','label' => 'Плановая дата','rules' => '','type' => 'date'),
					array('field' => 'EvnPrescrOperBlock_IsCito','label' => 'Cito','rules' => '','type' => 'string'),
					array('field' => 'EvnPrescrOperBlock_Descr','label' => 'Комментарий','rules' => 'trim','type' => 'string'),
					array('field' => 'EvnDirection_id','label' => 'Идентификатор','rules' => '','type' => 'id'),
					array('field' => 'PersonEvn_id','label' => 'Идентификатор','rules' => 'required','type' => 'id'),
					array('field' => 'Server_id','label' => 'Идентификатор сервера','rules' => 'required','type' => 'int'),
					array('field' => 'EvnPrescrLimitData','label' => 'Ограничения','rules' => '','type' => 'string'),
					array('field' => 'EvnUslugaOrder_id','label' => 'Идентификатор заказа','rules' => '','type' => 'id'),
					array('field' => 'EvnUslugaOrder_UslugaChecked','label' => 'Измененный состав для обновления заказа','rules' => '','type' => 'string'),
				);
				break;
			case 'doLoad':
				$rules[] = array(
					'field' => 'EvnPrescrOperBlock_id',
					'label' => 'Идентификатор назначения',
					'rules' => 'required',
					'type' =>  'id'
				);
				break;
		}
		return $rules;
	}

	/**
	 * Сохранение назначения
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array|boolean
	 */
	public function doSave($data = array(), $isAllowTransaction = true) {
		if(empty($data['EvnPrescrOperBlock_id']))
		{
			$action = 'ins';
			$allow_sign = true;
			$data['EvnPrescrOperBlock_id'] = NULL;
			$data['PrescriptionStatusType_id'] = 1;
			$EvnPrescr_id = NULL;
		}
		else
		{
			$action = 'upd';
			$EvnPrescr_id = $data['EvnPrescrOperBlock_id'];
			$o_data = $this->getAllData($data['EvnPrescrOperBlock_id']);
			if(!empty($o_data['Error_Msg']))
			{
				return array($o_data);
			}
			$allow_sign = (isset($o_data['PrescriptionStatusType_id']) && $o_data['PrescriptionStatusType_id'] == 1);
			foreach($o_data as $k => $v) {
				if(!isset($data[$k]))
				{
					$data[$k] = $v;
				}
			}
		}
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :EvnPrescrOperBlock_id;

			exec p_EvnPrescrOperBlock_" . $action . "
				@EvnPrescrOperBlock_id = @Res output,
				@EvnPrescrOperBlock_pid = :EvnPrescrOperBlock_pid,
				@PrescriptionType_id = :PrescriptionType_id,
				@PrescriptionStatusType_id = :PrescriptionStatusType_id,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@UslugaComplex_id = :UslugaComplex_id,
				@EvnPrescrOperBlock_setDT = :EvnPrescrOperBlock_setDate,
				@EvnPrescrOperBlock_IsCito = :EvnPrescrOperBlock_IsCito,
				@EvnPrescrOperBlock_Descr = :EvnPrescrOperBlock_Descr,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnPrescrOperBlock_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$data['EvnPrescrOperBlock_IsCito'] = (empty($data['EvnPrescrOperBlock_IsCito']) || $data['EvnPrescrOperBlock_IsCito'] != 'on')? 1 : 2;
		$data['PrescriptionType_id'] = $this->getPrescriptionTypeId();

		//echo getDebugSQL($query, $data); exit();

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$trans_result = $result->result('array');
			if(!empty($trans_result) && !empty($trans_result[0]) && !empty($trans_result[0]['EvnPrescrOperBlock_id']) && empty($trans_result[0]['Error_Msg']))
			{
				$trans_good = true;
				$EvnPrescr_id = $trans_result[0]['EvnPrescrOperBlock_id'];
			}
			else
			{
				$trans_good = false;
			}
		}
		else {
			$trans_good = false;
			$trans_result = false;
		}

		$uslugalist = array();
		if($trans_good === true && !empty($data['EvnPrescrLimitData']))
		{
			$data['EvnPrescrLimitData'] = toUtf($data['EvnPrescrLimitData']);
			$limitdata = json_decode($data['EvnPrescrLimitData'], true);
			foreach($limitdata as $limit) {
				$limit['EvnPrescrLimit_id'] = null;
				$limit['LimitType_IsCatalog'] = 1;
				$limit['EvnPrescr_id'] = $EvnPrescr_id;
				$limit['pmUser_id'] = $data['pmUser_id'];

				if (!empty($limit['LimitType_id'])) {
					// 1. ищем запись для соответвующего LimitType_id и RefValues_id
					$query = "
						select top 1
							epl.EvnPrescrLimit_id,
							ISNULL(lt.LimitType_IsCatalog, 1) as LimitType_IsCatalog
						from
							v_LimitType (nolock) lt
							left join v_EvnPrescrLimit (nolock) epl on epl.LimitType_id = lt.LimitType_id and epl.EvnPrescr_id = :EvnPrescr_id
						where
							lt.LimitType_id = :LimitType_id
					";

					$result = $this->db->query($query, $limit);
					if ( is_object($result) ) {
						$resp = $result->result('array');
						if (!empty($resp[0]['EvnPrescrLimit_id'])) {
							$limit['EvnPrescrLimit_id'] = $resp[0]['EvnPrescrLimit_id'];
						}
						if (!empty($resp[0]['LimitType_IsCatalog'])) {
							$limit['LimitType_IsCatalog'] = $resp[0]['LimitType_IsCatalog'];
						}
					}

					// 2. сохраняем
					$procedure = 'p_EvnPrescrLimit_ins';
					if ( !empty($limit['EvnPrescrLimit_id']) ) {
						$procedure = 'p_EvnPrescrLimit_upd';
					}

					if (empty($limit['EvnPrescrLimit_ValuesNum'])) { $limit['EvnPrescrLimit_ValuesNum'] = null;	}
					if (empty($limit['EvnPrescrLimit_Values'])) { $limit['EvnPrescrLimit_Values'] = null;	}

					$query = "
						declare
							@EvnPrescrLimit_id bigint,
							@ErrCode int,
							@ErrMessage varchar(4000);
						set @EvnPrescrLimit_id = :EvnPrescrLimit_id;
						exec " . $procedure . "
							@EvnPrescrLimit_id = @EvnPrescrLimit_id output,
							@LimitType_id = :LimitType_id,
							@EvnPrescrLimit_Values = :EvnPrescrLimit_Values,
							@EvnPrescr_id = :EvnPrescr_id,
							@EvnPrescrLimit_ValuesNum = :EvnPrescrLimit_ValuesNum,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @EvnPrescrLimit_id as EvnPrescrLimit_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					$result = $this->db->query($query, $limit);

					if ( is_object($result) ) {
						$res = $result->result('array');
						if(!empty($res) && !empty($res[0]) && empty($res[0]['Error_Msg']))
						{
							$trans_good = true;
						}
						else
						{
							$trans_good = false;
							$trans_result[0]['Error_Msg'] = $res[0]['Error_Msg'];
							break;//выходим из цикла
						}
					}
					else {
						$trans_good = false;
						$trans_result[0]['Error_Msg'] = 'Ошибка запроса при ';
						break;//выходим из цикла
					}
				}
			}
		}

		if($trans_good === true && !empty($data['EvnPrescrOperBlock_uslugaList']))
		{
			$uslugalist = explode(',', $data['EvnPrescrOperBlock_uslugaList']);
			if(empty($uslugalist) || !is_numeric ($uslugalist[0]))
			{
				$trans_result[0]['Error_Msg'] = 'Ошибка формата списка услуг';
				$trans_good = false;
			}
			else
			{
				$res = $this->clearEvnPrescrOperBlockUsluga(array('EvnPrescrOperBlock_id' => $EvnPrescr_id));
				if(empty($res))
				{
					$trans_result[0]['Error_Msg'] = 'Ошибка запроса при списка выбранных услуг';
					$trans_good = false;
				}
				if(!empty($res) && !empty($res[0]) && !empty($res[0]['Error_Msg']))
				{
					$trans_result[0]['Error_Msg'] = $res[0]['Error_Msg'];
					$trans_good = false;
				}
			}
		}

		if($trans_good === true && !empty($uslugalist))
		{
			foreach($uslugalist as $d)
			{
				$res = $this->saveEvnPrescrOperBlockUsluga(array(
					'UslugaComplex_id' => $d
				,'EvnPrescrOperBlock_id' => $EvnPrescr_id
				,'pmUser_id' => $data['pmUser_id']
				));
				if(empty($res))
				{
					$trans_result[0]['Error_Msg'] = 'Ошибка запроса при сохранении услуги';
					$trans_good = false;
					break;
				}
				if(!empty($res) && !empty($res[0]) && !empty($res[0]['Error_Msg']))
				{
					$trans_result[0]['Error_Msg'] = $res[0]['Error_Msg'];
					$trans_good = false;
					break;
				}
			}
		}

		if($trans_good === true && !empty($data['EvnUslugaOrder_id']) && !empty($data['EvnUslugaOrder_UslugaChecked']))
		{
			$uslugalist = explode(',', $data['EvnUslugaOrder_UslugaChecked']);
			if(empty($uslugalist) || !is_numeric ($uslugalist[0]))
			{
				$trans_result[0]['Error_Msg'] = 'Ошибка формата списка заказанных услуг';
				$trans_good = false;
			}
			else
			{
				$res = $this->_updateEvnUslugaOrder(array(
					'EvnUsluga_Result' => json_encode($uslugalist),
					'Evn_id' => $data['EvnUslugaOrder_id'],
					'pmUser_id' => $data['pmUser_id'],
				));
				if(!empty($res[0]['Error_Msg']))
				{
					$trans_result[0]['Error_Msg'] = $res[0]['Error_Msg'];
					$trans_good = false;
				}
			}
		}

		if ($action === 'upd' && $trans_good === true
			&& !empty($data['EvnDirection_id'])
		) {
			// обновляем пометку "Cito" заявки в АРМе лаборанта
			$this->load->model('EvnDirectionAll_model');
			$this->EvnDirectionAll_model->setScenario(swModel::SCENARIO_SET_ATTRIBUTE);
			$this->EvnDirectionAll_model->setParams(array(
				'session' => $data['session'],
			));
			$response = $this->EvnDirectionAll_model->updateIsCito($data['EvnDirection_id'], $data['EvnPrescrOperBlock_IsCito']);
			if ( !empty($response['Error_Msg']) ) {
				$trans_result[0]['Error_Msg'] = $response['Error_Msg'];
				$trans_good = false;
				//throw new Exception($response['Error_Msg']);
			}
		}

		if($trans_good === true && $allow_sign && !empty($data['signature']))
		{
			/*$res = $this->signEvnPrescr(array(
				'PrescriptionType_id' => 14
				,'parentEvnClass_SysNick' => $data['parentEvnClass_SysNick']
				,'EvnPrescr_pid' => $data['EvnPrescrOperBlock_pid']
				,'EvnPrescr_id' => $EvnPrescr_id
				,'pmUser_id' => $data['pmUser_id']
			));
			if(empty($res))
			{
				$trans_result[0]['Error_Msg'] = 'Ошибка запроса при подписании назначения';
				$trans_good = false;
			}
			if(!empty($res) && !empty($res[0]) && !empty($res[0]['Error_Msg']))
			{
				$trans_result[0]['Error_Msg'] = $res[0]['Error_Msg'];
				$trans_good = false;
			}*/
		}

		return $trans_result;
	}

	/**
	 *  метод обновления заказа
	 */
	function _updateEvnUslugaOrder($data) {
		$query = "
			declare
				@pmUser_id bigint = :pmUser_id,
				@Evn_id bigint = :Evn_id,
				@EvnUsluga_Result varchar(4000) = :EvnUsluga_Result,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set nocount on;

			begin try

			begin tran

			update Evn with (ROWLOCK)
			set
			Evn_updDT = dbo.tzGetDate(),
			pmUser_updID = @pmUser_id
			where Evn_id = @Evn_id

			update EvnUsluga with (ROWLOCK)
			set
			EvnUsluga_Result = @EvnUsluga_Result
			where EvnUsluga_id = @Evn_id

			commit tran

			end try

			begin catch
				set @ErrCode = error_number()
				set @ErrMessage = error_message()
				if @@trancount>0
					rollback
			end catch

			set nocount off;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		// echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg'=>'Ошибка обновления заказа!'));
		}
	}

	/**
	 *  метод очистки списка услуг
	 */
	function clearEvnPrescrOperBlockUsluga($data) {
		return $this->clearEvnPrescrTable(array(
			'object'=>'EvnPrescrOperUsluga'
		,'fk_pid'=>'EvnPrescrOper_id'
		,'pid'=>$data['EvnPrescrOperBlock_id']
		));
	}

	/**
	 * Сохранение назнач
	 */
	function saveEvnPrescrOperBlockUsluga($data) {
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :EvnPrescrOperUsluga_id;

			exec p_EvnPrescrOperUsluga_" . (!empty($data['EvnPrescrOperUsluga_id']) ? "upd" : "ins") . "
				@EvnPrescrOperUsluga_id = @Res output,
				@EvnPrescrOper_id = :EvnPrescrOper_id,
				@UslugaComplex_id = :UslugaComplex_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnPrescrOperUsluga_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'EvnPrescrOperUsluga_id' => (empty($data['EvnPrescrOperUsluga_id'])? NULL : $data['EvnPrescrOperUsluga_id'] ),
			'EvnPrescrOper_id' => $data['EvnPrescrOperBlock_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
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
				then 'edit' else 'view' end as accessType,
				EP.EvnPrescrOperBlock_id,
				EP.EvnPrescrOperBlock_pid,
				ED.EvnDirection_id,
				EP.UslugaComplex_id,
				UCC.UslugaComplex_id as UslugaComplex_sid,
				convert(varchar(10), EP.EvnPrescrOperBlock_setDT, 104) as EvnPrescrOperBlock_setDate,
				case when isnull(EP.EvnPrescrOperBlock_IsCito,1) = 1 then 'off' else 'on' end as EvnPrescrOperBlock_IsCito,
				EP.EvnPrescrOperBlock_Descr,
				EP.PersonEvn_id,
				EP.Person_id,
				EP.Server_id
			from 
				v_EvnPrescrOperBlock EP with (nolock)
				-- состав услуги, если услуга комплексная
				left join v_EvnPrescrOperUsluga UCC with (nolock) on UCC.EvnPrescrOper_id = EP.EvnPrescrOperBlock_id
				outer apply (
					Select top 1 ED.EvnDirection_id
					from v_EvnPrescrDirection epd with (nolock)
					inner join v_EvnDirection_all ED with (nolock) on epd.EvnDirection_id = ED.EvnDirection_id
						and ED.EvnDirection_failDT is null
						and ISNULL(ED.EvnStatus_id, 16) not in (12,13)
					where epd.EvnPrescr_id = EP.EvnPrescrOperBlock_id
					order by epd.EvnPrescrDirection_insDT desc
				) ED
			where
				EP.EvnPrescrOperBlock_id = :EvnPrescrOperBlock_id
		";

		$queryParams = array(
			'EvnPrescrOperBlock_id' => $data['EvnPrescrOperBlock_id']
		);
		//echo getDebugSql($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$tmp_arr = $result->result('array');
			if(count($tmp_arr) > 0)
			{
				$response = array();
				$uslugalist = array();
			}
			else
			{
				return $tmp_arr;
			}
			foreach($tmp_arr as $row) {
				if(!empty($row['UslugaComplex_sid']))
				{
					$uslugalist[] = $row['UslugaComplex_sid'];
				}
			}
			$response[0] = $tmp_arr[0];
			$response[0]['EvnPrescrOperBlock_uslugaList'] = implode(',',$uslugalist);
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
		$filter = '';
		$testFilter = getAccessRightsTestFilter('UC.UslugaComplex_id');
		if (!empty($testFilter)){
			$filter .= "
				and (
					EP.MedPersonal_sid = :MedPersonal_id
					or exists (
						select top 1 Evn_id from v_Evn with(nolock) where Evn_id = :EvnPrescr_pid and EvnClass_sysNick = 'EvnSection' and Evn_setDT <= EP.EvnPrescr_setDT and (Evn_disDT is null or Evn_disDT >= EP.EvnPrescr_setDT)
					)
					or $testFilter
				)";
		}

		if ($sysnick) {
			$accessType = "case when {$sysnick}.Lpu_id = :Lpu_id
			AND isnull({$sysnick}.{$sysnick}_IsSigned,1) = 1
			AND ISNULL(EP.EvnPrescr_IsExec, 1) = 1
			then 'edit' else 'view' end as accessType";
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
				,convert(varchar,EP.EvnPrescr_setDT,104) as EvnPrescr_setDate
				,null as EvnPrescr_setTime
				,isnull(EP.EvnPrescr_IsExec,1) as EvnPrescr_IsExec
				,case when EU.EvnUsluga_id is null then 1 else 2 end as EvnPrescr_IsHasEvn
				-- Если в качестве даты-времени выполнения брать EU.EvnUsluga_setDT, то дата может не отобразиться, если при выполнении не была создана услуга или услуга не связана с назначением
				-- Поэтому решил использовать EP.EvnPrescr_updDT, т.к. после выполнения эта дата не меняется
				,case when 2 = EP.EvnPrescr_IsExec
				then convert(varchar,EP.EvnPrescr_updDT,104)+' '+convert(varchar,EP.EvnPrescr_updDT,108) else null
				end as EvnPrescr_execDT
				,EP.PrescriptionStatusType_id
				,EP.PrescriptionType_id
				,EP.PrescriptionType_id as PrescriptionType_Code
				,isnull(EP.EvnPrescr_IsCito,1) as EvnPrescr_IsCito
				,isnull(EP.EvnPrescr_Descr,'') as EvnPrescr_Descr
				
				,case when ED.EvnDirection_id is null OR ISNULL(ED.EvnStatus_id, 16) in (12,13) then 1 else 2 end as EvnPrescr_IsDir
				,case when ED.EvnStatus_id is null and (ED.DirFailType_id > 0 OR EQ.QueueFailCause_id > 0 ) then 12 else ED.EvnStatus_id end as EvnStatus_id
				,case when EvnStatus.EvnStatus_Name is null and (ED.DirFailType_id > 0 OR EQ.QueueFailCause_id > 0 ) then 'Отменено' else EvnStatus.EvnStatus_Name end as EvnStatus_Name
				,coalesce(EvnStatusCause.EvnStatusCause_Name, DFT.DirFailType_Name, QFC.QueueFailCause_Name) as EvnStatusCause_Name
				,convert(varchar(10), coalesce(ED.EvnDirection_statusDate, ED.EvnDirection_failDT, EQ.EvnQueue_failDT), 104) as EvnDirection_statusDate
				,ESH.EvnStatusCause_id
				,ED.DirFailType_id
				,EQ.QueueFailCause_id 
				,ESH.EvnStatusHistory_Cause
				
				,ED.EvnDirection_id
				,EQ.EvnQueue_id
				,case when ED.EvnDirection_Num is null /*or isnull(ED.EvnDirection_IsAuto,1) = 2*/ then '' else cast(ED.EvnDirection_Num as varchar) end as EvnDirection_Num
				,case
					when TTR.TimetableResource_id is not null then isnull(MS.MedService_Name,'') +' / '+ isnull(Lpu.Lpu_Nick,'')
					when EQ.EvnQueue_id is not null then
						case
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is null
							then isnull(MS.MedService_Name,'')
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is not null
							then isnull(MS.MedService_Name,'') +' / '+ isnull(LU.LpuUnit_Name,'')
							when MS.MedService_id is not null and  MS.LpuSection_id is not null and MS.LpuUnit_id is not null
							then isnull(MS.MedService_Name,'') +' / '+ isnull(LSPD.LpuSectionProfile_Name,'') +' / '+ isnull(LU.LpuUnit_Name,'')
							else isnull(LSPD.LpuSectionProfile_Name,'') +' / '+ isnull(LU.LpuUnit_Name,'')
						end +' / '+ isnull(Lpu.Lpu_Nick,'')
				else '' end as RecTo
				,case
					when TTR.TimetableResource_id is not null then isnull(convert(varchar(10), TTR.TimetableResource_begTime, 104),'')+' '+isnull(convert(varchar(5), TTR.TimetableResource_begTime, 108),'')
					when EQ.EvnQueue_id is not null then 'В очереди с '+ isnull(convert(varchar(10), EQ.EvnQueue_setDate, 104),'')
				else '' end as RecDate
				,case
					when TTR.TimetableResource_id is not null then 'TimetableResource'
					when EQ.EvnQueue_id is not null then 'EvnQueue'
				else '' end as timetable
				,case
					when TTR.TimetableResource_id is not null  then TTR.TimetableResource_id
					--when EU.EvnUsluga_id is not null then EU.EvnUsluga_id
					when EQ.EvnQueue_id is not null then EQ.EvnQueue_id
				else '' end as timetable_id
				,EP.EvnPrescr_pid as timetable_pid
				,LU.LpuUnitType_SysNick
				,DT.DirType_Code
				,UC.UslugaComplex_id
				,UC.UslugaComplex_2011id
				,UC.UslugaComplex_Code
				,ISNULL(UCMS.UslugaComplex_Name, UC.UslugaComplex_Name) as UslugaComplex_Name
				,null as TableUsluga_id
			from v_EvnPrescr EP with (nolock)
				inner join EvnPrescrOperBlock EPOB with (nolock) on EPOB.EvnPrescrOperBlock_id = EP.EvnPrescr_id
				left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EPOB.UslugaComplex_id
				outer apply (
					Select top 1 ED.EvnDirection_id
						,isnull(ED.Lpu_sid, ED.Lpu_id) Lpu_id
						,ED.EvnQueue_id
						,ED.EvnDirection_Num
						,ED.EvnDirection_IsAuto
						,ED.LpuSection_did
						,ED.LpuUnit_did
						,ED.Lpu_did
						,ED.MedService_id
						,ED.LpuSectionProfile_id
						,ED.DirType_id
						,ED.EvnStatus_id
						,ED.EvnDirection_statusDate
						,ED.DirFailType_id
						,ED.EvnDirection_failDT
					from v_EvnPrescrDirection epd with (nolock)
					inner join v_EvnDirection_all ED with (nolock) on epd.EvnDirection_id = ED.EvnDirection_id
					where epd.EvnPrescr_id = EP.EvnPrescr_id
					order by 
						case when ISNULL(ED.EvnStatus_id, 16) in (12,13) then 2 else 1 end /* первым неотмененное/неотклоненное направление */
						,epd.EvnPrescrDirection_insDT desc
				) ED
				-- службы и параклиника
				outer apply (
					Select top 1 TimetableResource_id, TimetableResource_begTime from v_TimetableResource_lite TTR with (nolock) where TTR.EvnDirection_id = ED.EvnDirection_id
				) TTR
				-- очередь
				outer apply (
					Select top 1 EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id 
					from v_EvnQueue EQ with (nolock)
					where EQ.EvnDirection_id = ED.EvnDirection_id
					and EQ.EvnQueue_recDT is null
					-- это костыль для того, чтобы направления у которых есть связь по EQ.EvnQueue_id = ED.EvnQueue_id, но нет реальной записи в TimetableMedService можно было отменить
					union
					Select top 1 EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id 
					from v_EvnQueue EQ with (nolock)
					where (EQ.EvnQueue_id = ED.EvnQueue_id)
					and (EQ.EvnQueue_recDT is null or TTR.TimetableResource_id is null)
					and EQ.EvnQueue_failDT is null
				) EQ
				outer apply(
					select top 1 ESH.EvnStatus_id, ESH.EvnStatusCause_id, ESH.pmUser_insID, ESH.EvnStatusHistory_Cause
					from EvnStatusHistory ESH with(nolock)
					where ESH.Evn_id = ED.EvnDirection_id
						and ESH.EvnStatus_id = ED.EvnStatus_id
					order by ESH.EvnStatusHistory_begDate desc
				) ESH
				left join EvnStatus with(nolock) on EvnStatus.EvnStatus_id = ESH.EvnStatus_id
				left join EvnStatusCause with(nolock) on EvnStatusCause.EvnStatusCause_id = ESH.EvnStatusCause_id
				left join v_DirFailType DFT with(nolock) on DFT.DirFailType_id = ED.DirFailType_id
				left join v_QueueFailCause QFC with(nolock) on QFC.QueueFailCause_id = EQ.QueueFailCause_id
				-- сама служба (todo: надо ли оно)
				left join v_MedService MS with (nolock) on MS.MedService_id = ED.MedService_id -- ED.MedService_did должно быть
				-- услуга на службе
				left join v_UslugaComplexMedService UCMS (nolock) on UCMS.MedService_id = MS.MedService_id and UCMS.UslugaComplex_id = UC.UslugaComplex_id and UCMS.UslugaComplexMedService_pid is null
				-- отделение для полки и стаца и для очереди
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = coalesce(ED.LpuSection_did, MS.LpuSection_id)
				-- заказанная услуга для параклиники
				--left join v_EvnUslugaPar EUP with (NOLOCK) on EUP.EvnDirection_id = ED.EvnDirection_id
				-- подразделение для очереди и служб
				left join v_LpuUnit LU with (nolock) on coalesce(ED.LpuUnit_did,EQ.LpuUnit_did,MS.LpuUnit_id) = LU.LpuUnit_id -- todo: в ED.LpuUnit_did пусто, чего не должно быть
				-- профиль для очереди
				left join v_LpuSectionProfile LSPD with (nolock) on coalesce(ED.LpuSectionProfile_id,EQ.LpuSectionProfile_did,LS.LpuSectionProfile_id) = LSPD.LpuSectionProfile_id -- todo: тут на примере оказалось что почему то ED.LpuSectionProfile_id != EQ.LpuSectionProfile_did, чего не должно быть
				-- тип направления
				left join v_DirType DT with (NOLOCK) on ED.DirType_id = DT.DirType_id
				-- ЛПУ
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = coalesce(ED.Lpu_did, LS.Lpu_id, MS.Lpu_id, EQ.Lpu_id)
				outer apply (
					select top 1 EvnUsluga_id, EvnUsluga_setDT from v_EvnUsluga with (nolock)
					where EP.EvnPrescr_IsExec = 2 and UC.UslugaComplex_id is not null and EvnPrescr_id = EP.EvnPrescr_id
				) EU
				{$addJoin}
			where
				EP.EvnPrescr_pid  = :EvnPrescr_pid
				and EP.PrescriptionType_id = 7
				and EP.PrescriptionStatusType_id != 3
				{$filter}
			order by
				EP.EvnPrescr_id,
				EP.EvnPrescr_setDT
		";
		/*or exist (
			select top 1
				UslugaComplex_id
			from
				v_UslugaComplexComposition
			where
				UslugaComplex_pid = UC.UslugaComplex_id
				and UslugaComplex_id not in ()
		)*/
		$queryParams = array(
			'EvnPrescr_pid' => $evn_pid,
			'Lpu_id' => $sessionParams['lpu_id'],
			'MedPersonal_id' => $sessionParams['medpersonal_id'],
		);
		//echo getDebugSql($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$tmp_arr = $result->result('array');
			$response = array();
			foreach ($tmp_arr as $i => $row) {
				$row['UslugaId_List'] = $row['UslugaComplex_id'];
				if ($this->options['prescription']['enable_show_service_code']) {
					$row['Usluga_List'] = $row['UslugaComplex_Code'].' '.$row['UslugaComplex_Name'];
				} else {
					$row['Usluga_List'] = $row['UslugaComplex_Name'];
				}
				$row[$section . '_id'] = $row['EvnPrescr_id'].'-0';
				$response[] = $row;
			}
			//загружаем документы
			$tmp_arr = array();
			$evnPrescrIdList = array();
			foreach ($response as $key => $row) {
				if (isset($row['EvnPrescr_IsExec'])
					&& 2 == $row['EvnPrescr_IsExec']
					&& isset($row['EvnPrescr_IsHasEvn'])
					&& 2 == $row['EvnPrescr_IsHasEvn']
				) {
					$response[$key]['EvnXml_id'] = null;
					$id = $row['EvnPrescr_id'];
					$evnPrescrIdList[] = $id;
					$tmp_arr[$id] = $key;
				}
			}
			if (count($evnPrescrIdList) > 0) {
				$evnPrescrIdList = implode(',',$evnPrescrIdList);
				$query = "
				WITH EvnPrescrEvnXml
				as (
					select doc.EvnXml_id, EU.EvnPrescr_id
					from  v_EvnUsluga EU (nolock)
					inner join v_EvnXml doc (nolock) on doc.Evn_id = EU.EvnUsluga_id
					where EU.EvnPrescr_id in ({$evnPrescrIdList})
				)

				select EvnXml_id, EvnPrescr_id from EvnPrescrEvnXml with(nolock)
				order by EvnPrescr_id";
				$result = $this->db->query($query);
				if ( is_object($result) ) {
					$evnPrescrIdList = $result->result('array');
					foreach ($evnPrescrIdList as $row) {
						$id = $row['EvnPrescr_id'];
						if (isset($tmp_arr[$id])) {
							$key = $tmp_arr[$id];
							if (isset($response[$key])) {
								$response[$key]['EvnXml_id'] = $row['EvnXml_id'];
							}
						}
					}
				}
			}
			return $response;
		} else {
			return false;
		}
	}
}
