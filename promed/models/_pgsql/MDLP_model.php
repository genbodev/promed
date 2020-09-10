<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Класс модели для работы по иммунопрофилактике
 *
 * @author		Nigmatullin Tagir (Ufa) 26.06.2020
 * 
 *
 */

class MDLP_model extends CI_Model {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	
	function api_request($method, $action, $body=null) {

		$Org_id = getSessionParams()['session']['org_id']; 
				
		$queryParams = array();
		
		//$dbrep = $this->load->database('bdwork', true);

		$dbrep = $this->db;
		
		
		$query = "
				SELECT  count(*) as kol, 1 as RV_isMDLP FROM    sys.servers
					where name = 'progress'
				";
				  
			$result = $dbrep->query($query);
			
			$rv = $result->result('array')[0];
			if ($rv['kol'] == 0){
				return $rv;
			}
			
			
	
			$query = "
				Select 
					rv_ip
					,rv_port
					,rv_model
					,rv_login
					,rv_pass
					,COALESCE(RV_isMDLP, 1) RV_isMDLP
			   from [PROGRESS].[PromedWeb].bf.bf_aCode
				  where  Org_id = {$Org_id}
					  limit 1
				";

			$result = $dbrep->query($query, [
				'Org_id' => $Org_id
			]);

			if (empty($result)) {
				throw new Exception('Не найдены параметры РВ');
			}
			$resp = $result->result('array');
			if (empty($resp[0]['rv_ip'])) {
				throw new Exception('Не найдены параметры РВ');
			}
			$rv = $resp[0];
			//var_dump($rv); exit;
			$ip = $rv['rv_ip'];
			$port = $rv['rv_port'];
			$login = $rv['rv_login'];
			$password = $rv['rv_pass'];
			
			if ($rv['RV_isMDLP'] == 1){
				return $rv;
			}
		$full_url = "https://$ip:$port/v1/$action";
		//echo "$ip = {$ip}, $port = {$port}, $full_url = " .$full_url .PHP_EOL;
    $options = array(
        CURLOPT_URL => $full_url,
        CURLOPT_USERPWD => "$login:$password",
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => "NetAngels API client 0.1",
		//  Отключаем проверку сертификата
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => false
    );
	
	if ($body) {
		 $options[CURLOPT_POSTFIELDS] = $body;
	}
	//var_dump($options); exit;
    $ch = curl_init();
    curl_setopt_array($ch, $options);

    $result = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ( $status > 399 ) {
		if ($body) {
			$result .= "<br/> $body";
		}
        throw new Exception("Exception $status: $result");
    }
//	$response = array();
//	$response = array_merge($response, $result);
    return $result;
}

	/**
	 * Метод «Получить информацию об устройстве» 
	 */

	public function GetInformationRv($data) {
		$connectionType = '';
		$queryParams = array();
		if (!$data){
			return false;
		}
		
		$result = $this->api_request('GET', 'deviceInfo');
		//echo '<pre>' . print_r($result, 1) . '</pre>';  exit;
		if (empty($result) ) {
			//echo '$result пустой' .PHP_EOL;
			return array(array('success' => false, 'Error_Msg' => 'РВ не найден'));
		}
		
		if (isset($result['RV_isMDLP']) && $result['RV_isMDLP'] == '1') {
			return array(array('success' => true, 'data' => $result));
		}
		
		$result = json_decode($result);
		//echo '<pre>' . print_r($result, 1) . '</pre>';  exit;
		$response = array();
		$d = $result->devices[0];
		
		$response['RV_isMDLP'] = 2;
				
		if (!empty($d->connectionType)) {
			$connectionType = $d->connectionType;
		}
		
		$response['connectionType'] = $connectionType;
		
		if (!empty($d->deviceSerialNumber)) {
			$response['connectionType'] = $d->connectionType;
		}
		if (!empty($d->endDateRegistration)) {
			$response['endDateRegistration'] = $d->endDateRegistration;
		}
		if (!empty($d->id)) {
			$response['id'] = $d->id;
		}
		if (!empty($d->modelInfo)) {
			$response['modelInfo'] = $d->modelInfo;
		}
		if (!empty($d->moduleSerialNumber)) {
			$response['moduleSerialNumber'] = $d->moduleSerialNumber;
		}
		if (!empty($d->deviceSerialNumber)) {
			$response['deviceSerialNumber'] = $d->deviceSerialNumber;
		}
		if (!empty($d->softwareVersion)) {
			$response['softwareVersion'] = $d->softwareVersion;
		}
		if (!empty($d->startDateRegistration)) {
			$response['startDateRegistration'] = $d->startDateRegistration;
		}
		if (!empty($d->suid)) {
			$response['suid'] = $d->suid;
		}
		if (!empty($d->timeBlock)) {
			$response['timeBlock'] = str_replace('Z', '', str_replace('T', '', $d->timeBlock));
		}
		if (!empty($d->timeBlock)) {
			$response['timeBlock'] = str_replace('Z', '', str_replace('T', '', $d->timeBlock));
		}
				
		return array(array('success' => true, 'data' => $response));
        
	 }
	 
