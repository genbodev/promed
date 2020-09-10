<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Спецификация договора
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2010-2011 Swan Ltd.
 * @author       ModelGenerator
 * @version
 *
 * @property CI_DB_driver $db
 * @property DocumentUc_model $DocumentUc_model
 */
class WhsDocumentSupplySpec_model extends swPgModel
{
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
	 * @return mixed
	 */
	public function getWhsDocumentSupplySpec_id()
	{
		return $this->WhsDocumentSupplySpec_id;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentSupplySpec_id($value)
	{
		$this->WhsDocumentSupplySpec_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getWhsDocumentSupply_id()
	{
		return $this->WhsDocumentSupply_id;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentSupply_id($value)
	{
		$this->WhsDocumentSupply_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getWhsDocumentSupplySpec_PosCode()
	{
		return $this->WhsDocumentSupplySpec_PosCode;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentSupplySpec_PosCode($value)
	{
		$this->WhsDocumentSupplySpec_PosCode = $value;
	}

	/**
	 * @return mixed
	 */
	public function getDrug_id()
	{
		return $this->Drug_id;
	}

	/**
	 * @param $value
	 */
	public function setDrug_id($value)
	{
		$this->Drug_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getDrugComplexMnn_id()
	{
		return $this->DrugComplexMnn_id;
	}

	/**
	 * @param $value
	 */
	public function setDrugComplexMnn_id($value)
	{
		$this->DrugComplexMnn_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getFIRMNAMES_id()
	{
		return $this->FIRMNAMES_id;
	}

	/**
	 * @param $value
	 */
	public function setFIRMNAMES_id($value)
	{
		$this->FIRMNAMES_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getWhsDocumentSupplySpec_KolvoForm()
	{
		return $this->WhsDocumentSupplySpec_KolvoForm;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentSupplySpec_KolvoForm($value)
	{
		$this->WhsDocumentSupplySpec_KolvoForm = $value;
	}

	/**
	 * @return mixed
	 */
	public function getDRUGPACK_id()
	{
		return $this->DRUGPACK_id;
	}

	/**
	 * @param $value
	 */
	public function setDRUGPACK_id($value)
	{
		$this->DRUGPACK_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getOkei_id()
	{
		return $this->Okei_id;
	}

	/**
	 * @param $value
	 */
	public function setOkei_id($value)
	{
		$this->Okei_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getWhsDocumentSupplySpec_KolvoUnit()
	{
		return $this->WhsDocumentSupplySpec_KolvoUnit;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentSupplySpec_KolvoUnit($value)
	{
		$this->WhsDocumentSupplySpec_KolvoUnit = $value;
	}

	/**
	 * @return mixed
	 */
	public function getWhsDocumentSupplySpec_Count()
	{
		return $this->WhsDocumentSupplySpec_Count;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentSupplySpec_Count($value)
	{
		$this->WhsDocumentSupplySpec_Count = $value;
	}

	/**
	 * @return mixed
	 */
	public function getWhsDocumentSupplySpec_Price()
	{
		return $this->WhsDocumentSupplySpec_Price;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentSupplySpec_Price($value)
	{
		$this->WhsDocumentSupplySpec_Price = $value;
	}

	/**
	 * @return mixed
	 */
	public function getWhsDocumentSupplySpec_NDS()
	{
		return $this->WhsDocumentSupplySpec_NDS;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentSupplySpec_NDS($value)
	{
		$this->WhsDocumentSupplySpec_NDS = $value;
	}

	/**
	 * @return mixed
	 */
	public function getWhsDocumentSupplySpec_SumNDS()
	{
		return $this->WhsDocumentSupplySpec_SumNDS;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentSupplySpec_SumNDS($value)
	{
		$this->WhsDocumentSupplySpec_SumNDS = $value;
	}

	/**
	 * @return mixed
	 */
	public function getWhsDocumentSupplySpec_PriceNDS()
	{
		return $this->WhsDocumentSupplySpec_PriceNDS;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentSupplySpec_PriceNDS($value)
	{
		$this->WhsDocumentSupplySpec_PriceNDS = $value;
	}

	/**
	 * @return mixed
	 */
	public function getWhsDocumentSupplySpec_ShelfLifePersent()
	{
		return $this->WhsDocumentSupplySpec_ShelfLifePersent;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentSupplySpec_ShelfLifePersent($value)
	{
		$this->WhsDocumentSupplySpec_ShelfLifePersent = $value;
	}

	/**
	 * @return mixed
	 */
	public function getGoodsUnit_id()
	{
		return $this->GoodsUnit_id;
	}

	/**
	 * @param $value
	 */
	public function setGoodsUnit_id($value)
	{
		$this->GoodsUnit_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getWhsDocumentSupplySpec_GoodsUnitQty()
	{
		return $this->WhsDocumentSupplySpec_GoodsUnitQty;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentSupplySpec_GoodsUnitQty($value)
	{
		$this->WhsDocumentSupplySpec_GoodsUnitQty = $value;
	}

	/**
	 * @return mixed
	 */
	public function getWhsDocumentSupplySpec_SuppPrice()
	{
		return $this->WhsDocumentSupplySpec_SuppPrice;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentSupplySpec_SuppPrice($value)
	{
		$this->WhsDocumentSupplySpec_SuppPrice = $value;
	}

	/**
	 * @return mixed
	 */
	public function getpmUser_id()
	{
		return $this->pmUser_id;
	}

	/**
	 * @param $value
	 */
	public function setpmUser_id($value)
	{
		$this->pmUser_id = $value;
	}

	/**
	 * WhsDocumentSupplySpec_model constructor.
	 * @throws Exception
	 */
	function __construct()
	{
		parent::__construct();
		if (!isset($_SESSION["pmuser_id"])) {
			throw new Exception("Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)");
		}
		$this->setpmUser_id($_SESSION["pmuser_id"]);
	}

	/**
	 * Загрузка
	 * @return array|bool|CI_DB_result
	 */
	function load()
	{
		$query = "
			select
				WhsDocumentSupplySpec_id as \"WhsDocumentSupplySpec_id\",
				WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
				WhsDocumentSupplySpec_PosCode as \"WhsDocumentSupplySpec_PosCode\",
				DrugComplexMnn_id as \"DrugComplexMnn_id\",
				FIRMNAMES_id as \"FIRMNAMES_id\",
				WhsDocumentSupplySpec_KolvoForm as \"WhsDocumentSupplySpec_KolvoForm\",
				DRUGPACK_id as \"DRUGPACK_id\",
				Okei_id as \"Okei_id\",
				WhsDocumentSupplySpec_KolvoUnit as \"WhsDocumentSupplySpec_KolvoUnit\",
				WhsDocumentSupplySpec_Count as \"WhsDocumentSupplySpec_Count\",
				WhsDocumentSupplySpec_Price as \"WhsDocumentSupplySpec_Price\",
				WhsDocumentSupplySpec_NDS as \"WhsDocumentSupplySpec_NDS\",
				WhsDocumentSupplySpec_SumNDS as \"WhsDocumentSupplySpec_SumNDS\",
				WhsDocumentSupplySpec_PriceNDS as \"WhsDocumentSupplySpec_PriceNDS\",
				WhsDocumentSupplySpec_ShelfLifePersent as \"WhsDocumentSupplySpec_ShelfLifePersent\",
				GoodsUnit_id as \"GoodsUnit_id\",
				WhsDocumentSupplySpec_GoodsUnitQty as \"WhsDocumentSupplySpec_GoodsUnitQty\",
				WhsDocumentSupplySpec_SuppPrice as \"WhsDocumentSupplySpec_SuppPrice\"
			from dbo.v_WhsDocumentSupplySpec
			where WhsDocumentSupplySpec_id = :WhsDocumentSupplySpec_id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, ["WhsDocumentSupplySpec_id" => $this->WhsDocumentSupplySpec_id]);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result_array();
		if (!isset($result[0])) {
			return false;
		}
		$this->WhsDocumentSupplySpec_id = $result[0]["WhsDocumentSupplySpec_id"];
		$this->WhsDocumentSupply_id = $result[0]["WhsDocumentSupply_id"];
		$this->WhsDocumentSupplySpec_PosCode = $result[0]["WhsDocumentSupplySpec_PosCode"];
		$this->DrugComplexMnn_id = $result[0]["DrugComplexMnn_id"];
		$this->FIRMNAMES_id = $result[0]["FIRMNAMES_id"];
		$this->WhsDocumentSupplySpec_KolvoForm = $result[0]["WhsDocumentSupplySpec_KolvoForm"];
		$this->DRUGPACK_id = $result[0]["DRUGPACK_id"];
		$this->Okei_id = $result[0]["Okei_id"];
		$this->WhsDocumentSupplySpec_KolvoUnit = $result[0]["WhsDocumentSupplySpec_KolvoUnit"];
		$this->WhsDocumentSupplySpec_Count = $result[0]["WhsDocumentSupplySpec_Count"];
		$this->WhsDocumentSupplySpec_Price = $result[0]["WhsDocumentSupplySpec_Price"];
		$this->WhsDocumentSupplySpec_NDS = $result[0]["WhsDocumentSupplySpec_NDS"];
		$this->WhsDocumentSupplySpec_SumNDS = $result[0]["WhsDocumentSupplySpec_SumNDS"];
		$this->WhsDocumentSupplySpec_PriceNDS = $result[0]["WhsDocumentSupplySpec_PriceNDS"];
		$this->WhsDocumentSupplySpec_ShelfLifePersent = $result[0]["WhsDocumentSupplySpec_ShelfLifePersent"];
		$this->GoodsUnit_id = $result[0]["GoodsUnit_id"];
		$this->WhsDocumentSupplySpec_GoodsUnitQty = $result[0]["WhsDocumentSupplySpec_GoodsUnitQty"];
		$this->WhsDocumentSupplySpec_SuppPrice = $result[0]["WhsDocumentSupplySpec_SuppPrice"];
		return $result;
	}

	/**
	 * Загрузка списка
	 * @param $filter
	 * @return array|bool
	 */
	function loadList($filter)
	{
		$where = [];
		$params = [];
		$fields = [
			"WhsDocumentSupplySpec_id",
			"WhsDocumentSupply_id",
			"WhsDocumentSupplySpec_PosCode",
			"DrugComplexMnn_id",
			"FIRMNAMES_id",
			"WhsDocumentSupplySpec_KolvoForm",
			"DRUGPACK_id",
			"Okei_id",
			"WhsDocumentSupplySpec_KolvoUnit",
			"WhsDocumentSupplySpec_Count",
			"WhsDocumentSupplySpec_Price",
			"WhsDocumentSupplySpec_NDS",
			"WhsDocumentSupplySpec_SumNDS",
			"WhsDocumentSupplySpec_PriceNDS",
			"WhsDocumentSupplySpec_ShelfLifePersent"
		];
		foreach ($fields as $field) {
			if (isset($filter[$field]) && $filter[$field]) {
				$where[] = "v_WhsDocumentSupplySpec.{$field} = :{$field}";
				$params[$field] = $filter[$field];
			}
		}
		$whereString = (count($where) != 0) ? "where " . implode(" and ", $where) : "";
		$orderString = "ORDER BY WhsDocumentSupplySpec_PosCode ASC ";
		$query = "
			select
				v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_id as \"WhsDocumentSupplySpec_id\",
				v_WhsDocumentSupplySpec.WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
				v_WhsDocumentSupplySpec.WhsDocumentProcurementRequestSpec_id as \"WhsDocumentProcurementRequestSpec_id\",
				v_WhsDocumentSupplySpec.Drug_id as \"Drug_id\",
				d.Drug_Name as \"Drug_Name\",
				v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_PosCode as \"WhsDocumentSupplySpec_PosCode\",
				v_WhsDocumentSupplySpec.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				v_WhsDocumentSupplySpec.FIRMNAMES_id as \"FIRMNAMES_id\",
				v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_KolvoForm as \"WhsDocumentSupplySpec_KolvoForm\",
				v_WhsDocumentSupplySpec.DRUGPACK_id as \"DRUGPACK_id\",
				v_WhsDocumentSupplySpec.Okei_id as \"Okei_id\",
				v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_KolvoUnit as \"WhsDocumentSupplySpec_KolvoUnit\",
				v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_Count as \"WhsDocumentSupplySpec_Count\",
				v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_Price as \"WhsDocumentSupplySpec_Price\",
				v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_NDS as \"WhsDocumentSupplySpec_NDS\",
				v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_SumNDS as \"WhsDocumentSupplySpec_SumNDS\",
				v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_PriceNDS as \"WhsDocumentSupplySpec_PriceNDS\",
				v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_ShelfLifePersent as \"WhsDocumentSupplySpec_ShelfLifePersent\",
				v_WhsDocumentSupplySpec.GoodsUnit_id as \"GoodsUnit_id\",
				v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_GoodsUnitQty as \"WhsDocumentSupplySpec_GoodsUnitQty\",
				v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_SuppPrice as \"WhsDocumentSupplySpec_SuppPrice\",
				coalesce(DrugComplexMnn_id_ref.DrugComplexMnn_RusName, dcm.DrugComplexMnn_RusName) as \"DrugComplexMnn_RusName\",
				v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_id as \"grid_id\",
				Okei_id_ref.Okei_Name as \"Okei_id_Name\",
				am.ACTMATTERS_ID as \"Actmatters_id\", --МНН
				am.RUSNAME as \"ActMatters_RusName\", --МНН
				tn.NAME as \"Tradename_Name\", --Торговое наименование
				rtrim(fn.NAME || ' ' || COALESCE(c.NAME,'')) as \"Firm_Name\", --Производитель
				rc.REGNUM as \"Reg_Num\", --№ РУ
				df.NAME as \"DrugForm_Name\", --Форма выпуска
				Dose.Value as \"Drug_Dose\", --Дозировка
				Fas.Value as \"Drug_Fas\", --Фасовка
				DrugNomen.DrugNomen_Code as \"DrugNomen_Code\",
				dnds.DrugNds_id as \"DrugNds_id\",
				v_WhsDocumentSupplySpec.Okei_id as \"Okei_id\",
				CODPD.CommercialOfferDrug_PriceDetail as \"CommercialOfferDrug_PriceDetail\"
			FROM
				dbo.v_WhsDocumentSupplySpec
				left join dbo.v_WhsDocumentSupply WhsDocumentSupply_id_ref on WhsDocumentSupply_id_ref.WhsDocumentSupply_id = v_WhsDocumentSupplySpec.WhsDocumentSupply_id
				left join rls.v_DrugComplexMnn DrugComplexMnn_id_ref on DrugComplexMnn_id_ref.DrugComplexMnn_id = v_WhsDocumentSupplySpec.DrugComplexMnn_id
				left join rls.v_FIRMNAMES FIRMNAMES_id_ref on FIRMNAMES_id_ref.FIRMNAMES_id = v_WhsDocumentSupplySpec.FIRMNAMES_id
				left join rls.v_DRUGPACK DRUGPACK_id_ref on DRUGPACK_id_ref.DRUGPACK_id = v_WhsDocumentSupplySpec.DRUGPACK_id
				left join dbo.v_Okei Okei_id_ref on Okei_id_ref.Okei_id = v_WhsDocumentSupplySpec.Okei_id
				left join rls.v_Drug d on d.Drug_id = v_WhsDocumentSupplySpec.Drug_id
				left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				left join rls.NOMEN n on n.NOMEN_ID = d.Drug_id
				left join rls.PREP p on p.Prep_id = d.DrugPrep_id
				left join rls.TRADENAMES tn on tn.TRADENAMES_ID = p.TRADENAMEID
				left join rls.CLSDRUGFORMS df on df.CLSDRUGFORMS_ID = p.DRUGFORMID
				left join rls.REGCERT rc on rc.REGCERT_ID = p.REGCERTID
				left join rls.FIRMS f on f.FIRMS_ID = p.FIRMID
				left join rls.FIRMNAMES fn on fn.FIRMNAMES_ID = f.NAMEID
				left join rls.v_COUNTRIES c on c.COUNTRIES_ID = f.COUNTID
				left join rls.ACTMATTERS am on am.ACTMATTERS_ID = dcmn.ActMatters_id
				left join rls.MASSUNITS mu on mu.MASSUNITS_ID = n.PPACKMASSUNID
				left join rls.CUBICUNITS cu on cu.CUBICUNITS_ID = n.PPACKCUBUNID
				left join rls.MASSUNITS df_mu on df_mu.MASSUNITS_ID = p.DFMASSID
				left join rls.CONCENUNITS df_cu on df_cu.CONCENUNITS_ID = p.DFCONCID
				left join rls.ACTUNITS df_au on df_au.ACTUNITS_ID = p.DFACTID
				left join rls.SIZEUNITS df_su on df_su.SIZEUNITS_ID = p.DFSIZEID
				left join dbo.v_DrugNds as dnds on dnds.DrugNds_Code = v_WhsDocumentSupplySpec.WhsDocumentSupplySpec_NDS
				left join lateral (
					select CommercialOfferDrug_PriceDetail
					from v_CommercialOfferDrug
					where Drug_id = d.Drug_id
				    limit 1
				) as CODPD on true
				left join lateral (
					select coalesce(
						p.DFMASS::float::varchar||' '||df_mu.SHORTNAME,
						p.DFCONC::float::varchar||' '||df_cu.SHORTNAME,
						p.DFACT::varchar||' '||df_au.SHORTNAME,
						p.DFSIZE::varchar||' '||df_su.SHORTNAME
					) as Value
				) as Dose on true
				left join lateral (
					select
						replace(replace((
							case when coalesce(p.DRUGDOSE, 0) > 0
								then p.DRUGDOSE::float::varchar||' доз, '
								else ''
							end
						)||
						coalesce(coalesce(n.PPACKMASS::float::varchar||' '||mu.SHORTNAME, n.PPACKVOLUME::float::varchar||' '||cu.SHORTNAME)||', ','')||
						(
							case when coalesce(n.DRUGSINPPACK, 0) > 0
								then
									'№ '||(
									    case when coalesce(n.PPACKINUPACK, 0) > 0
											then (n.DRUGSINPPACK*n.PPACKINUPACK)::varchar
											else n.DRUGSINPPACK::varchar
										end
									)
								else
									case when coalesce(n.PPACKINUPACK, 0) > 0
										then '№ '||n.PPACKINUPACK::varchar
										else ''
									end
							end
						)||',,', ', ,,', ''), ',,', '') as Value
				) as Fas on true
				left join lateral (
					select v_DrugNomen.DrugNomen_Code
					from rls.v_DrugNomen
					where v_DrugNomen.Drug_id = d.Drug_id
					order by DrugNomen_id
				    limit 1
				) as DrugNomen on true
			{$whereString}
			{$orderString}
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Сохранение из JSON
	 * @param $data
	 * @throws Exception
	 */
	function saveFromJSON($data)
	{
		if (!empty($data["json_str"]) && $data["WhsDocumentSupply_id"] > 0) {
			ConvertFromWin1251ToUTF8($data["json_str"]);
			$dt = (array)json_decode($data["json_str"]);
			foreach ($dt as $record) {
				$this->WhsDocumentSupplySpec_id = $record->state == "add" ? null : $record->WhsDocumentSupplySpec_id;
				$this->pmUser_id = $data["pmUser_id"];
				switch ($record->state) {
					case "add":
					case "edit":
						$this->WhsDocumentSupply_id = $data["WhsDocumentSupply_id"];
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
					case "delete":
						$this->delete();
						break;
				}

				//сохраняем график поставок
				if ($record->state != "delete" && isset($record->graph_data) && $record->graph_data != "" && $this->WhsDocumentSupplySpec_id > 0) {
					$funcParams = [
						"WhsDocumentSupply_id" => $data["WhsDocumentSupply_id"],
						"WhsDocumentSupplySpec_id" => $this->WhsDocumentSupplySpec_id,
						"graph_data" => $record->graph_data,
						"pmUser_id" => $data["pmUser_id"]
					];
					$this->saveDeliveryDataFromJSON($funcParams);
				}
			}
		}
	}

	/**
	 * Сохранение
	 * @return array|CI_DB_result
	 * @throws Exception
	 */
	function save()
	{
		$procedure = ($this->WhsDocumentSupplySpec_id > 0)?"p_WhsDocumentSupplySpec_upd":"p_WhsDocumentSupplySpec_ins";
		$selectString = "
		    whsdocumentsupplyspec_id as \"WhsDocumentSupplySpec_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    whsdocumentsupplyspec_id := :WhsDocumentSupplySpec_id,
			    whsdocumentsupply_id := :WhsDocumentSupply_id,
			    whsdocumentsupplyspec_poscode := cast(:WhsDocumentSupplySpec_PosCode as varchar),
			    drugcomplexmnn_id := :DrugComplexMnn_id,
			    firmnames_id := :FIRMNAMES_id,
			    whsdocumentsupplyspec_kolvoform := :WhsDocumentSupplySpec_KolvoForm,
			    drugpack_id := :DRUGPACK_id,
			    okei_id := :Okei_id,
			    whsdocumentsupplyspec_kolvounit := :WhsDocumentSupplySpec_KolvoUnit,
			    whsdocumentsupplyspec_count := :WhsDocumentSupplySpec_Count,
			    whsdocumentsupplyspec_price := :WhsDocumentSupplySpec_Price,
			    whsdocumentsupplyspec_nds := :WhsDocumentSupplySpec_NDS,
			    whsdocumentsupplyspec_sumnds := :WhsDocumentSupplySpec_SumNDS,
			    whsdocumentsupplyspec_pricends := :WhsDocumentSupplySpec_PriceNDS,
			    whsdocumentsupplyspec_shelflifepersent := :WhsDocumentSupplySpec_ShelfLifePersent,
			    drug_id := :Drug_id,
			    whsdocumentprocurementrequestspec_id := :WhsDocumentProcurementRequestSpec_id,
			    goodsunit_id := :GoodsUnit_id,
			    whsdocumentsupplyspec_goodsunitqty := :WhsDocumentSupplySpec_GoodsUnitQty,
			    whsdocumentsupplyspec_suppprice := :WhsDocumentSupplySpec_SuppPrice,
			    drugnds_id := :DrugNds_id,
			    pmuser_id := :pmUser_id
			);
		";
		$params = [
			"WhsDocumentSupplySpec_id" => $this->WhsDocumentSupplySpec_id,
			"WhsDocumentSupply_id" => $this->WhsDocumentSupply_id,
			"WhsDocumentProcurementRequestSpec_id" => $this->WhsDocumentProcurementRequestSpec_id,
			"WhsDocumentSupplySpec_PosCode" => $this->WhsDocumentSupplySpec_PosCode,
			"Drug_id" => $this->Drug_id,
			"DrugComplexMnn_id" => $this->DrugComplexMnn_id,
			"FIRMNAMES_id" => $this->FIRMNAMES_id,
			"WhsDocumentSupplySpec_KolvoForm" => $this->WhsDocumentSupplySpec_KolvoForm,
			"DRUGPACK_id" => $this->DRUGPACK_id,
			"Okei_id" => $this->Okei_id,
			"WhsDocumentSupplySpec_KolvoUnit" => !empty($this->WhsDocumentSupplySpec_KolvoUnit) ? $this->WhsDocumentSupplySpec_KolvoUnit : null,
			"WhsDocumentSupplySpec_Count" => !empty($this->WhsDocumentSupplySpec_Count) ? $this->WhsDocumentSupplySpec_Count : null,
			"WhsDocumentSupplySpec_Price" => $this->WhsDocumentSupplySpec_Price,
			"WhsDocumentSupplySpec_NDS" => $this->WhsDocumentSupplySpec_NDS,
			"WhsDocumentSupplySpec_SumNDS" => $this->WhsDocumentSupplySpec_SumNDS,
			"WhsDocumentSupplySpec_PriceNDS" => $this->WhsDocumentSupplySpec_PriceNDS,
			"WhsDocumentSupplySpec_ShelfLifePersent" => $this->WhsDocumentSupplySpec_ShelfLifePersent,
			"GoodsUnit_id" => !empty($this->GoodsUnit_id) ? $this->GoodsUnit_id : null,
			"WhsDocumentSupplySpec_GoodsUnitQty" => !empty($this->WhsDocumentSupplySpec_GoodsUnitQty) ? $this->WhsDocumentSupplySpec_GoodsUnitQty : null,
			"WhsDocumentSupplySpec_SuppPrice" => !empty($this->WhsDocumentSupplySpec_SuppPrice) ? $this->WhsDocumentSupplySpec_SuppPrice : null,
			"DrugNds_id" => $this->getObjectIdByCode("DrugNds", $this->WhsDocumentSupplySpec_NDS),
			"pmUser_id" => $this->pmUser_id,
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			log_message("error", var_export(["query" => $query, "params" => $params, "e" => sqlsrv_errors()], true));
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		$result = $result->result_array();
		$this->WhsDocumentSupplySpec_id = $result[0]["WhsDocumentSupplySpec_id"];
		return $result;
	}

	/**
	 * Удаление
	 * @return array|bool
	 */
	function delete()
	{
		/**@var CI_DB_result $result */
		$queryParams = ["WhsDocumentSupplySpec_id" => $this->WhsDocumentSupplySpec_id];
		$query = "
			delete from WhsDocumentDelivery
			where WhsDocumentSupplySpec_id = :WhsDocumentSupplySpec_id
		";
		$this->db->query($query, $queryParams);

		$query = "
			delete from WhsDocumentSupplySpecDrug
			where WhsDocumentSupplySpec_id = :WhsDocumentSupplySpec_id
		";
		$this->db->query($query, $queryParams);
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_whsdocumentsupplyspec_del(whsdocumentsupplyspec_id := :WhsDocumentSupplySpec_id);
		";
		$result = $this->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Сохранение графика поставки из JSON
	 * @param $data
	 * @throws Exception
	 */
	function saveDeliveryDataFromJSON($data)
	{
		$dt = (array)json_decode($data["graph_data"]);
		foreach ($dt as $record) {
			switch ($record->state) {
				case "add":
				case "edit":
					$funcParams = [
						"WhsDocumentSupply_id" => $data["WhsDocumentSupply_id"],
						"WhsDocumentSupplySpec_id" => $data["WhsDocumentSupplySpec_id"],
						"WhsDocumentDelivery_id" => $record->delivery_id,
						"WhsDocumentDelivery_setDT" => $record->date,
						"WhsDocumentDelivery_Kolvo" => $record->amount,
						"pmUser_id" => $record->amount
					];
					$this->saveWhsDocumentDelivery($funcParams);
					break;
				case "delete":
					$this->deleteWhsDocumentDelivery(["WhsDocumentDelivery_id" => $record->delivery_id]);
					break;
			}
		}
	}

	/**
	 * Сохранение элемента графика поставки
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function saveWhsDocumentDelivery($data)
	{
		$procedure = ($data["WhsDocumentDelivery_id"] > 0) ? "p_WhsDocumentDelivery_upd" : "p_WhsDocumentDelivery_ins";
		$selectString = "
		    whsdocumentdelivery_id as \"WhsDocumentDelivery_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    whsdocumentdelivery_id := :WhsDocumentDelivery_id,
			    whsdocumentsupply_id := :WhsDocumentSupply_id,
			    whsdocumentsupplyspec_id := :WhsDocumentSupplySpec_id,
			    whsdocumentdelivery_setdt := :WhsDocumentDelivery_setDT,
			    okei_id := :Okei_id,
			    whsdocumentdelivery_kolvo := :WhsDocumentDelivery_Kolvo,
			    pmuser_id := :pmUser_id
			);
		";
		$params = [
			"WhsDocumentDelivery_id" => $data["WhsDocumentDelivery_id"],
			"WhsDocumentSupply_id" => $data["WhsDocumentSupply_id"],
			"WhsDocumentSupplySpec_id" => $data["WhsDocumentSupplySpec_id"],
			"WhsDocumentDelivery_setDT" => $data["WhsDocumentDelivery_setDT"] != "" ? join("-", array_reverse(explode('.', $data["WhsDocumentDelivery_setDT"]))) : "",
			"Okei_id" => $data["WhsDocumentSupplySpec_id"] > 0 ? "120" : "400",
			"WhsDocumentDelivery_Kolvo" => $data["WhsDocumentDelivery_Kolvo"],
			"pmUser_id" => $data["pmUser_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			log_message("error", var_export(["query" => $query, "params" => $params, "e" => sqlsrv_errors()], true));
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		return $result->result_array();
	}

	/**
	 * Удаление элемента графика поставки
	 * @param $data
	 * @return array|bool
	 */
	function deleteWhsDocumentDelivery($data)
	{
		/**@var CI_DB_result $result */
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_whsdocumentdelivery_del(whsdocumentdelivery_id := :WhsDocumentDelivery_id);
		";
		$result = $this->db->query($query, ["WhsDocumentDelivery_id" => $data["WhsDocumentDelivery_id"]]);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Получение дополнительных данных
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	function getWhsDocumentSupplySpecContext($data)
	{
		$query = "
			select
				d.Drug_Name as \"Drug_Name\", --Наименование ЛС
				dcm.DrugComplexMnn_RusName as \"DrugComplexMnn_RusName\", --Комплексное МНН
				am.RUSNAME as \"ActMatters_RusName\", --МНН
				tn.NAME as \"Tradename_Name\", --Торговое наименование
				rtrim(fn.NAME || ' ' || COALESCE(c.NAME,'')) as \"Firm_Name\", --Производитель
				rc.REGNUM as \"Reg_Num\", --№ РУ
				df.NAME as \"DrugForm_Name\", --Форма выпуска
				Dose.Value as \"Drug_Dose\", --Дозировка
				Fas.Value as \"Drug_Fas\" --Фасовка
			from
				rls.v_Drug d
				left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				left join rls.NOMEN n on n.NOMEN_ID = d.Drug_id
				left join rls.PREP p on p.Prep_id = d.DrugPrep_id
				left join rls.TRADENAMES tn on tn.TRADENAMES_ID = p.TRADENAMEID
				left join rls.CLSDRUGFORMS df on df.CLSDRUGFORMS_ID = p.DRUGFORMID
				left join rls.REGCERT rc on rc.REGCERT_ID = p.REGCERTID
				left join rls.FIRMS f on f.FIRMS_ID = p.FIRMID
				left join rls.v_COUNTRIES c on c.COUNTRIES_ID = f.COUNTID
				left join rls.FIRMNAMES fn on fn.FIRMNAMES_ID = f.NAMEID
				left join rls.ACTMATTERS am on am.ACTMATTERS_ID = dcmn.ActMatters_id
				left join rls.MASSUNITS mu on mu.MASSUNITS_ID = n.PPACKMASSUNID
				left join rls.CUBICUNITS cu on cu.CUBICUNITS_ID = n.PPACKCUBUNID
				left join rls.MASSUNITS df_mu on df_mu.MASSUNITS_ID = p.DFMASSID
				left join rls.CONCENUNITS df_cu on df_cu.CONCENUNITS_ID = p.DFCONCID
				left join rls.ACTUNITS df_au on df_au.ACTUNITS_ID = p.DFACTID
				left join rls.SIZEUNITS df_su on df_su.SIZEUNITS_ID = p.DFSIZEID
				left join lateral (
					select coalesce(
						p.DFMASS::float::varchar||' '||df_mu.SHORTNAME,
						p.DFCONC::float::varchar||' '||df_cu.SHORTNAME,
						p.DFACT::varchar||' '||df_au.SHORTNAME,
						p.DFSIZE::varchar||' '||df_su.SHORTNAME
					) as Value
				) as Dose on true
				left join lateral (
					select
						replace(
						    replace(
						        (case when coalesce(p.DRUGDOSE, 0) > 0 then p.DRUGDOSE::float::varchar||' доз, ' else '' end)||
								coalesce(coalesce(n.PPACKMASS::float::varchar||' '||mu.SHORTNAME, n.PPACKVOLUME::float::varchar||' '||cu.SHORTNAME)||', ', '')||
								(
									case when coalesce(n.DRUGSINPPACK, 0) > 0
										then '№ '||(case when coalesce(n.PPACKINUPACK, 0) > 0 then (n.DRUGSINPPACK*n.PPACKINUPACK)::varchar else n.DRUGSINPPACK::varchar end)
										else case when coalesce(n.PPACKINUPACK, 0) > 0 then '№ '||n.PPACKINUPACK::varchar else '' end
									end
								)||',,', ', ,,', ''), ',,', ''
						) as Value
				) as Fas on true
			where d.Drug_id = :Drug_id;
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, ["Drug_id" => $data["Drug_id"]]);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result_array();
		return (isset($result[0])) ? $result : false;
	}

	/**
	 * Импорт спецификации коммерческого предложения из xls файла.
	 * @param $data
	 * @return array
	 */
	function importFromXls($data)
	{
		require_once("promed/libraries/Spreadsheet_Excel_Reader/Spreadsheet_Excel_Reader.php");
		//указываем из каких столбцов файла брать код и цену медикаментов
		$code_col = 3;
		$kolvo_col = 4;
		$price_col = 5;
		$nds_col = 6;
		$data_start = false;
		$drug_array = [];
		$xls_data = new Spreadsheet_Excel_Reader();
		$xls_data->setOutputEncoding("CP1251");
		$xls_data->read($data["FileFullName"]);
		if (isset($xls_data->sheets[0])) {
			for ($i = 1; $i <= $xls_data->sheets[0]["numRows"]; $i++) {
				if (isset($xls_data->sheets[0]["cells"][$i])) {
					$row = $xls_data->sheets[0]["cells"][$i];
					if ($data_start) {
						$code = isset($row[$code_col]) ? $row[$code_col] : null;
						$kolvo = isset($row[$kolvo_col]) ? $row[$kolvo_col] : null;
						$price = isset($row[$price_col]) ? $row[$price_col] : null;
						$nds = isset($row[$nds_col]) ? $row[$nds_col] : null;
						if ($code !== null && $price !== null && ($code != $code_col || $kolvo != $kolvo_col || $price != $price_col)) {
							$drug = $this->getWhsDocumentSupplySpecImportContext(["DrugNomen_Code" => $code, "WhsDocumentUc_pid" => $data["WhsDocumentUc_pid"]]);
							if (is_array($drug) && count($drug) > 0) {
								$drug = $drug[0];
								if (isset($drug["Drug_id"]) && $drug["Drug_id"] > 0) {
									$tmp_array = [
										"Drug_id" => $drug["Drug_id"],
										"Okei_id" => $drug["Okei_id"],
										"Okei_id_Name" => strip_tags($drug["Okei_id_Name"]),
										"WhsDocumentSupplySpec_ShelfLifePersent" => isset($row[8]) ? $row[8] : null,
										"WhsDocumentSupplySpec_NDS" => $nds,
										"DrugNomen_Code" => $code,
										"Drug_Name" => strip_tags($drug["Drug_Name"]),
										"ActMatters_RusName" => strip_tags($drug["ActMatters_RusName"]),
										"Tradename_Name" => strip_tags($drug["Tradename_Name"]),
										"Firm_Name" => strip_tags($drug["Firm_Name"]),
										"Reg_Num" => $drug["Reg_Num"],
										"DrugForm_Name" => strip_tags($drug["DrugForm_Name"]),
										"Drug_Dose" => $drug["Drug_Dose"],
										"Drug_Fas" => $drug["Drug_Fas"],
										"WhsDocumentSupplySpec_KolvoUnit" => $kolvo,
										"WhsDocumentSupplySpec_Price" => $price > 0 && $nds > 0 ? ($price * 100) / ($nds + 100) : $price
									];
									array_walk($tmp_array, "ConvertFromWin1251ToUTF8");
									$drug_array[] = $tmp_array;
								}
							}
						}
					} else {
						if (isset($row[1]) && strpos($row[1], "/") > -1) {
							$data_start = true;
						}
					}
				}
			}
		}
		return ["success" => true, "data" => $drug_array];
	}

	/**
	 * Получение дополнительных данных
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	function getWhsDocumentSupplySpecImportContext($data)
	{
		$query = "
			select
				dn.Drug_id as \"Drug_id\",
				Okei.okei_id as \"Okei_id\",
				Okei.okei_name as \"Okei_id_Name\",
				d.Drug_Name as \"Drug_Name\",
				dcm.DrugComplexMnn_RusName as \"DrugComplexMnn_RusName\",
				am.RUSNAME as \"ActMatters_RusName\",
				tn.NAME as \"Tradename_Name\",
				fn.NAME as \"Firm_Name\",
				rc.REGNUM as \"Reg_Num\",
				df.NAME as \"DrugForm_Name\",
				Dose.Value as \"Drug_Dose\",
				Fas.Value as \"Drug_Fas\"
			from
				rls.v_DrugNomen dn
				left join rls.v_Drug d on d.Drug_id = dn.Drug_id
				left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				left join rls.NOMEN n on n.NOMEN_ID = d.Drug_id
				left join rls.PREP p on p.Prep_id = d.DrugPrep_id
				left join rls.TRADENAMES tn on tn.TRADENAMES_ID = p.TRADENAMEID
				left join rls.CLSDRUGFORMS df on df.CLSDRUGFORMS_ID = p.DRUGFORMID
				left join rls.REGCERT rc on rc.REGCERT_ID = p.REGCERTID
				left join rls.FIRMS f on f.FIRMS_ID = p.FIRMID
				left join rls.FIRMNAMES fn on fn.FIRMNAMES_ID = f.NAMEID
				left join rls.ACTMATTERS am on am.ACTMATTERS_ID = dcmn.ActMatters_id
				left join rls.MASSUNITS mu on mu.MASSUNITS_ID = n.PPACKMASSUNID
				left join rls.CUBICUNITS cu on cu.CUBICUNITS_ID = n.PPACKCUBUNID
				left join rls.MASSUNITS df_mu on df_mu.MASSUNITS_ID = p.DFMASSID
				left join rls.CONCENUNITS df_cu on df_cu.CONCENUNITS_ID = p.DFCONCID
				left join rls.ACTUNITS df_au on df_au.ACTUNITS_ID = p.DFACTID
				left join rls.SIZEUNITS df_su on df_su.SIZEUNITS_ID = p.DFSIZEID
				left join lateral (
				    select
				    	Okei_id,
				        Okei_Name
				    from v_Okei
				    where Okei_id = 120
				) as Okei on true
				left join lateral (
					select coalesce(
						p.DFMASS::float::varchar||' '||df_mu.SHORTNAME,
						p.DFCONC::float::varchar||' '||df_cu.SHORTNAME,
						p.DFACT::varchar||' '||df_au.SHORTNAME,
						p.DFSIZE::varchar||' '||df_su.SHORTNAME
					) as Value
				) as Dose on true
				left join lateral (
					select
						replace(
						    replace(
						        (case when coalesce(p.DRUGDOSE, 0) > 0 then p.DRUGDOSE::float::varchar||' доз, ' else '' end)||
								coalesce(coalesce(n.PPACKMASS::float::varchar||' '||mu.SHORTNAME, n.PPACKVOLUME::float::varchar||' '||cu.SHORTNAME)||', ', '')||
								(
									case when coalesce(n.DRUGSINPPACK, 0) > 0
										then '№ '||(case when coalesce(n.PPACKINUPACK, 0) > 0 then (n.DRUGSINPPACK*n.PPACKINUPACK)::varchar else n.DRUGSINPPACK::varchar end)
										else case when coalesce(n.PPACKINUPACK, 0) > 0 then '№ '||n.PPACKINUPACK::varchar else '' end
									end
								)||',,', ', ,,', ''),
						    ',,', ''
						) as Value
				) as Fas on true
			where dn.DrugNomen_Code = :DrugNomen_Code
			  and (:WhsDocumentUc_pid is null or dcm.DrugComplexMnn_id in (select wdprc.DrugComplexMnn_id from WhsDocumentProcurementRequestSpec wdprc where wdprc.WhsDocumentProcurementRequest_id = :WhsDocumentUc_pid))
		";
		/**@var CI_DB_result $result */
		$queryParams = [
			"DrugNomen_Code" => $data["DrugNomen_Code"],
			"WhsDocumentUc_pid" => isset($data["WhsDocumentUc_pid"]) && $data["WhsDocumentUc_pid"] > 0 ? $data["WhsDocumentUc_pid"] : null
		];
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result_array();
		return (isset($result[0])) ? $result : false;
	}

	/**
	 * Получение списка медикаментов в ГК вместе со списком синонимов
	 * @param $data
	 * @return array|false
	 */
	function loadWhsDocumentSupplyStrCombo($data)
	{
		$this->load->model("DocumentUc_model", "DocumentUc_model");
		$params = [
			"WhsDocumentSupply_id" => $data["WhsDocumentSupply_id"],
			"DefaultGoodsUnit_id" => $this->DocumentUc_model->getDefaultGoodsUnitId()
		];
		$query = "
			select
				'S.'||WDSS.WhsDocumentSupplySpec_id::varchar as \"WhsDocumentSupplyStr_id\",
				'Spec' as \"WhsDocumentSupplyStrType\",
				WDSS.WhsDocumentSupplySpec_id as \"WhsDocumentSupplySpec_id\",
				null as \"WhsDocumentSupplySpecDrug_id\",
				WDSS.WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
				WDSS.WhsDocumentSupplySpec_KolvoUnit as \"WhsDocumentSupplyStr_KolvoUnit\",
				WDSS.WhsDocumentSupplySpec_NDS as \"WhsDocumentSupplyStr_NDS\",
				WDSS.WhsDocumentSupplySpec_PriceNDS as \"WhsDocumentSupplyStr_PriceNDS\",
				D.Drug_id as \"Drug_id\",
				D.Drug_Name as \"Drug_Name\",
				DCM.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				DCM.DrugComplexMnn_Name as \"DrugComplexMnn_Name\",
				GU.GoodsUnit_id as \"GoodsUnit_id\",
				GU.GoodsUnit_Name as \"GoodsUnit_Name\"
			from
				v_WhsDocumentSupplySpec WDSS
				left join rls.v_Drug D on D.Drug_id = WDSS.Drug_id
				left join rls.v_DrugComplexMnn DCM on DCM.DrugComplexMnn_id = D.DrugComplexMnn_id
				left join v_GoodsUnit GU on GU.GoodsUnit_id = coalesce(WDSS.GoodsUnit_id, :DefaultGoodsUnit_id)
			where WDSS.WhsDocumentSupply_id = :WhsDocumentSupply_id
			union all
			select
				'SD.'||WDSSD.WhsDocumentSupplySpecDrug_id::varchar as \"WhsDocumentSupplyStr_id\",
				'SpecDrug' as \"WhsDocumentSupplyStrType\",
				WDSS.WhsDocumentSupplySpec_id as \"WhsDocumentSupplySpec_id\",
				WDSSD.WhsDocumentSupplySpecDrug_id as \"WhsDocumentSupplySpecDrug_id\",
				WDSS.WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
				WDSS.WhsDocumentSupplySpec_KolvoUnit*WDSSD.WhsDocumentSupplySpecDrug_Coeff as \"WhsDocumentSupplyStr_KolvoUnit\",
				WDSS.WhsDocumentSupplySpec_NDS as \"WhsDocumentSupplyStr_NDS\",
				WDSSD.WhsDocumentSupplySpecDrug_PriceSyn as \"WhsDocumentSupplyStr_PriceNDS\",
				D.Drug_id as \"Drug_id\",
				D.Drug_Name as \"Drug_Name\",
				DCM.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				DCM.DrugComplexMnn_Name as \"DrugComplexMnn_Name\",
				GU.GoodsUnit_id as \"GoodsUnit_id\",
				GU.GoodsUnit_Name as \"GoodsUnit_Name\"
			from
				v_WhsDocumentSupplySpecDrug WDSSD
				left join v_WhsDocumentSupplySpec WDSS on WDSS.WhsDocumentSupplySpec_id = WDSSD.WhsDocumentSupplySpec_id
				left join rls.v_Drug D on D.Drug_id = WDSSD.Drug_sid
				left join rls.v_DrugComplexMnn DCM on DCM.DrugComplexMnn_id = D.DrugComplexMnn_id
				left join v_GoodsUnit GU on GU.GoodsUnit_id = coalesce(WDSS.GoodsUnit_id, :DefaultGoodsUnit_id)
			where WDSS.WhsDocumentSupply_id = :WhsDocumentSupply_id
		";
		return $this->queryResult($query, $params);
	}
}