<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */

require_once('EvnAbstract_model.php');
/**
 * EvnQueue_model - Модель события постановки в очередь при выписке направления
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      09.2014
 *
 * @property-read int $rid КВС или ТАП
 * @property-read int $pid Движение в отделении или посещение
 *
 * @property-read int $QueueFailCause_id Причина отмены постановки в очередь
 *
 * @property Queue_model $queue_model
 * Сейчас бизнес логика постановки в очередь находится в Queue_model и во многих других моделях
 * @todo Перенести сюда всю бизнес-логику постановки в очередь, когда руки дойдут до рефакторинга
 */
class EvnQueue_model extends EvnAbstract_model
{

    protected $dateTimeForm108 = "HH24:MI:SS";
    protected $dateTimeForm120 = "'YYYY-MM-DD HH24:MI:SS'";
    /**
     * Конструктор объекта
     */
    function __construct()
    {
        parent::__construct();
	    $this->_setScenarioList([
		    self::SCENARIO_SET_ATTRIBUTE,
			self::SCENARIO_DO_SAVE
		    /* пока тут реализуются только обработчики
		    self::SCENARIO_LOAD_EDIT_FORM,
		    self::SCENARIO_AUTO_CREATE,
		    self::SCENARIO_DELETE,*/
	    ]);
    }

	/**
	 * Извлечение значений параметров модели из входящих параметров,
	 * переданных из контроллера
	 * @param array $data
	 * @throws Exception
	 */
	function setParams($data)
	{
		parent::setParams($data);
		//$this->_params[''] = isset($data['']) ? $data[''] : null;
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		parent::_validate();
		$scenarios = [
            self::SCENARIO_SET_ATTRIBUTE,
            self::SCENARIO_AUTO_CREATE,
            self::SCENARIO_DO_SAVE
        ];
		if (in_array($this->scenario, $scenarios)) {
			$this->_checkChangeQueueFailCauseId();
		}
	}

