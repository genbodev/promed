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

class InnovaSysService_Model extends swModel
{
	private $usCCode;
	private $EvnLabRequest_id;

	/**
	 * constructor
	 */
	function __construct()
	{
		parent::__construct();

		$this->load->library('textlog', array('file' => 'InnovaSysService_' . date('Y-m-d') . '.log'));
	}

	/**
	 * создание xml для отправки заявки
	 */
	function makeRequest($data)
	{
		$result = $this->getPatient($data);
		$samplesData = $this->getSamples($data);
		$lpu = $this->getLpuData($data);
		$medStaffFact = $this->getDoctor($data);
		$xmls = [];

		if (!empty($lpu[0]['Lpu_id'])) {
			$HospitalName = $lpu[0]['HospitalName'];
			$HospitalCode = $lpu[0]['HospitalCode'];
			$DepartmentCode = $lpu[0]['DepartmentCode'];
			$DepartmentName = $lpu[0]['DepartmentName'];
		} else {
			throw new Exception('Не удалось получить данные по МО');
		}

		foreach ($samplesData as $oneSampleData) {
			// для каждой пробы заявки свой запрос, в теге RequestCode - идентификатор пробы
			$xml = new SimpleXMLElement('<xml/>');

			$request = $xml->addChild('Request');
			$request->addAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
			$request->addAttribute('xmlns:xsd', "http://www.w3.org/2001/XMLSchema");

			$request->addChild('RequestCode', $oneSampleData['Barcode']);
			$request->addChild('HospitalCode', $HospitalCode);
			$request->addChild('DepartmentCode', $DepartmentCode);

			if (isset($DepartmentName)) {
				$request->addChild('DepartmentName', $DepartmentName);
			}
			if (isset($HospitalName)) {
				$request->addChild('HospitalName', $HospitalName);
			}
			if (isset($medStaffFact[0]['MedStaffFact_id'])) {
				$request->addChild('DoctorCode', $medStaffFact[0]['MedStaffFact_id']);
			}
			if (isset($medStaffFact[0]['MedStaffFact_Name'])) {
				$request->addChild('DoctorName', $medStaffFact[0]['MedStaffFact_Name']);
			}

			$patient = $request->addChild('Patient');

			$patient->addChild('Code', $result['Code']);
			$patient->addChild('PatientCard', $result['Card']);
			$patient->addChild('FirstName', $result['FirstName']);
			$patient->addChild('MiddleName', $result['MiddleName']);
			$patient->addChild('LastName', $result['LastName']);
			$patient->addChild('Sex', $result['Sex']);
			$patient->addChild('BirthYear', $result['BirthYear']);
			$patient->addChild('BirthMonth', $result['BirthMonth']);
			$patient->addChild('BirthDay', $result['BirthDay']);
			$patient->addChild('PolicySeries', $result['PolicySeries']);
			$patient->addChild('PolicyNumber', $result['PolicyNumber']);

			$address = explode(',', $result['pAddress']);
			if (count($address) > 6) {
				$patient->addChild('Country', $address[1]);
				$patient->addChild('City', $address[3]);
				$patient->addChild('Street', $address[5]);
				$patient->addChild('Building', $result['Building']);
				$patient->addChild('Flat', $result['Flat']);
			}


			$samples = $request->addChild('Samples');

			$sample = $samples->addChild('Sample');

			$sample->addChild('BiomaterialCode', $oneSampleData['BioMaterialCode']);
			$sample->addChild('Barcode', $oneSampleData['Barcode']);
			$sample->addChild('Priority', $oneSampleData['Priority']);

			$targets = $sample->addChild('Targets');
			$targetsData = $this->getTargets($oneSampleData);
			foreach ($targetsData as $oneTargetData) {
				$target = $targets->addChild('Target');

				$code = $this->getAISTargetCode($oneTargetData['Code'], $oneSampleData['Barcode']);
				$target->addChild('Code', $code);
				$target->addChild('Priority', $oneSampleData['Priority']);
				$target->addChild('ReadOnly', 'false');
			}

			$request->addChild('PayCategoryCode', 1);

			$xmls[] = $xml;
		}

		return $xmls;
	}

