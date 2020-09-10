<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * BIRADSQuestion_model - BI-RADS
 * сделано в режиме совместимости с OnkoCtrl
 */
class BIRADSQuestion_model extends SwPgModel {

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
			$filter .= " and p.BIRADSQuestion_setDate  >= :PeriodRangeBegin";
			$queryParams['PeriodRangeBegin'] = $data['PeriodRange'][0];
		}
		
		if (isset($data['PeriodRange'][1])) {
			$filter .= " and p.BIRADSQuestion_setDate  <= :PeriodRangeEnd";
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

		if (!empty($data['CategoryBIRADS_id'])) {
			$filter .= " and p.CategoryBIRADS_id in({$data['CategoryBIRADS_id']}) ";
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
				p.BIRADSQuestion_id as \"id\"
				,p.Person_id as \"Person_id\"
				,p.BIRADSQuestion_id as \"PersonOnkoProfile_id\"
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
				,to_char (p.BIRADSQuestion_setDate, 'dd.mm.yyyy') as \"PersonOnkoProfile_DtBeg\"
				,p.MedStaffFact_id as \"MedStaffFact_id\"
				,cbr.CategoryBIRADS_id as \"CategoryBIRADS_id\"
				,cbr.CategoryBIRADS_Name as \"CategoryBIRADS_Name\"
			-- end select
			FROM 
			-- from
				BIRADSQuestion p
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
				left join CategoryBIRADS cbr on cbr.CategoryBIRADS_id = p.CategoryBIRADS_id
			-- end from  
			WHERE 
			-- where
				{$filter}
			-- end where        
			order by 
			-- order by
				p.BIRADSQuestion_setDate desc
			-- end order by  
		";
				
		return $this->getPagingResponse($sql, $queryParams, $data['start'], $data['limit'], true);
    }

    /**
     * Загрузка доп инфы для формы анкетирования
     */
    public function loadOnkoContrProfileFormInfo($data) {
        $queryParams = [];
      
        $query = "
			SELECT 
				p.BIRADSQuestion_id as \"PersonOnkoProfile_id\",
				p.Person_id as \"Person_id\",
				to_char(p.BIRADSQuestion_setDate, 'dd.mm.yyyy') as \"PersonOnkoProfile_DtBeg\",
				msf.Lpu_id as \"Lpu_id\",
				l.Lpu_Nick as \"Lpu_Nick\",
				msf.MedStaffFact_id as \"MedStaffFact_id\",
				msf.LpuBuilding_id as \"LpuBuilding_id\",
				msf.LpuSection_id as \"LpuSection_id\",
				p.CategoryBIRADS_id as \"CategoryBIRADS_id\",
				ed.EvnDirection_id as \"EvnDirection_id\",
				QUL.EvnUslugaPar_id as \"EvnUslugaPar_id\"
			FROM BIRADSQuestion p
				left join v_MedStaffFact msf on msf.MedStaffFact_id = p.MedStaffFact_id
				left join v_Lpu l on l.Lpu_id = msf.lpu_id
				left join BIRADSQuestionUslugaLink QUL on QUL.BIRADSQuestion_id = p.BIRADSQuestion_id
				left join EvnUslugaPar EUP on QUL.EvnUslugaPar_id = EUP.EvnUslugaPar_id
				left join EvnUsluga EU on EU.EvnUsluga_id = Eup.EvnUsluga_id
				left join EvnDirection ED on ED.EvnDirection_id = EU.EvnDirection_id
            where
                p.Person_id = :Person_id
            and
                (QUL.EvnUslugaPar_id = :EvnUslugaPar_id or p.BIRADSQuestion_id = :PersonOnkoProfile_id)
		";

        $queryParams['Person_id'] = $data['Person_id'];
        $queryParams['PersonOnkoProfile_id'] = $data['PersonOnkoProfile_id'];
        $queryParams['EvnUslugaPar_id'] = $data['EvnUslugaPar_id'];
        
		return $this->queryResult($query, $queryParams);
    }

	
    /**
	 * Получение списка вопросов для анкеты
     */
    public function getOnkoQuestions($data) {
        $sql = "
            SELECT 
				q.BIRADSQuestionType_id as \"OnkoQuestions_id\",
				q.BIRADSQuestionType_Name as \"OnkoQuestions_Name\",
				coalesce(p.id, 1) as \"val\",
				at.AnswerType_Code as \"AnswerType_Code\",
				ac.AnswerClass_SysNick as \"AnswerClass_SysNick\",
				q.BIRADSQuestionType_pid as \"OnkoQuestions_pid\",
				q.QuestionKind_id as \"QuestionKind_id\"
            FROM BIRADSQuestionType q
				left join BIRADSQuestionAnswer p on 
					p.BIRADSQuestionType_id = q.BIRADSQuestionType_id and 
					p.BIRADSQuestion_id = :BIRADSQuestion_id
				left join v_AnswerType at on at.AnswerType_id = q.AnswerType_id
				left join v_AnswerClass ac on ac.AnswerClass_id = q.AnswerClass_id
			order by \"OnkoQuestions_id\" ";

        $queryParams['BIRADSQuestion_id'] = $data['PersonOnkoProfile_id'];
		return $this->queryResult($sql, $queryParams);
    }
	

    /**
     * сохранение информации об анкетировании пациента
     */
    public function savePersonOnkoProfile($data) {

		$procedure = empty($data['PersonOnkoProfile_id']) ? 'p_BIRADSQuestion_ins' : 'p_BIRADSQuestion_upd';
		
		$BIRADSQuestion_CountYes = 0;
		
		foreach($data['QuestionAnswer'] as $row) {
			if ($row[1] == 1) $BIRADSQuestion_CountYes++;
		}
		
		if (!empty($data['BIRADSQuestion_Other'])) $BIRADSQuestion_CountYes++;

        $med_staff_fact_id = $this->getFirstResultFromQuery("
			select
			    MedStaffFact_id as \"MedStaffFact_id\" 
			from
			    v_MedStaffFact
			where
			    LpuSection_id = :LpuSection_id
            and
                MedPersonal_id = :MedPersonal_id 
			limit 1", [
			    'LpuSection_id' => $data['LpuSection_id'], 
                'MedPersonal_id' => $data['MedPersonal_id']
            ]);
		
		$queryParams = array();
		$query = "
            select 
                BIRADSQuestion_id as \"BIRADSQuestion_id\", 
                Error_Code as \"Error_Code\", 
                Error_Message as \"Error_Msg\"
			from {$procedure} (
				BIRADSQuestion_id := :BIRADSQuestion_id,
				Person_id := :Person_id,
				BIRADSQuestion_setDate := :BIRADSQuestion_id,
				MedStaffFact_id := :MedStaffFact_id,
				BIRADSQuestion_Other := :BIRADSQuestion_Other,
				BIRADSQuestion_CountYes := :BIRADSQuestion_CountYes,
				PersonRegisterType_id := :PersonRegisterType_id,
				CategoryBIRADS_id := :CategoryBIRADS_id,
				pmUser_id := :pmUser_id
				)
		";

        $queryParams['BIRADSQuestion_id'] = $data['PersonOnkoProfile_id'];
        $queryParams['Person_id'] = $data['Person_id'];
        $queryParams['BIRADSQuestion_setDate'] = $data['Profile_Date'];
        $queryParams['MedStaffFact_id'] = $med_staff_fact_id;
        $queryParams['BIRADSQuestion_Other'] = $data['BIRADSQuestion_Other'];
        $queryParams['BIRADSQuestion_CountYes'] = $BIRADSQuestion_CountYes;
        $queryParams['PersonRegisterType_id'] = null;
        $queryParams['CategoryBIRADS_id'] = $data['CategoryBIRADS_id'];
        $queryParams['pmUser_id'] = $data['pmUser_id'];

        $result = $this->queryResult($query, $queryParams);
		
		if (count($result) && isset($result[0]['BIRADSQuestion_id'])) {
			$data['BIRADSQuestion_id'] = $result[0]['BIRADSQuestion_id'];
			$result[0]['PersonOnkoProfile_id'] = $result[0]['BIRADSQuestion_id'];
			$this->saveBIRADSQuestionAnswer($data);
			$data['CategoryBIRADS_id'] = $this->getCategoryBIRADS($data['BIRADSQuestion_id']);
			$this->db->query("update BIRADSQuestion set CategoryBIRADS_id = :CategoryBIRADS_id where BIRADSQuestion_id = :BIRADSQuestion_id", $data);
			$result[0]['CategoryBIRADS_id'] = $data['CategoryBIRADS_id'];
			$this->saveBIRADSQuestionUslugaLink($data);
		}
		
		return $result;
    }

    /**
     * сохранение ответов
     */
    public function saveBIRADSQuestionAnswer($data) {
		
		foreach($data['QuestionAnswer'] as $row) {
			$BIRADSQuestionAnswer_id = $this->getFirstResultFromQuery("
				select BIRADSQuestionAnswer_id as \"BIRADSQuestionAnswer_id\" 
				from BIRADSQuestionAnswer 
				where BIRADSQuestion_id = :BIRADSQuestion_id and BIRADSQuestionType_id = :BIRADSQuestionType_id
			", array(
				'BIRADSQuestion_id' => $data['BIRADSQuestion_id'],
				'BIRADSQuestionType_id' => $row[0]
			));

			$procedure = !$BIRADSQuestionAnswer_id ? 'p_BIRADSQuestionAnswer_ins' : 'p_BIRADSQuestionAnswer_upd';
			
			$query = "
                select 
                    BIRADSQuestionAnswer_id as \"BIRADSQuestionAnswer_id\",
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\"
				from {$procedure} (
					BIRADSQuestionAnswer_id := :BIRADSQuestionAnswer_id,
					BIRADSQuestion_id := :BIRADSQuestion_id,
					BIRADSQuestionType_id := :BIRADSQuestionType_id,
					id := :id,
					pmUser_id := :pmUser_id
					)
			";
			
			$this->queryResult($query, array(
				'BIRADSQuestionAnswer_id' => $BIRADSQuestionAnswer_id ? $BIRADSQuestionAnswer_id : null,
				'BIRADSQuestion_id' => $data['BIRADSQuestion_id'],
				'BIRADSQuestionType_id' => $row[0],
				'id' => is_bool($row[1]) ? ($row[1] == 1 ? 2 : 1) : $row[1],
				'pmUser_id' => $data['pmUser_id']
			));
		}
    }

    /**
     * сохранение связи
     */
    public function saveBIRADSQuestionUslugaLink($data) {
		
		$BIRADSQuestionUslugaLink_id = $this->getFirstResultFromQuery("
			select BIRADSQuestionUslugaLink_id as \"BIRADSQuestionUslugaLink_id\" from BIRADSQuestionUslugaLink where BIRADSQuestion_id = :BIRADSQuestion_id
		", array(
			'BIRADSQuestion_id' => $data['BIRADSQuestion_id']
		));

		$procedure = !$BIRADSQuestionUslugaLink_id ? 'p_BIRADSQuestionUslugaLink_ins' : 'p_BIRADSQuestionUslugaLink_upd';
		
		$query = "
            select 
                BIRADSQuestionUslugaLink_id as \"BIRADSQuestionUslugaLink_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from {$procedure} (
				BIRADSQuestionUslugaLink_id := :BIRADSQuestionUslugaLink_id,
				BIRADSQuestion_id := :BIRADSQuestion_id,
				EvnUslugaPar_id := :EvnUslugaPar_id,
				pmUser_id := :pmUser_id
				)
		";
		
		$this->queryResult($query, array(
			'BIRADSQuestionUslugaLink_id' => $BIRADSQuestionUslugaLink_id ? $BIRADSQuestionUslugaLink_id : null,
			'BIRADSQuestion_id' => $data['BIRADSQuestion_id'],
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
			'pmUser_id' => $data['pmUser_id']
		));
	}

    /**
     * Удаление анкеты
     */
    public function deleteOnkoProfile($data) {
		$tmp = $this->queryList("select BIRADSQuestionAnswer_id as \"BIRADSQuestionAnswer_id\" from BIRADSQuestionAnswer where BIRADSQuestion_id = ?", array($data['PersonOnkoProfile_id']));
		foreach($tmp as $pqa) {			
			$this->db->query("
                select
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\"
				from p_BIRADSQuestionAnswer_del (
					BIRADSQuestionAnswer_id := ?
					)
			", array($pqa));
		}
		
		$query = "
            select 
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from p_BIRADSQuestion_del (
				BIRADSQuestion_id := :PersonOnkoProfile_id
				)
		";
		
		return $this->queryResult($query, $data);
    }

    /**
     * CategoryBIRADS по анкете
     */
    public function getCategoryBIRADS($BIRADSQuestion_id) {
	
		$query = "
			select MAX(CategoryBIRADS_id) as \"CategoryBIRADS_id\"
			from BIRADSQuestionType as \"bqt\"
			inner join BIRADSQuestionAnswer bqa on bqa.BIRADSQuestionType_id = bqt.BIRADSQuestionType_id
			where 
				bqa.BIRADSQuestion_id = ? and 
				bqa.id = 2 and 
				bqt.AnswerClass_id = 1
		";
		return $this->getFirstResultFromQuery($query, array($BIRADSQuestion_id));
    }
}