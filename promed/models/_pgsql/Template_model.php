<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Template_model - контроллер работы с шаблонами и референтными значениями
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Марков Андрей Александрович
* @version      декабрь 2010 года
*/

/**
 *
 * @property PersonBloodGroup_model $PersonBloodGroup_model
 * @property PersonWeight_model $PersonWeight_model
 * @property PersonHeight_model $PersonHeight_model
 * @property HeadCircumference_model $HeadCircumference_model
 * @property ChestCircumference_model $ChestCircumference_model
 * @property Polka_PersonDisp_model $PersonDisp_model
 * @property PersonMedHistory_model $PersonMedHistory_model
 * @property PersonAllergicReaction_model $PersonAllergicReaction_model
 * @property Privilege_model $Privilege_model
 * @property EvnMediaFiles_model $EvnMediaFiles_model
 * @property PMMediaData_model $PMMediaData_model
 *
 * @property PersonRegister_model $PersonRegister_model
 * @property MorbusVener_model $MorbusVener_model
 * @property MorbusTub_model $MorbusTub_model
 * @property MorbusCrazy_model $MorbusCrazy_model
 * @property MorbusHIV_model $MorbusHIV_model
 * @property MorbusOnkoVizitPLDop_model $MorbusOnkoVizitPLDop_model
 * @property MorbusOnkoLeave_model $MorbusOnkoLeave_model
 * @property MorbusHepatitis_model $MorbusHepatitis_model
 * @property MorbusOnkoSpecifics_model $MorbusOnkoSpecifics
 * @property MorbusOnkoBasePersonState_model $MorbusOnkoBasePersonState
 * @property MorbusOnkoBasePS_model $MorbusOnkoBasePS
 *
 * @property EvnUsluga_model $EvnUsluga
 * @property EvnDirection_model $EvnDirection
 * @property EvnDiag_model $EvnDiag_model
 * @property EvnDrug_model $EvnDrug_model
 * @property EvnPrescr_model $EvnPrescr_model
 * @property EvnPrescrList_model $EvnPrescrList_model
 * @property EvnPrescrTreat_model $EvnPrescrTreat_model
 * @property EvnPrescrProc_model $EvnPrescrProc_model
 * @property EvnPrescrOper_model $EvnPrescrOper_model
 * @property EvnPrescrLabDiag_model $EvnPrescrLabDiag_model
 * @property EvnPrescrFuncDiag_model $EvnPrescrFuncDiag_model
 * @property EvnPrescrConsUsluga_model $EvnPrescrConsUsluga_model
 * @property EvnPrescrRegime_model $EvnPrescrRegime_model
 * @property EvnPrescrDiet_model $EvnPrescrDiet_model
 * @property EvnPrescrVaccination_model $EvnPrescrVaccination_model
 * @property EvnPrescrObserv_model $EvnPrescrObserv_model
 * @property EvnStick_model $EvnStick_model
 * @property EvnPL_model $EvnPL_model
 * @property EvnPLStom_model $EvnPLStom_model
 * @property EvnPLDisp_model $EvnPLDisp_model
 * @property EvnVizit_model $EvnVizit_model
 * @property EvnDiagDopDisp_model $EvnDiagDopDisp_model
 * @property Dlo_EvnRecept_model $EvnRecept_model
 * @property EvnUslugaPar_model $EvnUslugaPar_model
 * @property EvnPS_model $EvnPS_model
 * @property BleedingCard_model $BleedingCard_model
 * @property RepositoryObserv_model $RepositoryObserv_model
 * @property EvnSection_model $EvnSection_model
 * @property EvnSectionNarrowBed_model $EvnSectionNarrowBed_model
 *
 * @property Common_model $Common_model
 * @property EvnXmlBase_model $EvnXmlBase_model
 *
 * @property-read MorbusIBS_model $MorbusIBS_model
 * @property-read MorbusNephro_model $MorbusNephro_model
 * @property-read EvnDiagNephro_model $EvnDiagNephro_model
 * @property-read MorbusNephroLab_model $MorbusNephroLab_model
 * @property-read MorbusNephroDisp_model $MorbusNephroDisp_model
 * @property-read MorbusNephroDialysis_model $MorbusNephroDialysis_model
 * @property-read NephroCommission_model $NephroCommission_model #135648
 * @property-read NephroAccess_model $NephroAccess_model         #135648
 * @property-read NephroDocument_model $NephroDocument_model     #135648
 */
class Template_model extends SwPgModel {
	var $scheme = "dbo";

	private $_evnChildClasses = null;

