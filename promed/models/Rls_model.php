<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @author       Dmitry Storozhev
* @version      27.04.2011
*/

class Rls_model extends swModel 
{

    /**
     * Модель РЛС
     */
	function __construct() {
		parent::__construct();
	}

    /**
     * Серч дата
     */
	function searchData($data)
	{
		$filter = "(1 = 1)";
		$filter_RC = "(1 = 1)";
		$main_alias  = "";
		$queryParams = array();


		switch ($data['FormSign']) 
		{
			case 'tradenames':
				$filter .= " and TRADENAMES.NAME IS NOT NULL";
				
				$filter .= " and isnull(TRADENAMES_deleted, 1) <> 2";
				
				if(isset($data['KeyWord_filter']) && $data['KeyWord_filter'] != "") {
					$filter .= " and TRADENAMES.NAME LIKE :KeyWord_filter";
					$queryParams['KeyWord_filter'] = "%".$data['KeyWord_filter']."%";
				}
				if($data['id'] != "") {
					$filter .= " and TRADENAMES.TRADENAMES_ID = :id";
					$queryParams['id'] = $data['id'];
				}
				if(isset($data['RlsTorgNamesFilter_type'])) {
					switch ( $data['RlsTorgNamesFilter_type'] ) {
						case '':
						case 0:
							$filter .= "";
							break;
					
						case 1:
							$filter .= " and IDENT_WIND_STR.IWID IS NOT NULL";
							break;
						
						case 2:
							$filter .= " and (TN_DF_LIMP.TRADENAMEID IS NOT NULL)"; // AND AM_DF_LIMP.ACTMATTERID IS NOT NULL
							break;
						
						case 3:
							$filter .= " and (TRADENAMES_DRUGFORMS.TRADENAMEID IS NOT NULL OR ACTMATTERS_DRUGFORMS.ACTMATTERID IS NOT NULL)";
							break;
						
						case 4:
							$filter .= " and (TRADENAMES_DRUGFORMS.MZ_PHGR_ID = 61 OR ACTMATTERS_DRUGFORMS.MZ_PHGR_ID = 61)";
							break;
						
						case 5:
							$filter .= " and PREP.NTFRID = 1";
							break;
						
						case 6:
							$filter .= " and CLSPHARMAGROUP.CLSPHARMAGROUP_ID = 320";
							break;
					}
				}
				$field = "TRADENAMES.TRADENAMES_ID";
				$query = "
					select
						-- select
						TRADENAMES.TRADENAMES_ID as RlsTradename_id,
						TRADENAMES.NAME as RlsTradename_name
						-- end select
					from
						-- from
						rls.v_TRADENAMES TRADENAMES with (nolock)
						outer apply (
							select top 1 Prep_id, DRUGFORMID, NTFRID from rls.v_PREP with (nolock) where TRADENAMEID = TRADENAMES.TRADENAMES_ID
						) as PREP
						outer apply (
							select top 1 NOMEN_ID, PREPID from rls.v_NOMEN with (nolock) where PREPID = PREP.Prep_id
						) as NOMEN
						LEFT JOIN rls.IDENT_WIND_STR IDENT_WIND_STR on IDENT_WIND_STR.NOMENID = NOMEN.NOMEN_ID
						LEFT JOIN rls.v_CLSDRUGFORMS CLSDRUGFORMS with(nolock) on CLSDRUGFORMS.CLSDRUGFORMS_ID = PREP.DRUGFORMID
						outer apply (
							select top 1 PREPID, MATTERID from rls.PREP_ACTMATTERS with (nolock) where PREPID = PREP.Prep_id
						) as PREP_ACTMATTERS
						LEFT JOIN rls.v_ACTMATTERS ACTMATTERS with(nolock) on ACTMATTERS.ACTMATTERS_ID = PREP_ACTMATTERS.MATTERID
						LEFT JOIN rls.TN_DF_LIMP TN_DF_LIMP with(nolock) on TN_DF_LIMP.DRUGFORMID = CLSDRUGFORMS.CLSDRUGFORMS_ID
							and TN_DF_LIMP.TRADENAMEID = TRADENAMES.TRADENAMES_ID
						LEFT JOIN rls.AM_DF_LIMP AM_DF_LIMP with(nolock) on AM_DF_LIMP.ACTMATTERID = ACTMATTERS.ACTMATTERS_ID
							and AM_DF_LIMP.DRUGFORMID = CLSDRUGFORMS.CLSDRUGFORMS_ID
						LEFT JOIN rls.TRADENAMES_DRUGFORMS TRADENAMES_DRUGFORMS with(nolock) on TRADENAMES_DRUGFORMS.DRUGFORMID = CLSDRUGFORMS.CLSDRUGFORMS_ID
							and TRADENAMES_DRUGFORMS.TRADENAMEID = TRADENAMES.TRADENAMES_ID
						LEFT JOIN rls.ACTMATTERS_DRUGFORMS ACTMATTERS_DRUGFORMS with(nolock) on ACTMATTERS_DRUGFORMS.DRUGFORMID = CLSDRUGFORMS.CLSDRUGFORMS_ID
							and ACTMATTERS_DRUGFORMS.ACTMATTERID = ACTMATTERS.ACTMATTERS_ID
						outer apply(
							select top 1 PHGRID, PREPID from rls.v_PREP_IIC PREP_IIC with(nolock) where PREP_IIC.PREPID = PREP.Prep_id
						) as PREP_IIC
						LEFT JOIN rls.v_CLSPHARMAGROUP CLSPHARMAGROUP with(nolock) on CLSPHARMAGROUP.CLSPHARMAGROUP_ID = PREP_IIC.PHGRID
						-- end from
					where
						-- where
						{$filter}
						-- end where
					order by
						-- order by
						TRADENAMES.NAME
						-- end order by
				";
			break;
			
			case 'actmatters':
				$filter .= " and ACTMATTERS.RUSNAME IS NOT NULL";
				if(isset($data['KeyWord_filter']) && $data['KeyWord_filter'] != "")
				{
					$filter .= " and ACTMATTERS.RUSNAME LIKE :KeyWord_filter";
					$queryParams['KeyWord_filter'] = $data['KeyWord_filter']."%";
				}
				if($data['id'] != "")
				{
					$filter .= " and ACTMATTERS.ACTMATTERS_ID = :id";
					$queryParams['id'] = $data['id'];
				}
				
				$field = "ACTMATTERS.ACTMATTERS_ID";
				$query = "
					select
						-- select
						ACTMATTERS.ACTMATTERS_ID as RlsActmatters_id,
						ACTMATTERS.RUSNAME as RlsActmatters_RusName
						-- end select
					from
						-- from
						rls.v_ACTMATTERS ACTMATTERS with (nolock)
						-- end from
					where
						-- where
						{$filter}
						-- end where
					order by
						-- order by
						ACTMATTERS.RUSNAME
						-- end order by
				";
			break;
			
			case 'firms':
				$filter .= " and FIRMS.FULLNAME IS NOT NULL and FIRMS.FULLNAME != ''";
				if(isset($data['KeyWord_filter']) && $data['KeyWord_filter'] != "")
				{
					$filter .= " and FIRMS.FULLNAME LIKE :KeyWord_filter";
					$queryParams['KeyWord_filter'] = "%".$data['KeyWord_filter']."%";
				}
				if($data['id'] != "")
				{
					$filter .= " and FIRMS.FIRMS_ID = :id";
					$queryParams['id'] = $data['id'];
				}
				$field = "FIRMS.FIRMS_ID";
				$query = "
					select
						-- select
						FIRMS.FIRMS_ID as RlsFirms_id,
						FIRMS.FULLNAME as RlsFirms_name,
						COUNTRIES.COUNTRIES_ID as RlsCountries_id,
						COUNTRIES.NAME as RlsCountries_name
						-- end select
					from
						-- from
						rls.v_FIRMS FIRMS with (nolock)
						LEFT JOIN rls.v_COUNTRIES COUNTRIES with(nolock) on COUNTRIES.COUNTRIES_ID = FIRMS.COUNTID
						-- end from
					where
						-- where
						{$filter}
						-- end where
					order by
						-- order by
						FIRMS.FULLNAME
						-- end order by
				";
			break;
			
			case 'firmssearchform':
				$filter = '';
				if(!empty($data['Firm_Name'])){
					$filter .= " and RTRIM(case
							when F.FULLNAME is not null then F.FULLNAME
							else FN.NAME
						end) like :Firm_Name";
					$queryParams['Firm_Name'] = $data['Firm_Name']."%";
				}
				
				if(!empty($data['Firm_Address'])){
					$filter .= " and F.ADRMAIN like :Firm_Address";
					$queryParams['Firm_Address'] = "%".$data['Firm_Address']."%";
				}
				
				$field = "F.FIRMS_ID";
				$query = "
					select
						-- select
						F.FIRMS_ID,
						case
							when F.FULLNAME is not null then F.FULLNAME
							else FN.NAME
						end as FIRMS_NAME,
						F.ADRMAIN as FIRMS_ADRMAIN,
						F.ProducerType_id,
						FN.NAME
						-- end select
					from
						-- from
						rls.v_FIRMS F with(nolock)
						left join rls.v_FIRMNAMES FN with(nolock) on FN.FIRMNAMES_ID = F.NAMEID
						-- end from
					where
						-- where
						(FN.NAME is not null or F.FULLNAME is not null)
						and RTRIM(case
							when F.FULLNAME is not null then F.FULLNAME
							else FN.NAME
						end) != '' {$filter}
						-- end where
					order by
						-- order by
						FN.NAME
						-- end order by
				";
			break;
					
			default:
                $join = "";

				if(!empty($data['TRADENAMES_ID']))
				{
					$filter .= " and PREP.TRADENAMEID = :TRADENAMES_ID";
					$queryParams['TRADENAMES_ID'] = $data['TRADENAMES_ID'];
				}
				
				if(isset($data['RlsFirms_id']))
				{
					$filter .= " and FIRMS.FIRMS_ID = :RlsFirms_id";
					$queryParams['RlsFirms_id'] = $data['RlsFirms_id'];
				}
				
				if(isset($data['RlsActmatters_id']))
				{
					$filter .= " and ACTMATTERS.ACTMATTERS_ID = :RlsActmatters_id";
					$queryParams['RlsActmatters_id'] = $data['RlsActmatters_id'];
				}
			
				if(isset($data['RlsDesctextes_id']))
				{
					$filter .= " and DESCTEXTES.DESCID = :RlsDesctextes_id";
					$queryParams['RlsDesctextes_id'] = $data['RlsDesctextes_id'];
				}
				
				if(isset($data['RlsSynonim_id']))
				{
					$filter .= " and ACTMATTERS.ACTMATTERS_ID = :RlsSynonim_id";
					$queryParams['RlsSynonim_id'] = $data['RlsSynonim_id'];
				}
				
				if(isset($data['RlsCountries_id']))
				{
					$filter .= " and COUNTRIES.COUNTRIES_ID = :RlsCountries_id";
					$queryParams['RlsCountries_id'] = $data['RlsCountries_id'];
				}
				
				if(isset($data['RlsPharmagroup_id']))
				{
					$filter .= " and CLSPHARMAGROUP.CLSPHARMAGROUP_ID = :RlsPharmagroup_id";
					$queryParams['RlsPharmagroup_id'] = $data['RlsPharmagroup_id'];
				}
				
				if(isset($data['RlsClsiic_id']))
				{
					$filter .= " and CLSIIC.CLSIIC_ID = :RlsClsiic_id";
					$queryParams['RlsClsiic_id'] = $data['RlsClsiic_id'];
				}
				
				if(isset($data['RlsClsatc_id']))
				{
					$filter .= " and CLSATC.CLSATC_ID = :RlsClsatc_id";
					$queryParams['RlsClsatc_id'] = $data['RlsClsatc_id'];
				}

				if(isset($data['CLS_MZ_PHGROUP_ID']))
				{
					//Получение списка потомков для фильтрации по ним
					$q = "
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
					$result = $this->db->query($q, array(
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
							$filter .= " and TRADENAMES_DRUGFORMS.MZ_PHGR_ID in ({$mz_pg_str})";
						}
					}
				}
				
				if(isset($data['RlsClsdrugforms_id']))
				{
					$filter .= " and CLSDRUGFORMS.CLSDRUGFORMS_ID = :RlsClsdrugforms_id";
					$queryParams['RlsClsdrugforms_id'] = $data['RlsClsdrugforms_id'];
				}
				
				if(isset($data['RlsDaterange'][0]) && isset($data['RlsDaterange'][1]))
				{
					$filter .= " and REGCERT.REGDATE >= cast(:RlsDaterange_with as date) and REGCERT.REGDATE <= cast(:RlsDaterange_to as date)";
					$queryParams['RlsDaterange_with'] = $data['RlsDaterange'][0];
					$queryParams['RlsDaterange_to'] = $data['RlsDaterange'][1];
				}

				if(isset($data['RlsRegnum']) && !empty($data['RlsRegnum']))
				{
					$filter .= " and REGCERT.REGNUM like :RlsRegnum";
					$queryParams['RlsRegnum'] = '%'.$data['RlsRegnum'].'%';
				}

				if(isset($data['RlsRegOwnerFirm']) && !empty($data['RlsRegOwnerFirm']))
				{
                    $join .= " left join rls.REGCERT_EXTRAFIRMS RCEF with (nolock) on RCEF.CERTID = REGCERT.REGCERT_ID";
                    $join .= " left join rls.v_FIRMS RCEFF with(nolock) on RCEFF.FIRMS_ID = RCEF.FIRMID";
					$filter .= " and RCEFF.FULLNAME like :RlsRegOwnerFirm";
					$queryParams['RlsRegOwnerFirm'] = '%'.$data['RlsRegOwnerFirm'].'%';
				}

				if(isset($data['RlsProdFirm']) && !empty($data['RlsProdFirm']))
				{
					$filter .= " and FIRMS.FULLNAME like :RlsProdFirm";
					$queryParams['RlsProdFirm'] = '%'.$data['RlsProdFirm'].'%';
				}

				if(isset($data['RlsPackFirm']) && !empty($data['RlsPackFirm']))
				{
                    $join .= " left join rls.v_FIRMS NOMENF with(nolock) on NOMENF.FIRMS_ID = Nomen.FIRMID";
					$filter .= " and NOMENF.FULLNAME like :RlsPackFirm";
					$queryParams['RlsPackFirm'] = '%'.$data['RlsPackFirm'].'%';
				}

				if(!empty($data['RlsSearchKodEAN']))
				{
					$filter .= " and Nomen.EANCODE like :RlsSearchKodEAN";
					$queryParams['RlsSearchKodEAN'] = '%'.$data['RlsSearchKodEAN'].'%';
				}

				if(!empty($data['DrugNonpropNames_id']))
				{
					$filter .= " and PREP.DrugNonpropNames_id = :DrugNonpropNames_id";
					$queryParams['DrugNonpropNames_id'] = $data['DrugNonpropNames_id'];
				}

				if(isset($data['RlsDrug_Dose']))
				{
                    $PPACKVALUE = preg_replace("/[^\d]+/", "", $data['RlsDrug_Dose']);
                    $SHORTNAME = preg_replace("|[^a-zа-яё]|", "", $data['RlsDrug_Dose']);

                    if (!empty($PPACKVALUE) && !empty($SHORTNAME)) {

                        $filter .= " and ((
                                cast(cast(Nomen.PPACKMASS as float) as varchar(14)) = :PPACKVALUE and Nomen.PPACKMASS is not null
                                and MUS.SHORTNAME = :SHORTNAME and MUS.SHORTNAME is not null
                            ) or (
                                cast(cast(Nomen.PPACKVOLUME as float) as varchar(14)) = :PPACKVALUE and Nomen.PPACKVOLUME is not null
                                and CUS.SHORTNAME = :SHORTNAME and CUS.SHORTNAME is not null
                            ))
                        ";
                        $queryParams['PPACKVALUE'] = $PPACKVALUE;
                        $queryParams['SHORTNAME'] = $SHORTNAME;

                    } else if (!empty($PPACKVALUE)) {

                        $filter .= " and ((cast(cast(Nomen.PPACKMASS as float) as varchar(14)) = :PPACKVALUE and Nomen.PPACKMASS is not null)
                        or (cast(cast(Nomen.PPACKVOLUME as float) as varchar(14)) = :PPACKVALUE and Nomen.PPACKVOLUME is not null))
                        ";
                        $queryParams['PPACKVALUE'] = $PPACKVALUE;

                    } else if (!empty($SHORTNAME)) {

                        $filter .= " and ((MUS.SHORTNAME = :SHORTNAME and MUS.SHORTNAME is not null)
                        or (CUS.SHORTNAME = :SHORTNAME and CUS.SHORTNAME is not null))";
                        $queryParams['SHORTNAME'] = $SHORTNAME;

                    }
				}

				if($data['check_0_1'] == 1)
					$filter .= " and PREP.NORECIPE = 'Y'";
				
				if($data['check_0_2'] == 1)
					$filter .= " and (TRADENAMES_DRUGFORMS.MZ_PHGR_ID = 61 AND ACTMATTERS_DRUGFORMS.MZ_PHGR_ID = 61)";
				
				if($data['check_0_3'] == 1)
					$filter .= " and (TRADENAMES_DRUGFORMS.TRADENAMEID IS NOT NULL OR ACTMATTERS_DRUGFORMS.ACTMATTERID IS NOT NULL)";
				
				if($data['check_0_4'] == 1)
					$filter .= " and ACTMATTERS.NARCOGROUPID != 0";
				
				if($data['check_0_5'] == 1)
					$filter .= " and ACTMATTERS.STRONGGROUPID = 1";
				
				/*
				if($data['check_1_1'] == 1)
					$filter .= " and DESCTEXTES.COMPOSITION IS NOT NULL";
				
				if($data['check_1_2'] == 1)
					$filter .= " and DESCTEXTES.DRUGFORMDESCR IS NOT NULL";
				
				if($data['check_1_3'] == 1)
					$filter .= " and DESCTEXTES.CHARACTERS IS NOT NULL";
				
				if($data['check_1_4'] == 1)
					$filter .= " and DESCTEXTES.PHARMAACTIONS IS NOT NULL";
				
				if($data['check_1_5'] == 1)
					$filter .= " and DESCTEXTES.ACTONORG IS NOT NULL";
				
				if($data['check_1_6'] == 1)
					$filter .= " and DESCTEXTES.COMPONENTSPROPERTIES IS NOT NULL";
				
				if($data['check_1_7'] == 1)
					$filter .= " and DESCTEXTES.PHARMAKINETIC IS NOT NULL";
				
				if($data['check_1_8'] == 1)
					$filter .= " and DESCTEXTES.PHARMADYNAMIC IS NOT NULL";
				
				if($data['check_1_9'] == 1)
					$filter .= " and DESCTEXTES.CLINICALPHARMACOLOGY IS NOT NULL";
				
				if($data['check_2_1'] == 1)
					$filter .= " and DESCTEXTES.INDICATIONS IS NOT NULL";
				
				if($data['check_2_2'] == 1)
					$filter .= " and DESCTEXTES.RECOMMENDATIONS IS NOT NULL";
				
				if($data['check_2_3'] == 1)
					$filter .= " and DESCTEXTES.CONTRAINDICATIONS IS NOT NULL";
				
				if($data['check_2_4'] == 1)
					$filter .= " and DESCTEXTES.PREGNANCYUSE IS NOT NULL";
				
				if($data['check_2_5'] == 1)
					$filter .= " and DESCTEXTES.SIDEACTIONS IS NOT NULL";
				
				if($data['check_2_6'] == 1)
					$filter .= " and DESCTEXTES.INTERACTIONS IS NOT NULL";
				
				if($data['check_2_7'] == 1)
					$filter .= " and DESCTEXTES.USEMETHODANDDOSES IS NOT NULL";
				
				if($data['check_2_8'] == 1)
					$filter .= " and DESCTEXTES.INSTRFORPAC IS NOT NULL";
				
				if($data['check_2_9'] == 1)
					$filter .= " and DESCTEXTES.OVERDOSE IS NOT NULL";
				
				if($data['check_3_1'] == 1)
					$filter .= " and DESCTEXTES.PRECAUTIONS IS NOT NULL";
				
				if($data['check_3_2'] == 1)
					$filter .= " and DESCTEXTES.SPECIALGUIDELINES IS NOT NULL";
				
				if($data['check_3_3'] == 1)
					$filter .= " and DESCTEXTES.MANUFACTURER IS NOT NULL";
				
				if($data['check_3_4'] == 1)
					$filter .= " and DESCTEXTES.LITERATURE IS NOT NULL";
				
				if($data['check_3_5'] == 1)
					$filter .= " and DESCTEXTES.COMMENT IS NOT NULL";
				
				if($data['check_3_6'] == 1)
					$filter .= " and ACTMATTERS.PHARMACOLOGY IS NOT NULL";
				
				if($data['check_3_7'] == 1)
					$filter .= " and ACTMATTERS.USAGE IS NOT NULL";
				
				if($data['check_3_8'] == 1)
					$filter .= " and ACTMATTERS.USELIMITATIONS IS NOT NULL";
				*/
				
				if($data['sevennozology'] == 1)
					$filter .= " and CLSIIC.CLSIIC_ID in (1380, 1439, 1418, 1425, 1388, 1395, 1398, 1405, 1406, 1407, 1412, 1430, 2350, 1902, 1903, 1905, 2867, 2100, 11317, 11318, 11319, 11326)";
				
				if(isset($data['RlsSearchKeyWord']) && $data['RlsSearchKeyWord'] != "")
				{
					$filter .= " and TRADENAMES.NAME LIKE :RlsSearchKeyWord";
					$queryParams['RlsSearchKeyWord'] = "%".$data['RlsSearchKeyWord']."%";
				}
				
				//$filter .= " and isnull(Nomen.Nomen_deleted, 1) <> 2";
				
				$field = "Nomen.NOMEN_ID";
				/*$query = "
					select
						-- select
						Drug.Drug_id as RlsDrug_id,
						Drug.DrugTorg_id as RlsTorg_id,
						Drug.DrugTorg_Name as RlsTorg_Name,
						Drug.Drug_Nomen as RlsPack_Code,
						Drug.Drug_Firm as RlsFirms_Name,
						REGCERT.REGNUM as RlsRegcert_Number,
						convert(varchar(10), cast(REGCERT.REGDATE as datetime), 104) as RlsRegcert_Date,
						ACTMATTERS.RUSNAME as RlsActmatters_RusName,
						COUNTRIES.NAME as RlsCountries_Name
						-- end select
					from
						-- from
						rls.v_Drug Drug with (nolock)
						LEFT JOIN rls.v_PREP PREP with(nolock) on PREP.Prep_id = Drug.DrugPrep_id
						LEFT JOIN rls.v_REGCERT REGCERT with(nolock) on REGCERT.REGCERT_ID = PREP.REGCERTID
						LEFT JOIN rls.v_FIRMS FIRMS with(nolock) on FIRMS.FIRMS_ID = PREP.FIRMID
						LEFT JOIN rls.v_COUNTRIES COUNTRIES with(nolock) on COUNTRIES.COUNTRIES_ID = FIRMS.COUNTID
						
						outer apply (
							select top 1
								PREPID,	MATTERID
							from
								rls.PREP_ACTMATTERS with(nolock)
							where
								PREPID = PREP.Prep_id
						) as PREP_ACTMATTERS
						
						LEFT JOIN rls.v_ACTMATTERS ACTMATTERS with(nolock) on ACTMATTERS.ACTMATTERS_ID = PREP_ACTMATTERS.MATTERID
						LEFT JOIN rls.v_DESCRIPTIONS DESCRIPTIONS with(nolock) on DESCRIPTIONS.FIRMID = FIRMS.FIRMS_ID
						LEFT JOIN rls.v_DESCTEXTES DESCTEXTES with(nolock) on DESCTEXTES.DESCID = DESCRIPTIONS.DESCRIPTIONS_ID
						LEFT JOIN rls.v_PREP_IIC PREP_IIC with(nolock) on PREP_IIC.PREPID = PREP.Prep_id
						LEFT JOIN rls.v_CLSPHARMAGROUP CLSPHARMAGROUP with(nolock) on CLSPHARMAGROUP.CLSPHARMAGROUP_ID = PREP_IIC.PHGRID
						LEFT JOIN rls.v_CLSIIC CLSIIC with(nolock) on CLSIIC.CLSIIC_ID = PREP_IIC.UNIQID
						LEFT JOIN rls.v_PREP_ATC PREP_ATC with(nolock) on PREP_ATC.PREPID = PREP.Prep_id
						LEFT JOIN rls.v_CLSATC CLSATC with(nolock) on CLSATC.CLSATC_ID = PREP_ATC.UNIQID
						LEFT JOIN rls.v_CLSDRUGFORMS CLSDRUGFORMS with(nolock) on CLSDRUGFORMS.CLSDRUGFORMS_ID = PREP.DRUGFORMID
						LEFT JOIN rls.TRADENAMES_DRUGFORMS TRADENAMES_DRUGFORMS with(nolock) on TRADENAMES_DRUGFORMS.DRUGFORMID = CLSDRUGFORMS.CLSDRUGFORMS_ID
						
						outer apply(
							select top 1
								ACTMATTERS_DRUGFORMS.ACTMATTERID,
								ACTMATTERS_DRUGFORMS.MZ_PHGR_ID
							from
								rls.ACTMATTERS_DRUGFORMS ACTMATTERS_DRUGFORMS with(nolock)
							where
								ACTMATTERS_DRUGFORMS.ACTMATTERID = ACTMATTERS.ACTMATTERS_ID
						) as ACTMATTERS_DRUGFORMS						
						-- end from
					where
						-- where
						{$filter}
						-- end where
					order by
						-- order by
						Drug.DrugTorg_Name
						-- end order by
				";*/
				$query = "
					select
						-- select
						Nomen.NOMEN_ID as RlsNomen_id,
						PREP.Prep_id as RlsPrep_id,
						TRADENAMES.TRADENAMES_ID as RlsTorg_id,
						TRADENAMES.NAME as RlsTorg_Name,
						case when ltrim(RC.RlsPack_Code) != 'Признак АНГРО - нет' then RC.RlsPack_Code else TRADENAMES.NAME end as RlsPack_Code,
						(case when FIRMNAMES.NAME is not null then FIRMNAMES.NAME else FIRMS.FULLNAME end) as RlsFirms_Name,
						REGCERT.REGNUM as RlsRegcert_Number,
						convert(varchar(10), cast(REGCERT.REGDATE as datetime), 104) as RlsRegcert_Date,
						COUNTRIES.NAME as RlsCountries_Name,
						isnull(Nomen.PrepType_id, 1) as RlsPrepType_id,
						isnull(PREP.NTFRID, 1) as RlsNTFR_id
						-- end select
					from
						-- from
						rls.v_Nomen Nomen with (nolock)
						outer apply (
							select
								(DP.NAME + 
								(case when N.DRUGSINPPACK is not null and N.DRUGSINPPACK > 0 then cast(N.DRUGSINPPACK as varchar(10)) else '' end) + ' ' +
								(case when N.PPACKMASS is not null then cast(cast(N.PPACKMASS as float) as varchar(14)) else '' end)
								 + MU.SHORTNAME +  ' ' +
								(case when N.PPACKVOLUME is not null then cast(cast(N.PPACKVOLUME as float) as varchar(14)) + CU.SHORTNAME else '' end) +
								' ' + DS.SHORTNAME + ' ' + UPD.NAME + 
								(case when N.PPACKINUPACK is not null and N.PPACKINUPACK > 0 then cast(N.PPACKINUPACK as varchar(10)) else '' end) + ' ' + SPD.NAME + 
								(case when N.UPACKINSPACK is not null then cast(N.UPACKINSPACK as varchar(10)) else '' end) + ' Признак АНГРО - ' +
								(case when N.INANGRO = 'N' then 'нет' else 'да' end)) as RlsPack_Code
							from
								rls.v_Nomen N with (nolock)
								left join rls.v_DRUGPACK DP with(nolock) on DP.DRUGPACK_ID = N.PPACKID
								left join rls.v_MASSUNITS MU with(nolock) on MU.MASSUNITS_ID = N.PPACKMASSUNID
								left join rls.v_CUBICUNITS CU with(nolock) on CU.CUBICUNITS_ID = N.PPACKCUBUNID
								left join rls.v_DRUGSET DS with(nolock) on DS.DRUGSET_ID = N.SETID
								left join rls.v_DRUGPACK UPD with(nolock) on UPD.DRUGPACK_ID = N.UPACKID
								left join rls.v_DRUGPACK SPD with(nolock) on SPD.DRUGPACK_ID = N.SPACKID
							where
								N.NOMEN_ID = Nomen.NOMEN_ID
						) as RC
						outer apply(
							select top 1 NOMENID, DESCID from rls.NOMEN_DESC with (nolock) where NOMENID = Nomen.NOMEN_ID
						) as NOMEN_DESC
						--rls.v_Drug Drug with (nolock)
						LEFT JOIN rls.v_PREP PREP with(nolock) on PREP.Prep_id = Nomen.PREPID
						left join rls.v_MASSUNITS MUS with(nolock) on MUS.MASSUNITS_ID = Nomen.PPACKMASSUNID
						left join rls.v_CUBICUNITS CUS with(nolock) on CUS.CUBICUNITS_ID = Nomen.PPACKCUBUNID
						left join rls.v_TRADENAMES TRADENAMES with(nolock) on TRADENAMES.TRADENAMES_ID = PREP.TRADENAMEID
						LEFT JOIN rls.v_REGCERT REGCERT with(nolock) on REGCERT.REGCERT_ID = PREP.REGCERTID
				        LEFT JOIN rls.v_FIRMS FIRMS with(nolock) on FIRMS.FIRMS_ID = PREP.FIRMID
						left join rls.v_FIRMNAMES FIRMNAMES with(nolock) on FIRMNAMES.FIRMNAMES_ID = FIRMS.NAMEID --
						LEFT JOIN rls.v_COUNTRIES COUNTRIES with(nolock) on COUNTRIES.COUNTRIES_ID = FIRMS.COUNTID
						outer apply (
							select top 1 PREPID, MATTERID from rls.PREP_ACTMATTERS with (nolock) where PREPID = PREP.Prep_id
						) as PREP_ACTMATTERS
						LEFT JOIN rls.v_ACTMATTERS ACTMATTERS with(nolock) on ACTMATTERS.ACTMATTERS_ID = PREP_ACTMATTERS.MATTERID
						LEFT JOIN rls.v_DESCRIPTIONS DESCRIPTIONS with(nolock) on DESCRIPTIONS.DESCRIPTIONS_ID = NOMEN_DESC.DESCID
						LEFT JOIN rls.v_DESCTEXTES DESCTEXTES with(nolock) on DESCTEXTES.DESCID = DESCRIPTIONS.DESCRIPTIONS_ID
						outer apply (
							select top 1 PREPID, UNIQID, PHGRID from rls.PREP_IIC with (nolock) where PREPID = PREP.Prep_id
						) as PREP_IIC
						LEFT JOIN rls.v_CLSPHARMAGROUP CLSPHARMAGROUP with(nolock) on CLSPHARMAGROUP.CLSPHARMAGROUP_ID = PREP_IIC.PHGRID
						LEFT JOIN rls.v_CLSIIC CLSIIC with(nolock) on CLSIIC.CLSIIC_ID = PREP_IIC.UNIQID
						LEFT JOIN rls.v_PREP_ATC PREP_ATC with(nolock) on PREP_ATC.PREPID = PREP.Prep_id
						LEFT JOIN rls.v_CLSATC CLSATC with(nolock) on CLSATC.CLSATC_ID = PREP_ATC.UNIQID
						LEFT JOIN rls.v_CLSDRUGFORMS CLSDRUGFORMS with(nolock) on CLSDRUGFORMS.CLSDRUGFORMS_ID = PREP.DRUGFORMID
						LEFT JOIN rls.TRADENAMES_DRUGFORMS TRADENAMES_DRUGFORMS with(nolock) on TRADENAMES_DRUGFORMS.DRUGFORMID = CLSDRUGFORMS.CLSDRUGFORMS_ID
							and TRADENAMES.TRADENAMES_ID = TRADENAMES_DRUGFORMS.TRADENAMEID
						outer apply(
							select top 1 ACTMATTERS_DRUGFORMS.ACTMATTERID, ACTMATTERS_DRUGFORMS.MZ_PHGR_ID from
								rls.ACTMATTERS_DRUGFORMS ACTMATTERS_DRUGFORMS with (nolock)
							where ACTMATTERS_DRUGFORMS.ACTMATTERID = ACTMATTERS.ACTMATTERS_ID
						) as ACTMATTERS_DRUGFORMS
						{$join}
						-- end from
					where
						-- where
						{$filter}
						-- end where
					order by
						-- order by
						TRADENAMES.NAME
						-- end order by
				";
			break;
		}

		return $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true);
	}

    /**
     * Поулчаем структуру фармакологии РЛС?
     */
	function GetRlsPharmacologyStructure($data)
	{
		$filter = "(1 = 1)";
		
		if($data['node'] == 'all')
		{
			$data['node'] = 0;
			$filter .= " and CLSPHARMAGROUP.PARENTID = 0";
		}
		else
		{
			$filter .= " and CLSPHARMAGROUP.PARENTID = ".$data['node'];
		}
		$query = "
			select
				-- select
				CLSPHARMAGROUP.CLSPHARMAGROUP_ID as RlsPharmagroup_id,
				CLSPHARMAGROUP.NAME as RlsPharmagroup_name
				-- end select
			from
				-- from
				rls.v_CLSPHARMAGROUP CLSPHARMAGROUP with(nolock)
				-- end from
			where
				-- where
				{$filter} and CLSPHARMAGROUP.CLSPHARMAGROUP_ID != 0
				-- end where
			order by
				-- order by
				CLSPHARMAGROUP.NAME
				-- end order by
		";
		
		$res = $this->db->query($query);
		
		if ( is_object($res) )
		{
			return $res->result('array');
		}
		else 
		{
			return false;
		}
	}

    /**
     * Получаем структуру нозологии РЛС?
     */
	function GetRlsNozologyStructure($data)
	{
		$filter = "(1 = 1)";
		
		if($data['node'] == 'all')
		{
			$data['node'] = 0;
			$filter .= " and CLSIIC.PARENTID = 0";
		}
		else
		{
			$filter .= " and CLSIIC.PARENTID = ".$data['node'];
		}
		
		$query = "
			select
				-- select
				CLSIIC.CLSIIC_ID as RlsNozology_id,
				CLSIIC.NAME as RlsNozology_name
				-- end select
			from
				-- from
				rls.v_CLSIIC CLSIIC with(nolock)
				-- end from
			where
				-- where
				{$filter} and CLSIIC.CLSIIC_ID != 0
				-- end where
			order by
				-- order by
				CLSIIC.NAME
				-- end order by
		";
		
		$res = $this->db->query($query);
		
		if ( is_object($res) )
		{
			return $res->result('array');
		}
		else 
		{
			return false;
		}
	}

    /**
     * Получаем структуру АТХ РЛС?
     */
	function GetRlsAtxStructure($data)
	{
		$filter = "(1 = 1)";
		
		if($data['node'] == 'all')
		{
			$data['node'] = 0;
			$filter .= " and CLSATC.PARENTID = 0";
		}
		else
		{
			$filter .= " and CLSATC.PARENTID = ".$data['node'];
		}
		
		$query = "
			select
				-- select
				CLSATC.CLSATC_ID as RlsAtx_id,
				CLSATC.NAME as RlsAtx_name
				-- end select
			from
				-- from
				rls.v_CLSATC CLSATC with(nolock)
				-- end from
			where
				-- where
				{$filter} and CLSATC.CLSATC_ID != 0
				-- end where
			order by
				-- order by
				CLSATC.NAME
				-- end order by
		";
		
		$res = $this->db->query($query);
		
		if ( is_object($res) )
		{
			return $res->result('array');
		}
		else 
		{
			return false;
		}
	}

    /**
     * Определение количества дочерних элементов (для построения дерева)
     */
	function getCountChildElement($parent_id, $tabname, $fieldname)
	{
		$query = "
			select
				count(*) as cnt
			from
				".$tabname."
			where
				".$fieldname." = ".$parent_id."
		";
		$res = $this->db->query($query);
		
		if ( is_object($res) )
		{
			return $res->result('array');
		}
		else 
		{
			return false;
		}
	}

    /**
     * Получаем вид АТХ РЛС?
     */
	function GetRlsAtxView($data)
	{
		$query = "
			select distinct
				CLSATC.CLSATC_ID,
				CLSATC.NAME as RlsAtx_name,
				ACTMATTERS.ACTMATTERS_ID as RlsActmatter_id,
				ACTMATTERS.RUSNAME as RlsActmatter_name
			from
				rls.v_CLSATC CLSATC with(nolock)
				
				outer apply(
					select distinct
						PREP_ATC.PREPID,
						PREP_ATC.UNIQID
					from
						rls.PREP_ATC PREP_ATC with(nolock)
					where
						PREP_ATC.UNIQID = CLSATC.CLSATC_ID
				) as PREP_ATC
				LEFT JOIN rls.PREP_ACTMATTERS PREP_ACTMATTERS with(nolock) on PREP_ACTMATTERS.PREPID = PREP_ATC.PREPID
				LEFT JOIN rls.v_ACTMATTERS ACTMATTERS with(nolock) on ACTMATTERS.ACTMATTERS_ID = PREP_ACTMATTERS.MATTERID

			where
				CLSATC_ID = ".$data['node']."
		";
		$res = $this->db->query($query);
		
		if ( is_object($res) )
		{
			return $res->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 * Получение списка
	 */
	function GetRlsAtxList($data)
	{
		$params = array();
		$filters = '';

		if (!empty($data['maxCodeLength'])) {
			$filters .= " and charindex(' ',CLSATC.NAME) between 2 and :maxCodeLength+1";
			$params['maxCodeLength'] = $data['maxCodeLength'];
		}

		$query = "
			select
				CLSATC.CLSATC_ID as RlsClsatc_id,
				CLSATC.NAME as RlsClsatc_Name
			from rls.v_CLSATC CLSATC with(nolock)
			where
				CLSATC.CLSATC_ID <> 0
				{$filters}
			order by CLSATC.NAME
		";

		$result = $this->db->query($query,$params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка
	 */
	function GetRlsPharmagroupList($data)
	{
		$params = array();

		$query = "
			select
				CLSPHARMAGROUP_ID as RlsPharmagroup_id,
				NAME as RlsPharmagroup_Name
			from rls.v_CLSPHARMAGROUP t with(nolock)
			where t.CLSPHARMAGROUP_ID <> 0
			order by
				case when t.PARENTID <> 0 then t.PARENTID else t.CLSPHARMAGROUP_ID end,
				t.PARENTID,
				t.NAME
		";

		$result = $this->db->query($query,$params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка
	 */
	function GetRlsClsMzPhgroupList($data)
	{
		$params = array();

		$query = "
			SELECT
				t.CLS_MZ_PHGROUP_ID as RlsClsMzPhgroup_id,
				t.NAME as RlsClsMzPhgroup_Name
			FROM rls.v_CLS_MZ_PHGROUP t with(nolock)
			where t.CLS_MZ_PHGROUP_ID <> 0
			order by
				case when t.PARENTID <> 0 then t.PARENTID else t.CLS_MZ_PHGROUP_ID end,
				t.PARENTID,
				t.NAME
		";

		$result = $this->db->query($query,$params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

    /**
     * Получаем вид нозологии РЛС?
     */
	function GetRlsNozologyView($data)
	{
		$query = "
			select
				CLSIIC.CLSIIC_ID as RlsNozology_id,
				CLSIIC.NAME RlsNozology_name,
				CLSPHARMAGROUP.CLSPHARMAGROUP_ID as RlsPharmagroup_id,
				CLSPHARMAGROUP.NAME as RlsPharmagroup_name
			
			from
				rls.v_CLSIIC CLSIIC with(nolock)
				outer apply(
					select distinct
						PREP_IIC.UNIQID,
						PREP_IIC.PHGRID
					from
						rls.v_PREP_IIC PREP_IIC
					where
						PREP_IIC.UNIQID = CLSIIC.CLSIIC_ID
				) as PREP_IIC

				LEFT JOIN rls.v_CLSPHARMAGROUP CLSPHARMAGROUP with(nolock) on CLSPHARMAGROUP.CLSPHARMAGROUP_ID = PREP_IIC.PHGRID


			where
				CLSIIC.CLSIIC_ID = ".$data['node']."
			order by CLSPHARMAGROUP.NAME
		";
		
		$res = $this->db->query($query);
		
		if ( is_object($res) )
		{
			return $res->result('array');
		}
		else
		{
			return false;
		}
	}

    /**
     * Получаем синонимы для данной нозологической группы
     */
	function GetRlsSynonimsforNozology($iic_id)
	{
		$query = "
			select
				IICSYNONYMS.IICSYNONYMS_ID as RlsSynonimNozology_id,
				IICSYNONYMS.NAME as RlsSynonimNozology_name
			from
				rls.v_IICSYNONYMS IICSYNONYMS with(nolock)
		
				outer apply(
					select distinct
						SYNON_IIC.IIC_ID,
						SYNON_IIC.SYNON_ID
					from
						rls.SYNON_IIC SYNON_IIC
					where
						SYNON_IIC.SYNON_ID = IICSYNONYMS.IICSYNONYMS_ID
				) as SYNON_IIC
			where
				SYNON_IIC.IIC_ID = ".$iic_id."
			order by IICSYNONYMS.NAME
		";
		$res = $this->db->query($query);
		
		if ( is_object($res) )
		{
			return $res->result('array');
		}
		else
		{
			return false;
		}
	}

    /**
     * Получаем все действующие вещества для данной фармгруппы в соответствующей нозол. группе
     */
	function GetRlsAcmattersOnPharmagroup($RlsPharmagroup_id, $iic_id)
	{
		$query = "
			select
				ACTMATTERS.ACTMATTERS_ID as RlsActmatter_id,
				ACTMATTERS.RUSNAME as RlsActmatter_name
			from
				rls.v_ACTMATTERS ACTMATTERS with(nolock)
				
				outer apply(
					select distinct
						ACTMAT_PHGR.MATTERID,
						ACTMAT_PHGR.UNIQID
					from
						rls.ACTMAT_PHGR ACTMAT_PHGR with(nolock)
					where
						ACTMAT_PHGR.MATTERID = ACTMATTERS.ACTMATTERS_ID
				) as ACTMAT_PHGR
				
				LEFT JOIN rls.v_CLSPHARMAGROUP CLSPHARMAGROUP with(nolock) on CLSPHARMAGROUP.CLSPHARMAGROUP_ID = ACTMAT_PHGR.UNIQID
				outer apply(
					select distinct
						PREP_IIC.UNIQID,
						PREP_IIC.PHGRID
					from
						rls.v_PREP_IIC PREP_IIC with(nolock)
					where
						PREP_IIC.PHGRID = CLSPHARMAGROUP.CLSPHARMAGROUP_ID
				) as PREP_IIC
			
				LEFT JOIN rls.v_CLSIIC CLSIIC with(nolock) on CLSIIC.CLSIIC_ID = PREP_IIC.UNIQID
			where
				CLSPHARMAGROUP.CLSPHARMAGROUP_ID = ".$RlsPharmagroup_id." and 
				CLSIIC.CLSIIC_ID = ".$iic_id."
			order by ACTMATTERS.RUSNAME
		";
		
		$res = $this->db->query($query);
		
		if ( is_object($res) )
		{
			return $res->result('array');
		}
		else
		{
			return false;
		}
	}

    /**
     * Получаем торговые названия препаратов для данного действующего вещества
     */
	function GetRlsTradenamesOnActmatters($act_id)
	{
		$query = "
			select distinct
				Drug.DrugTorg_id as RlsTorgNames_id,
				Drug.DrugTorg_Name as RlsTorgNames_name
			from
				rls.v_Drug Drug with(nolock)
				
				LEFT JOIN rls.PREP_ACTMATTERS PREP_ACTMATTERS with(nolock) on PREP_ACTMATTERS.PREPID = Drug.DrugPrep_id
				LEFT JOIN rls.v_ACTMATTERS ACTMATTERS with(nolock) on ACTMATTERS.ACTMATTERS_ID = PREP_ACTMATTERS.MATTERID
				
			where
				ACTMATTERS.ACTMATTERS_ID = ".$act_id."
			order by
				Drug.DrugTorg_Name
		";
		
		$res = $this->db->query($query);
		
		if ( is_object($res) )
		{
			return $res->result('array');
		}
		else
		{
			return false;
		}
	}

    /**
     * Получаем действующие вещества для данного торгового названия препарата
     */
	function GetRlsActmattersOnTradenames($torg_id)
	{
		$query = "
			select distinct
				ACTMATTERS.ACTMATTERS_ID as RlsActmatter_id,
				ACTMATTERS.RUSNAME as RlsActmatter_name
			from
				rls.v_ACTMATTERS ACTMATTERS with(nolock)
				
				LEFT JOIN rls.PREP_ACTMATTERS PREP_ACTMATTERS with(nolock) on PREP_ACTMATTERS.MATTERID = ACTMATTERS.ACTMATTERS_ID
				LEFT JOIN rls.v_Drug Drug with(nolock) on Drug.DrugPrep_id = PREP_ACTMATTERS.PREPID
				
			where
				Drug.DrugTorg_id = ".$torg_id."
			order by
				ACTMATTERS.RUSNAME
		";
		
		$res = $this->db->query($query);
		
		if ( is_object($res) )
		{
			return $res->result('array');
		}
		else
		{
			return false;
		}
	}

    /**
     * Получаем вид фармакологии РЛС
     */
	function GetRlsPharmacologyView($data)
	{
		switch ($data['view'])
		{
			case 1:
				$query = "
					select
						CLSPHARMAGROUP.CLSPHARMAGROUP_ID as RlsPharmagroup_id,
						isnull(CLSPHARMAGROUP.NAME, '') as RlsPharmagroup_name
					from
						rls.v_CLSPHARMAGROUP CLSPHARMAGROUP with(nolock)
					where
						CLSPHARMAGROUP.CLSPHARMAGROUP_ID != 0 and CLSPHARMAGROUP.CLSPHARMAGROUP_ID = ".$data['node']."
				";
			break;
			
			case 2:
				$query = "
					select distinct
						Drug.DrugTorg_id as RlsTorgNames_id,
						isnull(Drug.DrugTorg_Name, '') as RlsTorgNames_name
					from
						rls.v_CLSPHARMAGROUP CLSPHARMAGROUP with(nolock)
			
						LEFT JOIN rls.v_PREP_IIC PREP_IIC with(nolock) on PREP_IIC.PHGRID = CLSPHARMAGROUP.CLSPHARMAGROUP_ID
						LEFT JOIN rls.v_Drug Drug with(nolock) on Drug.DrugPrep_id = PREP_IIC.PREPID	
					where
						CLSPHARMAGROUP.CLSPHARMAGROUP_ID != 0 and CLSPHARMAGROUP.CLSPHARMAGROUP_ID = ".$data['node']." and (PREP_IIC.PREPID is null or Drug.DrugPrep_id is not null)
					order by isnull(Drug.DrugTorg_Name, '')
				";
			break;
		}
		
		$res = $this->db->query($query);
		
		if ( is_object($res) )
		{
			return $res->result('array');
		}
		else
		{
			return false;
		}
	}

    /**
     * Получаем вид активного вещества РЛС?
     */
	function GetRlsActmattersView($data)
	{
		$query = "
			select distinct
				ACTMATTERS.ACTMATTERS_ID as RlsActmatter_id,
				ACTMATTERS.RUSNAME as RlsActmatter_rusname,
				ACTMATTERS.LATNAME as RlsActmatter_latname,
				STRONGGROUPS.NAME as RlsStronggroups,
				NARCOGROUPS.NAME as RlsNarcogroups,
				AM_DF_LIMP.ACTMATTERID as RlsVital,
				ACTMATTERS_DRUGFORMS.ACTMATTERID as RlsPreferential
			from
				rls.v_ACTMATTERS ACTMATTERS with(nolock)
				LEFT JOIN rls.v_STRONGGROUPS STRONGGROUPS with(nolock) on STRONGGROUPS.STRONGGROUPS_ID = ACTMATTERS.STRONGGROUPID
				LEFT JOIN rls.v_NARCOGROUPS NARCOGROUPS with(nolock) on NARCOGROUPS.NARCOGROUPS_ID = ACTMATTERS.NARCOGROUPID
				LEFT JOIN rls.AM_DF_LIMP AM_DF_LIMP with(nolock) on AM_DF_LIMP.ACTMATTERID = ACTMATTERS.ACTMATTERS_ID
				LEFT JOIN rls.ACTMATTERS_DRUGFORMS ACTMATTERS_DRUGFORMS with(nolock) on ACTMATTERS_DRUGFORMS.ACTMATTERID = ACTMATTERS.ACTMATTERS_ID
			where
				ACTMATTERS.ACTMATTERS_ID = ".$data['id']."
		";

		$res = $this->db->query($query);
		
		if ( is_object($res) )
		{
			return $res->result('array');
		}
		else
		{
			return false;
		}
	}

    /**
     * Получаем все фармгруппы для данного действующего вещества
     */
	function GetRlsPharmaonActmatter($act_id)
	{
		$query = "
			select distinct
				CLSPHARMAGROUP.CLSPHARMAGROUP_ID as RlsPharmagroup_id,
				CLSPHARMAGROUP.NAME as RlsPharmagroup_name
			from
				rls.v_CLSPHARMAGROUP CLSPHARMAGROUP with(nolock)
				LEFT JOIN rls.ACTMAT_PHGR ACTMAT_PHGR with(nolock) on ACTMAT_PHGR.UNIQID = CLSPHARMAGROUP.CLSPHARMAGROUP_ID
			where
				ACTMAT_PHGR.MATTERID = ".$act_id."
		";
		$res = $this->db->query($query);
		
		if ( is_object($res) )
		{
			return $res->result('array');
		}
		else
		{
			return false;
		}
	}

    /**
     * Получаем все нозологические группы для данного действующего вещества
     */
	function GetRlsNozologyonActmatter($act_id)
	{
		$query = "
			select distinct
				CLSIIC.CLSIIC_ID as RlsNozology_id,
				CLSIIC.NAME as RlsNozology_name
			from
				rls.v_CLSIIC CLSIIC with(nolock)
				
				LEFT JOIN rls.v_PREP_IIC PREP_IIC with(nolock) on PREP_IIC.UNIQID = CLSIIC.CLSIIC_ID
				LEFT JOIN rls.PREP_ACTMATTERS PREP_ACTMATTERS with(nolock) on PREP_ACTMATTERS.PREPID = PREP_IIC.PREPID
				LEFT JOIN rls.v_ACTMATTERS ACTMATTERS with(nolock) on ACTMATTERS.ACTMATTERS_ID = PREP_ACTMATTERS.MATTERID
			where
				ACTMATTERS.ACTMATTERS_ID = ".$act_id."
		";
		$res = $this->db->query($query);
		
		if ( is_object($res) )
		{
			return $res->result('array');
		}
		else
		{
			return false;
		}
	}

    /**
     * Получаем название фирмы для вида?
     */
	function GetRlsFirmsView($data)
	{
		$query = "
			select
				FIRMS.FIRMS_ID as RlsFirms_id,
				FIRMS.FULLNAME as RlsFirms_name,
				COUNTRIES.COUNTRIES_ID as RlsCountries_id,
				COUNTRIES.NAME as RlsCountries_name,
				isnull(FIRMS.ADRMAIN, '') as RlsFirms_addr,
				isnull(FIRMS.ADRRUSSIA, '') as RlsFirms_addr_rus,
				isnull(FIRMS.ADRUSSR, '') as RlsFirms_addr_ussr
			from
				rls.v_FIRMS FIRMS with (nolock)
				LEFT JOIN rls.v_COUNTRIES COUNTRIES with(nolock) on COUNTRIES.COUNTRIES_ID = FIRMS.COUNTID
			where
				FIRMS.FIRMS_ID = ".$data['id']."
			order by
				FIRMS.FULLNAME
		";
		
		$res = $this->db->query($query);
		
		if ( is_object($res) )
		{
			return $res->result('array');
		}
		else
		{
			return false;
		}
	}

    /**
     * Получаем все торговые названия препаратов, принадлежащие данному производителю
     */
	function GetRlsTradenamesonFirm($data)
	{
		$query = "
			select distinct
				TRADENAMES.TRADENAMES_ID as RlsTorgNames_id,
				TRADENAMES.NAME as RlsTorgNames_name
			from
				rls.v_TRADENAMES TRADENAMES with(nolock)
				LEFT JOIN rls.v_PREP PREP with(nolock) on PREP.TRADENAMEID = TRADENAMES.TRADENAMES_ID
				LEFT JOIN rls.v_FIRMS FIRMS with(nolock) on FIRMS.FIRMS_ID = PREP.FIRMID	
			where
				FIRMS.FIRMS_ID = ".$data['id']."
			order by
				TRADENAMES.NAME
		";
		$res = $this->db->query($query);
		
		if ( is_object($res) )
		{
			return $res->result('array');
		}
		else
		{
			return false;
		}
	}

    /**
     * Получаем пак код
     */
	function GetRlsPackCode($data)
	{
		$filter = '1=1';
		if(isset($data['id'])) {
			$filter .= " and TRADENAMES.TRADENAMES_ID = :id";
		}
		//$filter .= " and isnull(NOMEN.Nomen_deleted, 1) <> 2";
		$query = "
			select-- distinct
				NOMEN.NOMEN_id as RlsPack_id,
				case when ltrim(RC.RlsPack_Code) != '' then RC.RlsPack_Code else TRADENAMES.NAME end as RlsPack_Code
			from
				rls.v_Nomen NOMEN with(nolock)
				outer apply (
					select
						(DP.NAME + 
						(case when N.DRUGSINPPACK is not null and N.DRUGSINPPACK > 0 then cast(N.DRUGSINPPACK as varchar(10)) else '' end) + ' ' +
						(case when N.PPACKMASS is not null then cast(cast(N.PPACKMASS as float) as varchar(14)) else '' end)
						 + MU.SHORTNAME +  ' ' +
						(case when N.PPACKVOLUME is not null then cast(cast(N.PPACKVOLUME as float) as varchar(14)) + CU.SHORTNAME else '' end) +
						' ' + DS.SHORTNAME + ' ' + UPD.NAME + 
						(case when N.PPACKINUPACK is not null and N.PPACKINUPACK > 0 then cast(N.PPACKINUPACK as varchar(10)) else '' end) + ' ' + SPD.NAME + 
						(case when N.UPACKINSPACK is not null then cast(N.UPACKINSPACK as varchar(10)) else '' end)) as RlsPack_Code
					from
						rls.v_Nomen N with (nolock)
						left join rls.v_DRUGPACK DP with(nolock) on DP.DRUGPACK_ID = N.PPACKID
						left join rls.v_MASSUNITS MU with(nolock) on MU.MASSUNITS_ID = N.PPACKMASSUNID
						left join rls.v_CUBICUNITS CU with(nolock) on CU.CUBICUNITS_ID = N.PPACKCUBUNID
						left join rls.v_DRUGSET DS with(nolock) on DS.DRUGSET_ID = N.SETID
						left join rls.v_DRUGPACK UPD with(nolock) on UPD.DRUGPACK_ID = N.UPACKID
						left join rls.v_DRUGPACK SPD with(nolock) on SPD.DRUGPACK_ID = N.SPACKID
					where
						N.NOMEN_ID = NOMEN.NOMEN_ID
				) as RC
				left join rls.v_Prep PREP with(nolock) on PREP.Prep_id = NOMEN.PREPID
				left join rls.v_TRADENAMES TRADENAMES with(nolock) on TRADENAMES.TRADENAMES_ID = PREP.TRADENAMEID
				left join rls.IDENT_WIND_STR IWS with(nolock) on IWS.NOMENID = NOMEN.NOMEN_id
			where
				{$filter}
			order by
				IWS.IDENT_WIND_STR_id desc
		";
		
		$res = $this->db->query($query, $data);
		
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

    /**
     * Получаем торговый вид?
     */
	function GetRlsTorgView($data)
	{
		$query = "
			select top 1
				NOMEN.NOMEN_ID as RlsUniqueCode,
				FIRMS.FULLNAME as RlsFirm_Name,
				Prep.Prep_id as RlsPrep_id,
				TRADENAMES.NAME as RlsPrep_Name,
				case when len(LATINNAMES.NAME) > 0 then LATINNAMES.NAME else null end as RlsPrep_Latname,
				COUNTRIES.NAME as RlsCountrie_Name,
				isnull(ACTMATTERS.ACTMATTERS_ID, '') as RlsActmatter_id,
				ACTMATTERS.RUSNAME as RlsActmatter_Name,
				ACTMATTERS.LATNAME as RlsActmatter_Latname,
				REGCERT.REGNUM as RlsRegNum,
				convert(varchar(10), cast(REGCERT.REGDATE as datetime), 104) as RlsRegDate,
				ltrim(case when REGCERT.REGNUM is not null then REGCERT.REGNUM else '' end + 
					case when REGCERT.REGDATE is not null then ' от ' + convert(varchar(10), cast(REGCERT.REGDATE as datetime), 104) else '' end +
					case when FIRMS.FULLNAME is not null then ' ' + FIRMS.FULLNAME else '' end +
					case when COUNTRIES.NAME is not null then ' [' + COUNTRIES.NAME + ']' else '' end
				) as GosRegistrNumber,
				case when IDENT_WIND_STR.IWID is not null then '".DRUGSPATH."' + cast(IDENT_WIND_STR.IWID as varchar(10)) + '.gif' else null end as image_url, 
				CLSATC.CLSATC_ID as RlsAtc_id,
				CLSATC.NAME as RlsAtc_name,
				case when len(DRUGLIFETIME.TEXT) > 0 then DRUGLIFETIME.TEXT else null end as RlsPrep_lifetime,
				case when len(DRUGSTORCOND.TEXT) > 0 then DRUGSTORCOND.TEXT else null end as RlsPrep_cond,
				case when len(NOMEN.EANCODE) > 0 then NOMEN.EANCODE else null end as RlsPrep_eancode,
				cast(cast(DESCTEXTES.COMPOSITION as varbinary(max)) as varchar(max)) as RlsPrep_composition,
				cast(cast(DESCTEXTES.CHARACTERS as varbinary(max)) as varchar(max)) as RlsPrep_characters,
				case when len(DESCTEXTES.PHARMAACTIONS) > 0 then cast(cast(DESCTEXTES.PHARMAACTIONS as varbinary(max)) as varchar(max)) else null end as RlsPrep_pharmaactions,
				case when len(DESCTEXTES.ACTONORG) > 0 then cast(cast(DESCTEXTES.ACTONORG as varbinary(max)) as varchar(max)) else null end as RlsPrep_actonorg,
				case when len(DESCTEXTES.COMPONENTSPROPERTIES) > 0 then cast(cast(DESCTEXTES.COMPONENTSPROPERTIES as varbinary(max)) as varchar(max)) else null end as RlsPrep_compproperties,
				DESCTEXTES.PHARMAKINETIC as RlsPharmakinetic,
				DESCTEXTES.PHARMADYNAMIC as RlsPharmadynamic,
				case when len(DESCTEXTES.CLINICALPHARMACOLOGY) > 0 then DESCTEXTES.CLINICALPHARMACOLOGY else null end as RlsClinicalPharmacology,
				DESCTEXTES.DIRECTION as RlsDirection,
				DESCTEXTES.INTERACTIONS as RlsInteractions,
				cast(cast(DESCTEXTES.RECOMMENDATIONS as varbinary(max)) as varchar(max)) as RlsPrep_recommendations,
				cast(cast(DESCTEXTES.CONTRAINDICATIONS as varbinary(max)) as varchar(max)) as RlsPrep_contraindications,
				case when len(DESCTEXTES.PREGNANCYUSE) > 0 then cast(cast(DESCTEXTES.PREGNANCYUSE as varbinary(max)) as varchar(max)) else null end as RlsPrep_pregnancyuse,
				cast(cast(DESCTEXTES.SIDEACTIONS as varbinary(max)) as varchar(max)) as RlsPrep_sideactions,
				cast(cast(DESCTEXTES.USEMETHODANDDOSES as varbinary(max)) as varchar(max)) as RlsPrep_usemethodanddoses,
				case when len(DESCTEXTES.INSTRFORPAC) > 0 then DESCTEXTES.INSTRFORPAC else null end as RlsInstrforPac,
				case when len(DESCTEXTES.OVERDOSE) > 0 then DESCTEXTES.OVERDOSE else null end as RlsOverdose,
				cast(cast(DESCTEXTES.PRECAUTIONS as varbinary(max)) as varchar(max)) as RlsPrep_precautions,
				DESCTEXTES.SPECIALGUIDELINES as RlsSpecialguidelines
			from
				rls.v_Prep Prep with(nolock)
				LEFT JOIN rls.v_TRADENAMES TRADENAMES with(nolock) on TRADENAMES.TRADENAMES_ID = Prep.TRADENAMEID
				LEFT JOIN rls.v_FIRMS FIRMS with(nolock) on FIRMS.FIRMS_ID = Prep.FIRMID
				LEFT JOIN rls.v_LATINNAMES LATINNAMES with(nolock) on LATINNAMES.LATINNAMES_ID = Prep.LATINNAMEID
				LEFT JOIN rls.v_COUNTRIES COUNTRIES with(nolock) on COUNTRIES.COUNTRIES_ID = FIRMS.COUNTID
				LEFT JOIN rls.PREP_ACTMATTERS PREP_ACTMATTERS with(nolock) on PREP_ACTMATTERS.PREPID = Prep.Prep_id
				LEFT JOIN rls.v_ACTMATTERS ACTMATTERS with(nolock) on ACTMATTERS.ACTMATTERS_ID = PREP_ACTMATTERS.MATTERID
				LEFT JOIN rls.v_REGCERT REGCERT with(nolock) on REGCERT.REGCERT_ID = Prep.REGCERTID
				LEFT JOIN rls.v_NOMEN NOMEN with(nolock) on NOMEN.PREPID = Prep.Prep_id
				LEFT JOIN rls.IDENT_WIND_STR IDENT_WIND_STR with(nolock) on IDENT_WIND_STR.NOMENID = NOMEN.NOMEN_ID
				LEFT JOIN rls.v_PREP_ATC PREP_ATC with(nolock) on PREP_ATC.PREPID = Prep.Prep_id
				LEFT JOIN rls.v_CLSATC CLSATC with(nolock) on CLSATC.CLSATC_ID = PREP_ATC.UNIQID
				LEFT JOIN rls.v_DRUGLIFETIME DRUGLIFETIME with(nolock) on DRUGLIFETIME.DRUGLIFETIME_ID = NOMEN.LIFEID
				LEFT JOIN rls.DRUGSTORCOND DRUGSTORCOND with(nolock) on DRUGSTORCOND.DRUGSTORCOND_ID = NOMEN.CONDID
				LEFT JOIN rls.NOMEN_DESC NOMEN_DESC with(nolock) on NOMEN_DESC.NOMENID = NOMEN.NOMEN_ID
				LEFT JOIN rls.v_DESCRIPTIONS DESCRIPTIONS with(nolock) on DESCRIPTIONS.DESCRIPTIONS_ID = NOMEN_DESC.DESCID
				LEFT JOIN rls.v_DESCTEXTES DESCTEXTES with(nolock) on DESCTEXTES.DESCID = DESCRIPTIONS.DESCRIPTIONS_ID
			where
				NOMEN.NOMEN_ID = :NOMEN_ID
			order by
				IDENT_WIND_STR.IWID desc, cast(cast(DESCTEXTES.COMPOSITION as varbinary(max)) as varchar(max)) desc
		";

        //echo getDebugSQL($query, $data); die;
		$res = $this->db->query($query, $data);
		
		if ( is_object($res) )	{
			return $res->result('array');
		} else {
			return false;
		}
	}
	

    /**
     * Получаем названия фармгрупп, к которым относится препарат
     */
    function GetPharmagrouponPrep($data)
	{
		$query = "
			select distinct	
				CLSPHARMAGROUP.CLSPHARMAGROUP_ID as RlsPharmagroup_id,
				CLSPHARMAGROUP.NAME as RlsPharmagroup_Name
			from
				rls.v_CLSPHARMAGROUP CLSPHARMAGROUP with(nolock)
				LEFT JOIN rls.v_PREP_PHARMAGROUP PREP_PHARMAGROUP with(nolock) on PREP_PHARMAGROUP.UNIQID = CLSPHARMAGROUP.CLSPHARMAGROUP_ID
			where
				PREP_PHARMAGROUP.PREPID = ".$data."
		";
	
		$res = $this->db->query($query);
		
		if ( is_object($res) )
		{
			return $res->result('array');
		}
		else
		{
			return false;
		}
	}
	
	/**
     * Получаем нозологические группы к которым относится препарат
     */
	function GetNozolonPrep($data)
	{
		$query = "
			select distinct
				CLSIIC.CLSIIC_ID as RlsNozology_id,
				CLSIIC.NAME as RlsNozology_name
			from
				rls.v_CLSIIC CLSIIC with(nolock)
				LEFT JOIN rls.v_PREP_IIC PREP_IIC with(nolock) on PREP_IIC.UNIQID = CLSIIC.CLSIIC_ID
			where 
				PREP_IIC.PREPID = ".$data."
		";
		$res = $this->db->query($query);
		
		if ( is_object($res) )
		{
			return $res->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
     * получаем родителя для нода
     */
	function defineparent($parent_id, $tabname, $fieldname)
	{
		$query = "
			select
				PARENTID as parent_id
			from
				".$tabname."
			where
				".$fieldname." = ".$parent_id."
		";
		
		$res = $this->db->query($query);
		$res->result('array');
		return $res->result_array[0]['parent_id'];	
	}

	/**
     * Получаем родительский нод
     */
	function GetParentNode($id, $tabname, $fieldname)
	{
		$parent_id = array();
		$i = 0;
		do {
			if($i == 0)	{
				$parent_id[$i] = $this->defineparent($id, $tabname, $fieldname);
			} else {
				$parent_id[$i] = $this->defineparent($j, $tabname, $fieldname);
			}
			$j = $parent_id[$i];
			$i++;
		} while ($j != 0);
		
		$pop = array_pop($parent_id);
		return array_reverse($parent_id);
	}
	/**
     * Получаем торговые наименования
     */
	function getTorgNames($data)
	{
		$query = "
			select
				TRADENAMES_ID,
				NAME
			from
				rls.v_TRADENAMES with(nolock)
			where
				1=1 ".$data['where'];
		
		$res = $this->db->query($query);
		
		if ( is_object($res) )
		{
			return $res->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
     * Получаем дату для комбостора
     */
	function getDataForComboStore($data)
	{
		if(!empty($data['lowercase'])){
			$id = 'id';
		} else {
			$id = 'ID';
		}
		$codeField = '';
		if(!empty($data['codeField']))
			$codeField .= 'RTRIM('.$data['codeField'].') as '.$data['codeField'].',';
		
		$query = "
			select
				".$data['object']."_{$id},
				".$codeField."
			RTRIM(".$data['stringfield'].") as ".$data['stringfield']."
			from
				rls.v_".$data['object']." with(nolock)
		";
		
		if(!empty($data['where'])){
			$query .= "where ".$data['where'];
		}
		//echo getDebugSQL($query, $data); return false;
		$res = $this->db->query($query);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}
			
	/**
     * Получаем дату для комбостора с дополнительными полями
     */
	function getDataForComboStoreWithFields($data)
	{
		if(!empty($data['lowercase'])){
			$id = 'id';
		} else {
			$id = 'ID';
		}
		$codeField = '';
		if(!empty($data['codeField'])){
			$codeField .= 'RTRIM('.$data['codeField'].') as '.$data['codeField'].',';
		}
		$additionalFields = '';
		if(!empty($data['additionalFields'])){
			$additionalFields = $data['additionalFields'].',';
		}	
		
		$query = "
			select
				".$data['object']."_{$id},
				".$codeField."
				".$additionalFields."
				RTRIM(".$data['stringfield'].") as ".$data['stringfield']."
			from
				rls.v_".$data['object']." with(nolock)
		";
		
		if(!empty($data['where'])){
			$query .= "where ".$data['where'];
		}
		//echo getDebugSQL($query, $data); return false;
		$res = $this->db->query($query);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
     * Получаем грид фирм
     */
	function loadSearchFirmsGrid($data)
	{
		$filter = '';
		
		$query = "
			select
				LPU.Org_id,
				LPU.Lpu_Name as Org_Name,
				ADDR.Address_Address as Org_Address,
				'LPU' as Org_attachment
			from
				v_Lpu LPU with(nolock)
				left join v_Address ADDR with(nolock) on LPU.UAddress_id = ADDR.Address_id
			where
				1=1 {$filter}

			union all

			select
				F.FIRMS_ID as Org_id,
				case
					when F.FULLNAME is not null then F.FULLNAME
					else FN.NAME
				end as Org_Name,
				F.ADRMAIN as Org_Address,
				'RLS' as Org_attachment
			from
				rls.v_FIRMS F with(nolock)
				left join rls.v_FIRMNAMES FN with(nolock) on FN.FIRMNAMES_ID = F.NAMEID
			where	
				(FN.NAME is not null or F.FULLNAME is not null)
				and RTRIM(case
					when F.FULLNAME is not null then F.FULLNAME
					else FN.NAME
				end) != '' {$filter}
		";
	
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}
	
	/**
     * Проверка на существование производителей с похожим названием
     */
	function checkFirmOnExist($data)
	{
		$query = "
			select
				F.FIRMS_ID,
				RTRIM(case
					when F.FULLNAME is not null then F.FULLNAME
					else FN.NAME
				end) as FIRMS_NAME
			from
				rls.v_FIRMS F with(nolock)
				left join rls.v_FIRMNAMES FN with(nolock) on FN.FIRMNAMES_ID = F.NAMEID
			where
				RTRIM(case
					when F.FULLNAME is not null then F.FULLNAME
					else FN.NAME
				end) like :FIRMS_NAME+'%'
		";
		//echo getDebugSQL($query, $data); return false;
	
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}
	
	/**
     * Проверка производителя на принадлежность РЛС
     */
	function checkFirmOnRls($data)
	{
		$query = "
			select
				FIRMS_ID,
				ProducerType_id,
				NAMEID as FIRMNAMES_ID
			from
				rls.v_FIRMS with(nolock)
			where
				FIRMS_ID = :FIRMS_ID
		";
		//echo getDebugSQL($query, $data); return false;
	
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}
	/**
     * Сохраняет название фирмы
     */
	function saveFirmName($data)
	{
		if(!empty($data['FIRMNAMES_ID']))
			$action = 'upd';
		else
			$action = 'ins';
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :FIRMNAMES_ID;
			exec rls.p_FIRMNAMES_{$action}
				@FIRMNAMES_ID = @Res output,
				@NAME = :FIRMS_NAME,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as FIRMNAMES_ID, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
	
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}
	/**
     * Сохраняем фирму
     */
	function saveFirm($data)
	{
		if(!empty($data['FIRMS_ID']))
			$action = 'upd';
		else
			$action = 'ins';
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :FIRMS_ID;
			exec rls.p_FIRMS_{$action}
				@FIRMS_ID = @Res output,
				@FULLNAME = :FIRMS_NAME,
				@NAMEID = :FIRMNAMES_ID,
				@COUNTID = :FIRMS_COUNTID,
				@ADRMAIN = :FIRMS_ADRMAIN,
				@ADRRUSSIA = :FIRMS_ADRRUSSIA,
				@ADRUSSR = null,
				@ProducerType_id = 2,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as FIRMS_ID, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
	
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
     * Получаем фирму
     */
	function getFirm($data)
	{
		$filter = '1=1';
		if(!empty($data['FIRMS_ID'])){
			$filter .= ' and F.FIRMS_ID = :FIRMS_ID';
		}
		
		if(!empty($data['FIRMS_NAME'])){
			$filter .= ' and case
							when F.FULLNAME is not null then F.FULLNAME
							else FN.NAME
						end like :FIRMS_NAME+\'%\'';
		}
		$top100 = "";
		if(!empty($data['forCombo'])){
			$top100 = " top 100 ";
		}
	
		$query = "
			select {$top100}
				F.FIRMS_ID,
				FN.FIRMNAMES_ID,
				case
					when F.FULLNAME is not null then F.FULLNAME
					else FN.NAME
				end as FIRMS_NAME,
				F.COUNTID as FIRMS_COUNTID,
				F.ADRMAIN as FIRMS_ADRMAIN,
				F.ADRRUSSIA as FIRMS_ADRRUSSIA
			from
				rls.v_FIRMS F with(nolock)
				left join rls.v_FIRMNAMES FN with(nolock) on FN.FIRMNAMES_ID = F.NAMEID
			where
				{$filter}
		";
		//echo getDebugSQL($query, $data); return false;
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
     * Удаляем название фирмы
     */
	function deleteFirmName($data)
	{
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec rls.p_FIRMNAMES_del
				@FIRMNAMES_ID = :FIRMNAMES_ID,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
     * Удаляем фирму
     */
	function deleteFirm($data)
	{
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec rls.p_FIRMS_del
				@FIRMS_ID = :FIRMS_ID,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
	
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

    /**
     * Сохраняем описание
     */
	function saveDesctextes($data)
	{		
		// Проверяем существует ли искомое описание
		$query = "
			select DESCID from rls.DESCTEXTES with(nolock) where DESCID = :DESCRIPTIONS_ID
		";
		$params = array(
			'DESCRIPTIONS_ID' => $data['DESCRIPTIONS_ID']
		);
		$result = $this->db->query($query, $params);
		if ( !is_object($result) ) {
			return false;
		}
		
		$result = $result->result('array');
		
		if( count($result) > 0 ) {
			if( count($result) == 1 )
				$action = 'upd';
			else
				return false;
		} else {
			$action = 'ins';
		}
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :DESCRIPTIONS_ID;
			exec rls.p_DESCTEXTES_{$action}
				@DESCID = @Res output,
				@COMPOSITION = :DESCTEXTES_COMPOSITION,
				@DRUGFORMDESCR = null,
				@CHARACTERS = :DESCTEXTES_CHARACTERS,
				@PHARMAACTIONS = :DESCTEXTES_PHARMAACTIONS,
				@COMPONENTSPROPERTIES = :DESCTEXTES_COMPONENTSPROPERTIES,
				@PHARMAKINETIC = :DESCTEXTES_PHARMAKINETIC,
				@PHARMADYNAMIC = :DESCTEXTES_PHARMADYNAMIC,
				@CLINICALPHARMACOLOGY = :DESCTEXTES_CLINICALPHARMACOLOGY,
				@DIRECTION = :DESCTEXTES_DIRECTION,
				@INDICATIONS = :DESCTEXTES_INDICATIONS,
				@RECOMMENDATIONS = :DESCTEXTES_RECOMMENDATIONS,
				@CONTRAINDICATIONS = :DESCTEXTES_CONTRAINDICATIONS,
				@SIDEACTIONS = :DESCTEXTES_SIDEACTIONS,
				@INTERACTIONS = :DESCTEXTES_INTERACTIONS,
				@USEMETHODANDDOSES = :DESCTEXTES_USEMETHODANDDOSES,
				@OVERDOSE = :DESCTEXTES_OVERDOSE,
				@PREGNANCYUSE = :DESCTEXTES_PREGNANCYUSE,
				@PRECAUTIONS = :DESCTEXTES_PRECAUTIONS,
				@SPECIALGUIDELINES = :DESCTEXTES_SPECIALGUIDELINES,
				@LITERATURE = null,
				@COMMENT = null,
				@ACTONORG = :DESCTEXTES_ACTONORG,
				@MANUFACTURER = null,
				@INSTRFORPAC = :DESCTEXTES_INSTRFORPAC,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as DESCRIPTIONS_ID, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

    /**
     * Сохраняем препарат
     */
	function savePrep($data)
	{
		if(!empty($data['Prep_id'])){
			$action = 'upd';
		} else {
			$action = 'ins';
		}
	
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :Prep_id;
			exec rls.p_Prep_{$action}
				@Prep_id = @Res output,
				@FIRMID = :FIRMS_ID,
				@TRADENAMEID = :TRADENAMES_ID,
				@LATINNAMEID = :LATINNAMES_ID,
				@DRUGFORMID = :CLSDRUGFORMS_ID,
				@DFMASS = :DFMASS,
				@DFMASSID = :DFMASSID,
				@DFCONC = :DFCONC,
				@DFCONCID = :DFCONCID,
				@DFACT = :DFACT,
				@DFACTID = :DFACTID,
				@DFSIZE = :DFSIZE,
				@DFSIZEID = :DFSIZEID,
				@DFCHARID = :DFCHARID,
				@DRUGDOSE = :DRUGDOSE,
				@NORECIPE = :NORECIPE,
				@LISTTYPE = null,
				@REGEND = 'N',
				@REGCERTID = :REGCERT_ID,
				@NTFRID = :CLSNTFR_ID,
				@PrepType_id = :PrepType_id,
				@DFSIZELATIN = :DFSIZE_LAT,
				@DrugNonpropNames_id = :DrugNonpropNames_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as Prep_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//echo getDebugSQL($query, $data); return false; 
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

    /**
     * Сохраняем DrugMnnOne
     */
	function saveDrugMnnOne($data)
	{
		$query = "
			set nocount on;

			begin try
				--объявляем переменные
				declare
					@nomen_id bigint = :NOMEN_ID,
					@prep_id bigint = :Prep_id,
					@ACTMATTERS_ID bigint = null,
					@tradenames_id int = null, @DRUGFORMID int = null,
					@dfmass numeric(14, 6) = null, @dfmassid int = null,
					@dfconc numeric(14, 6) = null, @dfconcid int = null,
					@dfact numeric(14, 6) = null, @dfactid int = null,
					@dfsize varchar(1064) = null, @dfsizeid int = null,
					@drugdose numeric(14, 6) = null, @PPACKMASS numeric(14, 6) = null,
					@PPACKMASSUNID int = null, @PPACKVOLUME numeric(14, 6) = null,
					@PPACKCUBUNID int = null, @DRUGSINPPACK int = null,
					@PPACKINUPACK int = null, @UPACKINSPACK INT = null,
					@DrugComplexMnnFas_id bigint = null,
					@date datetime = null,
					@Error_Code int,
					@Error_Message varchar(4000);

				set @date = GETDATE();

				--хранимка которая обновит данные по медикаменту
				exec rls.xp_DrugPrep_reload_2
					@nomen_id  = @nomen_id,
					@prep_id = @prep_id,
					@Error_Code = @Error_Code output, -- int
					@Error_Message = @Error_Message output; -- varchar(4000);
					
				--запихиваем данные в переменные по номену           
				select top 1
					@ActMatters_id = zzz.ActMatters_id, @tradenames_id = TRADENAMEID,
					@DRUGFORMID = DRUGFORMID, @dfmass = dfmass,
					@dfmassid = dfmassid, @dfconc = dfconc,
					@dfconcid = dfconcid, @dfact = dfact,
					@dfsizeid = dfactid, @dfsize = dfsize,
					@dfsizeid = dfsizeid, @drugdose = drugdose,
					@PPACKMASS = PPACKMASS, @PPACKMASSUNID = PPACKMASSUNID,
					@PPACKVOLUME = PPACKVOLUME, @PPACKCUBUNID = PPACKCUBUNID,
					@DRUGSINPPACK = DRUGSINPPACK, @PPACKINUPACK = PPACKINUPACK,
					@UPACKINSPACK = UPACKINSPACK, @DrugComplexMnnFas_id = DrugComplexMnnFas_id
				from
					rls.NOMEN n with(nolock)
					left join rls.Drug d with(nolock) on n.NOMEN_ID = d.Drug_id
					left join rls.DrugComplexMnn dcm with(nolock) on d.DrugComplexMnn_id = dcm.DrugComplexMnn_id
					left join rls.prep pr with(nolock) on pr.Prep_id = n.PREPID
					outer apply (
						select top 1
							a.ACTMATTERS_ID 
						FROM
							rls.PREP_ACTMATTERS pa  with(nolock)
							inner join rls.ACTMATTERS a with(nolock) on a.ACTMATTERS_ID = pa.MATTERID
						where
							pa.PREPID = n.PREPID
					) as zzz
				where
					NOMEN_ID = @nomen_id;

				--кривые данные
				if @ACTMATTERS_ID = 0 set @ACTMATTERS_ID = null
				if @tradenames_id = 0 set @tradenames_id = null
				if @DRUGFORMID = 0 set @DRUGFORMID = null
				if @dfmass = 0 set @dfmass = null
				if @dfmassid = 0 set @dfmassid = null
				if @dfconc = 0 set @dfconc = null
				if @dfconcid = 0 set @dfconcid = null
				if @dfact = 0 set @dfact = null
				if @dfactid = 0 set @dfactid = null
				if @dfsize = '' set @dfsize = null
				if @dfsizeid = 0 set @dfsizeid = null
				if @drugdose = 0 set @drugdose = null
				if @PPACKMASS = 0 set @PPACKMASS = null
				if @PPACKMASSUNID = 0 set @PPACKMASSUNID = null
				if @PPACKVOLUME = 0 set @PPACKVOLUME = null
				if @PPACKCUBUNID = 0 set @PPACKCUBUNID = null
				if @DRUGSINPPACK = 0 set @DRUGSINPPACK = null
				if @PPACKINUPACK = 0 set @PPACKINUPACK = null
				if @UPACKINSPACK = 0 set @UPACKINSPACK = null
					
				--выходные параметры
				declare
					@DrugComplexMnnName_id BIGINT = null,
					@DrugComplexMnnDose_id BIGINT = null,
					@DrugComplexMnn_id BIGINT = null,
					@DrugComplexMnn_idd BIGINT = null,
					@pmUser_id bigint;
					
				--хранимка которая всё сделает
				exec rls.p_DrugComplexMnnOne_ins
					@ACTMATTERS_ID = @ACTMATTERS_ID, -- bigint
					@tradenames_id = @tradenames_id, -- bigint
					@DrugComplexMnnName_id = @DrugComplexMnnName_id OUTPUT, -- bigint
					@DRUGFORMID = @DRUGFORMID, -- bigint
					@dfmass = @dfmass, -- numeric
					@dfmassid = @dfmassid, -- bigint
					@dfconc = @dfconc, -- numeric
					@dfconcid = @dfconcid, -- bigint
					@dfact = @dfact, -- numeric
					@dfactid = @dfactid, -- bigint
					@dfsize = @dfsize, -- varchar(1024)
					@dfsizeid = @dfsizeid, -- bigint
					@drugdose = @drugdose, -- numeric
					@DrugComplexMnnDose_id = @DrugComplexMnnDose_id OUTPUT, -- int
					@DrugComplexMnn_id = @DrugComplexMnn_id OUTPUT, -- bigint
					@DrugComplexMnn_idd = @DrugComplexMnn_idd OUTPUT,
					@PPACKMASS = @PPACKMASS, -- bigint
					@PPACKMASSUNID = @PPACKMASSUNID, -- bigint
					@PPACKVOLUME = @PPACKVOLUME, -- bigint
					@PPACKCUBUNID = @PPACKCUBUNID, -- bigint
					@DRUGSINPPACK = @DRUGSINPPACK, -- bigint
					@PPACKINUPACK = @PPACKINUPACK, -- bigint
					@UPACKINSPACK = @UPACKINSPACK, -- bigint
					@DrugComplexMnnFas_id = @DrugComplexMnnFas_id OUTPUT, -- bigint
					@pmUser_id = @pmUser_id, -- bigint
					@Error_Code = @Error_Code output, -- int
					@Error_Message = @Error_Message output; -- varchar(4000);

				--связь новых мнн и номенов, чтобы потом проапдейтить их в драге
				insert into [tmp].[NomenMnn] (
					[Nomen_id],
					[DrugComplexMnn_id],
					[DrugComplexMnn_idd],
					[Mnn_date],
					[afftar]
				) values (
					@nomen_id, -- Nomen_id - bigint
					@DrugComplexMnn_id, -- DrugComplexMnn_id - bigint
					@DrugComplexMnn_idd, -- DrugComplexMnn_idd - bigint
					@date, -- Mnn_date - datetime
					'sprform'  -- afftar - varchar(300)
				)

				update
					d with (updlock)
				set
					DrugComplexMnn_id = isnull(t.DrugComplexMnn_id,t.DrugComplexMnn_idd),
					Drug_updDT = GETDATE(),
					pmUser_updID = 1
				from
					[tmp].[NomenMnn] t
					join rls.drug d on d.drug_id = t.Nomen_id and d.DrugComplexMnn_id is null and t.Nomen_id = @nomen_id
			end try

			begin catch
				SET @Error_Code = ERROR_NUMBER()
				SET @Error_Message = ERROR_MESSAGE()
			end catch;

			set nocount off;
			
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		//echo getDebugSQL($query, $data); return false;
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

    /**
     * Сохраняем наименование
     */
	function saveNomen($data)
	{
		if(!empty($data['NOMEN_ID'])){
			$action = 'upd';
		} else {
			$action = 'ins';
		}
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :NOMEN_ID;
			exec rls.p_Nomen_{$action}
				@NOMEN_ID = @Res output,
				@PREPID = :Prep_id,
				@FIRMID = :FIRMS_ID,
				@PPACKID = :NOMEN_PPACKID,
				@DRUGSINPPACK = :NOMEN_DRUGSINPPACK,
				@PPACKMASS = :NOMEN_PPACKMASS,
				@PPACKMASSUNID = :NOMEN_PPACKMASSUNID,
				@PPACKVOLUME = :NOMEN_PPACKVOLUME,
				@PPACKCUBUNID = :NOMEN_PPACKCUBUNID,
				@SETID = :NOMEN_SETID,
				@UPACKID = :NOMEN_UPACKID,
				@PPACKINUPACK = :NOMEN_PPACKINUPACK,
				@INANGRO = 'N',
				@EANCODE = :NOMEN_EANCODE,
				@CONDID = :DRUGSTORCOND_ID,
				@LIFEID = :DRUGLIFETIME_ID,
				@PRICEINRUB = null,
				@PRICEORDER = null,
				@PRICEDATE = null,
				@PRICEINCURR = null,
				@CURRID = 1,
				@DRUGSTORCOND = null,
				@DRUGLIFETIME = null,
				@SPACKID = :NOMEN_SPACKID,
				@UPACKINSPACK = :NOMEN_UPACKINSPACK,
				@PrepType_id = :PrepType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as NOMEN_ID, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//echo getDebugSQL($query, $data); return false;
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

    /**
     * Торговое наименование
     */
	function saveTradeName($data)
	{
		$result = array();

		$data['TRADENAMES_INAME'] = strtoupper($data['TRADENAMES_NAME']);
		$data['TRADENAMES_INAME'] = mb_convert_encoding($data['TRADENAMES_INAME'], "UTF-8");
		if(!empty($data['TRADENAMES_ID'])){
			$action = 'upd';
		} else {
			$action = 'ins';
		}

		//при добавлении новой позиции проверяем нет ли уже такой в базе
		if ($action == 'ins') {
			$query = "
				select top 1
					TRADENAMES_ID
				from
					rls.TRADENAMES with (nolock)
				where
					NAME = :TRADENAMES_NAME
					and ISNULL(INAME, '') = ISNULL(:TRADENAMES_INAME, '')
				order by
					TRADENAMES_ID;
			";
			$res = $this->getFirstRowFromQuery($query, $data);
			if (!empty($res['TRADENAMES_ID'])) {
				$result['TRADENAMES_ID'] = $res['TRADENAMES_ID'];
			}
		}
		

		if (empty($result['TRADENAMES_ID'])) {
			$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = :TRADENAMES_ID;
				exec rls.p_TRADENAMES_{$action}
					@TRADENAMES_ID = @Res output,
					@NAME = :TRADENAMES_NAME,
					@INAME = :TRADENAMES_INAME,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as TRADENAMES_ID, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$res = $this->db->query($query, $data);
			if ( is_object($res) ) {
				$result = $res->result('array');
			} else {
				return false;
			}
		}

		return $result;
	}

    /**
     * Латинское название
     */
	function saveLatinName($data)
	{
		$result = array();

		if($data['LATINNAMES_ID']>0){
			$action = 'upd';
		} else {
			$data['LATINNAMES_ID'] = null;
			$action = 'ins';
		}

		//при добавлении новой позиции проверяем нет ли уже такой в базе
		if ($action == 'ins') {
			$query = "
				select top 1
					LATINNAMES_ID
				from
					rls.LATINNAMES with (nolock)
				where
					NAME = :LATINNAMES_NAME and
					ISNULL(LATINNAMES_NameGen, '') = ISNULL(:LATINNAMES_NameGen, '')
				order by
					LATINNAMES_ID;
			";
			$res = $this->getFirstRowFromQuery($query, $data);
			if (!empty($res['LATINNAMES_ID'])) {
				$result['LATINNAMES_ID'] = $res['LATINNAMES_ID'];
			}
		}

		if (empty($result['LATINNAMES_ID'])) {
			$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = :LATINNAMES_ID;
				exec rls.p_LATINNAMES_{$action}
					@LATINNAMES_ID = @Res output,
					@NAME = :LATINNAMES_NAME,
					@LATINNAMES_NameGen = :LATINNAMES_NameGen,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as LATINNAMES_ID, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$res = $this->db->query($query, $data);
			if ( is_object($res) ) {
				$result = $res->result('array');
			} else {
				return false;
			}
		}

		return $result;
	}

    /**
     * Сохраняем регистрационный серифтификат?
     */
	function saveRegCert($data)
	{
		if( $data['REGCERT_ID'] > 0) {
			$action = 'upd';
		} else {
			$data['REGCERT_ID'] = null;
			$action = 'ins';
		}
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :REGCERT_ID;
			exec rls.p_REGCERT_{$action}
				@REGCERT_ID = @Res output,
				@TRADENAMEID = :TRADENAMES_ID,
				@LATINNAMEID = :LATINNAMES_ID,
				@REGNUM = :REGCERT_REGNUM,
				@REGDATE = :REGCERT_REGDATE,
				@COMPOSITION = null,
				@PHARMAACTIONS = null,
				@ENDDATE = :REGCERT_ENDDATE,
				@KLCountry_id = :KLCountry_id,
				@Reregdate = :Reregdate,
				@REGCERT_excDT = :REGCERT_excDT,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as REGCERT_ID, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//echo getDebugSQL($query, $data); return false;
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			$resp = $res->result('array');
			if (!empty($resp[0]['REGCERT_ID'])) {
				$this->db->query("
					update
						d with (rowlock)
					set
						d.Drug_excDT = :REGCERT_excDT
					from
						rls.Drug d
						inner join rls.Prep p with (nolock) on p.Prep_id = d.DrugPrep_id
					where
						p.REGCERTID = :REGCERT_ID
				", [
					'REGCERT_ID' => $resp[0]['REGCERT_ID'],
					'REGCERT_excDT' => $data['REGCERT_excDT']
				]);
			}
			return $resp;
		} else {
			return false;
		}
	}

	/**
     * Сохраняем владельца РУ
     */
	function saveRegCertOwner($data)
	{
		if( $data['REGCERT_ID'] > 0) {
			$query = "
				select top 1
				CERTID, FIRMID from rls.v_REGCERT_EXTRAFIRMS rex with (nolock)
				where CERTID = :REGCERT_ID
			";
			$resl = $this->db->query($query, $data);
			if ( is_object($resl) ) {
				$resl = $resl->result('array');
				if(!empty($resl[0]['CERTID'])){
					$query = "
						UPDATE
							rls.REGCERT_EXTRAFIRMS with (rowlock)
						SET
							FIRMID = :RegOwner
						WHERE
							CERTID = :REGCERT_ID
					";
					$result = $this->db->query($query, $data);
					
					if ( $result ) {
						return $result;
					} else {
						return false;
					}
				} else {
					$query = "
						INSERT INTO
							rls.REGCERT_EXTRAFIRMS with (rowlock)
							(CERTID,FIRMID)
						VALUES
							(:REGCERT_ID,:RegOwner)
					";
					$result = $this->db->query($query, $data);
					
					if ( $result ) {
						return $result;
					} else {
						return false;
					}
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
     * Сохраняем фармакологической группы
     */
	function saveCLSPharmaGroup($data)
	{
		if( $data['Prep_id'] > 0) {
			$query = "
				select top 1
				PREPID, UNIQID from rls.v_PREP_PHARMAGROUP with (nolock)
				where PREPID = :Prep_id
			";
			$resl = $this->db->query($query, $data);
			if ( is_object($resl) ) {
				$resl = $resl->result('array');
				if(!empty($resl[0]['PREPID'])){
					$query = "
						UPDATE
							rls.PREP_PHARMAGROUP with (rowlock)
						SET
							UNIQID = :CLSPHARMAGROUP_ID
						WHERE
							PREPID = :Prep_id
					";
					$result = $this->db->query($query, $data);
					
					if ( $result ) {
						return $result;
					} else {
						return false;
					}
				} else {
					$query = "
						insert into rls.PREP_PHARMAGROUP with (ROWLOCK) (PREPID, UNIQID) values (:Prep_id, :CLSPHARMAGROUP_ID)
					";
					$result = $this->db->query($query, $data);
					
					if ( $result ) {
						return $result;
					} else {
						return false;
					}
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Метод для получения id срока годности, предполагается поиск по полю TEXT
	 * @param $data
	 * @return bool|float|int|string
	 */
	function getDrugLifeTimeId($data)
	{
		$filters = array();

		if ( ! empty($data['DRUGLIFETIME_TEXT']))
		{
			$filters[] = 'TEXT = :DRUGLIFETIME_TEXT';
		}

		$filter = $filters ? implode(' AND ', $filters) : null;


		$query = "
			SELECT TOP 1
				DRUGLIFETIME_ID
			FROM
				rls.v_DRUGLIFETIME
			WHERE
			{$filter}
		";

		$result = $this->getFirstResultFromQuery($query, $data);

		return $result;
	}

    /**
     * Сохраняем время действвия лекарства
     */
	function saveDrugLifeTime($data)
	{
		if($data['DRUGLIFETIME_ID']>0){
			$action = 'upd';
		} else {
			$action = 'ins';
		}
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :DRUGLIFETIME_ID;
			exec rls.p_DRUGLIFETIME_{$action}
				@DRUGLIFETIME_ID = @Res output,
				@TEXT = :DRUGLIFETIME_TEXT,
				@LIFETIME = null,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as DRUGLIFETIME_ID, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
	
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
     * Сохраняем условия хранения
     */
	function saveDrugStorCond($data)
	{
		if($data['DRUGSTORCOND_ID']>0){
			$action = 'upd';
		} else {
			$action = 'ins';
		}
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :DRUGSTORCOND_ID;
			exec rls.p_DRUGSTORCOND_{$action}
				@DRUGSTORCOND_ID = @Res output,
				@TEXT = :DRUGSTORCOND_TEXT,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as DRUGSTORCOND_ID, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
	
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
     * Сохраняем опиания
     */
	function saveDescriptions($data)
	{
		if(!empty($data['DESCRIPTIONS_ID'])){
			$action = 'upd';
		} else {
			$action = 'ins';
		}
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :DESCRIPTIONS_ID;
			exec rls.p_DESCRIPTIONS_{$action}
				@DESCRIPTIONS_ID = @Res output,
				@FPREPNAME = :TRADENAMES_NAME,
				@FIRMID = :FIRMS_ID,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as DESCRIPTIONS_ID, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

    /**
     * Директ инсерт дата
     */
	function directInsertData($data, $object)
	{
		switch($object){
            //решено было заблокировать в связи с сомнительным характером изменения данных #132750
			case 'NOMEN_DESC':
				$query = "
					set nocount on;

					begin try
						begin tran

						declare
						    @NOMEN_ID int = :NOMEN_ID,
						    @DESCRIPTIONS_ID int = :DESCRIPTIONS_ID,
                            @cnt int,
                            @Error_Code int = null,
                            @Error_Message varchar(4000) = null;

						set @cnt = (select count(NOMENID) as cnt from rls.NOMEN_DESC with (ROWLOCK) where NOMENID = @NOMEN_ID and isnull(DESCID, 0) = isnull(@DESCRIPTIONS_ID, 0));

						if (@cnt = 0 and @NOMEN_ID is not null and @DESCRIPTIONS_ID is not null)
							begin
								insert into rls.NOMEN_DESC with (ROWLOCK) (NOMENID, DESCID, NUMBER) values (@NOMEN_ID, @DESCRIPTIONS_ID, 1);
							end

						commit tran
					end try

					begin catch
						set @Error_Code = error_number()
						set @Error_Message = error_message()
						if @@trancount>0
							rollback tran
					end catch

					set nocount off;

					select @NOMEN_ID as NOMEN_ID, @Error_Code as Error_Code, @Error_Message as Error_Msg;
				";
			break;
			
			case 'TN_DF_LIMP':
				$query = "
					set nocount on;

					begin try
						begin tran
						
						declare @TRADENAMES_ID int,
						@tn bigint,
						@Error_Code int = null,
						@Error_Message varchar(4000) = null
						set @TRADENAMES_ID = :TRADENAMES_ID
						set @tn = :TN_DF_LIMP
							delete from rls.TN_DF_LIMP with (ROWLOCK) where TRADENAMEID = @TRADENAMES_ID
						if @tn = 2
							insert into rls.TN_DF_LIMP with (ROWLOCK) (TRADENAMEID, DRUGFORMID, LIMP_PHGR_ID) values (@TRADENAMES_ID, :CLSDRUGFORMS_ID, 0)
						
						commit tran
					end try

					begin catch
						set @Error_Code = error_number()
						set @Error_Message = error_message()
						if @@trancount>0
							rollback tran
					end catch

					set nocount off;

					select @TRADENAMES_ID as TRADENAMES_ID, @Error_Code as Error_Code, @Error_Message as Error_Msg;
				";
			break;
			
			case 'TRADENAMES_DRUGFORMS':
				$query = "
					set nocount on;

					begin try
						begin tran
						
						declare @TRADENAMES_ID int,
						@tn bigint,
						@Error_Code int = null,
						@Error_Message varchar(4000) = null
						set @TRADENAMES_ID = :TRADENAMES_ID
						set @tn = :TRADENAMES_DRUGFORMS
							delete from rls.TRADENAMES_DRUGFORMS with (ROWLOCK) where TRADENAMEID = @TRADENAMES_ID
						if @tn = 2
							insert into rls.TRADENAMES_DRUGFORMS with (ROWLOCK) (TRADENAMEID, DRUGFORMID, MZ_PHGR_ID) values (@TRADENAMES_ID, :CLSDRUGFORMS_ID, 0)
						
						commit tran
					end try

					begin catch
						set @Error_Code = error_number()
						set @Error_Message = error_message()
						if @@trancount>0
							rollback tran
					end catch

					set nocount off;

					select @TRADENAMES_ID as TRADENAMES_ID, @Error_Code as Error_Code, @Error_Message as Error_Msg;
				";
			break;
			
			case 'PREP_ATC':
				$query = "
					insert into rls.PREP_ATC with (ROWLOCK) (PREPID, UNIQID) values (:Prep_id, :CLSATC_ID)
				";
			break;
			case 'PREP_IIC':
				$query = "
					insert into rls.PREP_IIC with (ROWLOCK) (PREPID, UNIQID, PHGRID) values (:Prep_id, :CLSIIC_ID, :CLSPHARMAGROUP_ID)
				";
			break;
			
			case 'PREP_ACTMATTERS':
				$query = "
					insert into rls.PREP_ACTMATTERS with (ROWLOCK) (PREPID, MATTERID) values (:Prep_id, :ACTMATTERS_ID)
				";
			break;
			
			case 'IDENT_WIND_STR':
				$action = !empty($data['IDENT_WIND_STR_id']) ? 'upd' : 'ins';
				$query = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @Res = :IDENT_WIND_STR_id;
					exec rls.p_IDENT_WIND_STR_{$action}
						@IDENT_WIND_STR_id = @Res output,
						@IWID = :IWID,
						@NOMENID = :NOMEN_ID,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @Res as IDENT_WIND_STR_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
			break;
			case 'PHARMAGROUPS':
				$query = "
						insert into rls.PREP_PHARMAGROUP with (ROWLOCK) (PREPID, UNIQID) values (:Prep_id, :CLSPHARMAGROUP_ID)
					";
				break;
			case 'FTGGRLS':
				$query = "
						insert into rls.PREP_FTGGRLS with (ROWLOCK) (PREP_ID, FTGGRLS_ID, PREP_FTGGRLS_insDT, PREP_FTGGRLS_updDT, pmUser_insID, pmUser_updID) values (:Prep_id, :FTGGRLS_ID, dbo.tzGetDate(), dbo.tzGetDate(), :pmUser_id, :pmUser_id)
					";
				break;
			case 'ACTMATTERSFTGGRLS':
				$query = "
						insert into rls.ACTMATTERS_FTGGRLS with (ROWLOCK) (ACTMATTERS_ID, FTGGRLS_ID, pmUser_insID, pmUser_updID, ACTMATTERS_FTGGRLS_insDT, ACTMATTERS_FTGGRLS_updDT) values (:Actmatters_id, :FTGGRLS_ID, :pmUser_id, :pmUser_id, dbo.tzGetDate(), dbo.tzGetDate())
					";
				break;
			case 'ACTMATTERSPHARMAGROUPS':
				$query = "
						insert into rls.ACTMAT_PHGR with (ROWLOCK) (MATTERID, UNIQID) values (:Actmatters_id, :CLSPHARMAGROUP_ID)
					";
				break;
		}
		//echo getDebugSQL($query, $data); return false;
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

    /**
     * Удаляем препарат по действующему веществу?
     */
	function deletePrepOnActmatters($data)
	{
		$query = "
			delete from rls.PREP_ACTMATTERS with (ROWLOCK) where PREPID = :Prep_id
		";
	
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Удаляет группу "Классификации АТХ"
	 */
	function deleteATCGroup($data)
	{
		if( empty($data['Prep_id']) ) {
			return false;
		}

		$query = "
			delete from rls.PREP_ATC with (ROWLOCK) where PREPID = :Prep_id and UNIQID = :CLSATC_ID
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Удаляет фармакологическую группу для препарата
	 */
	function deletePharmaGroups($data)
	{
		if (empty($data['Prep_id'])) {
			return false;
		}
		$query = "
			delete from rls.PREP_PHARMAGROUP with (ROWLOCK) where PREPID = :Prep_id and UNIQID = :CLSPHARMAGROUP_ID
		";
		$res = $this->db->query($query, $data);
		if (is_object($res)) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Удаляет фармакотерапевтическую группу для препарата
	 */
	function deleteFTGGRLS($data)
	{
		if (empty($data['Prep_id'])) {
			return false;
		}
		$query = "
			delete from rls.PREP_FTGGRLS with (ROWLOCK) where PREP_ID = :Prep_id and FTGGRLS_ID = :FTGGRLS_ID
		";
		$res = $this->db->query($query, $data);
		if (is_object($res)) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Удаляет фармакологическую группу для действующего вещества
	 */
	function deleteActmattersPharmaGroups($data)
	{
		if (empty($data['Actmatters_id'])) {
			return false;
		}
		$query = "
			delete from rls.ACTMAT_PHGR with (ROWLOCK) where MATTERID = :Actmatters_id and UNIQID = :CLSPHARMAGROUP_ID
		";
		$res = $this->db->query($query, $data);
		if (is_object($res)) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Удаляет фармакологическую группу для действующего вещества
	 */
	function deleteActmattersFTGGRLS($data)
	{
		if (empty($data['Actmatters_id'])) {
			return false;
		}
		$query = "
			delete from rls.ACTMATTERS_FTGGRLS with (ROWLOCK) where ACTMATTERS_ID = :Actmatters_id and FTGGRLS_ID = :FTGGRLS_ID
		";
		$res = $this->db->query($query, $data);
		if (is_object($res)) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
     * Удаляет все препараты?
     */
	function deleteAllPrepIIC($data) {
		if( empty($data['Prep_id']) ) {
			return false;
		}
		$query = "
			delete from rls.PREP_IIC with (ROWLOCK) where PREPID = :Prep_id
		";
	
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получаем для препарата группы по Классификации АТХ
	 */
	function getATCGroups($data)
	{
		$query = "
			select
				PA.UNIQID
			from
				rls.PREP_ATC PA with(nolock)	
			where
				PA.PREPID = :Prep_id
		";
		$res = $this->db->query($query, $data);

		if ( is_object($res) ) {
			//избавляемся от вложенных массивов с одинаковым для всех записей ключом 'UNIQID', возвращая только массив классов
			$resp = $res->result('array');
			$result = array();
			foreach ($resp as $array)
			{
				$result[] = $array['UNIQID'];
			}
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * Получаем для препарата записи по Классификации АТХ
	 */
	function getClsATC ($data){
		$query = "
			select
				A.CLSATC_ID,
				A.NAME
			from
				rls.CLSATC A with(nolock)
				left join rls.PREP_ATC PA with(nolock)  on PA.UNIQID = A.CLSATC_ID
  			where
				PA.PREPID = :Prep_id
		";
		$res = $this->db->query($query, array(
			'Prep_id' => $data['Prep_id']
		));
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}

	}
	/**
	 * Получаем для препарата группы по Классификатору фармакологических групп
	 */
	function getPrepPharmaGroups($data)
	{
		$query = "
			select
				PF.UNIQID
			from
				rls.PREP_PHARMAGROUP PF with(nolock)	
			where
				PF.PREPID = :Prep_id
		";
		$res = $this->db->query($query, $data);

		if ( is_object($res) ) {
			//избавляемся от вложенных массивов с одинаковым для всех записей ключом 'UNIQID', возвращая только массив классов
			$resp = $res->result('array');
			$result = array();
			foreach ($resp as $array)
			{
				$result[] = $array['UNIQID'];
			}
			return $result;
		} else {
			return false;
		}
	}
	/**
	 * Получаем для препарата группы по Классификатору фармакотерапевтических групп
	 */
	function getPrepFTGGRLSs($data)
	{
		$query = "
			select
				PF.FTGGRLS_ID
			from
				rls.PREP_FTGGRLS PF with(nolock)	
			where
				PF.PREP_ID = :Prep_id
		";
		$res = $this->db->query($query, $data);

		if ( is_object($res) ) {
			//избавляемся от вложенных массивов с одинаковым для всех записей ключом 'UNIQID', возвращая только массив классов
			$resp = $res->result('array');
			$result = array();
			foreach ($resp as $array)
			{
				$result[] = $array['FTGGRLS_ID'];
			}
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * Получаем записи фармакологических групп для препарата
	 */
	function getPharmaGroups($data){
		$query = "
			select
				FG.CLSPHARMAGROUP_ID,
				FG.NAME
			from
				rls.CLSPHARMAGROUP FG with(nolock)
				left join rls.PREP_PHARMAGROUP PFG with(nolock) on PFG.UNIQID = FG.CLSPHARMAGROUP_ID 
			  where
				PFG.PREPID = :Prep_id
		";
		$res = $this->db->query($query, array(
			'Prep_id' => $data['Prep_id']
		));
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получаем записи фармакотерапевтических групп для препарата
	 */
	function getFTGGRLSs($data){
		$query = "
			select
				FG.FTGGRLS_ID,
				FG.NAME
			from
				rls.FTGGRLS FG with(nolock)
				left join rls.PREP_FTGGRLS PFG with(nolock) on PFG.FTGGRLS_ID = FG.FTGGRLS_ID 
			  where
				PFG.PREP_ID = :Prep_id
		";
		$res = $this->db->query($query, array(
			'Prep_id' => $data['Prep_id']
		));
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получаем фармакологические группы для действующего вещества
	 */
	function getActmatPharmaGroups($data){
		$query = "
			select
				APG.UNIQID
			from
				rls.ACTMAT_PHGR APG with(nolock)	
			where
				APG.MATTERID = :Actmatters_id
		";
		$res = $this->db->query($query, $data);

		if ( is_object($res) ) {
			//избавляемся от вложенных массивов с одинаковым для всех записей ключом 'UNIQID', возвращая только массив классов
			$resp = $res->result('array');
			$result = array();
			foreach ($resp as $array)
			{
				$result[] = $array['UNIQID'];
			}
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * Получаем фармакологические группы для действующего вещества
	 */
	function getActmatFTGGRLSs($data){
		$query = "
			select
				APG.FTGGRLS_ID
			from
				rls.ACTMATTERS_FTGGRLS APG with(nolock)	
			where
				APG.ACTMATTERS_ID = :Actmatters_id
		";
		$res = $this->db->query($query, $data);

		if ( is_object($res) ) {
			//избавляемся от вложенных массивов с одинаковым для всех записей ключом 'UNIQID', возвращая только массив классов
			$resp = $res->result('array');
			$result = array();
			foreach ($resp as $array)
			{
				$result[] = $array['FTGGRLS_ID'];
			}
			return $result;
		} else {
			return false;
		}
	}

	/**
     * Получения действующего вещества на препарат
     */
	function getActmattersOnPrep($data) {
		$query = "
			select
				A.ACTMATTERS_ID,
				A.RUSNAME,
				A.LATNAME,
				A.ACTMATTERS_LatNameGen
			from
				rls.ACTMATTERS A with(nolock)
				left join rls.PREP_ACTMATTERS P with(nolock) on P.MATTERID = A.ACTMATTERS_ID
			where
				P.PREPID = :Prep_id
		";
	
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

    /**
     * Получаем клас на препарат?
     */
	function getClsIIConPrep($data) {
		$query = "
			select
				C.CLSIIC_ID,
				C.NAME
			from
				rls.CLSIIC C with(nolock)
				left join rls.PREP_IIC P with(nolock) on P.UNIQID = C.CLSIIC_ID
			where
				P.PREPID = :Prep_id
		";
		$res = $this->db->query($query, array(
			'Prep_id' => $data['Prep_id']
		));
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
		
	}
	/**
	 * Получаем классы МКБ-10, связанные с препаратом
	 */
	function getClsIIC($data){
		$query = "
			select
				P.UNIQID
			from
				rls.PREP_IIC P 
			where
				P.PREPID = :Prep_id
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			//избавляемся от вложенных массивов с одинаковым для всех записей ключом 'UNIQID', возвращая только массив классов
			$resp = $res->result('array');
			$result = array();
			foreach ($resp as $array)
			{
				$result[] = $array['UNIQID'];
			}
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * Удаляем классы МКБ-10, связанные с препаратом
	 */
	function deleteClsIICs($data) {
		if (empty($data['Prep_id'])) {
			return false;
		}
		$query = "
			delete from rls.PREP_IIC with (ROWLOCK) where PREPID = :Prep_id and UNIQID = :CLSIIC_ID
		";
		$res = $this->db->query($query, $data);
		if (is_object($res)) {
			return $res->result('array');
		} else {
			return false;
		}
	}

    /**
     * Получаем ИВИД
     */
	function getIWID($data)
	{
		$query = "						
			declare @iw int
			set @iw = (select IWID from rls.IDENT_WIND_STR with(nolock) where NOMENID = :NOMEN_ID)
			select
				case when @iw > 0 then @iw else MAX(IWID)+1 end as IWID
			from
				rls.IDENT_WIND_STR with(nolock)
		";
		
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

    /**
     * Получаем препарат
     */
	function getPrep($data)
	{
		$query = "
			select
				Nomen.NOMEN_ID,
				PREP.Prep_id,
				PREP.NTFRID as CLSNTFR_ID,
				Extemporal.Extemporal_id,
				PREP.DrugNonpropNames_id,
				REGCERT.REGCERT_ID,
				TRADENAMES.TRADENAMES_ID,
				TRADENAMES.NAME as TRADENAMES_NAME,
				case when LATINNAMES.LATINNAMES_ID>0 then LATINNAMES.LATINNAMES_ID else null end as LATINNAMES_ID,
				LATINNAMES.NAME as LATINNAMES_NAME,
				LATINNAMES.LATINNAMES_NameGen as LATINNAMES_NameGen,
				CLSDRUGFORMS.CLSDRUGFORMS_ID,
				PREP.DFMASS,
				case when PREP.DFMASSID>0 then PREP.DFMASSID else null end as DFMASSID,
				PREP.DFCONC,
				case when PREP.DFCONCID>0 then PREP.DFCONCID else null end as DFCONCID,
				PREP.DFACT,
				case when PREP.DFACTID>0 then PREP.DFACTID else null end as DFACTID,
				PREP.DRUGDOSE,
				case when PREP.NORECIPE = 'Y' then 2 else 1 end as NORECIPE,
				case when DRUGLIFETIME.DRUGLIFETIME_ID>0 then DRUGLIFETIME.DRUGLIFETIME_ID else null end as DRUGLIFETIME_ID,
				DRUGLIFETIME.TEXT as DRUGLIFETIME_TEXT,
				case when DRUGSTORCOND.DRUGSTORCOND_ID>0 then DRUGSTORCOND.DRUGSTORCOND_ID else null end as DRUGSTORCOND_ID,
				DRUGSTORCOND.TEXT as DRUGSTORCOND_TEXT,
				PREP.DFSIZE,
				case when PREP.DFSIZEID>0 then PREP.DFSIZEID else null end as DFSIZEID,
				PREP.DFSIZELATIN as DFSIZE_LAT,
				isnull(SIZEUNITS.SHORTNAMELATIN, '') as SIZEUNITS_LAT,
				case when PREP.DFCHARID>0 then PREP.DFCHARID else null end as DFCHARID,
				Nomen.PrepType_id,
				Nomen.DRUGSINPPACK as NOMEN_DRUGSINPPACK,
				Nomen.PPACKID as NOMEN_PPACKID,
				Nomen.PPACKVOLUME as NOMEN_PPACKVOLUME,
				case when Nomen.PPACKCUBUNID>0 then Nomen.PPACKCUBUNID else null end as NOMEN_PPACKCUBUNID,
				Nomen.PPACKMASS as NOMEN_PPACKMASS,
				case when Nomen.PPACKMASSUNID>0 then Nomen.PPACKMASSUNID else null end as NOMEN_PPACKMASSUNID,
				case when Nomen.SETID>0 then Nomen.SETID else null end as NOMEN_SETID,
				Nomen.PPACKINUPACK as NOMEN_PPACKINUPACK,
				case when Nomen.UPACKID>0 then Nomen.UPACKID else null end as NOMEN_UPACKID,
				Nomen.UPACKINSPACK as NOMEN_UPACKINSPACK,
				case when Nomen.SPACKID>0 then Nomen.SPACKID else null end as NOMEN_SPACKID,
				FIRMS.FIRMS_ID,
				FIRMS.FIRMS_ID as Manufacturer,
				FIRMS_Packer.FIRMS_ID as Packer,
				FIRMS_RegOwner.FIRMS_ID as RegOwner,
				Nomen.EANCODE as NOMEN_EANCODE,
				REGCERT.REGNUM as REGCERT_REGNUM,
				REGCERT.KLCountry_id,	
				convert(varchar(10), cast(REGCERT.REGDATE as datetime), 104) as REGCERT_REGDATE,
				convert(varchar(10), cast(REGCERT.ENDDATE as datetime), 104) as REGCERT_ENDDATE,
				convert(varchar(10), cast(REGCERT.Reregdate as datetime), 104) as Reregdate,
				convert(varchar(10), cast(REGCERT.REGCERT_excDT as datetime), 104) as REGCERT_excDT,
				PREP_ATC.UNIQID as CLSATC_ID,
				PREP_IIC.UNIQID as CLSIIC_ID,
				PREP_IIC.PHGRID as CLSPHARMAGROUP_ID,
				PREP_PHARMAGROUP.UNIQID as PREP_CLSPHARMAGROUP_ID,
				(case when TN_DF_LIMP.TRADENAMEID is not null then 2 else 1 end) as TN_DF_LIMP,
				(case when TRADENAMES_DRUGFORMS.TRADENAMEID is not null then 2 else 1 end) as TRADENAMES_DRUGFORMS,
				DESCRIPTIONS.DESCRIPTIONS_ID,
				DESCTEXTES.COMPOSITION as DESCTEXTES_COMPOSITION,
				DESCTEXTES.CHARACTERS as DESCTEXTES_CHARACTERS,
				DESCTEXTES.PHARMAACTIONS as DESCTEXTES_PHARMAACTIONS,
				DESCTEXTES.ACTONORG as DESCTEXTES_ACTONORG,
				DESCTEXTES.COMPONENTSPROPERTIES as DESCTEXTES_COMPONENTSPROPERTIES,
				DESCTEXTES.PHARMAKINETIC as DESCTEXTES_PHARMAKINETIC,
				DESCTEXTES.PHARMADYNAMIC as DESCTEXTES_PHARMADYNAMIC,
				DESCTEXTES.CLINICALPHARMACOLOGY as DESCTEXTES_CLINICALPHARMACOLOGY,
				DESCTEXTES.DIRECTION as DESCTEXTES_DIRECTION,
				DESCTEXTES.INDICATIONS as DESCTEXTES_INDICATIONS,
				DESCTEXTES.RECOMMENDATIONS as DESCTEXTES_RECOMMENDATIONS,
				DESCTEXTES.CONTRAINDICATIONS as DESCTEXTES_CONTRAINDICATIONS,
				DESCTEXTES.PREGNANCYUSE as DESCTEXTES_PREGNANCYUSE,
				DESCTEXTES.SIDEACTIONS as DESCTEXTES_SIDEACTIONS,
				DESCTEXTES.INTERACTIONS as DESCTEXTES_INTERACTIONS,
				DESCTEXTES.USEMETHODANDDOSES as DESCTEXTES_USEMETHODANDDOSES,
				DESCTEXTES.INSTRFORPAC as DESCTEXTES_INSTRFORPAC,
				DESCTEXTES.OVERDOSE as DESCTEXTES_OVERDOSE,
				DESCTEXTES.PRECAUTIONS as DESCTEXTES_PRECAUTIONS,
				DESCTEXTES.SPECIALGUIDELINES as DESCTEXTES_SPECIALGUIDELINES,
				IDENT_WIND_STR.IDENT_WIND_STR_id,
				case when IDENT_WIND_STR.IWID is not null
					then '".DRUGSPATH."'+cast(IDENT_WIND_STR.IWID as varchar(10))+'.gif'
					else ''
				end as file_url,
				convert(varchar(20), cast(PREP.PREP_insDT as datetime), 113) as autor_date_ins,
				isnull(PMU_INS.PMUser_Name, '-') as autor_username_ins,
				case
					when PREP.pmUser_insID <> 1 then LPU_INS.Lpu_Name
					else 'РЛС'
				end as autor_orgname_ins,
				
				convert(varchar(20), cast(PREP.PREP_updDT as datetime), 113) as autor_date_upd,
				isnull(PMU_UPD.PMUser_Name, '-') as autor_username_upd,
				case
					when PREP.pmUser_updID <> 1 then LPU_UPD.Lpu_Name
					else 'РЛС'
				end as autor_orgname_upd
			from
				rls.v_Nomen Nomen with(nolock)
				left join rls.v_PREP PREP with(nolock) on PREP.Prep_id = Nomen.PREPID
				left join rls.v_TRADENAMES TRADENAMES with(nolock) on TRADENAMES.TRADENAMES_ID = PREP.TRADENAMEID
				left join rls.v_LATINNAMES LATINNAMES with(nolock) on LATINNAMES.LATINNAMES_ID = PREP.LATINNAMEID
				left join rls.v_REGCERT REGCERT with(nolock) on REGCERT.REGCERT_ID = PREP.REGCERTID
				left join rls.v_REGCERT_EXTRAFIRMS REGCERT_EXTRAFIRMS with(nolock) on REGCERT_EXTRAFIRMS.CERTID = PREP.REGCERTID
				left join rls.v_FIRMS FIRMS with(nolock) on FIRMS.FIRMS_ID = PREP.FIRMID
				left join rls.v_FIRMS FIRMS_Packer with(nolock) on FIRMS_Packer.FIRMS_ID = Nomen.FIRMID
				left join rls.v_FIRMS FIRMS_RegOwner with(nolock) on FIRMS_RegOwner.FIRMS_ID = REGCERT_EXTRAFIRMS.FIRMID
				left join rls.v_FIRMNAMES FIRMNAMES with(nolock) on FIRMNAMES.FIRMNAMES_ID = FIRMS.NAMEID
				left join rls.v_CLSDRUGFORMS CLSDRUGFORMS with(nolock) on CLSDRUGFORMS.CLSDRUGFORMS_ID = PREP.DRUGFORMID
				left join rls.v_DRUGLIFETIME DRUGLIFETIME with(nolock) on DRUGLIFETIME.DRUGLIFETIME_ID = Nomen.LIFEID
				left join rls.v_DRUGSTORCOND DRUGSTORCOND with(nolock) on DRUGSTORCOND.DRUGSTORCOND_ID = Nomen.CONDID
				left join rls.PREP_PHARMAGROUP PREP_PHARMAGROUP with(nolock) on PREP_PHARMAGROUP.PREPID = PREP.Prep_id
				left join rls.v_ExtemporalNomen ExtemporalNomen with(nolock) on ExtemporalNomen.Nomen_id = Nomen.NOMEN_ID
				left join rls.v_Extemporal Extemporal with(nolock) on Extemporal.Extemporal_id = ExtemporalNomen.Extemporal_id
				outer apply (
					select top 1 PREPID, UNIQID from rls.PREP_ATC with (nolock) where PREPID = PREP.Prep_id
				) as PREP_ATC
				outer apply (
					select top 1 PREPID, UNIQID, PHGRID from rls.PREP_IIC with (nolock) where PREPID = PREP.Prep_id
				) as PREP_IIC
				left join rls.TN_DF_LIMP TN_DF_LIMP with(nolock) on TN_DF_LIMP.TRADENAMEID = TRADENAMES.TRADENAMES_ID
					and TN_DF_LIMP.DRUGFORMID = CLSDRUGFORMS.CLSDRUGFORMS_ID
				outer apply (
					select top 1 TRADENAMEID, DRUGFORMID from rls.TRADENAMES_DRUGFORMS with (nolock)
					where TRADENAMEID = TRADENAMES.TRADENAMES_ID and DRUGFORMID = CLSDRUGFORMS.CLSDRUGFORMS_ID
				) as TRADENAMES_DRUGFORMS
				outer apply(
					select top 1
						DESCRIPTIONS.*
					from
						rls.NOMEN_DESC NOMEN_DESC with (nolock)
						left join rls.DESCRIPTIONS DESCRIPTIONS with(nolock) on DESCRIPTIONS.DESCRIPTIONS_ID = NOMEN_DESC.DESCID
					where
						NOMEN_DESC.NOMENID = Nomen.NOMEN_ID and
						DESCRIPTIONS.FIRMID = FIRMS.FIRMS_ID
					order by
						DESCRIPTIONS.DESCRIPTIONS_ID
				) as DESCRIPTIONS
				left join rls.v_DESCTEXTES DESCTEXTES with(nolock) on DESCTEXTES.DESCID = DESCRIPTIONS.DESCRIPTIONS_ID
				left join rls.IDENT_WIND_STR IDENT_WIND_STR with(nolock) on IDENT_WIND_STR.NOMENID = Nomen.NOMEN_ID
				left join rls.v_SIZEUNITS SIZEUNITS with(nolock) on SIZEUNITS.SIZEUNITS_ID = PREP.DFSIZEID
				
				left join v_pmUserCache PMU_INS with(nolock) on PMU_INS.PMUser_id = PREP.pmUser_insID
				left join v_Lpu LPU_INS with(nolock) on LPU_INS.Lpu_id = PMU_INS.Lpu_id
				left join v_pmUserCache PMU_UPD with(nolock) on PMU_UPD.PMUser_id = PREP.pmUser_updID
				left join v_Lpu LPU_UPD with(nolock) on LPU_UPD.Lpu_id = PMU_UPD.Lpu_id
			where
				Nomen.NOMEN_ID = :Nomen_id
		";
		/*		and Nomen.PrepType_id = :PrepType_id
				and PREP.PrepType_id = :PrepType_id
		";*/
		//echo getDebugSQL($query, $data); exit();
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

    /**
     * Получаем наименования
     */
	function getNomens($data)
	{
		$filter = "1=1";
		if( isset($data['Prep_id']) && !empty($data['Prep_id']) ) {
			$filter .= " and PREPID = :Prep_id";
		}
		if( isset($data['Nomen_deleted']) && !empty($data['Nomen_deleted']) ) {
			$filter .= " and isnull(Nomen_deleted, 1) = :Nomen_deleted";
		}
		
		$query = "			
			select
				Nomen_id 
			from
				rls.Nomen with(nolock)
			where
				{$filter}
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

    /**
     * Получаем препараты
     */
	function getPreps($data)
	{
		$filter = "1=1";
		if( isset($data['TradeNames_id']) && !empty($data['TradeNames_id']) ) {
			$filter .= " and TRADENAMEID = :TradeNames_id";
		}
		if( isset($data['Prep_deleted']) && !empty($data['Prep_deleted']) ) {
			$filter .= " and isnull(Prep_deleted, 1) = :Prep_deleted";
		}
		
		$query = "
			select
				Prep_id
			from
				rls.PREP with(nolock)
			where
				{$filter}
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

    /**
     * Удаляем наименования
     */
	function deleteNomen($data)
	{
		// если не суперадмин, то нужна проверка имеет ли право юзер удалять (т/у, препарат, т/н)
		if(!isSuperAdmin()) {
			$checkQuery = "
				select top 1
					pmUser_insID
				from
					rls.NOMEN with(nolock)
				where	
					NOMEN_ID = :Nomen_id
			";
			$checkresult = $this->db->query($checkQuery, $data);
			if ( !is_object($checkresult) )
				return false;
			$checkresult = $checkresult->result('array');
			if($checkresult[0] && $checkresult[0]['pmUser_insID'] !== $data['pmUser_id']) {
				return array(array('success' => false, 'Error_Msg' => 'У вас нет прав на удаление этой номенклатуры!'));
			}
		}
		$query = "	
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec rls.p_Nomen_del
				@NOMEN_ID = :Nomen_id,
				@pmUser_delID = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$res = $this->db->query($query, $data);
		if ( !is_object($res) )
			return false;
		
		$result = $res->result('array');
		if( strlen($result[0]['Error_Msg']) > 0 )
			return $result;
			

		$nomens = $this->getNomens(array(
			'Prep_id' => $data['Prep_id'],
			'Nomen_deleted' => 1 // не удаленные
		));
		if( !is_array($nomens) )
			return false;

		// тоесть если у этого препарата есть другие упаковки
		if( count($nomens) > 0 )
			return $result;
		
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec rls.p_Prep_del
				@Prep_id = :Prep_id,
				@pmUser_delID = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$res = $this->db->query($query, $data);
		if ( !is_object($res) )
			return false;
		
		$result = $res->result('array');
		if( strlen($result[0]['Error_Msg']) > 0 )
			return $result;
		
		
		$preps = $this->getPreps(array(
			'TradeNames_id' => $data['TradeNames_id'],
			'Prep_deleted' => 1 // не удаленные
		));
		if( !is_array($preps) )
			return false;
		
		// тоесть если у торг. назв. есть другие препараты
		if( count($preps) > 0 )
			return $result;
		
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec rls.p_TRADENAMES_del
				@TRADENAMES_ID = :TradeNames_id,
				@pmUser_delID = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
     * Сохраняем связь рецептуры
     */
	function saveExtemporalNomen($data)
	{
		$query = "
			select ExtemporalNomen_id
			from rls.v_ExtemporalNomen with (nolock)
			where Extemporal_id = :Extemporal_id and Nomen_id = :NOMEN_ID
		";
	
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			$res = $res->result('array');
		} else {
			return false;
		}
		if(count($res) == 0){
			$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = null;
				exec rls.p_ExtemporalNomen_ins
					@ExtemporalNomen_id = @Res output,
					@Extemporal_id = :Extemporal_id,
					@Nomen_id = :NOMEN_ID,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as ExtemporalNomen_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
		
			$res = $this->db->query($query, $data);
			if ( is_object($res) ) {
				return $res->result('array');
			} else {
				return false;
			}
		} else {
			return $res;
		}
	}

	/**
     * проверка на наличие записи в справочнике Действующих веществ записи с именем равным значению поля МНН 
	 * (Если в форме заполнены данные о рецептуре)
     */
	function checkActmattersName($data)
	{
		$query = "
			select top 1 ACTMATTERS_ID
			from rls.v_ACTMATTERS with (nolock)
			where RUSNAME = :Actmatters_Names
		";
	
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			$res = $res->result('array');
		} else {
			return false;
		}
		if(count($res) == 0){
			$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = null;
				exec rls.p_ACTMATTERS_ins
					@ACTMATTERS_ID = @Res output,
					@RUSNAME = :Actmatters_Names,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as ACTMATTERS_ID, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
		
			$res = $this->db->query($query, $data);
			if ( is_object($res) ) {
				return $res->result('array');
			} else {
				return false;
			}
		} else {
			return $res;
		}
	}
	
}
