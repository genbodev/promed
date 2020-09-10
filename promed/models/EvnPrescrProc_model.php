<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		PromedWeb
 * @access		public
 * @copyright	Copyright (c) 2013 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 * @version		09.2013
 */
require_once('EvnPrescrAbstract_model.php');
/**
 * Модель назначения "Процедуры и манипуляции"
 *
 * Назначения с типом "Процедуры и манипуляции" хранятся в таблицах EvnPrescrProc, EvnUslugaCommon, EvnPrescrProcTimetable.
 * В назначении должна быть указана только одна услуга в таблице EvnUslugaCommon.
 * В назначении указывается график выполнения в таблице EvnPrescrProcTimetable.
 * Отметки о выполнении ставятся на отдельных записях (бирках) графика, а не в назначении.
 *
 * @package		EvnPrescr
 * @author		Александр Пермяков
 *
 */
class EvnPrescrProc_model extends EvnPrescrAbstract_model
{
	/**
	 * Конструктор
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Определение идентификатора типа назначения
	 * @return int
	 */
	public function getPrescriptionTypeId() {
		return 6;
	}

	/**
	 * Определение идентификатора типа курса
	 * @return int
	 */
	public function getCourseTypeId() {
		return 2;
	}

	/**
	 * Определение имени таблицы с данными назначения
	 * @return string
	 */
	public function getTableName() {
		return 'EvnPrescrProc';
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $scenario
	 * @return array
	 */
	public function getInputRules($scenario) {
		$rules = array();
		switch ($scenario) {
			case 'doSaveEvnCourseProc':
				$rules = array(
					array('field' => 'UslugaComplex_id','label' => 'Услуга','rules' => 'required','type' => 'string'),
					array('field' => 'EvnPrescrProc_IsCito','label' => 'Cito','rules' => '','type' => 'string'),
					array('field' => 'parentEvnClass_SysNick','label' => 'Системное имя род.события','rules' => '', 'default' => 'EvnSection','type' => 'string'),
					array('field' => 'signature','label' => 'Признак для подписания','rules' => '','type' => 'int'),
					array('field' => 'EvnCourseProc_id','label' => 'Идентификатор курса','rules' => '','type' => 'id'),
					array('field' => 'EvnCourseProc_pid','label' => 'Идентификатор род.события','rules' => 'required','type' => 'id'),
					array('field' => 'EvnCourseProc_setDate','label' => 'Начать','rules' => '','type' => 'date'),
					array('field' => 'EvnCourseProc_setTime','label' => 'Начать','rules' => '','type' => 'string'),
					array('field' => 'PersonEvn_id','label' => 'Человек','rules' => 'required','type' => 'id'),
					array('field' => 'Server_id','label' => 'Идентификатор сервера','rules' => 'required','type' => 'int'),
					array('field' => 'MedPersonal_id','label' => 'Врач, создавший курс','rules' => '','type' => 'id'),
					array('field' => 'LpuSection_id','label' => 'Отделение врача, создавшего курс','rules' => '','type' => 'id'),
					array('field' => 'Lpu_id','label' => 'Идентификатор ЛПУ','rules' => '','type' => 'id'),
					array('field' => 'Morbus_id','label' => 'Заболевание','rules' => '','type' => 'id'),
					array('field' => 'EvnCourseProc_MaxCountDay','label' => 'Повторов в сутки','rules' => '','type' => 'int'),
					array('field' => 'EvnCourseProc_MinCountDay','label' => 'Повторов в сутки','rules' => '','type' => 'int'),
					array('field' => 'EvnCourseProc_ContReception','label' => 'Повторять непрерывно','rules' => '','type' => 'int'),//непрерывный прием
					array('field' => 'DurationType_recid','label' => 'Тип продолжительности непрерывного повтора','rules' => '','type' => 'id'),//непрерывного приема
					array('field' => 'EvnCourseProc_Interval','label' => 'Перерыв','rules' => '','type' => 'int'),
					array('field' => 'DurationType_intid','label' => 'Тип продолжительности перерыва','rules' => '','type' => 'id'),
					array('field' => 'EvnCourseProc_Duration','label' => 'Продолжительность курса','default' => 1,'rules' => '','type' => 'int'),
					array('field' => 'DurationType_id','label' => 'Тип продолжительности курса','rules' => 'required','type' => 'id'),
					array('field' => 'EvnCourseProc_FactCount','label' => 'Исполненное количество','rules' => '','type' => 'int'),
					array('field' => 'ResultDesease_id','label' => 'Исход','rules' => '','type' => 'id'),
					array('field' => 'EvnPrescrProc_Descr','label' => 'Комментарий','rules' => 'trim','type' => 'string'),
				);
				break;
			case 'doSave':
				$rules = array(
					array('field' => 'parentEvnClass_SysNick','label' => 'Системное имя род.события','rules' => '', 'default' => 'EvnSection','type' => 'string'),
					array('field' => 'signature','label' => 'Признак для подписания','rules' => '','type' => 'int'),

					array('field' => 'EvnPrescrProc_id','label' => 'Идентификатор назначения','rules' => '','type' => 'id'),
					array('field' => 'EvnCourse_id','label' => 'Идентификатор курса','rules' => 'required','type' => 'id'),
					array('field' => 'EvnPrescrProc_pid','label' => 'Идентификатор род.события','rules' => 'required','type' => 'id'),
					array('field' => 'UslugaComplex_id','label' => 'Услуга','rules' => 'required','type' => 'string'),
					array('field' => 'EvnPrescrProc_setDate','label' => 'Дата','rules' => 'required','type' => 'date'),
					array('field' => 'EvnPrescrProc_IsCito','label' => 'Cito','rules' => '','type' => 'string'),
					array('field' => 'EvnPrescrProc_Descr','label' => 'Комментарий','rules' => 'trim','type' => 'string'),
					array('field' => 'PersonEvn_id','label' => 'Человек','rules' => 'required','type' => 'id'),
					array('field' => 'Server_id','label' => 'Идентификатор сервера','rules' => 'required','type' => 'int')
				);
				break;
			case 'doLoadEvnCourseProcEditForm':
				$rules[] = array(
					'field' => 'EvnCourseProc_id',
					'label' => 'Идентификатор курса',
					'rules' => 'required',
					'type' =>  'id'
				);
				break;
			case 'doLoad':
				$rules[] = array(
					'field' => 'EvnPrescrProc_id',
					'label' => 'Идентификатор назначения',
					'rules' => 'required',
					'type' =>  'id'
				);
				break;
		}
		return $rules;
	}

	/**
	 * Вспомогательная функция для создания курса
	 */
	protected function _addDate($new_date, &$date_list, $countInDay) {
		while ($countInDay > 0) {
			$date_list[]=$new_date;
			$countInDay--;
		}
	}

	/**
	 * Вспомогательная функция для создания курса
	 */
	protected function _getNumDay($durationtype_id) {
		$num = 1;
		switch ($durationtype_id) {
			case 2: $num = 7; break;
			case 3: $num = 30; break;
		}
		return $num;
	}

	/**
	 * Сохранение данных курса
	 * 
	 * @param array $data
	 * @return type
	 * @throws Exception
	 */
	protected function _saveEvnCourseProc($data) {
		if(empty($data['EvnCourseProc_id'])) {
			$action = 'ins';
			$data['EvnCourseProc_id'] = NULL;
		} else {
			$action = 'upd';
		}
		if (empty($data['EvnCourseProc_pid'])) {
			throw new Exception('Не указано посещение или движение, на котором создан курс', 400);
		}
		if (empty($data['UslugaComplex_id'])) {
			throw new Exception('Не указана услуга', 400);
		}
		if (empty($data['MedPersonal_id'])) {
			throw new Exception('Не указан врач, создавший курс', 400);
		}
		if (empty($data['LpuSection_id'])) {
			throw new Exception('Не указано отделение', 400);
		}
		if (empty($data['Lpu_id'])) {
			throw new Exception('Не указана МО', 400);
		}
		if (empty($data['PersonEvn_id'])) {
			throw new Exception('Не указан человек', 400);
		}
		$query = "
			declare
			@Res bigint,
			@pmUser_id bigint,
			@ErrCode int,
			@ErrMessage varchar(4000);

			set @Res = :EvnCourseProc_id;

			exec p_EvnCourseProc_{$action}
			   @EvnCourseProc_id = @Res OUTPUT
			  ,@EvnCourseProc_pid = :EvnCourseProc_pid --Посещение или движение, на котором создан курс
			  ,@Lpu_id = :Lpu_id
			  ,@Server_id = :Server_id
			  ,@PersonEvn_id = :PersonEvn_id
			  ,@EvnCourseProc_setDT = :EvnCourseProc_setDT
			  ,@EvnCourseProc_disDT = :EvnCourseProc_disDT
			  ,@Morbus_id = :Morbus_id
			  ,@MedPersonal_id = :MedPersonal_id --Врач, создавший курс
			  ,@LpuSection_id = :LpuSection_id
			  ,@CourseType_id = :CourseType_id
			  ,@EvnCourseProc_MinCountDay = :EvnCourseProc_MinCountDay
			  ,@EvnCourseProc_MaxCountDay = :EvnCourseProc_MaxCountDay
			  ,@EvnCourseProc_ContReception = :EvnCourseProc_ContReception
			  ,@DurationType_recid = :DurationType_recid
			  ,@EvnCourseProc_Interval = :EvnCourseProc_Interval
			  ,@DurationType_intid = :DurationType_intid
			  ,@EvnCourseProc_Duration = :EvnCourseProc_Duration
			  ,@DurationType_id = :DurationType_id
			  ,@EvnCourseProc_PrescrCount = :EvnCourseProc_PrescrCount
			  ,@EvnCourseProc_FactCount = :EvnCourseProc_FactCount
			  ,@ResultDesease_id = :ResultDesease_id
			  ,@UslugaComplex_id = :UslugaComplex_id
			  ,@pmUser_id = :pmUser_id
			  ,@Error_Code = @ErrCode OUTPUT
			  ,@Error_Message = @ErrMessage OUTPUT;

			select @Res as EvnCourseProc_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$data['EvnCourseProc_setDT'] = empty($data['EvnCourseProc_setDT']) ? NULL : $data['EvnCourseProc_setDT'];
		$data['EvnCourseProc_disDT'] = empty($data['EvnCourseProc_disDT']) ? NULL : $data['EvnCourseProc_disDT'];
		$data['ResultDesease_id'] = empty($data['ResultDesease_id']) ? NULL : $data['ResultDesease_id'];
		$data['EvnCourseProc_FactCount'] = empty($data['EvnCourseProc_FactCount']) ? 0 : $data['EvnCourseProc_FactCount'];
		$data['DurationType_id'] = empty($data['DurationType_id']) ? 1 : $data['DurationType_id'];
		$data['EvnCourseProc_Duration'] = empty($data['EvnCourseProc_Duration']) ? 1 : $data['EvnCourseProc_Duration'];
		$data['EvnCourseProc_PrescrCount'] = empty($data['EvnCourseProc_PrescrCount']) ? NULL : $data['EvnCourseProc_PrescrCount'];
		$data['DurationType_intid'] = empty($data['DurationType_intid']) ? 1 : $data['DurationType_intid'];
		$data['EvnCourseProc_Interval'] = empty($data['EvnCourseProc_Interval']) ? 0 : $data['EvnCourseProc_Interval'];
		$data['DurationType_recid'] = empty($data['DurationType_recid']) ? 1 : $data['DurationType_recid'];
		$data['EvnCourseProc_ContReception'] = empty($data['EvnCourseProc_ContReception']) ? 1 : $data['EvnCourseProc_ContReception'];
		$data['EvnCourseProc_MaxCountDay'] = empty($data['EvnCourseProc_MaxCountDay']) ? 1 : $data['EvnCourseProc_MaxCountDay'];
		$data['EvnCourseProc_MinCountDay'] = empty($data['EvnCourseProc_MinCountDay']) ? 1 : $data['EvnCourseProc_MinCountDay'];
		$data['Morbus_id'] = empty($data['Morbus_id']) ? NULL : $data['Morbus_id'];
		$data['CourseType_id'] = $this->getCourseTypeId();

		// echo getDebugSQL($query, $data); exit();

		$result = $this->db->query($query, $data);
		if ( !is_object($result) ) {
			throw new Exception('Ошибка при сохранении курса', 500);
		}
		$trans_result = $result->result('array');
		if(!empty($trans_result[0]['Error_Msg'])) {
			throw new Exception($trans_result[0]['Error_Msg'], 500);
		}
		return $trans_result;
	}

	/**
	 * Сохранение назначения
	 * 
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	protected function _save($data = array()) {
		if (empty($data['Lpu_id'])) {
			throw new Exception('Неправильный массив параметров', 500);
		}

		if (empty($data['UslugaComplex_id'])) {
			throw new Exception('Не указана услуга', 400);
		}

		if (empty($data['EvnCourse_id'])) {
			throw new Exception('Не указан курс', 400);
		}

		if (empty($data['EvnPrescrProc_pid'])) {
			throw new Exception('Не указано событие', 400);
		}

		if(empty($data['EvnPrescrProc_id']))
		{
			$action = 'ins';
			$allow_sign = true;
			$data['EvnPrescrProc_id'] = NULL;
			$data['PrescriptionStatusType_id'] = 1;
		}
		else
		{
			$action = 'upd';
			$o_data = $this->getAllData($data['EvnPrescrProc_id']);
			if(!empty($o_data['Error_Msg'])) {
				throw new Exception($o_data['Error_Msg'], 500);
			}
			$allow_sign = (isset($o_data['PrescriptionStatusType_id']) && $o_data['PrescriptionStatusType_id'] == 1);
			$data['PrescriptionStatusType_id'] = $o_data['PrescriptionStatusType_id'];
		}
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :EvnPrescrProc_id;

			exec p_EvnPrescrProc_" . $action . "
				@EvnPrescrProc_id = @Res output,
				@EvnPrescrProc_pid = :EvnPrescrProc_pid,
				@EvnCourse_id = :EvnCourse_id,
				@PrescriptionType_id = :PrescriptionType_id,
				@PrescriptionStatusType_id = :PrescriptionStatusType_id,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@UslugaComplex_id = :UslugaComplex_id,
				@EvnPrescrProc_setDT = :EvnPrescrProc_setDT,
				@EvnPrescrProc_IsCito = :EvnPrescrProc_IsCito,
				@EvnPrescrProc_Descr = :EvnPrescrProc_Descr,
				@pmUser_id = :pmUser_id,
				@EvnPrescrProc_IsExec = :EvnPrescrProc_IsExec,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnPrescrProc_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$data['EvnPrescrProc_setDT'] = NULL;
		if ( !empty($data['EvnPrescrProc_setDate']) ) {
			$data['EvnPrescrProc_setDT'] = $data['EvnPrescrProc_setDate'];
			if ( !empty($data['EvnPrescrProc_setTime']) ) {
				$data['EvnPrescrProc_setDT'] .= ' ' . $data['EvnPrescrProc_setTime'];
			}
		}

		$data['EvnCourse_id'] = empty($data['EvnCourse_id']) ? NULL : $data['EvnCourse_id'];
		$data['EvnPrescrProc_IsExec'] = empty($data['EvnPrescrProc_IsExec']) ? 1 : $data['EvnPrescrProc_IsExec'];
		if(!empty($data['EvnPrescrProc_IsCito']) && ($data['EvnPrescrProc_IsCito'] == 'on' || $data['EvnPrescrProc_IsCito'] == 2)) {
			$data['EvnPrescrProc_IsCito'] = 2;
		} else {
			$data['EvnPrescrProc_IsCito'] = 1;
		}
		$data['PrescriptionType_id'] = $this->getPrescriptionTypeId();

		//echo getDebugSQL($query, $data); exit();

		$result = $this->db->query($query, $data);
		if ( !is_object($result) ) {
			throw new Exception('Ошибка при сохранении назначения', 500);
		}
		$trans_result = $result->result('array');
		if(!empty($trans_result[0]['Error_Msg'])) {
			throw new Exception($trans_result[0]['Error_Msg'], 500);
		}
		return $trans_result;
	}

	/**
	 * Сохранение курса
	 * 
	 * @param array $data
	 * @param bool $isAllowTransaction
	 * @return type
	 * @throws Exception
	 */
	public function doSaveEvnCourseProc($data, $isAllowTransaction = true) {
		// Стартуем транзакцию
		if ($isAllowTransaction) {
			$this->beginTransaction();
		}
		try {
			//определяем есть ли обязательные параметры графика
			$allow_graf_create = false;
			if (
				!empty($data['EvnCourseProc_setDate']) &&
				!empty($data['EvnCourseProc_Duration']) &&
				!empty($data['EvnCourseProc_ContReception']) &&
				!empty($data['DurationType_intid']) &&
				!empty($data['DurationType_id']) &&
				!empty($data['DurationType_recid'])
			) {
				$allow_graf_create = true;
			}
			//создаем график назначений
			$data['EvnCourseProc_setDT'] = empty($data['EvnCourseProc_setDate']) ? NULL : $data['EvnCourseProc_setDate'];
			if (!empty($data['EvnCourseProc_setTime'])) {
				$data['EvnCourseProc_setDT'] .= ' '.$data['EvnCourseProc_setTime'];
			} else {
				$data['EvnCourseProc_setDT'] .= ' 00:00';
			}
			$countInDay = empty($data['EvnCourseProc_MaxCountDay']) ? 1 : $data['EvnCourseProc_MaxCountDay'];
			$date_list = array();
			$this->_addDate($data['EvnCourseProc_setDT'], $date_list, $countInDay);
			if ($allow_graf_create) {
				$courseDuration = $data['EvnCourseProc_Duration']*$this->_getNumDay($data['DurationType_id']);
				$contReception = $data['EvnCourseProc_ContReception']*$this->_getNumDay($data['DurationType_recid']);
				$interval = empty($data['EvnCourseProc_Interval']) ? 0 : $data['EvnCourseProc_Interval'];
				$interval *= $this->_getNumDay($data['DurationType_intid']);
				$day = 1;
				$count_cont = 1;
				$course_begin = strtotime($data['EvnCourseProc_setDT']);
				for($i=1; $i<$courseDuration; $i++)
				{
					if (empty($interval)) {
						//Непрерывный прием
						$new_date = date('Y-m-d',strtotime('+'.$day.' day', $course_begin));
						$this->_addDate($new_date, $date_list, $countInDay);
						$day++;
					} else if(!empty($contReception)) {
						//прием с перерывами между числом дней непрерывного приема
						if($contReception == $count_cont)
						{
							$count_cont = 0;
							$day += $interval;
							$i--;
						}
						else
						{
							$new_date = date('Y-m-d',strtotime('+'.$day.' day', $course_begin));
							$this->_addDate($new_date, $date_list, $countInDay);
							$count_cont++;
							$day++;
						}
					}
				}
				$data['EvnCourseProc_PrescrCount'] = count($date_list);
			} else {
				if (empty($data['EvnCourseProc_Duration'])) {
					$courseDuration = 1;
				} else {
					$courseDuration = $data['EvnCourseProc_Duration']*$this->_getNumDay($data['DurationType_id']);
				}
				$data['EvnCourseProc_PrescrCount'] = $courseDuration*$countInDay;
			}

			if (empty($data['EvnCourseProc_id'])) {
				//нужно создавать курс с назначениями
				$data['EvnCourseProc_FactCount'] = 0;
				$data['EvnCourseProc_MaxCountDay'] = $countInDay;
				$data['EvnCourseProc_MinCountDay'] = $countInDay;
				$tmp = $this->_saveEvnCourseProc($data);
				$data['EvnCourseProc_id'] = $tmp[0]['EvnCourseProc_id'];

				//создаем назначения
				$data['EvnCourse_id'] = $tmp[0]['EvnCourseProc_id'];
				$data['EvnPrescrProc_pid'] = $data['EvnCourseProc_pid'];
				foreach($date_list as $i => $d) {
					$data['EvnPrescrProc_setDate'] = $d;
					$res = $this->_save($data);
					$tmp[0]['EvnPrescrProc_id'.$i] = $res[0]['EvnPrescrProc_id'];
				}
				$tmp[0]['EvnXmlDir_id'] = null;
				$tmp[0]['EvnXmlDirType_id'] = null;
			} else {
				//обновление курсa
				$o_data = $this->getAllData($data['EvnCourseProc_id'], 'EvnCourseProc');
				if (!empty($o_data['Error_Msg'])) {
					throw new Exception($o_data['Error_Msg'], 500);
				}
				//получаем список назначений
				$queryParams = array(
					'EvnCourse_id' => $data['EvnCourseProc_id'],
				);
				$query = "
					select
						EP.EvnPrescrProc_id,
						EP.PrescriptionStatusType_id,
						EP.EvnPrescrProc_setDT,
						ED.EvnDirection_id,
						EU.EvnUsluga_id,
						EP.EvnPrescrProc_IsExec
					from
						v_EvnPrescrProc EP with (nolock)
						outer apply (
							select top 1 EPD.EvnDirection_id from v_EvnPrescrDirection EPD with (nolock)
							inner join v_EvnDirection_all ED with (nolock) on EPD.EvnDirection_id = ED.EvnDirection_id
							where EPD.EvnPrescr_id = EP.EvnPrescrProc_id
							and ED.EvnDirection_failDT is null and ISNULL(ED.EvnStatus_id, 16) not in (12,13)
							order by EPD.EvnPrescrDirection_insDT desc
						) ED
						outer apply (
							select top 1 EvnUsluga_id from v_EvnUsluga with (nolock)
							where EvnPrescr_id = EP.EvnPrescrProc_id and EvnUsluga_setDT is not null
						) EU
					where
						EP.EvnCourse_id = :EvnCourse_id
				";
				$result = $this->db->query($query, $queryParams);
				if (!is_object($result)) {
					throw new Exception('Ошибка при выполнении запроса к базе данных', 500);
				}
				$ep_list = $result->result('array');
				foreach ($ep_list as $row) {
					if (is_object($row['EvnPrescrProc_setDT'])) {
						if ($row['EvnPrescrProc_setDT'] instanceof DateTime) {
							/**
							 * @var DateTime $var
							 */
							$var = $row['EvnPrescrProc_setDT'];
							$row['EvnPrescrProc_setDT'] = $var->format('Y-m-d');
						}
					}
					if (2 == $row['EvnPrescrProc_IsExec']
						|| 2 == $row['PrescriptionStatusType_id']
						|| !empty($row['EvnDirection_id'])
						|| !empty($row['EvnUsluga_id'])
					) {
						//выполненные назначения и назначения с направлением оставляем как есть,
						$isAllowCancel = false;
					} else {
						$isAllowCancel = true;
					}
					if ($isAllowCancel) {
						//отменяем назначение
						$this->_destroy(array(
							'object'=>$this->getTableName(),
							'id'=>$row['EvnPrescrProc_id'],
							'pmUser_id'=>$data['pmUser_id'],
						));
					} else {
						if (in_array($row['EvnPrescrProc_setDT'], $date_list)) {
							foreach ($date_list as $i => $d) {
								if ($d == $row['EvnPrescrProc_setDT'] && count($date_list) != 1) {
									unset($date_list[$i]);
									break;//удаляем только одну запись из графика
								}
							}
						} else {
							// если они не вписываются в график,
							//то пересчитываем $data['EvnCourseProc_Duration']?
							$data['EvnCourseProc_PrescrCount']++;
						}
					}
				}

				$data['EvnCourseProc_MaxCountDay'] = $o_data['EvnCourseProc_MaxCountDay'];
				$data['EvnCourseProc_MinCountDay'] = $o_data['EvnCourseProc_MinCountDay'];
				if ( $data['EvnCourseProc_MinCountDay'] > $countInDay ) {
					// надо обновить минимальное число приемов в сутки
					$data['EvnCourseProc_MinCountDay'] = $countInDay;
				}
				if ( $data['EvnCourseProc_MaxCountDay'] < $countInDay ) {
					// надо обновить максимальное число приемов в сутки
					$data['EvnCourseProc_MaxCountDay'] = $countInDay;
				}
				$data['EvnCourseProc_FactCount'] = $o_data['EvnCourseProc_FactCount'];
				$tmp = $this->_saveEvnCourseProc($data);
				$data['EvnCourseProc_id'] = $tmp[0]['EvnCourseProc_id'];

				//создаем назначения
				$data['EvnPrescrProc_id'] = NULL;
				$data['EvnCourse_id'] = $tmp[0]['EvnCourseProc_id'];
				$data['EvnPrescrProc_pid'] = $data['EvnCourseProc_pid'];
				foreach($date_list as $i => $d) {
					$data['EvnPrescrProc_setDate'] = $d;
					$res = $this->_save($data);
					$tmp[0]['EvnPrescrProc_id'.$i] = $res[0]['EvnPrescrProc_id'];
				}
			}
		} catch (Exception $e) {
			if ($isAllowTransaction) {
				$this->rollbackTransaction();
			}
			return array(array('Error_Msg'=>$e->getMessage(), 'Error_Code'=>$e->getCode()));
		}
		if ($isAllowTransaction) {
			$this->commitTransaction();
		}
		return $tmp;
	}

	/**
	 * Сохранение назначения
	 * 
	 * @param array $data
	 * @return array
	 */
	public function doSave($data = array(), $isAllowTransaction = true) {
		try {
			//создание или редактирование "назначения" в существующем курсе
			return $this->_save($data);
		} catch (Exception $e) {
			return array(array('Error_Msg'=>$e->getMessage(), 'Error_Code'=>$e->getCode()));
		}
	}

	/**
	 * Получение данных для формы редактирования курса
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array|boolean
	 */
	public function doLoadEvnCourseProcEditForm($data) {
		$query = "
			select
				case when 1 = 1 then 'edit' else 'view' end as accessType,
				EСP.EvnCourseProc_id,
				EСP.EvnCourseProc_pid,
				EСP.ResultDesease_id,
				EСP.Morbus_id,
				EСP.UslugaComplex_id,
				EСP.EvnCourseProc_MinCountDay,
				EСP.EvnCourseProc_MaxCountDay,
				EСP.EvnCourseProc_Duration,
				EСP.DurationType_id,
				EСP.EvnCourseProc_ContReception,
				EСP.DurationType_recid,
				EСP.EvnCourseProc_Interval,
				EСP.DurationType_intid,
				EСP.MedPersonal_id,
				EСP.LpuSection_id,
				convert(varchar(10), EСP.EvnCourseProc_setDT, 104) as EvnCourseProc_setDate,
				convert(varchar(5), EСP.EvnCourseProc_setDT, 108) as EvnCourseProc_setTime,
				case when isnull(EP.EvnPrescrProc_IsCito,1) = 1 then 'off' else 'on' end as EvnPrescrProc_IsCito,
				EP.EvnPrescrProc_Descr,
				EСP.PersonEvn_id,
				EСP.Server_id
			from
				v_EvnCourseProc EСP with (nolock)
				outer apply (
					select top 1
						EvnPrescrProc_IsCito,
						EvnPrescrProc_Descr
					from v_EvnPrescrProc with (nolock)
					where EСP.EvnCourseProc_id = EvnCourse_id
					order by EvnPrescrProc_insDT desc
				) EP
			where
				EСP.EvnCourseProc_id = :EvnCourseProc_id
		";

		$queryParams = array(
			'EvnCourseProc_id' => $data['EvnCourseProc_id']
		);
		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение данных для формы редактирования назначения (не курса!)
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array|boolean
	 */
	public function doLoad($data) {
		$query = "
			select
				case when EP.PrescriptionStatusType_id = 1 then 'edit' else 'view' end as accessType,
				EP.EvnPrescrProc_id,
				EP.EvnPrescrProc_pid,
				EP.EvnCourse_id,
				EP.PrescriptionStatusType_id,
				EP.UslugaComplex_id,
				EP.UslugaComplex_id as EvnPrescrProc_uslugaList,
				ED.EvnDirection_id,
				convert(varchar(10), EP.EvnPrescrProc_setDT, 104) as EvnPrescrProc_setDate,
				case when isnull(EP.EvnPrescrProc_IsCito,1) = 1 then 'off' else 'on' end as EvnPrescrProc_IsCito,
				EP.EvnPrescrProc_Descr,
				EP.Person_id,
				EP.PersonEvn_id,
				EP.Server_id
			from 
				v_EvnPrescrProc EP with (nolock)
				outer apply (
					Select top 1 ED.EvnDirection_id
					from v_EvnPrescrDirection epd with (nolock)
					inner join v_EvnDirection_all ED with (nolock) on epd.EvnDirection_id = ED.EvnDirection_id
						and ED.EvnDirection_failDT is null
						and ISNULL(ED.EvnStatus_id, 16) not in (12,13)
					where epd.EvnPrescr_id = EP.EvnPrescrProc_id
					order by epd.EvnPrescrDirection_insDT desc
				) ED
			where
				EP.EvnPrescrProc_id = :EvnPrescrProc_id
		";
		
		$queryParams = array(
			'EvnPrescrProc_id' => $data['EvnPrescrProc_id']
		);
		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $queryParams);
		
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Возвращает данные для шаблона print_evnprescrproc_list
	 */
	function getPrintData($data) {
		$query = "
			select
				EP.EvnPrescrProc_id
				,UC.UslugaComplex_Name as UslugaComplex_Name
				,1 as cntUsluga
				,convert(varchar(10),EP.EvnPrescrProc_setDate,104) as EvnPrescr_setDate
				,EP.EvnPrescrProc_Descr as Descr
				,EP.EvnPrescrProc_IsCito as IsCito
				,null as ContReception
				,null as CountInDay
				,null as CourseDuration
				,null as Interval
				,ISNULL(DTP.DurationType_Nick, '') as DurationTypeP_Nick
				,ISNULL(DTN.DurationType_Nick, '') as DurationTypeN_Nick
				,ISNULL(DTI.DurationType_Nick, '') as DurationTypeI_Nick
			from v_EvnPrescrProc EP with (nolock)
				inner join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EP.UslugaComplex_id
				left join DurationType DTP with (nolock) on null = DTP.DurationType_id
				left join DurationType DTN with (nolock) on null = DTN.DurationType_id
				left join DurationType DTI with (nolock) on null = DTI.DurationType_id
			where
				EP.EvnPrescrProc_pid = :Evn_pid and EP.PrescriptionStatusType_id != 3
			order by
				EP.EvnPrescrProc_id
		";
		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		$response = array();
		if ( is_object($result) )
		{
			$tmp = $result->result('array');
			$cnt = 0;
			foreach($tmp as $row) {
				if($cnt == 0)
				{
					$usluga_list = array();
				}

				$usluga_list[] = $row['UslugaComplex_Name'];
				$cnt++;

				if($cnt == $row['cntUsluga'])
				{
					$response[]=array(
						'Usluga_List' => implode(', ',$usluga_list)
					,'EvnPrescr_setDate' => $row['EvnPrescr_setDate']
					,'Descr' => $row['Descr']
					,'IsCito' => $row['IsCito']
					,'ContReception' => $row['ContReception']
					,'CountInDay' => $row['CountInDay']
					,'CourseDuration' => $row['CourseDuration']
					,'Interval' => $row['Interval']
					,'DurationTypeP_Nick' => $row['DurationTypeP_Nick']
					,'DurationTypeN_Nick' => $row['DurationTypeN_Nick']
					,'DurationTypeI_Nick' => $row['DurationTypeI_Nick']
					);
					$cnt = 0;
				}
			}
		}
		return $response;
	}

	/**
	 * Обработка после отмены назначения
	 */
	function onAfterCancel($data)
	{
		// Если назначение имеет курс, то нужно обновить число назначений
		if (!empty($data['EvnCourse_id'])) {
			$query = 'select * from v_EvnCourse with(nolock) where EvnCourse_id=?';
			$result = $this->db->query($query, array($data['EvnCourse_id']));
			if ( !is_object($result) ) {
				throw new Exception('Ошибка при сохранении курса', 500);
			}
			$tmp = $result->result('array');
			if(empty($tmp)) {
				throw new Exception('Курс не найден', 500);
			}

			$dataEvnCourse = $tmp[0];
			$dataEvnCourse['EvnCourse_PrescrCount'] = $dataEvnCourse['EvnCourse_PrescrCount'] - 1;
			$dataEvnCourse['pmUser_id'] = $data['pmUser_id'];
			$query = "
						declare
						@Res bigint,
						@pmUser_id bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);

						set @Res = :EvnCourse_id;

						exec p_EvnCourse_upd
						   @EvnCourse_id = @Res OUTPUT
						  ,@EvnCourse_pid = :EvnCourse_pid --Посещение или движение, на котором создан курс
						  ,@Lpu_id = :Lpu_id
						  ,@Server_id = :Server_id
						  ,@PersonEvn_id = :PersonEvn_id
						  ,@EvnCourse_setDT = :EvnCourse_setDT
						  ,@EvnCourse_disDT = :EvnCourse_disDT
						  ,@Morbus_id = :Morbus_id
						  ,@MedPersonal_id = :MedPersonal_id --Врач, создавший курс
						  ,@LpuSection_id = :LpuSection_id
						  ,@CourseType_id = :CourseType_id
						  ,@EvnCourse_MinCountDay = :EvnCourse_MinCountDay
						  ,@EvnCourse_MaxCountDay = :EvnCourse_MaxCountDay
						  ,@EvnCourse_ContReception = :EvnCourse_ContReception
						  ,@DurationType_recid = :DurationType_recid
						  ,@EvnCourse_Interval = :EvnCourse_Interval
						  ,@DurationType_intid = :DurationType_intid
						  ,@EvnCourse_Duration = :EvnCourse_Duration
						  ,@DurationType_id = :DurationType_id
						  ,@EvnCourse_PrescrCount = :EvnCourse_PrescrCount
						  ,@EvnCourse_FactCount = :EvnCourse_FactCount
						  ,@ResultDesease_id = :ResultDesease_id
						  ,@pmUser_id = :pmUser_id
						  ,@Error_Code = @ErrCode OUTPUT
						  ,@Error_Message = @ErrMessage OUTPUT;

						select @Res as EvnCourse_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
			$result = $this->db->query($query, $dataEvnCourse);
			if ( !is_object($result) ) {
				throw new Exception('Ошибка при сохранении курса', 500);
			}
			$tmp = $result->result('array');
			if(!empty($tmp[0]['Error_Msg'])) {
				throw new Exception($tmp[0]['Error_Msg'], 500);
			}
		}
	}

	/**
	 * Получение данных для панели просмотра ЭМК и правой панели формы добавления назначений
	 */
	public function doLoadViewData($section, $evn_pid, $sessionParams) {
		$sysnick = swPrescription::getParentEvnClassSysNickBySectionName($section);
		$addJoin = '';
		if ($sysnick) {
			$accessType = "case when {$sysnick}.Lpu_id = :Lpu_id AND isnull({$sysnick}.{$sysnick}_IsSigned,1) = 1 then 'edit' else 'view' end as accessType";
			$addJoin = "left join v_{$sysnick} {$sysnick} with (nolock) on {$sysnick}.{$sysnick}_id = EP.EvnPrescr_pid";
		} else {
			$accessType = "'view' as accessType";
		}
		$addSelect = ' ';
		/*
		if (isset($addSelect)) {
			$addSelect .= ',EvnXmlDir.EvnXml_id as EvnXmlDir_id
				,EvnXmlDir.XmlType_id as EvnXmlDirType_id';
			$addJoin .= "
				outer apply (
					select top 1 EvnXml.EvnXml_id, XmlType.XmlType_id
					from XmlType with (nolock)
					left join EvnXml with (nolock) on EvnXml.XmlType_id = XmlType.XmlType_id and EvnXml.Evn_id = ED.EvnDirection_id
					where XmlType.XmlType_id = 2
				) EvnXmlDir";
		}
		*/
		$query = "
			select
				{$accessType},
				EP.EvnPrescr_id
				,'EvnCourseProc' as object
				,EP.EvnPrescr_pid
				,EP.EvnPrescr_rid
				,convert(varchar,EP.EvnPrescr_setDT,104) as EvnPrescr_setDate
				,null as EvnPrescr_setTime
				,isnull(EP.EvnPrescr_IsExec,1) as EvnPrescr_IsExec
				,case when EU.EvnUsluga_id is null then 1 else 2 end as EvnPrescr_IsHasEvn
				-- Если в качестве даты-времени выполнения брать EU.EvnUsluga_setDT, то дата может не отобразиться, если при выполнении не была создана услуга или услуга не связана с назначением
				-- Поэтому решил использовать EP.EvnPrescr_updDT, т.к. после выполнения эта дата не меняется
				,case when 2 = EP.EvnPrescr_IsExec then convert(varchar,EP.EvnPrescr_updDT,104)+' '+convert(varchar,EP.EvnPrescr_updDT,108) else null end as EvnPrescr_execDT
				,EP.PrescriptionStatusType_id
				,EP.PrescriptionType_id
				,EP.PrescriptionType_id as PrescriptionType_Code
				,isnull(EP.EvnPrescr_IsCito,1) as EvnPrescr_IsCito
				,isnull(EP.EvnPrescr_Descr,'') as EvnPrescr_Descr
				,CUC.UslugaComplex_id as CourseUslugaComplex_id
				,CUC.UslugaComplex_2011id as CourseUslugaComplex_2011id
				,CUC.UslugaComplex_Name as CourseUslugaComplex_Name
				,CUC.UslugaComplex_Code as CourseUslugaComplex_Code
				,MS.MedService_id
				,MS.MedService_Name
				,EPPR.EvnCourse_id
				,isnull(ECPR.EvnCourseProc_MaxCountDay, '') as MaxCountInDay
				,isnull(ECPR.EvnCourseProc_MinCountDay, '') as MinCountInDay
				,isnull(ECPR.EvnCourseProc_Duration, '') as Duration
				,isnull(ECPR.EvnCourseProc_ContReception, '') as ContReception
				,isnull(ECPR.EvnCourseProc_Interval, '') as Interval
				,DTP.DurationType_Nick
				,DTI.DurationType_Nick as DurationType_IntNick
				,DTN.DurationType_Nick as DurationType_RecNick
				,UC.UslugaComplex_id
				,UC.UslugaComplex_2011id
				,UC.UslugaComplex_Code
				,UC.UslugaComplex_Name
				,null as TableUsluga_id
				
				,case when ED.EvnDirection_id is null OR ISNULL(ED.EvnStatus_id, 16) in (12,13) then 1 else 2 end as EvnPrescr_IsDir
				,case when ED.EvnStatus_id is null and (ED.DirFailType_id > 0 OR EQ.QueueFailCause_id > 0 ) then 12 else ED.EvnStatus_id end as EvnStatus_id
				,case when EvnStatus.EvnStatus_Name is null and (ED.DirFailType_id > 0 OR EQ.QueueFailCause_id > 0 ) then 'Отменено' else EvnStatus.EvnStatus_Name end as EvnStatus_Name
				,EvnStatus.EvnStatus_SysNick
				,coalesce(EvnStatusCause.EvnStatusCause_Name, DFT.DirFailType_Name, QFC.QueueFailCause_Name) as EvnStatusCause_Name
				,convert(varchar(10), coalesce(ED.EvnDirection_statusDate, ED.EvnDirection_failDT, EQ.EvnQueue_failDT), 104) as EvnDirection_statusDate
				,ESH.EvnStatusCause_id
				,ED.DirFailType_id
				,EQ.QueueFailCause_id 
				,ESH.EvnStatusHistory_Cause
				
				,ED.EvnDirection_id
				,EQ.EvnQueue_id
				,case when ED.EvnDirection_Num is null /*or isnull(ED.EvnDirection_IsAuto,1) = 2*/ then '' else cast(ED.EvnDirection_Num as varchar) end as EvnDirection_Num
				,case
					when TTMS.TimetableMedService_id is not null then isnull(MS.MedService_Name,'') +' / '+ isnull(Lpu.Lpu_Nick,'')
					when EQ.EvnQueue_id is not null then
						case
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is null
							then isnull(MS.MedService_Name,'')
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is not null
							then isnull(MS.MedService_Name,'') +' / '+ isnull(LU.LpuUnit_Name,'')
							when MS.MedService_id is not null and  MS.LpuSection_id is not null and MS.LpuUnit_id is not null
							then isnull(MS.MedService_Name,'') +' / '+ isnull(LSPD.LpuSectionProfile_Name,'') +' / '+ isnull(LU.LpuUnit_Name,'')
							else isnull(LSPD.LpuSectionProfile_Name,'') +' / '+ isnull(LU.LpuUnit_Name,'')
						end +' / '+ isnull(Lpu.Lpu_Nick,'')
				else '' end as RecTo
				,case
					when TTMS.TimetableMedService_id is not null then isnull(convert(varchar(10), TTMS.TimetableMedService_begTime, 104),'')+' '+isnull(convert(varchar(5), TTMS.TimetableMedService_begTime, 108),'')
					when EQ.EvnQueue_id is not null then 'В очереди с '+ isnull(convert(varchar(10), EQ.EvnQueue_setDate, 104),'')
				else '' end as RecDate
				,case
					when TTMS.TimetableMedService_id is not null then 'TimetableMedService'
					when EQ.EvnQueue_id is not null then 'EvnQueue'
				else '' end as timetable
				,case
					when TTMS.TimetableMedService_id is not null  then TTMS.TimetableMedService_id
					when EQ.EvnQueue_id is not null then EQ.EvnQueue_id
				else '' end as timetable_id
				,EP.EvnPrescr_pid as timetable_pid
				,LU.LpuUnitType_SysNick
				,DT.DirType_Code
				,EUP.EvnUslugaPar_id
				,CASE 
					when ((ED.Lpu_did is not null and ED.Lpu_did <> LpuSession.Lpu_id) or (Lpu.Lpu_id is not null and Lpu.Lpu_id <> LpuSession.Lpu_id) or (ECPR.Lpu_id is not null and ECPR.Lpu_id <> LpuSession.Lpu_id)) then 2 else 1
				end as otherMO
				{$addSelect}
			from v_EvnPrescr EP with (nolock)
				inner join v_EvnPrescrProc EPPR with (nolock) on EPPR.EvnPrescrProc_id = EP.EvnPrescr_id
				left join v_EvnCourseProc ECPR with (nolock) on ECPR.EvnCourseProc_id = EPPR.EvnCourse_id
				left join DurationType DTP with (nolock) on ECPR.DurationType_id = DTP.DurationType_id
				left join DurationType DTN with (nolock) on ECPR.DurationType_recid = DTN.DurationType_id
				left join DurationType DTI with (nolock) on ECPR.DurationType_intid = DTI.DurationType_id
				left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EPPR.UslugaComplex_id
				left join v_UslugaComplex CUC with (nolock) on CUC.UslugaComplex_id = ECPR.UslugaComplex_id

				outer apply (
					Select top 1 ED.EvnDirection_id
						,isnull(ED.Lpu_sid, ED.Lpu_id) Lpu_id
						,ED.EvnQueue_id
						,ED.EvnDirection_Num
						,ED.EvnDirection_IsAuto
						,ED.LpuSection_did
						,ED.LpuUnit_did
						,ED.Lpu_did
						,ED.MedService_id
						,ED.LpuSectionProfile_id
						,ED.DirType_id
						,ED.EvnStatus_id
						,ED.EvnDirection_statusDate
						,ED.DirFailType_id
						,ED.EvnDirection_failDT
					from v_EvnPrescrDirection epd with (nolock)
					inner join v_EvnDirection_all ED with (nolock) on epd.EvnDirection_id = ED.EvnDirection_id
					where epd.EvnPrescr_id = EP.EvnPrescr_id
					order by 
						case when ISNULL(ED.EvnStatus_id, 16) in (12,13) then 2 else 1 end /* первым неотмененное/неотклоненное направление */
						,epd.EvnPrescrDirection_insDT desc
				) ED
				-- службы и параклиника
				outer apply (
					Select top 1 TimetableMedService_id, TimetableMedService_begTime from v_TimetableMedService_lite TTMS with (nolock) where TTMS.EvnDirection_id = ED.EvnDirection_id
				) TTMS
				-- очередь
				outer apply (
					Select top 1 EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id 
					from v_EvnQueue EQ with (nolock)
					where EQ.EvnDirection_id = ED.EvnDirection_id
					and EQ.EvnQueue_recDT is null
					-- это костыль для того, чтобы направления у которых есть связь по EQ.EvnQueue_id = ED.EvnQueue_id, но нет реальной записи в TimetableMedService можно было отменить
					union
					Select top 1 EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id 
					from v_EvnQueue EQ with (nolock)
					where (EQ.EvnQueue_id = ED.EvnQueue_id)
					and (EQ.EvnQueue_recDT is null or TTMS.TimetableMedService_id is null)
					and EQ.EvnQueue_failDT is null
				) EQ
				outer apply(
					select top 1 ESH.EvnStatus_id, ESH.EvnStatusCause_id, ESH.pmUser_insID, ESH.EvnStatusHistory_Cause
					from EvnStatusHistory ESH with(nolock)
					where ESH.Evn_id = ED.EvnDirection_id
						and ESH.EvnStatus_id = ED.EvnStatus_id
					order by ESH.EvnStatusHistory_begDate desc
				) ESH
				left join EvnStatus with(nolock) on EvnStatus.EvnStatus_id = ESH.EvnStatus_id
				left join EvnStatusCause with(nolock) on EvnStatusCause.EvnStatusCause_id = ESH.EvnStatusCause_id
				left join v_DirFailType DFT with(nolock) on DFT.DirFailType_id = ED.DirFailType_id
				left join v_QueueFailCause QFC with(nolock) on QFC.QueueFailCause_id = EQ.QueueFailCause_id
				-- сама служба (todo: надо ли оно)
				left join v_MedService MS with (nolock) on MS.MedService_id = ED.MedService_id -- ED.MedService_did должно быть
				-- отделение для полки и стаца и для очереди
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = coalesce(ED.LpuSection_did, MS.LpuSection_id)
				-- заказанная услуга для параклиники
				left join v_EvnUslugaPar EUP with (NOLOCK) on EUP.EvnDirection_id = ED.EvnDirection_id
				-- подразделение для очереди и служб
				left join v_LpuUnit LU with (nolock) on coalesce(ED.LpuUnit_did,EQ.LpuUnit_did,MS.LpuUnit_id) = LU.LpuUnit_id -- todo: в ED.LpuUnit_did пусто, чего не должно быть
				-- профиль для очереди
				left join v_LpuSectionProfile LSPD with (nolock) on coalesce(ED.LpuSectionProfile_id,EQ.LpuSectionProfile_did,LS.LpuSectionProfile_id) = LSPD.LpuSectionProfile_id -- todo: тут на примере оказалось что почему то ED.LpuSectionProfile_id != EQ.LpuSectionProfile_did, чего не должно быть
				-- тип направления
				left join v_DirType DT with (NOLOCK) on ED.DirType_id = DT.DirType_id
				-- ЛПУ
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = coalesce(ED.Lpu_did, LS.Lpu_id, MS.Lpu_id, EQ.Lpu_id)
				left join v_Lpu LpuSession with (nolock) on LpuSession.Lpu_id = :Lpu_id

				outer apply (
					select top 1 EvnUsluga_id, EvnUsluga_setDT from v_EvnUsluga with (nolock)
					where EP.EvnPrescr_IsExec = 2 and UC.UslugaComplex_id is not null and EvnPrescr_id = EP.EvnPrescr_id
				) EU
				{$addJoin}
			where
				EP.EvnPrescr_pid  = :EvnPrescr_pid
				and EP.PrescriptionType_id = 6
				and EP.PrescriptionStatusType_id != 3
			order by
				EPPR.EvnCourse_id,
				EP.EvnPrescr_setDT
		";

		$queryParams = array(
			'EvnPrescr_pid' => $evn_pid,
			'Lpu_id' => $sessionParams['lpu_id'],
		);
		//echo getDebugSql($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$tmp_arr = $result->result('array');
			$response = array();
			$last_course = null;
			$medServices = array();
			$first_index = 0;
			$index_course = 0;
			$prescr_count = 0;
			$last_index = -1;
			foreach ($tmp_arr as $i => $row) {
				$last_index++;
				if ($last_course != $row['EvnCourse_id']) {
					//это первая итерация с другим курсом
					$index_course = $last_index;
					$last_index++;
					$first_index = $i;
					$last_course = $row['EvnCourse_id'];
					$medServices = array();
					$prescr_count = 0;
					$response[$index_course] = $row;
				}
				$prescr_count++;
				if (!empty($row['MedService_Name']) && empty($medServices[$row['MedService_id']])) {
					$medServices[$row['MedService_id']] = $row['MedService_Name'];
				}
				if (empty($tmp_arr[$i+1]) || $last_course != $tmp_arr[$i+1]['EvnCourse_id']) {
					$response[$index_course] = array(
						'EvnCourse_Title'=>$row['CourseUslugaComplex_Code'].' '.$row['CourseUslugaComplex_Name'],
						'EvnCourse_id'=>$row['EvnCourse_id'],
						'EvnPrescr_IsExec'=>$row['EvnPrescr_IsExec'],
						'EvnPrescr_IsCito'=>$row['EvnPrescr_IsCito'],
						'isEvnCourse'=>1,
						'EvnCourse_begDate'=>$tmp_arr[$first_index]['EvnPrescr_setDate'],
						'EvnCourse_endDate'=>$row['EvnPrescr_setDate'],
						'MaxCountInDay'=>$row['MaxCountInDay'],
						'MinCountInDay'=>$row['MinCountInDay'],
						'MedServices'=>implode(', ', $medServices),
						'PrescriptionType_id'=>$row['PrescriptionType_id'],
						'PrescriptionType_Code'=>$row['PrescriptionType_Code'],
						'EvnPrescr_pid'=>$row['EvnPrescr_pid'],
						'EvnPrescr_Count'=>$prescr_count,
						$section . '_id'=>$row['EvnPrescr_pid'].'-'.$row['EvnCourse_id'],
					);
					if (!$this->options['prescription']['enable_show_service_code']) {
						$response[$index_course]['EvnCourse_Title'] = $row['CourseUslugaComplex_Name'];
					}
				}
				$row['UslugaId_List'] = $row['UslugaComplex_id'];
				$row['Usluga_List'] = $row['UslugaComplex_Code'].' '.$row['UslugaComplex_Name'];
				if ($this->options['prescription']['enable_show_service_code']) {
					$row['Usluga_List'] = $row['UslugaComplex_Code'].' '.$row['UslugaComplex_Name'];
				} else {
					$row['Usluga_List'] = $row['UslugaComplex_Name'];
				}
				$row[$section . '_id'] = $row['EvnPrescr_id'].'-0';
				$response[$last_index] = $row;
			}
			return $response;
		} else {
			return false;
		}
	}
}
