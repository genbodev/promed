<?php
/**
 * ReportRun - модель для работы с новой концепцией формирования отчётов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Report
 * @access			public
 * @copyright		Copyright (c) 2011 Swan Ltd.
 * @author			Ivan Petukhov aka Lich (ethereallich@gmail.com)
 */

class ReportRun_model extends swPgModel
{
	/**
	 * Время начала выполнения отчёта
	 */
	var $ReportRun_BegTime = NULL;

	/**
	 * Идентификатор отчёта
	 */
	var $ReportRun_id = NULL;
	
	/**
	 * Уникальный код отчёта
	 */
	var $ReportRun_Code = NULL;
	
	/**
	 * Идентификатор отчёта
	 */
	var $Report_id = NULL;
	
	/**
	 * Наименование отчёта
	 */
	var $Report_Name = NULL;
	
	/**
	 * Строка параметров
	 */
	var $Report_Params = NULL;
	
	
	/**
	 * Пользователь
	 */
	var $pmUser_id = null;
	
	
	/**
	 * Название выходного файла
	 */
	var $Report_FileName = NULL;
	
	/**
	 * Формат выходного файла отчёта
	 */
	var $ReportRun_Format = NULL;
	
	/**
	 * Признак постановки в очередь
	 */
	var $isqueue = false;
	
	/**
	 * Массив параметров
	 */
	var $Report_ParamsArr = array();
	
	/**
	 * Признак отчет отвалился по таймауту
	 */
	var $stoppedOnTimeout = false;
	
	/**
	 * Массив возможных HTTP статус кодов
	 */
	var $codesStatus = array(
		0=>'Domain Not Found',
		100=>'Continue',
		101=>'Switching Protocols',
		200=>'OK',
		201=>'Created',
		202=>'Accepted',
		203=>'Non-Authoritative Information',
		204=>'No Content',
		205=>'Reset Content',
		206=>'Partial Content',
		300=>'Multiple Choices',
		301=>'Moved Permanently',
		302=>'Found',
		303=>'See Other',
		304=>'Not Modified',
		305=>'Use Proxy',
		307=>'Temporary Redirect',
		400=>'Bad Request',
		401=>'Unauthorized',
		402=>'Payment Required',
		403=>'Forbidden',
		404=>'Not Found',
		405=>'Method Not Allowed',
		406=>'Not Acceptable',
		407=>'Proxy Authentication Required',
		408=>'Request Timeout',
		409=>'Conflict',
		410=>'Gone',
		411=>'Length Required',
		412=>'Precondition Failed',
		413=>'Request Entity Too Large',
		414=>'Request-URI Too Long',
		415=>'Unsupported Media Type',
		416=>'Requested Range Not Satisfiable',
		417=>'Expectation Failed',
		500=>'Internal Server Error',
		501=>'Not Implemented',
		502=>'Bad Gateway',
		503=>'Service Unavailable',
		504=>'Gateway Timeout',
		505=>'HTTP Version Not Supported'
	);
	
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Добавление нового случая формирования отчёта
	 */
	public function Add($Report_id = NULL, $Report_Params = NULL, $pmUser_id = NULL, $Report_ParamsArr = false)
	{
		if ( isset($Report_id) ) {
			$this->Report_id = $Report_id;
			$this->Report_Params = $Report_Params;
			$this->pmUser_id = $pmUser_id;
			$this->ReportRun_Code = $this->generateReportRunCode();
			$this->ReportRun_BegTime = time();
			$this->isqueue = false;
			$this->Report_ParamsArr = $Report_ParamsArr;
			$this->InsertNewReportRun();
		}
	}
	
	/**
	 * Заполнение параметров нового случая формирования из ранее сохраненной очереди
	 */
	public function ParamsQueue($data = array())
	{
		if ( isset($data['Report_id']) ) {
			$this->ReportRun_id = $data['ReportRun_id'];
			$this->Report_id = $data['Report_id'];
			$this->Report_Params = $data['Report_Params'];
			$this->pmUser_id = $data['pmUser_insID'];//$data['pmUser_id'];
			$this->ReportRun_Code = $data['ReportRun_Code'];
			$this->ReportRun_BegTime = time();
			$this->isqueue = true;
		}
	}
	
	/**
	 * Добавление нового случая формирования отчёта в очередь
	 */
	public function AddQueue($Report_id = NULL, $Report_Params = NULL, $pmUser_id = NULL)
	{
		if ( isset($Report_id) ) {
			$this->Report_id = $Report_id;
			$this->Report_Params = $Report_Params;
			$this->pmUser_id = $pmUser_id;
			$this->ReportRun_Code = $this->generateReportRunCode();
			$this->ReportRun_BegTime = time();
			$this->isqueue = true;
			$this->InsertNewReportRun();
		}
	}
	
	/**
	 * Получение URL'а отчёта
	 */
	private function getReportUrl($relative = false, $fromQueue = false) {
		
		
		$params = array();
		if ( !$relative ) {
			$host = BIRT_SERVLET_PATH_ABS.'preview?';
		} else {
			$host = BIRT_SERVLET_PATH.'run?';
		}

		/*if($data['session']['region']['nick'] == 'ufa'){
			if($fromQueue){
				$host = BIRT_SERVLET_PATH_QUEUE.'preview?';
			}
		}*/
		if($fromQueue){
			$host = BIRT_SERVLET_PATH_QUEUE.'preview?';
		}
		
		$query = "
			select
				Report_Caption as \"Report_Caption\",
				ReportCatalog_Path as \"ReportCatalog_Path\",
				Report_FileName as \"Report_FileName\"
			from rpt.Report Report
			join rpt.ReportCatalog ReportCatalog on
			Report.ReportCatalog_id = ReportCatalog.ReportCatalog_id where Report_id = :Report_id
		";

		$result = $this->db->query(
			$query,
			array(
				'Report_id' => $this->Report_id
			)
		);
		
		$response = $result->result('array');
		$report = $response[0];
		
		$path = $report['ReportCatalog_Path'];
		if($path) $path .= '/';
		$params[] = '__report=report/'.$path.$report['Report_FileName'];
		$params[] = toUTF(str_replace(" ","%20", $this->Report_Params));
		
		$this->Report_Name = $report['Report_Caption'];
		
		return $host.implode('&',$params);
	}
	
	
	