	/**
	* 	Метод «Записать задание в очередь»
	 * 
	 * Значение параметра $data['type'] :
			egistration – регистрация РВ;
			checkMarks – проверка кода маркировки;
			registerMarksByRequisites – регистрация выбытия кодов маркировки по
					реквизитам документа-основания.
		 
	*/
	public function QueueUp($data) {
		
		$queryParams = array();
		if (!$data){
			return false;
		}
		if (empty($data['type'])) {
			return false;
		}
		if (empty($data['rvRequestId'])) {
			$rvRequestId = com_create_guid();
		}
		else {
			$rvRequestId = $data['rvRequestId'];
		}
		
		 switch($data['type']) {
			 case 'checkMarks': // проверка кода маркировки; 
				//$json_args = json_encode(marksJSON);
				$marks_array = json_decode($data['marksJSON']);
				 $marks = '';
				 $i = 0;
				 foreach ($marks_array as $mark) {
					 $i++;
					 if ($i > 1) {
						 $marks .= ',
							 ';
					 };
					 $marks .='"' .$i .'" :{ 
						 "mark": "' .base64_encode($mark->DrugPackageBarCode_BarCode) .'"					 
						 }';			 
				 }
				 $json = '
								"type": "'.$data['type'] .'",
								"localCheck": true,
								"marks": {
									'. $marks .'
								}';
				$body = '{
							"rvRequestId": "' .$rvRequestId .'",
							"request":{ 	
								' .$json .'
							}
						}';		 
		
						
				//  Постановка задания
				$res = $this->api_request('POST', 'requests', $body);
				
				//  Запросить статус задания
				$result = $this->api_request('GET', 'requests/' .$rvRequestId);
				$result = json_decode($result);
				//echo '<pre>' . print_r($result, 1) . '</pre>'; exit;
				
				//requests/{rvRequestId}[?deviceId={deviceIdvalue}] 
			
				 return $result;
				break;
			case 'registerMarksByRequisites': // регистрация выбытия кодов маркировки по реквизитам документа-основания
				if (empty($data['DocumentUc_id']) && empty($data['EvnRecept_id'])) {
					return false;
				}
				$aBody = array();
				$request = array();
				$documentOut = array();
				$where = '';
				if (!empty($data['DocumentUc_id'])) {
					$where .= 'dus.DocumentUc_id = :DocumentUc_id';
					$queryParams ['DocumentUc_id'] = $data['DocumentUc_id'];
				}
				else if (!empty($data['EvnRecept_id'])) {
					$where .= 'dus.EvnRecept_id = :EvnRecept_id';
					$queryParams ['EvnRecept_id'] = $data['EvnRecept_id'];
				};
				$query = "
					Select 
						case 
							when DrugDocumentType_id = 11
								then 1
							else 0
						end as [type],
						case 
							when DrugDocumentType_id = 11
								then '0504204'
							else null
						end as code,
						'Требование накладная' as codeName,
						convert(varchar, du.DocumentUc_didDate, 126) + 'Z' [date],
						dus.EvnRecept_Ser as series,
						dus.EvnRecept_Num as number
					from v_DocumentUc du
						LEFT JOIN LATERAL (Select er.EvnRecept_Ser, er.EvnRecept_Num, dus.DocumentUc_id  
							from v_documentUcStr dus 
							join v_EvnRecept er on er.EvnRecept_id = dus.EvnRecept_id
							 where {$where}
								 limit 1
						) dus
						where  dus.DocumentUc_id = du.DocumentUc_id;
					";
				
				
				//var_dump($data); exit;
				
					//$dbrep = $this->load->database('bdwork', true);
 
					$dbrep = $this->db;
		
		
				$result = $dbrep->query($query, $queryParams);
				
				$documentOut = $result->result('array');
				
				$request['type'] = $data['type'];
				$request['documentOut'] = $documentOut[0];
				
				$query = "
					Select -- '01' + DrugPackageBarCode_GTIN + DrugPackageBarCode_IndNum as mark
						substring(DrugPackageBarCode_BarCode, 1, 31) + '[GS]' + substring(DrugPackageBarCode_BarCode, 32, 6) + '[GS]'
							 + substring(DrugPackageBarCode_BarCode, 38, len(DrugPackageBarCode_BarCode)) as mark
					from DrugPackageBarCode bc 
						inner join v_DocumentUcStr dus on dus.DocumentUcStr_id = bc.DocumentUcStr_id
						inner join v_DocumentUc du on du.DocumentUc_id = dus.DocumentUc_id
							and DrugDocumentType_id in(11)
							and du.DrugDocumentStatus_id in (2, 7)
							where {$where};
					";
				
