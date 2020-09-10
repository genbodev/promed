<?php defined('BASEPATH') or die ('No direct script access allowed');
require_once(APPPATH . 'models/_pgsq;/DocumentUc_model.php');

class Ufa_DocumentUc_model extends DocumentUc_model
{
	/**
	 * construct
	 */
	function __construct()
	{
		//parent::__construct();
		parent::__construct();
	}


	/**
	 * Загрузка списка строк документа учета для аптек
	 */
	function farm_loadDocumentUcStrList($filter)
	{
		//  Временно по Уфе (до перехода БФ на справочние РЛС) Тагир

		//log_message('debug', 'loadDocumentUcStrList = Ufa2');
		$query = "
			select
				dus.DocumentUcStr_id as \"DocumentUcStr_id\",
				dus.DocumentUcStr_oid as \"DocumentUcStr_oid\",
                dus.DrugFinance_id as \"DrugFinance_id\",
				Drug_Code as \"Drug_Code\", -- DocumentUcStr_oid,
				--o_ds.DrugShipment_Name as DocumentUcStr_oName,
				--convert(varchar (15), Drug_Code) as DocumentUcStr_oName,
				sh.DrugShipment_Name as \"DocumentUcStr_oName\",
				lpu.Lpu_id as \"Lpu_id\",
				lpu.Lpu_Nick as \"Lpu_Nick\",
				/*
					Признак наличия склада МО в аптеке
					Значения:
					2 - В строке не указано МО
					1 - Склад существует
					0 - Склада нет
				*/
				case
					when lpu.Lpu_id is null
						then 2
					else	
						COALESCE((select 1 from  OrgFarmacyIndex i where i.Lpu_id = lpu.Lpu_id limit 1), 0)
				end as \"Storage_ctrl\",
				dus.Drug_id as \"Drug_id\",
				d.Drug_Name as \"Drug_Name\",
				--dnmn.DrugNomen_Code,
				Drug_Code as \"DrugNomen_Code\",
				dus.DocumentUcStr_Count as \"DocumentUcStr_Count\",
				dus.DocumentUcStr_EdCount as \"DocumentUcStr_EdCount\",
				--dus.DocumentUcStr_RashCount,
				--dus.DocumentUcStr_RashEdCount,
				dus.DrugNds_id as \"DrugNds_id\",
				COALESCE(dn.DrugNds_Code, 0) as \"DrugNds_Code\",
				dus.DocumentUcStr_Price as \"DocumentUcStr_Price\",
				--dus.DocumentUcStr_NdsPrice,
				dus.DocumentUcStr_Sum as \"DocumentUcStr_Sum\",
				dus.DocumentUcStr_SumNds as \"DocumentUcStr_SumNds\",
				(
					case
						when
							COALESCE(isnds.YesNo_Code, 0) = 1
						then
							COALESCE(dus.DocumentUcStr_Sum, 0)
						else
							COALESCE(dus.DocumentUcStr_Sum, 0) + COALESCE(dus.DocumentUcStr_SumNds, 0)
					end

				) as \"DocumentUcStr_NdsSum\",
				dus.DocumentUcStr_Ser as \"DocumentUcStr_Ser\",
				to_char(dus.DocumentUcStr_godnDate, 'dd.mm.yyyy') as \"DocumentUcStr_godnDate\",
				to_char(DocumentUcStr_godnDate, 'dd.mm.yyyy') as \"PrepSeries_GodnDate\",
				1 as \"PrepSeries_id\",
				null as \"PrepSeries_isDefect\",
				dus.DocumentUcStr_CertNum as \"DocumentUcStr_CertNum\",
				to_char(dus.DocumentUcStr_CertDate, 'dd.mm.yyyy') as \"DocumentUcStr_CertDate\",
				to_char(dus.DocumentUcStr_CertGodnDate, 'dd.mm.yyyy') as \"DocumentUcStr_CertGodnDate\",
				dus.DocumentUcStr_CertOrg as \"DocumentUcStr_CertOrg\",
				dus.DrugLabResult_Name as \"DrugLabResult_Name\",
				COALESCE(isnds.YesNo_Code, 0) as \"DocumentUcStr_IsNDS\",
				--isnull(okei.Okei_NationSymbol, 'упак') as Okei_NationSymbol,
				null as \"Okei_NationSymbol\",
				--sf.cnt as SavedFileCount,
				null as \"SavedFileCount\",
				--ds.DrugShipment_Name,
				sh.DrugShipment_Name as \"DrugShipment_Name\",
				dus.DocumentUcStr_Reason as \"DocumentUcStr_Reason\",
				COALESCE(df.DrugFinance_Name, '') || ' / ' || COALESCE(wdcit.WhsDocumentCostItemType_Name, '') as \"Finance_and_CostItem\"
			from 
				v_DocumentUcStr dus
				inner join v_DocumentUc du on du.DocumentUc_id = dus.DocumentUc_id
				left join r2.attachMoByFarmacy a on a.DocumentUcStr_id = --dus.DocumentUcStr_id
				    case when  du.DrugDocumentType_id in (11) then dus.DocumentUcStr_oid else  dus.DocumentUcStr_id end
				left join v_Lpu lpu on lpu.Lpu_id = a.lpu_id
                left join v_DrugShipmentLink ln on ln.DocumentUcStr_id =
				    case when  du.DrugDocumentType_id in (11) then dus.DocumentUcStr_oid else  dus.DocumentUcStr_id end
				left join v_DrugShipment sh on sh.DrugShipment_id = ln.DrugShipment_id
				left join dbo.v_Drug d on d.Drug_id = dus.Drug_id
				left join v_DrugNds dn on dn.DrugNds_id = dus.DrugNds_id
				--left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dus.PrepSeries_id
				--left join v_YesNo isdef with (nolock) on isdef.YesNo_id = ps.PrepSeries_isDefect
				left join v_YesNo isnds on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
				left join v_Okei okei on okei.Okei_id = dus.Okei_id  
				left join v_DrugFinance df on df.DrugFinance_id = dus.DrugFinance_id
				left join v_WhsDocumentCostItemType wdcit on wdcit.WhsDocumentCostItemType_id = du.WhsDocumentCostItemType_id
			where dus.DocumentUc_id = :DocumentUc_id;      
		";

		$result = $this->db->query($query, array(
			'DocumentUc_id' => $filter['DocumentUc_id']
		));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение данных учетного документа
	 */
	protected function _farm_getDocumentUcStr($data)
	{

		$rules = array(
			array('field' => 'DocumentUc_id', 'label' => 'Идентификатор документа учета', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'WhsDocumentSupply_id', 'label' => 'Идентификатор договора поставок', 'type' => 'id', 'default' => null),
			array('field' => 'Org_sid', 'label' => 'Поставщик', 'type' => 'id', 'default' => null),
			array('field' => 'Org_rid', 'label' => 'Получатель', 'type' => 'id', 'default' => null),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams || !empty($err))
			return $err;

		$query = "
			select
				null as \"Drug_id\",
				dus.Drug_id as \"Drug_did\",
				dus.PrepSeries_id as \"PrepSeries_id\",
				COALESCE(sup_spec.Okei_id, 120) as \"Okei_id\",
				COALESCE(dus.DocumentUcStr_Count, 0) as \"DocumentUcStr_Count\",
				(
					case
						when
							COALESCE(isnds.YesNo_Code, 0) = 1
						then
							COALESCE(COALESCE(dus.DocumentUcStr_PriceR, dus.DocumentUcStr_Price), 0)
						else
							cast(COALESCE(COALESCE(dus.DocumentUcStr_PriceR, dus.DocumentUcStr_Price), 0)*(1+(COALESCE(dn.DrugNds_Code, 0)/100.0)) as decimal(12,2))
					end
				) as \"DocumentUcStr_Price\",
				ds.DrugShipment_id as \"DrugShipment_id\",
				(case when :WhsDocumentSupply_id is not null then :WhsDocumentSupply_id else wds.WhsDocumentSupply_id end) as \"WhsDocumentSupply_id\",
				(case when :Org_sid is not null then :Org_sid else wds.Org_sid end) as \"Org_sid\",
				(case when :Org_rid is not null then :Org_rid else wds.Org_rid end) as \"Org_rid\",
				(Select Storage_id from  v_OrgFarmacyIndex i where i.OrgFarmacy_id = farm.OrgFarmacy_id and i.Lpu_id = a.lpu_id limit 1) as \"Storage_id\",
				du.SubAccountType_tid aS \"SubAccountType_id\",
				dusw.DocumentUcStorageWork_id as \"DocumentUcStorageWork_id\",
				dusw.DocumentUcStorageWork_endDate as \"DocumentUcStorageWork_endDate\",
				COALESCE(dusw.DocumentUcStorageWork_FactQuantity, 0) as \"DocumentUcStorageWork_FactQuantity\",
				dusw.DocumentUcStorageWork_Comment as \"DocumentUcStorageWork_Comment\"
			from
				v_DocumentUcStr dus
				left join r2.attachMoByFarmacy a on a.DocumentUcStr_id = dus.DocumentUcStr_id
				join DocumentUc du On du.DocumentUc_id = dus.DocumentUc_id
				left join OrgFarmacy farm On farm.Org_id = du.Org_id
				left join v_YesNo isnds on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
				left join v_DrugNds dn on dn.DrugNds_id = dus.DrugNds_id
				left join lateral (
					select
						i_dsl.DrugShipment_id,
						i_ds.WhsDocumentSupply_id
					from
						v_DrugShipmentLink i_dsl
						left join v_DrugShipment i_ds on i_ds.DrugShipment_id = i_dsl.DrugShipment_id
					where
						i_dsl.DocumentUcStr_id = dus.DocumentUcStr_id
					limit 1
				) ds on true
				left join lateral (
					select
						WhsDocumentSupply_id,
						Org_sid,
						Org_rid
					from
						v_WhsDocumentSupply i_wds
					where
						i_wds.WhsDocumentSupply_id = ds.WhsDocumentSupply_id
					limit 1
				) wds on true
				left join lateral (
					select
						wdss.Okei_id
					from
						v_WhsDocumentSupplySpec wdss
					where
						(
							(:WhsDocumentSupply_id is not null and wdss.WhsDocumentSupply_id = :WhsDocumentSupply_id) or
							(wdss.WhsDocumentSupply_id is null and wdss.WhsDocumentSupply_id = ds.WhsDocumentSupply_id)
						)
						and wdss.Drug_id = dus.Drug_id
					limit 1
				) sup_spec on true
				left join lateral (
					select
						dusw.*
					from
						v_DocumentUcStorageWork dusw
					where
						dusw.DocumentUcStr_id = dus.DocumentUcStr_id
					order by
						dusw.DocumentUcStorageWork_insDT desc
					limit 1
				) dusw on true
			where
				dus.DocumentUc_id = :DocumentUc_id; 
		";

		return $this->queryResult($query, $queryParams);

	}


	/**
	 * Метод сохранения изменений в остатках складов
	 */
	protected function _farm_updateDrugOstatRegistry($data)
	{

		$rules = array(
			array('field' => 'Contragent_id', 'label' => 'Идентификатор контрагента', 'rules' => '', 'default' => null, 'type' => 'id'),
			array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'default' => null, 'type' => 'id'),
			array('field' => 'Storage_id', 'label' => 'Идентификатор склада', 'rules' => '', 'default' => null, 'type' => 'id'),
			array('field' => 'DrugShipment_id', 'label' => 'Идентификатор партии поступления', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Drug_id', 'label' => 'Идентификатор медикамента', 'rules' => '', 'type' => 'int'),
			array('field' => 'Drug_did', 'label' => 'Идентификатор медикамента2', 'rules' => '', 'type' => 'int'),
			array('field' => 'PrepSeries_id', 'label' => 'идентификатор справочника серий выпуска ЛС', 'rules' => '', 'default' => null, 'type' => 'id'),
			array('field' => 'SubAccountType_id', 'label' => 'Тип субсчета', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Okei_id', 'label' => 'Единицы измерения', 'rules' => 'required', 'type' => 'id'),

			array('field' => 'DrugOstatRegistry_Kolvo', 'label' => 'Количество упаковок на остатке', 'rules' => 'required', 'type' => 'float'),
			array('field' => 'DrugOstatRegistry_Sum', 'label' => 'Сумма', 'rules' => 'required', 'type' => 'float'),
			array('field' => 'DrugOstatRegistry_Cost', 'label' => 'Стоимость', 'rules' => '', 'default' => null, 'type' => 'float'),

			array('field' => 'pmUser_id', 'rules' => 'required', 'label' => 'Идентификатор пользователя', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams || !empty($err)) {
			echo '<pre>' . print_r($err, 1);
			return $err;
		}

		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from xp_DrugOstatRegistry_count (
				Contragent_id := :Contragent_id,
				Org_id := :Org_id,
				Storage_id := :Storage_id,
				DrugShipment_id := :DrugShipment_id,
				Drug_id := :Drug_id,
                Drug_did := :Drug_did,
				PrepSeries_id := :PrepSeries_id,
				SubAccountType_id := :SubAccountType_id,
				Okei_id := :Okei_id,
				DrugOstatRegistry_Kolvo := :DrugOstatRegistry_Kolvo,
				DrugOstatRegistry_Sum := :DrugOstatRegistry_Sum,
				DrugOstatRegistry_Cost := :DrugOstatRegistry_Cost,
				pmUser_id := :pmUser_id
			);
		";


		//echo getDebugSQL($query, $queryParams); exit;
		return $this->queryResult($query, $queryParams);
	}


	/**
	 * Корректировка регистра остатков при исполнении приходной накладной
	 */
	public function farm_updateDrugOstatRegistryForDokNak($data)
	{
		if (!isset($data['DocumentUc_id'])) {
			return array(array('Error_Msg' => 'Не указан идентификатор документа учета'));
		}

		//
		// Получаем данные о документе учета
		//

		$doc_data = $this->_getDocumentUcData($data);
		//echo '<pre>' . print_r( $doc_data, 1) . '</pre>';
		if (!$this->isSuccessful($doc_data)) {

			return $doc_data;
		}

		if (count($doc_data) == 0 || empty($doc_data[0])) {
			return array(array('Error_Msg' => 'Документа учета не найден'));
		}

		$doc_data = $doc_data[0];

		//
		// Получаем данные о гк
		//
		/*
		$sup_data = $this->_getWhsDocumentSupply( array(
			'WhsDocumentUc_id' => $doc_data[ 'WhsDocumentUc_id' ] ,
			'WhsDocumentUc_Num' => $doc_data[ 'DocumentUc_DogNum' ]
				) ) ;

		if ( !$this->isSuccessful( $sup_data ) ) {
			return $sup_data ;
		}
		*/

		$sup_data = (!empty($sup_data[0])) ? $sup_data[0] : array();

		//
		// Получаем строки документа учета
		//

		$drug_arr = $this->_farm_getDocumentUcStr(array(
			'DocumentUc_id' => $data['DocumentUc_id'],
			'WhsDocumentSupply_id' => isset($sup_data['WhsDocumentSupply_id']) ? $sup_data['WhsDocumentSupply_id'] : null,
			'Org_sid' => isset($sup_data['Org_sid']) ? $sup_data['Org_sid'] : null,
			'Org_rid' => isset($sup_data['Org_rid']) ? $sup_data['Org_rid'] : null
		));

		if (!$this->isSuccessful($drug_arr)) {
			return $drug_arr;
		}

		if (count($drug_arr) == 0) {
			return array(array('Error_Msg' => 'Список медикаментов пуст'));
		}

		foreach ($drug_arr as $drug) {
			//#102022 Проверка наряда на выполнение работ по документу
			if (!empty($drug['DocumentUcStorageWork_id'])) {
				//Не исполнять если не заполнена дата исполнения наряда
				if (empty($drug['DocumentUcStorageWork_endDate'])) {
					continue;
				}
				//Не исполнять если фактическое количество равно нулю
				if ($drug['DocumentUcStorageWork_FactQuantity'] == 0) {
					continue;
				}
				//Не исполнять если фактическое количество не равно количеству, указанному в накладной
				if ($drug['DocumentUcStorageWork_FactQuantity'] != $drug['DocumentUcStr_Count']) {
					continue;
				}
				//Не исполнять если указано примечание
				if (!empty($drug['DocumentUcStorageWork_Comment'])) {
					continue;
				}
			}

			//проверяем наличие получателя по документу в списке пунктов отпуска
			if (!isset($title_doc_cnt) || count($sup_data) < 1) {
				$query = "
					select
						count(wdt.WhsDocumentTitle_id) as \"cnt\"
					from
						v_WhsDocumentTitle wdt
						left join v_WhsDocumentTitleType wdtt on wdtt.WhsDocumentTitleType_id = wdt.WhsDocumentTitleType_id
						left join v_WhsDocumentRightRecipient wdrr on wdrr.WhsDocumentTitle_id = wdt.WhsDocumentTitle_id
					where
						wdt.WhsDocumentUc_id = :WhsDocumentSupply_id and
						wdtt.WhsDocumentTitleType_Code = 3 and --Приложение к ГК: список пунктов отпуска
						Org_id = :Org_id;
				";
				$title_doc_cnt = $this->getFirstResultFromQuery($query, array(
					'Org_id' => $doc_data['Org_tid'],
					'WhsDocumentSupply_id' => $drug['WhsDocumentSupply_id']
				));
			}

			if ($doc_data['DrugDocumentType_pCode'] != '10' && ((isset($title_doc_cnt) && $title_doc_cnt > 0) || $doc_data['Org_tid'] == $drug['Org_rid'])) { //проверяем является ли получатель по документу - грузополучателем по ГК (проверка отключена для документов созданых на основе расходных накладных)
				//списание остатков со счета поставщика
				//ищем нужные записи в регистре и проверяем наличие необходимого количества медикамента
				$kolvo = $drug['DocumentUcStr_Count'];

				$res = $this->farm_getDrugOstatRegistryData(array(
					'Org_id' => $drug['Org_sid'],
					'Drug_id' => $drug['Drug_id'],
					'SubAccountType_id' => $drug['SubAccountType_id'],
					'WhsDocumentSupply_id' => $drug['WhsDocumentSupply_id'],
					'PrepSeries_id' => $doc_data['Org_sid'] != $drug['Org_sid'] ? $drug['PrepSeries_id'] : null, //серия при списании учитывается только если поставщик из документа учета не является поставщиком по госконтракту
					'DocumentUcStr_Price' => $drug['DocumentUcStr_Price']
				));

				if (!$this->isSuccessful($res)) {
					return $this->createError(0, 'Ошибка создания регистра остатков');
				}

				foreach ($res as $ostat) {
					if ($kolvo > 0) {
						//списание
						$kol = $ostat['DrugOstatRegistry_Kolvo'] <= $kolvo ? $ostat['DrugOstatRegistry_Kolvo'] : $kolvo;
						$sum = $ostat['DrugOstatRegistry_Cost'] > 0 ? $ostat['DrugOstatRegistry_Cost'] * $kol : ($ostat['DrugOstatRegistry_Sum'] / $ostat['DrugOstatRegistry_Kolvo']) * $kol;

						$kolvo -= $kol;


						$q_params = array(
							'Contragent_id' => $ostat['Contragent_id'],
							'Org_id' => $drug['Org_sid'],
							'Storage_id' => $ostat['Storage_id'],
							'DrugShipment_id' => $ostat['DrugShipment_id'],
							'Drug_id' => $ostat['Drug_id'],
							'PrepSeries_id' => $ostat['PrepSeries_id'],
							'SubAccountType_id' => $drug['SubAccountType_id'],
							'Okei_id' => $ostat['Okei_id'],
							'DrugOstatRegistry_Kolvo' => $kol * (-1),
							'DrugOstatRegistry_Sum' => $sum * (-1),
							'DrugOstatRegistry_Cost' => $ostat['DrugOstatRegistry_Cost'],
							'pmUser_id' => $data['pmUser_id']
						);


						$result = $this->_farm_updateDrugOstatRegistry($q_params);
						if (!$this->isSuccessful($result)) {
							return $this->createError(0, 'Ошибка списания остатков');
						}

						//зачисление
						$q_params['Contragent_id'] = $doc_data['Contragent_tid'];
						$q_params['PrepSeries_id'] = $drug['PrepSeries_id'];
						$q_params['Org_id'] = $doc_data['Org_tid'];
						$q_params['Storage_id'] = $drug['Storage_id'];
						//$q_params[ 'Storage_id' ] = $doc_data[ 'Storage_tid' ] ;
						$q_params['DrugOstatRegistry_Kolvo'] = $kol;
						$q_params['DrugOstatRegistry_Sum'] = $sum;
						$q_params['DrugShipment_id'] = $drug['DrugShipment_id'];

						$result = $result = $this->_farm_updateDrugOstatRegistry($q_params);
						if (!$this->isSuccessful($result)) {
							return $this->createError(0, 'Ошибка зачисления остатков');
						}
					}
				}

				if ($kolvo > 0) {
					return $this->createError(0, 'На остатках поставщика недостаточно медикаментов для списания.');
				}
			} else {
				$shipment_id = null;

				//создаем записи в регистре
				$result = $result = $this->_farm_updateDrugOstatRegistry(array(
					'Contragent_id' => $doc_data['Contragent_tid'],
					'Org_id' => $doc_data['Org_tid'],
					//'Storage_id' => $doc_data[ 'Storage_tid' ] ,
					'Storage_id' => $drug['Storage_id'],
					//'DrugShipment_id' => 1, //$drug[ 'DrugShipment_id' ] ,
					//  Если партия не заполнена, ставим дежурную"
					'DrugShipment_id' => !empty($drug['DrugShipment_id']) ? $drug['DrugShipment_id'] : 1,
					'Drug_id' => $drug['Drug_id'],
					'Drug_did' => $drug['Drug_did'],
					'SubAccountType_id' => $drug['SubAccountType_id'],
					'PrepSeries_id' => $drug['PrepSeries_id'],
					'Okei_id' => $drug['Okei_id'],
					'DrugOstatRegistry_Kolvo' => $drug['DocumentUcStr_Count'],
					'DrugOstatRegistry_Sum' => $drug['DocumentUcStr_Price'] * $drug['DocumentUcStr_Count'],
					'DrugOstatRegistry_Cost' => $drug['DocumentUcStr_Price'],
					'pmUser_id' => $data['pmUser_id']
				));
				if (!$this->isSuccessful($result)) {
					return $this->createError(0, 'Ошибка создания регистра остатков2');
				}
			}
		}

		return array(array());
	}


	/**
	 * Корректировка регистра остатков при исполнении накладной на внутреннее перемещение
	 */
	function farm_updateDrugOstatRegistryForDocNVP($data)
	{
		if (!isset($data['DocumentUc_id'])) {
			return array(array('Error_Msg' => 'Не указан идентификатор документа учета'));
		}

		//получаем данные о документе учета
		$doc_data = array();
		$query = "
			select
				COALESCE(c_sid.Org_id, l_sid.Org_id) as \"Org_sid\",
				COALESCE(c_tid.Org_id, l_tid.Org_id) as \"Org_tid\",
				du.Contragent_sid as \"Contragent_sid\",
				du.Contragent_tid as \"Contragent_tid\",
				du.Storage_sid as \"Storage_sid\",
				du.Storage_tid as \"Storage_tid\"
			from
				v_DocumentUc du
				left join v_Contragent c_tid on c_tid.Contragent_id = du.Contragent_tid
				left join v_Lpu l_tid on l_tid.Lpu_id = c_tid.Lpu_id
				inner join v_Contragent c_sid on c_sid.Contragent_id = du.Contragent_sid
				left join v_Lpu l_sid on l_sid.Lpu_id = c_sid.Lpu_id
			where
				du.DocumentUc_id = :DocumentUc_id;
		";
		/*
		echo getDebugSQL($query, array(
	'DocumentUc_id' => $data['DocumentUc_id']
));
		exit;
		*/
		$result = $this->db->query($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));
		if (is_object($result)) {
			$res = $result->result('array');
			if (count($res) > 0) {
				$doc_data = $res[0];
			}
		}
		if (count($doc_data) == 0) {
			return array(array('Error_Msg' => 'Документа учета не найден'));
		}

		//получаем строки документа учета
		$drug_arr = array();
		$query = "
			select
				dus.DocumentUcStr_id as \"DocumentUcStr_id\",
				dus.Drug_id as \"Drug_id\",
				dus.PrepSeries_id as \"PrepSeries_id\",
				COALESCE(dus.DocumentUcStr_Count, 0) as \"DocumentUcStr_Count\",
				(
					case
						when
							COALESCE(isnds.YesNo_Code, 0) = 1
						then
							COALESCE(COALESCE(dus.DocumentUcStr_PriceR, dus.DocumentUcStr_Price), 0)
						else
							cast(COALESCE(COALESCE(dus.DocumentUcStr_PriceR, dus.DocumentUcStr_Price), 0)*(1+(COALESCE(dn.DrugNds_Code, 0)/100.0)) as decimal(12,2))
					end
				) as \"DocumentUcStr_Price\",
				ds.DrugShipment_id as \"DrugShipment_id\",
				ds.WhsDocumentSupply_id as \"WhsDocumentSupply_id\"
			from
				v_DocumentUcStr dus 
				left join v_YesNo isnds on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
				left join v_DrugNds dn on dn.DrugNds_id = dus.DrugNds_id
				left join lateral (
					select
						i_dsl.DrugShipment_id,
						i_ds.WhsDocumentSupply_id
					from
						v_DrugShipmentLink i_dsl
						left join v_DrugShipment i_ds on i_ds.DrugShipment_id = i_dsl.DrugShipment_id
					where
						i_dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
					limit 1
				) ds on true
			where
				dus.DocumentUc_id = :DocumentUc_id;
		";

		/*
		 echo getDebugSQL($query, array(
	 'DocumentUc_id' => $data['DocumentUc_id']
 ));
		 exit;
		 */

		$result = $this->db->query($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));
		if (is_object($result)) {
			$drug_arr = $result->result('array');
		}
		if (count($drug_arr) == 0) {
			return array(array('Error_Msg' => 'Список медикаментов пуст'));
		}

		//var_dump ($drug_arr); exit;

		//получаем стартовый номер партии
		$query = "
			select
				COALESCE(max(cast(DrugShipment_Name as bigint)),0) + 1 as \"DrugShipment_Name\"
			from
				v_DrugShipment
			where
				DrugShipment_Name not ilike '%.%' and
				DrugShipment_Name not ilike '%,%' and
				DrugShipment_Name not ilike '%e%' and
				length(DrugShipment_Name) <= 18 and
				(DrugShipment_Name ~ '^([0-9]+[.]?[0-9]*|[.][0-9]+)$')
		";
		$sh_num = $this->getFirstResultFromQuery($query);

		//запросы для создания партий
		$sh_query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\",
				DrugShipment_id as \"DrugShipment_id\"
			from p_DrugShipment_ins (
				DrugShipment_setDT := dbo.tzGetDate(),
				DrugShipment_Name := :DrugShipment_Name,
				WhsDocumentSupply_id := :WhsDocumentSupply_id,
				pmUser_id := :pmUser_id
			);
		";

		$shl_query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\",
				DrugShipmentLink_id as \"DrugShipmentLink_id\"
			from p_DrugShipmentLink_ins (
				DrugShipment_id := :DrugShipment_id,
				DocumentUcStr_id := :DocumentUcStr_id,
				pmUser_id := :pmUser_id
			);
		";


		foreach ($drug_arr as $drug) {
			//создаем новую партию для строки накладной
			if (isset($drug['DrugShipment_id'])) {
				$sh_id = $drug['DrugShipment_id'];
			} else {
				$sh_id = $this->getFirstResultFromQuery($sh_query, array(
					'DrugShipment_Name' => $sh_num++,
					'WhsDocumentSupply_id' => $drug['WhsDocumentSupply_id'],
					'pmUser_id' => $data['pmUser_id']
				));

				//связь партии со строкой накладной
				$shl_id = $this->getFirstResultFromQuery($shl_query, array(
					'DrugShipment_id' => $sh_id,
					'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
					'pmUser_id' => $data['pmUser_id']
				));
			}


			//ищем нужные записи в регистре и проверяем наличие необходимого количества медикамента
			$kolvo = $drug['DocumentUcStr_Count'];

			$query = "
				select
					dor.Contragent_id as \"Contragent_id\",
					dor.Org_id as \"Org_id\",
					dor.Storage_id as \"Storage_id\",
					dor.DrugShipment_id as \"DrugShipment_id\",
					dor.Drug_id as \"Drug_id\",
                    dor.Drug_did as \"Drug_did\",
					dor.PrepSeries_id as \"PrepSeries_id\",
					dor.Okei_id as \"Okei_id\",
					dor.DrugOstatRegistry_Kolvo as \"DrugOstatRegistry_Kolvo\",
					dor.DrugOstatRegistry_Sum as \"DrugOstatRegistry_Sum\",
					dor.DrugOstatRegistry_Cost as \"DrugOstatRegistry_Cost\"
				from
					v_DrugOstatRegistry dor
					left join v_Contragent c on c.Contragent_id = dor.Contragent_id
					left join v_Lpu l on l.Lpu_id = c.Lpu_id
					left join v_SubAccountType sat on sat.SubAccountType_id = dor.SubAccountType_id
					left join v_DrugShipment ds on ds.DrugShipment_id = dor.DrugShipment_id
				where
					coalesce(dor.Org_id,c.Org_id,l.Org_id,0) = COALESCE(:Org_id,0) and
					COALESCE(dor.Storage_id, 0) = COALESCE(:Storage_id, 0) and
					dor.Drug_did = :Drug_id and
					dor.DrugShipment_id = :DrugShipment_id and
					sat.SubAccountType_Code = 1 and
					dor.DrugOstatRegistry_Kolvo > 0 and
					(:DocumentUcStr_Price is null or dor.DrugOstatRegistry_Cost = :DocumentUcStr_Price);
			";

			/*
			echo getDebugSQL($query, array(
				'Org_id' => $doc_data['Org_sid'],
				'Storage_id' => $doc_data['Storage_sid'],
				'Drug_id' => $drug['Drug_id'],
				'DrugShipment_id' => $drug['DrugShipment_id'],
				'DocumentUcStr_Price' => $drug['DocumentUcStr_Price']
			));
			exit;
			*/


			$result = $this->db->query($query, array(
				'Org_id' => $doc_data['Org_sid'],
				'Storage_id' => $doc_data['Storage_sid'],
				'Drug_id' => $drug['Drug_id'],
				'DrugShipment_id' => $drug['DrugShipment_id'],
				'DocumentUcStr_Price' => $drug['DocumentUcStr_Price']
			));

			if (is_object($result)) {
				$res = $result->result('array');
				if (!empty($res[0]['Error_Msg'])) {
					return array(0 => array('Error_Msg' => 'Ошибка создания регистра остатков'));
				}

				foreach ($res as $ostat) {
					if ($kolvo > 0) {
						//списание
						$kol = $ostat['DrugOstatRegistry_Kolvo'] <= $kolvo ? $ostat['DrugOstatRegistry_Kolvo'] : $kolvo;
						$sum = $ostat['DrugOstatRegistry_Cost'] > 0 ? $ostat['DrugOstatRegistry_Cost'] * $kol : ($ostat['DrugOstatRegistry_Sum'] / $ostat['DrugOstatRegistry_Kolvo']) * $kol;

						$kolvo -= $kol;

						$query = "
							select
								Error_Code as \"Error_Code\",
								Error_Message as \"Error_Msg\"
				            from xp_DrugOstatRegistry_count (
								Contragent_id := :Contragent_id,
								Org_id := :Org_id,
								Storage_id := :Storage_id,
								DrugShipment_id := :DrugShipment_id,
								Drug_id := :Drug_id,
                                Drug_did := :Drug_did,
								PrepSeries_id := :PrepSeries_id,
								SubAccountType_id := 1, -- субсчёт доступно
								Okei_id := :Okei_id,
								DrugOstatRegistry_Kolvo := :DrugOstatRegistry_Kolvo,
								DrugOstatRegistry_Sum := :DrugOstatRegistry_Sum,
								DrugOstatRegistry_Cost := :DrugOstatRegistry_Cost,
								pmUser_id := :pmUser_id
							);
						";

						$q_params = array(
							'Contragent_id' => $ostat['Contragent_id'],
							'Org_id' => !empty($ostat['Org_id']) ? $doc_data['Org_sid'] : null,
							'Storage_id' => $ostat['Storage_id'],
							'DrugShipment_id' => $ostat['DrugShipment_id'],
							'Drug_id' => $ostat['Drug_id'],
							'Drug_did' => $ostat['Drug_did'],
							'PrepSeries_id' => $ostat['PrepSeries_id'],
							'Okei_id' => $ostat['Okei_id'],
							'DrugOstatRegistry_Kolvo' => $kol * (-1),
							'DrugOstatRegistry_Sum' => $sum * (-1),
							'DrugOstatRegistry_Cost' => $ostat['DrugOstatRegistry_Cost'],
							'pmUser_id' => $data['pmUser_id']
						);

						//echo getDebugSQL($query, $q_params);

						$result = $this->db->query($query, $q_params);
						if (is_object($result)) {
							$res = $result->result('array');
							if (!empty($res[0]['Error_Msg'])) {
								return array(0 => array('Error_Msg' => 'Ошибка списания остатков'));
							}
						} else {
							return array(0 => array('Error_Msg' => 'Ошибка запроса списания остатков'));
						}

						//зачисление
						$q_params['Storage_id'] = $doc_data['Storage_tid'];
						$q_params['DrugShipment_id'] = $sh_id; //идентификатор новой созданной партии
						$q_params['DrugOstatRegistry_Kolvo'] = $kol;
						$q_params['DrugOstatRegistry_Sum'] = $sum;
						//echo getDebugSQL($query, $q_params);exit;
						$result = $this->db->query($query, $q_params);
						if (is_object($result)) {
							$res = $result->result('array');
							if (!empty($res[0]['Error_Msg'])) {
								return array(0 => array('Error_Msg' => 'Ошибка зачисления остатков'));
							}
						} else {
							return array(0 => array('Error_Msg' => 'Ошибка запроса зачисления остатков'));
						}
					}
				}
			}

			if ($kolvo > 0) {
				return array(0 => array('Error_Msg' => 'На остатках поставщика недостаточно медикаментов для списания.'));
			}
		}

		return array(array());
	}

	/**
	 * Создание электронной накладной
	 */
	function farm_createDokNakByDocumentUc($data)
	{
		$new_doc_id = null;
		$error = array();
		$doc_data = array();
		$drug_data = array();

		//получаем стартовый номер партии
		$query = "
			select
				COALESCE(max(cast(DrugShipment_Name as bigint)),0) + 1 as \"DrugShipment_Name\"
			from
				v_DrugShipment
			where
				DrugShipment_Name not ilike '%.%' and
				DrugShipment_Name not ilike '%,%' and
				DrugShipment_Name not ilike '%e%' and
				length(DrugShipment_Name) <= 18 and
				(DrugShipment_Name ~ '^([0-9]+[.]?[0-9]*|[.][0-9]+)$')
		";
		$sh_num = $this->getFirstResultFromQuery($query);

		//получение данных документа учета
		$query = "
			select
				du.WhsDocumentUc_id as \"WhsDocumentUc_id\",
				du.Contragent_tid as \"Contragent_tid\",
				tcon.Org_id as \"Org_tid\"
			from
				v_DocumentUc du
				left join v_Contragent tcon on tcon.Contragent_id = du.Contragent_tid
			where
				du.DocumentUc_id = :DocumentUc_id;
		";
		$doc_data = $this->getFirstRowFromQuery($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));
		if (!is_array($doc_data) || count($doc_data) < 1) {
			$error[] = 'Не удалось получить данные о документе учета.';
		}

		//получение списка медикаментов
		$query = "
			select
				dus.DocumentUcStr_id as \"DocumentUcStr_id\",
				ds.WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
                ds.DrugShipment_id as \"DrugShipment_id\"
			from
				v_DocumentUcStr dus
				left join v_DrugShipmentLink dsl on dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
				left join v_DrugShipment ds on ds.DrugShipment_id = dsl.DrugShipment_id
			where
				dus.DocumentUc_id = :DocumentUc_id;
		";
		$result = $this->db->query($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));
		if (is_object($result)) {
			$drug_data = $result->result('array');
		}

