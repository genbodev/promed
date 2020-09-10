<?php
/**
 * InnovaSysService - модель для интеграции с Innova Systems ЛИС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 *
 * @package      Common
 * @access       public
 */

/**
 * @property EvnLabRequest_model $EvnLabRequest_model
 * @property MedService_model $MedService_model
 * @property EvnLabSample_model $EvnLabSample_model
 *
 */
class AlfaLab_model extends SwModel
{
	/**
	 * constructor
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Добавление/изменение статуса исследований
	 */
	function setResearchTransferStatus($data)
	{
		if (empty($data['ResearchTransferList_id'])) {
			$action = 'ins';
			$data['ResearchTransferList_id'] = null;
		} else {
			$action = 'upd';
		}

		$fields = [];
		foreach ($data as $key => $value) {
			$fields[] = '@' . $key . " = :" . $key;
		}

		$fields = implode(",\n", $fields);

		$query = "
			declare
				@ResearchTransferList_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @ResearchTransferList_id = :ResearchTransferList_id;
			exec lis.p_researchtransferlist_{$action}
				@ResearchTransferList_id = @ResearchTransferList_id output,
				{$fields},
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ResearchTransferList_id as ResearchTransferList_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->queryResult($query, $data);
		if (!is_array($result)) {
			return $this->createError('', 'Ошибка при изменении статуса исследования');
		}

		return ['Error_Msg' => '', 'success' => true];
	}

