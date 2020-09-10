<?php

defined('BASEPATH') or die('No direct script access allowed');
/*
 * Модель для двига отчетов
 */

/**
 * @author yunitsky
 */
class ReportEndUser_model extends SwPgModel {
    /**
     * задолбался я уже втыкать коменты перед функциями
     */
    function ReportEndUserx_model() {
        parent::__construct();
    }

    //const REPORTS_FOLDERS_SQL = 'select * from rpt.v_ReportCatalog (nolock) ';
	const REPORTS_FOLDERS_SQL = "
		select
			ReportCatalog_id as \"ReportCatalog_id\",
			ReportCatalog_pid as \"ReportCatalog_pid\",
			ReportCatalog_Name as \"ReportCatalog_Name\",
			ReportCatalog_Status as \"ReportCatalog_Status\",
			ReportCatalog_Position as \"ReportCatalog_Position\"
		from rpt.v_ReportCatalog
	";
    const REPORTS_FOLDERS_SQL_HN = "
    select
			RC.ReportCatalog_id as \"ReportCatalog_id\",
			RC.ReportCatalog_pid as \"ReportCatalog_pid\",
			RC.ReportCatalog_Name as \"ReportCatalog_Name\",
			RC.ReportCatalog_Status as \"ReportCatalog_Status\",
			RC.ReportCatalog_Position as \"ReportCatalog_Position\",
			case when RC_P.ReportCatalog_Name = 'Государственные отчёты' then 1 else 0 end as \"allow_folder\"
		from rpt.v_ReportCatalog RC
		left join rpt.v_ReportCatalog RC_P on RC_P.ReportCatalog_id = RC.ReportCatalog_rid
    ";
	const REPORT_MYFOLDER_SQL = "
		select distinct 
			R.Report_id as \"Report_id\",
			R.ReportCatalog_id as \"ReportCatalog_id\",
			R.Report_Description as \"Report_Description\",
			R.Report_Title as \"Report_Title\",
			R.Report_FileName as \"Report_FileName\",
			R.Report_Status as \"Report_Status\",
			R.Report_Position as \"Report_Position\",
			R.ReportType_id as \"ReportType_id\",
			R.DatabaseType as \"DatabaseType\",
			R.Region_id as \"Region_id\",
			R.Report_Caption as \"Report_Caption\"
		from rpt.v_ReportUser RU
		inner join rpt.v_Report R on R.Report_id = RU.Report_id
		where RU.User_id = :pmUser_id
		order by R.Report_Caption
	";
    const REPORTS_SQL = "
		select 
			R.Report_id as \"Report_id\",
			R.ReportCatalog_id as \"ReportCatalog_id\",
			R.Report_Description as \"Report_Description\",
			R.Report_Title as \"Report_Title\",
			R.Report_FileName as \"Report_FileName\",
			R.Report_Status as \"Report_Status\",
			R.Report_Position as \"Report_Position\",
			R.ReportType_id as \"ReportType_id\",
			R.DatabaseType as \"DatabaseType\",
			R.Region_id as \"Region_id\",
			R.Report_Caption as \"Report_Caption\"
		from rpt.v_Report R
		where R.ReportCatalog_id = :nodeId 
		order by R.Report_Position
	";
    const REPORTS_SQL_HN = "
        select 
        	R.Report_id as \"Report_id\",
			R.ReportCatalog_id as \"ReportCatalog_id\",
			R.Report_Description as \"Report_Description\",
			R.Report_Title as \"Report_Title\",
			R.Report_FileName as \"Report_FileName\",
			R.Report_Status as \"Report_Status\",
			R.Report_Position as \"Report_Position\",
			R.ReportType_id as \"ReportType_id\",
			R.DatabaseType as \"DatabaseType\",
			R.Region_id as \"Region_id\",
        	case when (RC_P.ReportCatalog_Name = 'Государственные отчёты' or RC.ReportCatalog_Name = 'Государственные отчёты') then 1 else 0 end as \"allow_report\"
        from rpt.v_Report R
        left join rpt.v_ReportCatalog RC on RC.ReportCatalog_id = R.ReportCatalog_id
        left join rpt.v_ReportCatalog RC_P on RC_P.ReportCatalog_id = RC.ReportCatalog_rid
        where R.ReportCatalog_id = :nodeId
        order by R.Report_Position
    ";
	//Запрос для получения отчетов с сортировкой по текущему АРМу. В рамках задачи http://redmine.swan.perm.ru/issues/18509
	const REPORTS_SQL_WITH_ARMSORT = "
										select distinct
										R.Report_id as \"Report_id\",
										R.Report_Position as \"Report_Position\",
										R.Report_Caption as \"Report_Caption\",
										R.Report_Status as \"Report_Status\",
										case when AT.ARMType_SysNick=:arm_type then '0' end as \"Arm_Position\"
									from rpt.v_Report R
										left join rpt.v_ReportARM RA on RA.Report_id = R.Report_id
										left join v_ARMType AT on AT.ARMType_id = RA.ARMType_id
									where R.ReportCatalog_id = :nodeId
										and AT.ARMType_SysNick = :arm_type
									union
									select distinct
										R.Report_id as \"Report_id\",
										R.Report_Position as \"Report_Position\",
										R.Report_Caption as \"Report_Caption\",
										R.Report_Status as \"Report_Status\",
										case when (AT.ARMType_SysNick<>:arm_type or AT.ARMType_SysNick is null) then '1' end as \"Arm_Position\"
									from rpt.v_Report R
										left join rpt.v_ReportARM RA on RA.Report_id = R.Report_id
										left join v_ARMType AT on AT.ARMType_id = RA.ARMType_id
									where R.ReportCatalog_id = :nodeId
										and (AT.ARMType_SysNick is null or AT.ARMType_SysNick <> :arm_type)
										and R.Report_id not in (
															select distinct R.Report_id as Report
															from rpt.v_Report R
															left join rpt.v_ReportARM RA on RA.Report_id = R.Report_id
															left join v_ARMType AT on AT.ARMType_id = RA.ARMType_id
															where R.ReportCatalog_id = :nodeId
															and AT.ARMType_SysNick = :arm_type
															)
									order by Arm_Position,R.Report_Position
									";

