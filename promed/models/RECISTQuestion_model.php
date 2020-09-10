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
				MFP.MedicalFormPerson_id id,
				MFP.Person_id,
				MFP.MedicalFormPerson_id PersonOnkoProfile_id,
				ps.Person_SurName SurName,
				ps.Person_FirName FirName,
				ps.Person_SecName SecName,
				rtrim(rtrim(isnull(ps.Person_Surname, '')) + ' ' + rtrim(isnull(ps.Person_Firname, '')) + ' ' + rtrim(isnull(ps.Person_Secname, ''))) fio,
				convert(Varchar, ps.Person_BirthDay, 104) BirthDay,
				ps.Sex_id,
				substring(sex.Sex_Name, 1, 1) sex,
				isnull(uaddr.Address_Address,paddr.Address_Address) as Address,
				lpu.Lpu_id,
				lpu.Lpu_Nick,
				lr.LpuRegionType_Name + ' №' + lr.LpuRegion_Name uch,
				lr.LpuRegion_id,
				convert(Varchar, MFP.MedicalFormPerson_setDT, 104) PersonOnkoProfile_DtBeg,
				MFP.MedStaffFact_id
			-- end select
			FROM 
			-- from
				v_MedicalFormPerson MFP with(nolock)
				left join v_MedStaffFact msf with(nolock) on msf.MedStaffFact_id = MFP.MedStaffFact_id
				inner join v_PersonState ps with(nolock) on MFP.Person_id = ps.Person_id
				left join v_Sex sex with(nolock) on sex.Sex_id = ps.Sex_id
				outer apply (
					select top 1
						pc.Person_id as PersonCard_Person_id,
						pc.Lpu_id,
						pc.LpuRegion_id
					from v_PersonCard pc with(nolock)
					where
						pc.Person_id = ps.Person_id
						and LpuAttachType_id = 1
					order by PersonCard_begDate desc
				) as pcard
				left join v_Lpu lpu with(nolock) on pcard.Lpu_id = lpu.Lpu_id
				left join v_LpuRegion lr with(nolock) on pcard.LpuRegion_id = lr.LpuRegion_id
				left join v_Address uaddr with(nolock) on ps.UAddress_id = uaddr.Address_id
				left join v_Address paddr with(nolock) on ps.PAddress_id = paddr.Address_id
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
				MFP.MedicalFormPerson_id as PersonOnkoProfile_id,
				MFP.Person_id,
				convert(Varchar, MFP.MedicalFormPerson_setDT, 104) as PersonOnkoProfile_DtBeg,
				msf.Lpu_id,
				l.Lpu_Nick,
				msf.MedStaffFact_id,
				msf.LpuBuilding_id,
				msf.LpuSection_id,
				MFD.MedicalFormDecision_Name as ResultRECIST_id
			FROM v_MedicalFormPerson MFP WITH (NOLOCK)
				left join v_MedicalFormDecisionPerson MFDP WITH (NOLOCK) on MFDP.MedicalFormPerson_id = MFP.MedicalFormPerson_id
				left join v_MedicalFormDecision MFD WITH (NOLOCK) on MFD.MedicalFormDecision_id = MFDP.MedicalFormDecision_id
				left join v_MedStaffFact msf WITH (NOLOCK) on msf.MedStaffFact_id = MFP.MedStaffFact_id
				left join v_Lpu l WITH (NOLOCK) on l.Lpu_id = msf.lpu_id
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
			MFQ.MedicalFormQuestion_id as OnkoQuestions_id,
			MFQ.MedicalFormQuestion_Name as OnkoQuestions_Name,
			isnull(MFD.MedicalFormAnswers_id, 1) as val,
			'MedicalFormAnswers' as AnswerClass_SysNick,
			3 as AnswerType_Code
		from
			dbo.MedicalFormQuestion MFQ with(nolock)
			left join v_MedicalFormData MFD WITH (nolock) on 
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
			select MedStaffFact_id from v_MedStaffFact with (nolock)
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
				declare
					@MedicalFormPerson_id bigint = :MedicalFormPerson_id,
					@Error_Code bigint,
					@Error_Message varchar(4000);
				exec {$procedure}
					@MedicalFormPerson_id = @MedicalFormPerson_id output,
					@MedicalForm_id = :MedicalForm_id,
					@Person_id = :Person_id,
					@MedicalFormPerson_setDT = :MedicalFormPerson_setDT,
					@Lpu_id = :Lpu_id,
					@Evn_id = :Evn_id,
					@MedStaffFact_id = :MedStaffFact_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select @MedicalFormPerson_id as MedicalFormPerson_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
				select MFD.MedicalFormData_id 
				from v_MedicalFormData MFD with (nolock) 
				where MFD.MedicalFormPerson_id = :MedicalFormPerson_id and MFD.MedicalFormQuestion_id = :MedicalFormQuestion_id
			", array(
				'MedicalFormPerson_id' => $data['MedicalFormPerson_id'],
				'MedicalFormQuestion_id' => $row[0]
			));
			$procedure = !$MedicalFormData_id ? 'p_MedicalFormData_ins' : 'p_MedicalFormData_upd';
			$query = "
				declare
					@MedicalFormData_id bigint = :MedicalFormData_id,
					@Error_Code bigint,
					@Error_Message varchar(4000);
				exec {$procedure}
					@MedicalFormData_id = @MedicalFormData_id output,
					@MedicalFormPerson_id = :MedicalFormPerson_id,
					@MedicalFormQuestion_id = :MedicalFormQuestion_id,
					@MedicalFormAnswers_id = :MedicalFormAnswers_id,
					@MedicalFormData_ValueNumber = :MedicalFormData_ValueNumber,
					@MedicalFormData_ValueText = :MedicalFormData_ValueText,
					@MedicalFormData_ValueDT = :MedicalFormData_ValueDT,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select @MedicalFormData_id as MedicalFormData_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
			MFA.MedicalFormAnswers_id as MedicalFormAnswers_id,
			MFA.MedicalFormQuestion_id as MedicalFormQuestion_id,
			MFA.MedicalFormAnswers_Name as MedicalFormAnswers_Name
		from dbo.MedicalFormAnswers MFA with (nolock)
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
			MFD.MedicalFormDecision_id,
			(select	MFDA.MedicalFormAnswers_id as MatrixAnswer_id
			from
				v_MedicalFormDecisionAnswer MFDA with (nolock)
				join v_MedicalFormAnswers MFA with (nolock) on MFDA.MedicalFormAnswers_id = MFA.MedicalFormAnswers_id
			where MFDA.MedicalFormDecision_id = MFD.MedicalFormDecision_id
				and MFA.MedicalFormQuestion_id = ?
			) as First,
			(select	MFDA.MedicalFormAnswers_id as MatrixAnswer_id
			from
				v_MedicalFormDecisionAnswer MFDA with (nolock)
				join v_MedicalFormAnswers MFA with (nolock) on MFDA.MedicalFormAnswers_id = MFA.MedicalFormAnswers_id
				where MFDA.MedicalFormDecision_id = MFD.MedicalFormDecision_id
				and MFA.MedicalFormQuestion_id = ?
			) as Second,
			(select	MFDA.MedicalFormAnswers_id as MatrixAnswer_id
			from
				v_MedicalFormDecisionAnswer MFDA with (nolock)
				join v_MedicalFormAnswers MFA with (nolock) on MFDA.MedicalFormAnswers_id = MFA.MedicalFormAnswers_id
			where MFDA.MedicalFormDecision_id = MFD.MedicalFormDecision_id
				and MFA.MedicalFormQuestion_id = ?
			) as Third
			from v_MedicalFormDecision MFD with (nolock)",
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
			declare
				@MedicalFormDecisionPerson_id bigint = :MedicalFormDecisionPerson_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_MedicalFormDecisionPerson_ins
				@MedicalFormDecisionPerson_id = @MedicalFormDecisionPerson_id output,
				@MedicalFormDecision_id = :MedicalFormDecision_id,
				@MedicalFormPerson_id = :MedicalFormPerson_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @MedicalFormDecisionPerson_id as MedicalFormDecisionPerson_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		return $this->queryResult($query, $Params);
	}
}