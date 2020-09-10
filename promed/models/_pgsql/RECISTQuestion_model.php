<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * RECISTQuestion_model - RECIST
 * сделано в режиме совместимости с OnkoCtrl
 */
class RECISTQuestion_model extends swModel {

    /**
     * Журнал анкет
     */
    public function GetOnkoCtrlProfileJurnal($data) {
		$queryParams = array();
		$filter = "(1=1) and MFP.MedicalForm_id = '62' and MFP.Evn_id is not null";
		if (isset($data['Empty']) && $data['Empty'] == 1) {
			return array('data'=>array(),'totalCount'=>0);
		}
		if (isset($data['Lpu_id'])) {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}
		if (isset($data['SurName'])) {
			$filter .= " and ps.Person_SurName like :SurName";
			$queryParams['SurName'] = $data['SurName']."%";
		}
		if (isset($data['FirName'])) {
			$filter .= " and ps.Person_FirName like :FirName";
			$queryParams['FirName'] = $data['FirName']."%";
		}
		if (isset($data['SecName'])) {
			$filter .= " and ps.Person_SecName like :SecName";
			$queryParams['SecName'] = $data['SecName']."%";
		}
		if (isset($data['BirthDayRange'][0])) {
			$filter .= " and ps.Person_BirthDay  <= :BirthDayRangeBegin";
			$queryParams['BirthDayRangeBegin'] = $data['BirthDayRange'][0];
		}
		if (isset($data['BirthDayRange'][1])) {
			$filter .= " and ps.Person_BirthDay  <= :BirthDayRangeEnd";
			$queryParams['BirthDayRangeEnd'] = $data['BirthDayRange'][1];
		}
		if (isset($data['BirthDay'])) {
			$filter .= " and ps.Person_BirthDay = :BirthDay";
			$queryParams['BirthDay'] = $data['BirthDay'];
		}
		if (isset($data['PeriodRange'][0])) {
			$filter .= " and MFP.MedicalFormPerson_setDT  >= :PeriodRangeBegin";
			$queryParams['PeriodRangeBegin'] = $data['PeriodRange'][0];
		}
		if (isset($data['PeriodRange'][1])) {
			$filter .= " and MFP.MedicalFormPerson_setDT  <= :PeriodRangeEnd";
			$queryParams['PeriodRangeEnd'] = $data['PeriodRange'][1];
		}
		if (isset($data['Doctor'])) {
			$filter .= " and MFP.MedStaffFact_id = :Doctor";
			$queryParams['Doctor'] = $data['Doctor'];
		}
		if (isset($data['Sex_id'])) {
			$filter .= " and ps.Sex_id = :Sex_id";
			$queryParams['Sex_id'] = $data['Sex_id'];
		}
		if (isset($data['Uch'])) {
			if ($data['Uch'] == '0') {
				$filter .= " and (pcard.LpuRegion_id = '' or lpu.Lpu_id is null)";
			} else {
				$filter .= " and pcard.LpuRegion_id = :Uch";
				$queryParams['Uch'] = $data['Uch'];
			}
		}
   
		$sql = "
			SELECT  
			-- select
				MFP.MedicalFormPerson_id as \"id\",
				MFP.Person_id as \"Person_id\",
				MFP.MedicalFormPerson_id as \"PersonOnkoProfile_id\",
				ps.Person_SurName as \"SurName\",
				ps.Person_FirName as \"FirName\",
				ps.Person_SecName as \"SecName\",
				rtrim(rtrim(coalesce(ps.Person_Surname, '')) || ' ' || rtrim(coalesce(ps.Person_Firname, '')) || ' ' || rtrim(coalesce(ps.Person_Secname, ''))) as \"fio\",
				to_char (ps.Person_BirthDay, 'dd.mm.yyyy') as \"BirthDay\",
				ps.Sex_id as \"Sex_id\",
				substring(sex.Sex_Name, 1, 1) as \"sex\",
				coalesce(uaddr.Address_Address,paddr.Address_Address) as \"Address\",
				lpu.Lpu_id as \"Lpu_id\",
				lpu.Lpu_Nick as \"Lpu_Nick\",
				lr.LpuRegionType_Name || ' №' || lr.LpuRegion_Name as \"uch\",
				lr.LpuRegion_id as \"LpuRegion_id\",
				to_char (MFP.MedicalFormPerson_setDT, 'dd.mm.yyyy') as \"PersonOnkoProfile_DtBeg\",				
				MFP.MedStaffFact_id as \"MedStaffFact_id\"
			-- end select
			FROM 
			-- from
				v_MedicalFormPerson MFP
				left join v_MedStaffFact msf on msf.MedStaffFact_id = MFP.MedStaffFact_id
				inner join v_PersonState ps on MFP.Person_id = ps.Person_id
				left join v_Sex sex on sex.Sex_id = ps.Sex_id
				LEFT JOIN LATERAL (
					select
						pc.Person_id as PersonCard_Person_id,
						pc.Lpu_id as Lpu_id,
						pc.LpuRegion_id as LpuRegion_id
					from v_PersonCard pc
					where
						pc.Person_id = ps.Person_id
						and LpuAttachType_id = 1
					order by PersonCard_begDate desc
					limit 1
				) as pcard ON TRUE
				left join v_Lpu lpu on pcard.Lpu_id = lpu.Lpu_id
				left join v_LpuRegion lr on pcard.LpuRegion_id = lr.LpuRegion_id
				left join v_Address uaddr on ps.UAddress_id = uaddr.Address_id
				left join v_Address paddr on ps.PAddress_id = paddr.Address_id
			-- end from  
			WHERE 
			-- where
				{$filter}
			-- end where        
			order by 
			-- order by
				MFP.MedicalFormPerson_setDT desc
			-- end order by  
		";
				
		return $this->getPagingResponse($sql, $queryParams, $data['start'], $data['limit'], true);
    }

