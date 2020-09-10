<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'models/_pgsql/DocumentUc_model.php');

class Pskov_DocumentUc_model extends DocumentUc_model {


	/**
	 * Корректировка регистра остатков при исполнении приходной накладной
	 */
	public function updateDrugOstatRegistryForDokNak($data) {
		if ( !isset( $data[ 'DocumentUc_id' ] ) ) {
			return array( array( 'Error_Msg' => 'Не указан идентификатор документа учета' ) ) ;
		}

		//
		// Получаем данные о документе учета
		//
		
		$doc_data = $this->_getDocumentUcData( $data ) ;

		if ( !$this->isSuccessful( $doc_data ) ) {
			return $doc_data ;
		}

		if ( count( $doc_data ) == 0 || empty( $doc_data[ 0 ] ) ) {
			return array( array( 'Error_Msg' => 'Документа учета не найден' ) ) ;
		}

		$doc_data = $doc_data[ 0 ] ;

		//
		// Получаем данные о гк
		//
		
		$sup_data = $this->_getWhsDocumentSupply( array(
			'WhsDocumentUc_id' => $doc_data[ 'WhsDocumentUc_id' ] ,
			'WhsDocumentUc_Num' => $doc_data[ 'DocumentUc_DogNum' ]
				) ) ;

		if ( !$this->isSuccessful( $sup_data ) ) {
			return $sup_data ;
		}

		$sup_data = (!empty( $sup_data[ 0 ] )) ? $sup_data[ 0 ] : array( ) ;

		//
		// Получаем строки документа учета
		//
		
		$drug_arr = $this->_getDocumentUcStr( array(
			'DocumentUc_id' => $data[ 'DocumentUc_id' ] ,
			'WhsDocumentSupply_id' => isset( $sup_data[ 'WhsDocumentSupply_id' ] ) ? $sup_data[ 'WhsDocumentSupply_id' ] : null ,
			'Org_sid' => isset( $sup_data[ 'Org_sid' ] ) ? $sup_data[ 'Org_sid' ] : null ,
			'Org_rid' => isset( $sup_data[ 'Org_rid' ] ) ? $sup_data[ 'Org_rid' ] : null
		) ) ;

		if ( !$this->isSuccessful( $drug_arr ) ) {
			return $drug_arr ;
		}

		if ( count( $drug_arr ) == 0 ) {
			return array( array( 'Error_Msg' => 'Список медикаментов пуст' ) ) ;
		}

		foreach ( $drug_arr as $drug ) {
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
			if ( !isset( $title_doc_cnt ) || count( $sup_data ) < 1 ) {
				$query = "
					select
						count(wdt.WhsDocumentTitle_id) as \"cnt\"
					from
						v_WhsDocumentTitle wdt 
						left join v_WhsDocumentTitleType wdtt  on wdtt.WhsDocumentTitleType_id = wdt.WhsDocumentTitleType_id
						left join v_WhsDocumentRightRecipient wdrr  on wdrr.WhsDocumentTitle_id = wdt.WhsDocumentTitle_id
					where
						wdt.WhsDocumentUc_id = :WhsDocumentSupply_id and
						wdtt.WhsDocumentTitleType_Code = '3' and --Приложение к ГК: список пунктов отпуска
						Org_id = :Org_id
				" ;
				$title_doc_cnt = $this->getFirstResultFromQuery( $query , array(
					'Org_id' => $doc_data[ 'Org_tid' ] ,
					'WhsDocumentSupply_id' => $drug[ 'WhsDocumentSupply_id' ]
						) ) ;
			}

			//создаем записи в регистре
			$result = $result = $this->_updateDrugOstatRegistry( array(
				'Contragent_id' => $doc_data[ 'Contragent_tid' ] ,
				'Org_id' => $doc_data[ 'Org_tid' ] ,
				'Storage_id' => $doc_data[ 'Storage_tid' ] ,
				'DrugShipment_id' => $drug[ 'DrugShipment_id' ] ,
				'Drug_id' => $drug[ 'Drug_id' ] ,
				'SubAccountType_id' => 1 , // -- субсчёт доступно
				'PrepSeries_id' => $drug[ 'PrepSeries_id' ] ,
				'Okei_id' => $drug[ 'Okei_id' ] ,
				'DrugOstatRegistry_Kolvo' => $drug[ 'DocumentUcStr_Count' ] ,
				'DrugOstatRegistry_Sum' => $drug[ 'DocumentUcStr_Price' ] * $drug[ 'DocumentUcStr_Count' ] ,
				'DrugOstatRegistry_Cost' => $drug[ 'DocumentUcStr_Price' ] ,
				'pmUser_id' => $data[ 'pmUser_id' ]
			) ) ;
			
			if ( !$this->isSuccessful( $result ) ) {
				return $this->createError( 0 , 'Ошибка создания регистра остатков' ) ;
			}
		}

		return array( array( ) ) ;
	}
	
