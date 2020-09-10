<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * BIRADSQuestion_model - BI-RADS
 * сделано в режиме совместимости с OnkoCtrl
 */
class BIRADSQuestion_model extends swModel {

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
				$filter .= " and (pcard.LpuRegion_id = '' or lpu.Lpu_id is null)";
			} else {
				$filter .= " and pcard.LpuRegion_id = :Uch";
				$queryParams['Uch'] = $data['Uch'];
			}
		};
   
		$sql = "
			SELECT  
			-- select
				p.BIRADSQuestion_id id
				,p.Person_id
				,p.BIRADSQuestion_id PersonOnkoProfile_id
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
				,convert(Varchar, p.BIRADSQuestion_setDate, 104) PersonOnkoProfile_DtBeg
				,p.MedStaffFact_id
				,cbr.CategoryBIRADS_id
				,cbr.CategoryBIRADS_Name
			-- end select
			FROM 
			-- from
				BIRADSQuestion p (nolock)
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
				left join CategoryBIRADS cbr with (nolock) on cbr.CategoryBIRADS_id = p.CategoryBIRADS_id
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
        $queryParams = array();
        $filter = '';

        /*if (!empty($data['PersonOnkoProfile_id'])) {
            $filter = ' and p.BIRADSQuestion_id = :PersonOnkoProfile_id ';
        }*/
		
		/*$query = "
			SELECT 
				p.BIRADSQuestion_id as PersonOnkoProfile_id
				,p.Person_id
				,convert(Varchar, p.BIRADSQuestion_setDate, 104) PersonOnkoProfile_DtBeg
				,msf.Lpu_id
				,l.Lpu_Nick
				,msf.MedStaffFact_id
				,msf.LpuBuilding_id
				,msf.LpuSection_id
				,p.CategoryBIRADS_id
				,ed.EvnDirection_id
			FROM BIRADSQuestion p WITH (NOLOCK)
				left join v_MedStaffFact msf WITH (NOLOCK) on msf.MedStaffFact_id = p.MedStaffFact_id
				left join v_Lpu l WITH (NOLOCK) on l.Lpu_id = msf.lpu_id
				outer apply (
					select top 1 ed.EvnDirection_id 
					from v_EvnDirection ed WITH (NOLOCK) 
					inner join v_EvnUslugaPar eup (nolock) on eup.EvnUslugaPar_id = ed.EvnDirection_pid
					inner join BIRADSQuestionUslugaLink bqul (nolock) on bqul.EvnUslugaPar_id = eup.EvnUslugaPar_id
					where ed.DirType_id = 17 and bqul.BIRADSQuestion_id = p.BIRADSQuestion_id
				) ed
				where p.Person_id = :Person_id
				{$filter} 
		";*/
		$query = "
			SELECT 
				p.BIRADSQuestion_id as PersonOnkoProfile_id
				,p.Person_id
				,convert(Varchar, p.BIRADSQuestion_setDate, 104) PersonOnkoProfile_DtBeg
				,msf.Lpu_id
				,l.Lpu_Nick
				,msf.MedStaffFact_id
				,msf.LpuBuilding_id
				,msf.LpuSection_id
				,p.CategoryBIRADS_id
				,ed.EvnDirection_id
				,QUL.EvnUslugaPar_id
			FROM BIRADSQuestion p WITH (NOLOCK)
				left join v_MedStaffFact msf WITH (NOLOCK) on msf.MedStaffFact_id = p.MedStaffFact_id
				left join v_Lpu l WITH (NOLOCK) on l.Lpu_id = msf.lpu_id
				left join BIRADSQuestionUslugaLink QUL on QUL.BIRADSQuestion_id = p.BIRADSQuestion_id
				left join EvnUslugaPar EUP on QUL.EvnUslugaPar_id = EUP.EvnUslugaPar_id
				left join EvnUsluga EU on EU.EvnUsluga_id = Eup.EvnUsluga_id
				left join EvnDirection ED on ED.EvnDirection_id = EU.EvnDirection_id
				where p.Person_id = :Person_id
				and (QUL.EvnUslugaPar_id = :EvnUslugaPar_id or p.BIRADSQuestion_id = :PersonOnkoProfile_id)
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
				q.BIRADSQuestionType_id as OnkoQuestions_id,
				q.BIRADSQuestionType_Name as OnkoQuestions_Name,
				isnull(p.id, 1) as val,
				at.AnswerType_Code,
				ac.AnswerClass_SysNick,
				q.BIRADSQuestionType_pid as OnkoQuestions_pid,
				q.QuestionKind_id
            FROM BIRADSQuestionType q WITH (NOLOCK)
				left join BIRADSQuestionAnswer p WITH (NOLOCK) on 
					p.BIRADSQuestionType_id = q.BIRADSQuestionType_id and 
					p.BIRADSQuestion_id = :BIRADSQuestion_id
				left join v_AnswerType at with(nolock) on at.AnswerType_id = q.AnswerType_id
				left join v_AnswerClass ac with(nolock) on ac.AnswerClass_id = q.AnswerClass_id
			order by OnkoQuestions_id ";

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

		$med_staff_fact_id = $this->queryResult("
			select top 1 MedStaffFact_id from v_MedStaffFact with (nolock)
			where LpuSection_id = :LpuSection_id and MedPersonal_id = :MedPersonal_id",
			['LpuSection_id'=>$data['LpuSection_id'],'MedPersonal_id'=>$data['MedPersonal_id']])[0]['MedStaffFact_id'];

		$queryParams = array();
		$query = "
			declare
				@BIRADSQuestion_id bigint = :BIRADSQuestion_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$procedure}
				@BIRADSQuestion_id = @BIRADSQuestion_id output,
				@Person_id = :Person_id,
				@BIRADSQuestion_setDate = :BIRADSQuestion_setDate,
				@MedStaffFact_id = :MedStaffFact_id,
				@BIRADSQuestion_Other = :BIRADSQuestion_Other,
				@BIRADSQuestion_CountYes = :BIRADSQuestion_CountYes,
				@PersonRegisterType_id = :PersonRegisterType_id,
				@CategoryBIRADS_id = :CategoryBIRADS_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @BIRADSQuestion_id as BIRADSQuestion_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

        $queryParams['BIRADSQuestion_id'] = $data['PersonOnkoProfile_id'];
        $queryParams['Person_id'] = $data['Person_id'];
        $queryParams['BIRADSQuestion_setDate'] = $data['Profile_Date'];
        $queryParams['MedStaffFact_id'] = /*$data['MedStaffFact_id']*/$med_staff_fact_id;
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
			$this->db->query("update BIRADSQuestion with(rowlock) set CategoryBIRADS_id = :CategoryBIRADS_id where BIRADSQuestion_id = :BIRADSQuestion_id", $data);
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
				select BIRADSQuestionAnswer_id 
				from BIRADSQuestionAnswer (nolock) 
				where BIRADSQuestion_id = :BIRADSQuestion_id and BIRADSQuestionType_id = :BIRADSQuestionType_id
			", array(
				'BIRADSQuestion_id' => $data['BIRADSQuestion_id'],
				'BIRADSQuestionType_id' => $row[0]
			));

			$procedure = !$BIRADSQuestionAnswer_id ? 'p_BIRADSQuestionAnswer_ins' : 'p_BIRADSQuestionAnswer_upd';
			
			$query = "
				declare
					@BIRADSQuestionAnswer_id bigint = :BIRADSQuestionAnswer_id,
					@Error_Code bigint,
					@Error_Message varchar(4000);
				exec {$procedure}
					@BIRADSQuestionAnswer_id = @BIRADSQuestionAnswer_id output,
					@BIRADSQuestion_id = :BIRADSQuestion_id,
					@BIRADSQuestionType_id = :BIRADSQuestionType_id,
					@id = :id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select @BIRADSQuestionAnswer_id as BIRADSQuestionAnswer_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
			select BIRADSQuestionUslugaLink_id from BIRADSQuestionUslugaLink (nolock) where BIRADSQuestion_id = :BIRADSQuestion_id
		", array(
			'BIRADSQuestion_id' => $data['BIRADSQuestion_id']
		));

		$procedure = !$BIRADSQuestionUslugaLink_id ? 'p_BIRADSQuestionUslugaLink_ins' : 'p_BIRADSQuestionUslugaLink_upd';
		
		$query = "
			declare
				@BIRADSQuestionUslugaLink_id bigint = :BIRADSQuestionUslugaLink_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$procedure}
				@BIRADSQuestionUslugaLink_id = @BIRADSQuestionUslugaLink_id output,
				@BIRADSQuestion_id = :BIRADSQuestion_id,
				@EvnUslugaPar_id = :EvnUslugaPar_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @BIRADSQuestionUslugaLink_id as BIRADSQuestionUslugaLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
		$tmp = $this->queryList("select BIRADSQuestionAnswer_id from BIRADSQuestionAnswer (nolock) where BIRADSQuestion_id = ?", array($data['PersonOnkoProfile_id']));
		foreach($tmp as $pqa) {			
			$this->db->query("
				declare
					@Error_Code int,
					@Error_Msg varchar(4000);

				exec p_BIRADSQuestionAnswer_del
					@BIRADSQuestionAnswer_id = ?,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Msg output;

				select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
			", array($pqa));
		}
		
		$query = "
			declare
				@Error_Code int,
				@Error_Msg varchar(4000);

			exec p_BIRADSQuestion_del
				@BIRADSQuestion_id = :PersonOnkoProfile_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;

			select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";
		
		return $this->queryResult($query, $data);
    }

    /**
     * CategoryBIRADS по анкете
     */
    public function getCategoryBIRADS($BIRADSQuestion_id) {
	
		$query = "
			select MAX(CategoryBIRADS_id) as CategoryBIRADS_id
			from BIRADSQuestionType bqt (nolock)
			inner join BIRADSQuestionAnswer bqa on bqa.BIRADSQuestionType_id = bqt.BIRADSQuestionType_id
			where 
				bqa.BIRADSQuestion_id = ? and 
				bqa.id = 2 and 
				bqt.AnswerClass_id = 1
		";
		return $this->getFirstResultFromQuery($query, array($BIRADSQuestion_id));
    }
}