	/**
	 * TODO описать
	 */
	function __construct()
	{
		parent::__construct();

	}
	/**
	*  Читает часть данных (используя пейджинг) из таблицы референтных значений (RefValues)
	*/
	function loadRefValues($data)
	{
		if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
		{
			return false;
		}

		$params = array(
			'RefValues_Name' => "%".$data['RefValues_Name']."%",
			'RefValuesType_id' => $data['RefValuesType_id'],
			'RefCategory_id' => $data['RefCategory_id'],
			'RefMaterial_id' => $data['RefMaterial_id'],
			'Lpu_id'=>$data['Lpu_id']
			);

		$query = "
			Select
				-- select
				rv.RefValues_id as \"RefValues_id\",
				rv.RefValues_OPMUCode as \"RefValues_OPMUCode\",
				rv.RefValues_Name as \"RefValues_Name\",
				rv.RefValues_Nick as \"RefValues_Nick\",
				rv.RefValuesType_id as \"RefValuesType_id\",
				RefValuesType.RefValuesType_Name as \"RefValuesType_Name\",
				rv.RefCategory_id as \"RefCategory_id\",
				RefCategory.RefCategory_Name as \"RefCategory_Name\",
				rv.RefValues_LowerLimit as \"RefValues_LowerLimit\",
				rv.RefValues_UpperLimit as \"RefValues_UpperLimit\",
				rv.RefValuesUnit_id as \"RefValuesUnit_id\",
				RefValuesUnit.RefValuesUnit_Name as \"RefValuesUnit_Name\",
				rv.RefMaterial_id as \"RefMaterial_id\",
				RefMaterial.RefMaterial_Name as \"RefMaterial_Name\"
				-- end select
			from
				-- from
				v_RefValues rv
				inner join v_RefValuesType RefValuesType on RefValuesType.RefValuesType_id = rv.RefValuesType_id
				left join v_RefCategory RefCategory on RefCategory.RefCategory_id = rv.RefCategory_id
				left join v_RefValuesUnit RefValuesUnit on RefValuesUnit.RefValuesUnit_id = rv.RefValuesUnit_id
				left join v_RefMaterial RefMaterial on RefMaterial.RefMaterial_id = rv.RefMaterial_id
			-- end from
			where
				-- where
				(rv.RefValues_Name like (:RefValues_Name) or :RefValues_Name is null) and 
				(rv.RefValuesType_id = :RefValuesType_id or :RefValuesType_id is null) and 
				(rv.RefMaterial_id = :RefMaterial_id or :RefMaterial_id is null) and 
				(rv.RefCategory_id = :RefCategory_id or :RefCategory_id is null) 
				-- end where
			order by
				-- order by
				rv.RefValues_OPMUCode, rv.RefValues_id
				-- end order by
		";
		/*
		echo getDebugSql(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		exit;
		*/
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);

		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	*  Читает данные (не больше 50) для комбо из таблицы референтных значений (RefValues)
	*/
	function loadRefValuesList($data)
	{

		$params = array(
			'query' => (!isset($data['query']))?null:'%'.$data['query'].'%',
			'RefValues_id' => $data['RefValues_id'],
			'RefValuesType_id' => $data['RefValuesType_id'],
			'RefCategory_id' => $data['RefCategory_id'],
			'RefValues_Code' => (!isset($data['RefValues_Code']))?null:$data['RefValues_Code'].'%',
			'RefValues_Nick' => (!isset($data['RefValues_Nick']))?null:$data['RefValues_Nick'].'%',
			'RefValues_Name' => (!isset($data['RefValues_Name']))?null:$data['RefValues_Name'].'%',
			'Lpu_id'=>$data['Lpu_id']
			);
		//$filter = '';

		$query = "
			Select
				-- select
				rv.RefValues_id as \"RefValues_id\",
				rv.RefValues_Code as \"RefValues_Code\",
				rv.RefValues_Name as \"RefValues_Name\",
				rv.RefValues_Nick as \"RefValues_Nick\",
				rv.RefValuesType_id as \"RefValuesType_id\",
				rv.RefCategory_id as \"RefCategory_id\",
				rv.RefValues_LowerLimit as \"RefValues_LowerLimit\",
				rv.RefValues_UpperLimit as \"RefValues_UpperLimit\",
				rm.RefMaterial_Name as \"RefMaterial_Name\",
				rc.RefCategory_Name as \"RefCategory_Name\",
				RefValuesUnit.RefValuesUnit_Name as \"RefValuesUnit_Name\"
				-- end select
			from
				-- from
				v_RefValues rv
				left join v_RefValuesUnit RefValuesUnit on RefValuesUnit.RefValuesUnit_id = rv.RefValuesUnit_id
				left join v_RefMaterial rm on rm.RefMaterial_id = rv.RefMaterial_id
				left join v_RefCategory rc on rc.RefCategory_id = rv.RefCategory_id
				/*inner join v_RefValuesType RefValuesType on RefValuesType.RefValuesType_id = rv.RefValuesType_id
				left join v_RefCategory RefCategory on RefCategory.RefCategory_id = rv.RefCategory_id
				*/
			-- end from
			where
				-- where
				(rv.RefValues_Nick like (:query) or :query is null) and 
				(rv.RefValues_id = :RefValues_id or :RefValues_id is null) and 
				(rv.RefValuesType_id = :RefValuesType_id or :RefValuesType_id is null) and 
				(rv.RefCategory_id = :RefCategory_id or :RefCategory_id is null) and 
				(rv.RefValues_Code like (:RefValues_Code) or :RefValues_Code is null) and 
				(rv.RefValues_Nick like (:RefValues_Nick) or :RefValues_Nick is null) and 
				(rv.RefValues_Name like (:RefValues_Name) or :RefValues_Name is null) and 
				(rv.Lpu_id = :Lpu_id or Lpu_id is null)  
				-- end where
			order by
				-- order by
				rv.RefValues_Nick, rv.RefValues_id
				-- end order by
			limit 50
		";

		//echo getDebugSql($query, $params);exit;

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			$response = $result->result('array');
			return $response;
		}
		else {
			return false;
		}
	}
	/**
	*  Читает одну строку из таблицы референтных значений (RefValues)
	*/
	function editRefValues($data)
	{
		$params = array(
			'RefValues_id' => $data['RefValues_id'],
			'Lpu_id'=>$data['Lpu_id']
			);

		$query = "
			Select
				rv.RefValues_id as \"RefValues_id\",
				rv.Lpu_id as \"Lpu_id\",
				rv.RefValues_Code as \"RefValues_Code\",
				rv.RefValues_OPMUCode as \"RefValues_OPMUCode\",
				rv.RefValues_LocalCode as \"RefValues_LocalCode\",
				rv.RefValues_Name as \"RefValues_Name\",
				rv.RefValues_Nick as \"RefValues_Nick\",
				rv.RefValuesType_id as \"RefValuesType_id\",
				rv.RefValuesUnit_id as \"RefValuesUnit_id\",
				rv.RefValues_LowerLimit as \"RefValues_LowerLimit\",
				rv.RefValues_UpperLimit as \"RefValues_UpperLimit\",
				rv.RefValuesGroup_id as \"RefValuesGroup_id\",
				rv.RefValues_LowerAge as \"RefValues_LowerAge\",
				rv.RefValues_UpperAge as \"RefValues_UpperAge\",
				rv.AgeUnit_id as \"AgeUnit_id\",
				rv.RefCategory_id as \"RefCategory_id\",
				rv.HormonalPhaseType_id as \"HormonalPhaseType_id\",
				rv.TimeOfDay_id as \"TimeOfDay_id\",
				rv.RefMaterial_id as \"RefMaterial_id\",
				rv.RefValues_Cost as \"RefValues_Cost\",
				rv.RefValues_UET as \"RefValues_UET\",
				rv.RefValues_Method as \"RefValues_Method\",
				rv.RefValues_Description as \"RefValues_Description\"
			from
				v_RefValues rv
			where
				(rv.RefValues_id=:RefValues_id) and 
				(rv.Lpu_id = :Lpu_id or rv.Lpu_id is null)
			";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	*  Записывает одну строку в таблицу референтных значений (RefValues)
	*/
	function saveRefValues($data)
	{
		if ($data['RefValues_id']>0)
		{
			$proc = 'p_RefValues_upd';
		}
		else
		{
			$proc = 'p_RefValues_ins';
		}
		$params = array
		(
			'RefValues_id' => $data['RefValues_id'],
			'Lpu_id' => (!isSuperadmin())?$data['Lpu_id']:null, // Если пользователь - не суперадмин, тогда добавляем ЛПУ
			'RefValues_Code' => $data['RefValues_Code'],
			'RefValues_OPMUCode' => $data['RefValues_OPMUCode'],
			'RefValues_LocalCode' => $data['RefValues_LocalCode'],
			'RefValues_Name' => $data['RefValues_Name'],
			'RefValues_Nick' => $data['RefValues_Nick'],
			'RefValuesType_id' => $data['RefValuesType_id'],
			'RefValuesUnit_id' => $data['RefValuesUnit_id'],
			'RefValues_LowerLimit' => $data['RefValues_LowerLimit'],
			'RefValues_UpperLimit' => $data['RefValues_UpperLimit'],
			'RefValuesGroup_id' => $data['RefValuesGroup_id'],
			'RefValues_LowerAge' => $data['RefValues_LowerAge'],
			'RefValues_UpperAge' => $data['RefValues_UpperAge'],
			'AgeUnit_id' => $data['AgeUnit_id'],
			'RefCategory_id' => $data['RefCategory_id'],
			'HormonalPhaseType_id' => $data['HormonalPhaseType_id'],
			'TimeOfDay_id' => $data['TimeOfDay_id'],
			'RefMaterial_id' => $data['RefMaterial_id'],
			'RefValues_Cost' => $data['RefValues_Cost'],
			'RefValues_UET' => $data['RefValues_UET'],
			'RefValues_Method' => $data['RefValues_Method'],
			'RefValues_Description' => $data['RefValues_Description'],
			'pmUser_id' => $data['pmUser_id']
		);

		$query = "				
			select
				RefValues_id as \"RefValues_id\", 
				Error_Code as \"Error_Code\", 
				Error_Message as \"Error_Msg\"
			from {$proc} (
				RefValues_id := :RefValues_id, 
				Lpu_id := :Lpu_id,
				RefValues_Code := :RefValues_Code,
				RefValues_OPMUCode := :RefValues_OPMUCode,
				RefValues_LocalCode := :RefValues_LocalCode,
				RefValues_Name := :RefValues_Name,
				RefValues_Nick := :RefValues_Nick,
				RefValuesType_id := :RefValuesType_id,
				RefValuesUnit_id := :RefValuesUnit_id,
				RefValues_LowerLimit := :RefValues_LowerLimit,
				RefValues_UpperLimit := :RefValues_UpperLimit,
				RefValuesGroup_id := :RefValuesGroup_id,
				RefValues_LowerAge := :RefValues_LowerAge,
				RefValues_UpperAge := :RefValues_UpperAge,
				AgeUnit_id := :AgeUnit_id,
				RefCategory_id := :RefCategory_id,
				HormonalPhaseType_id := :HormonalPhaseType_id,
				TimeOfDay_id := :TimeOfDay_id,
				RefMaterial_id := :RefMaterial_id,
				RefValues_Cost := :RefValues_Cost,
				RefValues_UET := :RefValues_UET,
				RefValues_Method := :RefValues_Method,
				RefValues_Description := :RefValues_Description,
				pmUser_id := :pmUser_id
			)
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	*  Читает данные из таблицы XmlTemplateCat
	*/
	function loadXmlTemplateCatCombo($data)
	{

		$params = array();
		$filter_cat = '';
		if(empty($data['EvnClass_id']))
		{
			//назначения не показываем
			$filter_cat .= ' and xtc.EvnClass_id <> 63 ';
		}
		else
		{
			$params['EvnClass_id'] = $data['EvnClass_id'];
			$filter_cat .= ' and xtc.EvnClass_id = :EvnClass_id ';
		}

		if(!empty($data['XmlType_id']))
		{
			$params['XmlType_id'] = $data['XmlType_id'];
			$filter_cat .= ' and (xtc.XmlType_id is null or xtc.XmlType_id = :XmlType_id) ';
		}

		$query = "
			Select
				xtc.XmlTemplateCat_Name as \"XmlTemplateCat_Name\",
				xtc.EvnClass_id as \"EvnClass_id\",
				xtc.XmlType_id as \"XmlType_id\",
				xtc.XmlTemplateCat_id as \"XmlTemplateCat_id\"
			from
				v_XmlTemplateCat xtc
			where
				(1=1)
				{$filter_cat}
			order by
				xtc.XmlTemplateCat_Name
			";

		//echo getDebugSql($query, $params);exit;

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Функция чтения списка документов
	 * На выходе: JSON-строка
	 * Используется: swEmkDocumentsListWindow
	 */
	function loadEvnXmlList($data) {
		if(empty($data['Evn_rid']) && empty($data['EvnXml_id']) && empty($data['Person_id']))
		{
			return array();
		}
		$filters = '';
		switch ($data['filterDoc']) {
			case 'emk':
				// Все документы в рамках ЭМК пациента
				$filters .= 'doc.Person_id = (select Person_id from myvars)';
				break;
			default:
				// evn Все документы в рамках случая
				$filters .= 'doc.Evn_rid = (select Evn_rid from myvars)';
				break;
		}
		if(isset($data['EvnXml_id']))
		{
			$filters .= ' and doc.EvnXml_id != (select EvnXml_id from myvars)';
		}
		$query = "
			with myvars as (
				select
					:EvnXml_id as EvnXml_id,
					case 
						when ( coalesce(to_number(:Person_id),0) = 0 or coalesce(:Evn_rid,0) = 0 ) and :EvnXml_id is not null
						then (select Evn.Person_id from v_EvnXml doc inner join v_Evn Evn on Evn.Evn_id = doc.Evn_id where doc.EvnXml_id = :EvnXml_id)
						when coalesce(to_number(:Person_id),0) = 0 and :Evn_rid is not null
						then (select Evn.Person_id from v_Evn Evn where Evn.Evn_id = :Evn_rid)
						else :Person_id
					end as Person_id,
					case
						when ( coalesce(to_number(:Person_id),0) = 0 or coalesce(:Evn_rid,0) = 0 ) and :EvnXml_id is not null
						then (select Evn.Evn_rid from v_EvnXml doc inner join v_Evn Evn on Evn.Evn_id = doc.Evn_id where doc.EvnXml_id = :EvnXml_id)
						else :Evn_rid
					end as Evn_rid
			)
			select
				doc.EvnXml_id as \"EvnXml_id\"
				,doc.XmlType_id as \"XmlType_id\"
				,coalesce(PL.Diag_id,ES.Diag_id) as \"Diag_id\"
				,doc.Lpu_id as \"Lpu_id\"
				/*
				,doc.XmlTemplate_id as \"XmlTemplate_id\"
				,XmlTemplate.XmlTemplateType_id as \"XmlTemplateType_id\"
				,doc.Evn_rid as \"Evn_rid\"
				,doc.Evn_id as \"Evn_id\"
				,doc.Person_id as \"Person_id\"
				,EvnClass.EvnClass_id as \"EvnClass_id\"
				*/
				,to_char(doc.EvnXml_updDT,'DD.MM.YYYY') as \"EvnXml_updDT\"
				,XmlType.XmlType_Name as \"XmlType_Name\"
				,coalesce(UC.UslugaComplex_Name,doc.EvnXml_Name,EvnClass.EvnClass_Name) as \"EvnXml_Name\"
				,puc.PMUser_Name as \"pmUser_Name\"
			from
				(
					select
						doc.EvnXml_id
						,doc.XmlTemplate_id
						,coalesce(doc.XmlType_id,1) as XmlType_id
						,doc.EvnXml_updDT
						,doc.EvnXml_Name
						,doc.Evn_id
						,Evn.EvnClass_id
						,Evn.Evn_rid
						,Evn.Lpu_id
						,Evn.Person_id
						,doc.pmUser_insID
					from v_EvnXml doc inner join Evn on Evn.Evn_id = doc.Evn_id and coalesce(Evn.Evn_deleted,1) = 1
				) doc
				left join v_EvnVizitPL PL on PL.EvnVizitPL_id = doc.Evn_id
				left join v_EvnSection ES on ES.EvnSection_id = doc.Evn_id
				left join v_EvnUslugaPar UP on UP.EvnUslugaPar_id = doc.Evn_id and UP.EvnUslugaPar_setDT is not null
				left join v_UslugaComplex UC on UC.UslugaComplex_id = UP.UslugaComplex_id
				left join pmUserCache puc on doc.pmUser_insID = puc.pmUser_id
				inner join EvnClass on EvnClass.EvnClass_id = doc.EvnClass_id
				inner join XmlTemplate on XmlTemplate.XmlTemplate_id = doc.XmlTemplate_id
				inner join XmlType on XmlType.XmlType_id = doc.XmlType_id
			where
				". $filters ."
			order by
				doc.EvnXml_updDT desc
		";

        $result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return swFilterResponse::filterNotViewDiag($result->result('array'), $data);
		} else {
			return false;
		}
	}

	/**
	 * Функция чтения документа для формы просмотра
	 * На выходе: JSON-строка
	 * Используется: swEmkDocumentsListWindow
	 */
	function loadEvnXmlViewData($data) {
		return array(array('html'=>'<b>empty</b> '.'<b>'.$data['EvnXml_id'].'</b>'));
	}

	/**
	 * для дебага сделал и отрефакторил
	 */
	function getEvnDocumentDebug(&$map, $child_object = false, $pid = 0, $pdata = array()) {

		if (empty($map)) return '';
		$iteration = 0; $nl = "\xA"; $document = '';

		echo "############### getEvnDocument ###############".$nl;
		echo "child_object: ".$child_object.$nl;
		echo "pid: ".$pid.$nl;
		echo "pdata: ".var_dump($pdata).$nl;

		foreach($map as $object => &$list_data) {

			echo 'iteration: '. $iteration.$nl; $iteration++;
			echo 'map_obj: '. $object.$nl;

			$object_key = $list_data['object_key'];

			if (empty($list_data['template'])) continue; // пропускаем итерацию
			if ($child_object && $child_object != $object) continue; // пропускаем итерацию

			echo 'list_data[list].child_object.List:'. (!empty($list_data['list']) && !empty($list_data['list'][$child_object.'List']) ? $list_data['list'][$child_object.'List'] : "").$nl;

			if (!empty($pid)
				&& !empty($list_data['list'])
				&& !empty($list_data['list'][$child_object.'List'])
				&& !in_array($object_key, array('EvnXml_id', 'EvnXmlDirectionLink_id'))
			){

				$parse_data = array(
					'pid' => $pid,
					'items' => '',
					'item_arr' => array()
				);

				$parse_data = array_merge($parse_data, $pdata); // чтобы в шаблоне были доступны данные род.события

				if (!empty($list_data['item'])) {
					foreach($list_data['item'] as $i => &$object_data) {

						$dirObjects = array('EvnDirectionStom', 'EvnDirectionStac', 'EvnDirection');

						// для отображения документов в разделе направления,
						// для направлений с типом удаленная консультация
						if (in_array($object, $dirObjects)
							&& $object_key == 'EvnDirection_id'
							&& !empty($object_data['children'])
						){
							$pid2 = $object_data[$object_key];
							$parse_data['children'] = '';

							foreach($list_data['children_list'] as $obj_code) {
								if ($obj_code == 'EvnXmlDirectionLink') {
									// тут убираем id элементов, по которым происходит подстановка полей для редактирования. Документы в этом разделе должны быть только в режиме просмотра
									$object_data['data'][$obj_code] = swXmlTemplate::destroyInputParamIds( $this->getEvnDocument($object_data['children'],$obj_code,$pid2,$object_data['data']) );
								}
							}
						}

						if (!empty($list_data['extra'])) {
							$object_data['data'] = array_merge($object_data['data'], $list_data['extra']);
						}

						//echo var_dump($list_data['list']);

						if (empty($object_data['data']['use_template_str'])
							&& empty($list_data['use_item_arr'])
						) {
							$parse_data['items'] .= $this->parser->parse(
								$list_data['list'][$child_object.'List'],
								$object_data['data'],
								true
							);
						}

						if (!empty($object_data['data']['use_template_str'])
							&& !empty($list_data['template_str'])
						){
							$parse_data['items'] .= $this->parser->parse_string(
								$list_data['template_str'],
								$object_data['data'],
								true
							);
						}

						if (!empty($list_data['use_item_arr'])) {
							$parse_data['item_arr'][] = $object_data['data'];
						}

						//array_walk_recursive($object_data['data'],'ConvertFromWin1251ToUTF8');
					}
				}

				$section_id = $child_object.'_'.$pid;

				if (!empty($list_data['extra'])) {
					$parse_data = array_merge($parse_data, $list_data['extra']);
				}

				$document .= $this->getSection($section_id, $list_data['template'], $parse_data, false);
				$list_data['list'] = $child_object.'List'; // на клиент будем передавать только строку

			} else {

				echo 'object_key:'.$object_key.$nl;

				if ($pid > 0 &&
					!empty($list_data['list'])
					&& !empty($list_data['list'][$child_object.'List'])
					&& $object_key == 'EvnXml_id'
				) {
					$parse_data = array(
						'pid' => $pid,
						'items' => '',
						'item_arr' => array()
					);

					$section_id = $child_object.'_'.$pid;
					$document .= $this->getSection(
						$section_id,
						$list_data['list'][$child_object.'List'],
						$parse_data,
						false
					);

					$list_data['list'] = $child_object.'List'; // на клиент будем передавать только строку
				}

				if (empty($list_data['item']) || !is_array($list_data['item'])) continue;

				foreach($list_data['item'] as $i => &$object_data) {

					echo 'ch_list:'.var_dump($list_data['children_list']).$nl;

					$parse_data = $object_data['data'];

					if (!empty($object_data['children'])) {

						$pid = $object_data[$object_key];
						$parse_data['children'] = '';

						foreach($list_data['children_list'] as $obj_code) {

							echo 'childrenParent: '. $object_key.$nl;
							echo 'children: '. $obj_code.$nl;

							$parse_data[$obj_code] = $this->getEvnDocument(
								$object_data['children'],
								$obj_code,
								$pid,
								$object_data['data']
							);
						}
					}

					if (!empty($list_data['subsection'])){

						foreach($list_data['subsection'] as &$subsection){

							if ($subsection['template'] != 'xmltemplate'){

								echo 'subsection: '. $subsection['code'].$nl;

								$parse_data[$subsection['code']] = $this->parser->parse(
									$subsection['template'],
									$parse_data,
									true
								);

							} else {

								$evn_id = $object_data[$object_key];

								if ('EvnXml_Data' == $subsection['code']) {

									// не стоит для каждого документа из списка запрашивать данные из БД,
									// т.к. они уже были получены в EvnXmlBase_model::loadListViewData
									// Имитируем ответ EvnXmlBase_model::doLoadEvnXmlPanel
									$xml_data = array(array(
										'Evn_id' => $object_data['data']['EvnXml_pid'],
										'EvnClass_id' => $object_data['data']['EvnClass_id'],
										'XmlType_id' => $object_data['data']['XmlType_id'],
										'EvnXml_id' => $object_data['data']['EvnXml_id'],
										'EvnXml_Data' => $object_data['data']['EvnXml_Data'],
										'XmlTemplate_Settings' => $object_data['data']['XmlTemplate_Settings'],
										'XmlTemplate_HtmlTemplate' => $object_data['data']['XmlTemplate_HtmlTemplate'],
										'XmlTemplate_Data' => $object_data['data']['XmlTemplate_Data'],
										'Evn_pid' => $object_data['data']['Evn_pid'],
										'Evn_rid' => $object_data['data']['Evn_rid'],
										'Frame' => !empty($object_data['data']['Frame'])?$object_data['data']['Frame']:null
									));
									unset($object_data['data']['XmlTemplate_HtmlTemplate']);
									unset($object_data['data']['XmlTemplate_Data']);
									unset($object_data['data']['Evn_pid']);
									unset($object_data['data']['Evn_rid']);
									//$object_data['data']['EvnXml_Data'] = htmlspecialchars_decode($object_data['data']['EvnXml_Data']);
								} else {
									$params = array('Evn_id'=>$evn_id, 'XmlType_id'=>$subsection['XmlType_id']);
									if ($object_key == 'EvnXml_id') {
										$params = array('EvnXml_id'=>$evn_id, 'XmlType_id'=>$subsection['XmlType_id']);
									}
									if (isset($subsection['isLab']) && $subsection['isLab']) {
										$params['isLab'] = true;
									}
									$this->load->model('EvnXmlBase_model');
									$xml_data = $this->EvnXmlBase_model->doLoadEvnXmlPanel($params);
								}

								try {
									$html_from_xml = swEvnXml::doHtmlView($xml_data, $parse_data, $object_data);
									// скрытие фио/фамилии пациента
									if(isset($_REQUEST['from_MSE']) && $_REQUEST['from_MSE'] == 2 && $this->globalOptions['globals']['use_depersonalized_expertise'] && isset($object_data['data']['Person_FIO'])) {
										list($surname) = explode(" ", $object_data['data']['Person_FIO']);
										$html_from_xml = str_replace(array($object_data['data']['Person_FIO'], $surname), '***', $html_from_xml);
									}
								} catch (Exception $e) {
									$html_from_xml = '<div>'. $e->getMessage() .'</div>';
								}
								if (!empty($object_data['xml_data'])) {
									array_walk($object_data['xml_data'],'ConvertFromUTF8ToWin1251');
								}

								if($html_from_xml)
								{
									$parse_data[$subsection['code']] = $this->parser->parse_string($html_from_xml, $parse_data, true);
								}
								else
								{
									if($object == 'EvnVizitPL' && $object_data['data']['accessType'] == 'edit')
									{
										$parse_data[$subsection['code']] = '<span id="'.$subsection['code'].'_'.$evn_id.'_select" class="link">Выберите шаблон протокола осмотра</span>';
									}
									else
									{
										$parse_data[$subsection['code']] = '';
									}
									$object_data['emptyxmltemplate'] = true;
								}
							}
						}
					}

					$section_id = $object.'_'.$object_data[$object_key];
					$document .= $this->getSection($section_id,$list_data['template'], $parse_data, false);
					//array_walk_recursive($object_data['data'],'ConvertFromWin1251ToUTF8');
				}
			}
			unset($list_data['xml_data']);
			unset($list_data['template']);
			unset($list_data['use_item_arr']);
			unset($list_data['template_str']);
			unset($list_data['children_list']);
		}

		//echo var_dump($document);
		return $document;
	}

	/**
	 * Создает документ отображения события, пока нормально работает только для АПЛ, под документы и др. события придется модифицировать.
	 * На выходе: строка
	 */
	function getEvnDocument(&$map, $child_object = false, $pid = 0, $pdata = array()) {

		if (empty($map)) return '';

		// метод для дебажинга этой архитектуры
		//$document = $this->getEvnDocumentDebug($map, $child_object, $pid, $pdata);
		// если использовать, то код ниже до ретурна надо закомментить

		$document = '';

		foreach($map as $object => &$list_data)
		{
			if (empty($list_data['template']))
			{
				continue;
			}
			/* for test
			$list_data['test_'.$object.'_child_object_pid_'.mt_rand (0 ,100000000000)] = $pid;
			$list_data['test_'.$object.'_NOempty_list_'.mt_rand (0 ,100000000000)] = (!empty($list_data['list']));
			$list_data['test_'.$object.'_child_object_'.mt_rand (0 ,100000000000)] = $child_object;
			if (isset($list_data['list']))
			{
				$list_data['test_'.$object.'_list_'.mt_rand (0 ,100000000000)] = $list_data['list'];
				$list_data['test_'.$object.'_NOempty_'.$child_object.'List_'.mt_rand (0 ,100000000000)] = (!empty($list_data['list'][$child_object.'List']));
			}
			//$list_data['item']
			//$list_data['list'] $list = array('AllergHistoryList'=>'eew_allergic_reaction_item');
			*/

			$object_key = $list_data['object_key'];
			if ($child_object AND $child_object != $object)
			{
				continue;
			}
			if (!empty($pid) AND !empty($list_data['list']) AND !empty($list_data['list'][$child_object.'List']) && ! in_array($object_key, array('EvnXml_id', 'EvnXmlDirectionLink_id')) )
			{
				$parse_data = array('pid' => $pid,'items' => '','item_arr' => array());
				// чтобы в шаблоне были доступны данные род.события
				$parse_data = array_merge($parse_data, $pdata);
				if (!empty($list_data['item']))
				{
					foreach($list_data['item'] as $i => &$object_data)
					{
						// для отображения документов в разделе направления, для направлений с типом удаленная консультация
						if (in_array($object, array('EvnDirectionStom', 'EvnDirectionStac', 'EvnDirection')) && $object_key == 'EvnDirection_id' && !empty($object_data['children']))
						{
							$pid2 = $object_data[$object_key];
							$parse_data['children'] = '';
							foreach($list_data['children_list'] as $obj_code)
							{
								if ($obj_code == 'EvnXmlDirectionLink')
								{
									// тут убираем id элементов, по которым происходит подстановка полей для редактирования. Документы в этом разделе должны быть только в режиме просмотра
									$object_data['data'][$obj_code] = swXmlTemplate::destroyInputParamIds( $this->getEvnDocument($object_data['children'],$obj_code,$pid2,$object_data['data']) );
								}
							}
						}


						if (!empty($list_data['extra'])) {
							$object_data['data'] = array_merge($object_data['data'], $list_data['extra']);
						}

						if (empty($object_data['data']['use_template_str']) AND empty($list_data['use_item_arr']))
						{
							$parse_data['items'] .= $this->parser->parse($list_data['list'][$child_object.'List'], $object_data['data'], true);
						}
						if (!empty($object_data['data']['use_template_str']) AND !empty($list_data['template_str']))
						{
							$parse_data['items'] .= $this->parser->parse_string($list_data['template_str'], $object_data['data'], true);
						}
						if (!empty($list_data['use_item_arr']))
						{
							$parse_data['item_arr'][] = $object_data['data'];
						}
						//array_walk_recursive($object_data['data'],'ConvertFromWin1251ToUTF8');
					}
				}
				$section_id = $child_object.'_'.$pid;
				if (!empty($list_data['extra'])) {
					$parse_data = array_merge($parse_data, $list_data['extra']);
				}
				$document .= $this->getSection($section_id,$list_data['template'], $parse_data, false);
				// на клиент будем передавать только строку
				$list_data['list'] = $child_object.'List';
			}
			else
			{
				if ($pid > 0 AND !empty($list_data['list']) AND !empty($list_data['list'][$child_object.'List']) && $object_key == 'EvnXml_id')
				{
					$parse_data = array('pid' => $pid,'items' => '','item_arr' => array());
					// чтобы в шаблоне были доступны данные род.события
					//$parse_data = array_merge($parse_data, $pdata);
					$section_id = $child_object.'_'.$pid;
					$document .= $this->getSection($section_id, $list_data['list'][$child_object.'List'], $parse_data, false);
					// на клиент будем передавать только строку
					$list_data['list'] = $child_object.'List';
				}
				if (empty($list_data['item']) || !is_array($list_data['item']))
				{
					continue;
				}

				foreach($list_data['item'] as $i => &$object_data)
				{
					$parse_data = $object_data['data'];
					// чтобы в шаблоне были доступны данные род.события
					//$parse_data = array_merge($parse_data, $pdata);
					if (!empty($object_data['children']))
					{
						$pid = $object_data[$object_key];//(empty($pid))?$object_data[$object_key]:$pid;
						$parse_data['children'] = '';
						foreach($list_data['children_list'] as $obj_code)
						{
							$parse_data[$obj_code] = $this->getEvnDocument($object_data['children'],$obj_code,$pid,$object_data['data']);
						}
					}
					if (!empty($list_data['subsection']))
					{
						foreach($list_data['subsection'] as &$subsection)
						{
							if ($subsection['template'] != 'xmltemplate')
							{
								$parse_data[$subsection['code']] = $this->parser->parse($subsection['template'], $parse_data, true);
							}
							else
							{
								$evn_id = $object_data[$object_key];
								if ('EvnXml_Data' == $subsection['code']) {
									// не стоит для каждого документа из списка запрашивать данные из БД,
									// т.к. они уже были получены в EvnXmlBase_model::loadListViewData
									// Имитируем ответ EvnXmlBase_model::doLoadEvnXmlPanel
									$xml_data = array(array(
										'Evn_id' => $object_data['data']['EvnXml_pid'],
										'EvnClass_id' => $object_data['data']['EvnClass_id'],
										'XmlType_id' => $object_data['data']['XmlType_id'],
										'EvnXml_id' => $object_data['data']['EvnXml_id'],
										'EvnXml_Data' => $object_data['data']['EvnXml_Data'],
										'XmlTemplate_Settings' => $object_data['data']['XmlTemplate_Settings'],
										'XmlTemplate_HtmlTemplate' => $object_data['data']['XmlTemplate_HtmlTemplate'],
										'XmlTemplate_Data' => $object_data['data']['XmlTemplate_Data'],
										'Evn_pid' => $object_data['data']['Evn_pid'],
										'Evn_rid' => $object_data['data']['Evn_rid'],
										'Frame' => !empty($object_data['data']['Frame'])?$object_data['data']['Frame']:null,
										'instance_id' => !empty($object_data['data']['instance_id'])?$object_data['data']['instance_id']:null,
									));
									unset($object_data['data']['XmlTemplate_HtmlTemplate']);
									unset($object_data['data']['XmlTemplate_Data']);
									unset($object_data['data']['Evn_pid']);
									unset($object_data['data']['Evn_rid']);
									//$object_data['data']['EvnXml_Data'] = htmlspecialchars_decode($object_data['data']['EvnXml_Data']);
								} else {
									$params = array('Evn_id'=>$evn_id, 'XmlType_id'=>$subsection['XmlType_id']);
									if ($object_key == 'EvnXml_id') {
										$params = array('EvnXml_id'=>$evn_id, 'XmlType_id'=>$subsection['XmlType_id']);
                                    } else if ($object_key == 'EvnUslugaPar_id' && $this->usePostgreLis) {
                                        $res = $this->db->query("
											select EvnXml_id as \"EvnXml_id\" from v_EvnXml  where Evn_id = :Evn_id order by EvnXml_Index desc limit 1
										", array('Evn_id' => $evn_id));
                                        $resp = $res->result('array');
                                        if (count($resp) > 0) {
                                            $params = array('EvnXml_id'=>$resp[0]['EvnXml_id'], 'XmlType_id'=>$subsection['XmlType_id']);
                                        }
									}
									if (isset($subsection['isLab']) && $subsection['isLab']) {
										$params['isLab'] = true;
									}
									if (!empty($object_data['data']['instance_id'])) {
										$params['instance_id'] = $object_data['data']['instance_id'];
									}
									$this->load->model('EvnXmlBase_model');
									$xml_data = $this->EvnXmlBase_model->doLoadEvnXmlPanel($params);
								}

								try {
									$html_from_xml = swEvnXml::doHtmlView($xml_data, $parse_data, $object_data);
									// скрытие фио/фамилии пациента
									if(isset($_REQUEST['from_MSE']) && $_REQUEST['from_MSE'] == 2 && $this->globalOptions['globals']['use_depersonalized_expertise'] && isset($object_data['data']['Person_FIO'])) {
										list($surname) = explode(" ", $object_data['data']['Person_FIO']);
										$html_from_xml = str_replace(array($object_data['data']['Person_FIO'], $surname), '***', $html_from_xml);
									}
								} catch (Exception $e) {
									$html_from_xml = '<div>'. $e->getMessage() .'</div>';
								}
								if (!empty($object_data['xml_data'])) {
									array_walk($object_data['xml_data'],'ConvertFromUTF8ToWin1251');
								}

								if($html_from_xml)
								{
									$parse_data[$subsection['code']] = $this->parser->parse_string($html_from_xml, $parse_data, true);
								}
								else
								{
									if($object == 'EvnVizitPL' && $object_data['data']['accessType'] == 'edit')
									{
										$parse_data[$subsection['code']] = '<span id="'.$subsection['code'].'_'.$evn_id.'_select" class="link">Выберите шаблон протокола осмотра</span>';
									}
									else
									{
										$parse_data[$subsection['code']] = '';
									}
									$object_data['emptyxmltemplate'] = true;
								}
							}
						}
					}
					$section_id = $object.'_'.$object_data[$object_key];
					$document .= $this->getSection($section_id,$list_data['template'], $parse_data, false);
					//array_walk_recursive($object_data['data'],'ConvertFromWin1251ToUTF8');
				}

			}
			unset($list_data['xml_data']);
			unset($list_data['template']);
			unset($list_data['use_item_arr']);
			unset($list_data['template_str']);
			unset($list_data['children_list']);
		}
		return $document;
	}

	/**
	 * Создает массив объектов с дочерними объектами в определенном формате:
	 * На выходе: массив
	 */
	function getEvnData($data) {
		//$this->load->model('Registry_model', 'Reg_model');
		//$this->textlog->add('getEvnData: Старт! ');
		//$this->textlog->add('getEvnData: Параметры: '.serialize($data));
		$object = $data['object'];
		$object_id = $data['object_id'];

		$this->load->model("Options_model");
		$this->globalOptions = $this->Options_model->getOptionsGlobals($data);

		if (isset($data['parent_object_id']) AND isset($data['parent_object_value']))
		{
			//$parent_object_id = $data['parent_object_id'];
			$data[$object.'_pid'] = $data['parent_object_value'];
		}
		else
		{
			$data[$object_id] = $data['object_value'];
		}

		if ( !empty($data['param_name']) && !array_key_exists($data['param_name'], $data) )
		{
			$data[$data['param_name']] = $data['param_value'];
		}

		//Строка дополнение для определения шабонов для мобильного арма СМП
		$isMobileTemplate = ((isset($data['ARMType']))&&($data['ARMType']=='headBrigMobile'))? 'mobile_':'';

		/**
		* @parent_object string Системное имя родителя
		*/
		$parent_object = null;

		/**
		* @parent_key string Имя столбца с идентификатором родителя
		*/
		$parent_key = null;

		/**
		* @parent_value int Идентификатор родителя
		*/
		$parent_value = null;

		/**
		* @object_key string Имя столбца с первичным ключом
		*/
		$object_key = null;

		/**
		* @first_key string Имя дополнительного параметра для идентификаторов секций, кнопок
		*/
		$first_key = null;

		/**
		* @object_data array Массив данных объектов одного класса (например, направлений, посещений, рецептов), являющихся дочерними какого-либо объекта.
		*/
		$object_data = null;

		/**
		* @subsection array Список кодов субсекций
		*/
		$subsection = null;

		/**
		* @list array Массив с одним элементом: ключ - это код секции-списка, содержащей секции объектов одного класса (например, только рецепты), являющихся дочерними какого-либо объекта, а значение - имя шаблона секции дочерних объектов
		*/
		$list = null;

		/**
		* @children_list array Список кодов дочерних объектов, чтобы они отобразились в родительном шаблоне - они и там должны быть явно указаны
		*/
		$children_list = null;

		/**
		* @template string Имя шаблона секции
		*/
		$template = null;

		/**
		* @template_str string Строка с шаблоном для элемента секции списка, эта строка может быть использована если в ответе модель присутствует не пустое поле use_template_str
		*/
		$template_str = null;

		/**
		* @related_objects array Список конфигов связанных объектов для передачи на клиент, чтобы им тоже можно было определить экшены
		*/
		$related_objects = null;

		/**
		* @use_item_arr boolean Флаг того, что в секции-списке будет использоваться шаблон для элемента не из файла, что шаблон для элемента есть в шаблоне секции списка внутри {item_arr}...{/item_arr}
		*/
		$use_item_arr = null;

		/**
		* @xml_data boolean Признак того, что в секции объекта есть субсекция, содержимое которой формируется из XML-шаблона
		*/
		$xml_data = false;

		$filter_field = null;

		/**
		 * Дополнительные параметры для списокв
		 */
		$extra = array();

		//$test_id = $object.'_'.((isset($data['parent_object_value']))?$data['parent_object_value']:$data['object_value']);
		//$this->testmap[$test_id]['input'] = $data;
		//$this->textlog->add('getEvnData: Объект: '.$object);
		switch($object)
		{
			// в выборке обязательно должны быть идентификатор объекта и столбец Children_Count
			// методы модели должны уметь возвращать несколько записей по pid и одну запись по id

			case 'EvnMediaData':
				$this->load->model('EvnMediaFiles_model', 'EvnMediaFiles_model');
				$object_data = $this->EvnMediaFiles_model->getEvnMediaDataViewData($data);
				// Ссылки пока так
				for ($i = 0; $i < count($object_data); $i++) {
					if (mb_strtolower(mb_substr($object_data[$i]['EvnMediaData_FileName'], -3)) == 'dcm') {
						$object_data[$i]['EvnMediaData__Dir'] = 'http://'.$_SERVER["SERVER_NAME"].':8080/weasis-start/?cdb=/weasis&obj=';
						//$object_data[$i]['EvnMediaData_Link'] = "javascript:getWnd('swDicomViewerWindow').show({link: 'http://demo.swan.perm.ru:8080/dcm2img/?file=http://demo.swan.perm.ru/uploads/".$object_data[$i]['EvnMediaData_FilePath']."&format=png&h=600'});";
                        $http = (isset($_SERVER["HTTP_X_FORWARDED_PROTO"])?$_SERVER["HTTP_X_FORWARDED_PROTO"].'://':'http://');
                        $ha = $http.$_SERVER["SERVER_NAME"].':'.$_SERVER["SERVER_PORT"];
                        $object_data[$i]['EvnMediaData_Link'] = "http://".$_SERVER["SERVER_NAME"].":8080/dcm2img/?file=".$ha."/uploads/".$object_data[$i]['EvnMediaData_FilePath']."&format=png";
                    } else {
						$object_data[$i]['EvnMediaData_Link'] = $object_data[$i]['EvnMediaData__Dir'].$object_data[$i]['EvnMediaData_FilePath'];
					}
				}

				//$parent_key = 'EvnMediaData_pid';
				//$parent_value = $data['EvnMediaData_pid'];
				$object_key = 'EvnMediaData_id';
				$list = array('EvnMediaDataList'=>'eew_file_list_item');
				$template = 'eew_file_list';
				break;

			case 'MigrantContact':
				$this->load->model('EvnPLDispMigrant_model', 'EvnPLDispMigrant_model');
				$object_data = $this->EvnPLDispMigrant_model->getMigrantContactViewData($data);
				//$parent_key = 'EvnPLDispMigrant_id';
				//$parent_value = $data['EvnPLDispMigrant_id'];
				$object_key = 'MigrantContact_id';
				$list = array('MigrantContactList' => 'migrant_contact_item');
				$template = 'migrant_contact';
				break;

			case 'AllergHistory':
				$this->load->model('PersonAllergicReaction_model', 'PersonAllergicReaction_model');
				$object_data = $this->PersonAllergicReaction_model->getPersonAllergicReactionViewData($data);
				$parent_key = 'Person_id';
				$parent_value = $data['Person_id'];
				$object_key = 'PersonAllergicReaction_id';
				$list = array('AllergHistoryList' => $isMobileTemplate.'eew_allergic_reaction_item');
				$template = $isMobileTemplate.'eew_allergic_reaction';
				break;

            case 'FeedingType':
                $this->load->model('PersonFeedingType_model', 'PersonFeedingType_model');
                $object_data = $this->PersonFeedingType_model->loadPersonFeedingType($data);
                $parent_key = 'Person_id';
                $parent_value = $data['Person_id'];
                $object_key = 'FeedingTypeAge_id';
                $list = array('FeedingTypeList' => $isMobileTemplate . 'eew_feeding_type_item');
                $template = $isMobileTemplate . 'eew_feeding_type';
                break;

			case 'Anthropometry':
				// $this->load->model('Person_model', 'Person_model');
				// $object_data = $this->Person_model->getAnthropometryViewData($data);
				$object_data = array(array('Person_id' => $data['Person_id'], 'Children_Count' => 0));
				$children_list = array(
					'PersonHeight',
					'PersonWeight',
					'HeadCircumference',	// #182939 Окружность головы
					'ChestCircumference',	// #182939 Окружность груди
					'PersonRace',			// #183123 Раса
					'PersonPPT');
				$object_key = 'Person_id';
				$object_id = 'Person_id';
				$template = $isMobileTemplate.'eew_anthropometry';
				break;

			case 'PersonMedHistory':
				$this->load->model('PersonMedHistory_model', 'PersonMedHistory_model');
				$object_data = $this->PersonMedHistory_model->getPersonMedHistoryViewData($data);
				
				$this->load->model('PersonQuarantine_model', 'PersonQuarantine_model');
				$object_data[0]['PersonQuarantine'] = $this->PersonQuarantine_model->getPersonQuarantineViewData($data);
				
				$object_key = 'Person_id';
				$object_id = 'Person_id';
				$template = $isMobileTemplate.'eew_personmedhistory';
				break;

			case 'PersonLpuInfoPersData':
				$this->load->model('Person_model', 'Person_model');
				$object_data = $this->Person_model->loadPersonLpuInfoPanel($data);
				$object_key = 'PersonLpuInfo_id';
				$parent_key = 'Person_id';
				$parent_value = $data['Person_id'];
				$object_id = 'Person_id';
				$list = array('PersonLpuInfoPersDataList' => 'eew_personlpuinfopersdata_item');
				$template = $isMobileTemplate.'eew_personlpuinfopersdata';
				break;

			case 'PalliatInfoConsentData':
				$this->load->model('PalliatInfoConsent_model');
				$object_data = $this->PalliatInfoConsent_model->getPalliatInfoConsentViewData($data);
				$parent_key = 'Person_id';
				$parent_value = $data['Person_id'];
				$object_key = 'PalliatInfoConsent_id';
				$list = array('PalliatInfoConsentDataList' => 'eew_palliatinfoconsentdata_item');
				$template = 'eew_palliatinfoconsentdata';
				break;

			case 'PersonQuarantine':
				$this->load->model('PersonQuarantine_model');
				$object_data = $this->PersonQuarantine_model->doLoadGrid($data);
				$parent_key = 'Person_id';
				$parent_value = $data['Person_id'];
				$object_key = 'PersonQuarantine_id';
				$list = ['PersonQuarantineList' => 'eew_personquarantine_list_item'];
				$template = 'eew_personquarantine_list';
				break;

			case 'PersonSvidInfo':
				$this->load->library('swFilterResponse');
				$this->load->model('Person_model','pmodel');
				$object_data = $this->pmodel->getPersonSvidInfo($data);
				if (count($object_data)) {
					$object_data[0]['isMseDepers'] = (!empty($data['from_MSE']) && $data['from_MSE'] == 2 && $this->globalOptions['globals']['use_depersonalized_expertise']);
				}
				$parent_key = 'Person_id';
				$parent_value = $data['Person_id'];
				$object_key = 'PersonSvidInfo_id';
				$list = array('PersonSvidInfoList' => $isMobileTemplate.'eew_person_svid_item');
				$template = $isMobileTemplate . 'eew_person_svid_data';
				break;

			case 'PersonDispInfo':
				$this->load->library('swFilterResponse');
				$this->load->model('Polka_PersonDisp_model', 'PersonDisp_model');
				$object_data = $this->PersonDisp_model->getPersonDispViewData($data);
				$parent_key = 'Person_id';
				$parent_value = $data['Person_id'];
				$object_key = 'PersonDispInfo_id';
				$list = array('PersonDispInfoList' => $isMobileTemplate.'eew_person_disp_item');
				$template = $isMobileTemplate . 'eew_person_disp_data';
				break;

			case 'EvnPLDispInfo':
				$this->load->library('swFilterResponse');
				$this->load->model('EvnPLDisp_model', 'EvnPLDisp_model');
				$object_data = $this->EvnPLDisp_model->getEvnPLDispInfoViewData($data);
				foreach ($object_data as &$od) {
					$od['isMseDepers'] = (!empty($data['from_MSE']) && $data['from_MSE'] == 2 && $this->globalOptions['globals']['use_depersonalized_expertise']);
				}
				$parent_key = 'Person_id';
				$parent_value = $data['Person_id'];
				$object_key = 'EvnPLDispInfo_id';
				$list = array('EvnPLDispInfoList' => /*$isMobileTemplate.*/
					'eew_evn_pl_disp_info_item');
				$template = /*$isMobileTemplate.*/
					'eew_evn_pl_disp_info_data';
				break;

			case 'PersonHeight':
				if (empty($data['Person_id']) && !empty($data['PersonHeight_pid'])) {
					$data['Person_id'] = $data['PersonHeight_pid'];
				}
				$this->load->model('PersonHeight_model', 'PersonHeight_model');
				$object_data = $this->PersonHeight_model->getPersonHeightViewData($data);
				$parent_key = 'Person_id';
				$parent_value = $data['Person_id'];
				$object_key = 'PersonHeight_id';
				$list = array('PersonHeightList' => $isMobileTemplate.'eew_person_height_item');
				$template = $isMobileTemplate . 'eew_person_height_data';
				break;

			case 'PersonWeight':
				if (empty($data['Person_id']) && !empty($data['PersonWeight_pid'])) {
					$data['Person_id'] = $data['PersonWeight_pid'];
				}
				$this->load->model('PersonWeight_model', 'PersonWeight_model');
				$object_data = $this->PersonWeight_model->getPersonWeightViewData($data);
				$parent_key = 'Person_id';
				$parent_value = $data['Person_id'];
				$object_key = 'PersonWeight_id';
				$list = array('PersonWeightList' => $isMobileTemplate.'eew_person_weight_item');
				$template = $isMobileTemplate . 'eew_person_weight_data';
				break;

			// #182939 Окружность головы:
			case 'HeadCircumference':
				$this->load->model('HeadCircumference_model', 'HeadCircumference_model');
				$object_data = $this->HeadCircumference_model->getHeadCircumferenceViewData($data);
				$parent_key = 'Person_id';
				$parent_value = $data['Person_id'];
				$object_key = 'HeadCircumference_id';
				$list = array('HeadCircumferenceList' => $isMobileTemplate.'eew_head_circumference_item');
				$template = $isMobileTemplate.'eew_head_circumference_data';
				break;

			// #182939 Окружность груди:
			case 'ChestCircumference':
				$this->load->model('ChestCircumference_model', 'ChestCircumference_model');
				$object_data = $this->ChestCircumference_model->getChestCircumferenceViewData($data);
				$parent_key = 'Person_id';
				$parent_value = $data['Person_id'];
				$object_key = 'ChestCircumference_id';
				$list = array('ChestCircumferenceList' => $isMobileTemplate.'eew_chest_circumference_item');
				$template = $isMobileTemplate.'eew_chest_circumference_data';
				break;

			// #183123 Раса
			case 'PersonRace':
				$this->load->model('PersonRace_model', 'PersonRace_model');
				$object_data = $this->PersonRace_model->loadGrid($data);
				$parent_key = 'Person_id';
				$parent_value = $data['Person_id'];
				$object_key = 'PersonRace_id';
				$list = array('PersonRaceList' => $isMobileTemplate.'eew_person_race_item');
				$template = $isMobileTemplate.'eew_person_race_data';
				break;

			case 'PersonPPT':
				$object_data = array(array('pid' => $data['Person_id'], 'Person_id' => $data['Person_id'], 'Person_PPT' => null, 'Children_Count' => 0, 'showPPTBlock' => false));
				$children_list = array();
				$object_key = 'Person_id';
				$object_id = 'Person_id';
				$parent_key = 'Person_id';
				$parent_value = $data['Person_id'];
				$template = 'eew_person_ppt_data';

				$this->load->model('PersonWeight_model', 'PersonWeight_model');
				$heightData = $this->PersonWeight_model->getFirstRowFromQuery("
					select 
						PersonHeight_Height as \"PersonHeight_Height\", 
						Okei_id as \"Okei_id\"
					from v_PersonHeight where Person_id = :Person_id 
					order by PersonHeight_setDT desc limit 1
				", $data);

				if ($heightData !== false && !empty($heightData['PersonHeight_Height'])) {
					$weightData = $this->PersonWeight_model->getFirstRowFromQuery("
						select
							PersonWeight_Weight as \"PersonWeight_Weight\", 
							Okei_id as \"Okei_id\"
						from v_PersonWeight where Person_id = :Person_id 
						order by PersonWeight_setDT desc limit 1
					", $data);

					if ($weightData !== false && !empty($weightData['PersonWeight_Weight'])) {
						if ($weightData['Okei_id'] == 36) {
							$weightData['PersonWeight_Weight'] = $weightData['PersonWeight_Weight'] / 1000;
						}

						$object_data[0]['showPPTBlock'] = true;
						$object_data[0]['Person_PPT'] = round(sqrt($weightData['PersonWeight_Weight'] * $heightData['PersonHeight_Height'] / 3600), 2);
					}
				}
				break;

			case 'BloodData':
				$this->load->model('PersonBloodGroup_model', 'PersonBloodGroup_model');
				$object_data = $this->PersonBloodGroup_model->getPersonBloodGroupViewData($data);
				$parent_key = 'Person_id';
				$parent_value = $data['Person_id'];
				$object_key = 'BloodData_id';
				$list = array('BloodDataList'=>$isMobileTemplate.'eew_blooddata_item');
				$template = $isMobileTemplate.'eew_blooddata';
				break;

			case 'DiagList':
				$this->load->library('swFilterResponse');
				$this->load->model('EvnDiag_model', 'EvnDiag_model');
				$object_data = $this->EvnDiag_model->getDiagListViewData($data);
				$parent_key = 'Person_id';
				$parent_value = $data['Person_id'];
				$object_key = 'spec_id';
				$first_key = 'Diag_id';
				$list = array('DiagListList'=>$isMobileTemplate.'eew_diag_list_item');
				$template = $isMobileTemplate.'eew_diag_list';
				break;

			case 'ExpertHistory':
				$this->load->model('Privilege_model', 'Privilege_model');
				$object_data = $this->Privilege_model->getPersonPrivilegeViewData($data);
				$parent_key = 'Person_id';
				$parent_value = $data['Person_id'];
				$object_key = 'PersonPrivilege_id';
				$list = array('ExpertHistoryList' => $isMobileTemplate.'eew_expert_history_item');
				$template = $isMobileTemplate.'eew_expert_history';
				break;

			case 'EvnStickOpenInfo':
				$this->load->model('EvnStick_model', 'EvnStick_model');
				$object_data = $this->EvnStick_model->getEvnStickOpenInfoViewData($data);
				$parent_key = 'Person_id';
				$parent_value = $data['Person_id'];
				$object_key = 'EvnStick_id';
				$list = array('EvnStickOpenInfoList' =>/*$isMobileTemplate.*/
					'eew_evn_stick_open_info_item');
				$template = /*$isMobileTemplate.*/
					'eew_evn_stick_open_info_data';
				break;

			case 'PersonOnkoProfileInfo':
				$object_data = array(array('Person_id' => $data['Person_id'], 'Children_Count' => 0));
				$children_list = array('PersonOnkoProfile', 'PalliatNotify');
				$object_key = 'Person_id';
				$object_id = 'Person_id';
				$template = 'eew_persononkoprofile';
				break;

			case 'PersonDrugRequestInfo':
				$this->load->model('Dlo_EvnRecept_model');
				$object_data = $this->Dlo_EvnRecept_model->loadPersonDrugRequestPanel($data);
				$parent_key = 'Person_id';
				$parent_value = $data['Person_id'];
				$object_key = 'DrugRequest_id';
				$list = array('PersonDrugRequestInfoList'=>'eww_person_drug_request_item');
				$template = 'eww_person_drug_request';
				break;

			case 'PersonOnkoProfile':
				$this->load->model('OnkoCtrl_model');
				$object_data = $this->OnkoCtrl_model->loadPersonOnkoProfileList($data);
				$parent_key = 'Person_id';
				$parent_value = $data['Person_id'];
				$object_key = 'PersonOnkoProfile_id';
				$list = array('PersonOnkoProfileList'=>'null');
				$template = 'view_data/PersonOnkoProfile_list';
				$use_item_arr = true;
				break;

			case 'PalliatNotify':
				$this->load->model('EvnNotifyPalliat_model');
				$object_data = $this->EvnNotifyPalliat_model->getViewListData($data);
				$parent_key = 'Person_id';
				$parent_value = $data['Person_id'];
				$object_key = 'PalliatNotify_id';
				$list = array('PalliatNotifyList'=>'null');
				$template = 'view_data/PalliatNotify_list';
				$use_item_arr = true;
				break;

			case 'CmpCloseCard':
				$this->load->model('CmpCallCard_model', 'CmpCallCard_model');
				$object_data = $this->CmpCallCard_model->printCmpCloseCardEMK($data);
				if (count($object_data) > 0) $object_data[0]['ResultUfa_id'] = $this->CmpCallCard_model->getComboRelEMK($data['CmpCloseCard_id'], 'ResultUfa_id');
				if (count($object_data)) {
					$object_data[0]['isMseDepers'] = (!empty($data['from_MSE']) && $data['from_MSE'] == 2 && $this->globalOptions['globals']['use_depersonalized_expertise']);
				}
				$object_key = 'CmpCloseCard_id';
				$subsection = array(array('code' => 'CmpCloseCard_data', 'template' => 'print_form_emk'));
				/*if ($isMobileTemplate != '') {
					$children_list = array('EvnVizitPL','EvnStick');
				} else {
					$children_list = array('EvnVizitPL','EvnStick','EvnMediaData');
				}*/
				$template = 'print_form_emk';
				break;

			case 'CmpCallCard':
				$this->load->model('CmpCallCard_model', 'CmpCallCard_model');
				$object_data = $this->CmpCallCard_model->printCmpCallCardEMK($data);
				$object_data[0]['isMseDepers'] = (!empty($data['from_MSE']) && $data['from_MSE'] == 2 && $this->globalOptions['globals']['use_depersonalized_expertise']);
				//тут что-то непонятное
				//if (count($object_data)>0) $object_data[0]['ResultUfa_id'] = $this->CmpCallCard_model->getComboRelEMK($data['CmpCloseCard_id'], 'ResultUfa_id');
				$object_key = 'CmpCallCard_id';
				$subsection = array(array('code' => 'CmpCallCard_data', 'template' => 'print_callcard_form_emk'));
				/*if ($isMobileTemplate != '') {
					$children_list = array('EvnVizitPL','EvnStick');
				} else {
					$children_list = array('EvnVizitPL','EvnStick','EvnMediaData');
				}*/
				$template = 'print_callcard_form_emk';
				break;

			case 'EvnCostPrint':
				$this->load->model('CostPrint_model', 'CostPrint_model');
				$object_data = $this->CostPrint_model->getEvnCostPrintViewData($data);
				$object_key = 'EvnCostPrint_id';
				$template = 'eew_evn_cost_print_data';
				break;

			case 'CmpCallCardCostPrint':
				$this->load->model('CostPrint_model', 'CostPrint_model');
				$object_data = $this->CostPrint_model->getCmpCallCardCostPrintViewData($data);
				$object_key = 'CmpCallCardCostPrint_id';
				$template = 'eew_cmpcallcard_cost_print_data';
				break;

			case 'EvnPL':
				$this->load->model('EvnPL_model', 'EvnPL_model');
				$object_data = $this->EvnPL_model->getEvnPLViewData($data);
				if (!empty($object_data[0]['EvnPL_id'])) {
					$this->load->model('Registry' . (getRegionNick() == 'ufa' ? 'Ufa' : '') . '_model', 'Reg_model');
					$dbConnection = getRegistryChecksDBConnection();
					if ($dbConnection != 'default') {
						$this->regDB = $this->load->database($dbConnection, true);
						$this->Reg_model->db = $this->regDB;
					}
					if (in_array(getRegionNick(), array('buryatiya'/*, 'kareliya'*/))) {
						if ($this->Reg_model->checkEvnInRegistry(array(
								'EvnPL_id' => $object_data[0]['EvnPL_id'],
								'Lpu_id' => $data['Lpu_id']
							), 'edit') !== false) {
							$object_data[0]['accessType'] = 'view';
						}
					} else {
						$registryData = $this->Reg_model->checkEvnAccessInRegistry(array(
							'EvnPL_id' => $object_data[0]['EvnPL_id'],
							'Lpu_id' => $data['Lpu_id'],
							'session' => $data['session']
						), 'edit');

						if (is_array($registryData)) {
							if (!empty($registryData['Error_Msg'])) {
								$object_data[0]['accessType'] = 'view';
								$object_data[0]['AlertReg_Msg'] = $registryData['Error_Msg'];
							} elseif (!empty($registryData['Alert_Msg'])) {
								$object_data[0]['AlertReg_Msg'] = $registryData['Alert_Msg'];
							}
						}
					}
				}
				$object_key = 'EvnPL_id';
				$subsection = array(array('code' => 'EvnPL_data', 'template' => $isMobileTemplate . 'eew_evn_pl_data'));
				if ($isMobileTemplate != '') {
					$children_list = array('EvnVizitPL', 'EvnStick');
				} else {
					$children_list = array('EvnVizitPL', 'EvnStick', 'EvnMediaData');
				}
				$template = $isMobileTemplate . 'eew_evn_pl';
				break;

			case 'EvnPLDispAdult':
				$this->load->model('EvnPLDisp_model', 'EvnPLDisp_model');
				$object_data = $this->EvnPLDisp_model->getEvnPLDispAdultViewData($data);
				$object_key = 'EvnPLDisp_id';
				$children_list = array('EvnDiagDopDisp');
				$template = 'eew_evn_pl_disp_adult_data';
				break;

			case 'EvnPLDispDop13':
				$this->load->model('EvnPLDisp_model', 'EvnPLDisp_model');
				$this->load->model('EvnPLDispDop13_model');
				$object_key = 'EvnPLDispDop13_id';
				$object_data = $this->EvnPLDispDop13_model->getEvnPLDispDop13ViewData($data);
				$children_list = array('DopDispInfoConsent', 'EvnUslugaDispDop', 'EvnDiagDopDispBefore', 'HeredityDiag', 'ProphConsult', 'NeedConsult', 'EvnDiagDopDispFirst');

				$subsection = array(array('code' => 'EvnPLDispDop13_data', 'template' => 'eew_evn_pl_disp_dop13_data'));
				$template = 'eew_evn_pl_disp_dop13';
				break;

			case 'EvnPLDispProf':
				$this->load->model('EvnPLDisp_model', 'EvnPLDisp_model');
				$this->load->model('EvnPLDispProf_model');
				$object_key = 'EvnPLDispProf_id';
				$object_data = $this->EvnPLDispProf_model->getEvnPLDispProfViewData($data);
				$children_list = array('DopDispInfoConsent', 'EvnUslugaDispDop', 'EvnDiagDopDispBefore', 'HeredityDiag', 'ProphConsult', 'EvnDiagDopDispFirst');

				$subsection = array(array('code' => 'EvnPLDispProf_data', 'template' => 'eew_evn_pl_disp_prof_data'));
				$template = 'eew_evn_pl_disp_prof';
				break;

			case 'EvnPLDispOrp':
				$this->load->model('EvnPLDisp_model', 'EvnPLDisp_model');
				$this->load->model('EvnPLDispOrp13_model');
				$object_key = 'EvnPLDispOrp_id';
				$object_data = $this->EvnPLDispOrp13_model->getEvnPLDispOrpViewData($data);
				$children_list = array('DopDispInfoConsent', 'EvnVizitDispOrp', 'EvnUslugaDispOrp', 'EvnDiagAndRecomendation', 'EvnDiagDopDispAndRecomendation');

				$subsection = array(array('code' => 'EvnPLDispOrp_data', 'template' => 'eew_evn_pl_disp_orp_data'));
				$template = 'eew_evn_pl_disp_orp';
				break;

			case 'EvnPLDispTeenInspection':
				$this->load->model('EvnPLDisp_model', 'EvnPLDisp_model');
				$this->load->model('EvnPLDispTeenInspection_model');
				$object_key = 'EvnPLDispTeenInspection_id';
				$object_data = $this->EvnPLDispTeenInspection_model->getEvnPLDispTeenInspectionViewData($data);
				$children_list = array('DopDispInfoConsent', 'EvnUslugaDispDop');
				if (!empty($object_data[0]['DispClass_id']) && in_array($object_data[0]['DispClass_id'], array(10))) {
					$children_list[] = 'EvnDiagAndRecomendation';
					$children_list[] = 'EvnDiagDopDispAndRecomendation';
				}

				$subsection = array(array('code' => 'EvnPLDispTeenInspection_data', 'template' => 'eew_evn_pl_disp_teeninspection_data'));
				$template = 'eew_evn_pl_disp_teeninspection';
				break;

			case 'EvnPLDispChild':
				$this->load->model('EvnPLDisp_model', 'EvnPLDisp_model');
				$object_key = 'EvnPLDisp_id';
				$object_data = $this->EvnPLDisp_model->getEvnPLDispChildViewData($data);
				$children_list = array('EvnVizitDisp', 'EvnVizitDispRecommend');
				$template = 'eew_evn_pl_disp_child_data';
				break;

			case 'EvnPLDispMigrant':
				$this->load->model('EvnPLDisp_model', 'EvnPLDisp_model');
				$this->load->model('EvnPLDispMigrant_model');
				$object_key = 'EvnPLDispMigrant_id';
				$object_data = $this->EvnPLDispMigrant_model->getEvnPLDispMigrantViewData($data);
				$children_list = array('DopDispInfoConsent', 'EvnUslugaDispDop', 'EvnMediaData', 'MigrantContact');

				$subsection = array(array('code' => 'EvnPLDispMigrant_data', 'template' => 'eew_evn_pl_disp_migrant_data'));
				$template = 'eew_evn_pl_disp_migrant';
				break;

			case 'EvnPLDispDriver':
				$this->load->model('EvnPLDisp_model', 'EvnPLDisp_model');
				$this->load->model('EvnPLDispDriver_model');
				$object_key = 'EvnPLDispDriver_id';
				$object_data = $this->EvnPLDispDriver_model->getEvnPLDispDriverViewData($data);
				$children_list = array('DopDispInfoConsent', 'EvnUslugaDispDop', 'EvnMediaData');

				$subsection = array(array('code' => 'EvnPLDispDriver_data', 'template' => 'eew_evn_pl_disp_driver_data'));
				$template = 'eew_evn_pl_disp_driver';
				break;

			case 'EvnPS':
				$this->load->model('EvnPS_model', 'EvnPS_model');
				$object_data = $this->EvnPS_model->getEvnPSViewData($data);
				if (!empty($object_data[0]['EvnPS_id'])) {
					$this->load->model('Registry' . (getRegionNick() == 'ufa' ? 'Ufa' : '') . '_model', 'Reg_model');
					//$this->load->model('RegistryUfa_model', 'Reg_model');  //BOB - 11.04.2018
					//echo 'getRegionNick() = '.getRegionNick(); exit(); //BOB - 11.04.2018
					$dbConnection = getRegistryChecksDBConnection();
					if ($dbConnection != 'default') {
						$this->regDB = $this->load->database($dbConnection, true);
						$this->Reg_model->db = $this->regDB;
					}
					if (in_array(getRegionNick(), array('buryatiya'/*, 'kareliya'*/))) {
						if ($this->Reg_model->checkEvnInRegistry(array(
								'EvnPS_id' => $object_data[0]['EvnPS_id'],
								'Lpu_id' => $data['Lpu_id']
							), 'edit') !== false) {
							$object_data[0]['accessType'] = 'view';
						}
					} else {
						$registryData = $this->Reg_model->checkEvnAccessInRegistry(array(
							'EvnPS_id' => $object_data[0]['EvnPS_id'],
							'Lpu_id' => $data['Lpu_id'],
							'session' => $data['session']
						), 'edit');

						if (is_array($registryData)) {
							if (!empty($registryData['Error_Msg'])) {
								$object_data[0]['accessType'] = 'view';
								$object_data[0]['AlertReg_Msg'] = $registryData['Error_Msg'];
							} elseif (!empty($registryData['Alert_Msg'])) {
								$object_data[0]['AlertReg_Msg'] = $registryData['Alert_Msg'];
							}
						}
					}
				}
				$object_key = 'EvnPS_id';
				$subsection = array(array('code' => 'EvnPS_data', 'template' => $isMobileTemplate . 'eew_evn_ps_data'));
				if ($isMobileTemplate != '') {
					$children_list = array('EvnStick', 'EvnDiagDirectPS', 'EvnSection');
				} else {
					$children_list = array('EvnStick', 'EvnMediaData', 'EvnDiagDirectPS', 'EvnSection');
				}
				$template = $isMobileTemplate . 'eew_evn_ps';
				break;

			case 'EvnSection':
				//$this->load->library('swFilterResponse');
				$this->load->model('EvnSection_model', 'EvnSection_model');
				$this->load->model('Dlo_EvnRecept_model', 'EvnRecept_model');
				$object_data = $this->EvnSection_model->getEvnSectionViewData($data);
				$this->_evnChildClasses = array();
				if (!empty($object_data[0]['EvnSection_id'])) {
					$this->load->model('Registry' . (getRegionNick() == 'ufa' ? 'Ufa' : '') . '_model', 'Reg_model');
					$dbConnection = getRegistryChecksDBConnection();
					if ($dbConnection != 'default') {
						$this->regDB = $this->load->database($dbConnection, true);
						$this->Reg_model->db = $this->regDB;
					}
					if (in_array(getRegionNick(), array('buryatiya'/*, 'kareliya'*/))) {
						if ($this->Reg_model->checkEvnInRegistry(array(
								'EvnSection_id' => $object_data[0]['EvnSection_id'],
								'Lpu_id' => $data['Lpu_id']
							), 'edit') !== false) {
							$object_data[0]['accessType'] = 'view';
						}
					} else {
						$registryData = $this->Reg_model->checkEvnAccessInRegistry(array(
							'EvnSection_id' => $object_data[0]['EvnSection_id'],
							'Lpu_id' => $data['Lpu_id'],
							'session' => $data['session']
						), 'edit');

						if (is_array($registryData)) {
							if (!empty($registryData['Error_Msg'])) {
								$object_data[0]['accessType'] = 'view';
								$object_data[0]['AlertReg_Msg'] = $registryData['Error_Msg'];
							} elseif (!empty($registryData['Alert_Msg'])) {
								$object_data[0]['AlertReg_Msg'] = $registryData['Alert_Msg'];
							}
						}
					}
					if (in_array(getRegionNick(), array('perm'))) {
						$visible_data = $this->EvnRecept_model->getEvnReceptKardioVisibleData(array(
							'parent_object' => $data['parent_object'],
							'parent_object_value' => $data['parent_object_value']
						));
						$object_data[0]['EvnReceptKardio_isVisible'] = (isset($visible_data['is_visible']) && $visible_data['is_visible']);
					} else {
						$object_data[0]['EvnReceptKardio_isVisible'] = false;
					}
					if (in_array(getRegionNick(), array('vologda'))) {
						//история изменения врачей, только Вологда #192334
						$this->load->model('Evn_model', 'Evn_model');
						if (isset($object_data[0]['EvnSection_id'])){
							$object_data[0]['ListDoctorHistory']=$this->Evn_model->getDoctorHistoryEMKWrapper(["EvnDoctor_pid"=>$object_data[0]['EvnSection_id']]);
						}
					} else {
						$object_data[0]['ListDoctorHistory']=[];
					}

					foreach ($object_data as $evnsection) {
						$this->_evnChildClasses[$evnsection['EvnSection_id']] = array();

						$queryResult = $this->db->query("
							select EvnClass_SysNick as \"EvnClass_SysNick\"
							from v_Evn 
							where Evn_pid = :EvnSection_id
						", array(
							'EvnSection_id' => $evnsection['EvnSection_id']
						));

						if (!is_object($queryResult)) {
							return false;
						}

						$childEvnClassList = $queryResult->result('array');

						if (is_array($childEvnClassList)) {
							foreach ($childEvnClassList as $row) {
								$child = '';

								if (substr($row['EvnClass_SysNick'], 0, 12) == 'EvnDirection') {
									$child = 'EvnDirectionStac';
								} else if (substr($row['EvnClass_SysNick'], 0, 9) == 'EvnUsluga') {
									$child = 'EvnUslugaStac';
								} else if (substr($row['EvnClass_SysNick'], 0, 9) == 'EvnDiagPS') {
									$child = 'EvnDiagPS';
								} else if (substr($row['EvnClass_SysNick'], 0, 9) == 'EvnRecept') {
									$child = 'EvnReceptKardio';
								} else if (in_array($row['EvnClass_SysNick'], array('EvnReanimatPeriod', 'EvnDrug', 'EvnSectionNarrowBed'))) {
									$child = $row['EvnClass_SysNick'];
								}

								if (!empty($child) && !in_array($child, $this->_evnChildClasses[$evnsection['EvnSection_id']])) {
									$this->_evnChildClasses[$evnsection['EvnSection_id']][] = $child;
								}
							}
						}
					}
				}
				$object_key = 'EvnSection_id';
				$subsection = array(array('code'=>'EvnSection_data','template'=>$isMobileTemplate.'eew_evn_section_data'));
				if ($isMobileTemplate != '') {
					$children_list = array('EvnDiagPS', 'BleedingCard', 'RepositoryObserv', 'EvnDrug', 'EvnReceptKardio', 'EvnUslugaStac', 'EvnSectionNarrowBed', 'EvnObservGraphs', 'EvnPrescrPlan', 'EvnDirectionStac');
				} else {
					$children_list = array('EvnDiagPS', 'BleedingCard', 'RepositoryObserv', 'EvnDrug', 'EvnReceptKardio', 'EvnUslugaStac', 'EvnSectionNarrowBed', 'EvnPrescrPlan', 'EvnDirectionStac', 'EvnXmlProtokol', 'EvnXmlRecord', 'EvnXmlEpikriz', 'EvnXmlOther', 'EvnReanimatPeriod');  //BOB - 18.04.2017
				}
				$filter_field = 'EvnDiagPS_class';
				$template = $isMobileTemplate.'eew_evn_section';
				break;

			//BOB - 18.04.2017
			case 'EvnReanimatPeriod':
				$this->load->model('EvnReanimatPeriod_model', 'EvnReanimatPeriod_model');
				if (empty($data[$object . '_pid']) || !is_array($this->_evnChildClasses) || (isset($this->_evnChildClasses[$data[$object . '_pid']]) && in_array($object, $this->_evnChildClasses[$data[$object . '_pid']]))) {
					$object_data = $this->EvnReanimatPeriod_model->loadEvnReanimatPeriodViewData($data);
				}
				else {
					$object_data = array();
				}
				$object_key = 'EvnReanimatPeriod_id';
				$template = $isMobileTemplate . 'eew_evn_reanimatperiod';
				$list = array('EvnReanimatPeriodList' => $isMobileTemplate . 'eew_evn_reanimatperiod_item');
				$children_list = array('EvnReanimatCondition', 'EvnReanimatAction', 'EvnScale'); //BOB - 18.04.2017
				break;

			case 'EvnReanimatCondition':
				$this->load->model('EvnReanimatPeriod_model', 'EvnReanimatPeriod_model');
				$object_data = $this->EvnReanimatPeriod_model->loudEvnReanimatConditionGridEMK($data);
				$object_key = 'EvnReanimatCondition_id';
				break;

			case 'EvnReanimatAction':
				$this->load->model('EvnReanimatPeriod_model', 'EvnReanimatPeriod_model');
				$object_data = $this->EvnReanimatPeriod_model->loudEvnReanimatActionEMK($data);
				$object_key = 'EvnReanimatAction_id';
				break;

			case 'EvnScale':
				$this->load->model('EvnReanimatPeriod_model', 'EvnReanimatPeriod_model');
				$object_data = $this->EvnReanimatPeriod_model->loudEvnScaleGrid($data);
				$object_key = 'EvnScale_id';
				break;

			//BOB - 18.04.2017


			case 'EvnDrug':
				$this->load->model('EvnDrug_model', 'EvnDrug_model');
				if (empty($data[$object . '_pid']) || !is_array($this->_evnChildClasses) || (isset($this->_evnChildClasses[$data[$object . '_pid']]) && in_array($object, $this->_evnChildClasses[$data[$object . '_pid']]))) {
					$object_data = $this->EvnDrug_model->loadEvnDrugViewData($data);
				}
				else {
					$object_data = array();
				}
				$object_key = 'EvnDrug_id';
				$template = $isMobileTemplate . 'eew_evn_drug';
				$list = array('EvnDrugList' => $isMobileTemplate . 'eew_evn_drug_item');
				break;

			case 'EvnSectionNarrowBed':
				$this->load->model('EvnSectionNarrowBed_model', 'EvnSectionNarrowBed_model');
				if (empty($data[$object . '_pid']) || !is_array($this->_evnChildClasses) || (isset($this->_evnChildClasses[$data[$object . '_pid']]) && in_array($object, $this->_evnChildClasses[$data[$object . '_pid']]))) {
					$object_data = $this->EvnSectionNarrowBed_model->loadEvnSectionNarrowBedGrid($data);
				}
				else {
					$object_data = array();
				}
				$object_key = 'EvnSectionNarrowBed_id';
				$template = $isMobileTemplate . 'eew_evn_section_narrow_bed';
				$list = array('EvnSectionNarrowBedList' => $isMobileTemplate . 'eew_evn_section_narrow_bed_item');
				break;

			//Сопутствующие диагнозы направившего учреждения
			case 'EvnDiagDirectPS':
				$this->load->model('EvnDiag_model', 'EvnDiag_model');
				$object_data = $this->EvnDiag_model->loadEvnDiagPSGrid(array('class' => 'EvnDiagPSHosp', 'EvnDiagPS_pid' => $data['EvnDiagDirectPS_pid'], 'Lpu_id' => $data['Lpu_id']));
				$object_key = 'EvnDiagPS_id';
				$object_id = 'EvnDiagPS_id';
				$list = array('EvnDiagDirectPSList' => $isMobileTemplate . 'eew_direct_diag_item');
				$template = $isMobileTemplate . 'eew_direct_diag';
				break;

			//Сопутствующие диагнозы отделения, в т.ч. приемного
			case 'EvnDiagPS':
				$this->load->model('EvnDiag_model', 'EvnDiag_model');
				if (empty($data[$object . '_pid']) || !is_array($this->_evnChildClasses) || (isset($this->_evnChildClasses[$data[$object . '_pid']]) && in_array($object, $this->_evnChildClasses[$data[$object . '_pid']]))) {
					$object_data = $this->EvnDiag_model->loadEvnDiagPSGrid(array('class' => $data['filter_field'], 'EvnDiagPS_pid' => $data['EvnDiagPS_pid'], 'Lpu_id' => $data['Lpu_id']));
				}
				else {
					$object_data = array();
				}
				$object_key = 'EvnDiagPS_id';
				$list = array('EvnDiagPSList' => $isMobileTemplate . 'eew_evndiagps_item');
				$template = $isMobileTemplate . 'eew_evndiagps';
				break;

			//Карты наблюдения за кровотечением
			case 'BleedingCard':
				$this->load->model('BleedingCard_model', 'BleedingCard_model');
				$object_data = $this->BleedingCard_model->loadBleedingCardGrid([ 'EvnSection_id' => $data['BleedingCard_pid'] ]);
				$object_key = 'BleedingCard_id';
				$object_id = 'BleedingCard_id';
				$list = [ 'BleedingCardList' => $isMobileTemplate . 'eew_bleedingcard_item' ];
				$template = $isMobileTemplate.'eew_bleedingcard';
				break;

			//Наблюдения за пациентом с пневмонией, подозрением на COVID-19 и COVID-19
			case 'RepositoryObserv':
				$this->load->model('RepositoryObserv_model', 'RepositoryObserv_model');
				$object_data = $this->RepositoryObserv_model->loadList([ 'Evn_id' => $data['RepositoryObserv_pid'] ]);
				$object_key = 'RepositoryObserv_id';
				$object_id = 'RepositoryObserv_id';
				$list = [ 'RepositoryObservList' => $isMobileTemplate . 'RepositoryObserv_item' ];
				$template = $isMobileTemplate.'RepositoryObserv_list';
				break;

			// Графики наблюдений
			case 'EvnObservGraphs':
				$object_key = 'EvnObservGraphs_id';
				$object_id = 'EvnObservGraphs_id';
				$this->load->model('EvnPrescr_model');
				$response = $this->EvnPrescr_model->loadEvnObservGraphsData(array(
					'EvnObserv_pid' => $data['parent_object_value'],
					'loadAll' => 1,
				));
				$graph_data = $this->EvnPrescr_model->preparePrintEvnObservGraphsData($response);
				$object_data = array(array(
					// Т.к. у графиков нет своего ИД, мы назначаем родительский
					// данные для графиков тоже получаются по родительскому ИД
					'EvnObservGraphs_id' => $data['parent_object_value'],
					'data' => $graph_data,
				));
				$template = 'eew_evn_observgraphs';
				break;

			case 'EvnStick':
				//$this->load->library('swFilterResponse');
				$this->load->model('EvnStick_model', 'EvnStick_model');
				$object_data = $this->EvnStick_model->getEvnStickViewData($data);
				//$parent_key = 'EvnPL_id';
				//$parent_value = (isset($data['parent_object_value']))?$data['parent_object_value']:null;
				$object_key = 'EvnStick_id';
				$template = $isMobileTemplate . 'eew_evn_stick';
				$list = array('EvnStickList' => $isMobileTemplate . 'eew_evn_stick_item');
				break;

			case 'EvnVizitPL':
				$this->load->library('swEvnXml');
				$this->load->library('swFilterResponse');
				$this->load->model('EvnVizit_model', 'EvnVizit_model');
				$this->load->model('Dlo_EvnRecept_model', 'EvnRecept_model');
				$object_data = $this->EvnVizit_model->getEvnVizitPLViewData($data);
				$this->_evnChildClasses = array();
				if (!empty($object_data[0]['EvnVizitPL_id'])) {
					$this->load->model('Registry' . (getRegionNick() == 'ufa' ? 'Ufa' : '') . '_model', 'Reg_model');
					$dbConnection = getRegistryChecksDBConnection();
					if ($dbConnection != 'default') {
						$this->regDB = $this->load->database($dbConnection, true);
						$this->Reg_model->db = $this->regDB;
					}

					$count = 0;
					foreach ($object_data as $vizit) {
						if (in_array(getRegionNick(), array('buryatiya'/*, 'kareliya'*/))) {
							if ($this->Reg_model->checkEvnInRegistry(array(
									'EvnVizitPL_id' => $vizit['EvnVizitPL_id'],
									'Lpu_id' => $data['Lpu_id']
								), 'edit') !== false) {
								$object_data[$count]['accessType'] = 'view';
							}
						} else {
							$registryData = $this->Reg_model->checkEvnAccessInRegistry(array(
								'EvnVizitPL_id' => $vizit['EvnVizitPL_id'],
								'Lpu_id' => $data['Lpu_id'],
								'session' => $data['session']
							), 'edit');
							
							if (is_array($registryData)) {
								if (!empty($registryData['Error_Msg'])) {
									$object_data[$count]['accessType'] = 'view';
									$object_data[$count]['AlertReg_Msg'] = $registryData['Error_Msg'];
								} elseif (!empty($registryData['Alert_Msg'])) {
									$object_data[$count]['AlertReg_Msg'] = $registryData['Alert_Msg'];
								}
							}
						}
						
						$msf_id = !empty($vizit['MedStaffFact_id']) ? $vizit['MedStaffFact_id'] : null;
						if (in_array(getRegionNick(), array('perm')) && !empty($msf_id)) {
							$visible_data = $this->EvnRecept_model->getEvnReceptKardioVisibleData(array(
								'parent_object' => $data['parent_object'],
								'parent_object_value' => $data['parent_object_value'],
								'MedStaffFact_id' => $msf_id
							));
							$object_data[$count]['EvnReceptKardio_isVisible'] = (isset($visible_data['is_visible']) && $visible_data['is_visible']);
						} else {
							$object_data[$count]['EvnReceptKardio_isVisible'] = false;
						}
						
						$this->_evnChildClasses[$vizit['EvnVizitPL_id']] = array();

						$queryResult = $this->db->query("
							select EvnClass_SysNick as \"EvnClass_SysNick\"
							from v_Evn
							where Evn_pid = :EvnVizitPL_id
						", array(
							'EvnVizitPL_id' => $vizit['EvnVizitPL_id']
						));

						if (!is_object($queryResult)) {
							return false;
						}

						$childEvnClassList = $queryResult->result('array');

						if (is_array($childEvnClassList)) {
							foreach ($childEvnClassList as $row) {
								$child = '';

								if (substr($row['EvnClass_SysNick'], 0, 9) == 'EvnUsluga') {
									$child = 'EvnUsluga';
								} else if (substr($row['EvnClass_SysNick'], 0, 9) == 'EvnDiagPL') {
									$child = 'EvnDiagPL';
								} else if (in_array($row['EvnClass_SysNick'], array('EvnDirection', 'EvnDrug', 'EvnRecept', 'EvnReceptGeneral', 'EvnReceptKardio', 'EvnDirectionHistologic', 'EvnDirectionCVI', 'EvnDirectionCytologic'))) {
									$child = (in_array($row['EvnClass_SysNick'] , array('EvnDirectionHistologic', 'EvnDirectionCytologic', 'EvnDirectionCVI'))) ? 'EvnDirection' : $row['EvnClass_SysNick'];
								}

								if (!empty($child) && !in_array($child, $this->_evnChildClasses[$vizit['EvnVizitPL_id']])) {
									$this->_evnChildClasses[$vizit['EvnVizitPL_id']][] = $child;
								}
							}
						}
						$count++;
					}
				}

				$object_key = 'EvnVizitPL_id';
				$subsection = array(array('code' => 'EvnVizitPL_data', 'template' => $isMobileTemplate . 'eew_evn_vizitpl_data'), array('code' => 'EvnVizitPL_protocol', 'template' => 'xmltemplate', 'XmlType_id' => swEvnXml::EVN_VIZIT_PROTOCOL_TYPE_ID));
				$children_list = array('EvnDiagPL', 'EvnDrug', 'EvnPrescrPolka', 'EvnRecept', 'EvnReceptGeneral', 'EvnReceptKardio', 'EvnDirection', 'EvnUsluga');

				if (empty($isMobileTemplate)) {
					$children_list[] = 'FreeDocument';
					$children_list[] = 'EvnPLDispScreenOnko';
					$children_list[] = 'EvnXmlEpikriz';
				}

				$children_list[] = 'RepositoryObserv';

				$template = $isMobileTemplate . 'eew_evn_vizitpl';
				$xml_data = true;
				break;

			case 'EvnDiagPL':
				$this->load->library('swFilterResponse');
				$this->load->model('EvnDiag_model', 'EvnDiag_model');
				if (empty($data[$object . '_pid']) || !is_array($this->_evnChildClasses) || (isset($this->_evnChildClasses[$data[$object . '_pid']]) && in_array($object, $this->_evnChildClasses[$data[$object . '_pid']]))) {
					$object_data = $this->EvnDiag_model->getEvnDiagPLViewData($data);
				}
				else {
					$object_data = array();
				}
				$object_key = 'EvnDiagPL_id';
				$template = $isMobileTemplate.'eew_evn_diagpl';
				$list = array('EvnDiagPLList'=>$isMobileTemplate.'eew_evn_diagpl_item');
			break;

			case 'EvnRecept':
				$this->load->library('swFilterResponse');
				$this->load->model('Dlo_EvnRecept_model', 'EvnRecept_model');
				if (empty($data[$object . '_pid']) || !is_array($this->_evnChildClasses) || (isset($this->_evnChildClasses[$data[$object . '_pid']]) && in_array($object, $this->_evnChildClasses[$data[$object . '_pid']]))) {
					$object_data = $this->EvnRecept_model->getEvnReceptViewData($data);
				}
				else {
					$object_data = array();
				}
				if (count($object_data)) {
					$object_data[0]['isMseDepers'] = (!empty($data['from_MSE']) && $data['from_MSE'] == 2 && $this->globalOptions['globals']['use_depersonalized_expertise']);
				}
				$object_key = 'EvnRecept_id';
				$template = $isMobileTemplate.'eew_evn_recept';
				$list = array('EvnReceptList'=>$isMobileTemplate.'eew_evn_recept_item');
			break;

			case 'EvnReceptView':
				$this->load->model('Dlo_EvnRecept_model', 'EvnRecept_model');
				$object_data = $this->EvnRecept_model->getEvnReceptView($data);
				$object_data[0]['isMseDepers'] = (!empty($data['from_MSE']) && $data['from_MSE'] == 2 && $this->globalOptions['globals']['use_depersonalized_expertise']);
				$object_key = 'EvnRecept_id';
				$template = 'eew_evn_recept_view';
			break;

			case 'EvnReceptGeneral':
				$this->load->library('swFilterResponse');
				$this->load->model('Dlo_EvnRecept_model', 'EvnRecept_model');
				if (empty($data[$object . '_pid']) || !is_array($this->_evnChildClasses) || (isset($this->_evnChildClasses[$data[$object . '_pid']]) && in_array($object, $this->_evnChildClasses[$data[$object . '_pid']]))) {
					$object_data = $this->EvnRecept_model->getEvnReceptGeneralViewData($data);
				}
				else {
					$object_data = array();
				}
				if (count($object_data)) {
					$object_data[0]['isMseDepers'] = (!empty($data['from_MSE']) && $data['from_MSE'] == 2 && $this->globalOptions['globals']['use_depersonalized_expertise']);
				}
				$object_key = 'EvnReceptGeneral_id';
				$template = $isMobileTemplate.'eew_evn_recept_general';
				$list = array('EvnReceptGeneralList'=>$isMobileTemplate.'eew_evn_recept_general_item');
			break;

			case 'EvnReceptGeneralView':
				$this->load->model('Dlo_EvnRecept_model', 'EvnRecept_model');
				$object_data = $this->EvnRecept_model->getEvnReceptGeneralView($data);
				$object_data[0]['isMseDepers'] = (!empty($data['from_MSE']) && $data['from_MSE'] == 2 && $this->globalOptions['globals']['use_depersonalized_expertise']);
				$object_key = 'EvnReceptGeneral_id';
				$template = 'eew_evn_recept_general_view';
			break;

			case 'EvnReceptKardio':
				$this->load->library('swFilterResponse');
				$this->load->model('Dlo_EvnRecept_model', 'EvnRecept_model');
				if (empty($data[$object . '_pid']) || !is_array($this->_evnChildClasses) || (isset($this->_evnChildClasses[$data[$object . '_pid']]) && (in_array($object, $this->_evnChildClasses[$data[$object . '_pid']]) || in_array('EvnRecept', $this->_evnChildClasses[$data[$object . '_pid']])))) {
					$object_data = $this->EvnRecept_model->getEvnReceptKardioViewData($data);
				}
				else {
					$object_data = array();
				}
				if (count($object_data)) {
					$object_data[0]['isMseDepers'] = (!empty($data['from_MSE']) && $data['from_MSE'] == 2 && $this->globalOptions['globals']['use_depersonalized_expertise']);
				}
				$object_key = 'EvnRecept_id';
				$template = $isMobileTemplate.'eew_evn_recept_kardio';
				$list = array('EvnReceptKardioList'=>$isMobileTemplate.'eew_evn_recept_kardio_item');
			break;

			case 'EvnReceptKardioView':
				$this->load->model('Dlo_EvnRecept_model', 'EvnRecept_model');
				$object_data = $this->EvnRecept_model->getEvnReceptView($data);
				$object_data[0]['isMseDepers'] = (!empty($data['from_MSE']) && $data['from_MSE'] == 2 && $this->globalOptions['globals']['use_depersonalized_expertise']);
				$object_key = 'EvnRecept_id';
				$template = 'eew_evn_recept_view';
			break;

			case 'EvnDirection':
				$this->load->library('swFilterResponse');
				$this->load->model('EvnDirection_model', 'EvnDirection');
				if (empty($data[$object . '_pid']) || !is_array($this->_evnChildClasses) || (isset($this->_evnChildClasses[$data[$object . '_pid']]) && in_array($object, $this->_evnChildClasses[$data[$object . '_pid']]))) {
					$object_data = $this->EvnDirection->getEvnDirectionViewData($data);
				}
				else {
					$object_data = array();
				}
				$object_key = 'EvnDirection_id';
				$use_item_arr = false;
				$template_str = '
				<tr class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById(\'timetable_{timetable}_{timetable_id}_toolbar\').style.display=\'block\'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById(\'timetable_{timetable}_{timetable_id}_toolbar\').style.display=\'none\'"><td class="content"><div id="timetable_{timetable}_{timetable_id}"><span id="timetable_{timetable}_{timetable_id}_go" class="link" title="перейти по записи">{RecWhat}</span> {RecTo} / {RecDate} <span id="timetable_{timetable}_{timetable_id}_num">{EvnDirection_Num}</span></div></td><td class="toolbar"><div id="timetable_{timetable}_{timetable_id}_toolbar" class="toolbar"><!--<a id="timetable_{timetable}_{timetable_id}_print" class="button icon icon-print16" title="Печать направления"><span></span></a><a id="timetable_{timetable}_{timetable_id}_edit" class="button icon icon-edit16" title="Редактировать"><span></span></a>--><a id="timetable_{timetable}_{timetable_id}_delete" class="button icon icon-delete16" title="Удалить"><span></span></a></div></td></tr>';
				$children_list = array('EvnXmlDirectionLink');
				if ($isMobileTemplate != '') {
					$template_str = '<tr <td><span >{RecWhat}</span> {RecTo} / {RecDate} <span>{EvnDirection_Num}</span></td></tr>';
					$children_list = null;
				}
				$related_objects = array(array('field_code'=>'timetable','field_key'=>'timetable_id'));
				$template = $isMobileTemplate.'eew_evn_direction';
				$list = array('EvnDirectionList'=>$isMobileTemplate.'eew_evn_direction_item');
			break;

			case 'EvnXmlDirectionLink':
				$this->load->model('EvnXmlBase_model');
				$object_data = $this->EvnXmlBase_model->getEvnXmlForEvnDirectionList($data['parent_object_value']);
				$object_key = 'EvnXmlDirectionLink_id';
				$template = 'eew_evn_xml_direction_link';
				$xml_data = true;
			break;

			case 'EvnDirectionStac':
				$this->load->library('swFilterResponse');
				$this->load->model('EvnDirection_model', 'EvnDirection');
				if (empty($data[$object . '_pid']) || !is_array($this->_evnChildClasses) || (isset($this->_evnChildClasses[$data[$object . '_pid']]) && in_array($object, $this->_evnChildClasses[$data[$object . '_pid']]))) {
					$object_data = $this->EvnDirection->getEvnDirectionStacViewData($data);
				}
				else {
					$object_data = array();
				}
				$object_key = 'EvnDirection_id';
				$children_list = array('EvnXmlDirectionLink');
				$use_item_arr = false;
				$template_str = '
				<tr class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById(\'timetable_{timetable}_{timetable_id}_toolbar\').style.display=\'block\'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById(\'timetable_{timetable}_{timetable_id}_toolbar\').style.display=\'none\'"><td class="content"><div id="timetable_{timetable}_{timetable_id}"><span id="timetable_{timetable}_{timetable_id}_go" class="link" title="перейти по записи">{RecWhat}</span> {RecTo} / {RecDate} <span id="timetable_{timetable}_{timetable_id}_num">{EvnDirection_Num}</span></div></td><td class="toolbar"><div id="timetable_{timetable}_{timetable_id}_toolbar" class="toolbar"><!--<a id="timetable_{timetable}_{timetable_id}_print" class="button icon icon-print16" title="Печать направления"><span></span></a><a id="timetable_{timetable}_{timetable_id}_edit" class="button icon icon-edit16" title="Редактировать"><span></span></a>--><a id="timetable_{timetable}_{timetable_id}_delete" class="button icon icon-delete16" title="Удалить"><span></span></a></div></td></tr>';
				if ($isMobileTemplate != '') {
					$template_str = '<tr <td><span >{RecWhat}</span> {RecTo} / {RecDate} <span>{EvnDirection_Num}</span></td></tr>';
					$children_list = null;
				}
				$related_objects = array(array('field_code'=>'timetable','field_key'=>'timetable_id'));
				$template = ($isMobileTemplate)?$isMobileTemplate.'eew_evn_direction':'eew_evn_direction_stac';
				$list = array('EvnDirectionStacList'=>($isMobileTemplate)?$isMobileTemplate.'eew_evn_direction_item':'eew_evn_direction_stac_item');
				break;

			case 'EvnUslugaStac':
				$this->load->model('EvnUsluga_model', 'EvnUsluga');
				if (empty($data[$object . '_pid']) || !is_array($this->_evnChildClasses) || (isset($this->_evnChildClasses[$data[$object . '_pid']]) && in_array($object, $this->_evnChildClasses[$data[$object . '_pid']]))) {
					$object_data = $this->EvnUsluga->getEvnUslugaStacViewData($data);
				}
				else {
					$object_data = array();
				}
				if (count($object_data)) {
					$object_data[0]['isMseDepers'] = (!empty($data['from_MSE']) && $data['from_MSE'] == 2 && $this->globalOptions['globals']['use_depersonalized_expertise']);
				}
				$object_key = 'EvnUsluga_id';
				$object_id = 'EvnUsluga_id';
				$template = $isMobileTemplate.'eew_evn_uslugastac';
				$list = array('EvnUslugaStacList'=>$isMobileTemplate.'eew_evn_uslugastac_item');
			break;

			case 'EvnUsluga':
				$this->load->model('EvnUsluga_model', 'EvnUsluga');
				if (empty($data[$object . '_pid']) || !is_array($this->_evnChildClasses) || (isset($this->_evnChildClasses[$data[$object . '_pid']]) && in_array($object, $this->_evnChildClasses[$data[$object . '_pid']]))) {
					$object_data = $this->EvnUsluga->getEvnUslugaViewData($data);
				}
				else {
					$object_data = array();
				}
				if (is_array($object_data) && count($object_data)) {
					$object_data[0]['isMseDepers'] = (!empty($data['from_MSE']) && $data['from_MSE'] == 2 && $this->globalOptions['globals']['use_depersonalized_expertise']);
				}
				$object_key = 'EvnUsluga_id';
				$template = $isMobileTemplate.'eew_evn_usluga';
				$list = array('EvnUslugaList'=>$isMobileTemplate.'eew_evn_usluga_item');
			break;

			case 'EvnUslugaPar':
				$this->load->library('swEvnXml');
				$this->load->model('EvnUslugaPar_model', 'EvnUslugaPar_model');

                if ($this->usePostgreLis) {
                    $object_data = $this->EvnUslugaPar_model->getEvnUslugaParViewDataLis($data);
                }
                if (empty($object_data)) {
                    $object_data = $this->EvnUslugaPar_model->getEvnUslugaParViewData($data);
                }
				$object_key = 'EvnUslugaPar_id';
				$template = 'eew_evn_uslugapar';
				//
				//Проверяем наличие исследования на локальном PACS. Если не находим, то на глобальном
				//

				$this->load->model('Dicom_model', 'Dicom_model');

				$object_data[0]['StudyViews'] = $this->Dicom_model->getStudiesEmkView($data);
				$object_data[0]['LabStydyResult'] = $this->EvnUslugaPar_model->getLabStydyResultDoc($data);
				$object_data[0]['isMseDepers'] = (!empty($data['from_MSE']) && $data['from_MSE'] == 2 && $this->globalOptions['globals']['use_depersonalized_expertise']);

				$isLab = false;
				if (!empty($object_data[0]['isLab'])) {
					$isLab = true;
				}

				$subsection = array(array('code'=>'EvnUslugaPar_data','template'=>'eew_evn_uslugapar_data'),array('code'=>'EvnUslugaPar_protocol','template'=>'xmltemplate', 'XmlType_id' => swEvnXml::EVN_USLUGA_PROTOCOL_TYPE_ID, 'isLab' => $isLab));
				$children_list = array('EvnMediaData');
				$xml_data = true;
			break;

			case 'BactEvnUslugaPar':
				$this->load->library('swEvnXml');
				$this->load->model('EvnUslugaPar_model', 'EvnUslugaPar_model');

				if ($this->usePostgreLis) {
					$object_data = $this->EvnUslugaPar_model->getEvnUslugaParViewDataLis($data);
				}
				if (empty($object_data)) {
					$object_data = $this->EvnUslugaPar_model->getEvnUslugaParViewData($data);
				}

				$object_key = 'EvnUslugaPar_id';
				$template = 'eew_bact_evn_uslugapar';
				//
				//Проверяем наличие исследования на локальном PACS. Если не находим, то на глобальном
				//

				$this->load->model('Dicom_model', 'Dicom_model');

				$object_data[0]['StudyViews'] = $this->Dicom_model->getStudiesEmkView($data);
				$object_data[0]['LabStydyResult'] = $this->EvnUslugaPar_model->getLabStydyResultDoc($data);
				$object_data[0]['isMseDepers'] = (!empty($data['from_MSE']) && $data['from_MSE'] == 2 && $this->globalOptions['globals']['use_depersonalized_expertise']);

				$bactMicroList = $this->EvnUslugaPar_model->getBactMicroList($data);
				$bactAntibioticList = $this->EvnUslugaPar_model->getBactAntibioticList($data);
				$bactIsNotFind = empty($this->EvnUslugaPar_model->getBactMicroIsNotFind($data)) ? true : false;

				if (count($bactAntibioticList) > 0) {
					$uniqAntibioticList = array_unique(array_map(function ($elem) {
						return $elem['BactAntibiotic_Name'];
					}, $bactAntibioticList));
					function searchMicro($id, $name, $array)
					{
						foreach ($array as $key => $val) {
							if ($val['BactMicro_id'] == $id && $val['BactAntibiotic_Name'] == $name) {
								return $key;
							}
						}
						return null;
					}

					$bactAntibiotic_items = "";

					foreach ($uniqAntibioticList as $item) {
						$bactAntibiotic_items .= "<tr class='list-item'><td>{$item}</td>";

						foreach ($bactMicroList as $micro) {
							$indx = searchMicro($micro['BactMicro_id'], $item, $bactAntibioticList);
							$sense = $indx === null ? '-' : $bactAntibioticList[$indx]['BactMicroABPSens_ShortName'];
							$bactAntibiotic_items .= "<td>{$sense}</td>";
						}
						$bactAntibiotic_items .= "</tr>";
					}

					$bactAntibiotic_head = "<th>Антибиотикограмма**</th>";
					$bactAntibiotic_col = "";
					foreach ($bactMicroList as $micro) {
						$bactAntibiotic_head .= "<th>{$micro['RowNumber']}</th>";
						$bactAntibiotic_col .= "<col>";
					}
				}

				$object_data[0]['BactMicroList'] = $bactMicroList;
				$object_data[0]['BactAntibiotic_items'] = $bactAntibiotic_items ?? null;
				$object_data[0]['BactAntibiotic_head'] = $bactAntibiotic_head ?? null;
				$object_data[0]['BactAntibiotic_col'] = $bactAntibiotic_col ?? null;
				$object_data[0]['BactAntibiotic_isEmpty'] = empty($bactAntibiotic_items) ? true : false ;
				$object_data[0]['BactMicro_isEmpty'] = empty($bactMicroList) ? true : false ;
				$object_data[0]['BactIsNotFind'] = $bactIsNotFind;

				$isLab = false;
				if (!empty($object_data[0]['isLab'])) {
					$isLab = true;
				}

				$subsection = array(
					array(
						'code' => 'EvnUslugaPar_data',
						'template' => 'eew_bact_evn_uslugapar_data'
					),
					array(
						'code'=>'EvnUslugaPar_protocol',
						'template'=>'xmltemplate',
						'XmlType_id' => swEvnXml::EVN_USLUGA_PROTOCOL_TYPE_ID,
						'isLab' => $isLab
					)
				);
				$children_list = array('EvnMediaData');
				$xml_data = true;
				break;

			case 'EvnUslugaTelemed':
				$this->load->library('swEvnXml');
				$this->load->model('EvnUslugaTelemed_model', 'EvnUslugaTelemed_model');
				$this->load->model('Dlo_EvnRecept_model', 'EvnRecept_model');
				$object_data = $this->EvnUslugaTelemed_model->getEvnUslugaTelemedViewData($data);
				$object_key = 'EvnUslugaTelemed_id';
				$template = 'eew_evn_uslugatelemed';

				if (in_array(getRegionNick(), array('perm'))) {
					$visible_data = $this->EvnRecept_model->getEvnReceptKardioVisibleData(array(
						'parent_object' => $data['object'],
						'parent_object_value' => $data['object_value']
					));
					$object_data[0]['EvnReceptKardio_isVisible'] = (isset($visible_data['is_visible']) && $visible_data['is_visible']);
				} else {
					$object_data[0]['EvnReceptKardio_isVisible'] = false;
				}
				$object_data[0]['isVisibleEvnMediaData'] = $this->EvnUslugaTelemed_model->getEvnMediaDataExists($data);
				$subsection = array(array('code'=>'EvnUslugaTelemed_data','template'=>'eew_evn_uslugatelemed_data'),array('code'=>'EvnUslugaTelemed_protocol','template'=>'xmltemplate', 'XmlType_id' => 1));
				$children_list = array('EvnMediaData', 'EvnReceptKardio');
				$xml_data = true;
			break;

			case 'EvnUslugaCommon':
				$this->load->library('swEvnXml');
				$this->load->model('EvnUsluga_model', 'EvnUsluga_model');
				$object_data = $this->EvnUsluga_model->getEvnUslugaCommonViewData($data);
				//print_r($object_data);
				$object_key = 'EvnUslugaCommon_id';
				$template = 'eew_evn_uslugacommon';
				//$list = array('EvnUslugaList'=>$isMobileTemplate.'eew_evn_usluga_item');
				$object_data[0]['isMseDepers'] = (!empty($data['from_MSE']) && $data['from_MSE'] == 2 && $this->globalOptions['globals']['use_depersonalized_expertise']);

				$subsection = array(array('code'=>'EvnUslugaCommon_data','template'=>'eew_evn_uslugacommon_data'),array('code'=>'EvnUslugaCommon_protocol','template'=>'xmltemplate', 'XmlType_id' => swEvnXml::EVN_USLUGA_PROTOCOL_TYPE_ID));
				$children_list = array('EvnMediaData');
				$xml_data = true;
			break;

			case 'EvnUslugaOper':
				$this->load->library('swEvnXml');
				$this->load->model('EvnUsluga_model', 'EvnUsluga_model');
				$object_data = $this->EvnUsluga_model->getEvnUslugaOperViewData($data);
				//print_r($object_data);
				$object_key = 'EvnUslugaOper_id';
				$template = 'eew_evn_uslugaoper';
				//$list = array('EvnUslugaList'=>$isMobileTemplate.'eew_evn_usluga_item');

				$subsection = array(array('code'=>'EvnUslugaOper_data','template'=>'eew_evn_uslugaoper_data'),array('code'=>'EvnUslugaOper_protocol','template'=>'xmltemplate', 'XmlType_id' => swEvnXml::EVN_USLUGA_PROTOCOL_TYPE_ID));
				$children_list = array('EvnMediaData');
				$xml_data = true;
			break;

			case 'EvnDiagDopDisp':
				$this->load->library('swFilterResponse');
				$this->load->model('EvnDiagDopDisp_model', 'EvnDiagDopDisp_model');
				$object_data = $this->EvnDiagDopDisp_model->getEvnDiagDopDispViewData($data);
				$object_key = 'EvnDiagDopDisp_id';
				$template = 'eew_evn_diagdopdisp';
				$list = array('EvnDiagDopDispList'=>'eew_evn_diagdopdisp_item');
			break;

			case 'EvnVizitDisp':
				$this->load->library('swFilterResponse');
				$this->load->model('EvnPLDisp_model', 'EvnPLDisp_model');
				$object_data = $this->EvnPLDisp_model->getEvnVizitDispViewData($data);
				$object_key = 'EvnVizitDisp_id';
				$template = 'eew_evn_vizitdisp';
				$list = array('EvnVizitDispList'=>'eew_evn_vizitdisp_item');
			break;

			case 'EvnVizitDispRecommend':
				$this->load->library('swFilterResponse');
				$this->load->model('EvnPLDisp_model', 'EvnPLDisp_model');
				$object_data = $this->EvnPLDisp_model->getEvnVizitDispRecommendViewData($data);
				$object_key = 'EvnVizitDisp_id';
				$template = 'eew_evn_vizitdisp_recommend';
				$list = array('EvnVizitDispRecommendList'=>'eew_evn_vizitdisp_recommend_item');
			break;

			case 'EvnUslugaDispDop':
				$this->load->model('EvnPLDisp_model', 'EvnPLDisp_model');
				$object_data = $this->EvnPLDisp_model->getEvnUslugaDispDopViewData($data);

				// используем множественные назначения \ направления только для вод. комиссии
				if (in_array($data['parent_object'], array('EvnPLDispDriver'))) {

					if (!empty($object_data)) {
						foreach ($object_data as $dispdop) {
							if (!empty($dispdop['DispDopDirections_Count'])) {
								$data['EvnUslugaDispDopDirections'][$dispdop['DopDispInfoConsent_id']] = array(
									'EvnPrescr_Count' => (!empty($dispdop['EvnPrescr_Count']) ? $dispdop['EvnPrescr_Count'] : null),
									'EvnDirection_Count' => (!empty($dispdop['EvnDirection_Count']) ? $dispdop['EvnDirection_Count'] : null)
								);
							}
						}
					}

					$subsection = array(array('code'=>'EvnUslugaDispDop_data','template'=>'eew_evn_evnuslugadispdopdriver_item'));
					$template = 'eew_evn_evnuslugadispdopdriver';
					$children_list = array('EvnPrescrDispDop');

				} else {
					$list = array('EvnUslugaDispDopList'=>'eew_evn_evnuslugadispdop_item');
					$template = 'eew_evn_evnuslugadispdop';
				}

				$object_key = 'DopDispInfoConsent_id';

			break;

			case 'EvnPrescrDispDop':

				$this->load->model('EvnPLDisp_model', 'EvnPLDisp_model');
				$object_data = $this->EvnPLDisp_model->getEvnUslugaDispDopViewPrescrData($data, $data[$object . '_pid']);
				$object_key = 'EvnPrescrDispDop_id';
				$list = array('EvnPrescrDispDopList'=>'eew_evn_evnuslugadispdopprescr_item');
				$template = 'eew_evn_evnuslugadispdopprescr';
				break;

			case 'EvnVizitDispOrp':
				$this->load->model('EvnPLDisp_model', 'EvnPLDisp_model');
				$object_data = $this->EvnPLDisp_model->getEvnVizitDispOrpViewData($data);
				$object_key = 'EvnVizitDispOrp_id';
				$template = 'eew_evn_evnvizitdisporp';
				$list = array('EvnVizitDispOrpList'=>'eew_evn_evnvizitdisporp_item');
			break;

			case 'EvnUslugaDispOrp':
				$this->load->model('EvnPLDisp_model', 'EvnPLDisp_model');
				$object_data = $this->EvnPLDisp_model->getEvnUslugaDispOrpViewData($data);
				$object_key = 'EvnUslugaDispOrp_id';
				$template = 'eew_evn_evnuslugadisporp';
				$list = array('EvnUslugaDispOrpList'=>'eew_evn_evnuslugadisporp_item');
			break;

			case 'DopDispInfoConsent':
				$this->load->model('EvnPLDisp_model', 'EvnPLDisp_model');
				$object_data = $this->EvnPLDisp_model->getDopDispInfoConsentViewData($data);
				$object_key = 'DopDispInfoConsent_id';
				if (in_array($data['parent_object'], array('EvnPLDispMigrant', 'EvnPLDispDriver'))) {
					$template = 'eew_evn_dopdispinfoconsentmigrant';
					$list = array('DopDispInfoConsentList'=>'eew_evn_dopdispinfoconsentmigrant_item');
				} else {
					$template = 'eew_evn_dopdispinfoconsent';
					$list = array('DopDispInfoConsentList'=>'eew_evn_dopdispinfoconsent_item');
				}
			break;

			case 'EvnDiagDopDispBefore':
				$this->load->library('swFilterResponse');
				$this->load->model('EvnDiagDopDisp_model', 'EvnDiagDopDisp_model');
				$data['DeseaseDispType_id'] = 1;
				$data['EvnDiagDopDisp_pid'] = $data['EvnDiagDopDispBefore_pid'];
				$object_data = $this->EvnDiagDopDisp_model->getEvnDiagDopDispViewData($data);
				$object_key = 'EvnDiagDopDisp_id';
				$template = 'eew_evn_diagdopdisp';
				$extra['EvnDiagDopDispType'] = 'EvnDiagDopDispBefore'; // 'Ранее известные имеющиеся заболевания';
				$list = array('EvnDiagDopDispBeforeList'=>'eew_evn_diagdopdisp_item');
			break;

			case 'EvnDiagDopDispFirst':
				$this->load->library('swFilterResponse');
				$this->load->model('EvnDiagDopDisp_model', 'EvnDiagDopDisp_model');
				$data['DeseaseDispType_id'] = 2;
				$data['EvnDiagDopDisp_pid'] = $data['EvnDiagDopDispFirst_pid'];
				$object_data = $this->EvnDiagDopDisp_model->getEvnDiagDopDispViewData($data);
				$object_key = 'EvnDiagDopDisp_id';
				$template = 'eew_evn_diagdopdisp';
				$extra['EvnDiagDopDispType'] = 'EvnDiagDopDispFirst'; // 'Впервые выявленные заболевания';
				$list = array('EvnDiagDopDispFirstList'=>'eew_evn_diagdopdisp_item');
				break;

			case 'HeredityDiag':
				$this->load->library('swFilterResponse');
				$this->load->model('HeredityDiag_model', 'HeredityDiag_model');
				$object_data = $this->HeredityDiag_model->getHeredityDiagViewData($data);
				$object_key = 'HeredityDiag_id';
				$template = 'eew_evn_hereditydiag';
				$list = array('HeredityDiagList'=>'eew_evn_hereditydiag_item');
			break;

			case 'ProphConsult':
				$this->load->model('ProphConsult_model', 'ProphConsult_model');
				$object_data = $this->ProphConsult_model->getProphConsultViewData($data);
				$object_key = 'ProphConsult_id';
				$template = 'eew_evn_prophconsult';
				$list = array('ProphConsultList'=>'eew_evn_prophconsult_item');
			break;

			case 'NeedConsult':
				$this->load->model('NeedConsult_model', 'NeedConsult_model');
				$object_data = $this->NeedConsult_model->getNeedConsultViewData($data);
				$object_key = 'NeedConsult_id';
				$template = 'eew_evn_needconsult';
				$list = array('NeedConsultList'=>'eew_evn_needconsult_item');
			break;

			case 'EvnDiagAndRecomendation':
				$this->load->library('swFilterResponse');
				$this->load->model('EvnPLDisp_model', 'EvnPLDisp_model');
				$object_data = $this->EvnPLDisp_model->getEvnDiagAndRecomendationViewData($data);
				$object_key = 'EvnVizitDispDop_id';
				$template = 'eew_evn_evndiagandrecomendation';
				$template_item = 'eew_evn_evndiagandrecomendation_item';
				if ($data['parent_object'] == 'EvnPLDispOrp') {
					$object_key = 'EvnVizitDispOrp_id';
					//$template = 'eew_evn_evndiagandrecomendation_disporp';
					$template_item = 'eew_evn_evndiagandrecomendation_disporp_item';
				}
				$list = array('EvnDiagAndRecomendationList'=>$template_item);
			break;

			case 'EvnDiagDopDispAndRecomendation':
				$this->load->library('swFilterResponse');
				$this->load->model('EvnDiagDopDisp_model', 'EvnDiagDopDisp_model');
				$data['EvnDiagDopDisp_pid'] = $data['EvnDiagDopDispAndRecomendation_pid'];
				$object_data = $this->EvnDiagDopDisp_model->getEvnDiagDopDispViewData($data);
				$object_key = 'EvnDiagDopDisp_id';
				$template = 'eew_evn_diagdopdisp';
				$extra['EvnDiagDopDispType'] = 'EvnDiagDopDispAndRecomendation'; // 'Состояние здоровья до проведения диспансеризации / профосмотра';
				$list = array('EvnDiagDopDispAndRecomendationList'=>'eew_evn_diagdopdisp_item');
			break;

			case 'SignalInformationAll':
				$this->load->model('Common_model', 'Common_model');
				$this->load->model('PMMediaData_model', 'PMMediaData_model');
				$object_data = $this->Common_model->loadPersonData($data, 'EmkPanel');
				$object_data[0]['pmMediaData_ObjectName'] = 'PersonPhoto';
				$object_data[0]['PersonPhotoThumbName'] = $this->PMMediaData_model->getPersonPhotoThumbName($object_data[0]);
				$object_data[0]['ParentSection'] = 'SignalInformationAll';
				$object_data[0]['isMseDepers'] = (!empty($data['from_MSE']) && $data['from_MSE'] == 2 && $this->globalOptions['globals']['use_depersonalized_expertise']);
				if(getRegionNick()=='vologda') {
					$this->load->model('Polka_PersonDisp_model', 'Polka_PersonDisp_model');
					$dmTemp = $this->Polka_PersonDisp_model->getMonitorTemperatureInfo($data);
					$object_data[0]['MonitorTemperatureStartDate'] = count($dmTemp)>0 ? $dmTemp[0]['MonitorTemperatureStartDate'] : '';
					$object_data[0]['MonitorTemperatureLpuEnable'] = $object_data[0]['Lpu_id']==$data['Lpu_id'];
				}
				$object_key = 'Person_id';
				$subsection = array(array('code'=>'person_data','template'=>$isMobileTemplate.'eew_person_data'));
				$children_list = array('Anthropometry', 'FeedingType','PersonMedHistory','BloodData',
					'AllergHistory', 'ExpertHistory', 'DiagList', 'PersonSvidInfo', 'PersonDispInfo',
					'EvnPLDispInfo', 'SurgicalList', 'DirFailList', 'EvnStickOpenInfo', 'PersonLpuInfoPersData', 'PalliatInfoConsentData'
				);// 'PersonHeight', 'PersonWeight'
				if( getRegionNick() != 'kz' ) {
					$children_list[] = 'PersonQuarantine';
				}
				if ( $data['session']['region']['nick'] == 'ufa' ){
					$children_list[] = 'PersonOnkoProfileInfo';
					array_push($children_list,'MantuReaction','Inoculation','InoculationPlan');
				}
				$template = $isMobileTemplate.'eew_signal_info_all';
			break;

			case 'SurgicalList':
				$this->load->model('EvnUsluga_model', 'EvnUsluga');
				$object_data = $this->EvnUsluga->getEvnUslugaOperViewData($data);
				$list = array('SurgicalListList'=>$isMobileTemplate.'eew_surgical_list_item');
				$template = $isMobileTemplate.'eew_surgical_list';
				$parent_key = 'Person_id';
				$parent_value = $data['Person_id'];
				$object_key = 'EvnUslugaOper_id';
			break;
			case 'DirFailList':
				$this->load->model('EvnDirection_model', 'EvnDirection');
				$object_data = $this->EvnDirection->getDirFailListViewData($data);
				$list = array('DirFailListList'=>/*$isMobileTemplate.*/'eew_dirfail_list_item');
				$template = /*$isMobileTemplate.*/'eew_dirfail_list';
				$parent_key = 'Person_id';
				$parent_value = $data['Person_id'];
				$object_key = 'EvnDirection_id';
			break;
			case 'MantuReaction':
				//$this->load->model('EvnUsluga_model', 'EvnUsluga');
				$this->load->model('Vaccine_model','VacModel');
				$object_data = $this->VacModel->getMantuReaction($data);
				$list = array('MantuReactionList'=>$isMobileTemplate.'eew_mantureaction_list_item');
				$template = $isMobileTemplate.'eew_mantureaction_list';
				$parent_key = 'Person_id';
				$parent_value = $data['Person_id'];
				$object_key = 'dateVac';
			break;
			case 'Inoculation':
				$this->load->model('Vaccine_model','VacModel');
				$object_data = $this->VacModel->getInoculationData($data);
				$list = array('InoculationList'=>$isMobileTemplate.'eew_inoculation_list_item');
				$template = $isMobileTemplate.'eew_inoculation_list';
				$parent_key = 'Person_id';
				$parent_value = $data['Person_id'];
				$object_key = 'Inoculation_id';
			break;
			case 'InoculationPlan':
				$this->load->model('Vaccine_model','VacModel');
				$object_data = $this->VacModel->getInoculationPlanData($data);
				$list = array('InoculationPlanList'=>$isMobileTemplate.'eew_inoculationplan_list_item');
				$template = $isMobileTemplate.'eew_inoculationplan_list';
				$parent_key = 'Person_id';
				$parent_value = $data['Person_id'];
				$object_key = 'datePlan_Sort';
			break;

			case 'FreeDocument': //Список и содержимое документов посещения поликлиники
				$this->load->model('EvnXmlBase_model');
				$object_data = $this->EvnXmlBase_model->loadListViewData(array(
					'session' => $data['session'],
					'Evn_id' => !empty($data['FreeDocument_pid'])?$data['FreeDocument_pid']:null,
					'EvnXML_id' => !empty($data['FreeDocument_id'])?$data['FreeDocument_id']:null,
					'XmlType_id' => swEvnXml::MULTIPLE_DOCUMENT_TYPE_ID,
					'Frame' => ($data['view_section'] == 'main'),
				));
				$list = array('FreeDocumentList'=>'eew_free_document');
				$object_key = 'EvnXml_id';
				$template = 'eew_free_document_item';
				$xml_data = true;
				break;

			case 'EvnXmlRecord': //Список и содержимое документов движения в стационаре, Дневниковые записи
				$this->load->model('EvnXmlBase_model');
				$object_data = $this->EvnXmlBase_model->loadListViewData(array(
					'session' => $data['session'],
					'Evn_id' => $data['EvnXmlRecord_pid'],
					'XmlType_id' => swEvnXml::STAC_RECORD_TYPE_ID,
				));
				$list = array('EvnXmlRecordList'=>'view_data/EvnXmlRecord_list');
				$object_key = 'EvnXml_id';
				$template = 'view_data/EvnXmlRecord_item';
				$xml_data = true;
				break;

			case 'EvnXmlProtokol': //Список и содержимое документов движения в стационаре, Осмотры
				$this->load->model('EvnXmlBase_model');
				$object_data = $this->EvnXmlBase_model->loadListViewData(array(
					'session' => $data['session'],
					'Evn_id' => $data['EvnXmlProtokol_pid'],
					'XmlType_id' => swEvnXml::STAC_PROTOCOL_TYPE_ID,
				));
				$list = array('EvnXmlProtokolList'=>'view_data/EvnXmlProtokol_list');
				$object_key = 'EvnXml_id';
				$template = 'view_data/EvnXmlProtokol_item';
				$xml_data = true;
				break;

			case 'EvnXmlEpikriz': //Список и содержимое документов движения в стационаре, Эпикризы
				$this->load->model('EvnXmlBase_model');
				$object_data = $this->EvnXmlBase_model->loadListViewData(array(
					'session' => $data['session'],
					'Evn_id' => $data['EvnXmlEpikriz_pid'],
					'XmlType_id' => swEvnXml::STAC_EPIKRIZ_TYPE_ID,
				));
				$list = array('EvnXmlEpikrizList'=>'view_data/EvnXmlEpikriz_list');
				$object_key = 'EvnXml_id';
				$template = 'view_data/EvnXmlEpikriz_item';
				$xml_data = true;
				break;

			case 'EvnPLDispScreenOnko': //Список и содержимое документов посещения поликлиники
				$this->load->model('EvnPLDispScreenOnko_model');
				$object_data = $this->EvnPLDispScreenOnko_model->loadEvnPLDispScreenOnko($data);
				$list = ['EvnPLDispScreenOnkoList' => 'view_data/EvnPLDispScreenOnko_list_item'];
				$object_key = 'EvnPLDispScreenOnko_id';
				$template = 'view_data/EvnPLDispScreenOnko_list';
				break;

			case 'EvnXmlOther': //Список и содержимое документов движения в стационаре, Прочие документы
				$this->load->model('EvnXmlBase_model');
				$object_data = $this->EvnXmlBase_model->loadListViewData(array(
					'session' => $data['session'],
					'Evn_id' => $data['EvnXmlOther_pid'],
					'XmlType_id' => swEvnXml::MULTIPLE_DOCUMENT_TYPE_ID,
				));
				$list = array('EvnXmlOtherList'=>'view_data/EvnXmlOther_list');
				$object_key = 'EvnXml_id';
				$template = 'view_data/EvnXmlOther_item';
				$xml_data = true;
				break;

			case 'MorbusOnkoVizitPLDop':
				$this->load->model('MorbusOnkoVizitPLDop_model');
				$object_data = $this->MorbusOnkoVizitPLDop_model->loadViewData($data);
				$object_key = 'MorbusOnkoVizitPLDop_id';
				$template = 'eew_morbusonkovizitpldop';
				break;
			case 'MorbusOnkoLeave':
				$this->load->model('MorbusOnkoLeave_model');
				$object_data = $this->MorbusOnkoLeave_model->loadViewData($data);
				$object_key = 'MorbusOnkoLeave_id';
				if($this->MorbusOnkoLeave_model->regionNick == 'kz'){
					$template = 'eew_MorbusOnkoLeave_kz';
				} else {
					$template = 'eew_MorbusOnkoLeave';
				}
				break;

			//Специфика по гепатитам
			case 'PersonMorbusHepatitis':
				$this->load->model('Common_model', 'Common_model');
				$this->load->model('Morbus_model', 'Morbus_model');
				$this->load->model('PMMediaData_model', 'PMMediaData_model');
				if($data['object_id'] == 'MorbusHepatitis_pid') {
					$this->Morbus_model->Evn_pid = $data['object_value'];
					$data['Person_id'] = $this->Morbus_model->getPersonId();
				}
				$object_data = $this->Common_model->loadPersonData($data, 'EmkPanel');
				$object_data[0]['PersonPhotoThumbName'] = $this->PMMediaData_model->getPersonPhotoThumbName($object_data[0]);
				$object_data[0]['ParentSection'] = 'PersonMorbusHepatitis';
				$object_key = 'Person_id';
				if($data['object_id'] == 'MorbusHepatitis_pid') {
					$object_data[0]['MorbusHepatitis_pid'] = $data['object_value'];
					$object_key = 'MorbusHepatitis_pid';
				}
				$subsection = array(array('code'=>'person_data','template'=>'eew_person_data'));
				$children_list = array('MorbusHepatitis');
				$template = 'eew_personmorbushepatitis';
			break;

			case 'MorbusHepatitis':
				$this->load->model('MorbusHepatitis_model', 'MorbusHepatitis_model');
				$object_data = $this->MorbusHepatitis_model->getMorbusHepatitisViewData($data);
				$object_key = 'MorbusHepatitis_id';
				$first_key = 'MorbusHepatitis_pid';
				$children_list = array('MorbusHepatitisDiag', /*'MorbusHepatitisLabConfirm','MorbusHepatitisFuncConfirm',*/ 'MorbusHepatitisCure','MorbusHepatitisVaccination','MorbusHepatitisQueue','MorbusHepatitisSopDiag','MorbusHepatitisEvn','MorbusHepatitisPlan');
				$template = 'eew_hepatitis';
			break;

			// все посещения/движения пациента по заболеванию «Вирусный гепатит» (с указанным в учетном документе диагнозом из группы В15.0 – В19.9)
			case 'MorbusHepatitisEvn':
				$this->load->model('MorbusHepatitis_model', 'MorbusHepatitis_model');
				$object_data = $this->MorbusHepatitis_model->getMorbusHepatitisEvnViewData(array('Evn_id'=>$data['MorbusHepatitis_pid'], 'Person_id' => !empty($data['Person_id']) ? $data['Person_id'] : null));
				$object_key = 'Evn_id';
				$first_key = 'MorbusHepatitis_pid';
				$parent_object = 'MorbusHepatitis';
				$template = 'eew_morbushepatitisevn';
				$list = array('MorbusHepatitisEvnList'=>'eew_morbushepatitisevn_item');
			break;

			// сопутствующие диагнозы, указанные во всех посещения/движения пациента по заболеванию «Вирусный гепатит» (с указанным в учетном документе диагнозом из группы В15.0 – В19.9)
			case 'MorbusHepatitisSopDiag':
				$this->load->model('MorbusHepatitis_model', 'MorbusHepatitis_model');
				$object_data = $this->MorbusHepatitis_model->getMorbusHepatitisSopDiagViewData(array('Evn_id'=>$data['MorbusHepatitis_pid']));
				$object_key = 'MorbusHepatitisSopDiag_id';
				$first_key = 'MorbusHepatitis_pid';
				$parent_object = 'MorbusHepatitis';
				$template = 'eew_morbushepatitissopdiag';
				$list = array('MorbusHepatitisSopDiagList'=>'eew_morbushepatitissopdiag_item');
			break;

			case 'MorbusHepatitisDiag':
				$this->load->model('MorbusHepatitis_model', 'MorbusHepatitis_model');
				$object_data = $this->MorbusHepatitis_model->getMorbusHepatitisDiagViewData(array('MorbusHepatitis_id'=>$data['MorbusHepatitisDiag_pid'],'Evn_id'=>$data['MorbusHepatitis_pid']));
				$object_key = 'MorbusHepatitisDiag_id';
				$first_key = 'MorbusHepatitis_pid';
				$parent_object = 'MorbusHepatitis';
				$template = 'eew_morbushepatitisdiag';
				$list = array('MorbusHepatitisDiagList'=>'eew_morbushepatitisdiag_item');
			break;

			case 'MorbusHepatitisLabConfirm':
				$this->load->model('MorbusHepatitis_model', 'MorbusHepatitis_model');
				$object_data = $this->MorbusHepatitis_model->getMorbusHepatitisLabConfirmViewData(array('MorbusHepatitis_id'=>$data['MorbusHepatitisLabConfirm_pid'],'Evn_id'=>$data['MorbusHepatitis_pid']));
				$object_key = 'MorbusHepatitisLabConfirm_id';
				$first_key = 'MorbusHepatitis_pid';
				$parent_object = 'MorbusHepatitis';
				$template = 'eew_morbushepatitislabconfirm';
				$list = array('MorbusHepatitisLabConfirmList'=>'eew_morbushepatitislabconfirm_item');
			break;

			case 'MorbusHepatitisFuncConfirm':
				$this->load->model('MorbusHepatitis_model', 'MorbusHepatitis_model');
				$object_data = $this->MorbusHepatitis_model->getMorbusHepatitisFuncConfirmViewData(array('MorbusHepatitis_id'=>$data['MorbusHepatitisFuncConfirm_pid'],'Evn_id'=>$data['MorbusHepatitis_pid']));
				$object_key = 'MorbusHepatitisFuncConfirm_id';
				$first_key = 'MorbusHepatitis_pid';
				$parent_object = 'MorbusHepatitis';
				$template = 'eew_morbushepatitisfuncconfirm';
				$list = array('MorbusHepatitisFuncConfirmList'=>'eew_morbushepatitisfuncconfirm_item');
			break;

			case 'MorbusHepatitisCure':
				$this->load->model('MorbusHepatitis_model', 'MorbusHepatitis_model');
				$object_data = $this->MorbusHepatitis_model->getMorbusHepatitisCureViewData(array('MorbusHepatitis_id'=>$data['MorbusHepatitisCure_pid'],'Evn_id'=>$data['MorbusHepatitis_pid']));
				$object_key = 'MorbusHepatitisCure_id';
				$first_key = 'MorbusHepatitis_pid';
				$parent_object = 'MorbusHepatitis';
				$template = 'eew_morbushepatitiscure';
				$list = array('MorbusHepatitisCureList'=>'eew_morbushepatitiscure_item');
			break;

			case 'MorbusHepatitisVaccination':
				//print_r($data['']);
				$this->load->model('MorbusHepatitis_model', 'MorbusHepatitis_model');
				$object_data = $this->MorbusHepatitis_model->getMorbusHepatitisVaccinationViewData(array('MorbusHepatitis_id'=>$data['MorbusHepatitisVaccination_pid'],'Evn_id'=>$data['MorbusHepatitis_pid']));
				$object_key = 'MorbusHepatitisVaccination_id';
				$first_key = 'MorbusHepatitis_pid';
				$parent_object = 'MorbusHepatitis';
				$template = 'eew_morbushepatitisvaccination';
				$list = array('MorbusHepatitisVaccinationList'=>'eew_morbushepatitisvaccination_item');
			break;

			case 'MorbusHepatitisQueue':
				//print_r($data['']);
				$this->load->model('MorbusHepatitis_model', 'MorbusHepatitis_model');
				$object_data = $this->MorbusHepatitis_model->getMorbusHepatitisQueueViewData(array('MorbusHepatitis_id'=>$data['MorbusHepatitisQueue_pid'],'Evn_id'=>$data['MorbusHepatitis_pid']));
				$object_key = 'MorbusHepatitisQueue_id';
				$first_key = 'MorbusHepatitis_pid';
				$parent_object = 'MorbusHepatitis';
				$template = 'eew_morbushepatitisqueue';
				$list = array('MorbusHepatitisQueueList'=>'eew_morbushepatitisqueue_item');
			break;

			case 'MorbusHepatitisPlan':
				//print_r($data['']);
				$this->load->model('MorbusHepatitis_model', 'MorbusHepatitis_model');
				$object_data = $this->MorbusHepatitis_model->getMorbusHepatitisPlanViewData(array('MorbusHepatitis_id'=>$data['MorbusHepatitisPlan_pid'],'Evn_id'=>$data['MorbusHepatitis_pid']));
				$object_key = 'MorbusHepatitisPlan_id';
				$first_key = 'MorbusHepatitis_pid';
				$parent_object = 'MorbusHepatitis';
				$template = 'eew_morbushepatitisplan';
				$list = array('MorbusHepatitisPlanList'=>'eew_morbushepatitisplan_item');
			break;

			//Специфика по onko
			case 'PersonMorbusOnko':
				$this->load->model('Common_model', 'Common_model');
				$this->load->model('Morbus_model', 'Morbus_model');
				$this->load->model('PMMediaData_model', 'PMMediaData_model');
				if($data['object_id'] == 'MorbusOnko_pid') {
					$this->Morbus_model->Evn_pid = $data['object_value'];
					$data['Person_id'] = $this->Morbus_model->getPersonId();
				}
				$object_data = $this->Common_model->loadPersonData($data, 'EmkPanel');
				$object_data[0]['PersonPhotoThumbName'] = $this->PMMediaData_model->getPersonPhotoThumbName($object_data[0]);
				$object_data[0]['ParentSection'] = 'PersonMorbusOnko';
				$object_key = 'Person_id';
				if($data['object_id'] == 'MorbusOnko_pid') {
					$data['Evn_pid'] = $this->Morbus_model->Evn_pid;
				}
				$subsection = array(array('code'=>'person_data','template'=>'eew_person_data'));
				$children_list = array('MorbusOnko');
				$template = 'eew_personmorbusonko';
			break;

			case 'MorbusOnko':
				if($data['parent_object_id'] == 'MorbusOnko_pid' && !empty($data['Evn_pid'])) {
					$data['MorbusOnko_pid'] = $data['Evn_pid'];
				}
				$this->load->model('MorbusOnkoSpecifics_model', 'MorbusOnkoSpecifics');
				$object_data = $this->MorbusOnkoSpecifics->getViewData($data);
				//print_r($object_data); exit;
				$object_key = 'Morbus_id';
				$first_key = 'MorbusOnko_pid';
				$data['MorbusOnkoLeave_id'] = $object_data[0]['MorbusOnkoLeave_id']; // всё лучшее - детям
				$data['MorbusOnkoVizitPLDop_id'] = $object_data[0]['MorbusOnkoVizitPLDop_id'];
				$data['MorbusOnkoDiagPLStom_id'] = $object_data[0]['MorbusOnkoDiagPLStom_id'];
				$data['EvnDiagPLSop_id'] = $object_data[0]['EvnDiagPLSop_id'];
				$children_list = array('MorbusOnkoBasePS','MorbusOnkoBasePersonState','MorbusOnkoEvnNotify','MorbusOnkoDrug','MorbusOnkoSpecTreat','MorbusOnkoRefusal','MorbusOnkoHirTer','MorbusOnkoRadTer','MorbusOnkoChemTer','MorbusOnkoGormTer','MorbusOnkoLink');
				if($this->MorbusOnkoSpecifics->getRegionNick() == 'kz'){
					$template = 'eew_morbusonko_kz';
					array_push($children_list, 'MorbusOnkoSopDiag');
				} else {
					$template = 'eew_morbusonko';
					array_push($children_list, 'MorbusOnkoNonSpecTer');
				}
				if(!empty($data['PersonRegister_id'])){
					array_push($children_list, 'MorbusOnkoPersonDisp');
				}
				if(!$this->MorbusOnkoSpecifics->getRegionNick() != 'kz'){
					array_push($children_list, 'OnkoConsult');
					array_push($children_list, 'DrugTherapyScheme');
				}
			break;

			case 'MorbusOnkoPersonDisp':
				$this->load->library('swFilterResponse');
				$this->load->model('Polka_PersonDisp_model', 'PersonDisp_model');
				$object_data = $this->PersonDisp_model->getMorbusOnkoPersonDispViewData($data);
				$data['haveCommonARM'] = (in_array('common', $data['session']['ARMList'])?1:null);
				$extra['can_add'] = true;
				foreach ($object_data as $item) {
					if (empty($item['PersonDisp_endDate']) && $item['Lpu_id'] == $data['Lpu_id']) {
						$extra['can_add'] = false;
					}
				}
				$object_key = 'MorbusOnkoPersonDisp_id';
				$first_key = 'MorbusOnko_pid';
				$parent_object = 'MorbusOnko';
				$list = array('MorbusOnkoPersonDispList' => 'eew_MorbusOnkoPersonDisp_item');
				$template = 'eew_MorbusOnkoPersonDisp';
				$extra['haveCommonARM'] = $data['haveCommonARM'];
			break;

			case 'MorbusPregnancy':
				$this->load->model('MorbusPregnancy_model', 'MorbusPregnancy');
				$object_data = $this->MorbusPregnancy->getViewData($data);
				//print_r($object_data); exit;
				$object_key = 'MorbusPregnancy_id';
				$first_key = 'MorbusPregnancy_pid';
				$template = 'eew_morbuspregnancy';
			break;

			//Общее состояние пациента
			case 'MorbusOnkoBasePersonState':
				$this->load->model('MorbusOnkoBasePersonState_model', 'MorbusOnkoBasePersonState');
				$object_data = $this->MorbusOnkoBasePersonState->getViewData(array('Morbus_id'=>$data['MorbusOnkoBasePersonState_pid'], 'Evn_id'=>$data['MorbusOnko_pid']));
				$object_key = 'MorbusOnkoBasePersonState_id';
				$first_key = 'MorbusOnko_pid';
				$parent_object = 'MorbusOnko';
				$template = 'eew_MorbusOnkoBasePersonState';
				$list = array('MorbusOnkoBasePersonStateList'=>'eew_MorbusOnkoBasePersonState_item');
			break;

			//Госпитализация
			case 'MorbusOnkoBasePS':
				$this->load->model('MorbusOnkoBasePS_model', 'MorbusOnkoBasePS');
				$object_data = $this->MorbusOnkoBasePS->getViewData(array('Morbus_id'=>$data['MorbusOnkoBasePS_pid'], 'Evn_id'=>$data['MorbusOnko_pid']));
				$object_key = 'MorbusOnkoBasePS_id';
				$first_key = 'MorbusOnko_pid';
				$parent_object = 'MorbusOnko';
				$template = 'eew_MorbusOnkoBasePS';
				$list = array('MorbusOnkoBasePSList'=>'eew_MorbusOnkoBasePS_item');
			break;

			//Сопутствующие заболевания
			case 'MorbusOnkoSopDiag':
				$this->load->model('MorbusOnkoSopDiag_model', 'MorbusOnkoSopDiag');
				$object_data = $this->MorbusOnkoSopDiag->getViewData(array('Morbus_id'=>$data['MorbusOnkoSopDiag_pid'], 'Evn_id'=>$data['MorbusOnko_pid']));
				$object_key = 'MorbusOnkoBaseDiagLink_id';
				$first_key = 'MorbusOnko_pid';
				$parent_object = 'MorbusOnko';
				$template = 'eew_morbusonkosopdiag_kz';
				$list = array('MorbusOnkoSopDiagList'=>'eew_morbusonkosopdiag_item_kz');
			break;

			// Извещения по онкологии
			case 'MorbusOnkoEvnNotify':
				$this->load->model('EvnOnkoNotify_model');
				$object_data = $this->EvnOnkoNotify_model->getDataForSpecific(array(
					'session' => $data['session'],
					'Morbus_id'=>$data['MorbusOnkoEvnNotify_pid'],
					'Evn_id'=>$data['MorbusOnko_pid']
				));
				$object_key = 'MorbusOnkoEvnNotify_id';
				$first_key = 'MorbusOnko_pid';
				$parent_object = 'MorbusOnko';
				if(!empty($data['PersonRegister_id'])){
					$template = 'eew_MorbusOnkoEvnNotifyPersonRegister';
				} else {
					$template = 'eew_MorbusOnkoEvnNotify';
				}
				$list = array('MorbusOnkoEvnNotifyList'=>'eew_MorbusOnkoEvnNotify_item');
			break;

			// Консилиумы
			case 'OnkoConsult':
				$this->load->model('OnkoConsult_model', 'OnkoConsult');
				$params = array();
				$params['session'] = $data['session'];
				$params['Morbus_id'] = $data['OnkoConsult_pid'];
				$params['MorbusOnkoVizitPLDop_id'] = $data['MorbusOnkoVizitPLDop_id'];
				$params['MorbusOnkoLeave_id'] = $data['MorbusOnkoLeave_id'];
				$params['MorbusOnkoDiagPLStom_id'] = $data['MorbusOnkoDiagPLStom_id'];
				$params['Evn_id'] = $data['MorbusOnko_pid'];
				$object_data = $this->OnkoConsult->loadList($params);
				$object_key = 'OnkoConsult_id';
				$first_key = 'MorbusOnko_pid';
				$parent_object = 'MorbusOnko';
				$template = 'eew_onkoconsult';
				$list = array('OnkoConsultList'=>'eew_onkoconsult_item');
			break;

			// Схема лекарственной терапии
			case 'DrugTherapyScheme':
				$this->load->model('EvnSection_model', 'EvnSection');
				$params = array();
				$params['EvnSection_id'] = $data['MorbusOnko_pid'];
				$params['isForEMK'] = true;
				$object_data = $this->EvnSection->loadDrugTherapySchemeList($params);
				$object_key = 'DrugTherapyScheme_id';
				$first_key = 'MorbusOnko_pid';
				$parent_object = 'MorbusOnko';
				$template = 'eew_drugtherapyscheme';
				$list = array('DrugTherapySchemeList'=>'eew_drugtherapyscheme_item');
			break;

			// Данные о препаратах
			case 'MorbusOnkoDrug':
				$this->load->model('MorbusOnkoDrug_model');
				$params = array();
				$params['Morbus_id'] = $data['MorbusOnkoDrug_pid'];
				$params['MorbusOnko_pid'] = $data['MorbusOnko_pid'];
				$params['MorbusOnkoDiagPLStom_id'] = (!empty($data['MorbusOnkoDiagPLStom_id']) ? $data['MorbusOnkoDiagPLStom_id'] : null);
				$params['MorbusOnkoLeave_id'] = (!empty($data['MorbusOnkoLeave_id']) ? $data['MorbusOnkoLeave_id'] : null);
				$params['MorbusOnkoVizitPLDop_id'] = (!empty($data['MorbusOnkoVizitPLDop_id']) ? $data['MorbusOnkoVizitPLDop_id'] : null);
				$object_data = $this->MorbusOnkoDrug_model->getViewData($params);
				$object_key = 'MorbusOnkoDrug_id';
				$first_key = 'MorbusOnko_pid';
				$parent_object = 'MorbusOnko';
				$template = 'eew_morbusonkodrug';
				$list = array('MorbusOnkoDrugList' => 'eew_morbusonkodrug_item');
				break;

			// Специальное лечение
			case 'MorbusOnkoSpecTreat':
				$this->load->model('MorbusOnkoSpecTreat_model');
				$object_data = $this->MorbusOnkoSpecTreat_model->getViewData(array(
					'session' => $data['session'],
					'Morbus_id'=>$data['MorbusOnkoSpecTreat_pid'],
					'MorbusOnkoVizitPLDop_id'=>$data['MorbusOnkoVizitPLDop_id'],
					'MorbusOnkoLeave_id'=>$data['MorbusOnkoLeave_id'],
					'MorbusOnkoDiagPLStom_id'=>$data['MorbusOnkoDiagPLStom_id'],
					'accessType'=>$data['accessType'] ?? null,
					'Evn_id'=>$data['MorbusOnko_pid']
				));
				$object_key = 'MorbusOnkoSpecTreat_id';
				$first_key = 'MorbusOnko_pid';
				$parent_object = 'MorbusOnko';
				if($this->MorbusOnkoSpecTreat_model->getRegionNick() == 'kz'){
					$template = 'eew_MorbusOnkoSpecTreat_kz';
					$list = array('MorbusOnkoSpecTreatList'=>'eew_MorbusOnkoSpecTreat_item_kz');
				} else {
					$template = 'eew_MorbusOnkoSpecTreat';
					$list = array('MorbusOnkoSpecTreatList'=>'eew_MorbusOnkoSpecTreat_item');
				}
			break;

			// Результаты диагностики
			case 'MorbusOnkoLink':

				$this->load->model('MorbusOnkoSpecifics_model', 'MorbusOnkoSpecifics');
				$object_data = $this->MorbusOnkoSpecifics->getMorbusOnkoLinkViewData(array(
					'Morbus_id' => $data['MorbusOnkoLink_pid'] ?? null,
					'MorbusOnkoVizitPLDop_id'=>$data['MorbusOnkoVizitPLDop_id'],
					'MorbusOnkoLeave_id'=>$data['MorbusOnkoLeave_id'],
					'MorbusOnkoDiagPLStom_id'=>$data['MorbusOnkoDiagPLStom_id'],
					'Evn_id'=>$data['MorbusOnko_pid']
				));
				$object_key = 'MorbusOnkoLink_id';
				$first_key = 'MorbusOnko_pid';
				$parent_object = 'MorbusOnko';

				$template = 'eew_MorbusOnkoLink';
				$list = array('MorbusOnkoLinkList'=>'eew_MorbusOnkoLink_item');

				break;

			// Данные об отказах / противопоказаниях
			case 'MorbusOnkoRefusal':
				$this->load->model('MorbusOnkoRefusal_model');
				$object_data = $this->MorbusOnkoRefusal_model->getViewData(array(
					'session' => $data['session'],
					'Morbus_id' => $data['MorbusOnkoRefusal_pid'],
					'MorbusOnkoVizitPLDop_id' => $data['MorbusOnkoVizitPLDop_id'],
					'MorbusOnkoLeave_id' => $data['MorbusOnkoLeave_id'],
					'MorbusOnkoDiagPLStom_id' => $data['MorbusOnkoDiagPLStom_id'],
					'Evn_id' => $data['MorbusOnko_pid']
				));
				$object_key = 'MorbusOnkoRefusal_id';
				$first_key = 'MorbusOnko_pid';
				$parent_object = 'MorbusOnko';
				$template = 'eew_MorbusOnkoRefusal';
				$list = array('MorbusOnkoRefusalList' => 'eew_MorbusOnkoRefusal_item');
			break;

			// Лучевое лечение
			case 'MorbusOnkoRadTer':
				$this->load->model('EvnUsluga_model', 'EvnUsluga');
				$params = array();
				$params['pid'] = null;
				$params['Lpu_id'] = $data['Lpu_id'];
				$params['session'] = $data['session'];
				$params['class'] = 'EvnUslugaOnkoBeam';
				$params['byMorbus'] = 1;
				$params['Morbus_id'] = $data['MorbusOnkoRadTer_pid'];
				$params['EvnEdit_id'] = ($data['MorbusOnko_pid'] == $data['Person_id']) ? null : $data['MorbusOnko_pid'];
				$params['accessType'] = $data['accessType'] ?? null;
				$object_data = $this->EvnUsluga->loadEvnUslugaGrid($params);
				/*if(is_array($object_data)) {
					foreach($object_data as &$row) {
						$row['accessType'] = ($data['MorbusOnko_pid'] == $row['EvnUsluga_pid'] || (empty($row['EvnUsluga_pid']) && $data['MorbusOnko_pid'] == $row['Person_id']))?'edit':'view';
						$row['MorbusOnko_pid'] = $data['MorbusOnko_pid'];
						$row['Morbus_id'] = $data['MorbusOnkoRadTer_pid'];
					}
				}*/
				//print_r($object_data); exit;
				$object_key = 'EvnUsluga_id';
				$first_key = 'MorbusOnko_pid';
				$parent_object = 'MorbusOnko';
				if($this->EvnUsluga->getRegionNick() == 'kz'){
					$template = 'eew_morbusonkoradter_kz';
					$list = array('MorbusOnkoRadTerList'=>'eew_morbusonkoradter_item_kz');
				} else {
					$template = 'eew_morbusonkoradter';
					$list = array('MorbusOnkoRadTerList'=>'eew_morbusonkoradter_item');
				}
			break;

			// Хирургическое лечение
			case 'MorbusOnkoHirTer':
				$this->load->model('EvnUsluga_model', 'EvnUsluga');
				$params = array();
				$params['pid'] = null;
				$params['Lpu_id'] = $data['Lpu_id'];
				$params['session'] = $data['session'];
				$params['class'] = 'EvnUslugaOnkoSurg';
				$params['byMorbus'] = 1;
				$params['Morbus_id'] = $data['MorbusOnkoHirTer_pid'];
				$params['EvnEdit_id'] = ($data['MorbusOnko_pid'] == $data['Person_id']) ? null : $data['MorbusOnko_pid'];
				$params['accessType'] = $data['accessType'] ?? null;
				$object_data = $this->EvnUsluga->loadEvnUslugaGrid($params);
				/*if(is_array($object_data)) {
					foreach($object_data as &$row) {
						$row['accessType'] = ($data['MorbusOnko_pid'] == $row['EvnUsluga_pid'] || (empty($row['EvnUsluga_pid']) && $data['MorbusOnko_pid'] == $row['Person_id']))?'edit':'view';
						$row['MorbusOnko_pid'] = $data['MorbusOnko_pid'];
						$row['Morbus_id'] = $data['MorbusOnkoHirTer_pid'];
					}
				}*/
				//print_r($object_data); exit;
				$object_key = 'EvnUsluga_id';
				$first_key = 'MorbusOnko_pid';
				$parent_object = 'MorbusOnko';
				if($this->EvnUsluga->getRegionNick() == 'kz'){
					$template = 'eew_morbusonkohirter_kz';
					$list = array('MorbusOnkoHirTerList'=>'eew_morbusonkohirter_item_kz');
				} else {
					$template = 'eew_morbusonkohirter';
					$list = array('MorbusOnkoHirTerList'=>'eew_morbusonkohirter_item');
				}
			break;

			// Химиотерапевтическое лечение
			case 'MorbusOnkoChemTer':
				$this->load->model('EvnUsluga_model', 'EvnUsluga');
				$params = array();
				$params['pid'] = null;
				$params['Lpu_id'] = $data['Lpu_id'];
				$params['session'] = $data['session'];
				$params['class'] = 'EvnUslugaOnkoChem';//EvnClass_SysNick
				$params['byMorbus'] = 1;
				$params['Morbus_id'] = $data['MorbusOnkoChemTer_pid'];
				$params['EvnEdit_id'] = ($data['MorbusOnko_pid'] == $data['Person_id']) ? null : $data['MorbusOnko_pid'];
				$params['accessType'] = $data['accessType'] ?? null;
				$object_data = $this->EvnUsluga->loadEvnUslugaGrid($params);
				/*if(is_array($object_data)) {
					foreach($object_data as &$row) {
						$row['accessType'] = ($data['MorbusOnko_pid'] == $row['EvnUsluga_pid'] || (empty($row['EvnUsluga_pid']) && $data['MorbusOnko_pid'] == $row['Person_id']))?'edit':'view';
						$row['MorbusOnko_pid'] = $data['MorbusOnko_pid'];
						$row['Morbus_id'] = $data['MorbusOnkoChemTer_pid'];
					}
				}*/
				//print_r($object_data); exit;
				$object_key = 'EvnUsluga_id';
				$first_key = 'MorbusOnko_pid';
				$parent_object = 'MorbusOnko';
				if($this->EvnUsluga->getRegionNick() == 'kz'){
					$template = 'eew_morbusonkochemter_kz';
					$list = array('MorbusOnkoChemTerList'=>'eew_morbusonkochemter_item_kz');
				} else {
					$template = 'eew_morbusonkochemter';
					$list = array('MorbusOnkoChemTerList'=>'eew_morbusonkochemter_item');
				}
			break;

			//Гормоноиммунотерапевтическое лечение
			case 'MorbusOnkoGormTer':
				$this->load->model('EvnUsluga_model', 'EvnUsluga');
				$params = array();
				$params['pid'] = null;
				$params['Lpu_id'] = $data['Lpu_id'];
				$params['session'] = $data['session'];
				$params['class'] = 'EvnUslugaOnkoGormun';
				$params['byMorbus'] = 1;
				$params['Morbus_id'] = $data['MorbusOnkoGormTer_pid'];
				$params['EvnEdit_id'] = ($data['MorbusOnko_pid'] == $data['Person_id']) ? null : $data['MorbusOnko_pid'];
				$params['accessType'] = $data['accessType'] ?? null;
				$object_data = $this->EvnUsluga->loadEvnUslugaGrid($params);
				/*if(is_array($object_data)) {
					foreach($object_data as &$row) {
						$row['accessType'] = ($data['MorbusOnko_pid'] == $row['EvnUsluga_pid'] || (empty($row['EvnUsluga_pid']) && $data['MorbusOnko_pid'] == $row['Person_id']))?'edit':'view';
						$row['MorbusOnko_pid'] = $data['MorbusOnko_pid'];;
						$row['Morbus_id'] = $data['MorbusOnkoGormTer_pid'];
					}
				}*/
				//print_r($object_data); exit;
				$object_key = 'EvnUsluga_id';
				$first_key = 'MorbusOnko_pid';
				$parent_object = 'MorbusOnko';
				$template = 'eew_morbusonkogormter';
				$list = array('MorbusOnkoGormTerList'=>'eew_morbusonkogormter_item');
			break;

			//Неспецифическое лечение
			case 'MorbusOnkoNonSpecTer':
				$this->load->model('EvnUsluga_model', 'EvnUsluga');
				$params = array();
				$params['pid'] = null;
				$params['Lpu_id'] = $data['Lpu_id'];
				$params['session'] = $data['session'];
				$params['class'] = 'EvnUslugaOnkoNonSpec';
				$params['byMorbus'] = 1;
				$params['Morbus_id'] = $data['MorbusOnkoNonSpecTer_pid'];
				$params['EvnEdit_id'] = ($data['MorbusOnko_pid'] == $data['Person_id']) ? null : $data['MorbusOnko_pid'];
				$params['accessType'] = $data['accessType'] ?? null;
				$object_data = $this->EvnUsluga->loadEvnUslugaGrid($params);
				$object_key = 'EvnUsluga_id';
				$first_key = 'MorbusOnko_pid';
				$parent_object = 'MorbusOnko';
				$template = 'eew_morbusonkononspecter';
				$list = array('MorbusOnkoNonSpecTerList'=>'eew_morbusonkononspecter_item');
			break;

			//Специфика по орфанным заболеваниям 
			case 'PersonMorbusOrphan':
				$this->load->model('Common_model', 'Common_model');
				$this->load->model('PMMediaData_model', 'PMMediaData_model');
				$object_data = $this->Common_model->loadPersonData($data, 'EmkPanel');
				$object_data[0]['PersonPhotoThumbName'] = $this->PMMediaData_model->getPersonPhotoThumbName($object_data[0]);
				$object_data[0]['ParentSection'] = 'PersonMorbusOrphan';
				$object_key = 'Person_id';
				$subsection = array(array('code'=>'person_data','template'=>'eew_person_data'));
				$children_list = array('MorbusOrphan');
				$template = 'eew_personmorbusorphan';
				break;
			case 'MorbusOrphan':
				$this->load->model('PersonRegister_model', 'PersonRegister_model');
				$object_data = $this->PersonRegister_model->getPersonRegisterViewData($data);
				//print_r($object_data); exit;
				$object_key = 'PersonRegister_id';
				$first_key = 'From_id';
				$children_list = array('PersonRegisterExport', 'PersonPrivilege', 'PersonPrivilegeFed', 'DrugOrphan');
				$template = 'eew_morbusorphan';
			break;

			// Венеро
			case 'PersonMorbusVener':
				$this->load->model('Common_model', 'Common_model');
				$this->load->model('Morbus_model', 'Morbus_model');
				$this->load->model('PMMediaData_model', 'PMMediaData_model');
				if($data['object_id'] == 'MorbusVener_pid') {
					$this->Morbus_model->Evn_pid = $data['object_value'];
					$data['Person_id'] = $this->Morbus_model->getPersonId();
				}
				$object_data = $this->Common_model->loadPersonData($data, 'EmkPanel');
				$object_data[0]['PersonPhotoThumbName'] = $this->PMMediaData_model->getPersonPhotoThumbName($object_data[0]);
				$object_data[0]['ParentSection'] = 'PersonMorbusVener';
				$object_key = 'Person_id';
				if($data['object_id'] == 'MorbusVener_pid') {
					$object_data[0]['MorbusVener_pid'] = $data['object_value'];
					$object_key = 'MorbusVener_pid';
				}
				$subsection = array(array('code'=>'person_data','template'=>'eew_person_data'));
				$children_list = array('MorbusVener');
				$template = 'eew_personmorbusvener';
				break;
			case 'MorbusVener':
				$this->load->model('MorbusVener_model', 'MorbusVener_model');
				$object_data = $this->MorbusVener_model->getMorbusVenerViewData($data);

				$object_key = 'MorbusVener_id';
				$first_key = 'MorbusVener_pid';
				$children_list = array(
					'MorbusVenerContact','MorbusVenerTreatSyph','MorbusVenerAccurTreat','MorbusVenerEndTreat'
				);
				$template = 'eew_morbusvener';
				break;
			case 'MorbusVenerContact':
				$this->load->model('MorbusVener_model', 'MorbusVener_model');
				$object_data = $this->MorbusVener_model->getMorbusVenerContactViewData(array('MorbusVener_id'=>$data['MorbusVenerContact_pid'],'Evn_id'=>$data['MorbusVener_pid']));
				$object_key = 'MorbusVenerContact_id';
				$first_key = 'MorbusVener_pid';
				$parent_object = 'MorbusVener';
				$template = 'eew_MorbusVenerContact';
				$list = array('MorbusVenerContactList'=>'eew_MorbusVenerContact_item');
				break;
			case 'MorbusVenerTreatSyph':
				$this->load->model('MorbusVener_model', 'MorbusVener_model');
				$object_data = $this->MorbusVener_model->getMorbusVenerTreatSyphViewData(array('MorbusVener_id'=>$data['MorbusVenerTreatSyph_pid'],'Evn_id'=>$data['MorbusVener_pid']));
				$object_key = 'MorbusVenerTreatSyph_id';
				$first_key = 'MorbusVener_pid';
				$parent_object = 'MorbusVener';
				$template = 'eew_MorbusVenerTreatSyph';
				$list = array('MorbusVenerTreatSyphList'=>'eew_MorbusVenerTreatSyph_item');
				break;
			case 'MorbusVenerAccurTreat':
				$this->load->model('MorbusVener_model', 'MorbusVener_model');
				$object_data = $this->MorbusVener_model->getMorbusVenerAccurTreatViewData(array('MorbusVener_id'=>$data['MorbusVenerAccurTreat_pid'],'Evn_id'=>$data['MorbusVener_pid']));
				$object_key = 'MorbusVenerAccurTreat_id';
				$first_key = 'MorbusVener_pid';
				$parent_object = 'MorbusVener';
				$template = 'eew_MorbusVenerAccurTreat';
				$list = array('MorbusVenerAccurTreatList'=>'eew_MorbusVenerAccurTreat_item');
				break;
			case 'MorbusVenerEndTreat':
				$this->load->model('MorbusVener_model', 'MorbusVener_model');
				$object_data = $this->MorbusVener_model->getMorbusVenerEndTreatViewData(array('MorbusVener_id'=>$data['MorbusVenerEndTreat_pid'],'Evn_id'=>$data['MorbusVener_pid']));
				$object_key = 'MorbusVenerEndTreat_id';
				$first_key = 'MorbusVener_pid';
				$parent_object = 'MorbusVener';
				$template = 'eew_MorbusVenerEndTreat';
				$list = array('MorbusVenerEndTreatList'=>'eew_MorbusVenerEndTreat_item');
				break;

			// Туберкулез
			case 'PersonMorbusTub':
				$this->load->model('Common_model', 'Common_model');
				$this->load->model('Morbus_model', 'Morbus_model');
				$this->load->model('PMMediaData_model', 'PMMediaData_model');
				if($data['object_id'] == 'MorbusTub_pid') {
					$this->Morbus_model->Evn_pid = $data['object_value'];
					$data['Person_id'] = $this->Morbus_model->getPersonId();
				}
				$object_data = $this->Common_model->loadPersonData($data, 'EmkPanel');
				$object_data[0]['PersonPhotoThumbName'] = $this->PMMediaData_model->getPersonPhotoThumbName($object_data[0]);
				$object_data[0]['ParentSection'] = 'PersonMorbusTub';
				$object_key = 'Person_id';
				if($data['object_id'] == 'MorbusTub_pid') {
					$object_data[0]['MorbusTub_pid'] = $data['object_value'];
					$object_key = 'MorbusTub_pid';
				}
				$subsection = array(array('code'=>'person_data','template'=>'eew_person_data'));
				$children_list = array('MorbusTub');
				$template = 'eew_personmorbustub';
				break;
			case 'MorbusTub':
				$this->load->model('MorbusTub_model', 'MorbusTub_model');
				$object_data = $this->MorbusTub_model->getMorbusTubViewData($data);

				$object_key = 'MorbusTub_id';
				$first_key = 'MorbusTub_pid';
				$children_list = array(
					/*'MorbusTubDiagSop',*/'MorbusTubDiagGeneralForm', 'MorbusTubConditChem','MorbusTubStudyResult',
					'MorbusTubAdvice', 'MorbusTubPrescr', 'EvnDirectionTub'
				);
				if ($this->MorbusTub_model->isAllowMorbusTubMDR) {
					$children_list[] = 'MorbusTubMDRStudyResult';
					$children_list[] = 'MorbusTubMDRPrescr';
				}
				if (getRegionNick() == "kz") {
					$template = 'eew_morbustub_kz';
				} else {
					$template = 'eew_morbustub';
				}
				break;
			case 'MorbusTubAdvice':
				$this->load->model('MorbusTub_model', 'MorbusTub_model');
				$object_data = $this->MorbusTub_model->getMorbusTubAdviceViewData(array('MorbusTub_id'=>$data['MorbusTubAdvice_pid'],'Evn_id'=>$data['MorbusTub_pid']));
				$object_key = 'MorbusTubAdvice_id';
				$first_key = 'MorbusTub_pid';
				$parent_object = 'MorbusTub';
				$template = 'eew_MorbusTubAdvice';
				//$list = array('MorbusTubAdviceList'=>'eew_MorbusTubAdvice_item');
				$children_list = array('MorbusTubAdviceOper');
				break;
			case 'MorbusTubAdviceOper':
				$this->load->model('MorbusTub_model', 'MorbusTub_model');
				$object_data = $this->MorbusTub_model->getMorbusTubAdviceOperViewData(array('MorbusTubAdvice_id'=>$data['MorbusTubAdviceOper_pid'],'Evn_id'=>$data['MorbusTub_pid']));
				$object_key = 'MorbusTubAdviceOper_id';
				$first_key = 'MorbusTub_pid';
				$parent_object = 'MorbusTubAdvice';
				$template = 'eew_MorbusTubAdviceOper';
				$list = array('MorbusTubAdviceOperList'=>'eew_MorbusTubAdviceOper_item');
				break;
			case 'MorbusTubDiagSop':
				$this->load->model('MorbusTub_model', 'MorbusTub_model');
				$object_data = $this->MorbusTub_model->getMorbusTubDiagSopViewData(array('MorbusTub_id'=>$data['MorbusTubDiagSop_pid'],'Evn_id'=>$data['MorbusTub_pid']));
				$object_key = 'MorbusTubDiagSop_id';
				$first_key = 'MorbusTub_pid';
				$parent_object = 'MorbusTub';
				$template = 'eew_MorbusTubDiagSop';
				$list = array('MorbusTubDiagSopList'=>'eew_MorbusTubDiagSop_item');
				break;
			case 'MorbusTubDiagGeneralForm':
				$this->load->model('MorbusTub_model', 'MorbusTub_model');
				$object_data = $this->MorbusTub_model->getMorbusTubDiagGeneralFormViewData(array('MorbusTub_id'=>$data['MorbusTubDiagGeneralForm_pid'],'Evn_id'=>$data['MorbusTub_pid']));
				$object_key = 'TubDiagGeneralForm_id';
				$first_key = 'MorbusTub_pid';
				$parent_object = 'MorbusTub';
				$template = 'eew_MorbusTubDiagGeneralForm';
				$list = array('MorbusTubDiagGeneralFormList'=>'eew_MorbusTubDiagGeneralForm_item');
				break;
			case 'MorbusTubConditChem':
				$this->load->model('MorbusTub_model', 'MorbusTub_model');
				$object_data = $this->MorbusTub_model->getMorbusTubConditChemViewData(array('MorbusTub_id'=>$data['MorbusTubConditChem_pid'],'Evn_id'=>$data['MorbusTub_pid']));
				$object_key = 'MorbusTubConditChem_id';
				$first_key = 'MorbusTub_pid';
				$parent_object = 'MorbusTub';
				$template = 'eew_MorbusTubConditChem';
				$list = array('MorbusTubConditChemList'=>'eew_MorbusTubConditChem_item');
				break;
			case 'MorbusTubStudyResult':
				$this->load->model('MorbusTub_model', 'MorbusTub_model');
				$object_data = $this->MorbusTub_model->getMorbusTubStudyResultViewData(array('MorbusTub_id'=>$data['MorbusTubStudyResult_pid'],'Evn_id'=>$data['MorbusTub_pid']));
				$object_key = 'MorbusTubStudyResult_id';
				$first_key = 'MorbusTub_pid';
				$parent_object = 'MorbusTub';
				$template = 'eew_MorbusTubStudyResult';
				$list = array('MorbusTubStudyResultList'=>'eew_MorbusTubStudyResult_item');
				break;
			case 'EvnDirectionTub':
				$this->load->model('MorbusTub_model', 'MorbusTub_model');
				$object_data = $this->MorbusTub_model->getEvnDirectionTubViewData(array('MorbusTub_id'=>$data['EvnDirectionTub_pid'],'Evn_id'=>$data['MorbusTub_pid']));
				$object_key = 'EvnDirectionTub_id';
				$first_key = 'MorbusTub_pid';
				$parent_object = 'MorbusTub';
				$template = 'eew_EvnDirectionTub';
				$list = array('EvnDirectionTubList'=>'eew_EvnDirectionTub_item');
				break;
			case 'MorbusTubPrescr':
				$this->load->model('MorbusTub_model', 'MorbusTub_model');
				$object_data = $this->MorbusTub_model->getMorbusTubPrescrViewData(array('MorbusTub_id'=>$data['MorbusTubPrescr_pid'],'Evn_id'=>$data['MorbusTub_pid']));
				$object_key = 'MorbusTubPrescr_id';
				$first_key = 'MorbusTub_pid';
				$parent_object = 'MorbusTub';
				$template = 'eew_MorbusTubPrescr';
				$list = array('MorbusTubPrescrList'=>'eew_MorbusTubPrescr_item');
				break;
			case 'MorbusTubMDRStudyResult':
				$this->load->model('MorbusTub_model', 'MorbusTub_model');
				$object_data = $this->MorbusTub_model->getMorbusTubMDRStudyResultViewData(array('MorbusTub_id'=>$data['MorbusTubMDRStudyResult_pid'],'Evn_id'=>$data['MorbusTub_pid']));
				$object_key = 'MorbusTubMDRStudyResult_id';
				$first_key = 'MorbusTub_pid';
				$parent_object = 'MorbusTub';
				$template = 'eew_MorbusTubMDRStudyResult';
				$list = array('MorbusTubMDRStudyResultList'=>'eew_MorbusTubMDRStudyResult_item');
				break;
			case 'MorbusTubMDRPrescr':
				$this->load->model('MorbusTub_model', 'MorbusTub_model');
				$object_data = $this->MorbusTub_model->getMorbusTubMDRPrescrViewData(array('MorbusTub_id'=>$data['MorbusTubMDRPrescr_pid'],'Evn_id'=>$data['MorbusTub_pid']));
				$object_key = 'MorbusTubPrescr_id';
				$first_key = 'MorbusTub_pid';
				$parent_object = 'MorbusTub';
				$template = 'eew_MorbusTubMDRPrescr';
				$list = array('MorbusTubMDRPrescrList'=>'eew_MorbusTubMDRPrescr_item');
				break;

			//Специфика по ИБС
			case 'PersonMorbusIBS':
				$this->load->model('Common_model', 'Common_model');
				$this->load->model('PMMediaData_model', 'PMMediaData_model');
				$object_data = $this->Common_model->loadPersonData($data, 'EmkPanel');
				$object_data[0]['PersonPhotoThumbName'] = $this->PMMediaData_model->getPersonPhotoThumbName($object_data[0]);
				$object_data[0]['ParentSection'] = 'PersonMorbusIBS';
				$object_key = 'Person_id';
				$subsection = array(array('code'=>'person_data','template'=>'eew_person_data'));
				$children_list = array('MorbusIBS');
				$template = 'view_data/PersonMorbusIBS';
				break;
			case 'MorbusIBS':
				$this->load->model('MorbusIBS_model');
				$object_data = $this->MorbusIBS_model->getViewData($data);
				$object_key = 'MorbusIBS_id';
				$first_key = 'MorbusIBS_pid';
				$children_list = array();
				if ($data['MorbusIBS_pid'] != $data['Person_id']) {
					// это посещение в ЭМК
				} else {
					// это в регистре
				}
				$template = 'view_data/MorbusIBS';
				break;

			case 'PersonRegisterOrphan':
				$this->load->model('PersonRegisterOrphan_model');
				$data['PersonRegisterType_SysNick'] = 'orphan';
				$object_data = $this->PersonRegisterOrphan_model->getViewData(array(
					'PersonRegister_id'=>$data['PersonRegister_id'],
					'session'=>$data['session'],
				));
				$data['Person_id'] = $object_data[0]['Person_id'];
				$object_key = 'PersonRegister_id';
				$children_list = array('PersonRegisterExport', 'PersonPrivilegeInvAll', 'PersonPrivilegeFedAll', 'PersonDrug');
				$template = 'view_data/PersonRegisterOrphan';
				break;

			case 'PersonPrivilegeInvAll':
				$this->load->model('PersonRegisterOrphan_model');
				$object_data = $this->PersonRegisterOrphan_model->getPersonPrivilegeInvAllViewData(array(
					'Person_id'=>$data['Person_id'],
					'session'=>$data['session'],
				));
				$object_key = 'PersonPrivilege_id';
				$list = array('PersonPrivilegeInvAllList'=>'null');
				$template = 'view_data/PersonPrivilegeInvAll_list';
				$use_item_arr = true;
			break;

			case 'PersonRegisterNolos':
				$this->load->model('PersonRegisterBase_model');
				$data['PersonRegisterType_SysNick'] = 'nolos';
				$object_data = $this->PersonRegisterBase_model->getViewData(array(
					'PersonRegisterType_SysNick'=>$data['PersonRegisterType_SysNick'],
					'PersonRegister_id'=>$data['PersonRegister_id'],
					'session'=>$data['session'],
				));
				if (is_array($object_data) && !empty($object_data)) {
					$this->load->model('PMMediaData_model', 'PMMediaData_model');
					$object_data[0]['PersonPhotoThumbName'] = $this->PMMediaData_model->getPersonPhotoThumbName($object_data[0]);
					if (empty($object_data[0]['Person_id'])) {
						throw new Exception('Не определен идентификатор человека');
					}
					$data['Person_id'] = $object_data[0]['Person_id'];
				}
				$object_key = 'PersonRegister_id';
				$children_list = array('PersonRegisterExport', 'PersonPrivilegeRegAll', 'PersonPrivilegeFedAll', 'PersonDrug');
				$template = 'view_data/PersonRegisterNolos';
				break;

			case 'PersonRegisterFmba':
				$this->load->model('PersonRegisterBase_model');
				$data['PersonRegisterType_SysNick'] = 'fmba';
				$object_data = $this->PersonRegisterBase_model->getViewData(array(
					'PersonRegisterType_SysNick'=>$data['PersonRegisterType_SysNick'],
					'PersonRegister_id'=>$data['PersonRegister_id'],
					'session'=>$data['session'],
				));
				if (is_array($object_data) && !empty($object_data)) {
					$this->load->model('PMMediaData_model', 'PMMediaData_model');
					$object_data[0]['PersonPhotoThumbName'] = $this->PMMediaData_model->getPersonPhotoThumbName($object_data[0]);
					if (empty($object_data[0]['Person_id'])) {
						throw new Exception('Не определен идентификатор человека');
					}
					$data['Person_id'] = $object_data[0]['Person_id'];
				}
				$object_key = 'PersonRegister_id';
				$children_list = array('PersonRegisterExport', 'PersonPrivilegeRegAll', 'PersonPrivilegeFedAll', 'PersonDrug');
				$template = 'view_data/PersonRegisterFmba';
				break;

			case 'PersonPrivilegeRegAll':
				$this->load->model('PersonRegisterBase_model');
				$object_data = $this->PersonRegisterBase_model->getPersonPrivilegeRegAllViewData(array(
					'Person_id'=>$data['Person_id'],
					'session'=>$data['session'],
				));
				$object_key = 'PersonPrivilege_id';
				$list = array('PersonPrivilegeRegAllList'=>'null');
				$template = 'view_data/PersonPrivilegeRegAll_list';
				$use_item_arr = true;
			break;

			case 'PersonPrivilegeFedAll':
				$this->load->model('PersonRegisterBase_model');
				$object_data = $this->PersonRegisterBase_model->getPersonPrivilegeFedAllViewData(array(
					'Person_id'=>$data['Person_id'],
					'session'=>$data['session'],
				));
				$object_key = 'PersonPrivilege_id';
				$list = array('PersonPrivilegeFedAllList'=>'null');
				$template = 'view_data/PersonPrivilegeFedAll_list';
				$use_item_arr = true;
			break;

			case 'PersonDrug':
				$this->load->model('PersonRegisterBase_model');
				$object_data = $this->PersonRegisterBase_model->getPersonDrugViewData(array(
					'PersonRegister_id'=>$data['PersonDrug_pid'],
					'PersonRegisterType_SysNick'=>$data['PersonRegisterType_SysNick'],
				));
				$object_key = 'PersonDrug_id';
				$template = 'view_data/PersonDrug_list';
				$list = array('PersonDrugList'=>'view_data/PersonDrug_item');
			break;

			//Специфика по нефро
			case 'PersonMorbusNephro':
				$this->load->model('Common_model', 'Common_model');
				$this->load->model('Morbus_model', 'Morbus_model');
				$this->load->model('PMMediaData_model', 'PMMediaData_model');
				if($data['object_id'] == 'MorbusNephro_pid') {
					$this->Morbus_model->Evn_pid = $data['object_value'];
					$data['Person_id'] = $this->Morbus_model->getPersonId();
				}
				$object_data = $this->Common_model->loadPersonData($data, 'EmkPanel');
				$object_data[0]['PersonPhotoThumbName'] = $this->PMMediaData_model->getPersonPhotoThumbName($object_data[0]);
				$object_data[0]['ParentSection'] = 'PersonMorbusNephro';
				$object_key = 'Person_id';
				if($data['object_id'] == 'MorbusNephro_pid') {
					$object_data[0]['MorbusNephro_pid'] = $data['object_value'];
					$object_key = 'MorbusNephro_pid';
				}
				$subsection = array(array('code'=>'person_data','template'=>'eew_person_data'));
				$children_list = array('MorbusNephro');
				$template = 'view_data/PersonMorbusNephro';
				break;
			case 'PersonMorbusProf':
				$this->load->model('Common_model', 'Common_model');
				$this->load->model('Morbus_model', 'Morbus_model');
				$this->load->model('PMMediaData_model', 'PMMediaData_model');
				if($data['object_id'] == 'MorbusProf_pid') {
					$this->Morbus_model->Evn_pid = $data['object_value'];
					$data['Person_id'] = $this->Morbus_model->getPersonId();
				}
				$object_data = $this->Common_model->loadPersonData($data, 'EmkPanel');
				$object_data[0]['PersonPhotoThumbName'] = $this->PMMediaData_model->getPersonPhotoThumbName($object_data[0]);
				$object_data[0]['ParentSection'] = 'PersonMorbusProf';
				$object_key = 'Person_id';
				if($data['object_id'] == 'MorbusProf_pid') {
					$object_data[0]['MorbusProf_pid'] = $data['object_value'];
					$object_key = 'MorbusProf_pid';
				}
				$subsection = array(array('code'=>'person_data','template'=>'eew_person_data'));
				//$children_list = array('MorbusProf');
				$template = 'view_data/PersonMorbusProf';
				break;
			case 'MorbusNephro':
				$this->load->model('MorbusNephro_model');
				$object_data = $this->MorbusNephro_model->getViewData($data);

				$object_key = 'MorbusNephro_id';
				$first_key = 'MorbusNephro_pid';
				$children_list = array();
				if ($data['MorbusNephro_pid'] != $data['Person_id']) {
					// это посещение в ЭМК
					$children_list[] = 'MorbusNephroLab';
				} else {
					// это в регистре
					$children_list[] = 'EvnDiagNephro';
					$children_list[] = 'MorbusNephroDisp';
					$children_list[] = 'MorbusNephroDialysis';
					if(getRegionNick() == 'ufa') { //#135648
						$children_list[] = 'NephroCommission';
						$children_list[] = 'MorbusNephroDrug';
						$children_list[] = 'NephroAccess';
						$children_list[] = 'NephroDocument';
						$children_list[] = 'NephroBloodCreatinine';
					}
				}
				$template = 'view_data/MorbusNephro';
				break;
			case 'MorbusProf':
				$this->load->model('MorbusProf_model');
				$object_data = $this->MorbusProf_model->getViewData($data);

				$object_key = 'MorbusProf_id';
				$first_key = 'MorbusProf_pid';
				$children_list = array();
				if ($data['MorbusProf_pid'] != $data['Person_id']) {
					// это посещение в ЭМК
				} else {
					// это в регистре
					$children_list[] = 'EvnDiagProf';
				}
				$template = 'view_data/MorbusProf';
				break;
			case 'MorbusNephroLab':
				$this->load->model('MorbusNephroLab_model');
				$object_data = $this->MorbusNephroLab_model->doLoadGrid(array(
					'session'=>$data['session'],
					'MorbusNephro_id'=>$data['MorbusNephroLab_pid'],
					'isOnlyLast'=>$data['isOnlyLast'],
					'Evn_id'=>$data['MorbusNephro_pid']
				));
				$object_key = 'MorbusNephroLab_id';
				$first_key = 'MorbusNephro_pid';
				$parent_object = 'MorbusNephro';
				$template = 'view_data/MorbusNephroLab_list';
				$list = array('MorbusNephroLabList'=>'view_data/MorbusNephroLab_item');
				break;
			case 'MorbusNephroDisp':
				$this->load->model('MorbusNephroDisp_model');
				$object_data = $this->MorbusNephroDisp_model->doLoadGrid(array(
					'session'=>$data['session'],
					'MorbusNephro_id'=>$data['MorbusNephroDisp_pid'],
					'isOnlyLast'=>$data['isOnlyLast'],
					'Evn_id'=>$data['MorbusNephro_pid']
				));
				$object_key = 'MorbusNephroDisp_id';
				$first_key = 'MorbusNephro_pid';
				$parent_object = 'MorbusNephro';
				$template = 'view_data/MorbusNephroDisp_list';
				$list = array('MorbusNephroDispList'=>'view_data/MorbusNephroDisp_item');
				break;
			case 'MorbusNephroDialysis':
				$this->load->model('MorbusNephroDialysis_model');
				$object_data = $this->MorbusNephroDialysis_model->doLoadGrid(array(
					'session'=>$data['session'],
					'MorbusNephro_id'=>$data['MorbusNephroDialysis_pid'],
					'isOnlyLast'=>$data['isOnlyLast'],
					'Evn_id'=>$data['MorbusNephro_pid']
				));
				$object_key = 'MorbusNephroDialysis_id';
				$first_key = 'MorbusNephro_pid';
				$parent_object = 'MorbusNephro';
				$template = 'view_data/MorbusNephroDialysis_list';
				$list = array('MorbusNephroDialysisList'=>'view_data/MorbusNephroDialysis_item');
				break;
			case 'MorbusNephroDrug':
				$this->load->model('MorbusNephroDrug_model');
				$object_data = $this->MorbusNephroDrug_model->doLoadGrid(array(
					'session'=>$data['session'],
					'MorbusNephro_id'=>$data['MorbusNephroDrug_pid'],
					'isOnlyLast'=>$data['isOnlyLast'],
					'Evn_id'=>$data['MorbusNephro_pid']
				));
				$object_key = 'MorbusNephroDrug_id';
				$first_key = 'MorbusNephro_pid';
				$parent_object = 'MorbusNephro';
				$template = 'view_data/MorbusNephroDrug_list';
				$list = array('MorbusNephroDrugList'=>'view_data/MorbusNephroDrug_item');
				break;
			case 'NephroAccess':
				$this->load->model('NephroAccess_model');
				$object_data = $this->NephroAccess_model->loadViewData(array(
					'session'=>$data['session'],
					'isOnlyLast'=>$data['isOnlyLast'],
					'MorbusNephro_id'=>$data['NephroAccess_pid'],
					'MorbusNephro_pid'=>$data['MorbusNephro_pid']
				));
				$object_key = 'NephroAccess_id';
				$first_key = 'MorbusNephro_pid';
				$parent_object = 'MorbusNephro';
				$template = 'view_data/NephroAccess_list';
				$list = array('NephroAccessList'=>'view_data/NephroAccess_item');
				break;
			case 'NephroCommission':
				$this->load->model('NephroCommission_model');
				$object_data = $this->NephroCommission_model->loadViewData(array(
					'session'=>$data['session'],
					'isOnlyLast'=>$data['isOnlyLast'],
					'MorbusNephro_id'=>$data['NephroCommission_pid'],
					'MorbusNephro_pid'=>$data['MorbusNephro_pid']
				));
				$object_key = 'NephroCommission_id';
				$first_key = 'MorbusNephro_pid';
				$parent_object = 'MorbusNephro';
				$template = 'view_data/NephroCommission_list';
				$list = array('NephroCommissionList'=>'view_data/NephroCommission_item');
				break;
			case 'NephroDocument':
				$this->load->model('NephroDocument_model');
				$object_data = $this->NephroDocument_model->loadViewData(array(
					'session'=>$data['session'],
					'isOnlyLast'=>$data['isOnlyLast'],
					'MorbusNephro_id'=>$data['NephroDocument_pid'],
					'MorbusNephro_pid'=>$data['MorbusNephro_pid']
				));
				$object_key = 'NephroDocument_id';
				$first_key = 'MorbusNephro_pid';
				$parent_object = 'MorbusNephro';
				$template = 'view_data/NephroDocument_list';
				$list = array('NephroDocumentList'=>'view_data/NephroDocument_item');
				break;
			case 'NephroBloodCreatinine':
				$this->load->model('MorbusNephroDisp_model');
				$object_data = $this->MorbusNephroDisp_model->getUslugaCreatineResult(array(
					'session'=>$data['session'],
					'isOnlyLast'=>$data['isOnlyLast'],
					'MorbusNephro_id'=>$data['NephroBloodCreatinine_pid'],
					'Evn_id'=>$data['MorbusNephro_pid']
				));
				$object_key = 'NephroBloodCreatinine_id';
				$first_key = 'MorbusNephro_pid';
				$parent_object = 'MorbusNephro';
				$template = 'view_data/NephroBloodCreatinine_list';
				$list = array('NephroBloodCreatinineList'=>'view_data/NephroBloodCreatinine_item');
				break;
			case 'EvnDiagNephro':
				$this->load->model('EvnDiagNephro_model');
				$object_data = $this->EvnDiagNephro_model->loadViewData(array(
					'session'=>$data['session'],
					'MorbusNephro_id'=>$data['EvnDiagNephro_pid'],
					'Evn_id'=>$data['MorbusNephro_pid']
				));
				$object_key = 'EvnDiagNephro_id';
				$first_key = 'MorbusNephro_pid';
				$parent_object = 'MorbusNephro';
				$template = 'view_data/EvnDiagNephro_list';
				$list = array('EvnDiagNephroList'=>'view_data/EvnDiagNephro_item');
				break;

			//Специфика по психиатрии
			case 'PersonMorbusCrazy':
				$this->load->model('Common_model', 'Common_model');
				$this->load->model('Morbus_model', 'Morbus_model');
				$this->load->model('PMMediaData_model', 'PMMediaData_model');
				if ($data['object_id'] == 'MorbusCrazy_pid') {
					$this->Morbus_model->Evn_pid = $data['object_value'];
					$data['Person_id'] = $this->Morbus_model->getPersonId();
				}
				$object_data = $this->Common_model->loadPersonData($data, 'EmkPanel');
				$object_data[0]['PersonPhotoThumbName'] = $this->PMMediaData_model->getPersonPhotoThumbName($object_data[0]);
				$object_data[0]['ParentSection'] = 'PersonMorbusCrazy';
				$object_key = 'Person_id';
				if ($data['object_id'] == 'MorbusCrazy_pid') {
					$object_data[0]['MorbusCrazy_pid'] = $data['object_value'];
					$object_key = 'MorbusCrazy_pid';
				}
				$subsection = array(array('code'=>'person_data','template'=>'eew_person_data'));
				$children_list = array('MorbusCrazy');
				$template = 'eew_personmorbuscrazy';
				break;
			case 'MorbusCrazy':
				$this->load->model('MorbusCrazy_model', 'MorbusCrazy_model');
				$object_data = $this->MorbusCrazy_model->getMorbusCrazyViewData($data);
				/*$object_key = 'MorbusHepatitis_id';
				$first_key = 'MorbusHepatitis_pid';
				$children_list = array('MorbusHepatitisDiag', 'MorbusHepatitisCure','MorbusHepatitisVaccination','MorbusHepatitisQueue','MorbusHepatitisSopDiag','MorbusHepatitisEvn');
				$template = 'eew_hepatitis';
				*/
				//print_r($object_data); exit;
				//$object_key = 'PersonRegister_id';
				//$first_key = 'From_id';
				if (count($object_data)) {
					$object_data[0]['isMseDepers'] = (!empty($data['from_MSE']) && $data['from_MSE'] == 2 && $this->globalOptions['globals']['use_depersonalized_expertise']);
				}
				$object_key = 'MorbusCrazy_id';
				$first_key = 'MorbusCrazy_pid';
				$children_list = array(
					'MorbusCrazyDiag','MorbusCrazyDynamicsObserv','MorbusCrazyVizitCheck','MorbusCrazyDynamicsState',
					'MorbusCrazyBasePS','MorbusCrazyForceTreat','MorbusCrazyPersonStick','MorbusCrazyPersonSuicidalAttempt',
					'MorbusCrazyPersonSocDangerAct','MorbusCrazyBaseDrugStart','MorbusCrazyDrug','MorbusCrazyPersonSurveyHIV',
					'MorbusCrazyPersonInvalid', 'MorbusCrazyNdOsvid', 'MorbusCrazyDrugVolume', 'MorbusCrazyBBK'
				);
				$template = 'eew_morbuscrazy';
				break;
			case 'MorbusCrazyDiag':
				$this->load->model('MorbusCrazy_model', 'MorbusCrazy_model');
				$object_data = $this->MorbusCrazy_model->getMorbusCrazyDiagViewData(array('MorbusCrazy_id'=>$data['MorbusCrazyDiag_pid'],'Evn_id'=>$data['MorbusCrazy_pid']));
				$object_key = 'MorbusCrazyDiag_id';
				$first_key = 'MorbusCrazy_pid';
				$parent_object = 'MorbusCrazy';
				$template = 'eew_morbuscrazydiag';
				$list = array('MorbusCrazyDiagList'=>'eew_morbuscrazydiag_item');
				break;
			case 'MorbusCrazyDynamicsObserv':
				$this->load->model('MorbusCrazy_model', 'MorbusCrazy_model');
				$object_data = $this->MorbusCrazy_model->getMorbusCrazyDynamicsObservViewData(array('MorbusCrazy_id'=>$data['MorbusCrazyDynamicsObserv_pid'],'Evn_id'=>$data['MorbusCrazy_pid']));
				$object_key = 'MorbusCrazyDynamicsObserv_id';
				$first_key = 'MorbusCrazy_pid';
				$parent_object = 'MorbusCrazy';
				$template = 'eew_morbuscrazydynamicsobserv';
				$list = array('MorbusCrazyDynamicsObservList'=>'eew_morbuscrazydynamicsobserv_item');
				break;
			case 'MorbusCrazyVizitCheck': // контроль посещений
				$this->load->model('MorbusCrazy_model', 'MorbusCrazy_model');
				$object_data = $this->MorbusCrazy_model->getMorbusCrazyVizitCheckViewData(array('MorbusCrazy_id'=>$data['MorbusCrazyVizitCheck_pid'],'Evn_id'=>$data['MorbusCrazy_pid']));
				$object_key = 'MorbusCrazyVizitCheck_id';
				$first_key = 'MorbusCrazy_pid';
				$parent_object = 'MorbusCrazy';
				$template = 'eew_morbuscrazyvizitcheck';
				$list = array('MorbusCrazyVizitCheckList'=>'eew_morbuscrazyvizitcheck_item');
			break;
			case 'MorbusCrazyDynamicsState': // Динамика состояния
				$this->load->model('MorbusCrazy_model', 'MorbusCrazy_model');
				//unset($data['session']);
				//print_r($data);
				$object_data = $this->MorbusCrazy_model->getMorbusCrazyDynamicsStateViewData(array('MorbusCrazy_id'=>$data['MorbusCrazyDynamicsState_pid'],'Evn_id'=>$data['MorbusCrazy_pid']));

				$object_key = 'MorbusCrazyDynamicsState_id';
				$first_key = 'MorbusCrazy_pid';
				$parent_object = 'MorbusCrazy';
				$template = 'eew_morbuscrazydynamicsstate';
				$list = array('MorbusCrazyDynamicsStateList'=>'eew_morbuscrazydynamicsstate_item');
				break;
			case 'MorbusCrazyBasePS': // Сведения о госпитализациях
				$this->load->model('MorbusCrazy_model', 'MorbusCrazy_model');
				$object_data = $this->MorbusCrazy_model->getMorbusCrazyBasePSViewData(array('MorbusCrazy_id'=>$data['MorbusCrazyBasePS_pid'],'Evn_id'=>$data['MorbusCrazy_pid']));
				$object_key = 'MorbusCrazyBasePS_id';
				$first_key = 'MorbusCrazy_pid';
				$parent_object = 'MorbusCrazy';
				$template = 'eew_morbuscrazybaseps';
				$list = array('MorbusCrazyBasePSList'=>'eew_morbuscrazybaseps_item');
				break;
			case 'MorbusCrazyForceTreat': // принудительное лечение
				$this->load->model('MorbusCrazy_model', 'MorbusCrazy_model');
				$object_data = $this->MorbusCrazy_model->getMorbusCrazyForceTreatViewData(array('MorbusCrazy_id'=>$data['MorbusCrazyForceTreat_pid'],'Evn_id'=>$data['MorbusCrazy_pid']));
				$object_key = 'MorbusCrazyForceTreat_id';
				$first_key = 'MorbusCrazy_pid';
				$parent_object = 'MorbusCrazy';
				$template = 'eew_morbuscrazyforcetreat';
				$list = array('MorbusCrazyForceTreatList'=>'eew_morbuscrazyforcetreat_item');
				break;
			// Недобровольное освидетельствование
			case 'MorbusCrazyNdOsvid': // Обследование на ВИЧ
				$this->load->model('MorbusCrazy_model', 'MorbusCrazy_model');
				$object_data = $this->MorbusCrazy_model->getMorbusCrazyNdOsvidViewData(array('MorbusCrazy_id'=>$data['MorbusCrazyNdOsvid_pid'],'Evn_id'=>$data['MorbusCrazy_pid']));
				$object_key = 'MorbusCrazyNdOsvid_id';
				$first_key = 'MorbusCrazy_pid';
				$parent_object = 'MorbusCrazy';
				$template = 'eew_morbuscrazyndosvid';
				$list = array('MorbusCrazyNdOsvidList'=>'eew_morbuscrazyndosvid_item');
				break;
			case 'MorbusCrazyPersonSurveyHIV': // Обследование на ВИЧ
				$this->load->model('MorbusCrazy_model', 'MorbusCrazy_model');
				$object_data = $this->MorbusCrazy_model->getMorbusCrazyPersonSurveyHIVViewData(array('MorbusCrazy_id'=>$data['MorbusCrazyPersonSurveyHIV_pid'],'Evn_id'=>$data['MorbusCrazy_pid']));
				$object_key = 'MorbusCrazyPersonSurveyHIV_id';
				$first_key = 'MorbusCrazy_pid';
				$parent_object = 'MorbusCrazy';
				$template = 'eew_morbuscrazypersonsurveyhiv';
				$list = array('MorbusCrazyPersonSurveyHIVList'=>'eew_morbuscrazypersonsurveyhiv_item');
				break;

			case 'MorbusCrazyPersonStick': // Временная нетрудоспособность
				$this->load->model('MorbusCrazy_model', 'MorbusCrazy_model');
				$object_data = $this->MorbusCrazy_model->getMorbusCrazyPersonStickViewData(array('MorbusCrazy_id'=>$data['MorbusCrazyPersonStick_pid'],'Evn_id'=>$data['MorbusCrazy_pid']));
				$object_key = 'MorbusCrazyPersonStick_id';
				$first_key = 'MorbusCrazy_pid';
				$parent_object = 'MorbusCrazy';
				$template = 'eew_morbuscrazypersonstick';
				$list = array('MorbusCrazyPersonStickList'=>'eew_morbuscrazypersonstick_item');
				break;

			case 'MorbusCrazyPersonSuicidalAttempt': // Суицидальные попытки
				$this->load->model('MorbusCrazy_model', 'MorbusCrazy_model');
				$object_data = $this->MorbusCrazy_model->getMorbusCrazyPersonSuicidalAttemptViewData(array('MorbusCrazy_id'=>$data['MorbusCrazyPersonSuicidalAttempt_pid'],'Evn_id'=>$data['MorbusCrazy_pid']));
				$object_key = 'MorbusCrazyPersonSuicidalAttempt_id';
				$first_key = 'MorbusCrazy_pid';
				$parent_object = 'MorbusCrazy';
				$template = 'eew_morbuscrazypersonsuicidalattempt';
				$list = array('MorbusCrazyPersonSuicidalAttemptList'=>'eew_morbuscrazypersonsuicidalattempt_item');
				break;

			case 'MorbusCrazyPersonSocDangerAct': // Общественно-опасные действия
				$this->load->model('MorbusCrazy_model', 'MorbusCrazy_model');
				$object_data = $this->MorbusCrazy_model->getMorbusCrazyPersonSocDangerActViewData(array('MorbusCrazy_id'=>$data['MorbusCrazyPersonSocDangerAct_pid'],'Evn_id'=>$data['MorbusCrazy_pid']));
				$object_key = 'MorbusCrazyPersonSocDangerAct_id';
				$first_key = 'MorbusCrazy_pid';
				$parent_object = 'MorbusCrazy';
				$template = 'eew_morbuscrazypersonsocdangeract';
				$list = array('MorbusCrazyPersonSocDangerActList'=>'eew_morbuscrazypersonsocdangeract_item');
				break;
			case 'MorbusCrazyBaseDrugStart': // Возраст начала употребления психоактивных веществ
				$this->load->model('MorbusCrazy_model', 'MorbusCrazy_model');
				$object_data = $this->MorbusCrazy_model->getMorbusCrazyBaseDrugStartViewData(array('MorbusCrazy_id'=>$data['MorbusCrazyBaseDrugStart_pid'],'Evn_id'=>$data['MorbusCrazy_pid']));
				$object_key = 'MorbusCrazyBaseDrugStart_id';
				$first_key = 'MorbusCrazy_pid';
				$parent_object = 'MorbusCrazy';
				$template = 'eew_morbuscrazybasedrugstart';
				$list = array('MorbusCrazyBaseDrugStartList'=>'eew_morbuscrazybasedrugstart_item');
				break;
			case 'MorbusCrazyDrug': // Употребление психоактивных веществ
				$this->load->model('MorbusCrazy_model', 'MorbusCrazy_model');
				$object_data = $this->MorbusCrazy_model->getMorbusCrazyDrugViewData(array('MorbusCrazy_id'=>$data['MorbusCrazyDrug_pid'],'Evn_id'=>$data['MorbusCrazy_pid']));
				$object_key = 'MorbusCrazyDrug_id';
				$first_key = 'MorbusCrazy_pid';
				$parent_object = 'MorbusCrazy';
				$template = 'eew_morbuscrazydrug';
				$list = array('MorbusCrazyDrugList'=>'eew_morbuscrazydrug_item');
				break;
			case 'MorbusCrazyDrugVolume': // Полученный объем наркологической помощи
				$this->load->model('MorbusCrazy_model', 'MorbusCrazy_model');
				$object_data = $this->MorbusCrazy_model->getMorbusCrazyDrugVolumeViewData(array('MorbusCrazy_id'=>$data['MorbusCrazyDrugVolume_pid'],'Evn_id'=>$data['MorbusCrazy_pid']));
				$object_key = 'MorbusCrazyDrugVolume_id';
				$first_key = 'MorbusCrazy_pid';
				$parent_object = 'MorbusCrazy';
				$template = 'eew_morbuscrazydrugvolume';
				$list = array('MorbusCrazyDrugVolumeList'=>'eew_morbuscrazydrugvolume_item');
				break;
			case 'MorbusCrazyBBK': // Военно-врачебная комиссия
				$this->load->model('MorbusCrazy_model', 'MorbusCrazy_model');
				$object_data = $this->MorbusCrazy_model->getMorbusCrazyBBKViewData(array('MorbusCrazy_id'=>$data['MorbusCrazyBBK_pid'],'Evn_id'=>$data['MorbusCrazy_pid']));
				$object_key = 'MorbusCrazyBBK_id';
				$first_key = 'MorbusCrazy_pid';
				$parent_object = 'MorbusCrazy';
				$template = 'eew_morbuscrazybbk';
				$list = array('MorbusCrazyBBKList'=>'eew_morbuscrazybbk_item');
				break;
			case 'MorbusCrazyPersonInvalid': // Инвалидность по психическому заболеванию
				$this->load->model('MorbusCrazy_model', 'MorbusCrazy_model');
				$object_data = $this->MorbusCrazy_model->getMorbusCrazyPersonInvalidViewData(array('MorbusCrazy_id'=>$data['MorbusCrazyPersonInvalid_pid'],'Evn_id'=>$data['MorbusCrazy_pid']));
				$object_key = 'MorbusCrazyPersonInvalid_id';
				$first_key = 'MorbusCrazy_pid';
				$parent_object = 'MorbusCrazy';
				$template = 'eew_morbuscrazypersoninvalid';
				$list = array('MorbusCrazyPersonInvalidList'=>'eew_morbuscrazypersoninvalid_item');
				break;
			case 'PersonRegisterExport':
				$this->load->model('PersonRegister_model', 'PersonRegister_model');
				$object_data = $this->PersonRegister_model->getPersonRegisterExportViewData(array('PersonRegister_id'=>$data['PersonRegisterExport_pid']));
				$object_key = 'PersonRegisterExport_id';
				$template = 'eew_personregisterexport';
				$list = array('PersonRegisterExportList'=>'eew_personregisterexport_item');
			break;

			case 'PersonPrivilege':
				$this->load->model('PersonRegister_model', 'PersonRegister_model');
				$object_data = $this->PersonRegister_model->getPersonPrivilegeViewData(array('Person_id'=>$data['Person_id']));
				$object_key = 'PersonPrivilege_id';
				$template = 'eew_personprivilege';
				$list = array('PersonPrivilegeList'=>'eew_personprivilege_item');
			break;

			case 'PersonPrivilegeFed':
				$this->load->model('PersonRegister_model', 'PersonRegister_model');
				$object_data = $this->PersonRegister_model->getPersonPrivilegeFedViewData(array('Person_id'=>$data['Person_id']));
				$object_key = 'PersonPrivilege_id';
				$template = 'eew_personprivilegefed';
				$list = array('PersonPrivilegeFedList'=>'eew_personprivilegefed_item');
			break;

			case 'DrugOrphan':
				$this->load->model('PersonRegister_model', 'PersonRegister_model');
				$object_data = $this->PersonRegister_model->getDrugOrphanViewData(array('PersonRegister_id'=>$data['DrugOrphan_pid']));
				$object_key = 'DrugOrphan_id';
				$template = 'eew_drugorphan';
				$list = array('DrugOrphanList'=>'eew_drugorphan_item');
			break;

			//Специфика по HIV
			case 'PersonMorbusHIV':
				$this->load->model('Common_model', 'Common_model');
				$this->load->model('PMMediaData_model', 'PMMediaData_model');
				$object_data = $this->Common_model->loadPersonData($data, 'EmkPanel');
				$object_data[0]['PersonPhotoThumbName'] = $this->PMMediaData_model->getPersonPhotoThumbName($object_data[0]);
				$object_data[0]['ParentSection'] = 'PersonMorbusHIV';
				$object_key = 'Person_id';
				$subsection = array(array('code'=>'person_data','template'=>'eew_person_data'));
				$children_list = array('MorbusHIV');
				$template = 'eew_personmorbushiv';
			break;

			case 'MorbusHIV':
				$this->load->model('MorbusHIV_model', 'MorbusHIV_model');
				$object_data = $this->MorbusHIV_model->getMorbusHIVViewData($data);
				//print_r($object_data); exit;
				$object_key = 'Morbus_id';
				$first_key = 'MorbusHIV_pid';
				$children_list = array('MorbusHIVChem','MorbusHIVVac','MorbusHIVSecDiag');
				$template = 'eew_morbushiv';
			break;

			//Раздел «Проведение химиопрофилактики ВИЧ-инфекции»
			case 'MorbusHIVChem':
				$this->load->model('MorbusHIV_model', 'MorbusHIV_model');
				$object_data = $this->MorbusHIV_model->getMorbusHIVChemViewData(array('Morbus_id'=>$data['MorbusHIVChem_pid'], 'Evn_id'=>$data['MorbusHIV_pid']));
				$object_key = 'MorbusHIVChem_id';
				$first_key = 'MorbusHIV_pid';
				$parent_object = 'MorbusHIV';
				$template = 'eew_morbushivchem';
				$list = array('MorbusHIVChemList'=>'eew_morbushivchem_item');
			break;

			//Раздел «Вакцинация»
			case 'MorbusHIVVac':
				$this->load->model('MorbusHIV_model', 'MorbusHIV_model');
				$object_data = $this->MorbusHIV_model->getMorbusHIVVacViewData(array('Morbus_id'=>$data['MorbusHIVVac_pid'], 'Evn_id'=>$data['MorbusHIV_pid']));
				$object_key = 'MorbusHIVVac_id';
				$first_key = 'MorbusHIV_pid';
				$parent_object = 'MorbusHIV';
				$template = 'eew_morbushivvac';
				$list = array('MorbusHIVVacList'=>'eew_morbushivvac_item');
			break;

			//Раздел «Вторичные заболевания и оппортунистические инфекции»
			case 'MorbusHIVSecDiag':
				$this->load->model('MorbusHIV_model', 'MorbusHIV_model');
				$object_data = $this->MorbusHIV_model->getMorbusHIVSecDiagViewData(array('Morbus_id'=>$data['MorbusHIVSecDiag_pid'], 'Evn_id'=>$data['MorbusHIV_pid']));
				$object_key = 'MorbusHIVSecDiag_id';
				$first_key = 'MorbusHIV_pid';
				$parent_object = 'MorbusHIV';
				$template = 'eew_morbushivsecdiag';
				$list = array('MorbusHIVSecDiagList'=>'eew_morbushivsecdiag_item');
			break;

			case 'EvnPLStom':
				$this->load->model('EvnPLStom_model', 'EvnPLStom_model');
				$object_data = $this->EvnPLStom_model->getEvnPLStomViewData($data);
				if (!empty($object_data[0]['EvnPLStom_id'])) {
					$this->load->model('Registry' . (getRegionNick() == 'ufa' ? 'Ufa' : '') . '_model', 'Reg_model');
					$dbConnection = getRegistryChecksDBConnection();
					if ( $dbConnection != 'default' ) {
						$this->regDB = $this->load->database($dbConnection, true);
						$this->Reg_model->db = $this->regDB;
					}
					if (in_array(getRegionNick(), array('buryatiya'/*, 'kareliya'*/))) {
						if ($this->Reg_model->checkEvnInRegistry(array(
								'EvnPLStom_id' => $object_data[0]['EvnPLStom_id'],
								'Lpu_id' => $data['Lpu_id']
							), 'edit') !== false) {
							$object_data[0]['accessType'] = 'view';
						}
					} else {
						$registryData = $this->Reg_model->checkEvnAccessInRegistry(array(
							'EvnPLStom_id' => $object_data[0]['EvnPLStom_id'],
							'Lpu_id' => $data['Lpu_id'],
							'session' => $data['session']
						), 'edit');

						if (is_array($registryData)) {
							if (!empty($registryData['Error_Msg'])) {
								$object_data[0]['accessType'] = 'view';
								$object_data[0]['AlertReg_Msg'] = $registryData['Error_Msg'];
							} elseif (!empty($registryData['Alert_Msg'])) {
								$object_data[0]['AlertReg_Msg'] = $registryData['Alert_Msg'];
							}
						}
					}
				}
				$object_key = 'EvnPLStom_id';
				$subsection = array(array('code'=>'EvnPLStom_data','template'=>'view_data/EvnPLStom_data'));
				$children_list = array('EvnVizitPLStom', 'EvnStick', 'EvnMediaData');
				$template = 'view_data/EvnPLStom_layout';
				break;

			case 'EvnVizitPLStom':
				$this->load->library('swEvnXml');
				$this->load->library('swFilterResponse');
				$this->load->model('EvnVizit_model', 'EvnVizit_model');
				$object_data = $this->EvnVizit_model->getEvnVizitPLStomViewData($data);
				if ( !empty($object_data[0]['EvnVizitPLStom_id']) ) {
					$this->load->model('Registry' . (getRegionNick() == 'ufa' ? 'Ufa' : '') . '_model', 'Reg_model');
					$dbConnection = getRegistryChecksDBConnection();
					if ( $dbConnection != 'default' ) {
						$this->regDB = $this->load->database($dbConnection, true);
						$this->Reg_model->db = $this->regDB;
					}
					if (in_array(getRegionNick(), array('buryatiya'/*, 'kareliya'*/))) {
						if ($this->Reg_model->checkEvnInRegistry(array(
								'EvnVizitPLStom_id' => $object_data[0]['EvnVizitPLStom_id'],
								'Lpu_id' => $data['Lpu_id']
							), 'edit') !== false) {
							$object_data[0]['accessType'] = 'view';
						}
					} else {
						$registryData = $this->Reg_model->checkEvnAccessInRegistry(array(
							'EvnVizitPLStom_id' => $object_data[0]['EvnVizitPLStom_id'],
							'Lpu_id' => $data['Lpu_id'],
							'session' => $data['session']
						), 'edit');

						if (is_array($registryData)) {
							if (!empty($registryData['Error_Msg'])) {
								$object_data[0]['accessType'] = 'view';
								$object_data[0]['AlertReg_Msg'] = $registryData['Error_Msg'];
							} elseif (!empty($registryData['Alert_Msg'])) {
								$object_data[0]['AlertReg_Msg'] = $registryData['Alert_Msg'];
							}
						}
					}
				}
				$object_key = 'EvnVizitPLStom_id';
				$subsection = array(
					array('code'=>'EvnVizitPLStom_data','template'=>'view_data/EvnVizitPLStom_data'),
					array('code'=>'EvnVizitPLStom_protocol','template'=>'xmltemplate', 'XmlType_id' => swEvnXml::EVN_VIZIT_PROTOCOL_TYPE_ID)
				);
				$children_list = array('EvnDiagPLStom','EvnDiagPLStomSop','EvnDrug', 'EvnPrescrStom', 'EvnDirectionStom', 'FreeDocument', 'EvnUslugaStom', 'EvnMediaData');
				$template = 'view_data/EvnVizitPLStom_layout';
				$xml_data = true;
				break;

			case 'EvnDiagPLStom':
				$this->load->library('swFilterResponse');
				$this->load->model('EvnDiag_model', 'EvnDiag_model');

				$object_data = $this->EvnDiag_model->getEvnDiagPLStomViewData($data);
				$object_key = 'EvnDiagPLStom_id';
				$first_key = 'get_pid';

				if ( !empty($data['EvnDiagPLStom_id']) ) {
					$template = 'view_data/EvnDiagPLStom_data';
				}
				else {
					$list = array('EvnDiagPLStomList'=>'view_data/EvnDiagPLStom_item');
					$template = 'view_data/EvnDiagPLStom_list';
				}
				break;

			case 'EvnDiagPLStomSop':
				$this->load->library('swFilterResponse');
				$this->load->model('EvnDiag_model', 'EvnDiag_model');
				$object_data = $this->EvnDiag_model->getEvnDiagPLStomSopViewData($data);
				$object_key = 'EvnDiagPLStomSop_id';
				$list = array('EvnDiagPLStomSopList'=>'view_data/EvnDiagPLStomSop_item');
				$template = 'view_data/EvnDiagPLStomSop_list';
				break;

			case 'EvnUslugaStom':
				$this->load->model('EvnUsluga_model', 'EvnUsluga');
				$params = array();
				$params['pid'] = $data['EvnUslugaStom_pid'];
				$params['Lpu_id'] = $data['Lpu_id'];
				$params['session'] = $data['session'];
				$params['class'] = 'EvnUslugaStom';
				$params['parent'] = 'EvnPLStom';
				$object_data = $this->EvnUsluga->loadEvnUslugaGrid($params);
				$object_key = 'EvnUsluga_id';
				$list = array('EvnUslugaStomList'=>'view_data/EvnUslugaStom_item');
				$template = 'view_data/EvnUslugaStom_list';
				break;

			// направления в стоматологии
			case 'EvnDirectionStom':
				$this->load->library('swFilterResponse');
				$this->load->model('EvnDirection_model', 'EvnDirection');
				$object_data = $this->EvnDirection->getEvnDirectionStomViewData($data);
				$object_key = 'EvnDirection_id';
				$use_item_arr = false;
				$children_list = array('EvnXmlDirectionLink');
				$template_str = '
				<tr class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById(\'timetable_{timetable}_{timetable_id}_toolbar\').style.display=\'block\'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById(\'timetable_{timetable}_{timetable_id}_toolbar\').style.display=\'none\'"><td class="content"><div id="timetable_{timetable}_{timetable_id}"><span id="timetable_{timetable}_{timetable_id}_go" class="link" title="перейти по записи">{RecWhat}</span> {RecTo} / {RecDate} <span id="timetable_{timetable}_{timetable_id}_num">{EvnDirection_Num}</span></div></td><td class="toolbar"><div id="timetable_{timetable}_{timetable_id}_toolbar" class="toolbar"><!--<a id="timetable_{timetable}_{timetable_id}_print" class="button icon icon-print16" title="Печать направления"><span></span></a><a id="timetable_{timetable}_{timetable_id}_edit" class="button icon icon-edit16" title="Редактировать"><span></span></a>--><a id="timetable_{timetable}_{timetable_id}_delete" class="button icon icon-delete16" title="Удалить"><span></span></a></div></td></tr>';
				if ($isMobileTemplate != '') {
					$template_str = '<tr <td><span >{RecWhat}</span> {RecTo} / {RecDate} <span>{EvnDirection_Num}</span></td></tr>';
					$children_list = null;
				}
				$related_objects = array(array('field_code'=>'timetable','field_key'=>'timetable_id'));
				$template = ($isMobileTemplate)?$isMobileTemplate.'eew_evn_direction':'view_data/EvnDirectionStom_list';;
				$list = array(
					'EvnDirectionStomList' => ($isMobileTemplate)?$isMobileTemplate.'eew_evn_direction_item':'view_data/EvnDirectionStom_item'
				);
				break;

			case 'EvnPrescrPlan': // группы назначений в стационаре
			case 'EvnPrescrPolka': // группы назначений в поликлинике
			case 'EvnPrescrStom': // группы назначений в стоматологии
				$this->load->model('EvnPrescrList_model', 'EvnPrescrList_model');
				$this->load->library('swPrescription');
				$object_data = $this->EvnPrescrList_model->doLoadPrescriptionGroupViewData($object, $data[$object . '_pid'], $data['session']);
				$object_key = $object . '_id';
				$list = array($object . 'List'=>'null');
				$template = 'view_data/' . $object . '_list';
				$use_item_arr = true;
				break;
			case 'EvnPrescrRegime':
				$this->load->model('EvnPrescrRegime_model');
				$this->load->library('swPrescription');
				$object_data = $this->EvnPrescrRegime_model->doLoadViewData($data['section'], $data[$object . '_pid'], $data['session']);
				$object_key = $data['section'].'_id';
				$list = array($object . 'List'=>'null');
				$template = 'view_data/' . $data['section'] . '_item';
				$use_item_arr = true;
				break;
			case 'EvnPrescrDiet':
				$this->load->model('EvnPrescrDiet_model');
				$this->load->library('swPrescription');
				$object_data = $this->EvnPrescrDiet_model->doLoadViewData($data['section'], $data[$object . '_pid'], $data['session']);
				$object_key = $data['section'].'_id';
				$list = array($object . 'List'=>'null');
				$template = 'view_data/' . $data['section'] . '_item';
				$use_item_arr = true;
				break;
			case 'EvnPrescrObserv':
				$this->load->model('EvnPrescrObserv_model');
				$this->load->library('swPrescription');
				$object_data = $this->EvnPrescrObserv_model->doLoadViewData($data['section'], $data[$object . '_pid'], $data['session']);
				$object_key = $data['section'].'_id';
				$list = array($object . 'List'=>'null');
				$template = 'view_data/' . $data['section'] . '_item';
				$use_item_arr = true;
				break;
			case 'EvnCourseTreat':
				$this->load->model('EvnPrescrTreat_model');
				$this->load->library('swPrescription');
				$object_data = $this->EvnPrescrTreat_model->doLoadViewData($data['section'], $data[$object . '_pid'], $data['session']);
				$object_key = $data['section'].'_id';
				$list = array($object . 'List'=>'null');
				$template = 'view_data/' . $data['section'] . '_item';
				$use_item_arr = true;
				break;
			case 'EvnCourseProc':
				$this->load->model('EvnPrescrProc_model');
				$this->load->library('swPrescription');
				$object_data = $this->EvnPrescrProc_model->doLoadViewData($data['section'], $data[$object . '_pid'], $data['session']);
				$object_key = $data['section'].'_id';
				$list = array($object . 'List'=>'null');
				$template = 'view_data/' . $data['section'] . '_item';
				$use_item_arr = true;
				break;
			case 'EvnPrescrOper':
				$this->load->model('EvnPrescrOper_model');
				$this->load->library('swPrescription');
				$object_data = $this->EvnPrescrOper_model->doLoadViewData($data['section'], $data[$object . '_pid'], $data['session']);
				$object_key = $data['section'].'_id';
				$list = array($object . 'List'=>'null');
				$template = 'view_data/' . $data['section'] . '_item';
				$use_item_arr = true;
				break;
			case 'EvnPrescrOperBlock':
				$this->load->model('EvnPrescrOperBlock_model');
				$this->load->library('swPrescription');
				$object_data = $this->EvnPrescrOperBlock_model->doLoadViewData($data['section'], $data[$object . '_pid'], $data['session']);
				$object_key = $data['section'].'_id';
				$list = array($object . 'List'=>'null');
				$template = 'view_data/' . $data['section'] . '_item';
				$use_item_arr = true;
				break;
			case 'EvnPrescrLabDiag':
				$this->load->model('EvnPrescrLabDiag_model');
				$this->load->library('swPrescription');
				if ($this->usePostgreLis) {
					$object_data = $this->EvnPrescrLabDiag_model->doLoadViewDataPostgres($data['section'], $data[$object . '_pid'], $data['session']);
				} else {
                    $object_data = array();
                }
                if (is_array($object_data)) {
                    $object_data = array_merge($object_data, $this->EvnPrescrLabDiag_model->doLoadViewData($data['section'], $data[$object . '_pid'], $data['session'], $object_data));
                }
				$object_key = $data['section'].'_id';
				$list = array($object . 'List'=>'null');
				$template = 'view_data/' . $data['section'] . '_item';
				$use_item_arr = true;
				break;
			case 'EvnPrescrFuncDiag':
				$this->load->model('EvnPrescrFuncDiag_model');
				$this->load->library('swPrescription');
				$object_data = $this->EvnPrescrFuncDiag_model->doLoadViewData($data['section'], $data[$object . '_pid'], $data['session']);
				$object_key = $data['section'].'_id';
				$list = array($object . 'List'=>'null');
				$template = 'view_data/' . $data['section'] . '_item';
				$use_item_arr = true;
				break;
			case 'EvnPrescrConsUsluga':
				$this->load->model('EvnPrescrConsUsluga_model');
				$this->load->library('swPrescription');
				$object_data = $this->EvnPrescrConsUsluga_model->doLoadViewData($data['section'], $data[$object . '_pid'], $data['session']);
				$object_key = $data['section'].'_id';
				$list = array($object . 'List'=>'null');
				$template = 'view_data/' . $data['section'] . '_item';
				$use_item_arr = true;
				break;

			case 'EvnVKSopDiag':
				$this->load->model('ClinExWork_model', 'ClinExWork_model');
				$object_data = $this->ClinExWork_model->getEvnVKSopDiagViewData(array('EvnVK_id' => $data['EvnVK_id']));
				$object_key = 'EvnVKDiagLink_id';
				$template = 'eew_evnvksopdiag';
				$list = array('EvnVKSopDiagList'=>'eew_evnvksopdiag_item');
			break;

			case 'EvnVKOslDiag':
				$this->load->model('ClinExWork_model', 'ClinExWork_model');
				$object_data = $this->ClinExWork_model->getEvnVKOslDiagViewData(array('EvnVK_id' => $data['EvnVK_id']));
				$object_key = 'EvnVKDiagLink_id';
				$template = 'eew_evnvkosldiag';
				$list = array('EvnVKOslDiagList'=>'eew_evnvkosldiag_item');
			break;

			case 'EvnVK':
				$this->load->model('ClinExWork_model', 'ClinExWork_model');
				$object_data = $this->ClinExWork_model->getEvnVKViewData($data);

				$object_key = 'EvnVK_id';

				$subsection = array(array('code'=>'EvnVK_data','template'=>'eew_evn_vk_data'));
				$children_list = array('EvnVKExpert', 'EvnVKSopDiag', 'EvnVKOslDiag');
				$template = 'eew_evn_vk';
				break;
			case 'EvnVKExpert':
				$this->load->model('ClinExWork_model', 'ClinExWork_model');
				$object_data = $this->ClinExWork_model->getEvnVKExpertViewData($data);
				$object_key = 'EvnVKExpert_id';
				$template = 'eew_evn_vk_expert';
				$list = array('EvnVKExpertList'=>'eew_evn_vk_expert_item');
				break;
			case 'EvnPrescrVaccination':
				$this->load->model('EvnPrescrVaccination_model');
				$this->load->library('swPrescription');
				$object_data = $this->EvnPrescrVaccination_model->doLoadViewData($data['section'], $data[$object . '_pid'], $data['session']);
				$object_key = $data['section'].'_id';
				$list = array($object . 'List'=>'null');
				$template = 'view_data/' . $data['section'] . '_item';
				$use_item_arr = true;
				break;

			default:
				return false;
		}

		if ($isMobileTemplate !== '') {
			if (is_array($object_data)&&count($object_data)>0) {
				foreach ($object_data[0] as $key=>$value) {
					$object_data[0][$key] = str_replace('"', '&quot;', $value);
				}
			}
		}

		// если грузим архивную запись, то accessType должен быть view.
		if (!empty($_REQUEST['archiveRecord'])) {
			foreach($object_data as &$object_dataone) {
				if (!empty($object_dataone['accessType'])) {
					$object_dataone['accessType'] = 'view';
				}
			}
		}

		//$this->textlog->add('getEvnData: Запрос на получение данных выполнен');
		/*
		$this->testmap[$test_id]['object_data'] = $object_data;
		$this->testmap[$test_id]['list'] = $list;
		$this->testmap[$test_id]['template'] = $template;
		$this->testmap[$test_id]['subsection'] = $subsection;
		$this->testmap[$test_id]['children_list'] = $children_list;
		$this->testmap[$test_id]['xml_data'] = $xml_data;
		*/
		$result = array();
		//$this->textlog->add('getEvnData: Обработка результата ...');
		if (isset($parent_key))
		{
			$result[$object]['parent_key']=$parent_key;
		}
		if (isset($parent_value))
		{
			$result[$object]['parent_value']=$parent_value;
		}
		$result[$object]['object_key']=$object_key;
		if (isset($first_key))
		{
			$result[$object]['first_key']=$first_key;
		}
		if (isset($parent_object))
		{
			$result[$object]['parent_object']=$parent_object;
		}
		$result[$object]['xml_data']=$xml_data;
		$result[$object]['children_list']=$children_list;
		$result[$object]['template']=$template;
		$result[$object]['use_item_arr']=$use_item_arr;
		$result[$object]['template_str']=$template_str;
		$result[$object]['extra'] = $extra;
		if (isset($related_objects))
		{
			$result[$object]['related_objects']=$related_objects;
		}
		if (isset($list))
		{
			$result[$object]['list']=$list;
		}
		if (isset($subsection))
		{
			$result[$object]['subsection']=$subsection;
		}

		if (is_array($object_data) AND count($object_data) > 0)
		{
			$result[$object]['item']=array();
			foreach($object_data as $i => $item)
			{
				if (!isset($item[$object_key])) {
					echo 'not_found object_key:'. $object_key;
					var_dump($item);
					continue;
				}
				$item['instance_id'] = uniqid(rand());
				$result[$object]['item'][$i] = array(
					$object_key =>$item[$object_key],
					'data' => $item
				);
				$filter = (isset($filter_field) && !empty($item[$filter_field]))?$item[$filter_field]:null;
				$result[$object]['item'][$i]['children'] = $this->getEvnChildrenData($data,$children_list,$item[$object_key],$filter);
			}

		}

		//$this->testmap[$test_id]['result'] = $result;
		//$this->textlog->add('getEvnData: Обработка результата завершена');
		//$this->textlog->add('getEvnData: Финиш ');

		return $result;
	}

	/**
	*  Возвращает массив дочерних объектов
	*  На выходе: массив
	*/
	function getEvnChildrenData($data,$children_list,$parent_id,$filter_field) {
		$data['parent_object'] = $data['object'];
		$children_data = array();
		if (is_array($children_list))
		{
			foreach ($children_list as $child_object)
			{
				$data['object'] = $child_object;
				$data['parent_object_id'] = $data['object_id'];
				$data['parent_object_value'] = $parent_id;
				$data['object_id'] = $child_object.'_id';
				$data['object_value'] = null;
				$data['filter_field'] = $filter_field;
				$child = $this->getEvnData($data);
				if (is_array($child))
				{
					$children_data = array_merge($children_data,$child);
				}
				/*
				else
				{
					$child = array($child_object => array('list'=>$child_object.'List'));
					$children_data = array_merge($children_data,$child);

				}*/
			}
		}
		if (empty($children_data))
			return false;
		else
			return $children_data;
	}

	/**
	 * Парсит шаблон секции и возвращает строку результата
	 * На выходе: строка $this->is_reload_one_section
	 */
	function getSection($section_id,$template, $data, $template_is_string = true) {
		$document = '';
		if ($template_is_string)
		{
			$document = $this->parser->parse_string($template, $data, true);
		}
		else
		{

			$document = $this->parser->parse($template, $data, true);
		}
		/*
		if(empty($this->is_reload_one_section))
		{
			$document = '<div id="'.$section_id.'_wrap" class="section-wrap">'.$document.'</div>';
		}
		*/
		return $document;
	}


	/**
	 * Добавляет в документ панели просмотра ЭМК гиперссылки по конкретным словам
	 */
	function addHyperLinks() {
		if(empty($this->document))
		{
			$this->document = '';
			return false;
		}
		$links = array(
			array('title'=>'Показать аллергологический анамнез','class'=>'showAllergHistory','regExp'=>'/(аллерг[а-я]{2,11} анамнез[а-я]{0,2}|аллерг[а-я]{2,11})/iu')
		);
		foreach($links as $row)
		{
			$this->document = preg_replace($row['regExp'], '<span class="link '.$row['class'].'" title="'.$row['title'].'">\\1</span>', $this->document);
		}
		return true;
	}

	/**
	 * Создает массив объектов с дочерними объектами в определенном формате:
	 * На выходе: массив
	 */
	function getEvnPrescrData($data) {

		$object = $data['object'];
		$object_id = $data['object_id'];

		if (isset($data['parent_object_id']) AND isset($data['parent_object_value']))
		{
			//$parent_object_id = $data['parent_object_id'];
			$data[$object.'_pid'] = $data['parent_object_value'];
		}
		else
		{
			$data[$object_id] = $data['object_value'];
		}

		if ( !empty($data['param_name']) && !array_key_exists($data['param_name'], $data) )
		{
			$data[$data['param_name']] = $data['param_value'];
		}

		/**
		 * @result array Массив данных объектов одного класса (например, направлений, посещений, рецептов), являющихся дочерними какого-либо объекта.
		 */
		$result = array();

		switch($object)
		{
			// в выборке обязательно должны быть идентификатор объекта и столбец Children_Count
			// методы модели должны уметь возвращать несколько записей по pid и одну запись по id
			case 'EvnPrescrPlan': // группы назначений в стационаре
			case 'EvnPrescrPolka': // группы назначений в поликлинике
			case 'EvnPrescrStom': // группы назначений в стоматологии
				$this->load->model('EvnPrescrList_model', 'EvnPrescrList_model');
				$this->load->library('swPrescription');
				$result = $this->EvnPrescrList_model->doLoadPrescriptionGroupViewData($object, $data[$object . '_pid'], $data['session']);
				break;
			case 'EvnPrescrRegime':
				$this->load->model('EvnPrescrRegime_model');
				$this->load->library('swPrescription');
				$result = $this->EvnPrescrRegime_model->doLoadViewData($data['section'], $data[$object . '_pid'], $data['session']);
				break;
			case 'EvnPrescrDiet':
				$this->load->model('EvnPrescrDiet_model');
				$this->load->library('swPrescription');
				$result = $this->EvnPrescrDiet_model->doLoadViewData($data['section'], $data[$object . '_pid'], $data['session']);

				break;
			case 'EvnPrescrObserv':
				$this->load->model('EvnPrescrObserv_model');
				$this->load->library('swPrescription');
				$result = $this->EvnPrescrObserv_model->doLoadViewData($data['section'], $data[$object . '_pid'], $data['session']);
				break;
			case 'EvnCourseTreat':
				$this->load->model('EvnPrescrTreat_model');
				$this->load->library('swPrescription');
				$result = $this->EvnPrescrTreat_model->doLoadViewDataGridTree($data['section'], $data[$object . '_pid'], $data['session']);
				break;
			case 'EvnCourseProc':
				$this->load->model('EvnPrescrProc_model');
				$this->load->library('swPrescription');
				$result = $this->EvnPrescrProc_model->doLoadViewData($data['section'], $data[$object . '_pid'], $data['session']);
				break;
			case 'EvnPrescrOper':
				$this->load->model('EvnPrescrOper_model');
				$this->load->library('swPrescription');
				$result = $this->EvnPrescrOper_model->doLoadViewData($data['section'], $data[$object . '_pid'], $data['session']);
				break;
			case 'EvnPrescrOperBlock':
				$this->load->model('EvnPrescrOperBlock_model');
				$this->load->library('swPrescription');
				$result = $this->EvnPrescrOperBlock_model->doLoadViewData($data['section'], $data[$object . '_pid'], $data['session']);
				break;
			case 'EvnPrescrLabDiag':
				$this->load->model('EvnPrescrLabDiag_model');
				$this->load->library('swPrescription');
				if ($this->usePostgreLis) {
					$result = $this->EvnPrescrLabDiag_model->doLoadViewDataPostgres($data['section'], $data[$object . '_pid'], $data['session']);
				} if (is_array($result)) {
                $result = array_merge($result, $this->EvnPrescrLabDiag_model->doLoadViewData($data['section'], $data[$object . '_pid'], $data['session'], $result));
            }
				break;
			case 'EvnPrescrFuncDiag':
				$this->load->model('EvnPrescrFuncDiag_model');
				$this->load->library('swPrescription');
				$result = $this->EvnPrescrFuncDiag_model->doLoadViewData($data['section'], $data[$object . '_pid'], $data['session']);
				break;
			case 'EvnPrescrConsUsluga':
				$this->load->model('EvnPrescrConsUsluga_model');
				$this->load->library('swPrescription');
				$result = $this->EvnPrescrConsUsluga_model->doLoadViewData($data['section'], $data[$object . '_pid'], $data['session']);
				break;
			case 'EvnPrescrVaccination':
				$this->load->model('EvnPrescrVaccination_model');
				$this->load->library('swPrescription');
				$result = $this->EvnPrescrVaccination_model->doLoadViewData($data['section'], $data[$object . '_pid'], $data['session']);
				break;

			default:
				return false;
		}

		// если грузим архивную запись, то accessType должен быть view.
		if (!empty($_REQUEST['archiveRecord'])) {
			foreach($result as &$object_dataone) {
				if (!empty($object_dataone['accessType'])) {
					$object_dataone['accessType'] = 'view';
				}
			}
		}

		if (is_array($result) AND count($result) > 0 /*&& $object != 'EvnCourseTreat'*/)
		{
			if($object != 'EvnPrescrLabDiag') {
				foreach ($result as $i => $item) {
					$result[$i]['object'] = $object;
					if ($object == 'EvnCourseProc' && empty($result[$i]['EvnPrescr_id'])){
						unset($result[$i]);
					}
				}
				if ($object == 'EvnCourseProc')
					$result = array_values($result);
			}
			else{
				$directionArr = array();
				$directionCount = array();
				foreach($result as $i => $item){
					if($result[$i]['EvnDirection_id']) {
						if (!in_array($result[$i]['EvnDirection_id'], $directionArr)) {
							$directionArr[] = $result[$i]['EvnDirection_id'];
							$directionCount[$result[$i]['EvnDirection_id']] = 1;
						} else
							$directionCount[$result[$i]['EvnDirection_id']]++;
					}
				}
				if(!empty($directionCount)) {
					foreach ($result as $i => $item) {
						$result[$i]['object'] = $object;
						if (isset($directionCount[$result[$i]['EvnDirection_id']]) && $directionCount[$result[$i]['EvnDirection_id']] > 1)
							$result[$i]['couple'] = 2;
						else
							$result[$i]['couple'] = 1;
					}
				}
			}
		}
		return $result;
	}

}