    /**
     * @param $status
     * @return bool
     */
    public function checkStatus($status) {
        if ($status == 0)
            return true;
        if ($status == 1 && (isMinZdrav() || isSuperadmin() || havingGroup('OuzSpec')))
            return true;
        if ($status == 2 && isSuperadmin())
            return true;
        return false;
    }

    /**
     * Формирование дерева отчетов.
     * @param $params параметры запроса
     *        $params['objectType'] - тип ноды
     *        $params['node'] - ид ноды
     */
    public function getTree($params) {
        $responce = new JsonResponce();
        $node = $params['node'];

		if (isset($params['arm_type'])){
			if($params['arm_type']){
				$arm_type = $params['arm_type'];
			}
		}
        $where = '';
		if(strpos($node,'myfolder')>0) //https://redmine.swan.perm.ru/issues/56057
		//Если находимся в папке "Мои отчеты" - используем соответствующий запрос, запихивая отчеты не с #rr, а с #_rr, чтоб избежать дурблирования в дереве
		{
			$tmpr = array();
			$pmUser = $params['pmUser_id'];
			$reports = $this->db->query(self::REPORT_MYFOLDER_SQL, array('pmUser_id' => $pmUser));
			$tmpr = $reports->result('array');
			$responce->toTree($tmpr, '#_rr', 'Report_id', 'Report_Caption',
				array('objectType' => 'report', 'viewClass' => 'ReportView', 'iconCls' => 'rpt-report', 'leaf' => true));
			return $responce->utf()->json();
		}
		else
		{
			if ($node == 'root') {
				$where = ' where ReportCatalog_pid is null order by ReportCatalog_Position';
				$node = 0;
			} else {
				$node = preg_replace('/[^\d]*/', '', $node);
				$where = ' where ReportCatalog_pid = ' . $node . '  order by ReportCatalog_Position';
			}
			$folders = $this->db->query(self::REPORTS_FOLDERS_SQL . $where);
			if (isset($arm_type)){	//http://redmine.swan.perm.ru/issues/18509
				if($arm_type){
					$reports = $this->db->query(self::REPORTS_SQL_WITH_ARMSORT, array('nodeId' => $node, 'arm_type' => $arm_type));
				}
			}
			else{
				$reports = $this->db->query(self::REPORTS_SQL, array('nodeId' => $node));
			}
			$folders = $folders->result('array');
			$reports = $reports->result('array');
			// Отсеиваем по статусу доступа
			$tmpf = array();
			$tmpr = array();

			if (is_array($folders) && is_array($reports)) {
				foreach ($folders as $folder) {
					if ($this->checkStatus($folder['ReportCatalog_Status']) && $this->checkFolderOnReport($folder) )
						$tmpf[] = $folder;
				}
				foreach ($reports as $report) {
					if ($this->checkStatus($report['Report_Status'])) {
						if( isSuperAdmin() || $this->isAccessToReport($report) ) {
							$tmpr[] = $report;
						}
					}
				}

				if($node == 0) //https://redmine.swan.perm.ru/issues/56057 Если грузим из конревой папки, добавляем папку "Мои отчеты"
				{
					$newfolder = array(
						'ReportCatalog_id' => 'myfolder',
						'ReportCatalog_pid' => null,
						'ReportCatalog_Name' => 'Мои отчеты',
						'ReportCatalog_Status' => '0',
						'ReportCatalog_Position' => 0
					);
					$tmpf[] = $newfolder;
				}

				$responce->toTree($tmpf, '#rc', 'ReportCatalog_id', 'ReportCatalog_Name', array('objectType' => 'catalog', 'viewClass' => 'ReportFolderView', 'iconCls' => 'rpt-folder'));
				$responce->toTree($tmpr, '#rr', 'Report_id', 'Report_Caption',
						array('objectType' => 'report', 'viewClass' => 'ReportView', 'iconCls' => 'rpt-report', 'leaf' => true));
				return $responce->utf()->json();
			} else {
				return false;
			}
		}
    }

    /**
     * Получает путь до папки вида root/folder1/folder2/... (root обозначает папку региона)
     */
    function getFolderPath($folders_array, $ReportCatalog_id) {
		$result = '';
		// ищем текущий каталог в массиве
		$index = array_search($ReportCatalog_id, array_column($folders_array, 'ReportCatalog_id'));
		// текущий каталог существует
		if ($index) {
			//добавляем текущий каталог в путь
			$result .= '/' . $folders_array[$index]['ReportCatalog_Name'];
			//ищем родительский
			$result = $this->getFolderPath($folders_array, $folders_array[$index]['ReportCatalog_pid']) . $result;
		} else if (empty($ReportCatalog_id)) {
			$result .= 'root';
		}
		

		return $result;
    }
	
