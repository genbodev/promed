<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * PreVizitQuestion_model - Предварительное анкетирование
 * сделано в режиме совместимомти с OnkoCtrl
 */
class PreVizitQuestion_model extends SwPgModel {

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
			$filter .= " and ps.Person_SurName ilike :SurName";
			$queryParams['SurName'] = $data['SurName']."%";
		};
		
		if (isset($data['FirName'])) {
			$filter .= " and ps.Person_FirName ilike :FirName";
			$queryParams['FirName'] = $data['FirName']."%";
		};
		
		if (isset($data['SecName'])) {
			$filter .= " and ps.Person_SecName ilike :SecName";
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
				$filter .= " and (pcard.LpuRegion_id = 0 or lpu.Lpu_id is null)";
			} else {
				$filter .= " and pcard.LpuRegion_id = :Uch";
				$queryParams['Uch'] = $data['Uch'];
			}
		};
   
		$sql = "
			SELECT  
			-- select
				p.PreVizitQuestion_id as \"id\"
				,p.Person_id as \"Person_id\"
				,p.PreVizitQuestion_id as \"PersonOnkoProfile_id\"
				,ps.Person_SurName as \"SurName\"
				,ps.Person_FirName as \"FirName\"
				,ps.Person_SecName as \"SecName\"
				,rtrim(rtrim(coalesce(ps.Person_Surname, '')) || ' ' || rtrim(coalesce(ps.Person_Firname, '')) || ' ' || rtrim(coalesce(ps.Person_Secname, ''))) as \"fio\"
				,to_char (ps.Person_BirthDay, 'dd.mm.yyyy') as \"BirthDay\"
				,ps.Sex_id as \"Sex_id\"
				,substring(sex.Sex_Name, 1, 1) as \"sex\"
				,coalesce(uaddr.Address_Address,paddr.Address_Address) as \"Address\"
				,lpu.Lpu_id as \"Lpu_id\"
				,lpu.Lpu_Nick as \"Lpu_Nick\"
				,lr.LpuRegionType_Name || ' №' || lr.LpuRegion_Name as \"uch\"
				,lr.LpuRegion_id as \"LpuRegion_id\"
				,to_char (p.PreVizitQuestion_setDate, 'dd.mm.yyyy') as \"PersonOnkoProfile_DtBeg\"
				,p.MedStaffFact_id as \"MedStaffFact_id\"
				,msf.Person_Fin as \"MedPersonal_fin\"
			-- end select
			FROM 
			-- from
				PreVizitQuestion p
				left join v_MedStaffFact msf on msf.MedStaffFact_id = p.MedStaffFact_id
				inner join v_PersonState ps on p.Person_id = ps.Person_id
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
				p.PreVizitQuestion_id as \"PersonOnkoProfile_id\"
				,p.Person_id as \"Person_id\"
				,to_char (p.PreVizitQuestion_setDate, 'dd.mm.yyyy') as \"PersonOnkoProfile_DtBeg\"
				,msf.Lpu_id as \"Lpu_id\"
				,l.Lpu_Nick as \"Lpu_Nick\"
				,msf.MedStaffFact_id as \"MedStaffFact_id\"
				,msf.LpuBuilding_id as \"LpuBuilding_id\"
				,msf.LpuSection_id as \"LpuSection_id\"
			FROM PreVizitQuestion p
				left join v_MedStaffFact msf on msf.MedStaffFact_id = p.MedStaffFact_id
				left join v_Lpu l on l.Lpu_id = msf.lpu_id
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
			with PersonAgeGroup_id as (
			    select
			        case when dbo.Age2(Person_Birthday, dbo.tzGetDate()) >= 18 then 1 else 2 end as PersonAgeGroup_id
				from
				    v_PersonState
				where
				    Person_id = :Person_id
				limit 1
			)
			
            SELECT 
				q.PreVizitQuestionType_id as \"OnkoQuestions_id\",
				q.PreVizitQuestionType_Name as \"OnkoQuestions_Name\",
				q.PreVizitQuestionType_Num as \"Questions_Num\",
				case 
					when AnswerType_Code = 2 then p.PreVizitQuestionAnswer_FreeForm
					else cast(coalesce(p.AnswerYesNoType_id, 1) as varchar)
				end as \"val\",
				p.PreVizitQuestionAnswer_FreeForm as \"FreeForm\",
				at.AnswerType_Code as \"AnswerType_Code\",
				ac.AnswerClass_SysNick as \"AnswerClass_SysNick\",
				q.PreVizitQuestionType_pid as \"OnkoQuestions_pid\",
				q.QuestionKind_id as \"QuestionKind_id\"
            FROM PreVizitQuestionType q
				left join PreVizitQuestionAnswer p on 
					p.PreVizitQuestionType_id = q.PreVizitQuestionType_id and 
					p.PreVizitQuestion_id = :PreVizitQuestion_id
				left join v_AnswerType at on at.AnswerType_id = q.AnswerType_id
				left join v_AnswerClass ac on ac.AnswerClass_id = q.AnswerClass_id
			where 
				q.PersonAgeGroup_code = (select PersonAgeGroup_id from PersonAgeGroup_id)
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
            select
                PreVizitQuestion_id as \"PreVizitQuestion_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from {$procedure} (
				PreVizitQuestion_id := :PreVizitQuestion_id,
				Person_id := :Person_id,
				PreVizitQuestion_setDate := :PreVizitQuestion_setDate,
				MedStaffFact_id := :MedStaffFact_id,
				pmUser_id := :pmUser_id
				)
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
				select PreVizitQuestionAnswer_id as \"PreVizitQuestionAnswer_id\" 
				from PreVizitQuestionAnswer
				where PreVizitQuestion_id = :PreVizitQuestion_id and PreVizitQuestionType_id = :PreVizitQuestionType_id
			", array(
				'PreVizitQuestion_id' => $data['PreVizitQuestion_id'],
				'PreVizitQuestionType_id' => $row[0]
			));
			
			$AnswerType_id = $this->getFirstResultFromQuery("
				select AnswerType_id as \"AnswerType_id\" from PreVizitQuestionType where PreVizitQuestionType_id = :PreVizitQuestionType_id
			", array('PreVizitQuestionType_id' => $row[0]));

			$procedure = !$PreVizitQuestionAnswer_id ? 'p_PreVizitQuestionAnswer_ins' : 'p_PreVizitQuestionAnswer_upd';
			
			$query = "
                select
                    PreVizitQuestionAnswer_id as \"PreVizitQuestionAnswer_id\", 
                    Error_Code as \"Error_Code\", 
                    Error_Message as \"Error_Msg\"
				from {$procedure} (
					PreVizitQuestionAnswer_id := :PreVizitQuestionAnswer_id,
					PreVizitQuestion_id := :PreVizitQuestion_id,
					PreVizitQuestionType_id := :PreVizitQuestionType_id,
					AnswerYesNoType_id := :AnswerYesNoType_id,
					PreVizitQuestionAnswer_FreeForm := :PreVizitQuestionAnswer_FreeForm,
					pmUser_id := :pmUser_id
					)
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
		$tmp = $this->queryList("select PreVizitQuestionAnswer_id as \"PreVizitQuestionAnswer_id\" from PreVizitQuestionAnswer where PreVizitQuestion_id = ?", array($data['PersonOnkoProfile_id']));
		foreach($tmp as $pqa) {			
			$this->db->query("
                select
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\"
				from p_PreVizitQuestionAnswer_del (
					PreVizitQuestionAnswer_id := ?
					)
			", array($pqa));
		}
		
		$query = "
            select 
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from p_PreVizitQuestion_del (
				PreVizitQuestion_id := :PersonOnkoProfile_id
				)
		";
		
		return $this->queryResult($query, $data);
    }
}