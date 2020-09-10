<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * TODO: complete explanation, preamble and describing
 * Модель для объектов Спецификация договора
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2010-2011 Swan Ltd.
 * @author       ModelGenerator
 * @version
 */
class WhsDocumentSupplySpec_model extends swModel {
	private $WhsDocumentSupplySpec_id;//Идентификатор
	private $WhsDocumentSupply_id;//Договор поставок
	private $WhsDocumentProcurementRequestSpec_id;//Позиция лота
	private $WhsDocumentSupplySpec_PosCode;//Код позиции
	private $Drug_id;//Медикамент (rls)
	private $DrugComplexMnn_id;//Комплексное МНН
	private $FIRMNAMES_id;//Производитель
	private $WhsDocumentSupplySpec_KolvoForm;//Количество единиц форм выпуска в упаковке
	private $DRUGPACK_id;//Торговая упаковка
	private $Okei_id;//Единица поставки (ОКЕИ)
	private $WhsDocumentSupplySpec_KolvoUnit;//Количество единиц поставки
	private $WhsDocumentSupplySpec_Count;//Количество ЛС из лота
	private $WhsDocumentSupplySpec_Price;//Оптовая цена за ед. без НДС
	private $WhsDocumentSupplySpec_NDS;//НДС
	private $WhsDocumentSupplySpec_SumNDS;//Сумма с НДС
	private $WhsDocumentSupplySpec_PriceNDS;//Цена с НДС
	private $WhsDocumentSupplySpec_ShelfLifePersent;//Остаточный срок хранения не менее (%)
	private $GoodsUnit_id;//Идентификатор единицы измерения
	private $pmUser_id;//Идентификатор пользователя системы Промед
	private $WhsDocumentSupplySpec_GoodsUnitQty;//Кол-во ед.изм
	private $WhsDocumentSupplySpec_SuppPrice;//Цена поставщика

	/**
	 * Получение параметра
	 */
	public function getWhsDocumentSupplySpec_id() { return $this->WhsDocumentSupplySpec_id;}