	/**
	 * Генерация уникального кода формирования отчёта
	 */
	private function generateReportRunCode()
	{
		$this->ReportRun_Code = md5($this->Report_id . $this->Report_Params . $this->pmUser_id . time());
		return $this->ReportRun_Code;
	}

	/**
	 * Запуск отчёта
	 */
	function RunByFileName($data, $withoutHeaders = false) {
		// формируем url, отправляем запрос, получаем отчёт
		$params = array();

		$data['Report_Params'] = urldecode($data['Report_Params']);
		$reportParams = preg_split('/&amp;|&/', $data['Report_Params']);
		foreach($reportParams as $reportParam){
			$paramArr = explode('=', $reportParam);
			if (count($paramArr) > 1) {
				if ($paramArr[1] === null || $paramArr[1] === 'null' || $paramArr[1] === '') {
					$params[] = '__isnull=' . $paramArr[0];
				} else {
					$params[] = $paramArr[0] . '=' . urlencode($paramArr[1]);
				}
			} else {
				$params[] = $paramArr[0] . '=';
			}
		}

		if ( !empty($data['Report_Format']) ) {
			$params[] = '__format=' . $data['Report_Format'];
			//$data['Report_Params'] .= "&__format=".$data['Report_Format'];
		}

		$host = BIRT_SERVLET_PATH_ABS.'preview?';

		$params[] = '__report=report/'.$data['Report_FileName'];
		//$params[] = toUTF(str_replace(" ","%20", $data['Report_Params']));

		$PromedURL = $this->config->item('PromedURL');

		if ( !empty($data['pmUser_id']) ) {
			$params[] = 'paramUser=' . urlencode($data['pmUser_id']);
		}

		if ( !empty($data['Lpu_id']) ) {
			$params[] = 'paramUserLpu=' . urlencode($data['Lpu_id']);
		}

		if ( !empty($PromedURL) ) {
			$params[] = 'PromedURL=' . urlencode($PromedURL);
		}
		else {
			$params[] = 'PromedURL=' . urlencode('http' . ($_SERVER['SERVER_PORT'] == 443 ? "s" : "") . '://' . $_SERVER['SERVER_ADDR'] . ':' . $_SERVER['SERVER_PORT']);
		}

		$url = $host.implode('&',$params);

		if (isSuperAdmin() && !empty($data['isDebug'])) {
			var_dump($url); die();
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->config->item('ReportMaxExecutionTime'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		$str = curl_exec($ch);
		$res = curl_getinfo($ch);
		curl_close($ch);

		if ( $str && $res['http_code'] == 200 ) { //ух ты, мы получили ответ!
			$php_eol = "\r\n";

			if ( !preg_match("/\r/", substr($str, 0, 200)) ) {
				$php_eol = "\n";
			}

			//Берём заголовки ответа
			$headers = explode($php_eol, strstr($str, $php_eol.$php_eol, true) );
			//И само содержимое
			$str = substr($str, strpos($str, $php_eol.$php_eol) + 4);

			if (!$withoutHeaders) {
				$filename = NULL;
				foreach ($headers as $header) {

					if (strpos($header, 'Content-Disposition') !== false) {
						preg_match('/filename=["](.*)["]/', $header, $matches);
						if (!empty($matches[1])) {
							$filename = $matches[1];
						}
						header($header);
					}
					if (strpos($header, 'Content-Type') !== false) {
						$this->ReportRun_Format = $header;
						header($header);
					}
				}
			}
			return $str;
			//$this->downloadFile(REPORTPATH_ROOT.$this->Report_FileName, $this->ReportRun_Format);
		} else {
			if ( $res['http_code'] === 0 ) {
				return 'Формирование отчёта не удалось, сервер отчётности не ответил на запрос. Попробуйте повторить позже';
			}
		}
	}

	/**
	 * Запуск отчёта на формирование
	 */
	public function RunReport($show = true, $fromQueue = false)
	{
		$this->load->library('textlog', array('file'=>'ReportRunQueue_'.date('Y-m-d').'.log', 'rewrite'=>false));
		ignore_user_abort(true);
		set_time_limit(0);
		//Проставляем статус, что отчёт начал выполняться
		$this->textlog->add("......Запуск отчёта на формирование. Проставляем статус, что отчёт начал выполняться......");
		$this->SetReportRunStatusBegin();

		$isODS = false;
		if (!empty($this->Report_Params) && mb_strpos($this->Report_Params, '&__format=ods') !== false) {
			$this->Report_Params = str_replace('&__format=ods', '&__format=xls', $this->Report_Params);
			$isODS = true;
		}

		//Посылаем HTTP запрос к серверу BiRT
		$url = $this->getReportUrl(false,$fromQueue);
		
		//С HTML всё плохо, его приходится передавать напрямую
		/*if ( strpos($url, '__format=html') !== false ) {
			$url = $this->getReportUrl(true);
			header('Location: '.$url);
			//Сразу проставляем, что отчёт выполнился, а что делать
			$this->SetReportRunStatusEnd();
			return;
		}*/
		try {
			$this->textlog->add("создание нового ресурса cURL. Report_id=".$this->Report_id."  URL: ".$url);
			$ch = curl_init();
			if($ch === false){
				$this->textlog->add("Ошибка при инициализации сеанса CURL");
				$this->SetReportRunStatusFail();
				return 'Ошибка при инициализации сеанса CURL';
			}
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
			curl_setopt($ch, CURLOPT_TIMEOUT, ($show)?$this->config->item('ReportMaxExecutionTime'):28800); // Если отчет не показываем сразу (а выполняем из очереди), то таймаут для его формирования берем 8 часов.
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 1);
			$str = curl_exec($ch);
			
			if (curl_errno($ch) == 28) {				
				// если curl отвалился по таймауту 
				if($fromQueue){
					// из очереди
					$this->textlog->add(" (из очереди) curl отвалился по таймауту. Report_id=".$this->Report_id."  URL: ".$url);
					$this->SetReportRunStatusFail();
					return ;
				}else{
					// без очереди. если curl отвалился по таймауту установим отчет в очередь
					$this->textlog->add("(без очереди) curl отвалился по таймауту. Report_id=".$this->Report_id);
					$res = $this->updateReportRun();
				
					$table = ''; $reportGenerationOptions = '';
					if($this->Report_ParamsArr && is_array($this->Report_ParamsArr)){
						$reportGenerationOptions .= '<br><b>Параметры формирования отчета</b>:<br/>'.implode("<br>", $this->Report_ParamsArr);
					}
					$this->stoppedOnTimeout = $reportGenerationOptions;
					return 'Отчет <b>'.$this->Report_Name.'</b> не сформировался в течение получаса и с теми же параметрами поставлен в очередь на формирование. Информацию см. во вкладке очередь. '.$reportGenerationOptions;
				}
			}
			if($str === false){
				$this->textlog->add("Ошибка при выполнении запроса СURL. Report_id=".$this->Report_id."  URL: ".$url);				
				$this->SetReportRunStatusFail();
				return 'Ошибка при выполнении запроса CURL';
			}
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код
			$res = curl_getinfo($ch);
			curl_close($ch);
			
			// Ищем совпадения с нашим списком кодов
			if (isset($this->codesStatus[$http_code])){
				$this->textlog->add("Бирт: получили ответ: ".$http_code." - ".$this->codesStatus[$http_code]);
			}
			
			$log_queue = '(БЕЗ очереди)';
			if($fromQueue){
				$log_queue = '(ИЗ очереди)';
			}
			$this->textlog->add("Текущий URL ".$log_queue." - ".$url."\r\n");
			$this->textlog->add("Запускаем RunReport Report_id=".$this->Report_id." ReportRun=".$this->ReportRun_id." с параметрами:\r\n".print_r($res,true)."\r\n");
			
			if ( $str && $res['http_code'] == 200 ) { //ух ты, мы получили ответ!
				$php_eol = "\r\n";

				if ( !preg_match("/\r/", substr($str, 0, 200)) ) {
					$php_eol = "\n";
				}

				$this->textlog->add('Бирт: получили ответ (код 200)');
				//Берём заголовки ответа
				$headers = explode($php_eol, strstr($str, $php_eol.$php_eol, true) );
				//И само содержимое
				$str = substr($str, strpos($str, $php_eol.$php_eol) + 4);
				
				$filename = NULL;
				foreach($headers as $header) {
					
					if (strpos($header, 'Content-Disposition') !== false ) {
						preg_match('/filename=["](.*)["]/', $header, $matches);
						if (!empty($matches[1])) {
							$filename = $matches[1];
						}
						if ($show)
							header($header);
					}
					if (strpos($header, 'Content-Type') !== false ) {
						$this->ReportRun_Format = $header;
						if ($show)
							header($header);
					}
				}
				if ( isset($filename) ) {
					if ($isODS) {
						$filename = preg_replace('/\.xls$/', '.ods', $filename);
					}
					//Имя файла определено, сохраняем отчёт
					//$basedir = $_SERVER['DOCUMENT_ROOT'];
					$basedir = '';
					$repdir = REPORTPATH_ROOT.$this->pmUser_id."/";
					if ( !is_dir($basedir.$repdir) ) {
						mkdir($basedir.$repdir, 0777, true);
					}
					$this->Report_FileName = $this->pmUser_id . "/" . time() . '_' . $filename;
					$this->textlog->add('Получили из ответа имя файла отчета: сохраняем отчёт с именем '. $this->Report_FileName);
					
					//Записываем содержимое отчёта в файл
					file_put_contents($basedir . REPORTPATH_ROOT . $this->Report_FileName, $str);
					$this->SetReportRunStatusEnd();
					if ($show)
						header('Location: '.REPORTPATH_ROOT.$this->Report_FileName);
				} else {
					$this->textlog->add('Не удалось определить имя файла отчета (ошибка). Report_id='.$this->Report_id);
					$this->SetReportRunStatusFail();
				}
				return ;
				//Проставляем статус что запрос выполнен и путь где хранится результат
				/*$this->SetReportRunStatusEnd();
				if ($show)
					header('Location: '.REPORTPATH_ROOT.$this->Report_FileName);
				return ;*/
				//$this->downloadFile(REPORTPATH_ROOT.$this->Report_FileName, $this->ReportRun_Format);
			} else {
				$this->textlog->add('Бирт: получили ошибку (код '.$res['http_code'].'). Report_id='.$this->Report_id);
				$this->SetReportRunStatusFail();
				if ( $res['http_code'] === 0 ) {
					return 'Формирование отчёта не удалось, сервер отчётности не ответил на запрос. Попробуйте повторить позже';
				}elseif(isset($this->codesStatus[$http_code])){
					return 'Формирование отчёта не удалось. Сервер вернул ответ: '.$http_code.' - '.$this->codesStatus[$http_code];
				}
			}
		} catch (Exception $e) {
			$this->textlog->add('Формирование отчёта не удалось, критическая ошибка: '.$e->getMessage());
			$this->SetReportRunStatusFail();
		}
	}
	
	/**
	 * Отменяем отчет и устанавливаем в очередь
	 */
	function updateReportRun(){
		$query = "
			update rpt.ReportRun
			set
				ReportRun_queueDT = dbo.tzGetDate(),
				ReportRun_Status = null,
				ReportRun_begDT = null,
				ReportRun_endDT = null,
				ReportRun_FilePath = null
			where
				ReportRun_id = :ReportRun_id";

		$result = $this->db->query(
			$query,
			array(
				'ReportRun_id' => $this->ReportRun_id
			)
		);
		
		return $result;
	}
	
	/**
	 * Добавление данных об новом случае формирования отчёта в базу данных
	 */
	function InsertNewReportRun()
	{
		
		$query = "
			select 
				ReportRun_id as \"ReportRun_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from rpt.p_ReportRun_ins (
				ReportRun_Code := :ReportRun_Code,
				Report_id := :Report_id,
				Report_Params := :Report_Params,
				pmUser_id := :pmUser_id,
				ReportRun_queueDT := ".(($this->isqueue)?"dbo.tzGetDate()":"null").",
				ReportRun_begDT := :ReportRun_begDT,
				ReportRun_endDT := :ReportRun_endDT,
				ReportRun_Status := :ReportRun_Status,
				ReportRun_FilePath := :ReportRun_FilePath
			);
		";
		$result = $this->db->query(
			$query,
			array(
				'ReportRun_Code' => $this->ReportRun_Code,
				'Report_id' => $this->Report_id,
				'Report_Params' => $this->Report_Params,
				'pmUser_id' => $this->pmUser_id,
				'ReportRun_begDT' => null,
				'ReportRun_endDT' => null,
				'ReportRun_Status' => null,
				'ReportRun_FilePath' => null
			)
		);

		if ( is_object($result) ) {
			$res = $result->result('array');
			if ( isset($res[0]['ReportRun_id']) ) {
				$this->ReportRun_id = $res[0]['ReportRun_id'];
				return $this->ReportRun_id;
			} else {
				return false;
			}
		}
		else {
			return false;
		}
	}
	
