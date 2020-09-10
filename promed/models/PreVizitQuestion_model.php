<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * PreVizitQuestion_model - Предварительное анкетирование
 * сделано в режиме совместимомти с OnkoCtrl
 */
class PreVizitQuestion_model extends swModel {

    /**
     * Журнал анкет
     */
    
    public function GetOnkoCtrlProfileJurnal($data) {
		$filters = array();
		$filter = "(1=1)";
				
		$filter_join = '';
		$join = '';
		$queryParams = array();

		if (isset($data['Empty']) && $data['Empty']==1) {
			return array('data'=>array(),'totalCount'=>0);
		};

		if (isset($data['Lpu_id']))  {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
			//$filter .= " and msf.Lpu_id = :Lpu_id";
		}
		
		if (isset($data['SurName'])) {
			$filter .= " and ps.Person_SurName like :SurName";
			$queryParams['SurName'] = $data['SurName']."%";
		};
		
		if (isset($data['FirName'])) {
			$filter .= " and ps.Person_FirName like :FirName";
			$queryParams['FirName'] = $data['FirName']."%";
		};
		
		if (isset($data['SecName'])) {
			$filter .= " and ps.Person_SecName like :SecName";
			$queryParams['SecName'] = $data['SecName']."%";
		};

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
			$filter .= " and p.PreVizitQuestion_setDate  >= :PeriodRangeBegin";
			$queryParams['PeriodRangeBegin'] = $data['PeriodRange'][0];
		}
		
		if (isset($data['PeriodRange'][1])) {
			$filter .= " and p.PreVizitQuestion_setDate  <= :PeriodRangeEnd";
			$queryParams['PeriodRangeEnd'] = $data['PeriodRange'][1];
		}

		if (isset($data['Doctor'])) {
			$filter .= " and p.MedStaffFact_id = :Doctor";
			$queryParams['Doctor'] = $data['Doctor'];
		};

		if (isset($data['Sex_id'])) {
			$filter .= " and ps.Sex_id = :Sex_id";
			$queryParams['Sex_id'] = $data['Sex_id'];
		};
		
		if (isset($data['Uch'])) {
			if ($data['Uch'] == '0') {
				$filter .= " and (pcard.LpuRegion_id = '' or lpu.Lpu_id is null)";
			} else {
				$filter .= " and pcard.LpuRegion_id = :Uch";
				$queryParams['Uch'] = $data['Uch'];
			}
		};
   
		$sql = "
			SELECT  
			-- select
				p.PreVizitQuestion_id id
				,p.Person_id
				,p.PreVizitQuestion_id PersonOnkoProfile_id
				,ps.Person_SurName SurName
				,ps.Person_FirName FirName
				,ps.Person_SecName SecName
				,rtrim(rtrim(isnull(ps.Person_Surname, '')) + ' ' + rtrim(isnull(ps.Person_Firname, '')) + ' ' + rtrim(isnull(ps.Person_Secname, ''))) fio
				,convert(Varchar, ps.Person_BirthDay, 104) BirthDay
				,ps.Sex_id
				,substring(sex.Sex_Name, 1, 1) sex
				,isnull(uaddr.Address_Address,paddr.Address_Address) as Address
				,lpu.Lpu_id
				,lpu.Lpu_Nick
				,lr.LpuRegionType_Name + ' №' + lr.LpuRegion_Name uch
				,lr.LpuRegion_id
				,convert(Varchar, p.PreVizitQuestion_setDate, 104) PersonOnkoProfile_DtBeg
				,p.MedStaffFact_id
				,msf.Person_Fin as MedPersonal_fin
			-- end select
			FROM 
			-- from
				PreVizitQuestion p (nolock)
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = p.MedStaffFact_id
				inner join v_PersonState ps (nolock) on p.Person_id = ps.Person_id
				left join v_Sex sex (nolock) on sex.Sex_id = ps.Sex_id
				outer apply (
					select top 1
						pc.Person_id as PersonCard_Person_id,
						pc.Lpu_id,
						pc.LpuRegion_id
					from v_PersonCard pc (nolock)
					where
						pc.Person_id = ps.Person_id
						and LpuAttachType_id = 1
					order by PersonCard_begDate desc
				) as pcard
				left join v_Lpu lpu (nolock) on pcard.Lpu_id = lpu.Lpu_id
				left join v_LpuRegion lr (nolock) on pcard.LpuRegion_id = lr.LpuRegion_id
				left join v_Address uaddr with (nolock) on ps.UAddress_id = uaddr.Address_id
				left join v_Address paddr with (nolock) on ps.PAddress_id = paddr.Address_id
			-- end from  
			WHERE 
			-- where
				{$filter}
			-- end where        
			order by 
			-- order by
				p.PreVizitQuestion_setDate desc
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
				p.PreVizitQuestion_id as PersonOnkoProfile_id
				,p.Person_id
				,convert(Varchar, p.PreVizitQuestion_setDate, 104) PersonOnkoProfile_DtBeg
				,msf.Lpu_id
				,l.Lpu_Nick
				,msf.MedStaffFact_id
				,msf.LpuBuilding_id
				,msf.LpuSection_id
			FROM PreVizitQuestion p WITH (NOLOCK)
				left join v_MedStaffFact msf WITH (NOLOCK) on msf.MedStaffFact_id = p.MedStaffFact_id
				left join v_Lpu l WITH (NOLOCK) on l.Lpu_id = msf.lpu_id
				where p.Person_id = :Person_id -- на одного человека может быть только одна анкета
		";

