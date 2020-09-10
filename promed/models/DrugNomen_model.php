<?php defined('BASEPATH') or die ('No direct script access allowed');

class DrugNomen_model extends swModel {
	/**
	 *  Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	*  Читает дерево комплексных услуг
	*/
	function loadPrepClassTree($data) {
		$filter = " and PrepClass_pid IS NULL";
		
		if (!empty($data['PrepClass_pid'])) {
			$filter = " and PrepClass_pid = :PrepClass_pid";
		}

		if (!empty($data['PrepClass_Code'])) {
			$filter .= " and pc.PrepClass_Code = :PrepClass_Code";
		}
		
		$query = "
			select
				pc.PrepClass_id as id,
				pc.PrepClass_Code as code,
				pc.PrepClass_Name as name,
				'PrepClass' as object,
				case when pccount.cnt = 0 then 1 else 0 end as leaf
			from
				rls.v_PrepClass pc with (nolock)
				outer apply (
					select
						count(PrepClass_id) as cnt
					from
						rls.v_PrepClass with (nolock)
					where
						PrepClass_pid = pc.PrepClass_id
				) pccount
			where
				(1=1)
				{$filter}
			order by
				leaf,
				pc.PrepClass_Name
		";

		//echo getDebugSql($query, $data); exit();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Формирование join'ов для грида DrugNomen
	 */
	function getDrugNomenGridJoin($data) {
		$join = '';

		if (!empty($data['CLSPHARMAGROUP_ID']) || !empty($data['CLSATC_ID'])) {
			if (!empty($data['CLSPHARMAGROUP_ID'])) {
				$join .= ' inner join rls.v_PREP_PHARMAGROUP PP with(nolock) on PP.PREPID = D.DrugPrep_id';
			}
			if (!empty($data['CLSATC_ID'])) {
				$join .= ' inner join rls.v_PREP_ATC PA with(nolock) on PA.PREPID = D.DrugPrep_id';
			}
		}
		if (!empty($data['CLS_MZ_PHGROUP_ID'])) {
			$join .= ' inner join rls.TRADENAMES_DRUGFORMS TD with(nolock) on TD.TRADENAMEID = DTC.TRADENAMES_id and TD.DRUGFORMID = DCM.CLSDRUGFORMS_ID';
		}

		return $join;
	}

	/**
	 * Формирование фильтров для загрузки грида DrugNomen
	 */
	function getDrugNomenGridFilter($data) {
		$filter = array();
		$filter_str = '';

		if (!empty($data['DrugNomenOrgLink_Code'])) {
			$filter[] = 'DNOL.DrugNomenOrgLink_Code = :DrugNomenOrgLink_Code';
		}

		if (!empty($data['SprType_Code']) && in_array($data['SprType_Code'], array('org_nom', 'llo_nom'))) {
            $filter[] = 'DNOL.DrugNomenOrgLink_id is not null';
		}

		if (!empty($data['DrugComplexMnnCode_Code'])) {
			$filter[] = 'DCMC.DrugComplexMnnCode_Code = :DrugComplexMnnCode_Code';
		}

		if (!empty($data['DrugPrepFasCode_Code'])) {
			$filter[] = 'DPFC.DrugPrepFasCode_Code = :DrugPrepFasCode_Code';
		}
		//------
		if (!empty($data['Actmatters_id'])) {
			$filter[] = 'DMC.ACTMATTERS_id = :Actmatters_id';
		}

		if (!empty($data['Tradenames_id'])) {
			$filter[] = 'DTC.TRADENAMES_id = :Tradenames_id';
		}

		if (!empty($data['Clsdrugforms_id'])) {
			$filter[] = 'DCM.CLSDRUGFORMS_ID like :Clsdrugforms_id';
		}
		//------
		if (!empty($data['RlsActmatters_RusName'])) {
			$filter[] = "AM.RUSNAME like :RlsActmatters_RusName+'%'";
		}

		if (!empty($data['RlsTorg_Name'])) {
			$filter[] = "TN.NAME like :RlsTorg_Name+'%'";
		}

		if (!empty($data['RlsClsdrugforms_Name'])) {
			$filter[] = "CDF.FULLNAME like :RlsClsdrugforms_Name+'%'";
		}
		//------
		if (!empty($data['CLSATC_ID'])) {
			$filter[] = 'PA.UNIQID = :CLSATC_ID';
		}

		if (!empty($data['STRONGGROUPS_ID'])) {
			$filter[] = 'AM.STRONGGROUPID = :STRONGGROUPS_ID';
		}

		if (!empty($data['NARCOGROUPS_ID'])) {
			$filter[] = 'AM.NARCOGROUPID = :NARCOGROUPS_ID';
		}

		if (!empty($data['FIRMS_ID'])) {
			$filter[] = 'P.FIRMID = :FIRMS_ID';
		}

		if (!empty($data['COUNTRIES_ID'])) {
			$filter[] = 'F.COUNTID = :COUNTRIES_ID';
		}

		if (!empty($data['CLSPHARMAGROUP_ID'])) {
			//Получение списка потомков для фильтрации по ним
			$query = "
				with Rec(CLSPHARMAGROUP_ID)
				as
				(
					select t.CLSPHARMAGROUP_ID
					from rls.v_CLSPHARMAGROUP t with(nolock)
					where
						t.CLSPHARMAGROUP_ID = :CLSPHARMAGROUP_ID
					union all
					select t.CLSPHARMAGROUP_ID
					from rls.v_CLSPHARMAGROUP t with(nolock)
						join Rec R on t.PARENTID = R.CLSPHARMAGROUP_ID
				)
				select
					R.CLSPHARMAGROUP_ID
				from Rec R with(nolock)
			";
			$result = $this->db->query($query,array(
				'CLSPHARMAGROUP_ID' => $data['CLSPHARMAGROUP_ID']
			));
			if (is_object($result)) {
				$res_arr = $result->result('array');
				if (is_array($res_arr) && !empty($res_arr)) {
					$ph_gr_arr = array();
					foreach($res_arr as $row) {
						$ph_gr_arr[] = $row['CLSPHARMAGROUP_ID'];
					}
					$ph_gr_str = empty($ph_gr_arr)?'null':implode(',', $ph_gr_arr);
					$filter[] = "PP.UNIQID in ({$ph_gr_str})";
				}
			}
		}

		if (!empty($data['CLS_MZ_PHGROUP_ID'])) {
			//Получение списка потомков для фильтрации по ним
			$query = "
				with Rec(CLS_MZ_PHGROUP_ID)
				as
				(
					select t.CLS_MZ_PHGROUP_ID
					from rls.v_CLS_MZ_PHGROUP t with(nolock)
					where
						t.CLS_MZ_PHGROUP_ID = :CLS_MZ_PHGROUP_ID
					union all
					select t.CLS_MZ_PHGROUP_ID
					from rls.v_CLS_MZ_PHGROUP t with(nolock)
						join Rec R on t.PARENTID = R.CLS_MZ_PHGROUP_ID
				)
				select
					R.CLS_MZ_PHGROUP_ID
				from Rec R with(nolock)
			";
			$result = $this->db->query($query,array(
				'CLS_MZ_PHGROUP_ID' => $data['CLS_MZ_PHGROUP_ID']
			));
			if (is_object($result)) {
				$res_arr = $result->result('array');
				if (is_array($res_arr) && !empty($res_arr)) {
					$mz_pg_arr = array();
					foreach($res_arr as $row) {
						$mz_pg_arr[] = $row['CLS_MZ_PHGROUP_ID'];
					}
					$mz_pg_str = empty($mz_pg_arr)?'null':implode(',', $mz_pg_arr);
					$filter[] = "TD.MZ_PHGR_ID in ({$mz_pg_str})";
				}
			}
		}

        if (!empty($data['no_rmz']) && $data['no_rmz'] == 1) {
            $filter[] = 'DRMZ.DrugRMZ_id is null';
        }

        //если в результат велючены позиции без идентификатора медикамента, то для записей без медикамента отключаем все фильтры кроме фильтра по коду
		if (count($filter) > 0 && !empty($data['rls_drug_link']) && in_array($data['rls_drug_link'], array('all', 'no'))) {
			if ($data['rls_drug_link'] == 'all') { //если вытаскиваем все виды записей то обьединяем фильтры
				$sub_filter = '(DN.Drug_id is null or ('.join(' and ', $filter).'))';
				$filter = array();
				$filter[] = $sub_filter;
			} else { //иначе просто сбрасываем
				$filter = array();
			}
		}

		if (!empty($data['DrugNomen_Code'])) {
			$filter[] = 'DN.DrugNomen_Code = :DrugNomen_Code';
		}

        if (!empty($data['rls_drug_link'])) {
        	if ($data['rls_drug_link'] == 'yes') {
				$filter[] = 'DN.Drug_id is not null';
			}
			if ($data['rls_drug_link'] == 'no') {
				$filter[] = 'DN.Drug_id is null';
			}
        } else {
			$filter[] = 'DN.Drug_id is not null';
		}

        if (count($filter) > 0) {
			$filter_str = ' and '.join(' and ', $filter);
		}

		return $filter_str;
	}

	/**
	 *  Получение списка нормативов
     */
	function loadDrugNomenGrid($data) {
		$params = $data;
		$join = $this->getDrugNomenGridJoin($params);
		$filter = $this->getDrugNomenGridFilter($params);

        $prepclass = array();
        $prepclass[] = $data['PrepClass_id'];

        //получение дочерних классов
        $query = "
            with PrepClassTree (id, pid)
            as (
                select
                    pc.PrepClass_id,
                    pc.PrepClass_pid
                from
                    rls.v_PrepClass pc with (nolock)
                where
                    isnull(pc.PrepClass_pid, 0) = isnull(:PrepClass_id, 0)
                union all
                select
                    pc.PrepClass_id,
                    pc.PrepClass_pid
                from
                    rls.v_PrepClass pc with (nolock)
                    inner join PrepClassTree pct on pc.PrepClass_pid = pct.id
            )
            select
                pct.id
            from
                PrepClassTree pct with (nolock);
        ";
        $result = $this->db->query($query, $data);
        if (is_object($result)) {
            $arr = $result->result('array');
            for($i = 0; $i < count($arr); $i++) {
                $prepclass[] = $arr[$i]['id'];
            }
        }
        $prepclass_filter = "DN.PrepClass_id in (".join(',', $prepclass).")";

		$query = "
			select
				-- select
                DN.DrugNomen_id,
                DN.DrugNomen_Code,
                DN.DrugNomen_Name,
                DNOL.DrugNomenOrgLink_Code,
                DPFC.DrugPrepFasCode_Code,
                DN.PrepClass_id,
                --DN.DrugNomen_Nick,
                --D.Drug_Name,
                D.Drug_Ean,
                DMC.DrugMnnCode_Code,
                DTC.DrugTorgCode_Code,
                DCMC.DrugComplexMnnCode_Code,
                DRMZ.DrugRPN_id,
                DCM.DrugComplexMnn_RusName,
                AM.RUSNAME as ActMatters_RusName, --МНН
				TN.NAME as RlsTorg_Name, --Лекарственный препарат -- Торговое наименование
				CDF.NAME as RlsClsdrugforms_Name, --Форма выпуска -- лекарственная форма
				Dose.Value as Drug_Dose, --Дозировка
				Fas.Value as Drug_Fas, --Фасовка
                Pack.Value as DrugPack_Name, --Упаковка
				RC.REGNUM as Reg_Num, --№ РУ
				RCEFF.FULLNAME as Reg_Firm,
				RCEFFC.NAME as Reg_Country,
				(convert(varchar, RC.REGDATE, 104)+isnull(' - '+convert(varchar, RC.ENDDATE, 104), '')) as Reg_Period,
				convert(varchar, RC.Reregdate, 104) as Reg_ReRegDate,
				DCMC.DrugComplexMnnCode_DosKurs
				-- end select
			from
				-- from
				rls.v_DrugNomen DN with(nolock)
				left join rls.v_Drug D with(nolock) on D.Drug_id = DN.Drug_id
				left join rls.v_DrugComplexMnnCode DCMC with(nolock) on DCMC.DrugComplexMnnCode_id = DN.DrugComplexMnnCode_id
				left join rls.v_DrugComplexMnn DCM with(nolock) on DCM.DrugComplexMnn_id = DCMC.DrugComplexMnn_id
				left join rls.v_DrugMnnCode DMC with(nolock) on DMC.DrugMnnCode_id = DN.DrugMnnCode_id
				left join rls.v_ACTMATTERS AM with(nolock) on AM.ACTMATTERS_ID = DMC.ACTMATTERS_id
				left join rls.v_DrugTorgCode DTC with(nolock) on DTC.DrugTorgCode_id = DN.DrugTorgCode_id
				left join rls.v_TRADENAMES TN with(nolock) on TN.TRADENAMES_ID = DTC.TRADENAMES_id
				left join rls.CLSDRUGFORMS CDF with(nolock) on CDF.CLSDRUGFORMS_ID = DCM.CLSDRUGFORMS_ID
				left join rls.v_Prep P with(nolock) on P.Prep_id = D.DrugPrep_id
				left join rls.v_FIRMS F with(nolock) on F.FIRMS_ID = P.FIRMID
				left join rls.v_FIRMNAMES FN with(nolock) on FN.FIRMNAMES_ID = F.NAMEID
				left join rls.v_REGCERT RC with (nolock) on RC.REGCERT_ID = P.REGCERTID
				left join rls.REGCERT_EXTRAFIRMS RCEF with (nolock) on RCEF.CERTID = RC.REGCERT_ID
				left join rls.v_FIRMS RCEFF with(nolock) on RCEFF.FIRMS_ID = RCEF.FIRMID
                left join rls.v_COUNTRIES RCEFFC with(nolock) on RCEFFC.COUNTRIES_ID = RCEFF.COUNTID
				left join rls.MASSUNITS MU with (nolock) on MU.MASSUNITS_ID = p.DFMASSID
				left join rls.CONCENUNITS CU with (nolock) on CU.CONCENUNITS_ID = p.DFCONCID
				left join rls.ACTUNITS AU with (nolock) on AU.ACTUNITS_ID = p.DFACTID
				left join rls.SIZEUNITS SU with (nolock) on SU.SIZEUNITS_ID = p.DFSIZEID
				outer apply (
					select top 1
					    DrugRMZ_id,
						DrugRPN_id
					from
						rls.v_DrugRMZ with (nolock)
					where
						v_DrugRMZ.Drug_id = D.Drug_id
					order by
						DrugRMZ_id
				) DRMZ
				outer apply (
				    select top 1
				        DrugNomenOrgLink_id,
				        DrugNomenOrgLink_Code
				    from
				        rls.v_DrugNomenOrgLink with (nolock)
				    where
				        v_DrugNomenOrgLink.DrugNomen_id = DN.DrugNomen_id and (
                            :DrugNomenOrgLink_Org_id is null or
                            v_DrugNomenOrgLink.Org_id = :DrugNomenOrgLink_Org_id
				        )
				    order by
				        DrugNomenOrgLink_id
				) DNOL
				outer apply (
				    select top 1
				        DrugPrepFasCode_Code
				    from
				        rls.v_DrugPrepFasCode with (nolock)
				    where
				        v_DrugPrepFasCode.DrugPrepFas_id = D.DrugPrepFas_id and (
                            isnull(v_DrugPrepFasCode.Org_id, 0) = isnull(:DrugNomenOrgLink_Org_id, 0)
				        )
				    order by
				        DrugNomenOrgLink_id
				) DPFC
				outer apply (
					select coalesce(
						cast(cast(P.DFMASS as float) as varchar)+' '+MU.SHORTNAME,
						cast(cast(p.DFCONC as float) as varchar)+' '+CU.SHORTNAME,
						cast(P.DFACT as varchar)+' '+AU.SHORTNAME,
						cast(P.DFSIZE as varchar)+' '+SU.SHORTNAME
					) as Value
				) Dose
				outer apply(
					select (
						(case when D.Drug_Fas is not null then cast(D.Drug_Fas as varchar)+' доз' else '' end)+
						(case when D.Drug_Fas is not null and coalesce(D.Drug_Volume,D.Drug_Mass) is not null then ', ' else '' end)+
						(case when coalesce(D.Drug_Volume,D.Drug_Mass) is not null then coalesce(D.Drug_Volume,D.Drug_Mass) else '' end)
					) as Value
				) Fas
				outer apply(
					select top 1
					    (
					        isnull(cast(N.DRUGSINPPACK as varchar)+'шт., '+DP1.FULLNAME+' ', '') +
                            isnull('('+cast(N.PPACKINUPACK as varchar)+'), '+DP2.FULLNAME+' ', '') +
                            isnull('('+cast(N.UPACKINSPACK as varchar)+'), '+DP3.FULLNAME+' ', '')
					    ) as Value
					from
					    rls.v_NOMEN N with(nolock)
                        left join rls.v_DRUGPACK DP1 with(nolock) on DP1.DRUGPACK_ID = N.PPACKID
                        left join rls.v_DRUGPACK DP2 with(nolock) on DP2.DRUGPACK_ID = N.UPACKID
                        left join rls.v_DRUGPACK DP3 with(nolock) on DP3.DRUGPACK_ID = N.SPACKID
                    where
                        N.PREPID = P.Prep_id
                    order by
                        N.NOMEN_ID
				) Pack
				{$join}
				-- end from
			where
				-- where
				{$prepclass_filter}
				{$filter}
				-- end where
			order by
				-- order by
				DN.DrugNomen_Code
				-- end order by
		";

		//echo getDebugSQL($query,$params);exit;
		//print_r($params);exit;
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);
		
		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	 *  Получение комбо списка медикаментов (для карты закрытия вызова СМП)
	 */
	function loadDrugNomenCmpDrugUsageCombo($data) {

		$prepclass = array();

		$data['PrepClass_id'] = 1; // только медикаменты
		$data['start'] = 0;
		$data['limit'] = 100;
		//$data['DrugNomenOrgLink_Org_id'] = 68320032807;
		//$data['DrugNomenOrgLink_Org_id'] = $data['Lpu_id'];

		$params = $data;
		$prepclass[] = $data['PrepClass_id'];

		//получение дочерних классов
		$query = "
            with PrepClassTree (id, pid)
            as (
                select
                    pc.PrepClass_id,
                    pc.PrepClass_pid
                from
                    rls.v_PrepClass pc with (nolock)
                where
                    isnull(pc.PrepClass_pid, 0) = isnull(:PrepClass_id, 0)
                union all
                select
                    pc.PrepClass_id,
                    pc.PrepClass_pid
                from
                    rls.v_PrepClass pc with (nolock)
                    inner join PrepClassTree pct on pc.PrepClass_pid = pct.id
            )
            select
                pct.id
            from
                PrepClassTree pct with (nolock);
        ";

		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$arr = $result->result('array');
			for($i = 0; $i < count($arr); $i++) {
				$prepclass[] = $arr[$i]['id'];
			}
		}

		$prepclass_filter = "DN.PrepClass_id in (".join(',', $prepclass).")";

		$filters = '';

		if (!empty($data['query'])) {
			$filters .= " and DN.DrugNomen_Name like :query";
			$params['query'] = "%".$data['query'] . "%";
		}

		$query = "
			select
				-- select
                DN.DrugNomen_id,
                DN.Drug_id,
                DN.DrugNomen_Code,
                DN.DrugNomen_Name,
                --DCM.DrugComplexMnn_RusName,
				--TN.NAME as RlsTorg_Name,
				DN.GoodsUnit_id,
				GU.GoodsUnit_Name
				-- end select
			from
				-- from
				rls.v_DrugNomen DN with(nolock)
				left join v_GoodsUnit GU with(nolock) on GU.GoodsUnit_id = DN.GoodsUnit_id
				--left join rls.v_DrugComplexMnnCode DCMC with(nolock) on DCMC.DrugComplexMnnCode_id = DN.DrugComplexMnnCode_id
				--left join rls.v_DrugComplexMnn DCM with(nolock) on DCM.DrugComplexMnn_id = DCMC.DrugComplexMnn_id
				--left join rls.v_DrugTorgCode DTC with(nolock) on DTC.DrugTorgCode_id = DN.DrugTorgCode_id
				--left join rls.v_TRADENAMES TN with(nolock) on TN.TRADENAMES_ID = DTC.TRADENAMES_id
				--left join rls.v_DrugNomenOrgLink DNOL with(nolock) on DNOL.DrugNomen_id = DN.DrugNomen_id
				outer apply (
				    select top 1
				        DrugNomenOrgLink_id,
				        DrugNomenOrgLink_Code
				    from
				        rls.v_DrugNomenOrgLink with (nolock)
				        left join v_Lpu L with(nolock) on v_DrugNomenOrgLink.Org_id = L.Org_id
				    where
				        v_DrugNomenOrgLink.DrugNomen_id = DN.DrugNomen_id
				        and L.Lpu_id = :Lpu_id
				    order by
				        DrugNomenOrgLink_id
				) DNOL
				-- end from
			where
				-- where
				(1=1)
				and DNOL.DrugNomenOrgLink_id is not null
				and DN.GoodsUnit_id is not null
				and {$prepclass_filter}
				{$filters}
				-- end where
			order by
				-- order by
				DN.DrugNomen_Code
				-- end order by
		";

		//echo getDebugSQL($query,$params);exit;
		//print_r($params);exit;
		$result = $this->db->query($query, $params);

		if (is_object($result)) {

			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Загрузка списка дозировок (nsi)
	 */
	function loadNsiDrugDoseCombo($data) {
        $where = array();
        $params = array();

		if (!empty($data['DrugDose_id'])) {
			$where[] = "dd.DrugDose_id = :DrugDose_id";
			$params['DrugDose_id'] = $data['DrugDose_id'];
		} else {
            if (!empty($data['query'])) {
				$where[] = "(dd.DrugDose_Code like :query or dd.DrugDose_Name like :query)";
                $params['query'] = "".$data['query']."%";
            }
        }

        $where_clause = implode(" and ", $where);
        if (strlen($where_clause)) {
            $where_clause = "
				where
					{$where_clause}
			";
        }

		$query = "
			select top 100
                dd.DrugDose_id,
                (isnull(dd.DrugDose_Code+' - ', '')+dd.DrugDose_Name) as DrugDose_Name
			from
                nsi.v_DrugDose dd with (nolock)
            $where_clause
            order by
                dd.DrugDose_id
		";
		$result = $this->queryResult($query, $params);

    	return $result;
	}

	/**
	 *  Загрузка списка количеств доз в упаковках (nsi)
	 */
	function loadNsiDrugKolDoseCombo($data) {
        $where = array();
        $params = array();

		if (!empty($data['DrugKolDose_id'])) {
			$where[] = "dkd.DrugKolDose_id = :DrugKolDose_id";
			$params['DrugKolDose_id'] = $data['DrugKolDose_id'];
		} else {
            if (!empty($data['query'])) {
				$where[] = "(dkd.DrugKolDose_Code like :query or dkd.DrugKolDose_KolDose like :query)";
                $params['query'] = "".$data['query']."%";
            }
        }

        $where_clause = implode(" and ", $where);
        if (strlen($where_clause)) {
            $where_clause = "
				where
					{$where_clause}
			";
        }

		$query = "
			select top 100
                dkd.DrugKolDose_id,
                (isnull(dkd.DrugKolDose_Code+' - ', '')+dkd.DrugKolDose_KolDose) as DrugKolDose_Name
			from
                nsi.v_DrugKolDose dkd with (nolock)
            $where_clause
            order by
                dkd.DrugKolDose_id
		";
		$result = $this->queryResult($query, $params);

    	return $result;
	}

	/**
	 *  Загрузка формы редактирования
     */
	function loadDrugNomenEditForm($data) {
		$query = "
			select
				DN.DrugNomen_id,
				DN.Drug_id,
				DN.DrugNomen_Code,
				DN.DrugNomen_Nick,
				DMC.DrugMnnCode_id,
				DMC.DrugMnnCode_Code,
				DTC.DrugTorgCode_id,
				DTC.DrugTorgCode_Code,
				DCMC.DrugComplexMnnCode_id,
				isnull(Drug.DrugComplexMnn_id,0) as DrugComplexMnn_id,
				DCMC.DrugComplexMnnCode_Code,
				DN.Okpd_id
			from
				rls.v_DrugNomen DN (nolock)
				left join rls.v_DrugMnnCode DMC with(nolock) on DMC.DrugMnnCode_id = DN.DrugMnnCode_id
				left join rls.v_DrugTorgCode DTC with(nolock) on DTC.DrugTorgCode_id = DN.DrugTorgCode_id
				left join rls.v_DrugComplexMnnCode DCMC with(nolock) on DCMC.DrugComplexMnnCode_id = DN.DrugComplexMnnCode_id
				left join rls.v_Drug Drug with (nolock) on Drug.Drug_id = DN.Drug_id
			where (1 = 1)
				and DN.DrugNomen_id = :DrugNomen_id
		";

		$result = $this->db->query($query, $data);
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *  Загрузка данных справочника ЛП ВЗН
     */
	function loadDrugVznData($data) {
		$query = "
			select
				isnull(dv.DrugVZN_fid, nsi_dv.DrugVZN_id) as DrugVZN_fid, -- МНН
				isnull(dv.DrugRelease_id, nsi_dr.DrugRelease_id) as DrugRelease_id, -- Торг. наим.
				isnull(dv.DrugFormVZN_id, nsi_dfv.DrugFormVZN_id) as DrugFormVZN_id, -- Лек.форма 
				dv.DrugDose_id, -- Дозировка
				isnull(dv.DrugKolDose_id, nsi_dkd.DrugKolDose_id) as DrugKolDose_id, -- Кол-во доз в уп.
				(isnull(nsi_dv.DrugVZN_Code+' - ', '') + nsi_dv.DrugVZN_Name) as DrugMnnVZN_Code,
				(isnull(nsi_dr.DrugRelease_Code+' - ', '') + nsi_dr.DrugRelease_Name) as TradeNamesVZN_Code,
				(isnull(nsi_dfv.DrugFormVZN_Code+' - ', '') + nsi_dfv.DrugFormVZN_Name) as DrugFormVZN_Code
			from
				rls.v_Drug d with(nolock)
				outer apply (
					select top 1
						i_dv.DrugVZN_fid,
						i_dv.DrugFormVZN_id,
						i_dv.DrugDose_id,
						i_dv.DrugKolDose_id,
						i_dv.DrugRelease_id 
					from
						rls.DrugVzn i_dv with (nolock)
					where
						i_dv.Drug_id = d.Drug_id
					order by
						i_dv.DrugVZN_id
				) dv
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				left join rls.v_ACTMATTERS am with (nolock) on am.ACTMATTERS_ID = dcmn.ActMatters_id
				left join rls.v_TRADENAMES tn with (nolock) on tn.TRADENAMES_ID = d.DrugTorg_id
				left join rls.CLSDRUGFORMS cdf with(nolock) on cdf.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
				outer apply (
					select top 1
						i_nsi_dv.DrugVzn_id,
						i_nsi_dv.DrugVzn_Code,
						i_nsi_dv.DrugVzn_Name
					from
						nsi.v_DrugVzn i_nsi_dv with (nolock)
					where
						i_nsi_dv.DrugVzn_id = dv.DrugVZN_fid or
						(
							dv.DrugVZN_fid is null and
							i_nsi_dv.ACTMATTERS_ID = dcmn.ActMatters_id
						)
					order by
						i_nsi_dv.DrugVzn_id
				) nsi_dv
				outer apply (
					select top 1
						i_nsi_dr.DrugRelease_id,
						i_nsi_dr.DrugRelease_Code,
						i_nsi_dr.DrugRelease_Name
					from
						nsi.v_DrugRelease i_nsi_dr with (nolock)
					where
						i_nsi_dr.DrugRelease_id = dv.DrugRelease_id or
						(
							dv.DrugRelease_id is null and
							i_nsi_dr.TRADENAMES_ID = d.DrugTorg_id
						)
					order by
						i_nsi_dr.DrugRelease_id
				) nsi_dr
				outer apply (
					select top 1
						i_nsi_dkd.DrugKolDose_id
					from
						nsi.v_DrugKolDose i_nsi_dkd with (nolock)
					where
						i_nsi_dkd.DrugKolDose_KolDose = d.Drug_Fas
					order by
						i_nsi_dkd.DrugKolDose_id
				) nsi_dkd	
				left join nsi.v_DrugFormVZN nsi_dfv with (nolock) on nsi_dfv.DrugFormVZN_id = isnull(dv.DrugFormVZN_id, cdf.DrugFormVZN_id)
				left join nsi.v_DrugDose nsi_dd with (nolock) on nsi_dd.DrugDose_id = dv.DrugDose_id
			where
				d.Drug_id = :Drug_id;
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *  Сохранение норматива
     */
	function saveDrugNomen($data) {
		$query = "
			select top 1
				COUNT(DN.DrugNomen_id) as Count
			from rls.v_DrugNomen DN with(nolock)
			where DN.Drug_id = :Drug_id and DN.DrugNomen_id <> :DrugNomen_id
		";

		$proc = 'p_DrugNomen_ins';
		if (!empty($data['DrugNomen_id'])) {
			$proc = 'p_DrugNomen_upd';
		}
		
		$query = "
			declare
				@DrugNomen_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@DrugNomen_Name varchar(500);
			
			set @DrugNomen_Name = (select top 1 Drug_Name from rls.v_Drug (nolock) where Drug_id = :Drug_id);
			set @DrugNomen_id = :DrugNomen_id;
			
			exec rls.{$proc}
				@DrugNomen_id = @DrugNomen_id output,
				@Drug_id = :Drug_id,
				@DrugNomen_Name = @DrugNomen_Name,
				@DrugNomen_Nick = :DrugNomen_Nick,
				@DrugNomen_Code = :DrugNomen_Code,
				@PrepClass_id = :PrepClass_id,
				@DrugMnnCode_id = :DrugMnnCode_id,
				@DrugComplexMnnCode_id = :DrugComplexMnnCode_id,
				@DrugTorgCode_id = :DrugTorgCode_id,
				@Okpd_id = :Okpd_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
            select @DrugNomen_id as DrugNomen_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
   		$result = $this->db->query($query, $data);
		
		if ( is_object($result) ) {
 	    	return $result->result('array');
		}
 	    
		return false;
	}

	/**
	 * Сохранение кода МНН
	 */
	function saveDrugMnnCode($data) {
		$response = array('success' => false);

		if (empty($data['Actmatters_id'])) {
			$data['Actmatters_id'] = null;
		}

		$proc = 'p_DrugMnnCode_ins';
		if (!empty($data['DrugMnnCode_id'])) {
			$proc = 'p_DrugMnnCode_upd';
		}

		$query = "
			declare
				@DrugMnnCode_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @DrugMnnCode_id = :DrugMnnCode_id;
			exec rls.{$proc}
				@DrugMnnCode_id = @DrugMnnCode_id output,
				@ACTMATTERS_id = :Actmatters_id,
				@DrugMnnCode_Code = :DrugMnnCode_Code,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
            select @DrugMnnCode_id as DrugMnnCode_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			return $response;
		}
		$arr = $result->result('array');
		if ( !is_array($arr) ) {
			return $response;
		}

		return array('success' => true, 'DrugMnnCode_id' => $arr[0]['DrugMnnCode_id']);
	}

	/**
	 * Сохранение кода комплексного МНН
	 */
	function saveDrugComplexMnnCode($data) {
		$response = array('success' => false);

		if (empty($data['DrugComplexMnn_id'])) {
			$data['DrugComplexMnn_id'] = null;
		}

		$proc = 'p_DrugComplexMnnCode_ins';
		if (!empty($data['DrugComplexMnnCode_id'])) {
			$proc = 'p_DrugComplexMnnCode_upd';
		}

		$query = "
			declare
				@DrugComplexMnnCode_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @DrugComplexMnnCode_id = :DrugComplexMnnCode_id;
			exec rls.{$proc}
				@DrugComplexMnnCode_id = @DrugComplexMnnCode_id output,
				@DrugComplexMnn_id = :DrugComplexMnn_id,
				@DrugComplexMnnCode_Code = :DrugComplexMnnCode_Code,
				@DrugComplexMnnCode_DosKurs = :DrugComplexMnnCode_DosKurs,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
            select @DrugComplexMnnCode_id as DrugComplexMnnCode_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			return $response;
		}
		$arr = $result->result('array');
		if ( !is_array($arr) ) {
			return $response;
		}

		return array('success' => true, 'DrugComplexMnnCode_id' => $arr[0]['DrugComplexMnnCode_id']);
	}

	/**
	 * Сохранение кода торгового наименования
	 */
	function saveDrugTorgCode($data) {
		$response = array('success' => false);

		if (empty($data['Tradenames_id'])) {
			$data['Tradenames_id'] = null;
		}

		$proc = 'p_DrugTorgCode_ins';
		if (!empty($data['DrugTorgCode_id'])) {
			$proc = 'p_DrugTorgCode_upd';
		}

		$query = "
			declare
				@DrugTorgCode_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @DrugTorgCode_id = :DrugTorgCode_id;
			exec rls.{$proc}
				@DrugTorgCode_id = @DrugTorgCode_id output,
				@TRADENAMES_id = :Tradenames_id,
				@DrugTorgCode_Code = :DrugTorgCode_Code,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
            select @DrugTorgCode_id as DrugTorgCode_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			return $response;
		}
		$arr = $result->result('array');
		if ( !is_array($arr) ) {
			return $response;
		}

		return array('success' => true, 'DrugTorgCode_id' => $arr[0]['DrugTorgCode_id']);
	}

	/**
	 * Сохранение кода группировочного торгового наименования
	 */
	function saveDrugPrepFasCode($data) {
		$response = array('success' => false);

		if (empty($data['DrugPrepFas_id'])) {
			$data['DrugPrepFas_id'] = null;
		}

        //ищем существующую запись с кодом
        if (empty($data['DrugPrepFasCode_id'])) {
            $query = "
                select top 1
                    dpfc.DrugPrepFasCode_id
                from
                    rls.v_DrugPrepFasCode dpfc with (nolock)
                where
                    dpfc.DrugPrepFas_id = :DrugPrepFas_id and
                    isnull(dpfc.Org_id, 0) = isnull(:Org_id, 0)
                order by
                    dpfc.DrugPrepFasCode_id
            ";
            $result = $this->getFirstRowFromQuery($query, array(
                'DrugPrepFas_id' => $data['DrugPrepFas_id'],
                'Org_id' => $data['Org_id']
            ));
            $data['DrugPrepFasCode_id'] = !empty($result['DrugPrepFasCode_id']) ? $result['DrugPrepFasCode_id'] : null;
        }

		$proc = 'p_DrugPrepFasCode_ins';
		if (!empty($data['DrugPrepFasCode_id'])) {
			$proc = 'p_DrugPrepFasCode_upd';
		}

		$query = "
			declare
				@DrugPrepFasCode_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @DrugPrepFasCode_id = :DrugPrepFasCode_id;
			exec rls.{$proc}
				@DrugPrepFasCode_id = @DrugPrepFasCode_id output,
				@DrugPrepFas_id = :DrugPrepFas_id,
				@Org_id = :Org_id,
				@DrugPrepFasCode_Code = :DrugPrepFasCode_Code,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
            select @DrugPrepFasCode_id as DrugPrepFasCode_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			return $response;
		}
		$arr = $result->result('array');
		if ( !is_array($arr) ) {
			return $response;
		}

		return array('success' => true, 'DrugPrepFasCode_id' => $arr[0]['DrugPrepFasCode_id']);
	}