	/**
	*	Новая функция отображения дерева
	*/
    public function getTreeNew($params) {
    	$responce = new JsonResponce();
        $node = $params['node'];
		if(empty($params['nmp'])) $params['nmp'] = false;
		$this->load->library('swCache');
		if (isset($params['arm_type'])){
			if($params['arm_type']){
				$arm_type = $params['arm_type'];
			}
		}
        $where = '';
		if(strpos($node,'myfolder')>0) //https://redmine.swan.perm.ru/issues/56057
		//Если находимся в папке "Мои отчеты" - используем соответствующий запрос, запихивая отчеты не с #rr, а с #_rr, чтоб избежать дурблирования в дереве
		{
			$tmpr = array();
			$pmUser = $params['pmUser_id'];
			$reports = $this->db->query(self::REPORT_MYFOLDER_SQL, array('pmUser_id' => $pmUser));
			$tmpr = $reports->result('array');
			$responce->toTree($tmpr, '#_rr', 'Report_id', 'Report_Caption',
				array('objectType' => 'report', 'viewClass' => 'ReportView', 'iconCls' => 'rpt-report', 'leaf' => true));
			return $responce->utf()->json();
		}
		else
		{
			$reports_res = array();
			if(empty($params['nmp']) && $this->swcache->get("ReportsList_".$params['pmUser_id']))
				$reports_res = $this->swcache->get("ReportsList_".$params['pmUser_id']);
			else
			{
				$join_reports = "";
				$queryParams = array('Region_id' => getRegionNumber());
				$pmUserCacheGroup_SysNick = null;
				switch(true) {
					case havingGroup('PM'):
						$pmUserCacheGroup_SysNick = 'PM';
						break;
					case havingGroup('MIACStat'):
						$pmUserCacheGroup_SysNick = 'MIACStat';
						break;
				}
				if (!empty($pmUserCacheGroup_SysNick)) {
					$join_reports .= "
						inner join rpt.ReportUserGroup RUG on RUG.Report_id = R.Report_id
						inner join v_pmUserCacheGroup pucg on pucg.pmUserCacheGroup_id = RUG.pmUserCacheGroup_id and pucg.pmUserCacheGroup_SysNick = :pmUserCacheGroup_SysNick
					";
					$queryParams['pmUserCacheGroup_SysNick'] = $pmUserCacheGroup_SysNick;
				}
				
				$query_reports = "
					select
						Report_id as \"Report_id\",
						ReportCatalog_id as \"ReportCatalog_id\",
						Report_Caption as \"Report_Caption\",
						Report_Description as \"Report_Description\",
						Report_Title as \"Report_Title\",
						Report_FileName as \"Report_FileName\",
						Report_Status as \"Report_Status\",
						Report_Position as \"Report_Position\",
						Region_id as \"Region_id\",
						ReportType_id as \"ReportType_id\",
						DatabaseType as \"DatabaseType\",
						ReportArmStr.ReportArmStr_Items as \"ReportArm\"
					from rpt.v_Report R
					{$join_reports}
					left join lateral (
						select string_agg(AT.ARMType_SysNick, ', ') as ReportArmStr_Items
						from rpt.v_ReportARM RA
						inner join v_ARMType AT on AT.ARMType_id = RA.ARMType_id
						where RA.Report_id = R.Report_id
					) ReportArmStr on true
					where
						R.Region_id = :Region_id
						or exists (	-- загружаем отчёты связанные с регионом через таблицу ReportsList
							select RLs.ReportList_id
							from
								rpt.ReportList RLs
							where
								RLs.Report_id = R.Report_id
								and RLs.Region_id = :Region_id
							limit 1
						)
					/*order by R.Report_Caption*/
					order by R.Report_Position
				";
				$result_reports = $this->db->query($query_reports, $queryParams);
				if(is_object($result_reports))
				{
					$reports = $result_reports->result('array');
					
					$reports_res = array();
					$armList = array();
					if($params['nmp'] && isset($params['ARMList'])){
						// для НПМ только в зависимости от того, под каким АРМ работает пользователь
						$armList = $params['ARMList'];
					} elseif (isset($_SESSION['ARMList']) && sizeof($_SESSION['ARMList']) > 0) {
						$armList = $_SESSION['ARMList'];
					}
					foreach($reports as $report)
					{
						$flag_r = false;
						if($this->checkStatus($report['Report_Status'])) //Если статус отчета позволяет его отобразить, то смотрим на права доступа для АРМов. Иначе вообще не отображаем этот отчет.
						{
							if(sizeof($armList) == 0) //Если по какой-то причине $_SESSION['ARMList'] пустой, то отображаем все отчеты
								$reports_res[] = $report;
							else
							{
								$ReportArm = explode(',',str_replace(' ','',$report['ReportArm']));
								for($j=0;$j<count($ReportArm);$j++)
								{
									if(in_array($ReportArm[$j], $armList)){
										$flag_r = true;
									}
								}
								// для НМП только в зависимости от того, под каким АРМ работает пользователь
								if(empty($params['nmp'])){
									if(isSuperAdmin())
										$flag_r = true;
									if($report['Report_Status'] == 1 && (isMinZdrav() || isSuperadmin() || havingGroup('OuzSpec')))
										$flag_r = true;
								}
								if($flag_r)
									$reports_res[] = $report;
							}
						}
					}
					// НМП не кещируем.
					if( empty($params['nmp']) ) $this->swcache->set("ReportsList_".$params['pmUser_id'], $reports_res, array('ttl'=>1800));
				}
			}

			$folders_res = array();
			
			if(empty($params['nmp']) && $this->swcache->get("CatalogsList_".$params['pmUser_id']))
				$folders_res = $this->swcache->get("CatalogsList_".$params['pmUser_id']);
			else
			{
				$query_folders = "
					select
						ReportCatalog_id as \"ReportCatalog_id\",
						ReportCatalog_pid as \"ReportCatalog_pid\",
						ReportCatalog_rid as \"ReportCatalog_rid\",
						ReportCatalog_Name as \"ReportCatalog_Name\",
						ReportCatalog_Status as \"ReportCatalog_Status\",
						ReportCatalog_Path as \"ReportCatalog_Path\",
						ReportCatalog_Position as \"ReportCatalog_Position\",
						Region_id as \"Region_id\"
					from rpt.v_ReportCatalog RC
					where
						Region_id = :Region_id
						or exists (
							select RLs.ReportList_id
							from
								rpt.ReportList RLs
							where
								RLs.ReportCatalog_id = RC.ReportCatalog_id
								and RLs.Region_id = :Region_id
							limit 1
						)
					/*order by ReportCatalog_Name*/
					order by ReportCatalog_Position
				";
				$result_folders = $this->db->query($query_folders,array('Region_id' => getRegionNumber()));
				if(is_object($result_folders)){
					$folders = $result_folders->result('array');
					
					$folders_res = array();
					foreach ($folders as $folder)
					{
						if($this->checkFolder($folder, $reports_res, $folders))
							$folders_res[] = $folder;
					}
					// НМП не кещируем.
					if( empty($params['nmp']) ) $this->swcache->set("CatalogsList_".$params['pmUser_id'], $folders_res, array('ttl'=>1800));
				}
			}

			if ($node == 'root') {
				$node = 0;
			} else {
				$node = preg_replace('/[^\d]*/', '', $node);
			}
			
			$tmpr = array();
			$folder_ids = array();
			// ищем все папки у которых путь совпадает с текущей
			$nodePath = $this->getFolderPath($folders_res, $node);
			
			
			foreach ($folders_res as $key => $folder) {
				if ( $nodePath == $this->getFolderPath($folders_res, $folder['ReportCatalog_id']) ) {
					$folder_ids[] = $folder['ReportCatalog_id'];
				}
			}

			// добавляем отчёты, дочерние для текущей папки и папки с тем же путём, в ответ
			foreach ($reports_res as $report) {	
				if( in_array($report['ReportCatalog_id'], $folder_ids) ) {
					$hide = false;
					if (!empty($report['Report_FileName']) && $report['Region_id'] == null) {
						$report_path = $this->getFolderPath($folders_res, $report['ReportCatalog_id']);
						foreach ($reports_res as $rep) {
							if (
								$rep['Region_id'] != null 
								&& $report['Report_FileName'] == $rep['Report_FileName'] 
								&& $report_path == $this->getFolderPath($folders_res, $rep['ReportCatalog_id'])
							) {
								$hide = true;
							}
						}
					}
					if (!$hide) {
						if ($report['Region_id'] == null) {
							$report['iconCls'] = 'reports16';
						}
						
						$tmpr[] = $report;
					}
				}
			}

			$tmpf = array();
			foreach($folders_res as $folder)
			{
				if($node == 0)
				{
					if($folder['ReportCatalog_pid'] == NULL && $this->checkStatus($folder['ReportCatalog_Status']))
						$tmpf[] = $folder;
				}
				else
				{
					
					if($folder['ReportCatalog_pid'] == $node) {
						
						$folder_path = $this->getFolderPath($folders_res, $folder['ReportCatalog_pid']);
						foreach ($folders_res as $folder2) {
							if (
								$folder['Region_id'] != $folder2['Region_id']
								&& $folder_path == $this->getFolderPath($folders_res, $folder2['ReportCatalog_pid'])
							) {
								$tmpf[] = $folder2;
							}
						}
						
						$tmpf[] = $folder;
					}
				}
			}
			/*function compare_folder($f1,$f2) {
				if($f1['ReportCatalog_Name'] == $f2['ReportCatalog_Name'])
					return 0;
				return($f1['ReportCatalog_Name'] < $f2['ReportCatalog_Name'])? -1 : 1;
			}*/

			// объединяем папки с одинаковыми путями
			foreach ($tmpf as $folderKey1 => $folder1) {
				foreach ($tmpf as $folderKey2 => $folder2) {
					// находим папку с тем же названием
					if ($folderKey1 < $folderKey2 && $this->getFolderPath($folders_res, $folder1['ReportCatalog_id']) == $this->getFolderPath($folders_res, $folder2['ReportCatalog_id']) ) {
						//переносим все отчёты в первую папку
						foreach ($tmpr as $reportKey => $report) {
							if ($report['ReportCatalog_id'] == $folder2['ReportCatalog_id'] ) {
								$tmpr[$reportKey]['ReportCatalog_id'] = $folder1['ReportCatalog_id'];
							}
						}
						unset($tmpf[$folderKey2]);//Удаляем папку с тем же названием
					}
				}
			}
			/**
			* Условие для сортировки папок
			*/
			function compare_folder($f1,$f2) {
				if($f1['ReportCatalog_Position'] == $f2['ReportCatalog_Position'])
					return 0;
				return($f1['ReportCatalog_Position'] < $f2['ReportCatalog_Position'])? -1 : 1;
			}
			/*function compare_report($r1,$r2) {
				if($r1['Report_Caption'] == $r2['Report_Caption'])
					return 0;
				return($r1['Report_Caption'] < $r2['Report_Caption'])? -1 : 1;
			}*/
			/**
			* Условие для сортировки отчетов
			*/
			function compare_report($r1,$r2) {
				if($r1['Report_Position'] == $r2['Report_Position'])
					return 0;
				return($r1['Report_Position'] < $r2['Report_Position'])? -1 : 1;
			}
			usort($tmpf,"compare_folder");
			usort($tmpr,"compare_report");
			if($node == 0 /*&& empty($params['nmp'])*/)
			{
				$newfolder = array(
						'ReportCatalog_id' => 'myfolder',
						'ReportCatalog_pid' => null,
						'ReportCatalog_Name' => 'Мои отчеты',
						'ReportCatalog_Status' => '0',
						'ReportCatalog_Position' => 0
					);
				$tmpf[] = $newfolder;
			}
			
			if(is_array($tmpf) || is_array($tmpr))
			{
				$responce->toTree($tmpf, '#rc', 'ReportCatalog_id', 'ReportCatalog_Name', array('objectType' => 'catalog', 'viewClass' => 'ReportFolderView', 'iconCls' => 'rpt-folder'));
				$responce->toTree($tmpr, '#rr', 'Report_id', 'Report_Caption', array('objectType' => 'report', 'viewClass' => 'ReportView', 'iconCls' => 'rpt-report', 'leaf' => true));
				return $responce->utf()->json();
			}
			else
				return false;
		}
    }
	
