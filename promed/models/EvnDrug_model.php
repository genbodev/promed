<?php

defined('BASEPATH') or die('No direct script access allowed');

/**
 * EvnDrug_model - модель для работы с персучетом
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright		Copyright (c) 2009 Swan Ltd.
 * @author				Марков Андрей
 * @version			18.04.2010
 * @property EvnPrescr_model $EvnPrescr_model
 * @property EvnPrescrTreat_model $EvnPrescrTreat_model
 */
class EvnDrug_model extends swModel {

	/**
	 * Rjycnhernjh
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Загрузка данных в раздел "Использование медикаментов" в ЭМК
	 * @param array $data
	 * @return array|bool
	 */
	function loadEvnDrugViewData($data) {
		$query = "
			select
				ED.EvnDrug_id,
				ED.EvnDrug_pid,
				ED.Drug_id,
				Drug.Drug_Code,
				Drug.DrugTorg_Name as Drug_Name,
				convert(varchar(10), ED.EvnDrug_setDate, 104) as EvnDrug_setDate,
				cast(ROUND(ED.EvnDrug_Kolvo, 4) as numeric (20,4)) as EvnDrug_Kolvo
			FROM v_EvnDrug ED with (nolock)
				inner join rls.v_Drug Drug with (nolock) on Drug.Drug_id = ED.Drug_id
			WHERE ED.EvnDrug_pid = :EvnDrug_pid
			ORDER BY
				ED.Drug_id, ED.EvnDrug_setDate
		";
		$result = $this->db->query($query, array('EvnDrug_pid' => $data['EvnDrug_pid']));

		if (!is_object($result)) {
			return false;
		}
		$tmp = $result->result('array');
		$response = array();
		$indexGr = null;
		$lastIndex = -1;
		$lastDrug_id = null;
		// Нужно группировать по медикаменту с указанием общего списанного количества
		foreach ($tmp as $row) {
			if ($lastDrug_id != $row['Drug_id']) {
				$indexGr = count($response);
				$lastDrug_id = $row['Drug_id'];
				if ($lastIndex > 0) {
					$response[$lastIndex]['addHtml'] = '</table>';
				}
			}
			if (empty($response[$indexGr])) {
				$response[$indexGr] = array(
					'EvnDrug_id' => $row['EvnDrug_id'],
					'Drug_Code' => $row['Drug_Code'],
					'Drug_Name' => $row['Drug_Name'],
					'EvnDrug_setDate' => null,
					'EvnDrug_Kolvo' => 0,
					'IsGroup' => 1,
				);
				$lastIndex++;
			}
			$response[$indexGr]['EvnDrug_Kolvo'] += $row['EvnDrug_Kolvo'];
			$response[] = array(
				'EvnDrug_id' => $row['EvnDrug_id'],
				'EvnDrug_pid' => $row['EvnDrug_pid'],
				'EvnDrug_setDate' => $row['EvnDrug_setDate'],
				'EvnDrug_Kolvo' => $row['EvnDrug_Kolvo']+0,
				'IsGroup' => 0,
				'addHtml' => '',
			);
			$lastIndex++;
		}
		if ($lastIndex > 0) {
			$response[$lastIndex]['addHtml'] = '</table>';
		}
		return $response;
	}

