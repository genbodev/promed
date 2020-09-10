<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ServiceERS_model - модель для синхронизации данных по ЭРС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			ServiceERS
 * @access			public
 * @copyright		Copyright (c) 2019 Swan Ltd.
 */
class SoapClientExt extends SoapClient {
	public $customXml = null;
	public $lastRequest = null;

	/**
	 * Устаналивает кастомную XML для отправки в сервис
	 */
	public function setCustomXml($customXml) {
		$this->customXml = $customXml;
		return $this;
	}

	/**
	 * Получение последнего запроса
	 */
	public function __getLastRequest() {
		return $this->lastRequest;
	}

	/**
	 * Выполнение SOAP запроса
	 */
	public function __doRequest($request, $location, $action, $version, $one_way = 0) {
		if (!empty($this->customXml)) {
			$request = $this->customXml;
		}

		$this->lastRequest = $request;

		return parent::__doRequest($request, $location, $action, $version, $one_way);
	}
}

class ServiceERS_model extends SwModel {
	
	protected $host;
	protected $port;
	protected $user;
	protected $password;
	protected $ServiceList_id;

	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();

		/*$this->load->model('ServiceList_model');
		$this->load->helper('ServiceListLog');

		$this->load->model('ObjectSynchronLog_model');
		$this->ObjectSynchronLog_model->setServiceSysNick('ERSKrym');

		$this->load->model('ObjectSynchronLog_model');
		$this->ServiceList_id = $this->ServiceList_model->getServiceListId('AISInfoSup');
		if (empty($this->ServiceList_id)) {
			throw new Exception('Не найден сервис AISInfoSup в stg.ServiceList');
		}*/
		
		$this->load->helper('xml');

