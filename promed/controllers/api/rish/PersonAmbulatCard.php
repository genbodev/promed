<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PersonAmbulatCard - контроллер API для работы с амбулаторной картой
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class PersonAmbulatCard extends SwREST_Controller {
	protected  $inputRules = array(
		'getPersonAmbulatCard' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonAmbulatCard_Num','label' => 'Номер амбулаторной карты','rules' => '','type' => 'string'),
			array('field' => 'Date_DT','label' => 'Дата (для получения списка действующих на дату амбулаторных карт)','rules' => '','type' => 'date')
		),
		'createPersonAmbulatCard' => array(
			array('field' => 'Person_id','label' => 'Идентификатор человека','rules' => 'required','type' => 'id'),
			array('field' => 'Lpu_id','label' => 'Идентификатор МО','rules' => 'required','type' => 'id'),
			array('field' => 'PersonAmbulatCard_Num','label' => 'Номер амбулаторной карты','rules' => '','type' => 'string'),
			array('field' => 'PersonAmbulatCard_begDate','label' => 'Дата начала действия амбулаторной карты','rules' => '','type' => 'date'),
			array('field' => 'PersonAmbulatCard_endDate','label' => 'Дата окончания действия амбулаторной карты','rules' => '','type' => 'date'),
			array('field' => 'PersonAmbulatCard_CloseCause','label' => 'Причина закрытия амбулаторной карты','rules' => '','type' => 'string')
		),
		'updatePersonAmbulatCard' => array(
			array('field' => 'Person_id',						'label' => 'Идентификатор человека',						'rules' => '',			'type' => 'id'),
			array('field' => 'Lpu_id',							'label' => 'Идентификатор МО',								'rules' => '',			'type' => 'id'),
			array('field' => 'PersonAmbulatCard_Num',			'label' => 'Номер амбулаторной карты',						'rules' => '',			'type' => 'string'),
			array('field' => 'PersonAmbulatCard_begDate',		'label' => 'Дата начала действия амбулаторной карты',		'rules' => '',			'type' => 'date'),
			array('field' => 'PersonAmbulatCard_endDate',		'label' => 'Дата окончания действия амбулаторной карты',	'rules' => '',			'type' => 'date'),
			array('field' => 'PersonAmbulatCard_CloseCause',	'label' => 'Причина закрытия амбулаторной карты',			'rules' => '',			'type' => 'string'),
			array('field' => 'PersonAmbulatCard_id',			'label' => 'ИД амбулаторной карты',							'rules' => 'required',	'type' => 'id'),
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('PersonAmbulatCard_model', 'dbmodel');
	}

	/**
	 * Получение данных об амбулаторных картах человека
	 */
	function index_get() {
		$data = $this->ProcessInputData('getPersonAmbulatCard');
		
		if(empty($data['Person_id']) && empty($data['Lpu_id'])){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Хотя бы один из параметров Person_id, Lpu_id должен быть передан'
			));
		}

		//$resp = $this->dbmodel->loadPersonAmbulatCardListForAPI($data);
		$resp = $this->dbmodel->getPersonAmbulatCardForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		//$this->response($resp);
		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
	
	/**
	 * Создание амбулаторной карты
	 */
	function index_post() {
		$data = $this->ProcessInputData('createPersonAmbulatCard');
		$sp = getSessionParams();
		
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['Server_id'] = $sp['Server_id'];
		
		if( isset($data['PersonAmbulatCard_endDate']) ){
			$endDate =  new DateTime($data['PersonAmbulatCard_endDate']);
			$currentDate =  new DateTime();
			if($endDate<=$currentDate){
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Дата окончания действия амбулаторной карты должна быть больше текущей даты'
				));
			}
		}
		
		$resp = $this->dbmodel->savePersonAmbulatCard($data);
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}
		
		if(!is_array($resp) || empty($resp[0]['PersonAmbulatCard_id'])){
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array(
			'error_code' => 0,
			'PersonAmbulatCard_id' => $resp[0]['PersonAmbulatCard_id']
		));
	}
	
	/**
	 * Редактирование амбулаторной карты человека
	 */
	function index_put() {
		$data = $this->ProcessInputData('updatePersonAmbulatCard',  false, true);
		$sql = "
			SELECT top 1
				PersonAmbulatCard_id,
				Person_id,
				PersonAmbulatCard_Num,
				Lpu_id
			FROM v_PersonAmbulatCard with (nolock)
			WHERE
				PersonAmbulatCard_id = ?;";
		$res = $this->dbmodel->getFirstRowFromQuery($sql, array($data['PersonAmbulatCard_id']));
		if(empty($res['PersonAmbulatCard_id'])){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Не удалось проверить наличие уже существующего прикрепления',
			));
		}
		
		if(empty($data['Person_id'])) $data['Person_id'] = $res['Person_id'];
		if(empty($data['Lpu_id'])) $data['Lpu_id'] = $res['Lpu_id'];
		if(empty($data['PersonAmbulatCard_id'])) $data['PersonAmbulatCard_id'] = $res['PersonAmbulatCard_id'];
		if(empty($data['PersonAmbulatCard_Num'])) {
			$data['PersonAmbulatCard_Num'] = $res['PersonAmbulatCard_Num'];
			$data['ignoreUniq'] = true;
		}
		
		$resp = $this->dbmodel->savePersonAmbulatCard($data);
		
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}
		
		if(empty($resp[0]['PersonAmbulatCard_id'])){
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array(
			'error_code' => 0
		));
	}
}