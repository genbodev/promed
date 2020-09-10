<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusProf_model - модель для MorbusProf
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Prof
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version      12.2014
 *
 * Магические свойства
 * @property-read int $morbusTypeId
 * @property-read string $morbusTypeSysNick
 * @property-read string $groupRegistry
 * @property-read Morbus_model $Morbus
 * @property PersonWeight_model $PersonWeight_model
 * @property PersonHeight_model $PersonHeight_model
 * @property-read MorbusProfLab_model $morbusProfLab
 * @property-read MorbusProfDisp_model $morbusProfDisp
 */
class MorbusProf_model extends swModel
{
	/**
	 * @var bool Требуется ли параметр pmUser_id для хранимки удаления
	 */
	protected $_isNeedPromedUserIdForDel = true;
	/*
	* Список полей для метода updateMorbusSpecific
	*/
	private $entityFields = array(
		'MorbusProf' => array(
			'Morbus_id',
			'MorbusProfDiag_id',
			'MorbusProf_Year',
			'MorbusProf_Month',
			'MorbusProf_Day',
			'MorbusProf_IsFit'
		),
		'Person' => array(
			'Org_id',
			'OnkoOccupationClass_id'
		),
		'Morbus' => array(
			'MorbusBase_id',
			'Evn_pid', //Учетный документ, в рамках которого добавлено заболевание
			'Diag_id','MorbusKind_id','Morbus_Name','Morbus_Nick',
			'Morbus_setDT','Morbus_disDT','MorbusResult_id'
		),
		'MorbusBase' => array(
			'Person_id','Evn_pid','MorbusType_id','MorbusBase_setDT',
			'MorbusBase_disDT','MorbusResult_id'
		)
	);

	protected $_MorbusType_id = 47;

