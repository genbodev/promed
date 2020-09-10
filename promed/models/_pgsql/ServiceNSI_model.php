<?php
defined('BASEPATH') or die ('No direct script access allowed');

/**
 * ServiceNSI_model - модель для работы с сервисом НСИ ЕГИСЗ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      ServiceNSI
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      26.11.2018
 *
 * @property-read ServiceList_model $ServiceList_model
 */
class ServiceNSI_model extends SwPgModel
{
    protected $ServiceList_id;

    /**
     * Конструктор
     */
    function __construct()
    {
        parent::__construct();
        $this->load->library('textlog', ['file' => 'ServiceNSI_' . date('Y-m-d') . '.log']);

        $this->load->model('ServiceList_model');
        $this->load->helper('ServiceListLog');
        $this->ServiceList_id = $this->ServiceList_model->getServiceListId('NSIEGISZUpdate');
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
     * Обработка Fatal Error
     * @param $func
     */
    public function shutdownErrorHandler($func)
    {
        $error = error_get_last();

        if (!empty($error)) {
            switch ($error['type']) {
                case E_NOTICE:
                case E_USER_NOTICE:
                    $type = "Notice";
                    break;
                case E_WARNING:
                case E_USER_WARNING:
                    $type = "Warning";
                    break;
                case E_ERROR:
                case E_USER_ERROR:
                    $type = "Fatal Error";
                    break;
                default:
                    $type = "Unknown Error";
                    break;
            }

            $msg = sprintf("%s:  %s in %s on line %d", $type, $error['message'], $error['file'], $error['line']);

            //$func($msg);
            call_user_func($func, $msg);

            exit($error['type']);
        }
    }

    /**
     * Выполнение запросов к сервису НСИ ЕГИСЗ и обработка ошибок, которые возвращает сервис
     * @param $method
     * @param null $data
     * @return array
     */
    public function exec($method, $data = null)
    {
        $this->load->library('swServiceNSI', $this->config->item('NSI_EGISZ'), 'service');
        $this->textlog->add("exec method: $method, data: " . print_r($data, true));
        $result = $this->service->data($method, $data);
        $this->textlog->add("result: " . print_r($result, true));
        if (is_object($result) && !empty($result->Message)) {
            $result = [
                'success' => false,
                'errorMsg' => 'Ошибка в работе сервиса НСИ ЕГИСЗ: ' . $result->Message
            ];
        }
        if (is_object($result) && !empty($result->ExceptionMessage)) {
            $result = [
                'success' => false,
                'errorMsg' => 'Ошибка в работе сервиса НСИ ЕГИСЗ: ' . $result->ExceptionMessage
            ];
        }
        return $result;
    }

    /**
     * Сохранение справочников в БД
     * @param $data
     * @return array|false
     */
    public function saveRefTableRegistry($data)
    {
        // проверяем, есть ли такой справочник у нас уже
        $resp = $this->queryResult("
			select
				RefTableRegistry_id as \"RefTableRegistry_id\",
				RefTableRegistry_SysNick as \"RefTableRegistry_SysNick\",
				RefTableRegistryVersion_id as \"RefTableRegistryVersion_id\",
				Org_id as \"Org_id\",
				OrgType_id as \"OrgType_id\",
				Org_rid as \"Org_rid\",
				RefTableRegistry_isRecUpd as \"RefTableRegistry_isRecUpd\"
			from
				nsi.v_RefTableRegistry
			where
				RefTableRegistry_Oid = :RefTableRegistry_Oid
			limit 1
		", [
            'RefTableRegistry_Oid' => $data['RefTableRegistry_Oid']
        ]);

        if (empty($resp[0]['RefTableRegistry_id'])) {
            $proc = 'p_RefTableRegistry_ins';
            $data['RefTableRegistry_id'] = null;
            $data['RefTableRegistry_SysNick'] = null;
            $data['RefTableRegistryVersion_id'] = null;
            $data['Org_id'] = null;
            $data['OrgType_id'] = null;
            $data['Org_rid'] = null;
            $data['RefTableRegistry_isRecUpd'] = null;
        } else {
            $proc = 'p_RefTableRegistry_upd';
            $data['RefTableRegistry_id'] = $resp[0]['RefTableRegistry_id'];
            $data['RefTableRegistry_SysNick'] = $resp[0]['RefTableRegistry_SysNick'];
            $data['RefTableRegistryVersion_id'] = $resp[0]['RefTableRegistryVersion_id'];
            $data['Org_id'] = $resp[0]['Org_id'];
            $data['OrgType_id'] = $resp[0]['OrgType_id'];
            $data['Org_rid'] = $resp[0]['Org_rid'];
            $data['RefTableRegistry_isRecUpd'] = $resp[0]['RefTableRegistry_isRecUpd'];
        }

        $query = "
			select
			    RefTableRegistry_id as \"RefTableRegistry_id\",
                :RefTableRegistry_isRecUpd as \"RefTableRegistry_isRecUpd\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from nsi.{$proc}
			(
				RefTableRegistry_id := :RefTableRegistry_id,
				RefTableRegistry_Oid := :RefTableRegistry_Oid,
				RefTableRegistry_Code := :RefTableRegistry_Code,
				RefTableRegistry_SysNick := :RefTableRegistry_SysNick,
				RefTableRegistryVersion_id := :RefTableRegistryVersion_id,
				RefTableRegistry_createDT := :RefTableRegistry_createDT,
				RefTableRegistry_publishDT := :RefTableRegistry_publishDT,
				RefTableRegistry_IsArchive := :RefTableRegistry_IsArchive,
				RefTableRegistry_FullName := :RefTableRegistry_FullName,
				RefTableRegistry_Nick := :RefTableRegistry_Nick,
				Org_id := :Org_id,
				OrgType_id := :OrgType_id,
				RefTableRegistry_Group := :RefTableRegistry_Group,
				Org_rid := :Org_rid,
				RefTableRegistry_isRecUpd := :RefTableRegistry_isRecUpd,
				pmUser_id := :pmUser_id
			)
		";

        return $this->queryResult($query, $data);
    }

