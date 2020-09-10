<?php defined('BASEPATH') or die ('No direct script access allowed');
/* 
 * Контроллер для отчета о стуктуре базы данных
*/

/**
 * @author yunitsky
 */
class ReportDBStructure extends swController {
	/**
	 * ReportDBStructure constructor.
	 */
    function __construct() {
		parent::__construct();

		$this->inputRules = array(
			'getReport' => array(
				array('field' => 'SelectedObjects', 'label' => 'Идентификаторы таблиц', 'rules' => '', 'type' => 'string'),
				array('field' => 'MaxRows', 'label' => 'Максимальное количество строк', 'rules' => '', 'type' => 'int'),
				array('field' => 'ShowRows', 'label' => 'Количество строк для отображения', 'rules' => '', 'type' => 'int')				
			)
		);
    }

	/**
	 * Некая функция
	 */
	function getReport() {		
		$data = $this->ProcessInputData('getReport', false);
		$params = array();
		if (isset($data['SelectedObjects']))
			$params['allowed_tables'] = explode(',', $data['SelectedObjects']);
		$params['max_row'] = isset($data['MaxRows']) && $data['MaxRows'] > 0 ? floor($data['MaxRows']) : 10;
		$params['show_row'] = isset($data['ShowRows']) && $data['ShowRows'] > 0 ? floor($data['ShowRows']) : 10;
		$this->renderReport('custom', $params);
	}

	/**
	 * Некая функция
	 */
    function getFullReport() {
		$this->renderReport();
    }

	/**
	 * @param string $mode
	 * @param array $params
	 */
	function renderReport($mode = 'full', $params = array()) {
		if (!isSuperAdmin()) return;
	
		$this->load->database();
        $this->load->model('ReportDBStructure_model','dbmodel');
		
        echo '
			<html>
			<head>
				<title>Отчёт по структуре базы РИАМС ПроМед</title>
				<style type="text/css">
					h1 { font-size: 24px; }
					table { border-collapse: collapse; }
					td { border: solid 1px black; padding: 2px 5px; text-align: left; font-size: 9pt; }
					tr.header td { font-weight: bolder; text-align: center; background-color: #dddddd; }
					td.header { font-weight: bolder; text-align: left; background-color: #dddddd; }
					td.header2 { font-weight: bolder; text-align: left; background-color: #ffffff; }
				</style>
			</head>
			<body>
		';		
		
		$table_list = $this->dbmodel->getTableList();
		if ($mode == 'custom' && isset($params['allowed_tables']) && is_array($params['allowed_tables']) && count($params['allowed_tables']) > 0) {
			$tmp_arr = array();
			foreach($table_list as $tbl)
				if (in_array($tbl['object_id'], $params['allowed_tables']))
					$tmp_arr[] = $tbl;
			$table_list = $tmp_arr;
		}
		
		if ((!is_array($table_list)) || (count($table_list) == 0)) {
			echo 'Ошибка при получении списка таблиц';			
		} else {
			echo '<h1>1. Перечень таблиц</h1>';
			$this->renderTable('list', $table_list);
			
			echo '<h1>2. Структура таблиц</h1>';
			for($i = 0; $i < count($table_list); $i++) {
				set_time_limit (30);
				$this->renderTable('structure', $this->dbmodel->getTableStructure($table_list[$i]['object_id']), array('num' => $i+1, 'name' => $table_list[$i]['schema_name'].'.'.$table_list[$i]['table_name'], 'description' => $table_list[$i]['description']));
				$this->renderTable('data', $this->dbmodel->getTableData($table_list[$i]['schema_name'].'.'.$table_list[$i]['table_name'], $params));
				echo '<br/><br/>';
			}
		}		
		echo '
			</body>
			</html>
		';
	}

	/**
	 * @param $type
	 * @param $data
	 * @param array $add_data
	 * @return bool
	 */
	function renderTable($type, $data, $add_data = array()) {
		//types: 'list', 'structure', 'data'
		$i = 1;
		if (!is_array($data) || count($data) == 0)
			return false;
		
		echo '<table cellspacing="0">';
		
		//формируем заголовок таблицы
		switch($type) {
			case 'list': {
				echo '
					<tr class="header">
						<td>№</td>
						<td>Название схемы</td>
						<td>Имя таблицы</td>
						<td>Наименование</td>
					</tr>
				';
				break;
			}
			case 'structure': {
				echo '
					<tr>
						<td colspan="2" class="header">Таблица '.$add_data['num'].' :</td>
						<td class="header2">'.($add_data['name'] ? $add_data['name'] : '&nbsp;').'</td>
					</tr>
					<tr>
						<td colspan="2" class="header">Название таблицы:</td>
						<td class="header2">'.$add_data['description'].'</td>
					</tr>
					<tr class="header">
						<td>Структура таблицы</td>
						<td>Тип</td>
						<td>Обьяснение</td>
					</tr>
				';
				break;
			}
			case 'data': {
				echo '<tr class="header">';
					foreach($data[0] as $key => $value)
						echo '<td>'.($key ? $key : '&nbsp;').'</td>';
				echo '</tr>';
				break;
			}
		}
		
		foreach($data as $row) { //формируем тело таблицы
			echo '<tr>';
			if ($type == 'list') {				
				echo '<td>'.($i++).'</td>';
				foreach($row as $key=>$val) if ($key != 'object_id')
					echo '<td>'.($val ? $val : '&nbsp;').'</td>';
			} else {
				foreach($row as $key=>$val) {
					$vl = $val;
					if (!empty($vl)) {
						if (is_object($vl) && get_class($vl) == 'DateTime')
							$vl = $vl->format("Y-m-d H:i:s");
						if (strlen($vl) > 300)
							$vl = substr($vl, 0, 300).'...';
					} else {
						$vl = '&nbsp;';
					}					
					echo '<td>'.($vl != '&nbsp;' ? htmlspecialchars($vl) : $vl).'</td>';
				}
			}
			echo '</tr>';
		}
		
		echo '</table>';
	}

	/**
	 * @return bool
	 */
	function getTablesGrid() {
		$this->load->database();
		$this->load->model('ReportDBStructure_model', 'dbmodel');
		$val  = array();

		$response = $this->dbmodel->getTableList();
		if (is_array($response)) {
			foreach($response as $row) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$row['checkbox'] = "<input type=\"checkbox\" name=\"tables\" value=\"".$row['object_id']."\"/>";
				$val[] = $row;
			}
		}		
		$this->ReturnData($val);
		return true;
	}
}
?>
