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

class AssessmentHealth_model extends swModel
{
	/**
	 *	Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_DO_SAVE
		));
	}

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = array();

		$arr[self::ID_KEY] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
			),
			'alias' => 'AssessmentHealth_id',
			'label' => 'Идентификатор события',
			'save' => '',
			'type' => 'id'
		);
		$arr['evnpldisp_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDisp_id',
			'label' => 'Идентификатор карты диспансеризации',
			'save' => 'required',
			'type' => 'id'
		);
		$arr['weight'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_Weight',
			'label' => 'Масса (кг)',
			'save' => '',
			'type' => 'float'
		);
		$arr['height'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_Height',
			'label' => 'Рост (см)',
			'save' => '',
			'type' => 'int'
		);
		$arr['head'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_Head',
			'label' => 'Окружность головы (см)',
			'save' => '',
			'type' => 'int'
		);
		$arr['weightabnormtype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'WeightAbnormType_id',
			'label' => 'Тип отклонения (масса)',
			'save' => '',
			'type' => 'id'
		);
		$arr['heightabnormtype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'HeightAbnormType_id',
			'label' => 'Тип отклонения (рост)',
			'save' => '',
			'type' => 'id'
		);
		$arr['gnostic'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_Gnostic',
			'label' => 'Познавательная функция (возраст развития)',
			'save' => '',
			'type' => 'int'
		);
		$arr['motion'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_Motion',
			'label' => 'Моторная функция (возраст развития)',
			'save' => '',
			'type' => 'int'
		);
		$arr['social'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_Social',
			'label' => 'Эмоциональная и социальная (контакт с окружающим миром) функции (возраст развития)',
			'save' => '',
			'type' => 'int'
		);
		$arr['speech'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_Speech',
			'label' => 'Предречевое и речевое развитие (возраст развития)',
			'save' => '',
			'type' => 'int'
		);
		$arr['p'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_P',
			'label' => 'P',
			'save' => '',
			'type' => 'int'
		);
		$arr['ax'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_Ax',
			'label' => 'Ax',
			'save' => '',
			'type' => 'int'
		);
		$arr['fa'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_Fa',
			'label' => 'Fa',
			'save' => '',
			'type' => 'int'
		);
		$arr['ma'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_Ma',
			'label' => 'Ma',
			'save' => '',
			'type' => 'int'
		);
		$arr['me'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_Me',
			'label' => 'Me',
			'save' => '',
			'type' => 'int'
		);
		$arr['years'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_Years',
			'label' => 'Характеристика менструальной функции: лет',
			'save' => '',
			'type' => 'int'
		);
		$arr['month'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_Month',
			'label' => 'Характеристика менструальной функции: месяцев',
			'save' => '',
			'type' => 'int'
		);
		$arr['isregular'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_IsRegular',
			'label' => 'Регулярные',
			'save' => '',
			'type' => 'checkbox'
		);
		$arr['isirregular'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_IsIrregular',
			'label' => 'Нерегулярные',
			'save' => '',
			'type' => 'checkbox'
		);
		$arr['isabundant'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_IsAbundant',
			'label' => 'Обильные',
			'save' => '',
			'type' => 'checkbox'
		);
		$arr['ismoderate'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_IsModerate',
			'label' => 'Умеренные',
			'save' => '',
			'type' => 'checkbox'
		);
		$arr['isscanty'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_IsScanty',
			'label' => 'Скудные',
			'save' => '',
			'type' => 'checkbox'
		);
		$arr['ispainful'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_IsPainful',
			'label' => 'Болезненные',
			'save' => '',
			'type' => 'checkbox'
		);
		$arr['ispainless'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_IsPainless',
			'label' => 'Безболезненные',
			'save' => '',
			'type' => 'checkbox'
		);
		$arr['invalidtype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'InvalidType_id',
			'label' => 'Инвалидность',
			'save' => '',
			'type' => 'id'
		);
		$arr['setdt'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_DATE_TIME,
			),
			'alias' => 'AssessmentHealth_setDT',
			'label' => 'Дата установления',
			'save' => '',
			'type' => 'date'
		);
		$arr['reexamdt'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_DATE_TIME,
			),
			'alias' => 'AssessmentHealth_reExamDT',
			'label' => 'Дата последнего освидетельствования',
			'save' => '',
			'type' => 'date'
		);
		$arr['invaliddiagtype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'InvalidDiagType_id',
			'label' => 'Заболевания, обусловившие возникновение инвалидности',
			'save' => '',
			'type' => 'id'
		);
		$arr['ismental'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_IsMental',
			'label' => 'Умственные',
			'save' => '',
			'type' => 'checkbox'
		);
		$arr['isotherpsych'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_IsOtherPsych',
			'label' => 'Другие психологические',
			'save' => '',
			'type' => 'checkbox'
		);
		$arr['islanguage'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_IsLanguage',
			'label' => 'Языковые и речевые',
			'save' => '',
			'type' => 'checkbox'
		);
		$arr['isvestibular'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_IsVestibular',
			'label' => 'Слуховые и вестибулярные',
			'save' => '',
			'type' => 'checkbox'
		);
		$arr['isvisual'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_IsVisual',
			'label' => 'Зрительные',
			'save' => '',
			'type' => 'checkbox'
		);
		$arr['ismeals'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_IsMeals',
			'label' => 'Висцеральные и метаболические расстройства питания',
			'save' => '',
			'type' => 'checkbox'
		);
		$arr['ismotor'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_IsMotor',
			'label' => 'Двигательные',
			'save' => '',
			'type' => 'checkbox'
		);
		$arr['isdeform'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_IsDeform',
			'label' => 'Уродующие',
			'save' => '',
			'type' => 'checkbox'
		);
		$arr['isgeneral'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_IsGeneral',
			'label' => 'Общие и генерализованные',
			'save' => '',
			'type' => 'checkbox'
		);
		$arr['reabdt'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_DATE_TIME,
			),
			'alias' => 'AssessmentHealth_ReabDT',
			'label' => 'Дата назначения',
			'save' => '',
			'type' => 'date'
		);
		$arr['rehabilitendtype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'RehabilitEndType_id',
			'label' => 'Тип выполнения реабилитации',
			'save' => '',
			'type' => 'id'
		);
		$arr['profvaccintype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'ProfVaccinType_id',
			'label' => 'Проведение профилактических прививок',
			'save' => '',
			'type' => 'id'
		);
		$arr['healthkind_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'HealthKind_id',
			'label' => 'Группа здоровья',
			'save' => '',
			'type' => 'id'
		);
		$arr['normadisturbancetype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'NormaDisturbanceType_id',
			'label' => 'Психомоторная сфера',
			'save' => '',
			'type' => 'id'
		);
		$arr['normadisturbancetype_uid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'NormaDisturbanceType_uid',
			'label' => 'Интеллект',
			'save' => '',
			'type' => 'id'
		);
		$arr['normadisturbancetype_eid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'NormaDisturbanceType_eid',
			'label' => 'Эмоционально-вегетативная сфера',
			'save' => '',
			'type' => 'id'
		);
		$arr['healthgrouptype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'HealthGroupType_id',
			'label' => 'Группа здоровья для занятий физкультурой',
			'save' => '',
			'type' => 'id'
		);
		$arr['healthgrouptype_oid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'HealthGroupType_oid',
			'label' => 'Группа здоровья для занятий физкультурой до проведения обследования',
			'save' => '',
			'type' => 'id'
		);
		$arr['vaccinename'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_VaccineName',
			'label' => 'Наименование прививки',
			'save' => '',
			'type' => 'string'
		);
		$arr['healthrecom'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_HealthRecom',
			'label' => 'Рекомендации по формированию здорового образа жизни',
			'save' => '',
			'type' => 'string'
		);
		$arr['disprecom'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealth_DispRecom',
			'label' => 'Рекомендации о необходимости установления или продолжения диспансерного наблюдения',
			'save' => '',
			'type' => 'string'
		);
		$arr['assessmenthealthvaccin_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AssessmentHealthVaccin_id',
			'label' => 'Идентификатор стыковочной табилцы прививок',
			'save' => '',
			'type' => 'id'
		);
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
		$this->_params['AssessmentHealthVaccinData'] = (!empty($data['AssessmentHealthVaccinData']) ? $data['AssessmentHealthVaccinData'] : null);
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'AssessmentHealth';
	}

	/**
	 * После сохранения
	 */
	protected function _afterSave($result) {
		if (!empty($result[0]['AssessmentHealth_id'])) {
			// сохраняем прививки
			if(isset($this->_params['AssessmentHealthVaccinData']) && is_array($this->_params['AssessmentHealthVaccinData'])) {
				// получаем те, что есть
				$query = "
					select
						AssessmentHealthVaccin_id,
						VaccinType_id
					from
						v_AssessmentHealthVaccin (nolock)
					where
						AssessmentHealth_id = :AssessmentHealth_id
				";
				$resp_vac = $this->queryResult($query, array(
					'AssessmentHealth_id' => $result[0]['AssessmentHealth_id']
				));

				// удаляем тех, что не стало
				$VacExist = array();
				foreach($resp_vac as $resp_vacone) {
					if (!in_array($resp_vacone['VaccinType_id'], $this->_params['AssessmentHealthVaccinData'])) {
						$query = "
							declare
								@ErrCode int,
								@ErrMessage varchar(4000);
							exec p_AssessmentHealthVaccin_del
								@AssessmentHealthVaccin_id = :AssessmentHealthVaccin_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMessage output;
							select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
						";
						$resp_vacdel = $this->queryResult($query, array(
							'AssessmentHealthVaccin_id' => $resp_vacone['AssessmentHealthVaccin_id'],
							'pmUser_id' => $this->promedUserId
						));
						if (!is_array($resp_vacdel) || count($resp_vacdel) == 0)
						{
							return array(0 => array('Error_Msg' => 'Ошибка при удалении прививки'));
						}
						else if (!empty($resp_vacdel[0]['Error_Msg']))
						{
							return $resp_vacdel;
						}
					} else {
						$VacExist[] = $resp_vacone['VaccinType_id'];
					}
				}

				// сохраняем новые
				foreach($this->_params['AssessmentHealthVaccinData'] as $VaccinType_id) {
					if (!in_array($VaccinType_id, $VacExist)) {
						$query = "
							declare
								@ErrCode int,
								@AssessmentHealthVaccin_id bigint = null,
								@ErrMessage varchar(4000);
							exec p_AssessmentHealthVaccin_ins
								@AssessmentHealthVaccin_id = @AssessmentHealthVaccin_id output,
								@VaccinType_id = :VaccinType_id,
								@AssessmentHealth_id = :AssessmentHealth_id,
								@pmUser_id = :pmUser_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMessage output;
							select @AssessmentHealthVaccin_id as AssessmentHealthVaccin_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
						";
						$resp_vacdel = $this->queryResult($query, array(
							'AssessmentHealth_id' => $result[0]['AssessmentHealth_id'],
							'VaccinType_id' => $VaccinType_id,
							'pmUser_id' => $this->promedUserId
						));
						if (!is_array($resp_vacdel) || count($resp_vacdel) == 0) {
							return array(0 => array('Error_Msg' => 'Ошибка при сохранении прививки'));
						} else if (!empty($resp_vacdel[0]['Error_Msg'])) {
							return $resp_vacdel;
						}
					}
				}
			}
		}

		// @task https://redmine.swan.perm.ru//issues/117123
		// Удаление дублей
		if ( !empty($this->EvnPLDisp_id) ) {
			$dataList = $this->queryResult("
				select AssessmentHealth_id
				from v_AssessmentHealth with (nolock)
				where EvnPLDisp_id = :EvnPLDisp_id
				order by AssessmentHealth_updDT desc
			", array(
				'EvnPLDisp_id' => $this->EvnPLDisp_id
			));

			if ( is_array($dataList) && count($dataList) > 1 ) {
				unset($dataList[0]);

				foreach ( $dataList as $row ) {
					$rsp = $this->getFirstRowFromQuery("
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);

						exec p_AssessmentHealth_del
							@AssessmentHealth_id = :AssessmentHealth_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;

						select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					", array(
						'AssessmentHealth_id' => $row['AssessmentHealth_id'],
					));

					if ( !is_array($rsp) || count($rsp) == 0) {
						return array(array('Error_Msg' => 'Ошибка при удалении дублей'));
					}
					else if ( !empty($rsp['Error_Msg']) ) {
						return array($rsp);
					}
				}
			}
		}
	}
}
?>