	/**
	*	Проверка возможности отображения каталога
	*/
	function checkFolder($folder, $reports_res, $folders)
	{
		foreach ($reports_res as $report)
		{
			if($report['ReportCatalog_id'] == $folder['ReportCatalog_id'])
			{
				return true;
			}
		}
		foreach($folders as $f)
		{
			if($f['ReportCatalog_pid'] == $folder['ReportCatalog_id'])
				if($this->checkFolder($f, $reports_res, $folders))
					return true;
		}
		
		return false;
	}

    /**
     * @param $params
     * @return bool|string
     * Функция для АРМ оператора call-центра
     * http://redmine.swan.perm.ru/issues/19455 пункт 8
     */
    public function getTreeCC($params) {
		$responce = new JsonResponce();
        $node = $params['node'];
        $where = '';
		if(strpos($node,'myfolder')>0) //https://redmine.swan.perm.ru/issues/56057
			//Если находимся в папке "Мои отчеты" - используем соответствующий запрос, запихивая отчеты не с #rr, а с #_rr, чтоб избежать дурблирования в дереве
		{
			$tmpr = array();
			$pmUser = $params['pmUser_id'];
			$reports = $this->db->query(self::REPORT_MYFOLDER_SQL, array('pmUser_id' => $pmUser));
			$tmpr = $reports->result('array');
			$responce->toTree($tmpr, '#_rr', 'Report_id', 'Report_Caption',
				array('objectType' => 'report', 'viewClass' => 'ReportView', 'iconCls' => 'rpt-report', 'leaf' => true));
			return $responce->utf()->json();
		}
		else {
			if ($node == 'root') {
				$where = " where ReportCatalog_Name ilike '%для Центра записи%'";
				$node = 0;
			} else {
				$node = preg_replace('/[^\d]*/', '', $node);
				$where = ' where ReportCatalog_pid = ' . $node . '  order by ReportCatalog_Position';
			}
			$folders = $this->db->query(self::REPORTS_FOLDERS_SQL . $where);
			$reports = $this->db->query(self::REPORTS_SQL, array('nodeId' => $node));
			$folders = $folders->result('array');
			$reports = $reports->result('array');
			// Отсеиваем по статусу доступа
			$tmpf = array();
			$tmpr = array();

			if (is_array($folders) && is_array($reports)) {
				foreach ($folders as $folder) {
					if ($this->checkStatus($folder['ReportCatalog_Status']) && $this->checkFolderOnReport($folder) )
						$tmpf[] = $folder;
				}
				foreach ($reports as $report) {
					if ($this->checkStatus($report['Report_Status'])) {
						if( isSuperAdmin() || $this->isAccessToReport($report) ) {
							$tmpr[] = $report;
						}
					}
				}
				if($node == 0) //https://redmine.swan.perm.ru/issues/56057 Если грузим из конревой папки, добавляем папку "Мои отчеты"
				{
					$newfolder = array(
						'ReportCatalog_id' => 'myfolder',
						'ReportCatalog_pid' => null,
						'ReportCatalog_Name' => 'Мои отчеты',
						'ReportCatalog_Status' => '0',
						'ReportCatalog_Position' => 0
					);
					$tmpf[] = $newfolder;
				}
				$responce->toTree($tmpf, '#rc', 'ReportCatalog_id', 'ReportCatalog_Name', array('objectType' => 'catalog', 'viewClass' => 'ReportFolderView', 'iconCls' => 'rpt-folder'));
				$responce->toTree($tmpr, '#rr', 'Report_id', 'Report_Caption',
						array('objectType' => 'report', 'viewClass' => 'ReportView', 'iconCls' => 'rpt-report', 'leaf' => true));
				return $responce->utf()->json();
			} else {
				return false;
			}
		}
    }

