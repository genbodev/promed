<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * MorbusHIV_model - модель для MorbusHIV
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2012 Swan Ltd.
 * @author       Valery Bondarev
 * @version      01.2020
 *
 * Магические свойства
 * @property-read int $morbusTypeId
 * @property-read string $morbusTypeSysNick
 * @property-read string $groupRegistry
 * @property Morbus_model $Morbus
 */
class MorbusHIV_model extends swPgModel
{
	/**
	 * @var bool Требуется ли параметр pmUser_id для хранимки удаления
	 */
	protected $_isNeedPromedUserIdForDel = true;
	/*
	* Список полей для метода updateMorbusSpecific
	*/
	private $entityFields = array(
		'MorbusHIV' => array(
			'Morbus_id',
			'HIVPregPathTransType_id',//Предполагаемый путь инфицирования
			'MorbusHIV_DiagDT',
			'HIVPregInfectStudyType_id',//Стадия ВИЧ-инфекции
			'HIVInfectType_id',//Тип вируса
			'MorbusHIV_CountCD4',//Количество CD4 Т-лимфоцитов (мм)
			'MorbusHIV_PartCD4',//Процент содержания CD4 Т-лимфоцитов
			'MorbusHIVOut_endDT',//Дата снятия с диспансерного наблюдения
			'HIVDispOutCauseType_id',//Причина снятия с диспансерного наблюдения
			'Diag_cid',//Причина смерти
			'MorbusHIV_NumImmun',//№ иммуноблота
			'MorbusHIV_confirmDate',//Дата подтверждения диагноза
			'MorbusHIV_EpidemCode',//Эпидемиологический код
		),
		'Morbus' => array( //allow Deleted
			'MorbusBase_id'
		, 'Evn_pid' //Учетный документ, в рамках которого добавлено заболевание
		, 'Diag_id'
		, 'MorbusKind_id'
		, 'Morbus_Name'
		, 'Morbus_Nick'
		, 'Morbus_disDT'
		, 'Morbus_setDT'
		, 'MorbusResult_id'
		),
		'MorbusBase' => array(
			'Person_id'
		, 'Evn_pid'
		, 'MorbusType_id'
		, 'MorbusBase_setDT'
		, 'MorbusBase_disDT'
		, 'MorbusResult_id'
		)
	);

	protected $_MorbusType_id = null;//9

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * @return string
	 */
	function getMorbusTypeSysNick()
	{
		return 'hiv';
	}

	/**
	 * @return int
	 * @throws Exception
	 */
	function getMorbusTypeId()
	{
		if (empty($this->_MorbusType_id)) {
			$this->load->library('swMorbus');
			$this->_MorbusType_id = swMorbus::getMorbusTypeIdBySysNick($this->getMorbusTypeSysNick());
			if (empty($this->_MorbusType_id)) {
				throw new Exception('Не удалось определить тип заболевания', 500);
			}
		}
		return $this->_MorbusType_id;
	}

	/**
	 * @return string
	 */
	function getGroupRegistry()
	{
		return 'HIV';
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'MorbusHIV';
	}

	/**
	 * Удаление данных специфик заболевания заведенных из регистра, когда заболевание нельзя удалить
	 *
	 * Вызывается из swMorbus::onBeforeDeletePersonRegister
	 * @param PersonRegister_model $model
	 * @param array $data
	 * @throws Exception Если выбросить исключение, то будет отменено удаление записи регистра
	 */
	public function onBeforeDeletePersonRegister(PersonRegister_model $model, $data)
	{
		// тут должно быть реализовано удаление данных введенных в разделах специфики заболевания,
		// в которых нет ссылки на Evn
		// если таковых разделов нет, то этот метод можно убрать
	}

	/**
	 * Удаление данных специфик заболевания заведенных в учетном документе, когда заболевание нельзя удалить
	 *
	 * Вызывается из swMorbus::onBeforeDeleteEvn
	 * @param EvnAbstract_model $evn
	 * @param array $data
	 * @throws Exception Если выбросить исключение, то будет отменено удаление учетного документа
	 */
	public function onBeforeDeleteEvn(EvnAbstract_model $evn, $data)
	{
		// тут должно быть реализовано удаление данных введенных в разделах специфики заболевания,
		// в которых есть ссылка на Evn
		// если таковых нет, то этот метод можно убрать
	}

	/**
	 * Проверка существования заболевания у человека, может быть только одно заболевание на человеке
	 * Обязательные параметры: Evn_pid или Person_id
	 * @return array
	 */
	private function checkMorbusSpecific($data, $only_open = false)
	{
		$query = "
			with t as (
                select
                    M.Morbus_disDT,
                    MB.MorbusBase_id,
                    M.Morbus_id,
                    MO.MorbusHIV_id
                from v_Morbus M
                inner join v_MorbusBase MB on M.MorbusBase_id = MB.MorbusBase_id
                    and MB.MorbusType_id = :MorbusType_id
                    and MB.Person_id = (select CASE WHEN (COALESCE(:Evn_pid, 0) > 0 and (COALESCE(:Person_id, 0) = 0))
                    THEN (select Evn.Person_id from Evn
                    where Evn.Evn_id = :Evn_pid) ELSE :Person_id END)
                    " . (($only_open) ? 'and M.Morbus_disDT is null' : '') . "
                left join v_MorbusHIV MO on M.Morbus_id = MO.Morbus_id
                order by M.Morbus_setDT DESC
                limit 1
			)
			
			select 
				case when t.Morbus_disDT is null then 1 else 2 end as \"Morbus_isClose\", 
				:Evn_pid as \"Evn_pid\", 
				(select CASE WHEN (COALESCE(:Evn_pid, 0) > 0 and (COALESCE(:Person_id, 0) = 0))
				THEN (select Evn.Person_id from Evn
				where Evn.Evn_id = :Evn_pid) ELSE :Person_id END) as \"Person_id\", 
				t.MorbusHIV_id as \"MorbusHIV_id\", 
				t.Morbus_id as \"Morbus_id\", 
				t.MorbusBase_id as \"MorbusBase_id\"
			from t;					
		";
		try {
			$data['Evn_pid'] = isset($data['Evn_pid']) ? $data['Evn_pid'] : null;
			$data['Person_id'] = isset($data['Person_id']) ? $data['Person_id'] : null;
			$data['MorbusType_id'] = $this->getMorbusTypeId();
			// echo getDebugSQL($query, $data); exit();
			$result = $this->db->query($query, $data);
			if (!is_object($result)) {
				throw new Exception('Ошибка БД');
			}
			return $result->result('array');
		} catch (Exception $e) {
			return array(array('Error_Msg' => 'Проверка существования заболевания у человека. ' . $e->getMessage()));
		}
	}

