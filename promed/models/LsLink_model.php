<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * LsLink_model - модель для работы с лекарственными взаимодействиями
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package		LsLink
 * @access		public
 * @copyright	Copyright (c) 2014 Swan Ltd.
 * @author		Dmitriy Vlasenko
 * @version		12.2019
 */
class LsLink_model extends swModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * Загрузка списка взаимодействий
	 */
	function loadLsLinkGrid($data) {
		$fields = "";
		$queryParams = [];
		$filter = "";
		if (!empty($data['PREP_ID'])) {
			$queryParams['PREP_ID'] = $data['PREP_ID'];
			$fields .= ", case when exists(select top 1 PREP_LS_ID from rls.v_PREP_LS with (nolock) where PREP_ID = :PREP_ID and LS_LINK_ID = ll.LS_LINK_ID) then 'true' else 'false' end as HAS_PREP_LS";
			$filter .= " and exists (
				select top 1
					p.PREP_ID
				from
					rls.v_PREP p with (nolock)
					left join rls.PREP_ACTMATTERS pa (nolock) on pa.PREPID = :PREP_ID and pa.MATTERID = ll.ACTMATTERS_G1ID
					left join rls.v_PREP_PHARMAGROUP pp (nolock) on pp.PREPID = :PREP_ID and pp.UNIQID = ll.CLSPHARMAGROUP_G1ID
					left join rls.v_PREP_FTGGRLS pf (nolock) on pf.PREP_ID = :PREP_ID and pf.FTGGRLS_ID = ll.FTGGRLS_G1ID
					left join rls.PREP_ACTMATTERS pa2 (nolock) on pa2.PREPID = :PREP_ID and pa2.MATTERID = ll.ACTMATTERS_G2ID
					left join rls.v_PREP_PHARMAGROUP pp2 (nolock) on pp2.PREPID = :PREP_ID and pp2.UNIQID = ll.CLSPHARMAGROUP_G2ID
					left join rls.v_PREP_FTGGRLS pf2 (nolock) on pf2.PREP_ID = :PREP_ID and pf2.FTGGRLS_ID = ll.FTGGRLS_G2ID
				where
					p.PREP_ID = :PREP_ID
					and (
						p.TRADENAMEID = ll.TRADENAMES_G1ID
						OR p.TRADENAMEID = ll.TRADENAMES_G2ID
						OR COALESCE(PA.PREPID, pp.PREPID, pf.PREP_ID, PA2.PREPID, pp2.PREPID, pf2.PREP_ID) IS NOT NULL
					)
			)";
		}
		if (!empty($data['LS_GROUP1'])) {
			$queryParams['LS_GROUP1'] = $data['LS_GROUP1'];
			$filter .= " and COALESCE(am1.RUSNAME, tn1.NAME, cpg1.NAME, fg1.NAME) like :LS_GROUP1 + '%'";
		}
		if (!empty($data['LS_GROUP2'])) {
			$queryParams['LS_GROUP2'] = $data['LS_GROUP2'];
			$filter .= " and COALESCE(am2.RUSNAME, tn2.NAME, cpg2.NAME, fg2.NAME) like :LS_GROUP2 + '%'";
		}
		if (!empty($data['PREP_NAME'])) {
			$queryParams['PREP_NAME'] = $data['PREP_NAME'];
			$filter .= " and exists (
				select top 1
					pl.PREP_LS_ID
				from
					rls.v_PREP_LS pl with (nolock)
					inner join rls.v_PREP p with (nolock) on p.PREP_ID = pl.PREP_ID
					inner join rls.v_TRADENAMES tn with (nolock) on tn.TRADENAMES_ID = p.TRADENAMEID
				where
					pl.LS_LINK_ID = ll.LS_LINK_ID
					and tn.NAME like :PREP_NAME + '%'
			)";
		}
		if (!empty($data['RlsRegnum'])) {
			$queryParams['RlsRegnum'] = $data['RlsRegnum'];
			$filter .= " and exists (
				select top 1
					pl.PREP_LS_ID
				from
					rls.v_PREP_LS pl with (nolock)
					inner join rls.v_PREP p with (nolock) on p.PREP_ID = pl.PREP_ID
					inner join rls.v_REGCERT rc with (nolock) on rc.REGCERT_ID = p.REGCERTID
				where
					pl.LS_LINK_ID = ll.LS_LINK_ID
					and rc.REGNUM like :RlsRegnum + '%'
			)";
		}

		return $this->getPagingResponse("
			select
				-- select
				ll.LS_LINK_ID,
				COALESCE(am1.RUSNAME, tn1.NAME, cpg1.NAME, fg1.NAME) as LS_GROUP1,
				COALESCE(am2.RUSNAME, tn2.NAME, cpg2.NAME, fg2.NAME) as LS_GROUP2,
				lit.NAME as LS_INFLUENCE_TYPE_NAME,
				le.DESCRIPTION as LS_EFFECT_NAME,
				lft.NAME as LS_FT_TYPE_NAME,
				lic.CODE as LS_INTERACTION_CLASS_NAME,
				ll.DESCRIPTION,
				ll.RECOMMENDATION,
				ll.BREAKTIME
				{$fields}
				-- end select
			from
				-- from
				rls.v_LS_LINK ll with (nolock)
				left join rls.v_LS_INFLUENCE_TYPE lit with (nolock) on lit.LS_INFLUENCE_TYPE_ID = ll.LS_INFLUENCE_TYPE_ID
				left join rls.v_LS_EFFECT le with (nolock) on le.LS_EFFECT_ID = ll.LS_EFFECT_ID
				left join rls.v_LS_FT_TYPE lft with (nolock) on lft.LS_FT_TYPE_ID = ll.LS_FT_TYPE_ID
				left join rls.v_LS_INTERACTION_CLASS lic with (nolock) on lic.LS_INTERACTION_CLASS_ID = ll.LS_INTERACTION_CLASS_ID
				left join rls.v_ACTMATTERS am1 with (nolock) on am1.ACTMATTERS_ID = ll.ACTMATTERS_G1ID
				left join rls.v_ACTMATTERS am2 with (nolock) on am2.ACTMATTERS_ID = ll.ACTMATTERS_G2ID
				left join rls.v_TRADENAMES tn1 with (nolock) on tn1.TRADENAMES_ID = ll.TRADENAMES_G1ID
				left join rls.v_TRADENAMES tn2 with (nolock) on tn2.TRADENAMES_ID = ll.TRADENAMES_G2ID
				left join rls.v_CLSPHARMAGROUP cpg1 with (nolock) on cpg1.CLSPHARMAGROUP_ID = ll.CLSPHARMAGROUP_G1ID
				left join rls.v_CLSPHARMAGROUP cpg2 with (nolock) on cpg2.CLSPHARMAGROUP_ID = ll.CLSPHARMAGROUP_G2ID
				left join rls.v_FTGGRLS fg1 with (nolock) on fg1.FTGGRLS_ID = ll.FTGGRLS_G1ID
				left join rls.v_FTGGRLS fg2 with (nolock) on fg2.FTGGRLS_ID = ll.FTGGRLS_G2ID
				-- end from
			where
				-- where
				(1=1)
				{$filter}
				-- end where
			order by
				-- order by
				ll.LS_LINK_ID
				-- end order by
		", $queryParams, $data['start'], $data['limit'], true);
	}

	/**
	 * Загрузка формы редактирования взаимодействия
	 */
	function loadLsLinkEditForm($data) {
		return $this->queryResult("
			select
				ll.LS_LINK_ID,
				COALESCE('A' + cast(ll.ACTMATTERS_G1ID as varchar), 'T' + cast(ll.TRADENAMES_G1ID as varchar), 'C' + cast(ll.CLSPHARMAGROUP_G1ID as varchar), 'F' + cast(ll.FTGGRLS_G1ID as varchar)) as LS_GROUP1,
				COALESCE('A' + cast(ll.ACTMATTERS_G2ID as varchar), 'T' + cast(ll.TRADENAMES_G2ID as varchar), 'C' + cast(ll.CLSPHARMAGROUP_G2ID as varchar), 'F' + cast(ll.FTGGRLS_G2ID as varchar)) as LS_GROUP2,
				ll.LS_FT_TYPE_ID,
				ll.LS_INFLUENCE_TYPE_ID,
				ll.LS_EFFECT_ID,
				ll.LS_INTERACTION_CLASS_ID,
				ll.DESCRIPTION,
				ll.RECOMMENDATION,
				ll.BREAKTIME
			from
				rls.v_LS_LINK ll with (nolock)
			where
				ll.LS_LINK_ID = :LS_LINK_ID
		", [
			'LS_LINK_ID' => $data['LS_LINK_ID']
		]);
	}

	/**
	 * Сохранение взаимодействия
	 */
	function saveLsLink($data) {
		$proc = "p_LS_LINK_ins";
		$filter = "";
		if (!empty($data['LS_LINK_ID'])) {
			$proc = "p_LS_LINK_upd";
			$filter .= " and LS_LINK_ID <> :LS_LINK_ID";
		} else {
			$data['LS_LINK_ID'] = null;
		}

		$resp_check = $this->queryResult("
			select top 1
				LS_LINK_ID
			from
				rls.v_LS_LINK (nolock)
			where
				COALESCE(ACTMATTERS_G1ID, TRADENAMES_G1ID, CLSPHARMAGROUP_G1ID, FTGGRLS_G1ID) = COALESCE(:ACTMATTERS_G1ID, :TRADENAMES_G1ID, :CLSPHARMAGROUP_G1ID, :FTGGRLS_G1ID)
				and COALESCE(ACTMATTERS_G2ID, TRADENAMES_G2ID, CLSPHARMAGROUP_G2ID, FTGGRLS_G2ID) = COALESCE(:ACTMATTERS_G2ID, :TRADENAMES_G2ID, :CLSPHARMAGROUP_G2ID, :FTGGRLS_G2ID)
				{$filter}
		", $data);

		if (!empty($resp_check[0]['LS_LINK_ID'])) {
			return ['Error_Msg' => 'Сохранение данных не может быть выполнено, т.к. данные о взаимодействии таких групп ЛС уже внесены в справочник'];
		}

		if (!empty($data['PREP_ID'])) {
			$resp_check = $this->queryResult("
				select top 1
					p.PREP_ID
				from
					rls.v_PREP p with (nolock)
					left join rls.PREP_ACTMATTERS pa (nolock) on pa.PREPID = :PREP_ID and pa.MATTERID = :ACTMATTERS_G1ID
					left join rls.v_PREP_PHARMAGROUP pp (nolock) on pp.PREPID = :PREP_ID and pp.UNIQID = :CLSPHARMAGROUP_G1ID
					left join rls.v_PREP_FTGGRLS pf (nolock) on pf.PREP_ID = :PREP_ID and pf.FTGGRLS_ID = :FTGGRLS_G1ID
					left join rls.PREP_ACTMATTERS pa2 (nolock) on pa2.PREPID = :PREP_ID and pa2.MATTERID = :ACTMATTERS_G2ID
					left join rls.v_PREP_PHARMAGROUP pp2 (nolock) on pp2.PREPID = :PREP_ID and pp2.UNIQID = :CLSPHARMAGROUP_G2ID
					left join rls.v_PREP_FTGGRLS pf2 (nolock) on pf2.PREP_ID = :PREP_ID and pf2.FTGGRLS_ID = :FTGGRLS_G2ID
				where
					p.PREP_ID = :PREP_ID
					and (
						p.TRADENAMEID = :TRADENAMES_G1ID
						OR p.TRADENAMEID = :TRADENAMES_G2ID
						OR COALESCE(PA.PREPID, pp.PREPID, pf.PREP_ID, PA2.PREPID, pp2.PREPID, pf2.PREP_ID) IS NOT NULL
					)
			", $data);

			if (empty($resp_check[0]['PREP_ID'])) {
				return ['Error_Msg' => 'Сохранение не может быть выполнено, так как указаны данные о взаимодействии Групп ЛС, в которые не входит выбранный ЛП'];
			}
		}

		$resp_save = $this->queryResult("
			declare
				@LS_LINK_ID bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @LS_LINK_ID = :LS_LINK_ID;
			
			exec rls.{$proc}
				@LS_LINK_ID = @LS_LINK_ID output,
				@ACTMATTERS_G1ID = :ACTMATTERS_G1ID,
				@TRADENAMES_G1ID = :TRADENAMES_G1ID,
				@CLSPHARMAGROUP_G1ID = :CLSPHARMAGROUP_G1ID,
				@FTGGRLS_G1ID = :FTGGRLS_G1ID,
				@ACTMATTERS_G2ID = :ACTMATTERS_G2ID,
				@TRADENAMES_G2ID = :TRADENAMES_G2ID,
				@CLSPHARMAGROUP_G2ID = :CLSPHARMAGROUP_G2ID,
				@FTGGRLS_G2ID = :FTGGRLS_G2ID,
				@LS_FT_TYPE_ID = :LS_FT_TYPE_ID,
				@LS_INFLUENCE_TYPE_ID = :LS_INFLUENCE_TYPE_ID,
				@LS_EFFECT_ID = :LS_EFFECT_ID,
				@LS_INTERACTION_CLASS_ID = :LS_INTERACTION_CLASS_ID,
				@DESCRIPTION = :DESCRIPTION,
				@RECOMMENDATION = :RECOMMENDATION,
				@BREAKTIME = :BREAKTIME,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @LS_LINK_ID as LS_LINK_ID, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", $data);

		if (!empty($resp_save[0]['LS_LINK_ID']) && !empty($data['PREP_ID'])) {
			$this->linkLsLink([
				'LS_LINK_ID' => $resp_save[0]['LS_LINK_ID'],
				'PREP_ID' => $data['PREP_ID'],
				'pmUser_id' => $data['pmUser_id']
			]);
		}

		return $resp_save;
	}

	/**
	 * Удаление взаимодействия
	 */
	function deleteLsLink($data) {
		// ищем связи с препаратами
		$resp_pl = $this->queryResult("
			select top 2
				PREP_LS_ID
			from
				rls.v_PREP_LS with (nolock)
			where
				LS_LINK_ID = :LS_LINK_ID
		", [
			'LS_LINK_ID' => $data['LS_LINK_ID']
		]);

		if (empty($data['ignorePrepLs']) && count($resp_pl) > 1) {
			return ['Error_Msg' => 'YesNo', 'Error_Code' => '100', 'Alert_Msg' => 'Выбранная запись о взаимодействии ЛС связана с несколькими ЛП. Удалить данные о взаимодействии для всех ЛП?'];
		}

		if (count($resp_pl) > 0) {
			$this->unlinkLsLink([
				'LS_LINK_ID' => $data['LS_LINK_ID']
			]);
		}

		return $this->queryResult("
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
							
			exec rls.p_LS_LINK_del
				@LS_LINK_ID = :LS_LINK_ID,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", [
			'LS_LINK_ID' => $data['LS_LINK_ID']
		]);
	}

	/**
	 * Получение описания взаимодействия
	 */
	function getLsLinkInfo($data) {
		$prepList = "";

		$resp = $this->queryResult("
			select
				rc.REGNUM as Drug_RegNum,
				a.RUSNAME as Drgu_MnnName,
				tn.NAME as Drug_Name,
				cdf.NAME as Drug_Form,
				cast(P.DFMASS as varchar) + ' ' + MU.SHORTNAME as Drug_Dose
			from
				rls.v_PREP_LS pl with (nolock)
				inner join rls.v_PREP p with (nolock) on p.PREP_ID = pl.PREP_ID
				left join rls.v_TRADENAMES tn with (nolock) on tn.TRADENAMES_ID = p.TRADENAMEID
				left join rls.v_REGCERT rc with (nolock) on rc.REGCERT_ID = p.REGCERTID
				left join rls.v_CLSDRUGFORMS cdf with (nolock) on cdf.CLSDRUGFORMS_ID = p.DRUGFORMID
				left join rls.v_MassUnits MU with (nolock) on MU.MASSUNITS_ID = P.DFMASSID
				outer apply (
					select top 1
						a.RUSNAME
					FROM
						rls.PREP_ACTMATTERS pa with (nolock)
						inner join rls.v_ACTMATTERS a with (nolock) on a.ACTMATTERS_ID = pa.MATTERID
					where
						pa.PREPID = pl.PREP_ID
				) a
			where
				pl.LS_LINK_ID = :LS_LINK_ID
		", [
			'LS_LINK_ID' => $data['LS_LINK_ID']
		]);

		foreach($resp as $respone) {
			$prepList .= "<br>" . implode(', ', $respone);
		}

		return [
			'Error_Msg' => '',
			'PREPLIST' => $prepList
		];
	}

	/**
	 * Связь с взаимодействием
	 */
	function linkLsLink($data) {
		$resp = $this->queryResult("
			select top 1
				PREP_LS_ID
			from
				rls.v_PREP_LS with (nolock)
			where
				PREP_ID = :PREP_ID
				and LS_LINK_ID = :LS_LINK_ID
		", [
			'PREP_ID' => $data['PREP_ID'],
			'LS_LINK_ID' => $data['LS_LINK_ID']
		]);

		if (!empty($resp[0]['PREP_LS_ID'])) {
			return ['Error_Msg' => ''];
		}

		return $this->queryResult("
			declare
				@PREP_LS_ID bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
							
			exec rls.p_PREP_LS_ins
				@PREP_LS_ID = @PREP_LS_ID output,
				@PREP_ID = :PREP_ID,
				@LS_LINK_ID = :LS_LINK_ID,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @PREP_LS_ID as PREP_LS_ID, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", [
			'PREP_ID' => $data['PREP_ID'],
			'LS_LINK_ID' => $data['LS_LINK_ID'],
			'pmUser_id' => $data['pmUser_id']
		]);
	}

	/**
	 * Удаление связи со взаимодействием
	 */
	function unlinkLsLink($data) {
		$filter = "";
		$queryParams = [
			'LS_LINK_ID' => $data['LS_LINK_ID']
		];
		if (!empty($data['PREP_ID'])) {
			$filter .= " and PREP_ID = :PREP_ID";
			$queryParams['PREP_ID'] = $data['PREP_ID'];
		}
		$resp = $this->queryResult("
			select
				PREP_LS_ID
			from
				rls.v_PREP_LS with (nolock)
			where
				LS_LINK_ID = :LS_LINK_ID
				{$filter}
		", $queryParams);

		foreach($resp as $respone) {
			$resp_del = $this->queryResult("
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);
								
				exec rls.p_PREP_LS_del
					@PREP_LS_ID = :PREP_LS_ID,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
	
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			", [
				'PREP_LS_ID' => $respone['PREP_LS_ID']
			]);

			if (!empty($resp_del[0]['Error_Msg'])) {
				return $resp_del;
			}
		}

		return ['Error_Msg' => ''];
	}

	/**
	 *
	 */
	function loadLsGroupCombo($data) {
		$filter = "";
		$queryParams = [];
		if (!empty($data['query'])) {
			$filter .= " and LS_GROUP_NAME like :query + '%'";
			$queryParams['query'] = $data['query'];
		}
		if (!empty($data['LS_GROUP_ID'])) {
			$filter .= " and LS_GROUP_ID = :LS_GROUP_ID";
			$queryParams['LS_GROUP_ID'] = $data['LS_GROUP_ID'];
		}
		return $this->queryResult("
			WITH DATA as (
				SELECT
					'A' + CAST(ACTMATTERS_ID AS VARCHAR) AS LS_GROUP_ID,
					RUSNAME AS LS_GROUP_NAME,
					ACTMATTERS_ID,
					NULL AS TRADENAMES_ID,
					NULL AS CLSPHARMAGROUP_ID,
					NULL AS FTGGRLS_ID
				FROM rls.v_ACTMATTERS WITH (NOLOCK)
				
				UNION ALL

				SELECT
					'T' + CAST(TRADENAMES_ID AS VARCHAR) AS LS_GROUP_ID,
					NAME AS LS_GROUP_NAME,
					NULL AS ACTMATTERS_ID,
					TRADENAMES_ID,
					NULL AS CLSPHARMAGROUP_ID,
					NULL AS FTGGRLS_ID
				FROM rls.v_TRADENAMES WITH (NOLOCK)
			)
			
			SELECT top 100
				*
			FROM
				DATA with (nolock)
			WHERE
				(1=1)
				{$filter}
		", $queryParams);

		/*
		 * чтобы заводить взаимодействия между фарм. группами нужно добавить в запрос выше следующий кусок
		 * пока убрали, т.к. проверка при назначении идет только между действующми веществами и торг. наименованиями
				UNION ALL

				SELECT
					'C' + CAST(CLSPHARMAGROUP_ID AS VARCHAR) AS LS_GROUP_ID,
					NAME AS LS_GROUP_NAME,
					NULL AS ACTMATTERS_ID,
					NULL AS TRADENAMES_ID,
					CLSPHARMAGROUP_ID,
					NULL AS FTGGRLS_ID
				FROM rls.CLSPHARMAGROUP

				UNION ALL

				SELECT
					'F' + CAST(FTGGRLS_ID AS VARCHAR) AS LS_GROUP_ID,
					NAME AS LS_GROUP_NAME,
					NULL AS ACTMATTERS_ID,
					NULL AS TRADENAMES_ID,
					NULL AS CLSPHARMAGROUP_ID,
					FTGGRLS_ID
				FROM rls.FTGGRLS
		 */
	}
}