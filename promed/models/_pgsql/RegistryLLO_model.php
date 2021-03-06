<?php defined('BASEPATH') or die ('No direct script access allowed');

class RegistryLLO_model extends SwPgModel {
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
		        RegistryLLO_id as \"RegistryLLO_id\",
                RegistryType_id as \"RegistryType_id\",
                RegistryLLO_begDate as \"RegistryLLO_begDate\",
                RegistryLLO_endDate as \"RegistryLLO_endDate\",
                KatNasel_id as \"KatNasel_id\",
                RegistryLLO_Num as \"RegistryLLO_Num\",
                RegistryLLO_accDate as \"RegistryLLO_accDate\",
                RegistryStatus_id as \"RegistryStatus_id\",
                RegistryLLO_Sum as \"RegistryLLO_Sum\",
                RegistryLLO_IsActive as \"RegistryLLO_IsActive\",
                RegistryLLO_ErrorCount as \"RegistryLLO_ErrorCount\",
                RegistryLLO_ErrorCommonCount as \"RegistryLLO_ErrorCommonCount\",
                RegistryLLO_RecordCount as \"RegistryLLO_RecordCount\",
                pmUser_insID as \"pmUser_insID\",
                pmUser_updID as \"pmUser_updID\",
                RegistryLLO_insDT as \"RegistryLLO_insDT\",
                RegistryLLO_updDT as \"RegistryLLO_updDT\",
                RegistryLLO_ExportPath as \"RegistryLLO_ExportPath\",
                RegistryLLO_expDT as \"RegistryLLO_expDT\",
                RegistryLLO_RecordPaidCount as \"RegistryLLO_RecordPaidCount\",
                RegistryLLO_xmlExportPath as \"RegistryLLO_xmlExportPath\",
                RegistryLLO_xmlExpDT as \"RegistryLLO_xmlExpDT\",
                RegistryLLO_Task as \"RegistryLLO_Task\",
                RegistryLLO_SumPaid as \"RegistryLLO_SumPaid\",
                RegistryLLO_CheckStatusDate as \"RegistryLLO_CheckStatusDate\",
                RegistryLLO_CheckStatusTFOMSDate as \"RegistryLLO_CheckStatusTFOMSDate\",
                RegistryLLO_sendDT as \"RegistryLLO_sendDT\",
                RegistryLLO_IsNeedReform as \"RegistryLLO_IsNeedReform\",
                ReceptUploadLog_id as \"ReceptUploadLog_id\",
                Registry_Sum2 as \"Registry_Sum2\",
                Registry_SumPaid2 as \"Registry_SumPaid2\",
                DrugFinance_id as \"DrugFinance_id\",
                WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
                WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
                Org_id as \"Org_id\",
                WhsDocumentServices_id as \"WhsDocumentServices_id\",
                UslugaComplex_id as \"UslugaComplex_id\",
                UslugaComplexTariff_id as \"UslugaComplexTariff_id\"
			from
				{$this->schema}.v_RegistryLLO rllo
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
				rs.RegistryStatus_Code as \"RegistryStatus_Code\",
				rus.ReceptUploadStatus_Code as \"ReceptUploadStatus_Code\"
			from
				{$this->schema}.v_RegistryLLO rllo
				left join v_RegistryStatus rs  on rs.RegistryStatus_id = rllo.RegistryStatus_id
				left join v_ReceptUploadLog rul  on rul.ReceptUploadLog_id = rllo.ReceptUploadLog_id
				left join v_ReceptUploadStatus rus  on rus.ReceptUploadStatus_id = rul.ReceptUploadStatus_id
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
				rs.RegistryStatus_Code as \"RegistryStatus_Code\"
			from
				{$this->schema}.v_RegistryDataRecept rdr
				left join {$this->schema}.v_RegistryLLO rllo on rllo.RegistryLLO_id = rdr.RegistryLLO_id
				left join v_RegistryStatus rs  on rs.RegistryStatus_id = rllo.RegistryStatus_id
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
			$filters[] = "wds.WhsDocumentUc_Num ilike :WhsDocumentUc_Num";
			$params['WhsDocumentUc_Num'] = '%'.$data['WhsDocumentUc_Num'].'%';
		}
		if (!empty($data['Year']) && $data['Year'] > 0) {
			$filters[] = "date_part('year', rllo.RegistryLLO_accDate) = :Year";
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
            $filters[] = "(COALESCE(rllo.RegistryLLO_Sum, 0) + COALESCE(rllo.Registry_Sum2, 0)) >= :MinSum";
            $params['MinSum'] = $data['MinSum'];
        }
        if (!empty($data['MaxSum'])) {
            $filters[] = "(COALESCE(rllo.RegistryLLO_Sum, 0) + COALESCE(rllo.Registry_Sum2, 0))  <= :MaxSum";
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
				rllo.RegistryLLO_id as \"RegistryLLO_id\",
				rllo.RegistryLLO_Num as \"RegistryLLO_Num\",
				rllo.Org_id as \"Org_id\",
				o.Org_Name as \"Org_Name\",
				to_char(rllo.RegistryLLO_accDate, 'dd.mm.yyyy') as \"RegistryLLO_accDate\",
				COALESCE(to_char(rllo.RegistryLLO_begDate, 'dd.mm.yyyy'), '') || ' - ' || COALESCE(to_char( rllo.RegistryLLO_endDate, 'dd.mm.yyyy'),'') as \"RegistryLLO_Period\",
				kn.KatNasel_Name as \"KatNasel_Name\",
				df.DrugFinance_Name as \"DrugFinance_Name\",
				wdcit.WhsDocumentCostItemType_Name as \"WhsDocumentCostItemType_Name\",
				wds.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
				to_char(rllo.RegistryLLO_updDT, 'dd.mm.yyyy') as \"RegistryLLO_updDT\",
				rs.RegistryStatus_Code as \"RegistryStatus_Code\",
				rs.RegistryStatus_Name as \"RegistryStatus_Name\",
				rllo.RegistryLLO_Sum as \"RegistryLLO_Sum\",
				rllo.Registry_Sum2 as \"Registry_Sum2\",
				rdr.cnt as \"Registry_Count\",
				rllo.RegistryLLO_ErrorCount as \"Registry_ErrorCount\",
				(
				    COALESCE(fin_doc.FinDocument_Number, '') ||
				    COALESCE(' ' || to_char(fin_doc.FinDocument_Date, 'dd.mm.yyyy'), '')
				) as \"FinDocument_Data\",
                fin_doc.FinDocument_Sum as \"FinDocument_Sum\",
                fds_data.FinDocumentSpec_HtmlData as \"FinDocumentSpec_HtmlData\",
                fds_sum.FinDocumentSpec_HtmlSum as \"FinDocumentSpec_HtmlSum\",
				rllo.DrugFinance_id as \"DrugFinance_id\",
				rllo.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
				sup_c.Contragent_id as \"SupplierContragent_id\",
				fin_doc.FinDocument_id as \"FinDocument_id\",
				(
				    COALESCE(cast(rul.ReceptUploadLog_id as varchar), '') ||
				    COALESCE(' ' || to_char(rul.ReceptUploadLog_setDT, 'dd.mm.yyyy'), '')
				) as \"ReceptUploadLog_Data\",
                rus.ReceptUploadStatus_Code as \"ReceptUploadStatus_Code\",
                rus.ReceptUploadStatus_Name as \"ReceptUploadStatus_Name\",
                rul.ReceptUploadLog_Act as \"ReceptUploadLog_Act\"
				-- end select
			from
				-- from
				{$this->schema}.v_RegistryLLO rllo
				left join v_KatNasel kn  on kn.KatNasel_id = rllo.KatNasel_id
				left join v_DrugFinance df  on df.DrugFinance_id = rllo.DrugFinance_id
				left join v_WhsDocumentCostItemType wdcit  on wdcit.WhsDocumentCostItemType_id = rllo.WhsDocumentCostItemType_id
				left join v_WhsDocumentSupply wds  on wds.WhsDocumentSupply_id = rllo.WhsDocumentSupply_id
				left join v_RegistryStatus rs  on rs.RegistryStatus_id = rllo.RegistryStatus_id
				left join v_Org o  on o.Org_id = rllo.Org_id
				left join v_ReceptUploadLog rul  on rul.ReceptUploadLog_id = rllo.ReceptUploadLog_id
				left join v_ReceptUploadStatus rus  on rus.ReceptUploadStatus_id = rul.ReceptUploadStatus_id
				LEFT JOIN LATERAL (
					select
						count(i_rdr.RegistryDataRecept_id) as cnt
					from
						{$this->schema}.v_RegistryDataRecept i_rdr
					where
						i_rdr.Registry_id = rllo.RegistryLLO_id
				) rdr on true
				LEFT JOIN LATERAL (
                    select
                        Contragent_id
                    from
                        v_Contragent i_c
                    where
                        i_c.Org_id = wds.Org_sid
                    order by
                        i_c.Contragent_id
                    limit 1
                ) sup_c on true
                LEFT JOIN LATERAL (
                   select
                        i_rlfd.FinDocument_id,
                        i_fd.FinDocument_Number,
                        i_fd.FinDocument_Date,
                        i_fd.FinDocument_Sum
                   from
                        {$this->schema}.v_RegistryLLOFinDocument i_rlfd
                        left join {$this->schema}.v_FinDocument i_fd  on i_fd.FinDocument_id = i_rlfd.FinDocument_id
                   where
                        i_rlfd.RegistryLLO_id = rllo.RegistryLLO_id and
                        i_fd.FinDocumentType_id = :FinDocumentType1_id
                   order by
                        i_rlfd.FinDocument_id
                    limit 1
                ) fin_doc on true
                LEFT JOIN LATERAL (
                	select
						STRING_AGG(COALESCE(i_fd.FinDocument_Number,'') || COALESCE(' ' || to_char( i_fd.FinDocument_Date, 'dd.mm.yyyy'), ''),'<br/>' ) as FinDocumentSpec_HtmlData
					 from
						{$this->schema}.v_RegistryLLOFinDocument i_rlfd
						left join {$this->schema}.v_FinDocument i_fd  on i_fd.FinDocument_id = i_rlfd.FinDocument_id
					where
						i_rlfd.RegistryLLO_id = rllo.RegistryLLO_id and
						i_fd.FinDocumentType_id = :FinDocumentType2_id
                ) fds_data on true
                LEFT JOIN LATERAL (
                	select
						STRING_AGG(to_char(COALESCE(i_fd.FinDocument_Sum, 0),'99 999 999 999 999 990D99' ),'<br/>') as FinDocumentSpec_HtmlSum
					from
						{$this->schema}.v_RegistryLLOFinDocument i_rlfd
						left join {$this->schema}.v_FinDocument i_fd  on i_fd.FinDocument_id = i_rlfd.FinDocument_id
					where
						i_rlfd.RegistryLLO_id = rllo.RegistryLLO_id and
						i_fd.FinDocumentType_id = :FinDocumentType2_id
                ) fds_sum on true
				-- end from
			{$where}
			order by
				-- order by
				rllo.RegistryLLO_Num, rllo.RegistryLLO_id
				-- end order by
		;";


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
            $filters[] = "wds.WhsDocumentUc_Num ilike :WhsDocumentUc_Num";
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
                select
                    i_rlr.RegistryLLOError_id
                from
                    {$this->schema}.v_RegistryLLOError i_rlr
                where
                    i_rlr.RegistryLLO_id = rdr.RegistryLLO_id and
                    i_rlr.ReceptOtov_id = rdr.ReceptOtov_id and
                    i_rlr.RegistryReceptErrorType_id = :RegistryReceptErrorType_id
                limit 1
            )";
            $params['RegistryReceptErrorType_id'] = $data['RegistryReceptErrorType_id'];
        }

        if (!empty($data['Person_SurName'])) {
            $filters[] = "ps.Person_SurName ilike :Person_SurName";
            $params['Person_SurName'] = '%'.$data['Person_SurName'].'%';
        }

        if (!empty($data['Person_FirName'])) {
            $filters[] = "ps.Person_FirName ilike :Person_FirName";
            $params['Person_FirName'] = '%'.$data['Person_FirName'].'%';
        }

        if (!empty($data['Person_SecName'])) {
            $filters[] = "ps.Person_SecName ilike :Person_SecName";
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
            $filters[] = "mp.Person_Fio ilike :MedPersonal_Name";
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
            $filters[] = "dcm.DrugComplexMnn_RusName ilike :DrugComplexMnn_Name";
            $params['DrugComplexMnn_Name'] = '%'.$data['DrugComplexMnn_Name'].'%';
        }

        if (!empty($data['Drug_Name'])) {
            $filters[] = "d.Drug_Name ilike :Drug_Name";
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
				rdr.RegistryDataRecept_id as \"RegistryDataRecept_id\",
				ro.ReceptOtov_id as \"ReceptOtov_id\",
				ro.EvnRecept_id as \"EvnRecept_id\",
				COALESCE(is_received.YesNo_Code, 0) as \"IsReceived_Code\",
				ro.EvnRecept_Ser as \"EvnRecept_Ser\",
				ro.EvnRecept_Num as \"EvnRecept_Num\",
				rsfm.ReceptStatusFLKMEK_Name as \"ReceptStatusFLKMEK_Name\",
                --rdr.RegistryDataRecept_Sum as \"RegistryDataRecept_Sum\",
                --rdr.RegistryDataRecept_Sum2 as \"RegistryDataRecept_Sum2\",
                farm_org.Org_Name as \"FarmacyOrg_Name\",
                farm_org.Org_Code as \"FarmacyOrg_Code\",
                to_char(ro.EvnRecept_obrDate, 'dd.mm.yyyy') as \"EvnRecept_obrDate\",
                to_char(ro.EvnRecept_otpDate, 'dd.mm.yyyy') as \"EvnRecept_otpDate\",
                (case
                    when COALESCE(is_mnn.YesNo_Code, 0) > 0 then er_dcm_code.Code
                    else er_d_code.Code
                end) as \"LS_Code\",
                (case
                    when COALESCE(is_mnn.YesNo_Code, 0) > 0 then er_dcm.DrugComplexMnn_RusName
                    else er_d.Drug_Name
                end) as \"LS_Name\",
                ro.EvnRecept_Kolvo as \"EvnRecept_Kolvo\",
                doc_d.Drug_Name as \"Drug_Name\",
			    doc_ps.PrepSeries_Ser as \"PrepSeries_Ser\",
			    doc_cnt.DocumentUcStr_Count as \"DocumentUcStr_Count\",
			    doc_pr.DocumentUcStr_PriceR as \"DocumentUcStr_PriceR\",
			    doc_sum.DocumentUcStr_SumR as \"DocumentUcStr_SumR\",
                wds.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
                pt.PrivilegeType_Code as \"PrivilegeType_Code\",
                rd.ReceptDiscount_Name as \"PaymentPercent\",
                diag.Diag_Code as \"Diag_Code\",
                mp.Person_Fio as \"MedPersonal_Fio\",
                l.Lpu_Name as \"Lpu_Name\",
	            COALESCE(is_vk.YesNo_Name, 'Нет') as \"EvnRecept_isVK\",
                ps.Person_Snils as \"Person_Snils\",
                (
                    COALESCE(ps.Person_SurName, '') ||
                    COALESCE(' ' || ps.Person_FirName, '') ||
                    COALESCE(' ' || ps.Person_SecName, '')
                ) as \"Person_Fio\",
                to_char(ps.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\"
				-- end select
			from
				-- from
				{$this->schema}.v_RegistryDataRecept rdr
				left join {$this->schema}.ReceptStatusFLKMEK rsfm  on rsfm.ReceptStatusFLKMEK_id = rdr.ReceptStatusFLKMEK_id
				left join v_YesNo is_received  on is_received.YesNo_id = rdr.RegistryDataRecept_IsReceived
				left join ReceptOtov ro  on ro.ReceptOtov_id = rdr.receptfinance_id
				left join v_EvnRecept er  on er.EvnRecept_id = ro.EvnRecept_id
				left join v_ReceptDiscount rd  on rd.ReceptDiscount_id = er.ReceptDiscount_id
	            left join v_PersonState ps  on ps.Person_id = ro.Person_id
	            left join v_MedPersonal mp  on mp.MedPersonal_id = ro.MedPersonalRec_id
	            left join rls.v_Drug d  on d.Drug_id = ro.Drug_cid
	            left join rls.v_DrugComplexMnn dcm  on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
                left join v_PrivilegeType pt  on pt.PrivilegeType_id = ro.PrivilegeType_id
                left join v_Diag diag  on diag.Diag_id = ro.Diag_id
                left join v_Lpu l  on l.Lpu_id = ro.Lpu_id
                left join v_YesNo is_vk  on is_vk.YesNo_id = er.EvnRecept_IsKEK
	            LEFT JOIN LATERAL (
                    select
                        i_du.DocumentUc_id,
                        COALESCE(i_p_ds.WhsDocumentSupply_id, i_ds.WhsDocumentSupply_id) as WhsDocumentSupply_id,
                        i_du.Contragent_sid,
                        i_c_sid.Org_id as Org_sid,
                        i_dus.Drug_id,
			            i_dus.DocumentUcStr_Count,
			            i_dus.DocumentUcStr_PriceR,
			            i_dus.DocumentUcStr_SumR
                    from
                        v_DocumentUcStr i_dus
                        left join v_DocumentUc i_du  on i_du.DocumentUc_id = i_dus.DocumentUc_id
                        left join v_Contragent i_c_sid  on i_c_sid.Contragent_id = i_du.Contragent_sid
                        -- ищем приходную партию чтобы извлечь оттуда идентификатор контракта
                        left join v_DrugShipmentLink i_dsl  on i_dsl.DocumentUcStr_id = i_dus.DocumentUcStr_oid
                        left join v_DrugShipment i_ds  on i_ds.DrugShipment_id = i_dsl.DrugShipment_id
                        left join v_DrugShipment i_p_ds  on i_p_ds.DrugShipment_id = i_ds.DrugShipment_pid
                    where
                        i_dus.ReceptOtov_id = rdr.receptfinance_id
                    order by
                        i_p_ds.WhsDocumentSupply_id desc, i_ds.WhsDocumentSupply_id desc, i_dus.DocumentUcStr_id
                    limit 1
                ) doc on true
                LEFT JOIN LATERAL (
                	select
						STRING_AGG(COALESCE(i_d.Drug_Name, '&nbsp;'), '<br/>') as Drug_Name
					from
						v_DocumentUcStr i_dus
						left join rls.v_Drug i_d  on i_d.Drug_id = i_dus.Drug_id
					where
						i_dus.ReceptOtov_id = rdr.receptfinance_id
                ) doc_d on true
                LEFT JOIN LATERAL (
                	select
						STRING_AGG(COALESCE(i_ps.PrepSeries_Ser, '&nbsp;'),'<br/>') as PrepSeries_Ser
					from
						v_DocumentUcStr i_dus
						left join rls.v_PrepSeries i_ps  on i_ps.PrepSeries_id = i_dus.PrepSeries_id
					where
						i_dus.ReceptOtov_id = rdr.receptfinance_id
                ) doc_ps on true
                LEFT JOIN LATERAL (
                	select
						STRING_AGG(COALESCE(cast(i_dus.DocumentUcStr_Count as varchar),''),'<br/>') as DocumentUcStr_Count
					from
						v_DocumentUcStr i_dus
					where
						i_dus.ReceptOtov_id = rdr.receptfinance_id
                ) doc_cnt on true
                LEFT JOIN LATERAL (
                	select
						STRING_AGG(COALESCE(cast(i_dus.DocumentUcStr_PriceR as varchar),''),'<br/>') as DocumentUcStr_PriceR
					from
						v_DocumentUcStr i_dus
					where
						i_dus.ReceptOtov_id = rdr.receptfinance_id
                ) doc_pr on true
                LEFT JOIN LATERAL(
                	select
						STRING_AGG(COALESCE(cast(i_dus.DocumentUcStr_SumR as varchar),''),'<br/>') as DocumentUcStr_SumR
					from
						v_DocumentUcStr i_dus
					where
						i_dus.ReceptOtov_id = rdr.receptfinance_id
                ) doc_sum on true
                left join v_Org farm_org  on farm_org.Org_id = doc.Org_sid
                left join v_WhsDocumentSupply wds  on wds.WhsDocumentSupply_id = doc.WhsDocumentSupply_id
                LEFT JOIN LATERAL (
                    select
                        Contragent_id
                    from
                        v_Contragent i_c
                    where
                        i_c.Org_id = wds.Org_sid
                    order by
                        i_c.Contragent_id
                    limit 1
                ) sup_c on true
                left join rls.v_Drug er_d  on er_d.Drug_id = er.Drug_rlsid
	            left join rls.v_DrugComplexMnn er_dcm  on er_dcm.DrugComplexMnn_id = er.DrugComplexMnn_id
	            left join v_YesNo is_mnn  on is_mnn.YesNo_id = er.EvnRecept_IsMnn
               LEFT JOIN LATERAL (
                    select
                        i_dcm_code.DrugComplexMnnCode_Code as Code
                    from
                        rls.v_DrugComplexMnnCode i_dcm_code
                    where
                        i_dcm_code.DrugComplexMnn_id = er.DrugComplexMnn_id
                    order by
                        i_dcm_code.DrugComplexMnnCode_id
                    limit 1
                ) as er_dcm_code on true
                LEFT JOIN LATERAL (
                    select
                        i_d_code.DrugNomen_Code as Code
                    from
                        rls.v_DrugNomen i_d_code
                    where
                        i_d_code.Drug_id = er.Drug_rlsid
                    order by
                        i_d_code.DrugNomen_id
                    limit 1
                ) as er_d_code on true
				-- end from
			{$where}
			order by
				-- order by
				rdr.RegistryDataRecept_id
				-- end order by
		;";


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
                rle.RegistryLLOError_id as \"RegistryLLOError_id\",
                to_char(rle.RegistryLLOError_insDT, 'dd.mm.yyyy') as \"RegistryLLOError_insDT\",
                rret.RegistryReceptErrorType_Type as \"RegistryReceptErrorType_Type\",
                rret.RegistryReceptErrorType_Name as \"RegistryReceptErrorType_Name\",
                rret.RegistryReceptErrorType_Descr as \"RegistryReceptErrorType_Descr\"
            from
                {$this->schema}.v_RegistryLLOError rle
                left join {$this->schema}.v_RegistryReceptErrorType rret  on rret.RegistryReceptErrorType_id = rle.RegistryReceptErrorType_id
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
	public function getStoredProcedureParamsList($sp, $schema)
	{
		$query = "
                select 
                  pg_catalog.pg_get_function_arguments(p.oid) as \"arguments\"
                from 
                  pg_catalog.pg_proc p
                  left join pg_catalog.pg_namespace n on n.oid = p.pronamespace
                where 
                  n.nspname = :scheme
                and 
                  p.proname = :proc
                limit 1
	      
	    ";
		$result = $this->db->query($query, array(
			'proc' => strtolower($sp),
			'scheme' => strtolower($schema)
		));

		if ( is_object($result) ) {
			$arguments = explode( ', ',$result->result('array')[0]['arguments']);
			$params = [];
			foreach ($arguments as $argument) {
				$argument = explode(' ', $argument);
				if($argument[0] === 'OUT') continue;
				if($argument[0] === 'INOUT') {
					$params[] = $argument[1];
					continue;
				}

				$params[] = $argument[0];
			}

			return $params;
		} else {
			return false;
		}
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
					{$schema}.{$object_name} 
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
				$query_part .= "{$key} := :{$key}, ";
			}
		}

		$save_data['pmUser_id'] = isset($data['pmUser_id']) ? $data['pmUser_id'] : null;


        $query = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Msg\",
            {$key_field} as \"{$key_field}\"
        from {$schema}.{$proc_name}
            (
            {$query_part}
            pmUser_id := :pmUser_id
             )";



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
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Message\"
        from {$schema}.p_{$object_name}_del
            (
                {$object_name}_id := :{$object_name}_id
            )";


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
			select
				{$object_name}_id as \"{$object_name}_id\"
			from
				{$schema}.{$object_name} 
			where
				{$object_name}_Code = :code;
            limit 1
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
				max(cast({$num_field} as int))+1 as \"num\"
			from
				{$object_name}
			where
				len({$num_field}) <= 6 and
				Coalesce((
					Select Case When position('.' in {$num_field}) > 0 Then 0 Else 1 End
					Where IsNumeric({$num_field}) = 1
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
				KatNasel_id as \"KatNasel_id\",
                DrugFinance_id as \"DrugFinance_id\",
                WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
                WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
                RegistryType_id as \"RegistryType_id\",
                RegistryLLO_begDate as \"RegistryLLO_begDate\",
                RegistryLLO_endDate as \"RegistryLLO_endDate\",
                RegistryLLO_id as \"RegistryLLO_id\"
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
							v_WhsDocumentSupply i_wds
							inner join v_WhsDocumentTitle i_wdt  on i_wdt.WhsDocumentUc_id = i_wds.WhsDocumentUc_id
							inner join v_WhsDocumentRightRecipient i_wdrr  on i_wdrr.WhsDocumentTitle_id = i_wdt.WhsDocumentTitle_id
							inner join OrgFarmacy i_of  on i_of.Org_id = i_wdrr.Org_id
						where
							i_wds.Org_sid = :Org_sid
					) and
				";
			}

			$query = "

                wuth cte as(
                            select
                                (select ReceptDelayType_id from v_ReceptDelayType  where ReceptDelayType_Code = 0) as \"ReceptDelayType_id\", -- 0 - Обслужен
                                (select ReceptStatusType_id from v_ReceptStatusType  where ReceptStatusType_Code = 5) as \"ReceptStatusType_id\", -- 5 - Исключен из реестра
                                (select KatNasel_Code from v_KatNasel  where KatNasel_id = :KatNasel_id) as \"KatNasel_Code\", -- Коды: 1 - Жители области; 2 - Иногородние.
                                dbo.GetRegion() as \"Region_id\",
                                (select WhsDocumentUc_id from v_WhsDocumentSupply  where WhsDocumentSupply_id = :WhsDocumentSupply_id) as \"WhsDocumentUc_id\"
                    )
				select
					ro.ReceptOtov_id as \"ReceptOtov_id\",
					ro.EvnRecept_Ser as \"EvnRecept_Ser\",
					ro.EvnRecept_Num as \"EvnRecept_Num\",
					cast((ro.EvnRecept_Price * ro.EvnRecept_Kolvo) as decimal(10,2)) as \"Recept_Sum\",
					ro.EvnRecept_otpDate as \"EvnRecept_otpDate\",
					ro.OrgFarmacy_id as \"OrgFarmacy_id\",
					er.WhsDocumentUc_id as \"WhsDocumentUc_id\"
				from
					ReceptOtov ro
					left join v_EvnRecept er  on er.EvnRecept_id = ro.EvnRecept_id
					left join v_PersonState ps  on ps.Person_id = ro.Person_id
					left join v_Address ua  on Address_id = ps.UAddress_id
				where
					ro.ReceptDelayType_id = (select ReceptDelayType_id from cte) and
					(ro.ReceptStatusType_id is null or ro.ReceptStatusType_id = (select ReceptStatusType_id from cte)) and
					ro.DrugFinance_id = :DrugFinance_id and
					ro.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id and
					{$of_subquery}
					(
						((select KatNasel_Code from cte) = 1 and ua.KLRgn_id = (select Region_id from cte)) or
						((select KatNasel_Code from cte) = 2 and ua.KLRgn_id <> (select Region_id from cte)) or
						(select KatNasel_Code from cte) = 3
					) and
					(
						(select WhsDocumentUc_id from cte) is null or
						er.WhsDocumentUc_id is null or
						er.WhsDocumentUc_id = (select WhsDocumentUc_id from cte)
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
					select
						uct.UslugaComplexTariff_Tariff as \"Tariff\"
					from
						v_WhsDocumentTitle wdt
						inner join v_WhsDocumentTitleTariff wdtt  on wdtt.WhsDocumentTitle_id = wdt.WhsDocumentTitle_id
						left join v_UslugaComplexTariff uct  on uct.UslugaComplexTariff_id = wdtt.UslugaComplexTariff_id
					where
						wdt.WhsDocumentUc_id = :WhsDocumentUc_id and
						(wdt.WhsDocumentTitle_begDate is null or wdt.WhsDocumentTitle_begDate <= :EvnRecept_otpDate) and
						(wdt.WhsDocumentTitle_endDate is null or wdt.WhsDocumentTitle_endDate >= :EvnRecept_otpDate);
                    limit 1
				";
				$service_tariff = $this->getFirstResultFromQuery($query, array(
					'EvnRecept_otpDate' => $recept['EvnRecept_otpDate'],
					'WhsDocumentUc_id' => $recept['WhsDocumentUc_id']
				));

				if (empty($service_tariff)) { //вторая попытка вычислить тариф уже без привязки к контракту
					$query = "
	                   with cte as (
                            (select  Org_id from v_OrgFarmacy  where OrgFarmacy_id = :OrgFarmacy_id limit 1) as \"Org_id\"
                        )
						select
							uct.UslugaComplexTariff_Tariff as \"Tariff\"
						from
							v_WhsDocumentTitle wdt
							inner join v_WhsDocumentTitleTariff wdtt  on wdtt.WhsDocumentTitle_id = wdt.WhsDocumentTitle_id
							inner join v_WhsDocumentRightRecipient wdrr  on wdrr.WhsDocumentTitle_id = wdt.WhsDocumentTitle_id and wdrr.Org_id = ( select Org_id from cte)
							left join v_UslugaComplexTariff uct  on uct.UslugaComplexTariff_id = wdtt.UslugaComplexTariff_id
						where
							(wdt.WhsDocumentTitle_begDate is null or wdt.WhsDocumentTitle_begDate <= :EvnRecept_otpDate) and
							(wdt.WhsDocumentTitle_endDate is null or wdt.WhsDocumentTitle_endDate >= :EvnRecept_otpDate)
						order by
							wdtt.WhsDocumentTitleTariff_id;
                        limit 1
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
			select 
				rllo.RegistryLLO_id as \"RegistryLLO_id\",
				AcceptRecept.AcceptRecept_Count as \"AcceptRecept_Count\",
				rllo.RegistryLLO_ErrorCount as \"RegistryLLO_ErrorCount\",
				rul.ReceptUploadStatus_id as \"ReceptUploadStatus_id\"
			from
				{$this->schema}.v_RegistryLLO rllo 
				left join v_ReceptUploadLog rul  on rul.ReceptUploadLog_id = rllo.ReceptUploadLog_id
				LEFT JOIN LATERAL(
					select 
						count(rdr.RegistryDataRecept_id) as AcceptRecept_Count
					from
						{$this->schema}.RegistryDataRecept rdr 
						left join {$this->schema}.ReceptStatusFLKMEK rsf  on rsf.ReceptStatusFLKMEK_id = rdr.ReceptStatusFLKMEK_id
					where
						rdr.RegistryLLO_id = rllo.RegistryLLO_id
						and coalesce(rsf.ReceptStatusFLKMEK_Code,0) in (3,5,6)
                    limit 1
				) AcceptRecept on true
			where
				rllo.RegistryLLO_id = :RegistryLLO_id
            limit 1
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Ручное изменение статуса экспертизы
	 */
	function saveRegistryLLOExpertise($data) {
		$ReceptUploadLog = $this->getFirstRowFromQuery("
			select
				rul.ReceptUploadLog_id as \"ReceptUploadLog_id\",
				rul.ReceptUploadStatus_id as \"ReceptUploadStatus_id\"
			from {$this->schema}.v_RegistryLLO rllo 
			inner join v_ReceptUploadLog rul  on rul.ReceptUploadLog_id = rllo.ReceptUploadLog_id
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
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Msg\",
            RegistryLLOError_id as \"RegistryLLOError_id\"
        from {$this->schema}.{$procedure}
            (
 			    RegistryLLOError_id := :RegistryLLOError_id,
				RegistryLLO_id := :RegistryLLO_id,
				ReceptOtov_id := :ReceptOtov_id,
				RegistryReceptErrorType_id := :RegistryReceptErrorType_id,
				pmUser_id := :pmUser_id
            )";


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
				rllo.RegistryLLO_id as \"RegistryLLO_id\",
				rllo.ReceptUploadLog_id as \"ReceptUploadLog_id\",
				rdr.RegistryDataRecept_id as \"RegistryDataRecept_id\",
				ro.ReceptOtov_id as \"ReceptOtov_id\",
				coalesce(ro.ReceptOtov_IsKek,1) as \"ReceptOtov_IsKek\",
				d.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				dnls.DrugNormativeList_id as \"DrugNormativeList_id\"
			from
				{$this->schema}.v_RegistryDataRecept rdr 
				inner join {$this->schema}.v_RegistryLLO rllo  on rllo.RegistryLLO_id = rdr.RegistryLLO_id
				inner join ReceptOtov ro  on ro.ReceptOtov_id = rdr.ReceptOtov_id
				left join rls.v_Drug d  on d.Drug_id = ro.Drug_cid
				left join rls.v_DrugComplexMnn dcm  on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnName dcmn  on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				LEFT JOIN LATERAL(
					select 
						dnls.DrugNormativeList_id
					from
						v_DrugNormativeListSpec dnls 
						left join v_DrugNormativeListSpecTorgLink dnlstl  on dnlstl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
						left join v_DrugNormativeListSpecFormsLink dnlsfl  on dnlsfl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
					where
						dnls.DrugNormativeListSpecMNN_id = dcmn.ACTMATTERS_id
						and (dnlstl.DrugNormativeListSpecTorg_id is null or dnlstl.DrugNormativeListSpecTorg_id = 15875)
						and (dnlsfl.DrugNormativeListSpecForms_id is null or dnlsfl.DrugNormativeListSpecForms_id = 694)
						and cast(ro.EvnRecept_setDT as date) between dnls.DrugNormativeListSpec_BegDT and coalesce(dnls.DrugNormativeListSpec_EndDT,cast(ro.EvnRecept_setDT as date))
						and coalesce(dnls.DrugNormativeListSpec_IsVK,1) = 1
                    limit 1
				) dnls on true
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
				delete {$this->schema}.RegistryLLOError 
				from {$this->schema}.RegistryLLOError rlloe
				left join {$this->schema}.RegistryReceptErrorType rret  on rret.RegistryReceptErrorType_id = rlloe.RegistryReceptErrorType_id
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
				select distinct rret.RegistryReceptErrorType_Type as \"RegistryReceptErrorType_Type\"
				from {$this->schema}.RegistryLLOError rlloe 
				inner join {$this->schema}.v_RegistryReceptErrorType rret  on rret.RegistryReceptErrorType_id = rlloe.RegistryReceptErrorType_id
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
				rsf.ReceptStatusFLKMEK_Code as \"ReceptStatusFLKMEK_Code\"
			from
				{$this->schema}.v_RegistryDataRecept rdr 
				left join {$this->schema}.ReceptStatusFLKMEK rsf  on rsf.ReceptStatusFLKMEK_id = rdr.ReceptStatusFLKMEK_id
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
			select
				rul.ReceptUploadLog_id as \"ReceptUploadLog_id\",
				to_char( dbo.tzGetDate(), 'dd.mm.yyyy') || ' ' || to_char(dbo.tzGetDate(), 'hh24:mm') as \"ActDateTime\",
				o.Org_Name as \"Org_Name\",
				rllo.RegistryLLO_Num as \"RegistryLLO_Num\",
				to_char(RegistryLLO_begDate, 'dd.mm.yyyy') as \"RegistryLLO_begDate\",
				to_char(RegistryLLO_endDate, 'dd.mm.yyyy') as \"RegistryLLO_endDate\",
				kn.KatNasel_Name as \"KatNasel_Name\",
				wdcit.WhsDocumentCostItemType_Name as \"WhsDocumentCostItemType_Name\",
				df.DrugFinance_Name as \"DrugFinance_Name\",
				rllo.RegistryLLO_RecordCount as \"RegistryLLO_RecordCount\",
				cast(rllo.RegistryLLO_Sum as Numeric(16,2)) as \"RegistryLLO_Sum\",
				rus.ReceptUploadStatus_Name as \"ReceptUploadStatus_Name\",
				rllo.RegistryLLO_ErrorCount as \"RegistryLLO_ErrorCount\",
				AcceptRecept.AcceptRecept_Count as \"AcceptRecept_Count\",
				AcceptRecept.AcceptRecept_Sum as \"AcceptRecept_Sum\",
				RefuseRecept.RefuseRecept_Count as \"RefuseRecept_Count\",
				RefuseRecept.RefuseRecept_Sum as \"RefuseRecept_Sum\"
			from
				{$this->schema}.v_RegistryLLO rllo 
				inner join v_ReceptUploadLog rul  on rul.ReceptUploadLog_id = rllo.ReceptUploadLog_id
				left join v_ReceptUploadStatus rus  on rus.ReceptUploadStatus_id = rul.ReceptUploadStatus_id
				left join v_Org o  on o.Org_id = rllo.Org_id
				left join v_KatNasel kn  on kn.KatNasel_id = rllo.KatNasel_id
				left join v_WhsDocumentCostItemType wdcit  on wdcit.WhsDocumentCostItemType_id = rllo.WhsDocumentCostItemType_id
				left join v_DrugFinance df  on df.DrugFinance_id = rllo.DrugFinance_id
				LEFT JOIN LATERAL(
					select 
						count(rdr.RegistryDataRecept_id) as AcceptRecept_Count,
						coalesce(sum(rdr.RegistryDataRecept_Sum),0) as AcceptRecept_Sum
					from
						{$this->schema}.RegistryDataRecept rdr 
						left join {$this->schema}.ReceptStatusFLKMEK rsf  on rsf.ReceptStatusFLKMEK_id = rdr.ReceptStatusFLKMEK_id
					where
						rdr.RegistryLLO_id = rllo.RegistryLLO_id
						and coalesce(rsf.ReceptStatusFLKMEK_Code,0) in (3,5,6)
                    limit 1
				) AcceptRecept on true
				LEFT JOIN LATERAL (
					select 
						count(rdr.RegistryDataRecept_id) as RefuseRecept_Count,
						coalesce(sum(rdr.RegistryDataRecept_Sum),0) as RefuseRecept_Sum
					from
						{$this->schema}.RegistryDataRecept rdr 
						left join {$this->schema}.ReceptStatusFLKMEK rsf  on rsf.ReceptStatusFLKMEK_id = rdr.ReceptStatusFLKMEK_id
					where
						rdr.RegistryLLO_id = rllo.RegistryLLO_id
						and coalesce(rsf.ReceptStatusFLKMEK_Code,0) not in (3,5,6)
                    limit 1
				) RefuseRecept on true
			where
				rllo.RegistryLLO_id = :RegistryLLO_id
		";
		$act_data = $this->getFirstRowFromQuery($query, $params);
		if (!is_array($act_data)) {
			return $this->createError('','Ошибка при получении данных реестра рецептов');
		}

		$query = "
			select
				ro.EvnRecept_Ser as \"EvnRecept_Ser\",
				ro.EvnRecept_Num as \"EvnRecept_Num\",
				rret.RegistryReceptErrorType_Type as \"RegistryReceptErrorType_Type\",
				rret.RegistryReceptErrorType_Name as \"RegistryReceptErrorType_Name\"
			from
				{$this->schema}.v_RegistryLLOError rlloe 
				left join ReceptOtov ro  on ro.ReceptOtov_id = rlloe.ReceptOtov_id
				left join {$this->schema}.v_RegistryReceptErrorType rret  on rret.RegistryReceptErrorType_id = rlloe.RegistryReceptErrorType_id
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
				sum(coalesce(RegistryDataRecept_Sum, 0)) as \"RegistryDataRecept_Sum\",
				sum(coalesce(RegistryDataRecept_Sum2, 0)) as \"RegistryDataRecept_Sum2\"
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
			select
				dbo.tzGetDate() as \"ReceptUploadLog_setDT\",
                (select ReceptUploadDeliveryType_id from v_ReceptUploadDeliveryType  where ReceptUploadDeliveryType_Code = :ReceptUploadDeliveryType_Code) as \"ReceptUploadDeliveryType_id\",
                (select ReceptUploadType_id from v_ReceptUploadType  where ReceptUploadType_Code = :ReceptUploadType_Code) as \"ReceptUploadType_id\",
                (select ReceptUploadStatus_id from v_ReceptUploadStatus  where ReceptUploadStatus_Code = :ReceptUploadStatus_Code) as \"ReceptUploadStatus_id\"
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
						ReceptUploadLog_id as \"ReceptUploadLog_id\"
					from
						{$this->schema}.RegistryLLO 
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
						ReceptUploadLog_id as \"ReceptUploadLog_id\"
					from
						{$this->schema}.RegistryLLO 
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
                count(rle.RegistryLLOError_id) as \"cnt\"
            from
                {$this->schema}.v_RegistryDataRecept rdr 
                inner join {$this->schema}.v_RegistryLLOError rle  on rle.RegistryLLO_id = rdr.RegistryLLO_id and rle.ReceptOtov_id = rdr.ReceptOtov_id
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
            $filters[] = "Lpu_Name ilike :Lpu_Name";
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
				Lpu_id as \"Lpu_id\",
				Lpu_Name as \"Lpu_Name\"
			from
				v_Lpu 
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
				ReceptStatusFLKMEK_id as \"ReceptStatusFLKMEK_id\",
				ReceptStatusFLKMEK_Code as \"ReceptStatusFLKMEK_Code\",
				ReceptStatusFLKMEK_Name as \"ReceptStatusFLKMEK_Name\"
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
				RegistryReceptErrorType_id as \"RegistryReceptErrorType_id\",
				RegistryReceptErrorType_Type as \"RegistryReceptErrorType_Type\",
				RegistryReceptErrorType_Name as \"RegistryReceptErrorType_Name\"
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
