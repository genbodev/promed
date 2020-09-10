<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* AssessmentHealth_model - модель для работы с картой здоровья в талонах ДД
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Власенко Дмитрий
* @version      01.08.2013
*/

class AssessmentHealth_model extends SwPgModel
{
	function __construct()
	{
		parent::__construct();
		$this->_setScenarioList([
			self::SCENARIO_DO_SAVE
		]);
	}

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = [];
		$arr[self::ID_KEY] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
			],
			"alias" => "AssessmentHealth_id",
			"label" => "Идентификатор события",
			"save" => "",
			"type" => "id"
		];
		$arr["evnpldisp_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "EvnPLDisp_id",
			"label" => "Идентификатор карты диспансеризации",
			"save" => "required",
			"type" => "id"
		];
		$arr["weight"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_Weight",
			"label" => "Масса (кг)",
			"save" => "",
			"type" => "float"
		];
		$arr["height"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_Height",
			"label" => "Рост (см)",
			"save" => "",
			"type" => "int"
		];
		$arr["head"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_Head",
			"label" => "Окружность головы (см)",
			"save" => "",
			"type" => "int"
		];
		$arr["weightabnormtype_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "WeightAbnormType_id",
			"label" => "Тип отклонения (масса)",
			"save" => "",
			"type" => "id"
		];
		$arr["heightabnormtype_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "HeightAbnormType_id",
			"label" => "Тип отклонения (рост)",
			"save" => "",
			"type" => "id"
		];
		$arr["gnostic"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_Gnostic",
			"label" => "Познавательная функция (возраст развития)",
			"save" => "",
			"type" => "int"
		];
		$arr["motion"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_Motion",
			"label" => "Моторная функция (возраст развития)",
			"save" => "",
			"type" => "int"
		];
		$arr["social"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_Social",
			"label" => "Эмоциональная и социальная (контакт с окружающим миром) функции (возраст развития)",
			"save" => "",
			"type" => "int"
		];
		$arr["speech"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_Speech",
			"label" => "Предречевое и речевое развитие (возраст развития)",
			"save" => "",
			"type" => "int"
		];
		$arr["p"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_P",
			"label" => "P",
			"save" => "",
			"type" => "int"
		];
		$arr["ax"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_Ax",
			"label" => "Ax",
			"save" => "",
			"type" => "int"
		];
		$arr["fa"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_Fa",
			"label" => "Fa",
			"save" => "",
			"type" => "int"
		];
		$arr["ma"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_Ma",
			"label" => "Ma",
			"save" => "",
			"type" => "int"
		];
		$arr["me"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_Me",
			"label" => "Me",
			"save" => "",
			"type" => "int"
		];
		$arr["years"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_Years",
			"label" => "Характеристика менструальной функции: лет",
			"save" => "",
			"type" => "int"
		];
		$arr["month"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_Month",
			"label" => "Характеристика менструальной функции: месяцев",
			"save" => "",
			"type" => "int"
		];
		$arr["isregular"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_IsRegular",
			"label" => "Регулярные",
			"save" => "",
			"type" => "checkbox"
		];
		$arr["isirregular"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_IsIrregular",
			"label" => "Нерегулярные",
			"save" => "",
			"type" => "checkbox"
		];
		$arr["isabundant"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_IsAbundant",
			"label" => "Обильные",
			"save" => "",
			"type" => "checkbox"
		];
		$arr["ismoderate"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_IsModerate",
			"label" => "Умеренные",
			"save" => "",
			"type" => "checkbox"
		];
		$arr["isscanty"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_IsScanty",
			"label" => "Скудные",
			"save" => "",
			"type" => "checkbox"
		];
		$arr["ispainful"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_IsPainful",
			"label" => "Болезненные",
			"save" => "",
			"type" => "checkbox"
		];
		$arr["ispainless"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_IsPainless",
			"label" => "Безболезненные",
			"save" => "",
			"type" => "checkbox"
		];
		$arr["invalidtype_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "InvalidType_id",
			"label" => "Инвалидность",
			"save" => "",
			"type" => "id"
		];
		$arr["setdt"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_DATE_TIME,
			],
			"alias" => "AssessmentHealth_setDT",
			"label" => "Дата установления",
			"save" => "",
			"type" => "date"
		];
		$arr["reexamdt"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_DATE_TIME,
			],
			"alias" => "AssessmentHealth_reExamDT",
			"label" => "Дата последнего освидетельствования",
			"save" => "",
			"type" => "date"
		];
		$arr["invaliddiagtype_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "InvalidDiagType_id",
			"label" => "Заболевания, обусловившие возникновение инвалидности",
			"save" => "",
			"type" => "id"
		];
		$arr["ismental"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_IsMental",
			"label" => "Умственные",
			"save" => "",
			"type" => "checkbox"
		];
		$arr["isotherpsych"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_IsOtherPsych",
			"label" => "Другие психологические",
			"save" => "",
			"type" => "checkbox"
		];
		$arr["islanguage"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_IsLanguage",
			"label" => "Языковые и речевые",
			"save" => "",
			"type" => "checkbox"
		];
		$arr["isvestibular"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_IsVestibular",
			"label" => "Слуховые и вестибулярные",
			"save" => "",
			"type" => "checkbox"
		];
		$arr["isvisual"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_IsVisual",
			"label" => "Зрительные",
			"save" => "",
			"type" => "checkbox"
		];
		$arr["ismeals"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_IsMeals",
			"label" => "Висцеральные и метаболические расстройства питания",
			"save" => "",
			"type" => "checkbox"
		];
		$arr["ismotor"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_IsMotor",
			"label" => "Двигательные",
			"save" => "",
			"type" => "checkbox"
		];
		$arr["isdeform"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_IsDeform",
			"label" => "Уродующие",
			"save" => "",
			"type" => "checkbox"
		];
		$arr["isgeneral"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_IsGeneral",
			"label" => "Общие и генерализованные",
			"save" => "",
			"type" => "checkbox"
		];
		$arr["reabdt"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_DATE_TIME,
			],
			"alias" => "AssessmentHealth_ReabDT",
			"label" => "Дата назначения",
			"save" => "",
			"type" => "date"
		];
		$arr["rehabilitendtype_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "RehabilitEndType_id",
			"label" => "Тип выполнения реабилитации",
			"save" => "",
			"type" => "id"
		];
		$arr["profvaccintype_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "ProfVaccinType_id",
			"label" => "Проведение профилактических прививок",
			"save" => "",
			"type" => "id"
		];
		$arr["healthkind_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "HealthKind_id",
			"label" => "Группа здоровья",
			"save" => "",
			"type" => "id"
		];
		$arr["normadisturbancetype_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "NormaDisturbanceType_id",
			"label" => "Психомоторная сфера",
			"save" => "",
			"type" => "id"
		];
		$arr["normadisturbancetype_uid"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "NormaDisturbanceType_uid",
			"label" => "Интеллект",
			"save" => "",
			"type" => "id"
		];
		$arr["normadisturbancetype_eid"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "NormaDisturbanceType_eid",
			"label" => "Эмоционально-вегетативная сфера",
			"save" => "",
			"type" => "id"
		];
		$arr["healthgrouptype_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "HealthGroupType_id",
			"label" => "Группа здоровья для занятий физкультурой",
			"save" => "",
			"type" => "id"
		];
		$arr["healthgrouptype_oid"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "HealthGroupType_oid",
			"label" => "Группа здоровья для занятий физкультурой до проведения обследования",
			"save" => "",
			"type" => "id"
		];
		$arr["vaccinename"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_VaccineName",
			"label" => "Наименование прививки",
			"save" => "",
			"type" => "string"
		];
		$arr["healthrecom"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_HealthRecom",
			"label" => "Рекомендации по формированию здорового образа жизни",
			"save" => "",
			"type" => "string"
		];
		$arr["disprecom"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealth_DispRecom",
			"label" => "Рекомендации о необходимости установления или продолжения диспансерного наблюдения",
			"save" => "",
			"type" => "string"
		];
		$arr["assessmenthealthvaccin_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "AssessmentHealthVaccin_id",
			"label" => "Идентификатор стыковочной табилцы прививок",
			"save" => "",
			"type" => "id"
		];
		return $arr;
	}

	/**
	 * Извлечение значений параметров модели из входящих параметров, переданных из контроллера
	 * @param array $data
	 * @throws Exception
	 */
	function setParams($data)
	{
		parent::setParams($data);
		$this->_params["AssessmentHealthVaccinData"] = (!empty($data["AssessmentHealthVaccinData"]) ? $data["AssessmentHealthVaccinData"] : null);
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return "AssessmentHealth";
	}

	/**
	 * После сохранения
	 *
	 * @param array $result
	 * @return array|false|void
	 * 
	 * @throws Exception
	 */
	protected function _afterSave($result)
	{
		if (!empty($result[0]["AssessmentHealth_id"])) {
			// сохраняем прививки
			if (isset($this->_params["AssessmentHealthVaccinData"]) && is_array($this->_params["AssessmentHealthVaccinData"])) {
				// получаем те, что есть
				$query = "
					select
						AssessmentHealthVaccin_id as \"AssessmentHealthVaccin_id\",
						VaccinType_id as \"VaccinType_id\"
					from v_AssessmentHealthVaccin
					where AssessmentHealth_id = :AssessmentHealth_id
				";
				$queryParams = ["AssessmentHealth_id" => $result[0]["AssessmentHealth_id"]];
				$resp_vac = $this->queryResult($query, $queryParams);
				// удаляем тех, что не стало
				$VacExist = [];
				foreach ($resp_vac as $resp_vacone) {
					if (!in_array($resp_vacone["VaccinType_id"], $this->_params["AssessmentHealthVaccinData"])) {
						$query = "
							select
								error_code as \"Error_Code\",
								error_message as \"Error_Message\"
							from p_assessmenthealthvaccin_del(assessmenthealthvaccin_id := :AssessmentHealthVaccin_id);
						";
						$queryParams = [
							"AssessmentHealthVaccin_id" => $resp_vacone["AssessmentHealthVaccin_id"],
							"pmUser_id" => $this->promedUserId
						];
						$resp_vacdel = $this->queryResult($query, $queryParams);
						if (!is_array($resp_vacdel) || count($resp_vacdel) == 0) {
							throw new Exception("Ошибка при удалении прививки");
						} else if (!empty($resp_vacdel[0]["Error_Msg"])) {
							return $resp_vacdel;
						}
					} else {
						$VacExist[] = $resp_vacone["VaccinType_id"];
					}
				}
				// сохраняем новые
				foreach ($this->_params['AssessmentHealthVaccinData'] as $VaccinType_id) {
					if (!in_array($VaccinType_id, $VacExist)) {
						$query = "
							select
								assessmenthealthvaccin_id as \"AssessmentHealthVaccin_id\",
								error_code as \"Error_Code\",
								error_message as \"Error_Message\"
							from p_assessmenthealthvaccin_ins(
							    assessmenthealthvaccin_id := 0,
							    assessmenthealth_id := :AssessmentHealth_id,
							    vaccintype_id := :VaccinType_id,
								pmuser_id := :pmUser_id
							);
						";
						$queryParams = [
							"AssessmentHealth_id" => $result[0]["AssessmentHealth_id"],
							"VaccinType_id" => $VaccinType_id,
							"pmUser_id" => $this->promedUserId
						];
						$resp_vacdel = $this->queryResult($query, $queryParams);
						if (!is_array($resp_vacdel) || count($resp_vacdel) == 0) {
							throw new Exception("Ошибка при сохранении прививки");
						} else if (!empty($resp_vacdel[0]["Error_Msg"])) {
							return $resp_vacdel;
						}
					}
				}
			}
		}
		// @task https://redmine.swan.perm.ru//issues/117123
		// Удаление дублей
		if (!empty($this->EvnPLDisp_id)) {
			$query = "
				select AssessmentHealth_id as \"AssessmentHealth_id\"
				from v_AssessmentHealth
				where EvnPLDisp_id = :EvnPLDisp_id
				order by AssessmentHealth_updDT desc
			";
			$queryParams = ["EvnPLDisp_id" => $this->EvnPLDisp_id];
			$dataList = $this->queryResult($query, $queryParams);
			if (is_array($dataList) && count($dataList) > 1) {
				unset($dataList[0]);
				foreach ($dataList as $row) {
					$query = "
						select
							error_code as \"Error_Code\",
							error_message as \"Error_Message\"
						from p_assessmenthealth_del(assessmenthealth_id := :AssessmentHealth_id);
					";
					$queryParams = ["AssessmentHealth_id" => $row["AssessmentHealth_id"],];
					$rsp = $this->getFirstRowFromQuery($query, $queryParams);
					if (!is_array($rsp) || count($rsp) == 0) {
						throw new Exception("Ошибка при удалении дублей");
					} else if (!empty($rsp["Error_Msg"])) {
						return [$rsp];
					}
				}
			}
		}
	}
}