	/**
	 * Обновление статуса отчёта при начале формирования
	 */
	function SetReportRunStatusBegin()
	{
		$query = "
			update rpt.ReportRun
			set
				ReportRun_begDT = dbo.tzGetDate(),
				ReportRun_Status = 2
			where
				ReportRun_id = :ReportRun_id";

		$result = $this->db->query(
			$query,
			array(
				'ReportRun_id' => $this->ReportRun_id
			)
		);
	}
	
	/**
	 * Обновление статуса отчёта в конце формирования
	 */
	function SetReportRunStatusEnd()
	{
		$query = "
			update rpt.ReportRun
			set
				ReportRun_endDT = dbo.tzGetDate(),
				ReportRun_Status = 1,
				ReportRun_FilePath = :ReportRun_FilePath,
				ReportRun_Format = :ReportRun_Format
			where
				ReportRun_id = :ReportRun_id";
	
		$result = $this->db->query(
			$query,
			array(
				'ReportRun_id' => $this->ReportRun_id,
				'ReportRun_FilePath' => $this->Report_FileName,
				'ReportRun_Format' => $this->ReportRun_Format
			)
		);
	}
	
	/**
	 * Обновление статуса отчёта в конце формирования в случае неудачи
	 */
	function SetReportRunStatusFail()
	{
		$query = "
			update rpt.ReportRun
			set
				ReportRun_endDT = dbo.tzGetDate(),
				ReportRun_Status = 0
			where
				ReportRun_id = :ReportRun_id";
	
		$result = $this->db->query(
			$query,
			array(
				'ReportRun_id' => $this->ReportRun_id,
				'ReportRun_FilePath' => $this->Report_FileName
			)
		);
	}
	
	
	/**
	 * Проверка, может ли пользователь запустить отчёт на формирование
	 * Возвращает true если может, или строку с ошибкой в другом случае
	 */
	function CheckIfUserCanRunReport($Report_id, $pmUser_id)
	{
		
		$ReportMaxExecutionTime = $this->config->item('ReportMaxExecutionTime');
		$ReportMaxExecutionTime = isset($ReportMaxExecutionTime) ? $ReportMaxExecutionTime : 0;
		
		//Ищем незавершенные отчёты исключая отчёты начатые давно и не завершившиеся
		$query = "
			select
				Report_id as \"Report_id\",
				ReportRun_begDT as \"ReportRun_begDT\",
				ReportRun_Code as \"ReportRun_Code\"
			from rpt.ReportRun
			where
				pmUser_insID = :pmUser_id
				and Report_id = :Report_id
				and ReportRun_endDT is null
				and ReportRun_queueDT is null
				and not (ReportRun_queueDT is null and ReportRun_begDT is null)
			";
			//	and dateadd(s, :ReportMaxExecutionTime, ReportRun_begDT) > dbo.tzGetDate()";
	
		$result = $this->db->query(
			$query,
			array(
				'pmUser_id' => $pmUser_id,
				'Report_id' => $Report_id
				//'ReportMaxExecutionTime' => $ReportMaxExecutionTime
			)
		);

		if ( is_object($result) ) {
			$res = $result->result('array');

			if ( isset($res[0]) ) {
				return "У вас уже выполняется данный отчёт и новый не может быть сформирован.<br/>
				Формирование отчёта началось: ".ConvertDateFormat($res[0]['ReportRun_begDT'], "d.m.Y H:i")."<br/>
				Дождитесь конца формирования отчёта в другом окне.<br/>
				Если вы случайно закрыли другое окно, то воспользуйтесь приведённой ниже ссылкой для загрузки отчёта.<br/>
				<a href='/?c=ReportRun&m=Get&ReportRun_Code={$res[0]['ReportRun_Code']}'>Загрузка сформированного отчёта</a><br/>
				Результаты по ссылке будут доступны, как только будет завершено формирование отчета.<br/>".$this->GetCurrentLoadText();
			} else {
				return true;
			}
		}
		else {
			return 'Ошибка получения результата.';
		}
	}
	
	
	
