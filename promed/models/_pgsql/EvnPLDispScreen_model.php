<?php	defined('BASEPATH') or die ('No direct script access allowed');

/**
 * EvnPLDispScreen_model - модель для работы с профосмотрами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @originalauthor	Petukhov Ivan aka Lich (megatherion@list.ru) / Stas Bykov aka Savage (savage1981@gmail.com)
 * @version      20.06.2013
 *
 * @property CI_DB_driver $db
 *
 * @property EvnDiagDopDisp_model $evndiagdopdisp
 * @property HeredityDiag_model $hereditydiag
 * @property PersonWeight_model $PersonWeight_model
 * @property PersonHeight_model $PersonHeight_model
 */
class EvnPLDispScreen_model extends SwPgModel
{
	private $dateTimeForm104 = "DD.MM.YYYY";
	private $dateTimeForm120 = "YYYY-MM-DD HH24:MI:SS";

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Удаление аттрибутов
	 *
	 * @param $attr
	 * @param $EvnPLDispScreen_id
	 * @param $pmUser_id
	 * @return string
	 *
	 * @throws Exception
	 */
	function deleteAttributes($attr, $EvnPLDispScreen_id, $pmUser_id)
	{
		if ($attr == "EvnUslugaDispDop") {
			$query = "
				select EUDD.EvnUslugaDispDop_id as id
				from v_EvnUslugaDispDop EUDD
				where EUDD.EvnUslugaDispDop_rid = :EvnPLDispScreen_id
			";
		} else {
			$whereString = "EvnPLDisp_id = :EvnPLDispScreen_id";
			$query = "
				select {$attr}_id as id
				from v_{$attr}
				where {$whereString}
			";
		}
		$queryParams = ["EvnPLDispScreen_id" => $EvnPLDispScreen_id];
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		/**@var CI_DB_result $result */
		$response = $result->result("array");
		if (is_array($response) && count($response) > 0) {
			foreach ($response as $array) {
				$_field = ($attr == "EvnUslugaDispDop") ? ",pmUser_id := :pmUser_id" : "";
				$query = "
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_{$attr}_del(
						{$attr}_id := :id
						{$_field}
					);
				";
				$queryParams = [
					"id" => $array["id"],
					"pmUser_id" => $pmUser_id
				];
				$result = $this->db->query($query, $queryParams);
				if (!is_object($result)) {
					throw new Exception("Ошибка при выполнении запроса к базе данных");
				}
				$result = $result->result("array");
				if (is_array($result) && count($result) > 0 && !empty($result[0]["Error_Msg"])) {
					return $result[0]["Error_Msg"];
				}
			}
		}
		return "";
	}

