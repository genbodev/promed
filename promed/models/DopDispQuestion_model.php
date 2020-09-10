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

class DopDispQuestion_model extends swModel
{
	/**
	 *	Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 *  Получение списка формы редактирования анкетирования
	 */
	function loadDopDispQuestionEditWindow($data) {
		$query = "
			select
				eudd.EvnUslugaDispDop_id,
				convert(varchar(10), eudd.EvnUslugaDispDop_setDate, 104) as EvnUslugaDispDop_setDate,
				convert(varchar(10), eudd.EvnUslugaDispDop_didDate, 104) as EvnUslugaDispDop_didDate,
				eudd.UslugaComplex_id,
				eudd.LpuSection_uid,
				eudd.MedPersonal_id,
				eudd.MedStaffFact_id,
				eudd.Diag_id,
				eudd.DopDispDiagType_id,
				ISNULL(eudd.EvnUslugaDispDop_DeseaseStage,'') as DeseaseStage
			from
				v_EvnUslugaDispDop eudd (nolock)
			where
				eudd.EvnUslugaDispDop_id = :EvnUslugaDispDop_id
		";

		return $this->queryResult($query, array(
			'EvnUslugaDispDop_id' => $data['EvnUslugaDispDop_id']
		));
	}

	/**
	 *  Получение шаблона для печати
	 */
	function getTemplateForPrint($data) {
		$resp = $this->queryResult("
			select top 1
				-- округление до ближайшего кратного трём
				cast(round(dbo.Age2(ps.Person_BirthDay, cast(cast(YEAR(epld.EvnPLDisp_consDT) as varchar) + '-12-31' as datetime))/3.0,0)*3 as int) as age
			from
				v_EvnPLDisp epld (nolock)
				inner join v_PersonState ps (nolock) on ps.Person_id = epld.Person_id
			where
				epld.EvnPLDisp_id = :EvnPLDisp_id
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
	 *	Загрузка анкетирования
	 */
	function loadDopDispQuestionGrid($data) {
		$query = "
			Declare @DispClass_id bigint, @EvnPLDisp_consDate date, @Person_Age int;

			select top 1
				@Person_Age = cast(round(dbo.Age2(ps.Person_BirthDay, cast(cast(YEAR(epld.EvnPLDisp_consDT) as varchar) + '-12-31' as datetime))/3.0,0)*3 as int), -- округление до ближайшего кратного трём
				@EvnPLDisp_consDate = epld.EvnPLDisp_consDT,
				@DispClass_id = epld.DispClass_id
			from
				v_EvnPLDisp epld (nolock)
				inner join v_PersonState ps (nolock) on ps.Person_id = epld.Person_id
			where
				epld.EvnPLDisp_id = :EvnPLDisp_id

			select
				ISNULL(DDQ.DopDispQuestion_id, -QT.QuestionType_id) as DopDispQuestion_id,
				DDQ.EvnPLDisp_id,
				QT.QuestionType_Num,
				case
					when QT.QuestionType_Num is not null then QT.QuestionType_Num
					else cast(ROW_NUMBER() OVER (ORDER BY QT.QuestionType_Code) as varchar)
				end as QuestionType_RowNum,
				-- QT.QuestionType_Code,
				QT.QuestionType_Name,
				QT.QuestionType_id,
				QT.QuestionType_pid,
				ISNULL(QT.AnswerType_id,1) as AnswerType_id,
				ISNULL(QT.AnswerClass_id,1) as AnswerClass_id,
				ISNULL(DDQ.DopDispQuestion_IsTrue, 1) as DopDispQuestion_IsTrue,
				DDQ.DopDispQuestion_Answer,
				case when ISNULL(QT.AnswerClass_id, 1) <> 6 then ISNULL(DDQ.DopDispQuestion_ValuesStr,1) else DDQ.DopDispQuestion_ValuesStr end as DopDispQuestion_ValuesStr,
				case
					when ISNULL(QT.AnswerType_id,1) = 1 then YN.YesNo_Name
					when ISNULL(QT.AnswerType_id,1) = 2 then DDQ.DopDispQuestion_Answer
					when ISNULL(QT.AnswerClass_id, 1) = 1 then AYNT.AnswerYesNoType_Name
					when ISNULL(QT.AnswerClass_id, 1) = 2 then AOT.AnswerOnkoType_Name
					when ISNULL(QT.AnswerClass_id, 1) = 3 then AST.AnswerSmokeType_Name
					when ISNULL(QT.AnswerClass_id, 1) = 4 then AWT.AnswerWalkType_Name
					when ISNULL(QT.AnswerClass_id, 1) = 5 then APT.AnswerPissType_Name
					when ISNULL(QT.AnswerClass_id, 1) = 6 then YN.YesNo_Name + ISNULL(', ' + D.Diag_Name,'')
					when ISNULL(QT.AnswerClass_id, 1) = 7 then AIT.AlcoholIngestType_Name
				end as DopDispQuestion_Response
			from v_QuestionType QT (nolock)
				left join v_DopDispQuestion DDQ (nolock) on DDQ.QuestionType_id = QT.QuestionType_id and DDQ.EvnPLDisp_id = :EvnPLDisp_id
				left join v_AnswerYesNoType AYNT (nolock) on AYNT.AnswerYesNoType_id = ISNULL(DDQ.DopDispQuestion_ValuesStr,1)
				left join v_AnswerOnkoType AOT (nolock) on AOT.AnswerOnkoType_id = ISNULL(DDQ.DopDispQuestion_ValuesStr,1)
				left join v_AnswerSmokeType AST (nolock) on AST.AnswerSmokeType_id = ISNULL(DDQ.DopDispQuestion_ValuesStr,1)
				left join v_AnswerWalkType AWT (nolock) on AWT.AnswerWalkType_id = ISNULL(DDQ.DopDispQuestion_ValuesStr,1)
				left join v_AnswerPissType APT (nolock) on APT.AnswerPissType_id = ISNULL(DDQ.DopDispQuestion_ValuesStr,1)
				left join v_YesNo YN (nolock) on YN.YesNo_id = ISNULL(DDQ.DopDispQuestion_IsTrue,1)
				left join v_AlcoholIngestType AIT (nolock) on AIT.AlcoholIngestType_id = ISNULL(DDQ.DopDispQuestion_ValuesStr,1)
				left join v_Diag D (nolock) on D.Diag_id = DDQ.DopDispQuestion_ValuesStr
			where
				QT.DispClass_id = @DispClass_id
				AND ISNULL(QuestionType_begDate, @EvnPLDisp_consDate) <= @EvnPLDisp_consDate
				AND ISNULL(QuestionType_endDate, @EvnPLDisp_consDate) >= @EvnPLDisp_consDate
				AND ISNULL(QuestionType_AgeFrom, @Person_Age) <= @Person_Age
				AND ISNULL(QuestionType_AgeTo, @Person_Age) >= @Person_Age
			order by QT.QuestionType_Code
		";
		// echo getDebugSql($query, array('EvnPLDisp_id' => $data['EvnPLDisp_id']));
		$result = $this->db->query($query, array(
			'EvnPLDisp_id' => $data['EvnPLDisp_id']
		));

		if ( is_object($result) ) {
			$resp = $result->result('array');
			foreach($resp as $key => $respone) {
				if ($respone['QuestionType_id'] == 121 && empty($respone['DopDispQuestion_Answer'])) {
					$resp[$key]['DopDispQuestion_Answer'] = 0;
					$resp[$key]['DopDispQuestion_Response'] = 0;
				}
			}
			return $resp;
		}
		else {
			return false;
		}
	}
}
?>