    /**
     * Сохранение версий справочников в БД
     * @param $data
     * @return array|false
     */
    public function saveRefTableRegistryVersion($data)
    {
        // проверяем, есть ли такая версия у нас уже
        $resp = $this->queryResult("
			select
				RefTableRegistryVersion_id as \"RefTableRegistryVersion_id\"
			from
				nsi.v_RefTableRegistryVersion
			where
				RefTableRegistry_id = :RefTableRegistry_id
            and
                RefTableRegistryVersion_Num = :RefTableRegistryVersion_Num
            limit 1
		", [
            'RefTableRegistry_id' => $data['RefTableRegistry_id'],
            'RefTableRegistryVersion_Num' => $data['RefTableRegistryVersion_Num']
        ]);

        if (!empty($resp[0]['RefTableRegistryVersion_id'])) {
            $proc = 'p_RefTableRegistryVersion_upd';
            $data['RefTableRegistryVersion_id'] = $resp[0]['RefTableRegistryVersion_id'];
        } else {
            $proc = 'p_RefTableRegistryVersion_ins';
            $data['RefTableRegistryVersion_id'] = null;
        }

        $query = "
			select
			    RefTableRegistryVersion_id as \"RefTableRegistryVersion_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from nsi.{$proc}
			(
				RefTableRegistryVersion_id := :RefTableRegistryVersion_id,
				RefTableRegistry_id := :RefTableRegistry_id,
				RefTableRegistryVersion_Num := :RefTableRegistryVersion_Num,
				RefTableRegistryVersion_createDate := :RefTableRegistryVersion_createDate,
				RefTableRegistryVersion_publishDate := :RefTableRegistryVersion_publishDate,
				RefTableRegistryVersion_lastUpdateDate := :RefTableRegistryVersion_lastUpdateDate,
				pmUser_id := :pmUser_id
			)
		";

