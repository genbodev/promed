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

/**
 * Абстрактная модель назначения определенного типа
 *
 * Частные модели инкапсулируют бизнес-логику назначения,
 * данные которого хранятся в отдельной таблице
 *
 * Назначения любого типа может быть:
 * Удалено из БД или отменено (PrescriptionStatusType_id = 3), если оно подписано, но не выполнено
 * Подписано (PrescriptionStatusType_id = 2) или отменена подпись (PrescriptionStatusType_id = 1),
 * если оно было подписано
 * Выполнено или отменено выполнение
 *
 * По назначению любого типа, которое содержит услугу, может быть создано направление
 * на службу, которая оказывает эту или аналогичную услугу
 * Если создано направление, то назначение не может быть удалено или отменено.
 * При создании направления создается заказ услуги,
 * который становится доступным для выполнения в соотв. АРМе службы
 *
 * @package		EvnPrescr
 * @author		Александр Пермяков
 *
 */
abstract class EvnPrescrAbstract_model extends swModel
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
	abstract public function getPrescriptionTypeId();

	/**
	 * Определение имени таблицы с данными назначения
	 * @return string
	 */
	abstract public function getTableName();

	/**
	 * Получение данных для формы редактирования
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array|boolean
	 */
	abstract public function doLoad($data);

	/**
	 * Возвращает данные учетного документа
	 * @param int $id
	 * @return array
	 * @throws Exception
	 */
	protected function getEvnData($id) {
		if (empty($id)) {
			throw new Exception('Отсутствует ключ учетного документа');
		}
		$query = "
			select top 1
				EvnClass_SysNick,
				Evn_setDT
			from v_Evn with (nolock) where Evn_id = :Evn_id
		";
		$queryParams = array('Evn_id' => $id);
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$response = $result->result('array');
			if( empty($response) || empty($response[0]) )
			{
				throw new Exception('Данные учетного документа не найдены');
			}
			return $response[0];
		}
		else {
			throw new Exception('Ошибка при запросе данных учетного документа');
		}
	}

	/**
	 * Возвращает все данные назначения по ключу
	 * @param int $id
	 * @param string $object
	 * @param string $format
	 * @return array
	 * @throws Exception
	 */
	protected function getAllData($id, $object = null, $format = 'Y-m-d') {
		if (empty($id)) {
			throw new Exception('Отсутствует ключ назначения');
		}
		if (empty($object)) {
			$object = $this->getTableName();
		}
		$query = "
			select top 1 * from v_{$object} with (nolock) where {$object}_id = :{$object}_id
		";
		$queryParams = array($object.'_id' => $id);
		//echo getDebugSQL($query, $queryParams); exit;
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$response = $result->result('array');
			if( empty($response) || empty($response[0]) )
			{
				throw new Exception('Данные объекта "'.$object.'" не найдены!');
			}
			foreach ($response[0] as $key => $value) {
				if (is_object($value)) {
					if ($value instanceof DateTime) {
						/**
						 * @var DateTime $var
						 */
						$response[0][$key] = $value->format($format);
					}
				}
			}
			return $response[0];
		}
		else {
			throw new Exception('Ошибка при запросе данных назначения');
		}
	}

	/**
	 * Проверяет наличие назначений на указанную дату
	 * @param string $set_date
	 * @param int $id
	 * @return array
	 * @throws Exception
	 */
	protected function isBusyDay($set_date, $id) {
		if (empty($set_date)) {
			throw new Exception('Не указана дата');
		}
		$object = $this->getTableName();
		$query = "
			select top 1 {$object}_id from v_{$object} with (nolock) where cast({$object}_setDT as date) = cast(:set_date as date) and {$object}_id = :id
		";
		$queryParams = array(
			'set_date' => $set_date,
			'id' => $id,
		);
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$response = $result->result('array');
			if( empty($response) || empty($response[0]) )
			{
				return false;
			}
			return true;
		}
		else {
			throw new Exception('Ошибка при проверке наличия назначений на указанную дату');
		}
	}

	/**
	 * Метод очистки дочерних таблиц назначений со списком услуг, с бирками
	 * @param $data
	 * @return array|bool
	 */
	protected function clearEvnPrescrTable($data) {
		if (empty($data['fk_pid']) || empty($data['object']) || empty($data['pid'])) {
			return array(array('Error_Msg'=>'Отсутствует ключ'));
		}
		$object = $data['object'];
		$fk_pid = $data['fk_pid'];
		$query = "
			select {$object}_id from v_{$object} with(nolock) where {$fk_pid} = :pid
		";
		$queryParams = array(
			'pid' => $data['pid']
		);
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');
			if(is_array($response))
			{
				if(empty($response))
				{
					return array(array('Error_Msg'=>null));
				}
				foreach($response as $row) {
					try {
						$this->_destroy(array(
							'object' => $object,
							'id' => $row[$object.'_id'],
							'pmUser_id' => isset($data['pmUser_id'])?$data['pmUser_id']:null,
						));
					} catch (Exception $e) {
						return array(array('Error_Msg'=>$e->getMessage()));
					}
				}
				return array(array('Error_Msg'=>null));
			}
			else
			{
				return false;
			}
		}
		else {
			return false;
		}
	}

	/**
	 * Перенос плановой даты
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function doMoveInDay($data) {
		$object = $this->getTableName();
		try {
			$row = $this->getAllData($data['EvnPrescr_id']);
			if (array_key_exists('EvnDirection_id', $row) && !empty($row['EvnDirection_id'])) {
				throw new Exception('Переместить назначение, если создано направление нельзя!');
			}
			if (array_key_exists($object.'_IsExec', $row) && !empty($row[$object.'_IsExec'])) {
				throw new Exception('Переместить исполненное назначение нельзя!');
			}

			$newDate = new DateTime($data['EvnPrescr_setDate']);//DateTime::createFromFormat('d.m.Y', $set_date);
			if($data['whither'] == 'next' ) {
				$newDate->add(new DateInterval('P1D'));
			} else {
				$newDate->sub(new DateInterval('P1D'));
			}

			if ($this->isBusyDay($newDate->format('Y-m-d'), $data['EvnPrescr_id'])) {
				throw new Exception('Сдвинуть назначение в уже заполненную ячейку нельзя!');
			}
			$evn = $this->getEvnData($row[$object.'_pid']);
			if ($data['whither'] == 'prev' && $newDate < $evn['Evn_setDT']) {
				throw new Exception('Сдвинуть назначение назад ранее дня поступления в отделение нельзя!');
			}
		} catch (Exception $e) {
			return array(array('Error_Msg' => $e->getMessage()));
		}
		$row['pmUser_id'] = $data['pmUser_id'];
		$row[$object.'_setDT'] = $newDate->format('Y-m-d');
		$fields_arr = array();
		foreach ($row as $key=>$value) {
			if (in_array($key, array($object.'_id',$object.'_setDate',$object.'_setTime',$object.'_rid',
				'EvnClass_id','EvnClass_Name','pmUser_insID','pmUser_updID','Person_id',
				$object.'_didDate',$object.'_didTime',$object.'_disDate',$object.'_disTime',
				$object.'_insDT',$object.'_updDT',$object.'_Index',$object.'_Count',
				$object.'_IsArchive', $object.'_Guid'))
			) {
				continue;
			}
			$fields_arr[] = "@{$key} = :{$key}";
		}
		$fields = implode(', ', $fields_arr);
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :{$object}_id;

			exec p_{$object}_upd
				@{$object}_id = @Res output,
				{$fields},
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as {$object}_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		// echo getDebugSQL($query, $row); exit();
		$result = $this->db->query($query, $row);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка запроса к БД'));
		}
	}

	/**
	 * Формирование списка дат для календаря
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	protected function _createDateList($data) {
		$dateList = array();
		if ( isset($data['EvnPrescr_setDate_Range']) && is_array($data['EvnPrescr_setDate_Range']) && count($data['EvnPrescr_setDate_Range']) == 2 && !empty($data['EvnPrescr_setDate_Range'][0]) && !empty($data['EvnPrescr_setDate_Range'][1]) ) {
			$compareResult = swCompareDates($data['EvnPrescr_setDate_Range'][0], $data['EvnPrescr_setDate_Range'][1]);
			if ( $compareResult[0] == -1 ) {
				throw new Exception('Дата окончания курса больше даты начала', 400);
			}
			else if ( $compareResult[0] == 100 ) {
				throw new Exception('Неверный формат дат продолжительности курса', 400);
			}
			$start = DateTime::createFromFormat("Y-m-d", $data['EvnPrescr_setDate_Range'][0]);
			$end = DateTime::createFromFormat("Y-m-d", $data['EvnPrescr_setDate_Range'][1]);
			$data['EvnPrescr_dayNum'] = $start->diff($end)->days+1;
		}

		if (empty($data['EvnPrescr_setDate'])) {
			$start = DateTime::createFromFormat("Y-m-d", date('Y-m-d'));
		} else {
			$start = DateTime::createFromFormat("Y-m-d", $data['EvnPrescr_setDate']);
			if ( !$start ) {
				throw new Exception('Неверный формат даты начала курса', 400);
			}
		}
		if (empty($data['EvnPrescr_dayNum'])) {
			$data['EvnPrescr_dayNum'] = 1;
		}

		$dateList[] = $start->format('Y-m-d');
		if ($data['EvnPrescr_dayNum'] > 1) {
			$interval = new DateInterval("P1D");
			$day_cnt = 1;
			while ($data['EvnPrescr_dayNum'] != $day_cnt) {
				$dateList[] = $start->add($interval)->format('Y-m-d');
				$day_cnt++;
			}
		}
		return $dateList;
	}

	/**
	 * Удаление записи назначения
	 */
	protected function _destroy($data) {
		if (empty($data['object']) || empty($data['id'])) {
			throw new Exception('Отсутствует ключ');
		}
		$object = $data['object'];
		$pmuser = '';
		if(isset($data['pmUser_id']))
		{
			$pmuser = '@pmUser_id = :pmUser_id,';
			$row['pmUser_id'] = $data['pmUser_id'];
		}
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_{$object}_del
				@{$object}_id = :id,
				{$pmuser}
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, $data);
		if ( !is_object($result) ) {
			throw new Exception('Ошибка при удалении записи назначения');
		}
		$response = $result->result('array');
		if ( !empty($response[0]['Error_Msg']) )
		{
			throw new Exception($response[0]['Error_Msg']);
		}
		return true;
	}

	/**
	 * Сохранение назначения без календаря, графиков и других дочерних объектов в EvnPrescr
	 */
	protected function _save($data = array()) {
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :EvnPrescr_id;

			exec p_EvnPrescr_" . (!empty($data['EvnPrescr_id']) && $data['EvnPrescr_id'] > 0 ? "upd" : "ins") . "
				@EvnPrescr_id = @Res output,
				@EvnPrescr_pid = :EvnPrescr_pid,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@PrescriptionType_id = :PrescriptionType_id,
				@PrescriptionStatusType_id = :PrescriptionStatusType_id,
				@EvnPrescr_IsCito = :EvnPrescr_IsCito,
				@EvnPrescr_Descr = :EvnPrescr_Descr,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnPrescr_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'EvnPrescr_id' => (!empty($data['EvnPrescr_id']) && $data['EvnPrescr_id'] > 0 ? $data['EvnPrescr_id'] : NULL),
			'EvnPrescr_pid' => $data['EvnPrescr_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'PrescriptionType_id' => $this->getPrescriptionTypeId(),
			'PrescriptionStatusType_id' => (!empty($data['PrescriptionStatusType_id']) ? $data['PrescriptionStatusType_id'] : 1),
			'EvnPrescr_Descr' => (!empty($data['EvnPrescr_Descr']) ? $data['EvnPrescr_Descr'] : NULL),
			'EvnPrescr_IsCito' => (!empty($data['EvnPrescr_IsCito']) ? $data['EvnPrescr_IsCito'] : 1),
			'pmUser_id' => $data['pmUser_id'],
		);

		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');
			if ( !empty($response[0]['Error_Msg']) ) {
				throw new Exception($response[0]['Error_Msg'], 500);
			}
			return $response[0]['EvnPrescr_id'];
		}
		else {
			throw new Exception('Ошибка при запросе к БД при сохранении назначения', 500);
		}
	}
}