    /**
     * Загрузка доп инфы для формы анкетирования
     */
    public function loadOnkoContrProfileFormInfo($data) {
        $queryParams = array();
		$query = "
			SELECT 
				MFP.MedicalFormPerson_id as \"PersonOnkoProfile_id\",
				MFP.Person_id as \"Person_id\",
				to_char (MFP.MedicalFormPerson_setDT, 'dd.mm.yyyy') as \"PersonOnkoProfile_DtBeg\",
				msf.Lpu_id as \"Lpu_id\",
				l.Lpu_Nick as \"Lpu_Nick\",
				msf.MedStaffFact_id as \"MedStaffFact_id\",
				msf.LpuBuilding_id as \"LpuBuilding_id\",
				msf.LpuSection_id as \"LpuSection_id\",
				MFD.MedicalFormDecision_Name as \"ResultRECIST_id\"
			FROM v_MedicalFormPerson MFP
				left join v_MedicalFormDecisionPerson MFDP on MFDP.MedicalFormPerson_id = MFP.MedicalFormPerson_id
				left join v_MedicalFormDecision MFD on MFD.MedicalFormDecision_id = MFDP.MedicalFormDecision_id
				left join v_MedStaffFact msf on msf.MedStaffFact_id = MFP.MedStaffFact_id
				left join v_Lpu l on l.Lpu_id = msf.lpu_id
				where MFP.Person_id = :Person_id
				and (MFP.Evn_id = :Evn_id or MFP.MedicalFormPerson_id = :PersonOnkoProfile_id)
		";
        $queryParams['Person_id'] = $data['Person_id'];
		$queryParams['PersonOnkoProfile_id'] = $data["PersonOnkoProfile_id"];
		$queryParams['Evn_id'] = $data["EvnUslugaPar_id"];

		return $this->queryResult($query, $queryParams);
    }

	
    /**
	 * Получение списка вопросов для анкеты
     */
    public function getOnkoQuestions($data) {
        $sql = "
        select
			MFQ.MedicalFormQuestion_id as \"OnkoQuestions_id\",
			MFQ.MedicalFormQuestion_Name as \"OnkoQuestions_Name\",
			coalesce(MFD.MedicalFormAnswers_id, 1) as \"val\",
			'MedicalFormAnswers' as \"AnswerClass_SysNick\",
			3 as \"AnswerType_Code\"
		from
			dbo.MedicalFormQuestion MFQ
			left join v_MedicalFormData MFD on 
					MFD.MedicalFormQuestion_id = MFQ.MedicalFormQuestion_id and 
					MFD.MedicalFormPerson_id = :MedicalFormPerson_id
			where MFQ.MedicalForm_id = '62'
			and MFQ.MedicalFormQuestion_deleted is null
			order by MFQ.MedicalFormQuestion_id asc
        ";
		$queryParams['MedicalFormPerson_id'] = $data['PersonOnkoProfile_id']; 
		return $this->queryResult($sql, $queryParams);
    }