	/**
	 * Проверка, нет ли уже такого сформированного отчёта в кэше
	 * Если есть, то возвращается его код, иначе false
	 */
	function CheckIfReportInCache($Report_id, $pmUser_id, $Report_Params)
	{
		// Ищем отчёты с такими же параметрами, которые завершились недавно
		
		$ReportCacheTime = $this->config->item('ReportCacheTime');
		$ReportCacheTime = isset($ReportCacheTime) ? $ReportCacheTime : 0;
		
		$query = "
			select
				Report_id as \"Report_id\",
				ReportRun_begDT as \"ReportRun_begDT\",
				ReportRun_Code as \"ReportRun_Code\"
			from rpt.ReportRun
			where
				pmUser_insID = :pmUser_id
				and Report_id = :Report_id
				and left(Report_Params, :ReportParamsLen) = :Report_Params
				and dbo.tzGetDate() < :ReportCacheTime * interval '1 minute' + ReportRun_endDT
				and ReportRun_FilePath is not null";
	
		$result = $this->db->query(
			$query,
			array(
				'pmUser_id' => $pmUser_id,
				'Report_id' => $Report_id,
				'Report_Params' => $Report_Params,
				'ReportParamsLen' => mb_strlen($Report_Params),
				'ReportCacheTime' => $ReportCacheTime
			)
		);
		
		if ( is_object($result) ) {
			$res = $result->result('array');
			if (isset($res[0]) && isset($res[0]['ReportRun_Code'])) {
				return $res[0]['ReportRun_Code'];
			} else {
				return false;
			}
		}
	}
	
	
	/**
	 * Получение количества ожидающих процессов
	 */
	function GetWaitingProcessCount($DatabaseType = 2)
	{
		$dbName = 'bdreports';

		if ( 3 == $DatabaseType ) {
			$dbName = 'registry';
		}

		$dbrep = $this->load->database($dbName, true);

		//Берём данные по отчёту
		$query = "
			SELECT count(*) as \"cnt\"
			FROM
			    pg_catalog.pg_stat_activity sa
            where
                datname=current_database()
            and datediff('second',query_start::timestamp,GetDate())>10
            and state='active'
            and lower(application_name) like lower('%JDBC Driver%')";

		$result = $dbrep->query(
			$query,
			array()
		);

		if ( is_object($result) ) {
			$res = $result->result('array');

			if ( isset($res[0]) ) {
				return $res[0]['cnt'];
			} else {
				return false;
			}
		}
		else {
			return false;
		}
	}
	
	
	/**
	 * Получение текста с описание загруженности сервера.
	 */
	function GetCurrentLoadText()
	{
		$str = "<b>Текущая нагрузка на сервер отчетов:</b> ";
		$ProcessesCount = $this->GetWaitingProcessCount();
		if ($ProcessesCount <= 1) {
			$str .= "<span style='color:green'>Нет</span>";
		} else if ($ProcessesCount < 3) {
			$str .= "<span style='color:green'>Низкая</span>";
		} else if ($ProcessesCount < 6) {
			$str .= "<span style='color:green'>Незначительная</span>";
		} else if ($ProcessesCount < 10) {
			$str .= "<span style='color:yellow'>Средняя</span>";
		} else if ($ProcessesCount < 15) {
			$str .= "<span style='color:orange'>Выше средней</span>";
		} else if ($ProcessesCount < 20) {
			$str .= "<span style='color:#ff0033'>Высокая</span>";
		} else {
			$str .= "<span style='color:#ff0000'>Критическая</span>";
		}
		return $str;
	}
	