		if (!is_array($doc_data) || count($doc_data) < 1) {
			$error[] = 'Не удалось получить данные о документе учета.';
		}

		//копирование документа учета
		if (count($error) == 0) {
			$response = $this->copyObject('DocumentUc', array(
				'DocumentUc_id' => $data['DocumentUc_id'],
				'DocumentUc_pid' => $data['DocumentUc_id'],
				'Contragent_id' => $doc_data['Contragent_tid'],
				'Org_id' => $doc_data['Org_tid'],
				'DrugDocumentType_id' => $this->getObjectIdByCode('DrugDocumentType', 6), //6 - Приходная накладная;
				'DrugDocumentStatus_id' => $this->getObjectIdByCode('DrugDocumentStatus', 1), //1 - Новый;
				'pmUser_id' => $data['pmUser_id']
			));
			if (!empty($response['Error_Msg'])) {
				$error[] = $response['Error_Msg'];
			} else {
				$new_doc_id = $response['DocumentUc_id'];
			}
		}

		//запросы для создания партий
		$sh_query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\",
				DrugShipment_id as \"DrugShipment_id\"
			from p_DrugShipment_ins (
				DrugShipment_setDT := dbo.tzGetDate(),
				DrugShipment_Name := :DrugShipment_Name,
				WhsDocumentSupply_id := :WhsDocumentSupply_id,
				pmUser_id := :pmUser_id
			);
		";