    /**
     * сохранение информации об анкетировании пациента
     */
    public function savePersonOnkoProfile($data) {
		asort($data["QuestionAnswer"]);
		$procedure = empty($data['PersonOnkoProfile_id']) ? 'p_MedicalFormPerson_ins' : 'p_MedicalFormPerson_upd';
		$med_staff_fact_id = $this->getFirstResultFromQuery("
			select MedStaffFact_id as \"MedStaffFact_id\" from v_MedStaffFact
			where LpuSection_id = :LpuSection_id and MedPersonal_id = :MedPersonal_id
			order by medstafffact_insdt desc", $data);
		if ($med_staff_fact_id) {
			$queryParams = array(
				'MedicalFormPerson_id' => $data["PersonOnkoProfile_id"],
				'MedicalForm_id' => '62',
				'Person_id' => $data["Person_id"],
				'MedicalFormPerson_setDT' => $data["Profile_Date"],
				'Lpu_id' => $data["Lpu_id"],
				'Evn_id' => $data["EvnUslugaPar_id"],
				'MedStaffFact_id' => $med_staff_fact_id,
				'pmUser_id' => $data["pmUser_id"]
			);
			$query = "
				select
					MedicalFormPerson_id as \"MedicalFormPerson_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Message\"
				from {$procedure} (
					MedicalFormPerson_id := :MedicalFormPerson_id,
					MedicalForm_id := :MedicalForm_id,
					Person_id := :Person_id,
					MedicalFormPerson_setDT := :MedicalFormPerson_setDT,
					Lpu_id := :Lpu_id,
					Evn_id := :Evn_id,
					MedStaffFact_id := :MedStaffFact_id,
					pmUser_id := :pmUser_id
					)
			";
			$result = $this->queryResult($query, $queryParams);
			if (!empty($result) && empty($result[0]["Error_Code"]) && isset($result[0]['MedicalFormPerson_id'])) {
				$data['MedicalFormPerson_id'] = $result[0]['MedicalFormPerson_id'];
				$result[0]['PersonOnkoProfile_id'] = $result[0]['MedicalFormPerson_id'];
				$this->saveRECISTQuestionAnswer($data);	
				$SummaryResult_id = $this->calcSummaryResultAnket($data);
				if ($SummaryResult_id) {
					$data["MedicalFormDecision_id"] = $SummaryResult_id;
					$data["MedicalFormDecisionPerson_id"] = null;
					$this->saveSummaryResultAnket($data);
				}
			} else {
				throw new Exception($result[0]["Error_Message"]);
			}
		} else {
			throw new Exception("Не удалось передать идентификатор сотрудника");
		}
		return $result;
    }

    /**
     * сохранение ответов анкеты
     */
    public function saveRECISTQuestionAnswer($data) {
		foreach($data['QuestionAnswer'] as $row) {
			$MedicalFormData_id = $this->getFirstResultFromQuery("
				select MFD.MedicalFormData_id as \"MedicalFormData_id\"
				from v_MedicalFormData MFD
				where MFD.MedicalFormPerson_id = :MedicalFormPerson_id and MFD.MedicalFormQuestion_id = :MedicalFormQuestion_id
			", array(
				'MedicalFormPerson_id' => $data['MedicalFormPerson_id'],
				'MedicalFormQuestion_id' => $row[0]
			));
			$procedure = !$MedicalFormData_id ? 'p_MedicalFormData_ins' : 'p_MedicalFormData_upd';
			$query = "
				select
					MedicalFormData_id as \"MedicalFormData_id\",
					Error_Code as \"Error_Code\",
					Error_Message \"Error_Message\"
				from {$procedure} (
					MedicalFormData_id := :MedicalFormData_id,
					MedicalFormPerson_id := :MedicalFormPerson_id,
					MedicalFormQuestion_id := :MedicalFormQuestion_id,
					MedicalFormAnswers_id := :MedicalFormAnswers_id,
					MedicalFormData_ValueNumber := :MedicalFormData_ValueNumber,
					MedicalFormData_ValueText := :MedicalFormData_ValueText,
					MedicalFormData_ValueDT := :MedicalFormData_ValueDT,
					pmUser_id := :pmUser_id
					)
			";
			$this->queryResult($query, array(
				'MedicalFormData_id' => $MedicalFormData_id ? $MedicalFormData_id : null,
				'MedicalFormPerson_id' => $data['MedicalFormPerson_id'],
				'MedicalFormQuestion_id' => $row[0],
				'MedicalFormAnswers_id' => $row[1],
				'MedicalFormData_ValueNumber' => null,
				'MedicalFormData_ValueText' => null,
				'MedicalFormData_ValueDT' => null,
				'pmUser_id' => $data['pmUser_id']
			));
		}
    }

