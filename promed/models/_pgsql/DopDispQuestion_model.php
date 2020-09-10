<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * DopDispQuestion_model - модель для работы с талонами по диспансеризации взрослого населения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @originalauthor	Petukhov Ivan aka Lich (megatherion@list.ru) / Stas Bykov aka Savage (savage1981@gmail.com)
 * @version      16.05.2013
 */

class DopDispQuestion_model extends SwPgModel
{
    protected $dateTimeFormat104 = "'dd.mm.yyyy'";

    /**
     * Получение списка формы редактирования анкетирования
     *
     * @param $data
     * @return array|false
     */
	public function loadDopDispQuestionEditWindow($data)
    {
		$query = "
			select
				eudd.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
				to_char(eudd.EvnUslugaDispDop_setDate, {$this->dateTimeFormat104}) as \"EvnUslugaDispDop_setDate\",
				to_char(eudd.EvnUslugaDispDop_didDate, {$this->dateTimeFormat104}) as \"EvnUslugaDispDop_didDate\",
				eudd.UslugaComplex_id as \"UslugaComplex_id\",
				eudd.LpuSection_uid as \"LpuSection_uid\",
				eudd.MedPersonal_id as \"MedPersonal_id\",
				eudd.MedStaffFact_id as \"MedStaffFact_id\",
				eudd.Diag_id as \"Diag_id\",
				eudd.DopDispDiagType_id as \"DopDispDiagType_id\",
				coalesce (CAST(eudd.EvnUslugaDispDop_DeseaseStage as varchar),'') as \"DeseaseStage\"
			from
				v_EvnUslugaDispDop eudd
			where
				eudd.EvnUslugaDispDop_id = :EvnUslugaDispDop_id
		";

		return $this->queryResult($query, [
			'EvnUslugaDispDop_id' => $data['EvnUslugaDispDop_id']
		]);
	}

	/**
	 *  Получение шаблона для печати
	 */
	function getTemplateForPrint($data) {
		$resp = $this->queryResult("
			select
				-- округление до ближайшего кратного трём
				cast(round(dbo.Age2(ps.Person_BirthDay, cast(cast(date_part('year', epld.EvnPLDisp_consDT) as varchar) || '-12-31' as timestamp)) / 3.0, 0) * 3 as int) as age 
			from
				v_EvnPLDisp epld
				inner join v_PersonState ps on ps.Person_id = epld.Person_id
			where
				epld.EvnPLDisp_id = :EvnPLDisp_id
			limit 1
		", [
			'EvnPLDisp_id' => $data['EvnPLDisp_id']
		]);

		$response = ['Error_Msg' => '', 'template' => 'Print_DispQuestion7.rptdesign'];
		if (!empty($resp[0]['age']) && $resp[0]['age'] >= 65) {
			$response['template'] = 'Print_DispQuestion8.rptdesign';
		}

