<?php defined('BASEPATH') or die ('No direct script access allowed');
/* 
 * Контроллер для двига отчетов
*/

/**
 * @author yunitsky
 */
class ReportEndUser extends swController {

	/**
	 * construct
	 */
	function __construct() {
        parent::__construct();
        $this->load->database( 'reports' );
        $this->load->model('ReportEndUser_model','model');
        $this->inputRules = array(
                'getTree' => array(
                        array(
                                'field' => 'node',
                                'label' => 'Код узла',
                                'rules' => 'required',
                                'type' => 'string'
                        ),
					array(
						'field' => 'arm_type',
						'label' => 'Тип Арма',
						'rules' => '',
						'type' => 'string'
					)
                ),
				'getTreeNmp' => array(
                        array(
                                'field' => 'node',
                                'label' => 'Код узла',
                                'rules' => 'required',
                                'type' => 'string'
                        ),
					array(
						'field' => 'arm_type',
						'label' => 'Тип Арма',
						'rules' => '',
						'type' => 'string'
					)
                ),
                'getReportContent' => array(
                        array(
                                'field' => 'reportId',
                                'label' => 'Идентификатор отчета',
                                'rules' => '',
                                'type' => 'int'
                        ),
                        array(
                                'field' => 'ownerId',
                                'label' => 'Код каталога',
                                'rules' => '',
                                'type' => 'string'
                        )
                ),
				'UpdateMyReports' => array(
					array(
						'field' => 'Report_id',
						'label' => 'Идентификатор отчета',
						'rules' => 'required',
						'type' => 'int'
					),
					array(
						'field' => 'pmUser_id',
						'label' => 'Идентификатор пользователя',
						'rules' => 'required',
						'type' => 'int'
					),
					array(
						'field' => 'upd_mode',
						'label' => 'Режим',
						'rules' => 'required',
						'type' => 'string'
					)
				),
				'getReportFromMyReports' => array(
					array(
						'field' => 'Report_id',
						'label' => 'Идентификатор отчета',
						'rules' => 'required',
						'type' => 'int'
					),
					array(
						'field' => 'pmUser_id',
						'label' => 'Идентификатор пользователя',
						'rules' => 'required',
						'type' => 'int'
					)
				)
        );
    }

	/**
	 * getTree ()
	 */
	function getTree() {
        //echo $this->model->getTree($this->ProcessInputData('getTree', true, true));
        echo $this->model->getTreeNew($this->ProcessInputData('getTree', true, true));
    }

	/**
	 * getTreeCC()
	 */
	function getTreeCC() {
        echo $this->model->getTreeCC($this->ProcessInputData('getTree', true, true));
    }

	/**
	 * getTreeHN()
	 */
	function getTreeHN() {
        echo $this->model->getTreeHN($this->ProcessInputData('getTree', true, true));
    }

	/**
	 * getTreeNmp()
	 */
	function getTreeNmp() {
        //echo $this->model->getTreeNmp($this->ProcessInputData('getTreeNmp', true, true));
		
		$data = $this->ProcessInputData('getTreeNmp', true, true);
		if(!empty($data['session']['CurArmType'])) $data['arm_type'] = $data['session']['CurArmType'];
		if($data['arm_type'] == 'nmpgranddoc'){
			echo $this->model->getTreeNmp($this->ProcessInputData('getTreeNmp', true, true));
		}else{
			$data['nmp'] = true;
			$data['ARMList'] = array();
			if($data['arm_type']){
				$data['ARMList'] = array($data['arm_type']);
			}
			echo $this->model->getTreeNew($data);
		}
    }

	/**
	 * getTreeOuzSpec
	 */
	function getTreeOuzSpec() {
		echo $this->model->getTreeOuzSpec($this->ProcessInputData('getTree', true, true));
	}

	/**
	 * getReportContentEngine()
	 */
	public function getReportContentEngine() {
        echo $this->model->getReportContentEngine($this->ProcessInputData('getReportContent', true, true));
    }

	/**
	 * getParameterContentEngine()
	 */
	public function getParameterContentEngine() {
        echo $this->model->getParameterContentEngine($_REQUEST);
    }

	/**
	 * getFormats()
	 */
	public function getFormats() {
        echo $this->model->getFormats();
    }

	/**
	 * Добавление/удаление отчета из папки "Мои отчеты" https://redmine.swan.perm.ru/issues/56057
	 */
	function UpdateMyReports()
	{
		$data = $this->ProcessInputData('UpdateMyReports',true,true);
		$result = $this->model->UpdateMyReports($data);
		$this->ProcessModelSave($result,true)->ReturnData();
		return true;
	}

	/**
	 * Проверка наличия отчета в папке "Мои отчеты" https://redmine.swan.perm.ru/issues/56057
	 */
	function getReportFromMyReports()
	{
		$data = $this->ProcessInputData('getReportFromMyReports',true,true);
		$result = $this->model->getReportFromMyReports($data);
		$this->ProcessModelList($result,true)->ReturnData();
		return true;
	}

}
?>