	/**
	 * Проверки и другая логика перед сохранением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeSave($data = [])
	{
		parent::_beforeSave($data);
	}

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnQueue_id';
		$arr[self::ID_KEY]['label'] = 'Идентификатор события постановки в очередь';
		$arr['pid']['alias'] = 'EvnQueue_pid';
		$arr['setdate']['label'] = 'Дата постановки в очередь';
		$arr['setdate']['alias'] = 'EvnQueue_setDate';
		$arr['settime']['label'] = 'Время постановки в очередь';
		$arr['settime']['alias'] = 'EvnQueue_setTime';
		$arr['diddt']['alias'] = 'EvnQueue_didDT';
		$arr['disdt']['alias'] = 'EvnQueue_disDT';
		$arr['isarchived'] = [
			'properties' => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			'alias' => 'EvnQueue_IsArchived',
		];
		$arr['uslugacomplex_did'] = [
			'properties' => [
				self::PROPERTY_IS_SP_PARAM,
			],
			'alias' => 'UslugaComplex_did',
		];
		$arr['evnuslugapar_id'] = [
			'properties' => [
				self::PROPERTY_IS_SP_PARAM,
			], // Идентификатор параклинической услуги
			'alias' => 'EvnUslugaPar_id',
		];
		$arr['queuefailcause_id'] = [
			'properties' => [
				self::PROPERTY_IS_SP_PARAM,
			], // Причина отмены постановки в очередь
			'alias' => 'QueueFailCause_id',
		];
		$arr['faildt'] = [
			'properties' => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			'alias' => 'EvnQueue_failDT',
		];
		$arr['pmuser_failid'] = [
			'properties' => [
				self::PROPERTY_IS_SP_PARAM,
			],
			'alias' => 'pmUser_failID',
		];
		$arr['pmuser_recid'] = [
			'properties' => [
				self::PROPERTY_IS_SP_PARAM,
			], //
			'alias' => 'pmUser_recID',
		];
		$arr['recdt'] = [
			'properties' => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			'alias' => 'EvnQueue_recDT',
		];
		// Данные направления
		$arr['evndirection_id'] = [
			'properties' => [
				self::PROPERTY_IS_SP_PARAM,
			],// идентификатор выписки направления
			'alias' => 'EvnDirection_id',
		];
		$arr['lpusectionprofile_did'] = [
			'properties' => [
				self::PROPERTY_IS_SP_PARAM,
			],// профиль направления ( === LpuSectionProfile_id в направлении)
			'alias' => 'LpuSectionProfile_did',
		];
		$arr['medservice_did'] = [
			'properties' => [
				self::PROPERTY_IS_SP_PARAM,
			],// служба, в которую направлен ( === MedService_id в направлении)
			'alias' => 'MedService_did',
		];
		$arr['resource_did'] = [
			'properties' => [
				self::PROPERTY_IS_SP_PARAM,
			],// служба, в которую направлен ( === Resource_id в направлении)
			'alias' => 'Resource_did',
		];
		$arr['lpusection_did'] = [
			'properties' => [
				self::PROPERTY_IS_SP_PARAM,
			],// отделение, в которое направлен
			'alias' => 'LpuSection_did',
		];
		$arr['lpuunit_did'] = [
			'properties' => [
				self::PROPERTY_IS_SP_PARAM,
			],// подразделение, в которое направлен
			'alias' => 'LpuUnit_did',
		];
		$arr['medpersonal_did'] = [
			'properties' => [
				self::PROPERTY_IS_SP_PARAM,
			],// врач, к которому направлен
			'alias' => 'MedPersonal_did',
		];
		$arr['lpusection_id'] = [
			'properties' => [
				self::PROPERTY_IS_SP_PARAM,
			],// отделение того, кто направил
			'alias' => 'LpuSection_id',
		];
		$arr['medpersonal_id'] = [
			'properties' => [
				self::PROPERTY_IS_SP_PARAM,
			],// кто направил
			'alias' => 'MedPersonal_id',
		];
		$arr['medpersonal_zid'] = [
			'properties' => [
				self::PROPERTY_IS_SP_PARAM,
			],// зав.отделением того, кто направил
			'alias' => 'MedPersonal_zid',
		];
		$arr['dirtype_id'] = [
			'properties' => [
				self::PROPERTY_IS_SP_PARAM,
			], // тип направления
			'alias' => 'DirType_id',
		];
		$arr['evndirection_descr'] = [
			'properties' => [
				self::PROPERTY_IS_SP_PARAM,
			], // Описание направления
			'alias' => 'EvnDirection_Descr',
		];
		$arr['direction_num'] = [
			'properties' => [
				self::PROPERTY_IS_SP_PARAM,
			],// Номер направления ( === EvnDirection_Num в направлении)
			'alias' => 'Direction_Num',
		];
		$arr['diag_id'] = [
			'properties' => [
				self::PROPERTY_IS_SP_PARAM,
			],
			'alias' => 'Diag_id',
		];
		$arr['timetablegraf_id'] = [
			'properties' => [
				self::PROPERTY_IS_SP_PARAM,
			],
			'alias' => 'TimetableGraf_id',
		];
		$arr['timetablestac_id'] = [
			'properties' => [
				self::PROPERTY_IS_SP_PARAM,
			],
			'alias' => 'TimetableStac_id',
		];
		$arr['timetablepar_id'] = [
			'properties' => [
				self::PROPERTY_IS_SP_PARAM,
			], // не используется
			'alias' => 'TimeTablePar_id',
		];
		$arr['timetablemedservice_id'] = [
			'properties' => [
				self::PROPERTY_IS_SP_PARAM,
			], // Расписание службы
			'alias' => 'TimetableMedService_id',
		];
		$arr['timetableresource_id'] = [
			'properties' => [
				self::PROPERTY_IS_SP_PARAM,
			], // Расписание ресурса
			'alias' => 'TimetableResource_id',
		];
		$arr['recmethodtype_id'] = [
			'properties' => [
				self::PROPERTY_IS_SP_PARAM,
			],
			'alias' => 'RecMethodType_id',
		];
		return $arr;
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 28;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnQueue';
	}

	/**
	 * Логика при изменении причины отмены постановки в очередь
	 */
	private function _checkChangeQueueFailCauseId()
	{
		if (empty($this->QueueFailCause_id)) {
			$this->setAttribute('queuefailcause_id', null);
			$this->setAttribute('faildt', null);
			$this->setAttribute('pmuser_failid', null);
		} else if ($this->_isAttributeChanged('queuefailcause_id')) {
			$this->setAttribute('faildt', $this->currentDT);
			$this->setAttribute('pmuser_failid', $this->promedUserId);
		}
	}

