<?php	defined("BASEPATH") or die ("No direct script access allowed");
/**
 * EvnPLDispScreenChild_model - модель для работы с профосмотрами
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
 * @version      20.06.2013
 * 
 * @property CI_DB_driver $db
 * @property EvnDiagDopDisp_model $evndiagdopdisp
 * @property HeredityDiag_model $hereditydiag
 * @property PersonHeight_model $PersonHeight_model
 * @property PersonWeight_model $PersonWeight_model
 *
 */
class EvnPLDispScreenChild_model extends SwPgModel
{
	private $dateTimeForm104 = "DD.MM.YYYY";
	private $dateTimeForm120 = "YYYY-MM-DD";

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Удаление аттрибутов
	 *
	 * @param $attr
	 * @param $EvnPLDispScreenChild_id
	 * @param $pmUser_id
	 *
	 * @throws Exception
	 */
	function deleteAttributes($attr, $EvnPLDispScreenChild_id, $pmUser_id)
	{
		// Сперва получаем список
		if ($attr == "EvnUslugaDispDop") {
			$query = "
				select EUDD.EvnUslugaDispDop_id as id
				from v_EvnUslugaDispDop EUDD
				where EUDD.EvnUslugaDispDop_rid = :EvnPLDispScreenChild_id
			";
		} else {
			$whereString = "EvnPLDisp_id = :EvnPLDispScreenChild_id";
			$query = "
				select {$attr}_id as id
				from v_{$attr}
				where {$whereString}
			";
		}
		$queryParams = ["EvnPLDispScreenChild_id" => $EvnPLDispScreenChild_id];
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		$response = $result->result('array');
		if (is_array($response) && count($response) > 0) {
			foreach ($response as $array) {
				$filter = "{$attr}_id := :id";
				if (in_array($attr, ['EvnUslugaDispDop'])) {
					$filter .= "
						,pmUser_id := :pmUser_id
					";
				}
				$query = "
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_{$attr}_del(
					    {$filter}
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
				$res = $result->result("array");
				if (is_array($res) && count($res) > 0 && !empty($res[0]["Error_Msg"])) {
					throw new Exception($res[0]["Error_Msg"]);
				}
			}
		}
	}

	/**
	 * Получение кода услуги
	 *
	 * @param $data
	 * @return mixed|null
	 *
	 * @throws Exception
	 */
	function getUslugaComplexCode($data)
	{
		$query = "
			select UslugaComplex_Code as \"UslugaComplex_Code\"
			from v_UslugaComplex
			where UslugaComplex_id = :UslugaComplex_id
		";
		$res = $this->db->query($query, $data);
		if (!is_object($res)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		$resp = $res->result("array");
		if (count($resp) > 0) {
			return $resp[0]["UslugaComplex_Code"];
		} else {
			return null;
		}
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
		// Если для расчетной возрастной группы (для детей на текущую дату) на пациента уже создана карта скрининговых исследований
		$sql = "
			select
			    eplds.EvnPLDispScreenChild_id as \"EvnPLDispScreenChild_id\",
			    l.Lpu_Nick as \"Lpu_Nick\",
			    agd.AgeGroupDisp_Name as \"AgeGroupDisp_Name\"
			from
			    v_PersonState ps
			        inner join v_AgeGroupDisp agd on
			                agd.DispType_id = 6
			            and agd.AgeGroupDisp_From <= (select age2(PS.Person_BirthDay, tzgetdate()) from v_PersonState PS where Person_id = :Person_id)
			            and agd.AgeGroupDisp_To >= (select age2(PS.Person_BirthDay, tzgetdate()) from v_PersonState PS where Person_id = :Person_id)
			            and agd.AgeGroupDisp_monthFrom <= (select extract(month from age(tzgetdate(), PS.Person_BirthDay)) from v_PersonState PS where Person_id = :Person_id)
			            and agd.AgeGroupDisp_monthTo >= (select extract(month from age(tzgetdate(), PS.Person_BirthDay)) from v_PersonState PS where Person_id = :Person_id)
			        inner join v_EvnPLDispScreenChild eplds on eplds.AgeGroupDisp_id = agd.AgeGroupDisp_id and eplds.Person_id = ps.Person_id
			        left join v_Lpu l on l.Lpu_id = eplds.Lpu_id
			where ps.Person_id = :Person_id
		";
		$sqlParams = ["Person_id" => $data["Person_id"]];
		$resp = $this->queryResult($sql, $sqlParams);
		if (!empty($resp[0]['EvnPLDispScreenChild_id'])) {
			throw new Exception("На данного пациента в МО " . $resp[0]["Lpu_Nick"] . " уже создана карта скринингового исследования с возрастной группой " . $resp[0]["AgeGroupDisp_Name"]);
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Получение диагноза по коду
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
		if (is_object($result)) {
			$resp = $result->result("array");
			if (count($resp) > 0) {
				return $resp[0]["Diag_id"];
			}
		}
		return false;
	}

	/**
	 * Получение данных карты
	 * @param $data
	 * @return bool
	 */
	function getEvnPLDispScreenChildData($data)
	{
		$query = "
			select 
				EvnPLDispScreenChild_id as \"EvnPLDispScreenChild_id\",
				PersonEvn_id as \"PersonEvn_id\",
				Server_id as \"Server_id\"
			from v_EvnPLDispScreenChild EPLDD
			where EPLDD.EvnPLDispScreenChild_id = :EvnPLDisp_id
			limit 1
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return $resp[0];
			}
		}
		return false;
	}

	/**
	 * Сохранение анкетирования
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function saveDopDispQuestionGrid($data)
	{
		$this->beginTransaction();
		// получаем данные о карте ДД
		$dd = $this->getEvnPLDispScreenChildData($data);
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
					select EvnUslugaDispDop_id
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
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			$this->rollbackTransaction();
			throw new Exception("Ошибка при выполнении запроса к базе данных (получение идентификатора услуги)");
		}
		$resp = $result->result("array");
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
			    paytype_id := (
			        select PayType_id
			        from v_PayType
			        where PayType_SysNick = 'dopdisp'
			        limit 1
			    ),
			    medpersonal_id := null,
			    uslugaplace_id := 1,
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
		/**@var CI_DB_result $result */
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
			$result = $this->db->query($query, $queryParams);
			if (!is_object($result)) {
				$this->rollbackTransaction();
				throw new Exception("Ошибка при выполнении запроса к базе данных (сохранение ответов на вопросы)");
			}
			$resp = $result->result("array");
			if (!is_array($resp) || count($resp) == 0) {
				$this->rollbackTransaction();
				throw new Exception("Ошибка при сохранении ответов на вопросы");
			} else if (!empty($resp[0]["Error_Msg"])) {
				$this->rollbackTransaction();
				throw new Exception($resp[0]["Error_Msg"]);
			}
		}
		$this->commitTransaction();
		return ["success" => true, "Error_Msg" => ""];
	}

	/**
	 * Удаление карты
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function deleteEvnPLDispScreenChild($data)
	{
		$query = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_evnpldispscreenchild_del(
			    evnpldispscreenchild_id := :EvnPLDispScreenChild_id,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"EvnPLDispScreenChild_id" => $data["EvnPLDispScreenChild_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
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
	function loadEvnPLDispScreenChildEditForm($data)
	{
		$accessType = '1=1';
		$query = "
			SELECT
				case when {$accessType} then 'edit' else 'view' end as \"accessType\",
				EPLDS.EvnPLDispScreenChild_id as \"EvnPLDispScreenChild_id\",
				epldscl.ScreenType_id as \"ScreenType_id\",
				elapp.ScreenEndCause_id as \"ScreenEndCause_id\",
				EPLDS.Person_id as \"Person_id\",
				EPLDS.PersonEvn_id as \"PersonEvn_id\",
				EPLDS.Server_id as \"Server_id\",
				coalesce(EPLDS.DispClass_id, 15) as \"DispClass_id\",
				EPLDS.EvnPLDispScreenChild_ArteriaSistolPress as \"EvnPLDispScreenChild_ArteriaSistolPress\",
				EPLDS.EvnPLDispScreenChild_ArteriaDiastolPress as \"EvnPLDispScreenChild_ArteriaDiastolPress\",
				EPLDS.EvnPLDispScreenChild_SystlcPressure as \"EvnPLDispScreenChild_SystlcPressure\",
				EPLDS.EvnPLDispScreenChild_DiastlcPressure as \"EvnPLDispScreenChild_DiastlcPressure\",
				EPLDS.HealthKind_id as \"HealthKind_id\",
				EPLDS.AgeGroupDisp_id as \"AgeGroupDisp_id\",
				EPLDS.EvnPLDispScreenChild_IsLowWeight as \"EvnPLDispScreenChild_IsLowWeight\",
				EPLDS.EvnPLDispScreenChild_Head as \"EvnPLDispScreenChild_Head\",
				EPLDS.EvnPLDispScreenChild_Breast as \"EvnPLDispScreenChild_Breast\",
				EPLDS.EvnPLDispScreenChild_IsActivity as \"EvnPLDispScreenChild_IsActivity\",
				EPLDS.EvnPLDispScreenChild_IsDecreaseEar as \"EvnPLDispScreenChild_IsDecreaseEar\",
				EPLDS.EvnPLDispScreenChild_IsDecreaseEye as \"EvnPLDispScreenChild_IsDecreaseEye\",
				EPLDS.EvnPLDispScreenChild_IsFlatFoot as \"EvnPLDispScreenChild_IsFlatFoot\",
				EPLDS.PsychicalConditionType_id as \"PsychicalConditionType_id\",
				EPLDS.SexualConditionType_id as \"SexualConditionType_id\",
				EPLDS.EvnPLDispScreenChild_IsAbuse as \"EvnPLDispScreenChild_IsAbuse\",
				EPLDS.EvnPLDispScreenChild_IsHealth as \"EvnPLDispScreenChild_IsHealth\",
				EPLDS.EvnPLDispScreenChild_IsPMSP as \"EvnPLDispScreenChild_IsPMSP\",
				EPLDS.EvnPLDispScreenChild_IsEndStage as \"EvnPLDispScreenChild_IsEndStage\",
				PH.PersonHeight_Height as \"PersonHeight_Height\",
				PW.PersonWeight_Weight as \"PersonWeight_Weight\",
				EPLDS.EvnPLDispScreenChild_IsAlco as \"EvnPLDispScreenChild_IsAlco\",
				EPLDS.EvnPLDispScreenChild_IsSmoking as \"EvnPLDispScreenChild_IsSmoking\",
				case when EPLDS.EvnPLDispScreenChild_IsInvalid = 2 then 'true' else 'false' end as \"EvnPLDispScreenChild_IsInvalid\",
				EPLDS.EvnPLDispScreenChild_YearInvalid as \"EvnPLDispScreenChild_YearInvalid\",
				EPLDS.EvnPLDispScreenChild_InvalidPeriod as \"EvnPLDispScreenChild_InvalidPeriod\",
				EPLDS.InvalidDiag_id as \"InvalidDiag_id\",
				to_char(EPLDS.EvnPLDispScreenChild_setDate, '{$this->dateTimeForm104}') as \"EvnPLDispScreenChild_setDate\",
				EPLDS.Lpu_id as \"Lpu_id\"
			from
				v_EvnPLDispScreenChild EPLDS
				left join v_PersonHeight PH on PH.Evn_id = EPLDS.EvnPLDispScreenChild_id
				left join v_PersonWeight PW on PW.Evn_id = EPLDS.EvnPLDispScreenChild_id
				left join r101.v_EvnPLDispScreenChildLink epldscl on epldscl.EvnPLDispScreenChild_id = EPLDS.EvnPLDispScreenChild_id
				left join r101.EvnLinkAPP elapp on elapp.Evn_id = EPLDS.EvnPLDispScreenChild_id
			where EPLDS.EvnPLDispScreenChild_id = :EvnPLDispScreenChild_id
			limit 1
		";
		$queryParams = ["EvnPLDispScreenChild_id" => $data["EvnPLDispScreenChild_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result("array");
		$RiskFactorTypeData = [];
		if (!empty($resp[0]["EvnPLDispScreenChild_id"])) {
			// получаем данные прививок
			$query = "
				select
					ProphConsult_id as \"ProphConsult_id\",
				    RiskFactorType_id as \"RiskFactorType_id\"
				from v_ProphConsult
				where EvnPLDisp_id = :EvnPLDisp_id
			";
			$queryParams = ["EvnPLDisp_id" => $resp[0]["EvnPLDispScreenChild_id"]];
			$resp_vac = $this->queryResult($query, $queryParams);
			foreach ($resp_vac as $resp_vacone) {
				$RiskFactorTypeData[] = $resp_vacone["RiskFactorType_id"];
			}
			if ($this->getRegionNick() == "kz") {
				$InvalidGroup_id = $this->getFirstResultFromQuery("
						select sp_InvalidGroup_id as InvalidGroup_id
						from r101.InvalidGroupLink
						where EvnPLDispScreenChild_id = :EvnPLDispScreenChild_id
					", ["EvnPLDispScreenChild_id" => $resp[0]["EvnPLDispScreenChild_id"]]
				);
				$resp[0]["InvalidGroup_id"] = $InvalidGroup_id ? $InvalidGroup_id : null;
			}
		}
		if (!empty($resp[0])) {
			$resp[0]["RiskFactorTypeData"] = $RiskFactorTypeData;
		}
		return $resp;
	}

	/**
	 * Получение полей карты
	 * @param $data
	 * @return array|bool
	 */
	function getEvnPLDispScreenChildFields($data)
	{
		$query = "
			SELECT
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
				to_char(EPLDD.EvnPLDispScreenChild_disDate, '{$this->dateTimeForm104}') as \"EvnPLDispScreenChild_disDate\"
			FROM
				v_EvnPLDispScreenChild EPLDD
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
			where EPLDD.EvnPLDispScreenChild_id = :EvnPLDispScreenChild_id
			  and EPLDD.Lpu_id = :Lpu_id
			limit 1
		";
		$queryParams = [
			"EvnPLDispScreenChild_id" => $data["EvnPLDispScreenChild_id"],
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
				EUDD.VizitKind_id as \"VizitKind_id\",
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
		if (is_object($result)) {
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
	 * @param $data
	 * @return array|bool
	 */
	function loadEvnUslugaDispDopGrid($data)
	{
		$query = "
			select
				ST.SurveyType_id as \"SurveyType_id\",
				ST.SurveyType_Name as \"SurveyType_Name\",
				ST.SurveyType_Code as \"SurveyType_Code\",
				STLINK.SurveyTypeLink_id as \"SurveyTypeLink_id\",
				eudd.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
				to_char(eudd.EvnUslugaDispDop_setDate, '{$this->dateTimeForm104}') as \"EvnUslugaDispDop_setDate\",
				stlink.UslugaComplex_id as \"UslugaComplex_id\",
				eudd.EvnDirection_id as \"EvnDirection_id\",
				eudd.MedPersonal_id as \"MedPersonal_id\",
				eudd.MedStaffFact_id as \"MedStaffFact_id\",
				eudd.LpuSection_uid as \"LpuSection_id\",
				eudd.Diag_id as \"Diag_id\"
			from
				v_SurveyType ST
				inner join v_PersonState ps on ps.Person_id = :Person_id
				inner join lateral(
					select 
						stl.SurveyTypeLink_id,stl.UslugaComplex_id
					from
						v_SurveyTypeLink STL
						inner join v_AgeGroupDisp AGD on
							AGD.AgeGroupDisp_id = :AgeGroupDisp_id
							and coalesce(STL.SurveyTypeLink_From, dbo.age(Person_BirthDay, dbo.tzGetDate())) <= dbo.age(Person_BirthDay, dbo.tzGetDate())
							and coalesce(STL.SurveyTypeLink_To, dbo.age(Person_BirthDay, dbo.tzGetDate())) >= dbo.age(Person_BirthDay, dbo.tzGetDate())
							and coalesce(STL.SurveyTypeLink_monthFrom, AGD.AgeGroupDisp_monthFrom) <= AGD.AgeGroupDisp_monthFrom
							and coalesce(STL.SurveyTypeLink_monthTo, AGD.AgeGroupDisp_monthTo) >= AGD.AgeGroupDisp_monthTo
							and coalesce(STL.SurveyTypeLink_IsLowWeight, :EvnPLDispScreenChild_IsLowWeight) = :EvnPLDispScreenChild_IsLowWeight
						inner join r101.SurveyTypeScreenLink stsl on stsl.SurveyTypeLink_id = STL.SurveyTypeLink_id
					where STL.DispClass_id = 15
					  	and coalesce(STL.Sex_id, ps.Sex_id) = ps.Sex_id
					  	and STL.SurveyType_id = ST.SurveyType_id
					  	and stsl.ScreenType_id = :ScreenType_id
					limit 1
				) as STLINK on true
				left join v_EvnUslugaDispDop eudd on eudd.EvnUslugaDispDop_pid = :EvnPLDispScreenChild_id and eudd.SurveyType_id = ST.SurveyType_id
		";
		$queryParams = [
			"AgeGroupDisp_id" => $data["AgeGroupDisp_id"],
			"EvnPLDispScreenChild_IsLowWeight" => $data["EvnPLDispScreenChild_IsLowWeight"],
			"Person_id" => $data["Person_id"],
			"EvnPLDispScreenChild_id" => $data["EvnPLDispScreenChild_id"],
			'ScreenType_id' => $data['ScreenType_id']
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Список карт для поточного ввода
	 * @param $data
	 * @return array|bool
	 */
	function loadEvnPLDispScreenChildStreamList($data)
	{
		$filterArray = [];
		$queryParams = [];
		$filterArray[] = "EPL.pmUser_insID = :pmUser_id";
		$queryParams["pmUser_id"] = $data["pmUser_id"];
		if (preg_match("/^\d{2}:\d{2}:\d{2}$/", $data["begTime"])) {
			$filterArray[] = "EPL.EvnPL_insDT >= cast(:date_time as date)";
			$queryParams["date_time"] = $data["begDate"] . " " . $data["begTime"];
		}
		if (isset($data["Lpu_id"])) {
			$filterArray[] = "EPL.Lpu_id = :Lpu_id";
			$queryParams["Lpu_id"] = $data["Lpu_id"];
		}
		$filterArrayString = implode(" and ", $filterArray);
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
			where {$filterArrayString}
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
			from
				v_EvnVizitPL EVPL
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
				(
				    extract(YEAR from PS.Person_Birthday - tzgetdate()) +
				    case when date_part('month', ps.Person_Birthday) > date_part('month', tzgetdate()) or (date_part('month', ps.Person_Birthday) = date_part('month', tzgetdate()) and date_part('day', ps.Person_Birthday) > date_part('day', tzgetdate()))
						then -1
				        else 0
				    end
				) as \"Person_Age\",
				to_char(PS.Person_Birthday, '{$this->dateTimeForm104}') as \"Person_Birthday\"
			from
				v_PersonState ps
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
		if (ArrayVal($response[0], 'Sex_id') == '') {
			$errors[] = 'Не заполнен Пол';
		}
		if (ArrayVal($response[0], 'SocStatus_id') == '') {
			$errors[] = 'Не заполнен Соц. статус';
		}
		if (ArrayVal($response[0], 'Person_UAddress_id') == '') {
			$errors[] = 'Не заполнен Адрес по месту регистрации';
		}
		if (ArrayVal($response[0], 'Polis_Num') == '') {
			$errors[] = 'Не заполнен Номер полиса';
		}
		if (ArrayVal($response[0], 'Polis_Ser') == '') {
			$errors[] = 'Не заполнена Серия полиса';
		}
		if (ArrayVal($response[0], 'OrgSmo_id') == '') {
			$errors[] = 'Не заполнена Организация, выдавшая полис';
		}
		if (ArrayVal($response[0], 'Org_UAddress_id') == '') {
			$errors[] = 'Не заполнен Адрес места работы';
		}
		if (ArrayVal($response[0], 'Org_INN') == '') {
			$errors[] = 'Не заполнен ИНН места работы';
		}
		if (ArrayVal($response[0], 'Org_OGRN') == '') {
			$errors[] = 'Не заполнена ОГРН места работы';
		}
		if (ArrayVal($response[0], 'Okved_id') == '') {
			$errors[] = 'Не заполнен ОКВЭД места работы';
		}
		if (count($errors) > 0) {
			// есть ошибки в заведении
			$errorsString = implode("<br/>", $errors);
			throw new Exception("Проверьте полноту заведения данных у человека!<br/>" . $errorsString);
		}
		return [
			"Ok",
			ArrayVal($response[0], 'Sex_id'),
			ArrayVal($response[0], 'Person_Age'),
			ArrayVal($response[0], 'Person_Birthday')
		];
	}

	/**
	 * Получение минимальной, максимальной дат
	 * @param $data
	 * @return bool
	 */
	function getEvnUslugaDispDopMinMaxDates($data)
	{

		$query = "
			select
				to_char(coalesce(min(eudd.EvnUslugaDispDop_setDate), tzgetdate())::date, '{$this->dateTimeForm120}') as mindate,
				to_char(coalesce(max(eudd.EvnUslugaDispDop_setDate), tzgetdate())::date, '{$this->dateTimeForm120}') as maxdate
			from v_EvnUslugaDispDop eudd
			where eudd.EvnUslugaDispDop_pid = :EvnPLDispScreenChild_id
		";
		$queryParams = ["EvnPLDispScreenChild_id" => $data["EvnPLDispScreenChild_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$resp = $result->result("array");
			if (count($resp) > 0) {
				return $resp[0];
			}
		}
		return false;
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
					where r.RateType_id = rt.RateType_id and eur.EvnUsluga_id = :EvnUsluga_id
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
		if (is_object($result)) {
			$result = $result->result("array");
			if (count($result) > 0) {
				return $result[0];
			}
		}
		return [];
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
		if (($data["session"]["region"]["nick"] == "kareliya") && isset($data["MedPersonal_id"])) {
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
		}
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
			with mv as (
				select
					paytype_id
				from v_paytype
				where paytype_sysnick = 'transf'
				limit 1
			)
			select {$selectString}
			from {$proc}(
			    EvnUslugaDispDop_id = :EvnUslugaDispDop_id,
				EvnUslugaDispDop_pid = :EvnUslugaDispDop_pid,
				SurveyType_id = :SurveyType_id,
				Lpu_id = :Lpu_id,
				Server_id = :Server_id,
				EvnDirection_id = :EvnDirection_id,
				PersonEvn_id = :PersonEvn_id,
				VizitKind_id = :VizitKind_id,
				PayType_id = (select paytype_id from mv),
				EvnUslugaDispDop_setDT = :EvnUslugaDispDop_setDate,
				UslugaComplex_id = :UslugaComplex_id,
				EvnUslugaDispDop_didDT = :EvnUslugaDispDop_didDate,
				EvnUslugaDispDop_disDT = :EvnUslugaDispDop_disDate,
				Lpu_uid = :Lpu_uid,
				Diag_id = :Diag_id,
				DeseaseType_id = :DeseaseType_id,
				LpuSectionProfile_id = :LpuSectionProfile_id,
				MedSpecOms_id = :MedSpecOms_id,
				ExaminationPlace_id = :ExaminationPlace_id,
				LpuSection_uid = :LpuSection_id,
				MedPersonal_id = :MedPersonal_id,
				MedStaffFact_id = :MedStaffFact_id,
				EvnUslugaDispDop_ExamPlace = :EvnUslugaDispDop_ExamPlace,
				EvnPrescrTimetable_id = null,
				EvnPrescr_id = null,
				pmUser_id = :pmUser_id
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
		$inResults = [];
		switch ($SurveyType_Code) {
			case 105:
				$inResults = ["electro_cardio_gramm"];
				break;

			case 112:
				$inResults = ["gemokult_test"];
				break;

			case 106:
				$inResults = ["total_cholesterol"];
				break;

			case 107:
				$inResults = ["bio_blood_triglycerid", "glucose"];
				break;

			case 108:
				$inResults = ["pap_test"];
				break;

			case 114:
				$inResults = ["res_mammo_graph"];
				break;

			case 111:
				$inResults = ["pressure_measure", "eye_pressure_left", "eye_pressure_right"];
				break;
		}
		//if ($data["DispClass_id"] == 15) $inResults = ["survey_result"];
		foreach ($inResults as $inResult) {
			if (!isset($data[$inResult]) || $data[$inResult] == "") {
				$data[$inResult] = null;
			}
			// получаем идентификатор EvnUslugaRate и тип сохраняемых данных
			$inResultData = $this->getRateData($inResult, $EvnUsluga_id);
			if (!empty($inResultData["RateType_id"])) {
				// если такого результата в бд ещё нет, то добавляем
				if (empty($inResultData["EvnUslugaRate_id"])) {
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
						"RateType_id" => $inResultData["RateType_id"],
						"Rate_ValueInt" => null,
						"Rate_ValueFloat" => null,
						"Rate_ValueStr" => null,
						"Rate_ValuesIs" => null,
						"Server_id" => $data["Server_id"],
						"pmUser_id" => $data["pmUser_id"]
					];
					switch ($inResultData["RateValueType_SysNick"]) {
						case "int":
							$queryParams["Rate_ValueInt"] = $data[$inResult];
							break;
						case "float":
							$queryParams["Rate_ValueFloat"] = $data[$inResult];
							break;
						case "string":
							$queryParams["Rate_ValueStr"] = $data[$inResult];
							break;
						case "reference":
							$queryParams["Rate_ValuesIs"] = $data[$inResult];
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
					$resp = $res->result("array");
					if (!is_array($resp) || count($resp) == 0) {
						$this->rollbackTransaction();
						throw new Exception("Ошибка при сохранении показателя услуги");
					} else if (!empty($resp[0]["Error_Msg"])) {
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
						"Rate_id" => $inResultData["Rate_id"],
						"RateType_id" => $inResultData["RateType_id"],
						"Rate_ValueInt" => null,
						"Rate_ValueFloat" => null,
						"Rate_ValueStr" => null,
						"Rate_ValuesIs" => null,
						"Server_id" => $data["Server_id"],
						"pmUser_id" => $data["pmUser_id"]
					];
					switch ($inResultData["RateValueType_SysNick"]) {
						case "int":
							$queryParams["Rate_ValueInt"] = $data[$inResult];
							break;
						case "float":
							$queryParams["Rate_ValueFloat"] = $data[$inResult];
							break;
						case "string":
							$queryParams["Rate_ValueStr"] = $data[$inResult];
							break;
						case "reference":
							$queryParams["Rate_ValuesIs"] = $data[$inResult];
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
				select 
					ScreenCheckListResult_id as \"ScreenCheckListResult_id\" 
				from r101.ScreenCheckListResult 
				where EvnPLDisp_id = ? and ScreenCheckList_id = ?
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
	 * @return array|false
	 *
	 * @throws Exception
	 */
	function saveEvnPLDispScreenChild($data)
	{
		if (!isset($data["EvnPLDispScreenChild_id"])) {
			$proc = "p_EvnPLDispScreenChild_ins";
		} else {
			$proc = "p_EvnPLDispScreenChild_upd";
		}
		// получаем даты начала и конца услуг внутри диспансеризации.
		$minmaxdates = $this->getEvnUslugaDispDopMinMaxDates($data);
		if (is_array($minmaxdates)) {
			$data["EvnPLDispScreenChild_setDate"] = $minmaxdates["mindate"];
			$data["EvnPLDispScreenChild_disDate"] = $minmaxdates["maxdate"];
		} else {
			$data["EvnPLDispScreenChild_setDate"] = date("Y-m-d");
			$data["EvnPLDispScreenChild_disDate"] = date("Y-m-d");
		}
		// если не закончен дата окончания нулевая.
		if (empty($data["EvnPLDispScreenChild_IsEndStage"]) || $data["EvnPLDispScreenChild_IsEndStage"] == 1) {
			$data["EvnPLDispScreenChild_disDate"] = null;
		}
		if (empty($data["ignoreEvnPLDispScreenChildExists"])) {
			// проверяем есть ли уже карта на данного пациента
			$query = "
				select
					epldsc.EvnPLDispScreenChild_id as \"EvnPLDispScreenChild_id\",
					l.Lpu_Nick as \"Lpu_Nick\",
					agd.AgeGroupDisp_Name as \"AgeGroupDisp_Name\"
				from
					v_EvnPLDispScreenChild epldsc
					inner join v_Lpu l on l.Lpu_id = epldsc.Lpu_id
					inner join v_AgeGroupDisp agd on agd.AgeGroupDisp_id = epldsc.AgeGroupDisp_id
				where epldsc.AgeGroupDisp_id = :AgeGroupDisp_id
				  and epldsc.Person_id = :Person_id
				  and (epldsc.EvnPLDispScreenChild_id <> :EvnPLDispScreenChild_id or :EvnPLDispScreenChild_id is null)
			";
			$result = $this->queryResult($query, $data);
			if (!is_array($result)) {
				throw new Exception("Ошибка проверки наличия карт скринингового исследования.");
			}
			if (!empty($result[0]["EvnPLDispScreenChild_id"])) {
				return [
					"Error_Msg" => "",
					"Alert_Code" => "105",
					"Alert_Msg" => "На данного пациента в МО " . $result[0]["Lpu_Nick"] . " уже создана карта скринингового исследования с возрастной группой " . $result[0]["AgeGroupDisp_Name"]
				];
			}
		}
		$data["EvnPLDispScreenChild_IsInvalid"] = (($data["EvnPLDispScreenChild_IsInvalid"]) ? 2 : 1);
		$selectString = "
			evnpldispscreenchild_id as \"EvnPLDispScreenChild_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$proc}(
			    evnpldispscreenchild_id := :EvnPLDispScreenChild_id,
			    lpu_id := :Lpu_id,
			    server_id := :Server_id,
			    personevn_id := :PersonEvn_id,
			    evnpldispscreenchild_setdt := :EvnPLDispScreenChild_setDate,
			    evnpldispscreenchild_disdt := :EvnPLDispScreenChild_disDate,
			    attachtype_id := 2,
			    dispclass_id := 15,
			    agegroupdisp_id := :AgeGroupDisp_id,
			    evnpldispscreenchild_islowweight := :EvnPLDispScreenChild_IsLowWeight,
			    evnpldispscreenchild_head := :EvnPLDispScreenChild_Head,
			    evnpldispscreenchild_breast := :EvnPLDispScreenChild_Breast,
			    evnpldispscreenchild_issmoking := :EvnPLDispScreenChild_IsSmoking,
			    evnpldispscreenchild_isalco := :EvnPLDispScreenChild_IsAlco,
			    evnpldispscreenchild_isactivity := :EvnPLDispScreenChild_IsActivity,
			    evnpldispscreenchild_arteriasistolpress := :EvnPLDispScreenChild_ArteriaSistolPress,
			    evnpldispscreenchild_arteriadiastolpress := :EvnPLDispScreenChild_ArteriaDiastolPress,
			    evnpldispscreenchild_isdecreaseear := :EvnPLDispScreenChild_IsDecreaseEar,
			    evnpldispscreenchild_isdecreaseeye := :EvnPLDispScreenChild_IsDecreaseEye,
			    evnpldispscreenchild_isflatfoot := :EvnPLDispScreenChild_IsFlatFoot,
			    psychicalconditiontype_id := :PsychicalConditionType_id,
			    sexualconditiontype_id := :SexualConditionType_id,
			    evnpldispscreenchild_isabuse := :EvnPLDispScreenChild_IsAbuse,
			    evnpldispscreenchild_ishealth := :EvnPLDispScreenChild_IsHealth,
			    evnpldispscreenchild_ispmsp := :EvnPLDispScreenChild_IsPMSP,
			    healthkind_id := :HealthKind_id,
			    evnpldispscreenchild_isendstage := :EvnPLDispScreenChild_IsEndStage,
			    evnpldispscreenchild_systlcpressure := :EvnPLDispScreenChild_SystlcPressure,
			    evnpldispscreenchild_diastlcpressure := :EvnPLDispScreenChild_DiastlcPressure,
			    evnpldispscreenchild_isinvalid := :EvnPLDispScreenChild_IsInvalid,
			    evnpldispscreenchild_yearinvalid := :EvnPLDispScreenChild_YearInvalid,
			    evnpldispscreenchild_invalidperiod := :EvnPLDispScreenChild_InvalidPeriod,
			    invaliddiag_id := :InvalidDiag_id,
			    pmuser_id := :pmUser_id
			);
		";
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных (сохранение карты скринингового исследования)");
		}
		$resp = $result->result("array");
		if (!empty($resp[0]["EvnPLDispScreenChild_id"])) {
			// надо удалить все услуги, которые больше не подходят по возрасту
			$saved = [];
			$uslugaData = $this->loadEvnUslugaDispDopGrid([
				"Person_id" => $data["Person_id"],
				"AgeGroupDisp_id" => $data["AgeGroupDisp_id"],
				"EvnPLDispScreenChild_IsLowWeight" => $data["EvnPLDispScreenChild_IsLowWeight"],
				'ScreenType_id' => $data['ScreenType_id'],
				"EvnPLDispScreenChild_id" => $resp[0]["EvnPLDispScreenChild_id"]
			]);
			foreach ($uslugaData as $usluga) {
				$saved[] = $usluga["EvnUslugaDispDop_id"];
			}
			$sql = "
				select eudd.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\"
				from v_EvnUslugaDispDop eudd
				where eudd.EvnUslugaDispDop_pid = :EvnPLDispScreenChild_id
			";
			$sqlParams = ["EvnPLDispScreenChild_id" => $resp[0]["EvnPLDispScreenChild_id"]];
			$alluslugaData = $this->queryResult($sql, $sqlParams);
			foreach ($alluslugaData as $usluga) {
				if (!in_array($usluga["EvnUslugaDispDop_id"], $saved)) {
					// удаляем, т.к. не удовлетврояет человеку
					$query = "
						select
							error_code as \"Error_Code\",
							error_message as \"Error_Msg\"
						from p_evnuslugadispdop_del(
						    evnuslugadispdop_id := :EvnUslugaDispDop_id,
						    pmuser_id := :pmUser_id
						);
					";
					$queryParams = [
						"EvnUslugaDispDop_id" => $usluga["EvnUslugaDispDop_id"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$result = $this->db->query($query, $queryParams);
					if (!is_object($result)) {
						throw new Exception("Ошибка при выполнении запроса к базе данных (удаление услуги) (" . __LINE__ . ")");
					}
					$response = $result->result('array');
					if (!is_array($response) || count($response) == 0) {
						throw new Exception("Ошибка при удалении услуги (" . __LINE__ . ")");
					} else if (!empty($response[0]["Error_Msg"])) {
						throw new Exception($response[0]["Error_Msg"] . " (" . __LINE__ . ")");
					}
				}
			}
			// сохраняем поведенческие факторы риска
			if (isset($data["RiskFactorTypeData"]) && is_array($data["RiskFactorTypeData"])) {
				// получаем те, что есть
				$query = "
					select
						ProphConsult_id as \"ProphConsult_id\",
						RiskFactorType_id as \"RiskFactorType_id\"
					from v_ProphConsult
					where EvnPLDisp_id = :EvnPLDisp_id
				";
				$resp_vac = $this->queryResult($query, ["EvnPLDisp_id" => $resp[0]["EvnPLDispScreenChild_id"]]);
				// удаляем тех, что не стало
				$VacExist = [];
				foreach ($resp_vac as $resp_vacone) {
					if (!in_array($resp_vacone["RiskFactorType_id"], $data["RiskFactorTypeData"])) {
						$query = "
							select
								error_code as \"Error_Code\",
								error_message as \"Error_Msg\"
							from p_prophconsult_del(prophconsult_id := :ProphConsult_id);
						";
						$queryParams = [
							"ProphConsult_id" => $resp_vacone["ProphConsult_id"],
							"pmUser_id" => $data["pmUser_id"]
						];
						$resp_vacdel = $this->queryResult($query, $queryParams);
						if (!is_array($resp_vacdel) || count($resp_vacdel) == 0) {
							throw new Exception("Ошибка при удалении прививки");
						} else if (!empty($resp_vacdel[0]["Error_Msg"])) {
							return $resp_vacdel;
						}
					} else {
						$VacExist[] = $resp_vacone["RiskFactorType_id"];
					}
				}
				// сохраняем новые
				foreach ($data["RiskFactorTypeData"] as $RiskFactorType_id) {
					if (!in_array($RiskFactorType_id, $VacExist)) {
						$query = "
							select
								prophconsult_id as \"ProphConsult_id\",
								error_code as \"Error_Code\",
								error_message as \"Error_Msg\"
							from p_prophconsult_ins(
							    evnpldisp_id := :EvnPLDisp_id,
							    riskfactortype_id := :RiskFactorType_id,
							    pmuser_id := :pmUser_id
							);
						";
						$queryParams = [
							"EvnPLDisp_id" => $resp[0]["EvnPLDispScreenChild_id"],
							"RiskFactorType_id" => $RiskFactorType_id,
							"pmUser_id" => $data["pmUser_id"]
						];
						$resp_vacdel = $this->queryResult($query, $queryParams);
						if (!is_array($resp_vacdel) || count($resp_vacdel) == 0) {
							throw new Exception("Ошибка при сохранении прививки");
						} else if (!empty($resp_vacdel[0]['Error_Msg'])) {
							return $resp_vacdel;
						}
					}
				}
			}
			// сохраняем рост/вес
			$this->load->model("PersonHeight_model");
			$data["PersonHeight_id"] = $this->getFirstResultFromQuery(
				"select PersonHeight_id from v_PersonHeight where Evn_id = :Evn_id",
				["Evn_id" => $resp[0]["EvnPLDispScreenChild_id"]]
			);
			if (empty($data["PersonHeight_id"])) {
				$data["PersonHeight_id"] = null;
			}
			$result = $this->PersonHeight_model->savePersonHeight([
				"Server_id" => $data["Server_id"],
				"PersonHeight_id" => $data["PersonHeight_id"],
				"Person_id" => $data["Person_id"],
				"PersonHeight_setDate" => $data["EvnPLDispScreenChild_setDate"],
				"PersonHeight_Height" => $data["PersonHeight_Height"],
				"PersonHeight_IsAbnorm" => null,
				"HeightAbnormType_id" => null,
				"HeightMeasureType_id" => null,
				"Evn_id" => $resp[0]["EvnPLDispScreenChild_id"],
				"pmUser_id" => $data["pmUser_id"]
			]);
			if (!empty($result[0]["Error_Msg"])) {
				throw new Exception($result[0]["Error_Msg"]);
			}
			if (empty($result[0]["PersonHeight_id"])) {
				throw new Exception("Ошибка при сохранении роста");
			}

			$this->load->model("PersonWeight_model");
			$data["PersonWeight_id"] = $this->getFirstResultFromQuery(
				"select PersonWeight_id from v_PersonWeight where Evn_id = :Evn_id",
				["Evn_id" => $resp[0]["EvnPLDispScreenChild_id"]]
			);
			if (empty($data["PersonWeight_id"])) {
				$data["PersonWeight_id"] = null;
			}
			$result = $this->PersonWeight_model->savePersonWeight([
				"Server_id" => $data["Server_id"],
				"PersonWeight_id" => $data["PersonWeight_id"],
				"Person_id" => $data["Person_id"],
				"PersonWeight_setDate" => $data["EvnPLDispScreenChild_setDate"],
				"PersonWeight_Weight" => $data["PersonWeight_Weight"],
				"PersonWeight_IsAbnorm" => null,
				"WeightAbnormType_id" => null,
				"WeightMeasureType_id" => null,
				"Evn_id" => $resp[0]["EvnPLDispScreenChild_id"],
				"Okei_id" => 37,//кг
				"pmUser_id" => $data["pmUser_id"]
			]);
			if (!empty($result[0]["Error_Msg"])) {
				throw new Exception($result[0]["Error_Msg"]);
			}
			if (empty($result[0]["PersonWeight_id"])) {
				throw new Exception("Ошибка при сохранении роста");
			}
			// сохраняем опрос по скрининг-тесту
			if ($this->getRegionNick() == "kz") {
				$InvalidGroupLink_id = $this->getFirstResultFromQuery(
					"select InvalidGroupLink_id from r101.InvalidGroupLink where EvnPLDispScreenChild_id = :EvnPLDispScreenChild_id",
					["EvnPLDispScreenChild_id" => $resp[0]["EvnPLDispScreenChild_id"]]
				);
				$proc = $InvalidGroupLink_id ? "p_InvalidGroupLink_upd" : "p_InvalidGroupLink_ins";
				$selectString = "
					InvalidGroupLink_id as \"InvalidGroupLink_id\",
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				";
				$query = "
					select {$selectString}
					from r101.{$proc}(
						invalidgrouplink_id := :InvalidGroupLink_id,
						evnpldispscreenchild_id := :EvnPLDispScreenChild_id,
						sp_invalidgroup_id := :InvalidGroup_id,
						pmuser_id := :pmUser_id
					);
				";
				$queryParams = [
					"InvalidGroupLink_id" => $InvalidGroupLink_id ? $InvalidGroupLink_id : null,
					"InvalidGroup_id" => $data["InvalidGroup_id"],
					"EvnPLDispScreenChild_id" => $resp[0]["EvnPLDispScreenChild_id"],
					"pmUser_id" => $data["pmUser_id"]
				];
				$this->queryResult($query, $queryParams);

				$checkrecord = $this->getFirstRowFromQuery("
					select 
						EvnPLDispScreenChildLink_id as \"EvnPLDispScreenChildLink_id\",
						EvnPLDispScreenChildLink_IsProfBegin as \"EvnPLDispScreenChildLink_IsProfBegin\",
						EvnPLDispScreenChildLink_IsProfEnd as \"EvnPLDispScreenChildLink_IsProfEnd\" 
					from 
						r101.v_EvnPLDispScreenChildLink
					where 
						EvnPLDispScreenChild_id = ?
				", [$resp[0]['EvnPLDispScreenChild_id']]);

				$kzScreenProc = 'r101.p_EvnPLDispScreenChildLink_upd';

				if (empty($checkrecord)) $kzScreenProc = 'r101.p_EvnPLDispScreenChildLink_ins';

				$queryParams = array(
					'EvnPLDispScreenChildLink_id' => $checkrecord['EvnPLDispScreenChildLink_id'],
					'EvnPLDispScreenChild_id' => $resp[0]['EvnPLDispScreenChild_id'],
					'ScreenType_id' => $data['ScreenType_id'],
					'EvnPLDispScreenChildLink_IsProfBegin' => $checkrecord['EvnPLDispScreenChildLink_IsProfBegin'],
					'EvnPLDispScreenChildLink_IsProfEnd' => $checkrecord['EvnPLDispScreenChildLink_IsProfEnd'],
					'pmUser_id' => $data['pmUser_id']
				);

				$query = "
					select
						EvnPLDispScreenChildLink_id as \"EvnPLDispScreenChildLink_id\",
						Error_Code as \"ErrorCode\",
						Error_Message as \"Error_Message\"
					from {$kzScreenProc}(
						EvnPLDispScreenChildLink_id := :EvnPLDispScreenChildLink_id,
						EvnPLDispScreenChild_id := :EvnPLDispScreenChild_id,
						EvnPLDispScreenChildLink_IsProfBegin := :EvnPLDispScreenChildLink_IsProfBegin,
						EvnPLDispScreenChildLink_IsProfEnd := :EvnPLDispScreenChildLink_IsProfEnd,
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
					", [$resp[0]['EvnPLDispScreenChild_id']]);

					$proc = 'r101.p_EvnLinkAPP_upd';

					if (empty($checkrecord)) $proc = 'r101.p_EvnLinkAPP_ins';

					$this->execCommonSP($proc, [
						'EvnLinkAPP_id' => $checkrecord['EvnLinkAPP_id'] ?? null,
						'Evn_id' => $resp[0]['EvnPLDispScreenChild_id'],
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
	 * @param $data
	 * @return array|bool
	 */
	function getEvnPLDispScreenChildYears($data)
	{
		$sql = "
			select
				count(EPLDP.EvnPLDispScreenChild_id) as count,
			    extract(year from EPLDP.EvnPLDispScreenChild_setDate) as \"EvnPLDispScreenChild_Year\"
			from
				v_PersonState PS
				inner join v_EvnPLDispScreenChild EPLDP on PS.Person_id = EPLDP.Person_id and EPLDP.Lpu_id = :Lpu_id
			where exists (
			    	select personcard_id
			    	from v_PersonCard PC
			    	     left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
				    where PC.Person_id = PS.Person_id
				      and PC.Lpu_id = :Lpu_id
			    )
				and extract(year from EPLDP.EvnPLDispScreenChild_setDate) >= 2013
				and EPLDP.EvnPLDispScreenChild_setDate is not null
			group by
				extract(year from EPLDP.EvnPLDispScreenChild_setDate)
			order by
				extract(year from EPLDP.EvnPLDispScreenChild_setDate)
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
	function checkIfEvnPLDispScreenChildExists($data)
	{
		$sql = "
			SELECT count(EvnPLDispScreenChild_id) as count
			FROM v_EvnPLDispScreenChild
			WHERE Person_id = :Person_id
			  and Lpu_id = :Lpu_id
			  and extract(year from EvnPLDispScreenChild_setDate) = extract(year from tzgetdate())
		";
		$sqlParams = [
			"Person_id" => $data["Person_id"],
			"Lpu_id" => $data["Lpu_id"]
		];
		$res = $this->db->query($sql, $sqlParams);
		if (is_object($res)) {
			return false;
		}
		$sel = $res->result("array");
		if ($sel[0]["count"] == 0) {
			return [["isEvnPLDispScreenChildExists" => false, "Error_Msg" => ""]];
		} else {
			return [["isEvnPLDispScreenChildExists" => true, "Error_Msg" => ""]];
		}
	}

	/**
	 * Проверка, есть ли талон на этого человека в этом или предыдущем году
	 *
	 * @param $data
	 * @return array|bool
	 */
	function checkIfEvnPLDispScreenChildExistsInTwoYear($data)
	{
		$sql = "
			select
				case
					when extract(year from EvnPLDispScreenChild_setDate) = extract(year from :EvnPLDisp_consDate) then 2
				    when extract(year from EvnPLDispScreenChild_setDate) = extract(year from :EvnPLDisp_consDate) - 1 then 1
				end as \"ExistCard\"
			from v_EvnPLDispScreenChild
			where Person_id = :Person_id
			  and Lpu_id = :Lpu_id
			  and extract(year from EvnPLDispScreenChild_setDate) IN (extract(year from :EvnPLDisp_consDate), extract(year from :EvnPLDisp_consDate) - 1)
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
	 *
	 * @param $data
	 * @return string
	 *
	 * @throws Exception
	 */
	function checkEvnPLDispScreenChildAge($data)
	{
		// Возраст пациента на конец года от 18 лет
		$sql = "
			select age2(P.Person_BirthDay, :EvnPLDispScreenChild_consDate) as \"Person_Age\"
			from v_PersonState P
			where P.Person_id = :Person_id
			limit 1
		";
		$res = $this->db->query($sql, $data);
		if (!is_object($res)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных (строка " . __LINE__ . ")");
		}
		$sel = $res->result("array");
		if (is_array($sel) && count($sel) > 0) {
			if ($sel[0]["Person_Age"] < 18) {
				return "Профосмотр проводится для людей в возрасте с 18 лет";
			}
		} else {
			throw new Exception("Ошибка при получении возраста пациента");
		}
		return "";
	}

	/**
	 * Получение идентификатора посещения
	 *
	 * @param null $EvnPLDispScreenChild_id
	 * @param null $DopDispInfoConsent_id
	 * @return bool|null
	 */
	function getEvnVizitDispDopId($EvnPLDispScreenChild_id = null, $DopDispInfoConsent_id = null)
	{
		$query = "
			select EUDD.EvnUslugaDispDop_pid as \"EvnVizitDispDop_id\"
			from
				v_DopDispInfoConsent DDIC
				inner join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
				inner join v_EvnUslugaDispDop EUDD on EUDD.UslugaComplex_id = STL.UslugaComplex_id
			where
				DDIC.DopDispInfoConsent_id = :DopDispInfoConsent_id
				and EUDD.EvnUslugaDispDop_rid = :EvnPLDispScreenChild_id
				and coalesce(DDIC.DopDispInfoConsent_IsAgree, 1) = 1
				and coalesce(DDIC.DopDispInfoConsent_IsEarlier, 1) = 1
			  and coalesce(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				and ST.SurveyType_Code <> 49
			limit 1
		";
		$queryParams = [
			"DopDispInfoConsent_id" => $DopDispInfoConsent_id,
			"EvnPLDispScreenChild_id" => $EvnPLDispScreenChild_id
		];
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$res = $result->result("array");
		if (is_array($res) && count($res) > 0 && !empty($res[0]["EvnVizitDispDop_id"])) {
			return $res[0]["EvnVizitDispDop_id"];
		} else {
			return null;
		}
	}
}