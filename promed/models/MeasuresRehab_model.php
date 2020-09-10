<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MeasuresRehab_model - модель для работы c мероприятиями реабилитации и абилитации
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			12.12.2016
 */

class MeasuresRehab_model extends swModel {
	public $IPRAScheme = 'dbo';

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		if (getRegionNick() == 'ufa') {
			$this->IPRAScheme = 'r2';
		}
	}

	/**
	 * Получение списка услуг, доступных для мероприятия реабилитации
	 */
	function loadEvnUslugaList($data) {
		$params = array('IPRARegistry_id' => $data['IPRARegistry_id']);
		$query = "
			declare
				@Person_id bigint,
				@begDate date,
				@endDate date
						
			select
				@Person_id = IR.Person_id,
				@begDate = IR.IPRARegistry_issueDate,
				@endDate = dateadd(day, -30, IR.IPRARegistry_EndDate)
			from
				{$this->IPRAScheme}.v_IPRARegistry IR with(nolock)
			where
				IR.IPRARegistry_id = :IPRARegistry_id
						
			if @endDate is null or @endDate > dbo.tzGetDate()
				set @endDate = dbo.tzGetDate()
						
			select
				EU.EvnUsluga_id,
				convert(varchar(10), EU.EvnUsluga_setDT, 104) as EvnUsluga_setDate,
				UC.UslugaComplex_id,
				UC.UslugaComplex_Code,
				UC.UslugaComplex_Name,
				L.Lpu_id,
				L.Org_id,
				L.Org_Nick
			from
				v_EvnUsluga EU with(nolock)
				inner join v_UslugaComplex UC with(nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
				inner join v_Lpu L with(nolock) on L.Lpu_id = EU.Lpu_id
			where
				EU.Person_id = @Person_id
				and EU.EvnUsluga_setDate between @begDate and @endDate
			order by
				EU.EvnUsluga_setDT asc
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка случаев лечения, доступных для мероприятия реабилитации
	 */
	function loadEvnList($data) {
		$params = array('IPRARegistry_id' => $data['IPRARegistry_id']);
		$query = "
			declare
				@Person_id bigint,
				@begDate date,
				@endDate date
			
			select
				@Person_id = IR.Person_id,
				@begDate = IR.IPRARegistry_issueDate,
				@endDate = dateadd(day, -30, IR.IPRARegistry_EndDate)
			from
				{$this->IPRAScheme}.v_IPRARegistry IR with(nolock)
			where
				IR.IPRARegistry_id = :IPRARegistry_id
			
			if @endDate is null or @endDate > dbo.tzGetDate()
				set @endDate = dbo.tzGetDate()
			
			select
				E.Evn_id,
				E.EvnClass_SysNick,
				case 
					when E.EvnClass_SysNick = 'EvnPL' then 'АПП'
					when E.EvnClass_SysNick = 'EvnPS' and LU.LpuUnitType_SysNick = 'stac' then 'КС'
					when E.EvnClass_SysNick = 'EvnPS' then 'СЗП'
				end as EvnClass_Nick,
				coalesce(EPL.EvnPL_NumCard, EPS.EvnPS_NumCard) as Evn_NumCard,
				convert(varchar(10), E.Evn_setDT, 104) as Evn_setDate,
				convert(varchar(10), E.Evn_disDT, 104) as Evn_disDate,
				L.Lpu_id,
				L.Org_id,
				L.Lpu_Nick
			from
				v_Evn E with(nolock)
				left join v_EvnPL EPL with(nolock) on EPL.EvnPL_id = E.Evn_id
				left join v_EvnPS EPS with(nolock) on EPS.EvnPS_id = E.Evn_id
				left join v_LpuSection LS with(nolock) on LS.LpuSection_id = EPS.LpuSection_id
				left join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_Lpu L with(nolock) on L.Lpu_id = E.Lpu_id
			where
				E.Person_id = @Person_id
				and E.Evn_disDate between @begDate and @endDate
				and E.EvnClass_SysNick in ('EvnPL','EvnPS')
				and (E.EvnClass_SysNick not like 'EvnPL' or isnull(EPL.EvnPL_IsFinish,1) = 2)
			order by
				E.Evn_setDate asc
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка медикаментов, доступных для мероприятия реабилитации
	 */
	function loadReceptOtovList($data) {
		$params = array('IPRARegistry_id' => $data['IPRARegistry_id']);
		$query = "
			declare
				@Person_id bigint,
				@begDate date,
				@endDate date
									
			select
				@Person_id = IR.Person_id,
				@begDate = IR.IPRARegistry_issueDate,
				@endDate = dateadd(day, -30, IR.IPRARegistry_EndDate)
			from
				v_IPRARegistry IR with(nolock)
			where
				IR.IPRARegistry_id = :IPRARegistry_id
									
			if @endDate is null or @endDate > dbo.tzGetDate()
				set @endDate = dbo.tzGetDate()
									
			select
				RO.ReceptOtov_id,
				ER.EvnRecept_Ser,
				ER.EvnRecept_Num,
				D.Drug_id,
				D.Drug_Name,
				convert(varchar(10), RO.EvnRecept_otpDate, 104) as EvnRecept_otpDate,
				L.Lpu_id,
				L.Org_id,
				L.Lpu_Nick
			from
				v_EvnRecept ER with(nolock)
				outer apply(
					select top 1
						*
					from ReceptOtov RO with(nolock)
					where RO.EvnRecept_id = ER.EvnRecept_id
					and RO.EvnRecept_otpDate is not null
				) RO
				left join v_Drug D with(nolock) on D.Drug_id = ER.Drug_id
				left join v_Lpu L with(nolock) on L.Lpu_id = ER.Lpu_id
			where
				ER.Person_id = @Person_id
				and cast(RO.EvnRecept_otpDate as date) between @begDate and @endDate
			order by
				cast(RO.EvnRecept_otpDate as date),
				ER.EvnRecept_Ser,
				ER.EvnRecept_Num
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}
	
	/**
	 * Получение списка мероприятий реабилитации или абилитации по пациенту
	 */
	function loadMeasuresRehabGridPerson($data) {
		$params = array('EvnPrescrMse_id' => $data['EvnPrescrMse_id']);
		//$params = array('EvnPrescrMse_id' => 730023880993197);
		$query = "
			select
				MeasuresRehabMSE_id,
				EvnPrescrMse_id,
				MeasuresRehabMSE_Name,
				MeasuresRehabMSE_Type,
				MeasuresRehabMSE_SubType,
				MeasuresRehabMSE_Result,
				MeasuresRehabMSE_IsExport,
				convert(varchar(10), MeasuresRehabMSE_BegDate, 104) as MeasuresRehabMSE_BegDate,
				convert(varchar(10), MeasuresRehabMSE_EndDate, 104) as MeasuresRehabMSE_EndDate
			from
				MeasuresRehabMSE
			where
				EvnPrescrMse_id = :EvnPrescrMse_id
			order by
				MeasuresRehabMSE_BegDate
		";

		$resp = $this->queryResult($query, $params);

		return array(
			'data' => $resp
		);
	}	

	/**
	 * Получение списка мероприятий реабилитации или абилитации
	 */
	function loadMeasuresRehabGrid($data) {
		$params = array('IPRARegistry_id' => $data['IPRARegistry_id']);

		$query = "
			select
				MR.MeasuresRehab_id,
				MR.IPRARegistry_id,
				MR.MeasuresRehab_Name,
				convert(varchar(10), MR.MeasuresRehab_setDate, 104) as MeasuresRehab_setDate,
				MRT.MeasuresRehabType_id,
				MRT.MeasuresRehabType_Name,
				MRT.MeasuresRehabType_Code,
				case
					when RO.ReceptOtov_id is not null then 'Рецепт №'+RO.EvnRecept_Num+' '+RO.EvnRecept_Ser+'. '+D.Drug_Name
					when EU.EvnUsluga_id is not null then UC.UslugaComplex_Name
					else MR.MeasuresRehab_Name
				end as MeasuresRehab_Name,
				MRST.MeasuresRehabSubType_id,
				MRST.MeasuresRehabSubType_Code,
				MRST.MeasuresRehabSubType_Name,
				MRR.MeasuresRehabResult_id,
				MRR.MeasuresRehabResult_Code,
				MRR.MeasuresRehabResult_Name,
				UC.UslugaComplex_Code as MeasuresRehab_Code
			from
				v_MeasuresRehab MR with(nolock)
				left join v_MeasuresRehabType MRT with(nolock) on MRT.MeasuresRehabType_id = MR.MeasuresRehabType_id
				left join v_MeasuresRehabSubType MRST with(nolock) on MRST.MeasuresRehabSubType_id = MR.MeasuresRehabSubType_id
				left join v_MeasuresRehabResult MRR with(nolock) on MRR.MeasuresRehabResult_id = MR.MeasuresRehabResult_id
				left join v_EvnUsluga EU with(nolock) on EU.EvnUsluga_id = MR.EvnUsluga_id
				left join v_UslugaComplex UC with(nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
				left join ReceptOtov RO with(nolock) on RO.ReceptOtov_id = MR.ReceptOtov_id
				left join v_EvnRecept ER with(nolock) on ER.EvnRecept_id = RO.EvnRecept_id
				left join v_Drug D with(nolock) on D.Drug_id = ER.Drug_id
			where
				MR.IPRARegistry_id = :IPRARegistry_id
			order by
				MR.MeasuresRehab_setDate
		";

		$resp = $this->queryResult($query, $params);

		return array(
			'data' => $resp
		);
	}

	/**
	 * Получение списка мероприятий реабилитации или абилитации для экспорта
	 */
	function loadMeasuresRehabExportGrid($data) {
		$params = array();
		$filters = array('1=1');

		if (!empty($data['MeasuresRehab_begRange'])) {
			$filters[] = "MR.MeasuresRehab_setDate >= :MeasuresRehab_begRange";
			$params['MeasuresRehab_begRange'] = $data['MeasuresRehab_begRange'];
		}
		if (!empty($data['MeasuresRehab_endRange'])) {
			$filters[] = "MR.MeasuresRehab_setDate <= :MeasuresRehab_endRange";
			$params['MeasuresRehab_endRange'] = $data['MeasuresRehab_endRange'];
		}
		if (!empty($data['MeasuresRehab_IsExport'])) {
			$filters[] = "isnull(MR.MeasuresRehab_IsExport, 1) = :MeasuresRehab_IsExport";
			$params['MeasuresRehab_IsExport'] = $data['MeasuresRehab_IsExport'];
		}
		if (!empty($data['LpuAttach_id'])) {
			$filters[] = "LpuAttach.Lpu_id = :LpuAttach_id";
			$params['LpuAttach_id'] = $data['LpuAttach_id'];
		}

		$filters_str = implode(" and ", $filters);
		$query = "
			select
				MR.MeasuresRehab_id,
				IR.IPRARegistry_id,
				IR.IPRARegistry_Number,
				isnull(PS.Person_SurName,'')+isnull(' '+PS.Person_FirName, '')+isnull(' '+PS.Person_SecName, '') as Person_Fio,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				LpuAttach.Lpu_id as LpuAttach_id,
				LpuAttach.Lpu_Nick as LpuAttach_Nick,
				MR.MeasuresRehab_Name,
				convert(varchar(10), MR.MeasuresRehab_setDate, 104) as MeasuresRehab_setDate,
				MRT.MeasuresRehabType_id,
				MRT.MeasuresRehabType_Code,
				MRT.MeasuresRehabType_Name,
				MRST.MeasuresRehabSubType_id,
				MRST.MeasuresRehabSubType_Code,
				MRST.MeasuresRehabSubType_Name,
				MRR.MeasuresRehabResult_id,
				MRR.MeasuresRehabResult_Code,
				MRR.MeasuresRehabResult_Name
			from
				v_MeasuresRehab MR with(nolock)
				inner join v_IPRARegistry IR with(nolock) on IR.IPRARegistry_id = MR.IPRARegistry_id
				inner join v_PersonState PS with(nolock) on PS.Person_id = IR.Person_id
				left join v_Lpu LpuAttach with(nolock) on LpuAttach.Lpu_id = PS.Lpu_id
				left join v_MeasuresRehabType MRT with(nolock) on MRT.MeasuresRehabType_id = MR.MeasuresRehabType_id
				left join v_MeasuresRehabSubType MRST with(nolock) on MRST.MeasuresRehabSubType_id = MR.MeasuresRehabSubType_id
				left join v_MeasuresRehabResult MRR with(nolock) on MRR.MeasuresRehabResult_id = MR.MeasuresRehabResult_id
			where
				{$filters_str}
		";

		//echo getDebugSQL($query, $params);exit;
		$resp = $this->queryResult($query, $params);

		return array(
			'data' => $resp
		);
	}

	/**
	 * Получение данных мероприятия реабилитиции для редактирования
	 */
	function loadMeasuresRehabForm($data) {
		$params = array('MeasuresRehab_id' => $data['MeasuresRehab_id']);
		$query = "
			select top 1
				MR.MeasuresRehab_id,
				MR.IPRARegistry_id,
				case 
					when MR.EvnUsluga_id is not null then 'usluga'
					when MR.Evn_id is not null then 'evn'
					when MR.ReceptOtov_id is not null then 'drug'
					else 'other'
				end as type,
				MR.MeasuresRehabType_id,
				MR.MeasuresRehabSubType_id,
				convert(varchar(10), MR.MeasuresRehab_setDate, 104) as MeasuresRehab_setDate,
				MR.MeasuresRehab_Name,
				case 
					when MR.Org_id is not null then cast(MR.Org_id as varchar)
					else MR.MeasuresRehab_OrgName
				end as Org_id,
				MR.MeasuresRehabResult_id,
				MR.EvnUsluga_id,
				MR.Evn_id,
				MR.ReceptOtov_id
			from 
				v_MeasuresRehab MR with(nolock)
			where MR.MeasuresRehab_id = :MeasuresRehab_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Сохранение мероприятия реабилитации
	 */
	function saveMeasuresRehab($data) {
		$params = array(
			'MeasuresRehab_id' => !empty($data['MeasuresRehab_id'])?$data['MeasuresRehab_id']:null,
			'IPRARegistry_id' => $data['IPRARegistry_id'],
			'MeasuresRehabType_id' => $data['MeasuresRehabType_id'],
			'MeasuresRehabSubType_id' => !empty($data['MeasuresRehabSubType_id'])?$data['MeasuresRehabSubType_id']:null,
			'MeasuresRehab_setDate' => !empty($data['MeasuresRehab_setDate'])?$data['MeasuresRehab_setDate']:null,
			'MeasuresRehab_OrgName' => !empty($data['MeasuresRehab_OrgName'])?$data['MeasuresRehab_OrgName']:null,
			'Org_id' => !empty($data['Org_id'])?$data['Org_id']:null,
			'MeasuresRehab_Name' => !empty($data['MeasuresRehab_Name'])?$data['MeasuresRehab_Name']:null,
			'MeasuresRehabResult_id' => !empty($data['MeasuresRehabResult_id'])?$data['MeasuresRehabResult_id']:null,
			'EvnUsluga_id' => !empty($data['EvnUsluga_id'])?$data['EvnUsluga_id']:null,
			'Evn_id' => !empty($data['Evn_id'])?$data['Evn_id']:null,
			'ReceptOtov_id' => !empty($data['ReceptOtov_id'])?$data['ReceptOtov_id']:null,
			'pmUser_id' => $data['pmUser_id'],
		);

		if (empty($params['MeasuresRehab_id'])) {
			$procedure = 'p_MeasuresRehab_ins';
		} else {
			$procedure = 'p_MeasuresRehab_upd';
		}

		$query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint = :MeasuresRehab_id;
			exec {$procedure}
				@MeasuresRehab_id = @Res output,
				@IPRARegistry_id = :IPRARegistry_id,
				@MeasuresRehabType_id = :MeasuresRehabType_id,
				@MeasuresRehabSubType_id = :MeasuresRehabSubType_id,
				@MeasuresRehab_setDate = :MeasuresRehab_setDate,
				@MeasuresRehab_OrgName = :MeasuresRehab_OrgName,
				@Org_id = :Org_id,
				@MeasuresRehab_Name = :MeasuresRehab_Name,
				@MeasuresRehabResult_id = :MeasuresRehabResult_id,
				@EvnUsluga_id = :EvnUsluga_id,
				@Evn_id = :Evn_id,
				@ReceptOtov_id = :ReceptOtov_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Res as MeasuresRehab_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при сохранении мероприятия реабилитации');
		}
		return $response;
	}

	/**
	 * Удаление мероприятия реабилитации
	 */
	function deleteMeasuresRehab($data) {
		$params = array('MeasuresRehab_id' => $data['MeasuresRehab_id']);
		$query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint;
			exec p_MeasuresRehab_del
				@MeasuresRehab_id = :MeasuresRehab_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при удалении мероприятия реабилитации');
		}
		return $response;
	}

	/**
	 * Добавление идентификатора мероприятия для экспорта
	 */
	function addMeasuresRehabExportID($data) {
		$params = array(
			'MeasuresRehab_id' => $data['MeasuresRehab_id'],
			'pmUser_id' => $data['pmUser_id'],
		);
		$query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint;
			set @Res = (
				select  top 1 MeasuresRehabExportID_id
				from v_MeasuresRehabExportID with(nolock) 
				where MeasuresRehab_id = :MeasuresRehab_id
				order by MeasuresRehabExportID_insDT desc
			);	
			if @Res is null
			begin
				exec p_MeasuresRehabExportID_ins
					@MeasuresRehabExportID_id = @Res output,
					@MeasuresRehab_id = :MeasuresRehab_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output
			end
			select @Res as MeasuresRehabExportID_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при подгатовке мероприятия для экспорта');
		}
		return $resp;
	}

	/**
	 * Формирование скрипта для экспорта данных мероприятий реабилитации
	 */
	function genMeasuresRehabExportScript($data) {
		if (!is_array($data['MeasuresRehab_ids'])) {
			return $this->createError('','Не передан список мероприятий для экспорта');
		}

		$ids = $data['MeasuresRehab_ids'];
		if (count($ids) == 0 || (count($ids) == 1 && empty($ids[0]))) {
			return $this->createError('','Не передан список мероприятий для экспорта');
		}

		//Сохранение идентификаторов мероприятий для экспорта
		foreach($ids as $id) {
			$resp = $this->addMeasuresRehabExportID(array(
				'MeasuresRehab_id' => $id,
				'pmUser_id' => $data['pmUser_id']
			));
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
		}

		//Формирование скрипта для экспорта данных мероприятий
		$query = "
			declare
			 	@SqlExportInsert varchar(max),
				@Error_Code int,
				@Error_Message varchar(4000);
			exec xp_IPRAExportInsert
				@IsNotTruncate = :IsNotTruncate,
				@SqlExportInsert = @SqlExportInsert output,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @SqlExportInsert as SqlExportInsert, @Error_Code as Error_Code, @Error_Message as Error_Message;
		";
		$params = array(
			'IsNotTruncate' => 1
		);
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при формировании скрипта для экспорта данных');
		}
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		return $resp;
	}
	
	/**
	 *	получение данных по "Мероприятия по медицинской реабилитации"
	 */
	function getMeasuresForMedicalRehabilitation($data)
	{
		$query = '
			select
				ES.EvnStick_id as EvnStick_id,
				ES.Person_id as Person_id,
				case
					when EPL.Diag_id is not null then EPL.Diag_id
					when EPS.Diag_id is not null then EPS.Diag_id
				end as Diag_id,
				convert(varchar(10), cast(ESWR.EvnStickWorkRelease_begDT as datetime), 104) as EvnStick_setDate,
				convert(varchar(10), cast(ESWR.EvnStickWorkRelease_endDT as datetime), 104) as EvnStick_disDate,
				DATEDIFF("d", ESWR.EvnStickWorkRelease_begDT, (case when ESWR.EvnStickWorkRelease_endDT is not null then ESWR.EvnStickWorkRelease_endDT else dbo.tzGetDate() end)) as DayCount,
				case
					when EPL.Diag_id is not null then (select diag_FullName from v_Diag with(nolock) where Diag_id = EPL.Diag_id)
					when EPS.Diag_id is not null then (select diag_FullName from v_Diag with(nolock) where Diag_id = EPS.Diag_id)
				end as Diag_Name,
				EvnStickClass = \'EvnStick\'
			from
				v_EvnStick ES with(nolock)
				left join v_EvnStickWorkRelease ESWR with(nolock) on ESWR.EvnStickBase_id = ES.EvnStick_id
				left join v_EvnPL EPL with(nolock) on EPL.EvnPL_id = ES.EvnStick_mid
					and EPL.Person_id = ES.Person_id and EPL.PersonEvn_id = ES.PersonEvn_id
				left join v_EvnPS EPS with(nolock) on EPS.EvnPS_id = ES.EvnStick_mid
					and EPS.Person_id = ES.Person_id and EPS.PersonEvn_id = ES.PersonEvn_id
			where
				ES.Person_id = :Person_id
				and ES.EvnStick_setDT is not null
				and datediff("m", (case when (ESWR.EvnStickWorkRelease_endDT is not null) then ESWR.EvnStickWorkRelease_endDT else dbo.tzGetDate() end), dbo.tzGetDate()) <=12
			union All
			select
				EMS.EvnMseStick_id as EvnStick_id,
				EMS.Person_id,
				EMS.Diag_id,
				convert(varchar(10), cast(EMS.EvnMseStick_begDT as datetime), 104) as EvnStick_setDate,
				convert(varchar(10), cast(EMS.EvnMseStick_endDT as datetime), 104) as EvnStick_disDate,
				DATEDIFF("d", EMS.EvnMseStick_begDT, (case when EMS.EvnMseStick_endDT is not null then EMS.EvnMseStick_endDT else dbo.tzGetDate() end)) as DayCount,
				(select diag_FullName from v_Diag with(nolock) where Diag_id = EMS.Diag_id) as Diag_Name,
				EvnStickClass = \'EvnMseStick\'
			from
				v_EvnMseStick EMS with(nolock)
			where
				EMS.Person_id = :Person_id
				and EMS.EvnMseStick_begDT is not null
				and datediff("m", (case when (EMS.EvnMseStick_endDT is not null) then EMS.EvnMseStick_endDT else dbo.tzGetDate() end), dbo.tzGetDate()) <=12
		';
		//echo getDebugSql($query, $data);
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Сохранение мероприятия реабилитации
	 */
	function saveMeasuresForMedicalRehabilitation($data){
		if(!$data['pmUser_id'] || !$data['EvnPrescrMse_id']) return false;
		//очистим старые записи
		$this->clearMeasuresFMR($data);
		if( (int)$data['EvnPrescrMse_IsFirstTime'] != 2 ) return false;
		if( $data['MeasuresForMedicalRehabilitation'] ){
			//добавим новые
			$MeasuresForMedicalRehabilitation = json_decode($data['MeasuresForMedicalRehabilitation']);
			if(count($MeasuresForMedicalRehabilitation)>0){
				$data['MeasuresForMedicalRehabilitation'] = $MeasuresForMedicalRehabilitation;
				$this->addMeasuresFMR($data);
			}
		}
	}
	
	/**
	 * добавление записи из формы «Мероприятия по медицинской реабилитации»
	 */
	function addMeasuresFMR($data){
		if(!$data['pmUser_id'] || !$data['EvnPrescrMse_id']) return false;
		$MeasuresForMedicalRehabilitation = $data['MeasuresForMedicalRehabilitation'];
		$countAdd = array();
		if(count($MeasuresForMedicalRehabilitation)>0){
			foreach ($MeasuresForMedicalRehabilitation as $MeasuresFMR) {
				$params = array(
					'MeasuresRehabMSE_BegDate' => $MeasuresFMR->MeasuresRehabMSE_BegDate,
					'MeasuresRehabMSE_EndDate' => $MeasuresFMR->MeasuresRehabMSE_EndDate,
					'MeasuresRehabMSE_Type' => $MeasuresFMR->MeasuresRehabMSE_Type,
					'MeasuresRehabMSE_SubType' => $MeasuresFMR->MeasuresRehabMSE_SubType,
					'MeasuresRehabMSE_Name' => $MeasuresFMR->MeasuresRehabMSE_Name,
					'MeasuresRehabMSE_Result' => $MeasuresFMR->MeasuresRehabMSE_Result,
					'action' => 'add',
					'MeasuresRehabMSE_IsExport' => $MeasuresFMR->MeasuresRehabMSE_IsExport,
					'EvnPrescrMse_id' => $data['EvnPrescrMse_id'],
					'pmUser_id' => $data['pmUser_id']
				);
				//$params = array_merge($data, $params);
				$res = $this->saveMeasuresFMR($params);
				if($res) $countAdd[] = $res;
			}
		}
		return $countAdd;
	}
	
	/**
	 * Сохранение мероприятия реабилитации
	 */
	function saveMeasuresFMR($data) {	
		if(!$data['pmUser_id'] || !$data['EvnPrescrMse_id'] || !$data['MeasuresRehabMSE_Result'] || !$data['MeasuresRehabMSE_Name']) return false;
		$params = array(
			'MeasuresRehabMSE_id' => !empty($data['MeasuresRehabMSE_id'])?$data['MeasuresRehabMSE_id']:null,
			'EvnPrescrMse_id' => !empty($data['EvnPrescrMse_id'])?$data['EvnPrescrMse_id']:null,
			'MeasuresRehabMSE_Name' => !empty($data['MeasuresRehabMSE_Name'])?$data['MeasuresRehabMSE_Name']:null,
			'MeasuresRehabMSE_Type' => !empty($data['MeasuresRehabMSE_Type'])?$data['MeasuresRehabMSE_Type']:null,
			'MeasuresRehabMSE_SubType' => !empty($data['MeasuresRehabMSE_SubType'])?$data['MeasuresRehabMSE_SubType']:null,
			'MeasuresRehabMSE_BegDate' => !empty($data['MeasuresRehabMSE_BegDate'])?$data['MeasuresRehabMSE_BegDate']:null,
			'MeasuresRehabMSE_EndDate' => !empty($data['MeasuresRehabMSE_EndDate'])?$data['MeasuresRehabMSE_EndDate']:null,
			'MeasuresRehabMSE_Result' => !empty($data['MeasuresRehabMSE_Result'])?$data['MeasuresRehabMSE_Result']:null,
			'MeasuresRehabMSE_IsExport' => !empty($data['MeasuresRehabMSE_IsExport'])?$data['MeasuresRehabMSE_IsExport']:null,
			'pmUser_id'  => $data['pmUser_id'],
		);
		
				
		if (empty($params['MeasuresRehabMSE_id'])) {
			$procedure = 'p_MeasuresRehabMSE_ins';
		} else {
			$procedure = 'p_MeasuresRehabMSE_upd';
		}
		
		$query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint = :MeasuresRehabMSE_id;
			exec {$procedure}
				@MeasuresRehabMSE_id = @Res output,
				@EvnPrescrMse_id = :EvnPrescrMse_id,
				@MeasuresRehabMSE_Name = :MeasuresRehabMSE_Name,
				@MeasuresRehabMSE_Type = :MeasuresRehabMSE_Type,
				@MeasuresRehabMSE_SubType = :MeasuresRehabMSE_SubType,
				@MeasuresRehabMSE_BegDate = :MeasuresRehabMSE_BegDate,
				@MeasuresRehabMSE_EndDate = :MeasuresRehabMSE_EndDate,
				@MeasuresRehabMSE_Result = :MeasuresRehabMSE_Result,
				@MeasuresRehabMSE_IsExport = :MeasuresRehabMSE_IsExport,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Res as MeasuresRehabMSE_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при сохранении мероприятия реабилитации');
		}
		return $response;
	}
	
	/**
	 * удаление записи из формы «Мероприятия по медицинской реабилитации»
	 */
	function deleteMeasuresForMedicalRehabilitation($data) {
		$params = array('MeasuresRehabMSE_id' => $data['MeasuresRehabMSE_id']);
		$query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint;
			exec p_MeasuresRehabMSE_del
				@MeasuresRehabMSE_id = :MeasuresRehabMSE_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при удалении мероприятия по медицинской реабилитации');
		}
		return $response;
	}
	
	/**
	 * очистить мероприятия  из формы «Мероприятия по медицинской реабилитации»
	 * загруженные из регистра ИПРА
	 */
	function clearMeasuresFMR($data) {
		if(!$data['EvnPrescrMse_id']) return false;
		$countMeasuresRehabMSE = array();
		// получим список 
		$listMeasuresFMR_ID = $this->getIDListMeasuresFMR($data);
		// удалим записи с полученными значениями
		if( count($listMeasuresFMR_ID)>0 ){
			foreach ($listMeasuresFMR_ID as $MeasuresFMR) {
				if( isset($MeasuresFMR['MeasuresRehabMSE_id']) ) {
					$countMeasuresRehabMSE[$MeasuresFMR['MeasuresRehabMSE_id']] = $this->deleteMeasuresForMedicalRehabilitation($MeasuresFMR);
				}
			}
		}
		return $countMeasuresRehabMSE;
	}
	
	/**
	 * получим список «Мероприятия по медицинской реабилитации»
	 */
	function downloadIPRAinMeasuresFMR($data){
		$listIPRARegistry = $this->getListIPRARegistry($data);
		return $listIPRARegistry;
	}
	
	
	/**
	 * получить список id мероприятий из формы «Мероприятия по медицинской реабилитации»
	 */
	function getIDListMeasuresFMR($data){
		if( !$data['EvnPrescrMse_id'] ) return false;
		$params = array(
			'EvnPrescrMse_id' => $data['EvnPrescrMse_id']
		);
		$query = "
			select MRM.MeasuresRehabMSE_id
			from MeasuresRehabMSE MRM
			where
				MRM.EvnPrescrMse_id = :EvnPrescrMse_id
		";

		$resp = $this->queryResult($query, $params);
		return $resp;
	}	
	
	/**
	 * получить список незакрытых записей пациента в регистре ИПРА
	 */
	function getListIPRARegistry($data) {
		if( !$data['Person_id'] ) return false;
		$params = array('Person_id' => $data['Person_id']);
		//$params = array('Person_id' => 5887474);
		$query = "
			select
				convert(varchar(10), MR.MeasuresRehab_setDate, 120) as MeasuresRehab_setDate, --время начало
				--MR.MeasuresRehab_setDate,
				--EV.Evn_disDT,
				convert(varchar(10), EV.Evn_disDT, 120) as Evn_disDT, --время конец
				MRT.MeasuresRehabType_Name, --тип
				case
					when RO.ReceptOtov_id is not null then 'Рецепт №'+RO.EvnRecept_Num+' '+RO.EvnRecept_Ser+'. '+D.Drug_Name
					when EU.EvnUsluga_id is not null then UC.UslugaComplex_Name
					else MR.MeasuresRehab_Name
				end as MeasuresRehab_Name, --наименование
				MRST.MeasuresRehabSubType_Name, -- подтип
				MRR.MeasuresRehabResult_Name, -- результат
				R.Person_id,
				case 
					when MR.EvnUsluga_id is not null then 'usluga'
					when MR.Evn_id is not null then 'evn'
					when MR.ReceptOtov_id is not null then 'drug'
					else 'other'
				end as type
			from
				v_IPRARegistry R with(nolock)
				left join v_MeasuresRehab MR with(nolock) on MR.IPRARegistry_id = R.IPRARegistry_id
				left join v_MeasuresRehabType MRT with(nolock) on MRT.MeasuresRehabType_id = MR.MeasuresRehabType_id
				left join v_MeasuresRehabSubType MRST with(nolock) on MRST.MeasuresRehabSubType_id = MR.MeasuresRehabSubType_id
				left join v_MeasuresRehabResult MRR with(nolock) on MRR.MeasuresRehabResult_id = MR.MeasuresRehabResult_id
				left join v_EvnUsluga EU with(nolock) on EU.EvnUsluga_id = MR.EvnUsluga_id
				left join v_UslugaComplex UC with(nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
				left join ReceptOtov RO with(nolock) on RO.ReceptOtov_id = MR.ReceptOtov_id
				left join v_EvnRecept ER with(nolock) on ER.EvnRecept_id = RO.EvnRecept_id
				left join v_Drug D with(nolock) on D.Drug_id = ER.Drug_id
				left join v_Evn EV with(nolock) on EV.Evn_id = MR.Evn_id
			where
				R.Person_id = :Person_id
		";

		$resp = $this->queryResult($query, $params);
		return $resp;
	}
}