	/**
	 * Установка статуса отмены очереди
	 */
	public function setQueueFailCause($data) {
		$this->load->model('Queue_model', 'Queue_model');

		$data['cancelType'] = ($data['EvnStatus_id'] == 13)?'decline':'cancel'; // отклонено или отменено

		$tmp = $this->Queue_model->cancelQueueRecord($data);
		if (!empty($tmp['Error_Msg'])) {
			throw new Exception($tmp['Error_Msg']);
		}

		return [];
	}

	/**
	 * Сохранение очереди из АПИ
	 */
	public function saveEvnQueue($data) {
		$this->load->model('EvnDirection_model');

		$query = "
			select
				Person_id as \"Person_id\",
				Server_id as \"Server_id\",
				PersonEvn_id as \"PersonEvn_id\"
			from
				v_PersonState
			where
				Person_id = :Person_id
		";

		$resp_info = $this->queryResult($query, [
			'Person_id' => $data['Person_id']
		]);

		if (empty($resp_info[0]['Person_id'])) {
			throw new Exception("Ошибка получения данных по человеку");
		}

		$params = [
            'toQueue' => true, // в очередь
            'EvnDirection_id' => null, // новое
            'EvnDirection_pid' => null,
            'Diag_id' => null,
            'EvnDirection_Num' => '0', // сгенерится
            'EvnDirection_Descr' => null,
            'LpuSection_did' => (!empty($data['LpuSection_id']) ? $data['LpuSection_id'] : null), // отделение, куда направили
            'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
            'LpuSectionProfile_did' => $data['LpuSectionProfile_id'], // профиль, по которому направляют
            'LpuSection_id' => null, // направившее отделение
            'MedPersonal_id' => (!empty($data['MedPersonal_id']) ? $data['MedPersonal_id'] : null), // направивший врач
            'MedPersonal_zid' => null,
            'EvnDirection_IsCito' => $data['EvnDirection_IsCito'],
            'EvnDirection_setDT' => date('Y-m-d H:i:s'),
            'EvnDirection_setDate' => date('Y-m-d H:i:s'),
            'Person_id' => $resp_info[0]['Person_id'],
            'PersonEvn_id' => $resp_info[0]['PersonEvn_id'],
            'Server_id' => $resp_info[0]['Server_id'],
            'MedService_id' => null,
            'DirType_id' => 16, // поликлинический приём
            'Lpu_id'  => $data['Lpu_id'],
            'Lpu_did' => $data['Lpu_did'],
            'Lpu_sid' => $data['Lpu_sid'],
            'From_MedStaffFact_id' => -1,
            'EvnDirection_desDT' => (!empty($data['EvnDirection_desDT']) ? $data['EvnDirection_desDT'] : null), // желаемая дата посещения
            'pmUser_id' => $data['pmUser_id']
        ];

		$resp = $this->EvnDirection_model->saveEvnDirection($params);

		if (!empty($resp[0]['EvnDirection_id'])) {
		    $query = "
				select
					EvnQueue_id as \"EvnQueue_id\"
				from
					v_EvnQueue
				where
					EvnDirection_id = :EvnDirection_id
			";

			$resp_eq = $this->queryResult($query,[
				'EvnDirection_id' => $resp[0]['EvnDirection_id']
			]);

			if (!empty($resp_eq[0]['EvnQueue_id'])) {
			    $query = "
				    update EvnQueue
					set 
					    MedStaffFact_did = :MedStaffFact_did,
                        EvnQueueStatus_id = :EvnQueueStatus_id,
                        RecMethodType_id = :RecMethodType_id,
                        EvnQueue_desDT = :EvnQueue_desDT
                    where 
                        EvnQueue_id = :EvnQueue_id
					returning 
					  0 as \"Error_code\",
					  '' as \"Error_Msg\",
					  :EvnQueue_id as \"EvnQueue_id\",
					  :EvnQueueStatus_id as \"EvnQueueStatus_id\"  
				";

			    $params =  [
                    'EvnQueue_id' => $resp_eq[0]['EvnQueue_id'],
                    'MedStaffFact_did' => !empty($data['MedStaffFact_did']) ? $data['MedStaffFact_did'] : $data['MedStaffFact_id'],
                    'EvnQueueStatus_id' => !empty($data['EvnQueueStatus_id']) ? $data['EvnQueueStatus_id'] : 1,
                    'RecMethodType_id' => !empty($data['RecMethodType_id']) ? $data['RecMethodType_id'] : null,
                    'EvnQueue_desDT' => !empty($data['EvnDirection_desDT']) ? $data['EvnDirection_desDT'] : null,
                ];

				$resp_eq = $this->queryResult($query, $params);
				
				return $resp_eq[0];
			} else {
				throw new Exception("Ошибка получения идентификатора очереди");
			}
		} else {
			if (!empty($resp[0]['Error_Msg'])) {
				throw new Exception($resp[0]['Error_Msg']);
			} else {
				throw new Exception("Ошибка сохранения направления");
			}
		}
	}