    /**
     * @param $params
     * @return bool|string
     * Функция для АРМ старшей медсестры
     */
    public function getTreeHN($params) {
		$responce = new JsonResponce();
		$node = $params['node'];
        $allow_reports = array(
            'Форма №016/у-02',
            //'Форма № 007/у-02',
            //'Форма № 007дс/у-02',
            //'Список пациентов, находящихся в отделении МО',
            //'Сведения о состоянии пациентов в отделении МО',
            'Журнал госпитализаций',
            'Количество госпитализированных пациентов',
            'Количество госпитализированных пациентов (развёрнутый)',
            'Отчёт движения выбывших пациентов',
            'Отчёт по не выписанным пациентам',
            'Оперативный отчёт по стационару',

            'Акт списания медикаментов',
            'Итоговая ведомость расхода медикаментов',
            'Ведомость: наличие медикаментов',
            'Ведомость: накопительная ведомость по расходу',
            'Ведомость: накопительная ведомость по приходу',
            'Оборотная ведомость'
        );
        $allow_folders = array(
            'Государственные отчёты',
            'Статистические отчёты',
            'Стационар',
            //'Отделения',
            //'Отчеты для пищеблока',
            'Аптека'
        );
		$where = '';
        if ($params['session']['region']['nick'] == 'ufa') {
            $allow_reports[] = 'Форма № 007дс/у-02';
            $allow_reports[] = 'Форма № 007/у-02';
            $allow_reports[] = 'Список пациентов, находящихся в отделении МО';
            $allow_reports[] = 'Сведения о состоянии пациентов в отделении МО';
            $allow_folders[] = 'Отделения';
            $allow_folders[] = 'Отчёты для пищеблока';
        }
		if ($params['session']['region']['nick'] == 'astra') {
			$allow_reports[] = 'Список пациентов';
			$allow_reports[] = 'Список пациентов с некорректным адресом или без адреса';

			$allow_folders[] = 'Больные';
			$allow_reports[] = 'Отчёт по больным: должности';
			$allow_reports[] = 'Отчёт по больным: место работы';
			$allow_reports[] = 'Список стационарных больных';

			$allow_folders[] = 'Отделения';
			$allow_reports[] = 'Отделения: пол, возраст';
			$allow_reports[] = 'Отделения: исход госпитализации';
			$allow_reports[] = 'Отделения: исход госпитализации (с учётом переводов)';
			$allow_reports[] = 'Отделения: территории';
			$allow_reports[] = 'Отделения: операции';
			$allow_reports[] = 'Отделения: виды оплаты';
			$allow_reports[] = 'Отчёт по отделениям, оказавшим услугу';
			$allow_reports[] = 'Список пациентов, находящихся на лечении';

			$allow_folders[] = 'Списки для проверки движения';
			$allow_reports[] = 'Список выписанных больных из стационара';
			$allow_reports[] = 'Список поступивших больных (без переведённых внутри больницы)';
			$allow_reports[] = 'Список переведённых больных внутри больницы';
			$allow_reports[] = 'Список детей находящихся в отделении в сопровождении взрослых';

			$allow_folders[] = 'Учёт медикаментов';
			$allow_reports[] = 'Список пациентов, получавших конкретное ЛС за период';
			$allow_reports[] = 'Расход ЛС на пациента';
			$allow_reports[] = 'Учёт стоимости лечения в отделении за период';
		}
		if(strpos($node,'myfolder')>0) //https://redmine.swan.perm.ru/issues/56057
			//Если находимся в папке "Мои отчеты" - используем соответствующий запрос, запихивая отчеты не с #rr, а с #_rr, чтоб избежать дурблирования в дереве
		{
			$tmpr = array();
			$pmUser = $params['pmUser_id'];
			$reports = $this->db->query(self::REPORT_MYFOLDER_SQL, array('pmUser_id' => $pmUser));
			$tmpr = $reports->result('array');
			$responce->toTree($tmpr, '#_rr', 'Report_id', 'Report_Caption',
				array('objectType' => 'report', 'viewClass' => 'ReportView', 'iconCls' => 'rpt-report', 'leaf' => true));
			return $responce->utf()->json();
		}
		else
		{
			if ($node == 'root') {
				$where = " where RC.ReportCatalog_Name in ('Государственные отчёты','Статистические отчёты') order by RC.ReportCatalog_Position";
				$node = 0;
			} else {
				$node = preg_replace('/[^\d]*/', '', $node);
				$where = " where RC.ReportCatalog_pid = " . $node . " order by RC.ReportCatalog_Position";
			}
			$folders = $this->db->query(self::REPORTS_FOLDERS_SQL_HN . $where);
			$reports = $this->db->query(self::REPORTS_SQL_HN, array('nodeId' => $node));
			$folders = $folders->result('array');
			$reports = $reports->result('array');
			// Отсеиваем по статусу доступа
			$tmpf = array();
			$tmpr = array();

			if (is_array($folders) && is_array($reports)) {
				foreach ($folders as $folder) {
					if(in_array($folder['ReportCatalog_Name'], $allow_folders) || $folder['allow_folder'] == '1')
						$tmpf[] = $folder;
				}
				foreach ($reports as $report) {
					if(in_array($report['Report_Caption'],$allow_reports) || $report['allow_report'] == '1')
					$tmpr[] = $report;
				}
				if($node == 0) //https://redmine.swan.perm.ru/issues/56057 Если грузим из конревой папки, добавляем папку "Мои отчеты"
				{
					$newfolder = array(
						'ReportCatalog_id' => 'myfolder',
						'ReportCatalog_pid' => null,
						'ReportCatalog_Name' => 'Мои отчеты',
						'ReportCatalog_Status' => '0',
						'ReportCatalog_Position' => 0
					);
					$tmpf[] = $newfolder;
				}
				$responce->toTree($tmpf, '#rc', 'ReportCatalog_id', 'ReportCatalog_Name', array('objectType' => 'catalog', 'viewClass' => 'ReportFolderView', 'iconCls' => 'rpt-folder'));
				$responce->toTree($tmpr, '#rr', 'Report_id', 'Report_Caption',
						array('objectType' => 'report', 'viewClass' => 'ReportView', 'iconCls' => 'rpt-report', 'leaf' => true));
				return $responce->utf()->json();
			}
			else
			{
				return false;
			}
		}
	}