		$config = $this->config->item('ERS');
		$this->host = $config['host'];
		$this->port = $config['port'];
		$this->user = $config['user'];
		$this->password = $config['password'];
	}
	
	/* ----- различние вспомоательные методы получения данных ----- */
	
	
	/**
	 *	Сведения об используемом ПО 
	 */
	function getSystemInfo() {
		return [
			'specVersion' => '1.0',
			'software' => 'fss-mo',
			'softwareVerison' => '3.1'
		];
	}
	
	/**
	 *	Сведения о МО
	 */
	function getMedicalOrganization($evners_id) {
		
		return $this->getFirstRowFromQuery("
			select top 1
				ee.EvnERS_OrgName as orgName,
				ee.EvnERS_OrgINN as inn,
				ee.EvnERS_OrgOGRN as ogrn,
				ee.EvnERS_OrgKPP as kpp,
				lfct.LpuFSSContractType_Code as contractServiceType,
				lfc.LpuFssConrtact_Num as contractNumber,
				lfc.LpuFssConrtact_begDate as conractDate
			from v_EvnERS ee (nolock)
				left join LpuFssContractType lfct (nolock) on lfct.LpuFSSContractType_id = ee.LpuFSSContractType_id
				outer apply (
					select top 1 
						lfc.LpuFssConrtact_Num,
						convert(varchar(10), lfc.LpuFssConrtact_begDate, 120) as LpuFssConrtact_begDate
					from LpuFssContract lfc (nolock)
					where lfc.Lpu_id = ee.Lpu_id
					order by lfc.LpuFssConrtact_begDate desc
				) lfc
			where 
				ee.EvnERS_id = :EvnERS_id
		", ['EvnERS_id' => $evners_id]);
	}
	
	/**
	 *	Сведения о МО
	 */
	function getMedicalOrganizationShort($evners_id) {
		
		return $this->getFirstRowFromQuery("
			select top 1
				ee.EvnERS_OrgName as orgName,
				ee.EvnERS_OrgINN as inn,
				ee.EvnERS_OrgOGRN as ogrn,
				ee.EvnERS_OrgKPP as kpp,
				lfc.LpuFssConrtact_Num as contractNumber,
				lfc.LpuFssConrtact_begDate as conractDate
			from v_EvnERS ee (nolock)
				outer apply (
					select top 1 
						lfc.LpuFssConrtact_Num,
						convert(varchar(10), lfc.LpuFssConrtact_begDate, 120) as LpuFssConrtact_begDate
					from LpuFssContract lfc (nolock)
					where lfc.Lpu_id = ee.Lpu_id
					order by lfc.LpuFssConrtact_begDate desc
				) lfc
			where 
				ee.EvnERS_id = :EvnERS_id
		", ['EvnERS_id' => $evners_id]);
	}
	
	/**
	 *	Сведения о получателе услуг 
	 */
	function getFullPersonInfo($data) {
		
		$pd = $this->getFirstRowFromQuery("
			select top 1
				convert(varchar(10), ps.Person_BirthDay, 120) as birthdate,
				ps.Person_FirName as firstName,
				ps.Person_SurName as secondName,
				ps.Person_SecName as patronymic,
				p.Polis_Num as insuranceNumber,
				convert(varchar(10), p.Polis_begDate, 120) as beginDate,
				ps.Person_Snils as snils,
				DT.DocumentType_Code as kind,
				D.Document_Num as docNumber,
				D.Document_Ser as docSerie,
				convert(varchar(10), D.Document_begDate, 120) as docIssueDate,
				OD.Org_Name as docIssuer,
				pa.Address_Address as residenceAddress
			from v_PersonState ps (nolock)
				left join v_Polis p (nolock) on p.Polis_id = ps.Polis_id
				left join v_Document d with (nolock) on d.Document_id = ps.Document_id
				left join DocumentType DT with (nolock) on DT.DocumentType_id = D.DocumentType_id
				left join v_OrgDep OD with (nolock) on OD.OrgDep_id = D.OrgDep_id
				left join v_Address pa with (nolock) on pa.Address_id = isnull(ps.PAddress_id, ps.UAddress_id)
			where ps.Person_id = :Person_id
		", ['Person_id' => $data['Person_id']]);
		
		if (!$pd) return false;
		
		$person = [
			'birthdate' => $pd['birthdate'],
			'firstName' => $pd['firstName'],
			'secondName' => $pd['secondName'],
			'patronymic' => $pd['patronymic']
		];
		
		if (!empty($pd['insuranceNumber'])) {
			$person['medicalInsurance'] = ['medicalInsurance' => [
				'insuranceNumber' => $pd['insuranceNumber'],
				'beginDate' => $pd['beginDate']
			]];
		} else {
			$person['medicalInsurance'] = ['noDataReason' => isset($data['EvnERS_PolisNoReason']) ? $data['EvnERS_PolisNoReason'] : 'отсутствует'];
		}
		
		$person['snils'] = !empty($pd['snils'])
			? ['snils' => $pd['snils']]
			: ['noDataReason' => isset($data['EvnERS_SnilsNoReason']) ? $data['EvnERS_SnilsNoReason'] : 'не предоставлен'];
		
		if (!empty($pd['docNumber'])) {
			$person['identifyingDocument'] = ['idDoc' => [
				'kind' => $pd['kind'],
				'docNumber' => $pd['docNumber'],
				'docSerie' => $pd['docSerie'],
				'docIssueDate' => $pd['docIssueDate'],
				'docIssuer' => $pd['docIssuer']
			]];
		} else {
			$person['identifyingDocument'] = ['noDataReason' => isset($data['EvnERS_DocNoReason']) ? $data['EvnERS_DocNoReason'] : 'не предоставлен'];
		}
		
		$person['residenceAddress'] = !empty($pd['residenceAddress'])
			? ['residenceAddress' => $pd['residenceAddress']]
			: ['noDataReason' => isset($data['EvnERS_AddressNoReason']) ? $data['EvnERS_AddressNoReason'] : ''];
			
		return $person;
	}
	
	/**
	 *	Сведения о новорожденных
	 */
	function getNewbornInfo($evners_id) {
		
		$newborn = $this->queryResult("
			select
				enb.ErsNewborn_Gender gender,
				enb.ErsNewborn_Height as height,
				enb.ErsNewborn_Weight as weight,
				enb.ErsNewborn_DeathReason as deathReason
			from ErsNewborn enb (nolock)
			where enb.EvnERS_id = :EvnERS_id
		", ['EvnERS_id' => $evners_id]);
		
		foreach($newborn as &$nb) {
			$nb = ['storage' => $nb];
		}
		
		return $newborn;
	}
	
	/**
	 *	Сведения о детях
	 */
	function getChildInfo($evners_id) {
		
		$children = $this->queryResult("
			select
				convert(varchar(10), ps.Person_BirthDay, 120) as birthdate,
				ps.Person_FirName as firstName,
				ps.Person_SurName as secondName,
				ps.Person_SecName as patronymic,
				ps.Polis_Num as medicalInsuranceNumber,
				convert(varchar(10), eci.ERSChildInfo_WatchBegDate, 120) as watchBeginDate,
				convert(varchar(10), eci.ERSChildInfo_fWatchEndDate, 120) as watchEndDate
			from v_PersonState ps (nolock)
				inner join ErsChildInfo eci (nolock) on eci.Person_id = ps.Person_id
				inner join EvnErsChild eec (nolock) on eec.EvnERSChild_id = eci.EvnERSChild_id
			where eec.EvnErs_id = :EvnErs_id
		", ['EvnErs_id' => $evners_id]);
		
		foreach($children as &$cn) {
			$cn = ['storage' => $cn];
		}
		
		return $children;
	}
	
	/**
	 *	Сведения о детях
	 */
	function getChildShortInfo($evners_id) {
		
		$children = $this->queryResult("
			select
				convert(varchar(10), ps.Person_BirthDay, 120) as birthdate,
				ps.Person_FirName as firstName,
				ps.Person_SurName as secondName,
				ps.Person_SecName as patronymic,
				ps.Polis_Num as medicalInsuranceNumber,
				convert(varchar(10), eci.ERSChildInfo_WatchBegDate, 120) as registerDate
			from v_PersonState ps (nolock)
				inner join ErsChildInfo eci (nolock) on eci.Person_id = ps.Person_id
				inner join EvnErsChild eec (nolock) on eec.EvnERSChild_id = eci.EvnERSChild_id
			where eec.EvnErs_id = :EvnErs_id
		", ['EvnErs_id' => $evners_id]);
		
		foreach($children as &$cn) {
			$cn = ['storage' => $cn];
		}
		
		return $children;
	}
	
	/**
	 *	Счет
	 */
	function getBillInfo($evners_id) {
		
		return $this->queryResult("
			select
				convert(varchar(10), er.EvnERS_OrgDogNum, 120) as contractDate,
				er.EvnERS_OrgDogDate as contractNumber,
				erb.EvnERSBill_Name as billName,
				erb.EvnERSBill_Number as billNumber,
				convert(varchar(10), erb.EvnERSBill_Date, 120) as billDate,
				erb.EvnERSBill_BankAccount as bankCheckingAcc,
				erb.EvnERSBill_BankName as bankName,
				erb.EvnERSBill_BankBIK as bankBIK,
				erb.EvnERSBill_CorrAccount as bankCorrAcc,
				erb.EvnERSBill_BillAmount as billAmount
			from EvnErsBill erb (nolock)
			inner join v_EvnErs er (nolock) on er.EvnErs_id = erb.EvnErs_id
			where erb.EvnErs_id = :EvnErs_id
		", ['EvnErs_id' => $evners_id]);
	}
	
	/**
	 *	Счет
	 */
	function getBillInfoShort($evners_id) {
		
		return $this->queryResult("
			select
				erb.EvnERSBill_Number as billNumber,
				convert(varchar(10), erb.EvnERSBill_Date, 120) as billDate
			from EvnErsBill erb (nolock)
			where erb.EvnErs_id = :EvnErs_id
		", ['EvnErs_id' => $evners_id]);
	}


	/* ----- основные методы ----- */
	
	
	/**
	 *	Метод предназначен для формирования нового ЭРС или получения номера ЭРС, сформированного ранее
	 */
	function createErs($evners_id) {
		
		$res = $this->getFirstRowFromQuery("
			select 
				ee.EvnErs_id,
				ee.Person_id,
				ee.EvnERS_PolisNoReason,
				ee.EvnERS_SnilsNoReason,
				ee.EvnERS_DocNoReason,
				ee.EvnERS_AddressNoReason
			from v_EvnErs ee (nolock) 
			where ee.EvnErs_id = :EvnErs_id
		", ['EvnErs_id' => $evners_id]);
		
		$data = [
			'systemInfo' => $this->getSystemInfo(),
			'medicalOrganization' => $this->getMedicalOrganization($evners_id),
			'person' => $this->getFullPersonInfo($res),
			'registerDate' => 'дата'
		];
		
		$xml = ArrayToXml($data, 'createErsRequest');
		
		
		$xml = simplexml_load_string($xml);
		$xml->addAttribute("xmlns", 'http://gtw.ws.fss.ru/openservice/ers');
		$xml = $xml->asXML();
		
		echo $xml;
	}
	
	
	/**
	 *	Метод предназначен для предоставления данных от МО в ФСС о Талоне 2 ЭРС 
	 */
	function putTicket1($evners_id) {
		
		$res = $this->getFirstRowFromQuery("
			select
				eet.EvnERSTicket_id,
				eet.Person_id,
				erq.ERSRequest_ERSNumber as ersNumber,
				convert(varchar(10), eet.EvnERSTicket_setDate, 120) as createDate,
				'' as pregnancyTimeOnRegisterDate,
				convert(varchar(10), eet.EvnERSTicket_PregnancyPutTime, 120) as pregnancyTimeOnPutDate,
				eet.EvnERSTicket_IsMultiplePregnancy as isMultiplePregnancy,
				eet.EvnERSTicket_StickNumber as lnNumber,
				eet.EvnERSTicket_CardNumber as exchangeCardNumber,
				convert(varchar(10), eet.EvnERSTicket_CardDate) as exchangeCardDate,
				eet.EvnERSTicket_PolisNoReason,
				eet.EvnERSTicket_SnilsNoReason,
				eet.EvnERSTicket_DocNoReason,
				eet.EvnERSTicket_AddressNoReason
			from v_EvnERSTicket eet (nolock)
				inner join v_ErsRequest erq (nolock) on erq.ErsRequest_id = eet.ErsRequest_id
			where erq.EvnErs_id = :EvnErs_id
		", ['EvnErs_id' => $evners_id]);

		$data = [
			'systemInfo' => $this->getSystemInfo(),
			'medicalOrganization' => $this->getMedicalOrganizationShort($evners_id),
			'tickets' => ['storage' => [
				'person' => $this->getFullPersonInfo($res),
				'ticket1' => [
					'ersNumber' => $res['ersNumber'],
					'createDate' => $res['createDate'],
					'pregnancyTimeOnRegisterDate' => $res['pregnancyTimeOnRegisterDate'],
					'pregnancyTimeOnPutDate' => $res['pregnancyTimeOnPutDate'],
					'isMultiplePregnancy' => $res['isMultiplePregnancy'],
					'lnNumber' => $res['lnNumber'],
					'exchangeCardNumber' => $res['exchangeCardNumber'],
					'exchangeCardDate' => $res['exchangeCardDate'],
				]
			]]
		];
		
		$xml = ArrayToXml($data, 'putTicket1Request');
		
		$xml = simplexml_load_string($xml);
		$xml->addAttribute("xmlns", 'http://gtw.ws.fss.ru/openservice/ers');
		$xml = $xml->asXML();
		
		echo $xml;
		
	}
	
	
	/**
	 *	Метод предназначен для предоставления данных от МО в ФСС о Талоне 3 ЭРС 
	 */
	function putTicket2($evners_id) {
		
		$res = $this->getFirstRowFromQuery("
			select
				eet.EvnERSTicket_id,
				eet.Person_id,
				erq.ERSRequest_ERSNumber as ersNumber,
				convert(varchar(10), eet.EvnERSTicket_setDate, 120) as createDate,
				convert(varchar(10), eet.EvnERSTicket_ArrivalDT, 120) as arrivalDate,
				convert(varchar(10), eet.EvnERSTicket_BirthDT, 120) as birthDateTime,
				eet.EvnERSTicket_BirthResultMKB10 as childbirthResultMKB10,
				eet.EvnERSTicket_DeathReason as deathReason,
				eet.EvnERSTicket_ChildrenCount as totalChildrenCount,
				eet.EvnERSTicket_PolisNoReason,
				eet.EvnERSTicket_SnilsNoReason,
				eet.EvnERSTicket_DocNoReason,
				eet.EvnERSTicket_AddressNoReason
			from v_EvnERSTicket eet (nolock)
				inner join v_ErsRequest erq (nolock) on erq.ErsRequest_id = eet.ErsRequest_id
			where erq.EvnErs_id = :EvnErs_id
		", ['EvnErs_id' => $evners_id]);

		$data = [
			'systemInfo' => $this->getSystemInfo(),
			'medicalOrganization' => $this->getMedicalOrganizationShort($evners_id),
			'tickets' => ['storage' => [
				'person' => $this->getFullPersonInfo($res),
				'ticket2' => [
					'ersNumber' => $res['ersNumber'],
					'createDate' => $res['createDate'],
					'arrivalDate' => $res['arrivalDate'],
					'birthDateTime' => $res['birthDateTime'],
					'childbirthResultMKB10' => $res['childbirthResultMKB10'],
					'deathReason' => $res['deathReason'],
					'totalChildrenCount' => $res['totalChildrenCount'],
					'newbornList' => $this->getNewbornInfo($evners_id),
				]
			]]
		];
		
		$xml = ArrayToXml($data, 'putTicket2Request');
		
		$xml = simplexml_load_string($xml);
		$xml->addAttribute("xmlns", 'http://gtw.ws.fss.ru/openservice/ers');
		$xml = $xml->asXML();
		
		echo $xml;
		
	}
	
	
	/**
	 *	Метод предназначен для предоставления данных от МО в ФСС о Талоне 3 ЭРС 
	 */
	function putTicket3($evners_id) {
		
		$res = $this->getFirstRowFromQuery("
			select
				eet.EvnERSTicket_id,
				eet.Person_id,
				erq.ERSRequest_ERSNumber as ersNumber,
				convert(varchar(10), eet.EvnERSTicket_setDate, 120) as createDate,
				eet.EvnERSTicket_PolisNoReason,
				eet.EvnERSTicket_SnilsNoReason,
				eet.EvnERSTicket_DocNoReason,
				eet.EvnERSTicket_AddressNoReason
			from v_EvnERSTicket eet (nolock)
				inner join v_ErsRequest erq (nolock) on erq.ErsRequest_id = eet.ErsRequest_id
			where erq.EvnErs_id = :EvnErs_id
		", ['EvnErs_id' => $evners_id]);

		$data = [
			'systemInfo' => $this->getSystemInfo(),
			'medicalOrganization' => $this->getMedicalOrganization($evners_id),
			'tickets' => ['storage' => [
				'person' => $this->getFullPersonInfo($res),
				'ticket3' => [
					'ersNumber' => $res['ersNumber'],
					'createDate' => $res['createDate'],
					'childrenList' => $this->getChildInfo($evners_id),
				]
			]]
		];
		
		$xml = ArrayToXml($data, 'putTicket3Request');
		
		$xml = simplexml_load_string($xml);
		$xml->addAttribute("xmlns", 'http://gtw.ws.fss.ru/openservice/ers');
		$xml = $xml->asXML();
		
		echo $xml;
		
	}
	
	
	/**
	 *	Метод предназначен для получения результата обработки сообщений-запросов от МО по формированию ЭРС
	 */
	function getResultById($evners_id) {
		
		// todo: пока чёт непонятно
		
		/*$res = $this->getFirstRowFromQuery("
			select
				eet.EvnERSTicket_id,
				eet.Person_id,
				eet.EvnERSTicket_OrgName as orgName,
				eet.EvnERSTicket_OrgINN as inn,
				eet.EvnERSTicket_OrgOGRN as ogrn,
				eet.EvnERSTicket_OrgKPP as kpp,
				erq.ERSRequest_ERSNumber as ersNumber,
				convert(varchar(10), eet.EvnERSTicket_setDate, 120) as createDate,
				eet.EvnERSTicket_PolisNoReason,
				eet.EvnERSTicket_SnilsNoReason,
				eet.EvnERSTicket_DocNoReason,
				eet.EvnERSTicket_AddressNoReason
			from v_EvnERSTicket eet (nolock)
				inner join v_ErsRequest erq (nolock) on erq.ErsRequest_id = eet.ErsRequest_id
			where erq.EvnErs_id = :EvnErs_id
		", ['EvnErs_id' => $evners_id]);

		$data = [
			'requestType' => 
			'requestId' => 
		
		
			'systemInfo' => $this->getSystemInfo(),
			'medicalOrganization' => [
				'orgName'=> $res['orgName'],
				'inn'=> $res['inn'],
				'ogrn'=> $res['ogrn'],
				'kpp'=> $res['kpp'],
				'contractServiceType'=> $res['contractServiceType'],
				'contractNumber'=> $res['contractNumber'],
				'conractDate'=> $res['conractDate']
			],
			'tickets' => ['storage' => [
				'person' => $this->getFullPersonInfo($res),
				'ticket3' => [
					'ersNumber' => $res['ersNumber'],
					'createDate' => $res['createDate'],
					'childrenList' => $this->getChildInfo($evners_id),
				]
			]]
		];
		
		$xml = ArrayToXml($data, 'getResultByIdRequest');
		
		$xml = simplexml_load_string($xml);
		$xml->addAttribute("xmlns", 'http://gtw.ws.fss.ru/openservice/ers');
		$xml = $xml->asXML();
		
		echo $xml;*/
		
	}
	
	
	/**
	 *	Метод предназначен для предоставления от МО в ФСС данных документов на оплату: Счет на оплату + реестр Талонов
	 */
	function putTicketsPay($evners_id) {
		
		$res = $this->getFirstRowFromQuery("
			select
				eer.EvnErsRegistry_id,
				eer.EvnErsRegistry_Number as registerNumber,
				eer.EvnErsRegistry_Date as registerDate,
				eer.EvnErsRegistry_Month as [month],
				eer.EvnErsRegistry_Year as [year],
				eer.EvnErsRegistry_TicketCount as ticketsCount,
				eer.ERSRequest_ERSNumber as ersNumber,
				eeb.EvnERSBill_BillAmount
			from v_EvnErsRegistry eer (nolock)
				inner join v_ErsRequest erq (nolock) on erq.ErsRequest_id = eer.ErsRequest_id
				inner join v_EvnErsBill eeb (nolock) on eeb.ErsRequest_id = eer.ErsRequest_id
			where erq.EvnErs_id = :EvnErs_id
		", ['EvnErs_id' => $evners_id]);

		$data = [
			'systemInfo' => $this->getSystemInfo(),
			'medicalOrganization' => $this->getMedicalOrganization($evners_id),
			'bill' => $this->getBillInfo($evners_id),
			'ticketsRegistry' => [
				'registerNumber' => $res['registerNumber'],
				'registerDate' => $res['registerDate'],
				'accPeriod' => [
					'month' => $res['month'],
					'year' => $res['year'],
				],
				'ticketsCount' => $res['ticketsCount'],
				'ersTickets' => ['storage' => [
					'ticket3' => [
						'ersNumber' => $res['ersNumber'],
						'contractServiceType' => $res['contractServiceType'],
						'price' => $res['EvnErsBill_BillAmount'],
					]
				]]
			]
		];
		
		$xml = ArrayToXml($data, 'putTicketsPayRequest');
		
		$xml = simplexml_load_string($xml);
		$xml->addAttribute("xmlns", 'http://gtw.ws.fss.ru/openservice/ers');
		$xml = $xml->asXML();
		
		echo $xml;
		
	}
	
	/**
	 *	Метод предназначен для получения от ФСС данных по статусу ранее направленных от МО платежных документов, включая Счет на оплату 
	 */
	function getTicketsPayStatus($evners_id) {
		
		$res = $this->getFirstRowFromQuery("
			select
				eer.EvnErsRegistry_id
			from v_EvnErsRegistry eer (nolock)
				inner join v_ErsRequest erq (nolock) on erq.ErsRequest_id = eer.ErsRequest_id
			where erq.EvnErs_id = :EvnErs_id
		", ['EvnErs_id' => $evners_id]);

		$data = [
			'systemInfo' => $this->getSystemInfo(),
			'medicalOrganization' => $this->getMedicalOrganizationShort($evners_id),
			'bill' => $this->getBillInfoShort($evners_id)
		];
		
		$xml = ArrayToXml($data, 'getTicketsPayStatusRequest');
		
		$xml = simplexml_load_string($xml);
		$xml->addAttribute("xmlns", 'http://gtw.ws.fss.ru/openservice/ers');
		$xml = $xml->asXML();
		
		echo $xml;
		
	}
	
	/**
	 *	Метод предназначен для получения от ФСС данных по сформированному ЭРС 
	 */
	function getErsData($evners_id) {
		
		$res = $this->getFirstRowFromQuery("
			select
				erq.EvnErs_id,
				erq.ErsRequest_ERSNumber as ersNumber
			from v_ErsRequest erq (nolock)
			where erq.EvnErs_id = :EvnErs_id
		", ['EvnErs_id' => $evners_id]);

		$data = [
			'systemInfo' => $this->getSystemInfo(),
			'medicalOrganization' => $this->getMedicalOrganizationShort($evners_id),
			'params' => [
				'ersIdentifier' => $res['ersNumber']
			]
		];
		
		$xml = ArrayToXml($data, 'getErsDataRequest');
		
		$xml = simplexml_load_string($xml);
		$xml->addAttribute("xmlns", 'http://gtw.ws.fss.ru/openservice/ers');
		$xml = $xml->asXML();
		
		echo $xml;
		
	}
	
	/**
	 *	Метод предназначен для направления от МО запроса на закрытие ранее сформированного ЭРС
	 */
	function disableErs($evners_id) {
		
		$res = $this->getFirstRowFromQuery("
			select
				erq.EvnErs_id,
				erq.ErsRequest_ERSNumber as ersNumber
			from v_ErsRequest erq (nolock)
			where erq.EvnErs_id = :EvnErs_id
		", ['EvnErs_id' => $evners_id]);

		$data = [
			'systemInfo' => $this->getSystemInfo(),
			'medicalOrganization' => $this->getMedicalOrganizationShort($evners_id),
			'disableErsRequest' => [
				'ersIdentifier' => $res['ersNumber'],
				'reasonCode' => $res['ersNumber'],
				'reasonMessage' => $res['ersNumber']

			]
		];
		
		$xml = ArrayToXml($data, 'disableErsRequest');
		
		$xml = simplexml_load_string($xml);
		$xml->addAttribute("xmlns", 'http://gtw.ws.fss.ru/openservice/ers');
		$xml = $xml->asXML();
		
		echo $xml;
		
	}
	
	/**
	 *	Метод предназначен для передачи от МО информации о факте постановки на учет в детскую поликлинику детей 
	 */
	function registerChildErs($evners_id) {
		
		$res = $this->getFirstRowFromQuery("
			select
				erq.EvnErs_id,
				erq.ErsRequest_ERSNumber as ersNumber
			from v_ErsRequest erq (nolock)
			where erq.EvnErs_id = :EvnErs_id
		", ['EvnErs_id' => $evners_id]);

		$data = [
			'systemInfo' => $this->getSystemInfo(),
			'medicalOrganization' => $this->getMedicalOrganizationShort($evners_id),
			'person' => $this->getFullPersonInfo($res),
			'registerChildrenInfo' => [
				'ersNumber' => $res['ersNumber'],
				'childrenList' => $this->getChildShortInfo($evners_id)
			]
		];
		
		$xml = ArrayToXml($data, 'registerChildErsRequest');
		
		$xml = simplexml_load_string($xml);
		$xml->addAttribute("xmlns", 'http://gtw.ws.fss.ru/openservice/ers');
		$xml = $xml->asXML();
		
		echo $xml;
		
	}
	
	/**
	 *	Метод предназначен для получения сведений по Талонам, которые были направлены в ФСС РФ, включая получение сведений по неоплаченным Талонам
	 */
	function getTickets($evners_id) {
		
		$res = $this->getFirstRowFromQuery("
			select
				erss.ERSStatus_Code as ticketStatus,
				convert(varchar(10), erq.ERSRequest_TicketBegDate, 120) as startDate,
				convert(varchar(10), erq.ERSRequest_TicketEndDate, 120) as endDate
			from v_ErsRequest erq (nolock)
				inner join ERSStatus erss (nolock) on erss.ERSStatus_id = erq.ERSStatus_id
			where erq.EvnErs_id = :EvnErs_id
		", ['EvnErs_id' => $evners_id]);

		$data = [
			'systemInfo' => $this->getSystemInfo(),
			'medicalOrganization' => $this->getMedicalOrganizationShort($evners_id),
			'person' => $this->getFullPersonInfo($res),
			'getTicketsParams' => [
				'ticketStatus' => $res['ticketStatus'],
				'startDate' => $res['startDate'],
				'endDate' => $res['endDate'],
			]
		];
		
		$xml = ArrayToXml($data, 'getTicketsRequest');
		
		$xml = simplexml_load_string($xml);
		$xml->addAttribute("xmlns", 'http://gtw.ws.fss.ru/openservice/ers');
		$xml = $xml->asXML();
		
		echo $xml;
		
	}
	
	
	/* --------------- прочие вспомогательные методы ----------------  */
	
	/**
	 *	Подпиание и шифрование запроса
	 */
	function prepareRequest() {
		
		$this->load->helper('openssl');
		$this->load->library('parser');
		

		$ogrn = $this->getFirstResultFromQuery("select Lpu_Ogrn from v_Lpu (nolock) where Lpu_id = :Lpu_id", array(
			'Lpu_id' => $data['Lpu_id']
		));
		
		$xml = "<?xml version='1.0' encoding='UTF-8' standalone='no'?>\n";
		$xml .= '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:fil="http://ru/ibs/fss/ln/ws/FileOperationsLn.wsdl" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><soapenv:Header>';

		$certAlgo = getCertificateAlgo($data['certbase64']);

		$xml .= $this->parser->parse('export_xml/ers_xml_signature', array(
			'id' => 'http://eln.fss.ru/actor/mo/' . $ogrn . '/ELN_' . $item['EvnStick_Num'],
			'block' => 'OGRN_' . $item['ogrn'],
			'BinarySecurityToken' => $data['certbase64'],
			'DigestValue' => '',
			'SignatureValue' => '',
			'signatureMethod' => $certAlgo['signatureMethod'],
			'digestMethod' => $certAlgo['digestMethod']
		), true);

		$xml .= '</soapenv:Header>';
		$xml .= '<soapenv:Body>';
		//$xml .= $item['LpuLn'];
		$xml .= '</soapenv:Body>';
		$xml .= '</soapenv:Envelope>';
		

		$doc = new DOMDocument();
		$doc->loadXML($xml);
		$toHash = $doc->getElementsByTagName('Body')->item(0)->C14N(false, false);
		// считаем хэш
		$cryptoProHash = getCryptCpHash($toHash, $data['certbase64']);
		// 2. засовываем хэш в DigestValue
		$doc->getElementsByTagName('DigestValue')->item(0)->nodeValue = $cryptoProHash;
		// 3. считаем хэш по SignedInfo
		$toSign = $doc->getElementsByTagName('SignedInfo')->item(0)->C14N(false, false);
		$Base64ToSign = base64_encode($toSign);
	}
	
	/**
	 *	Обработка ответа
	 */
	function processResponse() {
		
		
		$data = [];
		//EvnERS_id
		//ERSRequest_id
		
		
		switch ($var) {
			case 'REGISTERED':
				$data['ERSRequestStatus_id'] = 1;
				$data['EvnERS_FSSGUID'] = $resp->requestId;
				$this->saveFssGuid($data);
				break;
			case 'READY_TO_PROCESS':
				$data['ERSRequestStatus_id'] = 2;
				break;
			case 'PROCESSING':
				$data['ERSRequestStatus_id'] = 3;
				break;
			case 'PROCESSED':
				$data['ERSRequestStatus_id'] = 4;
				break;
			case 'PROCESSING_ERROR':
				$data['ERSRequestStatus_id'] = 5;
				$this->saveErsRequestErrors();
				break;
			
		}
		
		$this->setErsRequestStastus($data);
	}
	
	/**
	 *	Установка статуса
	 */
	function setErsRequestStastus($data) {
			
		$this->db->query("
			update 
				ErsRequest with(rowlock) 
			set
				ERSRequest_updDT = dbo.tzGetDate()
				,pmUser_updID = :pmUser_id
				,ERSRequestStatus_id = :ERSRequestStatus_id
			where 
				ERSRequest_id = :ERSRequest_id
		", $data);
	}
	
	/**
	 *	Запись идентификатора запроса
	 */
	function saveFssGuid($data) {
			
		$this->db->query("
			update 
				EvnERS with(rowlock) 
			set
				EvnERS_FSSGUID = :EvnERS_FSSGUID
			where 
				EvnERS_id = :EvnERS_id
		", $data);
	}
	
	/**
	 *	Запись ошибок
	 */
	function saveErsRequestErrors($data, $processingResult) {
		
		foreach($processingResult  as $res) {
			$query = "
				declare
					@Res bigint = null,
					@ErrCode int,
					@ErrMessage varchar(4000);
				exec p_ERSRequestError_ins
					@ERSRequestError_id = @Res output,
					@ERSRequest_id = :ERSRequest_id,
					@EvnERS_id = :EvnERS_id,
					@ERSRequestError_Code = :ERSRequestError_Code,
					@ERSRequestError_Descr = :ERSRequestError_Descr,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$result = $this->queryResult($query, [
				'ERSRequest_id' => $data['ERSRequest_id'],
				'EvnERS_id' => $data['EvnERS_id'],
				'ERSRequestError_Code' => $res->code,
				'ERSRequestError_Descr' => $res->message,
				'pmUser_id' => $data['pmUser_id']
			]);
		}
	}
	
}