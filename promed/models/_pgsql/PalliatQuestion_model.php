<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * PalliatQuestion_model - палллиативка
 * сделано в режиме совместимомти с OnkoCtrl
 */
class PalliatQuestion_model extends SwPgModel {

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
                $filter .= " and (pcard.LpuRegion_id = 0 or lpu.Lpu_id is null)";
            } else {
                $filter .= " and pcard.LpuRegion_id = :Uch";
                $queryParams['Uch'] = $data['Uch'];
            }
        };

        $sql = "
			SELECT  
			-- select
				p.PalliatQuestion_id as id,
				p.Person_id as \"Person_id\",
				p.PalliatQuestion_id as \"PersonOnkoProfile_id\",
				ps.Person_SurName as \"SurName\",
				ps.Person_FirName as \"FirName\",
				ps.Person_SecName as \"SecName\",
				rtrim(rtrim(COALESCE(ps.Person_Surname, '')) || ' ' || rtrim(COALESCE(ps.Person_Firname, '')) || ' ' || rtrim(COALESCE(ps.Person_Secname, ''))) as fio,
				to_char(ps.Person_BirthDay, 'DD.MM.YYYY') as \"BirthDay\",
				ps.Sex_id as \"Sex_id\",
				substring(sex.Sex_Name, 1, 1) as sex,
				COALESCE(uaddr.Address_Address, paddr.Address_Address) as \"Address\",
				lpu.Lpu_id as \"Lpu_id\",
				lpu.Lpu_Nick as \"Lpu_Nick\",
				lr.LpuRegionType_Name || ' №' || lr.LpuRegion_Name as uch,
				lr.LpuRegion_id as \"LpuRegion_id\",
				to_char(p.PalliatQuestion_setDate, 'DD.MM.YYYY') as \"PersonOnkoProfile_DtBeg\",
				p.MedStaffFact_id as \"MedStaffFact_id\"
			-- end select
			FROM 
			-- from
				PalliatQuestion p
				left join v_MedStaffFact msf on msf.MedStaffFact_id = p.MedStaffFact_id
				inner join v_PersonState ps on p.Person_id = ps.Person_id
				left join v_Sex sex on sex.Sex_id = ps.Sex_id
				LEFT JOIN LATERAL (
					select
						pc.Person_id as PersonCard_Person_id,
						pc.Lpu_id,
						pc.LpuRegion_id
					from v_PersonCard pc
					where
						pc.Person_id = ps.Person_id
                    and
                        LpuAttachType_id = 1
					order by PersonCard_begDate desc
					limit 1
				) pcard on true
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
				p.PalliatQuestion_id as \"PersonOnkoProfile_id\"
				,p.Person_id as \"Person_id\"
				,to_char(p.PalliatQuestion_setDate, 'DD.MM.YYYY') as \"PersonOnkoProfile_DtBeg\"
				,p.Lpu_id as \"Lpu_id\"
				,l.Lpu_Nick as \"Lpu_Nick\"
				,p.MedStaffFact_id as \"MedStaffFact_id\"
				,msf.LpuBuilding_id as \"LpuBuilding_id\"
				,msf.LpuSection_id as \"LpuSection_id\"
				,p.PalliatQuestion_Other as \"PalliatQuestion_Other\"
				,p.PalliatQuestion_CountYes as \"PalliatQuestion_CountYes\"
				,p.PalliatPPSScale_id as \"PalliatPPSScale_id\"
				,p.PalliatPainScale_id as \"PalliatPainScale_id\"
			FROM PalliatQuestion p
				left join v_MedStaffFact msf on msf.MedStaffFact_id = p.MedStaffFact_id
				left join v_Lpu l on l.Lpu_id = p.lpu_id
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
        $sql = "
            select 
				q.PalliatQuestionType_id as \"OnkoQuestions_id\",
				q.PalliatQuestionType_Name as \"OnkoQuestions_Name\",
				q.PalliatQuestionType_Num as \"Questions_Num\",
				case
					when AnswerType_Code = 2 then p.PalliatQuestionAnswer_FreeForm
					when AnswerClass_Code = 1 then cast(coalesce(p.AnswerYesNoType_id, 1) as varchar)
					else cast(p.PalliatQuestionAnswer_AnswerId as varchar)
				end as \"val\",
				p.PalliatQuestionAnswer_FreeForm as \"FreeForm\",
				at.AnswerType_Code as \"AnswerType_Code\",
				ac.AnswerClass_SysNick as \"AnswerClass_SysNick\",
				q.PalliatQuestionType_IsRequired as \"IsRequired\"
            from v_PalliatQuestionType q
				left join PalliatQuestionAnswer p on 
					p.PalliatQuestionType_id = q.PalliatQuestionType_id and 
					p.PalliatQuestion_id = :PalliatQuestion_id
				left join v_AnswerType at on at.AnswerType_id = q.AnswerType_id
				left join v_AnswerClass ac on ac.AnswerClass_id = q.AnswerClass_id
			where
				coalesce(q.PalliatQuestionType_endDate, dbo.tzGetDate()) >= dbo.tzGetDate()
			order by 
				case when ac.AnswerClass_Code = 1 or AnswerType_Code is null then 1 else 0 end, 
				q.PalliatQuestionType_Code";

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
        SELECT
        PalliatQuestion_id as \"PalliatQuestion_id\",
        error_code as \"Error_Code\",
        error_message as \"Error_Msg\"
        FROM
        {$procedure}(
                PalliatQuestion_id => :PalliatQuestion_id,
				Person_id => :Person_id,
				PalliatQuestion_setDate => :PalliatQuestion_setDate,
				MedStaffFact_id => :MedStaffFact_id,
				PalliatQuestion_Other => :PalliatQuestion_Other,
				PalliatQuestion_CountYes => :PalliatQuestion_CountYes,
				PersonRegisterType_id => :PersonRegisterType_id,
				PalliatPPSScale_id => :PalliatPPSScale_id,
				PalliatPainScale_id => :PalliatPainScale_id,
				Lpu_id => :Lpu_id,
				pmUser_id => :pmUser_id
        )
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
				from PalliatQuestionAnswer
				where PalliatQuestion_id = :PalliatQuestion_id and PalliatQuestionType_id = :PalliatQuestionType_id
			", array(
                'PalliatQuestion_id' => $data['PalliatQuestion_id'],
                'PalliatQuestionType_id' => $row[0]
            ));

            $AnswerType_id = $this->getFirstResultFromQuery("
				select AnswerType_id as \"AnswerType_id\" from PalliatQuestionType where PalliatQuestionType_id = :PalliatQuestionType_id
			", array('PalliatQuestionType_id' => $row[0]));

            $procedure = !$PalliatQuestionAnswer_id ? 'p_PalliatQuestionAnswer_ins' : 'p_PalliatQuestionAnswer_upd';

            $query = "
            SELECT
            PalliatQuestionAnswer_id as \"PalliatQuestionAnswer_id\",
            error_code as \"Error_Code\",
            error_message as \"Error_Msg\"
            FROM
            {$procedure}(
                    PalliatQuestionAnswer_id => :PalliatQuestionAnswer_id,
					PalliatQuestion_id => :PalliatQuestion_id,
					PalliatQuestionType_id => :PalliatQuestionType_id,
					PalliatQuestionAnswer_FreeForm := :PalliatQuestionAnswer_FreeForm,
					PalliatQuestionAnswer_AnswerId := :PalliatQuestionAnswer_AnswerId,
					AnswerYesNoType_id => :AnswerYesNoType_id,
					pmUser_id => :pmUser_id
            )
            ";

            $this->queryResult($query, array(
                'PalliatQuestionAnswer_id' => $PalliatQuestionAnswer_id ? $PalliatQuestionAnswer_id : null,
                'PalliatQuestion_id' => $data['PalliatQuestion_id'],
                'PalliatQuestionType_id' => $row[0],
                'AnswerYesNoType_id' => $row[1] == 1 ? 2 : 1,
                'PalliatQuestionAnswer_FreeForm' => $AnswerType_id == 2 ? $row[1] : $row[2],
                'PalliatQuestionAnswer_AnswerId' => $AnswerType_id == 2 ? null : ($row[1] ? 2 : 1),
                'pmUser_id' => $data['pmUser_id']
            ));
        }
    }

    /**
     * Удаление анкеты
     */
    public function deleteOnkoProfile($data) {
        $tmp = $this->queryList("select PalliatQuestionAnswer_id as \"PalliatQuestionAnswer_id\" from PalliatQuestionAnswer where PalliatQuestion_id = ?", array($data['PersonOnkoProfile_id']));
        foreach($tmp as $pqa) {
            $this->db->query("
				SELECT
				error_code as \"Error_Code\",
                error_message as \"Error_Msg\"
                FROM
                p_PalliatQuestionAnswer_del(
                    PalliatQuestionAnswer_id => ?
                )
			", array($pqa));
        }

        $query = "
        SELECT
        error_code as \"Error_Code\",
        error_message as \"Error_Msg\"
        FROM
        p_PalliatQuestion_del(
            PalliatQuestion_id => :PersonOnkoProfile_id
        )
        ";

        return $this->queryResult($query, $data);
    }

    /**
     * loadPalliatPPSScale
     */
    public function loadPalliatPPSScale() {

        $query = "select PalliatPPSScale_id as \"PalliatPPSScale_id\"
                  ,PalliatPPSScale_Percent as \"PalliatPPSScale_Percent\"
                  ,PalliatPPSScale_MoveAbility as \"PalliatPPSScale_MoveAbility\"
                  ,PalliatPPSScale_ActivityType as \"PalliatPPSScale_ActivityType\"
                  ,PalliatPPSScale_SelfCare as \"PalliatPPSScale_SelfCare\"
                  ,PalliatPPSScale_Diet as \"PalliatPPSScale_Diet\"
                  ,PalliatPPSScale_ConsiousLevel as \"PalliatPPSScale_ConsiousLevel\"
                  ,Region_id as \"Region_id\"
                  ,pmUser_insID as \"pmUser_insID\"
                  ,pmUser_updID as \"pmUser_updID\"
                  ,PalliatPPSScale_insDT as \"PalliatPPSScale_insDT\"
                  ,PalliatPPSScale_updDT as \"PalliatPPSScale_updDT\" from v_PalliatPPSScale";
        return $this->queryResult($query);
    }

    /**
     * PalliatPainScale
     */
    public function loadPalliatPainScale() {

        $query = "select PalliatPainScale_id as \"PalliatPainScale_id\"
                  ,PalliatPainScale_Characteristic as \"PalliatPainScale_Characteristic\"
                  ,PalliatPainScale_PointCount as \"PalliatPainScale_PointCount\"
                  ,Region_id as \"Region_id\"
                  ,pmUser_insID as \"pmUser_insID\"
                  ,pmUser_updID as \"pmUser_updID\"
                  ,PalliatPainScale_insDT as \"PalliatPainScale_insDT\"
                  ,PalliatPainScale_updDT as \"PalliatPainScale_updDT\" from v_PalliatPainScale";
        return $this->queryResult($query);
    }
}