				//echo getDebugSql($query, $queryParams); exit;
				
				$result = $dbrep->query($query, $queryParams);
				
				if (is_object($result)) {
					$marks_array = $result->result('array');
					if (count($marks_array) > 0) {
						$aMarks = array();
						$i = 0;
						foreach ($marks_array as $mark) {
							$i++;
							$mark['mark'] = str_replace('[GS]', chr(29), $mark['mark']);
							$mark['mark'] = base64_encode($mark['mark']);
							$aMarks ["{$i}"] = $mark;
						}
						$request['marks'] = $aMarks;

						$aBody['rvRequestId'] = $rvRequestId;
						$aBody['request'] = $request;

						$body = json_encode($aBody);

						//echo '<pre>' . print_r($body, 1) . '</pre>'; exit;
						//  Постановка задания
						$res = $this->api_request('POST', 'requests', $body);

						$query = "
					with t as (
					Select DrugPackageBarCode_id
					from dbo.DrugPackageBarCode bc 
						inner join v_DocumentUcStr dus on dus.DocumentUcStr_id = bc.DocumentUcStr_id
						inner join v_DocumentUc du on du.DocumentUc_id = dus.DocumentUc_id
							and DrugDocumentType_id in(11)
							and du.DrugDocumentStatus_id in (2, 7)
							where {$where}
					)
					update dbo.DrugPackageBarCode
						set DrugPackageBarCode_ValidCode = '{$rvRequestId}'
					from dbo.DrugPackageBarCode bc
						join t on t.DrugPackageBarCode_id = bc.DrugPackageBarCode_id
					";

						$result = $dbrep->query($query, $queryParams);

						//  Запросить статус задания
						sleep(2);
						$result = $this->api_request('GET', 'requests/' . $rvRequestId);
						$result = json_decode($result);
						//echo '<pre>' . print_r($result, 1) . '</pre>';
						//exit;
						$set = '';
						if (isset($result->results->result->mdlpRequestId)) {
							$str = $result->results->result->mdlpRequestId . ' (СУЗ)';
							$set = ", DrugPackageBarCode_ValidCode = '{$str}'";
						}

						if (isset($result->results->status)) {
							$set = ", DrugPackageBarCode_Packer = '{$result->results->status}'";
						}
						if ($set != '') {
							$query = "
						with t as (
						Select DrugPackageBarCode_id
						from dbo.DrugPackageBarCode bc 
							inner join v_DocumentUcStr dus  on dus.DocumentUcStr_id = bc.DocumentUcStr_id
							inner join v_DocumentUc du  on du.DocumentUc_id = dus.DocumentUc_id
								and DrugDocumentType_id in(11)
								and du.DrugDocumentStatus_id in (2, 7)
								where {$where}
						)
						update dbo.DrugPackageBarCode
							set DrugPackageBarCode_updDT = getDate()
							{$set}
						from dbo.DrugPackageBarCode bc
							join t on t.DrugPackageBarCode_id = bc.DrugPackageBarCode_id
						";

							$result = $dbrep->query($query, $queryParams);
						}
					}
				}
				return array('success' => true);
				break;
			 default:
				return false;
				 break;			 
		 }		
	}
	 
	
	/**
	 * регистрация выбытия кодов маркировки по списку рецептов 
	 */
	
	public function QueueUpRegisterMarksList($data) {
		
		$queryParams = array();
		$Org_id = getSessionParams()['session']['org_id'];
		
		$query = "
				with er as (
					Select  distinct dus.EvnRecept_id, dus.DocumentUcStr_id
						from DrugPackageBarCode bc
							join v_DocumentUcStr dus on dus.DocumentUcStr_id = bc.DocumentUcStr_id
							join v_DocumentUc du on du.DocumentUc_id = dus.DocumentUc_id
						where  du.DrugDocumentType_id = 11
							and du.DrugDocumentStatus_id = 2
							and (DrugPackageBarCode_ValidCode is  null
									or  DrugPackageBarCode_ValidCode = '')
							and du.Org_id = {$Org_id}
					)
					Select NEWID() as rvRequestId, EvnRecept_id from er
						order by DocumentUcStr_id
						limit 100
			";
							
		//$dbrep = $this->load->database('bdwork', true);

		$dbrep = $this->db;					
							
		$result = $dbrep->query($query, $queryParams);
		
		$response = $result->result('array');

		$rvRequestId = $data['rvRequestId'];
		foreach ( $response as $row ) {
			$params = array();
			$params['rvRequestId'] = $row['rvRequestId'];
			$params['EvnRecept_id'] = $row['EvnRecept_id'];
			$params['type'] = 'registerMarksByRequisites';
						
			$this->QueueUp($params);
			
		}
		
		return array('success' => true);
	} 
	
}
?>