	/**
	 * Загрузка списка медикаментов для комбо (используется при редактировании спецификации документа учета)
	 */
	/*
	public function loadDrugComboForDocumentUcStr($filter) {
		$where = array();
		$with = array();
		$join = array();

		if ($filter['Drug_id'] > 0) {
			$where[] = 'd.Drug_id = :Drug_id';
		} else {
			if (!empty($filter['DrugNomen_Code'])) {
				$where[] = 'dn.DrugNomen_Code = :DrugNomen_Code';
			}
			if ($filter['WhsDocumentUc_id'] > 0) {
				$query = "
					select
						count(Drug_id) as cnt
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
							Drug_id
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
						Drug_id
					from
						v_DocumentUcStr 

					where
						DocumentUc_id = :DocumentUc_id
				)';
			}
			if (strlen($filter['query']) > 0) {
				$filter['query'] = '%'.preg_replace('/ /', '%', $filter['query']).'%';
				$where[] = 'd.Drug_Name iLIKE :query';

			}
			if ((!empty($filter['Storage_id']) && $filter['Storage_id'] > 0) || (!empty($filter['Contragent_id']) && $filter['Contragent_id'] > 0)) {
				$with[] = " ostat_cnt as (
					select top 100
						dor.Drug_id,
						COALESCE(sum(DrugOstatRegistry_Kolvo), 0) as cnt

					from
						v_DrugOstatRegistry dor 

						left join v_SubAccountType sat  on sat.SubAccountType_id = dor.SubAccountType_id

					where
						dor.DrugOstatRegistry_Kolvo > 0 and
						sat.SubAccountType_Code = 1 and
						(:Storage_id is null or dor.Storage_id = :Storage_id) and
						(:Contragent_id is null or dor.Contragent_id = :Contragent_id) and
						(COALESCE(dor.DrugFinance_id,0)=0 or :DrugFinance_id is null or dor.DrugFinance_id = :DrugFinance_id) and

						(COALESCE(dor.WhsDocumentCostItemType_id,0)=0 or :WhsDocumentCostItemType_id is null or dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id)

					group by
						dor.Drug_id
				)";
				$join[] = "left join ostat_cnt on ostat_cnt.Drug_id = d.Drug_id";
				$where[] = 'ostat_cnt.cnt > 0';
			}
		}

		if (count($where) > 0 || count($with) > 0) {
			$q = "
				select top 1000
					d.Drug_id,
					d.Drug_Name,
					dn.DrugNomen_Code,
					d.Drug_Fas,
					rtrim(COALESCE(d.DrugForm_Name, '')) as DrugForm_Name,

					rtrim(COALESCE(d.Drug_PackName, '')) as DrugUnit_Name

				from
					rls.v_Drug d 

					LEFT JOIN LATERAL (

						select top 1
							v_DrugNomen.DrugNomen_Code
						from
							rls.v_DrugNomen 

						where
							v_DrugNomen.Drug_id = d.Drug_id
						order by
							DrugNomen_id
					) dn
					".join($join, ' ')."
				where
					".join($where, ' and ')."
				order by
					d.Drug_Name;
			";

			if (count($with) > 0) {
				$q = "with ".join($with, ', ').$q;
			}

			//print getDebugSQL($q, $filter);exit;
			$result = $this->db->query($q, $filter);
			if ( is_object($result) ) {
				return $result->result('array');
			}
		}

		return false;
	}
	*/
}