	/**
     * @param $params
     * @return bool|string
     * Функция для АРМ старшей медсестры
     */
    public function getTreeNmp($params) {
		$responce = new JsonResponce();
		$node = $params['node'];

        $allow_folders = array(
            'НМП'
        );
		$where = '';

		if ($node == 'root') {
			$where = " where RC.ReportCatalog_Name in ('НМП') order by RC.ReportCatalog_Position";
			$node = 0;
		} else {
			$node = preg_replace('/[^\d]*/', '', $node);
			$where = " where RC.ReportCatalog_pid = " . $node . " order by RC.ReportCatalog_Position";
		}
		$folders = $this->db->query(self::REPORTS_FOLDERS_SQL_HN . $where);
		$reports = $this->db->query(self::REPORTS_SQL_HN, array('nodeId' => $node));
		$folders = $folders->result('array');
		$reports = $reports->result('array');
		// Отсеиваем по статусу доступа
		$tmpf = array();
		$tmpr = array();

		if (is_array($folders) && is_array($reports)) {
			foreach ($folders as $folder) {
				if(in_array($folder['ReportCatalog_Name'], $allow_folders) || $folder['allow_folder'] == '1')
					$tmpf[] = $folder;
			}
			foreach ($reports as $report) {
				//if(in_array($report['Report_Caption'],$allow_reports) || $report['allow_report'] == '1')
				$tmpr[] = $report;
			}
			/*
			 * мои отчеты
			if($node == 0) //https://redmine.swan.perm.ru/issues/56057 Если грузим из конревой папки, добавляем папку "Мои отчеты"
			{
				$newfolder = array(
					'ReportCatalog_id' => 'myfolder',
					'ReportCatalog_pid' => null,
					'ReportCatalog_Name' => 'Мои отчеты',
					'ReportCatalog_Status' => '0',
					'ReportCatalog_Position' => 0
				);
				$tmpf[] = $newfolder;
			}
			*/
			$responce->toTree($tmpf, '#rc', 'ReportCatalog_id', 'ReportCatalog_Name', array('objectType' => 'catalog', 'viewClass' => 'ReportFolderView', 'iconCls' => 'rpt-folder'));
			$responce->toTree($tmpr, '#rr', 'Report_id', 'Report_Caption',
					array('objectType' => 'report', 'viewClass' => 'ReportView', 'iconCls' => 'rpt-report', 'leaf' => true));
			return $responce->utf()->json();
		}
		else
		{
			return false;
		}

	}