	/**
	 * Изменение очереди из АПИ
	 */
	public function updateEvnQueueFromAPI($data) {
		$this->load->model('Queue_model', 'Queue_model');

		$data['cancelType'] = 'cancel';

		$tmp = $this->Queue_model->cancelQueueRecord($data);

		if (!empty($tmp['Error_Msg'])) {
			throw new Exception($tmp['Error_Msg']);
		}
		$query = "
			select
			    --select
				EvnQueue_id as \"EvnQueue_id\",
				Person_id as \"Person_id\",
				Lpu_id as \"Lpu_id\",
				LpuSectionProfile_did as \"LpuSectionProfile_id\",
				LpuSection_id as \"LpuSection_id\",
				MedStaffFact_did as \"MedStaffFact_id\",
				to_char(EvnQueue_desDT, ) as EvnQueue_desDT,
				to_char(EvnQueue_setDT, {$this->dateTimeForm120}) as \"EvnQueue_setDate\",
				to_char(EvnQueue_setDT, {$this->dateTimeForm108}) as \"EvnQueue_setTime\",
				EvnQueueStatus_id as \"EvnQueueStatus_id\",
				to_char(EvnQueue_failDT, {$this->dateTimeForm120}) as \"EvnQueue_failDT\"
			    --end select
			from
			    --from
			    v_EvnQueue
			    --end from
			where 
			    --filter
			    EvnQueue_id = :EvnQueue_id
			    --end filter
			limit 10
		";

		return $this->getFirstRowFromQuery($query, $data);
	}