	/**
	 * Получение общей информации + создание xml-результата
	 */
	function prepareResponseWithNewRequests($data)
	{
		$xml = new SimpleXMLElement('<xml/>');
		$request = $xml->addChild('Envelope');
		$request->addAttribute('xmlns:soapenv', "http://schemas.xmlsoap.org/soap/envelope/");
		$request = $request->addChild('Body');

		foreach ($data as $id => $req) {
			$res = $this->getFirstRowFromQuery("
				select top 1
					elr.EvnLabRequest_id as \"RequestCode\",
					elr.MedService_id as \"OrganizationCode\",
					ms.Lpu_id as \"HospitalCode\",
					convert(varchar, elr.EvnLabRequest_insDT, 104) as \"DateCreate\",
					convert(varchar, elr.EvnLabRequest_updDT, 104) as \"DateOfChange\",
					case when 2 = ed.EvnDirection_isCito
						then '1'
						else '0'
					end as \"IsUrgent\",
					els.MedPersonal_did as \"DoctorCode\",
					mp.Person_FirName as \"FirstName\",
    				mp.Person_SecName as \"MiddleName\",
    				mp.Person_SurName as \"LastName\",
    				mpps.Person_SNILS as \"SNILS\",
    				ps.Person_FirName as \"pFirstName\",
    				ps.Person_SecName as \"pMiddleName\",
    				ps.Person_FirName as \"pLastName\",
    				ps.Person_SNILS as \"Snils\",
    				DAY(ps.Person_Birthday) as \"BirthDay\",
    				MONTH(ps.Person_Birthday) as \"BirthMonth\",
    				YEAR(ps.Person_Birthday) as \"BirthYear\",
    				sex.Sex_Name as \"Sex\",
    				ps.Person_id as \"UID\",
    				ps.Person_Phone as \"Phone\",
    				ps.Polis_Ser as \"PolisSer\",
    				ps.Polis_Num as \"PolisNum\",
    				a.Address_Address as \"Address\"    				
				from v_EvnLabRequest elr
					left join v_MedService ms with (nolock) on elr.MedService_id = ms.MedService_id
					outer apply(
						select top 1 *
						from v_EvnLabSample els1 with (nolock)
						where els1.EvnLabRequest_id = elr.EvnLabRequest_id
					) els
					left join v_EvnDirection ed with (nolock) on ed.EvnDirection_id = elr.EvnDirection_id
					outer apply(
						select top 1 *
						from v_MedPersonal mp1 with (nolock)
						where els.MedPersonal_did = mp1.MedPersonal_id
					) mp
					left join v_Person_all mpps with (nolock) on mpps.Person_id = MP.Person_id
					left join v_PersonState ps with (nolock) on ps.PersonEvn_id = elr.PersonEvn_id
					left join v_Sex sex with (nolock) on ps.Sex_id = sex.Sex_id
					left join v_Address a with (nolock) on ps.UAddress_id = a.Address_id
				where elr.EvnLabRequest_id = :EvnLabRequest_id
			", ['EvnLabRequest_id' => $id], true);

			$request = $request->addChild('Request');
			$request->addAttribute('xmlns', $this->config->item('PromedURL') . 'api/rish/AlfaLab/GetNewRequest');
			$request->addAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
			$request->addChild('RequestCode', $res['RequestCode']);
			$request->addChild('OrganizationCode', $res['OrganizationCode']);
			$request->addChild('DateCreate', $res['DateCreate']);
			$request->addChild('DateOfChange', $res['DateOfChange']);
			$request->addChild('IsUrgent', $res['IsUrgent'] === '1' ? true : false);
			$doctor = $request->addChild('Doctor');
			$doctor->addChild('DoctorCode', $res['DoctorCode']);
			$doctor->addChild('FirstName', $res['FirstName']);
			$doctor->addChild('LastName', $res['LastName']);
			$doctor->addChild('MiddleName', $res['MiddleName']);
			$doctor->addChild('SNILS', $res['SNILS']);
			$patient = $request->addChild('Patient');
			$patient->addChild('FirstName', $res['pFirstName']);
			$patient->addChild('MiddleName', $res['pMiddleName']);
			$patient->addChild('LastName', $res['pLastName']);
			$patient->addChild('BirthDay', $res['BirthDay']);
			$patient->addChild('BirthMonth', $res['BirthMonth']);
			$patient->addChild('BirthYear', $res['BirthYear']);
			$patient->addChild('Sex', $res['Sex']);
			$patient->addChild('UID', $res['UID']);
			$patient->addChild('PolisSer', $res['PolisSer']);
			$patient->addChild('PolisNum', $res['PolisNum']);
			$patient->addChild('Snils', $res['Snils']);
			$patient->addChild('Address', $res['Address']);
			$patient->addChild('Phone', $res['Phone']);

			$targetUslugas = $this->getTargetUslugas($req);

			foreach ($targetUslugas as $targetUsluga) {
				$target = $request->addChild('Target');
				$target->addChild('TargetCode', $targetUsluga['TargetCode']);
			}
		}

		return $this->generateXMLResponse($xml);
	}

	/**
	 * Получение коодов услуг по объему "ЛИСУслуги"
	 */
	function getTargetUslugas($data)
	{
		$res = [];
		foreach ($data as $datum) {
			$res[]['TargetCode'] = $this->getFirstResultFromQuery("
				select
					substring(cast(AttributeValue_ValueText as varchar), 23, LEN(AttributeValue_ValueText) - 22) as \"TargetCode\"
				FROM v_AttributeVision avis with (nolock)
					inner join v_AttributeValue av with (nolock) on av.AttributeVision_id = avis.AttributeVision_id
				WHERE avis.AttributeVision_TableName = 'dbo.VolumeType'
					and avis.AttributeVision_TablePKey = '10215'
					and avis.AttributeVision_IsKeyValue = 2
                	and av.attributevalue_valueident is not null
					and coalesce(av.AttributeValue_endDate, dbo.tzGetDate()) >= dbo.tzGetDate()
					and coalesce(av.AttributeValue_begDate, dbo.tzGetDate()) <= dbo.tzGetDate()
					and av.attributevalue_valueident = :UslugaComplex_id
			", $datum, true);
		}

		return $res;
	}

	/**
	 * Устраняет возможные проблемы с кодировкой
	 */
	function generateXMLResponse($xml)
	{
		$xmlDOM = new DOMDocument('1.0', 'utf-8');
		$xmlData = dom_import_simplexml($xml);
		$xmlData = $xmlDOM->importNode($xmlData, true);
		$xmlData = $xmlDOM->appendChild($xmlData);
		$xml = $xmlDOM->saveXML();
		$xml = trim($xml);
		echo $xml;
	}

	/**
	 * Получение услуг и идентификатора в списке переданнных исследований
	 */
	function getEvnUslugaPars($data)
	{
		return $this->queryResult("
			select
				elruc.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				rtl.ResearchTransferList_id as \"ResearchTransferList_id\"
			from v_EvnLabRequestUslugaComplex elruc with (nolock)
				inner join lis.v_ResearchTransferList rtl with (nolock) on elruc.EvnUslugaPar_id = rtl.EvnUslugaPar_id
			where elruc.EvnLabRequest_id = :EvnLabRequest_id
		", $data);
	}

	/**
	 * Запрос заявки на ЛИС
	 */
	function newRequest($data)
	{
		$this->load->model('MedService_model');
		$check = $this->MedService_model->checkIsExternal($data);
		if (empty($check) || empty($check['MedService_IsExternal'])) {
			return;
		}

		$this->load->model('EvnLabRequest_model');
		$params = [
			'MedService_id' => $data['MedService_id'],
			'begDate' => date('Y-m-d'),
			'endDate' => date('Y-m-d', strtotime('-2 day')),
			'Lpu_id' => $check['Lpu_id'] ?? null
		];
		$result = $this->EvnLabRequest_model->getLabRequestsForExport($params);

		$res = [];
		$params = [
			'pmUser_id' => $data['pmUser_id'],
			'ResearchTransferStatus_id' => 1
		];
		foreach ($result as $item) {
			if (!in_array($item['EvnLabRequest_id'], array_keys($res))) {
				$res[$item['EvnLabRequest_id']] = [];
			}

			$res[$item['EvnLabRequest_id']][] = [
				'EvnUslugaPar_id' => $item['EvnUslugaPar_id'],
				'UslugaComplex_id' => $item['UslugaComplex_id']
			];

			$params['EvnUslugaPar_id'] = $item['EvnUslugaPar_id'];
			$this->setResearchTransferStatus($params);
		}

		return $this->prepareResponseWithNewRequests($res);
	}

	/**
	 * Установка статуса принятия заявки в ЛИС
	 */
	function RequestProcessingStatus($data)
	{
		$uslugas = $this->getEvnUslugaPars($data);
		foreach ($uslugas as $id => $usluga) {
			$uslugas[$id]['pmUser_id'] = $data['pmUser_id'];
			//'State' === 2 => Error
			$uslugas[$id]['ResearchTransferStatus_id'] = $data['State'] == 2 ? 3 : 2; //3 - Error, 2 - Transferred
			if ($uslugas[$id]['ResearchTransferStatus_id'] == 3) {
				$uslugas[$id]['ResearchTransferList_Error'] = $data['ErrorText'];
			}

			$this->setResearchTransferStatus($uslugas[$id]);
		}

		$xml = new SimpleXMLElement('<xml/>');
		$request = $xml->addChild('Envelope');
		$request->addAttribute('xmlns:soapenv', "http://schemas.xmlsoap.org/soap/envelope/");
		$request = $request->addChild('Body');
		$request = $request->addChild('RequestProcessingStatusResponse');
		$request->addAttribute('xmlns', $this->config->item('PromedURL') . 'api/rish/AlfaLab/RequestProcessingStatus');
		$request->addChild('Result', "ACCEPTED");

		return $this->generateXMLResponse($xml);
	}

	function SendResultObtained($data)
	{
		$res = $this->findUsluga($data);
		if (empty($res)) {
			return;
		}

		$dateString = $data['SendResultObtained']['AppResYear'] 
			. '-' . $data['SendResultObtained']['AppResMonth'] 
			. '-' . $data['SendResultObtained']['AppResDay']
			. ' ' . $data['SendResultObtained']['AppResHour']
			. ':' . $data['SendResultObtained']['AppResMin'];
		$params = [
			'ResearchTransferList_id' => $res['ResearchTransferList_id'],
			'ResearchTransferStatus_id' => 4,//Approved
			'MedPersonal_id' => $data['SendResultObtained']['DoctorCode'],
			'ResearchTransferList_Date' => $dateString
		];
		$this->setResearchTransferStatus($params);

		$xml = new SimpleXMLElement('<xml/>');
		$request = $xml->addChild('Envelope');
		$request->addAttribute('xmlns:soapenv', "http://schemas.xmlsoap.org/soap/envelope/");
		$request = $request->addChild('Body');
		$request = $request->addChild('SendResultResponse');
		$request->addAttribute('xmlns', $this->config->item('PromedURL') . 'api/rish/AlfaLab/SendResultObtained');
		$request->addChild('rendserviceid', $res['EvnUslugaPar_id']);

		return $this->generateXMLResponse($xml);
	}

	function findUsluga($data)
	{
		return $this->getFirstRowFromQuery("
			select
				elruc.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				AttributeValue_Valueident as \"UslugaComplex_id\",
				ut.UslugaTest_id as \"UslugaTest_id\",
				els.EvnLabSample_id as \"EvnLabSample_id\",
				rtl.ResearchTransferList_id as \"ResearchTransferList_id\",
				convert(varchar(20), rtl.ResearchTransferList_Date, 104) as \"ResearchTransferList_Date\"
			from v_EvnLabRequestUslugaComplex elruc with (nolock)
				cross apply (
					select
						AttributeValue_Valueident
					FROM v_AttributeVision avis with (nolock)
						inner join v_AttributeValue av with (nolock) on av.AttributeVision_id = avis.AttributeVision_id
					WHERE avis.AttributeVision_TableName = 'dbo.VolumeType'
						and avis.AttributeVision_TablePKey = '10215'
						and avis.AttributeVision_IsKeyValue = 2
                		and av.attributevalue_valueident is not null
						and coalesce(av.AttributeValue_endDate, dbo.tzGetDate()) >= dbo.tzGetDate()
						and coalesce(av.AttributeValue_begDate, dbo.tzGetDate()) <= dbo.tzGetDate()
						and substring(cast(AttributeValue_ValueText as varchar), 23, LEN(AttributeValue_ValueText) - 22) = :UslugaComplex_Code
				) uc
				inner join v_UslugaTest ut with (nolock) on ut.UslugaTest_pid = elruc.EvnUslugaPar_id
					and ut.UslugaComplex_id = AttributeValue_Valueident
				left join v_EvnLabSample els with (nolock) on els.EvnLabSample_id = ut.EvnLabSample_id
				left join lis.v_ResearchTransferList rtl with (nolock) on elruc.EvnUslugaPar_id = rtl.EvnUslugaPar_id
			where elruc.EvnLabRequest_id = :EvnLabRequest_id
				and exists (
					select *
					from lis.v_ResearchTransferList rtl with (nolock)
					where elruc.EvnUslugaPar_id = rtl.EvnUslugaPar_id
				)
		", [
			'EvnLabRequest_id' => $data['RequestCode'],
			'UslugaComplex_Code' => $data['TargetCode']
		], true);
	}

	function getApprovedUslugas()
	{
		return $this->queryResult("
			select
				elruc.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				elruc.EvnLabRequest_id as \"EvnLabRequest_id\"
			from lis.v_ResearchTransferList rtl with (nolock)
				left join v_EvnLabRequestUslugaComplex elruc with (nolock) on elruc.EvnUslugaPar_id = rtl.EvnUslugaPar_id
			where rtl.ResearchTransferStatus_id = 4
		");
	}

	function processTestData($data)
	{
		foreach ($data['result'] as $res) {
			switch ($res['@attributes']['archetype_node_id']) {
				case 'at0012':
					$data['code'] = $res['value'];
					break;
				case 'at0004':
					$data['resultValue'] = $res['value'];
					break;
				case 'at0010':
					$data['comment'] = $res['comment'];
					break;
			}

			if (empty($data['code'])) {
				return;
			}

			$usluga = $this->findUsluga([
				'RequestCode' => $data['EvnLabRequest_id'],
				'TargetCode' => $data['code']
			]);

			if (empty($usluga)) {
				return;
			}
			foreach ($usluga as $key => $value) {
				$data[$key] = $value;
			}
			$this->saveTestData($data);
		}
	}

	function saveTestData($data)
	{
		$this->queryResult("
			update lis.ResearchTransferList with (rowlock)
			set ResearchTransferStatus_id = 5,
				ResearchTransferList_xml = :xml
			where ResearchTransferList_id = :ResearchTransferList_id
		", $data);

		$mp = $this->getFirstResultFromQuery("
			select
				MedPersonal_id as \"MedPersonal_id\"
			from lis.v_ResearchTransferList with (nolock)
			where ResearchTransferList_id = :ResearchTransferList_id
		", $data, true);

		$this->queryResult("
			UPDATE UslugaTest with (rowlock)
			SET UslugaTest_ResultApproved = 2,
				UslugaTest_CheckDT = :ResearchTransferList_Date
			WHERE UslugaTest_id = :UslugaTest_id;
				
			update EvnUslugaPar with (rowlock)
			set MedPersonal_id = :MedPersonal_id
			where EvnUslugaPar_id = :EvnUslugaPar_id
		", [
			'ResearchTransferList_Date' => $data['ResearchTransferList_Date'],
			'UslugaTest_id' => $data['UslugaTest_id'],
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
			'MedPersonal_id' => $mp
		]);

		$this->load->model('EvnLabSample_model');
		$this->load->model('EvnLabRequest_model');
		$dataForUpdate = array(
			'UslugaTest_id' => $data['UslugaTest_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'UslugaTest_ResultValue' => $data['resultValue'],
			'UslugaTest_setDT' => $data['ResearchTransferList_Date'],
			'UslugaTest_Comment' => $data['comment'],
			'isAutoApprove' => true,
			'updateType' => 'fromLISwithRefValues',
			'session' => $data['session'],
			'pmUser_id' => $data['pmUser_id']
		);
		$res = $this->EvnLabSample_model->updateResult($dataForUpdate);

		if (!$res) {
			$this->queryResult("
				update lis.ResearchTransferList with (rowlock)
				set ResearchTransferStatus_id = 6
				where ResearchTransferList_id = :ResearchTransferList_id
			", $data);
		}
	}
}
