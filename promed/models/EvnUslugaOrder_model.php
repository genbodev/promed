<?php
/**
 * Модель для работы с заказами услуг параклиники
 */
class EvnUslugaOrder_model extends swModel
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 *  Установка статуса "Выполнено" для заказа услуги
	 */
	function exec($data)
	{
		$data['EvnUslugaPar_setDT'] = date('Y-m-d') .' '. date('H:i') .':00.000';

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnUslugaPar_exec
				@EvnUslugaPar_id = :EvnUslugaPar_id,
				@EvnUslugaPar_setDT = :EvnUslugaPar_setDT,
				@MedPersonal_id = :MedPersonal_id,
				@LpuSection_uid = :LpuSection_uid,
				@Lpu_uid = :Lpu_uid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select  @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
			'EvnUslugaPar_setDT' => $data['EvnUslugaPar_setDT'],
			'MedPersonal_id' => $data['MedPersonal_uid'],
			'LpuSection_uid' => $data['LpuSection_uid'],
			'Lpu_uid' => $data['Lpu_uid'],
			'pmUser_id' => $data['pmUser_id'],
		);
		/*
		echo getDebugSql($query, $queryParams); exit;
		*/
		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) ) {
			return array(array('Error_Msg' => 'Ошибка при сохранении статуса "Выполнено" для заказа услуги'));
		}
		return $result->result('array');
	}

	/**
	 *  Получение списка заказов и бирок
	 */
	function loadList($data)
	{
		$params = array();
		$params['Lpu_did'] = $data['Lpu_id'];
		$params['MedService_id'] = $data['MedService_id'];
		$params['begDate'] = $data['begDate'];
		$params['endDate'] = $data['endDate'];
		$filtersOrders = array(
			'ED.Lpu_did = :Lpu_did',
			'ED.MedService_id = :MedService_id',
		);
		$filtersTtms = array();
		//записанные отображаются в тот день, на который они записаны
		//те, кто в очереди отображаются в тот день, когда они направлены
		$filtersOrders[] = "
				(
					cast(coalesce(EUC.EvnUslugaCommon_setDate, EPL.EvnPL_setDate, EVPL.EvnVizitPL_setDate, ED.EvnDirection_setDT) as date)
					between cast(:begDate as date) and cast(:endDate as date)
					or (
						2 != ISNULL(EPCU.EvnPrescrConsUsluga_IsExec, 1) and
						15 != ED.EvnStatus_id and
						ED.TimetableMedService_id is null
					)
				)
		";
		$filtersTtms[] = "(TTMS.MedService_id = :MedService_id OR UCMS.MedService_id = :MedService_id)
			AND cast(TTMS.TimetableMedService_begTime as date)
				between cast(:begDate as date) and cast(:endDate as date)
		";

		if (!empty($data['EvnDirection_Num']))
		{
			$filtersOrders[] = "ED.EvnDirection_Num LIKE (:EvnDirection_Num+'%')";
			$params['EvnDirection_Num'] = $data['EvnDirection_Num'];
			$filtersTtms = array('1=2');
		}
		if (!empty($data['Person_SurName']))
		{
			$filtersOrders[] = "PS.Person_Surname LIKE (:Person_SurName+'%')";
			$params['Person_SurName'] = $data['Person_SurName'];
			$filtersTtms = array('1=2');
		}
		if (!empty($data['Person_FirName'])) 
		{
			$filtersOrders[] = "PS.Person_Firname LIKE (:Person_FirName+'%')";
			$params['Person_FirName'] = $data['Person_FirName'];
			$filtersTtms = array('1=2');
		}
		if (!empty($data['Person_SecName'])) 
		{
			$filtersOrders[] = "PS.Person_Secname LIKE (:Person_SecName+'%')";
			$params['Person_SecName'] = $data['Person_SecName'];
			$filtersTtms = array('1=2');
		}
		if (!empty($data['Person_BirthDay'])) 
		{
			$filtersOrders[] = "cast(PS.Person_BirthDay as date) = cast(:Person_BirthDay as date)";
			$params['Person_BirthDay'] = $data['Person_BirthDay'];
			$filtersTtms = array('1=2');
		}
		if (!empty($data['UslugaComplex_id'])) 
		{
			$filtersOrders[] = "EUP.UslugaComplex_id = :UslugaComplex_id";
			$params['UslugaComplex_id'] = $data['UslugaComplex_id'];
		}
		$filter = implode(' AND ', $filtersOrders);
		$filter_tt = implode(' AND ', $filtersTtms);

		// бирки
		$sql = "
			declare @now date = cast(dbo.tzGetDate() as date);

			SELECT
				'ttms'+ cast(TTMS.TimetableMedService_id as varchar) as item_key,
				EUP.EvnUslugaPar_id,
				ED.EvnDirection_id,
				ED.Diag_id,
				ED.EvnStatus_id,
				ED.EvnDirection_Num,
				null as EvnQueue_id,
				TTMS.TimetableMedService_id,
				case when TTMS.TimetableMedService_begTime is not null
					then convert(varchar,cast(TTMS.TimetableMedService_begTime as datetime),104)
					else convert(varchar,cast(TTMS.TimetableMedService_factTime as datetime),104)
				end as TimetableMedService_begDate,
				case when exists(
					select * 
					from v_PersonQuarantine PQ with(nolock)
					where PQ.Person_id = EUP.Person_id
					and PQ.PersonQuarantine_endDT is null
				) then 'true' else 'false' end as PersonQuarantine_IsOn,
				EUP.Person_id,
				EUP.PersonEvn_id,
				EUP.Server_id,
				EUP.UslugaComplex_id,
				EPCU.EvnPrescrConsUsluga_id as EvnPrescr_id,
				isnull(EPCU.EvnPrescrConsUsluga_IsExec, 1) as EvnPrescr_IsExec,
				EPCU.PrescriptionStatusType_id,
				13 as PrescriptionType_id,
				EPP.Evn_id as EvnPrescrParentEvn_id,
				EPP.Evn_rid as EvnPrescrParentEvn_rid,
				EPP.EvnClass_SysNick as EvnPrescrParentEvnClass_SysNick,
				LpuFrom.Lpu_Nick as Lpu_Name,
				LpuSectionFrom.LpuSection_Name as LpuSection_Name,
				case when 2 = coalesce(EUP.EvnUslugaPar_IsCito, ED.EvnDirection_IsCito, 1) then 'true' else 'false' end as EvnUslugaPar_isCito,
				case when 2 = ISNULL(EPCU.EvnPrescrConsUsluga_IsExec, 1) OR 15 = ED.EvnStatus_id then 'true' else 'false' end as Usluga_IsHasReception,
				convert(varchar(10), ED.EvnDirection_setDT, 104) as EvnDirection_setDT,
				isnull(convert(varchar(5), TTMS.TimetableMedService_begTime, 108),'б/з') as TimetableMedService_begTime,
				PS.Person_Fio as Person_FIO,
				UC.UslugaComplex_Name as UslugaComplex_Name,
				MP.Person_Fin as MedPersonal_FIO,
				D.Diag_FullName as Diag_Name,
				case when UCMS.UslugaComplexMedService_id IS NULL then 'Общее' else UC.UslugaComplex_Name end as TimetableMedServiceType,
				UCMS.UslugaComplex_id as UslugaComplexRecord_id,
				MPF.EvnDirection_From,
				null as UslugaComplexMedService_id -- услуга для записи
			FROM v_TimetableMedService_lite TTMS with (nolock)
				left join v_UslugaComplexMedService UCMS (nolock) on UCMS.UslugaComplexMedService_id = TTMS.UslugaComplexMedService_id
				left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = UCMS.UslugaComplex_id
				left join v_EvnDirection_all ED with (nolock) on TTMS.EvnDirection_id = ED.EvnDirection_id
					and ED.EvnDirection_failDT is null
					and ED.EvnStatus_id not in (12,13)	
				left join v_EvnUslugaPar EUP with (nolock) on EUP.EvnDirection_id = ED.EvnDirection_id
				left join v_Person_all PS with (nolock) on EUP.PersonEvn_id = PS.PersonEvn_id AND EUP.Server_id = PS.Server_id
				left join v_Lpu LpuFrom with (nolock) on LpuFrom.Lpu_id = ED.Lpu_id
				left join v_LpuSection LpuSectionFrom with (nolock) on LpuSectionFrom.LpuSection_id = ED.LpuSection_id
				left join v_Diag D with (nolock) on D.Diag_id = ED.Diag_id
				left join v_EvnPrescrDirection EPD with (nolock) on EPD.EvnDirection_id = ED.EvnDirection_id
				left join v_EvnPrescrConsUsluga EPCU with (nolock) on EPCU.EvnPrescrConsUsluga_id = EPD.EvnPrescr_id
				left join v_EvnUslugaCommon EUC with (nolock) on EUC.EvnPrescr_id = EUP.EvnPrescr_id
				left join v_Evn EPP with (nolock) on EPP.Evn_id = EPCU.EvnPrescrConsUsluga_pid
				left join v_EvnPL EPL with (nolock) on EPL.EvnDirection_id = ED.EvnDirection_id
				left join v_EvnVizitPL EVPL with (nolock) on EVPL.EvnDirection_id = ED.EvnDirection_id
				left join v_MedPersonal MP with (nolock) on coalesce(EPL.MedPersonal_id,EVPL.MedPersonal_id,EUC.MedPersonal_id) = MP.MedPersonal_id
				outer apply (
					select top 1
						(l.Lpu_Nick + ISNULL(' / ' + LS.LpuSection_Name, '') + ISNULL(' / ' + MP.Person_Fio, '')) as EvnDirection_From
					from
						v_MedPersonal MP with (nolock)
						left join v_LpuSection LS with (nolock) on ED.LpuSection_id = LS.LpuSection_id
						left join v_Lpu l with (nolock) on l.Lpu_id = ED.Lpu_sid
					where
						MP.MedPersonal_id = ED.MedPersonal_id
				) MPF
			WHERE
				{$filter_tt}
		";
		
		// очередь и принятые без записи
		if ($data['loadQueue'] == 1) {
			$sql = "
				declare @now date = cast(dbo.tzGetDate() as date);
				
				SELECT TOP 10000
					'order'+ cast(EUP.EvnUslugaPar_id as varchar) as item_key,
					EUP.EvnUslugaPar_id,
					ED.EvnDirection_id,
					ED.Diag_id,
					ED.EvnStatus_id,
					ED.EvnDirection_Num,
					EQ.EvnQueue_id,
					ED.TimetableMedService_id,
					null as TimetableMedService_begDate,
					case when exists(
						select * 
						from v_PersonQuarantine PQ with(nolock)
						where PQ.Person_id = EUP.Person_id
						and PQ.PersonQuarantine_endDT is null
					) then 'true' else 'false' end as PersonQuarantine_IsOn,
					EUP.Person_id,
					EUP.PersonEvn_id,
					EUP.Server_id,
					EUP.UslugaComplex_id,
					EPCU.EvnPrescrConsUsluga_id as EvnPrescr_id,
					isnull(EPCU.EvnPrescrConsUsluga_IsExec, 1) as EvnPrescr_IsExec,
					EPCU.PrescriptionStatusType_id,
					13 as PrescriptionType_id,
					EPP.Evn_id as EvnPrescrParentEvn_id,
					EPP.Evn_rid as EvnPrescrParentEvn_rid,
					EPP.EvnClass_SysNick as EvnPrescrParentEvnClass_SysNick,
					LpuFrom.Lpu_Nick as Lpu_Name,
					LpuSectionFrom.LpuSection_Name as LpuSection_Name,
					case when 2 = coalesce(EUP.EvnUslugaPar_IsCito, ED.EvnDirection_IsCito, 1) then 'true' else 'false' end as EvnUslugaPar_isCito,
					case when 2 = ISNULL(EPCU.EvnPrescrConsUsluga_IsExec, 1) OR 15 = ED.EvnStatus_id then 'true' else 'false' end as Usluga_IsHasReception,
					convert(varchar(10), ED.EvnDirection_setDT, 104) as EvnDirection_setDT,
					'б/з' as TimetableMedService_begTime,
					PS.Person_Fio as Person_FIO,
					UC.UslugaComplex_Name as UslugaComplex_Name,
					MP.Person_Fin as MedPersonal_FIO,
					D.Diag_FullName as Diag_Name,
					'Общее' as TimetableMedServiceType,
					null as UslugaComplexRecord_id,
					MPF.EvnDirection_From,
					ttmsuc.UslugaComplexMedService_id -- услуга для записи
				FROM v_EvnUslugaPar EUP with (nolock)
					inner join v_EvnDirection_all ED with (nolock) on EUP.EvnDirection_id = ED.EvnDirection_id
						and ED.EvnDirection_failDT is null
						and ED.EvnStatus_id not in (12,13)
					inner join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EUP.UslugaComplex_id
					left join v_Person_all PS with (nolock) on EUP.PersonEvn_id = PS.PersonEvn_id AND EUP.Server_id = PS.Server_id
					left join v_Lpu LpuFrom with (nolock) on LpuFrom.Lpu_id = ED.Lpu_id
					left join v_LpuSection LpuSectionFrom with (nolock) on LpuSectionFrom.LpuSection_id = ED.LpuSection_id
					left join v_Diag D with (nolock) on D.Diag_id = ED.Diag_id
					left join v_EvnPrescrDirection EPD with (nolock) on EPD.EvnDirection_id = ED.EvnDirection_id
					left join v_EvnPrescrConsUsluga EPCU with (nolock) on EPCU.EvnPrescrConsUsluga_id = EPD.EvnPrescr_id
					left join v_EvnUslugaCommon EUC with (nolock) on EUC.EvnPrescr_id = EUP.EvnPrescr_id
					left join v_Evn EPP with (nolock) on EPP.Evn_id = EPCU.EvnPrescrConsUsluga_pid
					left join v_EvnPL EPL with (nolock) on EPL.EvnDirection_id = ED.EvnDirection_id
					left join v_EvnVizitPL EVPL with (nolock) on EVPL.EvnDirection_id = ED.EvnDirection_id
					left join v_MedPersonal MP with (nolock) on coalesce(EPL.MedPersonal_id,EVPL.MedPersonal_id,EUC.MedPersonal_id) = MP.MedPersonal_id
					/* только из очереди */
					cross apply (
						SELECT top 1 EvnQueue_id
						from v_EvnQueue (nolock)
						where EvnDirection_id = ED.EvnDirection_id
							--and EvnQueue_id = ED.EvnQueue_id
							AND EvnQueue_recDT is null
							AND EvnQueue_failDT is NULL
					) EQ
					outer apply (
						select top 1
							UCMS.UslugaComplexMedService_id
						from
							v_UslugaComplexMedService UCMS (nolock)
							inner join v_TimetableMedService_lite TTMS2 (nolock) on TTMS2.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
						where
							(TTMS2.MedService_id = :MedService_id OR UCMS.MedService_id = :MedService_id)
							AND cast(TTMS2.TimetableMedService_begTime as date) > @now
							and UCMS.UslugaComplex_id = EUP.UslugaComplex_id
					) ttmsuc
					outer apply (
						select top 1
							(l.Lpu_Nick + ISNULL(' / ' + LS.LpuSection_Name, '') + ISNULL(' / ' + MP.Person_Fio, '')) as EvnDirection_From
						from
							v_MedPersonal MP with (nolock)
							left join v_LpuSection LS with (nolock) on ED.LpuSection_id = LS.LpuSection_id
							left join v_Lpu l with (nolock) on l.Lpu_id = ED.Lpu_sid
						where
							MP.MedPersonal_id = ED.MedPersonal_id
					) MPF
				WHERE
					{$filter}
			";
			
		}
		
		/*
		echo getDebugSql($sql, $params);
		*/
		$res = $this->db->query($sql,$params);
		if ( is_object($res) ) {
			$result = $res->result('array');
			$result[] = array(
				'EvnUslugaPar_id' => null,
				'EvnQueue_id' => -1,
				'TimetableMedService_id' => null
			);
			return $result;
		} else {
			return false;
		}
	}

}
