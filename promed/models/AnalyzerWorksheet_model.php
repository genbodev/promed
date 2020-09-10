<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * TODO: complete explanation, preamble and describing
 * Модель для объектов Рабочий список
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2010-2011 Swan Ltd.
 * @author       gabdushev
 * @version
 *
 * @property AnalyzerWorksheetEvnLabSample_model AnalyzerWorksheetEvnLabSample_model
 */
class AnalyzerWorksheet_model extends swModel {
	private $AnalyzerWorksheet_id;//Идентификатор
	private $AnalyzerWorksheet_Code;//Код
	private $AnalyzerWorksheet_Name;//Наименование
	private $AnalyzerWorksheet_setDT;//Дата создания
	private $AnalyzerRack_id;//Штатив
	private $AnalyzerWorksheetStatusType_id;//Статус рабочего списка
	private $AnalyzerWorksheetType_id;//Тип рабочих списков
	private $Analyzer_id;//Анализатор
	private $pmUser_id;//Идентификатор пользователя системы Промед

	/**
	 * TO-DO описать
	 */
	public function getAnalyzerWorksheet_id() { return $this->AnalyzerWorksheet_id;}
	/**
	 * TO-DO описать
	 */
	public function setAnalyzerWorksheet_id($value) { $this->AnalyzerWorksheet_id = $value; }

	/**
	 * TO-DO описать
	 */
	public function getAnalyzerWorksheet_Code() { return $this->AnalyzerWorksheet_Code;}
	/**
	 * TO-DO описать
	 */
	public function setAnalyzerWorksheet_Code($value) { $this->AnalyzerWorksheet_Code = $value; }

	/**
	 * TO-DO описать
	 */
	public function getAnalyzerWorksheet_Name() { return $this->AnalyzerWorksheet_Name;}
	/**
	 * TO-DO описать
	 */
	public function setAnalyzerWorksheet_Name($value) { $this->AnalyzerWorksheet_Name = $value; }

	/**
	 * TO-DO описать
	 */
	public function getAnalyzerWorksheet_setDT() { return $this->AnalyzerWorksheet_setDT;}
	/**
	 * TO-DO описать
	 */
	public function setAnalyzerWorksheet_setDT($value) { $this->AnalyzerWorksheet_setDT = $value; }

	/**
	 * TO-DO описать
	 */
	public function getAnalyzerRack_id() { return $this->AnalyzerRack_id;}
	/**
	 * TO-DO описать
	 */
	public function setAnalyzerRack_id($value) { $this->AnalyzerRack_id = $value; }

	/**
	 * TO-DO описать
	 */
	public function getAnalyzerWorksheetStatusType_id() { return $this->AnalyzerWorksheetStatusType_id;}
	/**
	 * TO-DO описать
	 */
	public function setAnalyzerWorksheetStatusType_id($value) { $this->AnalyzerWorksheetStatusType_id = $value; }

	/**
	 * TO-DO описать
	 */
	public function getAnalyzerWorksheetType_id() { return $this->AnalyzerWorksheetType_id;}
	/**
	 * TO-DO описать
	 */
	public function setAnalyzerWorksheetType_id($value) { $this->AnalyzerWorksheetType_id = $value; }

	/**
	 * TO-DO описать
	 */
	public function getAnalyzer_id() { return $this->Analyzer_id;}
	/**
	 * TO-DO описать
	 */
	public function setAnalyzer_id($value) { $this->Analyzer_id = $value; }

	/**
	 * TO-DO описать
	 */
	public function getpmUser_id() { return $this->pmUser_id;}
	/**
	 * TO-DO описать
	 */
	public function setpmUser_id($value) { $this->pmUser_id = $value; }

	/**
	 * Конструктор
	 */
	function __construct(){
		if (isset($_SESSION['pmuser_id'])) {
			$this->setpmUser_id($_SESSION['pmuser_id']);
		} else {
			throw new Exception('Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)');
		}
	}