        return $this->queryResult($query, $data);
    }

    /**
     * Сохранение файла версии справочника в БД
     *
     * @param $data
     * @return array|false
     */
    function saveRefTableRegistryVersionFile($data)
    {
        // проверяем, есть ли такой файл у нас уже
        $resp = $this->queryResult("
			select
			    RefTableRegistryVersionFile_id as \"RefTableRegistryVersionFile_id\"
			from
				nsi.v_RefTableRegistryVersionFile
			where
				RefTableRegistryVersion_id = :RefTableRegistryVersion_id
			limit 1
		", [
            'RefTableRegistryVersion_id' => $data['RefTableRegistryVersion_id']
        ]);

        if (!empty($resp[0]['RefTableRegistryVersionFile_id'])) {
            $proc = 'p_RefTableRegistryVersionFile_upd';
            $data['RefTableRegistryVersionFile_id'] = $resp[0]['RefTableRegistryVersionFile_id'];
        } else {
            $proc = 'p_RefTableRegistryVersionFile_ins';
            $data['RefTableRegistryVersionFile_id'] = null;
        }

        $path_parts = pathinfo($data['RefTableRegistryVersionFile_Path']);
        $data['RefTableRegistryVersionFile_Name'] = $path_parts['filename'];
        $data['RefTableRegistryVersionFile_MethodName'] = null;

        $query = "
			select 
			    RefTableRegistryVersionFile_id as \"RefTableRegistryVersionFile_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from nsi.{$proc}
			(
				RefTableRegistryVersionFile_id := :RefTableRegistryVersionFile_id,
				RefTableRegistryVersion_id := :RefTableRegistryVersion_id,
				RefTableRegistryVersionFile_MethodName := :RefTableRegistryVersionFile_MethodName,
				RefTableRegistryVersionFile_Name := :RefTableRegistryVersionFile_Name,
				RefTableRegistryVersionFile_Path := :RefTableRegistryVersionFile_Path,
				pmUser_id := :pmUser_id
			)
		";

        return $this->queryResult($query, $data);
    }

    /**
     * Получение данных из справочника
     * @param $data
     * @throws Exception
     */
    public function getSprData($data)
    {
        file_put_contents($data['filePath'], '[');

        $page = 1;
        $size = 500;
        $total = 501;
        while ($total > $page * $size) {
            $resp_data = $this->exec('/data', [
                'identifier' => $data['oid'],
                'version' => $data['version'],
                'page' => $page,
                'size' => $size
            ]);

            if (isset($resp_data['result']) && $resp_data['result'] == 'OK') {
                $total = $resp_data['total'];
                if ($total >= 1000 && empty($data['RefTableRegistry_isRecUpd'])) {
                    $this->log->add(true, "В справочнике {$data['oid']} более 1000 элементов, помечаем его как не требующий получения обновлений");

                    // если в справочнике больше 1000 элементов, то помечаем его как не требующий получения обновлений
                    $this->db->query("
						update
							nsi.RefTableRegistry
						set
							RefTableRegistry_isRecUpd = 1
						where
						  	RefTableRegistry_id = :RefTableRegistry_id
					", [
                        'RefTableRegistry_id' => $data['RefTableRegistry_id']
                    ]);

                    break;
                }

                $str = json_encode($resp_data['list']);
                unset($resp_data['list']);
                if (mb_strlen($str) > 2) {
                    if ($page > 1) {
                        file_put_contents($data['filePath'], ',', FILE_APPEND);
                    }

                    $str = mb_substr($str, 1, mb_strlen($str) - 2); // убираем [ в начале
                    file_put_contents($data['filePath'], $str, FILE_APPEND);
                }
                unset($str);

                $page++;
            } else {
                throw new Exception('Ошибка получения данных справочника: ' . print_r($resp_data, true));
            }
        }

        file_put_contents($data['filePath'], ']', FILE_APPEND);
    }

    /**
     * Получение списка справочников
     * @param $data
     * @throws Exception
     */
    public function syncSprList($data)
    {
        $page = 1;
        $size = 500;
        $total = 501;
        while ($total > $page * $size) {
            // 1. Читаем список справочников
            $resp = $this->exec('/searchDictionary', [
                'publishDateFrom' => $data['publishDateFrom'],
                'publishDateTo' => date('Y-m-d') . ' 00:00',
                'page' => $page,
                'size' => $size
            ]);

            if (isset($resp['result']) && $resp['result'] == 'OK') {
                $total = $resp['total'];
                // получили список справочников
                foreach ($resp['list'] as $spr) {
                    // сохраняем справочник в БД
                    $resp_rtr = $this->saveRefTableRegistry([
                        'RefTableRegistry_Oid' => $spr['oid'],
                        'RefTableRegistry_Code' => $spr['identifier'],
                        'RefTableRegistry_createDT' => date('Y-m-d H:i:s', strtotime($spr['createDate'])),
                        'RefTableRegistry_publishDT' => date('Y-m-d H:i:s', strtotime($spr['publishDate'])),
                        'RefTableRegistry_IsArchive' => $spr['archive'] ? 2 : 1,
                        'RefTableRegistry_FullName' => $spr['fullName'],
                        'RefTableRegistry_Nick' => $spr['shortName'],
                        'RefTableRegistry_Group' => $spr['groupId'],
                        'pmUser_id' => $data['pmUser_id']
                    ]);

                    if (empty($resp_rtr[0]['RefTableRegistry_id'])) {
                        throw new Exception('Ошибка сохранения справочника: ' . print_r($resp_rtr, true));
                    }

                    if (!empty($resp_rtr[0]['RefTableRegistry_isRecUpd']) && $resp_rtr[0]['RefTableRegistry_isRecUpd'] == 1) {
                        // если не требует обновления, пропускаем
                        continue;
                    }

					//$this->log->add(true, "Получение справочника: {$spr['identifier']} (oid: {$spr['oid']})");

					$last_version = null;

                    // тянем список версий справочника
                    $resp_version = $this->exec('/versions', [
                        'identifier' => $spr['oid']
                    ]);

                    if (isset($resp_version['result']) && $resp_version['result'] == 'OK') {
                        foreach ($resp_version['list'] as $key => $version) {
                            // сохраняем версию в БД
                            $resp_rtrv = $this->saveRefTableRegistryVersion([
                                'RefTableRegistry_id' => $resp_rtr[0]['RefTableRegistry_id'],
                                'RefTableRegistryVersion_Num' => $version['version'],
                                'RefTableRegistryVersion_createDate' => date('Y-m-d H:i:s', strtotime($version['createDate'])),
                                'RefTableRegistryVersion_publishDate' => date('Y-m-d H:i:s', strtotime($version['publishDate'])),
                                'RefTableRegistryVersion_lastUpdateDate' => date('Y-m-d H:i:s', strtotime($version['lastUpdate'])),
                                'pmUser_id' => $data['pmUser_id']
                            ]);

                            if (empty($resp_rtrv[0]['RefTableRegistryVersion_id'])) {
                                throw new Exception('Ошибка сохранения версии справочника: ' . print_r($resp_rtrv, true));
                            }

                            if ($key === 0) {
                                // создаём папку

                                //запомним версию для записи в лог
                                $last_version = $version['version'];

                                $path = EXPORTPATH_ROOT . "nsi_egisz";
                                if (!file_exists($path)) {
                                    mkdir($path);
                                }
                                // создаём папку под справочник
                                $folder = $path . '/' . $spr['oid'];
                                if (!file_exists($folder)) {
                                    mkdir($folder);
                                }
                                $filePath = $folder . '/' . $version['version'] . '.json';

                                // тянем данные только для последней (актуальной) версии
                                $data['RefTableRegistry_id'] = $resp_rtr[0]['RefTableRegistry_id'];
                                $data['RefTableRegistry_isRecUpd'] = $resp_rtr[0]['RefTableRegistry_isRecUpd'];
                                $data['oid'] = $spr['oid'];
                                $data['version'] = $version['version'];
                                $data['filePath'] = $filePath;
                                $this->getSprData($data);

                                $resp_rtrvf = $this->saveRefTableRegistryVersionFile([
                                    'RefTableRegistryVersion_id' => $resp_rtrv[0]['RefTableRegistryVersion_id'],
                                    'RefTableRegistryVersionFile_Path' => $filePath,
                                    'pmUser_id' => $data['pmUser_id']
                                ]);

                                if (empty($resp_rtrvf[0]['RefTableRegistryVersionFile_id'])) {
                                    throw new Exception('Ошибка сохранения данных версии справочника: ' . print_r($resp_rtrvf, true));
                                }
                            }
                        }
                    } else {
                        throw new Exception('Ошибка получения списка версий справочника: ' . print_r($resp_version, true));
                    }

                    $this->log->add(true, "Получение справочника: {$spr['identifier']} (oid: {$spr['oid']}, version: {$last_version}, shortName: {$spr['shortName']})");
                }
                $page++;
            } else {
                throw new Exception('Ошибка получения списка справочников: ' . print_r($resp, true));
            }
        }
    }

    /**
     * Запуск импорта данных из НСИ ЕГИСЗ
     */
    public function syncAll($data)
    {
        set_time_limit(0);
        ini_set("max_execution_time", "0");

        $pmUser_id = !empty($data['pmUser_id']) ? $data['pmUser_id'] : 1;

        $this->log = new ServiceListLog($this->ServiceList_id, $pmUser_id);

        $resp = $this->log->start();
        if (!$this->isSuccessful($resp)) {
            return $resp;
        }

        $log = $this->log;
        $this->load->helper('ShutdownErrorHandler');
        registerShutdownErrorHandler([$this, 'shutdownErrorHandler'], function ($error) use ($log) {
            $log->add(false, ["Импорт данных из сервиса НСИ ЕГИСЗ завершён с ошибкой:", $error]);
            $log->finish(false);
        });

        try {
            set_error_handler([$this, 'exceptionErrorHandler']);

            $this->log->add(true, "Запуск импорта данных из сервиса НСИ ЕГИСЗ");
            $data['publishDateFrom'] = '1990-01-01 00:00';
            // если уже было успешное получение справочников, то дату начала берем из него
            $resp = $this->queryResult("
				select
					to_char(ServiceListLog_begDT, 'yyyy-mm-dd hh24:mi:ss') as \"ServiceListLog_begDT\"
				from
					stg.v_ServiceListLog
				where
					ServiceList_id = :ServiceList_id
                and
                    ServiceListResult_id = 1 -- успешно
				order by
					ServiceListLog_begDT desc
				limit 1
			", [
                'ServiceList_id' => $this->ServiceList_id
            ]);
            if (!empty($resp[0]['ServiceListLog_begDT'])) {
                $data['publishDateFrom'] = $resp[0]['ServiceListLog_begDT'];
            }
            $this->syncSprList($data);
            $this->log->add(true, "Импорт данных из сервиса НСИ ЕГИСЗ завершён успешно");
            $this->log->finish(true);
        } catch (Exception $e) {
            restore_exception_handler();

            $code = $e->getCode();
            $error = $e->getMessage();

            $this->log->add(false, ["Импорт данных из сервиса НСИ ЕГИСЗ завершён с ошибкой:", $error]);
            $this->log->finish(false);

            $response = $this->createError($code, $error);
            $response[0]['ServiceListLog_id'] = $this->log->getId();

            return $response;
        }

        return [['success' => true, 'ServiceListLog_id' => $this->log->getId()]];
    }

    /**
     * Скачивание файла справочника
     */
    function downloadRefTableRegistry($data)
    {
        // тянем файл с последней версией
        $where = '';
        if ( $data['RefTableName'] == 'RefTableRegistryVersion' ) { /*echo $record['RefTableRegistryVersion_Num']*/
            $where = 'rtrv.RefTableRegistryVersion_id = ' . $data['RefTableId'];
        } elseif ( $data['RefTableName'] == 'RefTableRegistry' ) {
            $where = 'rtrv.RefTableRegistry_id = ' . $data['RefTableId'];
        } elseif ( $data['RefTableName'] == 'RefTableRegistryVersionFile' ) {
            $where = 'rtrvf.RefTableRegistryVersionFile_id = ' . $data['RefTableId'];
        }

        $resp = $this->queryResult("
			select
				RefTableRegistryVersionFile_Path as \"RefTableRegistryVersionFile_Path\"
			from
				nsi.v_RefTableRegistryVersionFile rtrvf
				inner join nsi.v_RefTableRegistryVersion rtrv on rtrv.RefTableRegistryVersion_id = rtrvf.RefTableRegistryVersion_id 
			where
                {$where}
			order by
				rtrv.RefTableRegistryVersion_lastUpdateDate desc
		");

        if (!empty($resp[0]['RefTableRegistryVersionFile_Path']) && file_exists($resp[0]['RefTableRegistryVersionFile_Path'])) {
            $array = json_decode(file_get_contents($resp[0]['RefTableRegistryVersionFile_Path']), true);
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><items/>');
            foreach ($array as $one) {
                $row = $xml->addChild('item');
                foreach ($one as $subone) {
                    $row->addChild($subone['column'], $subone['value']);
                }
            }

            $filename = basename($resp[0]['RefTableRegistryVersionFile_Path']) . '.xml';

            header("Content-type: text/xml");
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            echo $xml->asXML();
        } else {
            die('Файл справочника не найден');
        }
    }
}