	/**
	 * Получение данных ранее сформированного отчёта
	 */
	function GetReportData($data)
	{
		//Берём данные по отчёту
		$query = "
			select
				Report_id as \"Report_id\",
				ReportRun_begDT as \"ReportRun_begDT\",
				ReportRun_endDT as \"ReportRun_endDT\",
				ReportRun_FilePath as \"ReportRun_FilePath\",
				ReportRun_Status as \"ReportRun_Status\",
				ReportRun_Format as \"ReportRun_Format\"
			from rpt.ReportRun 
			where ReportRun_Code = :ReportRun_Code";
	
		$result = $this->db->query(
			$query,
			array(
				'ReportRun_Code' => $data['ReportRun_Code']
			)
		);

		if ( is_object($result) ) {
			$res = $result->result('array');

			if ( isset($res[0]) ) {
				if ( !isset($res[0]['ReportRun_endDT']) ) {
					return "Этот отчет в данный момент формируется. Попробуйте обновить страницу через некоторое время.<br/><a href='{$_SERVER['REQUEST_URI']}'>Попробовать еще раз.</a><br/>".$this->GetCurrentLoadText();
				} else if ( isset($res[0]['ReportRun_Status']) && $res[0]['ReportRun_Status'] === 0 ) {
					return 'При формировании отчёта возникли проблемы, отчет не сформировался в течении 30 минут или сформировался с ошибкой. Попробуйте выполнить отчет еще раз, когда сервер будет не так нагружен.<br/>'.$this->GetCurrentLoadText();
				} else {
					if ( !isset($res[0]['ReportRun_FilePath']) ) {
						return 'К сожалению данные сформированного отчёта не хранятся на сервере.';
					} else if ( !file_exists(REPORTPATH_ROOT.$res[0]['ReportRun_FilePath']) ) {
						return 'К сожалению данные сформированного отчёта не хранятся на сервере.';
					} else {
						$filename = $res[0]['ReportRun_FilePath'];
						$filename = substr($filename, strrpos($filename, '/') + 1);
						/*header("Content-Disposition: inline; filename=\"{$filename}\"");
						header($res[0]['ReportRun_Format']);
						return file_get_contents( REPORTPATH_ROOT.$res[0]['ReportRun_FilePath'] );*/
						header('Location: '.REPORTPATH_ROOT.$res[0]['ReportRun_FilePath']);
						//$this->downloadFile(REPORTPATH_ROOT.$res[0]['ReportRun_FilePath'], $res[0]['ReportRun_Format']);
						return;
					}
				}
			} else {
				return 'Отчёт с таким кодом не найден.';
			}
		}
		else {
			return 'Ошибка получения результата.';
		}
	}
	
