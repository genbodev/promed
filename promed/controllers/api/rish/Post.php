<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы со специальностями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Alexander Kurakin
 * @version			11.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class Post extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('Post_model', 'dbmodel');
		$this->inputRules = $this->dbmodel->inputRules;
	}

	/**
	 * Получение списка специальностей в МО
	 */
	function PostByMO_get() {
		$data = $this->ProcessInputData('PostByMO');

		$resp = $this->dbmodel->getPostsForLpu($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение должности по коду
	 */
	function PostByCode_get() {
		$data = $this->ProcessInputData('loadPostByCode');

		$resp = $this->dbmodel->loadPostForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6',
				'data' => ''
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение должности по идентификатору
	 */
	function PostByid_get() {
		$data = $this->ProcessInputData('loadPostByid');

		$resp = $this->dbmodel->loadPostForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6',
				'data' => ''
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
	
	/**
	 * 	Создание должности
	 */
	function index_post(){
		$data = $this->ProcessInputData('createPost');

		$resp = $this->dbmodel->createPostForAPI($data);
		if(!empty($resp['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp['Post_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'Post_id' => $resp['Post_id']
		));
	}
	
	/**
	 * Изменение должности
	 */
	function index_put(){
		$data = $this->ProcessInputData('updatePost');

		$resp = $this->dbmodel->updatePostForAPI($data);
		if(!empty($resp['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp['Error_Msg'],
				'error_code' => $resp['success']
			));
		}
		if (!$resp) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}
}