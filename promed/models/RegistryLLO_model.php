<?php defined('BASEPATH') or die ('No direct script access allowed');

class RegistryLLO_model extends swModel {
	var $schema = "dbo"; //региональная схема

	/**
	 *  Конструктор
	 */
	function __construct() {
		parent::__construct();

		//установка региональной схемы
		$config = get_config();
		$this->schema = $config['regions'][getRegionNumber()]['schema'];
	}

	/**
	 * Загрузка реестра рецептов
	 */
	function load($data) {
		$q = "
			select
				*
			from
				{$this->schema}.v_RegistryLLO rllo with (nolock)
			where
				rllo.RegistryLLO_id = :RegistryLLO_id
		";

		$result = $this->db->query($q, array('RegistryLLO_id' => $data['RegistryLLO_id']));

		if (is_object($result)) {
			$result = $result->result('array');
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * Удаление реестра
	 */
	function delete($data) {
		$error = array();

		$query = "
			select
				rs.RegistryStatus_Code,
				rus.ReceptUploadStatus_Code
			from
				{$this->schema}.v_RegistryLLO rllo with (nolock)
				left join v_RegistryStatus rs with(nolock) on rs.RegistryStatus_id = rllo.RegistryStatus_id
				left join v_ReceptUploadLog rul with(nolock) on rul.ReceptUploadLog_id = rllo.ReceptUploadLog_id
				left join v_ReceptUploadStatus rus with(nolock) on rus.ReceptUploadStatus_id = rul.ReceptUploadStatus_id
			where
				rllo.RegistryLLO_id = :RegistryLLO_id;
		";
		$registry = $this->getFirstRowFromQuery($query, array(
			'RegistryLLO_id' => $data['id']
		));

		$allowDelete = (
			empty($registry['RegistryStatus_Code']) ||
			$registry['RegistryStatus_Code'] == 1 ||
			($registry['RegistryStatus_Code'] == 3 && in_array($registry['ReceptUploadStatus_Code'], array(3,5)))
		);

		if (!$allowDelete) {
			$error[] = 'Удаление реестра недопустимо.';
		}

		$this->beginTransaction();

		if (count($error) == 0) {
			//восстановление статуса рецептов
			$query = "
				update
					dbo.ReceptOtov
				set
					ReceptStatusType_id = :ReceptStatusType_id,
					ReceptOtov_updDT = dbo.tzGetDate(),
					pmUser_updID = :pmUser_id
				where
					ReceptOtov_id in (
						select
							ReceptOtov_id
						from
							{$this->schema}.RegistryDataRecept
						where
							RegistryLLO_id = :RegistryLLO_id
					) and
					ReceptStatusType_id = :OldReceptStatusType_id;
			";
			$result = $this->db->query($query, array(
				'RegistryLLO_id' => $data['id'],
				'ReceptStatusType_id' => $this->getObjectIdByCode('ReceptStatusType', 5), //5 - исключен из реестра
				'OldReceptStatusType_id' => $this->getObjectIdByCode('ReceptStatusType', 4), //4 - включен в реестр
				'pmUser_id' => $data['pmUser_id']
			));

			//очистка списка рецептов
			$query = "
				delete from
					{$this->schema}.RegistryDataRecept
				where
					RegistryLLO_id = :RegistryLLO_id
			";
			$result = $this->db->query($query, array(
				'RegistryLLO_id' => $data['id']
			));
		}


		if (count($error) == 0) {
			//удалениее реестра
			$result = $this->deleteObject("{$this->schema}.RegistryLLO", array(
				'RegistryLLO_id' => $data['id']
			));
			if (!empty($response['Error_Msg'])) {
				$error[] = $response['Error_Msg'];
			}
		}

		if (count($error) > 0) {
			$this->rollbackTransaction();
			return array('Error_Msg' => $error[0]);
		} else {
			$this->commitTransaction();
			return array(
				'success' => true
			);
		}
	}

    /**
     * Удаление рецепта из реестра
     */
    function deleteRegistryDataRecept($data) {
		$error = array();

		$query = "
			select
				rs.RegistryStatus_Code
			from
				{$this->schema}.v_RegistryDataRecept rdr with (nolock)
				{$this->schema}.v_RegistryLLO rllo with (nolock) rllo.RegistryLLO_id = rdr.RegistryLLO_id
				left join v_RegistryStatus rs with(nolock) on rs.RegistryStatus_id = rllo.RegistryStatus_id
			where
				rdr.RegistryDataRecept_id = :RegistryDataRecept_id;
		";
		$status_code = $this->getFirstResultFromQuery($query, array(
			'RegistryDataRecept_id' => $data['id']
		));

		if (!empty($status_code) && $status_code != 1) { //1 - Сформированный.
			$error[] = 'Удаление рецепта недопустимо.';
		}

		$this->beginTransaction();

		if (count($error) == 0) {
			//восстановление статуса рецепта
			$query = "
				update
					dbo.ReceptOtov
				set
					ReceptStatusType_id = :ReceptStatusType_id,
					ReceptOtov_updDT = dbo.tzGetDate(),
					pmUser_updID = :pmUser_id
				where
					ReceptOtov_id in (
						select
							ReceptOtov_id
						from
							{$this->schema}.RegistryDataRecept
						where
							RegistryDataRecept_id = :RegistryDataRecept_id
					) and
					ReceptStatusType_id = :OldReceptStatusType_id;
			";
			$result = $this->db->query($query, array(
				'RegistryDataRecept_id' => $data['id'],
				'ReceptStatusType_id' => $this->getObjectIdByCode('ReceptStatusType', 5), //5 - исключен из реестра
				'OldReceptStatusType_id' => $this->getObjectIdByCode('ReceptStatusType', 4), //4 - включен в реестр
				'pmUser_id' => $data['pmUser_id']
			));

			//удаление рецепта
			$query = "
				delete from
					{$this->schema}.RegistryDataRecept
				where
					RegistryDataRecept_id = :RegistryDataRecept_id
			";
			$result = $this->db->query($query, array(
				'RegistryDataRecept_id' => $data['id']
			));
		}

		if (count($error) > 0) {
			$this->rollbackTransaction();
			return array('Error_Msg' => $error[0]);
		} else {
			$this->commitTransaction();
			return array(
				'success' => true
			);
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadList($data) {
		$params = array();
		$filters = array();
		$where = null;

        $params['FinDocumentType1_id'] = $this->getObjectIdByCode('FinDocumentType', 1); //1 - счет
        $params['FinDocumentType2_id'] = $this->getObjectIdByCode('FinDocumentType', 2); //2 - платежное поручение

		if (!empty($data['Org_id'])) {
			$filters[] = "rllo.Org_id = :Org_id";
			$params['Org_id'] = $data['Org_id'];
		}
		if (!empty($data['RegistryStatus_id'])) {
			$filters[] = "rllo.RegistryStatus_id = :RegistryStatus_id";
			$params['RegistryStatus_id'] = $data['RegistryStatus_id'];
		}
		if (!empty($data['RegistryStatus_Code']) || $data['RegistryStatus_Code'] == 0) {
			$filters[] = $data['RegistryStatus_Code'] == 0 ? "rllo.RegistryStatus_id is null" : "rs.RegistryStatus_Code = :RegistryStatus_Code";
			$params['RegistryStatus_Code'] = $data['RegistryStatus_Code'];
		}
		if (!empty($data['KatNasel_id']) && $data['KatNasel_id'] > 0) {
			$filters[] = "rllo.KatNasel_id = :KatNasel_id";
			$params['KatNasel_id'] = $data['KatNasel_id'];
		}
		if (!empty($data['DrugFinance_id']) && $data['DrugFinance_id'] > 0) {
			$filters[] = "rllo.DrugFinance_id = :DrugFinance_id";
			$params['DrugFinance_id'] = $data['DrugFinance_id'];
		}
		if (!empty($data['WhsDocumentCostItemType_id']) && $data['WhsDocumentCostItemType_id'] > 0) {
			$filters[] = "rllo.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
			$params['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
		}
		if (!empty($data['WhsDocumentUc_Num'])) {
			$filters[] = "wds.WhsDocumentUc_Num like :WhsDocumentUc_Num";
			$params['WhsDocumentUc_Num'] = '%'.$data['WhsDocumentUc_Num'].'%';
		}
		if (!empty($data['Year']) && $data['Year'] > 0) {
			$filters[] = "datepart(year, rllo.RegistryLLO_accDate) = :Year";
			$params['Year'] = $data['Year'];
		}
		if (is_array($data['ReceptUploadLog_setDT_range']) && !empty($data['ReceptUploadLog_setDT_range'][0]) && !empty($data['ReceptUploadLog_setDT_range'][1])) {
			$filters[] = "cast(rul.ReceptUploadLog_setDT as date) between :ReceptUploadLog_setDT_range_0 and :ReceptUploadLog_setDT_range_1";
            $params['ReceptUploadLog_setDT_range_0'] = $data['ReceptUploadLog_setDT_range'][0];
            $params['ReceptUploadLog_setDT_range_1'] = $data['ReceptUploadLog_setDT_range'][1];
		}
        if (!empty($data['ReceptUploadStatus_id']) && $data['ReceptUploadStatus_id'] > 0) {
            $filters[] = "rul.ReceptUploadStatus_id = :ReceptUploadStatus_id";
            $params['ReceptUploadStatus_id'] = $data['ReceptUploadStatus_id'];
        }
        if (!empty($data['MinSum'])) {
            $filters[] = "(isnull(rllo.RegistryLLO_Sum, 0) + isnull(rllo.Registry_Sum2, 0)) >= :MinSum";
            $params['MinSum'] = $data['MinSum'];
        }
        if (!empty($data['MaxSum'])) {
            $filters[] = "(isnull(rllo.RegistryLLO_Sum, 0) + isnull(rllo.Registry_Sum2, 0)) <= :MaxSum";
            $params['MaxSum'] = $data['MaxSum'];
        }
        if (!empty($data['RegistryLLO_begDate']) && !empty($data['RegistryLLO_endDate'])) {
            $filters[] = "(rllo.RegistryLLO_begDate is null or rllo.RegistryLLO_begDate <= :RegistryLLO_endDate) and (rllo.RegistryLLO_endDate is null or rllo.RegistryLLO_endDate >= :RegistryLLO_begDate)";
            $params['RegistryLLO_begDate'] = $data['RegistryLLO_begDate'];
            $params['RegistryLLO_endDate'] = $data['RegistryLLO_endDate'];
        }

		if (count($filters) > 0) {
			$where = "
				where
					-- where
					".join(" and ", $filters)."
					-- end where
			";
		}

		$query = "
			select
				-- select
				rllo.RegistryLLO_id,
				rllo.RegistryLLO_Num,
				rllo.Org_id,
				o.Org_Name,
				convert(varchar(10), rllo.RegistryLLO_accDate, 104) as RegistryLLO_accDate,
				isnull(convert(varchar(10), rllo.RegistryLLO_begDate, 104), '')+' - '+isnull(convert(varchar(10), rllo.RegistryLLO_endDate, 104),'') as RegistryLLO_Period,
				kn.KatNasel_Name,
				df.DrugFinance_Name,
				wdcit.WhsDocumentCostItemType_Name,
				wds.WhsDocumentUc_Num,
				convert(varchar(10), rllo.RegistryLLO_updDT, 104) as RegistryLLO_updDT,
				rs.RegistryStatus_Code,
				rs.RegistryStatus_Name,
				rllo.RegistryLLO_Sum,
				rllo.Registry_Sum2,
				rdr.cnt as Registry_Count,
				rllo.RegistryLLO_ErrorCount as Registry_ErrorCount,
				(
				    isnull(fin_doc.FinDocument_Number, '') +
				    isnull(' ' + convert(varchar(10), fin_doc.FinDocument_Date, 104), '')
				) as FinDocument_Data,
                fin_doc.FinDocument_Sum as FinDocument_Sum,
                fds_data.FinDocumentSpec_HtmlData,
                fds_sum.FinDocumentSpec_HtmlSum,
				rllo.DrugFinance_id,
				rllo.WhsDocumentCostItemType_id,
				sup_c.Contragent_id as SupplierContragent_id,
				fin_doc.FinDocument_id,
				(
				    cast(isnull(rul.ReceptUploadLog_id, '') as varchar) +
				    isnull(' ' + convert(varchar(10), rul.ReceptUploadLog_setDT, 104), '')
				) as ReceptUploadLog_Data,
                rus.ReceptUploadStatus_Code,
                rus.ReceptUploadStatus_Name,
                rul.ReceptUploadLog_Act
				-- end select
			from
				-- from
				{$this->schema}.v_RegistryLLO rllo with (nolock)
				left join v_KatNasel kn with(nolock) on kn.KatNasel_id = rllo.KatNasel_id
				left join v_DrugFinance df with(nolock) on df.DrugFinance_id = rllo.DrugFinance_id
				left join v_WhsDocumentCostItemType wdcit with(nolock) on wdcit.WhsDocumentCostItemType_id = rllo.WhsDocumentCostItemType_id
				left join v_WhsDocumentSupply wds with(nolock) on wds.WhsDocumentSupply_id = rllo.WhsDocumentSupply_id
				left join v_RegistryStatus rs with(nolock) on rs.RegistryStatus_id = rllo.RegistryStatus_id
				left join v_Org o with(nolock) on o.Org_id = rllo.Org_id
				left join v_ReceptUploadLog rul with(nolock) on rul.ReceptUploadLog_id = rllo.ReceptUploadLog_id
				left join v_ReceptUploadStatus rus with (nolock) on rus.ReceptUploadStatus_id = rul.ReceptUploadStatus_id
				outer apply (
					select
						count(i_rdr.RegistryDataRecept_id) as cnt
					from
						{$this->schema}.v_RegistryDataRecept i_rdr with (nolock)
					where
						i_rdr.RegistryLLO_id = rllo.RegistryLLO_id
				) rdr
				outer apply (
                    select top 1
                        Contragent_id
                    from
                        v_Contragent i_c with (nolock)
                    where
                        i_c.Org_id = wds.Org_sid
                    order by
                        i_c.Contragent_id
                ) sup_c
                outer apply (
                   select top 1
                        i_rlfd.FinDocument_id,
                        i_fd.FinDocument_Number,
                        i_fd.FinDocument_Date,
                        i_fd.FinDocument_Sum
                   from
                        {$this->schema}.v_RegistryLLOFinDocument i_rlfd with (nolock)
                        left join {$this->schema}.v_FinDocument i_fd with (nolock) on i_fd.FinDocument_id = i_rlfd.FinDocument_id
                   where
                        i_rlfd.RegistryLLO_id = rllo.RegistryLLO_id and
                        i_fd.FinDocumentType_id = :FinDocumentType1_id
                   order by
                        i_rlfd.FinDocument_id
                ) fin_doc
                outer apply (
                    select
                        replace(replace((
                            select
                                isnull(i_fd.FinDocument_Number, '') +
                                isnull(' ' + convert(varchar(10), i_fd.FinDocument_Date, 104), '') +
                                '<br/>'
                            from
                                {$this->schema}.v_RegistryLLOFinDocument i_rlfd with (nolock)
                                left join {$this->schema}.v_FinDocument i_fd with (nolock) on i_fd.FinDocument_id = i_rlfd.FinDocument_id
                            where
                                i_rlfd.RegistryLLO_id = rllo.RegistryLLO_id and
                                i_fd.FinDocumentType_id = :FinDocumentType2_id
                            for xml path('')
                        , TYPE).value('.', 'nvarchar(max)')+',,', '<br/>,,', ''), ',,', '') as FinDocumentSpec_HtmlData
                ) fds_data
                outer apply (
                    select
                        replace(replace((
                            select
                                replace(convert(varchar, cast(isnull(i_fd.FinDocument_Sum, 0) as money), 101), ',', ' ')+
                                '<br/>'
                            from
                                {$this->schema}.v_RegistryLLOFinDocument i_rlfd with (nolock)
                                left join {$this->schema}.v_FinDocument i_fd with (nolock) on i_fd.FinDocument_id = i_rlfd.FinDocument_id
                            where
                                i_rlfd.RegistryLLO_id = rllo.RegistryLLO_id and
                                i_fd.FinDocumentType_id = :FinDocumentType2_id
                            for xml path('')
                        , TYPE).value('.', 'nvarchar(max)')+',,', '<br/>,,', ''), ',,', '') as FinDocumentSpec_HtmlSum
                ) fds_sum
				-- end from
			{$where}
			order by
				-- order by
				rllo.RegistryLLO_Num, rllo.RegistryLLO_id
				-- end order by
		";

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);

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
	 * Загрузка списка рецептов
     */
	function loadRegistryDataReceptList($data) {
		$params = array();
		$filters = array();
		$where = null;

        $filters[] = "rdr.RegistryLLO_id = :RegistryLLO_id";
        $params['RegistryLLO_id'] = $data['RegistryLLO_id'];

        if (!empty($data['SupplierContragent_id'])) {
            $filters[] = "sup_c.Contragent_id = :SupplierContragent_id";
            $params['SupplierContragent_id'] = $data['SupplierContragent_id'];
        }

        if (!empty($data['WhsDocumentUc_Num'])) {
            $filters[] = "wds.WhsDocumentUc_Num like :WhsDocumentUc_Num";
            $params['WhsDocumentUc_Num'] = '%'.$data['WhsDocumentUc_Num'].'%';
        }

        if (!empty($data['DrugFinance_id'])) {
            $filters[] = "ro.DrugFinance_id = :DrugFinance_id";
            $params['DrugFinance_id'] = $data['DrugFinance_id'];
        }

        if (!empty($data['WhsDocumentCostItemType_id'])) {
            $filters[] = "ro.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
            $params['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
        }

        if (!empty($data['ReceptStatusFLKMEK_id'])) {
            $filters[] = "rdr.ReceptStatusFLKMEK_id = :ReceptStatusFLKMEK_id";
            $params['ReceptStatusFLKMEK_id'] = $data['ReceptStatusFLKMEK_id'];
        }

        if (!empty($data['RegistryReceptErrorType_id'])) {
            $filters[] = "exists(
                select top 1
                    i_rlr.RegistryLLOError_id
                from
                    {$this->schema}.v_RegistryLLOError i_rlr with (nolock)
                where
                    i_rlr.RegistryLLO_id = rdr.RegistryLLO_id and
                    i_rlr.ReceptOtov_id = rdr.ReceptOtov_id and
                    i_rlr.RegistryReceptErrorType_id = :RegistryReceptErrorType_id
            )";
            $params['RegistryReceptErrorType_id'] = $data['RegistryReceptErrorType_id'];
        }

        if (!empty($data['Person_SurName'])) {
            $filters[] = "ps.Person_SurName like :Person_SurName";
            $params['Person_SurName'] = '%'.$data['Person_SurName'].'%';
        }

        if (!empty($data['Person_FirName'])) {
            $filters[] = "ps.Person_FirName like :Person_FirName";
            $params['Person_FirName'] = '%'.$data['Person_FirName'].'%';
        }

        if (!empty($data['Person_SecName'])) {
            $filters[] = "ps.Person_SecName like :Person_SecName";
            $params['Person_SecName'] = '%'.$data['Person_SecName'].'%';
        }

        if (!empty($data['Person_Snils'])) {
            $filters[] = "ro.Person_Snils = :Person_Snils";
            $params['Person_Snils'] = $data['Person_Snils'];
        }

        if (!empty($data['EvnRecept_Ser'])) {
            $filters[] = "ro.EvnRecept_Ser = :EvnRecept_Ser";
            $params['EvnRecept_Ser'] = $data['EvnRecept_Ser'];
        }

        if (!empty($data['EvnRecept_Num'])) {
            $filters[] = "ro.EvnRecept_Num = :EvnRecept_Num";
            $params['EvnRecept_Num'] = $data['EvnRecept_Num'];
        }

        if (!empty($data['PrivilegeType_id'])) {
            $filters[] = "ro.PrivilegeType_id = :PrivilegeType_id";
            $params['PrivilegeType_id'] = $data['PrivilegeType_id'];
        }

        if (!empty($data['MedPersonal_Name'])) {
            $filters[] = "mp.Person_Fio like :MedPersonal_Name";
            $params['MedPersonal_Name'] = '%'.str_replace(' ', '%', $data['MedPersonal_Name']).'%';
        }

        if (!empty($data['Lpu_id'])) {
            $filters[] = "ro.Lpu_id = :Lpu_id";
            $params['Lpu_id'] = $data['Lpu_id'];
        }

        if (!empty($data['EvnRecept_otpDate_Range'][0])) {
            $filters[] = "ro.EvnRecept_otpDate >= :EvnRecept_otpDate_0";
            $params['EvnRecept_otpDate_0'] = $data['EvnRecept_otpDate_Range'][0];
        }

        if (!empty($data['EvnRecept_otpDate_Range'][1])) {
            $filters[] = "cast(ro.EvnRecept_otpDate as date) <= :EvnRecept_otpDate_1";
            $params['EvnRecept_otpDate_1'] = $data['EvnRecept_otpDate_Range'][1];
        }

        if (!empty($data['FarmacyContragent_id'])) {
            $filters[] = "doc.Contragent_sid = :FarmacyContragent_id";
            $params['FarmacyContragent_id'] = $data['FarmacyContragent_id'];
        }

        if (!empty($data['Recept_isAfterDelay'])) { //После отсрочки
            if ($data['Recept_isAfterDelay'] == 1) { //Нет
                 $filters[] = "cast(ro.EvnRecept_obrDate as date) = cast(ro.EvnRecept_otpDate as date)";
            }
            if ($data['Recept_isAfterDelay'] == 2) { //Да
                 $filters[] = "cast(ro.EvnRecept_obrDate as date) <> cast(ro.EvnRecept_otpDate as date)";
            }
        }

        if (!empty($data['DrugComplexMnn_Name'])) {
            $filters[] = "dcm.DrugComplexMnn_RusName like :DrugComplexMnn_Name";
            $params['DrugComplexMnn_Name'] = '%'.$data['DrugComplexMnn_Name'].'%';
        }

        if (!empty($data['Drug_Name'])) {
            $filters[] = "d.Drug_Name like :Drug_Name";
            $params['Drug_Name'] = '%'.$data['Drug_Name'].'%';
        }

		if (count($filters) > 0) {
			$where = "
				where
					-- where
					".join(" and ", $filters)."
					-- end where
			";
		}

		$query = "
			select
				-- select
				rdr.RegistryDataRecept_id,
				ro.ReceptOtov_id,
				ro.EvnRecept_id,
				isnull(is_received.YesNo_Code, 0) as IsReceived_Code,
				ro.EvnRecept_Ser,
				ro.EvnRecept_Num,
				rsfm.ReceptStatusFLKMEK_Name,
                rdr.RegistryDataRecept_Sum,
                rdr.RegistryDataRecept_Sum2,
                farm_org.Org_Name as FarmacyOrg_Name,
                farm_org.Org_Code as FarmacyOrg_Code,
                convert(varchar(10), ro.EvnRecept_obrDate, 104) as EvnRecept_obrDate,
                convert(varchar(10), ro.EvnRecept_otpDate, 104) as EvnRecept_otpDate,
                (case
                    when isnull(is_mnn.YesNo_Code, 0) > 0 then er_dcm_code.Code
                    else er_d_code.Code
                end) as LS_Code,
                (case
                    when isnull(is_mnn.YesNo_Code, 0) > 0 then er_dcm.DrugComplexMnn_RusName
                    else er_d.Drug_Name
                end) as LS_Name,
                ro.EvnRecept_Kolvo,
                doc_d.Drug_Name,
			    doc_ps.PrepSeries_Ser,
			    doc_cnt.DocumentUcStr_Count,
			    doc_pr.DocumentUcStr_PriceR,
			    doc_sum.DocumentUcStr_SumR,
                wds.WhsDocumentUc_Num,
                pt.PrivilegeType_Code,
                rd.ReceptDiscount_Name as PaymentPercent,
                diag.Diag_Code,
                mp.Person_Fio as MedPersonal_Fio,
                l.Lpu_Name,
	            isnull(is_vk.YesNo_Name, 'Нет') as EvnRecept_isVK,
                ps.Person_Snils,
                (
                    isnull(ps.Person_SurName, '') +
                    isnull(' ' + ps.Person_FirName, '') +
                    isnull(' ' + ps.Person_SecName, '')
                ) as Person_Fio,
                convert(varchar(10), ps.Person_BirthDay, 104) as Person_BirthDay
				-- end select
			from
				-- from
				{$this->schema}.v_RegistryDataRecept rdr with (nolock)
				left join {$this->schema}.ReceptStatusFLKMEK rsfm with (nolock) on rsfm.ReceptStatusFLKMEK_id = rdr.ReceptStatusFLKMEK_id
				left join v_YesNo is_received with(nolock) on is_received.YesNo_id = rdr.RegistryDataRecept_IsReceived
				left join ReceptOtov ro with (nolock) on ro.ReceptOtov_id = rdr.ReceptOtov_id
				left join v_EvnRecept er with (nolock) on er.EvnRecept_id = ro.EvnRecept_id
				left join v_ReceptDiscount rd with (nolock) on rd.ReceptDiscount_id = er.ReceptDiscount_id
	            left join v_PersonState ps with (nolock) on ps.Person_id = ro.Person_id
	            left join v_MedPersonal mp with (nolock) on mp.MedPersonal_id = ro.MedPersonalRec_id
	            left join rls.v_Drug d with (nolock) on d.Drug_id = ro.Drug_cid
	            left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
                left join v_PrivilegeType pt with (nolock) on pt.PrivilegeType_id = ro.PrivilegeType_id
                left join v_Diag diag with (nolock) on diag.Diag_id = ro.Diag_id
                left join v_Lpu l with (nolock) on l.Lpu_id = ro.Lpu_id
                left join v_YesNo is_vk with (nolock) on is_vk.YesNo_id = er.EvnRecept_IsKEK
	            outer apply (
                    select top 1
                        i_du.DocumentUc_id,
                        isnull(i_p_ds.WhsDocumentSupply_id, i_ds.WhsDocumentSupply_id) as WhsDocumentSupply_id,
                        i_du.Contragent_sid,
                        i_c_sid.Org_id as Org_sid,
                        i_dus.Drug_id,
			            i_dus.DocumentUcStr_Count,
			            i_dus.DocumentUcStr_PriceR,
			            i_dus.DocumentUcStr_SumR
                    from
                        v_DocumentUcStr i_dus with (nolock)
                        left join v_DocumentUc i_du with (nolock) on i_du.DocumentUc_id = i_dus.DocumentUc_id
                        left join v_Contragent i_c_sid with (nolock) on i_c_sid.Contragent_id = i_du.Contragent_sid
                        -- ищем приходную партию чтобы извлечь оттуда идентификатор контракта
                        left join v_DrugShipmentLink i_dsl with (nolock) on i_dsl.DocumentUcStr_id = i_dus.DocumentUcStr_oid
                        left join v_DrugShipment i_ds with (nolock) on i_ds.DrugShipment_id = i_dsl.DrugShipment_id
                        left join v_DrugShipment i_p_ds with (nolock) on i_p_ds.DrugShipment_id = i_ds.DrugShipment_pid
                    where
                        i_dus.ReceptOtov_id = rdr.ReceptOtov_id
                    order by
                        i_p_ds.WhsDocumentSupply_id desc, i_ds.WhsDocumentSupply_id desc, i_dus.DocumentUcStr_id
                ) doc
                outer apply (
                    select
                        replace(replace((
                            select
                                isnull(i_d.Drug_Name, '&nbsp;')+'<br/>'
                            from
                                v_DocumentUcStr i_dus with (nolock)
                                left join rls.v_Drug i_d with (nolock) on i_d.Drug_id = i_dus.Drug_id
                            where
                                i_dus.ReceptOtov_id = rdr.ReceptOtov_id
                            for xml path('')
                        , TYPE).value('.', 'nvarchar(max)')+',,', '<br/>,,', ''), ',,', '') as Drug_Name
                ) doc_d
                outer apply (
                    select
                        replace(replace((
                            select
                                isnull(i_ps.PrepSeries_Ser, '&nbsp;')+'<br/>'
                            from
                                v_DocumentUcStr i_dus with (nolock)
                                left join rls.v_PrepSeries i_ps with (nolock) on i_ps.PrepSeries_id = i_dus.PrepSeries_id
                            where
                                i_dus.ReceptOtov_id = rdr.ReceptOtov_id
                            for xml path('')
                        , TYPE).value('.', 'nvarchar(max)')+',,', '<br/>,,', ''), ',,', '') as PrepSeries_Ser
                ) doc_ps
                outer apply (
                    select
                        replace(replace((
                            select
                                isnull(cast(i_dus.DocumentUcStr_Count as varchar), '&nbsp;')+'<br/>'
                            from
                                v_DocumentUcStr i_dus with (nolock)
                            where
                                i_dus.ReceptOtov_id = rdr.ReceptOtov_id
                            for xml path('')
                        , TYPE).value('.', 'nvarchar(max)')+',,', '<br/>,,', ''), ',,', '') as DocumentUcStr_Count
                ) doc_cnt
                outer apply (
                    select
                        replace(replace((
                            select
                                isnull(cast(i_dus.DocumentUcStr_PriceR as varchar), '&nbsp;')+'<br/>'
                            from
                                v_DocumentUcStr i_dus with (nolock)
                            where
                                i_dus.ReceptOtov_id = rdr.ReceptOtov_id
                            for xml path('')
                        , TYPE).value('.', 'nvarchar(max)')+',,', '<br/>,,', ''), ',,', '') as DocumentUcStr_PriceR
                ) doc_pr
                outer apply (
                    select
                        replace(replace((
                            select
                                isnull(cast(i_dus.DocumentUcStr_SumR as varchar), '&nbsp;')+'<br/>'
                            from
                                v_DocumentUcStr i_dus with (nolock)
                            where
                                i_dus.ReceptOtov_id = rdr.ReceptOtov_id
                            for xml path('')
                        , TYPE).value('.', 'nvarchar(max)')+',,', '<br/>,,', ''), ',,', '') as DocumentUcStr_SumR
                ) doc_sum
                left join v_Org farm_org with (nolock) on farm_org.Org_id = doc.Org_sid
                left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentSupply_id = doc.WhsDocumentSupply_id
                outer apply (
                    select top 1
                        Contragent_id
                    from
                        v_Contragent i_c with (nolock)
                    where
                        i_c.Org_id = wds.Org_sid
                    order by
                        i_c.Contragent_id
                ) sup_c
                left join rls.v_Drug er_d with (nolock) on er_d.Drug_id = er.Drug_rlsid
	            left join rls.v_DrugComplexMnn er_dcm with (nolock) on er_dcm.DrugComplexMnn_id = er.DrugComplexMnn_id
	            left join v_YesNo is_mnn with(nolock) on is_mnn.YesNo_id = er.EvnRecept_IsMnn
                outer apply (
                    select top 1
                        i_dcm_code.DrugComplexMnnCode_Code as Code
                    from
                        rls.v_DrugComplexMnnCode i_dcm_code with (nolock)
                    where
                        i_dcm_code.DrugComplexMnn_id = er.DrugComplexMnn_id
                    order by
                        i_dcm_code.DrugComplexMnnCode_id
                ) as er_dcm_code
                outer apply (
                    select top 1
                        i_d_code.DrugNomen_Code as Code
                    from
                        rls.v_DrugNomen i_d_code with (nolock)
                    where
                        i_d_code.Drug_id = er.Drug_rlsid
                    order by
                        i_d_code.DrugNomen_id
                ) as er_d_code
				-- end from
			{$where}
			order by
				-- order by
				rdr.RegistryDataRecept_id
				-- end order by
		";

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);

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
	 * Загрузка списка ошибок рецептов
     */
	function loadRegistryLLOErrorList($data) {
		$params = array();
		$filters = array();
		$where = null;

        $filters[] = "rle.RegistryLLO_id = :RegistryLLO_id";
        $params['RegistryLLO_id'] = $data['RegistryLLO_id'];

        if (!empty($data['ReceptOtov_id'])) {
            $filters[] = "rle.ReceptOtov_id = :ReceptOtov_id";
            $params['ReceptOtov_id'] = $data['ReceptOtov_id'];
        }

        if (count($filters) > 0) {
            $where = "
				where
					".join(" and ", $filters)."
			";
        }

		$query = "
			select
                rle.RegistryLLOError_id,
                convert(varchar(10), rle.RegistryLLOError_insDT, 104) as RegistryLLOError_insDT,
                rret.RegistryReceptErrorType_Type,
                rret.RegistryReceptErrorType_Name,
                rret.RegistryReceptErrorType_Descr
            from
                {$this->schema}.v_RegistryLLOError rle with (nolock)
                left join {$this->schema}.v_RegistryReceptErrorType rret with (nolock) on rret.RegistryReceptErrorType_id = rle.RegistryReceptErrorType_id
			    {$where}
			order by
				rle.RegistryLLOError_id
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	Получение списка параметров хранимой процедуры
	 */
	function getStoredProcedureParamsList($sp, $schema) {
		$query = "
			select
				ps.[name]
			from
				sys.all_parameters ps with(nolock)
				left join sys.types t with(nolock) on t.system_type_id = ps.system_type_id and t.user_type_id = ps.user_type_id
			where
				ps.[object_id] = (
					select
						top 1 [object_id]
					from
						sys.objects with(nolock)
					where
						[type_desc] = 'SQL_STORED_PROCEDURE' and
						[name] = :name and
						(
							:schema is null or
							[schema_id] = (select top 1 [schema_id] from sys.schemas with(nolock) where [name] = :schema)
						)
				) and
				ps.[name] not in ('@pmUser_id', '@Error_Code', '@Error_Message', '@isReloadCount') and
				t.[is_user_defined] = 0;
		";

		$queryParams = array(
			'name' => $sp,
			'schema' => $schema
		);

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$outputData = array();
		$response = $result->result('array');

		foreach ( $response as $row ) {
			$outputData[] = str_replace('@', '', $row['name']);
		}

		return $outputData;
	}

	/**
	 * Сохранение произвольного обьекта (без повреждения предыдущих данных).
	 */
	function saveObject($object_name, $data) {
		$schema = "dbo";

		//при необходимости выделяем схему из имени обьекта
		$name_arr = explode('.', $object_name);
		if (count($name_arr) > 1) {
			$schema = $name_arr[0];
			$object_name = $name_arr[1];
		}

		$key_field = !empty($data['key_field']) ? $data['key_field'] : "{$object_name}_id";

		if (!isset($data[$key_field])) {
			$data[$key_field] = null;
		}

		$action = $data[$key_field] > 0 ? "upd" : "ins";
		$proc_name = "p_{$object_name}_{$action}";
		$params_list = $this->getStoredProcedureParamsList($proc_name, $schema);
		$save_data = array();
		$query_part = "";

		//получаем существующие данные если апдейт
		if ($action == "upd") {
			$query = "
				select
					*
				from
					{$schema}.{$object_name} with (nolock)
				where
					{$key_field} = :id;
			";
			$result = $this->getFirstRowFromQuery($query, array(
				'id' => $data[$key_field]
			));
			if (is_array($result)) {
				foreach($result as $key => $value) {
					if (in_array($key, $params_list)) {
						$save_data[$key] = $value;
					}
				}
			}
		}

		foreach($data as $key => $value) {
			if (in_array($key, $params_list)) {
				$save_data[$key] = $value;
			}
		}

		foreach($save_data as $key => $value) {
			if (in_array($key, $params_list) && $key != $key_field) {
				//перобразуем даты в строки
				if (is_object($save_data[$key]) && get_class($save_data[$key]) == 'DateTime') {
					$save_data[$key] = $save_data[$key]->format('Y-m-d H:i:s');
				}
				$query_part .= "@{$key} = :{$key}, ";
			}
		}

		$save_data['pmUser_id'] = isset($data['pmUser_id']) ? $data['pmUser_id'] : null;

		$query = "
			declare
				@{$key_field} bigint = :{$key_field},
				@Error_Code int,
				@Error_Message varchar(4000);

			execute {$schema}.{$proc_name}
				@{$key_field} = @{$key_field} output,
				{$query_part}
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @{$key_field} as {$key_field}, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		if (isset($data['debug_query'])) {
			print getDebugSQL($query, $save_data);
		}
		$result = $this->getFirstRowFromQuery($query, $save_data);
		if ($result && is_array($result)) {
			if($result[$key_field] > 0) {
				$result['success'] = true;
			}
			return $result;
		} else {
			return array('Error_Msg' => 'При сохранении произошла ошибка');
		}
	}

	/**
	 * Удаление произвольного обьекта.
	 */
	function deleteObject($object_name, $data) {
		$schema = "dbo";

		//при необходимости выделяем схему из имени обьекта
		$name_arr = explode('.', $object_name);
		if (count($name_arr) > 1) {
			$schema = $name_arr[0];
			$object_name = $name_arr[1];
		}

		$query = "
			declare
				@Error_Code int,
				@Error_Message varchar(4000);

			execute {$schema}.p_{$object_name}_del
				@{$object_name}_id = :{$object_name}_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @Error_Code as Error_Code, @Error_Message as Error_Message;
		";

		$result = $this->getFirstRowFromQuery($query, $data);
		if ($result && is_array($result)) {
			if(empty($result['Error_Message'])) {
				$result['success'] = true;
			}
			return $result;
		} else {
			return array('Error_Message' => 'При удалении произошла ошибка');
		}
	}

	/**
	 * Получение идентификатора типа документа по коду
	 */
	function getObjectIdByCode($object_name, $code) {
        $schema = "dbo";

        //при необходимости выделяем схему из имени обьекта
        $name_arr = explode('.', $object_name);
        if (count($name_arr) > 1) {
            $schema = $name_arr[0];
            $object_name = $name_arr[1];
        }

		$query = "
			select top 1
				{$object_name}_id
			from
				{$schema}.{$object_name} with (nolock)
			where
				{$object_name}_Code = :code;
		";
		$result = $this->getFirstResultFromQuery($query, array(
			'code' => $code
		));

		return $result && $result > 0 ? $result : false;
	}

	/**
	 * Получение следующего номера произвольного обьекта.
	 */
	function getObjectNextNum($object_name, $num_field) {
		$query = "
			select
				max(cast({$num_field} as int))+1 as num
			from
				{$object_name} (nolock)
			where
				len({$num_field}) <= 6 and
				IsNull((
					Select Case When CharIndex('.', {$num_field}) > 0 Then 0 Else 1 End
					Where IsNumeric({$num_field} + 'e0') = 1
				), 0) = 1
		";
		$num = $this->getFirstResultFromQuery($query);

		return !empty($num) && $num > 0 ? $num : 0;
	}

	/**
	 * Формирование реестра рецептов
	 */
	function forming($data) {
		$this->load->helper('Options');

		$error = array();
		$registry_data = array();
		$recept_array = array();
		$tariff_recept_array = array();
		$logistics_system = $data['session']['setting']['server']['dlo_logistics_system'];
		$orgfarmacy_id = $data['OrgFarmacy_id'];
		$org_id = $data['session']['org_id'];
		$of_subquery = null;
		$total_sum = 0;
		$total_tariff_sum = 0;

		//получение данных реестра
		$query = "
			select
				*
			from
				{$this->schema}.v_RegistryLLO
			where
				RegistryLLO_id = :RegistryLLO_id;
		";
		$registry_data = $this->getFirstRowFromQuery($query, array(
			'RegistryLLO_id' => $data['RegistryLLO_id']
		));

		if (!is_array($registry_data) || count($registry_data) <= 0) {
			$error[] = 'Не удалось получить данные реестра.';
		}

		$this->beginTransaction();

		//при наличии признака переформирования необходимо удалить список рецептов в реестре и восстановить статус рецептов
		if (!empty($data['reforming'])) {
			//восстановление статуса рецепта
			$query = "
				update
					dbo.ReceptOtov
				set
					ReceptStatusType_id = :ReceptStatusType_id,
					ReceptOtov_updDT = dbo.tzGetDate(),
					pmUser_updID = :pmUser_id
				where
					ReceptOtov_id in (
						select
							ReceptOtov_id
						from
							{$this->schema}.RegistryDataRecept
						where
							RegistryLLO_id = :RegistryLLO_id
					) and
					ReceptStatusType_id = :OldReceptStatusType_id;
			";
			$result = $this->db->query($query, array(
				'RegistryLLO_id' => $data['RegistryLLO_id'],
				'ReceptStatusType_id' => $this->getObjectIdByCode('ReceptStatusType', 5), //5 - исключен из реестра
				'OldReceptStatusType_id' => $this->getObjectIdByCode('ReceptStatusType', 4), //4 - включен в реестр
				'pmUser_id' => $data['pmUser_id']
			));

			//очистка списка рецептов
			$query = "
				delete from
					{$this->schema}.RegistryDataRecept
				where
					RegistryLLO_id = :RegistryLLO_id
			";
			$result = $this->db->query($query, array(
				'RegistryLLO_id' => $data['RegistryLLO_id']
			));

			//обновление статуса и сумм реестра рецептов
			$response = $this->saveObject($this->schema.'.RegistryLLO', array(
				'RegistryLLO_id' => $data['RegistryLLO_id'],
				'RegistryLLO_Sum' => null,
				'Registry_Sum2' => null,
				'RegistryStatus_id' => null,
                'pmUser_id' => $data['pmUser_id']
			));
		}

		//получение списка рецептов
		if (count($error) < 1) {
			//подзапрос для получения списка аптек
			if ($logistics_system == 'level3' && $orgfarmacy_id > 0) {
				$of_subquery = "
					ro.OrgFarmacy_id = :OrgFarmacy_id and
				";
			} else if ($logistics_system == 'level2' && $org_id > 0) {
				$of_subquery = "
					ro.OrgFarmacy_id in (
						select
							i_of.OrgFarmacy_id
						from
							v_WhsDocumentSupply i_wds with (nolock)
							inner join v_WhsDocumentTitle i_wdt with (nolock) on i_wdt.WhsDocumentUc_id = i_wds.WhsDocumentUc_id
							inner join v_WhsDocumentRightRecipient i_wdrr with (nolock) on i_wdrr.WhsDocumentTitle_id = i_wdt.WhsDocumentTitle_id
							inner join OrgFarmacy i_of with (nolock) on i_of.Org_id = i_wdrr.Org_id
						where
							i_wds.Org_sid = :Org_sid
					) and
				";
			}

			$query = "
				declare
					@ReceptDelayType_id bigint,
					@ReceptStatusType_id bigint,
					@KatNasel_Code int,
					@Region_id int,
					@WhsDocumentUc_id bigint;

				set @ReceptDelayType_id = (select ReceptDelayType_id from v_ReceptDelayType with(nolock) where ReceptDelayType_Code = 0); -- 0 - Обслужен
				set @ReceptStatusType_id = (select ReceptStatusType_id from v_ReceptStatusType with(nolock) where ReceptStatusType_Code = 5); -- 5 - Исключен из реестра
				set @KatNasel_Code = (select KatNasel_Code from v_KatNasel with(nolock) where KatNasel_id = :KatNasel_id); -- Коды: 1 - Жители области; 2 - Иногородние.
				set @Region_id = dbo.GetRegion();

				set @WhsDocumentUc_id = (select WhsDocumentUc_id from v_WhsDocumentSupply with(nolock) where WhsDocumentSupply_id = :WhsDocumentSupply_id);

				select
					ro.ReceptOtov_id,
					ro.EvnRecept_Ser,
					ro.EvnRecept_Num,
					cast((ro.EvnRecept_Price * ro.EvnRecept_Kolvo) as decimal(10,2)) as Recept_Sum,
					ro.EvnRecept_otpDate,
					ro.OrgFarmacy_id,
					er.WhsDocumentUc_id
				from
					ReceptOtov ro with (nolock)
					left join v_EvnRecept er with (nolock) on er.EvnRecept_id = ro.EvnRecept_id
					left join v_PersonState ps with(nolock) on ps.Person_id = ro.Person_id
					left join v_Address ua with(nolock) on Address_id = ps.UAddress_id
				where
					ro.ReceptDelayType_id = @ReceptDelayType_id and
					(ro.ReceptStatusType_id is null or ro.ReceptStatusType_id = @ReceptStatusType_id) and
					ro.DrugFinance_id = :DrugFinance_id and
					ro.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id and
					{$of_subquery}
					(
						(@KatNasel_Code = 1 and ua.KLRgn_id = @Region_id) or
						(@KatNasel_Code = 2 and ua.KLRgn_id <> @Region_id) or
						@KatNasel_Code = 3
					) and
					(
						@WhsDocumentUc_id is null or
						er.WhsDocumentUc_id is null or
						er.WhsDocumentUc_id = @WhsDocumentUc_id
					) and
					cast(ro.EvnRecept_otpDate as date) between :RegistryLLO_begDate and :RegistryLLO_endDate
			";
			$params = array(
				'KatNasel_id' => $registry_data['KatNasel_id'],
				'DrugFinance_id' => $registry_data['DrugFinance_id'],
				'WhsDocumentCostItemType_id' => $registry_data['WhsDocumentCostItemType_id'],
				'WhsDocumentSupply_id' => $registry_data['WhsDocumentSupply_id'],
				'RegistryLLO_begDate' => !empty($registry_data['RegistryLLO_begDate']) ? $registry_data['RegistryLLO_begDate']->format("Y-m-d") : null,
				'RegistryLLO_endDate' => !empty($registry_data['RegistryLLO_endDate']) ? $registry_data['RegistryLLO_endDate']->format("Y-m-d") : null,
				'OrgFarmacy_id' => $orgfarmacy_id,
				'Org_sid' => $org_id
			);
			//echo getDebugSQL($query, $params);exit;
			$result = $this->db->query($query, $params);

			if (is_object($result)) {
				$recept_array = $result->result('array');
			}

			if (count($recept_array) <= 0) {
				$error[] = 'Нет рецептов для формирования реестра.';
			}
		}

		$status_id = $this->getObjectIdByCode('ReceptStatusType', 4); // 4 - включен в реестр
		foreach($recept_array as $recept) {
			$service_tariff = null;

			//рассчет тарифа на выписку рецепта
			if (count($error) < 1 && !in_array("{$recept['EvnRecept_Ser']}_{$recept['EvnRecept_Num']}", $tariff_recept_array)) {
				$query = "
					select top 1
						uct.UslugaComplexTariff_Tariff as Tariff
					from
						v_WhsDocumentTitle wdt with (nolock)
						inner join v_WhsDocumentTitleTariff wdtt with (nolock) on wdtt.WhsDocumentTitle_id = wdt.WhsDocumentTitle_id
						left join v_UslugaComplexTariff uct with (nolock) on uct.UslugaComplexTariff_id = wdtt.UslugaComplexTariff_id
					where
						wdt.WhsDocumentUc_id = :WhsDocumentUc_id and
						(wdt.WhsDocumentTitle_begDate is null or wdt.WhsDocumentTitle_begDate <= :EvnRecept_otpDate) and
						(wdt.WhsDocumentTitle_endDate is null or wdt.WhsDocumentTitle_endDate >= :EvnRecept_otpDate);
				";
				$service_tariff = $this->getFirstResultFromQuery($query, array(
					'EvnRecept_otpDate' => $recept['EvnRecept_otpDate'],
					'WhsDocumentUc_id' => $recept['WhsDocumentUc_id']
				));

				if (empty($service_tariff)) { //вторая попытка вычислить тариф уже без привязки к контракту
					$query = "
						declare
							@Org_id bigint;

						set @Org_id = (select  top 1 Org_id from v_OrgFarmacy with(nolock) where OrgFarmacy_id = :OrgFarmacy_id);

						select top 1
							uct.UslugaComplexTariff_Tariff as Tariff
						from
							v_WhsDocumentTitle wdt with (nolock)
							inner join v_WhsDocumentTitleTariff wdtt with (nolock) on wdtt.WhsDocumentTitle_id = wdt.WhsDocumentTitle_id
							inner join v_WhsDocumentRightRecipient wdrr with (nolock) on wdrr.WhsDocumentTitle_id = wdt.WhsDocumentTitle_id and wdrr.Org_id = @Org_id
							left join v_UslugaComplexTariff uct with (nolock) on uct.UslugaComplexTariff_id = wdtt.UslugaComplexTariff_id
						where
							(wdt.WhsDocumentTitle_begDate is null or wdt.WhsDocumentTitle_begDate <= :EvnRecept_otpDate) and
							(wdt.WhsDocumentTitle_endDate is null or wdt.WhsDocumentTitle_endDate >= :EvnRecept_otpDate)
						order by
							wdtt.WhsDocumentTitleTariff_id;
					";
					$service_tariff = $this->getFirstResultFromQuery($query, array(
						'EvnRecept_otpDate' => $recept['EvnRecept_otpDate'],
						'OrgFarmacy_id' => $recept['OrgFarmacy_id']
					));
				}

				if (!empty($service_tariff) && $service_tariff > 0) {
					$total_tariff_sum += $service_tariff;
				}

				//для того чтобы не посчитать стоимость выписки несколько раз для одного рецепта
				$tariff_recept_array[] = "{$recept['EvnRecept_Ser']}_{$recept['EvnRecept_Num']}";
			}

			if (count($error) < 1) {
				if (!empty($recept['Recept_Sum']) && $recept['Recept_Sum'] > 0) {
					$total_sum += $recept['Recept_Sum'];
				}

				//добавление рецепта в реестр
				$response = $this->saveObject("{$this->schema}.RegistryDataRecept", array(
					'WhsDocumentSupply_id' => $registry_data['WhsDocumentSupply_id'],
					'RegistryType_id' => $registry_data['RegistryType_id'],
					'ReceptOtov_id' => $recept['ReceptOtov_id'],
					'RegistryDataRecept_Sum' => $recept['Recept_Sum'],
					'RegistryDataRecept_Sum2' => $service_tariff > 0 ? $service_tariff : null,
					'RegistryLLO_id' => $registry_data['RegistryLLO_id'],
					'pmUser_id' => $data['pmUser_id']
				));

				//обновление статуса рецепта
				$query = "
					update
						dbo.ReceptOtov
					set
						ReceptStatusType_id = :ReceptStatusType_id,
						ReceptOtov_updDT = dbo.tzGetDate(),
						pmUser_updID = :pmUser_id
					where
						ReceptOtov_id = :ReceptOtov_id;
				";
				$response = $this->getFirstRowFromQuery($query, array(
					'ReceptOtov_id' => $recept['ReceptOtov_id'],
					'ReceptStatusType_id' => $status_id,
					'pmUser_id' => $data['pmUser_id']
				));
			}

			//обновление данных реестра
			if (count($error) < 1) {
				$response = $this->saveObject($this->schema.'.RegistryLLO', array(
					'RegistryLLO_id' => $data['RegistryLLO_id'],
					'RegistryLLO_Sum' => $total_sum,
					'Registry_Sum2' => $total_tariff_sum,
					'RegistryStatus_id' => $this->getObjectIdByCode('RegistryStatus', 1), //1 - Сформированные
					'pmUser_id' => $data['pmUser_id']
				));

				$response = $this->expertise($data, false, false);
				if (!$this->isSuccessful($response)) {
					$error[] = $response[0]['Error_Msg'];
				}
			}
		}

		$result = array();

		if (count($error) > 0) {
			$result['Error_Msg'] = $error[0];
			$this->rollbackTransaction();
			return $result;
		} else {
			$result['success'] = true;
			$result['RegistryLLO_id'] = $data['RegistryLLO_id'];
		}

		$this->commitTransaction();
		return $result;
	}

	/**
	 * Получение данных для редактирования статуса экспертизы
	 */
	function loadRegistryLLOExpertiseForm($data) {
		$params = array('RegistryLLO_id' => $data['RegistryLLO_id']);

		$query = "
			select top 1
				rllo.RegistryLLO_id,
				AcceptRecept.AcceptRecept_Count,
				rllo.RegistryLLO_ErrorCount,
				rul.ReceptUploadStatus_id
			from
				{$this->schema}.v_RegistryLLO rllo with(nolock)
				left join v_ReceptUploadLog rul with(nolock) on rul.ReceptUploadLog_id = rllo.ReceptUploadLog_id
				outer apply(
					select top 1
						count(rdr.RegistryDataRecept_id) as AcceptRecept_Count
					from 
						{$this->schema}.RegistryDataRecept rdr with(nolock)
						left join {$this->schema}.ReceptStatusFLKMEK rsf with(nolock) on rsf.ReceptStatusFLKMEK_id = rdr.ReceptStatusFLKMEK_id
					where
						rdr.RegistryLLO_id = rllo.RegistryLLO_id
						and isnull(rsf.ReceptStatusFLKMEK_Code,0) in (3,5,6)
				) AcceptRecept
			where
				rllo.RegistryLLO_id = :RegistryLLO_id
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Ручное изменение статуса экспертизы
	 */
	function saveRegistryLLOExpertise($data) {
		$ReceptUploadLog = $this->getFirstRowFromQuery("
			select
				rul.ReceptUploadLog_id,
				rul.ReceptUploadStatus_id
			from {$this->schema}.v_RegistryLLO rllo with(nolock)
			inner join v_ReceptUploadLog rul with(nolock) on rul.ReceptUploadLog_id = rllo.ReceptUploadLog_id
			where rllo.RegistryLLO_id = :RegistryLLO_id
		", array(
			'RegistryLLO_id' => $data['RegistryLLO_id']
		));
		if (!is_array($ReceptUploadLog)) {
			return $this->createError('','Ошибка при получении данных реестра');
		}

		if ($ReceptUploadLog['ReceptUploadStatus_id'] != $data['ReceptUploadStatus_id']) {
			$this->beginTransaction();

			if (empty($ReceptUploadLog['ReceptUploadLog'])) {
				$resp = $this->addReceptUploadLog($data);
				if (!$this->isSuccessful($resp)) {
					$this->rollbackTransaction();
					return $resp;
				}
				$ReceptUploadLog['ReceptUploadLog'] = $resp[0]['ReceptUploadLog_id'];
			} else {
				$resp = $this->saveObject('ReceptUploadLog', array(
					'ReceptUploadLog_id' => $ReceptUploadLog['ReceptUploadLog'],
					'ReceptUploadStatus_id' => $data['ReceptUploadStatus_id'],
					'pmUser_id' => $data['pmUser_id'],
				));
				if (!empty($resp['Error_Msg'])) {
					$this->rollbackTransaction();
					return $this->createError('',$resp['Error_Msg']);
				}
			}

			//Переформировать акт
			$resp = $this->createExpertiseAct($data);
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}

			$this->commitTransaction();
		}

		return array(array('success' => true));
	}

	/**
	 * Сохранениее данных об ошибке реестра рецептов
	 */
	function saveRegistryLLOError($data) {
		$params = array(
			'RegistryLLOError_id' => !empty($data['RegistryLLOError_id'])?$data['RegistryLLOError_id']:null,
			'RegistryLLO_id' => $data['RegistryLLO_id'],
			'ReceptOtov_id' => $data['ReceptOtov_id'],
			'RegistryReceptErrorType_id' => $data['RegistryReceptErrorType_id'],
			'pmUser_id' => $data['pmUser_id'],
		);
		if (empty($params['RegistryLLOError_id'])) {
			$procedure = "p_RegistryLLOError_ins";
		} else {
			$procedure = "p_RegistryLLOError_upd";
		}
		$query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code int,
				@Res bigint = :RegistryLLOError_id;
			execute {$this->schema}.{$procedure}
				@RegistryLLOError_id = @Res output,
				@RegistryLLO_id = :RegistryLLO_id,
				@ReceptOtov_id = :ReceptOtov_id,
				@RegistryReceptErrorType_id = :RegistryReceptErrorType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Res as RegistryLLOError_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при сохранении данных об ошибке реестра рецептов');
		}
		return $response;
	}

	/**
	 * Экспертиза реестра рецептов
	 */
	function expertise($data, $allow_transaction = true, $create_act = true) {
		$params = array('RegistryLLO_id' => $data['RegistryLLO_id']);

		$resp = $this->loadRegistryReceptErrorTypeCombo();
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при получении данных из справочника ошибок реестра рецептов');
		}

		$error_type_by_code = array();
		foreach ($resp as $item) {
			$error_type_by_code[$item['RegistryReceptErrorType_Type']] = $item['RegistryReceptErrorType_id'];
		}

		$query = "
			select
				rllo.RegistryLLO_id,
				rllo.ReceptUploadLog_id,
				rdr.RegistryDataRecept_id,
				ro.ReceptOtov_id,
				isnull(ro.ReceptOtov_IsKek,1) as ReceptOtov_IsKek,
				d.DrugComplexMnn_id,
				dnls.DrugNormativeList_id
			from
				{$this->schema}.v_RegistryDataRecept rdr with(nolock)
				inner join {$this->schema}.v_RegistryLLO rllo with(nolock) on rllo.RegistryLLO_id = rdr.RegistryLLO_id
				inner join ReceptOtov ro with(nolock) on ro.ReceptOtov_id = rdr.ReceptOtov_id
				left join rls.v_Drug d with(nolock) on d.Drug_id = ro.Drug_cid
				left join rls.v_DrugComplexMnn dcm with(nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnName dcmn with(nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				outer apply(
					select top 1
						dnls.DrugNormativeList_id
					from
						v_DrugNormativeListSpec dnls with(nolock)
						left join v_DrugNormativeListSpecTorgLink dnlstl with(nolock) on dnlstl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
						left join v_DrugNormativeListSpecFormsLink dnlsfl with(nolock) on dnlsfl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
					where
						dnls.DrugNormativeListSpecMNN_id = dcmn.ACTMATTERS_id
						and (dnlstl.DrugNormativeListSpecTorg_id is null or dnlstl.DrugNormativeListSpecTorg_id = 15875)
						and (dnlsfl.DrugNormativeListSpecForms_id is null or dnlsfl.DrugNormativeListSpecForms_id = 694)
						and cast(ro.EvnRecept_setDT as date) between dnls.DrugNormativeListSpec_BegDT and isnull(dnls.DrugNormativeListSpec_EndDT,cast(ro.EvnRecept_setDT as date))
						and isnull(dnls.DrugNormativeListSpec_IsVK,1) = 1
				) dnls
			where
				rdr.RegistryLLO_id = :RegistryLLO_id
		";

		$recept_list = $this->queryResult($query, $params);
		if (!is_array($recept_list)) {
			return $this->createError('','Ошибка при получении данных рецептов');
		}
		if (count($recept_list) == 0) {
			return $this->createError('','Отсутвуют в реестре отсутвуют рецепты');
		}

		$ReceptUploadLog_id = $recept_list[0]['ReceptUploadLog_id'];

		if ($allow_transaction) $this->beginTransaction();

		$hasErrors = function($errors, $receptErrors) {
			return count(array_intersect($errors, $receptErrors)) > 0;
		};

		$error_count = 0;

		foreach ($recept_list as $recept) {
			//Очищение рецепта от предыдущих ошибок
			$query = "
				delete {$this->schema}.RegistryLLOError with(rowlock)
				from {$this->schema}.RegistryLLOError rlloe
				left join {$this->schema}.RegistryReceptErrorType rret with(nolock) on rret.RegistryReceptErrorType_id = rlloe.RegistryReceptErrorType_id
				where rlloe.ReceptOtov_id = :ReceptOtov_id and rret.RegistryReceptErrorType_Type in ('Л01')
			";
			$this->db->query($query, $recept);

			$errors = array();
			//Определение ошибок
			if (in_array($this->regionNick, array('saratov','khak')) &&
				$recept['ReceptOtov_IsKek'] == 1 && empty($recept['DrugNormativeList_id'])
			) {
				$errors[] = 'Л01';
			}

			//Сохранение ошибок
			foreach ($errors as $error) {
				$resp = $this->saveRegistryLLOError(array(
					'RegistryLLOError_id' => null,
					'RegistryLLO_id' => $recept['RegistryLLO_id'],
					'ReceptOtov_id' => $recept['ReceptOtov_id'],
					'RegistryReceptErrorType_id' => $error_type_by_code[$error],
					'pmUser_id' => $data['pmUser_id'],
				));
				if (!$this->isSuccessful($resp)) {
					if ($allow_transaction) $this->rollbackTransaction();
					return $resp;
				}
			}

			$status = 3;		//годен к оплате

			//Получение всех ошибок по рецепту
			$errors = $this->queryList("
				select distinct rret.RegistryReceptErrorType_Type
				from {$this->schema}.RegistryLLOError rlloe with(nolock)
				inner join {$this->schema}.v_RegistryReceptErrorType rret with(nolock) on rret.RegistryReceptErrorType_id = rlloe.RegistryReceptErrorType_id
				where rlloe.RegistryLLO_id = :RegistryLLO_id and rlloe.ReceptOtov_id = :ReceptOtov_id
			", $recept);
			if (!is_array($errors)) {
				if ($allow_transaction) $this->rollbackTransaction();
				return $this->createError('','Ошибка при получении списка ошибок рецепта');
			}

			$error_count += count($errors);

			$statusErrorsMap = array(
				'khak' => array(
					1 => array('Л03','Л04','Ц110','Ц111','Ц113'),
					4 => array('П01','П04','Р01','Р02','Р03','Р04','Л01'),
				),
				'saratov' => array(
					1 => array('С01','С02','Л03','Л04'),
					4 => array('П01','П02','П03','П04','Р01','Р02','Р03','Р04','Р05','Р06','Р07','Р08','Р09','Р10','Р11','Р12','Р13','Л01','Л02'),
				),
			);

			if (isset($statusErrorsMap[$this->regionNick])) {
				foreach ($statusErrorsMap[$this->regionNick] as $m_status => $m_errors) {
					if ($hasErrors($m_errors, $errors)) {
						$status = $m_status;
						break;
					}
				}
			}

			if (!empty($status)) {
				$resp = $this->setReceptStatus(array(
					'RegistryDataRecept_id' => $recept['RegistryDataRecept_id'],
					'ReceptStatusFLKMEK_Code' => $status,
					'pmUser_id' => $data['pmUser_id']
				), false);
				if (!empty($resp['Error_Msg'])) {
					if ($allow_transaction) $this->rollbackTransaction();
					return $this->createError('',$resp['Error_Msg']);
				}

				$resp = $this->saveObject('ReceptOtov', array(
					'ReceptOtov_id' => $recept['ReceptOtov_id'],
					'ReceptStatusType_id' => ($status==3)?2:1,
					'pmUser_id' => $data['pmUser_id'],
				));
				if (!empty($resp['Error_Msg'])) {
					if ($allow_transaction) $this->rollbackTransaction();
					return $this->createError('',$resp['Error_Msg']);
				}
			}
		}

		//расчет статуса реестра
		$query = "
			select
				rsf.ReceptStatusFLKMEK_Code
			from
				{$this->schema}.v_RegistryDataRecept rdr with(nolock)
				left join {$this->schema}.ReceptStatusFLKMEK rsf with(nolock) on rsf.ReceptStatusFLKMEK_id = rdr.ReceptStatusFLKMEK_id
			where
				rdr.RegistryLLO_id = :RegistryLLO_id
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			if ($allow_transaction) $this->rollbackTransaction();
			return $this->createError('','Ошибка при получении статутусов рецптов');
		}
		$all_count = count($resp);
		$accept_count = 0;
		$refuse_count = 0;
		foreach ($resp as $item) {
			if (in_array($item['ReceptStatusFLKMEK_Code'], array(3,5,6))) {
				$accept_count++;
			} else {
				$refuse_count++;
			}
		}

		$ReceptUploadStatus_Code = null;
		if ($all_count > 0) {
			if ($all_count == $refuse_count) {
				$ReceptUploadStatus_Code = 3;	//отказ: ошибки ФЛК/МЭК
			} else if ($all_count == $accept_count) {
				$ReceptUploadStatus_Code = 4;	//приняты
			} else {
				$ReceptUploadStatus_Code = 5;	//частично приняты
			}
		}
		if (!empty($ReceptUploadStatus_Code)) {
			if (empty($ReceptUploadLog_id)) {
				$data['ReceptUploadStatus_id'] = $this->getObjectIdByCode('ReceptUploadStatus', $ReceptUploadStatus_Code);
				$resp = $this->addReceptUploadLog($data);
				if (!$this->isSuccessful($resp)) {
					if ($allow_transaction) $this->rollbackTransaction();
					return $resp;
				}
				$ReceptUploadLog_id = $resp[0]['ReceptUploadLog_id'];
			} else {
				$resp = $this->saveObject('ReceptUploadLog', array(
					'ReceptUploadLog_id' => $ReceptUploadLog_id,
					'ReceptUploadStatus_id' => $this->getObjectIdByCode('ReceptUploadStatus', $ReceptUploadStatus_Code),
					'pmUser_id' => $data['pmUser_id'],
				));
				if (!empty($resp['Error_Msg'])) {
					if ($allow_transaction) $this->rollbackTransaction();
					return $this->createError('',$resp['Error_Msg']);
				}
			}
		}

		$resp = $this->saveObject($this->schema.'.RegistryLLO', array(
			'RegistryLLO_id' => $data['RegistryLLO_id'],
			'RegistryLLO_RecordCount' => $all_count,
			'RegistryLLO_ErrorCount' => /*$refuse_count*/$error_count,
			'RegistryLLO_RecordPaidCount' => $accept_count,
			'pmUser_id' => $data['pmUser_id'],
		));
		if (!empty($resp['Error_Msg'])) {
			if ($allow_transaction) $this->rollbackTransaction();
			return $this->createError('',$resp['Error_Msg']);
		}

		$resp = $this->recount($data);
		if (!empty($resp['Error_Msg'])) {
			if ($allow_transaction) $this->rollbackTransaction();
			return $this->createError('',$resp['Error_Msg']);
		}

        if ($create_act) {
            $resp = $this->createExpertiseAct($data);
            if (!$this->isSuccessful($resp)) {
                if ($allow_transaction) $this->rollbackTransaction();
                return $resp;
            }
        }

		if ($allow_transaction) $this->commitTransaction();

		return array(array('success' => true));
	}

	/**
	 * Создание акта по экспертизе
	 */
	function createExpertiseAct($data) {
		$params = array('RegistryLLO_id' => $data['RegistryLLO_id']);

		$query = "
			declare @dt datetime = dbo.tzGetDate()
			select
				rul.ReceptUploadLog_id,
				convert(varchar(10), @dt, 104)+' '+convert(varchar(5), @dt, 108) as ActDateTime,
				o.Org_Name,
				rllo.RegistryLLO_Num,
				convert(varchar(10), RegistryLLO_begDate, 104) as RegistryLLO_begDate,
				convert(varchar(10), RegistryLLO_endDate, 104) as RegistryLLO_endDate,
				kn.KatNasel_Name,
				wdcit.WhsDocumentCostItemType_Name,
				df.DrugFinance_Name,
				rllo.RegistryLLO_RecordCount,
				cast(rllo.RegistryLLO_Sum as Numeric(16,2)) as RegistryLLO_Sum,
				rus.ReceptUploadStatus_Name,
				rllo.RegistryLLO_ErrorCount,
				AcceptRecept.AcceptRecept_Count,
				AcceptRecept.AcceptRecept_Sum,
				RefuseRecept.RefuseRecept_Count,
				RefuseRecept.RefuseRecept_Sum
			from
				{$this->schema}.v_RegistryLLO rllo with(nolock)
				inner join v_ReceptUploadLog rul with(nolock) on rul.ReceptUploadLog_id = rllo.ReceptUploadLog_id
				left join v_ReceptUploadStatus rus with(nolock) on rus.ReceptUploadStatus_id = rul.ReceptUploadStatus_id
				left join v_Org o with(nolock) on o.Org_id = rllo.Org_id
				left join v_KatNasel kn with(nolock) on kn.KatNasel_id = rllo.KatNasel_id
				left join v_WhsDocumentCostItemType wdcit with(nolock) on wdcit.WhsDocumentCostItemType_id = rllo.WhsDocumentCostItemType_id
				left join v_DrugFinance df with(nolock) on df.DrugFinance_id = rllo.DrugFinance_id
				outer apply (
					select top 1
						count(rdr.RegistryDataRecept_id) as AcceptRecept_Count,
						isnull(sum(rdr.RegistryDataRecept_Sum),0) as AcceptRecept_Sum
					from 
						{$this->schema}.RegistryDataRecept rdr with(nolock)
						left join {$this->schema}.ReceptStatusFLKMEK rsf with(nolock) on rsf.ReceptStatusFLKMEK_id = rdr.ReceptStatusFLKMEK_id
					where
						rdr.RegistryLLO_id = rllo.RegistryLLO_id
						and isnull(rsf.ReceptStatusFLKMEK_Code,0) in (3,5,6)
				) AcceptRecept
				outer apply (
					select top 1
						count(rdr.RegistryDataRecept_id) as RefuseRecept_Count,
						isnull(sum(rdr.RegistryDataRecept_Sum),0) as RefuseRecept_Sum
					from 
						{$this->schema}.RegistryDataRecept rdr with(nolock)
						left join {$this->schema}.ReceptStatusFLKMEK rsf with(nolock) on rsf.ReceptStatusFLKMEK_id = rdr.ReceptStatusFLKMEK_id
					where
						rdr.RegistryLLO_id = rllo.RegistryLLO_id
						and isnull(rsf.ReceptStatusFLKMEK_Code,0) not in (3,5,6)
				) RefuseRecept
			where
				rllo.RegistryLLO_id = :RegistryLLO_id
		";
		$act_data = $this->getFirstRowFromQuery($query, $params);
		if (!is_array($act_data)) {
			return $this->createError('','Ошибка при получении данных реестра рецептов');
		}

		$query = "
			select
				ro.EvnRecept_Ser,
				ro.EvnRecept_Num,
				rret.RegistryReceptErrorType_Type,
				rret.RegistryReceptErrorType_Name
			from
				{$this->schema}.v_RegistryLLOError rlloe with(nolock)
				left join ReceptOtov ro with(nolock) on ro.ReceptOtov_id = rlloe.ReceptOtov_id
				left join {$this->schema}.v_RegistryReceptErrorType rret with(nolock) on rret.RegistryReceptErrorType_id = rlloe.RegistryReceptErrorType_id
			where
				rlloe.RegistryLLO_id = :RegistryLLO_id
			order by
				rlloe.ReceptOtov_id
		";
		$errors = $this->queryResult($query, $params);
		if (!is_array($errors)) {
			return $this->createError('','Ошибка при получении данных ошибок реестра рецептов');
		}

		$act_data['errors'] = $errors;
		$template = 'registry_llo_expertise_act';

		$this->load->library('parser');
		$act = $this->parser->parse($template, $act_data, true);

		$path = EXPORTPATH_ROOT."registry_llo/";
		if (!file_exists($path)) mkdir($path);

		$filename = "act_".time();
		$path = $path.$filename."/";
		if (!file_exists($path)) mkdir($path);

		$filepath = $path.$filename.".txt";

		file_put_contents($filepath, $act);

		$resp = $this->saveObject('ReceptUploadLog', array(
			'ReceptUploadLog_id' => $act_data['ReceptUploadLog_id'],
			'ReceptUploadLog_Act' => $filepath,
			'pmUser_id' => $data['pmUser_id'],
		));
		if (!empty($resp['Error_Msg'])) {
			return $this->createError('',$resp['Error_Msg']);
		}

		return array(array('success' => true, 'url' => $filepath));
	}

	/**
	 * Пересчет сумм для реестра рецептов
	 */
	function recount($data) {
		$error = array();

		$query = "
			select
				sum(isnull(RegistryDataRecept_Sum, 0)) as RegistryDataRecept_Sum,
				sum(isnull(RegistryDataRecept_Sum2, 0)) as RegistryDataRecept_Sum2
			from
				{$this->schema}.RegistryDataRecept
			where
				RegistryLLO_id = :RegistryLLO_id;
		";
		$sum_data = $this->getFirstRowFromQuery($query, $data);

		$response = $this->saveObject($this->schema.'.RegistryLLO', array(
			'RegistryLLO_id' => $data['RegistryLLO_id'],
			'RegistryLLO_Sum' => $sum_data['RegistryDataRecept_Sum'],
			'Registry_Sum2' => $sum_data['RegistryDataRecept_Sum2'],
			'pmUser_id' => $data['pmUser_id']
		));

		if (!empty($response['Error_Msg'])) {
			$error[] = $response['Error_Msg'];
		}

		if (count($error) > 0) {
			return array('Error_Msg' => $error[0]);
		} else {
			return array(
				'RegistryLLO_id' => $data['RegistryLLO_id'],
				'success' => true
			);
		}
	}

	/**
	 * Добавление записи в журнал
	 */
	function addReceptUploadLog($data) {
		$query = "
			declare
				@ReceptUploadLog_setDT datetime,
				@ReceptUploadDeliveryType_id bigint,
				@ReceptUploadType_id bigint,
				@ReceptUploadStatus_id bigint;

			set @ReceptUploadLog_setDT = dbo.tzGetDate();
			set @ReceptUploadDeliveryType_id = (select ReceptUploadDeliveryType_id from v_ReceptUploadDeliveryType with(nolock) where ReceptUploadDeliveryType_Code = :ReceptUploadDeliveryType_Code);
			set @ReceptUploadType_id = (select ReceptUploadType_id from v_ReceptUploadType with(nolock) where ReceptUploadType_Code = :ReceptUploadType_Code);
			set @ReceptUploadStatus_id = (select ReceptUploadStatus_id from v_ReceptUploadStatus with(nolock) where ReceptUploadStatus_Code = :ReceptUploadStatus_Code);

			select
				@ReceptUploadLog_setDT as ReceptUploadLog_setDT,
				@ReceptUploadDeliveryType_id as ReceptUploadDeliveryType_id,
				@ReceptUploadType_id as ReceptUploadType_id,
				@ReceptUploadStatus_id as ReceptUploadStatus_id;
		";
		$common_data = $this->getFirstRowFromQuery($query, array(
			'ReceptUploadDeliveryType_Code' => 3, //3 - ПроМед
			'ReceptUploadType_Code' => 3, //3 - сводные реестры рецептов
			'ReceptUploadStatus_Code' => 1 //1 - данные получены
		));

		$response = $this->saveObject('ReceptUploadLog', array(
			'ReceptUploadLog_id' => null,
			'ReceptUploadLog_setDT' => $common_data['ReceptUploadLog_setDT'],
			'ReceptUploadDeliveryType_id' => $common_data['ReceptUploadDeliveryType_id'],
			'ReceptUploadType_id' => $common_data['ReceptUploadType_id'],
			'Contragent_id' => $data['Contragent_id'],
			'ReceptUploadStatus_id' => !empty($data['ReceptUploadStatus_id'])?$data['ReceptUploadStatus_id']:$common_data['ReceptUploadStatus_id'],
			'pmUser_id' => $data['pmUser_id']
		));
		if (!empty($response['Error_Msg'])) {
			return $this->createError('',$response['Error_Msg']);
		}
		$log_id = $response['ReceptUploadLog_id'];

		//связывем реестр с логом
		if ($log_id > 0) {
			$resp = $this->saveObject($this->schema.'.RegistryLLO', array(
				'RegistryLLO_id' => $data['RegistryLLO_id'],
				'ReceptUploadLog_id' => $log_id,
				'pmUser_id' => $data['pmUser_id']
			));
			if (!empty($resp['Error_Msg'])) {
				return $this->createError('',$response['Error_Msg']);
			}
		}

		return array($response);
	}

	/**
	 * Установка статуса для реестра рецептов
	 */
	function setRegistryStatus($data) {
		$error = array();

		$this->beginTransaction();

		//внесение сопутствующих данных
		switch($data['RegistryStatus_Code']) {
			case 2: //2 - К оплате
				//получаем идентификатор записи в логе загрузок
				$query = "
					select
						ReceptUploadLog_id
					from
						{$this->schema}.RegistryLLO with (nolock)
					where
						RegistryLLO_id = :RegistryLLO_id;
				";
				$log_id = $this->getFirstResultFromQuery($query, $data);

				//меняем статус записи в логе загрузок
				if ($log_id > 0) {
					$response = $this->saveObject('ReceptUploadLog', array(
						'ReceptUploadLog_id' => $log_id,
						'ReceptUploadStatus_id' => $this->getObjectIdByCode('ReceptUploadStatus', 4), //4 - приняты
						'pmUser_id' => $data['pmUser_id']
					));
					if (!empty($response['Error_Msg'])) {
						$error[] = $response['Error_Msg'];
					}
				}
				break;
			case 3: //3 - В работе
				//проверяем наличие записи в логе загрузке реестров рецептов
				$query = "
					select
						ReceptUploadLog_id
					from
						{$this->schema}.RegistryLLO with (nolock)
					where
						RegistryLLO_id = :RegistryLLO_id;
				";
				$log_id = $this->getFirstResultFromQuery($query, $data);

				//добавляем запись в лог
				if (empty($log_id)) {
					$resp = $this->addReceptUploadLog($data);
					if (!$this->isSuccessful($resp)) {
						$error[] = $resp[0]['Error_Msg'];
					}
					$log_id = $resp[0]['ReceptUploadLog_id'];
				}
				break;
		}

		//смена статуса
		if (count($error) == 0) {
			$status_id = $this->getObjectIdByCode('RegistryStatus', $data['RegistryStatus_Code']);

			$response = $this->saveObject($this->schema.'.RegistryLLO', array(
				'RegistryLLO_id' => $data['RegistryLLO_id'],
				'RegistryStatus_id' => $status_id,
				'pmUser_id' => $data['pmUser_id']
			));
			if (!empty($response['Error_Msg'])) {
				$error[] = $response['Error_Msg'];
			}
		}

		if (count($error) > 0) {
			$this->rollbackTransaction();
			return array('Error_Msg' => $error[0]);
		} else {
			$this->commitTransaction();
			return array(
				'RegistryLLO_id' => $data['RegistryLLO_id'],
				'success' => true
			);
		}
	}

	/**
	 * Установка статуса для рецепта
	 */
	function setReceptStatus($data, $allow_transaction = true) {
		$error = array();
        $region = '';

        if (isset($data['session']['region'])) {
            $region = $data['session']['region']['nick'];
        }

		if ($allow_transaction) $this->beginTransaction();

        //подсчет количества ошибок для рецепта
        $query = "
            select
                count(rle.RegistryLLOError_id) as cnt
            from
                {$this->schema}.v_RegistryDataRecept rdr with (nolock)
                inner join {$this->schema}.v_RegistryLLOError rle with (nolock) on rle.RegistryLLO_id = rdr.RegistryLLO_id and rle.ReceptOtov_id = rdr.ReceptOtov_id
            where
                rdr.RegistryDataRecept_id = :RegistryDataRecept_id
        ";
        $exp_err_cnt = $this->getFirstResultFromQuery($query, array(
            'RegistryDataRecept_id' => $data['RegistryDataRecept_id']
        ));

        //сопутствующие проверки и изменения в данных
        $is_received_id = null;
        switch($data['ReceptStatusFLKMEK_Code']) {
            case 0: //0 - В обработке
                if ($region == 'saratov') {
                    $is_received_id = $this->getObjectIdByCode('YesNo', '0'); //Признак получения рецепта: 1 - Нет
                }
                break;
            case 1: //1 - Отказ в оплате
            case 4: //4 - Не принят к оплате, ошибки
                if ($exp_err_cnt <= 0) {
                    $error[] = "Присвоение статуса не возможно, т.к. у рецепта нет ошибок.";
                }
                break;
            case 5: //5 - Принят к оплате
                if ($region == 'saratov') {
                    $is_received_id = $this->getObjectIdByCode('YesNo', '1'); //Признак получения рецепта: 1 - Да
                }
                break;
            case 6: //6 - Передан на оплату
                if ($exp_err_cnt > 0) {
                    $error[] = "Присвоение статуса не возможно, т.к. у рецепта есть ошибки.";
                }
                break;
        }

		//смена статуса, а также проставление признака получения рецепта при необходимости
		if (count($error) == 0) {
			$status_id = $this->getObjectIdByCode($this->schema.'.ReceptStatusFLKMEK', $data['ReceptStatusFLKMEK_Code']);

            $saved_data = array(
                'RegistryDataRecept_id' => $data['RegistryDataRecept_id'],
                'ReceptStatusFLKMEK_id' => $status_id,
                'pmUser_id' => $data['pmUser_id']
            );

            if ($is_received_id > 0) {
                $saved_data['RegistryDataRecept_IsReceived'] = $is_received_id;
            }

			$response = $this->saveObject($this->schema.'.RegistryDataRecept', $saved_data);
			if (!empty($response['Error_Msg'])) {
				$error[] = $response['Error_Msg'];
			}
		}

		if (count($error) > 0) {
			if ($allow_transaction) $this->beginTransaction();
			return array('Error_Msg' => $error[0]);
		} else {
			if ($allow_transaction) $this->commitTransaction();
			return array(
				'RegistryDataRecept_id' => $data['RegistryDataRecept_id'],
				'success' => true
			);
		}
	}

    /**
     * Загрузка списка ЛПУ для комбобокса
     */
    function loadLpuCombo($data) {
        $params = array();
        $filters = array();
        $where = null;

        if (!empty($data['query'])) {
            $filters[] = "Lpu_Name like :Lpu_Name";
            $params['Lpu_Name'] = '%'.$data['query'].'%';
        }

        if (count($filters) > 0) {
            $where = "
				where
				    ".join(" and ", $filters)."
			";
        }

        $query = "
			select
				Lpu_id,
				Lpu_Name
			from
				v_Lpu with(nolock)
			{$where}
			order by
			    Lpu_Name;
		";

        $result = $this->db->query($query, $params);

        if (is_object($result)) {
            $result = $result->result('array');
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка статусов рецепта в реестре для комбобокса
     */
    function loadReceptStatusFLKMEKCombo() {
        $query = "
			select
				ReceptStatusFLKMEK_id,
				ReceptStatusFLKMEK_Code,
				ReceptStatusFLKMEK_Name
			from
				{$this->schema}.ReceptStatusFLKMEK
			order by
			    ReceptStatusFLKMEK_Code;
		";

        $result = $this->db->query($query);

        if (is_object($result)) {
            $result = $result->result('array');
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка ошибок экспертизы для комбобокса
     */
    function loadRegistryReceptErrorTypeCombo() {
        $query = "
			select
				RegistryReceptErrorType_id,
				RegistryReceptErrorType_Type,
				RegistryReceptErrorType_Name
			from
				{$this->schema}.RegistryReceptErrorType
			order by
			    RegistryReceptErrorType_Name;
		";

        $result = $this->db->query($query);

        if (is_object($result)) {
            $result = $result->result('array');
            return $result;
        } else {
            return false;
        }
    }
}
?>