	/**
	 * getTreeOuzSpec
	 */
	public function getTreeOuzSpec($params)
	{
		$responce = new JsonResponce();
		$node = $params['node'];
		$allow_reports = array(
				'Сведения об использовании (эксплуатации) медицинских изделий в МО'
		);
		$allow_folders = array(
				'Статистические отчёты',
				'Паспорт МО'
		);
		$where = '';
		if(strpos($node,'myfolder')>0) //https://redmine.swan.perm.ru/issues/56057
			//Если находимся в папке "Мои отчеты" - используем соответствующий запрос, запихивая отчеты не с #rr, а с #_rr, чтоб избежать дурблирования в дереве
		{
			$tmpr = array();
			$pmUser = $params['pmUser_id'];
			$reports = $this->db->query(self::REPORT_MYFOLDER_SQL, array('pmUser_id' => $pmUser));
			$tmpr = $reports->result('array');
			$responce->toTree($tmpr, '#_rr', 'Report_id', 'Report_Caption',
					array('objectType' => 'report', 'viewClass' => 'ReportView', 'iconCls' => 'rpt-report', 'leaf' => true));
			return $responce->utf()->json();
		}
		else
		{
			if ($node == 'root') {
				$where = " where RC.ReportCatalog_Name in ('Статистические отчёты') order by RC.ReportCatalog_Position";
				$node = 0;
			} else {
				$node = preg_replace('/[^\d]*/', '', $node);
				$where = " where RC.ReportCatalog_pid = " . $node . " order by RC.ReportCatalog_Position";
			}
			$folders = $this->db->query(self::REPORTS_FOLDERS_SQL_HN . $where);
			$reports = $this->db->query(self::REPORTS_SQL_HN, array('nodeId' => $node));
			$folders = $folders->result('array');
			$reports = $reports->result('array');
			// Отсеиваем по статусу доступа
			$tmpf = array();
			$tmpr = array();

			if (is_array($folders) && is_array($reports)) {
				foreach ($folders as $folder) {
					if(in_array($folder['ReportCatalog_Name'], $allow_folders) || $folder['allow_folder'] == '1')
						$tmpf[] = $folder;
				}
				foreach ($reports as $report) {
					if(in_array($report['Report_Caption'],$allow_reports) || $report['allow_report'] == '1')
						$tmpr[] = $report;
				}
				if($node == 0) //https://redmine.swan.perm.ru/issues/56057 Если грузим из конревой папки, добавляем папку "Мои отчеты"
				{
					$newfolder = array(
							'ReportCatalog_id' => 'myfolder',
							'ReportCatalog_pid' => null,
							'ReportCatalog_Name' => 'Мои отчеты',
							'ReportCatalog_Status' => '0',
							'ReportCatalog_Position' => 0
					);
					$tmpf[] = $newfolder;
				}
				$responce->toTree($tmpf, '#rc', 'ReportCatalog_id', 'ReportCatalog_Name', array('objectType' => 'catalog', 'viewClass' => 'ReportFolderView', 'iconCls' => 'rpt-folder'));
				$responce->toTree($tmpr, '#rr', 'Report_id', 'Report_Caption',
						array('objectType' => 'report', 'viewClass' => 'ReportView', 'iconCls' => 'rpt-report', 'leaf' => true));
				return $responce->utf()->json();
			}
			else
			{
				return false;
			}
		}
	}
	/**
     * @param $folderData
     * @return bool
     */
    public function checkFolderOnReport($folderData) {
		$query = "
			select
				Report_id as \"Report_id\"
				,Report_Status as \"Report_Status\"
			from
				rpt.v_Report
			where
				ReportCatalog_id = :ReportCatalog_id
		";
		$res = $this->db->query($query, array(
			'ReportCatalog_id' => $folderData['ReportCatalog_id']
		));
		if ( !is_object($res) ) {
 	    	return false;
 	    }
		$rs = $res->result('array');
		foreach($rs as $k=>$r) {
			if( (!$this->isAccessToReport($r) && !isSuperAdmin()) || !$this->checkStatus($r['Report_Status']) ) {
				unset($rs[$k]);
			}
		}
		if( count($rs) > 0 )
			return true;

		$query = "
			select
				ReportCatalog_id as \"ReportCatalog_id\"
			from
				rpt.v_ReportCatalog
			where
				ReportCatalog_pid = :ReportCatalog_id
		";
		$res = $this->db->query($query, array(
			'ReportCatalog_id' => $folderData['ReportCatalog_id']
		));
		if ( !is_object($res) ) {
 	    	return false;
 	    }
		$cs = $res->result('array');
		if( count($cs) == 0 )
			return false;

		foreach( $cs as $c ) {
			if($this->checkFolderOnReport($c)) {
				return true;
			}
		}
		return false;
	}

