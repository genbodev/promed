<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * SystemMonitor_model - модель для работы мониторинга системы
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package            Admin
 * @access            public
 * @copyright        Copyright (c) 2014 Swan Ltd.
 * @author            Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version            04.04.2014
 *
 * @property        swMongodb swmongodb
 * @property        swMongoExt swmongoext
 */
class SystemMonitor_model extends SwPgModel
{
    /**
     * Конструктор
     */
    function __construct()
    {
        parent::__construct();

        switch (checkMongoDb()) {
            case 'mongo':
                $this->load->library('swMongodb', array(), 'swmongodb');
                break;
            case 'mongodb':
                $this->load->library('swMongodbPHP7', array(), 'swmongodb');
                break;
        }

        $this->load->library('swMongoExt');
        $this->load->helper('MongoDB');
    }

    /**
     * Сохранение запроса для логирования
     *
     * @param array $data
     * @return array
     */
    public function saveSystemMonitorQuery($data)
    {
        $object = 'systemmonitorquery';
        $id = $object . '_id';

        $params = [];
        foreach ($data as $field => $value) {
            $index = strtolower($field);
            $params[$index] = $value;
            if ($index == $id) {
                $uc_id = $field;
            }
        }
        array_walk($params, 'convertFieldToInt');
        array_walk($params, 'ConvertFromWin1251ToUTF8');

        if (empty($params[$id])) {
            $params[$id] = $this->swmongoext->generateCode($object);
            $this->swmongodb->insert($object, $params);
        } else {
            $this->swmongodb->wheres = array($id => $params[$id]);
            $this->swmongodb->update($object, $params);
        }

        return ['success' => true, $uc_id => $params[$id], 'Error_Msg' => ''];
    }

    /**
     * Сохранение строки лога
     *
     * @param array $data
     * @return array
     */
    public function saveSystemMonitorQueryLog($data)
    {
        $object = 'systemmonitorquerylog';
        $id = $object . '_id';
        $uc_id = '';

        $params = [
            'pmuser_id' => isset($data['pmUser_id']) ? $data['pmUser_id'] : Null,
            'systemmonitorquerylog_userip' => $_SERVER['REMOTE_ADDR'],
            'systemmonitorquerylog_serverip' => $_SERVER['SERVER_ADDR']
        ];
        foreach ($data as $field => $value) {
            $index = strtolower($field);
            $params[$index] = $value;
            if ($index == $id) {
                $uc_id = $field;
            }
        }
        array_walk($params, 'convertFieldToInt');
        array_walk($params, 'ConvertFromWin1251ToUTF8');

        if (empty($params[$id]) || $params[$id] < 0) {
            $params[$id] = $this->swmongoext->generateCode($object);
            $this->swmongodb->insert($object, $params);
        } else {
            $this->swmongodb->wheres = array($id => $params[$id]);
            $this->swmongodb->update($object, $params);
        }

        return ['success' => true, $uc_id => $params[$id], 'Error_Msg' => ''];
    }

    /**
     * Удаление запроса для логировния
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function deleteSystemMonitorQuery($data)
    {
        $object = 'systemmonitorquery';
        $id = $object . '_id';

        $params = [];
        foreach ($data as $field => $value) {
            $index = strtolower($field);
            $params[$index] = $value;
        }
        array_walk($params, 'convertFieldToInt');
        $this->swmongodb->wheres = array($id => $params[$id]);
        $res = $this->swmongodb->delete($object);

        if (!$res) {
            throw new Exception('Удаление не выполнено');
        }

        return array('success' => true, 'Error_Msg' => '');
    }

    /**
     * Возвращает список запросов для мониторинга
     * @param $data
     * @return array
     */
    function loadSystemMonitorQueryLogGrid()
    {
        $object = 'systemmonitorquerylog';

        $fields = [
            'SystemMonitorQueryLog_id',
            'SystemMonitorQueryLog_Num',
            'SystemMonitorQuery_id',
            'SystemMonitorQuery_Name',
            'SystemMonitorQueryLog_Date',
            'SystemMonitorQueryLog_Time',
            'SystemMonitorQueryLog_RunCount',
            'SystemMonitorQuery_TimeLimit',
            'SystemMonitorQueryLog_minRunTime',
            'SystemMonitorQueryLog_maxRunTime',
            'SystemMonitorQueryLog_avgRunTime',
            'SystemMonitorQueryLog_isError',
            'SystemMonitorQueryLog_serverIP'
        ];

       return $this->getResult($object, $fields);
    }

