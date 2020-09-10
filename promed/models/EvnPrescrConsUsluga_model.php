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
 * Модель назначения "Консультационная услуга"
 *
 * Назначения с типом "Консультационная услуга" хранятся в таблицах Evn, EvnPrescr, EvnPrescrConsUsluga
 * В назначении должна быть указана только одна услуга.
 *
 * @package		EvnPrescr
 * @author		Александр Пермяков
 *
 */
class EvnPrescrConsUsluga_model extends EvnPrescrAbstract_model
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
		return 13;
	}

	/**
	 * Определение имени таблицы с данными назначения
	 * @return string
	 */
	public function getTableName() {
		return 'EvnPrescrConsUsluga';
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
					array('field' => 'parentEvnClass_SysNick','label' => 'Системное имя учетного документа','rules' => '', 'default' => 'EvnSection','type' => 'string'),
					array('field' => 'signature','label' => 'Признак для подписания','rules' => '','type' => 'int'),
					array('field' => 'EvnPrescrConsUsluga_id','label' => 'Идентификатор назначения','rules' => '','type' => 'id'),
					array('field' => 'EvnPrescrConsUsluga_pid','label' => 'Идентификатор учетного документа','rules' => 'required','type' => 'id'),
					array('field' => 'UslugaComplex_id','label' => 'Услуга','rules' => 'required','type' => 'id'),
					array('field' => 'EvnPrescrConsUsluga_setDate','label' => 'Плановая дата','rules' => 'required','type' => 'date'),
					array('field' => 'EvnPrescrConsUsluga_IsCito','label' => 'Cito','rules' => '','type' => 'string'),
					array('field' => 'EvnPrescrConsUsluga_Descr','label' => 'Комментарий','rules' => 'trim','type' => 'string'),
					array('field' => 'DopDispInfoConsent_id','label' => 'Идентификатор согласия карты диспансеризации','rules' => '','type' => 'id'),
					array('field' => 'PersonEvn_id','label' => 'Идентификатор человека','rules' => 'required','type' => 'id'),
					array('field' => 'Server_id','label' => 'Идентификатор сервера','rules' => 'required','type' => 'int'),
					array('field' => 'MedService_id','label' => 'Служба','rules' => '','type' => 'id'),
				);
				break;
			case 'doLoad':
				$rules[] = array(
					'field' => 'EvnPrescrConsUsluga_id',
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
		if(empty($data['EvnPrescrConsUsluga_id']))
		{
			$action = 'ins';
			$allow_sign = true;
			$data['EvnPrescrConsUsluga_id'] = NULL;
			$data['PrescriptionStatusType_id'] = 1;
		}
		else
		{
			$action = 'upd';
			try {
				$o_data = $this->getAllData($data['EvnPrescrConsUsluga_id']);
			} catch (Exception $e) {
				return array(array('Error_Msg' => $e->getMessage()));
			}
			$allow_sign = (isset($o_data['PrescriptionStatusType_id']) && $o_data['PrescriptionStatusType_id'] == 1);
			foreach($o_data as $k => $v) {
				if(!array_key_exists($k, $data)) {
					$data[$k] = $v;
				}
			}
		}
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :EvnPrescrConsUsluga_id;

			exec p_EvnPrescrConsUsluga_" . $action . "
				@EvnPrescrConsUsluga_id = @Res output,
				@EvnPrescrConsUsluga_pid = :EvnPrescrConsUsluga_pid,
				@PrescriptionType_id = :PrescriptionType_id,
				@PrescriptionStatusType_id = :PrescriptionStatusType_id,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@UslugaComplex_id = :UslugaComplex_id,
				@EvnPrescrConsUsluga_setDT = :EvnPrescrConsUsluga_setDate,
				@EvnPrescrConsUsluga_IsCito = :EvnPrescrConsUsluga_IsCito,
				@EvnPrescrConsUsluga_Descr = :EvnPrescrConsUsluga_Descr,
				@DopDispInfoConsent_id = :DopDispInfoConsent_id,
				@MedService_id = :MedService_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnPrescrConsUsluga_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$data['EvnPrescrConsUsluga_IsCito'] = (empty($data['EvnPrescrConsUsluga_IsCito']) || $data['EvnPrescrConsUsluga_IsCito'] != 'on')? 1 : 2;
		$data['MedService_id'] = (!empty($data['MedService_id']))? $data['MedService_id'] : null;
		$data['PrescriptionType_id'] = $this->getPrescriptionTypeId();

		//echo getDebugSQL($query, $data); exit();

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$trans_result = $result->result('array');
			if(!empty($trans_result) && !empty($trans_result[0]) && !empty($trans_result[0]['EvnPrescrConsUsluga_id']) && empty($trans_result[0]['Error_Msg']))
			{
				$trans_good = true;
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

		if($trans_good === true && $allow_sign && !empty($data['signature']))
		{
			/*
			Пока функционал подписания назначений не нужен
			$this->load->model('EvnPrescr_model', 'EvnPrescr_model');
			$res = $this->EvnPrescr_model->signEvnPrescr(array(
				'PrescriptionType_id' => $this->getPrescriptionTypeId(),
				'parentEvnClass_SysNick' => $data['parentEvnClass_SysNick'],
				'EvnPrescr_pid' => $data['EvnPrescrConsUsluga_pid'],
				'EvnPrescr_id' => $trans_result[0]['EvnPrescrConsUsluga_id'],
				'pmUser_id' => $data['pmUser_id'],
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
			}
			*/
		}
		return $trans_result;
	}

	/**
	 * Получение данных для формы редактирования
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array|boolean
	 */
	public function doLoad($data) {
		$query = "
			select top 1
				case when ep.PrescriptionStatusType_id = 1 then 'edit' else 'view' end as accessType,
				ep.EvnPrescrConsUsluga_Descr,
				ep.EvnPrescrConsUsluga_id,
				ep.EvnPrescrConsUsluga_pid,
				convert(varchar(10), ep.EvnPrescrConsUsluga_setDT, 104) as EvnPrescrConsUsluga_setDate,
				ep.UslugaComplex_id,
				ED.EvnDirection_id,
				null as EvnPrescrConsUsluga_uslugaList,
				case when isnull(ep.EvnPrescrConsUsluga_IsCito,1) = 1 then 'off' else 'on' end as EvnPrescrConsUsluga_IsCito,
				ep.PersonEvn_id,
				ep.Server_id
			from
				v_EvnPrescrConsUsluga ep with (nolock)
				outer apply (
					Select top 1 ED.EvnDirection_id
					from v_EvnPrescrDirection epd with (nolock)
					inner join v_EvnDirection_all ED with (nolock) on epd.EvnDirection_id = ED.EvnDirection_id
						and ED.EvnDirection_failDT is null
						and ISNULL(ED.EvnStatus_id, 16) not in (12,13)
					where epd.EvnPrescr_id = EP.EvnPrescrConsUsluga_id
					order by epd.EvnPrescrDirection_insDT desc
				) ED
			where
				EvnPrescrConsUsluga_id = :EvnPrescrConsUsluga_id
		";

		$queryParams = array(
			'EvnPrescrConsUsluga_id' => $data['EvnPrescrConsUsluga_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
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
				,EP.MedService_id
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
					when TTMS.TimetableMedService_id is not null then isnull(MS.MedService_Name,'') +' / '+ isnull(Lpu.Lpu_Nick,'')
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
					when TTMS.TimetableMedService_id is not null then isnull(convert(varchar(10), TTMS.TimetableMedService_begTime, 104),'')+' '+isnull(convert(varchar(5), TTMS.TimetableMedService_begTime, 108),'')
					when EQ.EvnQueue_id is not null then 'В очереди с '+ isnull(convert(varchar(10), EQ.EvnQueue_setDate, 104),'')
				else '' end as RecDate
				,case
					when TTMS.TimetableMedService_id is not null then 'TimetableMedService'
					when EQ.EvnQueue_id is not null then 'EvnQueue'
				else '' end as timetable
				,case
					when TTMS.TimetableMedService_id is not null  then TTMS.TimetableMedService_id
					--when EU.EvnUsluga_id is not null then EU.EvnUsluga_id
					when EQ.EvnQueue_id is not null then EQ.EvnQueue_id
				else '' end as timetable_id
				,EP.EvnPrescr_pid as timetable_pid
				,LU.LpuUnitType_SysNick
				,DT.DirType_Code
				,UC.UslugaComplex_id
				,UC.UslugaComplex_2011id
				,UC.UslugaComplex_Code
				,UC.UslugaComplex_Name
				,null as TableUsluga_id
				,CASE 
					when ((ED.Lpu_did is not null and ED.Lpu_did <> LpuSession.Lpu_id) or (EPCU.Lpu_id is not null and EPCU.Lpu_id <> LpuSession.Lpu_id)) then 2 else 1
				end as otherMO
				,EvnStatus.EvnStatus_SysNick
				,EUP.EvnUslugaPar_id
				,EPCU.Lpu_id
			from v_EvnPrescr EP with (nolock)
				inner join v_EvnPrescrConsUsluga EPCU with (nolock) on EPCU.EvnPrescrConsUsluga_id = EP.EvnPrescr_id
				left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EPCU.UslugaComplex_id
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
					Select top 1 TimetableMedService_id, TimetableMedService_begTime from v_TimetableMedService_lite TTMS with (nolock) where TTMS.EvnDirection_id = ED.EvnDirection_id
				) TTMS
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
					and (EQ.EvnQueue_recDT is null or TTMS.TimetableMedService_id is null)
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
				-- отделение для полки и стаца и для очереди
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = coalesce(ED.LpuSection_did, MS.LpuSection_id)
				-- заказанная услуга для параклиники
				left join v_EvnUslugaPar EUP with (NOLOCK) on EUP.EvnDirection_id = ED.EvnDirection_id
				-- подразделение для очереди и служб
				left join v_LpuUnit LU with (nolock) on coalesce(ED.LpuUnit_did,EQ.LpuUnit_did,MS.LpuUnit_id) = LU.LpuUnit_id -- todo: в ED.LpuUnit_did пусто, чего не должно быть
				-- профиль для очереди
				left join v_LpuSectionProfile LSPD with (nolock) on coalesce(ED.LpuSectionProfile_id,EQ.LpuSectionProfile_did,LS.LpuSectionProfile_id) = LSPD.LpuSectionProfile_id -- todo: тут на примере оказалось что почему то ED.LpuSectionProfile_id != EQ.LpuSectionProfile_did, чего не должно быть
				-- тип направления
				left join v_DirType DT with (NOLOCK) on ED.DirType_id = DT.DirType_id
				-- ЛПУ
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = coalesce(ED.Lpu_did, LS.Lpu_id, MS.Lpu_id, EQ.Lpu_id)
				left join v_Lpu LpuSession with (nolock) on LpuSession.Lpu_id = :Lpu_id
				outer apply (
					select top 1 EvnUsluga_id, EvnUsluga_setDT from v_EvnUsluga with (nolock)
					where EP.EvnPrescr_IsExec = 2 and UC.UslugaComplex_id is not null and EvnPrescr_id = EP.EvnPrescr_id
				) EU
				{$addJoin}
			where
				EP.EvnPrescr_pid  = :EvnPrescr_pid
				and EP.PrescriptionType_id = 13
				and EP.PrescriptionStatusType_id != 3
			order by
				EP.EvnPrescr_id,
				EP.EvnPrescr_setDT
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
			return $response;
		} else {
			return false;
		}
	}
}
