<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * QueryToDbfExporter - выгрузка результатов SQL-запроса в DBF
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package		Common
 * @access		public
 * @copyright	Copyright (c) 2009-2013 Swan Ltd.
 * @author		gabdushev
 * @version		17.03.2013
 *
 * @property QueryToDbfExporter_model QueryToDbfExporter_model
 */
class QueryToDbfExporter extends swController {

    public $inputRules = array(
        'exportQuery' => array(
            array(
                'field' => 'query_nick',
                'label' => 'Запрос, результаты которого требуется экспортровать',
                'rules' => '',
                'type' => 'string'
            ),
        )
    );

    public function __construct(){
        parent::__construct();
        $this->load->model('QueryToDbfExporter_model', 'QueryToDbfExporter_model');
    }

    public function exportQuery(){
        $data = $this->ProcessInputData('exportQuery',true, false);
        $this->ProcessModelSave($this->QueryToDbfExporter_model->export($data['query_nick']))->ReturnData();
    }

    public function packResult(){
        $this->ProcessModelSave($this->QueryToDbfExporter_model->packResult())->ReturnData();
    }

    public function getQueryList(){
        return $this->ProcessModelList($this->QueryToDbfExporter_model->getQueryList())->ReturnData();
    }

    public function reset(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            return $this->ProcessModelSave($this->QueryToDbfExporter_model->resetDoneFilesList(),true,'Ошибка при подготовке к экспорту')->ReturnData();
        } else {
            throw new Exception('HTTP 405 Method not allowed');
        }
    }

	public function getQueries(){
		echo '<pre>';
		var_export($this->QueryToDbfExporter_model->getQueries());
		/*$i=0;
		foreach ($this->QueryToDbfExporter_model->getQueries() as $q_nick => $q) {
			$this->QueryToDbfExporter_model->getFirstResultFromQuery('INSERT INTO rls.exp_Query ( Query_Nick, Filename, Name, Query, Ord ) VALUES  ( :Query_Nick, :Filename, :Name, :Query, :Ord )',
				array('Query_Nick' => $q_nick, 'Filename' => $q['filename'],'Name' => $q['name'],'Query' => $q['query'][0],'Ord' => $i++)).PHP_EOL;
			$Query_id = $this->QueryToDbfExporter_model->getFirstResultFromQuery('select @@IDENTITY');
			$k=0;
			foreach ($q['dbf_structure'] as $dbfcol) {
				$this->QueryToDbfExporter_model->getFirstResultFromQuery('INSERT INTO rls.exp_DbaseStructure (Query_id, Query_ColumnName, Dbase_ColumnName, Dbase_ColumnType, Dbase_ColumnLength, Dbase_ColumnPrecision, Description, Ord)
					VALUES  ( :Query_id, :Query_ColumnName, :Dbase_ColumnName, :Dbase_ColumnType, :Dbase_ColumnLength, :Dbase_ColumnPrecision, :Description, :Ord)',
					array(
						'Query_id' => $Query_id,
						'Query_ColumnName' => $dbfcol[0],
						'Dbase_ColumnName' => $dbfcol[0],
						'Dbase_ColumnType' => $dbfcol[1],
						'Dbase_ColumnLength' => isset($dbfcol[2])?$dbfcol[2]:null,
						'Dbase_ColumnPrecision' => isset($dbfcol[3])?$dbfcol[3]:null,
						'Description' => isset($dbfcol['name'])?$dbfcol['name']:null,
						'Ord' => $k++
					));

			}
		}*/
	}

}