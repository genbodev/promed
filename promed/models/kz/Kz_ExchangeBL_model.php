<?php defined('BASEPATH') or die ('No direct script access allowed');
require_once(APPPATH . 'models/ExchangeBL_model.php');

/**
 * Kz_ExchangeBL_model - модель
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package            Common
 * @access            public
 * @copyright        Copyright (c) 2019 Swan Ltd.
 *
 *
 * @property Options_model $Options_model
 * @property ServiceList_model $ServiceList_model
 * @property SwServiceKZ $swserviceaispolka
 * @property SwServiceKZ $swserviceexchangebl
 * @property Textlog $textlog
 *
 */
class Kz_ExchangeBL_model extends ExchangeBL_model
{

    /**
     * pmUser_id используемый по умолчанию для журнала действий
     */
    const DEFAULT_PM_USER_ID = 1;

    /**
     * Минимальная дата получения услуг
     */
    const MIN_DATE = '2019-06-27';

    /**
     * Запись еще не отправлялась
     */
    const APP_STATUS_PENDING = null;

    /**
     * Запись принята
     */
    const APP_STATUS_SUCCESS = 2;

    /**
     * Запись отклонена
     */
    const APP_STATUS_FAIL = 1;

    /**
     * Ответ не получен
     */
    const APP_STATUS_NO_RESPONSE = 3;

    /**
     * Лимит получения услуг за одну итерацию
     */
    const LIMIT = 100;

    /**
     * @var object
     */
    private $auth_data;

    /**
     * @var array
     */
    private $soap_clients = [];

    /**
     * @var ServiceListLog
     */
    private $log;

    /**
     * @var int
     */
    private $ServiceList_id;

    /**
     * @var bool
     */
    private $ServiceListLog_enable = true;

    /**
     * @var string Y-m-d
     */
    private $date_start;

    /**
     * Kz_ExchangeBL_model constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->helper('ShutdownErrorHandler');

        set_time_limit(86400);
        ini_set("default_socket_timeout", 600);
        ini_set('precision', '24');
        ini_set('soap.wsdl_cache_enabled', '0');
        ini_set('soap.wsdl_cache_ttl', '0');

		$log_name = (isset($_REQUEST['sqlMethods']) && strpos($_REQUEST['sqlMethods'], 'deleteEvnLabTestSql') !== false)
			? 'Kz_ExchangeBL_' . date('Y-m-d') . '_del.log' 
			: 'Kz_ExchangeBL_' . date('Y-m-d') . '.log';

        $this->load->library('textlog', ['file' => $log_name], 'textlog');

        $this->load->model('ServiceList_model');
        $this->ServiceList_id = $this->ServiceList_model->getServiceListId('ExchangeBL');

        $this->initServiceListLog();
    }

    /**
     * @return void
     */
    private function initServiceListLog()
    {
        $this->ServiceListLog_enable = !isset($_REQUEST['getDebug']);

        if ($this->ServiceListLog_enable) {
            $this->load->helper('ServiceListLog');
            $this->log = new ServiceListLog($this->ServiceList_id, self::DEFAULT_PM_USER_ID);

            if (!$this->isSuccessful($r = $this->log->start())) {
                $this->textlog('error during ServiceListLog log start, response: ' . print_r($r, true));
                throw new RuntimeException('error during ServiceListLog log start');
            }
        }
    }

    /**
     * @param array|string $message
     * @return array
     */
    private function serviceListLogAdd($message)
    {
        if ($this->ServiceListLog_enable) {
            return $this->log->add(false, $message);
        } else {
            return [];
        }
    }

    /**
     * @param bool $success
     */
    private function serviceListLogFinish($success)
    {
        if ($this->ServiceListLog_enable) {
            $this->log->finish($success);
        }
    }

    /**
     * @return int|null
     */
    private function serviceListLogGetId()
    {
        if ($this->ServiceListLog_enable) {
            return $this->log->getId();
        } else {
            return null;
        }
    }

    /**
     * Создание исключений по ошибкам
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     * @throws ErrorException
     */
    public function exceptionErrorHandler($errno, $errstr, $errfile, $errline)
    {
        switch ($errno) {
            case E_NOTICE:
            case E_USER_NOTICE:
                $errors = 'Notice';
                break;
            case E_WARNING:
            case E_USER_WARNING:
                $errors = 'Warning';
                break;
            case E_ERROR:
            case E_USER_ERROR:
                $errors = 'Fatal Error';
                break;
            default:
                $errors = 'Unknown Error';
                break;
        }

        $msg = sprintf('%s:  %s in %s on line %d', $errors, $errstr, $errfile, $errline);
        throw new ErrorException($msg, 0, $errno, $errfile, $errline);
    }

    /**
     * Обработка Fatal Error
     * @param callable $callback
     */
    public function shutdownErrorHandler($callback)
    {
        $error = error_get_last();

        if (!empty($error)) {
            switch ($error['type']) {
                case E_NOTICE:
                case E_USER_NOTICE:
                    $type = 'Notice';
                    break;
                case E_WARNING:
                case E_USER_WARNING:
                    $type = 'Warning';
                    break;
                case E_ERROR:
                case E_USER_ERROR:
                    $type = 'Fatal Error';
                    break;
                default:
                    $type = 'Unknown Error';
                    break;
            }

            $msg = sprintf('%s:  %s in %s on line %d', $type, $error['message'], $error['file'], $error['line']);

            call_user_func($callback, $msg);

            exit($error['type']);
        }
    }

    /**
     * @return object
     * @throws RuntimeException
     */
    private function getAuthData()
    {
        if ($this->auth_data === null) {
            $this->load->library('swServiceKZ', $this->config->item('ExchangeBL'), 'swserviceexchangebl');
            $this->auth_data = $this->swserviceexchangebl->getAuthResult();

            if (!is_object($this->auth_data)) {
                $this->textlog->add('error unable to get auth token');
                throw new RuntimeException('Unable to receive auth data');
            }
        }

        return $this->auth_data;
    }

    /**
     * @param $service_type
     * @return SoapClient
     * @throws SoapFault
     */
    private function getSoapClient($service_type)
    {
        if (!isset($this->soap_clients[$service_type])) {
            switch ($service_type) {
                case 'appwais':
                    $url = '/appwais/ws/ws1.1cws?wsdl';
                    break;
				case 'appprof':
					$url = $this->config->item('ExchangeProf')['url'];
					break;
                default:
                    throw new RuntimeException('Unknown soap service type');
                    break;
            }

            @$this->soap_clients[$service_type] = new SoapClient(
                // @todo кеширование схемы
                $this->config->item('ExchangeBL')['apihost'] . $url,
                // WSDL_PATH . 'ExchangeBL.wsdl',
                $this->config->item('ExchangeBLSoap')
            );
        }

        return $this->soap_clients[$service_type];
    }

    /**
     * @param $service_type
     * @param $method
     * @param array $params
     * @return mixed
     * @throws SoapFault
     */
    private function exec($service_type, $method, $params = [])
    {
        $original_timeout = ini_get('default_socket_timeout');
        ini_set('default_socket_timeout', 100);

        $log_param = $params;
        if (isset($params['Token'])) {
            unset($log_param['Token']);
        }
        $this->textlog->add(sprintf('exec: %s.%s %s', $service_type, $method, print_r($log_param, true)));
        unset($log_param);

        $client = $this->getSoapClient($service_type);

        try {
            if (!empty($params)) {
                $soap_params = new SoapParam($params, $method);
                $soap_result = $client->$method($soap_params);
            } else {
                $soap_result = $client->$method();
            }
        } catch (SoapFault $e) {
            $this->textlog->add(sprintf('error: %s.%s, message: %s, data:%s', $service_type, $method, $e->getMessage(), print_r(isset($soap_result) ? $soap_result : null, true)));
            throw new RuntimeException('Error during soap function call', 0, $e);
        }

        if (isset($_REQUEST['getDebug'])) {
            echo "<br><br>REQUEST:<br><textarea cols=150 rows=50>" . $client->__getLastRequest() . "</textarea><br>";
            echo "<br><br>RESPONSE:<br><textarea cols=150 rows=50>" . $client->__getLastResponse() . "</textarea><br>";
        }

        $this->textlog->add(sprintf('response: %s.%s, data: %s', $service_type, $method, print_r($soap_result, true)));

        ini_set('default_socket_timeout', $original_timeout);

        return $soap_result;
    }

    /**
     * Передача перечня выполненных медицинских услуг
     * в ПС АПП посредством сервиса ExchangeBL
     */
    public function syncAll()
    {
        registerShutdownErrorHandler([$this, 'shutdownErrorHandler'], function ($error) {
            $this->serviceListLogAdd(['Передача данных в систему АПП (ExchangeBL) завершена с ошибкой:', $error]);
            $this->serviceListLogFinish(false);
        });
        set_error_handler([$this, 'exceptionErrorHandler']);

        $this->serviceListLogAdd('Получение токена авторизации');
        $token = $this->getAuthData();

        $sql_methods = [
            'getEvnUslugaSql',
            'getEvnUslugaDispDopSql',
            'getEvnFuncSql',
            'getEvnLabSql',
            'getEvnLabTestSql',
            'getEvnUslugaDeletedSql',
            'getEvnUslugaDispDopDeletedSql',
            'getEvnFuncDeletedSql',
            'getEvnLabDeletedSql',
            'SetReferral',
            'deleteEvnLabTestSql',
            'Prof_beginTemp',
        ];
        if (isset($_REQUEST['sqlMethods'])) {
            $sql_methods = array_intersect($sql_methods, explode(',', $_REQUEST['sqlMethods']));
        }

        $errors = [];

        foreach ($sql_methods as $sql_method) {
            $max_iterations = isset($_REQUEST['getDebug']) ? 1 : 100000;
            $last_id = 0;
            $i = 0;
            do {
                $i++;

                try {
                    $this->serviceListLogAdd('Получение данных для отправки (' . $sql_method . '). Итерация ' . $i . '.');

                    $data = $this->getServiceList($sql_method, $last_id);

                    if (isset($_REQUEST['getDebug'])) {
                        echo '<br><br>Top ' . static::LIMIT . ' from ' . $sql_method . '<br><textarea cols=150 rows=50>' . htmlspecialchars(print_r($data, true)) . '</textarea><br>';
                    }
                } catch (Exception $e) {
                    $this->serviceListLogAdd(['Во время получения данных для отправки произошла ошибка:', $e->getMessage()]);

                    if (!in_array($e->getMessage(), $errors)) {
                        $errors[] = $e->getMessage();
                    }
                    continue;
                }

                try {
                    if (!empty($data)) {
                        $this->serviceListLogAdd('Передача данных сервису ExchangeBL');
						
						switch($sql_method) {
							case 'Prof_beginTemp': 
								$app = 'appprof';
								$soap_method = 'Prof_begin'; 
								$params = [
									'rToken' => $token->access_token,
									'ProfData' => [
										'Items' => $data
									]
								];
								break;
							case 'SetReferral': 
								$app = 'appwais';
								$soap_method = 'SetReferral'; 
								$params = [
									'Token' => $token->access_token,
									'sData' => $data,
								];
								break;
							default: 
								$app = 'appwais';
								$soap_method = 'SetData'; 
								$params = [
									'Token' => $token->access_token,
									'sData' => $data,
								];
								break;
						}

                        $response = $this->exec($app, $soap_method, $params);
						
						if ($sql_method == 'deleteEvnLabTestSql') {
							$this->processSetDataResponseDel($response, array_column($data, 'ID'));
						} else {
							$this->processSetDataResponse($response, array_column($data, 'ID'));
						}

                        $this->serviceListLogAdd('Передача данных завершена');
                    }
                } catch (Exception $e) {
                    $this->serviceListLogAdd(['Во время передачи данных сервису ExchangeBL произошла ошибка:', $e->getMessage()]);

                    if (!in_array($e->getMessage(), $errors)) {
                        $errors[] = $e->getMessage();
                    }
                }

                usleep(100);
                if ($i >= $max_iterations) {
                    break;
                }
            } while (!empty($data));
        }

        restore_exception_handler();

        if ($errors) {
            $this->serviceListLogFinish(false);

            $response = $this->createError('', 'Во время экспорта данных были ошибки');
            $response[0]['ServiceListLog_id'] = $this->serviceListLogGetId();

            $this->textlog->add('Во время экспорта данных произошли ошибки' . "\n" . implode("\n", $errors));

            return $response;
        } else {
            $this->serviceListLogFinish(true);

            return [
                ['success' => true, 'ServiceListLog_id' => $this->serviceListLogGetId()]
            ];
        }
    }

    /**
     * @param $$response
     */
    private function processSetDataResponse($response, $sended_ids)
    {
        if (empty($response->return)) {
            $this->textlog->add('unable to parse soap response from SetData method' . "\n" . print_r($response, true));
            throw new RuntimeException('Unable to parse soap response from SetData method');
        }
		
		if (isset($response->return->Report)) { // Prof_beginResponse
			return $this->processResultsProf($response, $sended_ids);
		}

        if (isset($response->return->ResultsDirection)) {
			return $this->processResultsDirection($response, $sended_ids);
		}

        if (empty($response->return->ResultsMIS)) {
            $this->textlog->add('unable to find ResultsMIS object in soap response from SetData method' . "\n" . print_r($response, true));
            throw new RuntimeException('Unable to find ResultsMIS object in soap response from SetData method');
        }

        // @todo Remove or comment after debug
        if (isset($_REQUEST['getDebug']) && isset($_REQUEST['skipSetAppStatus'])) {
            return;
        }
        
		if (!is_array($response->return->ResultsMIS) && is_object($response->return->ResultsMIS)) {
			$response->return->ResultsMIS = [$response->return->ResultsMIS];
		}

        foreach ($response->return->ResultsMIS as $result) {
            $key = array_search($result->ID, $sended_ids);
            if ($key !== false) {
                unset($sended_ids[$key]);
            }

            $this->setAppStatus($result->ID, $result->Status ? self::APP_STATUS_SUCCESS : self::APP_STATUS_FAIL, $result->Info);
        }

        foreach ($sended_ids as $ID) {
            $this->setAppStatus($ID, self::APP_STATUS_NO_RESPONSE);
        }
    }

    /**
     * @param $$response
     */
    private function processSetDataResponseDel($response, $sended_ids)
    {
        if (empty($response->return)) {
            $this->textlog->add('unable to parse soap response from SetData method' . "\n" . print_r($response, true));
            throw new RuntimeException('Unable to parse soap response from SetData method');
        }

        if (empty($response->return->ResultsMIS)) {
            $this->textlog->add('unable to find ResultsMIS object in soap response from SetData method' . "\n" . print_r($response, true));
            throw new RuntimeException('Unable to find ResultsMIS object in soap response from SetData method');
        }

        // @todo Remove or comment after debug
        if (isset($_REQUEST['getDebug']) && isset($_REQUEST['skipSetAppStatus'])) {
            return;
        }

        foreach ($response->return->ResultsMIS as $result) {
            $key = array_search($result->ID, $sended_ids);
            if ($key !== false) {
                unset($sended_ids[$key]);
            }

            $this->db->query("
                UPDATE tmp.UslugaTestDEL_KZ with(rowlock)
                SET UslugaTest_isDEL = :UslugaTest_isDEL
                WHERE UslugaTest_id = :UslugaTest_id
            ", [
                'UslugaTest_isDEL' => 2,
                'UslugaTest_id' => $result->ID
            ]);
        }

