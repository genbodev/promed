<?php defined('BASEPATH') or die ('No direct script access allowed');

class CommercialOffer_model extends swModel {
	private $CommercialOffer_id;//идентификатор
	private $CommercialOffer_begDT;//дата
	private $Org_id;//поставщик
	private $CommercialOffer_Comment;//примечание
	private $pmUser_id;//Идентификатор пользователя системы Промед
    private $Org_did;//организация, в адрес которой направлено коммерческое предложение

	/**
	 *  Установка значения
	 */
	public function getCommercialOffer_id() { return $this->CommercialOffer_id;}

	/**
	 *  Получение значения
	 */
	public function setCommercialOffer_id($value) { $this->CommercialOffer_id = $value; }

	/**
	 *  Установка значения
	 */
	public function getCommercialOffer_begDT() { return $this->CommercialOffer_begDT;}

	/**
	 *  Получение значения
	 */
	public function setCommercialOffer_begDT($value) { $this->CommercialOffer_begDT = $value; }

	/**
	 *  Установка значения
	 */
	public function getOrg_id() { return $this->Org_id;}

	/**
	 *  Получение значения
	 */
	public function setOrg_id($value) { $this->Org_id = $value; }

	/**
	 *  Установка значения
	 */
	public function getCommercialOffer_Comment() { return $this->CommercialOffer_Comment;}

	/**
	 *  Получение значения
	 */
	public function setCommercialOffer_Comment($value) { $this->CommercialOffer_Comment = $value; }

    /**
     *  Установка значения
     */
    public function getOrg_did() { return $this->Org_did;}

    /**
     *  Получение значения
     */
    public function setOrg_did($value) { $this->Org_did = $value; }

	/**
	 *  Установка значения
	 */
	public function getpmUser_id() { return $this->pmUser_id;}

	/**
	 *  Получение значения
	 */
	public function setpmUser_id($value) { $this->pmUser_id = $value; }

	/**
	 *  Конструктор
	 */
	function __construct(){
		if (isset($_SESSION['pmuser_id'])) {
			$this->setpmUser_id($_SESSION['pmuser_id']);
		} else {
			throw new Exception('Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)');
		}
	}