		$shl_query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\",
				DrugShipmentLink_id as \"DrugShipmentLink_id\"
			from p_DrugShipmentLink_ins (
				DrugShipment_id := :DrugShipment_id,
				DocumentUcStr_id := :DocumentUcStr_id,
				pmUser_id := :pmUser_id
			);
		";

		//копирование списка медикаментов
		if (count($error) == 0) {
			foreach ($drug_data as $drug) {
				$response = $this->copyObject('DocumentUcStr', array(
					'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
					'DocumentUcStr_oid' => null,
					'DocumentUc_id' => $new_doc_id,
					'pmUser_id' => $data['pmUser_id']
				));
				if (!empty($response['Error_Msg'])) {
					$error[] = $response['Error_Msg'];
					break;
				} else if (!empty($response['DocumentUcStr_id'])) {
					if (isset($drug['DrugShipment_id'])) {
						$sh_id = $drug['DrugShipment_id'];
					} else {
						//создание партии
						$sh_id = $this->getFirstResultFromQuery($sh_query, array(
							'DrugShipment_Name' => $sh_num++,
							'WhsDocumentSupply_id' => $drug['WhsDocumentSupply_id'],
							'pmUser_id' => $data['session']['pmuser_id']
						));
					}

					//связь партии со строкой документа учета
					$shl_id = $this->getFirstResultFromQuery($shl_query, array(
						'DrugShipment_id' => $sh_id,
						'DocumentUcStr_id' => $response['DocumentUcStr_id'],
						'pmUser_id' => $data['session']['pmuser_id']
					));
				}
			}
		}

