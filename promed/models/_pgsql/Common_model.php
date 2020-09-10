<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb
 *
 * The New Generation of Medical Statistic Software
 *
 * @package				Common
 * @copyright			Copyright (c) 2009 Swan Ltd.
 * @author				Stas Bykov aka Savage (savage1981@gmail.com)
 * @link				http://swan.perm.ru/PromedWeb
 * @version				?
 */

/**
 * Класс модели для общих операций используемых во всех модулях
 *
 * @package		Common
 * @author		Stas Bykov aka Savage (savage1981@gmail.com)
 *
 * @property EvnNotifyHIVPreg_model EvnNotifyHIVPreg_model
 * @property EvnOnkoNotify_model EvnOnkoNotify_model
 * @property EvnNotifyNarco_model $EvnNotifyNarco_model
 * @property EvnNotifyCrazy_model EvnNotifyCrazy_model
 * @property EvnNotifyHepatitis_model EvnNotifyHepatitis_model
 * @property EvnNotifyTub_model EvnNotifyTub_model
 * @property EvnNotifyOrphan_model EvnNotifyOrphan_model
 * @property EvnNotifyVener_model EvnNotifyVener_model
 * @property EvnNotifyHIV_model EvnNotifyHIV_model
 * @property-read EvnNotifyNephro_model $EvnNotifyNephro_model
 * @property swMongoCache swmongocache
 */