    /**
     * Возвращает список запросов для мониторинга
     *
     * @return array
     */
    public function loadSystemMonitorQueryList()
    {
        $object = 'systemmonitorquery';

        $fields = [
            'SystemMonitorQuery_id',
            'SystemMonitorQuery_Name',
            'SystemMonitorQuery_Query',
            'SystemMonitorQuery_RepeatCount',
            'SystemMonitorQuery_TimeLimit'
        ];

       return $this->getResult($object, $fields);
    }

    /**
     * Возврашает список мониторинга
     *
     * @param $object
     * @param $fields
     * @return array
     */
    protected function getResult($object, $fields)
    {
        $keys = [];
        for ($i = 0; $i < count($fields); $i++) {
            $field_name = strtolower($fields[$i]);
            $keys[$field_name] = $fields[$i];
        }

        $result = $this->swmongodb->get($object, $keys);

        return ['data' => $result];
    }

    /**
     * Получения данных запроса для редактирвания
     *
     * @param $data
     * @return array
     */
    public function loadSystemMonitorQueryForm($data)
    {
        $object = 'systemmonitorquery';
		$id = $object.'_id';

		$fields = [
			'SystemMonitorQuery_id',
			'SystemMonitorQuery_Name',
			'SystemMonitorQuery_Query',
			'SystemMonitorQuery_RepeatCount',
			'SystemMonitorQuery_TimeLimit'
		];
		$keys = [];
		for($i=0;$i<count($fields);$i++) {
			$field_name = strtolower($fields[$i]);
			$keys[$field_name] = $fields[$i];
		}

		$params = array();
		foreach($data as $field=>$value) {
			$index = strtolower($field);
			$params[$index] = $value;
		}
		array_walk($params,'convertFieldToInt');
		array_walk($params, 'ConvertFromWin1251ToUTF8');

		$this->swmongodb->wheres = [$id => (int)$params[$id]];
		$result = $this->swmongodb->get($object, $keys);

		return $result;
    }

    /**
     * Удаление лога из mongodb
     * @return array
     */
    public function clearSystemMonitorQueryLog()
    {
        $object = 'systemmonitorquerylog';

        $this->swmongodb->delete_all($object);

        return ['success' => true];
    }