		if (count($error) > 0) {
			return array(array('Error_Msg' => $error[0]));
		} else {
			return array(array());
		}
	}


	/**
	 * Загрузка списка медикаментов для комбо (используется при редактировании спецификации документа учета)
	 * Для аптек
	 */
	public function farm_loadDrugComboForDocumentUcStr($filter)
	{
		$where = array();
		$with = array();
		$join = array();
		$type = 'vozv';

		//d.Drug_begDate<= GetDate() and isnull(d.Drug_endDate, GetDate()) >= GetDate() and
		$where[] = 'd.Drug_begDate <= GetDate() and COALESCE(d.Drug_endDate, GetDate()) >= GetDate()';

		if ($filter['Drug_id'] > 0) {
			$where[] = 'd.Drug_id = :Drug_id';
		} else {
			if (!empty($filter['DrugNomen_Code'])) {
				$where[] = 'dn.DrugNomen_Code = :DrugNomen_Code';
			}
			if ($filter['WhsDocumentUc_id'] > 0) {
				$type = 'prix';
				$query = "
					select
						count(Drug_did) as \"cnt\"
					from
						v_WhsDocumentSupplySpec
					where
						WhsDocumentSupply_id = :WhsDocumentUc_id;
				";
				$result = $this->getFirstResultFromQuery($query, array(
					'WhsDocumentUc_id' => $filter['WhsDocumentUc_id']
				));
				if ($result && $result > 0) {
					$where[] = 'd.Drug_id in (
						select
							Drug_did
						from
							v_WhsDocumentSupplySpec
						where
							WhsDocumentSupply_id = :WhsDocumentUc_id
					)';
				}
			}
			if ($filter['DocumentUc_id'] > 0) {
				$where[] = 'd.Drug_id in (
					select
						Drug_did
					from
						v_DocumentUcStr
					where
						DocumentUc_id = :DocumentUc_id
				)';
			}
			if (strlen($filter['query']) > 0) {
				$filter['query'] = '%' . preg_replace('/ /', '%', $filter['query']) . '%';
				$where[] = 'd.Drug_Name iLIKE :query';
			}
			if ($type == 'vozv' && ((!empty($filter['Storage_id']) && $filter['Storage_id'] > 0) || (!empty($filter['Contragent_id']) && $filter['Contragent_id'] > 0))) {
				$with[] = " ostat_cnt as (
					select 
						COALESCE(dor.Drug_id, dor.Drug_did) Drug_id,
						COALESCE(sum(DrugOstatRegistry_Kolvo), 0) as cnt
					from
						v_DrugOstatRegistry dor
						left join v_SubAccountType sat on sat.SubAccountType_id = dor.SubAccountType_id
					where
						dor.DrugOstatRegistry_Kolvo > 0 and
						sat.SubAccountType_Code = 1 and
						(:Storage_id is null or dor.Storage_id = :Storage_id) and
						(:Contragent_id is null or dor.Contragent_id = :Contragent_id) and
						(COALESCE(dor.DrugFinance_id,0)=0 or :DrugFinance_id is null or dor.DrugFinance_id = :DrugFinance_id) and
						(COALESCE(dor.WhsDocumentCostItemType_id,0)=0 or :WhsDocumentCostItemType_id is null or dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id)
					group by
						dor.Drug_id,
						dor.Drug_did
				)";
				$join[] = "inner join ostat_cnt on ostat_cnt.Drug_id = d.Drug_id";
				$where[] = 'ostat_cnt.cnt > 0';
			}
		}

		if (count($where) > 0 || count($with) > 0) {
			log_message('debug', 'loadDrugComboForDocumentUcStr = Ufa3');
			$q = "
				select
					-- select
					d.Drug_id as \"Drug_id\",
					COALESCE(cast(d.Drug_Code as varchar), '') || '  ' || d.Drug_Name as \"Drug_Name\",
					d. Drug_Code as \"DrugNomen_Code\",
					d.Drug_Fas as \"Drug_Fas\",
					f.drugform_name as \"DrugForm_Name\",
					'упак.' as \"DrugUnit_Name\",
					bf.PR_REG as \"Price\"
					--rtrim(isnull(d.DrugForm_Name, '')) as DrugForm_Name,
					--rtrim(isnull(d.Drug_PackName, '')) as DrugUnit_Name
					-- end select
				from
					-- from
					dbo.v_Drug d
					left join drugform  f on f.drugform_id = d.drugform_id
					left join tmp.BF_SPRLSMNN bf on bf.nomk_ls = d.Drug_Code 
					left join lateral (
						select
							v_DrugNomen.DrugNomen_Code
						from
							rls.v_DrugNomen
						/*
						where
							v_DrugNomen.Drug_id = d.Drug_id
						*/
						order by
							DrugNomen_id
						limit 1
					) dn on true
                                       
					" . join($join, ' ') . "
					-- end from
				where
					-- where
					" . join($where, ' and ') . "
					-- end where
				order by
					-- order by
					d.Drug_Name
					-- end order by
                limit 1000
			";
			/*
if (count($with) > 0) {
	$q = "with ".join($with, ', ').$q;
}
			*/

			if (count($with) > 0) {
				$with_str = join($with, ', ');
				$q = "-- addit with
					with {$with_str} 
					 -- end addit with
					{$q}
				";
			}


			//echo getDebugSQL($q, $filter);exit;

			/*
			$result = $this->db->query(getLimitSQLPH($q, $filter['start'], $filter['limit']), $filter);
			$result_count = $this->db->query(getCountSQLPH($q), $filter);

			if (is_object($result_count))
			{
				$cnt_arr = $result_count->result('array');
				$count = $cnt_arr[0]['cnt'];
				unset($cnt_arr);
			} else {
				$count = 0;
			} if (is_object($result)) {
				$response = array();
				$response['data'] = $result->result('array');
				$response['totalCount'] = $count;
				return $response;
			} else {
				return false;
			}
			*/

			if (!empty($filter['limit'])) {
				$result = $this->db->query(getLimitSQLPH($q, $filter['start'], $filter['limit']), $filter);
				$count = $this->getFirstResultFromQuery(getCountSQLPH($q), $filter);
				if (is_object($result) && $count !== false) {
					return array(
						'data' => $result->result('array'),
						'totalCount' => $count
					);
				} else {
					return false;
				}
			} else {

				//$dbrep = $this->load->database('bdwork', true);

				$dbrep = $this->db;

				$result = $dbrep->query($q, $filter);
				if (is_object($result)) {
					return $result->result('array');
				} else {
					return false;
				}
			}

		}

		return false;
	}

	/**
	 * Исполнение документа для аптек
	 */
	function farm_executeDocumentUc($data)
	{
		//старт транзакции
		$this->beginTransaction();

		$error = array(); //для сбора ошибок при "исполнении" документа
		$result = array();

		//Проверки
		//Недопустимый статус
		if (!in_array($data['DrugDocumentStatus_Code'], array(1))) { //1 - Новый
			$error[] = "Исполнение документа невозможно. Недопустимый статус документа: {$data['DrugDocumentStatus_Name']}.";
		}

		//Смена статуса документа на время исполнения
		if (count($error) < 1) {
			$response = $this->farm_saveObject('DocumentUc', array(
				'DocumentUc_id' => $data['DocumentUc_id'],
				'DrugDocumentStatus_id' => $this->getObjectIdByCode('DrugDocumentStatus', '12'), //12 - На исполнении
				'pmUser_id' => $data['pmUser_id']
			));
			if (!empty($response['Error_Msg'])) {
				$error[] = $response['Error_Msg'];
			}
		}

		/*
		 ******   Для Уфы проверка пока убрана  Тагир 28.08.2015

//В списке медикаментов есть позиции с пустой серией или сроком годности
$query = "
	select
		count(DocumentUcStr_id) as cnt
	from
		DocumentUcStr with (nolock)
	where
		DocumentUc_id = :DocumentUc_id and
						(DocumentUcStr_Ser is null or DocumentUcStr_Ser = '')
		--PrepSeries_id is null
";
$res = $this->getFirstResultFromQuery($query, array('DocumentUc_id' => $data['DocumentUc_id']));
if ($res > 0) {
	$error[] = "Исполнение документа невозможно, так как в списке медикаментов есть строки без серии.";
}
		*/

		//Непосредственное исполнение
		if (count($error) < 1) {
			switch ($data['DrugDocumentType_Code']) {
				case 2: //Документ списания
					//Корректировка регистра остатков
					$response = $this->updateDrugOstatRegistryForDokSpis($data);
					if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
						$error[] = $response[0]['Error_Msg'];
					}
					break;
				case 3: //Документ ввода остатков
					//Корректировка регистра остатков
					$response = $this->updateDrugOstatRegistryForDokOst($data);
					if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
						$error[] = $response[0]['Error_Msg'];
					}
					$response = $this->saveDrugListToWhsDocumentSupply($data);
					if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
						$error[] = $response[0]['Error_Msg'];
					}
					break;
				case 6: //Приходная накладная
					//Корректировка регистра остатков

					$response = $this->farm_updateDrugOstatRegistryForDokNak($data);


					if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
						$error[] = $response[0]['Error_Msg'];
					}
					break;
				case 10: //Расходная накладная
					//копируем документ, превращая в накладную
					$response = $this->farm_createDokNakByDocumentUc($data);
					if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
						$error[] = $response[0]['Error_Msg'];
					}
					$response = $this->farm_updateDrugOstatRegistryForDocRas($data);
					if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
						$error[] = $response[0]['Error_Msg'];
					}
					break;
				case 12: //Документ оприходования
					//Корректировка регистра остатков
					$response = $this->updateDrugOstatRegistryForDocOprih($data);
					if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
						$error[] = $response[0]['Error_Msg'];
					}
					break;
				case 15: //Накладная на внутреннее перемещение
					//Корректировка регистра остатков
					$response = $this->farm_updateDrugOstatRegistryForDocNVP($data);
					if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
						$error[] = $response[0]['Error_Msg'];
					}
					break;
				case 11:
				case 17: //Возвратная накладная (расходная)
					//Корректировка регистра остатков
					$response = $this->farm_updateDrugOstatRegistryForDocVozNakR($data);
					if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
						$error[] = $response[0]['Error_Msg'];
					}
					break;
				case 18: //Возвратная накладная (приходная)
					//Корректировка регистра остатков
					$response = $this->updateDrugOstatRegistryForDocVozNakP($data);
					if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
						$error[] = $response[0]['Error_Msg'];
					}
					break;
				case 21: // Списание медикаментов со склада на пациента
					$response = $this->updateDrugOstatRegistryForDocRealPat($data);
					if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
						$error[] = $response[0]['Error_Msg'];
					}
					break;
				default:
					$error[] = "Для данного типа документов не предусмотрен механизм \"исполнения\"";
					break;
			}

		}

		//смена статуса документа
		if (count($error) < 1) {
			//проставляем статус "исполнен" для изначльного документа
			$result = $this->farm_saveObject('DocumentUc', array(
				'DocumentUc_id' => $data['DocumentUc_id'],
				'DrugDocumentStatus_id' => $this->getObjectIdByCode('DrugDocumentStatus', 4), //4 - Исполнен
				'pmUser_id' => $data['pmUser_id']
			));
		}

		if (count($error) > 0) {
			$result = array('Error_Msg' => $error[0]);
			$this->rollbackTransaction();
			return $result;
		}

		//коммит транзакции
		$this->commitTransaction();

		return $result;
	}

	/**
	 * Корректировка регистра остатков при исполнении возвратной накладной (расходной)
	 */
	function farm_updateDrugOstatRegistryForDocVozNakR($data)
	{
		if (!isset($data['DocumentUc_id'])) {
			return array(array('Error_Msg' => 'Не указан идентификатор документа учета'));
		}

		//получаем данные о документе учета
		$doc_data = array();
		$query = "
			select
				du.DocumentUc_DogNum as \"DocumentUc_DogNum\",
				du.WhsDocumentUc_id as \"WhsDocumentUc_id\",
				du.DrugDocumentType_id as \"DrugDocumentType_id\",
				du.Contragent_tid as \"Contragent_tid\",
				c_tid.Org_id as \"Org_tid\",
				du.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
				COALESCE(is_acc.YesNo_Code, 0) as \"Org_tid_IsAccess\",
				du.Storage_tid as \"Storage_tid\",
				du.Contragent_sid as \"Contragent_sid\",
				c_sid.Org_id as \"Org_sid\",
				du.Storage_sid as \"Storage_sid\",
				ddt.DrugDocumentType_Code as \"DrugDocumentType_Code\"
			from
				v_DocumentUc du
				inner join v_Contragent c_tid on c_tid.Contragent_id = du.Contragent_tid
				left join v_Org o_tid on o_tid.Org_id = c_tid.Org_id
				left join v_YesNo is_acc on is_acc.YesNo_id = o_tid.Org_IsAccess
				inner join v_Contragent c_sid on c_sid.Contragent_id = du.Contragent_sid
				left join v_DrugDocumentType ddt on ddt.DrugDocumentType_id = du.DrugDocumentType_id
			where
				du.DocumentUc_id = :DocumentUc_id;
		";
		$doc_data = $this->getFirstRowFromQuery($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));
		if (count($doc_data) == 0) {
			return array(array('Error_Msg' => 'Документа учета не найден'));
		}

		//получаем строки документа учета
		$drug_arr = array();
		$query = "
			select
				dus.Drug_id as \"Drug_id\",
				dus.PrepSeries_id as \"PrepSeries_id\",
				120 as \"Okei_id\", -- 120 - Упаковка
				COALESCE(dus.DocumentUcStr_Count, 0) as \"DocumentUcStr_Count\",
				COALESCE(dus.DocumentUcStr_Price, 0) as \"DocumentUcStr_Price\",
				ds.DrugShipment_id as \"DrugShipment_id\",
				ds.WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
				wds.Org_sid as \"Org_sid\",
				(Select Storage_id from  v_OrgFarmacyIndex i where i.OrgFarmacy_id = farm.OrgFarmacy_id and i.Lpu_id = a.lpu_id limit 1) as \"Storage_id\"
			from
				v_DocumentUcStr dus
				inner join v_DocumentUc du On du.DocumentUc_id = dus.DocumentUc_id
				inner join v_Contragent c_sid on c_sid.Contragent_id = du.Contragent_sid
				left join r2.attachMoByFarmacy a on a.DocumentUcStr_id = dus.DocumentUcStr_oid
				left join OrgFarmacy farm On farm.Org_id = c_sid.Org_id
				left join lateral (
					select
						i_dsl.DrugShipment_id,
						i_ds.WhsDocumentSupply_id
					from
						v_DrugShipmentLink i_dsl
						left join v_DrugShipment i_ds on i_ds.DrugShipment_id = i_dsl.DrugShipment_id
					where
						i_dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
					limit 1
				) ds on true
				left join lateral (
					select
						WhsDocumentSupply_id,
						Org_sid
					from
						v_WhsDocumentSupply i_wds
					where
						i_wds.WhsDocumentSupply_id = ds.WhsDocumentSupply_id
					limit 1
				) wds on true
			where
				dus.DocumentUc_id = :DocumentUc_id;
		";
		$result = $this->db->query($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));
		/*
		echo getDebugSQL($query,
				 array(
	'DocumentUc_id' => $data['DocumentUc_id']
));
		exit;
		*/
		if (is_object($result)) {
			$drug_arr = $result->result('array');
		}
		if (count($drug_arr) == 0) {
			return array(array('Error_Msg' => 'Список медикаментов пуст'));
		}

		//редактируем записи в регистре
		foreach ($drug_arr as $drug) {
			$query = "
				select
					COALESCE(sum(DrugOstatRegistry_Kolvo), 0) as \"DrugOstatRegistry_Kolvo\"
				from
					v_DrugOstatRegistry 
				where
                    Contragent_id = :Contragent_id and
					Org_id = :Org_id and
					(:Storage_id is null or Storage_id = :Storage_id) and
					DrugShipment_id = :DrugShipment_id and
					Drug_id = :Drug_id and
					PrepSeries_id = :PrepSeries_id and
					DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost;  
                                        
			";
			/*
			  Заменил запрос. Подразумевается, что ИД DrugShipment_id - уникальное
			  а SubAccountType_id - субсчет: по умолчанию он доступен (равен 1)
			 */
			$query = "
				select
					COALESCE(sum(DrugOstatRegistry_Kolvo), 0) as \"DrugOstatRegistry_Kolvo\"
				from
					v_DrugOstatRegistry
				where
					DrugShipment_id = :DrugShipment_id and
					SubAccountType_id = 1
			";
			$params = array(
				'Contragent_id' => $doc_data['Contragent_sid'],
				'Org_id' => $doc_data['Org_sid'],
				'Storage_id' => $drug['Storage_id'],
				//!empty($doc_data['Contragent_sid']) ? $drug['Storage_id'] : null,
				'DrugShipment_id' => $drug['DrugShipment_id'],
				'Drug_id' => $drug['Drug_id'],
				'PrepSeries_id' => $drug['PrepSeries_id'],
				'DrugOstatRegistry_Cost' => $drug['DocumentUcStr_Price'],
			);

			$result = $this->getFirstResultFromQuery($query, $params);
			if ($result === false) {
				return array(array('Error_Msg' => 'Ошибка при получении данных регистра остатков'));
			} else if ($result <= 0 || $result < $drug['DocumentUcStr_Count'] * 1) {
				return array(array('Error_Msg' => 'В регистре остатков недостаточно медикаментов для списания'));
			}

			$query = "
				
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from exec xp_DrugOstatRegistry_count
						Contragent_id := :Contragent_id,
						Org_id := :Org_id,
						Storage_id := null, --Storage_id,
						DrugShipment_id := :DrugShipment_id,
						Drug_did := :Drug_id,
						PrepSeries_id := 1,
						SubAccountType_id := 3, -- субсчёт доступно (в пути)
						Okei_id := :Okei_id,
						DrugOstatRegistry_Kolvo := :DrugOstatRegistry_Kolvo * (-1),
						DrugOstatRegistry_Sum := :DrugOstatRegistry_Sum * (-1),
						DrugOstatRegistry_Cost := :DrugOstatRegistry_Cost,
						pmUser_id := :pmUser_id
					)
					where COALESCE(:WhsDocumentCostItemType_id, 0) <> 34 or (COALESCE(:WhsDocumentCostItemType_id, 0) = 34  and :Org_tid = 68320120724)
					
					union 
					
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Mag\"
					from xp_DrugOstatRegistry_count (
						Contragent_id := :Contragent_id,
						Org_id := :Org_id,
						Storage_id := :Storage_id,
						DrugShipment_id := :DrugShipment_id,
						Drug_did := :Drug_id,
						PrepSeries_id := 1, 
						SubAccountType_id := 1, -- субсчёт доступно
						Okei_id := :Okei_id,
						DrugOstatRegistry_Kolvo := :DrugOstatRegistry_Kolvo,
						DrugOstatRegistry_Sum := :DrugOstatRegistry_Sum,
						DrugOstatRegistry_Cost := :DrugOstatRegistry_Cost,
						pmUser_id := :pmUser_id
					);
			";
			$params = array(
				'Contragent_id' => $doc_data['Contragent_sid'],
				'Org_id' => $doc_data['Org_sid'],
				'Org_tid' => $doc_data['Org_tid'],
				'Storage_id' => $drug['Storage_id'],
				//!empty($doc_data['Contragent_sid'])  ? $drug['Storage_id'] : null,
				'DrugShipment_id' => $drug['DrugShipment_id'],
				'Drug_id' => $drug['Drug_id'],
				'PrepSeries_id' => $drug['PrepSeries_id'],
				'Okei_id' => $drug['Okei_id'],
				'DrugOstatRegistry_Kolvo' => $drug['DocumentUcStr_Count'] * (-1),
				'DrugOstatRegistry_Sum' => $drug['DocumentUcStr_Price'] * $drug['DocumentUcStr_Count'] * (-1),
				'DrugOstatRegistry_Cost' => $drug['DocumentUcStr_Price'],
				'pmUser_id' => $data['pmUser_id'],
				'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id']

			);
			//echo getDebugSQL($query, $params); exit;

			$result = $this->getFirstRowFromQuery($query, $params);

			if ($result !== false) {
				if (!empty($result['Error_Msg'])) {
					return array(array('Error_Msg' => 'Ошибка редактирования регистра остатков'));
				}
			} else {
				return array(array('Error_Msg' => 'Ошибка запроса редактирования регистра остатков'));
			}

			//если поставщик по ГК равен получателю из возвртаной накладной, то начисляем на остатки поставщика остатки
			if ($doc_data['Org_tid'] == $drug['Org_sid'] && !empty($doc_data['WhsDocumentUc_id'])) {
				//ищем подходящие партии для начисления
				$query = "
					select
						dor.DrugShipment_id as \"DrugShipment_id\"
					from
						DrugOstatRegistry dor
						left join v_DrugShipment ds on ds.DrugShipment_id = dor.DrugShipment_id
						left join v_DrugShipmentLink dsl on dsl.DrugShipment_id = dor.DrugShipment_id
					where
						dor.SubAccountType_id = 1 and
						dor.Org_id = :Org_id and
						dor.Drug_id = :Drug_id and
						dor.DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost and
						dor.Contragent_id is null and
						ds.WhsDocumentSupply_id = :WhsDocumentSupply_id and
						dsl.DrugShipmentLink_id is null
					limit 1
				";
				$sh_id = $this->getFirstResultFromQuery($query, array(
					'Org_id' => $doc_data['Org_tid'],
					'Drug_id' => $drug['Drug_id'],
					'DrugOstatRegistry_Cost' => $drug['DocumentUcStr_Price'],
					'WhsDocumentSupply_id' => $drug['WhsDocumentSupply_id']
				));
				if ($sh_id === false) {
					return array(array('Error_Msg' => 'Не удалось найти партию для начисления остатков'));
				} else if ($sh_id > 0) {
					//начисление остатков
					$query = "
						select
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from xp_DrugOstatRegistry_count (
							Contragent_id := :Contragent_id,
							Org_id := :Org_id,
							Storage_id := :Storage_id,
							DrugShipment_id := :DrugShipment_id,
							Drug_id := :Drug_id,
							PrepSeries_id := :PrepSeries_id,
							SubAccountType_id := 1, -- субсчёт доступно
							Okei_id := :Okei_id,
							DrugOstatRegistry_Kolvo := :DrugOstatRegistry_Kolvo,
							DrugOstatRegistry_Sum := :DrugOstatRegistry_Sum,
							DrugOstatRegistry_Cost := :DrugOstatRegistry_Cost,
							pmUser_id := :pmUser_id
						);
					";
					$params = array(
						'Contragent_id' => null,
						'Org_id' => $doc_data['Org_tid'],
						'Storage_id' => null,
						'DrugShipment_id' => $sh_id,
						'Drug_id' => $drug['Drug_id'],
						'PrepSeries_id' => $drug['PrepSeries_id'],
						'Okei_id' => $drug['Okei_id'],
						'DrugOstatRegistry_Kolvo' => $drug['DocumentUcStr_Count'],
						'DrugOstatRegistry_Sum' => $drug['DocumentUcStr_Price'] * $drug['DocumentUcStr_Count'],
						'DrugOstatRegistry_Cost' => $drug['DocumentUcStr_Price'],
						'pmUser_id' => $data['pmUser_id']
					);
					$result = $this->getFirstRowFromQuery($query, $params);

					if ($result !== false) {
						if (!empty($result['Error_Msg'])) {
							//echo '<pre>' . print_r($result['Error_Msg'], 1) . '</pre>';
							return array(array('Error_Msg' => 'Ошибка редактирования регистра остатков'));
						}
					} else {
						return array(array('Error_Msg' => 'Ошибка запроса редактирования регистра остатков'));
					}
				}
			}
		}

		//если получатель по возвратной накладной (расходной) имеет доступ в систему, то создаем возвратную накладную (приходную)
		if ($doc_data['Org_tid_IsAccess'] > 0) {
			$response = $this->createDocVozNakPByDocumentUc(array(
				'DocumentUc_id' => $data['DocumentUc_id'],
				'pmUser_id' => $data['pmUser_id']
			));
			if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
				return array(array('Error_Msg' => $response[0]['Error_Msg']));
			}
		}

		//return array(array('Error_Msg' => 'Отладка'));

		return array(array());
	}

	/**
	 * Загрузка списка партий для комбо (используется при редактировании спецификации документа учета)
	 * для аптек
	 */
	function farm_loadDocumentUcStrOidCombo($filter)
	{
		$where = array();
		$with = array();
		$where_sql = "";

		$where[] = 'ddt.DrugDocumentType_Code in (3, 6, 15)'; //3 - документ ввода остатков; 6 - приходная накладная; 15 - Накладная на внутреннее перемещение;
		$where[] = 'ost.cnt > 0';

		if (!empty($filter['DocumentUc_id']) && $filter['DocumentUc_id'] > 0) {
			$where[] = 'du.DocumentUc_id = :DocumentUc_id';
		}
		if (!empty($filter['Contragent_id']) && $filter['Contragent_id'] > 0) {
			$where[] = 'du.Contragent_id = :Contragent_id';
		}
		if (!empty($filter['Drug_id']) && $filter['Drug_id'] > 0) {
			$where[] = 'dus.Drug_id = :Drug_id';
		}
		if (!empty($filter['WhsDocumentUc_id']) && $filter['WhsDocumentUc_id'] > 0) {
			$where[] = 'du.WhsDocumentUc_id = :WhsDocumentUc_id';
		}
		if (!empty($filter['DrugFinance_id']) && $filter['DrugFinance_id'] > 0) {
			$where[] = 'du.DrugFinance_id = :DrugFinance_id';
		}
		if (!empty($filter['WhsDocumentCostItemType_id']) && $filter['WhsDocumentCostItemType_id'] > 0) {
			$where[] = 'du.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id';
		}

		$where_sql = join($where, ' and ');

		if (!empty($filter['DocumentUcStr_id']) && $filter['DocumentUcStr_id'] > 0) {
			$where_sql = "({$where_sql}) or dus.DocumentUcStr_id = :DocumentUcStr_id";
		}

		$q = "
			select Distinct
				dus.Drug_id as \"Drug_id\",
                d.Drug_Code as \"Drug_Code\",
				dus.DocumentUcStr_id as \"DocumentUcStr_id\",
				ost.DrugShipment_Name as \"DrugShipment_Name\",
				(
					'№ '||ost.DrugShipment_Name||
					--isnull(', '+PrepSeries_Ser, '')||
					COALESCE(', '||dus.DocumentUcStr_Ser, '')||
					COALESCE(', '||to_char(dus.DocumentUcStr_godnDate, 'dd.mm.yyyy'), '')||
					COALESCE(', '||cast(dus.DocumentUcStr_Price as varchar), '')||
					COALESCE(', '||cast(dn.DrugNds_Code as varchar), '')||
					COALESCE(', '||ltrim(rtrim(d.Drug_Name)), '')||
					COALESCE(', '||df.DrugFinance_Name, '')||
					COALESCE(', '||wdcit.WhsDocumentCostItemType_Name, '')

				) as \"DocumentUcStr_Name\",
				dus.DocumentUcStr_Price as \"DocumentUcStr_Price\",
				dus.DrugNds_id as \"DrugNds_id\",
				COALESCE(isnds.YesNo_Code, 0) as \"DocumentUcStr_IsNDS\",
				dus.DocumentUcStr_Ser as \"DocumentUcStr_Ser\",
				1 PrepSeries_id as \"PrepSeries_id\",
				to_char(dus.DocumentUcStr_godnDate, 'dd.mm.yyyy') as \"PrepSeries_GodnDate\",
				dus.DocumentUcStr_godnDate as \"DocumentUcStr_godnDate\",
				COALESCE(isdef.YesNo_Code, 0) as \"PrepSeries_isDefect\",
				dus.DocumentUcStr_CertNum as \"DocumentUcStr_CertNum\",
				dus.DocumentUcStr_CertOrg as \"DocumentUcStr_CertOrg\",
				to_char(dus.DocumentUcStr_CertDate, 'dd.mm.yyyy') as \"DocumentUcStr_CertDate\",
				to_char(dus.DocumentUcStr_CertGodnDate, 'dd.mm.yyyy') as \"DocumentUcStr_CertGodnDate\",
				dus.DrugLabResult_Name as \"DrugLabResult_Name\",
				dn.DrugNds_Code as \"DrugNds_Code\",
				cast(ost.cnt as float) as \"DocumentUcStr_OstCount\",
                du.DrugFinance_id as \"DrugFinance_id\",
				COALESCE(df.DrugFinance_Name, '') as DrugFinance_Name,
				COALESCE(wdcit.WhsDocumentCostItemType_Name, '') as \"WhsDocumentCostItemType_Name\",
                Storage_id as \"Storage_id\", COALESCE(ost.Lpu_Nick, '') as \"Lpu_Nick\", ost.lpu_id as \"lpu_id\"
			from
				v_DocumentUcStr dus 
				--inner join v_DrugShipmentLink dsl with (nolock) on dsl.DocumentUcStr_id = dus.DocumentUcStr_id
				--left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dsl.DrugShipment_id
				left join v_DocumentUc du on du.DocumentUc_id = dus.DocumentUc_id
				left join v_DrugDocumentType ddt on ddt.DrugDocumentType_id = du.DrugDocumentType_id
				left join v_Contragent con_s on con_s.Contragent_id = du.Contragent_sid
				left join  r2.AttachMoByFarmacy at  on at.DocumentUcStr_id = dus.DocumentUcStr_id
				left join v_Lpu lpu_s on lpu_s.Lpu_id = con_s.Lpu_id
				left join v_Contragent con_t on con_t.Contragent_id = du.Contragent_tid
				left join v_Lpu lpu_t on lpu_t.Lpu_id = con_t.Lpu_id
				left join v_DrugNds dn on dn.DrugNds_id = dus.DrugNds_id
				--left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dus.PrepSeries_id
				left join v_Drug d on d.Drug_id = dus.Drug_id
				left join v_YesNo isdef on isdef.YesNo_id = 0--ps.PrepSeries_isDefect
				left join v_YesNo isnds on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
				left join v_DrugFinance df on df.DrugFinance_id = du.DrugFinance_id
				left join v_WhsDocumentCostItemType wdcit on wdcit.WhsDocumentCostItemType_id = du.WhsDocumentCostItemType_id
				left join lateral (
					select (
						case
							when COALESCE(isnds.YesNo_Code, 0) = 1 then dus.DocumentUcStr_Price
							else dus.DocumentUcStr_Price*(1+(COALESCE(dn.DrugNds_Code, 0)/100.0))
						end
					) as price
				) price on true
				left join lateral (
					select
						COALESCE(sum(DrugOstatRegistry_Kolvo), 0) as cnt,
						Storage_id, l.Lpu_Nick, i.lpu_id, dor.DrugShipment_id, DrugShipment_Name
					from
						v_DrugOstatRegistry dor
						left join v_SubAccountType sat on sat.SubAccountType_id = dor.SubAccountType_id
						INNER JOIN LATERAL ( Select * from v_DrugShipmentLink dsl
							where dsl.DrugShipment_id = dor.DrugShipment_id 
								and (dsl.DocumentUcStr_id = dus.DocumentUcStr_id or dsl.DocumentUcStr_id = dus.DocumentUcStr_oid) limit 1) dsl
						left join lateral (
							Select Lpu_id
							from OrgFarmacyIndex i
							--where  isnull(i.storage_id, 0) = isnull(dor.storage_id, isnull(i.storage_id, 0)))  i
							where  i.storage_id = dor.storage_id limit 1
						) i on true
						left join v_lpu l on l.Lpu_id = i.lpu_id	
						left join v_DrugShipment ds on ds.DrugShipment_id = dor.DrugShipment_id
					where
						sat.SubAccountType_Code = 1  and
						(
							dor.Org_id is NULL or
							(ddt.DrugDocumentType_Code in (3, 6) and dor.Org_id = COALESCE(con_t.Org_id, lpu_t.Org_id)) or
							(ddt.DrugDocumentType_Code = 15 and dor.Org_id = COALESCE(con_s.Org_id, lpu_s.Org_id))
						) and
						dor.Drug_did = dus.Drug_id and
						(:Storage_id is null or dor.Storage_id = :Storage_id) and
						(dor.Contragent_id is null or :Contragent_id is null or dor.Contragent_id = :Contragent_id) and 
						--ps.PrepSeries_Ser = dus.DocumentUcStr_Ser and
						dor.DrugOstatRegistry_Cost = price.price and
						dor.DrugShipment_id = dsl.DrugShipment_id and
						dor.DrugOstatRegistry_Kolvo > 0 and
						COALESCE(i.lpu_id, 0) = COALESCE(at.lpu_id, 0)
						/*
						 and COALESCE(i.Lpu_id, 0) = case when dor.Storage_id is null and at.lpu_id is null 
							then COALESCE(i.Lpu_id, 0)else COALESCE(at.lpu_id, 0) end
						*/	
                        group by Storage_id, l.Lpu_Nick, i.lpu_id, dor.DrugShipment_id, DrugShipment_Name	
				) ost on true
			where
				" . $where_sql;

		if (count($with) > 0) {
			$q = "" . join($with, ', ') . $q;
		}

		$q = "
			Select
				Min (DocumentUcStr_id) as \"DocumentUcStr_id\",
				Drug_id as \"Drug_id\", Drug_Code as \"Drug_Code\", DrugShipment_Name as \"DrugShipment_Name\",
				DocumentUcStr_Name as \"DocumentUcStr_Name\", DocumentUcStr_Price as \"DocumentUcStr_Price\",
				DrugNds_id as \"DrugNds_id\", DocumentUcStr_IsNDS as \"DocumentUcStr_IsNDS\",
				DocumentUcStr_Ser as \"DocumentUcStr_Ser\", PrepSeries_GodnDate as \"PrepSeries_GodnDate\",
				PrepSeries_isDefect as \"PrepSeries_isDefect\", DocumentUcStr_CertNum as \"DocumentUcStr_CertNum\",
				DocumentUcStr_CertOrg as \"DocumentUcStr_CertOrg\",
				DocumentUcStr_CertDate as \"DocumentUcStr_CertDate\", DocumentUcStr_CertGodnDate as \"DocumentUcStr_CertGodnDate\",
				DocumentUcStr_godnDate as \"DocumentUcStr_godnDate\", DrugLabResult_Name as \"DrugLabResult_Name\",
				DrugNds_Code as \"DrugNds_Code\",
				DocumentUcStr_OstCount as \"DocumentUcStr_OstCount\", DrugFinance_id as \"DrugFinance_id\",
				DrugFinance_Name as \"DrugFinance_Name\", WhsDocumentCostItemType_Name as \"WhsDocumentCostItemType_Name\",
				Storage_id as \"Storage_id\",  Lpu_Nick as \"Lpu_Nick\", lpu_id as \"lpu_id\"
			from (
				{$q}
			) t
			group by
				Drug_id, Drug_Code, DrugShipment_Name, DocumentUcStr_Name, DocumentUcStr_Price, DrugNds_id, DocumentUcStr_IsNDS,
				DocumentUcStr_Ser, PrepSeries_GodnDate, PrepSeries_isDefect, DocumentUcStr_CertNum, DocumentUcStr_CertOrg,
				DocumentUcStr_CertDate, DocumentUcStr_CertGodnDate, DocumentUcStr_godnDate, DrugLabResult_Name, DrugNds_Code,
				DocumentUcStr_OstCount, DrugFinance_id, DrugFinance_Name, WhsDocumentCostItemType_Name, Storage_id,  Lpu_Nick, lpu_id
			
				order by Drug_Code, DocumentUcStr_godnDate;
			";


		//print getDebugSQL($q, $filter);exit;

		$result = $this->db->query($q, $filter);

		if (is_object($result)) {
			return $result->result('array');
		}


		return false;
	}

	/**
	 * Сохранение произвольного обьекта (без повреждения предыдущих данных) для аптек.
	 */
	function farm_saveObject($object_name, $data)
	{
		$schema = "dbo";
		//при необходимости выделяем схему из имени обьекта
		$name_arr = explode('.', $object_name);
		if (count($name_arr) > 1) {
			$schema = $name_arr[0];
			$object_name = $name_arr[1];
		}

		//echo '<pre>'. print_r ($data, 1);

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
				foreach ($result as $key => $value) {
					if (in_array($key, $params_list)) {
						$save_data[$key] = $value;
					}
				}
			}
		}

		foreach ($data as $key => $value) {
			if (in_array($key, $params_list)) {
				$save_data[$key] = $value;
			}
		}

		foreach ($save_data as $key => $value) {
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

			from {$schema}.{$proc_name} (
				{$key_field} := :{$key_field},
				{$query_part}
				pmUser_id := :pmUser_id
			);
		";
		/*
		if ($proc_name ==  'p_DocumentUcStr_ins') {
			$query .= "
			  declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_DrugShipmentLink_ins
				@DrugShipmentLink_id = @Res output,
				@DrugShipment_id = 1,
				@DocumentUcStr_id =  @{$key_field},
				@pmUser_id = {$save_data['pmUser_id']},
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as DrugShipmentLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					  ";
		}
		*/


		if ($object_name == 'DocumentUcStr' && isset($data['Lpu_id'])) {
			$query .= "
 				select
 					:{$key_field} as \"{$key_field}\",
 					Error_Code as \"Error_Code\",
 					Error_Message as \"Error_Msg\"
 				from r2.p_attachMoByFarmacy_ins (
					DocumentUcStr_id = :{$key_field},
					Lpu_id := {$data['Lpu_id']}
				);
			";
		}
		/*
		if ($object_name ==  'DocumentUc') {
		   print getDebugSQL($query, $save_data); exit;
}
		*/

		if (isset($data['debug_query'])) {
			print getDebugSQL($query, $save_data);
		}

		$result = $this->getFirstRowFromQuery($query, $save_data);


		if ($result && is_array($result)) {
			if ($result[$key_field] > 0) {
				$result['success'] = true;
			}
			return $result;
		} else {
			return array('Error_Msg' => 'При сохранении произошла ошибка');
		}
	}

	/**
	 * Корректировка регистра остатков при исполнении расходной накладной
	 */
	function farm_updateDrugOstatRegistryForDocRas($data)
	{

		if (!isset($data['DocumentUc_id'])) {
			return array(array('Error_Msg' => 'Не указан идентификатор документа учета'));
		}

		//получаем данные о документе учета
		$doc_data = array();
		$query = "
			select
				du.DocumentUc_DogNum as \"DocumentUc_DogNum\",
				du.WhsDocumentUc_id as \"WhsDocumentUc_id\",
				du.DrugDocumentType_id as \"DrugDocumentType_id\",
				du.Contragent_tid as \"Contragent_tid\",
				c_tid.Org_id as \"Org_tid\",
				du.Storage_tid as \"Storage_tid\",
				du.Contragent_sid as \"Contragent_sid\",
				c_sid.Org_id as \"Org_sid\",
				du.Storage_sid as \"Storage_sid\",
				ddt.DrugDocumentType_Code as \"DrugDocumentType_Code\"
			from
				v_DocumentUc du
				inner join v_Contragent c_tid on c_tid.Contragent_id = du.Contragent_tid
				inner join v_Contragent c_sid on c_sid.Contragent_id = du.Contragent_sid
				left join v_DrugDocumentType ddt on ddt.DrugDocumentType_id = du.DrugDocumentType_id
			where
				du.DocumentUc_id = :DocumentUc_id;
		";
		$doc_data = $this->getFirstRowFromQuery($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));
		if (count($doc_data) == 0) {
			return array(array('Error_Msg' => 'Документа учета не найден'));
		}

		//получаем строки документа учета
		$drug_arr = array();
		$query = "
			select
				dus.Drug_id as \"Drug_id\",
				dus.PrepSeries_id as \"PrepSeries_id\",
				120 as \"Okei_id\", -- 120 - Упаковка
				COALESCE(dus.DocumentUcStr_Count, 0) as \"DocumentUcStr_Count\",
				COALESCE(dus.DocumentUcStr_Price, 0) as \"DocumentUcStr_Price\",
				ds.DrugShipment_id as \"DrugShipment_id\",
				(Select Storage_id from  v_OrgFarmacyIndex i where i.OrgFarmacy_id = farm.OrgFarmacy_id and i.Lpu_id = a.lpu_id limit 1) as \"Storage_id\"
			from
				v_DocumentUcStr dus
				inner join v_DocumentUc du On du.DocumentUc_id = dus.DocumentUc_id
				inner join v_Contragent c_sid on c_sid.Contragent_id = du.Contragent_sid
				left join r2.attachMoByFarmacy a on a.DocumentUcStr_id = dus.DocumentUcStr_oid
				left join OrgFarmacy farm On farm.Org_id = c_sid.Org_id
				left join lateral (
					select
						dsl.DrugShipment_id
					from
						v_DrugShipmentLink dsl
						inner join DrugOstatRegistry ost  on ost.DrugShipment_id = dsl.DrugShipment_id
					where
						dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
					limit 1
				) ds on true
			where
				dus.DocumentUc_id = :DocumentUc_id;
		";
		/*
		echo getDebugSQL($query, array(
	'DocumentUc_id' => $data['DocumentUc_id']
)); exit;
		*/

		$result = $this->db->query($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));
		if (is_object($result)) {
			$drug_arr = $result->result('array');
		}
		if (count($drug_arr) == 0) {
			return array(array('Error_Msg' => 'Список медикаментов пуст'));
		}

		//редактируем записи в регистре
		foreach ($drug_arr as $drug) {
			$query = "
				
				select
					COALESCE(sum(DrugOstatRegistry_Kolvo), 0) as \"DrugOstatRegistry_Kolvo\"
					--10  DrugOstatRegistry_Kolvo
				from
					v_DrugOstatRegistry
				where
					Contragent_id = :Contragent_id and
					Org_id = :Org_id and
                    coalesce(Storage_id, 0) = COALESCE(:Storage_id, 0) and
					DrugShipment_id = :DrugShipment_id and
					Drug_did = :Drug_id and
					COALESCE(PrepSeries_id, 1) = COALESCE(:PrepSeries_id, 1) and
					DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost;
			";
			$params = array(
				'Contragent_id' => $doc_data['Contragent_sid'],
				'Org_id' => $doc_data['Org_sid'],
				'Storage_id' => $drug['Storage_id'],
				'DrugShipment_id' => $drug['DrugShipment_id'],
				'Drug_id' => $drug['Drug_id'],
				'PrepSeries_id' => $drug['PrepSeries_id'],
				'DrugOstatRegistry_Cost' => $drug['DocumentUcStr_Price'],
			);

			//echo getDebugSQL($query, $params); //exit;

			$result = $this->getFirstResultFromQuery($query, $params);

			if ($result === false) {
				return array(array('Error_Msg' => 'Ошибка при получении данных регистра остатков'));
			} else if ($result <= 0 || $result < $drug['DocumentUcStr_Count'] * 1) {
				return array(array('Error_Msg' => 'В регистре остатков недостаточно медикаментов для списания?'));
			}

			$query = "
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
					
				from xp_DrugOstatRegistry_count (
					Contragent_id := :Contragent_id,
					Org_id := :Org_id,
					Storage_id := :Storage_id,
					DrugShipment_id := :DrugShipment_id,
					--@Drug_id = 
                    Drug_did := :Drug_id,
					PrepSeries_id := 1,
					SubAccountType_id := 1, -- субсчёт доступно
					Okei_id := :Okei_id,
					DrugOstatRegistry_Kolvo := :DrugOstatRegistry_Kolvo,
					DrugOstatRegistry_Sum := :DrugOstatRegistry_Sum,
					DrugOstatRegistry_Cost := :DrugOstatRegistry_Cost,
					pmUser_id := :pmUser_id
				);
			";
			$params = array(
				'Contragent_id' => $doc_data['Contragent_sid'],
				'Org_id' => $doc_data['Org_sid'],
				//'Storage_id' => !empty($doc_data['Contragent_sid']) ? $doc_data['Storage_sid'] : null,
				// Если в документе склад не значится - то берем склад из записи препарата
				'Storage_id' => !empty($doc_data['Storage_sid']) ? $doc_data['Storage_sid'] : $drug['Storage_id'],
				'DrugShipment_id' => $drug['DrugShipment_id'],
				'Drug_id' => $drug['Drug_id'],
				'PrepSeries_id' => $drug['PrepSeries_id'],
				'Okei_id' => $drug['Okei_id'],
				'DrugOstatRegistry_Kolvo' => $drug['DocumentUcStr_Count'] * (-1),
				'DrugOstatRegistry_Sum' => $drug['DocumentUcStr_Price'] * $drug['DocumentUcStr_Count'] * (-1),
				'DrugOstatRegistry_Cost' => $drug['DocumentUcStr_Price'],
				'pmUser_id' => $data['pmUser_id']
			);
			//echo getDebugSQL($query, $params); exit;

			$result = $this->getFirstRowFromQuery($query, $params);

			if ($result !== false) {
				if (!empty($result['Error_Msg'])) {
					return array(array('Error_Msg' => 'Ошибка редактирования регистра остатков'));
				}
			} else {
				return array(array('Error_Msg' => 'Ошибка запроса редактирования регистра остатков'));
			}
		}

		return array(array());
	}

	/**
	 * Сохранение партии связанной со строкой документа учета
	 */
	function farm_saveLinkedDrugShipment($data)
	{
		$q = "
			select
				DrugShipment_id as \"DrugShipment_id\"
			from
				v_DrugShipmentLink
			where
				DocumentUcStr_id = :DocumentUcStr_id
			order by
				DrugShipmentLink_id
			limit 1;
		";

		$r = $this->getFirstResultFromQuery($q, $data);
		if ($r > 0) {
			$r = $this->farm_saveObject('DrugShipment', array(
				'DrugShipment_id' => $r,
				'DrugShipment_Name' => $data['DrugShipment_Name'],
				'pmUser_id' => $data['pmUser_id']
			));
		} else {
			$q = "
				select
					DrugShipment_id as \"DrugShipment_id\"
				from
					v_DrugShipment
				where
					DrugShipment_Name = :DrugShipment_Name
					and DrugShipment_Name <> 'ЛС'
				order by
					DrugShipment_id
				limit 1;
			";

			$r = $this->getFirstResultFromQuery($q, $data);
			if ($r > 0) {
				$r = $this->farm_saveObject('DrugShipmentLink', array(
					'DrugShipment_id' => $r,
					'DocumentUcStr_id' => $data['DocumentUcStr_id'],
					'pmUser_id' => $data['pmUser_id']
				));
			} else {
				if ($data['DrugShipment_Name'] == 'ЛС' || !$data['DrugShipment_Name']) {
					//сохранение партии
					$ds_data = $this->farm_generateDrugShipmentName();
					if (!empty($ds_data[0]) && !empty($ds_data[0]['DrugShipment_Name'])) {
						$data['DrugShipment_Name'] = $ds_data[0]['DrugShipment_Name'];
					}
				}
				$r = $this->farm_saveObject('DrugShipment', array(
					'DrugShipment_setDT' => date('Y-m-j G:i:s'),
					'DrugShipment_Name' => $data['DrugShipment_Name'],
					'DocumentUcStr_id' => $data['DocumentUcStr_id'],
					'pmUser_id' => $data['pmUser_id']
				));
				if (is_array($r) && isset($r['DrugShipment_id'])) {
					$r = $this->farm_saveObject('DrugShipmentLink', array(
						'DrugShipment_id' => $r['DrugShipment_id'],
						'DocumentUcStr_id' => $data['DocumentUcStr_id'],
						'pmUser_id' => $data['pmUser_id']
					));

				}
			}
		}

	}

	/**
	 * Удаление строки документа.
	 */
	function farm_deleteDocumentUcStr($data)
	{
		$this->load->model("EmergencyTeam_model4E", "et_model");

		$this->db->trans_begin();

		//Удаляем запись из укладки бригады СМП, если существует
		$etdpm_result = $this->et_model->deleteEmergencyTeamPackMoveByDocumentUcStr(array(
			'DocumentUcStr_id' => $data['DocumentUcStr_id']
		));
		if (isset($etdpm_result[0]) && !empty($etdpm_result[0]['Error_Msg'])) {
			$this->db->trans_rollback();
			return array('Error_Message' => $etdpm_result[0]['Error_Msg']);
		}

		//проверка на использование данной строки в качестве партии в другом документе учета
		$query = "
			select
				count(dus.DocumentUcStr_id) as \"cnt\"
			from
				v_DocumentUcStr dus
			where
				dus.DocumentUcStr_oid = :DocumentUcStr_id;
		";
		$result = $this->getFirstRowFromQuery($query, $data);
		if ($result && $result['cnt'] == 0) {
			//удаление партии
			$sh_arr = array();
			$query = "
				select
					dsl.DrugShipmentLink_id as \"DrugShipmentLink_id\",
					dsl.DrugShipment_id as \"DrugShipment_id\",
					dor.cnt as \"DrugOstatRegistry_Cnt\"
				from
					v_DrugShipmentLink dsl
					left join lateral (
						select
							count(dor.DrugOstatRegistry_id) as cnt
						from
							v_DrugOstatRegistry dor
						where
							dor.DrugShipment_id = dsl.DrugShipment_id
					) dor on true
				where
					dsl.DocumentUcStr_id = :DocumentUcStr_id
					--  Партии сквозные, поэтому  текущая партия
					--  может быть в других документоах
					and Not exists (SElect 1 from v_DrugShipmentLink ln
						where ln.DrugShipment_id = dsl.DrugShipment_id
						and ln.DocumentUcStr_id <> dsl.DocumentUcStr_id)
				order by
					dor.cnt desc;
			";
			//print(getDebugSQL($query, $data)); die;  //Обговорить с Рустамом
			$result = $this->queryResult($query, $data);
			if ($this->isSuccessful($result)) {
				$sh_arr = $result;
			}

			foreach ($sh_arr as $sh) {
				//echo (' удаление партии 0');
				if ($sh['DrugOstatRegistry_Cnt'] == 0) {
					//echo ('удаление партии 1');
					$response = $this->deleteObject('DrugShipmentLink', $sh);
					if (!empty($response['Error_Message'])) {
						$this->db->trans_rollback();
						return $response;
					}
					//echo ('удаление партии 3');
					$response = $this->deleteObject('DrugShipment', $sh);
					if (!empty($response['Error_Message'])) {
						$this->db->trans_rollback();
						return $response;
					}
				} else {
					$this->db->trans_rollback();
					return array('Error_Message' => 'Удаление строки документа невозможно, так как связанная партия уже используется');
				}
			}

			//Удалим связь с DrugShipmentLink
			$queryShipmentLink = "
				select DrugShipmentLink_id as \"DrugShipmentLink_id\"
				from v_DrugShipmentLink
				where DocumentUcStr_id = :DocumentUcStr_id
			";

			$resultShipmentLink = $this->db->query($queryShipmentLink, $data);
			if (is_object($resultShipmentLink)) {
				$response = $resultShipmentLink->result('array');
				//var_dump($resultShipmentLink->result('array'));die;
				if (is_array($response) && count($response) > 0) {
					$DrugShipmentLink_id = $response[0]['DrugShipmentLink_id'];
					$response = $this->deleteDrugShipLink($DrugShipmentLink_id);
				}
			}


			$response = $this->deleteObject('DocumentUcStr', $data);

			if (!$this->isSuccessful($response)) {
				$this->db->trans_rollback();
			}

			$this->db->trans_commit();
			return $response;

		} else {
			$this->db->trans_rollback();
			return array('Error_Message' => 'Удаление строки документа невозможно, так как она используется в другом документе');
		}
	}

	/**
	 * Проверка на формирование инв. ведомостей. Используется при исполнении документов учета.
	 */
	function checkInventExists($data)
	{
		$res = array();

		$query = "
			select
				count(wdui.WhsDocumentUcInvent_id) as \"cnt\"
			from
				dbo.v_WhsDocumentUcInvent wdui
				left join dbo.v_WhsDocumentStatusType i_wdst on i_wdst.WhsDocumentStatusType_id = wdui.WhsDocumentStatusType_id
				left join v_WhsDocumentUc i_ord on i_ord.WhsDocumentUc_id = wdui.WhsDocumentUc_pid
				left join dbo.v_WhsDocumentStatusType wdst on wdst.WhsDocumentStatusType_id = i_ord.WhsDocumentStatusType_id
				left join lateral (
					select
						count(WhsDocumentUcInvent_id) as cnt
					from
						v_WhsDocumentUcInventDrug wduid
					where
						wduid.WhsDocumentUcInvent_id = wdui.WhsDocumentUcInvent_id
				) as drug_list on true
				left join dbo.DocumentUc du  on du.DocumentUc_id = :DocumentUc_id
			where 
				wdst.WhsDocumentStatusType_Code = 2 and
				i_wdst.WhsDocumentStatusType_Code != 2 and
				wdui.Org_id = :Org_id and
				wdui.WhsDocumentUc_Date <= COALESCE(du.DocumentUc_setDate, wdui.WhsDocumentUc_Date + 1) and
				drug_list.cnt = 0;
		";

		/*
		echo getDebugSQL($query, array(
			'Org_id' => $data['Org_id'],
			'DocumentUc_id' => $data['DocumentUc_id']
			)); exit;
		*/


		$result = $this->getFirstRowFromQuery($query, array(
			'Org_id' => $data['Org_id'],
			'DocumentUc_id' => $data['DocumentUc_id']
		));


		if ($result && $result['cnt'] > 0) {
			$res['Error_Msg'] = "Для текущей организации не сформированы инвентаризационные ведомости. Исполнение невозможно.";
		}

		return $res;
	}

	/**
	 * Удаление строки документа.
	 */
	function farm_delete($data)
	{
		$error = array(); //для сбора ошибок
		$str_arr = array();

		//получение статуса документа
		$query = "
			select
				dds.DrugDocumentStatus_Code as \"DrugDocumentStatus_Code\"
			from
				v_DocumentUc du
				left join v_DrugDocumentStatus dds on dds.DrugDocumentStatus_id = du.DrugDocumentStatus_id
			where
				du.DocumentUc_id = :DocumentUc_id;
		";
		$result = $this->getFirstResultFromQuery($query, array(
			'DocumentUc_id' => $data['id']
		));
		if ($result > 0) {
			if ($result == 4) {
				$error[] = "Удаление невозможно, так как документ исполнен.";
			} else if ($result == 10) {
				$error[] = "Удаление невозможно, так как документ исполнен, затем отменен.";
			}
		} else {
			$error[] = "Не удалось получить статус документа.";
		}

		if (count($error) == 0) {
			//получение списка строк документа учета
			$query = "
				select
					dus.DocumentUcStr_id as \"DocumentUcStr_id\"
				from
					v_DocumentUcStr dus
				where
					dus.DocumentUc_id = :DocumentUc_id;
			";
			$result = $this->db->query($query, array(
				'DocumentUc_id' => $data['id']
			));
			if (is_object($result)) {
				$str_arr = $result->result('array');
			}

			$this->beginTransaction();

			foreach ($str_arr as $str) {
				$response = $this->deleteDocumentUcStr($str);
				if (!empty($response['Error_Message'])) {
					$error[] = $response['Error_Message'];
					break;
				}
			}

			if (count($error) == 0) {
				$response = $this->deleteObject('DocumentUc', array(
					'DocumentUc_id' => $data['id']
				));
				if (!empty($response['Error_Message'])) {
					$error[] = $response['Error_Message'];
				}
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
	 * Получение сгенерированного наименования партии
	 */
	function farm_generateDrugShipmentName()
	{
		$q = "
			with t as (
				Select
					DrugShipment_Name n1,
					replace(DrugShipment_Name, 'ЛС.', '') DrugShipment_Name
				from DrugShipment
				where DrugShipment_Name ilike 'ЛС.%'
			)

			select
				'ЛС.' || cast((COALESCE(max(cast(DrugShipment_Name as bigint)),0) + 1) to varchar)  as \"DrugShipment_Name\"
			from
				t
			where
				DrugShipment_Name not ilike '%.%' and
				DrugShipment_Name not ilike '%,%' and
				DrugShipment_Name not ilike '%e%' and
				length(DrugShipment_Name) <= 18 and
				(DrugShipment_Name ~ '^([0-9]+[.]?[0-9]*|[.][0-9]+)$')
		";

		$result = $this->db->query($q);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение данных об остатках медикаментов
	 * @param type $data
	 */
	protected function farm_getDrugOstatRegistryData($data)
	{

		$rules = array(
			array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'default' => null, 'type' => 'id'),
			array('field' => 'Drug_id', 'label' => 'Идентификатор ЛС', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'WhsDocumentSupply_id', 'label' => 'Идентификатор договора поставок', 'type' => 'id', 'default' => null),
			array('field' => 'PrepSeries_id', 'label' => 'Идентификатор справочника серий выпуска ЛС', 'type' => 'id', 'default' => null),
			array('field' => 'DocumentUcStr_Price', 'label' => 'Стоимость', 'type' => 'float', 'default' => null),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams || !empty($err))
			return $err;

		$query = "
			select
				dor.Contragent_id as \"Contragent_id\",
				dor.Org_id as \"Org_id\",
				dor.Storage_id as \"Storage_id\",
				dor.DrugShipment_id as \"DrugShipment_id\",
				dor.Drug_id as \"Drug_id\",
				dor.PrepSeries_id as \"PrepSeries_id\",
				dor.Okei_id as \"Okei_id\",
				dor.DrugOstatRegistry_Kolvo as \"DrugOstatRegistry_Kolvo\",
				dor.DrugOstatRegistry_Sum as \"DrugOstatRegistry_Sum\",
				dor.DrugOstatRegistry_Cost as \"DrugOstatRegistry_Cost\",
				dor.SubAccountType_id as \"SubAccountType_id\"
			from
				v_DrugOstatRegistry dor
				left join v_SubAccountType sat on sat.SubAccountType_id = dor.SubAccountType_id
				left join v_DrugShipment ds on ds.DrugShipment_id = dor.DrugShipment_id
			where
				dor.Org_id = :Org_id and
				dor.Drug_id = :Drug_id and
				dor.SubAccountType_id = :SubAccountType_id and
				dor.DrugOstatRegistry_Kolvo > 0 and
				(:WhsDocumentSupply_id is null or ds.WhsDocumentSupply_id = :WhsDocumentSupply_id) and
				(:PrepSeries_id is null or dor.PrepSeries_id = :PrepSeries_id) and
				(:DocumentUcStr_Price is null or dor.DrugOstatRegistry_Cost = :DocumentUcStr_Price);
		";
		//echo getDebugSQL($query, $queryParams);
		return $this->queryResult($query, $queryParams);
	}


}