	/**
	 * Загрузка вариантов ответов на вопросы анкеты
	 */
    public function loadMedicalFormAnswers($data) {
		$query = "
		select
			MFA.MedicalFormAnswers_id as \"MedicalFormAnswers_id\",
			MFA.MedicalFormQuestion_id as \"MedicalFormQuestion_id\",
			MFA.MedicalFormAnswers_Name as \"MedicalFormAnswers_Name\"
		from dbo.MedicalFormAnswers MFA
		where MFA.MedicalFormQuestion_id = :OnkoQuestions_id
			and MFA.MedicalFormAnswers_delDT is null
		";
		return $this->queryResult($query, $data);
	}

	/**
	 * Расчет поля "Общий ответ" анкеты
	 */
	public function calcSummaryResultAnket($data) {
		$MatrixAnswers = $this->queryResult("
		select
			MFD.MedicalFormDecision_id as \"MedicalFormDecision_id\",
			(select	MFDA.MedicalFormAnswers_id as \"MatrixAnswer_id\"
			from
				v_MedicalFormDecisionAnswer MFDA
				join v_MedicalFormAnswers MFA on MFDA.MedicalFormAnswers_id = MFA.MedicalFormAnswers_id
			where MFDA.MedicalFormDecision_id = MFD.MedicalFormDecision_id
				and MFA.MedicalFormQuestion_id = ?
			) as \"First\",
			(select	MFDA.MedicalFormAnswers_id as \"MatrixAnswer_id\"
			from
				v_MedicalFormDecisionAnswer MFDA
				join v_MedicalFormAnswers MFA on MFDA.MedicalFormAnswers_id = MFA.MedicalFormAnswers_id
				where MFDA.MedicalFormDecision_id = MFD.MedicalFormDecision_id
				and MFA.MedicalFormQuestion_id = ?
			) as \"Second\",
			(select	MFDA.MedicalFormAnswers_id as \"MatrixAnswer_id\"
			from
				v_MedicalFormDecisionAnswer MFDA
				join v_MedicalFormAnswers MFA on MFDA.MedicalFormAnswers_id = MFA.MedicalFormAnswers_id
			where MFDA.MedicalFormDecision_id = MFD.MedicalFormDecision_id
				and MFA.MedicalFormQuestion_id = ?
			) as \"Third\"
			from v_MedicalFormDecision MFD",
			array($data["QuestionAnswer"][0][0],
				$data["QuestionAnswer"][1][0],
				$data["QuestionAnswer"][2][0]));
		$SummaryResult_id = null;
		foreach ($MatrixAnswers as $row) {
			if ($data["QuestionAnswer"][0][1] == $row["First"]
				&& $data["QuestionAnswer"][1][1] == $row["Second"]
				&& $data["QuestionAnswer"][2][1] ==  $row["Third"]) {
				$SummaryResult_id = $row["MedicalFormDecision_id"];
			}
		}
		return $SummaryResult_id;
	}
	
	/**
	 * Сохранение Общего ответа анкеты
	 */
	public function saveSummaryResultAnket($Params) {
		$query = "
			select
				MedicalFormDecisionPerson_id as \"MedicalFormDecisionPerson_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Message\"
			from p_MedicalFormDecisionPerson_ins (
				MedicalFormDecisionPerson_id := :MedicalFormDecisionPerson_id,
				MedicalFormDecision_id := :MedicalFormDecision_id,
				MedicalFormPerson_id := :MedicalFormPerson_id,
				pmUser_id := :pmUser_id
				)
		";
		return $this->queryResult($query, $Params);
	}
}