    /**
     * Передача данных в php_log перед удалением лога из MongoDB
     *
     * @param $data
     * @return bool
     */
    public function dumpSystemMonitorQueryLog($data)
    {
        $params = [];
        $paramsAll = []; // собираем сюда запрос из 100 values
        $send = 1; // суммируется до 100 штук и отправляется
        $j = 1; // на 1 больше, чем $i
        $query = "
				
				INSERT INTO dbo.MonitorResult
				(
					MonitorResult_DT,
					MonitorResult_Query,
					MonitorResult_RunsCount,
					MonitorResult_DurationMin,
					MonitorResult_DurationMax,
					MonitorResult_DurationAvg,
					MonitorResult_Server
				)
				VALUES ";
        $queryIntit = $query; //Вызываем при стирании $query
        $result = false;

        for ($i = 0; $i < count($data['data']); $i++) {
            //Собираем дату и время в одну строку
            $SystemMonitorQueryLog_Date = $data['data'][$i]['SystemMonitorQueryLog_Date'];
            $SystemMonitorQueryLog_Time = $data['data'][$i]['SystemMonitorQueryLog_Time'];
            $DT = Datetime::createFromFormat("d.m.Y H:i", $SystemMonitorQueryLog_Date . " " . $SystemMonitorQueryLog_Time);

            $MonitorResult_DT = date_format($DT, "d.m.Y H:i");
            $MonitorResult_Query = $data['data'][$i]['SystemMonitorQueryLog_id'];
            $MonitorResult_RunsCount = $data['data'][$i]['SystemMonitorQueryLog_RunCount'];
            $MonitorResult_DurationMin = $data['data'][$i]['SystemMonitorQueryLog_minRunTime'];
            $MonitorResult_DurationMax = $data['data'][$i]['SystemMonitorQueryLog_maxRunTime'];
            $MonitorResult_DurationAvg = $data['data'][$i]['SystemMonitorQueryLog_avgRunTime'];
            $MonitorResult_Server = $data['data'][$i]['SystemMonitorQueryLog_serverIP'];

            $params[] = [
                "MonitorResult_DT_" . $i => $DT,
                "MonitorResult_Query_" . $i => $MonitorResult_Query,
                "MonitorResult_RunsCount_" . $i => $MonitorResult_RunsCount,
                "MonitorResult_DurationMin_" . $i => $MonitorResult_DurationMin,
                "MonitorResult_DurationMax_" . $i => $MonitorResult_DurationMax,
                "MonitorResult_DurationAvg_" . $i => $MonitorResult_DurationAvg,
                "MonitorResult_Server_" . $i => $MonitorResult_Server
            ];

            // объединяем порцию параметров из 100 массивов в один
            $paramsAll = array_merge($paramsAll, $params[$i]);

            $query .= " (
					:MonitorResult_DT_" . $i . ",
					:MonitorResult_Query_" . $i . ",
					:MonitorResult_RunsCount_" . $i . ",
					:MonitorResult_DurationMin_" . $i . ",
					:MonitorResult_DurationMax_" . $i . ",
					:MonitorResult_DurationAvg_" . $i . ",
					:MonitorResult_Server_" . $i . "
				),";

            //посылаем партиями по 100 штук
            if ($send >= 100) {

                $query = substr($query, 0, -1); //Убираем последнюю запятую
                $result = $query != "" ? $this->db->query($query, $paramsAll) : false;

                //стираем параметры и строку, чтобы записать следующие 100 элементов в порцию
                $query = $queryIntit;
                $paramsAll = [];
                $send = 0;
            } else if (count($data['data']) <= $j) {
                //отсылаем остатки
                $query = substr($query, 0, -1); //Убираем последнюю запятую
                $result = $query != "" ? $this->db->query($query, $paramsAll) : false;

            }

            //увеличиваем на 1 параметры, с которыми сравниваем
            ++$send;
            ++$j;

        }

        return $result;
    }

    /**
     * Логирвание работы сканера штрихкода в БД
     *
     * @param $data
     * @return array|bool
     */
    public function barcodeScannerLogging($data)
    {
        $query = "
			select
			    ScannerHistory_id as \"ScannerHistory_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from p_ScannerHistory_ins
			(
			    ScannerHistory_id := NULL,
				MedPersonal_id := :MedPersonal_id,
				ScannerHistory_PolisNum := :ScannerHistory_PolisNum,
				pmUser_id := :pmUser_id
			)
		";

        $resp = $this->queryResult($query, [
            'MedPersonal_id' => $data['MedPersonal_id'],
            'pmUser_id' => $data['pmUser_id'],
            'ScannerHistory_PolisNum' => (!empty($data['polisNum'])) ? $data['polisNum'] : null
        ]);

        if (empty($resp[0]['ScannerHistory_id']))
            return false;


        return [
            'ScannerHistory_id' => $resp[0]['ScannerHistory_id']
        ];
    }
}