	/**
	 * Установка параметра
	 */
	public function setWhsDocumentSupplySpec_id($value) { $this->WhsDocumentSupplySpec_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getWhsDocumentSupply_id() { return $this->WhsDocumentSupply_id;}

	/**
	 * Установка параметра
	 */
	public function setWhsDocumentSupply_id($value) { $this->WhsDocumentSupply_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getWhsDocumentSupplySpec_PosCode() { return $this->WhsDocumentSupplySpec_PosCode;}

	/**
	 * Установка параметра
	 */
	public function setWhsDocumentSupplySpec_PosCode($value) { $this->WhsDocumentSupplySpec_PosCode = $value; }

	/**
	 * Получение параметра
	 */
	public function getDrug_id() { return $this->Drug_id;}

	/**
	 * Установка параметра
	 */
	public function setDrug_id($value) { $this->Drug_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getDrugComplexMnn_id() { return $this->DrugComplexMnn_id;}

	/**
	 * Установка параметра
	 */
	public function setDrugComplexMnn_id($value) { $this->DrugComplexMnn_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getFIRMNAMES_id() { return $this->FIRMNAMES_id;}

	/**
	 * Установка параметра
	 */
	public function setFIRMNAMES_id($value) { $this->FIRMNAMES_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getWhsDocumentSupplySpec_KolvoForm() { return $this->WhsDocumentSupplySpec_KolvoForm;}

	/**
	 * Установка параметра
	 */
	public function setWhsDocumentSupplySpec_KolvoForm($value) { $this->WhsDocumentSupplySpec_KolvoForm = $value; }

	/**
	 * Получение параметра
	 */
	public function getDRUGPACK_id() { return $this->DRUGPACK_id;}

	/**
	 * Установка параметра
	 */
	public function setDRUGPACK_id($value) { $this->DRUGPACK_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getOkei_id() { return $this->Okei_id;}

	/**
	 * Установка параметра
	 */
	public function setOkei_id($value) { $this->Okei_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getWhsDocumentSupplySpec_KolvoUnit() { return $this->WhsDocumentSupplySpec_KolvoUnit;}

	/**
	 * Установка параметра
	 */
	public function setWhsDocumentSupplySpec_KolvoUnit($value) { $this->WhsDocumentSupplySpec_KolvoUnit = $value; }

	/**
	 * Получение параметра
	 */
	public function getWhsDocumentSupplySpec_Count() { return $this->WhsDocumentSupplySpec_Count;}

	/**
	 * Установка параметра
	 */
	public function setWhsDocumentSupplySpec_Count($value) { $this->WhsDocumentSupplySpec_Count = $value; }

	/**
	 * Получение параметра
	 */
	public function getWhsDocumentSupplySpec_Price() { return $this->WhsDocumentSupplySpec_Price;}

	/**
	 * Установка параметра
	 */
	public function setWhsDocumentSupplySpec_Price($value) { $this->WhsDocumentSupplySpec_Price = $value; }

	/**
	 * Получение параметра
	 */
	public function getWhsDocumentSupplySpec_NDS() { return $this->WhsDocumentSupplySpec_NDS;}

	/**
	 * Установка параметра
	 */
	public function setWhsDocumentSupplySpec_NDS($value) { $this->WhsDocumentSupplySpec_NDS = $value; }

	/**
	 * Получение параметра
	 */
	public function getWhsDocumentSupplySpec_SumNDS() { return $this->WhsDocumentSupplySpec_SumNDS;}

	/**
	 * Установка параметра
	 */
	public function setWhsDocumentSupplySpec_SumNDS($value) { $this->WhsDocumentSupplySpec_SumNDS = $value; }

	/**
	 * Получение параметра
	 */
	public function getWhsDocumentSupplySpec_PriceNDS() { return $this->WhsDocumentSupplySpec_PriceNDS;}

	/**
	 * Установка параметра
	 */
	public function setWhsDocumentSupplySpec_PriceNDS($value) { $this->WhsDocumentSupplySpec_PriceNDS = $value; }

	/**
	 * Получение параметра
	 */
	public function getWhsDocumentSupplySpec_ShelfLifePersent() { return $this->WhsDocumentSupplySpec_ShelfLifePersent;}

	/**
	 * Установка параметра
	 */
	public function setWhsDocumentSupplySpec_ShelfLifePersent($value) { $this->WhsDocumentSupplySpec_ShelfLifePersent = $value; }

	/**
	 * Получение параметра
	 */
	public function getGoodsUnit_id() { return $this->GoodsUnit_id;}

	/**
	 * Установка параметра
	 */
	public function setGoodsUnit_id($value) { $this->GoodsUnit_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getWhsDocumentSupplySpec_GoodsUnitQty() { return $this->WhsDocumentSupplySpec_GoodsUnitQty;}

	/**
	 * Установка параметра
	 */
	public function setWhsDocumentSupplySpec_GoodsUnitQty($value) { $this->WhsDocumentSupplySpec_GoodsUnitQty = $value; }

	/**
	 * Получение параметра
	 */
	public function getWhsDocumentSupplySpec_SuppPrice() { return $this->WhsDocumentSupplySpec_SuppPrice;}

	/**
	 * Установка параметра
	 */
	public function setWhsDocumentSupplySpec_SuppPrice($value) { $this->WhsDocumentSupplySpec_SuppPrice = $value; }

	/**
	 * Получение параметра
	 */
	public function getpmUser_id() { return $this->pmUser_id;}

	/**
	 * Установка параметра
	 */
	public function setpmUser_id($value) { $this->pmUser_id = $value; }

	/**
	 * Конструктор
	 */
	function __construct(){
		if (isset($_SESSION['pmuser_id'])) {
			$this->setpmUser_id($_SESSION['pmuser_id']);
		} else {
			throw new Exception('Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)');
		}
	}

	/**
	 * Загрузка
	 */
	function load() {
		$q = "
			select
				WhsDocumentSupplySpec_id, 
				WhsDocumentSupply_id, 
				WhsDocumentSupplySpec_PosCode, 
				DrugComplexMnn_id, 
				FIRMNAMES_id, 
				WhsDocumentSupplySpec_KolvoForm, 
				DRUGPACK_id, 
				Okei_id, 
				WhsDocumentSupplySpec_KolvoUnit, 
				WhsDocumentSupplySpec_Count, 
				WhsDocumentSupplySpec_Price, 
				WhsDocumentSupplySpec_NDS, 
				WhsDocumentSupplySpec_SumNDS, 
				WhsDocumentSupplySpec_PriceNDS, 
				WhsDocumentSupplySpec_ShelfLifePersent,
				GoodsUnit_id,
				WhsDocumentSupplySpec_GoodsUnitQty,
				WhsDocumentSupplySpec_SuppPrice
			from
				dbo.v_WhsDocumentSupplySpec with(nolock)
			where
				WhsDocumentSupplySpec_id = :WhsDocumentSupplySpec_id
		";
		$r = $this->db->query($q, array('WhsDocumentSupplySpec_id' => $this->WhsDocumentSupplySpec_id));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$this->WhsDocumentSupplySpec_id = $r[0]['WhsDocumentSupplySpec_id'];
				$this->WhsDocumentSupply_id = $r[0]['WhsDocumentSupply_id'];
				$this->WhsDocumentSupplySpec_PosCode = $r[0]['WhsDocumentSupplySpec_PosCode'];
				$this->DrugComplexMnn_id = $r[0]['DrugComplexMnn_id'];
				$this->FIRMNAMES_id = $r[0]['FIRMNAMES_id'];
				$this->WhsDocumentSupplySpec_KolvoForm = $r[0]['WhsDocumentSupplySpec_KolvoForm'];
				$this->DRUGPACK_id = $r[0]['DRUGPACK_id'];
				$this->Okei_id = $r[0]['Okei_id'];
				$this->WhsDocumentSupplySpec_KolvoUnit = $r[0]['WhsDocumentSupplySpec_KolvoUnit'];
				$this->WhsDocumentSupplySpec_Count = $r[0]['WhsDocumentSupplySpec_Count'];
				$this->WhsDocumentSupplySpec_Price = $r[0]['WhsDocumentSupplySpec_Price'];
				$this->WhsDocumentSupplySpec_NDS = $r[0]['WhsDocumentSupplySpec_NDS'];
				$this->WhsDocumentSupplySpec_SumNDS = $r[0]['WhsDocumentSupplySpec_SumNDS'];
				$this->WhsDocumentSupplySpec_PriceNDS = $r[0]['WhsDocumentSupplySpec_PriceNDS'];
				$this->WhsDocumentSupplySpec_ShelfLifePersent = $r[0]['WhsDocumentSupplySpec_ShelfLifePersent'];
				$this->GoodsUnit_id = $r[0]['GoodsUnit_id'];
				$this->WhsDocumentSupplySpec_GoodsUnitQty = $r[0]['WhsDocumentSupplySpec_GoodsUnitQty'];
				$this->WhsDocumentSupplySpec_SuppPrice = $r[0]['WhsDocumentSupplySpec_SuppPrice'];
				return $r;
			} else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadList($filter) {
		$where = array();
		$p = array();
		if (isset($filter['WhsDocumentSupplySpec_id']) && $filter['WhsDocumentSupplySpec_id']) {
			$where[] = 'v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_id = :WhsDocumentSupplySpec_id';
			$p['WhsDocumentSupplySpec_id'] = $filter['WhsDocumentSupplySpec_id'];
		}
		if (isset($filter['WhsDocumentSupply_id']) && $filter['WhsDocumentSupply_id']) {
			$where[] = 'v_WhsDocumentSupplySpec.WhsDocumentSupply_id = :WhsDocumentSupply_id';
			$p['WhsDocumentSupply_id'] = $filter['WhsDocumentSupply_id'];
		}
		if (isset($filter['WhsDocumentSupplySpec_PosCode']) && $filter['WhsDocumentSupplySpec_PosCode']) {
			$where[] = 'v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_PosCode = :WhsDocumentSupplySpec_PosCode';
			$p['WhsDocumentSupplySpec_PosCode'] = $filter['WhsDocumentSupplySpec_PosCode'];
		}
		if (isset($filter['DrugComplexMnn_id']) && $filter['DrugComplexMnn_id']) {
			$where[] = 'v_WhsDocumentSupplySpec.DrugComplexMnn_id = :DrugComplexMnn_id';
			$p['DrugComplexMnn_id'] = $filter['DrugComplexMnn_id'];
		}
		if (isset($filter['FIRMNAMES_id']) && $filter['FIRMNAMES_id']) {
			$where[] = 'v_WhsDocumentSupplySpec.FIRMNAMES_id = :FIRMNAMES_id';
			$p['FIRMNAMES_id'] = $filter['FIRMNAMES_id'];
		}
		if (isset($filter['WhsDocumentSupplySpec_KolvoForm']) && $filter['WhsDocumentSupplySpec_KolvoForm']) {
			$where[] = 'v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_KolvoForm = :WhsDocumentSupplySpec_KolvoForm';
			$p['WhsDocumentSupplySpec_KolvoForm'] = $filter['WhsDocumentSupplySpec_KolvoForm'];
		}
		if (isset($filter['DRUGPACK_id']) && $filter['DRUGPACK_id']) {
			$where[] = 'v_WhsDocumentSupplySpec.DRUGPACK_id = :DRUGPACK_id';
			$p['DRUGPACK_id'] = $filter['DRUGPACK_id'];
		}
		if (isset($filter['Okei_id']) && $filter['Okei_id']) {
			$where[] = 'v_WhsDocumentSupplySpec.Okei_id = :Okei_id';
			$p['Okei_id'] = $filter['Okei_id'];
		}
		if (isset($filter['WhsDocumentSupplySpec_KolvoUnit']) && $filter['WhsDocumentSupplySpec_KolvoUnit']) {
			$where[] = 'v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_KolvoUnit = :WhsDocumentSupplySpec_KolvoUnit';
			$p['WhsDocumentSupplySpec_KolvoUnit'] = $filter['WhsDocumentSupplySpec_KolvoUnit'];
		}
		if (isset($filter['WhsDocumentSupplySpec_Count']) && $filter['WhsDocumentSupplySpec_Count']) {
			$where[] = 'v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_Count = :WhsDocumentSupplySpec_Count';
			$p['WhsDocumentSupplySpec_Count'] = $filter['WhsDocumentSupplySpec_Count'];
		}
		if (isset($filter['WhsDocumentSupplySpec_Price']) && $filter['WhsDocumentSupplySpec_Price']) {
			$where[] = 'v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_Price = :WhsDocumentSupplySpec_Price';
			$p['WhsDocumentSupplySpec_Price'] = $filter['WhsDocumentSupplySpec_Price'];
		}
		if (isset($filter['WhsDocumentSupplySpec_NDS']) && $filter['WhsDocumentSupplySpec_NDS']) {
			$where[] = 'v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_NDS = :WhsDocumentSupplySpec_NDS';
			$p['WhsDocumentSupplySpec_NDS'] = $filter['WhsDocumentSupplySpec_NDS'];
		}
		if (isset($filter['WhsDocumentSupplySpec_SumNDS']) && $filter['WhsDocumentSupplySpec_SumNDS']) {
			$where[] = 'v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_SumNDS = :WhsDocumentSupplySpec_SumNDS';
			$p['WhsDocumentSupplySpec_SumNDS'] = $filter['WhsDocumentSupplySpec_SumNDS'];
		}
		if (isset($filter['WhsDocumentSupplySpec_PriceNDS']) && $filter['WhsDocumentSupplySpec_PriceNDS']) {
			$where[] = 'v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_PriceNDS = :WhsDocumentSupplySpec_PriceNDS';
			$p['WhsDocumentSupplySpec_PriceNDS'] = $filter['WhsDocumentSupplySpec_PriceNDS'];
		}
		if (isset($filter['WhsDocumentSupplySpec_ShelfLifePersent']) && $filter['WhsDocumentSupplySpec_ShelfLifePersent']) {
			$where[] = 'v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_ShelfLifePersent = :WhsDocumentSupplySpec_ShelfLifePersent';
			$p['WhsDocumentSupplySpec_ShelfLifePersent'] = $filter['WhsDocumentSupplySpec_ShelfLifePersent'];
		}
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}
		$order_clause = "ORDER BY WhsDocumentSupplySpec_PosCode ASC ";
		$q = "
			SELECT
				v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_id,
				v_WhsDocumentSupplySpec.WhsDocumentSupply_id,
				v_WhsDocumentSupplySpec.WhsDocumentProcurementRequestSpec_id,
				v_WhsDocumentSupplySpec.Drug_id,
				d.Drug_Name,
				v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_PosCode, 
				v_WhsDocumentSupplySpec.DrugComplexMnn_id,
				v_WhsDocumentSupplySpec.FIRMNAMES_id, 
				v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_KolvoForm, 
				v_WhsDocumentSupplySpec.DRUGPACK_id,
				v_WhsDocumentSupplySpec.Okei_id, 
				v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_KolvoUnit,
				v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_Count, 
				v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_Price,
				v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_NDS, 
				v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_SumNDS,
				v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_PriceNDS, 
				v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_ShelfLifePersent,
				v_WhsDocumentSupplySpec.GoodsUnit_id,
				v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_GoodsUnitQty,
				v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_SuppPrice,
				coalesce(DrugComplexMnn_id_ref.DrugComplexMnn_RusName, dcm.DrugComplexMnn_RusName) as DrugComplexMnn_RusName,
				v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_id as grid_id,
				Okei_id_ref.Okei_Name Okei_id_Name,
				am.ACTMATTERS_ID as Actmatters_id, --МНН
				am.RUSNAME as ActMatters_RusName, --МНН
				tn.NAME as Tradename_Name, --Торговое наименование
				rtrim(fn.NAME + ' ' + isnull(c.NAME,'')) as Firm_Name, --Производитель
				rc.REGNUM as Reg_Num, --№ РУ
				df.NAME as DrugForm_Name, --Форма выпуска
				Dose.Value as Drug_Dose, --Дозировка
				Fas.Value as Drug_Fas, --Фасовка
				DrugNomen.DrugNomen_Code,
				dnds.DrugNds_id,
				v_WhsDocumentSupplySpec.Okei_id,
				CODPD.CommercialOfferDrug_PriceDetail
			FROM
				dbo.v_WhsDocumentSupplySpec WITH (NOLOCK)
				left join dbo.v_WhsDocumentSupply WhsDocumentSupply_id_ref with (nolock) on WhsDocumentSupply_id_ref.WhsDocumentSupply_id = v_WhsDocumentSupplySpec.WhsDocumentSupply_id
				left join rls.v_DrugComplexMnn DrugComplexMnn_id_ref with (nolock) on DrugComplexMnn_id_ref.DrugComplexMnn_id = v_WhsDocumentSupplySpec.DrugComplexMnn_id
				left join rls.v_FIRMNAMES FIRMNAMES_id_ref with (nolock) on FIRMNAMES_id_ref.FIRMNAMES_id = v_WhsDocumentSupplySpec.FIRMNAMES_id
				left join rls.v_DRUGPACK DRUGPACK_id_ref with (nolock) on DRUGPACK_id_ref.DRUGPACK_id = v_WhsDocumentSupplySpec.DRUGPACK_id
				left join dbo.v_Okei Okei_id_ref with (nolock) on Okei_id_ref.Okei_id = v_WhsDocumentSupplySpec.Okei_id
				left join rls.v_Drug d with (nolock) on d.Drug_id = v_WhsDocumentSupplySpec.Drug_id
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				left join rls.NOMEN n with (nolock) on n.NOMEN_ID = d.Drug_id
				left join rls.PREP p with (nolock) on p.Prep_id = d.DrugPrep_id
				left join rls.TRADENAMES tn with (nolock) on tn.TRADENAMES_ID = p.TRADENAMEID
				left join rls.CLSDRUGFORMS df with (nolock) on df.CLSDRUGFORMS_ID = p.DRUGFORMID
				left join rls.REGCERT rc with (nolock) on rc.REGCERT_ID = p.REGCERTID
				left join rls.FIRMS f with (nolock) on f.FIRMS_ID = p.FIRMID
				left join rls.FIRMNAMES fn with (nolock) on fn.FIRMNAMES_ID = f.NAMEID
				left join rls.v_COUNTRIES c with (nolock) on c.COUNTRIES_ID = f.COUNTID
				left join rls.ACTMATTERS am with (nolock) on am.ACTMATTERS_ID = dcmn.ActMatters_id
				left join rls.MASSUNITS mu with (nolock) on mu.MASSUNITS_ID = n.PPACKMASSUNID
				left join rls.CUBICUNITS cu with (nolock) on cu.CUBICUNITS_ID = n.PPACKCUBUNID
				left join rls.MASSUNITS df_mu with (nolock) on df_mu.MASSUNITS_ID = p.DFMASSID
				left join rls.CONCENUNITS df_cu with (nolock) on df_cu.CONCENUNITS_ID = p.DFCONCID
				left join rls.ACTUNITS df_au with (nolock) on df_au.ACTUNITS_ID = p.DFACTID
				left join rls.SIZEUNITS df_su with (nolock) on df_su.SIZEUNITS_ID = p.DFSIZEID
				left join dbo.v_DrugNds as dnds with (nolock) on dnds.DrugNds_Code = v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_NDS

				outer apply(
					select top 1 CommercialOfferDrug_PriceDetail
					from v_CommercialOfferDrug
					where Drug_id = d.Drug_id
				) as CODPD

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
						v_DrugNomen.DrugNomen_Code
					from
						rls.v_DrugNomen with(nolock)
					where
						v_DrugNomen.Drug_id = d.Drug_id
					order by
						DrugNomen_id
				) DrugNomen
			$where_clause
			$order_clause
		";
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохранение из JSON
	 */
	function saveFromJSON($data) {
		if (!empty($data['json_str']) && $data['WhsDocumentSupply_id'] > 0) {
			ConvertFromWin1251ToUTF8($data['json_str']);
			$dt = (array) json_decode($data['json_str']);

			foreach($dt as $record) {
				$this->WhsDocumentSupplySpec_id = $record->state == 'add' ? 0 :  $record->WhsDocumentSupplySpec_id;
				$this->pmUser_id = $data['pmUser_id'];
				switch($record->state) {
					case 'add':
					case 'edit':						
						$this->WhsDocumentSupply_id = $data['WhsDocumentSupply_id'];
						$this->WhsDocumentProcurementRequestSpec_id = !empty($record->WhsDocumentProcurementRequestSpec_id) ? $record->WhsDocumentProcurementRequestSpec_id : null;
						$this->WhsDocumentSupplySpec_PosCode = $record->WhsDocumentSupplySpec_PosCode ? $record->WhsDocumentSupplySpec_PosCode : '';
						$this->Drug_id = $record->Drug_id;
						$this->DrugComplexMnn_id = isset($record->DrugComplexMnn_id) && $record->DrugComplexMnn_id > 0 ? $record->DrugComplexMnn_id : null;
						$this->FIRMNAMES_id = isset($record->FIRMNAMES_id) && $record->FIRMNAMES_id > 0 ? $record->FIRMNAMES_id : null;
						$this->WhsDocumentSupplySpec_KolvoForm = isset($record->WhsDocumentSupplySpec_KolvoForm) && $record->WhsDocumentSupplySpec_KolvoForm > 0 ? $record->WhsDocumentSupplySpec_KolvoForm : null;
						$this->DRUGPACK_id = isset($record->DRUGPACK_id) && $record->DRUGPACK_id > 0 ? $record->DRUGPACK_id : null;
						$this->Okei_id = $record->Okei_id;
						$this->WhsDocumentSupplySpec_KolvoUnit = $record->WhsDocumentSupplySpec_KolvoUnit;
						$this->WhsDocumentSupplySpec_Count = isset($record->WhsDocumentSupplySpec_Count) && $record->WhsDocumentSupplySpec_Count >= 0 ? $record->WhsDocumentSupplySpec_Count : null;
						$this->WhsDocumentSupplySpec_Price = $record->WhsDocumentSupplySpec_Price;
						$this->WhsDocumentSupplySpec_NDS = $record->WhsDocumentSupplySpec_NDS;
						$this->WhsDocumentSupplySpec_SumNDS = $record->WhsDocumentSupplySpec_SumNDS;
						$this->WhsDocumentSupplySpec_PriceNDS = $record->WhsDocumentSupplySpec_PriceNDS;
						$this->WhsDocumentSupplySpec_ShelfLifePersent = $record->WhsDocumentSupplySpec_ShelfLifePersent;
						$this->GoodsUnit_id = !empty($record->GoodsUnit_id) ? $record->GoodsUnit_id : null;
						$this->WhsDocumentSupplySpec_GoodsUnitQty = !empty($record->WhsDocumentSupplySpec_GoodsUnitQty) ? $record->WhsDocumentSupplySpec_GoodsUnitQty : null;
						$this->WhsDocumentSupplySpec_SuppPrice = !empty($record->WhsDocumentSupplySpec_SuppPrice) ? $record->WhsDocumentSupplySpec_SuppPrice : null;
						$this->save();
					break;
					case 'delete':
						$this->delete();
					break;						
				}

				//сохраняем график поставок
				if ($record->state != 'delete' && isset($record->graph_data) && $record->graph_data != '' && $this->WhsDocumentSupplySpec_id > 0) {
					$this->saveDeliveryDataFromJSON(array(
						'WhsDocumentSupply_id' => $data['WhsDocumentSupply_id'],
						'WhsDocumentSupplySpec_id' => $this->WhsDocumentSupplySpec_id,
						'graph_data' => $record->graph_data,
						'pmUser_id' => $data['pmUser_id']
					));
				}
			}
		}
		
	}

	/**
	 * Сохранение
	 */
	function save() {
		$procedure = 'p_WhsDocumentSupplySpec_ins';
		if ( $this->WhsDocumentSupplySpec_id > 0 ) {
			$procedure = 'p_WhsDocumentSupplySpec_upd';
		}
		$q = "
			declare
				@WhsDocumentSupplySpec_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @WhsDocumentSupplySpec_id = :WhsDocumentSupplySpec_id;
			exec dbo." . $procedure . "
				@WhsDocumentSupplySpec_id = @WhsDocumentSupplySpec_id output,
				@WhsDocumentSupply_id = :WhsDocumentSupply_id,
				@WhsDocumentProcurementRequestSpec_id = :WhsDocumentProcurementRequestSpec_id,
				@WhsDocumentSupplySpec_PosCode = :WhsDocumentSupplySpec_PosCode,
				@Drug_id = :Drug_id,
				@DrugComplexMnn_id = :DrugComplexMnn_id,
				@FIRMNAMES_id = :FIRMNAMES_id,
				@WhsDocumentSupplySpec_KolvoForm = :WhsDocumentSupplySpec_KolvoForm,
				@DRUGPACK_id = :DRUGPACK_id,
				@Okei_id = :Okei_id,
				@WhsDocumentSupplySpec_KolvoUnit = :WhsDocumentSupplySpec_KolvoUnit,
				@WhsDocumentSupplySpec_Count = :WhsDocumentSupplySpec_Count,
				@WhsDocumentSupplySpec_Price = :WhsDocumentSupplySpec_Price,
				@WhsDocumentSupplySpec_NDS = :WhsDocumentSupplySpec_NDS,
				@WhsDocumentSupplySpec_SumNDS = :WhsDocumentSupplySpec_SumNDS,
				@WhsDocumentSupplySpec_PriceNDS = :WhsDocumentSupplySpec_PriceNDS,
				@WhsDocumentSupplySpec_ShelfLifePersent = :WhsDocumentSupplySpec_ShelfLifePersent,
				@GoodsUnit_id = :GoodsUnit_id,
				@WhsDocumentSupplySpec_GoodsUnitQty = :WhsDocumentSupplySpec_GoodsUnitQty,
				@WhsDocumentSupplySpec_SuppPrice = :WhsDocumentSupplySpec_SuppPrice,
				@DrugNds_id = :DrugNds_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @WhsDocumentSupplySpec_id as WhsDocumentSupplySpec_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'WhsDocumentSupplySpec_id' => $this->WhsDocumentSupplySpec_id,
			'WhsDocumentSupply_id' => $this->WhsDocumentSupply_id,
			'WhsDocumentProcurementRequestSpec_id' => $this->WhsDocumentProcurementRequestSpec_id,
			'WhsDocumentSupplySpec_PosCode' => $this->WhsDocumentSupplySpec_PosCode,
			'Drug_id' => $this->Drug_id,
			'DrugComplexMnn_id' => $this->DrugComplexMnn_id,
			'FIRMNAMES_id' => $this->FIRMNAMES_id,
			'WhsDocumentSupplySpec_KolvoForm' => $this->WhsDocumentSupplySpec_KolvoForm,
			'DRUGPACK_id' => $this->DRUGPACK_id,
			'Okei_id' => $this->Okei_id,
			'WhsDocumentSupplySpec_KolvoUnit' => !empty($this->WhsDocumentSupplySpec_KolvoUnit) ? $this->WhsDocumentSupplySpec_KolvoUnit : null,
			'WhsDocumentSupplySpec_Count' => !empty($this->WhsDocumentSupplySpec_Count) ? $this->WhsDocumentSupplySpec_Count : null,
			'WhsDocumentSupplySpec_Price' => $this->WhsDocumentSupplySpec_Price,
			'WhsDocumentSupplySpec_NDS' => $this->WhsDocumentSupplySpec_NDS,
			'WhsDocumentSupplySpec_SumNDS' => $this->WhsDocumentSupplySpec_SumNDS,
			'WhsDocumentSupplySpec_PriceNDS' => $this->WhsDocumentSupplySpec_PriceNDS,
			'WhsDocumentSupplySpec_ShelfLifePersent' => $this->WhsDocumentSupplySpec_ShelfLifePersent,
			'GoodsUnit_id' => !empty($this->GoodsUnit_id) ? $this->GoodsUnit_id : null,
			'WhsDocumentSupplySpec_GoodsUnitQty' => !empty($this->WhsDocumentSupplySpec_GoodsUnitQty) ? $this->WhsDocumentSupplySpec_GoodsUnitQty : null,
			'WhsDocumentSupplySpec_SuppPrice' => !empty($this->WhsDocumentSupplySpec_SuppPrice) ? $this->WhsDocumentSupplySpec_SuppPrice : null,
			'DrugNds_id' => $this->getObjectIdByCode('DrugNds', $this->WhsDocumentSupplySpec_NDS),
			'pmUser_id' => $this->pmUser_id,
		);
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
		    $result = $r->result('array');
		    $this->WhsDocumentSupplySpec_id = $result[0]['WhsDocumentSupplySpec_id'];
		} else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * Удаление
	 */
	function delete() {
		$q = "
			delete from
				WhsDocumentDelivery with(rowlock)
			where
				WhsDocumentSupplySpec_id = :WhsDocumentSupplySpec_id
		";
		$r = $this->db->query($q, array(
			'WhsDocumentSupplySpec_id' => $this->WhsDocumentSupplySpec_id
		));

        $q = "
			delete from
				WhsDocumentSupplySpecDrug with(rowlock)
			where
				WhsDocumentSupplySpec_id = :WhsDocumentSupplySpec_id
		";
		$r = $this->db->query($q, array(
			'WhsDocumentSupplySpec_id' => $this->WhsDocumentSupplySpec_id
		));
	
		$q = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec dbo.p_WhsDocumentSupplySpec_del
				@WhsDocumentSupplySpec_id = :WhsDocumentSupplySpec_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array(
			'WhsDocumentSupplySpec_id' => $this->WhsDocumentSupplySpec_id
		));
		if ( is_object($r) ) {
			return $r->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение графика поставки из JSON
	 */
	function saveDeliveryDataFromJSON($data) {
		$dt = (array) json_decode($data['graph_data']);
		foreach($dt as $record) {
			switch ($record->state) {
				case 'add':
				case 'edit':
					$res = $this->saveWhsDocumentDelivery(array(
						'WhsDocumentSupply_id' => $data['WhsDocumentSupply_id'],
						'WhsDocumentSupplySpec_id' => $data['WhsDocumentSupplySpec_id'],
						'WhsDocumentSupplySpec_id' => $data['WhsDocumentSupplySpec_id'],
						'WhsDocumentDelivery_id' => $record->delivery_id,
						'WhsDocumentDelivery_setDT' => $record->date,
						'WhsDocumentDelivery_Kolvo' => $record->amount,
						'pmUser_id' => $record->amount
					));
					break;
				case 'delete':
					$res = $this->deleteWhsDocumentDelivery(array(
						'WhsDocumentDelivery_id' => $record->delivery_id
					));
					break;
			}
		}
	}

	/**
	 * Сохранение элемента графика поставки
	 */
	function saveWhsDocumentDelivery($data) {
		$procedure = 'p_WhsDocumentDelivery_ins';
		if ($data['WhsDocumentDelivery_id'] > 0) {
			$procedure = 'p_WhsDocumentDelivery_upd';
		}
		$q = "
			declare
				@WhsDocumentDelivery_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @WhsDocumentDelivery_id = :WhsDocumentDelivery_id;
			exec dbo." . $procedure . "
				@WhsDocumentDelivery_id = @WhsDocumentDelivery_id output,
				@WhsDocumentSupply_id = :WhsDocumentSupply_id,
				@WhsDocumentSupplySpec_id = :WhsDocumentSupplySpec_id,
				@WhsDocumentDelivery_setDT = :WhsDocumentDelivery_setDT,
				@Okei_id = :Okei_id,
				@WhsDocumentDelivery_Kolvo = :WhsDocumentDelivery_Kolvo,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @WhsDocumentDelivery_id as WhsDocumentDelivery_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";		
		$p = array(
			'WhsDocumentDelivery_id' => $data['WhsDocumentDelivery_id'],
			'WhsDocumentSupply_id' => $data['WhsDocumentSupply_id'],
			'WhsDocumentSupplySpec_id' => $data['WhsDocumentSupplySpec_id'],
			'WhsDocumentDelivery_setDT' => $data['WhsDocumentDelivery_setDT'] != '' ? join('-', array_reverse(explode('.', $data['WhsDocumentDelivery_setDT']))) : '',
			'Okei_id' => $data['WhsDocumentSupplySpec_id'] > 0 ? '120' : '400', //400 - Процент, 120 - Упаковка
			'WhsDocumentDelivery_Kolvo' => $data['WhsDocumentDelivery_Kolvo'],
			'pmUser_id' => $data['pmUser_id']
		);
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
		    $result = $r->result('array');		    
		} else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * Удаление элемента графика поставки
	 */
	function deleteWhsDocumentDelivery($data) {
		$q = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec dbo.p_WhsDocumentDelivery_del
				@WhsDocumentDelivery_id = :WhsDocumentDelivery_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array(
			'WhsDocumentDelivery_id' => $data['WhsDocumentDelivery_id']
		));
		if ( is_object($r) ) {
			return $r->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение дополнительных данных
	 */
	function getWhsDocumentSupplySpecContext($data) {
		$q = "
			select
				d.Drug_Name, --Наименование ЛС
				dcm.DrugComplexMnn_RusName, --Комплексное МНН
				am.RUSNAME as ActMatters_RusName, --МНН
				tn.NAME as Tradename_Name, --Торговое наименование
				rtrim(fn.NAME + ' ' + isnull(c.NAME,'')) as Firm_Name, --Производитель
				rc.REGNUM as Reg_Num, --№ РУ
				df.NAME as DrugForm_Name, --Форма выпуска
				Dose.Value as Drug_Dose, --Дозировка
				Fas.Value as Drug_Fas --Фасовка
			from
				rls.v_Drug d with (nolock)
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				left join rls.NOMEN n with (nolock) on n.NOMEN_ID = d.Drug_id
				left join rls.PREP p with (nolock) on p.Prep_id = d.DrugPrep_id
				left join rls.TRADENAMES tn with (nolock) on tn.TRADENAMES_ID = p.TRADENAMEID
				left join rls.CLSDRUGFORMS df with (nolock) on df.CLSDRUGFORMS_ID = p.DRUGFORMID
				left join rls.REGCERT rc with (nolock) on rc.REGCERT_ID = p.REGCERTID
				left join rls.FIRMS f with (nolock) on f.FIRMS_ID = p.FIRMID
				left join rls.v_COUNTRIES c with (nolock) on c.COUNTRIES_ID = f.COUNTID
				left join rls.FIRMNAMES fn with (nolock) on fn.FIRMNAMES_ID = f.NAMEID
				left join rls.ACTMATTERS am with (nolock) on am.ACTMATTERS_ID = dcmn.ActMatters_id
				left join rls.MASSUNITS mu with (nolock) on mu.MASSUNITS_ID = n.PPACKMASSUNID
				left join rls.CUBICUNITS cu with (nolock) on cu.CUBICUNITS_ID = n.PPACKCUBUNID
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
			where
				d.Drug_id = :Drug_id;
		";
		$r = $this->db->query($q, array(
			'Drug_id' => $data['Drug_id']
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
	 *  Импорт спецификации коммерческого предложения из xls файла.
	 */
	function importFromXls($data) {
		require_once("promed/libraries/Spreadsheet_Excel_Reader/Spreadsheet_Excel_Reader.php");

		$result = array(array('Error_Msg' => null));

		//указываем из каких столбцов файла брать код и цену медикаментов
		$code_col = 3;
		$kolvo_col = 4;
		$price_col = 5;
		$nds_col = 6;
		$data_start = false;
		$drug_array = array();

		$xls_data = new Spreadsheet_Excel_Reader();
		$xls_data->setOutputEncoding('CP1251');
		$xls_data->read($data['FileFullName']);

		if (isset($xls_data->sheets[0])) {
			for ($i = 1; $i <= $xls_data->sheets[0]['numRows']; $i++) {
				if (isset($xls_data->sheets[0]['cells'][$i])) {
					$row = $xls_data->sheets[0]['cells'][$i];

					if ($data_start) {
						$code = isset($row[$code_col]) ? $row[$code_col] : null;
						$kolvo = isset($row[$kolvo_col]) ? $row[$kolvo_col] : null;
						$price = isset($row[$price_col]) ? $row[$price_col] : null;
						$nds = isset($row[$nds_col]) ? $row[$nds_col] : null;
						if ($code !== null && $price !== null && ($code != $code_col || $kolvo != $kolvo_col || $price != $price_col)) {
							$drug = $this->getWhsDocumentSupplySpecImportContext(array('DrugNomen_Code' => $code, 'WhsDocumentUc_pid' => $data['WhsDocumentUc_pid']));

							if (is_array($drug) && count($drug) > 0) {
								$drug = $drug[0];
								if(isset($drug['Drug_id']) && $drug['Drug_id'] > 0) {
									$tmp_array = array(
										'Drug_id' => $drug['Drug_id'],
										'Okei_id' => $drug['Okei_id'],
										'Okei_id_Name' => strip_tags($drug['Okei_id_Name']),
										'WhsDocumentSupplySpec_ShelfLifePersent' => isset($row[8]) ? $row[8] : null,
										'WhsDocumentSupplySpec_NDS' => $nds,
										'DrugNomen_Code' => $code,
										'Drug_Name' => strip_tags($drug['Drug_Name']),
										'ActMatters_RusName' => strip_tags($drug['ActMatters_RusName']),
										'Tradename_Name' => strip_tags($drug['Tradename_Name']),
										'Firm_Name' => strip_tags($drug['Firm_Name']),
										'Reg_Num' => $drug['Reg_Num'],
										'DrugForm_Name' => strip_tags($drug['DrugForm_Name']),
										'Drug_Dose' => $drug['Drug_Dose'],
										'Drug_Fas' => $drug['Drug_Fas'],
										'WhsDocumentSupplySpec_KolvoUnit' => $kolvo,
										'WhsDocumentSupplySpec_Price' => $price > 0 && $nds > 0 ? ($price*100)/($nds+100) : $price
									);
									array_walk($tmp_array, 'ConvertFromWin1251ToUTF8');
									$drug_array[] = $tmp_array;
								}
							}
						}
					} else {
						if (isset($row[1]) && strpos($row[1], '/') > -1) {
							$data_start = true;
						}
					}
				}
			}
		}

		return array('success' => true, 'data' => $drug_array);
	}

	/**
	 * Получение дополнительных данных
	 */
	function getWhsDocumentSupplySpecImportContext($data) {
		$q = "
			declare
				@Okei_id bigint,
				@Okei_id_Name varchar(250);

			select
				@Okei_id = Okei_id,
				@Okei_id_Name = Okei_Name
			from
				v_Okei with(nolock)
			where
				Okei_id = 120;

			select
				dn.Drug_id,
				@Okei_id as Okei_id,
				@Okei_id_Name as Okei_id_Name,
				d.Drug_Name, --Наименование ЛС
				dcm.DrugComplexMnn_RusName, --Комплексное МНН
				am.RUSNAME as ActMatters_RusName, --МНН
				tn.NAME as Tradename_Name, --Торговое наименование
				fn.NAME as Firm_Name, --Производитель
				rc.REGNUM as Reg_Num, --№ РУ
				df.NAME as DrugForm_Name, --Форма выпуска
				Dose.Value as Drug_Dose, --Дозировка
				Fas.Value as Drug_Fas --Фасовка
			from
				rls.v_DrugNomen dn with (nolock)
				left join rls.v_Drug d with (nolock) on d.Drug_id = dn.Drug_id
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				left join rls.NOMEN n with (nolock) on n.NOMEN_ID = d.Drug_id
				left join rls.PREP p with (nolock) on p.Prep_id = d.DrugPrep_id
				left join rls.TRADENAMES tn with (nolock) on tn.TRADENAMES_ID = p.TRADENAMEID
				left join rls.CLSDRUGFORMS df with (nolock) on df.CLSDRUGFORMS_ID = p.DRUGFORMID
				left join rls.REGCERT rc with (nolock) on rc.REGCERT_ID = p.REGCERTID
				left join rls.FIRMS f with (nolock) on f.FIRMS_ID = p.FIRMID
				left join rls.FIRMNAMES fn with (nolock) on fn.FIRMNAMES_ID = f.NAMEID
				left join rls.ACTMATTERS am with (nolock) on am.ACTMATTERS_ID = dcmn.ActMatters_id
				left join rls.MASSUNITS mu with (nolock) on mu.MASSUNITS_ID = n.PPACKMASSUNID
				left join rls.CUBICUNITS cu with (nolock) on cu.CUBICUNITS_ID = n.PPACKCUBUNID
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
			where
				dn.DrugNomen_Code = :DrugNomen_Code and (
					:WhsDocumentUc_pid is null or
					dcm.DrugComplexMnn_id in (
						select
							wdprc.DrugComplexMnn_id
						from
							WhsDocumentProcurementRequestSpec wdprc with (nolock)
						where
							wdprc.WhsDocumentProcurementRequest_id = :WhsDocumentUc_pid
					)
				);
		";
		$r = $this->db->query($q, array(
			'DrugNomen_Code' => $data['DrugNomen_Code'],
			'WhsDocumentUc_pid' => isset($data['WhsDocumentUc_pid']) && $data['WhsDocumentUc_pid'] > 0 ? $data['WhsDocumentUc_pid'] : null
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
	 * Получение списка медикаментов в ГК вместе со списком синонимов
	 */
	function loadWhsDocumentSupplyStrCombo($data) {
		$this->load->model('DocumentUc_model', 'DocumentUc_model');

		$params = array(
			'WhsDocumentSupply_id' => $data['WhsDocumentSupply_id'],
			'DefaultGoodsUnit_id' => $this->DocumentUc_model->getDefaultGoodsUnitId()
		);
		$query = "
			declare @WhsDocumentSupply_id bigint = :WhsDocumentSupply_id;
			select
				'S.'+cast(WDSS.WhsDocumentSupplySpec_id as varchar) as WhsDocumentSupplyStr_id,
				'Spec' as WhsDocumentSupplyStrType,
				WDSS.WhsDocumentSupplySpec_id,
				null as WhsDocumentSupplySpecDrug_id,
				WDSS.WhsDocumentSupply_id,
				WDSS.WhsDocumentSupplySpec_KolvoUnit as WhsDocumentSupplyStr_KolvoUnit,
				WDSS.WhsDocumentSupplySpec_NDS as WhsDocumentSupplyStr_NDS,
				WDSS.WhsDocumentSupplySpec_PriceNDS as WhsDocumentSupplyStr_PriceNDS,
				D.Drug_id,
				D.Drug_Name,
				DCM.DrugComplexMnn_id,
				DCM.DrugComplexMnn_Name,
				GU.GoodsUnit_id,
				GU.GoodsUnit_Name
			from
				v_WhsDocumentSupplySpec WDSS with(nolock)
				left join rls.v_Drug D with(nolock) on D.Drug_id = WDSS.Drug_id
				left join rls.v_DrugComplexMnn DCM with(nolock) on DCM.DrugComplexMnn_id = D.DrugComplexMnn_id
				left join v_GoodsUnit GU with (nolock) on GU.GoodsUnit_id = isnull(WDSS.GoodsUnit_id, :DefaultGoodsUnit_id)
			where
				WDSS.WhsDocumentSupply_id = @WhsDocumentSupply_id
			union all
			select
				'SD.'+cast(WDSSD.WhsDocumentSupplySpecDrug_id as varchar) as WhsDocumentSupplyStr_id,
				'SpecDrug' as WhsDocumentSupplyStrType,
				WDSS.WhsDocumentSupplySpec_id,
				WDSSD.WhsDocumentSupplySpecDrug_id,
				WDSS.WhsDocumentSupply_id,
				WDSS.WhsDocumentSupplySpec_KolvoUnit*WDSSD.WhsDocumentSupplySpecDrug_Coeff as WhsDocumentSupplyStr_KolvoUnit,
				WDSS.WhsDocumentSupplySpec_NDS as WhsDocumentSupplyStr_NDS,
				WDSSD.WhsDocumentSupplySpecDrug_PriceSyn as WhsDocumentSupplyStr_PriceNDS,
				D.Drug_id,
				D.Drug_Name,
				DCM.DrugComplexMnn_id,
				DCM.DrugComplexMnn_Name,
				GU.GoodsUnit_id,
				GU.GoodsUnit_Name
			from
				v_WhsDocumentSupplySpecDrug WDSSD with(nolock)
				left join v_WhsDocumentSupplySpec WDSS with(nolock) on WDSS.WhsDocumentSupplySpec_id = WDSSD.WhsDocumentSupplySpec_id
				left join rls.v_Drug D with(nolock) on D.Drug_id = WDSSD.Drug_sid
				left join rls.v_DrugComplexMnn DCM with(nolock) on DCM.DrugComplexMnn_id = D.DrugComplexMnn_id
				left join v_GoodsUnit GU with (nolock) on GU.GoodsUnit_id = isnull(WDSS.GoodsUnit_id, :DefaultGoodsUnit_id)
			where
				WDSS.WhsDocumentSupply_id = @WhsDocumentSupply_id
		";
		return $this->queryResult($query, $params);
	}
}