<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Msk_Dlo_EvnRecept_model - модель, для работы с таблицей EvnRecept (Московская область)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan-it.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2019 Swan Ltd.
* @author       Bykov Stas aka Savage (savage@swan-it.ru)
* @version      21.10.2019
*/

require_once(APPPATH.'models/Dlo_EvnRecept_model.php');

class Msk_Dlo_EvnRecept_model extends Dlo_EvnRecept_model {
    /**
     * Конструктор
     */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Возвращает список рецептов введенных с заданной даты, для арма ЛЛО
	 */
	public function loadReceptList($data) {
		$filterList = [ "ER.Lpu_id = :Lpu_id" ];
		$queryParams = [];
		$queryParams['Lpu_id'] = $data['Lpu_id'];

		if( !empty($data['EvnRecept_pid']) ) {
			$filterList[] = "ER.EvnRecept_pid = :EvnRecept_pid";
			$queryParams['EvnRecept_pid'] = $data['EvnRecept_pid'];
		}

		if( !empty($data['begDate']) ) {
			$filterList[] = "ER.EvnRecept_setDT >= :begDate";
			$queryParams['begDate'] = $data['begDate'];
		}

		if( !empty($data['endDate']) ) {
			$filterList[] = "ER.EvnRecept_setDT <= :endDate";
			$queryParams['endDate'] = $data['endDate'];
		}
		
		if( !empty($data['Search_SurName']) ) {
			$filterList[] = "PS.Person_SurName like :Person_SurName + '%'";
			$queryParams['Person_SurName'] = rtrim($data['Search_SurName']);
		}
		
		if( !empty($data['Search_FirName']) ) {
			$filterList[] = "PS.Person_FirName like :Person_FirName + '%'";
			$queryParams['Person_FirName'] = rtrim($data['Search_FirName']);
		}
		
		if( !empty($data['Search_SecName']) ) {
			$filterList[] = "PS.Person_SecName like :Person_SecName + '%'";
			$queryParams['Person_SecName'] = rtrim($data['Search_SecName']);
		}
		
		if( !empty($data['Search_BirthDay']) ) {
			$filterList[] = "PS.Person_BirthDay = :Person_BirthDay";
			$queryParams['Person_BirthDay'] = $data['Search_BirthDay'];
		}
		
		if( !empty($data['Person_Snils']) ) {
			$filterList[] = "PS.Person_Snils = :Person_Snils";
			$queryParams['Person_Snils'] = $data['Person_Snils'];
		}
		
		$query = "
			select
				-- select
				 ER.EvnRecept_id
				,ER.EvnRecept_pid
				,ER.ReceptRemoveCauseType_id
				,ER.EvnRecept_deleted
				,ER.Person_id
				,ER.PersonEvn_id
				,ER.Server_id
				,ER.Drug_rlsid
				,ER.Drug_id
				,ER.DrugComplexMnn_id
				,RTrim(PS.Person_SurName) as Person_Surname
				,RTrim(PS.Person_FirName) as Person_Firname
				,RTrim(PS.Person_SecName) as Person_Secname
				,convert(varchar(10), PS.Person_BirthDay, 104) as Person_Birthday
				,convert(varchar(10), ER.EvnRecept_setDT, 104) as EvnRecept_setDate
				,RTrim(COALESCE(DRls.Drug_Name, Drug.Drug_Name, DCM.DrugComplexMnn_RusName, ER.EvnRecept_ExtempContents)) as Drug_Name
				,RTrim(ER.EvnRecept_Ser) as EvnRecept_Ser
				,RTrim(ER.EvnRecept_Num) as EvnRecept_Num
				,RTrim(MP.Person_FIO) as MedPersonal_Fio
				,mt.MorbusType_SysNick
				,mt.MorbusType_id
				,CASE WHEN ER.EvnRecept_IsSigned = 2 THEN 'true' ELSE 'false' END as EvnRecept_IsSigned
				,CASE WHEN ER.EvnRecept_IsPrinted = 2 THEN 'true' ELSE 'false' END as EvnRecept_IsPrinted
				,RT.ReceptType_Code
				,RT.ReceptType_Name
				,CASE WHEN RDT.ReceptDelayType_Code = 4 THEN 'true' ELSE 'false' END as Recept_MarkDeleted
				,RF.ReceptForm_id
				,RF.ReceptForm_Code
				,RF.ReceptForm_Name
				,cast(ER.EvnRecept_Kolvo as numeric(10, 2)) as EvnRecept_Kolvo
				,case
					when ER.EvnRecept_deleted = 2 then 'Аннулирован'
					when ER.ReceptRemoveCauseType_id is not null then RRCT.ReceptRemoveCauseType_Name
					when ER.ReceptDelayType_id is not null then RDT.ReceptDelayType_Name
					else 'Выписан'
				 end as EvnRecept_Status
				-- end select
			from
				-- from
				dbo.v_EvnRecept_all as ER with (nolock)
				inner join dbo.v_Person_FIO as PS on PS.Server_id = ER.Server_id
					and PS.PersonEvn_id = ER.PersonEvn_id
				cross apply (
					select top 1 Person_FIO
					from dbo.v_MedPersonal with (nolock)
					where MedPersonal_id = ER.MedPersonal_id
						and Lpu_id = :Lpu_id
				) MP
				left join dbo.v_ReceptType as RT on RT.ReceptType_id = ER.ReceptType_id
				left join dbo.v_ReceptForm as RF on RF.ReceptForm_id = ER.ReceptForm_id
				left join dbo.v_ReceptDelayType as RDT on RDT.ReceptDelayType_id = ER.ReceptDelayType_id
				left join dbo.v_ReceptRemoveCauseType as RRCT on RRCT.ReceptRemoveCauseType_id = ER.ReceptRemoveCauseType_id
				left join dbo.v_Drug as Drug on Drug.Drug_id = ER.Drug_id
				left join rls.v_Drug as DRls on DRls.Drug_id = ER.Drug_rlsid
				left join rls.v_DrugComplexMnn as DCM on DCM.DrugComplexMnn_id = ER.DrugComplexMnn_id
				left join dbo.v_WhsDocumentCostItemType as wdcit on wdcit.WhsDocumentCostItemType_id = ER.WhsDocumentCostItemType_id
				left join dbo.v_MorbusType as mt on mt.MorbusType_id = isnull(wdcit.MorbusType_id, 1)
				-- end from
			where
				-- where
				" . implode(" and ", $filterList) . "
				-- end where
			order by
				-- order by
				ER.EvnRecept_setDT desc
				-- end order by
		";

		$countQuery = getCountSQLPH($query);

		// определение общего количества записей
		$countResult = $this->db->query($countQuery, $queryParams);

		if ( !is_object($countResult) ) {
			return false;
		}

		$cnt_arr = $countResult->result('array');

		$count = $cnt_arr[0]['cnt'];

		if ( isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0 ) {
			$query = getLimitSQLPH($query, $data['start'], $data['limit']);
		}

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		return [
			'data' => $result->result('array')
			,'totalCount' => $count
		];
	}
}