	/**
	 *определение кода заказанного исследования и проверки наличия услуг
	 */
	function getAISTargetCode($data, $barcode)
	{
		$query = "
		select 
			uc.UslugaComplex_id
		from
			v_UslugaComplex uc with(nolock)
		where	
			uc.UslugaComplex_Code = :UslugaComplex_Code
		";
		$result = $this->db->query($query, array('UslugaComplex_Code' => $data));
		$result = $result->result('array');
		$result = $result[0];
		if (empty($result))
			throw new Exception('Не найдено услуги');

		$query = "
		with myvars as (
			select
				dbo.tzgetdate() as curdate,
				:AttributeValue_id as AttributeValue_id
		)
		select top 1
			case
				when a.AttributeValueType_id = 1 then cast(avpid.AttributeValue_ValueInt as varchar)
				when a.AttributeValueType_id = 2 then cast(avpid.AttributeValue_ValueFloat as varchar)
				when a.AttributeValueType_id = 3 then cast(avpid.AttributeValue_ValueFloat as varchar)
				when a.AttributeValueType_id = 4 then cast(avpid.AttributeValue_ValueBoolean as varchar)
				when a.AttributeValueType_id = 5 then avpid.AttributeValue_ValueString
				when a.AttributeValueType_id = 6 then cast(avpid.AttributeValue_ValueIdent as varchar)
				when a.AttributeValueType_id = 7 then convert(varchar(10), avpid.AttributeValue_ValueDate, 104)
				when a.AttributeValueType_id = 8 then cast(avpid.AttributeValue_ValueIdent as varchar)
			end as AttributeValue,
			av.AttributeValue_id
		from v_AttributeValue av with(nolock)
			inner join v_AttributeValue avpid with(nolock) on avpid.AttributeValue_rid = av.AttributeValue_id
				OR avpid.AttributeValue_id = av.AttributeValue_id
			left join v_Attribute a with(nolock) on avpid.Attribute_id = a.Attribute_id
		where (1 = 1)
			and a.Attribute_Name = 'Код услуги Инновасистем'
			and av.AttributeValue_begDate <= coalesce((select curdate from myvars), av.AttributeValue_begDate)
			and coalesce(av.AttributeValue_endDate, (select curdate from myvars)) >= (select curdate from myvars)
			and av.AttributeValue_id in (
				select
					av.AttributeValue_id
				from v_AttributeValue av with(nolock)
					inner join v_AttributeValue avpid with(nolock) on avpid.AttributeValue_rid = av.AttributeValue_id OR
						avpid.AttributeValue_id = av.AttributeValue_id
					left join v_Attribute a with(nolock) on avpid.Attribute_id = a.Attribute_id
				where (1 = 1)
					and a.Attribute_Name = 'Услуга'
					and (case
						when a.AttributeValueType_id = 1
							then cast(avpid.AttributeValue_ValueInt as varchar)
						when a.AttributeValueType_id = 2
							then cast(avpid.AttributeValue_ValueFloat as varchar)
						when a.AttributeValueType_id = 3
							then cast(avpid.AttributeValue_ValueFloat as varchar)
						when a.AttributeValueType_id = 4
							then cast(avpid.AttributeValue_ValueBoolean as varchar)
						when a.AttributeValueType_id = 5
							then avpid.AttributeValue_ValueString
						when a.AttributeValueType_id = 6
							then cast(avpid.AttributeValue_ValueIdent as varchar)
						when a.AttributeValueType_id = 7
							then convert(varchar(10), avpid.AttributeValue_ValueDate, 104)
						when a.AttributeValueType_id = 8
							then cast(avpid.AttributeValue_ValueIdent as varchar)
					end) = (select AttributeValue_id from myvars)
			)
		";
		$code = $this->db->query($query, array('AttributeValue_id' => $result['UslugaComplex_id']));
		$code = $code->result('array');

		if (empty($code) || empty($code[0]) || empty($code[0]['AttributeValue']) || empty($code[0]['AttributeValue_id']))
			throw new Exception('Не найдено действующего атрибута «Код услуги Инновасистем» для объема «АИСУслугаСистем»');
		$code = $code[0];

		//проверка, подходит ли МО службы, в которой взяли пробу, или службы, в которой выполняется заявка
		$hosp = $this->queryResult("
			with mv as (			
				select
					ms.Lpu_id as id
				from v_EvnLabSample els with(nolock)
					left join v_MedService ms with(nolock) on ms.MedService_id = els.MedService_id
						or ms.MedService_id = els.MedService_did
				where els.EvnLabSample_Barcode = :EvnLabSample_Barcode
			)
			
			select top 1
				case when avpid.AttributeValue_ValueIdent in (select id from mv)
					then avpid.AttributeValue_ValueIdent
					else null
				end as AttributeValue_ValueIdent
			from v_AttributeValue av with(nolock)
				inner join v_AttributeValue avpid with(nolock) on avpid.AttributeValue_rid = av.AttributeValue_id
					OR avpid.AttributeValue_id = av.AttributeValue_id
				left join v_Attribute a with(nolock) on avpid.Attribute_id = a.Attribute_id
			where (1 = 1)
				and av.AttributeValue_id = :AttributeValue_id
				and a.Attribute_Name = 'МО'
		", [
			'AttributeValue_id' => $code['AttributeValue_id'],
			'EvnLabSample_Barcode' => $barcode
		]);
		if (empty($hosp) || empty($hosp[0]['AttributeValue_ValueIdent'])) {
			throw new Exception('Для данной МО не найдено действующего атрибута «Код услуги Инновасистем» для объема «АИСУслугаСистем»');
		}

