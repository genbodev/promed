<?php defined('BASEPATH') or die ('No direct script access allowed');


require_once(APPPATH.'controllers/EvnUsluga.php');

class Samara_EvnUsluga extends EvnUsluga {
  
	protected $evnUslugaClasses = array(
		'EvnUsluga',
		'EvnUslugaCommon',
		'EvnUslugaOper',
		'EvnUslugaStom',
		'EvnUslugaPregnancySpec'//! только удаление
        ,
        'EvnUslugaOnkoBeam',
        'EvnUslugaOnkoChem',
        'EvnUslugaOnkoGormun',
	);	

	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('Samara_EvnUsluga_model', 'samara_dbmodel');
	}    


	function loadEvnUslugaGrid() {
		$data = $this->ProcessInputData('loadEvnUslugaGrid', true);
		if ($data === false) { return false; }

		if ( !in_array($data['class'], $this->evnUslugaClasses) )  {
			echo json_return_errors('Неверный класс услуги');
			return false;
		}

		$response = $this->samara_dbmodel->loadEvnUslugaGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}

	    
}