		return $response;
	}

    /**
     * Загрузка анкетирования
     *
     * @param $data
     * @return bool
     */
	function loadDopDispQuestionGrid($data)
    {
        $vars = "
            with cte as
            (
                select
                    -- округление до ближайшего кратного трём
                    cast(round(dbo.Age2(ps.Person_BirthDay, cast(cast(date_part('year', epld.EvnPLDisp_consDT) as varchar) || '-12-31' as timestamp)) / 3.0, 0) * 3 as int) as Person_Age, 
                    epld.EvnPLDisp_consDT as EvnPLDisp_consDate,
                    epld.DispClass_id as DispClass_id 
                from
                    v_EvnPLDisp epld
                    inner join v_PersonState ps on ps.Person_id = epld.Person_id
                where
                    epld.EvnPLDisp_id = :EvnPLDisp_id
                limit 1
            )
        ";
		$query = $vars."
			select
				coalesce(DDQ.DopDispQuestion_id, -QT.QuestionType_id) as \"DopDispQuestion_id\",
				DDQ.EvnPLDisp_id as \"EvnPLDisp_id\",
				case
					when QT.QuestionType_Num is not null then QT.QuestionType_Num
					else cast(ROW_NUMBER() OVER (ORDER BY QT.QuestionType_Code) as varchar)
				end as \"QuestionType_RowNum\",
				QT.QuestionType_Code as \"QuestionType_Code\",
				QT.QuestionType_Name as \"QuestionType_Name\",
				QT.QuestionType_id as \"QuestionType_id\",
				coalesce(QT.AnswerType_id,1) as \"AnswerType_id\",
				coalesce(QT.AnswerClass_id,1) as \"AnswerClass_id\",
				coalesce(DDQ.DopDispQuestion_IsTrue, 1) as \"DopDispQuestion_IsTrue\",
				DDQ.DopDispQuestion_Answer as \"DopDispQuestion_Answer\",
				case 
				    when coalesce(QT.AnswerClass_id, 1) <> 6 
				        then coalesce(DDQ.DopDispQuestion_ValuesStr,1) 
				        else DDQ.DopDispQuestion_ValuesStr 
                    end as \"DopDispQuestion_ValuesStr\",
				case
					when coalesce(QT.AnswerType_id,1) = 1 then YN.YesNo_Name
					when coalesce(QT.AnswerType_id,1) = 2 then DDQ.DopDispQuestion_Answer
					when coalesce(QT.AnswerClass_id, 1) = 1 then AYNT.AnswerYesNoType_Name
					when coalesce(QT.AnswerClass_id, 1) = 2 then AOT.AnswerOnkoType_Name
					when coalesce(QT.AnswerClass_id, 1) = 3 then AST.AnswerSmokeType_Name
					when coalesce(QT.AnswerClass_id, 1) = 4 then AWT.AnswerWalkType_Name
					when coalesce(QT.AnswerClass_id, 1) = 5 then APT.AnswerPissType_Name
					when coalesce(QT.AnswerClass_id, 1) = 6 then YN.YesNo_Name || coalesce(', ' || D.Diag_Name, '')
					when coalesce(QT.AnswerClass_id, 1) = 7 then AIT.AlcoholIngestType_Name
				end as \"DopDispQuestion_Response\"
			from v_QuestionType QT
				left join v_DopDispQuestion DDQ on DDQ.QuestionType_id = QT.QuestionType_id and DDQ.EvnPLDisp_id = :EvnPLDisp_id
				left join v_AnswerYesNoType AYNT on AYNT.AnswerYesNoType_id = coalesce(DDQ.DopDispQuestion_ValuesStr, 1)
				left join v_AnswerOnkoType AOT on AOT.AnswerOnkoType_id = coalesce(DDQ.DopDispQuestion_ValuesStr, 1)
				left join v_AnswerSmokeType AST on AST.AnswerSmokeType_id = coalesce(DDQ.DopDispQuestion_ValuesStr, 1)
				left join v_AnswerWalkType AWT on AWT.AnswerWalkType_id = coalesce(DDQ.DopDispQuestion_ValuesStr, 1)
				left join v_AnswerPissType APT on APT.AnswerPissType_id = coalesce(DDQ.DopDispQuestion_ValuesStr, 1)
				left join v_YesNo YN on YN.YesNo_id = coalesce(DDQ.DopDispQuestion_IsTrue, 1)
				left join v_AlcoholIngestType AIT on AIT.AlcoholIngestType_id = coalesce(DDQ.DopDispQuestion_ValuesStr, 1)
				left join v_Diag D on D.Diag_id = DDQ.DopDispQuestion_ValuesStr
			where
				QT.DispClass_id = (select DispClass_id from cte)
				AND coalesce(QuestionType_begDate, (select EvnPLDisp_consDate from cte)) <= (select EvnPLDisp_consDate from cte)
				AND coalesce(QuestionType_endDate, (select EvnPLDisp_consDate from cte)) >= (select EvnPLDisp_consDate from cte)
				AND coalesce(QuestionType_AgeFrom, (select Person_Age from cte)) <= (select Person_Age from cte)
				AND coalesce(QuestionType_AgeTo, (select Person_Age from cte)) >= (select Person_Age from cte)
			order by QT.QuestionType_Code
		";
		// echo getDebugSql($query, array('EvnPLDisp_id' => $data['EvnPLDisp_id']));
		$result = $this->db->query($query, array(
			'EvnPLDisp_id' => $data['EvnPLDisp_id']
		));

		if (!is_object($result) ) {
            return false;

		}

        $resp = $result->result('array');
        foreach($resp as $key => $respone) {
            if ($respone['QuestionType_id'] == 121 && empty($respone['DopDispQuestion_Answer'])) {
                $resp[$key]['DopDispQuestion_Answer'] = 0;
                $resp[$key]['DopDispQuestion_Response'] = 0;
            }
        }
        return $resp;
	}
}