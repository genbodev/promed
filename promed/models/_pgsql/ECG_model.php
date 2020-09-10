<?php defined('BASEPATH') or die('No direct script access allowed');
/**
 * ECG_model - модель
 *
 * @package     ECG
 * @access      public
 * @author		ApaevAV
 * @version     06.11.2019
 *
 */

class ECG_model extends swPGModel
{
	protected $ecgConfig = null;
	/**
	 * Конструктор
	 */
	public function __construct()
	{
		parent::__construct();
		$this->ecgConfig = $this->config->item('ECG');
	}
	/**
	 * Проверка подключения к сервису AI Server Service
	 */
	function connect($data, $tryConnection = false) {
		$connected = false;
		$url = $data['ecg_server'];
		$port = $data['ecg_port'];
		$timeout = 5;
		if ($tryConnection) {
			flush();
			if(($socket = fsockopen($url, $port, $errno, $errstr, $timeout))!==false) {
				$connected = true;
				fclose($socket);
				return array('success'=>true, "data"=>'Сервис доступен');
		
			} else {
				return array('success'=>false, 'Error_Msg'=>'Невозвможно соединиться с сервисом AI Server Service. Обратитесь к администратору');
			}
		}

	}
	/*
	* Формируем xml для службы AI_ServerService
	*/
	function getXmlForTransfer($data)
	{
		$this->load->library('parser');
		$session = getSessionParams();

		$filter = "";
		$queryParams = array();

		if (!empty($data['Person_id'])) {
			$filter .= " and PS.Person_id = :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];
		}
		if (!empty($data['EvnUslugaPar_id'])) {
			$filter .= " and EvnUslugaPar.EvnUslugaPar_id = :EvnUslugaPar_id";
			$queryParams['EvnUslugaPar_id'] = $data['EvnUslugaPar_id'];
		}

		$resp = $this->queryResult("
			select 
				EvnUslugaPar.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				EFR.EvnFuncRequest_id as \"EvnFuncRequest_id\",
				ED.EvnDirection_Num as \"EvnDirection_Num\",	
				ED.Person_id as \"Person_id\",
				ED.PersonEvn_id as \"PersonEvn_id\",
				ED.Server_id as \"Server_id\",
				PS.Person_SurName as \"Person_SurName\",
				coalesce(PS.Person_FirName, '') as \"Person_FirName\",
				coalesce(PS.Person_SecName,'') as \"Person_SecName\",
				to_char(PS.Person_BirthDay, 'yyyy-MM-dd') as \"Person_BirthDay\",
				coalesce(dbo.Age2(PS.Person_BirthDay, dbo.tzGetDate()), '') as \"Person_Age\",
				case when PS.Sex_id = 2 then 'ж' 
					when PS.Sex_id = 1 then 'м' else '?' end as \"Sex\",
				case when coalesce(ed.EvnDirection_IsCito, 1) = 1 then 0 else 1 end as \"EvnDirection_IsCito\"
			from	v_EvnFuncRequest efr
				inner join v_EvnDirection_all ed on ed.EvnDirection_id = efr.EvnFuncRequest_pid and (ed.TimetableResource_id is not null or ed.EvnQueue_id is not null) -- только записанные или из очереди
				left join v_PersonState PS on PS.Person_id = ED.Person_id
				left join lateral (
					select EvnUslugaPar_id from v_EvnUslugaPar where EvnDirection_id = ED.EvnDirection_id limit 1
				) EvnUslugaPar on true
			where 
				(1=1)
				{$filter}
			limit 1
		", $queryParams);

		$xml = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:web=\"http://webservice.ates.com/\">\n";
		$xml .= "<soapenv:Header/>\n";
		$xml .= "<soapenv:Body>\n";
		$xml .= "<web:sendMessages>\n";

		foreach ($resp as $item) {
			$xml .= $this->parser->parse('export_xml/xml_ecg', array(
				//отправитель
				'Sender_Name' => $session['pmUser_id'],
				//Информаия о пациенте
				'Person_id' => $item['Person_id'],
				'Patient_SourceID' => $item['EvnFuncRequest_id'],
				'Code' => $item['EvnUslugaPar_id'],
				'Person_SurName' => $item['Person_SurName'],
				'Person_FirName' => !empty($item['Person_FirName']) ? $item['Person_FirName'] : '',
				'Person_SecName' => !empty($item['Person_SecName']) ? $item['Person_SecName'] : '',
				'Comment' => !empty($item['EvnDirection_Descr']) ? $item['EvnDirection_Descr'] : '',
				'Person_BirthDay' => $item['Person_BirthDay'],
				'Sex' => $item['Sex'],
				//Cito
				'DoExam' => $item['EvnDirection_IsCito'],

			), true);
		}

		$xml .= "</web:sendMessages>\n";
		$xml .= "</soapenv:Body>\n";
		$xml .= "</soapenv:Envelope>";

		$PartData = base64_encode($xml);
		return array('length'=>strlen($PartData), "xmlbase64"=>$PartData);
	}
}