	/**
	 * @return string
	 */
	function getMorbusTypeSysNick()
	{
		return 'prof';
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
		return 'ProfRegistry';
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $name
	 * @return array
	 */
	function getInputRules($name)
	{
		$rules = array();
		switch ($name) {
			case self::SCENARIO_DO_SAVE:
				$rules[self::ID_KEY] = array(
					'field' => 'MorbusProf_id',
					'rules' => 'trim|required',
					'label' => 'Идентификатор специфики',
					'type' => 'id'
				);
				$rules['morbus_id'] = array(
					'field' => 'Morbus_id',
					'rules' => 'trim',
					'label' => 'Идентификатор простого заболевания',
					'type' => 'id'
				);
				$rules['person_id'] = array(
					'field' => 'Person_id',
					'label' => 'Пациент',
					'rules' => 'trim|required',
					'type' => 'id'
				);
				$rules['diag_id'] = array(
					'field' => 'Diag_id',
					'label' => 'Диагноз',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['evn_pid'] = array(
					'field' => 'Evn_pid',
					'label' => 'Учетный документ',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['morbusprofdiag_id'] = array(
					'field' => 'MorbusProfDiag_id',
					'label' => 'Заболевание',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['morbusprof_year'] = array(
					'field' => 'MorbusProf_Year',
					'label' => 'лет',
					'rules' => 'trim',
					'type' => 'int'
				);
				$rules['morbusprof_month'] = array(
					'field' => 'MorbusProf_Month',
					'label' => 'месяцев',
					'rules' => 'trim',
					'type' => 'int'
				);
				$rules['morbusprof_day'] = array(
					'field' => 'MorbusProf_Day',
					'label' => 'дней',
					'rules' => 'trim',
					'type' => 'int'
				);
				$rules['org_id'] = array(
					'field' => 'Org_id',
					'label' => 'Организация',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['onkooccupationclass_id'] = array(
					'field' => 'OnkoOccupationClass_id',
					'label' => 'Профессия',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['morbusprof_isfit'] = array(
					'field' => 'MorbusProf_IsFit',
					'label' => 'Профпригодность',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['Mode'] = array(
					'field' => 'Mode',
					'label' => 'Режим сохранения',
					'rules' => 'trim|required',
					'type' => 'string'
				);
				break;
			case self::SCENARIO_LOAD_EDIT_FORM:
				$rules[self::ID_KEY] = array(
					'field' => 'Morbus_id',
					'rules' => 'trim',
					'label' => 'Идентификатор',
					'type' => 'id'
				);
				break;
			case 'getMorbusProfDiagData':
				$rules['morbusprofdiag_id'] = array(
					'field' => 'MorbusProfDiag_id',
					'label' => 'Заболевание',
					'rules' => 'trim|required',
					'type' => 'id'
				);
				break;
		}
		return $rules;
	}

	/**
	 * construct
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'MorbusProf';
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
	 * Получение списка пользователей регистра
	 */
	function loadUsersRegistry($data, $group)
	{
		if (empty($group)) {
			$group = $this->groupRegistry;
		}
		$query = "
			select PMUser_id
			from v_pmUserCache (nolock)
			where pmUser_groups like '%{$group}%'
				and Lpu_id = ?
		";
		$result = $this->db->query($query, array($data['Lpu_id']));
		$response = $result->result('array');
		if ( is_array($response) && count($response) > 0 ) {
			return $response;
		} else	{
			return false;
		}
	}

 	/**
	 * Проверка обязательных параметров специфики
	 *
	 * @params Mode 
	 *	- check_by_personregister - это создание нового заболевания при ручном вводе новой записи регистра из формы "Регистр по ..." (если есть открытое заболевание, то ничего не сохраняем. В регистре сохранится связь с открытым или созданным заболевание)
	 *	- personregister_viewform - это ввод данных специфики из панели просмотра в форме записи регистра
	 *	- evnsection_viewform - это ввод данных специфики из панели просмотра движения в ЭМК
	 *	- evnvizitpl_viewform - это ввод данных специфики из панели просмотра посещения в ЭМК
	 *	- check_by_evn - это создание нового заболевания при редактировании данных движения/посещения (если есть открытое заболевание и диагноз уточнился и посещение/движение актуально, то сохраняем диагноз и привязываем заболевание к этому посещению/движению)
	 */
	protected function checkParams($data)
	{
		if ( empty($data['Mode']) ) {
			throw new Exception('Не указан режим сохранения');
		}
		$fields = array(
			'Diag_id' => 'Идентификатор диагноза'
			,'Person_id' => 'Идентификатор человека'
			,'Evn_pid' => 'Идентификатор движения/посещения'
			,'pmUser_id' => 'Идентификатор пользователя'
			,'Morbus_id' => 'Идентификатор заболевания'
			,'MorbusProf_id' => 'Идентификатор специфики заболевания'
		);
		switch ($data['Mode']) {
			case 'check_by_evn':
				$check_fields_list = array('Evn_pid','pmUser_id');//'Diag_id','Person_id', - не обязательные, но рекомендуемые
				break;
			case 'check_by_personregister':
				$check_fields_list = array('Diag_id','Person_id','pmUser_id');
				$data['Evn_pid'] = null;
				break;
			case 'personregister_viewform':
				$check_fields_list = array('MorbusProf_id','Morbus_id','Person_id','pmUser_id');
				$data['Evn_pid'] = null;
				break;
			case 'evnsection_viewform':
			case 'evnvizitpl_viewform':
				$check_fields_list = array('MorbusProf_id','Morbus_id','Evn_pid','pmUser_id');
				break;
			default:
				throw new Exception('Указан неправильный режим сохранения');
				break;
		}
		$errors = array();
		foreach($check_fields_list as $field) {
			if( empty($data[$field]) )
			{
				$errors[] = 'Не указан '. $fields[$field];
			}
		}
		if( count($errors) > 0 ) {
			throw new Exception(implode('<br />',$errors));
		}
		return $data;
	}


	/**
	 * Сохранение
	 *
	 * @param array $data Обязательные параметры:
	 * 1) Evn_pid или пара Person_id и Diag_id
	 * 2) pmUser_id
	 * 3) Mode
	 * @param bool $isAllowTransaction
	 * @return array Идентификаторы заболевания, специфики заболевания или ошибка
	 * @throws Exception
	 * @comment Будут сохранены те данные, которые переданы, т.е. можно отдельные параметры сохранять
	 */
	function doSave($data = array(), $isAllowTransaction = true) {
		try {
			$this->isAllowTransaction = false;
			$data = $this->checkParams($data);
			//exit(var_export($data, true));

			$data['Evn_aid'] = null;
			if (in_array($data['Mode'],array(
				'evnsection_viewform','evnvizitpl_viewform'
			))) {
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
				$result = swMorbus::getStaticMorbusCommon()->loadLastEvnData($this->getMorbusTypeSysNick(), $data['Evn_pid'], $data['Person_id'], $data['Diag_id']);
				if ( !empty($result) ) {
					//учетный документ найден
					$data['Evn_aid'] = $result[0]['Evn_id'];
					$data['Diag_id'] = $result[0]['Diag_id'];
					$data['Person_id'] = $result[0]['Person_id'];
				} else {
					throw new Exception('Ошибка определения актуального учетного документа с данным заболеванием');
				}
			}
			
			if (in_array($data['Mode'],array('personregister_viewform')) || $data['Evn_pid'] == $data['Evn_aid']) {
				// Если редактирование происходит из актуального учетного документа
				// или из панели просмотра в форме записи регистра, то сохраняем данные
				// Стартуем транзакцию
				$this->isAllowTransaction = $isAllowTransaction;
				if ( !$this->beginTransaction() ) {
					throw new Exception('Ошибка при попытке запустить транзакцию');
				}
				$PersonWeight_delid = NULL;
				$PersonHeight_delid = NULL;
				if (array_key_exists('PersonWeight_Weight', $data)) {
					if ( empty($data['PersonWeight_Weight']) ) {
						if (isset($data['PersonWeight_id'])) {
							$PersonWeight_delid = $data['PersonWeight_id'];
						}
						$data['PersonWeight_id'] = NULL;
					} else {
						// создаем или обновляем запись о весе
						$result = $this->savePersonWeight(array(
							'Server_id' => $data['Server_id'],
							'PersonWeight_id' => $data['PersonWeight_id'],
							'Person_id' => $data['Person_id'],
							'PersonWeight_setDate' => date('Y-m-d'),
							'PersonWeight_Weight' => $data['PersonWeight_Weight'],
							'Evn_id'=>$data['Evn_pid'],
							'pmUser_id' => $data['pmUser_id']
						));
						$data['PersonWeight_id'] = $result[0]['PersonWeight_id'];
					}
				}

				if (array_key_exists('PersonHeight_Height', $data)) {
					if ( empty($data['PersonHeight_Height']) ) {
						if (isset($data['PersonHeight_id'])) {
							$PersonHeight_delid = $data['PersonHeight_id'];
						}
						$data['PersonHeight_id'] = NULL;
					} else {
						// создаем или обновляем запись о росте
						$result = $this->savePersonHeight(array(
							'Server_id' => $data['Server_id'],
							'PersonHeight_id' => $data['PersonHeight_id'],
							'Person_id' => $data['Person_id'],
							'PersonHeight_setDate' => date('Y-m-d'),
							'PersonHeight_Height' => $data['PersonHeight_Height'],
							'Evn_id'=>$data['Evn_pid'],
							'pmUser_id' => $data['pmUser_id']
						));
						$data['PersonHeight_id'] = $result[0]['PersonHeight_id'];
					}
				}

				//update таблиц Morbus, MorbusProf
				$tmp = $this->updateMorbusSpecific($data);
				if ( isset($tmp[0]['Error_Msg']) ) {
					throw new Exception($tmp[0]['Error_Msg']);
				}
				$response = $tmp;

				if (isset($PersonWeight_delid)) {
					// удаляем запись о весе
					$this->deletePersonWeight($PersonWeight_delid);
				}
				if (isset($PersonHeight_delid)) {
					$this->deletePersonHeight($PersonHeight_delid);
				}
				
				$this->commitTransaction();
				return $response;
			} else {
				//Ничего не сохраняем
				throw new Exception('Данные не были сохранены, т.к. данный учетный документ не является актуальным для данного заболевания.');
			}
		} catch (Exception $e) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Сохранение специфики заболевания. <br />'. $e->getMessage()));	
		}
	}

	/**
	 * Применение данных извещения
	 * @param EvnNotifyProf_model $evn
	 * @return array
	 * @throws Exception
	 */
	function setEvnNotifyProf(EvnNotifyProf_model $evn)
	{
		$morbus = $this->getFirstRowFromQuery('
			select top 1 MorbusBase_id, MorbusProf_id
			from v_MorbusProf (nolock)
			where Morbus_id = :Morbus_id
		', array('Morbus_id' => $evn->Morbus_id));
		if (empty($morbus)) {
			throw new Exception('Не удалось получить данные заболевания!', 500);
		}
		$data = array(
			'MorbusBase_id' => $morbus['MorbusBase_id'],
			'MorbusBase_setDT' => date('Y-m-d'),
			'Morbus_id' => $evn->Morbus_id,
			'Diag_id' => $evn->Diag_id,
			'Morbus_setDT' => date('Y-m-d'),
			'MorbusProf_id' => $morbus['MorbusProf_id'],
			'MorbusProfDiag_id' => $evn->morbusprofdiag_id,
			'pmUser_id' => $evn->promedUserId,
		);
		return $this->updateMorbusSpecific($data);
	}

	/**
	 * Сохранение специфики
	 * @param $data
	 * @return array
	 * @comment Будут сохранены те данные, которые переданы, т.е. можно отдельные параметры сохранять
	 */
	private function updateMorbusSpecific($data) {
		$err_arr = array();
		$entity_saved_arr = array();
		$not_edit_fields = array('Evn_pid', 'Person_id','MorbusProf_id', 'Morbus_id', 'MorbusBase_id','MorbusType_id','Morbus_disDT','MorbusBase_disDT');
		if (isset($data['field_notedit_list']) && is_array($data['field_notedit_list'])) {
			$not_edit_fields = array_merge($not_edit_fields,$data['field_notedit_list']);
		}

		foreach($this->entityFields as $entity => $l_arr) {
			if ($entity == 'Person') {
				// сохраняем данные по человеку (организация и профессия)
				if (array_key_exists('Org_id', $data)) {
					// обновляем организацию
					$query = "
						select top 1
							job.Job_id
						from
							v_PersonState ps (nolock)
							inner join v_Job job (nolock) on job.Job_id = ps.Job_id
						where
							Person_id = :Person_id
							and ISNULL(job.Org_id, 0) = ISNULL(:Org_id, 0)
					";

					$result = $this->db->query($query, $data);
					if (is_object($result)) {
						$resp = $result->result('array');
						if (empty($resp[0]['Job_id'])) {
							// добавляем периодику
							$query = "
								declare @ErrCode int
								declare @ErrMsg varchar(400)
								declare @curdate datetime = dbo.tzGetDate();

								exec p_PersonJob_ins
									@Server_id = :Server_id,
									@Person_id = :Person_id,
									@PersonJob_insDT = @curdate,
									@Org_id = :Org_id,
									@pmUser_id = :pmUser_id,
									@Error_Code = @ErrCode output,
									@Error_Message = @ErrMsg output
								select @ErrMsg as ErrMsg
							";
							$this->db->query($query, array(
								'Server_id' => $data['Server_id'],
								'Person_id' => $data['Person_id'],
								'Org_id' => $data['Org_id'],
								'pmUser_id' => $data['pmUser_id']
							));
						}
					}
				}
				if (array_key_exists('OnkoOccupationClass_id', $data)) {
					// обновляем профессию
					$query = "
						select top 1
							OnkoOccupationClass_id
						from
							v_MorbusOnkoPerson with (nolock)
						where
							Person_id = :Person_id
						order by
							MorbusOnkoPerson_insDT desc
					";

					$result = $this->db->query($query, $data);
					if (is_object($result)) {
						$resp = $result->result('array');
						if (empty($resp[0]['OnkoOccupationClass_id']) || (!empty($resp[0]['OnkoOccupationClass_id']) && $resp[0]['OnkoOccupationClass_id'] != $data['OnkoOccupationClass_id'])) {
							// добавляем периодику
							$query = "
								declare @ErrCode int
								declare @ErrMsg varchar(400)
								declare @curdate datetime = dbo.tzGetDate();

								exec p_MorbusOnkoPerson_ins
									@Person_id = :Person_id,
									@OnkoOccupationClass_id = :OnkoOccupationClass_id,
									@pmUser_id = :pmUser_id,
									@Error_Code = @ErrCode output,
									@Error_Message = @ErrMsg output
								select @ErrMsg as ErrMsg
							";
							$this->db->query($query, array(
								'Person_id' => $data['Person_id'],
								'OnkoOccupationClass_id' => $data['OnkoOccupationClass_id'],
								'pmUser_id' => $data['pmUser_id']
							));
						}
					}
				}
				continue;
			}

			$allow_save = false;
			foreach($data as $key => $value) {
				if (in_array($key, $l_arr) && !in_array($key, $not_edit_fields)) {
					$allow_save = true;
					break;
				}
			}
			if ( $allow_save && !empty($data[$entity.'_id']) ) {
				$q = 'select top 1 '. implode(', ',$l_arr) .' from dbo.v_'. $entity .' WITH (NOLOCK) where '. $entity .'_id = :'. $entity .'_id';
				$p = array($entity.'_id' => $data[$entity.'_id']);
				$r = $this->db->query($q, $data);
				//echo getDebugSQL($q, $data);
				if (is_object($r)) {
					$result = $r->result('array');
					if( empty($result) || !is_array($result[0]) || count($result[0]) == 0 ) {
						$err_arr[] = 'Получение данных '. $entity .' По идентификатору '. $data[$entity.'_id'] .' данные не получены';
						continue;
					}
					foreach($result[0] as $key => $value) {
						if (is_object($value) && $value instanceof DateTime)
						{
							$value = $value->format('Y-m-d H:i:s');
						}
						//в $data[$key] может быть null
						$p[$key] = array_key_exists($key, $data)?$data[$key]:$value;
						// ситуация, когда пользователь удалил какое-то значение
						$p[$key] = (empty($p[$key]) || $p[$key]=='0')?null:$p[$key];
					}
				} else {
					$err_arr[] = 'Получение данных '. $entity .' Ошибка при выполнении запроса к базе данных';
					continue;
				}
				$field_str = '';
				foreach($l_arr as $key) {
					$field_str .= '
						@'. $key .' = :'. $key .',';
				}
				$q = '
					declare
						@'. $entity .'_id bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @'. $entity .'_id = :'. $entity .'_id;
					exec dbo.p_'. $entity .'_upd
						@'. $entity .'_id = @'. $entity .'_id output, '. $field_str .'
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @'. $entity .'_id as '. $entity .'_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				';
				$p['pmUser_id'] = $data['pmUser_id'];
				$r = $this->db->query($q, $p);
				if (is_object($r)) {
					$result = $r->result('array');
					if( !empty($result[0]['Error_Msg']) )
					{
						$err_arr[] = 'Сохранение данных '. $entity .' '. $result[0]['Error_Msg'];
						continue;
					}
					$entity_saved_arr[$entity .'_id'] = $data[$entity.'_id'];
				} else {
					$err_arr[] = 'Сохранение данных '. $entity .' Ошибка при выполнении запроса к базе данных';
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
		$entity_saved_arr['Error_Msg'] = (count($err_arr) > 0) ? implode('<br />',$err_arr) : null;
		return array($entity_saved_arr);
	}

	/**
	 * Создание специфики заболевания
	 * Должно выполняться внутри транзакции
	 * @param array $data
	 * @param bool $isAllowTransaction
	 * @return array
	 * @throws Exception
	 */
	public function autoCreate($data, $isAllowTransaction = true)
	{
		if (empty($data['MorbusBase_id']) ||empty($data['Person_id'])
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
		$queryParams['MorbusProfDiag_id'] = isset($data['MorbusProfDiag_id'])?$data['MorbusProfDiag_id']:null;

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@Morbus_id bigint = :Morbus_id,
				@{$tableName}_id bigint = null,
				@pmUser_id bigint = :pmUser_id;

			-- должно быть одно на Morbus
			select top 1 @{$tableName}_id = {$tableName}_id from v_{$tableName} with (nolock) where Morbus_id = @Morbus_id

			if isnull(@{$tableName}_id, 0) = 0
			begin
				exec p_{$tableName}_ins
					@{$tableName}_id = @{$tableName}_id output,
					@Morbus_id = @Morbus_id,
					@MorbusProfDiag_id = :MorbusProfDiag_id,
					@MorbusProf_Year = null,
					@MorbusProf_Month = null,
					@MorbusProf_Day = null,
					@MorbusProf_IsFit = null,
					@pmUser_id = @pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;

				select @{$tableName}_id as {$tableName}_id, 2 as IsCreate, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			end
			else
			begin
				select @{$tableName}_id as {$tableName}_id, 1 as IsCreate, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			end
		";
		//echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) ) {
			throw new Exception('Ошибка БД', 500);
		}
		$resp = $result->result('array');
		if (!empty($resp[0]['Error_Msg'])) {
			throw new Exception($resp[0]['Error_Msg'], 500);
		}
		if (empty($resp[0][$tableName . '_id'])) {
			throw new Exception('Что-то пошло не так', 500);
		}
		$this->_saveResponse[$tableName . '_id'] = $resp[0][$tableName . '_id'];
		return $this->_saveResponse;
	}

	/**
	 * Метод получения данных для панели просмотра
	 * При вызове из формы просмотра записи регистра параметр MorbusProf_pid будет содержать Person_id, также будет передан PersonRegister_id
	 * При вызове из формы просмотра движения/посещения параметр MorbusProf_pid будет содержать Evn_id просматриваемого движения/посещения
	 */
	function getViewData($data)
	{
		if (empty($data['session'])) { $data['session'] = null; }
		if (empty($data['MorbusProf_pid'])) { $data['MorbusProf_pid'] = null; }
		if (empty($data['PersonRegister_id'])) { $data['PersonRegister_id'] = null; }
		$this->load->library('swMorbus');
		$params = swMorbus::onBeforeViewData($this->getMorbusTypeSysNick(), $data['session'], $data['MorbusProf_pid'], $data['PersonRegister_id']);
		if ($params['Error_Msg']) {
			throw new Exception($params['Error_Msg']);
		}
		$params['MorbusProf_pid'] = $data['MorbusProf_pid'];
		// предусмотрено создание специфических учетных документов (в которых есть ссылка на посещение/движение из которого они созданы)
		$query = "
			select
				" . swMorbus::getAccessTypeQueryPart('MV', 'MB', 'MorbusProf_pid', '1', '0', 'accessType', 'AND not exists(
									select top 1 Evn.Evn_id from v_Evn Evn with (nolock)
									where
										Evn.Person_id = MB.Person_id
										and Evn.Morbus_id = MV.Morbus_id
										and Evn.EvnClass_id in (11,13,32)
										and Evn.Evn_id <> :MorbusProf_pid
										and Evn.Evn_setDT > EvnEdit.Evn_setDT
										and exists (
											select top 1 v_PersonHeight.Evn_id
											from v_PersonHeight with (nolock)
											where v_PersonHeight.Evn_id = Evn.Evn_id
											union all
											select top 1 v_PersonWeight.Evn_id
											from v_PersonWeight with (nolock)
											where v_PersonWeight.Evn_id = Evn.Evn_id
										)
								) /* можно редактировать, если нет более актуального документа в рамках которого изменялась специфика */') . ",
				MV.Diag_id,
				ISNULL(D.Diag_Code + '. ','') + D.Diag_Name as Diag_Name,
				HWFT.HarmWorkFactorType_Name,
				MPD.HarmWorkFactorType_id,
				MPD.Diag_oid,
				ISNULL(DO.Diag_Code + '. ','') + DO.Diag_Name as Diag_oName,
				MV.MorbusProf_id,
				MV.Morbus_id,
				MV.MorbusProfDiag_id,
				MV.MorbusProf_Year,
				MV.MorbusProf_Month,
				MV.MorbusProf_Day,
				MV.MorbusProf_IsFit,
				case when MV.MorbusProf_IsFit = 2 then 'Годен к работе с вредными условиями' else 'Не годен к работе с вредными условиями' end as MorbusProfIsFit_Name,
				MPD.MorbusProfDiag_Name,
				MB.MorbusBase_id,
				:MorbusProf_pid as MorbusProf_pid,
				MB.Person_id,
				l.Lpu_id as Lpu_iid,
				l.Lpu_Nick as Lpu_Name,
				O.Org_id,
				O.Org_Nick as Org_Name,
				OOC.OnkoOccupationClass_id,
				OOC.OnkoOccupationClass_Name
			from
				v_MorbusProf MV with (nolock)
				inner join v_MorbusBase MB with (nolock) on MB.MorbusBase_id = MV.MorbusBase_id
				left join v_PersonState PS (nolock) on ps.Person_id = mb.Person_id
				left join v_Job j (nolock) on j.Job_id = ps.Job_id
				left join v_Org o (nolock) on o.Org_id = j.Org_id
				left join v_PersonRegister PR with (nolock) on PR.Morbus_id = MV.Morbus_id and PR.Diag_id = MV.Diag_id
				left join v_EvnNotifyProf ENP with (nolock) on ENP.Morbus_id = MV.Morbus_id and ENP.Diag_id = MV.Diag_id
				left join v_MorbusProfDiag MPD (nolock) on MPD.MorbusProfDiag_id = MV.MorbusProfDiag_id
				left join v_Diag d (nolock) on d.Diag_id = mv.Diag_id
				left join v_Diag do (nolock) on do.Diag_id = mpd.Diag_oid
				left join v_HarmWorkFactorType HWFT (nolock) on HWFT.HarmWorkFactorType_id = MPD.HarmWorkFactorType_id
				left join v_Lpu l (nolock) on l.Lpu_id = isnull(PR.Lpu_iid,ENP.Lpu_did)
				outer apply (
					select top 1
						OnkoOccupationClass_id
					from
						v_MorbusOnkoPerson with (nolock)
					where
						Person_id = PS.Person_id
					order by
						MorbusOnkoPerson_insDT desc
				) as mop
				left join v_OnkoOccupationClass OOC (nolock) on OOC.OnkoOccupationClass_id = mop.OnkoOccupationClass_id
			where
				MV.Morbus_id = :Morbus_id
		";
		//echo getDebugSql($query, $params);die();
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Читает одну строку для формы редактирования
	 * @param array $data
	 * @return array
	 */
	function doLoadEditForm($data)
	{
		$data['scenario'] = self::SCENARIO_LOAD_EDIT_FORM;
		$params = array(
			'Morbus_id' => $data['Morbus_id']
		);
		$query = "
			select top 1
				case when MV.Morbus_disDT is null then 1 else 0 end as accessType,
				MV.Diag_id,
				MV.MorbusProf_id,
				MV.Morbus_id,
				MB.MorbusBase_id,
				MB.Person_id,
				MV.MorbusProfDiag_id,
				job.Org_id,
				job.Post_id
			from
				v_MorbusProf MV with (nolock)
				inner join v_MorbusBase MB with (nolock) on MB.MorbusBase_id = MV.MorbusBase_id
				--left join v_MorbusProgDiag MPD with(nolock) on MPD.MorbusProfDiag_id = MV.MorbusProfDiag_id
				left join v_PersonState ps (nolock) on ps.Person_id = mb.Person_id
				left join v_Job job (nolock) on job.Job_id = ps.Job_id
			where
				MV.Morbus_id = :Morbus_id
		";
		//echo getDebugSql($query, $params);die();
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return array();
		}
	}


	/**
	 * Запись роста
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	function savePersonHeight($data)
	{
		$this->load->model('PersonHeight_model');
		$result = $this->PersonHeight_model->savePersonHeight(array(
			'Server_id' => $data['Server_id'],
			'PersonHeight_id' => $data['PersonHeight_id'],
			'Person_id' => $data['Person_id'],
			'PersonHeight_setDate' => $data['PersonHeight_setDate'],
			'PersonHeight_Height' => $data['PersonHeight_Height'],
			'PersonHeight_IsAbnorm' => NULL,
			'HeightAbnormType_id' => NULL,
			'HeightMeasureType_id' => NULL,
			'Evn_id'=>$data['Evn_id'],
			'pmUser_id' => $data['pmUser_id']
		));
		if (!empty($result[0]['Error_Msg'])) {
			throw new Exception($result[0]['Error_Msg']);
		}
		if (empty($result[0]['PersonHeight_id'])) {
			throw new Exception('Ошибка при сохранении роста');
		}
		return $result;
	}

	/**
	 * Удаляет запись о росте
	 * @param int $id
	 * @return void
	 * @throws Exception
	 */
	function deletePersonHeight($id)
	{
		$this->load->model('PersonHeight_model');
		$result = $this->PersonHeight_model->deletePersonHeight(array(
			'PersonHeight_id' => $id
		));
		if (empty($result)) {
			throw new Exception('Ошибка при выполнении запроса к базе данных (удаление результатов измерения роста пациента)');
		}
		if (!empty($result[0]['Error_Msg'])) {
			throw new Exception($result[0]['Error_Msg']);
		}
	}

	/**
	 * Запись массы
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	function savePersonWeight($data)
	{
		// создаем или обновляем запись о весе
		$this->load->model('PersonWeight_model');
		$result = $this->PersonWeight_model->savePersonWeight(array(
			'Server_id' => $data['Server_id'],
			'PersonWeight_id' => $data['PersonWeight_id'],
			'Person_id' => $data['Person_id'],
			'PersonWeight_setDate' => $data['PersonWeight_setDate'],
			'PersonWeight_Weight' => $data['PersonWeight_Weight'],
			'PersonWeight_IsAbnorm' => NULL,
			'WeightAbnormType_id' => NULL,
			'WeightMeasureType_id' => NULL,
			'Evn_id'=>$data['Evn_id'],
			'Okei_id' => 37,//кг
			'pmUser_id' => $data['pmUser_id']
		));
		if (!empty($result[0]['Error_Msg'])) {
			throw new Exception($result[0]['Error_Msg']);
		}
		if (empty($result[0]['PersonWeight_id'])) {
			throw new Exception('Ошибка при сохранении массы');
		}
		return $result;
	}

	/**
	 * Удаляет запись о весе
	 * @param int $id
	 * @return void
	 * @throws Exception
	 */
	function deletePersonWeight($id)
	{
		// удаляем запись о весе
		$this->load->model('PersonWeight_model');
		$result = $this->PersonWeight_model->deletePersonWeight(array(
			'PersonWeight_id' => $id
		));
		if (empty($result)) {
			throw new Exception('Ошибка при выполнении запроса к базе данных (удаление результатов измерения массы пациента)');
		}
		if (!empty($result[0]['Error_Msg'])) {
			throw new Exception($result[0]['Error_Msg']);
		}
	}
	/**
	 * Вывод печатной формы «Карта динамического наблюдения»
	 */
	function doPrint($data)
	{
		$parse_data = $this->loadPrintData($data['Morbus_id']);
		if (empty($parse_data)) {
			return 'Не удалось получить данные карты';
		}
		$parse_data['MorbusProfDispDates'] = array();
		$parse_data['MorbusProfDisp'] = array();
		$data['MorbusProf_id'] = $parse_data['MorbusProf_id'];
		$data['isOnlyLast'] = 0;
		$this->load->model('MorbusProfDisp_model', 'morbusProfDisp');
		$tmp = $this->morbusProfDisp->doLoadGrid($data);
		if (is_array($tmp)) {
			foreach ($tmp as $row) {
				$d = $row['MorbusProfDisp_Date'];
				$t = $row['RateType_id'];
				if (!in_array($d, $parse_data['MorbusProfDispDates'])) {
					$parse_data['MorbusProfDispDates'][] = $d;
				}
				if (empty($parse_data['MorbusProfDisp'][$t])) {
					$parse_data['MorbusProfDisp'][$t] = array(
						'RateType_Name' => $row['RateType_Name'],
						'RateValues' => array(),
					);
				}
				$parse_data['MorbusProfDisp'][$t]['RateValues'][$d] = $row['Rate_ValueStr'];
			}
		}
		$this->load->library('parser');
		return $this->parser->parse('print_Profregistry', $parse_data, true);
	}

	/**
	 * Получаем данные для печатной формы
	 */
	protected function loadPrintData($id)
	{
		$query = "
			select top 1
				MN.MorbusProf_id,
				convert(varchar(10), EN.EvnNotifyProf_diagDate, 104) as MorbusProf_diagDate,
				convert(varchar(10), MN.MorbusProf_transDate, 104) as MorbusProf_transDate,
				convert(varchar(10), MN.MorbusProf_dialDate, 104) as MorbusProf_dialDate,
				convert(varchar(10), MN.MorbusProf_firstDate, 104) as MorbusProf_firstDate,
				convert(varchar(10), MN.MorbusProf_begDate, 104) as MorbusProf_begDate,
				convert(varchar(10), MN.MorbusProf_deadDT, 104) as MorbusProf_deadDT,
				MN.MorbusProf_CRIDinamic,
				MN.KidneyTransplantType_id,
				MN.DialysisType_id,
				MN.DispGroupType_id,
				MN.ProfResultType_id,
				Diag.Diag_Code,
				Diag.Diag_Name,
				cast(PersonHeight.PersonHeight_Height as int) PersonHeight_Height,
				cast(PersonWeight.PersonWeight_Weight as int) PersonWeight_Weight,
				PR.PersonRegister_id as PersonRegister_Num,
				Sex.Sex_Code,
				Sex.Sex_Name,
				a.Address_Address as Person_Address,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				dbo.Age2(PS.Person_BirthDay, dbo.tzGetDate()) as Person_Age,
				PS.Person_Phone,
				PS.Person_SurName,
				PS.Person_FirName,
				PS.Person_SecName
			from
				v_MorbusProf MN with (nolock)
				inner join v_MorbusBase MB with (nolock) on MB.MorbusBase_id = MN.MorbusBase_id
				inner join v_PersonState PS with (nolock) on PS.Person_id = MB.Person_id
				left join Sex with (nolock) on Sex.Sex_id = PS.Sex_id
				left join v_Address a (nolock) on a.Address_id = ISNULL(PS.PAddress_id,PS.UAddress_id)
				left join Diag with (nolock) on Diag.Diag_id = MN.Diag_id
				left join PersonHeight with (nolock) on PersonHeight.PersonHeight_id = MN.PersonHeight_id
				left join PersonWeight with (nolock) on PersonWeight.PersonWeight_id = MN.PersonWeight_id
				left join v_PersonRegister PR with (nolock) on PR.Morbus_id = MN.Morbus_id
				left join v_EvnNotifyProf EN with (nolock) on PR.Morbus_id = MN.Morbus_id
			where
				EN.Morbus_id = ?
		";
		$res = $this->db->query($query, array($id));
		if ( is_object($res) ) {
			$tmp = $res->result('array');
			if (is_array($tmp) && !empty($tmp)) {
				return $tmp[0];
			}
		}
		return array();
	}

	/**
	 * Получение данных по профзаболению
	 */
	function getMorbusProfDiagData($data) {
		$params = array('MorbusProfDiag_id' => $data['MorbusProfDiag_id']);

		$query = "
			select top 1
				HWFT.HarmWorkFactorType_id,
				HWFT.HarmWorkFactorType_Code,
				HWFT.HarmWorkFactorType_Name,
				DiagO.Diag_id as Diag_oid,
				DiagO.Diag_Code as Diag_oCode,
				DiagO.Diag_Name as Diag_oName
			from v_MorbusProfDiag MPD with(nolock)
			left join v_HarmWorkFactorType HWFT with(nolock) on HWFT.HarmWorkFactorType_id = MPD.HarmWorkFactorType_id
			left join v_Diag DiagO with(nolock) on DiagO.Diag_id = MPD.Diag_oid
			where MPD.MorbusProfDiag_id = :MorbusProfDiag_id
		";

		return $this->queryResult($query, $params);
	}
}
