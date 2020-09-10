<?php
defined('BASEPATH') or die('No direct script access allowed');

/**
 * Контроллер Cmp110
 * 
 * Контроллер для работы с картами вызова формы 110У
 */
class Cmp110 extends swController {
	
	public $inputRules = array(
		'addEntity' => array(
			array('field' => 'entity', 'label' => 'Entity', 'rules' => 'required'),
			array('field' => 'attribute', 'label' => 'Attribute', 'rules' => 'required'),
			array('field' => 'values', 'label' => 'Values', 'rules' => 'required'),
		),
	);
	
	/**
	 * @inheritdoc
	 */
	public function __construct(){
		parent::__construct();
		
		$this->initModel();
	}
	
	/**
	 * Инициализация модели
	 */
	public function initModel(){
		$this->load->database();
		$this->load->model('Cmp110_model', 'model');
	}
	
	/**
	 * Возвращает форму
	 */
	public function getForm(){
		$result = $this->model->buildForm();
		$this->output
			->set_content_type('application/json; charset=UTF-8')
			->set_output(json_encode($result, JSON_UNESCAPED_UNICODE));
	}
	
}