<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы с таблицей EvnNotifyHIV ОПЕРАТИВНОЕ ДОНЕСЕНИЕ о лице в крови которого при исследовании в реакции иммуноблота выявлены антитела к ВИЧ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 * @author			Vyacheslav Gluchov
 * @version			11.2018
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class EvnOnkoNotify extends SwREST_Controller {
	protected  $inputRules = array(
		'getEvnOnkoNotify' => array(
			array(
				'field' => 'EvnOnkoNotify_id',
				'label' => 'Идентификатор извещения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => '',
				'type' => 'id'
			),
		),
		'createEvnOnkoNotify' => array(
					/*array(
						'field' => 'EvnOnkoNotify_id',
						'label' => 'Идентификатор',
						'rules' => '',
						'type' => 'id'
					),
					array(
						'field' => 'EvnOnkoNotify_pid',
						'label' => 'Идентификатор движения или посещения',
						'rules' => 'required',
						'type' => 'id'
					),
					array(
						'field' => 'Server_id',
						'label' => 'Идентификатор сервера',
						'rules' => 'required',
						'type' => 'int'
					),
					array(
						'field' => 'PersonEvn_id',
						'label' => 'Идентификатор состояния человека',
						'rules' => 'required',
						'type' => 'id'
					),*/
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnOnkoNotify_setDT',
				'label' => 'Дата заболевания',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Врач, заполнивший извещение',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_sid',
				'label' => 'ЛПУ, куда направлено извещение',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnOnkoNotify_setDiagDT',
				'label' => 'Дата установления диагноза',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'TumorStage_id',
				'label' => 'Стадия опухолевого процесса',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OnkoT_id',
				'label' => 'T',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'OnkoN_id',
				'label' => 'N',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'OnkoM_id',
				'label' => 'M',
				'rules' => 'required',
				'type' => 'id'
			),	
			array('field' => 'EvnOnkoNotify_IsDiagConfCito', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsDiagConfClinic', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsDiagConfExplo', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsDiagConfLab', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsDiagConfMorfo', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsDiagConfUnknown', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsTumorDepoBones', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsTumorDepoBrain', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsTumorDepoKidney', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsTumorDepoLiver', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsTumorDepoLungs', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsTumorDepoLympha', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsTumorDepoMarrow', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsTumorDepoMulti', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsTumorDepoOther', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsTumorDepoOvary', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsTumorDepoPerito', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsTumorDepoSkin', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsTumorDepoUnknown', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'Evn_pid', 'label' => '', 'rules' => '','type' => 'id')
		),
		'updateEvnOnkoNotify' => array(
			array('field' => 'EvnOnkoNotify_id', 'label' => '', 'rules' => 'required','type' => 'id'),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnOnkoNotify_setDT',
				'label' => 'Дата заболевания',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Врач, заполнивший извещение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_sid',
				'label' => 'ЛПУ, куда направлено извещение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnOnkoNotify_setDiagDT',
				'label' => 'Дата установления диагноза',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'TumorStage_id',
				'label' => 'Стадия опухолевого процесса',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OnkoT_id',
				'label' => 'T',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OnkoN_id',
				'label' => 'N',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OnkoM_id',
				'label' => 'M',
				'rules' => '',
				'type' => 'id'
			),	
			array('field' => 'EvnOnkoNotify_IsDiagConfCito', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsDiagConfClinic', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsDiagConfExplo', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsDiagConfLab', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsDiagConfMorfo', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsDiagConfUnknown', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsTumorDepoBones', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsTumorDepoBrain', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsTumorDepoKidney', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsTumorDepoLiver', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsTumorDepoLungs', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsTumorDepoLympha', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsTumorDepoMarrow', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsTumorDepoMulti', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsTumorDepoOther', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsTumorDepoOvary', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsTumorDepoPerito', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsTumorDepoSkin', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'EvnOnkoNotify_IsTumorDepoUnknown', 'label' => '', 'rules' => '','type' => 'int'),
			array('field' => 'Evn_pid', 'label' => '', 'rules' => '','type' => 'id')
		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnOnkoNotify_model', 'EvnOnkoNotify_model');
	}
	
	/**
	 * Создание извещения об онкобольном
	 */
	function index_post(){
		$data = $this->ProcessInputData('createEvnOnkoNotify', null, true);
		$data['methodApi'] = true;
		$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		
		$res = $this->EvnOnkoNotify_model->saveEvnOnkoNotifyAPI($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res['EvnOnkoNotify_id'])){
			$this->response(array(
				'error_code' => 0,
				'EvnOnkoNotify_id' => $res['EvnOnkoNotify_id']
			));
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res['Error_Msg'])) ? $res['Error_Msg'] : 'ошибка создания извещения об онкобольном'
			));
		}
	}
	
	/**
	 * Получение извещения об онкобольном
	 */
	function index_get(){
		$data = $this->ProcessInputData('getEvnOnkoNotify', null, true);
		if(empty($data['Person_id']) && empty($data['EvnOnkoNotify_id'])){
			$this->response(array(
				'error_msg' => 'Хотя бы один из параметров (Person_id или EvnOnkoNotify_id) должен быть задан',
				'error_code' => '3'
			));
		}
		
		$resp = $this->EvnOnkoNotify_model->loadAPI($data);		
		$this->response($resp);
	}
	
	/**
	 * Изменение извещения об онкобольном
	 */
	function index_put(){
		$data = $this->ProcessInputData('updateEvnOnkoNotify');
		
		$data['methodApi'] = true;
		$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		$data['EvnOnkoNotify_pid'] = $data['Evn_pid'];
				
		$res = $this->EvnOnkoNotify_model->updateEvnOnkoNotifyAPI($data);
		if (!is_array($res)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($res[0]['EvnOnkoNotify_id'])){
			$this->response(array(
				'error_code' => 0
			));
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($res[0]['Error_Msg'])) ? $res[0]['Error_Msg'] : 'ошибка изменения извещения об онкобольном'
			));
		}
	}
}