        foreach ($sended_ids as $ID) {
            $this->db->query("
                UPDATE tmp.UslugaTestDEL_KZ with(rowlock)
                SET UslugaTest_isDEL = :UslugaTest_isDEL
                WHERE UslugaTest_id = :UslugaTest_id
            ", [
                'UslugaTest_isDEL' => 3,
                'UslugaTest_id' => $result->ID
            ]);
        }
    }

    /**
     * @param $$response
     */
    private function processResultsDirection($response, $sended_ids)
    {
		
		if (!is_array($response->return->ResultsDirection) && is_object($response->return->ResultsDirection)) {
			$response->return->ResultsDirection = [$response->return->ResultsDirection];
		}
		
        foreach ($response->return->ResultsDirection as $result) {
            $key = array_search($result->ID, $sended_ids);
            if ($key !== false) {
                unset($sended_ids[$key]);
            }

			$evn_id = substr($result->ID, 1);
			
			if (!empty($result->FinanceSourceID)) {
				$pt = $this->getFirstResultFromQuery(
					"select PayType_id from r101.PayTypeLink (nolock) where PayTypeLink_SUR = ?",
					[$result->FinanceSourceID], 
					true
				);
				
				$opt = $this->getFirstResultFromQuery(
					"select PayType_id from v_EvnDirection_all (nolock) where EvnDirection_id = ?",
					[$evn_id], 
					true
				);
				
				if (!empty($pt) && $pt != $opt) {
					$this->db->query("
						UPDATE EvnDirection with(rowlock)
						SET PayType_id = :PayType_id
						WHERE EvnDirection_id = :EvnDirection_id
					", [
						'PayType_id' => $pt,
						'EvnDirection_id' => $evn_id,
					]);
				}
			}

			$this->saveAISResponse([
				'Evn_id' => $evn_id,
				'AISResponse_ErrorText' => $result->Info,
				'AISResponse_id' => null,
				'AISResponse_usid' => null,
				'AISResponse_uid' => null,
				'AISResponse_IsSuccess' => $result->Status ? 1 : 0,
				'AISFormLoad_id' => null,
				'AISResponse_IDEPS' => $result->IDEPS ?? null,
				'pmUser_id' => 1
			]);
        }

        foreach ($sended_ids as $ID) {
			$this->saveAISResponse([
				'Evn_id' => $evn_id,
				'AISResponse_ErrorText' => null,
				'AISResponse_id' => null,
				'AISResponse_usid' => null,
				'AISResponse_uid' => null,
				'AISResponse_IsSuccess' => 0,
				'AISFormLoad_id' => null,
				'AISResponse_IDEPS' => null,
				'pmUser_id' => 1
			]);
        }
    }

	/**
	 * -------
	 */
	private function processResultsProf($response, $sended_ids)
	{
		if (!is_array($response->return->Report) && is_object($response->return->Report)) {
			$response->return->Report = [$response->return->Report];
		}

		foreach ($response->return->Report as $result) {
			if (!empty($result->screening_id)) {
				$this->execCommonSP('r101.p_EvnLinkAPPTemp_ins', [
					'EvnLinkAPPTemp_id' => null,
					'Evn_id' => $result->screening_mis_id,
					'Screening_id' => $result->screening_id,
					'pmUser_id' => 1
				], 'array_assoc');
			}
		}
	}

    /**
     * @param $EnvUsluga_id
     * @param $EvnUsluga_IsAPP
     */
    private function setAppStatus($EnvUsluga_id, $EvnUsluga_IsAPP, $AISResponse_ErrorText = null)
    {
		$EnvUsluga_id = substr($EnvUsluga_id, 1);
        // Костыль для лабораторных тестов
        // Если длина ID больше либо 18 симв (больше или равна возможной длине bigint в бд)
        // @see static::getEvnLabTestSql()
        if (strlen($EnvUsluga_id) >= 18) {
            $UslugaTest_id = ltrim(substr($EnvUsluga_id, -15), '0');
            $this->db->query("
                UPDATE UslugaTest with(rowlock)
                SET UslugaTest_IsAPP = :UslugaTest_IsAPP
                WHERE UslugaTest_id = :UslugaTest_id
            ", [
                'UslugaTest_IsAPP' => $EvnUsluga_IsAPP,
                'UslugaTest_id' => $UslugaTest_id,
            ]);
        } else {
            $this->db->query("
                UPDATE EvnUsluga with(rowlock)
                SET EvnUsluga_IsAPP = :EvnUsluga_IsAPP
                WHERE EvnUsluga_id = :EvnUsluga_id
            ", [
                'EvnUsluga_IsAPP' => $EvnUsluga_IsAPP,
                'EvnUsluga_id' => $EnvUsluga_id,
            ]);
        }
        
		$this->saveAISResponse([
			'Evn_id' => (empty($UslugaTest_id))?$EnvUsluga_id:$UslugaTest_id,
			'AISResponse_id' => null,
			'AISResponse_ErrorText' => $AISResponse_ErrorText,
			'AISResponse_uid' => null,
			'AISResponse_IsSuccess' => null,
			'AISFormLoad_id' => null,
			'AISResponse_IDEPS' => null,
			'pmUser_id' => 1
		]);
    }

	/**
	 * Сохранение ответа
	 */
	function saveAISResponse($data) 
	{
		$check_record = $this->getFirstResultFromQuery("
			select AISResponse_id from r101.AISResponse (nolock) where Evn_id = ?
		", [$data['Evn_id']], true);

		$proc = 'p_AISResponse_ins';
		
		if (!empty($check_record)) {
			$proc = 'p_AISResponse_upd';
			$data['AISResponse_id'] = $check_record;
		}
		
		if (!isset($data['AISResponse_usid'])) $data['AISResponse_usid'] = null;
		return $this->queryResult("
			declare
				@AISResponse_id bigint = :AISResponse_id,
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec r101.{$proc}
				@AISResponse_id = @AISResponse_id output,
				@AISResponse_ErrorText = :AISResponse_ErrorText,
				@Evn_id = :Evn_id,
				@AISResponse_uid = :AISResponse_uid,
				@AISResponse_usid = :AISResponse_usid,
				@AISResponse_IsSuccess = :AISResponse_IsSuccess,
				@AISFormLoad_id = :AISFormLoad_id,
				@AISResponse_IDEPS = :AISResponse_IDEPS,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @AISResponse_id as AISResponse_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", $data);
	}

    /**
     * @return int
     */
    private function getPayTypeMoneyId()
    {
        static $pay_type_money_id = null;

        if ($pay_type_money_id === null) {
            $sql = "SELECT TOP 1 PayType_id FROM v_PayType WHERE PayType_SysNick='money'";
            $pay_type_money_id = (int)$this->getFirstResultFromQuery($sql);
        }

        return $pay_type_money_id;
    }

    /**
     * @param string $sql_method
     * @param string $last_id
     * @return array|false
     * @throws Exception
     */
    private function getServiceList($sql_method, &$last_id)
    {
        $result = $this->queryResult($this->$sql_method(), [
            'LAST_ID' => $last_id,
            'PayType_id' => $this->getPayTypeMoneyId(),
            'MIN_DATE' => $this->getAisReportingPeriod(),
        ]);

        if (!$result) {
            return [];
        }
		
		if ($sql_method == 'SetReferral') {
			foreach ($result as &$v) {
				$last_id = $v['ID'];
				$v['ID'] = $this->config->item('ExchangeBLSync')['regionId'].$v['ID'];
			}
			return $result;
		}
		
		if ($sql_method == 'Prof_beginTemp') {
			foreach ($result as &$v) {
				$last_id = $v['ID'];
			}
			return $result;
		}

        $data = [];
        foreach ($result as $v) {
			$last_id = $v['ID'];
			if(strlen($last_id) >= 18 && $sql_method != 'deleteEvnLabTestSql') {
				$last_id = ltrim(substr($last_id, -15), '0');
			}
            // https://redmine.swan-it.ru/issues/165055#note-14 административная единица

            // По найденному идентификатору рабочего места врача можно получить идентификатор подразделения и МО
			$PerformerDepartament = null;
			$GetPersonalWorkMOID = null;
            if (!empty($v['MedStaffFact_id'])) {
                $sql = "
					SELECT TOP 1
                        GPW.FPID as \"FPID\", -- Идентификатор Функционального Подразделения в СУР
						MO.ID as \"MOID\"
                    FROM
                        r101.v_GetPersonalWork as GPW with (nolock)
                        INNER JOIN r101.GetPersonalHistoryWP as GPHWP on GPHWP.ID = GPW.ID
						INNER JOIN dbo.v_MedStaffFact as WP on WP.MedStaffFact_id = GPHWP.WorkPlace_id
						INNER JOIN r101.GetMO as MO on MO.Lpu_id = WP.Lpu_id
                    WHERE
                        GPHWP.WorkPlace_id = :MedStaffFact_id
                ";
                $get_personal_work = $this->getFirstRowFromQuery($sql, ['MedStaffFact_id' => $v['MedStaffFact_id']]);
                if ($get_personal_work) {
                    $PerformerDepartament = $get_personal_work['FPID'];
                    $GetPersonalWorkMOID = $get_personal_work['MOID'];
                }
            }

            //Тип оплаты
			$PaymentType = null;

			$index = ($sql_method == 'getEvnLabTestSql')?'Parent_id':'ID';

			$sql = ($sql_method == 'getEvnUslugaSql')?"
				select top 100 ucat.UslugaComplexAttributeType_SysNick from v_EvnUsluga EU with (nolock)
				INNER JOIN v_UslugaComplex UC with(nolock) ON UC.UslugaComplex_id = EU.UslugaComplex_id
				inner join v_UslugaComplexAttribute uca (nolock) on uca.UslugaComplex_id = UC.UslugaComplex_id
				inner join v_UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
				where EU.EvnUsluga_id = :INDEX
			":"
				select top 100 ucat.UslugaComplexAttributeType_SysNick from v_EvnUslugaPar EUP with (nolock)
				--inner join v_EvnUsluga EU with (nolock) on EU.EvnUsluga_id = EUP.EvnUsluga_id
				--INNER JOIN v_UslugaComplex UC with(nolock) ON UC.UslugaComplex_id = EU.UslugaComplex_id
				INNER JOIN v_UslugaComplex UC with(nolock) ON UC.UslugaComplex_id = EUP.UslugaComplex_id
				inner join v_UslugaComplexAttribute uca (nolock) on uca.UslugaComplex_id = UC.UslugaComplex_id
				inner join v_UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
				where EUP.EvnUslugaPar_id = :INDEX
			";

			if ($sql_method == 'getEvnUslugaDispDopSql') {
				$PaymentType = 3;
			} else {
				$rslt = $this->queryResult($sql, ['INDEX' => $v[$index]]);
				$Kpn_count = 0;
				$IsNotKpn_count = 0;
				foreach ($rslt as $tmp) {
					if ( $tmp['UslugaComplexAttributeType_SysNick'] == 'Kpn' ) $Kpn_count = $Kpn_count + 1;
					if ( $tmp['UslugaComplexAttributeType_SysNick'] == 'IsNotKpn' ) $IsNotKpn_count = $IsNotKpn_count + 1;
				}
				$PaymentType = ($Kpn_count == 0 && $IsNotKpn_count > 0)?2:1;
			}

			$Customer = (!empty($v['Customer_MO_ID']))?$v['Customer_MO_ID']:0;

			$Perfomer = isset($GetPersonalWorkMOID) ? (string)$GetPersonalWorkMOID : ((!empty($v['Performer_MO_ID']))?$v['Performer_MO_ID']:0);
			
            //ищем FinanceSourceID
			$FinanceSourceID = null;

			if ($sql_method == 'getEvnUslugaDispDopSql') {//скрининг
				$isKPN = $this->getFirstRowFromQuery("
					select top 100 * from v_EvnUsluga EU with (nolock)
					INNER JOIN v_UslugaComplex UC with(nolock) ON UC.UslugaComplex_id = EU.UslugaComplex_id
					inner join V_UslugaComplexAttribute uca (nolock) on uca.UslugaComplex_id = UC.UslugaComplex_id
					inner join v_UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
					where EU.EvnUsluga_id = :EvnUsluga_id and ucat.UslugaComplexAttributeType_SysNick = 'Kpn'",
					['EvnUsluga_id'=>(string)$v['ID']],true
				);

				$isInList = $this->getFirstRowFromQuery("
					select top 100 * from v_UslugaComplex UC with (nolock)
					inner join v_EvnUsluga EU with (nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
					where UC.Region_id = 101 and UC.UslugaComplex_Code in ('B03.549.002','B03.803.002',
					'B03.804.002','B03.103.003','C01.037.001','C01.027.001','B08.737.001','D99.295.007',
					'B08.749.001','B08.749.002','B06.125.005','B06.469.005','B09.841.020','B09.820.020',
					'C03.018.004','C03.084.005','C03.086.005','D99.294.006','D41.312.427','B08.737.001',
					'A02.074.000','B03.401.003','A02.015.000','C02.001.000','A02.021.000','D95.470.223','A02.023.000')
					and UC.UslugaComplex_endDT is null and EU.EvnUsluga_id = :EvnUsluga_id",
					['EvnUsluga_id'=>(string)$v['ID']],true
				);

				if ($isKPN || $isInList){
					$FinanceSourceID = '50';
				} else {
					$isInFoms = $this->getFirstResultFromQuery("select Person_IsInFOMS from v_Person with (nolock) where Person_id=:Patient", ['Patient' => (string)$v['PatientID']]);
					$FinanceSourceID = ($isInFoms && $isInFoms==2)?'69':'71';
				}
			} elseif($sql_method == 'getEvnLabTestSql') {//Тесты
				$FinanceSourceID = $v['PayTypeLink_SUR'];
			} else {
				$sql = "
					select top 1 PTL.PayTypeLink_SUR from v_EvnUsluga EU with (nolock)
					inner join r101.v_PayTypeLink PTL with (nolock) on PTL.PayType_id = EU.PayType_id
					where EU.EvnUsluga_id = :EvnUsluga_id
				";

				$FinanceSourceID = $this->getFirstResultFromQuery($sql, ['EvnUsluga_id' => $v['ID']]);
			}

			//Идентификатор причины получения медицинской услуги
			$TreatmentReasonID = null;

			//словарь плохих ИД по 194443
			$TC = (empty($v['TreatmentClass_id']))?null:$v['TreatmentClass_id'];
			$badTreatmentClassIdDict = [
				'1' => '19', '2' => '19', '3' => '22', '4' => '38', '5' => '27', '6' => '27', '7' => '29', '8' => '29', '9' => '39', '10' => '30', '11' => '27'
			];
			if (!empty($badTreatmentClassIdDict[$TC])) $TC = $badTreatmentClassIdDict[$TC];

			if ($sql_method == 'getEvnUslugaSql') {
				$TreatmentReasonID = $this->getFirstResultFromQuery("select top 1 TreatmentClass_Code from v_TreatmentClass with (nolock) where TreatmentClass_id=:TC ", ['TC' => $TC],true);
			}

			if ($sql_method == 'getEvnUslugaDispDopSql') $TreatmentReasonID = 17;

			if (in_array($sql_method,['getEvnFuncSql','getEvnLabSql'])) {
				$TreatmentReasonID = $v['TreatmentClass_Code'];
			}

			if ($sql_method == 'getEvnLabTestSql') {
				$TreatmentReasonID = $this->getFirstResultFromQuery("select top 1 TreatmentClass_Code from v_TreatmentClass with (nolock) where TreatmentClass_id=:TC ", ['TC' => $TC],true);
				if (empty($TreatmentReasonID)) $TreatmentReasonID = 7;
			}

			if ($sql_method == 'deleteEvnLabTestSql') {
				$TreatmentReasonID = 15;
			}

			//Вид активного посещения
			$Visit_type = (!empty($v['VizitActiveType_id']))?'00000000'.$v['VizitActiveType_id']:null;

			if ($TreatmentReasonID == 18 && empty($Visit_type)) {
				$bDay = $v['PatientBirthDate']->format('Y-m-d H:i:s');

				if ($v['age'] >= 18) {
					$Visit_type = '000000008';
					if ($v['PatientSexID'] == 2){
						$Visit_type = '000000005';
						if ($v['MKB10'] == 'Z51.5') {
							$Visit_type = '000000008';
						} else if (in_array($v['MKB10'],[
							'O00.0', 'O00.1', 'O00.2', 'O00.8', 'O00.9', 'O08.0', 'O08.1', 'O08.2',
							'O08.3', 'O08.4', 'O08.5', 'O08.6', 'O08.7', 'O08.8', 'O08.9','O10.0', 'O10.1', 'O10.2', 'O10.3',
							'O10.4', 'O10.9', 'O12.0', 'O12.1', 'O12.2', 'O13', 'O15.0', 'O20.8', 'O20.9', 'O21.0', 'O21.1',
							'O21.2', 'O21.8', 'O21.9', 'O22.0', 'O22.1', 'O22.2', 'O22.3', 'O22.4', 'O22.5', 'O22.8', 'O22.9',
							'O23.0', 'O23.1', 'O23.2', 'O23.3', 'O23.4', 'O23.5', 'O23.9', 'O24.4', 'O24.9', 'O25', 'O26.0',
							'O26.1', 'O26.2', 'O26.3', 'O26.4', 'O26.6', 'O26.7', 'O26.8', 'O26.9', 'O29.0', 'O29.1', 'O29.2',
							'O29.3', 'O29.5', 'O29.6', 'O29.8', 'O29.9', 'O30.0', 'O30.1', 'O30.2', 'O30.8', 'O30.9', 'O31.1',
							'O31.2', 'O31.8', 'O32.4', 'O32.5', 'O34.5', 'O36.7', 'O47.0', 'O47.1', 'O48', 'O83.3', 'O98.0',
							'O98.1', 'O98.2', 'O98.3', 'O98.4', 'O98.5', 'O98.6', 'O98.8', 'O98.9', 'O99.0', 'O99.3', 'O99.4',
							'O99.5', 'O99.6', 'O99.7', 'O99.8', 'Z32.0', 'Z32.1', 'Z33', 'Z34.0', 'Z34.8', 'Z34.9', 'Z35.0',
							'Z35.1', 'Z35.2', 'Z35.3', 'Z35.4', 'Z35.7', 'Z35.8', 'Z35.9', 'Z64.0', 'Z87.5'
						])) {
							$Visit_type = '000000004';
						}
					}
				} else {
					$sql = "select dbo.Age_newborn('{$bDay}', dbo.tzGetDate()) as ageInMonth";
					$ageInMonth = $this->getFirstResultFromQuery($sql);
					if ($ageInMonth < 1){
						$Visit_type = '000000006';
					} else {
						$Visit_type = '000000007';
					}
				}
			} else if ($TreatmentReasonID == 10 && empty($Visit_type)) {
				$sql = "
					declare @prsn int = :prsn;
					declare @date datetime = :date;
					
					select top 100 EC.EvnClass_SysNick as nm, E.Evn_disDT as dt from v_Evn E with (nolock)
						inner join v_EvnClass EC with (nolock) on E.EvnClass_id = EC.EvnClass_id
						where 
							E.Person_id = @prsn
							and E.Evn_disDT < @date
							and E.EvnClass_id in (3,30)
					union
					select top 100 'Cmp' as nm, CCC.CmpCallCard_HospitalizedTime as dt from v_CmpCallCard CCC with (nolock)
						where 
							CCC.Person_id = @prsn
							and CCC.CmpCallCard_HospitalizedTime < @date
					order by dt desc
				";

				$params = [
					'prsn' => $v['PatientID'],
					'date' => $v['Date']
				];

				$tmp = $this->getFirstResultFromQuery($sql,$params);

				if (!empty($tmp)){
					switch ($tmp) {
						case 'EvnPL': $Visit_type = '000000003';break;
						case 'EvnPS': $Visit_type = '000000002';break;
						case 'Cmp': $Visit_type = '000000001';break;
						default: $Visit_type = '000000003';
					}
				} else {
					$Visit_type = '000000003';
				}
			}

			//Место оказания услуги
			$Place = null;

			if ($sql_method == 'getEvnUslugaSql') {
				if ($TreatmentReasonID == 9 || $v['ServiceType_id'] == 45) {
					$Place = 'Ц';
				} else if ( in_array($v['ServiceType_id'],[2,3,5,39]) || in_array($TreatmentReasonID,[10,18]) ) {
					$Place = 'Д';
				} else {
					switch ($v['ServiceType_id']) {
						case 40: $Place = 'К'; break;
						case 42: $Place = 'У'; break;
						case 43: $Place = 'Ш'; break;
						case 44: $Place = 'Р'; break;
						case 46: $Place = 'Т'; break;
					}
				}
			}

			$Place = (empty($Place))?'П':$Place;

			//Вид скрининга
			$Group_id = ($sql_method == 'getEvnUslugaDispDopSql' && $v['Group_id'])?$v['Group_id']:null;

			//Чек-лист (результаты) услуги
			$chek_list = null;

			if ($sql_method == 'getEvnUslugaDispDopSql') {
				$result = $this->db->query("
					select distinct
						scl.ScreenCheckList_PunktCode as chek_id,
						sv.ScreenValue_Code as chek_value,
						elapp.Screening_id as screening_id
					from 
						v_EvnUslugaDispDop EUDD (nolock)
						inner join r101.ScreenCheckListResult sclr (nolock) on sclr.EvnPLDisp_id = EUDD.EvnUslugaDispDop_pid
						inner join r101.ScreenCheckList scl (nolock) on scl.ScreenCheckList_id = sclr.ScreenCheckList_id
						inner join r101.ScreenValue sv (nolock) on sv.ScreenValue_id = sclr.ScreenValue_id
						inner join r101.EvnLinkAPP elapp (nolock) on elapp.Evn_id = EUDD.EvnUslugaDispDop_pid
					where EUDD.EvnUslugaDispDop_id = ? and scl.UslugaComplex_id = EUDD.UslugaComplex_id
				", [$v['ID']]);
				
				if (is_object($result)) {
					$result = $result->result('array');
					if (!empty($result[0])) {
						$chek_list = [];
						foreach ($result as $res) {
							$chek_list[] = [
								'scrining_id' => $res['screening_id'],
								'chek_id' => $res['chek_id'],
								'chek_value' => $res['chek_value']
							];
						}
					}
				}
			}

			if ($sql_method == 'getEvnUslugaSql' && !empty($v['scrining_id'])) {
				$chek_list = [
					'scrining_id' => $v['scrining_id'],
					'chek_id' => '',
					'chek_value' => ''
				];
			}
			
			//if ($sql_method == 'getEvnUslugaDispDopSql' && empty($chek_list)) continue; //Скрининг с пустым чек листом не должен уходить

			if ($sql_method != 'deleteEvnLabTestSql') {
				$v['ID'] = $this->config->item('ExchangeBLSync')['regionId'].$v['ID'];
			}

			//Идентификатор основной услуги
			$Parent_id = null;
			$ServiceKind2 = $v['ServiceKind2'];

			if ($v['ServiceKind2'] && $v['ServiceKind2'] == 1) {
				if (in_array($sql_method,['getEvnUslugaDispDopSql','getEvnUslugaSql','getEvnLabTestSql'])){
					if (empty($v['Parent_id'])) {
						$ServiceKind2 = 0;
					} else {
						$Parent_id = $this->config->item('ExchangeBLSync')['regionId'].$v['Parent_id'];
					}
				}
			}

			$Refferal_id = ($Customer != $Perfomer) ? $v['Refferal_id'] : null;

			$ServiceKind2 = (empty($Parent_id))?0:$ServiceKind2;

			$tmp = [
				//Идентификатор основной услуги
				'Parent_id' => $Parent_id,
				//Идентификатор направления
				'Refferal_id' => $Refferal_id,
				//Вид скрининга
				'Group_id' => $Group_id,
				//Вид активного посещения
				'Visit_type' => $Visit_type,
				//Идентификатор причины получения медицинской услуги
				'TreatmentReasonID' => $TreatmentReasonID,
				//Тип диагноза
				'Diag_type' => 1,
				//Место оказания услуги
				'Place' => $Place,
                // Идентификатор услуги в базе данных МИС
                'ID' => (string)$v['ID'],
                // Дата выполнения услуги
                'Date' => $v['Date']->format('Y-m-d') . ' 00:00:00.000',
                // Идентификатор поставщика (sur_id)
                // Если направившая МО на заполнена, то указываем МО прикрепления пациента. Иначе 0.
                // МО прикрепления пациента - Lpu_id в v_PersonState
                'Customer' => $Customer,
                // Идентификатор субподрядчика (sur_id)
                'Performer' => $Perfomer,
                // Подразделение поставщика
                'CustomerDepartament' => 0,
                // Подразделение субподрядчика
                'PerformerDepartament' => isset($PerformerDepartament) ? (string)$PerformerDepartament : 0,
                // Идентификатор сотрудника подрядчика
                'CustomerEmployee' => null,
                // Идентификатор сотрудника субподрядчика
                'PerformerEmployee' => isset($PerformerDepartament) ? (string)$PerformerDepartament : null,
                // Идентификатор услуги
                'ServiceID' => 0,
                // Идентификатор пациента в РПН
                'PatientID' => (string)$v['PatientID'],
                // Фамилия пациента *
                'PatientFirstName' => $v['PatientFirstName'],
                // Имя пациента *
                'PatientLastName' => $v['PatientLastName'],
                // Отчество пациента
                'PatientMiddleName' => $v['PatientMiddleName'],
                // Пол пациента
                // (1-неизвестно, 2-ж, 3-м)
                'PatientSexID' => $v['PatientSexID'],
                // ИИН пациента
                'PatientIDN' => $v['PatientIDN'],
                // Дата рождения пациента
                'PatientBirthDate' => !empty($v['PatientBirthDate']) ? $v['PatientBirthDate']->format('Y-m-d') . ' 00:00:00.000' : null,
                // Идентификатор источника финансирования
                'FinanceSourceID' => $FinanceSourceID,
                // Вид посещения
                'VisitKindID' => 0,
                // Стоимость услуги
                'Cost' => 0,
                // Количество оказанных услуг
                'Count' => $v['Count'],
                // Вид услуги (дополнительный)
                // (0 – основная услуга, 1 - дополнительная)
				'ServiceKind2' => $ServiceKind2,
                // Оборудование (может быть пустым)
                // True, false
                'LeasingID' => null,
                // Код диагноза (МКБ10)
                'MKB10' => $v['MKB10'],
                // Фамилия врача (может быть пустым)
                'DoctorFirstName' => $v['DoctorFirstName'],
                // Имя врача (может быть пустым)
                'DoctorLastName' => $v['DoctorLastName'],
                // Отчество врача (может быть пустым)
                'DoctorMiddleName' => $v['DoctorMiddleName'],
                // Вид направления
                'ServiceKind' => 0,
                // Вид услуги выполненные
                'ServiceCDSKind' => !empty($v['ServiceCDSKind']) ? $v['ServiceCDSKind'] : 0,
                // Тип оплаты
                // 1 – в рамках КПН, 2 – КДУ вне КПН, 3 – скрининг
                //'PaymentType' => $v['PaymentType'],
				'PaymentType' => $PaymentType,
                // Дата подтверждения карты
                'DateVerified' => $v['DateVerified'] ? $v['DateVerified']->format('Y-m-d') . ' 00:00:00.000' : null,
                'Result' => null,
                // Код услуги по тарификатору
                // A01.001.000 смотреть таблицу UslugComplex
                'Service' => $v['Service'],
                // Дата удаления услуги
                'DeleteDate' => $v['DeleteDate'] ? $v['DeleteDate']->format('Y-m-d') . ' 00:00:00.000' : null,
				// Идентификатор скрининга в ЕПС
                'scrining_id' => $v['scrining_id'] ?? null
            ];
			
			if (!empty($chek_list)) $tmp['chek_list'] = $chek_list;
			
			$data[] = $tmp;
        }

        return $data;
    }

    /**
     * @return string возвращает условие для WHERE в SQL запросе
     */
    private function syncLpuSqlWhere()
    {
        if ($sync_lpu_ids = $this->config->item('ExchangeBLSync')['Lpu_ids']) {
            return " AND Lpu.Lpu_id IN(" . implode(',', $sync_lpu_ids).")";
        }

        return '';
    }

    /**
     * @return string возвращает код ФСМС по МО
     */
    private function getFsmsSurId($lpu_id)
    {
		$def = null;
		foreach($this->config->item('ExchangeBLSync')['FSMS_SurId'] as $id => $list) {
			if(!count($list)) {
				$def = $id;
			}
			if(in_array($lpu_id, $list)) {
				return $id;
			}
		}
		
		return $def;
    }

    /**
     * Устанавливает период с которого сервис начитывает услуги
     * @param int $ais_reporting_period количество месяцев отчетного периода
     */
    public function setAisReportPeriodStartDate($ais_reporting_period)
    {
        $period = date('j') <= 5 ? $ais_reporting_period : $ais_reporting_period - 1;
		$this->date_start = date('Y-m-d', mktime(0,0,0,date('m')-$period,1));
        if ($this->date_start < ($year_start = date('Y-01-01'))) {
            $this->date_start = $year_start;
        }

        $service_start_at = $this->config->item('ExchangeBLSync')['service_start_at'];
        $this->date_start = $this->date_start > $service_start_at ? $this->date_start : $service_start_at;

		//Костыль к задаче 191368, кто знает возможно он еще пригодится. Не убираю.
		//if (date('n')==1) $this->date_start = date('Y-m-01', strtotime('- 1 months'));
    }

    /**
     * Отчетный период для передачи В АИС Поликлиника 25-5у
     * @return string Y-m-d
     * @throws Exception
     */
    private function getAisReportingPeriod()
    {
        if ($this->date_start === null) {
            throw new Exception('Не задана отчетный период для передачи в АИС Поликлиника');
        }

        return $this->date_start;
    }

    /**
     * @param bool $deleted получение услуг на удалеие
     * @return string
     */
    private function getEvnUslugaSql($deleted = false)
    {
        $limit = static::LIMIT;

        $sync_lpu = $this->syncLpuSqlWhere();

        if (!$deleted) {
            $v_EvnUsluga = 'v_EvnUsluga';
            $app_status = ' AND COALESCE(EU.EvnUsluga_IsAPP,0)<>' . static::APP_STATUS_SUCCESS;
            $deleted_condition = '';
            $deleted_select = 'null as DeleteDate';
        } else {
            $v_EvnUsluga = 'v_EvnUsluga_Del';
            $app_status = ' AND EU.EvnUsluga_IsAPP IS NOT NULL';
            $deleted_condition = ' AND EU.Evn_deleted=2';
            $deleted_select = 'getdate() as DeleteDate';
        }

        return <<<SQL
            SELECT TOP $limit
                -- select
                'First query' as separator,
                CASE
                    -- если PaymentType=3
                    WHEN UMT.UslugaMedType_Code=1800 THEN EU.EvnUsluga_setDT
                    ELSE NULL 
                END as DateVerified,
                'v_EvnUsluga' as separator_1,
                EU.EvnUsluga_id as 'ID',
                --EUVizit.EvnUsluga_id as 'Parent_id',
                case when EU.EvnPrescr_id is null and (EU.EvnUsluga_IsVizitCode is null or EU.EvnUsluga_IsVizitCode = 1) then EUVizit.EvnUsluga_id else null end as 'Parent_id',
                ISNULL(EU.EvnUsluga_Kolvo, 1) as 'Count',
                EU.EvnUsluga_setDT as 'Date',
                MO_Usluga.ID as Usluga_MO_ID,
                'v_PersonState' as separator_3,
                ISNULL(PRS.BDZ_id, PS.Person_id) as PatientID,
                PS.Person_SurName as PatientFirstName,
                PS.Person_FirName as PatientLastName,
                PS.Person_SecName as PatientMiddleName,
                PS.Person_Inn as PatientIDN,
                PS.Person_BirthDay as PatientBirthDate,
                dbo.Age2(PS.Person_BirthDay, dbo.tzGetDate()) as age,
                MO_Patient.ID as PatientLpuId,
                'v_Sex' as separator_4,
                CASE
                    WHEN Sex_SysNick='man' THEN 3
                    WHEN Sex_SysNick='woman' THEN 2
                    ELSE 1
                END PatientSexID,
                'v_UslugaComplex' as separator_5,
                UC.UslugaComplex_Code as Service,
                --case when EU.EvnUsluga_IsVizitCode = 2 then 0 else 1 end as ServiceKind2,
                --case when EU.EvnPrescr_id is null and (EU.EvnUsluga_IsVizitCode is null or EU.EvnUsluga_IsVizitCode = 1) then 1 else 0 end as ServiceKind2,
                case
                	when EU.EvnPrescr_id is not null then 0
                	when EVPL.EvnDirection_id is not null then 0
                	when EU.EvnUsluga_IsVizitCode = 2 then 0
                	when EUP.EvnUslugaPar_id is not null then 0
                	else 1 
                end as ServiceKind2,
                'v_Mkb10Cause' as separator_7,
                Diag.Diag_Code /*Mkb10.Mkb10Cause_Code*/ as MKB10,
                'v_MedStaffFact' as separator_8,
                MSF.MedStaffFact_id,
                MSF.Person_SurName as DoctorFirstName,
                MSF.Person_FirName as DoctorLastName,
                MSF.Person_SecName as DoctorMiddleName,
                'v_PayType' as separator_9,
                CASE
                    WHEN COALESCE(UMT.UslugaMedType_Code, 1400)=1400 THEN 1
                    WHEN UMT.UslugaMedType_Code=1800 THEN 3
                    ELSE 2
                END PaymentType,
                'v_EvnDirection' as separator_10,
                MO_Customer.ID as Customer_MO_ID,
                MO_Performer.ID as Performer_MO_ID,
                'v_UslugaMedType' as separator_11,
                COALESCE(UMT.UslugaMedType_Code, '1400') as ServiceCDSKind,
				Lpu.Lpu_id,
				EVPL.ServiceType_id,
				EVPL.TreatmentClass_id,
				coalesce(ed_air1.AISResponse_IDEPS,ed_air2.AISResponse_IDEPS,null) as Refferal_id,
				ELAPP.VizitActiveType_id,
				coalesce(edlatemp1.Screening_id,edlatemp2.Screening_id) as scrining_id,
                $deleted_select
                -- end select
            FROM
                -- from
                $v_EvnUsluga EU with (nolock)
                INNER JOIN v_EvnVizitPL EVPL with (nolock) on EVPL.EvnVizitPL_id = EU.EvnUsluga_pid
                left join r101.EvnLinkAPP ELAPP with (nolock) on ELAPP.Evn_id = EVPL.EvnVizitPL_id
                LEFT JOIN v_EvnUsluga EUVizit with (nolock) ON EUVizit.EvnUsluga_pid=EVPL.EvnVizitPL_id and EUVizit.EvnUsluga_IsVizitCode = 2
                INNER JOIN v_PersonState PS with(nolock) ON PS.Person_id = EVPL.Person_id
                INNER JOIN v_Person PRS with(nolock) ON PRS.Person_id = PS.Person_id
                INNER JOIN v_Sex Sex with(nolock) ON Sex.Sex_id = PS.Sex_id
                INNER JOIN v_UslugaComplex UC with(nolock) ON UC.UslugaComplex_id = EU.UslugaComplex_id
                LEFT JOIN v_Diag Diag with(nolock) ON Diag.Diag_id = ISNULL(EU.Diag_id, EVPL.Diag_id)
                -- LEFT JOIN v_Mkb10Cause Mkb10 with(nolock) ON Mkb10.Mkb10Cause_id = Diag.Mkb10Cause_id
                LEFT JOIN v_MedStaffFact MSF with(nolock) ON MSF.MedStaffFact_id = ISNULL(EU.MedStaffFact_id, EVPL.MedStaffFact_id)
                -- LEFT JOIN v_PayType PT with(nolock) ON PT.PayType_id = ISNULL(EU.PayType_id, EVPL.PayType_id)
                
                -- В направлении (EvnDirection) есть поля Lpu_sid - направившая МО, Lpu_did - мо, куда направиили.
                -- Customer - Идентификатор поставщика (sur_id) – Заполняется ID из СУР направившей МО.
                -- Performer - Идентификатор субподрядчика (sur_id) -  ID из СУР МО,  выполнившей услугу
                LEFT JOIN v_EvnDirection_all ED with(nolock) ON ED.EvnDirection_id = isnull(EVPL.EvnDirection_id, EU.EvnDirection_id)
                outer apply (
                	select top 1 Lpu_id
                	from v_PersonCard_all with (nolock)
                	where Person_id = PS.Person_id
                		and LpuAttachType_id = 1
                		and PersonCard_begDate <= EU.EvnUsluga_setDate
                		and (PersonCard_endDate is null or PersonCard_endDate >= EU.EvnUsluga_setDate) 
                ) PC
                LEFT JOIN r101.GetMO MO_Customer with(nolock) ON MO_Customer.Lpu_id = COALESCE(ED.Lpu_sid,EVPL.Lpu_id /*PC.Lpu_id, EU.Lpu_id*/) -- направившая МО, МО прикрепления пациента или МО, в которой оказан случай лечения
                LEFT JOIN r101.GetMO MO_Performer with(nolock) ON MO_Performer.Lpu_id = coalesce(EU.Lpu_uid,EU.Lpu_id)--ED.Lpu_did -- выполнившая МО
                
                LEFT JOIN r101.GetMO MO_Patient with(nolock) ON MO_Patient.Lpu_id = PS.Lpu_id -- прикрепления пациента
                
                LEFT JOIN r101.GetMO MO_Usluga with(nolock) ON MO_Usluga.Lpu_id = EU.Lpu_id -- ID МО, в которой внесен случай лечения/скрининг.
                
                INNER JOIN v_Lpu Lpu with(nolock) ON Lpu.Lpu_id=EU.Lpu_id -- оставляем только услуги выбранного региона
                
                LEFT JOIN r101.v_UslugaMedTypeLink UMTL with(nolock) ON UMTL.Evn_id=EU.EvnUsluga_id
                LEFT JOIN r101.v_UslugaMedTypeLink UMTL2 with(nolock) ON UMTL2.Evn_id=EVPL.EvnVizitPL_id
                LEFT JOIN r101.v_UslugaMedTypeLink UMTL3 with(nolock) ON UMTL3.Evn_id=EUVizit.EvnUsluga_id
                LEFT JOIN r101.v_UslugaMedType UMT with(nolock) ON UMT.UslugaMedType_id=COALESCE(UMTL.UslugaMedType_id,UMTL2.UslugaMedType_id,UMTL3.UslugaMedType_id)
                left join v_EvnUslugaPar EUP with(nolock) on EUP.EvnUslugaPar_id = EU.EvnUsluga_id
				
				left join r101.AISResponse ed_air1 (nolock) on ed_air1.AISResponse_IsSuccess = 1 and ed_air1.Evn_id = EU.EvnDirection_id
				left join r101.AISResponse ed_air2 (nolock) on ed_air2.AISResponse_IsSuccess = 1 and ed_air2.Evn_id = EVPL.EvnDirection_id
				left join TreatmentClass tc (nolock) on tc.TreatmentClass_id = EVPL.TreatmentClass_id
				
				outer apply (
					select top 1 
						edlatemp.Screening_id 
					from 
						r101.EvnLinkAPPTemp edlatemp (nolock)
					where 
						edlatemp.Evn_id = EVPL.EvnVizitPL_id
				) edlatemp1
				outer apply (
					select top 1 
						case when coalesce(tc.TreatmentClass_Code,'1') = '17' then edlatemp.Screening_id else null end as Screening_id
					from 
						r101.EvnLinkAPPTemp edlatemp (nolock)
						inner join v_Evn e (nolock) on E.evn_id = edlatemp.Evn_id
					where 
						e.Person_id = PRS.Person_id
				) edlatemp2
                -- end from
            WHERE
                -- where
                EU.EvnUsluga_id > :LAST_ID
                AND EVPL.PayType_id <> :PayType_id
                AND EU.EvnUsluga_setDT >= :MIN_DATE
                $app_status
                $sync_lpu
                $deleted_condition
                -- end where
            ORDER BY
                -- order by
                EU.EvnUsluga_id
                -- end order by
SQL;
    }

    /**
     * @param bool $deleted получение услуг на удалеие
     * @return string
     */
    private function getEvnUslugaDispDopSql($deleted = false)
    {
        $limit = static::LIMIT;

        $sync_lpu = $this->syncLpuSqlWhere();

        if (!$deleted) {
            $v_EvnUsluga = 'v_EvnUsluga';
            $app_status = ' AND COALESCE(EU.EvnUsluga_IsAPP,0)<>' . static::APP_STATUS_SUCCESS;
            $deleted_condition = '';
            $deleted_select = 'null as DeleteDate';
        } else {
            $v_EvnUsluga = 'v_EvnUsluga_Del';
            $app_status = ' AND EU.EvnUsluga_IsAPP IS NOT NULL';
            $deleted_condition = ' AND EU.Evn_deleted = 2';
            $deleted_select = 'getdate() as DeleteDate';
        }

        return <<<SQL
            SELECT TOP $limit
                -- select
                'Second query' as separator,
                EUDDLast.EvnUslugaDispDop_setDT as DateVerified,
                'v_EvnUsluga' as separator_1,
                EUDD.EvnUslugaDispDop_id as 'ID',
                EUDD.EvnUslugaDispDop_pid as 'Parent_id',
                ISNULL(EUDD.EvnUslugaDispDop_Kolvo, 1) as 'Count',
                EUDD.EvnUslugaDispDop_setDT as 'Date',
                MO_Usluga.ID as Usluga_MO_ID,
                'v_PersonState' as separator_3,
                ISNULL(PRS.BDZ_id, PS.Person_id) as PatientID,
                PS.Person_SurName as PatientFirstName,
                PS.Person_FirName as PatientLastName,
                PS.Person_SecName as PatientMiddleName,
                PS.Person_Inn as PatientIDN,
                PS.Person_BirthDay as PatientBirthDate,
                dbo.Age2(PS.Person_BirthDay, dbo.tzGetDate()) as age,
                MO_Patient.ID as PatientLpuId,
                'v_Sex' as separator_4,
                CASE
                    WHEN Sex_SysNick='man' THEN 3
                    WHEN Sex_SysNick='woman' THEN 2
                    ELSE 1
                END PatientSexID,
                'v_UslugaComplex' as separator_5,
                UC.UslugaComplex_Code as Service,
                case when UC.UslugaComplex_Code in ('A01.001.000', 'A02.002.000') then 0 else 1 end as ServiceKind2,
                'v_Mkb10Cause' as separator_7,
                Diag.Diag_Code /*Mkb10.Mkb10Cause_Code*/ as MKB10,
                'v_MedStaffFact' as separator_8,
                MSF.MedStaffFact_id,
                MSF.Person_SurName as DoctorFirstName,
                MSF.Person_FirName as DoctorLastName,
                MSF.Person_SecName as DoctorMiddleName,
                'v_PayType' as separator_9,
                '3' as PaymentType, -- Заполняем 3, если это услуга в рамках скрининга
                'v_EvnDirection' as separator_10,
                MO_Customer.ID as Customer_MO_ID,
                MO_Performer.ID as Performer_MO_ID,
                'v_UslugaMedType' as separator_11,
                '1800' as ServiceCDSKind, -- Для услуг в рамках скринингов заполняем всегда 1800
				Lpu.Lpu_id,
				ed_air.AISResponse_IDEPS as Refferal_id,
				coalesce (epldsl.ScreenType_id,epldscl.ScreenType_id) as "Group_id",
                $deleted_select
                -- end select
            FROM
                -- from
                v_EvnUslugaDispDop EUDD with (nolock)
                INNER JOIN $v_EvnUsluga EU with (nolock) on EU.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
                INNER JOIN v_EvnPLDisp EPLD with (nolock) on EPLD.EvnPLDisp_id = EUDD.EvnUslugaDispDop_rid
                INNER JOIN v_EvnUslugaDispDop EUDDLast with (nolock) on EUDDLast.EvnUslugaDispDop_rid = EPLD.EvnPLDisp_id
                    and EUDDLast.EvnUslugaDispDop_Index = EUDDLast.EvnUslugaDispDop_Count - 1
                INNER JOIN v_PersonState PS with(nolock) ON PS.Person_id = EPLD.Person_id
                INNER JOIN v_Person PRS with(nolock) ON PRS.Person_id = PS.Person_id
                INNER JOIN v_Sex Sex with(nolock) ON Sex.Sex_id = PS.Sex_id
                INNER JOIN v_UslugaComplex UC with(nolock) ON UC.UslugaComplex_id = EUDD.UslugaComplex_id
                LEFT JOIN v_Diag Diag with(nolock) ON Diag.Diag_id = EUDD.Diag_id
                -- LEFT JOIN v_Mkb10Cause Mkb10 with(nolock) ON Mkb10.Mkb10Cause_id = Diag.Mkb10Cause_id
                LEFT JOIN v_MedStaffFact MSF with(nolock) ON MSF.MedStaffFact_id = EUDD.MedStaffFact_id
                
                -- В направлении (EvnDirection) есть поля Lpu_sid - направившая МО, Lpu_did - мо, куда направиили.
                -- Customer - Идентификатор поставщика (sur_id) – Заполняется ID из СУР направившей МО.
                -- Performer - Идентификатор субподрядчика (sur_id) -  ID из СУР МО,  выполнившей услугу
                LEFT JOIN r101.GetMO MO_Customer ON MO_Customer.Lpu_id = EPLD.Lpu_id -- направившая МО
                LEFT JOIN r101.GetMO MO_Performer ON MO_Performer.Lpu_id = coalesce(EUDD.Lpu_uid,EUDD.Lpu_id)--EPLD.Lpu_id -- выполнившая МО
                
                LEFT JOIN r101.GetMO MO_Patient ON MO_Patient.Lpu_id = PS.Lpu_id -- прикрепления пациента
                
                LEFT JOIN r101.GetMO MO_Usluga ON MO_Usluga.Lpu_id = EUDD.Lpu_id -- ID МО, в которой внесен случай лечения/скрининг.
                
                INNER JOIN v_Lpu Lpu ON Lpu.Lpu_id=EUDD.Lpu_id -- оставляем только услуги выбранного региона
				
				left join r101.AISResponse ed_air (nolock) on ed_air.AISResponse_IsSuccess = 1 and ed_air.Evn_id = EU.EvnDirection_id
				left join r101.EvnPLDispScreenLink epldsl (nolock) on epldsl.EvnPLDispScreen_id = eudd.EvnUslugaDispDop_pid
				
				left join r101.EvnPLDispScreenChildLink epldscl on epldscl.EvnPLDispScreenChild_id = eudd.EvnUslugaDispDop_pid
                -- end from
            WHERE
                -- where
                EUDD.EvnUslugaDispDop_id > :LAST_ID
                AND ISNULL(EUDD.PayType_id, EPLD.PayType_id) <> :PayType_id
                AND EPLD.EvnClass_id IN (183, 187)
                AND EUDD.EvnUslugaDispDop_setDT >= :MIN_DATE
                $app_status
                $sync_lpu
                $deleted_condition
                -- end where
            ORDER BY
                -- order by
                EUDD.EvnUslugaDispDop_id
                -- end order by
SQL;
    }

    /**
     * @param bool $deleted получение услуг на удалеие
     * @return string
     */
    private function getEvnFuncSql($deleted = false)
    {
        $limit = static::LIMIT;

        $sync_lpu = $this->syncLpuSqlWhere();

        if (!$deleted) {
            $v_EvnUslugaPar = 'v_EvnUslugaPar';
            $app_status = ' AND COALESCE(EUP.EvnUslugaPar_IsAPP,0)<>' . static::APP_STATUS_SUCCESS;
            $deleted_condition = '';
            $deleted_select = 'null as DeleteDate';
        } else {
            $v_EvnUslugaPar = 'v_EvnUslugaPar_Del';
            $app_status = ' AND EUP.EvnUslugaPar_IsAPP IS NOT NULL';
            $deleted_condition = ' AND EUP.Evn_deleted = 2';
            $deleted_select = 'getdate() as DeleteDate';
        }

        return <<<SQL
            SELECT TOP $limit
                -- select
                'Third query' as separator,
                CASE
                    -- если PaymentType=3
                    WHEN UMT.UslugaMedType_Code=1800 THEN EUP.EvnUslugaPar_setDate
                    ELSE NULL 
                END as DateVerified,
                'v_EvnUsluga' as separator_1,
                EUP.EvnUslugaPar_id as 'ID',
                ISNULL(EUP.EvnUslugaPar_Kolvo, 1) as 'Count',
                EUP.EvnUslugaPar_setDate as 'Date',
                MO_Usluga.ID as Usluga_MO_ID,
                'v_PersonState' as separator_3,
                ISNULL(PRS.BDZ_id, PS.Person_id) as PatientID,
                PS.Person_SurName as PatientFirstName,
                PS.Person_FirName as PatientLastName,
                PS.Person_SecName as PatientMiddleName,
                PS.Person_Inn as PatientIDN,
                PS.Person_BirthDay as PatientBirthDate,
                dbo.Age2(PS.Person_BirthDay, dbo.tzGetDate()) as age,
                MO_Patient.ID as PatientLpuId,
                'v_Sex' as separator_4,
                CASE
                    WHEN Sex_SysNick='man' THEN 3
                    WHEN Sex_SysNick='woman' THEN 2
                    ELSE 1
                END PatientSexID,
                'v_UslugaComplex' as separator_5,
                UC.UslugaComplex_Code as Service,
                CASE
                    WHEN EUP.EvnUslugaPar_IsVizitCode = 2 THEN 0
                    ELSE 1
                END ServiceKind2,
                'v_Mkb10Cause' as separator_7,
                CASE
                    WHEN UC.UslugaComplex_Code IN ('C03.003.006', 'C03.010.006', 'C03.013.007') AND EUP.EvnUslugaPar_setDate BETWEEN '2019-06-01' AND '2019-07-31' THEN 'C00.1'
                    ELSE Diag.Diag_Code
                END as MKB10,
                'v_MedStaffFact' as separator_8,
                MSF.MedStaffFact_id,
                MSF.Person_SurName as DoctorFirstName,
                MSF.Person_FirName as DoctorLastName,
                MSF.Person_SecName as DoctorMiddleName,
                'v_PayType' as separator_9,
                CASE
                    WHEN COALESCE(UMT.UslugaMedType_Code, 1400)=1400 THEN 1
                    WHEN UMT.UslugaMedType_Code=1800 THEN 3
                    ELSE 2
                END PaymentType,
                'v_EvnDirection' as separator_10,
                MO_Customer.ID as Customer_MO_ID,
                MO_Performer.ID as Performer_MO_ID,
                'v_UslugaMedType' as separator_11,
                COALESCE(UMT.UslugaMedType_Code, '1400') as ServiceCDSKind,
				Lpu.Lpu_id,
				isnull(tc.TreatmentClass_Code, '7') as TreatmentClass_Code,
				ed_air.AISResponse_IDEPS as Refferal_id,
                $deleted_select
                -- end select
            FROM
                -- from
                v_EvnFuncRequest EFR with(nolock)
                INNER JOIN v_EvnDirection_all ED_all with(nolock) ON ED_all.EvnDirection_id=EFR.EvnFuncRequest_pid
            	INNER JOIN $v_EvnUslugaPar EUP with (nolock) ON EUP.EvnDirection_id = ED_all.EvnDirection_id
	            INNER JOIN UslugaComplex UC with (nolock) ON UC.UslugaComplex_id = EUP.UslugaComplex_id
	            
	            LEFT JOIN v_EvnSection ES with (nolock) on ES.EvnSection_id = EUP.EvnUslugaPar_pid
				LEFT JOIN v_EvnPS EPS with (nolock) on EPS.EvnPS_id = EUP.EvnUslugaPar_pid
                
                LEFT JOIN v_EvnVizitPL EVPL with (nolock) on EVPL.EvnVizitPL_id = EUP.EvnUslugaPar_pid
                LEFT JOIN v_EvnUsluga EUVizit with (nolock) ON EUVizit.EvnUsluga_pid=EVPL.EvnVizitPL_id and EUVizit.EvnUsluga_IsVizitCode = 2
                
                INNER JOIN v_PersonState PS with(nolock) ON PS.Person_id = EFR.Person_id
                INNER JOIN v_Person PRS with(nolock) ON PRS.Person_id = PS.Person_id
                INNER JOIN v_Sex Sex with(nolock) ON Sex.Sex_id = PS.Sex_id
                
                LEFT JOIN v_Diag Diag with(nolock) ON Diag.Diag_id = coalesce(EUP.Diag_id, EFR.Diag_id, ED_all.Diag_id)
                
                LEFT JOIN v_MedStaffFact MSF with(nolock) ON MSF.MedStaffFact_id = ISNULL(EUP.MedStaffFact_id, EVPL.MedStaffFact_id)
                
                -- В направлении (EvnDirection) есть поля Lpu_sid - направившая МО, Lpu_did - мо, куда направиили.
                -- Customer - Идентификатор поставщика (sur_id) – Заполняется ID из СУР направившей МО.
                -- Performer - Идентификатор субподрядчика (sur_id) -  ID из СУР МО,  выполнившей услугу
                outer apply (
                	select top 1 Lpu_id
                	from v_PersonCard_all with (nolock)
                	where Person_id = PS.Person_id
                		and LpuAttachType_id = 1
                		and PersonCard_begDate <= EUP.EvnUslugaPar_setDate
                		and (PersonCard_endDate is null or PersonCard_endDate >= EUP.EvnUslugaPar_setDate) 
                ) PC
                LEFT JOIN r101.GetMO MO_Customer ON MO_Customer.Lpu_id = COALESCE(ED_all.Lpu_sid,PC.Lpu_id,EUP.Lpu_id) -- направившая МО, МО прикрепления пациента или МО, в которой оказан случай лечения
                LEFT JOIN r101.GetMO MO_Performer ON MO_Performer.Lpu_id = coalesce(EUP.Lpu_uid,EUP.Lpu_id)--EFR.Lpu_id -- выполнившая МО
                
                LEFT JOIN r101.GetMO MO_Patient ON MO_Patient.Lpu_id = PS.Lpu_id -- прикрепления пациента
                
                LEFT JOIN r101.GetMO MO_Usluga ON MO_Usluga.Lpu_id = EFR.Lpu_id -- ID МО, в которой внесен случай лечения/скрининг.
                
                INNER JOIN v_Lpu Lpu ON Lpu.Lpu_id=EFR.Lpu_id -- оставляем только услуги выбранного региона
                
                LEFT JOIN r101.v_UslugaMedTypeLink UMTL ON UMTL.Evn_id=EFR.EvnFuncRequest_id
                LEFT JOIN r101.v_UslugaMedTypeLink UMTL2 ON UMTL2.Evn_id=EUP.EvnUslugaPar_id
                LEFT JOIN r101.v_UslugaMedTypeLink UMTL3 ON UMTL3.Evn_id=EUVizit.EvnUsluga_id
                LEFT JOIN r101.v_UslugaMedType UMT ON UMT.UslugaMedType_id=COALESCE(UMTL.UslugaMedType_id,UMTL2.UslugaMedType_id,UMTL3.UslugaMedType_id)
				
				left join r101.EvnLinkAPP edla (nolock) on edla.Evn_id = ED_all.EvnDirection_id
				left join v_TreatmentClass tc (nolock) on tc.TreatmentClass_id = edla.TreatmentClass_id
				
				left join r101.AISResponse ed_air (nolock) on ed_air.AISResponse_IsSuccess = 1 and ed_air.Evn_id = ED_all.EvnDirection_id
                -- end from
            WHERE
                -- where
                EUP.EvnUslugaPar_id > :LAST_ID
                AND ISNULL(EUP.PayType_id, 0) <> :PayType_id
                AND EUP.EvnUslugaPar_setDate >= :MIN_DATE
                AND ED_all.EvnDirection_pid is null
	            AND EUP.EvnUslugaPar_setDate is not null
	            AND EVPL.EvnVizitPL_id is null
	            
	            AND ES.EvnSection_id is null
				and EPS.EvnPS_id is null
                $app_status
                $sync_lpu
	            $deleted_condition
                -- end where
            ORDER BY
                -- order by
                EUP.EvnUslugaPar_id
                -- end order by
SQL;
    }

    /**
     * @param bool $deleted получение услуг на удалеие
     * @return string
     */
    private function getEvnLabSql($deleted = false)
    {
        $limit = static::LIMIT;

        $sync_lpu = $this->syncLpuSqlWhere();

        if (!$deleted) {
            $v_EvnUslugaPar = 'v_EvnUslugaPar';
            $app_status = ' AND COALESCE(EUP.EvnUslugaPar_IsAPP,0)<>' . static::APP_STATUS_SUCCESS;
            $deleted_condition = '';
            $deleted_select = 'null as DeleteDate';
        } else {
            $v_EvnUslugaPar = 'v_EvnUslugaPar_Del';
            $app_status = ' AND EUP.EvnUslugaPar_IsAPP IS NOT NULL';
            $deleted_condition = ' AND EUP.Evn_deleted = 2';
            $deleted_select = 'getdate() as DeleteDate';
        }

        return <<<SQL
            SELECT TOP $limit
                -- select
                'Fourth query' as separator,
                CASE
                    -- если PaymentType=3
                    WHEN UMT.UslugaMedType_Code=1800 THEN EUP.EvnUslugaPar_setDate
                    ELSE NULL 
                END as DateVerified,
                'v_EvnUsluga' as separator_1,
                EUP.EvnUslugaPar_id as 'ID',
                ISNULL(EUP.EvnUslugaPar_Kolvo, 1) as 'Count',
                EUP.EvnUslugaPar_setDate as 'Date',
                MO_Usluga.ID as Usluga_MO_ID,
                'v_PersonState' as separator_3,
                ISNULL(PRS.BDZ_id, PS.Person_id) as PatientID,
                PS.Person_SurName as PatientFirstName,
                PS.Person_FirName as PatientLastName,
                PS.Person_SecName as PatientMiddleName,
                PS.Person_Inn as PatientIDN,
                PS.Person_BirthDay as PatientBirthDate,
                dbo.Age2(PS.Person_BirthDay, dbo.tzGetDate()) as age,
                MO_Patient.ID as PatientLpuId,
                'v_Sex' as separator_4,
                CASE
                    WHEN Sex_SysNick='man' THEN 3
                    WHEN Sex_SysNick='woman' THEN 2
                    ELSE 1
                END PatientSexID,
                'v_UslugaComplex' as separator_5,
                UC.UslugaComplex_Code as Service,
                CASE
                    WHEN EUP.EvnUslugaPar_IsVizitCode = 2 THEN 0
                    ELSE 1
                END ServiceKind2,
                'v_Mkb10Cause' as separator_7,
                Diag.Diag_Code /*Mkb10.Mkb10Cause_Code*/ as MKB10,
                'v_MedStaffFact' as separator_8,
                MSF.MedStaffFact_id,
                MSF.Person_SurName as DoctorFirstName,
                MSF.Person_FirName as DoctorLastName,
                MSF.Person_SecName as DoctorMiddleName,
                'v_PayType' as separator_9,
                CASE
                    WHEN COALESCE(UMT.UslugaMedType_Code, 1400)=1400 THEN 1
                    WHEN UMT.UslugaMedType_Code=1800 THEN 3
                    ELSE 2
                END PaymentType,
                'v_EvnDirection' as separator_10,
                MO_Customer.ID as Customer_MO_ID,
                MO_Performer.ID as Performer_MO_ID,
                'v_UslugaMedType' as separator_11,
                COALESCE(UMT.UslugaMedType_Code, '1400') as ServiceCDSKind,
				Lpu.Lpu_id,
				isnull(tc.TreatmentClass_Code, '7') as TreatmentClass_Code,
				ed_air.AISResponse_IDEPS as Refferal_id,
                $deleted_select
                -- end select
            FROM
                -- from
                v_EvnLabRequest ELR with(nolock)
                INNER JOIN v_EvnLabSample ELS with(nolock) ON ELS.EvnLabRequest_id=ELR.EvnLabRequest_id
                INNER JOIN $v_EvnUslugaPar EUP with(nolock) ON EUP.EvnLabSample_id=ELS.EvnLabSample_id
                LEFT JOIN v_EvnDirection ED with (nolock) on ED.EvnDirection_id = ELR.EvnDirection_id
                INNER JOIN UslugaComplex UC ON UC.UslugaComplex_id=EUP.UslugaComplex_id
                
                LEFT JOIN v_EvnVizitPL EVPL with (nolock) on EVPL.EvnVizitPL_id = EUP.EvnUslugaPar_pid
                LEFT JOIN v_EvnSection ES with (nolock) on ES.EvnSection_id = EUP.EvnUslugaPar_pid
                LEFT JOIN v_EvnPS EPS with (nolock) on EPS.EvnPS_id = EUP.EvnUslugaPar_pid
                
                INNER JOIN v_PersonState PS with(nolock) ON PS.Person_id = ELR.Person_id
                INNER JOIN v_Person PRS with(nolock) ON PRS.Person_id = PS.Person_id
                INNER JOIN v_Sex Sex with(nolock) ON Sex.Sex_id = PS.Sex_id
                
                LEFT JOIN v_Diag Diag with(nolock) ON Diag.Diag_id = ISNULL(EUP.Diag_id, ELR.Diag_id)
                
                LEFT JOIN v_MedStaffFact MSF with(nolock) ON MSF.MedStaffFact_id = ISNULL(EUP.MedStaffFact_id, EVPL.MedStaffFact_id)
                
                -- В направлении (EvnDirection) есть поля Lpu_sid - направившая МО, Lpu_did - мо, куда направиили.
                -- Customer - Идентификатор поставщика (sur_id) – Заполняется ID из СУР направившей МО.
                -- Performer - Идентификатор субподрядчика (sur_id) -  ID из СУР МО,  выполнившей услугу
                outer apply (
                	select top 1 Lpu_id
                	from v_PersonCard_all with (nolock)
                	where Person_id = PS.Person_id
                		and LpuAttachType_id = 1
                		and PersonCard_begDate <= EUP.EvnUslugaPar_setDate
                		and (PersonCard_endDate is null or PersonCard_endDate >= EUP.EvnUslugaPar_setDate) 
                ) PC
                LEFT JOIN r101.GetMO MO_Customer ON MO_Customer.Lpu_id = COALESCE(ED.Lpu_sid, PC.Lpu_id,EUP.Lpu_id) -- направившая МО, МО прикрепления пациента или МО, в которой оказан случай лечения
                LEFT JOIN r101.GetMO MO_Performer ON MO_Performer.Lpu_id = coalesce(EUP.Lpu_uid,EUP.Lpu_id)--ELR.Lpu_id -- выполнившая МО
                
                LEFT JOIN r101.GetMO MO_Patient ON MO_Patient.Lpu_id = PS.Lpu_id -- прикрепления пациента
                
                LEFT JOIN r101.GetMO MO_Usluga ON MO_Usluga.Lpu_id = ELR.Lpu_id -- ID МО, в которой внесен случай лечения/скрининг.
                
                INNER JOIN v_Lpu Lpu ON Lpu.Lpu_id=ELR.Lpu_id -- оставляем только услуги выбранного региона
                
                LEFT JOIN r101.v_UslugaMedTypeLink UMTL ON UMTL.Evn_id=ELR.EvnLabRequest_id
                LEFT JOIN r101.v_UslugaMedTypeLink UMTL2 ON UMTL2.Evn_id=EUP.EvnUslugaPar_id
                LEFT JOIN r101.v_UslugaMedType UMT ON UMT.UslugaMedType_id=COALESCE(UMTL.UslugaMedType_id, UMTL2.UslugaMedType_id)
				
				left join r101.EvnLinkAPP edla (nolock) on edla.Evn_id = ed.EvnDirection_id
				left join v_TreatmentClass tc (nolock) on tc.TreatmentClass_id = edla.TreatmentClass_id
				
				left join r101.AISResponse ed_air (nolock) on ed_air.AISResponse_IsSuccess = 1 and ed_air.Evn_id = ed.EvnDirection_id
                -- end from
            WHERE
                -- where
                EUP.EvnUslugaPar_id > :LAST_ID
                AND ISNULL(EUP.PayType_id, 0) <> :PayType_id
                AND EUP.EvnUslugaPar_setDate >= :MIN_DATE
            	AND ELR.EvnLabRequest_pid IS NULL
	            AND EUP.EvnUslugaPar_setDate IS NOT NULL
	            AND EVPL.EvnVizitPL_id is null
	            AND ES.EvnSection_id is null
	            and EPS.EvnPS_id is null
                $app_status
                $sync_lpu
	            $deleted_condition
                -- end where
            ORDER BY
                -- order by
                EUP.EvnUslugaPar_id
                -- end order by
SQL;
    }


	/**
	 * Тесты лабораторных услуг
	 * @param bool $deleted получение услуг на удалеие
	 * @return string
	 */
	private function getEvnLabTestSql($deleted = false)
	{
		$limit = static::LIMIT;

		$sync_lpu = $this->syncLpuSqlWhere();

		if (!$deleted) {
			$v_EvnUslugaPar = 'v_EvnUslugaPar';
			$app_status = ' AND isnull(UT.UslugaTest_IsAPP, 0) != 2 ';
			$deleted_condition = '';
			$deleted_select = 'null as DeleteDate';
		} else {
			$v_EvnUslugaPar = 'v_EvnUslugaPar_Del';
			$app_status = ' AND UT.UslugaTest_IsAPP IS NOT NULL';
			$deleted_condition = ' AND EUP.Evn_deleted = 2';
			$deleted_select = 'getdate() as DeleteDate';
		}

        return <<<SQL
			with PayTypeList as (
				select PayType_id from v_PayType with (nolock) where PayType_id <> :PayType_id
			)
			
            SELECT TOP $limit
                -- select
                'Fith query' as separator,
                CASE
                    -- если PaymentType=3
                    WHEN UMT.UslugaMedType_Code=1800 THEN EUP.EvnUslugaPar_setDate
                    ELSE NULL 
                END as DateVerified,
                'v_EvnUsluga' as separator_1,
                
                -- Всё как в постановке :)
                (
                    RIGHT(CONVERT(nvarchar(20), EUP.EvnUslugaPar_id), 3)
                    -- добиваем идентификатор лаб.теста до 15 символов
                    + RIGHT('000000000000000' + CONVERT(nvarchar(20), UslugaTest_id), 15)
                )
                as 'ID',
                                
                ISNULL(EUP.EvnUslugaPar_Kolvo, 1) as 'Count',
                EUP.EvnUslugaPar_setDate as 'Date',
                --EUPVizit.EvnUsluga_id as 'Parent_id',
                case when EUP.EvnPrescr_id is null then EUPVizit.EvnUsluga_id else null end as 'Parent_id',
                MO_Usluga.ID as Usluga_MO_ID,
                'v_PersonState' as separator_3,
                ISNULL(PRS.BDZ_id, PS.Person_id) as PatientID,
                PS.Person_SurName as PatientFirstName,
                PS.Person_FirName as PatientLastName,
                PS.Person_SecName as PatientMiddleName,
                PS.Person_Inn as PatientIDN,
                PS.Person_BirthDay as PatientBirthDate,
                dbo.Age2(PS.Person_BirthDay, dbo.tzGetDate()) as age,
                MO_Patient.ID as PatientLpuId,
                'v_Sex' as separator_4,
                CASE
                    WHEN Sex_SysNick='man' THEN 3
                    WHEN Sex_SysNick='woman' THEN 2
                    ELSE 1
                END PatientSexID,
                'v_UslugaComplex' as separator_5,
                UC.UslugaComplex_Code as Service,
                --CASE WHEN EUP.EvnUslugaPar_IsVizitCode = 2 THEN 0 ELSE 1 END ServiceKind2, 
                --CASE WHEN EUP.EvnPrescr_id is null THEN 1 ELSE 0 END ServiceKind2,
                0 as ServiceKind2,
                'v_Mkb10Cause' as separator_7,
                Diag.Diag_Code /*Mkb10.Mkb10Cause_Code*/ as MKB10,
                'v_MedStaffFact' as separator_8,
                MSF.MedStaffFact_id,
                MSF.Person_SurName as DoctorFirstName,
                MSF.Person_FirName as DoctorLastName,
                MSF.Person_SecName as DoctorMiddleName,
                'v_PayType' as separator_9,
                CASE
                    WHEN COALESCE(UMT.UslugaMedType_Code, 1400)=1400 THEN 1
                    WHEN UMT.UslugaMedType_Code=1800 THEN 3
                    ELSE 2
                END PaymentType,
                'v_EvnDirection' as separator_10,
                MO_Customer.ID as Customer_MO_ID,
                MO_Performer.ID as Performer_MO_ID,
                'v_UslugaMedType' as separator_11,
                COALESCE(UMT.UslugaMedType_Code, '1400') as ServiceCDSKind,
				Lpu.Lpu_id,
				PTL.PayTypeLink_SUR,
				EVPL.TreatmentClass_id,
				ed_air.AISResponse_IDEPS as Refferal_id,
				ELAPP.VizitActiveType_id,
                $deleted_select
                -- end select
            FROM
                -- from
                v_EvnLabRequest ELR with(nolock)
                inner join r101.PayTypeLink PTL with(nolock) on ELR.PayType_id = PTL.PayType_id
                INNER JOIN v_EvnLabSample ELS with(nolock) ON ELS.EvnLabRequest_id=ELR.EvnLabRequest_id
                LEFT JOIN v_EvnDirection ED with (nolock) on ED.EvnDirection_id = ELR.EvnDirection_id
                INNER JOIN v_UslugaTest UT with(nolock) ON UT.EvnLabSample_id=ELS.EvnLabSample_id -- тесты лабораторных услуг
                INNER JOIN $v_EvnUslugaPar EUP with(nolock) ON EUP.EvnUslugaPar_id = UT.UslugaTest_pid and UT.UslugaComplex_id <> EUP.UslugaComplex_id
                INNER JOIN UslugaComplex UC (nolock) ON UC.UslugaComplex_id=UT.UslugaComplex_id
				
				--inner join UslugaComplexAttribute uca (nolock) on uca.UslugaComplex_id = UT.UslugaComplex_id
				--inner join UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
				
				LEFT JOIN v_EvnSection ES with (nolock) on ES.EvnSection_id = EUP.EvnUslugaPar_pid
				LEFT JOIN v_EvnPS EPS with (nolock) on EPS.EvnPS_id = EUP.EvnUslugaPar_pid
                
                LEFT JOIN v_EvnVizitPL EVPL with (nolock) on EVPL.EvnVizitPL_id = EUP.EvnUslugaPar_pid
                
                left join r101.EvnLinkAPP ELAPP with (nolock) on ELAPP.Evn_id = EVPL.EvnVizitPL_id
                
                left join v_EvnUsluga EUPVizit with (nolock) on EUPVizit.EvnUsluga_pid = EVPL.EvnVizitPL_id and EUPVizit.EvnUsluga_IsVizitCode = 2
                
                INNER JOIN v_PersonState PS with(nolock) ON PS.Person_id = ELR.Person_id
                INNER JOIN v_Person PRS with(nolock) ON PRS.Person_id = PS.Person_id
                
                INNER JOIN v_Sex Sex with(nolock) ON Sex.Sex_id = PS.Sex_id
                
                LEFT JOIN v_Diag Diag with(nolock) ON Diag.Diag_id = ISNULL(EUP.Diag_id, ELR.Diag_id)
                
                LEFT JOIN v_MedStaffFact MSF with(nolock) ON MSF.MedStaffFact_id = EUP.MedStaffFact_id
                
                -- В направлении (EvnDirection) есть поля Lpu_sid - направившая МО, Lpu_did - мо, куда направиили.
                -- Customer - Идентификатор поставщика (sur_id) – Заполняется ID из СУР направившей МО.
                -- Performer - Идентификатор субподрядчика (sur_id) -  ID из СУР МО,  выполнившей услугу
                outer apply (
                	select top 1 Lpu_id
                	from v_PersonCard_all with (nolock)
                	where Person_id = PS.Person_id
                		and LpuAttachType_id = 1
                		and PersonCard_begDate <= EUP.EvnUslugaPar_setDate
                		and (PersonCard_endDate is null or PersonCard_endDate >= EUP.EvnUslugaPar_setDate) 
                ) PC
                
                outer apply (
					select EDparent.* from v_EvnUsluga EUparent (nolock)
					left JOIN v_EvnVizitPL EVPLparent with (nolock) on EVPLparent.EvnVizitPL_id = EUparent.EvnUsluga_pid
					LEFT JOIN v_EvnDirection_all EDparent with(nolock) ON EDparent.EvnDirection_id = isnull(EVPLparent.EvnDirection_id, EUparent.EvnDirection_id)
					where EUparent.EvnUsluga_id = UT.UslugaTest_pid
				) as ParentTest
                
                LEFT JOIN r101.GetMO MO_Customer (nolock) ON MO_Customer.Lpu_id = COALESCE(ParentTest.Lpu_sid, EUP.Lpu_id)--EUP.Lpu_id--COALESCE(ED.Lpu_sid, PC.Lpu_id,EUP.Lpu_id) -- направившая МО, МО прикрепления пациента или МО, в которой оказан случай лечения
                LEFT JOIN r101.GetMO MO_Performer (nolock) ON MO_Performer.Lpu_id = coalesce(EUP.Lpu_uid,EUP.Lpu_id)--ELR.Lpu_id -- выполнившая МО
                
                LEFT JOIN r101.GetMO MO_Patient (nolock) ON MO_Patient.Lpu_id = PS.Lpu_id -- прикрепления пациента
                
                LEFT JOIN r101.GetMO MO_Usluga (nolock) ON MO_Usluga.Lpu_id = ELR.Lpu_id -- ID МО, в которой внесен случай лечения/скрининг.
                
                INNER JOIN v_Lpu Lpu (nolock) ON Lpu.Lpu_id=ELR.Lpu_id -- оставляем только услуги выбранного региона
                
                LEFT JOIN r101.v_UslugaMedTypeLink UMTL (nolock) ON UMTL.Evn_id=ELR.EvnLabRequest_id
                LEFT JOIN r101.v_UslugaMedTypeLink UMTL2 (nolock) ON UMTL2.Evn_id=EUP.EvnUslugaPar_id
                LEFT JOIN r101.v_UslugaMedType UMT (nolock) ON UMT.UslugaMedType_id=COALESCE(UMTL.UslugaMedType_id, UMTL2.UslugaMedType_id)
				
				left join r101.AISResponse ed_air (nolock) on ed_air.AISResponse_IsSuccess = 1 and ed_air.Evn_id = isnull(ParentTest.EvnDirection_id, ED.EvnDirection_id)
                -- end from
            WHERE
                -- where
                UT.UslugaTest_id > :LAST_ID
                AND ISNULL(EUP.PayType_id, 0) in (select PayType_id from PayTypeList)
                AND EUP.EvnUslugaPar_setDate >= :MIN_DATE
	            AND EUP.EvnUslugaPar_setDate IS NOT NULL
	            AND UT.UslugaTest_ResultApproved = 2
	            AND UC.UslugaCategory_id = 95
	            AND UC.UslugaComplexLevel_id = 3
	            AND ES.EvnSection_id is null
				and EPS.EvnPS_id is null
				AND UT.UslugaTest_setDT >= (cast(:MIN_DATE as datetime) - 365)
	            --AND ucat.UslugaComplexAttributeType_SysNick = 'gobmp'
                $app_status
                $sync_lpu
	            $deleted_condition
                -- end where
            ORDER BY
                -- order by
                UT.UslugaTest_id
                -- end order by
SQL;
	}

    /**
	 * Удаление тестов
	 */
	private function deleteEvnLabTestSql($deleted = false)
	{
		$limit = static::LIMIT;

        return <<<SQL
            SELECT TOP $limit
				cast(UslugaTest_id as varchar) as 'ID',
				null as ServiceKind2,
				15300000038121334 as PatientID,
				'' as PatientFirstName,
                '' as PatientLastName,
                '' as PatientMiddleName,
                180211603313 as PatientIDN,
                1 as PatientSexID,
                1 as Count,
                'Z00.0' as MKB10,
				'' as DoctorFirstName,
                '' as DoctorLastName,
                '' as DoctorMiddleName,
                'B02.061.001' as Service,
                628 as Customer_MO_ID,
                628 as Performer_MO_ID,
                cast('2018-02-11' as date) as PatientBirthDate,
				'' as DateVerified,
				cast('2020-05-02' as date) as [Date],
				cast('2020-05-06' as date) as DeleteDate
            FROM tmp.UslugaTestDEL_KZ (nolock)
            WHERE
				UslugaTest_id > :LAST_ID 
				and UslugaTest_isDEL is null
            ORDER BY
                UslugaTest_id
SQL;
	}

    /**
     * Отправка из МИС в ПС АПП перечня направлений на КДУ
     */
    function SetReferral() {
		
		$limit = static::LIMIT;

		$sync_lpu = $this->syncLpuSqlWhere();
		
        return <<<SQL
			select top $limit 
				ed.EvnDirection_id as ID,
				p.BDZ_id as person_id,
				convert(varchar(10), ed.EvnDirection_setDate, 120) as direction_date,
				mo.ID as sending_mo_id,
				gph.PersonalID as personal_id,
				gph.PostFuncID as position_id,
				mol.ID as directed_mo_id,
				isnull(uc1.UslugaComplex_Code,'1') as service_code,
				isnull(ptl.PayTypeLink_SUR, 59) as finance_source,
				1 as icd_type,
				d.Diag_Code as ocd_code,
				case
					when tc.TreatmentClass_Code in ('1','1.1','2.7') then '7'
					when tc.TreatmentClass_Code = '1.2' then '10'
					when tc.TreatmentClass_Code = '1.3' then '26'
					when tc.TreatmentClass_Code in ('2','2.1','2.6') then '15'
					when tc.TreatmentClass_Code in ('2.2','2.3') then '17'
					when tc.TreatmentClass_Code = '2.4' then '27'
					when tc.TreatmentClass_Code = '2.5' then '18'
					else coalesce(tc.TreatmentClass_Code, '7')
				end as direction_reason,
				convert(varchar(10), ed.EvnDirection_failDT, 120) as cancel_date,
				edla.PayTypeKAZ_id as paymentType,
				edla.ScreenType_id as group_id,--Group_id,
				case 
					when ed.DirType_id = 15 then isnull(ECPR.EvnCourseProc_Duration, 1) * isnull(ECPR.EvnCourseProc_MaxCountDay, 1)
					else 1 
				end as Count,
				coalesce (edlaSreen.Screening_id, edlatemp1.Screening_id, edlatemp2.Screening_id) as scrining_id--screening_id
			from v_EvnDirection_all ed (nolock)
				left join v_Org org (nolock) on org.Org_id = ed.Org_oid
				left join v_Lpu l1 (nolock) on l1.Org_id = org.Org_id
				inner join v_Lpu lpu (nolock) on lpu.Lpu_id = ISNULL(ed.Lpu_did, l1.Lpu_id)
				inner join v_Person p (nolock) on p.Person_id = ed.Person_id
				inner join r101.GetMO mo (nolock) on mo.Lpu_id = ed.Lpu_id
				inner join r101.GetMO mol (nolock) on mol.Lpu_id = lpu.Lpu_id
				inner join v_Diag d (nolock) on d.Diag_id = ed.Diag_id
				left join r101.AISResponse ar (nolock) on ar.Evn_id = ed.EvnDirection_id and ar.AISResponse_IsSuccess = 1
				outer apply (
					select top 1 pul.UslugaComplex_id
					from r101.ProfileUslugaLink pul  (nolock)
					where pul.LpuSectionProfile_id = ed.LpuSectionProfile_id
				) pul 
				left join v_UslugaComplex uc1 (nolock) on uc1.UslugaComplex_id = isnull(ed.UslugaComplex_did, pul.UslugaComplex_id)
				outer apply (
					select top 1
						gpw.PersonalID,
						gpw.PostFuncID
					from
						r101.v_GetPersonalHistoryWP gphwp (nolock)
						inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
						left join r101.v_GetMO gm (nolock) on gm.ID = gpw.MOID
					where
						gphwp.WorkPlace_id = ed.MedStaffFact_id
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
				) gph
				left join r101.EvnLinkAPP edla (nolock) on edla.Evn_id = ed.EvnDirection_id
				left join v_TreatmentClass tc (nolock) on tc.TreatmentClass_id = edla.TreatmentClass_id
				left join r101.PayTypeLink ptl (nolock) on ptl.PayType_id = ed.PayType_id
				left join v_EvnPS evnps (nolock) on evnps.EvnPS_id = ed.EvnDirection_rid
				outer apply (
					select top 1 ECPR.*
					from v_EvnPrescrDirection EPD (nolock)
						inner join v_EvnPrescrProc EPPR (nolock) on EPPR.EvnPrescrProc_id = EPD.EvnPrescr_id
						inner join v_EvnCourseProc ECPR (nolock) on ECPR.EvnCourseProc_id = EPPR.EvnCourse_id
					where EPD.EvnDirection_id = ED.EvnDirection_id
				) ECPR
				outer apply (
					select top 1 
						edlatemp.Screening_id
					from v_EvnPL EPL (nolock)
						inner join v_EvnVizitPL EVPL (nolock) on EPL.EvnPL_id = evpl.EvnVizitPL_pid
						inner join r101.EvnLinkAPPTemp edlatemp (nolock) on edlatemp.Evn_id = evpl.EvnVizitPL_id
					where 
						EPL.EvnPL_id = ED.EvnDirection_rid
				) edlatemp1
				outer apply (
					select top 1 
						case when coalesce(tc.TreatmentClass_Code,'1') = '17' then edlatemp.Screening_id else null end as Screening_id 
					from 
						r101.EvnLinkAPPTemp edlatemp (nolock)
						inner join v_Evn e (nolock) on E.evn_id = edlatemp.Evn_id
					where 
						e.Person_id = p.Person_id
				) edlatemp2
				left join v_EvnUslugaDispDop eudd (nolock) on eudd.EvnDirection_id = ed.EvnDirection_id
				left join r101.EvnLinkAPP edlaSreen (nolock) on edlaSreen.Evn_id = eudd.EvnUslugaDispDop_pid
			where 
				ed.EvnDirection_id > :LAST_ID
				and ed.DirType_id in (2, 3, 10, 11) 
				and (
					org.Org_IsNotForSystem = 2 or 
					lpu.Lpu_id != ed.Lpu_id
				)
				and evnps.EvnPS_id is null
				and p.BDZ_id is not null
				--and gph.PersonalID is not null
				and ed.EvnDirection_failDT is null
				and ar.AISResponse_IsSuccess is null
				and isnull(uc1.UslugaComplex_Code,'1') != '1'
				and ed.EvnDirection_setDate >= :MIN_DATE
                $sync_lpu
			order by 
				ed.EvnDirection_id
SQL;
    }

    /**
     * Запрос источника финансирования
     */
    function FinanceSource() {
		
        return <<<SQL
			declare @UslugaComplex_id bigint = :UslugaComplex_id;
			select top 1
				:Person_id as ID,
				p.BDZ_id as person_id,
				convert(varchar(10), :EvnDirection_setDate, 120) as direction_date,
				isnull(uc.UslugaComplex_Code, 1) as service_code,
				uc.UslugaComplex_id,
				1 as icd_type,
				case
					when tc.TreatmentClass_id = '37' or tc.TreatmentClass_id = '24' then 'K00.9'
					else d.Diag_Code	
				end as ocd_code,
				tc.TreatmentClass_Code as direction_reason,
				mo.ID as sended_mo,
				mol.ID as sending_mo
			from v_Person p (nolock)
				left join r101.GetMO mo (nolock) on mo.Lpu_id = :Lpu_id
				left join v_Lpu lpu (nolock) on lpu.Org_id = :Org_oid
				left join r101.GetMO mol (nolock) on mol.Lpu_id = coalesce(lpu.Lpu_id,:Lpu_did)
				inner join v_Diag d (nolock) on d.Diag_id = :Diag_id
				left join r101.ProfileUslugaLink pul (nolock) on pul.LpuSectionProfile_id = :LpuSectionProfile_id
				left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = isnull(@UslugaComplex_id, pul.UslugaComplex_id)
				inner join v_TreatmentClass tc (nolock) on tc.TreatmentClass_id = :TreatmentClass_id
			where 
				p.Person_id = :Person_id
SQL;
    }

	/**
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
    function getPayType($data)
    {
		if ($data['isStac'] == 2) {
			return $this->getPayTypeStac($data);
		} 
		
        $this->serviceListLogAdd('Получение токена авторизации');
        $result = $this->queryResult($this->FinanceSource(), $data);
		
		try {
			$token = $this->getAuthData();
		} catch (Exception $e) {
			$this->serviceListLogAdd('Передача данных завершена с ошибкой');
			return $this->getPayTypeLocal($data, $result);
		}
		
		try {
			$response = $this->exec('appwais', 'FinanceSource', [
				'Token' => $token->access_token,
				'sData' => $result,
			]);
		} catch (Exception $e) {
			$this->serviceListLogAdd('Передача данных завершена с ошибкой');
			return $this->getPayTypeLocal($data, $result);
		}

        if (!isset($response->return->ResultsDirection) || empty($response->return->ResultsDirection->FinanceSourceID) || empty($response->return) || empty($response)) {
			return ['success' => true, 'PayType_id' => 152, 'PayType_Name' => 'Не определен'];
		}

		$this->serviceListLogAdd('Передача данных завершена');
		
		$result = $this->getFirstRowFromQuery(
			"select PT.PayType_id,PT.PayType_Name from r101.PayTypeLink PTL (nolock)
					inner join PayType PT on PT.PayType_id = PTL.PayType_id
					where PayTypeLink_SUR = ?",
			[$response->return->ResultsDirection->FinanceSourceID], 
			true
		);

		$paytype_id = $result['PayType_id'];
		$paytype_name = $result['PayType_Name'];
		
		return ['success' => true, 'PayType_id' => $paytype_id, 'PayType_Name' => $paytype_name];
    }

	/**
	 * @param $data
	 * @param $result
	 * @return array
	 * @throws Exception
	 */
    private function getPayTypeLocal($data, $result)
    {
    	if (empty($data['UslugaComplex_id'])) {
			$data['UslugaComplex_id'] = $result[0]['UslugaComplex_id'];
		}

		if ($data['TreatmentClass_id'] != 36) {
			$chk = $this->getFirstResultFromQuery("
				select top 1 UslugaComplex_id
				from UslugaComplexAttribute uca (nolock) 
					inner join UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
				where 
					UslugaComplex_id = :UslugaComplex_id
					and ucat.UslugaComplexAttributeType_SysNick = 'Kpn'
			", $data);

			if ($chk !== false) {
				return ['success' => true, 'PayType_id' => 151, 'PayType_Name' => 'Республиканский (БП 067, ПП 100)"Трансферты ФСМС на оплату ГОБМП"'];
			}
		}

		$paytype_id = $this->getFirstResultFromQuery("
			declare @curDT datetime = dbo.tzGetDate();
			declare @Prizn_Strahov int = (select isnull(Person_IsInFOMS, 1) as Person_IsInFOMS from v_Person (nolock) where Person_id = :Person_id);
			declare @Prizn_DispU int = (
				select case when exists (
					select top 1 PersonDisp_id 
					from v_PersonDisp (nolock)
					where 
						Person_id = :Person_id and 
						Diag_id = :Diag_id and 
						isnull(PersonDisp_endDate, @curDT) >= @curDT
				) then 2 else 1 end as Prizn_DispU
			);

			select top 1 PayType_id
			from r101.FinanceSourcePL (nolock)
			where 
				TreatmentClass_id = :TreatmentClass_id and
				(PayTypeKAZ_id = :PayTypeKAZ_id or PayTypeKAZ_id is null) and 
				(UslugaComplex_id = :UslugaComplex_id or UslugaComplex_id is null) and 
				(Prizn_Strahov = @Prizn_Strahov or Prizn_Strahov is null) and 
				(Prizn_DispU = @Prizn_DispU or Prizn_DispU is null) and 
				(Diag_id = :Diag_id or Diag_id is null)
			order by (case PayType_id 
				when 151 then 1
				when 246 then 2
				when 248 then 3
			end)
		", $data);
		
        if ($paytype_id == false) {
			return ['success' => true, 'PayType_id' => 152, 'PayType_Name' => 'Не определен'];
		}
		
		return ['success' => true, 'PayType_id' => $paytype_id];
    }

	/**
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
    private function getPayTypeStac($data)
    {
    	if (!empty($data['EvnPS_id'])) {
			$dir_data = $this->getFirstRowFromQuery("
				select 
					ed.DirType_id,
					ed.LpuUnitType_id,
					edla.EvnLinkAPP_StageRecovery
				from v_EvnPS eps (nolock) 
					inner join v_EvnDirection (nolock) on eps.EvnDirection_id = ed.EvnDirection_id
					left join r101.EvnLinkAPP edla (nolock) on edla.Evn_id = ed.EvnDirection_id
				where EvnPS_id = :EvnPS_id
			", $data);
			
			if ($dir_data !== false) {
				$data['DirType_id'] = $dir_data['DirType_id'];
				$data['LpuUnitType_id'] = $dir_data['LpuUnitType_id'];
				$data['EvnLinkAPP_StageRecovery'] = $dir_data['EvnLinkAPP_StageRecovery'];
			}
			
			if (empty($data['PurposeHospital_id'])) {
				$data['PurposeHospital_id'] = $this->getFirstResultFromQuery("select PurposeHospital_id from r101.EvnLinkAPP (nolock) where Evn_id = :EvnPS_id", $data, true);
			}
		}
		
    	if (empty($data['Lpu_did'])) {
			$data['Lpu_did'] = $this->getFirstResultFromQuery("select Lpu_id from v_Lpu (nolock) where Org_id = :Org_oid", $data);
		}

		$data['isVillage'] = false === $this->getFirstResultFromQuery("
			select top 1 AttributeSignValue_id
			from v_AttributeSignValue ASV with(nolock)
				inner join v_AttributeSign [AS] with(nolock) on [AS].AttributeSign_id = ASV.AttributeSign_id
			where 
				[AS].AttributeSign_Code = 17 and 
				ASV.AttributeSignValue_TablePKey = :Lpu_did
		", $data) ? 1 : 2;

		// пока вообще без проверок
		return $this->_getPayTypeStac($data);

		if ($data['PurposeHospital_id'] == 1 && $data['LpuUnitType_id'] == 7) {
			return ['success' => true, 'PayType_id' => ($data['isVillage'] == 2 ? 238 : 151)];
		}

		if ($data['PrehospType_id'] == 2 && in_array($data['PurposeHospital_id'], [3, 4, 5])) {
			return ['success' => true, 'PayType_id' => ($data['isVillage'] == 2 ? 238 : 151)];
		}

		if (in_array($data['PrehospType_id'], [1, 3])) {
			return $this->getPayTypeStacExtr($data);
		}
		
		// https://redmine.swan-it.ru/issues/170566#note-63
		// пока отключить проверку на соответствие Диагноз/услуга в КВС/Движение (нет поля услуга). После полной доработки вернем
		if (!empty($data['EvnPS_id'])) {
			return $this->_getPayTypeStac($data); 
		}

		$pers_age = $this->getFirstResultFromQuery("select dbo.Age2(Person_Birthday, dbo.tzGetDate()) from v_PersonState (nolock) where Person_id = :Person_id", $data);
		
		if (in_array($data['LpuUnitType_id'], [6, 7])) {
			
			if ($data['PurposeHospital_id'] == 2) {
				$dus = $this->getFirstResultFromQuery("
					select DiagUslugaStac_id
					from r101.DiagUslugaStac (nolock) 
					where 
						UslugaComplex_id = :UslugaComplex_id and
						(Diag_id = :Diag_id or Diag_id is null) and
						isnull(DiagUslugaStac_isVillage,1) = :isVillage and 
						LpuUnitType_id = 6
				", $data);
				
				if ($dus != false) {
					return $this->_getPayTypeStac($data); 
				}
			}
			
			$dus = $this->getFirstResultFromQuery("
				select DiagUslugaStac_id
				from r101.DiagUslugaStac (nolock) 
				where 
					Diag_id = :Diag_id and
					(UslugaComplex_id = :UslugaComplex_id or UslugaComplex_id is null) and
					isnull(DiagUslugaStac_isVillage,1) = :isVillage and 
					LpuUnitType_id = 6
			", $data);
			
			if ($dus != false) {
				return $this->_getPayTypeStac($data); 
			}
		}
		
		if ($data['LpuUnitType_id'] == 1) {
			
			if ($pers_age < 18 || $pers_age >= 65) {
				return $this->_getPayTypeStac($data); 
			}
			
			if (in_array($data['PurposeHospital_id'], [2, 8, 9])) {
				$dus = $this->getFirstResultFromQuery("
					select DiagUslugaStac_id
					from r101.DiagUslugaStac (nolock) 
					where 
						UslugaComplex_id = :UslugaComplex_id and
						(Diag_id = :Diag_id or Diag_id is null) and
						isnull(DiagUslugaStac_isVillage,1) = :isVillage and 
						LpuUnitType_id = 1
				", $data);
				
				if ($dus != false) {
					return $this->_getPayTypeStac($data); 
				}
			}
			
			$dus = $this->getFirstResultFromQuery("
				select DiagUslugaStac_id
				from r101.DiagUslugaStac (nolock) 
				where 
					Diag_id = :Diag_id and
					(UslugaComplex_id = :UslugaComplex_id or UslugaComplex_id is null) and
					isnull(DiagUslugaStac_isVillage,1) = :isVillage and 
					LpuUnitType_id = 1
			", $data);
			
			if ($dus != false) {
				return $this->_getPayTypeStac($data); 
			}
			
			$dus = $this->getFirstResultFromQuery("
				select Diag_id
				from Diag (nolock) 
				where 
					Diag_id = :Diag_id and
					Diag_Code in (
						'G50.0', 'G54.0', 'G54.2', 'G54.4', 'М42.1', 'М51.1', 'E05.0', 'E05.2', 'E10.5', 'E11.5', 'E10.6', 'I20.8', 
						'I11.9', 'I67.8', 'K25.3', 'K26.3', 'K74.3', 'K74.4', 'М05.8', 'J18.0', 'J18.8', 'J44.8', 'J45.0', 'J45.8', 
						'N10.', 'N11.1', 'N11.8', 'N70.1', 'S06.0', 'J30.4', 'J45.0', 'D69.0', 'L50.0', 'Т78.4'
					)
			", $data);
			
			if ($dus != false) {
				return $this->_getPayTypeStac($data); 
			}
			
			throw new Exception('Данный код диагноза / услуги не входит в перечень кодов, подлежащих лечению в круглосуточном стационаре');
			
		} elseif ($data['LpuUnitType_id'] == 6) {
			
			if ($data['PurposeHospital_id'] == 2) {
				$dus = $this->getFirstResultFromQuery("
					select DiagUslugaStac_id
					from r101.DiagUslugaStac (nolock) 
					where 
						UslugaComplex_id = :UslugaComplex_id and
						(Diag_id = :Diag_id or Diag_id is null) and
						isnull(DiagUslugaStac_isVillage,1) = :isVillage and 
						LpuUnitType_id = 6
				", $data);
				
				if ($dus != false) {
					return $this->_getPayTypeStac($data); 
				}
			}
			
			$dus = $this->getFirstResultFromQuery("
				select DiagUslugaStac_id
				from r101.DiagUslugaStac (nolock) 
				where 
					Diag_id = :Diag_id and
					(UslugaComplex_id = :UslugaComplex_id or UslugaComplex_id is null) and
					isnull(DiagUslugaStac_isVillage,1) = :isVillage and 
					LpuUnitType_id = 6
			", $data);
			
			if ($dus != false) {
				return $this->_getPayTypeStac($data); 
			}
			
			throw new Exception('Данный код диагноза / услуги не входит в перечень кодов, подлежащих лечению в дневном стационаре');
		}
		
		
		return $this->_getPayTypeStac($data); 	
    }

	/**
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
    private function _getPayTypeStac($data)
    {
		$paytype_id = $this->getFinSrcByCritery($data);
    	if (is_array($paytype_id)) {
			return $paytype_id;
		}

    	if ($data['DirType_id'] == 5) {
    		return $this->getPayTypeStacExtr($data);
		}

		$paytype_id = $this->getFirstResultFromQuery("
			declare @EvnLinkAPP_StageRecovery int = :EvnLinkAPP_StageRecovery;
			declare @curDT datetime = dbo.tzGetDate();
			declare @Prizn_Strahov int = (select isnull(Person_IsInFOMS, 1) as Person_IsInFOMS from v_Person (nolock) where Person_id = :Person_id);
			declare @LpuSectionAge_id int = (
				select case when dbo.Age2(Person_Birthday, @curDT) >= 18 then 1 else 2 end as LpuSectionAge_id
				from v_PersonState (nolock) 
				where Person_id = :Person_id
			);
			declare @FinanceStac_IsForeigner int = (
				select case when (KLCountry_id = 398 or SocStatus_id = 10000092) then 1 else 2 end as FinanceStac_IsForeigner
				from v_PersonState (nolock) 
				where Person_id = :Person_id
			);

			select top 1 PayType_id
			from r101.FinanceStac (nolock)
			where 
				DirType_id = :DirType_id and
				(LpuUnitType_id = :LpuUnitType_id or LpuUnitType_id is null or DirType_id = 4) and 
				(isnull(FinanceStac_StageRecovery, 0) = isnull(@EvnLinkAPP_StageRecovery, 0)) and 
				(Diag_id = :Diag_id or Diag_id is null) and 
				(Diag_cid = :Diag_cid or Diag_cid is null) and 
				(FinanceStac_isInsured = @Prizn_Strahov or FinanceStac_isInsured is null) and 
				(LpuSectionAge_id = @LpuSectionAge_id or LpuSectionAge_id is null) and 
				(FinanceStac_IsForeigner = @FinanceStac_IsForeigner or FinanceStac_IsForeigner is null)
			order by (case PayType_id 
				when 151 then 1
				when 150 then 2
				when 153 then 3
			end)
		", $data);
		
        if ($paytype_id == false) {
			return ['success' => true, 'PayType_id' => 152];
		}
		
		return ['success' => true, 'PayType_id' => $paytype_id];
    }

	/**
	 * Определение источника финансирования при экстренной госпитализации
	 * @param $data
	 * @return array
	 */
	private function getPayTypeStacExtr($data)
	{
		if (empty($data['Lpu_did'])) {
			$data['Lpu_did'] = $this->getFirstResultFromQuery("select Lpu_id from v_Lpu (nolock) where Org_id = :Org_oid", $data);
		}

		$data['isVillage'] = false === $this->getFirstResultFromQuery("
			select top 1 AttributeSignValue_id
			from v_AttributeSignValue ASV with(nolock)
				inner join v_AttributeSign [AS] with(nolock) on [AS].AttributeSign_id = ASV.AttributeSign_id
			where 
				[AS].AttributeSign_Code = 17 and 
				ASV.AttributeSignValue_TablePKey = :Lpu_did
		", $data) ? 1 : 2;

		$chk = $this->getFirstResultFromQuery("
			select FinanceStac_id 
			from r101.FinanceStac (nolock)
			where 
				DirType_id = 5 and 
				LpuUnitType_id = 1 and 
				Diag_id = :Diag_id
		", $data);

		if ($chk !== false) {
			return ['success' => true, 'PayType_id' => ($data['isVillage'] == 2 ? 238 : 151)];
		}

		$chk = $this->getFirstResultFromQuery("
			select Person_id
			from v_PersonState ps (nolock)
				left join v_SocStatus sc (nolock) on sc.SocStatus_id = ps.SocStatus_id
				left join v_Document doc (nolock) on doc.Document_id = ps.Document_id
			where Person_id = :Person_id and (
					ps.KLCountry_id in (31,51,112,268,398,417,643,762,804,860,498,795) or
					sc.SocStatus_SysNick = 'oralman' or
					doc.DocumentType_id in (26, 30, 33) or 
					isnull(ps.Person_IsInErz,1) = 1
				)
		", $data);

		if ($chk !== false) {
			return ['success' => true, 'PayType_id' => ($data['isVillage'] == 2 ? 238 : 151)];
		}

		return ['success' => true, 'PayType_id' => 248];
	}

	/**
	 * Определение ИФ для стац через сервис БГ
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	private function getFinSrcByCritery($data) {
		$pay_type = null;
		$this->load->model('HospitalOffice_model');

		$params = $this->getFirstRowFromQuery("
			select top 1
				null as EvnDirection_id,
				diag.Diag_Code,
				diag.Diag_Name,
				ipht.p_publCod as StacType,
				uc.UslugaComplex_Code,
				ph.PurposeHospital_Code,
				convert(varchar(19), ps.Person_BirthDay, 127) as Person_BirthDay,
				hbp.p_publCod as BedProfile_Code,
				hbp.p_ID as BedProfile_id,
				cdiag.Diag_Code as Diag_CodeC,
				cdiag.Diag_Name as Diag_NameC
			from v_Diag diag with (nolock) 
				left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = :UslugaComplex_id
				left join r101.v_PurposeHospital ph (nolock) on ph.PurposeHospital_id = :PurposeHospital_id
				left join v_Diag cdiag with (nolock) on cdiag.Diag_id = :Diag_cid
				left join r101.GetBed gb (nolock) on gb.GetBed_id = :GetBed_id
				left join r101.hInPatientHelpTypes ipht (nolock) on ipht.p_ID = gb.StacType
				left join r101.hBedProfile hbp (nolock) on hbp.p_ID = gb.BedProfile
				left join v_PersonState ps (nolock) on ps.Person_id = :Person_id
			where 
				diag.Diag_id = :Diag_id
		", $data);

		$params = array_merge($params, $data);
		$alerts = [];
		$hosp_date = '';
		$bookingDateReserveId = null;

		$pt = $this->HospitalOffice_model->getFinSrcByCritery($params);

		if (count($pt) == 2 && $pt[1] == 0) {
			return false;
		}

		if (!empty($pt[2])) {
			$alerts[] = $pt[2];
		}

		$bd = $this->HospitalOffice_model->calculateAutoBookingDate($params);
		if ($bd['success'] == true && !empty($bd['date'])) {
			$hosp_date = date('d.m.Y', strtotime(substr($bd['date'],0,10)));
			$bookingDateReserveId = $bd['bookingDateReserveId'];
		} elseif (isset($bd['msg'])) {
			$alerts[] = $bd['msg'];
		}

		if (is_array($pt) && count($pt) == 3) {
			return [
				'success' => true,
				'PayType_id' => 152,
				'alert' => join('<br>', $alerts),
				'date' => $hosp_date,
				'bookingDateReserveId' => $bookingDateReserveId
			];
		}
		if (is_array($pt) && $pt[1] > 0) {
			$pay_type = $this->getFirstResultFromQuery("select top 1 PayType_id from r101.PayTypeLink (nolock) where PayTypeLink_PubCOD = ? ", [$pt[1]], true);
		}

		return [
			'success' => true,
			'PayType_id' => $pay_type,
			'alert' => join('<br>', $alerts),
			'date' => $hosp_date,
			'bookingDateReserveId' => $bookingDateReserveId
		];
	}

    /**
     * @return string
     */
    function GetReferral()
    {
		$lpu_ids = $this->config->item('ExchangeBLSync')['Lpu_ref_ids'];
		
		if (empty($lpu_ids) || !is_array($lpu_ids)) return false;
		
        $this->serviceListLogAdd('Получение токена авторизации');
        $token = $this->getAuthData();
		
        $this->load->model('EvnDirection_model');
		
		$this->serviceListLogAdd('Получение данных');
		
		foreach($lpu_ids as $lpu_id) {
			
			$moid = $this->getFirstResultFromQuery("select ID from r101.v_GetMO (nolock) where Lpu_id = ? ", [$lpu_id]);
			
			if (!$moid) continue;
			
			$response = $this->exec('appwais', 'GetReferral', [
				'Token' => $token->access_token,
				'directed_mo_id' => $moid,
				'date' => $this->currentDT->format('Y-m-d')
			]);
			
			if (isset($response->return->Services)) {
				$this->processGetReferral($response->return->Services, $lpu_id);
			}
			
		}
		
		$this->serviceListLogAdd('Получение данных завершено');
    }
	
    /**
     * @return string
     */
    function getRefferalByPerson($data)
    {		
		$Person_Inn = $this->getFirstResultFromQuery("select Person_Inn from v_PersonState (nolock) where Person_id = ? ", [$data['Person_id']]);
		
		if (!$Person_Inn) return []; 
			
        $this->serviceListLogAdd('Получение токена авторизации');
        $token = $this->getAuthData();
		
		$this->serviceListLogAdd('Получение данных');
		
		$response = $this->exec('appwais', 'GetRefferalByPerson', [
			'Token' => $token->access_token,
			'IIN' => $Person_Inn
		]);
		
		$this->serviceListLogAdd('Получение данных завершено');
			
		if (!isset($response->return->Services)) {
			return [];
		}
		
		$dir_list = $this->processGetReferral($response->return->Services, $data['Lpu_id']);
		
		return $dir_list;
	}
	
    /**
     * @return array
     */
    private function processGetReferral($services, $lpu_id)
    {
		$dir_list = [];
		
		if (!is_array($services) && is_object($services)) {
			$services = [$services];
		}
		
		foreach($services as $dir) {
			
			//$dir->ID = (float)substr($dir->ID, -19);
			$ideps = $dir->ID;
			$dir->ID = (int)substr($dir->ID, -9);
			
			//$Evn_id = $this->getFirstResultFromQuery("select Evn_id from r101.AISResponse (nolock) where AISResponse_usid = ? or AISResponse_IDEPS = ?", [$dir->ID, $ideps]);
			$Evn_id = $this->getFirstResultFromQuery("select Evn_id from r101.AISResponse (nolock) where AISResponse_IDEPS = ?", [$ideps]);

			if ($dir->direction_reason == 17) continue; //https://redmine.swan-it.ru/issues/200496#note-64
			
			if ($Evn_id != false) {
				
				$direction = $this->getFirstRowFromQuery("
					select 
						ed.EvnDirection_Num,
						uc.UslugaComplex_id,
						uc.UslugaComplex_Name,
						ed.EvnDirection_failDT,
						ed.EvnStatus_id,
						ED.DirType_id,
						dt.DirType_Name,
						ed.Diag_id,
						d.Diag_FullName as Diag_Name,
						ed.Lpu_sid,
						l.Lpu_Name,
						ed.LpuSectionProfile_id,
						lsp.LpuSectionProfile_Name
					from v_EvnDirection ed (nolock)
						left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = ed.UslugaComplex_did
						left join v_DirType DT (nolock) on dt.DirType_id = ed.DirType_id
						left join v_Diag d (nolock) on d.Diag_id = ed.Diag_id
						left join v_Lpu l (nolock) on l.Lpu_id = ed.Lpu_sid
						left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ed.LpuSectionProfile_id
					where 
						ed.EvnDirection_id = ? 
						and ISNULL(ED.EvnStatus_id, 16) not in (12,13,15) 
						and ED.DirFailType_id is null 
						and ED.EvnDirection_failDT is null
						and ED.Lpu_did = ?
				", [$Evn_id, $lpu_id]);
				
				if (empty($direction)) continue;
				
				$dir_list[] = [
					'EvnDirection_id' => $Evn_id,
					'EvnDirection_setDate' => date('d.m.Y', strtotime($dir->direction_date)),
					'EvnDirection_Num' => $direction['EvnDirection_Num'],
					'LpuSectionProfile_Name' => $direction['LpuSectionProfile_Name'],
					'Lpu_Name' => $direction['Lpu_Name'],
					'PrehospDirect_Name' => $this->getFirstResultFromQuery("
						select lpu.Lpu_Nick 
						from v_Lpu lpu (nolock) 
							inner join r101.v_GetMO mo (nolock) on mo.Lpu_id = lpu.Lpu_id
						where 
							mo.ID = ?
					", [$dir->sending_mo_id], true),
					'Diag_id' => $direction['Diag_id'],
					'Diag_Name' => $direction['Diag_Name'],
					'EvnLabRequest_UslugaName' => $direction['UslugaComplex_Name'],
					'UslugaComplex_Name' => $direction['UslugaComplex_Name'],
					'UslugaComplex_id' => $direction['UslugaComplex_id'],
					'EvnStatus_id' => $direction['EvnStatus_id'],
					'DirType_Name' => $direction['DirType_Name'],
					'enabled' => 2,
					'OuterKzDirection' => 1
				];
				continue;
			}
			
			$info = $this->getFirstRowFromQuery("
				select top 1
					ps.Person_id,
					ps.PersonEvn_id,
					ps.Server_id
				from v_Person p (nolock)
				inner join v_PersonState ps (nolock) on ps.Person_id = p.Person_id
				where 
					p.BDZ_id = :BDZ_id or 
					ps.Person_Inn = :Person_Inn 
			", [
				'BDZ_id' => $dir->person_id,
				'Person_Inn' => $dir->PatientIDN
			]);
			
			if (!$info) continue;

			$docInfo = $this->getFirstRowFromQuery("
				select top 1
					msf.MedStaffFact_id,
					msf.LpuSection_id,
					msf.MedPersonal_id
				from v_MedStaffFact msf (nolock)
					inner join r101.v_GetPersonalHistoryWP gphwp (nolock) on gphwp.WorkPlace_id = msf.MedStaffFact_id
					inner join r101.v_GetPersonalWork gpw (nolock) on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
				where 
					gpw.PersonalID = :PersonalID and
					gpw.PostFuncID = :PostFuncID
			",  [
				'PersonalID' => $dir->personal_id,
				'PostFuncID' => $dir->position_id
			]);
			
			$PayType_id = $this->getFirstResultFromQuery("select PayType_id from r101.PayTypeLink (nolock) where PayTypeLink_SUR = ? ", [$dir->finance_source], true);
			
			$Diag_id = $this->getFirstResultFromQuery("select Diag_id from v_Diag (nolock) where Diag_Code = ? ", [$dir->ocd_code], true);
			
			$Lpu = $this->getFirstRowFromQuery("
				select lpu.Lpu_id, lpu.Org_id, lpu.Lpu_Nick
				from v_Lpu lpu (nolock) 
					inner join r101.v_GetMO mo (nolock) on mo.Lpu_id = lpu.Lpu_id
				where 
					mo.ID = ?
			", [$dir->sending_mo_id], true);

			if (!$Lpu) continue;
			
			$Lpu_did = $this->getFirstResultFromQuery("select Lpu_id from r101.v_GetMO (nolock) where ID = ? ", [$dir->directed_mo_id]);
			
			$TreatmentClass_id = $this->getFirstResultFromQuery("select TreatmentClass_id from v_TreatmentClass (nolock) where TreatmentClass_Code = ? ", [(string)$dir->direction_reason], true);
			
			$UslugaComplex = $this->getFirstRowFromQuery("
				declare @curDt date = dbo.tzGetDate();
				select UslugaComplex_id, UslugaComplex_pid 
				from v_UslugaComplex (nolock) 
				where UslugaComplex_Code = ? and @curDt between UslugaComplex_begDT and ISNULL(UslugaComplex_endDT, @curDt)
			", [$dir->service_code]);
			
			if (false != $UslugaComplex) {
				$LpuSectionProfile_id = $this->getFirstResultFromQuery("select LpuSectionProfile_id from r101.ProfileUslugaLink (nolock) where UslugaComplex_id = ? ", [$UslugaComplex['UslugaComplex_id']], true);
			}
			
			$params = [
				'EvnDirection_pid' => null,
				'PersonEvn_id' => $info['PersonEvn_id'],
				'Server_id' => $info['Server_id'],
				'Person_id' => $info['Person_id'],
				'EvnDirection_Num' => '0',
				'EvnDirection_setDate' => $dir->direction_date,
				'PayType_id' => $PayType_id,
				'DirType_id' => $UslugaComplex['UslugaComplex_pid'] == 4560013 ? 3 : 10,
				'Diag_id' => $Diag_id,
				'EvnDirection_Descr' => null,
				'Org_oid' => $Lpu['Org_id'],
				'LpuSection_id' => $docInfo['LpuSection_id'] ?$docInfo['LpuSection_id']: null,
				'MedPersonal_id' => $docInfo['MedPersonal_id'] ?$docInfo['MedPersonal_id']: null,
				'MedStaffFact_id' => $docInfo['MedStaffFact_id'] ?$docInfo['MedStaffFact_id']: null,
				'From_MedStaffFact_id' => $docInfo['MedStaffFact_id'] ?$docInfo['MedStaffFact_id']: null,
				'MedPersonal_zid' => null,
				'Lpu_id' => $Lpu['Lpu_id'],
				'Lpu_did' => $Lpu_did,
				'LpuUnit_did' => null,
				'LpuSection_did' => null,
				'UslugaComplex_id' => $UslugaComplex['UslugaComplex_id'],
				'LpuSectionProfile_id' => $LpuSectionProfile_id ?$LpuSectionProfile_id: null,
				'MedPersonal_did' => null,
				'TimetableGraf_id' => null,
				'TimetableStac_id' => null,
				'TimetableMedService_id' => null,
				'TimetableResource_id' => null,
				'EvnDirection_IsCito' => null,
				'EvnPrescr_id' => null,
				'PrescriptionType_Code' => null,
				'MedService_id' => null,
				'Resource_id' => null,
				'pmUser_id' => 1,
				'EvnStatus_id' => 16,
				'EvnDirection_IsAuto' => 1,
				'StudyTarget_id' => null,
				'RecMethodType_id' => 13,
				'PrehospDirect_id' => 16,
				'RemoteConsultCause_id' => null, 
				//'PayTypeKAZ_id' => $dir->paymentType > 0 ? $dir->paymentType : null,
				'PayTypeKAZ_id' => in_array($dir->paymentType,[1,2,3]) ? $dir->paymentType : null, 
				'ScreenType_id' => $dir->group_id > 0 ? $dir->group_id : null, 
				'TreatmentClass_id' => $TreatmentClass_id > 0 ? $TreatmentClass_id : null,
				'OuterKzDirection' => 1
			];

			$this->load->model('EvnDirection_model');
			$resp_ed = $this->EvnDirection_model->saveEvnDirection($params);
			
			$this->textlog->add('Сохранение направления: ' . print_r($resp_ed, true));
			
			if ($this->isSuccessful($resp_ed) && !empty($resp_ed[0]['EvnDirection_id'])) {
				$this->saveAISResponse([
					'Evn_id' => $resp_ed[0]['EvnDirection_id'],
					'AISResponse_ErrorText' => null,
					'AISResponse_id' => null,
					'AISResponse_usid' => $dir->ID,
					'AISResponse_uid' => null,
					'AISResponse_IsSuccess' => 1,
					'AISFormLoad_id' => null,
					'AISResponse_IDEPS' => /*$dir->IDEPS ?? null*/ $ideps,
					'pmUser_id' => 1
				]);

				$direction = $this->getFirstRowFromQuery("
					select 
						ed.EvnDirection_Num,
						uc.UslugaComplex_id,
						uc.UslugaComplex_Name,
						ed.EvnDirection_failDT,
						ed.EvnStatus_id,
						ED.DirType_id,
						dt.DirType_Name,
						ed.Diag_id,
						d.Diag_FullName as Diag_Name,
						ed.Lpu_sid,
						l.Lpu_Name,
						ed.LpuSectionProfile_id,
						lsp.LpuSectionProfile_Name
					from v_EvnDirection ed (nolock)
						left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = ed.UslugaComplex_did
						left join v_DirType DT (nolock) on dt.DirType_id = ed.DirType_id
						left join v_Diag d (nolock) on d.Diag_id = ed.Diag_id
						left join v_Lpu l (nolock) on l.Lpu_id = ed.Lpu_sid
						left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ed.LpuSectionProfile_id
					where 
						ed.EvnDirection_id = ? 
						and ISNULL(ED.EvnStatus_id, 16) not in (12,13,15) 
						and ED.DirFailType_id is null 
						and ED.EvnDirection_failDT is null
						and ED.Lpu_did = ?
				", [$resp_ed[0]['EvnDirection_id'], $lpu_id]);
				
				$dir_list[] = [
					'EvnDirection_id' => $resp_ed[0]['EvnDirection_id'],
					'EvnDirection_setDate' => date('d.m.Y', strtotime($dir->direction_date)),
					'EvnDirection_Num' => $direction['EvnDirection_Num'],
					'LpuSectionProfile_Name' => $direction['LpuSectionProfile_Name'],
					'Lpu_Name' => $direction['Lpu_Name'],
					'PrehospDirect_Name' => $Lpu['Lpu_Nick'],
					'Diag_id' => $direction['Diag_id'],
					'Diag_Name' => $direction['Diag_Name'],
					'EvnLabRequest_UslugaName' => $direction['UslugaComplex_Name'],
					'UslugaComplex_Name' => $direction['UslugaComplex_Name'],
					'UslugaComplex_id' => $direction['UslugaComplex_id'],
					'EvnStatus_id' => $direction['EvnStatus_id'],
					'DirType_Name' => $direction['DirType_Name'],
					'enabled' => 2,
					'OuterKzDirection' => 1
				];
			}
		}
		
		return $dir_list;
    }

    /**
     * Отправка из МИС в ПС АПП информации о начале скрининга
     */
    function Prof_beginTemp() {
		
		$limit = static::LIMIT;

		$sync_lpu = $this->syncLpuSqlWhere();
		
        return <<<SQL
			select top $limit 
				evpl.EvnVizitPL_id as ID
				,edla.ScreenType_id as GroupID
				,p.BDZ_id as PatientID
				,convert(varchar(10), evpl.EvnVizitPL_setDate, 120) as screening_begin_date
				,evpl.EvnVizitPL_id as screening_mis_id
				,mo.ID as mo_id
			from v_EvnVizitPL evpl (nolock)
				inner join r101.EvnLinkAPP edla (nolock) on edla.Evn_id = evpl.EvnVizitPL_id
				inner join v_Person p (nolock) on p.Person_id = evpl.Person_id
                inner join v_Lpu Lpu (nolock) on Lpu.Lpu_id = evpl.Lpu_id
				inner join r101.GetMO mo (nolock) on mo.Lpu_id = evpl.Lpu_id
				left join r101.EvnLinkAPPTemp edlatemp (nolock) on edlatemp.Evn_id = evpl.EvnVizitPL_id
			where 
				evpl.EvnVizitPL_id > :LAST_ID
				and evpl.EvnVizitPL_setDate between '2020-01-01' and '2020-05-31'
				and evpl.TreatmentClass_id = 29
				and edla.ScreenType_id is not null
				and p.BDZ_id is not null
				and edlatemp.EvnLinkAPPTemp_id is null
                $sync_lpu
			order by 
				evpl.EvnVizitPL_id
SQL;
    }

    /**
     * @return string
     */
    private function getEvnUslugaDeletedSql()
    {
        return $this->getEvnUslugaSql(true);
    }

    /**
     * @return string
     */
    private function getEvnUslugaDispDopDeletedSql()
    {
        return $this->getEvnUslugaDispDopSql(true);
    }

    /**
     * @return string
     */
    private function getEvnFuncDeletedSql()
    {
        return $this->getEvnFuncSql(true);
    }

    /**
     * @return string
     */
    private function getEvnLabDeletedSql()
    {
        return $this->getEvnLabSql(true);
    }

    /**
     * @return string
     */
    function loadProfileUslugaLink() {
		return $this->queryResult("
			select  
				pul.ProfileUslugaLink_id,
				pul.LpuSectionProfile_id,
				pul.UslugaComplex_id,
				replace(substring(uca.UslugaComplexAttributeType_SysNick, 1, len(uca.UslugaComplexAttributeType_SysNick)-1), ' ', '') as UslugaComplex_AttributeList
			from r101.ProfileUslugaLink pul (nolock)
				outer apply (
					Select (
						select t2.UslugaComplexAttributeType_SysNick + ',' as 'data()'
						from v_UslugaComplexAttribute t1 (nolock)
						inner join v_UslugaComplexAttributeType t2 (nolock) on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
						where t1.UslugaComplex_id = pul.UslugaComplex_id
						for xml path('')
					) as UslugaComplexAttributeType_SysNick	
				) uca
		");
    }
    
    /**
	 * Отправка из МИС в ПС АПП информации о начале скрининга
	 */
    function sendEvnPLDispScreenAPP($data) {

		$token = $this->getAuthData();
		
		if (!empty($data['EvnPLDispScreen_id'])) {
			$screenId = $data['EvnPLDispScreen_id'];
			$child = '';
		} else if (!empty($data['EvnPLDispScreenChild_id'])) {
			$child = 'Child';
			$screenId = $data['EvnPLDispScreenChild_id'];
		}
		
		$result = $this->getFirstRowFromQuery("
			select top 1 
				screenLink.ScreenType_id as Group_id,
				p.bdz_id as PatientID,
				screen.EvnPLDispScreen{$child}_setDT as screening_begin_date,
				screen.EvnPLDispScreen{$child}_id as screening_mis_id,
				gmo.ID as Mo_id,
				diag.Diag_Code as diagnosis_code,
				screenLink.EvnPLDispScreen{$child}Link_IsProfBegin as IsProfBegin,
				screenLink.EvnPLDispScreen{$child}Link_IsProfEnd as IsProfEnd,
				screenLink.EvnPLDispScreen{$child}Link_id,
				elapp.Screening_id as screening_id,
				elapp.ScreenEndCause_id as completion_reason
			from 
				v_EvnPLDispScreen{$child} screen (nolock)
				inner join v_Person p (nolock) on p.person_id = screen.Person_id
				inner join r101.v_getmo gmo (nolock) on gmo.Lpu_id = screen.Lpu_id
				inner join r101.EvnPLDispScreen{$child}Link screenLink (nolock) on screenLink.EvnPLDispScreen{$child}_id = screen.EvnPLDispScreen{$child}_id
				left join r101.EvnLinkAPP elapp (nolock) on elapp.Evn_id = screen.EvnPLDispScreen{$child}_id
				outer apply (
					select top 1
						eudd.Diag_id,
						d.Diag_Code,
						eudd.EvnUslugaDispDop_setDT 
					from 
						v_EvnUslugaDispDop eudd (nolock)
						inner join v_Diag d (nolock) on d.Diag_id = eudd.Diag_id
					where eudd.EvnUslugaDispDop_rid = screen.EvnPLDispScreen{$child}_id
					order by eudd.EvnUslugaDispDop_setDT desc
				) diag
			where 1=1
				and screen.EvnPLDispScreen{$child}_id = ?
		", [$screenId]);
		
		$params = [
			'rToken' => $token->access_token,
			'ProfData' => [
				'Items' => [
					'GroupID' => $result['Group_id'],
					'PatientID' => $result['PatientID'],
					'screening_begin_date' => $result['screening_begin_date']->format('Y-m-d'),
					'screening_mis_id' => $result['screening_mis_id'],
					'mo_id' => $result['Mo_id'],
					'screening_id' => $result['screening_id'],
					'completion_reason' => $result['completion_reason'],
					'diagnosis_code' => $result['diagnosis_code'],
					'screening_end_date' => date('Y-m-d')
				]
			]
		];
		
		if ($data['EvnPLDispScreen_IsEndStage'] == 1) {
			if (empty($result['IsProfBegin'])) {
				$method = 'Prof_begin';
			} else {
				return ['success' => true, 'info' => 'Добавлен ранее', 'isAlreadySendedToApp' => true];
			}
		} else {
			if (empty($result['IsProfEnd'])) {
				$method = 'Prof_end';
			} else {
				return ['success' => true, 'info' => 'Добавлен ранее', 'isAlreadySendedToApp' => true];
			}
		}

		$response = $this->exec('appprof', $method, $params);
		
		if ($response->return->Report->status && $method == 'Prof_begin') {
			$checkrecord = $this->getFirstResultFromQuery("
				select EvnLinkAPP_id from r101.EvnLinkAPP (nolock) where Evn_id = ?
			", [$result['screening_mis_id']]);

			$proc = 'r101.p_EvnLinkAPP_upd';

			if (empty($checkrecord)) $proc = 'r101.p_EvnLinkAPP_ins';
			
			$this->execCommonSP($proc, [
				'EvnLinkAPP_id' => $checkrecord ?? null,
				'Evn_id' => $result['screening_mis_id'],
				'Screening_id' => $response->return->Report->screening_id,
				'pmUser_id' => $data['pmUser_id']
			], 'array_assoc');
			
			$this->db->query("
				update r101.EvnPLDispScreen{$child}Link
				set EvnPLDispScreen{$child}Link_IsProfBegin = 2
				where EvnPLDispScreen{$child}_id = ?
			",[$result['screening_mis_id']]);
		} else if ($response->return->Report->status && $method == 'Prof_end') {
			$this->db->query("
				update r101.EvnPLDispScreen{$child}Link
				set EvnPLDispScreen{$child}Link_IsProfEnd = 2
				where EvnPLDispScreen{$child}_id = ?
			",[$result['screening_mis_id']]);
		}
		
		return ['success' => $response->return->Report->status, 'info' => $response->return->Report->info];
	}
}