	/**
	 * Создание заболевания у человека
	 * Обязательные параметры:
	 * 1) Evn_pid или пара Person_id и Diag_id
	 * 2) pmUser_id
	 * @return array
	 */
	private function createMorbusSpecific($data)
	{
		$query = "
			SELECT
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Message\",
				MorbusHIV as \"MorbusHIV\",
				2 as \"IsCreate\"
			FROM p_MorbusHIV_ins (
					MorbusHIV_id = :MorbusHIV_id, 
					Morbus_id = (CASE WHEN (COALESCE(:Morbus_id, 0) = 0) THEN
						(select Morbus_id 
							from p_Morbus_ins (
								Morbus_id := :Morbus_id,
								MorbusBase_id := CASE WHEN COALESCE((select MorbusBase_id from v_MorbusBase where Person_id = :Person_id and MorbusType_id = :MorbusType_id and MorbusBase_disDT is null limit 1), 0) = 0 THEN 
									(select MorbusBase_id 
										from p_MorbusBase_ins (
											MorbusBase_id := :MorbusBase_id,
											Person_id := (SELECT CASE WHEN (COALESCE(:Env_pid, 0) > 0 AND COALESCE(:Person_id, 0) = 0 AND COALESCE(:Diag_id, 0) = 0) THEN
												(select Evn.Person_id from Evn
													left join v_EvnVizitPL PL on Evn.Evn_id = PL.EvnVizitPL_id
													left join v_EvnSection ST on Evn.Evn_id = ST.EvnSection_id
													where Evn.Evn_id = :Evn_pid)),
											MorbusBase_setDT := (SELECT CASE WHEN :Morbus_setDT IS NULL THEN GetDate() ELSE :Morbus_setDT END),
											MorbusType_id := :MorbusType_id,
											Evn_pid := :Evn_pid,
											pmUser_id := :pmUser_id
											) ELSE :MorbusBase_id END,
								Morbus_setDT := (SELECT CASE WHEN :Morbus_setDT IS NULL THEN GetDate() ELSE :Morbus_setDT END),
								Evn_pid := :Evn_pid,
								Diag_id := (SELECT CASE WHEN (COALESCE(:Env_pid, 0) > 0 AND COALESCE(:Person_id, 0) = 0 AND COALESCE(:Diag_id, 0) = 0) THEN
									(select COALESCE(PL.Diag_id,ST.Diag_id) from Evn
										left join v_EvnVizitPL PL on Evn.Evn_id = PL.EvnVizitPL_id
										left join v_EvnSection ST on Evn.Evn_id = ST.EvnSection_id
										where Evn.Evn_id = :Evn_pid)),
								pmUser_id := :pmUser_id
								)) ELSE :Morbus_id END),
					MorbusHIV_DiagDT = (SELECT CASE WHEN :Morbus_setDT IS NULL THEN GetDate() ELSE :Morbus_setDT END),
					pmUser_id = :pmUser_id
					)
			WHERE COALESCE(:MorbusHIV_id, 0) <> 0
			
			UNION ALL
			
			SELECT
				'' as \"Error_Code\",
				'' as \"Error_Message\",
				:MorbusHIV as \"MorbusHIV\",
				1 \"IsCreate\"
			
			WHERE COALESCE(:MorbusHIV_id, 0) = 0
		";
		try {
			$data['MorbusHIV_id'] = null;
			$data['Morbus_id'] = isset($data['Morbus_id']) ? $data['Morbus_id'] : null;
			$data['MorbusBase_id'] = isset($data['MorbusBase_id']) ? $data['MorbusBase_id'] : null;
			$data['Evn_pid'] = isset($data['Evn_pid']) ? $data['Evn_pid'] : null;
			$data['Person_id'] = isset($data['Person_id']) ? $data['Person_id'] : null;
			$data['Diag_id'] = isset($data['Diag_id']) ? $data['Diag_id'] : null;
			$data['Morbus_setDT'] = isset($data['Morbus_setDT']) ? $data['Morbus_setDT'] : null;
			//$data['Lpu_id'] = isset($data['Lpu_id'])?$data['Lpu_id']:null;
			$data['MorbusType_id'] = $this->getMorbusTypeId();
			//echo getDebugSQL($query, $data); exit();
			// Стартуем транзакцию
			if (!$this->beginTransaction()) {
				throw new Exception('Ошибка при попытке запустить транзакцию');
			}

			// создаем заболевание
			$result = $this->db->query($query, $data);
			if (!is_object($result)) {
				throw new Exception('Ошибка БД');
			}
			$tmp = $result->result('array');
			if (isset($tmp[0]['Error_Msg'])) {
				$this->rollbackTransaction();
				throw new Exception($tmp[0]['Error_Msg']);
			}
			$response = $tmp;
			$data['Morbus_id'] = $tmp[0]['Morbus_id'];
			$data['MorbusHIV_id'] = $tmp[0]['MorbusHIV_id'];

			// определяем гражданство
			$tmp = $this->definePatriality($data);
			if (isset($tmp[0]['Error_Msg'])) {
				$this->rollbackTransaction();
				throw new Exception($tmp[0]['Error_Msg']);
			}
			$data['HIVContingentType_pid'] = $tmp[0]['HIVContingentType_id'];

			//Сохраняем гражданство в MorbusHIVContingent
			$tmp = $this->saveMorbusHIVContingentListWithMorbusHIV_id($data);
			if (isset($tmp[0]['Error_Msg'])) {
				$this->rollbackTransaction();
				throw new Exception($tmp[0]['Error_Msg']);
			}
			$response[0]['MorbusHIVContingent_id_list'] = $tmp[0]['MorbusHIVContingent_id_list'];

			$this->commitTransaction();
			return $response;
		} catch (Exception $e) {
			return array(array('Error_Msg' => 'Создание заболевания у человека. ' . $e->getMessage()));
		}
	}

	/**
	 * Определение диагноза последнего учетного документа, относящегося к ВИЧ
	 * Обязательные параметры:
	 * 1) Evn_pid или Person_id
	 * 2) pmUser_id
	 * @return array
	 * @comment Диагноз заболевания – это диагноз последнего учетного документа, относящегося к ВИЧ
	 */
	private function getLastEvnAndDiagByMorbus($data)
	{
		$query = "
			select
				Evn.Evn_id as \"Evn_id\",
				Evn.Person_id as \"Person_id\",
				MD.Diag_id as \"Diag_id\"
			from v_Evn Evn
			left join v_EvnVizitPL PL on Evn.Evn_id = PL.EvnVizitPL_id
			left join v_EvnSection ST on Evn.Evn_id = ST.EvnSection_id
			inner join v_MorbusDiag MD on MD.MorbusType_id = :MorbusType_id and MD.Diag_id = COALESCE(PL.Diag_id,ST.Diag_id)
			where
				Evn.Person_id = (CASE WHEN (COALESCE(:Evn_pid, 0) > 0 AND COALESCE(:Person_id, 0) = 0) THEN (select Evn.Person_id from Evn
				where Evn.Evn_id = :Evn_pid) ELSE :Person_id END)
				and Evn.EvnClass_id in (11,32)
				
			order by
				Evn.Evn_setDT desc
			limit 1
		";
		try {
			$data['Evn_pid'] = isset($data['Evn_pid']) ? $data['Evn_pid'] : null;
			$data['Person_id'] = isset($data['Person_id']) ? $data['Person_id'] : null;
			$data['MorbusType_id'] = $this->getMorbusTypeId();
			// echo getDebugSQL($query, $data); exit();
			$result = $this->db->query($query, $data);
			if (!is_object($result)) {
				throw new Exception('Ошибка БД');
			}
			return $result->result('array');
		} catch (Exception $e) {
			return array(array('Error_Msg' => 'Определение диагноза последнего учетного документа, относящегося к ВИЧ. ' . $e->getMessage()));
		}
	}

	/**
	 * Проверка обязательных параметров специфики
	 *
	 * @params Mode
	 *    - personregister_viewform - это ввод данных специфики из панели просмотра в форме записи регистра
	 *    - evnsection_viewform - это ввод данных специфики из панели просмотра движения в ЭМК
	 *    - evnvizitpl_viewform - это ввод данных специфики из панели просмотра посещения в ЭМК
	 */
	private function checkParams($data)
	{
		if (empty($data['Mode'])) {
			throw new Exception('Не указан режим сохранения');
		}
		$check_fields_list = array();
		$fields = array(
			'Diag_id' => 'Идентификатор диагноза'
		, 'Person_id' => 'Идентификатор человека'
		, 'Evn_pid' => 'Идентификатор движения/посещения'
		, 'pmUser_id' => 'Идентификатор пользователя'
		, 'Morbus_id' => 'Идентификатор заболевания'
		, 'MorbusHIV_id' => 'Идентификатор специфики заболевания'
		, 'LabAssessmentResult_iid' => 'Идентификатор результата реакции иммуноблота'
		, 'LabAssessmentResult_cid' => 'Идентификатор результата полимеразной цепной реакции'
		);
		switch ($data['Mode']) {
			case 'evnnotifyhivdisp_form':
			case 'personregister_viewform':
				$check_fields_list = array('MorbusHIV_id', 'Morbus_id', 'Person_id', 'pmUser_id');
				$data['Evn_pid'] = null;
				if (!empty($data['MorbusHIVLab_BlotDT'])) {
					$check_fields_list[] = 'LabAssessmentResult_iid';
				}
				if (!empty($data['MorbusHIVLab_PCRDT'])) {
					$check_fields_list[] = 'LabAssessmentResult_cid';
				}
				break;
			case 'evnsection_viewform':
			case 'evnvizitpl_viewform':
				$check_fields_list = array('MorbusHIV_id', 'Morbus_id', 'Evn_pid', 'pmUser_id');
				break;
			case 'check_by_personregister':
				$check_fields_list = array('Person_id', 'pmUser_id');
				$data['Evn_pid'] = null;
				break;
			default:
				throw new Exception('Указан неправильный режим сохранения');
				break;
		}
		$errors = array();
		foreach ($check_fields_list as $field) {
			if (empty($data[$field])) {
				$errors[] = 'Не указан ' . $fields[$field];
			}
		}
		if (count($errors) > 0) {
			throw new Exception(implode('<br />', $errors));
		}
		return $data;
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function checkByPersonRegister($data)
	{
		try {
			$data['Mode'] = 'check_by_personregister';
			$data = $this->checkParams($data);
			//Проверка существования ОТКРЫТОГО заболевания у человека с данной группой диагнозов
			$result = $this->checkMorbusSpecific($data, true);
			if (isset($result[0]['Error_Msg'])) {
				throw new Exception($result[0]['Error_Msg']);
			}
			if (isset($result[0]['MorbusHIV_id']) && isset($result[0]['Morbus_id'])) {
				//В системе найдено заболеваниe, возвращаем результат проверки
				return $result;
			}
			//Ищем последний учетный документ у человека с данной группой диагнозов для привязки заболевания к нему
			$result = $this->getLastEvnAndDiagByMorbus($data);
			$data['Evn_aid'] = null;
			if (!empty($result)) {
				if (isset($result[0]['Error_Msg'])) {
					throw new Exception($result[0]['Error_Msg']);
				}
				$data['Evn_aid'] = $result[0]['Evn_id'];
				$data['Person_id'] = $result[0]['Person_id'];
			}
			//Создание заболевания с диагнозом и датой, указанными в регистре
			$data['MorbusBase_id'] = null;
			$data['Morbus_id'] = null;
			$data['MorbusHIV_id'] = null;
			$data['Evn_pid'] = $data['Evn_aid'];
			return $this->createMorbusSpecific($data);
		} catch (Exception $e) {
			return array(array('Error_Msg' => 'Проверка существования/создание заболевания ВИЧ по идентификатору человека. <br />' . $e->getMessage()));
		}
	}

	/**
	 * Сохранение специфики заболевания
	 * Обязательные параметры:
	 * 1) Evn_pid или Person_id
	 * 2) pmUser_id
	 * 3) Mode
	 *    - personregister_viewform - это ввод данных специфики из панели просмотра в форме записи регистра
	 *    - evnsection_viewform - это ввод данных специфики из панели просмотра движения в ЭМК
	 *    - evnvizitpl_viewform - это ввод данных специфики из панели просмотра посещения в ЭМК
	 * @return array Идентификаторы заболевания, специфики заболевания или ошибка
	 * @comment Будут сохранены те данные, которые переданы, т.е. можно отдельные параметры сохранять
	 * @author Alexander Permyakov aka Alexpm
	 */
	function saveMorbusSpecific($data)
	{
		try {
			$data = $this->checkParams($data);

			// контроль на уникальность введенного значения в поле № иммуноблота
			$this->checkMorbusHivNumImmun($data['MorbusHIV_id'], $data['MorbusHIV_NumImmun']);
			$this->checkDiag($data);
			$data['Evn_aid'] = null;
			/* Редактирование из учетного документа не реализовано, поэтому не нужна
			// Проверка существования у человека актуального учетного документа с данной группой диагнозов для привязки к нему заболевания и определения последнего диагноза заболевания
			if (empty($data['Evn_pid'])) {
				$data['Evn_pid'] = null;
			}
			if (empty($data['Person_id'])) {
				$data['Person_id'] = null;
			}
			if (empty($data['Diag_id'])) {
				$data['Diag_id'] = null;
			}
			$this->load->library('swMorbus');
			$tmp = swMorbus::getStaticMorbusCommon()->loadLastEvnData($this->getMorbusTypeSysNick(), $data['Evn_pid'], $data['Person_id'], $data['Diag_id']);
			if (empty($tmp)) {
				if ( in_array($data['Mode'],array('evnsection_viewform','evnvizitpl_viewform')) ) {
					throw new Exception('Ошибка определения актуального учетного документа с данным заболеванием');
				}
				$data['Evn_aid'] = null;
			} else {
				//учетный документ найден
				$data['Evn_aid'] = $tmp[0]['Evn_id'];
				$data['Diag_id'] = $tmp[0]['Diag_id'];
				$data['Person_id'] = $tmp[0]['Person_id'];
			}
			*/
			if ($data['Mode'] == 'personregister_viewform' || $data['Evn_pid'] == $data['Evn_aid']) {
				// Если редактирование происходит из актуального учетного документа или из панели просмотра в форме записи регистра, то сохраняем данные

				// Стартуем транзакцию
				if (!$this->beginTransaction()) {
					throw new Exception('Ошибка при попытке запустить транзакцию');
				}
				//update таблиц Morbus, MorbusHIV
				$tmp = $this->updateMorbusSpecific($data);
				if (isset($tmp[0]['Error_Msg'])) {
					$this->rollbackTransaction();
					throw new Exception($tmp[0]['Error_Msg']);
				}
				$response = $tmp;

				if ($data['Mode'] != 'evnnotifyhivdisp_form') {
					//Сохраняем MorbusHIVLab
					$tmp = $this->saveMorbusHIVLabWithMorbusHIV_id($data);
					if (isset($tmp[0]['Error_Msg'])) {
						$this->rollbackTransaction();
						throw new Exception($tmp[0]['Error_Msg']);
					}
					$response[0]['MorbusHIVLab_id'] = $tmp[0]['MorbusHIVLab_id'];

					//Сохраняем MorbusHIVContingent
					$tmp = $this->saveMorbusHIVContingentListWithMorbusHIV_id($data);
					if (isset($tmp[0]['Error_Msg'])) {
						$this->rollbackTransaction();
						throw new Exception($tmp[0]['Error_Msg']);
					}
					$response[0]['MorbusHIVContingent_id_list'] = $tmp[0]['MorbusHIVContingent_id_list'];
				}

				$this->commitTransaction();
				return $response;
			} else {
				//Ничего не сохраняем
				throw new Exception('Данные не были сохранены, т.к. данный учетный документ не является актуальным для данного заболевания.');
			}
		} catch (Exception $e) {
			return array(array('Error_Msg' => 'Сохранение специфики заболевания. <br />' . $e->getMessage()));
		}
	}

	/**
	 * Создание специфики заболевания
	 * @param array $data
	 * @param bool $isAllowTransaction
	 * @return array
	 * @throws Exception
	 */
	public function autoCreate($data, $isAllowTransaction = true)
	{
		if (empty($data['MorbusBase_id']) || empty($data['Person_id'])
			|| empty($data['Morbus_id']) || empty($data['Diag_id']) || empty($data['Morbus_setDT'])
			|| empty($data['mode'])
			|| false == in_array($data['mode'], array('onBeforeViewData', 'onBeforeSavePersonRegister', 'onBeforeSaveEvnNotify'))
		) {
			throw new Exception('Переданы неправильные параметры', 500);
		}
		$this->setParams($data);
		$tableName = $this->tableName();
		$queryParams = array();
		$queryParams['pmUser_id'] = $this->promedUserId;
		$queryParams['Morbus_id'] = $data['Morbus_id'];
		$queryParams['Morbus_setDT'] = $data['Morbus_setDT'];
		$queryParams['Morbus_confirmDate'] = !empty($data['Morbus_confirmDate']) ? $data['Morbus_confirmDate'] : null;
		$queryParams['Morbus_EpidemCode'] = !empty($data['Morbus_EpidemCode']) ? $data['Morbus_EpidemCode'] : null;

		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Message\",
				{$tableName}_id as \"{$tableName}_id\"
			from p_{$tableName}_ins (
					{$tableName}_id := (select {$tableName}_id from v_{$tableName} where Morbus_id = :Morbus_id limit 1),
					Morbus_id := :Morbus_id,
					MorbusHIV_DiagDT := :Morbus_setDT,
					MorbusHIV_confirmDate := :Morbus_confirmDate,
					MorbusHIV_EpidemCode := :Morbus_EpidemCode,
					pmUser_id := :pmUser_id
					)
			where (select {$tableName}_id from v_{$tableName} where Morbus_id = :Morbus_id limit 1) IS NOT NULL;
		";
		//echo getDebugSQL($query, $queryParams); exit();
		$this->isAllowTransaction = $isAllowTransaction;
		// Стартуем транзакцию
		if (!$this->beginTransaction()) {
			throw new Exception('Ошибка при попытке запустить транзакцию');
		}
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			$this->rollbackTransaction();
			throw new Exception('Ошибка БД', 500);
		}
		$resp = $result->result('array');
		if (!empty($resp[0]['Error_Msg'])) {
			$this->rollbackTransaction();
			throw new Exception($resp[0]['Error_Msg'], 500);
		}
		if (empty($resp[0][$tableName . '_id'])) {
			$this->rollbackTransaction();
			throw new Exception('Что-то пошло не так', 500);
		}
		$this->_saveResponse[$tableName . '_id'] = $resp[0][$tableName . '_id'];

		// определяем гражданство
		$data['pmUser_id'] = $this->promedUserId;
		$data[$tableName . '_id'] = $this->_saveResponse[$tableName . '_id'];
		$tmp = $this->definePatriality($data);
		if (isset($tmp[0]['Error_Msg'])) {
			$this->rollbackTransaction();
			throw new Exception($tmp[0]['Error_Msg']);
		}
		$data['HIVContingentType_pid'] = $tmp[0]['HIVContingentType_id'];

		//Сохраняем гражданство в MorbusHIVContingent
		$tmp = $this->saveMorbusHIVContingentListWithMorbusHIV_id($data);
		if (isset($tmp[0]['Error_Msg'])) {
			$this->rollbackTransaction();
			throw new Exception($tmp[0]['Error_Msg']);
		}
		$this->_saveResponse['MorbusHIVContingent_id_list'] = $tmp[0]['MorbusHIVContingent_id_list'];

		$this->commitTransaction();
		return $this->_saveResponse;
	}

	/**
	 * Проверка на наличие в системе записи регистра с пустым атрибутом «Дата исключения из регистра»
	 * на данного человека с указанной «Датой смерти»
	 */
	function checkPersonDead($data)
	{
		$query = "select
			ps.Server_id as \"Server_id\"
			,ps.Person_id as \"Person_id\"
			,ps.PersonEvn_id as \"PersonEvn_id\"
			,ps.Person_SurName as \"Person_SurName\"
			,ps.Person_FirName as \"Person_FirName\"
			,ps.Person_SecName as \"Person_SecName\"
			,DATEDIFF('SECOND', '1970', ps.Person_BirthDay) as \"Person_BirthDay\"
			from v_PersonState ps
			inner join v_PersonRegister pr on pr.Person_id = ps.Person_id
			inner join v_MorbusType mt on pr.MorbusType_id = mt.MorbusType_id
			where 
				ps.Person_deadDT is not null 
				and pr.PersonRegister_disDate is null
				and mt.MorbusType_SysNick = 'HIV'
				and ps.Person_id = :Person_id
			limit 1";
		$result = $this->db->query($query, array('Person_id' => data['Person_id']));
		$response = $result->result('array');
		if (is_array($response) && count($response) > 0) {
			return $response;
		} else {
			return false;
		}
	}

	/**
	 *  Получение списка пользователей с группой «Регистр по HIV»
	 */
	function getUsersHIV($data)
	{
		$query = "
		select 
			PMUser_id as \"PMUser_id\" 
		from 
			v_pmUserCache
		where 
			pmUser_groups like '%\"HIV\"%'
			and Lpu_id = :Lpu_id";
		$result = $this->db->query($query, array('Lpu_id' => $data['Lpu_id']));
		$response = $result->result('array');
		if (is_array($response) && count($response) > 0) {
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Получает список «Вторичные заболевания и оппортунистические инфекции»
	 */
	function getMorbusHIVSecDiagViewData($data)
	{
		$filter = "(1=1)";
		if (isset($data['MorbusHIVSecDiag_id']) and ($data['MorbusHIVSecDiag_id'] > 0)) {
			$filter = "MM.MorbusHIVSecDiag_id = :MorbusHIVSecDiag_id";
		} else if (isset($data['MorbusHIV_id']) and $data['MorbusHIV_id'] > 0) {
			$filter = "MM.MorbusHIV_id = :MorbusHIV_id and MM.EvnNotifyBase_id is null ";
		} else if (isset($data['EvnNotifyBase_id']) and $data['EvnNotifyBase_id'] > 0) {
			$filter = "MM.EvnNotifyBase_id = :EvnNotifyBase_id and MM.MorbusHIV_id is null ";
		} else if (isset($data['Morbus_id']) and $data['Morbus_id'] > 0) {
			$filter = "M.Morbus_id = :Morbus_id and MM.EvnNotifyBase_id is null ";
		} else {
			return array();
		}

		$query = "
			select
				case when (COALESCE(EvnEdit.Evn_IsSigned,1) = 1) and M.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				to_char(MM.MorbusHIVSecDiag_setDT,'dd.mm.yyyy') as \"MorbusHIVSecDiag_setDT\",
				MM.Diag_id as \"Diag_id\",
				Diag.Diag_FullName as \"Diag_FullName\",
				Diag.Diag_FullName as \"Diag_Name\",
				MM.MorbusHIVSecDiag_id as \"MorbusHIVSecDiag_id\",
				MM.EvnNotifyBase_id as \"EvnNotifyBase_id\",
				MM.MorbusHIV_id as \"MorbusHIV_id\",
				M.Morbus_id as \"Morbus_id\",
				:Evn_id as \"MorbusHIV_pid\"
			from
				v_MorbusHIVSecDiag MM
				inner join v_MorbusHIV MH on MH.MorbusHIV_id = MM.MorbusHIV_id
				inner join v_Morbus M on M.Morbus_id = MH.Morbus_id
				inner join v_MorbusBase MB on M.MorbusBase_id = MB.MorbusBase_id
				left join v_Diag Diag on Diag.Diag_id = MM.Diag_id
				left join v_Evn EvnEdit on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
			where
				' . $filter . '
		";

		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику «Вторичные заболевания и оппортунистические инфекции»
	 * @param $data
	 * @return array
	 */
	function saveMorbusHIVSecDiag($data)
	{
		$procedure = '';

		if ((!isset($data['MorbusHIVSecDiag_id'])) || ($data['MorbusHIVSecDiag_id'] <= 0)) {
			$procedure = 'p_MorbusHIVSecDiag_ins';
		} else {
			$procedure = 'p_MorbusHIVSecDiag_upd';
		}

		$query = "
			select
                Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\",
                MorbusHIVSecDiag_id as \"MorbusHIVSecDiag_id\"
			from " . $procedure . " (
				MorbusHIVSecDiag_id := :MorbusHIVSecDiag_id,
				MorbusHIV_id := :MorbusHIV_id,
				EvnNotifyBase_id := :EvnNotifyBase_id,
				MorbusHIVSecDiag_setDT := :MorbusHIVSecDiag_setDT,
				Diag_id := :Diag_id,
				pmUser_id := :pmUser_id
				);
		";
		if (!empty($data['MorbusHIV_id'])) {
			$data['EvnNotifyBase_id'] = null;
		}
		if (!empty($data['EvnNotifyBase_id'])) {
			$data['MorbusHIV_id'] = null;
		}
		try {
			if (empty($data['EvnNotifyBase_id']) && empty($data['MorbusHIV_id'])) {
				throw new Exception('Не указано извещение или заболевание');
			}
			if (empty($data['Diag_id'])) {
				throw new Exception('Не указано заболевание');
			}
			if (empty($data['MorbusHIVSecDiag_setDT'])) {
				$data['MorbusHIVSecDiag_setDT'] = date('Y-m-d');
			}
			//echo getDebugSQL($query, $data);die();
			$result = $this->db->query($query, $data);
			if (!is_object($result)) {
				throw new Exception('Ошибка БД');
			}
			return $result->result('array');
		} catch (Exception $e) {
			return array(array('Error_Msg' => 'Сохранение специфики «Вторичные заболевания и оппортунистические инфекции». <br />' . $e->getMessage()));
		}
	}


	/**
	 * Получает список ««Вакцинация»»
	 */
	function getMorbusHIVVacViewData($data)
	{
		$filter = "(1=1)";
		if (isset($data['MorbusHIVVac_id']) and ($data['MorbusHIVVac_id'] > 0)) {
			$filter = "MM.MorbusHIVVac_id = :MorbusHIVVac_id";
		} else if (isset($data['MorbusHIV_id']) and $data['MorbusHIV_id'] > 0) {
			$filter = "MM.MorbusHIV_id = :MorbusHIV_id and MM.EvnNotifyBase_id is null ";
		} else if (isset($data['EvnNotifyBase_id']) and $data['EvnNotifyBase_id'] > 0) {
			$filter = "MM.EvnNotifyBase_id = :EvnNotifyBase_id and MM.MorbusHIV_id is null ";
		} else if (isset($data['Morbus_id']) and $data['Morbus_id'] > 0) {
			$filter = "M.Morbus_id = :Morbus_id and MM.EvnNotifyBase_id is null ";
		} else {
			return array();
		}

		$query = "
			select
				case when (COALESCE(EvnEdit.Evn_IsSigned,1) = 1) and M.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				to_char(MM.MorbusHIVVac_setDT,'dd.mm.yyyy') as \"MorbusHIVVac_setDT\",
				MM.Drug_id as \"Drug_id\",
				RTRIM(COALESCE(DrugPrep.DrugPrep_Name, '')) as \"Drug_Name\",
				MM.MorbusHIVVac_id as \"MorbusHIVVac_id\",
				MM.EvnNotifyBase_id as \"EvnNotifyBase_id\",
				MM.MorbusHIV_id as \"MorbusHIV_id\",
				M.Morbus_id as \"Morbus_id\",
				:Evn_id as \"MorbusHIV_pid\"
			from
				v_MorbusHIVVac MM
				inner join v_MorbusHIV MH on MH.MorbusHIV_id = MM.MorbusHIV_id and MM.EvnNotifyBase_id is null
				inner join v_Morbus M on M.Morbus_id = MH.Morbus_id
				inner join v_MorbusBase MB on M.MorbusBase_id = MB.MorbusBase_id
				left join rls.v_Drug Drug on Drug.Drug_id = MM.Drug_id
				left join rls.v_DrugPrep DrugPrep on Drug.DrugPrepFas_id = DrugPrep.DrugPrepFas_id
				left join v_Evn EvnEdit on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
			where
				{$filter}
		";

		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику «Вакцинация»
	 * @param $data
	 * @return array
	 */
	function saveMorbusHIVVac($data)
	{
		$procedure = '';

		if ((!isset($data['MorbusHIVVac_id'])) || ($data['MorbusHIVVac_id'] <= 0)) {
			$procedure = 'p_MorbusHIVVac_ins';
		} else {
			$procedure = 'p_MorbusHIVVac_upd';
		}

		$query = "
			select
               Error_Code as \"Error_Code\",
			   Error_Message as \"Error_Msg\",
               MorbusHIVVac_id as \"MorbusHIVVac_id\"
			from " . $procedure . " (
				MorbusHIVVac_id := :MorbusHIVVac_id,
				MorbusHIV_id := :MorbusHIV_id,
				EvnNotifyBase_id := :EvnNotifyBase_id,
				MorbusHIVVac_setDT := :MorbusHIVVac_setDT,
				Drug_id := :Drug_id,
				pmUser_id := :pmUser_id
				);
		";
		if (!empty($data['MorbusHIV_id'])) {
			$data['EvnNotifyBase_id'] = null;
		}
		if (!empty($data['EvnNotifyBase_id'])) {
			$data['MorbusHIV_id'] = null;
		}
		try {
			if (empty($data['EvnNotifyBase_id']) && empty($data['MorbusHIV_id'])) {
				throw new Exception('Не указано извещение или заболевание');
			}
			if (empty($data['Drug_id'])) {
				throw new Exception('Не указан препарат');
			}
			if (empty($data['MorbusHIVVac_setDT'])) {
				throw new Exception('Не указанa дата');
			}
			//echo getDebugSQL($query, $data);die();
			$result = $this->db->query($query, $data);
			if (!is_object($result)) {
				throw new Exception('Ошибка БД');
			}
			return $result->result('array');
		} catch (Exception $e) {
			return array(array('Error_Msg' => 'Сохранение специфики «Вакцинация». <br />' . $e->getMessage()));
		}
	}

	/**
	 * Получает список «Проведение перинатальной профилактики ВИЧ»
	 */
	function getMorbusHIVChemPregViewData($data)
	{
		$filter = "(1=1)";

		if (isset($data['MorbusHIVChemPreg_id']) and ($data['MorbusHIVChemPreg_id'] > 0)) {
			$filter = "MM.MorbusHIVChemPreg_id = :MorbusHIVChemPreg_id";
		} else if (isset($data['MorbusHIV_id']) and $data['MorbusHIV_id'] > 0) {
			$filter = "MM.MorbusHIV_id = :MorbusHIV_id and MM.EvnNotifyBase_id is null ";
		} else if (isset($data['EvnNotifyBase_id']) and $data['EvnNotifyBase_id'] > 0) {
			$filter = "MM.EvnNotifyBase_id = :EvnNotifyBase_id and MM.MorbusHIV_id is null ";
		} else if (isset($data['Morbus_id']) and $data['Morbus_id'] > 0) {
			$filter = "M.Morbus_id = :Morbus_id and MM.EvnNotifyBase_id is null ";
		} else {
			return array();
		}

		$query = "
			select
				case when (COALESCE(EvnEdit.Evn_IsSigned,1) = 1) and M.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				MM.HIVPregnancyTermType_id as \"HIVPregnancyTermType_id\",
				PTT.HIVPregnancyTermType_Name as \"HIVPregnancyTermType_Name\",
				MM.MorbusHIVChemPreg_Dose as \"MorbusHIVChemPreg_Dose\",
				MM.Drug_id as \"Drug_id\",
				RTRIM(COALESCE(DrugPrep.DrugPrep_Name, '')) as \"Drug_Name\",
				MM.MorbusHIVChemPreg_id as \"MorbusHIVChemPreg_id\",
				MM.EvnNotifyBase_id as \"EvnNotifyBase_id\",
				MM.MorbusHIV_id as \"MorbusHIV_id\",
				M.Morbus_id as \"Morbus_id\",
				:Evn_id as \"MorbusHIV_pid\"
			from
				v_MorbusHIVChemPreg MM
				inner join v_MorbusHIV MH on MH.MorbusHIV_id = MM.MorbusHIV_id and MM.EvnNotifyBase_id is null
				inner join v_Morbus M on M.Morbus_id = MH.Morbus_id
				inner join v_MorbusBase MB on M.MorbusBase_id = MB.MorbusBase_id
				left join v_HIVPregnancyTermType PTT  on PTT.HIVPregnancyTermType_id = MM.HIVPregnancyTermType_id
				left join rls.v_Drug Drug  on Drug.Drug_id = MM.Drug_id
				left join rls.v_DrugPrep DrugPrep on Drug.DrugPrepFas_id = DrugPrep.DrugPrepFas_id
				left join v_Evn EvnEdit on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
			where
				{$filter}
		";

		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику «Проведение перинатальной профилактики ВИЧ»
	 * @param $data
	 * @return array
	 */
	function saveMorbusHIVChemPreg($data)
	{
		$procedure = '';

		if ((!isset($data['MorbusHIVChemPreg_id'])) || ($data['MorbusHIVChemPreg_id'] <= 0)) {
			$procedure = 'p_MorbusHIVChemPreg_ins';
		} else {
			$procedure = 'p_MorbusHIVChemPreg_upd';
		}

		$query = "
			select
                Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\",
                MorbusHIVChemPreg_id as \"MorbusHIVChemPreg_id\"
			from " . $procedure . " (
				MorbusHIVChemPreg_id := :MorbusHIVChemPreg_id,
				MorbusHIV_id := :MorbusHIV_id,
				EvnNotifyBase_id := :EvnNotifyBase_id,
				HIVPregnancyTermType_id := :HIVPregnancyTermType_id,
				Drug_id := :Drug_id,
				MorbusHIVChemPreg_Dose := :MorbusHIVChemPreg_Dose,
				pmUser_id := :pmUser_id
				);
		";
		if (!empty($data['MorbusHIV_id'])) {
			$data['EvnNotifyBase_id'] = null;
		}
		if (!empty($data['EvnNotifyBase_id'])) {
			$data['MorbusHIV_id'] = null;
		}
		try {
			if (empty($data['EvnNotifyBase_id']) && empty($data['MorbusHIV_id'])) {
				throw new Exception('Не указано извещение или заболевание');
			}
			if (empty($data['Drug_id'])) {
				throw new Exception('Не указан препарат');
			}
			if (empty($data['MorbusHIVChemPreg_Dose'])) {
				throw new Exception('Не указанa доза');
			}
			if (empty($data['HIVPregnancyTermType_id'])) {
				throw new Exception('Не указан период проведения');
			}
			//echo getDebugSQL($query, $data);die();
			$result = $this->db->query($query, $data);
			if (!is_object($result)) {
				throw new Exception('Ошибка БД');
			}
			return $result->result('array');
		} catch (Exception $e) {
			return array(array('Error_Msg' => 'Сохранение специфики «Проведение перинатальной профилактики ВИЧ». <br />' . $e->getMessage()));
		}
	}

	/**
	 * Получает список «Проведение химиопрофилактики ВИЧ-инфекции»
	 */
	function getMorbusHIVChemViewData($data)
	{
		$filter = "(1=1)";

		if (isset($data['MorbusHIVChem_id']) and ($data['MorbusHIVChem_id'] > 0)) {
			$filter = "MM.MorbusHIVChem_id = :MorbusHIVChem_id";
		} else if (isset($data['MorbusHIV_id']) and $data['MorbusHIV_id'] > 0) {
			$filter = "MM.MorbusHIV_id = :MorbusHIV_id and MM.EvnNotifyBase_id is null ";
		} else if (isset($data['EvnNotifyBase_id']) and $data['EvnNotifyBase_id'] > 0) {
			$filter = "MM.EvnNotifyBase_id = :EvnNotifyBase_id and MM.MorbusHIV_id is null ";
		} else if (isset($data['Morbus_id']) and $data['Morbus_id'] > 0) {
			$filter = "M.Morbus_id = :Morbus_id and MM.EvnNotifyBase_id is null ";
		} else {
			return array();
		}

		$query = "
			select
				case when (COALESCE(EvnEdit.Evn_IsSigned,1) = 1) and M.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				to_char(MM.MorbusHIVChem_begDT,'dd.mm.yyyy') as \"MorbusHIVChem_begDT\",
				to_char(MM.MorbusHIVChem_endDT,'dd.mm.yyyy') as \"MorbusHIVChem_endDT\",
				MM.MorbusHIVChem_Dose as \"MorbusHIVChem_Dose\",
				MM.Drug_id as \"Drug_id\",
				RTRIM(COALESCE(DrugPrep.DrugPrep_Name, '')) as \"Drug_Name\",
				MM.MorbusHIVChem_id as \"MorbusHIVChem_id\",
				MM.EvnNotifyBase_id as \"EvnNotifyBase_id\",
				MM.MorbusHIV_id as \"MorbusHIV_id\",
				M.Morbus_id as \"Morbus_id\",
				:Evn_id as \"MorbusHIV_pid\"
			from
				v_MorbusHIVChem MM  
				inner join v_MorbusHIV MH  on MH.MorbusHIV_id = MM.MorbusHIV_id and MM.EvnNotifyBase_id is null
				inner join v_Morbus M  on M.Morbus_id = MH.Morbus_id
				inner join v_MorbusBase MB  on M.MorbusBase_id = MB.MorbusBase_id
				left join rls.v_Drug Drug   on Drug.Drug_id = MM.Drug_id
				left join rls.v_DrugPrep DrugPrep  on Drug.DrugPrepFas_id = DrugPrep.DrugPrepFas_id
				left join v_Evn EvnEdit  on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
			where
				{$filter}
		";

		// echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику «Проведение химиопрофилактики ВИЧ-инфекции»
	 * @param $data
	 * @return array
	 */
	function saveMorbusHIVChem($data)
	{
		$procedure = '';

		if ((!isset($data['MorbusHIVChem_id'])) || ($data['MorbusHIVChem_id'] <= 0)) {
			$procedure = 'p_MorbusHIVChem_ins';
		} else {
			$procedure = 'p_MorbusHIVChem_upd';
		}

		$query = "
			select
                Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\",
                MorbusHIVChem_id as \"MorbusHIVChem_id\"
			from " . $procedure . " (
				 MorbusHIVChem_id  := :MorbusHIVChem_id,
				 MorbusHIV_id  := :MorbusHIV_id,
				 EvnNotifyBase_id  := :EvnNotifyBase_id,
				 MorbusHIVChem_begDT  := :MorbusHIVChem_begDT,
				 MorbusHIVChem_endDT  := :MorbusHIVChem_endDT,
				 Drug_id  := :Drug_id,
				 MorbusHIVChem_Dose  := :MorbusHIVChem_Dose,
				 pmUser_id  := :pmUser_id
				);
		";
		if (!empty($data['MorbusHIV_id'])) {
			$data['EvnNotifyBase_id'] = null;
		}
		if (!empty($data['EvnNotifyBase_id'])) {
			$data['MorbusHIV_id'] = null;
		}
		try {
			if (empty($data['EvnNotifyBase_id']) && empty($data['MorbusHIV_id'])) {
				throw new Exception('Не указано извещение или заболевание');
			}
			if (empty($data['Drug_id'])) {
				throw new Exception('Не указан препарат');
			}
			if (empty($data['MorbusHIVChem_Dose'])) {
				throw new Exception('Не указанa доза');
			}
			if (empty($data['MorbusHIVChem_begDT'])) {
				throw new Exception('Не указанa дата начала');
			}
			//echo getDebugSQL($query, $data);die();
			$result = $this->db->query($query, $data);
			if (!is_object($result)) {
				throw new Exception('Ошибка БД');
			}
			return $result->result('array');
		} catch (Exception $e) {
			return array(array('Error_Msg' => 'Сохранение специфики «Проведение химиопрофилактики ВИЧ-инфекции». <br />' . $e->getMessage()));
		}
	}

	/**
	 * Метод получения данных по HIV
	 * При вызове из формы просмотра записи регистра параметр MorbusHIV_pid будет содержать Person_id, также будет передан PersonRegister_id
	 * При вызове из формы просмотра движения/посещения параметр MorbusHIV_pid будет содержать Evn_id просматриваемого движения/посещения
	 */
	function getMorbusHIVViewData($data)
	{
		if (empty($data['session'])) {
			$data['session'] = null;
		}
		if (empty($data['MorbusHIV_pid'])) {
			$data['MorbusHIV_pid'] = null;
		}
		if (empty($data['PersonRegister_id'])) {
			$data['PersonRegister_id'] = null;
		}
		$this->load->library('swMorbus');
		$params = swMorbus::onBeforeViewData($this->getMorbusTypeSysNick(), $data['session'], $data['MorbusHIV_pid'], $data['PersonRegister_id']);
		if ($params['Error_Msg']) {
			throw new Exception($params['Error_Msg']);
		}
		$HIVContingentType = array(
			100 => 'Граждане РФ',
			200 => 'Иностранные граждане'
		);
		$params['MorbusHIV_pid'] = $data['MorbusHIV_pid'];
		$query = "
			select
				" . swMorbus::getAccessTypeQueryPart('M', 'MB', 'MorbusHIV_pid') . " as \"MorbusHIV_pid\",
				gr.HIVContingentType_id as \"HIVContingentTypeP_id\",
				grt.HIVContingentType_Name as \"HIVContingentTypeP_id_Name\",
				con.HIVContingentType_id as \"HIVContingentType_id\", --to HIVContingentType_id_list
				cont.HIVContingentType_Name as \"HIVContingentType_Name\", --to HIVContingentType_Name_list
				MV.HIVPregPathTransType_id as \"HIVPregPathTransType_id\",	
				ptt.HIVPregPathTransType_Name as \"HIVPregPathTransType_id_Name\",
				to_char(MV.MorbusHIV_DiagDT,'dd.mm.yyyy') as \"MorbusHIV_DiagDT\",
				MV.HIVInfectType_id as \"HIVInfectType_id\",
				it.HIVInfectType_Name as \"HIVInfectType_id_Name\",
				MV.HIVPregInfectStudyType_id as \"HIVPregInfectStudyType_id\",
				study.HIVPregInfectStudyType_Name as \"HIVPregInfectStudyType_id_Name\",
				MV.MorbusHIV_CountCD4 as \"MorbusHIV_CountCD4\",			
				MV.MorbusHIV_PartCD4 as \"MorbusHIV_PartCD4\",			
				to_char(MV.MorbusHIVOut_endDT,'dd.mm.yyyy') as \"MorbusHIVOut_endDT\",
				MV.HIVDispOutCauseType_id as \"HIVDispOutCauseType_id\",
				doct.HIVDispOutCauseType_Name as \"HIVDispOutCauseType_id_Name\",
				MV.Diag_cid as \"DiagD_id\",
				DiagD.Diag_FullName as \"DiagD_id_Name\",
				MV.MorbusHIV_NumImmun as \"MorbusHIV_NumImmun\",
				to_char(MV.MorbusHIV_confirmDate, 'dd.mm.yyyy') as \"MorbusHIV_confirmDate\",
				rtrim(MV.MorbusHIV_EpidemCode) as \"MorbusHIV_EpidemCode\",
				
				lab.MorbusHIVLab_id as \"MorbusHIVLab_id\",
				to_char(lab.MorbusHIVLab_BlotDT,'dd.mm.yyyy') as \"MorbusHIVLab_BlotDT\",
				lab.MorbusHIVLab_TestSystem as \"MorbusHIVLab_TestSystem\",
				lab.MorbusHIVLab_BlotNum as \"MorbusHIVLab_BlotNum\",
				lab.MorbusHIVLab_BlotResult as \"MorbusHIVLab_BlotResult\",
				to_char(lab.MorbusHIVLab_PCRDT,'dd.mm.yyyy') as \"MorbusHIVLab_PCRDT\",
				lab.MorbusHIVLab_PCRResult as \"MorbusHIVLab_PCRResult\",
				to_char(lab.MorbusHIVLab_IFADT,'dd.mm.yyyy') as \"MorbusHIVLab_IFADT\",
				lab.MorbusHIVLab_IFAResult as \"MorbusHIVLab_IFAResult\",
				lab.Lpu_id as \"Lpuifa_id\",
				Lpuifa.Lpu_Nick as \"Lpuifa_id_Name\",
				iLAR.LabAssessmentResult_id as \"LabAssessmentResult_iid\",
				iLAR.LabAssessmentResult_Name as \"LabAssessmentResult_iName\",
				cLAR.LabAssessmentResult_id as \"LabAssessmentResult_cid\",
				cLAR.LabAssessmentResult_Name as \"LabAssessmentResult_cName\",

				MV.MorbusHIV_id as \"MorbusHIV_id\",
				MV.Morbus_id as \"Morbus_id\",
				MB.MorbusBase_id as \"MorbusBase_id\",
				:MorbusHIV_pid as \"MorbusHIV_pid\",
				M.Diag_id as \"Diag_id\",
				MB.Person_id as \"Person_id\"
			from
				v_Morbus M  
				inner join v_MorbusBase MB on MB.MorbusBase_id = M.MorbusBase_id
				inner join v_MorbusHIV MV on MV.Morbus_id = M.Morbus_id
				--inner join v_Diag Diag on M.Diag_id = Diag.Diag_id
				left join v_MorbusHIVLab lab on MV.MorbusHIV_id = lab.MorbusHIV_id and lab.EvnNotifyBase_id is null
				left join v_MorbusHIVContingent gr on MV.MorbusHIV_id = gr.MorbusHIV_id and gr.EvnNotifyBase_id is null and gr.HIVContingentType_id in (100,200)
				left join v_HIVContingentType grt on grt.HIVContingentType_id = gr.HIVContingentType_id
				left join v_MorbusHIVContingent con on MV.MorbusHIV_id = con.MorbusHIV_id and con.EvnNotifyBase_id is null and con.HIVContingentType_id != gr.HIVContingentType_id
				left join v_HIVContingentType cont on cont.HIVContingentType_id = con.HIVContingentType_id
				left join v_HIVPregPathTransType ptt on ptt.HIVPregPathTransType_id = MV.HIVPregPathTransType_id
				left join v_HIVPregInfectStudyType study on study.HIVPregInfectStudyType_id = MV.HIVPregInfectStudyType_id
				left join v_HIVDispOutCauseType doct on doct.HIVDispOutCauseType_id = MV.HIVDispOutCauseType_id
				left join v_HIVInfectType it on it.HIVInfectType_id = MV.HIVInfectType_id
				left join v_Diag DiagD on DiagD.Diag_id = MV.Diag_cid
				left join v_Lpu Lpuifa on Lpuifa.Lpu_id = lab.Lpu_id
				left join v_LabAssessmentResult iLAR on iLAR.LabAssessmentResult_id = lab.LabAssessmentResult_iid
				left join v_LabAssessmentResult cLAR on cLAR.LabAssessmentResult_id = lab.LabAssessmentResult_cid
			where
				M.Morbus_id = :Morbus_id
		";
		//echo getDebugSql($query, $params);die();
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			$tmp = $result->result('array');
			if (empty($tmp)) {
				return array();
			}
			$id_list = array();
			$name_list = array();
			foreach ($tmp as $row) {
				$id_list[] = $row['HIVContingentType_id']/* - $row['HIVContingentTypeP_id']*/;
				$name_list[] = $row['HIVContingentType_Name'];
			}
			unset($tmp[0]['HIVContingentType_id']);
			unset($tmp[0]['HIVContingentType_Name']);
			$tmp[0]['HIVContingentTypeP_id_Name'] = empty($tmp[0]['HIVContingentTypeP_id']) ? '' : $HIVContingentType[$tmp[0]['HIVContingentTypeP_id']];
			$tmp[0]['HIVContingentType_id_list'] = implode(',', $id_list);
			$tmp[0]['HIVContingentType_Name_list'] = implode(', ', $name_list);//Нужно на тот случай, когда редактирование запрещено и группа чекбоксов не будет отрисована
			return array($tmp[0]);
		} else {
			return false;
		}
	}


	/**
	 * Сохранение специфики
	 * @param $data
	 * @return array
	 * @comment Будут сохранены те данные, которые переданы, т.е. можно отдельные параметры сохранять
	 */
	private function updateMorbusSpecific($data)
	{
		$err_arr = array();
		$entity_saved_arr = array();
		$not_edit_fields = array('Evn_pid', 'Person_id', 'MorbusHIV_id', 'Morbus_id', 'MorbusBase_id', 'MorbusType_id', 'Morbus_setDT', 'Morbus_disDT', 'MorbusBase_setDT', 'MorbusBase_disDT');
		if (isset($data['field_notedit_list']) && is_array($data['field_notedit_list'])) {
			$not_edit_fields = array_merge($not_edit_fields, $data['field_notedit_list']);
		}

		foreach ($this->entityFields as $entity => $l_arr) {

			$allow_save = false;
			foreach ($data as $key => $value) {
				if (in_array($key, $l_arr) && !in_array($key, $not_edit_fields)) {
					$allow_save = true;
					break;
				}
			}
			if ($allow_save && !empty($data[$entity . '_id'])) {
				$q = 'select ' . implode(', ', $l_arr) . ' from dbo.v_' . $entity . ' where ' . $entity . '_id = :' . $entity . '_id limit 1';
				$p = array($entity . '_id' => $data[$entity . '_id']);
				$r = $this->db->query($q, $data);
				//echo getDebugSQL($q, $data);
				if (is_object($r)) {
					$result = $r->result('array');
					if (empty($result) || !is_array($result[0]) || count($result[0]) == 0) {
						$err_arr[] = 'Получение данных ' . $entity . ' По идентификатору ' . $data[$entity . '_id'] . ' данные не получены';
						continue;
					}
					foreach ($result[0] as $key => $value) {
						if (is_object($value) && $value instanceof DateTime) {
							$value = $value->format('Y-m-d H:i:s');
						}
						//в $data[$key] может быть null
						$p[$key] = array_key_exists($key, $data) ? $data[$key] : $value;
						// ситуация, когда пользователь удалил какое-то значение
						$p[$key] = (empty($p[$key]) || $p[$key] == '0') ? null : $p[$key];
					}
				} else {
					$err_arr[] = 'Получение данных ' . $entity . ' Ошибка при выполнении запроса к базе данных';
					continue;
				}
				$field_str = '';
				foreach ($l_arr as $key) {
					$field_str .= '
						' . $key . ' := :' . $key . ',';
				}
				$q = '
					select
                        Error_Code as "Error_Code",
				        Error_Message as "Error_Msg",
                        ' . $entity . '_id as "' . $entity . '_id"
					from dbo.p_' . $entity . '_upd (
						' . $entity . '_id := ' . $entity . '_id, ' . $field_str . '
						pmUser_id := :pmUser_id
						);
				';
				$p['pmUser_id'] = $data['pmUser_id'];
				//echo getDebugSQL($q, $p);exit;
				$r = $this->db->query($q, $p);
				if (is_object($r)) {
					$result = $r->result('array');
					if (!empty($result[0]['Error_Msg'])) {
						$err_arr[] = 'Сохранение данных ' . $entity . ' ' . $result[0]['Error_Msg'];
						continue;
					}
					$entity_saved_arr[$entity . '_id'] = $data[$entity . '_id'];
				} else {
					$err_arr[] = 'Сохранение данных ' . $entity . ' Ошибка при выполнении запроса к базе данных';
					continue;
				}
			} else {
				continue;
			}
		}
		if (!empty($data['Evn_pid']) && !empty($data['Morbus_id'])) {
			$this->load->library('swMorbus');
			$tmp = swMorbus::updateMorbusIntoEvn(array(
				'Evn_id' => $data['Evn_pid'],
				'Morbus_id' => $data['Morbus_id'],
				'session' => $data['session'],
				'mode' => 'onAfterSaveMorbusSpecific',
			));
			if (isset($tmp['Error_Msg'])) {
				//нужно откатить транзакцию
				throw new Exception($tmp['Error_Msg']);
			}
		}
		$entity_saved_arr['Morbus_id'] = $data['Morbus_id'];
		$entity_saved_arr['Error_Msg'] = (count($err_arr) > 0) ? implode('<br />', $err_arr) : null;
		return array($entity_saved_arr);
	}

	/**
	 * Сохраняет специфику MorbusHIVContingent при редактировании данных заболевания
	 * @param array $data
	 * @return array
	 * @comment Сохраняется с привязкой к заболеванию MorbusHIV_id
	 */
	public function saveMorbusHIVContingentListWithMorbusHIV_id($data)
	{
		$data['EvnNotifyBase_id'] = null;
		try {
			if (empty($data['MorbusHIV_id'])) {
				throw new Exception('Не указано заболевание');
			}
			$id_list = $this->processingDataMorbusHIVContingent($data);
			$response = array(array('MorbusHIVContingent_id_list' => array()));
			if (!empty($id_list)) {
				//проверяем, есть ли уже данные, если уже есть, то удаляем
				$tmp = $this->clearMorbusHIVContingent($data);
				if (isset($tmp[0]['Error_Msg'])) {
					throw new Exception($tmp[0]['Error_Msg']);
				}
				foreach ($id_list as $id) {
					if ($id < 0) continue;
					$data['HIVContingentType_id'] = $id;
					$tmp = $this->saveMorbusHIVContingent($data);
					if (isset($tmp[0]['Error_Msg'])) {
						throw new Exception($tmp[0]['Error_Msg']);
					}
					$response[0]['MorbusHIVContingent_id_list'][] = $tmp[0]['MorbusHIVContingent_id'];
				}
			}
			return $response;
		} catch (Exception $e) {
			return array(array('Error_Msg' => 'Сохранение специфики «Код контингента». <br />' . $e->getMessage()));
		}
	}

	/**
	 * Сохраняет специфику MorbusHIVContingent после сохранения оперативного донесения
	 * @param array $data
	 * @return array
	 * @comment Сохраняется с привязкой к извещению EvnNotifyBase_id
	 */
	public function saveMorbusHIVContingentListWithEvnNotifyBase_id($data)
	{
		try {
			if (empty($data['MorbusHIV_id'])) {
				throw new Exception('Не указано заболевание');
			}
			if (empty($data['EvnNotifyBase_id'])) {
				throw new Exception('Не указано извещение');
			}
			$id_list = $this->processingDataMorbusHIVContingent($data);
			$response = array(array('MorbusHIVContingent_id_list' => array(), 'MorbusHIVContingent_id_copy_list' => array()));
			if (!empty($id_list)) {
				//проверяем, есть ли данные на заболевании, если есть, то очищаем
				$tmp = $this->clearMorbusHIVContingent($data);
				if (isset($tmp[0]['Error_Msg'])) {
					throw new Exception($tmp[0]['Error_Msg']);
				}
				foreach ($id_list as $id) {
					if ($id < 0) continue;
					//Сохраняем MorbusHIVContingent на извещении без привязки к заболеванию
					$data['HIVContingentType_id'] = $id;
					$tmpdata = $data;
					$tmpdata['MorbusHIV_id'] = null;
					$tmp = $this->saveMorbusHIVContingent($tmpdata);
					if (isset($tmp[0]['Error_Msg'])) {
						throw new Exception($tmp[0]['Error_Msg']);
					}
					$response[0]['MorbusHIVContingent_id_list'][] = $tmp[0]['MorbusHIVContingent_id'];

					//Копируем данные MorbusHIVContingent на заболевание без привязки к извещению
					$tmpdata = $data;
					$tmpdata['EvnNotifyBase_id'] = null;
					$tmp = $this->saveMorbusHIVContingent($tmpdata);
					if (isset($tmp[0]['Error_Msg'])) {
						throw new Exception($tmp[0]['Error_Msg']);
					}
					$response[0]['MorbusHIVContingent_id_copy_list'][] = $tmp[0]['MorbusHIVContingent_id'];
				}
			}
			return $response;
		} catch (Exception $e) {
			return array(array('Error_Msg' => 'Сохранение специфики «Код контингента». <br />' . $e->getMessage()));
		}
	}

	/**
	 * Обрабатывает данные перед сохранением специфики MorbusHIVContingent
	 * @param array $data $data['HIVContingentType_pid'] $data['HIVContingentType_id_list']
	 * @return array
	 * @throws Exception
	 */
	private function processingDataMorbusHIVContingent($data)
	{
		if (empty($data['HIVContingentType_pid'])) {
			//без указания гражданства нельзя будет ничего сохранить
			return array();
		}
		//Гражданство
		$data['HIVContingentType_pid'] = (int)$data['HIVContingentType_pid'];
		if (!in_array($data['HIVContingentType_pid'], array(100, 200))) {
			throw new Exception('Неправильно указано гражданство!');
		}
		$id_list = array($data['HIVContingentType_pid']);
		//Код контингента
		if (!empty($data['HIVContingentType_id_list'])) {
			$data['HIVContingentType_id_list'] = (string)$data['HIVContingentType_id_list'];
			$tmpdata = explode(',', $data['HIVContingentType_id_list']);
			foreach ($tmpdata as $id) {
				$id = (int)trim($id);
				$id_list[] = $id;
			}
		}
		return $id_list;
	}

	/**
	 * Очищает специфику MorbusHIVContingent при редактировании специфики заболевания
	 * @param array $data
	 * @param bool $only_check
	 * @return array
	 * @throws Exception
	 */
	private function clearMorbusHIVContingent($data, $only_check = false)
	{
		$querySel = "
			select
				MorbusHIVContingent_id \"MorbusHIVContingent_id\"
			from
				v_MorbusHIVContingent
			where
				MorbusHIV_id = :MorbusHIV_id
		";
		$queryDel = "
			select
                Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\",
                MorbusHIVContingent_id as \"MorbusHIVContingent_id\"
			from p_MorbusHIVContingent_del (
				MorbusHIVContingent_id := :MorbusHIVContingent_id
				);
		";
		try {
			if (empty($data['MorbusHIV_id'])) {
				throw new Exception('Не указано заболевание');
			}
			$result = $this->db->query($querySel, $data);
			if (!is_object($result)) {
				throw new Exception('Ошибка БД при предварительной проверке');
			}
			$tmp = $result->result('array');
			if ($only_check && count($tmp) > 0) {
				return array(array('Alert_Msg' => 'Найдены данные специфики «Код контингента» в регистре заболевания'));
			}
			foreach ($tmp as $row) {
				$result = $this->db->query($queryDel, $row);
				if (!is_object($result)) {
					throw new Exception('Ошибка БД при предварительной очистке');
				}
				$res_del = $result->result('array');
				if (isset($res_del[0]['Error_Msg'])) {
					throw new Exception($res_del[0]['Error_Msg']);
				}
			}
			return array(array());
		} catch (Exception $e) {
			return array(array('Error_Msg' => 'Сохранение специфики «Код контингента». <br />' . $e->getMessage()));
		}
	}

	/**
	 * Сохраняет специфику MorbusHIVContingent
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	private function saveMorbusHIVContingent($data)
	{
		$proc = 'p_MorbusHIVContingent_ins';
		$data['MorbusHIVContingent_id'] = null;
		$query = "
			select
                Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\",
                MorbusHIVContingent_id as \"MorbusHIVContingent_id\"
			from ".$proc." (
				MorbusHIVContingent_id := :MorbusHIVContingent_id,
				MorbusHIV_id := :MorbusHIV_id,
				EvnNotifyBase_id := :EvnNotifyBase_id,
				HIVContingentType_id := :HIVContingentType_id,
				pmUser_id := :pmUser_id
				);
		";
		$params = array(
			'MorbusHIVContingent_id' => $data['MorbusHIVContingent_id'],
			'MorbusHIV_id' => $data['MorbusHIV_id'],
			'EvnNotifyBase_id' => $data['EvnNotifyBase_id'],
			'HIVContingentType_id' => $data['HIVContingentType_id'],
			'pmUser_id' => $data['pmUser_id'],
		);
		//echo getDebugSQL($query, $params);
		$result = $this->db->query($query, $params);

		if (!is_object($result)) {
			throw new Exception('Ошибка БД');
		}

		return $result->result('array');
	}

	/**
	 * Сохраняет специфику MorbusHIVLab извещения, а также копирует данные на заболевание
	 * @param array $data
	 * @return array
	 * @comment Сохраняется с привязкой к EvnNotifyBase_id
	 */
	public function saveMorbusHIVLabWithEvnNotifyBase_id($data, $update_morbus_data = false)
	{
		try {
			if (empty($data['EvnNotifyBase_id'])) {
				throw new Exception('Не указано извещение');
			}
			// Сохраняем MorbusHIVLab на извещении без привязки к заболеванию
			$tmpdata = $data;
			$tmpdata['MorbusHIV_id'] = null;
			$tmpdata['MorbusHIVLab_id'] = null;
			$res = $this->saveMorbusHIVLab($tmpdata);
			if (isset($res[0]['Error_Msg'])) {
				throw new Exception($res[0]['Error_Msg']);
			}

			if (isset($data['MorbusHIV_id']) && $data['MorbusHIV_id'] > 0) {
				// проверка на случай, если на заболевании уже есть эта данные, чтобы не создавать дубля
				$query = "
					select 
						MorbusHIVLab_id as \"MorbusHIVLab_id\"
					from
						v_MorbusHIVLab
					where
						MorbusHIV_id = :MorbusHIV_id
					limit 1
				";
				$result = $this->db->query($query, $data);
				if (!is_object($result)) {
					throw new Exception('Ошибка БД при проверке перед копированием данных извещения');
				}
				$tmp = $result->result('array');
				if (count($tmp) > 0) {
					// на заболевании уже есть эта данные, ничего не копируем
					if ($update_morbus_data === false)
						return $res;
					// нужно обновить данные
					$data['MorbusHIVLab_id'] = $tmp[0]['MorbusHIVLab_id'];
				}
			} else {
				//throw new Exception('Не указано заболевание');
				return $res;
			}

			//Сохраняем данные MorbusHIVLab на заболевании без привязки к извещению
			$data['EvnNotifyBase_id'] = null;
			$tmp = $this->saveMorbusHIVLab($data);
			if (isset($tmp[0]['Error_Msg'])) {
				throw new Exception($tmp[0]['Error_Msg']);
			}
			$res[0]['MorbusHIVLab_id_copy'] = $tmp[0]['MorbusHIVLab_id'];
			return $res;
		} catch (Exception $e) {
			return array(array('Error_Msg' => 'Сохранение специфики «Лабораторная диагностика ВИЧ-инфекции». <br />' . $e->getMessage()));
		}
	}

	/**
	 * Сохраняет специфику MorbusHIVLab при редактировании специфики заболевания
	 * @param array $data
	 * @return array
	 * @comment Сохраняется с привязкой к заболеванию MorbusHIV_id
	 */
	public function saveMorbusHIVLabWithMorbusHIV_id($data)
	{
		$data['EvnNotifyBase_id'] = null;
		try {
			if (empty($data['MorbusHIV_id'])) {
				throw new Exception('Не указано заболевание');
			}
			return $this->saveMorbusHIVLab($data);
		} catch (Exception $e) {
			return array(array('Error_Msg' => 'Сохранение специфики «Лабораторная диагностика ВИЧ-инфекции». <br />' . $e->getMessage()));
		}
	}

	/**
	 * Сохраняет специфику MorbusHIVLab
	 * @param array $data
	 * @return array
	 * @comment Сохраняется с привязкой или к извещению или к заболеванию
	 * @throws Exception
	 */
	private function saveMorbusHIVLab($data)
	{
		if (empty($data['MorbusHIVLab_id'])) {
			$proc = 'p_MorbusHIVLab_ins';
			$data['MorbusHIVLab_id'] = null;
		} else {
			$proc = 'p_MorbusHIVLab_upd';
		}
		if (!empty($data['MorbusHIV_id'])) {
			$data['EvnNotifyBase_id'] = null;
		}
		if (!empty($data['EvnNotifyBase_id'])) {
			$data['MorbusHIV_id'] = null;
		}
		if (empty($data['LabAssessmentResult_iid'])) {
			$data['LabAssessmentResult_iid'] = null;
		}
		if (empty($data['LabAssessmentResult_cid'])) {
			$data['LabAssessmentResult_cid'] = null;
		}
		if (empty($data['EvnNotifyBase_id']) && empty($data['MorbusHIV_id'])) {
			throw new Exception('Не указано извещение или заболевание');
		}
		$query = "
            select
                Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\",
                MorbusHIVLab_id as \"MorbusHIVLab_id\"
			from '.$proc.' (
				MorbusHIVLab_id := :MorbusHIVLab_id,
				MorbusHIV_id := :MorbusHIV_id,
				EvnNotifyBase_id := :EvnNotifyBase_id,
				Lpu_id := :Lpuifa_id,
				MorbusHIVLab_IFADT := :MorbusHIVLab_IFADT,
				MorbusHIVLab_IFAResult := :MorbusHIVLab_IFAResult,
				
				MorbusHIVLab_BlotDT := :MorbusHIVLab_BlotDT,
				MorbusHIVLab_TestSystem := :MorbusHIVLab_TestSystem,
				MorbusHIVLab_BlotNum := :MorbusHIVLab_BlotNum,
				MorbusHIVLab_BlotResult := :MorbusHIVLab_BlotResult,
				LabAssessmentResult_iid := :LabAssessmentResult_iid,

				MorbusHIVLab_PCRDT := :MorbusHIVLab_PCRDT,
				MorbusHIVLab_PCRResult := :MorbusHIVLab_PCRResult,
				LabAssessmentResult_cid := :LabAssessmentResult_cid,

				pmUser_id := :pmUser_id
				);
		";
		//echo getDebugSQL($query, $data);die();
		$result = $this->db->query($query, $data);

		if (!is_object($result)) {
			throw new Exception('Ошибка БД');
		}
		return $result->result('array');
	}


	/**
	 * Определяет гражданство человека
	 * @param array $data
	 * @return array
	 */
	public function definePatriality($data)
	{
		$query = "
			select
			PD.Document_id as \"Document_id\"
			from v_PersonDocument PD 
			inner join v_DocumentType DT  on DT.DocumentType_Code = 9 and PD.DocumentType_id = DT.DocumentType_id
			where PD.Person_id = :Person_id
			limit 1
		";
		//echo getDebugSQL($query, $data);die();
		try {
			if (empty($data['Person_id'])) {
				throw new Exception('Не указан человек');
			}
			$result = $this->db->query($query, $data);
			if (!is_object($result)) {
				throw new Exception('Ошибка БД');
			}
			$response = array(array('HIVContingentType_id' => 100, 'Error_Msg' => null));
			$tmp = $result->result('array');
			if (count($tmp) > 0) {
				$response = array(array('HIVContingentType_id' => 200, 'Error_Msg' => null));
			}
			return $response;
		} catch (Exception $e) {
			return array(array('Error_Msg' => 'Определение гражданства человека. <br />' . $e->getMessage()));
		}
	}

	/**
	 * Определяет ЛПУ рождения ребенка
	 * @param array $data
	 * @return array
	 */
	public function defineBirthSvidLpu($data)
	{
		$query = "
			select BirthSvid.Lpu_id as \"Lpu_id\" from v_BirthSvid BirthSvid where BirthSvid.Person_id = :Person_id limit 1
		";
		//echo getDebugSQL($query, $data);die();
		try {
			if (empty($data['Person_id'])) {
				throw new Exception('Не указан ребенок');
			}
			$result = $this->db->query($query, $data);
			if (!is_object($result)) {
				throw new Exception('Ошибка БД');
			}
			return $result->result('array');
		} catch (Exception $e) {
			return array(array('Error_Msg' => 'Определение ЛПУ рождения ребенка. <br />' . $e->getMessage()));
		}
	}

	/**
	 * Контроль на уникальность значения номера иммуноблота
	 * @param int $morbus_hiv
	 * @param int $num_immun
	 * @return bool
	 * @throws Exception
	 */
	private function checkMorbusHivNumImmun($morbus_hiv, $num_immun)
	{
		if (empty($morbus_hiv) || empty($num_immun)) {
			return true;
		}
		$num_immun = (int)$num_immun;

		$query = "
			select
				P.Person_FirName as \"Person_FirName\",
				P.Person_SecName as \"Person_SecName\",
				P.Person_SurName as \"Person_SurName\",
				to_char(P.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\"
			from v_MorbusHIV MH
			inner join v_Morbus M on MH.Morbus_id = M.Morbus_id
			inner join v_PersonState P on M.Person_id = P.Person_id
			where MH.MorbusHIV_NumImmun = :MorbusHIV_NumImmun and MH.MorbusHIV_id != :MorbusHIV_id
			limit 1
		";
		$params = array(
			'MorbusHIV_NumImmun' => $num_immun,
			'MorbusHIV_id' => $morbus_hiv,
		);
		//echo getDebugSQL($query, $params);die();
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			throw new Exception('Ошибка БД');
		}
		$result = $result->result('array');
		if (count($result) > 0) {
			throw new Exception('№ иммуноблота "' . $num_immun
				. '" заведен у пациента '
				. $result[0]['Person_SurName'] . ' '
				. $result[0]['Person_FirName'] . ' '
				. $result[0]['Person_SecName'] . ', '
				. $result[0]['Person_BirthDay'] . '.'
			);
		}
		return true;
	}

	/**
	 * @param array $data
	 * @throws Exception
	 */
	private function checkDiag($data)
	{
		if (!empty($data['HIVContingentTypeP_id']) && $data['HIVContingentTypeP_id'] == 100 && $this->regionNick != 'kz') {
			$diag_code = $this->getFirstResultFromQuery("
				select  Diag_Code as \"Diag_Code\"
				from v_MorbusHIV MV 
				inner join v_Diag D  on D.Diag_id = MV.Diag_id
				where MV.MorbusHIV_id = :MorbusHIV_id
				limit 1
			", $data);
			if (empty($diag_code)) {
				throw new Exception('Ошибка при проверке диагноза');
			}
			if ($diag_code >= 'B20' && $diag_code <= 'B24' && (empty($data['MorbusHIV_confirmDate']) || empty($data['MorbusHIV_EpidemCode']))) {
				throw new Exception('При заболевании с диагнозом из диапазона B20-B24 обязательно заполнение полей "Дата подтверждения диагноза", "Эпидемиологический код"');
			}
		}
	}

	/**
	 * В результате получаем массив типов контингента с кодами
	 * @param array $data
	 * @return array
	 */
	public function getHIVContingentType($data)
	{
		// коды контингента ведут отчет в зависимости от гражданства (РФ -  от 100, иностранцы - от 200)
		if (isset($data['Nationality']) && strlen(strval($data['Nationality'])))
			$nat = $data['Nationality'][0] . '%';
		else
			$nat = '1%';

		// если существует случай, в котором необходимо отметить выбранные значения
		$filterMHIV = '';
		if (!empty($data['MorbusHIV_id']) && $data['MorbusHIV_id'] != '') {
			$filterMHIV = " AND MHIV.MorbusHIV_id = " . $data['MorbusHIV_id'];
		}

		// пока в справочнике присутствуют лишь значения для Екатеринбурга, другие (с регионом null) не должны отображаться
		if ($this->regionNick == 'ekb')
			$filter = " AND HIVC.Region_id = dbo.getRegion()";
		else
			$filter = " AND HIVC.Region_id IS NULL";

		$query = "
			SELECT
				HIVC.HIVContingentType_id as \"HIVContingentType_id\",
				HIVC.HIVContingentType_Code as \"HIVContingentType_Code\",
				HIVC.HIVContingentType_Name as \"HIVContingentType_Name\",
			CASE
				WHEN
					MHIV.MorbusHIVContingent_id IS NOT NULL THEN 'true' ELSE 'false'
			END AS \"CHECKED\"
			FROM  v_HIVContingentType AS HIVC
			LEFT JOIN LATERAL
			(
				SELECT MHIV.MorbusHIVContingent_id
					FROM v_MorbusHIVContingent AS MHIV
					WHERE MHIV.HIVContingentType_id = HIVC.HIVContingentType_id
					" . $filterMHIV . "
					limit 1
			) AS MHIV on true
			WHERE
			-- where
			HIVC.HIVContingentType_Code LIKE :NAT -- по коду гражданства
			AND HIVC.HIVContingentType_Code NOT IN ('100', '200') -- не включая тип Граждане РФ и Иностранные граждане
			" . $filter . "
			-- end where
			";
		//echo getDebugSQL($query, array('NAT' => $nat)); die();
		try {
			$result = $this->db->query($query, array('NAT' => $nat));
			if (!is_object($result)) {
				throw new Exception('Ошибка БД');
			}
			$res = $result->result('array');
			return array(
				'data' => $res,
				'totalCount' => sizeof($res)
			);
		} catch (Exception $e) {
			return array(array('Error_Msg' => 'Получение списка типов контингента<br />' . $e->getMessage()));
		}
	}

	/**
	 * @return array
	 */
	function getFieldsMapForXLS()
	{
		return array(
			// Сведения о пациенте
			'Person_SurName' => 'Фамилия',
			'Person_FirName' => 'Имя',
			'Person_SecName' => 'Отчество',
			'Person_BirthDay' => 'Дата рождения',
			'Sex' => 'Пол',
			'SNILS' => 'СНИЛС',
			'DocumentTypeFRMIS_id' => 'Тип документа',
			'Document_Ser' => 'Серия',
			'Document_Num' => 'Номер',
			'Document_begDate' => 'Дата выдачи',
			'OrgDep_Nick' => 'Выдан',

			// Сведения о регистровой записи пациента
			'OID' => 'Медицинская организация',
			'PersonRegister_setDate' => 'Дата включения в регистр',
			'Person_BirthFam' => 'Фамилия при рождении',
			'Diag_Code' => 'Диагноз (B20-B24)',
			'MorbusHIV_confirmDate' => 'Дата подтверждения диагноза',
			'MorbusHIV_EpidemCode' => 'Эпидемиологический код',
			'HIVContingentTypeFRMIS_Code' => 'Код контингента',
			'PersonRegister_disDate' => 'Дата исключения из регистра',
			'OutCause' => 'Причина исключения',
			'Diag_cCode' => 'Причина смерти',

			// Сведения о лабораторных исследованиях пациента
			'LabIssl_Type' => 'Лабораторное исследование',
			'LabIssl_Date' => 'Дата исследования',
			'LabIssl_Bio' => 'Вид биоматериала',
			'LabIssl_BioDate' => 'Дата взятия биоматериала',
			'LabIssl_BioLpu' => 'МО, направившая биоматериал',
			'LabIssl_Name' => 'Наименование тест-системы',
			'LabIssl_Ser' => 'Серия тест-системы',
			'LabIssl_Result' => 'Результат',

			// Сведения о контактных данных пациента
			'AddressType' => 'Тип адреса',
			'Phone' => 'Номер телефона',
			'Rgn' => 'Субъект',
			'SubRgn' => 'Район',
			'TownSocr' => 'Префикс населенного пункта',
			'Town' => 'Населенный пункт',
			'StreetSocr' => 'Префикс улицы',
			'Street' => 'Улица',
			'House' => 'Дом',
			'Flat' => 'Квартира'
		);
	}

	/**
	 * @param array $data
	 * return array
	 */
	function getDataForXLS($data)
	{
		$filters = array();
		$params = array();

		$params['Range_begDate'] = $data['Range'][0];
		$params['Range_endDate'] = $data['Range'][1];

		switch ($data['ExportType_id']) {
			case 1:    //в регистре
				$filters[] = "PR.PersonRegister_setDate between :Range_begDate and :Range_endDate";
				$filters[] = "COALESCE(PR.PersonRegister_disDate, :Range_endDate) >= :Range_endDate";
				break;

			case 2:    //исключенные из регистра
				$filters[] = "PR.PersonRegister_disDate between :Range_begDate and :Range_endDate";
				break;

			case 3:    //все
				$filters[] = "(PR.PersonRegister_setDate between :Range_begDate and :Range_endDate or PR.PersonRegister_disDate between :Range_begDate and :Range_endDate)";
				break;
		}

		if (!empty($data['Lpu_oid'])) {
			$filters[] = "PR.Lpu_iid = :Lpu_oid";
			$params['Lpu_oid'] = $data['Lpu_oid'];
		}

		$filters_str = implode(" and ", $filters);

		$query = "
			select
				(
					substring(PS.Person_Snils,1,3)||'-'||
					substring(PS.Person_Snils,4,3)||'-'||
					substring(PS.Person_Snils,7,3)||' '||
					substring(PS.Person_Snils,10,2)
				) as \"SNILS\",
				rtrim(PS.Person_SurName) as \"Person_SurName\",
				rtrim(PS.Person_FirName) as \"Person_FirName\",
				rtrim(PS.Person_SecName) as \"Person_SecName\",
				to_char(PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
				case
					when Sex.Sex_fedid = 1 then '1-М'
					when Sex.Sex_fedid = 2 then '2-Ж'
				end as \"Sex\",
				DTF.DocumentTypeFRMIS_id as \"DocumentTypeFRMIS_id\",
				Doc.Document_Ser as \"Document_Ser\",
				Doc.Document_Num as \"Document_Num\",
				to_char(Doc.Document_begDate, 'dd.mm.yyyy') as \"Document_begDate\",
				OD.OrgDep_Nick as \"OrgDep_Nick\",
				to_char(PR.PersonRegister_setDate, 'dd.mm.yyyy') as \"PersonRegister_setDate\",
				to_char(PR.PersonRegister_disDate, 104) as \"PersonRegister_disDate\",
				to_char(MO.MorbusHIV_confirmDate, 104) as \"MorbusHIV_confirmDate\",
				MO.MorbusHIV_EpidemCode as \"MorbusHIV_EpidemCode\",
				HCTF.HIVContingentTypeFRMIS_Code as \"HIVContingentTypeFRMIS_Code\",
				case
					when OC.PersonRegisterOutCause_SysNick like 'OutFromRF' then '1-Выезд за пределы РФ'
					when OC.PersonRegisterOutCause_SysNick like 'Death' then '2-Смерть'
					when OC.PersonRegisterOutCause_SysNick like 'sdisp' then '3-Прекращение диспансерного наблюдения'
					when OC.PersonRegisterOutCause_id is not null then '4-Иное'
				end as \"OutCause\",
				DC.Diag_Code as \"Diag_cCode\",
				
				case when MHL.MorbusHIVLab_BlotDT is not null then 2 else 3 end as \"LabIssl_Type\", 
				case when MHL.MorbusHIVLab_BlotDT is not null then to_char(MHL.MorbusHIVLab_BlotDT, 'dd.mm.yyyy') else to_char(MHL.MorbusHIVLab_PCRDT, 'dd.mm.yyyy') end as \"LabIssl_Date\",
				1 as \"LabIssl_Bio\",
				'' as \"LabIssl_BioDate\",
				'' as \"LabIssl_BioLpu\",
				'' as \"LabIssl_Name\",
				case when MHL.MorbusHIVLab_BlotDT is not null then MHL.MorbusHIVLab_BlotNum else '' end as \"LabIssl_Ser\",
				case when MHL.MorbusHIVLab_BlotDT is not null then MHL.MorbusHIVLab_BlotResult else MHL.MorbusHIVLab_PCRResult end as \"LabIssl_Result\",
				
				OID.PassportToken_tid as \"OID\",
				D.Diag_Code as \"Diag_Code\",
				case 
					when PT.PrivilegeType_id is not null
					then 'да' else 'нет'
				end as \"IsInvalid\",
				null as \"HivDiagSop\",
				case
					when A.Address_id = PS.PAddress_id then '2-Адрес места пребывания'
					when A.Address_id = PS.UAddress_id then '2-Адрес места жительства'
				end as \"AddressType\",
				(
					cast(Rgn.KLRgn_id as varchar)||'-'||
					UPPER(LEFT(Rgn.KLRgn_Name,1))||LOWER(SUBSTRING(Rgn.KLRgn_Name,2,LEN(Rgn.KLRgn_Name)))||' '||
					UPPER(LEFT(RgnSocr.KLSocr_Name,1))||LOWER(SUBSTRING(RgnSocr.KLSocr_Name,2,LEN(RgnSocr.KLSocr_Name)))
				) as \"Rgn\",
				(
					UPPER(LEFT(SubRgn.KLSubRgn_Name,1))||LOWER(SUBSTRING(SubRgn.KLSubRgn_Name,2,LEN(SubRgn.KLSubRgn_Name)))
				) as \"SubRgn\",
				case
					when A.KLCity_id is not null then (
						LOWER(CitySocr.KLSocr_Nick)||' - '||
						UPPER(LEFT(CitySocr.KLSocr_Name,1))||LOWER(SUBSTRING(CitySocr.KLSocr_Name,2,LEN(CitySocr.KLSocr_Name)))
					)
					when A.KLTown_id is not null then (
						LOWER(TownSocr.KLSocr_Nick)||' - '||
						UPPER(LEFT(TownSocr.KLSocr_Name,1))||LOWER(SUBSTRING(TownSocr.KLSocr_Name,2,LEN(TownSocr.KLSocr_Name)))
					)
				end as \"TownSocr\",
				case
					when A.KLCity_id is not null then (
						UPPER(LEFT(City.KLCity_Name,1))||LOWER(SUBSTRING(City.KLCity_Name,2,LEN(City.KLCity_Name)))
					)
					when A.KLTown_id is not null then (
						UPPER(LEFT(Town.KLTown_Name,1))||LOWER(SUBSTRING(Town.KLTown_Name,2,LEN(Town.KLTown_Name)))
					)
				end as \"Town\",
				(
					LOWER(StreetSocr.KLSocr_Nick)||' - '||
					UPPER(LEFT(StreetSocr.KLSocr_Name,1))||LOWER(SUBSTRING(StreetSocr.KLSocr_Name,2,LEN(StreetSocr.KLSocr_Name)))
				) as \"StreetSocr\",
				(
					UPPER(LEFT(Street.KLStreet_Name,1))||LOWER(SUBSTRING(Street.KLStreet_Name,2,LEN(Street.KLStreet_Name)))
				) as \"Street\",
				A.Address_House as \"House\",
				A.Address_Corpus as \"Corpus\",
				A.Address_Flat as \"Flat\",
				A.Address_Zip as \"Zip\",
				'+7'||PS.Person_Phone as \"Phone\"
			from
				v_PersonRegister PR 
				inner join v_PersonState PS  on PS.Person_id = PR.Person_id
				inner join v_MorbusHiv MO  on MO.Morbus_id = PR.Morbus_id
				left join v_MorbusHIVLab MHL on MO.MorbusHIV_id = MHL.MorbusHIV_id
				left join v_EvnNotifyHiv ENT  on ENT.Morbus_id = PR.Morbus_id
				left join v_Sex Sex  on Sex.Sex_id = PS.Sex_id
				left join v_Job Job  on Job.Job_id = PS.Job_id
				left join v_Document Doc  on Doc.Document_id = PS.Document_id
				left join v_DocumentType DT  on DT.DocumentType_id = Doc.DocumentType_id
				left join v_DocumentTypeFRMIS DTF on DTF.DocumentTypeFRMIS_id = DT.DocumentTypeFRMIS_id
				left join lateral (
					select
						HCTF.HIVContingentTypeFRMIS_Code
					from
						v_MorbusHIVContingent MHC
						left join v_HIVContingentType HCT on HCT.HIVContingentType_id = MHC.HIVContingentType_id
						left join v_HIVContingentTypeFRMIS HCTF on HCTF.HIVContingentTypeFRMIS_id = HCT.HIVContingentTypeFRMIS_id
					where
						MHC.MorbusHIV_id = MO.MorbusHIV_id
					limit 1
				) HCTF on true
				left join v_OrgDep OD  on OD.OrgDep_id = Doc.OrgDep_id
				left join v_PersonRegisterOutCause OC  on OC.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
				left join v_Lpu L  on L.Lpu_id = PR.Lpu_iid
				left join fed.v_PassportToken OID  on OID.Lpu_id = L.Lpu_id
				left join v_Diag D  on D.Diag_id = MO.Diag_id
				left join v_Diag DC  on DC.Diag_id = MO.Diag_cid
				left join lateral (
					select PT.PrivilegeType_id
					from v_PersonPrivilege PP 
					inner join v_PrivilegeType PT  on PT.PrivilegeType_id = PP.PrivilegeType_id
					where PP.Person_id = PS.Person_id and PP.PersonPrivilege_endDate is null
					and PT.PrivilegeType_Code in ('81','82','83')
					order by PP.PersonPrivilege_begDate desc
					limit 1
				) PT on true
				left join v_Address A  on A.Address_id = COALESCE(PS.PAddress_id, PS.UAddress_id)/*PS.UAddress_id*/	-- todo: check
				left join v_KLRgn Rgn  on Rgn.KLRgn_id = A.KLRgn_id
				left join v_KLSocr RgnSocr  on RgnSocr.KLSocr_id = Rgn.KLSocr_id
				left join v_KLSubRgn SubRgn  on SubRgn.KLSubRgn_id = A.KLSubRgn_id
				left join v_KLSocr SubRgnSocr  on SubRgnSocr.KLSocr_id = SubRgn.KLSocr_id
				left join v_KLCity City  on City.KLCity_id = A.KLCity_id
				left join v_KLSocr CitySocr  on CitySocr.KLSocr_id = City.KLSocr_id
				left join v_KLTown Town  on Town.KLTown_id = A.KLTown_id
				left join v_KLSocr TownSocr  on TownSocr.KLSocr_id = Town.KLSocr_id
				left join v_KLStreet Street  on Street.KLStreet_id = A.KLStreet_id
				left join v_KLSocr StreetSocr  on StreetSocr.KLSocr_id = Street.KLSocr_id
			where
				{$filters_str}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение специфики по ВИЧ. Метод для API
	 */
	function loadMorbusHIV_API($data)
	{
		if (empty($data['MorbusHIV_id']) && empty($data['PersonRegister_id'])) {
			return false;
		}
		$where = '';
		if (!empty($data['MorbusHIV_id'])) {
			$where .= ' AND MH.MorbusHIV_id = :MorbusHIV_id';
		}
		if (!empty($data['PersonRegister_id'])) {
			$where .= ' AND PR.PersonRegister_id = :PersonRegister_id';
		}
		$query = "
			select distinct 
				MH.MorbusHIV_id as \"MorbusHIV_id\"
				,PR.Person_id as \"Person_id\"
				,PR.Morbus_id as \"Morbus_id\"
				,PR.Diag_id as \"Diag_id\"
				,gr.HIVContingentType_id as \"HIVContingentTypeP_id\"
				,(
					select 
						string_agg(cast(cont.HIVContingentType_id as varchar), ',') 
					from v_MorbusHIVContingent con
						left join v_HIVContingentType cont on cont.HIVContingentType_id = con.HIVContingentType_id
					where MH.MorbusHIV_id = con.MorbusHIV_id and con.EvnNotifyBase_id is null and con.HIVContingentType_id != gr.HIVContingentType_id
				) as \"HIVContingentType_Name_list\"
				,MH.Diag_cid as \"DiagD_id\"
				,MH.HIVDispOutCauseType_id as \"HIVDispOutCauseType_id\"
				,to_char(MH.MorbusHIVOut_endDT,'dd.mm.yyyy') as \"MorbusHIVOut_endDT\"
				,MH.HIVInfectType_id as \"HIVInfectType_id\"
				,MH.HIVPregInfectStudyType_id as \"HIVPregInfectStudyType_id\"
				,MH.HIVPregPathTransType_id as \"HIVPregPathTransType_id\"
				,to_char(MH.MorbusHIV_confirmDate, 'dd.mm.yyyy') as \"MorbusHIV_confirmDate\"
				,rtrim(MH.MorbusHIV_EpidemCode) as \"MorbusHIV_EpidemCode\"
				,MH.MorbusHIV_CountCD4 as \"MorbusHIV_CountCD4\"
				,MH.MorbusHIV_PartCD4 as \"MorbusHIV_PartCD4\"
				,to_char(lab.MorbusHIVLab_BlotDT,'dd.mm.yyyy') as \"MorbusHIVLab_BlotDT\"
				,MH.MorbusHIV_NumImmun as \"MorbusHIV_NumImmun\"
				,lab.MorbusHIVLab_BlotNum as \"MorbusHIVLab_BlotNum\"
				,lab.MorbusHIVLab_BlotResult as \"MorbusHIVLab_BlotResult\"
				,lab.MorbusHIVLab_TestSystem as \"MorbusHIVLab_TestSystem\"
				,lab.MorbusHIVLab_id as \"MorbusHIVLab_id\"
				,lab.LabAssessmentResult_iid as \"LabAssessmentResult_iid\"
				,lab.Lpu_id as \"Lpu_id\"
				,to_char(lab.MorbusHIVLab_IFADT,'dd.mm.yyyy') as \"MorbusHIVLab_IFADT\"
				,lab.MorbusHIVLab_IFAResult as \"MorbusHIVLab_IFAResult\"
				,to_char(lab.MorbusHIVLab_PCRDT,'dd.mm.yyyy') as \"MorbusHIVLab_PCRDT\"
				,lab.MorbusHIVLab_PCRResult as \"MorbusHIVLab_PCRResult\"
				,lab.LabAssessmentResult_cid as \"LabAssessmentResult_cid\"
			from
				v_MorbusHIV MH
				left join dbo.v_PersonRegister PR on MH.Morbus_id = PR.Morbus_id
				left join v_MorbusHIVContingent gr on MH.MorbusHIV_id = gr.MorbusHIV_id and gr.EvnNotifyBase_id is null and gr.HIVContingentType_id in (100,200)
				left join v_MorbusHIVLab lab on MH.MorbusHIV_id = lab.MorbusHIV_id and lab.EvnNotifyBase_id is null
			where 1=1
				{$where}
		";
		//echo getDebugSQL($query, $data); die();
		$res = $this->db->query($query, $data);
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Получение химиопрофилактики ВИЧ–инфекции. Метод для API
	 */
	function getMorbusHIVChemAPI($data)
	{
		if (empty($data['MorbusHIV_id']) && empty($data['MorbusHIVChem_id'])) {
			return false;
		}
		$where = '';
		if (!empty($data['MorbusHIV_id'])) {
			$where .= ' AND MH.MorbusHIV_id = :MorbusHIV_id';
		}
		if (!empty($data['MorbusHIVChem_id'])) {
			$where .= ' AND MM.MorbusHIVChem_id = :MorbusHIVChem_id';
		}
		$query = "
			select
				M.Person_id as \"Person_id\"
				,MM.MorbusHIVChem_id as \"MorbusHIVChem_id\"
				,MH.MorbusHIV_id as \"MorbusHIV_id\"
				,MM.Drug_id as \"Drug_id\"
				,to_char(MM.MorbusHIVChem_begDT,'dd.mm.yyyy') as \"MorbusHIVChem_begDT\"
				,MM.MorbusHIVChem_Dose as \"MorbusHIVChem_Dose\"
				,to_char(MM.MorbusHIVChem_endDT,'dd.mm.yyyy') as \"MorbusHIVChem_endDT\"
			from
				v_MorbusHIVChem MM
				inner join v_MorbusHIV MH on MH.MorbusHIV_id = MM.MorbusHIV_id and MM.EvnNotifyBase_id is null
				inner join v_Morbus M on M.Morbus_id = MH.Morbus_id
			where 1=1
				{$where}
		";
		//echo getDebugSQL($query, $data); die();
		$res = $this->db->query($query, $data);
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Получение вакцинации в рамках специфики ВИЧ
	 */
	function getMorbusHIVVacAPI($data)
	{
		if (empty($data['MorbusHIV_id']) && empty($data['MorbusHIVVac_id'])) {
			return false;
		}
		$where = '';
		if (!empty($data['MorbusHIV_id'])) {
			$where .= ' AND MM.MorbusHIV_id = :MorbusHIV_id';
		}
		if (!empty($data['MorbusHIVVac_id'])) {
			$where .= ' AND MM.MorbusHIVVac_id = :MorbusHIVVac_id';
		}
		$query = "
			select
				MM.MorbusHIVVac_id as \"MorbusHIVVac_id\"
				,MM.MorbusHIV_id as \"MorbusHIV_id\"
				,MM.Drug_id as \"Drug_id\"
				,to_char(MM.MorbusHIVVac_setDT,'dd.mm.yyyy') as \"MorbusHIVVac_setDT\"
				,MM.EvnNotifyBase_id as \"EvnNotifyBase_id\"
			from
				v_MorbusHIVVac MM		
			where 1=1
				{$where}
		";
		//echo getDebugSQL($query, $data); die();
		$res = $this->db->query($query, $data);
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Получение вторичных заболеваний и оппортунистических инфекций в рамках специфики ВИЧ
	 */
	function getMorbusHIVSecDiagAPI($data)
	{
		if (empty($data['MorbusHIV_id']) && empty($data['MorbusHIVSecDiag_id'])) {
			return false;
		}
		$where = '';
		if (!empty($data['MorbusHIV_id'])) {
			$where .= ' AND MM.MorbusHIV_id = :MorbusHIV_id';
		}
		if (!empty($data['MorbusHIVSecDiag_id'])) {
			$where .= ' AND MM.MorbusHIVSecDiag_id = :MorbusHIVSecDiag_id';
		}
		$query = "
			select
				MM.MorbusHIVSecDiag_id as \"MorbusHIVSecDiag_id\"
				,MM.MorbusHIV_id as \"MorbusHIV_id\"
				,MM.Diag_id as \"Diag_id\"
				,to_char(MM.MorbusHIVSecDiag_setDT,'dd.mm.yyyy') as \"MorbusHIVSecDiag_setDT\"
				,MM.EvnNotifyBase_id as \"EvnNotifyBase_id\"
				,M.Person_id as \"Person_id\"
			from
				v_MorbusHIVSecDiag MM
				inner join v_MorbusHIV MH on MH.MorbusHIV_id = MM.MorbusHIV_id and MM.EvnNotifyBase_id is null
				inner join v_Morbus M on M.Morbus_id = MH.Morbus_id
			where 1=1
				{$where}
		";
		//echo getDebugSQL($query, $data); die();
		$res = $this->db->query($query, $data);
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Изменение специфики по ВИЧ. Метод API
	 */
	function saveMorbusHivAPI($data)
	{
		$query = "SELECT 
						morbusbase_id as \"MorbusBase_id\", 
						evn_pid as \"EVN_pid\", 
						diag_id as \"Diag_id\", 
						morbuskind_id as \"MorbusKind_id\", 
						morbus_name as \"Morbus_Name\", 
						morbus_nick as \"Morbus_Nick\", 
						morbus_setdt as \"Morbus_setDT\", 
						morbus_disdt as \"Morbus_disDT\", 
						morbusresult_id as \"MorbusResult_id\", 
						morbushiv_id as \"MorbusHIV_id\", 
						morbus_id as \"Morbus_id\", 
						hivpregpathtranstype_id as \"HIVPregPathTransType_id\", 
						morbushiv_diagdt as \"MorbusHIV_DiagDT\", 
						hivpreginfectstudytype_id as \"HIVPregInfectStudyType_id\", 
						morbushiv_countcd4 as \"MorbusHIV_Countcd4\", 
						morbushiv_partcd4 as \"MorbusHIV_Partcd4\", 
						morbushivout_enddt as \"MorbusHIVout_endDT\", 
						hivdispoutcausetype_id as \"HIVDispOutCauseType_id\", 
						diag_cid as \"Diag_cid\", 
						pmuser_insid as \"pmUser_insid\", 
						pmuser_updid as \"pmUser_updid\", 
						morbushiv_insdt as \"MorbusHIV_insDT\", 
						morbushiv_upddt as \"MorbusHIV_updDT\", 
						morbushiv_numimmun as \"MorbusHIV_numimmun\", 
						hivinfecttype_id as \"HIVInfectType_id\", 
						morbushiv_confirmdate as \"MorbusHIV_ConfirmDate\", 
						morbushiv_epidemcode as \"MorbusHIV_EpidemCode\"
 					FROM v_MorbusHIV WHERE MorbusHIV_id = :MorbusHIV_id ORDER BY MorbusHIV_id DESC";
		$res = $this->getFirstRowFromQuery($query, $data);
		if (!empty($res['MorbusHIV_id'])) {
			foreach ($data as $key => $value) {
				if (empty($data[$key]) && !empty($res[$key])) {
					$data[$key] = $res[$key];
				}
			}
		} else {
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res['Error_Msg'])) ? $res['Error_Msg'] : 'Данные по цпецифике не найдены'
			));
		}
		if (!empty($data['MorbusHIVLab_id'])) {
			$query = "SELECT 
 							morbushivlab_id as \"MorbusHIVLab_id\", 
 							morbushiv_id as \"MorbusHIV_id\", 
 							evnnotifybase_id as \"EVNNotifyBase_id\", 
 							morbushivlab_ifadt as \"MorbusHIVLab_ifaDT\", 
 							lpu_id as \"Lpu_id\", 
 							morbushivlab_ifaresult as \"MorbusHIVLab_ifaresult\", 
 							morbushivlab_blotdt as \"MorbusHIVLab_blotDT\", 
 							morbushivlab_testsystem as \"MorbusHIVLab_testsystem\", 
 							morbushivlab_blotnum as \"MorbusHIVLab_blotnum\", 
 							morbushivlab_blotresult as \"MorbusHIVLab_blotresult\", 
 							morbushivlab_pcrdt as \"MorbusHIVLab_pcrDT\", 
 							morbushivlab_pcrresult as \"MorbusHIVLab_pcrresult\", 
 							pmuser_insid as \"pmUser_insid\", 
 							pmuser_updid as \"pmUser_updid\", 
 							morbushivlab_insdt as \"MorbusHIVLab_insDT\", 
 							morbushivlab_upddt as \"MorbusHIVLab_updDT\", 
 							labassessmentresult_iid as \"LabAssessmentResult_iid\", 
 							labassessmentresult_cid as \"LabAssessmentResult_cid\"
 							FROM v_MorbusHIVLab WHERE MorbusHIVLab_id = :MorbusHIVLab_id ORDER BY MorbusHIVLab_id DESC ";
			$res = $this->getFirstRowFromQuery($query, $data);
			if (!empty($res['MorbusHIVLab_id'])) {
				foreach ($data as $key => $value) {
					if (empty($data[$key]) && !empty($res[$key])) {
						$data[$key] = $res[$key];
					}
				}
			}
		}
		//$data['HIVContingentType_id_list']
		$result = $this->saveMorbusSpecific($data);

		return $result;
	}
}