	/**
	 * Добавление кода группировочного торгового наименования по идентификатору медикамента
     * Если кода для заданого медикамента еще нет, то добавляется новый
	 * Возвращает идентификатор кода
	 */
	function addDrugPrepFasCodeByDrugId($data) {
		$code_id = null;

		if (empty($data['Org_id'])) {
			$data['Org_id'] = null;
		}

        if (empty($data['DrugPrepFas_id'])) {
            $query = "
                select top 1
                    d.DrugPrepFas_id,
                    d.DrugComplexMnn_id,
                   	d.DrugTorg_id,
                   	d.DrugTorg_Name,
                    dc.DrugComplexMnn_RusName,
                    dcn.DrugComplexMnnName_Name
                from
                    rls.v_Drug d with (nolock)
                    left join rls.v_DrugComplexMnn dc with (nolock) on dc.DrugComplexMnn_id = d.DrugComplexMnn_id
                    left join rls.v_DrugComplexMnnName dcn with (nolock) on dcn.DrugComplexMnnName_id = dc.DrugComplexMnnName_id
                where
                    d.Drug_id = :Drug_id;
            ";
			$result = $this->getFirstRowFromQuery($query, array(
                'Drug_id' => $data['Drug_id']
            ));
            $data['DrugPrepFas_id'] = (!empty($result['DrugPrepFas_id'])?$result['DrugPrepFas_id']:null);
            $data['DrugComplexMnn_id'] = (!empty($result['DrugComplexMnn_id'])?$result['DrugComplexMnn_id']:null);
            $data['TRADENAMES_ID'] = (!empty($result['DrugTorg_id'])?$result['DrugTorg_id']:null);
            $rusname = $result['DrugComplexMnn_RusName'];
            $name = $result['DrugComplexMnnName_Name'];
            $torgname = $result['DrugTorg_Name'];
            if(stripos($rusname, $name)>=0){
            	$data['DrugPrepFasCode_Name'] = str_ireplace($name, $torgname, $rusname);
            } else {
            	$data['DrugPrepFasCode_Name'] = $rusname;
            }
		}

        //ищем существующую запись с кодом
        $query = "
            select top 1
                dpfc.DrugPrepFasCode_id
            from
                rls.v_DrugPrepFasCode dpfc with (nolock)
            where
                dpfc.DrugPrepFas_id = :DrugPrepFas_id and
                isnull(dpfc.Org_id, 0) = isnull(:Org_id, 0)
            order by
                dpfc.DrugPrepFasCode_id
        ";
        $result = $this->getFirstRowFromQuery($query, array(
            'DrugPrepFas_id' => $data['DrugPrepFas_id'],
            'Org_id' => $data['Org_id']
        ));
        $code_id = !empty($result['DrugPrepFasCode_id']) ? $result['DrugPrepFasCode_id'] : null;

        if (empty($code_id)) { //если код не найден, добавляем его
            $query = "
                declare
                    @DrugPrepFasCode_id bigint,
                    @ErrCode int,
                    @ErrMessage varchar(4000);
                set @DrugPrepFasCode_id = null;
                exec rls.p_DrugPrepFasCode_ins
                    @DrugPrepFasCode_id = @DrugPrepFasCode_id output,
                    @DrugPrepFas_id = :DrugPrepFas_id,
                    @Org_id = :Org_id,
                    @DrugPrepFasCode_Code = :DrugPrepFasCode_Code,
                    @TRADENAMES_ID = :TRADENAMES_ID,
                    @DrugPrepFasCode_Name = :DrugPrepFasCode_Name,
                    @DrugComplexMnn_id = :DrugComplexMnn_id,
                    @pmUser_id = :pmUser_id,
                    @Error_Code = @ErrCode output,
                    @Error_Message = @ErrMessage output;
                select @DrugPrepFasCode_id as DrugPrepFasCode_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
            ";
            $result = $this->getFirstRowFromQuery($query, $data);
            if (!empty($result['DrugPrepFasCode_id'])) {
                $code_id = $result['DrugPrepFasCode_id'];
            }
        }
        return $code_id;
	}

