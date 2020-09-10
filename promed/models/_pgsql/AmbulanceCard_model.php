<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* AmbulanceCard_model - модель для работы с картами вызова.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2010 Swan Ltd.
* @author       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
* @version      05.01.2010
*/
class AmbulanceCard_model extends swPgModel
{
	/**
	 * Кэш для списка исходов по кодам
	 */
	private $_resultDeseaseTypeList = array();

	/**
	 * Кэш для списка исходов по LeaveType_id
	 */
	private $_resultDeseaseLeaveTypeList = array();

	/**
	 * Кэш для списка МО (ключ: SMPB или SMPT)
	 */
	private $_lpuList = array();

	/**
	 * Кэш для списка подразделений (ключ: SMPB_STBR или SMPT_STAN)
	 */
	private $_lpuBuildingList = array();

	/**
	 * Идентификатор для вида оплаты ОМС
	 */
	private $_payTypeOms_id = null;

	/**
	 * Идентификатор для вида оплаты Бюджет субъекта РФ
	 */
	private $_payTypeBud_id = null;

	protected $ServiceListLog_id = null;

	/**
	 *	Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->_payTypeOms_id = $this->getFirstResultFromQuery("select PayType_id  as \"PayType_id\" from v_PayType  where PayType_SysNick = 'oms' limit 1", array());


		if ( $this->regionNick == 'kareliya' ) {
			$this->_payTypeBud_id = $this->getFirstResultFromQuery("select PayType_id  as \"PayType_id\" from v_PayType  where PayType_SysNick = 'subrf' limit 1", array());

		}
	}
	
	/**
	* Сохранение медикамента
	*/
	function saveAmbulanceDrug($data)
	{
		$queryParams = array();
		$queryParams['CmpDrug_id'] = $data['CmpDrug_id'];
		$queryParams['CmpCallDrug_Kolvo'] = $data['CmpDrug_Kolvo'];
		$queryParams['CmpCallCard_id'] = $data['AmbulanceCard_id'];
		$queryParams['pmUser_id'] = $data['pmUser_id'];
		$sql = "
		    select Error_Code as \"Error_Code\", error_message as \"Error_Msg\", CmpCallDrug_id as \"CmpCallDrug_id\"
			from p_CmpCallDrug_ins(
				CmpCallDrug_id := null,
				CmpCallCard_id := :CmpCallCard_id,
				CmpDrug_id := :CmpDrug_id,
				CmpCallDrug_Kolvo := :CmpCallDrug_Kolvo,
				pmUser_id := :pmUser_id);

		";
		$result = $this->db->query($sql, $queryParams);
		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}			
	}
	
	/**
	* Сохранение данных карты
	*/
	function saveAmbulanceCard($data)
	{
		$response = array();
		$corrected_data = array();
		$ngod = (isset($data['NGOD'])?$data['NGOD']:'не определен');
		$this->load->library('textlog', array('file'=>'AmbulanceCard_'.date('Y-m-d').'.log','logging' => false));
		$this->textlog->add('['.$ngod.'] Сохраняем карту вызова');

		// @todo начало логирования в базу
		$begDT = date('Y-m-d H:i:s');
		$this->load->model('ServiceList_model');
		$ServiceList_id = $this->ServiceList_model->getServiceListId('ImportDateSMP');

		$resp = $this->ServiceList_model->saveServiceListLog(array(
			'ServiceListLog_id' => null,
			'ServiceList_id' => $ServiceList_id,
			'ServiceListLog_begDT' => $begDT,
			'ServiceListResult_id' => 2,
			'pmUser_id' => 1
		));

		if (!$this->isSuccessful($resp)) {
			throw new Exception($resp[0]['Error_Msg'], $resp[0]['Error_Code']);
		}
		$this->ServiceListLog_id = $resp[0]['ServiceListLog_id'];

		//исправляем айдишники по заданым кодам
		$resp = $this->saveAmbulanceCardPrepareId($data);
		if (!empty($resp['Error_Msg']))  {
			$this->ServiceList_model->saveServiceListDetailLog(array(
				'ServiceListLog_id' => $this->ServiceListLog_id,
				'ServiceListLogType_id' => 2,
				'ServiceListDetailLog_Message' => $resp['Error_Msg'],
				'pmUser_id' => 1
			));

			$this->textlog->add('['.$ngod.'] '.$resp['Error_Msg']);
			if ( in_array($this->regionNick, array('ekb', 'kareliya')) ) {
				return array(array('success' => false, 'Error_Msg' => $resp['Error_Msg']));
			}
		}
		$corrected_data = array_merge($corrected_data,$resp['data']);
		$data['Sex_id'] = !empty($corrected_data['Sex_id']) ? $corrected_data['Sex_id'] : null;

		$data['VOZR'] = (!empty($data['VOZR']) ? intval($data['VOZR']) : null);

		//идентифицируем человека
		$resp = $this->saveAmbulanceCardPreparePersonId($data);
		if ( is_array($resp) && count($resp) > 0 ) {
			$this->ServiceList_model->saveServiceListDetailLog(array(
				'ServiceListLog_id' => $this->ServiceListLog_id,
				'ServiceListLogType_id' => 2,
				'ServiceListDetailLog_Message' => ( !empty($resp[0]) && !empty($resp[0]['Error_Msg']) ) ? $resp[0]['Error_Msg'] : 'Ошибка идентификации пациента',
				'pmUser_id' => 1
			));

			return $resp;
		}
		else if ( !empty($resp) ) {
			$corrected_data = array_merge($corrected_data,array('Person_id' => $resp));
		}

		// Запрашиваем идентификаторы КЛАДР
		$resp = $this->getKladrAddress($data);
		if ($resp != null)
			$corrected_data = array_merge($corrected_data, array(
				'KLRgn_id' => $resp['KLRgn_id'],
				'KLSubRgn_id' => $resp['KLSubRgn_id'],
				'KLCity_id' => $resp['KLCity_id'],
				'KLTown_id' => $resp['KLTown_id'],
				'KLStreet_id' => $resp['KLStreet_id']
				));

		// Если врач установлен
		if (isset($corrected_data['MedPersonal_id']) && (!empty($corrected_data['MedPersonal_id']))) {
			$corrected_data['CmpCallCard_IsMedPersonalIdent'] = 2; // YesNo
		}
		//Корректировка данных импорта из dbf
		if (isset($data['KOD1']) && (!empty($data['KOD1']))) {
			$corrected_data['PBDD'] = $data['KOD1'];
		}
		if (isset($data['TABN']) && (!empty($data['TABN']))) {
			$corrected_data['Tabn'] = $data['TABN'];
		}

		// массив название параметра => название поля
		$params_name = array(
			'CmpCallCard_Numv' => array('form_field' => 'NUMV'),
			'CmpCallCard_Ngod' => array('form_field' => 'NGOD'),
			'CmpCallCard_Prty' => array('form_field' => 'PRTY'),
			'CmpCallCard_Sect' => array('form_field' => 'SECT'),
			'CmpCallCard_City' => array('form_field' => 'CITY'),
			'CmpCallCard_Ulic' => array('form_field' => 'ULIC'),
			'CmpCallCard_Dom' => array('form_field' => 'DOM'),
			'CmpCallCard_Kvar' => array('form_field' => 'KVAR'),
			'CmpCallCard_Podz' => array('form_field' => 'PODZ'),
			'CmpCallCard_Etaj' => array('form_field' => 'ETAJ'),
			'CmpCallCard_Kodp' => array('form_field' => 'KODP'),
			'CmpCallCard_Telf' => array('form_field' => 'TELF'),
			//'cmpPlace_Str' => array('form_field' => 'PLS'),
			//'cmpPlace_Code' => array('form_field' => 'PLC'),
			//'cmpArea_Str' => array('form_field' => 'ARS'),
			//'cmpArea_Code' => array('form_field' => 'ARC'),
			//'cmpReason_Str' => array('form_field' => 'REAS'),
			//'cmpReason_Code' => array('form_field' => 'REAC'),
			//'cmpProfile_cStr' => array('form_field' => 'PROFS'),
			//'cmpProfile_cCode' => array('form_field' => 'PROFC'),
			//'cmpArea_gStr' => array('form_field' => 'ARGS'),
			//'cmpArea_gCode' => array('form_field' => 'ARGC'),
			//'cmpArea_pStr' => array('form_field' => 'ARPS'),
			//'cmpArea_pCode' => array('form_field' => 'ARPC'),
			//'cmpDiag_oStr' => array('form_field' => 'DIAGS'),
			//'cmpDiag_oCode' => array('form_field' => 'DIAGC'),
			//'cmpDiag_aStr' => array('form_field' => 'DIAGS1'),
			//'cmpDiag_aCode' => array('form_field' => 'DIAGC1'),
			//'cmpProfile_bStr' => array('form_field' => 'PRFBS'),
			//'cmpProfile_bCode' => array('form_field' => 'PRFBC'),
			//'CmpResult_Str' => array('form_field' => 'REZLS'),
			//'CmpResult_Code' => array('form_field' => 'REZLC'),
			//'cmpTrauma_Str' => array('form_field' => 'TRAVS'),
			//'cmpTrauma_Code' => array('form_field' => 'TRAVC'),
			//'Diag_uCode' => array('form_field' => 'DGUC'),
			//'Diag_sCode' => array('form_field' => 'DGSC'),
			//'lpu_Code' => array('form_field' => 'LPUC'),
			//'lpu_Str' => array('form_field' => 'LPUS'),
			//'cmpCallType_Str' => array('form_field' => 'CLTPS'),
			//'cmpCallType_Code' => array('form_field' => 'CLTPC'),
			//'cmpTalon_Str' => array('form_field' => 'TALS'),
			//'cmpTalon_Code' => array('form_field' => 'TALC'),
			'person_BirthDay' => array('form_field' => 'PBDD'),
			'CmpCallCard_Izv1' => array('form_field' => 'CCIZVS'),
			'CmpCallCard_Tiz1' => array('form_field' => 'CCTIZD'),
			'CmpCallCard_PCity' => array('form_field' => 'CCPCS'),
			'CmpCallCard_PUlic' => array('form_field' => 'CCPUS'),
			'CmpCallCard_PDom' => array('form_field' => 'CCPDS'),
			'CmpCallCard_PKvar' => array('form_field' => 'CCPKS'),
			'Person_PolisSer' => array('form_field' => 'PPSS'),
			'Person_PolisNum' => array('form_field' => 'PPNS'),
			'CmpCallCard_Medc' => array('form_field' => 'CCMI'),
			'Person_Age' => array('form_field' => 'VOZR'),
			'CmpCallCard_Comm' => array('form_field' => 'COMM'),
			'Person_SurName' => array('form_field' => 'FAM'),
			'Person_FirName' => array('form_field' => 'IMYA'),
			'Person_SecName' => array('form_field' => 'OTCH'),
			//'person_Sex' => array('form_field' => 'POL'),
			'CmpCallCard_Ktov' => array('form_field' => 'KTOV'),
			'CmpCallCard_Smpt' => array('form_field' => 'SMPT'),
			'CmpCallCard_Stan' => array('form_field' => 'STAN'),
			'CmpCallCard_prmDT' => array('form_field' => 'DPRM'),
			'CmpCallCard_IsAlco' => array('form_field' => 'ALK'),
			'CmpCallCard_Numb' => array('form_field' => 'NUMB'),
			'CmpCallCard_Smpb' => array('form_field' => 'SMPB'),
			'CmpCallCard_Stbr' => array('form_field' => 'STBR'),
			'CmpCallCard_Stbb' => array('form_field' => 'STBB'),
			'CmpCallCard_Ncar' => array('form_field' => 'NCAR'),
			'CmpCallCard_RCod' => array('form_field' => 'RCOD'),
			'CmpCallCard_TabN' => array('form_field' => 'Tabn'),
			'CmpCallCard_Tab2' => array('form_field' => 'TAB2'),
			'CmpCallCard_Tab3' => array('form_field' => 'TAB3'),
			'CmpCallCard_Tab4' => array('form_field' => 'TAB4'),
			'CmpCallCard_Expo' => array('form_field' => 'EXPO'),
			'CmpCallCard_Dokt' => array('form_field' => 'DOKT'),
			'MedPersonal_id' => array(),
			'MedStaffFact_id' => array(),
			'CmpCallCard_IsMedPersonalIdent' => array(),
			'CmpCallCard_Smpp' => array('form_field' => 'SMPP'),
			'CmpCallCard_Vr51' => array('form_field' => 'VR51'),
			'CmpCallCard_D201' => array('form_field' => 'D201'),
			'CmpCallCard_Dsp1' => array('form_field' => 'DSP1'),
			'CmpCallCard_Dsp2' => array('form_field' => 'DSP2'),
			'CmpCallCard_Dspp' => array('form_field' => 'DSPP'),
			'CmpCallCard_Dsp3' => array('form_field' => 'DSP3'),
			'CmpCallCard_Kakp' => array('form_field' => 'KAKP'),
			'CmpCallCard_Tper' => array('form_field' => 'TPER'),
			'CmpCallCard_Vyez' => array('form_field' => 'VYEZ'),
			'CmpCallCard_Przd' => array('form_field' => 'PRZD'),
			'CmpCallCard_Tgsp' => array('form_field' => 'TGSP'),
			'CmpCallCard_Tsta' => array('form_field' => 'TSTA'),
			'CmpCallCard_Tisp' => array('form_field' => 'TISP'),
			'CmpCallCard_Tvzv' => array('form_field' => 'TVZV'),
			'CmpCallCard_Kilo' => array('form_field' => 'KILO'),
			'CmpCallCard_Dlit' => array('form_field' => 'DLIT'),
			'CmpCallCard_Prdl' => array('form_field' => 'PRDL'),
			'CmpCallCard_IsPoli' => array('form_field' => 'POLI'),
			'CmpCallCard_Line' => array('form_field' => 'novalue1'),
			'CmpCallCard_Inf1' => array('form_field' => 'INF1'),
			'CmpCallCard_Inf2' => array('form_field' => 'INF2'),
			'CmpCallCard_Inf3' => array('form_field' => 'INF3'),
			'CmpCallCard_Inf4' => array('form_field' => 'INF4'),
			'CmpCallCard_Inf5' => array('form_field' => 'INF5'),
			'CmpCallCard_Inf6' => array('form_field' => 'INF6'),
			'CmpArea_id' => array(),
			'CmpArea_gid' => array(),
			'CmpArea_pid' => array(),
			'CmpPlace_id' => array(),
			'CmpReason_id' => array(),
			'CmpDiag_oid' => array(),
			'CmpDiag_aid' => array(),
			'CmpProfile_cid' => array(),
			'CmpProfile_bid' => array(),
			'CmpResult_id' => array(),
			'CmpTrauma_id' => array(),
			'Diag_uid' => array(),
			'Diag_sid' => array(),
			'CmpLpu_id' => array(),
			'LeaveType_id'=>array(),
			'Sex_id' => array(),
			'CmpCallType_id' => array(),
			'CmpTalon_id' => array(),
			'Person_id' => array(),
			'KLRgn_id' => array(),
			'KLSubRgn_id' => array(),
			'KLCity_id' => array(),
			'KLTown_id' => array(),
			'KLStreet_id' => array(),
			'Lpu_id' => array(),
			'LpuBuilding_id' => array(),
			'Lpu_ppdid' => array(),
			'CmpCallCard_IsReceivedInPPD' => array(),
			'CmpPPDResult_id' => array(),
			'EmergencyTeam_id' => array(),
			'ResultDeseaseType_id' => array(),
			'UslugaComplex_id' => array(),
			'CmpCallerType_id' => array(),
			'CmpCallCardInputType_id' => array(),
			'PayType_id' => array(),
		);

		if ( in_array($this->regionNick, array('ekb', 'kareliya', 'perm')) ) {
			$params_name['CmpCallCard_PCity'] = array('form_field' => 'PCTY');
			$params_name['CmpCallCard_PUlic'] = array('form_field' => 'PULC');
			$params_name['CmpCallCard_PDom'] = array('form_field' => 'PDOM');
			$params_name['CmpCallCard_PKvar'] = array('form_field' => 'PKVR');
			$params_name['person_BirthDay'] = array('form_field' => 'KOD1');
		}

		$queryParams = array();
		// По идее теперь не надо
		/*
		select top 100  CmpPlace_id, CmpReason_id, CmpDiag_oid, CmpDiag_aid, CmpProfile_cid, CmpProfile_bid, CmpResult_id, CmpTrauma_id from cmpcallcard  where  CmpReason_id is null


		$queryParams['Lpu_id'] = null;

		if (strlen($data['SMPT'])>0) {
			$sql = "
				Select Lpu_id from CmpStation  where CmpStation_Code = :SMPT

			";
			$result = $this->db->query($sql, array('SMPT'=>$data['SMPT']));
			if ( is_object($result) ) {
				$r = $result->result('array');
				if (count($r)>0) {
					$queryParams['Lpu_id'] = $r[0]['Lpu_id'];
				}
			}

		}
		*/

		// пользователь
		$queryParams['pmUser_id'] = $data['pmUser_id'];

		// формирование данных для запроса и их корректировка
		foreach($params_name as $k => $v) {
			$value = null;
			if (isset($v['form_field']) && isset($data[$v['form_field']])) {
				if (is_object($data[$v['form_field']]) && get_class($data[$v['form_field']]) == 'DateTime') {
					$value = $data[$v['form_field']]->format('Y-m-d H:i:s');
				} else {
					$value = trim($data[$v['form_field']]);
				}
			}
			if ($value != null)
				$queryParams[$k] = $value;
		}
		foreach($corrected_data as $k => $v) {
			$queryParams[$k] = $v;
		}

		// Получаем идентификатор места работы врача
		// @task https://redmine.swan.perm.ru/issues/79584
		if ( !empty($queryParams['Lpu_id']) && !empty($queryParams['MedPersonal_id']) ) {
			if ( empty($queryParams['LpuBuilding_id']) ) {
				$queryParams['LpuBuilding_id'] = null;
			}

			$queryParams['MedStaffFact_id'] = $this->getFirstResultFromQuery("
				select msf.MedStaffFact_id as \"MedStaffFact_id\"
				from v_MedStaffFact msf 

					left join v_LpuUnit lu  on lu.LpuUnit_id = msf.LpuUnit_id

				where msf.MedPersonal_id = :MedPersonal_id
					and msf.Lpu_id = :Lpu_id
					and COALESCE(msf.MedStaffFactCache_IsDisableInDoc, 1) <> 2

					and (msf.WorkData_begDate is null or msf.WorkData_begDate <= cast(:CmpCallCard_prmDT as date))
					and (msf.WorkData_endDate is null or msf.WorkData_endDate >= cast(:CmpCallCard_prmDT as date))
				order by
					case
						when lu.LpuBuilding_id = COALESCE(:LpuBuilding_id, 0) then 1

						else 2
					end,
					case
						when lu.LpuUnitType_id = 13 then 1
						when lu.LpuUnitType_id = 14 then 2
						else 3
					end,
					msf.PostOccupationType_id
			    limit 1
			", $queryParams);

			if ( $queryParams['MedStaffFact_id'] === false ) {
				$queryParams['MedStaffFact_id'] = null;
			}

			$this->textlog->add('['.$ngod.'] определили MedStaffFact_id = ' . $queryParams['MedStaffFact_id'] . '.');
		}

		//Проставляем стандартные значения для добавления
		$cardExists = false;
		$id = 0;
		$proc = 'ins';

		$queryParams['CmpCallCardInputType_id'] = 1;
		$queryParams['PayType_id'] = $this->_payTypeOms_id;

		if ( $this->regionNick == 'kareliya' && !in_array(mb_strtoupper($data['PRFB']), array('Л','К','Е','Р','Ф')) ) {
			$queryParams['PayType_id'] = $this->_payTypeBud_id;
		}

		//провряем есть ли карта СМП в промеде
		$checkExists = $this->checkExistCmpCallCard($queryParams);
		if( is_array($checkExists) ) {
			if (!empty($checkExists[0]['CmpCallCard_id'])) {
				$queryParams['CmpCallCard_id'] = $checkExists[0]['CmpCallCard_id'];
				$cardExists = true;
				$this->textlog->add('['.$ngod.'] карта найдена в системе, CmpCallCard_id = ' . $queryParams['CmpCallCard_id'] . '.');
			} else {
				$this->textlog->add('['.$ngod.'] ошибка проверки карты в системе [1].');

				$this->ServiceList_model->saveServiceListDetailLog(array(
					'ServiceListLog_id' => $this->ServiceListLog_id,
					'ServiceListLogType_id' => 2,
					'ServiceListDetailLog_Message' => '['.$ngod.'] ошибка проверки карты в системе [1].',
					'pmUser_id' => 1
				));

				return array(array(
					'success' => false,
					'exists' => true
				));
			}

			//проверяем нужно ли обновить карту
			$needToReload = $this->needToRealoadCmpCallCard($queryParams);
			if (is_array($needToReload) && !empty($needToReload[0]['RegistryQueue_id'])) {

				$this->ServiceList_model->saveServiceListDetailLog(array(
					'ServiceListLog_id' => $this->ServiceListLog_id,
					'ServiceListLogType_id' => 2,
					'ServiceListDetailLog_Message' => '['.$ngod.'] ошибка импорта карты, реестр содержащий карту находится в очереди, обновление карты невозможно.',
					'pmUser_id' => 1
				));

				$this->textlog->add('['.$ngod.'] ошибка импорта карты, реестр содержащий карту находится в очереди, обновление карты невозможно.');
				return array(array(
					'success' => false,
					'exists' => true
				));
			} else if (is_array($needToReload) && !empty($needToReload[0]['RegistryCheckStatus_Code']) && in_array($needToReload[0]['RegistryCheckStatus_Code'], array(0,1,3,4,7,8))) {

				$this->ServiceList_model->saveServiceListDetailLog(array(
					'ServiceListLog_id' => $this->ServiceListLog_id,
					'ServiceListLogType_id' => 2,
					'ServiceListDetailLog_Message' => '['.$ngod.'] ошибка импорта карты, реестр содержащий карту уже отправлен в ТФОМС или оплачен ( код '.$needToReload[0]['RegistryCheckStatus_Code'].' ), обновление карты невозможно.',
					'pmUser_id' => 1
				));

				$this->textlog->add('['.$ngod.'] ошибка импорта карты, реестр содержащий карту уже отправлен в ТФОМС или оплачен ( код '.$needToReload[0]['RegistryCheckStatus_Code'].' ), обновление карты невозможно.');
				return array(array(
					'success' => false,
					'exists' => true
				));
			}

			//Если надо - обновляем карту, если обновили карту - выставляем реестру признак на переформирование
			$existChangeInCCC = $this->existChangeInCCC($queryParams, $params_name);

			if (is_array($existChangeInCCC)){
				if (array_key_exists('needUpdate', $existChangeInCCC) && $existChangeInCCC['needUpdate'] === true) {
					$existChangeInCCC['response']['pmUser_id'] = $data['pmUser_id'];
					$id = $queryParams['CmpCallCard_id'];
					unset($queryParams['CmpCallCard_id']);
					$proc = 'upd';
					$queryParams = $existChangeInCCC['response'];
				} else {
					$this->ServiceList_model->saveServiceListDetailLog(array(
						'ServiceListLog_id' => $this->ServiceListLog_id,
						'ServiceListLogType_id' => 2,
						'ServiceListDetailLog_Message' => '['.$ngod.'] ошибка импорта карты, карта уже существует в системе!',
						'pmUser_id' => 1
					));

					$this->textlog->add('['.$ngod.'] ошибка импорта карты, карта уже существует в системе!');
					return array(array(
						'success' => false,
						'exists' => true
					));
				}
			} else {
				$this->textlog->add('['.$ngod.'] ошибка импорта карты, ошибка при проверке необходимости обновления карты.');

				$this->ServiceList_model->saveServiceListDetailLog(array(
					'ServiceListLog_id' => $this->ServiceListLog_id,
					'ServiceListLogType_id' => 2,
					'ServiceListDetailLog_Message' => '['.$ngod.'] ошибка импорта карты, ошибка при проверке необходимости обновления карты.',
					'pmUser_id' => 1
				));

				return array(array(
					'success' => false,
					'exists' => true
				));
			}

			if (is_array($needToReload) && !empty($needToReload[0]['Registry_id']) && !empty($needToReload[0]['RegistryCheckStatus_Code']) && in_array($needToReload[0]['RegistryCheckStatus_Code'], array(2,5,6))) {
				//надо выставить реестру признак переформирования
				$isNeedReform = true;
			}
		}

		if( isset($queryParams['KLCity_id']) && $queryParams['KLCity_id'] == 3310 ) {
			$r = $this->getAttachLpuAddress($queryParams);
			if (is_array($r)) {
				$queryParams['Lpu_ppdid'] = $r['Lpu_id'];
			} else {
				$queryParams['Lpu_ppdid'] = $r;
			}
		}

		if ( !empty($queryParams['CmpCallCard_IsPoli']) ) {
			if ( in_array($queryParams['CmpCallCard_IsPoli'], array('+', '*')) ) {
				$queryParams['CmpCallCard_IsPoli'] = 1;
			}
			else if ( in_array($queryParams['CmpCallCard_IsPoli'], array('-')) ) {
				$queryParams['CmpCallCard_IsPoli'] = 0;
			}
			else {
				$queryParams['CmpCallCard_IsPoli'] = null;
			}
		}

		//print_r($queryParams); exit();

		// генерация запроса
		$sql = "
			SELECT
			 CmpCallCard_id as \"CmpCallCard_id\", Error_Code as \"Error_Code\", error_message as \"Error_Msg\"
			FROM p_CmpCallCard_{$proc}(
				CmpCallCard_id := {$id}
				";

		if ($proc == 'setCardUpd') {
			foreach($queryParams as $k => $v)
				$sql .= ",".$k." := :".$k."";

		} else {
			foreach($params_name as $k => $v)
				if (isset($queryParams[$k]))
					$sql .= ",".$k." := :".$k."";
			$sql .= ",pmUser_id := :pmUser_id";
		}

		$sql .= ");
		";

		try {
			if ( empty($queryParams['LeaveType_id']) ) {
				$this->textlog->add('['.$ngod.'] Алярма! Пустой LeaveType_id! ' . getDebugSQL($sql, $queryParams));
			}
			//echo getDebugSQL($sql, $queryParams);die;
			$result = $this->db->query($sql, $queryParams);
		} catch (Exception $e) {
			sql_log_message('error','CMPCallCard error exec query: ',getDebugSql($sql, $queryParams));

			$this->ServiceList_model->saveServiceListDetailLog(array(
				'ServiceListLog_id' => $this->ServiceListLog_id,
				'ServiceListLogType_id' => 2,
				'ServiceListDetailLog_Message' => '['.$ngod.'] ошибка импорта карты при сохранении в БД',
				'pmUser_id' => 1
			));

			$this->textlog->add('['.$ngod.'] ошибка импорта карты при сохранении в БД');
			return array(array('success' => false, 'Error_Code' => $e->getCode(), 'Error_Msg' => $e->getMessage()));
		}
		if (!is_object($result)) {
			return false;
		}

		$endDT = date('Y-m-d H:i:s');
		$resp = $this->ServiceList_model->saveServiceListLog(array(
			'ServiceListLog_id' => $this->ServiceListLog_id,
			'ServiceList_id' => $ServiceList_id,
			'ServiceListLog_begDT' => $begDT,
			'ServiceListLog_endDT' => $endDT,
			'ServiceListResult_id' => 1,
			'pmUser_id' => 1
		));

		$this->textlog->add('['.$ngod.'] карта вызова сохранена в БД успешно');
		if (!empty($isNeedReform) && $isNeedReform) {

			//Выставляем реестру признак на переформирование
			$query = "update Registry set Registry_isNeedReform = 2 where Registry_id = ".$needToReload[0]['Registry_id']."";
			$this->db2->query($query);
		}

		$response = $result->result('array');

		if ( !is_array($response) || count($response) == 0 ) {
			$this->textlog->add('['.$ngod.'] Ошибка при сохранении карты (строка ' . __LINE__ . ')');
			return false;
		}

		$response[0]['exists'] = $cardExists;

		$this->textlog->add('['.$ngod.'] Сохраняем услугу');
		// Добавляем услугу
		// @task https://redmine.swan.perm.ru/issues/65634
		if ( $this->getRegionNick() == 'perm' && empty($response[0]['Error_Msg']) && !empty($queryParams['UslugaComplex_id']) ) {
			// Проверяем наличие услуг в карте
			// Доработал условие на выборку услуг по карте
			// @task https://redmine.swan.perm.ru/issues/110645
			$CmpCallCardUsluga_id = null;
			$resp_usluga = $this->queryResult("
				select
					CmpCallCardUsluga_id as \"CmpCallCardUsluga_id\",
					UslugaComplex_id as \"UslugaComplex_id\"
				from
					v_CmpCallCardUsluga 

				where
					CmpCallCard_id = :CmpCallCard_id
					and pmUser_insID = :pmUser_id
			", array(
				'CmpCallCard_id' => $response[0]['CmpCallCard_id'],
				'pmUser_id' => $data['pmUser_id'],
			));

			foreach($resp_usluga as $one_usluga) {
				// удаляем все
				if (empty($CmpCallCardUsluga_id) && $one_usluga['UslugaComplex_id'] == $queryParams['UslugaComplex_id']) {
					$this->textlog->add('['.$ngod.'] Услуга уже сохранена, будем её апдейтить');
					$CmpCallCardUsluga_id = $one_usluga['CmpCallCardUsluga_id'];
				} else {
					$this->textlog->add('['.$ngod.'] Удаляем другую услугу');
					$query = "
						select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
						from p_CmpCallCardUsluga_del(
							CmpCallCardUsluga_id := :CmpCallCardUsluga_id);
					";
					$resp_usluga_del = $this->queryResult($query, array(
						'CmpCallCardUsluga_id' => $one_usluga['CmpCallCardUsluga_id']
					));

					if (!empty($resp_usluga_del[0]['Error_Msg'])) {
						$this->textlog->add('['.$ngod.'] Ошибка при удалении: '.$resp_usluga_del[0]['Error_Msg']);
					}
				}
			}

			$proc_usluga = "p_CmpCallCardUsluga_ins";
			if (!empty($CmpCallCardUsluga_id)) {
				$proc_usluga = "p_CmpCallCardUsluga_upd";
			}

			$query = "
				WITH cte AS (
                	SELECT
						cast(:CmpCallCard_prmDT as date) AS setDate,
						cast(:CmpCallCard_prmDT as time) AS setTime,
						(select UslugaCategory_id from v_UslugaComplex  where UslugaComplex_id = :UslugaComplex_id limit 1) AS ucc,
						UslugaComplexTariff_Tariff AS ucp,
						UslugaComplexTariff_id AS uct
				from v_UslugaComplexTariff 
				where UslugaComplex_id = :UslugaComplex_id
					and (UslugaComplexTariff_begDate is null or UslugaComplexTariff_begDate <= cast(:CmpCallCard_prmDT as date))
					and (UslugaComplexTariff_endDate is null or UslugaComplexTariff_endDate > cast(:CmpCallCard_prmDT as date))
					and LpuUnitType_id = " . (!empty($queryParams['CmpCallCard_Prty']) && $queryParams['CmpCallCard_Prty'] == 7 ? 14 : 13) . " -- 13 - LpuUnitType_Code = 12; 14 - LpuUnitType_Code = 13
                 limit 1
                )
select CmpCallCardUsluga_id as \"CmpCallCardUsluga_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				from {$proc_usluga}(
					CmpCallCardUsluga_id := :CmpCallCardUsluga_id,
					CmpCallCard_id := :CmpCallCard_id,
					CmpCallCardUsluga_setDate := (SELECT setDate FROM cte),
					CmpCallCardUsluga_setTime := (SELECT setTime FROM cte),
					MedStaffFact_id := :MedStaffFact_id,
					PayType_id := :PayType_id,
					UslugaCategory_id := (SELECT ucc FROM cte),
					UslugaComplex_id := :UslugaComplex_id,
					UslugaComplexTariff_id := (SELECT uct FROM cte),
					CmpCallCardUsluga_Cost := (SELECT ucp FROM cte),
					CmpCallCardUsluga_Kolvo := 1,
					pmUser_id := :pmUser_id);


			";

			$params = array(
				'CmpCallCardUsluga_id' => $CmpCallCardUsluga_id,
				'CmpCallCard_id' => $response[0]['CmpCallCard_id'],
				'CmpCallCard_prmDT' => $queryParams['CmpCallCard_prmDT'],
				'MedStaffFact_id' => (!empty($queryParams['MedStaffFact_id']) ? $queryParams['MedStaffFact_id'] : null),
				'PayType_id' => $this->_payTypeOms_id,
				'UslugaComplex_id' => $queryParams['UslugaComplex_id'],
				'Lpu_id' => (!empty($queryParams['Lpu_id']) ? $queryParams['Lpu_id'] : null),
				'pmUser_id' => $queryParams['pmUser_id']
			);

			try {
				$result = $this->db->query($query, $params);
			} catch (Exception $e) {
				sql_log_message('error', 'CMPCallCard error exec query: ', getDebugSql($query, $params));
				$this->textlog->add('[' . $ngod . '] ошибка импорта карты при сохранении услуги в БД');
			}

			if (!is_object($result)) {
				$this->textlog->add('[' . $ngod . '] Ошибка при сохранении услуги');
			}
			else {
				$this->textlog->add('[' . $ngod . '] Услуга успешно сохранена');

				$respTmp = $result->result('array');

				if (!is_array($respTmp) || count($respTmp) == 0) {
					$this->textlog->add('[' . $ngod . '] Ошибка при сохранении услуги (строка ' . __LINE__ . ')');
				} else if (!empty($respTmp[0]['Error_Msg'])) {
					$this->textlog->add('[' . $ngod . '] ' . $respTmp[0]['Error_Msg'] . ' (строка ' . __LINE__ . ')');
				}
			}
		} else {
			$this->textlog->add('['.$ngod.'] Нет возможности сохранить услугу: регион = '.$this->getRegionNick().", ошибка: ".((!empty($response[0]['Error_Msg']))?$response[0]['Error_Msg']:'нет').", UslugaComplex_id:".((!empty($queryParams['UslugaComplex_id']))?$queryParams['UslugaComplex_id']:'нет'));
		}
		return $response;
	}


	/**
	* Подготовкка ID для сохранения данных карты.
	*/
	function saveAmbulanceCardPrepareId($data) {
		$response = array();
		$response['data'] = array();
		$err = array('Error_Code' => '', 'Error_Msg' => '');
		$this->load->library('textlog', array('file'=>'AmbulanceCard_'.date('Y-m-d').'.log'));
		$ngod = (isset($data['NGOD'])?$data['NGOD']:'не определен');
		$this->textlog->add('['.$ngod.'] saveAmbulanceCardPrepareId определяем id для сохранения данных карты');

		$fields_array = array(
			'CmpArea_id' => array('code' => 'ARC', 'str' => 'ARS', 'tbl' => 'cmpArea'),
			'CmpArea_gid' => array('code' => 'ARGC', 'str' => 'ARGS', 'tbl' => 'cmpArea'),
			'CmpArea_pid' => array('code' => 'ARPC', 'str' => 'ARPS', 'tbl' => 'cmpArea'),
			'CmpPlace_id' => array('code' => 'PLC', 'str' => 'PLS', 'tbl' => 'CmpPlace'),
			'CmpReason_id' => array('code' => 'REAC', 'str' => 'REAS', 'tbl' => 'CmpReason'),
			'CmpDiag_oid' => array('code' => 'DIAGC', 'str' => 'DIAGS', 'tbl' => 'CmpDiag'),
			'CmpDiag_aid' => array('code' => 'DIAGC1', 'str' => 'DIAGS1', 'tbl' => 'CmpDiag'),
			'CmpProfile_cid' => array('code' => 'PROFC', 'str' => 'PROFS', 'tbl' => 'CmpProfile'),
			'CmpProfile_bid' => array('code' => 'PRFBC', 'str' => 'PRFBS', 'tbl' => 'CmpProfile'),
			'CmpResult_id' => array('code' => 'REZLC', 'str' => 'REZLS', 'tbl' => 'CmpResult'),
			'CmpTrauma_id' => array('code' => 'TRAV', 'tbl' => 'CmpTrauma'),
			'Diag_uid' => array('code' => 'DGUC', 'tbl' => 'Diag'),
			'Diag_sid' => array('code' => 'DGSC', 'tbl' => 'Diag'),
			'CmpLpu_id' => array('code' => 'LPUC', 'str' => 'LPUS', 'tbl' => 'CmpLpu'),
			'Lpu_id' => array('code' => 'SMPB', 'code1' => 'SMPT', 'code2' => 'STBR', 'code3' => 'STAN', 'tbl' => 'CmpStation'),
			'MedPersonal_id' => array('code' => 'DOKT', 'tbl' => 'MedPersonal'),
			'Sex_id' => array('code' => 'POL', 'tbl' => 'Sex'),
			'CmpCallType_id' => array('code' => 'CLTPC', 'str' => 'CLTPS', 'tbl' => 'CmpCallType'),
			'ResultDeseaseType_id' => array('code' => 'MEDS', 'tbl' => 'ResultDeseaseType'),
			'CmpTalon_id' => array('code' => 'TALC', 'str' => 'TALS', 'tbl' => 'CmpTalon'),
			'CmpCallerType_id' => array('code' => 'KTOV', 'str' => 'KTOV', 'tbl' => 'CmpCallerType'),
			'LpuBuilding_id' => array('code' => 'SMPB', 'code1' => 'SMPT', 'code2' => 'STBR', 'code3' => 'STAN', 'tbl' => 'LpuBuilding'),
		);

		if ( in_array($this->regionNick, array('ekb', 'kareliya', 'perm')) ) {
			$fields_array['CmpReason_id'] = array('code' => 'POVD', 'tbl' => 'CmpReason');
			$fields_array['Diag_uid'] = array('code' => 'MKB', 'tbl' => 'Diag');
			$fields_array['CmpResult_id'] = array('code' => 'REZL', 'tbl' => 'CmpResult');
			$fields_array['CmpProfile_cid'] = array('code' => 'PROF', 'tbl' => 'CmpProfile');
			$fields_array['CmpProfile_bid'] = array('code' => 'PRFB', 'tbl' => 'CmpProfile');
			$fields_array['CmpPlace_id'] = array('code' => 'MEST', 'tbl' => 'CmpPlace');
			$fields_array['CmpCallType_id'] = array('code' => 'POVT', 'tbl' => 'CmpCallType');
			$fields_array['UslugaComplex_id'] = array('code' => 'PRFB', 'code1' => 'MEST', 'code2' => 'REZL', 'code3'=>'INF6');

			if ( in_array($this->regionNick, array('kareliya', 'perm'))) {
				$fields_array['CmpDiag_oid'] = array('code' => 'DS1', 'str' => 'TDIAG', 'tbl' => 'CmpDiag');
				$fields_array['CmpDiag_aid'] = array('code' => 'DS2', 'tbl' => 'CmpDiag');
				$fields_array['Diag_sid'] = array('code' => 'DSHS', 'tbl' => 'Diag');
			}
		}

		foreach($fields_array as $key => $val) if ((isset($data[$val['code']])) || ($key=='MedPersonal_id') || ($key=='ResultDeseaseType_id') || ($key == 'CmpLpu_id' && isset($data[$val['str']]) && $data[$val['str']] != '')) {
			$id = null;
			$sql = "";
			$query_params = array();
			if ($key == 'CmpLpu_id' && !(isset($data[$val['code']]) && $data[$val['code']] != '')) {
				$sql = "select ".$val['tbl']."_id as \"id\" from v_".$val['tbl']."  where ".$val['tbl']."_Name = :str limit 1";

				$query_params = array('str' => $data[$val['str']]);
			}
			/*else {if ($key == 'CmpResult_id' && (isset($data[$val['code']]) && $data[$val['code']] != '')) {
				$sql = "select top 1 ".$val['tbl']."_id as id from v_".$val['tbl']."  where ".$val['tbl']."_Code = cast(:code as int)";

				$query_params = array('str' => $data[$val['str']]);
			}*/
			elseif ($key == 'Lpu_id') {
				switch ( $this->getRegionNick() ) {
					case 'ekb':
					case 'kareliya':
						// Определяем Lpu_id по кодам подстанций
						// @task https://redmine.swan-it.ru/issues/97561
						// @task https://redmine.swan-it.ru/issues/133298
						// @task https://redmine.swan-it.ru/issues/136833
						if ( !empty($data[$val['code']]) ) {
							if ( array_key_exists($data[$val['code']], $this->_lpuList) ) {
								$id = $this->_lpuList[$data[$val['code']]];
							}
							else if ( !empty($data[$val['code2']]) && array_key_exists($data[$val['code']] . "_" . $data[$val['code2']], $this->_lpuBuildingList) ) {
								$id = $this->_lpuBuildingList[$data[$val['code']] . "_" . $data[$val['code2']]]['Lpu_id'];
							}
							else {
								// Сперва ищем по SMPB
								$lpuIdList = $this->queryResult(
									"select distinct Lpu_id  as \"Lpu_id\" from v_LpuBuilding  where LpuBuilding_CmpStationCode = :SMPB limit 2",

									array('SMPB' => $data[$val['code']])
								);

								if ( !is_array($lpuIdList) ) {
									$this->textlog->add('['.$ngod.'] saveAmbulanceCardPrepareId ошибка запроса при определении Lpu_id по SMPB (' . $data[$val['code']] . ')');
								}
								else if ( count($lpuIdList) == 0 ) {
									$this->textlog->add('['.$ngod.'] saveAmbulanceCardPrepareId не найден Lpu_id по SMPB (' . $data[$val['code']] . ')');
									$this->_lpuList[$data[$val['code']]] = null;
								}
								else if ( count($lpuIdList) == 1 ) {
									$id = $lpuIdList[0]['Lpu_id'];
									$this->_lpuList[$data[$val['code']]] = $lpuIdList[0]['Lpu_id'];
								}
								else {
									$this->textlog->add('['.$ngod.'] saveAmbulanceCardPrepareId найдено несколько Lpu_id по SMPB (' . $data[$val['code']] . ')');

									// Ищем по SMPB и STBR
									if ( !empty($data[$val['code2']]) ) {
										$lpuIdList = $this->queryResult(
											"select distinct Lpu_id  as \"Lpu_id\" from v_LpuBuilding  where LpuBuilding_CmpStationCode = :SMPB and LpuBuilding_CmpSubstationCode = :STBR limit 2",

											array('SMPB' => $data[$val['code']], 'STBR' => $data[$val['code2']])
										);

										if ( !is_array($lpuIdList) ) {
											$this->textlog->add('['.$ngod.'] saveAmbulanceCardPrepareId ошибка запроса при определении Lpu_id по SMPB (' . $data[$val['code']] . ')) и STBR (' . $data[$val['code2']] . ')');
										}
										else if ( count($lpuIdList) == 0 ) {
											$this->textlog->add('['.$ngod.'] saveAmbulanceCardPrepareId не найден Lpu_id по SMPB (' . $data[$val['code']] . ') и STBR (' . $data[$val['code2']] . ')');
											$this->_lpuBuildingList[$data[$val['code']] . "_" . $data[$val['code2']]] = array('Lpu_id' => null, 'LpuBuilding_id' => null);
										}
										else if ( count($lpuIdList) == 1 ) {
											$id = $lpuIdList[0]['Lpu_id'];
											$this->_lpuBuildingList[$data[$val['code']] . "_" . $data[$val['code2']]] = array('Lpu_id' => $lpuIdList[0]['Lpu_id'], 'LpuBuilding_id' => null);
										}
										else {
											$this->textlog->add('['.$ngod.'] saveAmbulanceCardPrepareId найдено несколько Lpu_id по SMPB (' . $data[$val['code']] . ') и STBR (' . $data[$val['code2']] . ')');
											$this->_lpuBuildingList[$data[$val['code']] . "_" . $data[$val['code2']]] = array('Lpu_id' => null, 'LpuBuilding_id' => null);
										}
									}
								}
							}
						}

						$response['data'][$key] = $id;
					break;

					case 'perm':
						if ( !empty($data[$val['code']]) && !empty($data[$val['code2']]) ) {

							$prmDT = null;
							if ( array_key_exists('DPRM', $data) ) {
								if ( is_object($data['DPRM']) && get_class($data['DPRM']) == 'DateTime' ) {
									$prmDT = $data['DPRM']->format('Y-m-d H:i:s');
								}
								else if ( !empty($data['DPRM']) ) {
									$prmDT = trim($data['DPRM']);
								}
							}

							$lpuIdList = $this->queryResult(
								"select distinct LB.Lpu_id   as \"Lpu_id\"
											from v_LpuBuilding LB  

  												join v_Lpu L  ON L.Lpu_id = LB.Lpu_id 

											where LB.LpuBuilding_CmpStationCode = :SMPB 
											and LB.LpuBuilding_CmpSubstationCode = :STBR 
											and COALESCE( LB.LpuBuilding_endDate, :prmDT) >= :prmDT 

											and COALESCE( L.Lpu_endDate, :prmDT)>= :prmDT
											limit 2",

								array('SMPB' => $data[$val['code']], 'STBR' => $data[$val['code2']], 'prmDT' => $prmDT)
							);

							if ( is_array($lpuIdList) && count($lpuIdList) == 1 ) {
								$id = $lpuIdList[0]['Lpu_id'];
								$this->_lpuBuildingList[$data[$val['code']] . "_" . $data[$val['code2']]] = array('Lpu_id' => $lpuIdList[0]['Lpu_id'], 'LpuBuilding_id' => null);
							}
							elseif ( !empty($data[$val['code1']]) && !empty($data[$val['code3']]) ) {

								$lpuIdList = $this->queryResult("select distinct LB.Lpu_id   as \"Lpu_id\"
											from v_LpuBuilding LB  

  												join v_Lpu L  ON L.Lpu_id = LB.Lpu_id 

											where LB.LpuBuilding_CmpStationCode = :SMPT 
												and LB.LpuBuilding_CmpSubstationCode = :STAN 
												and COALESCE( LB.LpuBuilding_endDate, :prmDT) >= :prmDT 

												and COALESCE( L.Lpu_endDate, :prmDT) >= :prmDT
										    limit 2",

									array('SMPT' => $data[$val['code1']], 'STAN' => $data[$val['code3']], 'prmDT' => $prmDT)
								);

								if ( is_array($lpuIdList) && count($lpuIdList) == 1 ) {
									$id = $lpuIdList[0]['Lpu_id'];
									$this->_lpuBuildingList[$data[$val['code1']] . "_" . $data[$val['code3']]] = array('Lpu_id' => $lpuIdList[0]['Lpu_id'], 'LpuBuilding_id' => null);
								}
								else {
									$this->textlog->add('['.$ngod.'] saveAmbulanceCardPrepareId ошибка запроса при определении Lpu_id по SMPB (' . $data[$val['code']] . ')) и STBR (' . $data[$val['code2']] . '), 
									а так же при определении  по SMPT (' . $data[$val['code1']] . ')) и STAN (' . $data[$val['code3']] . ')');
									$err['Error_Code'] = 100501;
									$err['Error_Msg'] = 'ошибка запроса при определении Lpu_id по SMPB и STBR, а так же по SMPT и STAN ';
								}
							}
							else {
								$this->textlog->add('['.$ngod.'] saveAmbulanceCardPrepareId ошибка запроса при определении Lpu_id по SMPB (' . $data[$val['code']] . ')) и STBR (' . $data[$val['code2']] . '),
								 SMPT и STAN не уазаны');
								$err['Error_Code'] = 100502;
								$err['Error_Msg'] = 'ошибка запроса при определении Lpu_id по SMPB и STBR, SMPT и STAN не уазаны';
							}
						}
						else {
							$this->textlog->add('['.$ngod.'] saveAmbulanceCardPrepareId не указаны SMPB и SMPT');
							$err['Error_Code'] = 100503;
							$err['Error_Msg'] = 'Не указаны SMPB и STBR';
						}

						$response['data'][$key] = $id;
					break;
				}
			}
			elseif ($key == 'LpuBuilding_id') {
				switch ( $this->getRegionNick() ) {
					case 'ekb':
					case 'kareliya':
						// Определяем LpuBuilding_id по кодам подстанций
						// @task https://redmine.swan-it.ru/issues/103168
						// @task https://redmine.swan-it.ru/issues/136833
						if ( !empty($data[$val['code1']]) && !empty($data[$val['code3']]) ) {
							if ( !empty($this->_lpuBuildingList[$data[$val['code1']] . "_" . $data[$val['code3']]]['LpuBuilding_id']) ) {
								$id = $this->_lpuBuildingList[$data[$val['code1']] . "_" . $data[$val['code3']]]['LpuBuilding_id'];
							}
							else {
								// Ищем по SMPT и STAN
								$lpuBuildingIdList = $this->queryResult(
									"select distinct Lpu_id as \"Lpu_id\", LpuBuilding_id  as \"LpuBuilding_id\" from v_LpuBuilding  where LpuBuilding_CmpStationCode = :SMPT and LpuBuilding_CmpSubstationCode = :STAN limit 2",

									array('SMPT' => $data[$val['code1']], 'STAN' => $data[$val['code3']])
								);

								if ( !is_array($lpuBuildingIdList) ) {
									$this->textlog->add('['.$ngod.'] saveAmbulanceCardPrepareId ошибка запроса при определении LpuBuilding_id по SMPT (' . $data[$val['code1']] . ')) и STAN (' . $data[$val['code3']] . ')');
								}
								else if ( count($lpuBuildingIdList) == 0 ) {
									$this->textlog->add('['.$ngod.'] saveAmbulanceCardPrepareId не найден LpuBuilding_id по SMPT (' . $data[$val['code1']] . ') и STAN (' . $data[$val['code3']] . ')');

									if ( array_key_exists($data[$val['code1']] . "_" . $data[$val['code3']], $this->_lpuBuildingList) ) {
										$this->_lpuBuildingList[$data[$val['code1']] . "_" . $data[$val['code3']]]['LpuBuilding_id'] = null;
									}
									else {
										$this->_lpuBuildingList[$data[$val['code1']] . "_" . $data[$val['code3']]] = array('Lpu_id' => null, 'LpuBuilding_id' => null);
									}
								}
								else if ( count($lpuBuildingIdList) == 1 ) {
									$id = $lpuBuildingIdList[0]['LpuBuilding_id'];
									$this->_lpuBuildingList[$data[$val['code1']] . "_" . $data[$val['code3']]] = array('Lpu_id' => $lpuBuildingIdList[0]['Lpu_id'], 'LpuBuilding_id' => $lpuBuildingIdList[0]['LpuBuilding_id']);
								}
								else {
									$this->textlog->add('['.$ngod.'] saveAmbulanceCardPrepareId найдено несколько LpuBuilding_id по SMPT (' . $data[$val['code1']] . ') и STAN (' . $data[$val['code3']] . ')');

									if ( array_key_exists($data[$val['code1']] . "_" . $data[$val['code3']], $this->_lpuBuildingList) ) {
										$this->_lpuBuildingList[$data[$val['code1']] . "_" . $data[$val['code3']]]['LpuBuilding_id'] = null;
									}
									else {
										$this->_lpuBuildingList[$data[$val['code1']] . "_" . $data[$val['code3']]] = array('Lpu_id' => null, 'LpuBuilding_id' => null);
									}
								}
							}

							if ( empty($response['data']['Lpu_id']) && !empty($this->_lpuBuildingList[$data[$val['code1']] . "_" . $data[$val['code3']]]['Lpu_id']) ) {
								$response['data']['Lpu_id'] = $this->_lpuBuildingList[$data[$val['code1']] . "_" . $data[$val['code3']]]['Lpu_id'];
							}
						}

						$response['data'][$key] = $id;
					break;

					case 'perm':
						// Определяем LpuBuilding_id по кодам подстанций
						// @task https://redmine.swan.perm.ru/issues/103168

						//'LpuBuilding_id' => array('code' => 'SMPB', 'code1' => 'SMPT', 'code2' => 'STBR', 'code3' => 'STAN', 'tbl' => 'LpuBuilding'),
						if ( !empty($data[$val['code']]) && !empty($data[$val['code2']]) && !empty($this->_lpuBuildingList[$data[$val['code']] . "_" . $data[$val['code2']]]) ) {
							$id = $this->_lpuBuildingList[$data[$val['code']] . "_" . $data[$val['code2']]]['LpuBuilding_id'];
						}
						else if ( !empty($data[$val['code1']]) && !empty($data[$val['code3']]) && !empty($this->_lpuBuildingList[$data[$val['code1']] . "_" . $data[$val['code3']]]) ) {
							$id = $this->_lpuBuildingList[$data[$val['code1']] . "_" . $data[$val['code3']]]['LpuBuilding_id'];
						}

						if ( !empty($data[$val['code']]) && !empty($data[$val['code2']]) && empty($id) ) {
							$lpuBuildingIdList = $this->queryResult(
								"select distinct Lpu_id as \"Lpu_id\", LpuBuilding_id  as \"LpuBuilding_id\" from v_LpuBuilding  where LpuBuilding_CmpStationCode = :SMPB and LpuBuilding_CmpSubstationCode = :STBR limit 2",

								array('SMPB' => $data[$val['code']], 'STBR' => $data[$val['code2']])
							);

							if ( !is_array($lpuBuildingIdList) ) {
								$this->textlog->add('['.$ngod.'] saveAmbulanceCardPrepareId ошибка запроса при определении LpuBuilding_id по SMPB (' . $data[$val['code']] . ') и STBR (' . $data[$val['code2']] . ')');
								$err['Error_Code'] = 100500;
								$err['Error_Msg'] = 'Ошибка запроса при определении LpuBuilding_id по SMPB (' . $data[$val['code']] . ') и STBR (' . $data[$val['code2']] . ')';
								continue 2;
							}
							else if ( count($lpuBuildingIdList) == 0 ) {
								$this->textlog->add('['.$ngod.'] saveAmbulanceCardPrepareId не найден LpuBuilding_id по SMPB (' . $data[$val['code']] . ') и STBR (' . $data[$val['code2']] . ')');
								$err['Error_Code'] = 100500;
								$err['Error_Msg'] = 'Не найден LpuBuilding_id по SMPB (' . $data[$val['code']] . ') и STBR (' . $data[$val['code2']] . ')';
								continue 2;
							}
							else if ( count($lpuBuildingIdList) == 2 ) {
								$this->textlog->add('['.$ngod.'] saveAmbulanceCardPrepareId найдено несколько LpuBuilding_id по SMPB (' . $data[$val['code']] . ') и STBR (' . $data[$val['code2']] . ')');
								$err['Error_Code'] = 100500;
								$err['Error_Msg'] = 'Найдено несколько LpuBuilding_id по SMPB (' . $data[$val['code']] . ') и STBR (' . $data[$val['code2']] . ')';
								continue 2;
							}

							$id = $lpuBuildingIdList[0]['LpuBuilding_id'];
							$this->_lpuBuildingList[$data[$val['code']] . "_" . $data[$val['code2']]] = $lpuBuildingIdList[0];
						}

						if ( !empty($data[$val['code1']]) && !empty($data[$val['code3']]) && empty($id) ) {
							$lpuBuildingIdList = $this->queryResult(
								"select distinct Lpu_id as \"Lpu_id\", LpuBuilding_id  as \"LpuBuilding_id\" from v_LpuBuilding  where LpuBuilding_CmpStationCode = :SMPT and LpuBuilding_CmpSubstationCode = :STAN limit 2",

								array('SMPT' => $data[$val['code1']], 'STAN' => $data[$val['code3']])
							);

							if ( !is_array($lpuBuildingIdList) ) {
								$this->textlog->add('['.$ngod.'] saveAmbulanceCardPrepareId ошибка запроса при определении LpuBuilding_id по SMPT и STAN');
								$err['Error_Code'] = 100500;
								$err['Error_Msg'] = 'Ошибка запроса при определении LpuBuilding_id по SMPT и STAN';
								continue 2;
							}
							else if ( count($lpuBuildingIdList) == 0 ) {
								$this->textlog->add('['.$ngod.'] saveAmbulanceCardPrepareId не найден LpuBuilding_id по SMPT и STAN');
								$err['Error_Code'] = 100500;
								$err['Error_Msg'] = 'Не найден LpuBuilding_id по SMPT и STAN';
								continue 2;
							}
							else if ( count($lpuBuildingIdList) == 2 ) {
								$this->textlog->add('['.$ngod.'] saveAmbulanceCardPrepareId найдено несколько LpuBuilding_id по SMPT и STAN');
								$err['Error_Code'] = 100500;
								$err['Error_Msg'] = 'Найдено несколько LpuBuilding_id по SMPT и STAN';
								continue 2;
							}

							$id = $lpuBuildingIdList[0]['LpuBuilding_id'];
							$this->_lpuBuildingList[$data[$val['code1']] . "_" . $data[$val['code3']]] = $lpuBuildingIdList[0];
						}

						if ( empty($id) ) {
							$this->textlog->add('['.$ngod.'] saveAmbulanceCardPrepareId не определен идентификатор подразделения');
							$err['Error_Code'] = 100500;
							$err['Error_Msg'] = 'Не определен идентификатор подразделения';
							continue 2;
						}

						$response['data'][$key] = $id;
					break;
				}
			}
			elseif ($key == 'MedPersonal_id') {
				//$this->textlog->add('saveAmbulanceCardPrepareId');
				// определяем есть Tabn или DOKT (refs #16405)
				$val['code'] = '';
				/*
				if (empty($data['Tabn']) || empty($data['DOKT']) || !is_numeric($data['Tabn'])) {
					continue; // Если хотя бы одно из полей пусто или  поле TABN не числовой, то не идентифицируем
				}
				//$this->textlog->add('Tabn: '.$data['Tabn']);
				//$this->textlog->add('DOKT: '.$data['DOKT']);
				$where = "(1=1)";
				// по Tabn фильтр на табельный номер
				$query_params['tabcode'] = trim($data['Tabn']);
				$where .= " and MedPersonal_TabCode = :tabcode";

				// разбиваем DOKT на параметры
				$pd = explode(' ',$data['DOKT']);
				$i = 0;
				if (isset($pd[0]) && !empty($pd[0]) && is_numeric(trim($pd[0]))) { // если первым идет число, то его отсекаем
					$i = 1;
				}
				//$this->textlog->add('pd: '.var_export($pd,true));
				if (isset($pd[$i]) && !empty($pd[$i])) {
					$query_params['surname'] = trim($pd[$i]);
					$where .= " and Person_Surname = :surname";
				} else {
					continue; // если нет фамилии то не идентифицируем.
				}
				if (isset($pd[$i+1]) && !empty($pd[$i+1])) {
					$query_params['firname'] = trim($pd[$i+1]);
					$where .= " and Person_Firname iLIKE :firname+'%'";

				}
				if (isset($pd[$i+2]) && !empty($pd[$i+2])) {
					$query_params['secname'] = trim($pd[$i+2]);
					$where .= " and Person_Secname iLIKE :secname+'%'";

				}
				if (isset($response['data']['Lpu_id'])) {
					$query_params['Lpu_id'] = $response['data']['Lpu_id'];
					$where .= " and Lpu_id = :Lpu_id";
				}

				$sql = "select top 1 ".$val['tbl']."_id as id from v_".$val['tbl']."  where ".$where;

				*/

				if (isset($response['data']['Lpu_id'])) {
					$query_params['Lpu_id'] = $response['data']['Lpu_id'];
				} else {
					$query_params['Lpu_id'] = NULL;
				}

				$query_params['DOKT'] = !empty($data['DOKT'])?$data['DOKT']:null;
				$query_params['Tabn'] = !empty($data['TABN'])?$data['TABN']:null;


				$this->textlog->add('s$query_params',json_encode($query_params));
				$sql = "select dbo.getCMPMedPersonal(:Tabn, :DOKT, :Lpu_id) as \"id\"";
				// echo getDebugSql($sql, $query_params); die();
				//sql_log_message('error','SMP MedPersonal Ident: ',getDebugSql($sql, $query_params));

			}
			elseif ($key == 'ResultDeseaseType_id') {
				$sql = "select ResultDeseaseType_id as \"id\" from fed.v_".$val['tbl']."  where ".$val['tbl']."_Code = :code limit 1";


				if( in_array($this->regionNick, array('perm')) ){
					//$rdtcode = null;
					$rdtcode = 402; // Без эффекта
				}
				else if( in_array($this->regionNick, array('kareliya')) ){
					$rdtcode = $data[$val['code']];
				}
				else if ( $this->getRegionNick() == 'ekb' ) {
					// @task https://redmine.swan.perm.ru/issues/97561
					if ( !empty($response['data']['LeaveType_id']) ) {
						if ( array_key_exists($response['data']['LeaveType_id'], $this->_resultDeseaseLeaveTypeList) ) {
							$response['data'][$key] = $this->_resultDeseaseLeaveTypeList[$response['data']['LeaveType_id']];
							continue;
						}

						$sqlTmp = "
							select ResultDeseaseType_id as \"ResultDeseaseType_id\"
							from r66.ResultDeseaseLeaveType 

							where LeaveType_id = :LeaveType_id
							limit 1
						";
						$query_params = array('LeaveType_id' => $response['data']['LeaveType_id']);
						$this->textlog->add('['.$ngod.'] saveAmbulanceCardPrepareId определяем идентификатор ' . $key . ': '.getDebugSQL($sqlTmp, $query_params));
						$ResultDeseaseType_id = $this->getFirstResultFromQuery($sqlTmp, $query_params);

						if ( $ResultDeseaseType_id !== false && !empty($ResultDeseaseType_id) ) {
							$this->_resultDeseaseLeaveTypeList[$response['data']['LeaveType_id']] = $ResultDeseaseType_id;
							$response['data'][$key] = $ResultDeseaseType_id;
							continue;
						}
					}

					$rdtcode = 402; // без эффекта
				}
				else {
					// Доработал определение исхода в карте СМП
					// @task https://redmine.swan.perm.ru/issues/86395
					$cnt8001 = 0;
					$cnt8002 = 0;

					if (mb_substr($data[$val['code']],0,4) == '8001') {
						$cnt8001 = 1;
					} elseif (mb_substr($data[$val['code']],0,4) == '8002') {
						$cnt8002 = 1;
					}
					else {

						$arr = unserialize($data[$val['code']]);

						if ( is_array($arr) && count($arr) > 0 ) {
							foreach ( $arr as $rec ) {
								if ( is_array($rec) && !empty($rec['code']) ) {
									switch ( $rec['code'] ) {
										case '8001': $cnt8001++; break;
										case '8002': $cnt8002++; break;
									}
								}
							}
						}
					}

					if ( $cnt8001 + $cnt8002 == 1 ) {
						if ( $cnt8001 == 1 ) {
							$rdtcode = 401; // улучшение
						}
						else if ( $cnt8002 == 1 ) {
							$rdtcode = 403; // ухудшение
						}
					}
					else {
						$rdtcode = 402; // без эффекта
					}
				}

				// Если значение уже известно, то новый запрос не делаем
				if ( array_key_exists($rdtcode, $this->_resultDeseaseTypeList) ) {
					$response['data'][$key] = $this->_resultDeseaseTypeList[$rdtcode];
					continue;
				}

				$query_params = array('code' => $rdtcode);
			}
			elseif ($key == 'Diag_uid') {
				$sql = "
					select Diag_id as \"id\", 1 as \"sortID\" from v_Diag  where Diag_Code = :code

				";
				if ( !empty($data['DIAGC']) && $data['DIAGC'] != $data[$val['code']] ) {
					$sql = "
						union all
						(
						select Diag_id as \"id\", 2 as \"sortID\" from v_Diag  where Diag_Code = :addCode 

						order by \"sortID\"
						limit 1
						)
					";
				}
				$query_params = array('code' => $data[$val['code']], 'addCode' => (!empty($data['DIAGC']) ? $data['DIAGC'] : NULL));
			}
			elseif ($key == 'Sex_id') {
				$sql = "
				SELECT 
                    CASE WHEN isnumeric(:code) = 1 
                    THEN (select Sex_id as id from v_Sex  where Sex_Code = 2 - to_number(:code) limit 1) 
                    ELSE (select Sex_id as id from v_Sex  where SUBSTRING(Sex_Name, 1, 1) = :code limit 1)
                    END AS \"id\"
				;";

				$query_params = array('code' => $data[$val['code']]);
			}
			elseif ($key == 'CmpCallerType_id') {
				if ( !empty($data[$val['code']]) ) {
					$sql = "select CmpCallerType_id as \"id\" from v_CmpCallerType  where CmpCallerType_Name = :code limit 1";

					$query_params = array('code' => $data[$val['code']]);
				}
			}
			elseif ($key == 'UslugaComplex_id') {

				$prmDT = null;
				if ( array_key_exists('DPRM', $data) ) {
					if ( is_object($data['DPRM']) && get_class($data['DPRM']) == 'DateTime' ) {
						$prmDT = $data['DPRM']->format('Y-m-d H:i:s');
					}
					else if ( !empty($data['DPRM']) ) {
						$prmDT = trim($data['DPRM']);
					}
				}

				$resp = $this->getUslugaComplexCodeForCmpCallCard(array(
					'CmpProfile_Code' => $data[$val['code']],
					'CmpPlace_Code' => $data[$val['code1']],
					'CmpResult_Code' => $data[$val['code2']],
					'CmpCallCard_Inf6' => $data[$val['code3']],
					'prmDT' => $prmDT
				));
				if ($resp === false || empty($resp)) {
					$this->textlog->add('['.$ngod.'] saveAmbulanceCardPrepareId Ошибка при определении кода услуги');
					//return array('Error_Msg' => 'Ошибка при определении кода услуги');
				}
				else {

					$sql = "
						select uc.UslugaComplex_id as \"id\"
						from v_UslugaComplex uc 

							inner join v_UslugaCategory ucat  on ucat.UslugaCategory_id = uc.UslugaCategory_id

						where uc.UslugaComplex_Code iLIKE :code

							and COALESCE(uc.UslugaComplex_begDT, :prmDT) <= :prmDT

							and COALESCE(uc.UslugaComplex_endDT, :prmDT) >= :prmDT
                        limit 1
					";

					$query_params = array('code' => $resp, 'prmDT' => $prmDT);

					// @task https://redmine.swan.perm.ru/issues/68177
					if ( $this->getRegionNick() == 'perm' ) {
						$sql .= "and ucat.UslugaCategory_SysNick = 'gost2011'";
					}
					else if ( $this->getRegionNick() == 'ekb' ) {

						$sql .= "
							and exists (
								select ucpl.UslugaComplexPartitionLink_id
								from r66.UslugaComplexPartitionLink ucpl 

									left join r66.UslugaComplexPartition ucp  on ucp.UslugaComplexPartition_id = ucpl.UslugaComplexPartition_id 

								where ucpl.UslugaComplex_id = uc.UslugaComplex_id
									" . (!empty($prmDT) ? "and ucpl.UslugaComplexPartitionLink_begDT <= :prmDT
									and (ucpl.UslugaComplexPartitionLink_endDT > :prmDT or ucpl.UslugaComplexPartitionLink_endDT is null)" : "") . "
									and ucp.MedicalCareType_id = 4
									and ucp.UslugaComplexPartition_Code = '400'
							)
						";

						if ( ! empty($prmDT) ) {
							$query_params['prmDT'] = $prmDT;
						}
					}
				}
			}
			elseif ($data[$val['code']] != '') {
				$sql = "WITH cte AS (
                SELECT
                    (select ".$val['tbl']."_id as id from v_".$val['tbl']."  where isnumeric(".$val['tbl']."_Code)=1 and ".$val['tbl']."_Code = :code limit 1) AS id1,
                    (select ".$val['tbl']."_id as id from v_".$val['tbl']."  where ".$val['tbl']."_Code = :code limit 1) AS id2
                )
                SELECT 
                    CASE WHEN ('".$val['tbl']."' = 'CmpResult') and ISNUMERIC(:code)=1
                    THEN id1 
                    ELSE id2
                    END AS \"id\"
                FROM cte;";

				//$sql = "select top 1 ".$val['tbl']."_id as id from v_".$val['tbl']."  where ".$val['tbl']."_Code = :code";

				$query_params = array('code' => $data[$val['code']]);
			}

			// todo: удалить после уточнения проблемы
			$this->textlog->add('['.$ngod.'] saveAmbulanceCardPrepareId определяем идентификатор ' . $key . ': '.getDebugSQL($sql, $query_params));

			if (empty($sql)) {continue;}

			$result = $this->db->query($sql, $query_params);

			if (is_object($result)) {
				$result = $result->result('array');
				if (isset($result[0])) {
					$id = $result[0]['id'];
				}
				if (in_array($key, array('MedPersonal_id', 'Diag_uid')) && empty($id)) {
					continue; // Если записей не нашлось, то не идентифицируем
				}
			}

			if (!empty($id)) {
				$response['data'][$key] = $id;

				if ( $key == 'ResultDeseaseType_id' ) {
					$this->_resultDeseaseTypeList[$query_params['code']] = $id;
				}

				if(in_array($val['code'], array('REZL','REZLC'))){
					$query="Select LeaveType_id  as \"LeaveType_id\" from v_CmpResult  where CmpResult_id=:id limit 1";

					// todo: удалить после уточнения проблемы
					$this->textlog->add('['.$ngod.'] saveAmbulanceCardPrepareId определяем LeaveType_id: '.getDebugSQL($query, array('id'=>$id)));
					$res =  $this->db->query($query, array('id'=>$id));
					$result = $res->result('array');
					if (isset($result[0])) {
						$response['data']['LeaveType_id'] = $result[0]['LeaveType_id'];
						$this->textlog->add('['.$ngod.'] saveAmbulanceCardPrepareId LeaveType_id = '.$response['data']['LeaveType_id']);
					}
				}
			}
			elseif (in_array($val['code'], array('POL', 'REZL', 'PRFB', 'KTOV'))) {
				$response['data'][$key] = null;
			}
			else {
				if (isset($val['str']) && isset($data[$val['str']]) && $data[$val['str']] != '') {
					$dopFields = '';

					if ( in_array($key, array('CmpReason_id')) ) {
						$dopFields = 'CmpReason_isCmp := 2,';
					}

					$sql = "
						select Error_Code as \"Error_Code\", error_message as \"Error_Msg\", ".$val['tbl']."_id as \"id\"
						from p_".$val['tbl']."_ins(
							".$val['tbl']."_id := null,
							".$val['tbl']."_Code := :code,
							".$val['tbl']."_Name := :str,
							{$dopFields}
							pmUser_id := :pmUser_id);

					";
					$result = $this->db->query($sql, array('code' => $data[$val['code']], 'str' => $data[$val['str']], 'pmUser_id' => $data['pmUser_id']));
					if (is_object($result)) {
						$result = $result->result('array');
						if (isset($result[0])) {
							if (empty($result[0]['Error_Msg'])) {
								$id = $result[0]['id'];
							} else {
								/*$err['Error_Code'] = $result[0]['Error_Code'];
								$err['Error_Msg'] = $result[0]['Error_Msg'];*/
								sql_log_message('error','CmpCallCard save warning: (ошибка добавления записи в справочник):',getDebugSql($sql, $query_params));
								$this->textlog->add('['.$ngod.'] saveAmbulanceCardPrepareId (ошибка добавления записи в справочник):' . $result[0]['Error_Msg']);
							}
						}
					}
					$response['data'][$key] = $id;
				} else {
					sql_log_message('error','CmpCallCard save warning: (нет данных):',getDebugSql($sql, $query_params));
					/*$err['Error_Code'] = "2";
					$err['Error_Msg'] = $val['code'].', код не найден в базе данных';*/
				}
			}
		}
		/*
		else {
			print 'ignore '.$key.' '.$val['code'].'</br>';
		}*/
		$response['Error_Code'] = $err['Error_Code'];
		$response['Error_Msg'] = $err['Error_Msg'];

		$this->textlog->add('['.$ngod.'] выполнение saveAmbulanceCardPrepareId завершено');

		return $response;
	}

	/**
	 * Определение услуги для карты СМП (Екатерибург)
	 */
	function getUslugaComplexCodeForCmpCallCard($data) {
		$usluga_complex_code = '';

		switch ( $this->getRegionNick() ) {
			case 'ekb':
				$sql = "select crt.CmpResultType_Code as \"CmpResultType_Code\"
				from v_CmpResult cr 

				inner join v_CmpResultType crt  on crt.CmpResultType_id = cr.CmpResultType_id

				where cr.CmpResult_Code = :CmpResult_Code
				limit 1";
				//echo getDebugSQL($sql, array('CmpResult_Code' => $data['CmpResult_Code']));exit;
				$res = $this->db->query($sql, array('CmpResult_Code' => $data['CmpResult_Code']));
				if (!is_object($res)) {return false;}
				$resp = $res->result('array');
				$data['CmpResultType_Code'] = isset($resp[0]['CmpResultType_Code']) ? $resp[0]['CmpResultType_Code'] : null;
				// 1. Если профиль бригады «Ф. Фельдшерский» и Тип результата (CmpResult.CmpResultType_id) = «Транспортировка», то Услуга:
				// A23.30.042.002 Санитарная транспортировка СМП. Тип результата определяется по справочнику результатов АДИС
				if ($data['CmpProfile_Code'] == 'Ф' && $data['CmpResultType_Code'] == 2) {
					$usluga_complex_code = 'A23.30.042.002';
				}
				// 2. Иначе, если профиль бригады «Ф. Фельдшерский» и место оказания помощи «Подстанция», то Услуга:
				// B01.044.002.999 Осмотр фельдшером скорой медицинской помощи на станции/ подразделении СМП.
				elseif ($data['CmpProfile_Code'] == 'Ф' && $data['CmpPlace_Code'] == 5) {
					$usluga_complex_code = 'B01.044.002.999';
				}
				// 3. Иначе, если профиль бригады «Я. Диспетчер» и место оказания помощи «Подстанция», то Услуга:
				// B01.044.002.999 Осмотр фельдшером скорой медицинской помощи на станции/ подразделении СМП.
				elseif ($data['CmpProfile_Code'] == 'Я' && $data['CmpPlace_Code'] == 5) {
					$usluga_complex_code = 'B01.044.002.999';
				}
				// 4. Иначе, если профиль бригады «Ф. Фельдшерский» и место оказания помощи любое кроме «Подстанция», то
				elseif ($data['CmpProfile_Code'] == 'Ф' && $data['CmpPlace_Code'] != 5) {
					//	Если в поле INF6 указана какая-либо услуга, то осуществляется поиск услуг в
					// справочнике услуг по полному совпадению Кода услуги.
					if(!empty($data['CmpCallCard_Inf6'])){
						$usluga_complex_code = $this->checkIssetUsluga(array(
							'CmpCallCard_Inf6' => $data['CmpCallCard_Inf6'],
							'prmDT' =>  $data['prmDT']
						));
					}
					// Иначе, услуга B01.044.002
					else{
						$usluga_complex_code = 'B01.044.002';
					}

				}
				// 5. Иначе, если профиль бригады НЕ «Ф. Фельдшерский» и НЕ «Я. Диспетчер» и место оказания помощи «Подстанция», то
				// B01.044.001.999 Осмотр врачом скорой медицинской помощи на станции/ подразделении СМП.
				elseif ($data['CmpProfile_Code'] != 'Ф' && $data['CmpProfile_Code'] != 'Я' && $data['CmpPlace_Code'] == 5) {
					$usluga_complex_code = 'B01.044.001.999';
				}
				// 6. Иначе, если профиль бригады НЕ «Ф. Фельдшерский» и место оказания помощи любое кроме «Подстанция», то Услуга:
				// B01.044.001 Осмотр врачом скорой медицинской помощи.
				elseif ($data['CmpProfile_Code'] != 'Ф' && $data['CmpPlace_Code'] != 5) {
					$usluga_complex_code = 'B01.044.001';
				}
				// 7. Иначе, если в поле INF6 указана какая-либо услуга, то осуществляется поиск услуг в справочнике услуг
				// по полному совпадению Кода услуги.
				elseif(!empty($data['CmpCallCard_Inf6'])){
					$usluga_complex_code = $this->checkIssetUsluga(array(
						'CmpCallCard_Inf6' => $data['CmpCallCard_Inf6'],
						'prmDT' =>  $data['prmDT']
					));
				}
			break;

			case 'perm':
				if (!in_array($data['CmpResult_Code'], array(1, 2, 3, 4, 5, 6, 7, 8, 9, 17, 18, 90, 91, 92, 93, 94, 95, 96, 97, 98, 99))){

					// Если обращение было в подстанцию СМП (место обслуживания – «Подстанция») и Код результата – «21. Оставлен на месте» или «22. Отказ от госпитализации»
					if ( $data['CmpPlace_Code'] == 5 && in_array($data['CmpResult_Code'], array(21, 22)) ) {
						if ( $data['CmpProfile_Code'] == 'Ф' ) {
							$usluga_complex_code = 'B01.044.002.999';
						}
						else {
							$usluga_complex_code = 'B01.044.001.999';
						}
					}
					else {
						switch ( $data['CmpProfile_Code'] ) {
							case 'Ф':
								if ( $data['CmpPlace_Code'] != 5 ) {
									$usluga_complex_code = 'B01.044.002';
								}
							break;

							case 'Б':
							case 'Л':
								$usluga_complex_code = 'B01.044.001';
							break;

							case 'Е':
								$usluga_complex_code = 'B01.031.001';
							break;

							case 'Р':
							case 'Д':
								$usluga_complex_code = 'B01.003.001';
							break;

							case 'Н':
								$usluga_complex_code = 'B01.023.001';
							break;

							case 'К':
								$usluga_complex_code = 'B01.015.001';
							break;

							case 'Т':
								$usluga_complex_code = 'A23.30.042.002';
							break;
						}
					}
				}
			break;
		}

		return $usluga_complex_code ? $usluga_complex_code : false;
	}

	/**
	 *	Функция проверки кода услуги
	 */
	function checkIssetUsluga($data)
	{
		$sql = "select uc.UslugaComplex_Code as \"UslugaComplex_Code\"
				from v_UslugaComplex uc 

				where uc.UslugaComplex_Code iLIKE :code

					and COALESCE(uc.UslugaComplex_begDT, :prmDT) <= :prmDT

					and COALESCE(uc.UslugaComplex_endDT, :prmDT) >= :prmDT

					and (uc.Region_id = '66' or uc.Region_id is null)
					and exists (
						select ucpl.UslugaComplexPartitionLink_id
						from r66.UslugaComplexPartitionLink ucpl 

							left join r66.UslugaComplexPartition ucp  on ucp.UslugaComplexPartition_id = ucpl.UslugaComplexPartition_id 

						where ucpl.UslugaComplex_id = uc.UslugaComplex_id
							and ucpl.UslugaComplexPartitionLink_begDT <= :prmDT
							and (ucpl.UslugaComplexPartitionLink_endDT > :prmDT or ucpl.UslugaComplexPartitionLink_endDT is null)
							and ucp.MedicalCareType_id = 4
							and ucp.UslugaComplexPartition_Code = '400'
					)
				limit 1";

		$query_params = array('code' => $data['CmpCallCard_Inf6'], 'prmDT'=> $data['prmDT']);
		$result = $this->db->query($sql, $query_params);

		if ( !is_object( $result ) ) {
			return false;
		}
		$result = $result->result( 'array' );

		if (isset($result[0]) ) {
			return $data['CmpCallCard_Inf6'];
		} else{
			return 'B01.044.002';
		}
	}

	/**
	* Подготовкка Person_id для сохранения данных карты.
	*/
	function saveAmbulanceCardPreparePersonId($data) {
		$queryParams = array();
		if (isset($data['personID']) && $data['personID']>0) { // Если из сервиса АДИС передан personID, то используем его без всяких проверок
			$sql = "select Person_id  as \"Person_id\" from v_PersonState  where Person_id = :personID";

			$queryParams['personID'] = $data['personID'];
			$result = $this->db->query($sql, $queryParams);
			if (is_object($result)) {
				if(isset($r[0]) && isset($r[0]['Person_id'])) { // Если по указанному personID нашли человека в ПромедВеб, то всячески радуемся и передаем идентификатор
					return $r[0]['Person_id'];
				}
			}
		} // Если по переданному personID ничего не нашли или personID не содержал данных, то определяем человека по старинке
		//$sql = "select dbo.GetPersonIdByFIOAge(:Person_SurName, :Person_FirName, :Person_SecName, :Person_Age) as Person_id";
		//$sql = "select dbo.getPersonIdByFIOPolis(:Person_SurName, :Person_FirName, :Person_SecName, :Person_BirthDay, :Person_PolisSer, :Person_PolisNum) as Person_id";
		$sql = "select dbo.GetPersonIdByFIOAgePolis(:Person_SurName, :Person_FirName, :Person_SecName, :Sex_id, :Person_Age, :Person_BirthDay, :Person_PolisSer, :Person_PolisNum, :setDT) as \"Person_id\"";

		if (isset($data['KOD1']) && (!empty($data['KOD1']))) {
			$data['DATR'] = $data['KOD1'];
		}
		$queryParams['Person_SurName'] = isset($data['FAM']) ? $data['FAM'] : null;
		$queryParams['Person_FirName'] = isset($data['IMYA']) ? $data['IMYA'] : null;
		$queryParams['Person_SecName'] = isset($data['OTCH']) ? $data['OTCH'] : null;
		$queryParams['Sex_id'] = !empty($data['Sex_id']) ? $data['Sex_id'] : null;
		$queryParams['Person_Age'] = !empty($data['VOZR']) ? intval($data['VOZR']) : null;
		$queryParams['Person_BirthDay'] = isset($data['DATR']) ? $data['DATR'] : null;
		$queryParams['Person_PolisSer'] = isset($data['PPSS']) ? $data['PPSS'] : null;
		$queryParams['Person_PolisNum'] = isset($data['PPNS']) ? $data['PPNS'] : null;
		$queryParams['setDT'] = !empty($data['DPRM']) ? $data['DPRM'] : null;

		try {
			$result = $this->db->query($sql, $queryParams);
		} catch (Exception $e) {
			sql_log_message('error','Error exec query: ',getDebugSql($sql, $queryParams));
			return array(array('success' => false, 'Error_Code' => $e->getCode(), 'Error_Msg' => $e->getMessage()));
		}
		if (is_object($result)) {
			$response = $result->result('array');

			if ( !empty($response[0]['Person_id']) ) {
				return $response[0]['Person_id'];
			}
		}

		//здесь дно... пациент не найден нигде, тогда создадим (что нам стоит)
		$this->load->model( 'Person_model', 'Person_model' );

		$Person_BirthDay = null;
		if (!empty($queryParams['Person_BirthDay'])) {
			$Person_BirthDay = $queryParams['Person_BirthDay'];
		} else {
			$Person_BirthDay = '01.01.' . (date("Y") - $queryParams['Person_Age']);
		}
		$result = $this->Person_model->savePersonEditWindow(array(
			'Server_id' => $data['Server_id'],
			'NationalityStatus_IsTwoNation' => false,
			'Polis_CanAdded' => 0,
			'Person_SurName' => $queryParams['Person_SurName'],
			'Person_FirName' => $queryParams['Person_FirName'],
			'Person_SecName' => $queryParams['Person_SecName'],
			'Person_BirthDay'=> $Person_BirthDay,
			'Person_IsUnknown' => 2,
			'PersonSex_id' => $queryParams['Sex_id'],
			'SocStatus_id' => null,
			'session' => $data['session'],
			'mode' => 'add',
			'pmUser_id' =>  $data['pmUser_id'],
			'Person_id' => null,
			'Polis_begDate' => null
		));

		if (!empty($result[0]['Person_id'])) {
			return $result[0]['Person_id'];
		}

		return null;
	}

	/**
	* Получение данных карты
	*/
	function getAmbulanceCard($data)
	{	
		$sql = "
			select 
				CmpCallCard_id as \"AmbulanceCard_id\",
				rtrim(CAST(CmpArea_id as varchar)) as \"RJON\",
				rtrim(CmpCallCard_City) as \"CITY\",
				rtrim(CmpCallCard_Ulic) as \"ULIC\",
				rtrim(CmpCallCard_Dom) as \"DOM\",
				rtrim(CAST(CmpCallCard_Podz as varchar)) as \"PODZ\",
				rtrim(CmpCallCard_Kvar) as \"KVAR\",
				rtrim(CmpCallCard_Kodp) as \"KODP\",
				rtrim(CmpCallCard_Telf) as \"TELF\",
				rtrim(CAST(CmpCallCard_Etaj as varchar)) as \"ETAJ\",
				rtrim(CAST(CmpPlace_id as varchar)) as \"MEST\",
				rtrim(CmpCallCard_Comm) as \"COMM\",
				rtrim(CAST(CmpReason_id as varchar)) as \"POVD\",
				rtrim(Person_SurName) as \"FAM\",
				rtrim(Person_SecName) as \"OTCH\",
				rtrim(Person_FirName) as \"IMYA\",
				rtrim(CmpCallCard_Ktov) as \"KTOV\",
				rtrim(CAST(Person_Age as varchar)) \"as \"VOZR,
				rtrim(CAST(Sex_id as varchar)) as \"POL\",
				rtrim(CAST(CmpCallCard_Numv as varchar)) as \"NUMV\",
				rtrim(CAST(CmpCallCard_Ngod as varchar)) as \"NGOD\",
				rtrim(CAST(CmpCallType_id as varchar)) as \"POVT\",
				rtrim(CAST(CmpCallCard_Prty as varchar)) as \"PRTY\",
				rtrim(CAST(CmpProfile_cid as varchar)) as \"PROF\",
				rtrim(CAST(CmpCallCard_Sect as varchar)) as \"SECT\",
				rtrim(CAST(CmpCallCard_Smpt as varchar)) as \"SMPT\",
				rtrim(CAST(CmpCallCard_Stan as varchar)) as \"STAN\",
				to_char(cast(CmpCallCard_prmDT as timestamp), 'DD.MM.YYYY') as \"DPRM\",

				to_char(cast(CmpCallCard_prmDT as timestamp), 'HH24:MI') as \"TPRM\",

				rtrim(CAST(CmpCallCard_Tper as varchar)) as \"TPER\",
				case when date_part('dow', CmpCallCard_prmDT) = 1 then 7 else date_part('dow', CmpCallCard_prmDT) - 1 end as \"WDAY\",
				rtrim(CAST(CmpResult_id as varchar)) as \"REZL\",
				rtrim(CAST(CmpTrauma_id as varchar)) as \"TRAV\",
				rtrim(CAST(CmpArea_gid as varchar)) as \"RGSP\",
				rtrim(CAST(Lpu_ppdid as varchar)) as \"KUDA\",
				rtrim(CAST(CmpDiag_oid as varchar)) as \"DS1\",
				rtrim(CAST(CmpDiag_aid as varchar)) as \"DS2\",
				rtrim(CAST(CmpCallCard_IsAlco as varchar)) as \"ALK\",
				rtrim(CAST(Diag_uid as varchar)) as \"MKB\",
				rtrim(CAST(CmpCallCard_Numb as varchar)) as \"NUMB\",
				rtrim(CAST(CmpCallCard_Smpb as varchar)) as \"SMPB\",
				rtrim(CAST(CmpCallCard_Stbr as varchar)) as \"STBR\",
				rtrim(CAST(CmpCallCard_Stbb as varchar)) as \"STBB\",
				rtrim(CAST(CmpProfile_bid as varchar)) as \"PRFB\",
				rtrim(CAST(CmpCallCard_Ncar as varchar)) as \"NCAR\",
				rtrim(CAST(CmpCallCard_RCod as varchar)) as \"RCOD\",
				rtrim(CAST(CmpCallCard_TabN as varchar)) as \"TABN\",
				rtrim(CAST(CmpCallCard_Tab2 as varchar)) as \"TAB2\",
				rtrim(CAST(CmpCallCard_Tab3 as varchar)) as \"TAB3\",
				rtrim(CAST(CmpCallCard_Tab4 as varchar)) as \"TAB4\",
				rtrim(CAST(CmpCallCard_Smpp as varchar)) as \"SMPP\",
				rtrim(CAST(CmpCallCard_Vr51 as varchar)) as \"VR51\",
				rtrim(CAST(CmpCallCard_D201 as varchar)) as \"D201\",
				rtrim(CAST(CmpCallCard_Dsp1 as varchar)) as \"DSP1\",
				rtrim(CAST(CmpCallCard_Dsp2 as varchar)) as \"DSP2\",
				rtrim(CAST(CmpCallCard_Dspp as varchar)) as \"DSPP\",
				rtrim(CAST(CmpCallCard_Dsp3 as varchar)) as \"DSP3\",
				rtrim(CAST(CmpCallCard_Kakp as varchar)) as \"KAKP\",
				rtrim(CAST(CmpCallCard_Vyez as varchar)) as \"VYEZ\",
				rtrim(CAST(CmpCallCard_Przd as varchar)) as \"PRZD\",
				rtrim(CAST(CmpCallCard_Tgsp as varchar)) as \"TGSP\",
				rtrim(CAST(CmpCallCard_Tsta as varchar)) as \"TSTA\",
				rtrim(CAST(CmpCallCard_Tisp as varchar)) as \"TISP\",
				rtrim(CAST(CmpCallCard_Tvzv as varchar)) as \"TVZV\",
				rtrim(CAST(CmpCallCard_Kilo as varchar)) as \"KILO\",
				rtrim(CAST(CmpCallCard_Dlit as varchar)) as \"DLIT\",
				rtrim(CAST(CmpCallCard_Prdl as varchar)) as \"PRDL\",
				rtrim(CAST(CmpCallCard_IsPoli as varchar)) as \"POLI\",
				rtrim(CAST(CmpCallCard_Izv1 as varchar)) as \"IZV1\",
				rtrim(CAST(CmpCallCard_Tiz1 as varchar)) as \"TIZ1\",
				rtrim(CAST(CmpCallCard_Inf1 as varchar)) as \"INF1\",
				rtrim(CAST(CmpCallCard_Inf2 as varchar)) as \"INF2\",
				rtrim(CAST(CmpCallCard_Inf3 as varchar)) as \"INF3\",
				rtrim(CAST(CmpCallCard_Inf4 as varchar)) as \"INF4\",
				rtrim(CAST(CmpCallCard_Inf5 as varchar)) as \"INF5\",
				rtrim(CAST(CmpCallCard_Inf6 as varchar)) as \"INF6\",
				rtrim(CAST(Diag_sid as varchar)) as \"DSHS\",
				rtrim(CAST(CmpTalon_id as varchar)) as \"FERR\",
				rtrim(CAST(CmpCallCard_Expo as varchar)) as \"EXPO\"
			from
				CmpCallCard 

			where
				CmpCallCard_id = ?
		";
		
		$result = $this->db->query($sql, array($data['AmbulanceCard_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Неведомая функция
	 */
	function deleteAmbulanceDrug($data)
	{
		$sql = "SELECT p_CmpCallDrug_del( CmpCallDrug_id := ?)";
        $result = $this->db->query($sql, array($data['CmpCallDrug_id']));
	}
	
	/**
	* Получение данных карты
	*/
	function getAmbulanceMedicamentList($data)
	{			
		$sql = "
			select 
				ccd.CmpCallDrug_id as \"CmpCallDrug_id\",
				cd.CmpDrug_id as \"CmpDrug_id\",
				ccd.CmpCallCard_id as \"AmbulanceCard_id\",
				cd.CmpDrug_Name as \"CmpDrug_Name\",
				cd.CmpDrug_Code as \"CmpDrug_Code\",	
				cd.CmpDrug_Ei as \"CmpDrug_Ei\",
				cd.CmpDrug_Kolvo as \"CmpDrug_Kolvo\"
			from
				CmpCallDrug ccd 

				inner join CmpDrug cd  on cd.CmpDrug_id = ccd.CmpDrug_id

			where
				ccd.CmpCallCard_id = ?
			order by cd.CmpDrug_Name
		";
		
		$result = $this->db->query($sql, array($data['AmbulanceCard_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	* получение КЛАДРовских индентификаторов по адресу
	*/
	function getKladrAddress ($data)
	{
		$queryParams = array();	
		
		$sql = "select 
		            KLTown_id as \"KLTown_id\",
                    KLCity_id as \"KLCity_id\",
                    KLSubRgn_id as \"KLSubRgn_id\",
                    KLRgn_id as \"KLRgn_id\",
                    KLStreet_id as \"KLStreet_id\"
		        from dbo.CmpCallCard_KLADR (:CmpCallCard_City, :CmpCallCard_Ulic)";
		
		$queryParams['CmpCallCard_City'] = isset($data['CITY']) ? $data['CITY'] : null;
		$queryParams['CmpCallCard_Ulic'] = isset($data['ULIC']) ? $data['ULIC'] : null;
		
		$result = $this->db->query($sql, $queryParams);
		
		if (is_object($result))
		{
			$data = $result->result('array');
			if(isset($data[0])) 
			{
				return $data[0];
			}
		}
		
		return null;

	}
	
	/**
	*	получение возможных ЛПУ передачи
	*/
	function getAttachLpuAddress($data) {
		$queryParams = array();
		
		if( isset($data['Person_id']) && !empty($data['Person_id']) ) {
			$q = "
				select 
					datediff('YEAR', PS.Person_BirthDay, dbo.tzGetDate()) as \"Person_Age\"
					,PS.Lpu_id as \"LpuAttach_id\"
				from
					v_PersonState PS 

				where
					PS.Person_id = :Person_id
				limit 1
			";
			$r = $this->db->query($q, array('Person_id' => $data['Person_id']));
			if(!is_object($r))
				return false;
			
			$r = $r->result('array');
			if( count($r) > 0 ) {
				foreach($r[0] as $key=>$row) {
					$data[$key] = $row;
				}
			}
		}
		
		if( !isset($data['CITY']) || !isset($data['ULIC']) ) {
			$data['CITY'] = $data['CmpCallCard_City'];
			$data['ULIC'] = $data['CmpCallCard_Ulic'];
			$kl = $this->getKladrAddress($data);
			if(!empty($kl)) {
				foreach($kl as $k=>$r) {
					$data[$k] = $r;
				}
			}
			$data['Address_House'] = (isset($data['CmpCallCard_Dom']))?$data['CmpCallCard_Dom']:'';
		}
		
		$filter = "1=1";
		$filter .= " and MS.MedServiceType_id = 18";
		$iskind = null;
		
		if (isset($data['Person_Age']) && ( !empty($data['Person_Age']) || $data['Person_Age'] === 0 )) {
			if( $data['Person_Age'] < 1 ) {
				return null;
			} 
			$iskind = $data['Person_Age'] >= 18 ? 1 : 2;
		}
		
		$query = "
			select distinct
				LA.Lpu_id as \"Lpu_id\",
				LA.LpuRegionType_id as \"LpuRegionType_id\"
			from
				dbo.GetAttachLpuAddress(:KLRgn_id, :KLSubRgn_id, :KLCity_id, :KLTown_id, :KLStreet_id, :Address_House) LA
				left join v_MedService MS  on MS.Lpu_id = LA.Lpu_id

		";
		
		$queryParams['KLRgn_id'] = isset($data['KLRgn_id']) ? $data['KLRgn_id'] : null;
		$queryParams['KLSubRgn_id'] = isset($data['KLSubRgn_id']) ? $data['KLSubRgn_id'] : null;
		$queryParams['KLCity_id'] = isset($data['KLCity_id']) ? $data['KLCity_id'] : null;
		$queryParams['KLTown_id'] = isset($data['KLTown_id']) ? $data['KLTown_id'] : null;
		$queryParams['KLStreet_id'] = isset($data['KLStreet_id']) ? $data['KLStreet_id'] : null;
		$queryParams['Address_House'] = isset($data['Address_House']) ? $data['Address_House'] : '99';
		
		//echo getDebugSql($query, $queryParams);
		
		$result = $this->db->query($query . "where {$filter}", $queryParams);
		if(!is_object($result))
			return false;
		
		$result = $result->result('array');
		
		// если не найдено лпу по данному адресу со службой ппд, тогда выбираем без службы
		if( count($result) == 0 || $this->getLpuOnLpuRegion($result, $iskind) == null ) {
			$result = $this->db->query($query, $queryParams);
			if(!is_object($result))
				return false;
			
			$result = $result->result('array');
		}
		
		//var_dump($result); exit();
		
		if(count($result) > 0) {
			if( isset($data['LpuAttach_id']) && !empty($data['LpuAttach_id']) ) {
				foreach($result as $row) {
					if($data['LpuAttach_id'] == $row['Lpu_id']) { // если ЛПУ прикрепления есть в списке выбранных лпу
						$lpu_at = $row['Lpu_id'];
					}
				}
				if(isset($lpu_at)) {
					return array('Lpu_id'=>$lpu_at, 'LpuAttach_id'=>$lpu_at);
				} else {
					return array('Lpu_id'=>$this->getLpuOnLpuRegion($result, $iskind), 'LpuAttach_id'=>$data['LpuAttach_id']);
				}
			} else {
				return array('Lpu_id'=>$this->getLpuOnLpuRegion($result, $iskind), 'LpuAttach_id'=>null);
			}
		} else {
			return array('Lpu_id'=>null, 'LpuAttach_id' => isset($data['LpuAttach_id']) ? $data['LpuAttach_id'] : null);
		}
	}

	/**
	 *	Неведомая функция
	 */
	function getLpuOnLpuRegion($data, $lrgn) {
		if( count($data) == 1 && empty($lrgn) ) {
			return $data[0]['Lpu_id'];
		} else {
			foreach($data as $d) {
				if( $d['LpuRegionType_id'] == $lrgn ) {
					$lpu_id = $d['Lpu_id'];
					break;
				}
			}
			if(isset($lpu_id)) {
				return $lpu_id;
			} else {
				return null/*$data[0]['Lpu_id']*/;
			}
		}
	}
	
	/**
	*	Проверка на существование карты (по CmpCallCard_Ngod, CmpCallCard_Numv и CmpCallCard_prmDT )
	*/
	function checkExistCmpCallCard($data) {

		$query = "
			select
				CCC.CmpCallCard_id as \"CmpCallCard_id\"
			from
				v_CmpCallCard CCC 

			where
				(CCC.CmpCallCard_Ngod = :CmpCallCard_Ngod or (CCC.CmpCallCard_Ngod is null and :CmpCallCard_Ngod is NULL))
				and (CCC.CmpCallCard_Numv = :CmpCallCard_Numv or (CCC.CmpCallCard_Numv is null and :CmpCallCard_Numv is NULL ))
				and (CCC.Lpu_id = :Lpu_id or (CCC.Lpu_id is null and :Lpu_id is NULL))
				and cast(CCC.CmpCallCard_prmDT as date) = cast(:CmpCallCard_prmDT as date)
				and COALESCE(CCC.CmpCallCardInputType_id, 0) = :CmpCallCardInputType_id
			limit 1

		";

		/*echo getDebugSQL($query, array(
			'CmpCallCard_Ngod' => isset($data['CmpCallCard_Ngod'])?$data['CmpCallCard_Ngod']:NULL
			,'CmpCallCard_Numv' => isset($data['CmpCallCard_Numv'])?$data['CmpCallCard_Numv']:NULL
			,'CmpCallCard_prmDT' => isset($data['CmpCallCard_prmDT'])?$data['CmpCallCard_prmDT']:NULL
			,'Lpu_id' => isset($data['Lpu_id'])?$data['Lpu_id']:NULL
		));die;*/
		$result = $this->db->query($query, array(
			'CmpCallCard_Ngod' => isset($data['CmpCallCard_Ngod'])?$data['CmpCallCard_Ngod']:NULL
			,'CmpCallCard_Numv' => isset($data['CmpCallCard_Numv'])?$data['CmpCallCard_Numv']:NULL
			,'CmpCallCard_prmDT' => isset($data['CmpCallCard_prmDT'])?$data['CmpCallCard_prmDT']:NULL
			,'Lpu_id' => isset($data['Lpu_id'])?$data['Lpu_id']:NULL
			,'CmpCallCardInputType_id' => !empty($data['CmpCallCardInputType_id'])?$data['CmpCallCardInputType_id']:NULL
		));
		if(!is_object($result))
			return true; // чтобы далее не сохранять карту
		
		$response = $result->result('array');

		if (is_array($response) && count($response) > 0) {
			return $response;
		} else {
			return true;
		}
	}

	/**
	*	Проверка необходимости обновления карты и обновление при необходимости
	*/
	function needToRealoadCmpCallCard($data) {

		$query = "
			select
				RDC.CmpCallCard_id as \"CmpCallCard_id\",
				RDC.Registry_id as \"Registry_id\",
				RQ.RegistryQueue_id as \"RegistryQueue_id\",
				RDC.RegistryDataCMP_isPaid as \"RegistryDataCMP_isPaid\",
				RCS.RegistryCheckStatus_Code as \"RegistryCheckStatus_Code\"
			from
				RegistryDataCMP RDC 

				left join v_Registry R  on R.Registry_id = RDC.Registry_id

				left join v_RegistryQueue RQ  on R.Registry_id = RQ.Registry_id

				left join v_RegistryCheckStatus RCS  on R.RegistryCheckStatus_id = RCS.RegistryCheckStatus_id

			where
				RDC.CmpCallCard_id = :CmpCallCard_id
		";
		$result = $this->db2->query($query, array(
			'CmpCallCard_id' => $data['CmpCallCard_id']
		));

		if(is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	*	Проверка  есть ли у передаваемой карты отличия с загруженой
	*/
	function existChangeInCCC($data) {

		$needUpdate = false;
		$query = "
			select
                CmpCallCard_Dsp1 as \"CmpCallCard_Dsp1\",
                Person_PolisNum as \"Person_PolisNum\",
                Sex_id as \"Sex_id\",
                CmpCallCard_Ktov as \"CmpCallCard_Ktov\",
                CmpCallType_id as \"CmpCallType_id\",
                CmpProfile_cid as \"CmpProfile_cid\",
                CmpCallCard_Smpt as \"CmpCallCard_Smpt\",
                CmpCallCard_Stan as \"CmpCallCard_Stan\",
                CmpCallCard_prmDT as \"CmpCallCard_prmDT\",
                CmpCallCard_Line as \"CmpCallCard_Line\",
                CmpResult_id as \"CmpResult_id\",
                CmpArea_gid as \"CmpArea_gid\",
                CmpLpu_id as \"CmpLpu_id\",
                CmpDiag_oid as \"CmpDiag_oid\",
                CmpDiag_aid as \"CmpDiag_aid\",
                CmpTrauma_id as \"CmpTrauma_id\",
                CmpCallCard_IsAlco as \"CmpCallCard_IsAlco\",
                Diag_uid as \"Diag_uid\",
                CmpCallCard_Numb as \"CmpCallCard_Numb\",
                CmpCallCard_Smpb as \"CmpCallCard_Smpb\",
                CmpCallCard_id as \"CmpCallCard_id\",
                CmpCallCard_Numv as \"CmpCallCard_Numv\",
                CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
                CmpCallCard_Prty as \"CmpCallCard_Prty\",
                CmpCallCard_Sect as \"CmpCallCard_Sect\",
                CmpArea_id as \"CmpArea_id\",
                CmpCallCard_City as \"CmpCallCard_City\",
                CmpCallCard_Ulic as \"CmpCallCard_Ulic\",
                CmpCallCard_Dom as \"CmpCallCard_Dom\",
                CmpCallCard_Kvar as \"CmpCallCard_Kvar\",
                CmpCallCard_Podz as \"CmpCallCard_Podz\",
                CmpCallCard_Etaj as \"CmpCallCard_Etaj\",
                CmpCallCard_Kodp as \"CmpCallCard_Kodp\",
                CmpCallCard_Telf as \"CmpCallCard_Telf\",
                CmpPlace_id as \"CmpPlace_id\",
                CmpCallCard_Comm as \"CmpCallCard_Comm\",
                CmpReason_id as \"CmpReason_id\",
                Person_id as \"Person_id\",
                Person_SurName as \"Person_SurName\",
                Person_FirName as \"Person_FirName\",
                Person_SecName as \"Person_SecName\",
                Person_Age as \"Person_Age\",
                Person_BirthDay as \"Person_BirthDay\",
                Person_PolisSer as \"Person_PolisSer\",
                KLRgn_id as \"KLRgn_id\",
                KLSubRgn_id as \"KLSubRgn_id\",
                KLCity_id as \"KLCity_id\",
                KLTown_id as \"KLTown_id\",
                KLStreet_id as \"KLStreet_id\",
                Lpu_ppdid as \"Lpu_ppdid\",
                CmpCallCard_IsEmergency as \"CmpCallCard_IsEmergency\",
                CmpCallCard_IsOpen as \"CmpCallCard_IsOpen\",
                CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\",
                CmpCallCardStatus_Comment as \"CmpCallCardStatus_Comment\",
                CmpCallCard_IsReceivedInPPD as \"CmpCallCard_IsReceivedInPPD\",
                CmpPPDResult_id as \"CmpPPDResult_id\",
                EmergencyTeam_id as \"EmergencyTeam_id\",
                CmpCallCard_IsInReg as \"CmpCallCard_IsInReg\",
                Lpu_id as \"Lpu_id\",
                CmpCallCard_IsMedPersonalIdent as \"CmpCallCard_IsMedPersonalIdent\",
                MedPersonal_id as \"MedPersonal_id\",
                ResultDeseaseType_id as \"ResultDeseaseType_id\",
                CmpCallCard_firstVersion as \"CmpCallCard_firstVersion\",
                CmpCallCard_Deleted as \"CmpCallCard_Deleted\",
                pmUser_delID as \"pmUser_delID\",
                CmpCallCard_delDT as \"CmpCallCard_delDT\",
                UnformalizedAddressDirectory_id as \"UnformalizedAddressDirectory_id\",
                CmpCallCard_IsPaid as \"CmpCallCard_IsPaid\",
                CmpCallCard_Korp as \"CmpCallCard_Korp\",
                CmpCallCard_Room as \"CmpCallCard_Room\",
                CmpCallCard_DiffTime as \"CmpCallCard_DiffTime\",
                UslugaComplex_id as \"UslugaComplex_id\",
                LpuBuilding_id as \"LpuBuilding_id\",
                CmpCallerType_id as \"CmpCallerType_id\",
                CmpCallPlaceType_id as \"CmpCallPlaceType_id\",
                CmpCallCard_rid as \"CmpCallCard_rid\",
                CmpCallCard_Urgency as \"CmpCallCard_Urgency\",
                CmpCallCard_BoostTime as \"CmpCallCard_BoostTime\",
                CmpCallCard_Stbr as \"CmpCallCard_Stbr\",
                CmpCallCard_Stbb as \"CmpCallCard_Stbb\",
                CmpProfile_bid as \"CmpProfile_bid\",
                CmpCallCard_Ncar as \"CmpCallCard_Ncar\",
                CmpCallCard_RCod as \"CmpCallCard_RCod\",
                CmpCallCard_TabN as \"CmpCallCard_TabN\",
                CmpCallCard_Dokt as \"CmpCallCard_Dokt\",
                CmpCallCard_Tab2 as \"CmpCallCard_Tab2\",
                CmpCallCard_Tab3 as \"CmpCallCard_Tab3\",
                CmpCallCard_Tab4 as \"CmpCallCard_Tab4\",
                Diag_sid as \"Diag_sid\",
                CmpTalon_id as \"CmpTalon_id\",
                CmpCallCard_Expo as \"CmpCallCard_Expo\",
                CmpCallCard_Smpp as \"CmpCallCard_Smpp\",
                CmpCallCard_Vr51 as \"CmpCallCard_Vr51\",
                CmpCallCard_D201 as \"CmpCallCard_D201\",
                CmpCallReasonType_id as \"CmpCallReasonType_id\",
                CmpReasonNew_id as \"CmpReasonNew_id\",
                CmpCallCard_EmergencyTeamDiscardReason as \"CmpCallCard_EmergencyTeamDiscardReason\",
                CmpCallCard_IndexRep as \"CmpCallCard_IndexRep\",
                CmpCallCard_IndexRepInReg as \"CmpCallCard_IndexRepInReg\",
                CmpCallCard_IsArchive as \"CmpCallCard_IsArchive\",
                MedStaffFact_id as \"MedStaffFact_id\",
                RankinScale_id as \"RankinScale_id\",
                RankinScale_sid as \"RankinScale_sid\",
                LeaveType_id as \"LeaveType_id\",
                CmpCallCard_isShortEditVersion as \"CmpCallCard_isShortEditVersion\",
                LpuSection_id as \"LpuSection_id\",
                CmpCallCard_Recomendations as \"CmpCallCard_Recomendations\",
                CmpCallCard_Condition as \"CmpCallCard_Condition\",
                Lpu_cid as \"Lpu_cid\",
                CmpCallCard_Tend as \"CmpCallCard_Tend\",
                CmpCallCard_CallLtd as \"CmpCallCard_CallLtd\",
                CmpCallCard_CallLng as \"CmpCallCard_CallLng\",
                CmpCallCard_IsNMP as \"CmpCallCard_IsNMP\",
                CmpRejectionReason_id as \"CmpRejectionReason_id\",
                CmpCallCard_HospitalizedTime as \"CmpCallCard_HospitalizedTime\",
                CmpCallCard_saveDT as \"CmpCallCard_saveDT\",
                CmpCallCard_PlanDT as \"CmpCallCard_PlanDT\",
                CmpCallCard_FactDT as \"CmpCallCard_FactDT\",
                CmpCallCardInputType_id as \"CmpCallCardInputType_id\",
                CmpCallCard_IsExtra as \"CmpCallCard_IsExtra\",
                CmpCallCardStatus_id as \"CmpCallCardStatus_id\",
                CmpCallCard_GUID as \"CmpCallCard_GUID\",
                CmpCallCard_rGUID as \"CmpCallCard_rGUID\",
                CmpCallCard_firstVersionGUID as \"CmpCallCard_firstVersionGUID\",
                CmpCallCardStatus_GUID as \"CmpCallCardStatus_GUID\",
                EmergencyTeam_GUID as \"EmergencyTeam_GUID\",
                CmpCallCard_storDT as \"CmpCallCard_storDT\",
                CmpCallCard_defCom as \"CmpCallCard_defCom\",
                MedService_id as \"MedService_id\",
                CmpCallCard_PolisEdNum as \"CmpCallCard_PolisEdNum\",
                CmpSecondReason_id as \"CmpSecondReason_id\",
                CmpDiseaseAndAccidentType_id as \"CmpDiseaseAndAccidentType_id\",
                CmpCallCard_IsDeterior as \"CmpCallCard_IsDeterior\",
                Diag_sopid as \"Diag_sopid\",
                CmpLeaveType_id as \"CmpLeaveType_id\",
                CmpLeaveTask_id as \"CmpLeaveTask_id\",
                CmpMedicalCareKind_id as \"CmpMedicalCareKind_id\",
                CmpTransportType_id as \"CmpTransportType_id\",
                CmpResultDeseaseType_id as \"CmpResultDeseaseType_id\",
                CmpCallCardResult_id as \"CmpCallCardResult_id\",
                Person_IsUnknown as \"Person_IsUnknown\",
                CmpCallCard_IsPassSSMP as \"CmpCallCard_IsPassSSMP\",
                Lpu_smpid as \"Lpu_smpid\",
                Lpu_hid as \"Lpu_hid\",
                UnformalizedAddressDirectory_wid as \"UnformalizedAddressDirectory_wid\",
                PayType_id as \"PayType_id\",
                CmpCallCard_UlicSecond as \"CmpCallCard_UlicSecond\",
                CmpCallCard_sid as \"CmpCallCard_sid\",
                CmpCallCard_IsActiveCall as \"CmpCallCard_IsActiveCall\",
                CmpCallCard_isControlCall as \"CmpCallCard_isControlCall\",
                CmpCallCard_isTimeExceeded as \"CmpCallCard_isTimeExceeded\",
                CmpCallCard_NumvPr as \"CmpCallCard_NumvPr\",
                CmpCallCard_NgodPr as \"CmpCallCard_NgodPr\",
                CmpCallSignType_id as \"CmpCallSignType_id\",
                Lpu_CodeSMO as \"Lpu_CodeSMO\",
                Registry_sid as \"Registry_sid\",
                Diag_gid as \"Diag_gid\",
                MedicalCareBudgType_id as \"MedicalCareBudgType_id\",
                CmpCommonState_id as \"CmpCommonState_id\",
                CmpCallKind_id as \"CmpCallKind_id\",
                CmpCallCard_isViewCancelCall as \"CmpCallCard_isViewCancelCall\",
                CmpCallCard_Dsp2 as \"CmpCallCard_Dsp2\",
                CmpCallCard_Dspp as \"CmpCallCard_Dspp\",
                CmpCallCard_Dsp3 as \"CmpCallCard_Dsp3\",
                CmpCallCard_Kakp as \"CmpCallCard_Kakp\",
                CmpCallCard_Tper as \"CmpCallCard_Tper\",
                CmpCallCard_Vyez as \"CmpCallCard_Vyez\",
                CmpCallCard_Przd as \"CmpCallCard_Przd\",
                CmpCallCard_Tgsp as \"CmpCallCard_Tgsp\",
                CmpCallCard_Tsta as \"CmpCallCard_Tsta\",
                CmpCallCard_Tisp as \"CmpCallCard_Tisp\",
                CmpCallCard_Tvzv as \"CmpCallCard_Tvzv\",
                CmpCallCard_Kilo as \"CmpCallCard_Kilo\",
                CmpCallCard_Dlit as \"CmpCallCard_Dlit\",
                CmpCallCard_Prdl as \"CmpCallCard_Prdl\",
                CmpArea_pid as \"CmpArea_pid\",
                CmpCallCard_PCity as \"CmpCallCard_PCity\",
                CmpCallCard_PUlic as \"CmpCallCard_PUlic\",
                CmpCallCard_PDom as \"CmpCallCard_PDom\",
                CmpCallCard_PKvar as \"CmpCallCard_PKvar\",
                CmpLpu_aid as \"CmpLpu_aid\",
                CmpCallCard_IsPoli as \"CmpCallCard_IsPoli\",
                cmpCallCard_Medc as \"cmpCallCard_Medc\",
                CmpCallCard_Izv1 as \"CmpCallCard_Izv1\",
                CmpCallCard_Tiz1 as \"CmpCallCard_Tiz1\",
                CmpCallCard_Inf1 as \"CmpCallCard_Inf1\",
                CmpCallCard_Inf2 as \"CmpCallCard_Inf2\",
                CmpCallCard_Inf3 as \"CmpCallCard_Inf3\",
                CmpCallCard_Inf4 as \"CmpCallCard_Inf4\",
                CmpCallCard_Inf5 as \"CmpCallCard_Inf5\",
                CmpCallCard_Inf6 as \"CmpCallCard_Inf6\",
                pmUser_insID as \"pmUser_insID\",
                pmUser_updID as \"pmUser_updID\",
                CmpCallCard_insDT as \"CmpCallCard_insDT\",
                CmpCallCard_updDT as \"CmpCallCard_updDT\"
			from
				CmpCallCard 

			where
				CmpCallCard_id = :CmpCallCard_id
		";

		$result = $this->db2->query($query, array(
			'CmpCallCard_id' => $data['CmpCallCard_id']
		));

		if (is_object($result)) {
			$response =  $result->result('array');

			if (is_array($response) && !empty($response[0])) {
				$response = $response[0];
				$exceptionFields = array('CmpCallCard_insDT','CmpCallCard_updDT', 'pmUser_insID', 'pmUser_updID');
				foreach ($exceptionFields as $ef){
					if (array_key_exists($ef, $response)) {
						unset($response[$ef]);
					}
				}
				foreach($response as $k => $v){
					if (is_object($v) && get_class($v) == 'DateTime') {
						$v = $v->format('Y-m-d H:i:s');
					}

					if (!empty($data[$k]) && $v != $data[$k]){
						$response[$k] = $data[$k];
						$needUpdate = true;
					}
				}
				return array('response' => $response, 'needUpdate' => $needUpdate);
			} else {
				return false;
			}

		} else {
			return false;
		}
	}
}