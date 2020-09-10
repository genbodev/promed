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

class WhsDocumentUc_model extends swPgModel {
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
            $where[] = "wdt.WhsDocumentType_Code = :WhsDocumentType_Code::varchar";
            $params['WhsDocumentType_Code'] = $data['WhsDocumentType_Code'];
        }
        if (!empty($data['Ins_Year'])) {
            $where[] = "date_part('year', wdu.WhsDocumentUc_insDT) = :Ins_Year";
            $params['Ins_Year'] = $data['Ins_Year'];
        }
        if (isset($data['Org_aid'])) {
            $where[] = "COALESCE(wdu.Org_aid, 0) = COALESCE(:Org_aid::bigint, 0)";

            $params['Org_aid'] = $data['Org_aid'];
        }

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause)) {
            $where_clause = ' and '.$where_clause;
        }

		$query = "
			select
				COALESCE(max(cast(wdu.WhsDocumentUc_Num as bigint)), 0)+1 as \"num\"

			from
				v_WhsDocumentUc wdu 

				left join v_WhsDocumentType wdt  on wdt.WhsDocumentType_id = wdu.WhsDocumentType_id

			where
				wdu.WhsDocumentUc_Num not iLIKE '%.%' and

				wdu.WhsDocumentUc_Num not iLIKE '%,%' and

				wdu.WhsDocumentUc_Num not iLIKE '%e%' and

				length(wdu.WhsDocumentUc_Num) <= 18 and
				isnumeric(wdu.WhsDocumentUc_Num || 'e0') = 1
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
				left join rls.v_Drug D  on D.Drug_id = ECTD.Drug_id

			";
			$filters .= " and D.DrugTorg_id = :Tradenames_id";
			$params['Tradenames_id'] = $data['Tradenames_id'];
		}
		if (!empty($data['MedService_id'])) {
			/*$joins .= "
				left join v_LpuSection LS  on LS.LpuSection_id = ES.LpuSection_id

				left join v_LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id

				left join v_LpuUnitType LUT  on LUT.LpuUnitType_id = LU.LpuUnitType_id

				left join v_LpuBuilding LB  on LB.LpuBuilding_id =LU.LpuBuilding_id

				left join v_Lpu L  on L.Lpu_id = LB.Lpu_id

			";
			$filters .= " and :MedService_id in (
				select MS.MedService_id
				from v_MedService MS 

				where
					COALESCE(MS.Lpu_id, L.Lpu_id) = L.Lpu_id

					and COALESCE(MS.LpuBuilding_id, LB.LpuBuilding_id) = LB.LpuBuilding_id

					and COALESCE(MS.LpuUnitType_id, LUT.LpuUnitType_id) = LUT.LpuUnitType_id

					and COALESCE(MS.LpuUnit_id, LU.LpuUnit_id) = LU.LpuUnit_id

					and COALESCE(MS.LpuSection_id, LS.LpuSection_id) = LS.LpuSection_id

			)";*/
			$filters .= "
				and ES.LpuSection_id in (
					select
						i_ls_ssl.LpuSection_id
					from
						v_StorageStructLevel i_ssl 

						left join v_Storage i_s  on i_s.Storage_id = i_ssl.Storage_id or i_s.Storage_pid = i_ssl.Storage_id -- склады прописаны на службе и их дочерние склады

						LEFT JOIN LATERAL ( -- проверка не связаны ли дочерние склады со службой с типом АРМ товароведа

							select 
								ii_ms.MedService_id
							from
								v_StorageStructLevel ii_ssl 

								left join v_MedService ii_ms  on ii_ms.MedService_id = ii_ssl.MedService_id			

								left join v_MedServiceType ii_mst  on ii_mst.MedServiceType_id = ii_ms.MedServiceType_id

							where
								i_s.Storage_id <> i_ssl.Storage_id and
								ii_ssl.Storage_id = i_s.Storage_id and
								ii_mst.MedServiceType_SysNick = 'merch'
                            limit 1
						) i_ms ON true
						left join v_StorageStructLevel i_ls_ssl  on i_ls_ssl.Storage_id = i_s.Storage_id -- получаем связь с отделениями для полученой совокупности складов

					where
						i_ssl.MedService_id = :MedService_id and
						i_ms.MedService_id is null and
						i_ls_ssl.LpuSection_id is not null
				)
			";
			$params['MedService_id'] = $data['MedService_id'];
		}

		$query = "
			select distinct
				PS.Person_id as \"Person_id\",
				rtrim(PS.Person_SurName)||' '||rtrim(PS.Person_FirName)||rtrim(COALESCE(' '||PS.Person_SecName,'')) as \"Person_Fio\",

				EPS.EvnPS_NumCard as \"EvnPS_NumCard\",
				ECT.EvnCourseTreat_id as \"EvnCourseTreat_id\",
				to_char(ECT.EvnCourseTreat_setDate, 'DD.MM.YYYY') as \"EvnCourseTreat_setDate\",

				ECT.EvnCourseTreat_Duration as \"EvnCourseTreat_Duration\"
			from
				v_EvnCourseTreatDrug ECTD 

				inner join v_EvnCourseTreat ECT  on ECT.EvnCourseTreat_id = ECTD.EvnCourseTreat_id

				inner join v_EvnSection ES  on ES.EvnSection_id = ECT.EvnCourseTreat_pid

				inner join v_EvnPS EPS  on EPS.EvnPS_id = ES.EvnSection_pid

				inner join v_PersonState PS  on PS.Person_id = ES.Person_id

				{$joins}
			where
				{$filters}
			order by
				2, -- Person_Fio
				5  -- EvnCourseTreat_setDate
			limit 100
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
				select OrgFarmacy_id  as \"OrgFarmacy_id\" from OrgFarmacy  where Org_id = :Org_id limit 1

			", array('Org_id' => $data['Org_id']), true);

			$filters .= " and RO.OrgFarmacy_id = :OrgFarmacy_id";
			$params['OrgFarmacy_id'] = $OrgFarmacy_id;
		}
		if (!empty($data['DrugComplexMnn_id'])) {
			$filters .= " and ER.DrugComplexMnn_id = :DrugComplexMnn_id";
			$params['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];
		}

		$query = "
			select 
				RO.Person_id as \"Person_id\",
				RO.Person_Fio as \"Person_Fio\",
				RO.ReceptOtov_id as \"ReceptOtov_id\",
				ER.EvnRecept_id as \"EvnRecept_id\",
				ER.EvnRecept_Ser as \"EvnRecept_Ser\",
				ER.EvnRecept_Num as \"EvnRecept_Num\",
				to_char(ER.EvnRecept_setDate, 'DD.MM.YYYY') as \"EvnRecept_setDate\"

			from
				v_ReceptOtov_all RO 

				inner join v_EvnRecept ER  on ER.EvnRecept_id = RO.EvnRecept_id

			where
				RO.ReceptDelayType_id = 2
				{$filters}
			order by
				2, --Person_Fio,
				7  --EvnRecept_setDate
			limit 100
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
                    wdu.WhsDocumentUc_id as \"WhsDocumentUc_id\",
                    wdu.WhsDocumentStatusType_id as \"WhsDocumentStatusType_id\",
                    wdu.WhsDocumentUc_Num as \"WhsDocumentUc_Num\"
                from
                    v_WhsDocumentUc wdu 

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
			select WhsDocumentUc_id as \"WhsDocumentUc_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from {$procedure}(
				WhsDocumentUc_id := :WhsDocumentUc_id,
				WhsDocumentUc_pid := :WhsDocumentUc_pid,
				WhsDocumentUc_Num := :WhsDocumentUc_Num,
				WhsDocumentUc_Name := :WhsDocumentUc_Name,
				WhsDocumentType_id := :WhsDocumentType_id,
				WhsDocumentUc_Date := :WhsDocumentUc_Date,
				WhsDocumentUc_Sum := :WhsDocumentUc_Sum,
				WhsDocumentStatusType_id := :WhsDocumentStatusType_id,
				Org_aid := :Org_aid,
				WhsDocumentClass_id := :WhsDocumentClass_id,
				pmUser_id := :pmUser_id);


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
			select WhsDocumentSpecificity_id as \"WhsDocumentSpecificity_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from {$procedure}(
				WhsDocumentSpecificity_id := :WhsDocumentSpecificity_id,
				WhsDocumentUc_id := :WhsDocumentUc_id,
				WhsDocumentStatusType_id := :WhsDocumentStatusType_id,
				WhsDocumentSpecificity_setDate := :WhsDocumentSpecificity_setDate,
				WhsDocumentSpecificity_disDate := :WhsDocumentSpecificity_disDate,
				Org_sid := :Org_sid,
				Org_tid := :Org_tid,
				Storage_sid := :Storage_sid,
				Storage_tid := :Storage_tid,
				WhsDocumentSupply_id := :WhsDocumentSupply_id,
				DrugFinance_id := :DrugFinance_id,
				WhsDocumentCostItemType_id := :WhsDocumentCostItemType_id,
				pmUser_id := :pmUser_id);


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
			select WhsDocumentStatusHistory_id as \"WhsDocumentStatusHistory_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from p_WhsDocumentStatusHistory_ins(
				WhsDocumentStatusHistory_setDate := dbo.tzGetDate(),
				WhsDocumentUc_id := :WhsDocumentUc_id,
				WhsDocumentStatusType_id := :WhsDocumentStatusType_id,
				pmUser_id := :pmUser_id);


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
			select WhsDocumentSpecification_id as \"WhsDocumentSpecification_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from {$procedure}(
				WhsDocumentSpecification_id := :WhsDocumentSpecification_id,
				WhsDocumentSpecificity_id := :WhsDocumentSpecificity_id,
				DrugComplexMnn_id := :DrugComplexMnn_id,
				Tradenames_id := :Tradenames_id,
				WhsDocumentSpecification_Method := :WhsDocumentSpecification_Method,
				EvnCourseTreat_id := :EvnCourseTreat_id,
				ReceptOtov_id := :ReceptOtov_id,
				DrugRequestRow_id := :DrugRequestRow_id,
				Okei_id := :Okei_id,
				WhsDocumentSpecification_Count := :WhsDocumentSpecification_Count,
				WhsDocumentSpecification_Cost := :WhsDocumentSpecification_Cost,
				GoodsUnit_id := :GoodsUnit_id,
				Drug_id := :Drug_id,
				pmUser_id := :pmUser_id);


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
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from p_WhsDocumentSpecification_del(
				WhsDocumentSpecification_id := :WhsDocumentSpecification_id);


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
			select
				WDU.WhsDocumentUc_id as \"WhsDocumentUc_id\",
				WDU.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
				to_char(WDU.WhsDocumentUc_Date, 'DD.MM.YYYY') as \"WhsDocumentUc_Date\",

				WDU.WhsDocumentStatusType_id as \"WhsDocumentStatusType_id\",
				WDC.WhsDocumentClass_id as \"WhsDocumentClass_id\",
				WDC.WhsDocumentClass_Code as \"WhsDocumentClass_Code\",
				WDS.WhsDocumentSpecificity_id as \"WhsDocumentSpecificity_id\",
				WDS.Org_sid as \"Org_sid\",
				WDS.Org_tid as \"Org_tid\",
				WDS.Storage_sid as \"Storage_sid\",
				WDS.Storage_tid as \"Storage_tid\",
				WDS.WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
				WDS.DrugFinance_id as \"DrugFinance_id\",
				WDS.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\"
			from
				v_WhsDocumentUc WDU 

				inner join v_WhsDocumentClass WDC  on WDC.WhsDocumentClass_id = WDU.WhsDocumentClass_id

				inner join v_WhsDocumentSpecificity WDS  on WDS.WhsDocumentUc_id = WDU.WhsDocumentUc_id


			where
				WDU.WhsDocumentUc_id = :WhsDocumentUc_id
			limit 1
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
            $select[] = "WDS.Drug_id as \"Drug_id\"";
            $select[] = "D.Drug_Name as \"Drug_Name\"";
            $select[] = "D.Drug_Nomen as \"hintPackagingData\"";  // Хинт: Данные об упаковке – это часть поля Упаковка (drug_nomen) от начала строки до данных о производителе
            $select[] = "RTRIM(COALESCE(DP.DrugPrep_Name, '')) as \"hintTradeName\""; // Хинт: Торговое наименование, лекарственная форма, дозировка, фасовка
            $select[] = "COALESCE(D.Drug_RegNum || ',   ', '') || COALESCE(to_char(D.Drug_begDate, 'DD.MM.YYYY') || ', ', '') || COALESCE(to_char(D.Drug_endDate, 'DD.MM.YYYY') ||', ', '--, ') || COALESCE(to_char(REG.REGCERT_insDT, 'DD.MM.YYYY')||', ', '') || COALESCE(REGISTR.regNameCauntries, '') as \"hintRegistrationData\"";  // Хинт: Данные о регистрации
            $select[] = "
				case
					when NOMENF.FIRMS_ID = MANUFACTURF.FIRMS_ID
					then COALESCE(MANUFACTURF.FULLNAME, '')
					else
						case
							when (NOMENF.FULLNAME IS NULL OR rtrim(NOMENF.FULLNAME)='') AND (MANUFACTURF.FULLNAME IS NULL OR rtrim(MANUFACTURF.FULLNAME)='')
								then COALESCE(REGISTR.regNameCauntries, '')
							when NOMENF.FULLNAME IS NULL OR rtrim(NOMENF.FULLNAME)=''
								then COALESCE(MANUFACTURF.FULLNAME, '')
							else COALESCE(MANUFACTURF.FULLNAME, '') || ' / ' || NOMENF.FULLNAME
						end
				end as \"hintPRUP\"
			"; // Хинт: ПР./УП.
            $select[] = "FNM.NAME as \"hintFirmNames\""; // Хинт: Данные о производителе
            $join[] = "left join rls.v_Drug D on D.Drug_id = WDS.Drug_id";
            $join[] = "left join rls.DrugPrep DP on D.DrugPrepFas_id = dp.DrugPrepFas_id";
            $join[] = "left join rls.PREP P on P.Prep_id = d.DrugPrep_id";
            $join[] = "left join rls.REGCERT REG on REG.REGCERT_ID =P.REGCERTID";
            $join[] = "left join rls.NOMEN NM on NM.Nomen_id = d.Drug_id";
            $join[] = "left join rls.FIRMS NOMENF on NOMENF.FIRMS_ID = NM.FIRMID";
            $join[] = "left join rls.FIRMS MANUFACTURF on MANUFACTURF.FIRMS_ID = P.FIRMID";
            $join[] = "left join rls.FIRMNAMES FNM on FNM.FIRMNAMES_ID = MANUFACTURF.NAMEID";
            $join[] = "LEFT JOIN LATERAL(
				select
					FN.NAME || ' (' || C.NAME || ')' as regNameCauntries
				from
					rls.REGCERT_EXTRAFIRMS RE 
					left join rls.FIRMS F on F.FIRMS_ID = RE.FIRMID
					left join rls.FIRMNAMES FN on FN.FIRMNAMES_ID = F.NAMEID
					left join rls.COUNTRIES C on C.COUNTRIES_ID = F.COUNTID
				where
					RE.CERTID = P.REGCERTID
				limit 1
			) REGISTR on true";
        }

        $select_clause = count($select) > 0 ? ', '.join(', ', $select) : '';;
        $join_clause = count($join) > 0 ? join(' ', $join) : '';

		$query = "
			select
				WDS.WhsDocumentSpecification_id as \"WhsDocumentSpecification_id\",
				WDS.WhsDocumentSpecificity_id as \"WhsDocumentSpecificity_id\",
				WDS.WhsDocumentSpecification_Count as \"WhsDocumentSpecification_Count\",
				WDS.WhsDocumentSpecification_Cost as \"WhsDocumentSpecification_Cost\",
				WDS.WhsDocumentSpecification_Method as \"WhsDocumentSpecification_Method\",
				DN.DrugNomen_id as \"DrugNomen_id\",
				DN.DrugNomen_Code as \"DrugNomen_Code\",
				DN.DrugComplexMnnCode_Code as \"DrugComplexMnnCode_Code\",
				DN.Drug_Ean as \"Drug_Ean\",
				DCM.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				DCM.DrugComplexMnn_RusName as \"DrugComplexMnn_Name\",
				DCMD.DrugComplexMnnDose_Name as \"DrugComplexMnn_Dose\",
				CDF.Name as \"RlsClsdrugforms_RusName\",
				T.Tradenames_id as \"Tradenames_id\",
				T.Name as \"DrugTorg_Name\",
				O.Okei_id as \"Okei_id\",
				O.Okei_Name as \"Okei_Name\",
				GU.GoodsUnit_id as \"GoodsUnit_id\",
				GU.GoodsUnit_Name as \"GoodsUnit_Name\",
				ECT.EvnCourseTreat_id as \"EvnCourseTreat_id\",
				RO.ReceptOtov_id as \"ReceptOtov_id\",
				case
					when ECT.EvnCourseTreat_id is not null then
						PS.Person_SurName||' '||PS.Person_FirName||COALESCE(' '||PS.Person_SecName,'')||', КВС № '||EPS.EvnPS_NumCard

					when RO.ReceptOtov_id is not null then
						PS.Person_SurName||' '||PS.Person_FirName||COALESCE(' '||PS.Person_SecName,'')

						||', рецепт серия '||RO.EvnRecept_Ser||' № '||RO.EvnRecept_Num||' от '||to_char(RO.EvnRecept_setDate, 'DD.MM.YYYY')

				end as \"WhsDocumentSpecification_Note\",
				null as \"OtpuskCount\",
				null as \"OtpuskSum\",
				null as \"Budget\"
				{$select_clause}
			from
				v_WhsDocumentSpecification WDS 

				left join rls.v_DrugComplexMnn DCM  on DCM.DrugComplexMnn_id = WDS.DrugComplexMnn_id

				left join rls.v_DrugComplexMnnDose DCMD  on DCMD.DrugComplexMnnDose_id = DCM.DrugComplexMnnDose_id

				left join rls.v_Clsdrugforms CDF  on CDF.Clsdrugforms_id = DCM.Clsdrugforms_id

				left join rls.v_Tradenames T  on T.Tradenames_id = WDS.Tradenames_id

				left join v_Okei O  on O.Okei_id = WDS.Okei_id

				left join v_GoodsUnit GU  on GU.GoodsUnit_id = COALESCE(WDS.GoodsUnit_id, 
                :DefaultGoodsUnit_id
                )


				LEFT JOIN LATERAL(

					select 
						DN.DrugNomen_id,
						DN.DrugNomen_Code,
						D.Drug_Ean,
						DCMC.DrugComplexMnnCode_Code
					from
						rls.v_DrugNomen DN 

						inner join rls.v_Drug D  on D.Drug_id = DN.Drug_id

						inner join rls.v_DrugComplexMnnCode DCMC  on DCMC.DrugComplexMnnCode_id = DN.DrugComplexMnnCode_id

					where
						D.DrugComplexMnn_id = WDS.DrugComplexMnn_id
						and (WDS.Tradenames_id is null or D.DrugTorg_id = WDS.Tradenames_id)
                    limit 1
				) DN ON true
				left join v_EvnCourseTreat ECT  on ECT.EvnCourseTreat_id = WDS.EvnCourseTreat_id

				left join v_EvnPS EPS  on EPS.EvnPS_id = ECT.EvnCourseTreat_rid

				left join v_ReceptOtov_all RO  on RO.ReceptOtov_id = WDS.ReceptOtov_id

				left join v_PersonState PS  on PS.Person_id = coalesce(EPS.Person_id,RO.ReceptOtov_id)
                
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
				null as \"WhsDocumentSpecification_id\",
				null as \"WhsDocumentSpecificity_id\",
				DOR.DrugOstatRegistry_Kolvo as \"WhsDocumentSpecification_Count\",
				DOR.DrugOstatRegistry_Cost as \"WhsDocumentSpecification_Cost\",
				null as \"WhsDocumentSpecification_Method\",
				DCM.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				DCM.DrugComplexMnn_RusName as \"DrugComplexMnn_Name\",
				DCMD.DrugComplexMnnDose_Name as \"DrugComplexMnn_Dose\",
				CDF.Name as \"RlsClsdrugforms_RusName\",
				D.DrugTorg_id as \"Tradenames_id\",
				D.DrugTorg_Name as \"DrugTorg_Name\",
				D.Drug_Ean as \"Drug_Ean\",
				DN.DrugNomen_id as \"DrugNomen_id\",
				DN.DrugNomen_Code as \"DrugNomen_Code\",
				O.Okei_id as \"Okei_id\",
				O.Okei_Name as \"Okei_Name\",
				null as \"EvnCourseTreat_id\",
				null as \"ReceptOtov_id\",
				null as \"WhsDocumentSpecification_Note\",
				null as \"OtpuskCount\",
				null as \"OtpuskSum\",
				null as \"Budget\"
			from
				v_WhsDocumentSupplySpec WDSS 

				inner join v_WhsDocumentSupply WDS  on WDS.WhsDocumentSupply_id = WDSS.WhsDocumentSupply_id

				left join v_DrugOstatRegistry DOR  on DOR.Drug_id = WDSS.Drug_id and DOR.Org_id = WDS.Org_sid

				left join rls.v_Drug D  on D.Drug_id = DOR.Drug_id

				left join rls.v_DrugComplexMnn DCM  on DCM.DrugComplexMnn_id = D.DrugComplexMnn_id

				left join rls.v_DrugComplexMnnDose DCMD  on DCMD.DrugComplexMnnDose_id = DCM.DrugComplexMnnDose_id

				left join rls.v_CLSDRUGFORMS CDF  on CDF.CLSDRUGFORMS_ID = DCM.CLSDRUGFORMS_ID

				left join rls.v_DrugNomen DN  on DN.Drug_id = D.Drug_id

				left join v_Okei O  on O.Okei_id = DOR.Okei_id

			where
				WDSS.WhsDocumentSupply_id = :WhsDocumentSupply_id
				and 
                DOR.SubAccountType_id = 1
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
				:Storage_id as \"Storage_id\"
			union all
			select -- дочерние склады
				s.Storage_id as \"Storage_id\"
			from
				v_Storage s 

				LEFT JOIN LATERAL (

					select
						i_ms.MedService_id
					from
						v_StorageStructLevel i_ssl 

						left join v_MedService i_ms  on i_ms.MedService_id = i_ssl.MedService_id			

						left join v_MedServiceType i_mst  on i_mst.MedServiceType_id = i_ms.MedServiceType_id

					where
						i_ssl.Storage_id = s.Storage_id and
						i_mst.MedServiceType_SysNick = 'merch'
                    limit 1
				) ms ON true
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
            $join[] = "left join v_EvnPS eps  on eps.EvnPS_id = ect.EvnCourseTreat_rid";

            $join[] = "left join v_PayType pt  on pt.PayType_id = eps.PayType_id";

            $join[] = "left join v_DrugFinance df  on df.DrugFinance_Name = pt.PayType_Name or df.DrugFinance_SysNick = pt.PayType_SysNick";

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

        $params['Upak_GoodsUnit_id'] = $this->getFirstResultFromQuery("select GoodsUnit_id  as \"GoodsUnit_id\" from v_GoodsUnit where GoodsUnit_Name = 'упаковка' limit 1");


        $query_cte = "
            WITH cte AS (
			select d.Drug_id,
             dcm.DrugComplexMnn_id,
             (COALESCE(dcmn.DrugComplexMnnName_Name, '') || COALESCE(' (' || d.Drug_Name || ')', '')) as Drug_Name,
             --(COALESCE(ectd.EvnCourseTreatDrug_KolvoEd*koef.kf_ke, ectd.EvnCourseTreatDrug_Kolvo*koef.kf_k)*(ect.EvnCourseTreat_PrescrCount-ectd.EvnCourseTreatDrug_FactCount)) as Count,
             --(COALESCE(COALESCE(koef.kf_ke, koef.kf_k), 0)) as koef_value,
             ect_id.EvnCourseTreat_id,
             ect.Person_id,
             (case
                when COALESCE(gpc.GoodsPackCount_sCount, 0) > 0 then gpc.GoodsPackCount_sCount
                else gpc.GoodsPackCount_Count
              end) as GoodsPackCount_Count,
             (case
                when COALESCE(gpc.GoodsPackCount_sCount, 0) > 0 then ectd.GoodsUnit_sid
                else ectd.GoodsUnit_id
              end) as GoodsUnit_id,
             ((case
                 when COALESCE(gpc.GoodsPackCount_sCount, 0) > 0 then ectd.EvnCourseTreatDrug_KolvoEd
                 else ectd.EvnCourseTreatDrug_Kolvo
               end) *(ect.EvnCourseTreat_PrescrCount - ectd.EvnCourseTreatDrug_FactCount)) as GoodsUnit_Kolvo,
             min_gpc.GoodsUnit_id as MinGoodsUnit_id,
             ((case
                 when COALESCE(gpc.GoodsPackCount_sCount, 0) > 0 then ectd.EvnCourseTreatDrug_KolvoEd *(min_gpc.GoodsPackCount_Count / gpc.GoodsPackCount_sCount)
                 when COALESCE(gpc.GoodsPackCount_Count, 0) > 0 then ectd.EvnCourseTreatDrug_Kolvo *(min_gpc.GoodsPackCount_Count / gpc.GoodsPackCount_Count)
                 else null
               end) *(ect.EvnCourseTreat_PrescrCount - ectd.EvnCourseTreatDrug_FactCount)) as MinGoodsUnit_Kolvo,
             min_gpc.GoodsPackCount_Count as MinGoodsPackCount_Count
      from v_EvnCourseTreatDrug ectd
           inner join v_EvnCourseTreat ect on ect.EvnCourseTreat_id = ectd.EvnCourseTreat_id
           inner join v_EvnSection ES on ES.EvnSection_id = ECT.EvnCourseTreat_pid
           left join rls.v_Drug d on d.Drug_id = ectd.Drug_id
           left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = COALESCE(d.DrugComplexMnn_id, ectd.DrugComplexMnn_id)
           left join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
           left join rls.v_ACTMATTERS am on am.ACTMATTERS_ID = dcmn.ACTMATTERS_id
           left join v_LpuSection LS on LS.LpuSection_id = ES.LpuSection_id
           left join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
           left join v_LpuUnitType LUT on LUT.LpuUnitType_id = LU.LpuUnitType_id
           left join v_LpuBuilding LB on LB.LpuBuilding_id = LU.LpuBuilding_id
           left join v_Lpu L on L.Lpu_id = LB.Lpu_id
           LEFT JOIN LATERAL
           (
             select (case
                       when am.NARCOGROUPID > 0 then ect.EvnCourseTreat_id -- для наркотиков производим дополнительную группировку по курсу
                       else null
                     end) as EvnCourseTreat_id
           ) ect_id ON true
           LEFT JOIN LATERAL
           (
             select COALESCE(i_yn.YesNo_Code, 0) as YesNo_Code
             from v_EvnPrescrTreat i_ept
                  left join v_EvnPrescr i_ep on i_ep.EvnPrescr_id = i_ept.EvnPrescrTreat_id
                  left join v_YesNo i_yn on i_yn.YesNo_id = i_ep.EvnPrescr_IsExec
             where i_ept.EvnCourse_id = ect.EvnCourseTreat_id
             limit 1
           ) is_exec ON true
           LEFT JOIN LATERAL
           (
             select i_gp.GoodsPackCount_Count,
                    (case
                       when i_gp.TRADENAMES_ID = d.DrugTorg_id then 1
                       else 0
                     end) as sp -- поле для сортировки
             from GoodsPackCount i_gp
             where i_gp.DrugComplexMnn_id = ectd.DrugComplexMnn_id and
                   i_gp.GoodsUnit_id = ectd.GoodsUnit_sid
             order by sp desc,
                      i_gp.GoodsPackCount_id
             limit 1
           ) gpc_ke ON true -- информация для конвертации количества в произвольных ед. изм. в количество в упаковках
           LEFT JOIN LATERAL
           (
             select i_gp.GoodsPackCount_Count,
                    (case
                       when i_gp.TRADENAMES_ID = d.DrugTorg_id then 1
                       else 0
                     end) as sp -- поле для сортировки
             from GoodsPackCount i_gp
             where i_gp.DrugComplexMnn_id = ectd.DrugComplexMnn_id and
                   i_gp.GoodsUnit_id = ectd.GoodsUnit_id
             order by sp desc,
                      i_gp.GoodsPackCount_id
             limit 1
           ) gpc_k ON true -- информация для конвертации количества в произвольных ед. изм. в количество в упаковках                
           LEFT JOIN LATERAL
           (
             select (case
                       when ectd.GoodsUnit_sid = 
                       :DefaultGoodsUnit_id 
                       then 1
                       else gpc_ke.GoodsPackCount_Count
                     end) GoodsPackCount_sCount,
                    (case
                       when ectd.GoodsUnit_id = 
                       :DefaultGoodsUnit_id 
                       then 1
                       else gpc_k.GoodsPackCount_Count
                     end) GoodsPackCount_Count
           ) gpc ON true -- если количество задано в упаковках то количество в упаковке равно 1, иначе значение из таблиц
           LEFT JOIN LATERAL 
           (
             select i_gp.GoodsUnit_id,
                    i_gp.GoodsPackCount_Count
             from GoodsPackCount i_gp
             where i_gp.DrugComplexMnn_id = ectd.DrugComplexMnn_id
             order by i_gp.GoodsPackCount_Count desc,
                      i_gp.GoodsPackCount_id
             limit 1
           ) min_gpc ON true -- данные для персчета в самую мелкую ед. измерения которая есть в справочнике для данного медикамента 
          {$join_clause}
      where ect.LpuSection_id in (
                                   select LpuSection_id
                                   from v_StorageStructLevel t
                                   where Storage_id in (" . join(", ", $storage_list) . ")
            ) and
            ES.EvnSection_disDate is null and
            is_exec.YesNo_Code = 0 and
            COALESCE(ectd.EvnCourseTreatDrug_KolvoEd, ectd.EvnCourseTreatDrug_Kolvo) is not null and
            ect.EvnCourseTreat_PrescrCount - ectd.EvnCourseTreatDrug_FactCount > 0 and
      :MedService_id 
      in (
                          select MS.MedService_id
                          from v_MedService MS
                          where COALESCE(MS.Lpu_id, L.Lpu_id) = L.Lpu_id and
                                COALESCE(MS.LpuBuilding_id, LB.LpuBuilding_id) = LB.LpuBuilding_id and
                                COALESCE(MS.LpuUnitType_id, LUT.LpuUnitType_id) = LUT.LpuUnitType_id and
                                COALESCE(MS.LpuUnit_id, LU.LpuUnit_id) = LU.LpuUnit_id and
                                COALESCE(MS.LpuSection_id, LS.LpuSection_id) = LS.LpuSection_id
            ) 
            {$where_clause}
            )
		";
