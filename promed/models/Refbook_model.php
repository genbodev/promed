<?php defined('BASEPATH') or die ('No direct script access allowed');

class Refbook_model extends swModel {

	public $inputRules = array(
		'getLeaveTypeByLpuUnitType' => array(
			array(
				'field' => 'RISHLeaveType_id',
				'label' => 'Исход госпитализации РИШ',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LpuUnitType_id',
				'label' => 'Тип подразделения МО',
				'rules' => '',
				'type' => 'id'
			)
		),
		'getResultDeseaseByLpuUnitType' => array(
			array(
				'field' => 'RISHResultDesease_id',
				'label' => 'Исход заболевания РИШ',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LpuUnitType_id',
				'label' => 'Тип подразделения МО',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadRefbook' => array(
			array(
				'field' => 'Refbook_Code',
				'label' => 'Код справочника',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Refbook_TableName',
				'label' => 'Наименование таблицы справочника',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'id',
				'label' => 'ИД элемента справочника',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Code',
				'label' => 'Код элемента справочника',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Name',
				'label' => 'Наименование элемента справочника',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadRefbookUslugaComplex' => array(
			array('field' => 'UslugaComplex_id', 'label' => 'Идентификатор услуги', 'rules' => '', 'type' => 'string'),
			array('field' => 'UslugaComplex_pid', 'label' => 'Идентификатор услуги-родителя', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaComplexLevel_id', 'label' => 'Уровень услуги', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaComplex_Code', 'label' => 'Код услуги', 'rules' => '', 'type' => 'string'),
			array('field' => 'UslugaComplex_Name', 'label' => 'Наименование услуги', 'rules' => '', 'type' => 'string'),
			array('field' => 'UslugaCategory_id', 'label' => 'Категория услуги', 'rules' => '', 'type' => 'id')
		),
		'mLoadRefbookUslugaComplex' => array(
			array('field' => 'UslugaComplex_id', 'label' => 'Идентификатор услуги', 'rules' => '', 'type' => 'string'),
			array('field' => 'UslugaComplex_pid', 'label' => 'Идентификатор услуги-родителя', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaComplexLevel_id', 'label' => 'Уровень услуги', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaComplex_Code', 'label' => 'Код услуги', 'rules' => '', 'type' => 'string'),
			array('field' => 'UslugaComplex_Name', 'label' => 'Наименование услуги', 'rules' => '', 'type' => 'string'),
			array('field' => 'UslugaCategory_id', 'label' => 'Категория услуги', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => '', 'type' => 'id')
		),
		'loadRefbookMap' => array(
			array(
				'field' => 'Refbook_Code',
				'label' => 'Код справочника',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Refbook_MapName',
				'label' => 'Наименование таблицы стыковки',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Column_Name',
				'label' => 'Наименование поля таблицы',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Column_Value',
				'label' => 'Значение поля таблицы',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadRefbookbyColumn' => array(
			array(
				'field' => 'Refbook_Code',
				'label' => 'Код справочника',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Column_Name',
				'label' => 'Наименование поля таблицы',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Column_Value',
				'label' => 'Значение поля таблицы',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadRefbookbyColumnExt' => array(
			array(
				'field' => 'Refbook_Code',
				'label' => 'Код справочника',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Refbook_TableName',
				'label' => 'Наименование таблицы справочника',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Column_Callback',
				'label' => 'Наименование поля таблицы, значение которого нужно вернуть',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Column_Name',
				'label' => 'Наименование поля таблицы для фильтрации',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Column_Value',
				'label' => 'Значение поля таблицы для фильтрации',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadOrgSMOList' => array(
			array(
				'field' => 'OrgSMO_id',
				'label' => 'Идентификатор СМО в справочнике РИШ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OrgSmo_Name',
				'label' => 'Наименование СМО',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Orgsmo_f002smocod',
				'label' => 'Код СМО в федеральном справочнике',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'OrgSMO_Fedid',
				'label' => 'Идентификатор СМО в федеральном справочнике',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'KLRgn_id',
				'label' => 'Идентификатор региона',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadLpuList' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО в справочнике РИШ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_Name',
				'label' => 'Наименование МО',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Lpu_f003mcod',
				'label' => 'Код МО в федеральном справочнике',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'LPU_OID',
				'label' => 'OID МО в федеральном справочнике',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadKLAreaList' => array(
			array(
				'field' => 'KLAdr_Code',
				'label' => 'Код КЛАДР',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadOmsSprTerrList' => array(
			array(
				'field' => 'KLAdr_Code',
				'label' => 'Код КЛАДР',
				'rules' => '',
				'type' => 'id'
			),
            array(
                'field' => 'KLArea_id',
                'label' => 'Идентификатор территории',
                'rules' => '',
                'type' => 'id'
            )
		),
		'loadKLStreetList' => array(
			array(
				'field' => 'KLStreet_Name',
				'label' => 'Наименование улицы',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'KLArea_id',
				'label' => 'Идентификатор территории',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadKLSubRgnList' => array(
			array(
				'field' => 'KLAdr_Code',
				'label' => 'Код КЛАДР',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadRefbookGoodsUnit' => array(
			array(
				'field' => 'GoodsUnit_id',
				'label' => 'Идентификатор единицы измерения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'GoodsUnit_Name',
				'label' => 'Наименование единицы измерения',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'GoodsUnit_Nick',
				'label' => 'Краткое наименование единицы измерения',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadRefbookPrivilegeType' => array(
			array(
				'field' => 'PrivilegeType_id',
				'label' => 'Идентификатор типа льготы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrivilegeType_Code',
				'label' => 'Код типа льготы',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PrivilegeType_Name',
				'label' => 'Наименование типа льготы',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'PrivilegeType_Descr',
				'label' => 'Описание типа льготы',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'ReceptDiscount_id',
				'label' => 'Идентификатор скидки',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ReceptFinance_id',
				'label' => 'Идентификатор типа финансирования',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadRefbookDrugComplexMnn' => array(
			array(
				'field' => 'CLSDRUGFORMS_ID',
				'label' => 'Идентификатор лекарственной формы внутреннего справочника РИШ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'FEDDRUGFORMS_ID',
				'label' => 'Идентификатор лекарственной формы cправочника Реестра НСИ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CLSATC_Code',
				'label' => 'Код действующего вещества АТХ',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Drug_Dose',
				'label' => 'Доза медикамента',
				'rules' => '',
				'type' => 'string'
			),
		),
		'mLoadRefbookData' => array(
			array(
				'field' => 'load_list',
				'label' => 'Массив идентификаторов справочников РИШ',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'start',
				'label' => 'Стартовая позиция справочника',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'limit',
				'label' => 'Конечная позиция справочника',
				'rules' => '',
				'type' => 'int'
			),
		),
		'mRefbookList' => array(
			array(
				'field' => 'recache',
				'label' => 'признак принудительного перекеширования результатов',
				'rules' => '',
				'type' => 'boolean'
			)
		),
		'mGetRefbookDiag' => array()
	);
	
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

    /**
	 * Получение списка справочников
	 */
	function loadRefbookList() {
		$resp = $this->queryResult("
			select
				RISHRefbook_Code as Refbook_Code,
				RISHRefbook_Name as Refbook_Name,
				RISHRefbookType_id as RefbookType_id,
				Refbook_TableName
			from
				v_RISHRefbook (nolock)
		");

		return $resp;
	}

    /**
	 * Получение исхода госпитализации по типу подразделения МО
	 */
	function getLeaveTypeByLpuUnitType($data) {
		$filter = "";
		$queryParams = array(
			'RISHLeaveType_id' => $data['RISHLeaveType_id']
		);

		if (!empty($data['LpuUnitType_id'])) {
			$filter .= " and rlt.LpuUnitType_id = :LpuUnitType_id";
			$queryParams['LpuUnitType_id'] = $data['LpuUnitType_id'];
		}

		$resp = $this->queryResult("
			select
				rlt.RISHLeaveType_id,
				rlt.LpuUnitType_id,
				rlt.LeaveType_id,
				lt.LeaveType_fedid
			from
				v_RISHLeaveTypeLink rlt (nolock)
				left join LeaveType lt (nolock) on lt.LeaveType_id = rlt.LeaveType_id
			where
				rlt.RISHLeaveType_id = :RISHLeaveType_id
				{$filter}
		", $queryParams);

		return $resp;
	}

    /**
	 * Получение исхода заболевания по типу подразделения МО
	 */
	function getResultDeseaseByLpuUnitType($data) {
		$filter = "";
		$queryParams = array(
			'RISHResultDesease_id' => $data['RISHResultDesease_id']
		);

		if (!empty($data['LpuUnitType_id'])) {
			$filter .= " and LpuUnitType_id = :LpuUnitType_id";
			$queryParams['LpuUnitType_id'] = $data['LpuUnitType_id'];
		}

		$resp = $this->queryResult("
			select
				RISHResultDesease_id,
				LpuUnitType_id,
				ResultDesease_id
			from
				v_RISHResultDeseaseLink (nolock)
			where
				RISHResultDesease_id = :RISHResultDesease_id
				{$filter}
		", $queryParams);

		return $resp;
	}

	/**
	 *  Получение списка СМО
	 */
	function loadOrgSMOList($data) {
		$filter = "";
		$queryParams = array();

		if (!empty($data['OrgSMO_id'])) {
			$filter .= " and OrgSMO_id = :OrgSMO_id";
			$queryParams['OrgSMO_id'] = $data['OrgSMO_id'];
		}

		if (!empty($data['OrgSmo_Name'])) {
			$filter .= " and OrgSmo_Name = :OrgSmo_Name";
			$queryParams['OrgSmo_Name'] = $data['OrgSmo_Name'];
		}

		if (!empty($data['Orgsmo_f002smocod'])) {
			$filter .= " and Orgsmo_f002smocod = :Orgsmo_f002smocod";
			$queryParams['Orgsmo_f002smocod'] = $data['Orgsmo_f002smocod'];
		}

		if (!empty($data['OrgSMO_Fedid'])) {
			$filter .= " and OrgSMO_Fedid = :OrgSMO_Fedid";
			$queryParams['OrgSMO_Fedid'] = $data['OrgSMO_Fedid'];
		}

		if (!empty($data['KLRgn_id'])) {
			$filter .= " and KLRgn_id = :KLRgn_id";
			$queryParams['KLRgn_id'] = $data['KLRgn_id'];
		}

		$resp = $this->queryResult("
			select
				OrgSMO_id,
				OrgSmo_Name,
				Orgsmo_f002smocod,
				OrgSMO_Fedid,
				KLRgn_id
			from
				v_OrgSMO (nolock)
			where
				(1=1)
				{$filter}
		", $queryParams);

		return $resp;
	}

	/**
	 *  Получение списка СМО для мобильных устройств
	 */
	function loadOrgSMOListForAPI($data) {

		$filter = "";
		$queryParams = array();

		if (!empty($data['OrgSMO_id'])) {
			$filter .= " and OrgSMO_id = :OrgSMO_id";
			$queryParams['OrgSMO_id'] = $data['OrgSMO_id'];
		}

		if (!empty($data['OrgSmo_Name'])) {
			$filter .= " and OrgSmo_Name = :OrgSmo_Name";
			$queryParams['OrgSmo_Name'] = $data['OrgSmo_Name'];
		}

		if (!empty($data['Orgsmo_f002smocod'])) {
			$filter .= " and Orgsmo_f002smocod = :Orgsmo_f002smocod";
			$queryParams['Orgsmo_f002smocod'] = $data['Orgsmo_f002smocod'];
		}

		if (!empty($data['OrgSMO_Fedid'])) {
			$filter .= " and OrgSMO_Fedid = :OrgSMO_Fedid";
			$queryParams['OrgSMO_Fedid'] = $data['OrgSMO_Fedid'];
		}

		if (!empty($data['KLRgn_id'])) {
			$filter .= " and KLRgn_id = :KLRgn_id";
			$queryParams['KLRgn_id'] = $data['KLRgn_id'];
		}

		$resp = $this->queryResult("
		select
			OrgSMO_id,
			Org_id,
			OrgSMO_RegNomC,
			OrgSMO_RegNomN,
			OrgSmo_Name,
			OrgSMO_Nick,
			OrgSMO_isDMS,
			KLRgn_id,
			convert(varchar(10), OrgSMO_endDate, 104) as OrgSMO_endDate,
			ISNULL(OrgSMO_IsTFOMS, 1) as OrgSMO_IsTFOMS,
			Orgsmo_f002smocod
			from
				v_OrgSMO (nolock)
			where
				(1=1)
				{$filter}
		", $queryParams);

		return $resp;
	}

	/**
	 *  Получение списка МО
	 */
	function loadLpuList($data) {
		$filter = "";
		$queryParams = array();

		if (!empty($data['Lpu_id'])) {
			$filter .= " and l.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		if (!empty($data['Lpu_Name'])) {
			$filter .= " and l.Lpu_Name = :Lpu_Name";
			$queryParams['Lpu_Name'] = $data['Lpu_Name'];
		}

		if (!empty($data['Lpu_f003mcod'])) {
			$filter .= " and l.Lpu_f003mcod = :Lpu_f003mcod";
			$queryParams['Lpu_f003mcod'] = $data['Lpu_f003mcod'];
		}

		if (!empty($data['LPU_OID'])) {
			$filter .= " and pt.PassportToken_tid = :LPU_OID";
			$queryParams['LPU_OID'] = $data['LPU_OID'];
		}

		$resp = $this->queryResult("
			select
				l.Lpu_id,
				l.Lpu_Name,
				l.Lpu_f003mcod,
				pt.PassportToken_tid as LPU_OID
			from
				v_Lpu l (nolock)
				left join fed.v_PassportToken pt (nolock) on pt.Lpu_id = l.Lpu_id
			where
				(1=1)
				{$filter}
		", $queryParams);

		return $resp;
	}

	/**
	 *  Получение элементов справочника КЛАДР
	 */
	function loadKLAreaList($data) {
		$filter = "";
		$queryParams = array();

		$filter .= " and kla.KLAdr_Code = :KLAdr_Code";
		$queryParams['KLAdr_Code'] = $data['KLAdr_Code'];

		$resp = $this->queryResult("
			select
				kla.KLCountry_id,
				kla.KLArea_id, 
				kla_rgn.KLArea_id as KLRgn_id, 
				kla_subrgn.KLArea_id as KLSubRgn_id,
				kla_city.KLArea_id as KLCity_id,
				kla_town.KLArea_id as KLTown_id
			from
				v_KLArea kla (nolock)
				left join v_KLArea kla_town (nolock) on case when kla.KLAreaLevel_id = 4 then kla.KLArea_id else kla.KLArea_pid end = kla_town.KLArea_id and kla_town.KLAreaLevel_id = 4
				left join v_KLArea kla_city (nolock) on case when kla.KLAreaLevel_id = 3 then kla.KLArea_id else coalesce(kla_town.KLArea_pid, kla.KLArea_pid) end = kla_city.KLArea_id and kla_city.KLAreaLevel_id = 3
				left join v_KLArea kla_subrgn (nolock) on case when kla.KLAreaLevel_id = 2 then kla.KLArea_id else coalesce(kla_city.KLArea_pid, kla_town.KLArea_pid, kla.KLArea_pid) end = kla_subrgn.KLArea_id and kla_subrgn.KLAreaLevel_id = 2
				left join v_KLArea kla_rgn (nolock) on case when kla.KLAreaLevel_id = 1 then kla.KLArea_id else coalesce(kla_subrgn.KLArea_pid, kla_city.KLArea_pid, kla_town.KLArea_pid, kla.KLArea_pid) end = kla_rgn.KLArea_id and kla_rgn.KLAreaLevel_id = 1
			where
				(1=1)
				{$filter}
		", $queryParams);

		return $resp;
	}

	/**
	 *  Получение элементов справочника
	 */
	function loadOmsSprTerrList($data) {

		$select = ""; $filter = ""; $type = ""; $queryParams = array();

        if(isset($data['KLAdr_Code'])) {
            $filter .= " and kla.KLAdr_Code = :KLAdr_Code";
            $queryParams['KLAdr_Code'] = $data['KLAdr_Code'];
        }

		if(isset($data['KLArea_id'])){
            $filter .= " and kla.KLArea_id = :KLArea_id";
            $queryParams['KLArea_id'] = $data['KLArea_id'];
        }

		if(!empty($data['fromMobile'])){

			$type = " distinct ";
			$select = ",
				ost.KLRgn_id,
				ost.OmsSprTerr_Code
			";

			$filter = "
				order by ost.OmsSprTerr_Code
			";
		}

		$resp = $this->queryResult("
			select {$type}
				ost.OmsSprTerr_id,
				ost.OmsSprTerr_Name
				{$select}
			from
				v_KLArea kla (nolock)
				inner join v_OmsSprTerr ost (nolock) on kla.KLArea_id = ( case
					when kla.KLAreaLevel_id = 1 then ost.KLRgn_id
					when kla.KLAreaLevel_id = 2 then ost.KLSubRgn_id
					when kla.KLAreaLevel_id = 3 then ost.KLCity_id
					when kla.KLAreaLevel_id = 4 then ost.KLTown_id
				end )
			where
				(1=1)
				{$filter}
		", $queryParams);

		return $resp;
	}

	/**
	*  Получение справочника услуг
	*/
	function loadRefbookUslugaComplex($data) {
		$filter = "";
		$queryParams = array();

		if (!empty($data['UslugaComplex_id'])) {
			$filter .= " and uc.UslugaComplex_id = :UslugaComplex_id";
			$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
		}
		if (!empty($data['UslugaComplex_pid'])) {
			$filter .= " and uc.UslugaComplex_pid = :UslugaComplex_pid";
			$queryParams['UslugaComplex_pid'] = $data['UslugaComplex_pid'];
		}
		if (!empty($data['UslugaComplexLevel_id'])) {
			$filter .= " and uc.UslugaComplexLevel_id = :UslugaComplexLevel_id";
			$queryParams['UslugaComplexLevel_id'] = $data['UslugaComplexLevel_id'];
		}
		if (!empty($data['Lpu_id'])) {
			$filter .= " and uc.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		} else {
			$filter .= " and ISNULL(uc.Lpu_id, :Lpu_id) = :Lpu_id";
			$queryParams['Lpu_id'] = $data['session']['lpu_id'];
		}
		if (!empty($data['UslugaComplex_Code'])) {
			$filter .= " and uc.UslugaComplex_Code = :UslugaComplex_Code";
			$queryParams['UslugaComplex_Code'] = $data['UslugaComplex_Code'];
		}
		if (!empty($data['UslugaComplex_Name'])) {
			$filter .= " and uc.UslugaComplex_Name = :UslugaComplex_Name";
			$queryParams['UslugaComplex_Name'] = $data['UslugaComplex_Name'];
		}
		if (!empty($data['UslugaCategory_id'])) {
			$filter .= " and uc.UslugaCategory_id = :UslugaCategory_id";
			$queryParams['UslugaCategory_id'] = $data['UslugaCategory_id'];
		}

		$resp = $this->queryResult("
			select
				uc.UslugaComplex_id,
				uc.UslugaComplex_pid,
				uc.UslugaComplexLevel_id,
				uc.Lpu_id,
				uc.LpuSection_id,
				uc.UslugaComplex_Code,
				uc.UslugaComplex_Name,
				uc.UslugaCategory_id
			from
				v_UslugaComplex UC (nolock)
			where
				(1=1)
				{$filter}
		", $queryParams);

		return $resp;
	}

	/**
	 *  Получение справочника услуг
	 */
	function mLoadRefbookUslugaComplex($data) {

		$filter = ""; $join = "";
		$queryParams = array();

		if (!empty($data['UslugaComplex_id'])) {
			$filter .= " and uc.UslugaComplex_id = :UslugaComplex_id";
			$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
		}

		if (!empty($data['UslugaComplex_pid'])) {
			$filter .= " and uc.UslugaComplex_pid = :UslugaComplex_pid";
			$queryParams['UslugaComplex_pid'] = $data['UslugaComplex_pid'];
		}

		if (!empty($data['UslugaComplexLevel_id'])) {
			$filter .= " and uc.UslugaComplexLevel_id = :UslugaComplexLevel_id";
			$queryParams['UslugaComplexLevel_id'] = $data['UslugaComplexLevel_id'];
		}

		if (!empty($data['Lpu_id'])) {
			$filter .= " and uc.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		if (!empty($data['UslugaComplex_Code'])) {
			$filter .= " and uc.UslugaComplex_Code = :UslugaComplex_Code";
			$queryParams['UslugaComplex_Code'] = $data['UslugaComplex_Code'];
		}

		if (!empty($data['UslugaComplex_Name'])) {
			$filter .= " and uc.UslugaComplex_Name = :UslugaComplex_Name";
			$queryParams['UslugaComplex_Name'] = $data['UslugaComplex_Name'];
		}

		if (!empty($data['UslugaCategory_id'])) {
			$filter .= " and uc.UslugaCategory_id = :UslugaCategory_id";
			$queryParams['UslugaCategory_id'] = $data['UslugaCategory_id'];
		}

		$select = "
			uc.Lpu_id,
			uc.LpuSection_id,
		";

		if (!empty($data['MedService_id'])) {

			$join .= "
				left join v_UslugaComplexMedService ucms (nolock) on ucms.UslugaComplex_id = uc.UslugaComplex_id
				left join v_MedService ms (nolock) on ms.MedService_id = ucms.MedService_id
			";

			$select = "
				ms.Lpu_id,
				ms.LpuSection_id,
			";

			$filter .= " 
				and ucms.MedService_id = :MedService_id
				and ucms.UslugaComplexMedService_pid is null
			";

			$queryParams['MedService_id'] = $data['MedService_id'];
		}

		$resp = $this->queryResult("
			select
				uc.UslugaComplex_id,
				uc.UslugaComplex_pid,
				uc.UslugaComplexLevel_id,
				{$select}
				uc.UslugaComplex_Code,
				uc.UslugaComplex_Name,
				uc.UslugaCategory_id
			from v_UslugaComplex UC (nolock)
			{$join}
			where
				(1=1)
				and uc.UslugaComplex_endDT is null
				{$filter}
		", $queryParams);

		return $resp;
	}

	/**
	 *  Получение справочника диагнозов
	 */
	function loadRefbookDiag($data) {

		$resp = $this->queryResult("
			select
				d.Diag_id,
				d.Diag_pid,
				d.DiagLevel_id,
				d.Diag_Code,
				d.Diag_Name,
				d.Diag_begDate
			from v_Diag d (nolock)
			where d.Diag_endDate is null
		");

		return $resp;
	}

	/**
	 *  Получение улиц справочника КЛАДР
	 */
	function loadKLStreetList($data) {
		$filter = "";
		$queryParams = array();

		$namefilter = " and kst.KLStreet_Name = :KLStreet_Name";
		$queryParams['KLStreet_Name'] = $data['KLStreet_Name'];

		if (!empty($data['KLArea_id'])) {
			$filter .= " and kst.KLArea_id = :KLArea_id";
			$queryParams['KLArea_id'] = $data['KLArea_id'];
		}

		$resp = $this->queryResult("
			select
				kst.KLStreet_id,
				kst.KLStreet_Name
			from
				v_KLStreet kst (nolock)
			where
				(1=1)
				{$namefilter}
				{$filter}
		", $queryParams);

		if (empty($resp)) {
			$namefilter = " and kst.KLStreet_Name + isnull(' ' + ksc.KLSocr_Nick,'') = :KLStreet_Name";
			$resp = $this->queryResult("
				select
					kst.KLStreet_id,
					kst.KLStreet_Name
				from
					v_KLStreet kst (nolock)
					left join v_KLSocr ksc (nolock) on kst.KLSocr_id = ksc.KLSocr_id
				where
					(1=1)
					{$namefilter}
					{$filter}
			", $queryParams);
		}

		if (empty($resp)) {
			$namefilter = " and kst.KLStreet_Name + isnull(' ' + ksc.KLSocr_Nick + '.','') = :KLStreet_Name";
			$resp = $this->queryResult("
				select
					kst.KLStreet_id,
					kst.KLStreet_Name
				from
					v_KLStreet kst (nolock)
					left join v_KLSocr ksc (nolock) on kst.KLSocr_id = ksc.KLSocr_id
				where
					(1=1)
					{$namefilter}
					{$filter}
			", $queryParams);
		}

		return $resp;
	}

	/**
	 *  Получение списка районов
	 */
	function loadKLSubRgnList($data) {
		$queryParams = array(
			'KLAdr_Code' => $data['KLAdr_Code']
		);

		$resp = $this->queryResult("
			declare @KLRgn_id bigint = (select top 1 KLArea_id from v_KLArea with (nolock) where KLAdr_Code = :KLAdr_Code and KLAreaLevel_id = 1);

			select
				ka.KLArea_id,
				ka.KLArea_Name,
				ka.KLSocr_id,
				ks.KLSocr_Name,
				ks.KLSocr_Nick
			from
				v_KLArea ka with (nolock)
				inner join v_KLSocr ks on ks.KLSocr_id = ka.KLSocr_id
			where
				ka.KLArea_pid = @KLRgn_id
				and ka.KLAreaLevel_id = 2
		", $queryParams);

		return $resp;
	}

	/**
	 * Получение элементов справочника
	 */
	function loadRefbook($data) {
		$filter = "";
		$queryParams = array();
		if(empty($data['Refbook_TableName']) && empty($data['Refbook_Code'])){
			return array();
		}
		if (!empty($data['Refbook_Code'])) {
			$filter .= " and RISHRefbook_Code = :RISHRefbook_Code";
			$queryParams['RISHRefbook_Code'] = $data['Refbook_Code'];
		} 
		if (!empty($data['Refbook_TableName'])) {
			$filter .= " and Refbook_TableName = :Refbook_TableName";
			$queryParams['Refbook_TableName'] = $data['Refbook_TableName'];
		} 

		$resp = $this->queryResult("
			select
				RISHRefbook_id,
				Refbook_TableName
			from
				v_RISHRefbook (nolock)
			where
				Refbook_TableName IS NOT NULL
				{$filter}
		", $queryParams);

		if(empty($resp[0]['RISHRefbook_id'])){
			return array(array('Error_Msg'=>'Данного справочника нет в списке Внутренних справочников РИШ'));
		}
		
		if(empty($data['Refbook_TableName']) && !empty($data['Refbook_Code']) && count($resp)>1){
			return array(array('Error_Msg'=>'По указанному коду '.$data['Refbook_Code'].' найдено несколько справочников. Уточните запрос данными о наименовании таблицы (Refbook_TableName)'));
		}
		$table = $resp[0]['Refbook_TableName'];
		$subject = explode('.', $table);
		$schema = $subject[0];
		$subject = trim($subject[1]);
		$view = 'v_';
		$fields = "
			{$subject}_id as id,
			{$subject}_Name as Name,
			{$subject}_Code as Code
		";

		$queryParams = array();
		$filter = "";
		if (isset($data['id'])) {
			$filter .= " and {$subject}_id = :id";
			$queryParams['id'] = $data['id'];
		}
		if (isset($data['Code'])) {
			$filter .= " and {$subject}_Code = :Code";
			$queryParams['Code'] = $data['Code'];
		}
		if (isset($data['Name'])) {
			$filter .= " and {$subject}_Name = :Name";
			$queryParams['Name'] = $data['Name'];
		}

		if(in_array($subject, array('RecType'))){
			$view = '';
		}

		// определяем наличие столбцов (код есть не во всех таблицах)
		$resp = $this->queryResult("select * from dbo.v_columns where schema_name = :schema_name and table_type = '".(!empty($view)?"V":"U")."' and table_name = :table_name", array(
			'schema_name' => $schema,
			'table_name' => $view.$subject
		));

		if (!empty($resp)) {
			$columns = array();
			foreach($resp as $respone) {
				$columns[] = mb_strtolower($respone['column_name']);
			}

			$queryParams = array();
			$filter = "";
			$fields = "";

			if (in_array(mb_strtolower($subject."_id"), $columns)) {
				if (isset($data['id'])) {
					$filter .= " and {$subject}_id = :id";
					$queryParams['id'] = $data['id'];
				}
				$fields .= "{$subject}_id as id,";
				$isIdBySubject = true;
			} else {
				$fields .= "null as id,";
			}

			if (in_array(mb_strtolower($subject."_Name"), $columns)) {
				if (isset($data['Name'])) {
					$filter .= " and {$subject}_Name = :Name";
					$queryParams['Name'] = $data['Name'];
				}
				$fields .= "{$subject}_Name as Name,";
			} else {
				$fields .= "null as Name,";
			}

			if (in_array(mb_strtolower($subject."_Code"), $columns)) {
				if (isset($data['Code'])) {
					$filter .= " and {$subject}_Code = :Code";
					$queryParams['Code'] = $data['Code'];
				}
				$fields .= "{$subject}_Code as Code,";
			} else {
				$fields .= "null as Code,";
			}

			if (in_array(mb_strtolower($subject."_begDT"), $columns)) {
				$fields .= "convert(varchar(10), {$subject}_begDT, 120) as begDate,";
			} elseif (in_array(mb_strtolower($subject."_begDate"), $columns)) {
				$fields .= "convert(varchar(10), {$subject}_begDate, 120) as begDate,";
			} else {
				$fields .= "null as begDate,";
			}

			if (in_array(mb_strtolower($subject."_endDT"), $columns)) {
				$fields .= "convert(varchar(10), {$subject}_endDT, 120) as endDate";
			} elseif (in_array(mb_strtolower($subject."_endDate"), $columns)) {
				$fields .= "convert(varchar(10), {$subject}_endDate, 120) as endDate";
			} else {
				$fields .= "null as endDate";
			}

			if (in_array(mb_strtolower($subject."_SysNick"), $columns) && !empty($data['fromMobile'])) {
				$fields .= ", {$subject}_SysNick as SysNick";
			}
		}

		if($schema == 'persis'){
			//уточним наличие столбцов в таблице
			if(!in_array($subject, array('postkind','Category'))){
				$view = '';
			}
			$columns_in_the_table = "
				select Column_name 
				from Information_schema.columns 
				where Table_name like '{$view}{$subject}' and TABLE_SCHEMA = '{$schema}'";
			$resp_columns = $this->queryResult($columns_in_the_table);
			$arrColumns = array();
			foreach ($resp_columns as $arr_value) {
				if(!empty($arr_value['Column_name'])) $arrColumns[] = mb_strtolower($arr_value['Column_name']);
			}
			$fields = '';
			$fields .= (in_array('id', $arrColumns)) ? ' id,' : ' null as id,';
			$fields .= (in_array('name', $arrColumns)) ? ' name as Name,' : ' null as Name,';
			$fields .= (in_array('code', $arrColumns)) ? ' code as Code,' : ' null as Code,';
			$fields .= " 
				null as begDate,
				null as endDate
			";
			/*$fields = "
				id,
				name as Name,
				code as Code,
				null as begDate,
				null as endDate
			";*/

			$queryParams = array();
			$filter = "";
			if (isset($data['id']) && in_array('id', $arrColumns)) {
				$filter .= " and id = :id";
				$queryParams['id'] = $data['id'];
			}
			if (isset($data['Code']) && in_array('code', $arrColumns)) {
				$filter .= " and code = :Code";
				$queryParams['Code'] = $data['Code'];
			}
			if (isset($data['Name']) && in_array('name', $arrColumns)) {
				$filter .= " and name = :Name";
				$queryParams['Name'] = $data['Name'];
			}

			if(!in_array($subject, array('postkind','Category'))){
				$view = '';
			}

			$isIdBySubject = false;
		}

		$query_body = "
			select
				".$fields."
			from {$schema}.{$view}{$subject} with (nolock)
			where (1=1)
			{$filter}
		";

		// если у нас указаны параметры выгрузки по частям и признак идентификатора существует
		if (isset($data['start']) && (!empty($data['limit'])) && isset($isIdBySubject)) {

			$id_field = ($isIdBySubject) ? $subject."_id" : 'id';
			$queryParams['start'] = $data['start'];
			$queryParams['limit'] = $data['limit'];

			$query_body = "
				with rb AS
					(
						select
							{$fields}
							,ROW_NUMBER() OVER (ORDER BY {$id_field}) AS 'RowNumber'
						from {$schema}.{$view}{$subject} with (nolock)
					)
				select id, Name, Code, begDate, endDate from rb where RowNumber between :start and :limit;
			";
		}

		$query = "
			declare
				@Error_Code int = null,
				@Error_Message varchar(4000) = null;

			set nocount on;

			begin try
				{$query_body}
			end try

			begin catch
				set @Error_Code = error_number();
				set @Error_Message = error_message();
				select @Error_Code as Error_Code, @Error_Message as Error_Msg;
			end catch

			set nocount off;
		";
		//echo getDebugSQL($query, $data);exit;
		//echo '<pre>',print_r(getDebugSQL($query, $data)),'</pre>'; die();
		$resp = $this->queryResult($query, $queryParams);

		if (!empty($data['Column_Name']) && !empty($data['Column_Value'])) {

			foreach($resp as $one => $key) {

				if (!empty($key[$data['Column_Name']]) && $key[$data['Column_Name']] == $data['Column_Value']) {
					// удовлетворяет фильтру
				} else {
					// не удовлетворяет фильтру
					unset($resp[$one]);
				}
			}
			$resp = array_values($resp);
		}

		return $resp;
	}

	/**
	 * Получение элементов справочника (расширенный)
	 */
	function loadRefbookbyColumnExt($data) {
		$filter = "";
		$queryParams = array();
		if (!empty($data['Refbook_Code'])) {
			$filter .= " and RISHRefbook_Code = :RISHRefbook_Code";
			$queryParams['RISHRefbook_Code'] = $data['Refbook_Code'];
		} else if (!empty($data['Refbook_TableName'])) {
			$filter .= " and Refbook_TableName = :Refbook_TableName";
			$queryParams['Refbook_TableName'] = $data['Refbook_TableName'];
		} else {
			return array();
		}

		$resp = $this->queryResult("
			select
				RISHRefbook_id,
				Refbook_TableName
			from
				v_RISHRefbook (nolock)
			where
				Refbook_TableName IS NOT NULL
				{$filter}
		", $queryParams);

		if(empty($resp[0]['RISHRefbook_id'])){
			return array(array('Error_Msg'=>'Данного справочника нет в списке Внутренних справочников РИШ'));
		}
		$table = $resp[0]['Refbook_TableName'];
		$subject = explode('.', $table);
		$schema = $subject[0];
		$subject = trim($subject[1]);
		$view = "";

		// определяем наличие столбцов
		$resp = $this->queryResult("select * from dbo.v_columns where schema_name = :schema_name and table_type = '".(!empty($view)?"V":"U")."' and table_name = :table_name", array(
			'schema_name' => $schema,
			'table_name' => $view.$subject
		));
		if (!empty($resp)) {
			$columns = array();
			foreach($resp as $respone) {
				$columns[] = mb_strtolower($respone['column_name']);
			}

			$filter2 = "";
			if (!empty($data['Column_Name']) && in_array(mb_strtolower($data['Column_Name']), $columns) && isset($data['Column_Value'])) {
				$filter2 .= " and {$data['Column_Name']} = :Column_Value";
				$queryParams['Column_Value'] = $data['Column_Value'];
			}

			if (in_array(mb_strtolower($data['Column_Callback']), $columns)) {
				$sql = "
					select
						{$data['Column_Callback']}
					from
						{$schema}.{$view}{$subject}
					where
						1=1
						{$filter2}
				";
			} else {
				return array(array('Error_Msg' => 'Поле не найдено в справочнике'));
			}

			$query = "
				declare
					@Error_Code int = null,
					@Error_Message varchar(4000) = null;
	
				set nocount on;
	
				begin try
					{$sql}
				end try
	
				begin catch
					set @Error_Code = error_number();
					set @Error_Message = error_message();
					select @Error_Code as Error_Code, @Error_Message as Error_Msg;
				end catch
	
				set nocount off;
			";
			//echo getDebugSQL($query, $data);exit;
			return $this->queryResult($query, $queryParams);
		}

		return array();
	}

	/**
	 * Получение элементов справочника
	 */
	function loadRefbookMap($data) {
		$filter = "";
		$queryParams = array(
			'RISHRefbook_Code' => $data['Refbook_Code']
		);
		if (!empty($data['Refbook_MapName'])) {
			$filter .= " and RISHRefbook_MapName = :Refbook_MapName";
			$queryParams['Refbook_MapName'] = $data['Refbook_MapName'];
		}
		$resp = $this->queryResult("
			select
				RISHRefbook_id,
				RISHRefbook_Code,
				RISHRefbook_Name,
				RISHRefbookType_id,
				RISHRefbook_MapName
			from
				v_RISHRefbook (nolock)
			where
				RISHRefbook_Code = :RISHRefbook_Code
				and RISHRefbook_MapName is not null
				{$filter}
		", $queryParams);

		if(empty($resp[0]['RISHRefbook_id'])){
			return null;
		}
		$table = $resp[0]['RISHRefbook_MapName'];
		$subject = explode('.', $table);
		$schema = $subject[0];
		$subject = trim($subject[1]);
		$subject2 = str_replace('RISH', '', $subject);
		$view = 'v_';
		$fields = "
			t2.{$subject2}_id as id,
			t2.{$subject2}_Code as Code,
			t3.{$subject}_id as Fed_id,
			t3.{$subject}_Code as Fed_Code
		";

		if(in_array($subject2, array('DrugPrep','OrgHeadPost','OrgDep','RecordQueue','RecType','OrgMilitary','LpuBuildingType'))){
			$fields = "
				t2.{$subject2}_id as id,
				null as Code,
				t3.{$subject}_id as Fed_id,
				t3.{$subject}_Code as Fed_Code
			";
		}

		$queryParams = array();

		$query = "
			declare
				@Error_Code int = null,
				@Error_Message varchar(4000) = null;

			set nocount on;

			begin try
				select
				".$fields."	
				from
					{$schema}.{$view}{$subject}Link t1 with (nolock)
					inner join {$schema}.{$view}{$subject2} t2 with (nolock) on t2.{$subject2}_id = t1.{$subject2}_id
					inner join {$schema}.{$view}{$subject} t3 with (nolock) on t3.{$subject}_id = t1.{$subject}_id
			end try

			begin catch
				set @Error_Code = error_number();
				set @Error_Message = error_message();
				select @Error_Code as Error_Code, @Error_Message as Error_Msg;
			end catch

			set nocount off;
		";
		//echo getDebugSQL($query, $data);exit;
		$resp = $this->queryResult($query, $queryParams);

		if (!empty($data['Column_Name']) && !empty($data['Column_Value'])) {
			foreach($resp as $one => $key) {
				if (!empty($key[$data['Column_Name']]) && $key[$data['Column_Name']] == $data['Column_Value']) {
					// удовлетворяет фильтру
				} else {
					// не удовлетворяет фильтру
					unset($resp[$one]);
				}
			}
			$resp = array_values($resp);
		}

		return $resp;
	}

	/**
	 * Получение справочника единиц измерения
	 */
	function loadRefbookGoodsUnit($data) {
		$params = array();
		$filters = array();

		if (!empty($data['GoodsUnit_id'])) {
			$filters[] = "GU.GoodsUnit_id = :GoodsUnit_id";
			$params['GoodsUnit_id'] = $data['GoodsUnit_id'];
		}
		if (!empty($data['GoodsUnit_Name'])) {
			$filters[] = "GU.GoodsUnit_Name like :GoodsUnit_Name";
			$params['GoodsUnit_Name'] = $data['GoodsUnit_Name'];
		}
		if (!empty($data['GoodsUnit_Nick'])) {
			$filters[] = "GU.GoodsUnit_Nick like :GoodsUnit_Nick";
			$params['GoodsUnit_Nick'] = $data['GoodsUnit_Nick'];
		}

		if (count($filters) == 0) {
			return $this->createError('','Не передан ни один параметр');
		}
		$filters_str = implode(" and ", $filters);

		$query = "
			select
				GU.GoodsUnit_id,
				GU.GoodsUnit_Name,
				GU.GoodsUnit_Nick
			from
				v_GoodsUnit GU with(nolock)
			where
				{$filters_str}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение справочника типов льгот
	 */
	function loadRefbookPrivilegeType($data) {
		$params = array();
		$filters = array();

		if (!empty($data['PrivilegeType_id'])) {
			$filters[] = "PT.PrivilegeType_id = :PrivilegeType_id";
			$params['PrivilegeType_id'] = $data['PrivilegeType_id'];
		}
		if (!empty($data['PrivilegeType_Code'])) {
			$filters[] = "PT.PrivilegeType_Code = :PrivilegeType_Code";
			$params['PrivilegeType_Code'] = $data['PrivilegeType_Code'];
		}
		if (!empty($data['PrivilegeType_Name'])) {
			$filters[] = "PT.PrivilegeType_Name like :PrivilegeType_Name";
			$params['PrivilegeType_Name'] = $data['PrivilegeType_Name'];
		}
		if (!empty($data['PrivilegeType_Descr'])) {
			$filters[] = "PT.PrivilegeType_Descr like :PrivilegeType_Descr";
			$params['PrivilegeType_Descr'] = $data['PrivilegeType_Descr'];
		}
		if (!empty($data['ReceptDiscount_id'])) {
			$filters[] = "PT.ReceptDiscount_id = :ReceptDiscount_id";
			$params['ReceptDiscount_id'] = $data['ReceptDiscount_id'];
		}
		if (!empty($data['ReceptFinance_id'])) {
			$filters[] = "PT.ReceptFinance_id = :ReceptFinance_id";
			$params['ReceptFinance_id'] = $data['ReceptFinance_id'];
		}

		if (count($filters) == 0) {
			return $this->createError('','Не передан ни один параметр');
		}
		$filters_str = implode(" and ", $filters);

		$query = "
			select
				PT.PrivilegeType_id,
				PT.PrivilegeType_Code,
				PT.PrivilegeType_Name,
				PT.PrivilegeType_Descr,
				PT.ReceptDiscount_id,
				PT.ReceptFinance_id
			from
				v_PrivilegeType PT with(nolock)
			where
				{$filters_str}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение справочника типов льгот
	 */
	function loadRefbookDrugComplexMnn($data) {
		$params = array();
		$filters = array();

		if (!empty($data['CLSDRUGFORMS_ID'])) {
			$filters[] = "DCM.CLSDRUGFORMS_ID = :CLSDRUGFORMS_ID";
			$params['CLSDRUGFORMS_ID'] = $data['CLSDRUGFORMS_ID'];
		} else if (!empty($data['FEDDRUGFORMS_ID'])) {
			$filters[] = "DCM.CLSDRUGFORMS_ID in (
				select CLSDRUGFORMS_ID from v_RISHDrugFormsLink with(nolock) where RISHDrugForms_id = :FEDDRUGFORMS_ID
			)";
			$params['FEDDRUGFORMS_ID'] = $data['FEDDRUGFORMS_ID'];
		}
		if (!empty($data['CLSATC_Code'])) {
			$filters[] = "CLSATC.NAME like '%'+:CLSATC_Code+'%'";
			$params['CLSATC_Code'] = $data['CLSATC_Code'];
		}
		if (!empty($data['Drug_Dose'])) {
			$filters[] = "D.Drug_Dose = :Drug_Dose";
			$params['Drug_Dose'] = $data['Drug_Dose'];
		}

		if (count($filters) == 0) {
			return $this->createError('','Не передан ни один параметр');
		}
		$filters_str = implode(" and ", $filters);

		//Фильтруем список второго уровня, затем по pid получаем список первого уровня
		$query = "
			with SecondLevelDCM as (
				select distinct
					DCM.DrugComplexMnn_pid as pid
				from
					rls.v_DrugComplexMnn DCM with(nolock)
					left join rls.v_Drug D with(nolock) on D.DrugComplexMnn_id = DCM.DrugComplexMnn_id
					left join rls.v_PREP_ATC PA with(nolock) on PA.PREPID = D.DrugPrep_id
					left join rls.v_CLSATC CLSATC with(nolock) on CLSATC.CLSATC_ID = PA.UNIQID
				where
					DCM.DrugComplexMnn_pid is not null and {$filters_str}
			)
			select
				DCM.DrugComplexMnn_id,
				DCM.DrugComplexMnn_RusName,
				DCM.CLSDRUGFORMS_ID
			from 
				rls.v_DrugComplexMnn DCM with(nolock)
			where
				DCM.DrugComplexMnn_id in (select pid from SecondLevelDCM)
		";

		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Метод для API. Получение состояние последнего обновления справочника (время и количестиво строк в нем)
	 */
	function getRefbookState($data) {

		list($schema, $tableName) = explode('.',$data['Refbook_TableName']);
		$schema = str_replace(chr(194).chr(160), "", $schema); // убрем NO-BREAK символы

		$view = "v_";
		if ($schema == "persis" && !in_array($tableName, array('postkind', 'Category')) || $tableName == "RecType") {
			$view = "";
		}

		$idFieldName = $tableName.'_id';
		$updateFieldName = $tableName.'_updDT';

		if ($schema == "persis") {
			$idFieldName = 'id';
			$updateFieldName = 'updDT'; // кое где может и не быть, см. fiction
		}

		$state = $this->queryResult(
			"
				select
					( select top 1
						max({$updateFieldName}) from {$schema}.{$view}{$tableName} (nolock)
					) as lastUpdateDT,
					( select top 1
						count({$idFieldName}) from {$schema}.{$view}{$tableName} (nolock)
					) as row_count
				from (select 0 as updDT) as fiction;

			", array()
		);

		if (!empty($state[0])) {

			$state = $state[0];
			if (!empty($state['lastUpdateDT']) && $state['lastUpdateDT'] instanceof DateTime) {
				$state['lastUpdateDT'] = $state['lastUpdateDT']->format('Y-m-d H:i:s');
			}

			return $state;

		} else return null;
	}
	
	/**
	 * Метод для API. Получение списка справочников
	 */
	function loadRefbookListAPI($data) {

		$this->load->library('swCache');

		// здесь будем подгружать либо актуальную дату из кэша либо из бд, если кэш устарел
		if ($this->swcache->get("RISHRefbook_List") && empty($data['recache'])) {

			$cachedResult = $this->swcache->get("RISHRefbook_List");
			// очистим кэш от ненужных параметров
			foreach ($cachedResult as $key => $r) { unset($cachedResult[$key]['_id']); }
			return $cachedResult;
		}

		$resp = $this->queryResult("
			select
				RISHRefbook_id,
				Refbook_TableName,
				RISHRefbook_Name
			from v_RISHRefbook (nolock)
		");

		if (!empty($resp)) {
			foreach ($resp as $key => $refbook) {

				$state = $this->getRefbookState(array('Refbook_TableName' => $refbook['Refbook_TableName']));

				$resp[$key]['lastUpdateDT'] = !empty($state['lastUpdateDT']) ? $state['lastUpdateDT'] : null ;
				$resp[$key]['row_count'] = !empty($state['row_count']) ? $state['row_count'] : null ;
			}
			$this->swcache->clear("RISHRefbook_List");
			$this->swcache->set("RISHRefbook_List", $resp, array('ttl'=> 24*60*60)); // 24 часа
		}

		return $resp;
	}

	/**
	 * uc_first
	 */
	function ucfirst_utf8($text) {
		return mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1);
	}

	/**
	 * Метод для API. Подгрузка справочника(ов) из БД
	 */
	function loadRefbookDataForApi($data) {

		$result = array();

		// если указан список загрузки справочиников, определяем имена таблиц
		if (!empty($data['load_list'])) {

			$json_array = (array)json_decode($data['load_list']);
			if (empty($json_array['load_list'])
				|| !empty($json_array['load_list']) && !is_array($json_array['load_list'])
			) return array('Error_Msg' => "Неверный формат входных данных");

			$data['load_list'] = $json_array['load_list'];

			$id_filter = "(";

			foreach ($data['load_list'] as $id) {$id_filter .= $id . ',';}

			$id_filter = rtrim($id_filter, ',');
			$id_filter .= ")";

			if (!empty($id_filter)) { //получим табличное имя справочника
				$load_items = $this->queryResult("
					select
						RISHRefbook_id,
						Refbook_TableName,
						RISHRefbook_Name
					from v_RISHRefbook (nolock)
					where RISHRefbook_id in {$id_filter}
				");
			}
		}

		if (!empty($load_items)) {

			$result['loaded_refbooks'] = 0;

			// получим значения
			foreach ($load_items as $i => $table) {

				if (!empty($table['Refbook_TableName'])) {

					$data['Refbook_TableName'] = $table['Refbook_TableName'];

					// получим дату последнего обновления из БД и количество строк
					$state = $this->getRefbookState(array('Refbook_TableName' => $data['Refbook_TableName']));

					// если число строк в справочнике меньше 20000 подгружаем его
					// или если он один то подгружаем с параметрами start, limit
					if (!empty($state['row_count']) && $state['row_count'] < 20000 || count($load_items) == 1) {

						$item_data = $this->loadRefbook($data); // получим контент справочника

						if (!empty($item_data)) {

							$result['loaded_refbooks'] += 1;

							$refbook = array(
								'RISHRefbook_id' => $table['RISHRefbook_id'],
								'Refbook_TableName' => $table['Refbook_TableName'],
								'RISHRefbook_Name' => $table['RISHRefbook_Name'],
								'lastUpdateDT' => $state['lastUpdateDT'],
								'row_count' => $state['row_count']
							);

							// уберем нулловые параметры из ответа
							foreach ($item_data as $key => $item) {
								foreach ($item as $fieldName => $field) {
									if (empty($field)) unset($item_data[$key][$fieldName]);
									if ($fieldName == "Code") {
										$item_data[$key][$fieldName] = (string)$field;
									}

									if ($fieldName == "Name") {
										$item_data[$key][$fieldName] = trim($field);

										// Да начнутся костыли!

										if ($table['Refbook_TableName'] == "dbo.LpuSectionProfile") {
											$item_data[$key][$fieldName] =  $this->ucfirst_utf8(($item_data[$key][$fieldName]));
										}
									}
								}
							}

							$refbook['loaded_rows'] = count($item_data);
							$refbook['content'] = $item_data;
						}
					}
				}

				if (!empty($refbook)) $result['refbooks'][] = $refbook;
			}
		}
		//die();
		return $result;
	}
}