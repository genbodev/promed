<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * WhsDocumentUc_model - модель для работы документами учета
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Farmacy
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			11.03.2016
 */

class WhsDocumentUc_model extends swModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение сгенерированного номера для документа учета
	 */
	function generateWhsDocumentUcNum($data) {
		$where = array();
		$params = array();

        if (!empty($data['WhsDocumentType_Code'])) {
            $where[] = "wdt.WhsDocumentType_Code = :WhsDocumentType_Code";
            $params['WhsDocumentType_Code'] = $data['WhsDocumentType_Code'];
        }
        if (!empty($data['Ins_Year'])) {
            $where[] = "datepart(year, wdu.WhsDocumentUc_insDT) = :Ins_Year";
            $params['Ins_Year'] = $data['Ins_Year'];
        }
        if (isset($data['Org_aid'])) {
            $where[] = "isnull(wdu.Org_aid, 0) = isnull(:Org_aid, 0)";
            $params['Org_aid'] = $data['Org_aid'];
        }

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause)) {
            $where_clause = ' and '.$where_clause;
        }

		$query = "
			select
				isnull(max(cast(wdu.WhsDocumentUc_Num as bigint)), 0)+1 as num
			from
				v_WhsDocumentUc wdu with (nolock)
				left join v_WhsDocumentType wdt with (nolock) on wdt.WhsDocumentType_id = wdu.WhsDocumentType_id
			where
				wdu.WhsDocumentUc_Num not like '%.%' and
				wdu.WhsDocumentUc_Num not like '%,%' and
				wdu.WhsDocumentUc_Num not like '%e%' and
				len(wdu.WhsDocumentUc_Num) <= 18 and
				isnumeric(wdu.WhsDocumentUc_Num + 'e0') = 1
				{$where_clause};
		";
		$num = $this->getFirstResultFromQuery($query, $params);

		if (!empty($num)) {
			return array(array('WhsDocumentUc_Num' => $num));
		} else {
			return false;
		}
	}

	/**
	 * Получение списка лекарственных назначений
	 */
	function loadEvnCourseTreatList($data) {
		$filters = "1=1";
		$joins = "";
		$params = array();

		if (!empty($data['DrugComplexMnn_id'])) {
			$filters .= " and ECTD.DrugComplexMnn_id = :DrugComplexMnn_id";
			$params['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];
		}
		if (!empty($data['Tradenames_id'])) {
			$joins .= "
				left join rls.v_Drug D with(nolock) on D.Drug_id = ECTD.Drug_id
			";
			$filters .= " and D.DrugTorg_id = :Tradenames_id";
			$params['Tradenames_id'] = $data['Tradenames_id'];
		}
		if (!empty($data['MedService_id'])) {
			/*$joins .= "
				left join v_LpuSection LS with(nolock) on LS.LpuSection_id = ES.LpuSection_id
				left join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuUnitType LUT with(nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
				left join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id =LU.LpuBuilding_id
				left join v_Lpu L with(nolock) on L.Lpu_id = LB.Lpu_id
			";
			$filters .= " and :MedService_id in (
				select MS.MedService_id
				from v_MedService MS with(nolock)
				where
					isnull(MS.Lpu_id, L.Lpu_id) = L.Lpu_id
					and isnull(MS.LpuBuilding_id, LB.LpuBuilding_id) = LB.LpuBuilding_id
					and isnull(MS.LpuUnitType_id, LUT.LpuUnitType_id) = LUT.LpuUnitType_id
					and isnull(MS.LpuUnit_id, LU.LpuUnit_id) = LU.LpuUnit_id
					and isnull(MS.LpuSection_id, LS.LpuSection_id) = LS.LpuSection_id
			)";*/
			$filters .= "
				and ES.LpuSection_id in (
					select
						i_ls_ssl.LpuSection_id
					from
						v_StorageStructLevel i_ssl with (nolock)
						left join v_Storage i_s with (nolock) on i_s.Storage_id = i_ssl.Storage_id or i_s.Storage_pid = i_ssl.Storage_id -- склады прописаны на службе и их дочерние склады
						outer apply ( -- проверка не связаны ли дочерние склады со службой с типом АРМ товароведа
							select top 1
								ii_ms.MedService_id
							from
								v_StorageStructLevel ii_ssl with (nolock)
								left join v_MedService ii_ms with (nolock) on ii_ms.MedService_id = ii_ssl.MedService_id			
								left join v_MedServiceType ii_mst with (nolock) on ii_mst.MedServiceType_id = ii_ms.MedServiceType_id
							where
								i_s.Storage_id <> i_ssl.Storage_id and
								ii_ssl.Storage_id = i_s.Storage_id and
								ii_mst.MedServiceType_SysNick = 'merch'
						) i_ms
						left join v_StorageStructLevel i_ls_ssl with (nolock) on i_ls_ssl.Storage_id = i_s.Storage_id -- получаем связь с отделениями для полученой совокупности складов
					where
						i_ssl.MedService_id = :MedService_id and
						i_ms.MedService_id is null and
						i_ls_ssl.LpuSection_id is not null
				)
			";
			$params['MedService_id'] = $data['MedService_id'];
		}

		$query = "
			select distinct top 100
				PS.Person_id,
				rtrim(PS.Person_SurName)+' '+rtrim(PS.Person_FirName)+rtrim(ISNULL(' '+PS.Person_SecName,'')) as Person_Fio,
				EPS.EvnPS_NumCard,
				ECT.EvnCourseTreat_id,
				convert(varchar(10), ECT.EvnCourseTreat_setDate, 104) as EvnCourseTreat_setDate,
				ECT.EvnCourseTreat_Duration
			from
				v_EvnCourseTreatDrug ECTD with(nolock)
				inner join v_EvnCourseTreat ECT with(nolock) on ECT.EvnCourseTreat_id = ECTD.EvnCourseTreat_id
				inner join v_EvnSection ES with(nolock) on ES.EvnSection_id = ECT.EvnCourseTreat_pid
				inner join v_EvnPS EPS with(nolock) on EPS.EvnPS_id = ES.EvnSection_pid
				inner join v_PersonState PS with(nolock) on PS.Person_id = ES.Person_id
				{$joins}
			where
				{$filters}
			order by
				Person_Fio,
				EvnCourseTreat_setDate
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка рецептов из реестра рецептов аптеки
	 */
	function loadReceptOtovList($data) {
		$filters = "";
		$params = array();

		if (!empty($data['Org_id'])) {
			$OrgFarmacy_id = $this->getFirstResultFromQuery("
				select top 1 OrgFarmacy_id from OrgFarmacy with(nolock) where Org_id = :Org_id
			", array('Org_id' => $data['Org_id']));

			$filters .= " and RO.OrgFarmacy_id = :OrgFarmacy_id";
			$params['OrgFarmacy_id'] = $OrgFarmacy_id;
		}
		if (!empty($data['DrugComplexMnn_id'])) {
			$filters .= " and ER.DrugComplexMnn_id = :DrugComplexMnn_id";
			$params['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];
		}

		$query = "
			select top 100
				RO.Person_id,
				RO.Person_Fio,
				RO.ReceptOtov_id,
				ER.EvnRecept_id,
				ER.EvnRecept_Ser,
				ER.EvnRecept_Num,
				convert(varchar(10), ER.EvnRecept_setDate, 104) as EvnRecept_setDate
			from
				v_ReceptOtov_all RO with(nolock)
				inner join v_EvnRecept ER with(nolock) on ER.EvnRecept_id = RO.EvnRecept_id
			where
				RO.ReceptDelayType_id = 2
				{$filters}
			order by
				Person_Fio,
				EvnRecept_setDate
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Сохранение документа учета
	 */
	function saveWhsDocumentUc($data) {
		if (empty($data['WhsDocumentClass_id']) && !empty($data['WhsDocumentClass_Code'])) {
            $WhsDocumentClass_id = $this->getObjectIdByCode('WhsDocumentClass', $data['WhsDocumentClass_Code']);
			if (empty($WhsDocumentClass_id)) {
				return $this->createError('', 'Ошибка при получении идентфикатора класса документа по коду');
			}
			$data['WhsDocumentClass_id'] = $WhsDocumentClass_id;
		}

        $wdu_data = array(
            'WhsDocumentUc_Num' => null,
            'WhsDocumentStatusType_id' => null
        );
		if (!empty($data['WhsDocumentUc_id'])) { //если документ уже существет, получаем его данные
            $query = "
                select
                    wdu.WhsDocumentUc_id,
                    wdu.WhsDocumentStatusType_id,
                    wdu.WhsDocumentUc_Num
                from
                    v_WhsDocumentUc wdu with(nolock)
                where
                     wdu.WhsDocumentUc_id = :WhsDocumentUc_id;
            ";
            $wdu_data = $this->getFirstRowFromQuery($query, array(
                'WhsDocumentUc_id' => $data['WhsDocumentUc_id']
            ));
			if (empty($wdu_data['WhsDocumentUc_id'])) {
				return $this->createError('', 'Ошибка при получении данных документа');
			}
		} else {
            $num_data = $this->generateWhsDocumentUcNum(array(
                'WhsDocumentType_Code' => 22,
                'Ins_Year' => date("Y"),
                'Org_aid' => $data['Org_tid']
            ));
            if ($num_data !== false && !empty($num_data[0]['WhsDocumentUc_Num'])) {
                $wdu_data['WhsDocumentUc_Num'] = $num_data[0]['WhsDocumentUc_Num'];
            } else {
                return $this->createError('', 'Ошибка при генерации номера документа');
            }
        }

		$params = array(
			'WhsDocumentUc_id' => !empty($data['WhsDocumentUc_id'])?$data['WhsDocumentUc_id']:null,
			'WhsDocumentUc_pid' => !empty($data['WhsDocumentUc_pid'])?$data['WhsDocumentUc_pid']:null,//???
			'WhsDocumentUc_Num' => $wdu_data['WhsDocumentUc_Num'],
			'WhsDocumentUc_Name' => $wdu_data['WhsDocumentUc_Num'],
			'WhsDocumentType_id' => 22, //Заявка
			'WhsDocumentUc_Date' => $data['WhsDocumentUc_Date'],
			'WhsDocumentUc_Sum' => !empty($data['WhsDocumentUc_Sum'])?$data['WhsDocumentUc_Sum']:null,
			'WhsDocumentStatusType_id' => $data['WhsDocumentStatusType_id'],
			'Org_aid' => $data['Org_tid'],
			'WhsDocumentClass_id' => $data['WhsDocumentClass_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		if (empty($params['WhsDocumentUc_id'])) {
			$procedure = 'p_WhsDocumentUc_ins';
		} else {
			$procedure = 'p_WhsDocumentUc_upd';
		}

		$query = "
			declare
				@WhsDocumentUc_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @WhsDocumentUc_id = :WhsDocumentUc_id;
			exec {$procedure}
				@WhsDocumentUc_id = @WhsDocumentUc_id output,
				@WhsDocumentUc_pid = :WhsDocumentUc_pid,
				@WhsDocumentUc_Num = :WhsDocumentUc_Num,
				@WhsDocumentUc_Name = :WhsDocumentUc_Name,
				@WhsDocumentType_id = :WhsDocumentType_id,
				@WhsDocumentUc_Date = :WhsDocumentUc_Date,
				@WhsDocumentUc_Sum = :WhsDocumentUc_Sum,
				@WhsDocumentStatusType_id = :WhsDocumentStatusType_id,
				@Org_aid = :Org_aid,
				@WhsDocumentClass_id = :WhsDocumentClass_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @WhsDocumentUc_id as WhsDocumentUc_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$this->beginTransaction();

		$response = $this->queryResult($query, $params);
		if (!$this->isSuccessful($response)) {
			$this->rollbackTransaction();
			return $response;
		}

		if ($wdu_data['WhsDocumentStatusType_id'] != $data['WhsDocumentStatusType_id']) { //если статус изменился
			$resp = $this->addWhsDocumentStatusHistory(array(
				'WhsDocumentUc_id' => $response[0]['WhsDocumentUc_id'],
				'WhsDocumentStatusType_id' => $data['WhsDocumentStatusType_id'],
				'pmUser_id' => $data['pmUser_id']
			));
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		$params = array(
			'WhsDocumentSpecificity_id' => !empty($data['WhsDocumentSpecificity_id'])?$data['WhsDocumentSpecificity_id']:null,
			'WhsDocumentUc_id' => $response[0]['WhsDocumentUc_id'],
			'WhsDocumentStatusType_id' => $data['WhsDocumentStatusType_id'],
			'WhsDocumentSpecificity_setDate' => $data['WhsDocumentUc_Date'],
			'WhsDocumentSpecificity_disDate' => null,
			'Org_sid' => $data['Org_sid'],
			'Org_tid' => $data['Org_tid'],
			'Storage_sid' => !empty($data['Storage_sid'])?$data['Storage_sid']:null,
			'Storage_tid' => !empty($data['Storage_tid'])?$data['Storage_tid']:null,
			'WhsDocumentSupply_id' => !empty($data['WhsDocumentSupply_id'])?$data['WhsDocumentSupply_id']:null,
			'DrugFinance_id' => !empty($data['DrugFinance_id'])?$data['DrugFinance_id']:null,
			'WhsDocumentCostItemType_id' => !empty($data['WhsDocumentCostItemType_id'])?$data['WhsDocumentCostItemType_id']:null,
			'pmUser_id' => $data['pmUser_id']
		);

		if (empty($params['WhsDocumentSpecificity_id'])) {
			$procedure = 'p_WhsDocumentSpecificity_ins';
		} else {
			$procedure = 'p_WhsDocumentSpecificity_upd';
		}

		$query = "
			declare
				@WhsDocumentSpecificity_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @WhsDocumentSpecificity_id = :WhsDocumentSpecificity_id;
			exec {$procedure}
				@WhsDocumentSpecificity_id = @WhsDocumentSpecificity_id output,
				@WhsDocumentUc_id = :WhsDocumentUc_id,
				@WhsDocumentStatusType_id = :WhsDocumentStatusType_id,
				@WhsDocumentSpecificity_setDate = :WhsDocumentSpecificity_setDate,
				@WhsDocumentSpecificity_disDate = :WhsDocumentSpecificity_disDate,
				@Org_sid = :Org_sid,
				@Org_tid = :Org_tid,
				@Storage_sid = :Storage_sid,
				@Storage_tid = :Storage_tid,
				@WhsDocumentSupply_id = :WhsDocumentSupply_id,
				@DrugFinance_id = :DrugFinance_id,
				@WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @WhsDocumentSpecificity_id as WhsDocumentSpecificity_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$resp = $this->queryResult($query, $params);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}
		$response[0]['WhsDocumentSpecificity_id'] = $resp[0]['WhsDocumentSpecificity_id'];

		$WhsDocumentSpecificationList = array();
		if (!empty($data['WhsDocumentSpecificationJSON'])) {
			$WhsDocumentSpecificationList = json_decode($data['WhsDocumentSpecificationJSON'], true, 512, JSON_BIGINT_AS_STRING);
		}
		foreach($WhsDocumentSpecificationList as $WhsDocumentSpecification) {
			$WhsDocumentSpecification['WhsDocumentSpecificity_id'] = $response[0]['WhsDocumentSpecificity_id'];
			$WhsDocumentSpecification['pmUser_id'] = $data['pmUser_id'];

			$resp = array();
			switch($WhsDocumentSpecification['state']) {
				case 'add':
					$WhsDocumentSpecification['WhsDocumentSpecification_id'] = null;
					$resp = $this->saveWhsDocumentSpecification($WhsDocumentSpecification);
					break;

				case 'edit':
					$resp = $this->saveWhsDocumentSpecification($WhsDocumentSpecification);
					break;

				case 'delete':
					$resp = $this->deleteWhsDocumentSpecification($WhsDocumentSpecification);
					break;
			}
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		$this->commitTransaction();

		return $response;
	}

	/**
	 * Добавление записи в историю изменения статусов документа учета
	 */
	function addWhsDocumentStatusHistory($data) {
		$params = array(
			'WhsDocumentUc_id' => $data['WhsDocumentUc_id'],
			'WhsDocumentStatusType_id' => $data['WhsDocumentStatusType_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$query = "
			declare
				@WhsDocumentStatusHistory_id bigint,
				@WhsDocumentStatusHistory_setDate datetime,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @WhsDocumentStatusHistory_setDate = (select dbo.tzGetDate());
			exec p_WhsDocumentStatusHistory_ins
				@WhsDocumentStatusHistory_id = @WhsDocumentStatusHistory_id output,
				@WhsDocumentStatusHistory_setDate = @WhsDocumentStatusHistory_setDate,
				@WhsDocumentUc_id = :WhsDocumentUc_id,
				@WhsDocumentStatusType_id = :WhsDocumentStatusType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @WhsDocumentStatusHistory_id as WhsDocumentStatusHistory_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при сохранении истории изменения статуса документа');
		}

		return $response;
	}

	/**
	 * Сохранение медикамента в спецификации документа учета
	 */
	function saveWhsDocumentSpecification($data) {
		$params = array(
			'WhsDocumentSpecification_id' => !empty($data['WhsDocumentSpecification_id']) ? $data['WhsDocumentSpecification_id'] : null,
			'WhsDocumentSpecificity_id' => $data['WhsDocumentSpecificity_id'],
			'DrugComplexMnn_id' => $data['DrugComplexMnn_id'],
			'Tradenames_id' => !empty($data['Tradenames_id']) ? $data['Tradenames_id'] : null,
			'WhsDocumentSpecification_Method' => !empty($data['WhsDocumentSpecification_Method']) ? $data['WhsDocumentSpecification_Method'] : null,
			'EvnCourseTreat_id' => !empty($data['EvnCourseTreat_id']) ? $data['EvnCourseTreat_id'] : null,
			'ReceptOtov_id' => !empty($data['ReceptOtov_id']) ? $data['ReceptOtov_id'] : null,
			'DrugRequestRow_id' => !empty($data['DrugRequestRow_id']) ? $data['DrugRequestRow_id'] : null,
			'Okei_id' => !empty($data['Okei_id']) ? $data['Okei_id'] : null,
			'WhsDocumentSpecification_Count' => !empty($data['WhsDocumentSpecification_Count']) ? $data['WhsDocumentSpecification_Count'] : null,
			'WhsDocumentSpecification_Cost' => !empty($data['WhsDocumentSpecification_Cost']) ? $data['WhsDocumentSpecification_Cost'] : null,
			'GoodsUnit_id' => !empty($data['GoodsUnit_id']) ? $data['GoodsUnit_id'] : null,
			'Drug_id' => !empty($data['Drug_id']) ? $data['Drug_id'] : null,
			'pmUser_id' => $data['pmUser_id']
		);

		if (empty($data['WhsDocumentSpecification_id'])) {
			$procedure = 'p_WhsDocumentSpecification_ins';
		} else {
			$procedure = 'p_WhsDocumentSpecification_upd';
		}

		$query = "
			declare
				@WhsDocumentSpecification_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @WhsDocumentSpecification_id = :WhsDocumentSpecification_id;
			exec {$procedure}
				@WhsDocumentSpecification_id = @WhsDocumentSpecification_id output,
				@WhsDocumentSpecificity_id = :WhsDocumentSpecificity_id,
				@DrugComplexMnn_id = :DrugComplexMnn_id,
				@Tradenames_id = :Tradenames_id,
				@WhsDocumentSpecification_Method = :WhsDocumentSpecification_Method,
				@EvnCourseTreat_id = :EvnCourseTreat_id,
				@ReceptOtov_id = :ReceptOtov_id,
				@DrugRequestRow_id = :DrugRequestRow_id,
				@Okei_id = :Okei_id,
				@WhsDocumentSpecification_Count = :WhsDocumentSpecification_Count,
				@WhsDocumentSpecification_Cost = :WhsDocumentSpecification_Cost,
				@GoodsUnit_id = :GoodsUnit_id,
				@Drug_id = :Drug_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @WhsDocumentSpecification_id as WhsDocumentSpecification_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при сохранении спецификации медикамента в документе учета');
		}

		return $response;
	}

	/**
	 * Удаление медикамента из спецификации документа учета
	 */
	function deleteWhsDocumentSpecification($data) {
		$params = array('WhsDocumentSpecification_id' => $data['WhsDocumentSpecification_id']);

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_WhsDocumentSpecification_del
				@WhsDocumentSpecification_id = :WhsDocumentSpecification_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при удалении спецификации медикамента из документе учета');
		}

		return $response;
	}

	/**
	 * Получение данных для редактирования докумкента учета
	 */
	function loadWhsDocumentUcForm($data) {
		$params = array('WhsDocumentUc_id' => $data['WhsDocumentUc_id']);

		$query = "
			select top 1
				WDU.WhsDocumentUc_id,
				WDU.WhsDocumentUc_Num,
				convert(varchar(10), WDU.WhsDocumentUc_Date, 104) as WhsDocumentUc_Date,
				WDU.WhsDocumentStatusType_id,
				WDC.WhsDocumentClass_id,
				WDC.WhsDocumentClass_Code,
				WDS.WhsDocumentSpecificity_id,
				WDS.Org_sid,
				WDS.Org_tid,
				WDS.Storage_sid,
				WDS.Storage_tid,
				WDS.WhsDocumentSupply_id,
				WDS.DrugFinance_id,
				WDS.WhsDocumentCostItemType_id
			from
				v_WhsDocumentUc WDU with(nolock)
				inner join v_WhsDocumentClass WDC with(nolock) on WDC.WhsDocumentClass_id = WDU.WhsDocumentClass_id
				inner join v_WhsDocumentSpecificity WDS with(nolock) on WDS.WhsDocumentUc_id = WDU.WhsDocumentUc_id

			where
				WDU.WhsDocumentUc_id = :WhsDocumentUc_id
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение спецификации медикаментов в документе учета
	 */
	function loadWhsDocumentSpecificationGrid($data) {
		$get_drug_data = true; //получать данные о медикаменте (включая данные для хинта)
		$select = array();
		$join = array();

        $this->load->model('DocumentUc_model', 'DocumentUc_model');

		$params = array(
            'WhsDocumentSpecificity_id' => $data['WhsDocumentSpecificity_id'],
            'DefaultGoodsUnit_id' => $this->DocumentUc_model->getDefaultGoodsUnitId()
        );

		if ($get_drug_data) {
			$select[] = "WDS.Drug_id";
			$select[] = "D.Drug_Name";
			$select[] = "D.Drug_Nomen as hintPackagingData";  // Хинт: Данные об упаковке – это часть поля Упаковка (drug_nomen) от начала строки до данных о производителе
			$select[] = "RTRIM(ISNULL(DP.DrugPrep_Name, '')) as hintTradeName"; // Хинт: Торговое наименование, лекарственная форма, дозировка, фасовка
			$select[] = "isnull(D.Drug_RegNum + ',   ', '') + isnull(convert(varchar(10), D.Drug_begDate, 104) + ', ', '') + isnull(convert(varchar(10), D.Drug_endDate, 104) +', ', '--, ') + isnull(convert(varchar(10), REG.REGCERT_insDT, 104)+', ', '') + isnull(REGISTR.regNameCauntries, '') as hintRegistrationData";  // Хинт: Данные о регистрации
			$select[] = "
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
				end as hintPRUP
			"; // Хинт: ПР./УП.
			$select[] = "FNM.NAME as hintFirmNames"; // Хинт: Данные о производителе
			$join[] = "left join rls.v_Drug D with(nolock) on D.Drug_id = WDS.Drug_id";
			$join[] = "left join rls.DrugPrep DP with(nolock) on D.DrugPrepFas_id = dp.DrugPrepFas_id";
			$join[] = "left join rls.PREP P with(nolock) on P.Prep_id = d.DrugPrep_id";
			$join[] = "left join rls.REGCERT REG with(nolock) on REG.REGCERT_ID =P.REGCERTID";
			$join[] = "left join rls.NOMEN NM with(nolock) on NM.Nomen_id = d.Drug_id";
			$join[] = "left join rls.FIRMS NOMENF with(nolock) on NOMENF.FIRMS_ID = NM.FIRMID";
			$join[] = "left join rls.FIRMS MANUFACTURF with(nolock) on MANUFACTURF.FIRMS_ID = P.FIRMID";
			$join[] = "left join rls.FIRMNAMES FNM with(nolock) on FNM.FIRMNAMES_ID = MANUFACTURF.NAMEID";
			$join[] = "outer apply(
				select top 1
					FN.NAME + ' (' + C.NAME + ')' as regNameCauntries
				from
					rls.REGCERT_EXTRAFIRMS RE with(nolock)
					left join rls.FIRMS F with(nolock) on F.FIRMS_ID = RE.FIRMID
					left join rls.FIRMNAMES FN with(nolock) on FN.FIRMNAMES_ID = F.NAMEID
					left join rls.COUNTRIES C with(nolock) on C.COUNTRIES_ID = F.COUNTID
				where
					RE.CERTID = P.REGCERTID
			) REGISTR";
		}

		$select_clause = count($select) > 0 ? ', '.join(', ', $select) : '';;
		$join_clause = count($join) > 0 ? join(' ', $join) : '';

		$query = "
			select
				WDS.WhsDocumentSpecification_id,
				WDS.WhsDocumentSpecificity_id,
				WDS.WhsDocumentSpecification_Count,
				WDS.WhsDocumentSpecification_Cost,
				WDS.WhsDocumentSpecification_Method,
				DN.DrugNomen_id,
				DN.DrugNomen_Code,
				DN.DrugComplexMnnCode_Code,
				DN.Drug_Ean,
				DCM.DrugComplexMnn_id,
				DCM.DrugComplexMnn_RusName as DrugComplexMnn_Name,
				DCMD.DrugComplexMnnDose_Name as DrugComplexMnn_Dose,
				CDF.Name as RlsClsdrugforms_RusName,
				T.Tradenames_id,
				T.Name as DrugTorg_Name,
				O.Okei_id,
				O.Okei_Name,
				GU.GoodsUnit_id,
				GU.GoodsUnit_Name,
				ECT.EvnCourseTreat_id,
				RO.ReceptOtov_id,
				case
					when ECT.EvnCourseTreat_id is not null then
						PS.Person_SurName+' '+PS.Person_FirName+ISNULL(' '+PS.Person_SecName,'')+', КВС № '+EPS.EvnPS_NumCard
					when RO.ReceptOtov_id is not null then
						PS.Person_SurName+' '+PS.Person_FirName+ISNULL(' '+PS.Person_SecName,'')
						+', рецепт серия '+RO.EvnRecept_Ser+' № '+RO.EvnRecept_Num+' от '+convert(varchar(10), RO.EvnRecept_setDate, 104)
				end as WhsDocumentSpecification_Note,
				null as OtpuskCount,
				null as OtpuskSum,
				null as Budget
				{$select_clause}
			from
				v_WhsDocumentSpecification WDS with(nolock)
				left join rls.v_DrugComplexMnn DCM with(nolock) on DCM.DrugComplexMnn_id = WDS.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnDose DCMD with(nolock) on DCMD.DrugComplexMnnDose_id = DCM.DrugComplexMnnDose_id
				left join rls.v_Clsdrugforms CDF with(nolock) on CDF.Clsdrugforms_id = DCM.Clsdrugforms_id
				left join rls.v_Tradenames T with(nolock) on T.Tradenames_id = WDS.Tradenames_id
				left join v_Okei O with(nolock) on O.Okei_id = WDS.Okei_id
				left join v_GoodsUnit GU with(nolock) on GU.GoodsUnit_id = isnull(WDS.GoodsUnit_id, :DefaultGoodsUnit_id)
				outer apply(
					select top 1
						DN.DrugNomen_id,
						DN.DrugNomen_Code,
						D.Drug_Ean,
						DCMC.DrugComplexMnnCode_Code
					from
						rls.v_DrugNomen DN with(nolock)
						inner join rls.v_Drug D with(nolock) on D.Drug_id = DN.Drug_id
						inner join rls.v_DrugComplexMnnCode DCMC with(nolock) on DCMC.DrugComplexMnnCode_id = DN.DrugComplexMnnCode_id
					where
						D.DrugComplexMnn_id = WDS.DrugComplexMnn_id
						and (WDS.Tradenames_id is null or D.DrugTorg_id = WDS.Tradenames_id)
				) DN
				left join v_EvnCourseTreat ECT with(nolock) on ECT.EvnCourseTreat_id = WDS.EvnCourseTreat_id
				left join v_EvnPS EPS with(nolock) on EPS.EvnPS_id = ECT.EvnCourseTreat_rid
				left join v_ReceptOtov_all RO with(nolock) on RO.ReceptOtov_id = WDS.ReceptOtov_id
				left join v_PersonState PS with(nolock) on PS.Person_id = coalesce(EPS.Person_id,RO.ReceptOtov_id)
				{$join_clause}
			where
				WDS.WhsDocumentSpecificity_id = :WhsDocumentSpecificity_id
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Формирование спецификации медикаментов на основе контракта
	 */
	function createWhsDocumentSpecificationByWhsDocumentSupply($data) {
		$params = array('WhsDocumentSupply_id' => $data['WhsDocumentSupply_id']);
		$filter = "";

		if (!empty($data['Storage_id'])) {
			$filter .= " and DOR.Storage_id = :Storage_id";
			$params['Storage_id'] = $data['Storage_id'];
		}

		$query = "
			select
				null as WhsDocumentSpecification_id,
				null as WhsDocumentSpecificity_id,
				DOR.DrugOstatRegistry_Kolvo as WhsDocumentSpecification_Count,
				DOR.DrugOstatRegistry_Cost as WhsDocumentSpecification_Cost,
				null as WhsDocumentSpecification_Method,
				DCM.DrugComplexMnn_id,
				DCM.DrugComplexMnn_RusName as DrugComplexMnn_Name,
				DCMD.DrugComplexMnnDose_Name as DrugComplexMnn_Dose,
				CDF.Name as RlsClsdrugforms_RusName,
				D.DrugTorg_id as Tradenames_id,
				D.DrugTorg_Name,
				D.Drug_Ean,
				DN.DrugNomen_id,
				DN.DrugNomen_Code,
				O.Okei_id,
				O.Okei_Name,
				null as EvnCourseTreat_id,
				null as ReceptOtov_id,
				null as WhsDocumentSpecification_Note,
				null as OtpuskCount,
				null as OtpuskSum,
				null as Budget
			from
				v_WhsDocumentSupplySpec WDSS with(nolock)
				inner join v_WhsDocumentSupply WDS with(nolock) on WDS.WhsDocumentSupply_id = WDSS.WhsDocumentSupply_id
				left join v_DrugOstatRegistry DOR with(nolock) on DOR.Drug_id = WDSS.Drug_id and DOR.Org_id = WDS.Org_sid
				left join rls.v_Drug D with(nolock) on D.Drug_id = DOR.Drug_id
				left join rls.v_DrugComplexMnn DCM with(nolock) on DCM.DrugComplexMnn_id = D.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnDose DCMD with(nolock) on DCMD.DrugComplexMnnDose_id = DCM.DrugComplexMnnDose_id
				left join rls.v_CLSDRUGFORMS CDF with(nolock) on CDF.CLSDRUGFORMS_ID = DCM.CLSDRUGFORMS_ID
				left join rls.v_DrugNomen DN with(nolock) on DN.Drug_id = D.Drug_id
				left join v_Okei O with(nolock) on O.Okei_id = DOR.Okei_id
			where
				WDSS.WhsDocumentSupply_id = :WhsDocumentSupply_id
				and DOR.SubAccountType_id = 1
				and DOR.DrugOstatRegistry_Kolvo > 0
				{$filter}
		";
		//echo getDebugSQL($query, $params);exit;
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при формировании заявки медикаментов из спецификации контраката');
		}

		$response = array('success' => true, 'Error_Msg' => '', 'data' => $resp);

		return $response;
	}

	/**
	 * Формирование спецификации медикаментов на основе назначений лекарственного лечения
	 */
	function createWhsDocumentSpecificationByPrescr($data) {
		$this->load->model('DocumentUc_model', 'DocumentUc_model');

		$params = array(
			'Storage_id' => $data['Storage_id'],
			'MedService_id' => $data['MedService_id'],
			'DefaultGoodsUnit_id' => $this->DocumentUc_model->getDefaultGoodsUnitId()
		);

		//получение списка складов (склад + дочерние склады не связанные со службой с типом АРМ Товаровед)
		$query = "
			select
				:Storage_id as Storage_id
			union select -- дочерние склады
				s.Storage_id
			from
				v_Storage s with (nolock)
				outer apply (
					select top 1
						i_ms.MedService_id
					from
						v_StorageStructLevel i_ssl with (nolock)
						left join v_MedService i_ms with (nolock) on i_ms.MedService_id = i_ssl.MedService_id			
						left join v_MedServiceType i_mst with (nolock) on i_mst.MedServiceType_id = i_ms.MedServiceType_id
					where
						i_ssl.Storage_id = s.Storage_id and
						i_mst.MedServiceType_SysNick = 'merch'
				) ms
			where
				s.Storage_pid = :Storage_id and
				ms.MedService_id is null;
		";
		$storage_list  = $this->queryList($query, $params);
		if (!is_array($storage_list) || count($storage_list) == 0) { //список не должен быть пуст, так как он должен содержать как минимум основной склад, передаваемый в качестве параметра
			return $this->createError('','Ошибка при получени списка складов');
		}

        $where = array();
        $join = array();

        if (!empty($data['DrugFinance_id'])) {
            $join[] = "left join v_EvnPS eps with (nolock) on eps.EvnPS_id = ect.EvnCourseTreat_rid";
            $join[] = "left join v_PayType pt with (nolock) on pt.PayType_id = eps.PayType_id";
            $join[] = "left join v_DrugFinance df with (nolock) on df.DrugFinance_Name = pt.PayType_Name or df.DrugFinance_SysNick = pt.PayType_SysNick";
            $where[] = "df.DrugFinance_id = :DrugFinance_id";
            $params['DrugFinance_id'] = $data['DrugFinance_id'];
        }

        $join_clause = implode(' ', $join);
        $where_clause = implode(' and ', $where);
        if (count($where) > 0) {
            $where_clause = " and ".$where_clause;
        }

        // создаем и заполняем временную таблицу для данных назначений
        $tmpPrescrTableName = "#prescr_tbl_tmp" . time();

        $params['Upak_GoodsUnit_id'] = $this->getFirstResultFromQuery("select top 1 GoodsUnit_id from v_GoodsUnit where GoodsUnit_Name = 'упаковка'");


        $query = "
			IF OBJECT_ID(N'tempdb..{$tmpPrescrTableName}', N'U') IS NOT NULL
				DROP TABLE {$tmpPrescrTableName};

			create table {$tmpPrescrTableName} (
				Drug_id bigint,
				DrugComplexMnn_id bigint,
				Drug_Name varchar(500),
				EvnCourseTreat_id bigint,
				Person_id bigint,
				GoodsPackCount_Count float,
				GoodsUnit_id bigint,
				GoodsUnit_Kolvo float,
				MinGoodsPackCount_Count float,
				MinGoodsUnit_id bigint,
				MinGoodsUnit_Kolvo float
			)

			insert into {$tmpPrescrTableName}
			select
				d.Drug_id,
				dcm.DrugComplexMnn_id,
				(isnull(dcmn.DrugComplexMnnName_Name,'')+isnull(' ('+d.Drug_Name+')', '')) as Drug_Name,
				--(isnull(ectd.EvnCourseTreatDrug_KolvoEd*koef.kf_ke, ectd.EvnCourseTreatDrug_Kolvo*koef.kf_k)*(ect.EvnCourseTreat_PrescrCount-ectd.EvnCourseTreatDrug_FactCount)) as Count,
				--(isnull(isnull(koef.kf_ke, koef.kf_k), 0)) as koef_value,
				ect_id.EvnCourseTreat_id,
				ect.Person_id,
				(case
					when isnull(gpc.GoodsPackCount_sCount, 0) > 0 then gpc.GoodsPackCount_sCount
					else gpc.GoodsPackCount_Count
				end) as GoodsPackCount_Count,				
				(case
					when isnull(gpc.GoodsPackCount_sCount, 0) > 0 then ectd.GoodsUnit_sid
					else ectd.GoodsUnit_id
				end) as GoodsUnit_id,				
				(
					(case
						when isnull(gpc.GoodsPackCount_sCount, 0) > 0 then ectd.EvnCourseTreatDrug_KolvoEd
						else ectd.EvnCourseTreatDrug_Kolvo
					end)*(ect.EvnCourseTreat_PrescrCount-ectd.EvnCourseTreatDrug_FactCount)
				) as GoodsUnit_Kolvo,				
				min_gpc.GoodsUnit_id as MinGoodsUnit_id,
				(
					(case
						when isnull(gpc.GoodsPackCount_sCount, 0) > 0 then ectd.EvnCourseTreatDrug_KolvoEd*(min_gpc.GoodsPackCount_Count/gpc.GoodsPackCount_sCount)
						when isnull(gpc.GoodsPackCount_Count, 0) > 0 then ectd.EvnCourseTreatDrug_Kolvo*(min_gpc.GoodsPackCount_Count/gpc.GoodsPackCount_Count)
						else null
					end)*(ect.EvnCourseTreat_PrescrCount-ectd.EvnCourseTreatDrug_FactCount)
				) as MinGoodsUnit_Kolvo,
				min_gpc.GoodsPackCount_Count as MinGoodsPackCount_Count
			from
				v_EvnCourseTreatDrug ectd with(nolock)
				inner join v_EvnCourseTreat ect with(nolock) on ect.EvnCourseTreat_id = ectd.EvnCourseTreat_id
				inner join v_EvnSection ES with(nolock) on ES.EvnSection_id = ECT.EvnCourseTreat_pid
				left join rls.v_Drug d with(nolock) on d.Drug_id = ectd.Drug_id
				left join rls.v_DrugComplexMnn dcm with(nolock) on dcm.DrugComplexMnn_id = ISNULL(d.DrugComplexMnn_id, ectd.DrugComplexMnn_id)
                left join rls.v_DrugComplexMnnName dcmn with(nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
                left join rls.v_ACTMATTERS am with(nolock) on am.ACTMATTERS_ID = dcmn.ACTMATTERS_id
				left join v_LpuSection LS with(nolock) on LS.LpuSection_id = ES.LpuSection_id
				left join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuUnitType LUT with(nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
				left join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id =LU.LpuBuilding_id
				left join v_Lpu L with(nolock) on L.Lpu_id = LB.Lpu_id
                outer apply (
                    select (
                        case
                            when am.NARCOGROUPID > 0 then ect.EvnCourseTreat_id -- для наркотиков производим дополнительную группировку по курсу
                            else null
                        end
                    ) as EvnCourseTreat_id
                ) ect_id
                outer apply (
                    select top 1
                        isnull(i_yn.YesNo_Code, 0) as YesNo_Code
                    from
                        v_EvnPrescrTreat i_ept with (nolock)
                        left join v_EvnPrescr i_ep with (nolock) on i_ep.EvnPrescr_id = i_ept.EvnPrescrTreat_id
                        left join v_YesNo i_yn with (nolock) on i_yn.YesNo_id = i_ep.EvnPrescr_IsExec
                    where
                        i_ept.EvnCourse_id = ect.EvnCourseTreat_id
                ) is_exec
                outer apply (
                    select top 1
                        i_gp.GoodsPackCount_Count,
                        (
                            case
                                when i_gp.TRADENAMES_ID = d.DrugTorg_id then 1
                                else 0
                            end
                        ) as sp -- поле для сортировки
                    from
                        GoodsPackCount i_gp with (nolock)
                    where
                        i_gp.DrugComplexMnn_id = ectd.DrugComplexMnn_id and
                        i_gp.GoodsUnit_id = ectd.GoodsUnit_sid
                    order by
                        sp desc, i_gp.GoodsPackCount_id
                ) gpc_ke -- информация для конвертации количества в произвольных ед. изм. в количество в упаковках
                outer apply (
                    select top 1
                        i_gp.GoodsPackCount_Count,
                        (
                            case
                                when i_gp.TRADENAMES_ID = d.DrugTorg_id then 1
                                else 0
                            end
                        ) as sp -- поле для сортировки
                    from
                        GoodsPackCount i_gp with (nolock)
                    where
                        i_gp.DrugComplexMnn_id = ectd.DrugComplexMnn_id and
                        i_gp.GoodsUnit_id = ectd.GoodsUnit_id
                    order by
                        sp desc, i_gp.GoodsPackCount_id
                ) gpc_k -- информация для конвертации количества в произвольных ед. изм. в количество в упаковках                
                outer apply (
                    select
                        (
                            case
                                when ectd.GoodsUnit_sid = :DefaultGoodsUnit_id then 1
                                else gpc_ke.GoodsPackCount_Count
                            end
                        ) GoodsPackCount_sCount,
                        (
                            case
                                when ectd.GoodsUnit_id = :DefaultGoodsUnit_id then 1
                                else gpc_k.GoodsPackCount_Count
                            end
                        ) GoodsPackCount_Count
                ) gpc -- если количество задано в упаковках то количество в упаковке равно 1, иначе значение из таблиц
                outer apply (
                	select top 1
						i_gp.GoodsUnit_id,
						i_gp.GoodsPackCount_Count
                    from
                        GoodsPackCount i_gp with (nolock)
                    where
                        i_gp.DrugComplexMnn_id = ectd.DrugComplexMnn_id
                    order by
                        i_gp.GoodsPackCount_Count desc, i_gp.GoodsPackCount_id
                ) min_gpc -- данные для персчета в самую мелкую ед. измерения которая есть в справочнике для данного медикамента 
                {$join_clause}
			where
				ect.LpuSection_id in (select LpuSection_id from v_StorageStructLevel t with(nolock) where Storage_id in (".join(",", $storage_list)."))
				and ES.EvnSection_disDate is null
				and is_exec.YesNo_Code = 0
				and isnull(ectd.EvnCourseTreatDrug_KolvoEd, ectd.EvnCourseTreatDrug_Kolvo) is not null
				and ect.EvnCourseTreat_PrescrCount-ectd.EvnCourseTreatDrug_FactCount > 0
				and :MedService_id in (
					select MS.MedService_id
					from v_MedService MS with(nolock)
					where
						isnull(MS.Lpu_id, L.Lpu_id) = L.Lpu_id
						and isnull(MS.LpuBuilding_id, LB.LpuBuilding_id) = LB.LpuBuilding_id
						and isnull(MS.LpuUnitType_id, LUT.LpuUnitType_id) = LUT.LpuUnitType_id
						and isnull(MS.LpuUnit_id, LU.LpuUnit_id) = LU.LpuUnit_id
						and isnull(MS.LpuSection_id, LS.LpuSection_id) = LS.LpuSection_id
				)
				{$where_clause}
		";
        $result = $this->db->query(getDebugSQL($query, $params)); //без предварительной сборки запроса работать не хочет

		$query = "
		    select
				rl.Drug_id,
				rl.DrugComplexMnn_id,
				rl.EvnCourseTreat_id,
				rl.Person_id,								
				rl.GoodsUnit_id,
				max(rl.GoodsPackCount_Count) as GoodsPackCount_Count,				
				sum(rl.GoodsUnit_Kolvo) as GoodsUnit_Kolvo,				
				max(rl.MinGoodsUnit_id) as MinGoodsUnit_id,
				sum(rl.MinGoodsUnit_Kolvo) as MinGoodsUnit_Kolvo,
				max(rl.MinGoodsPackCount_Count) as MinGoodsPackCount_Count
			from
				{$tmpPrescrTableName} rl with(nolock)
			where
			    isnull(rl.GoodsPackCount_Count, 0) > 0 and
			    isnull(rl.MinGoodsPackCount_Count, 0) > 0
			group by
				rl.Drug_id,
				rl.DrugComplexMnn_id,
				rl.EvnCourseTreat_id,
				rl.Person_id,
				rl.GoodsUnit_id
			order by
				rl.Drug_id,
				rl.DrugComplexMnn_id
		";
		$prescr_list = $this->queryResult($query);
		if (!is_array($prescr_list)) {
			return $this->createError('','Ошибка при получении медикаментов из назначений');
		}

        //составление списка позиций для которых не был выполнен расчет коэфицента из за отсутсвия данных
        $query = "
			select distinct
				dcm.DrugComplexMnn_RusName as DrugComplexMnn_Name,
				d.DrugTorg_Name,
				isnull(gu.GoodsUnit_Name, '') as GoodsUnit_Name
			from
				{$tmpPrescrTableName} rl with(nolock)
				left join v_GoodsUnit gu with (nolock) on gu.GoodsUnit_id in (rl.GoodsUnit_id)
				left join rls.v_Drug d with(nolock) on d.Drug_id = rl.Drug_id
				left join rls.v_DrugComplexMnn dcm with(nolock) on dcm.DrugComplexMnn_id = rl.DrugComplexMnn_id
			where
			    isnull(rl.GoodsPackCount_Count, 0) = 0 or 
			    isnull(rl.MinGoodsPackCount_Count, 0) = 0
			order by
			    d.DrugTorg_Name, dcm.DrugComplexMnn_RusName, isnull(gu.GoodsUnit_Name, '')
		";
        $gu_data = $this->queryResult($query);

        //проверка наличия коэфицента для пересчета
        /*foreach($prescr_list as $prescr) {
            if (empty($prescr['koef_value']) || $prescr['koef_value']*1 <= 0) {
                return $this->createError('', "Операция по формированию списка медикаментов заявки не может быть завершена, т.к. для медикамента \"{$prescr['Drug_Name']}\"  не заданы единицы измерения отличные от упаковки. Обратитесь к администратору системы для добавления значения в Номенклатурный справочник и повторите операцию.");
            }
        }*/

        $where = array();
        $join = array();

        if (!empty($data['DrugFinance_id'])) {
            $where[] = "dor.DrugFinance_id = :DrugFinance_id";
            $params['DrugFinance_id'] = $data['DrugFinance_id'];
        }

        if (!empty($data['WhsDocumentCostItemType_id'])) {
            $where[] = "dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
            $params['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
        }

        if (!empty($data['WhsDocumentSupply_id'])) {
            $join[] = "left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dor.DrugShipment_id";
            $where[] = "ds.WhsDocumentSupply_id = :WhsDocumentSupply_id";
            $params['WhsDocumentSupply_id'] = $data['WhsDocumentSupply_id'];
        }

        $join_clause = implode(' ', $join);
        $where_clause = implode(' and ', $where);
        if (count($where) > 0) {
            $where_clause = " and ".$where_clause;
        }

        $query = "
			select
				d.Drug_id,
				d.DrugComplexMnn_id,								
				min_gpc.GoodsUnit_id as MinGoodsUnit_id,
				sum(case
					when isnull(gpc.GoodsPackCount_Count, 0) > 0 then dor.DrugOstatRegistry_Kolvo*(min_gpc.GoodsPackCount_Count/gpc.GoodsPackCount_Count)
					else null
				end) as MinGoodsUnit_Kolvo,
				cost.cost as MinGoodsUnit_Cost,
				max(min_gpc.GoodsPackCount_Count) as MinGoodsPackCount_Count
			from
				v_DrugOstatRegistry dor with(nolock)
				left join rls.v_Drug d with(nolock) on d.Drug_id = dor.Drug_id
				outer apply (
                    select top 1
                        i_gp.GoodsPackCount_Count,
                        (
                            case
                                when i_gp.TRADENAMES_ID = d.DrugTorg_id then 1
                                else 0
                            end
                        ) as sp -- поле для сортировки
                    from
                        GoodsPackCount i_gp with (nolock)
                    where
                        i_gp.DrugComplexMnn_id = d.DrugComplexMnn_id and
                        i_gp.GoodsUnit_id = dor.GoodsUnit_id
                    order by
                        sp desc, i_gp.GoodsPackCount_id
                ) gpc
                outer apply (
                	select (case
                		when isnull(dor.GoodsUnit_id, :DefaultGoodsUnit_id) = :DefaultGoodsUnit_id then 1
                		else gpc.GoodsPackCount_Count
                	end) as GoodsPackCount_Count
                ) dor_gpc
 				outer apply (
                	select top 1
						i_gp.GoodsUnit_id,
						i_gp.GoodsPackCount_Count
                    from
                        GoodsPackCount i_gp with (nolock)
                    where
                        i_gp.DrugComplexMnn_id = d.DrugComplexMnn_id
                    order by
                        i_gp.GoodsPackCount_Count desc, i_gp.GoodsPackCount_id
                ) min_gpc -- данные для персчета в самую мелкую ед. измерения которая есть в справочнике для данного медикамента
				outer apply (
					select (case
						when isnull(gpc.GoodsPackCount_Count, 0) > 0 then dor.DrugOstatRegistry_Cost*(min_gpc.GoodsPackCount_Count/gpc.GoodsPackCount_Count)
						else null
					end) as cost
				) cost 
				{$join_clause}
			where
				/*dor.Storage_id in (
					select Storage_id from v_StorageStructLevel t with(nolock)
					where LpuSection_id in (select LpuSection_id from v_StorageStructLevel t with(nolock) where Storage_id = :Storage_id)
				)*/
				dor.Storage_id in (".join(",", $storage_list).")
				{$where_clause}
			group by
				d.Drug_id,
				d.DrugComplexMnn_id,
				min_gpc.GoodsUnit_id,
				cost.cost
			having
				SUM(dor.DrugOstatRegistry_Kolvo) > 0
			order by
				Drug_id,
				DrugComplexMnn_id,
				MinGoodsUnit_id
		";
		$ostat_list = $this->queryResult($query, $params);
		if (!is_array($ostat_list)) {
			return $this->createError('','Ошибка при получении медикаментов из остатков отделений');
		}

        foreach($prescr_list as &$prescr) {
            foreach($ostat_list as &$ostat) {
                //если данные остатки уже исчерпаны, переходим к следующим
                if ($ostat['MinGoodsUnit_Kolvo'] == 0) {
                    continue;
                }

                if (
                    (!empty($prescr['Drug_id']) && $prescr['Drug_id'] == $ostat['Drug_id'])
                    || ($prescr['DrugComplexMnn_id'] == $ostat['DrugComplexMnn_id'])
                ) { //найдено совпадение между остатками и назначениями
					if ($prescr['MinGoodsUnit_id'] != $ostat['MinGoodsUnit_id']) {
						return $this->createError('','Ошибка при пересчете ед. учета');
					}
                    if ($ostat['MinGoodsUnit_Kolvo'] <= $prescr['MinGoodsUnit_Kolvo']) { //если остатков недостаточно для перекрытия потребности, то уменьшаем потребность на количество из остатка и обнуляем остаток
                        $prescr['MinGoodsUnit_Kolvo'] = $prescr['MinGoodsUnit_Kolvo'] - $ostat['MinGoodsUnit_Kolvo'];
                        $ostat['MinGoodsUnit_Kolvo'] = 0;
                    } else { //иначе обнуляем потребность, и уменьшаем количество остатков на величину равную потребности
                        $ostat['MinGoodsUnit_Kolvo'] = $ostat['MinGoodsUnit_Kolvo'] - $prescr['MinGoodsUnit_Kolvo'];
                        $prescr['MinGoodsUnit_Kolvo'] = 0;
                    }
                    $prescr['MinGoodsUnit_Cost'] = $ostat['MinGoodsUnit_Cost'];
                }

                //если даннная потребность уже перекрыта, переходим к следующей
                if ($prescr['MinGoodsUnit_Kolvo'] == 0) {
                    break;
                }
            }
            if (!isset($prescr['MinGoodsUnit_Cost'])) {
                $prescr['MinGoodsUnit_Cost'] =  0;
            }
        }

		$insert_values = array();
		foreach($prescr_list as &$prescr) {
			if ($prescr['MinGoodsUnit_Kolvo'] > 0) {
				$prescr['Drug_id'] = !empty($prescr['Drug_id']) ? $prescr['Drug_id'] : 'null';
				$prescr['EvnCourseTreat_id'] = !empty($prescr['EvnCourseTreat_id']) ? $prescr['EvnCourseTreat_id'] : 'null';

				//пересчет количества и цены обратно из "минимальных" ед. учета, в те, что указаны в назначениях
				$cost = round($prescr['MinGoodsUnit_Cost']*($prescr['GoodsPackCount_Count']/$prescr['MinGoodsPackCount_Count']), 2);
				$count = round($prescr['MinGoodsUnit_Kolvo']*($prescr['GoodsPackCount_Count']/$prescr['MinGoodsPackCount_Count']),4);

				$ins_val = "(";
				$ins_val .= !empty($prescr['Drug_id']) ? $prescr['Drug_id'] : "null";
				$ins_val .= ", ";
				$ins_val .= !empty($prescr['DrugComplexMnn_id']) ? $prescr['DrugComplexMnn_id'] : "null";
				$ins_val .= ", {$cost}, {$count}, ";
				$ins_val .= !empty($prescr['EvnCourseTreat_id']) ? $prescr['EvnCourseTreat_id'] : "null";
				$ins_val .= ", ";
				$ins_val .= !empty($prescr['Person_id']) ? $prescr['Person_id'] : "null";
				$ins_val .= ", ";
				$ins_val .= !empty($prescr['GoodsUnit_id']) ? $prescr['GoodsUnit_id'] : "null";
				$ins_val .= ")";
				$insert_values[] = $ins_val;
			}
		}

		//составление списка позиций для которых не был выполнен расчет коэфицента из за отсутсвия данных и добавление их в заявку
		$query = "
			select
				rl.Drug_id,
				rl.DrugComplexMnn_id,
				rl.EvnCourseTreat_id,
				rl.Person_id,								
				rl.GoodsUnit_id,
				sum(rl.GoodsUnit_Kolvo) as GoodsUnit_Kolvo
			from
				{$tmpPrescrTableName} rl with(nolock)
			where
			    isnull(rl.GoodsPackCount_Count, 0) = 0 or
			    isnull(rl.MinGoodsPackCount_Count, 0) = 0
			group by
				rl.Drug_id,
				rl.DrugComplexMnn_id,
				rl.EvnCourseTreat_id,
				rl.Person_id,
				rl.GoodsUnit_id
			order by
				rl.Drug_id,
				rl.DrugComplexMnn_id
		";
		$add_list = $this->queryResult($query);
		foreach($add_list as $add_data) {
			$ins_val = "(";
			$ins_val .= !empty($add_data['Drug_id']) ? $add_data['Drug_id'] : "null";
			$ins_val .= ", ";
			$ins_val .= !empty($add_data['DrugComplexMnn_id']) ? $add_data['DrugComplexMnn_id'] : "null";
			$ins_val .= ", 0, ";
			$ins_val .= $add_data['GoodsUnit_Kolvo'] != "" ? $add_data['GoodsUnit_Kolvo'] : "null";
			$ins_val .= ", ";
			$ins_val .= !empty($add_data['EvnCourseTreat_id']) ? $add_data['EvnCourseTreat_id'] : "null";
			$ins_val .= ", ";
			$ins_val .= !empty($add_data['Person_id']) ? $add_data['Person_id'] : "null";
			$ins_val .= ", ";
			$ins_val .= !empty($add_data['GoodsUnit_id']) ? $add_data['GoodsUnit_id'] : "null";
			$ins_val .= ")";
			$insert_values[] = $ins_val;
		}

		if (count($insert_values) == 0) {
			return array('success' => true, 'Error_Msg' => '', 'data' => array());
		}

		$insert_values_str = implode(",\n", $insert_values);

		$tmpTableName = "#drug_request_tmp" . time();

		// Создаем временную таблицу
		$query = "
			IF OBJECT_ID(N'tempdb..{$tmpTableName}', N'U') IS NOT NULL
				DROP TABLE {$tmpTableName};
			create table {$tmpTableName} (
				Drug_id bigint,
				DrugComplexMnn_id bigint,
				Cost float,
				Count float,
				EvnCourseTreat_id bigint,
				Person_id bigint,
				GoodsUnit_id bigint
			)
			insert into {$tmpTableName} (Drug_id, DrugComplexMnn_id, Cost, Count, EvnCourseTreat_id, Person_id, GoodsUnit_id)
			values {$insert_values_str}
		";
		$result = $this->db->query($query);

		$query = "
			select
				null as WhsDocumentSpecification_id,
				null as WhsDocumentSpecificity_id,
				rl.Count as WhsDocumentSpecification_Count,
				rl.Cost as WhsDocumentSpecification_Cost,
				null as WhsDocumentSpecification_Method,
				DCM.DrugComplexMnn_id,
				DCM.DrugComplexMnn_RusName as DrugComplexMnn_Name,
				COALESCE(DCMD.DrugComplexMnnDose_Name,dcm.DrugComplexMnn_Dose) as DrugComplexMnn_Dose,
				CDF.Name as RlsClsdrugforms_RusName,
				D.DrugTorg_id as Tradenames_id,
				D.DrugTorg_Name,
				DN.Drug_Ean,
				DN.DrugNomen_id,
				DN.DrugNomen_Code,
				O.Okei_id,
				O.Okei_Name,
				ect.EvnCourseTreat_id as EvnCourseTreat_id,
				null as ReceptOtov_id,
				PS.Person_SurName+' '+PS.Person_FirName+ISNULL(' '+PS.Person_SecName,'')+', КВС № '+EPS.EvnPS_NumCard as WhsDocumentSpecification_Note,
				null as OtpuskCount,
				null as OtpuskSum,
				null as Budget
			from
				{$tmpTableName} rl with(nolock)
				left join rls.v_Drug d with(nolock) on d.Drug_id = rl.Drug_id
				left join rls.v_DrugComplexMnn dcm with(nolock) on dcm.DrugComplexMnn_id = rl.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnDose dcmd with(nolock) on dcmd.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id
				left join rls.v_CLSDRUGFORMS cdf with(nolock) on cdf.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
				left join v_Okei o with(nolock) on o.Okei_id = 120
				left join v_EvnCourseTreat ect with(nolock) on ect.EvnCourseTreat_id = rl.EvnCourseTreat_id
				left join v_EvnPS eps with(nolock) on eps.EvnPS_id = ect.EvnCourseTreat_rid
				left join v_PersonState ps with(nolock) on ps.Person_id = rl.Person_id
				outer apply(
					select top 1
						DN.DrugNomen_id,
						DN.DrugNomen_Code,
						Drug.Drug_Ean
					from
						rls.v_DrugNomen DN with(nolock)
						inner join rls.v_Drug Drug with(nolock) on Drug.Drug_id = DN.Drug_id
					where
						Drug.DrugComplexMnn_id = dcm.DrugComplexMnn_id
						and (d.DrugTorg_id is null or Drug.DrugTorg_id = d.DrugTorg_id)
				) DN
		";
		$resp = $this->queryResult($query);

		if (!is_array($resp)) {
			return $this->createError('','Ошибка при формировании заявки медикаментов на основе назначений');
		}

		$response = array('success' => true, 'Error_Msg' => '', 'data' => $resp, 'gu_data' => $gu_data);

		return $response;
	}

	/**
	 * Формирование спецификации медикаментов из остатков поставщика
	 */
	function createWhsDocumentSpecificationByDrugOstatRegistry($data) {
        $this->load->model('DocumentUc_model', 'DocumentUc_model');

        $params = array(
            'DefaultGoodsUnit_id' => $this->DocumentUc_model->getDefaultGoodsUnitId()
        );

		$group_by_drug = true; //группировать остатки по медикаменту
		$get_drug_data = $group_by_drug; //получать данные о медикаменте (включая данные для хинта)

		$select = array();
		$select_dor = array();
		$join = array();
		$group_dor = array();

		if ($group_by_drug) {
			$select_dor[] = "D.Drug_id";
			$group_dor[] = "D.Drug_id";
		}

		if ($group_by_drug) {
			if ($get_drug_data) {
				$select[] = "dor.Drug_id";
				$select[] = "D.Drug_Name";
				$select[] = "D.Drug_Nomen as hintPackagingData";  // Хинт: Данные об упаковке – это часть поля Упаковка (drug_nomen) от начала строки до данных о производителе
				$select[] = "RTRIM(ISNULL(DP.DrugPrep_Name, '')) as hintTradeName"; // Хинт: Торговое наименование, лекарственная форма, дозировка, фасовка
				$select[] = "isnull(D.Drug_RegNum + ',   ', '') + isnull(convert(varchar(10), D.Drug_begDate, 104) + ', ', '') + isnull(convert(varchar(10), D.Drug_endDate, 104) +', ', '--, ') + isnull(convert(varchar(10), REG.REGCERT_insDT, 104)+', ', '') + isnull(REGISTR.regNameCauntries, '') as hintRegistrationData";  // Хинт: Данные о регистрации
				$select[] = "
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
					end as hintPRUP
				"; // Хинт: ПР./УП.
				$select[] = "FNM.NAME as hintFirmNames"; // Хинт: Данные о производителе
				$join[] = "left join rls.v_Drug D with(nolock) on D.Drug_id = dor.Drug_id";
				$join[] = "left join rls.DrugPrep DP with(nolock) on D.DrugPrepFas_id = dp.DrugPrepFas_id";
				$join[] = "left join rls.PREP P with(nolock) on P.Prep_id = d.DrugPrep_id";
				$join[] = "left join rls.REGCERT REG with(nolock) on REG.REGCERT_ID =P.REGCERTID";
				$join[] = "left join rls.NOMEN NM with(nolock) on NM.Nomen_id = d.Drug_id";
				$join[] = "left join rls.FIRMS NOMENF with(nolock) on NOMENF.FIRMS_ID = NM.FIRMID";
				$join[] = "left join rls.FIRMS MANUFACTURF with(nolock) on MANUFACTURF.FIRMS_ID = P.FIRMID";
				$join[] = "left join rls.FIRMNAMES FNM with(nolock) on FNM.FIRMNAMES_ID = MANUFACTURF.NAMEID";
				$join[] = "outer apply(
					select top 1
						FN.NAME + ' (' + C.NAME + ')' as regNameCauntries
					from
						rls.REGCERT_EXTRAFIRMS RE with(nolock)
						left join rls.FIRMS F with(nolock) on F.FIRMS_ID = RE.FIRMID
						left join rls.FIRMNAMES FN with(nolock) on FN.FIRMNAMES_ID = F.NAMEID
						left join rls.COUNTRIES C with(nolock) on C.COUNTRIES_ID = F.COUNTID
					where
						RE.CERTID = P.REGCERTID
				) REGISTR";
			} else {
				$select[] = "dor.Drug_id";
			}
		}

		$insert_sql = "";
		$select_clause = count($select) > 0 ? ', '.join(', ', $select) : '';;
		$select_dor_clause = count($select_dor) > 0 ? ', '.join(', ', $select_dor) : '';;
		$join_clause = count($join) > 0 ? join(' ', $join) : '';
		$group_dor_clause = count($group_dor) > 0 ? ', '.join(', ', $group_dor) : '';

        if (is_array($data['DrugOstatRegistryJSON'])) {
            foreach($data['DrugOstatRegistryJSON'] as $dor_data) {
                $insert_sql .= "insert into @OstatKolvo(DrugOstatRegistry_id, DrugOstatRegistry_Kolvo) values ({$dor_data->DrugOstatRegistry_id}, {$dor_data->DrugOstatRegistry_Kolvo});
                ";
            }
        }

		$query = "
            declare @OstatKolvo table (
                DrugOstatRegistry_id bigint,
                DrugOstatRegistry_Kolvo numeric(18, 2)
            );

            set nocount on;
            {$insert_sql}
            set nocount off;

			select
				null as WhsDocumentSpecification_id,
				null as WhsDocumentSpecificity_id,
				DOR.DrugOstatRegistry_Kolvo as WhsDocumentSpecification_Count,
				DOR.DrugOstatRegistry_Cost as WhsDocumentSpecification_Cost,
				null as WhsDocumentSpecification_Method,
				DCM.DrugComplexMnn_id,
				DCM.DrugComplexMnn_RusName as DrugComplexMnn_Name,
				DCMD.DrugComplexMnnDose_Name as DrugComplexMnn_Dose,
				CDF.Name as RlsClsdrugforms_RusName,
				DOR.DrugTorg_id as Tradenames_id,
				DOR.DrugTorg_Name,
				DN.Drug_Ean,
				DN.DrugNomen_id,
				DN.DrugNomen_Code,
				DN.DrugComplexMnnCode_Code,
				GU.GoodsUnit_id,
				GU.GoodsUnit_Name,
				null as EvnCourseTreat_id,
				null as ReceptOtov_id,
				null as WhsDocumentSpecification_Note,
				null as OtpuskCount,
				null as OtpuskSum,
				null as Budget
				{$select_clause}
			from (
                select
                    SUM(OK.DrugOstatRegistry_Kolvo) as DrugOstatRegistry_Kolvo,
                    DOR.DrugOstatRegistry_Cost,
                    DOR.GoodsUnit_id,
                    D.DrugTorg_id,
                    D.DrugTorg_Name,
                    D.DrugComplexMnn_id
                    {$select_dor_clause}
                from
                    @OstatKolvo OK
                    left join v_DrugOstatRegistry DOR with(nolock) on DOR.DrugOstatRegistry_id = OK.DrugOstatRegistry_id
                    inner join rls.v_Drug D with(nolock) on D.Drug_id = DOR.Drug_id
                group by
                    DOR.DrugOstatRegistry_Cost,
                    DOR.GoodsUnit_id,
                    D.DrugTorg_id,
                    D.DrugTorg_Name,
                    D.DrugComplexMnn_id
                    {$group_dor_clause}
            ) dor
            inner join rls.v_DrugComplexMnn DCM with(nolock) on DCM.DrugComplexMnn_id = DOR.DrugComplexMnn_id
            left join rls.v_DrugComplexMnnDose DCMD with(nolock) on DCMD.DrugComplexMnnDose_id = DCM.DrugComplexMnnDose_id
            left join rls.v_CLSDRUGFORMS CDF with(nolock) on CDF.CLSDRUGFORMS_ID = DCM.CLSDRUGFORMS_ID
            left join v_GoodsUnit GU with(nolock) on GU.GoodsUnit_id = isnull(DOR.GoodsUnit_id, :DefaultGoodsUnit_id)
            outer apply(
                select top 1
                    DN.DrugNomen_id,
                    DN.DrugNomen_Code,
                    Drug.Drug_Ean,
                    DCMC.DrugComplexMnnCode_Code
                from
                    rls.v_DrugNomen DN with(nolock)
                    inner join rls.v_Drug Drug with(nolock) on Drug.Drug_id = DN.Drug_id
                    left join rls.v_DrugComplexMnnCode DCMC with(nolock) on DCMC.DrugComplexMnnCode_id = DN.DrugComplexMnnCode_id
                where
                    Drug.DrugComplexMnn_id = DCM.DrugComplexMnn_id
                    and (DOR.DrugTorg_id is null or Drug.DrugTorg_id = DOR.DrugTorg_id)
            ) DN
            {$join_clause}
		";
		//echo getDebugSQL($query, array());exit;
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при формировании заявки медикаментов на остатков поставщика');
		}

		$response = array('success' => true, 'Error_Msg' => '', 'data' => $resp);

		return $response;
	}

    /**
     *	Получение списка параметров хранимой процедуры
     */
    function getStoredProcedureParamsList($sp, $schema) {
        $query = "
			select
				ps.[name]
			from
				sys.all_parameters ps
				left join sys.types t on t.system_type_id = ps.system_type_id and t.user_type_id = ps.user_type_id
			where
				ps.[object_id] = (
					select
						top 1 [object_id]
					from
						sys.objects
					where
						[type_desc] = 'SQL_STORED_PROCEDURE' and
						[name] = :name and
						(
							:schema is null or
							[schema_id] = (select top 1 [schema_id] from sys.schemas where [name] = :schema)
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
     * Получение идентификатора произвольного обьекта по коду
     */
    function getObjectIdByCode($object_name, $code) {
        $query = "
			select top 1
				{$object_name}_id
			from
				v_{$object_name} with (nolock)
			where
				{$object_name}_Code = :code;
		";
        $result = $this->getFirstResultFromQuery($query, array(
            'code' => $code
        ));

        return $result && $result > 0 ? $result : false;
    }

    /**
     * Исполнение накладной-требования
     */
    function executeWhsDocumentSpecificity($data) {
		$this->load->model('DocumentUc_model', 'DocumentUc_model');

        $result = array();
        $error = array();
        $doc_data = array();
        $doc_drugs = array();
        $err_drugs = array();
        $new_doc_data = array();
        $new_doc_drugs = array();
        $total_need_cnt = 0; //счетчик количества медикаментов не покрытых исполнением
		$default_goods_unit_id = $this->DocumentUc_model->getDefaultGoodsUnitId();

        $debug_execution = false; //вывод отладочных сообщений

        $allowed_class = array ( //список кодов типов для которых доступно исполнение
            2 //2 - Накладная-требование
        );

        $not_allowed_status = array ( //список кодов статусов для которых запрещено исполнение
            1, //1 - Новый
            3, //3 - Отказ в исполнении
            6, //6 - Принят
            7, //7 - Отклонен
            8  //8 - Отменен
        );

		try {
			$this->beginTransaction();

			//получение данных документа
			$query = "
				select
					wdu.WhsDocumentUc_id,
					wds.WhsDocumentSpecificity_id,
					wds.Org_sid,
					wds.Storage_sid,
					wds.Org_tid,
					wds.Storage_tid,
					s_t.Storage_Name as Storage_tName,
					c_t.Contragent_id as Contragent_tid,
					wds.WhsDocumentStatusType_id,
					wdst.WhsDocumentStatusType_Code,
					wdc.WhsDocumentClass_Code,
					wds.DrugFinance_id,
					wds.WhsDocumentCostItemType_id
				from
					v_WhsDocumentUc wdu with (nolock)
					left join v_WhsDocumentSpecificity wds with (nolock) on wds.WhsDocumentUc_id = wdu.WhsDocumentUc_id
					left join v_WhsDocumentClass wdc on wdc.WhsDocumentClass_id = wdu.WhsDocumentClass_id
					left join v_WhsDocumentStatusType wdst on wdst.WhsDocumentStatusType_id = wdu.WhsDocumentStatusType_id
					left join v_Storage s_t with (nolock) on s_t.Storage_id = wds.Storage_tid
					outer apply (
						select top 1
							i_c.Contragent_id
						from
							v_Contragent i_c with (nolock)
							inner join v_ContragentOrg i_co with (nolock) on i_co.Contragent_id = i_c.Contragent_id
						where
							i_c.Org_id = wds.Org_tid and
							i_c.ContragentType_id = '5' and -- тип контрагента - медицинская организация
							i_co.Org_id = :Org_id
						order by
							i_co.ContragentOrg_id desc
					) c_t
				where
					wdu.WhsDocumentUc_id = :WhsDocumentUc_id;
			";
			$doc_data = $this->getFirstRowFromQuery($query, array(
				'WhsDocumentUc_id' => $data['WhsDocumentUc_id'],
				'Org_id' => $data['session']['org_id']
			));

			//проверки для заявки
			if (empty($doc_data['WhsDocumentSpecificity_id'])) {
				throw new Exception('Документ не найден');
			} elseif (!in_array($doc_data['WhsDocumentClass_Code'], $allowed_class)) { // проверка типа
				throw new Exception('Недопустимый тип документа');
			} elseif (in_array($doc_data['WhsDocumentStatusType_Code'], $not_allowed_status)) { // проверка статуса
				throw new Exception('Недопустимый статус документа');
			}
			if (!empty($doc_data['Storage_tid']) && !empty($data['MedService_id'])) { //если для исполнения передеан идентификатор службы, проверяем наличие связи между службой и складом поставщика
				$query = "
					select
						count(ssl.StorageStructLevel_id) as cnt
					from
						v_StorageStructLevel ssl with (nolock)
					where
						ssl.MedService_id = :MedService_id and
						ssl.Storage_id = :Storage_id;
				";
				$cnt = $this->getFirstResultFromQuery($query, array(
					'MedService_id' => $data['MedService_id'],
					'Storage_id' => $doc_data['Storage_tid']
				));
				if ($cnt <= 0) {
					throw new Exception("Исполнение заявки доступно только на складе \"{$doc_data['Storage_tName']}\"");
				}
			}

			//получение списка медикаментов из заявки
			$query = "
				select
					wds.DrugComplexMnn_id,
					isnull(wds.TRADENAMES_ID, 0) as Tradenames_id,
					isnull(wds.GoodsUnit_id, :DefaultGoodsUnit_id) as GoodsUnit_id,
					gpc.GoodsPackCount_Count,
					wds.WhsDocumentSpecification_Count as Kolvo,
					wds.WhsDocumentSpecification_Cost as Cost,
					min_gpc.GoodsUnit_id as MinGoodsUnit_id,
					min_gpc.GoodsPackCount_Count as MinGoodsPackCount_Count,                
					(case
						when isnull(gpc.GoodsPackCount_Count, 0) > 0 then wds.WhsDocumentSpecification_Count*(min_gpc.GoodsPackCount_Count/gpc.GoodsPackCount_Count)
						else null
					end) as MinGoodsUnit_Kolvo,
					(case
						when isnull(gpc.GoodsPackCount_Count, 0) > 0 then wds.WhsDocumentSpecification_Cost*(min_gpc.GoodsPackCount_Count/gpc.GoodsPackCount_Count)
						else null
					end) as MinGoodsUnit_Cost
				from
					v_WhsDocumentSpecification wds with (nolock)
					outer apply (
						select top 1
							i_gp.GoodsPackCount_Count,
							(
								case
									when i_gp.TRADENAMES_ID = wds.TRADENAMES_ID then 1
									else 0
								end
							) as sp -- поле для сортировки
						from
							GoodsPackCount i_gp with (nolock)
						where
							i_gp.DrugComplexMnn_id = wds.DrugComplexMnn_id and
							i_gp.GoodsUnit_id = wds.GoodsUnit_id
						order by
							sp desc, i_gp.GoodsPackCount_id
					) tbl_gpc
					outer apply (
						select (case
							when wds.GoodsUnit_id is null or wds.GoodsUnit_id = :DefaultGoodsUnit_id then 1
							else tbl_gpc.GoodsPackCount_Count
						end) as GoodsPackCount_Count
					) gpc
					outer apply (
						select top 1
							i_gp.GoodsUnit_id,
							i_gp.GoodsPackCount_Count
						from
							GoodsPackCount i_gp with (nolock)
						where
							i_gp.DrugComplexMnn_id = wds.DrugComplexMnn_id
						order by
							i_gp.GoodsPackCount_Count desc, i_gp.GoodsPackCount_id
					) min_gpc -- данные для персчета в самую мелкую ед. измерения которая есть в справочнике для данного медикамента
				where
					wds.WhsDocumentSpecificity_id = :WhsDocumentSpecificity_id
				order by
					wds.TRADENAMES_ID desc;
			";
			$res = $this->db->query($query, array(
				'WhsDocumentSpecificity_id' => $doc_data['WhsDocumentSpecificity_id'],
				'DefaultGoodsUnit_id' => $default_goods_unit_id
			));
			if (is_object($res)) {
				$doc_drugs_list = $res->result('array');
			}

			if ($debug_execution) {
				print "<br/> - - - Drug data:<br/>";
				foreach($doc_drugs as $dg) {
					print "Need dcm: {$dg['DrugComplexMnn_id']}; tn: {$dg['Tradenames_id']}; cnt: {$dg['Kolvo']};<br/>";
				}
			}

			if (count($doc_drugs_list) == 0) {
				throw new Exception('Список медикаментов пуст');
			} else {
				//отбор позиций бз данных для пересчета, такие позиции не получится корректно добавить в документ учета, поэтому нужно собрать их отдельно и вернуть списком пользователю
				foreach($doc_drugs_list as $drug_data) {
					if (!empty($drug_data['MinGoodsPackCount_Count'])) {
						array_push($doc_drugs, $drug_data);
					} else {
						array_push($err_drugs, $drug_data);
					}
				}
			}

			//получение списка остатков
			$ostat_data = array();
			$ostat_array = array();
			$sum = 0;
			$sum_r = 0;

            $query = "
                select
                    dor.Drug_id,
                    d.DrugComplexMnn_id,
                    isnull(d.DrugTorg_id, 0) as Tradenames_id,
                    dor.DrugOstatRegistry_Kolvo as bKolvo,
                    isnull(dor.GoodsUnit_id, :DefaultGoodsUnit_id) as GoodsUnit_bid, -- ед. учета
                    b_gpc.GoodsPackCount_Count as GoodsPackCount_bCount,
                    isnull(dus.GoodsUnit_id, :DefaultGoodsUnit_id) as GoodsUnit_id, -- ед. списания
					gpc.GoodsPackCount_Count,
                    dsl.DocumentUcStr_id,
                    dus.DocumentUcStr_Price as Price,
                    dus.DocumentUcStr_PriceR as PriceR,
                    min_gpc.GoodsUnit_id as MinGoodsUnit_id,
					min_gpc.GoodsPackCount_Count as MinGoodsPackCount_Count,                
					(case
						when isnull(gpc.GoodsPackCount_Count, 0) > 0 then dor.DrugOstatRegistry_Kolvo*(min_gpc.GoodsPackCount_Count/b_gpc.GoodsPackCount_Count)
						else null
					end) as MinGoodsUnit_Kolvo
                from
                    v_DrugOstatRegistry dor with (nolock)
                    left join v_SubAccountType sat on sat.SubAccountType_id = dor.SubAccountType_id
                    left join v_DrugShipmentLink dsl with (nolock) on dsl.DrugShipment_id = dor.DrugShipment_id
                    left join rls.v_Drug d on d.Drug_id = dor.Drug_id
                    left join v_DocumentUcStr dus with (nolock) on dus.DocumentUcStr_id = dsl.DocumentUcStr_id
					outer apply (
						select top 1
							i_gp.GoodsPackCount_Count,
							(
								case
									when i_gp.TRADENAMES_ID = d.DrugTorg_id then 1
									else 0
								end
							) as sp -- поле для сортировки
						from
							GoodsPackCount i_gp with (nolock)
						where
							i_gp.DrugComplexMnn_id = d.DrugComplexMnn_id and
							i_gp.GoodsUnit_id = dor.GoodsUnit_id
						order by
							sp desc, i_gp.GoodsPackCount_id
					) b_tbl_gpc
					outer apply (
						select (case
							when dor.GoodsUnit_id is null or dor.GoodsUnit_id = :DefaultGoodsUnit_id then 1
							else b_tbl_gpc.GoodsPackCount_Count
						end) as GoodsPackCount_Count
					) b_gpc
                    outer apply (
						select top 1
							i_gp.GoodsPackCount_Count,
							(
								case
									when i_gp.TRADENAMES_ID = d.DrugTorg_id then 1
									else 0
								end
							) as sp -- поле для сортировки
						from
							GoodsPackCount i_gp with (nolock)
						where
							i_gp.DrugComplexMnn_id = d.DrugComplexMnn_id and
							i_gp.GoodsUnit_id = dus.GoodsUnit_id
						order by
							sp desc, i_gp.GoodsPackCount_id
					) tbl_gpc
					outer apply (
						select (case
							when dus.GoodsUnit_id is null or dus.GoodsUnit_id = :DefaultGoodsUnit_id then 1
							else tbl_gpc.GoodsPackCount_Count
						end) as GoodsPackCount_Count
					) gpc
					outer apply (
						select top 1
							i_gp.GoodsUnit_id,
							i_gp.GoodsPackCount_Count
						from
							GoodsPackCount i_gp with (nolock)
						where
							i_gp.DrugComplexMnn_id = d.DrugComplexMnn_id
						order by
							i_gp.GoodsPackCount_Count desc, i_gp.GoodsPackCount_id
					) min_gpc
                where
                    sat.SubAccountType_Code = 1 and -- 1 - Доступно
                    dor.Org_id = :Org_id and
                    (
                        :Storage_id is null or
                        dor.Storage_id = :Storage_id
                    ) and
                    dor.DrugOstatRegistry_Kolvo > 0 and
                    dsl.DocumentUcStr_id is not null and
                    exists (
                        select
                            wds.WhsDocumentSpecification_id
                        from
                            v_WhsDocumentSpecification wds with (nolock)
                        where
                            wds.WhsDocumentSpecificity_id = :WhsDocumentSpecificity_id and
                            wds.DrugComplexMnn_id = d.DrugComplexMnn_id and
                            (
                                wds.TRADENAMES_ID is null or
                                wds.TRADENAMES_ID = d.DrugTorg_id
                            )
                    );
            ";

            $res = $this->db->query($query, array(
                'WhsDocumentSpecificity_id' => $doc_data['WhsDocumentSpecificity_id'],
                'Org_id' => $doc_data['Org_tid'],
                'Storage_id' => $doc_data['Storage_tid'],
				'DefaultGoodsUnit_id' => $default_goods_unit_id
            ));
            if (is_object($res)) {
                $ostat_array = $res->result('array');
            }

            if ($debug_execution) {
                print "<br/> - - - Ostat data:<br/>";
                foreach($ostat_array as $dg) {
                    print "Ost dcm: {$dg['DrugComplexMnn_id']}; tn: {$dg['Tradenames_id']}; cnt: {$dg['MinGoodsUnit_Kolvo']};<br/>";
                }
            }

            //упаковка данных об остатках в вид, более удобный для поиска данных (поиск по комплексному и торговому)
            for ($i = 0; $i < count($ostat_array); $i++) {
                $mnn_id = $ostat_array[$i]['DrugComplexMnn_id'];
                $tn_id = $ostat_array[$i]['Tradenames_id'];
                if (!isset($ostat_data[$mnn_id])) {
                    $ostat_data[$mnn_id] = array();
                }
                if (!isset($ostat_data[$mnn_id][$tn_id])) {
                    $ostat_data[$mnn_id][$tn_id] = array();
                }
                $ostat_data[$mnn_id][$tn_id][] = $ostat_array[$i];
            }

            //обработка позиций заявки с указанным торговым
            for($tn_count = 0; $tn_count < count($doc_drugs); $tn_count++) {
                if ($doc_drugs[$tn_count]['Tradenames_id'] > 0) {
                    $need_cnt = $doc_drugs[$tn_count]['MinGoodsUnit_Kolvo']; //записываем сколько нужно медикамента по заявке
                    $mnn_id = $doc_drugs[$tn_count]['DrugComplexMnn_id'];
                    $tn_id = $doc_drugs[$tn_count]['Tradenames_id'];

                    if ($debug_execution) {
                        print "<br/>Search start. dcm: {$mnn_id}; tn: {$tn_id}; need: {$need_cnt};";
                    }

                    if ($need_cnt > 0 && isset($ostat_data[$mnn_id]) && isset($ostat_data[$mnn_id][$tn_id])) { //ищем остатки с подходящими параметрами
                        for ($j = 0; $j < count($ostat_data[$mnn_id][$tn_id]); $j++) { //
                            if ($ostat_data[$mnn_id][$tn_id][$j]['MinGoodsUnit_Kolvo'] <= $need_cnt) {
                                $kolvo = $ostat_data[$mnn_id][$tn_id][$j]['MinGoodsUnit_Kolvo'];
                            } else {
                                $kolvo = $need_cnt;
                            }
                            $need_cnt -= $kolvo;

                            if ($debug_execution) {
                                print "<br/>Kolvo: {$kolvo}; Ostat - before: {$ostat_data[$mnn_id][$tn_id][$j]['MinGoodsUnit_Kolvo']};";
                            }

                            $ostat_data[$mnn_id][$tn_id][$j]['MinGoodsUnit_Kolvo'] -= $kolvo;

                            if ($debug_execution) {
                                print " after: {$ostat_data[$mnn_id][$tn_id][$j]['MinGoodsUnit_Kolvo']}; need: {$need_cnt};";
                            }

                            //заполняем массив данными о медикаменте
                            if ($kolvo > 0) {
                            	//количество приходит в "минимальных ед. измерения", поэтому его надо пересчитать
								$count = ($kolvo/$ostat_data[$mnn_id][$tn_id][$j]['MinGoodsPackCount_Count'])*$ostat_data[$mnn_id][$tn_id][$j]['GoodsPackCount_bCount'];
								$ed_count = ($kolvo/$ostat_data[$mnn_id][$tn_id][$j]['MinGoodsPackCount_Count'])*$ostat_data[$mnn_id][$tn_id][$j]['GoodsPackCount_Count'];
                                $new_doc_drugs[] = array(
                                    'DocumentUcStr_id' => $ostat_data[$mnn_id][$tn_id][$j]['DocumentUcStr_id'],
                                    'DocumentUcStr_Count' => $count,
                                    'DocumentUcStr_EdCount' => $ed_count
                                );
                                $sum += ($count * $ostat_data[$mnn_id][$tn_id][$j]['Price']);
                                $sum_r += ($count * $ostat_data[$mnn_id][$tn_id][$j]['PriceR']);
                            }
                        }
                    }

                    $total_need_cnt += $need_cnt;

                    if ($debug_execution) {
                        print "<br/>need: {$need_cnt}; total_need: {$total_need_cnt}; <br/> - - - - - - - - - - - - - - - <br/>";
                    }
                } else {
                    break;
                }
            }

            //перепаковка данных об остатках в вид, более удобный для поиска данных (поиск по комплексному)
            $ostat_array = $ostat_data;
            $ostat_data = array();
            foreach ($ostat_array as $mnn_id => $arr) {
                if (!isset($ostat_data[$mnn_id])) {
                    $ostat_data[$mnn_id] = array();
                }
                foreach ($arr as $item1) {
                    foreach ($item1 as $item2) {
                        if ($item2['MinGoodsUnit_Kolvo'] > 0) {
                            $ostat_data[$mnn_id][] = $item2;
                        }
                    }
                }
            }

            //обработка позиций заявки без указанного торгового
            for($i = $tn_count; $i < count($doc_drugs); $i++) {
                $need_cnt = $doc_drugs[$i]['MinGoodsUnit_Kolvo']; //записываем сколько нужно медикамента по заявке
                $mnn_id = $doc_drugs[$i]['DrugComplexMnn_id'];

                if ($debug_execution) {
                    print "<br/>Search start. dcm: {$mnn_id}; need: {$need_cnt};";
                }

                if ($need_cnt > 0 && isset($ostat_data[$mnn_id])) { //ищем остатки с подходящими параметрами
                    for ($j = 0; $j < count($ostat_data[$mnn_id]); $j++) { //
                        if ($ostat_data[$mnn_id][$j]['MinGoodsUnit_Kolvo'] <= $need_cnt) {
                            $kolvo = $ostat_data[$mnn_id][$j]['MinGoodsUnit_Kolvo'];
                        } else {
                            $kolvo = $need_cnt;
                        }
                        $need_cnt -= $kolvo;

                        if ($debug_execution) {
                            print "<br/>Kolvo: {$kolvo}; Ostat - before: {$ostat_data[$mnn_id][$j]['MinGoodsUnit_Kolvo']};";
                        }

                        $ostat_data[$mnn_id][$j]['MinGoodsUnit_Kolvo'] -= $kolvo;

                        if ($debug_execution) {
                            print " after: {$ostat_data[$mnn_id][$j]['MinGoodsUnit_Kolvo']}; need: {$need_cnt};";
                        }

                        //заполняем массив данными о медикаменте
                        if ($kolvo > 0) {
                            //количество приходит в "минимальных ед. измерения", поэтому его надо пересчитать
							$count = ($kolvo/$ostat_data[$mnn_id][$j]['MinGoodsPackCount_Count'])*$ostat_data[$mnn_id][$j]['GoodsPackCount_bCount'];
							$ed_count = ($kolvo/$ostat_data[$mnn_id][$j]['MinGoodsPackCount_Count'])*$ostat_data[$mnn_id][$j]['GoodsPackCount_Count'];
							$new_doc_drugs[] = array(
								'DocumentUcStr_id' => $ostat_data[$mnn_id][$j]['DocumentUcStr_id'],
								'DocumentUcStr_Count' => $count,
								'DocumentUcStr_EdCount' => $ed_count
							);
							$sum += ($count * $ostat_data[$mnn_id][$j]['Price']);
							$sum_r += ($count * $ostat_data[$mnn_id][$j]['PriceR']);
                        }
                    }
                }

                $total_need_cnt += $need_cnt;

                if ($debug_execution) {
                    print "<br/>need: {$need_cnt}; total_need: {$total_need_cnt}; <br/> - - - - - - - - - - - - - - - <br/>";
                }
            }

			if (count($new_doc_drugs) == 0) {
				throw new Exception('На остатках нет медикаментов для исполнения документа');
			}

			//формирование шапки документа
			$new_doc_data['DocumentUc_id'] = null;
			$new_doc_data['DrugDocumentType_Code'] = 15; // 15 - Накладная на внутреннее перемещение
			$new_doc_data['DrugDocumentType_id'] = $this->getObjectIdByCode('DrugDocumentType', $new_doc_data['DrugDocumentType_Code']);

			$current_date = date("Y-m-d");

            $saved_data = array(
                'DocumentUc_id' => null,
                'DocumentUc_Num' => $data['DocumentUc_NewNum'],
                'DocumentUc_setDate' => $current_date,
                'DocumentUc_didDate' => $current_date,
                'DocumentUc_Sum' => $sum,
                'DocumentUc_SumR' => $sum_r,
                'Contragent_id' => $data['Contragent_id'],
                'Contragent_sid' => $doc_data['Contragent_tid'],
                'Contragent_tid' => null,
                'DrugFinance_id' => $doc_data['DrugFinance_id'],
                'DrugDocumentType_id' => $new_doc_data['DrugDocumentType_id'],
                'DrugDocumentStatus_id' => $this->getObjectIdByCode('DrugDocumentStatus', 1), // 1 - Новый
                'Org_id' => $data['session']['org_id'],
                'Storage_sid' => $doc_data['Storage_tid'], //потомучто в таблице с заявками поля почемуто названы наоборот и Storage_tid это склад поставщика
                'Storage_tid' => $doc_data['Storage_sid'],
                'WhsDocumentUc_id' => $doc_data['WhsDocumentUc_id'],
                'WhsDocumentCostItemType_id' => $doc_data['WhsDocumentCostItemType_id'],
                'pmUser_id' => $data['pmUser_id']
            );

            $save_result = $this->saveObject('DocumentUc', $saved_data);
            if (empty($save_result['Error_Msg'])) {
                if (!empty($save_result['DocumentUc_id'])) {
                    $new_doc_data['DocumentUc_id'] = $save_result['DocumentUc_id'];
                } else {
					throw new Exception('При сохранении документа учета произошла ошибка');
                }
            } else {
				throw new Exception($save_result['Error_Msg']);
            }

	        //формирование списка медикаментов
            for ($i = 0; $i < count($new_doc_drugs); $i++) {
                $query = "
                    select	
                        dus.Drug_id,
                        dus.DrugFinance_id,
                        dus.DrugNds_id,
                        dus.DrugProducer_id,
                        dus.DocumentUcStr_Price,
                        dus.DocumentUcStr_PriceR,
                        dus.DocumentUcStr_Count,
                        dus.DocumentUcStr_Sum,
                        dus.DocumentUcStr_SumR,
                        dus.DocumentUcStr_Ser,
                        dus.DocumentUcStr_CertNum,
                        dus.DocumentUcStr_CertDate,
                        dus.DocumentUcStr_CertGodnDate,
                        dus.DocumentUcStr_CertOrg,
                        dus.DrugLabResult_Name,
                        dus.PrepSeries_id,
                        dus.DocumentUcStr_IsNDS,
                        dus.GoodsUnit_bid,
                        dus.GoodsUnit_id
                    from
                        v_DocumentUcStr dus with (nolock)
                    where
                        dus.DocumentUcStr_id = :DocumentUcStr_id
                ";
                $str_data = $this->getFirstRowFromQuery($query, array(
                    'DocumentUcStr_id' => $new_doc_drugs[$i]['DocumentUcStr_id']
                ));

                $count = $new_doc_drugs[$i]['DocumentUcStr_Count']*1;
                $ed_count = $new_doc_drugs[$i]['DocumentUcStr_EdCount']*1;

                $saved_data = array(
                    'DocumentUcStr_id' => null,
                    'DocumentUcStr_oid' => $new_doc_drugs[$i]['DocumentUcStr_id'],
                    'DocumentUc_id' => $new_doc_data['DocumentUc_id'],
                    'Drug_id' => $str_data['Drug_id'],
                    'DrugFinance_id' => $str_data['DrugFinance_id'],
                    'DrugNds_id' => $str_data['DrugNds_id'],
                    'DocumentUcStr_Price' => $str_data['DocumentUcStr_Price'],
                    'DocumentUcStr_PriceR' => $str_data['DocumentUcStr_PriceR'],
                    'DocumentUcStr_Count' => $count,
                    'DocumentUcStr_EdCount' => $ed_count,
                    'DocumentUcStr_Sum' => $str_data['DocumentUcStr_Price'] * $count,
                    'DocumentUcStr_SumR' => $str_data['DocumentUcStr_PriceR'] * $count,
                    'DocumentUcStr_Ser' => $str_data['DocumentUcStr_Ser'],
                    'DocumentUcStr_CertNum' => $str_data['DocumentUcStr_CertNum'],
                    'DocumentUcStr_CertDate' => $str_data['DocumentUcStr_CertDate'],
                    'DocumentUcStr_CertGodnDate' => $str_data['DocumentUcStr_CertGodnDate'],
                    'DocumentUcStr_CertOrg' => $str_data['DocumentUcStr_CertOrg'],
                    'DrugLabResult_Name' => $str_data['DrugLabResult_Name'],
                    'PrepSeries_id' => $str_data['PrepSeries_id'],
                    'DocumentUcStr_IsNDS' => $str_data['DocumentUcStr_IsNDS'],
                    'WhsDocumentUc_id' => $doc_data['WhsDocumentUc_id'],
					'GoodsUnit_bid' => $str_data['GoodsUnit_bid'],
					'GoodsUnit_id' => $str_data['GoodsUnit_id'],
                    'pmUser_id' => $data['pmUser_id']
                );

                $save_result = $this->saveObject('DocumentUcStr', $saved_data);
                if (empty($save_result['Error_Msg'])) {
                    if (empty($save_result['DocumentUcStr_id'])) {
						throw new Exception('При сохранении строки документа учета произошла ошибка');
                    }
                } else {
					throw new Exception($save_result['Error_Msg']);
                }
            }

			//записываем в результат данные созданного документа
			if (!empty($new_doc_data['DocumentUc_id'])) {
				$result['DocumentUc_id'] = $new_doc_data['DocumentUc_id'];
				$result['DrugDocumentType_Code'] = $new_doc_data['DrugDocumentType_Code'];
			}

	        //смена статуса документа
            $save_result = $this->saveObject('WhsDocumentUc', array(
                'WhsDocumentUc_id' => $doc_data['WhsDocumentUc_id'],
                'WhsDocumentStatusType_id' => $this->getObjectIdByCode('WhsDocumentStatusType', 6), //6 - Принят
                'pmUser_id' => $data['pmUser_id']
            ));
            if (empty($save_result['Error_Msg'])) {
                if (empty($save_result['WhsDocumentUc_id'])) {
					throw new Exception('При сохранении документа учета произошла ошибка');
                }
            } else {
				throw new Exception($save_result['Error_Msg']);
            }

            $save_result = $this->saveObject('WhsDocumentSpecificity', array(
                'WhsDocumentSpecificity_id' => $doc_data['WhsDocumentSpecificity_id'],
                'WhsDocumentStatusType_id' => $this->getObjectIdByCode('WhsDocumentStatusType', 6), //6 - Принят
                'pmUser_id' => $data['pmUser_id']
            ));
            if (empty($save_result['Error_Msg'])) {
                if (empty($save_result['WhsDocumentSpecificity_id'])) {
					throw new Exception('При сохранении документа учета произошла ошибка');
                }
            } else {
				throw new Exception($save_result['Error_Msg']);
            }

			$result['success'] = true;
			$this->commitTransaction();
		} catch (Exception $e) {
			$result['successs'] = false;
			$result['Error_Msg'] = $e->getMessage();
			$this->rollbackTransaction();
		}

		return $result;
    }

    /**
     * Отмена заявки
     */
    function cancelWhsDocumentSpecificity($data) {
        $error = array();

        $query = "
            select
                wdst.WhsDocumentStatusType_Code
            from
                v_WhsDocumentUc wdu with (nolock)
				left join v_WhsDocumentStatusType wdst with(nolock) on wdst.WhsDocumentStatusType_id = wdu.WhsDocumentStatusType_id
            where
                wdu.WhsDocumentUc_id = :WhsDocumentUc_id;
        ";
        $doc_data = $this->getFirstRowFromQuery($query, array(
            'WhsDocumentUc_id' => $data['WhsDocumentUc_id']
        ));

        if (!is_array($doc_data) || count($doc_data) == 0) {
            $error[] = 'Не удалось получить данные заявки';
        }

        //проверка статуса
        if (count($error) <= 0 && !in_array($doc_data['WhsDocumentStatusType_Code'], array('1', '4', '5', '7'))) { //1 - Новый; 4 - Сформирован; 5 - Утвержден; 7 - Отклонен
            $error[] = 'Статус заявки не допускает отмены';
        }

        $this->beginTransaction();

        $query = "
            declare
                @WhsDocumentStatusType_id bigint,
                @datetime datetime;

            set @WhsDocumentStatusType_id = (select top 1 WhsDocumentStatusType_id from v_WhsDocumentStatusType with (nolock) where WhsDocumentStatusType_Code = 8); -- 8 - Отменен
            set @datetime = dbo.tzGetDate();

			update
			    WhsDocumentUc
			set
			    WhsDocumentStatusType_id = @WhsDocumentStatusType_id,
			    pmUser_updID = :pmUser_id,
                WhsDocumentUc_updDT = @datetime
            where
                WhsDocumentUc_id = :WhsDocumentUc_id;
		";
        $this->db->query($query, array(
            'WhsDocumentUc_id' => $data['WhsDocumentUc_id'],
            'pmUser_id' => $data['pmUser_id']
        ));

        if (count($error) > 0) {
            $this->rollbackTransaction();
            return $this->createError('', $error[0]);
        } else {
            $this->commitTransaction();
            return array('success' => true);
        }
    }

    /**
     * Удаление заявки
     */
    function deleteWhsDocumentSpecificity($data) {
        $error = array();

        $query = "
            select
                wdu.WhsDocumentUc_id,
                wds.WhsDocumentSpecificity_id,
                wdst.WhsDocumentStatusType_Code
            from
                v_WhsDocumentSpecificity wds with (nolock)
                left join v_WhsDocumentUc wdu with (nolock) on wdu.WhsDocumentUc_id = wds.WhsDocumentUc_id
				left join v_WhsDocumentStatusType wdst with(nolock) on wdst.WhsDocumentStatusType_id = wdu.WhsDocumentStatusType_id
            where
                wds.WhsDocumentUc_id = :WhsDocumentUc_id;
        ";
        $doc_data = $this->getFirstRowFromQuery($query, array(
            'WhsDocumentUc_id' => $data['WhsDocumentUc_id']
        ));

        if (!is_array($doc_data) || count($doc_data) == 0) {
            $error[] = 'Не удалось получить данные заявки';
        }

        //проверка статуса
        if (count($error) <= 0 && !in_array($doc_data['WhsDocumentStatusType_Code'], array('1', '4', '5'))) { //1 - Новый; 4 - Сформирован; 5 - Утвержден.
            $error[] = 'Статус заявки не допускает удаления';
        }

        $this->beginTransaction();

		$query = "
			select WhsDocumentSpecification_id
			from v_WhsDocumentSpecification with(nolock)
			where WhsDocumentSpecificity_id = :WhsDocumentSpecificity_id
		";
		$list = $this->queryResult($query, array(
			'WhsDocumentSpecificity_id' => $doc_data['WhsDocumentSpecificity_id']
		));
		if (!is_array($list)) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при получении получении списка медикаментов в заявке');
		}
		foreach($list as $item) {
			$resp = $this->deleteWhsDocumentSpecification($item);
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		if (count($error) <= 0 && !empty($doc_data['WhsDocumentUc_id'])) {
			$query = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);
				exec p_WhsDocumentSpecificity_del
					@WhsDocumentSpecificity_id = :WhsDocumentSpecificity_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$response = $this->queryResult($query, array(
				'WhsDocumentSpecificity_id' => $doc_data['WhsDocumentSpecificity_id']
			));
			if (!is_array($response)) {
				$error[] = 'Ошибка при удалении заявки';
			}
		}

        if (count($error) <= 0 && !empty($doc_data['WhsDocumentUc_id'])) {
            //удаляем шапку документа (по хорошему это должно быть в хранимке)
            $query = "
                declare
                    @ErrCode int,
                    @ErrMessage varchar(4000);
                exec p_WhsDocumentUc_del
                    @WhsDocumentUc_id = :WhsDocumentUc_id,
                    @Error_Code = @ErrCode output,
                    @Error_Message = @ErrMessage output;
                select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
            ";
            $res = $this->queryResult($query, array(
                'WhsDocumentUc_id' => $doc_data['WhsDocumentUc_id']
            ));
            if (!is_array($res)) {
                $error[] = 'Ошибка при удалении заявки';
            }
        }

        if (count($error) > 0) {
            $this->rollbackTransaction();
            return $this->createError('', $error[0]);
        } else {
            $this->commitTransaction();
            return $response;
        }
    }

    /**
     * Загрузка списка для комбобокса
     */
	function loadGoodsUnitCombo($data) {
		$where = array();
		$params = $data;

		if (empty($data['Drug_id']) && empty($data['DrugComplexMnn_id']) && empty($data['Tradnames_id'])) {
			return false;
		}

		if (!empty($data['GoodsUnit_id'])) {
			$where[] = "gu.GoodsUnit_id = @GoodsUnit_id";
		} else {
			$descr_arr = array('единицы в упаковках');
			if (!empty($data['UserOrg_Type']) && $data['UserOrg_Type'] == 'lpu') { //если организация поставщика является ММО
				$descr_arr = array('единицы в упаковках', 'единицы количества', 'лекарственная форма');
			}
			$where[] = "(gpc.GoodsUnit_id is not null and gu.GoodsUnit_Descr in ('".join("', '", $descr_arr)."'))";

			if (!empty($data['query'])) {
				$where[] = "(gu.GoodsUnit_Nick like :query or gu.GoodsUnit_Name like :query)";
				$params['query'] = $data['query']."%";
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
		    declare
                @GoodsUnit_id bigint = :GoodsUnit_id,
                @Drug_id bigint = :Drug_id,
                @DrugComplexMnn_id bigint = :DrugComplexMnn_id,
                @Tradnames_id bigint = :Tradnames_id;

            if (@Drug_id is not null)
            begin
                select
                    @DrugComplexMnn_id = DrugComplexMnn_id,
                    @Tradnames_id = d.DrugTorg_id
                from
                    rls.v_Drug d with (nolock)
                where
                    Drug_id = @Drug_id
            end;

            select
                gu.GoodsUnit_id,
                gu.GoodsUnit_Name,
                (
                    isnull(gu.GoodsUnit_Nick, '') +
                    isnull(' / ' + gu.GoodsUnit_Name, '') +
                    isnull(' / ' + (case
                        when gu.GoodsUnit_Name = 'упаковка' then '1'
                        else cast(cast(gpc.GoodsPackCount_Count as decimal(10,0)) as varchar(10))
                    end) + ' шт. в уп.', '')
                ) as GoodsUnit_Str,
                (case
                    when gu.GoodsUnit_Name = 'упаковка' then 1
                    else gpc.GoodsPackCount_Count
                end) as GoodsPackCount_Count,
                (case
                    when gpc.GoodsPackCount_Count is not null then 'table'
                    else null
                end) as GoodsPackCount_Source
            from
                v_GoodsUnit gu with (nolock)
                outer apply (
                    select top 1
                        i_gpc.GoodsUnit_id,
                        i_gpc.GoodsPackCount_Count
                    from
                        v_GoodsPackCount i_gpc with (nolock)
                    where
                        i_gpc.GoodsUnit_id = gu.GoodsUnit_id and
                        i_gpc.DrugComplexMnn_id = @DrugComplexMnn_id and
                        (
                            @Tradnames_id is null or
                            i_gpc.TRADENAMES_ID is null or
                            i_gpc.TRADENAMES_ID = @Tradnames_id
                        ) and
                        (
                            i_gpc.Org_id is null or
                            isnull(i_gpc.Org_id, 0) = isnull(:UserOrg_id, 0)
                        )
                    order by
                        i_gpc.TRADENAMES_ID desc, i_gpc.Org_id desc
                ) gpc
            {$where_clause}
            union
            select
                gu.GoodsUnit_id,
                gu.GoodsUnit_Name,
                (
                    isnull(gu.GoodsUnit_Nick, '') +
                    isnull(' / ' + gu.GoodsUnit_Name, '') +
                    ' / 1 шт. в уп.'
                ) as GoodsUnit_Str,
                1 as GoodsPackCount_Count,
                'fixed_value' as GoodsPackCount_Count
            from
                v_GoodsUnit gu with (nolock)
            where
                @GoodsUnit_id is null and -- упаковка добавляется в список только если не передан id конкретной записи
                gu.GoodsUnit_Name = 'упаковка' and
                (
                    :query is null or
                    'упаковка' like :query
                )
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

    /**
     * Загрузка списка для комбобокса
     */
    function loadOrgCombo($data) {
        $where = array();
        $params = array();

        if (!empty($data['Org_id'])) {
            $where[] = 'o.Org_id = :Org_id';
            $params['Org_id'] = $data['Org_id'];
        } else {
            if (!empty($data['query'])) {
                $where[] = 'o.Org_Name like :query';
                $params['query'] = "%".$data['query']."%";
            }
            if (!empty($data['OrgType_id'])) {
                $where[] = 'o.OrgType_id = :OrgType_id';
                $params['OrgType_id'] = $data['OrgType_id'];
            }
        }

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause)) {
            $where_clause = "
				where
					{$where_clause}
			";
        }

        $query = "
		    select top 250
		        o.Org_id,
		        o.Org_Name
		    from
                v_Org o with (nolock)
		    {$where_clause}
		";

        $result = $this->db->query($query, $params);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
}