class Common_model extends SwPgModel {

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
		$this->load->library('parser');
	}

	/**
	 * Создание хэшей
	 */
	function createHashesForXmlTemplate() {
		set_time_limit(0);
		if (!isSuperAdmin()) {
			die('access denied');
		}
		$start = time();

		$resp = $this->queryResult('select xmltemplatehtml_id as "xmltemplatehtml_id", XmlTemplateHtml_HtmlTemplate as "XmlTemplateHtml_HtmlTemplate" from xmltemplatehtml where xmltemplatehtml_hashdata is null LIMIT 10000');
		while (count($resp) > 0) {
			foreach ($resp as $respone) {
				$md5 = md5($respone['XmlTemplateHtml_HtmlTemplate']);
				$query = "
					UPDATE xmltemplatehtml  set xmltemplatehtml_hashdata = :xmltemplatehtml_hashdata where xmltemplatehtml_id = :xmltemplatehtml_id
				";
				$this->db->query($query, array(
					'xmltemplatehtml_id' => $respone['xmltemplatehtml_id'],
					'xmltemplatehtml_hashdata' => $md5
				));
			}
			unset($resp);
			$resp = $this->queryResult('select xmltemplatehtml_id as "xmltemplatehtml_id", XmlTemplateHtml_HtmlTemplate as "XmlTemplateHtml_HtmlTemplate" from xmltemplatehtml where xmltemplatehtml_hashdata is null LIMIT 10000');
		}

		$resp = $this->queryResult('select XmlTemplateData_id as "XmlTemplateData_id", XmlTemplateData_Data as "XmlTemplateData_Data" from XmlTemplateData where XmlTemplateData_hashdata is null LIMIT 50000');
		while (count($resp) > 0) {
			foreach ($resp as $respone) {
				$md5 = md5($respone['XmlTemplateData_Data']);
				$query = "
					UPDATE XmlTemplateData  set XmlTemplateData_hashdata = :XmlTemplateData_hashdata where XmlTemplateData_id = :XmlTemplateData_id
				";
				$this->db->query($query, array(
					'XmlTemplateData_id' => $respone['XmlTemplateData_id'],
					'XmlTemplateData_hashdata' => $md5
				));
			}
			unset($resp);
			$resp = $this->queryResult('select XmlTemplateData_id as "XmlTemplateData_id", XmlTemplateData_Data  as "XmlTemplateData_Data" from XmlTemplateData where XmlTemplateData_hashdata is null LIMIT 50000');
		}

		$resp = $this->queryResult('select XmlTemplateSettings_id as "XmlTemplateSettings_id", XmlTemplateSettings_Settings as "XmlTemplateSettings_Settings" from XmlTemplateSettings where XmlTemplateSettings_hashdata is null Limit 100000');
		while (count($resp) > 0) {
			foreach ($resp as $respone) {
				$md5 = md5($respone['XmlTemplateSettings_Settings']);
				$query = "
					UPDATE XmlTemplateSettings  set XmlTemplateSettings_hashdata = :XmlTemplateSettings_hashdata where XmlTemplateSettings_id = :XmlTemplateSettings_id
				";
				$this->db->query($query, array(
					'XmlTemplateSettings_id' => $respone['XmlTemplateSettings_id'],
					'XmlTemplateSettings_hashdata' => $md5
				));
			}
			unset($resp);
			$resp = $this->queryResult('select XmlTemplateSettings_id as "XmlTemplateSettings_id", XmlTemplateSettings_Settings as "XmlTemplateSettings_Settings" from XmlTemplateSettings where XmlTemplateSettings_hashdata is null LIMIT 100000');
		}

		$end = time();

		$time = $end - $start;
		echo $time;
	}

	/**
	 * Парсит Registry_EvnNum в темповую таблицу
	 */
	function parseRegistryEvnNum() {
		set_time_limit(0);
		$dbreg = $this->load->database('registry', true);
		$dbreg->query_timeout = 600000;
		$result = $dbreg->query("
			select
				R.Registry_id as \"Registry_pid\",
				R.Registry_EvnNum as \"Registry_EvnNum\"
			from
				v_Registry R
			where
				R.RegistryType_id = 13
				and R.Registry_EvnNum is not null
				and R.Registry_accDate >= CAST('2015-01-01' as date)
		");

		if (is_object($result)) {
			$resp = $result->result('array');
			foreach($resp as $respone) {
				$Registry_EvnNum = json_decode($respone['Registry_EvnNum'], true);
				$values = array();
				foreach ($Registry_EvnNum as $oneevnnum) {
					$respone['Registry_id'] = $oneevnnum['Registry_id'];
					$respone['Evn_id'] = $oneevnnum['Evn_id'];

					$values[] = "(".$respone['Registry_pid'].", ".$respone['Registry_id'].", ".$respone['Evn_id'].", dbo.tzGetDate())";

					if (count($values) >= 500) {
						$query = "
							insert into
								tmp._RegistryParse 
							(
								Registry_pid,
								Registry_id,
								Evn_id,
								RegistryParse_insDT
							)
							values
							".implode(',', $values)."
						";
						$dbreg->query($query, $respone);
						$values = array();
					}
				}

				if (count($values) > 0) {
					$query = "
						insert into
							tmp._RegistryParse 
						(
							Registry_pid,
							Registry_id,
							Evn_id,
							RegistryParse_insDT
						)
						values
						".implode(',', $values)."
					";
					$dbreg->query($query, $respone);
				}
			}
		}

	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function loadResultDeseaseLeaveTypeList($data){
		switch ($this->getRegionNick()) {
			case "perm":
				$regionTable = 'r59';
				break;
			case "ekb":
				$regionTable = 'r66';
				break;
			default:
				$regionTable = 'fed';
				break;
		}

		$sql = "
			SELECT
				ResultDeseaseLeaveType_id as \"ResultDeseaseLeaveType_id\",
				LeaveType_id as \"LeaveType_id\",
				ResultDeseaseType_id as \"ResultDeseaseType_id\",
			    ResultDeseaseLeaveType_endDT as \"ResultDeseaseLeaveType_endDT\"
			from {$regionTable}.v_ResultDeseaseLeaveType
		";

		$res = $this->db->query($sql);
		if (!is_object($res))
			return false;
		
		return $res->result('array');
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function loadTreatmentClassServiceTypeList($data){
		$sql = "
			SELECT
				TreatmentClassServiceType_id as \"TreatmentClassServiceType_id\",
				TreatmentClass_id as \"TreatmentClass_id\",
				ServiceType_id as \"ServiceType_id\"
			from v_TreatmentClassServiceType 
		";
		$res = $this->db->query($sql, array());
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function loadTreatmentClassVizitTypeList($data){
		$sql = "
			SELECT
				TreatmentClassVizitType_id as \"TreatmentClassVizitType_id\",
				TreatmentClass_id as \"TreatmentClass_id\",
				VizitType_id as \"VizitType_id\"
			from v_TreatmentClassVizitType 
		";
		$res = $this->db->query($sql, array());
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function loadMedicalCareKindLpuSectionProfileList($data){
		$sql = "
			SELECT
				MedicalCareKindLpuSectionProfile_id as \"MedicalCareKindLpuSectionProfile_id\",
				MedicalCareKind_id as \"MedicalCareKind_id\",
				LpuSectionProfile_id as \"LpuSectionProfile_id\"
			from v_MedicalCareKindLpuSectionProfile 
		";
		$res = $this->db->query($sql, array());
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function loadMedSpecLinkList($data){
		$sql = "
			SELECT
				MedSpecLink_id as \"MedSpecLink_id\",
				MedSpec_id as \"MedSpec_id\",
				MedicalCareKind_id as \"MedicalCareKind_id\",
				MedicalCareType_id as \"MedicalCareType_id\"
			from r10.v_MedSpecLink 
		";
		$res = $this->db->query($sql, array());
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function getLpuHTMList($data){
		$filter = '';
		$params = array();
		if (!empty($data['Region_id'])) {
			$filter .= ' and Region_id = :Region_id';
			$params['Region_id'] = $data['Region_id'];
		}

		$filter .= ' and (:onDate between LpuHTM_begDate and LpuHTM_endDate or LpuHTM_endDate IS NULL)';

		if (!empty($data['onDate'])) {
			$params['onDate'] = $data['onDate'];
		} else {
			$params['onDate'] = (new DateTime())->format('Y-m-d');
		}

		$sql = "
			SELECT
				LpuHTM_id as \"LpuHTM_id\",
				LpuHTM_f003mcod as \"LpuHTM_f003mcod\",
				LpuHTM_Nick as \"LpuHTM_Nick\"
			from v_LpuHTM
			where (1=1)
			{$filter}
		";
		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 *	Получение списка ошибок
	 */
	function loadSystemErrorGrid($data) {
		switch (checkMongoDb()) {
			case 'mongo':
				$this->load->library('swMongodb', null, 'mongo_db');
				break;
			case 'mongodb':
				$this->load->library('swMongodbPHP7', null, 'mongo_db');
				break;
			default:
				return array('Error_Msg' => 'The MongoDB PECL extension has not been installed or enabled.');
				break;
		}
		// запрос ошибок из монго
		$response = array('totalCount' => 0, 'data' => array());

		$where = array();
		if (!empty($data['SystemError_Code'])) {
			$where['code'] = intval($data['SystemError_Code']);
		}
		if (!empty($data['SystemError_Error'])) {
			$where['error'] = array('$regex' => $data['SystemError_Error']);
		}

		if (!empty($data['SystemError_Date_From'])) {
			$where['datestamp'] = array('$gte' => strtotime($data['SystemError_Date_From']));
		}
		if (!empty($data['SystemError_Date_To'])) {
			$where['datestamp'] = array('$lte' => strtotime($data['SystemError_Date_To']));
		}

		if (!empty($data['SystemError_Date_From']) && !empty($data['SystemError_Date_To'])) {
			$where['datestamp'] = array('$lte' => strtotime($data['SystemError_Date_To']), '$gte' => strtotime($data['SystemError_Date_From']));
		}

		$rows = $this->mongo_db->offset($data['start'])->limit($data['limit'])->where($where)->get('sysErrors');
		$response['totalCount'] = $this->mongo_db->where($where)->count('sysErrors');

		foreach($rows as $row) {
			$response['data'][] = array(
				'SystemError_id' => $row['code'],
				'SystemError_Code' => $row['code'],
				'SystemError_Error' => strip_tags($row['error']),
				'SystemError_Login' => $row['login'],
				'SystemError_Date' => mb_substr($row['date'],0,10),
				'SystemError_Window' => $row['window'],
				'SystemError_Url' => $row['url'],
				'SystemError_Params' => $row['params'],
				'SystemError_Count' => $row['count'],
				'SystemError_Fixed' => $row['fixed'],
				'SystemError_OpenUrl' => "<a href='/?c=promed&se_code={$row['code']}&se_id={$row['_id']}'>ссылка</a>"
			);
		}

		return $response;
	}

	/**
	 *  Получение ошибки по id и коду
	 */
	function getSystemErrorInfo($code, $id) {
		switch (checkMongoDb()) {
			case 'mongo':
				$this->load->library('swMongodb', null, 'mongo_db');
				break;
			case 'mongodb':
				$this->load->library('swMongodbPHP7', null, 'mongo_db');
				break;
			default:
				return array('Error_Msg' => 'The MongoDB PECL extension has not been installed or enabled.');
				break;
		}
		// запрос ошибок из монго
		$response = array();

		$rows = $this->mongo_db->where(array('code' => intval($code)))->get('sysErrors');
		if (!empty($rows[0])) {
			$row = $rows[0];
			if ($row['_id'] == $id) {
				return array(
					'SystemError_id' => $row['code'],
					'SystemError_Code' => $row['code'],
					'SystemError_Error' => $row['error'],
					'SystemError_Login' => $row['login'],
					'SystemError_Date' => mb_substr($row['date'], 0, 10),
					'SystemError_Window' => $row['window'],
					'SystemError_Url' => $row['url'],
					'SystemError_Params' => $row['params'],
					'SystemError_Count' => $row['count'],
					'SystemError_Fixed' => $row['fixed'],
					'SystemError_TechInfo' => $row['techinfo']
				);
			}
		}

		return null;
	}

	/**
	 *	Получение ошибки
	 */
	function loadSystemErrorsViewWindow($data) {
		switch (checkMongoDb()) {
			case 'mongo':
				$this->load->library('swMongodb', null, 'mongo_db');
				break;
			case 'mongodb':
				$this->load->library('swMongodbPHP7', null, 'mongo_db');
				break;
			default:
				return array('Error_Msg' => 'The MongoDB PECL extension has not been installed or enabled.');
				break;
		}
		// запрос ошибок из монго
		$response = array();

		$rows = $this->mongo_db->where(array('code' => intval($data['SystemError_id'])))->get('sysErrors');
		if (!empty($rows[0])) {
			$row = $rows[0];
			$response[] = array(
				'SystemError_id' => $row['code'],
				'SystemError_Code' => $row['code'],
				'SystemError_Error' => $row['error'],
				'SystemError_Login' => $row['login'],
				'SystemError_Date' => mb_substr($row['date'],0,10),
				'SystemError_Window' => $row['window'],
				'SystemError_Url' => $row['url'],
				'SystemError_Params' => $row['params'],
				'SystemError_Count' => $row['count'],
				'SystemError_Fixed' => $row['fixed'],
				'SystemError_OpenUrl' => "<a href='/?c=promed&se_code={$row['code']}&se_id={$row['_id']}'>ссылка</a>"
			);
		}

		return $response;
	}

	/**
	 *	Проверка наличия записей в очереди ActiveMQ
	 */
	function checkActiveMQIsEmpty() {
		$resp = array(
			'empty' => true
		);

		$stomp = new Stomp(STOMPMQ_MESSAGE_SERVER_URL, 'system', 'manager');
		$stomp->setReadTimeout(STOMPMQ_MESSAGE_TIMEOUT);
		// подписываемся на очередь
		$isSubscribe = $stomp->subscribe('/queue/ru.swan.emergency.localtomaindb');
		if ($isSubscribe) {
			// читаем очередь
			if ($stomp->hasFrame()) {
				$frame = $stomp->readFrame();
				if ($frame != NULL) {
					$resp['empty'] = false;
				}
			}
		}

		return $resp;
	}

	/**
	 *	Сохранение ошибки
	 */
	function saveSystemError($data) {
		switch (checkMongoDb()) {
			case 'mongo':
				$this->load->library('swMongodb', null, 'mongo_db');
				break;
			case 'mongodb':
				$this->load->library('swMongodbPHP7', null, 'mongo_db');
				break;
			default:
				return array('Error_Msg' => 'The MongoDB PECL extension has not been installed or enabled.');
				break;
		}

		$data['day'] = date('z');
		$data['year'] = date('Y');
		$data['date'] = date('d.m.Y H:i:s');

		$data['code'] = 1;
		$data['count'] = 1;

		$row = $this->mongo_db->where(array())->select(array('code'))->order_by(array('code' => 'desc'))->limit(1)->get('sysErrors');
		if (!empty($row[0]['code'])) {
			$data['code'] = $row[0]['code'] + 1;
		}

		$wheres = array(
			'window' => $data['window'],
			'day' => $data['day'],
			'year' => $data['year']
		);

		if (empty($data['url'])) {
			$wheres['error'] = $data['error'];
		} else {
			$wheres['url'] = $data['url'];
		}

		$row = $this->mongo_db->where($wheres)->get('sysErrors');
		if (is_array($row)) {
			if ((count($row)>0) && is_array($row[0])) {
				$data['code'] = $row[0]['code'];
				$data['count'] = $row[0]['count'] + 1;
				$data['_id'] = $row[0]['_id'];
			}
		}

		$params = array(
			'login' => $_SESSION['login'],
			// 'session' => json_encode($_SESSION),
			'date' => $data['date'],
			'day' => $data['day'],
			'year' => $data['year'],
			'datestamp' => strtotime(substr($data['date'], 0, 10)),
			'error' => $data['error'],
			'techinfo' => $data['techInfo'],
			'window' => $data['window'],
			'url' => $data['url'],
			'params' => $data['params'],
			'code' => $data['code'],
			'count' => $data['count'],
			'fixed' => 0
		);

		if (!empty($data['_id'])) {
			$res = $this->mongo_db->where(array('_id'=>$data['_id']))->update('sysErrors', $params);
		} else {
			$this->mongo_db->insert('sysErrors', $params);
		}

		return array('Error_Msg' => '', 'num' => $data['code']);
	}

	/**
	 *	Сохранение признака исправлена ошибки
	 */
	function saveSystemErrorFixed($data) {
		switch (checkMongoDb()) {
			case 'mongo':
				$this->load->library('swMongodb', null, 'mongo_db');
				break;
			case 'mongodb':
				$this->load->library('swMongodbPHP7', null, 'mongo_db');
				break;
			default:
				return array('Error_Msg' => 'The MongoDB PECL extension has not been installed or enabled.');
				break;
		}

		$wheres = array(
			'code' => intval($data['SystemError_id'])
		);

		$row = $this->mongo_db->where($wheres)->get('sysErrors');
		if (is_array($row)) {
			if ((count($row)>0) && is_array($row[0])) {
				$data['_id'] = $row[0]['_id'];
			}
		}

		if (!empty($data['_id'])) {
			$params = array(
				'$set' => array('fixed' => intval($data['SystemError_Fixed']))
			);
			$res = $this->mongo_db->where(array('_id' => $data['_id']))->update('sysErrors', $params);
		}

		return array('Error_Msg' => '');
	}

	/**
	 *	Получнеие списка профилей
	 */
	function loadLpuSectionProfileList($data) {
		$filter = "";

		if (!empty($data['LpuSectionProfile_id'])) {
			$filter .= " and LSP.LpuSectionProfile_id = :LpuSectionProfile_id";
		} else {
			if ($data['session']['region']['nick'] == 'perm') {
				// фильтруем по отделению
				if (!empty($data['LpuSection_id'])) {
					$LpuSectionService_id = $this->getFirstResultFromQuery("select lss.LpuSectionService_id from v_LpuSectionService lss where lss.LpuSection_id = :LpuSection_id and lss.LpuSection_did is not null LIMIT 1", $data);
					// если есть обслуживаемые отделения
					if (!empty($LpuSectionService_id)) {
						$filter .= " and exists(select lss.LpuSectionService_id from v_LpuSectionService lss inner join v_LpuSection ls on ls.LpuSection_id = lss.LpuSection_did where ls.LpuSectionProfile_id = LSP.LpuSectionProfile_id and lss.LpuSection_id = :LpuSection_id LIMIT 1)";
					}
				}
			}

			$dateFilterMSO = "";
			if (!empty($data['onDate'])) {
				$dateFilterMSO = " and COALESCE(LSPMSO.LpuSectionProfileMedSpecOms_begDate, CAST(:onDate as date)) <= CAST(:onDate  as date) and COALESCE(LSPMSO.LpuSectionProfileMedSpecOms_endDate, CAST(:onDate as date)) >= CAST(:onDate as date)";
			}

			$join = "";
			if ($data['session']['region']['nick'] == 'ekb') {
				$join .= " left join r66.v_LpuSectionProfileGROUP lspg on lspg.LpuSectionProfile_id = lsp.LpuSectionProfile_id ";

				if (!empty($data['onDate'])) {
					$filter .= " and COALESCE(lspg.LpuSectionProfileGROUP_begDate, CAST(:onDate as date)) <= CAST(:onDate  as date) and COALESCE(lspg.LpuSectionProfileGROUP_endDate, CAST(:onDate as date)) >= CAST(:onDate as date)";
				}

				if (!empty($data['LpuSectionProfileGRAPP_CodeIsNotNull'])) {
					$filter .= " and lspg.LpuSectionProfileGROUP_APP > 0";
				}
				if (!empty($data['LpuSectionProfileGRKSS_CodeIsNotNull'])) {
					$filter .= " and lspg.LpuSectionProfileGROUP_KSS > 0";
				}
				if (!empty($data['LpuSectionProfileGRSZP_CodeIsNotNull'])) {
					$filter .= " and lspg.LpuSectionProfileGROUP_SZP > 0";
				}

				if (!empty($data['MedSpecOms_id'])) {
					$result = $this->getFirstResultFromQuery("select LSPMSO.LpuSectionProfileMedSpecOms_id from dbo.v_LpuSectionProfileMedSpecOms LSPMSO where LSPMSO.MedSpecOms_id = :MedSpecOms_id {$dateFilterMSO} LIMIT 1", $data);
					if (!empty($result)) {
						$filter .= " and exists(
							select LSPMSO.LpuSectionProfileMedSpecOms_id
							from dbo.v_LpuSectionProfileMedSpecOms LSPMSO 
							where LSPMSO.MedSpecOms_id = :MedSpecOms_id and LSPMSO.LpuSectionProfile_id = LSP.LpuSectionProfile_id
							and (LSPMSO.Lpu_id is null or LSPMSO.Lpu_id = :Lpu_id LIMIT 1) {$dateFilterMSO}
						)";
					}
				}
			} else {
				if (!empty($data['MedSpecOms_id'])) {
					$result = $this->getFirstResultFromQuery("select LSPMSO.LpuSectionProfileMedSpecOms_id from dbo.v_LpuSectionProfileMedSpecOms LSPMSO where LSPMSO.MedSpecOms_id = :MedSpecOms_id {$dateFilterMSO} LIMIT 1", $data);
					if (!empty($result)) {
						$filter .= " and exists(select LSPMSO.LpuSectionProfileMedSpecOms_id from dbo.v_LpuSectionProfileMedSpecOms LSPMSO where LSPMSO.MedSpecOms_id = :MedSpecOms_id and LSPMSO.LpuSectionProfile_id = LSP.LpuSectionProfile_id {$dateFilterMSO} LIMIT 1)";
					}
				}
			}

			if ($this->getRegionNick() == 'ekb' && !empty($data['AddLpusectionProfiles']) && $data['AddLpusectionProfiles'] == 1 && !empty($data['LpuSection_id'])) {
				$join .= " left join v_LpuSection LS on LS.LpuSectionProfile_id = LSP.LpuSectionProfile_id";
				$join .= " left join dbo.v_LpuSectionLpuSectionProfile LSLSP  on LSP.LpuSectionProfile_id = LSLSP.LpuSectionProfile_id";

				$filter .= " and (LS.LpuSection_id = :LpuSection_id or ";

				if ( !empty($data['onDate']) ) {
					$filter .= "(LSLSP.LpuSection_id = :LpuSection_id and COALESCE(LSLSP.LpuSectionLpuSectionProfile_begDate, CAST(:onDate as date)) <= CAST(:onDate  as date) and COALESCE(LSLSP.LpuSectionLpuSectionProfile_endDate, CAST(:onDate as date)) >= CAST(:onDate as date))";
				}
				else {
					$filter .= "LSLSP.LpuSection_id = :LpuSection_id";
				}

				$filter .= ")";

			} else {
				if (!empty($data['MedPersonal_id']) && !empty($data['LpuSection_id'])) {
					$result = $this->getFirstResultFromQuery("
						select 
							LSPMSO.LpuSectionProfileMedSpecOms_id as \"LpuSectionProfileMedSpecOms_id\"
						from
							dbo.v_LpuSectionProfileMedSpecOms LSPMSO
							inner join v_MedStaffFact msf on msf.MedSpecOms_id = LSPMSO.MedSpecOms_id
						where
							msf.MedPersonal_id = :MedPersonal_id and msf.LpuSection_id = :LpuSection_id
							{$dateFilterMSO}
						LIMIT 1
					", $data);
					if (!empty($result)) {
						$filter .= "
							and exists(
								select 
									LpuSectionProfileMedSpecOms_id
								from
									dbo.v_LpuSectionProfileMedSpecOms LSPMSO 
									inner join v_MedStaffFact msf on msf.MedSpecOms_id = LSPMSO.MedSpecOms_id
								where
									msf.MedPersonal_id = :MedPersonal_id and msf.LpuSection_id = :LpuSection_id
									and LSPMSO.LpuSectionProfile_id = LSP.LpuSectionProfile_id
									and (LSPMSO.Lpu_id is null or LSPMSO.Lpu_id = :Lpu_id)
									{$dateFilterMSO}
								LIMIT 1
							)";
					}
				}
			}

			if (!empty($data['onDate'])) {
				$filter .= " and COALESCE(LSP.LpuSectionProfile_begDT, CAST(:onDate as date)) <= CAST(:onDate  as date) and COALESCE(LSP.LpuSectionProfile_endDT, CAST(:onDate as date)) >= CAST(:onDate as date)";
			}

			if (!empty($data['LpuUnit_id'])) {
				$filter .= " and LSP.LpuSectionProfile_id in (
					select LpuSectionProfile_id
					from v_LpuSection
					where LpuUnit_id = :LpuUnit_id

					union all

					select t1.LpuSectionProfile_id
					from v_LpuSectionLpuSectionProfile t1
						inner join v_LpuSection t2 on t2.LpuSection_id = t1.LpuSection_id
					where t2.LpuUnit_id = :LpuUnit_id
				)";
			}

		}

		$query = "
			select distinct
				LSP.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				LSP.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
				LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\"
			from
				v_LpuSectionProfile LSP 
				{$join}
			where
				(1=1)
				{$filter}
		";

		//echo getDebugSQL($query, $data);die;
		$result = $this->db->query($query, $data);

		if (is_object($result))
		{
			return $result->result('array');
		}

		return false;
	}

	/**
	 *	Получение списка дополнительных профилей
	 */
	function loadLpuSectionProfileDopList($data) {
		$filter = "";
		$filterDT1 = "";
		$filterDT2 = "";

		if (!empty($data['filterByKPG'])) {
			// не отображать значения без объемов по КПГ
			$filter .= "
				and exists(
					select
						mokpg.Mes_id
					from MesOld mokpg 
					where
						mokpg.LpuSectionProfile_id = LSP.LpuSectionProfile_id
						and mokpg.MesType_id = (4)
					LIMIT 1
				)
			";
		}

		if ( !empty($data['onDate']) ) {
			$filterDT1 .= '
				and LSLSP.LpuSectionLpuSectionProfile_begDate <= CAST(:onDate as date)
				and (LSLSP.LpuSectionLpuSectionProfile_endDate >= CAST(:onDate  as date) OR LSLSP.LpuSectionLpuSectionProfile_endDate IS NULL)
			';
		}

		$query = "
			select
				LSP.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				LSP.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
				LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\"
			from
				v_LpuSectionLpuSectionProfile LSLSP 
				inner join v_LpuSectionProfile LSP  on LSP.LpuSectionProfile_id = LSLSP.LpuSectionProfile_id
			where
				LSLSP.LpuSection_id = :LpuSection_id
				{$filter}
				{$filterDT1}

			union

			select
				LSP.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				LSP.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
				LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\"
			from
				v_LpuSection LS 
				inner join v_LpuSectionProfile LSP  on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
			where
				LS.LpuSection_id = :LpuSection_id
				{$filter}
				{$filterDT2}

			order by
				\"LpuSectionProfile_Code\"
		";

		$result = $this->db->query($query, $data);

		if (is_object($result))
		{
			return $result->result('array');
		}

		return false;
	}

	/**
	 * Получение поля Code из таблицы $obj по его id
	 * @param string $obj Название таблицы
	 * @param integer $id Идентификатор в таблице
	 * @return string Значение поля Code у заданной идентификатором записи в таблице
	 */
	function getCodeById($obj, $id) {
		$result = -1;

		$query = "
			select " . $obj . "_Code as \"" . $obj . "_Code\"
			from v_" . $obj . " 
			where " . $obj . "_id = :id
			LIMIT 1
		";
		$res = $this->db->query($query, array('id' => $id));

		if ( !is_object($res) ) {
			return $result;
		}

		$response = $res->result('array');

		if ( !is_array($response) || count($response) == 0) {
			return $result;
		}

		$result = $response[$obj . "_Code"];

		return $result;
	}

	/**
	 *  Получение списка ЛПУ с особой сортировкой для выбора
	 */
	function loadLpuSelectList($data) {
		$params = array(
			'Lpu_id' => $data['Lpu_id']
		);
		$sql = "
			select
				-- select
				l.Lpu_id as \"Lpu_id\",
				l.Lpu_Nick as \"Lpu_Nick\",
				l.Lpu_Name as \"Lpu_Name\",
				l.UAddress_Address as \"UAddress_Address\"
				-- end select
			from
				-- from
				v_Lpu l 
				left join v_Address a  on l.UAddress_id = a.Address_id
				left join v_Lpu ul  on ul.Lpu_id=:Lpu_id
				left join v_Address ua  on ul.UAddress_id = ua.Address_id
				-- end from
			WHERE
				-- where
				(1=1)
				-- end where
			ORDER BY
				-- order by
				case
					when l.Lpu_id = ul.Lpu_id then 1
					when a.KLCity_id = ua.KLCity_id then 2
					else 3
				end,
				l.Lpu_Nick
				-- end order by
		";

		// echo getDebugSQL(getLimitSQLPH($sql, $data['start'], $data['limit']), $params); die;
		$result = $this->db->query(getLimitSQLPH($sql, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($sql), $params);

		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}
	}


	/**
	 * Получение списка ТАП и КВС
	 * @param array $data Фильтры
	 * @return array Ассоциативный массив со списком ТАП и КВС
	 */
	function loadEvnPLEvnPSGrid($data = array()) {
		$filter = "(1 = 1)";
		$queryParams = array();

		switch ( $data['EvnClass_id'] ) {
			case 3:
			case 30:
				$filter .= " and E.EvnClass_id = :EvnClass_id";
				$queryParams['EvnClass_id'] = $data['EvnClass_id'];
			break;

			default:
				$filter .= " and E.EvnClass_id in (3, 6, 30)";
			break;
		}

		if ( !empty($data['Person_id']) ) {
			$filter .= " and E.EvnClass_id in (3, 6, 30)";
			$filter .= " and E.Person_id = :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];

			if ( !empty($data['Lpu_eid']) ) {
				$filter .= " and E.Lpu_id = :Lpu_eid";
				$queryParams['Lpu_eid'] = $data['Lpu_id'];
			}

			if ( !empty($data['EvnClass_SysNick']) ) {
				$filter .= " and E.EvnClass_SysNick= :EvnClass_SysNick";
				$queryParams['EvnClass_SysNick'] = $data['EvnClass_SysNick'];
			}
		}
		else {
			if ( !empty($data['Evn_NumCard']) ) {
				$filter .= " and (COALESCE(EPL.EvnPL_NumCard, '') = :Evn_NumCard or COALESCE(EPS.EvnPS_NumCard, '') = :Evn_NumCard)";
				$queryParams['Evn_NumCard'] = $data['Evn_NumCard'];
			}

			if ( isset($data['Evn_setDate_Range'][0]) ) {
				$filter .= " and E.Evn_setDate >= cast(:Evn_setDate_0 as timestamp)";
				$queryParams['Evn_setDate_0'] = $data['Evn_setDate_Range'][0];
			}

			if ( isset($data['Evn_setDate_Range'][1]) ) {
				$filter .= " and E.Evn_setDate <= cast(:Evn_setDate_1 as timestamp)";
				$queryParams['Evn_setDate_1'] = $data['Evn_setDate_Range'][1];
			}

			if ( !empty($data['Lpu_eid']) ) {
				$filter .= " and E.Lpu_id = :Lpu_eid";
				$queryParams['Lpu_eid'] = $data['Lpu_eid'];
			}

			if ( !empty($data['Person_Surname']) ) {
				$filter .= " and PS.Person_Surname = :Person_Surname";
				$queryParams['Person_Surname'] = $data['Person_Surname'];
			}

			if ( !empty($data['Person_Firname']) ) {
				$filter .= " and PS.Person_Firname = :Person_Firname";
				$queryParams['Person_Firname'] = $data['Person_Firname'];
			}

			if ( !empty($data['Person_Secname']) ) {
				$filter .= " and PS.Person_Secname = :Person_Secname";
				$queryParams['Person_Secname'] = $data['Person_Secname'];
			}
		}

		$query = "
			select
				E.Evn_id as \"Evn_id\",
				E.EvnClass_id as \"EvnClass_id\",
				E.Person_id as \"Person_id\",
				E.PersonEvn_id as \"PersonEvn_id\",
				E.Server_id as \"Server_id\",
				case
					when E.EvnClass_id = 3 then 'ТАП'
					when E.EvnClass_id = 6 then 'Стомат. ТАП'
					when E.EvnClass_id = 30 then 'КВС'
					else ''
				end as \"EvnClass_Name\",
				coalesce(EPL.EvnPL_NumCard, EPLS.EvnPLStom_NumCard, EPS.EvnPS_NumCard, '') as \"Evn_NumCard\",
				COALESCE(PS.Person_Surname, '') as \"Person_Surname\",
				COALESCE(PS.Person_Firname, '') as \"Person_Firname\",
				COALESCE(PS.Person_Secname, '') as \"Person_Secname\",
				to_char(PS.Person_Birthday, 'DD.MM.YYYY') as \"Person_Birthday\",
				to_char(E.Evn_setDate, 'DD.MM.YYYY') as \"Evn_setDate\",
				to_char(E.Evn_disDate, 'DD.MM.YYYY') as \"Evn_disDate\",
				RTRIM(COALESCE(L.Lpu_Nick, '')) as \"Lpu_Name\",
				RTRIM(COALESCE(D.Diag_Code, '')) as \"Diag_Code\",
				RTRIM(COALESCE(D.Diag_Name, '')) as \"Diag_Name\"
			from
				v_Evn E 
				inner join v_PersonState PS  on PS.Person_id = E.Person_id
				inner join v_Lpu L  on L.Lpu_id = E.Lpu_id
				LEFT JOIN LATERAL  (
					select
						EvnPL_NumCard,
						Diag_id
					from v_EvnPL 
					where EvnPL_id = E.Evn_id
						and E.EvnClass_id = 3
					LIMIT 1
				) EPL on true
				LEFT JOIN LATERAL (
					select
						EvnPLStom_NumCard,
						Diag_id
					from v_EvnPLStom 
					where EvnPLStom_id = E.Evn_id
						and E.EvnClass_id = 6
					LIMIT 1
				) EPLS on true
				LEFT JOIN LATERAL (
					select
						EvnPS_NumCard,
						Diag_id
					from v_EvnPS 
					where EvnPS_id = E.Evn_id
						and E.EvnClass_id = 30
					LIMIT 1
				) EPS on true
				left join v_Diag D  on D.Diag_id = coalesce(EPL.Diag_id, EPLS.Diag_id, EPS.Diag_id)
			where " . $filter . "
			LIMIT 100
		";
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

   /**
     * Получение ближайшего созданного случая по врачу на дату
     */
	function checkIsEvnPLExist($data) {

		$query = "
			select 
				E.Evn_id as \"Evn_id\",
				E.EvnClass_SysNick as \"EvnClass_SysNick\",
				E.Evn_pid as \"Evn_pid\",
				pE.EvnClass_SysNick as \"parentEvnClass_SysNick\"
			from v_Evn E 
			LEFT JOIN LATERAL (
					select 
						MedStaffFact_id
					from v_EvnVizitPL 
					where EvnVizitPL_id = E.Evn_id
					limit 1
				) EVPL on true
			LEFT JOIN LATERAL (
				select 
					MedStaffFact_id
				from v_EvnVizitPLStom 
				where EvnVizitPLStom_id = E.Evn_id
				limit 1
			) EVPLS on true
			left join v_Evn pE on pE.Evn_id = E.Evn_pid
			where (1=1)
				and E.EvnClass_id in (11, 13)
				and E.Person_id = :Person_id
				and cast(E.Evn_insDT as date) = cast(dbo.tzGetDate() as date)
				and coalesce(EVPL.MedStaffFact_id, EVPLS.MedStaffFact_id) = :MedStaffFact_id
			order by E.Evn_insDT desc
			limit 1
		";

		$result = $this->queryResult($query, $data);
		return $result;
	}

	/**
	 * Получение списка филиалов по заданными фильтрами
	 * @param array $data Фильтры
	 * @return array Ассоциативный массив с данными филиалов
	 */
	public function loadLpuFilialList($data = array()) {
		$fieldsList = array();
		$filterList = array();
		$joinList = array();
		$queryParams = array();

		$this->load->library('swCache');
		// Читаем из кэша
		if ( $data['mode'] == 'all' && $data['Lpu_id'] > 0 ) {
			if ($resCache = $this->swcache->get("LpuFilialList_".$data['Lpu_id'])) {
				return $resCache;
			}
		}

		if ( array_key_exists('linkedLpuIdList', $data['session']) && $data['mode'] != 'combo' ) {
			$filterList[] = "LF.Lpu_id in (" . implode(',', $data['session']['linkedLpuIdList']) . ")";
		}
		else if ( !empty($data['Lpu_id']) ) {
			$filterList[] = "LF.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		switch ( $data['mode'] ) {
			case 'all':
				$fieldsList[] = 'L.Lpu_Nick as "Lpu_Name"';
				$joinList[] = 'inner join v_Lpu L  on L.Lpu_id = LF.Lpu_id';

				if ( !empty($data['Lpu_id']) ) {
					$fieldsList[] = 'case when LF.Lpu_id = :Lpu_id then 1 else 2 end as "sortID"';

					if ( empty($queryParams['Lpu_id']) ) {
						$queryParams['Lpu_id'] = $data['Lpu_id'];
					}
				}
			break;
		}

		$query = "
			SELECT
				LF.LpuFilial_id as \"LpuFilial_id\",
				LF.Lpu_id as \"Lpu_id\",
				RTRIM(LF.LpuFilial_Code) as \"LpuFilial_Code\",
				RTRIM(LF.LpuFilial_Name) as \"LpuFilial_Name\",
				to_char(LpuFilial_begDate, 'DD.MM.YYYY') as \"LpuFilial_begDate\",
				to_char(LpuFilial_endDate, 'DD.MM.YYYY') as \"LpuFilial_endDate\"
				" . (count($fieldsList) > 0 ? ',' . implode(',', $fieldsList) : '' ) . "
			FROM v_LpuFilial LF 
				" . implode(' ', $joinList) . "
			" . (count($filterList) > 0 ? "WHERE " . implode(' and ', $filterList) : "") . "
			ORDER by LF.LpuFilial_Code, LF.LpuFilial_Name
		";
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');
			if ( $data['mode'] == 'all' && $data['Lpu_id'] > 0 ) {
				// Кэшируем
				$this->swcache->set("LpuFilialList_".$data['Lpu_id'], $response, array('ttl'=>60*24));
			}
			return $response;
		}
		else {
			return false;
		}
	}

	/**
	 * Получение списка подразделений по заданными фильтрами
	 * @param array $data Фильтры
	 * @return array Ассоциативный массив с данными подразделений
	 */
	function loadLpuBuildingList($data = array()) {
		$fieldsList = array();
		$filterList = array();
		$joinList = array();
		$queryParams = array();

		$this->load->library('swCache');
		// Читаем из кэша
		if ( $data['mode'] == 'all' && $data['Lpu_id'] > 0 ) {
			if ($resCache = $this->swcache->get("LpuBuildingList_".$data['Lpu_id'])) {
				return $resCache;
			}
		}

		if ( array_key_exists('linkedLpuIdList', $data['session']) && $data['mode'] != 'combo' ) {
			$filterList[] = "LB.Lpu_id in (" . implode(',', $data['session']['linkedLpuIdList']) . ")";
		}
		else if ( !empty($data['Lpu_id']) ) {
			$filterList[] = "LB.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		switch ( $data['mode'] ) {
			case 'all':
				$fieldsList[] = 'L.Lpu_Nick as "Lpu_Name"';
				$fieldsList[] = 'LF.LpuFilial_id as "LpuFilial_id"';
				$fieldsList[] = 'LF.LpuFilial_Name as "LpuFilial_Name"';
				$joinList[] = 'inner join v_Lpu L  on L.Lpu_id = LB.Lpu_id';
				$joinList[] = 'left join v_LpuFilial LF  on LF.LpuFilial_id = LB.LpuFilial_id';

				if ( !empty($data['Lpu_id']) ) {
					$fieldsList[] = 'case when LB.Lpu_id = :Lpu_id then 1 else 2 end as "sortID"';

					if ( empty($queryParams['Lpu_id']) ) {
						$queryParams['Lpu_id'] = $data['Lpu_id'];
					}
				}
			break;
		}

		$query = "
			SELECT
				LB.LpuBuilding_id as \"LpuBuilding_id\",
				LB.Lpu_id as \"Lpu_id\",
				RTRIM(LB.LpuBuilding_Code) as \"LpuBuilding_Code\",
				RTRIM(LB.LpuBuilding_Name) as \"LpuBuilding_Name\"
				" . (count($fieldsList) > 0 ? ',' . implode(',', $fieldsList) : '' ) . "
			FROM v_LpuBuilding LB 
				" . implode(' ', $joinList) . "
			" . (count($filterList) > 0 ? "WHERE " . implode(' and ', $filterList) : "") . "
			ORDER by LB.LpuBuilding_Code, LB.LpuBuilding_Name
		";
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');
			if ( $data['mode'] == 'all' && $data['Lpu_id'] > 0 ) {
				// Кэшируем
				$this->swcache->set("LpuBuildingList_".$data['Lpu_id'], $response , array('ttl'=>60*24));
			}
			return $response;
		}
		else {
			return false;
		}
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function loadFederalKladrList($data)
	{
		/*return array(
			array('KLArea_id'=>77, 'KLAdr_Code'=>'7700000000000'),
			array('KLArea_id'=>78, 'KLAdr_Code'=>'7800000000000'),
			array('KLArea_id'=>259902, 'KLAdr_Code'=>'9200000000000')
		);*/

		$this->load->library('swCache', array('use'=>'mongo'));
		// Читаем из кэша
		if ($resCache = $this->swcache->get("FederalKladr")) {
			return $resCache;
		} else {
			$query = "
				select
					KLArea_id as \"KLArea_id\",
					KLAdr_Code as \"KLAdr_Code\"
				from KLArea 
				where KLSocr_id = 14
				and KLAreaLevel_id = 1
			";
			$queryParams = array();
			//echo getDebugSql($query, $queryParams); exit();
			$result = $this->db->query($query, $queryParams);

			if (is_object($result)) {
				$response = $result->result('array');
				// Закэшируем и в следующий раз достанем из кэша
				$this->swcache->set("FederalKladr", $response);
				return $response;
			} else {
				return false;
			}
		}
	}

	/**
	 * @param type $data
	 * @return type
	 */
	public function loadFRMOSectionList($data = array()) {
		$filter = "";
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);

		if (!empty($data['FRMOSection_id'])) {
			$filter .= " and s.FRMOSection_id = :FRMOSection_id";
			$queryParams['FRMOSection_id'] = $data['FRMOSection_id'];
		} else {
			if (!empty($data['RegisterMO_OID'])) {
				$filter .= " and s.FRMOSection_MOID = :RegisterMO_OID";
				$queryParams['RegisterMO_OID'] = $data['RegisterMO_OID'];
			}
			if (!empty($data['FRMOUnit_OID'])) {
				$filter .= " and s.FRMOUnit_OID = :FRMOUnit_OID";
				$queryParams['FRMOUnit_OID'] = $data['FRMOUnit_OID'];
			}
			if (!empty($data['query'])) {
				$filter .= " and COALESCE(s.FRMOSection_Name, s.FRMOSection_SubDivisionName, s.FRMOSection_AmbulanceName, s.FRMOSection_LabName) ilike :query || '%'";
				$queryParams['query'] = $data['query'];
			}
		}

		return $this->queryResult("
			(select 
				s.FRMOSection_id as \"FRMOSection_id\",
				pt.Lpu_id as \"Lpu_id\",
				s.FRMOSection_MOID as \"FRMOSection_MOID\",
				s.FRMOUnit_OID as \"FRMOUnit_OID\",
				s.FRMOSection_OID as \"FRMOSection_OID\",
				COALESCE(s.FRMOSection_Name, s.FRMOSection_SubDivisionName, s.FRMOSection_AmbulanceName, s.FRMOSection_LabName) as \"FRMOSection_Name\",
				COALESCE(s.FRMOSection_AreaPrefix, '') || ' ' || COALESCE(s.FRMOSection_AreaName, '')
					|| ' ' || COALESCE(s.FRMOSection_StreetPrefix, '') || ' ' || COALESCE(s.FRMOSection_StreetName, '')
					|| case when s.FRMOSection_House is not null then ' д.' || s.FRMOSection_House else '' end
				as \"FRMOSection_Address\",
				to_char(s.FRMOSection_LiquidationDate, 'DD.MM.YYYY') as \"FRMOSection_LiquidationDate\"
			from
				fed.PassportToken pt 
				inner join nsi.v_FRMOSection s  on pt.PassportToken_tid = s.FRMOSection_MOID
			where
				pt.Lpu_id = :Lpu_id
				{$filter}
			LIMIT 100)
			union all
		  
		  	-- по филиалам
			(select
				s.FRMOSection_id as \"FRMOSection_id\",
				pt.Lpu_id as \"Lpu_id\",
				s.FRMOSection_MOID as \"FRMOSection_MOID\",
				s.FRMOUnit_OID as \"FRMOUnit_OID\",
				s.FRMOSection_OID as \"FRMOSection_OID\",
				COALESCE(s.FRMOSection_Name, s.FRMOSection_SubDivisionName, s.FRMOSection_AmbulanceName, s.FRMOSection_LabName) as \"FRMOSection_Name\",
				COALESCE(s.FRMOSection_AreaPrefix, '') || ' ' || COALESCE(s.FRMOSection_AreaName, '')
					|| ' ' || COALESCE(s.FRMOSection_StreetPrefix, '') || ' ' || COALESCE(s.FRMOSection_StreetName, '')
					|| case when s.FRMOSection_House is not null then ' д.' || s.FRMOSection_House else '' end
				as \"FRMOSection_Address\",
				to_char(s.FRMOSection_LiquidationDate, 'DD.MM.YYYY') as \"FRMOSection_LiquidationDate\"
			from
				fed.PassportToken pt 
				inner join nsi.v_RegisterMO rm  on rm.RegisterMO_ParentOID = pt.PassportToken_tid
				inner join nsi.v_FRMOSection s  on rm.RegisterMO_OID = s.FRMOSection_MOID
			where
				pt.Lpu_id = :Lpu_id
				{$filter}
			LIMIT 100)
		", $queryParams);
	}

	/**
	 * @param type $data
	 * @return type
	 */
	public function loadFRMOUnitList($data = array()) {
		$filter = "";
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);

		if (!empty($data['FRMOUnit_id'])) {
			$filter .= " and u.FRMOUnit_id = :FRMOUnit_id";
			$queryParams['FRMOUnit_id'] = $data['FRMOUnit_id'];
		} else {
			if (!empty($data['RegisterMO_OID'])) {
				$filter .= " and u.FRMOUnit_MOID = :RegisterMO_OID";
				$queryParams['RegisterMO_OID'] = $data['RegisterMO_OID'];
			}
			if (!empty($data['FRMOUnit_OID'])) {
				$filter .= " and u.FRMOUnit_OID = :FRMOUnit_OID";
				$queryParams['FRMOUnit_OID'] = $data['FRMOUnit_OID'];
			}
			if (!empty($data['query'])) {
				$filter .= " and FRMOUnit_Name like :query || '%'";
				$queryParams['query'] = $data['query'];
			}
		}

		return $this->queryResult("
			(select
				u.FRMOUnit_id as \"FRMOUnit_id\",
				pt.Lpu_id as \"Lpu_id\",
				u.FRMOUnit_MOID as \"FRMOUnit_MOID\",
				u.FRMOUnit_OID as \"FRMOUnit_OID\",
				u.FRMOUnit_Name as \"FRMOUnit_Name\",
				u.FRMOUnit_TypeId as \"FRMOUnit_TypeId\",
				u.FRMOUnit_TypeName as \"FRMOUnit_TypeName\",
				u.FRMOUnit_KindId as \"FRMOUnit_KindId\",
				u.FRMOUnit_KindName as \"FRMOUnit_KindName\",
				COALESCE(u.FRMOUnit_AreaPrefix, '') || ' ' || COALESCE(u.FRMOUnit_AreaName, '')
					|| ' ' || COALESCE(u.FRMOUnit_StreetPrefix, '') || ' ' || COALESCE(u.FRMOUnit_StreetName, '')
					|| case when u.FRMOUnit_House is not null then ' д.' || u.FRMOUnit_House else '' end
				as \"FRMOUnit_Address\",
				to_char(u.FRMOUnit_LiquidationDate, 'DD.MM.YYYY') as \"FRMOUnit_LiquidationDate\"
			from
				fed.PassportToken pt 
				inner join nsi.v_FRMOUnit u  on pt.PassportToken_tid = u.FRMOUnit_MOID
			where
				pt.Lpu_id = :Lpu_id
				{$filter}
			LIMIT 100)
			union all
			-- по филиалам
			(select
				u.FRMOUnit_id as \"FRMOUnit_id\",
				pt.Lpu_id as \"Lpu_id\",
				u.FRMOUnit_MOID as \"FRMOUnit_MOID\",
				u.FRMOUnit_OID as \"FRMOUnit_OID\",
				u.FRMOUnit_Name as \"FRMOUnit_Name\",
				u.FRMOUnit_TypeId as \"FRMOUnit_TypeId\",
				u.FRMOUnit_TypeName as \"FRMOUnit_TypeName\",
				u.FRMOUnit_KindId as \"FRMOUnit_KindId\",
				u.FRMOUnit_KindName as \"FRMOUnit_KindName\",
				COALESCE(u.FRMOUnit_AreaPrefix, '') || ' ' || COALESCE(u.FRMOUnit_AreaName, '')
					|| ' ' || COALESCE(u.FRMOUnit_StreetPrefix, '') || ' ' || COALESCE(u.FRMOUnit_StreetName, '')
					|| case when u.FRMOUnit_House is not null then ' д.' || u.FRMOUnit_House else '' end
				as \"FRMOUnit_Address\",
				to_char(u.FRMOUnit_LiquidationDate, 'DD.MM.YYYY') as \"FRMOUnit_LiquidationDate\"
			from
				fed.PassportToken pt 
				inner join nsi.v_RegisterMO rm  on rm.RegisterMO_ParentOID = pt.PassportToken_tid
				inner join nsi.v_FRMOUnit u  on rm.RegisterMO_OID = u.FRMOUnit_MOID
			where
				pt.Lpu_id = :Lpu_id
				{$filter}
			LIMIT 100)
		", $queryParams);
	}

	/**
	 * Получение списка отделений по заданными фильтрами
	 * @param array $data Фильтры
	 * @return array Ассоциативный массив с данными отделений
	 */
	function loadLpuSectionList($data) {
		$dispContractFilterList = array();
		$dispFilterList = array();
		$fieldsList = array();
		$filterList = array();
		$joinList = array();
		$queryParams = array();

		$queryParams['Lpu_id'] = !empty($data['Lpu_id'])?$data['Lpu_id']:$data['session']['lpu_id'];

		$this->load->library('swCache');
		if ( $data['mode'] == 'all' && $data['Lpu_id'] > 0 ) {
			if ($resCache = $this->swcache->get("LpuSectionList_".$data['Lpu_id'])) {
				return $resCache;
			}
		}
		
		if (getRegionNick() == 'buryatiya' && $data['mode'] == 'dispcontractcombo') {
			// Если на дату оказания услуги с выбранной МО нет действующих договоров по сторонним специалистам, то грузим не по договорам
			$filter_ldc = "";
			$params_ldc = [
				'Lpu_oid' => $queryParams['Lpu_id'],
				'Lpu_id' => $data['session']['lpu_id']
			];
			if (!empty($data['date'])) {
				$filter_ldc .= " and coalesce(ldc.LpuDispContract_setDate, :onDate) <= :onDate";
				$filter_ldc .= " and coalesce(ldc.LpuDispContract_disDate, :onDate) >= :onDate";
				$params_ldc['onDate'] = $data['date'];
			}
			$resp_ldc = $this->queryResult("
				select
					ldc.LpuDispContract_id as \"LpuDispContract_id\"
				from
					v_LpuDispContract ldc
				where
					ldc.Lpu_oid = :Lpu_oid
					and ldc.Lpu_id = :Lpu_id
					{$filter_ldc}
				limit 1
			", $params_ldc);
			if (empty($resp_ldc[0]['LpuDispContract_id'])) {
				$data['mode'] = 'combo';
			}
		}

		$typejoin = 'inner';
		if(isset($data['fromMZ']) && $data['fromMZ'] == '2')
			$typejoin = 'left';

		if ( !empty($data['LpuSection_id']) ) {
			$filterList[] = "LS.LpuSection_id = :LpuSection_id";
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		} else {
            $lp_dlo_date = !empty($data['date']) ? $data['date'] : date('Y-m-d');

            if ( !empty($lp_dlo_date) ) {
                $fieldsList[] = 'lp_dlo.LpuPeriodDLO_Code as "LpuPeriodDLO_Code"';
                $fieldsList[] = 'to_char(lp_dlo.LpuPeriodDLO_begDate, \'DD.MM.YYYY\') as "LpuPeriodDLO_begDate"';
                $fieldsList[] = 'to_char(lp_dlo.LpuPeriodDLO_endDate, \'DD.MM.YYYY\') as "LpuPeriodDLO_endDate"';

                $joinList[] = '
					LEFT JOIN LATERAL (
						select 
							i_lpd.LpuPeriodDLO_Code,
							i_lpd.LpuPeriodDLO_begDate,
							i_lpd.LpuPeriodDLO_endDate
						from
							v_LpuPeriodDLO i_lpd
						where
							i_lpd.Lpu_id = LS.Lpu_id and
							(
								i_lpd.LpuUnit_id is null or
								i_lpd.LpuUnit_id = LS.LpuUnit_id			
							) and
							i_lpd.LpuPeriodDLO_begDate <= CAST(:lp_dlo_date as date) and
							(
								i_lpd.LpuPeriodDLO_endDate is null or
								i_lpd.LpuPeriodDLO_endDate >= CAST(:lp_dlo_date as date)
							)
						order by
							i_lpd.LpuUnit_id desc, LpuPeriodDLO_Code desc
						limit 1
					) lp_dlo ON true
				';
                $queryParams['lp_dlo_date'] = $lp_dlo_date;
            }
			if ( !empty($data['date']) ) {
				$dispContractFilterList[] = '(LpuDispContract_setDate is null or LpuDispContract_setDate <= CAST(:date as date))';
				$dispContractFilterList[] = '(LpuDispContract_disDate is null or LpuDispContract_disDate >= CAST(:date as date))';
				$dispFilterList[] = '(LS.LpuSection_setDate is null or LS.LpuSection_setDate <= CAST(:date as date))';
				$dispFilterList[] = '(LS.LpuSection_disDate is null or LS.LpuSection_disDate >= CAST(:date as date))';
				$filterList[] = '(LS.LpuSection_setDate is null or LS.LpuSection_setDate <= CAST(:date as date))';
				$filterList[] = '(LS.LpuSection_disDate is null or LS.LpuSection_disDate >= CAST(:date as date))';
				$queryParams['date'] = $data['date'];
			}

			if ( $data['Lpu_id'] > 0 && empty($data['Org_id']) ) {
				if (isFarmacy() && isset($data['session']['OrgFarmacy_id'])) {
					//TODO: выборка отделений конечно некорректная, в будущем необходимо уточнить критерии
					$filterList[] = "LS.LpuSection_id in (select LpuSection_id from Contragent  where OrgFarmacy_id = :OrgFarmacy_id or OrgFarmacy_id is null)";
					$queryParams['OrgFarmacy_id'] = $data['session']['OrgFarmacy_id'];

				}
				//else if ( array_key_exists('linkedLpuIdList', $data['session']) ) {
				else if ( array_key_exists('linkedLpuIdList', $data['session']) && (empty($data['mode']) || !in_array($data['mode'], array('combo', 'addSubProfile'))) ) {
					$filterList[] = "LB.Lpu_id in (" . implode(',', $data['session']['linkedLpuIdList']) . ")";
				} else {
					$filterList[] = "LB.Lpu_id = :Lpu_id";
				}
			}
		}

		if ( !empty($data['Org_id']) ) {
			$filterList[] = "L.Org_id = :Org_id";
			$queryParams['Org_id'] = $data['Org_id'];
		}

		if ( !empty($data['LpuUnit_id']) ) {
			$filterList[] = "LU.LpuUnit_id = :LpuUnit_id";
			$queryParams['LpuUnit_id'] = $data['LpuUnit_id'];
		}
		if ( !empty($data['LpuBuilding_id']) ) {
			$filterList[] = "LS.LpuBuilding_id = :LpuBuilding_id";
			$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}
		
		if ( !empty($data['LpuUnitType_id']) ) {
			$filterList[] = "LU.LpuUnitType_id = :LpuUnitType_id";
			$queryParams['LpuUnitType_id'] = $data['LpuUnitType_id'];
		}
		

		if ( !empty($data['LpuSectionProfile_id']) ) {
			$dispContractFilterList[] = "LpuSectionProfile_id = :LpuSectionProfile_id";
			$filterList[] = "LSP.LpuSectionProfile_id = :LpuSectionProfile_id";
			$queryParams['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
		}

		if ( $this->getRegionNick() == 'ekb' ) {
			$fieldsList[] = 'LSL.MedicalCareKind_id as "MedicalCareKind_id"';
			$fieldsList[] = 'LSL.MedicalCareKind_Code as "MedicalCareKind_Code"';
			$joinList[] = '
				LEFT JOIN LATERAL (
					select t2.MedicalCareKind_id, t2.MedicalCareKind_Code
					from r66.v_LpuSectionLink t1 
						left join fed.v_MedicalCareKind t2  on t2.MedicalCareKind_id = t1.MedicalCareKind_id
					where t1.LpuSection_id = LS.LpuSection_id
					limit 1
				) LSL on true
			';
		}

		switch ( $data['mode'] ) {
			case 'all':
				$fieldsList[] = 'L.Lpu_Nick as "Lpu_Name"';
				//$joinList[] = 'inner join v_Lpu L  on L.Lpu_id = LS.Lpu_id';

				if ( !empty($data['Lpu_id']) ) {
					$fieldsList[] = 'case when LS.Lpu_id = :Lpu_id then 1 else 2 end as "sortID"';

					if ( empty($queryParams['Lpu_id']) ) {
						$queryParams['Lpu_id'] = $data['Lpu_id'];
					}
				}
			break;

			case 'dlo':
				$filterList[] = "LSP.LpuSectionProfile_SysNick != 'priem'";
				$filterList[] = "LUT.LpuUnitType_id = 2";
			break;

			case 'stom':
				$filterList[] = "substring(cast(LS.LpuSection_Code as varchar), 1, 2) = '18'";
			break;

			case 'combo':
				//
			break;
		}
        if(!empty($data['where'])){
            $LpuSection_setDate='LS.LpuSection_setDate as "LpuSection_setDate"';
        }else{
            $LpuSection_setDate='to_char(LS.LpuSection_setDate, \'DD.MM.YYYY\') as "LpuSection_setDate"';
        }
		$query = "
			SELECT
				LS.LpuSection_id as \"LpuSection_id\",
				LS.Lpu_id as \"Lpu_id\",
				'main' as \"listType\",
				COALESCE(LB.LpuBuilding_id, 0) as \"LpuBuilding_id\",
				COALESCE(LU.LpuUnit_id, 0) as \"LpuUnit_id\",
				COALESCE(LU.LpuUnitSet_id, 0) as \"LpuUnitSet_id\",
				LS.LpuSection_pid as \"LpuSection_pid\",
				LS.LpuSectionAge_id as \"LpuSectionAge_id\",
				case when (LS.LpuSection_pid is not null) then 'x-combo-list-tree' else '' end as \"LpuSection_Class\",
				COALESCE(LSP.LpuSectionProfile_id, 0) as \"LpuSectionProfile_id\",
				COALESCE(LSP.LpuSectionProfile_Code, '0') as \"LpuSectionProfile_Code\",
				COALESCE(LSP.LpuSectionProfile_Name, '') as \"LpuSectionProfile_Name\",
				COALESCE(LSP.LpuSectionProfile_SysNick, '') as \"LpuSectionProfile_SysNick\",
				COALESCE(LSBP.LpuSectionBedProfile_id, 0) as \"LpuSectionBedProfile_id\",
				COALESCE(LSBP.LpuSectionBedProfile_Code, '0') as \"LpuSectionBedProfile_Code\",
				COALESCE(LSBP.LpuSectionBedProfile_Name, '') as \"LpuSectionBedProfile_Name\",
				COALESCE(LUT.LpuUnitType_id, 0) as \"LpuUnitType_id\",
				RTRIM(LS.LpuSection_Code) as \"LpuSection_Code\",
				RTRIM(LS.LpuSection_Name) as \"LpuSection_Name\",
				COALESCE(LUT.LpuUnitType_Code, 0) as \"LpuUnitType_Code\",
				COALESCE(LUT.LpuUnitType_SysNick, '') as \"LpuUnitType_SysNick\",
				COALESCE(LU.LpuUnitSet_Code, 0) as \"LpuUnitSet_Code\",
				to_char(COALESCE(LS.LpuSection_disDate, L.Lpu_endDate), 'DD.MM.YYYY') as \"LpuSection_disDate\",
				".$LpuSection_setDate.",
				COALESCE(LS.LpuSection_IsHTMedicalCare, 1) as \"LpuSection_IsHTMedicalCare\",
				(
					select
						STRING_AGG(cast(LpuSection_did as varchar), ',')
					FROM
						v_LpuSectionService 
					WHERE
						LpuSection_id = ls.LpuSection_id
				) as \"LpuSectionServiceList\",
				(
					select
						STRING_AGG(cast(LpuSectionProfile_id as varchar), ',')
					FROM
						v_LpuSectionLpuSectionProfile 
					WHERE
						LpuSection_id = LS.LpuSection_id
				) as \"LpuSectionLpuSectionProfileList\"
				" . (count($fieldsList) > 0 ? ',' . implode(',', $fieldsList) : '' ) . "
			FROM v_LpuSection LS 
				{$typejoin} join v_Lpu L  on L.Lpu_id = LS.Lpu_id
				{$typejoin} join v_LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id
					-- данное условие не нужно, расхождение может быть только на тестовой, поскольку данные изначально кривые - на рабочей все отлично 
					-- or LU.LpuUnit_id = (select top 1 LS1.LpuUnit_id from LpuSection LS1  where LS1.LpuSection_id = LS.LpuSection_pid)
				{$typejoin} join v_LpuBuilding LB  on LB.LpuBuilding_id = LU.LpuBuilding_id
				{$typejoin} join v_LpuSectionProfile LSP  on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
				left join v_LpuSectionBedProfile LSBP  on LSBP.LpuSectionBedProfile_id = LS.LpuSectionBedProfile_id
				{$typejoin} join v_LpuUnitType LUT  on LUT.LpuUnitType_id = LU.LpuUnitType_id
				" . implode(' ', $joinList) . "
			" . (count($filterList) > 0 ? "WHERE " . implode(' and ', $filterList) : "") . "
		";

		$dispcontractquery = "
			SELECT
				LS.LpuSection_id as \"LpuSection_id\",
				LS.Lpu_id as \"Lpu_id\",
				'dispcontract' as \"listType\",
				COALESCE(LB.LpuBuilding_id, 0) as \"LpuBuilding_id\",
				COALESCE(LU.LpuUnit_id, 0) as \"LpuUnit_id\",
				COALESCE(LU.LpuUnitSet_id, 0) as \"LpuUnitSet_id\",
				LS.LpuSection_pid as \"LpuSection_pid\",
				LS.LpuSectionAge_id as \"LpuSectionAge_id\",
				case when (LS.LpuSection_pid is not null) then 'x-combo-list-tree' else '' end as \"LpuSection_Class\",
				COALESCE(LSP.LpuSectionProfile_id, 0) as \"LpuSectionProfile_id\",
				COALESCE(LSP.LpuSectionProfile_Code, '0') as \"LpuSectionProfile_Code\",
				COALESCE(LSP.LpuSectionProfile_Name, '') as \"LpuSectionProfile_Name\",
				COALESCE(LSP.LpuSectionProfile_SysNick, '') as \"LpuSectionProfile_SysNick\",
				COALESCE(LSBP.LpuSectionBedProfile_id, 0) as \"LpuSectionBedProfile_id\",
				COALESCE(LSBP.LpuSectionBedProfile_Code, '0') as \"LpuSectionBedProfile_Code\",
				COALESCE(LSBP.LpuSectionBedProfile_Name, '') as \"LpuSectionBedProfile_Name\",
				COALESCE(LUT.LpuUnitType_id, 0) as \"LpuUnitType_id\",
				RTRIM(LS.LpuSection_Code) as \"LpuSection_Code\",
				RTRIM(LS.LpuSection_Name) as \"LpuSection_Name\",
				COALESCE(LUT.LpuUnitType_Code, 0) as \"LpuUnitType_Code\",
				COALESCE(LUT.LpuUnitType_SysNick, '') as \"LpuUnitType_SysNick\",
				COALESCE(LU.LpuUnitSet_Code, 0) as \"LpuUnitSet_Code\",
				to_char(COALESCE(LS.LpuSection_disDate, L.Lpu_endDate), 'DD.MM.YYYY') as \"LpuSection_disDate\",
				".$LpuSection_setDate.",
				COALESCE(LS.LpuSection_IsHTMedicalCare, 1) as \"LpuSection_IsHTMedicalCare\",
				(
					select
						STRING_AGG(cast(LpuSection_did as varchar), ',')
					FROM
						v_LpuSectionService 
					WHERE
						LpuSection_id = ls.LpuSection_id
				) as \"LpuSectionServiceList\",
				(
					select
						STRING_AGG(cast(LpuSectionProfile_id as varchar), ',')
					FROM
						v_LpuSectionLpuSectionProfile 
					WHERE
						LpuSection_id = LS.LpuSection_id
				) as \"LpuSectionLpuSectionProfileList\"
				" . (count($fieldsList) > 0 ? ',' . implode(',', $fieldsList) : '' ) . "
			FROM v_LpuSection LS 
				inner join v_Lpu L  on L.Lpu_id = LS.Lpu_id
				inner join v_LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id
				inner join v_LpuBuilding LB  on LB.LpuBuilding_id = LU.LpuBuilding_id
				inner join v_LpuSectionProfile LSP  on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
				left join v_LpuSectionBedProfile LSBP  on LSBP.LpuSectionBedProfile_id = LS.LpuSectionBedProfile_id
				inner join v_LpuUnitType LUT  on LUT.LpuUnitType_id = LU.LpuUnitType_id
				" . implode(' ', $joinList) . "
			WHERE (1 = 1)
				and exists (
					select Lpu_oid
					from v_LpuDispContract 
					where Lpu_oid = LB.Lpu_id
						and (LpuSection_id is null or LpuSection_id = LS.LpuSection_id)
						and LpuSectionProfile_id in (
							select LpuSectionProfile_id FROM v_LpuSectionLpuSectionProfile  WHERE LpuSection_id = LS.LpuSection_id
							union all
							select LS.LpuSectionProfile_id
						)
						" . (count($dispContractFilterList) > 0 ? "and " . implode(' and ', $dispContractFilterList) : "") . "
					limit 1
				)
				and LB.Lpu_id != :Lpu_id
				" . (count($dispFilterList) > 0 ? "and " . implode(' and ', $dispFilterList) : "") . "
				
		";

		if ( $data['mode'] == 'all' ) { // при загрузке
			$query = "
				select * from (
					" . $query . "
					union all
					" . $dispcontractquery. "
				) as LpuSection";
				if(!empty($data['where'])){
					$query .= $data['where'];
				}
				$query .= "
				ORDER by \"LpuSection_pid\", \"LpuSection_Name\"
			";
		} else if ( $data['mode'] == 'dispcontractcombo' ) {
			$query = $dispcontractquery . "
				AND LB.Lpu_id = :Lpu_oid
				ORDER by LS.LpuSection_Name
			";
			// фильтр по заданной МО
			$queryParams['Lpu_oid'] = $data['Lpu_id'];
			// фильтр по контрактам с текущей МО
			$queryParams['Lpu_id'] = $data['session']['lpu_id'];
		} else {
			$query .= "
				ORDER by LS.LpuSection_Name
			";
		}

		//echo getDebugSql($query, $queryParams); exit();
		//echo '<pre>',print_r(getDebugSql($query, $queryParams)),'</pre>'; die();
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			// сортируем под Pid в два цикла
			$m = $result->result('array');
			$ls = array();
			$lsp = array();

			foreach ($m as $rows) {
				if ($rows['LpuSection_pid']>0) {
					$lsp[] = $rows;
				} else {
					$ls[] = $rows;
				}
			}
			$m = array();
			foreach ($ls as $rows) {
				$m[] = $rows;
				foreach ($lsp as $prows) {
					if ($prows['LpuSection_pid']==$rows['LpuSection_id']) {
						$m[] = $prows;
					}
				}
			}
			if ( $data['mode'] == 'all' && $data['Lpu_id'] > 0 && count($queryParams)==1) {
				$this->swcache->set("LpuSectionList_".$data['Lpu_id'], $m, array('ttl'=>1800));
			}

			return $m;
		}
		else {
			return false;
		}
	}


	/**
	 * @param $data
	 * @return array|bool|false
	 * Получение списка палат по заданными фильтрами
	 */
	function loadLpuSectionWardList($data) {
		$fieldsList = array();
		$filterList = array();
		$joinList = array();
		$queryParams = array();

		$this->load->library('swCache');
		if ( $data['mode'] == 'all' && $data['Lpu_id'] > 0 ) {
			if ($resCache = $this->swcache->get("LpuSectionWardList_".$data['Lpu_id'])) {
				return $resCache;
			}
		}

		if ( array_key_exists('linkedLpuIdList', $data['session']) && $data['mode'] != 'combo' ) {
			$filterList[] = "LS.Lpu_id in (" . implode(',', $data['session']['linkedLpuIdList']) . ")";
		}
		else if ( !empty($data['Lpu_id']) ) {
			$filterList[] = 'LS.Lpu_id = :Lpu_id';
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		if ( !empty($data['LpuSection_id']) ) {
			$filterList[] = 'LS.LpuSection_id = :LpuSection_id';
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		}

		if ( !empty($data['LpuSectionWard_id']) ) {
			$filterList[] = 'LSW.LpuSectionWard_id = :LpuSectionWard_id';
			$queryParams['LpuSectionWard_id'] = $data['LpuSectionWard_id'];
		} else {
			$queryParams['LpuSectionWard_id'] = null;
		}

		if ( !empty($data['LpuSectionBedProfile_id']) ) {
			$joinList[] = 'inner join v_LpuSectionWardLink LSWL on LSWL.LpuSectionWard_id = LSW.LpuSectionWard_id';
			$joinList[] = 'inner join v_LpuSectionBedState LSBS on LSBS.LpuSectionBedState_id = LSWL.LpuSectionBedState_id and LSBS.LpuSectionBedProfile_id = :LpuSectionBedProfile_id';
			$queryParams['LpuSectionBedProfile_id'] = $data['LpuSectionBedProfile_id'];
		}

		if ( !empty($data['LpuUnit_id']) ) {
			$filterList[] = 'LS.LpuUnit_id = :LpuUnit_id';
			$queryParams['LpuUnit_id'] = $data['LpuUnit_id'];
		}

		if ( !empty($data['Sex_id']) ) {
			$filterList[] = '(3 = :Sex_id or LSW.Sex_id = :Sex_id or LSW.Sex_id is null)';
			$queryParams['Sex_id'] = $data['Sex_id'];
		}

		if ( !empty($data['date']) ) {
			$filterList[] = '(cast(:date as date) between cast(LSW.LpuSectionWard_setDate as date) and cast(COALESCE(LSW.LpuSectionWard_disDate,CAST(:date as date)) as date))';
			$queryParams['date'] = $data['date'];
		}

		switch ( $data['mode'] ) {
			case 'combo':
				//
			break;

			case 'freelyprofil':
				//если редактируем палату в движении, то в списке должны быть: указанная палата и остальные палаты профильного отделения, соответствующие полу пациента (включая общие палаты), в которых есть свободные места
				if ( isset($queryParams['LpuSection_id']) and isset($queryParams['Sex_id']) and isset($queryParams['date']) )
				{
					$joinList[] = 'LEFT JOIN LATERAL (
						select
							count(EvnSection.EvnSection_id) as busy
						from
							v_EvnSection EvnSection 
						where
							EvnSection.LpuSection_id = LSW.LpuSection_id
							and EvnSection.LpuSectionWard_id = LSW.LpuSectionWard_id
							and cast(EvnSection_setDate as DATE) <= cast(:date as DATE)
							and EvnSection_disDate is null
					) busyCountProfil on true
					';
					$filterList[] = '
						LSW.LpuSectionWard_id = :LpuSectionWard_id or (
						LSW.LpuSection_id = :LpuSection_id
						and (3 = :Sex_id or LSW.Sex_id = :Sex_id or LSW.Sex_id is null)
						and (cast(:date as date) between cast(LSW.LpuSectionWard_setDate as date) and cast(COALESCE(LSW.LpuSectionWard_disDate,CAST(:date as date)) as date))
						and (LSW.LpuSectionWard_BedCount - LSW.LpuSectionWard_BedRepair - busyCountProfil.busy) > 0
					)';
				}
			break;

			case 'freelypriem':
				//если редактируем палату в приемном отделении, то в списке должны быть: указанная палата и остальные палаты приемного отделения, соответствующие полу пациента (включая общие палаты), в которых есть свободные места
				if ( isset($queryParams['LpuSection_id']) and isset($queryParams['Sex_id']) and isset($queryParams['date']) )
				{
					$joinList[] = 'LEFT JOIN LATERAL (
						select
							count(EvnPS.EvnPS_id) as busy
						from
							v_EvnPS EvnPS 
						where
							EvnPS.LpuSection_pid = LSW.LpuSection_id
							and EvnPS.LpuSectionWard_id = LSW.LpuSectionWard_id
							and cast(EvnPS_setDate as DATE) <= cast(:date as DATE)
							and EvnPS_disDate is null
					) busyCountPriem on true
					';
					$filterList[] = '
						LSW.LpuSectionWard_id = :LpuSectionWard_id or (
						LSW.LpuSection_id = :LpuSection_id
						and (3 = :Sex_id or LSW.Sex_id = :Sex_id or LSW.Sex_id is null)
						and (cast(:date as date) between cast(LSW.LpuSectionWard_setDate as date) and cast(COALESCE(LSW.LpuSectionWard_disDate,CAST(:date as date)) as date))
						and (LSW.LpuSectionWard_BedCount - LSW.LpuSectionWard_BedRepair - busyCountPriem.busy) > 0
					)';
				}
			break;

			default:
				$fieldsList[] = 'L.Lpu_Nick as "Lpu_Name"';
				$joinList[] = 'inner join v_Lpu L  on L.Lpu_id = LS.Lpu_id';

				if ( !empty($data['Lpu_id']) ) {
					$fieldsList[] = 'case when LS.Lpu_id = :Lpu_id then 1 else 2 end as "sortID"';
				}
			break;
		}

		if ( empty($queryParams['Lpu_id']) ) {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		$busyWardsList = [];

		if (!empty($queryParams['date'])) {
			$lpuFilter = "";
			$lsFilter = "";
			if (!empty($data['Lpu_id'])) {
				$lpuFilter .= " and EvnSection.Lpu_id = :Lpu_id";
				$lpuFilter .= " and E.Lpu_id = :Lpu_id";
				$queryParams['Lpu_id'] = $data['Lpu_id'];
			}
			if (!empty($queryParams['LpuSection_id'])) {
				$lsFilter .= " and EvnSection.LpuSection_id = :LpuSection_id";
			}

			$queryResponse = $this->queryResult("
				select
					EvnSection.LpuSectionWard_id as \"LpuSectionWard_id\",
					count(EvnSection.EvnSection_id) as \"cnt\"
				from v_EvnSection as EvnSection
					left join Evn as E on E.Evn_id = EvnSection.EvnSection_pid
						and E.Evn_deleted = 1
				where
					(1 = 1)
					{$lpuFilter}
					{$lsFilter}
					and E.Evn_id is not null
					and EvnSection.LpuSectionWard_id is not null
					and EvnSection.EvnSection_setDate <= :date
					and (EvnSection.EvnSection_disDate is null or EvnSection.EvnSection_disDate > :date)
				group by
					EvnSection.LpuSectionWard_id
			", $queryParams);

			if (is_array($queryResponse)) {
				foreach ($queryResponse as $row) {
					$busyWardsList[$row['LpuSectionWard_id']] = $row['cnt'];
				}
			}
		}

		$response = $this->queryResult('
			SELECT
				LSW.LpuSectionWard_id as "LpuSectionWard_id",
				LSW.LpuSection_id as "LpuSection_id",
				LSW.Sex_id as "Sex_id",
				LS.Lpu_id as "Lpu_id",
				COALESCE(LSW.LpuSectionWard_BedCount, 0) as "LpuSectionWard_BedCount",
				COALESCE(LSW.LpuSectionWard_BedRepair, 0) as "LpuSectionWard_BedRepair",
				RTRIM(COALESCE(LSW.LpuSectionWard_Name, \'\')) as "LpuSectionWard_Name",
				to_char(LSW.LpuSectionWard_disDate, \'DD.MM.YYYY\') as "LpuSectionWard_disDate",
				to_char(LSW.LpuSectionWard_setDate, \'DD.MM.YYYY\') as "LpuSectionWard_setDate"
				' . (count($fieldsList) > 0 ? ',' . implode(',', $fieldsList) : '' ) . '
			FROM v_LpuSectionWard LSW
				inner join v_LpuSection LS on LS.LpuSection_id = LSW.LpuSection_id
				' . implode(' ', $joinList) . '
				' . (count($filterList) > 0 ? 'WHERE ' . implode(' and ', $filterList) : '') . '
		', $queryParams);

		if ($response === false) {
			return false;
		}

		if ( $data['mode'] == 'all' && $data['Lpu_id'] > 0 ) {
			$this->swcache->set("LpuSectionWardList_".$data['Lpu_id'], $response);
		}

		if (count($busyWardsList) > 0) {
			foreach ($response as $key => $row) {
				$busy = 0;

				if (!empty($busyWardsList[$row['LpuSectionWard_id']])) {
					$busy = $busyWardsList[$row['LpuSectionWard_id']];
				}

				if ($row['LpuSectionWard_BedCount'] - $row['LpuSectionWard_BedRepair'] - $busy <= 0) {
					unset($response[$key]);
				}
			}
		}

		return $response;
	}

	/**
	 * Получение списка профилей коек по заданными фильтрами
	 * @param array $data Фильтры
	 * @return array Ассоциативный массив с данными пофилей коек
	 */
	function loadLpuSectionBedProfileList($data) {
		$filterList[] = 'LSBP.LpuSectionBedProfile_id is not null';
		$joinList = array();
		$queryParams = array();

		if ( !empty($data['LpuSection_id']) ) {
			$filterList[] = 'LS.LpuSection_id = :LpuSection_id';
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		}

		if ( !empty($data['LpuSectionWard_id']) ) {
			$joinList[] = 'inner join v_LpuSectionWardLink LSWL on LSWL.LpuSectionBedState_id = LSBS.LpuSectionBedState_id and LSWL.LpuSectionWard_id = :LpuSectionWard_id';
			$queryParams['LpuSectionWard_id'] = $data['LpuSectionWard_id'];
		} else {
			$queryParams['LpuSectionWard_id'] = null;
		}

		if ( !empty($data['LpuUnit_id']) ) {
			$filterList[] = 'LS.LpuUnit_id = :LpuUnit_id';
			$queryParams['LpuUnit_id'] = $data['LpuUnit_id'];
		}

		if ( !empty($data['date']) ) {
			$filterList[] = '(cast(:date as date) between cast(LSBP.LpuSectionBedProfile_begDT as date) and cast(coalesce(LSBP.LpuSectionBedProfile_endDT,:date) as date))';
			$queryParams['date'] = $data['date'];
		}

		if( !empty($data['Is_Child']) ){
			$filterList[] = 'coalesce(LSBP.LpuSectionBedProfile_IsChild,1) = :Is_Child';
			$queryParams['Is_Child'] = $data['Is_Child'];
		}

		$query = "
			select
				LSBPL.LpuSectionBedProfileLink_id as \"LpuSectionBedProfileLink_id\",
				LSBP.LpuSectionBedProfile_Code as \"LpuSectionBedProfile_Code\",
				RTRIM(coalesce(LSBP.LpuSectionBedProfile_Name, '')) as \"LpuSectionBedProfile_Name\",
				LSBPF.LpuSectionBedProfile_id as \"LpuSectionBedProfile_fedid\",
				LSBPF.LpuSectionBedProfile_Code as \"LpuSectionBedProfile_fedCode\",
				LSBPF.LpuSectionBedProfile_Name as \"LpuSectionBedProfile_fedName\",
				to_char(LSBP.LpuSectionBedProfile_begDT, 'DD.MM.YYYY') as \"LpuSectionBedProfile_begDT\",
				to_char(LSBP.LpuSectionBedProfile_endDT, 'DD.MM.YYYY') as \"LpuSectionBedProfile_endDT\"
			from	fed.v_LpuSectionBedProfileLink LSBPL
				left join v_LpuSectionBedProfile LSBP on LSBPL.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id
				left join fed.v_LpuSectionBedProfile LSBPF on LSBPF.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_fedid
				inner join v_LpuSectionBedState LSBS on LSBS.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id
				inner join v_LpuSection LS on LS.LpuSection_id = LSBS.LpuSection_id
				". implode('
				', $joinList) . "
			" . (count($filterList) > 0 ? "where 
				" . implode(" 
				and ", $filterList) : '') . "
		";

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');
			return $response;
		}
		else {
			return false;
		}
	}

	/**
	 * Получение списка подразделений по заданными фильтрами
	 * @param array $data Фильтры
	 * @return array Ассоциативный массив с данными подразделений
	 */
	function loadLpuUnitList($data = array()) {
		$fieldsList = array();
		$filterList = array();
		$joinList = array();
		$queryParams = array();

		$this->load->library('swCache');
		if ( $data['mode'] == 'all' && $data['Lpu_id'] > 0 ) {
			if ($resCache = $this->swcache->get("LpuUnitList_".$data['Lpu_id'])) {
				return $resCache;
			}
		}

		if ( array_key_exists('linkedLpuIdList', $data['session']) && $data['mode'] != 'combo' ) {
			$filterList[] = "LU.Lpu_id in (" . implode(',', $data['session']['linkedLpuIdList']) . ")";
		}
		else if ( !empty($data['Lpu_id']) ) {
			$filterList[] = "LU.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		if ( !empty($data['LpuBuilding_id']) ) {
			$filterList[] = "LU.LpuBuilding_id = :LpuBuilding_id";
			$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}

		switch ( $data['mode'] ) {
			case 'all':
				$fieldsList[] = 'L.Lpu_Nick as "Lpu_Name"';
				$joinList[] = 'inner join v_Lpu L  on L.Lpu_id = LU.Lpu_id';

				if ( !empty($data['Lpu_id']) ) {
					$fieldsList[] = 'case when LU.Lpu_id = :Lpu_id then 1 else 2 end as "sortID"';

					if ( empty($queryParams['Lpu_id']) ) {
						$queryParams['Lpu_id'] = $data['Lpu_id'];
					}
				}
			break;

			case 'dlo':
				$filterList[] = "LUT.LpuUnitType_id = 2";
			break;
		}

		$query = "
			SELECT
				LU.LpuUnit_id as \"LpuUnit_id\",
				LU.Lpu_id as \"Lpu_id\",
				COALESCE(LU.LpuBuilding_id, 0) as \"LpuBuilding_id\",
				COALESCE(LUT.LpuUnitType_id, 0) as \"LpuUnitType_id\",
				RTRIM(LU.LpuUnit_Code) as \"LpuUnit_Code\",
				RTRIM(LU.LpuUnit_Name) as \"LpuUnit_Name\",
				COALESCE(LU.LpuUnit_IsEnabled, 2) as \"LpuUnit_IsEnabled\"
				" . (count($fieldsList) > 0 ? ',' . implode(',', $fieldsList) : '' ) . "
			FROM v_LpuUnit LU 
				left join LpuUnitType LUT  on LUT.LpuUnitType_id = LU.LpuUnitType_id
				" . implode(' ', $joinList) . "
			" . (count($filterList) > 0 ? 'WHERE ' . implode(' and ', $filterList) : '') . "
			ORDER by LU.LpuUnit_Code, LU.LpuUnit_Name
		";
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');
			if ( $data['mode'] == 'all' && $data['Lpu_id'] > 0 ) {
				$this->swcache->set("LpuUnitList_".$data['Lpu_id'], $response);
			}
			return $response;
		}
		else {
			return false;
		}
	}

	/**
	 * Получение списка контрактов
	 * @param array $data
	 * @return array Ассоциативный массив со списком контрактов
	 */
	function loadLpuDispContractList($data = array()) {
		$fieldsList = array();
		$filterList = array();
		$joinList = array();
		$queryParams = array('Lpu_id' => $data['Lpu_id']);

		$this->load->library('swCache');

        //Добавление отмены подкачки кеша для тестирования
        $ignore_cache = (isset($_REQUEST['ignore_cache'])) ? $_REQUEST['ignore_cache'] : (isset($_GET['ignore_cache']) ? $_GET['ignore_cache'] : false);

        if ( !$ignore_cache ){
            if ($resCache = $this->swcache->get("LpuDispContract_".$data['Lpu_id'])) {
                //$this->textlog->add('ignore_cache - is false');
                return $resCache;
            }
        } else {
            //$this->textlog->add('ignore_cache - is true');
        }
        /*if ($resCache = $this->swcache->get("LpuDispContract_".$data['Lpu_id'])) {
            return $resCache;
        }*/

		$query = "
			select 
				ldc.LpuDispContract_id as \"LpuDispContract_id\",
				ldc.Lpu_oid as \"Lpu_oid\",
				to_char(ldc.LpuDispContract_setDate, 'DD.MM.YYYY') as \"LpuDispContract_setDate\",
				to_char(ldc.LpuDispContract_disDate, 'DD.MM.YYYY') as \"LpuDispContract_disDate\",
				ldc.LpuSection_id as \"LpuSection_id\",
				ldc.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				lsp.LpuSectionProfile_Code as \"LpuSectionProfile_Code\"
			from v_LpuDispContract ldc 
			left join v_LpuSectionProfile lsp  on lsp.LpuSectionProfile_id = ldc.LpuSectionProfile_id
			where ldc.Lpu_id = :Lpu_id
		";
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');
			$this->swcache->set("LpuDispContract_".$data['Lpu_id'], $response);
			return $response;
		}
		else {
			return false;
		}
	}


	/**
	 * Получение истории лечения человека по заданными фильтрами
	 * теперь показывает все случаи лечения, независимо от ЛПУ
	 * @param array $data Фильтры
	 * @return array Ассоциативный массив событиями человека
	 */
	function loadPersonCureHistoryList($data) {

		$queryParams = array('Lpu_id' => $data['Lpu_id'], 'Person_id' => $data['Person_id']);
		$filterList = array();
		$filterpriv = '';
		$filter = ' and (1=1) ';
		if ( !empty($data['filterData']) ) {
			$json = $data['filterData'];
			ConvertFromWin1251ToUTF8($json);
			$items = json_decode($json, true);
			array_walk($items, 'ConvertFromUTF8ToWin1251');

			if ( !empty($items['EvnClass_Name']) ) {
				$filterList[] = "ec.EvnClass_Name = :EvnClass_Name";
				$queryParams['EvnClass_Name'] = $items['EvnClass_Name'];
			}

			if ( !empty($items['LpuSection_Name']) ) {
				$filterList[] = "ls.LpuSection_Name = :LpuSection_Name";
				$queryParams['LpuSection_Name'] = $items['LpuSection_Name'];
			}

			if ( !empty($items['MedPersonal_Fio']) ) {
				$filterList[] = "mp.Person_Fio = :MedPersonal_Fio";
				$queryParams['MedPersonal_Fio'] = $items['MedPersonal_Fio'];
			}

			if ( !empty($items['Diag_Name']) ) {
				$filterList[] = "COALESCE(NULLIF(RTRIM(d.Diag_Code) || '. ', '. '), '') || RTRIM(COALESCE(d.Diag_Name, '')) = :Diag_Name";
				$queryParams['Diag_Name'] = $items['Diag_Name'];
			}

			if ( !empty($items['Evn_setDate']) ) {
				$filterList[] = "to_char(e.Evn_setDT, 'YYYY-MM-DD') = :Evn_setDate";
				$queryParams['Evn_setDate'] = date('Y-m-d', strtotime($items['Evn_setDate']));
			}

			if ( !empty($items['Evn_disDate']) ) {
				$filterList[] = "to_char(e.Evn_disDT, 'YYYY-MM-DD') = :Evn_disDate";
				$queryParams['Evn_disDate'] = date('Y-m-d', strtotime($items['Evn_disDate']));
			}
		}

		$privilegeFilter = getAccessRightsPrivilegeTypeFilter("t1.PrivilegeType_id");
		if (!empty($privilegeFilter)) {
			$filter .= " and $privilegeFilter ";
		}

		$query = "";
		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$query .= "
				, case when COALESCE(e.Evn_IsArchive, 1) = 1 then 0 else 1 end as \"archiveRecord\"
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filterList[] = "COALESCE(e.Evn_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$filterList[] = "COALESCE(e.Evn_IsArchive, 1) = 2";
			}
		}

		$filterList = array_merge($filterList, getAccessRightsDiagFilter('d.Diag_Code', true));
		$filterList = array_merge($filterList, getAccessRightsLpuFilter('e.Lpu_id', true));
		$filterList = array_merge($filterList, getAccessRightsLpuBuildingFilter('ls.LpuBuilding_id', true));

		if ($this->getRegionNick() == 'ufa') {
			$lpuFilter = getAccessRightsLpuFilter("Lpu_id");
			$lpuFilter = !empty($lpuFilter) ? " and {$lpuFilter}" : '';
			$filterpriv .= " and exists (select PersonPrivilege_id from v_PersonPrivilege  where PrivilegeType_id = t1.PrivilegeType_id and Person_id = t1.Person_id {$lpuFilter} limit 1)";
		}

		if ( in_array($this->getRegionNick(), array('ekb', 'perm')) ) {
			$smpCardsQuery = "
				select
					ec.EvnClass_SysNick || '_' || cast(CCC.CmpCallCard_id as varchar(20)) as Evn_id
					,null as Evn_pid
					,CCC.Person_id
					,null as PersonEvn_id
					,null as Server_id
					,ec.EvnClass_SysNick
					,ec.EvnClass_Name
					,null as LpuSection_id
					,COALESCE(MSF.MedPersonal_id, CCC.MedPersonal_id) as MedPersonal_id
					,RTRIM(COALESCE(CCC.CmpCallCard_Vr51,'')) as MedPersonal_Fio
					,CCC.Diag_uid as Diag_id
					,CCC.CmpCallCard_prmDT as Evn_setDT
					,CCC.CmpCallCard_Przd as Evn_disDT
					,CCC.CmpCallCard_insDT as Evn_insDT
					,CCC.Lpu_id as Lpu_id
					,CCC.CmpCallCard_IsArchive as Evn_IsArchive
				from
					v_CmpCallCard CCC 
					inner join v_EvnClass ec  on ec.EvnClass_id = 111
					left join v_MedStaffFact msf  on msf.MedStaffFact_id = CCC.MedStaffFact_id
				where
					CCC.Person_id = :Person_id
					and CCC.Lpu_id = :Lpu_id
			";
		}
		else {
			$viewProcedure = "v_CmpCloseCard";
			$feldsherField = 'CCCl.Feldsher_id';
			if ($this->getRegionNick() == 'ufa') {
				$viewProcedure = "r2.v_CmpCloseCard";
			}
			if ($this->getRegionNick() == 'kz') {
				$viewProcedure = "r101.v_CmpCloseCard";
				$feldsherField = 'CCCl.Feldsher';
			}
			$smpCardsQuery = "
				select
					ec.EvnClass_SysNick || '_' || cast(CCC.CmpCallCard_id as varchar(20)) as Evn_id
					,null as Evn_pid
					,CCC.Person_id
					,null as PersonEvn_id
					,null as Server_id
					,ec.EvnClass_SysNick
					,ec.EvnClass_Name
					,null as LpuSection_id
					,COALESCE(MSF.MedPersonal_id, CCC.MedPersonal_id) as MedPersonal_id
					,RTRIM(COALESCE(CCC.CmpCallCard_Vr51, {$feldsherField}, '')) as MedPersonal_Fio
					,COALESCE(CCC.Diag_uid, CCCl.Diag_id) as Diag_id
					,CCC.CmpCallCard_prmDT as Evn_setDT
					,CCC.CmpCallCard_Przd as Evn_disDT
					,CCC.CmpCallCard_insDT as Evn_insDT
					,CCC.Lpu_id as Lpu_id
					,CCC.CmpCallCard_IsArchive as Evn_IsArchive
				from
					{$viewProcedure} CCCl 
					inner join v_CmpCallCard CCC  on CCC.CmpCallCard_id = CCCl.CmpCallCard_id
					inner join v_EvnClass ec  on ec.EvnClass_id = 111
					left join v_MedStaffFact msf  on msf.MedStaffFact_id = CCC.MedStaffFact_id
				where
					CCC.Person_id = :Person_id
					and CCC.Lpu_id = :Lpu_id
			";
		}

		$query = "
			select
				 e.Evn_id as \"Evn_id\"
				,e.Evn_pid as \"Evn_pid\"
				,e.Person_id as \"Person_id\"
				,e.PersonEvn_id as \"PersonEvn_id\"
				,e.Server_id as \"Server_id\"
				,e.EvnClass_SysNick as \"EvnClass_SysNick\"
				,e.EvnClass_Name as \"EvnClass_Name\"
				,RTRIM(COALESCE(ls.LpuSection_Name, '')) as \"LpuSection_Name\"
				,RTRIM(COALESCE(mp.Person_Fio, e.MedPersonal_Fio, '')) as \"MedPersonal_Fio\"
				,COALESCE(NULLIF(RTRIM(d.Diag_Code) || '. ', '. '), '') || RTRIM(COALESCE(d.Diag_Name, '')) as \"Diag_Name\"
				,to_char(e.Evn_setDT, 'DD.MM.YYYY') as \"Evn_setDate\"
				,to_char(e.Evn_disDT, 'DD.MM.YYYY') as \"Evn_disDate\"
				{$query}
			from (
				select
					ec.EvnClass_SysNick || '_' || cast(t1.EvnRecept_id as varchar(20)) as Evn_id
					,t1.EvnRecept_pid as Evn_pid
					,t1.Person_id
					,t1.PersonEvn_id
					,t1.Server_id
					,ec.EvnClass_SysNick
					,ec.EvnClass_Name
					,t1.LpuSection_id
					,t1.MedPersonal_id
					,null as MedPersonal_Fio
					,t1.Diag_id
					,t1.EvnRecept_setDT as Evn_setDT
					,t1.EvnRecept_disDT as Evn_disDT
					,t1.EvnRecept_insDT as Evn_insDT
					,t1.Lpu_id
					,t1.EvnRecept_IsArchive as Evn_IsArchive
				from v_EvnRecept t1 
					inner join EvnClass ec  on ec.EvnClass_id = t1.EvnClass_id
				where t1.Person_id = :Person_id
					and t1.Lpu_id = :Lpu_id
					{$filter}
					{$filterpriv}

				union all

				select
					ec.EvnClass_SysNick || '_' || cast(epl.EvnPL_id as varchar(20)) as Evn_id
					,epl.EvnPL_pid as Evn_pid
					,epl.Person_id
					,epl.PersonEvn_id
					,epl.Server_id
					,ec.EvnClass_SysNick
					,ec.EvnClass_Name
					,evpl.LpuSection_id
					,evpl.MedPersonal_id
					,null as MedPersonal_Fio
					,evpl.Diag_id
					,epl.EvnPL_setDT as Evn_setDT
					,epl.EvnPL_disDT as Evn_disDT
					,epl.EvnPL_insDT as Evn_insDT
					,epl.Lpu_id
					,epl.EvnPL_IsArchive as Evn_IsArchive
				from v_EvnPL epl 
					inner join EvnClass ec  on ec.EvnClass_id = epl.EvnClass_id
					left join v_EvnVizitPL evpl  on evpl.EvnVizitPL_pid = epl.EvnPL_id
						and evpl.EvnVizitPL_Index = evpl.EvnVizitPL_Count - 1
				where (1 = 1)
					and epl.Person_id = :Person_id
					and epl.Lpu_id = :Lpu_id
					and epl.EvnClass_id in (3, 6)

				union all

				select
					ec.EvnClass_SysNick || '_' || cast(eps.EvnPS_id as varchar(20)) as Evn_id
					,eps.EvnPS_pid as Evn_pid
					,eps.Person_id
					,eps.PersonEvn_id
					,eps.Server_id
					,ec.EvnClass_SysNick
					,ec.EvnClass_Name
					,es.LpuSection_id
					,es.MedPersonal_id
					,null as MedPersonal_Fio
					,es.Diag_id
					,eps.EvnPS_setDT as Evn_setDT
					,eps.EvnPS_disDT as Evn_disDT
					,eps.EvnPS_insDT as Evn_insDT
					,eps.Lpu_id
					,eps.EvnPS_IsArchive as Evn_IsArchive
				from v_EvnPS eps 
					inner join EvnClass ec  on ec.EvnClass_id = eps.EvnClass_id
					left join v_EvnSection es  on es.EvnSection_pid = eps.EvnPS_id
						and es.EvnSection_Index = es.EvnSection_Count - 1
				where (1 = 1)
					and eps.Person_id = :Person_id
					and eps.Lpu_id = :Lpu_id

				union all

				" . $smpCardsQuery . "
				) as e
				LEFT JOIN LATERAL (
					select Person_Fio
					from v_MedPersonal 
					where MedPersonal_id = e.MedPersonal_id
					limit 1
				) MP on true
				left join v_LpuSection ls  on ls.LpuSection_id = e.LpuSection_id
				left join v_Diag d  on d.Diag_id = e.Diag_id
			" . (count($filterList) > 0 ? "where " . implode(' and ', $filterList) : '') . "

				order by
					 e.Evn_setDT desc
					,e.Evn_disDT desc

		";

		//echo getDebugSQL($query, $queryParams);die;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение данных по заданному человеку, прикрепленному к заданному серверу
	 * @param array $data Фильтры
	 * @return array Ассоциативный массив с полями человека
	 * Используется для
	 * печати заявления на прикрепление Buryatiya_PersonCard::printPersonCardAttach
	 */
	function loadPersonDataForPrintPersonCard($data) {
		// Если передали PersonEvn_id, значит определенная периодика нужна и читать будем из периодики
		$object = "v_PersonState";
		$filter = " (1=1)";
		$params =  array('Person_id' => $data['Person_id']);
		$InnField = "COALESCE(ps.Person_Inn, '') as \"Person_Inn\"";
		if ((isset($data['PersonEvn_id'])) && ($data['PersonEvn_id']>0))
		{
			$object = "v_Person_bdz";
			$params['Server_id'] = $data['Server_id'];
			$filter .= " and PS.Server_id = :Server_id";
			$params['PersonEvn_id'] = $data['PersonEvn_id'];
			$filter .= " and PS.PersonEvn_id = :PersonEvn_id";
			$InnField = "COALESCE(ps.PersonInn_Inn, '') as \"Person_Inn\"";
		}
		else
		{
			$params['Person_id'] = $data['Person_id'];
			$filter .= " and PS.Person_id = :Person_id";
			$InnField = "COALESCE(ps.Person_Inn, '') as \"Person_Inn\"";
		}
		$extendFrom = "";
		$extendSelect = "";
		if ((isset($data['EvnDirection_id'])) && (!empty($data['EvnDirection_id']))) {
			$params['EvnDirection_id'] = $data['EvnDirection_id'];
			$extendSelect = "
				,ED.EvnDirection_id as \"EvnDirection_id\"
				,ED.EvnDirection_Num as \"EvnDirection_Num\"
				,ED.EvnDirection_setDT as \"EvnDirection_setDT\"
			";
			$extendFrom .= "
				LEFT JOIN LATERAL
				(SELECT 
					ED.EvnDirection_id,
					ED.EvnDirection_Num,
					COALESCE(to_char(ED.EvnDirection_setDT, 'DD.MM.YYYY'), '') as EvnDirection_setDT
                    LIMIT 1
				FROM
					v_EvnDirection_all ED 
				WHERE
					ED.EvnDirection_id = :EvnDirection_id
				) as ED on true
            ";
		}

		$params['curdate'] = $this->getFirstResultFromQuery("select dbo.tzGetDate()");

		$query = "
			SELECT
				ps.Person_id as \"Person_id\",
				{$InnField},
				dbo.getPersonPhones(ps.Person_id, ',') as \"Person_Phone\",
				ps.Server_id as \"Server_id\",
				ps.Server_pid as \"Server_pid\",
				CASE WHEN (pcard.PersonCard_endDate IS NOT NULL) THEN COALESCE(RTRIM(lpu.Lpu_Nick), '') || ' (Прикрепление неактуально. Дата открепления: '||COALESCE(to_char(pcard.PersonCard_endDate, 'DD.MM.YYYY'), '')||')' ELSE COALESCE(RTRIM(lpu.Lpu_Nick), '') end as \"Lpu_Nick\",
				PersonState.Lpu_id as \"Lpu_id\",
				pcard.PersonCard_id as \"PersonCard_id\",
				COALESCE(RTRIM(PS.Person_SurName), '') as \"Person_Surname\",
				COALESCE(RTRIM(PS.Person_FirName), '') as \"Person_Firname\",
				COALESCE(RTRIM(PS.Person_SecName), '') as \"Person_Secname\",
				PS.PersonEvn_id as \"PersonEvn_id\",
				COALESCE(to_char(PS.Person_BirthDay, 'DD.MM.YYYY'), '') as \"Person_Birthday\",
				date_part('year',age(now(), PS.Person_Birthday)) as \"Person_Age\",
				case when (KLArea.KLSocr_id = 68) or (KLArea.KLSocr_id = 56) then '1' else '0' end as \"KLAreaType_id\",
				COALESCE(RTRIM(PS.Person_Snils), '') as \"Person_Snils\",
				COALESCE(RTRIM(Sex.Sex_Name), '') as \"Sex_Name\",
				COALESCE(RTRIM(Sex.Sex_Code::varchar), '') as \"Sex_Code\",
				COALESCE(RTRIM(Sex.Sex_id::varchar), '') as \"Sex_id\",
				COALESCE(RTRIM(SocStatus.SocStatus_Name), '') as \"SocStatus_Name\",
				ps.SocStatus_id as \"SocStatus_id\",
				COALESCE(RTRIM(COALESCE(UAddress.Address_Nick, UAddress.Address_Address)),'') as \"Person_RAddress\",

                --Кусок для печати заявления о выборе МО для Бурятии https://redmine.swan.perm.ru/issues/47331
                PersonState.PersonPhone_Phone as \"Phone\",
                COALESCE(BAddress.Address_Address, '') as \"Person_BAddress\",
				COALESCE(KLCountry.KLCountry_Name, 'Лицо без гражданства') as \"Nationality\",
                COALESCE(URgn.KLArea_Name, '') || ' ' || COALESCE(URgnSocr.KLSocr_Nick, '') as \"URgn_Name\",
                COALESCE(USubRgn.KLArea_Name, '') as \"USubRgn_Name\",
                COALESCE(UCity.KLArea_Name, '') as \"UCity_Name\",
                COALESCE(UTown.KLArea_Name, '') as \"UTown_Name\",
                COALESCE(UStreet.KLStreet_Name, '') as \"UStreet_Name\",
                COALESCE(UAddress.Address_House, '') as \"UAddress_House\",
                COALESCE(UAddress.Address_Corpus, '') as \"UAddress_Corpus\",
                COALESCE(UAddress.Address_Flat, '') as \"UAddress_Flat\",
                COALESCE(PSubRgn.KLArea_Name, '') as \"PSubRgn_Name\",
                COALESCE(PCity.KLArea_Name, '') as \"PCity_Name\",
                COALESCE(PTown.KLArea_Name, '') as \"PTown_Name\",
                COALESCE(PStreet.KLStreet_Name, '') as \"PStreet_Name\",
                COALESCE(PAddress.Address_House, '') as \"PAddress_House\",
                COALESCE(PAddress.Address_Corpus, '') as \"PAddress_Corpus\",
                COALESCE(PAddress.Address_Flat, '') as \"PAddress_Flat\",
                COALESCE(RTRIM(PSDep.Person_SurName), '') as \"DPerson_Surname\",
				COALESCE(RTRIM(PSDep.Person_FirName), '') as \"DPerson_Firname\",
				COALESCE(RTRIM(PSDep.Person_SecName), '') as \"DPerson_Secname\",
                COALESCE(DK.DeputyKind_Name, '') as \"DeputyKind_Name\",
                COALESCE(DT.DocumentType_Name, '') as \"DocumentType_Name\",
				COALESCE(RTRIM(DDocument.Document_Num), '') as \"DDocument_Num\",
				COALESCE(RTRIM(DDocument.Document_Ser), '') as \"DDocument_Ser\",
				COALESCE(DDT.DocumentType_Name, '') as \"DDocumentType_Name\",
				COALESCE(to_char(DDocument.Document_begDate, 'DD.MM.YYYY'), '') as \"DDocument_begDate\",
				COALESCE(RTRIM(DDO.Org_Name), '') as \"DOrgDep_Name\",
				--Конец куска для печати для Бурятиии

				COALESCE(RTRIM(COALESCE(PAddress.Address_Nick, PAddress.Address_Address)),'') as \"Person_PAddress\",
				COALESCE(RTRIM(Document.Document_Num), '') as \"Document_Num\",
				COALESCE(RTRIM(Document.Document_Ser), '') as \"Document_Ser\",
				COALESCE(to_char(Document.Document_begDate, 'DD.MM.YYYY'), '') as \"Document_begDate\",
				COALESCE(RTRIM(DORG.Org_Name), '') as \"OrgDep_Name\",
				NS.KLCountry_id as \"KLCountry_id\",
				case when NS.NationalityStatus_IsTwoNation = 2 then 1 else 0 end as \"NationalityStatus_IsTwoNation\",
				COALESCE(OmsSprTerr.OmsSprTerr_id, 0) as \"OmsSprTerr_id\",
				COALESCE(OmsSprTerr.OmsSprTerr_Code, 0) as \"OmsSprTerr_Code\",
				CASE WHEN PolisType.PolisType_Code = 4 then '' ELSE COALESCE(RTRIM(Polis.Polis_Ser), '') END as \"Polis_Ser\",
				CASE WHEN PolisType.PolisType_Code = 4 then COALESCE(RTRIM(ps.Person_EdNum), '') ELSE COALESCE(RTRIM(Polis.Polis_Num), '') END as \"Polis_Num\",
				COALESCE(to_char(pcard.PersonCard_begDate, 'DD.MM.YYYY'), '') as \"PersonCard_begDate\",
				COALESCE(to_char(pcard.PersonCard_endDate, 'DD.MM.YYYY'), '') as \"PersonCard_endDate\",
				COALESCE(pcard.LpuRegion_Name, '') as \"LpuRegion_Name\",
				COALESCE(to_char(Polis.Polis_begDate, 'DD.MM.YYYY'), '') as \"Polis_begDate\",
				COALESCE(to_char(Polis.Polis_endDate, 'DD.MM.YYYY'), '') as \"Polis_endDate\",
				COALESCE(RTRIM(PO.Org_Name), '') as \"OrgSmo_Name\",
				COALESCE(RTRIM(PJ.Org_id::varchar), '') as \"JobOrg_id\",
				COALESCE(RTRIM(PJ.Org_Name), '') as \"Person_Job\",
				COALESCE(RTRIM(PP.Post_Name), '') as \"Person_Post\",
				'' as \"Ident_Lpu\",
				CASE WHEN PR.PersonRefuse_IsRefuse = 2
					THEN 'true' ELSE 'false' END as \"Person_IsRefuse\",
				/* -- в v_Person_all (reg) нет этих полей, надо Тарасу сказать чтобы добавил
				COALESCE(to_char(ps.Person_deadDT, 'DD.MM.YYYY'), '') as \"Person_deadDT\",
				COALESCE(to_char(ps.Person_closeDT, 'DD.MM.YYYY'), '') as \"Person_closeDT\",
				ps.Person_IsDead as \"Person_IsDead\",
				ps.PersonCloseCause_id as \"PersonCloseCause_id\"
				*/
				CASE WHEN PS.Server_pid = 0 THEN 1 ELSE 0 END as \"Person_IsBDZ\",
				CASE WHEN PersonPrivilegeFed.Person_id IS NOT NULL THEN 1 ELSE 0 END as \"Person_IsFedLgot\",
				COALESCE(to_char(Person.Person_deadDT, 'DD.MM.YYYY'), '') as \"Person_deadDT\",
				COALESCE(to_char(Person.Person_closeDT, 'DD.MM.YYYY'), '') as \"Person_closeDT\",
				Person.Person_IsDead as \"Person_IsDead\",
				Person.PersonCloseCause_id as \"PersonCloseCause_id\",
				0 as \"Children_Count\"
				,PersonPrivilegeFed.PrivilegeType_id as \"PrivilegeType_id\"
				,PersonPrivilegeFed.PrivilegeType_Name as \"PrivilegeType_Name\"
				{$extendSelect}
			FROM {$object} PS 
				left join Sex  on Sex.Sex_id = PS.Sex_id
				left join SocStatus  on SocStatus.SocStatus_id = PS.SocStatus_id
				left join Address UAddress  on UAddress.Address_id = PS.UAddress_id

				left join v_KLArea URgn  on URgn.KLArea_id = UAddress.KLRgn_id
				left join v_KLSocr URgnSocr  on URgnSocr.KLSocr_id = URgn.KLSocr_id
                left join v_KLArea USubRgn  on USubRgn.KLArea_id = UAddress.KLSubRgn_id
                left join v_KLArea UCity  on UCity.KLArea_id = UAddress.KLCity_id
                left join v_KLArea UTown  on UTown.KLArea_id = UAddress.KLTown_id
                left join v_KLStreet UStreet  on UStreet.KLStreet_id = UAddress.KLStreet_id


				left join v_KLArea KLArea  on KLArea.KLArea_id = UAddress.KLTown_id
				left join Address PAddress  on PAddress.Address_id = PS.PAddress_id
				left join v_KLArea PSubRgn  on PSubRgn.KLArea_id = PAddress.KLSubRgn_id
                left join v_KLArea PCity  on PCity.KLArea_id = PAddress.KLCity_id
                left join v_KLArea PTown  on PTown.KLArea_id = PAddress.KLTown_id
                left join v_KLStreet PStreet  on PStreet.KLStreet_id = PAddress.KLStreet_id

                left join v_PersonDeputy PDep  on PDep.Person_id = PS.Person_id
                left join v_DeputyKind DK  on DK.DeputyKind_id = PDep.DeputyKind_id
                left join v_PersonState PSDep  on PSDep.Person_id = PDep.Person_pid
                left join Document DDocument  on DDocument.Document_id = PSDep.Document_id
                left join v_DocumentType DDT  on DDT.DocumentType_id = DDocument.DocumentType_id
                left join NationalityStatus NS  on NS.NationalityStatus_id = PSDep.NationalityStatus_id
                left join OrgDep DOrgDep  on DOrgDep.OrgDep_id = DDocument.OrgDep_id
                left join Org DDO  on DDO.Org_id = DOrgDep.Org_id

				left join PersonBirthPlace PersonBirthPlace  on PersonBirthPlace.Person_id = PS.Person_id
				left join PersonInfo PersonInfo  on PersonInfo.Person_id = PS.Person_id
				left join v_Nationality Nationality  on Nationality.Nationality_id = PersonInfo.Nationality_id
				left join Address BAddress  on BAddress.Address_id = PersonBirthPlace.Address_id
				left join v_Job Job  on Job.Job_id = PS.Job_id
				left join Org PJ  on PJ.Org_id = Job.Org_id
				left join Post PP  on PP.Post_id = Job.Post_id
				left join Document  on Document.Document_id = PS.Document_id
				left join v_DocumentType DT  on DT.DocumentType_id = Document.DocumentType_id
				left join KLCountry  on KLCountry.KLCountry_id = PAddress.KLCountry_id
				left join OrgDep  on OrgDep.OrgDep_id = Document.OrgDep_id

				left join Org DORG  on DORG.Org_id = OrgDep.Org_id
				left join Polis  on Polis.Polis_id = PS.Polis_id
				left join PolisType  on PolisType.PolisType_id = Polis.PolisType_id
				left join OmsSprTerr  on OmsSprTerr.OmsSprTerr_id = Polis.OmsSprTerr_id
				left join OrgSmo  on OrgSmo.OrgSmo_id = Polis.OrgSmo_id
				left join Org PO  on PO.Org_id = OrgSmo.Org_id
				left join Person  on Person.Person_id = PS.Person_id
				left join PersonState  on PS.Person_id = PersonState.Person_id
				LEFT JOIN LATERAL
				(SELECT
					PP.Person_id
					,PP.PrivilegeType_id
					,PT.PrivilegeType_Name
				FROM
					v_PersonPrivilege PP 
					inner join v_PrivilegeType PT  on PT.PrivilegeType_id = PP.PrivilegeType_id
				WHERE
					PT.ReceptFinance_id = 1 and
					PP.PersonPrivilege_begDate <= CAST(:curdate  as date) AND
					(PP.PersonPrivilege_endDate IS NULL OR
					PP.PersonPrivilege_endDate >= cast(:curdate AS date)) AND
					PP.Person_id = PS.Person_id
				LIMIT 1
				) PersonPrivilegeFed on true
				LEFT JOIN LATERAL (select
						pc.Person_id as PersonCard_Person_id,
						pc.Lpu_id,
						pc.PersonCard_id,
						pc.PersonCard_begDate,
						pc.PersonCard_endDate,
						pc.LpuRegion_Name
					from v_PersonCard pc 
					where pc.Person_id = ps.Person_id and LpuAttachType_id = 1
					order by PersonCard_begDate desc
					limit 1
					) as pcard on true
				left join v_Lpu lpu  on lpu.Lpu_id=PersonState.Lpu_id
				LEFT JOIN v_PersonRefuse PR  ON PR.Person_id = ps.Person_id and PR.PersonRefuse_IsRefuse = 2 and PR.PersonRefuse_Year = date_part('year', cast(:curdate as date))
				{$extendFrom}
			WHERE {$filter}
			
		";


		if ((isset($data['PersonEvn_id'])) && ($data['PersonEvn_id']>0)) {
			$query .= " order by PersonEvn_updDT desc";
		}
		$query .= " LIMIT 1";
		//echo getDebugSQL($query, $params); exit;
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение персданных для печати заявления о прикреплении на Казахстане
	 */
	function loadPersonDataForAttachKz($data){
		$params = array();// array('Person_id' => $data['Person_id']);
		$params['Person_id'] = $data['Person_id'];
		if(isset($data['PCPDW_Deputy_id'])){
			$params['Deputy_id'] = $data['PCPDW_Deputy_id'];
		}
		$query = "
 		SELECT
				COALESCE(RTRIM(PS.Person_SurName), '') as \"Person_Surname\",
				COALESCE(RTRIM(PS.Person_FirName), '') as \"Person_Firname\",
				COALESCE(RTRIM(PS.Person_SecName), '') as \"Person_Secname\",
				COALESCE(to_char(PS.Person_BirthDay, 'DD.MM.YYYY'), '') as \"Person_Birthday\",
				COALESCE(ps.Person_Inn, '') as \"Person_Inn\",
				COALESCE(RTRIM(COALESCE(PAddress.Address_Nick, PAddress.Address_Address)),'') as \"Person_PAddress\",
				COALESCE(RTRIM(Document.Document_Num), '') as \"Document_Num\",
				COALESCE(RTRIM(Document.Document_Ser), '') as \"Document_Ser\",
				COALESCE(to_char(Document.Document_begDate, 'DD.MM.YYYY'), '') as \"Document_begDate\",
				COALESCE(RTRIM(DORG.Org_Name), '') as \"OrgDep_Name\",
				COALESCE(RTRIM(LAN.Lpu_Nick), '') as \"LpuAttach_Name\",
				COALESCE(RTRIM(LAN.PersonCard_begDate), '') as \"PersonCard_begDate\"

		FROM v_PersonState PS 
		left join Address PAddress  on PAddress.Address_id = PS.PAddress_id
		left join Document  on Document.Document_id = PS.Document_id
		left join OrgDep  on OrgDep.OrgDep_id = Document.OrgDep_id
		left join Org DORG  on DORG.Org_id = OrgDep.Org_id
		LEFT JOIN LATERAL (
			select
				O.Org_Nick as Lpu_Nick,
				COALESCE(to_char(PC.PersonCard_begDate, 'DD.MM.YYYY'), '') as PersonCard_begDate
			from
				v_PersonCard PC 
				left join Lpu L  on PC.Lpu_id = L.Lpu_id
				left join Org O  on O.Org_id = L.Org_id
			where
				Person_id = PS.Person_id
				and PC.PersonCard_endDate is null
			order by
				PC.PersonCard_begDate desc
			limit 1
		) LAN on true
		WHERE PS.Person_id = :Person_id
		";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * получим периодику для мобильного
	 */
	function loadPersonDataForApi($data) {

		$object = "v_PersonState";
		$filter = " (1=1)";

		$params =  array('Person_id' => $data['Person_id']);

		// Если передали PersonEvn_id, значит определенная периодика нужна и читать будем из периодики
		if (!empty($data['PersonEvn_id']))	{
			$object = "v_Person_bdz";
			$params['Server_id'] = $data['Server_id'];
			$filter .= " and PS.Server_id = :Server_id";
			$params['PersonEvn_id'] = $data['PersonEvn_id'];
			$filter .= " and PS.PersonEvn_id = :PersonEvn_id";
		} else {
			$params['Person_id'] = $data['Person_id'];
			$filter .= " and PS.Person_id = :Person_id";
		}

		$query = "
			SELECT
				ps.Person_id as \"Person_id\",
				PS.PersonEvn_id as \"PersonEvn_id\",
				ps.Server_id as \"Server_id\",
				ps.Server_pid as \"Server_pid\",
				COALESCE(to_char(PS.Person_BirthDay, 'DD.MM.YYYY'), '') as \"Person_Birthday\"
			FROM
				{$object} PS 
			WHERE
				{$filter}
			LIMIT 1
		";

		//echo getDebugSQL($query, $params); die;
		$result = $this->queryResult($query, $params);

		if (!empty($result)) return $result;
		else return false;
	}

    /**
     * получим периодику для мобильного
     */
    function mGetMainEvnDataByEvnId($data) {

        $result = $this->getFirstRowFromQuery("
				select
					e.Evn_pid as \"Evn_pid\",
					e.Evn_setDT as \"Evn_setDT\",
					ps.Person_id as \"Person_id\",
					PS.PersonEvn_id as \"PersonEvn_id\",
					ps.Server_id as \"Server_id\",
					ps.Server_pid as \"Server_pid\",
					COALESCE(to_char(PS.Person_BirthDay, 'DD.MM.YYYY'), '') as \"Person_Birthday\"
				from v_Evn e
				left join v_PersonState ps on ps.Person_id = e.Person_id
				where e.Evn_id = :Evn_id
                limit 1
			", array('Evn_id' => $data['Evn_id'])
        );

        return $result;
    }

	/**
	 * Новый метод для получения данных по заданному человеку (сборка из loadPersonData и loadPersonDataShort).
	 * В метод нужно передавать набор желаемых для получения полей, чтобы не грузить сервер лишними запросами.
	 */
	function loadPersonData($data, $mode = null, $additionalFields = array()) {
		// Если передали PersonEvn_id, значит определенная периодика нужна и читать будем из периодики
		$object = "v_PersonState";
		$filter = " (1=1)";
		$top = " LIMIT 1 ";
		$params =  array('Person_id' => $data['Person_id']);
		if ((isset($data['PersonEvn_id'])) && ($data['PersonEvn_id']>0))
		{
			$object = "v_Person_bdz";
			$params['Server_id'] = $data['Server_id'];
			$filter .= " and PS.Server_id = :Server_id";
			$params['PersonEvn_id'] = $data['PersonEvn_id'];
			$filter .= " and PS.PersonEvn_id = :PersonEvn_id";
		}
		else
		{
			$params['Person_id'] = $data['Person_id'];

			// для оффлайн режима
			if (!empty($data['person_in'])) {
				$top = "";
				$filter .= " and PS.Person_id in ({$data['person_in']}) ";
			} else {
				$filter .= " and PS.Person_id = :Person_id ";
			}
		}

		if (!empty($mode)) {
			$data['mode'] = $mode;
		}

		if (!empty($additionalFields)) {
			$data['additionalFields'] = $additionalFields;
		}

		$select = array();
		$join = array();
		$NeedFields = array(); // массив дополнительных полей для загрузки

		// здесь будем определять список полей необходимых
		if (!empty($data['mode'])) {
			switch ($data['mode']) {
				case 'AttachStatement':
					$NeedFields = array(
						'Person_RAddress',
						'Lpu_Nick',
						'Polis_begDate',
						'OrgSmo_Name',
						'Polis_Ser',
						'Polis_Num',
						'Person_PAddress',

						// @task https://redmine.swan-it.ru/issues/194242 - поля свидетельства о рождении
						'DocumentType_id',
						'DocumentType_Name',
						'Document_Num',
						'Document_Ser',

					);
					break;
				case 'AttachStatementKareliya':
					$NeedFields = array(
						'Lpu_Nick',
						'Sex_Name',
						'Person_BAddress',
						'Person_PAddress',
						'Person_RAddress',
						'DocumentType_id',
						'Document_Num',
						'Document_Ser',
						'Document_begDate',
						'OrgDep_Name',
						'Person_Phone',
						'Polis_Ser',
						'Polis_Num',
						'Person_Snils',
						'OrgSmo_Name'
					);
					break;
				case 'EmkPanel':
					$NeedFields = array(
                        'Person_Snils',
                        'Sex_Code',
                        'Sex_Name',
                        'SocStatus_Name',
                        'Person_RAddress',
                        'Person_PAddress',
                        'Person_Phone',
                        'Person_Inn',
                        'Polis_begDate',
                        'Polis_endDate',
                        'Polis_Num',
                        'Polis_Ser',
                        'OrgSmo_Name',
                        'Document_begDate',
                        'Document_Num',
                        'Document_Ser',

						// Семейное положение:
						'FamilyStatus_id',
						'FamilyStatus_Name',
                        
                        'OrgDep_Name',
                        'Person_Job',
                        'Person_Post',
                        'PersonCard_begDate',
                        'Lpu_Nick',
                        'LpuRegion_Name',
                        'Person_Age',
                        'Person_Birthday',
                        'Lpu_id',
                        'PersonChild_id',
                        'FeedingType_Name'
					);
					break;
				case 'PersonInformationPanelShort': // загрузка панелью PersonInformationPanelShort
					$NeedFields = array(
						'DocumentType_id',
						'Person_deadDT',
						'Person_closeDT',
						'Person_Snils',
						'Sex_Code',
						'OmsSprTerr_id',
						'OmsSprTerr_Code',
						'personAgeText',
						'Person_Job',
						'Person_IsAnonym'
					);
					break;
				case 'PersonInformationPanelShortWithDirection': // загрузка панелью PersonInformationPanelShortWithDirection
					$NeedFields = array(
						'Person_deadDT',
						'Person_closeDT',
						'Person_Snils',
						'Person_Inn',
						'Sex_Code',
						'OmsSprTerr_id',
						'OmsSprTerr_Code',
						'EvnDirection_Num',
						'EvnDirection_setDT',
						'EvnDirection_id'
					);
					break;
				case 'PersonInfoPanelView':
					$NeedFields = array(
						'Document_begDate',
						'Document_Num',
						'Document_Ser',
						'KLAreaType_id',
						'Lpu_Nick',
						'Lpu_id',
						'LpuRegion_Name',
						'OrgDep_Name',
						'OrgSmo_Name',
						'Person_Age',
						'PersonCard_begDate',
						'Person_Job',
						'Person_PAddress',
						'Person_Phone',
						'JobOrg_id',
						'Person_Post',
						'Person_RAddress',
						'Person_Snils',
						'Person_Inn',
						'Polis_begDate',
						'Polis_endDate',
						'Polis_Num',
						'Polis_Ser',
						'OmsSprTerr_id',
						'OmsSprTerr_Code',
						'Sex_Code',
						'Sex_id',
						'SocStatus_id',
						'Sex_Name',
						'SocStatus_Name',
						'Person_deadDT',
						'Person_closeDT',
						'PersonCloseCause_id',
						'Person_IsDead',
						'Person_IsBDZ',
						'PrivilegeType_id',
						'PrivilegeType_Name'
					);
					break;
				case 'PersonInfoPanel':
					$NeedFields = array(
    						'Document_begDate',
						'Document_Num',
						'Document_Ser',
						'KLAreaType_id',
						'Lpu_Nick',
						'Lpu_id',
						'LpuRegion_Name',
						'OrgDep_Name',
						'OrgSmo_Name',
						'Person_Age',
						'PersonCard_id',
						'PersonCard_begDate',
						'Person_Job',
						'Person_PAddress',
						'Person_Phone',
						'JobOrg_id',
						'Person_Post',
						'Person_RAddress',
						'Person_Snils',
						'Person_Inn',
						'Polis_begDate',
						'Polis_endDate',
						'Polis_Num',
						'Polis_Ser',
						'OmsSprTerr_id',
						'OmsSprTerr_Code',
						'KLRgn_id',
						'Sex_Code',
						'Sex_id',
						'Sex_Name',
						'SocStatus_id',
						'SocStatus_Name',

						// Семейное положение:
						'FamilyStatus_id',
						'FamilyStatus_Name',

						'Person_deadDT',
						'Person_closeDT',
						'PersonCloseCause_id',
						'Person_IsDead',
						'Person_IsBDZ',
						'Person_IsAnonym',
						'DeputyPerson_id',
						'NewslatterAccept',
						'personAgeText',
						'RemoteMonitoring',
						'PersonChild_id',
						'FeedingType_Name',
						'PersLabels'
					);
					break;
				case 'MobileAppPanel':
					$NeedFields = array(
						'Document_begDate',
						'Document_Num',
						'Document_Ser',
						'KLAreaType_id',
						'Lpu_Nick',
						'Lpu_id',
						'LpuRegion_Name',
						'OrgDep_Name',
						'OrgSmo_Name',
						'OrgSMO_id',
						'Person_Age',
						'PersonCard_id',
						'PersonCard_begDate',
						'Person_Job',
						'Person_PAddress',
						'Person_Phone',
						'JobOrg_id',
						'Person_Post',
						'Person_RAddress',
						'Person_Snils',
						'Person_Inn',
						'Polis_begDate',
						'Polis_endDate',
						'Polis_Num',
						'Polis_Ser',
						'PolisFormType_id',
						'PolisType_id',
						'OmsSprTerr_id',
						'OmsSprTerr_Code',
						'Sex_Code',
						'Sex_id',
						'SocStatus_id',
						'Sex_Name',
						'SocStatus_Name',
						'Person_deadDT',
						'Person_closeDT',
						'PersonCloseCause_id',
						'Person_IsDead',
						'Person_IsBDZ',
						'Person_IsAnonym',
						'DeputyPerson_id',
						'NewslatterAccept',
						'personAgeText'
					);
					break;
				case 'PersonDoublesInformationPanel':
					$NeedFields = array(
						'Document_begDate',
						'Document_Num',
						'Document_Ser',
						'KLAreaType_id',
						'Lpu_Nick',
						'Lpu_id',
						'LpuRegion_Name',
						'OrgDep_Name',
						'OrgSmo_Name',
						'Person_Age',
						'PersonCard_begDate',
						'Person_Job',
						'Person_PAddress',
						'JobOrg_id',
						'Person_Post',
						'Person_RAddress',
						'Person_Snils',
						'Polis_begDate',
						'Polis_endDate',
						'Polis_Num',
						'Polis_Ser',
						'Sex_Code',
						'Sex_id',
						'SocStatus_id',
						'Sex_Name',
						'SocStatus_Name',
						'Person_deadDT',
						'Person_closeDT',
						'PersonCloseCause_id',
						'Person_IsDead',
						'Person_IsBDZ',
						'BDZ_Guid',
						'Person_IsRefuse'
					);
					break;
				case 'PersonInformationPanel':
					$NeedFields = array(
						'Document_begDate',
						'Document_Num',
						'Document_Ser',
						'KLAreaType_id',
						'Lpu_Nick',
						'Lpu_id',
						'LpuRegion_Name',
						'OrgDep_Name',
						'OrgSmo_Name',
						'Person_Age',
						'PersonCard_begDate',
						'Person_Job',
						'Person_PAddress',
						'PAddress_Address',
						'Person_Phone',
						'JobOrg_id',
						'Person_Post',
						'Person_RAddress',
						'RAddress_Address',
						'Person_Snils',
						'Person_Inn',
						'Polis_begDate',
						'Polis_endDate',
						'Polis_Num',
						'Polis_Ser',
						'OmsSprTerr_id',
						'OmsSprTerr_Code',
						'Sex_Code',
						'Sex_id',
						'SocStatus_id',
						'Sex_Name',
						'SocStatus_Name',
						'Person_deadDT',
						'Person_closeDT',
						'PersonCloseCause_id',
						'Person_IsDead',
						'Person_IsBDZ',
						'Person_IsUnknown'
					);
					break;
				case 'Document':
					$NeedFields = array(
						'DocumentType_Name',
						'Document_begDate',
						'Document_Num',
						'Document_Ser',
						'OrgDep_Name',
						'MissingDataList',//Выводит список недостающих данных документа УДЛ: Тип, Серия, Номер, Дата и Кем выдан либо null
					);
					break;
				case 'NewslatterEditWindow':
					$NeedFields = array(
						'NewslatterAccept_id',
						'NewslatterAccept_IsSMS',
						'NewslatterAccept_IsEmail'
					);
					break;
				default:
					break;
			}
		}

		if (!empty($data['additionalFields'])) {
			$NeedFields = array_merge($NeedFields, $data['additionalFields']);
		}

		if (!empty($data['EvnDirection_id']) && (in_array('EvnDirection_id', $NeedFields) || in_array('EvnDirection_Num', $NeedFields) || in_array('EvnDirection_setDT', $NeedFields))) {
			$params['EvnDirection_id'] = $data['EvnDirection_id'];
			$select[] = "ED.EvnDirection_id as \"EvnDirection_id\"";
			$select[] = "ED.EvnDirection_Num as \"EvnDirection_Num\"";
			$select[] = "ED.EvnDirection_setDT as \"EvnDirection_setDT\"";
			$join['ED'] = "
				LEFT JOIN LATERAL(
					SELECT
						ED.EvnDirection_id,
						ED.EvnDirection_Num,
						COALESCE(to_char(ED.EvnDirection_setDT, 'DD.MM.YYYY'), '') as EvnDirection_setDT

					FROM
						v_EvnDirection_all ED 
					WHERE
						ED.EvnDirection_id = :EvnDirection_id
					LIMIT 1
				) as ED on true";
		}

		if (in_array('Lpu_id', $NeedFields)) {
			$select[] = "PersonState.Lpu_id as \"Lpu_id\"";
			$join['PersonState'] = "left join PersonState  on PS.Person_id = PersonState.Person_id";
		}

		if (in_array('Person_IsRefuse', $NeedFields)) {
			$select[] = "CASE WHEN PR.PersonRefuse_IsRefuse = 2 THEN 'true' ELSE 'false' END as \"Person_IsRefuse\"";
			$join['PR'] = "LEFT JOIN v_PersonRefuse PR  ON PR.Person_id = ps.Person_id and PR.PersonRefuse_IsRefuse = 2 and PR.PersonRefuse_Year = date_part('year', cast(:curdate as date))";
		}

		if (in_array('Person_PAddress', $NeedFields)) {
			$select[] = "COALESCE(RTRIM(COALESCE(PAddress.Address_Nick, PAddress.Address_Address)),'') as \"Person_PAddress\"";

			$join['PAddress'] = "left join Address PAddress  on PAddress.Address_id = PS.PAddress_id";
		}

		if (in_array('PAddress_Address', $NeedFields)) {
			$select[] = "RTRIM(PAddress.Address_Address) as \"PAddress_Address\"";

			$join['PAddress'] = "left join Address PAddress  on PAddress.Address_id = PS.PAddress_id";
		}

		if (in_array('Person_BAddress', $NeedFields)) {
			$select[] = "COALESCE(RTRIM(COALESCE(BAddress.Address_Nick, BAddress.Address_Address)),'') as \"Person_BAddress\"";

			$join['PersonBirthPlace'] = "left join PersonBirthPlace PersonBirthPlace  on PersonBirthPlace.Person_id = PS.Person_id";
			$join['BAddress'] = "left join Address BAddress  on BAddress.Address_id = PersonBirthPlace.Address_id";
		}

		if (in_array('Person_RAddress', $NeedFields)) {
			$select[] = "COALESCE(RTRIM(COALESCE(UAddress.Address_Nick, UAddress.Address_Address)),'') as \"Person_RAddress\"";

			$join['UAddress'] = "left join Address UAddress  on UAddress.Address_id = PS.UAddress_id";
		}

		if (in_array('RAddress_Address', $NeedFields)) {
			$select[] = "RTRIM(UAddress.Address_Address) as \"RAddress_Address\"";

			$join['UAddress'] = "left join Address UAddress  on UAddress.Address_id = PS.UAddress_id";
		}

		if (in_array('Person_Phone', $NeedFields)) {
			$select[] = "dbo.getPersonPhones(ps.Person_id, ',') as \"Person_Phone\"";
		}

		if (in_array('OrgDep_Name', $NeedFields)) {
			$select[] = "COALESCE(RTRIM(DORG.Org_Name), '') as \"OrgDep_Name\"";

			$join['Document'] = "left join Document  on Document.Document_id = PS.Document_id";
			$join['OrgDep'] = "left join OrgDep  on OrgDep.OrgDep_id = Document.OrgDep_id";
			$join['Org'] = "left join Org DORG  on DORG.Org_id = OrgDep.Org_id";
		}

		if (in_array('PersonChild_IsInvalid', $NeedFields)) {
			$select[] = "PCh.PersonChild_IsInvalid as \"PersonChild_IsInvalid\"";

			$join['PersonChild'] = "left join PersonChild PCh  on PCh.Person_id = PS.Person_id";
		}

		if (in_array('Diag_id', $NeedFields)) {
			$select[] = "PCh.Diag_id";

			$join['PersonChild'] = "left join PersonChild PCh  on PCh.Person_id = PS.Person_id";
		}

		if (in_array('PersonChild_invDate', $NeedFields)) {
			$select[] = "COALESCE(to_char(PCh.PersonChild_invDate, 'DD.MM.YYYY'), '') as \"PersonChild_invDate\"";

			$join['PersonChild'] = "left join PersonChild PCh  on PCh.Person_id = PS.Person_id";
		}

		if (in_array('Lpu_Nick', $NeedFields) || in_array('PersonCard_begDate', $NeedFields) || in_array('PersonCard_id', $NeedFields) || in_array('PersonCard_endDate', $NeedFields) || in_array('LpuRegion_Name', $NeedFields)) {
			$select[] = "CASE WHEN (pcard.PersonCard_endDate IS NOT NULL) THEN COALESCE(RTRIM(lpu_pcard.Lpu_Nick), '') || ' (Прикрепление неактуально. Дата открепления: ' || COALESCE(to_char(pcard.PersonCard_endDate, 'DD.MM.YYYY'), '') || ')' ELSE COALESCE(RTRIM(lpu_pcard.Lpu_Nick), '') end as \"Lpu_Nick\"";
			$select[] = "COALESCE(to_char(pcard.PersonCard_begDate, 'DD.MM.YYYY'), '') as \"PersonCard_begDate\"";
			$select[] = "pcard.PersonCard_id as \"PersonCard_id\"";
			$select[] = "COALESCE(to_char(pcard.PersonCard_endDate, 'DD.MM.YYYY'), '') as \"PersonCard_endDate\"";
			$select[] = "COALESCE(pcard.LpuRegion_Name, '') as \"LpuRegion_Name\"";
			$join['pcard'] = "
				LEFT JOIN LATERAL (
					select
						pc.Person_id as PersonCard_Person_id,
						pc.Lpu_id,
						pc.PersonCard_id,
						pc.PersonCard_begDate,
						pc.PersonCard_endDate,
						pc.LpuRegion_Name
					from
						v_PersonCard_all pc 
					where
						pc.Person_id = ps.Person_id and LpuAttachType_id = 1
					order
						by PersonCard_begDate desc, PersonCard_id desc
					limit 1
				) as pcard on true
				left join v_Lpu lpu_pcard  on lpu_pcard.Lpu_id = pcard.Lpu_id";
			$join['PersonState'] = "left join PersonState  on PS.Person_id = PersonState.Person_id";
			$join['Lpu'] = "left join v_Lpu Lpu  on Lpu.Lpu_id = PersonState.Lpu_id";
		}

		if (in_array('KLAreaType_id', $NeedFields)) {
			$select[] = "case when (KLArea.KLSocr_id = 68) or (KLArea.KLSocr_id = 56) then '1' else '0' end as \"KLAreaType_id\"";
			$join['UAddress'] = "left join Address UAddress  on UAddress.Address_id = PS.UAddress_id";
			$join['KLArea'] = "left join v_KLArea KLArea  on KLArea.KLArea_id = UAddress.KLTown_id";
		}

		if (in_array('DocumentType_id', $NeedFields)) {
			$select[] = "Document.DocumentType_id as \"DocumentType_id\"";
			$join['Document'] = "left join Document  on Document.Document_id = PS.Document_id";
		}

		if (in_array('DocumentType_Name', $NeedFields) || in_array('Document_begDate', $NeedFields) || in_array('Document_Num', $NeedFields) || in_array('Document_Ser', $NeedFields)) {
			$select[] = "COALESCE(RTRIM(DocumentType.DocumentType_Name), '') as \"DocumentType_Name\"";
			$select[] = "COALESCE(RTRIM(Document.Document_Num), '') as \"Document_Num\"";
			$select[] = "COALESCE(RTRIM(Document.Document_Ser), '') as \"Document_Ser\"";
			$select[] = "COALESCE(to_char(Document.Document_begDate, 'DD.MM.YYYY'), '') as \"Document_begDate\"";
			$join['Document'] = "left join Document  on Document.Document_id = PS.Document_id";
			$join['DocumentType'] = "left join DocumentType  on DocumentType.DocumentType_id = Document.DocumentType_id";
		}
		if(in_array('MissingDataList', $NeedFields)){
			$select[] = "(case when DocumentTypeFRMIS.DocumentTypeFRMIS_id is null then ' Тип документа,' ELSE '' end || case when PS.Document_Ser is null then ' Серия документа,' ELSE '' end || case when PS.Document_Num is null then ' Номер документа,' ELSE '' end || case when Document.Document_begDate is null then ' Дата документа,' ELSE '' end || case when OrgDep.Org_id is null then ' Кем выдан,' ELSE '' end ) as \"MissingDataList\"";
			$join['Document'] = "left join Document on Document.Document_id = PS.Document_id";
			$join['DocumentType'] = "left join DocumentType on DocumentType.DocumentType_id = Document.DocumentType_id";
			$join['DocumentTypeFRMIS'] = "left join DocumentTypeFRMIS on DocumentTypeFRMIS.DocumentTypeFRMIS_id = DocumentType.DocumentTypeFRMIS_id";
			$join['OrgDep'] = "left join OrgDep on OrgDep.OrgDep_id = Document.OrgDep_id";
		}

		if (in_array('Person_IsFedLgot', $NeedFields) || in_array('PrivilegeType_id', $NeedFields) || in_array('PrivilegeType_Name', $NeedFields)) {
			$select[] = "CASE WHEN PersonPrivilegeFed.Person_id IS NOT NULL THEN 1 ELSE 0 END as \"Person_IsFedLgot\"";
			$select[] = "PersonPrivilegeFed.PrivilegeType_id";
			$select[] = "COALESCE(PersonPrivilegeFed.PrivilegeType_Name, '') as \"PrivilegeType_Name\"";
			$join['PersonPrivilegeFed'] = "
				LEFT JOIN LATERAL(
					SELECT
						PP.Person_id,
						PP.PrivilegeType_id,
						PT.PrivilegeType_Name
					FROM
						v_PersonPrivilege PP 
						inner join v_PrivilegeType PT  on PT.PrivilegeType_id = PP.PrivilegeType_id
					WHERE
						PT.ReceptFinance_id = 1 and
						PP.PersonPrivilege_begDate <= CAST(:curdate  as date) AND
						(PP.PersonPrivilege_endDate IS NULL OR
						PP.PersonPrivilege_endDate >= cast(:curdate AS date)) AND
						PP.Person_id = PS.Person_id
					LIMIT 1
				) PersonPrivilegeFed on true";
		}

		if (in_array('Person_Inn', $NeedFields)) {
			if ($object == "v_Person_bdz") {
				$select[] = "COALESCE(ps.PersonInn_Inn, '') as \"Person_Inn\"";
			} else {
				$select[] = "COALESCE(ps.Person_Inn, '') as \"Person_Inn\"";
			}
		}

		if (in_array('Person_Snils', $NeedFields)) {
			$select[] = "COALESCE(PS.Person_Snils, '') as \"Person_Snils\"";
		}

		if (in_array('SocStatus_id', $NeedFields)) {
			$select[] = "ps.SocStatus_id as \"SocStatus_id\"";
		}

		if (in_array('UAddress_id', $NeedFields)) {
			$select[] = "ps.UAddress_id as \"UAddress_id\"";
		}

		if (in_array('PAddress_id', $NeedFields)) {
			$select[] = "ps.PAddress_id as \"PAddress_id\"";
		}

		if (in_array('Person_Age', $NeedFields)) {
			$select[] = "date_part('year',age(now(), PS.Person_Birthday)) as \"Person_Age\"";
		}
		if (in_array('personAgeText', $NeedFields)) {
			$select[] = "dbo.agecalculate(PS.Person_BirthDay, COALESCE(Person.Person_deadDT, cast(:curdate as timestamp))) as \"personAgeText\"";
			$join['Person'] = "left join Person  on Person.Person_id = PS.Person_id";
		}
		if (in_array('Person_IsBDZ', $NeedFields)) {
			$select[] = "CASE WHEN PS.Server_pid = 0 THEN 1 ELSE 0 END as \"Person_IsBDZ\"";
		}

		if (in_array('BDZ_Guid', $NeedFields)){
			$select[] = "COALESCE(Person.BDZ_Guid::varchar, '') as \"BDZ_Guid\"";
			$join['Person'] = "left join Person  on Person.Person_id = PS.Person_id";
		}

		if (in_array('Person_Post', $NeedFields)) {
			$select[] = "COALESCE(RTRIM(PP.Post_Name), '') as \"Person_Post\"";
			$join['job'] = "left join v_Job job  on job.Job_id = ps.Job_id";
			$join['Post'] = "left join Post PP  on PP.Post_id = Job.Post_id";
		}

		if (in_array('Person_Job', $NeedFields) || in_array('JobOrg_id', $NeedFields)) {
			$select[] = "job.Org_id as \"JobOrg_id\"";
			$select[] = "COALESCE(joborg.Org_Name, '') as \"Person_Job\"";
			$join['job'] = "left join v_Job job  on job.Job_id = ps.Job_id";
			$join['joborg'] = "left join v_Org joborg  on joborg.Org_id = job.Org_id";
		}

		if (in_array('OrgSMO_id', $NeedFields)) {
			$select[] = "Polis.OrgSmo_id as \"OrgSmo_id\"";
			$join['Polis'] = "left join Polis  on Polis.Polis_id = PS.Polis_id";
		}

		if (in_array('PolisFormType_id', $NeedFields)) {
			$select[] = "Polis.PolisFormType_id as \"PolisFormType_id\"";
			$join['Polis'] = "left join Polis  on Polis.Polis_id = PS.Polis_id";
		}

		if (in_array('PolisType_id', $NeedFields)) {
			$select[] = "Polis.PolisType_id as \"PolisType_id\"";
			$join['Polis'] = "left join Polis  on Polis.Polis_id = PS.Polis_id";
		}

		if (in_array('OrgSmo_Name', $NeedFields)) {
			$select[] = "COALESCE(RTRIM(PO.Org_Name), '') as \"OrgSmo_Name\"";
			$select[] = "OrgSmo.OrgSmo_id as \"OrgSmo_id\"";
			$join['Polis'] = "left join Polis  on Polis.Polis_id = PS.Polis_id";
			$join['OrgSmo'] = "left join OrgSmo  on OrgSmo.OrgSmo_id = Polis.OrgSmo_id";
			$join['PO'] = "left join Org PO  on PO.Org_id = OrgSmo.Org_id";
		}

		if (in_array('OmsSprTerr_id', $NeedFields) || in_array('OmsSprTerr_Code', $NeedFields)) {
			$select[] = "COALESCE(OmsSprTerr.OmsSprTerr_id, 0) as \"OmsSprTerr_id\"";
			$select[] = "COALESCE(OmsSprTerr.OmsSprTerr_Code, 0) as \"OmsSprTerr_Code\"";
			$join['Polis'] = "left join Polis  on Polis.Polis_id = PS.Polis_id";
			$join['OmsSprTerr'] = "left join OmsSprTerr  on OmsSprTerr.OmsSprTerr_id = Polis.OmsSprTerr_id";
		}

		if (in_array('Polis_Ser', $NeedFields) || in_array('Polis_Num', $NeedFields) || in_array('Polis_begDate', $NeedFields) || in_array('Polis_endDate', $NeedFields)) {
			$select[] = "COALESCE(RTRIM(case when Polis.PolisType_id = 4 then '' else Polis.Polis_Ser end), '') as \"Polis_Ser\"";


			if ($data['mode'] === 'MobileAppPanel') {
				$select[] = "case when Polis.PolisType_id = 4 then '' else Polis.Polis_Num end as \"Polis_Num\"";
			} else {
				$select[] = "COALESCE(RTRIM(case when Polis.PolisType_id = 4 and PS.Person_EdNum is not null then PS.Person_EdNum else Polis.Polis_Num end), '') as \"Polis_Num\"";
			}

			$select[] = "COALESCE(to_char(Polis.Polis_begDate, 'DD.MM.YYYY'), '') as \"Polis_begDate\"";
			$select[] = "COALESCE(to_char(Polis.Polis_endDate, 'DD.MM.YYYY'), '') as \"Polis_endDate\"";
			$join['Polis'] = "left join Polis  on Polis.Polis_id = PS.Polis_id";
		}

		if (in_array('Sex_id', $NeedFields) || in_array('Sex_Code', $NeedFields) || in_array('Sex_Name', $NeedFields)) {
			$select[] = "Sex.Sex_Code as \"Sex_Code\"";
			$select[] = "Sex.Sex_Name as \"Sex_Name\"";
			$select[] = "Sex.Sex_id as \"Sex_id\"";
			$join['Sex'] = "left join Sex  on Sex.Sex_id = PS.Sex_id";
		}

		if (in_array('SocStatus_Name', $NeedFields)) {
			$select[] = "COALESCE(RTRIM(SocStatus.SocStatus_Name), '') as \"SocStatus_Name\"";
			$join['SocStatus'] = "left join SocStatus  on SocStatus.SocStatus_id = PS.SocStatus_id";
		}

		if (in_array('Person_IsDead', $NeedFields) || in_array('Person_IsUnknown', $NeedFields) || in_array('PersonCloseCause_id', $NeedFields) || in_array('Person_deadDT', $NeedFields) || in_array('Person_closeDT', $NeedFields)) {
			$select[] = "Person.Person_IsUnknown as \"Person_IsUnknown\"";
			$select[] = "Person.Person_IsDead as \"Person_IsDead\"";
			$select[] = "Person.PersonCloseCause_id as \"PersonCloseCause_id\"";
			$select[] = "COALESCE(to_char(Person.Person_deadDT, 'DD.MM.YYYY'), '') as \"Person_deadDT\"";
			$select[] = "COALESCE(to_char(Person.Person_closeDT, 'DD.MM.YYYY'), '') as \"Person_closeDT\"";
			$join['Person'] = "left join Person  on Person.Person_id = PS.Person_id";
		}

		if (in_array('Person_IsAnonym', $NeedFields)) {
			$select[] = "Person.Person_IsAnonym as \"Person_IsAnonym\"";
		}

		// Семейное положение:
		if (in_array('FamilyStatus_id', $NeedFields) || in_array('FamilyStatus_Name', $NeedFields))
		{
			$select[] = "FS.FamilyStatus_id";
			$select[] = "FS.FamilyStatus_Name";

			$join['FamilyStatus'] =
				"left join FamilyStatus FS  on FS.FamilyStatus_id = PS.FamilyStatus_id";
		}

		if (empty($data['session'])) {
			$data['session'] = null;
		}
		if (allowPersonEncrypHIV($data['session'])) {
			$join['PEH'] = "left join v_PersonEncrypHIV PEH  on PEH.Person_id = PS.Person_id";
			$select[] = "case when PEH.PersonEncrypHIV_id is null then COALESCE(RTRIM(PS.Person_SurName), '') else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"";
			$select[] = "case when PEH.PersonEncrypHIV_id is null then COALESCE(RTRIM(PS.Person_FirName), '') else '' end as \"Person_Firname\"";
			$select[] = "case when PEH.PersonEncrypHIV_id is null then COALESCE(RTRIM(PS.Person_Secname), '') else '' end as \"Person_Secname\"";
			$select[] = "rtrim(PEH.PersonEncrypHIV_Encryp) as \"PersonEncrypHIV_Encryp\"";
		} else {
			$select[] = "COALESCE(RTRIM(PS.Person_Surname), '') as \"Person_Surname\"";
			$select[] = "COALESCE(RTRIM(PS.Person_Firname), '') as \"Person_Firname\"";
			$select[] = "COALESCE(RTRIM(PS.Person_Secname), '') as \"Person_Secname\"";
			$select[] = "null as \"PersonEncrypHIV_Encryp\"";
		}
		
		$select[] = "CASE WHEN PQ.PersonQuarantine_id is not null THEN 'true' ELSE 'false' END as \"PersonQuarantine_IsOn\"";
		$select[] = "to_char(PQ.PersonQuarantine_begDT, 'DD.MM.YYYY') as \"PersonQuarantine_begDT\"";
		$join['PersonQuarantine'] = "
			left join lateral (
				select PQ.PersonQuarantine_id, PQ.PersonQuarantine_begDT
				from v_PersonQuarantine PQ
				where PQ.Person_id = ps.Person_id 
				and PQ.PersonQuarantine_endDT is null
				limit 1
			) PQ on true
		";

		if (in_array('NewslatterAccept', $NeedFields)) {
			$select[] = "COALESCE(to_char(NA.NewslatterAccept_begDate, 'DD.MM.YYYY'), 'Отсутствует') as \"NewslatterAccept\"";
			$join['NewslatterAccept'] = "left join v_NewslatterAccept NA  on NA.Person_id = PS.Person_id and NA.Lpu_id = :Lpu_id and NA.NewslatterAccept_endDate is null";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		if (in_array('DeputyPerson_id', $NeedFields)) {
			$select[] = "PDEP.Person_pid as \"DeputyPerson_id\"";
			$join['PersonDeputy'] = "left join PersonDeputy PDEP  on PDEP.Person_id = PS.Person_id";
		}

		if (in_array('RemoteMonitoring', $NeedFields)) {//ищем какую-нибудь открытую карту наблюдения 
			//в дистанционном мониторинге, в первую очередь карту по метке "Заболевания АГ" (т.к. нужно для PersonInfoPanel). 
			//Все остальное загружать уже во вкладке "мониторинг" (ЭМК)
			$select[] = "remote_monitoring.Label_id as \"RemoteMonitoring_Label_id\"";
			$select[] = "remote_monitoring.LabelObserveChart_id as \"RemoteMonitoring_Chart_id\"";
			$select[] = "remote_monitoring.PersonModel_id as \"RemoteMonitoring_PersonModel_id\"";
			$select[] = "remote_monitoring.DispOutType_Name as \"RemoteMonitoring_DispOutType_Name\"";
			$select[] = "to_char(remote_monitoring.LabelObserveChart_endDate, 'DD.MM.YYYY') as \"RemoteMonitoring_ChartEndDT\"";
			$select[] = "(select count(LOCN.PersonLabel_id) from v_PersonLabel PLN
				inner join v_LabelObserveChart LOCN on LOCN.PersonLabel_id=PLN.PersonLabel_id
				where PLN.Person_id=PS.Person_id and LOCN.LabelObserveChart_endDate is null
				) as \"RemoteMonitoring_OpenedChartsCount\"";
			$join['PersonLabel'] = "
				LEFT JOIN LATERAL (
					select
						PL.Label_id,
						LOC.LabelObserveChart_id,
						PM.PersonModel_id,
						DOT.DispOutType_Name,
						LOC.LabelObserveChart_endDate
					from 
						v_PersonLabel PL 
						left join v_LabelObserveChart LOC  on LOC.PersonLabel_id=PL.PersonLabel_id
						left join v_PersonModel PM  on PM.PersonModel_id = LOC.PersonModel_id
						left join v_DispOutType DOT  on DOT.DispOutType_id = LOC.DispOutType_id
					where PL.Person_id = PS.Person_id and PL.Label_id=1 AND PL.PersonLabel_disDate is null
					order by
						PL.PersonLabel_id ASC, LOC.LabelObserveChart_id DESC
					LIMIT 1
					) remote_monitoring on true
			";
		}

        if (in_array('PersonChild_id', $NeedFields)) {
            $select[] = "PCH.PersonChild_id";
            $join['PersonChild_id'] = "
				LEFT JOIN LATERAL (
					select
						PersonChild_id as PersonChild_id
					from
						v_PersonChild PCH
					where
						PCH.Person_id = :Person_id
					) PCH ON true
			";
        }
        if (in_array('FeedingType_Name', $NeedFields)) {
            $select[] = "FTN.FeedingType_Name as FeedingType_Name";
            $join['FeedingType_Name'] = "
				LEFT JOIN LATERAL (
					select 
						FTN.FeedingType_Name
					from v_FeedingTypeAge FTA
					left join v_FeedingType FTN on FTN.FeedingType_id = FTA.FeedingType_id
					where FTA.PersonChild_id = PCH.PersonChild_id order by FTA.FeedingTypeAge_Age desc
					limit 1
					) FTN ON true
			";
        }

		if (getRegionNick()=='vologda' and in_array('PersLabels', $NeedFields)) {
			$select[] = "pers_labels.PersLabels";
			$join['PersLabels'] = "
				left join lateral (
					SELECT
						string_agg(L.Label_Name, '|') as PersLabels
					FROM (
						select L_ls.Label_Name
						from v_PersonLabel PL_ls
						inner join v_Label L_ls  on L_ls.Label_id=PL_ls.Label_id
						WHERE PL_ls.Person_id = PS.Person_id AND PL_ls.Label_id in (2,3,4,5,6) AND PL_ls.PersonLabel_disDate is null
						ORDER BY PL_ls.Label_id ASC
					) L
				) pers_labels on true
			";
		}

		if (in_array('KLCountry_Name', $NeedFields))
		{
			$select[] = "klc.KLCountry_Name as \"KLCountry_Name\"";

			$join['KLCountry'] = "
				LEFT JOIN (NationalityStatus ns
					INNER JOIN KLCountry klc ON klc.KLCountry_id = ns.KLCountry_id) ON ns.NationalityStatus_id = PS.NationalityStatus_id";
		}
		
		if (in_array('NewslatterAccept_IsSMS', $NeedFields))
		{
			$select[] = "NA.NewslatterAccept_id as \"NewslatterAccept_id\" ";
			$select[] = "NA.NewslatterAccept_IsSMS as \"NewslatterAccept_IsSMS\" ";
			$select[] = "NA.NewslatterAccept_IsEmail as \"NewslatterAccept_IsEmail\" ";
			$join['NewslatterEditWindow'] = "
				left join lateral (
					select
						*
					from v_NewslatterAccept NA
					where NA.Person_id = ps.Person_id order by NA.NewslatterAccept_id desc
					limit 1
					) NA on true
			";
		}

		if($object=='v_PersonState')
		{
			$select[]="COALESCE(PS.Person_EdNum, '') as \"Person_EdNum\"";
			$select[]="cast(PS.Person_SurName as varchar(1)) as \"SurNameLetter\"";
		}

		$select = implode($select, PHP_EOL.',');
		if (!empty($select)) {
			$select = ','.$select;
		}
		$join = implode($join, PHP_EOL.' ');

		$params['curdate'] = $this->getFirstResultFromQuery("select dbo.tzGetDate()");

		// тут только базовые поля, все остальные надо добавлять выше :)
		$query = "
			SELECT
				ps.Person_id as \"Person_id\",
				PS.PersonEvn_id as \"PersonEvn_id\",
				ps.Server_id as \"Server_id\",
				ps.Server_pid as \"Server_pid\",
				COALESCE(to_char(PS.Person_BirthDay, 'DD.MM.YYYY'), '') as \"Person_Birthday\"
				{$select}
			FROM
				{$object} PS 
				{$join}
			WHERE
				{$filter}
			{$top}
		";
		//echo getDebugSQL($query, $params); die;
		//echo '<pre>',print_r(getDebugSQL($query, $params)),'</pre>'; die();
		$result = $this->db->query($query, $params);


		if (is_object($result)) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение списка участков по заданными фильтрами
	 * @param array $data Фильтры
	 * @return array Ассоциативный массив с данными участков
	 */
	function loadLpuRegionList($data) {
		$filter = "";
		$join = "";
		$queryParams = array();
		if(isset($data['Org_id'])){
			$filter = " and O.Org_id = :Org_id";
			$queryParams['Org_id'] = $data['Org_id'];
			$join = "
				left join v_Lpu L  on L.Lpu_id = LR.Lpu_id
				left join v_Org O  on O.Org_id = L.Org_id
			";
		}
		else {
			if(isset($data['Lpu_id'])){
				$filter = " and LR.Lpu_id = :Lpu_id ";
				$queryParams['Lpu_id'] = $data['Lpu_id'];
			}
		}

		if ( isset($data['LpuAttachType_id']) /*&& empty($data['LpuRegionType_id'])*/ && getRegionNick() != 'kz') {
			$join = "
			";
			switch ($data['LpuAttachType_id'])
			{
				case 1:
					$filter .= " and LRT.LpuRegionType_SysNick in ('ter', 'ped', 'vop', 'comp','prip','feld') ";
				break;
				case 2:
					$filter .= " and LRT.LpuRegionType_SysNick = 'gin' ";
				break;
				case 3:
					$filter .= " and LRT.LpuRegionType_SysNick = 'stom' ";
				break;
				case 4:
					$filter .= " and LRT.LpuRegionType_SysNick in ('slug','ftiz','vop','psdet','pspod','psvz') ";
				break;
			}
		}

		if ( isset($data['LpuRegionType_SysNick']) ) {
			$queryParams['LpuRegionType_SysNick'] = $data['LpuRegionType_SysNick'];
			$filter .= " and LRT.LpuRegionType_SysNick = :LpuRegionType_SysNick ";
		}

		if ( isset($data['LpuRegionTypeList']) ) {
			$data['LpuRegionTypeList'] = json_decode($data['LpuRegionTypeList'], true);
			if (count($data['LpuRegionTypeList']) > 0) {
				$list_str = "'".implode("','", $data['LpuRegionTypeList'])."'";
				$filter .= " and LRT.LpuRegionType_SysNick IN ({$list_str})";
			}
		}

        if ( isset($data['LpuRegionType_id']) ) {
            $queryParams['LpuRegionType_id'] = $data['LpuRegionType_id'];
            $filter .= " and LRT.LpuRegionType_id = :LpuRegionType_id ";
        }

		if ( !empty($data['LpuRegionType_ids']) ) {
			$data['LpuRegionType_ids'] = json_decode($data['LpuRegionType_ids'], true);
			if (count($data['LpuRegionType_ids']) > 0) {
				$list_str = implode(',', $data['LpuRegionType_ids']);
				$filter .= " and LRT.LpuRegionType_id IN ({$list_str})";
			}
		}

		if ( !empty($data['showOpenerOnlyLpuRegions']) ) {
			$filter .= " and (LR.LpuRegion_endDate is null or LR.LpuRegion_endDate > dbo.tzGetDate())";
		}

		if ( !empty($data['showCrossedLpuRegions']) ) {
			if ( !empty($data['LpuRegion_begDate']) && empty($data['LpuRegion_endDate']) ) {
				$filter .= " and (
						(LR.LpuRegion_endDate is null) 
						or (LR.LpuRegion_endDate > CAST(:LpuRegion_begDate as date))
					)";
			} else if ( empty($data['LpuRegion_begDate']) && !empty($data['LpuRegion_endDate']) ) {
				$filter .= " and (
						(LR.LpuRegion_endDate is null and LR.LpuRegion_begDate <= CAST(:LpuRegion_endDate as date)) 
						or (LR.LpuRegion_endDate is not null and LR.LpuRegion_endDate < CAST(:LpuRegion_endDate as date))
						or (LR.LpuRegion_endDate is not null and LR.LpuRegion_endDate > CAST(:LpuRegion_endDate  as date) and LR.LpuRegion_begDate <= CAST(:LpuRegion_endDate as date))
					)";
			} else if ( !empty($data['LpuRegion_begDate']) && !empty($data['LpuRegion_endDate']) ){
				$filter .= " and (
						(LR.LpuRegion_endDate is null and LR.LpuRegion_begDate <= CAST(:LpuRegion_begDate as date))
						or (LR.LpuRegion_endDate is null and LR.LpuRegion_begDate <= CAST(:LpuRegion_endDate as date)) 
						or (LR.LpuRegion_endDate is not null and LR.LpuRegion_endDate < CAST(:LpuRegion_endDate  as date) and LR.LpuRegion_endDate > CAST(:LpuRegion_begDate as date))
						or (LR.LpuRegion_endDate is not null and LR.LpuRegion_endDate > CAST(:LpuRegion_endDate  as date) and LR.LpuRegion_begDate <= CAST(:LpuRegion_endDate as date))
					)";
			}
			$queryParams['LpuRegion_begDate'] = $data['LpuRegion_begDate'];
			$queryParams['LpuRegion_endDate'] = $data['LpuRegion_endDate'];
		}

		// Фильтр по врачу для получения участков этого врача
		if ( isset($data['MedPersonal_id']) ) {
			$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
			$filter .= " and LR.LpuRegion_id in (select LpuRegion_id from v_MedStaffRegion  where MedPersonal_id = :MedPersonal_id) ";
		}

		// Фильтр по участку
		if ( isset($data['LpuRegion_id']) ) {
			$queryParams['LpuRegion_id'] = $data['LpuRegion_id'];
			$filter .= " and LR.LpuRegion_id = :LpuRegion_id ";
		}
		
		//поиск по отделению
		if(!empty($data['LpuSection_id'])){
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
			$filter .= " and LR.LpuSection_id = :LpuSection_id ";
		}

		$query = "
			SELECT
				LR.LpuRegion_id as \"LpuRegion_id\",
				LR.Lpu_id as \"Lpu_id\",
				LR.LpuRegionType_id as \"LpuRegionType_id\",
				RTRIM(LR.LpuRegion_Name) as \"LpuRegion_Name\",
				RTRIM(LR.LpuRegionType_Name) as \"LpuRegionType_Name\",
				LR.LpuRegionType_SysNick as \"LpuRegionType_SysNick\",
				RTRIM(LR.LpuRegion_Descr) as \"LpuRegion_Descr\"
			FROM
				v_LpuRegion LR 
				left join v_LpuRegionType LRT  on LRT.LpuRegionType_id = LR.LpuRegionType_id
			".$join."
			WHERE (1 = 1) " . $filter . "
			ORDER BY
				case when ISNUMERIC(LR.LpuRegion_Name) = 1 then cast(LR.LpuRegion_Name as bigint) else 1488 end
		";

		//echo getDebugSql($query, $queryParams); die();
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Обработка данных учетного документа с получением данных
	 * для проверки наличия извещения по специфике заболевания
	 * @param array $evn_data Данные учетного документа
	 * @param array $data Входные параметры с данными сессии
	 * @return array
	 */
	private function processingEvnDataWithCheckEvnNotify($evn_data, $data) {
		switch($evn_data['EvnClass_SysNick']) {
			case 'EvnVizitPLStom':
			case 'EvnDiagPLStom':
			case 'EvnVizitPL':
			case 'EvnPS'://для приемн.отд-я в стационаре
			case 'EvnSection':
				$evn_data['Diag_id'] = null;
				$evn_data['Diag_Code'] = null;
				$queryParams = array('Evn_id' => $evn_data['Evn_id']);
				$add_where = '';
				if (!empty($data['MorbusType_SysNick'])) {
					$add_where .= ' and v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick';
					$queryParams['MorbusType_SysNick'] = $data['MorbusType_SysNick'];
				}
				$diag_join = "inner join v_Diag Diag  on Diag.Diag_id = coalesce(Evn.Diag_spid,Evn.Diag_id)";
				if ($evn_data['EvnClass_SysNick'] == 'EvnPS') {
					$diag_join = "inner join v_Diag Diag  on Diag.Diag_id = COALESCE(Evn.Diag_id, Evn.Diag_pid) or Diag.Diag_id = Evn.Diag_spid";
				}
				if (!empty($data['EvnDiagPLSop_id'])) {
					$diag_join = "inner join v_EvnDiag SD  on SD.EvnDiag_pid = Evn.{$evn_data['EvnClass_SysNick']}_id and SD.DiagSetClass_id != 1";
					$diag_join .= "inner join v_Diag Diag  on Diag.Diag_id = SD.Diag_id";
					$add_where = " and SD.EvnDiag_id = :EvnDiagPLSop_id ";
					$queryParams['EvnDiagPLSop_id'] = $data['EvnDiagPLSop_id'];
				}
				// параметра MorbusType_SysNick нет при подписании 
				$query = "
					Select
						Diag.Diag_id as \"Diag_id\"
						,Diag.Diag_Code as \"Diag_Code\"
						,Diag.Diag_FullName as \"Diag_Name\"
						,v_MorbusType.MorbusType_id as \"MorbusType_id\"
						,v_MorbusType.MorbusType_SysNick as \"MorbusType_SysNick\"
						,evn.Person_id as \"Person_id\"
					from v_{$evn_data['EvnClass_SysNick']} evn 
					{$diag_join}
					left join v_MorbusDiag  on v_MorbusDiag.Diag_id = Diag.Diag_id
					left join v_MorbusType  on v_MorbusType.MorbusType_id = COALESCE(v_MorbusDiag.MorbusType_id, 1)
					where Evn.{$evn_data['EvnClass_SysNick']}_id = :Evn_id
					{$add_where}
				";
				//echo getDebugSQL($query, $queryParams);
				$result = $this->db->query($query, $queryParams);
				if ( is_object($result) ) {
					$r = $result->result('array');
					if (isset($r[0]) && isset($r[0]['Diag_Code'])) {
						$evn_data['Diag_Code'] = $r[0]['Diag_Code'];
						$evn_data['Diag_Name'] = $r[0]['Diag_Name'];
						$evn_data['Diag_id'] = $r[0]['Diag_id'];
						//$evn_data['MorbusType_id'] = $r[0]['MorbusType_id'];
						$evn_data['Person_id'] = $r[0]['Person_id'];
						$evn_data['MorbusType_List'] = array();
						foreach ($r as $row) {
							$evn_data['MorbusType_List'][$row['MorbusType_SysNick']] = array('MorbusType_id'=>$row['MorbusType_id']);
						}
					}
				}
				if (isset($evn_data['MorbusType_List']) && is_array($evn_data['MorbusType_List'])) {
					foreach ($evn_data['MorbusType_List'] as $morbus_type => $row) {
						$evn_data['MorbusType_List'][$morbus_type]['Morbus_id'] = null;
						$evn_data['MorbusType_List'][$morbus_type]['PersonRegister_id'] = null;
						$evn_data['MorbusType_List'][$morbus_type]['EvnNotifyBase_id'] = null;
						$evn_data['MorbusType_List'][$morbus_type]['PersonRegisterOutCause_id'] = null;
						switch (true) {
							case ('pregnancy' == $morbus_type)://Беременность
								/*
								 * Проверяем наличие в системе записи регистра с типом ВИЧ
								 * и извещения о случае завершения беременности у ВИЧ-инфицированной женщины
								 * для учетного документа с диагнозом из групп: О00-О16, О30-О84
								 */
								$part_code = (int) substr($evn_data['Diag_Code'],1,2);
								if ( ($part_code >= 0 && $part_code <= 16) || ($part_code >= 30 && $part_code <= 84) ) {
									$this->load->model('EvnNotifyHIVPreg_model');
									$r = $this->EvnNotifyHIVPreg_model->checkEvnNotifyHIVPreg($evn_data);
									if (is_array($r) && count($r) > 0) {
										$evn_data['MorbusType_List'][$morbus_type]['Morbus_id'] = $r[0]['Morbus_id'];
										$evn_data['MorbusType_List'][$morbus_type]['PersonRegister_id'] = $r[0]['PersonRegister_id'];
										$evn_data['MorbusType_List'][$morbus_type]['EvnNotifyBase_id'] = $r[0]['EvnNotifyBase_id'];
										$evn_data['MorbusType_List'][$morbus_type]['PersonRegisterOutCause_id'] = $r[0]['PersonRegisterOutCause_id'];
										$evn_data['MorbusType_List'][$morbus_type]['EvnNotifyHIVPreg_id'] = $r[0]['EvnNotifyHIVPreg_id'];
									}
								}
								break;
							case ('onko' == $morbus_type)://онкоспецифика
								$evn_data['Lpu_id'] = $data['Lpu_id'];
								$evn_data['Evn_signDT'] = date('Y-m-d');
								$evn_data['MedPersonal_id'] = $data['session']['medpersonal_id'];
								$evn_data['pmUser_id'] = $data['pmUser_id'];
								$this->load->model('EvnOnkoNotify_model');
								//$r = $this->EvnOnkoNotify_model->loadDataCheckExists($evn_data['Person_id'], $evn_data['Diag_id']);

								//#142418 Делаем проверку по Evn_id, а не по диагнозу и пациенту
								if ( $evn_data['EvnClass_SysNick'] == 'EvnVizitPL' ) {
									$r = $this->EvnOnkoNotify_model->loadEvnVizitPLDataCheckExists($evn_data['Evn_id'], $data['EvnDiagPLSop_id']);
								}
								else if ( $evn_data['EvnClass_SysNick'] == 'EvnSection' ) {
									$r = $this->EvnOnkoNotify_model->loadEvnSectionDataCheckExists($evn_data['Evn_id'], $data['EvnDiagPLSop_id']);
								}
								else if ( $evn_data['EvnClass_SysNick'] == 'EvnDiagPLStom' ) {
									$r = $this->EvnOnkoNotify_model->loadEvnDiagPLStomDataCheckExists($evn_data['Evn_id']);
								}
								else {
									return false;
								}
								if (is_array($r) && count($r) > 0) {
									$evn_data['MorbusType_List'][$morbus_type]['Morbus_id'] = $r[0]['Morbus_id'];
									$evn_data['MorbusType_List'][$morbus_type]['EvnDiagPLSop_id'] = $data['EvnDiagPLSop_id'];
									$evn_data['MorbusType_List'][$morbus_type]['PersonRegister_id'] = $r[0]['PersonRegister_id'];
									$evn_data['MorbusType_List'][$morbus_type]['EvnNotifyBase_id'] = $r[0]['EvnNotifyBase_id'];
									$evn_data['MorbusType_List'][$morbus_type]['PersonRegisterOutCause_id'] = $r[0]['PersonRegisterOutCause_id'];
									$evn_data['MorbusType_List'][$morbus_type]['TumorStage_id'] = $r[0]['TumorStage_id'];
									$evn_data['MorbusType_List'][$morbus_type]['EvnOnkoNotifyNeglected_id'] = $r[0]['EvnOnkoNotifyNeglected_id'];
									$evn_data['MorbusType_List'][$morbus_type]['Alert_Msg'] = empty($r[0]['Error_Msg'])?null:$r[0]['Error_Msg'];
								}
								break;
							case ('narc' == $morbus_type):
								$this->load->model('EvnNotifyNarco_model');
								$r = $this->EvnNotifyNarco_model->checkEvnNotifyNarco($evn_data);
								if (is_array($r)) {
									if (count($r) > 0) {
										$evn_data['MorbusType_List'][$morbus_type]['Morbus_id'] = $r[0]['Morbus_id'];
										$evn_data['MorbusType_List'][$morbus_type]['PersonRegister_id'] = $r[0]['PersonRegister_id'];
										$evn_data['MorbusType_List'][$morbus_type]['EvnNotifyBase_id'] = $r[0]['EvnNotifyBase_id'];
										$evn_data['MorbusType_List'][$morbus_type]['PersonRegisterOutCause_id'] = $r[0]['PersonRegisterOutCause_id'];
									}
								}
								break;
							case ('crazy' == $morbus_type && mb_substr($evn_data['Diag_Code'], 0,2)=='F1'):
								$this->load->model('EvnNotifyNarco_model');
								$r = $this->EvnNotifyNarco_model->checkEvnNotifyNarco($evn_data);
								if (is_array($r)) {
									if (count($r) > 0) {
										$evn_data['MorbusType_List'][$morbus_type]['Morbus_id'] = $r[0]['Morbus_id'];
										$evn_data['MorbusType_List'][$morbus_type]['PersonRegister_id'] = $r[0]['PersonRegister_id'];
										$evn_data['MorbusType_List'][$morbus_type]['EvnNotifyBase_id'] = $r[0]['EvnNotifyBase_id'];
										$evn_data['MorbusType_List'][$morbus_type]['PersonRegisterOutCause_id'] = $r[0]['PersonRegisterOutCause_id'];
									}
								}
								break;
							case ('crazy' == $morbus_type && mb_substr($evn_data['Diag_Code'], 0,2)!='F1'):
								$this->load->model('EvnNotifyCrazy_model', 'EvnNotifyCrazy_model');
								$r = $this->EvnNotifyCrazy_model->checkEvnNotifyCrazy($evn_data);
								if (is_array($r)) {
									if (count($r) > 0) {
										$evn_data['MorbusType_List'][$morbus_type]['Morbus_id'] = $r[0]['Morbus_id'];
										$evn_data['MorbusType_List'][$morbus_type]['PersonRegister_id'] = $r[0]['PersonRegister_id'];
										$evn_data['MorbusType_List'][$morbus_type]['EvnNotifyBase_id'] = $r[0]['EvnNotifyBase_id'];
										$evn_data['MorbusType_List'][$morbus_type]['PersonRegisterOutCause_id'] = $r[0]['PersonRegisterOutCause_id'];
									}
								}
								break;
							case ('hepa' == $morbus_type)://гепатит
								$this->load->model('EvnNotifyHepatitis_model');
								$evn_data['Lpu_id'] = $data['Lpu_id'];
								$evn_data['Evn_signDT'] = date('Y-m-d');
								$evn_data['MedPersonal_id'] = $data['session']['medpersonal_id'];
								$evn_data['pmUser_id'] = $data['pmUser_id'];
								$r = $this->EvnNotifyHepatitis_model->checkEvnNotifyHepatitis($evn_data);
								if (is_array($r)) {
									if (count($r) > 0) {
										$evn_data['MorbusType_List'][$morbus_type]['Morbus_id'] = $r[0]['Morbus_id'];
										$evn_data['MorbusType_List'][$morbus_type]['PersonRegister_id'] = $r[0]['PersonRegister_id'];
										$evn_data['MorbusType_List'][$morbus_type]['EvnNotifyBase_id'] = $r[0]['EvnNotifyBase_id'];
										$evn_data['MorbusType_List'][$morbus_type]['PersonRegisterOutCause_id'] = $r[0]['PersonRegisterOutCause_id'];
									}
								}
								break;
							case ('orphan' == $morbus_type)://Извещение об орфанном заболевании
								$this->load->model('EvnNotifyOrphan_model');
								$r = $this->EvnNotifyOrphan_model->checkEvnNotifyOrphan($evn_data);
								if (is_array($r)) {
									if(count($r) > 0) {
										$evn_data['MorbusType_List'][$morbus_type]['Morbus_id'] = $r[0]['Morbus_id'];
										$evn_data['MorbusType_List'][$morbus_type]['PersonRegister_id'] = $r[0]['PersonRegister_id'];
										$evn_data['MorbusType_List'][$morbus_type]['EvnNotifyBase_id'] = $r[0]['EvnNotifyBase_id'];
										$evn_data['MorbusType_List'][$morbus_type]['PersonRegisterOutCause_id'] = $r[0]['PersonRegisterOutCause_id'];
									}
								}
								break;
							case ('tub' == $morbus_type)://туберкулез
								$this->load->model('EvnNotifyTub_model');
								$evn_data['Lpu_id'] = $data['Lpu_id'];
								$evn_data['Evn_signDT'] = date('Y-m-d');
								$evn_data['MedPersonal_id'] = $data['session']['medpersonal_id'];
								$evn_data['pmUser_id'] = $data['pmUser_id'];
								$r = $this->EvnNotifyTub_model->checkEvnNotifyTub($evn_data);
								if (is_array($r)) {
									if (count($r) > 0) {
										$evn_data['MorbusType_List'][$morbus_type]['Morbus_id'] = $r[0]['Morbus_id'];
										$evn_data['MorbusType_List'][$morbus_type]['PersonRegister_id'] = $r[0]['PersonRegister_id'];
										$evn_data['MorbusType_List'][$morbus_type]['EvnNotifyBase_id'] = $r[0]['EvnNotifyBase_id'];
										$evn_data['MorbusType_List'][$morbus_type]['PersonRegisterOutCause_id'] = $r[0]['PersonRegisterOutCause_id'];
									}
								}
								break;
							case ('vener' == $morbus_type)://венеро
								$this->load->model('EvnNotifyVener_model');
								$evn_data['Lpu_id'] = $data['Lpu_id'];
								$evn_data['Evn_signDT'] = date('Y-m-d');
								$evn_data['MedPersonal_id'] = $data['session']['medpersonal_id'];
								$evn_data['pmUser_id'] = $data['pmUser_id'];
								$r = $this->EvnNotifyVener_model->checkEvnNotifyVener($evn_data);
								if (is_array($r)) {
									if (count($r) > 0) {
										$evn_data['MorbusType_List'][$morbus_type]['Morbus_id'] = $r[0]['Morbus_id'];
										$evn_data['MorbusType_List'][$morbus_type]['PersonRegister_id'] = $r[0]['PersonRegister_id'];
										$evn_data['MorbusType_List'][$morbus_type]['EvnNotifyBase_id'] = $r[0]['EvnNotifyBase_id'];
										$evn_data['MorbusType_List'][$morbus_type]['PersonRegisterOutCause_id'] = $r[0]['PersonRegisterOutCause_id'];
									}
								}
								break;
							case ('hiv' == $morbus_type)://вич
								$this->load->model('EvnNotifyHIV_model');
								$evn_data['Lpu_id'] = $data['Lpu_id'];
								$evn_data['Evn_signDT'] = date('Y-m-d');
								$evn_data['MedPersonal_id'] = $data['session']['medpersonal_id'];
								$evn_data['pmUser_id'] = $data['pmUser_id'];
								$r = $this->EvnNotifyHIV_model->checkEvnNotifyHIV($evn_data);
								if (is_array($r)) {
									if (count($r) > 0) {
										$evn_data['MorbusType_List'][$morbus_type]['Morbus_id'] = $r[0]['Morbus_id'];
										$evn_data['MorbusType_List'][$morbus_type]['PersonRegister_id'] = $r[0]['PersonRegister_id'];
										$evn_data['MorbusType_List'][$morbus_type]['PersonRegisterOutCause_id'] = $r[0]['PersonRegisterOutCause_id'];
										$evn_data['MorbusType_List'][$morbus_type]['EvnNotifyBase_id'] = $r[0]['EvnNotifyBase_id'];
									}
								}
								break;
							case ('nephro' == $morbus_type)://нефрология
								$this->load->model('EvnNotifyNephro_model');
								$evn_data['Lpu_id'] = $data['Lpu_id'];
								$evn_data['Evn_signDT'] = date('Y-m-d');
								$evn_data['MedPersonal_id'] = $data['session']['medpersonal_id'];
								$evn_data['pmUser_id'] = $data['pmUser_id'];
								$r = $this->EvnNotifyNephro_model->loadDataCheckExists($evn_data['Person_id'], $evn_data['Diag_id']);
								if (is_array($r)) {
									if (count($r) > 0) {
										$evn_data['MorbusType_List'][$morbus_type]['Morbus_id'] = $r[0]['Morbus_id'];
										$evn_data['MorbusType_List'][$morbus_type]['PersonRegister_id'] = $r[0]['PersonRegister_id'];
										$evn_data['MorbusType_List'][$morbus_type]['EvnNotifyBase_id'] = $r[0]['EvnNotifyBase_id'];
										$evn_data['MorbusType_List'][$morbus_type]['PersonRegisterOutCause_id'] = $r[0]['PersonRegisterOutCause_id'];
									}
								}
								break;
							case ('prof' == $morbus_type)://профзаболевания

								/*$this->load->model('EvnNotifyProf_model');
								$evn_data['Lpu_id'] = $data['Lpu_id'];
								$evn_data['Evn_signDT'] = date('Y-m-d');
								$evn_data['MedPersonal_id'] = $data['session']['medpersonal_id'];
								$evn_data['pmUser_id'] = $data['pmUser_id'];

								$r = $this->EvnNotifyProf_model->loadDataCheckExistsExtended($evn_data['Person_id'], $evn_data['Diag_id']);
								if (is_array($r)) {
									if (count($r) > 0) {
										$evn_data['Morbus_id'] = $r[0]['Morbus_id'];
										$evn_data['PersonRegister_id'] = $r[0]['PersonRegister_id'];
										$evn_data['EvnNotifyBase_id'] = $r[0]['EvnNotifyBase_id'];
										$evn_data['PersonRegisterOutCause_id'] = $r[0]['PersonRegisterOutCause_id'];
									} else {
										//нет записей
										$evn_data['Morbus_id'] = null;
										$evn_data['PersonRegister_id'] = null;
										$evn_data['EvnNotifyBase_id'] = null;
										$evn_data['PersonRegisterOutCause_id'] = null;
									}
								}
								$this->load->model('EvnNotifyProf_model');
								$evn_data['Lpu_id'] = $data['Lpu_id'];
								$evn_data['Evn_signDT'] = date('Y-m-d');
								$evn_data['MedPersonal_id'] = $data['session']['medpersonal_id'];
								$evn_data['pmUser_id'] = $data['pmUser_id'];

								$r = $this->EvnNotifyProf_model->loadDataCheckExistsExtended($evn_data);
								//нет записей
								$evn_data['PersonRegister_id'] = null;
								$evn_data['EvnNotifyBase_id'] = null;
								$evn_data['PersonRegisterOutCause_id'] = null;

								$response['Records'] = $r;*/
								break;
							default:
								break;
						} // end switch(true)
					} // end foreach ($evn_data['MorbusType_List']
				}
				break;
		} // end switch($evn_data['EvnClass_SysNick'])
		return $evn_data;
	}

	/**
	 * Получение данных документа
	 */
	function loadEvnData($evn_id) {
		$query = "
			Select
				EvnClass.EvnClass_SysNick as \"EvnClass_SysNick\",
				Evn.Person_id as \"Person_id\",
				Evn.PersonEvn_id as \"PersonEvn_id\",
				Evn.Server_id as \"Server_id\",
				Evn.Evn_id as \"Evn_id\",
				Evn.Evn_pid as \"Evn_pid\",
				Evn.Evn_rid as \"Evn_rid\",
				COALESCE(Evn_IsSigned, 1) as \"Evn_IsSigned\",
				Evn.pmUser_signID as \"pmUser_signID\"
			from Evn 
			inner join EvnClass  on Evn.EvnClass_id = EvnClass.EvnClass_id
			where Evn.Evn_id =  ?
			LIMIT 1
			";
		//echo getDebugSQL($query, array($evn_id));
		$result = $this->db->query($query, array($evn_id));
		$evn_data = array();
		if (is_object($result)) {
			$r = $result->result('array');
			if (count($r)>0 && isset($r[0]['Evn_IsSigned'])) {
				$evn_data = $r[0];
			}
		}
		return $evn_data;
	}

	/**
	 * Получение данных для проверки наличия извещения по специфике заболевания
	 */
	function checkEvnNotify($data) {
		$evn_data = $this->loadEvnData($data['Evn_id']);
		if(empty($evn_data)) {
			return array(array('Error_Msg' => 'Документ не найден!'));
		}
		$evn_data['Error_Msg'] = null;
		$evn_data['Error_Code'] = null;
		$evn_data = $this->processingEvnDataWithCheckEvnNotify($evn_data, $data);
		return array($evn_data);
	}

	/**
	 * Получение данных для проверки наличия извещения по специфике заболевания
	 */
	function checkEvnNotifyProf($data) {
		$evn_data = $this->loadEvnData($data['Evn_id']);
		if(empty($evn_data)) {
			return array(array('Error_Msg' => 'Документ не найден!'));
		}
		$evn_data['Error_Msg'] = null;
		$evn_data['Error_Code'] = null;

		switch($evn_data['EvnClass_SysNick']) {
			case 'EvnVizitPLStom':
			case 'EvnVizitPL':
			case 'EvnPS'://для приемн.отд-я в стационаре
			case 'EvnSection':
				$evn_data['Diag_id'] = null;
				$evn_data['Diag_Code'] = null;
				$queryParams = array('Evn_id' => $evn_data['Evn_id']);
				$add_where = '';
				if (!empty($data['MorbusType_SysNick'])) {
					$add_where .= ' and v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick';
					$queryParams['MorbusType_SysNick'] = $data['MorbusType_SysNick'];
				}
				// параметра MorbusType_SysNick нет при подписании 
				$query = "
					Select
						Diag.Diag_id as \"Diag_id\"
						,Diag.Diag_Code as \"Diag_Code\"
						,Diag.Diag_FullName as \"Diag_Name\"
						,v_MorbusType.MorbusType_id as \"MorbusType_id\"
						,v_MorbusType.MorbusType_SysNick as \"MorbusType_SysNick\"
						,evn.Person_id as \"Person_id\"
					from v_{$evn_data['EvnClass_SysNick']} evn 
					inner join v_Diag Diag  on Evn.Diag_id = Diag.Diag_id
					left join v_MorbusDiag  on v_MorbusDiag.Diag_id = Diag.Diag_id
					left join v_MorbusType  on v_MorbusType.MorbusType_id = COALESCE(v_MorbusDiag.MorbusType_id, 1)
					where Evn.{$evn_data['EvnClass_SysNick']}_id = :Evn_id
					{$add_where}
				";
				//echo getDebugSQL($query, $queryParams);
				$result = $this->db->query($query, $queryParams);
				if ( is_object($result) ) {
					$r = $result->result('array');
					if (isset($r[0]) && isset($r[0]['Diag_Code'])) {
						$evn_data['Diag_Code'] = $r[0]['Diag_Code'];
						$evn_data['Diag_Name'] = $r[0]['Diag_Name'];
						$evn_data['Diag_id'] = $r[0]['Diag_id'];
						//$evn_data['MorbusType_id'] = $r[0]['MorbusType_id'];
						$evn_data['Person_id'] = $r[0]['Person_id'];
						$evn_data['MorbusType_List'] = array();
						foreach ($r as $row) {
							$evn_data['MorbusType_List']['prof'] = array('MorbusType_id'=>47);
						}
					}
				}
			break;
		}
		$this->load->model('EvnNotifyProf_model');
		$evn_data['Lpu_id'] = $data['Lpu_id'];
		$evn_data['Evn_signDT'] = date('Y-m-d');
		$evn_data['MedPersonal_id'] = $data['session']['medpersonal_id'];
		$evn_data['pmUser_id'] = $data['pmUser_id'];

		$r = $this->EvnNotifyProf_model->loadDataCheckExistsExtended($evn_data);
		//нет записей
		$evn_data['PersonRegister_id'] = null;
		$evn_data['EvnNotifyBase_id'] = null;
		$evn_data['PersonRegisterOutCause_id'] = null;

		$evn_data['Records'] = $r;
		$evn_data['Error_Msg'] = null;
		$evn_data['Error_Code'] = null;
		return $evn_data;
	}

	/**
	 * Проверка существования записи в регистре по суицидам и необходимости внесения
	 */
	function checkSuicideRegistry($data) {
		switch($data['EvnClass_SysNick']) {
			case 'EvnPL':
				$query = "
					select 
					COALESCE(to_char(EPL.EvnPL_setDate, 'YYYY-MM-DD'), '') as \"Evn_setDate\",
					EDPLS.Diag_id as \"Diag_id\",
					EVPL.MedPersonal_id as \"MedPersonal_id\",
					EVPL.Person_id as \"Person_id\"
					from v_EvnPL EPL 
					inner join v_EvnVizitPL EVPL  on EPL.EvnPL_id = EVPL.EvnVizitPL_pid
					inner join v_EvnDiagPLSop EDPLS  on EDPLS.EvnDiagPLSop_pid = EVPL.EvnVizitPL_id
					inner join v_PersonRegisterDiag PRD  on PRD.Diag_id = EDPLS.Diag_id and PRD.PersonRegisterType_id = 62
					where 
					EPL.EvnPL_id = :Evn_id and 
					not exists(
						select PersonRegister_id 
						from v_PersonRegister 
						where 
						Person_id = EVPL.Person_id and 
						PersonRegisterType_id = 62 and
						PersonRegister_setDate >= EPL.EvnPL_setDate and
						(PersonRegister_setDate <= EPL.EvnPL_didDate or EPL.EvnPL_didDate is null)
					)
				";
				break;
			case 'EvnPS':
				$query = "
					select 
					COALESCE(to_char(EPS.EvnPS_setDate, 'YYYY-MM-DD'), '') as \"Evn_setDate\",
					EDPS.Diag_id as \"Diag_id\",
					COALESCE(ES.MedPersonal_id, EPS.MedPersonal_pid) as \"MedPersonal_id\",
					EPS.Person_id as \"Person_id\"
					from v_EvnPS EPS 
					inner join v_EvnDiagPS EDPS  on EDPS.EvnDiagPS_rid = EPS.EvnPS_id
					inner join v_PersonRegisterDiag PRD  on PRD.Diag_id = EDPS.Diag_id and PRD.PersonRegisterType_id = 62
					left join v_EvnSection ES  on ES.EvnSection_id = EDPS.EvnDiagPS_pid
					where 
					EPS.EvnPS_id = :Evn_id and 
					not exists(
						select PersonRegister_id 
						from v_PersonRegister 
						where 
						Person_id = EPS.Person_id and 
						PersonRegisterType_id = 62 and
						PersonRegister_setDate >= EPS.EvnPS_setDate and
						(PersonRegister_setDate <= EPS.EvnPS_didDate or EPS.EvnPS_didDate is null)
					)
					
					union
					
					select 
					COALESCE(to_char(EPS.EvnPS_setDate, 'YYYY-MM-DD'), '') as \"Evn_setDate\",
					EDPS.Diag_id as \"Diag_id\",
					COALESCE(ES.MedPersonal_id, EPS.MedPersonal_pid) as \"MedPersonal_id\",
					EPS.Person_id as \"Person_id\"
					from v_EvnPS EPS 
					inner join v_EvnDiagPS EDPS  on EDPS.EvnDiagPS_pid = EPS.EvnPS_id
					inner join v_PersonRegisterDiag PRD  on PRD.Diag_id = EDPS.Diag_id and PRD.PersonRegisterType_id = 62
					left join v_EvnSection ES  on ES.EvnSection_id = EDPS.EvnDiagPS_pid
					where 
					EPS.EvnPS_id = :Evn_id and 
					not exists(
						select PersonRegister_id 
						from v_PersonRegister 
						where 
						Person_id = EPS.Person_id and 
						PersonRegisterType_id = 62 and
						PersonRegister_setDate >= EPS.EvnPS_setDate and
						(PersonRegister_setDate <= EPS.EvnPS_didDate or EPS.EvnPS_didDate is null)
					)
				";
				break;
			default:
				return false;
				break;
		}

		$result = $this->db->query($query, $data);
		if ( !is_object($result) ) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}

		return $result->result('array');
	}

	/**
	 *  Подписывание документа
	 *  Входящие данные: $_POST
	 *  На выходе: JSON-строка
	 *  Используется: общая функция signedDocument
	 *  Будет ошибкой обращаться к этому методу напрямую, минуя функцию signedDocument на клиенте.
	 */
	function signedDocument($data) {
		if ($data['type'] == 'Evn') {
			// Предварительно проверяем, может документ уже подписан
			$evn_data = $this->loadEvnData($data['id']);
			if(empty($evn_data)) return false;
			$query = "";
			if ( $evn_data['Evn_IsSigned'] == 1 || ($data['session']['region']['nick'] == 'pskov' && in_array($evn_data['EvnClass_SysNick'], array('EvnVizitPL', 'EvnSection'))) ) {
				$query = "
					select
						:id as \"id\",
						2 as \"Evn_IsSigned\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_Evn_sign (
						Evn_id := :id,
						pmUser_id := :pmUser_id
					)
				";
			} elseif (isSuperAdmin() || $data['pmUser_id'] == $evn_data['pmUser_signID'] || true) { // разрешено отменять подпись только суперадмину // открыть пока всем (refs #9350)
				// если тип назначения то ещё и проставляем PrescriptionStatusType_id = 1
				$query = "
					select
						:id as \"id\",
						1 as \"Evn_IsSigned\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_Evn_unsign (
						Evn_id := :id,
						pmUser_id := :pmUser_id
					)
				";
			}
			if (strlen($query)>0) {
				$result = $this->db->query($query, $data);

				if ( !is_object($result) ) {
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
				}

				$response = $result->result('array');
				if (empty($response) || !empty($response[0]['Error_Msg']) )
				{
					return $response;
				}
				$evn_data['Evn_IsSigned'] = $response[0]['Evn_IsSigned'];
				// действия после подписания/отмены подписи. Что-то тут, а что-то на клиенте в функции signedDocument
				if ($response[0]['Evn_IsSigned'] == 2 )
				{
					$evn_data['pmUser_signID'] = $data['pmUser_id'];
					//onSignedDocument
					$evn_data = $this->processingEvnDataWithCheckEvnNotify($evn_data, $data);
				}
				if ($response[0]['Evn_IsSigned'] == 1 )
				{
					//onUnSignedDocument
				}
				$response[0] = array_merge($response[0],$evn_data);
				return $response;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadMedStatWorkPlace($data) {
		$filter = '(1 = 1)';
		$queryList = array();
		$queryParams = array();
		$filterEPL = '';
		$filterEPS = '';

		$filter .= " and E.Lpu_id = :Lpu_id";
		$queryParams['Lpu_id'] = $data['Lpu_id'];

		if ( !empty($data['Search_SurName']) ) {
			$filter .= " and PS.Person_SurName ilike :Person_SurName";
			$queryParams['Person_SurName'] = rtrim($data['Search_SurName']) . '%';
		}

		if ( !empty($data['Search_FirName']) ) {
			$filter .= " and PS.Person_FirName ilike :Person_FirName";
			$queryParams['Person_FirName'] = rtrim($data['Search_FirName']) . '%';
		}

		if ( !empty($data['Search_SecName']) ) {
			$filter .= " and PS.Person_SecName ilike :Person_SecName";
			$queryParams['Person_SecName'] = rtrim($data['Search_SecName']) . '%';
		}

		if ( !empty($data['Search_BirthDay']) ) {
			$filter .= " and PS.Person_BirthDay = :Person_BirthDay";
			$queryParams['Person_BirthDay'] = $data['Search_BirthDay'];
		}

		if ( !empty($data['Evn_NumCard']) ) {
			$filterEPL .= " and E.EvnPL_NumCard ilike :Evn_NumCard";
			$filterEPS .= " and E.EvnPS_NumCard ilike :Evn_NumCard";
			$queryParams['Evn_NumCard'] = $data['Evn_NumCard'] . '%';
		}

		if ( !empty($data['begDate']) ) {
			$filterEPL .= " and E.EvnPL_setDate >= CAST(:begDate as date)";
			$filterEPS .= " and E.EvnPS_setDate >= CAST(:begDate as date)";
			$queryParams['begDate'] = $data['begDate'];
		}

		if ( !empty($data['endDate']) ) {
			$filterEPL .= " and E.EvnPL_setDate <= CAST(:endDate as date)";
			$filterEPS .= " and E.EvnPS_setDate <= CAST(:endDate as date)";
			$queryParams['endDate'] = $data['endDate'];
		}

		if ( !empty($data['Diag_From']) ) {
			$filter .= " and D.Diag_Code >= :Diag_From";
			$queryParams['Diag_From'] = $data['Diag_From'];
		}

		if ( !empty($data['Diag_To']) ) {
			$filter .= " and D.Diag_Code <= :Diag_To";
			$queryParams['Diag_To'] = $data['Diag_To'];
		}

		if ( !empty($data['LpuBuilding_id']) ) {
			$filter .= " and LB.LpuBuilding_id = :LpuBuilding_id";
			$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}

		$diagFilter = getAccessRightsDiagFilter('D.Diag_Code');
		if (!empty($diagFilter)) {
			$filter .= " and $diagFilter";
		}
		/**
		 *
		CASE WHEN PolisType.PolisType_Code = 4 then '' ELSE COALESCE(RTRIM(Polis.Polis_Ser), '') END as \"Polis_Ser\",
		CASE WHEN PolisType.PolisType_Code = 4 then COALESCE(RTRIM(ps.Person_EdNum), '') ELSE COALESCE(RTRIM(Polis.Polis_Num), '') END as \"Polis_Num\",		 */
		// Для EvnPL
		if ( empty($data['EvnClass_id']) || $data['EvnClass_id'] == 2 ) {
			$queryList[] = "
				select
					 E.EvnPL_id as \"Evn_id\"
					,E.EvnClass_id as \"EvnClass_id\"
					,E.Person_id as \"Person_id\"
					,E.PersonEvn_id as \"PersonEvn_id\"
					,E.Server_id as \"Server_id\"
					,PS.Person_SurName as \"Person_SurName\"
					,PS.Person_FirName as \"Person_FirName\"
					,PS.Person_SecName as \"Person_SecName\"
					,E.pmUser_insID as \"pmUser_insID\"
					,'ТАП' as \"EvnClass_Name\"
					,E.EvnPL_NumCard as \"Evn_NumCard\"
					,COALESCE(PS.Person_SurName, '') || ' ' || COALESCE(PS.Person_FirName, '') || ' ' || COALESCE(PS.Person_SecName, '') as \"Person_FIO\"
					,CASE WHEN PolT.PolisType_Code = 4 then '' ELSE COALESCE(RTRIM(Polis.Polis_Ser), '') END as \"Polis_Ser\"
					,CASE WHEN PolT.PolisType_Code = 4 then COALESCE(RTRIM(ps.Person_EdNum), '') ELSE COALESCE(RTRIM(Polis.Polis_Num), '') END as \"Polis_Num\"
					,COALESCE(RTRIM(PS.Person_Snils), '') as \"Person_Snils\"
					,to_char(PS.Person_BirthDay, 'DD.MM.YYYY') as \"Person_BirthDay\"
					,COALESCE(D.Diag_FullName, '') as \"Diag_Name\"
					,to_char(E.EvnPL_setDT, 'DD.MM.YYYY') as \"Evn_setDate\"
					,CASE WHEN E.EvnPL_IsFinish = 1 THEN to_char(E.EvnPL_disDT, 'DD.MM.YYYY') ELSE null END as \"Evn_disDate\"
					,RTRIM(LS.LpuSection_Name) as \"LpuSection_Name\"
					,RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\"
					,'' as \"PrehospType_Name\"
					,'' as \"EvnPS_HospCount\"
					,'' as \"LeaveType_Name\"
					,PT.PayType_Name as \"PayType_Name\"
					,LB.LpuBuilding_Name as \"LpuBuilding_Name\"
				from
					v_EvnPL E 
					inner join v_PersonState PS  on PS.Person_id = E.Person_id
					left join v_PersonPolis Polis  on Polis.Polis_id = PS.Polis_id
					left join v_PolisType PolT  on PolT.PolisType_id = Polis.PolisType_id
					left join v_Diag D  on D.Diag_id = E.Diag_id
					left join v_EvnVizitPL EVPL  on EVPL.EvnVizitPL_pid = E.EvnPL_id
						and EVPL.EvnVizitPL_Index = EVPL.EvnVizitPL_Count - 1
					left join v_LpuSection LS  on LS.LpuSection_id = EVPL.LpuSection_id
					LEFT JOIN LATERAL (
						select Person_Fio
						from v_MedPersonal 
						where MedPersonal_id = EVPL.MedPersonal_id
						LIMIT 1
					) MP on true
					left join PayType PT  on PT.PayType_id = EVPL.PayType_id
					left join v_LpuUnit LU  on LS.LpuUnit_id = LU.LpuUnit_id
					left join v_LpuBuilding LB  on LU.LpuBuilding_id = LB.LpuBuilding_id
				where
					" . $filter . "
					" . $filterEPL . "
			";
		}

		// Для EvnPS
		if ( empty($data['EvnClass_id']) || $data['EvnClass_id'] == 30 ) {
			$queryList[] = "
				select
					 E.EvnPS_id as \"Evn_id\"
					,E.EvnClass_id as \"EvnClass_id\"
					,E.Person_id as \"Person_id\"
					,E.PersonEvn_id as \"PersonEvn_id\"
					,E.Server_id as \"Server_id\"
					,PS.Person_SurName as \"Person_SurName\"
					,PS.Person_FirName as \"Person_FirName\"
					,PS.Person_SecName as \"Person_SecName\"
					,E.pmUser_insID as \"pmUser_insID\"
					,'КВС' as \"EvnClass_Name\"
					,E.EvnPS_NumCard as \"Evn_NumCard\"
					,COALESCE(PS.Person_SurName, '') || ' ' || COALESCE(PS.Person_FirName, '') || ' ' || COALESCE(PS.Person_SecName, '') as \"Person_FIO\"
					,CASE WHEN PolT.PolisType_Code = 4 then '' ELSE COALESCE(RTRIM(Polis.Polis_Ser), '') END as \"Polis_Ser\"
					,CASE WHEN PolT.PolisType_Code = 4 then COALESCE(RTRIM(ps.Person_EdNum), '') ELSE COALESCE(RTRIM(Polis.Polis_Num), '') END as \"Polis_Num\"
					,COALESCE(RTRIM(PS.Person_Snils), '') as \"Person_Snils\"
					,to_char(PS.Person_BirthDay, 'DD.MM.YYYY') as \"Person_BirthDay\"
					,COALESCE(D.Diag_FullName, '') as \"Diag_Name\"
					,to_char(E.EvnPS_setDT, 'DD.MM.YYYY') as \"Evn_setDate\"
					,to_char(E.EvnPS_disDT, 'DD.MM.YYYY') as \"Evn_disDate\"
					,RTRIM(LS.LpuSection_Name) as \"LpuSection_Name\"
					,RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\"
					,PT.PrehospType_Name as \"PrehospType_Name\"
					,COALESCE(CAST(E.EvnPS_HospCount as varchar), '') as \"EvnPS_HospCount\"
					,LT.LeaveType_Name as \"LeaveType_Name\"
					,PayT.PayType_Name as \"PayType_Name\"
					,LB.LpuBuilding_Name as \"LpuBuilding_Name\"
				from
					v_EvnPS E
				    inner join  v_PersonState PS on PS.Person_id = E.Person_id
				    left join v_PersonPolis Polis  on Polis.Polis_id = PS.Polis_id
				    left join v_PolisType PolT  on PolT.PolisType_id = Polis.PolisType_id
					left join v_Diag D  on D.Diag_id = E.Diag_id
					left join v_EvnSection ES  on ES.EvnSection_pid = E.EvnPS_id
						and ES.EvnSection_Index = ES.EvnSection_IndexMinusOne
					left join v_PrehospType PT  on PT.PrehospType_id = E.PrehospType_id
					left join v_LeaveType LT  on LT.LeaveType_id = ES.LeaveType_id
					left join v_LpuSection LS  on LS.LpuSection_id = ES.LpuSection_id
					left join v_LpuUnit LU  on LS.LpuUnit_id = LU.LpuUnit_id
					left join v_LpuBuilding LB  on LU.LpuBuilding_id = LB.LpuBuilding_id
					left join v_PayType PayT  on PayT.PayType_id = COALESCE(ES.PayType_id, E.PayType_id)
					LEFT JOIN LATERAL (
						select Person_Fio
						from v_MedPersonal 
						where MedPersonal_id = ES.MedPersonal_id
						LIMIT 1
					) MP on true
				where
					" . $filter . "
					" . $filterEPS . "
			";
		}

		$query = implode(' union all ', $queryList) . "
			order by
				 \"Evn_setDate\"
				,\"Evn_disDate\"
				,\"Person_SurName\"
				,\"Person_FirName\"
				,\"Person_SecName\"
				,\"Person_BirthDay\"
		";

		//echo getDebugSQL($query, $queryParams);die;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$result = $result->result('array');
			for ($i=0; $i<count($result); $i++){
				$result[$i]['Polis_SerNum'] = $result[$i]['Polis_Ser'] . ' ' . $result[$i]['Polis_Num'];
			}
			return $result;
		}
		else {
			return false;
		}
	}


	/**
	 *	Получение данных человека на дату
	 */
	function getPersonDataOnDate($Person_id, $onDate = null) {
		if ( empty($onDate) ) {
			$query = "
				select
					 ps.Person_id as \"Person_id\"
					,COALESCE(ps.Person_SurName, '') as \"Person_SurName\"
					,COALESCE(ps.Person_FirName, '') as \"Person_FirName\"
					,COALESCE(ps.Person_SecName, '') as \"Person_SecName\"
					,to_char(ps.Person_BirthDay, 'DD.MM.YYYY') as \"Person_BirthDay\"
					,date_part('year',age(now(), PS.Person_Birthday)) as \"Person_Age\"
					,dbo.Age_newborn(ps.Person_Birthday, getdate()) as \"Person_AgeMonth\"
					,COALESCE(ps.Sex_id, 0) as \"Sex_id\"
					,COALESCE(p.Person_IsUnknown, 1) as \"Person_IsUnknown\"
				from
					v_PersonState ps 
					left join v_Person p  on p.Person_id = ps.Person_id
				where
					ps.Person_id = :Person_id
				LIMIT 1
			";
		}
		else {
			$query = "
				select
					 ps.Person_id as \"Person_id\"
					,COALESCE(ps.Person_SurName, '') as \"Person_SurName\"
					,COALESCE(ps.Person_FirName, '') as \"Person_FirName\"
					,COALESCE(ps.Person_SecName, '') as \"Person_SecName\"
					,to_char(ps.Person_BirthDay, 'DD.MM.YYYY') as \"Person_BirthDay\"
					,dbo.Age2(ps.Person_BirthDay, :onDate) as \"Person_Age\"
					,dbo.Age_newborn(ps.Person_Birthday, CAST(:onDate as date)) as \"Person_AgeMonth\"
					,COALESCE(ps.Sex_id, 0) as \"Sex_id\"
					,COALESCE(p.Person_IsUnknown, 1) as \"Person_IsUnknown\"
				from
					v_Person_all ps 
					left join v_Person p  on p.Person_id = ps.Person_id
				where
					ps.Person_id = :Person_id
					and ps.PersonEvn_insDT <= CAST(:onDate as date)
				order by
					ps.PersonEvn_insDT desc
				LIMIT 1
			";
		}

		$queryParams = array(
			 'Person_id' => $Person_id
			,'onDate' => $onDate
		);

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$response = $result->result('array');

		if ( !is_array($response) || count($response) == 0 ) {
			return false;
		}

		return $response[0];
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadPostCombo($data) {
		$sql = "
			select
				id as \"Post_id\",
				name as \"Post_Name\"
			from
				persis.v_Post 
			where
				PostKind_id = 1
		";
   		$res = $this->db->query($sql, $data);
		if ( is_object($res) ) {
 	    	return $res->result('array');
		} else {
 	    	return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function SQLDebug($data) {
		$this->db->trans_begin();

		if (!empty($data['params'])) {
			$data['params'] = json_decode($data['params'], true);
		} else {
			$data['params'] = false;
		}

		$res = $this->db->query($data['query'], $data['params']);

		if ( is_object($res) ) {
 	    	$resp = $res->result('array');
			$this->db->trans_rollback(); // на всякий случай откатим транзакцию (изменения запрещены)
			return $resp;
		} else {
 	    	return false;
		}
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function getEvnUslugaCommonPrintData($data){

		if (empty($data['accessType'])) {unset($data['accessType']); }
		$this->load->helper("Xml");
		$this->load->model('Template_model', 'Template_model');
		$map = $this->Template_model->getEvnData($data);
		$document = $this->Template_model->getEvnDocument($map);
		ConvertFromWin1251ToUTF8($document);
		return $document;
	}

	/**
	 *
	 * @param type $row
	 * @param type $data
	 * @return type
	 */
	function getReceptTemplateName($row, $data) {
		if ( 1 == $row['ReceptType_Code'] ) {
			return 'recept_template_blank';
		}
		else {
			// Получаем настройки
			$options = getOptions();

			switch ( $options['recepts']['print_format'] ) {

				case 1:
					// В 3-х экземплярах, на двух листах формата А4 и двумя корешками, на Уфе с тремя корешками
					return 'receptgroup_template_list_1';
				break;

				case 2:
					// В 3-х экземплярах, на трех листах формата А5 и двумя корешками
					return 'receptgroup_template_list_2';
				break;

				case 3:
					// В 3-х экземплярах, на одном листе формата А4 и двумя корешками
					return 'receptgroup_template_list_3';
				break;

				default:
					echo "Необходимо задать формат печати в настройках рецепта";
					return false;
				break;
			}
		}

		If (isset($data['IsForLpu']) && $data['IsForLpu'] == 1) {
			return 'recept_template_list_0';
		}
	}
	/**
	 * Получить дозу из ответа
	 */
	function getDrugDose($row) {
		return (string)($row['Drug_Fas'] * $row['EvnRecept_Kolvo']); // Drug_Fas * EvnRecept_Kolvo
	}

	/**
	 * Получить полную дозу из ответа
	 */
	function getDrugFullDose($row) {
		return (string)$row['Drug_DoseFull']; // Drug_DoseFull
	}
	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function getEvnReceptPrintData($data){
		$this->load->helper('Barcode');
		$this->load->helper('Options');
		$this->load->model('Dlo_EvnRecept_model', 'evnRecept_model');
		if ( empty($data['EvnRecept_id']) || empty($data['Lpu_id']) ) {
			echo 'Неверно заданы параметры';
			return true;
		}


		// Получаем данные по рецепту

        if(in_array($_SESSION['region']['nick'],array('saratov','khak','pskov','ekb','astra','kareliya')))
		//if ($_SESSION['region']['nick'] == 'saratov')
			$response = $this->evnRecept_model->getReceptFieldsSaratov($data);
		else
			$response = $this->evnRecept_model->getReceptFields($data);

		if ( !is_array($response) || count($response) == 0 ) {
			echo 'Ошибка при получении данных по рецепту';
			return true;
		}
		//var_dump($response[0]['Drug_Fas']);die;
		$recept_template = $this->getReceptTemplateName($response[0], $data);
		//echo $recept_template;
        if (($response[0]['ReceptForm_Code'] == '1-МИ') && ($response[0]['ReceptType_Code']!=1))
            $recept_template .= '_1-mi';

		$drug_code_array           = array('&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;');
		$evn_recept_set_date_array = array('&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;');
		$lpu_unit_set_array        = array('&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;');
		$med_personal_code_array   = array('&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;');
		$person_address_array      = array('&nbsp;', '&nbsp;');
		$person_birthday_array     = array('&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;');
		$person_snils_array        = array('&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;');
		$person_state_array        = array('&nbsp;', '&nbsp;');
		$polis_ser_num_array       = array('&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;');
		$privilege_type_code_array = array('&nbsp;', '&nbsp;', '&nbsp;');
        $lpu_ogrn_array            = array('&nbsp;','&nbsp;','&nbsp;','&nbsp;','&nbsp;','&nbsp;','&nbsp;','&nbsp;','&nbsp;','&nbsp;','&nbsp;','&nbsp;','&nbsp;','&nbsp;','&nbsp;');
		$noz_form_code_array 	   = array('&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;');
		$recept_discount_array     = array('none', 'none');
		$recept_finance_array      = array('none', 'none', 'none');




		if ( $response[0]['Drug_Fas'] == 0 ) {
			$response[0]['Drug_Fas'] = 1;
		}

		$drug_dose_count = $this->getDrugDose($response[0]);

		$drug_dose_full = $this->getDrugFullDose($response[0]);

		$diag_code               = (string)$response[0]['Diag_Code']; // Diag_Code
		$drug_code               = (string)sprintf('%08d', $response[0]['Drug_Code']); // Drug_Code
		$drug_dose               = (string)$response[0]['Drug_Dose']; // Drug_Dose
		$drug_form_name          = (string)$response[0]['DrugForm_Name']; // DrugForm_Name
		$drug_is_kek             = $response[0]['Drug_IsKEK']; // Drug_IsKEK
		$drug_is_mnn             = $response[0]['Drug_IsMnn']; // Drug_IsMnn
		$drug_mnn_torg_code      = (string)$response[0]['DrugMnnTorg_Code']; // DrugMnnTorg_Code
		$drug_name               = nl2br((string)$response[0]['Drug_Name']); // Drug_Name
        if(($response[0]['ReceptForm_Code'] == '1-МИ') && ($_SESSION['region']['nick'] != 'saratov'))
            $drug_form_name = $response[0]['Drug_Name_mi1'];
        if(($response[0]['ReceptForm_Code'] == '1-МИ') && ($_SESSION['region']['nick'] == 'saratov'))
            $drug_form_name = $response[0]['DrugTorg_Name_mi1'];

		$OrgFarmacy_id        = $response[0]['OrgFarmacy_id']; // OrgFarmacy_id
        if(($_SESSION['region']['nick'] == 'ufa') && ($response[0]['ReceptForm_Code'] == '1-МИ')){ //Для Уфы для 1-МИ убираем печать информации о наличии лек средств в аптеке
            $options = getOptions();
            if($options['recepts']['print_format'] == 1)
                $farm_info = "";//"<br><br><br><br><br>";
            else
                $farm_info = "<br><br><br><br><br>";
        }
        else{
			if (!empty($OrgFarmacy_id) && $OrgFarmacy_id != 1) {
				$farm_info = "<div style='font-weight: bold; font-size: 12px;'>Наличие лекарственных препаратов:</div>
				<div>
					<div style='font-size: 9px; font-weight: bold;'>Аптека: {orgfarmacy_name}</div>
					<div style='font-size: 9px; font-weight: bold;'>Адрес: {orgfarmacy_howgo}</div>
					<div style='font-size: 9px; font-weight: bold;'>Телефон: {orgfarmacy_phone}</div>
				</div>";
			} else {
				$farm_info = "";
			}
        }
        $drugmnn_name            = $drug_form_name;
		$evn_recept_num          = (string)$response[0]['EvnRecept_Num']; // EvnRecept_Num
		$evn_recept_ser          = (string)$response[0]['EvnRecept_Ser']; // EvnRecept_Ser
		$evn_recept_set_date     = (string)$response[0]['EvnRecept_setDate']; // EvnRecept_setDate
		$evn_recept_set_day      = $response[0]['EvnRecept_setDay']; // EvnRecept_setDay
		$evn_recept_set_month    = $response[0]['EvnRecept_setMonth']; // EvnRecept_setMonth
		$evn_recept_set_year     = $response[0]['EvnRecept_setYear'] - 2000; // EvnRecept_setYear
		$evn_recept_signa        = (string)$response[0]['EvnRecept_Signa']; // EvnRecept_Signa
		$lpu_code                = $response[0]['Lpu_Code']; // Lpu_Code
        $lpu_name                = $response[0]['Lpu_Name'];
		$lpu_ogrn                = strlen((string)$response[0]['Lpu_Ogrn']) > 0 ? (string)$response[0]['Lpu_Ogrn'] : '&nbsp;'; // Lpu_Orgn
		$lpu_unit_set_code       = $response[0]['LpuUnitSet_Code']; // LpuUnitSet_Code
		$medpersonal_code        = str_pad($response[0]['MedPersonal_Code'], 6, '0', STR_PAD_LEFT); // MedPersonal_Code
		$medpersonal_fio         = (string)$response[0]['MedPersonal_Fio']; // MedPersonal_Fio
		$orgfarmacy_howgo        = $response[0]['OrgFarmacy_HowGo']; // OrgFarmacy_HowGo
		$orgfarmacy_name         = $response[0]['OrgFarmacy_Name']; // OrgFarmacy_Name
		$orgfarmacy_phone        = $response[0]['OrgFarmacy_Phone']; // OrgFarmacy_Phone
		$orgsmo_name_mi1         = (strlen($response[0]['OrgSmo_Name']) > 0 ? htmlspecialchars($response[0]['OrgSmo_Name']) : '&nbsp;');// для печати рецепта МИ-1 http://redmine.swan.perm.ru/issues/31345
		$orgsmo_name             = '&nbsp;';
		$person_address_array[0] = (string)$response[0]['Person_Address_1']; // Person_Address_1
		$person_address_array[1] = strlen((string)$response[0]['PersonCard_Code']) > 0 ? '&nbsp;' : ($_SESSION['region']['nick'] !='perm' ? '&nbsp' : (string)$response[0]['Person_Address_2']);
		$person_birthday         = (string)$response[0]['Person_Birthday']; // Person_Birthday
		$person_card_code        = strlen((string)$response[0]['PersonCard_Code']) > 0 ? (string)$response[0]['PersonCard_Code'] : ($_SESSION['region']['nick']!='perm' ? '&nbsp;' : $person_address_array[0]);
		$person_fio              = (string)$response[0]['Person_Fio']; // Person_Fio
		$person_snils            = (string)$response[0]['Person_Snils']; // Person_Snils
		$privilege_type_code     = sprintf('%03d', strval($response[0]['PrivilegeType_Code'])); // PrivilegeType_Code
		$recept_discount_code    = $response[0]['ReceptDiscount_Code']; // ReceptDiscount_Code
		$recept_finance_code     = $response[0]['ReceptFinance_Code']; // ReceptFinance_Code
		$recept_type_code        = $response[0]['ReceptType_Code']; // ReceptType_Code
		$recept_valid_code       = $response[0]['ReceptValid_Code']; // ReceptValid_Code
		//echo $recept_valid_code;
		$polis_ser_num           = '';
		$recept_valid_4          = '';
		$recept_valid_7          = '';
		$recept_valid_1          = '';
		$recept_valid_2          = '';
		$style_striked           = '1; text-decoration:underline;';

		if ( strlen(trim($response[0]['Polis_Ser'])) > 0 ) {
			$polis_ser_num .= trim($response[0]['Polis_Ser']) . ' ';
		}

		$polis_ser_num .= trim($response[0]['Polis_Num']);

		$polis_ser_num = substr($polis_ser_num, 0, 25);
		$polis_ser_num .= str_repeat(' ', 25 - strlen($polis_ser_num));

		if ( preg_match('/^\d{8}$/', $drug_code) ) {
			for ( $i = 0; $i < strlen($drug_code); $i++ ) {
				$drug_code_array[$i] = substr($drug_code, $i, 1);
			}
		}

		if ( preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $evn_recept_set_date) ) {
			for ( $i = 0; $i < strlen($evn_recept_set_date); $i++ ) {
				$evn_recept_set_date_array[$i] = substr($evn_recept_set_date, $i, 1);
			}
		}

		if ( $lpu_unit_set_code > 0 ) {
			for ( $i = 4; $i >= 0; $i-- ) {
				$lpu_unit_set_array[$i] = $lpu_unit_set_code - floor($lpu_unit_set_code / 10) * 10;
				$lpu_unit_set_code = floor($lpu_unit_set_code / 10);
			}
		}
		if($data['session']['region']['nick']=='saratov'){ //https://redmine.swan.perm.ru/issues/27883
			$lpu_unit_set_array[0] = '';
			$lpu_unit_set_array[1] = '';
			$lpu_unit_set_array[2] = '';
			$lpu_unit_set_array[3] = '';
			$lpu_unit_set_array[4] = '';
			for($i=0;$i<5;$i++){
				if(isset($response[0]['Lpu_Ouz'][$i])){
					$lpu_unit_set_array[$i] = $response[0]['Lpu_Ouz'][$i];
				}
			}
		}

        for ( $i = 0; $i < strlen($medpersonal_code); $i++ ) {
            $med_personal_code_array[$i] = substr($medpersonal_code, $i, 1);
        }

		if ( preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $person_birthday) ) {
			for ( $i = 0; $i < strlen($person_birthday); $i++ ) {
				$person_birthday_array[$i] = substr($person_birthday, $i, 1);
			}
		}

		if ( preg_match('/^\d{11}$/', $person_snils) ) {
			$person_snils_temp = substr($person_snils, 0, 3) . '-' . substr($person_snils, 3, 3) . '-' . substr($person_snils, 6, 3) . ' ' . substr($person_snils, 9, 2);
			for ( $i = 0; $i < strlen($person_snils_temp); $i++ ) {
				$person_snils_array[$i] = substr($person_snils_temp, $i, 1);
			}
		}

		if ( preg_match('/^\d{3}$/', $privilege_type_code) ) {
			for ( $i = 0; $i < strlen($privilege_type_code); $i++ ) {
				$privilege_type_code_array[$i] = substr($privilege_type_code, $i, 1);
			}
		}

        for ($i=0; $i < strlen($lpu_ogrn); $i++) {
            $lpu_ogrn_array[$i] = substr($lpu_ogrn,$i,1);
        }

		for ( $i = 0; $i < strlen($diag_code); $i++ ) {
			$noz_form_code_array[$i] = substr($diag_code, $i, 1);
		}

		if ( ($recept_discount_code >= 1) && ($recept_discount_code <= 2) ) {
			$recept_discount_code = $recept_discount_code - 1;
			$recept_discount_array[$recept_discount_code] = '1px solid #000';
		}

		if ( ($recept_finance_code >= 1) && ($recept_finance_code <= 3) ) {
			$recept_finance_array[$recept_finance_code - 1] = '1px solid #000';
		}

		switch ( $recept_valid_code ) {
			case 4:
				$recept_valid_4 = $style_striked;
				$recept_valid_code = 4;
			break;

			case 7:
				$recept_valid_7 = $style_striked;
				$recept_valid_code = 7;
			break;
		    case 1:
				$recept_valid_1 = $style_striked;
				$recept_valid_code = 1;
			break;
		    case 2:
				$recept_valid_2 = $style_striked;
				$recept_valid_code = 2;
			break;
            /*
			case 3:
				$recept_valid = '14 дней';
				$recept_valid_code = 0;
			break;
            */
		}

		$max_i = (strlen($polis_ser_num) <= 24 ? strlen($polis_ser_num) : 24);
		for ( $i = 0; $i < $max_i; $i++ ) {
			if ( substr($polis_ser_num, $i, 1) != ' ' ) {
				$polis_ser_num_array[$i] = substr($polis_ser_num, $i, 1);
			}
		}

		if ( $drug_is_mnn == 1 ) {
			$drug_is_mnn = 0;
		}
		else if ( $drug_is_mnn == 0 ) {
			$drug_is_mnn = 1;
		}

		$person_state_array[0] = $person_address_array[0];
		$person_state_array[1] = $person_address_array[1];

		$this->load->model('Barcode_model');
		$barcode_string = $this->Barcode_model->getBinaryString($response[0]);

		$drug_name = str_replace('+', ' + ', $drug_name);
		 if(in_array($_SESSION['region']['nick'],array('saratov','khak')))
			$drug_form_name = '';
		$parse_data = array(
            'farm_info' => $farm_info,
			'address_string_1' => $person_address_array[0],
			'address_string_2' => $person_address_array[1],
			'ambul_card_num' => $person_card_code,
			'barcode_string' => urlencode($barcode_string),
            'drugmnn_name' => $drugmnn_name,
			'drug_dose' => $drug_dose_full,
			'drug_form' => $drug_form_name,
			'drug_kolvo' => $drug_dose_count . ' ' . $response[0]['Drug_Fas_Ed'],
			'drug_name' => $drug_name,
			'lpu_ogrn' => $lpu_ogrn,
			'lpu_stamp_1' => $lpu_unit_set_array[0],
			'lpu_stamp_2' => $lpu_unit_set_array[1],
			'lpu_stamp_3' => $lpu_unit_set_array[2],
			'lpu_stamp_4' => $lpu_unit_set_array[3],
			'lpu_stamp_5' => $lpu_unit_set_array[4],
			'medpersonal_code_1' => $med_personal_code_array[0],
			'medpersonal_code_2' => $med_personal_code_array[1],
			'medpersonal_code_3' => $med_personal_code_array[2],
			'medpersonal_code_4' => $med_personal_code_array[3],
			'medpersonal_code_5' => $med_personal_code_array[4],
			'medpersonal_code_6' => $med_personal_code_array[5],
			'medpersonal_fio' => $medpersonal_fio,
			'noz_form_code' => $diag_code,
			'orgfarmacy_howgo' => $orgfarmacy_howgo,
			'OrgFarmacy_id' => $OrgFarmacy_id,
			'orgfarmacy_name' => $orgfarmacy_name,
			'orgfarmacy_phone' => $orgfarmacy_phone,
			'orgsmo_name' => $orgsmo_name,
            'orgsmo_name_mi1' =>$orgsmo_name_mi1, // для печати рецепта МИ-1 http://redmine.swan.perm.ru/issues/31345
            'Lpu_Ouz' => $response[0]['Lpu_Ouz'],
			'person_birthday_1' => $person_birthday_array[0],
			'person_birthday_2' => $person_birthday_array[1],
			'person_birthday_3' => $person_birthday_array[3],
			'person_birthday_4' => $person_birthday_array[4],
			'person_birthday_5' => $person_birthday_array[6],
			'person_birthday_6' => $person_birthday_array[7],
			'person_birthday_7' => $person_birthday_array[8],
			'person_birthday_8' => $person_birthday_array[9],
			'person_fio' => $person_fio,
            'person_snils' => $person_snils,
			'person_snils_1' => $person_snils_array[0],
			'person_snils_2' => $person_snils_array[1],
			'person_snils_3' => $person_snils_array[2],
			'person_snils_4' => $person_snils_array[3],
			'person_snils_5' => $person_snils_array[4],
			'person_snils_6' => $person_snils_array[5],
			'person_snils_7' => $person_snils_array[6],
			'person_snils_8' => $person_snils_array[7],
			'person_snils_9' => $person_snils_array[8],
			'person_snils_10' => $person_snils_array[9],
			'person_snils_11' => $person_snils_array[10],
			'person_snils_12' => $person_snils_array[11],
			'person_snils_13' => $person_snils_array[12],
			'person_snils_14' => $person_snils_array[13],
            'polis_ser_num'   => $polis_ser_num,
			'polis_ser_num_1' => $polis_ser_num_array[0],
			'polis_ser_num_2' => $polis_ser_num_array[1],
			'polis_ser_num_3' => $polis_ser_num_array[2],
			'polis_ser_num_4' => $polis_ser_num_array[3],
			'polis_ser_num_5' => $polis_ser_num_array[4],
			'polis_ser_num_6' => $polis_ser_num_array[5],
			'polis_ser_num_7' => $polis_ser_num_array[6],
			'polis_ser_num_8' => $polis_ser_num_array[7],
			'polis_ser_num_9' => $polis_ser_num_array[8],
			'polis_ser_num_10' => $polis_ser_num_array[9],
			'polis_ser_num_11' => $polis_ser_num_array[10],
			'polis_ser_num_12' => $polis_ser_num_array[11],
			'polis_ser_num_13' => $polis_ser_num_array[12],
			'polis_ser_num_14' => $polis_ser_num_array[13],
			'polis_ser_num_15' => $polis_ser_num_array[14],
			'polis_ser_num_16' => $polis_ser_num_array[15],
			'polis_ser_num_17' => $polis_ser_num_array[16],
			'polis_ser_num_18' => $polis_ser_num_array[17],
			'polis_ser_num_19' => $polis_ser_num_array[18],
			'polis_ser_num_20' => $polis_ser_num_array[19],
			'polis_ser_num_21' => $polis_ser_num_array[20],
			'polis_ser_num_22' => $polis_ser_num_array[21],
			'polis_ser_num_23' => $polis_ser_num_array[22],
			'polis_ser_num_24' => $polis_ser_num_array[23],
			'polis_ser_num_25' => $polis_ser_num_array[24],
			'privilege_type_code_1' => $privilege_type_code_array[0],
			'privilege_type_code_2' => $privilege_type_code_array[1],
			'privilege_type_code_3' => $privilege_type_code_array[2],
            'lpu_name'   => $lpu_name,
            'lpu_ogrn_0' => $lpu_ogrn_array[0],
            'lpu_ogrn_1' => $lpu_ogrn_array[1],
            'lpu_ogrn_2' => $lpu_ogrn_array[2],
            'lpu_ogrn_3' => $lpu_ogrn_array[3],
            'lpu_ogrn_4' => $lpu_ogrn_array[4],
            'lpu_ogrn_5' => $lpu_ogrn_array[5],
            'lpu_ogrn_6' => $lpu_ogrn_array[6],
            'lpu_ogrn_7' => $lpu_ogrn_array[7],
            'lpu_ogrn_8' => $lpu_ogrn_array[8],
            'lpu_ogrn_9' => $lpu_ogrn_array[9],
            'lpu_ogrn_10' => $lpu_ogrn_array[10],
            'lpu_ogrn_11' => $lpu_ogrn_array[11],
            'lpu_ogrn_12' => $lpu_ogrn_array[12],
            'lpu_ogrn_13' => $lpu_ogrn_array[13],
            'lpu_ogrn_14' => $lpu_ogrn_array[14],
			'noz_form_code_1' => $noz_form_code_array[0],
			'noz_form_code_2' => $noz_form_code_array[1],
			'noz_form_code_3' => $noz_form_code_array[2],
			'noz_form_code_4' => $noz_form_code_array[3],
			'noz_form_code_5' => $noz_form_code_array[4],
			'recept_date' => $evn_recept_set_date,
			'recept_date_1' => $evn_recept_set_date_array[0],
			'recept_date_2' => $evn_recept_set_date_array[1],
			'recept_date_3' => $evn_recept_set_date_array[3],
			'recept_date_4' => $evn_recept_set_date_array[4],
			'recept_date_5' => $evn_recept_set_date_array[6],
			'recept_date_6' => $evn_recept_set_date_array[7],
			'recept_date_7' => $evn_recept_set_date_array[8],
			'recept_date_8' => $evn_recept_set_date_array[9],
			'recept_discount_1' => $recept_discount_array[0],
			'recept_discount_2' => $recept_discount_array[1],
            'recept_discount_mi1' => '1px solid #000',
			'recept_finance_1' => $recept_finance_array[0],
			'recept_finance_2' => $recept_finance_array[1],
			'recept_finance_3' => $recept_finance_array[2],
			'recept_num' => $evn_recept_num,
			'recept_ser' => $evn_recept_ser,
			'recept_template_title' => 'Печать рецепта ' . $evn_recept_ser . ' ' . $evn_recept_num,
			'recept_valid_4' => $recept_valid_4,
			'recept_valid_7' => $recept_valid_7,
			'recept_valid_1' => $recept_valid_1,
			'recept_valid_2' => $recept_valid_2,
			'signa' => $evn_recept_signa,

			'drug_code_1' => $drug_code_array[0],
			'drug_code_2' => $drug_code_array[1],
			'drug_code_3' => $drug_code_array[2],
			'drug_code_4' => $drug_code_array[3],
			'drug_code_5' => $drug_code_array[4],
			'drug_code_6' => $drug_code_array[5],
			'drug_code_7' => $drug_code_array[6],
			'drug_code_8' => $drug_code_array[7],
			'evn_recept_id' => $data['EvnRecept_id'],
			'person_state_1' => $person_state_array[0],
			'person_state_2' => $person_state_array[1]
		);

		// array_walk($data, 'htmlspecialchars');
		return $this->parser->parse($recept_template, $parse_data, true);
	}
	/**
	 *
	 * @return type
	 */
	function getEvnReceptDarkSidePrintData(){
		$this->load->helper('Options');

		// Получаем настройки
		$options = getOptions();

		switch ( $options['recepts']['print_format'] ) {
			case 1:
				$recept_template = 'receptgroup_dark_side_template_list_1';
			break;

			case 2:
				$recept_template = 'receptgroup_dark_side_template_list_2';
			break;

			case 3:
				$recept_template = 'receptgroup_dark_side_template_list_3';
			break;

			default:
				echo "Необходимо задать формат печати в настройках рецепта";
				return false;
			break;
		}

		return $this->parser->parse($recept_template, array(),true);
	}
	/**
	 *
	 * @param array $data
	 * @return bool
	 */
	function getEvnXmlPrintData($data){
		try {
			$this->load->library('swXmlTemplate');
			$instance = swXmlTemplate::getEvnXmlModelInstance();
			$xml_data = $instance->doLoadPrintData($data);
			return swEvnXml::doPrint(
				$xml_data[0],
				$data['session']['region']['nick'],
				false,
				false,
				true
			);
		} catch (Exception $e) {
			echo $e->getMessage();
			return false;
		}
	}
	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function getEvnDirectionPrintData($data){
		$this->load->model('EvnDirection_model', 'dirmodel');

		$arMonthOf = array(
			1 => "января",
			2 => "февраля",
			3 => "марта",
			4 => "апреля",
			5 => "мая",
			6 => "июня",
			7 => "июля",
			8 => "августа",
			9 => "сентября",
			10 => "октября",
			11 => "ноября",
			12 => "декабря",
		);

		// Получаем данные по направлению
		$response = $this->dirmodel->getEvnDirectionFields($data);

		if ( !is_array($response) || count($response) == 0 ) {
			echo 'Ошибка при получении данных по направлению';
			return false;
		}

		$print_data = !empty($response[0]) ? $response[0] : $response;
		$print_data['EvnDirection_Num'] = str_pad($print_data['EvnDirection_Num'], 6, "0", STR_PAD_LEFT);

		$dirstring = "";
		$dirstring .= ( $print_data['DirType_id'] == 1 || $print_data['DirType_id'] == 5 ) ? "<u>на госпитализацию</u>," : "на госпитализацию,";
		$dirstring .= ( $print_data['DirType_id'] == 4 ) ? " <u>восстановительное лечение</u>," : " восстановительное лечение,";
		$dirstring .= ( $print_data['DirType_id'] == 2 ) ? " <u>обследование</u>," : " обследование,";
		$dirstring .= ( in_array($print_data['DirType_id'], array(3, 11)) ) ? " <u>консультацию</u>," : " консультацию,";
		$dirstring .= ( $print_data['DirType_id'] == 6 ) ? " <u>осмотр с целью госпитализации,</u>" : " осмотр с целью госпитализации,";
		$dirstring .= ( $print_data['DirType_id'] == 10 ) ? " <u>исследование</u>" : " исследование";
		$print_data['dirstring'] = $dirstring;

		If ($print_data['DirType_id'] != 5)
			$HospType = 1;
		else
			$HospType = 2;
		$hospstring = "";
		$hospstring .= ( $HospType == 1 ) ? " <u>плановая</u>," : " плановая,";
		$hospstring .= ( $HospType == 2 ) ? " <u>экстренная</u>" : " экстренная";
        if (!( $print_data['DirType_id'] == 1 || $print_data['DirType_id'] == 5 ))
            $hospstring = "плановая, экстренная";
		$print_data['hospstring'] = $hospstring;

		if ( trim($print_data['SectionContact_Phone']) == '' )
			$print_data['Сontact_Phone'] = "Контактные телефоны : {$print_data['Contact_Phone']}";
		else
			$print_data['Сontact_Phone'] = "Контактные телефоны : {$print_data['SectionContact_Phone']}";

		$print_data['RecMP'] .= "&nbsp;";

        if ($print_data['MedPersonal_did'] == '') {
            $print_data['RecDate'] = $print_data['RecDate']."&nbsp;";
        } else {
            $print_data['RecDate'] = "Живая очередь";
        }

		If ($print_data['TimetableGraf_id'])
				$print_data['TType'] = "Врач";
		else
				$print_data['TType'] = "Отделение";

		$print_data['JobPost'] = $print_data['Job_Name']."&nbsp;".$print_data['Post_Name'];

		$print_data['Dir_Day'] = str_pad($print_data['Dir_Day'], 2, "0", STR_PAD_LEFT);
		$print_data['Dir_Month'] = str_pad($arMonthOf[$print_data['Dir_Month']], 16, " ", STR_PAD_BOTH);
		$print_data['Dir_Year'] = $print_data['Dir_Year'];
		$print_data['MedDol'] = str_pad($print_data['PostMed_Name'], 30, "_", STR_PAD_RIGHT);

		$print_data['region_nick'] =
			(isset($data['session'])
				&& isset($data['session']['region'])
				&& isset($data['session']['region']['nick']))
					? $data['session']['region']['nick']
					: null;

		if($print_data['region_nick']=='kareliya'){

			return $this->parser->parse('printgroup_evndirection_kareliya', $print_data,true);

		} else {

            if($print_data['region_nick']=='kz') {
                return $this->parser->parse('printgroup_evndirection_kz', $print_data,true);
            } else {
				$parsed = $this->parser->parse('printgroup_evndirection', $print_data, true, false, (defined('USE_UTF') && USE_UTF));
				return $parsed;
			}
		}
	}

	/**
	 * Загрузка грида графиков дежурств
	 */
	function loadWorkGraphGrid($data){
		$params = array();
		$filters = "";
		$join = "";
		if(isset($data['Lpu_id'])){
			$params['Lpu_id'] = $data['Lpu_id'];
			$filters .= " and MSF.Lpu_id = :Lpu_id";
		}
		if(isset($data['MedStaffFact_id'])){
			$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
			$filters .= " and WG.MedStaffFact_id = :MedStaffFact_id";
		}
		if(isset($data['LpuSection_id'])){
			$params['LpuSection_id'] = $data['LpuSection_id'];
			$join = "
				INNER JOIN LATERAL(

					select WGLS.WorkGraphLpuSection_id
					from v_WorkGraphLpuSection WGLS 
					where WGLS.WorkGraph_id = WG.WorkGraph_id
					and WGLS.LpuSection_id = :LpuSection_id
				) S on true
			";
			//$filters .= " and WG.LpuSection_id = :LpuSection_id";
		}
		if(isset($data['LpuBuilding_id'])){
			$lpusection_where = ' and (1=1) ';
			if(isset($data['LpuSection_id'])){
				$params['LpuSection_id'] = $data['LpuSection_id'];
				$lpusection_where = ' and  WGLS.LpuSection_id = :LpuSection_id';
			}
			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
			$join = "
				INNER JOIN LATERAL(

					select WGLS.WorkGraphLpuSection_id
					from v_WorkGraphLpuSection WGLS 
					inner join v_LpuSection LS  on (LS.LpuSection_id = WGLS.LpuSection_id and LS.LpuBuilding_id = :LpuBuilding_id)
					{$lpusection_where}
					where WGLS.WorkGraph_id = WG.WorkGraph_id
				) B on true
			";
		}
		if ( isset($data['WorkGraph_Date'][0]) && isset($data['WorkGraph_Date'][1])) {
			//$filters .= " and WG.Evn_setDate >= cast(:Evn_setDate_0 as datetime)";
			$filters .= " and not ((WG.WorkGraph_begDT < :WorkGraph_begDT and WG.WorkGraph_endDT < :WorkGraph_begDT) or (WG.WorkGraph_begDT > :WorkGraph_endDT and WG.WorkGraph_endDT > :WorkGraph_endDT))";
			$params['WorkGraph_begDT'] = $data['WorkGraph_Date'][0];
			$params['WorkGraph_endDT'] = $data['WorkGraph_Date'][1];
		}

		$query = "
			select
			-- select
				WG.WorkGraph_id as \"WorkGraph_id\",
				COALESCE(MSF.Person_SurName, '') as \"Person_SurName\",
				COALESCE(MSF.Person_FirName, '') as \"Person_FirName\",
				COALESCE(MSF.Person_SecName, '') as \"Person_SecName\",
				COALESCE(to_char(WG.WorkGraph_begDT, 'DD.MM.YYYY'), '') as \"WorkGraph_begDate\",
				COALESCE(to_char(WG.WorkGraph_endDT, 'DD.MM.YYYY'), '') as \"WorkGraph_endDate\",
				COALESCE(PUC.PMUser_Name, '') as \"PMUser_Name\",
				'' as \"WorkGraph_Sections\"
			-- end select
			from
			 -- from
				v_WorkGraph WG 
				left join v_MedStaffFact MSF  on MSF.MedStaffFact_id = WG.MedStaffFact_id
				left join v_pmUserCache PUC  on PUC.PMUser_id = WG.pmUser_insID
				{$join}
			-- end from
			where
			-- where
			(1=1)
			{$filters}
			-- end where
			order by
			-- order by
			WG.WorkGraph_begDT desc
			-- end order by
		";

		//echo getDebugSQL($query,$params);die;
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);

		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			if($count>0)
			{
				$query_lpu_section = "
						select LS.LpuSection_Name as \"LpuSection_Name\"
						from v_WorkGraphLpuSection WGLS 
						left join v_LpuSection LS  on LS.LpuSection_id = WGLS.LpuSection_id
						where WGLS.WorkGraph_id = :WorkGraph_id
					";
				for($i=0;$i<count($response['data']);$i++) {
					$result_lpusection = $this->db->query($query_lpu_section, array('WorkGraph_id' => $response['data'][$i]['WorkGraph_id']));

					if (is_object($result_lpusection)) {
						$result_lpusection = $result_lpusection->result('array');
						if (count($result_lpusection) > 0) {
							foreach ($result_lpusection as $sectiondata) {
								$response['data'][$i]['WorkGraph_Sections'] .= ", " . $sectiondata['LpuSection_Name'];
							}
							$response['data'][$i]['WorkGraph_Sections'] = substr($response['data'][$i]['WorkGraph_Sections'],1);
						}
					}
				}
			}
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Загрузка списка отделений
	 */
	function loadWorkGraphLpuSectionGrid($data){
		$params = array();
		$params['WorkGraph_id'] = $data['WorkGraph_id'];
		$query = "
			select
				WGLS.WorkGraph_id as \"WorkGraph_id\",
				WGLS.WorkGraphLpuSection_id as \"WorkGraphLpuSection_id\",
				COALESCE(LS.LpuSection_Code, '') as \"LpuSection_Code\",
				COALESCE(LS.LpuSection_Name, '') as \"LpuSection_Name\",
				COALESCE(LB.LpuBuilding_Name, '') as \"LpuBuilding_Name\"
			from v_WorkGraphLpuSection WGLS 
			left join v_LpuSection LS  on LS.LpuSection_id = WGLS.LpuSection_id
			left join v_LpuBuilding LB  on LB.LpuBuilding_id = LS.LpuBuilding_id
			where WGLS.WorkGraph_id = :WorkGraph_id
		";
		$result = $this->db->query($query,$params);
		if(is_object($result))
		{
			$result = $result->result('array');
			$response = array();
			$response['data'] = $result;
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Получение конкретного id строки графика дежурств
	 */
	function LoadWorkGraphLpuSection($data){
		$params = array();
		$params['WorkGraph_id'] = $data['WorkGraph_id'];
		$params['LpuSection_id'] = $data['LpuSection_id'];
		$query = "
			select
			WGLS.WorkGraphLpuSection_id as \"WorkGraphLpuSection_id\"
			from v_WorkGraphLpuSection WGLS 
			where WGLS.WorkGraph_id = :WorkGraph_id
			and WGLS.LpuSection_id = :LpuSection_id
		";
		$result = $this->db->query($query,$params);
		if(is_object($result))
		{
			$result = $result->result('array');
			return $result;
		}
		else
			return false;
	}

	/**
	 * Получение данных графика дежурств
	 */
	function loadWorkGraphData($data){
		$params = array();
		$params['WorkGraph_id'] = $data['WorkGraph_id'];
		$query = "
			select WG.MedStaffFact_id as \"MedStaffFact_id\",
			COALESCE(to_char(WG.WorkGraph_begDT, 'DD.MM.YYYY'), '') as \"WorkGraph_begDate\",
			COALESCE(to_char(WG.WorkGraph_endDT, 'DD.MM.YYYY'), '') as \"WorkGraph_endDate\"
			from v_WorkGraph WG 
			where WG.WorkGraph_id = :WorkGraph_id
		";
		$result = $this->db->query($query,$params);
		if(is_object($result))
		{
			$result = $result->result('array');
			return $result;
		}
		else
			return false;
	}

	/**
	 * Сохранение графика дежурств
	 */
	function saveWorkGraph($data){
		$params = array();
		$procedure = 'p_WorkGraph_ins';
		$params['WorkGraph_id'] = null;

		//Предварительно удалим помеченные к удалению записи
		$del_ids = (array) json_decode($data['del_ids']);
		$del_params = array();
		for ($i=0; $i<count($del_ids); $i++){
			$del_params['WorkGraphLpuSection_id'] = $del_ids[$i];
			$this->deleteWorkGraphLpuSection($del_params);
		}

		if(isset($data['WorkGraph_id']) && $data['WorkGraph_id'] > 0)
		{
			$procedure = 'p_WorkGraph_upd';
			$params['WorkGraph_id'] = $data['WorkGraph_id'];
		}
		$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
		$params['WorkGraph_begDT'] = date('Y-m-d', strtotime($data['WorkGraph_begDate']));//$data['WorkGraph_begDate'];
		$params['WorkGraph_endDT'] = date('Y-m-d', strtotime($data['WorkGraph_endDate']));//$data['WorkGraph_endDate'];
		//Проверка пересечения дат для текущего MedStaffFact:
		$params_check = array(
			'WorkGraph_id' => ($data['WorkGraph_id']==null)?-1:$data['WorkGraph_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'WorkGraph_begDT' => $params['WorkGraph_begDT'],
			'WorkGraph_endDT' => $params['WorkGraph_endDT']
		);
		$query_check = "
			select WG.WorkGraph_id as \"WorkGraph_id\"
			from v_WorkGraph WG 
			where WG.MedStaffFact_id = :MedStaffFact_id
			and (
				(:WorkGraph_begDT >= WG.WorkGraph_begDT and :WorkGraph_begDT <= WG.WorkGraph_endDT)
				or
				(:WorkGraph_endDT >= WG.WorkGraph_begDT and :WorkGraph_endDT <= WG.WorkGraph_endDT)
			)
			and WG.WorkGraph_id <> :WorkGraph_id
		";
		//echo getDebugSQL($query_check,$params);die;
		$result_query = $this->db->query($query_check,$params_check);
		if(is_object($result_query)){
			$result_query = $result_query->result('array');
			if(count($result_query) > 0){
				return array(0 => array('success' => false, 'Error_Msg' => 'Введенный диапазон дат пересекается с ранее добавленным по этому сотруднику', 'Error_Code' => 1));
			}
		}
		$params['pmUser_id'] = $data['pmUser_id'];
		$query = "
		    SELECT 
		        WorkGraph_id as \"WorkGraph_id\", 
		        error_code as \"Error_Code\", 
		        error_message as \"Error_Msg\"
		    FROM {$procedure} (
		        WorkGraph_id => :WorkGraph_id,
			    MedStaffFact_id => :MedStaffFact_id,
			    WorkGraph_begDT => :WorkGraph_begDT,
			    WorkGraph_endDT => :WorkGraph_endDT,
			    pmUser_id => :pmUser_id
		    )
		";
		//echo getDebugSQL($query,$params);die;
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(0 => array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
	}

	/**
	 * Удаление строки графика дежурств
	 */
	function deleteWorkGraph($data)
	{
		$params = array();
		$params['WorkGraph_id'] = $data['WorkGraph_id'];
		$query_del = "delete from WorkGraphLpuSection where WorkGraph_id = :WorkGraph_id";
		$result_del = $this->db->query($query_del,$params);
		$query = "
		    SELECT 
		        error_code as \"Error_Code\", 
		        error_message as \"Error_Msg\"
		    FROM p_WorkGraph_del (
		        WorkGraph_id => :WorkGraph_id
		    )
		";
		//echo getDebugSQL($query,$params);die;
		$res = $this->db->query($query,$params);
		if (is_object($res))
		{
			return $res->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 * Сохранение отделений для графика дежурств
	 */
	function saveWorkGraphLpuSection($data)
	{
		$params = array();
		$new_ids = array();

		$params['WorkGraph_id'] = $data['WorkGraph_id'];
		$params['LpuSection_id'] = $data['LpuSection_id'];
		$params['pmUser_id'] = $data['pmUser_id'];
		$params['WorkGraphLpuSection_id'] = NULL;

		$allow_add = 1;
		$query_check = "
			select count(WorkGraphLpuSection_id) as ctn
			from v_WorkGraphLpuSection 
			where WorkGraph_id = :WorkGraph_id
			and LpuSection_id = :LpuSection_id
		";

		if(isset($data['WorkGraphLpuSection_id']) && $data['WorkGraphLpuSection_id']>0)
		{
			$params['WorkGraphLpuSection_id'] = $data['WorkGraphLpuSection_id'];
			$procedure = 'p_WorkGraphLpuSection_upd';
		}
		else
		{
			$procedure = 'p_WorkGraphLpuSection_ins';
		}
		$query = "
		    SELECT 
		        WorkGraphLpuSection_id as \"WorkGraphLpuSection_id\", 
		        error_code as \"Error_Code\", 
		        error_message as \"Error_Msg\"
		    FROM {$procedure} (
		        WorkGraphLpuSection_id => :WorkGraphLpuSection_id,
				WorkGraph_id => :WorkGraph_id,
				LpuSection_id => :LpuSection_id,
				pmUser_id => :pmUser_id
		    )
		";
		//$this->db->trans_begin();
		if(!isset($data['LpuSection_id']))
		{
			//var_dump($data['LpuSectionList']);die;
			$LpuSectionList = (array) json_decode($data['LpuSectionList']);
			$j=0;
			for ($i=0;$i<count($LpuSectionList);$i++){
				$allow_add = 1;
				$params['LpuSection_id'] = $LpuSectionList[$i];
				$result_check = $this->db->query($query_check,$params);
				if(is_object($result_check)){
					$result_check = $result_check->result('array');
					if($result_check[0]['ctn'] > 0)
					{
						$allow_add = 0;
					}
				}
				if($allow_add == 1){
					$result = $this->db->query($query, $params);
					$res = $result->result('array');
					$new_ids[]= $res[0]['WorkGraphLpuSection_id'];
					$j = 1;
				}
			}
		}
		else{
			$params['LpuSection_id'] = $data['LpuSection_id'];
			$result_check = $this->db->query($query_check,$params);
			if(is_object($result_check)){
				$result_check = $result_check->result('array');
				if($result_check[0]['ctn'] > 0)
				{
					$allow_add = 0;
					return array(0 => array('success' => false, 'Error_Msg' => 'Данное отделение уже добавлено в график дежурств'));
				}
			}
			if($allow_add==1)
			{
				$result = $this->db->query($query, $params);
				$res = $result->result('array');
				$new_ids[]= $res[0]['WorkGraphLpuSection_id'];
			}
		}

		if ( isset($result) && is_object($result) ) {
			$res = $result->result('array');
			$res[0]['new_ids'] = $new_ids;
			return $res;
		}
		else {
			if($j==0){
				return array(0 => array('success' => false, 'Error_Msg' => 'Все данные отделение уже добавлено в график дежурств'));
			}
			return array(0 => array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
	}

	/**
	 * Удаление отделений из графика дежурств
	 */
	function deleteWorkGraphLpuSection($data){
		$params = array();
		$params['WorkGraphLpuSection_id'] = $data['WorkGraphLpuSection_id'];
		$query = "
		    SELECT 
		        error_code as \"Error_Code\", 
		        error_message as \"Error_Msg\"
		    FROM p_WorkGraphLpuSection_del (
		        WorkGraphLpuSection_id => :WorkGraphLpuSection_id
		    )
		";
		//echo getDebugSQL($query,$params);die;
		$res = $this->db->query($query,$params);
		if (is_object($res))
		{
			return $res->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 * Удаление списка отделений из графика дежурств
	 */
	function deleteWorkGraphLpuSectionArray($data){
		$new_ids = (array) json_decode($data['new_ids']);
		$new_params = array();
		for ($i=0; $i<count($new_ids); $i++){
			$new_params['WorkGraphLpuSection_id'] = $new_ids[$i];
			$this->deleteWorkGraphLpuSection($new_params);
		}
		return array(0 => array('success' => true));
	}

	/**
	 * Получение списка работников из руководства
	 */
	function loadOrgHeadList($data){
		$params = array();
		$params['Lpu_id'] = $data['Lpu_id'];
		$params['Post_id'] = $data['Post_id'];
		$query = "
			select
				oh.Person_id as \"Person_id\",
				rtrim(ps.Person_SurName) || ' ' || rtrim(ps.Person_FirName) || ' ' || rtrim(COALESCE(ps.Person_SecName, '')) as \"OrgHeadPerson_Fio\"
			from v_OrgHead oh 
			inner join v_OrgHeadPost ohp  on ohp.OrgHeadPost_id = oh.OrgHeadPost_id
			inner join v_PersonState ps  on ps.Person_id = oh.Person_id
			WHERE
				oh.Lpu_id = :Lpu_id
				and oh.LpuUnit_id is null
				and ohp.OrgHeadPost_id = :Post_id
		";
		$result = $this->db->query($query,$params);
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
	 * Получение списка работников (Person_id и ФИО)
	 */
	function loadMedPersList($data){
		$params = array();
		$params['Lpu_id'] = $data['Lpu_id'];
		$query = "
			select distinct
					ps.Person_id as \"Person_id\",
					ps.Person_SurName as \"Person_SurName\",
					ps.Person_FirName as \"Person_FirName\",
					ps.Person_SecName as \"Person_SecName\",
					rtrim(ps.Person_SurName) || ' ' || rtrim(ps.Person_FirName) || ' ' || rtrim(COALESCE(ps.Person_SecName, '')) as \"IspolnPerson_Fio\"
				from v_MedStaffFact msf 
				inner join v_PersonState ps  on ps.Person_id = msf.Person_id
				where msf.Lpu_id = :Lpu_id
				and (msf.WorkData_endDate is null or msf.WorkData_endDate > dbo.tzGetDate())
		";
		$result = $this->db->query($query,$params);
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
	 * Получение списка возможных усуг для диспанцеризаций и осмотров
	 */
	function loadDispUslugaComplex($data){
		$params = array();
		$params['DispClass_id'] = $data['DispClass_id'];

		$params['getdate'] = $this->getFirstResultFromQuery("select dbo.tzGetDate()");

		$query = "
			select distinct
				UC.UslugaComplex_id as \"UslugaComplex_id\",
				case when ST.SurveyType_Code = 2 then '' else UC.UslugaComplex_Code end as \"UslugaComplex_Code\",
				case when ST.SurveyType_Code = 2 then ST.SurveyType_Name else UC.UslugaComplex_Name end as \"UslugaComplex_Name\"
			from v_SurveyType ST 
				inner join v_SurveyTypeLink STL  on (STL.SurveyType_id = ST.SurveyType_id and STL.DispClass_id = :DispClass_id)
				inner join v_UslugaComplex UC  on UC.UslugaComplex_id = STL.UslugaComplex_id
			where
				(1=1)
				and (UC.UslugaComplex_endDT is null or UC.UslugaComplex_endDT >= CAST(:getdate as date))
				and (STL.SurveyTypeLink_begDate is null or STL.SurveyTypeLink_begDate <= CAST(:getdate as date))
				and (STL.SurveyTypeLink_endDate is null or STL.SurveyTypeLink_endDate >= CAST(:getdate as date))
			order by 3
		";
		$result = $this->db->query($query,$params);
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
     * Смена пациента в документе
     */
	function setAnotherPersonForDocument($data) {
		if ( empty($data['Evn_id']) && empty($data['CmpCallCard_id']) ) {
			return array('Error_Msg' => 'Не указан идентификатор документа');
		}

		// Если это событие...
		if ( !empty($data['Evn_id']) ) {
			$this->load->model('Evn_model');

			// Получаем список всех связанных событий
			$evnTreeData = $this->Evn_model->getRelatedEvnList($data);

			if ( !is_array($evnTreeData) || count($evnTreeData) == 0 ) {
				return array('Error_Msg' => 'Ошибка при получении списка связанных событий');
			}

			// ПРОВЕРКИ

			// Если человек тот же, то ошибка
			if ( $data['Person_id'] == $evnTreeData[0]['Person_id'] ) {
				return array('Error_Msg' => 'Выбран тот же человек');
			}

			// Специально для ЛВН, выданных по уходу, сохраняем в $data идентификатор предыдущего человека
			$data['Person_oid'] = $evnTreeData[0]['Person_id'];

			$evnDirectionList = array();

			// Проверяем, чтобы в случае не было указано направление, а также подписанных документов
			foreach ( $evnTreeData as $evnData ) {
				if ( in_array($evnData['EvnClass_SysNick'], array('EvnPL', 'EvnPS')) || preg_match("/EvnVizit/", $evnData['EvnClass_SysNick']) ) {
					$response = $this->Evn_model->checkEvnDirection($evnData['EvnClass_SysNick'], $evnData['Evn_id']);

					if ( !is_array($response) || count($response) == 0 ) {
						return array('Error_Msg' => 'Ошибка при проверке события на использование направлений');
					}
					else if ( $response['evnDirectionIsNotEmpty'] == true ) {
						return array('Error_Msg' => 'Замена человека недопустима, т.к. в случае указано направление, выписанное для другого человека');
					}
				}

				// Проверяем признак подписания документа
				if ( $evnData['Evn_IsSigned'] == 2 ) {
					return array('Error_Msg' => 'Перенос человека недопустим, т.к. в рамках случая имеются подписанные документы');
				}

				// Если есть рецепты, направления или назначения, то запрещаем перенос данных, предупреждая о том, почему не возможен перенос
				// Закомментировал направления, т.к. автоматически не надо учитывать
				// Вынес проверку нарправлений в отдельный запрос
				if ( in_array($evnData['EvnClass_SysNick'], array('EvnRecept'/*, 'EvnDirection'*/)) || preg_match("/EvnPrescr/", $evnData['EvnClass_SysNick']) ) {
					return array('Error_Msg' => 'Перенос невозможен, т.к. в рамках случая были созданы другие события, которые переносить не допускается (рецепты, назначения)');
				}

				if ( 'EvnDirection' == $evnData['EvnClass_SysNick'] ) {
					$evnDirectionList[] = (string)$evnData['Evn_id'];
				}
			}

			if ( count($evnDirectionList) > 0 ) {
				$response = $this->Evn_model->checkNonAutoEvnDirections($evnDirectionList);

				if ( !is_array($response) || count($response) == 0 ) {
					return array('Error_Msg' => 'Ошибка при проверке события на наличие созданных направлений');
				}
				else if ( $response['evnDirectionIsNotAuto'] == true ) {
					return array('Error_Msg' => 'Перенос невозможен, т.к. в рамках случая были созданы другие события, которые переносить не допускается (направления)');
				}
			}

			// 3.1. Для детей до года (включительно) на момент случая контроль не требуется
			// 3.2. Для пациентов старше года необходимо совпадение данных текущего и нового пациентов в разрезе:
			//		Дата рождения, Пол и 2 из 3-х элементов ФИО
			// При нарушении контроля вывести сообщение "Изменение пациента невозможно"
			$newPersonData = $this->getPersonDataOnDate($data['Person_id'], $evnTreeData[0]['Evn_setDT']);
			$oldPersonData = $this->getPersonDataOnDate($evnTreeData[0]['Person_id'], $evnTreeData[0]['Evn_setDT']);
			// актуальные данные
			$newPersonDataNow = $this->getPersonDataOnDate($data['Person_id']);
			$oldPersonDataNow = $this->getPersonDataOnDate($evnTreeData[0]['Person_id']);

			// Должно вернуться 2 строки
			if ( $newPersonData === false || $oldPersonData === false ) {
				return array('Error_Msg' => 'Ошибка при получении данных о людях на момент случая');
			}

			// Заменяем Ё на Е в ФИО
			// https://redmine.swan.perm.ru/issues/15462
			foreach ( $newPersonData as $key => $value ) {
				if ( in_array($key, array('Person_FirName', 'Person_SecName', 'Person_SurName')) ) {
					$newPersonData[$key] = str_replace('Ё', 'Е', mb_strtoupper(trim($value)));
				}
			}

			foreach ( $oldPersonData as $key => $value ) {
				if ( in_array($key, array('Person_FirName', 'Person_SecName', 'Person_SurName')) ) {
					$oldPersonData[$key] = str_replace('Ё', 'Е', mb_strtoupper(trim($value)));
				}
			}

			foreach ( $newPersonDataNow as $key => $value ) {
				if ( in_array($key, array('Person_FirName', 'Person_SecName', 'Person_SurName')) ) {
					$newPersonDataNow[$key] = str_replace('Ё', 'Е', mb_strtoupper(trim($value)));
				}
			}

			foreach ( $oldPersonDataNow as $key => $value ) {
				if ( in_array($key, array('Person_FirName', 'Person_SecName', 'Person_SurName')) ) {
					$oldPersonDataNow[$key] = str_replace('Ё', 'Е', mb_strtoupper(trim($value)));
				}
			}

			if ($oldPersonDataNow['Person_Age'] == 0 && $newPersonDataNow['Person_AgeMonth'] > 18) { // человек меньше года на текущий момент
				return array('Error_Msg' => 'Выбран пациент другого возраста');
			} elseif ($oldPersonData['Person_Age'] == 0 && $newPersonData['Person_AgeMonth'] > 18) { // человек меньше года на момент случая
				if ($data['ignoreAgeFioCheck'] != 2) {
					return array('Error_Msg' => '', 'Alert_Msg' => 'На момент случая у пациента был указан другой возраст.<br>Продолжить?', 'Alert_Code' => 2, 'success' => true);
				}
			} elseif ( $oldPersonDataNow['Person_IsUnknown'] == 2 && $oldPersonDataNow['Person_Age'] >= 1 ) { // с признаком "неизвестный"
				$err = '';
				if ( ($newPersonDataNow['Person_Age'] < $oldPersonDataNow['Person_Age'] - 2) || ($newPersonDataNow['Person_Age'] > $oldPersonDataNow['Person_Age'] + 2) ) {
					$err .= 'Выбран пациент другого возраста<br>';
				}
				if (isset($oldPersonDataNow['Sex_id']) && ($newPersonDataNow['Sex_id'] != $oldPersonDataNow['Sex_id'])) {
					$err .= 'Выбран пациент другого пола<br>';
				}
				if (!in_array('НЕИЗВЕСТЕН', array('srn' => $oldPersonDataNow['Person_SurName'], 'frn' => $oldPersonDataNow['Person_FirName'], 'scn' => $oldPersonDataNow['Person_SecName']))
                && count(array_diff_assoc(
                    array('srn' => $newPersonDataNow['Person_SurName'], 'frn' => $newPersonDataNow['Person_FirName'], 'scn' => $newPersonDataNow['Person_SecName'])
                    ,array('srn' => $oldPersonDataNow['Person_SurName'], 'frn' => $oldPersonDataNow['Person_FirName'], 'scn' => $oldPersonDataNow['Person_SecName'])
                )) > 1
            ) {
					$err .= 'Выбран пациент с другими ФИО<br>';
				}
				if (!empty($err) && $data['ignoreAgeFioCheck'] != 2) {
					return array('Error_Msg' => '', 'Alert_Msg' => $err. 'Продолжить?', 'Alert_Code' => 2, 'success' => true);
				}
			} elseif($oldPersonDataNow['Person_Age'] >= 1) { // все остальные случаи
				// на текущий момент
				if ( ($newPersonDataNow['Person_Age'] < $oldPersonDataNow['Person_Age'] - 2) || ($newPersonDataNow['Person_Age'] > $oldPersonDataNow['Person_Age'] + 2) ) {
					return array('Error_Msg' => 'Выбран пациент другого возраста');
				}
				if (isset($oldPersonDataNow['Sex_id']) && ($newPersonDataNow['Sex_id'] != $oldPersonDataNow['Sex_id'])) {
					return array('Error_Msg' => 'Выбран пациент другого пола');
				}
				if (count(array_diff_assoc(
						array('srn' => $newPersonDataNow['Person_SurName'], 'frn' => $newPersonDataNow['Person_FirName'], 'scn' => $newPersonDataNow['Person_SecName'])
						,array('srn' => $oldPersonDataNow['Person_SurName'], 'frn' => $oldPersonDataNow['Person_FirName'], 'scn' => $oldPersonDataNow['Person_SecName'])
					)) > 1
				) {
					return array('Error_Msg' => 'Выбран пациент с другими ФИО');
				}
				// на момент случая
				if ($data['ignoreAgeFioCheck'] != 2) {
					$err = array();
					if ( ($newPersonData['Person_Age'] < $oldPersonData['Person_Age'] - 2) || ($newPersonData['Person_Age'] > $oldPersonData['Person_Age'] + 2) ) {
						$err[] = 'другой возраст';
					}
					if (isset($oldPersonData['Sex_id']) && ($newPersonData['Sex_id'] != $oldPersonData['Sex_id'])) {
						$err[] = 'другие ФИО';
					}
					if (count(array_diff_assoc(
							array('srn' => $newPersonData['Person_SurName'], 'frn' => $newPersonData['Person_FirName'], 'scn' => $newPersonData['Person_SecName'])
							,array('srn' => $oldPersonData['Person_SurName'], 'frn' => $oldPersonData['Person_FirName'], 'scn' => $oldPersonData['Person_SecName'])
						)) > 1
					) {
						$err[] = 'другой пол';
					}
					if (!empty($err)) {
						return array('Error_Msg' => '', 'Alert_Msg' => 'На момент случая у пациента был указан ' . join($err, ', ') . '.<br>Продолжить?', 'Alert_Code' => 2, 'success' => true);
					}
				}
			}

			// Если есть ЛВН, то делаем проверку: 1) на то, чтобы не было привязки к другим учетным документам; 2) на ВК
			$evnStickList = array();

			foreach ( $evnTreeData as $evnData ) {
				if ( in_array($evnData['EvnClass_SysNick'], array('EvnStick', 'EvnStickDop', 'EvnStickStudent')) ) {
					$evnStickList[] = $evnData['Evn_id'];
				}
			}

			if ( count($evnStickList) > 0 ) {
				/*
                // Пока заглушка на документы с ЛВН
                return array('Error_Msg' => 'Замена пациента невозможна, т.к. документ содержит ЛВН');
                 */
				if ( $data['allowEvnStickTransfer'] != 2 ) {
					return array('Error_Msg' => '', 'Alert_Msg' => toUTF('Документ содержит ЛВН, продолжение операции заменит в нем персональные данные на новые. Продолжить?'), 'Alert_Code' => 1, 'success' => true);
				}

				$response = $this->Evn_model->checkEvnStickListOnUsage($evnStickList);

				if ( !is_array($response) || count($response) == 0 ) {
					return array('Error_Msg' => 'Ошибка при проверке ЛВН на использование в других случаях');
				}
				else if ( $response['allow'] == false ) {
					return array('Error_Msg' => 'Замена человека недопустима, т.к. в указанном случае присутствуют ЛВН, которые использованы в других событиях');
				}

				$response = $this->Evn_model->checkEvnStickListOnVK($evnStickList);

				if ( !is_array($response) || count($response) == 0 ) {
					return array('Error_Msg' => 'Ошибка при проверке записей об освобождении от работы на ВК');
				}
				else if ( $response['allow'] == false ) {
					return array('Error_Msg' => 'Больничный лист используется в ВК, изменение настоящего документа невозможно.');
				}
			}

			// Проверяем вхождение случая в реестр
			// Подключаемся к реестровой БД
			$dbreg = $this->load->database('registry', true);

            if ( $this->regionNick == 'ufa' ) {
				$evnObject = 'EvnSection';
				$paidField = 'Paid_id';
				$registryDataObject = 'RegistryDataEvnPS';
				$schema = 'r2';
			}
			else {
				$paidField = 'RegistryData_IsPaid';
				$schema = 'dbo';

				if ( $evnTreeData[0]['EvnClass_SysNick'] == 'EvnPS' ) {
					$evnObject = 'EvnSection';
					$registryDataObject = 'RegistryDataEvnPS';
				}
				else {
					$evnObject = 'EvnVizit';
					$registryDataObject = 'RegistryData';
				}
			}


            $query = "
				select
					 r.Registry_id as \"Registry_id\"
					,rt.RegistryType_SysNick as \"RegistryType_SysNick\"
					,rs.RegistryStatus_SysNick as \"RegistryStatus_SysNick\"
					,COALESCE(rd.{$paidField}, 1) as \"RegistryData_IsPaid\"
					,COALESCE(r.Registry_IsNeedReform, 1) as Registry_IsNeedReform
				from {$schema}.v_{$registryDataObject} rd
					inner join {$schema}.v_Registry r on r.Registry_id = rd.Registry_id
					inner join v_RegistryStatus rs  on rs.RegistryStatus_id = r.RegistryStatus_id
					inner join v_RegistryType rt  on rt.RegistryType_id = r.RegistryType_id
					inner join v_{$evnObject} e  on e.{$evnObject}_id = rd.Evn_id
				where
					rd.Evn_rid = :Evn_rid
					and COALESCE(e.{$evnObject}_IsInReg, 1) = 2
			";

			$result = $dbreg->query($query, array(
				'Evn_rid' => $evnTreeData[0]['Evn_id']
			));
			$response = false;
			if ( is_object($result) ) {
				$response = $result->result('array');
			}

			$registryList = array();
			$registryListToReform = array();

			if ( $response === false ) {
				return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (проверка вхождения случая в реестр)');
			}
			else if ( is_array($response) && count($response) > 0 ) {
				foreach ( $response as $row ) {
					if ( $row['RegistryStatus_SysNick'] == 'forpay' ) {
						return array('Error_Msg' => 'Не допускается замена пациента для выбранного случая, т.к. случай находится в реестре со статусом "На оплату"');
					}
					else if ( $row['RegistryStatus_SysNick'] == 'paid' && $row['RegistryData_IsPaid'] == 2 ) {
						return array('Error_Msg' => 'Не допускается замена пациента для выбранного случая, т.к. случай уже оплачен');
					}
					else if ( !in_array($row['RegistryType_SysNick'], array('omsstac', 'omspol')) ) {
						return array('Error_Msg' => '???');
					}
					else if ( !in_array($row['Registry_id'], array_keys($registryList)) ) {
						$registryList[$row['Registry_id']] = $row['RegistryType_SysNick'];

						//if ( $row['Registry_IsNeedReform'] == 1 ) {
						$registryListToReform[] = $row['Registry_id'];
						//}
					}
				}
			}

			// END OF ПРОВЕРКИ

			// Формируем дерево событий
			$evnTree = $this->Evn_model->getEvnTree($evnTreeData);

			if ( !is_array($evnTree) || count($evnTree) == 0 ) {
				return array('Error_Msg' => 'Список событий пуст');
			}

			// Стартуем транзакцию
			if (empty($data['no_trans'])) {
				$this->beginTransaction();
			}

			// Связка "старый ID -> новый ID"
			$evnLink = array();

			// Сохраняем новые события
			$response = $this->Evn_model->setAnotherPersonForDocument($data, $evnTree, $evnLink);

			if ( !is_array($response) || count($response) == 0 ) {
				if (empty($data['no_trans'])) {
					$this->rollbackTransaction();
				}
				return array('Error_Msg' => 'Ошибка при сохранении событий');
			}
			else if ( !empty($response[0]['Error_Msg']) ) {
				if (empty($data['no_trans'])) {
					$this->rollbackTransaction();
				}
				return array('Error_Msg' => $response[0]['Error_Msg']);
			}

			$data['Evn_id'] = (!empty($response[0]['Evn_id']) ? $response[0]['Evn_id'] : null);

			// копируем документы из EvnXml до удаления копируемого события
			$this->load->library('swXmlTemplate');
			try {
				$cntDocuments = swXmlTemplate::getEvnXmlModelInstance()->onSetAnotherPersonForDocument($data, $evnLink);
			}
            catch (Exception $e) {
				if (empty($data['no_trans'])) {
					$this->rollbackTransaction();
				}
				return array('Error_Msg' => $e->getMessage());
			}

			// Удаляем копируемое событие
			$response = $this->Evn_model->deleteEvn(array(
				'EvnClass_SysNick' => $evnTree[0]['EvnClass_SysNick']
			,$evnTree[0]['EvnClass_SysNick'] . '_id' => $evnTree[0]['Evn_id']
			,'pmUser_id' => $data['pmUser_id']
			), true);

			if ( !is_array($response) || count($response) == 0 ) {
				if (empty($data['no_trans'])) {
					$this->rollbackTransaction();
				}
				return array('Error_Msg' => 'Ошибка при удалении события');
			}
			else if ( !empty($response[0]['Error_Msg']) ) {
				if (empty($data['no_trans'])) {
					$this->rollbackTransaction();
				}
				return array('Error_Msg' => $response[0]['Error_Msg']);
			}

			if (empty($data['no_trans'])) {
				$this->commitTransaction();
			}

			if ( count($registryList) > 0 ) {
				// Снова цепляем реестровую БД
				// Помечаем реестр на переформирование
				$this->db = null;
				$this->load->database('registry');
				$this->load->model('Registry_model', 'regmodel');

				foreach ( $registryListToReform as $Registry_id ) {
					$resposne = $this->regmodel->setRegistryIsNeedReform(array(
						'Registry_id' => $Registry_id
					,'Registry_IsNeedReform' => 2
					,'pmUser_id' => $data['pmUser_id']
					));
				}

				$resposne = $this->regmodel->setRegistryDataDeleted($registryList, $evnTree[0]['Evn_id']);
			}
			$outdata = array(
				'Error_Msg' => '',
				'success' => true,
				'Evn_id' => $data['Evn_id'],
				'Registry_IsNeedReform' => (count($registryListToReform) > 0 ? 2 : 1)
			);
			if ($cntDocuments > 0) {
				$outdata['Info_Msg'] = 'Внимание: документы ЭМК (протоколы осмотров и обследований, эпикризы и т.п.) могут содержать данные предыдущего пациента. Необходима корректировка';
			}
			return $outdata;
		}
		// Если это карта вызова...
		else if ( !empty($data['CmpCallCard_id']) ) {
			$this->load->model('CmpCallCard_model');

			$response = $this->CmpCallCard_model->loadCmpCallCardEditForm($data);

			if ( $response === false ) {
				return array('Error_Msg' => 'Ошибка при выполнения запроса к базе данных (получение информации по карте вызова)');
			}
			else if ( !is_array($response) || count($response) == 0 ) {
				return array('Error_Msg' => 'Ошибка при получении информации по карте вызова');
			}

			$cmpCallCardData = $response[0];

			// ПРОВЕРКИ

			// Если человек тот же, то ошибка
			if ( $data['Person_id'] == $cmpCallCardData['Person_id'] ) {
				return array('Error_Msg' => 'Выбран тот же человек');
			}

			// 3.1. Для детей до года (включительно) на момент случая контроль не требуется
			// 3.2. Для пациентов старше года необходимо совпадение данных текущего и нового пациентов в разрезе:
			//		Дата рождения, Пол и 2 из 3-х элементов ФИО
			// При нарушении контроля вывести сообщение "Изменение пациента невозможно"
			$newPersonData = $this->getPersonDataOnDate($data['Person_id']);
			$oldPersonData = $this->getPersonDataOnDate($cmpCallCardData['Person_id']);

			// Должно вернуться 2 строки
			if ( $newPersonData === false ) {
				return array('Error_Msg' => 'Ошибка при получении данных по человеку');
			}
			else if ( $oldPersonData === false ) {
				$oldPersonData = array(
					 'Person_Age' => $cmpCallCardData['Person_Age']
					,'Person_FirName' => $cmpCallCardData['Person_FirName']
					,'Person_SecName' => $cmpCallCardData['Person_SecName']
					,'Person_SurName' => $cmpCallCardData['Person_SurName']
					,'Sex_id' => $cmpCallCardData['Sex_id']
					,'Person_IsUnknown' => 1
				);
			}

			// Заменяем Ё на Е в ФИО
			// https://redmine.swan.perm.ru/issues/15462
			foreach ( $newPersonData as $key => $value ) {
				if ( in_array($key, array('Person_FirName', 'Person_SecName', 'Person_SurName')) ) {
					$newPersonData[$key] = str_replace('Ё', 'Е', mb_strtoupper(trim($value)));
				}
			}

			foreach ( $oldPersonData as $key => $value ) {
				if ( in_array($key, array('Person_FirName', 'Person_SecName', 'Person_SurName')) ) {
					$oldPersonData[$key] = str_replace('Ё', 'Е', mb_strtoupper(trim($value)));
				}
			}

			// Проверка по связаной КВС
			$evnpsdata = $this->getFirstRowFromQuery("
				select
					Person.Person_id as \"Person_id\",
					COALESCE(Person.Person_IsUnknown, 1) as \"Person_IsUnknown\"
				from v_CmpCallCard C
				inner join v_EvnPS EPS  on EPS.CmpCallCard_id = C.CmpCallCard_id
				inner join Person  on Person.Person_id = EPS.Person_id
				where
					C.CmpCallCard_id = :CmpCallCard_id
				LIMIT 1
			", array(
				'CmpCallCard_id' => $data['CmpCallCard_id']
			));

			if (
				is_array($evnpsdata) &&
				$evnpsdata['Person_id'] != $newPersonData['Person_id'] &&
				$evnpsdata['Person_IsUnknown'] != 2
			) {
				return array('Error_Msg' => 'Для смены доступен только пациент из КВС');
			} elseif(empty($evnpsdata)) {// Проверки работают только если нет связки с КВС
				// Убрал проверку на дату рождения
				// https://redmine.swan.perm.ru/issues/15462
				if ($oldPersonData['Person_Age'] == 0) { // человек меньше года
					if ( $newPersonData['Person_AgeMonth'] > 18 ) {
						return array('Error_Msg' => 'Выбран пациент другого возраста');
					}
				} elseif ( $oldPersonData['Person_IsUnknown'] == 2 && $oldPersonData['Person_Age'] >= 1 ) { // с признаком "неизвестный"
					$err = '';
					if ( ($newPersonData['Person_Age'] < $oldPersonData['Person_Age'] - 2) || ($newPersonData['Person_Age'] > $oldPersonData['Person_Age'] + 2) ) {
						$err .= 'Выбран пациент другого возраста<br>';
					}
					//Скрыл проверку на пол #130654
					/*else if (isset($oldPersonData['Sex_id']) && ($newPersonData['Sex_id'] != $oldPersonData['Sex_id']) ) {
                    return array('Error_Msg' => 'Выбран пациент другого пола');
					}*/
					if (!in_array('НЕИЗВЕСТЕН', array('srn' => $oldPersonData['Person_SurName'], 'frn' => $oldPersonData['Person_FirName'], 'scn' => $oldPersonData['Person_SecName']))
                    && count(array_diff_assoc(
                        array('srn' => $newPersonData['Person_SurName'], 'frn' => $newPersonData['Person_FirName'], 'scn' => $newPersonData['Person_SecName'])
                        ,array('srn' => $oldPersonData['Person_SurName'], 'frn' => $oldPersonData['Person_FirName'], 'scn' => $oldPersonData['Person_SecName'])
                    )) > 1
                ) {
						$err .= 'Выбран пациент с другими ФИО<br>';
					}
					if (!empty($err) && $data['ignoreAgeFioCheck'] != 2) {
						return array('Error_Msg' => '', 'Alert_Msg' => $err. 'Продолжить?', 'Alert_Code' => 2, 'success' => true);
					}
				} else { // все остальные случаи
					if ( ($newPersonData['Person_Age'] < $oldPersonData['Person_Age'] - 2) || ($newPersonData['Person_Age'] > $oldPersonData['Person_Age'] + 2) ) {
						return array('Error_Msg' => 'Выбран пациент другого возраста');
					}
					//Скрыл проверку на пол #130654
					/*else if (isset($oldPersonData['Sex_id']) && ($newPersonData['Sex_id'] != $oldPersonData['Sex_id']) ) {
                    return array('Error_Msg' => 'Выбран пациент другого пола');
					}*/
					if (count(array_diff_assoc(
							array('srn' => $newPersonData['Person_SurName'], 'frn' => $newPersonData['Person_FirName'], 'scn' => $newPersonData['Person_SecName'])
							,array('srn' => $oldPersonData['Person_SurName'], 'frn' => $oldPersonData['Person_FirName'], 'scn' => $oldPersonData['Person_SecName'])
						)) > 1
					) {
						return array('Error_Msg' => 'Выбран пациент с другими ФИО');
					}
				}
			}

			// Проверяем вхождение карты вызова в реестр
			// Подключаемся к реестровой БД
			$dbreg = $this->load->database('registry', true);
			$query = "
				select
					 r.Registry_id as \"Registry_id\"
					,rt.RegistryType_SysNick as \"RegistryType_SysNick\"
					,rs.RegistryStatus_SysNick as \"RegistryStatus_SysNick\"
					,COALESCE(rd.RegistryData_IsPaid, 1) as \"RegistryData_IsPaid\"
					,COALESCE(r.Registry_IsNeedReform, 1) as \"Registry_IsNeedReform\"
				from v_RegistryData rd
					inner join v_Registry r  on r.Registry_id = rd.Registry_id
					inner join v_RegistryStatus rs  on rs.RegistryStatus_id = r.RegistryStatus_id
					inner join v_RegistryType rt  on rt.RegistryType_id = r.RegistryType_id
					inner join v_CmpCallCard ccc  on ccc.CmpCallCard_id = rd.Evn_id
				where
					ccc.CmpCallCard_id = :CmpCallCard_id
					and COALESCE(ccc.CmpCallCard_IsInReg, 1) = 2
			";
			$result = $dbreg->query($query, array(
				'CmpCallCard_id' => $data['CmpCallCard_id']
			));
			$response = false;
			if ( is_object($result) ) {
				$response = $result->result('array');
			}

			$registryList = array();
			$registryListToReform = array();

			if ( $response === false ) {
				return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (проверка вхождения случая в реестр)');
			}
			else if ( is_array($response) && count($response) > 0 ) {
				foreach ( $response as $row ) {
					if ( $row['RegistryStatus_SysNick'] == 'forpay' ) {
						return array('Error_Msg' => 'Не допускается замена пациента для выбранной карты вызова, т.к. карта вызова находится в реестре со статусом "На оплату"');
					}
					else if ( $row['RegistryStatus_SysNick'] == 'paid' && $row['RegistryData_IsPaid'] == 2 ) {
						return array('Error_Msg' => 'Не допускается замена пациента для выбранной карты вызова, т.к. случай уже оплачен');
					}
					else if ( !in_array($row['RegistryType_SysNick'], array('smp')) ) {
						return array('Error_Msg' => '???');
					}
					else if ( !in_array($row['Registry_id'], array_keys($registryList)) ) {
						$registryList[$row['Registry_id']] = $row['RegistryType_SysNick'];

						//if ( $row['Registry_IsNeedReform'] == 1 ) {
						$registryListToReform[] = $row['Registry_id'];
						//}
					}
				}
			}

			// END OF ПРОВЕРКИ

			// Стартуем транзакцию
			if (empty($data['no_trans'])) {
				$this->beginTransaction();
			}

			// Меняем человека в карте вызова
			$response = $this->CmpCallCard_model->setAnotherPersonForCmpCallCard($data);

			if ( !is_array($response) || count($response) == 0 ) {
				if (empty($data['no_trans'])) {
					$this->rollbackTransaction();
				}
				return array('Error_Msg' => 'Ошибка при смене пациента в карте вызова');
			}
			else if ( !empty($response[0]['Error_Msg']) ) {
				if (empty($data['no_trans'])) {
					$this->rollbackTransaction();
				}
				return array('Error_Msg' => $response[0]['Error_Msg']);
			}

			if (empty($data['no_trans'])) {
				$this->commitTransaction();
			}

			if ( count($registryList) > 0 ) {
				// Снова цепляем реестровую БД
				// Помечаем реестр на переформирование
				$this->db = null;
				$this->load->database('registry');
				$this->load->model('Registry_model', 'regmodel');

				foreach ( $registryListToReform as $Registry_id ) {
					$resposne = $this->regmodel->setRegistryIsNeedReform(array(
						'Registry_id' => $Registry_id
					,'Registry_IsNeedReform' => 2
					,'pmUser_id' => $data['pmUser_id']
					));
				}

				$resposne = $this->regmodel->setRegistryDataDeleted($registryList, $data['CmpCallCard_id']);
			}

			return array('Error_Msg' => '', 'success' => true, 'CmpCallCard_id' => $data['CmpCallCard_id'], 'Registry_IsNeedReform' => (count($registryListToReform) > 0 ? 2 : 1));
		}
	}

	/**
	* Получение списка случаев оказания МП
	*/
	function loadMedicalCareCases($data) {
		$params = array();
		$params['Person_id'] = $data['Person_id'];
		$and_PS = '';
		$and_Vizit = '';
		$and_VizitStom = '';
		$and_Dir = '';
		$and_Que = ' ';
		$and_Cmp = '';

		$diag_PS = ' and '.getAccessRightsDiagFilter('D.Diag_Code');
		$diag_Vizit = ' and '.getAccessRightsDiagFilter('D.Diag_Code');
		$diag_VizitStom = ' and '.getAccessRightsDiagFilter('D.Diag_Code');
		$diag_Dir = ' and '.getAccessRightsDiagFilter('D.Diag_Code');
		$diag_Que = ' and '.getAccessRightsDiagFilter('D.Diag_Code');
		$diag_Cmp = ' and '.getAccessRightsDiagFilter('D.Diag_Code');
		if(isset($data['MedCareCasesDate_Range'][0]))
		{
			$params['Evn_BegDate'] = $data['MedCareCasesDate_Range'][0];
			$and_PS .= ' and EPS.EvnPS_setDate >= CAST(:Evn_BegDate as date)';
			$and_Vizit .= ' and EPL.EvnVizitPL_setDate >= CAST(:Evn_BegDate as date)';
			$and_VizitStom .= ' and EPL.EvnVizitPLStom_setDate >= CAST(:Evn_BegDate as date)';
			$and_Dir .= ' and TTG.TimeTableGraf_insDT >= CAST(:Evn_BegDate as date)';
			$and_Que .= ' and ED.EvnDirection_insDT >= CAST(:Evn_BegDate as date)';
			$and_Cmp .= ' and CCal.CmpCallCard_prmDT >= CAST(:Evn_BegDate as date)';
		}
		if(isset($data['MedCareCasesDate_Range'][1]))
		{
			$params['Evn_EndDate'] = $data['MedCareCasesDate_Range'][1];
			$and_PS .= ' and to_char(EPS.EvnPS_setDate, \'YYYY-MM-DD\') <= :Evn_EndDate';
			$and_Vizit .= ' and to_char(EPL.EvnVizitPL_setDate, \'YYYY-MM-DD\') <= :Evn_EndDate';
			$and_VizitStom .= ' and to_char(EPL.EvnVizitPLStom_setDate, \'YYYY-MM-DD\') <= :Evn_EndDate';
			$and_Dir .= ' and to_char(TTG.TimeTableGraf_insDT, \'YYYY-MM-DD\') <= :Evn_EndDate';
			$and_Que .= ' and to_char(ED.EvnDirection_insDT, \'YYYY-MM-DD\') <= :Evn_EndDate';
			$and_Cmp .= ' and to_char(CCal.CmpCallCard_prmDT, \'YYYY-MM-DD\') <= :Evn_EndDate';
		}
		$query = "
			select
				EPS.EvnPS_id as \"Evn_id\",
				COALESCE(to_char(EPS.EvnPS_setDate, 'DD.MM.YYYY'), '') || ' ' || COALESCE(to_char(EPS.EvnPS_setTime, 'HH24:MI'), '') as \"Evn_Date\",
				'' as \"Evn_FutDate\",
				'Поступление в стационар' as \"Evn_Type\",
				COALESCE(L.Lpu_Nick, '') as \"Evn_Lpu\",
				COALESCE(LB.LpuBuilding_Name, '') as \"Evn_LpuBuilding\",
				COALESCE(LU.LpuUnit_Name, '') as \"Evn_LpuUnit\",
				COALESCE(MSF.Person_Fio, '') as \"Evn_Doctor\",
				/*COALESCE(ED.EvnDirection_Num, '') as \"Evn_Direction\",*/
				COALESCE(COALESCE(ED.EvnDirection_Num,EPS.EvnDirection_Num),'') as \"Evn_Direction\",
				'' as \"Evn_RecType\",
				MSF.MedStaffFact_id as \"Evn_Doctor_id\",
				L.Lpu_id as \"Evn_Lpu_id\"
			from v_EvnPS EPS 
			left join v_Lpu L  on L.Lpu_id = EPS.Lpu_id
			left join v_LpuSection LS  on LS.LpuSection_id = EPS.LpuSection_id
			left join v_LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id
			left join v_LpuBuilding LB  on LB.LpuBuilding_id = LU.LpuBuilding_id
			left join v_MedStaffFact MSF  on MSF.MedStaffFact_id = EPS.MedStaffFact_pid
			left join v_EvnDirection ED  on ED.EvnDirection_id = EPS.EvnDirection_id
			left join v_Diag D  on D.Diag_id = EPS.Diag_id
			where EPS.Person_id = :Person_id {$and_PS} {$diag_PS}
			
			union
			
			select EPL.EvnVizitPL_id as \"Evn_id\",
				COALESCE(to_char(EPL.EvnVizitPL_setDate, 'DD.MM.YYYY'), '') || ' ' || COALESCE(to_char(EPL.EvnVizitPL_setTime, 'HH24:MI'), '') as \"Evn_Date\",
				'' as \"Evn_FutDate\",
				'Посещение поликлиники' as \"Evn_Type\",
				COALESCE(L.Lpu_Nick, '') as \"Evn_Lpu\",
				COALESCE(LB.LpuBuilding_Name, '') as \"Evn_LpuBuilding\",
				COALESCE(LU.LpuUnit_Name, '') as \"Evn_LpuUnit\",
				COALESCE(MSF.Person_Fio, '') as \"Evn_Doctor\",
				/*COALESCE(ED.EvnDirection_Num, '') as \"Evn_Direction\",*/
				COALESCE(COALESCE(ED.EvnDirection_Num, EP.EvnDirection_Num),'') as \"Evn_Direction\",
				'' as \"Evn_RecType\",
				MSF.MedStaffFact_id as \"Evn_Doctor_id\",
				L.Lpu_id as \"Evn_Lpu_id\"
			from v_EvnVizitPL EPL 
			inner join v_EvnPL EP  on EP.EvnPL_id = EPL.EvnVizitPL_pid
			left join v_Lpu L  on L.Lpu_id = EPL.Lpu_id
			left join v_LpuSection LS  on LS.LpuSection_id = EPL.LpuSection_id
			left join v_LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id
			left join v_LpuBuilding LB  on LB.LpuBuilding_id = LU.LpuBuilding_id
			left join v_MedStaffFact MSF  on MSF.MedStaffFact_id = EPL.MedStaffFact_id
			left join v_EvnDirection ED  on ED.EvnDirection_id = EPL.EvnDirection_id
			left join v_Diag D  on D.Diag_id = EP.Diag_id
			where EPL.Person_id = :Person_id {$and_Vizit} {$diag_Vizit}
			
			union
			
			select EPL.EvnVizitPLStom_id as \"Evn_id\",
				COALESCE(to_char(EPL.EvnVizitPLStom_setDate, 'DD.MM.YYYY'), '') || ' ' || COALESCE(to_char(EPL.EvnVizitPLStom_setTime, 'HH24:MI'), '') as \"Evn_Date\",
				'' as \"Evn_FutDate\",
				'Посещение поликлиники' as \"Evn_Type\",
				COALESCE(L.Lpu_Nick, '') as \"Evn_Lpu\",
				COALESCE(LB.LpuBuilding_Name, '') as \"Evn_LpuBuilding\",
				COALESCE(LU.LpuUnit_Name, '') as \"Evn_LpuUnit\",
				COALESCE(MSF.Person_Fio, '') as \"Evn_Doctor\",
				/*COALESCE(ED.EvnDirection_Num, '') as \"Evn_Direction\",*/
				COALESCE(COALESCE(ED.EvnDirection_Num, EP.EvnDirection_Num),'') as \"Evn_Direction\",
				'' as \"Evn_RecType\",
				MSF.MedStaffFact_id as \"Evn_Doctor_id\",
				L.Lpu_id as \"Evn_Lpu_id\"
			from v_EvnVizitPLStom EPL  
			inner join v_EvnPLStom EP  on EP.EvnPLStom_id = EPL.EvnVizitPLStom_pid
			left join v_Lpu L  on L.Lpu_id = EPL.Lpu_id
			left join v_LpuSection LS  on LS.LpuSection_id = EPL.LpuSection_id
			left join v_LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id
			left join v_LpuBuilding LB  on LB.LpuBuilding_id = LU.LpuBuilding_id
			left join v_MedStaffFact MSF  on MSF.MedStaffFact_id = EPL.MedStaffFact_id
			left join v_EvnDirection ED  on ED.EvnDirection_id = EPL.EvnDirection_id
			left join v_Diag D  on D.Diag_id = EP.Diag_id
			where EPL.Person_id = :Person_id {$and_VizitStom} {$diag_VizitStom}
			
			union
			
			select ED.EvnDirection_id as \"Evn_id\",
				COALESCE(to_char(TTG.TimeTableGraf_insDT, 'DD.MM.YYYY HH24:MI'), '') as \"Evn_Date\",
				COALESCE(to_char(TTG.TimeTableGraf_begTime, 'DD.MM.YYYY HH24:MI'), '') as \"Evn_FutDate\",
				'Направление' as \"Evn_Type\",
				COALESCE(L.Lpu_Nick, '') as \"Evn_Lpu\",
				COALESCE(LB.LpuBuilding_Name, '') as \"Evn_LpuBuilding\",
				COALESCE(LU.LpuUnit_Name, '') as \"Evn_LpuUnit\",
				COALESCE(MSF.Person_Fio, '') as \"Evn_Doctor\",
				COALESCE(ED.EvnDirection_Num, '') as \"Evn_Direction\",
				COALESCE(RC.RecClass_id, 0) as \"Evn_RecType\",
				MSF.MedStaffFact_id as \"Evn_Doctor_id\",
				L.Lpu_id as \"Evn_Lpu_id\"
			from v_EvnDirection ED  
			left join v_TimeTableGraf_lite TTG  on TTG.TimeTableGraf_id = ED.TimeTableGraf_id
			left join v_Lpu L  on L.Lpu_id = ED.Lpu_id
			left join v_LpuSection LS  on LS.LpuSection_id = COALESCE(ED.LpuSection_did,ED.LpuSection_id)
			left join v_LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id
			left join v_LpuBuilding LB  on LB.LpuBuilding_id = LU.LpuBuilding_id
			left join v_MedStaffFact MSF  on MSF.MedStaffFact_id = ED.MedStaffFact_id
			left join v_RecClass RC  on RC.RecClass_id = TTG.RecClass_id
			left join v_Diag D  on D.Diag_id = ED.Diag_id
			where ED.Person_id = :Person_id
			and ED.TimeTableGraf_id is not null
			and COALESCE(ED.EvnDirection_IsAuto, 1) != 2 {$and_Dir} {$diag_Dir}
			
			union
			
			select ED.EvnDirection_id as \"Evn_id\",
				COALESCE(to_char(ED.EvnDirection_insDT, 'DD.MM.YYYY HH24:MI'), '') as \"Evn_Date\",
				'Не определено' as \"Evn_FutDate\",
				'Направление' as \"Evn_Type\",
				COALESCE(L.Lpu_Nick, '') as \"Evn_Lpu\",
				COALESCE(LB.LpuBuilding_Name, '') as \"Evn_LpuBuilding\",
				COALESCE(LU.LpuUnit_Name, '') as \"Evn_LpuUnit\",
				COALESCE(MSF.Person_Fio, '') as \"Evn_Doctor\",
				COALESCE(ED.EvnDirection_Num, '') as \"Evn_Direction\",
				0 as \"Evn_RecType\",
				MSF.MedStaffFact_id as \"Evn_Doctor_id\",
				L.Lpu_id as \"Evn_Lpu_id\"
			from v_EvnQueue EQ  
			inner join v_EvnDirection ED  on ED.EvnDirection_id = EQ.EvnDirection_id
			left join v_Lpu L  on L.Lpu_id = ED.Lpu_id
			left join v_LpuSection LS  on LS.LpuSection_id = COALESCE(ED.LpuSection_did,ED.LpuSection_id)
			left join v_LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id
			left join v_LpuBuilding LB  on LB.LpuBuilding_id = LU.LpuBuilding_id
			left join v_MedStaffFact MSF  on MSF.MedStaffFact_id = ED.MedStaffFact_id
			left join v_Diag D  on D.Diag_id = ED.Diag_id
			where EQ.Person_id = :Person_id
			and COALESCE(ED.EvnDirection_IsAuto, 1) != 2 {$and_Que} {$diag_Que}
			
			union
			
			select CCal.CmpCallCard_id as \"Evn_id\",
				COALESCE(to_char(CCal.CmpCallCard_prmDT, 'DD.MM.YYYY HH24:MI'), '') as \"Evn_Date\",
				'' as \"Evn_FutDate\",
				'Вызов СМП' as \"Evn_Type\",
				COALESCE(L.Lpu_Nick, '') as \"Evn_Lpu\",
				COALESCE(LB.LpuBuilding_Name, '') as \"Evn_LpuBuilding\",
				COALESCE(LU.LpuUnit_Name, '') as \"Evn_LpuUnit\",
				COALESCE(MSF.Person_Fio, '') as \"Evn_Doctor\",
				'' as \"Evn_Direction\",
				'' as \"Evn_RecType\",
				MSF.MedStaffFact_id as \"Evn_Doctor_id\",
				L.Lpu_id as \"Evn_Lpu_id\"
			from v_CmpCallCard CCal  
			left join v_Lpu L  on L.Lpu_id = CCal.Lpu_id
			left join v_LpuSection LS  on LS.LpuSection_id = CCal.LpuSection_id
			left join v_LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id
			left join v_LpuBuilding LB  on LB.LpuBuilding_id = LU.LpuBuilding_id
			left join v_MedStaffFact MSF  on MSF.MedStaffFact_id = CCal.MedStaffFact_id
			left join v_Diag D  on D.Diag_id = CCal.Diag_uid
			where CCal.Person_id = :Person_id {$and_Cmp}
			
			order by 3
		";
		//echo getDebugSQL($query,$params);die;
		$result = $this->db->query($query,$params);
		if(is_object($result))
		{
			$result = $result->result('array');
			if(count($result) > 0)
			{
				$kvrachu_url = KVRACHU_URL;
				for($i=0;$i<count($result);$i++)
				{
					$Evn_MO_Full = $result[$i]['Evn_Lpu'] . ' / ' . $result[$i]['Evn_LpuBuilding'] . ' / ' . $result[$i]['Evn_LpuUnit'];
					$Evn_Lpu_id = $result[$i]['Evn_Lpu_id'];

					$Evn_Doctor = $result[$i]['Evn_Doctor'];
					$Evn_Doctor_id = $result[$i]['Evn_Doctor_id'];

					if(isset($result[$i]['Evn_Lpu_id']) && $result[$i]['Evn_Lpu_id'] > 0)
						$result[$i]['Evn_MO_Link'] = "
							<a href='{$kvrachu_url}/service/hospitals/view/{$Evn_Lpu_id}'  target='_blank'>{$Evn_MO_Full}</a>
						";
					else
						$result[$i]['Evn_MO_Link'] = $Evn_MO_Full;

					if(isset($result[$i]['Evn_Doctor_id']) && $result[$i]['Evn_Doctor_id'] > 0)
						$result[$i]['Evn_Doctor_Link'] = "
							<a href='{$kvrachu_url}/service/schedule/{$Evn_Doctor_id}/info' target='_blank'>{$Evn_Doctor}</a>
						";
					else
						$result[$i]['Evn_Doctor_Link'] = $Evn_Doctor;

					if ($result[$i]['Evn_Type'] == 'Направление')
					{
						if($result[$i]['Evn_RecType'] == '1')
							$result[$i]['Evn_RecType'] = 'Call-центр';
						else if($result[$i]['Evn_RecType'] == '2')
							$result[$i]['Evn_RecType'] = 'Интернет';
						else if($result[$i]['Evn_RecType'] == '3')
						{
							$result[$i]['Evn_RecType'] = 'Врач ' . $result[$i]['Evn_Doctor_Link'];
						}
						else
							$result[$i]['Evn_RecType'] = '';
						$result[$i]['Evn_Doctor_Link'] = '';
					}
					else
						$result[$i]['Evn_RecType'] = '';
				}
			}
			$response = array();
			$response['data'] = $result;
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Проверяем наличие объекта для МО
	 */
	function checkRecordInLpu($data) {
		$object = $data['object'];
		$resp_rec = $this->queryResult("
			select 
				{$object}_id as \"{$object}_id\"
			from
				v_{$object} 
			where
				Lpu_id = :Lpu_id
				and {$object}_id = :value
			LIMIT 1
		", array(
			'value' => $data['value'],
			'Lpu_id' => $data['Lpu_id']
		));

		if (!empty($resp_rec[0][$object.'_id'])) {
			return true;
		}

		return false;
	}

	/**
	 * Загрузка справочника связей между профилем и специальностью в глобальный стор. Для пензы
	 *
	 * @return array|false
	 */
	function loadLpuSectionProfileMedSpecOms()
	{
		$this->load->library('swCache');

		if ( $resCache = $this->swcache->get("LpuSectionProfileMedSpecOms_Penza") )
		{
			return $resCache;
		}

		$query = "
			SELECT
				LpuSectionProfileMedSpecOms_id as \"LpuSectionProfileMedSpecOms_id\",
				LpuSectionProfile_id as \"LpuSectionProfile_id\",
				MedSpecOms_id as \"MedSpecOms_id\",
				to_char(LpuSectionProfileMedSpecOms_begDate, 'YYYY-MM-DD') as \"LpuSectionProfileMedSpecOms_begDate\",
				to_char(LpuSectionProfileMedSpecOms_endDate, 'YYYY-MM-DD') as \"LpuSectionProfileMedSpecOms_endDate\",
				Lpu_id as \"Lpu_id\"
			FROM
				v_LpuSectionProfileMedSpecOms LSPMSO 
		";

		$response = $this->queryResult($query);

		if ( ! empty($response) )
		{
			$this->swcache->set("LpuSectionProfileMedSpecOms_Penza", $response, array('ttl'=> 2*60*60)); // 2 часа

		}

		return $response;
	}

	/**
	 * Загрузка справочника связей между услугой и специальностью в глобальный стор. Для пензы
	 *
	 * @return array|false
	 */
	function loadUslugaComplexMedSpec()
	{
		$this->load->library('swCache');

		if ($resCache = $this->swcache->get("UslugaComplexMedSpec_Penza"))
		{
			return $resCache;
		}


		$query = "
			SELECT
				UslugaComplexMedSpec_id as \"UslugaComplexMedSpec_id\",
				UslugaComplex_id as \"UslugaComplex_id\",
				MedSpecOms_id as \"MedSpecOms_id\",
				DispClass_id as \"DispClass_id\",
				to_char(UslugaComplexMedSpec_begDate, 'YYYY-MM-DD') as \"UslugaComplexMedSpec_begDate\",
				to_char(UslugaComplexMedSpec_endDate, 'YYYY-MM-DD') as \"UslugaComplexMedSpec_endDate\"
			FROM
				v_UslugaComplexMedSpec UCMS 
		";

		$response = $this->queryResult($query);


		if ( ! empty($response) )
		{
			$this->swcache->set("UslugaComplexMedSpec_Penza", $response, array('ttl'=> 2*60*60)); // 2 часа
		}

		return $response;
	}

	/**
	 * Разбор логов и сохранение в БД
	 */
	function parsePerfLog($data) {
		if (!empty($data['list'])) {
			$list = explode("\n", $data['list']);
			// построчно читаем файлы
			foreach ($list as $k => $v) {
				$descriptor = fopen(trim($v), 'r');
				if ($descriptor) {
					while (($string = fgets($descriptor)) !== false) {
						$m = explode(';', $string);
						$savedate = date('Y-m-d H:i:s', trim(strtotime($m[1])));
						$login = trim($m[2]);
						$json = mb_substr($string, mb_strpos($string, $login) + mb_strlen($login) + 2);
						$array = json_decode($json, true);
						foreach($array as $one) {
							if (isset($one['params']) && is_array($one['params'])) {
								$one['params'] = json_encode($one['params']);
							}

							// пишем в БД
							$this->db->query("
								insert into tmp.perfLog (login, window, url, params, time, self, type, perfLog_saveDT) values (:login, :window, :url, :params, :time, :self, :type, :perfLog_saveDT)
							", array(
								'login' => $login,
								'window' => $one['window'],
								'url' => isset($one['url']) ? $one['url'] : null,
								'params' => isset($one['params']) ? $one['params'] : null,
								'time' => date('Y-m-d H:i:s', round($one['time']/1000)),
								'self' => isset($one['self']) ? $one['self'] : null,
								'type' => $one['type'],
								'perfLog_saveDT' => $savedate
							));
						}
					}
					fclose($descriptor);
				} else {
					return array('result' => 'Файл ' . $v . ' не удалось открыть');
				}
			}
			return array('result' => 'Файлы успешно обработаны');
		} else {
			return array('result' => 'Укажите список файлов!');
		}
	}

	/**
	 * @return array
	 */
	function getEditedDataCollectionConfig() {
		$config = array(
			'EvnLabRequest' => array('object' => 'dbo.EvnLabRequest', 'key' => 'EvnLabRequest_id'),
			'EvnLabSample' => array('object' => 'dbo.EvnLabSample', 'key' => 'EvnLabSample_id'),
			'EvnUslugaPar' => array('object' => 'dbo.EvnUslugaPar', 'key' => 'EvnUslugaPar_id'),
			'EvnDirection' => array('object' => 'dbo.EvnDirection', 'key' => 'EvnDirection_id'),
			'UslugaTest' => array('object' => 'dbo.UslugaTest', 'key' => 'UslugaTest_id'),
			'Analyzer' => array('object' => 'lis.Analyzer', 'key' => 'Analyzer_id'),
			'EvnLabRequestUslugaComplex' => array('object' => 'dbo.EvnLabRequestUslugaComplex', 'key' => 'EvnLabRequestUslugaComplex_id'),
		);

		if ($this->usePostgreLis) {
			$config = array_merge($config, array(
				'EvnLabRequest' => array('object' => 'dbo.EvnLabRequest', 'key' => 'Evn_id'),
				'EvnLabSample' => array('object' => 'dbo.EvnLabSample', 'key' => 'Evn_id'),
				'EvnUslugaPar' => array('object' => 'dbo.EvnUslugaPar', 'key' => 'Evn_id'),
				'EvnDirection' => array('object' => 'dbo.EvnDirection', 'key' => 'Evn_id'),
			));
		}

		return $config;
	}

	/**
	 * @throws Exception
	 */
	function flushEditedDataCollection() {
		$module = isset($this->moduleName)?$this->moduleName:null;
		$config = $this->config->item('dbReplicatorQueue');

		if (empty($module) ||
			empty($this->editedDataCollection) ||
			empty($config) || !$config['enable']
		) {
			return;
		}

		$collectionConfig = $this->getEditedDataCollectionConfig();
		$collection = $this->editedDataCollection;
		$collectionByKey = array();

		foreach($collection as $item) {
			if (empty($collectionConfig[$item['object']])) {
				continue;
			}
			$objectConfig = $collectionConfig[$item['object']];
			$item['name'] = $objectConfig['object'];
			$item['key_name'] = $objectConfig['key'];
			$item_key = $item['object'].':'.$item['key_name'].':'.$item['key'];

			if (isset($collectionByKey[$item_key]) && $collectionByKey[$item_key]['operation'] == 'del') {
				continue;
			}
			if (isset($collectionByKey[$item_key]) && $collectionByKey[$item_key]['operation'] == 'ins' && $item['operation'] != 'del') {
				continue;
			}

			$collectionByKey[$item_key] = $item;
		}
		$this->editedDataCollection = array();

		$result = array();
		foreach($collectionByKey as $item) {
			if ($this->usePostgreLis) {
				$query = "select * from {$item['name']} where {$item['key_name']} = :key limit 1";
			} else {
				$query = "select * from {$item['name']} where {$item['key_name']} = :key limit 1";
			}
			$resp = $this->getFirstRowFromQuery($query, $item, true);
			if ($resp === false) {
				throw new Exception('Ошибка при получении данных для публикации');
			}

			if (in_array($item['operation'], array('del')) && empty($resp)) {
				$result[] = array_merge($item, array(
					'data' => array($item['key_name'] => $item['key'])
				));
			}
			if (in_array($item['operation'], array('ins', 'upd')) && !empty($resp)) {
				foreach($resp as $field => $value) {
					if ($value === null) {
						unset($resp[$field]);
					}
				}
				$result[] = array_merge($item, array('data' => $resp));
			}
		}

		/*$this->load->library('textlog', array('file' => 'Stomp_'.date('Y-m-d').'.log'), 'collection_log');
		$this->collection_log->add('Collection: '.print_r($collection, true));
		$this->collection_log->add('Result: '.print_r($result, true));*/

		if (count($result) > 0) {
			$this->load->library('SwStomp', $config, 'stomp');
			$this->stomp->publicate($result, $module);
		}
	}

	/**
	 * Получение текущих даты и времени
	 */
	function getCurrentDateTime() {
		return $this->getFirstRowFromQuery('select to_char(dbo.tzGetDate(), \'DD.MM.YYYY\') as date, to_char(dbo.tzGetDate(), \'HH24:MI:SS\') as time');
	}

	/**
	 * Получение LeaveType_id по LeaveType_SysNick
	 */
	function getLeaveTypeBySysNick($data)
	{
		return $this->dbmodel->getFirstResultFromQuery("
			select
				LeaveType_id as \"LeaveType_id\"
			from v_LeaveType_id
			where LeaveType_SysNick ilike :LeaveType_SysNick
		", [
			'LeaveType_SysNick' => $data['LeaveType_SysNick']
		]);
	}
	/**
	 * Метод получения последнего прикреплениия пациента к МО
	 */
	function getPersonLastAtachment($data){
		$sql = "
			select
				PC.PersonCard_id as \"PersonCard_id\",
				PC.PersonCard_begDate as \"PersonCard_begDate\",
				PC.PersonCard_endDate as \"PersonCard_endDate\",
				PC.LpuAttachType_id as \"LpuAttachType_id\",
				O.Org_Name as \"Org_Name\",
				O.Org_Nick as \"Org_Nick\"
			from
				v_PersonCard_all PC
				left join Lpu L on PC.Lpu_id = L.Lpu_id
				left join Org O on L.Org_id = O.Org_id
			where Person_id = ?
			Order by PersonCard_id desc
			limit 1
			";
		$res = $this->db->query($sql, array($data['Person_id']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

/**
 * Список участковых врачей на участке.
 */
	function getDistrictDoctor($lpuRegionId)
	{
		$query = '
			SELECT mp.Person_Fio AS "Person_Fio",
				mp.Dolgnost_id AS "Dolgnost_id"
				FROM v_MedStaffRegion msr
					INNER JOIN v_MedPersonal mp
						ON mp.MedPersonal_id = msr.MedPersonal_id
				WHERE LpuRegion_id = :LpuRegion_id
				ORDER BY msr.MedStaffRegion_isMain DESC';

		$res = $this->db->query($query, ['LpuRegion_id' => $lpuRegionId]);

		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}

/**
 * Предыдущее ЛПУ прикрепления.
 */
	function getPrevAttachLpu($personId)
	{
		$query = "
			SELECT RTRIM(l.Lpu_Name) AS \"Lpu_Name\",
				COALESCE(l.UAddress_Address, '') AS \"Lpu_UAddress\"
				FROM v_PersonCard_all pc
					INNER JOIN v_Lpu l ON l.Lpu_id = pc.Lpu_id
				WHERE pc.Person_id = :Person_id
				ORDER BY PersonCard_begDate DESC
				OFFSET 1 ROW
				FETCH NEXT 1 ROW ONLY";

		$res = $this->db->query($query, ['Person_id' => $personId]);

		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}
}