//        $result = $this->db->query(getDebugSQL($query, $params)); //без предварительной сборки запроса работать не хочет

		$query = $query_cte . "\n\n
		    select
				rl.Drug_id as \"Drug_id\",
				rl.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				rl.EvnCourseTreat_id as \"EvnCourseTreat_id\",
				rl.Person_id as \"Person_id\",								
				rl.GoodsUnit_id as \"GoodsUnit_id\",
				max(rl.GoodsPackCount_Count) as \"GoodsPackCount_Count\",				
				sum(rl.GoodsUnit_Kolvo) as \"GoodsUnit_Kolvo\",				
				max(rl.MinGoodsUnit_id) as \"MinGoodsUnit_id\",
				sum(rl.MinGoodsUnit_Kolvo) as \"MinGoodsUnit_Kolvo\",
				max(rl.MinGoodsPackCount_Count) as \"MinGoodsPackCount_Count\"
			from
				cte rl 

			where
			    COALESCE(rl.GoodsPackCount_Count, 0) > 0 and

			    COALESCE(rl.MinGoodsPackCount_Count, 0) > 0

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
		$prescr_list = $this->queryResult($query, $params);
		if (!is_array($prescr_list)) {
			return $this->createError('','Ошибка при получении медикаментов из назначений');
		}

        //составление списка позиций для которых не был выполнен расчет коэфицента из за отсутсвия данных
        $query = $query_cte."\n\n
			select distinct
				dcm.DrugComplexMnn_RusName as \"DrugComplexMnn_Name\",
				d.DrugTorg_Name as \"DrugTorg_Name\",
				COALESCE(gu.GoodsUnit_Name, '') as \"GoodsUnit_Name\"

			from
				cte rl 

				left join v_GoodsUnit gu  on gu.GoodsUnit_id = rl.GoodsUnit_id

				left join rls.v_Drug d  on d.Drug_id = rl.Drug_id

				left join rls.v_DrugComplexMnn dcm  on dcm.DrugComplexMnn_id = rl.DrugComplexMnn_id

			where
			    COALESCE(rl.GoodsPackCount_Count, 0) = 0 or 

			    COALESCE(rl.MinGoodsPackCount_Count, 0) = 0

			order by
			    d.DrugTorg_Name, dcm.DrugComplexMnn_RusName, COALESCE(gu.GoodsUnit_Name, '')

		";
        $gu_data = $this->queryResult($query, $params);

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
            $join[] = "left join v_DrugShipment ds  on ds.DrugShipment_id = dor.DrugShipment_id";

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
				d.Drug_id as \"Drug_id\",
				d.DrugComplexMnn_id as \"DrugComplexMnn_id\",								
				min_gpc.GoodsUnit_id as \"MinGoodsUnit_id\",
				sum(case
					when COALESCE(gpc.GoodsPackCount_Count, 0) > 0 then dor.DrugOstatRegistry_Kolvo*(min_gpc.GoodsPackCount_Count/gpc.GoodsPackCount_Count)

					else null
				end) as \"MinGoodsUnit_Kolvo\",
				cost.cost as \"MinGoodsUnit_Cost\",
				max(min_gpc.GoodsPackCount_Count) as \"MinGoodsPackCount_Count\"
			from
				v_DrugOstatRegistry dor 

				left join rls.v_Drug d  on d.Drug_id = dor.Drug_id

				LEFT JOIN LATERAL (

                    select
                        i_gp.GoodsPackCount_Count,
                        (
                            case
                                when i_gp.TRADENAMES_ID = d.DrugTorg_id then 1
                                else 0
                            end
                        ) as sp -- поле для сортировки
                    from
                        GoodsPackCount i_gp 

                    where
                        i_gp.DrugComplexMnn_id = d.DrugComplexMnn_id and
                        i_gp.GoodsUnit_id = dor.GoodsUnit_id
                    order by
                        sp desc, i_gp.GoodsPackCount_id
                    limit 1
                ) gpc  ON true
                LEFT JOIN LATERAL (

                	select (case
                		when COALESCE(dor.GoodsUnit_id, 
                        :DefaultGoodsUnit_id
                        ) =
                        :DefaultGoodsUnit_id 
                        then 1

                		else gpc.GoodsPackCount_Count
                	end) as GoodsPackCount_Count
                ) dor_gpc ON true
 				LEFT JOIN LATERAL (

                	select 
						i_gp.GoodsUnit_id,
						i_gp.GoodsPackCount_Count
                    from
                        GoodsPackCount i_gp 

                    where
                        i_gp.DrugComplexMnn_id = d.DrugComplexMnn_id
                    order by
                        i_gp.GoodsPackCount_Count desc, i_gp.GoodsPackCount_id
                    limit 1
                ) min_gpc ON true 
                -- данные для персчета в самую мелкую ед. измерения которая есть в справочнике для данного медикамента
				LEFT JOIN LATERAL (

					select (case
						when COALESCE(gpc.GoodsPackCount_Count, 0) > 0 then dor.DrugOstatRegistry_Cost*(min_gpc.GoodsPackCount_Count/gpc.GoodsPackCount_Count)

						else null
					end) as cost
				) cost ON true
				{$join_clause}
			where
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
				1, --Drug_id,
				2, --DrugComplexMnn_id,
				3  --MinGoodsUnit_id
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
		$query = $query_cte."\n\n
			select
				rl.Drug_id as \"Drug_id\",
				rl.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				rl.EvnCourseTreat_id as \"EvnCourseTreat_id\",
				rl.Person_id as \"Person_id\",								
				rl.GoodsUnit_id as \"GoodsUnit_id\",
				sum(rl.GoodsUnit_Kolvo) as \"GoodsUnit_Kolvo\"
			from
				cte rl 

			where
			    COALESCE(rl.GoodsPackCount_Count, 0) = 0 or

			    COALESCE(rl.MinGoodsPackCount_Count, 0) = 0

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
        $add_list = $this->queryResult($query, $params);
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

		$tmpTableName = "drug_request_tmp" . time();

		// Создаем временную таблицу
        $this->beginTransaction();
        $query = "
		    CREATE TEMP TABLE $tmpTableName (
		        Drug_id bigint,
				DrugComplexMnn_id bigint,
				Cost float,
				Count float,
				EvnCourseTreat_id bigint,
				Person_id bigint,
				GoodsUnit_id bigint
		    )
		    ON COMMIT DROP;
			insert into {$tmpTableName} (Drug_id, DrugComplexMnn_id, Cost, Count, EvnCourseTreat_id, Person_id, GoodsUnit_id)
			values {$insert_values_str};
		";
		$this->db->query($query);

		$query = "
			select
				null as \"WhsDocumentSpecification_id\",
				null as \"WhsDocumentSpecificity_id\",
				rl.Count as \"WhsDocumentSpecification_Count\",
				rl.Cost as \"WhsDocumentSpecification_Cost\",
				null as \"WhsDocumentSpecification_Method\",
				DCM.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				DCM.DrugComplexMnn_RusName as \"DrugComplexMnn_Name\",
				COALESCE(DCMD.DrugComplexMnnDose_Name,dcm.DrugComplexMnn_Dose) as \"DrugComplexMnn_Dose\",
				CDF.Name as \"RlsClsdrugforms_RusName\",
				D.DrugTorg_id as \"Tradenames_id\",
				D.DrugTorg_Name as \"DrugTorg_Name\",
				DN.Drug_Ean as \"Drug_Ean\",
				DN.DrugNomen_id as \"DrugNomen_id\",
				DN.DrugNomen_Code as \"DrugNomen_Code\",
				O.Okei_id as \"Okei_id\",
				O.Okei_Name as \"Okei_Name\",
				ect.EvnCourseTreat_id as \"EvnCourseTreat_id\",
				null as \"ReceptOtov_id\",
				PS.Person_SurName||' '||PS.Person_FirName||COALESCE(' '||PS.Person_SecName,'')||', КВС № '||EPS.EvnPS_NumCard as \"WhsDocumentSpecification_Note\",

				null as \"OtpuskCount\",
				null as \"OtpuskSum\",
				null as \"Budget\"
			from
				$tmpTableName rl 

				left join rls.v_Drug d  on d.Drug_id = rl.Drug_id::bigint

				left join rls.v_DrugComplexMnn dcm  on dcm.DrugComplexMnn_id = rl.DrugComplexMnn_id::bigint

				left join rls.v_DrugComplexMnnDose dcmd  on dcmd.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id

				left join rls.v_CLSDRUGFORMS cdf  on cdf.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID

				left join v_Okei o  on o.Okei_id = 120

				left join v_EvnCourseTreat ect  on ect.EvnCourseTreat_id = rl.EvnCourseTreat_id::bigint

				left join v_EvnPS eps  on eps.EvnPS_id = ect.EvnCourseTreat_rid

				left join v_PersonState ps  on ps.Person_id = rl.Person_id::bigint

				LEFT JOIN LATERAL(

					select
						DN.DrugNomen_id,
						DN.DrugNomen_Code,
						Drug.Drug_Ean
					from
						rls.v_DrugNomen DN 

						inner join rls.v_Drug Drug  on Drug.Drug_id = DN.Drug_id

					where
						Drug.DrugComplexMnn_id = dcm.DrugComplexMnn_id
						and (d.DrugTorg_id is null or Drug.DrugTorg_id = d.DrugTorg_id)
                    limit 1
				) DN ON true;
		";
		$resp = $this->queryResult($query);
        $this->commitTransaction();

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

        $insert_sql = "WITH cte(DrugOstatRegistry_id, DrugOstatRegistry_Kolvo) AS ( values (null::bigint, null::numeric(18, 2))";

        if (is_array($data['DrugOstatRegistryJSON'])) {
            foreach($data['DrugOstatRegistryJSON'] as $dor_data) {
                $insert_sql .= ", \n\n ({$dor_data->DrugOstatRegistry_id}::bigint, {$dor_data->DrugOstatRegistry_Kolvo}::numeric(18, 2))
                ";
            }
        }
        $insert_sql .= ")\n";

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
                $select[] = "dor.Drug_id as \"Drug_id\"";
                $select[] = "D.Drug_Name as \"Drug_Name\"";
                $select[] = "D.Drug_Nomen as \"hintPackagingData\"";  // Хинт: Данные об упаковке – это часть поля Упаковка (drug_nomen) от начала строки до данных о производителе
                $select[] = "RTRIM(COALESCE(DP.DrugPrep_Name, '')) as \"hintTradeName\""; // Хинт: Торговое наименование, лекарственная форма, дозировка, фасовка
                $select[] = "COALESCE(D.Drug_RegNum || ',   ', '') || COALESCE(to_char(D.Drug_begDate, 'DD.MM.YYYY') || ', ', '') || COALESCE(to_char(D.Drug_endDate, 'DD.MM.YYYY') ||', ', '--, ') || COALESCE(to_char(REG.REGCERT_insDT, 'DD.MM.YYYY')||', ', '') || COALESCE(REGISTR.regNameCauntries, '') as \"hintRegistrationData\"";  // Хинт: Данные о регистрации
                $select[] = "
					case
						when NOMENF.FIRMS_ID = MANUFACTURF.FIRMS_ID
						then COALESCE(MANUFACTURF.FULLNAME, '')
						else
							case
								when (NOMENF.FULLNAME IS NULL OR rtrim(NOMENF.FULLNAME)='') AND (MANUFACTURF.FULLNAME IS NULL OR rtrim(MANUFACTURF.FULLNAME)='')
									then COALESCE(REGISTR.regNameCauntries, '')
								when NOMENF.FULLNAME IS NULL OR rtrim(NOMENF.FULLNAME)=''
									then COALESCE(MANUFACTURF.FULLNAME, '')
								else COALESCE(MANUFACTURF.FULLNAME, '') || ' / ' || NOMENF.FULLNAME
							end
					end as \"hintPRUP\"
				"; // Хинт: ПР./УП.
                $select[] = "FNM.NAME as \"hintFirmNames\""; // Хинт: Данные о производителе
                $join[] = "left join rls.v_Drug D on D.Drug_id = dor.Drug_id";
                $join[] = "left join rls.DrugPrep DP on D.DrugPrepFas_id = dp.DrugPrepFas_id";
                $join[] = "left join rls.PREP P on P.Prep_id = d.DrugPrep_id";
                $join[] = "left join rls.REGCERT REG on REG.REGCERT_ID =P.REGCERTID";
                $join[] = "left join rls.NOMEN NM on NM.Nomen_id = d.Drug_id";
                $join[] = "left join rls.FIRMS NOMENF on NOMENF.FIRMS_ID = NM.FIRMID";
                $join[] = "left join rls.FIRMS MANUFACTURF on MANUFACTURF.FIRMS_ID = P.FIRMID";
                $join[] = "left join rls.FIRMNAMES FNM on FNM.FIRMNAMES_ID = MANUFACTURF.NAMEID";
                $join[] = "LEFT JOIN LATERAL(
					select
						FN.NAME || ' (' || C.NAME || ')' as regNameCauntries
					from
						rls.REGCERT_EXTRAFIRMS RE
						left join rls.FIRMS F on F.FIRMS_ID = RE.FIRMID
						left join rls.FIRMNAMES FN on FN.FIRMNAMES_ID = F.NAMEID
						left join rls.COUNTRIES C on C.COUNTRIES_ID = F.COUNTID
					where
						RE.CERTID = P.REGCERTID
					limit 1
				) REGISTR on true";
            } else {
                $select[] = "dor.Drug_id";
            }
        }

        //$insert_sql = "";
        $select_clause = count($select) > 0 ? ', '.join(', ', $select) : '';;
        $select_dor_clause = count($select_dor) > 0 ? ', '.join(', ', $select_dor) : '';;
        $join_clause = count($join) > 0 ? join(' ', $join) : '';
        $group_dor_clause = count($group_dor) > 0 ? ', '.join(', ', $group_dor) : '';


        $query = $insert_sql."
			select
				null as \"WhsDocumentSpecification_id\",
				null as \"WhsDocumentSpecificity_id\",
				DOR.DrugOstatRegistry_Kolvo as \"WhsDocumentSpecification_Count\",
				DOR.DrugOstatRegistry_Cost as \"WhsDocumentSpecification_Cost\",
				null as \"WhsDocumentSpecification_Method\",
				DCM.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				DCM.DrugComplexMnn_RusName as \"DrugComplexMnn_Name\",
				DCMD.DrugComplexMnnDose_Name as \"DrugComplexMnn_Dose\",
				CDF.Name as \"RlsClsdrugforms_RusName\",
				DOR.DrugTorg_id as \"Tradenames_id\",
				DOR.DrugTorg_Name as \"DrugTorg_Name\",
				DN.Drug_Ean as \"Drug_Ean\",
				DN.DrugNomen_id as \"DrugNomen_id\",
				DN.DrugNomen_Code as \"DrugNomen_Code\",
				DN.DrugComplexMnnCode_Code as \"DrugComplexMnnCode_Code\",
				GU.GoodsUnit_id as \"GoodsUnit_id\",
				GU.GoodsUnit_Name as \"GoodsUnit_Name\",
				null as \"EvnCourseTreat_id\",
				null as \"ReceptOtov_id\",
				null as \"WhsDocumentSpecification_Note\",
				null as \"OtpuskCount\",
				null as \"OtpuskSum\",
				null as \"Budget\"
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
                    cte OK
                    left join v_DrugOstatRegistry DOR  on DOR.DrugOstatRegistry_id = OK.DrugOstatRegistry_id

                    inner join rls.v_Drug D  on D.Drug_id = DOR.Drug_id

                group by
                    DOR.DrugOstatRegistry_Cost,
                    DOR.GoodsUnit_id,
                    D.DrugTorg_id,
                    D.DrugTorg_Name,
                    D.DrugComplexMnn_id
                    {$group_dor_clause}
            ) dor
            inner join rls.v_DrugComplexMnn DCM  on DCM.DrugComplexMnn_id = DOR.DrugComplexMnn_id

            left join rls.v_DrugComplexMnnDose DCMD  on DCMD.DrugComplexMnnDose_id = DCM.DrugComplexMnnDose_id

            left join rls.v_CLSDRUGFORMS CDF  on CDF.CLSDRUGFORMS_ID = DCM.CLSDRUGFORMS_ID

            left join v_GoodsUnit GU  on GU.GoodsUnit_id = COALESCE(DOR.GoodsUnit_id, 
            :DefaultGoodsUnit_id
            )


            LEFT JOIN LATERAL(

                select 
                    DN.DrugNomen_id,
                    DN.DrugNomen_Code,
                    Drug.Drug_Ean,
                    DCMC.DrugComplexMnnCode_Code
                from
                    rls.v_DrugNomen DN 

                    inner join rls.v_Drug Drug  on Drug.Drug_id = DN.Drug_id

                    left join rls.v_DrugComplexMnnCode DCMC  on DCMC.DrugComplexMnnCode_id = DN.DrugComplexMnnCode_id

                where
                    Drug.DrugComplexMnn_id = DCM.DrugComplexMnn_id
                    and (DOR.DrugTorg_id is null or Drug.DrugTorg_id = DOR.DrugTorg_id)
                limit 1
            ) DN ON true
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
            SELECT
                name as \"name\"
            FROM (
            SELECT
                   unnest(proargnames) as name
            FROM pg_proc p
                 LEFT OUTER JOIN pg_description ds ON ds.objoid = p.oid
                 INNER JOIN pg_namespace n ON p.pronamespace = n.oid
            WHERE p.proname = :name AND
                  n.nspname = :schema
            ) t
            WHERE t.name not in ('pmuser_id', 'error_code', 'error_message', 'isreloadcount')
		";

        $queryParams = array(
            'name' => strtolower($sp),
            'schema' => strtolower($schema)
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
     * Получение идентификатора произвольного обьекта по коду
     */
    function getObjectIdByCode($object_name, $code) {
        $query = "
			select 
				{$object_name}_id
			from
				v_{$object_name} 

			where
				cast({$object_name}_Code as varchar) = cast(:code as varchar)
	        limit 1
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
					wdu.WhsDocumentUc_id as \"WhsDocumentUc_id\",
					wds.WhsDocumentSpecificity_id as \"WhsDocumentSpecificity_id\",
					wds.Org_sid as \"Org_sid\",
					wds.Storage_sid as \"Storage_sid\",
					wds.Org_tid as \"Org_tid\",
					wds.Storage_tid as \"Storage_tid\",
					s_t.Storage_Name as \"Storage_tName\",
					c_t.Contragent_id as \"Contragent_tid\",
					wds.WhsDocumentStatusType_id as \"WhsDocumentStatusType_id\",
					wdst.WhsDocumentStatusType_Code as \"WhsDocumentStatusType_Code\",
					wdc.WhsDocumentClass_Code as \"WhsDocumentClass_Code\",
					wds.DrugFinance_id as \"DrugFinance_id\",
					wds.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\"
				from
					v_WhsDocumentUc wdu 

					left join v_WhsDocumentSpecificity wds  on wds.WhsDocumentUc_id = wdu.WhsDocumentUc_id

					left join v_WhsDocumentClass wdc on wdc.WhsDocumentClass_id = wdu.WhsDocumentClass_id
					left join v_WhsDocumentStatusType wdst on wdst.WhsDocumentStatusType_id = wdu.WhsDocumentStatusType_id
					left join v_Storage s_t  on s_t.Storage_id = wds.Storage_tid

					LEFT JOIN LATERAL (

						select 
							i_c.Contragent_id
						from
							v_Contragent i_c 

							inner join v_ContragentOrg i_co  on i_co.Contragent_id = i_c.Contragent_id

						where
							i_c.Org_id = wds.Org_tid
                        and
                            i_c.ContragentType_id = '5'
                        and 
							i_co.Org_id = :Org_id
						order by
							i_co.ContragentOrg_id desc
                        limit 1
					) c_t ON true
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
						count(ssl.StorageStructLevel_id) as \"cnt\"
					from
						v_StorageStructLevel ssl 

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
					wds.DrugComplexMnn_id as \"DrugComplexMnn_id\",
					COALESCE(wds.TRADENAMES_ID, 0) as \"Tradenames_id\",

					COALESCE(wds.GoodsUnit_id, 
                    :DefaultGoodsUnit_id
                    ) as \"GoodsUnit_id\",

					gpc.GoodsPackCount_Count as \"GoodsPackCount_Count\",
					wds.WhsDocumentSpecification_Count as \"Kolvo\",
					wds.WhsDocumentSpecification_Cost as \"Cost\",
					min_gpc.GoodsUnit_id as \"MinGoodsUnit_id\",
					min_gpc.GoodsPackCount_Count as \"MinGoodsPackCount_Count\",                
					(case
						when COALESCE(gpc.GoodsPackCount_Count, 0) > 0 then wds.WhsDocumentSpecification_Count*(min_gpc.GoodsPackCount_Count/gpc.GoodsPackCount_Count)

						else null
					end) as \"MinGoodsUnit_Kolvo\",
					(case
						when COALESCE(gpc.GoodsPackCount_Count, 0) > 0 then wds.WhsDocumentSpecification_Cost*(min_gpc.GoodsPackCount_Count/gpc.GoodsPackCount_Count)

						else null
					end) as \"MinGoodsUnit_Cost\"
				from
					v_WhsDocumentSpecification wds 

					LEFT JOIN LATERAL (

						select 
							i_gp.GoodsPackCount_Count,
							(
								case
									when i_gp.TRADENAMES_ID = wds.TRADENAMES_ID then 1
									else 0
								end
							) as sp -- поле для сортировки
						from
							GoodsPackCount i_gp 

						where
							i_gp.DrugComplexMnn_id = wds.DrugComplexMnn_id and
							i_gp.GoodsUnit_id = wds.GoodsUnit_id
						order by
							sp desc, i_gp.GoodsPackCount_id
                        limit 1
					) tbl_gpc ON true
					LEFT JOIN LATERAL (

						select (case
							when wds.GoodsUnit_id is null or wds.GoodsUnit_id = 
                            :DefaultGoodsUnit_id 
                            then 1
							else tbl_gpc.GoodsPackCount_Count
						end) as GoodsPackCount_Count
					) gpc ON true
					LEFT JOIN LATERAL (

						select 
							i_gp.GoodsUnit_id,
							i_gp.GoodsPackCount_Count
						from
							GoodsPackCount i_gp 

						where
							i_gp.DrugComplexMnn_id = wds.DrugComplexMnn_id
						order by
							i_gp.GoodsPackCount_Count desc, i_gp.GoodsPackCount_id
                        limit 1
					) min_gpc ON true
                    -- данные для персчета в самую мелкую ед. измерения которая есть в справочнике для данного медикамента
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
                    dor.Drug_id as \"Drug_id\",
                    d.DrugComplexMnn_id as \"DrugComplexMnn_id\",
                    COALESCE(d.DrugTorg_id, 0) as \"Tradenames_id\",

                    dor.DrugOstatRegistry_Kolvo as \"bKolvo\",
                    COALESCE(dor.GoodsUnit_id,
                    :DefaultGoodsUnit_id
                    ) as \"GoodsUnit_bid\", -- ед. учета

                    b_gpc.GoodsPackCount_Count as \"GoodsPackCount_bCount\",
                    COALESCE(dus.GoodsUnit_id,
                    :DefaultGoodsUnit_id
                    ) as \"GoodsUnit_id\", -- ед. списания

					gpc.GoodsPackCount_Count as \"GoodsPackCount_Count\",
                    dsl.DocumentUcStr_id as \"DocumentUcStr_id\",
                    dus.DocumentUcStr_Price as \"Price\",
                    dus.DocumentUcStr_PriceR as \"PriceR\",
                    min_gpc.GoodsUnit_id as \"MinGoodsUnit_id\",
					min_gpc.GoodsPackCount_Count as \"MinGoodsPackCount_Count\",                
					(case
						when COALESCE(gpc.GoodsPackCount_Count, 0) > 0 then dor.DrugOstatRegistry_Kolvo*(min_gpc.GoodsPackCount_Count/b_gpc.GoodsPackCount_Count)

						else null
					end) as \"MinGoodsUnit_Kolvo\"
                from
                    v_DrugOstatRegistry dor 

                    left join v_SubAccountType sat on sat.SubAccountType_id = dor.SubAccountType_id
                    left join v_DrugShipmentLink dsl  on dsl.DrugShipment_id = dor.DrugShipment_id

                    left join rls.v_Drug d on d.Drug_id = dor.Drug_id
                    left join v_DocumentUcStr dus  on dus.DocumentUcStr_id = dsl.DocumentUcStr_id

					LEFT JOIN LATERAL (

						select
							i_gp.GoodsPackCount_Count,
							(
								case
									when i_gp.TRADENAMES_ID = d.DrugTorg_id then 1
									else 0
								end
							) as sp -- поле для сортировки
						from
							GoodsPackCount i_gp 

						where
							i_gp.DrugComplexMnn_id = d.DrugComplexMnn_id and
							i_gp.GoodsUnit_id = dor.GoodsUnit_id
						order by
							sp desc, i_gp.GoodsPackCount_id
                        limit 1
					) b_tbl_gpc ON true
					LEFT JOIN LATERAL (

						select (case
							when dor.GoodsUnit_id is null or dor.GoodsUnit_id =
                            :DefaultGoodsUnit_id 
                            then 1
							else b_tbl_gpc.GoodsPackCount_Count
						end) as GoodsPackCount_Count
					) b_gpc ON true
                    LEFT JOIN LATERAL (

						select
							i_gp.GoodsPackCount_Count,
							(
								case
									when i_gp.TRADENAMES_ID = d.DrugTorg_id then 1
									else 0
								end
							) as sp -- поле для сортировки
						from
							GoodsPackCount i_gp 

						where
							i_gp.DrugComplexMnn_id = d.DrugComplexMnn_id and
							i_gp.GoodsUnit_id = dus.GoodsUnit_id
						order by
							sp desc, i_gp.GoodsPackCount_id
                        limit 1
					) tbl_gpc ON true
					LEFT JOIN LATERAL (

						select (case
							when dus.GoodsUnit_id is null or dus.GoodsUnit_id = 
                            :DefaultGoodsUnit_id 
                            then 1
							else tbl_gpc.GoodsPackCount_Count
						end) as GoodsPackCount_Count
					) gpc ON true
					LEFT JOIN LATERAL (

						select 
							i_gp.GoodsUnit_id,
							i_gp.GoodsPackCount_Count
						from
							GoodsPackCount i_gp 

						where
							i_gp.DrugComplexMnn_id = d.DrugComplexMnn_id
						order by
							i_gp.GoodsPackCount_Count desc, i_gp.GoodsPackCount_id
                        limit 1
					) min_gpc ON true
                where
                    sat.SubAccountType_Code = 1 and -- 1 - Доступно
                    dor.Org_id =
                    :Org_id 
                    and
                    (
                        :Storage_id is null or
                        dor.Storage_id = :Storage_id
                    ) 
                    and
                    dor.DrugOstatRegistry_Kolvo > 0 and
                    dsl.DocumentUcStr_id is not null and
                    exists (
                        select
                            wds.WhsDocumentSpecification_id
                        from
                            v_WhsDocumentSpecification wds 

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
                        dus.Drug_id as \"Drug_id\",
                        dus.DrugFinance_id as \"DrugFinance_id\",
                        dus.DrugNds_id as \"DrugNds_id\",
                        dus.DrugProducer_id as \"DrugProducer_id\",
                        dus.DocumentUcStr_Price as \"DocumentUcStr_Price\",
                        dus.DocumentUcStr_PriceR as \"DocumentUcStr_PriceR\",
                        dus.DocumentUcStr_Count as \"DocumentUcStr_Count\",
                        dus.DocumentUcStr_Sum as \"DocumentUcStr_Sum\",
                        dus.DocumentUcStr_SumR as \"DocumentUcStr_SumR\",
                        dus.DocumentUcStr_Ser as \"DocumentUcStr_Ser\",
                        dus.DocumentUcStr_CertNum as \"DocumentUcStr_CertNum\",
                        dus.DocumentUcStr_CertDate as \"DocumentUcStr_CertDate\",
                        dus.DocumentUcStr_CertGodnDate as \"DocumentUcStr_CertGodnDate\",
                        dus.DocumentUcStr_CertOrg as \"DocumentUcStr_CertOrg\",
                        dus.DrugLabResult_Name as \"DrugLabResult_Name\",
                        dus.PrepSeries_id as \"PrepSeries_id\",
                        dus.DocumentUcStr_IsNDS as \"DocumentUcStr_IsNDS\",
                        dus.GoodsUnit_bid as \"GoodsUnit_bid\",
                        dus.GoodsUnit_id as \"GoodsUnit_id\"
                    from
                        v_DocumentUcStr dus 

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
                wdst.WhsDocumentStatusType_Code as \"WhsDocumentStatusType_Code\"
            from
                v_WhsDocumentUc wdu 

				left join v_WhsDocumentStatusType wdst  on wdst.WhsDocumentStatusType_id = wdu.WhsDocumentStatusType_id

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
			update
			    WhsDocumentUc
			set
			    WhsDocumentStatusType_id = (select WhsDocumentStatusType_id from v_WhsDocumentStatusType  where WhsDocumentStatusType_Code = '8' limit 1), -- 8 - Отменен
			    pmUser_updID = :pmUser_id,
                WhsDocumentUc_updDT = dbo.tzGetDate()
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
                wdu.WhsDocumentUc_id as \"WhsDocumentUc_id\",
                wds.WhsDocumentSpecificity_id as \"WhsDocumentSpecificity_id\",
                wdst.WhsDocumentStatusType_Code as \"WhsDocumentStatusType_Code\"
            from
                v_WhsDocumentSpecificity wds 

                left join v_WhsDocumentUc wdu  on wdu.WhsDocumentUc_id = wds.WhsDocumentUc_id

				left join v_WhsDocumentStatusType wdst  on wdst.WhsDocumentStatusType_id = wdu.WhsDocumentStatusType_id

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
			select WhsDocumentSpecification_id as \"WhsDocumentSpecification_id\"
			from v_WhsDocumentSpecification 

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
				select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				from p_WhsDocumentSpecificity_del(
					WhsDocumentSpecificity_id := :WhsDocumentSpecificity_id);


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
                select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
                from p_WhsDocumentUc_del(
                    WhsDocumentUc_id := :WhsDocumentUc_id);


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
			$where[] = "gu.GoodsUnit_id = :GoodsUnit_id";
		} else {
			$descr_arr = array('единицы в упаковках');
			if (!empty($data['UserOrg_Type']) && $data['UserOrg_Type'] == 'lpu') { //если организация поставщика является ММО
				$descr_arr = array('единицы в упаковках', 'единицы количества', 'лекарственная форма');
			}
			$where[] = "(gpc.GoodsUnit_id is not null and gu.GoodsUnit_Descr in ('".join("', '", $descr_arr)."'))";

			if (!empty($data['query'])) {
				$where[] = "(gu.GoodsUnit_Nick iLIKE :query or gu.GoodsUnit_Name iLIKE :query)";

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
		    WITH cte AS (
                select
                    DrugComplexMnn_id as DrugComplexMnn_id,
                    d.DrugTorg_id as Tradnames_id
                from
                    rls.v_Drug d 

                where
                    Drug_id = :Drug_id
            )
            select
                gu.GoodsUnit_id as \"GoodsUnit_id\",
                gu.GoodsUnit_Name as \"GoodsUnit_Name\",
                (
                    COALESCE(gu.GoodsUnit_Nick, '') ||

                    COALESCE(' / ' || gu.GoodsUnit_Name, '') ||

                    COALESCE(' / ' || (case

                        when gu.GoodsUnit_Name = 'упаковка' then '1'
                        else cast(cast(gpc.GoodsPackCount_Count as decimal(10,0)) as varchar(10))
                    end) || ' шт. в уп.', '')
                ) as \"GoodsUnit_Str\",
                (case
                    when gu.GoodsUnit_Name = 'упаковка' then 1
                    else gpc.GoodsPackCount_Count
                end) as \"GoodsPackCount_Count\",
                (case
                    when gpc.GoodsPackCount_Count is not null then 'table'
                    else null
                end) as \"GoodsPackCount_Source\"
            from
                v_GoodsUnit gu

                LEFT JOIN LATERAL (

                    select
                        i_gpc.GoodsUnit_id,
                        i_gpc.GoodsPackCount_Count
                    from
                        v_GoodsPackCount i_gpc 

                    where
                        i_gpc.GoodsUnit_id = gu.GoodsUnit_id 
                        and
                        i_gpc.DrugComplexMnn_id = (CASE WHEN :Drug_id IS NOT NULL THEN (SELECT DrugComplexMnn_id FROM cte) ELSE :DrugComplexMnn_id END) and
                        (
                        
                            (CASE WHEN :Drug_id IS NOT NULL THEN (SELECT Tradnames_id FROM cte) ELSE :Tradnames_id END) is null or
                            i_gpc.TRADENAMES_ID is null or
                            i_gpc.TRADENAMES_ID = (CASE WHEN :Drug_id IS NOT NULL THEN (SELECT Tradnames_id FROM cte) ELSE :Tradnames_id END)
                            
                        ) and
                        (
                            i_gpc.Org_id is null or
                            COALESCE(i_gpc.Org_id, 0) = COALESCE(
                                                          CAST(:UserOrg_id  AS BIGINT)
                                                        , CAST(0            AS BIGINT)
                                                        )

                        )
                        
                    order by
                        i_gpc.TRADENAMES_ID desc, i_gpc.Org_id desc
                    limit 1
                ) gpc ON true
            {$where_clause}
            union
            select
                gu.GoodsUnit_id as \"GoodsUnit_id\",
                gu.GoodsUnit_Name as \"GoodsUnit_Name\",
                (
                    COALESCE(gu.GoodsUnit_Nick, '') ||

                    COALESCE(' / ' || gu.GoodsUnit_Name, '') ||

                    ' / 1 шт. в уп.'
                ) as \"GoodsUnit_Str\",
                1 as \"GoodsPackCount_Count\",
                'fixed_value' as \"GoodsPackCount_Count\"
            from
                v_GoodsUnit gu 
            where
                :GoodsUnit_id is null and -- упаковка добавляется в список только если не передан id конкретной записи
                gu.GoodsUnit_Name = 'упаковка' and
                (
                    :query is null or
                    'упаковка' iLIKE :query

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
                $where[] = 'o.Org_Name iLIKE :query';

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
		    select 
		        o.Org_id as \"Org_id\",
		        o.Org_Name as \"Org_Name\"
		    from
                v_Org o 

		    {$where_clause}
		    limit 250
		";

        $result = $this->db->query($query, $params);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
}