        $queryParams['Person_id'] = $data['Person_id'];
		
		return $this->queryResult($query, $queryParams);
    }

	
    /**
	 * Получение списка вопросов для анкеты
     */
    public function getOnkoQuestions($data) {
        $sql = "
			declare @PersonAgeGroup_id bigint = (
				select top 1 case when dbo.Age2(Person_Birthday, dbo.tzGetDate()) >= 18 then 1 else 2 end as PersonAgeGroup_id
				from v_PersonState (nolock)
				where Person_id = :Person_id
			);
            SELECT 
				q.PreVizitQuestionType_id as OnkoQuestions_id,
				q.PreVizitQuestionType_Name as OnkoQuestions_Name,
				q.PreVizitQuestionType_Num as Questions_Num,
				case 
					when AnswerType_Code = 2 then p.PreVizitQuestionAnswer_FreeForm
					else cast(isnull(p.AnswerYesNoType_id, 1) as varchar)
				end as val,
				p.PreVizitQuestionAnswer_FreeForm as FreeForm,
				at.AnswerType_Code,
				ac.AnswerClass_SysNick,
				q.PreVizitQuestionType_pid as OnkoQuestions_pid,
				q.QuestionKind_id
            FROM PreVizitQuestionType q WITH (NOLOCK)
				left join PreVizitQuestionAnswer p WITH (NOLOCK) on 
					p.PreVizitQuestionType_id = q.PreVizitQuestionType_id and 
					p.PreVizitQuestion_id = :PreVizitQuestion_id
				left join v_AnswerType at with(nolock) on at.AnswerType_id = q.AnswerType_id
				left join v_AnswerClass ac with(nolock) on ac.AnswerClass_id = q.AnswerClass_id
			where 
				q.PersonAgeGroup_code = @PersonAgeGroup_id
			order by 
				q.PreVizitQuestionType_Code";

        $queryParams['PreVizitQuestion_id'] = $data['PersonOnkoProfile_id'];
        $queryParams['Person_id'] = $data['Person_id'];
		
		return $this->queryResult($sql, $queryParams);
    }
	

    /**
     * сохранение информации об анкетировании пациента
     */
    public function savePersonOnkoProfile($data) {

		$procedure = empty($data['PersonOnkoProfile_id']) ? 'p_PreVizitQuestion_ins' : 'p_PreVizitQuestion_upd';

		$queryParams = array();
		$query = "
			declare
				@PreVizitQuestion_id bigint = :PreVizitQuestion_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$procedure}
				@PreVizitQuestion_id = @PreVizitQuestion_id output,
				@Person_id = :Person_id,
				@PreVizitQuestion_setDate = :PreVizitQuestion_setDate,
				@MedStaffFact_id = :MedStaffFact_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @PreVizitQuestion_id as PreVizitQuestion_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

        $queryParams['PreVizitQuestion_id'] = $data['PersonOnkoProfile_id'];
        $queryParams['Person_id'] = $data['Person_id'];
        $queryParams['PreVizitQuestion_setDate'] = $data['Profile_Date'];
        $queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
        $queryParams['pmUser_id'] = $data['pmUser_id'];
		
        $result = $this->queryResult($query, $queryParams);
		
		if (count($result) && isset($result[0]['PreVizitQuestion_id'])) {
			$data['PreVizitQuestion_id'] = $result[0]['PreVizitQuestion_id'];
			$result[0]['PersonOnkoProfile_id'] = $result[0]['PreVizitQuestion_id'];
			$this->savePreVizitQuestionAnswer($data);
		}
		
		return $result;
    }

    /**
     * сохранение ответов
     */
    public function savePreVizitQuestionAnswer($data) {
		
		foreach($data['QuestionAnswer'] as $row) {
			$PreVizitQuestionAnswer_id = $this->getFirstResultFromQuery("
				select PreVizitQuestionAnswer_id 
				from PreVizitQuestionAnswer (nolock) 
				where PreVizitQuestion_id = :PreVizitQuestion_id and PreVizitQuestionType_id = :PreVizitQuestionType_id
			", array(
				'PreVizitQuestion_id' => $data['PreVizitQuestion_id'],
				'PreVizitQuestionType_id' => $row[0]
			));
			
			$AnswerType_id = $this->getFirstResultFromQuery("
				select AnswerType_id from PreVizitQuestionType (nolock) where PreVizitQuestionType_id = :PreVizitQuestionType_id
			", array('PreVizitQuestionType_id' => $row[0]));

			$procedure = !$PreVizitQuestionAnswer_id ? 'p_PreVizitQuestionAnswer_ins' : 'p_PreVizitQuestionAnswer_upd';
			
			$query = "
				declare
					@PreVizitQuestionAnswer_id bigint = :PreVizitQuestionAnswer_id,
					@Error_Code bigint,
					@Error_Message varchar(4000);
				exec {$procedure}
					@PreVizitQuestionAnswer_id = @PreVizitQuestionAnswer_id output,
					@PreVizitQuestion_id = :PreVizitQuestion_id,
					@PreVizitQuestionType_id = :PreVizitQuestionType_id,
					@AnswerYesNoType_id = :AnswerYesNoType_id,
					@PreVizitQuestionAnswer_FreeForm = :PreVizitQuestionAnswer_FreeForm,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select @PreVizitQuestionAnswer_id as PreVizitQuestionAnswer_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			";
			
			$this->queryResult($query, array(
				'PreVizitQuestionAnswer_id' => $PreVizitQuestionAnswer_id ? $PreVizitQuestionAnswer_id : null,
				'PreVizitQuestion_id' => $data['PreVizitQuestion_id'],
				'PreVizitQuestionType_id' => $row[0],
				'AnswerYesNoType_id' => $row[1] == 1 ? 2 : 1,
				'PreVizitQuestionAnswer_FreeForm' => $AnswerType_id == 2 ? $row[1] : $row[2],
				'pmUser_id' => $data['pmUser_id']
			));
		}
	}

    /**
     * Удаление анкеты
     */
    public function deleteOnkoProfile($data) {
		$tmp = $this->queryList("select PreVizitQuestionAnswer_id from PreVizitQuestionAnswer (nolock) where PreVizitQuestion_id = ?", array($data['PersonOnkoProfile_id']));
		foreach($tmp as $pqa) {			
			$this->db->query("
				declare
					@Error_Code int,
					@Error_Msg varchar(4000);

				exec p_PreVizitQuestionAnswer_del
					@PreVizitQuestionAnswer_id = ?,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Msg output;

				select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
			", array($pqa));
		}
		
		$query = "
			declare
				@Error_Code int,
				@Error_Msg varchar(4000);

			exec p_PreVizitQuestion_del
				@PreVizitQuestion_id = :PersonOnkoProfile_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;

			select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";
		
		return $this->queryResult($query, $data);
    }
}