<?php defined('BASEPATH') or die ('No direct script access allowed');

/* 
 * Модель для двига отчетов
 */

/**
 * @author yunitsky
 */
class ReportEngine_model extends CI_Model {
	private $_regionList = [
		'main' => ['code' => null, 'name' => 'root'],
		'Adygeya' => [ 'code' => '1', 'name' => 'Адыгея' ],
		'Ufa' => [ 'code' => '2', 'name' => 'Уфа' ],
		'Buryatiya' => [ 'code' => '3', 'name' => 'Бурятия' ],
		'Kar' => [ 'code' => '10', 'name' => 'Карелия' ],
		'Komi' => [ 'code' => '11', 'name' => 'Сыктывкар' ],
		'Mariel' => [ 'code' => '12', 'name' => 'Марий Эл' ],
		'Khak' => [ 'code' => '19', 'name' => 'Хакасия' ],
		'Krasnoyarsk' => [ 'code' => '24', 'name' => 'Красноярский край' ],
		'Stavropol' => [ 'code' => '26', 'name' => 'Ставропольский край' ],
		'Astra' => [ 'code' => '30', 'name' => 'Астрахань' ],
		'Vologda' => [ 'code' => '35', 'name' => 'Вологда' ],
		'Kaluga' => [ 'code' => '40', 'name' => 'Калуга' ],
		'Msk' => [ 'code' => '50', 'name' => 'Московская область' ],
		'Penza' => [ 'code' => '58', 'name' => 'Пенза' ],
		'Perm' => [ 'code' => '59', 'name' => 'Пермь' ],
		'Pskov' => [ 'code' => '60', 'name' => 'Псков' ],
		'Saratov' => [ 'code' => '64', 'name' => 'Саратов' ],
		'Ekat' => [ 'code' => '66', 'name' => 'Екатеринбург' ],
		'Yaroslavl' => [ 'code' => '76', 'name' => 'Ярославль' ],
		'Moscow' => [ 'code' => '77', 'name' => 'Москва' ],
		'Krym' => [ 'code' => '91', 'name' => 'Крым' ],
		'Kaz' => [ 'code' => '101', 'name' => 'Казахстан' ],
		'By' => [ 'code' => '201', 'name' => 'Беларусь' ],
	];

	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
	}

	/**
	 * Запускаем транзакцию
	 */
	function beginTransaction() {
		$this->db->trans_begin();
	}

	/**
	 * Завершаем транзакцию
	 */
	function commitTransaction() {
		$this->db->trans_commit();
	}

	/**
	 * Отменяем транзакцию
	 */
	function rollbackTransaction() {
		$this->db->trans_rollback();
	}

	/**
	 * @return string
	 */
	public static function getRegionAndDBType() {
		$region_name = '';
		$dbtype = '';

		if ( getenv('REGION') !== false ) {
			$region_name = getenv('REGION');
		}
		if ( getenv('DBTYPE') !== false ) {
			$dbtype = getenv('DBTYPE');
		}

		if ( getenv('USER_CAN_CHANGE_REGION') ) {
			// смотрим регион в сессии
			$startsession = false;
			if (!isset($_SESSION)) {
				$startsession = true;
			}
			if ($startsession) {
				session_start();
			}
			if (!empty($_SESSION['REGION_ENV'])) {
				$region_name = $_SESSION['REGION_ENV'];
			}
			if (!empty($_COOKIE['DBTYPE_ENV'])) {
				$dbtype = $_COOKIE['DBTYPE_ENV'];
			} else if (!empty($_SESSION['DBTYPE_ENV'])) {
				$dbtype = $_SESSION['DBTYPE_ENV'];
			}
			// закрываем если была закрыта.
			if ($startsession) {
				session_write_close();
			}
		}

		return [
			'region_name' => $region_name,
			'dbtype' => $dbtype
		];
	}

	/**
	 * @return array
	 */
	public static function getServers(){
		/*$region = self::getRegion();
		if ( !empty($region) && file_exists(APPPATH.'config/'.$region.'/database'.EXT) ) {
			require(APPPATH.'config/'.$region.'/database'.EXT);
		} else {
			require(APPPATH.'config/database'.EXT);
		}
		$servers = array($db['reports']);*/
		$servers = array(
			array(
				'hostname' => 'Сервер БД',
				'id' => 1,
			)
		);
		return $servers;
	}

	/**
	 * @return int
	 */
	public static function getServersCount(){
		return count(1);
	}

	/**
	 * @param $id
	 * @return mixed
	 */
	public static function getServer($id){
		$params = self::getRegionAndDBType();
		
		$configPath = 'config/';
		if (isset($params['dbtype']) && $params['dbtype'] == 'pgsql') {
			$configPath .= '_pgsql/';
		}
		
		if (!empty($params['region_name']) && file_exists(APPPATH . $configPath . $params['region_name'] . '/database' . EXT)) {
			require(APPPATH . $configPath . $params['region_name'] . '/database' . EXT);
		} else {
			require(APPPATH . $configPath . 'database' . EXT);
		}
		
		return (isset($db) ? $db['reports'] : []);
	}

	/**
	 * @return string
	 * @comment Прямой вызов функции библиотеки sqlsrv
	 */
	public static function getErrors() {
		$result = '';
		if( ($errors = sqlsrv_errors() ) != null) {
			foreach( $errors as $error) {
				$result .= "SQLSTATE: ".$error[ 'SQLSTATE']."\n";
				$result .= "code: ".$error[ 'code']."\n";
				$result .= "message: ".$error[ 'message']."\n";
			}
		}
		return $result;
	}

	/**
	 * @param $id
	 * @return false|resource
	 * @comment Прямой вызов функции библиотеки sqlsrv
	 * Коннект к серверу
	 */
	public static function getServerConnection($id){
		$server = self::getServer($id);
		if (!empty($server['username'])) {
			$connection = array(
				'UID' => $server['username'],
				'PWD' => $server['password'],
				'Database' => $server['database'],
				'ConnectionPooling' => 0,
				'LoginTimeout'=>30
			);
		} else {
			$connection = array(
				'Database' => $server['database'],
				'ConnectionPooling' => 0,
				'LoginTimeout'=>30
			);
		}
		return sqlsrv_connect($server['hostname'], $connection);
	}
	
	/**
	 * Возвращаем список серверов. И их статус.
	 */
	public function getServersList() {
		$result = array();
		foreach(self::getServers() as $server){
			$server['status'] = 'OK';
			$conn = self::getServerConnection($server['id']);
			if(!$conn) $server['status'] = self::getErrors();
			if(!empty($server['password']))unset($server['password']);
			$result[] = $server;
		}
		return $result;
	}

	/**
	 * @param $serverId
	 * @param $query
	 * @param null $params
	 * @param bool $identity
	 * @return bool|resource
	 * @comment Прямой вызов функции библиотеки sqlsrv
	 */
	static function query($serverId,$query,$params = null,$identity = false){
		$conn = self::getServerConnection($serverId);
		$result = sqlsrv_query($conn,$query,$params);
		sqlsrv_close($conn);
		return $result;
	}

	/**
	 * @param $serverId
	 * @param $sql
	 * @param $params
	 * @param bool $identity
	 * @return bool|resource
	 */
	public function command($sql,$params,$identity = false ){ // public static function
		if($identity) $sql.='; select @@IDENTITY as insertId';
		$res = $this->db->query($sql,$params);
		if($res) {
			if($identity) {
				$rsp = $res->result('array');
				if (!empty($rsp[0]['insertId'])) {
					$res = $rsp[0]['insertId'];
				} else {
					$res = $rsp;
				}
			}
		}
		return $res;
	}


	/**
	 * Формирование дерева сервера.
	 * Состоит из трех секций, каталога отчетов, каталога параметров и таблиц
	 * @param $params параметры запроса
	 *        $params['serverId'] - код сервера
	 *        $params['objectType'] - тип ноды
	 *        $params['node'] - ид ноды
	 */
	public function getServerTree($params){
		$conn = self::getServerConnection($params['serverId']);
		$objectType = $params['objectType'];
		$nodeId = $params['node'];
		$mode = (isset($params['mode']))?$params['mode']:false;
		switch ($objectType){
			// Корневые константные ноды
			case 'root' : return $this->getRootElements($conn,$mode);
			// Нода - Каталог регионов
			case 'catalogroot' : return $this->getRegionsFolders($conn,$nodeId,$mode);
			// Нода - Каталог отчетов, или папка отчетов
			case 'catalog' : return $this->getReportFolders($conn,$nodeId,$mode);
			// Нода - Каталог параметров, или папка параметров
			case 'params' : return $this->getParamFolders($conn,$nodeId);
			// Нода - Таблицы БД
			case 'tables' : return $this->getTables($conn);
			// Нода - отчет
			case 'report' : return $this->getReportContentForTree($conn, $nodeId);
			// Нода - филдсет
			case 'fieldset' : return $this->getFieldsetContent($conn, $nodeId);
		}
	}

	/**
	 * @param $conn
	 * @param $mode
	 * @return string
	 */
	protected function getRootElements($conn,$mode){
		$onlyCatalog = (!empty($mode) && $mode == 'onlyCatalog');
		$result = array(
			array('id'=>'catalogroot','text'=>'<b>Каталог отчетов</b>','objectType'=>'catalogroot','iconCls'=>'rpt-folder','viewClass'=>'ReportFolderView')
		);
		if(!$onlyCatalog){
			$result[] = array('id'=>'params','text'=>'<b>Каталог Параметров</b>','objectType'=>'params','iconCls'=>'rpt-folder','viewClass'=>'ParamFolderView');
			$result[] =array('id'=>'tables','text'=>'<b>Таблицы БД</b>','objectType'=>'tables','iconCls'=>'rpt-folder','viewClass'=>'EmptyContent');
		}
		$response = new JsonResponce($result);
		return $response->utf()->json();
	}

	/**
	 * @return string
	 */
	protected function getRegionsFolders($conn,$nodeId,$mode){
		$result = [];
		$isDebug = (int)$this->config->item('IS_DEBUG');
		
		foreach ( $this->_regionList as $nick => $region ) {
			$result[] = [
				'id' => $nick,
				'text' => '<b>' . $region['name'] . '</b>',
				'objectType' => 'catalog',
				'iconCls' => 'rpt-folder',
				'viewClass' => 'ReportFolderView'
			];
			if ($region['code'] == getRegionNumber() && $mode == 'onlyCatalog') { //#196244-2
				$tempNick = $nick;
				foreach ($result as $item) {
					if ($item['id'] == $tempNick) {
						$resultTemp[] = $item;
					}
				}
			}
			if ($isDebug && getRegionNick() == 'perm') {
				$resultTemp = [];
			}
			if (!empty($resultTemp)) {
				$result = [];
				$result = $resultTemp;
			}
		}
		$response = new JsonResponce($result);
		return $response->utf()->json();
	}
	/**
	 * @param $conn
	 * @return string
	 */
	protected function getTables($conn){
		$result = array(
			array('id'=>'Report','text'=>'Report','objectType'=>'tables','iconCls'=>'rpt-table','viewClass'=>'TableView'),
			array('id'=>'ReportCatalog','text'=>'ReportCatalog','objectType'=>'tables','iconCls'=>'rpt-table','viewClass'=>'TableView'),
			array('id'=>'ReportContent','text'=>'ReportContent','objectType'=>'tables','iconCls'=>'rpt-table','viewClass'=>'TableView'),
			array('id'=>'ReportContentParameter','text'=>'ReportContentParameter','objectType'=>'tables','iconCls'=>'rpt-table','viewClass'=>'TableView'),
			array('id'=>'ReportParameter','text'=>'ReportParameter','objectType'=>'tables','iconCls'=>'rpt-table','viewClass'=>'TableView'),
			array('id'=>'ReportParameterCatalog','text'=>'ReportParameterCatalog','objectType'=>'tables','iconCls'=>'rpt-table','viewClass'=>'TableView')
		);
		$response = new JsonResponce();
		return $response->toTree($result,'','id','text',array('leaf'=>true))->json();
	}

	const REPORTS_FOLDERS_SQL = "
		select ReportCatalog.ReportCatalog_id,
		   (
				ReportCatalog.ReportCatalog_Name + ' (' +
				case when ReportCatalog.Region_id is not null then dbo.GetRegionNamebyCode(ReportCatalog.Region_id) else 'root'	end
				+ ')'
			) as ReportCatalog_Name,
		   ReportCatalog.ReportCatalog_Status,
		   ReportCatalog.ReportCatalog_Path,
		   ReportCatalog.ReportCatalog_Position,
		   ReportCatalog.pmUser_insID,
		   ReportCatalog.pmUser_updID,
		   ReportCatalog.ReportCatalog_insDT,
		   ReportCatalog.ReportCatalog_updDT,
		   ReportCatalog.Region_id
		from rpt.ReportCatalog ReportCatalog (nolock)
	";

	const REPORTS_SQL = 'select Report.Report_id, Report.ReportCatalog_id, Report.Report_Caption, Report.Report_Description, Report.Report_Title, Report.Report_FileName, Report.Report_Status, Report.Report_Position, Report.pmUser_insID, Report.pmUser_updID, Report.Report_insDT, Report.Report_updDT, Report.Region_id, Report.ReportType_id, Report.DatabaseType from rpt.Report Report (nolock) where ReportCatalog_id = ? order by Report_Position';

	/**
	 * @param $conn
	 * @param $nodeId
	 * @param $mode
	 * @return string
	 * Получение каталогов и отчетов
	 */
	protected function getReportFolders($conn,$nodeId,$mode){
		$response = new JsonResponce();
		$temp = array();
		$onlyCatalog = (!empty($mode) && $mode == 'onlyCatalog');

		// Корневая нода
		/*if($nodeId == 'catalog'){
			$where = ' where ReportCatalog_pid is null';
		} */
		if ( isset($this->_regionList[$nodeId]) && $this->_regionList[$nodeId]['code'] != null ) {
			$where = " where ReportCatalog_pid is null and Region_id = " . $this->_regionList[$nodeId]['code'];
		}
		else if ( isset($this->_regionList[$nodeId]) ) {
			$where = " where ReportCatalog_pid is null and Region_id is null";
		} else {
			$nodeId = preg_replace('/[^\d]*/','',$nodeId);
			$where = ' where ReportCatalog_pid = '.$nodeId;
		}
		// Забираем папки
		//$result = @sqlsrv_query($conn,self::REPORTS_FOLDERS_SQL.$where.' order by ReportCatalog_Position');
		$result = @$this->db->query(self::REPORTS_FOLDERS_SQL.$where.' order by ReportCatalog_Position');
		if(!$result) return $response->error(self::getErrors())->json();
		//while($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) $temp[] = $row;
		$temp = $result->result('array');
		$response->toTree($temp,'#rc','ReportCatalog_id','ReportCatalog_Name',array('objectType'=>'catalog','viewClass'=>'ReportFolderView','iconCls'=>'rpt-folder'));

		// Забираем отчеты в папке
		if ( !$onlyCatalog && !isset($this->_regionList[$nodeId]) ) {
			$temp = array();
			//$result = @sqlsrv_query($conn,self::REPORTS_SQL,array($nodeId));
			$result = $this->db->query(self::REPORTS_SQL,array($nodeId));
			if(!$result) return $response->error(self::getErrors())->json();
			$rsp = $result->result('array');
			//while($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) { 
			foreach ( $rsp as $row ) {
				// Проверка доступа
				if( isSuperAdmin() || $this->isAccessToReport($row) ) {
					$temp[] = $row;
				}
			}
			$response->toTree($temp,'#rr','Report_id','Report_Caption',
					array('objectType'=>'report','viewClass'=>'ReportView','iconCls'=>'rpt-report'));
		}
		return $response->utf()->json();
	}

	/**
	 * @param $reportData
	 * @return bool
	 * Проверка доступа на отчет
	 */
	private function isAccessToReport($reportData) {
		$arms_rep = $this->getAvailableARMs(array('Report_id' => $reportData['Report_id']));
		if( !isset($_SESSION['ARMList']) ) return true;
		$i=0;
		while( isset($arms_rep[$i]) && !in_array($arms_rep[$i]['ARMType_SysNick'], $_SESSION['ARMList']) ) $i++;
		return $i < count($arms_rep);
	}

	/**
	 * @param $data
	 * @return bool
	 * Получение доступных АРМов
	 */
	protected function getAvailableARMs($data) {
		$query = "
			select distinct
				AT.ARMType_Code,
				AT.ARMType_SysNick
			from
				v_ARMType AT with(nolock)
				left join rpt.v_ReportARM RA with(nolock) on RA.ARMType_id = AT.ARMType_id
			where
				RA.Report_id = :Report_id
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	const PARAM_FOLDERS_SQL = '
		select
			ReportParameterCatalog_id as "ReportParameterCatalog_id",
			ReportParameterCatalog_pid as "ReportParameterCatalog_pid",
			ReportParameterCatalog_Name as "ReportParameterCatalog_Name",
			pmUser_insID as "pmUser_insID",
			pmUser_updID as "pmUser_updID",
			ReportParameterCatalog_insDT as "ReportParameterCatalog_insDT",
			ReportParameterCatalog_updDT as "ReportParameterCatalog_updDT"
		from rpt.ReportParameterCatalog ReportParameterCatalog (nolock)
	';
	const PARAM_SQL = '
		select
			ReportParameter_id as "ReportParameter_id",
        	ReportParameter_SQL_XTemplate as "ReportParameter_SQL_XTemplate",
        	pmUser_insID as "pmUser_insID",
        	pmUser_updID as "pmUser_updID",
        	ReportParameter_insDT as "ReportParameter_insDT",
        	ReportParameter_updDT as "ReportParameter_updDT",
        	Region_id as "Region_id",
        	ReportParameterCatalog_id as "ReportParameterCatalog_id",
        	ReportParameter_Name as "ReportParameter_Name",
        	ReportParameter_Type as "ReportParameter_Type",
        	ReportParameter_Label as "ReportParameter_Label",
        	ReportParameter_Mask as "ReportParameter_Mask",
        	ReportParameter_Length as "ReportParameter_Length",
        	ReportParameter_MaxLength as "ReportParameter_MaxLength",
        	ReportParameter_Default as "ReportParameter_Default",
        	ReportParameter_Align as "ReportParameter_Align",
        	ReportParameter_CustomStyle as "ReportParameter_CustomStyle",
        	ReportParameter_SQL as "ReportParameter_SQL",
        	ReportParameter_SQL_IdField as "ReportParameter_SQL_IdField",
        	ReportParameter_SQL_TextField as "ReportParameter_SQL_TextField"
		from rpt.ReportParameter ReportParameter (nolock)
		where ReportParameterCatalog_id = ?
		order by ReportParameter_Name
	';

	/**
	 * @param $conn
	 * @param $nodeId
	 * @return string
	 */
	protected function getParamFolders($conn,$nodeId){
		$response = new JsonResponce();
		$temp = array();
		// Корневая нода
		if($nodeId == 'params'){
			$where = ' where ReportParameterCatalog_pid is null';
		} else {
			$nodeId = preg_replace('/[^\d]*/','',$nodeId);
			$where = ' where ReportParameterCatalog_pid = '.$nodeId;
		}
		// Забираем папки
		//$result = @sqlsrv_query($conn,self::PARAM_FOLDERS_SQL.$where.' order by ReportParameterCatalog_Name');
		$result = @$this->db->query(self::PARAM_FOLDERS_SQL.$where.' order by ReportParameterCatalog_Name');
		if(!$result) return $response->error(self::getErrors())->json();
		//while($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) $temp[] = $row;
		$temp = $result->result('array');
		$response->toTree($temp,'#pc','ReportParameterCatalog_id','ReportParameterCatalog_Name',
				array('objectType'=>'params','viewClass'=>'ParamFolderView','iconCls'=>'rpt-folder'));
		// Забираем отчеты в папке
		if($nodeId != 'params'){
			$temp = array();
			//$result = @sqlsrv_query($conn,self::PARAM_SQL,array($nodeId));
			$result = @$this->db->query(self::PARAM_SQL,array($nodeId));
			if(!$result) return $response->error(self::getErrors())->json();
			//while($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) $temp[] = $row;
			$temp = $result->result('array');
			$response->toTree($temp,'#rp','ReportParameter_id','ReportParameter_Name',
					array('objectType'=>'param','iconCls'=>'rpt-param','viewClass'=>'EmptyContent','leaf'=>'true'));
		}
		return $response->utf()->json();
	}

	const REPORT_FIELDSET_SQL = '
		select
			ReportContent_id as "ReportContent_id",
			Report_id as "Report_id",
			ReportContent_Name as "ReportContent_Name",
			ReportContent_Position as "ReportContent_Position",
			pmUser_insID as "pmUser_insID",
			pmUser_updID as "pmUser_updID",
			ReportContent_insDT as "ReportContent_insDT",
			ReportContent_updDT as "ReportContent_updDT"
		from rpt.ReportContent ReportContent (nolock)
		where ReportContent_Name is not null
		  and Report_id = ?
		order by ReportContent_Position';
	const REPORT_PARAM_SQL = '
		select
			a.ReportContentParameter_id as "ReportContentParameter_id",
			a.ReportParameter_id as "ReportParameter_id",
			a.ReportContent_id as "ReportContent_id",
			a.ReportContentParameter_Default as "ReportContentParameter_Default",
			a.ReportContentParameter_Required as "ReportContentParameter_Required",
			a.ReportContentParameter_ReportId as "ReportContentParameter_ReportId",
			a.ReportContentParameter_Position as "ReportContentParameter_Position",
			a.ReportContentParameter_PrefixId as "ReportContentParameter_PrefixId",
			a.ReportContentParameter_PrefixText as "ReportContentParameter_PrefixText",
			a.ReportContentParameter_ReportLabel as "ReportContentParameter_ReportLabel",
			a.pmUser_insID as "pmUser_insID",
			a.pmUser_updID as "pmUser_updID",
			a.ReportContentParameter_insDT as "ReportContentParameter_insDT",
			a.ReportContentParameter_updDT as "ReportContentParameter_updDT",
		    a.ReportContentParameter_Position as "position",
			isnull(a.ReportContentParameter_ReportLabel,c.ReportParameter_Label) +
			\' - <span class="x-grid-gray-text">\' + isnull(a.ReportContentParameter_ReportId,c.ReportParameter_Name) +
			\'</span>\' as "Name"
		from
			rpt.ReportContentParameter a (nolock)
			join rpt.v_ReportContent b (nolock) on a.ReportContent_id = b.ReportContent_id
			join rpt.v_ReportParameter c (nolock) on c.ReportParameter_id = a.ReportParameter_id
		where b.ReportContent_Name is null and b.Report_id = ?
		order by position
	';

	/**
	 * @param $conn
	 * @param $nodeId
	 * @return string
	 */
	protected function getReportContentForTree($conn,$nodeId){
		$response = new JsonResponce();
		$nodeId = preg_replace('/[^\d]*/','',$nodeId);
		$temp = array();
		// Забираем филдсеты
		//$result = @sqlsrv_query($conn,self::REPORT_FIELDSET_SQL,array($nodeId));
		$result = @$this->db->query(self::REPORT_FIELDSET_SQL,array($nodeId));
		if(!$result) return $response->error(self::getErrors())->json();
		//while($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) $temp[] = $row;
		$temp = $result->result('array');
		$response->toTree($temp,'#cf','ReportContent_id','ReportContent_Name',
				array('objectType'=>'fieldset','viewClass'=>'FieldsetView','iconCls'=>'rpt-reports'));
		// Забираем параметры для отчета
		$temp = array();
		//$result = @sqlsrv_query($conn,self::REPORT_PARAM_SQL,array($nodeId));
		$result = @$this->db->query(self::REPORT_PARAM_SQL,array($nodeId));
		if(!$result) return $response->error(self::getErrors())->json();
		//while($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) $temp[] = $row;
		$temp = $result->result('array');
		$response->toTree($temp,'#cp','ReportContentParameter_id','Name',
				array('objectType'=>'param','iconCls'=>'rpt-param','viewClass'=>'EmptyContent','leaf'=>'true'));
		return $response->utf()->json();
	}

	const FIELDSET_SQL = '
		select
			a.ReportContentParameter_id as "ReportContentParameter_id",
			a.ReportParameter_id as "ReportParameter_id",
			a.ReportContent_id as "ReportContent_id",
			a.ReportContentParameter_Default as "ReportContentParameter_Default",
			a.ReportContentParameter_Required as "ReportContentParameter_Required",
			a.ReportContentParameter_ReportId as "ReportContentParameter_ReportId",
			a.ReportContentParameter_Position as "ReportContentParameter_Position",
			a.ReportContentParameter_PrefixId as "ReportContentParameter_PrefixId",
			a.ReportContentParameter_PrefixText as "ReportContentParameter_PrefixText",
			a.ReportContentParameter_ReportLabel as "ReportContentParameter_ReportLabel",
			a.pmUser_insID as "pmUser_insID",
			a.pmUser_updID as "pmUser_updID",
			a.ReportContentParameter_insDT as "ReportContentParameter_insDT",
			a.ReportContentParameter_updDT as "ReportContentParameter_updDT",
			b.ReportContent_Position as "position",
			isnull(a.ReportContentParameter_ReportLabel,c.ReportParameter_Label) +
			\' - <span class="x-grid-gray-text">\' + c.ReportParameter_Name +
			\'</span>\' as Name
		from
			rpt.ReportContentParameter a (nolock)
			join rpt.v_ReportContent b (nolock) on a.ReportContent_id = b.ReportContent_id
			join rpt.v_ReportParameter c (nolock) on c.ReportParameter_id = a.ReportParameter_id
		where b.ReportContent_Name is not null and b.ReportContent_id = ?
		order by position
	';

	/**
	 * @param $conn
	 * @param $nodeId
	 * @return string
	 */
	protected function getFieldsetContent($conn,$nodeId){
		$response = new JsonResponce();
		$nodeId = preg_replace('/[^\d]*/','',$nodeId);
		// Забираем параметры для отчета
		$temp = array();
		//$result = @sqlsrv_query($conn,self::FIELDSET_SQL,array($nodeId));
		$result = @$this->db->query(self::FIELDSET_SQL,array($nodeId));
		if(!$result) return $response->error(self::getErrors())->json();
		//while($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) $temp[] = $row;
		$temp = $result->result('array');
		$response->toTree($temp,'#fp','ReportContentParameter_id','Name',
				array('objectType'=>'param','iconCls'=>'rpt-param','viewClass'=>'EmptyContent','leaf'=>'true'));
		return $response->utf()->json();
	}

	/**
	 * @param $params
	 * @param null $result
	 * @return mixed
	 * Получение дерева
	 */
	public function getAllTree($params,$result = null){
		//static $ret = array();
		$tree = $this->getServerTree($params);
		$result = json_decode($tree);
		foreach($result as $obj){
			if(isset($obj->leaf)) continue;
			$params['objectType'] = $obj->objectType;
			$params['node'] = $obj->id;
			$obj->children = $this->getAllTree($params);
		}
		return $result;
	}

	/**
	 * Функционал в нодах "Таблицы БД"
	 */
	const REPORT_SQL = 'select Report.Report_id, Report.ReportCatalog_id, Report.Report_Caption, Report.Report_Description, Report.Report_Title, Report.Report_FileName, Report.Report_Status, Report.Report_Position, Report.pmUser_insID, Report.pmUser_updID, Report.Report_insDT, Report.Report_updDT, Report.Region_id, Report.ReportType_id, Report.DatabaseType from rpt.Report Report (nolock) ';
	//const REPORTCATALOG_SQL = 'select * from rpt.v_ReportCatalog ReportCatalog (nolock) ';
	const REPORTCATALOG_SQL = "
		select
			ReportCatalog.ReportCatalog_id,
			ReportCatalog.ReportCatalog_Name,
			ReportCatalog.ReportCatalog_Status,
			ReportCatalog.ReportCatalog_Path,
			ReportCatalog.ReportCatalog_Position,
			ReportCatalog.pmUser_insID,
			ReportCatalog.pmUser_updID,
			ReportCatalog.ReportCatalog_insDT,
			ReportCatalog.ReportCatalog_updDT,
			ReportCatalog.Region_id
		from rpt.ReportCatalog ReportCatalog (nolock)
	";

	const REPORTCONTENT_SQL = '
		select
			ReportContent_id as "ReportContent_id",
			Report_id as "Report_id",
			ReportContent_Name as "ReportContent_Name",
			ReportContent_Position as "ReportContent_Position",
			pmUser_insID as "pmUser_insID",
			pmUser_updID as "pmUser_updID",
			ReportContent_insDT as "ReportContent_insDT",
			ReportContent_updDT as "ReportContent_updDT"
		from rpt.ReportContent ReportContent (nolock)
	';
	const REPORTCONTENTFIELDSET_SQL = '
		select
			ReportContentParameter_id as "ReportContentParameter_id",
			ReportParameter_id as "ReportParameter_id",
			ReportContent_id as "ReportContent_id",
			ReportContentParameter_Default as "ReportContentParameter_Default",
			ReportContentParameter_Required as "ReportContentParameter_Required",
			ReportContentParameter_ReportId as "ReportContentParameter_ReportId",
			ReportContentParameter_Position as "ReportContentParameter_Position",
			ReportContentParameter_PrefixId as "ReportContentParameter_PrefixId",
			ReportContentParameter_PrefixText as "ReportContentParameter_PrefixText",
			ReportContentParameter_ReportLabel as "ReportContentParameter_ReportLabel",
			pmUser_insID as "pmUser_insID",
			pmUser_updID as "pmUser_updID",
			ReportContentParameter_insDT as "ReportContentParameter_insDT",
			ReportContentParameter_updDT as "ReportContentParameter_updDT"
		from rpt.ReportContentFieldset ReportContentFieldset (nolock)
	';
	const REPORTCONTENTPARAMETER_SQL = '
		select
			ReportContentParameter_id as "ReportContentParameter_id",
			ReportParameter_id as "ReportParameter_id",
			ReportContent_id as "ReportContent_id",
			ReportContentParameter_Default as "ReportContentParameter_Default",
			ReportContentParameter_Required as "ReportContentParameter_Required",
			ReportContentParameter_ReportId as "ReportContentParameter_ReportId",
			ReportContentParameter_Position as "ReportContentParameter_Position",
			ReportContentParameter_PrefixId as "ReportContentParameter_PrefixId",
			ReportContentParameter_PrefixText as "ReportContentParameter_PrefixText",
			ReportContentParameter_ReportLabel as "ReportContentParameter_ReportLabel",
			pmUser_insID as "pmUser_insID",
			pmUser_updID as "pmUser_updID",
			ReportContentParameter_insDT as "ReportContentParameter_insDT",
			ReportContentParameter_updDT as "ReportContentParameter_updDT",
			ReportContentParameter_SQL as "ReportContentParameter_SQL",
			ReportContentParameter_SQLIdField as "ReportContentParameter_SQLIdField",
			ReportContentParameter_SQLTextField as "ReportContentParameter_SQLTextField"
		from rpt.ReportContentParameter ReportContentParameter (nolock)
	';
	const REPORTPARAMETER_SQL = "
		select
			ReportParameter.ReportParameter_id,
			ReportParameter.ReportParameterCatalog_id,
			ReportParameter.ReportParameter_Name,
			ReportParameter.ReportParameter_Type,
			ReportParameter.ReportParameter_Label,
			case 
				when ReportParameter.Region_id is not null then dbo.GetRegionNamebyCode(ReportParameter.Region_id) 
				else 'root' 
			end as ReportParameter_RegionName,
			ReportParameter.ReportParameter_Mask,
			ReportParameter.ReportParameter_Length,
			ReportParameter.ReportParameter_MaxLength,
			ReportParameter.ReportParameter_Default,
			ReportParameter.ReportParameter_Align,
			ReportParameter.ReportParameter_CustomStyle,
			ReportParameter.ReportParameter_SQL,
			ReportParameter.ReportParameter_SQL_IdField,
			ReportParameter.ReportParameter_SQL_TextField,
			ReportParameter.ReportParameter_SQL_XTemplate,
			ReportParameter.pmUser_insID,
			ReportParameter.pmUser_updID,
			ReportParameter.ReportParameter_insDT,
			ReportParameter.ReportParameter_updDT,
			ReportParameter.Region_id
		from rpt.ReportParameter with(nolock)
	";
	const REPORTPARAMETERCATALOG_SQL = '
		select
			ReportParameterCatalog_id as "ReportParameterCatalog_id",
			ReportParameterCatalog_pid as "ReportParameterCatalog_pid",
			ReportParameterCatalog_Name as "ReportParameterCatalog_Name",
			pmUser_insID as "pmUser_insID",
			pmUser_updID as "pmUser_updID",
			ReportParameterCatalog_insDT as "ReportParameterCatalog_insDT",
			ReportParameterCatalog_updDT as "ReportParameterCatalog_updDT"
		from rpt.ReportParameterCatalog ReportParameterCatalog (nolock)
	';

	/**
	 * @param $result
	 * @return string
	 */
	public function toResponce($result, $id = null, $Object_name = null){
		$response = new JsonResponce();
		if(!$result) return $response->error(self::getErrors())->json();
		$temp = array();
		//echo memory_get_usage(), '<br />';
		//$rsp = $result->result('array');
		//echo memory_get_usage(), '<br />';
		//foreach ( $rsp as $key => $row ) {
		while($row = $result->_fetch_assoc()){
			foreach($row as $key=>$value) {
				if(is_string($value)) $row[$key] = trim($value);
			}
			$include = true;
			if ($id == 'paramLpu' && !empty($_SESSION['TOUZLpuArr'])) {
				if (!in_array($row['Lpu_id'], $_SESSION['TOUZLpuArr'])) {
					$include = false;
				}
			}
			if ($include) {
				$temp[] = $row;
			}
			//unset($rsp[$key]);
			//echo memory_get_usage(), '<br />';
		}
		if (!empty($Object_name)) {
			$temp = $this->applyRegion_ids($Object_name, $temp);
			if( !empty($temp['Error_Msg']) ) {
				return $response->error($temp['Error_Msg'])->utf()->json();
			}
		}
		return $response->toStore($temp)->success(true)->utf()->json();
	}

	/**
	 * @param $field
	 * @param $params
	 * @return string
	 */
	protected function getWhere($field,$params){
		if(isset($params['ownerId'])){
			if ( isset($this->_regionList[$params['ownerId']]) && $this->_regionList[$params['ownerId']]['code'] != null ) {
				return " where Region_id = " . $this->_regionList[$params['ownerId']]['code'] . " and " . $field . " is null";
			} else if ( isset($this->_regionList[$params['ownerId']]) ) {
				return " where Region_id is null and " . $field . " is null";
			} else {

				return $params['ownerId'] == 'all' ? '' : ' where ' . $field . ' = ' . $params['ownerId'];
			}
		}
		else {
			return ' where ' . $field . ' is null';
		}
	}

	/**
	 * @param $name название объекта
	 * @param $Object_arr массив с данными
	 * @return array|false
	 * Добавляет в ответ массив выбранных регионов
	 */
	protected function applyRegion_ids($name, $Object_arr) {
		if (empty($Object_arr)) { return $Object_arr; } // если массив пустой, ничего не делаем

		if (!isset($Object_arr[0][$name . '_id'])) {// проверяем наличие id соответствующего объекта в переданном массиве
			return array(
				'Error_Msg' => 'Не удалось получить список выбранных регионов(в массиве отсутсвует идентификатор объекта)'
			);
		}

		$Object_ids = array();

		// Получаем идентификаторы объектов
		foreach ($Object_arr as $key => $value) {
			$Object_ids[] = $value[$name . '_id'];
		}

		$strObject_ids = implode(', ', $Object_ids);
		$query = "
			select 
				Region_id,
				{$name}_id
			from
				rpt.ReportList with (nolock)
			where
				{$name}_id in ({$strObject_ids})
		";
		$response = $this->db->query($query);
		if (is_object($response)) {
			$result = $response->result('array');
		} else {
			return false;
		}

		//преобразуем полученный массив к формату array('<Object_id>' => array(<Region_id>,...))
		$Region_ids = array();
		foreach ($result as $key => $value) {
			// создаём подмассив для id объекта текущей записи(если его ещё нет)
			if ( !isset($Region_ids[$value[$name . '_id']]) ) {
				$Region_ids[$value[$name . '_id']] = array();
			}
			//Добавляем id выбранного региона к соответствующему подмассиву
			$Region_ids[$value[$name . '_id']] [] = $value['Region_id'];
		}

		// прикрепляем данные о выбранных регионах к ответу
		foreach ($Object_arr as $key => $value) {
			if ( isset($Region_ids[$value[$name . '_id']]) ) {
				$Object_arr[$key]['Region_ids'] = json_encode($Region_ids[$value[$name . '_id']]);
			}
			
		}

		return $Object_arr;
	}

	/**
	 * @param $params
	 * @return false|resource
	 * Получение коннекта
	 */
	public function getConnection($params){
		//$serverId = $params['serverId'];
		//return self::getServerConnection($serverId);
		return self::getServerConnection(1);
	}

	/**
	 * @param $params
	 * @return string
	 * Получение отчетов
	 */
	public function getReport($params){
		//$conn = $this->getConnection($params);
		//$result = @sqlsrv_query($conn,self::REPORT_SQL.$this->getWhere('ReportCatalog_id',$params));
		if (!empty($params['ReportContent_id'])) {
			$query = "
				select top 1
					R.Report_id as \"Report_id\",
					R.ReportCatalog_id as \"ReportCatalog_id\",
					R.Report_Caption as \"Report_Caption\",
					R.Report_Description as \"Report_Description\",
					R.Report_Title as \"Report_Title\",
					R.Report_FileName as \"Report_FileName\",
					R.Report_Status as \"Report_Status\",
					R.Report_Position as \"Report_Position\",
					R.Region_id as \"Region_id\",
					R.ReportType_id as \"ReportType_id\"
				from 
					rpt.ReportContent RC with (nolock)
					inner join rpt.Report R with (nolock) on R.Report_id = RC.Report_id
				where
					RC.ReportContent_id = :ReportContent_id
			";
			$queryParams = array(
				'ReportContent_id' => $params['ReportContent_id']
			);

			$result = $this->db->query($query, $queryParams);
			if (is_object($result)) {
				$resp = $result->result('array');
				if (is_array($resp)) {
					return $resp;
				}
			}
		} else {
			$result = @$this->db->query(self::REPORT_SQL.$this->getWhere('ReportCatalog_id',$params)." order by Report_Position");
		}
		
		return $this->toResponce($result, null, 'Report');
	}

	/**
	 * @param $params
	 * @return string
	 * Получение каталогов
	 */
	public function getReportCatalog($params){
		//$conn = $this->getConnection($params);
		//$result = @sqlsrv_query($conn,self::REPORTCATALOG_SQL.$this->getWhere('ReportCatalog_pid',$params));
		$result = @$this->db->query(self::REPORTCATALOG_SQL.$this->getWhere('ReportCatalog_pid',$params));
		return $this->toResponce($result, null, 'ReportCatalog');
	}

	/**
	 * @param $params
	 * @return string
	 * Получение содержимого отчета
	 */
	public function getReportContent($params){
		//$conn = $this->getConnection($params);
		if($params['ownerId'] && $params['ownerId'] != 'all'){
			$sql = '
				select
					ReportContent_id as "ReportContent_id",
					Report_id as "Report_id",
					ReportContent_Name as "ReportContent_Name",
					ReportContent_Position as "ReportContent_Position",
					pmUser_insID as "pmUser_insID",
					pmUser_updID as "pmUser_updID",
					ReportContent_insDT as "ReportContent_insDT",
					ReportContent_updDT as "ReportContent_updDT"
				from rpt.v_ReportContent ReportContent (nolock) 
				where ReportContent_Name is not null
				  and Report_id = ?
				order by ReportContent_Position
			';
			//$result = @sqlsrv_query($conn,$sql,array($params['ownerId']));
			$result = @$this->db->query($sql,array($params['ownerId']));
		} else {
			$where = isset($params['reportId']) ? ' where Report_id = '.$params['reportId'] : '';
			//$result = @sqlsrv_query($conn,self::REPORTCONTENT_SQL.$where);
			$result = @$this->db->query(self::REPORTCONTENT_SQL.$where);
		}
		return $this->toResponce($result);
	}

	/**
	 * @param $params
	 * @return string
	 */
	public function getReportContentFieldset($params){
		//$conn = $this->getConnection($params);
		//$result = @sqlsrv_query($conn,self::REPORTCONTENTFIELDSET_SQL);
		$result = @$this->db->query(self::REPORTCONTENTFIELDSET_SQL);
		return $this->toResponce($result);
	}

	/**
	 * @param $params
	 * @return string
	 */
	public function getReportContentParameter($params){
		//$conn = $this->getConnection($params);
		if($params['ownerId'] && $params['ownerId'] != 'all'){
			if($_REQUEST['isFieldset']){
				$sql = 'select
					a.ReportContentParameter_id as "ReportContentParameter_id",
					a.ReportParameter_id as "ReportParameter_id",
					a.ReportContent_id as "ReportContent_id",
					a.ReportContentParameter_Default as "ReportContentParameter_Default",
					a.ReportContentParameter_Required as "ReportContentParameter_Required",
					a.ReportContentParameter_ReportId as "ReportContentParameter_ReportId",
					a.ReportContentParameter_Position as "ReportContentParameter_Position",
					a.ReportContentParameter_PrefixId as "ReportContentParameter_PrefixId",
					a.ReportContentParameter_PrefixText as "ReportContentParameter_PrefixText",
					a.ReportContentParameter_ReportLabel as "ReportContentParameter_ReportLabel",
					a.pmUser_insID as "pmUser_insID",
					a.pmUser_updID as "pmUser_updID",
					a.ReportContentParameter_insDT as "ReportContentParameter_insDT",
					a.ReportContentParameter_updDT as "ReportContentParameter_updDT",
					a.ReportContentParameter_Position as "position",
					a.ReportContentParameter_SQL as "ReportContentParameter_SQL",
					a.ReportContentParameter_SQLIdField as "ReportContentParameter_SQLIdField",
					a.ReportContentParameter_SQLTextField as "ReportContentParameter_SQLTextField",
					isnull(cast(a.ReportContentParameter_ReportId as varchar(200)),c.ReportParameter_Name +
						\'<span class="x-grid-gray-text"> - наследуется </span>\') as originalId,
					isnull(cast(a.ReportContentParameter_ReportLabel as varchar(200)),c.ReportParameter_Label +
						\'<span class="x-grid-gray-text"> - наследуется </span>\') as originalLabel,
					isnull(cast(a.ReportContentParameter_Default as varchar(200)),c.ReportParameter_Default +
						\'<span class="x-grid-gray-text"> - наследуется </span>\') as originalDefault
					from rpt.ReportContentParameter a (nolock)
						join rpt.ReportContent b (nolock) on a.ReportContent_id = b.ReportContent_id
						join rpt.ReportParameter c (nolock) on c.ReportParameter_id = a.ReportParameter_id
					where a.ReportContent_id = ?
						order by position';
			} else {
				$sql = '
					select
						a.ReportContentParameter_id as "ReportContentParameter_id",
						a.ReportParameter_id as "ReportParameter_id",
						a.ReportContent_id as "ReportContent_id",
						a.ReportContentParameter_Default as "ReportContentParameter_Default",
						a.ReportContentParameter_Required as "ReportContentParameter_Required",
						a.ReportContentParameter_ReportId as "ReportContentParameter_ReportId",
						a.ReportContentParameter_Position as "ReportContentParameter_Position",
						a.ReportContentParameter_PrefixId as "ReportContentParameter_PrefixId",
						a.ReportContentParameter_PrefixText as "ReportContentParameter_PrefixText",
						a.ReportContentParameter_ReportLabel as "ReportContentParameter_ReportLabel",
						a.pmUser_insID as "pmUser_insID",
						a.pmUser_updID as "pmUser_updID",
						a.ReportContentParameter_insDT as "ReportContentParameter_insDT",
						a.ReportContentParameter_updDT as "ReportContentParameter_updDT",
						a.ReportContentParameter_Position as "position",
						a.ReportContentParameter_SQL as "ReportContentParameter_SQL",
						a.ReportContentParameter_SQLIdField as "ReportContentParameter_SQLIdField",
						a.ReportContentParameter_SQLTextField as "ReportContentParameter_SQLTextField",
						isnull(cast(a.ReportContentParameter_ReportId as varchar(200)),c.ReportParameter_Name +
							\'<span class="x-grid-gray-text"> - наследуется </span>\') as originalId,
						isnull(cast(a.ReportContentParameter_ReportLabel as varchar(200)),c.ReportParameter_Label +
							\'<span class="x-grid-gray-text"> - наследуется </span>\') as originalLabel,
						isnull(cast(a.ReportContentParameter_Default as varchar(200)),c.ReportParameter_Default +
							\'<span class="x-grid-gray-text"> - наследуется </span>\') as originalDefault
					from rpt.ReportContentParameter a (nolock)
						join rpt.ReportContent b (nolock) on a.ReportContent_id = b.ReportContent_id
						join rpt.ReportParameter c (nolock) on c.ReportParameter_id = a.ReportParameter_id
					where b.ReportContent_Name is null and b.Report_id = ?
						order by position';
			}
			//$result = @sqlsrv_query($conn,$sql,array($params['ownerId']));
			$result = @$this->db->query($sql,array($params['ownerId']));
		} else {
			$result = @$this->db->query(self::REPORTCONTENTPARAMETER_SQL);
		}
		return $this->toResponce($result, null, 'ReportContentParameter');
	}

	/**
	 * @param $params
	 * @return string
	 * Получение параметров
	 */
	public function getReportParameter($params){
		//$conn = $this->getConnection($params);
		//$result = @sqlsrv_query($conn,self::REPORTPARAMETER_SQL.$this->getWhere('ReportParameterCatalog_id',$params));
		$result = @$this->db->query(self::REPORTPARAMETER_SQL.$this->getWhere('ReportParameterCatalog_id',$params));
		return $this->toResponce($result);
	}

	/**
	 * @param $params
	 * @return string
	 * Получение каталога параметров
	 */
	public function getReportParameterCatalog($params){
		//$conn = $this->getConnection($params);
		//$result = @sqlsrv_query($conn,self::REPORTPARAMETERCATALOG_SQL.$this->getWhere('ReportParameterCatalog_pid',$params));
		$result = @$this->db->query(self::REPORTPARAMETERCATALOG_SQL.$this->getWhere('ReportParameterCatalog_pid',$params));
		return $this->toResponce($result);
	}

	/**
	 * Функционал формы для редактирования параметров
	 */
	public function getParametersCombo($params){
		//$conn = $this->getConnection($params);
		$response = new JsonResponce();
		// Возможна проблема с кодировкой $_REQUEST['query']
		$filter = "";
		$join = "";
		$queryParams = array();

		if (!empty($_REQUEST['query'])) {
			$filter .= " and ReportParameter_Name like '%' + :Query + '%'";
			$queryParams['Query'] = $_REQUEST['query'];
		}

		if (!empty($_REQUEST['reportId'])) {
			$join .= "
				outer apply(
					select top 1 R.Region_id 
					from 
						rpt.Report R with(nolock) 
					where R.Report_id = :Report_id
				) as R
			";
			$filter .= " 
				and  isnull(R.Region_id, 0) = isnull(ReportParameter.Region_id, 0)
			";
			$queryParams['Report_id'] =  $_REQUEST['reportId'];
		}
		if ( !empty($_REQUEST['reportContentId']) ) {
			$join .= "
				outer apply(
					select top 1 R.Region_id 
					from
						rpt.ReportContent RC with (nolock) 
						inner join rpt.Report R with (nolock) on R.Report_id = RC.Report_id
					where RC.ReportContent_id = :ReportContent_id
				) as R
			";
			$filter .= " 
				and  isnull(R.Region_id, 0) = isnull(ReportParameter.Region_id, 0)
			";
			$queryParams['ReportContent_id'] = $_REQUEST['reportContentId'];
		}

		$query = "
			select
				ReportParameter.ReportParameter_id,
				ReportParameter.ReportParameterCatalog_id,
				ReportParameter.ReportParameter_Name,
				ReportParameter.ReportParameter_Type,
				(
					ReportParameter.ReportParameter_Label + ' (' +
					case when ReportParameter.Region_id is not null then dbo.GetRegionNamebyCode(ReportParameter.Region_id) else 'root' end
					+ ')'
				) as ReportParameter_Label,
				ReportParameter.ReportParameter_Mask,
				ReportParameter.ReportParameter_Length,
				ReportParameter.ReportParameter_MaxLength,
				ReportParameter.ReportParameter_Default,
				ReportParameter.ReportParameter_Align,
				ReportParameter.ReportParameter_CustomStyle,
				ReportParameter.ReportParameter_SQL,
				ReportParameter.ReportParameter_SQL_IdField,
				ReportParameter.ReportParameter_SQL_TextField,
				ReportParameter.ReportParameter_SQL_XTemplate,
				ReportParameter.pmUser_insID,
				ReportParameter.pmUser_updID,
				ReportParameter.ReportParameter_insDT,
				ReportParameter.ReportParameter_updDT,
				ReportParameter.Region_id
			from 
				rpt.ReportParameter with(nolock)
				{$join}
			where
				(1=1)
				{$filter}
			order by ReportParameter.ReportParameter_Label

		";
		//$par = array();
		//echo getDebugSQL($query,$par);
		//$result = @sqlsrv_query($conn, $query);
		$result = @$this->db->query($query, $queryParams);
		return $this->toResponce($result);
	}

	const SQL = '
		select
			ReportParameter_id as "ReportParameter_id",
			ReportParameterCatalog_id as "ReportParameterCatalog_id",
			ReportParameter_Name as "ReportParameter_Name",
			ReportParameter_Type as "ReportParameter_Type",
			ReportParameter_Label as "ReportParameter_Label",
			ReportParameter_Mask as "ReportParameter_Mask",
			ReportParameter_Length as "ReportParameter_Length",
			ReportParameter_MaxLength as "ReportParameter_MaxLength",
			ReportParameter_Default as "ReportParameter_Default",
			ReportParameter_Align as "ReportParameter_Align",
			ReportParameter_CustomStyle as "ReportParameter_CustomStyle",
			ReportParameter_SQL as "ReportParameter_SQL",
			ReportParameter_SQL_IdField as "ReportParameter_SQL_IdField",
			ReportParameter_SQL_TextField as "ReportParameter_SQL_TextField",
			ReportParameter_SQL_XTemplate as "ReportParameter_SQL_XTemplate",
			pmUser_insID as "pmUser_insID",
			pmUser_updID as "pmUser_updID",
			ReportParameter_insDT as "ReportParameter_insDT",
			ReportParameter_updDT as "ReportParameter_updDT",
			Region_id as "Region_id"
		from rpt.ReportParameter ReportParameter (nolock)
	';
	/**
	 * Проверка правильности запроса в параметре отчета.
	 * На входе - sql с другими параметрами, имя столбца для id
	 * и имя столбца для текста.
	 * 1. Парсим текст запроса и выбираем все параметры (@*)
	 *    их может и не быть, тогда к шагу 5
	 * 2. Выбираем эти параметры из таблицы параметров
	 * 3. Проверяем что такие параметры есть, если нет то ошибка
	 * 4. Готовим sql, вместо параметров подставляем их дефолтные значения
	 *    если таких нет то смотрим тип и если возможно подставляем сами
	 *    для простых типов - текущую дату, 0, истина, пустая строка
	 *    для датасетов -1
	 * 5. Выполняем запрос, с ограничением на 100 строк
	 * 6. Если ошибка то отдаем, если ОК то проверяем наличие нужных столбцов и
	 *    отдаем список параметтров и сам результат
	 */
	protected function prepareSql($conn,$sql,$Region_id) {
		$defaults = array();
		$matches = array();
		$names = array();
		$parameters = array();
		$types = array();
		if(preg_match_all('/\@([a-zA-Z_]+)/',$sql,$matches)) {
			$matches[1] = array_unique($matches[1]);
			$in = '(\''.implode('\',\'',$matches[1]).'\')';
			//2.
			//$result = @sqlsrv_query($conn,self::SQL.' where ReportParameter_Name in '.$in. ' and Region_id = '.$Region_id);
			$query = self::SQL." where ReportParameter_Name in {$in}";
			if (!empty($Region_id)) {
				$query .= " and Region_id = {$Region_id}";
			} else {
				$query .= " and Region_id is null";
			}

			$result = @$this->db->query($query);
			if(!$result) throw new Exception(self::getErrors());
			$rsp = $result->result('array');
			//while($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			if ( is_array($rsp) && count($rsp) > 0 ) {
				foreach ( $rsp as $row ) {
					$types[] = $row['ReportParameter_Type'];
					$names[] = $row['ReportParameter_Name'];
					$defaults[] = $row['ReportParameter_Default'];
					$parameters[] = $row;
				}
			}
			//3.
			if(count($names) == 0) throw new Exception('Параметры указанные вами не найдены в каталоге параметров<br>'.$in);
			if(count($names) != count($matches[1])) {
				$diff = '(\''.implode('\',\'',array_diff($matches[1], $names)).'\')';
				throw new Exception('Параметры указанные вами не найдены в каталоге параметров<br>'.$diff);
			}
			//4.
			$def = array('char'=>'','int'=>0,'date'=>getdate(),'datetime'=>getdate(),'time'=>getdate(),
					'money'=>0.0,'bool'=>true,'yesno'=>'no','dataset'=>-1,'multidata'=>-1,'person'=>-1,'diag'=>-1,'org'=>-1);
			$replace = array();
			for($i=0; $i < count($names); $i++) {
				// ЗАТЫК !!! 
				if(!isset($_REQUEST[$names[$i]])){
					$data = $defaults[$i];
				} else {
					$data = $_REQUEST[$names[$i]];
				}
				if(!$data) $data = $def[$types[$i]];
				switch($types[$i]) {
					case 'char' :
					case 'date' :
					case 'datetime' :
					case 'time' :
					case 'yesno' :
						if($data != 'NULL') $data = '\''.$data.'\'';
						break;
				}
				$replace[$names[$i]] = $data;
			}
			// Сортируем имена так, чтобы самые длинные были первыми
			// иначе замена будет неверной
			krsort($replace);
			foreach($replace as $name=>$data) {
				$sql = preg_replace('/\@'.$name.'/',$data ,$sql);
			}
		}
		return array($sql,$parameters);
	}

	/**
	 * @param $params
	 * @return string
	 */
	public function checkSql($params) {
		$conn = $this->getConnection($params);
		$response = new JsonResponce();
		$sql = $params['sql'];
		$Region_id = $params['Region_id'];
		$parameters = array();
		// 1.
		try {
			list($sql,$parameters) = $this->prepareSql($conn,$sql,$Region_id);
		} catch (Exception $e){
			return $response->error($e->getMessage())->utf()->json();
		}
		//5.
		//$result = @sqlsrv_query($conn,$sql);
		$result = @$this->db->query($sql);
		if(!$result) return $response->error(self::getErrors())->json();
		$counter = 2;
		$data = array();
		$rsp = $result->result('array');
		//while($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
		if ( is_array($rsp) && count($rsp) > 0 ) {
			foreach ( $rsp as $row ) {
				$data[] = $row;
				$counter++;
				if($counter > 100) break;
			}
		}
		$result = array('data'=>$response->toStore($data)->get(),'params'=>$response->toStore($parameters)->get());
		$response->clear();
		$response->success(true);
		return $response->set('result',$result)->utf()->json();
	}


	/**
	 * Функции самого движка построения формы и получения отчета
	 */


	/**
	 * Функция возвращает зависимости для каждого параметра.
	 * Выполняет поиск default значения.
	 * @param <type> $sql
	 * @return <type>
	 */
	protected function getDependences($sql,$params) {
		$matches = array();
		$conn = $this->getConnection($params);
		$ret = array();
		if(preg_match_all('/\@([a-zA-Z_]+)/',$sql,$matches)) {
			$depends = array_values(array_unique($matches[1]));
			foreach($depends as $id){
				/*$result = sqlsrv_query(
						$conn,
						"select ReportParameter_Default from rpt.ReportParameter ReportParameter (nolock) where ReportParameter_Name = ?",
						array($id)
				);*/
				$result = @$this->db->query(
						"select ReportParameter_Default from rpt.ReportParameter ReportParameter (nolock) where ReportParameter_Name = ?",
						array($id)
				);
				if(!$result){
					$ret[] = array('name'=>$id,'default'=>null);
				} else {
					//$row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
					$rsp = $result->result('array');
					$row = $rsp[0];
					$ret[] = array('name'=>$id,'default'=>$row['ReportParameter_Default']);
				}
			}
		} else return null;
		return $ret;
	}

	/**
	 * @param $conn
	 * @param $sql
	 * @return array
	 * @throws Exception
	 */
	protected function getFields($conn,$sql,$Region_id){
		$parameters = array();
		list($sql,$parameters) = $this->prepareSql($conn,$sql,$Region_id);
		$prep = sqlsrv_prepare($conn,$sql);
		if(!$prep) throw new Exception(self::getErrors());
		$fields = sqlsrv_field_metadata($prep);
		$temp = array();
		if (is_array($fields)) {
			foreach ($fields as $field) {
				$temp[] = $field['Name'];
			}
		}
		return $temp;
	}

	/**
	 * Функция возвращает json - содержащий все необходимые сведения
	 * для генерации формы с параметрами отчета,
	 * включая зависимости параметров
	 */
	const REPORT_ENGINE_SQL = 'select Report.Report_id, Report.ReportCatalog_id, Report.Report_Caption, Report.Report_Description, Report.Report_Title, Report.Report_FileName, Report.Report_Status, Report.Report_Position, Report.pmUser_insID, Report.pmUser_updID, Report.Report_insDT, Report.Report_updDT, Report.Region_id, Report.ReportType_id, Report.DatabaseType from rpt.Report Report (nolock) join rpt.ReportCatalog ReportCatalog (nolock) on
		Report.ReportCatalog_id = ReportCatalog.ReportCatalog_id where Report_id = ?';
	//const PARAMETER_ENGINE_SQL = 'select * from rpt.v_ReportContent with(nolock) where reportid = ? order by position,inPosition';

	const PARAMETER_ENGINE_SQL = "
		select
			RCP.ReportContentParameter_id,
			RCP.ReportContentParameter_Required as required,
			ISNULL(RCP.ReportContentParameter_Default, RP.ReportParameter_Default) AS [default],
			RCP.ReportContentParameter_Position AS inPosition,
			RCP.ReportContentParameter_PrefixId AS prefixid,
			RCP.ReportContentParameter_PrefixText AS prefixtext,
			ISNULL(RCP.ReportContentParameter_ReportLabel, RP.ReportParameter_Label) AS label,
			ISNULL(RCP.ReportContentParameter_ReportId, RP.ReportParameter_Name) AS id,
			RP.ReportParameter_Type AS type,
			RP.ReportParameter_Mask AS mask,
			RP.ReportParameter_Length AS length,
			RP.ReportParameter_MaxLength AS maxLength,
			RP.ReportParameter_Align AS align,
			RP.ReportParameter_CustomStyle AS customStyle,
			case
				when isnull(RCP.ReportContentParameter_SQL, '') != '' then RCP.ReportContentParameter_SQL
				else RP.ReportParameter_SQL 
			end AS sql,
			case 
				when isnull(RCP.ReportContentParameter_SQLIdField, '') != '' and isnull(RCP.ReportContentParameter_SQL, '') != '' then RCP.ReportContentParameter_SQLIdField
				else RP.ReportParameter_SQL_IdField
			end AS idField,
			case
				when isnull(RCP.ReportContentParameter_SQLTextField, '') != '' and isnull(RCP.ReportContentParameter_SQL, '') != '' then RCP.ReportContentParameter_SQLTextField
				else RP.ReportParameter_SQL_TextField 
			end AS textField,
			RP.ReportParameter_SQL_XTemplate AS xtemplate,
			RC.Report_id AS reportid,
			RC.ReportContent_Name AS fieldsetLabel,
			RC.ReportContent_id AS contentid,
			RP.ReportParameter_Name AS originalName,
			RP.ReportParameter_Label AS originalLabel,
			RP.ReportParameter_Default AS originalDefault,
			substring(RCParamArmStr.RCParamArmStr_Items, 1, len(RCParamArmStr.RCParamArmStr_Items)-1) as RCParameterArm,
		CASE WHEN RC.ReportContent_Name IS NULL THEN RCP.ReportContentParameter_Position ELSE RC.ReportContent_Position END AS position
		from rpt.ReportContent RC (nolock)
		INNER JOIN rpt.ReportContentParameter RCP (nolock) ON RCP.ReportContent_id = RC.ReportContent_id
		INNER JOIN rpt.ReportParameter RP (nolock) ON RP.ReportParameter_id = RCP.ReportParameter_id
		outer apply (
			select (
				select (AT.ARMType_SysNick + ',') as 'data()'
				from rpt.v_ReportContentParameterLink RCPL (nolock)
				inner join v_ARMType AT (nolock) on AT.ARMType_id = RCPL.ARMType_id
				where RCPL.ReportContentParameter_id = RCP.ReportContentParameter_id
				for xml path('')
			) as RCParamArmStr_Items
		) RCParamArmStr
		where 
			RC.report_id = ?
			and (
				RP.Region_id = dbo.getRegion()
				or exists (
					select top 1 ReportList_id
					from 
						rpt.ReportList (nolock)
					where 
						ReportContentParameter_id = RCP.ReportContentParameter_id
						and Region_id = dbo.getRegion()
				)
			)
		order by position, inPosition";


	const PARAMETER_LPU_DEFAULT_SQL = 'select Lpu_id, Lpu_IsOblast, Lpu_Nick, isnull(case when cast(substring(ltrim(str(Lpu_Ouz)),3,2) as int)<=7 then 1 else cast(substring(ltrim(str(Lpu_Ouz)),3,2)as int) end,-1) OMSSprTerrPrm from v_Lpu (nolock) where Lpu_id = ?';

	/**
	 * @param $params
	 * @return string
	 */
	public function getReportContentEngine($params) {
		$Cur_Lpu = $params['Lpu_id'];
		$Cur_OrgFarmacy = $params['OrgFarmacy_id'];
		//var_dump($params);
		//Получим регион
		$Region_id = '';
		$params_region = array();
		$params_region['Report_id'] = $params['reportId'];
		$sql_region = "select Region_id from rpt.Report with(nolock) where Report_id = :Report_id";
		$result_region = @$this->db->query($sql_region,$params_region);
		if(is_object($result_region)){
			$result_region = $result_region->result('array');
			if(is_array($result_region) && count($result_region) > 0){
				$Region_id = $result_region[0]['Region_id'];
			}
		}
		session_write_close();
		$conn = $this->getConnection($params);
		$response = new JsonResponce();
		$reportId = $params['reportId'];
		$ret = array();
		// 1. Достаем данные об самом отчете
		//$result = sqlsrv_query($conn,self::REPORT_ENGINE_SQL,array($reportId));
		$result = @$this->db->query(self::REPORT_ENGINE_SQL,array($reportId));
		if(!$result) return $response->error(self::getErrors())->json();
		//$row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
		$rsp = $result->result('array');
		if ( is_array($rsp) && count($rsp) > 0 ) {
			$ret['report'] = $rsp[0];
		}
		else {
			$ret['report'] = array();
		}
		// 2. Достаем параметры
		$data = array();
		//$result = sqlsrv_query($conn,self::PARAMETER_ENGINE_SQL,array($reportId));
		$result = @$this->db->query(self::PARAMETER_ENGINE_SQL,array($reportId));
		if(!$result) return $response->error(self::getErrors())->json();
		$currentFieldSet = null;
		$temp = array();
		$maxLength = 300;
		$maxLabel = 0;
		
		// для всех, кроме суперадмина и минздрава для отчетов где есть paramLpu_IsOblast и paramOMSSprTerrPrm
		$lpu_data = false;
		if(!(isMinZdrav() || isSuperadmin()))// && in_array($reportId, array(322,375,380,381,2859)))
		{
			//$r = sqlsrv_query($conn,self::PARAMETER_LPU_DEFAULT_SQL,array($Cur_Lpu ));
			$r = @$this->db->query(self::PARAMETER_LPU_DEFAULT_SQL,array($Cur_Lpu ));
			if(!$r) return $response->error(self::getErrors())->json();
			//$lpu_data = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
			$rsp = $r->result('array');
			if ( is_array($rsp) && count($rsp) > 0 ) {
				$lpu_data = $rsp[0];
			}
		}

		$armList = array();
		if (!empty($_SESSION['ARMList'])) {
			$armList = $_SESSION['ARMList'];	
		}

		//while($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
		$rsp = $result->result('array');
		foreach ( $rsp as $row ) {
			// Упрощаем задачу для JS.
			// а) Соединяем унаследованные параметры
			// б) Формируем филдсеты
			// в) Формируем зависимости
			// г) Вычисляем максимальную ширину  поля
			// д) Вычисляем самую длинную метку
			// e) Выполняем тестовый запрос, для того чтобы передать список полей
			if($row['type'] == 'dataset' || $row['type'] == 'multidata') {

				//Если параметр прикреплён к отчёту из ветки root, то проверяем включена ли видимость этого параметра для текущего региона
				if ( empty($Region_id) ) {
					// проверяем связан ли текущий регион с параметром
					$query = "
						select Region_id
						from rpt.ReportList (nolock)
						where 
							ReportContentParameter_id = :ReportContentParameter_id
							and Region_id = :Region_id
					";
					$queryParams = array(
						'ReportContentParameter_id' => $row['ReportContentParameter_id'],
						'Region_id' => getRegionNumber()
					);
					
					$reg_resp = $this->db->query($query, $queryParams);
					if (is_object($reg_resp)) {
						$reg_result = $reg_resp->result('array');
						// если у параметра не указан текущий регион, то пропускаем обработку параметра
						if (empty($reg_result)) {
							continue;
						}
					} else {
						continue;
					}
				}


				if(!$row['sql']) return $response->error('Не задан SQL для параметра - '.$row['id'])->utf()->json();
				$row['depends'] = $this->getDependences($row['sql'],$params);

				try {
					$row['fields'] = $this->getFields($conn, $row['sql'], $Region_id);
					$row['sql'] = '';
				} catch (Exception $e){
					return $response->error($e->getMessage())->json();
				}
			}

			// проверяем имеет ли пользователь АРМ указанный в настройках доступа для параметра, если нет, то блокируем редактирование параметра
			if (!empty($row['RCParameterArm']) && empty(array_intersect($armList, explode(',', str_replace(' ', '', $row['RCParameterArm'])))) ) {
				$row['disabled'] = true;
			} else {
				$row['disabled'] = false;
			}

			/*if ($_SESSION['login']=='user1_1') {
				print_r($row);
			}*/
			// Наткнулись на филдсет
			if($row['fieldsetLabel'] && !$currentFieldSet) {
				$currentFieldSet = array('id'=>$row['contentid'],'label'=>$row['fieldsetLabel']);
			}

			if($currentFieldSet) {
				if(!$row['fieldsetLabel']) {
					$data[] = $currentFieldSet;
					$currentFieldSet = null;
				} elseif($currentFieldSet['id'] != $row['contentid']) {
					$data[] = $currentFieldSet;
					$currentFieldSet = array('id'=>$row['contentid'],'label'=>$row['fieldsetLabel']);
				}
			}
			// ЗАТЫК !!!! подставляем default для paramLpu и paramLpuDlo из сессии
			if(in_array($row['originalName'], array('paramLpu','paramLpuDlo', 'paramLpuCmp','paramLpuHosp','paramLpuPC', 'paramLpuList')) && isset($Cur_Lpu) && $Cur_Lpu > 0)
			{
				$row['default'] = $Cur_Lpu;
			}
            if(in_array($row['originalName'], array('paramMedService')) && isset($params['session']['CurMedService_id'])){
                $row['default'] = $params['session']['CurMedService_id'];
            }
            if(in_array($row['originalName'], array('paramOrgFarmacySokr')) && isset($Cur_OrgFarmacy) && $Cur_OrgFarmacy > 0)
            {
            	$row['default'] = $Cur_OrgFarmacy;
            }
			//Аналогичный затык для paramSMO
			if((in_array($row['originalName'],array('paramSMO'))) &&($_SESSION['org_id'])){
				//http://redmine.swan.perm.ru/issues/29669 В сессии есть только org_id, а в отчетах используется orgsmo_id, поэтому дёрнем его и запихнем в дефолт
				$orgsmo_id = -1;
				$query_orgsmo_id = "
					select orgSMO_id
					from v_OrgSMO with(nolock)
					where Org_id = ".$_SESSION['org_id'];
				$result_orgsmo = $this->db->query($query_orgsmo_id,array());
				if(is_object($result_orgsmo)){
					$result_orgsmo = $result_orgsmo->result('array');
					if(count($result_orgsmo)){
						$orgsmo_id = $result_orgsmo[0]['orgSMO_id'];
					}
				}
				$row['default'] = $orgsmo_id;
			}

			// для всех, кроме суперадмина и минздрава для отчетов где есть paramLpu_IsOblast и paramOMSSprTerrPrm
			if($lpu_data)
			{
				if($row['originalName'] == 'paramOMSSprTerrPrm' )
				{
					$row['default'] = $lpu_data['OMSSprTerrPrm'];
				}
				if($row['originalName'] == 'paramLpu_IsOblast' )
				{
					$row['default'] = $lpu_data['Lpu_IsOblast'];
				}
				if($row['originalName'] == 'paramLpuTerr' )
				{
					$row['default'] = $lpu_data['Lpu_id'];
				}
			}

			$maxLength = $row['length'] > $maxLength ? $row['length'] : $maxLength;
			$maxLabel = strlen($row['label']) > $maxLabel ? strlen($row['label']) : $maxLabel;

			if($currentFieldSet) {
				$currentFieldSet['parameters'][] = $row;
			} else {
				$data[] = $row;
			}
		}
		//var_dump($data);die;
		$ret['params'] = $data;
		$ret['maxLength'] = $maxLength;
		$ret['maxLabel'] = $maxLabel;
		/*if ($_SESSION['login']=='user1_1') {
			print_r($ret);
		}*/
		$response->set('result',$ret);
		$response->success(true);
		return $response->utf()->json();
	}

	const GET_PARAMETER_ENGINE_SQL = '
		select
			ReportParameter_id as "ReportParameter_id",
			ReportParameterCatalog_id as "ReportParameterCatalog_id",
			ReportParameter_SQL_XTemplate as "ReportParameter_SQL_XTemplate",
			pmUser_insID as "pmUser_insID",
			pmUser_updID as "pmUser_updID",
			ReportParameter_insDT as "ReportParameter_insDT",
			ReportParameter_Name as "ReportParameter_Name",
			ReportParameter_updDT as "ReportParameter_updDT",
			Region_id as "Region_id",
			ReportParameter_Type as "ReportParameter_Type",
			ReportParameter_Label as "ReportParameter_Label",
			ReportParameter_Mask as "ReportParameter_Mask",
			ReportParameter_Length as "ReportParameter_Length",
			ReportParameter_MaxLength as "ReportParameter_MaxLength",
			ReportParameter_Default as "ReportParameter_Default",
			ReportParameter_Align as "ReportParameter_Align",
			ReportParameter_CustomStyle as "ReportParameter_CustomStyle",
			ReportParameter_SQL as "ReportParameter_SQL",
			ReportParameter_SQL_IdField as "ReportParameter_SQL_IdField",
			ReportParameter_SQL_TextField as "ReportParameter_SQL_TextField"
		from rpt.ReportParameter RP (nolock)
		where ReportParameter_Name = ?
		  and isnull(Region_id, 0) = isnull(?, 0)
	';
	const CHECK_UNIQUE_REPORT_CAPTION = 'select Report.Report_id, Report.ReportCatalog_id, Report.Report_Caption, Report.Report_Description, Report.Report_Title, Report.Report_FileName, Report.Report_Status, Report.Report_Position, Report.pmUser_insID, Report.pmUser_updID, Report.Report_insDT, Report.Report_updDT, Report.Region_id, Report.ReportType_id, Report.DatabaseType from rpt.Report Report (nolock) where Report_Caption = ? and isnull(Report_id, 0)<> isnull(?, 0) and isnull(Region_id, 0) = (select isnull(Region_id, 0) as Region_id from rpt.ReportCatalog where ReportCatalog_id = ?)';

	/**
	 * @param $params
	 * @return string
	 * Функция для отдачи данных в комбобоксы параметров отчета
	 */
	public function getParameterContentEngine($params){
		$conn = $this->getConnection($params);
		$id = $params['__id'];
		$response = new JsonResponce();
		//Получим регион
		$Region_id = '';
		$params_region = array();
		$params_region['ReportContent_id'] = $params['contentId'];
		$sql_region = "select R.Region_id from rpt.ReportContent RC with(nolock) left join rpt.Report R with(nolock) on R.Report_id = RC.Report_id where RC.ReportContent_id = :ReportContent_id";
		$result_region = @$this->db->query($sql_region,$params_region);
		if(is_object($result_region)){
			$result_region = $result_region->result('array');
			if(is_array($result_region) && count($result_region) > 0){
				$Region_id = $result_region[0]['Region_id'];
			}
		}
		//1. Выбираем параметр
		//$result = sqlsrv_query($conn,self::GET_PARAMETER_ENGINE_SQL,array($id,$Region_id));
		$result = @$this->db->query(self::GET_PARAMETER_ENGINE_SQL,	array($id, $Region_id));
		if(!$result) return $response->error(self::getErrors())->utf()->json();
		//$row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
		$rsp = $result->result('array');
		$row = $rsp[0];

		$query = "
			select RCP.ReportContentParameter_SQL as \"ReportContentParameter_SQL\"
			from 
				rpt.ReportContentParameter RCP with (nolock)
			where
				RCP.ReportContent_id = :ReportContent_id
				and RCP.ReportParameter_id = :ReportParameter_id
				and (
					dbo.getRegion() = :Region_id -- регион базового параметра равен текущему
					or exists ( -- или текущий регион выбран на форме параметра привязанного к отчёту
						select top 1 ReportList_id
						from rpt.ReportList (nolock)
						where 
							ReportContentParameter_id = RCP.ReportContentParameter_id
							and Region_id = dbo.getRegion()
					)
				)
		";
		$queryParams = array(
			'ReportContent_id' => $params['contentId'],
			'ReportParameter_id' => $row['ReportParameter_id'],
			'Region_id' => $row['Region_id']
		);
		$rcp_resp = $this->db->query($query, $queryParams);

		if (is_object($rcp_resp)) {
			$rcp_result = $rcp_resp->result('array');
			if (!empty($rcp_result[0])) {
				$row['ReportContentParameter_SQL'] = $rcp_result[0]['ReportContentParameter_SQL'];
			}
		}

		if (!empty($row['ReportContentParameter_SQL'])) {
			$sql = $row['ReportContentParameter_SQL'];
		} else {
			$sql = $row['ReportParameter_SQL'];
		}
		
		$parameters = array();
		try {
			list($sql,$parameters) = $this->prepareSql($conn,$sql,$Region_id);
		} catch (Exception $e){
			return $response->error($e->getMessage())->json();
		}
		//$result = sqlsrv_query($conn,$sql);
		$result = @$this->db->query($sql);
		if(!$result) return $response->error(self::getErrors())->json();
		$data = array();
		$resp = $this->toResponce($result, $id);
		return $resp;
	}

	/**
	 * Копирует настройки доступа из отчёта в параметр
	 */
	public function copyArmAccess($Report_id, $ReportContentParameter_id) {
		$query = "
			select ARMType_id as \"ARMType_id\"
			from
				rpt.ReportARM RA with (nolock)
			where
				RA.Report_id = :Report_id
		";
		$resp = $this->db->query($query, array('Report_id' => $Report_id));
		if (is_object($resp)) {

			$result = $resp->result('array');
			if (!empty($result)) {

				if(isset($_SESSION['pmuser_id']) && $_SESSION['pmuser_id'] > 0) {
					$pmUser_id = $_SESSION['pmuser_id'];
				} else {
					$pmUser_id = 0;
				}

				$query = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);

					exec rpt.p_ReportContentParameterLink_ins
						@ReportContentParameterLink_id = @Res output,
						@ARMType_id = :ARMType_id,
						@ReportContentParameter_id = :ReportContentParameter_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					select @Res as ReportContentParameterLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg
				";
				foreach ($result as $row) {
					$queryParams = array(
						'ReportContentParameter_id' => $ReportContentParameter_id,
						'ARMType_id' => $row['ARMType_id'],
						'pmUser_id' => $pmUser_id
					);

					$resp = $this->db->query($query, $queryParams);
					if (is_object($resp)) {
						$result = $resp->result('array');
						if (!empty($result[0]['Error_Msg'])) {
							throw new Exception($result[0]['Error_Msg']);
						}
					} else {
						throw new Exception("Ошибка при копировании прав доступа");	
					}
				}
			}
		}

		return true;
	}

	/**
	 * CRUD функции
	 */

	public function executeSQL($name) {
		$mode = $_REQUEST['__mode'];
		$formData = $_REQUEST;
		//Внезапно упала возможность редактирования каталога - почему-то перестал передаваться ReportCatalog_pid. Поэтому дергаем его руками. https://redmine.swan.perm.ru/issues/24267
		if(($mode == 'edit') && ($name == 'ReportCatalog')){
			if (!$_REQUEST['ReportCatalog_pid']){
				$params_reportcatalog_pid = array();
				$params_reportcatalog_pid['ReportCatalog_id'] = $_POST['ReportCatalog_id'];
				$sql_reportcatalog_pid = "
									select ReportCatalog_pid
									from rpt.ReportCatalog with(nolock)
									where ReportCatalog_id = :ReportCatalog_id
								";
				$result_reportcatalog_pid = $this->db->query($sql_reportcatalog_pid,$params_reportcatalog_pid);
				if(is_object($result_reportcatalog_pid)){
					$res_reportcatalog_pid = $result_reportcatalog_pid->result('array');
					if (!empty($res_reportcatalog_pid[0]['ReportCatalog_pid'])) {
						$_REQUEST['ReportCatalog_pid'] = $res_reportcatalog_pid[0]['ReportCatalog_pid'];
					}
				}
			}
		}

		if ( !empty($formData['Region_ids']) ) {
			$ErrorRegion_ids = false;
			$Region_ids = json_decode($formData['Region_ids']);
			if ( !is_array($Region_ids) ) {
				$ErrorRegion_ids = true;
			} else {
				foreach ($Region_ids as $value) {
					if (!is_integer(0 + $value)) {
						$ErrorRegion_ids = true;
						break;
					}
				}
			}

			if ($ErrorRegion_ids) {
				$response = new JsonResponce();
				return $response->error('В параметр Region_ids переданы некорректные данные')->utf()->json();
			}
			
		}

		$values = array($_REQUEST[$name.'_id']);
		switch($mode){
			case 'delete' :
				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
					exec rpt.p_{$name}_del
						@{$name}_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output";
			break;
			case 'edit':
			case 'add' :


				if($name == 'Report'){ //Если добавляем или редактируем отчет
					//if(strlen(toAnsi($_REQUEST['Report_Caption']))>120){ //https://redmine.swan.perm.ru/issues/24673
                    if(strlen(mb_convert_encoding($_REQUEST['Report_Caption'],'ASCII'))>120){
						$response = new JsonResponce();
						return $response->error('Длина заголовка отчета не должна превышать 120 символов.')->utf()->json();
					}
					//Проверим на уникальность заголовка (https://redmine.swan.perm.ru/issues/24673):
					$params = array(
						'Report_Caption' => $_REQUEST['Report_Caption']
					);
					$and = '';
					if(isset($_REQUEST['Report_id']) && ($_REQUEST['Report_id'] <> 0)){
						$and = ' and Report_id !='.$_REQUEST['Report_id'];
					}
                    if(isset($_REQUEST['ReportCatalog_id']) && ($_REQUEST['ReportCatalog_id'] <> 0)){
                        $and .= ' and isnull(Region_id, 0) = (select isnull(Region_id, 0) as Region_id from rpt.ReportCatalog (nolock) where ReportCatalog_id = '.$_REQUEST['ReportCatalog_id'].')';
                    }
					$query = "
						select Report_id
						from rpt.Report (nolock)
						where Report_Caption = :Report_Caption".$and;
					$res = $this->db->query($query,$params);
					if ( is_object($res) ){
						$result = $res->result('array');
						if(count($result)>0){
							$response = new JsonResponce();
							return $response->error('Отчет с таким заголовком уже существует.')->utf()->json();
						}
					}
				}
				$params = '';
				unset($_REQUEST[$name.'_id']);
				$sfx = $mode == 'add' ? 'ins' : 'upd';
				$length = strlen('Report');
				foreach ($_REQUEST as $key=>$value) {
					if ( $key == 'Region_ids' || (substr($key,0,$length) != 'Report' and $key != 'DatabaseType') ) continue;
						$params .= '@'.$key.' = ?,';
						if (($key == 'ReportType_id') && ($value == '')) {
							$values[] = null;
						}
						else{
							$values[] = $value === null ? null : toAnsi($value);
						}
				}
				//Получим регион:
				$region_id = '';
				switch($name){
					case 'ReportCatalog':
						//Узнаем region_id у каталог-пида
						//$reportCatalog_id  = $_REQUEST['ReportCatalog_pid'];
						$params_region = array();
						if ( isset($this->_regionList[$_REQUEST['ReportCatalog_pid']]) ) {
							$values[1] = null;

							if ($this->_regionList[$_REQUEST['ReportCatalog_pid']]['code'] === null) {
								$region_id = '@Region_id = null,';
							} else {
								$region_id = '@Region_id = ' . $this->_regionList[$_REQUEST['ReportCatalog_pid']]['code'] . ',';
							}
			
						}

						else
						{
							if($mode == 'add')
								$params_region['ReportCatalog_id'] = $_REQUEST['ReportCatalog_pid'];
							if($mode == 'edit')
								{
									$params_region['ReportCatalog_id'] = $_POST['ReportCatalog_id'];
								}
							if(	isset($_REQUEST['Region_id'])){
								if ( !$_REQUEST['Region_id'] ) {
									$region_id = '@Region_id = null,';
								} else if (is_integer(0 + $_REQUEST['Region_id'])) {
									$region_id = '@Region_id = '.$_REQUEST['Region_id'].',';
								}
								
							}
							else{
								$sql_region = "
										select Region_id
										from rpt.ReportCatalog with(nolock)
										where ReportCatalog_id = :ReportCatalog_id";
								$result_region = $this->db->query($sql_region,$params_region);
								if(is_object($result_region)){
									$res_region = $result_region->result('array');
									if(count($res_region) > 0) {
										if ($res_region[0]['Region_id'] === null ) {
											$region_id = '@Region_id = null';
										} else {
											$region_id = '@Region_id = '.$res_region[0]['Region_id'].',';
										}
										
									}
								}
							}
						}
					break;
					case 'Report':
						//Узнаем region_id у каталога
						$params_region = array();
						$params_region['ReportCatalog_id'] = $_REQUEST['ReportCatalog_id'];
						$sql_region = "
								select Region_id
								from rpt.ReportCatalog with(nolock)
								where ReportCatalog_id = :ReportCatalog_id";
						$result_region = $this->db->query($sql_region,$params_region);
						if(is_object($result_region)){
							$res_region = $result_region->result('array');
							if(!empty($res_region) && is_array($res_region) && count($res_region) > 0) {
								if ($res_region[0]['Region_id'] === null) {
									$region_id = '@Region_id = null,';
								} else {
									$region_id = '@Region_id = '.$res_region[0]['Region_id'].',';
								}
								
							}
						}
						break;
					case 'ReportParameter':
						if($mode == 'add'){
							$checkParams = array(
								'ParamId' => $_REQUEST['ReportParameter_Name'],
								'RegionId' => $_REQUEST['Region_id']
							);
							$ans = $this->ajaxcheckParamId($checkParams);
							if(strpos($ans,'true') == false){
								$resp = new JsonResponce();
								return $resp->error('Параметр с таким идентификатором уже существует в выбранном Вами регионе')->utf()->json();
							}
						}

						if ( empty($_REQUEST['Region_id']) ) {
							$region_id = '@Region_id = null,';
						} else if (is_integer(0 + $_REQUEST['Region_id'])) {
							$region_id = '@Region_id = '.$_REQUEST['Region_id'].',';
						}
						
						break;
				}
				if(isset($_SESSION['pmuser_id']) && $_SESSION['pmuser_id'] > 0) {
					$pmUser_id = $_SESSION['pmuser_id'];
				} else {
					$pmUser_id = 0;
				}


				$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = ?;
				exec rpt.p_{$name}_{$sfx}
					@{$name}_id = @Res output,
								$params
					@pmUser_id = {$pmUser_id},
					@Error_Code = @ErrCode output,
					".$region_id."
					@Error_Message = @ErrMessage output

				select @Res as insertId, @ErrCode as Error_Code, @ErrMessage as Error_Msg
				";
		break;
		}
		try {
			$this->beginTransaction();
			//echo getDebugSQL($query,$values);
			//die;
			if($mode == 'add'){
				//$result = self::command($query, $values,true);
				$result = $this->command($query, $values,true);
			} else {
				//$result = self::command($query, $values);
				$result = $this->command($query, $values);
			}	

			$jsonResponse = new JsonResponce();
			if(!$result) {
				$this->rollbackTransaction();
				return $jsonResponse->error(self::getErrors())->utf()->json();
			} else {

				// при создании параметра (ReportContentParameter) копируем настройки доступа из родительского отчёта
				if ($name == 'ReportContentParameter' && $mode == 'add') {
					$this->copyArmAccess($formData['Report_id'], $result);
				}
				$filter = "";
				if (!empty($Region_ids)) {
					$strRegions = implode(', ', $Region_ids);
					$filter .= " and Region_id not in({$strRegions})";
				}

				// получаем идентификатор редактируемого объекта(папка, отчёт или параметр)
				if (in_array($name, array('Report', 'ReportCatalog', 'ReportContentParameter'))) { 
					if (!empty($formData[$name . '_id'])) {
						$Object_id = $formData[$name . '_id'];
					} else if (!empty($result)) {
						$Object_id = $result;
					} else {
						$Object_id = null;
					}
				}
				
				if (!empty($Object_id) && $mode != 'delete') {
					// удаляем записи регионов которых нет в запросе
					$query = "
						delete from rpt.ReportList with (rowlock)
						where
							{$name}_id = :Object_id
							{$filter}
					";
					$queryParams = array(
						'Object_id' => $Object_id
					);
					$this->db->query($query, $queryParams);
				
					if ( !empty($Region_ids) ) {
						
						$query = "
							select Region_id
							from rpt.ReportList with (nolock)
							where
								{$name}_id = :Object_id
						";
						$respRegion_ids = $this->db->query($query, array('Object_id' => $Object_id));
						if (is_object($respRegion_ids)) {
							$respRegion_ids = $respRegion_ids->result('array');

						} else {
							$this->rollbackTransaction();
							$resp = new JsonResponce();
							return $resp->error('Ошибка при получении данных о регионах')->utf()->json();
						}
						
						//получаем id регионов которые ещё не добавлены в таблицу, но выбраны на форме
						$diffRegion_ids = $Region_ids;
						foreach ($respRegion_ids as $key => $value) {
							$index = array_search($value['Region_id'], $diffRegion_ids);
							if ( $index !== false ) {
								unset($diffRegion_ids[$index]);
							}
						}

						foreach ($diffRegion_ids as $key => $value) {
							$query = "
								declare
									@ReportList_id bigint = null,
									@Error_Code int,
									@Error_Message varchar(4000);
								exec rpt.p_ReportList_ins
									@ReportList_id = @ReportList_id OUTPUT,
									@ReportCatalog_id = :ReportCatalog_id ,
									@Report_id = :Report_id,
									@ReportContentParameter_id = :ReportContentParameter_id,
									@Region_id = :Region_id,
									@pmUser_id = :pmUser_id,
									@Error_Code = @Error_Code OUTPUT,
									@Error_Message = @Error_Message OUTPUT
								select @ReportList_id as ReportList_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
							";
							$queryParams = array(
								'ReportCatalog_id' => null,
								'Report_id' => null,
								'ReportContentParameter_id' => null,
								'Region_id' => $value,
								'pmUser_id' => $pmUser_id
							);
							$queryParams[$name . '_id'] = $Object_id;
							$resp = $this->db->query($query, $queryParams);
							
							$jsonResp = new JsonResponce();
							$errorAddRegion = false;
							if ( is_object($resp) ) {
								$resp = $resp->result('array');
								if ( is_array($resp)) {
									if (!empty($resp[0]['Error_Msg'])) {
										$this->rollbackTransaction();	
										return $jsonResp->error($resp[0]['Error_Msg'])->utf()->json();
									}				
								} else {
									$errorAddRegion = true;
								}
							} else {
								$errorAddRegion = true;
							}
							if ($errorAddRegion) {
								$this->rollbackTransaction();	
								return $jsonResp->error('Ошибка при сохранении данных о выбранных регионах')->utf()->json();
							}
						}
					}	
				}


				$this->commitTransaction();
				return $jsonResponse->set('id',$result)->success(true)->utf()->json();
			}
		} catch (Exception $e) {
			$jsonResponse = new JsonResponce();
			$this->rollbackTransaction();
			return $jsonResponse->error($e->getMessage())->utf()->json();
		}

	}



	/**
	 * @param $ReportContentParameter_id
	 * смещение позиции параметров -1 при удалении (refs #2737)
	 */
	public function moveReportParametersUp($ReportContentParameter_id){
		//$conn = $this->getConnection($_REQUEST['serverId']);
		$response = new JsonResponce();
		$sql = "SELECT ReportContent_id, ReportContentParameter_Position FROM rpt.ReportContentParameter ReportContentParameter (nolock) WHERE ReportContentParameter_id = ?";
		//$result = sqlsrv_query($conn,$sql,array($ReportContentParameter_id));
		$result = @$this->db->query($sql,array($ReportContentParameter_id));
		if(!$result) return $response->error(self::getErrors())->json();
		$rsp = $result->result('array');

		if(is_array($rsp) && count($rsp) > 0){
			//$res = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
			$res = $rsp[0];
			$position = $res['ReportContentParameter_Position'];
			$ReportContent_id = $res['ReportContent_id'];
			$sql = "SELECT Report_id FROM rpt.ReportContent ReportContent (nolock) WHERE ReportContent_id = ?";
			//$result = sqlsrv_query($conn,$sql, array($ReportContent_id));
			$result = @$this->db->query($sql, array($ReportContent_id));
			if(!$result) return $response->error(self::getErrors())->json();
			$rsp = $result->result('array');

			//if(sqlsrv_has_rows($result)){
			if ( is_array($rsp) && count($rsp) > 0 ) {
				//$res = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
				$res = $rsp[0];
				$Report_id = $res['Report_id'];
			
				$sql = "UPDATE rpt.ReportContentParameter
						SET ReportContentParameter_Position = ReportContentParameter_Position - 1 
						WHERE ReportContentParameter_Position > ? AND ((SELECT COUNT(ReportContent_id) FROM rpt.v_ReportContent ReportContent (nolock) WHERE Report_Id = ? AND ReportContent_id = ReportContentParameter.ReportContent_id) > 0)";

				//sqlsrv_query($conn,$sql, array($position,$Report_id));
				@$this->db->query($sql, array($position,$Report_id));
			}
		}
	}

	/**
	 * @param $position
	 * @param $Report_id
	 * @param $mode
	 * смещение позиции параметров +1 при добавлении (refs #2737) TODO: изменить алгоритм или оптимизировать страшные запросы:)
	 */
	public function moveReportParametersDown($position, $Report_id, $mode){
		//$conn = $this->getConnection($_REQUEST['serverId']);
		$response = new JsonResponce();
		if ($mode=='edit') 
		{
			$ReportContentParameter_id = $_REQUEST['ReportContentParameter_id']; 
			// получаем старую позицию, если она больше новой, то сдвигаем все остальные параметры на +1 только до этой позици
			$sql = "SELECT ReportContentParameter_Position FROM rpt.ReportContentParameter ReportContentParameter (nolock)
					WHERE ReportContentParameter_id = ? AND ((SELECT COUNT(ReportContent_id) FROM rpt.ReportContent ReportContent (nolock) WHERE Report_Id = ? AND ReportContent_id = ReportContentParameter.ReportContent_id) > 0)";
			//$result = sqlsrv_query($conn,$sql, array($ReportParameter_id, $Report_id));
			//$res = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
			$result = @$this->db->query($sql, array($ReportContentParameter_id, $Report_id));
			if(!$result) return $response->error(self::getErrors())->json();
			$rsp = $result->result('array');
			$res = $rsp[0];
			$oldposition = $res['ReportContentParameter_Position'];
		} else { $ReportContentParameter_id = 0; $oldposition = 0; }
		
		// смотрим есть ли такая позиция уже.
		$sql = "SELECT ReportContentParameter_id FROM rpt.ReportContentParameter ReportContentParameter (nolock)
				WHERE ReportContentParameter_id <> ? AND ReportContentParameter_Position = ? AND ((SELECT COUNT(ReportContent_id) FROM rpt.ReportContent ReportContent (nolock) WHERE Report_Id = ? AND ReportContent_id = ReportContentParameter.ReportContent_id) > 0)";
		//$result = sqlsrv_query($conn,$sql, array($ReportParameter_id, $position,$Report_id));
		$result = @$this->db->query($sql, array($ReportContentParameter_id, $position,$Report_id));
		if(!$result) return $response->error(self::getErrors())->json();
		$rsp = $result->result('array');
		
		//if(sqlsrv_has_rows($result)){
		if ( is_array($rsp) && count($rsp) > 0 ) {
		
			if ($oldposition > $position) {
				$where = " AND ReportContentParameter_Position < '{$oldposition}'";
			} else {
				$where = "";
			}
			
			// прибавляем 1 всем
			$sql = "UPDATE rpt.ReportContentParameter
					SET ReportContentParameter_Position = ReportContentParameter_Position + 1 
					WHERE ReportContentParameter_Position >= '{$position}' {$where} AND ((SELECT COUNT(ReportContent_id) FROM rpt.ReportContent ReportContent (nolock) WHERE Report_Id = ? AND ReportContent_id = ReportContentParameter.ReportContent_id) > 0)";
			//$result2 = sqlsrv_query($conn,$sql, array($Report_id));
			$result2 = @$this->db->query($sql, array($Report_id));
			if(!$result2) return $response->error(self::getErrors())->json();
		}
	}

	/**
	 * @param $ReportContent_id
	 * смещение позиции группы параметров -1 при удалении
	 */
	public function moveDatasetUp($ReportContent_id) {
		$response = new JsonResponce();
		$sql = "
			SELECT
				Report_id,
				ReportContent_Position 
			FROM 
				rpt.ReportContent RCP (nolock) 
			WHERE 
				ReportContent_id = ?
		";
		//$result = sqlsrv_query($conn,$sql,array($ReportContentParameter_id));
		$result = @$this->db->query($sql,array($ReportContent_id));
		if(!$result) return $response->error(self::getErrors())->json();
		$rsp = $result->result('array');

		if(is_array($rsp) && count($rsp) > 0){
			//$res = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
			$res = $rsp[0];
			$position = $res['ReportContent_Position'];
			$Report_id = $res['Report_id'];
			
			$sql = "
				UPDATE rpt.ReportContent
				SET ReportContent_Position = ReportContent_Position - 1 
				WHERE 
					ReportContent_Position > :ReportContent_Position 
					AND Report_id = :Report_id
			";
			$queryParams = array(
				'Report_id' => $Report_id,
				'ReportContent_Position' => $position
			);
			//sqlsrv_query($conn,$sql, array($position,$Report_id));
			@$this->db->query($sql, $queryParams);		
		}
	}

	/**
	 * @param $position
	 * @param $Report_id
	 * @param $mode
	 * смещение позиции группы параметров +1 при добавлении
	 */
	public function moveDatasetDown($position, $Report_id, $mode) {
		$response = new JsonResponce();
		if ($mode=='edit') 
		{
			$ReportContent_id = $_REQUEST['ReportContent_id']; 
			// получаем старую позицию, если она больше новой, то сдвигаем все остальные параметры на +1 только до этой позици
			$sql = "
				SELECT ReportContent_Position 
				FROM 
					rpt.ReportContent RC (nolock)
				WHERE 
					ReportContent_id = :ReportContent_id
			";
			//$result = sqlsrv_query($conn,$sql, array($ReportParameter_id, $Report_id));
			//$res = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
			$result = @$this->db->query($sql, array('ReportContent_id' => $ReportContent_id, 'Report_id' => $Report_id));
			if(!$result) return $response->error(self::getErrors())->json();
			$rsp = $result->result('array');
			$res = $rsp[0];
			$oldposition = $res['ReportContent_Position'];
		} else { $ReportContent_id = 0; $oldposition = 0; }
		
		// смотрим есть ли такая позиция уже.
		$sql = "
			SELECT ReportContent_id 
			FROM 
				rpt.ReportContent ReportContent (nolock)
			WHERE 
				ReportContent_id <> :ReportContent_id 
				AND	ReportContent_Position = :ReportContent_Position
				AND Report_id = :Report_id
		";
		$queryParams = array(
			'ReportContent_id' => $ReportContent_id,
			'ReportContent_Position' => $position,
			'Report_id' => $Report_id
		);
		$result = @$this->db->query($sql, $queryParams);

		if(!$result) return $response->error(self::getErrors())->json();
		$rsp = $result->result('array');
		
		//if(sqlsrv_has_rows($result)){
		if ( is_array($rsp) && count($rsp) > 0 ) {
		
			if ($oldposition > $position) {
				$where = " AND ReportContent_Position < :OldPosition";
			} else {
				$where = "";
			}
			
			// прибавляем 1 всем
			$sql = "
				UPDATE rpt.ReportContent
				SET ReportContent_Position = ReportContent_Position + 1 
				WHERE 
					ReportContent_Position >= :Position 
					AND Report_id = :Report_id
					{$where} 
			";

			$queryParams = array(
				'Report_id' => $Report_id,
				'OldPosition' => $oldposition,
				'Position' => $position
			);
			$result2 = @$this->db->query($sql, $queryParams);
			if(!$result2) return $response->error(self::getErrors())->json();
		}
	}

	


	/**
	 * @param $Report_id
	 * смещение позиции отчётов -1 при удалении (refs #2737)
	 */
	public function moveReportUp($Report_id){
		//$conn = $this->getConnection($_REQUEST['serverId']);
		$response = new JsonResponce();
		$sql = "SELECT ReportCatalog_id, Report_Position FROM rpt.Report Report (nolock) WHERE Report_id = ?";
		//$result = sqlsrv_query($conn,$sql,array($Report_id));
		$result = @$this->db->query($sql,array($Report_id));
		if(!$result) return $response->error(self::getErrors())->json();
		$rsp = $result->result('array');

		//if(sqlsrv_has_rows($result)){
		if ( is_array($rsp) && count($rsp) > 0 ) {
			//$res = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
			$res = $rsp[0];
			$position = $res['Report_Position'];
			$ReportCatalog_id = $res['ReportCatalog_id'];
	
			$sql = "UPDATE rpt.Report
					SET Report_Position = Report_Position - 1 
					WHERE Report_Position > ? AND ReportCatalog_id = ?";
					
			//sqlsrv_query($conn,$sql, array($position,$ReportCatalog_id));
			$result = @$this->db->query($sql, array($position,$ReportCatalog_id));
			if(!$result) return $response->error(self::getErrors())->json();
		}
	}

	/**
	 * @param $position
	 * @param $ReportCatalog_id
	 * @param $mode
	 * смещение позиции отчётов +1 при добавлении (refs #2737) TODO: изменить алгоритм или оптимизировать страшные запросы:)
	 */
	public function moveReportDown($position, $ReportCatalog_id, $mode){
		//$conn = $this->getConnection($_REQUEST['serverId']);
		$response = new JsonResponce();
		if ($mode=='edit')
		{
			$Report_id = $_REQUEST['Report_id']; 
			// получаем старую позицию, если она больше новой, то сдвигаем все остальные параметры на +1 только до этой позици
			$sql = "SELECT Report_Position FROM rpt.Report Report (nolock) WHERE Report_id = ?";
			//$result = sqlsrv_query($conn,$sql, array($Report_id));
			//$res = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
			$result = @$this->db->query($sql, array($Report_id));
			if(!$result) return $response->error(self::getErrors())->json();
			$rsp = $result->result('array');
			$res = $rsp[0];
			$oldposition = $res['Report_Position'];
		} else { $Report_id = 0; $oldposition = 0; }
		
		// смотрим есть ли такая позиция уже.
		$sql = "SELECT Report_id FROM rpt.Report Report (nolock) WHERE Report_id <> ? AND Report_Position = ? AND ReportCatalog_id = ?";
		//$result = sqlsrv_query($conn,$sql, array($Report_id, $position, $ReportCatalog_id));
		$result = @$this->db->query($sql, array($Report_id, $position, $ReportCatalog_id));
		if(!$result) return $response->error(self::getErrors())->json();
		$rsp = $result->result('array');

		//if(sqlsrv_has_rows($result)){
		if ( is_array($rsp) && count($rsp) > 0 ) {
			if ($oldposition > $position) {
				$where = " AND Report_Position < '{$oldposition}'";
			} else {
				$where = "";
			}

			// прибавляем 1 всем
			$sql = "UPDATE rpt.Report with (rowlock) SET Report_Position = Report_Position + 1
					WHERE Report_Position >= '{$position}' {$where} AND ReportCatalog_id = ?";
			//$result2 = sqlsrv_query($conn,$sql, array($ReportCatalog_id));
			$result2 = @$this->db->query($sql, array($ReportCatalog_id));
			if(!$result2) return $response->error(self::getErrors())->json();
		}
	}

	/**
	 * @param $ReportCatalog_id
	 * Смещение позиций каталогов вверх (при удалении)
	 */
	public function moveReportCatalogUp($ReportCatalog_id)
	{
		//$conn = $this->getConnection($_REQUEST['serverId']);
		$response = new JsonResponce();

		//Получим пид у каталога - если находимся в корне, то пид - НУЛЛовый, поэтому в последующий апдейт нужно условие на is null
		$sql_RC_pid = "SELECT ReportCatalog_pid FROM rpt.ReportCatalog (nolock) WHERE ReportCatalog_id = ?";
		//$result = sqlsrv_query($conn,$sql_RC_pid, array($ReportCatalog_id));
		$result = @$this->db->query($sql_RC_pid, array($ReportCatalog_id));
		if(!$result) return $response->error(self::getErrors())->json();
		//$res = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
		$rsp = $result->result('array');

		if ( is_array($rsp) && count($rsp) > 0 ) {
			$res = $rsp[0];

			if (is_null($res['ReportCatalog_pid'])) //Если в корне
				$filter_RC_pid = " AND ReportCatalog_pid is NULL";
			else
				$filter_RC_pid = " AND ReportCatalog_pid = ".$res['ReportCatalog_pid'];

			$sql = "UPDATE rpt.ReportCatalog with (rowlock) SET ReportCatalog_Position = ReportCatalog_Position - 1
					WHERE ReportCatalog_Position > (SELECT ReportCatalog_Position FROM rpt.v_ReportCatalog (nolock) WHERE ReportCatalog_id = ?)".$filter_RC_pid;
			//sqlsrv_query($conn,$sql,array($ReportCatalog_id,$ReportCatalog_id));
			@$this->db->query($sql,array($ReportCatalog_id,$ReportCatalog_id));
		}
	}

	/**
	 * @param $ReportCatalog_id
	 * @param $mode
	 * @param $position
	 * @param $ReportCatalog_pid
	 * Смещение позиций каталогов вниз (при добавлении или редактировании)
	 */
	public function moveReportCatalogDown($ReportCatalog_id,$mode,$position,$ReportCatalog_pid)
	{
		//$conn = $this->getConnection($_REQUEST['serverId']);
		$response = new JsonResponce();

		if ( is_null($ReportCatalog_pid) || isset($this->_regionList[$ReportCatalog_pid]) ) {
			$filter_RC_pid = " AND ReportCatalog_pid is NULL";
		}else{
			$filter_RC_pid = " AND ReportCatalog_pid = ".$ReportCatalog_pid;
		}
		if ($mode == 'edit')
		{
			//Получаем старую позицию
			$sql = "SELECT ReportCatalog_Position from rpt.ReportCatalog (nolock) where ReportCatalog_id = ?";
			//$result = sqlsrv_query($conn,$sql,array($ReportCatalog_id));
			//$res = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
			$result = @$this->db->query($sql,array($ReportCatalog_id));
			if(!$result) return $response->error(self::getErrors())->json();
			$rsp = $result->result('array');
			$res = $rsp[0];
			$oldposition = $res['ReportCatalog_Position'];
		}
		else{$ReportCatalog_id=0;$oldposition=0;}

		//Есть ли уже такая позиция
		$sql = "SELECT ReportCatalog_id FROM rpt.v_ReportCatalog (nolock) WHERE ReportCatalog_id <> ? AND ReportCatalog_Position = ?".$filter_RC_pid;
		//$result = sqlsrv_query($conn,$sql,array($ReportCatalog_id,$position));
		//$res = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
		$result = @$this->db->query($sql,array($ReportCatalog_id,$position));
		if(!$result) return $response->error(self::getErrors())->json();
		$rsp = $result->result('array');

		//if(sqlsrv_has_rows($result)){
		if ( is_array($rsp) && count($rsp) > 0 ) {
			if($oldposition > $position)
				$where = " AND ReportCatalog_Position < ".$oldposition;
			else $where = "";

			$sql = "UPDATE rpt.ReportCatalog with (rowlock) SET ReportCatalog_Position = ReportCatalog_Position + 1
			WHERE ReportCatalog_Position >= ? {$where}".$filter_RC_pid;
			//sqlsrv_query($conn,$sql,array($position));
			@$this->db->query($sql,array($position));
		}
	}

	/**
	 * @param $params
	 * @return string
	 * Получение форматов
	 */
	public function getFormats($params){
		$reportId = $params['reportId'];
		$getall = $params['getall'];

		//$conn = $this->getConnection($params);
		
		$this->checkReportFormatList($params);
		
		if($getall){
			$sql = "
				SELECT
					RF.ReportFormat_id,
					RF.ReportFormat_Name,
					RF.ReportFormat_Ext,
					RF.ReportFormat_Icon,
					RFL.ReportFormat_Sort,
					YN.YesNo_id
				FROM
					rpt.ReportFormatList RFL with(nolock)
					LEFT JOIN rpt.ReportFormat RF with(nolock) ON RF.ReportFormat_id = RFL.ReportFormat_id 
					LEFT JOIN v_YesNo YN with(nolock) ON YN.YesNo_Code = CASE 
						WHEN RFL.ReportFormat_Disabled = 1 THEN 0 ELSE 1 END
				WHERE RFL.Report_id = ?
			";
		}
		else {
			$sql = "
				SELECT
					RF.ReportFormat_id,
					RF.ReportFormat_Name,
					RF.ReportFormat_Ext,
					RF.ReportFormat_Icon,
					RFL.ReportFormat_Sort
				FROM
					rpt.ReportFormatList RFL with(nolock)
					LEFT JOIN rpt.ReportFormat RF with(nolock) ON RF.ReportFormat_id = RFL.ReportFormat_id 
				WHERE RFL.Report_id = ? AND RFL.ReportFormat_Disabled = 0
			";

		}
		//$result = sqlsrv_query($conn,$sql,array($reportId));
		$result = @$this->db->query($sql,array($reportId));

		return $this->toResponce($result);
	}

	/**
	 * @param $params
	 */
	public function checkReportFormatList($params){
		//$conn = $this->getConnection($params);
		$reportId = $params['reportId'];
		
		//скопировать форматы из ReportFormat в rpt.ReportFormatList, т.к. их там нет.
		$sql = "
			INSERT INTO rpt.ReportFormatList (Report_id, ReportFormat_id, ReportFormat_Sort, ReportFormat_Disabled,
				pmUser_insID, pmUser_updID, ReportFormatList_insDT, ReportFormatList_updDT)
			SELECT :Report_id, ReportFormat_id, ReportFormat_Sort, 0, 1, 1, dbo.tzGetDate(), dbo.tzGetDate()
			FROM rpt.ReportFormat ReportFormat (nolock)
			WHERE not exists (
				SELECT top 1 ReportFormatList_id
				FROM rpt.ReportFormatList (nolock)
				WHERE ReportFormat_id = ReportFormat.ReportFormat_id
					AND Report_id = :Report_id
			)
		";
		@$this->db->query($sql, [ 'Report_id' => $reportId ]);

	}

	/**
	 * @param $params
	 * @return string
	 * вкл выкл формата для отчёта
	 */
	public function disableReportFormat($params){
		//$conn = $this->getConnection($params);
		$reportId = $params['reportId'];
		$formatId = $params['formatId'];
		$disableflag = $params['disableflag'];
		$response = new JsonResponce();
		
		$sql = "UPDATE rpt.ReportFormatList with (rowlock) SET ReportFormat_Disabled = ? WHERE Report_id = ? AND ReportFormat_Id = ?";
		//sqlsrv_query($conn,$sql,array($disableflag, $reportId, $formatId));
		@$this->db->query($sql,array($disableflag, $reportId, $formatId));
		
		return $response->success(true)->utf()->json();
	}

	/**
	 * @param $params
	 * @return string
	 * изменение позиции формата для отчёта
	 */
	public function changePositionFormat($params){
		//$conn = $this->getConnection($params);
		$reportId = $params['reportId'];
		$formatId = $params['formatId'];
		$changePositionflag = $params['changePositionflag'];
		$response = new JsonResponce();
		// берём текущую позицию
		$sql = "SELECT TOP 1 ReportFormat_Sort FROM rpt.ReportFormatList (nolock) WHERE Report_id = ? AND ReportFormat_Id = ?";
		//$result = sqlsrv_query($conn,$sql,array($reportId, $formatId));
		//$array = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
		$result = @$this->db->query($sql,array($reportId, $formatId));
		if(!$result) return $response->error(self::getErrors())->json();
		$rsp = $result->result('array');
		$array = $rsp[0];
		$ReportFormat_Sort = $array['ReportFormat_Sort'];
		
		if ($changePositionflag==0) {
			// изщем позицию меньшую
			$sql = "SELECT TOP 1 ReportFormat_Id, ReportFormat_Sort FROM rpt.ReportFormatList (nolock) WHERE Report_id = ? AND ReportFormat_Sort < ? ORDER BY ReportFormat_Sort DESC";
			//$result = sqlsrv_query($conn,$sql,array($reportId, $ReportFormat_Sort));
			$result = @$this->db->query($sql,array($reportId, $ReportFormat_Sort));
			if(!$result) return $response->error(self::getErrors())->json();
			$rsp = $result->result('array');

			//if (sqlsrv_has_rows($result)){
			if ( is_array($rsp) && count($rsp) > 0 ) {
				//$array = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
				$array = $rsp[0];
				$ReportFormat_Id = $array['ReportFormat_Id'];
				$ReportFormat_NewSort = $array['ReportFormat_Sort'];
				// меняем элементы местами
				$sql = "UPDATE rpt.ReportFormatList with (rowlock) SET ReportFormat_Sort = ? WHERE Report_id = ? AND ReportFormat_Id = ?";
				//sqlsrv_query($conn,$sql,array($ReportFormat_Sort, $reportId, $ReportFormat_Id));
				@$this->db->query($sql,array($ReportFormat_Sort, $reportId, $ReportFormat_Id));
				//$sql = "UPDATE rpt.ReportFormatList (rowlock) SET ReportFormat_Sort = ? WHERE Report_id = ? AND ReportFormat_Id = ?";
				//sqlsrv_query($conn,$sql,array($ReportFormat_NewSort, $reportId, $formatId));
				@$this->db->query($sql,array($ReportFormat_NewSort, $reportId, $formatId));
			}
		} else {
			// изщем позицию большую
			$sql = "SELECT TOP 1 ReportFormat_Id, ReportFormat_Sort FROM rpt.ReportFormatList with(nolock) WHERE Report_id = ? AND ReportFormat_Sort > ? ORDER BY ReportFormat_Sort ASC";
			//$result = sqlsrv_query($conn,$sql,array($reportId, $ReportFormat_Sort));
			$result = @$this->db->query($sql,array($reportId, $ReportFormat_Sort));
			if(!$result) return $response->error(self::getErrors())->json();
			$rsp = $result->result('array');

			//if (sqlsrv_has_rows($result)) {
			if ( is_array($rsp) && count($rsp) > 0 ) {
				//$array = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
				$array = $rsp[0];
				$ReportFormat_Id = $array['ReportFormat_Id'];
				$ReportFormat_NewSort = $array['ReportFormat_Sort'];
				// меняем элементы местами
				$sql = "UPDATE rpt.ReportFormatList with (rowlock) SET ReportFormat_Sort = ? WHERE Report_id = ? AND ReportFormat_Id = ?";
				//sqlsrv_query($conn,$sql,array($ReportFormat_Sort, $reportId, $ReportFormat_Id));
				@$this->db->query($sql,array($ReportFormat_Sort, $reportId, $ReportFormat_Id));
				//$sql = "UPDATE rpt.ReportFormatList (rowlock) SET ReportFormat_Sort = ? WHERE Report_id = ? AND ReportFormat_Id = ?";
				//sqlsrv_query($conn,$sql,array($ReportFormat_NewSort, $reportId, $formatId));
				@$this->db->query($sql,array($ReportFormat_NewSort, $reportId, $formatId));
			}
		}
		
		return $response->success(true)->utf()->json();
	}

	/**
	 * Запуск отчёта на формирование
	 */
	public function RunReportDBF($data)	{
		$colsql = 'FORMA, NUMBSTR';
		$titlename = array(	array("FORMA","C",10,0),array("NUMBSTR","C",10,0));
		for($i=1;$i<=30;$i++){
			$titlename[] = array("COL".$i,"C",12,0);
			$colsql .= ", ISNULL(COL".$i.",'0') as COL".$i."";
		}
		$sql = "SELECT {$colsql} FROM rpt35.ExpFormToDBF (:Lpu_id, :NumRep, :NumTab, :YearRep)";

		$params	= array('Lpu_id' => $data['Lpu_id'],'NumRep' => $data['NumRep'],'NumTab' => $data['NumTab'],'YearRep' => $data['YearRep']);
		$NumTabName = (($data['NumRep'] == '306') ? '5300' : $data['NumTab']);
		$filename = REPORTPATH_ROOT.$data['NumRep']."_".((!empty($NumTabName)) ? $NumTabName."_" : '').$data['YearRep'].".dbf";

		if (file_exists($filename)) {
			unlink($filename);
		}

		$DBF = dbase_create($filename, $titlename);
		$result =  $this->db->query($sql,$params);
		$res = $result->result('array');
		foreach($res as $row){
			dbase_add_record($DBF, array($row["FORMA"], $row["NUMBSTR"], $row["COL1"], $row["COL2"], $row["COL3"], $row["COL4"], $row["COL5"], $row["COL6"], $row["COL7"], $row["COL8"], $row["COL9"], $row["COL10"], $row["COL11"], $row["COL12"], $row["COL13"], $row["COL14"], $row["COL15"], $row["COL16"], $row["COL17"], $row["COL18"], $row["COL19"], $row["COL20"], $row["COL21"], $row["COL22"], $row["COL23"], $row["COL24"], $row["COL25"], $row["COL26"], $row["COL27"], $row["COL28"], $row["COL29"], $row["COL30"]));
		}

		return $filename;
	}

	/**
	 * @return string
	 */
	public function createReportUrl() {
		//$conn = $this->getConnection($_REQUEST['serverId']);
		$response = new JsonResponce();
		$reportId = $_REQUEST['reportId'];
		$params = array();
		$host = BIRT_SERVLET_PATH.'run?';
		// 1. Достаем данные об самом отчете
		//$result = sqlsrv_query($conn,self::REPORT_ENGINE_SQL,array($reportId));
		$result = @$this->db->query(self::REPORT_ENGINE_SQL,array($reportId));
		if(!$result) return $response->error(self::getErrors())->json();
		//$report = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
		$rsp = $result->result('array');
		$report = $rsp[0];
		$path = $report['ReportCatalog_Path'];
		if($path) $path .= '/';
		$params[] = '__report=report/'.$path.$report['Report_FileName'];
		// 2. Достаем параметры
		//$result = sqlsrv_query($conn,self::PARAMETER_ENGINE_SQL,array($reportId));
		$result = @$this->db->query(self::PARAMETER_ENGINE_SQL,array($reportId));
		if(!$result) return $response->error(self::getErrors())->json();
		//while($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) $data[] = $row;
		$data = $result->result('array');
		if ( is_array($data) && count($data) > 0 ) {
			foreach($data as $param){
					// 1. Если в запросе не пришел нужный параметр
					//    то считаем его нулом - исключение bool
				if(!isset($_REQUEST[$param['id']])){
					if($param['type'] == 'bool'){
						$params[] = $param['id'].'=off';
					} else {
						$params[] = '__isnull='.$param['id'];
					}
					// 2. Параметр пустой
					// Lich. IE зачем-то текстовой null в такие поля посылает. Какая прелесть
				} else if($_REQUEST[$param['id']] == '' || $_REQUEST[$param['id']] == 'null'){
					$params[] = '__isnull='.$param['id'];
					// 3. Если параметр = -1 и он датасет то
					//    если он не required то нулл
				} else if($_REQUEST[$param['id']] == -1 
						  && ($param['type']=='dataset' || $param['type']=='multidata')
						  && !$param['required']){
					$params[] = '__isnull='.$param['id'];
				} else {
					$params[] = $param['id'].'='.toAnsi($_REQUEST[$param['id']]);
				}
			}
		}
		return $response->set('url',$host.implode('&',$params))->success(true)->utf_ru_out();
	}

	/**
	 * @return string
	 */
	public function createReportProxyUrl($onlyParams = false, $requiredParams = false) {
		//$conn = $this->getConnection($_REQUEST['serverId']);
		$response = new JsonResponce();
		$reportId = $_REQUEST['reportId'];
	   
		$host = REPORT_CONTROLLER;
		
		 $params = array();
		// 2. Достаем параметры
		//$result = sqlsrv_query($conn,self::PARAMETER_ENGINE_SQL,array($reportId));
		$result = @$this->db->query(self::PARAMETER_ENGINE_SQL,array($reportId));
		if(!$result) return $response->error(self::getErrors())->json();
		//while($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) $data[] = $row;
		$data = $result->result('array');
		if ( is_array($data) && count($data) > 0 ) {
			foreach($data as $param){
				// 1. Если в запросе не пришел нужный параметр
				//    то считаем его нулом - исключение bool
				if(!isset($_REQUEST[$param['id']])){
					if($param['type'] == 'bool'){
						$params[] = $param['id'].'=off';
					} else {
						$params[] = '__isnull='.$param['id'];
					}
					// 2. Параметр пустой
					// Lich. IE зачем-то текстовой null в такие поля посылает. Какая прелесть
				} else if($_REQUEST[$param['id']] == '' || $_REQUEST[$param['id']] == 'null'){
					$params[] = '__isnull='.$param['id'];
					// 3. Если параметр = -1 и он датасет то
					//    если он не required то нулл
				} else if($_REQUEST[$param['id']] == -1 
						  && ($param['type']=='dataset' || $param['type']=='multidata')
						  && !$param['required']){
					$params[] = '__isnull='.$param['id'];
				} else {
					$params[] = $param['id'].'='.urlencode(toAnsi($_REQUEST[$param['id']]));
				}
			}
			/*
			$params[] = 'param_RegionCode='.$requiredParams['param_RegionCode'];
			$params[] = 'param_pmuser_id='.$requiredParams['param_pmuser_id'];
			$params[] = 'param_pmuser_org_id='.$requiredParams['param_pmuser_org_id'];
			*/
			if($requiredParams){
				$params[] = 'param_RegionCode='.getRegionNumber();
				if(!empty($requiredParams['pmUser_id'])) $params[] = 'param_pmuser_id='.$requiredParams['pmUser_id'];
				if(!empty($requiredParams['LpuOrg_id'])) $params[] = 'param_pmuser_org_id='.$requiredParams['LpuOrg_id'];
			}
		}
		if(!$onlyParams)
			return $response->set('url',$host.'&Report_id='.$reportId.'&Report_Params='.urlencode(implode('&',$params)))->success(true)->utf_ru_out();
		else
			return urlencode(implode('&',$params));
	}

	/**
	 * @param $params
	 * @return string
	 * Проверка параметра
	 */
	public function checkParamId($params){
		//$conn = $this->getConnection($params);
		$id = $params['value'];
		$response = new JsonResponce();
		if (empty($id)) {
			return $response->set('valid',false)
					->set('reason','Имя параметра не может быть пустым')
					->success(true)->utf()->json();
		}
		
		//$result = sqlsrv_query($conn,self::GET_PARAMETER_ENGINE_SQL,array($id));
		$result = @$this->db->query(self::GET_PARAMETER_ENGINE_SQL,array($id));
		if(!$result) return $response->error(self::getErrors())->utf()->json();
		//$rows = sqlsrv_has_rows($result);
		$rsp = $result->result('array');
		$rows = (is_array($rsp) && count($rsp) > 0);
		// Есть параметр с таким именем, и мы не повторяем параметр
		if($rows === true && $params['original'] != $id){
			return $response->set('valid',false)
					->set('reason','Параметр с таким именем уже есть')
					->success(true)->utf()->json();
		} else {
			return $response->set('valid',true)->success(true)->utf()->json();
		}
	}

	/**
	 * Проверка параметра на уникальность в рамках выбранного региона
	 */
	public function ajaxcheckParamId($params){
		//$conn = $this->getConnection($params);
		$response = new JsonResponce();
		$ParamId = $params['ParamId'];
		$RegionId = $params['RegionId'];
		//$result = sqlsrv_query($conn,self::GET_PARAMETER_ENGINE_SQL,array($ParamId,$RegionId));
		if (empty($RegionId)) {
			$query = '
				select
					ReportParameter_id as "ReportParameter_id",
		            ReportParameter_SQL_XTemplate as "ReportParameter_SQL_XTemplate",
		            pmUser_insID as "pmUser_insID",
		            pmUser_updID as "pmUser_updID",
		            ReportParameter_insDT as "ReportParameter_insDT",
		            ReportParameter_updDT as "ReportParameter_updDT",
		            Region_id as "Region_id",
		            ReportParameterCatalog_id as "ReportParameterCatalog_id",
		            ReportParameter_Name as "ReportParameter_Name",
		            ReportParameter_Type as "ReportParameter_Type",
		            ReportParameter_Label as "ReportParameter_Label",
		            ReportParameter_Mask as "ReportParameter_Mask",
		            ReportParameter_Length as "ReportParameter_Length",
		            ReportParameter_MaxLength as "ReportParameter_MaxLength",
		            ReportParameter_Default as "ReportParameter_Default",
		            ReportParameter_Align as "ReportParameter_Align",
		            ReportParameter_CustomStyle as "ReportParameter_CustomStyle",
		            ReportParameter_SQL as "ReportParameter_SQL",
		            ReportParameter_SQL_IdField as "ReportParameter_SQL_IdField",
		            ReportParameter_SQL_TextField as "ReportParameter_SQL_TextField"
				from rpt.ReportParameter ReportParameter (nolock)
				where ReportParameter_Name = ?
				  and Region_id is null
			';
			@$this->db->query($query,array($ParamId));
		}
		$result = @$this->db->query(self::GET_PARAMETER_ENGINE_SQL,array($ParamId,$RegionId));
		if($result){
			//$rows = sqlsrv_has_rows($result);
			$rsp = $result->result('array');
			$rows = (is_array($rsp) && count($rsp) > 0);
			if($rows == true)
				$response->success(false);
		   else
				$response->success(true);
			return $response->utf()->json();
		}
	}

	/**
	 * @param $params
	 * @return string
	 * Проверка заголовка отчета на уникальность
	 */
	public function checkUniqueReportCaption($params){
		//$conn = $this->getConnection($params);
		$ReportCaption = $params['value'];
		$ReportCatalog_id = $params['ReportCatalog_id'];
		$Report_id = $params['Report_id'];
		//var_dump($params);die;
		$response = new JsonResponce();
		if(empty($ReportCaption)){
			return $response->set('valid',false)
					->set('reason','Заголовок не может быть пустым')
					->success(true)->utf()->json();
		}

		if(strlen(mb_convert_encoding($ReportCaption,'ASCII'))>120){
			return $response->set('valid',false)
				->set('reason','Длина заголовка не может превышать 120 символов')
				->success(true)->utf()->json();
		}
		$result = @$this->db->query(self::CHECK_UNIQUE_REPORT_CAPTION,array($ReportCaption,$Report_id,$ReportCatalog_id));
		if(!$result) return $response->error(self::getErrors())->utf()->json();
		//$rows = sqlsrv_has_rows($result);
		$rsp = $result->result('array');
		$rows = (is_array($rsp) && count($rsp) > 0);
		if($rows === true && $params['original'] != $ReportCaption){
			return $response->set('valid',false)
				->set('reason','Отчет с таким заголовком уже есть')
				->success(true)->utf()->json();
		} else {
			return $response->set('valid',true)->success(true)->utf()->json();
		}
	}

	/**
	 * @param $params
	 * @return string
	 * Проверка описания отчета на длину
	 */
	public function checkReportDescriptionLength($params){
		$ReportDescription = $params['value'];
		$response = new JsonResponce();
		if(strlen(mb_convert_encoding($ReportDescription,'ASCII'))>500){
			return $response->set('valid',false)
				->set('reason','Длина описания не может превышать 500 символов')
				->success(true)->utf()->json();
		}
		else
			return $response->set('valid',true)->success(true)->utf()->json();
	}

	/**
	 * @param $params
	 * @return string
	 * Проверка наименования отчета на длину
	 */
	public function checkReportTitleLength($params){
		$ReportTitle = $params['value'];
		$response = new JsonResponce();
        if(strlen(mb_convert_encoding($ReportTitle,'ASCII'))>200){
			return $response->set('valid',false)
				->set('reason','Длина наименования не может превышать 200 символов')
				->success(true)->utf()->json();
		}
		else
			return $response->set('valid',true)->success(true)->utf()->json();
	}
	/**
	 * @param $data
	 * @return array|boolean
	 * Проверка наименования отчета на длину
	 */
	public function setReportCatalog($data){
		$proc = 'p_Report_upd';

		$reportData = $this->getInfoReport($data);
		if(!empty($reportData["Report_id"])){
			$reportData['ReportCatalog_id'] = $data['ReportCatalog_id'];
			$reportData['pmUser_id'] = $data['pmUser_id'];
		}
		else{
			return array('success' => false, 'Error_Code' => null, 'Error_Msg' => 'Ошибка идентификатора отчета (не существует)');
		}
		if(!empty($data['forceIns'])){
			$proc = 'p_Report_ins';
			$reportData['Report_Caption'] = 'Копия '.((!empty($reportData['Report_Caption']))?$reportData['Report_Caption']:'отчета');
			$reportData['Region_id'] = $data['Region_id'];
			$reportData['Report_id'] = null;
		}
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :Report_id;
			exec rpt.{$proc}
				@Report_id = @Res output,
				@ReportCatalog_id = :ReportCatalog_id,
				@Report_Caption = :Report_Caption,
				@Report_Description = :Report_Description,
				@Report_Title = :Report_Title,
				@Report_FileName = :Report_FileName,
				@Report_Status = :Report_Status,
				@Report_Position = :Report_Position,
				@Region_id = :Region_id,
				@ReportType_id = :ReportType_id,
				@DatabaseType = :DatabaseType,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as Report_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//echo getDebugSQL($query, $reportData); exit;
		$result = $this->db->query($query, $reportData);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return array
	 * Все поля отчета
	 */
	public function getInfoReport($data){
		$reportQuery = "select Report.Report_id, Report.ReportCatalog_id, Report.Report_Caption, Report.Report_Description, Report.Report_Title, Report.Report_FileName, Report.Report_Status, Report.Report_Position, Report.pmUser_insID, Report.pmUser_updID, Report.Report_insDT, Report.Report_updDT, Report.Region_id, Report.ReportType_id, Report.DatabaseType from rpt.Report Report with (nolock)  where Report.Report_id = :Report_id";
		$reportData = $this->db->query($reportQuery, $data)->row_array();
		return $reportData;
	}
	/**
	 * @param $data
	 * @return array
	 * Все поля отчета
	 */
	public function getAllReportARM($data){
		$ARMQuery = "
			select ReportARM_id as \"ReportARM_id\",
				   ARMType_id as \"ARMType_id\",
				   Report_id as \"Report_id\",
				   pmUser_insID as \"pmUser_insID\",
				   pmUser_updID as \"pmUser_updID\",
				   ReportARM_insDT as \"ReportARM_insDT\",
				   ReportARM_updDT as \"ReportARM_updDT\"
			from rpt.ReportARM as ARM with (nolock)
			where ARM.Report_id = :Report_id
		";
		$ARMData = $this->db->query($ARMQuery, $data);
		if ( is_object($ARMData) ) {
			$ARMData = $ARMData->result('array');
		} else {
			return false;
		}
		return $ARMData;
	}
	/**
	 * @param $data
	 * @return array|boolean
	 * Проверка наименования отчета на длину
	 */
	public function createReportARM($data){
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = null;
			exec rpt.p_ReportARM_ins
				@ReportARM_id = @Res output,
				@ARMType_id = :ARMType_id,
				@Report_id = :Report_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as ReportARM_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//echo getDebugSQL($query, $reportData); exit;
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * @param $data
	 * @return array|boolean
	 * Все поля отчета
	 */
	public function getAllReportContent($data){
		$ARMQuery = "
			select
				ReportContent_id as \"ReportContent_id\",
				Report_id as \"Report_id\",
				ReportContent_Name as \"ReportContent_Name\",
				ReportContent_Position as \"ReportContent_Position\",
				pmUser_insID as \"pmUser_insID\",
				pmUser_updID as \"pmUser_updID\",
				ReportContent_insDT as \"ReportContent_insDT\",
				ReportContent_updDT as \"ReportContent_updDT\"
			from rpt.ReportContent as RC with (nolock)
			where RC.Report_id = :Report_id
		";
		$ARMData = $this->db->query($ARMQuery, $data);
		if ( is_object($ARMData) ) {
			$ARMData = $ARMData->result('array');
		} else {
			return false;
		}
		return $ARMData;
	}
	/**
	 * @param $allRContentIDs
	 * @return array|boolean
	 * Все поля отчета
	 */
	public function getAllReportContentParam($allRContentIDs){
		if(empty($allRContentIDs))
			return false;
		$RCParamsQuery = "
			select
				RCP.ReportContentParameter_id as \"ReportContentParameter_id\",
				RCP.ReportParameter_id as \"ReportParameter_id\",
				RCP.ReportContent_id as \"ReportContent_id\",
				RCP.ReportContentParameter_Default as \"ReportContentParameter_Default\",
				RCP.ReportContentParameter_Required as \"ReportContentParameter_Required\",
				RCP.ReportContentParameter_ReportId as \"ReportContentParameter_ReportId\",
				RCP.ReportContentParameter_Position as \"ReportContentParameter_Position\",
				RCP.ReportContentParameter_PrefixId as \"ReportContentParameter_PrefixId\",
				RCP.ReportContentParameter_PrefixText as \"ReportContentParameter_PrefixText\",
				RCP.ReportContentParameter_ReportLabel as \"ReportContentParameter_ReportLabel\",
				RCP.pmUser_insID as \"pmUser_insID\",
				RCP.pmUser_updID as \"pmUser_updID\",
				RCP.ReportContentParameter_insDT as \"ReportContentParameter_insDT\",
				RCP.ReportContentParameter_updDT as \"ReportContentParameter_updDT\",
				RCP.ReportContentParameter_Position as \"position\"
			from rpt.ReportContentParameter as RCP with (nolock)  
			where RCP.ReportContent_id IN (".implode(',',$allRContentIDs).")
		";
		$RCParamsData = $this->db->query($RCParamsQuery, array());
		if ( is_object($RCParamsData) ) {
			$RCParamsData = $RCParamsData->result('array');
		} else {
			return false;
		}
		return $RCParamsData;
	}
	/**
	 * @param $allRParamIDs
	 * @return array|boolean
	 * Все поля отчета
	 */
	public function getAllReportParam($allRParamIDs){
		if(empty($allRParamIDs))
			return false;
		$RParamsQuery = "
			select
				ReportParameter_id as \"ReportParameter_id\",
				ReportParameterCatalog_id as \"ReportParameterCatalog_id\",
				ReportParameter_Name as \"ReportParameter_Name\",
				ReportParameter_Type as \"ReportParameter_Type\",
				ReportParameter_Label as \"ReportParameter_Label\",
				ReportParameter_Mask as \"ReportParameter_Mask\",
				ReportParameter_Length as \"ReportParameter_Length\",
				ReportParameter_MaxLength as \"ReportParameter_MaxLength\",
				ReportParameter_Default as \"ReportParameter_Default\",
				ReportParameter_SQL_XTemplate as \"ReportParameter_SQL_XTemplate\",
				pmUser_insID as \"pmUser_insID\",
				pmUser_updID as \"pmUser_updID\",
				ReportParameter_Align as \"ReportParameter_Align\",
				ReportParameter_insDT as \"ReportParameter_insDT\",
				ReportParameter_updDT as \"ReportParameter_updDT\",
				ReportParameter_CustomStyle as \"ReportParameter_CustomStyle\",
				Region_id as \"Region_id\",
				ReportParameter_SQL as \"ReportParameter_SQL\",
				ReportParameter_SQL_IdField as \"ReportParameter_SQL_IdField\",
				ReportParameter_SQL_TextField as \"ReportParameter_SQL_TextField\"
			from rpt.ReportParameter as RP with (nolock)  
			where RP.ReportParameter_id IN (".implode(',',$allRParamIDs).")
		";
		$RParamsData = $this->db->query($RParamsQuery, array());
		if ( is_object($RParamsData) ) {
			$RParamsData = $RParamsData->result('array');
		} else {
			return false;
		}
		return $RParamsData;
	}
	/**
	 * @param $data
	 * @return array|boolean
	 * Все поля отчета
	 */
	public function getRegionParamsByNames($data){
		if(empty($data['paramNames']))
			return false;
		$RParamsNameQuery = "select 
							rp.ReportParameter_id,
							rp.ReportParameter_Name
					  	from rpt.ReportParameter as rp with (nolock)  
					  	where isnull(rp.Region_id, 0) = isnull(:Region_id, 0)
							AND rp.ReportParameter_Name IN ('".implode("','",$data['paramNames'])."')";
		$RParamsNameData = $this->db->query($RParamsNameQuery, $data);
		if ( is_object($RParamsNameData) ) {
			$RParamsNameData = $RParamsNameData->result('array');
		} else {
			return false;
		}
		return $RParamsNameData;
	}

	/**
	 * Копирует видимость отчёта/параметра на отчёте в регионах
	 */
	public function copyRegionAccess ($ObjIdFrom, $ObjIdTo, $type) {
		$err = false;
		if ($type != 'Report' && $type != 'ReportContentParameter' ) {
			return array('Error_Msg' => 'Неверно указан тип объекта при копировании настроек видимости в регионах');
		}
		$query = "
			select
				Region_id
			from rpt.ReportList
			where
				{$type}_id = :Obj_id
		";
		$queryParams = array(
			'Obj_id' => $ObjIdFrom
		);
		$resp = $this->db->query($query, $queryParams);

		if(isset($_SESSION['pmuser_id']) && $_SESSION['pmuser_id'] > 0) {
			$pmUser_id = $_SESSION['pmuser_id'];
		} else {
			$pmUser_id = 0;
		}

		if (is_object($resp)) {
			$result = $resp->result('array');
			if (is_array($result)) {
				foreach ($result as $row) {
					$query = "
						declare
							@ReportList_id bigint = null,
							@Error_Code int,
							@Error_Message varchar(4000);
						exec rpt.p_ReportList_ins
							@ReportList_id = @ReportList_id OUTPUT,
							@{$type}_id = :Obj_id,
							@Region_id = :Region_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @Error_Code OUTPUT,
							@Error_Message = @Error_Message OUTPUT
						select @ReportList_id as ReportList_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
					";
					$queryParams = array(
						'Obj_id' => $ObjIdTo,
						'Region_id' => $row['Region_id'],
						'pmUser_id' => $pmUser_id
					);
					$region_resp = $this->db->query($query, $queryParams);
					if (is_object($region_resp)) {
						$region_result = $region_resp->result('array');
						if (is_array($region_result)) {
							if (!empty($region_result[0]['Error_Msg'])) {
								return $region_result[0];
							}
						} else {
							$err = true;
						}
					} else {
						$err = true;
					}
				}
			} else {
				$err = true;
			}
		} else {
			$err = true;
		}

		if ($err) {
			return array('Error_Msg' => 'Ошибка при копировании настроек регионов');
		}

		return false;
	}

	/**
	 * @param $data
	 * @return array|boolean
	 * Копирование отчета
	 */
	public function copyReport($data){

		//Берём ин-фу по Report
		//Создаем копию Report
		$data['forceIns'] = true;
		if ( empty($data['Region_id']) ) {
			$data['Region_id'] = null;
		}

		$this->beginTransaction();

		$newReportData = $this->setReportCatalog($data);
		if( empty($newReportData) // пустой массив
			|| ( !empty($newReportData) && is_array($newReportData)  // если не пустой и массив
				&& (!empty($newReportData[0]['Error_Msg']) || empty($newReportData[0]['Report_id'])) // наличие ошибки или нет Report_id
			)
		){
			$this->rollbackTransaction();
			return array('success' => false, 'Error_Code' => null, 'Error_Msg' => 'Ошибка создания отчета');
		}
		$NewReport_id = $newReportData[0]['Report_id']; // id нового отчета

		//Берём все ReportARM
		$allReportARM = $this->getAllReportARM($data);
		//Создаем копии ReportARM
		if(!empty($allReportARM)){
			foreach($allReportARM as $arm){
				$arm['Report_id'] = $NewReport_id;
				$arm['pmUser_id'] = $data['pmUser_id'];
				$this->createReportARM($arm);
			}
		}

		$reg_error = $this->copyRegionAccess($data['Report_id'], $NewReport_id, 'Report');
		if (!empty($reg_error['Error_Msg'])) {
			$this->rollbackTransaction();
			return $reg_error;
		}

		$paramNames = array();
		// Массив подмены старого id параметра на новый
		$allNewRParamIDs = array();
		// Массив получения id по имени параметра
		$allRParamIdByName = array();
		$allNewRContentIDs = array();


		// Массив всех ReportContent_id
		$allRContentIDs = array();
		//Берём все ReportContent
		$allReportContent = $this->getAllReportContent($data);
		//Создаем копии ReportContent c привязкой к новому Report_id и новому региону
		if(empty($allReportContent)) {
			$this->commitTransaction();
			return array('NewReport_id' => $NewReport_id);
		}
		foreach($allReportContent as $rContent){
			$allRContentIDs[] = $rContent['ReportContent_id'];
			$rContent['pmUser_id'] = (!empty($data['pmUser_id']))?$data['pmUser_id']:null;
			$rContent['Report_id'] = $NewReport_id;
			$newRContent = $this->createReportContent($rContent);
			if(!empty($newRContent) && !empty($newRContent[0]) && !empty($newRContent[0]['ReportContent_id']))
				$allNewRContentIDs[$rContent['ReportContent_id']] = $newRContent[0]['ReportContent_id'];
		}


		// Массив всех ReportParameter_id
		$allRParamIDs = array();
		//берем все ReportContentParameter по всем ReportContent_id
		$allReportContentParam = $this->getAllReportContentParam($allRContentIDs);
		if(empty($allReportContentParam)) {
			$this->commitTransaction();
			return array('NewReport_id' => $NewReport_id);
		}
		foreach($allReportContentParam as $rcParam)
			$allRParamIDs[] = $rcParam['ReportParameter_id'];


		//берем все ReportParameter по всем ReportContentParameter_id
		$allReportParam = $this->getAllReportParam($allRParamIDs);
		if(empty($allReportParam)) {
			$this->commitTransaction();
			return array('NewReport_id' => $NewReport_id);
		}
		//Собираем имена нужных параметров
		foreach($allReportParam as $rParam)
			$paramNames[] =  $rParam['ReportParameter_Name'];


		// Найдём уже существующие в регионе параметры
		$data['paramNames'] = $paramNames;
		$ExistParamsInRegion = $this->getRegionParamsByNames($data);
		// Массив уже существующих на регионе параметров
		$regExistRParamNames = array();
		foreach($ExistParamsInRegion as $regExistParam){
			$regExistRParamNames[] = $regExistParam['ReportParameter_Name'];
			// Массив получения id по имени параметра
			$allRParamIdByName[$regExistParam['ReportParameter_Name']] = $regExistParam['ReportParameter_id'];
		}


		// Если все параметры уже существуют на регионе, добавлять нечего
		if(count($paramNames) != count($ExistParamsInRegion)){
			foreach($allReportParam as $rParam){
				// Если параметр существует на регионе - не создаем его копию
				if(!in_array($rParam['ReportParameter_Name'],$regExistRParamNames)){
					//Иначе создаем копию параметра на необходимом регионе
					$rParam['pmUser_id'] = (!empty($data['pmUser_id']))?$data['pmUser_id']:null;
					$rParam['Region_id'] = $data['Region_id'];
					$newRParam = $this->createReportParameter($rParam);
					if(!empty($newRParam) && !empty($newRParam[0]) && !empty($newRParam[0]['ReportParameter_id']))
						$allRParamIdByName[$rParam['ReportParameter_Name']] = $newRParam[0]['ReportParameter_id'];
				}
			}
		}
		// Необходимо для каждого id параметра на старом регионе
		// поставить в соответствие идентификатор на новом регионе
		foreach($allReportParam as $rParam){
			$allNewRParamIDs[$rParam['ReportParameter_id']] = $allRParamIdByName[$rParam['ReportParameter_Name']];
		}

		//Создаем копии ReportContentParameter
		//подставляем ReportParameter_id созданных ReportParameter и существующих на регионе
		//подставляем ReportContent_id созданных ReportContent на регионе
		foreach($allReportContentParam as $rContentParam){
			$rContentParam['pmUser_id'] = (!empty($data['pmUser_id']))?$data['pmUser_id']:null;
			$rContentParam['ReportParameter_id'] = $allNewRParamIDs[$rContentParam['ReportParameter_id']];
			$rContentParam['ReportContent_id'] = $allNewRContentIDs[$rContentParam['ReportContent_id']];
			$rContentParam['Report_id'] = $NewReport_id;
			$newRContentParam = $this->createReportContentParameter($rContentParam);
			if (!empty($rContentParam['ReportContentParameter_id']) && !empty($newRContentParam[0]['ReportContentParameter_id'])) {
				$err_rcp = $this->copyRegionAccess($rContentParam['ReportContentParameter_id'], $newRContentParam[0]['ReportContentParameter_id'], 'ReportContentParameter');
				if (!empty($err_rcp['Error_Msg'])) {
					$this->rollbackTransaction();
					return $err_rcp;
				}
			}
			
		}

		if (!empty($NewReport_id)) {
			$this->commitTransaction();
			return array(
				'NewReport_id' => $NewReport_id,
				'existRParamNames' => (!empty($regExistRParamNames))?implode(", ",$regExistRParamNames):''
			);
		} else {
			$this->rollbackTransaction();
			return false;
		}
	}
	/**
	 * @param $data
	 * @return array|boolean
	 * Проверка наименования отчета на длину
	 */
	public function createReportContent($data){
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = null;
			exec rpt.p_ReportContent_ins
				@ReportContent_id = @Res output,
				@Report_id = :Report_id,
				@ReportContent_Name = :ReportContent_Name,
				@ReportContent_Position = :ReportContent_Position,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as ReportContent_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//echo getDebugSQL($query, $reportData); exit;
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * @param $data
	 * @return array|boolean
	 * Проверка наименования отчета на длину
	 */
	public function createReportParameter($data){
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = null;
			exec rpt.p_ReportParameter_ins
				@ReportParameter_id = @Res output,
				@ReportParameterCatalog_id = :ReportParameterCatalog_id,
				@ReportParameter_Name = :ReportParameter_Name,
				@ReportParameter_Type = :ReportParameter_Type,
				@ReportParameter_Label = :ReportParameter_Label,
				@ReportParameter_Mask = :ReportParameter_Mask,
				@ReportParameter_Length = :ReportParameter_Length,
				@ReportParameter_MaxLength = :ReportParameter_MaxLength,
				@ReportParameter_Default = :ReportParameter_Default,
				@ReportParameter_Align = :ReportParameter_Align,
				@ReportParameter_CustomStyle = :ReportParameter_CustomStyle,
				@ReportParameter_SQL = :ReportParameter_SQL,
				@ReportParameter_SQL_IdField = :ReportParameter_SQL_IdField,
				@ReportParameter_SQL_TextField = :ReportParameter_SQL_TextField,
				@ReportParameter_SQL_XTemplate = :ReportParameter_SQL_XTemplate,
				@Region_id = :Region_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as ReportParameter_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//echo getDebugSQL($query, $reportData); exit;
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * @param $data
	 * @return array|boolean
	 * Проверка наименования отчета на длину
	 */
	public function createReportContentParameter($data){
		// ReportContentParameter_ReportIdVARCHAR(50)Код параметра для Бирта, перекрывает Имя параметра из справочника
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = null;
			exec rpt.p_ReportContentParameter_ins
				@ReportContentParameter_id = @Res output,
				@ReportParameter_id = :ReportParameter_id,
				@ReportContent_id = :ReportContent_id,
				@Report_id = :Report_id,
				@ReportContentParameter_Default = :ReportContentParameter_Default,
				@ReportContentParameter_Required = :ReportContentParameter_Required,
				@ReportContentParameter_ReportId = :ReportContentParameter_ReportId,
				@ReportContentParameter_Position = :ReportContentParameter_Position,
				@ReportContentParameter_PrefixId = :ReportContentParameter_PrefixId,
				@ReportContentParameter_PrefixText = :ReportContentParameter_PrefixText,
				@ReportContentParameter_ReportLabel = :ReportContentParameter_ReportLabel,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as ReportContentParameter_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//echo getDebugSQL($query, $reportData); exit;
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
}
?>