	/**
	 * Загрузка сформированного файла отчета
	 */
	function downloadFile($filename, $mimetype='application/octet-stream')
	{
		if (!file_exists($filename)) die('Файл не найден ' . $filename);

		$from=$to=0; $cr=NULL;

		if (isset($_SERVER['HTTP_RANGE']))
		{
			$range=substr($_SERVER['HTTP_RANGE'], strpos($_SERVER['HTTP_RANGE'], '=')+1);
			$from=strtok($range, '-');
			$to=strtok('/'); if ($to>0) $to++;
			if ($to) $to-=$from;
			header('HTTP/1.1 206 Partial Content');
			$cr = 'Content-Range: bytes ' . $from . '-' . (($to) ? ($to . '/' . ($to + 1)) : filesize($filename));
		} else {
			header('HTTP/1.1 200 Ok');
		}

		$etag=md5($filename);
		$etag=substr($etag, 0, 8) . '-' . substr($etag, 8, 7) . '-' . substr($etag, 15, 8);
		header('ETag: "' . $etag . '"');

		header('Accept-Ranges: bytes');
		header('Content-Length: ' . (filesize($filename)-$to+$from));
		if ($cr) header($cr);

		header('Connection: close');
		header('Content-Type: ' . $mimetype);
		header('Last-Modified: ' . gmdate('r', filemtime($filename)));
		$f=fopen($filename, 'r');
		header('Content-Disposition: attachment; filename="' . basename($filename) . '";');
		if ($from) fseek($f, $from, SEEK_SET);
		if (!isset($to) or empty($to)) {
			$size=filesize($filename)-$from;
		} else {
			$size=$to;
		}

		$downloaded=0;

		while(!feof($f) and !connection_status() and ($downloaded<$size)) {
			echo fread($f, 512000);
			$downloaded+=512000;
			flush();
		}
		fclose($f);
	}
	/**
	 * Дерево для формы очереди и истории
	 */ 
	function loadReportTree($data) {
		return array(
			array("text"=>"Очередь","id"=>"queue","leaf"=>true,"iconCls"=>"report-16","cls"=>"folder"),
			array("text"=>"История","id"=>"history","leaf"=>true,"iconCls"=>"history-16","cls"=>"folder")
		);
	}
	
	/**
	 * Грид для формы очереди и истории
	 */
	function loadReportGrid($data) {
		$params = array();
		$filter = " (1=1)";
		if ($data['pmUser_id']>0) {
			$params['pmUser_id'] = $data['pmUser_id'];
			//$filter .= " and rr.pmUser_insID = :pmUser_id";
			$filter_user = " and rr.pmUser_insID = :pmUser_id";
		}
		if ($data['isqueue']) { // Если очередь 
			$filter .= " and ReportRun_queueDT is not null 
			and ReportRun_endDT is null";
			if(isSuperAdmin())
			{
				$filter_user = " and (1=1)";
				if(isset($data['Lpu_id']))
				{
					$params['Lpu_id'] = $data['Lpu_id'];
					$filter_user = " and pmuser.Lpu_id = :Lpu_id";
				}
			}
		}
		$filter .= $filter_user;
		if (isset($data['filterReportStatus'])) {
			$params['ReportRun_Status'] = $data['filterReportStatus'];
			$filter .= " and COALESCE(rr.ReportRun_Status, 0) = :ReportRun_Status";
		}
		if (isset($data['filterReportSign'])) {
			$params['ReportRun_IsSigned'] = $data['filterReportSign'];
			$filter .= " and COALESCE(rr.ReportRun_IsSigned, 1) = :ReportRun_IsSigned";
		}
		if (isset($data['filterReportPeriod'][0])) {
			if($data['isqueue'])
			{
				$params['ReportRun_queueDTBeg'] = $data['filterReportPeriod'][0];
				$filter .= " and cast(rr.ReportRun_queueDT as date) >= :ReportRun_queueDTBeg";
			}
			else
			{
				$params['ReportIns_begDT'] = $data['filterReportPeriod'][0];
				$filter .= " and cast(rr.ReportRun_insDT as date) >= :ReportIns_begDT";
			}
		}
		if (isset($data['filterReportPeriod'][1])) {
			if($data['isqueue'])
			{
				$params['ReportRun_queueDTEnd'] = $data['filterReportPeriod'][1];
				$filter .= " and cast(rr.ReportRun_queueDT as date) <= :ReportRun_queueDTEnd";
			}
			else {
				$params['ReportIns_endDT'] = $data['filterReportPeriod'][1];
				//$filter .= " and (cast(rr.ReportRun_endDT as date) <= :ReportRun_endDT or rr.ReportRun_endDT is null)";
				$filter .= " and cast(rr.ReportRun_insDT as date) <= :ReportIns_endDT";
			}
		}
		if (isset($data['filterReportName'])) {
			$params['Report_Title'] = "%".$data['filterReportName']."%";
			$filter .= " and r.Report_Title ILIKE :Report_Title";
		}
		$path = (defined('REPORTPATH_ROOT')?REPORTPATH_ROOT:"");
		$query = "
			select
			-- select
				ReportRun_id as \"ReportRun_id\",
				rr.Report_id as \"Report_id\", 
				Report_Title as \"Report_Name\", 
				Report_Params as \"Report_Params\",
				null as \"Report_paramBegDate\", -- заполняется в коде
				null as \"Report_paramEndDate\", -- заполняется в коде
				null as \"Lpu_id\", -- заполняется в коде
				rr.pmUser_insID as \"pmUser_insID\",
				pmuser.pmUser_name as \"pmUser_name\",
				to_char(cast(ReportRun_queueDT as timestamp), 'DD.MM.YYYY HH24:MI') as \"ReportRun_queueDT\",
				to_char(cast(ReportRun_begDT as timestamp), 'DD.MM.YYYY HH24:MI') as \"ReportRun_begDT\",
				to_char(cast(ReportRun_endDT as timestamp), 'DD.MM.YYYY HH24:MI') as \"ReportRun_endDT\",
				ReportRun_Status as \"ReportRun_Status\",
				case
					when ReportRun_Status is null then 'Не сформирован'
					when ReportRun_Status::text = '' then 'Не сформирован'
					when ReportRun_Status = 1 then 'Сформирован'
				end as \"ReportRun_StatusName\",
				'{$path}'||ReportRun_FilePath as \"ReportRun_FilePath\",
				ReportRun_Format as \"ReportRun_Format\",
				pmusers.pmUser_name as \"pmUser_signName\",
				rr.ReportRun_IsSigned as \"ReportRun_IsSigned\",
				to_char(rr.ReportRun_signDT, 'DD.MM.YYYY') as \"ReportRun_signDT\"
				-- end select
			from
			-- from
				rpt.ReportRun rr
				left join rpt.Report r on rr.Report_id = r.Report_id
				left join v_pmUser pmuser on pmuser.pmUser_id = rr.pmUser_insID
				left join v_pmUser pmusers on pmusers.pmUser_id = rr.pmUser_signID
			--end from
			where
			-- where
				{$filter}
			--end where
			order by
			--order by
				ReportRun_id
			--end order by
		";
		
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		if ( is_object($result) ) {
			$res = $result->result('array');
			for ($i=0; $i < count($res); $i++){
				$res[$i]['row_num'] = $i + 1;
			}
			if (count($res)==$data['limit']) {
				// определение общего количества записей
				$result_count = $this->db->query(getCountSQLPH($query), $params);
				if (is_object($result_count)) {
					$cnt_arr = $result_count->result('array');
					$count = $cnt_arr[0]['cnt'];
					unset($cnt_arr);
				} else {
					$count = 0;
				}
			} else { // Иначе считаем каунт по реальному количеству + start
				$count = $data['start'] + count($res);
			}
			$response = array();
			$response['totalCount'] = $count;
			$response['data'] =  $res;
			return $response;
		} else {
			return false;
		}
		
		

	}
	
