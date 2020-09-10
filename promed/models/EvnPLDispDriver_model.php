<?php
defined('BASEPATH') or die ('No direct script access allowed');
require_once('EvnPLDispAbstract_model.php');
/**
 * EvnPLDispDriver_model - модель для работы с талонами по диспансеризации водителей
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009 - 2016 Swan Ltd.
 *
 * @property EvnDiagDopDisp_model $evndiagdopdisp
 */

class EvnPLDispDriver_model extends EvnPLDispAbstract_model
{
	/**
	 * Конструктор
	 */
	function __construct()
    {
        parent::__construct();
    }

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]["alias"] = "EvnPLDispDriver_id";
		$arr[self::ID_KEY]["label"] = "Идентификатор талона освидетельствования";
		$arr["evnpldispdriver_num"] = ["properties" => [self::PROPERTY_IS_SP_PARAM], "alias" => "EvnPLDispDriver_Num", "label" => "Номер", "save" => "", "type" => "id"];
		$arr["evnpldispdriver_isfinish"] = ["properties" => [self::PROPERTY_IS_SP_PARAM], "alias" => "EvnPLDispDriver_IsFinish", "label" => "Медицинское обследование закончено", "save" => "", "type" => "id"];
		$arr["resultdispdriver_id"] = ["properties" => [self::PROPERTY_IS_SP_PARAM], "alias" => "ResultDispDriver_id", "label" => "Результат", "save" => "", "type" => "id"];
		$arr["evnpldispdriver_medser"] = ["properties" => [self::PROPERTY_IS_SP_PARAM], "alias" => "EvnPLDispDriver_MedSer", "label" => "Заключение - серия", "save" => "trim", "type" => "string"];
		$arr["evnpldispdriver_mednum"] = ["properties" => [self::PROPERTY_IS_SP_PARAM], "alias" => "EvnPLDispDriver_MedNum", "label" => "Заключение - номер", "save" => "trim", "type" => "string"];
		$arr["evnpldispdriver_meddate"] = ["properties" => [self::PROPERTY_IS_SP_PARAM], "alias" => "EvnPLDispDriver_MedDate", "label" => "Заключение - дата", "save" => "", "type" => "date"];
		$arr["evndirection_id"] = ["properties" => [self::PROPERTY_IS_SP_PARAM], "alias" => "EvnDirection_id", "label" => "Направление", "save" => "", "type" => "id"];
		return $arr;
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 190;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return "EvnPLDispDriver";
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 * @throws Exception
	 */
	function updateEvnPLDispDriverIsFinish($id, $value = null)
	{
		if ($value == 2) {
			$test = $this->getFirstResultFromQuery("
				select count(*) as [count]
				from v_EvnUslugaDispDop EUDD (nolock)
					inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
					inner join v_DopDispInfoConsent DDIC (nolock) on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
					inner join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					inner join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
					inner join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
					inner join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
				where EVDD.EvnVizitDispDop_pid = :EvnPLDispDriver_id
				  and R.Rate_ValuesIs is not null
				  and ST.SurveyType_Code in (155,156,157,158)
			", ["EvnPLDispDriver_id" => $id]);
			if (empty($test) || $test < 4) {
				throw new Exception("Случай медицинского освидетельствования водителя не может быть закончен, если не заполнен хотя бы один из результатов осмотров врачей");
			}
		}
		if ($value != 2) {
			$this->_updateAttribute($id, "evnpldispdriver_medser", null);
			$this->_updateAttribute($id, "evnpldispdriver_mednum", null);
			$this->_updateAttribute($id, "evnpldispdriver_meddate", null);
			$this->_updateAttribute($id, "resultdispdriver_id", null);
		}
		return $this->_updateAttribute($id, "evnpldispdriver_isfinish", $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 * @throws Exception
	 */
	function updateResultDispDriverid($id, $value = null)
	{
		if ($value == 1) {
			$test = $this->getFirstResultFromQuery("
				select count(*) as [count]
				from
					v_EvnUslugaDispDop EUDD (nolock)
					inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
					inner join v_DopDispInfoConsent DDIC (nolock) on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
					inner join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					inner join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
					inner join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
					inner join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
				where EVDD.EvnVizitDispDop_pid = :EvnPLDispDriver_id
				  and R.Rate_ValuesIs = 1
				  and ST.SurveyType_Code in (155,156,157,158)
			", ["EvnPLDispDriver_id" => $id]);
			if (empty($test) || $test < 4) {
				throw new Exception("Случай медицинского освидетельствования водителя не может быть закончен с результатом «Отсутствие медицинских противопоказаний к управлению ТС», если результат осмотра хотя бы одного врача не заполнен или выявил противопоказания к управлению ТС");
			}
		}
		return $this->_updateAttribute($id, "resultdispdriver_id", $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 * @throws Exception
	 */
	function updateMedSer($id, $value = null) {
		return $this->_updateAttribute($id, "evnpldispdriver_medser", $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 * @throws Exception
	 */
	function updateMedNum($id, $value = null) {
		return $this->_updateAttribute($id, "evnpldispdriver_mednum", $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 * @throws Exception
	 */
	function updateMedDate($id, $value = null) {
		return $this->_updateAttribute($id, "evnpldispdriver_meddate", $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 * @throws Exception
	 */
	function updateConsDT($id, $value = null)
	{
		$this->_updateAttribute($id, "setdt", $value);
		return $this->_updateAttribute($id, "consdt", $value);
	}

	/**
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function deleteEvnPLDispDriver($data)
	{
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnPLDispDriver_del
				@EvnPLDispDriver_id = :EvnPLDispDriver_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, [
			"EvnPLDispDriver_id" => $data["EvnPLDispDriver_id"],
			"pmUser_id" => $data["pmUser_id"]
		]);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		return $result->result_array();
	}

	/**
	 * Сохранение согласия
	 * @throws Exception
	 */
	function saveDopDispInfoConsent($data)
	{
		/**@var CI_DB_result $result */
		// Стартуем транзакцию
		$this->db->trans_begin();
		$EvnPLDispDopIsNew = false;
		$data["EvnPLDispDriver_IsMobile"] = ($data["EvnPLDispDriver_IsMobile"]) ? 2 : 1;
		$personage = $this->getFirstResultFromQuery("
			select ISNULL(dbo.Age2(Person_Birthday, dbo.tzGetDate()),0) as Person_Age
			from v_PersonState with (nolock)
			where Person_id = :Person_id
		", ["Person_id" => $data["Person_id"]]);
		if ($personage < 15) {
			throw new Exception("Возраст пациента меньше 15 лет. Создание случая медицинского освидетельствования водителя невозможно");
		}
		if (empty($data["EvnPLDispDriver_id"])) {
			$EvnPLDispDopIsNew = true;
			// добавляем новый талон ДД
			$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = :EvnPLDispDriver_id;
				exec p_EvnPLDispDriver_ins
					@EvnPLDispDriver_id = @Res output, 
					@MedStaffFact_id = :MedStaffFact_id, 
					@EvnPLDispDriver_pid = null, 
					@EvnPLDispDriver_rid = null, 
					@Lpu_id = :Lpu_id, 
					@Server_id = :Server_id, 
					@PersonEvn_id = :PersonEvn_id,
					@EvnPLDispDriver_setDT = :EvnPLDispDriver_setDate, 
					@EvnPLDispDriver_disDT = null, 
					@EvnPLDispDriver_didDT = null, 
					@Morbus_id = null, 
					@EvnPLDispDriver_IsSigned = null, 
					@pmUser_signID = null, 
					@EvnPLDispDriver_signDT = null, 
					@EvnPLDispDriver_VizitCount = null, 
					@EvnPLDispDriver_IsFinish = 1, 
					@Person_Age = null, 
					@AttachType_id = 2, 
					@Lpu_aid = null, 
					@EvnPLDispDriver_consDT = :EvnPLDispDriver_consDate,
					@EvnPLDispDriver_IsMobile = :EvnPLDispDriver_IsMobile,
					@Lpu_mid = :Lpu_mid,
					@DispClass_id = :DispClass_id,
					@PayType_id = :PayType_id,
					@EvnPLDispDriver_fid = :EvnPLDispDriver_fid,
					@EvnPLDispDriver_Num = :EvnPLDispDriver_Num,
					@ResultDispDriver_id = null,
					@EvnPLDispDriver_MedSer = null,
					@EvnPLDispDriver_MedNum = null,
					@EvnPLDispDriver_MedDate = null,
					@EvnDirection_id = :EvnDirection_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output
 				select @Res as EvnPLDispDriver_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$result = $this->db->query($query, [
				"EvnPLDispDriver_id" => $data["EvnPLDispDriver_id"],
				"MedStaffFact_id" => !empty($data["session"]["CurARM"]["MedStaffFact_id"]) ? $data["session"]["CurARM"]["MedStaffFact_id"] : null,
				"Lpu_id" => $data["Lpu_id"],
				"Server_id" => $data["Server_id"],
				"PersonEvn_id" => $data["PersonEvn_id"],
				"EvnPLDispDriver_setDate" => $data["EvnPLDispDriver_consDate"],
				"EvnPLDispDriver_consDate" => $data["EvnPLDispDriver_consDate"],
				"EvnPLDispDriver_IsMobile" => $data["EvnPLDispDriver_IsMobile"],
				"Lpu_mid" => $data["Lpu_mid"],
				"DispClass_id" => $data["DispClass_id"],
				"PayType_id" => $data["PayType_id"],
				"EvnPLDispDriver_fid" => $data["EvnPLDispDriver_fid"],
				"EvnPLDispDriver_Num" => $this->getEvnPLDispDriverNumber($data),
				"EvnDirection_id" => !empty($data["EvnDirection_id"]) ? $data["EvnDirection_id"] : null,
				"pmUser_id" => $data["pmUser_id"]
			]);
			if (is_object($result)) {
				$resp = $result->result_array();
				if (!isset($resp[0]["EvnPLDispDriver_id"])) {
					$this->db->trans_rollback();
					return $resp; // иначе выдаем.. там видимо ошибка
				}
				$data["EvnPLDispDriver_id"] = $resp[0]["EvnPLDispDriver_id"];
			}
		}
		$this->load->model("EvnDiagDopDisp_model", "evndiagdopdisp");
		// При наличии карты дисп. учета пациента с периодом действия включающим создаваемую карту ДВН/ПОВН (по дате инф. согласия) добавить диагноз с карты дисп. учета. (refs #22327)
		$query = "
			select
				pd.Diag_id,
				convert(varchar(10), pd.PersonDisp_begDate, 104) as PersonDisp_begDate
			from
				v_PersonDisp pd (nolock)
				inner join v_Diag d (nolock) on d.Diag_id = pd.Diag_id
				left join v_ProfileDiag pdiag (nolock) on pdiag.Diag_id = d.Diag_pid
			where pd.Person_id = :Person_id
			  and (pd.PersonDisp_begDate <= :EvnPLDispDriver_consDate OR pd.PersonDisp_begDate IS NULL)
			  and (pd.PersonDisp_endDate >= :EvnPLDispDriver_consDate OR pd.PersonDisp_endDate IS NULL)
			  and pdiag.ProfileDiagGroup_id IS NULL
		";
		$result = $this->db->query($query, [
			"EvnPLDispDriver_consDate" => $data["EvnPLDispDriver_consDate"],
			"Person_id" => $data["Person_id"]
		]);
		if (is_object($result)) {
			$resp = $result->result_array();
			if (count($resp) > 0 && !empty($resp[0]["Diag_id"])) {
				$data["EvnPLDisp_id"] = $data["EvnPLDispDriver_id"];
				foreach ($resp as $item) {
					$data["EvnDiagDopDisp_setDate"] = !empty($item["PersonDisp_begDate"]) ? date("Y-m-d", strtotime($item["PersonDisp_begDate"])) : null;
					$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $item["Diag_id"]);
				}
			}
		}
		// сохраняем данные по информир. добр. согласию для EvnPLDispDriver_id = $data["EvnPLDispDriver_id"]
		ConvertFromWin1251ToUTF8($data["DopDispInfoConsentData"]);
		$items = json_decode($data["DopDispInfoConsentData"], true);
		$itemsCount = 0;
		// Массив идентификаторов DopDispInfoConsent_id, которые надо удалить
		// Выполняться должно после удаления посещений, т.к. в посещениях сейчас есть ссылка на DopDispInfoConsent
		$DopDispInfoConsentToDel = [];
		// Список идентификаторов DopDispInfoConsent_id, которые
		// https://redmine.swan.perm.ru/issues/29017
		$DopDispInfoConsentList = [];
		foreach ($items as $item) {
			$item["DopDispInfoConsent_IsAgree"] = ((!empty($item["DopDispInfoConsent_IsAgree"]) && $item["DopDispInfoConsent_IsAgree"] == "1") || $item["DopDispInfoConsent_IsAgree"] === true) ? 2 : 1;
			$item["DopDispInfoConsent_IsEarlier"] = ((!empty($item["DopDispInfoConsent_IsEarlier"]) && $item["DopDispInfoConsent_IsEarlier"] == "1") || $item["DopDispInfoConsent_IsEarlier"] === true) ? 2 : 1;
			// получаем идентификатор DopDispInfoConsent_id для SurveyTypeLink_id и EvnPLDisp_id (должна быть только одна запись для каждой пары значений)
			$item["DopDispInfoConsent_id"] = $this->getDopDispInfoConsentForSurveyTypeLink($data["EvnPLDispDriver_id"], $item["SurveyTypeLink_id"]);
			if (!empty($item["DopDispInfoConsent_id"]) && $item["DopDispInfoConsent_id"] > 0) {
				$DopDispInfoConsentList[] = $item["DopDispInfoConsent_id"];
				$proc = "p_DopDispInfoConsent_upd";
			} else {
				$proc = "p_DopDispInfoConsent_ins";
				$item["DopDispInfoConsent_id"] = null;
			}
			// если убирают согласие для удалённого SurveyTypeLink, то удаляем его из DopDispInfoConsent. (refs #21573)
			if (!empty($item["DopDispInfoConsent_id"]) && $item["DopDispInfoConsent_id"] > 0 && !empty($item["SurveyTypeLink_IsDel"]) && $item["SurveyTypeLink_IsDel"] == "2" && $item["DopDispInfoConsent_IsAgree"] == 1) {
				// Удаление перенесено 
				$DopDispInfoConsentToDel[] = $item["DopDispInfoConsent_id"];
			} else {
				if (empty($item["SurveyTypeLink_id"])) {
					$this->db->trans_rollback();
					throw new Exception("Ошибка при сохранении информированного добровольного согласия (отсутсвует ссылка на SurveyTypeLink)");
				}
				$query = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @Res = :DopDispInfoConsent_id;
					exec {$proc}
						@DopDispInfoConsent_id = @Res output, 
						@EvnPLDisp_id = :EvnPLDispDriver_id, 
						@DopDispInfoConsent_IsAgree = :DopDispInfoConsent_IsAgree, 
						@DopDispInfoConsent_IsEarlier = :DopDispInfoConsent_IsEarlier, 
						@SurveyTypeLink_id = :SurveyTypeLink_id, 
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output
					select @Res as DopDispInfoConsent_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->db->query($query, [
					"EvnPLDispDriver_id" => $data["EvnPLDispDriver_id"],
					"DopDispInfoConsent_id" => $item["DopDispInfoConsent_id"],
					"DopDispInfoConsent_IsAgree" => $item["DopDispInfoConsent_IsAgree"],
					"DopDispInfoConsent_IsEarlier" => $item["DopDispInfoConsent_IsEarlier"],
					"SurveyTypeLink_id" => $item["SurveyTypeLink_id"],
					"pmUser_id" => $data["pmUser_id"]
				]);
				if (!is_object($result)) {
					$this->db->trans_rollback();
					throw new Exception("Ошибка при выполнении запроса к базе данных (строка " . __LINE__ . ")");
				}
				$res = $result->result_array();
				if (is_array($res) && count($res) > 0) {
					if (!empty($res[0]["Error_Msg"])) {
						$this->db->trans_rollback();
						throw new Exception($res[0]["Error_Msg"]);
					}
					if (!in_array($res[0]["DopDispInfoConsent_id"], $DopDispInfoConsentList)) {
						$DopDispInfoConsentList[] = $res[0]["DopDispInfoConsent_id"];
					}
				}
			}
		}
		if ($EvnPLDispDopIsNew === false) {
			// Обновляем дату EvnPLDispDriver_consDate и чистим атрибуты на карте, если пациент отказался от ДД
			$query = "
				select top 1
					 EvnPLDispDriver_pid
					,EvnPLDispDriver_rid
					,Lpu_id
					,Server_id
					,PersonEvn_id
					,convert(varchar(20), EvnPLDispDriver_setDT, 120) as EvnPLDispDriver_setDT
					,convert(varchar(20), EvnPLDispDriver_disDT, 120) as EvnPLDispDriver_disDT
					,convert(varchar(20), EvnPLDispDriver_didDT, 120) as EvnPLDispDriver_didDT
					,Morbus_id
					,EvnPLDispDriver_IsSigned
					,EvnPLDispDriver_IndexRep
					,EvnPLDispDriver_IndexRepInReg
					,pmUser_signID
					,EvnPLDispDriver_signDT
					,EvnPLDispDriver_IsFinish
					,Person_Age
					,AttachType_id
					,Lpu_aid
					,DispClass_id
					,EvnPLDispDriver_Num
					,ResultDispDriver_id
					,EvnPLDispDriver_MedSer
					,EvnPLDispDriver_MedNum
					,EvnPLDispDriver_MedDate
					,EvnDirection_id
				from v_EvnPLDispDriver with (nolock)
				where EvnPLDispDriver_id = :EvnPLDispDriver_id
			";
			$result = $this->db->query($query, ["EvnPLDispDriver_id" => $data["EvnPLDispDriver_id"]]);
			if (is_object($result)) {
				$resp = $result->result_array();
				if (is_array($resp) && count($resp) > 0) {
					$resp[0]["EvnPLDispDriver_setDT"] = $data["EvnPLDispDriver_consDate"];
					$resp[0]["EvnPLDispDriver_consDT"] = $data["EvnPLDispDriver_consDate"];
					$resp[0]["pmUser_id"] = $data["pmUser_id"];
					$resp[0]["EvnPLDispDriver_IsMobile"] = $data["EvnPLDispDriver_IsMobile"];
					$resp[0]["Lpu_mid"] = $data["Lpu_mid"];
					$resp[0]["PayType_id"] = $data["PayType_id"];
					$resp[0]["MedStaffFact_id"] = !empty($data["session"]["CurARM"]["MedStaffFact_id"]) ? $data["session"]["CurARM"]["MedStaffFact_id"] : null;
					$query = "
						declare
							@Res bigint,
							@ErrCode int,
							@ErrMessage varchar(4000);
						set @Res = :EvnPLDispDriver_id;
						exec p_EvnPLDispDriver_upd
							@EvnPLDispDriver_id = @Res output,
					";
					foreach ($resp[0] as $key => $value) {
						$query .= "@{$key} = :{$key},";
					}
					$query .= "
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output
 						select @Res as EvnPLDispDriver_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					$resp[0]["EvnPLDispDriver_id"] = $data["EvnPLDispDriver_id"];
					$result = $this->db->query($query, $resp[0]);
					if (is_object($result)) {
						$resp = $result->result_array();
						if (is_array($resp) && count($resp) > 0 && !empty($resp[0]["Error_Msg"])) {
							$this->db->trans_rollback();
							return $resp;
						}
					}
				}
			}
			// Чистим атрибуты и услуги
			// Услуги с отказом и посещения
			$attrArray = ["EvnVizitDispDop"];
			if ($itemsCount == 0) {
				$attrArray[] = "EvnDiagDopDisp"; // Ранее известные имеющиеся заболевания, впервые выявленные заболевания
				$attrArray[] = "HeredityDiag"; // Наследственность по заболеваниям
				$attrArray[] = "ProphConsult"; // Показания к углубленному профилактическому консультированию
				$attrArray[] = "NeedConsult"; // Показания к консультации врача-специалиста
			}
			foreach ($attrArray as $attr) {
				$deleteResult = $this->deleteAttributes($attr, $data["EvnPLDispDriver_id"], $data["pmUser_id"]);
				if (!empty($deleteResult)) {
					$this->db->trans_rollback();
					throw new Exception("{$deleteResult} (строка " . __LINE__ . ")");
				}
			}
		}
		$this->db->trans_commit();
		return [
			"success" => true,
			"Error_Msg" => "",
			"EvnPLDispDriver_id" => $data["EvnPLDispDriver_id"]
		];
	}

	/**
	 * выбрать всё (согласие)
	 * @param $data
	 * @return array
	 */
	function checkAllDopDispInfoConsent($data)
	{
		$query = "
			select
				ISNULL(MAX(DDIC.DopDispInfoConsent_id), -MAX(STL.SurveyTypeLink_id)) as DopDispInfoConsent_id,
				DDIC.DopDispInfoConsent_IsAgree,
				DDIC.DopDispInfoConsent_IsEarlier,
				STL.SurveyTypeLink_id
			from
				v_SurveyTypeLink (nolock) STL
				left join v_DopDispInfoConsent DDIC (nolock) on DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id and DDIC.EvnPLDisp_id = :EvnPLDisp_id
			where STL.DispClass_id = :DispClass_id
			  and ISNULL(STL.SurveyTypeLink_IsDel, 1) = 1
			group by STL.SurveyType_id, STL.SurveyTypeLink_id, DDIC.DopDispInfoConsent_IsAgree, DDIC.DopDispInfoConsent_IsEarlier
			
		";
		/**@var CI_DB_result $result */
		$result = $this->queryResult($query, [
			"EvnPLDisp_id" => $data["EvnPLDispDriver_id"],
			"DispClass_id" => 26
		]);
		foreach ($result as $row) {
			$DopDispInfoConsent_id = (!empty($row["DopDispInfoConsent_id"]) && $row["DopDispInfoConsent_id"] > 0) ? $row["DopDispInfoConsent_id"] : null;
			$proc = (!empty($row["DopDispInfoConsent_id"]) && $row["DopDispInfoConsent_id"] > 0) ? "p_DopDispInfoConsent_upd" : "p_DopDispInfoConsent_ins";
			$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = :DopDispInfoConsent_id;
				exec {$proc}
					@DopDispInfoConsent_id = @Res output, 
					@EvnPLDisp_id = :EvnPLDispDriver_id, 
					@DopDispInfoConsent_IsAgree = :DopDispInfoConsent_IsAgree, 
					@DopDispInfoConsent_IsEarlier = :DopDispInfoConsent_IsEarlier, 
					@SurveyTypeLink_id = :SurveyTypeLink_id, 
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output
				select @Res as DopDispInfoConsent_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$this->db->query($query, [
				"EvnPLDispDriver_id" => $data["EvnPLDispDriver_id"],
				"DopDispInfoConsent_id" => $DopDispInfoConsent_id,
				"DopDispInfoConsent_IsAgree" => $data["DopDispInfoConsent_IsAgree"],
				"DopDispInfoConsent_IsEarlier" => $data["DopDispInfoConsent_IsEarlier"],
				"SurveyTypeLink_id" => $row["SurveyTypeLink_id"],
				"pmUser_id" => $data["pmUser_id"]
			]);
		}
		return [
			"success" => true,
			"Error_Msg" => "",
			"EvnPLDispDriver_id" => $data["EvnPLDispDriver_id"]
		];
	}

	/**
	 * Обновление данных по информир. добр. согласию (штучно)
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function updateDopDispInfoConsent($data)
	{
		if ($data["DopDispInfoConsent_id"] > 0) {
			$SurveyTypeLink_id = $this->getFirstResultFromQuery("
				select SurveyTypeLink_id 
				from v_DopDispInfoConsent (nolock) 
				where DopDispInfoConsent_id = :DopDispInfoConsent_id
			", ["DopDispInfoConsent_id" => $data["DopDispInfoConsent_id"]]);
			if (!$SurveyTypeLink_id) {
				throw new Exception("Ошибка при выполнении обновления данных");
			}
		} else {
			$SurveyTypeLink_id = -$data["DopDispInfoConsent_id"];
			// проверим, не сохраняли ли ранее
			$ddic = $this->getFirstResultFromQuery("
				select DopDispInfoConsent_id 
				from v_DopDispInfoConsent (nolock) 
				where SurveyTypeLink_id = :SurveyTypeLink_id and EvnPLDisp_id = :EvnPLDisp_id
			", [
				"SurveyTypeLink_id" => $SurveyTypeLink_id,
				"EvnPLDisp_id" => $data["EvnPLDispDriver_id"]
			]);
			if ($ddic && $ddic > 0) {
				$data["DopDispInfoConsent_id"] = $ddic;
			}
		}
		$DopDispInfoConsent_id = (!empty($data["DopDispInfoConsent_id"]) && $data["DopDispInfoConsent_id"] > 0)?$data["DopDispInfoConsent_id"]:null;
		$proc = (!empty($data["DopDispInfoConsent_id"]) && $data["DopDispInfoConsent_id"] > 0)?"p_DopDispInfoConsent_upd":"p_DopDispInfoConsent_ins";
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :DopDispInfoConsent_id;
			exec {$proc}
				@DopDispInfoConsent_id = @Res output, 
				@EvnPLDisp_id = :EvnPLDispDriver_id, 
				@DopDispInfoConsent_IsAgree = :DopDispInfoConsent_IsAgree, 
				@DopDispInfoConsent_IsEarlier = :DopDispInfoConsent_IsEarlier, 
				@SurveyTypeLink_id = :SurveyTypeLink_id, 
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output
			select @Res as DopDispInfoConsent_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, [
			"EvnPLDispDriver_id" => $data["EvnPLDispDriver_id"],
			"DopDispInfoConsent_id" => $DopDispInfoConsent_id,
			"DopDispInfoConsent_IsAgree" => $data["DopDispInfoConsent_IsAgree"],
			"DopDispInfoConsent_IsEarlier" => $data["DopDispInfoConsent_IsEarlier"],
			"SurveyTypeLink_id" => $SurveyTypeLink_id,
			"pmUser_id" => $data["pmUser_id"]
		]);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		return $result->result_array();
	}

	/**
	 * Получение идентификатора из списка добровольного информированного согласия по $SurveyTypeLink_id
	 * @param $EvnPLDisp_id
	 * @param $SurveyTypeLink_id
	 * @return mixed|null
	 */
	function getDopDispInfoConsentForSurveyTypeLink($EvnPLDisp_id, $SurveyTypeLink_id)
	{
		$query = "
			select top 1 DopDispInfoConsent_id
			from v_DopDispInfoConsent (nolock)
			where EvnPLDisp_id = :EvnPLDisp_id
			  and SurveyTypeLink_id = :SurveyTypeLink_id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, [
			"EvnPLDisp_id" => $EvnPLDisp_id,
			"SurveyTypeLink_id" => $SurveyTypeLink_id
		]);
		if (!is_object($result)) {
			return null;
		}
		$resp = $result->result_array();
		return (count($resp) > 0) ? $resp[0]["DopDispInfoConsent_id"] : null;
	}

	/**
	 * Получение данных для отображения в ЭМК
	 * @param $data
	 * @return array|bool
	 */
	function getEvnPLDispDriverViewData($data)
	{
		$queryParams = [
			"EvnPLDisp_id" => $data["EvnPLDisp_id"],
			"EvnPLDispDriver_id" => $data["EvnPLDisp_id"]
		];
		$accessType = "'edit' as accessType,";
		$query = "
			select
				EPLDD.EvnPLDispDriver_id,
				IIF(EPLDD.MedStaffFact_id is not null, ISNULL(l.Lpu_Nick + ' ', '') + ISNULL(ls.LpuSection_Name + ' ', '') + ISNULL(msf.Person_Fio, ''), ISNULL(l.Lpu_Nick + ' ', '') + ISNULL(pu.pmUser_Name, '')) as AuthorInfo,
				'EvnPLDispDriver' as Object,
				EPLDD.DispClass_id,
				EPLDD.Person_id,
				EPLDD.EvnPLDispDriver_IsSigned,
				EPLDD.PersonEvn_id,
				EPLDD.Server_id,
				dc.DispClass_Code,
				dc.DispClass_Name,
				l.Lpu_Nick,
				{$accessType}
				EPLDD.PayType_id,
				pt.PayType_Name,
				convert(varchar(10), EPLDD.EvnPLDispDriver_setDT, 104) as EvnPLDispDriver_setDate,
				convert(varchar(10), EPLDD.EvnPLDispDriver_consDT, 104) as EvnPLDispDriver_consDate,
				EPLDD.EvnPLDispDriver_IsFinish,
				IsFinish.YesNo_Name as EvnPLDispDriver_IsFinish_Name,
				EPLDD.EvnPLDispDriver_Num,
				EPLDD.ResultDispDriver_id,
				RDD.ResultDispDriver_Name,
				EPLDD.EvnPLDispDriver_MedSer,
				EPLDD.EvnPLDispDriver_MedNum,
				convert(varchar(10), EPLDD.EvnPLDispDriver_MedDate, 104) as EvnPLDispDriver_MedDate,
				et.ElectronicTalon_id,
				et.ElectronicService_id,
				et.ElectronicTalonStatus_id,
				eqi.ElectronicQueueInfo_IsOff,
				IIF(eqil.ElectronicService_id = et.ElectronicService_id, 2, 1) as ElectronicQueueInfo_IsLast,
				IIF(ddic.SurveyTypeLink_id is null, 'checked', '') as EvnPLDispDriver_allChecked
			from
				v_EvnPLDispDriver EPLDD (nolock)
				left join v_Lpu l (nolock) on l.Lpu_id = EPLDD.Lpu_id
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = EPLDD.MedStaffFact_id
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = msf.LpuSection_id
				left join v_pmUser pu (nolock) on pu.pmUser_id = EPLDD.pmUser_updID
				left join v_DispClass dc (nolock) on dc.DispClass_id = EPLDD.DispClass_id
				left join v_PayType pt (nolock) on pt.PayType_id = EPLDD.PayType_id
				left join v_YesNo IsFinish (nolock) on IsFinish.YesNo_id = EPLDD.EvnPLDispDriver_IsFinish
				left join v_ResultDispDriver RDD (nolock) on RDD.ResultDispDriver_id = EPLDD.ResultDispDriver_id
				left join v_ElectronicTalon et (nolock) on et.EvnDirection_id = epldd.EvnDirection_id
				left join v_ElectronicQueueInfo eqi (nolock) on eqi.ElectronicQueueInfo_id = et.ElectronicQueueInfo_id
				left join v_MedServiceElectronicQueue mseq (nolock) on et.ElectronicService_id = mseq.ElectronicService_id
				left join v_MedServiceMedPersonal msp (nolock) on msp.MedServiceMedPersonal_id = mseq.MedServiceMedPersonal_id
				outer apply (
					select top 1 STL.SurveyTypeLink_id
					from
						v_SurveyTypeLink (nolock) STL
						left join v_DopDispInfoConsent DDIC (nolock) on DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id and DDIC.EvnPLDisp_id = EPLDD.EvnPLDispDriver_id
					where STL.DispClass_id = EPLDD.DispClass_id
					  and ISNULL(STL.SurveyTypeLink_IsDel, 1) = 1
					  and (DDIC.DopDispInfoConsent_id is null or DDIC.DopDispInfoConsent_IsAgree != 2)
				) ddic
				outer apply (
					select top 1 es.ElectronicService_id
					from
						v_MedServiceElectronicQueue mseq (nolock)
						inner join v_ElectronicService es (nolock) on es.ElectronicService_id = mseq.ElectronicService_id
						inner join v_MedServiceMedPersonal msmp (nolock) on msmp.MedServiceMedPersonal_id = mseq.MedServiceMedPersonal_id
					where msmp.MedService_id = msp.MedService_id
					order by es.ElectronicService_Num desc
				) eqil
			where EPLDD.EvnPLDispDriver_id = :EvnPLDisp_id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result_array();
		// данные для чекбоксов
		$resp[0]["DriverCategory"] = $this->getDriverCategory($queryParams);
		$resp[0]["DriverMedicalClose"] = $this->getDriverMedicalClose($queryParams);
		$resp[0]["DriverMedicalIndication"] = $this->getDriverMedicalIndication($queryParams);
		return $resp;
	}

	/**
	 * Получение данных для формы редактирования карты
	 * @param $data
	 * @return array|bool
	 */
	function loadEvnPLDispDriverEditForm($data)
	{
		$accessType = "
			case
				when EPLDD.Lpu_id = :Lpu_id then 1
				" . (count($data["session"]["linkedLpuIdList"]) > 1 ? "when EPLDD.Lpu_id in (" . implode(",", $data["session"]["linkedLpuIdList"]) . ") and ISNULL(EPLDD.EvnPLDispDriver_IsTransit, 1) = 2 then 1" : "") . "
				else 0
			end = 1
		";
		$query = "
			SELECT TOP 1
				IIF({$accessType}, 'edit', 'view') as accessType,
				EPLDD.EvnPLDispDriver_id,
				EPLDD.EvnPLDispDriver_IsSigned,
				ISNULL(EPLDD.EvnPLDispDriver_IsPaid, 1) as EvnPLDispDriver_IsPaid,
				ISNULL(EPLDD.EvnPLDispDriver_IndexRep, 0) as EvnPLDispDriver_IndexRep,
				EPLDD.EvnPLDispDriver_fid,
				EPLDD.Person_id,
				EPLDD.PersonEvn_id,
				EPLDD.EvnDirection_id,
				ISNULL(EPLDD.DispClass_id, 26) as DispClass_id,
				EPLDD.PayType_id,
				EPLDD.EvnPLDispDriver_pid,
				convert(varchar(10), EPLDD.EvnPLDispDriver_setDate, 104) as EvnPLDispDriver_setDate,
				convert(varchar(10), EPLDD.EvnPLDispDriver_disDate, 104) as EvnPLDispDriver_disDate,
				convert(varchar(10), EPLDD.EvnPLDispDriver_consDT, 104) as EvnPLDispDriver_consDate,
				EPLDD.Server_id,
				IIF(EPLDD.EvnPLDispDriver_IsMobile = 2, 1, 0) as EvnPLDispDriver_IsMobile,
				EPLDD.Lpu_mid,
				EPLDD.EvnPLDispDriver_IsFinish,
				EPLDD.EvnPLDispDriver_Num,
				EPLDD.ResultDispDriver_id,
				EPLDD.EvnPLDispDriver_MedSer,
				EPLDD.EvnPLDispDriver_MedNum,
				convert(varchar(10), EPLDD.EvnPLDispDriver_MedDate, 104) as EvnPLDispDriver_MedDate
			FROM v_EvnPLDispDriver EPLDD (nolock)
			WHERE EPLDD.EvnPLDispDriver_id = :EvnPLDispDriver_id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, ["Lpu_id" => $data["Lpu_id"], "EvnPLDispDriver_id" => $data["EvnPLDispDriver_id"]]);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result_array();
		// данные для чекбоксов
		$resp[0]["DriverCategory"] = $this->getDriverCategory($data);
		$resp[0]["DriverMedicalClose"] = $this->getDriverMedicalClose($data);
		$resp[0]["DriverMedicalIndication"] = $this->getDriverMedicalIndication($data);
		return $resp;
	}

	/**
	 * Загрузка согласий
	 * @param $data
	 * @return array|bool
	 */
	function loadDopDispInfoConsent($data)
	{
		$filter = "";
		$joinList = [];
		$params = [
			"EvnPLDispDriver_id" => $data["EvnPLDispDriver_id"],
			"Person_id" => $data["Person_id"],
			"DispClass_id" => $data["DispClass_id"],
			"EvnPLDispDriver_consDate" => $data["EvnPLDispDriver_consDate"]
		];
		if (!empty($data["EvnPLDispDriver_consDate"])) {
			$filter .= "				
				and (STL.SurveyTypeLink_begDate is null or STL.SurveyTypeLink_begDate <= :EvnPLDispDriver_consDate)
				and (STL.SurveyTypeLink_endDate is null or STL.SurveyTypeLink_endDate >= :EvnPLDispDriver_consDate)
			";
		}
		$query = "
			Declare @sex_id bigint, @age int
			select top 1
				@sex_id = ISNULL(Sex_id, 3),
				@age = dbo.Age2(Person_BirthDay, cast(substring(:EvnPLDispDriver_consDate, 1, 4) + '-12-31' as datetime))
			from v_PersonState ps (nolock)
			where ps.Person_id = :Person_id
			select
				ISNULL(MAX(DDIC.DopDispInfoConsent_id), -MAX(STL.SurveyTypeLink_id)) as DopDispInfoConsent_id,
				MAX(DDIC.EvnPLDisp_id) as EvnPLDispDriver_id,
				MAX(STL.SurveyTypeLink_id) as SurveyTypeLink_id,
				ISNULL(MAX(STL.SurveyTypeLink_IsNeedUsluga), 1) as SurveyTypeLink_IsNeedUsluga,
				ISNULL(MAX(STL.SurveyTypeLink_IsDel), 1) as SurveyTypeLink_IsDel,
				MAX(ST.SurveyType_Code) as SurveyType_Code,
				MAX(ST.SurveyType_Name) as SurveyType_Name,
				IIF(MAX(DDIC.DopDispInfoConsent_IsAgree) = 2, 1, 0) as DopDispInfoConsent_IsAgree,
				IIF(MAX(DDIC.DopDispInfoConsent_IsEarlier) = 2, 1, 0) as DopDispInfoConsent_IsEarlier,
				IIF(MAX(ST.SurveyType_Code) IN (49), 0, 1),
				STL.DispClass_id
			from
				v_SurveyTypeLink STL (nolock)
				left join v_DopDispInfoConsent DDIC (nolock) on DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id and DDIC.EvnPLDisp_id = :EvnPLDispDriver_id
				left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = STL.UslugaComplex_id
				left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				outer apply (
					select top 1 EvnUslugaDispDop_id
					from v_EvnUslugaDispDop with (nolock)
					where UslugaComplex_id = UC.UslugaComplex_id
					  and EvnUslugaDispDop_rid = :EvnPLDispDriver_id
					  and ISNULL(EvnUslugaDispDop_IsVizitCode, 1) = 1
				) EUDD
				" . implode(' ', $joinList) . "
			where IsNull(STL.DispClass_id, :DispClass_id) = :DispClass_id -- тип
			  and (ISNULL(STL.SurveyTypeLink_IsDel, 1) = 1 or (EUDD.EvnUslugaDispDop_id is not null and DDIC.DopDispInfoConsent_id IS NOT NULL))
			  {$filter}
			group by ST.SurveyType_id, STL.SurveyTypeLink_IsDel, STL.DispClass_id
			/*order by case when MAX(ST.SurveyType_Code) IN (49) then 0 else 1 end, MAX(ST.SurveyType_Code)*/
			
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Получение списка исследований в талоне
	 * Входящие данные: $data['EvnPLDispDriver_id']
	 * На выходе: ассоциативный массив результатов запроса
	 * @param $data
	 * @return array|bool
	 */
	function loadEvnUslugaDispDopGrid($data)
	{
		$query = "
			select
				DDIC.DopDispInfoConsent_id,
				DDIC.DopDispInfoConsent_IsAgree,
				DDIC.DopDispInfoConsent_IsEarlier,
				STL.SurveyTypeLink_id,
				ST.SurveyType_Name,
				ST.SurveyType_Code,
				ISNULL(ST.SurveyType_IsVizit, 1) as SurveyType_IsVizit,
				EUDDData.EvnUslugaDispDop_id,
				EUDDData.EvnUslugaDispDop_ExamPlace,
				convert(varchar(20), EUDDData.EvnUslugaDispDop_setDate + EUDDData.EvnUslugaDispDop_setTime, 113) as EvnUslugaDispDop_setDate,
				convert(varchar(10), EUDDData.EvnUslugaDispDop_didDate, 104) as EvnUslugaDispDop_didDate,
				IIF(el.Evn_lid is not null, 'true', 'false') as EvnUslugaDispDop_WithDirection,
				ep.EvnPrescr_id,
				isnull(ep.EvnPrescr_pid, ed.EvnDirection_pid) as EvnPrescr_pid,
				ep.PrescriptionType_id,
				ST.OrpDispSpec_id,
				isnull(epd.EvnDirection_id, ed.EvnDirection_id) as EvnDirection_id,
				EvnXmlData.EvnXml_id,
				uc.UslugaComplex_Code,
				STL.DispClass_id,
				isnull(ep.EvnPrescr_Count, 0)+isnull(ed.EvnDirection_Count,0) as DispDopDirections_Count,
				ep.EvnPrescr_Count,
				ed.EvnDirection_Count
			from v_DopDispInfoConsent DDIC (nolock)
				left join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = STL.UslugaComplex_id
				left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				outer apply(
					select top 1
						COUNT(*) OVER() as EvnPrescr_Count,
						EvnPrescr_id,
						EvnPrescr_pid,
						PrescriptionType_id
					from v_EvnPrescr (nolock)
					where DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
				) ep
				outer apply(
					select top 1
						COUNT(*) OVER() as EvnDirection_Count,
						EvnDirection_id,
						EvnDirection_pid
					from v_EvnDirection edoa (nolock)
					where edoa.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
					  and edoa.EvnStatus_id not in (12,13)
				) ed
				left join v_EvnPrescrDirection epd (nolock) on epd.EvnPrescr_id = ep.EvnPrescr_id
				outer apply(
					select top 1
						EUDD.EvnUslugaDispDop_id,
						EUDD.EvnUslugaDispDop_setDate,
						EUDD.EvnUslugaDispDop_setTime,
						EUDD.EvnUslugaDispDop_didDate,
						EUDD.EvnUslugaDispDop_ExamPlace
					from v_EvnUslugaDispDop EUDD (nolock)
					where EUDD.EvnUslugaDispDop_pid = :EvnPLDispDriver_id
					  and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				) EUDDData
				outer apply(
					select top 1 ex.EvnXml_id
					from
						v_EvnXml ex (nolock)
						inner join v_EvnUslugaPar eup (nolock) on ex.Evn_id = eup.EvnUslugaPar_id
					where eup.EvnPrescr_id = ep.EvnPrescr_id
				) EvnXmlData
				left join v_EvnLink el (nolock) on el.Evn_id = EUDDData.EvnUslugaDispDop_id
			where DDIC.EvnPLDisp_id = :EvnPLDispDriver_id
			  and ST.SurveyType_Code = 2
			  and (DDIC.DopDispInfoConsent_IsAgree = 2 OR DDIC.DopDispInfoConsent_IsEarlier = 2)
			  and (ISNULL(STL.SurveyTypeLink_IsDel, 1) = 1 or EUDDData.EvnUslugaDispDop_id is not null ) -- только если сохранено согласие
			union
			select
				DDIC.DopDispInfoConsent_id,
				DDIC.DopDispInfoConsent_IsAgree,
				DDIC.DopDispInfoConsent_IsEarlier,
				STL.SurveyTypeLink_id,
				ST.SurveyType_Name,
				ST.SurveyType_Code,
				ISNULL(ST.SurveyType_IsVizit, 1) as SurveyType_IsVizit,
				EUDDData.EvnUslugaDispDop_id,
				EUDDData.EvnUslugaDispDop_ExamPlace,
				convert(varchar(20), EUDDData.EvnUslugaDispDop_setDate + EUDDData.EvnUslugaDispDop_setTime, 113) as EvnUslugaDispDop_setDate,
				convert(varchar(10), EUDDData.EvnUslugaDispDop_didDate, 104) as EvnUslugaDispDop_didDate,
				IIF(el.Evn_lid is not null, 'true', 'false') as EvnUslugaDispDop_WithDirection,
				ep.EvnPrescr_id,
				isnull(ep.EvnPrescr_pid, ed.EvnDirection_pid) as EvnPrescr_pid,
				ep.PrescriptionType_id,
				ST.OrpDispSpec_id,
				isnull(epd.EvnDirection_id, ed.EvnDirection_id) as EvnDirection_id,
				EvnXmlData.EvnXml_id,
				uc.UslugaComplex_Code,
				STL.DispClass_id,
				isnull(ep.EvnPrescr_Count, 0)+isnull(ed.EvnDirection_Count,0) as DispDopDirections_Count,
				ep.EvnPrescr_Count,
				ed.EvnDirection_Count
			from
				v_DopDispInfoConsent DDIC (nolock)
				left join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = STL.UslugaComplex_id
				left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				outer apply(
					select top 1
						COUNT(*) OVER() as EvnPrescr_Count,
						EvnPrescr_id,
						EvnPrescr_pid,
						PrescriptionType_id
					from v_EvnPrescr (nolock)
					where DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
				) ep
				outer apply(
					select top 1
						COUNT(*) OVER() as EvnDirection_Count,
						EvnDirection_id,
						EvnDirection_pid
					from v_EvnDirection edoa (nolock)
					where edoa.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
					  and edoa.EvnStatus_id not in (12,13)
				) ed
				left join v_EvnPrescrDirection epd (nolock) on epd.EvnPrescr_id = ep.EvnPrescr_id
				outer apply(
					select top 1
						EUDD.EvnUslugaDispDop_id,
						EUDD.EvnUslugaDispDop_setDate,
						EUDD.EvnUslugaDispDop_setTime,
						EUDD.EvnUslugaDispDop_didDate,
						EUDD.EvnUslugaDispDop_ExamPlace
					from
						v_EvnUslugaDispDop EUDD (nolock)
						left join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
					where EVDD.EvnVizitDispDop_pid = :EvnPLDispDriver_id
					  and EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
					  and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				) EUDDData
				outer apply(
					select top 1 ex.EvnXml_id
					from
						v_EvnXml ex (nolock)
						inner join v_EvnUslugaPar eup (nolock) on ex.Evn_id = eup.EvnUslugaPar_id
					where eup.EvnPrescr_id = ep.EvnPrescr_id
				) EvnXmlData
				left join v_EvnLink el (nolock) on el.Evn_id = EUDDData.EvnUslugaDispDop_id
			where DDIC.EvnPLDisp_id = :EvnPLDispDriver_id
			  and (DDIC.DopDispInfoConsent_IsAgree = 2 OR DDIC.DopDispInfoConsent_IsEarlier = 2)
			  and ST.SurveyType_Code NOT IN (2, 49)
			  and (ISNULL(STL.SurveyTypeLink_IsDel, 1) = 1 or EUDDData.EvnUslugaDispDop_id is not null ) -- только если сохранено согласие
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, ["EvnPLDispDriver_id" => $data["EvnPLDispDriver_id"]]);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Удаление аттрибутов
	 * @param $attr
	 * @param $EvnPLDispDriver_id
	 * @param $pmUser_id
	 * @return string
	 */
	function deleteAttributes($attr, $EvnPLDispDriver_id, $pmUser_id)
	{
		// Сперва получаем список
		switch ($attr) {
			case "EvnVizitDispDop":
				$query = "
					select EVDD.EvnVizitDispDop_id as id
					from
						v_EvnUslugaDispDop EUDD with (nolock)
						inner join v_EvnVizitDispDop EVDD with (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
						inner join v_DopDispInfoConsent DDIC with (nolock) on DDIC.DopDispInfoConsent_id = EVDD.DopDispInfoConsent_id
						inner join v_SurveyTypeLink STL with (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						inner join v_SurveyType ST with (nolock) on ST.SurveyType_id = STL.SurveyType_id
					where EUDD.EvnUslugaDispDop_rid = :EvnPLDispDriver_id
					  and ISNULL(DDIC.DopDispInfoConsent_IsAgree, 1) = 1
					  and ISNULL(DDIC.DopDispInfoConsent_IsEarlier, 1) = 1
					  and ST.SurveyType_Code not in (1, 2, 48)
					  and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				";
				break;
			// Специально для удаления анкетирования
			case "EvnUslugaDispDop":
				$query = "
					select EUDD.EvnUslugaDispDop_id as id
					from
						v_EvnUslugaDispDop EUDD with (nolock)
						inner join v_SurveyTypeLink STL with (nolock) on STL.UslugaComplex_id = EUDD.UslugaComplex_id
						inner join v_SurveyType ST with (nolock) on ST.SurveyType_id = STL.SurveyType_id
						inner join v_DopDispInfoConsent DDIC with (nolock) on DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id
					where EUDD.EvnUslugaDispDop_rid = :EvnPLDispDriver_id
					  and DDIC.EvnPLDisp_id = :EvnPLDispDriver_id
					  and ISNULL(DDIC.DopDispInfoConsent_IsAgree, 1) = 1
					  and ISNULL(DDIC.DopDispInfoConsent_IsEarlier, 1) = 1
					  and ST.SurveyType_Code = 2
					  and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				";
				break;
			case "EvnDiagDopDisp":
				$where = "EvnDiagDopDisp_pid = :EvnPLDispDriver_id";
				$query = "
					select {$attr}_id as id
					from v_{$attr} with (nolock)
					where {$where}
				";
				break;
			case "DopDispInfoConsent":
				$query = "
					select DDIC.DopDispInfoConsent_id as id
					from
						v_DopDispInfoConsent DDIC with (nolock)
						inner join v_SurveyTypeLink STL with (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						inner join v_SurveyType ST with (nolock) on ST.SurveyType_id = STL.SurveyType_id
					where DDIC.EvnPLDisp_id = :EvnPLDispDriver_id
					  and ST.SurveyType_Code NOT IN (1,48)
				";
				break;
			default:
				$where = "EvnPLDisp_id = :EvnPLDispDriver_id";
				$query = "
					select {$attr}_id as id
					from v_{$attr} with (nolock)
					where {$where}
				";
				break;
		}
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, ["EvnPLDispDriver_id" => $EvnPLDispDriver_id]);
		if (!is_object($result)) {
			return "Ошибка при выполнении запроса к базе данных";
		}
		$response = $result->result_array();
		if (is_array($response) && count($response) > 0) {
			foreach ($response as $array) {
				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
					exec p_{$attr}_del
						@{$attr}_id = :id,
						" . (in_array($attr, ["EvnDiagDopDisp", "EvnUslugaDispDop", "EvnVizitDispDop"]) ? "@pmUser_id = :pmUser_id," : "") . "
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->db->query($query, ["id" => $array["id"], "pmUser_id" => $pmUser_id]);
				if (!is_object($result)) {
					return "Ошибка при выполнении запроса к базе данных";
				}
				$res = $result->result("array");
				if (is_array($res) && count($res) > 0 && !empty($res[0]["Error_Msg"])) {
					return $res[0]["Error_Msg"];
				}
			}
		}
		return "";
	}

	/**
	 * Сохранение карты
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
    function saveEvnPLDispDriver($data)
    {
	    if ($data["EvnPLDispDriver_IsFinish"] == 2) {
		    $test = $this->getFirstResultFromQuery("
				select count(*) as [count]
				from
					v_EvnUslugaDispDop EUDD (nolock)
					inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
					inner join v_DopDispInfoConsent DDIC (nolock) on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
					inner join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					inner join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
					inner join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
					inner join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
				where EVDD.EvnVizitDispDop_pid = :EvnPLDispDriver_id
				  and R.Rate_ValuesIs is not null
				  and ST.SurveyType_Code in(155,156,157,158)
			", ["EvnPLDispDriver_id" => $data["EvnPLDispDriver_id"]]);
		    if (empty($test) || $test < 4) {
			    throw new Exception("Случай медицинского освидетельствования водителя не может быть закончен, если не заполнен хотя бы один из результатов осмотров врачей");
		    }
	    }
	    if ($data["EvnPLDispDriver_IsFinish"] == 2 && $data["ResultDispDriver_id"] == 1) {
		    $test = $this->getFirstResultFromQuery("
				select count(*) as [count]
				from
					v_EvnUslugaDispDop EUDD (nolock)
					inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
					inner join v_DopDispInfoConsent DDIC (nolock) on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
					inner join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					inner join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
					inner join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
					inner join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
				where EVDD.EvnVizitDispDop_pid = :EvnPLDispDriver_id
				  and R.Rate_ValuesIs = 1
				  and ST.SurveyType_Code in(155,156,157,158)
			", ["EvnPLDispDriver_id" => $data["EvnPLDispDriver_id"]]);
		    if (empty($test) || $test < 4) {
		    	throw new Exception("Случай медицинского освидетельствования водителя не может быть закончен с результатом «Отсутствие медицинских противопоказаний к управлению ТС», если результат осмотра хотя бы одного врача не заполнен или выявил противопоказания к управлению ТС");
		    }
	    }
	    $proc = (!isset($data["EvnPLDispDriver_id"]))?"p_EvnPLDispDriver_ins":"p_EvnPLDispDriver_upd";
	    $data["EvnPLDispDriver_setDate"] = $data["EvnPLDispDriver_consDate"];
	    $data["MedStaffFact_id"] = !empty($data["session"]["CurARM"]["MedStaffFact_id"]) ? $data["session"]["CurARM"]["MedStaffFact_id"] : null;
	    $query = "
		    declare
		        @EvnPLDispDriver_id bigint,
		        @EvnPLDispDriver_IsSigned bigint,
		        @pmUser_signID bigint,
				@EvnPLDispDriver_signDT datetime,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@curdate datetime;

			set @curdate = dbo.tzGetDate();
			set @EvnPLDispDriver_id = :EvnPLDispDriver_id;
			if @EvnPLDispDriver_id is not null begin
			 	select
			 	    @EvnPLDispDriver_IsSigned = IIF(EvnPLDispDriver_IsSigned = 2, 1, EvnPLDispDriver_IsSigned),
					@pmUser_signID = pmUser_signID,
					@EvnPLDispDriver_signDT = EvnPLDispDriver_signDT
				from v_EvnPLDispDriver (nolock)
				where EvnPLDispDriver_id = @EvnPLDispDriver_id
			end
			exec {$proc}
				@EvnPLDispDriver_id = @EvnPLDispDriver_id output,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnDirection_id = :EvnDirection_id,
				@EvnPLDispDriver_setDT = :EvnPLDispDriver_setDate,
				@EvnPLDispDriver_IsFinish = :EvnPLDispDriver_IsFinish,
				@EvnPLDispDriver_consDT = :EvnPLDispDriver_consDate,
				@DispClass_id = :DispClass_id,
				@EvnPLDispDriver_IndexRep = :EvnPLDispDriver_IndexRep,
				@EvnPLDispDriver_IndexRepInReg = :EvnPLDispDriver_IndexRepInReg,
				@PayType_id = :PayType_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@EvnPLDispDriver_Num = :EvnPLDispDriver_Num,
				@ResultDispDriver_id = :ResultDispDriver_id,
				@EvnPLDispDriver_MedSer = :EvnPLDispDriver_MedSer,
				@EvnPLDispDriver_MedNum = :EvnPLDispDriver_MedNum,
				@EvnPLDispDriver_MedDate = :EvnPLDispDriver_MedDate,
				@AttachType_id = 2,
				@EvnPLDispDriver_IsSigned = @EvnPLDispDriver_IsSigned,
				@pmUser_signID = @pmUser_signID,
				@EvnPLDispDriver_signDT = @EvnPLDispDriver_signDT,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @EvnPLDispDriver_id as EvnPLDispDriver_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
	    /**@var CI_DB_result $result */
	    $result = $this->db->query($query, $data);
	    if (!is_object($result)) {
		    throw new Exception("Ошибка при выполнении запроса к базе данных (сохранение карты)");
	    }
	    $resp = $result->result_array();
	    if (count($resp) && !empty($resp[0]["EvnPLDispDriver_id"])) {
		    $data["EvnPLDispDriver_id"] = $resp[0]["EvnPLDispDriver_id"];
		    $this->saveCB($data);
	    }
	    return $resp;
    }

	/**
	 * Сохранение чекбоксов
	 * @param $data
	 */
	function saveCB($data)
	{
		// --- DriverCategory ---
		$this->saveCBgr($data, "DriverCategory");
		// --- DriverMedicalClose ---
		$this->saveCBgr($data, "DriverMedicalClose");
		// --- DriverMedicalIndication ---
		$this->saveCBgr($data, "DriverMedicalIndication");
	}

	/**
	 * сохранение флагов (из эмк)
	 * @param $data
	 * @return array
	 */
	function saveCBemk($data)
	{
		$data[$data["type"]] = $data["data"];
		$this->saveCBgr($data, $data["type"]);
		return ["success" => true];
	}

	/**
	 * Сохранение чекбоксов
	 * @param $data
	 * @param $type
	 */
	function saveCBgr($data, $type)
	{
		$DriverData = explode(",", $data[$type]);
		$filter = empty($data[$type]) ? "" : "DCL.{$type}_id not in(" . implode(",", $DriverData) . ") and ";
		$query = "
			declare cur1 cursor read_only for
				select {$type}Link_id 
				from {$type}Link DCL with(nolock) 
				where {$filter} DCL.EvnPLDispDriver_id=:EvnPLDispDriver_id
			
			declare @{$type}Link_id bigint
			declare @Error_Code bigint
			declare @Error_Message varchar(4000)
			open cur1
			fetch next from cur1 into @{$type}Link_id
			while @@FETCH_STATUS = 0 begin
				exec p_{$type}Link_del
					@{$type}Link_id = @{$type}Link_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output
				fetch next from cur1 into @{$type}Link_id
			end

			close cur1
			deallocate cur1
		";
		$this->db->query($query, ["EvnPLDispDriver_id" => $data["EvnPLDispDriver_id"]]);
		foreach ($DriverData as $val) {
			$query = "
				declare @ins bigint = (select {$type}Link_id from {$type}Link with(nolock) where {$type}_id = :{$type}_id and EvnPLDispDriver_id = :EvnPLDispDriver_id)
				declare @Error_Code bigint
				declare @Error_Message varchar(4000)
				if(@ins is null) begin
					exec p_{$type}Link_ins
						@{$type}Link_id = null,
						@{$type}_id = :{$type}_id,
						@EvnPLDispDriver_id = :EvnPLDispDriver_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output
				end
			";
			$this->db->query($query, [
				"EvnPLDispDriver_id" => $data["EvnPLDispDriver_id"],
				"{$type}_id" => $val,
				"pmUser_id" => $data["pmUser_id"]
			]);
		}
	}

	/**
	 * Генерация номера
	 * @param $data
	 * @return mixed|bool
	 */
	function getEvnPLDispDriverNumber($data)
	{
		$query = "
			declare @EvnPLDispDriver_Num bigint;
			exec xp_GenpmID
				@ObjectName = 'EvnPLDispDriver',
				@Lpu_id = :Lpu_id,
				@ObjectID = @EvnPLDispDriver_Num output;
			select @EvnPLDispDriver_Num as EvnPLDispDriver_Num, '' as Error_Msg;
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, ["Lpu_id" => $data["Lpu_id"]]);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result_array();
		return (count($result) && $result[0]["EvnPLDispDriver_Num"]) ? $result[0]["EvnPLDispDriver_Num"] : false;
	}

	/**
	 * Получение данных по инфекциям
	 * @param $data
	 * @return array|false
	 */
	function getUslugaResult($data)
	{
		$query = "
			declare
				@EvnPrescr_id bigint,
				@Lpu_id bigint;
			select top 1
				@EvnPrescr_id = EvnPrescr_id,
				@Lpu_id = Lpu_id
			from v_EvnPrescr (nolock)
			where DopDispInfoConsent_id = :DopDispInfoConsent_id;
			select 
				convert(varchar(10), eup.EvnUslugaPar_setDate, 104) as EvnUslugaDispDop_didDate,
				eup.EvnUslugaPar_setTime as EvnUslugaDispDop_didTime,
				convert(varchar(10), eup.EvnUslugaPar_setDate, 104) as EvnUslugaDispDop_disDate,
				eup.EvnUslugaPar_setTime as EvnUslugaDispDop_disTime,
				IIF(eup.Lpu_id = @Lpu_id, 1, 3) as ExaminationPlace_id,
				eup.Lpu_id as Lpu_uid,
				ls.LpuSectionProfile_id as LpuSectionProfile_id,
				msf.MedSpecOms_id,
				ls.LpuSection_id as LpuSection_id,
				eup.MedStaffFact_id as MedStaffFact_id,
				msf.MedPersonal_id,
				null as Diag_id
			from
				v_EvnPrescrDirection epd (nolock)
				inner join v_EvnUslugaPar eup (nolock) on eup.EvnDirection_id = epd.EvnDirection_id
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = eup.MedStaffFact_id
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = msf.LpuSection_id
			where epd.EvnPrescr_id = @EvnPrescr_id
			union all
			select 
				convert(varchar(10), epl.EvnVizitPL_setDate, 104) as EvnUslugaDispDop_didDate,
				epl.EvnVizitPL_setTime as EvnUslugaDispDop_didTime,
				convert(varchar(10), epl.EvnVizitPL_setDate, 104) as EvnUslugaDispDop_disDate,
				epl.EvnVizitPL_setTime as EvnUslugaDispDop_disTime,
				IIF(epl.Lpu_id = isnull(@Lpu_id, ed.Lpu_id), 1, 3) as ExaminationPlace_id,
				epl.Lpu_id as Lpu_uid,
				ls.LpuSectionProfile_id as LpuSectionProfile_id,
				msf.MedSpecOms_id,
				ls.LpuSection_id as LpuSection_id,
				epl.MedStaffFact_id as MedStaffFact_id,
				msf.MedPersonal_id,
				epl.Diag_id
			from
				v_EvnDirection ed (nolock)
				left join v_EvnPrescrDirection epd (nolock) on epd.EvnPrescr_id = @EvnPrescr_id
				inner join v_EvnVizitPL epl (nolock) on epl.EvnDirection_id = isnull(epd.EvnDirection_id, ed.EvnDirection_id)
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = epl.MedStaffFact_id
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = msf.LpuSection_id
			where ed.DopDispInfoConsent_id = :DopDispInfoConsent_id
			  and ed.EvnStatus_id not in (12,13)
		";
		return $this->queryResult($query, ["DopDispInfoConsent_id" => $data["DopDispInfoConsent_id"]]);
	}

	/**
	 * Получение списка категорий ТС
	 * @param $data
	 * @return array|false
	 */
	function getDriverCategory($data)
	{
		$query = "
			SELECT
				DC.DriverCategory_id as value,
				DC.DriverCategory_Name as boxLabel,
				IIF((ISNULL(DriverCategoryLink_id,0)=0), 'false', 'true') as checked
			FROM
				v_DriverCategory DC with(nolock)
				left join v_DriverCategoryLink DCL with (nolock) on	DCL.EvnPLDispDriver_id = :EvnPLDispDriver_id and DCL.DriverCategory_id = DC.DriverCategory_id
			ORDER BY
				value
		";
		return $this->queryResult($query, ["EvnPLDispDriver_id" => $data["EvnPLDispDriver_id"]]);
	}

	/**
	 * Медицинские ограничения к управлению ТС
	 * @param $data
	 * @return array|false
	 */
	function getDriverMedicalClose($data)
	{
		$query = "
			SELECT
				DC.DriverMedicalClose_id as value,
				DC.DriverMedicalClose_Name as boxLabel,
				IIF((ISNULL(DriverMedicalCloseLink_id,0)=0), 'false', 'true') as checked
			FROM
				DriverMedicalClose DC with(nolock)
				left join v_DriverMedicalCloseLink DCL with (nolock) on	DCL.EvnPLDispDriver_id = :EvnPLDispDriver_id and DCL.DriverMedicalClose_id = DC.DriverMedicalClose_id
		";
		return $this->queryResult($query, ["EvnPLDispDriver_id" => $data["EvnPLDispDriver_id"]]);
	}

	/**
	 * Медицинские показания к управлению ТС
	 * @param $data
	 * @return array|false
	 */
	function getDriverMedicalIndication($data)
	{
		$query = "
			SELECT
				DC.DriverMedicalIndication_id as value,
				DC.DriverMedicalIndication_Name as boxLabel,
				IIF((ISNULL(DriverMedicalIndicationLink_id,0)=0), 'false', 'true') as checked
			FROM
				DriverMedicalIndication DC with(nolock)
				left join v_DriverMedicalIndicationLink DCL with (nolock) on DCL.EvnPLDispDriver_id = :EvnPLDispDriver_id and DCL.DriverMedicalIndication_id = DC.DriverMedicalIndication_id
		";
		return $this->queryResult($query, ["EvnPLDispDriver_id" => $data["EvnPLDispDriver_id"]]);
	}

	/**
	 * данные из регистров
	 * @param $data
	 * @return array
	 */
	function getRegistryInfo($data)
	{
		$query = "
			select top 1
				MT.MorbusType_SysNick,
				isnull(Diag.Diag_FullName, PRDiag.Diag_FullName) as Diag_Name
			from
				v_PersonRegister PR with (nolock)
				inner join v_MorbusType MT with (nolock) on PR.MorbusType_id = MT.MorbusType_id
				left join v_EvnNotifyCrazy EN with (nolock) on EN.EvnNotifyCrazy_id = PR.EvnNotifyBase_id
				left join v_MorbusCrazy MO with (nolock) on MO.Morbus_id = isnull(EN.Morbus_id,PR.Morbus_id)
				outer apply (
					select top 1 CD.Diag_id 
					from
						v_MorbusCrazyDiag MCD with(nolock)
						left join v_CrazyDiag CD with(nolock) on CD.CrazyDiag_id = MCD.CrazyDiag_id 
					where MCD.MorbusCrazy_id = MO.MorbusCrazy_id
					order by MCD.MorbusCrazyDiag_setDT desc
				) CDiag
				left join v_Diag Diag with (nolock) on Diag.Diag_id = CDiag.Diag_id
				left join v_Diag PRDiag with (nolock) on PRDiag.Diag_id = PR.Diag_id
			where PR.Person_id = :Person_id
			  and PR.PersonRegister_disDate is null
			  and MT.MorbusType_SysNick in('crazy', 'narc')
		";
		$result = $this->queryResult($query, ["Person_id" => $data["Person_id"]]);
		return ["success" => true, "message" => (count($result)) ? "Пациент состоит в Регистре по " . ($result[0]["Diag_Name"] == "crazy" ? "психиатрии" : "наркологии") . " с диагнозом {$result[0]["Diag_Name"]}" : ""];
	}

	/**
	 * Печать направления на МСЭ в формате HL7
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function printEvnPLDispDriverHL7($data)
	{
		$resp = $this->queryResult("
			select top 1 
				Evn.EvnPLDispDriver_id, /*идентификатор bigint*/
				convert(varchar(10), Evn.EvnPLDispDriver_setDT, 120) as EvnPLDispDriver_setDT, /*идентификатор bigint*/
				LpuOID.PassportToken_tid,
				Evn.Person_id,
				PS.Person_Snils,
				PS.Person_SurName,
				PS.Person_FirName,
				PS.Person_SecName,
				s.Sex_Code,
				s.Sex_Name,
				ua.Address_Address as UAddress_Address,
				ua.KLRgn_id as UKLRgn_id,
				pa.Address_Address as PAddress_Address,
				pa.KLRgn_id as PKLRgn_id,
				ps.Person_Phone,
				VPI.PersonInfo_Email,
				L.Lpu_Name,
				L.Lpu_Phone,
				L.UAddress_id as LUAddress_id,
				lua.Address_Address as LUAddress_Address,
				lua.Address_Zip as LUAddress_Zip,
				luasr.KLSubRgn_Name as LUKLSubRgn_Name,
				luat.KLTown_Name as LUKLTown_Name,
				luac.KLCity_Name as LUKLCity_Name,
				luas.KLStreet_Name as LUKLStreet_Name,
				lua.Address_Corpus as LUAddress_Corpus,
				lua.Address_House as LUAddress_House,
				lua.KLRgn_id as LUKLRgn_id,
				L.PAddress_id as LPAddress_id,
				lpa.Address_Address as LPAddress_Address,
				lpa.Address_Zip as LPAddress_Zip,
				lpasr.KLSubRgn_Name as LPKLSubRgn_Name,
				lpat.KLTown_Name as LPKLTown_Name,
				lpac.KLCity_Name as LPKLCity_Name,
				lpas.KLStreet_Name as LPKLStreet_Name,
				lpa.Address_Corpus as LPAddress_Corpus,
				lpa.Address_House as LPAddress_House,
				lpa.KLRgn_id as LPKLRgn_id,
				convert(varchar(10), ps.Person_BirthDay, 120) as Person_BirthDay,
				msf.MedPersonal_id,
				msf.Person_SurName as MedPersonal_SurName,
				msf.Person_FirName as MedPersonal_FirName,
				msf.Person_SecName as MedPersonal_SecName,
				Evn.EvnPLDispDriver_MedSer,
				Evn.EvnPLDispDriver_MedNum,
				d.Document_Num,
				d.Document_Ser,
				convert(varchar(10), d.Document_begDate, 104) as Document_begDate,
				o.Org_Name as DocOrg_Name,
				Evn.ResultDispDriver_id
			from
				dbo.v_EvnPLDispDriver Evn with (nolock)
				outer apply (
					select top 1 evdd.MedStaffFact_id
					from
						v_EvnVizitDispDop evdd (nolock)
						inner join v_DopDispInfoConsent ddic (nolock) on ddic.DopDispInfoConsent_id = evdd.DopDispInfoConsent_id
						inner join v_SurveyTypeLink stl (nolock) on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
					where EvnVizitDispDop_pid = Evn.EvnPLDispDriver_id				
					  and stl.SurveyType_id = 158 -- прием (осмотр) терапевтом		
				) evdd
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = evdd.MedStaffFact_id
				left join fed.v_PassportToken LpuOID with(nolock) on LpuOID.Lpu_id = Evn.Lpu_id
				left join v_PersonState ps with (nolock) on ps.Person_id = Evn.Person_id
				left join v_Document d (nolock) on d.Document_id = ps.Document_id
				left join v_Org o (nolock) on o.Org_id = d.OrgDep_id
				left join v_Sex s with (nolock) on s.Sex_id = ps.Sex_id
				left join v_Address_all ua with (nolock) on ua.Address_id = ps.UAddress_id
				left join v_Address_all pa with (nolock) on pa.Address_id = ps.PAddress_id
				left join v_PersonInfo VPI with (nolock) on VPI.Person_id = PS.Person_id
				left join v_Lpu l with (nolock) on l.Lpu_id = Evn.Lpu_id
				left join v_Address_all lua with (nolock) on lua.Address_id = l.UAddress_id
				left join v_KLSubRgn luasr with (nolock) on luasr.KLSubRgn_id = lua.KLSubRgn_id
				left join v_KLTown luat with (nolock) on luat.KLTown_id = lua.KLTown_id
				left join v_KLCity luac with (nolock) on luac.KLCity_id = lua.KLCity_id
				left join v_KLStreet luas with (nolock) on luas.KLStreet_id = lua.KLStreet_id
				left join v_Address_all lpa with (nolock) on lpa.Address_id = l.PAddress_id
				left join v_KLSubRgn lpasr with (nolock) on lpasr.KLSubRgn_id = lpa.KLSubRgn_id
				left join v_KLTown lpat with (nolock) on lpat.KLTown_id = lpa.KLTown_id
				left join v_KLCity lpac with (nolock) on lpac.KLCity_id = lpa.KLCity_id
				left join v_KLStreet lpas with (nolock) on lpas.KLStreet_id = lpa.KLStreet_id
			where Evn.EvnPLDispDriver_id = :EvnPLDispDriver_id
		", ["EvnPLDispDriver_id" => $data["EvnPLDispDriver_id"]]);
		if (empty($resp[0]["EvnPLDispDriver_id"])) {
			throw new Exception("Ошибка получения данных по направлению на МСЭ", 500);
		}
		// достаем категории
		$resp[0]["DriverCategorys"] = [];
		$resp_dcl = $this->queryResult("
			select
				dc.DriverCategory_Code,
				dc.DriverCategory_Name
			from
				v_DriverCategoryLink dcl (nolock)
				inner join v_DriverCategory dc (nolock) on dc.DriverCategory_id = dcl.DriverCategory_id
			where dcl.EvnPLDispDriver_id = :EvnPLDispDriver_id
		", ["EvnPLDispDriver_id" => $data["EvnPLDispDriver_id"]]);
		foreach($resp_dcl as $one_dcl) {
			$resp[0]["DriverCategorys"][] = $one_dcl["DriverCategory_Name"];
		}
		// достаем ограничения
		$resp[0]["DriverMedicalCloses"] = [];
		$resp_dcl = $this->queryResult("
			select dmcl.DriverMedicalClose_id
			from v_DriverMedicalCloseLink dmcl (nolock)
			where dmcl.EvnPLDispDriver_id = :EvnPLDispDriver_id
		", ["EvnPLDispDriver_id" => $data["EvnPLDispDriver_id"]]);
		foreach($resp_dcl as $one_dcl) {
			$resp[0]["DriverMedicalCloses"][] = $one_dcl["DriverMedicalClose_id"];
		}
		// достаем показания
		$resp[0]["DriverMedicalIndications"] = [];
		$resp_dcl = $this->queryResult("
			select dmil.DriverMedicalIndication_id
			from v_DriverMedicalIndicationLink dmil (nolock)
			where dmil.EvnPLDispDriver_id = :EvnPLDispDriver_id
		", ["EvnPLDispDriver_id" => $data["EvnPLDispDriver_id"]]);
		foreach($resp_dcl as $one_dcl) {
			$resp[0]["DriverMedicalIndications"][] = $one_dcl["DriverMedicalIndication_id"];
		}
		if (!empty($resp[0]["KLCountry_id"]) && $resp[0]["KLCountry_id"] == 643 && $resp[0]["NationalityStatus_IsTwoNation"] == 1) {
			$resp[0]["personNationCode"] = "1";
			$resp[0]["personNationName"] = "Гражданин Российской Федерации";
		} else if (!empty($resp[0]["KLCountry_id"]) && $resp[0]["KLCountry_id"] == 643 && $resp[0]["NationalityStatus_IsTwoNation"] == 2) {
			$resp[0]["personNationCode"] = "2";
			$resp[0]["personNationName"] = "Гражданин Российской Федерации и иностранного государства (двойное гражданство)";
		} else if (!empty($resp[0]["KLCountry_id"]) && $resp[0]["KLCountry_id"] != 643) {
			$resp[0]["personNationCode"] = "3";
			$resp[0]["personNationName"] = "Иностранный гражданин";
		} else {
			$resp[0]["personNationCode"] = "4";
			$resp[0]["personNationName"] = "Лицо без гражданства";
		}
		$resp[0]["assignedTime"] = date("Y-m-d");
		$resp[0]["isAssigned"] = "S";

		$this->load->library("parser");
		$xml = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= '<?xml-stylesheet type="text/xsl" href="/documents/xsl/MBC.xsl"?>';
		$xml .= $this->parser->parse("print_evnpldispdriver_hl7", $resp[0], true);
		return ["xml" => $xml];
	}

	/**
	 * Проверка прав на подписание карты мед освидетельствования водителя
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	function checkSignAccess($data) {
		// Права на подписание карты имеет врач Проводивший прием (осмотр) врачом-терапевтом.
		// В поле «Медицинское освидетельствование закончено» установлено значение «Да»
		$resp_epldd = $this->queryResult("
			select
				epldd.EvnPLDispDriver_id,
				msf.MedPersonal_id,
				epldd.EvnPLDispDriver_IsFinish
			from
				v_EvnPLDispDriver EPLDD (nolock)
				outer apply (
					select top 1 evdd.MedStaffFact_id
					from
						v_EvnVizitDispDop evdd (nolock)
						inner join v_DopDispInfoConsent ddic (nolock) on ddic.DopDispInfoConsent_id = evdd.DopDispInfoConsent_id
						inner join v_SurveyTypeLink stl (nolock) on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
					where EvnVizitDispDop_pid = EPLDD.EvnPLDispDriver_id				
					  and stl.SurveyType_id = 158 -- прием (осмотр) терапевтом		
				) evdd
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = evdd.MedStaffFact_id 
			where EPLDD.EvnPLDispDriver_id = :EvnPLDispDriver_id
		", ["EvnPLDispDriver_id" => $data["EvnPLDispDriver_id"]]);
		if (empty($resp_epldd[0]["EvnPLDispDriver_id"])) {
			throw new Exception("Подписание невозможно, т.к. не найдено подписываемое медицинское освидетельствование водителя");
		}
		if (empty($resp_epldd[0]["MedPersonal_id"])) {
			throw new Exception("Подписание невозможно, т.к. не указан врач в приеме (осмотре) врачом-терапевтом");
		}
		if (empty($data["session"]["medpersonal_id"]) || $resp_epldd[0]["MedPersonal_id"] != $data["session"]["medpersonal_id"]) {
			throw new Exception("Подписание невозможно, права на подписание карты имеет врач проводивший прием (осмотр) врачом-терапевтом.");
		}
		if ($resp_epldd[0]["EvnPLDispDriver_IsFinish"] != 2) {
			throw new Exception("Подписание невозможно, т.к. медицинское освидетельствование не закончено");
		}
		return true;
	}
}