    /**
     * @param $reportData
     * @return bool
     */
    private function isAccessToReport($reportData) {
		$arms_rep = $this->getAvailableARMs(array('Report_id' => $reportData['Report_id']));
		if( !isset($_SESSION['ARMList']) ) return true;
		$i=0;
        if(sizeof($_SESSION['ARMList']) == 0)
            return true;
        else{
            //while( isset($arms_rep[$i]) && !in_array($arms_rep[$i]['ARMType_SysNick'], $_SESSION['ARMList']) ) $i++;
            //return $i < count($arms_rep) || count($arms_rep) == 0;
			//while( isset($arms_rep[$i]) && in_array($arms_rep[$i]['ARMType_SysNick'], $_SESSION['ARMList']) ) $i++;

			for($j=0;$j<count($arms_rep);$j++){
				if(in_array($arms_rep[$j]['ARMType_SysNick'],$_SESSION['ARMList']))
					$i++;
			}

			return $i>0;
        }
	}

    /**
     * @param $data
     * @return bool
     */
    protected function getAvailableARMs($data) {
		$query = "
			select distinct
				AT.ARMType_Code as \"ARMType_Code\",
				AT.ARMType_SysNick as \"ARMType_SysNick\"
			from
				v_ARMType AT
				left join rpt.ReportARM RA on RA.ARMType_id = AT.ARMType_id
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

	/**
	 * Проверка наличия отчета в папке "Мои отчеты" https://redmine.swan.perm.ru/issues/56057
	 */
	function getReportFromMyReports($data)
	{
		$query = "
			select ReportUser_id as \"ReportUser_id\"
			from rpt.v_ReportUser
			where Report_id = :Report_id
			and User_id = :pmUser_id
		";
		$result = $this->db->query($query, array('Report_id'=>$data['Report_id'],'pmUser_id'=>$data['pmUser_id']));
		if(is_object($result))
		{
			$result = $result->result('array');
			if(is_array($result) && count($result)>0)
				return array(true);
			else
				return array(false);
		}
		else
			return array(false);
	}

	/**
	 * Добавление/удаление отчета из папки "Мои отчеты" https://redmine.swan.perm.ru/issues/56057
	 */
	function UpdateMyReports($data)
	{
		if(isset($data['pmUser_id']) && $data['pmUser_id'] > 0)
		{
			if($data['upd_mode'] == 'add')
			{
				$query_add = "
				select 
					ReportUser_id as \"ReportUser_id\", 
					Error_Code as \"Error_Code\", 
					Error_Message as \"Error_Msg\"
				from rpt.p_ReportUser_ins (
					Report_id := :Report_id,
					pmUser_id := :pmUser_id,
					User_id := :pmUser_id
				)";
				$res = $this->db->query($query_add, [
					'Report_id' => $data['Report_id'],
					'pmUser_id' => $data['pmUser_id']
				]);
			} else {
				$query = "
					select ReportUser_id as \"ReportUser_id\"
					from rpt.v_ReportUser
					where Report_id = :Report_id
					and User_id = :pmUser_id
				";
				$result = $this->db->query($query,array('Report_id'=>$data['Report_id'],'pmUser_id'=>$data['pmUser_id']));
				if(is_object($result))
				{
					$result = $result->result('array');
					if(is_array($result))
					{
						$reportUser_id = $result[0]['ReportUser_id'];
						$query_del = "
							select 
								Error_Code as \"Error_Code\", 
								Error_Message as \"Error_Msg\"
							from rpt.p_ReportUser_del (
								ReportUser_id := :ReportUser_id
							)
						";
						//echo getDebugSQL($query_del, array('ReportUser_id'=>$reportUser_id));die;
						$res = $this->db->query($query_del, array('ReportUser_id'=>$reportUser_id));
					}
				}
			}
			if(is_object($res))
			{
				$response = $res->result('array');
				return $response;
			}
		}
	}

}
?>
