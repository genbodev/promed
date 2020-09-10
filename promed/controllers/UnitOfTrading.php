<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* TODO: complete explanation, preamble and describing
* Контроллер для работы с лотами
*/

class UnitOfTrading extends swController{
	/**
	 * Конструктор.
	 */
	function __construct() {
		parent::__construct();
		
		$this->inputRules = array(
			'loadUnitOfTradingList' => array(
				array('field' => 'DrugRequest_id', 'label' => 'сводная заявка', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => '', 'type' => 'id' ),
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Статья расхода', 'rules' => '', 'type' => 'id' ),
				array('field' => 'BudgetFormType_id', 'label' => 'Целевая статья', 'rules' => '', 'type' => 'id' ),
				array('field' => 'WhsDocumentPurchType_id', 'label' => 'Тип закупа', 'rules' => '', 'type' => 'id' ),
				array('field' => 'FinanceSource_id', 'label' => 'Источник оплаты', 'rules' => '', 'type' => 'id' ),
				array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0 ),
				array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 50 )
			),
			'saveUnitOfTrading' => array(
				array('field' => 'WhsDocumentUc_id', 'label' => 'Идентификатор документа', 'rules' => '', 'type' => 'id' ),
				array('field' => 'WhsDocumentUc_pid', 'label' => 'Идентификатор родитнльского документа', 'rules' => '', 'type' => 'id' ),
				array('field' => 'WhsDocumentUc_Num', 'label' => 'Номер документа', 'rules' => 'required', 'type' => 'string' ),
				array('field' => 'WhsDocumentUc_Name', 'label' => 'Наименование документа', 'rules' => 'required', 'type' => 'string' ),
				array('field' => 'WhsDocumentType_id', 'label' => 'Тип документа', 'rules' => '', 'type' => 'id', 'default' => 5 ),
				//array('field' => 'Contragent_sid', 'label' => 'Контрагент - источник', 'rules' => '', 'type' => 'id' ),
				//array('field' => 'Mol_sid', 'label' => 'МОЛ источник', 'rules' => '', 'type' => 'id' ),
				//array('field' => 'Contragent_tid', 'label' => 'Контрагент - приемник', 'rules' => '', 'type' => 'id' ),
				//array('field' => 'Mol_tid', 'label' => 'МОЛ приемник', 'rules' => '', 'type' => 'id' ),
				array('field' => 'WhsDocumentUc_Sum', 'label' => 'Сумма документа', 'rules' => '', 'type' => 'float' ),
				array('field' => 'WhsDocumentStatusType_id', 'label' => 'Статус документа', 'rules' => '', 'type' => 'id', 'default' => 1 ),
				array('field' => 'WhsDocumentUcStatusType_id', 'label' => 'Статус лота', 'rules' => '', 'type' => 'id' ),
				array('field' => 'PurchObjType_id', 'label' => 'Объект закупки', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Статья расходов', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'BudgetFormType_id', 'label' => 'Целевая статья', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'WhsDocumentPurchType_id', 'label' => 'Вид закупа', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'WhsDocumentProcurementRequest_setDate', 'label' => 'Срок действия контракта', 'rules' => 'required', 'type' => 'date' ),
				//array('field' => 'DrugRequestPurchaseSpec_id', 'label' => 'Медикамент', 'rules' => '', 'type' => 'id' ),
				array('field' => 'Org_aid', 'label' => 'Организация', 'rules' => '', 'type' => 'id' )
			),
			'deleteUnitOfTrading' => array(
				array('field' => 'WhsDocumentProcurementRequest_id', 'label' => 'Идентификатор лота', 'rules' => 'required', 'type' => 'id' )
			),
			'loadDrugList' => array(
				array('field' => 'DrugRequest_id', 'label' => 'сводная заявка', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'paging', 'label' => 'пэйджинг', 'rules' => '', 'type' => 'int', 'default' => 0 ),
				array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0 ),
				array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 50 )
			),
			'loadDrugListOnUnitOfTrading' => array(
				array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0 ),
				array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 50 ),
				//array('field' => 'WhsDocumentProcurementRequest_id', 'label' => 'Идентификатор лота', 'rules' => 'required', 'type' => 'id' )
				
				array('field' => 'DrugRequest_id', 'label' => 'Идентификатор сводной заявки', 'rules' => '', 'type' => 'id' ),
				array('field' => 'WhsDocumentUc_id', 'label' => 'Идентификатор лота', 'rules' => 'required', 'type' => 'id' )
			),
			'addDrugListInUnitOfTrading' => array(
				array('field' => 'WhsDocumentUc_id', 'label' => 'Идентификатор лота', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'DrugList', 'label' => 'Список медикаментов', 'rules' => 'required', 'type' => 'string' )
			),
			'deleteDrugListInUnitOfTrading' => array(
				//array('field' => 'WhsDocumentProcurementRequest_id', 'label' => 'Идентификатор лота', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'WhsDocumentUc_id', 'label' => 'Идентификатор лота', 'rules' => 'required', 'type' => 'id' ),
				
				array('field' => 'DrugList', 'label' => 'Список медикаментов', 'rules' => 'required', 'type' => 'string' ),
				array('field' => 'del_uot', 'label' => 'Удалять лот', 'rules' => '', 'type' => 'int', 'default' => 0 )
			),
			'loadDrugRequest' => array(
				array('field' => 'begDate', 'label' => 'Начало периода', 'rules' => 'required', 'type' => 'date' ),
				array('field' => 'endDate', 'label' => 'Окончание периода', 'rules' => 'required', 'type' => 'date' )
			),
			'exportUnitOfTrading' => array(
				array('field' => 'WhsDocumentProcurementRequest_id', 'label' => 'Идентификатор лота', 'rules' => 'required', 'type' => 'id' )
			),
			'printUnitOfTradingSpec' => array(
				array('field' => 'WhsDocumentProcurementRequest_id', 'label' => 'Идентификатор лота', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'format', 'label' => 'формат печати (html, csv)', 'rules' => 'required', 'type' => 'string' )
			),
			'signUnitOfTrading' => array(
				array('field' => 'UotList', 'label' => 'Список лотов', 'rules' => 'required', 'type' => 'string' )
			),
			'unsignUnitOfTrading' => array(
				array('field' => 'UotList', 'label' => 'Список лотов', 'rules' => 'required', 'type' => 'string' )
			),
			'moveDrugsInOtherUnitOfTrading' => array(
				array('field' => 'DrugList', 'label' => 'Список медикаментов', 'rules' => 'required', 'type' => 'string' ),
				array('field' => 'WhsDocumentUc_id', 'label' => 'Идентификатор лота', 'rules' => 'required', 'type' => 'id' )
			),
			'mergeUnitOfTradings' => array(
				array('field' => 'UotList', 'label' => 'Список лотов', 'rules' => 'required', 'type' => 'string' )
			),
			'formationUnitOfTrading' => array(
				array('field' => 'DrugRequest_id', 'label' => 'Идентификатор сводной заявки', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'PersonRegisterType_id', 'label' => 'Идентификатор типа регистра', 'rules' => '', 'type' => 'id' ),
				array('field' => 'Org_aid', 'label' => 'Организация', 'rules' => '', 'type' => 'id' ),
				array('field' => 'PurchObjType_id', 'label' => 'Объект закупки', 'rules' => '', 'type' => 'id' ),
				array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => '', 'type' => 'id' ),
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Статья расходов', 'rules' => '', 'type' => 'id' ),
				array('field' => 'BudgetFormType_id', 'label' => 'Целевая статья', 'rules' => '', 'type' => 'id' ),
				array('field' => 'WhsDocumentPurchType_id', 'label' => 'Вид закупа', 'rules' => '', 'type' => 'id' ),
				array('field' => 'WhsDocumentProcurementRequest_setDate', 'label' => 'Срок действия контракта', 'rules' => '', 'type' => 'string' )
			),
			'reformationUnitOfTrading' => array(
				array('field' => 'DrugRequest_id', 'label' => 'Идентификатор сводной заявки', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'PersonRegisterType_id', 'label' => 'Идентификатор типа регистра', 'rules' => '', 'type' => 'id' ),
				array('field' => 'Org_aid', 'label' => 'Организация', 'rules' => '', 'type' => 'id' ),
				array('field' => 'PurchObjType_id', 'label' => 'Объект закупки', 'rules' => '', 'type' => 'id' ),
				array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => '', 'type' => 'id' ),
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Статья расходов', 'rules' => '', 'type' => 'id' ),
				array('field' => 'BudgetFormType_id', 'label' => 'Целевая статья', 'rules' => '', 'type' => 'id' ),
				array('field' => 'WhsDocumentPurchType_id', 'label' => 'Вид закупа', 'rules' => '', 'type' => 'id' ),
				array('field' => 'WhsDocumentProcurementRequest_setDate', 'label' => 'Срок действия контракта', 'rules' => '', 'type' => 'string' )
			),
			'getWhsDocumentSupplyByUotId' => array(
				array('field' => 'UotList', 'label' => 'Список лотов', 'rules' => 'required', 'type' => 'string' )
			),
            'copyLots' => array(

            ),
            'getPrevDrugRequestPeriod' => array(
                array(
                    'field' => 'DrugRequest_id', 
                    'label' => 'Идентификатор сводной заявки', 
                    'rules' => 'required', 
                    'type' => 'id' 
                ),
                array(
                    'field' => 'DrugFinance_id', 
                    'label' => 'Источник финансирования', 
                    'rules' => 'required', 
                    'type' => 'id' 
                ),
                array(
                    'field' => 'WhsDocumentCostItemType_id', 
                    'label' => 'Тип льготы', 
                    'rules' => 'required', 
                    'type' => 'id' 
                ),                                
            ),
            'getUnitsOfTrading'=>array(
                array(
                    'field' => 'WhsDocumentUc_id', 
                    'label' => 'Идентификатор лота', 
                    'rules' => 'required', 
                    'type' => 'id' 
                ),            
            ),
            'getDrugRequestPurchaseSpesCurDrugRequest'=>array(
                array(
                    'field' => 'DrugRequest_id', 
                    'label' => 'Идентификатор сводной заявки', 
                    'rules' => 'required', 
                    'type' => 'id' 
                ),                
            ),
            'printLotRational'=>array(
                array(
                    'field' => 'lot_id', 
                    'label' => 'Идентификатор лота', 
                    'rules' => '', 
                    'type' => 'id' 
                ),                
            ),
            /*
            'getDrugsOfDrugRequestForFormationCopy' => array(
                array(
                    'field' => 'DrugRequest_id', 
                    'label' => 'Идентификатор сводной заявки', 
                    'rules' => 'required', 
                    'type' => 'id' 
                ),                             
            ) 
            */ 
            'saveUnitOfTradingDocsData' => array(
				array('field' => 'WhsDocumentUc_id', 'label' => 'Идентификатор документа', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'WhsDocumentProcurementRequestSpecDop_id', 'label' => 'Идентификатор документа', 'rules' => '', 'type' => 'id' ),
				array('field' => 'Okved_id', 'label' => 'Код ОКВЭД', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'Okpd_id', 'label' => 'Код ОКПД', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'WhsDocumentProcurementRequestSpecDop_CodeKOSGU', 'label' => 'Код КОСГУ', 'rules' => 'required', 'type' => 'int' ),
				array('field' => 'WhsDocumentProcurementRequestSpecDop_Count', 'label' => 'Количество разнарядок', 'rules' => 'required', 'type' => 'int' ),
				array('field' => 'SupplyPlaceType_id', 'label' => 'Место поставки товара', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'ProvSizeType_id', 'label' => 'Размер обеспечения', 'rules' => 'required', 'type' => 'id' )
			),
			'getBudgetFormType'=>array(
                array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => '', 'type' => 'id' ),
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Статья расходов', 'rules' => '', 'type' => 'id' )                
            ),
            'saveDrugOfUnitOfTrading' => array(
				array('field' => 'WhsDocumentProcurementRequestSpec_id', 'label' => 'Идентификатор документа', 'rules' => '', 'type' => 'id' ),
				array('field' => 'WhsDocumentProcurementRequestSpec_Kolvo', 'label' => 'Количество уп.', 'rules' => 'required', 'type' => 'int' ),
				array('field' => 'WhsDocumentProcurementRequestSpec_Name', 'label' => 'Наименование товара', 'rules' => 'required', 'type' => 'string' ),
				array('field' => 'GoodsUnit_id', 'label' => 'Ед.изм. товара', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'Okei_id', 'label' => 'Идентификатор ед.изм. по справочнику ОКЕИ', 'rules' => '', 'type' => 'id' ),
				array('field' => 'WhsDocumentProcurementRequestSpec_Count', 'label' => 'Кол-во товара в упаковке', 'rules' => '', 'type' => 'int' )
			),
			'calcDrugUnitQuant'=>array(
                array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'GoodsUnit_id', 'label' => 'Идентификатор ед.изм.', 'rules' => 'required', 'type' => 'id' )                
            ),
            'copyUnitOfTradingDocsData'=>array(
                array('field' => 'WhsDocumentUc_id', 'label' => 'Идентификатор документа', 'rules' => 'required', 'type' => 'id' )                
            ),
            'loadOkpdCombo' => array(
                array('field' => 'Okpd_id', 'label' => 'ОКПД', 'rules' => '', 'type' => 'id'),
                array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
            ),
            'getOrgNick' => array(
                array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => 'required', 'type' => 'id')
            ),
			'loadOrgCombo' => array(
				array(
					'field' => 'Org_id',
					'label' => 'Идентификатор организации',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'UserOrg_id',
					'label' => 'Идентификатор организации пользователя',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'query',
					'label' => 'Строка контекстного поиска',
					'rules' => '',
					'type'  => 'string'
				)
			),
            'getGoodsUnitByDrugComplexMnn' => array(
                array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор МНН', 'rules' => '', 'type' => 'id'),
                array('field' => 'GoodsUnit_id', 'label' => 'Идентификатор единицы измерения', 'rules' => '', 'type' => 'id')
            )
		);
		 
		$this->load->database();
		$this->load->model('UnitOfTrading_model', 'dbmodel');
	}
	
    /**
     *  Копирование лотов из предыдущего периода - устаревшая функция
     */ 
    /*function copyLots(){
        
        $lots = $this->getPrevDrugRequestPeriod();
        
        $data = $lots['data'];
        
        $this->dbmodel->beginTransaction();
        
        foreach($lots['lots'] as $k=>$lot){

			$response = $this->dbmodel->saveUnitOfTrading(array(
				'WhsDocumentUc_id' => null
				,'WhsDocumentUc_Num' => $lot['WhsDocumentUc_Num']
				,'WhsDocumentUc_Name' => $lot['WhsDocumentUc_Name']
				,'WhsDocumentType_id' => 5
				,'Contragent_sid' => null
				,'Mol_sid' => null
				,'Contragent_tid' => null
				,'Mol_tid' => null
				,'DrugFinance_id' => $lot['DrugFinance_id']
				,'WhsDocumentCostItemType_id' => $lot['WhsDocumentCostItemType_id']
				,'pmUser_id' => $data['pmUser_id']
			));

             
			if( $response === false || ( isset($response[0]) && strlen($response[0]['Error_Msg']) > 0 ) ) {
				$this->dbmodel->rollbackTransaction();
				DieWithError("Произошла ошибка при сохранении лота!");
			}
            
            //список медикаментов из предыдущий заявки по конкретному лоту
            $drugs = $this->dbmodel->getUnitsOfTrading(array('WhsDocumentUc_id'=>$lot['WhsDocumentUc_id']));
            //список медикаментов по текужей сводной заявке (все))
            $curDrugs = $this->getDrugRequestPurchaseSpesCurDrugRequest();
            
            //Перебор всех ЛС лота из предыдущего периода
			foreach($drugs as $drug) {
                //Поиск ЛС по DrugComplexMnn_id ЛС из текущей сводной заявке
                foreach($curDrugs as $curdrug){
                    //Добавим ЛС в новый лот
                    if($curdrug['DrugComplexMnn_id'] == $drug['DrugComplexMnn_id']){
        				$this->dbmodel->addDrugInUnitOfTrading(array(
        					'DrugRequestPurchaseSpec_id' => $curdrug['DrugRequestPurchaseSpec_id']
        					,'WhsDocumentUc_id' => $response[0]['WhsDocumentUc_id']
        				));                    
                    }
                }
			}

        }
        
        $this->dbmodel->commitTransaction();
        
    }*/
    
    /**
     *  Возвращает медикаменты по текущей заявке при копировании лотов
     */  
    function getDrugRequestPurchaseSpesCurDrugRequest(){
		$data = $this->ProcessInputData('getDrugRequestPurchaseSpesCurDrugRequest', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getDrugRequestPurchaseSpesCurDrugRequest($data);
        return $response;        
     }   
    
    /**
     *  Получение данных по предыдущему рабочему периоду
     */ 
  
    function getPrevDrugRequestPeriod(){
		$data = $this->ProcessInputData('getPrevDrugRequestPeriod', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getPrevDrugRequestPeriod($data);
        return array('lots'=>$response, 'data'=>$data);
    } 

    
	/**
	 *	Читает список лотов
	 */
	function loadUnitOfTradingList() {
		$data = $this->ProcessInputData('loadUnitOfTradingList', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadUnitOfTradingList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 *	Сохранение лота
	 */
	function saveUnitOfTrading() {
		$data = $this->ProcessInputData('saveUnitOfTrading', true);
		if ($data === false) { return false; }
		
		$this->dbmodel->beginTransaction();
		$response = $this->dbmodel->saveUnitOfTrading($data);
		if( !is_array($response) || strlen($response[0]['Error_Msg']) > 0 ) {
			return;
		}
		if(empty($data['WhsDocumentUc_id'])){
			$defaultDocsData = array(
				'WhsDocumentUc_id' => $response[0]['WhsDocumentUc_id'],
				'WhsDocumentProcurementRequestSpecDop_id' => null,
				'Okved_id' => 1396, // Значение по умолчанию - 51.46.1
				'Okpd_id' => 3303, // Значение по умолчанию - 24.42.21.155
				'WhsDocumentProcurementRequestSpecDop_CodeKOSGU' => 4,
				'WhsDocumentProcurementRequestSpecDop_Count' => null,
				'SupplyPlaceType_id' => 1,
				'ProvSizeType_id' => 1,
				'pmUser_id' => $data['pmUser_id']
			);
			$docsData = $this->dbmodel->saveUnitOfTradingDocsData($defaultDocsData);
		}
		/*if( !empty($data['DrugRequestPurchaseSpec_id']) ) {
			try {
				$res = $this->dbmodel->addDrugInUnitOfTrading(array(
					'DrugRequestPurchaseSpec_id' => $data['DrugRequestPurchaseSpec_id'],
					'WhsDocumentUc_id' => $response[0]['WhsDocumentUc_id'],
					'pmUser_id' => $data['pmUser_id']
				));
			} catch (Exception $e) {
				DieWithError('Невозможно сохранить медикамент, нет связи rls.DrugComplexMnn и rls.Drug<br />');
			}
			if( !is_array($res) || ( isset($res[0]['Error_Msg']) && strlen($res[0]['Error_Msg']) > 0 ) ) {
				$this->dbmodel->rollbackTransaction();
			}
		}*/
		$this->dbmodel->commitTransaction();
		$this->ProcessModelSave($response, true)->ReturnData();
	}
	
	/**
	 *	Удаление лота
	 */
	function deleteUnitOfTrading() {
		$data = $this->ProcessInputData('deleteUnitOfTrading', true);
		if ($data === false) { return false; }
		
		if( $this->dbmodel->isSignedUnitOfTrading(array('WhsDocumentUc_id' => $data['WhsDocumentProcurementRequest_id'])) ) {
			DieWithError("Нельзя удалить подписанный лот!");
		}
		// Медикаменты лота
		$drugs = $this->dbmodel->getDrugsOfUnitOfTrading(array('WhsDocumentUc_id' => $data['WhsDocumentProcurementRequest_id']));
		$this->dbmodel->beginTransaction();
		foreach($drugs as $drug_id) {
			try {
				$res = $this->dbmodel->deleteDrugOfUnitOfTrading(array( 'WhsDocumentProcurementRequestSpec_id' => $drug_id ));
			} catch (Exception $e) {
				DieWithError('Не удалось удалить медикамент!');
			}
			if( !is_array($res) || ( isset($res[0]['Error_Msg']) && strlen($res[0]['Error_Msg']) > 0 ) ) {
				$this->dbmodel->rollbackTransaction();
			}
		}
		$response = $this->dbmodel->deleteUnitOfTrading($data);
		$this->dbmodel->commitTransaction();
		$this->ProcessModelSave($response, true)->ReturnData();
	}
	
	/**
	 *	Читает список медикаментов сводной заявки у которых разница между количеством к закупу и количеством, включенным в лоты (в упаковках), больше нуля 
	 */
	function loadDrugList() {
		$data = $this->ProcessInputData('loadDrugList', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadDrugList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}
	
	/**
	 *	Читает список медикаментов лота
	 */
	function loadDrugListOnUnitOfTrading() {
		$data = $this->ProcessInputData('loadDrugListOnUnitOfTrading', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadDrugListOnUnitOfTrading($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}
	
	/**
	 *	Добавляет медикаменты в лот
	 */
	function addDrugListInUnitOfTrading() {
		$data = $this->ProcessInputData('addDrugListInUnitOfTrading', true);
		if ($data === false) { return false; }

		$sp = getSessionParams();
		$data['Lpu_id'] = isset($sp['Lpu_id']) ? $sp['Lpu_id'] : null;
		
		$data['DrugList'] = explode("|", urldecode($data['DrugList']));
		
		$this->dbmodel->beginTransaction();
		foreach($data['DrugList'] as $drug_id) {
			try {
				$response = $this->dbmodel->addDrugInUnitOfTrading(array(
					'DrugRequestPurchaseSpec_id' => $drug_id,
					'WhsDocumentUc_id' => $data['WhsDocumentUc_id'],
					'pmUser_id' => $data['pmUser_id'],
					'Lpu_id' => $data['Lpu_id']
				));
			} catch (Exception $e) {
				//DieWithError('Невозможно сохранить медикамент, нет связи rls.DrugComplexMnn и rls.Drug<br />');
				DieWithError($e);
			}
			if($response === false) {
				$this->dbmodel->rollbackTransaction();
			}
		}
		$this->dbmodel->commitTransaction();
		$this->ReturnData(array('success' => true));
	}
	
	/**
	 *	Удаляет медикаменты из лота
	 */
	function deleteDrugListInUnitOfTrading() {
		$data = $this->ProcessInputData('deleteDrugListInUnitOfTrading', true);
		if ($data === false) { return false; }
		
		$data['DrugList'] = explode("|", urldecode($data['DrugList']));
		$this->dbmodel->beginTransaction();
		foreach($data['DrugList'] as $drug_id) {
			$response = $this->dbmodel->deleteDrugOfUnitOfTrading(array(
				'WhsDocumentProcurementRequestSpec_id' => $drug_id,
				//'WhsDocumentUc_id' => $data['WhsDocumentUc_id'],
				//'pmUser_id' => $data['pmUser_id']
			));
    		if($response === false) {
    			$this->dbmodel->rollbackTransaction();
			}
		}
		// если надо удалить и сам лот
		if( !empty($data['del_uot']) ) {
			$res = $this->dbmodel->deleteUnitOfTrading(array(
				'WhsDocumentProcurementRequest_id' => $data['WhsDocumentUc_id'],
				'pmUser_id' => $data['pmUser_id']
			));
			if( !is_array($res) || strlen($res[0]['Error_Message']) > 0 ) {
				$this->dbmodel->rollbackTransaction();
			}
		}
		$this->dbmodel->commitTransaction();
		$this->ReturnData(array('success' => true));
	}

	/**
	 *	Сохранение изменений по медикаменту в лоте с формы редактирования медикамента
	 */
	function saveDrugOfUnitOfTrading() {
		$data = $this->ProcessInputData('saveDrugOfUnitOfTrading', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->saveDrugOfUnitOfTrading($data);
		if( !is_array($response) || strlen($response[0]['Error_Msg']) > 0 ) {
			return false;
		}
		
		$this->ProcessModelSave($response, true)->ReturnData();
	}
	
	/**
	 *	Загружает данные о сводных заявках на закуп
	 */
	function loadDrugRequest() {
		$data = $this->ProcessInputData('loadDrugRequest', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadDrugRequest($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 *	Определение первоначальной доступности экшнов в зависимости от настроек
	 */
	function defineActionsVisible() {
		$sp = getSessionParams();
		$settings = unserialize($sp['session']['settings']);
		
		$actions = array('form', 'reform', 'copy', 'merge', 'sign', 'sign_all', 'export', 'unsign','request_purchase','marker_research');
		
		if( isset($settings['drugpurchase']) ) {
			$options = $settings['drugpurchase'];
			
			if( $options['drugpurchase_rules_formation_lots'] == 1 ) {
				unset($actions[array_search('form', $actions)]);
				unset($actions[array_search('reform', $actions)]);
			}
			if( $options['drugpurchase_auto_reconfig_uot'] != 1 && (array_search('reform', $actions) !== false)) {
				unset($actions[array_search('reform', $actions)]);
			}
			
			if( $options['drugpurchase_requirements_for_signing_uots'] != 1 ) {
				unset($actions[array_search('sign', $actions)]);
				unset($actions[array_search('sign_all', $actions)]);
			}
		}
		
		if( !isSuperadmin() && !isMinZdrav() ) {
			unset($actions[array_search('unsign', $actions)]);
		}
		//Обновим индексы массива, иначе, при отсутствии одного из индексов, на форме вместо массива создастся объект
		$actions = array_values($actions);
		$this->ReturnData(array('success' => true, 'actions' => $actions));
	}
	
	/**
	 *	Экспорт лота в csv
	 */
	function exportUnitOfTrading() {
		$data = $this->ProcessInputData('exportUnitOfTrading', true);
		if ($data === false) { return false; }
		
		$uotData = $this->dbmodel->getUnitOfTradingForExport($data);
		if( !is_array($uotData) || !isset($uotData[0]) ) {
			DieWithError("Ошибка БД!");
		}
		$drugData = $this->dbmodel->getDrugListForExport($data);
		if( !is_array($drugData) ) {
			DieWithError("Ошибка БД!");
		}

		//Проставлем ведущие нули у нулевых выражений
		foreach ($uotData[0] as $key => &$value) {
			if (is_string($value) && $value[0] == '.') {
				$value = '0' . $value;
			}
		}

		foreach ($drugData[0] as $key2 => &$value2) {
			if (is_string($value2) && $value2[0] == '.') {
				$value2 = '0' . $value2;
			}
		}

		set_time_limit(0);
		/*$cols = array(
			array( "U_NUM", "C", 10, 0 ),
			array( "U_NAME", "C", 10, 0 ),
			array( "U_SUM", "C", 10, 0 ),
			array( "U_DATE", "C", 10, 0 )
		);
		*/
		
		if( !is_dir(EXPORTPATH_UOT) ) {
			if ( !mkdir(EXPORTPATH_UOT) ) {
				DieWithError("Ошибка при создании директории ".EXPORTPATH_UOT."!");
			}
		}
		$u_name = "uot_data";
		$file_name = EXPORTPATH_UOT . $u_name . ".csv";
		$archive_name = EXPORTPATH_UOT . $u_name . ".zip";
		if( is_file($archive_name) ) {
			unlink($archive_name);
		}
		
		try {
			/*$h = dbase_create( $file_name, $cols );
			if( !$h ) {
				throw new Exception("Ошибка при создании БД dbase!");
			}
			foreach ($uotData as $row) {
				array_walk($row, 'ConvertFromWin1251ToCp866');
				dbase_add_record($h, array_values($row));
			}
			dbase_close($h);
			
			$zip = new ZipArchive();
			$zip->open($archive_name, ZIPARCHIVE::CREATE);
			$zip->AddFile($file_name, basename($file_name));
			$zip->close();
			unlink($file_name);
			*/
			
			// Заголовки полей лота
			$cols_uot = array("№ лота", "Наименование лота", "Сумма лота", "Дата изменения");
			// Заголовки полей медикамента
			$cols_drug = array("№ п/п", "Наименование (компл.МНН или торговое)", "Ед.измерения", "Кол-во", "Цена", "Сумма");		
			
			$h = fopen($file_name, 'w');
			$result = "Лот:\n";
			$result .= implode(";", $cols_uot)."\n";
			foreach ($uotData as $row) {
				$result .= implode(";", array_values($row))."\n";
			}
			
			$result .= "\nСписок медикаментов:\n";
			$result .= implode(";", $cols_drug)."\n";
			
			foreach($drugData as $k=>$drug) {
				$result .= ++$k.";".implode(";", array_values($drug))."\n";
			}
			ConvertFromUTF8ToWin1251($result, null, true);
			fwrite($h, $result);
			fclose($h);
			
			$zip = new ZipArchive();
			$zip->open($archive_name, ZIPARCHIVE::CREATE);
			$zip->AddFile($file_name, basename($file_name));
			$zip->close();
			unlink($file_name);
			
			$this->ReturnData(array('success' => true, 'url' => $archive_name));
		} catch (Exception $e) {
			DieWithError($e->getMessage());
			$this->ReturnData(array('success' => false));
		}
	}
	
	/**
	 *	Печать спецификации лота
	 */
	function printUnitOfTradingSpec() {
		$data = $this->ProcessInputData('printUnitOfTradingSpec', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getDrugListForPrint($data);
		if( !is_array($response) || count($response) == 0 ) {
			return false;
		}

		$sub_contract_text = "«Обеспечение  лекарственными средствами «группа»  (поставка и отпуск) граждан, имеющих право на лекарственное обеспечение в соответствии с законом Саратовской области от 01.08.2005г №74-ЗСО в <подстановка года> году»";
		if (!empty($response[0]['WhsDocumentCostItemType_SubContract'])) {
			$sub_contract_text = $response[0]['WhsDocumentCostItemType_SubContract'];
		}
		$sub_contract_text = preg_replace(array(
			"/<подстановка группы>/",
			"/<подстановка года>/"
		), array(
			$response[0]['Uot_Name'],
			$response[0]['Year']
		), $sub_contract_text);

		switch( strToLower($data['format']) ) {
			case 'html':
				$this->load->library('parser');
				$view = 'uot_spec_list';
				
				$this->parser->parse($view, array(
					'uot_spec_template_title' => 'Печать спецификации',
					'results' => $response,
					'SubContractText' => $sub_contract_text,
					'Uot_Num' => $response[0]['Uot_Num']
				));
				break;
			case 'csv':
				$file_name = EXPORTPATH_UOT . "print_uot.csv";
				if( is_file($file_name) ) {
					@unlink($file_name);
				}
				$h = fopen($file_name, 'w');
				if( !$h ) {
					DieWithError("Ошибка при попытке открыть файл!");
				}
				$str_result = "";
				$str_result .= ";;Спецификация № {$response[0]['Uot_Num']}\n";
				$str_result .= "\n";
				$str_result .= "АУКЦИОН №;;«{$sub_contract_text}»\n";
				$str_result .= "№;мнн;форма выпуска;тн;Цена;Количество упаковок;Сумма\n";
				foreach( $response as $row ) {
					$str_result .= $row['Spec_Num'].";".$row['Spec_Name'].";".$row['Spec_Drugform'].";".$row['Spec_TNName'].";".$row['Spec_PriceMax'].";".$row['Spec_Kolvo'].";".$row['Spec_Sum']."\n";
				}
				$str_result .= "\nНачальник отдела организации лекарственного обеспечения ___________________________________";
				ConvertFromUTF8ToWin1251($str_result, null, true);
				fwrite($h, $str_result);
				fclose($h);
				$this->ReturnData(array('success' => true, 'url' => $file_name));
				break;
			default:
				return false;
				break;
		}
	}
	
	/**
	 *	Подписание лотов
	 */
	function signUnitOfTrading() {
		$data = $this->ProcessInputData('signUnitOfTrading', true);
		if ($data === false) { return false; }
		
		$data['UotList'] = explode("|", urldecode($data['UotList']));
		//print_r($data); die();
		
		$this->dbmodel->beginTransaction();
		Foreach($data['UotList'] as $uot_id ) {
			// если лот не подписан!
			if( !$this->dbmodel->isSignedUnitOfTrading(array('WhsDocumentUc_id' => $uot_id)) ) {
				try {
					$this->dbmodel->setSumForUnitOfTrading(array('WhsDocumentUc_id' => $uot_id,'pmUser_id' => $data['pmUser_id']));
					$response = $this->dbmodel->signUnitOfTrading(array(
						'WhsDocumentUc_id' => $uot_id,
						'pmUser_id' => $data['pmUser_id']
					));
				} catch( Exception $e ) {
					DieWithError($e->getMessage());
				}
			}
		}
		$this->dbmodel->commitTransaction();
		$this->ReturnData(array('success' => true));
	}
	
	/**
	 *	Отмена подписания лотов
	 */
	function unsignUnitOfTrading() {
		$data = $this->ProcessInputData('unsignUnitOfTrading', true);
		if ($data === false) { return false; }
		
		$data['UotList'] = explode("|", urldecode($data['UotList']));
		//print_r($data); die();
		
		$this->dbmodel->beginTransaction();
		Foreach($data['UotList'] as $uot_id ) {
			// если лот подписан!
			if( $this->dbmodel->isSignedUnitOfTrading(array('WhsDocumentUc_id' => $uot_id)) ) {
				try {
					$response = $this->dbmodel->unsignUnitOfTrading(array(
						'WhsDocumentUc_id' => $uot_id,
						'pmUser_id' => $data['pmUser_id']
					));
				} catch( Exception $e ) {
					DieWithError($e->getMessage());
				}
			}
		}
		$this->dbmodel->commitTransaction();
		$this->ReturnData(array('success' => true));
	}
	
	/**
	 *	Перемещает медикаменты в другой лот
	 */
	function moveDrugsInOtherUnitOfTrading() {
		$data = $this->ProcessInputData('moveDrugsInOtherUnitOfTrading', true);
		if ($data === false) { return false; }
	
		if( $this->dbmodel->isSignedUnitOfTrading($data) ) {
			DieWithError("Нельзя перемещать медикаменты в подписанный лот!");
		}
		$data['DrugList'] = explode("|", urldecode($data['DrugList']));
		
		$this->dbmodel->beginTransaction();
		
		// найдем ид лота которому в настоящее время принадлежат медикаменты
		$oldUotId = $this->dbmodel->getUnitOfTradingIdByDrugId(array(
			'WhsDocumentProcurementRequestSpec_id' => $data['DrugList'][0]
		));
		if( $oldUotId === false ) {
			DieWithError("Ошибка БД!");
		}
		$sp = getSessionParams();
		if(!isset($data['Lpu_id']))
			$data['Lpu_id'] = isset($sp['Lpu_id']) ? $sp['Lpu_id'] : null;
		foreach($data['DrugList'] as $drug_id) {
			$response = $this->dbmodel->moveDrugToUnitOfTrading(array(
				'WhsDocumentProcurementRequestSpec_id' => $drug_id
				,'WhsDocumentProcurementRequest_id' => $data['WhsDocumentUc_id']
				,'Lpu_id' => $data['Lpu_id']
				,'pmUser_id' => $data['pmUser_id']
			));
			if( !is_array($response) || ( isset($response[0]) && strlen($response[0]['Error_Msg']) > 0 ) ) {
				DieWithError("Ошибка БД!");
				$this->dbmodel->rollbackTransaction();
			}
		}
		// если в лоте не осталось медикаментов, то удаляем этот лот
		if($this->dbmodel->getCountDrugsOnUnitOfTrading(array( 'WhsDocumentUc_id' => $oldUotId )) == 0 ) {
			$this->dbmodel->deleteUnitOfTrading(array(
				'WhsDocumentProcurementRequest_id' => $oldUotId,
				'pmUser_id' => $data['pmUser_id']
			));
		}
		$this->dbmodel->commitTransaction();
		$this->ReturnData(array('success' => true));
	}
	
	/**
	 *	Объединяет лоты
	 */
	function mergeUnitOfTradings() {
		$data = $this->ProcessInputData('mergeUnitOfTradings', true);
		if ($data === false) { return false; }
		
		$data['UotList'] = explode("|", urldecode($data['UotList']));
		$headUot_id = $data['UotList'][0]; // Ид.лота, в который включим медикаменты объединяемых лотов
		array_shift($data['UotList']);
		$drugs = array();
		
		foreach($data['UotList'] as $uot_id) {
			$ds = $this->dbmodel->getDrugsOfUnitOfTrading(array(
				'WhsDocumentUc_id' => $uot_id
			));
			if( $ds !== false ) {
				$drugs = array_merge($drugs, $ds);
			}
		}
		//print_r($drugs);
		$this->dbmodel->beginTransaction();
		foreach($drugs as $drug_id) {
			$response = $this->dbmodel->saveDrugOfUnitOfTrading(array(
				'WhsDocumentProcurementRequestSpec_id' => $drug_id,
				'WhsDocumentProcurementRequest_id' => $headUot_id,
				'pmUser_id' => $data['pmUser_id']
			),true);
			if( $response === false || ( isset($response[0]) && strlen($response[0]['Error_Msg']) > 0 ) ) {
				$this->dbmodel->rollbackTransaction();
			}
		}
		
		// Удаляем опустевшие лоты
		foreach($data['UotList'] as $uot_id) {
			$response = $this->dbmodel->deleteUnitOfTrading(array(
				'WhsDocumentProcurementRequest_id' => $uot_id,
				'pmUser_id' => $data['pmUser_id']
			));
			if( $response === false || ( isset($response[0]) && strlen($response[0]['Error_Message']) > 0 ) ) {
				$this->dbmodel->rollbackTransaction();
			}
		}
		
		$this->dbmodel->commitTransaction();
		$this->ReturnData(array('success' => true));
	}
	
	/**
	 *	Формирование лотов
	 */
	function formationUnitOfTrading($inData = null) {
		if( isset($inData) && !empty($inData['DrugRequest_id']) ) {
			$data = array_merge($inData, getSessionParams());
		} else {
			$data = $this->ProcessInputData('formationUnitOfTrading', true);
			if ($data === false) { return false; }
		}
		$sp = getSessionParams();
		if(!isset($data['Lpu_id']))
			$data['Lpu_id'] = isset($sp['Lpu_id']) ? $sp['Lpu_id'] : null;
		//print_r($data); die();
		
		$settings = unserialize($data['session']['settings']);
        
		if( isset($settings['drugpurchase']) ) {
			$options = $settings['drugpurchase'];
		} else {
			// по дефолту
			$options = array(
				'drugpurchase_rules_formation_lots' => 1,
				'drugpurchase_grouping' => 'atc',
				'drugpurchase_atc_code_count_symbols' => 3,
				'drugpurchase_narc_psych_drugs' => 0,
				'drugpurchase_each_drug_listed_tradename' => 0,
				'drugpurchase_used_to_solve_vk' => 0,
				'drugpurchase_sum_than' => 0,
				'drugpurchase_sum_than_value' => 0,
				'drugpurchase_auto_reconfig_uot' => 0,
				'drugpurchase_inc_in_uot_tradename_when_used_in_request' => 0,
				'drugpurchase_select_uot_with_single_producer' => 0,
				'drugpurchase_requirements_for_signing_uots' => 0,
				'drugpurchase_allow_signing_uots' => 0
			);
		}

		$defaultDocsData = array(
			'WhsDocumentUc_id' => null,
			'WhsDocumentProcurementRequestSpecDop_id' => null,
			'Okved_id' => 1396, // Значение по умолчанию - 51.46.1
			'Okpd_id' => 3303, // Значение по умолчанию - 24.42.21.155
			'WhsDocumentProcurementRequestSpecDop_CodeKOSGU' => 4,
			'WhsDocumentProcurementRequestSpecDop_Count' => null,
			'SupplyPlaceType_id' => 1,
			'ProvSizeType_id' => 1,
			'pmUser_id' => $data['pmUser_id']
		);

		$drugs = $this->dbmodel->getDrugsOfDrugRequestForFormation(array(
			'DrugRequest_id' => $data['DrugRequest_id'],
			'sum' => !empty($options['drugpurchase_sum_than_value']) ? $options['drugpurchase_sum_than_value'] : 0
		));

		$CostItemTypeList = $this->dbmodel->getCostItemTypeList(array(
			'PersonRegisterType_id' => $data['PersonRegisterType_id']
		));

		if( !is_array($drugs) ) {
			return false;
		}
		if( count($drugs) == 0 ) {
			DieWithError("В сводной заявке нет медикаментов для которых могут быть сформированы лоты!");
		}

		$uot_num = 1;
		
		$this->dbmodel->beginTransaction();
		// если выбрано "автоматическое формирование лотов"
		if( $options['drugpurchase_rules_formation_lots'] == 2 ) {
			foreach($drugs as $k=>$drug) {
				if(
					( $options['drugpurchase_sum_than'] == 1 && $drug['isSUM'] == 1 )
					|| ( $options['drugpurchase_each_drug_listed_tradename'] == 1 && $drug['isTN'] == 1 )
					|| ( $options['drugpurchase_used_to_solve_vk'] == 1 && $drug['isVK'] == 1 )
					|| ( $options['drugpurchase_narc_psych_drugs'] == 1 && $drug['isNARC'] == 1 )
				) {
					// создаем отдельный лот для этого медикамента
					$response = $this->dbmodel->saveUnitOfTrading(array(
						'WhsDocumentUc_id' => null
						,'WhsDocumentUc_Num' => $uot_num++//($k+1)
						,'WhsDocumentUc_Name' => $drug['Drug_Name']
						,'WhsDocumentUc_Sum' => null
						,'WhsDocumentType_id' => 5
						,'WhsDocumentStatusType_id' => 1
						,'WhsDocumentUcStatusType_id' => null
						,'Org_aid' => $data['Org_aid']
						,'DrugFinance_id' => $data['DrugFinance_id']
						,'WhsDocumentCostItemType_id' => isset($data['WhsDocumentCostItemType_id'])?$data['WhsDocumentCostItemType_id'] : 15 // 15 - не определена
						,'PurchObjType_id' => $data['PurchObjType_id']
						,'BudgetFormType_id' => $data['BudgetFormType_id']
						,'WhsDocumentPurchType_id' => $data['WhsDocumentPurchType_id']
						,'WhsDocumentProcurementRequest_setDate' => $data['WhsDocumentProcurementRequest_setDate']
						,'pmUser_id' => $data['pmUser_id']
					));
					
					if( $response === false || ( isset($response[0]) && strlen($response[0]['Error_Msg']) > 0 ) ) {
						$this->dbmodel->rollbackTransaction();
						DieWithError("Произошла ошибка при сохранении лота!");
					}
					// Сохраняем данные для документации для созданного лота
					$docsDataForSave = $defaultDocsData;
					$docsDataForSave['WhsDocumentUc_id'] = $response[0]['WhsDocumentUc_id'];
					$docsData = $this->dbmodel->saveUnitOfTradingDocsData($docsDataForSave);
					// Добавляем медикамент в лот
					$this->dbmodel->addDrugInUnitOfTrading(array(
						'DrugRequestPurchaseSpec_id' => $drug['DrugRequestPurchaseSpec_id']
						,'WhsDocumentUc_id' => $response[0]['WhsDocumentUc_id']
						,'Lpu_id' => $data['Lpu_id']
						,'pmUser_id' => $data['pmUser_id']
					));
					$this->dbmodel->setSumForUnitOfTrading(array('WhsDocumentUc_id' => $response[0]['WhsDocumentUc_id'],'pmUser_id' => $data['pmUser_id']));
					unset($drugs[$k]);
				}
			}

			$groups = array();
			$s_groups = array();
			$others = array();
			$count_symbols = $options['drugpurchase_atc_code_count_symbols'];
			$id_array = array();

			if( count($drugs) > 0 ) {
				foreach($drugs as $k=>$drug) {
					$finance_id = $drug['DrugFinance_id'];
					$costitemtype_id = isset($CostItemTypeList[$drug['DrugFinance_id']]) ? $CostItemTypeList[$drug['DrugFinance_id']] : 1;

					if ($options['drugpurchase_select_uot_with_single_producer'] == 1 && $drug['firm_count'] == 1) {
						$s_groups[] = array(
							'group_name' => $drug['Drug_Name'],
							'finance_id' => $finance_id,
							'costitemtype_id' => $costitemtype_id,
							'drugs' => array($drug['DrugRequestPurchaseSpec_id'])
						);
						$id_array[] = $drug['DrugRequestPurchaseSpec_id'];
					} else {
						if( $options['drugpurchase_grouping'] == 'atc' && $options['drugpurchase_atc_code_count_symbols'] > 0 && !empty($drug['Atc_Name']) ) {
							if (!in_array($drug['DrugRequestPurchaseSpec_id'], $id_array)) {
								$reference = substr($drug['Atc_Name'], 0, $count_symbols);
								$group_name = $this->dbmodel->getClsAtcName($reference);

								$gp = array(
									'group_name' => $group_name, //drug['Atc_Name'],
									'finance_id' => $finance_id,
									'costitemtype_id' => $costitemtype_id,
									'drugs' => array($drug['DrugRequestPurchaseSpec_id'])
								);

								$id_array[] = $drug['DrugRequestPurchaseSpec_id'];
								foreach($drugs as $key=>$val) {
									if (!in_array($val['DrugRequestPurchaseSpec_id'], $id_array)) {
										if( !empty($val['Atc_Name']) && substr($val['Atc_Name'], 0, $count_symbols) == $reference && $val['DrugFinance_id'] == $finance_id ) {
											$gp['drugs'][] = $val['DrugRequestPurchaseSpec_id'];
											$id_array[] = $val['DrugRequestPurchaseSpec_id'];
										}
									}
								}
								$groups[] = $gp;
							}
						} elseif( $options['drugpurchase_grouping'] == 'farm' && !empty($drug['Pharmagroup_id']) ) {
							if (!in_array($drug['DrugRequestPurchaseSpec_id'], $id_array)) {
								$gp = array(
									'group_name' => $drug['Pharmagroup_Name'],
									'drugs' => array($drug['DrugRequestPurchaseSpec_id']),
									'finance_id' => $finance_id,
									'costitemtype_id' => $costitemtype_id
								);
								$phgr_id = $drug['Pharmagroup_id'];

								$id_array[] = $drug['DrugRequestPurchaseSpec_id'];
								foreach($drugs as $key=>$val) {
									if (!in_array($val['DrugRequestPurchaseSpec_id'], $id_array)) {
										if(!empty($val['Pharmagroup_id']) && $val['Pharmagroup_id'] == $phgr_id) {
											$gp['drugs'][] = $val['DrugRequestPurchaseSpec_id'];
											$id_array[] = $val['DrugRequestPurchaseSpec_id'];
										}
									}
								}
								$groups[] = $gp;
							}
						} elseif( $options['drugpurchase_grouping'] == 'mnn' && !empty($drug['Actmatters_id']) ) {
							if (!in_array($drug['DrugRequestPurchaseSpec_id'], $id_array)) {
								$gp = array(
									'group_name' => $drug['Actmatters_Name'],
									'drugs' => array($drug['DrugRequestPurchaseSpec_id']),
									'finance_id' => $finance_id,
									'costitemtype_id' => $costitemtype_id
								);
								$phgr_id = $drug['Actmatters_id'];

								$id_array[] = $drug['DrugRequestPurchaseSpec_id'];
								foreach($drugs as $key=>$val) {
									if (!in_array($val['DrugRequestPurchaseSpec_id'], $id_array)) {
										if(!empty($val['Actmatters_id']) && $val['Actmatters_id'] == $phgr_id) {
											$gp['drugs'][] = $val['DrugRequestPurchaseSpec_id'];
											$id_array[] = $val['DrugRequestPurchaseSpec_id'];
										}
									}
								}
								$groups[] = $gp;
							}
						} elseif( $options['drugpurchase_grouping'] == 'mzrf' && !empty($drug['MzPharmagroup_id']) ) {
							if (!in_array($drug['DrugRequestPurchaseSpec_id'], $id_array)) {
								$gp = array(
									'group_name' => $drug['MzPharmagroup_Name'],
									'drugs' => array($drug['DrugRequestPurchaseSpec_id']),
									'finance_id' => $finance_id,
									'costitemtype_id' => $costitemtype_id
								);
								$phgr_id = $drug['MzPharmagroup_id'];

								$id_array[] = $drug['DrugRequestPurchaseSpec_id'];
								foreach($drugs as $key=>$val) {
									if (!in_array($val['DrugRequestPurchaseSpec_id'], $id_array)) {
										if(!empty($val['MzPharmagroup_id']) && $val['MzPharmagroup_id'] == $phgr_id) {
											$gp['drugs'][] = $val['DrugRequestPurchaseSpec_id'];
											$id_array[] = $val['DrugRequestPurchaseSpec_id'];
										}
									}
								}
								$groups[] = $gp;
							}
						} else {
							if (!isset($others[$drug['DrugFinance_id']]))
								$others[$drug['DrugFinance_id']] = array();

							$others[$drug['DrugFinance_id']][] = $drug;
						}
					}
				}
			}

			//обьединяем группы медикаментов с единственным производителем и обычные группы
			$groups = array_merge($s_groups, $groups);

			// Добавляем сгруппированные лоты и медикаменты к ним
			foreach($groups as $group) {
				$response = $this->dbmodel->saveUnitOfTrading(array(
					'WhsDocumentUc_id' => null
					,'WhsDocumentUc_Num' => $uot_num++//'номер лота'
					,'WhsDocumentUc_Name' => $group['group_name']
					,'WhsDocumentUc_Sum' => null
					,'WhsDocumentType_id' => 5
					,'WhsDocumentStatusType_id' => 1
					,'WhsDocumentUcStatusType_id' => null
					,'Org_aid' => $data['Org_aid']
					,'DrugFinance_id' => $data['DrugFinance_id']//$group['finance_id']
					,'WhsDocumentCostItemType_id' => isset($data['WhsDocumentCostItemType_id'])?$data['WhsDocumentCostItemType_id'] : 15 // 15 - не определена
					,'PurchObjType_id' => $data['PurchObjType_id']
					,'BudgetFormType_id' => $data['BudgetFormType_id']
					,'WhsDocumentPurchType_id' => $data['WhsDocumentPurchType_id']
					,'WhsDocumentProcurementRequest_setDate' => $data['WhsDocumentProcurementRequest_setDate']
					,'pmUser_id' => $data['pmUser_id']
				));
				if( $response === false || ( isset($response[0]) && strlen($response[0]['Error_Msg']) > 0 ) ) {
					$this->dbmodel->rollbackTransaction();
					DieWithError("Произошла ошибка при сохранении лота!");
				}
				// Сохраняем данные для документации для созданного лота
				$docsDataForSave = $defaultDocsData;
				$docsDataForSave['WhsDocumentUc_id'] = $response[0]['WhsDocumentUc_id'];
				$docsData = $this->dbmodel->saveUnitOfTradingDocsData($docsDataForSave);
				foreach($group['drugs'] as $drug_id) {
					$this->dbmodel->addDrugInUnitOfTrading(array(
						'DrugRequestPurchaseSpec_id' => $drug_id
						,'WhsDocumentUc_id' => $response[0]['WhsDocumentUc_id']
						,'Lpu_id' => $data['Lpu_id']
						,'pmUser_id' => $data['pmUser_id']
					));
				}
				$this->dbmodel->setSumForUnitOfTrading(array('WhsDocumentUc_id' => $response[0]['WhsDocumentUc_id'],'pmUser_id' => $data['pmUser_id']));
			}

			foreach($others as $fin=>$other) {
				// Добавляем остальные медикаменты в отдельный лот
				$response = $this->dbmodel->saveUnitOfTrading(array(
					'WhsDocumentUc_id' => null
					,'WhsDocumentUc_Num' => $uot_num++//'номер лота'
					,'WhsDocumentUc_Name' => 'Лот, содержащий несгруппированные медикаменты'
					,'WhsDocumentUc_Sum' => null
					,'WhsDocumentType_id' => 5
					,'WhsDocumentStatusType_id' => 1
					,'WhsDocumentUcStatusType_id' => null
					,'Org_aid' => $data['Org_aid']
					,'DrugFinance_id' => $data['DrugFinance_id']//$fin
					,'WhsDocumentCostItemType_id' => isset($data['WhsDocumentCostItemType_id'])?$data['WhsDocumentCostItemType_id'] : 15 // 15 - не определена
					,'PurchObjType_id' => $data['PurchObjType_id']
					,'BudgetFormType_id' => $data['BudgetFormType_id']
					,'WhsDocumentPurchType_id' => $data['WhsDocumentPurchType_id']
					,'WhsDocumentProcurementRequest_setDate' => $data['WhsDocumentProcurementRequest_setDate']
					,'pmUser_id' => $data['pmUser_id']
				));
				if( $response === false || ( isset($response[0]) && strlen($response[0]['Error_Msg']) > 0 ) ) {
					$this->dbmodel->rollbackTransaction();
					DieWithError("Произошла ошибка при сохранении лота!");
				}
				// Сохраняем данные для документации для созданного лота
				$docsDataForSave = $defaultDocsData;
				$docsDataForSave['WhsDocumentUc_id'] = $response[0]['WhsDocumentUc_id'];
				$docsData = $this->dbmodel->saveUnitOfTradingDocsData($docsDataForSave);
				foreach($other as $other_item) {
					$this->dbmodel->addDrugInUnitOfTrading(array(
						'DrugRequestPurchaseSpec_id' => $other_item['DrugRequestPurchaseSpec_id']
						,'WhsDocumentUc_id' => $response[0]['WhsDocumentUc_id']
						,'Lpu_id' => $data['Lpu_id']
						,'pmUser_id' => $data['pmUser_id']
					));
				}
				$this->dbmodel->setSumForUnitOfTrading(array('WhsDocumentUc_id' => $response[0]['WhsDocumentUc_id'],'pmUser_id' => $data['pmUser_id']));
			}
		} else {
			//
		}
		
		$this->dbmodel->commitTransaction();
		$this->ReturnData(array('success' => true));
	}
	
	/**
	 *	Переформирование лотов
	 */
	function reformationUnitOfTrading() {
		$data = $this->ProcessInputData('reformationUnitOfTrading', true);
		if ($data === false) { return false; }
		
		$uots = $this->dbmodel->getUnitOfTradingListOnDrugRequest($data);
		$supp = array();
		foreach($uots as $uot) {
			$a_sup = $this->dbmodel->getAssociatedWhsDocumentSupply(array('WhsDocumentUc_id' => $uot['WhsDocumentUc_id']));
			foreach($a_sup as $sup) {
				$supp[] = $sup['WhsDocumentUc_Num'];
			}
		}
		if (count($supp) > 0) {
			DieWithError('Не удалось переформировать лоты. Существуют связанные контракты. Список номеров: '.join($supp,', '));
		}

		$this->dbmodel->beginTransaction();
		foreach($uots as $uot) {
			if( $this->dbmodel->isSignedUnitOfTrading($uot) ) {
				// Отменяем подписание
				try {
					$this->dbmodel->unsignUnitOfTrading(array(
						'WhsDocumentUc_id' => $uot['WhsDocumentUc_id'],
						'pmUser_id' => $data['pmUser_id']
					));
				} catch( Exception $e ) {
					DieWithError($e->getMessage());
				}
			}
			
			$drugs = $this->dbmodel->getDrugsOfUnitOfTrading($uot);
			foreach($drugs as $drug_id) {
				try {
					$res = $this->dbmodel->deleteDrugOfUnitOfTrading(array( 'WhsDocumentProcurementRequestSpec_id' => $drug_id ));
				} catch (Exception $e) {
					DieWithError('Не удалось удалить медикамент!');
				}
				if( !is_array($res) || ( isset($res[0]['Error_Msg']) && strlen($res[0]['Error_Msg']) > 0 ) ) {
					$this->dbmodel->rollbackTransaction();
				}
			}
			// Удаляем лот
			$response = $this->dbmodel->deleteUnitOfTrading(array(
				'WhsDocumentProcurementRequest_id' => $uot['WhsDocumentUc_id']
				,'pmUser_id' => $data['pmUser_id']
			));
			if( !is_array($response) || ( isset($response[0]['Error_Message']) && strlen($response[0]['Error_Message']) > 0 ) ) {
				$this->dbmodel->rollbackTransaction();
			}
		}
		$this->dbmodel->commitTransaction();
		
		// Формируем лоты по-новой
		return $this->formationUnitOfTrading($data);
	}

	/**
	 *	Возвращает список дочерних контрактов по идентификаторам лотов
	 */
	function getWhsDocumentSupplyByUotId() {
		$data = $this->ProcessInputData('getWhsDocumentSupplyByUotId', true);
		if ($data === false) { return false; }

		$data['UotList'] = explode("|", urldecode($data['UotList']));
		$response = $this->dbmodel->getWhsDocumentSupplyByUotId($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *	Печать Обоснования начальной (максимальной) цены контракта (лота)
	 */
	function printLotRational($return = false) {
		/**
		 *	Форматирование вывода денежных сумм 
		 */
		function makeMoneyView ($data = 0) {
			if($data === false || $data == 0){
				return ' -- ';
			} else {
				$data = round($data,2);
				return number_format($data, 2, '.', ' ');
			}
		}
		$this->load->library('parser');
		$data = $this->ProcessInputData('printLotRational', true);
		if ($data === false) { return false; }

		$template = 'print_lot_rational';
		$default_value = '&nbsp;';

		// Получаем данные
		$response = $this->dbmodel->getWhsDocumentUcRationalFields($data);

		if ( (!is_array($response)) || (count($response) == 0) ) {
			echo 'Ошибка при получении данных по лоту';
			return true;
		}

		//Список медикаментов в шапке
		$respsObj = array();
		if(isset($response['drugslist'])){
			$response0 = $response['drugslist'];
			/*foreach ($response0 as $resp) {
				$res = array(
					'PurchObjType_Name' => isset($resp['PurchObjType_Name']) ? $resp['PurchObjType_Name'] : $default_value,
					'WName' => isset($resp['WName']) ? $resp['WName'] : $default_value
				);
				array_push($respsObj,$res);
			}*/
			if(count($response0)>0){
				$resp = $response0[0];
				$res = array(
					'PurchObjType_Name' => isset($resp['PurchObjType_Name']) ? $resp['PurchObjType_Name'] : $default_value,
					'WName' => isset($resp['LotName']) ? $resp['LotName'] : $default_value
				);
				array_push($respsObj,$res);
			}
		}
		
		//Создание массива с именами колонок поставщиков Таблица 1
		$columns = array();
		$contractIds = array();
		$supplierIds = array();
		$num = 1;
		$itogo = 0;
		if(isset($response['contracts'])){
			$contracts = $response['contracts'];
			if(!empty($contracts)){
				foreach ($contracts as $contract) {
					if(!in_array($contract['id'], $contractIds) || $contract['id'] == null){
						array_push($contractIds, $contract['id']);
						array_push($columns, array('num'=>$num,'id'=>$contract['id'],'name'=>$contract['Name'],'type'=>'contract'));
						$num++;
					}
				}
			}
		}
		if(isset($response['suppliers'])){
			$suppliers = $response['suppliers'];
			if(!empty($suppliers)){
				foreach ($suppliers as $supplier) {
					if(!in_array($supplier['id'], $supplierIds) || $supplier['id'] == null){
						array_push($supplierIds, $supplier['id']);
						array_push($columns, array('num'=>$num,'id'=>$supplier['id'],'name'=>$supplier['Name'],'type'=>'supplier'));
						$num++;
					}
				}
			}
		}
		if(empty($columns)) array_push($columns, array('num'=>1,'id'=>0,'name'=>''));
		//

		// Заполнение строк таблицы 1
		$numb = 1;
		$respsGoods = array();
		if(isset($response['drugs1'])){
			$response1 = $response['drugs1'];
			foreach ($response1 as $resp) {

				$prices = array();
				$WCount = (isset($resp['WCount']) && $resp['WCount'] > 0 ? $resp['WCount'] : 1);
				$wWCount = (isset($resp['wWCount']) && $resp['wWCount'] > 0 ? $resp['wWCount'] : 1);
				$midprice = 0;
				foreach ($columns as $column) {
					array_push($prices, array('price'=>' -- ','initprice'=>0));
				}
				if(!empty($contracts)){
					foreach ($contracts as $contract) {
						if($contract['WDPRS_id'] == $resp['WDPRS_id']){
							foreach ($columns as $column) {
								if($column['type'] == 'contract' && $column['id'] == $contract['id']){
									$prices[$column['num']-1]['price'] = makeMoneyView(round((intval($contract['price']) / $WCount),2));
									$prices[$column['num']-1]['initprice'] = (intval($contract['price']) / $WCount);
									$midprice += (intval($contract['price']) / $WCount);
								}
							}
						}
					}
				}
				if(!empty($suppliers)){
					foreach ($suppliers as $supplier) {
						if($supplier['WDPRS_id'] == $resp['WDPRS_id']){
							foreach ($columns as $column) {
								if($column['type'] == 'supplier' && $column['id'] == $supplier['id']){
									$prices[$column['num']-1]['price'] = makeMoneyView(round((intval($supplier['price']) / $WCount),2));
									$prices[$column['num']-1]['initprice'] = (intval($supplier['price']) / $WCount);
									$midprice += (intval($supplier['price']) / $WCount);
								}
							}
						}
					}
				}
				$midpricecount = 0;
				foreach ($prices as $value) {
					if($value['initprice'] > 0)
						$midpricecount++;
				}
				if($midpricecount > 0){
					$midprice = round(($midprice / $midpricecount),2);
				}
				$sov = 0;
				foreach ($prices as $price) {
					if($price['initprice'] > 0){
						$sov += ((intval($price['initprice']) - $midprice)*(intval($price['initprice']) - $midprice));
					}
				}
				if($midpricecount > 1 && $midprice > 0){
					$sqotkl = sqrt(($sov)/($midpricecount - 1));
					$coef = round((($sqotkl / $midprice)*100),2);
				}
				else $coef = 0;
				if($coef<33)
					$sovokupn = 'однородная';
				else $sovokupn = 'неоднородная';
				//$rowprice = $wWCount*$midprice; - это расчет, но пока делим все до единиц, а потом обратно умножаем, то в итоге теряем точность и число получается меньше чем в лоте 
				$rowprice = ((isset($resp['rowprice']) && $resp['rowprice'] > 0) ? $resp['rowprice'] : $wWCount*$midprice);
				$itogo += $rowprice;

				$res = array(
					'numb' => $numb,
					'Okpd_Name' => isset($resp['Okpd_Name']) ? $resp['Okpd_Name'] : $default_value,
					'DMnnName' => isset($resp['DMnnName']) ? $resp['DMnnName'] : $default_value,
					'Tradename' => isset($resp['Tradename']) ? $resp['Tradename'] : $default_value,
					'DrugForm' => isset($resp['DrugForm']) ? $resp['DrugForm'] : $default_value,
					'DoseName' => isset($resp['DoseName']) ? $resp['DoseName'] : $default_value,
					'GUNick' => isset($resp['GUNick']) ? $resp['GUNick'] : $default_value,
					'WCount' => isset($resp['wWCount']) ? $resp['wWCount'] : $default_value,
					'FasName' => isset($resp['FasName']) ? $resp['FasName'] : $default_value,
					'SpecKolvo' => isset($resp['SpecKolvo']) ? $resp['SpecKolvo'] : $default_value,
					'prices'=>$prices,
					'coef'=>$coef,
					'sovokupn'=>$sovokupn,
					'midprice'=>$midprice,
					'rowprice'=>makeMoneyView($rowprice)
				);
				array_push($respsGoods,$res);
				$numb++;
			}
		}
		// Количество столбцов Поставщики в таблице 1
		$count = 0;
		if(isset($response['counts'])){
			$response2 = $response['counts'];
			$count = count($contracts) + count($suppliers);//intval($response2['ContractCount']) + intval($response2['SupplierCount']);
		}
		if($count == 0) $count = 1;

		//Создание массива с именами колонок цен Таблица 2
		$columns2 = array();
		$linkIds = array();
		$num2 = 1;
		$itogo2 = 0;
		if(isset($response['links'])){
			$links = $response['links'];
			if(!empty($links)){
				foreach ($links as $link) {
					if(!in_array($link['id'], $linkIds)){
						array_push($linkIds, $link['id']);
						array_push($columns2, array('num'=>$num2,'id'=>$link['id'],'name'=>$link['Name']));
						$num2++;
					}
				}
			}
		}
		if(empty($columns2)) array_push($columns2, array('num'=>1,'id'=>0,'name'=>''));
		//

		// Заполнение строк таблицы 2
		$numb2 = 1;
		$respsGoods2 = array();
		if(isset($response['drugs2'])) { 
			$response1 = $response['drugs2'];
			foreach ($response1 as $resp) {

				$prices = array();
				$WCount = (isset($resp['WCount']) && $resp['WCount'] > 0 ? $resp['WCount'] : 1);
				$wWCount = (isset($resp['wWCount']) && $resp['wWCount'] > 0 ? $resp['wWCount'] : 1);
				$midprice = 0;
				foreach ($columns2 as $column) {
					array_push($prices, array('price'=>0));
				}
				if(!empty($links)){
					foreach ($links as $link) {
						if($link['WDPRS_id'] == $resp['WDPRS_id']){
							foreach ($columns2 as $column) {
								if($column['id'] == $link['id']){
									$prices[$column['num']-1]['price'] = round((intval($link['price']) / $WCount),2);
									$midprice += (intval($link['price']) / $WCount);
								}
							}
						}
					}
				}
				if(count($prices) > 0){
					$midprice = round(($midprice / count($prices)),2);
				}
				//$rowprice = $wWCount*$midprice; - это расчет, но пока делим все до единиц, а потом обратно умножаем, то в итоге теряем точность и число получается меньше чем в лоте 
				$rowprice = ((isset($resp['rowprice']) && $resp['rowprice'] > 0) ? $resp['rowprice'] : $wWCount*$midprice);
				$itogo2 += $rowprice;

				$res = array(
					'numb' => $numb2,
					'Okpd_Name' => isset($resp['Okpd_Name']) ? $resp['Okpd_Name'] : $default_value,
					'DMnnName' => isset($resp['DMnnName']) ? $resp['DMnnName'] : $default_value,
					'Tradename' => isset($resp['Tradename']) ? $resp['Tradename'] : $default_value,
					'DrugForm' => isset($resp['DrugForm']) ? $resp['DrugForm'] : $default_value,
					'DoseName' => isset($resp['DoseName']) ? $resp['DoseName'] : $default_value,
					'GUNick' => isset($resp['GUNick']) ? $resp['GUNick'] : $default_value,
					'WCount' => isset($resp['wWCount']) ? $resp['wWCount'] : $default_value,
					'FasName' => isset($resp['FasName']) ? $resp['FasName'] : $default_value,
					'SpecKolvo' => isset($resp['SpecKolvo']) ? $resp['SpecKolvo'] : $default_value,
					'prices'=>$prices,
					'midprice'=>$midprice,
					'rowprice'=>makeMoneyView($rowprice)
				);
				array_push($respsGoods2,$res);
				$numb2++;
			}
		}

		// Количество колонок Цены в таблице 2
		$linkcount = 0;
		if(isset($response['links']))
			$linkcount = count($links);//intval($response['linkcounts']);
		if($linkcount == 0) $linkcount = 1;

		// Общая сумма в самом низу отчета
		$mainitogo = $itogo + $itogo2;

		// Массив для парсера
		$parse_data = array(
			'obj'=>$respsObj, 
			'goods'=>$respsGoods, 
			'count'=>$count, 
			'columns'=>$columns, 
			'itogo'=>makeMoneyView($itogo), 
			'goods2'=>$respsGoods2, 
			'count2'=>$linkcount, 
			'columns2'=>$columns2, 
			'itogo2'=>makeMoneyView($itogo2),
			'mainitogo'=>makeMoneyView($mainitogo)
			);

		$result = $this->parser->parse($template, $parse_data, $return);

		return $result;
	}

	/**
	 *	Сохранение данных для документации по лоту
	 */
	function saveUnitOfTradingDocsData() {
		$data = $this->ProcessInputData('saveUnitOfTradingDocsData', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveUnitOfTradingDocsData($data);
		if( !is_array($response) || strlen($response[0]['Error_Msg']) > 0 ) {
			return;
		}
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 *	Получает целевую статью
	 */
	function getBudgetFormType() {
		$data = $this->ProcessInputData('getBudgetFormType', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getBudgetFormType($data);
		$this->ReturnData($response);
	}

	/**
	 *	Проверяет параметры расчета и делает расчет количества единиц измерения товара в упаковке
	 */
	function calcDrugUnitQuant() {
		$data = $this->ProcessInputData('calcDrugUnitQuant', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->calcDrugUnitQuant($data);
		$this->ReturnData($response);
	}

	/**
	 *	Копирование данных для документации по лоту
	 */
	function copyUnitOfTradingDocsData() {
		$data = $this->ProcessInputData('copyUnitOfTradingDocsData', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->copyUnitOfTradingDocsData($data);
		if( !is_array($response) || strlen($response[0]['Error_Msg']) > 0 ) {
			return;
		}
		$this->ProcessModelSave($response, true)->ReturnData();
	}

    /**
     * Загрузка комбобокса для выбора кода ОКПД
     */
    function loadOkpdCombo() {
        $data = $this->ProcessInputData('loadOkpdCombo', false);
        if ($data) {
            $response = $this->dbmodel->loadOkpdCombo($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

     /**
     * Загрузка названия организации
     */
    function getOrgNick() {
        $data = $this->ProcessInputData('getOrgNick', false);
        if ($data) {
            $response = $this->dbmodel->getOrgNick($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка единицы измерения товара
     */
    function getGoodsUnitByDrugComplexMnn() {
        $data = $this->ProcessInputData('getGoodsUnitByDrugComplexMnn', false);
        if ($data) {
            $response = $this->dbmodel->getGoodsUnitByDrugComplexMnn($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
	 * Получение списка организаций
	 */
	function loadOrgCombo() {
		$data = $this->ProcessInputData('loadOrgCombo', false);
		if ($data) {
			$response = $this->dbmodel->loadOrgCombo($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}