<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * PalliatQuestion_model - палллиативка
 * сделано в режиме совместимомти с OnkoCtrl
 */
class PalliatQuestion_model extends swModel {

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
			$filter .= " and p.Lpu_id = :Lpu_id";
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
			$filter .= " and p.PalliatQuestion_setDate  >= :PeriodRangeBegin";
			$queryParams['PeriodRangeBegin'] = $data['PeriodRange'][0];
		}
		
		if (isset($data['PeriodRange'][1])) {
			$filter .= " and p.PalliatQuestion_setDate  <= :PeriodRangeEnd";
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
				p.PalliatQuestion_id id
				,p.Person_id
				,p.PalliatQuestion_id PersonOnkoProfile_id
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
				,convert(Varchar, p.PalliatQuestion_setDate, 104) PersonOnkoProfile_DtBeg
				,p.MedStaffFact_id
			-- end select
			FROM 
			-- from
				PalliatQuestion p (nolock)
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
				p.PalliatQuestion_setDate desc
			-- end order by  
		";
					
		return $this->getPagingResponse($sql, $queryParams, $data['start'], $data['limit'], true);
    }

    /**
     * Загрузка доп инфы для формы анкетирования
     */
    public function loadOnkoContrProfileFormInfo($data) {
        $queryParams = array();
        $filter = '';

        if (!empty($data['PersonOnkoProfile_id'])) {
            $filter = ' and PalliatQuestion_id = :PersonOnkoProfile_id ';
        }
		
		$query = "
			SELECT 
				p.PalliatQuestion_id as PersonOnkoProfile_id
				,p.Person_id
				,convert(Varchar, p.PalliatQuestion_setDate, 104) PersonOnkoProfile_DtBeg
				,p.Lpu_id
				,l.Lpu_Nick
				,p.MedStaffFact_id
				,msf.LpuBuilding_id
				,msf.LpuSection_id
				,p.PalliatQuestion_Other
				,p.PalliatQuestion_CountYes
				,p.PalliatPPSScale_id
				,p.PalliatPainScale_id
			FROM PalliatQuestion p WITH (NOLOCK)
				left join v_MedStaffFact msf WITH (NOLOCK) on msf.MedStaffFact_id = p.MedStaffFact_id
				left join v_Lpu l WITH (NOLOCK) on l.Lpu_id = p.lpu_id
				where p.Person_id = :Person_id
				{$filter} 
		";

        $queryParams['Person_id'] = $data['Person_id'];
        $queryParams['PersonOnkoProfile_id'] = $data['PersonOnkoProfile_id'];
		
		return $this->queryResult($query, $queryParams);
    }

	
    /**
	 * Получение списка вопросов для анкеты
     */
    public function getOnkoQuestions($data) {
        $sql = '
			declare 
				@curdate datetime = dbo.tzGetDate();
            select 
				q.PalliatQuestionType_id as OnkoQuestions_id,
				q.PalliatQuestionType_Name as OnkoQuestions_Name,
				q.PalliatQuestionType_Num as Questions_Num,
				case 
					when AnswerType_Code = 2 then p.PalliatQuestionAnswer_FreeForm
					when AnswerClass_Code = 1 then cast(isnull(p.AnswerYesNoType_id, 1) as varchar)
					else cast(p.PalliatQuestionAnswer_AnswerId as varchar)
				end as val,
				p.PalliatQuestionAnswer_FreeForm as FreeForm,
				at.AnswerType_Code,
				ac.AnswerClass_SysNick,
				q.PalliatQuestionType_IsRequired as IsRequired
            from v_PalliatQuestionType q with(nolock)
				left join PalliatQuestionAnswer p with(nolock) on p.PalliatQuestionType_id = q.PalliatQuestionType_id and p.PalliatQuestion_id = :PalliatQuestion_id
				left join v_AnswerType at with(nolock) on at.AnswerType_id = q.AnswerType_id
				left join v_AnswerClass ac with(nolock) on ac.AnswerClass_id = q.AnswerClass_id
			where
				isnull(q.PalliatQuestionType_endDate, @curdate) >= @curdate
			order by 
				case when ac.AnswerClass_Code = 1 or AnswerType_Code is null then 1 else 0 end, 
				q.PalliatQuestionType_Code';

        $queryParams['PalliatQuestion_id'] = $data['PersonOnkoProfile_id'];

		return $this->queryResult($sql, $queryParams);
    }
	

    /**
     * сохранение информации об анкетировании пациента
     */
    public function savePersonOnkoProfile($data) {

		$procedure = empty($data['PersonOnkoProfile_id']) ? 'p_PalliatQuestion_ins' : 'p_PalliatQuestion_upd';
		
		$PalliatQuestion_CountYes = 0;
		
		foreach($data['QuestionAnswer'] as $row) {
			if ($row[1] == 1) $PalliatQuestion_CountYes++;
		}
		
		if (!empty($data['PalliatQuestion_Other'])) $PalliatQuestion_CountYes++;

		$queryParams = array();
		$query = "
			declare
				@PalliatQuestion_id bigint = :PalliatQuestion_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$procedure}
				@PalliatQuestion_id = @PalliatQuestion_id output,
				@Person_id = :Person_id,
				@PalliatQuestion_setDate = :PalliatQuestion_setDate,
				@MedStaffFact_id = :MedStaffFact_id,
				@PalliatQuestion_Other = :PalliatQuestion_Other,
				@PalliatQuestion_CountYes = :PalliatQuestion_CountYes,
				@PersonRegisterType_id = :PersonRegisterType_id,
				@PalliatPPSScale_id = :PalliatPPSScale_id,
				@PalliatPainScale_id = :PalliatPainScale_id,
				@Lpu_id = :Lpu_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @PalliatQuestion_id as PalliatQuestion_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

        $queryParams['PalliatQuestion_id'] = $data['PersonOnkoProfile_id'];
        $queryParams['Person_id'] = $data['Person_id'];
        $queryParams['PalliatQuestion_setDate'] = $data['Profile_Date'];
        $queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
        $queryParams['PalliatQuestion_Other'] = $data['PalliatQuestion_Other'];
        $queryParams['PalliatQuestion_CountYes'] = $PalliatQuestion_CountYes;
        $queryParams['PersonRegisterType_id'] = null;
        $queryParams['PalliatPPSScale_id'] = $data['PalliatPPSScale_id'];
        $queryParams['PalliatPainScale_id'] = $data['PalliatPainScale_id'];
		$queryParams['Lpu_id'] = $data['Lpu_id'];
        $queryParams['pmUser_id'] = $data['pmUser_id'];

        $result = $this->queryResult($query, $queryParams);
		
		if (count($result) && isset($result[0]['PalliatQuestion_id'])) {
			$data['PalliatQuestion_id'] = $result[0]['PalliatQuestion_id'];
			$result[0]['PersonOnkoProfile_id'] = $result[0]['PalliatQuestion_id'];
			$this->savePalliatQuestionAnswer($data);
		}
		
		return $result;
    }

    /**
     * сохранение ответов
     */
    public function savePalliatQuestionAnswer($data) {
		
		foreach($data['QuestionAnswer'] as $row) {
			$PalliatQuestionAnswer_id = $this->getFirstResultFromQuery("
				select PalliatQuestionAnswer_id 
				from PalliatQuestionAnswer (nolock) 
				where PalliatQuestion_id = :PalliatQuestion_id and PalliatQuestionType_id = :PalliatQuestionType_id
			", array(
				'PalliatQuestion_id' => $data['PalliatQuestion_id'],
				'PalliatQuestionType_id' => $row[0]
			));
			
			$AnswerType_id = $this->getFirstResultFromQuery("
				select AnswerType_id from PalliatQuestionType (nolock) where PalliatQuestionType_id = :PalliatQuestionType_id
			", array('PalliatQuestionType_id' => $row[0]));

			$procedure = !$PalliatQuestionAnswer_id ? 'p_PalliatQuestionAnswer_ins' : 'p_PalliatQuestionAnswer_upd';
			
			$query = "
				declare
					@PalliatQuestionAnswer_id bigint = :PalliatQuestionAnswer_id,
					@Error_Code bigint,
					@Error_Message varchar(4000);
				exec {$procedure}
					@PalliatQuestionAnswer_id = @PalliatQuestionAnswer_id output,
					@PalliatQuestion_id = :PalliatQuestion_id,
					@PalliatQuestionType_id = :PalliatQuestionType_id,
					@AnswerYesNoType_id = :AnswerYesNoType_id,
					@PalliatQuestionAnswer_FreeForm = :PalliatQuestionAnswer_FreeForm,
					@PalliatQuestionAnswer_AnswerId = :PalliatQuestionAnswer_AnswerId,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select @PalliatQuestionAnswer_id as PalliatQuestionAnswer_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			";
			
			$this->queryResult($query, array(
				'PalliatQuestionAnswer_id' => $PalliatQuestionAnswer_id ? $PalliatQuestionAnswer_id : null,
				'PalliatQuestion_id' => $data['PalliatQuestion_id'],
				'PalliatQuestionType_id' => $row[0],
				'AnswerYesNoType_id' => $row[1] == 1 ? 2 : 1,
				'PalliatQuestionAnswer_FreeForm' => $AnswerType_id == 2 ? $row[1] : $row[2],
				'PalliatQuestionAnswer_AnswerId' => $AnswerType_id == 2 ? null : $row[1],
				'pmUser_id' => $data['pmUser_id']
			));
		}
	}

    /**
     * Удаление анкеты
     */
    public function deleteOnkoProfile($data) {
		$tmp = $this->queryList("select PalliatQuestionAnswer_id from PalliatQuestionAnswer (nolock) where PalliatQuestion_id = ?", array($data['PersonOnkoProfile_id']));
		foreach($tmp as $pqa) {			
			$this->db->query("
				declare
					@Error_Code int,
					@Error_Msg varchar(4000);

				exec p_PalliatQuestionAnswer_del
					@PalliatQuestionAnswer_id = ?,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Msg output;

				select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
			", array($pqa));
		}
		
		$query = "
			declare
				@Error_Code int,
				@Error_Msg varchar(4000);

			exec p_PalliatQuestion_del
				@PalliatQuestion_id = :PersonOnkoProfile_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;

			select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";
		
		return $this->queryResult($query, $data);
    }

    /**
     * loadPalliatPPSScale
     */
    public function loadPalliatPPSScale() {
	
		$query = "select * from v_PalliatPPSScale (nolock)";
		return $this->queryResult($query);
    }

    /**
     * PalliatPainScale
     */
    public function loadPalliatPainScale() {
	
		$query = "select * from v_PalliatPainScale (nolock)";
		return $this->queryResult($query);
    }
}