	/**
	 * Отмена списания
	 */
	function deleteEvnDrug($data) {
		$response = array(array(
			'Error_Msg' => null,
			'Error_Code' => null,
			'EvnPrescrTreatDrug_FactCount' => null,
		));
		$formParams = $this->loadEvnDrugEditForm($data);
		if (empty($formParams)) {
			$response[0]['Error_Msg'] = 'Случай использования медикаментов не найден!';
			return $response;
		}
		if (!empty($formParams['Error_Msg'])) {
			$response[0]['Error_Msg'] = $formParams['Error_Msg'];
			return $response;
		}

        // Если учет ведется в АРМ товароведа, необходимо проверить возможность редактирования документа учета
        $this->load->helper("Options");
        $this->load->model("Options_model", "Options_model");
        $options = $this->Options_model->getOptionsAll($data);

        if (!empty($options['drugcontrol']['drugcontrol_module']) && $options['drugcontrol']['drugcontrol_module'] == 2) {
            $dus_id = $this->getExecutedDocumentUcStrForEvnDrug($data['EvnDrug_id']);
            if (!empty($dus_id)) {
                return array(array('Error_Msg' => 'Удаление медикамента невозможно, т.к. медикаменты уже списаны со склада'));
            }
        }

		// Обработка резерва
		$query = "
            Select DocumentUcStr_id from DocumentUcStr with (nolock) where EvnDrug_id = :EvnDrug_id
        ";
        $DocumentUcStr_id = $this->getFirstResultFromQuery($query, array('EvnDrug_id' => $data['EvnDrug_id']));
		
		if($DocumentUcStr_id && in_array($_SESSION['region']['nick'], array('perm','penza','krym','astra'))){
			$this->load->model('VaccineCtrl_model', 'VaccineCtrl_model');
			$result = $this->VaccineCtrl_model->deleteDocumentUcStr(array(
				'DocumentUcStr_id' => $DocumentUcStr_id,
				'EvnDrug_id' => $data['EvnDrug_id']
			));
		}
		
		$this->load->model('DocumentUc_model', 'DocumentUc_model');
		$result = $this->DocumentUc_model->removeReserve(array(
			'DocumentUcStr_id' => $DocumentUcStr_id,
			'pmUser_id' => $data['pmUser_id']
		));
		if (!empty($result['Error_Msg'])) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (Обработка резерва)'));
		}


		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnDrug_del
				@EvnDrug_id = :EvnDrug_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'EvnDrug_id' => $data['EvnDrug_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление случая использования медикаментов)'));
		}

		$tmp = $result->result('array');
		if (!empty($tmp[0]['Error_Msg'])) {
			$response[0]['Error_Msg'] = $tmp[0]['Error_Msg'];
			$response[0]['Error_Code'] = $tmp[0]['Error_Code'];
			return $response;
		}
		if (!empty($formParams[0]['EvnPrescr_id'])) {
			//если случай списания ассоциирован с назначением, то надо обновить факт.выполнение
			$isAllowUpdateFactData = true;
			// нужно убрать отметку о выполнении, если назначение выполнено EvnPrescrTreat_isExec
			if (!empty($formParams[0]['EvnPrescrTreat_isExec']) && 2==$formParams[0]['EvnPrescrTreat_isExec']) {
				$this->load->model('EvnPrescr_model', 'EvnPrescr_model');
				$tmp = $this->EvnPrescr_model->rollbackEvnPrescrExecution(array(
					'EvnPrescr_id' => $formParams[0]['EvnPrescr_id'],
					'pmUser_id' => $data['pmUser_id'],
				));
				if (!empty($tmp[0]['Error_Msg'])) {
					//так-то нужно откатить транзакцию
					$response[0]['Alert_Msg'] = $tmp[0]['Error_Msg'];
					$isAllowUpdateFactData = false;
				}
			}
			if ($isAllowUpdateFactData) {
				/**
				нужно обновить поля:
				EvnPrescrTreatDrug_FactCount, -- Количество выполненных приемов
				EvnCourseTreatDrug_FactCount -- Количество выполненных приемов в курсе лечения
				EvnCourseTreatDrug_FactDose -- Фактическая курсовая доза в курсе лечения
				 */
				$this->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');
				try {
					$tmp = $this->EvnPrescrTreat_model->updateFactData(array(
						'EvnPrescrTreat_id' => $formParams[0]['EvnPrescr_id'],
						'EvnPrescrTreatDrug_id' => $formParams[0]['EvnPrescrTreatDrug_id'],
						'EvnPrescrTreat_Fact' => (empty($data['EvnPrescrTreat_Fact']))?1:$data['EvnPrescrTreat_Fact'],
						'EvnCourseTreat_id' => $formParams[0]['EvnCourse_id'],
						'EvnCourseTreatDrug_id' => $formParams[0]['EvnCourseTreatDrug_id'],
						'pmUser_id' => $data['pmUser_id'],
					), 'cancel');
					$response[0]['EvnPrescrTreatDrug_FactCount'] = $tmp['EvnPrescrTreatDrug_FactCount'];
					$response[0]['EvnPrescrTreat_PrescrCount'] = $tmp['EvnPrescrTreat_PrescrCount'];
					$response[0]['epFactCount'] = $tmp['epFactCount'];
					$response[0]['cntEvnPrescrTreatDrug'] = $tmp['cntEvnPrescrTreatDrug'];
				} catch (Exception $e) {
					//так-то нужно откатить транзакцию
					$response[0]['Alert_Msg'] = $e->getMessage();
				}
			}
		}
		return $response;
	}

	/**
	 * Получение данных для формы редактирования случая использования медикаментов
	 * @param array $data
	 * @return array
	 */
	function loadEvnDrugEditForm($data) {
		$access_type = '
			case
				when ED.Lpu_id = :Lpu_id then 1
				' . (count($data['session']['linkedLpuIdList']) > 1 ? 'when ED.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and ISNULL(ED.EvnDrug_IsTransit, 1) = 2 then 1' : '') . '
				else 0
			end = 1
		';

		$query = "
			select top 1
				case when {$access_type} then 'edit' else 'view' end as accessType,
				ISNULL(ED.EvnDrug_id, 0) as EvnDrug_id,
				ISNULL(ED.EvnDrug_pid, 0) as EvnDrug_pid,
				convert(varchar(10), ED.EvnDrug_setDate, 104) as EvnDrug_setDate,
				convert(varchar(10), ED.EvnDrug_didDate, 104) as EvnDrug_didDate,
				ISNULL(ED.Person_id, 0) as Person_id,
				ISNULL(ED.PersonEvn_id, 0) as PersonEvn_id,
				ISNULL(ED.Server_id, -1) as Server_id,
				ED.Drug_id,
				Drug.DrugPrepFas_id,
				ED.EvnPrescr_id,
				EPT.EvnPrescrTreat_isExec,
				ED.EvnCourse_id,
				EPTD.EvnPrescrTreatDrug_id,
				EPTD.EvnCourseTreatDrug_id,
				EPT.EvnPrescrTreat_PrescrCount - isnull(EPTD.EvnPrescrTreatDrug_FactCount,0) as EvnPrescrTreat_Fact,
				EPT.EvnPrescrTreat_PrescrCount - isnull(EPTD.EvnPrescrTreatDrug_FactCount,0) as PrescrFactCountDiff,
				ED.LpuSection_id,
				ED.EvnDrug_Price,
				ED.EvnDrug_Sum,
				ED.DocumentUc_id,
				ED.DocumentUcStr_id,
				ED.DocumentUcStr_oid,
				ED.Storage_id,
				ED.Mol_id,
				ED.EvnDrug_Kolvo,
				ED.EvnDrug_KolvoEd,
				ED.EvnDrug_RealKolvo,
				DuS.GoodsUnit_id
			from v_EvnDrug ED with (nolock)
				left join rls.v_Drug Drug with (nolock) on Drug.Drug_id = ED.Drug_id
				left join v_EvnPrescrTreat EPT with (nolock) on ED.EvnPrescr_id = EPT.EvnPrescrTreat_id
				left join v_EvnPrescrTreatDrug EPTD with (nolock) on ED.EvnPrescrTreatDrug_id = EPTD.EvnPrescrTreatDrug_id
				left join v_DocumentUcStr DuS with (nolock) on DuS.DocumentUcStr_id = ED.DocumentUcStr_id
			where ED.EvnDrug_id = :EvnDrug_id
				and ED.Lpu_id = :Lpu_id
		";

		$queryParams = array(
			'EvnDrug_id' => $data['EvnDrug_id'],
			'Lpu_id' => $data['Lpu_id']
		);
		//echo getDebugSQL($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array('Error_Msg' => 'Ошибка при получении данных для формы редактирования случая использования медикаментов', 'success' => false);
		}
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function loadEvnDrugGrid($data) {
		$query = "
			select
				ED.EvnDrug_id,
				Drug.Drug_Code,
				Drug.DrugTorg_Name as Drug_Name,
				convert(varchar(10), ED.EvnDrug_setDate, 104) as EvnDrug_setDate,
				cast(ROUND(ED.EvnDrug_Kolvo, 4) as numeric (20,4)) as EvnDrug_Kolvo
			FROM v_EvnDrug ED with (nolock)
				inner join rls.v_Drug Drug with (nolock) on Drug.Drug_id = ED.Drug_id
			WHERE ED.EvnDrug_rid = :EvnDrug_pid or ED.EvnDrug_pid = :EvnDrug_pid
		";
		//echo getDebugSQL($query, array('EvnDrug_pid' => $data['EvnDrug_pid']));
		$result = $this->db->query($query, array('EvnDrug_pid' => $data['EvnDrug_pid']));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function getMolCombo($data) {
		$params = array();
		$params['Lpu_id'] = $data['Lpu_id'];
		$filter = "";

		if (isset($data['LpuSection_id']) || isset($data['Storage_id'])) {
			if (isset($data['LpuSection_id'])) {
				// Для отделения мол берется со склада
				$filter .= " and S.Storage_id in (select Storage_id from v_StorageStructLevel with(nolock) where LpuSection_id = :LpuSection_id)";
				$params['LpuSection_id'] = $data['LpuSection_id'];
			}
			if (isset($data['Storage_id'])) {
				$filter .= " and S.Storage_id = :Storage_id";
				$params['Storage_id'] = $data['Storage_id'];
			}
		} else if (isset($data['Contragent_id'])) {
			$filter .= " and Mol.Contragent_id = :Contragent_id";
			$params['Contragent_id'] = $data['Contragent_id'];
		}

		if (empty($data['LpuSection_id']) && empty($data['Storage_id'])) {
			if(isset($data['Org_id']) && !empty($data['Org_id'])){
				$filter .= " and (Mol.Lpu_id = :Lpu_id or ssl.Org_id = :Org_id)";
				$params['Org_id'] = $data['Org_id'];
			} else {
				$filter .= " and Mol.Lpu_id = :Lpu_id";
			}
		}
		if (isset($data['MedService_id']) && !empty($data['MedService_id'])) {
			$filter .= " and S.Storage_id in (select Storage_id from v_StorageStructLevel with(nolock) where MedService_id = :MedService_id)";
			$params['MedService_id'] = $data['MedService_id'];
		}
		if (!empty($data['onDate'])) {
			$filter .= " and (Mol.Mol_begDT is null or Mol.Mol_begDT <= :onDate)";
			$filter .= " and (Mol.Mol_endDT is null or Mol.Mol_endDT > :onDate)";
			$params['onDate'] = $data['onDate'];
		} else
		{
			$filter .= " and ISNULL(Mol.Mol_endDT, @date) >= @date";
		}

		$query = "
			DECLARE @date date = dbo.tzGetDate();
			SELECT
				Mol.Mol_id,
				Mol.Mol_Code,
				Mol.Contragent_id,
				Mol.LpuSection_id,
				Mol.MedPersonal_id,
				convert(varchar(10), Mol.Mol_begDT, 104) Mol_begDT,
				case
					when Mol.MedPersonal_id is not null then MP.Person_Fio
					else (Person_SurName + ' ' + Person_FirName + ' ' + Person_SecName)
				end as Person_Fio,
				S.Storage_id
			FROM
				v_Mol Mol with (nolock)
				outer apply (
					select top 1 Person_Fio
					from v_MedPersonal with(nolock)
					where MedPersonal_id = Mol.MedPersonal_id
				) MP
				left join v_Storage S with(nolock) on S.Storage_id = Mol.Storage_id
				outer apply(
					select top 1
						isnull(t.Org_id, t1.Org_id) as Org_id
					from
						v_StorageStructLevel t with(nolock)
						left join v_Lpu_all t1 with(nolock) on t1.Lpu_id = t.Lpu_id
					where
						t.Storage_id = S.Storage_id
				) ssl
			WHERE
				(1 = 1) {$filter}
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка медикаментов (DrugPrepList), доступных для выбора
	 */
	function loadDrugPrepList($data) {
		$params = array();
		$filters = "";

		if (!empty($data['query'])) {
			$filters .= " and DrugPrep.DrugPrep_Name like :query";
			$params['query'] = "".$data['query'] . "%";
		}
		if (!empty($data['DrugPrepFas_id'])) {
			$filters .= " and DrugPrep.DrugPrepFas_id = :DrugPrepFas_id";
			$params['DrugPrepFas_id'] = $data['DrugPrepFas_id'];
		}
		if (!empty($data['Storage_id'])) {
			$filters .= " and DOR.Storage_id = :Storage_id";
			$params['Storage_id'] = $data['Storage_id'];
		}
		if (!empty($data['LpuSection_id'])) {
			$filters .= " and DOR.Storage_id in (
				select Storage_id
				from v_StorageStructLevel with(nolock)
				where LpuSection_id = :LpuSection_id
			)";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}
		if (!empty($data['date'])) {
			$filters .= " and cast(DS.DrugShipment_setDT as date) <= :date";
			$params['date'] = $data['date'];
		} else {
            $params['date'] = date("Y-m-d");
        }

		$filters .= " and DOR.DrugOstatRegistry_Kolvo > 0";
		$filters .= " and isnull(PS.PrepSeries_IsDefect, 1) = 1";
		$filters .= " and (IsNull(PS.PrepSeries_GodnDate,:date) >= :date)";

		$query = "
			select distinct top 500
				RTRIM(ISNULL(DrugPrep.DrugPrep_Name, '')) as DrugPrep_Name,
				DrugPrep.DrugPrep_id as DrugPrep_id,
				DrugPrep.DrugPrepFas_id as DrugPrepFas_id,
				DOR.Storage_id,

				Drug.Drug_Nomen as hintPackagingData,  --Данные об упаковке – это часть поля Упаковка (drug_nomen) от начала строки до данных о производителе.
				isnull(Drug.Drug_RegNum + ',   ', '') + isnull(convert(varchar(10), Drug.Drug_begDate, 104) + ', ', '') + isnull(convert(varchar(10), Drug.Drug_endDate, 104) +', ', '--, ') + isnull(convert(varchar(10), REG.REGCERT_insDT, 104)+', ', '') + isnull(REGISTR.regNameCauntries, '') as hintRegistrationData, --Данные о регистрации
				case
					when NOMENF.FIRMS_ID = MANUFACTURF.FIRMS_ID
					then isnull(MANUFACTURF.FULLNAME, '')
					else
						case
							when (NOMENF.FULLNAME IS NULL OR rtrim(NOMENF.FULLNAME)='') AND (MANUFACTURF.FULLNAME IS NULL OR rtrim(MANUFACTURF.FULLNAME)='')
								then isnull(REGISTR.regNameCauntries, '')
							when NOMENF.FULLNAME IS NULL OR rtrim(NOMENF.FULLNAME)=''
								then isnull(MANUFACTURF.FULLNAME, '')
							else isnull(MANUFACTURF.FULLNAME, '') + ' / ' + NOMENF.FULLNAME
						end
				end as hintPRUP,	--ПР./УП.
				FNM.NAME as FirmNames
			from
				rls.v_DrugPrep DrugPrep with (nolock)
				inner join rls.v_Drug Drug (nolock) on Drug.DrugPrepFas_id = DrugPrep.DrugPrepFas_id
				inner join v_DrugOstatRegistry DOR with(nolock) on DOR.Drug_id = Drug.Drug_id
				inner join v_DrugShipment DS with(nolock) on DS.DrugShipment_id = DOR.DrugShipment_id
				left join rls.v_PrepSeries PS with (nolock) on PS.PrepSeries_id = DOR.PrepSeries_id

				left join rls.PREP P with(nolock) on P.Prep_id = Drug.DrugPrep_id
				left join rls.REGCERT REG with(nolock) on REG.REGCERT_ID =P.REGCERTID
				left join rls.NOMEN NM with(nolock) on NM.Nomen_id = Drug.Drug_id
				left join rls.FIRMS NOMENF with(nolock) on NM.FIRMID = NOMENF.FIRMS_ID
				left join rls.FIRMS MANUFACTURF with(nolock) on P.FIRMID = MANUFACTURF.FIRMS_ID
				left join rls.FIRMNAMES FNM with(nolock) on FNM.FIRMNAMES_ID = MANUFACTURF.NAMEID

				outer apply(
					SELECT TOP 1
						FN.NAME + ' (' + C.NAME + ')' as regNameCauntries
					FROM
						rls.REGCERT_EXTRAFIRMS RE
						left join rls.FIRMS F with(nolock) on RE.FIRMID = F.FIRMS_ID
						left join rls.FIRMNAMES FN with(nolock) on FN.FIRMNAMES_ID = F.NAMEID
						left join rls.COUNTRIES C with(nolock) on C.COUNTRIES_ID = F.COUNTID
					WHERE RE.CERTID = P.REGCERTID
				) REGISTR
			where (1=1)
				{$filters}
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка доступных партий медикаментов
	 */
	function loadDocumentUcStrList($data) {
		$this->load->model('DocumentUc_model', 'DocumentUc_model');

		$params = array();
		$filters = "1=1";
		$dor_filters = "";
		$reserved_filters = "";

		if (!empty($data['Drug_id'])) {
            $dor_filters .= " and i_dor.Drug_id = :Drug_id";
			$params['Drug_id'] = $data['Drug_id'];
		}

		if (!empty($data['LpuSection_id'])) {
            $dor_filters .= " and i_dor.Storage_id in (
				select Storage_id
				from v_StorageStructLevel with(nolock)
				where LpuSection_id = :LpuSection_id
			)";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}
		if (!empty($data['Storage_id'])) {
            $dor_filters .= " and i_dor.Storage_id = :Storage_id";
			$params['Storage_id'] = $data['Storage_id'];
		}

		if (!empty($data['date'])) {
			$filters .= " and cast(DS.DrugShipment_setDT as date) <= :date";
			$params['date'] = $data['date'];
		} else {
            $params['date'] = date("Y-m-d");
        }

		if (!empty($data['EvnDrug_id'])) {
			$reserved_filters .= " and rED.EvnDrug_id <> :EvnDrug_id";
			$params['EvnDrug_id'] = $data['EvnDrug_id'];
		}

        $filters .= " and isnull(PS.PrepSeries_IsDefect, 1) = 1";
        $filters .= " and (IsNull(PS.PrepSeries_GodnDate,:date) >= :date)";

		$params['DefaultGoodsUnit_id'] = $this->DocumentUc_model->getDefaultGoodsUnitId();

		/*	
		$query = "
			select distinct
				DUS.DocumentUcStr_id,
				DUS.DocumentUc_id,
				PS.PrepSeries_Ser,
				isnull(PS.PrepSeries_GodnDate, '3000-01-01') as gDate,
				convert(varchar(10), PS.PrepSeries_GodnDate, 104) as PrepSeries_GodnDate,
				ost.DocumentUcStr_Count,
				ost.DocumentUcStr_Price,
				ost.DocumentUcStr_Sum,
				DF.DrugFinance_id,
				DF.DrugFinance_Name,
				(
					'годн. '+IsNull(convert(varchar(10), PS.PrepSeries_GodnDate, 104),'отсут.')
					+', цена '+cast(ROUND(IsNull(ost.DocumentUcStr_Price,0), 2) as varchar(20))
					+', ост. '+cast(cast(Round(IsNull(ost.DocumentUcStr_Count,0),4) as numeric(30,4)) as varchar(31))
					+', фин. '+RTRIM(RTRIM(ISNULL(DF.DrugFinance_Name, 'отсут.')))
					+', серия '+RTRIM(ISNULL(PS.PrepSeries_Ser, ''))
				) as DocumentUcStr_Name,
				coalesce(DUS.GoodsUnit_id, GPC.GoodsUnit_id, 57) as GoodsUnit_id
			from
				v_DrugShipmentLink DSL with(nolock)
				inner join v_DrugShipment DS with(nolock) on DS.DrugShipment_id = DSL.DrugShipment_id
				outer apply ( -- вычисление остатков доступных в рамках партии
					select
						i_dor.DrugFinance_id,
						i_dor.PrepSeries_id,
						i_dor.Drug_id,
						i_dor.DrugOstatRegistry_Cost,
						sum(DrugOstatRegistry_Kolvo) as DrugOstatRegistry_Kolvo
					from
						v_DrugOstatRegistry i_dor with (nolock)
					where
						i_dor.DrugShipment_id = DSL.DrugShipment_id
						{$dor_filters}
						and (
							i_dor.SubAccountType_id = 1 or -- субсчет Доступно
							(
								i_dor.SubAccountType_id = 2 and -- субсчет Резерв
								exists ( -- учитывать нужно только резерв являющийся резервом под EvnDrug
									select
										i_dorl.DrugOstatRegistryLink_id
									from
										v_DrugOstatRegistryLink i_dorl with (nolock)
										inner join v_EvnDrug i_ed with (nolock) on i_ed.DocumentUcStr_id = i_dorl.DrugOstatRegistryLink_TableID
									where
										i_dorl.DrugOstatRegistry_id = i_dor.DrugOstatRegistry_id and
										i_dorl.DrugOstatRegistryLink_TableName = 'DocumentUcStr'
								)
							)
						)
					group by
						i_dor.DrugFinance_id,
						i_dor.PrepSeries_id,
						i_dor.Drug_id,
						i_dor.DrugOstatRegistry_Cost
				) dor
				inner join v_DocumentUcStr DUS with(nolock) on DUS.DocumentUcStr_id = DSL.DocumentUcStr_id
				inner join v_DrugFinance DF with(nolock) on DF.DrugFinance_id = dor.DrugFinance_id
				inner join rls.v_PrepSeries PS with(nolock) on PS.PrepSeries_id = dor.PrepSeries_id
				inner join rls.v_Drug D with(nolock) on D.Drug_id = dor.Drug_id
				outer apply (
					select
					    isnull(sum(rED.EvnDrug_Kolvo),0) as EvnDrug_Kolvo
					from
					    v_EvnDrug rED with(nolock)
                        inner join v_DocumentUcStr rDUS with(nolock) on rDUS.DocumentUcStr_id = rED.DocumentUcStr_id
                        inner join v_DocumentUc rDU with(nolock) on rDU.DocumentUc_id = rDUS.DocumentUc_id
                        inner join v_DrugShipmentLink oDSL with(nolock) on oDSL.DocumentUcStr_id = rED.DocumentUcStr_oid
					where
					    oDSL.DrugShipment_id = DSL.DrugShipment_id
						and isnull(rDU.DrugDocumentStatus_id,1) = 1 --новый
						{$reserved_filters}
				) rl
				outer apply (
					select top 1
						(dor.DrugOstatRegistry_Kolvo - isnull(rl.EvnDrug_Kolvo,0)) as DocumentUcStr_Count,
						dor.DrugOstatRegistry_Cost as DocumentUcStr_Price,
						((dor.DrugOstatRegistry_Kolvo - isnull(rl.EvnDrug_Kolvo,0)) * dor.DrugOstatRegistry_Cost) as DocumentUcStr_Sum
				) ost
				outer apply (
					select top 1
					    GPC.GoodsUnit_id
					from
					    v_GoodsPackCount GPC with(nolock)
					where
					    GPC.DrugComplexMnn_id = D.DrugComplexMnn_id
				) GPC
			where
				{$filters}
				and isnull(ost.DocumentUcStr_Count,0) > 0
			order by
			    isnull(PS.PrepSeries_GodnDate, '3000-01-01')
		";
		*/
		
		// #154195 Для оптимизации запроса используем временную таблицу
		$query = "
			set nocount on;
			
			--  Создаем временную таблицу для оптимизации запроса
			select distinct
				DUS.DocumentUcStr_id,
				DUS.DocumentUc_id,
				PS.PrepSeries_Ser,
				PS.PrepSeries_GodnDate,
				DF.DrugFinance_id,
				DF.DrugFinance_Name,
				DrugOstatRegistry_Kolvo,
				DrugOstatRegistry_Cost,
				DSL.DrugShipment_id,
				coalesce(DUS.GoodsUnit_id, GPC.GoodsUnit_id, :DefaultGoodsUnit_id) as GoodsUnit_id,
				isnull(DUS.GoodsUnit_bid, :DefaultGoodsUnit_id) as GoodsUnit_bid,
				D.DrugComplexMnn_id,
				D.DrugTorg_id
			into
				#tbl
			from
				v_DrugShipmentLink DSL with(nolock)				
				inner join v_DrugShipment DS with(nolock) on DS.DrugShipment_id = DSL.DrugShipment_id
				outer apply ( -- вычисление остатков доступных в рамках партии
					select
						i_dor.DrugFinance_id,
						i_dor.PrepSeries_id,
						i_dor.Drug_id,
						i_dor.DrugOstatRegistry_Cost,
						sum(DrugOstatRegistry_Kolvo) as DrugOstatRegistry_Kolvo
					from
						v_DrugOstatRegistry i_dor with (nolock)
					where
						i_dor.DrugShipment_id = DSL.DrugShipment_id
						{$dor_filters}
						and (
							i_dor.SubAccountType_id = 1 or -- субсчет Доступно
							(
								i_dor.SubAccountType_id = 2 and -- субсчет Резерв
								exists ( -- учитывать нужно только резерв являющийся резервом под EvnDrug
									select
										i_dorl.DrugOstatRegistryLink_id
									from
										v_DrugOstatRegistryLink i_dorl with (nolock)
										inner join v_EvnDrug i_ed with (nolock) on i_ed.DocumentUcStr_id = i_dorl.DrugOstatRegistryLink_TableID
									where
										i_dorl.DrugOstatRegistry_id = i_dor.DrugOstatRegistry_id and
										i_dorl.DrugOstatRegistryLink_TableName = 'DocumentUcStr'
										and dbo.[GetRegion]() <> 2
								)
							)
						)
					group by
						i_dor.DrugFinance_id,
						i_dor.PrepSeries_id,
						i_dor.Drug_id,
						i_dor.DrugOstatRegistry_Cost
				) dor
				inner join v_DocumentUcStr DUS with(nolock) on DUS.DocumentUcStr_id = DSL.DocumentUcStr_id
				inner join v_DrugFinance DF with(nolock) on DF.DrugFinance_id = dor.DrugFinance_id
				inner join rls.v_PrepSeries PS with(nolock) on PS.PrepSeries_id = dor.PrepSeries_id
				inner join rls.v_Drug D with(nolock) on D.Drug_id = dor.Drug_id

				outer apply (
					select top 1
					    GPC.GoodsUnit_id
					from
					    v_GoodsPackCount GPC with(nolock)
					where
					    GPC.DrugComplexMnn_id = D.DrugComplexMnn_id
				) GPC
			where
				{$filters}
				;
			-- Выполняем запрос с использованием временной таблицы	
			Select
				t.DocumentUcStr_id,
				t.DocumentUc_id,
				t.PrepSeries_Ser,
				isnull(t.PrepSeries_GodnDate, '3000-01-01') as gDate,
				convert(varchar(10), t.PrepSeries_GodnDate, 104) as PrepSeries_GodnDate,
				ost.DocumentUcStr_Count,
				ost.DocumentUcStr_Price,
				ost.DocumentUcStr_Sum,
				t.DrugFinance_id,
				t.DrugFinance_Name,
				(
					'годн. '+IsNull(convert(varchar(10), t.PrepSeries_GodnDate, 104),'отсут.')
					+', цена '+cast(ROUND(IsNull(ost.DocumentUcStr_Price,0), 2) as varchar(20))
					+', ост. '+cast(cast(Round(IsNull(ost.DocumentUcStr_Count,0),4) as numeric(30,4)) as varchar(31))
					+', фин. '+RTRIM(RTRIM(ISNULL(t.DrugFinance_Name, 'отсут.')))
					+', серия '+RTRIM(ISNULL(t.PrepSeries_Ser, ''))
				) as DocumentUcStr_Name,							
				t.GoodsUnit_id,
				t.GoodsUnit_bid,
				gu_b.GoodsUnit_Nick as GoodsUnit_bNick,
				isnull(b_gpc.GoodsPackCount_Count, 1) as GoodsPackCount_bCount			
			from
				#tbl t			
				outer apply (
					select
					    isnull(sum(rED.EvnDrug_Kolvo),0) as EvnDrug_Kolvo
					from
					    v_EvnDrug rED with(nolock)
                        inner join v_DocumentUcStr rDUS with(nolock) on rDUS.DocumentUcStr_id = rED.DocumentUcStr_id
                        inner join v_DocumentUc rDU with(nolock) on rDU.DocumentUc_id = rDUS.DocumentUc_id
                        inner join v_DrugShipmentLink oDSL with(nolock) on oDSL.DocumentUcStr_id = rED.DocumentUcStr_oid
					where
					    oDSL.DrugShipment_id = t.DrugShipment_id
						and isnull(rDU.DrugDocumentStatus_id,1) = 1 --новый			
						and dbo.[GetRegion]() <> 2
				) rl
				cross apply (
					select top 1
						(t.DrugOstatRegistry_Kolvo - isnull(rl.EvnDrug_Kolvo,0)) as DocumentUcStr_Count,
						t.DrugOstatRegistry_Cost as DocumentUcStr_Price,
						((t.DrugOstatRegistry_Kolvo - isnull(rl.EvnDrug_Kolvo,0)) * t.DrugOstatRegistry_Cost) as DocumentUcStr_Sum
				) ost
				left join v_GoodsUnit gu_b with (nolock) on gu_b.GoodsUnit_id = t.GoodsUnit_bid
				outer apply (
                    select top 1
                        i_gpc.GoodsPackCount_Count
                    from
                        v_GoodsPackCount i_gpc with (nolock)
                    where
                        i_gpc.GoodsUnit_id = t.GoodsUnit_bid and
                        i_gpc.DrugComplexMnn_id = t.DrugComplexMnn_id and
                        (
                            t.DrugTorg_id is null or
                            i_gpc.TRADENAMES_ID is null or
                            i_gpc.TRADENAMES_ID = t.DrugTorg_id
                        )
                    order by
                        i_gpc.TRADENAMES_ID desc
                ) b_gpc
			where
				isnull(DocumentUcStr_Count,0) > 0
			order by
				isnull(t.PrepSeries_GodnDate, '3000-01-01')
		";
		
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка упаковок
	 */
	function loadDrugList($data) {
		$this->load->model('DocumentUc_model', 'DocumentUc_model');

		$params = array();
		$filters = "";

		if (!empty($data['query'])) {
			$filters .= " and DrugPrep.DrugPrep_Name like :query";
			$params['query'] = "".$data['query'] . "%";
		}
		if (!empty($data['Drug_id'])) {
			$filters .= " and Drug.Drug_id = :Drug_id";
			$params['Drug_id'] = $data['Drug_id'];
		}
		if (!empty($data['DrugPrepFas_id'])) {
			$filters .= " and Drug.DrugPrepFas_id = :DrugPrepFas_id";
			$params['DrugPrepFas_id'] = $data['DrugPrepFas_id'];
		}
		if (!empty($data['LpuSection_id'])) {
			$filters .= " and DOR.Storage_id in (
				select Storage_id
				from v_StorageStructLevel with(nolock)
				where LpuSection_id = :LpuSection_id
			)";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}
		if (!empty($data['Storage_id'])) {
			$filters .= " and DOR.Storage_id = :Storage_id";
			$params['Storage_id'] = $data['Storage_id'];
		}
		if (!empty($data['date'])) {
			$filters .= " and cast(DS.DrugShipment_setDT as date) <= :date";
			$params['date'] = $data['date'];
		} else {
			$params['date'] = date("Y-m-d");
		}

		$params['DefaultGoodsUnit_id'] = $this->DocumentUc_model->getDefaultGoodsUnitId();

		$filters .= " and DOR.DrugOstatRegistry_Kolvo > 0";
		$filters .= " and isnull(PS.PrepSeries_IsDefect, 1) = 1";
		$filters .= " and (IsNull(PS.PrepSeries_GodnDate,:date) >= :date)";

		$query = "
			select distinct top 1000
				RTRIM(ISNULL(Drug.Drug_Nomen, '')) as Drug_Name,
				RTRIM(ISNULL(Drug.Drug_Name, '')) as Drug_FullName,
				Drug.Drug_id,
				Drug.DrugPrepFas_id,
				Drug.Drug_Code as Drug_Code,
				Drug.Drug_Fas as Drug_Fas,
				RTRIM(ISNULL(Drug.DrugForm_Name, '')) as DrugForm_Name,
				RTRIM(ISNULL(Drug.Drug_PackName, '')) as DrugUnit_Name,
				B_GU.GoodsUnit_id as GoodsUnit_bid,
				B_GU.GoodsUnit_Nick as GoodsUnit_bName,
				(case
				    when B_GU.GoodsUnit_id = :DefaultGoodsUnit_id then 1
				    else B_GPC.GoodsPackCount_Count
				end) as GoodsPackCount_bCount
			from rls.v_Drug Drug with (nolock)
				inner join v_DrugOstatRegistry DOR (nolock) on DOR.Drug_id = Drug.Drug_id
				inner join v_DrugShipment DS with(nolock) on DS.DrugShipment_id = DOR.DrugShipment_id
				left join v_DrugShipmentLink DSL with (nolock) on DSL.DrugShipment_id = DS.DrugShipment_id
				left join v_DocumentUcStr DUS with (nolock) on DUS.DocumentUcStr_id = DSL.DocumentUcStr_id
				left join rls.v_PrepSeries PS with (nolock) on PS.PrepSeries_id = DOR.PrepSeries_id
				left join v_GoodsUnit B_GU with (nolock) on B_GU.GoodsUnit_id = isnull(DUS.GoodsUnit_bid, :DefaultGoodsUnit_id)
				outer apply (
                    select top 1
                        I_GPC.GoodsPackCount_Count
                    from
                        v_GoodsPackCount I_GPC with (nolock)
                    where
                        I_GPC.GoodsUnit_id = B_GU.GoodsUnit_id and
                        I_GPC.DrugComplexMnn_id = Drug.DrugComplexMnn_id and
                        (
                            Drug.DrugTorg_id is null or
                            I_GPC.TRADENAMES_ID is null or
                            I_GPC.TRADENAMES_ID = Drug.DrugTorg_id
                        )
                    order by
                        i_gpc.TRADENAMES_ID desc, i_gpc.Org_id
                ) B_GPC
			where
				1=1
				{$filters}
		";

		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка осмотров врача-специалиста в талоне угл. обсл.
	 * Входящие данные: $data['EvnDrug_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnVizitPLWOW($data) {

		$query = "
			select
				EVPLW.EvnVizitPLWOW_id,
				convert(varchar(10), EVPLW.EvnVizitPLWOW_setDate, 104) as EvnVizitPLWOW_setDate,
				RTRIM(LS.LpuSection_Name) as LpuSection_Name,
				EVPLW.MedPersonal_id,
				RTRIM(MP.Person_Fio) as MedPersonal_FIO,
				EVPLW.DispWOWSpec_id,
				RTRIM(DWS.DispWOWSpec_Name) as DispWOWSpec_Name,
				RTRIM(D.Diag_Code) as Diag_Code,
				EVPLW.LpuSection_id,
				RTRIM(LS.LpuSection_Name) as LpuSection_Name,
				EVPLW.Diag_id
			from v_EvnVizitPLWOW EVPLW with (nolock)
				left join LpuSection LS with (nolock) on LS.LpuSection_id = EVPLW.LpuSection_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EVPLW.MedPersonal_id
				left join DispWowSpec DWS with (nolock) on DWS.DispWowSpec_id = EVPLW.DispWowSpec_id
				left join Diag D with (nolock) on D.Diag_id = EVPLW.Diag_id
			where EVPLW.EvnVizitPLWOW_pid = ?
		";
		$result = $this->db->query($query, array($data['EvnDrug_id']));
		/*
		  echo getDebugSql($query, array($data['EvnDrug_id']));
		  exit;
		 */
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Кривая загрузка списка случаев использования медикаментов
	 */
	function loadEvnDrugView($data) {
		$filter = '(1=1)';
		$params = array();

		if (isset($data['Lpu_id'])) {
			$filter .= " and ED.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		if (isset($data['EvnDrug_pid'])) {
			$filter .= " and ED.EvnDrug_pid = :EvnDrug_pid";
			$params['EvnDrug_pid'] = $data['EvnDrug_pid'];
		}

		if (isset($data['EvnDrug_id'])) {
			$filter .= " and ED.EvnDrug_id = :EvnDrug_id";
			$params['EvnDrug_id'] = $data['EvnDrug_id'];
		}

		$query = "
			select
				ED.EvnDrug_id,
				ED.EvnDrug_pid as EvnDrug_pid,
				convert(varchar(10), ED.EvnDrug_setDate, 104) as EvnDrug_setDate,
				convert(varchar(10), ED.EvnDrug_didDate, 104) as EvnDrug_didDate,
				ED.Server_id,
				ED.Person_id,
				ED.PersonEvn_id,
				ED.Drug_id,
				Drug.DrugPrepFas_id,
				ED.LpuSection_id,
				ED.EvnDrug_Price,
				ED.EvnDrug_Sum,
				ED.DocumentUc_id,
				ED.DocumentUcStr_id,
				ED.DocumentUcStr_oid,
				ED.Mol_id,
				ED.EvnDrug_Kolvo,
				ED.EvnDrug_KolvoEd,
				ED.EvnDrug_RealKolvo,
				Dus.GoodsUnit_id
			from v_EvnDrug ED with (nolock)
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = ED.LpuSection_id
				left join rls.v_Drug Drug with (nolock) on Drug.Drug_id = ED.Drug_id
				left join v_Mol Mol with (nolock) on Mol.Mol_id = ED.Mol_id
				left join v_DocumentUcStr DuS with (nolock) on DuS.DocumentUcStr_id = ED.DocumentUcStr_id
			where {$filter}
		";
		/*
		  echo getDebugSql($query, $params);
		  exit;
		 */
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function loadEvnDrugStreamList($data) {
		$filter = '';
		$params = array();
		$filter .= " and EPW.pmUser_insID = :pmUser_id ";
		$params['pmUser_id'] = $data['pmUser_id'];

		if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $data['begTime'])) {
			$filter .= " and EPW.EvnDrug_insDT >= :date_time";
			$params['date_time'] = $data['begDate'] . " " . $data['begTime'];
		}

		if (isset($data['Lpu_id'])) {
			$filter .= " and EPW.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		$query = "
			SELECT DISTINCT TOP 100
				EPW.EvnDrug_id as EvnDrug_id,
				EPW.Person_id as Person_id,
				EPW.Server_id as Server_id,
				EPW.PersonEvn_id as PersonEvn_id,
				RTRIM(PS.Person_Surname) as Person_Surname,
				RTRIM(PS.Person_Firname) as Person_Firname,
				RTRIM(PS.Person_Secname) as Person_Secname,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				convert(varchar(10), EPW.EvnDrug_setDate, 104) as EvnPLWow_setDate,
				convert(varchar(10), EPW.EvnDrug_disDate, 104) as EvnPLWow_disDate,
				EPW.EvnDrug_VizitCount as EvnPLWow_VizitCount,
				IsFinish.YesNo_Name as EvnDrug_IsFinish,
				PTW.PrivilegeTypeWow_id,
				PTW.PrivilegeTypeWOW_Name
			FROM v_EvnDrug EPW with (nolock)
				inner join v_PersonState PS with (nolock) on PS.Person_id = EPW.Person_id
				-- здесь должен быть inner
				left join PersonPrivilegeWOW PPW with (nolock) on PPW.Person_id = PS.Person_id
				left join PrivilegeTypeWOW PTW with (nolock) on PTW.PrivilegeTypeWow_id = PPW.PrivilegeTypeWow_id
				left join YesNo IsFinish with (nolock) on IsFinish.YesNo_id = EPW.EvnPLWow_IsFinish
			WHERE (1 = 1)
				" . $filter . "
			ORDER BY EPW.EvnDrug_id desc
			";
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function loadEvnVizitPLDispDopGrid($data) {
		$query = "
			select
				EVPL.EvnVizitPL_id,
				EVPL.LpuSection_id,
				EVPL.MedPersonal_id,
				EVPL.MedPersonal_sid,
				EVPL.PayType_id,
				EVPL.ProfGoal_id,
				EVPL.ServiceType_id,
				EVPL.VizitType_id,
				EVPL.EvnVizitPL_Time,
				convert(varchar(10), EVPL.EvnVizitPL_setDate, 104) as EvnVizitPL_setDate,
				EVPL.EvnVizitPL_setTime,
				RTrim(LS.LpuSection_Name) as LpuSection_Name,
				RTrim(MP.Person_Fio) as MedPersonal_Fio,
				RTrim(PT.PayType_Name) as PayType_Name,
				RTrim(ST.ServiceType_Name) as ServiceType_Name,
				RTrim(VT.VizitType_Name) as VizitType_Name,
				1 as Record_Status
			from v_EvnVizitPL EVPL with (nolock)
				left join LpuSection LS with (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EVPL.MedPersonal_id
				left join PayType PT with (nolock) on PT.PayType_id = EVPL.PayType_id
				left join ServiceType ST with (nolock) on ST.ServiceType_id = EVPL.ServiceType_id
				left join VizitType VT with (nolock) on VT.VizitType_id = EVPL.VizitType_id
			where EVPL.EvnVizitPL_pid = ?
		";
		$result = $this->db->query($query, array($data['EvnPL_id']));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function checkPersonData($data) {
		$query = "
			select
				Sex_id,
				SocStatus_id,
				ps.UAddress_id as Person_UAddress_id,
				ps.Polis_Ser,
				ps.Polis_Num,
				o.Org_Name,
				o.Org_INN,
				o.Org_OGRN,
				o.UAddress_id as Org_UAddress_id,
				o.Okved_id,
				os.OrgSmo_Name
			from v_persondopdisp pdd with (nolock)
			left join v_PersonState ps with (nolock) on ps.Person_id=pdd.Person_id
			left join v_Job j with (nolock) on j.Job_id=ps.Job_id
			left join v_Org o with (nolock) on o.Org_id=j.Org_id
			left join v_Polis pol with (nolock) on pol.Polis_id=ps.Polis_id
			left join v_OrgSmo os with (nolock) on os.OrgSmo_id=pol.OrgSmo_id
			where PersonEvn_id = ?
		";

		$result = $this->db->query($query, array($data['PersonEvn_id']));
		$response = $result->result('array');

		$error = Array();
		if (ArrayVal($response[0], 'Sex_id') == '')
			$errors[] = 'Не заполнен Пол';
		if (ArrayVal($response[0], 'SocStatus_id') == '')
			$errors[] = 'Не заполнен Соц. статус';
		if (ArrayVal($response[0], 'Person_UAddress_id') == '')
			$errors[] = 'Не заполнен Адрес по месту регистрации';
		if (ArrayVal($response[0], 'Polis_Num') == '')
			$errors[] = 'Не заполнен Номер полиса';
		if (ArrayVal($response[0], 'Polis_Ser') == '')
			$errors[] = 'Не заполнена Серия полиса';
		if (ArrayVal($response[0], 'OrgSmo_id') == '')
			$errors[] = 'Не заполнена Организация, выдавшая полис';
		if (ArrayVal($response[0], 'Org_UAddress_id') == '')
			$errors[] = 'Не заполнен Адрес места работы';
		if (ArrayVal($response[0], 'Org_INN') == '')
			$errors[] = 'Не заполнен ИНН места работы';
		if (ArrayVal($response[0], 'Org_OGRN') == '')
			$errors[] = 'Не заполнена ОГРН места работы';
		if (ArrayVal($response[0], 'Okved_id') == '')
			$errors[] = 'Не заполнен ОКВЭД места работы';

		If (count($error) > 0) { // есть ошибки в заведении
			$errstr = implode("<br/>", $errors);
			return array('Error_Msg' => 'Проверьте полноту заведения данных у человека!<br/>' . $errstr);
		}
		return "Ok";
	}

	/**
	 * Сохранение медикамента персонифицированного учета
	 * @param array $data
	 * @return array
	 */
	function saveEvnDrug($data) {
		$response = array(array(
			'EvnDrug_id' => $data['EvnDrug_id'],
			'Error_Msg' => null,
			'Error_Code' => null,
			'EvnPrescrTreatDrug_FactCount' => null,
		));

		$this->beginTransaction();

		if (empty($data['EvnPrescrTreat_Fact'])) {
			// поле «Списать приемов» по умолчанию 1
			$data['EvnPrescrTreat_Fact'] = 1;
		}

		if (!is_numeric($data['EvnPrescrTreat_Fact']) || $data['EvnPrescrTreat_Fact'] <= 0 ) {
			$response[0]['Error_Msg'] = 'Неправильное значение в поле «Списать приемов»';
			return $response;
		}

		$this->load->helper("Options");
		$this->load->model("Options_model", "Options_model");
		$data['options'] = $this->Options_model->getOptionsAll($data);
		$is_merch_module = (!empty($data['options']['drugcontrol']['drugcontrol_module']) && $data['options']['drugcontrol']['drugcontrol_module'] == 2); //признак учета в АРМ Товароведа

		if (empty($data['EvnDrug_id'])) {
			$proc = 'p_EvnDrug_ins';
		} else {
			$proc = 'p_EvnDrug_upd';

			if ($is_merch_module) { //если учет ведется в АРМ товароведа, необходимо проверить возможность редактирования документа учета
				$dus_id = $this->getExecutedDocumentUcStrForEvnDrug($data['EvnDrug_id']);
				if (!empty($dus_id)) {
					$response[0]['Error_Msg'] = 'Редактирование использования медикамента невозможно, т.к. медикамент уже списан со склада';
					return $response;
				}
			}
		}

		$query = "
			declare
				@EvnDrug_id bigint = :EvnDrug_id,
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec " . $proc . "
				@EvnDrug_id = @EvnDrug_id output,
				@EvnDrug_pid = :EvnDrug_pid,
				@EvnDrug_setDT = :EvnDrug_setDate,
				@EvnDrug_didDT = null,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@Drug_id = :Drug_id,
				@LpuSection_id = :LpuSection_id,
				@EvnDrug_Price = :EvnDrug_Price,
				@EvnDrug_Sum = :EvnDrug_Sum,
				@DocumentUc_id = :DocumentUc_id,
				@DocumentUcStr_id = :DocumentUcStr_id,
				@DocumentUcStr_oid = :DocumentUcStr_oid,
				@Storage_id = :Storage_id,
				@Mol_id = :Mol_id,
				@EvnDrug_Kolvo = :EvnDrug_Kolvo,
				@EvnDrug_KolvoEd = :EvnDrug_KolvoEd,
				@EvnDrug_RealKolvo = :EvnDrug_RealKolvo,
				@EvnPrescrTreatDrug_id = :EvnPrescrTreatDrug_id,
				@EvnPrescr_id = :EvnPrescr_id,
				@EvnCourse_id = :EvnCourse_id,
				@GoodsUnit_id = :GoodsUnit_id,
				@GoodsUnit_bid = :GoodsUnit_bid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @EvnDrug_id as EvnDrug_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$params = array(
			'EvnDrug_id' => (!empty($data['EvnDrug_id']) ? $data['EvnDrug_id'] : NULL),
			//'EvnDrug_pid'=>$data['EvnDrug_pid'],
			//'EvnDrug_rid'=>$data['EvnDrug_rid'],
			'EvnDrug_pid' => (!empty($data['EvnDrug_pid']) ? $data['EvnDrug_pid'] : $data['EvnDrug_rid']),
			'EvnDrug_setDate' => $data['EvnDrug_setDate'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'Person_id' => (!empty($data['Person_id']) ? $data['Person_id'] : NULL),
			'PersonEvn_id' => $data['PersonEvn_id'],
			'Drug_id' => $data['Drug_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'EvnDrug_Price' => (!empty($data['EvnDrug_Price']) ? $data['EvnDrug_Price'] : NULL),
			'EvnDrug_Sum' => (!empty($data['EvnDrug_Sum']) ? $data['EvnDrug_Sum'] : NULL),
			'DocumentUc_id' => (!empty($data['DocumentUc_id']) ? $data['DocumentUc_id'] : NULL),
			'DocumentUcStr_id' => (!empty($data['DocumentUcStr_id']) ? $data['DocumentUcStr_id'] : NULL),
			'DocumentUcStr_oid' => $data['DocumentUcStr_oid'],
			'Storage_id' => (!empty($data['Storage_id']) ? $data['Storage_id'] : NULL),
			'Mol_id' => (!empty($data['Mol_id']) ? $data['Mol_id'] : NULL),
			'EvnDrug_Kolvo' => floatval( str_replace( ',', '.', $data['EvnDrug_Kolvo'] )) ,
			'EvnDrug_KolvoEd' => $data['EvnDrug_KolvoEd'],
			'GoodsUnit_id' => (!empty($data['GoodsUnit_id']) ? $data['GoodsUnit_id'] : NULL),
			'GoodsUnit_bid' => (!empty($data['GoodsUnit_bid']) ? $data['GoodsUnit_bid'] : NULL),
			'EvnDrug_RealKolvo' => (!empty($data['EvnDrug_RealKolvo']) ? $data['EvnDrug_RealKolvo'] : NULL),
			'EvnPrescr_id' => (!empty($data['EvnPrescr_id']) ? $data['EvnPrescr_id'] : NULL),
			'EvnCourse_id' => (!empty($data['EvnCourse_id']) ? $data['EvnCourse_id'] : NULL),
			'EvnPrescrTreatDrug_id' => (!empty($data['EvnPrescrTreatDrug_id']) ? $data['EvnPrescrTreatDrug_id'] : NULL),
			'pmUser_id' => $data['pmUser_id']
		);

		/* echo getDebugSql($query, $params);
		  exit; */

		$res = $this->db->query($query, $params);

		if (!is_object($res)) {
			$this->rollbackTransaction();
			$response[0]['Error_Msg'] = 'Ошибка запроса к БД (Сохранение медикамента персонифицированного учета)';
			return $response;
		}
		//print_r($res->result('array'));
		$tmp = $res->result('array');
		if (!empty($tmp[0]['Error_Msg'])) {
			$this->rollbackTransaction();
			$response[0]['Error_Msg'] = $tmp[0]['Error_Msg'];
			$response[0]['Error_Code'] = $tmp[0]['Error_Code'];
			return $response;
		}
		$response[0]['EvnDrug_id'] = $tmp[0]['EvnDrug_id'];

		$isExecEvnPrescr = false;
		//**********
		//  Для Уфы обрабатываем время приема
		if ($_SESSION['region']['nick'] == 'ufa' && $data['arr_time'] && !empty($response[0]['EvnDrug_id'])) {

			$EvnDrug_id = $response[0]['EvnDrug_id'];

			$arr_data = json_decode($data['arr_time'], 1);

			$xml = '<RD>';
			
			if (isset($data['pmUser_id']))
				$pmUser = $data['pmUser_id'];
			else
				$pmUser = '';
			foreach ($arr_data as $item) {
				$idx = "";
				$time = "";
				if (isset($item['idx']))
					$idx = $item['idx'];
				if (isset($item['time']))
					$time = $item['time'];
				
				$EvnCourse_id = (!empty($data['EvnCourse_id']) ? $data['EvnCourse_id'] : NULL);

				$xml .='<R|*|v1="' . $EvnCourse_id . '" 
							  |*|v2="' . $EvnDrug_id . '" 
							  |*|v3="' . $pmUser . '"
							  |*|v4="' . $idx . '"
							  |*|v5="' . $time . '" ></R>';
			}

			$xml .= '</RD>';
			$xml = strtr($xml, array(PHP_EOL => '', " " => ""));
			$xml = str_replace("|*|", " ", $xml);

			$params = array('xml' => (string) $xml);

			$query = "
							Declare 
							@xml nvarchar(max),
							@Error_Code int,
							@Error_Message varchar(4000)

								Set @xml = :xml;

								 exec r2.p_CourseTimeIntake_upd @xml, @Error_Code, @Error_Message

								 Select @Error_Code as Error_Code, @Error_Message as Error_Msg ;  
						";

		  
			$result = $this->db->query($query, $params);

			if (is_object($result)) {
				$trans_result = $result->result('array');
				if (!empty($trans_result[0]['Error_Msg'])) {
					throw new Exception($trans_result[0]['Error_Msg'], 500);
				}
			} else {
				throw new Exception('Ошибка при сохранении времени приема');
			}
		}
		//**********

		if (empty($data['EvnDrug_id']) && !empty($data['EvnPrescr_id'])) {
			/**
			при добавлении нужно определить выполнено ли назначение полностью, а также обновить поля:
			EvnPrescrTreatDrug_FactCount, -- Количество выполненных приемов
			EvnCourseTreatDrug_FactCount -- Количество выполненных приемов в курсе лечения
			EvnCourseTreatDrug_FactDose -- Фактическая курсовая доза в курсе лечения
			 */
			$this->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');
			try {
				$tmp = $this->EvnPrescrTreat_model->updateFactData(array(
					'EvnPrescrTreat_id' => $data['EvnPrescr_id'],
					'EvnPrescrTreatDrug_id' => $data['EvnPrescrTreatDrug_id'],
					'EvnPrescrTreat_Fact' => $data['EvnPrescrTreat_Fact'],
					'EvnCourseTreat_id' => $data['EvnCourse_id'],
					'EvnCourseTreatDrug_id' => $data['EvnCourseTreatDrug_id'],
					'pmUser_id' => $data['pmUser_id'],
				), 'upd');
				$response[0]['EvnPrescrTreatDrug_FactCount'] = $tmp['EvnPrescrTreatDrug_FactCount'];
				$response[0]['isExecEvnPrescr'] = $tmp['isExecEvnPrescr'];
				$response[0]['EvnPrescrTreat_PrescrCount'] = $tmp['EvnPrescrTreat_PrescrCount'];
				$response[0]['epFactCount'] = $tmp['epFactCount'];
				$response[0]['cntEvnPrescrTreatDrug'] = $tmp['cntEvnPrescrTreatDrug'];
				$isExecEvnPrescr = $tmp['isExecEvnPrescr'];
			} catch (Exception $e) {
				//так-то нужно откатить транзакцию
				$response[0]['Error_Msg'] = $e->getMessage();
				$response[0]['EvnPrescrTreatDrug_FactCount'] = null;
			}
		}

		if ($isExecEvnPrescr) {
			// если списаны все медикаменты из назначения, то считаем назначение выполненным
			$this->load->model('EvnPrescr_model', 'EvnPrescr_model');
			$tmp = $this->EvnPrescr_model->execEvnPrescr(array(
				'EvnPrescr_id' => $data['EvnPrescr_id'],
				'pmUser_id' => $data['pmUser_id'],
			));
			if (!empty($tmp[0]['Error_Msg'])) {
				//так-то нужно откатить транзакцию
				$response[0]['Error_Msg'] = $tmp[0]['Error_Msg'];
			}
		}

		$evnDrug = $this->loadEvnDrugView(array('EvnDrug_id' => $response[0]['EvnDrug_id']));
		if(count($evnDrug)>0 && !empty($evnDrug[0]['DocumentUcStr_id'])){
			$this->load->model('DocumentUc_model');
			if(empty($data['GoodsUnit_id'])){
				$data['GoodsUnit_id'] = null;
			}
			$tmp = $this->DocumentUc_model->saveObject('DocumentUcStr', array(
				'DocumentUcStr_id' => $evnDrug[0]['DocumentUcStr_id'],
				'pmUser_id' => $data['pmUser_id'],
				'GoodsUnit_id' => $data['GoodsUnit_id']
			));
			if (!empty($tmp[0]['Error_Msg'])) {
				//так-то нужно откатить транзакцию
				$response[0]['Error_Msg'] = $tmp[0]['Error_Msg'];
			} else if(!empty($tmp['DocumentUcStr_id']) && $is_merch_module) {
                // резервирование для документа Списание медикаментов со склада на пациента
                $tmp = $this->DocumentUc_model->reserveDrugOstatRegistryForDokRealPat(array(
                    'DocumentUcStr_id' => $tmp['DocumentUcStr_id'],
                    'pmUser_id' => $data['pmUser_id']
                ));
                if (!empty($tmp['Error_Msg'])) {
                    //так-то нужно откатить транзакцию
                    $response[0]['Error_Msg'] = $tmp['Error_Msg'];
                }
			}
		}

		if (!empty($response[0]['Error_Msg'])) {
			$this->rollbackTransaction();
		} else {
			$this->commitTransaction();
		}

		return $response;
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function MolIsAvailable($Mol_id, $setDate)
	{
		$query = "
			select
				Mol_id
			from
				Mol with (nolock)
			where
				Mol_id = :Mol_id and
				(Mol_endDT is null OR Mol_endDT >= :setDate)
		";
		$result = $this->getFirstResultFromQuery($query, array('Mol_id' => $Mol_id, 'setDate' => $setDate));

		return is_numeric($result) ? true : false;
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function checkUniDispWowUslugaType($data) {
		$filter = "(1=1)";
		$params = array();
		if ($data['EvnDrug_id'] > 0) {
			$filter .= " and ED.EvnUslugaWow_id != :EvnDrug_id";
			$params['EvnDrug_id'] = $data['EvnDrug_id'];
		}
		if ($data['EvnDrug_id'] > 0) {
			$filter .= " and ED.EvnDrug_pid = :EvnDrug_pid";
			$params['EvnDrug_pid'] = $data['EvnDrug_id'];
		}

		if ($data['DispWowUslugaType_id'] > 0) {
			$filter .= " and ED.DispWowUslugaType_id = :DispWowUslugaType_id";
			$params['DispWowUslugaType_id'] = $data['DispWowUslugaType_id'];
		}
		$sql = "
		Select
			count(*) as record_count
			from v_EvnUslugaWow ED with (nolock)
			where
				{$filter}
		";
		$params['Lpu_id'] = $data['Lpu_id'];
		$filter .= " and ED.Lpu_id = :Lpu_id";
		/*
		  echo getDebugSql($sql, $params);
		  exit;
		 */
		$res = $this->db->query($sql, $params);
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function checkUniWowCard($data) {
		$filter = "";
		$params = array();
		if ($data['EvnDrug_id'] > 0) {
			$filter .= " and EvnPLWow.EvnDrug_id != :EvnDrug_id";
			$params['EvnDrug_id'] = $data['EvnDrug_id'];
		}
		$params['Lpu_id'] = $data['Lpu_id'];
		if ($data['Person_id'] > 0) {
			$params['Person_id'] = $data['Person_id'];
		} else {
			return false;
		}
		$params['Lpu_id'] = $data['Lpu_id'];

		$sql = "
			Declare @Person_id as bigint = :Person_id;
			Declare @Lpu_id as bigint = :Lpu_id;
			Declare @NeLpu as int;
			Declare @Lpu as int;
			Declare @Lpu_Nick as char(100) = '';
			Select
				@NeLpu = Sum(case when EvnPLWow.Lpu_id!=@Lpu_id then 1 else 0 end),
				@Lpu = Sum(case when EvnPLWow.Lpu_id=@Lpu_id then 1 else 0 end)
			from v_EvnPLWow EvnPLWow with (nolock)
			where
				EvnPLWow.Person_id = @Person_id {$filter}

			if ((@Lpu=0) and (@NeLpu>0))
				Set @Lpu_Nick =
				(
					Select top 1 Lpu_Nick from v_EvnPLWow EvnPLWow with (nolock)
					inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = EvnPLWow.Lpu_id
					where EvnPLWow.Person_id = @Person_id and EvnPLWow.Lpu_id != @Lpu_id {$filter}
				)
			Select @NeLpu as NeLpu, @Lpu as Lpu, @Lpu_Nick as Lpu_Nick
		";

		$res = $this->db->query($sql, $params);
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}

	/**
	 *
	 * @param array $data
	 * @return boolean
	 */
	function checkDoublePerson($data) {
		$data['EvnDrug_id'] = 0;
		$result = $this->checkUniWowCard($data);
		if (is_array($result) && (count($result) > 0)) {
			if ($result[0]['Lpu'] > 0) {
				$result[0]['success'] = false;
				$result[0]['Error_Code'] = 100010;
				$result[0]['Error_Msg'] = 'На данного человека талон ВОВ уже занесен.';
			} elseif ($result[0]['NeLpu'] > 0) {
				$result[0]['success'] = true;
				$result[0]['Error_Code'] = 100011;
				$result[0]['Error_Msg'] = '<b>Обратите внимание!</b><br/> На данного человека талон заведен в ЛПУ: <br/>' . $result[0]['Lpu_Nick'];
			} else {
				$result[0]['success'] = true;
			}
			return $result;
		} else {
			return false;
		}
	}

	/**
	 *  Проверка на заполненность всех необходимых осмотров
	 */
	function checkIsVizit($data) {
		$filter = "(1=1)";
		$params = array();
		if ($data['EvnDrug_id'] > 0) {
			$params['EvnDrug_id'] = $data['EvnDrug_id'];
		} else {
			return false;
		}
		$sql = "
			Declare @Sex_id as bigint
			Declare @EvnPLWow_id as bigint = :EvnDrug_id;
			Declare @Lpu_id as bigint = :Lpu_id;

			Set @Sex_id = (Select Sex_id from v_EvnPLWow EvnPLWow with (nolock)
				inner join v_PersonState ps with (nolock) on EvnPLWow.Person_id = ps.Person_id
				where EvnPLWow.EvnPLWow_id = @EvnPLWow_id and EvnPLWow.Lpu_id = @Lpu_id)

			Select
			dws.DispWowSpec_id,
			dws.DispWowSpec_Name
			from DispWowSpec dws with (nolock)
			left join v_EvnVizitPLWow EvnVizit with (nolock) on dws.DispWowSpec_id = EvnVizit.DispWowSpec_id and EvnVizit.EvnVizitPLWow_pid = @EvnPLWow_id
			where
			EvnVizit.EvnVizitPLWow_id is null and
			((@Sex_id = 1 and dws.DispWowSpec_id in (1,2,3,4,5,6,8,9)) or
			(@Sex_id = 2 and dws.DispWowSpec_id in (1,2,3,4,5,6,7,8,9)))
			group by dws.DispWowSpec_id, dws.DispWowSpec_Name
		";
		$params['Lpu_id'] = $data['Lpu_id'];
		/*
		  echo getDebugSql($sql, $params);
		  exit;
		 */
		$res = $this->db->query($sql, $params);
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function checkIsUsluga($data) {
		$filter = "(1=1)";
		$params = array();
		if ($data['EvnDrug_id'] > 0) {
			$params['EvnDrug_id'] = $data['EvnDrug_id'];
		} else {
			return false;
		}
		$sql = "
			Declare @Sex_id as bigint
			Declare @Person_Age as int
			Declare @EvnPLWow_id as bigint = :EvnDrug_id;
			Declare @Lpu_id as bigint = :Lpu_id;

				Select @Sex_id = Sex_id, @Person_Age = EvnPLWow.Person_Age from v_EvnPLWow EvnPLWow with (nolock)
				inner join v_PersonState ps with (nolock) on EvnPLWow.Person_id = ps.Person_id
				where EvnPLWow.EvnPLWow_id = @EvnPLWow_id and EvnPLWow.Lpu_id = @Lpu_id

			Select
			dwut.DispWowUslugaType_id,
			dwut.DispWowUslugaType_Name
			from DispWowUslugaType dwut with (nolock)
			left join v_EvnUslugaWow EvnUsluga with (nolock) on dwut.DispWowUslugaType_id = EvnUsluga.DispWowUslugaType_id and EvnUsluga.EvnUslugaWow_pid = @EvnPLWow_id
			where
			EvnUsluga.EvnUslugaWow_id is null and
			((@Sex_id = 1 and dwut.DispWowUslugaType_id in (1,2,3,7,8,10,12,13,14,15,16)) or
			(@Sex_id = 2 and dwut.DispWowUslugaType_id in (1,2,3,4,5,6,8,9,10,11,12,13,14,15,16)))
			-- or (@Sex_id = 2 and dwut.DispWowUslugaType_id = 11 and @Person_Age>=45))
			group by dwut.DispWowUslugaType_id, dwut.DispWowUslugaType_Name
		";
		$params['Lpu_id'] = $data['Lpu_id'];
		/*
		  echo getDebugSql($sql, $params);
		  exit;
		 */
		$res = $this->db->query($sql, $params);
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Поиск талонов по ДД
	 */
	function searchEvnDrug($data) {
		$filter = "";
		$join_str = "";

		if ($data['PersonAge_Min'] > $data['PersonAge_Max']) {
			return false;
		}

		$params = array();

		if (($data['DocumentType_id'] > 0) || ($data['OrgDep_id'] > 0)) {
			$join_str .= " inner join [Document] with (nolock) on [Document].[Document_id] = [PS].[Document_id]";

			if ($data['DocumentType_id'] > 0) {
				$join_str .= " and [Document].[DocumentType_id] = :DocumentType_id";
				$params['DocumentType_id'] = $data['DocumentType_id'];
			}

			if ($data['OrgDep_id'] > 0) {
				$join_str .= " and [Document].[OrgDep_id] = :OrgDep_id";
				$params['OrgDep_id'] = $data['OrgDep_id'];
			}
		}

		if (($data['OMSSprTerr_id'] > 0) || ($data['OrgSmo_id'] > 0) || ($data['PolisType_id'] > 0)) {
			$join_str .= " inner join [Polis] with (nolock) on [Polis].[Polis_id] = [PS].[Polis_id]";

			if ($data['OMSSprTerr_id'] > 0) {
				$join_str .= " and [Polis].[OmsSprTerr_id] = :OMSSprTerr_id";
				$params['OMSSprTerr_id'] = $data['OMSSprTerr_id'];
			}

			if ($data['OrgSmo_id'] > 0) {
				$join_str .= " and [Polis].[OrgSmo_id] = :OrgSmo_id";
				$params['OrgSmo_id'] = $data['OrgSmo_id'];
			}

			if ($data['PolisType_id'] > 0) {
				$join_str .= " and [Polis].[PolisType_id] = :PolisType_id";
				$params['PolisType_id'] = $data['PolisType_id'];
			}
		}

		if (($data['Org_id'] > 0) || ($data['Post_id'] > 0)) {
			$join_str .= " inner join [Job] with (nolock) on [Job].[Job_id] = [PS].[Job_id]";

			if ($data['Org_id'] > 0) {
				$join_str .= " and [Job].[Org_id] = :Org_id";
				$params['Org_id'] = $data['Org_id'];
			}

			if ($data['Post_id'] > 0) {
				$join_str .= " and [Job].[Post_id] = :Post_id";
				$params['Post_id'] = $data['Post_id'];
			}
		}

		if (($data['KLRgn_id'] > 0) || ($data['KLSubRgn_id'] > 0) || ($data['KLCity_id'] > 0) || ($data['KLTown_id'] > 0) || ($data['KLStreet_id'] > 0) || (strlen($data['Address_House']) > 0)) {
			$join_str .= " inner join [Address] with (nolock) on [Address].[Address_id] = [PS].[UAddress_id]";

			if ($data['KLRgn_id'] > 0) {
				$filter .= " and [Address].[KLRgn_id] = :KLRgn_id";
				$params['KLRgn_id'] = $data['KLRgn_id'];
			}

			if ($data['KLSubRgn_id'] > 0) {
				$filter .= " and [Address].[KLSubRgn_id] = :KLSubRgn_id";
				$params['KLSubRgn_id'] = $data['KLSubRgn_id'];
			}

			if ($data['KLCity_id'] > 0) {
				$filter .= " and [Address].[KLCity_id] = :KLCity_id";
				$params['KLCity_id'] = $data['KLCity_id'];
			}

			if ($data['KLTown_id'] > 0) {
				$filter .= " and [Address].[KLTown_id] = :KLTown_id";
				$params['KLTown_id'] = $data['KLTown_id'];
			}

			if ($data['KLStreet_id'] > 0) {
				$filter .= " and [Address].[KLStreet_id] = :KLStreet_id";
				$params['KLStreet_id'] = $data['KLStreet_id'];
			}

			if (strlen($data['Address_House']) > 0) {
				$filter .= " and [Address].[Address_House] = :Address_House";
				$params['Address_House'] = $data['Address_House'];
			}
		}

		if (isset($data['EvnDrug_disDate'][1])) {
			$filter .= " and [EvnDrug].[EvnDrug_disDate] <= :EvnDrug_disDate1";
			$params['EvnDrug_disDate1'] = $data['EvnDrug_disDate'][1];
		}

		if (isset($data['EvnDrug_disDate'][0])) {
			$filter .= " and [EvnDrug].[EvnDrug_disDate] >= :EvnDrug_disDate1";
			$params['EvnDrug_disDate0'] = $data['EvnDrug_disDate'][0];
		}

		if ($data['EvnDrug_IsFinish'] > 0) {
			$filter .= " and [EvnDrug].[EvnDrug_IsFinish] = :EvnDrug_IsFinish";
			$params['EvnDrug_IsFinish'] = $data['EvnDrug_IsFinish'];
		}

		if (isset($data['EvnDrug_setDate'][1])) {
			$filter .= " and [EvnDrug].[EvnDrug_setDate] <= :EvnDrug_setDate1";
			$params['EvnDrug_setDate1'] = $data['EvnDrug_setDate'][1];
		}

		if (isset($data['EvnDrug_setDate'][0])) {
			$filter .= " and [EvnDrug].[EvnDrug_setDate] >= :EvnDrug_setDate0";
			$params['EvnDrug_setDate0'] = $data['EvnDrug_setDate'][0];
		}

		if ($data['PersonAge_Max'] > 0) {
			$filter .= " and [EvnDrug].[Person_Age] <= :PersonAge_Max";
			$params['PersonAge_Max'] = $data['PersonAge_Max'];
		}

		if ($data['PersonAge_Min'] > 0) {
			$filter .= " and [EvnDrug].[Person_Age] >= :PersonAge_Min";
			$params['PersonAge_Min'] = $data['PersonAge_Min'];
		}

		if (($data['PersonCard_Code'] != '') || ($data['LpuRegion_id'] > 0)) {
			$join_str .= " inner join [v_PersonCard] PC with (nolock) on [PC].[Person_id] = [PS].[Person_id]";

			if (strlen($data['PersonCard_Code']) > 0) {
				$filter .= " and [PC].[PersonCard_Code] = :PersonCard_Code";
				$params['PersonCard_Code'] = $data['PersonCard_Code'];
			}

			if (strlen($data['LpuRegion_id']) > 0) {
				$filter .= " and [PC].[LpuRegion_id] = :LpuRegion_id";
				$params['LpuRegion_id'] = $data['LpuRegion_id'];
			}
		}
		if (isset($data['Person_Birthday'][1])) {
			$filter .= " and [PS].[Person_Birthday] <= :Person_Birthday1";
			$params['Person_Birthday1'] = $data['Person_Birthday'][1];
		}

		if (isset($data['Person_Birthday'][0])) {
			$filter .= " and [PS].[Person_Birthday] >= :Person_Birthday0";
			$params['Person_Birthday0'] = $data['Person_Birthday'][0];
		}

		if (strlen($data['Person_Firname']) > 0) {
			$filter .= " and [PS].[Person_Firname] like :Person_Firname";
			$params['Person_Firname'] = $data['Person_Firname'] . "%";
		}

		if (strlen($data['Person_Secname']) > 0) {
			$filter .= " and [PS].[Person_Secname] like :Person_Secname";
			$params['Person_Secname'] = $data['Person_Secname'] . "%";
		}

		if ($data['Person_Snils'] > 0) {
			$filter .= " and [PS].[Person_Snils] = :Person_Snils";
			$params['Person_Snils'] = $data['Person_Snils'];
		}

		if (strlen($data['Person_Surname']) > 0) {
			$filter .= " and [PS].[Person_Surname] like :Person_Surname";
			$params['Person_Surname'] = $data['Person_Surname'] . "%";
		}

		if ($data['PrivilegeType_id'] > 0) {
			$join_str .= " inner join [v_PersonPrivilege] [PP] with (nolock) on [PP].[Person_id] = [EvnDrug].[Person_id] and [PP].[PrivilegeType_id] = :PrivilegeType_id and [PP].[PersonPrivilege_begDate] is not null and [PP].[PersonPrivilege_begDate] <= dbo.tzGetDate() and ([PP].[PersonPrivilege_endDate] is null or [PP].[PersonPrivilege_endDate] >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime)) and [PP].[Lpu_id] = :Lpu_id";
			$params['PrivilegeType_id'] = $data['PrivilegeType_id'];
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		if ($data['Sex_id'] >= 0) {
			$filter .= " and [PS].[Sex_id] = :Sex_id";
			$params['Sex_id'] = $data['Sex_id'];
		}

		if ($data['SocStatus_id'] > 0) {
			$filter .= " and [PS].[SocStatus_id] = :SocStatus_id";
			$params['SocStatus_id'] = $data['SocStatus_id'];
		}

		$query = "
			SELECT DISTINCT TOP 100
				[EvnDrug].[EvnDrug_id] as [EvnDrug_id],
				[EvnDrug].[Person_id] as [Person_id],
				[EvnDrug].[Server_id] as [Server_id],
				[EvnDrug].[PersonEvn_id] as [PersonEvn_id],
				RTRIM([PS].[Person_Surname]) as [Person_Surname],
				RTRIM([PS].[Person_Firname]) as [Person_Firname],
				RTRIM([PS].[Person_Secname]) as [Person_Secname],
				convert(varchar(10), [PS].[Person_Birthday], 104) as [Person_Birthday],
				[EvnDrug].[EvnDrug_VizitCount] as [EvnDrug_VizitCount],
				[IsFinish].[YesNo_Name] as [EvnDrug_IsFinish],
				convert(varchar(10), [EvnDrug].[EvnDrug_setDate], 104) as [EvnDrug_setDate],
				convert(varchar(10), [EvnDrug].[EvnDrug_disDate], 104) as [EvnDrug_disDate]
			FROM [v_EvnDrug] [EvnDrug] with (nolock)
				inner join [v_PersonState] [PS] with (nolock) on [PS].[Person_id] = [EvnDrug].[Person_id]
				left join [YesNo] [IsFinish] with (nolock) on [IsFinish].[YesNo_id] = [EvnDrug].[EvnDrug_IsFinish]
				" . $join_str . "
			WHERE (1 = 1)
				" . $filter . "
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка записей для потокового ввода
	 */
	function getEvnDrugStreamList($data) {

		$query = "
			SELECT DISTINCT TOP 100
				[EvnDrug].[EvnDrug_id] as [EvnDrug_id],
				[EvnDrug].[Person_id] as [Person_id],
				[EvnDrug].[Server_id] as [Server_id],
				[EvnDrug].[PersonEvn_id] as [PersonEvn_id],
				RTRIM([PS].[Person_Surname]) + ' ' + RTRIM([PS].[Person_Firname]) + ' ' + RTRIM([PS].[Person_Secname]) as [Person_Fio],
				convert(varchar(10), [PS].[Person_Birthday], 104) as [Person_Birthday],
				[EvnDrug].[EvnDrug_VizitCount] as [EvnDrug_VizitCount],
				[IsFinish].[YesNo_Name] as [EvnDrug_IsFinish],
				convert(varchar(10), [EvnDrug].[EvnDrug_setDate], 104) as [EvnDrug_setDate],
				convert(varchar(10), [EvnDrug].[EvnDrug_disDate], 104) as [EvnDrug_disDate]
			FROM [v_EvnDrug] [EvnDrug] with (nolock)
				inner join [v_PersonState] [PS] with (nolock) on [PS].[Person_id] = [EvnDrug].[Person_id]
				left join [YesNo] [IsFinish] with (nolock) on [IsFinish].[YesNo_id] = [EvnDrug].[EvnDrug_IsFinish]
			WHERE EvnDrug_updDT >= ? and [EvnDrug].pmUser_updID= ? ";

		$result = $this->db->query($query, array($data['begDate'] . " " . $data['begTime'], $data['pmUser_id']));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка лет, в которые выписывались талоны по ДД с количеством талонов, для комбобокса
	 */
	function getEvnDrugYears($data) {
		$sql = "
			SELECT
				count(EvnDrug_id) as count,
				year(EvnDrug_setDate) as EvnDrug_Year
			FROM
				v_EvnDrug with (nolock)
			WHERE
				Lpu_id = ?
			GROUP BY
				year(EvnDrug_setDate)
			ORDER BY
				year(EvnDrug_setDate)
		";

		$res = $this->db->query($sql, array($data['Lpu_id']));
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Получение списка лекарственных назначений. Метод для API
	 */
	function getEvnDrugListForAPI($data) {
		$params = array('Evn_pid' => $data['Evn_pid']);
		$filter ='';
		if (!empty($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filter .= " and ED.Lpu_id = :Lpu_id";
		}
		$query = "
			select
				ED.EvnDrug_id as Evn_id,
				ED.EvnDrug_pid as Evn_pid,
				convert(varchar(10), ED.EvnDrug_setDT, 120)+' '+convert(varchar(8), ED.EvnDrug_setDT, 108) as Evn_setDT,
				ED.LpuSection_id,
				ED.Mol_id,
				ED.Drug_id,
				ED.EvnDrug_Kolvo,
				ED.EvnDrug_KolvoEd
			from v_EvnDrug ED with(nolock)
			where ED.EvnDrug_pid = :Evn_pid
			{$filter}
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение лекарственного назначения. Метод для API
	 */
	function getEvnDrugForAPI($data) {
		$params = array();
		$filters = array();

		if (!empty($data['Evn_id'])) {
			$filters[] = "ED.EvnDrug_id = :Evn_id";
			$params['Evn_id'] = $data['Evn_id'];
		}
		if (!empty($data['Evn_pid'])) {
			$filters[] = "ED.EvnDrug_pid = :Evn_pid";
			$params['Evn_pid'] = $data['Evn_pid'];
		}
		if (!empty($data['Evn_setDT'])) {
			$filters[] = "ED.EvnDrug_setDT = :Evn_setDT";
			$params['Evn_setDT'] = $data['Evn_setDT'];
		}
		if (!empty($data['Drug_id'])) {
			$filters[] = "ED.Drug_id = :Drug_id";
			$params['Drug_id'] = $data['Drug_id'];
		}
		if (!empty($data['Lpu_id'])) {
			$filters[] = "ED.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		$filter_str = implode(" and ", $filters);
		$query = "
			select
				ED.EvnDrug_id as Evn_id,
				ED.EvnDrug_pid as Evn_pid,
				convert(varchar(10), ED.EvnDrug_setDT, 120)+' '+convert(varchar(8), ED.EvnDrug_setDT, 108) as Evn_setDT,
				ED.LpuSection_id,
				ED.Mol_id,
				ED.Drug_id,
				ED.EvnDrug_Kolvo,
				ED.EvnDrug_KolvoEd
			from
				v_EvnDrug ED with(nolock)
			where
				{$filter_str}
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение данных для редактирования лекарственного назначения
	 */
	function getEvnDrugInfoForAPI($data) {
		$params = array('Evn_id' => $data['Evn_id']);
		$query = "
			select top 1
				ED.EvnDrug_id as Evn_id,
				ED.EvnDrug_pid as Evn_pid,
				convert(varchar(10), ED.EvnDrug_setDT, 120)+' '+convert(varchar(8), ED.EvnDrug_setDT, 108) as Evn_setDT,
				ED.Lpu_id,
				ED.Server_id,
				ED.Person_id,
				ED.PersonEvn_id,
				ED.Drug_id,
				ED.LpuSection_id,
				ED.DocumentUc_id,
				ED.DocumentUcStr_oid,
				ED.Mol_id,
				ED.EvnDrug_Kolvo,
				ED.EvnDrug_KolvoEd,
				ED.EvnDrug_Price,
				isnull(ED.GoodsUnit_id, GU.GoodsUnit_id) as GoodsUnit_id
			from
				v_EvnDrug ED with(nolock)
				left join rls.v_Drug D with(nolock) on D.Drug_id = ED.Drug_id
				outer apply (
					select top 1 GPC.GoodsUnit_id
					from v_GoodsPackCount GPC with (nolock)
					where GPC.DrugComplexMnn_id = D.DrugComplexMnn_id
				) GU
			where
				ED.EvnDrug_id = :Evn_id
		";
		return $this->getFirstRowFromQuery($query, $params);
	}

    /**
     * Получение идентификатора первой строки исполненного документа учета, связанной с EvnDrug
     */
    function getExecutedDocumentUcStrForEvnDrug($evndrug_id) {
        $query = "
            select top 1
                dus.DocumentUcStr_id
            from
                v_DocumentUcStr dus with (nolock)
                left join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
            where
                dus.EvnDrug_id = :EvnDrug_id and
                (
                    du.DrugDocumentStatus_id in (2, 12) or -- 2 - Исполнен, 12 - На исполнении
                    dus.DrugDocumentStatus_id = 2
                )
            order by
                dus.DocumentUcStr_id;
        ";
        $dus_id = $this->getFirstResultFromQuery($query, array(
            'EvnDrug_id' => $evndrug_id
        ));
        return $dus_id > 0 ? $dus_id : null;
    }

	/**
	 *  Получение списка медикаментов для панели использования медикаментов в ЭМК
	 */
	function loadEvnDrugPanel($data)
	{
		return $this->queryResult("
			select
				ed.EvnDrug_id,
				d.Drug_Code,
				ed.PersonEvn_id,
				ed.Person_id,
				ed.Server_id,
				d.DrugTorg_Name as Drug_Name,
				convert(varchar(10), ED.EvnDrug_setDate, 104) as EvnDrug_setDate,
				cast(ROUND(ED.EvnDrug_Kolvo, 4) as numeric (20,4)) as EvnDrug_Kolvo
			from
				v_EvnDrug ed (nolock)
				left join rls.v_Drug d (nolock) on d.Drug_id = ed.Drug_id
			where
				ed.EvnDrug_pid = :EvnDrug_pid
		", $data);
	}
}

?>