	/**
	 * Получение отчета из очереди для добавления его выполнения 
	 */
	function getReportQueue($data = array()) {
		$filter = "";
		$queryParams = [];
		if (isSuperAdmin() && !empty($data['ReportRun_id'])) {
			$filter .= " and ReportRun_id = :ReportRun_id";
			$queryParams['ReportRun_id'] = $data['ReportRun_id'];
		}
		$query = "
			select
				ReportRun_id as \"ReportRun_id\",
				rr.Report_id as \"Report_id\",
				ReportRun_Code as \"ReportRun_Code\",
				Report_Title as \"Report_Title\",
				Report_Params as \"Report_Params\",
				rr.pmUser_insID as \"pmUser_insID\",
				pmUser_name as \"pmUser_name\",
				ReportRun_Status as \"ReportRun_Status\",
				ReportRun_FilePath as \"ReportRun_FilePath\",
				ReportRun_Format as \"ReportRun_Format\"
			from rpt.ReportRun rr
			left join rpt.Report r on rr.Report_id = r.Report_id
			left join v_pmUser pmuser on pmuser.pmUser_id = rr.pmUser_insID
			where ReportRun_queueDT is not null and ReportRun_begDT is null {$filter}
			order by ReportRun_queueDT
			limit 1
		";
		//echo getDebugSql($query,$params);exit;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$res = $result->result('array');
			if (is_array($res) && count($res)>0) {
				return $res[0];
			}
		}
		return false;
	}
	
	/**
	 * Получение выполняемых на текущий момент отчетов
	 */
	function countTaskReportQueue($data = array()) {
		$query = "
			-- Количество запрос которые из очереди (дата начала выполнения уже стоит, а дата окончания выполнения еще нет), а статус равен 0 (запущен, но еще не закончен)
			select count(*) as \"rec\"
			from rpt.ReportRun rr
			left join rpt.Report r on rr.Report_id = r.Report_id
			left join v_pmUser pmuser on pmuser.pmUser_id = rr.pmUser_insID
			where ReportRun_queueDT is not null and ReportRun_begDT is not null and ReportRun_endDT is null
			--and ReportRun_Status = 2
		";
		//echo getDebugSql($query,$params);exit;
		$result = $this->db->query($query,array());

		if ( is_object($result) ) {
			$res = $result->result('array');
			if (is_array($res) && count($res)>0 && isset($res[0]['rec'])) {
				return $res[0]['rec'];
			}
			return 0;
		} else {
			return false;
		}
	}

	
	/**
	 * Проверка наличия в очереди отчета с такими же параметрами для исключения повторной постановки в очередь
	 */
	function checkReportQueue($data = array()) {
		$query = "
			select to_char(cast(ReportRun_queueDT as timestamp), 'DD.MM.YYYY HH24:MI') as \"queueDT\"
			from rpt.ReportRun rr 
			left join rpt.Report r  on rr.Report_id = r.Report_id
			left join v_pmUser pmuser  on pmuser.pmUser_id = rr.pmUser_insID
			where ReportRun_queueDT is not null --and ReportRun_begDT is null and ReportRun_endDT is null
			and ReportRun_Status is null
			and left(Report_Params, :ReportParamsLen) = :Report_Params
			and rr.Report_id = :Report_id
			and rr.pmUser_insID = :pmUser_id
			limit 1
		";
		//echo getDebugSql($query,$data);exit;
		$result = $this->db->query($query,$data);

		if ( is_object($result) ) {
			$res = $result->result('array');
			if (is_array($res) && count($res)>0  && isset($res[0]['queueDT'])) {
				return $res[0]['queueDT'];
			}
		} else {
			return false;
		}
	}

	/**
	* Проверка нахождения отчета в очереди
	*/
	function getReportQueueId($data = array()) {
		$query = "
			select rr.ReportRun_id as \"ReportRun_id\"
			from rpt.ReportRun rr
			left join rpt.Report r on rr.Report_id = r.Report_id
			left join v_pmUser pmuser on pmuser.pmUser_id = rr.pmUser_insID
			where ReportRun_queueDT is not null --and ReportRun_begDT is null and ReportRun_endDT is null
			and (ReportRun_Status is null OR ReportRun_Status = 2) -- ReportRun_Status = 2 начало формирования отчета
			and left(Report_Params, :ReportParamsLen) = :Report_Params
			and rr.Report_id = :Report_id
			and rr.pmUser_insID = :pmUser_id
			limit 1
		";
		//echo getDebugSql($query,$data);exit;
		$result = $this->db->query($query,$data);

		if ( is_object($result) ) {
			$res = $result->result('array');
			if (is_array($res) && count($res)>0  && isset($res[0]['ReportRun_id'])) {
				return $res[0]['ReportRun_id'];
			}
		} else {
			return false;
		}
	}

	/**
	 * Проверка наличия отчета в v_ReportLong - в списке "тяжелых", которые на Уфе нужно сразу ставить в очередь (https://redmine.swan.perm.ru/issues/85094)
	 */
	function CheckReportForLong($Report_id) {
		$is_report_long = false;
		$params = array(
			'Report_id' => $Report_id
		);
		$query = "
			select count (Report_id) as \"ctn\"
			from rpt.v_ReportLong
			where Report_id = :Report_id
		";
		$result = $this->db->query($query,$params);
		if(is_object($result)){
			$result = $result->result('array');
			if(count($result)>0)
			{
				if($result[0]['ctn'] > 0)
					$is_report_long = true;
			}
		}
		return $is_report_long;
	}


	/**
	*	Проверка возможности удаление отчета из очереди
	*/
	function CheckIfReportRunCanBeDeleted($ReportRun_id) {
		$params = array(
			'ReportRun_id' => $ReportRun_id
		);
		$query = "
			select ReportRun_id as \"ReportRun_id\"
			from rpt.v_ReportRun
			where ReportRun_id = :ReportRun_id
			and ReportRun_queueDT is not null
			and ReportRun_begDT is null
			and ReportRun_Status is null
			limit 1
		";
		$result = $this->db->query($query,$params);
		if(is_object($result)){
			$result = $result->result('array');
			if(count($result) > 0)
				return true;
			else
				return false;
		}
		else
			return false;
	}

	/**
	*	Удаление отчета из очереди
	*/
	function deleteReportRuns($ReportRun_id){
		$query = "
			select 
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from rpt.p_ReportRun_del (
                ReportRun_id := :ReportRun_id
            );
		";

		$result = $this->db->query($query, array(
			'ReportRun_id' => $ReportRun_id
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление строки очереди отчетов)'));
		}
	}

	/**
	 * Получение идентификаторв БД, на которой должен выполняться отчет
	 * DatabaseType = 2 - отчетная БД
	 * DatabaseType = 3 - реестровая БД
	 * @task https://redmine.swan.perm.ru/issues/112230
	 */
	public function getDatabaseTypeByReport($Report_id = null) {
		if ( !empty($this->Report_id) ) {
			$Report_id = $this->Report_id;
		}

		$DatabaseType = $this->model->getFirstResultFromQuery('select DatabaseType  as "DatabaseType" from rpt.Report  where Report_id = :Report_id limit 1', array('Report_id' => $Report_id));

		if ( $DatabaseType === false ) {
			$DatabaseType = 2;
		}

		return $DatabaseType;
	}

	/**
	 * Получение отчёта для подписи
	 */
	function getReportForSign($data) {
		$db = $this->load->database('reports', true); // БД для отчётов

		$resp = $this->queryResult("
            select
                ReportRun_id as \"ReportRun_id\",
				ReportRun_Code as \"ReportRun_Code\",
				Report_id as \"Report_id\",
				Report_Params as \"Report_Params\",
				ReportRun_queueDT as \"ReportRun_queueDT\",
				ReportRun_begDT as \"ReportRun_begDT\",
				ReportRun_endDT as \"ReportRun_endDT\",
				ReportRun_Status as \"ReportRun_Status\",
				ReportRun_FilePath as \"ReportRun_FilePath\",
				ReportRun_Format as \"ReportRun_Format\",
				pmUser_insID as \"pmUser_insID\",
				pmUser_updID as \"pmUser_updID\",
				ReportRun_insDT as \"ReportRun_insDT\",
				ReportRun_updDT as \"ReportRun_updDT\",
				pmUser_signID as \"pmUser_signID\",
				ReportRun_IsSigned as \"ReportRun_IsSigned\",
				ReportRun_signDT as \"ReportRun_signDT\"
            from 
                rpt.ReportRun 
            where 
                ReportRun_id = :ReportRun_id", array(
			'ReportRun_id' => $data['ReportRun_id']
		), $db);

		$pdf = file_get_contents(REPORTPATH_ROOT.$resp[0]['ReportRun_FilePath']);

		return array('pdf' => $pdf);
	}

	/**
	 * Генерация параметров для отчета из JSON-строки
	 */
	function generateReportParamsFromJSON($jsonData) {

		$params = json_decode($jsonData, true);
		$output = '';

		if (is_array($params)) {
			foreach ($params as $key => $value) {
				if (!empty($key)) {
					$output .= '%26param'.$key.'%3D'.$value;
				}
			}
		}
		return $output;
	}
}
?>