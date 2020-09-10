<?php
/**
 * @package   All
 * @access   public
 * @copyright  Copyright (c) 2018 EMSIS.
 * @author   Apaev Alexander
 * @version   07.12.2018
 */
defined('BASEPATH') or die('No direct script access allowed');

class RegisterSixtyPlus_model extends SwPgModel {

	public $inputRules = array(
		'getDiagList' => array(
			array('default' => 0, 'field' => 'Person_id', 'label' => 'ИД пациента', 'rules' => '', 'type' => 'int')
		),
		'getDiagDU' => array(
			array('default' => 0, 'field' => 'Person_id', 'label' => 'ИД пациента', 'rules' => '', 'type' => 'int')
		),
		'getLabResearch' => array(
			array('default' => 0, 'field' => 'Person_id', 'label' => 'ИД пациента', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'object', 'label' => 'Профиль', 'rules' => '', 'type' => 'string')
		),
		'getIMT' => array(
			array('default' => 0, 'field' => 'Person_id', 'label' => 'ИД пациента', 'rules' => '', 'type' => 'int')
		),
		'getOncocontrol' => array(
			array('default' => 0, 'field' => 'Person_id', 'label' => 'ИД пациента', 'rules' => '', 'type' => 'int')
		),
		'getPersonIMT' => array(
			array('default' => 0, 'field' => 'Person_id', 'label' => 'ИД пациента', 'rules' => '', 'type' => 'int'),
		),
		'getMedicalCare' => array(
			array('default' => 0, 'field' => 'Person_id', 'label' => 'ИД пациента', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'object', 'label' => 'Профиль', 'rules' => '', 'type' => 'string')
		),
		'getStacMed' => array(
			array('default' => 0, 'field' => 'Person_id', 'label' => 'ИД пациента', 'rules' => '', 'type' => 'int')
		),
		'geTreatmentDrug' => array(
			array('default' => 0, 'field' => 'Person_id', 'label' => 'ИД пациента', 'rules' => '', 'type' => 'int')
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * загрузка уточненных диагнозов
	 */
	function getDiagList($data) {
		$where = '';
		$query = "with EvnDiag(
						EvnClass_SysNick,
						spec_id,
						Person_id,
						Diag_id,
						Diag_setDate,
						Lpu_id
						,MedPersonal_id
						,LpuSection_id
					) as (
							select 
									'EvnSection',
									0,
									Person_id,
									Diag_id,
									EvnSection_setDate,
									Lpu_id
									,MedPersonal_id
									,LpuSection_id
							from v_EvnSection
							where
									Person_id = :Person_id
									and Diag_id is not null
							union all
							select 
									'EvnVizitPL',
									0,
									Person_id,
									Diag_id,
									EvnVizitPL_setDate,
									Lpu_id
									,MedPersonal_id
									,LpuSection_id
							from v_EvnVizitPL EVPL
							where
									Person_id = :Person_id
									and Diag_id is not null
							union all
							select
									'EvnDiagPLSop',
									0,
									EDL.Person_id,
									EDL.Diag_id,
									EDL.EvnDiagPLSop_setDate,
									EDL.Lpu_id
									,ev.MedPersonal_id
									,LpuSection_id
							from v_EvnDiagPLSop EDL
							left join v_EvnVizit ev on EDL.EvnDiagPLSop_pid=ev.EvnVizit_id
							where 
									EDL.Person_id = :Person_id
									and EDL.Diag_id is not null
							union all
							select
									'EvnDiagPS',
									0,
									eds.Person_id,
									eds.Diag_id,
									EDS.EvnDiagPS_setDate,
									eds.Lpu_id
									,es.MedPersonal_id
									,LpuSection_id
							from v_EvnDiagPS EDS
							left join v_EvnSection es on EDS.EvnDiagPS_pid=es.EvnSection_id
							where 
									eds.Person_id = :Person_id
									and eds.Diag_id is not null
							union all
							select
									'EvnDiagSpec',
									eds.EvnDiagSpec_id,
									eds.Person_id,
									eds.Diag_id,
									EDS.EvnDiagSpec_didDT,
									eds.Lpu_id
									,0
									,0
							from v_EvnDiagSpec EDS
							where 
									eds.Person_id = :Person_id
									and eds.Diag_id is not null
							union all
							select
							'EvnVizitDispDop',
									0,
									EVDD.Person_id,
									EVDD.Diag_id,
									EVDD.EvnVizitDispDop_setDate,
									EVDD.Lpu_id
									,EVDD.MedPersonal_id
									,EVDD.LpuSection_id
							from v_EvnUslugaDispDop EVNU
							inner join v_EvnVizitDispDop EVDD on EVDD.EvnVizitDispDop_id = EVNU.EvnUslugaDispDop_pid
							inner join v_Diag diag on diag.Diag_id=EVDD.Diag_id
							left join v_DopDispInfoConsent DDIC on EVDD.DopDispInfoConsent_id=DDIC.DopDispInfoConsent_id
							left join v_SurveyTypeLink STL on STL.SurveyTypeLink_id=DDIC.SurveyTypeLink_id
							where EVNU.Person_id=:Person_id and STL.SurveyType_id=19 and EVDD.DopDispDiagType_id=2 and diag.Diag_Code not ilike 'Z%'
							union all
							select
									'EvnDiagDopDisp',
									0,
									EDDD.Person_id,
									EDDD.Diag_id,
									EDDD.EvnDiagDopDisp_setDate,
									EDDD.Lpu_id
									,0
									,0
							from
							v_EvnDiagDopDisp EDDD
					where
							(1=1) and EDDD.Person_id = :Person_id and EDDD.DeseaseDispType_id = '2'
					)
					
					select
							ED.EvnClass_SysNick as \"EvnClass_SysNick\",
							ED.Person_id as \"Person_id\",
							Ed.spec_id as \"spec_id\",
							ED.Person_id as \"pid\",
							0 as \"Children_Count\",
							ED.Lpu_id as \"Lpu_id\",
							--ED.Evn_id as \"Diag_pid\",
							ED.Diag_id as \"Diag_id\",
							ED.Diag_id as \"DiagList_id\",
							TO_CHAR (ED.Diag_setDate, 'dd.mm.yyyy') as \"Diag_setDate\",
							RTRIM(COALESCE(Diag.Diag_Code, '')) as \"Diag_Code\",
							RTRIM(COALESCE(Diag.Diag_Name, '')) as \"Diag_Name\",
							case ED.spec_id when 0 then RTRIM(COALESCE(Lpu.Lpu_Nick, ''))else EDS.EvnDiagSpec_Lpu end as \"Lpu_Nick\"
							,case ED.spec_id when 0 then RTRIM(COALESCE(MP.Person_Fio, ''))else COALESCE(EDS.EvnDiagSpec_MedWorker, MSF.Person_Fio) end as \"MedPersonal_Fio\"
							,case ED.spec_id when 0 then COALESCE(LS.LpuSectionProfile_Name, '')else EDS.EvnDiagSpec_LpuSectionProfile end as \"LpuSectionProfile_Name\"
					from EvnDiag ED
							left join v_Diag as Diag on Diag.Diag_id = ED.Diag_id
							left join v_Lpu Lpu on Lpu.Lpu_id = ED.Lpu_id
							left join v_EvnDiagSpec EDS on ED.spec_id = EDS.EvnDiagSpec_id
							left join v_MedStaffFact MSF on MSF.MedStaffFact_id = EDS.MedStaffFact_id
							left join v_MedPersonal MP on MP.MedPersonal_id = ED.MedPersonal_id and MP.Lpu_id = ED.Lpu_id
							left join v_LpuSection LS on LS.LpuSection_id = ED.LpuSection_id
					where (1=1) " . $where . "
					order by
							ED.Diag_setDate desc";


		$result = $this->db->query($query, array(
			'Person_id' => $data['Person_id']
		));

		if (is_object($result)) {
			$resp = $result->result('array');
			$diagArr = array();
			$respfiltered = array();
			foreach ($resp as $respone) {
				// фильтруем одинаковые диагнозы в посещениях
				if (!in_array($respone['Diag_id'], $diagArr)) {
					$diagArr[] = $respone['Diag_id'];
					$respfiltered[] = $respone;
				}
			}
			/* if(!$isKz){
			  return swFilterResponse::filterNotViewDiag($respfiltered, $data);
			  } */
			$diagArray = Array();
			$res = Array();
			foreach ($respfiltered as $val) {
				if (!in_array($val['Diag_id'], $diagArray)) {
					if ($val['spec_id'] > 0) {
						if ($val['MedPersonal_Fio'] != '') {
							$val['LpuSectionProfile_Name'] = '<a id="DiagList_' . $val["Diag_id"] . '_' . $val["spec_id"] . '_viewDiag">' . $val['MedPersonal_Fio'] . '</a>';
						} else {
							$val['LpuSectionProfile_Name'] = '<a id="DiagList_' . $val["Diag_id"] . '_' . $val["spec_id"] . '_viewDiag">' . 'Просмотр' . '</a>';
						}
					}
					$res[] = $val;
					$diagArray[] = $val['Diag_id'];
				}
			}
			return $res;
		} else
			return false;
	}

	/**
	 * Загрузка диагнозов ДУ
	 */
	function getDiagDU($data) {
		$diagFilter = "";
		$filter = "";
		$diagFilter = getAccessRightsDiagFilter('dg.Diag_Code');
		if (!empty($diagFilter)) {
			$filter .= " and $diagFilter";
		}
		$query = "
			select 
			    PD.PersonDisp_id as \"PersonDisp_id\",
			    PS.Person_id as \"Person_id\",
			    to_char (PD.PersonDisp_begDate, 'dd.mm.yyyy') as \"PersonDisp_begDate\",
			    dg.Diag_Code as \"Diag_Code\",
			    dg.Diag_Name as \"Diag_Name\"
			from v_PersonState PS
				inner join v_PersonDisp PD on PS.Person_id = PD.Person_id
				left join v_Diag dg on PD.Diag_id = dg.Diag_id 
			where (1=1)
				{$filter}
				and ( PD.PersonDisp_endDate is null or PD.PersonDisp_endDate > dbo.tzGetDate())
				and PD.Person_id = :Person_id";

		$result = $this->db->query($query, array('Person_id' => $data['Person_id']));
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Результаты анализов
	 */
	function getLabResearch($data) {
		$filters = "";
		$from = "";
		$select = "";
		switch ($data['object']) {
			case 'Glucose':
				$select = ",
				ut.UslugaTest_ResultValue as \"EvnUslugaPar_ResultValue\",
				ut.UslugaTest_ResultUnit as \"EvnUslugaPar_ResultUnit\"";
				$filters .= "and (UslugaComplex.UslugaComplex_Code = 'A09.05.023'
				or UslugaComplex.UslugaComplex_Code = 'A09.05.026.001'
				or UslugaComplex.UslugaComplex_Code = 'A09.05.023.001'
				or UslugaComplex.UslugaComplex_Code = 'A09.05.023.002'
				or UslugaComplex.UslugaComplex_Code = 'A09.05.023.003'
				or UslugaComplex.UslugaComplex_Code = 'A09.05.023.004'
				or UslugaComplex.UslugaComplex_Code = 'A09.05.023.005'
				or UslugaComplex.UslugaComplex_Code = 'A09.05.023.006'
				or UslugaComplex.UslugaComplex_Code = 'A09.05.023.007'
				or UslugaComplex.UslugaComplex_Code = 'A09.05.023.008'
				or UslugaComplex.UslugaComplex_Code = 'A09.05.023.009'
				or UslugaComplex.UslugaComplex_Code = 'A09.05.040')
					and ut.UslugaTest_ResultApproved = 2";
				$from = "left join v_UslugaTest ut on ut.UslugaTest_pid = EUP.EvnUslugaPar_id
					left join UslugaComplex on UslugaComplex.UslugaComplex_id = ut.UslugaComplex_id";
				break;

			case 'Cholesterol':
				$select = ",
				ut.UslugaTest_ResultValue as \"EvnUslugaPar_ResultValue\",
				ut.UslugaTest_ResultUnit as \"EvnUslugaPar_ResultUnit\"";
				$filters .= "and (UslugaComplex.UslugaComplex_Code = 'A09.05.026'
				or UslugaComplex.UslugaComplex_Code = 'A09.05.026.001'
				or UslugaComplex.UslugaComplex_Code = 'A09.05.026.002')
					and ut.UslugaTest_ResultApproved = 2";
				$from = "left join v_UslugaTest ut on ut.UslugaTest_pid = EUP.EvnUslugaPar_id
					left join UslugaComplex on UslugaComplex.UslugaComplex_id = ut.UslugaComplex_id";
				break;

			case 'creatine':
				$select = ",
				ut.UslugaTest_ResultValue as \"EvnUslugaPar_ResultValue\",
				ut.UslugaTest_ResultUnit as \"EvnUslugaPar_ResultUnit\"";
				$filters .= "and (UslugaComplex.UslugaComplex_Code = 'A09.05.020'
				or UslugaComplex.UslugaComplex_Code = 'A09.05.020.001'
				or UslugaComplex.UslugaComplex_Code = 'A09.28.006'
				or UslugaComplex.UslugaComplex_Code = 'A09.28.006.001'
				or UslugaComplex.UslugaComplex_Code = 'A09.28.006.006'
				or UslugaComplex.UslugaComplex_Code = 'A12.28.002.001'
				or UslugaComplex.UslugaComplex_Code = 'A12.28.002.006')
					and ut.UslugaTest_ResultApproved = 2";
				$from = "left join v_UslugaTest ut on ut.UslugaTest_pid = EUP.EvnUslugaPar_id
					left join UslugaComplex on UslugaComplex.UslugaComplex_id = ut.UslugaComplex_id";
				break;
			case 'ALT':
				$select = ",
				ut.UslugaTest_ResultValue as \"EvnUslugaPar_ResultValue\",
				ut.UslugaTest_ResultUnit as \"EvnUslugaPar_ResultUnit\"";
				$filters .= "and (UslugaComplex.UslugaComplex_Code = 'A09.05.042')
					and ut.UslugaTest_ResultApproved = 2";
				$from = "left join v_UslugaTest ut on ut.UslugaTest_pid = EUP.EvnUslugaPar_id
					left join UslugaComplex on UslugaComplex.UslugaComplex_id = ut.UslugaComplex_id";
				break;
			case 'AST':
				$select = ",
				ut.UslugaTest_ResultValue as \"EvnUslugaPar_ResultValue\",
				ut.UslugaTest_ResultUnit as \"EvnUslugaPar_ResultUnit\"";
				$filters .= "and (UslugaComplex.UslugaComplex_Code = 'A09.05.041')
					and ut.UslugaTest_ResultApproved = 2";
				$from = "left join v_UslugaTest ut on ut.UslugaTest_pid = EUP.EvnUslugaPar_id
					left join UslugaComplex on UslugaComplex.UslugaComplex_id = ut.UslugaComplex_id";
				break;
			case 'OAK':
				$select .= ",doc.EvnXml_id as \"EvnXml_id\",
				EvnXml_id as \"prosmotr\"";
				$from .= "LEFT JOIN LATERAL (
						select EvnXml_id as EvnXml_id
						from v_EvnXml
						where Evn_id = EUP.EvnUslugaPar_id
						order by EvnXml_insDT desc
						limit 1 
					) doc ON TRUE
					left join UslugaComplex on UslugaComplex.UslugaComplex_id = EUP.UslugaComplex_id";
				$filters .= "and (UslugaComplex.UslugaComplex_Code = 'B03.016.002'
				or UslugaComplex.UslugaComplex_Code = 'B03.016.003')
				and doc.EvnXml_id is not null";
				break;

			case 'OAM':
				$select .= ",doc.EvnXml_id as \"EvnXml_id\",
				EvnXml_id as \"prosmotr\"";
				$from .= "LEFT JOIN LATERAL (
						select EvnXml_id as EvnXml_id
						from v_EvnXml
						where Evn_id = EUP.EvnUslugaPar_id
						order by EvnXml_insDT desc
						limit 1 
					) doc ON TRUE
					left join UslugaComplex on UslugaComplex.UslugaComplex_id = EUP.UslugaComplex_id";
				$filters .= "and UslugaComplex.UslugaComplex_Code = 'B03.016.006'
					and doc.EvnXml_id is not null";
				break;

			case 'EKG':
				$select .= ",doc.EvnXml_id as \"EvnXml_id\",
				EvnXml_id as \"prosmotr\"";
				$from .= "LEFT JOIN LATERAL (
						select EvnXml_id as EvnXml_id
						from v_EvnXml
						where Evn_id = EUP.EvnUslugaPar_id
						order by EvnXml_insDT desc
						limit 1 
					) doc ON TRUE
					left join UslugaComplex on UslugaComplex.UslugaComplex_id = EUP.UslugaComplex_id";
				$filters .= "and (UslugaComplex.UslugaComplex_Code = 'A05.10.002'
				or UslugaComplex.UslugaComplex_Code = 'A05.10.004'
				or UslugaComplex.UslugaComplex_Code = 'A05.10.004.001'
				or UslugaComplex.UslugaComplex_Code = 'A05.10.006.001'
				or UslugaComplex.UslugaComplex_Code = 'A05.10.007'
				or UslugaComplex.UslugaComplex_Code = 'A05.10.007.002'
				or UslugaComplex.UslugaComplex_Code = 'A05.10.010'
				or UslugaComplex.UslugaComplex_Code = 'A12.10.001'
				or UslugaComplex.UslugaComplex_Code = 'A12.10.002')
				and doc.EvnXml_id is not null";
				break;

			case 'fluoro':
				$select .= ",doc.EvnXml_id as \"EvnXml_id\",
				EvnXml_id as \"prosmotr\"";
				$from .= "LEFT JOIN LATERAL (
						select EvnXml_id as EvnXml_id
 						from v_EvnXml
						where Evn_id = EUP.EvnUslugaPar_id
						order by EvnXml_insDT desc
						limit 1 
					) doc ON TRUE
					left join UslugaComplex on UslugaComplex.UslugaComplex_id = EUP.UslugaComplex_id";
				$filters .= "and (UslugaComplex.UslugaComplex_Code = 'A06.09.006'
				or UslugaComplex.UslugaComplex_Code = 'A06.09.006.001')
				and doc.EvnXml_id is not null";
				break;

			case 'Mammography':
				$select .= ",doc.EvnXml_id as \"EvnXml_id\",
				EvnXml_id as \"prosmotr\"";
				$from .= "LEFT JOIN LATERAL (
						select EvnXml_id as EvnXml_id
						from v_EvnXml
						where Evn_id = EUP.EvnUslugaPar_id
						order by EvnXml_insDT desc
						limit 1 
					) doc ON TRUE
					left join UslugaComplex on UslugaComplex.UslugaComplex_id = EUP.UslugaComplex_id";
				$filters .= "and (UslugaComplex.UslugaComplex_Code = 'A05.20.001'
				or UslugaComplex.UslugaComplex_Code = 'A05.20.004'
				or UslugaComplex.UslugaComplex_Code = 'A05.20.004.006')
				and doc.EvnXml_id is not null";
				break;
			case 'Echo':
				$select .= ",doc.EvnXml_id as \"EvnXml_id\",
				EvnXml_id as \"prosmotr\"";
				$from .= "LEFT JOIN LATERAL (
						select EvnXml_id as EvnXml_id
						from v_EvnXml
						where Evn_id = EUP.EvnUslugaPar_id
						order by EvnXml_insDT desc
						limit 1 
					) doc ON TRUE 
					left join UslugaComplex on UslugaComplex.UslugaComplex_id = EUP.UslugaComplex_id";
				$filters .= "and (UslugaComplex.UslugaComplex_Code = 'A04.10.002'
				or UslugaComplex.UslugaComplex_Code = 'A04.10.002.001'
				or UslugaComplex.UslugaComplex_Code = 'A04.10.002.002'
				or UslugaComplex.UslugaComplex_Code = 'A04.10.002.003'
				or UslugaComplex.UslugaComplex_Code = 'A04.10.002.004'
				or UslugaComplex.UslugaComplex_Code = 'A04.10.002.005')
				and doc.EvnXml_id is not null";
				break;
		}

		$query = "
			select
				-- select
				EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				to_char (EUP.EvnUslugaPar_setDate, 'dd.mm.yyyy') as \"EvnUslugaPar_setDate\"
				{$select}
				-- end select
			from
				-- from
				v_PersonState PS
				inner join v_EvnUslugaPar EUP on EUP.Person_id = PS.Person_id
				{$from}
				-- end from
			where
				-- where
				(1=1)
				{$filters}
				and EUP.EvnUslugaPar_setDate is not null
				and PS.Person_id = :Person_id
				-- end where
			order by
				-- order by
				EUP.EvnUslugaPar_setDate desc
				-- end order by
            limit 12
				";
		$result = $this->db->query($query, array('Person_id' => $data['Person_id']));
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	

	/**
	 * Онкоконтроль
	 */
	function getOncocontrol($data) {
		$query = "
			select
				-- select
				PS.Person_id as \"Person_id\",
				to_char (o.PersonOnkoProfile_DtBeg, 'dd.mm.yyyy') as \"PersonOnkoProfile_DtBeg\",
				COALESCE((select 'Необходим Онкоконтроль' 
								from onko.PersonOnkoQuestions t
								where t.PersonOnkoProfile_id = o.PersonOnkoProfile_id limit 1), 'Не нужен Онкоконтроль') as \"monitored_Name\"
				-- end select
			from
				-- from
				v_PersonState PS
				left join onko.v_PersonOnkoProfile o on o.Person_id = PS.Person_id
				-- end from
			where
				-- where
				(1=1)
				and PS.Person_id = :Person_id
				and o.PersonOnkoProfile_DtBeg is not null
				-- end where
			order by
				-- order by
				o.PersonOnkoProfile_DtBeg desc --o.PersonOnkoProfiles_insDT desc
				-- end order by
            limit 12
				";

		$result = $this->db->query($query, array('Person_id' => $data['Person_id']));
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Случаи медпомощи
	 */
	function getMedicalCare($data) {
		$filters = "";
		$from = "";
		$select = "";
		switch ($data['object']) {
			case 'Cardiology':
				$filters .= " and (LS.LpuSectionProfile_Code = '502' or LS.LpuSectionProfile_Code = '602' or LS.LpuSectionProfile_Code = '802')";
				break;
			case 'Neurology':
				$filters .= " and (LS.LpuSectionProfile_Code = '509' or LS.LpuSectionProfile_Code = '609' or LS.LpuSectionProfile_Code = '809')";
				break;
			case 'Oncology':
				$filters .= " and (LS.LpuSectionProfile_Code = '521' or LS.LpuSectionProfile_Code = '621' or LS.LpuSectionProfile_Code = '821')";
				break;
			case 'Ophthalmology':
				$filters .= " and (LS.LpuSectionProfile_Code = '518' or LS.LpuSectionProfile_Code = '618' or LS.LpuSectionProfile_Code = '818')";
				break;
			case 'Gynecology':
				$filters .= " and (LS.LpuSectionProfile_Code = '522' or LS.LpuSectionProfile_Code = '622' or LS.LpuSectionProfile_Code = '822')";
				break;
		}

		$query = "
			select
				-- select
				EVPL.EvnVizitPL_id as \"EvnVizitPL_id\",
				EVPL.EvnVizitPL_pid as \"EvnPL_id\",
				to_char (EVPL.EvnVizitPL_setDate, 'dd.mm.yyyy') as \"EvnVizitPL_setDate\",
				Evn.Person_id as \"Person_id\",
				LS.LpuSectionProfile_Code as \"LpuSectionProfile_Code\"
				-- end select
			from
				-- from
				v_EvnVizitPL EVPL
				inner join Evn on Evn.Evn_id = EVPL.EvnVizitPL_pid
								and Evn.EvnClass_id = 3
								and Evn.Evn_deleted = 1
				inner join EvnPL EPL on EPL.Evn_id = Evn.Evn_id				
				left join v_LpuSection LS on LS.LpuSection_id = EVPL.LpuSection_id
				-- end from
			where
				-- where
				(1=1)
				and Evn.Person_id = :Person_id
				{$filters}
				-- end where
			order by
				-- order by
				EVPL.EvnVizitPL_setDate desc
				-- end order by";

		$result = $this->db->query($query, array('Person_id' => $data['Person_id']));
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Случаи стац медпомощи
	 */
	function getStacMed($data) {
		$filters = '';
		$diag_field_code = '';
		$diag_field_code = array('Dtmp.Diag_Code', 'DP.Diag_Code');
		$diagFilter = getAccessRightsDiagFilter($diag_field_code);
		if (!empty($diagFilter)) {
			$filters .= " and $diagFilter";
		}

		$query = "
			select
				-- select
		
						EPS.EvnPS_id as \"EvnPS_id\",
						EPS.Person_id as \"Person_id\",
						EPS.PersonEvn_id as \"PersonEvn_id\",
						EPS.Server_id as \"Server_id\",
						to_char (EPS.EvnPS_setDate, 'dd.mm.yyyy') || ' - ' || coalesce(to_char (EPS.EvnPS_disDate, 'dd.mm.yyyy'), '') as \"EvnPS_Date\",
						COALESCE(Dtmp.Diag_FullName, DP.Diag_FullName) as \"Diag_Name\",
						EPS.EvnPS_id as \"EvnPS_id\"
						
				-- end select
			from
				-- from
		
						v_PersonState PS
						inner join v_EvnPS EPS on EPS.Person_id = PS.Person_id
						left join v_EvnSection EPSLastES on EPSLastES.EvnSection_pid = EPS.EvnPS_id and EPSLastES.EvnSection_Index = EPSLastES.EvnSection_Count-1
						left join v_Diag Dtmp on Dtmp.Diag_id = EPSLastES.Diag_id
						left join v_Diag DP on DP.Diag_id = EPS.Diag_pid
						--добавить ограничения по диагнозам
				-- end from
			where
				-- where
					(1 = 1) 
					and EPS.Person_id = :Person_id
					{$filters}
				-- end where
			
			order by
				-- order by
					EPS.EvnPS_setDate desc
				-- end order by";

		$result = $this->db->query($query, array('Person_id' => $data['Person_id']));
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка измерений массы пациента
	 */
	function getPersonIMT($data) {

		$query = "
			select
				-- select
				pw.PersonWeight_id as \"PersonWeight_id\",
				case when pw.Okei_id = 36 then
					cast(pw.PersonWeight_Weight as float) / 1000
				else
					pw.PersonWeight_Weight
				end as \"PersonWeight_Weight\",
				PH.PersonHeight_Height as \"PersonHeight_Height\",
				to_char (pw.PersonWeight_setDT, 'dd.mm.yyyy') as \"PersonWeight_setDate\",
				case 
					when COALESCE(PH.PersonHeight_Height, 0) > 0 and pw.PersonWeight_Weight is not null then
						to_char(ROUND(cast(
							case when pw.Okei_id = 36 then
								cast(pw.PersonWeight_Weight as numeric) / 1000
							else
								pw.PersonWeight_Weight
							end
						as numeric)/POWER(0.01*cast(PH.PersonHeight_Height as numeric),2),2), 'FM999999999999999999')
					else ''
				end as \"PersonWeight_Imt\",
				pw.Person_id as \"Person_id\"
				-- end select
			from
				-- from
				v_PersonWeight pw
				LEFT JOIN LATERAL (
					select PersonHeight_Height as PersonHeight_Height
					from v_PersonHeight
					where Person_id = :Person_id
						and HeightMeasureType_id is not null
						and PersonHeight_setDT <= PW.PersonWeight_setDT
					order by PersonHeight_setDT desc
					limit 1
				) PH ON TRUE
				-- end from
			where 
				-- where
				pw.Person_id = :Person_id
				-- end where
			order by
				-- order by
				pw.PersonWeight_setDT desc
				-- end order by
            limit 12
				";

		/* $response = $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
		  return $response; */

		$result = $this->db->query($query, array('Person_id' => $data['Person_id']));
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение даты обновления регистра
	 */
	function getupdDT() {
		$query = "
			SELECT
				TO_CHAR (max(RegisterSixtyPlus_insDT), 'dd.mm.yyyy') || ' ' || TO_CHAR (max(RegisterSixtyPlus_insDT), 'hh:mm:ss') as \"RegisterSixtyPlus_insDT\"
			from RegisterSixtyPlus limit 1
		";

		$result = $this->db->query($query);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Лекарственное лечение
	 */
	function geTreatmentDrug($data) {

		$query = "
			select distinct
				EP.EvnPrescrTreat_id as \"EvnPrescrTreat_id\",
				CLSPHARMAGROUP.NAME as \"NAME\",
				Drug.Drug_id as \"Drug_id\"
				,dcm.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				coalesce(df.CLSDRUGFORMS_NameLatinSocr,df.NAME,Drug.DrugForm_Name,'') as \"DrugForm_Name\",
				EPTD.EvnPrescrTreatDrug_id as \"EvnPrescrTreatDrug_id\"
				,to_char(EP.EvnPrescrTreat_setDate,'dd.mm.yyyy') as \"EvnPrescr_setDate\"
				,ECT.EvnCourseTreat_MaxCountDay as \"CountInDay\" -- сколько штук в день
				,EPTD.EvnPrescrTreatDrug_DoseDay as \"DoseDay\"
				,to_char(ECT.EvnCourseTreat_Duration, 'FM999999999999999999') || ' ' || COALESCE(DTP.DurationType_Nick, '') as \"CourseDuration\" -- продолжительность 
				,EPTD.EvnPrescrTreatDrug_KolvoEd as \"KolvoEd\" -- кол-во
				,EPTD.EvnPrescrTreatDrug_Kolvo as \"Kolvo\"
				,COALESCE(PIT.PrescriptionIntroType_Name, '') as \"PrescriptionIntroType_Name\" -- вид приема 
				,coalesce(dcm.DrugComplexMnn_RusName, Drug.Drug_Name, '') as \"Drug_Name\" -- МНН
				,GUS.GoodsUnit_Name as \"GoodsUnitS_Name\", -- упаковка
				MP.Person_Fio as \"Person_Fio\",
				LS.LpuSectionProfile_Name as \"LpuSectionProfile_Name\"
 				
			from v_EvnPrescrTreat EP
				--left join v_EvnPrescr Prescr on Ep.EvnPrescrTreat_id = Prescr.EvnPrescr_id

				left join v_EvnPrescrTreatDrug EPTD on EPTD.EvnPrescrTreat_id = EP.EvnPrescrTreat_id
				left join v_EvnCourseTreat ECT on EP.EvnCourse_id = ECT.EvnCourseTreat_id -- тут
				left join v_EvnCourseTreatDrug ECTD on ECT.EvnCourseTreat_id = ECTD.EvnCourseTreat_id
				left join PrescriptionIntroType PIT on ECT.PrescriptionIntroType_id = PIT.PrescriptionIntroType_id
				left join rls.v_Drug Drug on Drug.Drug_id = EPTD.Drug_id
				left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = coalesce(EPTD.DrugComplexMnn_id,Drug.DrugComplexMnn_id)
				left join rls.CLSDRUGFORMS df on dcm.CLSDRUGFORMS_ID = df.CLSDRUGFORMS_ID
				LEFT JOIN rls.v_PREP PREP on PREP.Prep_id = Drug.DrugPrep_id
				LEFT JOIN rls.v_PREP_IIC PREP_IIC on PREP_IIC.PREPID = PREP.Prep_id
				LEFT JOIN rls.v_CLSPHARMAGROUP CLSPHARMAGROUP on CLSPHARMAGROUP.CLSPHARMAGROUP_ID = PREP_IIC.PHGRID
				
				left join DurationType DTP on ECT.DurationType_id = DTP.DurationType_id
				left join v_GoodsUnit GUS on GUS.GoodsUnit_id = ECTD.GoodsUnit_sid
				
				left join v_MedPersonal MP on MP.MedPersonal_id = ECT.MedPersonal_id and MP.Lpu_id = ECT.Lpu_id
				left join v_LpuSection LS on LS.LpuSection_id = ECT.LpuSection_id

			where (1=1)
				and EP.Person_id = :Person_id
				and EP.PrescriptionStatusType_id != 3 -- исключаем отмененные
			order by
				\"EvnPrescr_setDate\",
				\"EvnPrescrTreatDrug_id\"
			";
		$result = $this->db->query($query, array(
			'Person_id' => $data['Person_id']
		));

		if (is_object($result)) {
			$resp = $result->result('array');
			$EvnCourseArr = array();
			$respfiltered = array();
			foreach ($resp as $respone) {

				if (!in_array($respone['EvnPrescrTreat_id'], $EvnCourseArr)) {
					$EvnCourseArr[] = $respone['EvnPrescrTreat_id'];
					$respfiltered[] = $respone;
				}
			}

			$EvnCourseArray = Array();
			$res = Array();
			foreach ($respfiltered as $val) {
				if (!in_array($val['EvnPrescrTreat_id'], $EvnCourseArray)) {

					$res[] = $val;
					$EvnCourseArray[] = $val['EvnPrescrTreat_id'];
				}
			}
			return $res;
		} else
			return false;
	}

}