    /**
     *  Сохранение кода организации
     */
    function saveDrugNomenOrgLink($data) {
        $query = "
			select top 1
				DNOL.DrugNomenOrgLink_id
			from
			    rls.v_DrugNomenOrgLink DNOL with(nolock)
			where
			    DNOL.DrugNomen_id = :DrugNomen_id and
			    DNOL.Org_id = :DrugNomenOrgLink_Org_id
			order by
			    DNOL.DrugNomenOrgLink_id
		";
        $dnol_data = $this->getFirstRowFromQuery($query, $data);

        $saved_data = array(
            'DrugNomenOrgLink_id' => !empty($dnol_data['DrugNomenOrgLink_id']) ? $dnol_data['DrugNomenOrgLink_id'] : null,
            'Org_id' => $data['DrugNomenOrgLink_Org_id'],
            'DrugNomen_id' => $data['DrugNomen_id'],
            'DrugNomenOrgLink_Code' => $data['DrugNomenOrgLink_Code'],
            'pmUser_id' => $data['pmUser_id']
        );

        if (!empty($data['DrugNomenOrgLink_Code'])) {
            $proc = !empty($saved_data['DrugNomenOrgLink_id']) ? 'p_DrugNomenOrgLink_upd' : 'p_DrugNomenOrgLink_ins';
            $query = "
                declare
                    @DrugNomenOrgLink_id bigint = :DrugNomenOrgLink_id,
                    @ErrCode int,
                    @ErrMessage varchar(4000);

                exec rls.{$proc}
                    @DrugNomenOrgLink_id = @DrugNomenOrgLink_id output,
                    @Org_id = :Org_id,
                    @DrugNomen_id = :DrugNomen_id,
                    @DrugNomenOrgLink_Code = :DrugNomenOrgLink_Code,
                    @pmUser_id = :pmUser_id,
                    @Error_Code = @ErrCode output,
                    @Error_Message = @ErrMessage output;
                select @DrugNomenOrgLink_id as DrugNomenOrgLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
            ";
        } else {
            $query = "
                declare
                    @ErrCode int,
                    @ErrMessage varchar(4000);

                exec rls.p_DrugNomenOrgLink_del
                    @DrugNomenOrgLink_id = :DrugNomenOrgLink_id,
                    @Error_Code = @ErrCode output,
                    @Error_Message = @ErrMessage output;
                select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
            ";
        }

        $result = $this->db->query($query, $saved_data);
        if ( is_object($result) ) {
            return $result->result('array');
        }
        return false;
    }

	/**
	 * Проверка уникалности полей при сохранении номенклатурной карточки
	 */
	function checkDrugNomen($data) {
		$response = array('success' =>false, 'Error_Msg' => '');

		$data['DrugNomen_id'] = empty($data['DrugNomen_id'])?0:$data['DrugNomen_id'];
		$data['DrugMnnCode_id'] = empty($data['DrugMnnCode_id'])?0:$data['DrugMnnCode_id'];
		$data['DrugTorgCode_id'] = empty($data['DrugTorgCode_id'])?0:$data['DrugTorgCode_id'];
		$data['DrugComplexMnnCode_id'] = empty($data['DrugComplexMnnCode_id'])?0:$data['DrugComplexMnnCode_id'];

		$query_arr = array(
			"
				select top 1
					'Drug_id' as Field, COUNT(DN.DrugNomen_id) as Count
				from rls.v_DrugNomen DN with(nolock)
				where DN.Drug_id = :Drug_id and DN.DrugNomen_id <> :DrugNomen_id
			",
			"
				select top 1
					'DrugNomen_Code' as Field, COUNT(DN.DrugNomen_id) as Count
				from rls.v_DrugNomen DN with(nolock)
				where DN.DrugNomen_Code = :DrugNomen_Code and DN.DrugNomen_id <> :DrugNomen_id
			"
		);

		if (!empty($data['Actmatters_id'])) {
			$query_arr[] = "
				select top 1
					'DrugMnnCode_Code' as Field, COUNT(DMC.DrugMnnCode_id) as Count
				from rls.v_DrugMnnCode DMC with(nolock)
				where DMC.DrugMnnCode_Code = :DrugMnnCode_Code and DMC.DrugMnnCode_id <> :DrugMnnCode_id
			";
		}
		if (!empty($data['Tradenames_id'])) {
			$query_arr[] = "
				select top 1
					'DrugTorgCode_Code' as Field, COUNT(DTC.DrugTorgCode_id) as Count
				from rls.v_DrugTorgCode DTC with(nolock)
				where DTC.DrugTorgCode_Code = :DrugTorgCode_Code and DTC.DrugTorgCode_id <> :DrugTorgCode_id
			";
		}
		if (!empty($data['DrugComplexMnn_id'])) {
			$query_arr[] = "
				select 'DrugComplexMnnCode_Code' as Field, COUNT(DCMC.DrugComplexMnnCode_id) as Count
				from rls.v_DrugComplexMnnCode DCMC with(nolock)
				where
					DCMC.DrugComplexMnnCode_Code = :DrugComplexMnnCode_Code
					and DCMC.DrugComplexMnnCode_id <> :DrugComplexMnnCode_id
			";
		}

		$error_arr = array(
			'Drug_id' => 'Указанный препарат уже имеется в номенклатурном справочнике',
			'DrugNomen_Code' => 'Код номенклатурной карточки должен быть уникальным',
			'DrugMnnCode_Code' => 'Код МНН должен быть уникальным',
			'DrugTorgCode_Code' => 'Код торг. наим. должен быть уникальным',
			'DrugComplexMnnCode_Code' => 'Код компл. МНН должен быть уникальным'
		);

		foreach ($query_arr as $key=>$query) {
			$result = $this->db->query($query, $data);

			if ( !is_object($result) ) {
				$response['Error_Msg'] = 'Ошибка выполнения запроса к базе данных';
				return $response;
			}
			$res_arr = $result->result('array');
			if (!is_array($res_arr)) {
				$response['Error_Msg'] = 'Ошибка выполнения запроса к базе данных';
				return $response;
			}
			if ($res_arr[0]['Count'] > 0) {
				$response['Error_Msg'] = $error_arr[$res_arr[0]['Field']];
				return $response;
			}
		}

		$response['success'] = true;
		return $response;
	}


