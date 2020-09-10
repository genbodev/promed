<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 EMSIS.
 * @author       Салават Магафуров
 * @version      11.2018
 */

/**
 * HTMRegister_model - Модель "Регистр ВМП"
 */
class HTMRegister_model extends SwPgModel {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_DO_SAVE
		));
	}


	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName() {
		return 'HTMRegister';
	}

	/**
	 * Получение региональной схемы
	 * @return string
	 */
	function getScheme() {
		return 'r2';
	} 

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes() {
		return array(
			self::ID_KEY => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME
				),
				'alias' => 'HTMRegister_id',
				'label' => 'Идентификатор',
				'save' => 'trim',
				'type' => 'id'
			),
			'register_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_NOT_NULL
				),
				'alias' => 'Register_id',
				'label' => 'Идентификатор в регистре',
				'save' => 'trim|required',
				'type' => 'id'
			),
			'htmregister_number' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'HTMRegister_Number',
				'label' => 'Номер в регистре',
				'save' => 'trim',
				'type' => 'id'
			),
			'htmregister_stage' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'HTMRegister_Stage',
				'label' => 'Этап заполнения анкеты',
				'save'  => 'trim',
				'type'  => 'int'
			),
			'evndirectionhtm_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_NOT_NULL
				),
				'alias' => 'EvnDirectionHTM_id',
				'label' => 'Идентификатор направления на ВМП',
				'save'  => 'trim|required',
				'type'  => 'id'
			),
			'htmregister_isallowpersondata' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'HTMRegister_IsAllowPersonData',
				'label' => 'Согласие на использование персональных данных',
				'save'  => 'trim',
				'type'  => 'id'
			),
			'htmregister_comment' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'HTMRegister_Comment',
				'label' => 'Комментарий (паспортная часть)',
				'save'  => 'trim',
				'type'  => 'string'
			),
			'htmdecision_firstid' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'HTMDecision_FirstId',
				'label' => 'Код принятого решения (ОУЗ)',
				'save'  => 'trim',
				'type'  => 'id'
			),
			'htmregister_firstdecisiondate' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'HTMRegister_FirstDecisionDate',
				'label' => 'Дата принятия решения (1 этап)',
				'save'  => 'trim',
				'type'  => 'date'
			),
			'diag_firstid' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'Diag_FirstId',
				'label' => 'Диагноз (1 этап)',
				'save'  => 'trim',
				'type'  => 'id'
			),
			'htmedicalcareclass_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'HTMedicalCareClass_id',
				'label' => 'Метод ВМП',
				'save'  => 'trim',
				'type'  => 'id'
			),
			'htmregister_docsentdate' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'HTMRegister_DocSentDate',
				'label' => 'Дата направления документов в МО ВМП(1 этап)',
				'save'  => 'trim',
				'type'  => 'date'
			),
			'htmregister_firstdocreceivedate' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'HTMRegister_FirstDocReceiveDate',
				'label' => 'Дата получения документов МО-ОМС (1 этап)',
				'save'  => 'trim',
				'type'  => 'date'
			),
			'medpersonal_firstid' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'MedPersonal_FirstId',
				'label' => 'Идентификатор должностного лица (1 этап)',
				'save'  => 'trim',
				'type'  => 'id'
			),
			'htmregister_firstcomment' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'HTMRegister_FirstComment',
				'label' => 'Комментарии (1 этап)',
				'save'  => 'trim',
				'type'  => 'string'
			),
			// 2 этап
			'htmregister_seconddocreceivedate' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'HTMRegister_SecondDocReceiveDate',
				'label' => 'Дата получения документов (2 этап)',
				'save'  => 'trim',
				'type'  => 'date'
			),
			'htmregister_docexecdate' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'HTMRegister_DocExecDate',
				'label' => 'Дата оформления документов МО-ВМП (2 этап)',
				'save'  => 'trim',
				'type'  => 'date'
			),
			'htmdecision_secondid' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'HTMDecision_SecondId',
				'label' => 'Код принятого решения (2 этап)',
				'save'  => 'trim',
				'type'  => 'id'
			),
			'htmregister_seconddecisiondate' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'HTMRegister_SecondDecisionDate',
				'label' => 'Дата принятия решения (2 этап)',
				'save'  => 'trim',
				'type'  => 'date'
			),
			'htmregister_plannedhospdate' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'HTMRegister_PlannedHospDate',
				'label' => 'Дата планируемой госпитализации (2 этап)',
				'save'  => 'trim',
				'type'  => 'date'
			),
			'htmregister_notifydate' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'HTMRegister_NotifyDate',
				'label' => 'Дата уведомления пациента о дате госпитализации (2 этап)',
				'save'  => 'trim',
				'type'  => 'date'
			),
			'htmnotificationtype_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'HTMNotificationType_id',
				'label' => 'Способ уведомления (2 этап)',
				'save'  => 'trim',
				'type'  => 'id'
			),
			'medpersonal_secondid' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'MedPersonal_SecondId',
				'label' => 'Должностное лицо (2 этап)',
				'save'  => 'trim',
				'type'  => 'id'
			),
			'htmregister_secondcomment' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'HTMRegister_SecondComment',
				'label' => 'Комментарии (2 этап)',
				'save'  => 'trim',
				'type'  => 'string'
			),
			// 3 Этап
			'htmregister_istravelticket' => array(
				'properties' => array(self::PROPERTY_IS_SP_PARAM),
				'alias' => 'HTMRegister_IsTravelTicket',
				'label' => 'Талоны на проезд предоставляются (3 этап)',
				'save'  => 'trim',
				'type'  => 'id'
			),
			'htmregister_ticketissuedate' => array(
				'properties' => array(self::PROPERTY_IS_SP_PARAM),
				'alias' => 'HTMRegister_TicketIssueDate',
				'label' => 'Дата выдачи талонов (3 этап)',
				'save'  => 'trim',
				'type'  => 'date'
			),
			'htmregister_isneedaccompany' => array(
				'properties' => array(self::PROPERTY_IS_SP_PARAM),
				'alias' => 'HTMRegister_IsNeedAccompany',
				'label' => 'Нуждается в сопровождении (3 этап)',
				'save'  => 'trim',
				'type'  => 'id'
			),
			'person_id' => array(
				'properties' => array(self::PROPERTY_IS_SP_PARAM),
				'alias' => 'Person_id',
				'label' => 'ФИО сопровождающего лица (3 этап)',
				'save'  => 'trim',
				'type'  => 'id'
			),
			'medpersonal_thirdid' => array(
				'properties' => array(self::PROPERTY_IS_SP_PARAM),
				'alias' => 'MedPersonal_ThirdId',
				'label' => 'Должностное лицо (3 этап)',
				'save'  => 'trim',
				'type'  => 'id'
			),
			'htmregister_thirdcomment' => array(
				'properties' => array(self::PROPERTY_IS_SP_PARAM),
				'alias' => 'HTMRegister_ThirdComment',
				'label' => 'Комментарии (3 этап)',
				'save'  => 'trim',
				'type'  => 'string'
			),
			//этап 4
			'htmdecision_fourthid' => array(
				'properties' => array(self::PROPERTY_IS_SP_PARAM),
				'alias' => 'HTMDecision_FourthId',
				'label' => 'Код принятого решения (4 этап)',
				'save'  => 'trim',
				'type'  => 'id'
			),
			'evnps_id' => array(
				'properties' => array(self::PROPERTY_IS_SP_PARAM),
				'alias' => 'EvnPS_id',
				'label' => '№ КВС (4 этап)',
				'save'  => 'trim',
				'type'  => 'id'
			),
			'htmregister_applicationdate' => array(
				'properties' => array(self::PROPERTY_IS_SP_PARAM),
				'alias' => 'HTMRegister_ApplicationDate',
				'label' => 'Дата обращения пациента в МО-ВМП (4 этап)',
				'save'  => 'trim',
				'type'  => 'date'
			),
			'medpersonal_fourthid' => array(
				'properties' => array(self::PROPERTY_IS_SP_PARAM),
				'alias' => 'MedPersonal_FourthId',
				'label' => 'Должностное лицо (4 этап)',
				'save'  => 'trim',
				'type'  => 'id'
			),
			'htmregister_fourthcomment' => array(
				'properties' => array(self::PROPERTY_IS_SP_PARAM),
				'alias' => 'HTMRegister_FourthComment',
				'label' => 'Комментарии (4 этап)',
				'save'  => 'trim',
				'type'  => 'string'
			),
			//5й этап
			'htmregister_disdate' => array(
				'properties' => array(self::PROPERTY_IS_SP_PARAM),
				'alias' => 'HTMRegister_DisDate',
				'label' => 'Дата выписки пациента из МО-ВМП (5 этап)',
				'save'  => 'trim',
				'type'  => 'date'
			),

			'htmdirectionresult_id' => array(
				'properties' => array(self::PROPERTY_IS_SP_PARAM),
				'alias' => 'HTMDirectionResult_id',
				'label' => 'Результат направления на ВМП (5 этап)',
				'save'  => 'trim',
				'type'  => 'id'
			),

			'diag_fifthid' => array(
				'properties' => array(self::PROPERTY_IS_SP_PARAM),
				'alias' => 'Diag_FifthId',
				'label' => 'Диагноз (5 этап)',
				'save'  => 'trim',
				'type'  => 'id'
			),

			'htmregister_operdate' => array(
				'properties' => array(self::PROPERTY_IS_SP_PARAM),
				'alias' => 'HTMRegister_OperDate',
				'label' => 'Дата проведения оперативного вмешательства (5 этап)',
				'save'  => 'trim',
				'type'  => 'date'
			),

			'htmresult_id' => array(
				'properties' => array(self::PROPERTY_IS_SP_PARAM),
				'alias' => 'HTMResult_id',
				'label' => 'Результат оказания ВМП (5 этап)',
				'save'  => 'trim',
				'type'  => 'id'
			),

			'htmrecomendation_id' => array(
				'properties' => array(self::PROPERTY_IS_SP_PARAM),
				'alias' => 'HTMRecomendation_id',
				'label' => 'Рекомендовано (5 этап)',
				'save'  => 'trim',
				'type'  => 'id'
			),

			'medpersonal_fifthid' => array(
				'properties' => array(self::PROPERTY_IS_SP_PARAM),
				'alias' => 'MedPersonal_FifthId',
				'label' => 'Должностное лицо (5 этап)',
				'save'  => 'trim',
				'type'  => 'id'
			),

			'htmregister_fifthcomment' => array(
				'properties' => array(self::PROPERTY_IS_SP_PARAM),
				'alias' => 'HTMRegister_FifthComment',
				'label' => 'Комментарии (5 этап)',
				'save'  => 'trim',
				'type'  => 'string'
			),
			// 6 этап
			'htmrejectionreason_id' => array(
				'properties' => array(self::PROPERTY_IS_SP_PARAM),
				'alias' => 'HTMRejectionReason_id',
				'label' => 'Отказано (6 этап)',
				'save'  => 'trim',
				'type'  => 'id'
			),

			'htmregister_issigned' => array(
				'properties' => array(self::PROPERTY_IS_SP_PARAM),
				'alias' => 'HTMRegister_IsSigned',
				'label' => 'Документ подписан (6 этап)',
				'save'  => 'trim',
				'type'  => 'id'
			),

			//разное
			'htmqueuetype_id' => array(
				'properties' => array(self::PROPERTY_IS_SP_PARAM),
				'alias' => 'HTMQueueType_id',
				'label' => 'Признак исключения из очереди',
				'save'  => 'trim',
				'type'  => 'id'
			),
			'htmwaitingreason_id' => array(
				'properties' => array(self::PROPERTY_IS_SP_PARAM),
				'alias' => 'HTMWaitingReason_id',
				'label' => 'Причина ожидания',
				'save'  => 'trim',
				'type'  => 'id'
			),
			'htmregister_waitbegdate' => array(
				'properties' => array(self::PROPERTY_IS_SP_PARAM),
				'alias' => 'HTMRegister_WaitBegDate',
				'label' => 'Дата начала ожидания',
				'save'  => 'trim',
				'type'  => 'date'
			),
			'htmregister_waitenddate' => array(
				'properties' => array(self::PROPERTY_IS_SP_PARAM),
				'alias' => 'HTMRegister_WaitEndDate',
				'label' => 'Дата окончания ожидания',
				'save'  => 'trim',
				'type'  => 'date'
			)
		);
	}

	/**
	 * Извлечение значений атрибутов из входящих параметров,
	 * переданных из контроллера.
	 * Устанавливаем только то, что пришло.
	 * Поэтому, чтобы при записи не потерять значения,
	 * предварительно подгружаем данные по идешнику,
	 * а потом их перезаписываем новыми
	 * @param array $data
	 */
	function setAttributes($data) {
		foreach ($this->defAttribute as $key => $info) {
			if (in_array(self::PROPERTY_NOT_SAFE, $info['properties'])) {
				continue;
			}
			if (isset($info['applyMethod']) && method_exists($this, $info['applyMethod'])) {
				call_user_func(array($this, $info['applyMethod']), $data);
				continue;
			}
			$param = $this->_getInputParamName($key, $info);
			if (!array_key_exists($param, $data)) {
				continue;
			}
			$this->setAttribute($key, $data[$param]);
		}
	}

	/**
	 * Логика перед сохранением
	 */
	protected function _beforeSave($data = null) {
		if (!empty($data)) {
			$this->applyData($data);
		}

		/**
			Не передан идентификатор?
			1. Пытаемся сохранить в общий регистр
			2. Генерим номер анкеты
		 */
		if( $this->getScenario() == self::SCENARIO_DO_SAVE ) {
			$id = $this->getAttribute(self::ID_KEY);
			if ( empty( $id ) ) {
				$this->load->model('Register_model','register');
				$params = array();
				$params['EvnDirectionHTM_id'] = $data['EvnDirectionHTM_id'];
				$params['Register_setDate'] 	= $data['Register_setDate'];
				$params['Person_id'] = $data['Person_id'];
				unset($data['Person_id']);
				$params['RegisterType_Code'] = 'HTM';
				$params['session'] = $this->getSessionParams();
				$params['scenario'] = Register_model::SCENARIO_ADD;
				$result = $this->register->doSave($params);
				$this->setAttribute('register_id',$result['Register_id']);

				$maxNum = $this->getMaxNum();
				$nextNum = $this->genNextNum($maxNum);
				$this->setAttribute( 'htmregister_number', $nextNum );
				$this->setAttribute( 'htmregister_stage', 0 );
				$this->setAttribute( 'htmregister_issigned', 1 );
				$this->setAttribute( 'htmqueuetype_id', 1);
				if(!empty($data['HTMRegister_PlannedHospDate'])) {
					$this->setAttribute( 'htmregister_plannedhospdate', $data['HTMRegister_PlannedHospDate'] );
					$this->setAttribute( 'diag_firstid', $data['Diag_FirstId'] );
					$this->setAttribute( 'htmedicalcareclass_id', $data['HTMedicalCareClass_id'] );
				}
			} else {
				$htmregister_number = $this->getAttribute('htmregister_number');
				if( empty( $htmregister_number )) {
					throw new Exception('Поле "№ талоноа на оказание ВМП" обязательно');
				}
			}
		}
	}

	/**
	 * Возвращает максимальный номер в регистре
	 * @return int
	 */
	function getMaxNum () {
		$query = 'select MAX(HTMRegister_Number) from '.$this->getScheme().'.HTMRegister';
		return $this->getFirstResultFromQuery($query,[]);
	}

	/**
	 * Генерация следующего номера ## #### ##### ## # - 14 символов
	 * 1-2 - Код ОКАТО РФ РБ
	 * 3-11 - номер очередности в регистре
	 * 12-13 - текущий год
	 * 14 - случайная цифра? по умолчанию будет 0
	 * @param str
	 * @return int
	 */
	function genNextNum($max) {
		if( strlen($max) == 14 ) {
			$subNum = intval( substr($max, 2, 9) );
			++$subNum;
			$num = '80'.str_pad($subNum, 9, '0', STR_PAD_LEFT).date('y').'0';
		} else {
			$num = '80000000000'.date('y').'0';
		}
		return $num;
	}

	/**
	 * Лишние поля
	 */
	function isUnecessary($field) {
		$uFields = ['_updDT','_insDT','_insID','_updID'];
		foreach($uFields as $ufield) {
			if(strpos($field, $ufield, 0) !== false) 
				return true;
		}
		return false;
	}

	/**
	 * Выполняет запрос к БД
	 */
	function query($query,$params = false) {
		if($params)
			$response = $this->db->query($query,$params);
		else
			$response = $this->db->query($query);

		$response = $response->result('array');

		foreach ($response as $i=>$resp ) {
			foreach($resp as $field=>$value) {
				$unecessary = $this->isUnecessary($field);
				if($unecessary)
					unset($response[$i][$field]);
				else if ($value  instanceof DateTime) {
					$response[$i][$field] = $value->format('d-m-Y');
				}
			}
		}

		if ( !is_array($response) ) {
			$msg = 'Ошибка при чтении объекта';
			if ($this->isDebug) {
				$msg .= '<br>' . getDebugSQL($query,$params) . '<br>';
			}
			throw new Exception($msg);
		}
		return $response;
	}

	/**
	 * Загрузка левой панели
	 */
	function loadGrid($data) {

		$params = [];
		$params['Register_id'] = $data['Register_id'];

		$query = "
			select
				HR.HTMRegister_id as \"HTMRegister_id\",
				HR.HTMRegister_Number as \"HTMRegister_Number\",
				HR.QueueNumber as \"QueueNumber\",
				LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\"
			from r2.v_HTMRegister HR
			inner join v_EvnDirectionHTM EDH on EDH.EvnDirectionHTM_id = HR.EvnDirectionHTM_id
			left join v_LpuSectionProfile LSP on LSP.LpuSectionProfile_id = EDH.LpuSectionProfile_id
			where Register_id = :Register_id
		";

		return $this->query($query,$params);
	}

	/**
	 * Загрузка данных для формы
	 */
	function loadEditForm($data) {

		$params = [];
		$params['HTMRegister_id'] = $data['HTMRegister_id'];
		
		$query = "
			select
				HR.htmregister_id as \"htmregister_id\",
                HR.register_id as \"register_id\",
                HR.evndirectionhtm_id as \"evndirectionhtm_id\",
                HR.htmregister_number as \"htmregister_number\",
                HR.htmregister_stage as \"htmregister_stage\",
                HR.htmregister_isallowpersondata as \"htmregister_isallowpersondata\",
                HR.htmregister_comment as \"htmregister_comment\",
                HR.htmdecision_firstid as \"htmdecision_firstid\",
                HR.htmregister_firstdecisiondate as \"htmregister_firstdecisiondate\",
                HR.diag_firstid as \"diag_firstid\",
                HR.htmedicalcareclass_id as \"htmedicalcareclass_id\",
                HR.htmregister_docsentdate as \"htmregister_docsentdate\",
                HR.htmregister_firstdocreceivedate as \"htmregister_firstdocreceivedate\",
                HR.htmregister_firstcomment as \"htmregister_firstcomment\",
                HR.htmregister_seconddocreceivedate as \"htmregister_seconddocreceivedate\",
                HR.htmregister_docexecdate as \"htmregister_docexecdate\",
                HR.htmdecision_secondid as \"htmdecision_secondid\",
                HR.htmregister_seconddecisiondate as \"htmregister_seconddecisiondate\",
                HR.htmregister_notifydate as \"htmregister_notifydate\",
                HR.htmnotificationtype_id as \"htmnotificationtype_id\",
                HR.htmregister_secondcomment as \"htmregister_secondcomment\",
                HR.htmregister_istravelticket as \"htmregister_istravelticket\",
                HR.htmregister_ticketissuedate as \"htmregister_ticketissuedate\",
                HR.htmregister_isneedaccompany as \"htmregister_isneedaccompany\",
                HR.person_id as \"person_id\",
                HR.htmregister_thirdcomment as \"htmregister_thirdcomment\",
                HR.htmdecision_fourthid as \"htmdecision_fourthid\",
                HR.evnps_id as \"evnps_id\",
                HR.htmregister_applicationdate as \"htmregister_applicationdate\",
                HR.htmregister_fourthcomment as \"htmregister_fourthcomment\",
                HR.htmregister_disdate as \"htmregister_disdate\",
                HR.htmdirectionresult_id as \"htmdirectionresult_id\",
                HR.diag_fifthid as \"diag_fifthid\",
                HR.htmregister_operdate as \"htmregister_operdate\",
                HR.htmresult_id as \"htmresult_id\",
                HR.htmrecomendation_id as \"htmrecomendation_id\",
                HR.htmregister_fifthcomment as \"htmregister_fifthcomment\",
                HR.htmrejectionreason_id as \"htmrejectionreason_id\",
                HR.htmregister_issigned as \"htmregister_issigned\",
                HR.htmqueuetype_id as \"htmqueuetype_id\",
                HR.htmwaitingreason_id as \"htmwaitingreason_id\",
                HR.htmregister_waitbegdate as \"htmregister_waitbegdate\",
                HR.htmregister_waitenddate as \"htmregister_waitenddate\",
                HR.htmregister_insdt as \"htmregister_insdt\",
                HR.htmregister_upddt as \"htmregister_upddt\",
                HR.pmuser_insid as \"pmuser_insid\",
                HR.pmuser_updid as \"pmuser_updid\",
                HR.medpersonal_firstid as \"medpersonal_firstid\",
                HR.medpersonal_secondid as \"medpersonal_secondid\",
                HR.medpersonal_thirdid as \"medpersonal_thirdid\",
                HR.medpersonal_fourthid as \"medpersonal_fourthid\",
                HR.medpersonal_fifthid as \"medpersonal_fifthid\",
				HR.HTMRegister_PlannedHospDate as \"HTMRegister_PlannedHospDate3\",
				PS.Person_SurName as \"Person_SurName\",
				PS.Person_FirName as \"Person_FirName\",
				PS.Person_SecName as \"Person_SecName\",
				PS.Sex_id as \"Sex_id\",
				PS.Person_BirthDay as \"Person_BirthDay\",
				PS.Person_Snils as \"Person_Snils\",
				PS.Person_Phone as \"Person_Phone\",
				PS.Polis_Num as \"Polis_Num\",
				P.OrgSMO_id as \"OrgSMO_id\",
				--D.Document_id,
				D.DocumentType_id as \"DocumentType_id\",
				D.Document_Ser as \"Document_Ser\",
				D.Document_Num as \"Document_Num\",
				D.OrgDep_id as \"OrgDep_id\",
				Document_begDate as \"Document_begDate\",
				coalesce(PA.Address_Address, '') as \"Person_Address\",
				--case when PCitySocr.KLSocr_Nick in ('Г','ПГТ') then 1 else 2 end as PlaceKind_id,
				--coalesce(PI.PersonInfo_Email,'') as PersonInfo_Email,
				--Lpu.Org_id,
				O.Org_Nick as \"Org_Nick\",
				O.Org_OKPO as \"Org_OKPO\",
				O.Org_OKATO as \"Org_OKATO\",
				coalesce(A.Address_Address, '') as \"Org_Address\",
				O.Org_Email as \"Org_Email\",
				A.Address_Zip as \"OrgAddress_Zip\",
				EvnDirectionHTM_setDate,
				case when EDH.EvnDirectionHTM_IsHTM = 1 then 'Вторичное' else 'Первичное' end as \"EvnDirectionHTM_IsHTM\",
				EDH.HTMFinance_id as \"HTMFinance_id\",
				EDH.HTMOrgDirect_id as \"HTMOrgDirect_id\",
				EDH.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				concat(HRPS.Person_SurName,' ',HRPS.Person_FirName,' ',HRPS.Person_SecName) as \"HRPerson_Name\",
				EPS.EvnPS_NumCard as \"EvnPS_NumCard\"
			from r2.v_HTMRegister HR
				left join r2.v_Register R on R.Register_id = HR.Register_id
				left join v_PersonState PS on PS.Person_id = R.Person_id
				left join v_Document D on D.Document_id = PS.Document_id
				left join v_Polis P on P.Polis_id = PS.Polis_id
				left join v_Address PA on PA.Address_id = PS.PAddress_id
				left join v_KLRgn PRgn on PRgn.KLRgn_id = PA.KLRgn_id
				left join v_KLCity PCity on PCity.KLCity_id = PA.KLCity_id
				left join v_KLTown PTown on PTown.KLTown_id = PA.KLTown_id
				left join v_KLSocr PCitySocr on PCitySocr.KLSocr_id = coalesce(PTown.KLSocr_id, PCity.KLSocr_id, PRgn.KLSocr_id)
				left join v_PersonInfo PI on PI.Person_id = PS.Person_id
				left join v_EvnDirectionHTM EDH on EDH.EvnDirectionHTM_id = HR.EvnDirectionHTM_id
				left join v_PersonState HRPS on HRPS.Person_id = HR.Person_id
				left join v_Lpu L on L.Lpu_id = EDH.Lpu_id
				left join v_Org O on O.Org_id = L.Org_id
				left join v_Address A on A.Address_id = O.PAddress_id
				left join v_EvnPS EPS on EPS.EvnPS_id = HR.EvnPS_id
				left join v_MedPersonal MP1 on MP1.MedPersonal_id = HR.MedPersonal_FirstId
			where HR.HTMRegister_id = :HTMRegister_id
			limit 1
		";

		return $this->query($query,$params);
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $name
	 * @return array
	 */
	function getInputRules($name) {
		$rules = parent::getInputRules($name);
		switch ($name) {
			case self::SCENARIO_LOAD_GRID:
				$rules = array('register_id' => array(
					'field' => 'Register_id',
					'rules' => 'trim|required',
					'label' => 'Идентификатор в регистр',
					'type' => 'id'
				));
				break;
			case 'getEvnPSData':
				$rules = array('evnps_id' => array(
					'field' => 'EvnPS_id',
					'rules' => 'trim|required',
					'label' => 'Идентификатор КВС',
					'type' => 'id'
				));
				break;

			case 'isAllowedToPlanDate':
				$rules = array('htmregister_number' => array(
					'field' => 'HTMRegister_Number',
					'rules' => 'trim|required',
					'label' => 'Номер талона',
					'type' => 'id'
				), 'lpu_id' => array(
					'field' => 'Lpu_id',
					'rules' => 'trim|required',
					'label' => 'Идентификатор МО',
					'type' => 'id'
				));
				break;

			case 'getHTMedicalCareClassComboStore':
				$rules = array(
					'diag_ids'=>array(
						'field' => 'Diag_ids',
						'label' => 'Список идентификаторов диагноза',
						'rules' => 'trim',
						'type' => 'string'
					),
					'date' => array(
						'field' => 'Date',
						'label' => 'Дата проведения ВМП',
						'rules' => 'trim',
						'type'  => 'string'
					)
				);
				break;
		}
		return $rules;
	}

	/**
	 * Запись данных объекта в БД
	 * @param array $queryParams Параметры запроса
	 * @return array Результат выполнения запроса
	 * @throws Exception В случае ошибки запроса или ошибки возвращенной хранимкой
	 */
	protected function _save($queryParams = array()) {
		if (empty($queryParams)) {
			$queryParams = array();
			$queryParams[$this->primaryKey()] = array(
				'value' => $this->id,
				'out' => true,
				'type' => 'bigint'
			);
			$queryParams['pmUser_id'] = $this->promedUserId;
			foreach ($this->defAttribute as $key => $info) {
				if (in_array(self::PROPERTY_IS_SP_PARAM, $info['properties'])) {
					$queryParams[$this->_getColumnName($key, $info)] = $this->getAttribute($key);
				}
			}
		}
		if (empty($queryParams[$this->primaryKey()]) || !array_key_exists('value', $queryParams[$this->primaryKey()])) {
			throw new Exception('Неправильный формат параметров запроса', 500);
		}
		// Конвертируем даты в строки
		foreach ($queryParams as $key => $value ) {
			if ($value instanceof DateTime) {
				$queryParams[$key] = $value->format('Y-m-d H:i:s');
			}
		}

		$id = $this->getAttribute(self::ID_KEY);
		if( empty( $id ) )
			$procedure = 'p_HTMRegister_ins';
		else
			$procedure = 'p_HTMRegister_upd';

		$sp_name = $this->getScheme().'.'.$procedure;

		$tmp = $this->execCommonSP($sp_name, $queryParams);
		if (empty($tmp)) {
			throw new Exception('Ошибка запроса записи данных объекта в БД', 500);
		}
		if (isset($tmp[0]['Error_Msg'])) {
			throw new Exception($tmp[0]['Error_Msg'], $tmp[0]['Error_Code']);
		}
		return $tmp;
	}


	/**
	 * Загрузка данных КВС
	 */
	function getEvnPSData($data) {
		$params = [];
		$params['EvnPS_id'] = $data["EvnPS_id"];
		
		$query = "
			SELECT
				EPS.EvnPS_setDate as \"HTMRegister_ApplicationDate\",
				EPS.EvnPS_disDT as \"HTMRegister_DisDate\",
				EPS.Diag_id as \"Diag_FifthId\",
				D.Diag_FullName as \"Diag_FullName\",
				--case when ES.HTMedicalCareClass_id is not null then 1 end as HTMDirectionResult_id,
				HTMedicalCareClass_id as \"HTMedicalCareClass_id\",
				case when ES.LeaveType_SysNick = 'die' then 1 end as \"isDead\",
				Usluga.EvnUslugaOper_setDate as \"HTMRegister_OperDate\"
			FROM v_evnps EPS
			--left join v_EvnSection ES with(nolock) on ES.EvnSection_pid = EPS.EvnPS_id and EPS.EvnPS_disDT = ES.EvnSection_disDT
			left join v_Evn E on E.Evn_id = EPS.EvnPS_id
			left join v_Diag D on D.Diag_id = EPS.Diag_id
			--left join v_LeaveType LT with(nolock) on LT.LeaveType_id = ES.LeaveType_id
			LEFT JOIN LATERAL (
				select 
					EvnSection_id as EvnSection_id,
					LT.LeaveType_id as LeaveType_id,
					HTMedicalCareClass_id as HTMedicalCareClass_id,
					LT.LeaveType_name as LeaveType_name,
					LT.LeaveType_SysNick as LeaveType_SysNick
				from v_EvnSection vES
				left join v_LeaveType LT on LT.LeaveType_id = vES.LeaveType_id
				where EvnSection_pid = EPS.EvnPS_id
				order by EvnSection_setDate desc
				limit 1
			) ES ON TRUE
			LEFT JOIN LATERAL (
				select EvnUslugaOper_setDate as EvnUslugaOper_setDate from v_EvnUslugaOper ESO
				inner join v_UslugaComplex UC on UC.UslugaComplex_id = ESO.UslugaComplex_id and UslugaComplex_Code ilike 'A16%'
				where ESO.EvnUslugaOper_rid = EPS.EvnPS_id
				order by EvnUslugaOper_setDate desc
				limit 1
			) Usluga ON TRUE
			WHERE EvnPS_id = :EvnPS_id
		";

		return $this->query($query,$params);
	}

	/**
	 * Разрешено ли планировать дату госпитализации
	 */
	function isAllowedToPlanDate($data) {

		$params = [];
		$params['HTMRegister_Number'] = $data['HTMRegister_Number'];
		$params['Lpu_id'] = $data['Lpu_id'];

		$query="
			SELECT HR.HTMRegister_id as \"HTMRegister_id\"
			FROM r2.v_HTMRegister HR
			left join r2.v_Register R on R.Register_id = HR.Register_id
			left join v_personstate PS on PS.Person_id = R.Person_id
			left join v_EvnDirectionHTM EDH on EDH.EvnDirectionHTM_id = HR.EvnDirectionHTM_id
			WHERE
				HR.HTMRegister_Number < :HTMRegister_Number
				and coalesce(HR.HTMQueueType_id, 0) = 1
				and HR.HTMRegister_PlannedHospDate is null
            limit 1
		";
		
		return $this->getFirstResultFromQuery($query,$params);
	}

	/**
	 * Получает список значений для поля "Метод ВМП"
	 */
	function getHTMedicalCareClassComboStore($data){
		$filterList = array();
		if (!empty($data['Diag_ids'])) {
			$diag_arr = json_decode($data['Diag_ids'], true);
			if (is_array($diag_arr) && count($diag_arr) > 0) {
				$filterList[] = "exists (
					select HTMedicalCareDiag_id as HTMedicalCareDiag_id
					from dbo.v_HTMedicalCareDiag DiagLink
					where DiagLink.HTMedicalCareClass_id = DHTMCC.HTMedicalCareClass_id
						and DiagLink.Diag_id in (".implode(',',$diag_arr).")
                    limit 1
					)
				";
			}
		}

		if(!empty($data['Date'])) {
			$filterList[] = "
				{$data['Date']} between DHTMCC.HTMedicalCareClass_begDate and coalesce(DHTMCC.HTMedicalCareClass_endDate,'2999-01-01')
			";
		} else {
			$filterList[] = "DHTMCC.HTMedicalCareClass_endDate is null";
		}
		$query = "
			select
				DHTMCC.HTMedicalCareClass_id as \"HTMedicalCareClass_id\",
				DHTMCC.HTMedicalCareClass_Code as \"HTMedicalCareClass_Code\",
				DHTMCC.HTMedicalCareClass_Name as \"HTMedicalCareClass_Name\",
				HTMPM.HTMedicalPersonModel_Name as \"HTMedicalPersonModel_Name\",
				HTMCT.HTMedicalCareType_Name as \"HTMedicalCareType_Name\"
			from
				dbo.v_HTMedicalCareClass DHTMCC
				left join dbo.HTMedicalCareType HTMCT on HTMCT.HTMedicalCareType_id = DHTMCC.HTMedicalCareType_id
				left join  fed.v_HTMedicalCareClass FHTMCC on FHTMCC.HTMedicalCareClass_id = DHTMCC.HTMedicalCareClass_fid
				left join fed.v_HTMedicalPersonModel HTMPM on FHTMCC.HTMedicalPersonModel_id = HTMPM.HTMedicalPersonModel_id
			where
				DHTMCC.Region_id = 2 and " . implode(' and ', $filterList); 
		return $this->query($query);
	}
}