	/**
	 *  Загрузка коммерческого предложения
	 */
	function load() {
		$q = "
			select
				co.CommercialOffer_id,
				co.CommercialOffer_begDT,
				co.Org_id,
				o.Org_Name,
				o.Org_OGRN,
				co.CommercialOffer_Comment,
				co.Org_did,
				co.CommercialOffer_Name,
				convert(varchar(10), co.CommercialOffer_endDT, 104) as CommercialOffer_endDT,
				(case
				    when co.CommercialOffer_Status = 1 then 'Действующий'
				    else ''
				end) as Status_Name
			from
				dbo.v_CommercialOffer co with (nolock)
				left join v_Org o with (nolock) on o.Org_id = co.Org_id
			where
				co.CommercialOffer_id = :CommercialOffer_id
		";
		$r = $this->db->query($q, array('CommercialOffer_id' => $this->CommercialOffer_id));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$this->CommercialOffer_id = $r[0]['CommercialOffer_id'];
				$this->CommercialOffer_begDT = $r[0]['CommercialOffer_begDT'];
				$this->Org_id = $r[0]['Org_id'];
				$this->CommercialOffer_Comment = $r[0]['CommercialOffer_Comment'];
                $this->Org_did = $r[0]['Org_did'];
				return $r;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 *  Загрузка списка коммерческих предложений
	 */
	function loadList($filter) {
		$where = array();
		$p = array();

		if (!empty($filter['Year'])) {
			$where[] = 'datepart(year, v_CommercialOffer.CommercialOffer_begDT) = :Year';
			$p['Year'] = $filter['Year'];
		}
		if (isset($filter['Org_did']) && $filter['Org_did']) {
			$where[] = '(v_CommercialOffer.Org_did is null or v_CommercialOffer.Org_did = :Org_did)';
			$p['Org_did'] = $filter['Org_did'];
		}

		$where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'where '.$where_clause;
		}

		$query = "
			select
				v_CommercialOffer.CommercialOffer_id,
				convert(varchar(10),v_CommercialOffer.CommercialOffer_begDT, 104) as CommercialOffer_begDT,
				v_CommercialOffer.CommercialOffer_Name,
				v_CommercialOffer.Org_id,
				v_CommercialOffer.CommercialOffer_Status,
				v_CommercialOffer.CommercialOffer_Comment,
				Org_id_ref.Org_Name Org_id_Name
			from
				dbo.v_CommercialOffer with (nolock)
				left join dbo.v_Org Org_id_ref with (nolock) on Org_id_ref.Org_id = v_CommercialOffer.Org_id
			$where_clause
		";
		$result = $this->db->query($query, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Загрузка спецификации коммерческого предложения
	 */
	function loadCommercialOfferDrugList($filter) {
		$select = array();
		$join = array();
		$where = array();
		$params = array();

		if (isset($filter['CommercialOffer_id']) && $filter['CommercialOffer_id']) {
			$where[] = 'v_CommercialOfferDrug.CommercialOffer_id = :CommercialOffer_id';
            $params['CommercialOffer_id'] = $filter['CommercialOffer_id'];
		}

        if (getRegionNick() == 'kz') { //для Казахстана
            $select[] = "dp.DrugPrep_Name";
            $select[] = "gu.GoodsUnit_Name";
            $select[] = "km_d.MnnName as KM_Drug_MnnName";
            $select[] = "v_CommercialOfferDrug.CommercialOfferDrug_MnnName";
            $select[] = "v_CommercialOfferDrug.CommercialOfferDrug_PharmName";
            $select[] = "v_CommercialOfferDrug.CommercialOfferDrug_Form";
            $select[] = "v_CommercialOfferDrug.CommercialOfferDrug_Package";
            $select[] = "v_CommercialOfferDrug.CommercialOfferDrug_UnitName";
            $select[] = "v_CommercialOfferDrug.CommercialOfferDrug_RegCertName";
            $select[] = "v_CommercialOfferDrug.CommercialOfferDrug_ProdName";
            $select[] = "v_CommercialOfferDrug.CommercialOfferDrug_ProdCountry";
            $select[] = "v_CommercialOfferDrug.CommercialOfferDrug_PriceDetail";
            $select[] = "v_CommercialOfferDrug.CommercialOfferDrug_PrevPriceDetail";
            $select[] = "convert(varchar(10), v_CommercialOfferDrug.CommercialOfferDrug_updDT, 104) as CommercialOfferDrug_updDT";

            $join[] = "left join rls.v_DrugPrep dp with (nolock) on dp.DrugPrepFas_id = d.DrugPrepFas_id";
            $join[] = "left join v_GoodsUnit gu with (nolock) on gu.GoodsUnit_id = v_CommercialOfferDrug.GoodsUnit_id";
            $join[] = "left join r101.KM_Drug km_d with (nolock) on km_d.KM_Drug_guid = v_CommercialOfferDrug.Goods_guid";
        } else {
            //$select[] = "Fas.Value as Drug_Fas"; //Фасовка
            $select[] = "(isnull(df.NAME, '')+isnull(' '+Dose.Value, '')+isnull(' '+Fas.Value, '')) as DrugForm_FullName"; //Полная форма выпуска
            $select[] = "isnull(dpfc.DrugPrepFasCode_Code, '') as DrugPrepFasCode_Code";
            $select[] = "cmp.NAME as ClsMzPhGroup_Name"; //Фармгруппа МЗ
			$select[] = "rc.REGNUM as Reg_Num"; //№ РУ
			$select[] = "fn.NAME as Firm_Name"; //Производитель
            //$select[] = "Dose.Value as Drug_Dose"; //Дозировка
			$select[] = "atc.NAME as Atc_Name"; //АТХ

            $join[] = "left join rls.REGCERT rc with (nolock) on rc.REGCERT_ID = p.REGCERTID";
            $join[] = "left join rls.FIRMS f with (nolock) on f.FIRMS_ID = p.FIRMID";
            $join[] = "left join rls.FIRMNAMES fn with (nolock) on fn.FIRMNAMES_ID = f.NAMEID";
            $join[] = "
				outer apply (
					select
						replace(replace((
							case when
								isnull(p.DRUGDOSE,0) > 0
							then
								cast(cast(p.DRUGDOSE as float) as varchar)+' доз, '
							else
								''
							end
						)+
						isnull(coalesce(cast(cast(n.PPACKMASS as float) as varchar)+' '+mu.SHORTNAME, cast(cast(n.PPACKVOLUME as float) as varchar)+' '+cu.SHORTNAME)+', ','')+
						(
							case when
								isnull(n.DRUGSINPPACK,0) > 0
							then
								'№ '+
								(case when
									isnull(n.PPACKINUPACK,0) > 0
								then
									cast(n.DRUGSINPPACK*n.PPACKINUPACK as varchar)
								else
									cast(n.DRUGSINPPACK as varchar)
								end)
							else
								case when
									isnull(n.PPACKINUPACK,0) > 0
								then
									'№ '+cast(n.PPACKINUPACK as varchar)
								else
									''
								end
							end
						)+',,', ', ,,', ''), ',,', '') as Value
				) Fas
            ";
            $join[] = "
				outer apply (
                    select top 1
                        i_dpfc.DrugPrepFasCode_Code
                    from
                        rls.v_DrugPrepFasCode i_dpfc with (nolock)
                    where
                         i_dpfc.DrugPrepFas_id = d.DrugPrepFas_id and
                         isnull(i_dpfc.Org_id, 0) = isnull(CommercialOffer_id_ref.Org_did, 0)
                    order by
                        i_dpfc.DrugPrepFasCode_id
                ) dpfc
            ";
            $join[] = "
                outer apply (
                    select top 1
                        i_cmp.NAME
                    from
                        rls.v_PREP_PHARMAGROUP i_pp with(nolock)
                        left join  rls.v_CLS_MZ_PHGROUP i_cmp with (nolock) on i_cmp.CLS_MZ_PHGROUP_ID = i_pp.UNIQID
                    where
                        i_pp.PREPID = p.Prep_id
                    order by
                        i_cmp.CLS_MZ_PHGROUP_ID
                ) cmp
            ";
            $join[] = "
				left join rls.MASSUNITS df_mu with (nolock) on df_mu.MASSUNITS_ID = p.DFMASSID
				left join rls.CONCENUNITS df_cu with (nolock) on df_cu.CONCENUNITS_ID = p.DFCONCID
				left join rls.ACTUNITS df_au with (nolock) on df_au.ACTUNITS_ID = p.DFACTID
				left join rls.SIZEUNITS df_su with (nolock) on df_su.SIZEUNITS_ID = p.DFSIZEID
				outer apply (
					select coalesce(
						cast(cast(p.DFMASS as float) as varchar)+' '+df_mu.SHORTNAME,
						cast(cast(p.DFCONC as float) as varchar)+' '+df_cu.SHORTNAME,
						cast(p.DFACT as varchar)+' '+df_au.SHORTNAME,
						cast(p.DFSIZE as varchar)+' '+df_su.SHORTNAME
					) as Value
				) Dose
            ";
            $join[] = "
				outer apply (
                    select top 1
                        i_catc.NAME
                    from
                        rls.PREP_ATC i_patc with (nolock)
                        left join rls.CLSATC i_catc with (nolock) on i_catc.CLSATC_ID = i_patc.UNIQID
                    where
                         i_patc.PREPID = p.Prep_id
                ) atc
            ";
        }

        $select_clause = count($select) > 0 ? ', '.implode(', ', $select) : '';

        $join_clause = implode(' ', $join);

		$where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'where '.$where_clause;
		}

		$query = "
			select
				v_CommercialOfferDrug.CommercialOfferDrug_id,
				v_CommercialOfferDrug.CommercialOffer_id,
				v_CommercialOfferDrug.Drug_id,
				v_CommercialOfferDrug.DrugPrepFas_id,
				v_CommercialOfferDrug.CommercialOfferDrug_Price,
				v_CommercialOfferDrug.GoodsUnit_id,
				isnull(DrugNomen_ref.DrugNomen_Code, '') as DrugNomen_Code,
				d.Drug_Name as Drug_Name,
				am.RUSNAME as ActMatters_RusName, --МНН
				tn.NAME as TradeName_Name, --Торговое наименование
				df.NAME as DrugForm_Name --Форма выпуска
				$select_clause
			from
				dbo.v_CommercialOfferDrug with (nolock)
				left join dbo.v_CommercialOffer CommercialOffer_id_ref with (nolock) on CommercialOffer_id_ref.CommercialOffer_id = v_CommercialOfferDrug.CommercialOffer_id
				outer apply (
				    select top 1
				        *
				    from
				        rls.v_Drug i_d with (nolock)
				    where
                        i_d.Drug_id = v_CommercialOfferDrug.Drug_id or (
                            v_CommercialOfferDrug.Drug_id is null and
                            i_d.DrugPrepFas_id = v_CommercialOfferDrug.DrugPrepFas_id
                        )
				    /*order by
				        i_d.Drug_id*/ -- очень сильно тормозит запрос, а полезность относительно не велика
				) d
				left join rls.v_DrugNomen DrugNomen_ref with (nolock) on DrugNomen_ref.Drug_id = v_CommercialOfferDrug.Drug_id
				left join rls.Nomen n with (nolock) on n.NOMEN_ID = d.Drug_id
				left join rls.Prep p with (nolock) on p.Prep_id = n.PREPID
				left join rls.PREP_ACTMATTERS pa with (nolock) on pa.PREPID = n.PREPID
				left join rls.TRADENAMES tn with (nolock) on tn.TRADENAMES_ID = p.TRADENAMEID
				left join rls.CLSDRUGFORMS df with (nolock) on df.CLSDRUGFORMS_ID = p.DRUGFORMID
				left join rls.ACTMATTERS am with (nolock) on am.ACTMATTERS_ID = pa.MATTERID
				left join rls.MASSUNITS mu with (nolock) on mu.MASSUNITS_ID = n.PPACKMASSUNID
				left join rls.CUBICUNITS cu with (nolock) on cu.CUBICUNITS_ID = n.PPACKCUBUNID
                $join_clause
			$where_clause
			order by
				v_CommercialOfferDrug.CommercialOfferDrug_id
		";
		$result = $this->db->query($query, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Сохранение коммерческого предложения
	 */
	function save() {
        $response = $this->saveObject('CommercialOffer', array(
            'CommercialOffer_id' => $this->CommercialOffer_id,
			'CommercialOffer_begDT' => $this->CommercialOffer_begDT,
			'Org_id' => $this->Org_id,
			'CommercialOffer_Comment' => $this->CommercialOffer_Comment,
            'Org_did' => $this->Org_did
        ));
		if (!empty($response['CommercialOffer_id'])) {
			$this->CommercialOffer_id = $response['CommercialOffer_id'];
            $result = $response;
		} else {
			$result['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных';
		}
		return array($result);
	}

	/**
	 *  Удаление коммерческого предложения
	 */
	function delete($data) {
        $error = array();
        $id_array = array();

        if (!empty($data['id'])) {
            $id_array[] = $data['id'];
        }

        if (!empty($data['id_list'])) {
            $id_array = explode(',', $data['id_list']);
        }

        $this->load->model("PMMediaData_model", "PMMediaData_model");
        $this->beginTransaction();

        foreach($id_array as $id) {
            //проверяем наличие связанных контарктов
            $query = "
                select
                    count(WhsDocumentSupply_id) as cnt
                from
                    v_WhsDocumentSupply with (nolock)
                where
                    CommercialOffer_id = :CommercialOffer_id;
            ";
            $cnt = $this->getFirstResultFromQuery($query, array(
                'CommercialOffer_id' => $id
            ));
            if ($cnt > 0) {
                $error[] = "Удаление прайса невозможно, т.к. его данные используются в системе";
            }

            //проверяем использование позиций из списка медикаментов
            $query = "
                select
                    count(cod.CommercialOfferDrug_id) as cnt
                from
                    v_CommercialOfferDrug cod with (nolock)
                    left join v_WhsDocumentCommercialOfferDrug wdcod with (nolock) on wdcod.CommercialOfferDrug_id = cod.CommercialOfferDrug_id
                    left join v_WhsDocumentSupplySpec wdss with (nolock) on wdss.CommercialOfferDrug_id = cod.CommercialOfferDrug_id
                    left join v_WhsDocumentUcPriceHistory wduph with (nolock) on wduph.CommercialOfferDrug_id = cod.CommercialOfferDrug_id
                where
                    cod.CommercialOffer_id = :CommercialOffer_id and
                    (
                        wdcod.WhsDocumentCommercialOfferDrug_id is not null or
                        wdss.WhsDocumentSupplySpec_id is not null or
                        wduph.WhsDocumentUcPriceHistory_id is not null
                    );
            ";
            $cnt = $this->getFirstResultFromQuery($query, array(
                'CommercialOffer_id' => $id
            ));
            if ($cnt > 0) {
                $error[] = "Удаление прайса невозможно, т.к. его данные используются в системе";
            }

            //удаляем прикрепленные файлы
            if (count($error) == 0) {
                $media_array = $this->PMMediaData_model->loadpmMediaDataListGrid(array(
                    'ObjectName' => 'CommercialOffer',
                    'ObjectID' => $id
                ));
                foreach($media_array as $media) {
                    $result = $this->PMMediaData_model->deletepmMediaData($media);
                    if (count($result) > 0 &&!empty($result[0]['Error_Msg'])) {
                        $error[] = $result[0]['Error_Msg'];
                        break;
                    }
                }
            }

            //удаляем список медикаментов
            if (count($error) == 0) {
                $query = "
                    delete from
                        CommercialOfferDrug
                    where
                        CommercialOffer_id = :CommercialOffer_id;
                ";
                $result = $this->db->query($query, array(
                    'CommercialOffer_id' => $id
                ));
            }

            //удаляем коммерческое предложение
            if (count($error) == 0) {
                $query = "
                    declare
                        @ErrCode int,
                        @ErrMessage varchar(4000);
                    exec dbo.p_CommercialOffer_del
                        @CommercialOffer_id = :CommercialOffer_id,
                        @Error_Code = @ErrCode output,
                        @Error_Message = @ErrMessage output;
                    select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
                ";
                $result = $this->getFirstRowFromQuery($query, array(
                    'CommercialOffer_id' => $id
                ));
            }
        }

        if (count($error) > 0) {
            $this->rollbackTransaction();
            return array(array('Error_Msg' => $error[0]));
        } else {
            $this->commitTransaction();
            return array(array('Error_Code' => null, 'Error_Msg' => null));
        }
    }

    /**
     * Дополнительные проверки данных перед удалением
     */
    function checkBeforeDelete($data) {
        $check_array = array();
        $id_array = array();
        $error_array = array();

        if (!empty($data['id'])) {
            $id_array[] = $data['id'];
        }

        if (!empty($data['id_list'])) {
            $id_array = explode(',', $data['id_list']);
        }

        //очистка списка идентификаторов
        for ($i = 0; $i < count($id_array); $i++) {
            if (!is_numeric($id_array[$i])) {
                unset($id_array[$i]);
            }
        }

        if (!empty($data['check_list'])) {
            $check_array = explode(',', $data['check_list']);
        }

        foreach($check_array as $check_name) {
            switch($check_name) {
                case 'kz_org_ogrn':
                    if (count($id_array) > 0) {
                        $query = "
                            select
                                count(co.CommercialOffer_id) as cnt
                            from
                                dbo.v_CommercialOffer co with (nolock)
                                left join v_Org o with (nolock) on o.Org_id = co.Org_id
                            where
                                co.CommercialOffer_id in (".join(',', $id_array).") and
                                o.Org_OGRN = :Org_OGRN
                        ";
                        $cnt = $this->getFirstResultFromQuery($query, array(
                            'Org_OGRN' => '090340007747' //090340007747 - ОГРН организации "СК Фармацея"
                        ));
                        if ($cnt > 0) { //если среди удаляемых, есть прайсы организации "СК Фармацея", то выдаем ошибку
                            $error_array[] = "Удаление прайса невозможно";
                        }
                    }
                    break;
            }
        }

        $result = array('Error_Msg' => null);
        if (count($error_array) > 0) {
            $result['Error_Msg'] = $error_array[0];
        }
        return $result;
    }

	/**
	 *  Загрузка данных для комбобокса
	 */
	function loadRlsDrugCombo($filter) {
		$where = array();

		if ($filter['Drug_id'] > 0) {
			$where[] = 'Drug.Drug_id = :Drug_id';
		} else {
            if (!empty($filter['Drug_Name'])) {
                $filter['query'] = $filter['Drug_Name'];
            }

            if ($filter['DrugPrepFas_id'] > 0) {
                $where[] = 'Drug.DrugPrepFas_id = :DrugPrepFas_id';
            }
			if (strlen($filter['query']) > 0) {
				$filter['query'] = '%'.$filter['query'].'%';
				$where[] = 'DrugPrep.DrugPrep_Name LIKE :query';
			}
            if (!empty($filter['Reg_Num'])) {
                $where[] = "
                    Drug.Drug_id in (
                        select
                            i_n.NOMEN_ID as Drug_id
                        from
                            rls.REGCERT i_rc with (nolock)
                            left join rls.v_PREP i_p with (nolock) on i_p.REGCERTID = i_rc.REGCERT_ID
                            left join rls.v_Nomen i_n with (nolock) on i_n.PREPID = i_p.Prep_id
                        where
                            i_rc.REGNUM = :Reg_Num
                    )
                ";
                $filter['Reg_Num'] = trim($filter['Reg_Num']);
            }
		}

		if (count($where) > 0) {
			$q = "
				select
					Drug.Drug_id,
					Drug.Drug_Name,
					DrugNomen.DrugNomen_Code
				from
					rls.v_Drug Drug with (nolock)
					inner join rls.v_DrugPrep DrugPrep WITH (NOLOCK) on Drug.DrugPrepFas_id = DrugPrep.DrugPrepFas_id
					left join rls.v_DrugNomen DrugNomen with (nolock) on DrugNomen.Drug_id = Drug.Drug_id
				where
			".join($where, ' and ');

			$result = $this->db->query($q, $filter);
			if ( is_object($result) ) {
				return $result->result('array');
			}
		}

		return false;
	}

    /**
	 *  Загрузка данных для комбобокса
	 */
	function loadRlsDrugPrepFasCombo($filter) {
		$where = array();

		if ($filter['DrugPrepFas_id'] > 0) {
			$where[] = 'dp.DrugPrepFas_id = :DrugPrepFas_id';
		} else {
			if (strlen($filter['query']) > 0) {
				$filter['query'] = '%'.$filter['query'].'%';
				$where[] = 'dp.DrugPrep_Name like :query';
			}
		}

		if (count($where) > 0) {
			$q = "
				select
					dp.DrugPrepFas_id,
					dp.DrugPrep_Name,
					dpfc.DrugPrepFasCode_Code
				from
					rls.v_DrugPrep dp with (nolock)
					outer apply (
					    select top 1
					        i_dpfc.DrugPrepFasCode_Code
					    from
					        rls.v_DrugPrepFasCode i_dpfc with (nolock)
					    where
					         i_dpfc.DrugPrepFas_id = dp.DrugPrepFas_id and
					         isnull(i_dpfc.Org_id, 0) = isnull(:Org_id, 0)
					    order by
					        i_dpfc.DrugPrepFasCode_id
					) dpfc
				where
			".join($where, ' and ');

			$result = $this->db->query($q, $filter);
			if ( is_object($result) ) {
				return $result->result('array');
			}
		}

		return false;
	}

	/**
	 *  Сохранение спецификации коммерческого предложения из JSON
	 */
	function saveCommercialOfferDrugFromJSON($data) {
        $error = array();
        $result = array();

		if (!empty($data['json_str']) && $data['CommercialOffer_id'] > 0) {
            ConvertFromWin1251ToUTF8($data['json_str']);
			$dt = (array) json_decode($data['json_str']);

			foreach($dt as $record) {
                //проверка допустимости редактирования или удаления медикамента
                //if (in_array($record->state, ['edit', 'delete'])) {
				if (in_array($record->state, array('edit','delete'))) {
                    $cnt_data = $this->getLinkedObjectCount('CommercialOfferDrug', $record->CommercialOfferDrug_id);

                    //если строка коммерческого предложения уже используется в спецификации контракта или в лотах, редактирование и удаление недопустимы
                    if (!empty($cnt_data['WhsDocumentCommercialOfferDrug_Cnt']) || !empty($cnt_data['WhsDocumentSupplySpec_Cnt'])) {
                        $error[] = ($record['state'] == 'delete' ? "Удаление медикамента из прайса" : "Редактирование медикамента в прайсе")." невозможно, т.к. прайс действующий и используется в системе";
                    } else if(!empty($cnt_data['WhsDocumentUcPriceHistory_Cnt']) && $record->state == 'delete') { //если строка упоминается в истории цен, то удалять её нельзя
                        $error[] = "Удаление медикамента из прайса невозможно, т.к. прайс действующий и используется в контрактах";
                    }
                }

                if (count($error) > 0) {
                    break;
                }

				switch($record->state) {
					case 'add':
					case 'edit':
                        $save_data = array(
                            'CommercialOfferDrug_id' => $record->state == 'edit' ? $record->CommercialOfferDrug_id : null,
                            'CommercialOffer_id' => $data['CommercialOffer_id'],
                            'Drug_id' => !empty($record->Drug_id) ? $record->Drug_id : null,
                            'DrugPrepFas_id' => !empty($record->DrugPrepFas_id) ? $record->DrugPrepFas_id : null,
                            'CommercialOfferDrug_Price' => !empty($record->CommercialOfferDrug_Price) ? $record->CommercialOfferDrug_Price : null,
                            'GoodsUnit_id' => !empty($record->GoodsUnit_id) ? $record->GoodsUnit_id : null
                        );
                        $response = $this->saveObject('CommercialOfferDrug', $save_data);
                        if (!empty($response['Error_Msg'])) {
                            $error[] = $response['Error_Msg'];
                        }
						break;
					case 'delete':
                        $response = $this->deleteObject('CommercialOfferDrug', array(
                            'CommercialOfferDrug_id' => $record->CommercialOfferDrug_id
                        ));
                        if (!empty($response['Error_Msg'])) {
                            $error[] = $response['Error_Msg'];
                        }
						break;
				}
			}
		}

        if (count($error) > 0) {
            $result['Error_Msg'] = $error[0];
        }

        return $result;
	}

	/**
	 *  Импорт спецификации коммерческого предложения из xls файла.
	 */
	function importFromXls($data) {
		require_once("promed/libraries/Spreadsheet_Excel_Reader/Spreadsheet_Excel_Reader.php");

		$result = array(array('Error_Msg' => null));
        $log_arr = array();
        $drug_count = 0;

		//указываем из каких столбцов файла брать код и цену медикаментов
		$code_col = 2;
		$price_col = 7;

		$xls_data = new Spreadsheet_Excel_Reader();
        $xls_data->setOutputEncoding('UTF-8');
		$xls_data->read($data['FileFullName']);

        //загружаем данные коммерческого предложения
        $this->setCommercialOffer_id($data['CommercialOffer_id']);
        $this->load();

		if (isset($xls_data->sheets[0])) {
			for ($i = 1; $i <= $xls_data->sheets[0]['numRows']; $i++) {
				if (isset($xls_data->sheets[0]['cells'][$i]) && isset($xls_data->sheets[0]['cells'][$i][$code_col]) && isset($xls_data->sheets[0]['cells'][$i][$price_col])) {
					$code = $xls_data->sheets[0]['cells'][$i][$code_col];
					$price = $xls_data->sheets[0]['cells'][$i][$price_col];
					if (is_numeric($code) && is_numeric($price) && $code > 0 && $price > 0) {
						$response = $this->saveCommercialOfferDrugFromXLS(array(
							'CommercialOffer_id' => $data['CommercialOffer_id'],
							'Org_did' => $this->getOrg_did(),
							'DrugPrepFasCode_Code' => $code,
							'CommercialOfferDrug_Price' => $price,
							'pmUser_id' => $data['pmUser_id']
						));
                        if (empty($response['DrugPrepFas_id'])) {
                            $log_arr[] = join(' ', $xls_data->sheets[0]['cells'][$i]);
                        } else {
                            $drug_count++;
                        }
					}
				}
			}
		}

        if (count($log_arr) > 0 || $drug_count == 0) {
            array_unshift($log_arr, 'Импорт не выполнен для медикаментов, т.к. не найден Код');
            $result[0]['Protocol_Link'] = $this->getImportFromXlsProtocol($log_arr);
        }

		return $result;
	}

	/**
	 *  Вспомогательная функция для импорта из xls.
	 */
	function saveCommercialOfferDrugFromXLS($data) {
		$q = "
			declare
				@CommercialOfferDrug_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@Drug_id bigint = null,
				@DrugPrepFas_id bigint;

			set @DrugPrepFas_id = (
			    select top 1
			        DrugPrepFas_id
			    from
			        rls.DrugPrepFasCode with (nolock)
			    where
			        DrugPrepFasCode_Code = :DrugPrepFasCode_Code and
			        isnull(Org_id, 0) = isnull(:Org_did, 0)
			);

			if (@DrugPrepFas_id is not null)
			begin
				exec dbo.p_CommercialOfferDrug_ins
					@CommercialOfferDrug_id = null,
					@CommercialOffer_id = :CommercialOffer_id,
					@Drug_id = @Drug_id,
					@DrugPrepFas_id = @DrugPrepFas_id,
					@CommercialOfferDrug_Price = :CommercialOfferDrug_Price,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
			end;

			select @CommercialOfferDrug_id as CommercialOfferDrug_id, @DrugPrepFas_id as DrugPrepFas_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->getFirstRowFromQuery($q, $data);

        return $result;
	}

    /**
     * Запись протокола импорта в файл
     */
    function getImportFromXlsProtocol($log_array) {
        $link = '';

        $out_dir = "import_com_offers_".time();
        mkdir(EXPORTPATH_REGISTRY.$out_dir);

        $msg_count = 0;
        $link = EXPORTPATH_REGISTRY.$out_dir."/protocol.txt";
        $fprot = fopen($link, 'w');

        foreach($log_array as $log_msg) {
            $msg = $log_msg;
            $msg .= "\r\n\r\n";
            fwrite($fprot, $msg);
        }

        fclose($fprot);

        return $link;
    }

	/**
	 * Получение дополнительных данных
	 */
	function getCommercialOfferDrugContext($data) {
		$q = "
			select
				isnull(dn.DrugNomen_Code, '') as DrugNomen_Code, --Номенклатурный код
				isnull(dpfc.DrugPrepFasCode_Code, '') as DrugPrepFasCode_Code, --Номенклатурный код
				am.RUSNAME as ActMatters_RusName, --МНН
				tn.NAME as TradeName_Name, --Торговое наименование
				fn.NAME as Firm_Name, --Производитель
				rc.REGNUM as Reg_Num, --№ РУ
				df.NAME as DrugForm_Name, --Форма выпуска
				Dose.Value as Drug_Dose, --Дозировка
				Fas.Value as Drug_Fas, --Фасовка
				cmp.NAME as ClsMzPhGroup_Name, -- Фармгруппа МЗ
				(isnull(df.NAME, '')+isnull(' '+Dose.Value, '')+isnull(' '+Fas.Value, '')) as DrugForm_FullName, -- Полная форма выпуска
				atc.NAME as Atc_Name, -- АТХ
				d.Drug_Name,
				dp.DrugPrep_Name
			from
				rls.v_Drug d with (nolock)
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
				left join rls.v_DrugNomen dn with (nolock) on dn.Drug_id = d.Drug_id
				left join rls.NOMEN n with (nolock) on n.NOMEN_ID = d.Drug_id
				left join rls.PREP p with (nolock) on p.Prep_id = d.DrugPrep_id
				left join rls.PREP_ACTMATTERS pa with (nolock) on pa.PREPID = n.PREPID
				left join rls.TRADENAMES tn with (nolock) on tn.TRADENAMES_ID = p.TRADENAMEID
				left join rls.CLSDRUGFORMS df with (nolock) on df.CLSDRUGFORMS_ID = p.DRUGFORMID
				left join rls.REGCERT rc with (nolock) on rc.REGCERT_ID = p.REGCERTID
				left join rls.FIRMS f with (nolock) on f.FIRMS_ID = p.FIRMID
				left join rls.FIRMNAMES fn with (nolock) on fn.FIRMNAMES_ID = f.NAMEID
				left join rls.ACTMATTERS am with (nolock) on am.ACTMATTERS_ID = pa.MATTERID
				left join rls.MASSUNITS mu with (nolock) on mu.MASSUNITS_ID = n.PPACKMASSUNID
				left join rls.CUBICUNITS cu with (nolock) on cu.CUBICUNITS_ID = n.PPACKCUBUNID
				left join rls.MASSUNITS df_mu with (nolock) on df_mu.MASSUNITS_ID = p.DFMASSID
				left join rls.CONCENUNITS df_cu with (nolock) on df_cu.CONCENUNITS_ID = p.DFCONCID
				left join rls.ACTUNITS df_au with (nolock) on df_au.ACTUNITS_ID = p.DFACTID
				left join rls.SIZEUNITS df_su with (nolock) on df_su.SIZEUNITS_ID = p.DFSIZEID
				left join rls.v_DrugPrep dp with (nolock) on dp.DrugPrepFas_id = d.DrugPrepFas_id
				outer apply (
					select coalesce(
						cast(cast(p.DFMASS as float) as varchar)+' '+df_mu.SHORTNAME,
						cast(cast(p.DFCONC as float) as varchar)+' '+df_cu.SHORTNAME,
						cast(p.DFACT as varchar)+' '+df_au.SHORTNAME,
						cast(p.DFSIZE as varchar)+' '+df_su.SHORTNAME
					) as Value
				) Dose
				outer apply (
					select
						replace(replace((
							case when
								isnull(p.DRUGDOSE,0) > 0
							then
								cast(cast(p.DRUGDOSE as float) as varchar)+' доз, '
							else
								''
							end
						)+
						isnull(coalesce(cast(cast(n.PPACKMASS as float) as varchar)+' '+mu.SHORTNAME, cast(cast(n.PPACKVOLUME as float) as varchar)+' '+cu.SHORTNAME)+', ','')+
						(
							case when
								isnull(n.DRUGSINPPACK,0) > 0
							then
								'№ '+
								(case when
									isnull(n.PPACKINUPACK,0) > 0
								then
									cast(n.DRUGSINPPACK*n.PPACKINUPACK as varchar)
								else
									cast(n.DRUGSINPPACK as varchar)
								end)
							else
								case when
									isnull(n.PPACKINUPACK,0) > 0
								then
									'№ '+cast(n.PPACKINUPACK as varchar)
								else
									''
								end
							end
						)+',,', ', ,,', ''), ',,', '') as Value
				) Fas
				outer apply (
                    select top 1
                        i_dpfc.DrugPrepFasCode_Code
                    from
                        rls.v_DrugPrepFasCode i_dpfc with (nolock)
                    where
                         i_dpfc.DrugPrepFas_id = d.DrugPrepFas_id and
                         isnull(i_dpfc.Org_id, 0) = isnull(:Org_id, 0)
                    order by
                        i_dpfc.DrugPrepFasCode_id
                ) dpfc
                outer apply (
                    select top 1
                        i_cmp.NAME
                    from
                        rls.v_PREP_PHARMAGROUP i_pp with(nolock)
                        left join  rls.v_CLS_MZ_PHGROUP i_cmp with (nolock) on i_cmp.CLS_MZ_PHGROUP_ID = i_pp.UNIQID
                    where
                        i_pp.PREPID = p.Prep_id
                    order by
                        i_cmp.CLS_MZ_PHGROUP_ID
                ) cmp
                outer apply (
                    select top 1
                        i_catc.NAME
                    from
                        rls.PREP_ATC i_patc with (nolock)
                        left join rls.CLSATC i_catc with (nolock) on i_catc.CLSATC_ID = i_patc.UNIQID
                    where
                         i_patc.PREPID = p.Prep_id
                ) atc
			where
				(
				    (:Drug_id is not null and d.Drug_id = :Drug_id) or
				     d.DrugPrepFas_id = :DrugPrepFas_id
				);
		";
		$r = $this->db->query($q, array(
			'DrugPrepFas_id' => $data['DrugPrepFas_id'],
			'Drug_id' => $data['Drug_id'],
			'Org_id' => $data['Org_id']
		));
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
     *  Загрузка данных для комбобокса
     */
    function loadOrgDidCombo($data) {
        $where = array();

        if ($data['Org_id'] > 0) {
            $where[] = 'Org_id = :Org_id';
        } else {
            $data['Org_id'] = $data['UserOrg_id'];
            $where[] = '(Org_id = :Org_id or Org_id = dbo.GetMinzdravDloOrgId())';
        }

        if (count($where) > 0) {
            $query = "
				select
					Org_id,
					Org_Name
				from
					v_Org with (nolock)
				where
			".join($where, ' and ');

            $result = $this->db->query($query, $data);
            if ( is_object($result) ) {
                return $result->result('array');
            }
        }

        return false;
    }

    /**
    * Получение списка прайсов
    */
    function loadCommercialOfferList()
    {
    	$query = "
    		select 
    			CommercialOffer_id,
    			CommercialOffer_Name
    		from v_CommercialOffer
    		where ISNULL(CommercialOffer_endDT, dbo.tzGetDate()) >= dbo.tzGetDate()
    		and CommercialOffer_Name is not null
    	";
    	$result = $this->db->query($query,array());
    	if(is_object($result))
    		return $result->result('array');
    	return false;
    }

    /**
    * Получение медикамента из прайса по коду СКП
    */
    function getCommercialOfferDrugDetail($data) {
    	$params = array(
    		'CommercialOfferDrug_PriceDetail' => $data['CommercialOfferDrug_PriceDetail']
    	);
    	$query = "
    		select
    			COD.CommercialOfferDrug_id,
    			COD.Drug_id,
    			ISNULL(COD.CommercialOfferDrug_MnnName,'') as CommercialOfferDrug_MnnName,
    			ISNULL(COD.CommercialOfferDrug_PharmName,'') as CommercialOfferDrug_PharmName,
    			ISNULL(COD.CommercialOfferDrug_Form,'') as CommercialOfferDrug_Form,
    			COD.CommercialOfferDrug_Package,
    			(ISNULL(COD.CommercialOfferDrug_ProdName,'') + ' ' + ISNULL(COD.CommercialOfferDrug_ProdCountry,'')) as CommercialOfferDrug_Prod,
    			ISNULL(COD.CommercialOfferDrug_RegCertName,'') as CommercialOfferDrug_RegCertName,
    			ISNULL(COD.CommercialOfferDrug_UnitName, '') as CommercialOfferDrug_UnitName,
    			D.DrugComplexMnn_id,
    			DCMN.ACTMATTERS_ID
    		from v_CommercialOfferDrug COD (nolock)
    		left join rls.v_Drug D (nolock) on D.Drug_id = COD.Drug_id
    		left join rls.v_DrugComplexMnn DCM (nolock) on DCM.DrugComplexMnn_id = D.DrugComplexMnn_id
			left join rls.v_DrugComplexMnnName DCMN (nolock) on DCMN.DrugComplexMnnName_id = DCM.DrugComplexMnnName_id
    		where COD.CommercialOfferDrug_PriceDetail = :CommercialOfferDrug_PriceDetail
    	";
    	$result = $this->db->query($query,$params);
    	if(is_object($result)) {
    		return $result->result('array');
    	}
    	return false;
    }

    /**
     * Получение количества связанных объектов
     */
    function getLinkedObjectCount($object_name, $object_id) {
        $cnt_data = array();
        $query = "";

        switch($object_name) {
            case 'CommercialOffer':
                $query = "
                    declare
                        @CommercialOffer_id bigint = :object_id,
                        @WhsDocumentSupply_Cnt int = 0,
                        @WhsDocumentCommercialOfferDrug_Cnt int = 0,
                        @WhsDocumentSupplySpec_Cnt int = 0,
                        @WhsDocumentUcPriceHistory_Cnt int = 0,
                        @Total_Cnt int = 0;

                    set @WhsDocumentSupply_Cnt = (
                        select
                            count(wds.WhsDocumentSupply_id)
                        from
                            v_WhsDocumentSupply wds with (nolock)
                        where
                            wds.CommercialOffer_id = @CommercialOffer_id
                    );

                    set @WhsDocumentCommercialOfferDrug_Cnt = (
                        select
                            count(wdcod.WhsDocumentCommercialOfferDrug_id)
                        from
                            v_CommercialOfferDrug cod with (nolock)
                            left join v_WhsDocumentCommercialOfferDrug wdcod with (nolock) on wdcod.CommercialOfferDrug_id = cod.CommercialOfferDrug_id
                        where
                            cod.CommercialOffer_id = @CommercialOffer_id
                    );

                    set @WhsDocumentSupplySpec_Cnt = (
                        select
                            count(wdss.WhsDocumentSupplySpec_id)
                        from
                            v_CommercialOfferDrug cod with (nolock)
                            left join v_WhsDocumentSupplySpec wdss with (nolock) on wdss.CommercialOfferDrug_id = cod.CommercialOfferDrug_id
                        where
                            cod.CommercialOffer_id = @CommercialOffer_id
                    );

                    set @WhsDocumentUcPriceHistory_Cnt = (
                        select
                            count(wduph.WhsDocumentUcPriceHistory_id)
                        from
                            v_CommercialOfferDrug cod with (nolock)
                            left join v_WhsDocumentUcPriceHistory wduph with (nolock) on wduph.CommercialOfferDrug_id = cod.CommercialOfferDrug_id
                        where
                            cod.CommercialOffer_id = @CommercialOffer_id
                    );

                    set @Total_Cnt = @WhsDocumentSupply_Cnt+@WhsDocumentCommercialOfferDrug_Cnt+@WhsDocumentSupplySpec_Cnt+@WhsDocumentUcPriceHistory_Cnt;

                    select
                        WhsDocumentSupply_Cnt = @WhsDocumentSupply_Cnt,
                        WhsDocumentCommercialOfferDrug_Cnt = @WhsDocumentCommercialOfferDrug_Cnt,
                        WhsDocumentSupplySpec_Cnt = @WhsDocumentSupplySpec_Cnt,
                        WhsDocumentUcPriceHistory_Cnt = @WhsDocumentUcPriceHistory_Cnt,
                        Total_Cnt = @Total_Cnt;
                ";
                break;
            case 'CommercialOfferDrug':
                $query = "
                    declare
                        @CommercialOfferDrug_id bigint = :object_id,
                        @WhsDocumentCommercialOfferDrug_Cnt int = 0,
                        @WhsDocumentSupplySpec_Cnt int = 0,
                        @WhsDocumentUcPriceHistory_Cnt int = 0,
                        @Total_Cnt int = 0;

                    set @WhsDocumentCommercialOfferDrug_Cnt = (
                        select
                            count(wdcod.WhsDocumentCommercialOfferDrug_id)
                        from
                            v_WhsDocumentCommercialOfferDrug wdcod with (nolock)
                        where
                            wdcod.CommercialOfferDrug_id = @CommercialOfferDrug_id
                    );

                    set @WhsDocumentSupplySpec_Cnt = (
                        select
                            count(wdss.WhsDocumentSupplySpec_id)
                        from
                            v_WhsDocumentSupplySpec wdss with (nolock)
                        where
                            wdss.CommercialOfferDrug_id = @CommercialOfferDrug_id
                    );

                    set @WhsDocumentUcPriceHistory_Cnt = (
                        select
                            count(wduph.WhsDocumentUcPriceHistory_id)
                        from
                            v_WhsDocumentUcPriceHistory wduph with (nolock)
                        where
                            wduph.CommercialOfferDrug_id = @CommercialOfferDrug_id
                    );

                    set @Total_Cnt = @WhsDocumentCommercialOfferDrug_Cnt+@WhsDocumentSupplySpec_Cnt+@WhsDocumentUcPriceHistory_Cnt;

                    select
                        WhsDocumentCommercialOfferDrug_Cnt = @WhsDocumentCommercialOfferDrug_Cnt,
                        WhsDocumentSupplySpec_Cnt = @WhsDocumentSupplySpec_Cnt,
                        WhsDocumentUcPriceHistory_Cnt = @WhsDocumentUcPriceHistory_Cnt,
                        Total_Cnt = @Total_Cnt;
                ";
                break;
        }

        if (!empty($query)) {
            $cnt_data = $this->getFirstRowFromQuery($query, array(
                'object_id' => $object_id
            ));
        }

        return $cnt_data;
    }

    /**
     * Проверка уникальности действующего прайса для СК Фармации
     */
    function checkSkFarmCommercialOfferUnique($data) {
        $result = array();

        //ищем ОГРН поставщика
        $query = "
            select
                Org_OGRN
            from
                v_Org with (nolock)
            where
                Org_id = :Org_id;
        ";
        $org_ogrn = $this->getFirstResultFromQuery($query, array(
            'Org_id' => $data['Org_id']
        ));

        if ($org_ogrn == '090340007747') { //ОГРН организации "СК Фармация"
            $query = "
                select
                    count(co.CommercialOffer_id) as cnt
                from
                    v_CommercialOffer co with (nolock)
                where
                    co.Org_id = :Org_id and
                    co.CommercialOffer_Status = 1 and -- Действующий
                    (
                        :CommercialOffer_id is null or
                        co.CommercialOffer_id <> :CommercialOffer_id
                    )
            ";
            $cnt = $this->getFirstResultFromQuery($query, array(
                'Org_id' => $data['Org_id'],
                'CommercialOffer_id' => $data['CommercialOffer_id']
            ));

            if ($cnt > 0) {
                $result['Error_Msg'] = "Создание второго действующего прайса от СК Фармация запрещено";
            }
        }


        return $result;
    }
}