<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ServiceISProf_model - модель для отправки данных в ИС "Профилактика"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package            ServiceISProf
 * @access            public
 * @copyright        Copyright (c) 2017 Swan Ltd.
 * @author            Dmitry Vlasenko
 * @version            11.11.2018
 */

require_once 'vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ServiceISProf_model extends SwModel
{
	protected $host;
	protected $port;
	protected $user;
	protected $password;
	protected $vhost;
	protected $exchange;
	protected $delivery_mode;

	/**
	 *    Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->load->model('ObjectSynchronLog_model');
		$this->ObjectSynchronLog_model->setServiceSysNick('IszlKrym');

		$this->load->model('ObjectSynchronLog_model');


		$config = $this->config->item('ServiceISProf');
		$this->host = $config['host'];
		$this->port = $config['port'];
		$this->user = $config['user'];
		$this->password = $config['password'];
		$this->vhost = $config['vhost'];
		$this->exchange = $config['exchange'];
		$this->delivery_mode = $config['delivery_mode'];
	}

	/**
	 * @param mixed $config
	 */
	function showConfig($config)
	{
		if (!isSuperadmin()) return;
		if (is_array($config)) {
			unset($config['password']);
		}
		echo '<pre>';
		print_r($config);
	}

	/**
	 * @param string $name
	 */
	function showServiceConfig()
	{
		$this->showConfig($this->config->item('ServiceISProf'));
	}

	/**
	 * @return string
	 */
	function GUID()
	{
		if (function_exists('com_create_guid')) {
			return trim(com_create_guid(), '{}');
		} else {
			mt_srand((double)microtime() * 10000);
			$charid = strtoupper(md5(uniqid(rand(), true)));
			$hyphen = chr(45);// "-"
			return ''
				. substr($charid, 0, 8) . $hyphen
				. substr($charid, 8, 4) . $hyphen
				. substr($charid, 12, 4) . $hyphen
				. substr($charid, 16, 4) . $hyphen
				. substr($charid, 20, 12);
		}
	}

	/**
	 * Создание исключений по ошибкам
	 */
	function exceptionErrorHandler($errno, $errstr, $errfile, $errline)
	{
		switch ($errno) {
			case E_NOTICE:
			case E_USER_NOTICE:
				$errors = "Notice";
				break;
			case E_WARNING:
			case E_USER_WARNING:
				$errors = "Warning";
				break;
			case E_ERROR:
			case E_USER_ERROR:
				$errors = "Fatal Error";
				break;
			default:
				$errors = "Unknown Error";
				break;
		}

		$msg = sprintf("%s:  %s in %s on line %d", $errors, $errstr, $errfile, $errline);
		throw new ErrorException($msg, 0, $errno, $errfile, $errline);
	}

	/**
	 * Запуск отправки данных в очередь RabbitMQ
	 * @param array $data
	 * @return array
	 */
	function runPublisher($data)
	{
		set_time_limit(0);

		$startDT = date_create();
		$this->load->library('textlog', ['file' => 'ServiceISProf_' . $startDT->format('Y-m-d') . '.log']);

		try {
			set_error_handler([$this, 'exceptionErrorHandler']);

			$connection = new AMQPStreamConnection($this->host, $this->port, $this->user, $this->password, $this->vhost, false, 'AMQPLAIN', null, 'en_US', 3.0, 130.0, null, false, 0);
			$channel = $connection->channel();

			$resp_child = $this->queryResult("
				with Person as (
					select
						evpl.Person_id,
						msf.MedPersonal_id,
						msf.Lpu_id,
						55 as KindId
					from
						v_EvnVizitPL evpl (nolock)
						inner join v_PersonState ps (nolock) on ps.Person_id = evpl.Person_id
						inner join v_Diag d (nolock) on d.Diag_id = evpl.Diag_id
						inner join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = evpl.MedStaffFact_id
					where
						dbo.Age2(ps.Person_BirthDay, :begDate) < 18
						and not exists (
							select top 1
								PreventionPersonList_id
							from
								r59.v_PreventionPersonList (nolock)
							where
								Person_id = evpl.Person_id
								and DATEDIFF(DAY, PreventionPersonList_insDT, :begDate) < 315
								and PreventionPersonList_IsPregnancy = 2
						)
						and evpl.EvnVizitPL_updDT >= :begDate
						and (
							(d.Diag_Code >= 'Z32.0' and d.Diag_Code <= 'Z36.9')
							OR (d.Diag_Code >= 'O00.0' and d.Diag_Code <= 'O99.8')
						)
						
					union
					
					select
						es.Person_id,
						msf.MedPersonal_id,
						msf.Lpu_id,
						55 as KindId
					from
						v_EvnSection es (nolock)
						inner join v_PersonState ps (nolock) on ps.Person_id = es.Person_id
						inner join v_Diag d (nolock) on d.Diag_id = es.Diag_id
						inner join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = es.MedStaffFact_id
					where
						dbo.Age2(ps.Person_BirthDay, :begDate) < 18
						and not exists (
							select top 1
								PreventionPersonList_id
							from
								r59.v_PreventionPersonList (nolock)
							where
								Person_id = es.Person_id
								and DATEDIFF(DAY, PreventionPersonList_insDT, :begDate) < 315
								and PreventionPersonList_IsPregnancy = 2				
						)
						and es.EvnSection_updDT >= :begDate
						and (
							(d.Diag_Code >= 'Z32.0' and d.Diag_Code <= 'Z36.9')
							OR (d.Diag_Code >= 'O00.0' and d.Diag_Code <= 'O99.8')
						)
						
					union
					
					select
						ps.Person_id,
						pu.MedPersonal_id,
						pu.Lpu_id,
						54 as KindId
					from
						v_PersonState ps (nolock)
						inner join v_PersonHeight ph (nolock) on ph.Person_id = ps.Person_id
						inner join v_pmUserCache pu (nolock) on pu.pmUser_id = ph.pmUser_updID
					where
						dbo.Age2(ps.Person_BirthDay, :begDate) < 18
						and PH.PersonHeight_updDT >= :begDate
						
					union
					
					select
						ps.Person_id,
						pu.MedPersonal_id,
						pu.Lpu_id,
						54 as KindId
					from
						v_PersonState ps (nolock)
						inner join v_PersonWeight pw (nolock) on pw.Person_id = ps.Person_id
						inner join v_pmUserCache pu (nolock) on pu.pmUser_id = pw.pmUser_updID
					where
						dbo.Age2(ps.Person_BirthDay, :begDate) < 18
						and PW.PersonWeight_updDT >= :begDate
				)
				
				select
					Person.MedPersonal_id,
					Person.KindId,
					ps.Person_id,
					ps.Person_FirName as FirstName,
					ps.Person_SurName as LastName,
					ps.Person_SecName as MiddleName,
					convert(varchar(10), ps.Person_BirthDay, 120) as BirthDate,
					case when ps.Sex_id = 2 then 'Female' else 'Male' end as Gender,
					ua.Address_Address as AddressName,
					coalesce(kls.KLAdr_Ocatd, kla.KLAdr_Ocatd, pstd.KLAdr_Ocatd) as AddressOkato,
					case when d.DocumentType_id IN (3,13) then d.Document_Ser else null end as DocSeries,
					case when d.DocumentType_id IN (3,13) then d.Document_Num else null end as DocNumber,
					case when d.DocumentType_id IN (3,13) then convert(varchar(10), d.Document_begDate, 120) else null end as DocDate,
					ph.PersonHeight_Height,
					pw.PersonWeight_Weight,
					mp.Person_SurName as MedPersonal_SurName,
					mp.Person_FirName as MedPersonal_FirName,
					mp.Person_SecName as MedPersonal_SecName,
					mp.Dolgnost_Name as MedPersonal_Post
				from
					Person with (nolock)
					inner join v_PersonState ps (nolock) on ps.Person_id = Person.Person_id
					cross apply (
						select top 1
							mp.Person_SurName,
							mp.Person_FirName,
							mp.Person_SecName,
							mp.Dolgnost_Name
						from
							v_MedPersonal mp (nolock)
						where
							mp.MedPersonal_id = Person.MedPersonal_id
							and mp.Lpu_id = Person.Lpu_id
					) mp
					left join v_Document d (nolock) on d.Document_id = ps.Document_id
					left join v_Address ua (nolock) on ua.Address_id = ps.UAddress_id
					left join v_KLArea kla (nolock) on kla.KLArea_id = coalesce(ua.KLTown_id, ua.KLCity_id, ua.KLSubRgn_id)
					left join v_KLStreet kls (nolock) on kls.KLStreet_id = ua.KLStreet_id
					left join v_PersonSprTerrDop pstd (nolock) on pstd.PersonSprTerrDop_id = ua.PersonSprTerrDop_id
					outer apply (
						select top 1
							PH.PersonHeight_Height
						from
							v_PersonHeight PH with (nolock)
						where
							PH.Person_id = Person.Person_id
						order by
							PH.PersonHeight_setDT DESC
					) PH
					outer apply (
						select top 1 
							PW.PersonWeight_Weight,
							PW.Okei_id
						from
							v_PersonWeight PW with (nolock)
						where
							PW.Person_id = Person.Person_id
						order by
							PW.PersonWeight_setDT DESC
					) PW
			", [
				'begDate' => $startDT->modify('-1 day')->format('Y-m-d')
			]);

			$startDT = date_create();

			$messages = [];
			foreach($resp_child as $one_child) {
				if (empty($one_child['AddressName'])) {
					$one_child['AddressName'] = 'Пермский край';
				}
				if (empty($one_child['AddressOkato'])) {
					$one_child['AddressOkato'] = '57000000000';
				}

				if ($one_child['KindId'] == 54) {
					if (!empty($one_child['PersonWeight_Weight']) && !empty($one_child['PersonHeight_Height'])) {
						$BodyMassindex = round($one_child['PersonWeight_Weight'] / ($one_child['PersonHeight_Height'] * $one_child['PersonHeight_Height'] / 10000), 2);
					} else {
						$BodyMassindex = null;
					}

					if (empty($BodyMassindex) || $BodyMassindex >= 18.5) {
						continue;
					}
				}

				if (!isset($messages[$one_child['Person_id']])) {
					$one_child['UpdatedDate'] = $startDT->format('c');
					$messages[$one_child['Person_id']] = [
						'Child' => $one_child,
						'IndicatorList' => [],
						'KindIds' => []
					];
				}

				$messages[$one_child['Person_id']]['KindIds'][] = $one_child['KindId'];

				$messages[$one_child['Person_id']]['IndicatorList'][] = [
					'KindId' => $one_child['KindId'],
					'StartDate' => $startDT->format('Y-m-d'),
					'FirstName' => $one_child['MedPersonal_FirName'],
					'LastName' => $one_child['MedPersonal_SurName'],
					'MiddleName' => $one_child['MedPersonal_SecName'],
					'Post' => $one_child['MedPersonal_Post'],
					'UpdatedDate' => $startDT->format('c'),
					'EmployeeUpdatedDate' => $startDT->format('c'),
					'EmployeeId' => $one_child['MedPersonal_id']
				];
			}

			$this->load->library('parser');
			$template = 'export_xml/export_pregnancy_isprof';

			if (!empty($messages)) {
				foreach ($messages as $Person_id => $message) {
					$params = [
						'ChangesCount' => count($message['IndicatorList']),
						'Version' => '04',
						'Uid' => GUID(),
						'Created' => $startDT->format('c'),
						'SystemCode' => 'Promed',
						'RequestId' => 1,
						'Child' => [
							$message['Child']
						],
						'IndicatorList' => $message['IndicatorList']
					];

					$body = '<?xml version="1.0" encoding="utf-8"?>' . $this->parser->parse($template, $params, true);

					$body = str_replace('<DocDate></DocDate>', '', $body);

					if (!empty($_REQUEST['getDebug'])) {
						echo '<textarea cols=150 rows=20>' . $body . '</textarea>';
					}

					$this->textlog->add(print_r(['body' => $body], true));

					$msg = new AMQPMessage($body, ['delivery_mode' => $this->delivery_mode]);
					$channel->basic_publish($msg, $this->exchange);

					$this->queryResult("
						declare
							@PreventionPersonList_id bigint = null,
							@ErrCode int,
							@ErrMessage varchar(4000);
						
						exec r59.p_PreventionPersonList_ins
							@PreventionPersonList_id = @PreventionPersonList_id output,
							@PreventionPersonList_MessageID = :PreventionPersonList_MessageID,
							@Person_id = :Person_id,
							@PreventionPersonList_sendDT = :PreventionPersonList_sendDT,
							@PreventionPersonList_IsLowIMT = :PreventionPersonList_IsLowIMT,
							@PreventionPersonList_IsPregnancy = :PreventionPersonList_IsPregnancy,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
							
						select @PreventionPersonList_id as PreventionPersonList_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					", [
						'PreventionPersonList_MessageID' => $params['Uid'],
						'Person_id' => $Person_id,
						'PreventionPersonList_sendDT' => $startDT->format('Y-m-d H:i:s'),
						'PreventionPersonList_IsLowIMT' => in_array(54, $message['KindIds']) ? 2 : 1,
						'PreventionPersonList_IsPregnancy' => in_array(55, $message['KindIds']) ? 2 : 1,
						'pmUser_id' => $data['pmUser_id']
					]);
				}
			} else {
				$params = [
					'ChangesCount' => 0,
					'Version' => '04',
					'Uid' => GUID(),
					'Created' => $startDT->format('c'),
					'SystemCode' => 'Promed',
					'RequestId' => 1,
					'Child' => [],
					'IndicatorList' => []
				];

				$body = '<?xml version="1.0" encoding="utf-8"?>' . $this->parser->parse($template, $params, true);

				if (!empty($_REQUEST['getDebug'])) {
					echo '<textarea cols=150 rows=20>' . $body . '</textarea>';
				}

				$this->textlog->add(print_r(['body' => $body], true));

				$msg = new AMQPMessage($body, ['delivery_mode' => $this->delivery_mode]);
				$channel->basic_publish($msg, $this->exchange);
			}

			$channel->close();
			$connection->close();

			restore_exception_handler();
		} catch (Exception $e) {
			restore_exception_handler();

			if (isset($channel)) $channel->close();
			if (isset($connection)) $connection->close();

			$code = $e->getCode();
			$error = $e->getMessage();

			$this->textlog->add($error);

			$response = $this->createError($code, $error);
			$response[0]['address'] = $this->host . ':' . $this->port;
			$response[0]['exchange'] = $this->exchange;

			return $response;
		}

		return [['success' => true]];
	}
}