	/**
	 * Загрузка
	 */
	function load() {
		$q = "
			select
				AnalyzerWorksheet_id, AnalyzerWorksheet_Code, AnalyzerWorksheet_Name, AnalyzerWorksheet_setDT, AnalyzerRack_id, AnalyzerWorksheetStatusType_id, AnalyzerWorksheetType_id, Analyzer_id
			from
				lis.v_AnalyzerWorksheet with (nolock)
			where
				AnalyzerWorksheet_id = :AnalyzerWorksheet_id
		";
		$r = $this->db->query($q, array('AnalyzerWorksheet_id' => $this->AnalyzerWorksheet_id));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$this->AnalyzerWorksheet_id = $r[0]['AnalyzerWorksheet_id'];
				$this->AnalyzerWorksheet_Code = $r[0]['AnalyzerWorksheet_Code'];
				$this->AnalyzerWorksheet_Name = $r[0]['AnalyzerWorksheet_Name'];
				$this->AnalyzerWorksheet_setDT = $r[0]['AnalyzerWorksheet_setDT'];
				$this->AnalyzerRack_id = $r[0]['AnalyzerRack_id'];
				$this->AnalyzerWorksheetStatusType_id = $r[0]['AnalyzerWorksheetStatusType_id'];
				$this->AnalyzerWorksheetType_id = $r[0]['AnalyzerWorksheetType_id'];
				$this->Analyzer_id = $r[0]['Analyzer_id'];
				return $r;
			} else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadList($filter) {
		$where = array();
		$p = array();
		if (isset($filter['AnalyzerWorksheet_id']) && $filter['AnalyzerWorksheet_id']) {
			$where[] = 'AnalyzerWorksheet.AnalyzerWorksheet_id = :AnalyzerWorksheet_id';
			$p['AnalyzerWorksheet_id'] = $filter['AnalyzerWorksheet_id'];
		}
		if (isset($filter['AnalyzerWorksheet_Code']) && $filter['AnalyzerWorksheet_Code']) {
			$where[] = 'AnalyzerWorksheet.AnalyzerWorksheet_Code = :AnalyzerWorksheet_Code';
			$p['AnalyzerWorksheet_Code'] = $filter['AnalyzerWorksheet_Code'];
		}
		if (isset($filter['AnalyzerWorksheet_Name']) && $filter['AnalyzerWorksheet_Name']) {
			$where[] = 'AnalyzerWorksheet.AnalyzerWorksheet_Name = :AnalyzerWorksheet_Name';
			$p['AnalyzerWorksheet_Name'] = $filter['AnalyzerWorksheet_Name'];
		}
		if (isset($filter['AnalyzerWorksheet_setDT']) && $filter['AnalyzerWorksheet_setDT']) {
			$where[] = 'AnalyzerWorksheet.AnalyzerWorksheet_setDT = :AnalyzerWorksheet_setDT';
			$p['AnalyzerWorksheet_setDT'] = $filter['AnalyzerWorksheet_setDT'];
		}
		if (isset($filter['AnalyzerRack_id']) && $filter['AnalyzerRack_id']) {
			$where[] = 'AnalyzerWorksheet.AnalyzerRack_id = :AnalyzerRack_id';
			$p['AnalyzerRack_id'] = $filter['AnalyzerRack_id'];
		}
		if (isset($filter['AnalyzerWorksheetStatusType_id']) && $filter['AnalyzerWorksheetStatusType_id']) {
			$where[] = 'AnalyzerWorksheet.AnalyzerWorksheetStatusType_id = :AnalyzerWorksheetStatusType_id';
			$p['AnalyzerWorksheetStatusType_id'] = $filter['AnalyzerWorksheetStatusType_id'];
		}
		if (isset($filter['AnalyzerWorksheetType_id']) && $filter['AnalyzerWorksheetType_id']) {
			$where[] = 'AnalyzerWorksheet.AnalyzerWorksheetType_id = :AnalyzerWorksheetType_id';
			$p['AnalyzerWorksheetType_id'] = $filter['AnalyzerWorksheetType_id'];
		}
		if (isset($filter['Analyzer_id']) && $filter['Analyzer_id']) {
			$where[] = 'AnalyzerWorksheet.Analyzer_id = :Analyzer_id';
			$p['Analyzer_id'] = $filter['Analyzer_id'];
		}
		if (isset($filter['MedService_id']) && $filter['MedService_id']) {
			$where[] = 'Analyzer_id_ref.MedService_id = :MedService_id';
			$p['MedService_id'] = $filter['MedService_id'];
		}
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}
		$q = "
			SELECT
				AnalyzerWorksheet.AnalyzerWorksheet_id,
				AnalyzerWorksheet.AnalyzerWorksheet_Code,
				AnalyzerWorksheet.AnalyzerWorksheet_Name,
				convert(varchar, AnalyzerWorksheet.AnalyzerWorksheet_setDT, 104) as AnalyzerWorksheet_setDT,
				AnalyzerWorksheet.AnalyzerRack_id,
				AnalyzerRack_id_ref.AnalyzerRack_DimensionX,
				AnalyzerRack_id_ref.AnalyzerRack_DimensionY,
				AnalyzerWorksheet.AnalyzerWorksheetStatusType_id,
				AnalyzerWorksheet.AnalyzerWorksheetType_id,
				AnalyzerWorksheet.Analyzer_id,
				AnalyzerWorksheetStatusType_id_ref.AnalyzerWorksheetStatusType_Name as AnalyzerWorksheetStatusType_Name,
				AnalyzerWorksheetType_id_ref.AnalyzerWorksheetType_Name as AnalyzerWorksheetType_Name,
				Analyzer_id_ref.Analyzer_Name Analyzer_id_Name
			FROM
				lis.v_AnalyzerWorksheet AnalyzerWorksheet  WITH (NOLOCK)
				LEFT JOIN lis.v_AnalyzerRack AnalyzerRack_id_ref WITH (NOLOCK) ON AnalyzerRack_id_ref.AnalyzerRack_id = AnalyzerWorksheet.AnalyzerRack_id
				LEFT JOIN lis.v_AnalyzerWorksheetStatusType AnalyzerWorksheetStatusType_id_ref WITH (NOLOCK) ON AnalyzerWorksheetStatusType_id_ref.AnalyzerWorksheetStatusType_id = AnalyzerWorksheet.AnalyzerWorksheetStatusType_id
				LEFT JOIN lis.v_AnalyzerWorksheetType AnalyzerWorksheetType_id_ref WITH (NOLOCK) ON AnalyzerWorksheetType_id_ref.AnalyzerWorksheetType_id = AnalyzerWorksheet.AnalyzerWorksheetType_id
				LEFT JOIN lis.v_Analyzer Analyzer_id_ref WITH (NOLOCK) ON Analyzer_id_ref.Analyzer_id = AnalyzerWorksheet.Analyzer_id
			$where_clause
		";
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохранение
	 */
	function save() {
		$procedure = 'p_AnalyzerWorksheet_ins';
		if ( $this->AnalyzerWorksheet_id > 0 ) {
			$procedure = 'p_AnalyzerWorksheet_upd';
		}
		$q = "
			declare
				@AnalyzerWorksheet_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@AnalyzerWorksheet_setDT Datetime;
			set @AnalyzerWorksheet_id = :AnalyzerWorksheet_id;
			set @AnalyzerWorksheet_setDT = getDate();
			exec lis." . $procedure . "
				@AnalyzerWorksheet_id = @AnalyzerWorksheet_id output,
				@AnalyzerWorksheet_Code = :AnalyzerWorksheet_Code,
				@AnalyzerWorksheet_Name = :AnalyzerWorksheet_Name,
				@AnalyzerWorksheet_setDT = @AnalyzerWorksheet_setDT,
				@AnalyzerRack_id = :AnalyzerRack_id,
				@AnalyzerWorksheetStatusType_id = :AnalyzerWorksheetStatusType_id,
				@AnalyzerWorksheetType_id = :AnalyzerWorksheetType_id,
				@Analyzer_id = :Analyzer_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @AnalyzerWorksheet_id as AnalyzerWorksheet_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'AnalyzerWorksheet_id' => $this->AnalyzerWorksheet_id,
			'AnalyzerWorksheet_Code' => $this->AnalyzerWorksheet_Code,
			'AnalyzerWorksheet_Name' => $this->AnalyzerWorksheet_Name,
			//'AnalyzerWorksheet_setDT' => $this->AnalyzerWorksheet_setDT,
			'AnalyzerRack_id' => $this->AnalyzerRack_id,
			'AnalyzerWorksheetStatusType_id' => $this->AnalyzerWorksheetStatusType_id,
			'AnalyzerWorksheetType_id' => $this->AnalyzerWorksheetType_id,
			'Analyzer_id' => $this->Analyzer_id,
			'pmUser_id' => $this->pmUser_id,
		);
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
			$result = $r->result('array');
			$this->AnalyzerWorksheet_id = $result[0]['AnalyzerWorksheet_id'];
		}
		else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * Установка статуса
	 */
	function setStatus($data) {
		$this->load->model('Lis_model', 'Lis_model');
		
		$this->load(); // todo: надо убрать пересохранение, сделать прсото апдейт нужных полей 	
		
		$response = $this->Lis_model->worklistChangeState($data);
		if ($response) {
			$this->setAnalyzerWorksheetStatusType_id($data['AnalyzerWorksheetStatusType_id']);
			return $this->save(); // todo: надо убрать пересохранение, сделать прсото апдейт нужных полей 	
		} else {
			return false;
		}
	}

	/**
	 * В работу
	 */
	function work($data) {
		$this->load();
		// предварительно сохраняем заявки в ЛИС, выбрав их их сохраненного рабочего списка
		$this->load->model('AnalyzerWorksheetEvnLabSample_model', 'AnalyzerWorksheetEvnLabSample_model');
		$this->load->model('Lis_model', 'Lis_model');
		$samples = $this->AnalyzerWorksheetEvnLabSample_model->loadEvnLabSampleWithAnalyzerWorksheet($data);
		if (is_array($samples) && (count($samples)>0)) { // если вообще есть список
			set_time_limit(0); // может потребоваться прилично времени
			foreach ($samples as $i=>$sample) {
				// Отправляем в ЛИС те, которые не были отправлены
				if (empty($sample['lis_id'])) {
					$data['EvnLabSample_id'] = $sample['EvnLabSample_id'];
					$response = $this->Lis_model->createRequest2($data);
					$samples[$i]['lis_id'] = (isset($response['lis_request_id']))?$response['lis_request_id']:null;
					// todo: по идее если заявку в ЛИС не смогли создать, надо об этом куда то сообщать или писать в лог 
				}
			}
			// $samples = $this->AnalyzerWorksheetEvnLabSample_model->loadEvnLabSampleWithAnalyzerWorksheet($data); // еще раз перечитываем список, если было добавление
		}
		// после того, как все отправили надо создать рабочий список в ЛИС
		$data['samples'] = $samples;
		$worklistId = $this->Lis_model->worklistSave($data); // создаем и отправляем рабочий список
		if (!empty($worklistId)) {
			$this->Lis_model->saveLink('AnalyzerWorksheet', $data['AnalyzerWorksheet_id'], $worklistId, $data); // сохраняем связь между рабочим списком в ПромедВеб и рабочим списком в ЛИС
			// меняем статус
			$this->setAnalyzerWorksheetStatusType_id(2);
			return $this->save(); // todo: надо убрать пересохранение, сделать проcто апдейт нужных полей
		}
	}

	/**
	 * Удаление
	 */
	function delete() {
		//проверяем статус
		$status = $this->getFirstResultFromQuery('SELECT AnalyzerWorksheetStatusType_id FROM lis.v_AnalyzerWorksheet with (nolock) WHERE AnalyzerWorksheet_id = :AnalyzerWorksheet_id', array('AnalyzerWorksheet_id' => $this->AnalyzerWorksheet_id));
		if ('1' !== $status) {
			throw new Exception('Удалять можено только те рабочие списки, которые имеют статус "Новые"');
		}
		//последовательно удаляем все пробы
		$samplesIndaShit = $this->db->query('SELECT AnalyzerWorksheetEvnLabSample_id FROM lis.v_AnalyzerWorksheetEvnLabSample with (nolock) WHERE AnalyzerWorksheet_id = :AnalyzerWorksheet_id', array('AnalyzerWorksheet_id' => $this->AnalyzerWorksheet_id));
		if ( is_object($samplesIndaShit) ) {
			$samplesIndaShit = $samplesIndaShit->result('array');
			$this->load->model('AnalyzerWorksheetEvnLabSample_model', 'AnalyzerWorksheetEvnLabSample_model');
			foreach ($samplesIndaShit as  $sample) {
				$this->AnalyzerWorksheetEvnLabSample_model->setAnalyzerWorksheetEvnLabSample_id($sample['AnalyzerWorksheetEvnLabSample_id']);
				if (!$this->AnalyzerWorksheetEvnLabSample_model->delete()) {
					throw new Exception("Ошибка при удалении связи {$sample['AnalyzerWorksheetEvnLabSample_id']} пробы и рабочего списка {$this->AnalyzerWorksheet_id}");
				}
			}
		}

		$q = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec lis.p_AnalyzerWorksheet_del
				@AnalyzerWorksheet_id = :AnalyzerWorksheet_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array(
			'AnalyzerWorksheet_id' => $this->AnalyzerWorksheet_id
		));
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение номера
	 */
	public function getDailyCount($data) {
		$q = <<<Q
			select
				count(*) as dailyCount
			from
				lis.v_AnalyzerWorksheet w (nolock)
			where
				:gendate = cast(w.AnalyzerWorksheet_setDT as date)
Q;
		$r = $this->db->query($q, $data);
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				return $r;
			} else {
				return false;
			}
		}
		else {
			return false;
		}
	}
}