	/**
	 * Получение кода услуги
	 *
	 * @param $data
	 * @return string
	 */
	function getUslugaComplexCode($data)
	{
		$query = "
			select UslugaComplex_Code as \"UslugaComplex_Code\"
			from v_UslugaComplex
			where UslugaComplex_id = :UslugaComplex_id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return "";
		}
		$result = $result->result("array");
		return (count($result) > 0) ? $result[0]["UslugaComplex_Code"] : "";
	}

	/**
	 * Проверка возможности добавления новой карты
	 * 
	 * @param $data
	 * @return array
	 * 
	 * @throws Exception
	 */
	function checkAddAvailability($data)
	{
		// Если по возрасту (на конец года) пациент не подлежит скрининговым исследованиям взрослых.
		$sql = "
			select ps.Person_id as \"Person_id\"
			from v_PersonState ps
			where ps.Person_id = :Person_id
			  and (age2(ps.Person_BirthDay, cast(:Year||'-12-31' as date)) IN (30,35,40,42,44,45,46,48,50,52,54,55,56,58,60,62,64,66,68,70))
		";
		$sqlParams = [
			"Year" => date("Y"),
			"Person_id" => $data["Person_id"]
		];
		$resp = $this->queryResult($sql, $sqlParams);
		if (empty($resp[0]["Person_id"])) {
			throw new Exception("Пациент не подлежит проведению скринингового исследования");
		}
		// Если для расчетной возрастной группы (для взрослых на конец текущего года) на пациента уже создана карта скрининговых исследований
		$sql = "
			select eplds.EvnPLDispScreen_id as \"EvnPLDispScreen_id\"
			from
				v_PersonState ps
				inner join v_AgeGroupDisp agd on agd.DispType_id = 5
						and agd.AgeGroupDisp_From <= age2(ps.Person_BirthDay, cast(:Year||'-12-31' as date))
				        and agd.AgeGroupDisp_To >= age2(ps.Person_BirthDay, cast(:Year||'-12-31' as date))
				inner join v_EvnPLDispScreen eplds on eplds.AgeGroupDisp_id = agd.AgeGroupDisp_id and eplds.Person_id = ps.Person_id
			where ps.Person_id = :Person_id
		";
		$sqlParams = [
			"Year" => date("Y"),
			"Person_id" => $data["Person_id"]
		];
		$resp = $this->queryResult($sql, $sqlParams);
		if (!empty($resp[0]["EvnPLDispScreen_id"])) {
			throw new Exception("На пациента уже создана карта скрининговых исследований");
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Получение диагноза по коду
	 * 
	 * @param $diag_code
	 * @return bool
	 */
	function getDiagIdByCode($diag_code)
	{
		$query = "
			select Diag_id as \"Diag_id\"
			from v_Diag
			where Diag_Code = :Diag_Code
			limit 1
		";
		$queryParams = ["Diag_Code" => $diag_code];
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result("array");
		return (count($resp) > 0) ? $resp[0]["Diag_id"] : false;
	}

	/**
	 * Получение данных карты
	 * 
	 * @param $data
	 * @return bool
	 */
	function getEvnPLDispScreenData($data)
	{
		$query = "
			select
				EvnPLDispScreen_id as \"EvnPLDispScreen_id\",
				PersonEvn_id as \"PersonEvn_id\",
				Server_id as \"Server_id\"
			from v_EvnPLDispScreen EPLDD
			where EPLDD.EvnPLDispScreen_id = :EvnPLDisp_id
			limit 1
		";
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result("array");
		return (count($resp) > 0) ? $resp[0] : false;
	}

	/**
	 * Сохранение анкетирования
	 *
	 * @param $data
	 * @return array
	 *
	 * @throws Exception
	 */
	function saveDopDispQuestionGrid($data)
	{
		$this->beginTransaction();
		$dd = $this->getEvnPLDispScreenData($data);

		if (empty($dd)) {
			$this->rollbackTransaction();
			throw new Exception("Ошибка получения данных карты диспансеризации");
		}
		$data["PersonEvn_id"] = $dd["PersonEvn_id"];
		$data["Server_id"] = $dd["Server_id"];
		// Нужно сохранять услугу по анкетированию (refs #20465)
		// Ищем услугу с UslugaComplex_id для SurveyType_Code = 2, если нет то создаём новую, иначе обновляем.
		$query = "
			select 
			    STL.UslugaComplex_id as \"UslugaComplex_id\",
				EUDDData.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\"
			from v_SurveyTypeLink STL
				inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
				inner join v_DopDispInfoConsent ddic on ddic.SurveyTypeLink_id = stl.SurveyTypeLink_id
				left join lateral (
					select  EvnUslugaDispDop_id
					from v_EvnUslugaDispDop EUDD
					where EUDD.EvnUslugaDispDop_pid = :EvnPLDisp_id
					  and EUDD.UslugaComplex_id IN (
					      select UslugaComplex_id
					      from v_SurveyTypeLink
					      where SurveyType_id = STL.SurveyType_id
					  )
					  and coalesce(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
					limit 1
				) as EUDDData on true
			where ST.SurveyType_Code = 2
			  and ddic.EvnPLDisp_id = :EvnPLDisp_id
			limit 1
		";
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			$this->rollbackTransaction();
			throw new Exception("Ошибка при выполнении запроса к базе данных (получение идентификатора услуги)");
		}
		$resp = $result->result('array');
		if (!is_array($resp) || count($resp) == 0) {
			$this->rollbackTransaction();
			throw new Exception("Ошибка при получении идентификатора услуги");
		}
		// сохраняем услугу
		if (!empty($resp[0]["EvnUslugaDispDop_id"])) {
			$data["EvnUslugaDispDop_id"] = $resp[0]["EvnUslugaDispDop_id"];
			$proc = "p_EvnUslugaDispDop_upd";
		} else {
			$data["EvnUslugaDispDop_id"] = null;
			$proc = "p_EvnUslugaDispDop_ins";
		}
		$data["UslugaComplex_id"] = $resp[0]["UslugaComplex_id"];
		$selectString = "
			evnuslugadispdop_id as \"EvnUslugaDispDop_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$proc}(
				evnuslugadispdop_id := :EvnUslugaDispDop_id,
				evnuslugadispdop_pid := :EvnPLDisp_id,
				lpu_id := :Lpu_id,
				server_id := :Server_id,
				personevn_id := :PersonEvn_id,
				evnuslugadispdop_setdt := null,
				evnuslugadispdop_diddt := :DopDispQuestion_setDate,
				paytype_id := (select PayType_id from v_PayType where PayType_SysNick = 'dopdisp' limit 1),
				medpersonal_id := null,
				uslugaplace_id := 1,
				lpusection_uid := null,
				uslugacomplex_id := :UslugaComplex_id,
				evndirection_id := null,
				evnprescr_id := null,
				evnprescrtimetable_id := null,
				examinationplace_id := null,
				evnuslugadispdop_examplace := null,
				pmuser_id := :pmUser_id
			);
		";
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			$this->rollbackTransaction();
			throw new Exception("Ошибка при выполнении запроса к базе данных (сохранение услуги)");
		}
		$resp = $result->result("array");
		if (!is_array($resp) || count($resp) == 0) {
			$this->rollbackTransaction();
			throw new Exception("Ошибка при сохранении услуги");
		} else if (!empty($resp[0]["Error_Msg"])) {
			$this->rollbackTransaction();
			throw new Exception($resp[0]["Error_Msg"]);
		}
		ConvertFromWin1251ToUTF8($data["DopDispQuestionData"]);
		$items = json_decode($data["DopDispQuestionData"], true);
		$this->load->model("EvnDiagDopDisp_model", "evndiagdopdisp");
		$this->load->model("HeredityDiag_model", "hereditydiag");
		// Получаем существующие данные из БД
		$ExistingDopDispQuestionData = [];
		$query = "
			select
				QuestionType_id as \"QuestionType_id\",
			    DopDispQuestion_id as \"DopDispQuestion_id\",
			    DopDispQuestion_ValuesStr as \"DopDispQuestion_ValuesStr\"
			from v_DopDispQuestion
			where EvnPLDisp_id = :EvnPLDisp_id
		";
		$queryParams = ["EvnPLDisp_id" => $data["EvnPLDisp_id"]];
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			$this->rollbackTransaction();
			throw new Exception("Ошибка при выполнении запроса к базе данных (получение списка имеющихся данных анкетирования)");
		}
		$resp = $result->result("array");
		if (is_array($resp) && count($resp) > 0) {
			foreach ($resp as $dataArray) {
				if ($dataArray["QuestionType_id"] == 50 && !empty($data["NeedCalculation"]) && $data["NeedCalculation"] == 1) {
					$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $dataArray["DopDispQuestion_ValuesStr"]);
				}
				$ExistingDopDispQuestionData[$dataArray["QuestionType_id"]] = $dataArray["DopDispQuestion_id"];
			}
		}
		$data["EvnDiagDopDisp_setDate"] = $data["DopDispQuestion_setDate"];
		foreach ($items as $item) {
			if (!empty($data["NeedCalculation"]) && $data["NeedCalculation"] == 1) {
				switch ($item["QuestionType_id"]) {
					/*
						1. Ранее известные имеющиеся заболевания, подраздел
						Примечание. Автоматически создавать поля списка по результатам анкетирования (при нажатии функциональной кнопки «Рассчитать») – При указании значения «Да» на следующие вопросы:
							– №2: I20.9
							– №3: Z03.4
							– №4: I67.9
							– №5: E10.0   upd: E14.9 ибо https://redmine.swan.perm.ru/issues/19459#note-74
							– №6: значение брать из анкеты
							– №7: A16.2
					*/
					case 46:
						if (($item["AnswerType_id"] == 1 && $item["DopDispQuestion_IsTrue"] == 2) || ($item["AnswerType_id"] == 3 && $item["DopDispQuestion_ValuesStr"] == 2)) {
							$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $this->getDiagIdByCode("I20.9"));
						} else {
							$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $this->getDiagIdByCode("I20.9"));
						}
						break;
					case 47:
						if (($item["AnswerType_id"] == 1 && $item["DopDispQuestion_IsTrue"] == 2) || ($item["AnswerType_id"] == 3 && $item["DopDispQuestion_ValuesStr"] == 2)) {
							$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $this->getDiagIdByCode("Z03.4"));
						} else {
							$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $this->getDiagIdByCode("Z03.4"));
						}
						break;
					case 48:
						if (($item["AnswerType_id"] == 1 && $item["DopDispQuestion_IsTrue"] == 2) || ($item["AnswerType_id"] == 3 && $item["DopDispQuestion_ValuesStr"] == 2)) {
							$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $this->getDiagIdByCode("I67.9"));
						} else {
							$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $this->getDiagIdByCode("I67.9"));
						}
						break;
					case 49:
						if (($item["AnswerType_id"] == 1 && $item["DopDispQuestion_IsTrue"] == 2) || ($item["AnswerType_id"] == 3 && $item["DopDispQuestion_ValuesStr"] == 2)) {
							$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $this->getDiagIdByCode("E14.9"));
						} else {
							$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $this->getDiagIdByCode("E14.9"));
						}
						break;
					case 50:
						if ($item["DopDispQuestion_IsTrue"] == 2) {
							$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $item["DopDispQuestion_ValuesStr"]);
						}
						break;
					case 51:
						if (($item["AnswerType_id"] == 1 && $item["DopDispQuestion_IsTrue"] == 2) || ($item["AnswerType_id"] == 3 && $item["DopDispQuestion_ValuesStr"] == 2)) {
							$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $this->getDiagIdByCode("A16.2"));
						} else {
							$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $this->getDiagIdByCode("A16.2"));
						}
						break;
					/* 
						2. Наследственность по заболеваниям, подраздел		
						Примечание. Автоматически создавать поля списка по результатам анкетирования (при нажатии функциональной кнопки «Рассчитать») – При указании значения «Да» или «не знаю» на следующие вопросы:
						– №8: Z03.4, «Да» - «отягощена», «не знаю» - «не известно»
						– №9: I64., «Да» - «отягощена», «не знаю» - «не известно»
						– №10: C16.9, «Да» - «отягощена», «не знаю» - «не известно»
					*/
					case 52:
						if (($item["AnswerType_id"] == 1 && $item["DopDispQuestion_IsTrue"] == 2) || ($item["AnswerType_id"] == 3 && $item["DopDispQuestion_ValuesStr"] >= 2)) {
							$this->hereditydiag->addHeredityDiag($data, $this->getDiagIdByCode("Z03.4"), ($item["DopDispQuestion_ValuesStr"] == 2) ? 1 : 2);
						} else {
							$this->hereditydiag->delHeredityDiagByDiag($data, $this->getDiagIdByCode("Z03.4"));
						}
						break;
					case 53:
						if (($item["AnswerType_id"] == 1 && $item["DopDispQuestion_IsTrue"] == 2) || ($item["AnswerType_id"] == 3 && $item["DopDispQuestion_ValuesStr"] >= 2)) {
							$this->hereditydiag->addHeredityDiag($data, $this->getDiagIdByCode("I64."), ($item["DopDispQuestion_ValuesStr"] == 2) ? 1 : 2);
						} else {
							$this->hereditydiag->delHeredityDiagByDiag($data, $this->getDiagIdByCode("I64."));
						}
						break;
					case 54:
						if (($item["AnswerType_id"] == 1 && $item["DopDispQuestion_IsTrue"] == 2) || ($item["AnswerType_id"] == 3 && $item["DopDispQuestion_ValuesStr"] >= 2)) {
							$this->hereditydiag->addHeredityDiag($data, $this->getDiagIdByCode("C16.9"), ($item["DopDispQuestion_ValuesStr"] == 2) ? 1 : 2);
						} else {
							$this->hereditydiag->delHeredityDiagByDiag($data, $this->getDiagIdByCode("C16.9"));
						}
						break;
					/*
						3. Показания к консультации врача-специалиста, подраздел.
						Примечание. Автоматически создавать поля списка по результатам анкетирования (при нажатии функциональной кнопки «Рассчитать») – При указании значения «Да» на следующие вопросы:
							– хотя бы на один из вопросов №22,23,24 или хотя бы на один из вопросов №41,42,43: «хирург» - «второй этап диспансеризации»
							– на все вопросы №27,28,29,30 или хотя бы на два из №36-40: «психиатр-нарколог» - «вне программы диспансеризации»
					*/
				}
			}
			if (array_key_exists($item["QuestionType_id"], $ExistingDopDispQuestionData)) {
				$item["DopDispQuestion_id"] = $ExistingDopDispQuestionData[$item["QuestionType_id"]];
			}
			$item["DopDispQuestion_Answer"] = toAnsi($item["DopDispQuestion_Answer"]);
			if (!empty($item["DopDispQuestion_id"]) && $item["DopDispQuestion_id"] > 0) {
				$proc = "p_DopDispQuestion_upd";
			} else {
				$proc = "p_DopDispQuestion_ins";
				$item["DopDispQuestion_id"] = null;
			}
			if (empty($item["DopDispQuestion_IsTrue"])) {
				$item["DopDispQuestion_IsTrue"] = null;
			}
			$selectString = "
				dopdispquestion_id as \"DopDispQuestion_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			";
			$query = "
				select {$selectString}
				from {$proc}(
				    dopdispquestion_id := :DopDispQuestion_id,
				    evnpldisp_id := :EvnPLDisp_id,
				    questiontype_id := :QuestionType_id,
				    dopdispquestion_istrue := :DopDispQuestion_IsTrue,
				    dopdispquestion_answer := :DopDispQuestion_Answer,
				    dopdispquestion_valuesstr := :DopDispQuestion_ValuesStr,
				    pmuser_id := :pmUser_id
				);
			";
			$queryParams = [
				"EvnPLDisp_id" => $data["EvnPLDisp_id"],
				"DopDispQuestion_id" => $item["DopDispQuestion_id"],
				"QuestionType_id" => $item["QuestionType_id"],
				"DopDispQuestion_IsTrue" => $item["DopDispQuestion_IsTrue"],
				"DopDispQuestion_Answer" => $item["DopDispQuestion_Answer"],
				"DopDispQuestion_ValuesStr" => $item["DopDispQuestion_ValuesStr"],
				"pmUser_id" => $data["pmUser_id"]
			];
			/**@var CI_DB_result $result */
			$result = $this->db->query($query, $queryParams);
			if (!is_object($result)) {
				$this->rollbackTransaction();
				throw new Exception("Ошибка при выполнении запроса к базе данных (сохранение ответов на вопросы)");
			}
			$result = $result->result("array");
			if (!is_array($result) || count($result) == 0) {
				$this->rollbackTransaction();
				throw new Exception("Ошибка при сохранении ответов на вопросы");
			} else if (!empty($result[0]["Error_Msg"])) {
				$this->rollbackTransaction();
				throw new Exception($result[0]["Error_Msg"]);
			}
		}
		$this->commitTransaction();
		return ["success" => true, "Error_Msg" => ""];
	}

	/**
	 * Удаление карты
	 * 
	 * @param $data
	 * @return array
	 * 
	 * @throws Exception
	 */
	function deleteEvnPLDispScreen($data)
	{
		$query = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_evnpldispscreen_del(
			    evnpldispscreen_id := :EvnPLDispScreen_id,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"EvnPLDispScreen_id" => $data["EvnPLDispScreen_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных (удаление талона ДД)");
		}
		return $result->result("array");
	}

	/**
	 * Получение данных для формы просмотра карты
	 * @param $data
	 * @return array|bool
	 */
	function loadEvnPLDispScreenEditForm($data)
	{
		$query = "
			select
				'edit' as \"accessType\",
				EPLDS.EvnPLDispScreen_id as \"EvnPLDispScreen_id\",
				epldsl.ScreenType_id as \"ScreenType_id\",
				elapp.ScreenEndCause_id as \"ScreenEndCause_id\",
				EPLDS.Person_id as \"Person_id\",
				EPLDS.PersonEvn_id as \"PersonEvn_id\",
				EPLDS.Server_id as \"Server_id\",
				coalesce(EPLDS.DispClass_id, 13) as \"DispClass_id\",
				EPLDS.EvnPLDispScreen_PersonWaist as \"EvnPLDispScreen_PersonWaist\",
				EPLDS.EvnPLDispScreen_QueteletIndex as \"EvnPLDispScreen_QueteletIndex\",
				EPLDS.EvnPLDispScreen_ArteriaSistolPress as \"EvnPLDispScreen_ArteriaSistolPress\",
				EPLDS.EvnPLDispScreen_ArteriaDiastolPress as \"EvnPLDispScreen_ArteriaDiastolPress\",
				EPLDS.HealthKind_id as \"HealthKind_id\",
				EPLDS.AgeGroupDisp_id as \"AgeGroupDisp_id\",
				EPLDS.EvnPLDispScreen_IsEndStage as \"EvnPLDispScreen_IsEndStage\",
				PH.PersonHeight_Height as \"PersonHeight_Height\",
				PW.PersonWeight_Weight as \"PersonWeight_Weight\",
				EPLDS.AlcoholIngestType_bid as \"AlcoholIngestType_bid\",
				EPLDS.AlcoholIngestType_vid as \"AlcoholIngestType_vid\",
				EPLDS.AlcoholIngestType_wid as \"AlcoholIngestType_wid\",
				EPLDS.EvnPLDispScreen_IsAlco as \"EvnPLDispScreen_IsAlco\",
				EPLDS.EvnPLDispScreen_IsBleeding as \"EvnPLDispScreen_IsBleeding\",
				EPLDS.EvnPLDispScreen_IsCoronary as \"EvnPLDispScreen_IsCoronary\",
				EPLDS.EvnPLDispScreen_IsHeadache as \"EvnPLDispScreen_IsHeadache\",
				EPLDS.EvnPLDispScreen_IsHeartache as \"EvnPLDispScreen_IsHeartache\",
				EPLDS.EvnPLDispScreen_IsHighPressure as \"EvnPLDispScreen_IsHighPressure\",
				EPLDS.EvnPLDispScreen_IsParCoronary as \"EvnPLDispScreen_IsParCoronary\",
				EPLDS.EvnPLDispScreen_IsSmoking as \"EvnPLDispScreen_IsSmoking\",
				EPLDS.WaistCircumference_id as \"WaistCircumference_id\",
				EPLDS.EvnPLDispScreen_IsBlurVision as \"EvnPLDispScreen_IsBlurVision\",
				EPLDS.EvnPLDispScreen_IsDailyPhysAct as \"EvnPLDispScreen_IsDailyPhysAct\",
				EPLDS.EvnPLDispScreen_IsDirectedPMSP as \"EvnPLDispScreen_IsDirectedPMSP\",
				EPLDS.EvnPLDispScreen_IsGenPredisposed as \"EvnPLDispScreen_IsGenPredisposed\",
				EPLDS.EvnPLDispScreen_IsGlaucoma as \"EvnPLDispScreen_IsGlaucoma\",
				EPLDS.EvnPLDispScreen_IsHealthy as \"EvnPLDispScreen_IsHealthy\",
				EPLDS.EvnPLDispScreen_IsHighMyopia as \"EvnPLDispScreen_IsHighMyopia\",
				EPLDS.EvnPLDispScreen_IsHyperglycaemia as \"EvnPLDispScreen_IsHyperglycaemia\",
				EPLDS.EvnPLDispScreen_IsHyperlipidemia as \"EvnPLDispScreen_IsHyperlipidemia\",
				EPLDS.EvnPLDispScreen_IsHypertension as \"EvnPLDispScreen_IsHypertension\",
				EPLDS.EvnPLDispScreen_IsLowPhysAct as \"EvnPLDispScreen_IsLowPhysAct\",
				EPLDS.EvnPLDispScreen_IsOverweight as \"EvnPLDispScreen_IsOverweight\",
				EPLDS.EvnPLDispScreen_IsVisImpair as \"EvnPLDispScreen_IsVisImpair\",
				EPLDS.FecalCasts_id as \"FecalCasts_id\",
				EPLDS.EvnPLDispScreen_IsAlcoholAbuse as \"EvnPLDispScreen_IsAlcoholAbuse\",
				EPLDS.EvnPLDispScreen_IsDisability as \"EvnPLDispScreen_IsDisability\",
				EPLDS.EvnPLDispScreen_DisabilityYear as \"EvnPLDispScreen_DisabilityYear\",
				EPLDS.EvnPLDispScreen_DisabilityPeriod as \"EvnPLDispScreen_DisabilityPeriod\",
				EPLDS.Diag_disid as \"Diag_disid\",
				to_char(EPLDS.EvnPLDispScreen_setDate::date, '{$this->dateTimeForm104}') as \"EvnPLDispScreen_setDate\",
				EPLDS.Lpu_id as \"Lpu_id\"
			from
				v_EvnPLDispScreen EPLDS
				left join v_PersonHeight PH on PH.Evn_id = EPLDS.EvnPLDispScreen_id
				left join v_PersonWeight PW on PW.Evn_id = EPLDS.EvnPLDispScreen_id
				left join r101.v_EvnPLDispScreenLink epldsl on epldsl.EvnPLDispScreen_id = EPLDS.EvnPLDispScreen_id
				left join r101.EvnLinkAPP elapp on elapp.Evn_id = EPLDS.EvnPLDispScreen_id
			where EPLDS.EvnPLDispScreen_id = :EvnPLDispScreen_id
			limit 1
		";
		$queryParams = ["EvnPLDispScreen_id" => $data["EvnPLDispScreen_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение полей карты
	 * @param $data
	 * @return array|bool
	 */
	function getEvnPLDispScreenFields($data)
	{
		$query = "
			select
				rtrim(lp.Lpu_Name) as \"Lpu_Name\",
				rtrim(coalesce(lp1.Lpu_Name, '')) as \"Lpu_AName\",
				rtrim(coalesce(addr1.Address_Address, '')) as \"Lpu_AAddress\",
				rtrim(lp.Lpu_OGRN) as \"Lpu_OGRN\",
				coalesce(pc.PersonCard_Code, '') as \"PersonCard_Code\",
				ps.Person_SurName||' '||ps.Person_FirName||' '||coalesce(ps.Person_SecName, '') as \"Person_FIO\",
				sx.Sex_Name as \"Sex_Name\",
				coalesce(osmo.OrgSMO_Nick, '') as \"OrgSMO_Nick\",
				coalesce(case when pls.PolisType_id = 4 then '' else pls.Polis_Ser end, '') as \"Polis_Ser\",
				coalesce(case when pls.PolisType_id = 4 then ps.Person_EdNum else pls.Polis_Num end, '') as \"Polis_Num\",
				coalesce(osmo.OrgSMO_Name, '') as \"OrgSMO_Name\",
				to_char(ps.Person_BirthDay, '{$this->dateTimeForm104}') as \"Person_BirthDay\",
				coalesce(addr.Address_Address, '') as \"Person_Address\",
				jborg.Org_Nick as \"Org_Nick\",
				atype.AttachType_Name as \"AttachType_Name\",
				to_char(EPLDD.EvnPLDispScreen_disDate, '{$this->dateTimeForm104}') as \"EvnPLDispScreen_disDate\"
			from
				v_EvnPLDispScreen EPLDD
				inner join v_Lpu lp on lp.Lpu_id = EPLDD.Lpu_id
				left join v_Lpu lp1 on lp1.Lpu_id = EPLDD.Lpu_aid
				left join Address addr1 on addr1.Address_id = lp1.UAddress_id
				left join v_PersonCard pc on pc.Person_id = EPLDD.Person_id and pc.LpuAttachType_id = 1
				inner join v_PersonState ps on ps.Person_id = EPLDD.Person_id
				inner join Sex sx on sx.Sex_id = ps.Sex_id
				left join Polis pls on pls.Polis_id = ps.Polis_id
				left join v_OrgSmo osmo on osmo.OrgSmo_id = pls.OrgSmo_id
				left join Address addr on addr.Address_id = ps.PAddress_id
				left join Job jb on jb.Job_id = ps.Job_id
				left join Org jborg on jborg.Org_id = jb.Org_id
				left join AttachType atype on atype.AttachType_id = EPLDD.AttachType_id
			where EPLDD.EvnPLDispScreen_id = :EvnPLDispScreen_id
			  and EPLDD.Lpu_id = :Lpu_id
			limit 1
		";
		$queryParams = [
			"EvnPLDispScreen_id" => $data["EvnPLDispScreen_id"],
			"Lpu_id" => $data["Lpu_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Загрузка формы редактирования услуги
	 *
	 * @param $data
	 * @return array|bool
	 */
	function loadEvnUslugaDispDop($data)
	{
		$query = "
			select
				EUDD.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
				EUDD.EvnUslugaDispDop_pid as \"EvnUslugaDispDop_pid\",
				EUDD.PersonEvn_id as \"PersonEvn_id\",
				EUDD.Server_id as \"Server_id\",
				to_char(EUDD.EvnUslugaDispDop_setDT, '{$this->dateTimeForm104}') as \"EvnUslugaDispDop_setDate\",
				EUDD.EvnUslugaDispDop_setTime as \"EvnUslugaDispDop_setTime\",
				EUDD.UslugaComplex_id as \"UslugaComplex_id\",
				EUDD.LpuSection_uid as \"LpuSection_id\",
				EUDD.MedPersonal_id as \"MedPersonal_id\",
				EUDD.Diag_id as \"Diag_id\",
				EUDD.SurveyType_id as \"SurveyType_id\",
				EUDD.DeseaseType_id as \"DeseaseType_id\",
				eu.Lpu_uid as \"Lpu_uid\",
				EUDD.Lpu_id as \"Lpu_id\",
				EUDD.MedStaffFact_id as \"MedStaffFact_id\",
				coalesce(eu.EvnUsluga_IsAPP,1) as \"EvnUsluga_IsAPP\"
			from 
				v_EvnUslugaDispDop EUDD
				inner join v_EvnUsluga eu on eu.EvnUsluga_id = eudd.EvnUslugaDispDop_id
			where EUDD.EvnUslugaDispDop_id = :EvnUslugaDispDop_id
		";
		$queryParams = ["EvnUslugaDispDop_id" => $data["EvnUslugaDispDop_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result("array");
		// нужно получить значения результатов услуги из EvnUslugaRate
		if (isset($resp[0]["EvnUslugaDispDop_id"])) {
			$query = "
				select
					RT.RateType_SysNick as nick,
					RVT.RateValueType_SysNick as \"RateValueType_SysNick\",
					CASE RVT.RateValueType_SysNick
						WHEN 'int' THEN cast(R.Rate_ValueInt as varchar)
						WHEN 'float' THEN cast(cast(R.Rate_ValueFloat as decimal(16,3)) as varchar)
						WHEN 'string' THEN R.Rate_ValueStr
						WHEN 'template' THEN R.Rate_ValueStr
						WHEN 'reference' THEN cast(R.Rate_ValuesIs as varchar)
					END as value
				from
					v_EvnUslugaRate eur
					left join v_Rate r on r.Rate_id = eur.Rate_id
					left join v_RateType rt on rt.RateType_id = r.RateType_id
					left join RateValueType rvt on rvt.RateValueType_id = rt.RateValueType_id
				where eur.EvnUsluga_id = :EvnUsluga_id
			";
			$queryParams = ["EvnUsluga_id" => $resp[0]["EvnUslugaDispDop_id"]];
			$result = $this->db->query($query, $queryParams);
			if (is_object($result)) {
				$results = $result->result("array");
				foreach ($results as $oneresult) {
					if ($oneresult["RateValueType_SysNick"] == "float") {
						// Убираем последнюю цифру в значении
						// http://redmine.swan.perm.ru/issues/23248
						$oneresult["value"] = substr($oneresult["value"], 0, strlen($oneresult["value"]) - 1);
					}
					$resp[0][$oneresult["nick"]] = $oneresult["value"];
				}
			}
		}
		return $resp;
	}

	/**
	 * Получение списка исследований в талоне по ДД
	 *
	 * @param $data
	 * @return array|bool
	 */
	function loadEvnUslugaDispDopGrid($data)
	{
		$setDate = strtotime($data["EvnPLDispScreen_setDate"]);
		$dateX = strtotime("01-05-2018");
		$queryBeforeFirstMay = "
			select
				ST.SurveyType_id as \"SurveyType_id\",
				ST.SurveyType_Name as \"SurveyType_Name\",
				ST.SurveyType_Code as \"SurveyType_Code\",
				STLINK.SurveyTypeLink_id as \"SurveyTypeLink_id\",
				eudd.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
				to_char(eudd.EvnUslugaDispDop_setDate, '{$this->dateTimeForm104}') as \"EvnUslugaDispDop_setDate\",
				0 as \"noIndication\"
			from
				v_SurveyType ST
				inner join lateral (
					select SurveyTypeLink_id
					from
						v_SurveyTypeLink STL
						inner join v_AgeGroupDisp AGD on AGD.AgeGroupDisp_id = :AgeGroupDisp_id
								and STL.SurveyTypeLink_From = AGD.AgeGroupDisp_From
						        and STL.SurveyTypeLink_To = AGD.AgeGroupDisp_To
					where STL.DispClass_id = 13
					  and coalesce(STL.Sex_id, (select Sex_id from v_PersonState where Person_id = :Person_id limit 1)) = (select Sex_id from v_PersonState where Person_id = :Person_id limit 1)
					  and STL.SurveyType_id = ST.SurveyType_id
					limit 1
				) as STLINK on true
				left join v_EvnUslugaDispDop eudd on eudd.EvnUslugaDispDop_rid = :EvnPLDispScreen_id and eudd.SurveyType_id = ST.SurveyType_id
		";
		$queryAfterFirstMay = "
			select
				ST.SurveyType_id as \"SurveyType_id\",
				ST.SurveyType_Name as \"SurveyType_Name\",
				ST.SurveyType_Code as \"SurveyType_Code\",
				STLINK.SurveyTypeLink_id as \"SurveyTypeLink_id\",
				eudd.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
				to_char(eudd.EvnUslugaDispDop_setDate::date, '{$this->dateTimeForm104}') as \"EvnUslugaDispDop_setDate\",
				0 as \"noIndication\",
				stlink.UslugaComplex_id as \"UslugaComplex_id\",
				eudd.EvnDirection_id as \"EvnDirection_id\",
				eudd.MedPersonal_id as \"MedPersonal_id\",
				eudd.MedStaffFact_id as \"MedStaffFact_id\",
				eudd.LpuSection_uid as \"LpuSection_id\",
				eudd.Diag_id as \"Diag_id\"
			from
				v_SurveyType ST
				inner join lateral (
					select 
						stl.SurveyTypeLink_id,
						stl.UslugaComplex_id
					from
						v_SurveyTypeLink STL
						inner join r101.SurveyTypeScreenLink stsl on stsl.SurveyTypeLink_id = STL.SurveyTypeLink_id
					where
						STL.DispClass_id = 13
						and coalesce(STL.Sex_id, (select Sex_id from v_PersonState where Person_id = :Person_id limit 1)) = (select Sex_id from v_PersonState where Person_id = :Person_id limit 1)
						and STL.SurveyType_id = ST.SurveyType_id
						and coalesce(STL.SurveyTypeLink_endDate, tzgetdate()) >= tzgetdate()
						and stsl.ScreenType_id = :ScreenType_id
						and (select dbo.age(Person_BirthDay, tzgetdate()) from v_PersonState where Person_id = :Person_id) >= STL.SurveyTypeLink_From 
						and (select dbo.age(Person_BirthDay, tzgetdate()) from v_PersonState where Person_id = :Person_id) <= STL.SurveyTypeLink_To
					limit 1
				) as STLINK on true
				left join v_EvnUslugaDispDop eudd on eudd.EvnUslugaDispDop_rid = :EvnPLDispScreen_id and eudd.SurveyType_id = ST.SurveyType_id
		";
		$queryWithoutAgeGroups = "
			select
				ST.SurveyType_id as \"SurveyType_id\",
				ST.SurveyType_Name as \"SurveyType_Name\",
				ST.SurveyType_Code as \"SurveyType_Code\",
				STLINK.SurveyTypeLink_id as \"SurveyTypeLink_id\",
				CASE 
					when STLINK.SurveyTypeLink_IsEarlier is null then 0
					else STLINK.SurveyTypeLink_IsEarlier
				end as \"SurveyTypeLink_IsEarlier\",
				eudd.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
				to_char(eudd.EvnUslugaDispDop_setDate::date, '{$this->dateTimeForm104}') as \"EvnUslugaDispDop_setDate\",
				0 as \"noIndication\"
			from
				v_SurveyType ST
				inner join lateral (
					select
						SurveyTypeLink_id,
						SurveyTypeLink_IsEarlier,
						SurveyTypeLink_Period
					from v_SurveyTypeLink STL
					where STL.DispClass_id = 13
					  and coalesce(STL.Sex_id, (select Sex_id from v_PersonState where Person_id = :Person_id limit 1)) = (select Sex_id from v_PersonState where Person_id = :Person_id limit 1)
					  and STL.SurveyType_id = ST.SurveyType_id
					  and EXTRACT(YEAR FROM ((select Person_BirthDay from v_PersonState where Person_id = :Person_id limit 1)) - cast('31.12.'||to_char(tzgetdate()::date, 'YYYY') as date)) between STL.SurveyTypeLink_From and STL.SurveyTypeLink_To
					  and coalesce(STL.SurveyTypeLink_endDate, tzgetdate()) >= tzgetdate()
					  and STL.SurveyTypeLink_id not in (
							select STLD.SurveyTypeLink_id
							from
								v_SurveyTypeLink STLD
								inner join v_EvnUslugaDispDop ED on ED.SurveyType_id = STLD.SurveyType_id
								left join v_EvnPLDispScreen EPDS on EPDS.EvnPLDispScreen_id = :EvnPLDispScreen_id
							where (:EvnPLDispScreen_id is null OR ED.EvnUslugaDispDop_rid <> :EvnPLDispScreen_id)
							  and ED.Person_id = :Person_id
							  and ED.EvnUslugaDispDop_setDate is not null
							  and EXTRACT(MONTH FROM ED.EvnUslugaDispDop_setDate - coalesce(EPDS.EvnPLDispScreen_setDate, tzgetdate())) < STLD.SurveyTypeLink_Period
					  )
					limit 1
				) as STLINK on true
				left join v_EvnUslugaDispDop eudd on eudd.EvnUslugaDispDop_rid = :EvnPLDispScreen_id and eudd.SurveyType_id = ST.SurveyType_id
		";
		if ($data["withoutAgeGroups"] == "true") {
			$query = $queryWithoutAgeGroups;
		} else {
			$query = $setDate >= $dateX ? $queryAfterFirstMay : $queryBeforeFirstMay;
		}
		$queryParams = [
			"AgeGroupDisp_id" => $data["AgeGroupDisp_id"],
			"Person_id" => $data["Person_id"],
			"EvnPLDispScreen_id" => $data["EvnPLDispScreen_id"],
			'ScreenType_id' => $data['ScreenType_id']
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result("array");
		foreach ($resp as $key => $value) {
			switch ($value["SurveyType_Code"]) {
				case "109":
					// Кольпоскопия
					$noIndication = 1; // нет показаний
					// Результат исследования Цитологическое исследование шейки матки (Рар-тест) один из: ASC-H, HSIL, AIS, рак.
					if (!empty($data["EvnPLDispScreen_id"])) {
						$subSql = "
							select eudd.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\"
							from
								v_EvnUslugaDispDop eudd
								inner join v_SurveyType st on st.SurveyType_id = eudd.SurveyType_id and st.SurveyType_Code = 108 -- Цитологическое исследование шейки матки (Рар-тест)
								inner join v_EvnUslugaRate eur on eur.EvnUsluga_id = eudd.EvnUslugaDispDop_id
								inner join v_Rate r on r.Rate_id = eur.Rate_id
								inner join v_RateType rt on rt.RateType_id = r.RateType_id
							where eudd.EvnUslugaDispDop_pid = :EvnPLDispScreen_id
							  and rt.RateType_SysNick = 'uteri_carvix_scrning'
							  and r.Rate_ValueStr IN ('5', '7', '8', '9')
							limit 1
						";
						$subSqlParams = ["EvnPLDispScreen_id" => $data["EvnPLDispScreen_id"]];
						$resp_check = $this->queryResult($subSql, $subSqlParams);
						if (!empty($resp_check[0]["EvnUslugaDispDop_id"])) {
							$noIndication = 0;
						}
					}
					$resp[$key]["noIndication"] = $noIndication;
					break;
				case "162":
					// Колоноскопия
					$noIndication = 1; // нет показаний
					// Результат исследования «Анализ кала на скрытую кровь (гемокульт-тест)» -положительный
					if (!empty($data["EvnPLDispScreen_id"])) {
						$subSql = "
							select eudd.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\"
							from
								v_EvnUslugaDispDop eudd
								inner join v_SurveyType st on st.SurveyType_id = eudd.SurveyType_id and st.SurveyType_Code = 112 -- Анализ кала на скрытую кровь (гемокульт-тест)
								inner join v_EvnUslugaRate eur on eur.EvnUsluga_id = eudd.EvnUslugaDispDop_id
								inner join v_Rate r on r.Rate_id = eur.Rate_id
								inner join v_RateType rt on rt.RateType_id = r.RateType_id
							where eudd.EvnUslugaDispDop_pid = :EvnPLDispScreen_id
							  and rt.RateType_SysNick = 'fec_occult_blood'
							  and r.Rate_ValueStr = '1'
							limit 1
						";
						$subSqlParams = ["EvnPLDispScreen_id" => $data["EvnPLDispScreen_id"]];
						$resp_check = $this->queryResult($subSql, $subSqlParams);
						if (!empty($resp_check[0]["EvnUslugaDispDop_id"])) {
							$noIndication = 0;
						}
					}
					$resp[$key]["noIndication"] = $noIndication;
					break;
				case "110":
					// Биопсия
					$noIndication = 1; // нет показаний
					// Результат исследования Цитологическое исследование шейки матки (Рар-тест) один из: ASC-H, HSIL, AIS, рак.
					if (!empty($data["EvnPLDispScreen_id"])) {
						$subSql = "
							select eudd.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\"
							from
								v_EvnUslugaDispDop eudd
								inner join v_SurveyType st on st.SurveyType_id = eudd.SurveyType_id and st.SurveyType_Code = 108 -- Цитологическое исследование шейки матки (Рар-тест)
								inner join v_EvnUslugaRate eur on eur.EvnUsluga_id = eudd.EvnUslugaDispDop_id
								inner join v_Rate r on r.Rate_id = eur.Rate_id
								inner join v_RateType rt on rt.RateType_id = r.RateType_id
							where eudd.EvnUslugaDispDop_pid = :EvnPLDispScreen_id
							  and rt.RateType_SysNick = 'uteri_carvix_scrning'
							  and r.Rate_ValueStr IN ('5', '7', '8', '9')
							limit 1
						";
						$subSqlParams = ["EvnPLDispScreen_id" => $data["EvnPLDispScreen_id"]];
						$resp_check = $this->queryResult($subSql, $subSqlParams);
						if (!empty($resp_check[0]["EvnUslugaDispDop_id"])) {
							$noIndication = 0;
						}
					}
					$resp[$key]["noIndication"] = $noIndication;
					break;
			}
		}
		return $resp;
	}

	/**
	 * Список карт для поточного ввода
	 * @param $data
	 * @return array|bool
	 */
	function loadEvnPLDispScreenStreamList($data)
	{
		$filterArray = [];
		$queryParams = [];
		$filterArray[] = "EPL.pmUser_insID = :pmUser_id";
		$queryParams["pmUser_id"] = $data["pmUser_id"];
		if (preg_match("/^\d{2}:\d{2}:\d{2}$/", $data["begTime"])) {
			$filterArray[] = "EPL.EvnPL_insDT >= :date_time";
			$queryParams["date_time"] = $data["begDate"] . " " . $data["begTime"];
		}
		if (isset($data["Lpu_id"])) {
			$filterArray[] = "EPL.Lpu_id = :Lpu_id";
			$queryParams["Lpu_id"] = $data["Lpu_id"];
		}
		$filterString = implode(" and ", $filterArray);
		$query = "
        	select distinct
				EPL.EvnPL_id as \"EvnPL_id\",
				EPL.Person_id as \"Person_id\",
				EPL.Server_id as \"Server_id\",
				EPL.PersonEvn_id as \"PersonEvn_id\",
				rtrim(EPL.EvnPL_NumCard) as \"EvnPL_NumCard\",
				rtrim(PS.Person_Surname) as \"Person_Surname\",
				rtrim(PS.Person_Firname) as \"Person_Firname\",
				rtrim(PS.Person_Secname) as \"Person_Secname\",
				to_char(PS.Person_Birthday, '{$this->dateTimeForm104}') as \"Person_Birthday\",
				to_char(EPL.EvnPL_setDate, '{$this->dateTimeForm104}') as \"EvnPL_setDate\",
				to_char(EPL.EvnPL_disDate, '{$this->dateTimeForm104}') as \"EvnPL_disDate\",
				EPL.EvnPL_VizitCount as \"EvnPL_VizitCount\",
				IsFinish.YesNo_Name as \"EvnPL_IsFinish\"
			from
				v_EvnPL EPL
				inner join v_PersonState PS on PS.Person_id = EPL.Person_id
				left join YesNo IsFinish on IsFinish.YesNo_id = EPL.EvnPL_IsFinish
			where {$filterString}
			order by EPL.EvnPL_id desc
			limit 100
    	";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Список посещений
	 * @param $data
	 * @return array|bool
	 */
	function loadEvnVizitPLDispDopGrid($data)
	{
		$query = "
			select
				EVPL.EvnVizitPL_id as \"EvnVizitPL_id\",
				EVPL.LpuSection_id as \"LpuSection_id\",
				EVPL.MedPersonal_id as \"MedPersonal_id\",
				EVPL.MedPersonal_sid as \"MedPersonal_sid\",
				EVPL.PayType_id as \"PayType_id\",
				EVPL.ProfGoal_id as \"ProfGoal_id\",
				EVPL.ServiceType_id as \"ServiceType_id\",
				EVPL.VizitType_id as \"VizitType_id\",
				EVPL.EvnVizitPL_Time as \"EvnVizitPL_Time\",
				to_char(EVPL.EvnVizitPL_setDate, '{$this->dateTimeForm104}') as \"EvnVizitPL_setDate\",
				EVPL.EvnVizitPL_setTime as \"EvnVizitPL_setTime\",
				rtrim(LS.LpuSection_Name) as \"LpuSection_Name\",
				rtrim(MP.Person_Fio) as \"MedPersonal_Fio\",
				rtrim(PT.PayType_Name) as \"PayType_Name\",
				rtrim(ST.ServiceType_Name) as \"ServiceType_Name\",
				rtrim(VT.VizitType_Name) as \"VizitType_Name\",
				1 as \"Record_Status\"
			from v_EvnVizitPL EVPL
				left join LpuSection LS on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_MedPersonal MP on MP.MedPersonal_id = EVPL.MedPersonal_id
				left join PayType PT on PT.PayType_id = EVPL.PayType_id
				left join ServiceType ST on ST.ServiceType_id = EVPL.ServiceType_id
				left join VizitType VT on VT.VizitType_id = EVPL.VizitType_id
			where EVPL.EvnVizitPL_pid = :EvnPL_id
		";
		$queryParams = ["EvnPL_id" => $data["EvnPL_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Проверка того, что человек есть в регистре по ДД и у него заведены все необходимые данные
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function checkPersonData($data)
	{
		$query = "
			select
				Sex_id as \"Sex_id\",
				SocStatus_id as \"SocStatus_id\",
				ps.UAddress_id as \"Person_UAddress_id\",
				ps.Polis_Ser as \"Polis_Ser\",
				ps.Polis_Num as \"Polis_Num\",
				o.Org_Name as \"Org_Name\",
				o.Org_INN as \"Org_INN\",
				o.Org_OGRN as \"Org_OGRN\",
				o.UAddress_id as \"Org_UAddress_id\",
				o.Okved_id as \"Okved_id\",
				os.OrgSmo_Name as \"OrgSmo_Name\",
				(extract(year from PS.Person_Birthday - tzgetdate()) +
					case when extract(month from ps.Person_Birthday) > extract(month from tzgetdate()) or (extract(month from ps.Person_Birthday) = extract(month from tzgetdate()) and extract(day from ps.Person_Birthday) > extract(day from tzgetdate()))
						then -1
					    else 0
				end) as \"Person_Age\",
				to_char(PS.Person_Birthday, '{$this->dateTimeForm104}') as \"Person_Birthday\"
			from v_PersonState ps
				left join v_Job j on j.Job_id=ps.Job_id
				left join v_Org o on o.Org_id=j.Org_id
				left join v_Polis pol on pol.Polis_id=ps.Polis_id
				left join v_OrgSmo os on os.OrgSmo_id=pol.OrgSmo_id
			where ps.Person_id = :Person_id
		";
		$queryParams = ["Person_id" => $data["Person_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		$response = $result->result("array");
		if (!is_array($response) || count($response) == 0) {
			throw new Exception("Ошибка при проверке персональных данных человека!");
		}
		$errors = [];
		if (ArrayVal($response[0], "Sex_id") == "") {
			$errors[] = "Не заполнен Пол";
		}
		if (ArrayVal($response[0], "SocStatus_id") == "") {
			$errors[] = "Не заполнен Соц. статус";
		}
		if (ArrayVal($response[0], "Person_UAddress_id") == "") {
			$errors[] = "Не заполнен Адрес по месту регистрации";
		}
		if (ArrayVal($response[0], "Polis_Num") == "") {
			$errors[] = "Не заполнен Номер полиса";
		}
		if (ArrayVal($response[0], "Polis_Ser") == "") {
			$errors[] = "Не заполнена Серия полиса";
		}
		if (ArrayVal($response[0], "OrgSmo_id") == "") {
			$errors[] = "Не заполнена Организация, выдавшая полис";
		}
		if (ArrayVal($response[0], "Org_UAddress_id") == "") {
			$errors[] = "Не заполнен Адрес места работы";
		}
		if (ArrayVal($response[0], "Org_INN") == "") {
			$errors[] = "Не заполнен ИНН места работы";
		}
		if (ArrayVal($response[0], "Org_OGRN") == "") {
			$errors[] = "Не заполнена ОГРН места работы";
		}
		if (ArrayVal($response[0], "Okved_id") == "") {
			$errors[] = "Не заполнен ОКВЭД места работы";
		}
		if (count($errors) > 0) {
			// есть ошибки в заведении
			$errString = implode("<br/>", $errors);
			throw new Exception("Проверьте полноту заведения данных у человека!<br/>" . $errString);
		}
		return [
			"Ok",
			ArrayVal($response[0], "Sex_id"),
			ArrayVal($response[0], "Person_Age"),
			ArrayVal($response[0], "Person_Birthday")
		];
	}

	/**
	 * Получение минимальной, максимальной дат
	 *
	 * @param $data
	 * @return mixed|bool
	 */
	function getEvnUslugaDispDopMinMaxDates($data)
	{
		$query = "
			select
				to_char(coalesce(MIN(eudd.EvnUslugaDispDop_setDate), tzgetdate())::date, '{$this->dateTimeForm120}') as mindate,
				to_char(coalesce(MAX(eudd.EvnUslugaDispDop_setDate), tzgetdate())::date, '{$this->dateTimeForm120}') as maxdate
			from v_EvnUslugaDispDop eudd
			where eudd.EvnUslugaDispDop_pid = :EvnPLDispScreen_id
		";
		$queryParams = ["EvnPLDispScreen_id" => $data["EvnPLDispScreen_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result("array");
		return (count($resp) > 0) ? $resp[0] : false;
	}

	/**
	 * Получение результатов
	 * @param $RateType_SysNick
	 * @param $EvnUsluga_id
	 * @return array
	 */
	function getRateData($RateType_SysNick, $EvnUsluga_id)
	{
		$query = "
			select
				rt.RateType_id as \"RateType_id\",
				rvt.RateValueType_SysNick as \"RateValueType_SysNick\",
				EURData.EvnUslugaRate_id as \"EvnUslugaRate_id\",
				EURData.Rate_id as \"Rate_id\"
			from
				v_RateType rt
				left join RateValueType rvt on rvt.RateValueType_id = rt.RateValueType_id
				left join lateral (
					select eur.EvnUslugaRate_id, r.Rate_id
					from
						v_EvnUslugaRate eur
						left join v_Rate r on r.Rate_id = eur.Rate_id
					where r.RateType_id = rt.RateType_id
					  and eur.EvnUsluga_id = :EvnUsluga_id
				    limit 1
				) as EURData on true
			where RateType_SysNick = :RateType_SysNick
		";
		$queryParams = [
			"RateType_SysNick" => $RateType_SysNick,
			"EvnUsluga_id" => $EvnUsluga_id
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return [];
		}
		$resp = $result->result("array");
		return (count($resp) > 0) ? $resp[0] : [];
	}

	/**
	 *  Удаление посещения/осмотра/исследования по доп. диспансеризации
	 */
	function deleteEvnUslugaDispDop($data) {
		$query = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_EvnUslugaDispDop_del(
				EvnUslugaDispDop_id := :EvnUslugaDispDop_id,
				pmUser_id := :pmUser_id
			)
		";
		$result = $this->db->query($query, array(
			'EvnUslugaDispDop_id' => $data['EvnUslugaDispDop_id'],
			'pmUser_id' => $data['pmUser_id']
		))->result('array');

		return array(array('Error_Code' => $result[0]['Error_Code'], 'Error_Msg' => $result[0]['Error_Msg']));
	}

	/**
	 * Сохранение посещения/осмотра/исследования по доп. диспансеризации
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function saveEvnUslugaDispDop($data)
	{
		$this->beginTransaction();
		$SurveyType_Code = $this->getFirstResultFromQuery("select SurveyType_Code from v_SurveyType where SurveyType_id = :SurveyType_id", $data);
		//Проверка на наличие у врача кода ДЛО и специальности https://redmine.swan.perm.ru/issues/47172
		/*if (($data['session']['region']['nick'] == 'kareliya') && isset($data['MedPersonal_id'])) {
			$queryCheckMedPersonal = "
				select
					coalesce(MSF.MedPersonal_Code, '') as \"MedPersonal_DloCode\",
					coalesce(MSF.MedSpecOms_id, '') as \"MedSpecOms_id\",
					coalesce(MSF.Person_Snils, '') as \"Person_Snils\"
				from v_MedStaffFact MSF
				where MSF.MedPersonal_id = :MedPersonal_id
				  and MSF.LpuSection_id = :LpuSection_id
			";
			$res_MP = $this->db->query($queryCheckMedPersonal, $data);
			if (is_object($res_MP)) {
				$result_MP = $res_MP->result("array");
				if (is_array($result_MP) && count($result_MP) > 0) {
					if ($result_MP[0]["Person_Snils"] == "") {
						throw new Exception("У врача не указан СНИЛС");
					}
					if (($result_MP[0]["MedSpecOms_id"] == "") || ($result_MP[0]["MedSpecOms_id"] == 0)) {
						throw new Exception("У врача не указана специальность");
					}
				} else {
					throw new Exception("У врача не указан СНИЛС или специальность");
				}
			}
		}*/
		if (!empty($data["EvnUslugaDispDop_id"])) {
			$proc = "p_EvnUslugaDispDop_upd";
		} else {
			$data["EvnUslugaDispDop_id"] = null;
			$proc = "p_EvnUslugaDispDop_ins";
		}
		if (!empty($data["EvnUslugaDispDop_setTime"])) {
			$data["EvnUslugaDispDop_setDate"] .= " " . $data["EvnUslugaDispDop_setTime"] . ":00.000";
		}
		$data["EvnVizitDispDop_setDate"] = $data["EvnUslugaDispDop_setDate"];
		if (!empty($data["EvnVizitDispDop_didDate"])) {
			$data["EvnVizitDispDop_setDate"] = $data["EvnVizitDispDop_didDate"];
		}
		if (!empty($data["EvnUslugaDispDop_didTime"])) {
			$data["EvnUslugaDispDop_didDate"] .= " " . $data["EvnUslugaDispDop_didTime"] . ":00.000";
		}
		if (!empty($data["EvnUslugaDispDop_disDate"]) && !empty($data["EvnUslugaDispDop_disTime"])) {
			$data["EvnUslugaDispDop_disDate"] .= " " . $data["EvnUslugaDispDop_disTime"] . ":00:000";
		}
		if (empty($data["EvnUslugaDispDop_disDate"])) {
			$data["EvnUslugaDispDop_disDate"] = $data["EvnUslugaDispDop_setDate"];
		}
		$selectString = "
			evnuslugadispdop_id as \"EvnUslugaDispDop_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Msg\"
		";
		$sql = "
			select {$selectString}
			from {$proc}(
			    evnuslugadispdop_id := :EvnUslugaDispDop_id,
			    evnuslugadispdop_pid := :EvnUslugaDispDop_pid,
			    lpu_id := :Lpu_id,
			    server_id := :Server_id,
			    personevn_id := :PersonEvn_id,
			    evnuslugadispdop_setdt := :EvnUslugaDispDop_setDate,
			    evnuslugadispdop_disdt := :EvnUslugaDispDop_disDate,
			    evnuslugadispdop_diddt := :EvnUslugaDispDop_didDate,
			    paytype_id := (select PayType_id from v_PayType where PayType_SysNick = 'transf' limit 1),
			    medpersonal_id := :MedPersonal_id,
			    lpu_uid := :Lpu_uid,
			    lpusection_uid := :LpuSection_id,
			    uslugacomplex_id := :UslugaComplex_id,
			    evndirection_id := :EvnDirection_id,
			    evnprescr_id := null,
			    evnprescrtimetable_id := null,
			    medstafffact_id := :MedStaffFact_id,
			    medspecoms_id := :MedSpecOms_id,
			    lpusectionprofile_id := :LpuSectionProfile_id,
			    diag_id := :Diag_id,
			    examinationplace_id := :ExaminationPlace_id,
			    evnuslugadispdop_examplace := :EvnUslugaDispDop_ExamPlace,
			    surveytype_id := :SurveyType_id,
			    deseasetype_id := :DeseaseType_id,
			    pmuser_id := :pmUser_id
			);
		";
		$res = $this->db->query($sql, $data);
		if (!is_object($res)) {
			$this->rollbackTransaction();
			throw new Exception("Ошибка при выполнении запроса к базе данных (сохранение услуги)");
		}
		$resp = $res->result("array");
		if (!is_array($resp) || count($resp) == 0) {
			$this->rollbackTransaction();
			throw new Exception("Ошибка при сохранении услуги");
		} else if (!empty($resp[0]["Error_Msg"])) {
			$this->rollbackTransaction();
			return $resp;
		}
		$EvnUsluga_id = $resp[0]["EvnUslugaDispDop_id"];
		$inresults = [];
		// учтены изменения после 1 мая 2018. Пустые значения пропускаются
		// blood_sugar_lvl intraocular_tens el_cardiography mammography_scrning mammography_scrning_conducted blood_cholest_lvl uteri_carvix_scrning colposcopy_res biopsy_res fec_occult_blood fec_occult_blood_conducted coloscopy_res
		switch ($SurveyType_Code) {
			case 6:
				$inresults = ["blood_sugar_lvl"];
				break;
			case 8:
				$inresults = ["pressure_measure", "eye_pressure_left", "eye_pressure_right", "intraocular_tens"];
				break;
			case 103:
			case 19:
				$inresults = ["terapevt_vop"];
				break;
			case 17:
				$inresults = ["el_cardiography"];
				break;
			case 21:
				$inresults = ["mammography_scrning", "mammography_scrning_conducted"];
				break;
			case 104:
				$inresults = ["accoucheur_gynecologist"];
				break;
			case 105:
				$inresults = ["electro_cardio_gramm"];
				break;
			case 106:
				$inresults = ["blood_cholest_lvl", "total_cholesterol"];
				break;
			case 107:
				$inresults = ["bio_blood_triglycerid", "glucose"];
				break;
			case 108:
				$inresults = ["uteri_carvix_scrning", "pap_test"];
				break;
			case 109:
				$inresults = ["colposcopy_res", "colposcopy"];
				break;
			case 110:
				$inresults = ["biopsy_res", "biopsy"];
				break;
			case 111:
				$inresults = ["pressure_measure", "eye_pressure_left", "eye_pressure_right"];
				break;
			case 112:
				$inresults = ["fec_occult_blood", "fec_occult_blood_conducted", "gemokult_test"];
				break;
			case 113:
				$inresults = ["rectoromanoscopy"];
				break;
			case 114:
				$inresults = ["res_mammo_graph"];
				break;
			case 115:
				$inresults = ["in_prostate_cancer"];
				break;
			case 116:
				$inresults = ["cancer_stomach"];
				break;
			case 162:
				$inresults = ["coloscopy_res"];
				break;
		}
		foreach ($inresults as $inresult) {
			if (empty($data[$inresult])) {
				continue;
			}
			// получаем идентификатор EvnUslugaRate и тип сохраняемых данных
			$inresultdata = $this->getRateData($inresult, $EvnUsluga_id);
			if (!empty($inresultdata["RateType_id"])) {
				// если такого результата в бд ещё нет, то добавляем
				if (empty($inresultdata["EvnUslugaRate_id"])) {
					// сначала p_Rate_ins
					$sql = "
						select
							rate_id as \"Rate_id\",
							error_code as \"Error_Code\",
							error_message as \"Error_Msg\"
						from p_rate_ins(
						    server_id := :Server_id,
						    rate_id := :Rate_id,
						    ratetype_id := :RateType_id,
						    rate_valueint := :Rate_ValueInt,
						    rate_valuefloat := :Rate_ValueFloat,
						    rate_valuestr := :Rate_ValueStr,
						    rate_valuesis := :Rate_ValuesIs,
						    pmuser_id := :pmUser_id
						);
					";
					$queryParams = [
						"Rate_id" => null,
						"RateType_id" => $inresultdata["RateType_id"],
						"Rate_ValueInt" => null,
						"Rate_ValueFloat" => null,
						"Rate_ValueStr" => null,
						"Rate_ValuesIs" => null,
						"Server_id" => $data["Server_id"],
						"pmUser_id" => $data["pmUser_id"]
					];
					switch ($inresultdata["RateValueType_SysNick"]) {
						case "int":
							$queryParams["Rate_ValueInt"] = $data[$inresult];
							break;
						case "float":
							$queryParams["Rate_ValueFloat"] = $data[$inresult];
							break;
						case "string":
							$queryParams["Rate_ValueStr"] = $data[$inresult];
							break;
						case "reference":
							$queryParams["Rate_ValuesIs"] = $data[$inresult];
							break;
					}
					$res = $this->db->query($sql, $queryParams);
					if (!is_object($res)) {
						$this->rollbackTransaction();
						throw new Exception("Ошибка при выполнении запроса к базе данных (сохранение показателя услуги)");
					}
					$resprate = $res->result("array");
					if (!is_array($resprate) || count($resprate) == 0) {
						$this->rollbackTransaction();
						throw new Exception("Ошибка при сохранении показателя услуги");
					} else if (!empty($resprate[0]["Error_Msg"])) {
						$this->rollbackTransaction();
						return $resprate;
					}
					// затем p_EvnUslugaRate_ins
					$sql = "
						select
							evnuslugarate_id as \"EvnUslugaRate_id\",
							error_code as \"Error_Code\",
							error_message as \"Error_Msg\"
						from p_evnuslugarate_ins(
						    server_id := :Server_id,
						    evnuslugarate_id := :EvnUslugaRate_id,
						    evnusluga_id := :EvnUsluga_id,
						    rate_id := :Rate_id,
						    pmuser_id := :pmUser_id
						);
					";
					$queryParams = [
						"EvnUslugaRate_id" => null,
						"EvnUsluga_id" => $EvnUsluga_id,
						"Rate_id" => $resprate[0]["Rate_id"],
						"Server_id" => $data["Server_id"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$res = $this->db->query($sql, $queryParams);
					if (!is_object($res)) {
						$this->rollbackTransaction();
						throw new Exception("Ошибка при выполнении запроса к базе данных (сохранение показателя услуги)");
					}
					$resp = $res->result('array');
					if (!is_array($resp) || count($resp) == 0) {
						$this->rollbackTransaction();
						throw new Exception("Ошибка при сохранении показателя услуги");
					} else if (!empty($resp[0]['Error_Msg'])) {
						$this->rollbackTransaction();
						return $resp;
					}
				} else {
					// иначе обновляем тот, что есть
					// p_Rate_upd
					$sql = "
						select
							rate_id as \"Rate_id\",
							error_code as \"Error_Code\",
							error_message as \"Error_Msg\"
						from p_rate_upd(
						    server_id := :Server_id,
						    rate_id := :Rate_id,
						    ratetype_id := :RateType_id,
						    rate_valueint := :Rate_ValueInt,
						    rate_valuefloat := :Rate_ValueFloat,
						    rate_valuestr := :Rate_ValueStr,
						    rate_valuesis := :Rate_ValuesIs,
						    pmuser_id := :pmUser_id
						);
					";
					$queryParams = [
						"Rate_id" => $inresultdata["Rate_id"],
						"RateType_id" => $inresultdata["RateType_id"],
						"Rate_ValueInt" => null,
						"Rate_ValueFloat" => null,
						"Rate_ValueStr" => null,
						"Rate_ValuesIs" => null,
						"Server_id" => $data["Server_id"],
						"pmUser_id" => $data["pmUser_id"]
					];
					switch ($inresultdata["RateValueType_SysNick"]) {
						case "int":
							$queryParams["Rate_ValueInt"] = $data[$inresult];
							break;
						case "float":
							$queryParams["Rate_ValueFloat"] = $data[$inresult];
							break;
						case "string":
							$queryParams["Rate_ValueStr"] = $data[$inresult];
							break;
						case "reference":
							$queryParams["Rate_ValuesIs"] = $data[$inresult];
							break;
					}
					$res = $this->db->query($sql, $queryParams);
					if (!is_object($res)) {
						$this->rollbackTransaction();
						throw new Exception("Ошибка при выполнении запроса к базе данных (обновление показателя услуги)");
					}
					$resp = $res->result("array");
					if (!is_array($resp) || count($resp) == 0) {
						$this->rollbackTransaction();
						throw new Exception("Ошибка при обновлении показателя услуги");
					} else if (!empty($resp[0]["Error_Msg"])) {
						$this->rollbackTransaction();
						return $resp;
					}
				}
			}
		}

		$results = empty($data['results'])?[]:json_decode($data['results'], true);

		foreach ($results as $res) {

			$checkrecord = $this->getFirstRowFromQuery("
				select ScreenCheckListResult_id as \"ScreenCheckListResult_id\" from r101.ScreenCheckListResult where EvnPLDisp_id = ? and ScreenCheckList_id = ?
			", [$data['EvnUslugaDispDop_pid'], $res['ScreenCheckList_id']]);

			$proc = 'r101.p_ScreenCheckListResult_upd';

			if (empty($checkrecord)) $proc = 'r101.p_ScreenCheckListResult_ins';

			$params = [
				'ScreenCheckListResult_id' => $checkrecord['ScreenCheckListResult_id'],
				'EvnPLDisp_id' => $data['EvnUslugaDispDop_pid'],
				'SurveyType_id' => $data['SurveyType_id'],
				'pmUser_id' => $data['pmUser_id'],
				'ScreenCheckList_id' => $res['ScreenCheckList_id'],
				'ScreenValue_id' => $res['ScreenValue_id']
			];

			$query = "
				select
					ScreenCheckListResult_id as \"ScreenCheckListResult_id\",
					Error_Code as \"ErrorCode\",
					Error_Message as \"Error_Message\"
				from {$proc}(
					ScreenCheckListResult_id := :ScreenCheckListResult_id,
					EvnPLDisp_id := :EvnPLDisp_id,
					SurveyType_id := :SurveyType_id,
					pmUser_id := :pmUser_id,
					ScreenCheckList_id := :ScreenCheckList_id,
					ScreenValue_id := :ScreenValue_id
				);
			";
			$this->db->query($query, $params);
		}

		$this->commitTransaction();
		return [["EvnUslugaDispDop_id" => $EvnUsluga_id, "Error_Code" => "", "Error_Msg" => ""]];
	}

	/**
	 * Сохранение карты
	 *
	 * @param $data
	 * @return array
	 *
	 * @throws Exception
	 */
	function saveEvnPLDispScreen($data)
	{
		if (!isset($data["EvnPLDispScreen_id"])) {
			$proc = "p_EvnPLDispScreen_ins";
			$data["EvnPLDispScreen_id"] = null;
			$action = "add";
		} else {
			$proc = "p_EvnPLDispScreen_upd";
			$action = null;
		}
		if (!isset($data["AgeGroupDisp_id"])) {
			$data["AgeGroupDisp_id"] = null;
		}
		$data["EvnPLDispScreen_disDate"] = $data["EvnPLDispScreen_setDate"];
		// если не закончен дата окончания нулевая.
		if (empty($data["EvnPLDispScreen_IsEndStage"]) || $data["EvnPLDispScreen_IsEndStage"] == 1) {
			$data["EvnPLDispScreen_disDate"] = NULL;
		}
		// Все проверки актуальны только для законченных случаев
		if ($data["EvnPLDispScreen_IsEndStage"] == 2) {
			// Выгребаем все диагнозы из осмотров
			$sql = "
				select distinct substring(d.Diag_Code, 1, 3) as \"Diag_Code\"
				from
					v_EvnUslugaDispDop eudd
					inner join v_Diag d on d.Diag_id = eudd.Diag_id
				where eudd.EvnUslugaDispDop_rid = :EvnPLDispScreen_id
			";
			$sqlParams = ["EvnPLDispScreen_id" => $data["EvnPLDispScreen_id"]];
			$diags = $this->queryList($sql, $sqlParams);
			$is_a00_t88 = false;
			foreach ($diags as $diag) {
				if ($diag >= "A00" && $diag <= "T88") {
					$is_a00_t88 = true;
				}
			}
			if ($data["EvnPLDispScreen_IsDirectedPMSP"] == 1 && $is_a00_t88) {
				// 1. Проверка значения в поле «Направлен к врачу ПМСП»
				throw new Exception("При диагнозах A00-T88.9 должно быть отмечено «Да» в поле «Направлен к врачу ПМСП");
			}
			if (in_array($data["HealthKind_id"], array(8, 9)) && $is_a00_t88) {
				// 2. Проверка значения в поле «Группа диспансерного наблюдения»
				throw new Exception("При диагнозах A00-T88.9 группа диспансерного наблюдения должна быть II, III");
			}
			if (!array_intersect($diags, ["D26", "N84", "N86", "N87"])) {
				// 3. Проверка результатов исследования Цитологическое исследование шейки матки (Рар-тест)
				$sql = "
					select r.Rate_ValueStr as \"Rate_ValueStr\"
					from
						v_EvnUslugaDispDop eudd
						left join v_EvnUslugaRate eur on eur.EvnUsluga_id = eudd.EvnUslugaDispDop_id
						left join v_Rate r on r.Rate_id = eur.Rate_id and r.RateType_id = 194
					where eudd.EvnUslugaDispDop_rid = :EvnPLDispScreen_id
					  and eudd.SurveyType_id = 108
					limit 1
				";
				$sqlParams = ["EvnPLDispScreen_id" => $data["EvnPLDispScreen_id"]];
				$rar_result = $this->getFirstResultFromQuery($sql, $sqlParams);
				if (in_array($rar_result, [2, 3, 4, 5, 6, 7, 8])) {
					throw new Exception("При указанном результате исследования Цитологическое исследование шейки матки (Рар-тест) требуется указать диагноз  D26, N84, N86, N87");
				}
			}
			// 4. Проверка результатов исследования Биопсия
			$sql = "
				select r.Rate_ValueStr as \"Rate_ValueStr\"
				from
					v_EvnUslugaDispDop eudd
					left join v_EvnUslugaRate eur on eur.EvnUsluga_id = eudd.EvnUslugaDispDop_id
					left join v_Rate r on r.Rate_id = eur.Rate_id and r.RateType_id = 198
				where eudd.EvnUslugaDispDop_rid = :EvnPLDispScreen_id
				  and eudd.SurveyType_id = 110
				limit 1
			";
			$sqlParams = ["EvnPLDispScreen_id" => $data["EvnPLDispScreen_id"]];
			$biopsy_result = $this->getFirstResultFromQuery($sql, $sqlParams);
			if (!array_intersect($diags, ["D26", "N84", "N86", "N87"]) && in_array($biopsy_result, [1, 2, 3, 4])) {
				throw new Exception("При указанном результате исследования Биопсия требуется указать диагноз  D26, N84, N86, N87");
			}
			if (!array_intersect($diags, ["D06"]) && $biopsy_result == 5) {
				throw new Exception("При указанном результате исследования Биопсия требуется указать диагноз  D06");
			}
		}
		$selectString = "
			evnpldispscreen_id as \"EvnPLDispScreen_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$proc}(
			    evnpldispscreen_id := :EvnPLDispScreen_id,
			    lpu_id := :Lpu_id,
			    server_id := :Server_id,
			    personevn_id := :PersonEvn_id,
			    evnpldispscreen_setdt := :EvnPLDispScreen_setDate,
			    evnpldispscreen_disdt := :EvnPLDispScreen_disDate,
			    attachtype_id := 2,
			    dispclass_id := 13,
			    agegroupdisp_id := :AgeGroupDisp_id,
			    healthkind_id := :HealthKind_id,
			    evnpldispscreen_personwaist := :EvnPLDispScreen_PersonWaist,
			    evnpldispscreen_queteletindex := :EvnPLDispScreen_QueteletIndex,
			    evnpldispscreen_arteriasistolpress := :EvnPLDispScreen_ArteriaSistolPress,
			    evnpldispscreen_arteriadiastolpress := :EvnPLDispScreen_ArteriaDiastolPress,
			    evnpldispscreen_isbleeding := :EvnPLDispScreen_IsBleeding,
			    evnpldispscreen_issmoking := :EvnPLDispScreen_IsSmoking,
			    evnpldispscreen_isalco := :EvnPLDispScreen_IsAlco,
			    alcoholingesttype_bid := :AlcoholIngestType_bid,
			    alcoholingesttype_wid := :AlcoholIngestType_wid,
			    alcoholingesttype_vid := :AlcoholIngestType_vid,
			    evnpldispscreen_isparcoronary := :EvnPLDispScreen_IsParCoronary,
			    evnpldispscreen_iscoronary := :EvnPLDispScreen_IsCoronary,
			    evnpldispscreen_isheartache := :EvnPLDispScreen_IsHeartache,
			    evnpldispscreen_isheadache := :EvnPLDispScreen_IsHeadache,
			    evnpldispscreen_ishighpressure := :EvnPLDispScreen_IsHighPressure,
			    evnpldispscreen_isendstage := :EvnPLDispScreen_IsEndStage,
			    evnpldispscreen_isdailyphysact := :EvnPLDispScreen_IsDailyPhysAct,
			    evnpldispscreen_isvisimpair := :EvnPLDispScreen_IsVisImpair,
			    evnpldispscreen_isblurvision := :EvnPLDispScreen_IsBlurVision,
			    evnpldispscreen_ishighmyopia := :EvnPLDispScreen_IsHighMyopia,
			    evnpldispscreen_isglaucoma := :EvnPLDispScreen_IsGlaucoma,
			    evnpldispscreen_ishealthy := :EvnPLDispScreen_IsHealthy,
			    evnpldispscreen_isalcoholabuse := :EvnPLDispScreen_IsAlcoholAbuse,
			    evnpldispscreen_isoverweight := :EvnPLDispScreen_IsOverweight,
			    evnpldispscreen_islowphysact := :EvnPLDispScreen_IsLowPhysAct,
			    evnpldispscreen_isgenpredisposed := :EvnPLDispScreen_IsGenPredisposed,
			    evnpldispscreen_ishypertension := :EvnPLDispScreen_IsHypertension,
			    evnpldispscreen_ishyperlipidemia := :EvnPLDispScreen_IsHyperlipidemia,
			    evnpldispscreen_ishyperglycaemia := :EvnPLDispScreen_IsHyperglycaemia,
			    evnpldispscreen_isdirectedpmsp := :EvnPLDispScreen_IsDirectedPMSP,
			    fecalcasts_id := :FecalCasts_id,
			    evnpldispscreen_isdisability := :EvnPLDispScreen_IsDisability,
			    evnpldispscreen_disabilityyear := :EvnPLDispScreen_DisabilityYear,
			    evnpldispscreen_disabilityperiod := :EvnPLDispScreen_DisabilityPeriod,
			    diag_disid := :Diag_disid,
			    pmuser_id := :pmUser_id
			);
		";
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных (сохранение карты скринингового исследования)");
		}
		$resp = $result->result("array");
		if (!empty($resp[0]["EvnPLDispScreen_id"])) {
			if ($action == 'add' && !empty($data['data']) && $data['withoutAgeGroups']) {
				//сохраняем флаги пройденных ранее обследований
				foreach ($data["data"] as $value) {
					$queryParams = [
						"checked" => $value->checked,
						"EvnPLDisp_id" => $resp[0]["EvnPLDispScreen_id"],
						"SurveyType_id" => $value->SurveyType_id,
						"pmUser_id" => $data["pmUser_id"]
					];
					$query = "
						select
							dopdispinfoconsent_id as \"DopDispInfoConsent_id\",
							error_code as \"Error_Code\",
							error_message as \"Error_Msg\"
						from p_dopdispinfoconsent_ins(
						    evnpldisp_id := :EvnPLDisp_id,
						    surveytype_id := :SurveyType_id,
						    dopdispinfoconsent_isearlier := :checked,
						    pmuser_id := :pmUser_id
						);
					";
					$this->db->query($query, $queryParams);
				}
			}
			// сохраняем рост/вес
			$this->load->model("PersonHeight_model");
			$sql = "select PersonHeight_id from v_PersonHeight where Evn_id = :Evn_id";
			$sqlParams = ["Evn_id" => $resp[0]["EvnPLDispScreen_id"]];
			$data["PersonHeight_id"] = $this->getFirstResultFromQuery($sql, $sqlParams);
			if (empty($data["PersonHeight_id"])) {
				$data["PersonHeight_id"] = null;
			}
			$result = $this->PersonHeight_model->savePersonHeight([
				"Server_id" => $data["Server_id"],
				"PersonHeight_id" => $data["PersonHeight_id"],
				"Person_id" => $data["Person_id"],
				"PersonHeight_setDate" => $data["EvnPLDispScreen_setDate"],
				"PersonHeight_Height" => $data["PersonHeight_Height"],
				"PersonHeight_IsAbnorm" => null,
				"HeightAbnormType_id" => null,
				"HeightMeasureType_id" => null,
				"Evn_id" => $resp[0]["EvnPLDispScreen_id"],
				"pmUser_id" => $data["pmUser_id"]
			]);
			if (!empty($result[0]["Error_Msg"])) {
				throw new Exception($result[0]["Error_Msg"]);
			}
			if (empty($result[0]["PersonHeight_id"])) {
				throw new Exception("Ошибка при сохранении роста");
			}
			$this->load->model("PersonWeight_model");
			$sql = "select PersonWeight_id from v_PersonWeight where Evn_id = :Evn_id";
			$sqlParams = ["Evn_id" => $resp[0]["EvnPLDispScreen_id"]];
			$data["PersonWeight_id"] = $this->getFirstResultFromQuery($sql, $sqlParams);
			if (empty($data["PersonWeight_id"])) {
				$data["PersonWeight_id"] = null;
			}
			$result = $this->PersonWeight_model->savePersonWeight([
				"Server_id" => $data["Server_id"],
				"PersonWeight_id" => $data["PersonWeight_id"],
				"Person_id" => $data["Person_id"],
				"PersonWeight_setDate" => $data["EvnPLDispScreen_setDate"],
				"PersonWeight_Weight" => $data["PersonWeight_Weight"],
				"PersonWeight_IsAbnorm" => null,
				"WeightAbnormType_id" => null,
				"WeightMeasureType_id" => null,
				"Evn_id" => $resp[0]["EvnPLDispScreen_id"],
				"Okei_id" => 37,//кг
				"pmUser_id" => $data["pmUser_id"]
			]);
			if (!empty($result[0]["Error_Msg"])) {
				throw new Exception($result[0]["Error_Msg"]);
			}
			if (empty($result[0]["PersonWeight_id"])) {
				throw new Exception("Ошибка при сохранении роста");
			}

			if (getRegionNick() == 'kz' && $data['ScreenType_id']) {

				$checkrecord = $this->getFirstRowFromQuery("
						select 
							EvnPLDispScreenLink_id as \"EvnPLDispScreenLink_id\",
							EvnPLDispScreenLink_IsProfBegin as \"EvnPLDispScreenLink_IsProfBegin\",
							EvnPLDispScreenLink_IsProfEnd as \"EvnPLDispScreenLink_IsProfEnd\" 
						from 
							r101.v_EvnPLDispScreenLink
						where 
							EvnPLDispScreen_id = ?
					", [$resp[0]['EvnPLDispScreen_id']]);

				$kzScreenProc = 'r101.p_EvnPLDispScreenLink_upd';

				if (empty($checkrecord)) $kzScreenProc = 'r101.p_EvnPLDispScreenLink_ins';

				$queryParams = array(
					'EvnPLDispScreenLink_id' => $checkrecord['EvnPLDispScreenLink_id'],
					'EvnPLDispScreen_id' => $resp[0]['EvnPLDispScreen_id'],
					'ScreenType_id' => $data['ScreenType_id'],
					'EvnPLDispScreenLink_IsProfBegin' => $checkrecord['EvnPLDispScreenLink_IsProfBegin'],
					'EvnPLDispScreenLink_IsProfEnd' => $checkrecord['EvnPLDispScreenLink_IsProfEnd'],
					'pmUser_id' => $data['pmUser_id']
				);

				$query = "
					select
						EvnPLDispScreenLink_id as \"EvnPLDispScreenLink_id\",
						Error_Code as \"ErrorCode\",
						Error_Message as \"Error_Message\"
					from {$kzScreenProc}(
						EvnPLDispScreenLink_id := :EvnPLDispScreenLink_id,
						EvnPLDispScreen_id := :EvnPLDispScreen_id,
						EvnPLDispScreenLink_IsProfBegin := :EvnPLDispScreenLink_IsProfBegin,
						EvnPLDispScreenLink_IsProfEnd := :EvnPLDispScreenLink_IsProfEnd,
						ScreenType_id := :ScreenType_id,
						pmUser_id := :pmUser_id
					);
				";
				$this->db->query($query, $queryParams);

				if ($data['ScreenEndCause_id']) {
					$checkrecord = $this->getFirstRowFromQuery("
						select 
							EvnLinkAPP_id as \"EvnLinkAPP_id\",
							Screening_id as \"Screening_id\"
						from 
							r101.EvnLinkAPP
						where 
							Evn_id = ?
					", [$resp[0]['EvnPLDispScreen_id']]);

					$proc = 'r101.p_EvnLinkAPP_upd';

					if (empty($checkrecord)) $proc = 'r101.p_EvnLinkAPP_ins';

					$this->execCommonSP($proc, [
						'EvnLinkAPP_id' => $checkrecord['EvnLinkAPP_id'] ?? null,
						'Evn_id' => $resp[0]['EvnPLDispScreen_id'],
						'Screening_id' => $checkrecord['Screening_id'] ?? null,
						'pmUser_id' => $data['pmUser_id'],
						'ScreenEndCause_id' => $data['ScreenEndCause_id']
					], 'array_assoc');
				}
			}
		}
		return $resp;
	}

	/**
	 * Получение списка лет, в которые выписывались талоны по ДД с количеством талонов, для комбобокса
	 * 
	 * @param $data
	 * @return array|bool
	 */
	function getEvnPLDispScreenYears($data)
	{
		$sql = "
			select
				count(EPLDP.EvnPLDispScreen_id) as count,
			    extract(year from EPLDP.EvnPLDispScreen_setDate) as \"EvnPLDispScreen_Year\"
			from
				v_PersonState PS
				inner join v_EvnPLDispScreen EPLDP on PS.Person_id = EPLDP.person_id and EPLDP.Lpu_id = :Lpu_id
			where exists (
			    	select PC.personcard_id
			    	from
			    	    v_PersonCard PC
			    	    left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
			    	where PC.Person_id = PS.Person_id
			    	  and PC.Lpu_id = :Lpu_id
				)
			  and extract(year from EPLDP.EvnPLDispScreen_setDate) >= 2013
			  and EPLDP.EvnPLDispScreen_setDate is not null
			GROUP BY extract(year from EPLDP.EvnPLDispScreen_setDate)
			ORDER BY extract(year from EPLDP.EvnPLDispScreen_setDate)
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Проверка, есть ли талон на этого человека в этом году
	 * @param $data
	 * @return array|bool
	 */
	function checkIfEvnPLDispScreenExists($data)
	{
		$sql = "
			select count(EvnPLDispScreen_id) as count
			from v_EvnPLDispScreen
			where Person_id = :Person_id
			  and Lpu_id = :Lpu_id
			  and extract(year from EvnPLDispScreen_setDate) = extract(year from tzgetdate())
		";
		$sqlParams = [
			"Person_id" => $data["Person_id"],
			"Lpu_id" => $data["Lpu_id"]
		];
		$res = $this->db->query($sql, $sqlParams);
		if (!is_object($res)) {
			return false;
		}
		$sel = $res->result("array");
		if ($sel[0]["count"] == 0) {
			return [["isEvnPLDispScreenExists" => false, "Error_Msg" => ""]];
		} else {
			return [["isEvnPLDispScreenExists" => true, "Error_Msg" => ""]];
		}
	}

	/**
	 * Проверка, есть ли талон на этого человека в этом или предыдущем году
	 *
	 * @param $data
	 * @return array|bool
	 */
	function checkIfEvnPLDispScreenExistsInTwoYear($data)
	{
		$sql = "
			select
				case
					when extract(year from EvnPLDispScreen_setDate) = extract(year from :EvnPLDisp_consDate) then 2
					when extract(year from EvnPLDispScreen_setDate) = extract(year from :EvnPLDisp_consDate) - 1 then 1
				end as \"ExistCard\"
			from v_EvnPLDispScreen
			where Person_id = :Person_id
			  and Lpu_id = :Lpu_id
			  and (extract(year from EvnPLDispScreen_setDate) IN (
			                                         extract(year from :EvnPLDisp_consDate),
			                                         extract(year from :EvnPLDisp_consDate) - 1)
			      )
			limit 1
		";
		$sqlParams = [
			"Person_id" => $data["Person_id"],
			"Lpu_id" => $data["Lpu_id"],
			"EvnPLDisp_consDate" => $data["EvnPLDisp_consDate"]
		];
		$res = $this->db->query($sql, $sqlParams);
		if (!is_object($res)) {
			return false;
		}
		$resp = $res->result("array");
		if (!empty($resp[0]["ExistCard"])) {
			if ($resp[0]["ExistCard"] == 2) {
				return ["Error_Msg" => "", "InThisYear" => 1];
			}
			if ($resp[0]["ExistCard"] == 1) {
				return ["Error_Msg" => "", "InPastYear" => 1];
			}
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Проверка на возраст
	 * @param $data
	 * @return string
	 */
	function checkEvnPLDispScreenAge($data)
	{
		// Возраст пациента на конец года от 18 лет
		$sql = "
			select age2(PS.Person_BirthDay, :EvnPLDispScreen_consDate) as \"Person_Age\"
			from v_PersonState PS
			where PS.Person_id = :Person_id
			limit 1
		";
		$res = $this->db->query($sql, $data);
		if (!is_object($res)) {
			return "Ошибка при выполнении запроса к базе данных (строка " . __LINE__ . ")";
		}
		$sel = $res->result("array");
		if (is_array($sel) && count($sel) > 0) {
			if ($sel[0]["Person_Age"] < 18) {
				return "Профосмотр проводится для людей в возрасте с 18 лет";
			}
		} else {
			return "Ошибка при получении возраста пациента";
		}
		return "";
	}

	/**
	 * Получение идентификатора посещения
	 *
	 * @param null $EvnPLDispScreen_id
	 * @param null $DopDispInfoConsent_id
	 * @return bool|null
	 */
	function getEvnVizitDispDopId($EvnPLDispScreen_id = null, $DopDispInfoConsent_id = null)
	{
		$query = "
			select EUDD.EvnUslugaDispDop_pid as \"EvnVizitDispDop_id\"
			from
				v_DopDispInfoConsent DDIC
				inner join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
				inner join v_EvnUslugaDispDop EUDD on EUDD.UslugaComplex_id = STL.UslugaComplex_id
			where DDIC.DopDispInfoConsent_id = :DopDispInfoConsent_id
			  and EUDD.EvnUslugaDispDop_rid = :EvnPLDispScreen_id
			  and coalesce(DDIC.DopDispInfoConsent_IsAgree, 1) = 1
			  and coalesce(DDIC.DopDispInfoConsent_IsEarlier, 1) = 1
			  and coalesce(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
			  and ST.SurveyType_Code <> 49
			limit 1
		";
		$queryParams = [
			"DopDispInfoConsent_id" => $DopDispInfoConsent_id,
			"EvnPLDispScreen_id" => $EvnPLDispScreen_id
		];
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$res = $result->result("array");
		return (is_array($res) && count($res) > 0 && !empty($res[0]["EvnVizitDispDop_id"])) ? $res[0]["EvnVizitDispDop_id"] : null;
	}

	/**
	 * Получаем список исследование - резудьтаты
	 * @param $data
	 * @return array
	 */
	function getEvnUslugaDispDopResult($data) {

		$result = $this->db->query("
			select
				scl.ScreenCheckList_id as \"ScreenCheckList_id\",
				sv.ScreenValue_id as \"ScreenValue_id\", 
				scl.ScreenCheckList_PunktName as \"ScreenCheckList_PunktName\",
				sv.ScreenValue_Name as \"ScreenValue_Name\",
				sclr.ScreenValue_id as \"currentValue\"
			from 
				r101.ScreenCheckList scl
				inner join r101.ScreenValue sv on sv.ScreenCheckList_PunktCode = scl.ScreenCheckList_PunktCode
				left join r101.ScreenCheckListResult sclr on sclr.ScreenCheckList_id = scl.ScreenCheckList_id and sclr.EvnPLDisp_id = ?
			where 
				scl.ScreenType_id = ? and scl.UslugaComplex_id = ?
		", [$data['EvnUslugaDispDop_pid'], $data['ScreenType_id'], $data['UslugaComplex_id']])->result('array');

		$fin = [];

		foreach ($result as $res) {
			if (empty($fin[$res['ScreenCheckList_id']])) {
				$fin[$res['ScreenCheckList_id']] = [];
			}

			$fin[$res['ScreenCheckList_id']][] = [
				'ScreenCheckList_id' => $res['ScreenCheckList_id'],
				'ScreenValue_id' => $res['ScreenValue_id'],
				'ScreenCheckList_PunktName' => $res['ScreenCheckList_PunktName'],
				'ScreenValue_Name' => $res['ScreenValue_Name'],
				'currentValue' => $res['currentValue']
			];
		}

		return $fin;
	}
}