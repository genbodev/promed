<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ReportRun - контроллер для формирования отчётов по новой концепции
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Report
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
 */

class ReportRun extends swController
{
	/**
	 * Сколько всего может выполняться процессов одновременно, не больше
	 */
	private $maxTasks = 25;
	/**
	 * Сколько всего может выполняться отчетов из очереди одновременно, не больше
	 */
	private $maxQueueTasks = 5;
	/**
	 * Используется ли очередь отчетов в принципе
	 */
	private $useReportQueue = false;
	/**
	 * construct
	 */
	function __construct()
	{
		parent::__construct();
		
		$maxTasks = $this->config->item('MaxTaskReportsInTime');
		if (!empty($maxTasks))
			$this->maxTasks = $maxTasks;
		$maxQueueTasks = $this->config->item('MaxTaskReportsQueueInTime');
		if (!empty($maxQueueTasks))
			$this->maxQueueTasks = $maxQueueTasks;
		$useReportQueue = $this->config->item('useReportQueue');
		if (!empty($useReportQueue))
			$this->useReportQueue = $useReportQueue;
		
		$this->inputRules = array(
			'Run' => array(
				array(
					'field' => 'Report_id',
					'label' => 'Идентификатор отчёта',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Report_Params',
					'label' => 'Параметры отчёта',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => '__format',
					'label' => 'Формат отчёта',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Report_ParamsArr',
					'label' => 'параметры отчета в текстовом варианте для сообщения',
					'rules' => '',
					'type' => 'string'
				)
			),
			'RunQueue' => array(
				array(
					'field' => 'ReportRun_id',
					'label' => 'Идентификатор отчёта в очереди',
					'rules' => '',
					'type' => 'id'
				)
			),
			'CheckIfReportInQueue' => array(
				array(
					'field' => 'Report_id',
					'label'	=> 'Идентификатор отчета',
					'rules'	=> 'required',
					'type'	=> 'string'
				),
				array(
					'field' => 'ReportParams',
					'label' => 'Параметры отчёта',
					'rules' => '',
					'type' => 'string'
				)
			),
			'RunByFileName' => array(
				array(
					'field' => 'Report_FileName',
					'label' => 'Наименование файла отчёта',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Report_Params',
					'label' => 'Параметры отчёта',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Report_Format',
					'label' => 'Формат отчёта',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'isDebug',
					'label' => 'Дебаг',
					'rules' => '',
					'type' => 'int'
				)
			),
			'Get' => array(
				array(
					'field' => 'ReportRun_Code',
					'label' => 'Код отчёта',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadReportTree' => array(
				array('field' => 'level', 'label' => 'Уровень', 'rules' => '', 'type' => 'int', 'default'=>0),
				array('field' => 'node', 'label' => 'Ветка', 'rules' => '', 'type' => 'string')
			),
			'loadReportGrid' => array(
				array('default' => 0,'field' => 'start','label' => 'Начальный номер записи','rules' => 'trim','type' => 'int'),
				array('default' => 100,'field' => 'limit','label' => 'Количество возвращаемых записей','rules' => 'trim','type' => 'int'),
				array('default' => 0,'field' => 'isqueue','label' => 'Очередь','rules' => '','type' => 'checkbox'),
				array('field' => 'filterReportName','label' => 'Наименование','rules' => '','type' => 'string'),
				array('field' => 'filterReportPeriod','label' => 'Период','rules' => '','type' => 'daterange'),
				array('field' => 'filterReportStatus','label' => 'Статус','rules' => '','type' => 'int'),
				array('field' => 'filterReportSign','label' => 'Наличие ЭП','rules' => '','type' => 'id')

			),
			'deleteReportRuns' => array(
				array(
                    'field' => 'reportRuns_array',
                    'label' => 'Массив идетнификаторов очереди отчетов',
                    'rules' => 'required',
                    'type'  => 'string'
                )
			)
		);
    }
	
	/**
	 * Возвращает длительность в днях диапазона дат, за которые делается отчет
	 */
	function GetReportDateRange($data) {
		parse_str($data['Report_Params'], $parts);
		// надо разбираться как определить отчет на один день или на период от начала года ло указанной даты
		/*
		if (empty($parts['paramBegDate']) && empty($parts['paramEndDate']) && !empty($parts['paramDate']) ){
			//когда не указана дата начала и окончания, отчет формируется с начала года (указанной даты) до указанной даты.
			$parts['paramBegDate'] = date('01-01-Y');
			$parts['paramEndDate'] = $parts['paramDate'];
		}
		 * *
		 */
		if (isset($parts['paramBegDate']) && isset($parts['paramEndDate']) ) {
			
			$datetime1 = date_create($parts['paramBegDate']);
			$datetime2 = date_create($parts['paramEndDate']);
			$interval = date_diff($datetime1, $datetime2);
			return ($interval->format('%a') + 1);
		} else {
			return 0;
		}
	}

	/**
	 * Запуск отчёта по имени файла отчёта
	 */
	function RunByFileName()
	{
		$this->load->model('ReportRun_model', 'dbmodel');

		$data = $this->ProcessInputData('RunByFileName', true);
		if ($data === false) { return false; }
		if(strtolower($data["Report_Format"]) == 'doc') //https://redmine.swan.perm.ru/issues/54256 - если с формы передается doc, то проверяем настройки - если там odt,
														//то заменяем соответствующий параметр на него
		{
			$this->load->helper('Options');
			$options = getOptions();
			if (is_array($options['print'])
				&& isset($options['print']['file_format_type'])
				&& $options['print']['file_format_type'] == 2
			)
			{
				$data["Report_Format"] = 'odt';
			}
		}
		$response = $this->dbmodel->RunByFileName($data);
		echo $response;

		return true;
	}
	
	/**
	 * Запуск нового отчёта
	 */
	function Run()
	{
		$this->load->database( 'reports' );
		$this->load->model('ReportRun_model', 'model');
		$_POST = $_GET;
		$data = $this->ProcessInputData('Run', true, true);
		$data['Report_Params'] = urldecode($data['Report_Params']);
		$check_queue_data = $data;
		$res = $this->model->CheckIfUserCanRunReport($data['Report_id'], $data['pmUser_id']);
		$Report_ParamsArr = array();
		if ( $res === true )
		{
			$DatabaseType = $this->model->getDatabaseTypeByReport($data['Report_id']);
			if ( isset($data['__format']) ) {
				$data['Report_Params'] .= "&__format=".$data['__format'];
			}
			$Report_Code = $this->model->CheckIfReportInCache($data['Report_id'], $data['pmUser_id'], $data['Report_Params']);
			//$Report_Code = false;
			if ( $Report_Code !== false ) {
				//Всячески радуемся и берём отчёт из кэша
				echo $this->model->GetReportData(array('ReportRun_Code' => $Report_Code));
			
			} else {
				// получаем количество процессов выполняемых на текущий момент
				$WaitingProcesses = $this->model->GetWaitingProcessCount($DatabaseType);

				if(isset($_POST['reportQueue'])){ //https://redmine.swan.perm.ru/issues/95436
					if ( isSuperAdmin() ) {
						if ($WaitingProcesses !== false) {
							if ($WaitingProcesses > $this->maxTasks) {
								echo "Извините, сервер отчетности перегружен, отчет не может быть поставлен сейчас на формирование.<br/>
								Попробуйте подождать некоторое время и нажать на ссылку ниже еще раз для новой попытки формирования отчета.";
								return;
							}
						}

						$delReportRun = $this->model->deleteReportRuns($_POST['reportQueue']);
						$this->model->Add(
							$data['Report_id'],
							$data['Report_Params'],
							$data['pmUser_id']
						);
						echo $this->model->RunReport(true,false);
						if (isset($this->model->Report_FileName)) {
							// если сформирован отчёт с путём файла, посылаем сообщение
							$this->SendReportFinishMessage($this->model);
						}
						return;
					}
					else {
						$delReportRun = $this->model->deleteReportRuns($_POST['reportQueue']);
						return;
					}
				}
				$add_to_queue = false;
				$IsSMPServer = $this->config->item('IsSMPServer');
				if(in_array($data['session']['region']['nick'],array('ufa', 'perm')) && $IsSMPServer == false){
					//Проверим, а не входит ли наш отчет в rpt.ReportLong (таблица с заведомо тяжелыми отчетами, которые надо кидать в очередь)
					$add_to_queue = $this->model->CheckReportForLong($data['Report_id']);
					$check_queue_data['ReportParamsLen'] = mb_strlen($check_queue_data['Report_Params']);
					$dtExistReport = $this->model->checkReportQueue($check_queue_data);
				}
				if ($add_to_queue) {
					if ($dtExistReport) {
						echo "<h2>Отчет с указанными параметрами уже находится в очереди формирования реестров c " . $dtExistReport . ".</h2>
								Дождитесь формирования ранее поставленного в очередь отчета. <br/>
								Вы будете информированы об окончании формирования через функционал личных сообщений.<br/>
								<br/>";
						return;
					}
					else
					{
						$this->model->AddQueue(
								$data['Report_id'],
								$data['Report_Params'],
								$data['pmUser_id']
						);
						return $this->load->view('report_queue', array('range' => $this->GetReportDateRange($data)));
					}
				}
				else
				{
					$runNow = false;
					if (!$this->useReportQueue) {
						//Старый вариант реализации, без очереди
						if ($WaitingProcesses !== false) {
							if ($WaitingProcesses > $this->maxTasks) {
								echo "Извините, сервер отчетности перегружен, отчет не может быть поставлен сейчас на формирование.<br/>
								Попробуйте подождать некоторое время и нажать на ссылку ниже еще раз для новой попытки формирования отчета.<br/>
								<a href='{$_SERVER['REQUEST_URI']}'>Попробовать сформировать отчет еще раз.</a><br/>" . $this->model->GetCurrentLoadText();
								return;
							}
						}
						if ($this->GetReportDateRange($data) > 100 && !isset($_GET['RangeOverride']) && $data['session']['region']['nick'] != 'khak' && $IsSMPServer == false) {
							return $this->load->view('report_longrange', array('range' => $this->GetReportDateRange($data), 'region' => $data['session']['region']['nick']));
						} else {
							// можно запустить и без ожидания
							$runNow = true;
						}
					} else {
						// Если количество процессов (исключая возможные для очереди) больше максимально возможного или количество дней запроса больше 100 (и это не хакасия), то добавляем в очередь, иначе выполняем отчет тут же
						if (($WaitingProcesses !== false && ($WaitingProcesses - $this->maxQueueTasks) > $this->maxTasks) ||
								($this->GetReportDateRange($data) > 100 && !isset($_GET['RangeOverride']) && $data['session']['region']['nick'] != 'khak')
						) {

							// Проверим нет ли похожего отчета в очереди от данного пользователя, по строке параметров, Report_id и pmUser_id
							$check_queue_data['ReportParamsLen'] = mb_strlen($check_queue_data['Report_Params']);
							$dtExistReport = $this->model->checkReportQueue($check_queue_data);
							if ($dtExistReport) {
								echo "<h2>Отчет с указанными параметрами уже находится в очереди формирования реестров c " . $dtExistReport . ".</h2>
								Дождитесь формирования ранее поставленного в очередь отчета. <br/>
								Вы будете информированы об окончании формирования через функционал личных сообщений.<br/>
								<br/>";
								return;
							} else {
								// Добавим в очередь и выведем окно
								$this->model->AddQueue(
										$data['Report_id'],
										$data['Report_Params'],
										$data['pmUser_id']
								);
								return $this->load->view('report_queue', array('range' => $this->GetReportDateRange($data)));
							}
						} else {
							// можно запустить и без помещения в очередь
							$runNow = true;
						}
					}
					if ($runNow) {
						if(!empty($data['Report_ParamsArr'])) {
							$Report_ParamsArr = explode("|", $data['Report_ParamsArr']);
						}
						if( !is_array($Report_ParamsArr) ) $Report_ParamsArr = false;
						$this->model->Add(
								$data['Report_id'],
								$data['Report_Params'],
								$data['pmUser_id'],
								$Report_ParamsArr
						);
						echo $this->model->RunReport(true,false);
						if (isset($this->model->Report_FileName)) {
							// если сформирован отчёт с путём файла, посылаем сообщение
							$this->SendReportFinishMessage($this->model);
						}elseif(isset($this->model->stoppedOnTimeout)){
							// если отчёт не сформировался за установленное время
							$txt = 'Отчет '.$this->model->Report_Name.' не сформировался в течение получаса и с теми же параметрами поставлен в очередь на формирование. Информацию см. во вкладке очередь. '.$this->model->stoppedOnTimeout;
							$title = "Отчёт {$this->model->Report_Name} поставлен в очередь.";
							$this->SendReportFinishMessage($this->model, $txt, $title);
						}
					}
				}
			}
		} else {
			echo $res;
		}
	}
	
	
	/**
	 * Запуск формирования отчета из очереди
	 */
	function RunQueue()
	{
		set_time_limit(0);
		$this->load->database( 'reports' );

		$data = $this->ProcessInputData('RunQueue', true);
		if ($data === false) { return false; }

		$this->load->model('ReportRun_model', 'model');
		$this->load->library('textlog', array('file'=>'ReportRunQueue_'.date('Y-m-d').'.log', 'rewrite'=>false));
        $this->textlog->add("\r\n"."Запускаем задание.\r\nНастройки: всего можно запустить ".$this->maxTasks." процессов, из очереди: ".$this->maxQueueTasks);

		$report = $this->model->getReportQueue($data); // получаем первый отчет из очереди для запуска
		if (!is_array($report) || count($report) == 0) {
			$this->textlog->add("Очередь отчетов пуста.");
			return true;
		}
		// есть данные в очереди
		$this->textlog->add("Получили первый отчет из очереди для запуска: ".var_export($report, true));
		// Устанавливаем параметры для дальнейшего выполнения запроса 
		$this->model->ParamsQueue($report);

		$DatabaseType = $this->model->getDatabaseTypeByReport();

		// Всего количество запущенных процессов
		$countAll = $this->model->GetWaitingProcessCount($DatabaseType);
        $this->textlog->add("\r\n"."Количество запущенных процессов: ".$countAll);
		if ($countAll < $this->maxTasks) { // Если количество выполняемых запросов всего меньше максимально возможного
			if ($countAll>$this->maxQueueTasks) { // чтобы не делать лишний запрос к БД, если процессов в БД меньше максимально возможного количества отчетов из очереди
				$countQueue = $this->model->countTaskReportQueue(); // Тут получаем количество запросов в очереди 
			} else {
				$countQueue = $countAll; // считаем что можно запускать отчет из очереди
			}
			
			if ($countQueue<$this->maxQueueTasks) { // Если количество выполняемых запросов из очереди меньше максимально возможного, то можно работать дальше 
				$this->textlog->add("Текущие данные: запущено ".$countAll." процессов, из очереди: ".$countQueue);
				// Отдаем бирту, ждем ответа, сохраняем файл, сообщаем 
				echo $this->model->RunReport(false,true);
				if (isset($this->model->Report_FileName) ) {
					// если сформирован отчёт с путём файла, посылаем сообщение
					$this->SendReportFinishMessage($this->model);
					$this->textlog->add("Успешно выполнили и отправили сообщение о выполнении отчета из очереди.");
					return true;
				}
			}
		}
		// данное сообщение будет записываться в лог, в случае, если отчет по какой то причине не сформировался
		$this->textlog->add(
			"Ошибка запуска формирования отчета из очереди:\r\n".
			"Текущие данные: запущено ".(isset($countAll)?$countAll:'X')." процессов, из очереди: ".(isset($countQueue)?$countQueue:'X')."\r\n".
			"Данные отчета: ".((isset($report) && is_array($report) && count($report)>0)?var_export($report, true):"[не известны]")."\r\n".
			"Имя файла: ".((isset($this->model->Report_FileName))?$this->model->Report_FileName:"[не известно]")
		);
	}
	
	
	/**
	 * Получение старого отчёта
	 */
	function Get()
	{
		$this->load->database( 'reports' );
		$this->load->model('ReportRun_model', 'model');
		
		$_POST = $_GET;
		$data = $this->ProcessInputData('Get', true, true);

		echo $this->model->GetReportData($data);
	}
	
	/**
	 * Отправка сообщения об том, что отчёт завершился
	 */
	function SendReportFinishMessage($model, $txtMessage = false, $titleMessage = false) {
		$this->db = null;
		$this->load->database();
		$this->load->model("Messages_model", "messages");
		$data['text'] = ($txtMessage) ? $txtMessage : "Отчёт <b>{$model->Report_Name}</b>, запущенный ".date("d.m.Y H:i", $model->ReportRun_BegTime).", завершился.<br/>
		Результаты выполнения доступны по ссылке <a href='/?c=ReportRun&m=Get&ReportRun_Code={$model->ReportRun_Code}' target='_blank'>Загрузка сформированного отчёта</a>";
		$data['type'] = 2;
		$data['autotype'] = 1;
		$data['title'] = ($titleMessage) ? $titleMessage : "Отчёт {$model->Report_Name}. Формирование завершено.";
		$data['User_rid'] = $model->pmUser_id;
		$data['pmUser_id'] = $model->pmUser_id;
		$result = $this->messages->autoMessage($data);
	}
	/**
	 * Дерево для формы очереди и истории
	 */
	function loadReportTree() {
		$this->load->database( 'reports' );
		$this->load->model('ReportRun_model', 'model');
		
		$data = $this->ProcessInputData('loadReportTree', true);
		if ($data === false) { return false; }
		
		$response = $this->model->loadReportTree($data);
		$this->ProcessModelList($response, true,true)->ReturnData();

	}
	
	/**
	 * Грид для формы очереди и истории
	 */
	function loadReportGrid() {
		$this->load->database( 'reports' );
		$this->load->model('ReportRun_model', 'model');
		
		$data = $this->ProcessInputData('loadReportGrid', true);
		if ($data === false) { return false; }
		
		$response = $this->model->loadReportGrid($data);
		foreach ($response as &$rows) {
			if (isset($rows['Report_Params'])) {
				parse_str($rows['Report_Params'], $parts);
				//print_r( $parts);
				if (isset($parts['paramBegDate'])) {
					$rows['Report_paramBegDate'] = $parts['paramBegDate'];
				}
				if (isset($parts['paramEndDate'])) {
					$rows['Report_paramEndDate'] = $parts['paramEndDate'];
				}
				if (isset($parts['paramLpu'])) {
					$rows['Lpu_id'] = $parts['paramLpu'];
				}
			}
		}
		$this->ProcessModelMultiList($response, true,true)->ReturnData();

	}

	/**
	*	Удаление отчетов из очереди
	*/
	function deleteReportRuns(){
		$data = $this->ProcessInputData('deleteReportRuns',true);
		if($data===false)
			return false;
		$this->load->database( 'reports' );
		$this->load->model('ReportRun_model', 'model');
		$report_runs_array = $dt = (array) json_decode($data['reportRuns_array']);
		for ($i=0; $i<count($report_runs_array); $i++)
		{
			$ReportRun_id = $report_runs_array[$i];
			//Проверим, можно ли удалять этот ReportRun - он должен иметь ReportRun_queueDT, не иметь ReportRun_begDT и иметь пустой ReportRun_Status
			$canDeleteReportRun = $this->model->CheckIfReportRunCanBeDeleted($ReportRun_id);
			if($canDeleteReportRun)
			{
				$response = $this->model->deleteReportRuns($ReportRun_id);
			}
		}
		$this->ProcessModelSave(array('success' => true), true, 'При удалении возникли ошибки')->ReturnData();
		return true;
	}
	
		}
?>