	/**
	 * Получение данных об изменениях в очереди на прием к врачу поликлиники
	 */
	function getEvnQueueByUpdPeriod($data) {
		$params = [];
		$filters = [];

		$filters[] = "EQHM.EvnQueue_updDT::timestamp between :Evn_updbeg and :Evn_updend";
		$params['Evn_updbeg'] = $data['Evn_updbeg'];
		$params['Evn_updend'] = $data['Evn_updend'];

		if (!empty($data['Lpu_did'])) {
			$filters[] = "EQHM.Lpu_id = :Lpu_did";
			$params['Lpu_did'] = $data['Lpu_did'];
		}

		$filters_str = implode(" and ", $filters);

		$query = "
			select
			    --select
				EQHM.Lpu_id as \"Lpu_did\",
				LS.LpuUnit_id as \"LpuUnit_did\",
				LS.LpuSection_id as \"LpuSection_did\",
				LS.LpuSectionProfile_id as \"LpuSectionProfile_did\",
				EQHM.MedPersonal_did as \"MedPersonal_did\",
				EQHM.EvnQueue_id as \"EvnQueue_id\",
				EQHM.Person_id as \"Person_id\",
				EQHM.EvnQueue_deleted as Evn_deleted,
				to_char(EQHM.EvnQueue_insDT, {$this->dateTimeForm120}) as \"Evn_insDT\",
				to_char(EQHM.EvnQueue_updDT, {$this->dateTimeForm120}) as \"Evn_updDT\"
			    --end select
			from
			    --from
				EvnQueueHistMIS EQHM
				left join v_LpuSection LS on LS.LpuSection_id = EQHM.LpuSection_did
				--end from
			where
			    --filter
				{$filters_str}
				--endfilter
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение листа ожидания
	 */
	function getEvnQueue($data) {
		$filters = [
			"(eq.EvnQueueStatus_id = 1 or ed.EvnStatus_id = 10)", // В очереди или поставлено в очередь
        ];
		$params = [];

		if ( !empty($data['Person_id']) ) {
			$filters[] = "eq.Person_id = :Person_id";
			$params['Person_id'] = $data['Person_id'];
		}

		if ( !empty($data['Lpu_id']) ) {
			$filters[] = "ed.Lpu_did = :Lpu_did";
			$params['Lpu_did'] = $data['Lpu_id'];
		}

		if ( !empty($data['LpuSection_id']) ) {
			$filters[] = "eq.LpuSection_did = :LpuSection_did";
			$params['LpuSection_did'] = $data['LpuSection_id'];
		}

		if ( !empty($data['LpuSectionProfile_id']) ) {
			$filters[] = "eq.LpuSectionProfile_did = :LpuSectionProfile_did";
			$params['LpuSectionProfile_did'] = $data['LpuSectionProfile_id'];
		}

		if ( !empty($data['MedStaffFact_id']) ) {
			$filters[] = "eq.MedStaffFact_did = :MedStaffFact_did";
			$params['MedStaffFact_did'] = $data['MedStaffFact_id'];
		}

		$query = "
			select
				eq.EvnQueue_id as \"EvnQueue_id\",
				eq.Person_id as \"Person_id\",
				ed.Lpu_did as \"Lpu_id\",
				eq.LpuSectionProfile_did as \"LpuSectionProfile_id\",
				eq.LpuSection_did as \"LpuSection_id\",
				eq.MedStaffFact_did as \"MedStaffFact_id\",
				to_char(eq.EvnQueue_setDT, {$this->dateTimeForm120}) as \"EvnQueue_setDate\",
				to_char(eq.EvnQueue_setDT, {$this->dateTimeForm108}) as \"EvnQueue_setTime\",
				eq.EvnQueueStatus_id as \"EvnQueueStatus_id\",
				to_char(eq.EvnQueue_desDT, {$this->dateTimeForm120}) as \"EvnQueue_desDT\"
			from
				v_EvnQueue eq
				inner join v_EvnDirection_all ed on ed.EvnDirection_id = eq.EvnDirection_id
				inner join DirType dt on dt.DirType_id = ed.DirType_id
			where
				" . implode(" and ", $filters) . "
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение справочника Статус очереди
	 */
	public function getEvnQueueStatus() {
		return $this->queryResult("
			select
				EvnQueueStatus_id as \"EvnQueueStatus_id\",
				EvnQueueStatus_Code as \"EvnQueueStatus_Code\",
				EvnQueueStatus_Name as \"EvnQueueStatus_Name\"
			from v_EvnQueueStatus
		", []);
	}
}