		return $code['AttributeValue'];
	}

	/**
	 * получение данных о пациенте
	 */
	function getPatient($data)
	{
		$query = "
			select top 1
				Person_id
			from v_EvnLabRequest with(nolock)
			where EvnLabRequest_id = :EvnLabRequest_id
		";
		$person_id = $this->getFirstResultFromQuery($query, $data);

		$res = [];
		if (!empty($person_id)) {
			$this->load->model('Person_model');
			$res = $this->Person_model->getPersonForInnova([
				'Person_id' => $person_id
			]);
			if (!is_array($res)) {
				return false;
			}
			if (isset($res[0])) {
				$res = $res[0];
			}
		}

		return $res;
	}

	/**
	 * получение данных о пробах
	 */
	function getSamples($data)
	{
		$query = "
		select
			evl.EvnLabSample_id as \"id\",
			rm.RefMaterial_Code as \"BioMaterialCode\",
			evl.EvnLabSample_BarCode as \"Barcode\",
			coalesce(case when evn.EvnDirection_IsCito is null then 0 else 1 end, 0) as \"Priority\"
		from v_EvnLabSample evl with(nolock)
			left join v_RefSample rs with(nolock) on rs.RefSample_id = evl.RefSample_id
			left join v_RefMaterial rm with(nolock) on rm.RefMaterial_id = rs.RefMaterial_id
			left join v_EvnLabRequest elr with(nolock) on elr.EvnLabRequest_id = evl.EvnLabRequest_id
			left join v_EvnDirection evn with(nolock) on evn.EvnDirection_id = elr.EvnDirection_id
		where
			evl.EvnLabRequest_id = :EvnLabRequest_id
		";
		$result = $this->db->query($query, $data);
		$result = $result->result('array');
		return $result;
	}

	/**
	 * получение данных о МО по заявке
	 */
	function getLpuData($data)
	{
		$query = "
			select
				lpu.Lpu_id as Lpu_id,
				lpu.Lpu_Nick as HospitalName,
				RIGHT(lpu.Lpu_f003mcod, 4) as HospitalCode,
				COALESCE(ls.LpuSection_id, eu.LpuSection_id) as DepartmentCode,
				COALESCE(ls.LpuSection_Name, eu.LpuSection_Name) as DepartmentName
			from
				v_EvnLabRequest elr with (nolock)
				inner join v_EvnDirection_all ed with (nolock) on ed.EvnDirection_id = elr.EvnDirection_id
				left join v_Lpu lpu with (nolock) on ed.Lpu_id = lpu.Lpu_id
				left join v_LpuSection ls with (nolock) on ls.LpuSection_id = ed.LpuSection_id
				outer apply (
					select top 1
						eup.LpuSection_did as LpuSection_id,
						euls.LpuSection_Name
					from
						v_EvnUslugaPar eup with(nolock)
						left join v_LpuSection euls with(nolock) on eup.LpuSection_did = euls.LpuSection_id
					where 
						eup.EvnDirection_id = ed.EvnDirection_id
				) eu
			where
				elr.EvnLabRequest_id = :EvnLabRequest_id
		";

		return $this->queryResult($query, $data);
	}

	/**
	 * получение данных о враче по заявке
	 */
	function getDoctor($data)
	{
		$query = "
			select
				msf.MedStaffFact_id,
				msf.Person_Fio as MedStaffFact_Name
			from
				v_EvnLabRequest elr with (nolock)
				inner join v_EvnDirection_all ed with (nolock) on ed.EvnDirection_id = elr.EvnDirection_id
				inner join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = ed.MedStaffFact_id
			where
				elr.EvnLabRequest_id = :EvnLabRequest_id
		";

		return $this->queryResult($query, $data);
	}

	/**
	 * получение данных об исследованиях
	 */
	function getTargets($data)
	{
		$query = "
			select top 1
				uc.UslugaComplex_Code as Code
			from
				v_UslugaTest ut with(nolock)
				inner join v_EvnUslugaPar eup with(nolock) on eup.EvnUslugaPar_id = ut.UslugaTest_pid
				left join v_UslugaComplex uc with(nolock) on uc.UslugaComplex_id = eup.UslugaComplex_id 
			where
				ut.EvnLabSample_id = :EvnLabSample_id
				order by EvnUslugaPar_setTime, EvnUslugaPar_setDate desc
		";
		$result = $this->db->query($query, array(
			'EvnLabSample_id' => $data['id']
		));

		return $result->result('array');
	}

	/**
	 * считывание ответа по заявке
	 */
	function parseRequest($data)
	{
		$this->textlog->add('Обрабатываем файл: ' . basename($data['xml']));
		$request = simplexml_load_file($data['xml']);

		$json = json_encode($request);
		$array = json_decode($json, true);

		$data['EvnLabSample_BarCode'] = $array['RequestCode'];
		foreach ($array['SampleResults'] as $sampleResult) {
			$data['sampleResult'] = $sampleResult;
			$this->saveSampleResult($data);

			if (!empty($this->EvnLabRequest_id))
				$this->processLabRequestByLabSample();
		}

		// чтобы оставить файл для отладки и не обрабатывает его в следующий раз переименуем его
		rename($data['xml'], $data['xml'] . '.bak');

		$this->textlog->add('Завершили обрабатывать файл: ' . basename($data['xml']));
	}

	function processLabRequestByLabSample()
	{
		$this->load->model('EvnLabRequest_model');
		// создаём/обновляем протокол
		$this->EvnLabRequest_model->EvnLabRequest_id = $this->EvnLabRequest_id;
		$this->EvnLabRequest_model->load();
		$res = $this->EvnLabRequest_model->saveEvnXml();
		if (isset($res[0]['success']) && $res[0]['success']) {
			$this->textlog->add('Отчет создан');
		} else {
			$this->textlog->add('Ошибка при формировании отчета: ' . "\n" . json_encode($res));
		}
	}

	/**
	 * Сохранение результата по пробе
	 */
	function saveSampleResult($data)
	{
		// ищем пробу по штрих-коду
		$resp = $this->queryResult("
			select
				EvnLabSample_id
			from
				v_EvnLabSample with(nolock)
			where
				EvnLabSample_BarCode = :EvnLabSample_BarCode
			order by
				EvnLabSample_id desc
		", array(
			'EvnLabSample_BarCode' => $data['EvnLabSample_BarCode']
		));

		if (!empty($resp[0]['EvnLabSample_id'])) {
			$this->textlog->add('Проба ' . $data['sampleResult']['Barcode'] . ' найдена (EvnLabSample_id = ' . $resp[0]['EvnLabSample_id'] . '), импортируем результаты');

			$data['EvnLabSample_id'] = $resp[0]['EvnLabSample_id'];

			// получаем все тесты по пробе
			$tests = $this->getTests($data);

			if ($tests && isset($tests[0]) && empty($tests[0]['Error_Msg'])) {
				$this->textlog->add('Список тестов по пробе получен успешно');
			} else {
				$this->textlog->add('Ошибка получения списка тестов по пробе');
				if (!empty($tests[0]['Error_Msg'])) {
					$this->textlog->add('Ошибка: ' . $tests[0]['Error_Msg']);
				}
			}

			$linktests = array();
			foreach ($tests as $test) {
				$linktests[$test['UslugaComplex_Code']] = array(
					'UslugaTest_id' => $test['UslugaTest_id'],
					'UslugaComplex_id' => $test['UslugaComplex_id']
				);
			}

			$this->load->model('EvnLabSample_model');
			$this->load->model('EvnLabRequest_model');
			if (isset($data['sampleResult']['TargetResults']['TargetResult']['Works']['Work'])) {
				// массив результатов
				if (isset($data['sampleResult']['TargetResults']['TargetResult']['Works']['Work'][1])) {
					$works = $data['sampleResult']['TargetResults']['TargetResult']['Works']['Work'];
				} else {
					$works = $data['sampleResult']['TargetResults']['TargetResult']['Works'];
				}

				foreach ($works as $work) {
					$code = $this->getCodeFromAISCode($work);
					if (!$code) {
						$this->textlog->add('Код исследования ' . $work['Code'] . ' не переведен');
						$code = $work['Code'];
					} else {
						$this->textlog->add('Код исследования ' . $work['Code'] . ' переведен: ' . $code);
					}

					if (isset($linktests[$code])) {
						$test = $linktests[$code];
						$dataForUpdate = array(
							'disableRecache' => true,
							'UslugaTest_id' => $test['UslugaTest_id'],
							'UslugaComplex_id' => $test['UslugaComplex_id'],
							'UslugaTest_ResultValue' => !empty($work['Value']) ? $work['Value'] : null,
							'UslugaTest_setDT' => date('Y-m-d H:i:s', strtotime($work['CreateDate'])),
							'RefValues_id' => null,
							'UslugaTest_ResultLower' => !empty($work['Norm']['LowLimit']) ? $work['Norm']['LowLimit'] : null,
							'UslugaTest_ResultUpper' => !empty($work['Norm']['HighLimit']) ? $work['Norm']['HighLimit'] : null,
							'UslugaTest_ResultUnit' => !empty($work['UnitName']) ? $work['UnitName'] : null,
							'UslugaTest_Comment' => '',
							'isAutoApprove' => true,
							'updateType' => 'fromLISwithRefValues',
							'session' => $data['session'],
							'pmUser_id' => $data['pmUser_id']
						);
						$this->EvnLabSample_model->updateResult($dataForUpdate);
					} else {
						$this->textlog->add('Тест ' . $code . ' не найден в пробе');
					}
				}

				// если есть брак пробы
				if (!empty($work['Defects'][0]['Code'])) {
					$defectCauseId = $this->getFirstResultFromQuery('
						SELECT
							DefectCauseType_id as \"DefectCauseType_id\"
						FROM
							lis.v_DefectCauseType with(nolock)
						WHERE
							DefectCauseType_Code = :DefectCauseType_Code	
					', array(
						'DefectCauseType_Code' => $work['Defects'][0]['Code']
					));
					if (empty($defectCauseId)) {
						$this->textlog->add('Код брака пробы ' . $work['Defects'][0]['Code'] . ' не найден в справочнике');
					} else {
						$sql = "
							UPDATE
								EvnLabSample with(rowlock)
							SET
								DefectCauseType_id = :DefectCauseType_id
							WHERE
								EvnLabSample_id = :EvnLabSample_id
						";
						$this->db->query($sql, array(
							'EvnLabSample_id' => $data['EvnLabSample_id'],
							'DefectCauseType_id' => $defectCauseId
						));
					}
				}

				$data['EvnLabRequest_id'] = $this->getFirstResultFromQuery("
					select top 1
						EvnLabRequest_id as \"EvnLabRequest_id\"
					from v_EvnLabSample with(nolock)
					where EvnLabSample_id = :EvnLabSample_id
				", array(
					'EvnLabSample_id' => $data['EvnLabSample_id']
				));

				$this->EvnLabRequest_id = $data['EvnLabRequest_id'];

				$this->EvnLabRequest_model->approveEvnLabRequestResults([
					'EvnLabRequests' => json_encode([$data['EvnLabRequest_id']])
				]);
				$this->textlog->add('Одобрение результатов ');

				$this->EvnLabSample_model->ReCacheLabSampleIsOutNorm(array(
					'EvnLabSample_id' => $data['EvnLabSample_id']
				));
				$this->textlog->add('Рекэш нормальности результатов пробы');

				$this->EvnLabSample_model->ReCacheLabSampleStatus(array(
					'EvnLabSample_id' => $data['EvnLabSample_id']
				));
				$this->textlog->add('Рекэш статуса пробы');

				if (!empty($data['EvnLabRequest_id'])) {
					// кэшируем статус заявки
					$this->EvnLabRequest_model->ReCacheLabRequestStatus(array(
						'EvnLabRequest_id' => $data['EvnLabRequest_id'],
						'pmUser_id' => $data['pmUser_id']
					));
					$this->textlog->add('Рекэш статуса заявки');

					// кэшируем статус проб в заявке
					$this->EvnLabRequest_model->ReCacheLabRequestSampleStatusType(array(
						'EvnLabRequest_id' => $data['EvnLabRequest_id'],
						'pmUser_id' => $data['pmUser_id']
					));
					$this->textlog->add('Рекэш статуса проб внутри заявки');
				}
			} else {
				$this->textlog->add('Результаты по пробе ' . $data['sampleResult']['Barcode'] . ' не получены');
			}
		} else {
			$this->textlog->add('Проба ' . ($data['sampleResult']['Barcode'] ?? '') . ' не найдена');
		}
	}


	function getCodeFromAISCode($data)
	{
		$query = "
			with mv as (
				SELECT  TOP 1
					VolumeType_id as at
				FROM
					v_VolumeType (nolock)
				WHERE
					1=1
					and (VolumeType_endDate is null or VolumeType_endDate = dbo.tzgetdate())
					and VolumeType_Code = 'АИСУслугаСистем'
				
				ORDER BY
					VolumeType_id
			)
			select
				a.Attribute_id
			from
				v_AttributeVision avis (nolock)
				inner join v_Attribute a (nolock) on a.Attribute_id = avis.Attribute_id
				inner join v_AttributeValueType avt (nolock) on avt.AttributeValueType_id = a.AttributeValueType_id
			where
				avis.AttributeVision_TableName = 'dbo.VolumeType'
				and avis.AttributeVision_TablePKey = (select at from mv)
				and a.Attribute_SysNick = 'UslugaComplex'
			order by avis.AttributeVision_Sort
		";

		if (empty($this->usCCode)) {
			$this->usCCode = $this->getFirstResultFromQuery($query);
		}

		$query = "
			select distinct
				avpid.AttributeValue_ValueIdent as UslugaComplex_id
			from
				v_AttributeValue av (nolock)
				inner join v_AttributeValue avpid (nolock) on avpid.AttributeValue_rid = av.AttributeValue_id OR avpid.AttributeValue_id = av.AttributeValue_id
				inner join v_Attribute a (nolock) on a.Attribute_id = avpid.Attribute_id
			where
				av.AttributeValue_ValueString = :UslugaComplex_Code
				and cast(dbo.tzgetdate() as date) >= isnull(av.AttributeValue_begDate, cast(dbo.tzgetdate() as date))
				and cast(dbo.tzgetdate() as date) <= isnull(av.AttributeValue_endDate, cast(dbo.tzgetdate() as date))
				and a.Attribute_id = :Attribute_id
		";
		$params = [
			'Attribute_id' => $this->usCCode,
			'UslugaComplex_Code' => $data['Code']
		];

		$uslugacomplex_id = $this->getFirstResultFromQuery($query, $params);

		return $this->getFirstResultFromQuery("
			select
				UslugaComplex_Code
			from v_UslugaComplex with(nolock)
			where UslugaComplex_id = :UslugaComplex_id
		", [
			'UslugaComplex_id' => $uslugacomplex_id
		]);
	}

	/**
	 * Получение тестов по пробе
	 */
	function getTests($data)
	{
		return $this->queryResult("
			select
				UslugaTest_id,
				uc.UslugaComplex_id,
				uc.UslugaComplex_Code
			from
				v_EvnLabSample els with(nolock)
				inner join v_UslugaTest ut with(nolock) ON ut.EvnLabSample_id = els.EvnLabSample_id
				inner join v_UslugaComplex uc with(nolock) ON uc.UslugaComplex_id = ut.UslugaComplex_id
			where
				ut.EvnLabSample_id = :EvnLabSample_id
			", $data);
	}

	/**
	 * создание xml для запроса на выгрузку
	 */
	function makeUnloadRequest($data)
	{
		$patient = $this->getPatient($data);
		$samplesData = $this->getSamples($data);
		$xmls = array();
		foreach ($samplesData as $oneSampleData) {
			$xml = new SimpleXMLElement('<xml/>');

			$requestFilter = $xml->addChild('RequestFilter');
			$requestFilter->addAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
			$requestFilter->addAttribute('xmlns:xsd', "http://www.w3.org/2001/XMLSchema");
			{
				$requestFilter->addChild('FirstName', $patient['FirstName']);
				$requestFilter->addChild('LastName', $patient['LastName']);
				$requestFilter->addChild('MiddleName', $patient['MiddleName']);
				$requestFilter->addChild('BirthDate', $patient['BirthDate']);
				$requestFilter->addChild('Sex', $patient['Sex']);

				$requestCodes = $requestFilter->addChild('RequestCodes');
				$requestCodes->addChild('String', $oneSampleData['Barcode']);
			}

			$xmls[] = $xml;
		}

		return $xmls;
	}
}
