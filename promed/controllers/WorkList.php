<?php	defined('BASEPATH') or die ('No direct script access allowed');

/**
 * @package	  WorkList
 * @author	  Yan Yudin
 * @version	  12 2019
 */

 class WorkList extends swController {

	const MYAETITLE = 'PROMED';

    /**
     * Конструктор
     */
    function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('WorkList_model','dbmodel');
        $this->inputRules = array(
            'getUsluaList' => array(
                array(
                    'field' => 'MedService_id',
                    'label' => 'Служба',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
            'addMedProductUslugaComplex' => array(
                array(
                    'field' => 'Data',
                    'label' => 'Связь',
                    'rules' => 'required',
                    'type' => 'string'
                )
			),
			'deleteOrAddToQueueMWL' => array(
                array(
                    'field' => 'Data',
                    'label' => 'Очередь',
                    'rules' => 'required',
                    'type' => 'string'
                )
			), 
			'getWorkList' => array(
				array(
                    'field' => 'Data',
                    'label' => 'Список',
                    'rules' => '',
                    'type' => 'string'
                )
			),
			'addRecordToDB' => array(
				array(
                    'field' => 'Data',
                    'label' => 'Информация о направлении',
                    'rules' => '',
                    'type' => 'string'
                )
			),
			'sentToPacs' => array(
				array(
                    'field' => 'Data',
                    'label' => 'Информация о направлении и операция',
                    'rules' => '',
                    'type' => 'string'
                )
			),
			'cancelRecordToDB' => array(
				array(
                    'field' => 'Data',
                    'label' => 'Информация о направлении',
                    'rules' => '',
                    'type' => 'string'
                )
			),
			'updRecordToDB' => array(
				array(
                    'field' => 'Data',
                    'label' => 'Информация о направлении',
                    'rules' => '',
                    'type' => 'string'
                )
			),
			'checkResult' => array(
				array(
					'field' => 'Data',
                    'label' => 'Список идентификаторов параклинических услуг',
                    'rules' => '',
                    'type' => 'string'
				)
			), 
			'getDirections' => array(
				array(
					'field' => 'Data',
                    'label' => 'Список направлений на службу',
                    'rules' => '',
                    'type' => 'string'
				)
			),
			'checkDirectionInWLQ' => array(
				array(
					'field' => 'EvnDirection_id',
                    'label' => 'Идентификатор направления',
                    'rules' => '',
                    'type' => 'string'
				)
			),
			'getMedProductCardIsWL' => array(
				array(
					'field' => 'MedService_id',
                    'label' => 'Идентификатор службы',
                    'rules' => '',
                    'type' => 'string'
				),
				array(
					'field' => 'MedProductCard_IsWorkList',
                    'label' => 'Признак работы с рабочим списком',
                    'rules' => 'required',
                    'type' => 'string'
				),
				array(
					'field' => 'EvnDirection_id',
                    'label' => 'Идентификатор направления',
                    'rules' => '',
                    'type' => 'string'
				),
				array(
					'field' => 'MedProductCard_isCombo',
                    'label' => 'Признак комбобокса',
                    'rules' => '',
                    'type' => 'string'
				)
			),
			'checkIsInWorkListQueue' => array(
				array(
					'field' => 'EvnUslugaPar_id',
                    'label' => 'Идентификатор параклинической услуги',
                    'rules' => 'required',
                    'type' => 'id'
				)
			)
        );
    }

    /**
     * Получение списка услуг на службе 
     */
    public function getUsluaList() {
        $data = $this->ProcessInputData('getUsluaList', true);
        if($data === false) { return false; }
        $response = $this->dbmodel->getUsluaList($data);
        $this->ReturnData($response);
    }

    /**
     * Сохранение связи между МИ и услугой на службе
     */
    public function addMedProductUslugaComplex() {
        $data = $this->ProcessInputData('addMedProductUslugaComplex', true);
        if($data === false) { return false; }
        $response = $this->dbmodel->addMedProductUslugaComplex($data);
        $this->ProcessModelList($response, true, 'При сохранении произошла ошибка')->ReturnData();
	}
	
	/**
	 * Отправка направления в очередь рабочего списка 
	 */
	public function deleteOrAddToQueueMWL() {
		$data = $this->ProcessInputData('deleteOrAddToQueueMWL', true);
        if($data === false) { return false; }
        $response = $this->dbmodel->deleteOrAddToQueueMWL($data);
        $this->ProcessModelList($response, true, 'При добавлении произошла ошибка')->ReturnData();
	}

	/**
	 * Получение рабоченго списка из БД
	 */
	public function getWorkList(){
		$data = $this->ProcessInputData('getWorkList', true);
		if($data === false) { return false; }
		$response = $this->dbmodel->getWorkList($data);
		$this->ProcessModelList($response, true, 'При получении списка рабочего списка произошла ошибка')->ReturnData();
	}

	/**
	 * Добавление в очередь заявок на выполнение услуги в MWL
	 */
	public function addRecordToDB() {
		$data = $this->ProcessInputData('addRecordToDB', true);
		if($data === false) { return false; }
		$response = $this->dbmodel->addRecordToDB($data);
		$this->ProcessModelList($response, true, 'При добавлении направления в очередь РС произошла ошибка')->ReturnData();
	}

	/**
	 * Получение списка не обслуженных направлений на службу
	 */
	public function getDirections() {
		$data = $this->ProcessInputData('getDirections', true);
		if($data === false) { return false; }
		$data['Todays_date'] = $this->getDate('Y-m-d');
		$response =$this->dbmodel->getDirections($data);
		$this->ProcessModelList($response, true, 'Во время получения направлений на службу произошла ошибка')->ReturnData();
	}

	/**
	 * Проверяем, есть ли направление в очереди в РС ПроМеда
	 */
	public function checkDirectionInWLQ() {
		$data = $this->ProcessInputData('checkDirectionInWLQ', true);
		if($data === false) { return false; }
		$response =$this->dbmodel->checkDirectionInWLQ($data);
		$this->ProcessModelList($response, true, 'Во врмемя проверки направления произошла ошибка')->ReturnData();
	}

	/**
	 * Проверяем параклиническую услугу на наличие в очереди рабочего списка 
	 */
	public function checkIsInWorkListQueue() {
		$data = $this->ProcessInputData('checkIsInWorkListQueue', true);
		if($data === false) { return false; }
		$response =$this->dbmodel->checkIsInWorkListQueue($data);
		$this->ProcessModelList($response, true, 'Во врмемя проверки направления произошла ошибка')->ReturnData();
	}

	/**
	 * Копия функции клиента на проверку статусов очереди рабочего списка
	 */
	public function checkWorkListQueue() {
		$data = $this->ProcessInputData(null);
		$workList_queue = $this->dbmodel->getWorkList($data);
		$recordArray = [];
		$resultArray = [];

		for($i = 0; $i < count($workList_queue); $i++) {
			$recordObject = array();
			switch($workList_queue[$i]['WorkListStatus_Code']) {
				case 'awaitingDisp':
				case 'errorDisp':
					$recordObject['personInfo'] = $workList_queue[$i];
					$recordObject['operation'] = 'NW';
					array_push($recordArray, $recordObject);
					break;
				case 'awaitingDel':
				case 'errorDel':
					$recordObject['personInfo'] = $workList_queue[$i];
					$recordObject['operation'] = 'CA';
					array_push($recordArray, $recordObject); 
					break;
				case 'changes':
				case 'errorChan':
					$recordObject['personInfo'] = $workList_queue[$i];
					$recordObject['operation'] = 'XO';
					array_push($recordArray, $recordObject); 
					break;
				case 'sent':
					$recordObject['EvnUslugaPar_id'] = $workList_queue[$i]['EvnUslugaPar_id'];
					$recordObject['WorkListQueue_id'] = $workList_queue[$i]['WorkListQueue_id'];
					$recordObject['MedProductCard_id'] = $workList_queue[$i]['MedProductCard_id'];
					$recordObject['LpuEquipmentPacs_id'] = $workList_queue[$i]['LpuEquipmentPacs_id'];
					array_push($resultArray, $recordObject);
					break;
			}
		}

		if(count($recordArray) > 0) {
			$data['Data'] = json_encode($recordArray);
			$recordArray = $this->sentToPacs($data);
		} 

		if(count($resultArray) > 0) {
			$data['Data'] = json_encode($resultArray);
			$resultArray = $this->checkResult($data);
		}
	}

	/**
	 * Отправка сообщения HL7 на PACS-сервер
	 */
	public function sentToPacs($input_data = null) {
		$data = $this->ProcessInputData('sentToPacs', true);
		if($data === false) {
			return false; 
		}

		if(empty($data['Data'])) {
			$data = $input_data;
		}
			
		$response = $this->dbmodel->getDirectionsData($data);
		$updPersonInfo = array();
		for($i = 0; $i < count($response); $i++) {
			$msg = $this->messageAssembly($response[$i]);
			$personInfo = array();
			$personInfo['WorkListQueue_id'] = $response[$i]['WorkListQueue_id'];
			$personInfo['EvnUslugaPar_id'] = $response[$i]['EvnUslugaPar_id'];
			$personInfo['LpuEquipmentPacs_id'] = $response[$i]['LpuEquipmentPacs_id'];
			$personInfo['MedProductCard_id'] = $response[$i]['MedProductCard_id'];
			$personInfo['WorkListStatus_Code'] = $response[$i]['WorkListStatus_Code'];
			$updPersonInfo[$i] = $this->connectToPacs($msg, $response[$i]['PACS_ip_vip'], $personInfo);
			$updPersonInfo[$i]['pmUser_id'] = $data['pmUser_id'];
			$refreshStatus = $this->dbmodel->updRecord($updPersonInfo[$i]);
		}
		
		$errorTransaction = 0;
		$successfullTransaction = 0;

		for ($i = 0; $i < count($response); $i++) {
			$refreshStatus = $this->dbmodel->updRecord($updPersonInfo[$i]);
			if(empty($refreshStatus)) 
				$errorTransaction++;
			else 
				$successfullTransaction++;
		}

		if($errorTransaction == 0) {
			$result = array('msg' => "Заявки были успешно обновлены");
		} else {
			if($errorTransaction == count($response))
				$result = array('msg' => "Не все заявки были успешно обновлены");
			else 
				$result = array('msg' => "Отправка заявок на PACS-сервер не удалась");
		}

		$this->ProcessModelList($result, true, 'При отправке направления в очередь MWL произошла ошибка')->ReturnData();
	}

	/**
	 * Удаление направления из РС ПроМеда
	 */
	public function cancelRecordToDB() {
		$data = $this->ProcessInputData('cancelRecordToDB', true);
		if($data === false) { return false; }
		$response = $this->dbmodel->cancelRecordToDB($data);
		$this->ProcessModelList($response, true, 'При удалении направления из очереди MWL произошла ошибка')->ReturnData();
	}

	/**
	 * Изменение МИ у направления в РС ПроМеда
	 */
	public function updRecordToDB() {
		$data = $this->ProcessInputData('updRecordToDB', true);
		if($data === false) { return false; }
		$response = $this->dbmodel->updRecordToDB($data);
		$this->ProcessModelList($response, true, 'Во время изменения направления в очереди MWL произошла ошибка')->ReturnData();
	}
	
	/**
	 * Проверяем есть ли на службе медицинские изделия с признаком "работа с Рабочим списком"
	 */
	public function getMedProductCardIsWL() {
		$data = $this->ProcessInputData('getMedProductCardIsWL', true);
		if($data === false) { return false; }
		$response = $this->dbmodel->getMedProductCardIsWL($data);
		$this->ProcessModelList($response, true, 'В процессе проверки МИ на службе произошла ошибка')->ReturnData();
	}

	/**
	 * Проверка получения результатов PACS-сервером
	 */
	public function checkResult($input_data) {
		$data = $this->ProcessInputData('cancelRecordToDB', true);
		if($data === false) { return false; }
		
		if(empty($data['Data'])) {
			$data = $input_data;
		}

		$decodeData = json_decode($data['Data'], true);
		$resultMsg = array('msg' => "Получен результат");
		$date = strtotime("-3 DAY");
		$date = $this->getDate('Ymd', $date) . "-" . $this->getDate('Ymd');
		$resultArray = array();
		$result = "";

		$local_pacs = $this->dbmodel->getLocalPacs($data['Lpu_id']);
		if(empty($local_pacs)) return false;
		if(count($local_pacs) > 1) {
			for($i = 0; $i < count($local_pacs); $i++){
				$paramsQueryString = http_build_query(array(
					'PatientID'=> '',
					'PatientName'=>'',
					'PatientBirthDate'=>'',
					'StudyDate'=> $date, 
					'StudyModality'=>'',
					'StudyNumber'=>'',
					'AeTitle'=>$local_pacs['PACS_aet'],
					'HostName'=>$local_pacs['PACS_ip_vip'],
					'Port'=>$local_pacs['PACS_port'],
					'WadoPort'=>$local_pacs['PACS_wado'],
					'DCMProtocol'=>'DICOM'));

				$queryString = 'http://'.PACS_SERVICE_IP.':'.PACS_SERVICE_PORT.'/'.((defined('PACS_SERVICE_NAME'))?PACS_SERVICE_NAME:'DCMWebService').'/rest/PatientStudyInfo?'.$paramsQueryString;

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $queryString);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($ch, CURLOPT_ENCODING, 1);
				$result = curl_exec($ch);
				curl_close($ch);
			}
		} else {
			$paramsQueryString = http_build_query(array(
				'PatientID'=> '',
				'PatientName'=>'',
				'PatientBirthDate'=>'',
				'StudyDate'=> $date, 
				'StudyModality'=>'',
				'StudyNumber'=>'',
				'AeTitle'=>$local_pacs[0]['PACS_aet'],
				'HostName'=>$local_pacs[0]['PACS_ip_vip'],
				'Port'=>$local_pacs[0]['PACS_port'],
				'WadoPort'=>$local_pacs[0]['PACS_wado'],
				'DCMProtocol'=>'DICOM'));
			
			$queryString = 'http://'.PACS_SERVICE_IP.':'.PACS_SERVICE_PORT.'/'.((defined('PACS_SERVICE_NAME'))?PACS_SERVICE_NAME:'DCMWebService').'/rest/PatientStudyInfo?'.$paramsQueryString;
		
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $queryString);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_ENCODING, 1);
			$result = curl_exec($ch);
			curl_close($ch);
		}

		$result = json_decode($result, true)['objectStudy'];

		if(!empty($result)) {
			for ($j = 0; $j < count($result); $j++) {
				if(!empty($result[$j]['accessionNumber'])) {
					for($i = 0; $i < count($decodeData); $i++) {
						if($result[$j]['accessionNumber'] == $decodeData[$i]['EvnUslugaPar_id']) {
							$decodeData[$i]['WorkListStatus_id'] = 5;
							$decodeData[$i]['Study_uid'] = $result[$j]['studyID'];
							$decodeData[$i]['Study_date'] = $result[$j]['studyDate'];
							$decodeData[$i]['Patient_Name'] = $result[$j]['patientName'];
							$decodeData[$i]['pmUser_id'] = $data['pmUser_id'];
							$updStatus = $this->dbmodel->updRecord($decodeData[$i]);
							$link = $this->dbmodel->addLinkOnStudy($decodeData[$i]);
							break;
						}
					}	
				}
			}
		} else {
			$resultMsg = array('msg' => "");
		}
		
		$this->ProcessModelList($resultMsg, true, "Ошибка при запросе результатов у PACS-сервера")->ReturnData();
	}

	/**
	 * Формируем сообщение в формате HL7
	 * @param array $data данные направления
	 * @return string $msg сообщение в формате HL7
	 */
	protected function messageAssembly($response) {
		$msg = array();
		$msg[0] = $this->getMSHsegment($response);
		$msg[1] = $this->getPIDsegment($response);
		$msg[2] = $this->getPV1segment($response);
		$msg[3] = $this->getORCsegment($response);
		$msg[4] = $this->getOBRsegment($response);
		$msg[5] = $this->getZDSsegment($response);
		$msg = join(chr(0x0d), $msg);
		$msg = chr(0x0b) . $msg . chr(0x1c) . chr(0x0d); 
		return $msg;
	}

	/**
	 * Соединяемся с PACS-сервером для отправки сообщения
	 * @param string $msg сообщение в формате HL7 для отправки на PACS-сервер
	 * @param string $pacs_ip интернет адрес PACS-сервера
	 * @param array $config адресные данные PACS-сервера
	 */
	public function connectToPacs($msg, $pacs_ip, $personInfo) {
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

		if(empty($socket)) {
			$read = array('Error_Message' => "Не удлаось создать сокет, попробуйте заного");
		} else {
			$connect = @socket_connect($socket, $pacs_ip, "2575");
			if($connect == true) {
				socket_write($socket, $msg, strlen($msg));
				$read = socket_read($socket, 1024);
				socket_close($socket);
			} else {
				$read = array('Error_Message' => "Не возможно подключиться к PACS-серверу, проверьте настройки подключения.");
			}
		}
		
		if(empty($read)) $read = array('Error_Message' => "Ответ на сообщение по заявке на услугу ". $personInfo['EvnUslugaPar_id'] . " не пришел");
	
		$updPersonInfo = $this->messageParsing($read, $personInfo);
		return $updPersonInfo;
	}

	/**
	* Получаем данные в транслитерации
	* @param string $textMsg строка в кириллическом алфавите
	* @return string $textTranslitereted строка в алфавите латиницы 
	*/
	public function getTranslateText($textMsg) {
		if(empty($textMsg)) {
			return "";
		}
		$textMsg_array = explode(" ", $textMsg);
		$array_translite = $this->dbmodel->getAlphabet();
		if(empty($array_translite)) {
			return false;
		}
		$rusLetter = array();
		$latLetter = array();

		for($i = 0; $i < count($array_translite); $i++) {
			array_push($rusLetter, $array_translite[$i]['WorkListTrans_Letter']);
			array_push($latLetter, trim($array_translite[$i]['WorkListTrans_Latin']));
		}

		for($i = 0; $i < count($textMsg_array); $i++) {
			$textTranslitereted[$i] = str_replace($rusLetter, $latLetter, $textMsg_array[$i]);
		}

		return join("^", $textTranslitereted);
	}

	/**
	* Преобразуем дату
	* @param string $format формат даты
	* @param string $date дата
	* @return date текущая дата и время 
	*/
	function getDate($format, $date = null) {
		if(empty($date)) {
			return date($format);
		} else {
			return date($format, $date);
		}
	}

	/**
	 * Получаем MSH сегмент для сообщения HL7
	 * @param array $dataForSegment данные для формирования сегмента
	 * @return string 
	 */
	protected function getMSHsegment($dataForSegment) {
		$MSH = array_fill(0, 19, "");
		$MSH[0] = "MSH";
		$MSH[1] = "^~\&";
		$MSH[2] = static::MYAETITLE;
		$MSH[3] = static::MYAETITLE;
		$MSH[4] = $dataForSegment['PACS_aet'];
		$MSH[5] = $dataForSegment['MedProductCard_AETitle'];
		$MSH[6] = $this->getDate("YmdHis");
		$MSH[8] = "ORM^O01";
		$MSH[9] = "MSG" . $this->getDate("N") . $dataForSegment['EvnUslugaPar_id'];
		$MSH[10] = "P";
		$MSH[11] = "2.3.1";
		$MSH[14] = "AL";
		$MSH[15] = "NE";
		$MSH[17] = "UNICODE UTF-8";

		$MSH = join("|", $MSH);
		return $MSH;
	}

	/**
	 * Получаем PID сегмент для сообщения HL7
	 * @param array $dataForSegment данные для формирования сегмента
	 * @return string 
	 */
	protected function getPIDsegment($dataForSegment) {
		$PID = array_fill(0, 19, "");
		$sex = "U";
		if($dataForSegment['Sex_Code'] == 1) $sex = "M";
		elseif ($dataForSegment['Sex_Code'] == 2) $sex = "F";
		$PID[0] = "PID";
		$PID[2] = $dataForSegment['Person_id'];
		$PID[3] = $dataForSegment['Person_id'];
		$PID[5] = $this->getTranslateText(mb_strtoupper($dataForSegment['Person_FIO']));
		$PID[7] = $this->getDate('Ymd', strtotime($dataForSegment['Person_BirthDay']));
		$PID[8] = $sex;
		$PID[18] = $dataForSegment['Person_id'];
		
		$PID = join("|", $PID);
		return $PID;
	}
	
	/**
	 * Получаем PV1 сегмент для сообщения HL7
	 * @param array $dataForSegment данные для формирования сегмента
	 * @return string 
	 */
	protected function getPV1segment($dataForSegment) {
		$PV1 = array_fill(0, 20, "");
		$PV1[0] = "PV1";
		$PV1[2] = "O";
		$PV1[6] = $this->getTranslateText(mb_strtoupper($dataForSegment['LpuSection_Name']));
		$PV1[7] = $this->getTranslateText(mb_strtoupper($dataForSegment['Doctor_FIO']));
		$PV1[19] = $dataForSegment['EvnDirection_id'];
		
		$PV1 = join("|", $PV1);
		return $PV1;
	}
	
	/**
	 * Получаем ORC сегмент для сообщения HL7
	 * @param array $dataForSegment данные для формирования сегмента
	 * @return string 
	 */
	protected function getORCsegment($dataForSegment) {
		$ORC = array_fill(0, 19, "");
		$ORC[0] = "ORC";
		$ORC[1] = $dataForSegment['operation'];
		$ORC[2] = $dataForSegment['EvnUslugaPar_id'] . "^" . static::MYAETITLE;
		$ORC[3] = $dataForSegment['PACS_aet'];
		$ORC[5] = "SC";
		$ORC[9] = $this->getDate("YmdHis");
		$ORC[10] = $this->getTranslateText(mb_strtoupper($dataForSegment['Doctor_FIO']));
		if(empty($dataForSegment['TimeTablePar_begTime'])) $ORC[15] = "";
		else $ORC[15] = $this->getDate('YmdHis', strtotime($dataForSegment['TimeTablePar_begTime']));
		$ORC[18] = $dataForSegment['MedProductCard_AETitle'];

		$ORC = join("|", $ORC);
		return $ORC;
	}
	
	/**
	 * Получаем OBR сегмент для сообщения HL7
	 * @param array $dataForSegment данные для формирования сегмента
	 * @return string 
	 */
	protected function getOBRsegment($dataForSegment) {
		$OBR = array_fill(0, 21, "");
		$OBR[0] = "OBR";
		$OBR[2] = $dataForSegment['EvnUslugaPar_id'] . "^" . static::MYAETITLE;
		$OBR[4] = $this->getUniversalServiceID($dataForSegment['UslugaComplex_Code'], $dataForSegment['UslugaComplex_Name']);
		if(empty($dataForSegment['TimeTablePar_begTime'])) $OBR[7] = $this->getDate("YmdHi");
		else $OBR[7] = $this->getDate('YmdHis', strtotime($dataForSegment['TimeTablePar_begTime']));
		$OBR[8] = $this->getTranslateText($dataForSegment['MedProductClass_Name']);
		$OBR[18] = $dataForSegment['EvnUslugaPar_id'];
		$OBR[19] = $dataForSegment['EvnUslugaPar_id'];
		$OBR[20] = $dataForSegment['EvnUslugaPar_id'];
		$OBR[24] = "OT";
		$OBR[25] = $dataForSegment['MedProductCard_AETitle'];
		
		$OBR = join("|", $OBR);
		return $OBR;
	}
	
	/**
	 * Получаем ZDS сегмент для сообщения HL7
	 * @param array $dataForSegment данные для формирования сегмента
	 * @return string 
	 */
	protected function getZDSsegment($dataForSegment) {
		$ZDS = array_fill(0, 2, "");;
		$ZDS[0] = "ZDS";
		$ZDS[1] = "1.2.4.0.13.1." . $this->getDate("YmdHi") . ".1^100^Application^DICOM";
		$ZDS = join("|", $ZDS);
		return $ZDS;
	}

	/**
	* Формируем уникальный идентификатор сервиса
	* @param string $code код услуги
	* @param string $name название услуги
	* @return string 
	*/
	public function getUniversalServiceID($code, $name) {
		$universalServiceId = $code . "^" . $this->getTranslateText(mb_strtoupper($name));
		$universalServiceId .= "^^" . $code . "^" . $this->getTranslateText(mb_strtoupper($name));
		$universalServiceId .= "^" . static::MYAETITLE;
		return $universalServiceId;
	}

	/**
	* Парсим полученное ответное сообщение
	* @param string $responseMsg полученное ответное сообщение
	* @return array пары идентификатора услуги и статус (error or take)
	*/
	public function messageParsing($responseMsg, $personInfo) {
		$status = "error";
		$textMsg = "";

		if(!is_array($responseMsg) && empty($responseMsg['Error_Message'])) {
			$arrayMsg = explode("|", $responseMsg);
			$typeMsg = $arrayMsg[18];
			$textMsg = $arrayMsg[19];
			switch ($typeMsg) {
				case 'AA':
				case 'CA':
					$status = "take";	
					break;
			}
		} else {
			$textMsg = $responseMsg['Error_Message'];
		}

		$personInfo['WorkListStatus_id'] = $this->setWorkListStatus($status, $personInfo['WorkListStatus_Code']);
		$personInfo['TextMessageResponse'] = $textMsg;
		return $personInfo;
	}

	/**
	* Получаем новый статус заявки в рабочем списке
	* @param string $status статус отправки сообщения на PACS-сервер
	* @param string $WorkListStatus_Code код статуса заказа в рабочем списке
	* @return integer $WorkListStatus_id идентификатор статуса заявки в рабочем списке
	*/
	public function setWorkListStatus($status, $WorkListStatus_Code) {
		if($status == "error") {
			switch ($WorkListStatus_Code) {
				case 'awaitingDisp':
				case 'errorDisp':
					$WorkListStatus_id = 7;
					break;
				case 'awaitingDel':
				case 'errorDel':
					$WorkListStatus_id = 8;
					break;
				case 'changes':
				case 'errorChan':
					$WorkListStatus_id = 9;
					break;
			}
		} elseif ($status == "take") {
			switch ($WorkListStatus_Code) {
				case 'awaitingDisp':
				case 'errorDisp':
				case 'changes':
				case 'errorChan':
					$WorkListStatus_id = 4;
					break;
				case 'awaitingDel':
				case 'errorDel':
					$WorkListStatus_id = 6;
					break;
				}
		}
		return $WorkListStatus_id;
	}
 }