	/**
	 * Получение кода
	 */
	function generateCodeForObject($data) {
		$object = $data['Object'];
        $query = "";
        $params = array();

        if (empty($data['Org_id'])) {
            $data['Org_id'] = null;
        }

        switch($object) {
            case 'DrugNomen':
            case 'DrugNomenOrgLink':
                $code = 0;
                $dpf_id = null;

                if (!empty($data['Drug_id'])) {
                    $query = "
                        select
                            d.DrugPrepFas_id,
                            dpfc.DrugPrepFasCode_Code
                        from
                            rls.v_Drug d with(nolock)
                            left join rls.DrugPrepFasCode dpfc with(nolock) on dpfc.DrugPrepFas_id = d.DrugPrepFas_id
                        where
                            d.Drug_id = :Drug_id and
                            isnull(dpfc.Org_id, 0) = isnull(:Org_id, 0)
                    ";
                    $result = $this->getFirstRowFromQuery($query, $data);

                    if (!empty($result['DrugPrepFasCode_Code'])) {
                        $code = $result['DrugPrepFasCode_Code'];
                    }
                    if (!empty($result['DrugPrepFas_id'])) {
                        $dpf_id = $result['DrugPrepFas_id'];
                    }
                }

                
                if ($object == 'DrugNomen') {
                    /*$query = "
                        select
                            '{$code}.'+cast(max(isnull(cast(p.num as bigint),0))+1 as varchar) as DrugNomen_Code
                        from
                        (
                            select
                                substring(DN.DrugNomen_Code, charindex('.', DN.DrugNomen_Code)+1, len(DN.DrugNomen_Code)) as num
                            from
                                rls.v_DrugNomen DN with(nolock)
                                left join rls.v_Drug D with(nolock) on D.Drug_id = DN.Drug_id
                            where
                                DN.DrugNomen_Code like '{$code}.%'
                            union select '0'
                        ) p
                        where
                            isnumeric(p.num) = 1
                    ";*/
                    $query = "
                        select
                            cast(max(isnull(cast(p.num as bigint),0))+1 as varchar) as DrugNomen_Code
                        from
                        (
                            select
                                substring(DN.DrugNomen_Code, charindex('.', DN.DrugNomen_Code)+1, len(DN.DrugNomen_Code)) as num
                            from
                                rls.v_DrugNomen DN with(nolock)
                                left join rls.v_Drug D with(nolock) on D.Drug_id = DN.Drug_id
                            union select '0'
                        ) p
                        where
                            isnumeric(p.num) = 1
                    ";
                }
                
                if ($object == 'DrugNomenOrgLink') {
                    $query = "
                        select
                            '{$code}.'+cast(max(isnull(cast(p.num as bigint),0))+1 as varchar) as DrugNomenOrgLink_Code
                        from
                        (
                            select
                                substring(DNOL.DrugNomenOrgLink_Code, charindex('.', DNOL.DrugNomenOrgLink_Code)+1, len(DNOL.DrugNomenOrgLink_Code)) as num
                            from
                                rls.v_DrugNomenOrgLink DNOL with(nolock)
                                left join rls.v_DrugNomen DN with (nolock) on DN.DrugNomen_id = DNOL.DrugNomen_id
                                left join rls.v_Drug D with(nolock) on D.Drug_id = DN.Drug_id
                            where
                                DNOL.DrugNomenOrgLink_Code like '{$code}.%' and
                                (
                                    :Org_id is null or
                                    DNOL.Org_id = :Org_id
                                ) and
                                (
                                    :DrugPrepFas_id is null or
                                    D.DrugPrepFas_id = :DrugPrepFas_id
                                )
                            union select '0'
                        ) p
                        where
                            isnumeric(p.num) = 1
                    ";
                }

                $params['DrugPrepFas_id'] = $dpf_id;
                $params['Org_id'] = $data['Org_id'];
                break;
            case 'DrugPrepFasCode':
                $dpf_id = null;

                if (!empty($data['Drug_id'])) {
                    $query = "
                        select
                            d.DrugPrepFas_id,
                            dpfc.DrugPrepFasCode_Code
                        from
                            rls.v_Drug d with(nolock)
                            left join rls.DrugPrepFasCode dpfc with(nolock) on dpfc.DrugPrepFas_id = d.DrugPrepFas_id
                        where
                            d.Drug_id = :Drug_id
                    ";
                    $result = $this->getFirstRowFromQuery($query, $data);

                    if (!empty($result['DrugPrepFas_id'])) {
                        $dpf_id = $result['DrugPrepFas_id'];
                    }
                }

                $query = "
                    select top 1
                        (max(isnull(cast(p.DrugPrepFasCode_Code as bigint),0))+1) as {$object}_Code
                    from (
                        select
                            dpfc.DrugPrepFasCode_Code
                        from
                            rls.v_DrugPrepFasCode dpfc with(nolock)
                        where
                            dpfc.DrugPrepFas_id = :DrugPrepFas_id and
                            isnull(dpfc.Org_id, 0) = isnull(:Org_id, 0)
                        union select '0'
                    ) p
                    where
                        len(p.DrugPrepFasCode_Code) <= 18 and
                        IsNull((
                            Select Case When CharIndex('.', p.DrugPrepFasCode_Code) > 0 Then 0 Else 1 End
                            Where IsNumeric(p.DrugPrepFasCode_Code + 'e0') = 1
                        ), 0) = 1
                ";

                $params['DrugPrepFas_id'] = $dpf_id;
                $params['Org_id'] = $data['Org_id'];
                break;
            default:
                $query = "
                    select top 1
                        (max(isnull(cast(DN.{$object}_Code as bigint),0))+1) as {$object}_Code
                    from
                        rls.v_{$object} DN with(nolock)
                    where
                        len(DN.{$object}_Code) <= 18 and
                        IsNull((
                            Select Case When CharIndex('.', DN.{$object}_Code) > 0 Then 0 Else 1 End
                            Where IsNumeric(DN.{$object}_Code + 'e0') = 1
                        ), 0) = 1
                ";
                break;
        }

		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

    /**
     * Получение регионального кода для позиции номенклатурного справочника
     */

	/**
	 * Получение данных, связанных с Drug_id
	 */
	function getDrugNomenData($data) {
		$params = array('Drug_id' => $data['Drug_id']);

		$query = "
			select top 1
                DN.DrugNomen_Code,
				case when AM.ACTMATTERS_ID > 0 then DMC.DrugMnnCode_id else DMCP.DrugMnnCode_id end as DrugMnnCode_id,
				case when AM.ACTMATTERS_ID > 0 then DMC.DrugMnnCode_Code else DMCP.DrugMnnCode_Code end as DrugMnnCode_Code,
				DTC.DrugTorgCode_id,
				DTC.DrugTorgCode_Code,
				DCMC.DrugComplexMnnCode_id,
				DCMC.DrugComplexMnnCode_Code,
				DCM.DrugComplexMnn_id,
				DCM.DrugComplexMnn_RusName,
				DCM.DrugComplexMnn_LatName,
				DPF.DrugTorg_NameLatin,
				isnull(AM.ACTMATTERS_ID,AMP.ACTMATTERS_ID) as Actmatters_id,
				case when AM.ACTMATTERS_ID > 0 then AM.RUSNAME else AMP.RUSNAME end as Actmatters_RusName,
				case when AM.ACTMATTERS_ID > 0 then AM.LATNAME else AMP.LATNAME end as Actmatters_LatN,
				case when AM.ACTMATTERS_ID > 0 then isnull(AM.ACTMATTERS_LatNameGen, AM.LATNAME) else isnull(AMP.ACTMATTERS_LatNameGen, AMP.LATNAME) end as Actmatters_LatName,
				TN.TRADENAMES_ID as Tradenames_id,
				TN.NAME as Tradenames_RusName,
				LN.LATINNAMES_ID as Tradenames_LatName_id,
				LN.NAME as Tradenames_LatN,
				isnull(LN.LATINNAMES_NameGen, LN.NAME) as Tradenames_LatName,
				CDF.CLSDRUGFORMS_ID as Clsdrugforms_id,
				CDF.FULLNAME as Clsdrugforms_RusName,
				CDF.CLSDRUGFORMS_NameLatin as Clsdrugforms_LatName,
				CDF.CLSDRUGFORMS_NameLatinSocr as Clsdrugforms_LatNameSocr,
				(case
					when P.DFSIZEID > 0 then P.DFSIZE
					when P.DFMASSID > 0 then cast(P.DFMASS as varchar(1024))
					when P.DFCONCID > 0 then cast(P.DFCONC as varchar(1024))
					when P.DFACTID > 0 then cast(P.DFACT as varchar(1024))
				end) as Unit_Value,
				(case
					when P.DFMASSID > 0 then MU.FULLNAME
					when P.DFSIZEID > 0 then SU.FULLNAME
					when P.DFCONCID > 0 then CU.FULLNAME
					when P.DFACTID > 0 then AU.FULLNAME
				end) as Unit_RusName,
				replace ((case
					when P.DFMASSID > 0 then MU.MassUnits_NameLatin
					when P.DFSIZEID > 0 then SU.FULLNAMELATIN
					when P.DFCONCID > 0 then CU.CONCENUNITS_NameLatin
					when P.DFACTID > 0 then AU.ACTUNITS_NameLatin
				end), CHAR(9), '') as Unit_LatName,
				(case
					when P.DFMASSID > 0 then P.DFMASSID
					when P.DFSIZEID > 0 then P.DFSIZEID
					when P.DFCONCID > 0 then P.DFCONCID
					when P.DFACTID > 0 then P.DFACTID
				end) as Unit_id,
				(case
					when P.DFMASSID > 0 then 'MassUnits'
					when P.DFSIZEID > 0 then 'sizeunits'
					when P.DFCONCID > 0 then 'CONCENUNITS'
					when P.DFACTID > 0 then 'ACTUNITS'
				end) as Unit_table,
				DRMZ.DrugRMZ_id,
				DRMZ.DrugRMZ_id as DrugRMZ_oldid,
				DRMZ.DrugRPN_id,
				isnull(DRMZ.DrugRMZ_RegNum, '')+isnull(' '+convert(varchar(10), DRMZ.DrugRMZ_RegDate, 104), '') as DrugRMZ_RegNum,
				DRMZ.DrugRMZ_EAN13Code,
				DRMZ.DrugRMZ_Name,
				DRMZ.DrugRMZ_Form,
				DRMZ.DrugRMZ_Dose,
				DRMZ.DrugRMZ_Pack,
				DRMZ.DrugRMZ_PackSize,
				DRMZ.DrugRMZ_Firm,
				DRMZ.DrugRMZ_Country,
				DRMZ.DrugRMZ_FirmPack,
				RC.REGNUM as Reg_Num,
				D.Drug_Ean,
				nomen.DRUGSINPPACK,
				D.DrugPrepFas_id,
				DPF.DrugPrep_Name,
				ext.Extemporal_id,
				ext.Extemporal_Name,
				replace(replace((
                    select
                        isnull(cast(I_DNOL.Org_id as varchar(max)), '0') + '|' + isnull(I_DNOL.DrugNomenOrgLink_Code, '0')+'|::|'
                    from
                        rls.v_DrugNomenOrgLink I_DNOL with (nolock)
                    where
                        I_DNOL.DrugNomen_id = DN.DrugNomen_id
                    for xml path('')
                )+':::|', '|::|:::|', ''), ':::|', '') as DrugNomenOrgLink_Data,
				replace(replace((
                    select
                        isnull(cast(I_DPFC.Org_id as varchar(max)), 'reg') + '|' + isnull(I_DPFC.DrugPrepFasCode_Code, '0')+'|::|'
                    from
                        rls.v_DrugPrepFasCode I_DPFC with (nolock)
                    where
                        I_DPFC.DrugPrepFas_id = DPF.DrugPrepFas_id
                    for xml path('')
                )+':::|', '|::|:::|', ''), ':::|', '') as DrugPrepFasCode_Data,
                DCMC.DrugComplexMnnCode_DosKurs
			from
				rls.v_Drug D with(nolock)
				left join rls.v_DrugComplexMnn DCM with(nolock) on DCM.DrugComplexMnn_id = D.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnFas DCMF with(nolock) on DCMF.DrugComplexMnnFas_id = DCM.DrugComplexMnnFas_id
				left join rls.v_DrugComplexMnnName DCMN with(nolock) on DCMN.DrugComplexMnnName_id = DCM.DrugComplexMnnName_id
				left join rls.v_Nomen nomen with (nolock) on nomen.NOMEN_ID = D.Drug_id
				left join rls.v_ACTMATTERS AM with(nolock) on AM.ACTMATTERS_ID = DCMN.ActMatters_id
				--left join rls.v_TRADENAMES TN with(nolock) on TN.DrugTorg_id = D.DrugTorg_id
				left join rls.v_Prep P with(nolock) on P.Prep_id = D.DrugPrep_id
				left join rls.v_DrugPrep DPF with(nolock) on DPF.DrugPrepFas_id = D.DrugPrepFas_id
				left join rls.PREP_ACTMATTERS PA with(nolock) on PA.PREPID = P.Prep_id
				left join rls.v_ACTMATTERS AMP with(nolock) on AMP.ACTMATTERS_ID = PA.MATTERID
				left join rls.v_TRADENAMES TN with(nolock) on TN.TRADENAMES_ID = P.TRADENAMEID
				left join rls.v_LATINNAMES LN with(nolock) on LN.LATINNAMES_ID = P.LATINNAMEID
				left join rls.v_CLSDRUGFORMS CDF with(nolock) on CDF.CLSDRUGFORMS_ID = DCM.CLSDRUGFORMS_ID
				left join rls.v_MassUnits MU with(nolock) on MU.MASSUNITS_ID = P.DFMASSID
				left join rls.v_CONCENUNITS CU with(nolock) on CU.CONCENUNITS_ID = P.DFCONCID
				left join rls.v_ACTUNITS AU with(nolock) on AU.ACTUNITS_ID = P.DFACTID
				left join rls.v_sizeunits SU with(nolock) on SU.SIZEUNITS_ID = P.DFSIZEID
				left join rls.v_DrugMnnCode DMC with (nolock) on DMC.ACTMATTERS_id = AM.ACTMATTERS_ID
				left join rls.v_DrugMnnCode DMCP with (nolock) on DMCP.ACTMATTERS_id = AMP.ACTMATTERS_ID
				left join rls.v_DrugTorgCode DTC with (nolock) on DTC.TRADENAMES_id = TN.TRADENAMES_ID
				left join rls.v_DrugComplexMnnCode DCMC with (nolock) on DCMC.DrugComplexMnn_id = DCM.DrugComplexMnn_id
				left join rls.v_REGCERT RC with (nolock) on RC.REGCERT_ID = P.REGCERTID
				left join rls.v_ExtemporalNomen extnomen with (nolock) on extnomen.Nomen_id = D.Drug_id
				left join rls.v_Extemporal ext with (nolock) on ext.Extemporal_id = extnomen.Extemporal_id
				outer apply (
					select top 1
						*
					from
						rls.v_DrugRMZ with (nolock)
					where
						v_DrugRMZ.Drug_id = D.Drug_id
					order by
						DrugRMZ_id
				) DRMZ
                outer apply (
                    select top 1
                        I_DN.DrugNomen_id,
                        I_DN.DrugNomen_Code
                    from
                        rls.v_DrugNomen I_DN with (nolock)
                    where
                        I_DN.Drug_id = D.Drug_id
                    order by
                        I_DN.DrugNomen_id
                ) DN
			where
				D.Drug_id = :Drug_id
		";

		//echo getDebugSQL($query,$data);exit;
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение регионального кода по Drug_id
	 */
	function getDrugNomenCode($data) {
		$params = array('Drug_id' => $data['Drug_id']);

		$query = "
			select top 1
				dn.DrugNomen_Code
			from
				rls.v_DrugNomen dn with(nolock)
			where
				dn.Drug_id = :Drug_id
		";

		//echo getDebugSQL($query,$data);exit;
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение списка ОКПД
	 */
	function loadOkpdList($data) {
		if (empty($data['query']) && empty($data['Okpd_id'])) {
			return false;
		}

		$where = '';
		$params = array();

		if (!empty($data['Okpd_id'])) {
			$where .= ' O.Okpd_id = :Okpd_id';
			$params['Okpd_id'] = $data['Okpd_id'];
		} else if (!empty($data['query'])) {
			$where .= " Okpd_Name like '%'+:Okpd_Name+'%'";
			$params = array('Okpd_Name' => $data['query']);
		}

		$query = "
			select distinct top 500
				O.Okpd_id,
				O.Okpd_Code,
				O.Okpd_Name
			from v_Okpd O with(nolock)
			where
				{$where}
		";

		$result = $this->db->query($query, $params);

		if ( !is_object($result) )
		{
			return false;
		}
		$response = $result->result('array');
		if ( !is_array($response) || count($response) == 0 )
		{
			return false;
		}
		return $response;
	}

	/**
	 * Получение номенклатурного кода по действующему веществу
	 */
	function getDrugMnnCodeByActMattersId($data) {
		$query = "
			select top 1
				DrugMnnCode_Code
			from
				rls.v_DrugMnnCode with (nolock)
			where
				ACTMATTERS_id = :ActMatters_id;
		";
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка кодов РЗН для формы просмотра справочника
	 */
	function loadDrugRMZList($data) {
		$queryParams = array();
		$where = '(1 = 1)';

		if (!empty($data['no_rls']) && $data['no_rls'] == 1) {
            $where .= ' and dr.Drug_id is null';
        }

		if (!empty($data['DrugRMZ_MNN'])) {
			$where .= ' and dr.DrugRMZ_MNN like :DrugRMZ_MNN';
			$queryParams['DrugRMZ_MNN'] = '%'.$data['DrugRMZ_MNN'].'%';
		}

		if (!empty($data['DrugRMZ_Name'])) {
			$where .= ' and dr.DrugRMZ_Name like :DrugRMZ_Name';
			$queryParams['DrugRMZ_Name'] = '%'.$data['DrugRMZ_Name'].'%';
		}

		if (!empty($data['DrugRMZ_Form'])) {
			$where .= ' and dr.DrugRMZ_Form like :DrugRMZ_Form';
			$queryParams['DrugRMZ_Form'] = '%'.$data['DrugRMZ_Form'].'%';
		}

		if (!empty($data['DrugRMZ_Dose'])) {
			$where .= ' and dr.DrugRMZ_Dose like :DrugRMZ_Dose';
			$queryParams['DrugRMZ_Dose'] = '%'.$data['DrugRMZ_Dose'].'%';
		}

		if (!empty($data['DrugRMZ_PackSize'])) {
			$where .= ' and dr.DrugRMZ_PackSize like :DrugRMZ_PackSize';
			$queryParams['DrugRMZ_PackSize'] = '%'.$data['DrugRMZ_PackSize'].'%';
		}

		if (!empty($data['DrugRMZ_RegNum'])) {
			$where .= ' and dr.DrugRMZ_RegNum like :DrugRMZ_RegNum';
			$queryParams['DrugRMZ_RegNum'] = '%'.$data['DrugRMZ_RegNum'].'%';
		}

		if (!empty($data['DrugRMZ_Firm'])) {
			$where .= ' and dr.DrugRMZ_Firm like :DrugRMZ_Firm';
			$queryParams['DrugRMZ_Firm'] = '%'.$data['DrugRMZ_Firm'].'%';
		}

		$query = "
			select
				-- select
				dr.DrugRMZ_id,
				dr.DrugRPN_id,
				dr.DrugRMZ_RegNum,
				convert(varchar, dr.DrugRMZ_RegDate, 104) as DrugRMZ_RegDate,
				dr.DrugRMZ_MNN,
				dr.DrugRMZ_EAN13Code,
				dr.DrugRMZ_CodeRZN,
				dr.DrugRMZ_Name,
				dr.DrugRMZ_Form,
				dr.DrugRMZ_Dose,
				dr.DrugRMZ_Pack,
				dr.DrugRMZ_PackSize,
				dr.DrugRMZ_Firm,
				dr.DrugRMZ_Country,
				dr.DrugRMZ_FirmPack,
				dr.DrugRMZ_CountryPack,
				dr.DrugRMZ_GodnDate,
				dr.DrugRMZ_GodnDateDay
				-- end select
			from
				-- from
				rls.v_DrugRMZ dr with (nolock)
				-- end from
			where
				-- where
				{$where}
				-- end where
			order by
				-- order by
				dr.DrugRPN_id
				-- end order by
		";

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $queryParams);
		$result_count = $this->db->query(getCountSQLPH($query), $queryParams);

		if (is_object($result_count)) {
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		} else {
			$count = 0;
		}

		if (is_object($result)) {
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка кодов РЗН для формы поиска
	 */
	function loadDrugRMZListByQuery($data) {
		$queryParams = array();
		$where = '(1 = 1)';
		$join = '';

		$queryParams['Reg_Num'] = !empty($data['Reg_Num']) ? $data['Reg_Num'] : null;
		$queryParams['Drug_Ean'] = !empty($data['Drug_Ean']) ? $data['Drug_Ean'] : null;
		$where .= ' and ((:Reg_Num is not null and DrugRMZ_RegNum = :Reg_Num) or (:Drug_Ean is not null and DrugRMZ_EAN13Code = :Drug_Ean))';

		if (!empty($data['no_rls']) && $data['no_rls'] == 1) {
			$where .= ' and Drug_id is null';
		}

		if (!empty($data['Drug_Fas']) && $data['Drug_Fas'] > 0) {
			$where .= ' and cast(DrugRMZ_PackSize as decimal) = cast(:Drug_Fas as decimal)';
			$queryParams['Drug_Fas'] = $data['Drug_Fas'];
		}

		if (strlen($data['query']) > 0) {
			$query_arr = explode(' ', $data['query']);
			$w_arr = array();

			foreach($query_arr as $qr) {
				if (!empty($qr)) {
					$w2_arr = array();
					$w2_arr[] = "DrugRMZ_Name like '%{$qr}%'";
					$w2_arr[] = "DrugRMZ_Form like '%{$qr}%'";
					$w2_arr[] = "DrugRMZ_Dose like '%{$qr}%'";
					$w2_arr[] = "DrugRMZ_Pack like '%{$qr}%'";
					$w2_arr[] = "DrugRMZ_PackSize like '%{$qr}%'";
					$w2_arr[] = "DrugRMZ_Firm like '%{$qr}%'";

					$w_arr[] = join( ' or ', $w2_arr);
				}
			}

			if (count($w_arr) > 0) {
				$where .= ' and ('.join( ') and (', $w_arr).')';
			}
		}

		$query = "
			select distinct top 100
				DrugRMZ_id,
				DrugRPN_id,
				isnull(DrugRMZ_RegNum, '')+isnull(' '+convert(varchar(10), DrugRMZ_RegDate, 104), '') as DrugRMZ_RegNum,
				DrugRMZ_EAN13Code,
				DrugRMZ_Name,
				DrugRMZ_Form,
				DrugRMZ_Dose,
				DrugRMZ_Pack,
				DrugRMZ_PackSize,
				DrugRMZ_Firm,
				DrugRMZ_Country,
				isnull(DrugRMZ_FirmPack+', ', '')+isnull(DrugRMZ_CountryPack, '') as DrugRMZ_FirmPack,
				DrugRMZ_CountryPack
			from
				rls.v_DrugRMZ with (nolock)
				{$join}
			where
				{$where};
		";
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Редактирование справочника кодов РЗН
	 */
	function saveDrugRMZ($data) {
		$proc = 'p_DrugRMZ_ins';
		if (!empty($data['DrugRMZ_id'])) {
			$proc = 'p_DrugRMZ_upd';
		} else {
			$data['DrugRMZ_id'] = null;
		}

		$query = "
			declare
				@DrugRMZ_id bigint = :DrugRMZ_id,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec rls.{$proc}
				@DrugRMZ_id = @DrugRMZ_id output,
				@DrugRPN_id = :DrugRPN_id,
				@DrugRMZ_CodeRZN = :DrugRMZ_CodeRZN,
				@DrugRMZ_RegNum = :DrugRMZ_RegNum,
				@DrugRMZ_Country = :DrugRMZ_Country,
				@DrugRMZ_Form = :DrugRMZ_Form,
				@DrugRMZ_Name = :DrugRMZ_Name,
				@DrugRMZ_Dose = :DrugRMZ_Dose,
				@DrugRMZ_Firm = :DrugRMZ_Firm,
				@DrugRMZ_MNN = :DrugRMZ_MNN,
				@DrugRMZ_RegDate = :DrugRMZ_RegDate,
				@DrugRMZ_Cond = :DrugRMZ_Cond,
				@DrugRMZ_Pack = :DrugRMZ_Pack,
				@DrugRMZ_PackSize = :DrugRMZ_PackSize,
				@DrugRMZ_EAN13Code = :DrugRMZ_EAN13Code,
				@DrugRMZ_FirmPack = :DrugRMZ_FirmPack,
				@DrugRMZ_CountryPack = :DrugRMZ_CountryPack,
				@DrugRMZ_UseRange = :DrugRMZ_UseRange,
				@DrugRMZ_GodnDate = :DrugRMZ_GodnDate,
				@DrugRMZ_GodnDateDay = :DrugRMZ_GodnDateDay,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
            select @DrugRMZ_id as DrugRMZ_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 * Редактирование связи справочника кодов РЗН со справочником ЛП
	 */
	function saveDrugRMZLink($data) {
		if ($data['Drug_id'] <= 0 || $data['pmUser_id'] <=0) {
			return array('success' => false);
		}
		if ($data['DrugRMZ_id'] <= 0) {
			$data['DrugRMZ_id'] = null;
		}
		if (empty($data['DrugRMZ_oldid']) || $data['DrugRMZ_oldid'] <= 0) {
			$data['DrugRMZ_oldid'] = null;
		}

		if ($data['DrugRMZ_id'] != $data['DrugRMZ_oldid']) {
			$query = "
				update
					rls.DrugRMZ
				set
					Drug_id = :Drug_id,
					pmUser_updID = :pmUser_id,
					DrugRMZ_updDT = dbo.tzGetDate()
				where
					DrugRMZ_id = :DrugRMZ_id;

				if (:DrugRMZ_oldid is not null)
				begin
					update
						rls.DrugRMZ
					set
						Drug_id = null,
						pmUser_updID = :pmUser_id,
						DrugRMZ_updDT = dbo.tzGetDate()
					where
						DrugRMZ_id = :DrugRMZ_oldid
				end;
			";
			$result = $this->db->query($query, $data);
		}

		return array('success' => true);
	}

	/**
	 * Получение медикамента по номенклатурному коду
	 */
	function getDrugByDrugNomenCode($data) {
		$query = "
			select top 1
				d.Drug_id,
				d.Drug_Name
			from
				rls.v_DrugNomen dn with (nolock)
				left join rls.v_Drug d with (nolock) on dn.Drug_id = d.Drug_id
			where
				dn.DrugNomen_Code = :DrugNomen_Code
			order by
				dn.DrugNomen_id;
		";
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение единиц измерения
	 */
	function getGoodsUnitData($data) {
		$query = "
			select top 1
				GoodsUnit_id
			from
				v_GoodsPackCount with (nolock)
			where
				DrugComplexMnn_id = :DrugComplexMnn_id and Org_id = :Org_id
		";
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка регионального кода МНН
	 */
	function loadDrugMnnCode($data) {
		$q = "
			select
				DrugMnnCode_id,
				ACTMATTERS_id as Actmatters_id,
				DrugMnnCode_Code
			from
				rls.v_DrugMnnCode with (nolock)
			where
				DrugMnnCode_id = :DrugMnnCode_id
		";
		$r = $this->db->query($q, array('DrugMnnCode_id' => $data['DrugMnnCode_id']));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				return $r;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка региональных кодов МНН
	 */
	function loadDrugMnnCodeList($filter) {
		$where = array();
		if (!empty($filter['query'])) {
			$where[] = 'am.RUSNAME like \'%\'+:query+\'%\'';
		}
		if (!empty($filter['DrugMnnCode_Code'])) {
			$where[] = 'dcm.DrugMnnCode_Code = :DrugMnnCode_Code';
		}
		$where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'where '.$where_clause;
		}
		$q = "
			select
				-- select
				dcm.DrugMnnCode_id,
				dcm.ACTMATTERS_id as Actmatters_id,
				dcm.DrugMnnCode_Code,
				am.RUSNAME as ACTMATTERS_RUSNAME
				-- end select
			from
				-- from
				rls.v_DrugMnnCode dcm with (nolock)
				left join rls.v_ACTMATTERS am with (nolock) on am.ACTMATTERS_id = dcm.ACTMATTERS_id
				$where_clause
				-- end from
			order by
				-- order by
				cast(dcm.DrugMnnCode_Code as float) desc
				-- end order by
		";

		$result = $this->db->query(getLimitSQLPH($q, $filter['start'], $filter['limit']), $filter);
		$result_count = $this->db->query(getCountSQLPH($q), $filter);
		if (is_object($result_count)) {
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		} else {
			$count = 0;
		}

		if (is_object($result)) {
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Удаление регионального кода МНН
	 */
	function deleteDrugMnnCode($data) {
		//проверяем наличие кода в номенклатурном справочнике
		$q = "
			select
				count(DrugNomen_id) as cnt
			from
				rls.v_DrugNomen with (nolock)
			where
				DrugMnnCode_id = :DrugMnnCode_id;
		";
		$r = $this->getFirstResultFromQuery($q, array('DrugMnnCode_id' => $data['id']));
		if ($r > 0) {
			return array(array('Error_Msg' => 'Удаление невозможно, так как код используется в номенклатурном справочнике'));
		}

		$q = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec rls.p_DrugMnnCode_del
				@DrugMnnCode_id = :DrugMnnCode_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array('DrugMnnCode_id' => $data['id']));
		if ( is_object($r) ) {
			return $r->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка регионального кода Торгового наименования
	 */
	function loadDrugTorgCode($data) {
		$q = "
			select
				DrugTorgCode_id,
				TRADENAMES_id as Tradenames_id,
				DrugTorgCode_Code
			from
				rls.v_DrugTorgCode with (nolock)
			where
				DrugTorgCode_id = :DrugTorgCode_id
		";
		$r = $this->db->query($q, array('DrugTorgCode_id' => $data['DrugTorgCode_id']));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				return $r;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка региональных кодов Торговых наименований
	 */
	function loadDrugTorgCodeList($filter) {
		$where = array();
		if (!empty($filter['query'])) {
			$where[] = 'tn.NAME like \'%\'+:query+\'%\'';
		}
		if (!empty($filter['DrugTorgCode_Code'])) {
			$where[] = 'dtc.DrugTorgCode_Code = :DrugTorgCode_Code';
		}
		$where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'where '.$where_clause;
		}
		$q = "
			select
				-- select
				dtc.DrugTorgCode_id,
				dtc.TRADENAMES_id as Tradenames_id,
				dtc.DrugTorgCode_Code,
				tn.NAME as TRADENAMES_NAME
				-- end select
			from
				-- from
				rls.v_DrugTorgCode dtc with (nolock)
				left join rls.v_TRADENAMES tn with (nolock) on tn.TRADENAMES_id = dtc.TRADENAMES_id
				$where_clause
				-- end from
			order by
				-- order by
				cast(dtc.DrugTorgCode_Code as float) desc
				-- end order by
		";

		$result = $this->db->query(getLimitSQLPH($q, $filter['start'], $filter['limit']), $filter);
		$result_count = $this->db->query(getCountSQLPH($q), $filter);
		if (is_object($result_count)) {
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		} else {
			$count = 0;
		}

		if (is_object($result)) {
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Удаление регионального кода Торгового наименования
	 */
	function deleteDrugTorgCode($data) {
		//проверяем наличие кода в номенклатурном справочнике
		$q = "
			select
				count(DrugNomen_id) as cnt
			from
				rls.v_DrugNomen with (nolock)
			where
				DrugTorgCode_id = :DrugTorgCode_id;
		";
		$r = $this->getFirstResultFromQuery($q, array('DrugTorgCode_id' => $data['id']));
		if ($r > 0) {
			return array(array('Error_Msg' => 'Удаление невозможно, так как код используется в номенклатурном справочнике'));
		}

		$q = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec rls.p_DrugTorgCode_del
				@DrugTorgCode_id = :DrugTorgCode_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array('DrugTorgCode_id' => $data['id']));
		if ( is_object($r) ) {
			return $r->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка кодов из таблицы dbo.DrugMnn по имени
	 */
	function loadDboDrugMnnCodeListByName($filter) {
		$where = array();
		if (!empty($filter['DrugMnn_Name'])) {
			$where[] = 'DrugMnn_Name like :DrugMnn_Name';
			$filter['DrugMnn_Name'] = preg_replace('/\*/', '', $filter['DrugMnn_Name']);
			$filter['DrugMnn_Name'] = preg_replace('/\-/', '%', $filter['DrugMnn_Name']);
			$filter['DrugMnn_Name'] = preg_replace('/ /', '%', $filter['DrugMnn_Name']);
			$filter['DrugMnn_Name'] = '%'.$filter['DrugMnn_Name'].'%';
		}
		$where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'where '.$where_clause;
		}
		$q = "
			select top 100
				DrugMnn_id,
				DrugMnn_Code,
				DrugMnn_Name,
				(cast(DrugMnn_Code as varchar) + ' ' + DrugMnn_Name)as DrugMnn_FullName
			from
				v_DrugMnn with (nolock)
			    $where_clause
			order by
				DrugMnn_Name desc
		";
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка кодов из таблицы dbo.DrugTorg по имени
	 */
	function loadDboDrugTorgCodeListByName($filter) {
		$where = array();
		if (!empty($filter['DrugTorg_Name'])) {
			$where[] = 'DrugTorg_Name like :DrugTorg_Name';
			$filter['DrugTorg_Name'] = preg_replace('/\*/', '', $filter['DrugTorg_Name']);
			$filter['DrugTorg_Name'] = preg_replace('/\-/', '%', $filter['DrugTorg_Name']);
			$filter['DrugTorg_Name'] = preg_replace('/ /', '%', $filter['DrugTorg_Name']);
			$filter['DrugTorg_Name'] = '%'.$filter['DrugTorg_Name'].'%';
		}
		$where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'where '.$where_clause;
		}
		$q = "
			select top 100
				DrugTorg_id,
				DrugTorg_Code,
				DrugTorg_Name,
				(cast(DrugTorg_Code as varchar) + ' ' + DrugTorg_Name)as DrugTorg_FullName
			from
				v_DrugTorg with (nolock)
			$where_clause
			order by
				DrugTorg_Name desc
		";
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка форм выпуска ЛС ВЗН
	 */
	function loadDrugFormMnnVZNCombo($filter) {
		$where = array();
		$where_sql = "";

		if (!empty($filter['DrugFormMnnVZN_id']) && $filter['DrugFormMnnVZN_id'] > 0) {
			$where[] = 'dfmv.DrugFormMnnVZN_id = :DrugFormMnnVZN_id';
		} else {
			if (!empty($filter['query']) && strlen($filter['query']) > 0) {
				$filter['query'] = '%'.$filter['query'].'%';
				$where[] = 'dfmv.DrugFormVipVZN_Name like :query';
			}

			if (!empty($filter['Drug_id']) && $filter['Drug_id']) {
				$where[] = "
					dfmv.DrugMnnVZN_Code in (
						select
							dmv.DrugMnnVZN_Code
						from
							rls.v_Drug d with (nolock)
							left join rls.DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
							left join rls.DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
							left join rls.v_prep p with (nolock) on p.Prep_id = d.DrugPrep_id
							inner join rls.v_DrugMnnVZN dmv with (nolock) on dmv.ACTMATTERS_ID = dcmn.ActMatters_id
							inner join rls.TradeNamesVZN tnv with (nolock) on tnv.TRADENAMES_ID = p.TRADENAMEID
						where
							d.Drug_id = :Drug_id
					)
				";
			}
		}

		$where_sql = join($where, ' and ');

		if (!empty($where_sql)) {
			$where_sql = "where {$where_sql}";
		}

		$q = "
			select top 500
				dfmv.DrugFormMnnVZN_id,
				dfmv.DrugFormVipVZN_Name
			from
				rls.v_DrugFormMnnVZN dfmv with (nolock)
			{$where_sql};
		";

		//print getDebugSQL($q, $filter);
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 * Редактирование справочника кодов ЛС ВЗН
	 */
	function saveDrugVznData($data) {
		//ищем по медикаменту существующую запись в справочнике
		$query = "
			select top 1
				DrugVZN_id
			from
				rls.v_DrugVZN with (nolock)
			where
				Drug_id = :Drug_id
			order by
				DrugVZN_id;
		";
		$id = $this->getFirstResultFromQuery($query, $data);

		$result = $this->saveObject('rls.DrugVZN', array(
			'DrugVZN_id' => !empty($id) ? $id : null,
			'DrugFormMnnVZN_id' => !empty($data['DrugFormMnnVZN_id']) ? $data['DrugFormMnnVZN_id'] : null,
			'Drug_id' => $data['Drug_id'],
			'DrugVZN_fid' => !empty($data['DrugVZN_fid']) ? $data['DrugVZN_fid'] : null,
			'DrugFormVZN_id' => !empty($data['DrugFormVZN_id']) ? $data['DrugFormVZN_id'] : null,
			'DrugDose_id' => !empty($data['DrugDose_id']) ? $data['DrugDose_id'] : null,
			'DrugKolDose_id' => !empty($data['DrugKolDose_id']) ? $data['DrugKolDose_id'] : null,
			'DrugRelease_id' => !empty($data['DrugRelease_id']) ? $data['DrugRelease_id'] : null
		));

		return $result;
	}

	/**
	 * Получение общей информации о справочнике ЛП Росздравнадзора
	 */
	function getDrugRMZInformation() {
		$query = "
			select
				convert(varchar(10), max(DrugRMZ_updDT), 104) as LastUpdate_Date,
				count(DrugRMZ_id) as Record_Count
			from
				rls.v_DrugRMZ with (nolock);
		";

		$result = $this->db->query($query);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Импорт данных справочника ЛП Росздравнадзора из csv файла.
	 */
	function importDrugRMZFromCsv($data) {
		$result = array(array('Error_Msg' => null));
		$start_data = false;
		$rec_data = array();
		$add_count = 0;

		//получаем максимальное значение DrugRPN_id из справочника
		$query = "
			select
				max(DrugRPN_id) as Max_id
			from
				rls.v_DrugRMZ with (nolock);
		";
		$max_id = $this->getFirstResultFromQuery($query);

		if(($h = fopen($data['FileFullName'], 'r')) !== false) {
			while(($rec_data = fgetcsv($h, 1000, ";")) !== false) {
				if ($start_data && $rec_data[0] > $max_id) {
					$response = $this->saveDrugRMZ(array(
						'DrugRPN_id' => iconv('cp1251', 'UTF-8', $rec_data[0]),
						'DrugRMZ_CodeRZN' => iconv('cp1251', 'UTF-8', $rec_data[1]),
						'DrugRMZ_RegNum' => iconv('cp1251', 'UTF-8', $rec_data[2]),
						'DrugRMZ_Country' => iconv('cp1251', 'UTF-8', $rec_data[3]),
						'DrugRMZ_Form' => iconv('cp1251', 'UTF-8', $rec_data[4]),
						'DrugRMZ_Name' => iconv('cp1251', 'UTF-8', $rec_data[5]),
						'DrugRMZ_Dose' => iconv('cp1251', 'UTF-8', $rec_data[6]),
						'DrugRMZ_Firm' => iconv('cp1251', 'UTF-8', $rec_data[7]),
						'DrugRMZ_MNN' => iconv('cp1251', 'UTF-8', $rec_data[8]),
						'DrugRMZ_RegDate' => iconv('cp1251', 'UTF-8', $rec_data[9]),
						'DrugRMZ_Cond' => iconv('cp1251', 'UTF-8', $rec_data[10]),
						'DrugRMZ_Pack' => iconv('cp1251', 'UTF-8', $rec_data[11]),
						'DrugRMZ_PackSize' => iconv('cp1251', 'UTF-8', $rec_data[12]),
						'DrugRMZ_EAN13Code' => iconv('cp1251', 'UTF-8', $rec_data[13]),
						'DrugRMZ_FirmPack' => iconv('cp1251', 'UTF-8', $rec_data[14]),
						'DrugRMZ_CountryPack' => iconv('cp1251', 'UTF-8', $rec_data[15]),
						'DrugRMZ_UseRange' => iconv('cp1251', 'UTF-8', $rec_data[18]),
						'DrugRMZ_GodnDate' => iconv('cp1251', 'UTF-8', $rec_data[19]),
						'DrugRMZ_GodnDateDay' => iconv('cp1251', 'UTF-8', $rec_data[20]),
						'pmUser_id' => $data['pmUser_id']
					));
					$add_count++;
				}
				if ($rec_data[0] == 'DrugID') {
					$start_data = true;
				}
			}
			fclose($h);
		}

		//обновление связей между таблицами rls.Drug и rls.DrugRMZ
		$response = $this->updateDrugRMZLink(array('pmUser_id' => $data['pmUser_id']));

		return array('success' => true, 'data' => array('add_count' => $add_count));
	}

	/**
	 * Получение данных для экспорта остатков и поставок по ОНЛС и ВЗН
	 */
	function getDrugRMZExportData($data) {
		$q = "
			declare
				@min_exp_date date;

			set @min_exp_date = dateadd(month, 2, dbo.tzGetDate());

			select
				1 as Error_Code,
				null as DrugID,
				d.Drug_Name as Drug_Name,
				null as VZN,
				null as RecordType,
				null as FinYear,
				null as ExpDate,
				null as Ser,
				null as Amount,
				null as Summa,
				null as NumOfUnits
			from
				v_DrugOstatRegistry dor with (nolock)
				left join v_SubAccountType sat with(nolock) on sat.SubAccountType_id = dor.SubAccountType_id
				left join WhsDocumentCostItemType wdcit with(nolock) on wdcit.WhsDocumentCostItemType_id = dor.WhsDocumentCostItemType_id
				left join Contragent c with (nolock) on c.Contragent_id = dor.Contragent_id
				left join ContragentType ct with(nolock) on ct.ContragentType_id = c.ContragentType_id
				left join rls.v_Drug d with(nolock) on d.Drug_id = dor.Drug_id
				outer apply (
					select top 1
						i_drmz.Drug_id,
						i_drmz.DrugRPN_id
					from
						rls.DrugRMZ i_drmz with (nolock)
					where
						i_drmz.Drug_id = dor.Drug_id
				) drmz
			where
				dor.DrugOstatRegistry_Kolvo > 0 and
				sat.SubAccountType_Code = 1 and
				wdcit.WhsDocumentCostItemType_Nick in ('vzn', 'fl') and
				ct.ContragentType_SysNick in ('apt', 'store') and
				drmz.Drug_id is null
			group by
				d.Drug_Name
			union all select
				(case
					when p.PrepSeries_GodnDate <= @min_exp_date then 3
					when isnull(isdef.YesNo_Code, 0) = 1 then 4
					else null
				end) as Error_Code,
				p.DrugRPN_id as DrugID,
				d.Drug_Name as Drug_Name,
				(case
					when p.WhsDocumentCostItemType_Nick = 'vzn' then 1
					else 0
				end) as VZN,
				2 as RecordType,
				datepart(year, WhsDocumentSupply_ExecDate) as FinYear,
				convert(varchar(10), p.PrepSeries_GodnDate, 104) as ExpDate,
				convert(varchar(10), p.PrepSeries_Ser, 104) as Ser,
				p.DrugOstatRegistry_Kolvo as Amount,
				p.DrugOstatRegistry_Sum as Summa,
				(case
					when isnull(nomen.DRUGSINPPACK, 0) > 0 then p.DrugOstatRegistry_Kolvo*nomen.DRUGSINPPACK
					else p.DrugOstatRegistry_Kolvo
				end) as NumOfUnits
			from
				(
					select
						dor.Drug_id,
						drmz.DrugRPN_id,
						wdcit.WhsDocumentCostItemType_Nick,
						ps.PrepSeries_GodnDate,
						ps.PrepSeries_Ser,
						ps.PrepSeries_isDefect,
						wds.WhsDocumentSupply_ExecDate,
						sum(dor.DrugOstatRegistry_Kolvo) as DrugOstatRegistry_Kolvo,
						sum(dor.DrugOstatRegistry_Sum) as DrugOstatRegistry_Sum
					from
						v_DrugOstatRegistry dor with (nolock)
						left join v_SubAccountType sat with(nolock) on sat.SubAccountType_id = dor.SubAccountType_id
						left join WhsDocumentCostItemType wdcit with(nolock) on wdcit.WhsDocumentCostItemType_id = dor.WhsDocumentCostItemType_id
						left join Contragent c with (nolock) on c.Contragent_id = dor.Contragent_id
						left join ContragentType ct  with(nolock) on ct.ContragentType_id = c.ContragentType_id
						left join rls.PrepSeries ps with (nolock) on ps.PrepSeries_id = dor.PrepSeries_id
						left join DrugShipment ds with (nolock) on ds.DrugShipment_id = dor.DrugShipment_id
						left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentSupply_id = ds.WhsDocumentSupply_id
						outer apply (
							select top 1
								i_drmz.Drug_id,
								i_drmz.DrugRPN_id
							from
								rls.DrugRMZ i_drmz with (nolock)
							where
								i_drmz.Drug_id = dor.Drug_id
						) drmz
					where
						dor.DrugOstatRegistry_Kolvo > 0 and
						sat.SubAccountType_Code = 1 and
						wdcit.WhsDocumentCostItemType_Nick in ('vzn', 'fl') and
						ct.ContragentType_SysNick in ('apt', 'store') and
						drmz.Drug_id is not null
					group by
						dor.Drug_id, drmz.DrugRPN_id, wdcit.WhsDocumentCostItemType_Nick, ps.PrepSeries_GodnDate, ps.PrepSeries_Ser, ps.PrepSeries_isDefect, wds.WhsDocumentSupply_ExecDate
				) p
				left join rls.v_Drug d with (nolock) on d.Drug_id = p.Drug_id
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnFas dcmf with (nolock) on dcmf.DrugComplexMnnFas_id = dcm.DrugComplexMnnFas_id
				left join rls.v_Nomen nomen with (nolock) on nomen.NOMEN_ID = d.Drug_id
				left join v_YesNo isdef with (nolock) on isdef.YesNo_id = p.PrepSeries_isDefect
			union select
				(case
					when p.DocumentUcStr_Count < 0 then 2
					when p.PrepSeries_GodnDate <= @min_exp_date then 3
					when isnull(isdef.YesNo_Code, 0) = 1 then 4
					else null
				end) as Error_Code,
				p.DrugRPN_id as DrugID,
				d.Drug_Name as Drug_Name,
				(case
					when p.WhsDocumentCostItemType_Nick = 'vzn' then 1
					else 0
				end) as VZN,
				1 as RecordType,
				datepart(year, WhsDocumentSupply_ExecDate) as FinYear,
				convert(varchar(10), p.PrepSeries_GodnDate, 104) as ExpDate,
				convert(varchar(10), p.PrepSeries_Ser, 104) as Ser,
				p.DocumentUcStr_Count as Amount,
				p.DocumentUcStr_SumR as Summa,
				(case
					when isnull(nomen.DRUGSINPPACK, 0) > 0 then p.DocumentUcStr_Count*nomen.DRUGSINPPACK
					else p.DocumentUcStr_Count
				end) as NumOfUnits
			from
				(
					select
						dus.Drug_id,
						drmz.DrugRPN_id,
						wdcit.WhsDocumentCostItemType_Nick,
						ps.PrepSeries_GodnDate,
						ps.PrepSeries_Ser,
						ps.PrepSeries_isDefect,
						wds.WhsDocumentSupply_ExecDate,
						sum((case
							when ddt.DrugDocumentType_SysNick = 'DocVozNakR' then dus.DocumentUcStr_Count*(-1)
							else dus.DocumentUcStr_Count
						end)) as DocumentUcStr_Count,
						sum((case
							when ddt.DrugDocumentType_SysNick = 'DocVozNakR' then dus.DocumentUcStr_SumR*(-1)
							else dus.DocumentUcStr_SumR
						end)) as DocumentUcStr_SumR
					from
						v_DocumentUc du with (nolock)
						left join Contragent c_s with (nolock) on c_s.Contragent_id = du.Contragent_sid
						left join Contragent c_t with (nolock) on c_t.Contragent_id = du.Contragent_tid
						left join v_DrugDocumentType ddt with(nolock) on ddt.DrugDocumentType_id = du.DrugDocumentType_id
						left join v_DrugDocumentStatus dds with(nolock) on dds.DrugDocumentStatus_id = du.DrugDocumentStatus_id
						left join v_WhsDocumentCostItemType wdcit with(nolock) on wdcit.WhsDocumentCostItemType_id = du.WhsDocumentCostItemType_id
						inner join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentUc_id = du.WhsDocumentUc_id
						left join v_DocumentUcStr dus with (nolock) on dus.DocumentUc_id = du.DocumentUc_id
						left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dus.PrepSeries_id
						outer apply (
							select top 1
								i_drmz.Drug_id,
								i_drmz.DrugRPN_id
							from
								rls.DrugRMZ i_drmz with (nolock)
							where
								i_drmz.Drug_id = dus.Drug_id
						) drmz
					where
						ddt.DrugDocumentType_SysNick in ('DokNak', 'DocVozNakR') and
						dds.DrugDocumentStatus_Code = 4 and --Исполнен
						wdcit.WhsDocumentCostItemType_Nick in ('vzn', 'fl') and
						du.DocumentUc_didDate between :Date1 and :Date2 and
						dus.DocumentUcStr_Count <> 0 and
						drmz.Drug_id is not null and
						(
							(ddt.DrugDocumentType_SysNick = 'DokNak' and c_s.Org_id = wds.Org_sid) or
							(ddt.DrugDocumentType_SysNick = 'DocVozNakR' and c_t.Org_id = wds.Org_sid)
						)
					group by
						dus.Drug_id, drmz.DrugRPN_id, wdcit.WhsDocumentCostItemType_Nick, ps.PrepSeries_GodnDate, ps.PrepSeries_Ser, ps.PrepSeries_isDefect, wds.WhsDocumentSupply_ExecDate
				) p
				left join rls.v_Drug d with (nolock) on d.Drug_id = p.Drug_id
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnFas dcmf with (nolock) on dcmf.DrugComplexMnnFas_id = dcm.DrugComplexMnnFas_id
				left join rls.v_Nomen nomen with (nolock) on nomen.NOMEN_ID = d.Drug_id
				left join v_YesNo isdef with (nolock) on isdef.YesNo_id = p.PrepSeries_isDefect
		";

		$result = $this->db->query($q, array(
			'Date1' => $data['Supply_DateRange'][0],
			'Date2' => $data['Supply_DateRange'][1]
		));
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение данных для сопоставления данных номенклатурного справочника и справочника ЛР РЗН
	 */
	function getDrugRMZLinkData($data) {
		$next_drug_id = null;

		$query = "
			select
				count(dn.Drug_id) as no_linked_cnt,
				max(dn.Drug_id) as max_drug_id
			from
				rls.v_DrugNomen dn with (nolock)
				outer apply (
					select top 1
						v_DrugRMZ.Drug_id
					from
						rls.v_DrugRMZ with (nolock)
					where
						v_DrugRMZ.Drug_id = dn.Drug_id
				) drmz
			where
				drmz.Drug_id is null;
		";
		$drug_data = $this->getFirstRowFromQuery($query);
		if (!$drug_data || count($drug_data) < 2) {
			return false;
		}

		$query = "
			select top 1
				dn.Drug_id
			from
				rls.v_DrugNomen dn with (nolock)
				outer apply (
					select top 1
						v_DrugRMZ.Drug_id
					from
						rls.v_DrugRMZ with (nolock)
					where
						v_DrugRMZ.Drug_id = dn.Drug_id
				) drmz
			where
				dn.Drug_id > isnull(:Drug_id, 0) and
				drmz.Drug_id is null
			order by
				dn.Drug_id;
		";
		$next_drug_id = $this->getFirstResultFromQuery($query, $data);

		//если записи кончились, переходим к первой
		if ($next_drug_id <= 0) {
			$next_drug_id = $drug_data['max_drug_id'];
		}

		if ($next_drug_id > 0) {
			$query = "
				select top 1
					D.Drug_id,
					DN.DrugNomen_Code as DrugNomen_Code,
					isnull(RC.REGNUM, '')+isnull(' '+convert(varchar(10), D.Drug_begDate, 104), '') as Reg_Data,
					RC.REGNUM as Reg_Num,
					D.Drug_Ean,
					TN.NAME as Tradenames_RusName,
					CDF.FULLNAME as Clsdrugforms_RusName,
					(case
						when P.DFSIZEID > 0 then cast(P.DFSIZE as varchar)+' '+SU.SHORTNAME
						when P.DFMASSID > 0 then cast(P.DFMASS as varchar)+' '+MU.SHORTNAME
						when P.DFCONCID > 0 then cast(P.DFCONC as varchar)+' '+CU.SHORTNAME
						when P.DFACTID > 0 then cast(P.DFACT as varchar)+' '+AU.SHORTNAME
					end) as Unit_Value,
					(case
						when F.FULLNAME is null or F.FULLNAME like ''
						then FN.NAME else F.FULLNAME
					end) as Firm_Name,
					D.Drug_Fas,
					(
						isnull(cast(N.DRUGSINPPACK as varchar)+'шт., '+DP1.FULLNAME+' ', '') +
						isnull('('+cast(N.PPACKINUPACK as varchar)+'), '+DP2.FULLNAME+' ', '') +
						isnull('('+cast(N.UPACKINSPACK as varchar)+'), '+DP3.FULLNAME+' ', '')
					) as DrugPack_Name,
					(case
						when PACK_F.FULLNAME is null or PACK_F.FULLNAME like ''
						then PACK_FN.NAME else PACK_F.FULLNAME
					end + isnull((case when PACK_C.NAME not like '' then ', ' else '' end)+PACK_C.NAME, '')) as DrugPack_FirmName
				from
					rls.v_Drug D with(nolock)
					left join rls.v_DrugComplexMnn DCM with(nolock) on DCM.DrugComplexMnn_id = D.DrugComplexMnn_id
					left join rls.v_Prep P with(nolock) on P.Prep_id = D.DrugPrep_id
					left join rls.v_FIRMS F with(nolock) on F.FIRMS_ID = P.FIRMID
					left join rls.v_FIRMNAMES FN with(nolock) on FN.FIRMNAMES_ID = F.NAMEID
					left join rls.v_NOMEN N with(nolock) on N.PREPID = P.Prep_id
					left join rls.v_DRUGPACK DP1 with(nolock) on DP1.DRUGPACK_ID = N.PPACKID
					left join rls.v_DRUGPACK DP2 with(nolock) on DP2.DRUGPACK_ID = N.UPACKID
					left join rls.v_DRUGPACK DP3 with(nolock) on DP3.DRUGPACK_ID = N.SPACKID
					left join rls.v_FIRMS PACK_F with(nolock) on PACK_F.FIRMS_ID = N.FIRMID
					left join rls.v_FIRMNAMES PACK_FN with(nolock) on PACK_FN.FIRMNAMES_ID = PACK_F.NAMEID
					left join rls.v_COUNTRIES PACK_C with(nolock) on PACK_C.COUNTRIES_ID = PACK_F.COUNTID
					left join rls.v_REGCERT RC with (nolock) on RC.REGCERT_ID = P.REGCERTID
					left join rls.v_TRADENAMES TN with(nolock) on TN.TRADENAMES_ID = P.TRADENAMEID
					left join rls.v_CLSDRUGFORMS CDF with(nolock) on CDF.CLSDRUGFORMS_ID = DCM.CLSDRUGFORMS_ID
					left join rls.v_MassUnits MU with(nolock) on MU.MASSUNITS_ID = P.DFMASSID
					left join rls.v_CONCENUNITS CU with(nolock) on CU.CONCENUNITS_ID = P.DFCONCID
					left join rls.v_ACTUNITS AU with(nolock) on AU.ACTUNITS_ID = P.DFACTID
					left join rls.v_sizeunits SU with(nolock) on SU.SIZEUNITS_ID = P.DFSIZEID
					outer apply (
						select top 1
							DrugNomen_Code
						from
							rls.v_DrugNomen with (nolock)
						where
							v_DrugNomen.Drug_id = D.Drug_id
						order by
							v_DrugNomen.DrugNomen_id
					) DN
				where
					D.Drug_id = :Drug_id
			";

			//echo getDebugSQL($query,$data);exit;
			$result = $this->db->query($query, array(
				'Drug_id' => $next_drug_id
			));

			if ( is_object($result) ) {
				$result = $result->result('array');
				$result[0]['no_linked_cnt'] = $drug_data['no_linked_cnt'];
				return $result;
			}
		}

		return false;
	}

	/**
	 * Обновление связей между таблицами rls.Drug и rls.DrugRMZ
	 */
	function updateDrugRMZLink($data) {
		$query_array = array(); //массив запросов для связываний позиций справочников

        //1. код EAN + № РУ + дата
        $query_array[] = "
            select
				drmz.DrugRMZ_id,
				d.Drug_id
			from
				rls.DrugRMZ drmz with (nolock)
				outer apply(
					select
						max(i_d.Drug_id) as Drug_id,
						count(i_d.Drug_id) as cnt
					from
						rls.v_Drug i_d with (nolock)
					where
						drmz.DrugRMZ_EAN13Code is not null and
						i_d.Drug_Ean = drmz.DrugRMZ_EAN13Code and
						i_d.Drug_RegNum = drmz.DrugRMZ_RegNum and
						i_d.Drug_begDate = drmz.DrugRMZ_RegDate
				) d
			where
				drmz.Drug_id is null and
				isnull(d.cnt, 0) = 1
        ";

        //2. код EAN + № РУ
        $query_array[] = "
            select
				drmz.DrugRMZ_id,
				d.Drug_id
			from
				rls.DrugRMZ drmz with (nolock)
				outer apply(
					select
						max(i_d.Drug_id) as Drug_id,
						count(i_d.Drug_id) as cnt
					from
						rls.v_Drug i_d with (nolock)
					where
						drmz.DrugRMZ_EAN13Code is not null and
						i_d.Drug_Ean = drmz.DrugRMZ_EAN13Code and
						i_d.Drug_RegNum = drmz.DrugRMZ_RegNum
				) d
			where
				drmz.Drug_id is null and
				isnull(d.cnt, 0) = 1
        ";

        //3. код РУ + дата
        $query_array[] = "
            select
				drmz.DrugRMZ_id,
				d.Drug_id
			from
				rls.DrugRMZ drmz with (nolock)
				outer apply(
					select
						max(i_d.Drug_id) as Drug_id,
						count(i_d.Drug_id) as cnt
					from
						rls.v_Drug i_d with (nolock)
					where
						i_d.Drug_RegNum = drmz.DrugRMZ_RegNum and
						i_d.Drug_begDate = drmz.DrugRMZ_RegDate
				) d
			where
				drmz.Drug_id is null and
				isnull(d.cnt, 0) = 1
        ";

        //4. код РУ
        $query_array[] = "
            select
				drmz.DrugRMZ_id,
				d.Drug_id
			from
				rls.DrugRMZ drmz with (nolock)
				outer apply(
					select
						max(i_d.Drug_id) as Drug_id,
						count(i_d.Drug_id) as cnt
					from
						rls.v_Drug i_d with (nolock)
					where
						i_d.Drug_RegNum = drmz.DrugRMZ_RegNum
				) d
			where
				drmz.Drug_id is null and
				isnull(d.cnt, 0) = 1
        ";

        //5. указан код EAN в спр. РЗН + код EAN + № РУ + лекарственная форма + дозировка + фасовка + страна фирмы производителя
        $query_array[] = "
            select
                drmz.DrugRMZ_id,
                d.Drug_id
            from
                rls.DrugRMZ drmz with (nolock)
                outer apply(
                    select
                        max(i_d.Drug_id) as Drug_id,
                        count(i_d.Drug_id) as cnt
                    from
                        rls.v_Drug i_d with (nolock)
                        left join rls.v_DrugComplexMnn i_dcm with(nolock) on i_dcm.DrugComplexMnn_id = i_d.DrugComplexMnn_id
                        left join rls.v_DrugComplexMnnDose i_dcmd with(nolock) on i_dcmd.DrugComplexMnnDose_id = i_dcm.DrugComplexMnnDose_id
                        left join rls.v_Prep i_p with(nolock) on i_p.Prep_id = i_d.DrugPrep_id
                        left join rls.v_FIRMS i_f with(nolock) on i_f.FIRMS_ID = i_p.FIRMID
                        left join rls.v_COUNTRIES i_c with(nolock) on i_c.COUNTRIES_ID = i_f.COUNTID
                    where
                        drmz.DrugRMZ_EAN13Code is not null and
                        i_d.Drug_Ean = drmz.DrugRMZ_EAN13Code and
                        i_d.Drug_RegNum = drmz.DrugRMZ_RegNum and
                        i_d.drugform_fullname = drmz.DrugRMZ_Form and
                        i_dcmd.DrugComplexMnnDose_Name = drmz.DrugRMZ_Dose and
                        i_d.Drug_Fas = drmz.DrugRMZ_PackSize and
                        i_c.NAME = drmz.DrugRMZ_Country
                ) d
            where
                drmz.Drug_id is null and
                isnull(d.cnt, 0) = 1
        ";

        //6. указан код EAN в спр. РЗН + код EAN + № РУ + лекарственная форма + дозировка + фасовка + фирма-производитель + страна фирмы производителя + фирма-упаковщик  (если записей более 1, то выбрать более позднюю запись)
        $query_array[] = "
            select
                drmz.DrugRMZ_id,
                d.Drug_id
            from
                rls.DrugRMZ drmz with (nolock)
                outer apply(
                select
                    max(i_d.Drug_id) as Drug_id,
                    count(i_d.Drug_id) as cnt
                from
                    rls.v_Drug i_d with (nolock)
                    left join rls.v_DrugComplexMnn i_dcm with(nolock) on i_dcm.DrugComplexMnn_id = i_d.DrugComplexMnn_id
                    left join rls.v_DrugComplexMnnDose i_dcmd with(nolock) on i_dcmd.DrugComplexMnnDose_id = i_dcm.DrugComplexMnnDose_id
                    left join rls.v_Nomen i_n with(nolock) on i_n.NOMEN_ID = i_d.Drug_id
                    left join rls.v_Prep i_p with(nolock) on i_p.Prep_id = i_d.DrugPrep_id
                    left join rls.v_FIRMS i_f with(nolock) on i_f.FIRMS_ID = i_p.FIRMID
                    left join rls.v_COUNTRIES i_c with(nolock) on i_c.COUNTRIES_ID = i_f.COUNTID
                    left join rls.v_FIRMS i_n_f with(nolock) on i_n_f.FIRMS_ID = i_n.FIRMID
                where
                    drmz.DrugRMZ_EAN13Code is not null and
                    i_d.Drug_Ean = drmz.DrugRMZ_EAN13Code and
                    i_d.Drug_RegNum = drmz.DrugRMZ_RegNum and
                    i_d.drugform_fullname = drmz.DrugRMZ_Form and
                    i_dcmd.DrugComplexMnnDose_Name = drmz.DrugRMZ_Dose and
                    i_d.Drug_Fas = drmz.DrugRMZ_PackSize and
                    i_f.FULLNAME = drmz.DrugRMZ_Firm and
                    i_c.NAME = drmz.DrugRMZ_Country and
                    i_n_f.FULLNAME = drmz.DrugRMZ_FirmPack
                ) d
            where
                drmz.Drug_id is null and
                isnull(d.cnt, 0) >= 1
        ";

        //7. не указан код EAN в спр. РЗН + № РУ + лекарственная форма + дозировка + фасовка + страна фирмы производителя
        $query_array[] = "
            select
                drmz.DrugRMZ_id,
                d.Drug_id
            from
                rls.DrugRMZ drmz with (nolock)
                outer apply(
                    select
                        max(i_d.Drug_id) as Drug_id,
                        count(i_d.Drug_id) as cnt
                    from
                        rls.v_Drug i_d with (nolock)
                        left join rls.v_DrugComplexMnn i_dcm with(nolock) on i_dcm.DrugComplexMnn_id = i_d.DrugComplexMnn_id
                        left join rls.v_DrugComplexMnnDose i_dcmd with(nolock) on i_dcmd.DrugComplexMnnDose_id = i_dcm.DrugComplexMnnDose_id
                        left join rls.v_Prep i_p with(nolock) on i_p.Prep_id = i_d.DrugPrep_id
                        left join rls.v_FIRMS i_f with(nolock) on i_f.FIRMS_ID = i_p.FIRMID
                        left join rls.v_COUNTRIES i_c with(nolock) on i_c.COUNTRIES_ID = i_f.COUNTID
                    where
                        drmz.DrugRMZ_EAN13Code is null and
                        i_d.Drug_RegNum = drmz.DrugRMZ_RegNum and
                        i_d.drugform_fullname = drmz.DrugRMZ_Form and
                        i_dcmd.DrugComplexMnnDose_Name = drmz.DrugRMZ_Dose and
                        i_d.Drug_Fas = drmz.DrugRMZ_PackSize and
                        i_c.NAME = drmz.DrugRMZ_Country
                ) d
            where
                drmz.Drug_id is null and
                isnull(d.cnt, 0) = 1
        ";

        //8. не указан код EAN в спр. РЗН + № РУ + лекарственная форма + дозировка + фасовка + фирма-производитель + страна фирмы производителя + фирма-упаковщик (если записей более 1, то выбрать более позднюю запись)
        $query_array[] = "
            select
                drmz.DrugRMZ_id,
                d.Drug_id
            from
                rls.DrugRMZ drmz with (nolock)
                outer apply(
                select
                    max(i_d.Drug_id) as Drug_id,
                    count(i_d.Drug_id) as cnt
                from
                    rls.v_Drug i_d with (nolock)
                    left join rls.v_DrugComplexMnn i_dcm with(nolock) on i_dcm.DrugComplexMnn_id = i_d.DrugComplexMnn_id
                    left join rls.v_DrugComplexMnnDose i_dcmd with(nolock) on i_dcmd.DrugComplexMnnDose_id = i_dcm.DrugComplexMnnDose_id
                    left join rls.v_Nomen i_n with(nolock) on i_n.NOMEN_ID = i_d.Drug_id
                    left join rls.v_Prep i_p with(nolock) on i_p.Prep_id = i_d.DrugPrep_id
                    left join rls.v_FIRMS i_f with(nolock) on i_f.FIRMS_ID = i_p.FIRMID
                    left join rls.v_COUNTRIES i_c with(nolock) on i_c.COUNTRIES_ID = i_f.COUNTID
                    left join rls.v_FIRMS i_n_f with(nolock) on i_n_f.FIRMS_ID = i_n.FIRMID
                where
                    drmz.DrugRMZ_EAN13Code is null and
                    i_d.Drug_RegNum = drmz.DrugRMZ_RegNum and
                    i_d.drugform_fullname = drmz.DrugRMZ_Form and
                    i_dcmd.DrugComplexMnnDose_Name = drmz.DrugRMZ_Dose and
                    i_d.Drug_Fas = drmz.DrugRMZ_PackSize and
                    i_f.FULLNAME = drmz.DrugRMZ_Firm and
                    i_c.NAME = drmz.DrugRMZ_Country and
                    i_n_f.FULLNAME = drmz.DrugRMZ_FirmPack
                ) d
            where
                drmz.Drug_id is null and
                isnull(d.cnt, 0) >= 1
        ";

        //получение данных и сохранение связей
        foreach($query_array as $query) {
            $result = $this->db->query($query);
            if (is_object($result)) {
                $result = $result->result('array');
                foreach($result as $rmz_data) {
                    $response = $this->dbmodel->saveDrugRMZLink(array(
                        'pmUser_id' => $data['pmUser_id'],
                        'DrugRMZ_id' => $rmz_data['DrugRMZ_id'],
                        'Drug_id' => $rmz_data['Drug_id']
                    ));
                }
            }
        }

		return true;
	}

	/**
	 *  Сохранение латинского наименования МНН
     */
	function saveActmatters_LatName($data) {
		$query = "
			UPDATE
				rls.ACTMATTERS with (rowlock)
			SET
				ACTMATTERS_LatNameGen = :Actmatters_LatName,
				pmUser_updID = :pmUser_id
			WHERE
				ACTMATTERS_ID = :Actmatters_id
		";
		$result = $this->db->query($query, $data);
		
		if ( $result ) {
			return $result;
		} else {
			return false;
		}
	}

	/**
	 *  Сохранение латинского наименования комплексного МНН
     */
	function saveDrugComplexMnn_LatName($data) {
		$query = "
			UPDATE
				rls.DrugComplexMnn with (rowlock)
			SET
				DrugComplexMnn_LatName = :DrugComplexMnn_LatName,
				pmUser_updID = :pmUser_id
			WHERE
				DrugComplexMnn_id = :DrugComplexMnn_id
		";
		$result = $this->db->query($query, $data);

		if ( $result ) {
			return $result;
		} else {
			return false;
		}
	}

	/**
	 *  Сохранение латинского наименования ЛП
     */
	function saveDrugTorg_NameLatin($data) {
		$query = "
			UPDATE
				rls.DrugPrep with (rowlock)
			SET
				DrugTorg_NameLatin = :DrugTorg_NameLatin,
				pmUser_updID = :pmUser_id
			WHERE
				DrugPrepFas_id = :DrugPrepFas_id
		";
		$result = $this->db->query($query, $data);

		if ( $result ) {
			return $result;
		} else {
			return false;
		}
	}

	/**
	 *  Сохранение латинского наименования торгового названия
     */
	function saveTradenames_LatName($data) {
		$query = "
			select *
			from rls.v_LATINNAMES with (nolock)
			where LATINNAMES_ID = :Tradenames_LatName_id
		";
		$result = $this->db->query($query, $data);
		
		if ( is_object($result) ) {
			$res = $result->result('array');
 	    	$proc = 'p_LATINNAMES_upd';
		
			$query = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);
				
				exec rls.{$proc}
					@LATINNAMES_ID = :LATINNAMES_ID,
					@NAME = :NAME,
	   				@LATINNAMES_NameGen = :LATINNAMES_NameGen,
	   				@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
	            select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			
	   		$reslt = $this->db->query($query, 
	   			array(
	   				'LATINNAMES_ID'=>$res[0]['LATINNAMES_ID'],
	   				'NAME'=>$res[0]['NAME'],
	   				'LATINNAMES_NameGen'=>$data['Tradenames_LatName'],
	   				'pmUser_id'=>$data['pmUser_id']
	   				)
	   		);
			
			if ( is_object($reslt) ) {
	 	    	return $reslt->result('array');
			}
			return false;
		} else {
			return false;
		}
	}

	/**
	 *  Сохранение латинского наименования формы выпуска
     */
	function saveClsdrugforms_LatName($data) {
		$query = "
			select *
			from rls.v_CLSDRUGFORMS with (nolock)
			where CLSDRUGFORMS_ID = :Clsdrugforms_id
		";
		$result = $this->db->query($query, $data);
		
		if ( is_object($result) ) {
			$res = $result->result('array');
 	    	$proc = 'p_CLSDRUGFORMS_upd';
		
			$query = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);
				
				exec rls.{$proc}
					@CLSDRUGFORMS_ID = :CLSDRUGFORMS_ID,
					@PARENTID = :PARENTID,
					@NAME = :NAME,
					@FULLNAME = :FULLNAME,
					@CLSDRUGFORMS_NameLatin = :CLSDRUGFORMS_NameLatin,
	   				@CLSDRUGFORMS_NameLatinSocr = :CLSDRUGFORMS_NameLatinSocr,
	   				@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
	            select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			
	   		$reslt = $this->db->query($query, 
	   			array(
	   				'CLSDRUGFORMS_ID'=>$res[0]['CLSDRUGFORMS_ID'],
	   				'PARENTID'=>$res[0]['PARENTID'],
	   				'NAME'=>$res[0]['NAME'],
	   				'FULLNAME'=>$res[0]['FULLNAME'],
	   				'CLSDRUGFORMS_NameLatin'=>$data['Clsdrugforms_LatName'],
	   				'CLSDRUGFORMS_NameLatinSocr'=>$data['Clsdrugforms_LatNameSocr'],
	   				'pmUser_id'=>$data['pmUser_id']
	   				)
	   		);
			
			if ( is_object($reslt) ) {
	 	    	return $reslt->result('array');
			}
			return false;
		} else {
			return false;
		}
	}

	/**
	 *  Сохранение латинского наименования дозировки
     */
	function saveUnit_LatName($data) {

		switch ($data['Unit_table']) {
			case 'MassUnits':
				$query = "
					select *
					from rls.v_MassUnits with (nolock)
					where MASSUNITS_ID = :Unit_id
				";
				$result = $this->db->query($query, $data);
				
				if ( is_object($result) ) {
					$res = $result->result('array');
		 	    	$proc = 'p_MassUnits_upd';
				
					$query = "
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);
						
						exec rls.{$proc}
							@MASSUNITS_ID = :MASSUNITS_ID,
							@FULLNAME = :FULLNAME,
							@SHORTNAME = :SHORTNAME,
							@DrugEdMass_id = :DrugEdMass_id,
							@MassUnits_NameLatin = :MassUnits_NameLatin,
			   				@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
			            select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					
			   		$reslt = $this->db->query($query, 
			   			array(
			   				'MASSUNITS_ID'=>$res[0]['MASSUNITS_ID'],
			   				'FULLNAME'=>$res[0]['FULLNAME'],
			   				'SHORTNAME'=>$res[0]['SHORTNAME'],
			   				'DrugEdMass_id'=>$res[0]['DrugEdMass_id'],
			   				'MassUnits_NameLatin'=>$data['Unit_LatName'],
			   				'pmUser_id'=>$data['pmUser_id']
			   				)
			   		);
					
					if ( is_object($reslt) ) {
			 	    	return $reslt->result('array');
					}
					return false;
				} else {
					return false;
				}
				break;
			case 'sizeunits':
				$query = "
					select *
					from rls.v_sizeunits with (nolock)
					where SIZEUNITS_ID = :Unit_id
				";
				$result = $this->db->query($query, $data);
				
				if ( is_object($result) ) {
					$res = $result->result('array');
		 	    	$proc = 'p_sizeunits_upd';
				
					$query = "
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);
						
						exec rls.{$proc}
							@SIZEUNITS_ID = :SIZEUNITS_ID,
							@FULLNAME = :FULLNAME,
							@SHORTNAME = :SHORTNAME,
							@FULLNAMELATIN = :FULLNAMELATIN,
							@SHORTNAMELATIN = :SHORTNAMELATIN,
			   				@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
			            select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					
			   		$reslt = $this->db->query($query, 
			   			array(
			   				'SIZEUNITS_ID'=>$res[0]['SIZEUNITS_ID'],
			   				'FULLNAME'=>$res[0]['FULLNAME'],
			   				'SHORTNAME'=>$res[0]['SHORTNAME'],
			   				'FULLNAMELATIN'=>$data['Unit_LatName'],
			   				'SHORTNAMELATIN'=>$res[0]['SHORTNAMELATIN'],
			   				'pmUser_id'=>$data['pmUser_id']
			   				)
			   		);
					
					if ( is_object($reslt) ) {
			 	    	return $reslt->result('array');
					}
					return false;
				} else {
					return false;
				}
				break;
			case 'CONCENUNITS':
				$query = "
					select *
					from rls.v_CONCENUNITS with (nolock)
					where CONCENUNITS_ID = :Unit_id
				";
				$result = $this->db->query($query, $data);
				
				if ( is_object($result) ) {
					$res = $result->result('array');
		 	    	$proc = 'p_CONCENUNITS_upd';
				
					$query = "
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);
						
						exec rls.{$proc}
							@CONCENUNITS_ID = :CONCENUNITS_ID,
							@FULLNAME = :FULLNAME,
							@SHORTNAME = :SHORTNAME,
							@DrugEdVol_id = :DrugEdVol_id,
							@CONCENUNITS_NameLatin = :CONCENUNITS_NameLatin,
			   				@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
			            select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					
			   		$reslt = $this->db->query($query, 
			   			array(
			   				'CONCENUNITS_ID'=>$res[0]['CONCENUNITS_ID'],
			   				'FULLNAME'=>$res[0]['FULLNAME'],
			   				'SHORTNAME'=>$res[0]['SHORTNAME'],
			   				'DrugEdVol_id'=>$res[0]['DrugEdVol_id'],
			   				'CONCENUNITS_NameLatin'=>$data['Unit_LatName'],
			   				'pmUser_id'=>$data['pmUser_id']
			   				)
			   		);
					
					if ( is_object($reslt) ) {
			 	    	return $reslt->result('array');
					}
					return false;
				} else {
					return false;
				}
				break;
			case 'ACTUNITS':
				$query = "
					select *
					from rls.v_ACTUNITS with (nolock)
					where ACTUNITS_ID = :Unit_id
				";
				$result = $this->db->query($query, $data);
				
				if ( is_object($result) ) {
					$res = $result->result('array');
		 	    	$proc = 'p_ACTUNITS_upd';
				
					$query = "
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);
						
						exec rls.{$proc}
							@ACTUNITS_ID = :ACTUNITS_ID,
							@FULLNAME = :FULLNAME,
							@SHORTNAME = :SHORTNAME,
							@ACTUNITS_NameLatin = :ACTUNITS_NameLatin,
			   				@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
			            select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					
			   		$reslt = $this->db->query($query, 
			   			array(
			   				'ACTUNITS_ID'=>$res[0]['ACTUNITS_ID'],
			   				'FULLNAME'=>$res[0]['FULLNAME'],
			   				'SHORTNAME'=>$res[0]['SHORTNAME'],
			   				'ACTUNITS_NameLatin'=>$data['Unit_LatName'],
			   				'pmUser_id'=>$data['pmUser_id']
			   				)
			   		);
					
					if ( is_object($reslt) ) {
			 	    	return $reslt->result('array');
					}
					return false;
				} else {
					return false;
				}
				break;
			default:
				return false;
				break;
		}
	}

	/**
	 * Получение списка номенклатур медикаментов
	 */
	function loadDrugNomenList($data) {
		$queryParams = array();
		$where = '(1 = 1)';

		if (!empty($data['DrugNomen_id'])) {
			$queryParams['DrugNomen_id'] = $data['DrugNomen_id'];
			$where .= ' and DrugNomen_id = :DrugNomen_id';
		} else {
			if (!empty($data['query'])) {
				$queryBy = !empty($data['queryBy'])?$data['queryBy']:null;
				$queryParams['query'] = $data['query'];
				switch($queryBy) {
					case 'DrugComplexMnnCode_Code':
						$where .= " and DCMC.DrugComplexMnnCode_Code LIKE :query+'%'";
						break;
					default:
						$where .= " and DN.DrugNomen_Code+' '+DN.DrugNomen_Name LIKE '%'+:query+'%'";
				}
			}
			if (!empty($data['DrugComplexMnn_id'])) {
				$queryParams['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];
				$where .= " and D.DrugComplexMnn_id = :DrugComplexMnn_id";
			}
			if (!empty($data['Tradenames_id'])) {
				$queryParams['Tradenames_id'] = $data['Tradenames_id'];
				$where .= " and D.DrugTorg_id = :Tradenames_id";
			}
		}

		$query = "
			select top 500
				DN.DrugNomen_id,
				DN.DrugNomen_Name,
				DN.DrugNomen_Code,
				D.Drug_id,
				D.Drug_Ean,
				D.Drug_Dose,
				D.DrugForm_Name,
				D.DrugTorg_id,
				D.DrugComplexMnn_id,
				DCMC.DrugComplexMnnCode_Code
			from
				rls.v_DrugNomen DN (nolock)
				left join rls.v_Drug D with(nolock) on D.Drug_id = DN.Drug_id
				left join rls.v_DrugComplexMnnCode DCMC with(nolock) on DCMC.DrugComplexMnnCode_id = DN.DrugComplexMnnCode_id
			where
				{$where}
			order by
				DN.DrugNomen_Name
		";
		//echo getDebugSql($query, $queryParams);exit;
		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Сохранение записи справочника Количество товара в упаковке
	 */
	function saveGoodsPackCount($data) {
		
		$data['Region_id'] = $this->getRegionNumber();

		if(!isset($data['GoodsPackCount_id'])){
			$query = "
				select
				    count(*) as cnt
				from
				    v_GoodsPackCount with (nolock)
				where
				    Region_id = :Region_id and
				    DrugComplexMnn_id = :DrugComplexMnn_id and
				    GoodsUnit_id = :GoodsUnit_id and
				    GoodsPackCount_Count = :GoodsPackCount_Count and
				    isnull(Org_id, 0) = isnull(:Org_id, 0)
			";
			//echo getDebugSql($query, $data);exit;
			$res = $this->db->query($query, $data);
			if ( is_object($res) ) {
				$res = $res->result('array');
				if ($res[0]['cnt'] > 0) {
					return array('success' => false, 'Error_Code' => 400);
				}
			} else {
				return array('success' => false);
			}
		}
		
		$proc = 'dbo.p_GoodsPackCount_'.(isset($data['GoodsPackCount_id']) > 0 ? 'upd' : 'ins');

		$query = "
			declare
				@GoodsPackCount_id bigint = :GoodsPackCount_id,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec {$proc}
				@GoodsPackCount_id = @GoodsPackCount_id output,
				@DrugComplexMnn_id = :DrugComplexMnn_id,
				@TRADENAMES_ID = :TRADENAMES_ID,
				@GoodsPackCount_Count = :GoodsPackCount_Count,
				@GoodsUnit_id = :GoodsUnit_id,
				@Org_id = :Org_id,
				@Region_id = :Region_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
            select @GoodsPackCount_id as GoodsPackCount_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$result = $result->result('array');
			if (isset($result[0]) && empty($result[0]['Error_Msg'])) {
				return array('success' => true);
			}
		}

		return array('success' => false);
	}

	/**
	 * Получение списка количества товара в упаковке
	 */
	function loadGoodsPackCountList($data) {
		$params = array();
		$where = '';
		if(isset($data['GoodsUnit_id'])){
			$params['GoodsUnit_id'] = $data['GoodsUnit_id'];
			$where .= " and GPC.GoodsUnit_id = :GoodsUnit_id ";
		}
		if(isset($data['DrugComplexMnn_id'])){
			$params['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];
			$where .= " and GPC.DrugComplexMnn_id = :DrugComplexMnn_id ";
		}
		$query = "
			select
				GPC.GoodsPackCount_id,
				GPC.GoodsPackCount_Count,
				GPC.GoodsUnit_id
			from
				v_GoodsPackCount GPC with (nolock)
			where
				(1=1)
				{$where}
			order by
				GPC.GoodsPackCount_Count
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка количества товара в упаковке для грида в номенклатурной карточке
	 */
	function loadGoodsPackCountListGrid($data) {
		$params = array();
		$where = '';

        $params['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];
        $where .= " and GPC.DrugComplexMnn_id = :DrugComplexMnn_id ";

        $params['Org_id'] = $data['Org_id'];
        $where .= " and (isnull(GPC.Org_id, 0) = isnull(:Org_id, 0) or GPC.Org_id is null)";

		$query = "
			select
				GPC.GoodsPackCount_id,
				cast(GPC.GoodsPackCount_Count as float) as GoodsPackCount_Count,
				GPC.GoodsUnit_id,
				GPC.DrugComplexMnn_id,
				gu.GoodsUnit_Name,
				GPC.Org_id,
				isnull(o.Org_Name, 'Регион') as Org_Name
			from
				v_GoodsPackCount GPC with (nolock)
				left join v_GoodsUnit gu with (nolock) on gu.GoodsUnit_id = GPC.GoodsUnit_id
				left join v_Org o with (nolock) on o.Org_id = GPC.Org_id
			where
				(1=1)
				{$where}
			order by
				GPC.GoodsPackCount_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка количества товара в упаковке для грида в номенклатурной карточке
	 */
	function loadDrugPrepEdUcCountListGrid($data) {
		$params = array();
		$where = '';

		$params['Drug_id'] = $data['Drug_id'];
		$where .= " and D.Drug_id = :Drug_id ";

        $params['Org_id'] = $data['Org_id'];
    	$where .= " and (isnull(dpeuc.Org_id, 0) = isnull(:Org_id, 0) or dpeuc.Org_id is null)";

		$query = "
			select
				dpeuc.DrugPrepEdUcCount_id,
				dpeuc.DrugPrepEdUcCount_Count,
				dpeuc.DrugPrepFas_id,
				dpeuc.GoodsUnit_id,
				D.Drug_id,
				gu.GoodsUnit_Name,
				dpeuc.Org_id,
				isnull(o.Org_Name, 'Регион') as Org_Name
			from
				rls.v_DrugPrepEdUcCount dpeuc with (nolock)
				left join rls.v_Drug D with (nolock) on D.DrugPrepFas_id = dpeuc.DrugPrepFas_id
				left join v_GoodsUnit gu with (nolock) on gu.GoodsUnit_id = dpeuc.GoodsUnit_id
				left join v_Org o with (nolock) on o.Org_id = dpeuc.Org_id
			where
				(1=1)
				{$where}
			order by
				dpeuc.DrugPrepEdUcCount_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Сохранение записи справочника Количество товара в упаковке
	 */
	function saveDrugPrepEdUcCount($data) {
		if(!isset($data['DrugPrepFas_id'])){
			$query = "
				select DrugPrepFas_id
				from rls.v_Drug with (nolock)
				where Drug_id = :Drug_id
			";
			//echo getDebugSql($query, $data);exit;
			$res = $this->db->query($query, $data);
			if ( is_object($res) ) {
				$res = $res->result('array');
				$data['DrugPrepFas_id'] = $res[0]['DrugPrepFas_id'];
			} else {
				return array('success' => false);
			}
		}

		$data['Region_id'] = $this->getRegionNumber();

		if(!isset($data['DrugPrepEdUcCount_id'])){
			$query = "
				select
				    count(*) as cnt
				from
				    rls.v_DrugPrepEdUcCount with (nolock)
				where
				    Region_id = :Region_id and
				    DrugPrepFas_id = :DrugPrepFas_id and
				    GoodsUnit_id = :GoodsUnit_id and
				    DrugPrepEdUcCount_Count = :DrugPrepEdUcCount_Count and
				    isnull(Org_id, 0) = isnull(:Org_id, 0)
			";
			//echo getDebugSql($query, $data);exit;
			$res = $this->db->query($query, $data);
			if ( is_object($res) ) {
				$res = $res->result('array');
				if ($res[0]['cnt'] > 0) {
					return array('success' => false, 'Error_Code' => 400);
				}
			} else {
				return array('success' => false);
			}
		}
		
		$proc = 'rls.p_DrugPrepEdUcCount_'.(isset($data['DrugPrepEdUcCount_id']) > 0 ? 'upd' : 'ins');

		$query = "
			declare
				@DrugPrepEdUcCount_id bigint = :DrugPrepEdUcCount_id,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec {$proc}
				@DrugPrepEdUcCount_id = @DrugPrepEdUcCount_id output,
				@DrugPrepFas_id = :DrugPrepFas_id,
				@DrugPrepEdUcCount_Count = :DrugPrepEdUcCount_Count,
				@GoodsUnit_id = :GoodsUnit_id,
				@Org_id = :Org_id,
				@Region_id = :Region_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
            select @DrugPrepEdUcCount_id as DrugPrepEdUcCount_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$result = $result->result('array');
			if (isset($result[0]) && empty($result[0]['Error_Msg'])) {
				return array('success' => true);
			}
		}

		return array('success' => false);
	}

	/**
	 * Удаление записи справочника Количество товара в упаковке
	 */
	function deleteGoodsPackCount($data) {
		$query = "
			select count(*) as cnt
			from v_WhsDocumentProcurementRequestSpec with (nolock)
			where GoodsUnit_id = :GoodsUnit_id and DrugComplexMnn_id = :DrugComplexMnn_id and WhsDocumentProcurementRequestSpec_Count = :GoodsPackCount_Count
		";
		//echo getDebugSql($query, $data);exit;
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			$res = $res->result('array');
			if ($res[0]['cnt'] > 0) {
				return array('success' => false, 'Error_Code' => 400);
			}
		}
		
		$proc = 'dbo.p_GoodsPackCount_del';

		$query = "
			declare
				@GoodsPackCount_id bigint = :GoodsPackCount_id,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec {$proc}
				@GoodsPackCount_id = @GoodsPackCount_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
            select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$result = $result->result('array');
			if (isset($result[0]) && empty($result[0]['Error_Msg'])) {
				return array('success' => true);
			}
		}

		return array('success' => false);
	}

	/**
	 * Удаление записи справочника Количество товара в упаковке ЛП
	 */
	function deleteDrugPrepEdUcCount($data) {

		if(isset($data['Org_id'])){
			$query = "
				select isnull(Org_id,0) as Org_id 
				from rls.v_DrugPrepEdUcCount with (nolock)
				where DrugPrepEdUcCount_id = :DrugPrepEdUcCount_id
			";
			//echo getDebugSql($query, $data);exit;
			$res = $this->db->query($query, $data);
			if ( is_object($res) ) {
				$res = $res->result('array');
				if (!isSuperadmin() && $res[0]['Org_id'] != $data['Org_id']) {
					return array('success' => false, 'Error_Code' => 500);
				}
			}
		}
		
		$proc = 'rls.p_DrugPrepEdUcCount_del';

		$query = "
			declare
				@DrugPrepEdUcCount_id bigint = :DrugPrepEdUcCount_id,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec {$proc}
				@DrugPrepEdUcCount_id = @DrugPrepEdUcCount_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
            select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$result = $result->result('array');
			if (isset($result[0]) && empty($result[0]['Error_Msg'])) {
				return array('success' => true);
			}
		}

		return array('success' => false);
	}



    /**
     * Добавление данных в номенклатурный справочник
     * $object - наименование сущности
     * $id - идентификатор сущности
     *
     * возвращает id записи из таблицы справочника
     */
    function addNomenData($object, $id, $data) {
        $this->load->model('RlsDrug_model', 'RlsDrug_model');

        if (empty($object) || $id <= 0) {
            return null;
        }

        $code_tbl = null;
        $code_id = null;

        $object_array = array(
            'Drug' => array('code_tbl' => 'DrugNomen'),
            'TRADENAMES' => array('code_tbl' => 'DrugTorgCode'),
            'ACTMATTERS' => array('code_tbl' => 'DrugMnnCode'),
            'DrugComplexMnn' => array('code_tbl' => 'DrugComplexMnnCode')
        );

        if (!empty($object_array[$object])) {
            $code_tbl = $object_array[$object]['code_tbl'];

            if ($object == 'Drug') { //для медикамента нужно предварительно добавить код группировочного торгового, так как этот код участвует в формировании кода медикамента
                $this->addDrugPrepFasCodeByDrugId(array(
                    'Drug_id' => $id,
                    'pmUser_id' => $data['pmUser_id']
                ));
            }

            // Ищем запись в таблице номенклатурного справочника
            $query = "
                select
                    {$code_tbl}_id as code_id
                from
                    rls.v_{$code_tbl}
                where
                    {$object}_id = :id;
            ";
            $code_id = $this->getFirstResultFromQuery($query, array('id' => $id));

            if (empty($code_id)) { //добавляем запись в номенклатурный справочник
                //получаем новый код
                $new_code_data = $this->generateCodeForObject(array(
                    'Object' => $code_tbl,
                    'Drug_id' => $object == 'Drug' ? $id : null
                ));
                $new_code = !empty($new_code_data[0]) && !empty($new_code_data[0][$code_tbl.'_Code']) ? $new_code_data[0][$code_tbl.'_Code'] : null;

                if (!empty($new_code)) {
                    if ($object == 'Drug') {
                        //получаем информацию о медикаменте
                        $query = "
                            select
                                d.Drug_Name,
                                d.DrugTorg_Name,
                                d.DrugTorg_id as Tradenames_id,
                                DrugComplexMnnName.ActMatters_id as Actmatters_id,
                                dcm.DrugComplexMnn_id,
								A.STRONGGROUPID,
								A.NARCOGROUPID,
								P.NTFRID as CLSNTFR_ID,
								d.PrepType_id
                            from
                                rls.v_Drug d with (nolock)
                                left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
                                left join rls.DrugComplexMnnName with (nolock) on DrugComplexMnnName.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
                            	left join rls.v_ACTMATTERS A with (nolock) on A.Actmatters_id = DrugComplexMnnName.ActMatters_id
								left join rls.Prep P with (nolock) on P.Prep_id = d.DrugPrep_id
                            where
                                Drug_id = :id
                        ";

                        $drug_data = $this->getFirstRowFromQuery($query, array('id' => $id));

                        if (is_array($drug_data)) {
                            //добавляем запись в таблицу
                            $query = "
                                declare
                                    @{$code_tbl}_id bigint,
                                    @PrepClass_id bigint,
                                    @ErrCode int,
                                    @ErrMessage varchar(4000);

                                set @PrepClass_id = (select PrepClass_id from rls.v_PrepClass with (nolock) where PrepClass_Code = 2);
								if @PrepClass_id is null set @PrepClass_id = :PrepClass_id;

                                exec rls.p_{$code_tbl}_ins
                                    @{$code_tbl}_id = @{$code_tbl}_id output,
                                    @{$object}_id = :{$object}_id,
                                    @{$code_tbl}_Code = :{$code_tbl}_Code,
                                    @DrugNomen_Name = :DrugNomen_Name,
                                    @DrugNomen_Nick = :DrugNomen_Nick,
                                    @DrugTorgCode_id = :DrugTorgCode_id,
                                    @DrugMnnCode_id = :DrugMnnCode_id,
                                    @DrugNds_id = :nds_id,
									@Okei_id = :okei_id,
                                    @DrugComplexMnnCode_id = :DrugComplexMnnCode_id,
                                    @PrepClass_id = @PrepClass_id,
                                    @Region_id = null,
                                    @pmUser_id = :pmUser_id,
                                    @Error_Code = @ErrCode output,
                                    @Error_Message = @ErrMessage output;
                                    select @{$code_tbl}_id as {$code_tbl}_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
                            ";

                            $params = array();
                            $params['DrugNomen_Name'] = $drug_data['Drug_Name'];
                            $params['DrugNomen_Nick'] = $drug_data['DrugTorg_Name'];
                            $params['DrugTorgCode_id'] = $drug_data['Tradenames_id'] > 0 ? $this->addNomenData('TRADENAMES', $drug_data['Tradenames_id'], $data) : null;
                            $params['DrugMnnCode_id'] = $drug_data['Actmatters_id'] > 0 ? $this->addNomenData('ACTMATTERS', $drug_data['Actmatters_id'], $data) : null;
                            $params['DrugComplexMnnCode_id'] = $drug_data['DrugComplexMnn_id'] > 0 ? $this->addNomenData('DrugComplexMnn', $drug_data['DrugComplexMnn_id'], $data) : null;
                            $params['PrepClass_id'] = $this->RlsDrug_model->getDrugPrepClassId($drug_data);
                            $params[$object.'_id'] = $id;
                            $params[$code_tbl.'_Code'] = $new_code;
                            $params['pmUser_id'] = $data['pmUser_id'];

							$params['nds_id'] = !empty($data['DrugNds_id']) ? $data['DrugNds_id'] : null;
							$params['okei_id'] = !empty($data['Okei_id']) ? $data['Okei_id'] : null;

                            $result = $this->getFirstRowFromQuery($query, $params);
                            if (!empty($result)) {
                                $code_id = $result[$code_tbl.'_id'];
                            }
                        }
                    } else {
                        //добавляем запись в таблицу
                        $query = "
                            declare
                                @{$code_tbl}_id bigint,
                                @ErrCode int,
                                @ErrMessage varchar(4000);
                            exec rls.p_{$code_tbl}_ins
                                @{$code_tbl}_id = @{$code_tbl}_id output,
                                @{$object}_id = :{$object}_id,
                                @{$code_tbl}_Code = :{$code_tbl}_Code,
                                @Region_id = null,
                                @pmUser_id = :pmUser_id,
                                @Error_Code = @ErrCode output,
                                @Error_Message = @ErrMessage output;
                                select @{$code_tbl}_id as {$code_tbl}_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
                        ";

                        $params = array();
                        $params[$object.'_id'] = $id;
                        $params[$code_tbl.'_Code'] = $new_code;
                        $params['pmUser_id'] = $data['pmUser_id'];

                        $result = $this->getFirstRowFromQuery($query, $params);
                        if (!empty($result)) {
                            $code_id = $result[$code_tbl.'_id'];
                        }

                        if ($object == 'DrugComplexMnn') { //При добавлении в справочник комплексного МНН необходимо позаботится и о добавлении действующего вещества
                            //получаем информацию о комплексном МНН
                            $query = "
                                select
                                    DrugComplexMnnName.ActMatters_id as Actmatters_id
                                from
                                    rls.v_DrugComplexMnn with (nolock)
                                    left join rls.DrugComplexMnnName with (nolock) on DrugComplexMnnName.DrugComplexMnnName_id = v_DrugComplexMnn.DrugComplexMnnName_id
                                where
                                    DrugComplexMnn_id = :id;
                            ";
                            $dcm_data = $this->getFirstRowFromQuery($query, array('id' => $id));

                            if (!empty($dcm_data['Actmatters_id'])) {
                                $this->addNomenData('ACTMATTERS', $dcm_data['Actmatters_id'], $data);
                            }
                        }
                    }
                }
            }
        }
        return $code_id;
    }
}
